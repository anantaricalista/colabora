<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Settings extends MY_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->require_role(array('company_admin'));
		$this->load->model('Company_settings_model');
		$this->load->library('form_validation');
	}

	public function index()
	{
		$this->form_validation->set_rules('timezone_code', 'Zona waktu', 'required|in_list[WIB,WITA,WIT]');

		$error = '';
		$success = '';
		$is_post = $this->input->method(TRUE) === 'POST';
		$post_shape_valid = $this->post_fields_are_strings(array('timezone_code'));
		if ($is_post && ! $post_shape_valid)
		{
			$error = 'Data pengaturan tidak valid.';
		}
		elseif ($is_post && $this->form_validation->run() === TRUE)
		{
			$updated = $this->Company_settings_model->update_timezone(
				$this->input->post('timezone_code', TRUE),
				(int) $this->session->userdata('user_id')
			);

			if ($updated)
			{
				$success = 'Pengaturan zona waktu berhasil diperbarui.';
			}
			else
			{
				$error = 'Pengaturan tidak dapat disimpan. Silakan coba lagi.';
			}
		}

		$this->load->view('settings/index', array(
			'timezone_code' => $this->Company_settings_model->timezone_code(),
			'timezones' => $this->Company_settings_model->timezone_options(),
			'error' => $error,
			'success' => $success,
		));
	}
}
