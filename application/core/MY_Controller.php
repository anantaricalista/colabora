<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class MY_Controller extends CI_Controller {

	public function __construct()
	{
		parent::__construct();

		if ( ! $this->session->userdata('user_id'))
		{
			redirect('login');
		}
	}

	protected function require_role($roles)
	{
		if ( ! in_array($this->session->userdata('user_role'), $roles, TRUE))
		{
			show_error('Anda tidak memiliki izin untuk mengakses halaman ini.', 403);
		}
	}
}
