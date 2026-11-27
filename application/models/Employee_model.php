<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Employee_model extends CI_Model {

	public function all()
	{
		return $this->db
			->select('employee_profiles.*, users.name, users.email, users.is_active AS account_active')
			->from('employee_profiles')
			->join('users', 'users.id = employee_profiles.user_id')
			->order_by('employee_profiles.employment_status', 'ASC')
			->order_by('users.name', 'ASC')
			->get()
			->result();
	}

	public function find($user_id)
	{
		return $this->db
			->select('employee_profiles.*, users.name, users.email, users.is_active AS account_active')
			->from('employee_profiles')
			->join('users', 'users.id = employee_profiles.user_id')
			->where('employee_profiles.user_id', $user_id)
			->get()
			->row();
	}

	public function employee_number_exists($number, $except_user_id = 0)
	{
		$this->db->where('employee_number', $number);
		if ($except_user_id > 0)
		{
			$this->db->where('user_id !=', $except_user_id);
		}

		return (bool) $this->db->count_all_results('employee_profiles');
	}

	public function create($data)
	{
		$now = date('Y-m-d H:i:s');
		return $this->db->insert('employee_profiles', array(
			'user_id' => $data['user_id'],
			'employee_number' => $data['employee_number'],
			'job_title' => $data['job_title'],
			'department' => $data['department'] === '' ? NULL : $data['department'],
			'start_date' => $data['start_date'],
			'employment_status' => 'active',
			'created_at' => $now,
			'updated_at' => $now,
		));
	}

	public function update($user_id, $data)
	{
		return $this->db
			->where('user_id', $user_id)
			->update('employee_profiles', array(
				'employee_number' => $data['employee_number'],
				'job_title' => $data['job_title'],
				'department' => $data['department'] === '' ? NULL : $data['department'],
				'start_date' => $data['start_date'],
				'employment_status' => $data['employment_status'],
				'updated_at' => date('Y-m-d H:i:s'),
			));
	}
}
