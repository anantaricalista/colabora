<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Project_model extends CI_Model {

	public function all_for_user($user_id, $is_admin)
	{
		$this->db
			->select(
				'projects.*, '
				.'(SELECT COUNT(*) FROM '.$this->db->dbprefix('project_tasks').' AS task_count '
				.'WHERE task_count.project_id = projects.id) AS task_count, '
				.'(SELECT COUNT(*) FROM '.$this->db->dbprefix('project_tasks').' AS task_done '
				.'WHERE task_done.project_id = projects.id AND task_done.status = \'complete\') AS completed_count',
				FALSE
			)
			->from('projects')
			->order_by('projects.updated_at', 'DESC');

		if ( ! $is_admin)
		{
			$this->db
				->distinct()
				->join('project_tasks AS assigned_task', 'assigned_task.project_id = projects.id', 'left')
				->group_start()
					->where('projects.coordinator_user_id', $user_id)
					->or_where('assigned_task.assignee_user_id', $user_id)
				->group_end();
		}

		return $this->db->get()->result();
	}

	public function find_for_user($project_id, $user_id, $is_admin)
	{
		$this->db
			->distinct()
			->select(
				'projects.*, '
				.'(SELECT COUNT(*) FROM '.$this->db->dbprefix('project_tasks').' AS task_count '
				.'WHERE task_count.project_id = projects.id) AS task_count, '
				.'(SELECT COUNT(*) FROM '.$this->db->dbprefix('project_tasks').' AS task_done '
				.'WHERE task_done.project_id = projects.id AND task_done.status = \'complete\') AS completed_count',
				FALSE
			)
			->from('projects')
			->where('projects.id', $project_id);

		if ( ! $is_admin)
		{
			$this->db
				->join('project_tasks AS assigned_task', 'assigned_task.project_id = projects.id', 'left')
				->group_start()
					->where('projects.coordinator_user_id', $user_id)
					->or_where('assigned_task.assignee_user_id', $user_id)
				->group_end();
		}

		return $this->db->get()->row();
	}

	public function tasks_for_user($project, $user_id, $is_admin)
	{
		$this->db
			->select('project_tasks.*, users.name AS assignee_name')
			->from('project_tasks')
			->join('users', 'users.id = project_tasks.assignee_user_id')
			->where('project_tasks.project_id', $project->id)
			->order_by('project_tasks.urgency', 'DESC')
			->order_by('project_tasks.due_date', 'ASC')
			->order_by('project_tasks.id', 'ASC');

		if ( ! $is_admin && (int) $project->coordinator_user_id !== (int) $user_id)
		{
			$this->db->where('project_tasks.assignee_user_id', $user_id);
		}

		return $this->db->get()->result();
	}

	public function task($task_id)
	{
		return $this->db
			->select('project_tasks.*, projects.coordinator_user_id, projects.status AS project_status')
			->from('project_tasks')
			->join('projects', 'projects.id = project_tasks.project_id')
			->where('project_tasks.id', $task_id)
			->get()
			->row();
	}

	public function create($data)
	{
		$now = date('Y-m-d H:i:s');
		return $this->db->insert('projects', array(
			'name' => $data['name'],
			'description' => $data['description'] === '' ? NULL : $data['description'],
			'coordinator_user_id' => $data['coordinator_user_id'],
			'status' => 'active',
			'completion_source' => NULL,
			'created_by' => $data['created_by'],
			'created_at' => $now,
			'updated_at' => $now,
			'completed_at' => NULL,
		)) ? (int) $this->db->insert_id() : FALSE;
	}

	public function create_task($data)
	{
		$now = date('Y-m-d H:i:s');
		return $this->db->insert('project_tasks', array(
			'project_id' => $data['project_id'],
			'title' => $data['title'],
			'description' => $data['description'] === '' ? NULL : $data['description'],
			'assignee_user_id' => $data['assignee_user_id'],
			'created_by' => $data['created_by'],
			'urgency' => $data['urgency'],
			'status' => 'incomplete',
			'due_date' => $data['due_date'] === '' ? NULL : $data['due_date'],
			'created_at' => $now,
			'updated_at' => $now,
			'completed_at' => NULL,
		));
	}

	public function change_coordinator($project_id, $coordinator_user_id)
	{
		return $this->db
			->where('id', $project_id)
			->update('projects', array(
				'coordinator_user_id' => $coordinator_user_id,
				'updated_at' => date('Y-m-d H:i:s'),
			));
	}

	public function set_task_status($task_id, $status)
	{
		$this->db->trans_begin();
		$updated = $this->db
			->where('id', $task_id)
			->update('project_tasks', array(
				'status' => $status,
				'updated_at' => date('Y-m-d H:i:s'),
				'completed_at' => $status === 'complete' ? date('Y-m-d H:i:s') : NULL,
			));

		if ( ! $updated)
		{
			$this->db->trans_rollback();
			return FALSE;
		}

		$task = $this->task($task_id);
		if ($task && $task->project_status === 'active')
		{
			if ( ! $this->complete_if_all_tasks_done($task->project_id))
			{
				$this->db->trans_rollback();
				return FALSE;
			}
		}

		if ( ! $this->db->trans_status())
		{
			$this->db->trans_rollback();
			return FALSE;
		}

		return $this->db->trans_commit();
	}

	public function complete($project_id, $source)
	{
		return $this->db
			->where('id', $project_id)
			->where('status', 'active')
			->update('projects', array(
				'status' => 'complete',
				'completion_source' => $source,
				'completed_at' => date('Y-m-d H:i:s'),
				'updated_at' => date('Y-m-d H:i:s'),
			));
	}

	public function reopen($project_id)
	{
		return $this->db
			->where('id', $project_id)
			->where('status', 'complete')
			->update('projects', array(
				'status' => 'active',
				'completion_source' => NULL,
				'completed_at' => NULL,
				'updated_at' => date('Y-m-d H:i:s'),
			));
	}

	private function complete_if_all_tasks_done($project_id)
	{
		$counts = $this->db
			->select('COUNT(*) AS total, SUM(status = \'complete\') AS completed', FALSE)
			->where('project_id', $project_id)
			->get('project_tasks')
			->row();

		if ($counts && (int) $counts->total > 0 && (int) $counts->total === (int) $counts->completed)
		{
			return $this->complete($project_id, 'automatic');
		}

		return TRUE;
	}
}
