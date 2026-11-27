<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Company_settings_model extends CI_Model {

	private $timezones = array(
		'WIB' => 'Asia/Jakarta',
		'WITA' => 'Asia/Makassar',
		'WIT' => 'Asia/Jayapura',
	);

	public function timezone_code()
	{
		$settings = $this->db
			->where('id', 1)
			->get('company_settings')
			->row();

		if ( ! $settings || ! isset($this->timezones[$settings->timezone_code]))
		{
			show_error('Pengaturan zona waktu perusahaan tidak tersedia atau tidak valid.', 500);
		}

		return $settings->timezone_code;
	}

	public function timezone_name($code = NULL)
	{
		$code = ($code === NULL) ? $this->timezone_code() : $code;
		if ( ! isset($this->timezones[$code]))
		{
			show_error('Zona waktu perusahaan tidak valid.', 500);
		}

		return $this->timezones[$code];
	}

	public function timezone_options()
	{
		return array(
			'WIB' => 'WIB (Asia/Jakarta)',
			'WITA' => 'WITA (Asia/Makassar)',
			'WIT' => 'WIT (Asia/Jayapura)',
		);
	}

	public function update_timezone($code, $user_id)
	{
		if ( ! isset($this->timezones[$code]))
		{
			return FALSE;
		}

		return $this->db
			->where('id', 1)
			->update('company_settings', array(
				'timezone_code' => $code,
				'updated_by' => $user_id,
				'updated_at' => date('Y-m-d H:i:s'),
			));
	}
}
