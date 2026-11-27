<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Create_users extends CI_Migration {

	public function up()
	{
		$this->dbforge->add_field(array(
			'id' => array(
				'type' => 'INT',
				'unsigned' => TRUE,
				'auto_increment' => TRUE,
			),
			'name' => array(
				'type' => 'VARCHAR',
				'constraint' => 150,
			),
			'email' => array(
				'type' => 'VARCHAR',
				'constraint' => 190,
			),
			'password_hash' => array(
				'type' => 'VARCHAR',
				'constraint' => 255,
			),
			'role' => array(
				'type' => 'VARCHAR',
				'constraint' => 32,
			),
			'is_active' => array(
				'type' => 'TINYINT',
				'constraint' => 1,
				'default' => 1,
			),
			'created_at' => array(
				'type' => 'DATETIME',
			),
			'updated_at' => array(
				'type' => 'DATETIME',
			),
		));
		$this->dbforge->add_key('id', TRUE);
		if ( ! $this->dbforge->create_table('users', TRUE, array(
			'ENGINE' => 'InnoDB',
			'DEFAULT CHARSET' => 'utf8mb4',
			'COLLATE' => 'utf8mb4_unicode_ci',
		)))
		{
			show_error('Tabel pengguna tidak dapat dibuat.');
		}

		$table = $this->db->escape_identifiers($this->db->dbprefix('users'));
		$index = $this->db->query('SHOW INDEX FROM '.$table.' WHERE Key_name = ?', array('users_email_unique'));
		if ($index === FALSE)
		{
			show_error('Indeks unik email tidak dapat diperiksa.');
		}

		if ($index->num_rows() === 0
			&& $this->db->query('ALTER TABLE '.$table.' ADD UNIQUE KEY `users_email_unique` (`email`)') === FALSE)
		{
			show_error('Indeks unik email tidak dapat dibuat.');
		}
	}

	public function down()
	{
		$this->dbforge->drop_table('users', TRUE);
	}
}
