<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Attendance_model extends CI_Model {

	public function record_for_date($user_id, $attendance_date)
	{
		return $this->db
			->select('id, user_id, attendance_date, timezone_code, check_in_at, check_in_source, check_in_reason, check_in_consent_at, check_in_location_blob, check_out_at, check_out_source, check_out_reason, check_out_consent_at, check_out_location_blob')
			->where('user_id', $user_id)
			->where('attendance_date', $attendance_date)
			->get('attendance_records')
			->row();
	}

	public function record($record_id)
	{
		return $this->db
			->select('attendance_records.id, attendance_records.user_id, attendance_records.attendance_date, attendance_records.timezone_code, attendance_records.check_in_at, attendance_records.check_in_source, attendance_records.check_in_reason, attendance_records.check_in_consent_at, attendance_records.check_in_location_blob, attendance_records.check_out_at, attendance_records.check_out_source, attendance_records.check_out_reason, attendance_records.check_out_consent_at, attendance_records.check_out_location_blob, users.name, employee_profiles.employee_number')
			->from('attendance_records')
			->join('users', 'users.id = attendance_records.user_id')
			->join('employee_profiles', 'employee_profiles.user_id = attendance_records.user_id')
			->where('attendance_records.id', $record_id)
			->get()
			->row();
	}

	public function recent_for_hr()
	{
		return $this->db
			->select('attendance_records.id, attendance_records.user_id, attendance_records.attendance_date, attendance_records.timezone_code, attendance_records.check_in_at, attendance_records.check_in_source, attendance_records.check_out_at, attendance_records.check_out_source, users.name, employee_profiles.employee_number')
			->from('attendance_records')
			->join('users', 'users.id = attendance_records.user_id')
			->join('employee_profiles', 'employee_profiles.user_id = attendance_records.user_id')
			->order_by('attendance_records.attendance_date', 'DESC')
			->order_by('attendance_records.check_in_at', 'DESC')
			->limit(100)
			->get()
			->result();
	}

	public function create_self_check($data)
	{
		$now = $data['now'];
		$this->db->trans_begin();

		if ($data['action'] === 'check_in')
		{
			$inserted = $this->db->insert('attendance_records', array(
				'user_id' => $data['user_id'],
				'attendance_date' => $data['attendance_date'],
				'timezone_code' => $data['timezone_code'],
				'check_in_at' => $now,
				'check_in_source' => 'self',
				'check_in_reason' => NULL,
				'check_in_consent_at' => $now,
				'check_in_photo_blob' => $data['photo_blob'],
				'check_in_location_blob' => $data['location_blob'],
				'created_at' => $now,
				'updated_at' => $now,
			));

			if ( ! $inserted)
			{
				$this->db->trans_rollback();
				return FALSE;
			}

			$record_id = (int) $this->db->insert_id();
			$audit = $this->add_audit(array(
				'attendance_record_id' => $record_id,
				'changed_by_user_id' => $data['user_id'],
				'action' => 'self_check_in',
				'field_name' => 'check_in_at',
				'old_value' => NULL,
				'new_value' => $now,
				'reason' => 'Absensi mandiri dengan bukti foto dan lokasi.',
				'timezone_code' => $data['timezone_code'],
				'created_at' => $now,
			));
			if ( ! $audit)
			{
				$this->db->trans_rollback();
				return FALSE;
			}
		}
		else
		{
			$record = $this->record_for_date($data['user_id'], $data['attendance_date']);
			if ( ! $record || $record->check_in_at === NULL || $record->check_out_at !== NULL)
			{
				$this->db->trans_rollback();
				return FALSE;
			}

			$updated = $this->db
				->where('id', $record->id)
				->where('check_out_at IS NULL', NULL, FALSE)
				->update('attendance_records', array(
					'check_out_at' => $now,
					'check_out_source' => 'self',
					'check_out_reason' => NULL,
					'check_out_consent_at' => $now,
					'check_out_photo_blob' => $data['photo_blob'],
					'check_out_location_blob' => $data['location_blob'],
					'updated_at' => $now,
				));

			if ( ! $updated || $this->db->affected_rows() !== 1)
			{
				$this->db->trans_rollback();
				return FALSE;
			}

			$audit = $this->add_audit(array(
				'attendance_record_id' => $record->id,
				'changed_by_user_id' => $data['user_id'],
				'action' => 'self_check_out',
				'field_name' => 'check_out_at',
				'old_value' => NULL,
				'new_value' => $now,
				'reason' => 'Absensi mandiri dengan bukti foto dan lokasi.',
				'timezone_code' => $data['timezone_code'],
				'created_at' => $now,
			));
			if ( ! $audit)
			{
				$this->db->trans_rollback();
				return FALSE;
			}
		}

		if ( ! $this->db->trans_status())
		{
			$this->db->trans_rollback();
			return FALSE;
		}

		return $this->db->trans_commit();
	}

	public function create_manual($data)
	{
		$this->db->trans_begin();
		$now = $data['created_at'];
		$inserted = $this->db->insert('attendance_records', array(
			'user_id' => $data['user_id'],
			'attendance_date' => $data['attendance_date'],
			'timezone_code' => $data['timezone_code'],
			'check_in_at' => $data['check_in_at'],
			'check_in_source' => 'manual',
			'check_in_reason' => $data['reason'],
			'check_out_at' => $data['check_out_at'],
			'check_out_source' => $data['check_out_at'] === NULL ? NULL : 'manual',
			'check_out_reason' => $data['check_out_at'] === NULL ? NULL : $data['reason'],
			'created_at' => $now,
			'updated_at' => $now,
		));

		if ( ! $inserted)
		{
			$this->db->trans_rollback();
			return FALSE;
		}

		$record_id = (int) $this->db->insert_id();
		$audit = $this->add_audit(array(
			'attendance_record_id' => $record_id,
			'changed_by_user_id' => $data['created_by'],
			'action' => 'manual_entry',
			'field_name' => 'attendance_times',
			'old_value' => NULL,
			'new_value' => json_encode(array(
				'check_in_at' => $data['check_in_at'],
				'check_out_at' => $data['check_out_at'],
			)),
			'reason' => $data['reason'],
			'timezone_code' => $data['timezone_code'],
			'created_at' => $now,
		));

		if ( ! $audit || ! $this->db->trans_status())
		{
			$this->db->trans_rollback();
			return FALSE;
		}

		return $this->db->trans_commit();
	}

	public function update_times($record, $data)
	{
		$changes = array();
		foreach (array('check_in_at', 'check_out_at') as $field)
		{
			$old_value = $record->$field;
			$new_value = $data[$field];
			if ($old_value !== $new_value)
			{
				$changes[$field] = array(
					'old_value' => $old_value,
					'new_value' => $new_value,
				);
			}
		}

		if (empty($changes))
		{
			return 'unchanged';
		}

		$this->db->trans_begin();
		$updates = array('updated_at' => $data['updated_at']);
		foreach ($changes as $field => $change)
		{
			$prefix = ($field === 'check_in_at') ? 'check_in' : 'check_out';
			$updates[$field] = $change['new_value'];
			$updates[$prefix.'_source'] = 'corrected';
			$updates[$prefix.'_reason'] = $data['reason'];
		}

		$updated = $this->db
			->where('id', $record->id)
			->update('attendance_records', $updates);
		if ( ! $updated)
		{
			$this->db->trans_rollback();
			return FALSE;
		}

		foreach ($changes as $field => $change)
		{
			if ( ! $this->add_audit(array(
				'attendance_record_id' => $record->id,
				'changed_by_user_id' => $data['changed_by'],
				'action' => 'correction',
				'field_name' => $field,
				'old_value' => $change['old_value'],
				'new_value' => $change['new_value'],
				'reason' => $data['reason'],
				'timezone_code' => $data['timezone_code'],
				'created_at' => $data['updated_at'],
			)))
			{
				$this->db->trans_rollback();
				return FALSE;
			}
		}

		if ( ! $this->db->trans_status())
		{
			$this->db->trans_rollback();
			return FALSE;
		}

		return $this->db->trans_commit();
	}

	public function audit_for_record($record_id)
	{
		return $this->db
			->select('attendance_audit.*, users.name AS changed_by_name')
			->from('attendance_audit')
			->join('users', 'users.id = attendance_audit.changed_by_user_id')
			->where('attendance_audit.attendance_record_id', $record_id)
			->order_by('attendance_audit.created_at', 'DESC')
			->order_by('attendance_audit.id', 'DESC')
			->get()
			->result();
	}

	public function encrypted_evidence($record_id, $field)
	{
		$allowed_fields = array('check_in_photo_blob', 'check_out_photo_blob');
		if ( ! in_array($field, $allowed_fields, TRUE))
		{
			return FALSE;
		}

		$row = $this->db
			->select($field)
			->where('id', $record_id)
			->get('attendance_records')
			->row();

		return ($row && $row->$field !== NULL) ? $row->$field : FALSE;
	}

	public function decrypt_location($encrypted_location, $record_id)
	{
		if ($encrypted_location === NULL)
		{
			return NULL;
		}

		if (strlen(config_item('encryption_key')) < 32)
		{
			show_error('Kunci enkripsi bukti absensi tidak tersedia.', 500);
		}

		$this->load->library('encryption');
		$location_json = $this->encryption->decrypt($encrypted_location);
		$location = ($location_json === FALSE) ? NULL : json_decode($location_json, TRUE);
		if ( ! is_array($location)
			|| ! isset($location['latitude'], $location['longitude'], $location['accuracy_m']))
		{
			log_message('error', 'Koordinat absensi tidak dapat didekripsi untuk catatan ID '.(int) $record_id.'.');
			show_error('Bukti lokasi absensi tidak dapat ditampilkan.', 500);
		}

		return $location;
	}

	private function add_audit($data)
	{
		return $this->db->insert('attendance_audit', array(
			'attendance_record_id' => $data['attendance_record_id'],
			'changed_by_user_id' => $data['changed_by_user_id'],
			'action' => $data['action'],
			'field_name' => $data['field_name'],
			'old_value' => $data['old_value'],
			'new_value' => $data['new_value'],
			'reason' => $data['reason'],
			'created_at' => $data['created_at'],
			'timezone_code' => $data['timezone_code'],
		));
	}
}
