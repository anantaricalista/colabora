<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Projects extends MY_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->require_role(array('company_admin', 'employee'));
		$this->load->model(array('Project_model', 'User_model'));
		$this->load->library('form_validation');
	}

	public function index()
	{
		$roles = (array) $this->session->userdata('user_roles');
		$is_admin = in_array('company_admin', $roles, TRUE);
		$this->load->view('projects/index', array(
			'projects' => $this->Project_model->all_for_user((int) $this->session->userdata('user_id'), $is_admin),
			'is_admin' => $is_admin,
		));
	}

	public function create()
	{
		$this->require_role(array('company_admin'));
		$employees = $this->User_model->active_employees();
		$this->form_validation->set_rules('name', 'Nama proyek', 'required|max_length[180]');
		$this->form_validation->set_rules('coordinator_user_id', 'Koordinator', 'required|integer');
		$this->form_validation->set_rules('description', 'Deskripsi', 'max_length[10000]');

		$error = '';
		if ($this->input->method(TRUE) === 'POST' && $this->form_validation->run() === TRUE)
		{
			$coordinator_id = (int) $this->input->post('coordinator_user_id');
			if ( ! $this->User_model->is_active_employee($coordinator_id))
			{
				$error = 'Koordinator harus memiliki akun aktif dengan peran Karyawan.';
			}
			else
			{
				$project_id = $this->Project_model->create(array(
					'name' => trim($this->input->post('name', TRUE)),
					'description' => trim($this->input->post('description', TRUE)),
					'coordinator_user_id' => $coordinator_id,
					'created_by' => (int) $this->session->userdata('user_id'),
				));

				if ($project_id)
				{
					$this->session->set_flashdata('success', 'Proyek berhasil dibuat dan siap dikelola koordinator.');
					redirect('projects/'.$project_id);
				}

				$error = 'Proyek tidak dapat dibuat. Silakan coba lagi.';
			}
		}

		$this->load->view('projects/create', array(
			'employees' => $employees,
			'error' => $error,
		));
	}

	public function change_coordinator($project_id = NULL)
	{
		$this->require_role(array('company_admin'));
		if ($this->input->method(TRUE) !== 'POST' || $project_id === NULL || ! ctype_digit((string) $project_id))
		{
			show_404();
		}

		$project = $this->Project_model->find_for_user((int) $project_id, 0, TRUE);
		if ( ! $project)
		{
			show_404();
		}

		$this->form_validation->set_rules('coordinator_user_id', 'Koordinator', 'required|integer');
		if ($this->form_validation->run() !== TRUE)
		{
			$this->session->set_flashdata('error', 'Pilih koordinator yang valid.');
			redirect('projects/'.$project->id);
		}

		$coordinator_id = (int) $this->input->post('coordinator_user_id');
		if ( ! $this->User_model->is_active_employee($coordinator_id))
		{
			$this->session->set_flashdata('error', 'Koordinator harus memiliki akun aktif dengan peran Karyawan.');
			redirect('projects/'.$project->id);
		}

		if ( ! $this->Project_model->change_coordinator((int) $project->id, $coordinator_id))
		{
			show_error('Koordinator proyek tidak dapat diperbarui.', 500);
		}

		$this->session->set_flashdata('success', 'Koordinator proyek berhasil diperbarui.');
		redirect('projects/'.$project->id);
	}

	public function show($project_id = NULL)
	{
		if ($project_id === NULL || ! ctype_digit((string) $project_id))
		{
			show_404();
		}

		$user_id = (int) $this->session->userdata('user_id');
		$roles = (array) $this->session->userdata('user_roles');
		$is_admin = in_array('company_admin', $roles, TRUE);
		$project = $this->Project_model->find_for_user((int) $project_id, $user_id, $is_admin);
		if ( ! $project)
		{
			show_404();
		}

		$is_coordinator = (int) $project->coordinator_user_id === $user_id;
		$this->load->view('projects/show', array(
			'project' => $project,
			'tasks' => $this->Project_model->tasks_for_user($project, $user_id, $is_admin),
			'is_coordinator' => $is_coordinator,
			'is_admin' => $is_admin,
			'employees' => $is_admin ? $this->User_model->active_employees() : array(),
			'success' => $this->session->flashdata('success'),
			'error' => $this->session->flashdata('error'),
		));
	}

	public function create_task($project_id = NULL)
	{
		$project = $this->coordinated_project($project_id);
		if ($project->status !== 'active')
		{
			show_error('Buka kembali proyek sebelum menambahkan tugas.', 409);
		}

		$employees = $this->User_model->active_employees();
		$this->form_validation->set_rules('title', 'Nama tugas', 'required|max_length[180]');
		$this->form_validation->set_rules('description', 'Deskripsi', 'max_length[10000]');
		$this->form_validation->set_rules('assignee_user_id', 'Penanggung jawab', 'required|integer');
		$this->form_validation->set_rules('urgency', 'Urgensi', 'required|in_list[normal,urgent]');
		$this->form_validation->set_rules('due_date', 'Tenggat waktu', 'callback_valid_optional_date');

		$error = '';
		if ($this->input->method(TRUE) === 'POST' && $this->form_validation->run() === TRUE)
		{
			$assignee_id = (int) $this->input->post('assignee_user_id');
			if ( ! $this->User_model->is_active_employee($assignee_id))
			{
				$error = 'Penanggung jawab harus memiliki akun aktif dengan peran Karyawan.';
			}
			else
			{
				$created = $this->Project_model->create_task(array(
					'project_id' => (int) $project->id,
					'title' => trim($this->input->post('title', TRUE)),
					'description' => trim($this->input->post('description', TRUE)),
					'assignee_user_id' => $assignee_id,
					'created_by' => (int) $this->session->userdata('user_id'),
					'urgency' => $this->input->post('urgency', TRUE),
					'due_date' => trim($this->input->post('due_date', TRUE)),
				));

				if ($created)
				{
					$this->session->set_flashdata('success', 'Tugas berhasil ditambahkan.');
					redirect('projects/'.$project->id);
				}

				$error = 'Tugas tidak dapat dibuat. Silakan coba lagi.';
			}
		}

		$this->load->view('projects/task_create', array(
			'project' => $project,
			'employees' => $employees,
			'error' => $error,
		));
	}

	public function update_task_status($task_id = NULL)
	{
		if ($this->input->method(TRUE) !== 'POST' || $task_id === NULL || ! ctype_digit((string) $task_id))
		{
			show_404();
		}

		$task = $this->Project_model->task((int) $task_id);
		if ( ! $task)
		{
			show_404();
		}

		$user_id = (int) $this->session->userdata('user_id');
		$roles = (array) $this->session->userdata('user_roles');
		$is_admin = in_array('company_admin', $roles, TRUE);
		$project = $this->Project_model->find_for_user((int) $task->project_id, $user_id, $is_admin);
		if ( ! $project)
		{
			show_404();
		}

		if ($project->status !== 'active')
		{
			show_error('Buka kembali proyek sebelum mengubah status tugas.', 409);
		}

		$target_status = $this->input->post('status', TRUE);
		$is_coordinator = (int) $project->coordinator_user_id === $user_id;
		if ( ! in_array($target_status, array('incomplete', 'complete'), TRUE)
			|| ($target_status === 'incomplete' && ! $is_coordinator)
			|| ($target_status === 'complete' && ! $is_coordinator && (int) $task->assignee_user_id !== $user_id))
		{
			show_error('Anda tidak memiliki izin untuk mengubah status tugas ini.', 403);
		}

		if ( ! $this->Project_model->set_task_status((int) $task->id, $target_status))
		{
			show_error('Status tugas atau progres proyek tidak dapat diperbarui.', 500);
		}

		$this->session->set_flashdata('success', 'Status tugas berhasil diperbarui.');
		redirect('projects/'.$project->id);
	}

	public function complete($project_id = NULL)
	{
		$project = $this->coordinated_project($project_id, TRUE);
		if ($this->input->method(TRUE) !== 'POST' || $project->status !== 'active')
		{
			show_404();
		}

		if ( ! $this->Project_model->complete((int) $project->id, 'manual'))
		{
			show_error('Proyek tidak dapat ditandai selesai.', 500);
		}

		$this->session->set_flashdata('success', 'Proyek ditandai selesai oleh koordinator.');
		redirect('projects/'.$project->id);
	}

	public function reopen($project_id = NULL)
	{
		$project = $this->coordinated_project($project_id, TRUE);
		if ($this->input->method(TRUE) !== 'POST' || $project->status !== 'complete')
		{
			show_404();
		}

		if ( ! $this->Project_model->reopen((int) $project->id))
		{
			show_error('Proyek tidak dapat dibuka kembali.', 500);
		}

		$this->session->set_flashdata('success', 'Proyek dibuka kembali untuk melanjutkan pekerjaan.');
		redirect('projects/'.$project->id);
	}

	public function valid_optional_date($value)
	{
		if ($value === '')
		{
			return TRUE;
		}

		if ( ! is_string($value))
		{
			$this->form_validation->set_message('valid_optional_date', 'Format tenggat waktu harus YYYY-MM-DD.');
			return FALSE;
		}

		$date = DateTime::createFromFormat('!Y-m-d', $value);
		$valid = $date && $date->format('Y-m-d') === $value;
		if ( ! $valid)
		{
			$this->form_validation->set_message('valid_optional_date', 'Format tenggat waktu harus YYYY-MM-DD.');
		}

		return $valid;
	}

	private function coordinated_project($project_id, $allow_completed = FALSE)
	{
		if ($project_id === NULL || ! ctype_digit((string) $project_id))
		{
			show_404();
		}

		$project = $this->Project_model->find_for_user(
			(int) $project_id,
			(int) $this->session->userdata('user_id'),
			FALSE
		);
		if ( ! $project || (int) $project->coordinator_user_id !== (int) $this->session->userdata('user_id'))
		{
			show_404();
		}

		if ( ! $allow_completed && $project->status !== 'active')
		{
			show_error('Proyek sudah selesai.', 409);
		}

		return $project;
	}
}
