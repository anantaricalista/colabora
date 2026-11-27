<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class User_model extends CI_Model {

	public function authenticate($email, $password)
	{
		if ( ! is_string($password))
		{
			return FALSE;
		}

		$user = $this->db
			->select('id, name, email, password_hash')
			->join('employee_profiles', 'employee_profiles.user_id = users.id', 'left')
			->where('email', $email)
			->where('users.is_active', 1)
			->group_start()
				->where('employee_profiles.user_id IS NULL', NULL, FALSE)
				->or_where('employee_profiles.employment_status', 'active')
			->group_end()
			->get('users')
			->row();

		if ( ! $user || ! password_verify($password, $user->password_hash))
		{
			return FALSE;
		}

		$user->roles = $this->roles_for_user($user->id);
		if (empty($user->roles))
		{
			log_message('error', 'Akun pengguna ID '.$user->id.' tidak memiliki peran yang ditetapkan.');
			return FALSE;
		}

		if (password_needs_rehash($user->password_hash, PASSWORD_DEFAULT))
		{
			$updated = $this->db->where('id', $user->id)->update('users', array(
				'password_hash' => password_hash($password, PASSWORD_DEFAULT),
				'updated_at' => date('Y-m-d H:i:s'),
			));
			if ( ! $updated)
			{
				log_message('error', 'Tidak dapat memperbarui hash kata sandi untuk akun pengguna ID '.$user->id.'.');
			}
		}

		unset($user->password_hash, $user->email);
		return $user;
	}

	public function is_active($id)
	{
		return (bool) $this->db
			->from('users')
			->join('employee_profiles', 'employee_profiles.user_id = users.id', 'left')
			->where('users.id', $id)
			->where('users.is_active', 1)
			->group_start()
				->where('employee_profiles.user_id IS NULL', NULL, FALSE)
				->or_where('employee_profiles.employment_status', 'active')
			->group_end()
			->count_all_results();
	}

	public function unprofiled_employee_accounts()
	{
		return $this->db
			->distinct()
			->select('users.id, users.name, users.email')
			->from('users')
			->join('user_roles', 'user_roles.user_id = users.id')
			->join('employee_profiles', 'employee_profiles.user_id = users.id', 'left')
			->where('users.is_active', 1)
			->where('user_roles.role_code', 'employee')
			->where('employee_profiles.user_id IS NULL', NULL, FALSE)
			->order_by('users.name', 'ASC')
			->get()
			->result();
	}

	public function active_employees()
	{
		return $this->db
			->distinct()
			->select('users.id, users.name')
			->from('users')
			->join('user_roles', 'user_roles.user_id = users.id')
			->join('employee_profiles', 'employee_profiles.user_id = users.id')
			->where('users.is_active', 1)
			->where('employee_profiles.employment_status', 'active')
			->where('user_roles.role_code', 'employee')
			->order_by('users.name', 'ASC')
			->get()
			->result();
	}

	public function is_active_employee($id)
	{
		return (bool) $this->db
			->from('users')
			->join('user_roles', 'user_roles.user_id = users.id')
			->join('employee_profiles', 'employee_profiles.user_id = users.id')
			->where('users.id', $id)
			->where('users.is_active', 1)
			->where('employee_profiles.employment_status', 'active')
			->where('user_roles.role_code', 'employee')
			->count_all_results();
	}

	public function all()
	{
		$rows = $this->db
			->select('users.id, users.name, users.email, users.is_active, users.created_at, user_roles.role_code')
			->from('users')
			->join('user_roles', 'user_roles.user_id = users.id', 'left')
			->order_by('users.name', 'ASC')
			->get()
			->result();

		$users = array();
		foreach ($rows as $row)
		{
			$id = (int) $row->id;
			if ( ! isset($users[$id]))
			{
				$users[$id] = (object) array(
					'id' => $id,
					'name' => $row->name,
					'email' => $row->email,
					'is_active' => $row->is_active,
					'created_at' => $row->created_at,
					'roles' => array(),
				);
			}

			if ($row->role_code !== NULL)
			{
				$users[$id]->roles[] = $row->role_code;
			}
		}

		return array_values($users);
	}

	public function count_all()
	{
		return (int) $this->db->count_all('users');
	}

	public function find($id)
	{
		$user = $this->db
			->select('id, is_active')
			->where('id', $id)
			->get('users')
			->row();
		if ($user)
		{
			$user->roles = $this->roles_for_user($user->id);
		}

		return $user;
	}

	public function count_active_admins()
	{
		return (int) $this->db
			->from('users')
			->join('user_roles', 'user_roles.user_id = users.id')
			->where('users.is_active', 1)
			->where('user_roles.role_code', 'company_admin')
			->count_all_results();
	}

	public function set_active($id, $active)
	{
		return $this->db
			->where('id', $id)
			->update('users', array(
				'is_active' => $active ? 1 : 0,
				'updated_at' => date('Y-m-d H:i:s'),
			));
	}

	public function create($data)
	{
		$now = date('Y-m-d H:i:s');
		$this->db->trans_begin();
		$created = $this->db->insert('users', array(
			'name' => $data['name'],
			'email' => $data['email'],
			'password_hash' => password_hash($data['password'], PASSWORD_DEFAULT),
			'is_active' => 1,
			'created_at' => $now,
			'updated_at' => $now,
		));

		if ( ! $created)
		{
			$this->db->trans_rollback();
			return FALSE;
		}

		$user_id = $this->db->insert_id();
		foreach ($data['roles'] as $role)
		{
			if ( ! $this->db->insert('user_roles', array(
				'user_id' => $user_id,
				'role_code' => $role,
			)))
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

	private function roles_for_user($user_id)
	{
		$rows = $this->db
			->select('role_code')
			->where('user_id', $user_id)
			->order_by('role_code', 'ASC')
			->get('user_roles')
			->result();

		return array_map(function ($row) {
			return $row->role_code;
		}, $rows);
	}
}
