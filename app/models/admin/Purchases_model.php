<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Purchases_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function addExpense($data = [], $attachments = [])
    {
        if ($this->db->insert('expenses', $data)) {
            $expense_id = $this->db->insert_id();
            if ($this->site->getReference('ex') == $data['reference']) {
                $this->site->updateReference('ex');
            }
            if (!empty($attachments)) {
                foreach ($attachments as $attachment) {
                    $attachment['subject_id']   = $expense_id;
                    $attachment['subject_type'] = 'expense';
                    $this->db->insert('attachments', $attachment);
                }
            }
            return true;
        }
        return false;
    }

    public function addPayment($data = [])
    {
        if ($this->db->insert('payments', $data)) {
            if ($this->site->getReference('ppay') == $data['reference_no']) {
                $this->site->updateReference('ppay');
            }
            $this->site->syncPurchasePayments($data['purchase_id']);
            return true;
        }
        return false;
    }

    public function addProductOptionQuantity($option_id, $warehouse_id, $quantity, $product_id)
    {
        if ($option = $this->getProductWarehouseOptionQty($option_id, $warehouse_id)) {
            $nq = $option->quantity + $quantity;
            if ($this->db->update('warehouses_products_variants', ['quantity' => $nq], ['option_id' => $option_id, 'warehouse_id' => $warehouse_id])) {
                return true;
            }
        } else {
            if ($this->db->insert('warehouses_products_variants', ['option_id' => $option_id, 'product_id' => $product_id, 'warehouse_id' => $warehouse_id, 'quantity' => $quantity])) {
                return true;
            }
        }
        return false;
    }

    public function addPurchase($data, $items, $attachments = [])
    {
        $this->db->trans_start();
        if ($this->db->insert('purchases', $data)) {
            $purchase_id = $this->db->insert_id();
            if ($this->site->getReference('po') == $data['reference_no']) {
                $this->site->updateReference('po');
            }
            if ($this->site->getReference('rep') == $data['return_purchase_ref']) {
                $this->site->updateReference('rep');
            }
            foreach ($items as $item) {
                $item['purchase_id'] = $purchase_id;
                $item['option_id']   = !empty($item['option_id']) && is_numeric($item['option_id']) ? $item['option_id'] : null;
                $this->db->insert('purchase_items', $item);
                
                // Code for serials here
                $serials_reference = $data['reference_no'];
                $serials_quantity = $item['quantity'];
                $serials_gtin = $item['product_code'];
                $serials_batch_no = $item['batchno'];
                $dispatch_array = $this->db->get_where('sma_rasd_notifications', ['invoice_no' => $serials_reference], 1);
                if ($dispatch_array->num_rows() > 0) {
                    foreach (($dispatch_array->result()) as $d_array) {
                        $dispatch_id = $d_array->dispatch_id;
                        $notification_serials = $this->db->get_where('sma_notification_serials', ['gtin' => $serials_gtin, 'dispatch_id' => $dispatch_id, 'batch_no' => $serials_batch_no, 'used' => 0], $serials_quantity);
                        if ($notification_serials->num_rows() > 0) {
                            foreach (($notification_serials->result()) as $row) {
                                $serials_data[] = $row;
                                $invoice_serials = array();
                                $invoice_serials['serial_number'] = $row->serial_no;
                                $invoice_serials['gtin'] = $row->gtin;
                                $invoice_serials['batch_no'] = $row->batch_no;
                                $invoice_serials['pid'] = $purchase_id;
                                $invoice_serials['date'] = date('Y-m-d');

                                $this->db->update('sma_notification_serials', ['used' => 1], ['serial_no' => $row->serial_no, 'batch_no' => $row->batch_no, 'gtin' => $row->gtin]);
                                $this->db->insert('sma_invoice_serials', $invoice_serials);
                            }
                        }
                    }
                }
                
                // Code for serials end here

                if ($this->Settings->update_cost) {
                    //$this->db->update('products', ['cost' => $item['base_unit_cost']], ['id' => $item['product_id']]);
                    if ($item['option_id']) {
                        $this->db->update('product_variants', ['cost' => $item['base_unit_cost']], ['id' => $item['option_id'], 'product_id' => $item['product_id']]);
                    }
                }
                if ($data['status'] == 'received' || $data['status'] == 'returned') {
                    $this->updateAVCO(['product_id' => $item['product_id'], 'warehouse_id' => $item['warehouse_id'], 'quantity' => $item['quantity'], 'batch' => $item['batchno'], 'cost' => $item['base_unit_cost'] ?? $item['real_unit_cost']]);
                }
            }

            if (!empty($attachments)) {
                foreach ($attachments as $attachment) {
                    $attachment['subject_id']   = $purchase_id;
                    $attachment['subject_type'] = 'purchase';
                    $this->db->insert('attachments', $attachment);
                }
            }

            if ($data['status'] == 'returned') {
                $this->db->update('purchases', ['return_purchase_ref' => $data['return_purchase_ref'], 'surcharge' => $data['surcharge'], 'return_purchase_total' => $data['grand_total'], 'return_id' => $purchase_id], ['id' => $data['purchase_id']]);
            }

            if ($data['status'] == 'received' || $data['status'] == 'returned') {
                $this->site->syncQuantity(null, $purchase_id);
            }
        }
        $this->db->trans_complete();
        if ($this->db->trans_status() === false) {
            log_message('error', 'An errors has been occurred while adding the sale (Add:Purchases_model.php)');
        } else {
            return true;
        }
        return false;
    }

    public function calculatePurchaseTotals($id, $return_id, $surcharge)
    {
        $purchase = $this->getPurchaseByID($id);
        $items    = $this->getAllPurchaseItems($id);
        if (!empty($items)) {
            $total            = 0;
            $product_tax      = 0;
            $order_tax        = 0;
            $product_discount = 0;
            $order_discount   = 0;
            foreach ($items as $item) {
                $product_tax      += $item->item_tax;
                $product_discount += $item->item_discount;
                $total            += $item->net_unit_cost * $item->quantity;
            }
            if ($purchase->order_discount_id) {
                $percentage        = '%';
                $order_discount_id = $purchase->order_discount_id;
                $opos              = strpos($order_discount_id, $percentage);
                if ($opos !== false) {
                    $ods            = explode('%', $order_discount_id);
                    $order_discount = (($total + $product_tax) * (float)($ods[0])) / 100;
                } else {
                    $order_discount = $order_discount_id;
                }
            }
            if ($purchase->order_tax_id) {
                $order_tax_id = $purchase->order_tax_id;
                if ($order_tax_details = $this->site->getTaxRateByID($order_tax_id)) {
                    if ($order_tax_details->type == 2) {
                        $order_tax = $order_tax_details->rate;
                    }
                    if ($order_tax_details->type == 1) {
                        $order_tax = (($total + $product_tax - $order_discount) * $order_tax_details->rate) / 100;
                    }
                }
            }
            $total_discount = $order_discount + $product_discount;
            $total_tax      = $product_tax    + $order_tax;
            $grand_total    = $total          + $total_tax          + $purchase->shipping - $order_discount          + $surcharge;
            $data           = [
                'total'            => $total,
                'product_discount' => $product_discount,
                'order_discount'   => $order_discount,
                'total_discount'   => $total_discount,
                'product_tax'      => $product_tax,
                'order_tax'        => $order_tax,
                'total_tax'        => $total_tax,
                'grand_total'      => $grand_total,
                'return_id'        => $return_id,
                'surcharge'        => $surcharge,
            ];

            if ($this->db->update('purchases', $data, ['id' => $id])) {
                return true;
            }
        } else {
            $this->db->delete('purchases', ['id' => $id]);
        }
        return false;
    }

    public function deleteExpense($id)
    {
        $this->site->log('Expense', ['model' => $this->getExpenseByID($id)]);
        if ($this->db->delete('expenses', ['id' => $id])) {
            $this->db->delete('attachments', ['subject_id' => $id, 'subject_type' => 'expense']);
            return true;
        }
        return false;
    }

    public function deletePayment($id)
    {
        $opay = $this->getPaymentByID($id);
        $this->site->log('Payment', ['model' => $opay]);
        if ($this->db->delete('payments', ['id' => $id])) {
            $this->site->syncPurchasePayments($opay->purchase_id);
            return true;
        }
        return false;
    }

    public function updateProductSalePrice($item_code, $item_sale_price, $item_tax_rate){
        if ($this->db->update('sma_products', ['price' => $item_sale_price, 'tax_rate' => $item_tax_rate], ['code' => $item_code])) {
            return true;
        }
    }

    public function deletePurchase($id)
    {
        $this->db->trans_start();
        $purchase       = $this->getPurchaseByID($id);
        $purchase_items = $this->site->getAllPurchaseItems($id);
        $this->site->log('Purchase', ['model' => $purchase, 'items' => $purchase_items]);
        if ($this->db->delete('purchase_items', ['purchase_id' => $id]) && $this->db->delete('purchases', ['id' => $id])  && ($purchase->status != 'received' && $purchase->status != 'partial')) {
            $this->db->delete('payments', ['purchase_id' => $id]);
            if ($purchase->status == 'received' || $purchase->status == 'partial') {
                foreach ($purchase_items as $oitem) {
                    $this->updateAVCO(['product_id' => $oitem->product_id, 'warehouse_id' => $oitem->warehouse_id, 'batch' => $oitem->batchno, 'quantity' => (0 - $oitem->quantity), 'cost' => $oitem->real_unit_cost]);
                    $received = $oitem->quantity_received ? $oitem->quantity_received : $oitem->quantity;
                    if ($oitem->quantity_balance < $received) {
                        $clause = ['purchase_id' => null, 'transfer_id' => null, 'product_id' => $oitem->product_id, 'warehouse_id' => $oitem->warehouse_id, 'option_id' => $oitem->option_id];
                        $this->site->setPurchaseItem($clause, ($oitem->quantity_balance - $received));
                    }
                }
            }
            $this->db->delete('attachments', ['subject_id' => $id, 'subject_type' => 'purchase']);
            $this->site->syncQuantity(null, null, $purchase_items);

            // Code for serials start here
            $notification_serials = $this->db->get_where('sma_invoice_serials', ['pid' => $id]);
            if ($notification_serials->num_rows() > 0) {
                foreach (($notification_serials->result()) as $row) {
                    $this->db->update('sma_notification_serials', ['used' => 0], ['serial_no' => $row->serial_number, 'gtin' => $row->gtin, 'batch_no' => $row->batch_no]);
                    $this->db->delete('sma_invoice_serials', ['id' => $row->id]);
                }
            }
            // Code for serials end here
        }else{
            return false;
        }
        $this->db->trans_complete();
        if ($this->db->trans_status() === false) {
            log_message('error', 'An errors has been occurred while adding the sale (Delete:Purchases_model.php)');
        } else {
            return true;
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

    public function getAllPurchaseItems($purchase_id)
    {
        $this->db->select('purchase_items.*, tax_rates.code as tax_code, tax_rates.name as tax_name, tax_rates.rate as tax_rate, products.unit, products.details as details, product_variants.name as variant, products.hsn_code as hsn_code, products.second_name as second_name')
            ->join('products', 'products.id=purchase_items.product_id', 'left')
            ->join('product_variants', 'product_variants.id=purchase_items.option_id', 'left')
            ->join('tax_rates', 'tax_rates.id=purchase_items.tax_rate_id', 'left')
            ->group_by('purchase_items.id')
            ->order_by('id', 'asc');
        $q = $this->db->get_where('purchase_items', ['purchase_id' => $purchase_id]);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getAllPurchases()
    {
        $q = $this->db->get('purchases');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
    }

    public function getAllQuoteItems($quote_id)
    {
        $q = $this->db->get_where('quote_items', ['quote_id' => $quote_id]);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getAllReturnItems($return_id)
    {
        $this->db->select('return_purchase_items.*, products.details as details, product_variants.name as variant, products.hsn_code as hsn_code, products.second_name as second_name')
            ->join('products', 'products.id=return_purchase_items.product_id', 'left')
            ->join('product_variants', 'product_variants.id=return_purchase_items.option_id', 'left')
            ->group_by('return_purchase_items.id')
            ->order_by('id', 'asc');
        $q = $this->db->get_where('return_purchase_items', ['return_id' => $return_id]);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
    }

    public function getExpenseByID($id)
    {
        $q = $this->db->get_where('expenses', ['id' => $id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getExpenseCategories()
    {
        $q = $this->db->get('expense_categories');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getExpenseCategoryByID($id)
    {
        $q = $this->db->get_where('expense_categories', ['id' => $id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getItemByID($id)
    {
        $q = $this->db->get_where('purchase_items', ['id' => $id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getOverSoldCosting($product_id)
    {
        $q = $this->db->get_where('costing', ['overselling' => 1]);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getPaymentByID($id)
    {
        $q = $this->db->get_where('payments', ['id' => $id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }

        return false;
    }

    public function getAccountsEntryByReferenceNo($reference_number){
        $q = $this->db->get_where('sma_accounts_entries', ['number' => $reference_number], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }

        return false;
    }

    public function getPayments(){
        $this->db->select('payments.id, payments.date, payments.paid_by, payments.amount, payments.reference_no, payments.note, users.first_name, users.last_name, companies.company, type')
            ->join('purchases', 'purchases.id=payments.purchase_id', 'left')
            ->join('companies', 'companies.id=purchases.supplier_id', 'left')
            ->join('users', 'users.id=payments.created_by', 'left')
            ->where('type', 'sent')
            ->where('payments.purchase_id >', 0);
        $q = $this->db->get('payments');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getPaymentsForPurchase($purchase_id)
    {
        $this->db->select('payments.date, payments.paid_by, payments.amount, payments.reference_no, users.first_name, users.last_name, type')
            ->join('users', 'users.id=payments.created_by', 'left');
        $q = $this->db->get_where('payments', ['purchase_id' => $purchase_id]);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
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

    public function getProductByID($id)
    {
        $q = $this->db->get_where('products', ['id' => $id], 1);
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

    public function getProductNames($term, $limit = 20)
    {
        $this->db->where("type = 'standard' AND (name LIKE '%" . $term . "%' OR code LIKE '%" . $term . "%' OR supplier1_part_no LIKE '%" . $term . "%' OR supplier2_part_no LIKE '%" . $term . "%' OR supplier3_part_no LIKE '%" . $term . "%' OR supplier4_part_no LIKE '%" . $term . "%' OR supplier5_part_no LIKE '%" . $term . "%' OR  concat(name, ' (', code, ')') LIKE '%" . $term . "%')");
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

    public function getProductsByCode($code)
    {
        $this->db->select('*')->from('products')->like('code', $code, 'both');
        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
    }

    public function getProductVariantByName($name, $product_id)
    {
        $q = $this->db->get_where('product_variants', ['name' => $name, 'product_id' => $product_id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
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

    public function getPurchaseByReference($reference){
        $q = $this->db->get_where('sma_purchases', ['reference_no' => $reference], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getPurcahseItemByID($id)
    {
        $q = $this->db->get_where('purchase_items', ['id' => $id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getPurchaseByID($id)
    {
        $q = $this->db->get_where('purchases', ['id' => $id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getPurchasePayments($purchase_id)
    {
        $this->db->order_by('id', 'asc');
        $q = $this->db->get_where('payments', ['purchase_id' => $purchase_id]);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
    }

    public function getQuoteByID($id)
    {
        $q = $this->db->get_where('quotes', ['id' => $id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getReturnByID($id)
    {
        $q = $this->db->get_where('return_purchases', ['id' => $id], 1);
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

    public function getWarehouseProductQuantity($warehouse_id, $product_id, $batchno)
    {
        $q = $this->db->get_where('warehouses_products', ['warehouse_id' => $warehouse_id, 'product_id' => $product_id, 'batchno' => $batchno], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function resetProductOptionQuantity($option_id, $warehouse_id, $quantity, $product_id)
    {
        if ($option = $this->getProductWarehouseOptionQty($option_id, $warehouse_id)) {
            $nq = $option->quantity - $quantity;
            if ($this->db->update('warehouses_products_variants', ['quantity' => $nq], ['option_id' => $option_id, 'warehouse_id' => $warehouse_id])) {
                return true;
            }
        } else {
            $nq = 0 - $quantity;
            if ($this->db->insert('warehouses_products_variants', ['option_id' => $option_id, 'product_id' => $product_id, 'warehouse_id' => $warehouse_id, 'quantity' => $nq])) {
                return true;
            }
        }
        return false;
    }

    public function returnPurchase($data = [], $items = [])
    {
        $purchase_items = $this->site->getAllPurchaseItems($data['purchase_id']);

        if ($this->db->insert('return_purchases', $data)) {
            $return_id = $this->db->insert_id();
            if ($this->site->getReference('rep') == $data['reference_no']) {
                $this->site->updateReference('rep');
            }
            foreach ($items as $item) {
                $item['return_id'] = $return_id;
                $this->db->insert('return_purchase_items', $item);

                if ($purchase_item = $this->getPurcahseItemByID($item['purchase_item_id'])) {
                    if ($purchase_item->quantity == $item['quantity']) {
                        $this->db->delete('purchase_items', ['id' => $item['purchase_item_id']]);
                    } else {
                        $nqty          = $purchase_item->quantity          - $item['quantity'];
                        $bqty          = $purchase_item->quantity_balance  - $item['quantity'];
                        $rqty          = $purchase_item->quantity_received - $item['quantity'];
                        $tax           = $purchase_item->unit_cost         - $purchase_item->net_unit_cost;
                        $discount      = $purchase_item->item_discount / $purchase_item->quantity;
                        $item_tax      = $tax                      * $nqty;
                        $item_discount = $discount                 * $nqty;
                        $subtotal      = $purchase_item->unit_cost * $nqty;
                        $this->db->update('purchase_items', ['quantity' => $nqty, 'quantity_balance' => $bqty, 'quantity_received' => $rqty, 'item_tax' => $item_tax, 'item_discount' => $item_discount, 'subtotal' => $subtotal], ['id' => $item['purchase_item_id']]);
                    }
                }
            }
            $this->calculatePurchaseTotals($data['purchase_id'], $return_id, $data['surcharge']);
            $this->site->syncQuantity(null, null, $purchase_items);
            $this->site->syncQuantity(null, $data['purchase_id']);
            return true;
        }
        return false;
    }

    public function updateAVCO($data)
    {
        if ($wp_details = $this->getWarehouseProductQuantity($data['warehouse_id'], $data['product_id'], $data['batch'])) {
            $total_cost     = (($wp_details->quantity * $wp_details->avg_cost) + ($data['quantity'] * $data['cost']));
            $total_quantity = $wp_details->quantity + $data['quantity'];

            if (!empty($total_quantity)) {
                $avg_cost = ($total_cost / $total_quantity);
                $this->db->update('warehouses_products', ['avg_cost' => $avg_cost], ['product_id' => $data['product_id'], 'warehouse_id' => $data['warehouse_id'], 'batchno' => $data['batch']]);
            }
        } else {
            $this->db->insert('warehouses_products', ['product_id' => $data['product_id'], 'warehouse_id' => $data['warehouse_id'], 'avg_cost' => $data['cost'], 'quantity' => 0, 'batchno' => $data['batch']]);
        }
    }

    public function updateExpense($id, $data = [], $attachments = [])
    {
        if ($this->db->update('expenses', $data, ['id' => $id])) {
            if (!empty($attachments)) {
                foreach ($attachments as $attachment) {
                    $attachment['subject_id']   = $id;
                    $attachment['subject_type'] = 'expense';
                    $this->db->insert('attachments', $attachment);
                }
            }
            return true;
        }
        return false;
    }

    public function updatePayment($id, $data = [])
    {
        if ($this->db->update('payments', $data, ['id' => $id])) {
            $this->site->syncPurchasePayments($data['purchase_id']);
            return true;
        }
        return false;
    }

    public function updatePurchase($id, $data, $items = [], $attachments = [])
    {
        $this->db->trans_start();
        $opurchase = $this->getPurchaseByID($id);
        $oitems    = $this->getAllPurchaseItems($id);
        if ($this->db->update('purchases', $data, ['id' => $id]) && $this->db->delete('purchase_items', ['purchase_id' => $id])) {
            $purchase_id = $id;

            // Code for serials start here
            $notification_serials = $this->db->get_where('sma_invoice_serials', ['pid' => $id]);
            if ($notification_serials->num_rows() > 0) {
                foreach (($notification_serials->result()) as $row) {
                    $this->db->update('sma_notification_serials', ['used' => 0], ['serial_no' => $row->serial_number, 'gtin' => $row->gtin, 'batch_no' => $row->batch_no]);
                    $this->db->delete('sma_invoice_serials', ['id' => $row->id]);
                }
            }
            // Code for serials end here

            foreach ($items as $item) {
                $item['purchase_id'] = $id;
                $item['option_id']   = !empty($item['option_id']) && is_numeric($item['option_id']) ? $item['option_id'] : null;
                $this->db->insert('purchase_items', $item);
                if ($data['status'] == 'received' || $data['status'] == 'partial') {
                    $this->updateAVCO(['product_id' => $item['product_id'], 'batch' => $item['batchno'], 'warehouse_id' => $item['warehouse_id'], 'quantity' => $item['quantity'], 'cost' => $item['real_unit_cost']]);
                }

                // Code for serials here
                $serials_reference = $data['reference_no'];
                $serials_quantity = $item['quantity'];
                $serials_gtin = $item['product_code'];
                $serials_batch_no = $item['batchno'];
                $dispatch_array = $this->db->get_where('sma_rasd_notifications', ['invoice_no' => $serials_reference], 1);
                if ($dispatch_array->num_rows() > 0) {
                    foreach (($dispatch_array->result()) as $d_array) {
                        $dispatch_id = $d_array->dispatch_id;
                        $notification_serials = $this->db->get_where('sma_notification_serials', ['gtin' => $serials_gtin, 'dispatch_id' => $dispatch_id, 'batch_no' => $serials_batch_no, 'used' => 0], $serials_quantity);
                        if ($notification_serials->num_rows() > 0) {
                            foreach (($notification_serials->result()) as $row) {
                                $serials_data[] = $row;
                                $invoice_serials = array();
                                $invoice_serials['serial_number'] = $row->serial_no;
                                $invoice_serials['gtin'] = $row->gtin;
                                $invoice_serials['batch_no'] = $row->batch_no;
                                $invoice_serials['pid'] = $id;
                                $invoice_serials['date'] = date('Y-m-d');

                                $this->db->update('sma_notification_serials', ['used' => 1], ['serial_no' => $row->serial_no, 'batch_no' => $row->batch_no, 'gtin' => $row->gtin]);
                                $this->db->insert('sma_invoice_serials', $invoice_serials);
                            }
                        }
                    }
                }

                // Code for serials end here
            }
            $this->site->syncQuantity(null, null, $oitems);

            if (!empty($attachments)) {
                foreach ($attachments as $attachment) {
                    $attachment['subject_id']   = $id;
                    $attachment['subject_type'] = 'purchase';
                    $this->db->insert('attachments', $attachment);
                }
            }
            if ($data['status'] == 'received' || $data['status'] == 'partial') {
                $this->site->syncQuantity(null, $id);
                foreach ($oitems as $oitem) {
                    $this->updateAVCO(['product_id' => $oitem->product_id, 'batch' => $item['batchno'], 'warehouse_id' => $oitem->warehouse_id, 'quantity' => (0 - $oitem->quantity), 'cost' => $oitem->real_unit_cost]);
                }
            }
            $this->site->syncPurchasePayments($id);
        }
        $this->db->trans_complete();
        if ($this->db->trans_status() === false) {
            log_message('error', 'An errors has been occurred while adding the sale (Update:Purchases_model.php)');
        } else {
            return true;
        }

        return false;
    }

    public function update_purchase_paid_amount($id, $amount){
        $q = $this->db->get_where('purchases', ['id' => $id], 1);
        if ($q->num_rows() > 0) {
            $row = $q->row();
            $paid_amount = $row->paid;
            $new_amount = $paid_amount + $amount;
            
            $data = array(
                'paid' => $new_amount
            );
    
            $this->db->update('purchases', $data, array('id' => $id));
        }
        return false;
    }

    public function getMemoAccountingEntries($id){
        $this->db->select('sma_accounts_entries.*');
        $this->db->from('sma_accounts_entries');
        $this->db->where(['memo_id' => $id]);
        $query = $this->db->get();

        if ($query->num_rows() > 0) {
            $data = $query->result();
            return $data;
        } else {
            $data = array();
            return $data;
        }
    }

    public function getDebitMemo($type){
        $this->db->order_by('date', 'asc');
        $this->db->select('sma_memo.*, companies.company');
        $this->db->from('memo');
        $this->db->join('companies', 'sma_memo.supplier_id = companies.id');
        $this->db->where(['type' => $type]);
        $query = $this->db->get();

        if ($query->num_rows() > 0) {
            $data = $query->result();
            return $data;
        } else {
            $data = array();
            return $data;
        }
    }

    public function getCreditMemo($type){
        $this->db->order_by('date', 'asc');
        $this->db->select('sma_memo.*, companies.company');
        $this->db->from('memo');
        $this->db->join('companies', 'sma_memo.customer_id = companies.id');
        $this->db->where(['type' => $type]);
        $query = $this->db->get();

        if ($query->num_rows() > 0) {
            $data = $query->result();
            return $data;
        } else {
            $data = array();
            return $data;
        }
    }

    public function getDebitMemoData($id) {
        $this->db->select('sma_memo.*');
        $this->db->from('sma_memo');
        $this->db->where('id', $id);
        $this->db->limit(1);
        $query = $this->db->get();
    
        if ($query->num_rows() > 0) {
            $data = $query->row(); // Retrieve the single row
            return $data;
        } else {
            return null; // Return null if no rows are found
        }
    }

    public function getDebitMemoEntriesData($id){
        $this->db->select('sma_memo_entries.*');
        $this->db->from('sma_memo_entries');
        $this->db->where(['memo_id' => $id]);
        $query = $this->db->get();
        if ($query->num_rows() > 0) {
            $data = $query->result();
            return $data;
        } else {
            $data = array();
            return $data;
        }
    }

    public function getPendingInvoicesBySupplier($supplier_id){
        $this->db->order_by('date', 'asc');
        //$q = $this->db->get_where('purchases', ['supplier_id' => $supplier_id, 'payment_status' => 'pending', ]);
        $q = $this->db->get_where('purchases', [
            'supplier_id' => $supplier_id,
            'payment_status' => 'pending',
            'purchase_id IS NULL' => null,
        ]);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data; 
        }else{
            $data = [];
            return $data;
        }
    }

    public function puchaseToInvoice($id)
    {
        $invoiceNumber = $this->generateInvoiceNumber(); // Generate the invoice number

        $data = array(
            'purchase_invoice' => 1,
            'invoice_number' => $invoiceNumber
        );

        $this->db->update('purchases', $data, array('id' => $id));

        return true;
    }

    public function generateInvoiceNumber()
    {
        $prefix = 'INV'; // Prefix for the invoice number
        $timestamp = time(); // Current timestamp

        $invoiceNumber = $prefix . '' . $timestamp;

        return $invoiceNumber;
    }

    public function updateStatus($id, $status, $note)
    {
        $this->db->trans_start();
        $purchase = $this->getPurchaseByID($id);
        $items    = $this->site->getAllPurchaseItems($id);
        if ($this->db->update('purchases', ['status' => $status, 'note' => $note], ['id' => $id])) {
            if (($purchase->status != 'received' || $purchase->status != 'partial') && ($status == 'received' || $status == 'partial')) {
                foreach ($items as $item) {
                    $qb = $status == 'received' ? ($item->quantity_balance + ($item->quantity - $item->quantity_received)) : $item->quantity_balance;
                    $qr = $status == 'received' ? $item->quantity : $item->quantity_received;
                    $this->db->update('purchase_items', ['status' => $status, 'quantity_balance' => $qb, 'quantity_received' => $qr], ['id' => $item->id]);
                    $this->updateAVCO(['product_id' => $item->product_id, 'warehouse_id' => $item->warehouse_id, 'batch' => $item->batchno, 'quantity' => $item->quantity, 'cost' => $item->real_unit_cost]);
                }
                $this->site->syncQuantity(null, null, $items);
            } elseif (($purchase->status == 'received' || $purchase->status == 'partial') && ($status == 'ordered' || $status == 'pending')) {
                foreach ($items as $item) {
                    $qb = 0;
                    $qr = 0;
                    $this->db->update('purchase_items', ['status' => $status, 'quantity_balance' => $qb, 'quantity_received' => $qr], ['id' => $item->id]);
                    $this->updateAVCO(['product_id' => $item->product_id, 'warehouse_id' => $item->warehouse_id, 'batch' => $item->batchno, 'quantity' => $item->quantity, 'cost' => $item->real_unit_cost]);
                }
                $this->site->syncQuantity(null, null, $items);
            }
        }
        $this->db->trans_complete();
        if ($this->db->trans_status() === false) {
            log_message('error', 'An errors has been occurred while adding the sale (UpdateStatus:Purchases_model.php)');
        } else {
            return true;
        }
        return false;
    }

    public function getThreeMonthSale($product, $start_date, $end_date)
    {
        $pp = "( SELECT product_id, p.date as date, p.created_by as created_by, SUM(CASE WHEN pi.purchase_id IS NOT NULL THEN quantity ELSE 0 END) as purchasedQty, SUM(quantity_balance) as balacneQty, SUM( unit_cost * quantity_balance ) balacneValue, SUM( (CASE WHEN pi.purchase_id IS NOT NULL THEN (pi.subtotal) ELSE 0 END) ) totalPurchase from {$this->db->dbprefix('purchase_items')} pi LEFT JOIN {$this->db->dbprefix('purchases')} p on p.id = pi.purchase_id WHERE pi.status = 'received' ";

        $sp = '( SELECT si.product_id, s.date as date, s.created_by as created_by, SUM( si.quantity ) soldQty, SUM( si.quantity * si.sale_unit_price ) totalSale from ' . $this->db->dbprefix('costing') . ' si JOIN ' . $this->db->dbprefix('sales') . ' s on s.id = si.sale_id ';

         $start_date = $this->sma->fld($start_date);
         $end_date   = $end_date ? $this->sma->fld($end_date) : date('Y-m-d');
         $pp .= " AND p.date >= '{$start_date}' AND p.date <= '{$end_date}' ";
         $sp .= " AND s.date >= '{$start_date}' AND s.date <= '{$end_date}' ";
         $pp .= ' GROUP BY pi.product_id ) PCosts';
         $sp .= ' GROUP BY si.product_id ) PSales';
         
        $this->db
                ->select('COALESCE( PSales.soldQty, 0 ) as sold', false)
                ->from('products')
                ->join($sp, 'products.id = PSales.product_id', 'left')
                ->join($pp, 'products.id = PCosts.product_id', 'left')
                ->where('products.type !=', 'combo');

        $this->db->where($this->db->dbprefix('products') . '.id', $product); 
         $q =  $this->db->get();

            if($q !== false)
            {
                 return $q->row()->sold;

            } else {
                return 0;
            }
       
                     
    }

    public function searchByReference($referenceNo)
    {
        $q = $this->db->get_where('purchases',['reference_no' => $referenceNo]);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data; 
        }else{
        $data =420;
        return $data;
        }
    }

    public function searchBySequenceCode($sequenceCode)
    {
        $q = $this->db->get_where('purchases',['sequence_code' => $sequenceCode]);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data; 
        }else{
        $data =420;
        return $data;
        }
    }

    public function searchByDate($start_date,$end_date)
    {
        $this->db
        ->select('reference_no,sequence_code,date,supplier,status')
        ->where('date >=', $start_date)
        ->where('date <=', $end_date);
        $q = $this->db->get('purchases');
        if ($q->num_rows() > 0) {
        foreach (($q->result()) as $row) {
        $data[] = $row;
        }
        return $data;
        }
        return false;
    }
}
