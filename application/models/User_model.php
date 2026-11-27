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
			->select('id, name, email, password_hash, role')
			->where('email', $email)
			->where('is_active', 1)
			->get('users')
			->row();

		if ( ! $user || ! password_verify($password, $user->password_hash))
		{
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

	public function all()
	{
		return $this->db
			->select('id, name, email, role, is_active, created_at')
			->order_by('name', 'ASC')
			->get('users')
			->result();
	}

	public function count_all()
	{
		return (int) $this->db->count_all('users');
	}

	public function find($id)
	{
		return $this->db
			->select('id, role, is_active')
			->where('id', $id)
			->get('users')
			->row();
	}

	public function count_active_admins()
	{
		return (int) $this->db
			->where('role', 'company_admin')
			->where('is_active', 1)
			->count_all_results('users');
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
		return $this->db->insert('users', array(
			'name' => $data['name'],
			'email' => $data['email'],
			'password_hash' => password_hash($data['password'], PASSWORD_DEFAULT),
			'role' => $data['role'],
			'is_active' => 1,
			'created_at' => $now,
			'updated_at' => $now,
		));
	}
}
