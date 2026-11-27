<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class MY_Controller extends CI_Controller {

	public function __construct()
	{
		parent::__construct();

		$user_id = (int) $this->session->userdata('user_id');
		if ($user_id <= 0)
		{
			redirect('login');
		}

		$this->load->model('User_model');
		if ( ! $this->User_model->is_active($user_id))
		{
			$this->session->sess_destroy();
			redirect('login');
		}
	}

	protected function require_role($roles)
	{
		$user_roles = $this->session->userdata('user_roles');
		if ( ! is_array($user_roles) || empty(array_intersect($user_roles, $roles)))
		{
			show_error('Anda tidak memiliki izin untuk mengakses halaman ini.', 403);
		}
	}
}
