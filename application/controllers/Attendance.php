<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Attendance extends MY_Controller {

	private $timezone;

	public function __construct()
	{
		parent::__construct();
		$roles = (array) $this->session->userdata('user_roles');
		if ( ! array_intersect($roles, array('employee', 'hr_payroll')))
		{
			show_error('Anda tidak memiliki izin untuk mengakses absensi.', 403);
		}

		$this->load->model(array('Attendance_model', 'Company_settings_model', 'Employee_model', 'User_model'));
		$this->load->library('form_validation');
		$this->timezone = new DateTimeZone($this->Company_settings_model->timezone_name());
	}

	public function index()
	{
		$user_id = (int) $this->session->userdata('user_id');
		$roles = (array) $this->session->userdata('user_roles');
		$is_hr = in_array('hr_payroll', $roles, TRUE);
		$can_punch = in_array('employee', $roles, TRUE) && $this->User_model->is_active_employee($user_id);
		$now = new DateTimeImmutable('now', $this->timezone);
		$today = $now->format('Y-m-d');
		$record = $can_punch ? $this->Attendance_model->record_for_date($user_id, $today) : NULL;
		if ($record)
		{
			$record->check_in_location = $this->Attendance_model->decrypt_location($record->check_in_location_blob, $record->id);
			$record->check_out_location = $this->Attendance_model->decrypt_location($record->check_out_location_blob, $record->id);
		}

		$this->load->view('attendance/index', array(
			'record' => $record,
			'recent_records' => $is_hr ? $this->Attendance_model->recent_for_hr() : array(),
			'is_hr' => $is_hr,
			'can_punch' => $can_punch,
			'timezone_code' => $this->Company_settings_model->timezone_code(),
			'today' => $today,
			'success' => $this->session->flashdata('success'),
			'error' => $this->session->flashdata('error'),
		));
	}

	public function punch()
	{
		$this->require_role(array('employee'));
		$user_id = (int) $this->session->userdata('user_id');
		if ( ! $this->User_model->is_active_employee($user_id))
		{
			show_error('Profil karyawan belum aktif. Hubungi HRD.', 403);
		}

		$this->form_validation->set_rules('action', 'Tindakan absensi', 'required|in_list[check_in,check_out]');
		$this->form_validation->set_rules('latitude', 'Latitude', 'required|callback_valid_coordinates');
		$this->form_validation->set_rules('longitude', 'Longitude', 'required|callback_valid_coordinates');
		$this->form_validation->set_rules('accuracy_m', 'Akurasi lokasi', 'required|callback_valid_accuracy');
		$this->form_validation->set_rules('photo_data', 'Foto kamera', 'required|callback_valid_photo_data');
		$this->form_validation->set_rules('evidence_consent', 'Persetujuan bukti absensi', 'required|in_list[1]');

		if ($this->input->method(TRUE) !== 'POST')
		{
			show_404();
		}
		if ( ! $this->post_fields_are_strings(array(
			'action', 'latitude', 'longitude', 'accuracy_m', 'photo_data', 'evidence_consent',
		)))
		{
			$this->session->set_flashdata('error', 'Data atau persetujuan bukti absensi tidak valid.');
			redirect('attendance');
		}
		if ($this->form_validation->run() !== TRUE)
		{
			$this->session->set_flashdata('error', 'Data atau persetujuan bukti absensi tidak valid. Silakan coba kembali atau hubungi HRD.');
			redirect('attendance');
		}

		$action = $this->input->post('action', TRUE);
		$today = new DateTimeImmutable('now', $this->timezone);
		$attendance_date = $today->format('Y-m-d');
		$record = $this->Attendance_model->record_for_date($user_id, $attendance_date);
		if (($action === 'check_in' && $record)
			|| ($action === 'check_out' && ( ! $record || $record->check_in_at === NULL || $record->check_out_at !== NULL)))
		{
			$this->session->set_flashdata('error', 'Tindakan absensi tidak sesuai dengan status hari ini.');
			redirect('attendance');
		}

		if (strlen(config_item('encryption_key')) < 32)
		{
			log_message('error', 'Kunci enkripsi aplikasi belum dikonfigurasi untuk bukti absensi.');
			$this->session->set_flashdata('error', 'Bukti absensi belum dapat disimpan. Hubungi Administrator.');
			redirect('attendance');
		}

		$this->load->library('encryption');
		$image = $this->decoded_photo_data($this->input->post('photo_data', FALSE));
		if ($image === FALSE)
		{
			$this->session->set_flashdata('error', 'Foto tidak valid. Silakan coba kembali atau hubungi HRD.');
			redirect('attendance');
		}

		$encrypted_image = $this->encryption->encrypt($image);
		if ($encrypted_image === FALSE)
		{
			log_message('error', 'Bukti foto absensi gagal dienkripsi untuk pengguna ID '.$user_id.'.');
			$this->session->set_flashdata('error', 'Bukti absensi tidak dapat disimpan. Hubungi Administrator.');
			redirect('attendance');
		}

		$location_blob = $this->encryption->encrypt(json_encode(array(
			'latitude' => (string) $this->input->post('latitude', TRUE),
			'longitude' => (string) $this->input->post('longitude', TRUE),
			'accuracy_m' => (string) $this->input->post('accuracy_m', TRUE),
		)));
		if ($location_blob === FALSE)
		{
			log_message('error', 'Koordinat absensi gagal dienkripsi untuk pengguna ID '.$user_id.'.');
			$this->session->set_flashdata('error', 'Bukti lokasi tidak dapat disimpan. Hubungi Administrator.');
			redirect('attendance');
		}

		$now = new DateTimeImmutable('now', $this->timezone);
		$saved = $this->Attendance_model->create_self_check(array(
			'user_id' => $user_id,
			'action' => $action,
			'attendance_date' => $attendance_date,
			'timezone_code' => $this->Company_settings_model->timezone_code(),
			'now' => $now->format('Y-m-d H:i:s'),
			'photo_blob' => $encrypted_image,
			'location_blob' => $location_blob,
		));

		if ( ! $saved)
		{
			$this->session->set_flashdata('error', 'Absensi tidak dapat disimpan. Periksa status hari ini atau hubungi HRD.');
			redirect('attendance');
		}

		$this->session->set_flashdata('success', $action === 'check_in' ? 'Check-in berhasil dicatat.' : 'Check-out berhasil dicatat.');
		redirect('attendance');
	}

	public function manual()
	{
		$this->require_role(array('hr_payroll'));
		$employees = $this->User_model->active_employees();
		$this->form_validation->set_rules('user_id', 'Karyawan', 'required|integer');
		$this->form_validation->set_rules('attendance_date', 'Tanggal absensi', 'required|callback_valid_date');
		$this->form_validation->set_rules('check_in_time', 'Waktu check-in', 'required|callback_valid_time');
		$this->form_validation->set_rules('check_out_time', 'Waktu check-out', 'callback_valid_optional_time');
		$this->form_validation->set_rules('reason', 'Alasan pencatatan manual', 'required|max_length[2000]');

		$error = '';
		$is_post = $this->input->method(TRUE) === 'POST';
		$post_shape_valid = $this->post_fields_are_strings(
			array('user_id', 'attendance_date', 'check_in_time', 'check_out_time', 'reason'),
			array('check_out_time')
		);
		if ($is_post && ! $post_shape_valid)
		{
			$error = 'Data pengecualian absensi tidak valid.';
		}
		elseif ($is_post && $this->form_validation->run() === TRUE)
		{
			$user_id = (int) $this->input->post('user_id');
			$employee_ids = array_map(function ($employee) {
				return (int) $employee->id;
			}, $employees);
			$check_in = $this->local_datetime(
				$this->input->post('attendance_date', TRUE),
				$this->input->post('check_in_time', TRUE)
			);
			$check_out_time = $this->input->post('check_out_time', TRUE);
			$check_out = ($check_out_time === NULL || $check_out_time === '')
				? NULL
				: $this->local_datetime($this->input->post('attendance_date', TRUE), $check_out_time);

			if ( ! in_array($user_id, $employee_ids, TRUE))
			{
				$error = 'Pilih karyawan yang memiliki profil aktif.';
			}
			elseif ($check_out !== NULL && $check_out < $check_in)
			{
				$error = 'Waktu check-out tidak boleh lebih awal daripada check-in.';
			}
			else
			{
				$now = new DateTimeImmutable('now', $this->timezone);
				$created = $this->Attendance_model->create_manual(array(
					'user_id' => $user_id,
					'attendance_date' => $this->input->post('attendance_date', TRUE),
					'timezone_code' => $this->Company_settings_model->timezone_code(),
					'check_in_at' => $check_in,
					'check_out_at' => $check_out,
					'created_by' => (int) $this->session->userdata('user_id'),
					'created_at' => $now->format('Y-m-d H:i:s'),
					'reason' => trim($this->input->post('reason', TRUE)),
				));

				if ($created)
				{
					$this->session->set_flashdata('success', 'Pengecualian absensi berhasil dicatat dengan alasan dan jejak audit.');
					redirect('attendance');
				}

				$error = 'Pencatatan tidak tersimpan. Periksa apakah absensi untuk karyawan dan tanggal itu sudah ada.';
			}
		}

		$this->load->view('attendance/manual', array(
			'employees' => $employees,
			'error' => $error,
			'timezone_code' => $this->Company_settings_model->timezone_code(),
		));
	}

	public function correct($record_id = NULL)
	{
		$this->require_role(array('hr_payroll'));
		if ($record_id === NULL || ! ctype_digit((string) $record_id))
		{
			show_404();
		}

		$record = $this->Attendance_model->record((int) $record_id);
		if ( ! $record)
		{
			show_404();
		}
		$record->check_in_location = $this->Attendance_model->decrypt_location($record->check_in_location_blob, $record->id);
		$record->check_out_location = $this->Attendance_model->decrypt_location($record->check_out_location_blob, $record->id);

		$this->form_validation->set_rules('check_in_time', 'Waktu check-in', 'required|callback_valid_time');
		$this->form_validation->set_rules('check_out_time', 'Waktu check-out', 'callback_valid_optional_time');
		$this->form_validation->set_rules('reason', 'Alasan koreksi', 'required|max_length[2000]');

		$error = '';
		$is_post = $this->input->method(TRUE) === 'POST';
		$post_shape_valid = $this->post_fields_are_strings(
			array('check_in_time', 'check_out_time', 'reason'),
			array('check_out_time')
		);
		if ($is_post && ! $post_shape_valid)
		{
			$error = 'Data koreksi absensi tidak valid.';
		}
		elseif ($is_post && $this->form_validation->run() === TRUE)
		{
			$check_in = $this->local_datetime($record->attendance_date, $this->input->post('check_in_time', TRUE));
			$check_out_time = $this->input->post('check_out_time', TRUE);
			$check_out = ($check_out_time === NULL || $check_out_time === '')
				? NULL
				: $this->local_datetime($record->attendance_date, $check_out_time);

			if ($check_out !== NULL && $check_out < $check_in)
			{
				$error = 'Waktu check-out tidak boleh lebih awal daripada check-in.';
			}
			else
			{
				$now = new DateTimeImmutable('now', $this->timezone);
				$updated = $this->Attendance_model->update_times($record, array(
					'check_in_at' => $check_in,
					'check_out_at' => $check_out,
					'changed_by' => (int) $this->session->userdata('user_id'),
					'updated_at' => $now->format('Y-m-d H:i:s'),
					'timezone_code' => $record->timezone_code,
					'reason' => trim($this->input->post('reason', TRUE)),
				));

				if ($updated === 'unchanged')
				{
					$error = 'Tidak ada perubahan waktu untuk dicatat.';
				}
				elseif ($updated)
				{
					$this->session->set_flashdata('success', 'Koreksi disimpan dan dicatat dalam riwayat audit.');
					redirect('attendance');
				}
				else
				{
					$error = 'Koreksi tidak dapat disimpan. Silakan coba lagi.';
				}
			}
		}

		$this->load->view('attendance/correct', array(
			'record' => $record,
			'error' => $error,
			'audit' => $this->Attendance_model->audit_for_record($record->id),
		));
	}

	public function audit($record_id = NULL)
	{
		$this->require_role(array('hr_payroll'));
		if ($record_id === NULL || ! ctype_digit((string) $record_id))
		{
			show_404();
		}

		$record = $this->Attendance_model->record((int) $record_id);
		if ( ! $record)
		{
			show_404();
		}

		$this->load->view('attendance/audit', array(
			'record' => $record,
			'audit' => $this->Attendance_model->audit_for_record($record->id),
		));
	}

	public function evidence($record_id = NULL, $punch = NULL)
	{
		if ($record_id === NULL || ! ctype_digit((string) $record_id)
			|| ! in_array($punch, array('check-in', 'check-out'), TRUE))
		{
			show_404();
		}

		$record = $this->Attendance_model->record((int) $record_id);
		if ( ! $record)
		{
			show_404();
		}

		$roles = (array) $this->session->userdata('user_roles');
		$is_hr = in_array('hr_payroll', $roles, TRUE);
		$is_owner = (int) $record->user_id === (int) $this->session->userdata('user_id')
			&& in_array('employee', $roles, TRUE);
		if ( ! $is_hr && ! $is_owner)
		{
			show_404();
		}

		$field = $punch === 'check-in' ? 'check_in_photo_blob' : 'check_out_photo_blob';
		$ciphertext = $this->Attendance_model->encrypted_evidence((int) $record_id, $field);
		if ($ciphertext === FALSE || strlen(config_item('encryption_key')) < 32)
		{
			show_404();
		}

		$this->load->library('encryption');
		$image = $this->encryption->decrypt($ciphertext);
		if ($image === FALSE || substr($image, 0, 3) !== "\xFF\xD8\xFF")
		{
			log_message('error', 'Bukti absensi tidak dapat didekripsi untuk catatan ID '.(int) $record_id.'.');
			show_error('Bukti absensi tidak dapat ditampilkan.', 500);
		}

		$this->output
			->set_content_type('image/jpeg')
			->set_header('Cache-Control: no-store, private, max-age=0')
			->set_header('Pragma: no-cache')
			->set_header('X-Content-Type-Options: nosniff')
			->set_output($image);
	}

	public function valid_coordinates($value)
	{
		if ( ! is_string($value) || ! is_numeric($value) || ! is_finite((float) $value))
		{
			$this->form_validation->set_message('valid_coordinates', 'Data koordinat tidak valid.');
			return FALSE;
		}

		$latitude_value = $this->input->post('latitude', TRUE);
		$longitude_value = $this->input->post('longitude', TRUE);
		if ( ! is_string($latitude_value) || ! is_numeric($latitude_value)
			|| ! is_string($longitude_value) || ! is_numeric($longitude_value))
		{
			$this->form_validation->set_message('valid_coordinates', 'Data koordinat tidak valid.');
			return FALSE;
		}

		$latitude = (float) $latitude_value;
		$longitude = (float) $longitude_value;
		$valid = $latitude >= -90 && $latitude <= 90 && $longitude >= -180 && $longitude <= 180;
		if ( ! $valid)
		{
			$this->form_validation->set_message('valid_coordinates', 'Nilai koordinat berada di luar rentang yang valid.');
		}

		return $valid;
	}

	public function valid_accuracy($value)
	{
		$valid = is_string($value) && is_numeric($value) && is_finite((float) $value) && (float) $value > 0;
		if ( ! $valid)
		{
			$this->form_validation->set_message('valid_accuracy', 'Akurasi lokasi tidak valid.');
		}

		return $valid;
	}

	public function valid_photo_data($value)
	{
		$valid = is_string($value) && $this->decoded_photo_data($value) !== FALSE;
		if ( ! $valid)
		{
			$this->form_validation->set_message('valid_photo_data', 'Foto kamera tidak valid atau terlalu besar.');
		}

		return $valid;
	}

	public function valid_date($value)
	{
		$date = is_string($value) && preg_match('/^\d{4}-\d{2}-\d{2}$/D', $value)
			? DateTime::createFromFormat('!Y-m-d', $value)
			: FALSE;
		$valid = $date && $date->format('Y-m-d') === $value;
		if ( ! $valid)
		{
			$this->form_validation->set_message('valid_date', 'Tanggal harus menggunakan format YYYY-MM-DD.');
		}

		return $valid;
	}

	public function valid_time($value)
	{
		$time = is_string($value) && preg_match('/^\d{2}:\d{2}$/D', $value)
			? DateTime::createFromFormat('!H:i', $value)
			: FALSE;
		$valid = $time && $time->format('H:i') === $value;
		if ( ! $valid)
		{
			$this->form_validation->set_message('valid_time', 'Waktu harus menggunakan format jam dan menit.');
		}

		return $valid;
	}

	public function valid_optional_time($value)
	{
		return $value === NULL || $value === '' || $this->valid_time($value);
	}

	private function decoded_photo_data($data_url)
	{
		if ( ! is_string($data_url)
			|| ! preg_match('#^data:image/jpeg;base64,([A-Za-z0-9+/]+={0,2})$#D', $data_url, $matches)
			|| strlen($matches[1]) > 700000)
		{
			return FALSE;
		}

		$image = base64_decode($matches[1], TRUE);
		if ($image === FALSE || strlen($image) > 512000)
		{
			return FALSE;
		}

		$info = @getimagesizefromstring($image);
		if ( ! $info || $info[2] !== IMAGETYPE_JPEG || $info[0] > 1280 || $info[1] > 1280)
		{
			return FALSE;
		}

		return $image;
	}

	private function local_datetime($date, $time)
	{
		$value = DateTimeImmutable::createFromFormat('!Y-m-d H:i', $date.' '.$time, $this->timezone);
		return $value ? $value->format('Y-m-d H:i:s') : NULL;
	}

}
