<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Auth extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->load->model('User_model');
		$this->load->library('form_validation');
	}

	public function index()
	{
		if ($this->session->userdata('user_id'))
		{
			redirect('dashboard');
		}

		redirect('login');
	}

	public function login()
	{
		if ($this->session->userdata('user_id'))
		{
			redirect('dashboard');
		}

		$this->form_validation->set_rules('email', 'Email', 'required|valid_email|max_length[190]');
		$this->form_validation->set_rules('password', 'Kata sandi', 'required');

		$error = '';
		if ($this->input->method(TRUE) === 'POST')
		{
			if ($this->form_validation->run() === TRUE)
			{
				$email = strtolower(trim($this->input->post('email', TRUE)));
				$password = $this->input->post('password', FALSE);
				$user = $this->User_model->authenticate($email, $password);

				if ($user)
				{
					$this->session->sess_regenerate(TRUE);
					$this->session->set_userdata(array(
						'user_id' => (int) $user->id,
						'user_name' => $user->name,
						'user_role' => $user->role,
					));

					redirect('dashboard');
				}

				$error = 'Email atau kata sandi tidak valid.';
			}
		}

		$this->load->view('auth/login', array('error' => $error));
	}

	public function logout()
	{
		if ($this->input->method(TRUE) !== 'POST')
		{
			show_404();
		}

		$this->session->sess_destroy();
		redirect('login');
	}
}
