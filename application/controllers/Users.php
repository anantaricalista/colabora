<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Users extends MY_Controller {

	private $roles = array(
		'company_admin' => 'Administrator Perusahaan',
		'hr_payroll' => 'HRD / Payroll',
		'finance' => 'Finance',
		'employee' => 'Karyawan',
	);

	public function __construct()
	{
		parent::__construct();
		$this->require_role(array('company_admin'));
		$this->load->model('User_model');
		$this->load->library('form_validation');
	}

	public function index()
	{
		$this->load->view('users/index', array(
			'users' => $this->User_model->all(),
			'roles' => $this->roles,
			'error' => '',
		));
	}

	public function create()
	{
		$this->form_validation->set_rules('name', 'Nama', 'required|max_length[150]');
		$this->form_validation->set_rules('email', 'Email', 'required|valid_email|max_length[190]|is_unique[users.email]');
		$this->form_validation->set_rules('roles', 'Peran', 'required');
		$this->form_validation->set_rules('password', 'Kata sandi', 'required|min_length[12]|max_length[255]');

		$error = '';
		$roles_error = '';
		$submitted_roles = $this->input->post('roles');
		$roles_valid = $this->valid_roles($submitted_roles);
		if ($this->input->method(TRUE) === 'POST' && $this->form_validation->run() === TRUE && $roles_valid)
		{
			$created = $this->User_model->create(array(
				'name' => trim($this->input->post('name', TRUE)),
				'email' => strtolower(trim($this->input->post('email', TRUE))),
				'roles' => $submitted_roles,
				'password' => $this->input->post('password', FALSE),
			));

			if ($created)
			{
				$this->session->set_flashdata('success', 'Akun pengguna berhasil dibuat.');
				redirect('users');
			}

			$error = 'Akun tidak dapat dibuat. Silakan periksa data dan coba lagi.';
		}
		elseif ($this->input->method(TRUE) === 'POST' && ! $roles_valid)
		{
			$roles_error = 'Pilih setidaknya satu peran yang tersedia.';
		}

		$this->load->view('users/create', array(
			'roles' => $this->roles,
			'error' => $error,
			'roles_error' => $roles_error,
		));
	}

	public function toggle($id = NULL)
	{
		if ($this->input->method(TRUE) !== 'POST' || $id === NULL || ! ctype_digit((string) $id))
		{
			show_404();
		}

		$user = $this->User_model->find((int) $id);
		if ( ! $user)
		{
			show_404();
		}

		if ((int) $user->id === (int) $this->session->userdata('user_id'))
		{
			show_error('Administrator tidak dapat menonaktifkan akunnya sendiri.', 403);
		}

		if (in_array('company_admin', $user->roles, TRUE) && (int) $user->is_active === 1
			&& $this->User_model->count_active_admins() <= 1)
		{
			$this->session->set_flashdata('error', 'Akun administrator aktif terakhir tidak dapat dinonaktifkan.');
			redirect('users');
		}

		if ( ! $this->User_model->set_active($user->id, ! (int) $user->is_active))
		{
			show_error('Status akun tidak dapat diperbarui.', 500);
		}

		$this->session->set_flashdata('success', 'Status akun pengguna berhasil diperbarui.');
		redirect('users');
	}

	private function valid_roles($roles)
	{
		if ( ! is_array($roles) || empty($roles))
		{
			return FALSE;
		}

		$seen = array();
		foreach ($roles as $role)
		{
			if ( ! is_string($role) || ! isset($this->roles[$role]) || isset($seen[$role]))
			{
				return FALSE;
			}

			$seen[$role] = TRUE;
		}

		return TRUE;
	}
}
