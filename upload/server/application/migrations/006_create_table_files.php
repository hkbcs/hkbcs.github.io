<?php
/**
 * @author   Natan Felles <natanfelles@gmail.com>
 */
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Class Migration_create_table_api_limits
 *
 * @property CI_DB_forge         $dbforge
 * @property CI_DB_query_builder $db
 */
class Migration_create_table_files extends CI_Migration {

	protected $table = 'files';

	public function up()
	{
		$this->load->helper('db_helper');

		$fields = array(
			'id'           => [
				'type'           => 'INT(11)',
				'auto_increment' => TRUE,
				'unsigned'       => TRUE,
			],
			'filename'     => [
				'type' => 'VARCHAR(255)',
			],
			'mimetype'     => [
				'type' => 'VARCHAR(255)',
			],
			'status' => [
				'type' => 'INT(1)',
			],
			'user_uid' => [
				'type' => 'INT(11)',
			],
			'is_deleted' => [
				'type' => 'INT(1)',
			],
			'created_at' => [
				'type' => 'DATETIME',
			],
			'expiry_at' => [
				'type' => 'DATETIME',
			]
		);
		$this->dbforge->add_field($fields);
		$this->dbforge->add_key('id', TRUE);
		$this->dbforge->add_key('user_uid');
		$this->dbforge->create_table($this->table);
		$this->db->query(add_foreign_key($this->table, 'user_uid', 'users(id)', 'CASCADE', 'CASCADE'));
	}


	public function down()
	{
		$this->load->helper('db_helper');

		if ($this->db->table_exists($this->table))
		{
			$this->dbforge->drop_table($this->table);
		}
	}

}
