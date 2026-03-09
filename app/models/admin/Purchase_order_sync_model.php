<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Purchase_order_sync_model extends CI_Model{
    public function __construct()
    {
        parent::__construct();
        $this->load->admin_model('Inventory_model');
    }

    // For pagination: count total records with filters
    public function count_purchases($filters = [])
    {
        $this->db->from('sma_purchase_orders')
            ->where('active', 1);

        if (!empty($filters)) {
            if (!empty($filters['pid'])) {
                $this->db->where('id', $filters['pid']);
            }

            if (!empty($filters['lastInsertedId'])) {
                $this->db->where('id', $filters['lastInsertedId']);
            }

            if (!empty($filters['supplier_id'])) {
                $this->db->where('supplier_id', $filters['supplier_id']);
            }

            if (!empty($filters['warehouse_id'])) {
                $this->db->where('warehouse_id', $filters['warehouse_id']);
            }

            if (!empty($filters['status'])) {
                $this->db->where('status', $filters['status']);
            }

            if (!empty($filters['from_date'])) {
                $this->db->where("DATE(date) >=", $filters['from_date']);
            }
            if (!empty($filters['to_date'])) {
                $this->db->where("DATE(date) <=", $filters['to_date']);
            }
        }

        return $this->db->count_all_results();
    }

    /**
     * Return the set of products/details used during Shopify sync for a PO
     */
    public function get_shopify_data($purchase_id)
    {
        $sql = "SELECT DISTINCT
        p.id,
        po.net_unit_cost AS cost_price,
        po.sale_price AS price,
        TRIM(LEADING '0' FROM po.product_code) AS barcode,
        REPLACE(po.product_name, '-', ' ') AS name,
        p.image AS image,
        p.details AS description,
        p.tags AS tags,
        COALESCE(SUM(si.qty), 0) AS initial_stock,
        p.tax_rate,
        p.brand_name AS brand,
        CASE 
            WHEN sm.wms_sku IS NOT NULL THEN 'update'
            ELSE 'create'
        END AS status
        FROM sma_purchase_order_items po
        JOIN sma_products p 
        ON p.code = po.product_code
        JOIN sma_purchase_order_shelving_items si
        ON TRIM(LEADING '0' FROM si.product_code) = TRIM(LEADING '0' FROM po.product_code)
        JOIN sma_purchase_order_shelving s
        ON s.id = si.shelving_id
        LEFT JOIN sma_shopify_product_map sm
        ON TRIM(LEADING '0' FROM sm.wms_sku) = TRIM(LEADING '0' FROM po.product_code)
        WHERE po.purchase_id = ?
        AND po.sale_price > po.net_unit_cost
        GROUP BY TRIM(LEADING '0' FROM po.product_code)
        HAVING SUM(si.qty) > 0";
        $query = $this->db->query($sql, [$purchase_id]);
        return $query->result();
    }

    /**
     * Helper: is this purchase order already marked synced
     */
    public function is_synced($purchase_id)
    {
        $q = $this->db->select('shopify_synced')->from('sma_purchase_orders')->where('id', $purchase_id)->get();
        if ($q->num_rows()) {
            $row = $q->row();
            return !empty($row->shopify_synced);
        }
        return false;
    }

    /**
     * Get aggregated Shopify data for purchase orders (for listing display)
     * Returns product count, total quantity, total cost, total value for each PO
     */
    public function get_shopify_aggregates($purchase_ids = [])
    {
        if (empty($purchase_ids)) {
            return [];
        }

        $sql = "SELECT
            po.purchase_id,
            COUNT(DISTINCT TRIM(LEADING '0' FROM po.product_code)) as total_products,
            COALESCE(SUM(si.qty), 0) as total_quantity,
            SUM(po.net_unit_cost * si.qty) as total_cost,
            SUM(po.sale_price * si.qty) as total_value
        FROM sma_purchase_order_items po
        JOIN sma_purchase_order_shelving_items si
        ON TRIM(LEADING '0' FROM si.product_code) = TRIM(LEADING '0' FROM po.product_code)
        WHERE po.purchase_id IN (" . implode(',', array_fill(0, count($purchase_ids), '?')) . ")
        AND po.sale_price > po.net_unit_cost
        GROUP BY po.purchase_id
        HAVING SUM(si.qty) > 0";

        $query = $this->db->query($sql, $purchase_ids);
        $results = $query->result();

        // Convert to associative array keyed by purchase_id
        $aggregates = [];
        foreach ($results as $row) {
            $aggregates[$row->purchase_id] = [
                'total_products' => (int)$row->total_products,
                'total_quantity' => (int)$row->total_quantity,
                'total_cost' => (float)$row->total_cost,
                'total_value' => (float)$row->total_value
            ];
        }

        return $aggregates;
    }

}
