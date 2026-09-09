<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Update321 extends CI_Migration {

    public function up() {
        $permissions_table = 'permissions';
        $prefixed_permissions_table = $this->db->dbprefix('permissions');

        $has_ar = $this->db->field_exists('reports-unpaid-invoices-ar', $permissions_table)
            || $this->db->field_exists('reports-unpaid-invoices-ar', $prefixed_permissions_table);
        $has_ap = $this->db->field_exists('reports-unpaid-invoices-ap', $permissions_table)
            || $this->db->field_exists('reports-unpaid-invoices-ap', $prefixed_permissions_table);

        $columns = [];
        if (!$has_ar) {
            $columns['reports-unpaid-invoices-ar'] = [
                'type'       => 'TINYINT',
                'constraint' => '1',
                'null'       => true,
                'default'    => 0,
            ];
        }
        if (!$has_ap) {
            $columns['reports-unpaid-invoices-ap'] = [
                'type'       => 'TINYINT',
                'constraint' => '1',
                'null'       => true,
                'default'    => 0,
            ];
        }

        if (!empty($columns)) {
            $this->dbforge->add_column($permissions_table, $columns);
        }
    }

    public function down() { }
}
