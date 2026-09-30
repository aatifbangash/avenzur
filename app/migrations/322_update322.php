<?php defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Update322 extends CI_Migration
{
    public function up()
    {
        $table = 'companies';
        if (!$this->db->field_exists('old_ledgers', $table)) {
            $this->dbforge->add_column($table, [
                'old_ledgers' => [
                    'type' => 'TEXT',
                    'null' => true,
                ],
            ]);
        }
    }

    public function down()
    {
        if ($this->db->field_exists('old_ledgers', 'companies')) {
            $this->dbforge->drop_column('companies', 'old_ledgers');
        }
    }
}
