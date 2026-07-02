<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Purchase_order_sync_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Count total purchase orders with filters (for pagination)
     * @param array $filters
     * @return int
     */
    public function count_purchases($filters = [])
    {
        $this->db->from('sma_purchase_orders')
            ->where('active', 1);

        if (!empty($filters)) {
            if (!empty($filters['pid'])) {
                $this->db->where('id', $filters['pid']);
            }
            
        }

        return $this->db->count_all_results();
    }

    /**
     * Get Shopify data for a specific purchase order
     * Returns product details needed for Shopify sync
     * @param int $purchase_id
     * @return array
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
     * Check if a purchase order is already synced to Shopify
     * @param int|null $purchase_id If provided, check specific PO, else return all synced
     * @return mixed
     */
    public function is_synced($purchase_id)
    {
        $q = $this->db
            ->where('id', $purchase_id)
            ->where('status','shelved')
            ->where('shopify_synced', 1)
            ->get('sma_purchase_orders');

        return $q->num_rows() > 0;
    }

    /**
     * Get aggregated Shopify data for purchase orders (for listing display)
     * @param int $purchase_ids
     * @return array
     */
    public function get_shopify_aggregates($purchase_ids)
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
                    GROUP BY TRIM(LEADING '0' FROM po.product_code)
                    HAVING SUM(si.qty) > 0
                    ORDER BY
                    CASE WHEN po.sale_price < po.net_unit_cost THEN 0 ELSE 1 END,
                    CASE WHEN p.image IS NULL OR p.image = '' THEN 0 ELSE 1 END,
                    CASE WHEN po.product_code IS NULL OR po.product_code = '' THEN 0 ELSE 1 END,
                    name ASC
                ";

    return $this->db->query($sql, [$purchase_ids])->result();
    }

    /**
     * Get shelved purchase orders with pagination
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function get_shelved_purchases($limit, $offset)
    {
         $this->db->where('status','shelved')->where('shopify_synced', 1)->order_by('id', 'DESC');
        $this->db->limit($limit, $offset);
        return $this->db->get('sma_purchase_orders')->result();
    }

    /**
     * Count total shelved purchase orders
     * @return int
     */
    public function count_shelved_purchases()
    {
        $this->db->where('status','shelved')->where('shopify_synced', 1)->order_by('id', 'DESC');
        return $this->db->count_all_results('sma_purchase_orders');
    }
}
