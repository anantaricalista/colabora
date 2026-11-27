<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Add_multiple_user_roles extends CI_Migration {

	private $role_names = array(
		'company_admin' => 'Administrator Perusahaan',
		'hr_payroll' => 'HRD / Payroll',
		'finance' => 'Finance',
		'employee' => 'Karyawan',
	);

	public function up()
	{
		$this->dbforge->add_field(array(
			'code' => array(
				'type' => 'VARCHAR',
				'constraint' => 32,
			),
			'name' => array(
				'type' => 'VARCHAR',
				'constraint' => 100,
			),
		));
		$this->dbforge->add_key('code', TRUE);
		if ( ! $this->dbforge->create_table('roles', TRUE, array(
			'ENGINE' => 'InnoDB',
			'DEFAULT CHARSET' => 'utf8mb4',
			'COLLATE' => 'utf8mb4_unicode_ci',
		)))
		{
			show_error('Tabel peran tidak dapat dibuat.');
		}

		$this->dbforge->add_field(array(
			'user_id' => array(
				'type' => 'INT',
				'unsigned' => TRUE,
			),
			'role_code' => array(
				'type' => 'VARCHAR',
				'constraint' => 32,
			),
		));
		$this->dbforge->add_key(array('user_id', 'role_code'), TRUE);
		$this->dbforge->add_key('role_code');
		if ( ! $this->dbforge->create_table('user_roles', TRUE, array(
			'ENGINE' => 'InnoDB',
			'DEFAULT CHARSET' => 'utf8mb4',
			'COLLATE' => 'utf8mb4_unicode_ci',
		)))
		{
			show_error('Tabel peran pengguna tidak dapat dibuat.');
		}

		foreach ($this->role_names as $code => $name)
		{
			$exists = $this->db->where('code', $code)->count_all_results('roles');
			if ($exists === 0 && ! $this->db->insert('roles', array('code' => $code, 'name' => $name)))
			{
				show_error('Data peran awal tidak dapat disimpan.');
			}
		}

		$users_table = $this->db->escape_identifiers($this->db->dbprefix('users'));
		$user_roles_table = $this->db->escape_identifiers($this->db->dbprefix('user_roles'));
		$roles_table = $this->db->escape_identifiers($this->db->dbprefix('roles'));
		if ($this->db->field_exists('role', 'users')
			&& $this->db->query(
				'INSERT IGNORE INTO '.$user_roles_table.' (user_id, role_code) '
				.'SELECT users.id, users.role FROM '.$users_table.' AS users '
				.'INNER JOIN '.$roles_table.' AS roles ON roles.code = users.role'
			) === FALSE)
		{
			show_error('Peran akun yang sudah ada tidak dapat dipindahkan.');
		}

		if ($this->db->field_exists('role', 'users') && ! $this->dbforge->drop_column('users', 'role'))
		{
			show_error('Kolom peran lama tidak dapat dihapus.');
		}
	}

	public function down()
	{
		if ( ! $this->db->field_exists('role', 'users')
			&& ! $this->dbforge->add_column('users', array(
				'role' => array(
					'type' => 'VARCHAR',
					'constraint' => 32,
					'default' => 'employee',
				),
			), 'email'))
		{
			show_error('Kolom peran lama tidak dapat dipulihkan.');
		}

		if ($this->db->table_exists('user_roles'))
		{
			$users_table = $this->db->escape_identifiers($this->db->dbprefix('users'));
			$user_roles_table = $this->db->escape_identifiers($this->db->dbprefix('user_roles'));
			$this->db->query(
				'UPDATE '.$users_table.' AS users '
				.'INNER JOIN (SELECT user_id, MIN(role_code) AS role_code FROM '.$user_roles_table.' GROUP BY user_id) AS assigned '
				.'ON assigned.user_id = users.id SET users.role = assigned.role_code'
			);
		}

		$this->dbforge->drop_table('user_roles', TRUE);
		$this->dbforge->drop_table('roles', TRUE);
	}
}
