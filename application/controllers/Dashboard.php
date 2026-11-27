<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Dashboard extends MY_Controller {

	public function index()
	{
		$this->load->view('dashboard/index', array(
			'user_name' => $this->session->userdata('user_name'),
			'user_role' => $this->session->userdata('user_role'),
		));
	}
}
