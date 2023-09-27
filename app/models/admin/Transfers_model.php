<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Transfers_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function addTransferAccountEntries($transferId){

        $transfer = $this->getTransferByID($transferId);

        $toWareHouseId = $transfer->to_warehouse_id;
        $fromWareHouseId = $transfer->from_warehouse_id;

       $toWareHouse =  $this->site->getWarehouseByID($toWareHouseId);
       $fromWareHouse =  $this->site->getWarehouseByID($fromWareHouseId);
       $goodsTrasitWareHouse = $this->site->getGoodsTrasitWareHouse();

        // Credit & Debit to one same Account (Goods in Transit)
        $accountsTotal = $transfer->grand_total + $transfer->grand_total;

         /*Accounts Entries*/
         $entry = array(
            'entrytype_id' => 4,
            'transaction_type' => 'transferorder',
            'number'       => 'TR-'.$transfer->transfer_no,
            'date'         => date('Y-m-d'), 
            'dr_total'     => $accountsTotal,
            'cr_total'     => $accountsTotal,
            'notes'        => 'Transfer Reference: '.$transfer->transfer_no.' Date: '.date('Y-m-d H:i:s'),
            'tid'          =>  $transfer->id
            );
    
            $add  = $this->db->insert('sma_accounts_entries', $entry);
            $insert_id = $this->db->insert_id();

            $entryitemdata = array();

            $entryitemdata[] = array(
            'Entryitem' => array(
                'entry_id' => $insert_id,
                'dc' => 'D',
                'ledger_id' => $goodsTrasitWareHouse->inventory_ledger,
                
                'amount' => $transfer->grand_total,
                'narration' => 'goods in transit'
            )
            );

            $entryitemdata[] = array(
                'Entryitem' => array(
                    'entry_id' => $insert_id,
                    'dc' => 'C',
                    'ledger_id' => $fromWareHouse->inventory_ledger,
                    'amount' => $transfer->grand_total,
                    'narration' => 'inventry'
                )
            );  

            $entryitemdata[] = array(
                'Entryitem' => array(
                    'entry_id' => $insert_id,
                    'dc' => 'C',
                    'ledger_id' => $goodsTrasitWareHouse->inventory_ledger,
                    'amount' => $transfer->grand_total,
                    'narration' => 'goods in transit'
                )
            );  

            $entryitemdata[] = array(
                'Entryitem' => array(
                    'entry_id' => $insert_id,
                    'dc' => 'D',
                    'ledger_id' => $toWareHouse->inventory_ledger,
                    'amount' => $transfer->grand_total,
                    'narration' => 'inventry'
                )
            );  

            foreach ($entryitemdata as $row => $itemdata)
            {
                
                  $this->db->insert('sma_accounts_entryitems', $itemdata['Entryitem']);
            }
    }

    public function transferPurchaseInvoice($data = [], $items = [], $attachments = []){
        $this->db->trans_start();
        $status = $data['status'];
        if ($this->db->insert('transfers', $data)) {
            $transfer_id = $this->db->insert_id();
            if ($this->site->getReference('to') == $data['transfer_no']) {
                $this->site->updateReference('to');
            }

            foreach ($items as $item) {
                $item['transfer_id'] = $transfer_id;
                $item['option_id']   = !empty($item['option_id']) && is_numeric($item['option_id']) ? $item['option_id'] : null;
                
                $this->db->insert('transfer_items', $item);
                $inserted_item_id = $this->db->insert_id();
                
                if (!empty($attachments)) {
                    foreach ($attachments as $attachment) {
                        $attachment['subject_id']   = $transfer_id;
                        $attachment['subject_type'] = 'transfer';
                        $this->db->insert('attachments', $attachment);
                    }
                }

                if ($status == 'completed') {
                    $this->syncTransderdItemFromInvoice($item['product_id'], $data['from_warehouse_id'], $item['batchno'], $item['quantity'], $item['option_id'], $status, 'add');
                }
            }
        }

        $this->db->trans_complete();
        if ($this->db->trans_status() === false) {
            log_message('error', 'An errors has been occurred while adding the sale (Add:Transfers_model.php)');
        } else {
            return true;
        }
        return false;
    }

    public function addTransfer($data = [], $items = [], $attachments = [])
    {
        $this->db->trans_start();
        $status = $data['status'];
        if ($this->db->insert('transfers', $data)) {
            $transfer_id = $this->db->insert_id();
            if ($this->site->getReference('to') == $data['transfer_no']) {
                $this->site->updateReference('to');
            }
            foreach ($items as $item) {
                $item['transfer_id'] = $transfer_id;
                $item['option_id']   = !empty($item['option_id']) && is_numeric($item['option_id']) ? $item['option_id'] : null;
                if ($status == 'completed') {
                    $item['date']         = date('Y-m-d');
                    $item['warehouse_id'] = $data['to_warehouse_id'];
                    $item['status']       = 'received';
                    $this->db->insert('purchase_items', $item);
                } else {
                    $this->db->insert('transfer_items', $item);
                }

                // Code for serials here
                $serials_quantity = $item['quantity'];
                $serials_gtin = $item['product_code'];
                $serials_batch_no = $item['batchno'];
                
                $this->db->select('sma_invoice_serials.*');
                $this->db->from('sma_invoice_serials');
                $this->db->join('sma_purchases', 'sma_invoice_serials.pid = sma_purchases.id');
                $this->db->where('sma_invoice_serials.gtin', $serials_gtin);
                $this->db->where('sma_invoice_serials.batch_no', $serials_batch_no);
                $this->db->where('sma_invoice_serials.sid', 0);
                $this->db->where('sma_invoice_serials.rsid', 0);
                $this->db->where('sma_invoice_serials.tid', 0);
                $this->db->where('sma_invoice_serials.pid !=', 0);
                $this->db->where('sma_purchases.status', 'received');
                $this->db->limit($serials_quantity);

                $notification_serials = $this->db->get();
                if ($notification_serials->num_rows() > 0) {
                    foreach (($notification_serials->result()) as $row) {
                        $this->db->update('sma_invoice_serials', ['tid' => $transfer_id], ['serial_number' => $row->serial_number, 'batch_no' => $row->batch_no, 'gtin' => $row->gtin]);
                    }
                }
                // Code for serials end here

                if (!empty($attachments)) {
                    foreach ($attachments as $attachment) {
                        $attachment['subject_id']   = $transfer_id;
                        $attachment['subject_type'] = 'transfer';
                        $this->db->insert('attachments', $attachment);
                    }
                }

                if ($status == 'sent' || $status == 'completed') {
                //if ($status == 'completed') {
                    $this->syncTransderdItem($item['product_id'], $data['from_warehouse_id'], $item['batchno'], $item['quantity'], $item['option_id'], $status, 'add');
                }
            }

            if($status == 'completed'){
                $this->addTransferAccountEntries($transfer_id);
            }

        }
        $this->db->trans_complete();
        if ($this->db->trans_status() === false) {
            log_message('error', 'An errors has been occurred while adding the sale (Add:Transfers_model.php)');
        } else {
            return true;
        }
        return false;
    }

    public function deleteTransfer($id)
    {
        $this->db->trans_start();
        $ostatus = $this->resetTransferActions($id, 1);
        $oitems  = $this->getAllTransferItems($id, $ostatus);
        $this->site->log('Transfer', ['model' => $this->getTransferByID($id), 'items' => $oitems]);
        $tbl = $ostatus == 'completed' ? 'purchase_items' : 'transfer_items';
        
        if ($this->db->delete('transfers', ['id' => $id]) && $this->db->delete($tbl, ['transfer_id' => $id]) && $ostatus == 'save') {
            foreach ($oitems as $item) {
                $this->site->syncQuantity(null, null, null, $item->product_id);
            }

            $this->db->update('sma_invoice_serials', ['tid' => 0], ['tid' => $id]);
        }else{
            log_message('error', 'An errors has been occurred while deleting the transfer (Delete:Transfers_model.php)');
            return false;
        }

        $this->db->trans_complete();
        if ($this->db->trans_status() === false) {
            log_message('error', 'An errors has been occurred while adding the sale (Delete:Transfers_model.php)');
        } else {
            return true;
        }
        return false;
    }

    public function getAllTransferItems($transfer_id, $status)
    {
        if ($status == 'completed') {
            $this->db->select('purchase_items.*, product_variants.name as variant, products.unit, products.hsn_code as hsn_code, products.second_name as second_name')
                ->from('purchase_items')
                ->join('products', 'products.id=purchase_items.product_id', 'left')
                ->join('product_variants', 'product_variants.id=purchase_items.option_id', 'left')
                ->group_by('purchase_items.id')
                ->where('transfer_id', $transfer_id);
        } else {
            $this->db->select('transfer_items.*, product_variants.name as variant, products.unit, products.hsn_code as hsn_code, products.second_name as second_name')
                ->from('transfer_items')
                ->join('products', 'products.id=transfer_items.product_id', 'left')
                ->join('product_variants', 'product_variants.id=transfer_items.option_id', 'left')
                ->group_by('transfer_items.id')
                ->where('transfer_id', $transfer_id);
        }
        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
    }

    public function getProductByCategoryID($id)
    {
        $q = $this->db->get_where('products', ['category_id' => $id], 1);
        if ($q->num_rows() > 0) {
            return true;
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
        $this->db->select('products.id as id, combo_items.item_code as code, combo_items.quantity as qty, products.name as name, warehouses_products.quantity as quantity')
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

    public function getProductNamesWithBatches($term, $warehouse_id, $limit = 10)
    {
        $this->db->select('products.id, products.price, code, name, warehouses_products.quantity, cost, tax_rate, type, unit, purchase_unit, tax_method, purchase_items.serial_number')
            ->join('warehouses_products', 'warehouses_products.product_id=products.id', 'left')
            ->join('purchase_items', 'purchase_items.product_id=products.id', 'left')
            ->group_by('products.id');
        if ($this->Settings->overselling) {
            $this->db->where("type = 'standard' AND (name LIKE '%" . $term . "%' OR code LIKE '%" . $term . "%' OR  concat(name, ' (', code, ')') LIKE '%" . $term . "%')");
        } else {
            $this->db->where("type = 'standard' AND warehouses_products.warehouse_id = '" . $warehouse_id . "' AND warehouses_products.quantity > 0 AND "
                . "(name LIKE '%" . $term . "%' OR code LIKE '%" . $term . "%' OR  concat(name, ' (', code, ')') LIKE '%" . $term . "%')");
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

    public function getProductNames($term, $warehouse_id, $limit = 5)
    {
        $this->db->select('products.id, code, name, warehouses_products.quantity, cost, tax_rate, type, unit, purchase_unit, tax_method')
            ->join('warehouses_products', 'warehouses_products.product_id=products.id', 'left')
            ->group_by('products.id');
        if ($this->Settings->overselling) {
            $this->db->where("type = 'standard' AND (name LIKE '%" . $term . "%' OR code LIKE '%" . $term . "%' OR  concat(name, ' (', code, ')') LIKE '%" . $term . "%')");
        } else {
            $this->db->where("type = 'standard' AND warehouses_products.warehouse_id = '" . $warehouse_id . "' AND warehouses_products.quantity > 0 AND "
                . "(name LIKE '%" . $term . "%' OR code LIKE '%" . $term . "%' OR  concat(name, ' (', code, ')') LIKE '%" . $term . "%')");
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
    
    public function wh_getProductNames($term, $warehouse_id, $limit = 5)
    {
        $this->db->select('products.id, code, name, warehouses_products.quantity, cost, tax_rate, type, unit, purchase_unit, tax_method, price')
            ->join('warehouses_products', 'warehouses_products.product_id=products.id', 'left')
            ->group_by('products.id');
        
         $this->db->where("type = 'standard' AND (name LIKE '%" . $term . "%' OR code LIKE '%" . $term . "%' OR  concat(name, ' (', code, ')') LIKE '%" . $term . "%')");    
        /*if ($this->Settings->overselling) {
            $this->db->where("type = 'standard' AND (name LIKE '%" . $term . "%' OR code LIKE '%" . $term . "%' OR  concat(name, ' (', code, ')') LIKE '%" . $term . "%')");
        } else {
            $this->db->where("type = 'standard' AND warehouses_products.warehouse_id = '" . $warehouse_id . "' AND warehouses_products.quantity > 0 AND "
                . "(name LIKE '%" . $term . "%' OR code LIKE '%" . $term . "%' OR  concat(name, ' (', code, ')') LIKE '%" . $term . "%')");
        }*/
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

    public function getProductOptions($product_id, $warehouse_id, $zero_check = true)
    {
        $this->db->select('product_variants.id as id, product_variants.name as name, product_variants.cost as cost, product_variants.quantity as total_quantity, warehouses_products_variants.quantity as quantity')
            ->join('warehouses_products_variants', 'warehouses_products_variants.option_id=product_variants.id', 'left')
            ->where('product_variants.product_id', $product_id)
            ->where('warehouses_products_variants.warehouse_id', $warehouse_id)
            ->group_by('product_variants.id');
        if ($zero_check) {
            $this->db->where('warehouses_products_variants.quantity >', 0);
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

    public function getProductQuantity($product_id, $warehouse = DEFAULT_WAREHOUSE)
    {
        $q = $this->db->get_where('warehouses_products', ['product_id' => $product_id, 'warehouse_id' => $warehouse], 1);
        if ($q->num_rows() > 0) {
            return $q->row_array(); //$q->row();
        }
        return false;
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

    public function getTransferByID($id)
    {
        $q = $this->db->get_where('transfers', ['id' => $id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }

        return false;
    }

    public function getWarehouseProduct($warehouse_id, $product_id, $variant_id, $item_batchno)
    {
        if ($variant_id) {
            return $this->getProductWarehouseOptionQty($variant_id, $warehouse_id);
        }
        return $this->getWarehouseProductQuantity($warehouse_id, $product_id, $item_batchno);
    }

    public function getWarehouseProductQuantity($warehouse_id, $product_id, $item_batchno)
    {
        $q = $this->db->get_where('warehouses_products', ['warehouse_id' => $warehouse_id, 'product_id' => $product_id, 'batchno' => $item_batchno], 1);
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

    public function insertQuantity($product_id, $warehouse_id, $quantity)
    {
        if ($this->db->insert('warehouses_products', ['product_id' => $product_id, 'warehouse_id' => $warehouse_id, 'quantity' => $quantity])) {
            $this->site->syncProductQty($product_id, $warehouse_id);
            return true;
        }
        return false;
    }

    public function resetTransferActions($id, $delete = null)
    {
        $otransfer = $this->getTransferByID($id);
        $oitems    = $this->getAllTransferItems($id, $otransfer->status);
        $ostatus   = $otransfer->status;
        if ($ostatus == 'sent' || $ostatus == 'completed') {
            foreach ($oitems as $item) {
                $option_id = (isset($item->option_id) && !empty($item->option_id)) ? $item->option_id : null;
                $clause    = ['purchase_id' => null, 'transfer_id' => null, 'product_id' => $item->product_id, 'warehouse_id' => $otransfer->from_warehouse_id, 'option_id' => $option_id];
                $this->site->setPurchaseItem($clause, $item->quantity);
                if ($delete) {
                    $option_id = (isset($item->option_id) && !empty($item->option_id)) ? $item->option_id : null;
                    $clause    = ['purchase_id' => null, 'transfer_id' => null, 'product_id' => $item->product_id, 'warehouse_id' => $otransfer->to_warehouse_id, 'option_id' => $option_id];
                    $this->site->setPurchaseItem($clause, ($item->quantity_balance - $item->quantity));
                }
            }
        }
        return $ostatus;
    }

    public function syncTransderdSavedItems($product_id, $warehouse_id, $batch_no, $quantity, $option_id = null, $status, $type){
        echo 'Here in saved items block...';exit;
        if ($pis = $this->site->getPurchasedItemsWithBatch($product_id, $warehouse_id, $batch_no, $option_id)) {
            if(($status == "sent" && $type == 'edit')){
                $balance_qty = $quantity;
                foreach ($pis as $pi) {
                    if ($balance_qty <= $quantity && $quantity > 0) {
                        if ($pi->quantity_balance >= $quantity) {
                            $balance_qty = $pi->quantity_balance - $quantity;
                            $this->db->update('purchase_items', ['quantity_balance' => $balance_qty], ['id' => $pi->id]);
                            $quantity = 0;
                        } elseif ($quantity > 0) {
                            $quantity    = $quantity - $pi->quantity_balance;
                            $balance_qty = $quantity;
                            $this->db->update('purchase_items', ['quantity_balance' => 0], ['id' => $pi->id]);
                        }
                    }
                    if ($quantity == 0) {
                        break;
                    }
                }
            }
            
        } else {
            $clause = ['purchase_id' => null, 'transfer_id' => null, 'product_id' => $product_id, 'warehouse_id' => $warehouse_id, 'batchno' => $batch_no, 'option_id' => $option_id];
            $this->site->setPurchaseItem($clause, (0 - $quantity));
        }
        $this->site->syncQuantity(null, null, null, $product_id, $batch_no);
    }

    public function syncTransderdItemFromInvoice($product_id, $warehouse_id, $batch_no, $quantity, $option_id = null, $status, $type){
        if ($pis = $this->site->getPurchasedItemsWithBatch($product_id, $warehouse_id, $batch_no, $option_id)) {
            if(($status == "sent" && $type == 'add') || ($status == "completed" && $type == 'add')){
                $balance_qty = $quantity;
                foreach ($pis as $pi) {
                    if ($balance_qty <= $quantity && $quantity > 0) {
                        if ($pi->quantity_balance >= $quantity) {
                            $balance_qty = $pi->quantity_balance - $quantity;
                            $this->db->update('purchase_items', ['quantity_balance' => $balance_qty], ['id' => $pi->id]);
                            $quantity = 0;
                        } elseif ($quantity > 0) {
                            $quantity    = $quantity - $pi->quantity_balance;
                            $balance_qty = $quantity;
                            $this->db->update('purchase_items', ['quantity_balance' => 0], ['id' => $pi->id]);
                        }
                    }
                    if ($quantity == 0) {
                        break;
                    }
                }
            }
            
        } else {
            $clause = ['purchase_id' => null, 'transfer_id' => null, 'product_id' => $product_id, 'warehouse_id' => $warehouse_id, 'batchno' => $batch_no, 'option_id' => $option_id];
            $this->site->setPurchaseItem($clause, (0 - $quantity));
        }
        $this->site->syncQuantity(null, null, null, $product_id, $batch_no);
    }

    public function syncTransderdItem($product_id, $warehouse_id, $batch_no, $quantity, $option_id = null, $status, $type)
    {
        echo 'Here in transferred items block';exit;
        if ($pis = $this->site->getPurchasedItemsWithBatch($product_id, $warehouse_id, $batch_no, $option_id)) {
            if(($status == "sent" && $type == 'add') || ($status == "completed" && $type == 'add')){
                $balance_qty = $quantity;
                foreach ($pis as $pi) {
                    if ($balance_qty <= $quantity && $quantity > 0) {
                        if ($pi->quantity_balance >= $quantity) {
                            $balance_qty = $pi->quantity_balance - $quantity;
                            $this->db->update('purchase_items', ['quantity_balance' => $balance_qty], ['id' => $pi->id]);
                            $quantity = 0;
                        } elseif ($quantity > 0) {
                            $quantity    = $quantity - $pi->quantity_balance;
                            $balance_qty = $quantity;
                            $this->db->update('purchase_items', ['quantity_balance' => 0], ['id' => $pi->id]);
                        }
                    }
                    if ($quantity == 0) {
                        break;
                    }
                }
            }
            
        } else {
            $clause = ['purchase_id' => null, 'transfer_id' => null, 'product_id' => $product_id, 'warehouse_id' => $warehouse_id, 'batchno' => $batch_no, 'option_id' => $option_id];
            $this->site->setPurchaseItem($clause, (0 - $quantity));
        }
        $this->site->syncQuantity(null, null, null, $product_id, $batch_no);
    }

    public function updateQuantity($product_id, $warehouse_id, $quantity)
    {
        if ($this->db->update('warehouses_products', ['quantity' => $quantity], ['product_id' => $product_id, 'warehouse_id' => $warehouse_id])) {
            $this->site->syncProductQty($product_id, $warehouse_id);
            return true;
        }
        return false;
    }

    public function updateStatus($id, $status, $note)
    {
        $this->db->trans_start();
        $ostatus  = $this->resetTransferActions($id);
        $transfer = $this->getTransferByID($id);
        $items    = $this->getAllTransferItems($id, $transfer->status);
        if ($this->db->update('transfers', ['status' => $status, 'note' => $note], ['id' => $id])) {
            $tbl = $ostatus == 'completed' ? 'purchase_items' : 'transfer_items';
            $this->db->delete($tbl, ['transfer_id' => $id]);

            foreach ($items as $item) {
                $item                = (array) $item;
                $item['transfer_id'] = $id;
                $item['option_id']   = !empty($item['option_id']) && is_numeric($item['option_id']) ? $item['option_id'] : null;
                unset($item['id'], $item['variant'], $item['unit'], $item['hsn_code'], $item['second_name']);
                if ($status == 'completed') {
                    $item['date']         = date('Y-m-d');
                    $item['warehouse_id'] = $transfer->to_warehouse_id;
                    $item['status']       = 'received';
                    $this->db->insert('purchase_items', $item);
                } else {
                    $this->db->insert('transfer_items', $item);
                }

                if ($status == 'sent' || $status == 'completed') {
                    $this->syncTransderdItem($item['product_id'], $transfer->from_warehouse_id, $item['quantity'], $item['option_id'], $status);
                } else {
                    $this->site->syncQuantity(null, null, null, $item['product_id']);
                }
            }

            if($status == 'completed'){
                $this->addTransferAccountEntries($id);
            }
        }
        $this->db->trans_complete();
        if ($this->db->trans_status() === false) {
            log_message('error', 'An errors has been occurred while adding the sale (UpdateStatus:Transfers_model.php)');
        } else {
            return true;
        }
        return false;
    }
    
     public function updateStatus1($id, $status,$approval, $note)
    {
        $this->db->trans_start();
        $ostatus  = $this->resetTransferActions($id);
        $transfer = $this->getTransferByID($id);
        $items    = $this->getAllTransferItems($id, $transfer->status);
        if ($this->db->update('transfers', ['status' => $status, 'note' => $note, 'approval' => $approval], ['id' => $id])) {
            $tbl = $ostatus == 'completed' ? 'purchase_items' : 'transfer_items';
            $this->db->delete($tbl, ['transfer_id' => $id]);

            foreach ($items as $item) {
                $item                = (array) $item;
                $item['transfer_id'] = $id;
                $item['option_id']   = !empty($item['option_id']) && is_numeric($item['option_id']) ? $item['option_id'] : null;
                unset($item['id'], $item['variant'], $item['unit'], $item['hsn_code'], $item['second_name']);
                if ($status == 'completed') {
                    $item['date']         = date('Y-m-d');
                    $item['warehouse_id'] = $transfer->to_warehouse_id;
                    $item['status']       = 'received';
                    $this->db->insert('purchase_items', $item);
                } else {
                    $this->db->insert('transfer_items', $item);
                }

                if ($status == 'sent' || $status == 'completed') {
                    $this->syncTransderdItem($item['product_id'], $transfer->from_warehouse_id, $item['quantity'], $item['option_id']);
                } else {
                    $this->site->syncQuantity(null, null, null, $item['product_id']);
                }
            }
        }
        $this->db->trans_complete();
        if ($this->db->trans_status() === false) {
            log_message('error', 'An errors has been occurred while adding the sale (UpdateStatus:Transfers_model.php)');
        } else {
            return true;
        }
        return false;
    }

    public function updateTransfer($id, $data = [], $items = [], $attachments = [])
    {
        $this->db->trans_start();
        $ostatus = $this->resetTransferActions($id);
        $status  = $data['status'];
        if ($this->db->update('transfers', $data, ['id' => $id])) {
            $tbl = $ostatus == 'completed' ? 'purchase_items' : 'transfer_items';
            $this->db->delete($tbl, ['transfer_id' => $id]);

            // Code for serials starts here
            $this->db->update('sma_invoice_serials', ['tid' => 0], ['tid' => $id]);
            // Code for serials ends here

            foreach ($items as $item) {
                $item['transfer_id'] = $id;
                $item['option_id']   = !empty($item['option_id']) && is_numeric($item['option_id']) ? $item['option_id'] : null;
                if ($status == 'completed') {
                    $item['date']         = date('Y-m-d');
                    $item['warehouse_id'] = $data['to_warehouse_id'];
                    $item['status']       = 'received';
                    $this->db->insert('purchase_items', $item);
                } else {
                    $this->db->insert('transfer_items', $item);
                }

                // Code for serials here
                $serials_quantity = $item['quantity'];
                $serials_gtin = $item['product_code'];
                $serials_batch_no = $item['batchno'];
                
                $this->db->select('sma_invoice_serials.*');
                $this->db->from('sma_invoice_serials');
                $this->db->join('sma_purchases', 'sma_invoice_serials.pid = sma_purchases.id');
                $this->db->where('sma_invoice_serials.gtin', $serials_gtin);
                $this->db->where('sma_invoice_serials.batch_no', $serials_batch_no);
                $this->db->where('sma_invoice_serials.sid', 0);
                $this->db->where('sma_invoice_serials.rsid', 0);
                $this->db->where('sma_invoice_serials.tid', 0);
                $this->db->where('sma_invoice_serials.pid !=', 0);
                $this->db->where('sma_purchases.status', 'received');
                $this->db->limit($serials_quantity);

                $notification_serials = $this->db->get();
                if ($notification_serials->num_rows() > 0) {
                    foreach (($notification_serials->result()) as $row) {
                        $this->db->update('sma_invoice_serials', ['tid' => $id], ['serial_number' => $row->serial_number, 'batch_no' => $row->batch_no, 'gtin' => $row->gtin]);
                    }
                }
                // Code for serials end here

                if($ostatus == 'save' && $data['status'] == 'sent'){
                    $this->syncTransderdSavedItems($item['product_id'], $data['from_warehouse_id'], $item['batchno'], $item['quantity'], $item['option_id'], $status, 'edit');
                }else if($data['status'] == 'sent' || $data['status'] == 'completed'){
                    $this->syncTransderdItem($item['product_id'], $data['from_warehouse_id'], $item['batchno'], $item['quantity'], $item['option_id'], $status, 'edit');
                }
            }

            if (!empty($attachments)) {
                foreach ($attachments as $attachment) {
                    $attachment['subject_id']   = $id;
                    $attachment['subject_type'] = 'transfer';
                    $this->db->insert('attachments', $attachment);
                }
            }

            if($status == 'completed'){
                $this->addTransferAccountEntries($id);
            }

        }
        $this->db->trans_complete();
        if ($this->db->trans_status() === false) {
            log_message('error', 'An errors has been occurred while adding the sale (Update:Transfers_model.php)');
        } else {
            return true;
        }

        return false;
    }
}
