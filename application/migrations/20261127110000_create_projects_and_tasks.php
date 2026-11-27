<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Create_projects_and_tasks extends CI_Migration {

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
				'constraint' => 180,
			),
			'description' => array(
				'type' => 'TEXT',
				'null' => TRUE,
			),
			'coordinator_user_id' => array(
				'type' => 'INT',
				'unsigned' => TRUE,
			),
			'status' => array(
				'type' => 'VARCHAR',
				'constraint' => 16,
				'default' => 'active',
			),
			'completion_source' => array(
				'type' => 'VARCHAR',
				'constraint' => 16,
				'null' => TRUE,
			),
			'created_by' => array(
				'type' => 'INT',
				'unsigned' => TRUE,
			),
			'created_at' => array(
				'type' => 'DATETIME',
			),
			'updated_at' => array(
				'type' => 'DATETIME',
			),
			'completed_at' => array(
				'type' => 'DATETIME',
				'null' => TRUE,
			),
		));
		$this->dbforge->add_key('id', TRUE);
		$this->dbforge->add_key('coordinator_user_id');
		if ( ! $this->dbforge->create_table('projects', TRUE, array(
			'ENGINE' => 'InnoDB',
			'DEFAULT CHARSET' => 'utf8mb4',
			'COLLATE' => 'utf8mb4_unicode_ci',
		)))
		{
			show_error('Tabel proyek tidak dapat dibuat.');
		}

		$this->dbforge->add_field(array(
			'id' => array(
				'type' => 'INT',
				'unsigned' => TRUE,
				'auto_increment' => TRUE,
			),
			'project_id' => array(
				'type' => 'INT',
				'unsigned' => TRUE,
			),
			'title' => array(
				'type' => 'VARCHAR',
				'constraint' => 180,
			),
			'description' => array(
				'type' => 'TEXT',
				'null' => TRUE,
			),
			'assignee_user_id' => array(
				'type' => 'INT',
				'unsigned' => TRUE,
			),
			'created_by' => array(
				'type' => 'INT',
				'unsigned' => TRUE,
			),
			'urgency' => array(
				'type' => 'VARCHAR',
				'constraint' => 16,
				'default' => 'normal',
			),
			'status' => array(
				'type' => 'VARCHAR',
				'constraint' => 16,
				'default' => 'incomplete',
			),
			'due_date' => array(
				'type' => 'DATE',
				'null' => TRUE,
			),
			'created_at' => array(
				'type' => 'DATETIME',
			),
			'updated_at' => array(
				'type' => 'DATETIME',
			),
			'completed_at' => array(
				'type' => 'DATETIME',
				'null' => TRUE,
			),
		));
		$this->dbforge->add_key('id', TRUE);
		$this->dbforge->add_key(array('project_id', 'status'));
		$this->dbforge->add_key('assignee_user_id');
		if ( ! $this->dbforge->create_table('project_tasks', TRUE, array(
			'ENGINE' => 'InnoDB',
			'DEFAULT CHARSET' => 'utf8mb4',
			'COLLATE' => 'utf8mb4_unicode_ci',
		)))
		{
			show_error('Tabel tugas proyek tidak dapat dibuat.');
		}
	}

	public function down()
	{
		$this->dbforge->drop_table('project_tasks', TRUE);
		$this->dbforge->drop_table('projects', TRUE);
	}
}
