<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migrate extends CI_Controller {

	public function latest()
	{
		if ( ! $this->input->is_cli_request())
		{
			show_404();
		}

		$this->load->library('migration');
		$result = $this->migration->latest();

		if ($result === FALSE)
		{
			show_error($this->migration->error_string());
		}

		echo "Migrasi database berhasil dijalankan.\n";
	}
}
