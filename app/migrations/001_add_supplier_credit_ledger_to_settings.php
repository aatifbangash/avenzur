<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Add_supplier_advance_ledger_to_settings extends CI_Migration
{
    public function up()
    {
        // Add supplier_advance_ledger column to sma_settings table
        $fields = array(
            'supplier_advance_ledger' => array(
                'type' => 'INT',
                'constraint' => 11,
                'null' => TRUE,
                'default' => NULL,
                'comment' => 'Supplier Advance Ledger Account ID'
            )
        );
        
        $this->dbforge->add_column('sma_settings', $fields);
        
        // Optional: Add an index for better performance if needed
        // $this->db->query('ALTER TABLE sma_settings ADD INDEX idx_supplier_advance_ledger (supplier_advance_ledger)');
        
        echo "Added supplier_advance_ledger column to sma_settings table successfully.\n";
    }

    public function down()
    {
        // Remove the supplier_advance_ledger column from sma_settings table
        $this->dbforge->drop_column('sma_settings', 'supplier_advance_ledger');
        
        echo "Removed supplier_advance_ledger column from sma_settings table successfully.\n";
    }
}