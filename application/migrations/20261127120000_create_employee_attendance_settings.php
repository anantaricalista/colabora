<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Create_employee_attendance_settings extends CI_Migration {

	public function up()
	{
		$this->dbforge->add_field(array(
			'id' => array(
				'type' => 'TINYINT',
				'constraint' => 1,
			),
			'timezone_code' => array(
				'type' => 'VARCHAR',
				'constraint' => 8,
				'default' => 'WIB',
			),
			'updated_by' => array(
				'type' => 'INT',
				'unsigned' => TRUE,
				'null' => TRUE,
			),
			'updated_at' => array(
				'type' => 'DATETIME',
				'null' => TRUE,
			),
		));
		$this->dbforge->add_key('id', TRUE);
		if ( ! $this->dbforge->create_table('company_settings', TRUE, array(
			'ENGINE' => 'InnoDB',
			'DEFAULT CHARSET' => 'utf8mb4',
			'COLLATE' => 'utf8mb4_unicode_ci',
		)))
		{
			show_error('Tabel pengaturan perusahaan tidak dapat dibuat.');
		}

		if ($this->db->count_all('company_settings') === 0
			&& ! $this->db->insert('company_settings', array('id' => 1, 'timezone_code' => 'WIB')))
		{
			show_error('Zona waktu awal perusahaan tidak dapat disimpan.');
		}

		$this->dbforge->add_field(array(
			'user_id' => array(
				'type' => 'INT',
				'unsigned' => TRUE,
			),
			'employee_number' => array(
				'type' => 'VARCHAR',
				'constraint' => 64,
			),
			'job_title' => array(
				'type' => 'VARCHAR',
				'constraint' => 120,
			),
			'department' => array(
				'type' => 'VARCHAR',
				'constraint' => 120,
				'null' => TRUE,
			),
			'start_date' => array(
				'type' => 'DATE',
			),
			'employment_status' => array(
				'type' => 'VARCHAR',
				'constraint' => 16,
				'default' => 'active',
			),
			'created_at' => array(
				'type' => 'DATETIME',
			),
			'updated_at' => array(
				'type' => 'DATETIME',
			),
		));
		$this->dbforge->add_key('user_id', TRUE);
		if ( ! $this->dbforge->create_table('employee_profiles', TRUE, array(
			'ENGINE' => 'InnoDB',
			'DEFAULT CHARSET' => 'utf8mb4',
			'COLLATE' => 'utf8mb4_unicode_ci',
		)))
		{
			show_error('Tabel profil karyawan tidak dapat dibuat.');
		}

		$profiles = $this->db->escape_identifiers($this->db->dbprefix('employee_profiles'));
		if ($this->db->query(
			'ALTER TABLE '.$profiles.' ADD UNIQUE KEY `employee_number_unique` (`employee_number`)'
		) === FALSE)
		{
			show_error('Indeks unik nomor karyawan tidak dapat dibuat.');
		}

		$this->dbforge->add_field(array(
			'id' => array(
				'type' => 'BIGINT',
				'unsigned' => TRUE,
				'auto_increment' => TRUE,
			),
			'user_id' => array(
				'type' => 'INT',
				'unsigned' => TRUE,
			),
			'attendance_date' => array(
				'type' => 'DATE',
			),
			'timezone_code' => array(
				'type' => 'VARCHAR',
				'constraint' => 8,
				'default' => 'WIB',
			),
			'check_in_at' => array(
				'type' => 'DATETIME',
				'null' => TRUE,
			),
			'check_in_source' => array(
				'type' => 'VARCHAR',
				'constraint' => 16,
				'null' => TRUE,
			),
			'check_in_reason' => array(
				'type' => 'TEXT',
				'null' => TRUE,
			),
			'check_in_consent_at' => array(
				'type' => 'DATETIME',
				'null' => TRUE,
			),
			'check_in_photo_blob' => array(
				'type' => 'MEDIUMBLOB',
				'null' => TRUE,
			),
			'check_in_location_blob' => array(
				'type' => 'MEDIUMBLOB',
				'null' => TRUE,
			),
			'check_out_at' => array(
				'type' => 'DATETIME',
				'null' => TRUE,
			),
			'check_out_source' => array(
				'type' => 'VARCHAR',
				'constraint' => 16,
				'null' => TRUE,
			),
			'check_out_reason' => array(
				'type' => 'TEXT',
				'null' => TRUE,
			),
			'check_out_consent_at' => array(
				'type' => 'DATETIME',
				'null' => TRUE,
			),
			'check_out_photo_blob' => array(
				'type' => 'MEDIUMBLOB',
				'null' => TRUE,
			),
			'check_out_location_blob' => array(
				'type' => 'MEDIUMBLOB',
				'null' => TRUE,
			),
			'created_at' => array(
				'type' => 'DATETIME',
			),
			'updated_at' => array(
				'type' => 'DATETIME',
			),
		));
		$this->dbforge->add_key('id', TRUE);
		if ( ! $this->dbforge->create_table('attendance_records', TRUE, array(
			'ENGINE' => 'InnoDB',
			'DEFAULT CHARSET' => 'utf8mb4',
			'COLLATE' => 'utf8mb4_unicode_ci',
		)))
		{
			show_error('Tabel absensi tidak dapat dibuat.');
		}

		$attendance = $this->db->escape_identifiers($this->db->dbprefix('attendance_records'));
		if ($this->db->query(
			'ALTER TABLE '.$attendance.' ADD UNIQUE KEY `attendance_user_date_unique` (`user_id`, `attendance_date`)'
		) === FALSE)
		{
			show_error('Indeks unik tanggal absensi tidak dapat dibuat.');
		}

		$this->dbforge->add_field(array(
			'id' => array(
				'type' => 'BIGINT',
				'unsigned' => TRUE,
				'auto_increment' => TRUE,
			),
			'attendance_record_id' => array(
				'type' => 'BIGINT',
				'unsigned' => TRUE,
			),
			'changed_by_user_id' => array(
				'type' => 'INT',
				'unsigned' => TRUE,
			),
			'action' => array(
				'type' => 'VARCHAR',
				'constraint' => 24,
			),
			'field_name' => array(
				'type' => 'VARCHAR',
				'constraint' => 32,
			),
			'old_value' => array(
				'type' => 'TEXT',
				'null' => TRUE,
			),
			'new_value' => array(
				'type' => 'TEXT',
				'null' => TRUE,
			),
			'reason' => array(
				'type' => 'TEXT',
			),
			'timezone_code' => array(
				'type' => 'VARCHAR',
				'constraint' => 8,
			),
			'created_at' => array(
				'type' => 'DATETIME',
			),
		));
		$this->dbforge->add_key('id', TRUE);
		$this->dbforge->add_key(array('attendance_record_id', 'created_at'));
		if ( ! $this->dbforge->create_table('attendance_audit', TRUE, array(
			'ENGINE' => 'InnoDB',
			'DEFAULT CHARSET' => 'utf8mb4',
			'COLLATE' => 'utf8mb4_unicode_ci',
		)))
		{
			show_error('Tabel audit absensi tidak dapat dibuat.');
		}
	}

	public function down()
	{
		$this->dbforge->drop_table('attendance_audit', TRUE);
		$this->dbforge->drop_table('attendance_records', TRUE);
		$this->dbforge->drop_table('employee_profiles', TRUE);
		$this->dbforge->drop_table('company_settings', TRUE);
	}
}
