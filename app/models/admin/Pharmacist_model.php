<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Pharmacist_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Get all active pharmacies
     * @return array
     */
    public function get_all_pharmacies()
    {
        // Temporarily disable prefix
        $prefix = $this->db->dbprefix;
        $this->db->dbprefix = '';
        
        $this->db->select('id, name, code');
        $this->db->from('loyalty_pharmacies');
        $this->db->order_by('name', 'ASC');
        
        $query = $this->db->get();
        
        // Restore prefix
        $this->db->dbprefix = $prefix;
        
        return $query->result();
    }

    /**
     * Get branches by pharmacy ID
     * @param string $pharmacy_id
     * @return array
     */
    public function get_branches_by_pharmacy($pharmacy_id)
    {
        // Temporarily disable prefix
        $prefix = $this->db->dbprefix;
        $this->db->dbprefix = '';
        
        $this->db->select('id, name, code');
        $this->db->from('loyalty_branches');
        $this->db->where('pharmacy_id', $pharmacy_id);
        $this->db->order_by('name', 'ASC');
        
        $query = $this->db->get();
        
        // Restore prefix
        $this->db->dbprefix = $prefix;
        
        return $query->result();
    }

    /**
     * Get pharmacists by branch
     * 
     * Logic:
     * 1. Get the branch code from loyalty_branches table
     * 2. Get all users where group_id = 8 (pharmacists)
     * 3. For each pharmacist, get their warehouse_id
     * 4. Get the warehouse code from sma_warehouses
     * 5. Match warehouse code with branch code
     * 
     * @param string $branch_id
     * @return array
     */
    public function get_pharmacists_by_branch($branch_id)
    {
        // Temporarily disable prefix
        $prefix = $this->db->dbprefix;
        $this->db->dbprefix = '';
        
        // First, get the branch code
        $this->db->select('code');
        $this->db->from('loyalty_branches');
        $this->db->where('id', $branch_id);
        $branch = $this->db->get()->row();

        // Restore prefix for sma_users and sma_warehouses queries
        $this->db->dbprefix = $prefix;

        if (!$branch || !$branch->code) {
            return [];
        }

        $branch_code = $branch->code;

        // Get all pharmacists (group_id = 8) with their warehouse information
        $this->db->select('
            u.id,
            u.username,
            u.email,
            u.first_name,
            u.last_name,
            u.phone,
            u.warehouse_id,
            w.code as warehouse_code,
            w.name as warehouse_name
        ');
        $this->db->from('sma_users u');
        $this->db->join('sma_warehouses w', 'u.warehouse_id = w.id', 'left');
        $this->db->where('u.group_id', 8); // Pharmacists group
        $this->db->where('u.active', 1);
        
        $query = $this->db->get();
        $all_pharmacists = $query->result();

        // Filter pharmacists by matching warehouse code with branch code
        $filtered_pharmacists = [];
        foreach ($all_pharmacists as $pharmacist) {
            if ($pharmacist->warehouse_code === $branch_code) {
                $filtered_pharmacists[] = $pharmacist;
            }
        }

        return $filtered_pharmacists;
    }

    /**
     * Get pharmacist details by ID
     * @param int $user_id
     * @return object|null
     */
    public function get_pharmacist_by_id($user_id)
    {
        $this->db->select('
            u.id,
            u.username,
            u.email,
            u.first_name,
            u.last_name,
            u.phone,
            u.warehouse_id,
            w.code as warehouse_code,
            w.name as warehouse_name
        ');
        $this->db->from('sma_users u');
        $this->db->join('sma_warehouses w', 'u.warehouse_id = w.id', 'left');
        $this->db->where('u.id', $user_id);
        $this->db->where('u.group_id', 8);
        
        $query = $this->db->get();
        return $query->row();
    }

    /**
     * Get latest purchase item for a product (to get batch number and expiry date)
     * @param int $product_id
     * @return object|null
     */
    public function get_latest_purchase_item($product_id)
    {
        $this->db->select('batchno, expiry, supplier_part_no');
        $this->db->from('sma_purchase_items');
        $this->db->where('product_id', $product_id);
        $this->db->where('batchno IS NOT NULL');
        $this->db->where('batchno !=', '0');
        $this->db->where('batchno !=', '');
        $this->db->order_by('id', 'DESC');
        $this->db->limit(1);
        
        $query = $this->db->get();
        return $query->row();
    }

    /**
     * Save pharmacist incentive header
     * @param array $data
     * @return int|bool Incentive ID on success, false on failure
     */
    public function save_incentive($data)
    {
        // Temporarily disable prefix for pharmacist_incentive table
        $prefix = $this->db->dbprefix;
        $this->db->dbprefix = '';
        
        $this->db->insert('pharmacist_incentive', $data);
        
        $insert_id = $this->db->insert_id();
        
        // Restore prefix
        $this->db->dbprefix = $prefix;
        
        return $insert_id ? $insert_id : false;
    }

    /**
     * Save pharmacist incentive items (batch insert)
     * @param array $items Array of item data
     * @return bool
     */
    public function save_incentive_items($items)
    {
        if (empty($items)) {
            return false;
        }

        // Temporarily disable prefix for pharmacist_incentive_items table
        $prefix = $this->db->dbprefix;
        $this->db->dbprefix = '';
        
        $this->db->insert_batch('pharmacist_incentive_items', $items);
        
        // Restore prefix
        $this->db->dbprefix = $prefix;
        
        return $this->db->affected_rows() > 0;
    }

    /**
     * Get incentive by ID
     * @param int $incentive_id
     * @return object|null
     */
    public function get_incentive_by_id($incentive_id)
    {
        // Temporarily disable prefix
        $prefix = $this->db->dbprefix;
        $this->db->dbprefix = '';
        
        $this->db->select('pi.*, u.first_name, u.last_name, u.username');
        $this->db->from('pharmacist_incentive pi');
        $this->db->join('sma_users u', 'pi.pharmacist_id = u.id', 'left');
        $this->db->where('pi.id', $incentive_id);
        
        $query = $this->db->get();
        
        // Restore prefix
        $this->db->dbprefix = $prefix;
        
        return $query->row();
    }

    /**
     * Get incentive items by incentive ID
     * @param int $incentive_id
     * @return array
     */
    public function get_incentive_items($incentive_id)
    {
        // Temporarily disable prefix
        $prefix = $this->db->dbprefix;
        $this->db->dbprefix = '';
        
        $this->db->select('
            pii.*,
            p.code as product_code,
            p.name as product_name,
            c.name as supplier_name
        ');
        $this->db->from('pharmacist_incentive_items pii');
        $this->db->join('sma_products p', 'pii.product_id = p.id', 'left');
        $this->db->join('sma_companies c', 'pii.supplier_id = c.id', 'left');
        $this->db->where('pii.incentive_id', $incentive_id);
        $this->db->order_by('pii.id', 'ASC');
        
        $query = $this->db->get();
        
        // Restore prefix
        $this->db->dbprefix = $prefix;
        
        return $query->result();
    }

    /**
     * Get all incentives for a pharmacist
     * @param int $pharmacist_id
     * @return array
     */
    public function get_pharmacist_incentives($pharmacist_id)
    {
        // Temporarily disable prefix
        $prefix = $this->db->dbprefix;
        $this->db->dbprefix = '';
        
        $this->db->select('pi.*, COUNT(pii.id) as item_count');
        $this->db->from('pharmacist_incentive pi');
        $this->db->join('pharmacist_incentive_items pii', 'pi.id = pii.incentive_id', 'left');
        $this->db->where('pi.pharmacist_id', $pharmacist_id);
        $this->db->group_by('pi.id');
        $this->db->order_by('pi.created_at', 'DESC');
        
        $query = $this->db->get();
        
        // Restore prefix
        $this->db->dbprefix = $prefix;
        
        return $query->result();
    }

    /**
     * Update pharmacist incentive header
     * @param int $incentive_id
     * @param array $data
     * @return bool
     */
    public function update_incentive($incentive_id, $data)
    {
        // Temporarily disable prefix
        $prefix = $this->db->dbprefix;
        $this->db->dbprefix = '';
        
        $this->db->where('id', $incentive_id);
        $this->db->update('pharmacist_incentive', $data);
        
        $affected_rows = $this->db->affected_rows();
        
        // Restore prefix
        $this->db->dbprefix = $prefix;
        
        return $affected_rows > 0;
    }

    /**
     * Delete incentive items by incentive ID
     * @param int $incentive_id
     * @return bool
     */
    public function delete_incentive_items($incentive_id)
    {
        // Temporarily disable prefix
        $prefix = $this->db->dbprefix;
        $this->db->dbprefix = '';
        
        $this->db->where('incentive_id', $incentive_id);
        $this->db->delete('pharmacist_incentive_items');
        
        // Restore prefix
        $this->db->dbprefix = $prefix;
        
        return true;
    }

    /**
     * Delete entire incentive (header and items)
     * @param int $incentive_id
     * @return bool
     */
    public function delete_incentive($incentive_id)
    {
        // Temporarily disable prefix
        $prefix = $this->db->dbprefix;
        $this->db->dbprefix = '';
        
        // Delete items first
        $this->db->where('incentive_id', $incentive_id);
        $this->db->delete('pharmacist_incentive_items');
        
        // Delete header
        $this->db->where('id', $incentive_id);
        $this->db->delete('pharmacist_incentive');
        
        $affected_rows = $this->db->affected_rows();
        
        // Restore prefix
        $this->db->dbprefix = $prefix;
        
        return $affected_rows > 0;
    }

    /**
     * Get active incentive for a pharmacist
     * @param int $pharmacist_id
     * @return object|null
     */
    public function get_active_incentive($pharmacist_id)
    {
        // Temporarily disable prefix
        $prefix = $this->db->dbprefix;
        $this->db->dbprefix = '';
        
        $this->db->select('*');
        $this->db->from('pharmacist_incentive');
        $this->db->where('pharmacist_id', $pharmacist_id);
        $this->db->where('status', 'ACTIVE');
        $this->db->order_by('created_at', 'DESC');
        $this->db->limit(1);
        
        $query = $this->db->get();
        
        // Restore prefix
        $this->db->dbprefix = $prefix;
        
        return $query->row();
    }

    /**
     * Get incentive items with product and batch matching
     * @param int $incentive_id
     * @param array $products Array of product objects with id and batch_number
     * @return array Matching incentive items
     */
    public function get_matching_incentive_items($incentive_id, $products)
    {
        if (empty($products)) {
            return [];
        }

        // Temporarily disable prefix
        $prefix = $this->db->dbprefix;
        $this->db->dbprefix = '';
        
        $this->db->select('pii.*, p.code as product_code, p.name as product_name');
        $this->db->from('pharmacist_incentive_items pii');
        $this->db->join('sma_products p', 'pii.product_id = p.id', 'left');
        $this->db->where('pii.incentive_id', $incentive_id);
        
        // Build WHERE clause for product/batch matching
        $this->db->group_start();
        foreach ($products as $product) {
            $this->db->or_group_start();
            $this->db->where('pii.product_id', $product['product_id']);
            if (!empty($product['batch_number'])) {
                $this->db->where('pii.batch_number', $product['batch_number']);
            }
            $this->db->group_end();
        }
        $this->db->group_end();
        
        $query = $this->db->get();
        
        // Restore prefix
        $this->db->dbprefix = $prefix;
        
        return $query->result();
    }

    /**
     * Save incentive transaction records
     * @param array $transactions Array of transaction data
     * @return bool
     */
    public function save_incentive_transactions($transactions)
    {
        if (empty($transactions)) {
            return false;
        }

        // Temporarily disable prefix
        $prefix = $this->db->dbprefix;
        $this->db->dbprefix = '';
        
        $this->db->insert_batch('incentive_transactions', $transactions);
        
        $affected_rows = $this->db->affected_rows();
        
        // Restore prefix
        $this->db->dbprefix = $prefix;
        
        return $affected_rows > 0;
    }
}
