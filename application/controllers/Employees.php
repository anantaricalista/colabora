<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Employees extends MY_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->require_role(array('hr_payroll'));
		$this->load->model(array('Employee_model', 'User_model'));
		$this->load->library('form_validation');
	}

	public function index()
	{
		$this->load->view('employees/index', array(
			'employees' => $this->Employee_model->all(),
			'accounts' => $this->User_model->unprofiled_employee_accounts(),
			'success' => $this->session->flashdata('success'),
			'error' => $this->session->flashdata('error'),
		));
	}

	public function create()
	{
		$accounts = $this->User_model->unprofiled_employee_accounts();
		$this->form_validation->set_rules('user_id', 'Akun karyawan', 'required|integer');
		$this->form_validation->set_rules('employee_number', 'Nomor karyawan', 'required|max_length[64]');
		$this->form_validation->set_rules('job_title', 'Jabatan', 'required|max_length[120]');
		$this->form_validation->set_rules('department', 'Departemen', 'max_length[120]');
		$this->form_validation->set_rules('start_date', 'Tanggal mulai', 'required|callback_valid_employee_date');

		$error = '';
		$is_post = $this->input->method(TRUE) === 'POST';
		$post_shape_valid = $this->post_fields_are_strings(
			array('user_id', 'employee_number', 'job_title', 'department', 'start_date'),
			array('department')
		);
		if ($is_post && ! $post_shape_valid)
		{
			$error = 'Data profil karyawan tidak valid.';
		}
		elseif ($is_post && $this->form_validation->run() === TRUE)
		{
			$user_id = (int) $this->input->post('user_id');
			$account_ids = array_map(function ($account) {
				return (int) $account->id;
			}, $accounts);

			if ( ! in_array($user_id, $account_ids, TRUE))
			{
				$error = 'Pilih akun aktif yang memiliki peran Karyawan dan belum mempunyai profil.';
			}
			elseif ($this->Employee_model->employee_number_exists(trim($this->input->post('employee_number', TRUE))))
			{
				$error = 'Nomor karyawan sudah digunakan.';
			}
			else
			{
				$created = $this->Employee_model->create(array(
					'user_id' => $user_id,
					'employee_number' => trim($this->input->post('employee_number', TRUE)),
					'job_title' => trim($this->input->post('job_title', TRUE)),
					'department' => trim($this->input->post('department', TRUE)),
					'start_date' => $this->input->post('start_date', TRUE),
				));

				if ($created)
				{
					$this->session->set_flashdata('success', 'Profil karyawan berhasil dibuat.');
					redirect('employees');
				}

				$error = 'Profil karyawan tidak dapat dibuat. Silakan periksa data dan coba lagi.';
			}
		}

		$this->load->view('employees/create', array(
			'accounts' => $accounts,
			'error' => $error,
		));
	}

	public function edit($user_id = NULL)
	{
		if ($user_id === NULL || ! ctype_digit((string) $user_id))
		{
			show_404();
		}

		$employee = $this->Employee_model->find((int) $user_id);
		if ( ! $employee)
		{
			show_404();
		}

		$this->form_validation->set_rules('employee_number', 'Nomor karyawan', 'required|max_length[64]');
		$this->form_validation->set_rules('job_title', 'Jabatan', 'required|max_length[120]');
		$this->form_validation->set_rules('department', 'Departemen', 'max_length[120]');
		$this->form_validation->set_rules('start_date', 'Tanggal mulai', 'required|callback_valid_employee_date');
		$this->form_validation->set_rules('employment_status', 'Status kerja', 'required|in_list[active,inactive]');

		$error = '';
		$is_post = $this->input->method(TRUE) === 'POST';
		$post_shape_valid = $this->post_fields_are_strings(
			array('employee_number', 'job_title', 'department', 'start_date', 'employment_status'),
			array('department')
		);
		if ($is_post && ! $post_shape_valid)
		{
			$error = 'Data profil karyawan tidak valid.';
		}
		elseif ($is_post && $this->form_validation->run() === TRUE)
		{
			$employee_number = trim($this->input->post('employee_number', TRUE));
			if ($this->Employee_model->employee_number_exists($employee_number, (int) $employee->user_id))
			{
				$error = 'Nomor karyawan sudah digunakan.';
			}
			else
			{
				$updated = $this->Employee_model->update((int) $employee->user_id, array(
					'employee_number' => $employee_number,
					'job_title' => trim($this->input->post('job_title', TRUE)),
					'department' => trim($this->input->post('department', TRUE)),
					'start_date' => $this->input->post('start_date', TRUE),
					'employment_status' => $this->input->post('employment_status', TRUE),
				));

				if ($updated)
				{
					$this->session->set_flashdata('success', 'Profil karyawan berhasil diperbarui.');
					redirect('employees');
				}

				$error = 'Profil karyawan tidak dapat diperbarui.';
			}
		}

		$this->load->view('employees/edit', array(
			'employee' => $employee,
			'error' => $error,
		));
	}

	public function valid_employee_date($value)
	{
		if ( ! is_string($value))
		{
			$this->form_validation->set_message('valid_employee_date', 'Tanggal harus menggunakan format YYYY-MM-DD.');
			return FALSE;
		}

		$date = preg_match('/^\d{4}-\d{2}-\d{2}$/D', $value)
			? DateTime::createFromFormat('!Y-m-d', $value)
			: FALSE;
		$valid = $date && $date->format('Y-m-d') === $value;
		if ( ! $valid)
		{
			$this->form_validation->set_message('valid_employee_date', 'Tanggal harus menggunakan format YYYY-MM-DD.');
		}

		return $valid;
	}
}
