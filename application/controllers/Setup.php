<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Setup extends CI_Controller {

	public function create_admin()
	{
		if ( ! $this->input->is_cli_request())
		{
			show_404();
		}

		$email = getenv('COLABORA_ADMIN_EMAIL');
		$name = getenv('COLABORA_ADMIN_NAME');
		$password = getenv('COLABORA_ADMIN_PASSWORD');

		if ($email === FALSE || $name === FALSE || $password === FALSE
			|| ! filter_var($email, FILTER_VALIDATE_EMAIL)
			|| strlen($email) > 190
			|| trim($name) === ''
			|| strlen($name) > 150
			|| strlen($password) < 12
			|| strlen($password) > 255)
		{
			show_error('Set COLABORA_ADMIN_EMAIL, COLABORA_ADMIN_NAME, dan kata sandi minimal 12 karakter melalui environment.', 400);
		}

		$this->load->model('User_model');
		if ($this->User_model->count_all() !== 0)
		{
			show_error('Administrator awal hanya dapat dibuat ketika belum ada akun.', 409);
		}

		$created = $this->User_model->create(array(
			'name' => trim($name),
			'email' => strtolower(trim($email)),
			'roles' => array('company_admin'),
			'password' => $password,
		));

		if ( ! $created)
		{
			show_error('Administrator awal tidak dapat dibuat.', 500);
		}

		echo "Administrator perusahaan berhasil dibuat.\n";
	}
}
