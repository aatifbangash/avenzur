<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Products_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();

        // Sequence-Code
        $this->load->library('SequenceCode');
        $this->sequenceCode = new SequenceCode();
        $this->load->admin_model('Inventory_model');
    }

    public function addPrintJob($zplCode, $location_details)
    {
        $print_job = array(
            'content' => $zplCode,
            'location_id' => $location_details->id,
            'location_name' => $location_details->name,
            'status' => 0,
            'date_created' => $currentDateTime = date('Y-m-d H:i:s'),
            'date_updated' => $currentDateTime = date('Y-m-d H:i:s')
        );
        $this->db->insert('print_jobs', $print_job);
    }
    public function add_products($products = [])
    {
        if (!empty($products)) {
            $warehouses = $this->site->getAllWarehouses();
            foreach ($products as $product) {
                $variants = explode('|', $product['variants']);
                unset($product['variants']);
                // Sequence-Code
                $product['sequence_code'] = $this->sequenceCode->generate('PRD', 5);
                if ($this->db->insert('products', $product)) {
                    $product_id = $this->db->insert_id();
                    // update item_code
                    $cat_id_q = $this->db->get_where('categories', ['id' => $product['category_id']], 1);
                    $category_code = 0;
                    if ($cat_id_q->num_rows() > 0) {
                        $row_cat = $cat_id_q->row();
                        $category_code = $row_cat->category_code;
                        $formatted_id = str_pad($product_id, 6, '0', STR_PAD_LEFT);
                        // Concatenate the category code and formatted ID
                        //$item_code = $category_code . $formatted_id;
                        //$this->db->update('sma_products', ['item_code' => $item_code], ['id' => $product_id]);
                    }
                    foreach ($warehouses as $warehouse) {
                        $this->db->insert('warehouses_products', ['product_id' => $product_id, 'warehouse_id' => $warehouse->id, 'quantity' => 0]);
                    }
                    foreach ($variants as $variant) {
                        if ($variant && trim($variant) != '') {
                            $vat = ['product_id' => $product_id, 'name' => trim($variant)];
                            $this->db->insert('product_variants', $vat);
                        }
                    }
                }
            }
            return true;
        }
        return false;
    }

    public function addAdjustment($data, $products)
    {
        if ($this->db->insert('adjustments', $data)) {
            $adjustment_id = $this->db->insert_id();
            foreach ($products as $product) {
                $product['adjustment_id'] = $adjustment_id;
                // Keep purchase_price for adjustment_items table
                $temp_purchase_price = isset($product['purchase_price']) ? $product['purchase_price'] : 0;
                unset($product['purchase_price']);
                $this->db->insert('adjustment_items', $product);
                // Use purchase_price as real_unit_cost for inventory sync
                $product['real_unit_cost'] = $temp_purchase_price;
                $this->syncAdjustment($product);
            }
            if ($this->site->getReference('qa') == $data['reference_no']) {
                $this->site->updateReference('qa');
            }
            return $adjustment_id; // Return adjustment_id instead of true
        }
        return false;
    }
    public function addProductSimplified($data)
    {
        // Generate sequence code
        $data['sequence_code'] = $this->sequenceCode->generate('PRD', 5);

        // Insert product
        if ($this->db->insert('products', $data)) {
            $product_id = $this->db->insert_id();

            // Create warehouse entries with 0 quantity for all warehouses
            $warehouses = $this->site->getAllWarehouses();

            if (!empty($warehouses)) {
                foreach ($warehouses as $warehouse) {
                    $this->db->insert('warehouses_products', [
                        'product_id'   => $product_id,
                        'warehouse_id' => $warehouse->id,
                        'quantity'     => 0,
                        'avg_cost'     => $data['cost']
                    ]);
                }
            }

            // Sync quantity (this maintains compatibility with existing system)
            $this->site->syncQuantity(null, null, null, $product_id);

            return true;
        }

        return false;
    }
    public function addCombo($data, $products)
    {
        if ($this->db->insert('combos', $data)) {
            $combo_id = $this->db->insert_id();
            foreach ($products as $product) {
                $product['combo_id'] = $combo_id;
                $this->db->insert('combo_products', $product);
            }
            return true;
        }
        return false;
    }
    public function updateCombo($id, $data, $products)
    {
        if ($this->db->update('combos', $data, ['id' => $id]) && $this->db->delete('combo_products', ['combo_id' => $id])) {
            foreach ($products as $product) {
                $product['combo_id'] = $id;
                $this->db->insert('combo_products', $product);
            }
            return true;
        }
        return false;
    }
    public function getComboByID($id)
    {

        $this->db->select('combos.*, products.code as product_code, products.name as product_name, products.price')
            ->join('products', 'products.id=combos.primary_product_id', 'left');
        $this->db->where('combos.id', $id);
        $q = $this->db->get('combos');
        // $q = $this->db->get_where('combos', ['id' => $id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }
    public function getComboItems($combo_id)
    {
        $this->db->select('combo_products.*, products.code as product_code, products.name as product_name, products.price, products.image, products.details as details')
            ->join('products', 'products.id=combo_products.product_id', 'left')
            ->group_by('combo_products.id')
            ->order_by('id', 'asc');

        $this->db->where('combo_id', $combo_id);
        $q = $this->db->get('combo_products');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }
    public function deleteCombo($id)
    {
        $this->site->log('Deleted Combo', ['model' => $this->getComboByID($id), 'items' => $this->getComboItems($id)]);
        if ($this->db->delete('combos', ['id' => $id]) && $this->db->delete('combo_products', ['combo_id' => $id])) {
            return true;
        }
        return false;
    }

    public function addBundle($data, $products)
    {
        if ($this->db->insert('bundles', $data)) {
            $bundle_id = $this->db->insert_id();
            foreach ($products as $product) {
                $product['bundle_id'] = $bundle_id;
                $this->db->insert('bundle_items', $product);
            }
            return true;
        }
        return false;
    }
    public function updateBundle($id, $data, $products)
    {
        if ($this->db->update('bundles', $data, ['id' => $id]) && $this->db->delete('bundle_items', ['bundle_id' => $id])) {
            foreach ($products as $product) {
                $product['bundle_id'] = $id;
                $this->db->insert('bundle_items', $product);
            }
            return true;
        }
        return false;
    }
    public function deleteBundle($id)
    {
        $this->site->log('Deleted Bundle', ['model' => $this->getBundleByID($id), 'items' => $this->getBundleItems($id)]);
        if ($this->db->delete('bundles', ['id' => $id]) && $this->db->delete('bundle_items', ['bundle_id' => $id])) {
            return true;
        }
        return false;
    }
    public function getBundleByID($id)
    {
        $q = $this->db->get_where('bundles', ['id' => $id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }
    public function getBundleItems($bundle_id)
    {
        $this->db->select('bundle_items.*, products.code as product_code, products.name as product_name, products.price, products.image, products.details as details')
            ->join('products', 'products.id=bundle_items.product_id', 'left')
            ->group_by('bundle_items.id')
            ->order_by('id', 'asc');

        $this->db->where('bundle_id', $bundle_id);
        $q = $this->db->get('bundle_items');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }
    public function getBUSuggestions($term, $limit = 5)
    {
        $this->db->select('' . $this->db->dbprefix('products') . '.id, code, ' . $this->db->dbprefix('products') . '.name as name, ' . $this->db->dbprefix('products') . '.price')
            ->where("type != 'combo' AND "
                . '(' . $this->db->dbprefix('products') . ".name LIKE '%" . $term . "%' OR code LIKE '%" . $term . "%' OR
                concat(" . $this->db->dbprefix('products') . ".name, ' (', code, ')') LIKE '%" . $term . "%')")
            ->limit($limit);
        $q = $this->db->get('products');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }
    public function getComboSuggestions($term, $limit = 5)
    {
        $this->db->select('' . $this->db->dbprefix('products') . '.id, code, ' . $this->db->dbprefix('products') . '.name as name, ' . $this->db->dbprefix('products') . '.price')
            ->where("type != 'combo' AND "
                . '(' . $this->db->dbprefix('products') . ".name LIKE '%" . $term . "%' OR code LIKE '%" . $term . "%' OR
                concat(" . $this->db->dbprefix('products') . ".name, ' (', code, ')') LIKE '%" . $term . "%')")
            ->limit($limit);
        $q = $this->db->get('products');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }


    public function addAjaxProduct($data)
    {
        // Sequence-Code
        $data['sequence_code'] = $this->sequenceCode->generate('PRD', 5);
        if ($this->db->insert('products', $data)) {
            $product_id = $this->db->insert_id();
            return $this->getProductByID($product_id);
        }
        return false;
    }

    public function addProduct($data, $items, $warehouse_qty, $product_attributes, $photos)
    {
        // Sequence-Code
        $data['sequence_code'] = $this->sequenceCode->generate('PRD', 5);
        if ($this->db->insert('products', $data)) {
            $product_id = $this->db->insert_id();
            // update item_code
            $cat_id_q = $this->db->get_where('categories', ['id' => $data['category_id']], 1);
            $category_code = 0;
            if ($cat_id_q->num_rows() > 0) {
                $row_cat = $cat_id_q->row();
                $category_code = $row_cat->category_code;
                $formatted_id = str_pad($product_id, 6, '0', STR_PAD_LEFT);
                // Concatenate the category code and formatted ID
                $item_code = $category_code . $formatted_id;
                $this->db->update('sma_products', ['item_code' => $item_code], ['id' => $product_id]);
            }

            if ($items) {
                foreach ($items as $item) {
                    $item['product_id'] = $product_id;
                    $this->db->insert('combo_items', $item);
                }
            }

            $warehouses = $this->site->getAllWarehouses();
            if ($data['type'] != 'standard') {
                foreach ($warehouses as $warehouse) {
                    $this->db->insert('warehouses_products', ['product_id' => $product_id, 'warehouse_id' => $warehouse->id, 'quantity' => 0]);
                }
            }

            $tax_rate = $this->site->getTaxRateByID($data['tax_rate']);

            if ($warehouse_qty && !empty($warehouse_qty)) {
                foreach ($warehouse_qty as $wh_qty) {
                    if (isset($wh_qty['quantity']) && !empty($wh_qty['quantity'])) {
                        $this->db->insert('warehouses_products', ['product_id' => $product_id, 'warehouse_id' => $wh_qty['warehouse_id'], 'quantity' => $wh_qty['quantity'], 'rack' => $wh_qty['rack'], 'avg_cost' => $data['cost']]);

                        if (!$product_attributes) {
                            $tax_rate_id = $tax_rate ? $tax_rate->id : null;
                            $tax = $tax_rate ? (($tax_rate->type == 1) ? $tax_rate->rate . '%' : $tax_rate->rate) : null;
                            $unit_cost = $data['cost'];
                            if ($tax_rate) {
                                if ($tax_rate->type == 1 && $tax_rate->rate != 0) {
                                    if ($data['tax_method'] == '0') {
                                        $pr_tax_val = ($data['cost'] * $tax_rate->rate) / (100 + $tax_rate->rate);
                                        $net_item_cost = $data['cost'] - $pr_tax_val;
                                        $item_tax = $pr_tax_val * $wh_qty['quantity'];
                                    } else {
                                        $net_item_cost = $data['cost'];
                                        $pr_tax_val = ($data['cost'] * $tax_rate->rate) / 100;
                                        $unit_cost = $data['cost'] + $pr_tax_val;
                                        $item_tax = $pr_tax_val * $wh_qty['quantity'];
                                    }
                                } else {
                                    $net_item_cost = $data['cost'];
                                    $item_tax = $tax_rate->rate;
                                }
                            } else {
                                $net_item_cost = $data['cost'];
                                $item_tax = 0;
                            }

                            $subtotal = (($net_item_cost * $wh_qty['quantity']) + $item_tax);

                            $item = [
                                'product_id' => $product_id,
                                'product_code' => $data['code'],
                                'product_name' => $data['name'],
                                'net_unit_cost' => $net_item_cost,
                                'unit_cost' => $unit_cost,
                                'real_unit_cost' => $unit_cost,
                                'quantity' => $wh_qty['quantity'],
                                'quantity_balance' => $wh_qty['quantity'],
                                'quantity_received' => $wh_qty['quantity'],
                                'item_tax' => $item_tax,
                                'tax_rate_id' => $tax_rate_id,
                                'tax' => $tax,
                                'subtotal' => $subtotal,
                                'warehouse_id' => $wh_qty['warehouse_id'],
                                'date' => date('Y-m-d'),
                                'status' => 'received',
                            ];
                            $this->db->insert('purchase_items', $item);
                            $this->site->syncProductQty($product_id, $wh_qty['warehouse_id']);
                        }
                    }
                }
            }

            if ($product_attributes) {
                foreach ($product_attributes as $pr_attr) {
                    $pr_attr_details = $this->getPrductVariantByPIDandName($product_id, $pr_attr['name']);

                    $pr_attr['product_id'] = $product_id;
                    $variant_warehouse_id = $pr_attr['warehouse_id'];
                    unset($pr_attr['warehouse_id']);
                    if ($pr_attr_details) {
                        $option_id = $pr_attr_details->id;
                    } else {
                        $this->db->insert('product_variants', $pr_attr);
                        $option_id = $this->db->insert_id();
                    }
                    if ($pr_attr['quantity'] != 0) {
                        $this->db->insert('warehouses_products_variants', ['option_id' => $option_id, 'product_id' => $product_id, 'warehouse_id' => $variant_warehouse_id, 'quantity' => $pr_attr['quantity']]);

                        $tax_rate_id = $tax_rate ? $tax_rate->id : null;
                        $tax = $tax_rate ? (($tax_rate->type == 1) ? $tax_rate->rate . '%' : $tax_rate->rate) : null;
                        $unit_cost = $data['cost'];
                        if ($tax_rate) {
                            if ($tax_rate->type == 1 && $tax_rate->rate != 0) {
                                if ($data['tax_method'] == '0') {
                                    $pr_tax_val = ($data['cost'] * $tax_rate->rate) / (100 + $tax_rate->rate);
                                    $net_item_cost = $data['cost'] - $pr_tax_val;
                                    $item_tax = $pr_tax_val * $pr_attr['quantity'];
                                } else {
                                    $net_item_cost = $data['cost'];
                                    $pr_tax_val = ($data['cost'] * $tax_rate->rate) / 100;
                                    $unit_cost = $data['cost'] + $pr_tax_val;
                                    $item_tax = $pr_tax_val * $pr_attr['quantity'];
                                }
                            } else {
                                $net_item_cost = $data['cost'];
                                $item_tax = $tax_rate->rate;
                            }
                        } else {
                            $net_item_cost = $data['cost'];
                            $item_tax = 0;
                        }

                        $subtotal = (($net_item_cost * $pr_attr['quantity']) + $item_tax);
                        $item = [
                            'product_id' => $product_id,
                            'product_code' => $data['code'],
                            'product_name' => $data['name'],
                            'net_unit_cost' => $net_item_cost,
                            'unit_cost' => $unit_cost,
                            'quantity' => $pr_attr['quantity'],
                            'option_id' => $option_id,
                            'quantity_balance' => $pr_attr['quantity'],
                            'quantity_received' => $pr_attr['quantity'],
                            'item_tax' => $item_tax,
                            'tax_rate_id' => $tax_rate_id,
                            'tax' => $tax,
                            'subtotal' => $subtotal,
                            'warehouse_id' => $variant_warehouse_id,
                            'date' => date('Y-m-d'),
                            'status' => 'received',
                        ];
                        $item['option_id'] = !empty($item['option_id']) && is_numeric($item['option_id']) ? $item['option_id'] : null;
                        $this->db->insert('purchase_items', $item);
                    }

                    foreach ($warehouses as $warehouse) {
                        if (!$this->getWarehouseProductVariant($warehouse->id, $product_id, $option_id)) {
                            $this->db->insert('warehouses_products_variants', ['option_id' => $option_id, 'product_id' => $product_id, 'warehouse_id' => $warehouse->id, 'quantity' => 0]);
                        }
                    }

                    $this->site->syncVariantQty($option_id, $variant_warehouse_id);
                }
            }

            if ($photos) {
                foreach ($photos as $photo) {
                    $this->db->insert('product_photos', ['product_id' => $product_id, 'photo' => $photo]);
                }
            }

            $this->site->syncQuantity(null, null, null, $product_id);
            return true;
        }
        return false;
    }

    public function addQuantity($product_id, $warehouse_id, $quantity, $rack = null)
    {
        if ($this->getProductQuantity($product_id, $warehouse_id)) {
            if ($this->updateQuantity($product_id, $warehouse_id, $quantity, $rack)) {
                return true;
            }
        } else {
            if ($this->insertQuantity($product_id, $warehouse_id, $quantity, $rack)) {
                return true;
            }
        }

        return false;
    }

    public function addStockCount($data)
    {
        if ($this->db->insert('stock_counts', $data)) {
            return true;
        }
        return false;
    }

    public function deleteAdjustment($id)
    {
        $this->reverseAdjustment($id);
        $this->site->log('Quantity adjustment', ['model' => $this->getAdjustmentByID($id), 'items' => $this->getAdjustmentItems($id)]);
        if ($this->db->delete('adjustments', ['id' => $id]) && $this->db->delete('adjustment_items', ['adjustment_id' => $id])) {
            return true;
        }
        return false;
    }

    public function deleteProduct($id)
    {
        $this->site->log('Product', ['model' => $this->getProductByID($id)]);
        if ($this->db->delete('products', ['id' => $id]) && $this->db->delete('warehouses_products', ['product_id' => $id])) {
            $this->db->delete('warehouses_products_variants', ['product_id' => $id]);
            $this->db->delete('product_variants', ['product_id' => $id]);
            $this->db->delete('product_photos', ['product_id' => $id]);
            $this->db->delete('product_prices', ['product_id' => $id]);
            return true;
        }
        return false;
    }

    public function fetch_products($category_id, $limit, $start, $subcategory_id = null)
    {
        $this->db->limit($limit, $start);
        if ($category_id) {
            $this->db->where('category_id', $category_id);
        }
        if ($subcategory_id) {
            $this->db->where('subcategory_id', $subcategory_id);
        }
        $this->db->order_by('id', 'asc');
        $query = $this->db->get('products');

        if ($query->num_rows() > 0) {
            foreach ($query->result() as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function finalizeStockCount($id, $data, $products)
    {
        if ($this->db->update('stock_counts', $data, ['id' => $id])) {
            foreach ($products as $product) {
                $this->db->insert('stock_count_items', $product);
            }
            return true;
        }
        return false;
    }
    public function getAdjustmentByCountID($count_id)
    {
        $q = $this->db->get_where('adjustments', ['count_id' => $count_id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getAdjustmentByID($id)
    {
        $q = $this->db->get_where('adjustments', ['id' => $id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function get_products_by_ids($product_ids)
{
    if (empty($product_ids)) {
        return false;
    }

    $this->db->select('id, code, name, cost, price, tax_rate, unit');
    $this->db->from('products');
    $this->db->where_in('id', $product_ids);
    $q = $this->db->get();

    if ($q->num_rows() > 0) {
        return $q->result();
    }
    return false;
}



    public function getAdjustmentItems($adjustment_id)
    {
        $this->db->select('adjustment_items.*, products.code as product_code, products.name as product_name, products.image, products.details as details, product_variants.name as variant')
            ->join('products', 'products.id=adjustment_items.product_id', 'left')
            ->join('product_variants', 'product_variants.id=adjustment_items.option_id', 'left')
            ->group_by('adjustment_items.id')
            ->order_by('id', 'asc');

        $this->db->where('adjustment_id', $adjustment_id);

        $q = $this->db->get('adjustment_items');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getAllProductsOnLocation($warehouse_id)
    {
        $this->db->select('p.*');
        $this->db->from('products p');
        $this->db->join('inventory_movements im', 'p.id = im.product_id', 'inner');
        $this->db->where('im.location_id', $warehouse_id);
        $this->db->group_by('p.id'); // To ensure unique products

        $q = $this->db->get();

        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getAllProducts()
    {
        $q = $this->db->get('products');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getAllVariants()
    {
        $q = $this->db->get('variants');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getAllWarehousesWithPQ($product_id)
    {
        // , wp.rack, wp.avg_cost //  ->join('(SELECT warehouse_id, product_id,  rack, avg_cost FROM sma_warehouses_products where product_id='.$product_id.' order by avg_cost limit 1) as wp', 'wp.warehouse_id = warehouses.id', 'left') 
        // ' . $this->db->dbprefix('warehouses_products') . '.rack, ' . $this->db->dbprefix('warehouses_products') . '.avg_cost'
        //$this->db->select('' . $this->db->dbprefix('warehouses') . '.*, SUM(' . $this->db->dbprefix('inventory_movements') . '.quantity) As quantity,' . $this->db->dbprefix('warehouses_products') . '.rack, ' . $this->db->dbprefix('warehouses_products') . '.avg_cost')
        $this->db->select('' . $this->db->dbprefix('warehouses') . '.*, SUM(' . $this->db->dbprefix('inventory_movements') . '.quantity) As quantity')
            // ->join('warehouses_products', 'warehouses_products.warehouse_id=warehouses.id', 'left')
            ->join('inventory_movements', 'inventory_movements.location_id=warehouses.id', 'left')
            ->where('inventory_movements.product_id', $product_id)
            ->group_by('warehouses.id');
        $q = $this->db->get('warehouses');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }

            return $data;
        }
        return false;
    }

    public function getAllWarehousesWithPQ__BK($product_id)
    {
        $this->db->select('' . $this->db->dbprefix('warehouses') . '.*, SUM(' . $this->db->dbprefix('warehouses_products') . '.quantity) As quantity,' . $this->db->dbprefix('warehouses_products') . '.rack, ' . $this->db->dbprefix('warehouses_products') . '.avg_cost')
            ->join('warehouses_products', 'warehouses_products.warehouse_id=warehouses.id', 'left')
            ->where('warehouses_products.product_id', $product_id)
            ->group_by('warehouses.id');
        $q = $this->db->get('warehouses');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }

            return $data;
        }
        return false;
    }

    public function getBrandByName($name)
    {
        $q = $this->db->get_where('brands', ['name' => $name], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getBrandByID($id)
    {
        $q = $this->db->get_where('brands', ['id' => $id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getCategoryByCode($code)
    {
        $q = $this->db->get_where('categories', ['code' => $code], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getCategoryProducts($category_id)
    {
        $q = $this->db->get_where('products', ['category_id' => $category_id]);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getPrductVariantByPIDandName($product_id, $name)
    {
        $q = $this->db->get_where('product_variants', ['product_id' => $product_id, 'name' => $name], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getProductByCategoryID($id)
    {
        $q = $this->db->get_where('products', ['category_id' => $id], 1);
        if ($q->num_rows() > 0) {
            return true;
        }
        return false;
    }

    public function getProductByCodeForAdj($code)
    {
        //echo $code.'###';
        $this->db->select('*');
        $this->db->from('sma_products');
        $this->db->where('code', $code);

        $query = $this->db->get();
        //echo $query->num_rows();
        // $q = $this->db->get_where('products', ['code' => $code], 1);
        if ($query->num_rows() > 0) {
            return $query->row();
        }

        return false;
    }

    public function getProductByCode($code)
    {
        //echo $code.'###';
        $this->db->select('*');
        $this->db->from('sma_products');

        if (preg_match('/^[A-Za-z]/', $code)) {
            $this->db->where('code', $code);
        } else {
            $this->db->where('CAST(code AS UNSIGNED) = ' . (int) $code, NULL, FALSE);
        }
        $query = $this->db->get();
        //echo $query->num_rows();
        // $q = $this->db->get_where('products', ['code' => $code], 1);
        if ($query->num_rows() > 0) {
            return $query->row();
        }

        return false;
    }

    public function getProductByID($id)
    {
        $q = $this->db->get_where('products', ['id' => $id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function remove_image($id)
    {
        $this->db->where('id', $id);
        $this->db->update('products', ['image' => NULL]);
    }

    public function getProductComboItems($pid)
    {
        $this->db->select($this->db->dbprefix('products') . '.id as id, ' . $this->db->dbprefix('products') . '.code as code, ' . $this->db->dbprefix('combo_items') . '.quantity as qty, ' . $this->db->dbprefix('products') . '.name as name, ' . $this->db->dbprefix('combo_items') . '.unit_price as price')->join('products', 'products.code=combo_items.item_code', 'left')->group_by('combo_items.id');
        $q = $this->db->get_where('combo_items', ['product_id' => $pid]);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }

            return $data;
        }
        return false;
    }

    public function getProductDetail($id)
    {
        $this->db->select($this->db->dbprefix('products') . '.*, ' . $this->db->dbprefix('tax_rates') . '.name as tax_rate_name, ' . $this->db->dbprefix('tax_rates') . '.code as tax_rate_code, c.code as category_code, sc.code as subcategory_code', false)
            ->join('tax_rates', 'tax_rates.id=products.tax_rate', 'left')
            ->join('categories c', 'c.id=products.category_id', 'left')
            ->join('categories sc', 'sc.id=products.subcategory_id', 'left');
        $q = $this->db->get_where('products', ['products.id' => $id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getProductDetails($id)
    {
        $this->db->select($this->db->dbprefix('products') . '.code, ' . $this->db->dbprefix('products') . '.name, ' . $this->db->dbprefix('categories') . '.code as category_code, cost, price, quantity, alert_quantity')
            ->join('categories', 'categories.id=products.category_id', 'left');
        $q = $this->db->get_where('products', ['products.id' => $id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getProductNames($term, $limit = 5)
    {
        $this->db->select('' . $this->db->dbprefix('products') . '.id, code, ' . $this->db->dbprefix('products') . '.name as name, ' . $this->db->dbprefix('products') . '.price as price, ' . $this->db->dbprefix('product_variants') . '.name as vname')
            ->where("type != 'combo' AND "
                . '(' . $this->db->dbprefix('products') . ".name LIKE '%" . $term . "%' OR code LIKE '%" . $term . "%' OR
                concat(" . $this->db->dbprefix('products') . ".name, ' (', code, ')') LIKE '%" . $term . "%')");
        $this->db->join('product_variants', 'product_variants.product_id=products.id', 'left')
            ->where('' . $this->db->dbprefix('product_variants') . '.name', null)
            ->group_by('products.id')->limit($limit);
        $q = $this->db->get('products');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }


    public function getProductOptions($pid)
    {
        $q = $this->db->get_where('product_variants', ['product_id' => $pid]);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getProductOptionsWithWH($pid)
    {
        $this->db->select($this->db->dbprefix('product_variants') . '.*, ' . $this->db->dbprefix('warehouses') . '.name as wh_name, ' . $this->db->dbprefix('warehouses') . '.id as warehouse_id, ' . $this->db->dbprefix('warehouses_products_variants') . '.quantity as wh_qty')
            ->join('warehouses_products_variants', 'warehouses_products_variants.option_id=product_variants.id', 'left')
            ->join('warehouses', 'warehouses.id=warehouses_products_variants.warehouse_id', 'left')
            ->group_by(['' . $this->db->dbprefix('product_variants') . '.id', '' . $this->db->dbprefix('warehouses_products_variants') . '.warehouse_id'])
            ->order_by('warehouses.id');
        $q = $this->db->get_where('product_variants', ['product_variants.product_id' => $pid, 'warehouses_products_variants.quantity !=' => null]);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
    }

    public function getProductPhotos($id)
    {
        $q = $this->db->get_where('product_photos', ['product_id' => $id]);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
    }

    public function getProductQuantity($product_id, $warehouse_id, $batch_no = NULL)
    {
        $this->db->select_sum('quantity');
        if($batch_no !== NULL){
            $this->db->where(['product_id' => $product_id, 'location_id' => $warehouse_id, 'batch_number' => $batch_no]);
        }else{
            $this->db->where(['product_id' => $product_id, 'location_id' => $warehouse_id]);
        }
        $q = $this->db->get('sma_inventory_movements');

        if ($q->num_rows() > 0) {
            return $q->row_array();
        }
        return false;
    }

    /*public function getProductQuantity($product_id, $warehouse)
    {
        $q = $this->db->get_where('warehouses_products', ['product_id' => $product_id, 'warehouse_id' => $warehouse], 1);
        if ($q->num_rows() > 0) {
            return $q->row_array();
        }
        return false;
    }*/

    public function getProductsForPrinting($term, $limit = 5)
    {
        $this->db->select('' . $this->db->dbprefix('products') . '.id, code, ' . $this->db->dbprefix('products') . '.name as name, ' . $this->db->dbprefix('products') . '.price as price, ' . $this->db->dbprefix('products') . '.sequence_code as sequence_code')
            ->where('(' . $this->db->dbprefix('products') . ".name LIKE '%" . $term . "%' OR code LIKE '%" . $term . "%' OR " . $this->db->dbprefix('products') . ".sequence_code LIKE '%" . $term . "%' OR concat(" . $this->db->dbprefix('products') . ".name, ' (', code, ')') LIKE '%" . $term . "%') ")
            ->limit($limit);
        $q = $this->db->get('products');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getProductVariantByID($product_id, $id)
    {
        $q = $this->db->get_where('product_variants', ['product_id' => $product_id, 'id' => $id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getProductVariantByName($product_id, $name)
    {
        $q = $this->db->get_where('product_variants', ['product_id' => $product_id, 'name' => $name], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getProductVariantID($product_id, $name)
    {
        $q = $this->db->get_where('product_variants', ['product_id' => $product_id, 'name' => $name], 1);
        if ($q->num_rows() > 0) {
            $variant = $q->row();
            return $variant->id;
        }
        return false;
    }

    public function getProductWarehouseOptionQty($option_id, $warehouse_id)
    {
        $q = $this->db->get_where('warehouses_products_variants', ['option_id' => $option_id, 'warehouse_id' => $warehouse_id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getProductWarehouseOptions($option_id)
    {
        $q = $this->db->get_where('warehouses_products_variants', ['option_id' => $option_id]);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getProductWithPrice($id, $type, $pid)
    {
        $this->db->select(
            $this->db->dbprefix('products') . '.*, ' .
                $this->db->dbprefix('categories') . '.name as category, ' .
                $this->db->dbprefix('inventory_movements') . '.net_unit_sale as price'
        )
            ->join('categories', 'categories.id = products.category_id', 'left')
            ->join('inventory_movements', 'sma_inventory_movements.product_id = products.id', 'left')
            ->where('inventory_movements.reference_id', $id)
            ->where('inventory_movements.type', $type);

        $q = $this->db->get_where('products', ['products.id' => $pid], 1);

        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getProductWithCategory($id)
    {
        $this->db->select($this->db->dbprefix('products') . '.*, ' . $this->db->dbprefix('categories') . '.name as category')
            ->join('categories', 'categories.id=products.category_id', 'left');
        $q = $this->db->get_where('products', ['products.id' => $id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getPurchasedQty($id)
    {
        $this->db->select('date_format(' . $this->db->dbprefix('purchases') . ".date, '%Y-%M') month, SUM( " . $this->db->dbprefix('purchase_items') . '.quantity ) as purchased, SUM( ' . $this->db->dbprefix('purchase_items') . '.subtotal ) as amount')
            ->from('purchases')
            ->join('purchase_items', 'purchases.id=purchase_items.purchase_id', 'left')
            ->group_by('date_format(' . $this->db->dbprefix('purchases') . ".date, '%Y-%m')")
            ->where($this->db->dbprefix('purchase_items') . '.product_id', $id)
            //->where('DATE(NOW()) - INTERVAL 1 MONTH')
            ->where('DATE_ADD(curdate(), INTERVAL 1 MONTH)')
            ->order_by('date_format(' . $this->db->dbprefix('purchases') . ".date, '%Y-%m') desc")->limit(3);
        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getPurchaseItems($purchase_id, $item_code = '')
    {
        //$q = $this->db->get_where('purchase_items', ['purchase_id' => $purchase_id]);
        // $this->db->where('purchase_id', $purchase_id);

        // if (!empty($item_code)) {
        //     $this->db->where('product_code', $item_code);
        // }

        // $q = $this->db->get('purchase_items');

        $this->db->select('purchase_items.*');
        $this->db->from('purchase_items');
        $this->db->join('products', 'products.id = purchase_items.product_id', 'inner');

        // Adding where clause for purchase_id
        $this->db->where('purchase_items.purchase_id', $purchase_id);

        // Add a condition for item_code if it is not empty and check on the products table
        if (!empty($item_code)) {
            $this->db->where('products.item_code', $item_code);
        }
        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }
    public function getQASuggestions($term, $limit = 5)
    {
        // First check if term matches an AVZ code
        $avz_result = $this->getProductByAVZCode($term);
        if ($avz_result) {
            return [$avz_result];
        }
        
        // Otherwise search by product name/code
        $this->db->select('' . $this->db->dbprefix('products') . '.id, code, ' . $this->db->dbprefix('products') . '.name as name')
            ->where("type != 'combo' AND "
                . '(' . $this->db->dbprefix('products') . ".name LIKE '%" . $term . "%' OR code LIKE '%" . $term . "%' OR
                concat(" . $this->db->dbprefix('products') . ".name, ' (', code, ')') LIKE '%" . $term . "%')")
            ->limit($limit);
        $q = $this->db->get('products');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }
    
    public function getProductByAVZCode($avz_code)
    {
        $this->db->select('
            p.id, 
            p.code, 
            p.name,
            im.batch_number as batch_no,
            im.expiry_date as expiry,
            im.real_unit_cost as purchase_price,
            im.net_unit_cost as cost_price,
            im.net_unit_sale as sale_price,
            im.avz_item_code
        ')
        ->from('inventory_movements im')
        ->join('products p', 'p.id = im.product_id', 'left')
        ->where('im.avz_item_code', $avz_code)
        ->where('p.type !=', 'combo')
        ->limit(1);
        
        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getProductBatchesForWarehouse($product_id, $warehouse_id)
    {
        $this->db->select('
            batch_number as batch_no, 
            avz_item_code,
            expiry_date as expiry, 
            SUM(quantity) as quantity,
            MAX(real_unit_cost) as purchase_price,
            MAX(net_unit_cost) as cost_price,
            MAX(net_unit_sale) as sale_price
        ')
            ->from('inventory_movements')
            ->where('product_id', $product_id)
            ->where('location_id', $warehouse_id)
            ->where('batch_number IS NOT NULL')
            ->where('batch_number !=', '')
            ->group_by(['avz_item_code'])
            ->having('SUM(quantity) >', 0)
            ->order_by('expiry_date', 'ASC');
        
        $q = $this->db->get();
        
        if ($q->num_rows() > 0) {
            return $q->result();
        }
        return false;
    }

    public function getSoldQty($id)
    {
        $this->db->select('date_format(' . $this->db->dbprefix('sales') . ".date, '%Y-%M') month, SUM( " . $this->db->dbprefix('sale_items') . '.quantity ) as sold, SUM( ' . $this->db->dbprefix('sale_items') . '.subtotal ) as amount')
            ->from('sales')
            ->join('sale_items', 'sales.id=sale_items.sale_id', 'left')
            ->group_by('date_format(' . $this->db->dbprefix('sales') . ".date, '%Y-%m')")
            ->where($this->db->dbprefix('sale_items') . '.product_id', $id)
            //->where('DATE(NOW()) - INTERVAL 1 MONTH')
            ->where('DATE_ADD(curdate(), INTERVAL 1 MONTH)')
            ->order_by('date_format(' . $this->db->dbprefix('sales') . ".date, '%Y-%m') desc")->limit(3);
        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getStockCountItems($stock_count_id)
    {
        $q = $this->db->get_where('stock_count_items', ['stock_count_id' => $stock_count_id]);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return null;
    }

    public function getStockCountProducts($warehouse_id, $type, $categories = null, $brands = null)
    {
        $this->db->select("
        {$this->db->dbprefix('products')}.id as id, 
        {$this->db->dbprefix('products')}.code as code, 
        {$this->db->dbprefix('products')}.cost as item_cost, 
        {$this->db->dbprefix('products')}.price as sale_price, 
        {$this->db->dbprefix('products')}.name as name, 
        {$this->db->dbprefix('warehouses_products')}.quantity as quantity, 
        {$this->db->dbprefix('warehouses_products')}.batchno as batchno, 
        {$this->db->dbprefix('warehouses_products')}.expiry as expiry, 
        {$this->db->dbprefix('warehouses_products')}.purchase_cost")
            ->join('warehouses_products', 'warehouses_products.product_id=products.id', 'left')
            ->where('warehouses_products.warehouse_id', $warehouse_id)
            ->where('products.type', 'standard')
            ->order_by('products.code', 'asc');
        if ($categories) {
            $r = 1;
            $this->db->group_start();
            foreach ($categories as $category) {
                if ($r == 1) {
                    $this->db->where('products.category_id', $category);
                } else {
                    $this->db->or_where('products.category_id', $category);
                }
                $r++;
            }
            $this->db->group_end();
        }
        if ($brands) {
            $r = 1;
            $this->db->group_start();
            foreach ($brands as $brand) {
                if ($r == 1) {
                    $this->db->where('products.brand', $brand);
                } else {
                    $this->db->or_where('products.brand', $brand);
                }
                $r++;
            }
            $this->db->group_end();
        }

        $q = $this->db->get('products');

        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getStockCountProductVariants($warehouse_id, $product_id)
    {
        $this->db->select("{$this->db->dbprefix('product_variants')}.name, {$this->db->dbprefix('warehouses_products_variants')}.quantity as quantity, {$this->db->dbprefix('products')}.price as sale_price, {$this->db->dbprefix('warehouses_products')}.batchno as batchno, {$this->db->dbprefix('warehouses_products')}.expiry as expiry, {$this->db->dbprefix('warehouses_products')}.purchase_cost")
            ->join('warehouses_products_variants', 'warehouses_products_variants.option_id=product_variants.id', 'left');
        $q = $this->db->get_where('product_variants', ['product_variants.product_id' => $product_id, 'warehouses_products_variants.warehouse_id' => $warehouse_id]);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
    }

    public function getStouckCountByID($id)
    {
        $q = $this->db->get_where('stock_counts', ['id' => $id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getSubCategories($parent_id)
    {
        $this->db->select('id as id, name as text')
            ->where('parent_id', $parent_id)->order_by('name');
        $q = $this->db->get('categories');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getSubCategoryProducts($subcategory_id)
    {
        $q = $this->db->get_where('products', ['subcategory_id' => $subcategory_id]);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getSupplierByName($name)
    {
        $q = $this->db->get_where('companies', ['name' => $name, 'group_name' => 'supplier'], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getTaxRateByName($name)
    {
        $q = $this->db->get_where('tax_rates', ['name' => $name], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getTransferItems($transfer_id)
    {
        $q = $this->db->get_where('purchase_items', ['transfer_id' => $transfer_id]);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getUnitByCode($code)
    {
        $q = $this->db->get_where('units', ['code' => $code], 1);
        //echo $this->db->last_query();exit;
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getWarehouseProductVariant($warehouse_id, $product_id, $option_id = null)
    {
        $q = $this->db->get_where('warehouses_products_variants', ['product_id' => $product_id, 'option_id' => $option_id, 'warehouse_id' => $warehouse_id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function has_purchase($product_id, $warehouse_id = null)
    {
        if ($warehouse_id) {
            $this->db->where('warehouse_id', $warehouse_id);
        }
        $q = $this->db->get_where('purchase_items', ['product_id' => $product_id], 1);
        if ($q->num_rows() > 0) {
            return true;
        }
        return false;
    }

    public function insertQuantity($product_id, $warehouse_id, $quantity, $rack = null)
    {
        $product = $this->site->getProductByID($product_id);
        if ($this->db->insert('warehouses_products', ['product_id' => $product_id, 'warehouse_id' => $warehouse_id, 'quantity' => $quantity, 'rack' => $rack, 'avg_cost' => $product->cost])) {
            $this->site->syncProductQty($product_id, $warehouse_id);
            return true;
        }
        return false;
    }

    public function products_count($category_id, $subcategory_id = null)
    {
        if ($category_id) {
            $this->db->where('category_id', $category_id);
        }
        if ($subcategory_id) {
            $this->db->where('subcategory_id', $subcategory_id);
        }
        $this->db->from('products');
        return $this->db->count_all_results();
    }

    public function reverseAdjustment($id)
    {
        if ($products = $this->getAdjustmentItems($id)) {
            foreach ($products as $adjustment) {
                $clause = ['product_id' => $adjustment->product_id, 'warehouse_id' => $adjustment->warehouse_id, 'option_id' => $adjustment->option_id, 'status' => 'received'];
                $qty = $adjustment->type == 'subtraction' ? (0 + $adjustment->quantity) : (0 - $adjustment->quantity);
                $this->site->setPurchaseItem($clause, $qty);
                $this->site->syncProductQty($adjustment->product_id, $adjustment->warehouse_id);
                if ($adjustment->option_id) {
                    $this->site->syncVariantQty($adjustment->option_id, $adjustment->warehouse_id, $adjustment->product_id);
                }
            }
        }
    }

    public function setAvgCost($id)
    {
        $warehouses = $this->db->select('id')->get('warehouses')->result();
        $purchase_items = $this->db->select('base_unit_cost, unit_cost, quantity_balance, warehouse_id, option_id, product_unit_id')->get_where('purchase_items', ['product_id' => $id, 'quantity_balance >' => 0])->result();
        foreach ($warehouses as $warehouse) {
            $total_cost = 0;
            $total_quantity = 0;
            foreach ($purchase_items as $pi) {
                if ($pi->warehouse_id == $warehouse->id) {
                    $total_quantity += $pi->quantity_balance;
                    if ($pi->base_unit_cost) {
                        $total_cost += $pi->base_unit_cost * $pi->quantity_balance;
                    } elseif ($pi->product_unit_id) {
                        $unit = $this->site->getUnitByID($pi->product_unit_id);
                        $base_cost = $this->site->convertToBase($unit, $pi->unit_cost);
                        $total_cost += $base_cost * $pi->quantity_balance;
                    } else {
                        $total_cost += $pi->unit_cost * $pi->quantity_balance;
                    }
                }
            }
            if ($total_cost && $total_quantity) {
                $avg_cost = $total_cost / $total_quantity;
                $this->db->update('warehouses_products', ['avg_cost' => $avg_cost], ['product_id' => $id, 'warehouse_id' => $warehouse->id]);
            }
        }
    }

    public function updateProductSlugs($slug, $product_id)
    {
        $this->db->update('sma_products', ['slug' => $slug], ['id' => $product_id]);
    }

    public function updateProductImages($imgArr)
    {
        foreach ($imgArr as $img) {
            echo 'Upading Code: ' . $img . '<br />';
            $this->db->update('sma_products', ['image' => trim($img) . '.jpg'], ['code' => trim($img)]);
        }
    }

    public function setRack($data)
    {
        if ($this->db->update('warehouses_products', ['rack' => $data['rack']], ['product_id' => $data['product_id'], 'warehouse_id' => $data['warehouse_id']])) {
            return true;
        }
        return false;
    }

    public function syncAdjustment($data = [])
    {
        if (!empty($data)) {
            //echo '<pre>';print_r($data);exit;
            if($data['type'] == 'subtraction' && $data['quantity'] > 0){
                $avz_item_code = $data['avz_item_code'];
            }else if($data['type'] == 'addition' && $data['quantity'] > 0 && !empty($data['avz_item_code'])){
                $avz_item_code = $data['avz_item_code'];
            }else{
                $avz_item_code = $this->sma->generateUUIDv4();
            }

            $clause = ['product_id' => $data['product_id'], 'unit_cost' => $data['unit_cost'], 'sale_price' => $data['sale_price'], 'vat' => $data['vat'], 'batchno' => $data['batchno'], 'expiry' => $data['expiry'], 'option_id' => $data['option_id'], 'warehouse_id' => $data['warehouse_id'], 'status' => 'received'];
            $qty = $data['type'] == 'subtraction' ? 0 - $data['quantity'] : 0 + $data['quantity'];
            //$this->site->setAdjustmentPurchaseItem($clause, $qty, $avz_item_code);

            /*$this->site->syncProductQty($data['product_id'], $data['warehouse_id'], $data['batchno']);
            if ($data['option_id']) {
                $this->site->syncVariantQty($data['option_id'], $data['warehouse_id'], $data['product_id']);
            }*/
            $movement_type = $data['type'] == 'subtraction' ? 'adjustment_decrease' : 'adjustment_increase';
            $adjustment_id = isset($data['adjustment_id']) ? $data['adjustment_id'] : null;
            //$this->Inventory_model->add_movement($data['product_id'], $data['batchno'], $movement_type, $data['quantity'], $data['warehouse_id'], $adjustment_id, $data['unit_cost'], $data['expiry'], $data['sale_price'], $data['unit_cost'], $avz_item_code);
            //echo 'here in inventory upload....';
            $this->Inventory_model->add_movement($data['product_id'], $data['batchno'], $movement_type, $data['quantity'], $data['warehouse_id'], $adjustment_id, $data['unit_cost'], $data['expiry'], $data['sale_price'], $data['real_unit_cost'], $avz_item_code, 0, NULL, $data['sale_price'], date('Y-m-d H:i:s'));
        }
    }

    public function syncVariantQty($option_id)
    {
        $wh_pr_vars = $this->getProductWarehouseOptions($option_id);
        $qty = 0;
        foreach ($wh_pr_vars as $row) {
            $qty += $row->quantity;
        }
        if ($this->db->update('product_variants', ['quantity' => $qty], ['id' => $option_id])) {
            return true;
        }
        return false;
    }

    public function totalCategoryProducts($category_id)
    {
        $q = $this->db->get_where('products', ['category_id' => $category_id]);
        return $q->num_rows();
    }

    public function updateAdjustment($id, $data, $products)
    {
        $this->reverseAdjustment($id);
        if ($this->db->update('adjustments', $data, ['id' => $id]) && $this->db->delete('adjustment_items', ['adjustment_id' => $id])) {
            foreach ($products as $product) {
                $product['adjustment_id'] = $id;
                $this->db->insert('adjustment_items', $product);
                $this->syncAdjustment($product);
            }
            return true;
        }
        return false;
    }

    public function updatePrice($data = [])
    {
        if ($this->db->update_batch('products', $data, 'code')) {
            return true;
        }
        return false;
    }

    public function updateProduct($id, $data, $items, $warehouse_qty, $product_attributes, $photos, $update_variants)
    {
        if ($this->db->update('products', $data, ['id' => $id])) {
            $cat_id_q = $this->db->get_where('categories', ['id' => $data['category_id']], 1);
            $category_code = 0;
            if ($cat_id_q->num_rows() > 0) {
                $row_cat = $cat_id_q->row();
                $category_code = $row_cat->category_code;
                $formatted_id = str_pad($id, 6, '0', STR_PAD_LEFT);
                // Concatenate the category code and formatted ID
                //$item_code = $category_code . $formatted_id;
                //$this->db->update('sma_products', ['item_code' => $item_code], ['id' => $id]);
            }
            if ($items) {
                $this->db->delete('combo_items', ['product_id' => $id]);
                foreach ($items as $item) {
                    $item['product_id'] = $id;
                    $this->db->insert('combo_items', $item);
                }
            }

            $tax_rate = $this->site->getTaxRateByID($data['tax_rate']);

            if ($warehouse_qty && !empty($warehouse_qty)) {
                foreach ($warehouse_qty as $wh_qty) {
                    $this->db->update('warehouses_products', ['rack' => $wh_qty['rack']], ['product_id' => $id, 'warehouse_id' => $wh_qty['warehouse_id']]);
                }
            }

            if (!empty($update_variants)) {
                foreach ($update_variants as $variant) {
                    $vr = $this->getProductVariantByName($id, $variant['name']);
                    if ($vr) {
                        $this->db->update('product_variants', $variant, ['id' => $vr->id]);
                    } else {
                        if ($variant['id']) {
                            $this->db->delete('product_variants', ['id' => $variant['id']]);
                        } else {
                            $this->db->insert('product_variants', $variant);
                        }
                    }
                }
            }

            if ($photos) {
                foreach ($photos as $photo) {
                    $this->db->insert('product_photos', ['product_id' => $id, 'photo' => $photo]);
                }
            }

            if ($product_attributes) {
                foreach ($product_attributes as $pr_attr) {
                    $pr_attr['product_id'] = $id;
                    $variant_warehouse_id = $pr_attr['warehouse_id'];
                    unset($pr_attr['warehouse_id']);
                    $this->db->insert('product_variants', $pr_attr);
                    $option_id = $this->db->insert_id();

                    if ($pr_attr['quantity'] != 0) {
                        $this->db->insert('warehouses_products_variants', ['option_id' => $option_id, 'product_id' => $id, 'warehouse_id' => $variant_warehouse_id, 'quantity' => $pr_attr['quantity']]);

                        $tax_rate_id = $tax_rate ? $tax_rate->id : null;
                        $tax = $tax_rate ? (($tax_rate->type == 1) ? $tax_rate->rate . '%' : $tax_rate->rate) : null;
                        $unit_cost = $data['cost'];
                        if ($tax_rate) {
                            if ($tax_rate->type == 1 && $tax_rate->rate != 0) {
                                if ($data['tax_method'] == '0') {
                                    $pr_tax_val = ($data['cost'] * $tax_rate->rate) / (100 + $tax_rate->rate);
                                    $net_item_cost = $data['cost'] - $pr_tax_val;
                                    $item_tax = $pr_tax_val * $pr_attr['quantity'];
                                } else {
                                    $net_item_cost = $data['cost'];
                                    $pr_tax_val = ($data['cost'] * $tax_rate->rate) / 100;
                                    $unit_cost = $data['cost'] + $pr_tax_val;
                                    $item_tax = $pr_tax_val * $pr_attr['quantity'];
                                }
                            } else {
                                $net_item_cost = $data['cost'];
                                $item_tax = $tax_rate->rate;
                            }
                        } else {
                            $net_item_cost = $data['cost'];
                            $item_tax = 0;
                        }

                        $subtotal = (($net_item_cost * $pr_attr['quantity']) + $item_tax);
                        $item = [
                            'product_id' => $id,
                            'product_code' => $data['code'],
                            'product_name' => $data['name'],
                            'net_unit_cost' => $net_item_cost,
                            'unit_cost' => $unit_cost,
                            'quantity' => $pr_attr['quantity'],
                            'option_id' => $option_id,
                            'quantity_balance' => $pr_attr['quantity'],
                            'quantity_received' => $pr_attr['quantity'],
                            'item_tax' => $item_tax,
                            'tax_rate_id' => $tax_rate_id,
                            'tax' => $tax,
                            'subtotal' => $subtotal,
                            'warehouse_id' => $variant_warehouse_id,
                            'date' => date('Y-m-d'),
                            'status' => 'received',
                        ];
                        $item['option_id'] = !empty($item['option_id']) && is_numeric($item['option_id']) ? $item['option_id'] : null;
                        $this->db->insert('purchase_items', $item);
                    }
                }
            }

            $this->site->syncQuantity(null, null, null, $id);
            return true;
        }
        return false;
    }

    public function updateProductOptionQuantity($option_id, $warehouse_id, $quantity, $product_id)
    {
        if ($option = $this->getProductWarehouseOptionQty($option_id, $warehouse_id)) {
            if ($this->db->update('warehouses_products_variants', ['quantity' => $quantity], ['option_id' => $option_id, 'warehouse_id' => $warehouse_id])) {
                $this->site->syncVariantQty($option_id, $warehouse_id);
                return true;
            }
        } else {
            if ($this->db->insert('warehouses_products_variants', ['option_id' => $option_id, 'product_id' => $product_id, 'warehouse_id' => $warehouse_id, 'quantity' => $quantity])) {
                $this->site->syncVariantQty($option_id, $warehouse_id);
                return true;
            }
        }
        return false;
    }

    public function updateQuantity($product_id, $warehouse_id, $quantity, $rack = null)
    {
        $data = $rack ? ['quantity' => $quantity, 'rack' => $rack] : $data = ['quantity' => $quantity];
        if ($this->db->update('warehouses_products', $data, ['product_id' => $product_id, 'warehouse_id' => $warehouse_id])) {
            $this->site->syncProductQty($product_id, $warehouse_id);
            return true;
        }
        return false;
    }

    public function getProductAvzCode($product_id, $purchase_id)
    {
        $this->db->select('avz_item_code');
        $this->db->from('purchase_items');
        $this->db->where('purchase_id', $purchase_id);
        $this->db->where('product_id', $product_id);
        $this->db->limit(1);
        $q = $this->db->get();

        if ($q->num_rows() > 0) {
            return $q->row()->avz_item_code;
        }
        return false;
    }

    public function getProductsBarcodeItemsForTransfer($transfer_id = '', $item_code = '', $warehouse_id = '', $inventory_id = '')
    {
        $where = '';
        if (!empty($transfer_id)) {
            $where = " AND a.type='transfer_out' AND a.reference_id = " . $transfer_id;
        }
        // Add a condition for item_code if it is not empty and check on the products table
        if (!empty($item_code)) {
            $where .= " AND b.item_code = " . $item_code;
        }
        if (!empty($warehouse_id)) {
            $where .= " AND a.location_id = " . $warehouse_id;
        }
        if (!empty($inventory_id)) {
            $where .= " AND a.id = " . $inventory_id;
        }

        $sql = "SELECT a.id,
        b.id as product_id,
        b.item_code as code,
        a.avz_item_code,
        b.name,
        abs(sum(a.quantity)) as quantity,
        a.net_unit_sale as price,
        a.batch_number as batchno,
        a.expiry_date as expiry
        FROM `sma_inventory_movements` a
        JOIN sma_products b ON a.product_id = b.id
        WHERE 1=1 " . $where . "
        group by a.avz_item_code , a.location_id";

        $q = $this->db->query($sql);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getProductsBarcodeItems($purchase_id = '', $item_code = '', $warehouse_id = '', $inventory_id = '')
    {
        $where = '';
        if (!empty($purchase_id)) {
            $where = " AND a.type='purchase' AND a.reference_id = " . $purchase_id;
        }
        // Add a condition for item_code if it is not empty and check on the products table
        /*if (!empty($item_code)) {
            $where .= " AND b.item_code = ".$item_code;
        }*/

        if (!empty($item_code)) {
            $where .= " AND (b.item_code = " . $this->db->escape($item_code) . " OR b.code = " . $this->db->escape($item_code) . ")";
        }
        if (!empty($warehouse_id)) {
            $where .= " AND a.location_id = " . $warehouse_id;
        }
        if (!empty($inventory_id)) {
            $where .= " AND a.id = " . $inventory_id;
        }

        $sql = "SELECT a.id,
        b.id as product_id,
        b.item_code as code,
        a.avz_item_code,
        b.name,
        sum(a.quantity) as quantity,
        a.net_unit_sale as price,
        a.batch_number as batchno,
        a.expiry_date as expiry
        FROM `sma_inventory_movements` a
        JOIN sma_products b ON a.product_id = b.id
        WHERE 1=1 " . $where . "
        group by a.avz_item_code , a.location_id";

        $q = $this->db->query($sql);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function existsByGTIN($gtin)
    {
        return $this->db->where('code', $gtin)->count_all_results('sma_products') > 0;
    }

    public function insert_batch($data)
    {
        if (!empty($data)) {
            return $this->db->insert_batch("sma_products", $data);
        }
        return false;
    }

    public function getTotalInventoryQuantity($warehouse_id, $item_id)
    {
        $this->db->select("SUM(quantity) as total_quantity");
        $this->db->from('sma_inventory_movements');
        $this->db->where('location_id', $warehouse_id);
        $this->db->where('product_id', $item_id);
        $query = $this->db->get();

        if ($query->num_rows() > 0) {
            $row = $query->row();
            return $row->total_quantity;
        }
        return false;
    }

    public function check_inventory($warehouse_id, $item_id, $item_batchno, $item_expiry, $item_quantity, $avz_code)
    {
        $this->db->select("im.net_unit_sale, 
                            im.net_unit_cost, 
                            im.real_unit_cost,
                            im.real_unit_sale,
                            im.customer_id,
                            im.product_id,
                            im.batch_number as batchno, 
                            im.expiry_date as expiry,
                            im.avz_item_code,
                            (SUM(im.quantity)) AS total_quantity", false);
        $this->db->from('sma_inventory_movements im');
        $this->db->where('im.location_id', $warehouse_id);
        $this->db->where('im.product_id', $item_id);
        $this->db->where('im.batch_number', $item_batchno);
        $this->db->where('im.expiry_date', $item_expiry);
        if ($avz_code != '' && $avz_code != 'null') {
            $this->db->where('im.avz_item_code', $avz_code);
        }
        $this->db->group_by(['im.product_id', 'im.batch_number', 'im.expiry_date']);
        $this->db->having('total_quantity >=', $item_quantity);
        $query = $this->db->get();

        if ($query->num_rows() > 0) {
            $rows = $query->result();
            return $rows[0];
        } else {
            return false;
        }
    }

    public function getAVZItemCodeDetails()
    {
        $item_id = $this->input->get('item_id');
        $warehouse_id = $this->input->get('warehouse_id'); // Optionally filter by warehouse if needed
        $customer_id = $this->input->get('customer_id');
        $quote_id = $this->input->get('quote_id');
        //echo json_encode(['status' => 'error', 'message' => $customer_id.'-'.$item_id.'-'.$warehouse_id]);
        // return;
        // Validate that avz_item_code is provided
        if (!$item_id && !$this->input->get('purchase_id') && !$quote_id) {
            echo json_encode(['status' => 'error', 'message' => 'No item code provided']);
            return;
        }


        if ($customer_id) {
            $this->db->select("im.net_unit_sale, 
                            im.net_unit_cost, 
                            im.real_unit_cost,
                            im.real_unit_sale,
                            im.customer_id,
                            im.product_id,
                            pr.name as product_name, im.batch_number as batchno, im.expiry_date as expiry,
                            pr.tax_rate, pr.type, pr.unit, pr.code as product_code, im.avz_item_code,
                            (SUM(CASE WHEN im.type = 'customer_return' AND im.customer_id = " . $customer_id . " THEN -1*im.quantity ELSE 0 END) - SUM(CASE WHEN im.type IN ('sale','pos') AND im.customer_id = " . $customer_id . " THEN im.quantity ELSE 0 END) ) AS total_quantity", false);
            $this->db->from('sma_inventory_movements im');
            $this->db->join('sma_products pr', 'pr.id = im.product_id', 'inner');
            $this->db->where('im.location_id', $warehouse_id);
            $this->db->where('im.product_id', $item_id);
            $this->db->where('im.customer_id', $customer_id);
            $this->db->group_by(['im.avz_item_code', 'im.batch_number', 'im.expiry_date']);
            $this->db->having('total_quantity !=', 0);
            $query = $this->db->get();
        } else if ($this->input->get('purchase_id')) {

            // $this->db->select("im.net_unit_sale, 
            //                 im.net_unit_cost, 
            //                 im.real_unit_cost,
            //                 im.product_id,
            //                 pr.name as product_name, im.batch_number as batchno, im.expiry_date as expiry,
            //                 p.supplier_id, p.supplier,
            //                 SUM(IFNULL(im.quantity, 0)) as total_quantity,
            //                 pr.tax_rate, pr.type, pr.unit, pr.code as product_code, im.avz_item_code", false);
            $sql = "
                SELECT 
                    pi.purchase_id,
                    pi.product_id,
                    pr.name AS product_name,
                    pr.code as product_code,
                    pr.unit,
                    pr.type,
                    pr.tax_rate,
                    p.supplier_id,
                    p.supplier,
                    im_summary.batch_number AS batchno,
                    im_summary.expiry_date AS expiry,
                    im_summary.net_unit_sale,
                    im_summary.net_unit_cost,
                    im_summary.real_unit_cost,
                    im_summary.avz_item_code,
                    im_summary.total_quantity
                FROM sma_purchase_items pi
                JOIN sma_purchases p 
                    ON p.id = pi.purchase_id
                JOIN sma_products pr 
                    ON pr.id = pi.product_id
                JOIN (
                    SELECT 
                        avz_item_code,
                        batch_number,
                        expiry_date,
                        net_unit_sale,
                        net_unit_cost,
                        real_unit_cost,
                        SUM(quantity) AS total_quantity
                    FROM sma_inventory_movements
                    WHERE location_id = " . $warehouse_id . " 

                    GROUP BY avz_item_code, batch_number, expiry_date
                ) im_summary
                    ON im_summary.avz_item_code = pi.avz_item_code
                WHERE pi.purchase_id = " . $this->input->get('purchase_id') . " 
                AND im_summary.total_quantity > 0";
            $query = $this->db->query($sql);

            //echo $this->db->last_query();exit;
        } else if ($quote_id) {
            $this->db->select("pr.price as net_unit_sale, 
                            im.net_unit_cost, 
                            im.real_unit_cost,
                            im.real_unit_sale,
                            im.customer_id,
                            im.product_id,
                            (SUM(im.bonus)) AS max_bonus,
                            pr.name as product_name, im.batch_number as batchno, im.expiry_date as expiry,
                            pr.tax_rate, pr.type, pr.unit, pr.code as product_code, im.avz_item_code,
                            pr.cash_discount, pr.credit_discount, pr.cash_dis2, pr.cash_dis3, pr.credit_dis2, pr.credit_dis3,
                            pu.supplier,
                            (SUM(im.quantity)) AS total_quantity", false);
            $this->db->from('sma_inventory_movements im');
            $this->db->join('sma_products pr', 'pr.id = im.product_id', 'inner');
            $this->db->join('sma_purchases pu', 'pu.id = im.reference_id AND im.type = "purchase"', 'left');
            $this->db->where('im.location_id', $warehouse_id);
            $this->db->where('im.product_id', $item_id);
            $this->db->group_by(['im.avz_item_code']);
            $this->db->order_by('im.expiry_date', 'asc');
            $this->db->having('total_quantity !=', 0);
            $query = $this->db->get();
            //echo $this->db->last_query();exit;
            if ($query->num_rows() <= 0) {
                $this->db->select("pr.price as net_unit_sale, 
                                pr.cost as net_unit_cost, 
                                pr.cost as real_unit_cost,
                                pr.id as product_id,
                                pr.cash_discount, pr.credit_discount, pr.cash_dis2, pr.cash_dis3, pr.credit_dis2, pr.credit_dis3,
                                pu.supplier,
                                pr.name as product_name, im.batch_number as batchno, im.expiry_date as expiry,
                                (SUM(im.bonus)) AS max_bonus,
                                SUM(IFNULL(im.quantity, 0)) as total_quantity,
                                pr.tax_rate, pr.type, pr.unit, pr.code as product_code, im.avz_item_code", false);
                $this->db->from('sma_products pr');
                $this->db->join('sma_inventory_movements im', 'im.product_id = pr.id', 'left');
                $this->db->join('sma_purchases pu', 'pu.id = im.reference_id AND im.type = "purchase"', 'left');
                $this->db->where('pr.id', $item_id);
                $this->db->where('im.location_id', $warehouse_id);
                $this->db->group_by(['im.avz_item_code']);
                $query = $this->db->get();
            }
        } else {
            /*$this->db->select('pi.avz_item_code, pi.product_code, im.net_unit_sale, im.net_unit_cost, im.real_unit_cost, pr.tax_rate, pr.type, pr.unit, p.supplier_id, p.supplier, pi.product_id, pi.product_name, pi.batchno, pi.expiry, SUM(IFNULL(im.quantity, 0)) as total_quantity');
            $this->db->from('sma_purchase_items pi');
            $this->db->join('sma_purchases p', 'p.id = pi.purchase_id', 'inner');
            $this->db->join('sma_inventory_movements im', 'pi.avz_item_code = im.avz_item_code', 'inner');
            $this->db->join('sma_products pr', 'pr.id = pi.product_id', 'inner');
            $this->db->where('pi.product_id', $item_id);
            if ($warehouse_id) {
                $this->db->where('pi.warehouse_id', $warehouse_id);
                $this->db->where('im.location_id', $warehouse_id);
            }
            $this->db->group_by(['pi.warehouse_id', 'pi.avz_item_code', 'pi.expiry']);
            $this->db->having('total_quantity >', 0);
            $query = $this->db->get();*/


            $this->db->select("im.net_unit_sale, 
                            im.net_unit_cost, 
                            im.real_unit_cost,
                            im.product_id,
                            pr.name as product_name, im.batch_number as batchno, im.expiry_date as expiry,
                            p.supplier_id, p.supplier,
                            SUM(IFNULL(im.quantity, 0)) as total_quantity,
                            pr.tax_rate, pr.type, pr.unit, pr.code as product_code, im.avz_item_code", false);
            $this->db->from('sma_inventory_movements im');
            $this->db->join('sma_purchases p', 'p.id = im.reference_id AND im.type = "purchase"', 'left');
            $this->db->join('sma_products pr', 'pr.id = im.product_id', 'left');
            if ($warehouse_id) {
                $this->db->where('im.location_id', $warehouse_id);
            }
            $this->db->where('im.product_id', $item_id);

            $this->db->group_by(['im.avz_item_code', 'im.batch_number', 'im.expiry_date']);
            $this->db->having('total_quantity !=', 0);
            $query = $this->db->get();
        }

        if ($query->num_rows() > 0) {
            $rows = $query->result();
            $pr = [];
            $r = 0;
            $count = 0;

            foreach ($rows as $row) {
                $c = uniqid(mt_rand(), true);
                $option = false;
                $row->quantity = $row->total_quantity;
                //$row->item_tax_method  = $row->tax_method;
                $row->base_quantity = 0;
                //$row->net_unit_cost    = 0; // commented because coming in query
                //$row->base_unit        = $row->unit;
                //$row->base_unit_cost   = $row->cost;
                //$row->unit             = $row->purchase_unit ? $row->purchase_unit : $row->unit;
                $row->qty = $row->total_quantity;
                $row->discount = '0';
                if ($this->input->get('purchase_id')) {
                    $row->current_qty = $row->total_quantity;
                }

                $row->quantity_balance = 0;
                $row->ordered_quantity = 0;
                $row->cost = $row->real_unit_cost;

                $row->batch_no = $row->batchno;
                $row->batchQuantity = 0;
                $row->batchPurchaseCost = 0;
                //$row->expiry  = null;

                $row->id = $row->product_id;
                $row->name = $row->product_name;
                $row->code = $row->product_code;

                $row->base_unit = $row->unit;

                $units = $this->site->getUnitsByBUID($row->base_unit);
                $tax_rate = $this->site->getTaxRateByID($row->tax_rate);

                //$tax_rate = $row->tax_rate = 5 ? 15 : 0;
                $batches = [];
                $options = [];
                $total_quantity = $row->total_quantity;
                $count++;
                $row->serial_no = $count;

                if ($this->input->get('purchase_id')) {
                    $randid = sha1($c . $r);
                    $pr[$randid] = [
                        'id' => $randid,
                        'item_id' => $row->product_id,
                        'label' => $row->product_name . ' (' . $row->code . ')',
                        'row' => $row,
                        'tax_rate' => $tax_rate,
                        'units' => $units,
                        'options' => $options,
                        'batches' => $batches,
                        'total_quantity' => $total_quantity
                    ];
                } else {
                    $pr[] = [
                        'id' => sha1($c . $r),
                        'item_id' => $row->product_id,
                        'label' => $row->product_name . ' (' . $row->code . ')',
                        'row' => $row,
                        'tax_rate' => $tax_rate,
                        'units' => $units,
                        'options' => $options,
                        'batches' => $batches,
                        'total_quantity' => $total_quantity
                    ];
                }


                $r++;
            }
            if ($this->input->get('purchase_id')) {
                return $pr;
            } else {
                $this->sma->send_json($pr);
            }
            // print_r($pr);exit;  


        } else {
            // Return an error if no records found
            echo json_encode(['status' => 'error', 'message' => 'No items found for this item code']);
        }
    }

   public function getProductNamesBySupplier($term, $supplier_id)
{
    $this->db->select('
        p.id, 
        p.code, 
        p.name, 
        d.deal_type, 
        d.dis1_percentage,
        d.dis2_percentage,
        d.dis3_percentage,
        d.deal_percentage, 
        d.threshold
    ');
    $this->db->from('products p');
    $this->db->join('purchase_contract_deal_items d', 'd.item_id = p.id', 'left'); // LEFT JOIN to include all products

    //$this->db->where('p.supplier1', $supplier_id);
    $this->db->group_start()
        ->like('p.name', $term)
        ->or_like('p.code', $term)
        ->or_like("CONCAT(p.name, ' (', p.code, ')')", $term, 'both', false)
    ->group_end();

    $this->db->group_by('p.id');

    $q = $this->db->get();

    if ($q->num_rows() > 0) {
        $data = [];
        foreach ($q->result() as $row) {
            $deal_available = ($row->deal_type && $row->deal_percentage  && $row->threshold) ?? false;
            $data[] = (object)[
                'id'             => $row->id,
                'code'           => $row->code,
                'name'           => $row->name,
                'price'          => $row->price ?? '',
                'deal_type'      => $row->deal_type ?? '',
                'dis1_percentage'=> $row->dis1_percentage ?? '',
                'dis2_percentage'=> $row->dis2_percentage ?? '',
                'dis3_percentage'=> $row->dis3_percentage ?? '',
                'deal_percentage'=> $row->deal_percentage ?? '',
                'threshold'      => $row->threshold ?? '',
                'deal_available' => $deal_available
            ];
        }
        return $data;
    }
    return false;
}

}
