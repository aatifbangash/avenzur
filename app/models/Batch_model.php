<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Batch Model
 * 
 * Handles database operations for inventory batch management
 * Manages batch selection with FEFO (First Expiry First Out) logic
 * Uses existing sma_inventory_movements table
 * 
 * @package    Avenzur ERP
 * @subpackage Models
 * @category   Inventory Management
 * @author     Avenzur Development Team
 */
class Batch_model extends CI_Model {

    public function __construct() {
        parent::__construct();
    }

    /**
     * Get batch with earliest expiry date that has sufficient quantity
     * Implements FEFO (First Expiry First Out) logic
     * Uses sma_inventory_movements table
     *
     * @param int $medicine_id Medicine/Product ID
     * @param int $required_quantity Quantity needed
     * @return object|null Batch object or null if not available
     */
    public function get_earliest_expiry_batch($medicine_id, $required_quantity) {
        $batch = $this->db
            ->select('id, product_id as medicine_id, batch_number, quantity, expiry_date, net_unit_sale as selling_price')
            ->where('product_id', $medicine_id)
            ->where('quantity >=', $required_quantity)
            ->where('(expiry_date IS NULL OR expiry_date > CURDATE())', NULL, FALSE) // Not expired or no expiry
            ->order_by('expiry_date', 'ASC')
            ->limit(1)
            ->get('sma_inventory_movements')
            ->row();

        return $batch;
    }

    /**
     * Get all available batches for a medicine (for multi-batch selection)
     * Uses sma_inventory_movements table
     *
     * @param int $medicine_id Medicine/Product ID
     * @return array Array of batch objects
     */
    public function get_available_batches($medicine_id) {
        return $this->db
            ->select('id, product_id as medicine_id, batch_number, quantity, expiry_date, net_unit_sale as selling_price')
            ->where('product_id', $medicine_id)
            ->where('quantity >', 0)
            ->where('(expiry_date IS NULL OR expiry_date > CURDATE())', NULL, FALSE)
            ->order_by('expiry_date', 'ASC')
            ->get('sma_inventory_movements')
            ->result();
    }

    /**
     * Reduce batch quantity after order
     * Used when dispensing products from inventory
     * Updates sma_inventory_movements table
     *
     * @param int $batch_id Inventory Movement ID
     * @param float $quantity Quantity to reduce
     * @return bool Success status
     */
    public function reduce_batch_quantity($batch_id, $quantity) {
        $this->db->set('quantity', 'quantity - ' . $quantity, FALSE);
        $this->db->where('id', $batch_id);
        return $this->db->update('sma_inventory_movements');
    }

    /**
     * Get batch by ID
     * Uses sma_inventory_movements table
     * 
     * @param int $batch_id Inventory Movement ID
     * @return object|null Batch object or null if not found
     */
    public function get_batch($batch_id) {
        return $this->db
            ->select('id, product_id as medicine_id, batch_no, quantity, expiry as expiry_date, price as selling_price')
            ->where('id', $batch_id)
            ->get('sma_inventory_movements')
            ->row();
    }
}
