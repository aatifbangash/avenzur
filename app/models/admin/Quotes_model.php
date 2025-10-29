<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Quotes_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function addQuote($data = [], $items = [])
    {
        ini_set('display_errors', '1');
        ini_set('display_startup_errors', '1');
        error_reporting(E_ALL);
        if ($this->db->insert('quotes', $data)) {
            $quote_id = $this->db->insert_id();
            if ($this->site->getReference('qu') == $data['reference_no']) {
                $this->site->updateReference('qu');
            }
            foreach ($items as $item) {
                $item['quote_id'] = $quote_id;
                $this->db->insert('quote_items', $item);
            }
            return true;
        }
        return false;
    }

    public function deleteQuote($id)
    {
        $this->site->log('Quotation', ['model' => $this->getQuoteByID($id), 'items' => $this->getAllQuoteItems($id)]);
        if ($this->db->delete('quote_items', ['quote_id' => $id]) && $this->db->delete('quotes', ['id' => $id])) {
            return true;
        }
        return false;
    }

    public function getAllQuoteItems($quote_id, $quote)
    {
        $this->db->select('
            quote_items.*, 
            tax_rates.code as tax_code, tax_rates.name as tax_name, tax_rates.rate as tax_rate, 
            products.image, products.cash_discount, products.credit_discount, products.details as details, product_variants.name as variant, products.hsn_code as hsn_code, products.second_name as second_name, products.unit as base_unit_id, 
            units.code as base_unit_code,
            SUM(IFNULL(CASE WHEN sma_inventory_movements.location_id = ' . $quote->warehouse_id . ' THEN sma_inventory_movements.quantity ELSE 0 END, 0)) as total_quantity')
            ->join('products', 'products.id=quote_items.product_id', 'left')
            ->join('inventory_movements', 'inventory_movements.avz_item_code=quote_items.avz_item_code', 'left')
            ->join('product_variants', 'product_variants.id=quote_items.option_id', 'left')
            ->join('tax_rates', 'tax_rates.id=quote_items.tax_rate_id', 'left')
            ->join('units', 'units.id=products.unit', 'left')
            ->group_by('quote_items.id')
            ->order_by('quote_items.id', 'desc');
        $this->db->where('quote_id', $quote_id);
        $q = $this->db->get('quote_items');

        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getProductNamesWithBatches($term, $warehouse_id, $pos = false, $limit = 20)
    {
         
        // removed from select ->  purchase_items.serial_number
       // $this->db->select('products.id, products.price, code, name, SUM(sma_inventory_movements.quantity) as quantity, cost, tax_rate, sma_products.type, unit, purchase_unit, tax_method')
       $this->db->select('products.*,   SUM(sma_inventory_movements.quantity) as quantity, categories.id as category_id, categories.name as category_name', false)
       ->join('inventory_movements', 'inventory_movements.product_id=products.id', 'left') 
       // ->join('purchase_items', 'purchase_items.product_id=products.id and purchase_items.warehouse_id='.$warehouse_id, 'left')
        ->join('categories', 'categories.id=products.category_id', 'left')
            ->group_by('products.id');
            if ($this->Settings->overselling) {
                $this->db->where("({$this->db->dbprefix('products')}.name LIKE '%" . $term . "%' OR {$this->db->dbprefix('products')}.code LIKE '%" . $term . "%' OR  concat({$this->db->dbprefix('products')}.name, ' (', {$this->db->dbprefix('products')}.code, ')') LIKE '%" . $term . "%')");
            } else {
                $this->db->where("({$this->db->dbprefix('products')}.name LIKE '%" . $term . "%' OR {$this->db->dbprefix('products')}.code LIKE '%" . $term . "%' OR  concat({$this->db->dbprefix('products')}.name, ' (', {$this->db->dbprefix('products')}.code, ')') LIKE '%" . $term . "%')");
            }
        //$this->db->having("SUM(sma_inventory_movements.quantity)>0"); 
        $this->db->limit($limit);
        if ($pos) {
            $this->db->where('hide_pos !=', 1);
        }
        $q = $this->db->get('products');
        //echo  $this->db->last_query(); exit; 
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $row->serial_number=''; 
                $data[] = $row;
            }
            return $data;
        }  
    }

    public function getAllQuoteItemsWithDetails($quote_id)
    {
        $this->db->select('quote_items.id, quote_items.product_name, quote_items.product_code, quote_items.quantity, quote_items.serial_no, quote_items.tax, quote_items.unit_price, quote_items.val_tax, quote_items.discount_val, quote_items.gross_total, products.details, products.hsn_code as hsn_code, products.second_name as second_name');
        $this->db->join('products', 'products.id=quote_items.product_id', 'left');
        $this->db->order_by('id', 'asc');
        $q = $this->db->get_where('quotes_items', ['quote_id' => $quote_id]);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
    }

    public function getItemByID($id)
    {
        $q = $this->db->get_where('quote_items', ['id' => $id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getProductByCode($code)
    {
        $q = $this->db->get_where('products', ['code' => $code], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getProductByName($name)
    {
        $q = $this->db->get_where('products', ['name' => $name], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getProductComboItems($pid, $warehouse_id)
    {
        $this->db->select('products.id as id, combo_items.item_code as code, combo_items.quantity as qty, products.name as name, products.type as type, warehouses_products.quantity as quantity')
            ->join('products', 'products.code=combo_items.item_code', 'left')
            ->join('warehouses_products', 'warehouses_products.product_id=products.id', 'left')
            ->where('warehouses_products.warehouse_id', $warehouse_id)
            ->group_by('combo_items.id');
        $q = $this->db->get_where('combo_items', ['combo_items.product_id' => $pid]);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }

            return $data;
        }
        return false;
    }

    public function getProductNames($term, $warehouse_id, $limit = 5)
    {
        $this->db->select('products.*, warehouses_products.quantity')
            ->join('warehouses_products', 'warehouses_products.product_id=products.id', 'left')
            ->group_by('products.id');

        $this->db->where("(name LIKE '%" . $term . "%' OR code LIKE '%" . $term . "%' OR  concat(name, ' (', code, ')') LIKE '%" . $term . "%')");

        $this->db->limit($limit);
        $q = $this->db->get('products');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
    }

    public function getProductOptionByID($id)
    {
        $q = $this->db->get_where('product_variants', ['id' => $id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getProductOptions($product_id, $warehouse_id, $all = null)
    {
        $wpv = "( SELECT option_id, warehouse_id, quantity from {$this->db->dbprefix('warehouses_products_variants')} WHERE product_id = {$product_id}) FWPV";
        $this->db->select('product_variants.id as id, product_variants.name as name, product_variants.price as price, product_variants.quantity as total_quantity, FWPV.quantity as quantity', false)
            ->join($wpv, 'FWPV.option_id=product_variants.id', 'left')
            ->where('product_variants.product_id', $product_id)
            ->group_by('product_variants.id');

        if (!$all) {
            $this->db->where('FWPV.warehouse_id', $warehouse_id);
            $this->db->where('FWPV.quantity >', 0);
        }
        $q = $this->db->get('product_variants');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getQuoteByID($id)
    {
        $q = $this->db->get_where('quotes', ['id' => $id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getWarehouseProductQuantity($warehouse_id, $product_id)
    {
        $q = $this->db->get_where('warehouses_products', ['warehouse_id' => $warehouse_id, 'product_id' => $product_id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getWHProduct($id)
    {
        $this->db->select('products.id, code, name, warehouses_products.quantity, cost, tax_rate')
            ->join('warehouses_products', 'warehouses_products.product_id=products.id', 'left')
            ->group_by('products.id');
        $q = $this->db->get_where('products', ['warehouses_products.product_id' => $id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function updateQuote($id, $data, $items = [])
    {
        if ($this->db->update('quotes', $data, ['id' => $id]) && $this->db->delete('quote_items', ['quote_id' => $id])) {
            foreach ($items as $item) {
                $item['quote_id'] = $id;
                $this->db->insert('quote_items', $item);
            }
            return true;
        }
        return false;
    }

    public function updateStatus($id, $status, $note)
    {
        if ($this->db->update('quotes', ['status' => $status, 'note' => $note], ['id' => $id])) {
            return true;
        }
        return false;
    }
}
