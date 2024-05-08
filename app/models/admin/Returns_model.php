<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Returns_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->load->admin_model('Inventory_model');
    }

    public function addReturn($data = [], $items = [])
    {
        // Enable error reporting
        
        $this->db->trans_start();
        if ($this->db->insert('returns', $data)) {
            $return_id = $this->db->insert_id();
            if ($this->site->getReference('re') == $data['reference_no']) {
                $this->site->updateReference('re');
            }
            
            foreach ($items as $item) {
                $item['return_id'] = $return_id;
                $this->db->insert('return_items', $item);
                
                if ($item['product_type'] == 'standard') {
                    $clause = ['product_id' => $item['product_id'], 'warehouse_id' => $item['warehouse_id'], 'batchno' => $item['batch_no'], 'purchase_id' => null, 'transfer_id' => null, 'option_id' => $item['option_id']];
                    $this->site->setPurchaseItem($clause, $item['quantity']);
                    $this->site->syncQuantityReturn($return_id, $item['product_id']);

                    $this->Inventory_model->add_movement($item['product_id'], $item['batch_no'], 'customer_return', $item['quantity'], $item['warehouse_id']); 
                    
                } elseif ($item['product_type'] == 'combo') {
                    $combo_items = $this->site->getProductComboItems($item['product_id']);
                    foreach ($combo_items as $combo_item) {
                        $clause = ['product_id' => $combo_item->id, 'purchase_id' => null, 'transfer_id' => null, 'option_id' => null];
                        $this->site->setPurchaseItem($clause, ($combo_item->qty * $item['quantity']));
                        $this->site->syncQuantity(null, null, null, $combo_item->id);
                    }
                }
            }
            $this->sma->update_award_points($data['grand_total'], $data['customer_id'], $data['created_by'], true);
        }
        $this->db->trans_complete();
        //echo $this->db->trans_status();exit;
        if ($this->db->trans_status() === false) {
            log_message('error', 'An errors has been occurred while adding the sale (Add:Returns_model.php)');
        } else {
            return $return_id;
        }

        return false;
    }

    public function deleteReturn($id)
    {
        $this->db->trans_start();
        $this->resetSaleActions($id);
        $this->site->log('Return', ['model' => $this->getReturnByID($id), 'items' => $this->getReturnItems($id)]);
        $this->db->delete('return_items', ['return_id' => $id]);
        $this->db->delete('returns', ['id' => $id]);
        $this->db->trans_complete();
        if ($this->db->trans_status() === false) {
            log_message('error', 'An errors has been occurred while adding the sale (Delete:Returns_model.php)');
        } else {
            return true;
        }
        return false;
    }

    public function getAverageCost($item_batchno, $item_code){
        $totalPurchases = [];
        $totalPurchasesQuery = "SELECT 
                                    p.id, 
                                    p.code item_code, 
                                    p.name name, 
                                    pi.batchno batch_no, 
                                    pi.expiry expiry, 
                                    round(sum(pi.quantity)) quantity,
                                    round(avg(pi.sale_price), 2) sale_price,
                                    round(avg(pi.net_unit_cost), 2) cost_price,
                                    round(sum(pi.net_unit_cost * pi.quantity), 2) total_cost_price,
                                    round(avg(pi.unit_cost), 2) purchase_price
                                FROM sma_products p
                                INNER JOIN sma_purchase_items pi ON p.id = pi.product_id
                                INNER JOIN sma_purchases pc ON pc.id = pi.purchase_id
                                WHERE pi.purchase_item_id IS NULL AND pc.status = 'received'";
        $totalPurchasesQuery .= "AND (p.code = '{$item_code}' OR p.name LIKE '%{$item_code}%') ";
        $totalPurchasesQuery .= "GROUP BY p.code, p.name, pi.batchno
                                ORDER BY p.id DESC";
        $totalPurchseResultSet = $this->db->query($totalPurchasesQuery);

        if ($totalPurchseResultSet->num_rows() > 0) {
            foreach ($totalPurchseResultSet->result() as $row) {
                $row->cost_price = ($row->total_cost_price / $row->quantity);
                $totalPurchases[] = $row;
            }
        }

        return $totalPurchases;
    }

    public function getProductNamesWithBatches($term, $warehouse_id, $pos = false, $limit = 5)
    {
        $wp = "( SELECT product_id, warehouse_id, quantity as quantity from {$this->db->dbprefix('warehouses_products')} ) FWP";

        $this->db->select('products.*, purchase_items.serial_number, FWP.quantity as quantity, categories.id as category_id, categories.name as category_name', false)
            ->join($wp, 'FWP.product_id=products.id', 'left')
            // ->join('warehouses_products FWP', 'FWP.product_id=products.id', 'left')
            ->join('purchase_items', 'purchase_items.product_id=products.id', 'left')
            ->join('categories', 'categories.id=products.category_id', 'left')
            ->group_by('products.id');
        if ($this->Settings->overselling) {
            $this->db->where("({$this->db->dbprefix('products')}.name LIKE '%" . $term . "%' OR {$this->db->dbprefix('products')}.code LIKE '%" . $term . "%' OR  concat({$this->db->dbprefix('products')}.name, ' (', {$this->db->dbprefix('products')}.code, ')') LIKE '%" . $term . "%')");
        } else {
            $this->db->where("((({$this->db->dbprefix('products')}.track_quantity = 0 OR FWP.quantity > 0) AND FWP.warehouse_id = '" . $warehouse_id . "') OR {$this->db->dbprefix('products')}.type != 'standard') AND "
                . "({$this->db->dbprefix('products')}.name LIKE '%" . $term . "%' OR {$this->db->dbprefix('products')}.code LIKE '%" . $term . "%' OR  concat({$this->db->dbprefix('products')}.name, ' (', {$this->db->dbprefix('products')}.code, ')') LIKE '%" . $term . "%')");
        }
        // $this->db->order_by('products.name ASC');
        if ($pos) {
            $this->db->where('hide_pos !=', 1);
        }
        $this->db->limit($limit);
        $q = $this->db->get('products');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
    }

    public function getProductNames($term, $limit = 5)
    {
        $this->db->where("(name LIKE '%" . $term . "%' OR code LIKE '%" . $term . "%' OR  concat(name, ' (', code, ')') LIKE '%" . $term . "%')");
        $this->db->limit($limit);
        $q = $this->db->get('products');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getProductOptionByID($id)
    {
        $q = $this->db->get_where('product_variants', ['id' => $id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getProductsSold($product_id)
    {
        $q = $this->db->get_where('products', ['id' => $product_id]);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;

    }
    public function getProductOptions($product_id)
    {
        $q = $this->db->get_where('product_variants', ['product_id' => $product_id]);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getReturnByID($id)
    {
        $q = $this->db->get_where('returns', ['id' => $id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getAccoutsEntryByID($id)
    {
        $q = $this->db->get_where('sma_accounts_entries', ['rid' => $id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getReturnItems($return_id)
    {
        $this->db->select('return_items.*, tax_rates.code as tax_code, tax_rates.name as tax_name, tax_rates.rate as tax_rate, products.image, products.details as details, product_variants.name as variant, products.hsn_code as hsn_code, products.second_name as second_name')
            ->join('products', 'products.id=return_items.product_id', 'left')
            ->join('product_variants', 'product_variants.id=return_items.option_id', 'left')
            ->join('tax_rates', 'tax_rates.id=return_items.tax_rate_id', 'left')
            ->where('return_id', $return_id)
            ->group_by('return_items.id')
            ->order_by('id', 'asc');

        $q = $this->db->get('return_items');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function resetSaleActions($id)
    {
        if ($items = $this->getReturnItems($id)) {
            foreach ($items as $item) {
                if ($item->product_type == 'standard') {
                    $clause = ['product_id' => $item->product_id, 'purchase_id' => null, 'transfer_id' => null, 'option_id' => $item->option_id];
                    $this->site->setPurchaseItem($clause, (0 - $item->quantity));
                    $this->site->syncQuantity(null, null, null, $item->product_id);
                } elseif ($item->product_type == 'combo') {
                    $combo_items = $this->site->getProductComboItems($item->product_id);
                    foreach ($combo_items as $combo_item) {
                        $clause = ['product_id' => $combo_item->id, 'purchase_id' => null, 'transfer_id' => null, 'option_id' => null];
                        $this->site->setPurchaseItem($clause, (0 - ($combo_item->qty * $item->quantity)));
                        $this->site->syncQuantity(null, null, null, $combo_item->id);
                    }
                }
            }
        }
    }

    public function updateReturn($id, $data = [], $items = [])
    {
        $this->db->trans_start();
        $this->resetSaleActions($id);
        if ($this->db->update('returns', $data, ['id' => $id]) && $this->db->delete('return_items', ['return_id' => $id])) {
            // $return_id = $id;
            foreach ($items as $item) {
                // $item['return_id'] = $return_id;
                $this->db->insert('return_items', $item);
                if ($item['product_type'] == 'standard') {
                    $clause = ['product_id' => $item['product_id'], 'purchase_id' => null, 'transfer_id' => null, 'option_id' => $item['option_id']];
                    $this->site->setPurchaseItem($clause, $item['quantity']);
                    $this->site->syncQuantity(null, null, null, $item['product_id']);
                    $this->Inventory_model->update_movement($item['product_id'], $item['batch_no'], 'customer_return', $item['quantity'], $item['warehouse_id']);
                } elseif ($item['product_type'] == 'combo') {
                    $combo_items = $this->site->getProductComboItems($item['product_id']);
                    foreach ($combo_items as $combo_item) {
                        $clause = ['product_id' => $combo_item->id, 'purchase_id' => null, 'transfer_id' => null, 'option_id' => null];
                        $this->site->setPurchaseItem($clause, ($combo_item->qty * $item['quantity']));
                        $this->site->syncQuantity(null, null, null, $combo_item->id);
                    }
                }
            }
        }
        $this->db->trans_complete();
        if ($this->db->trans_status() === false) {
            log_message('error', 'An errors has been occurred while adding the sale (Update:Returns_model.php)');
        } else {
            return true;
        }

        return false;
    }
}
