<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Transfers_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->load->admin_model('Inventory_model');
    }


    public function get_rasd_required_fields($data)
    {
        //$notification_id = $data['notification_id'];
        $transfer_id = $data['transfer_id'];
        $this->db->select('status');
        $this->db->from('sma_transfers');
        $this->db->where("id", $transfer_id);
        $query = $this->db->get();
        $status = "completed";
        if ($query->num_rows() > 0) {
            $status = $query->row()->status;
        }
        if ($status != "completed") {
            return ['payload' => [], 'user' => "", 'pass' => "", 'status' => $status];
        }

        $source_warehouse_id = $data['source_warehouse_id'];
        $desitnation_warehouse_id = $data['destination_warehouse_id'];
        $products = $data['products'];

        /**Get GLNs */
        $this->db->select("gln,rasd_user, rasd_pass");
        $this->db->from("sma_warehouses");
        $this->db->where('id', $source_warehouse_id);
        $query = $this->db->get();
        $source_gln = "";
        $destination_gln = "";
        $rasd_user = "";
        $rasd_pass = "";
        $rasd_pharmacy_user = "";
        $rasd_pharmacy_password = "";
        if ($query->num_rows() > 0) {
            $source_gln = $query->row()->gln;
            $rasd_user = $query->row()->rasd_user;
            $rasd_pass = $query->row()->rasd_pass;
        }

        /**Get GLNs */
        $this->db->select("gln,rasd_user,rasd_pass");
        $this->db->from("sma_warehouses");
        $this->db->where('id', $desitnation_warehouse_id);
        $query = $this->db->get();

        if ($query->num_rows() > 0) {
            $destination_gln = $query->row()->gln;
            $rasd_pharmacy_user = $query->row()->rasd_user;
            $rasd_pharmacy_password = $query->row()->rasd_pass;

        }

        // $payload = [
        //     "DicOfDic" => [
        //         "2762" => [
        //             "215" =>  $destination_gln
        //         ],
        //         "MH" => [
        //             "MN" => "2756",
        //             "222" =>  $source_gln
        //         ]
        //     ],
        //     "DicOfDT" =>  [
        //         "2762" => []
        //     ]
        // ];

        $c_2762 = [];
        $c_2760 = [];
        $to_update = [];
        $count = 0;

        $batch_size = 20; // Max size per payload batch
        $payload_index = 0;
        $payloads = [];
        $payloads_accept_dispatch = [];
        foreach ($products as $product) {
            $qty = (int) $product['quantity'];
            $expiry = $product['expiry'] . " 00:00:00";

            $gtin = $product['product_code'];
            if (strlen($gtin) < 13) {
                $gtin = str_pad($gtin, 13, "0", STR_PAD_LEFT); // Prepend zero if needed
            }

            /*$this->db->select("id, qty_remaining");
            $this->db->from("sma_rasd_notifcations_map");
            $this->db->where('gtin', $gtin);
            $this->db->where('batch', $product['batchno']);
            $this->db->where("qty_remaining >=", $qty);
            $this->db->where('expiry_date', $expiry);
            $query = $this->db->get();

            if ($query->num_rows() > 0) {
                $qty_remaining = $query->row()->qty_remaining;
                $to_update[] = [
                    "id" => $query->row()->id,
                    "qty" => (int) $qty_remaining - $qty
                ];
            } else {
                log_message("info", "NO DATA");
                continue;
            }*/

            $c_2762[] = [
                "223" => $gtin,
                "2766" => $product['batchno'],
                "220" => $product['expiry'],
                "224" => (string) $qty
            ];

            $c_2760[] = [
                "223" => $gtin,
                "219" => $product['batchno'],
                "220" => $product['expiry'],
                "224" => (string) $qty
            ];


            // If c_2762 reaches the batch size, create a payload
            if (count($c_2762) == $batch_size) {
                $payloads[$payload_index] = [
                    "DicOfDic" => [
                        "2762" => ["215" => $destination_gln, "3008" => "3010"],
                        "MH" => ["MN" => "2756", "222" => $source_gln]
                    ],
                    "DicOfDT" => ["2762" => $c_2762]
                ];
                $payloads_accept_dispatch[$payload_index] = $this->get_accept_dispatch_lot_params($destination_gln, $source_gln, $c_2760);

                $c_2762 = []; // Reset for next batch
                $c_2760 = [];
                $payload_index++;

            }

        }

        //Add Remaining.
        if (!empty($c_2762)) {

            $payloads[$payload_index] = [
                "DicOfDic" => [
                    "2762" => ["215" => $destination_gln, "3008" => "3010"],
                    "MH" => ["MN" => "2756", "222" => $source_gln]
                ],
                "DicOfDT" => ["2762" => $c_2762]
            ];

            $payloads_accept_dispatch[$payload_index] = $this->get_accept_dispatch_lot_params($destination_gln, $source_gln, $c_2760);
        }

        return [
            'payload' => $payloads,
            'user' => $rasd_user,
            'pass' => $rasd_pass,
            'status' => $status,
            'source_gln' => $source_gln,
            'destination_gln' => $destination_gln,
            'pharmacy_user' => $rasd_pharmacy_user,
            'pharmacy_pass' => $rasd_pharmacy_password,
            'payload_for_accept_dispatch' => $payloads_accept_dispatch,
            'update_map_table' => $to_update
        ];
    }
    public function update_notification_map($data)
    {
        foreach ($data as $row) {
            $d = ["qty_remaining" => $row['qty']];
            $this->db->update('sma_rasd_notifcations_map', $d, ['id' => $row['id']]);
        }
    }

    public function get_accept_dispatch_lot_params($pharmacy_gln, $warehouse_gln, $params)
    {

        $payload =
            [
                "DicOfDic" => [
                    "2760" => [
                        "215" => $warehouse_gln
                    ],
                    "MH" => [
                        "MN" => "2754",
                        "222" => $pharmacy_gln
                    ]
                ],
                "DicOfDT" => [
                    "2760" => $params
                ]
            ];
        return $payload;
    }
    public function get_cost_price_grand_total($transfer_id)
    {

        $this->db->select('SUM(quantity * net_unit_cost) as total_cost_price');
        $this->db->from('sma_purchase_items');
        $this->db->where('transfer_id', $transfer_id);
        $query = $this->db->get();
        //echo $this->db->last_query(); exit; 
        if ($query->num_rows() > 0) {
            return $query->row();
        }
    }

    public function addTransferAccountEntries($transferId)
    {

        $transfer = $this->getTransferByID($transferId);
        $result = $this->get_cost_price_grand_total($transferId);

        $toWareHouseId = $transfer->to_warehouse_id;
        $fromWareHouseId = $transfer->from_warehouse_id;

        $toWareHouse = $this->site->getWarehouseByID($toWareHouseId);
        $fromWareHouse = $this->site->getWarehouseByID($fromWareHouseId);
        $goodsTrasitWareHouse = $this->site->getGoodsTrasitWareHouse();


        // Credit & Debit to one same Account (Goods in Transit)
        $accountsTotal = $result->total_cost_price + $result->total_cost_price;

        /*Accounts Entries*/
        $entry = array(
            'entrytype_id' => 4,
            'transaction_type' => 'transferorder',
            'number' => 'TR-' . $transfer->transfer_no,
            'date' => date('Y-m-d'),
            'dr_total' => $accountsTotal,
            'cr_total' => $accountsTotal,
            'notes' => 'Transfer Reference: ' . $transfer->transfer_no . ' Date: ' . date('Y-m-d H:i:s'),
            'tid' => $transfer->id
        );

        $add = $this->db->insert('sma_accounts_entries', $entry);
        $insert_id = $this->db->insert_id();

        $entryitemdata = array();

        $entryitemdata[] = array(
            'Entryitem' => array(
                'entry_id' => $insert_id,
                'dc' => 'D',
                'ledger_id' => $goodsTrasitWareHouse->inventory_ledger,

                'amount' => $result->total_cost_price,
                'narration' => 'goods in transit'
            )
        );

        $entryitemdata[] = array(
            'Entryitem' => array(
                'entry_id' => $insert_id,
                'dc' => 'C',
                'ledger_id' => $fromWareHouse->inventory_ledger,
                'amount' => $result->total_cost_price,
                'narration' => 'inventry'
            )
        );

        $entryitemdata[] = array(
            'Entryitem' => array(
                'entry_id' => $insert_id,
                'dc' => 'C',
                'ledger_id' => $goodsTrasitWareHouse->inventory_ledger,
                'amount' => $result->total_cost_price,
                'narration' => 'goods in transit'
            )
        );

        $entryitemdata[] = array(
            'Entryitem' => array(
                'entry_id' => $insert_id,
                'dc' => 'D',
                'ledger_id' => $toWareHouse->inventory_ledger,
                'amount' => $result->total_cost_price,
                'narration' => 'inventry'
            )
        );

        foreach ($entryitemdata as $row => $itemdata) {

            $this->db->insert('sma_accounts_entryitems', $itemdata['Entryitem']);
        }
    }

    public function transferPurchaseInvoice($data = [], $items = [], $attachments = [])
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
                $item['option_id'] = !empty($item['option_id']) && is_numeric($item['option_id']) ? $item['option_id'] : null;

                $this->db->insert('transfer_items', $item);
                $inserted_item_id = $this->db->insert_id();

                if (!empty($attachments)) {
                    foreach ($attachments as $attachment) {
                        $attachment['subject_id'] = $transfer_id;
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
                $real_cost = $item['real_cost'];
                unset($item['real_cost']);
                $item['option_id'] = !empty($item['option_id']) && is_numeric($item['option_id']) ? $item['option_id'] : null;
                if ($status == 'completed') {
                    $item['date'] = date('Y-m-d');
                    $item['warehouse_id'] = $data['to_warehouse_id'];
                    $item['status'] = 'received';
                    //$this->db->insert('purchase_items', $item);
                } else {
                    //$this->db->insert('transfer_items', $item);
                }

                $this->db->insert('purchase_items', $item);
                if ($status != 'completed') {
                    $this->db->insert('transfer_items', $item);
                }

                if (!empty($attachments)) {
                    foreach ($attachments as $attachment) {
                        $attachment['subject_id'] = $transfer_id;
                        $attachment['subject_type'] = 'transfer';
                        $this->db->insert('attachments', $attachment);
                    }
                }

                if ($status == 'sent' || $status == 'completed') {
                    if ($status == 'sent') {
                        ////Inventory Movement - Transfer Out
                        $this->Inventory_model->add_movement($item['product_id'], $item['batchno'], 'transfer_out', $item['quantity'], $data['from_warehouse_id'], $transfer_id, $item['net_unit_cost'], $item['expiry'], $item['sale_price'], $real_cost, $item['avz_item_code'], NULL, NULL, $item['sale_price'], $data['date']);
                    }

                    if ($status == 'completed') {
                        //Inventory Movement - Transfer IN
                        $this->Inventory_model->add_movement($item['product_id'], $item['batchno'], 'transfer_in', $item['quantity'], $data['to_warehouse_id'], $transfer_id, $item['net_unit_cost'], $item['expiry'], $item['sale_price'], $real_cost, $item['avz_item_code'], NULL, NULL, $item['sale_price'], $data['date']);
                        ////Inventory Movement - Transfer Out
                        $this->Inventory_model->add_movement($item['product_id'], $item['batchno'], 'transfer_out', $item['quantity'], $data['from_warehouse_id'], $transfer_id, $item['net_unit_cost'], $item['expiry'], $item['sale_price'], $real_cost, $item['avz_item_code'], NULL, NULL, $item['sale_price'], $data['date']);
                    }

                    // This is commented on 2025-02-18 for no valid use case
                    //$this->syncTransderdItem($item['product_id'], $data['from_warehouse_id'], $item['batchno'], $item['quantity'], $item['option_id'], $status, 'add');
                }
            }

            if ($status == 'completed') {
                $this->addTransferAccountEntries($transfer_id);
            }

        }
        $this->db->trans_complete();
        if ($this->db->trans_status() === false) {
            log_message('error', 'An errors has been occurred while adding the sale (Add:Transfers_model.php)');
        } else {
            return $transfer_id;
        }
        return false;
    }

    public function getAverageCost($item_batchno, $item_code)
    {
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
                if ($row->quantity > 0) {
                    $row->cost_price = ($row->total_cost_price / $row->quantity);
                } else {
                    $row->cost_price = 0;
                }
                $totalPurchases[] = $row;
            }
        }

        return $totalPurchases;
    }

    public function deleteTransfer($id)
    {
        $this->db->trans_start();
        $ostatus = $this->resetTransferActions($id, 1);
        $oitems = $this->getAllTransferItems($id, $ostatus);
        $this->site->log('Transfer', ['model' => $this->getTransferByID($id), 'items' => $oitems]);
        $tbl = $ostatus == 'completed' ? 'purchase_items' : 'transfer_items';

        if ($this->db->delete('transfers', ['id' => $id]) && $this->db->delete($tbl, ['transfer_id' => $id]) && ($ostatus == 'save' || $ostatus == 'sent')) {
            foreach ($oitems as $item) {
                $this->site->syncQuantity(null, null, null, $item->product_id);
            }

            //$this->db->update('sma_invoice_serials', ['tid' => 0], ['tid' => $id]);

            $this->db->where('reference_id', $id);
            $this->db->where_in('type', ['transfer_in', 'transfer_out']);
            $this->db->delete('inventory_movements');
        } else {
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

    public function getAllTransferItemsForModule($transfer_id, $status, $warehouse_id=null)
    {
        if ($status == 'completed' || $status == 'save' || $status == 'sent') {
            // $this->db->select('purchase_items.*, product_variants.name as variant, products.unit, products.hsn_code as hsn_code, products.second_name as second_name')
            //     ->from('purchase_items')
            //     ->join('products', 'products.id=purchase_items.product_id', 'left')
            //     ->join('product_variants', 'product_variants.id=purchase_items.option_id', 'left')
            //     ->group_by('purchase_items.id')
            //     ->where('transfer_id', $transfer_id)
            //     ->order_by('purchase_items.id', 'DESC');

            $sql = "SELECT 
                        pi.*,
                       
                        p.unit,
                        p.hsn_code AS hsn_code,
                        p.second_name AS second_name,
                        im_summary.current_quantity
                    FROM sma_purchase_items pi
                    LEFT JOIN sma_products p 
                        ON p.id = pi.product_id
                    
                    JOIN (
                    SELECT 
                        avz_item_code,
                        batch_number,
                        expiry_date,
                        net_unit_sale,
                        net_unit_cost,
                        real_unit_cost,
                        SUM(quantity) AS current_quantity
                    FROM sma_inventory_movements
                    WHERE location_id = " . $warehouse_id . " 

                    GROUP BY avz_item_code, batch_number, expiry_date
                ) im_summary
                    ON im_summary.avz_item_code = pi.avz_item_code    
                    WHERE pi.transfer_id = $transfer_id
                    GROUP BY pi.id
                    ORDER BY pi.id DESC
                    ";
            $q = $this->db->query($sql);

        } else {
            $this->db->select('transfer_items.*, SUM(IFNULL(im.quantity, 0)) as base_quantity, im.avz_item_code, product_variants.name as variant, products.unit, products.hsn_code as hsn_code, products.second_name as second_name')
                ->from('transfer_items')
                ->join('products', 'products.id=transfer_items.product_id', 'left')
                ->join('product_variants', 'product_variants.id=transfer_items.option_id', 'left')
                ->join('inventory_movements im', 'transfer_items.avz_item_code = im.avz_item_code', 'left')
                ->group_by(['transfer_items.id', 'im.avz_item_code'])
                ->where('transfer_id', $transfer_id)
                ->order_by('transfer_items.id', 'DESC');
                $q = $this->db->get();
        }
        
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
    }

    public function getAllTransferItems($transfer_id, $status)
    {

        $this->db->select('purchase_items.*, product_variants.name as variant, products.unit, products.hsn_code as hsn_code, products.second_name as second_name')
            ->from('purchase_items')
            ->join('products', 'products.id=purchase_items.product_id', 'left')
            ->join('product_variants', 'product_variants.id=purchase_items.option_id', 'left')
            ->group_by('purchase_items.id')
            ->order_by('purchase_items.id', 'ASC')
            ->where('transfer_id', $transfer_id);

        $q = $this->db->get();
        //echo $this->db->last_query();exit;
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        } else {
            $this->db->select('transfer_items.*, SUM(IFNULL(im.quantity, 0)) as base_quantity, im.avz_item_code, product_variants.name as variant, products.unit, products.hsn_code as hsn_code, products.second_name as second_name')
                ->from('transfer_items')
                ->join('products', 'products.id=transfer_items.product_id', 'left')
                ->join('product_variants', 'product_variants.id=transfer_items.option_id', 'left')
                ->join('inventory_movements im', 'transfer_items.avz_item_code = im.avz_item_code', 'left')
                ->group_by(['transfer_items.id', 'im.avz_item_code'])
                ->order_by('transfer_items.id', 'ASC')
                ->where('transfer_id', $transfer_id);

            $q = $this->db->get();
            //echo $this->db->last_query();exit;
            if ($q->num_rows() > 0) {
                foreach (($q->result()) as $row) {
                    $data[] = $row;
                }
                return $data;
            }

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

    public function getProductById($id)
    {
        $q = $this->db->get_where('products', ['id' => $id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }

        return false;
    }

    public function getTransferByReferenceId($transfer_no)
    {
        $q = $this->db->get_where('transfers', ['transfer_no' => $transfer_no], 1);
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

        // removed from select ->  purchase_items.serial_number
        $this->db->select('products.id, products.price, code, name, SUM(sma_inventory_movements.quantity) as quantity, cost, tax_rate, sma_products.type, unit, purchase_unit, tax_method')
            ->join('inventory_movements', 'inventory_movements.product_id=products.id', 'left')
            //   ->join('warehouses_products', 'warehouses_products.product_id=products.id', 'left')
            //    ->join('purchase_items', 'purchase_items.product_id=products.id', 'left')
            ->group_by('products.id');
        if ($this->Settings->overselling) {
            $this->db->where("products.type = 'standard' AND (name LIKE '%" . $term . "%' OR code LIKE '%" . $term . "%' OR  concat(name, ' (', code, ')') LIKE '%" . $term . "%')");
        } else {
            $this->db->where("products.type = 'standard' AND inventory_movements.location_id = '" . $warehouse_id . "' AND "
                . "(name LIKE '%" . $term . "%' OR code LIKE '%" . $term . "%' OR  concat(name, ' (', code, ')') LIKE '%" . $term . "%')");
        }
        $this->db->having("SUM(sma_inventory_movements.quantity)>0");
        $this->db->limit($limit);
        $q = $this->db->get('products');
        //echo  $this->db->last_query(); exit; 
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {

                $row->serial_number = '';
                $data[] = $row;
            }
            return $data;
        }

    }

    public function getProductNamesWithBatches__BK($term, $warehouse_id, $limit = 10)
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
        // $q = $this->db->get_where('warehouses_products', ['warehouse_id' => $warehouse_id, 'product_id' => $product_id, 'batchno' => $item_batchno], 1);
        // if ($q->num_rows() > 0) {
        //     return $q->row();
        // }  
        $this->db->select('SUM(inv.quantity) as quantity ');
        $this->db->from('inventory_movements inv');
        // $this->db->join('warehouses_products wp', 'wp.warehouse_id=inv.location_id AND inv.product_id=wp.product_id AND wp.batchno=inv.batch_number', 'LEFT'); 
        $this->db->where('inv.location_id', $warehouse_id);
        $this->db->where('inv.product_id', $product_id);
        $this->db->where('inv.batch_number', $item_batchno);
        $query = $this->db->get();
        if ($query->num_rows() > 0) {
            return $query->row();
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
        $oitems = $this->getAllTransferItems($id, $otransfer->status);
        $ostatus = $otransfer->status;
        if ($ostatus == 'sent' || $ostatus == 'completed') {
            foreach ($oitems as $item) {
                $option_id = (isset($item->option_id) && !empty($item->option_id)) ? $item->option_id : null;
                $clause = ['purchase_id' => null, 'transfer_id' => null, 'product_id' => $item->product_id, 'warehouse_id' => $otransfer->from_warehouse_id, 'option_id' => $option_id];
                $this->site->setPurchaseItem($clause, $item->quantity);
                if ($delete) {
                    $option_id = (isset($item->option_id) && !empty($item->option_id)) ? $item->option_id : null;
                    $clause = ['purchase_id' => null, 'transfer_id' => null, 'product_id' => $item->product_id, 'warehouse_id' => $otransfer->to_warehouse_id, 'option_id' => $option_id];
                    $this->site->setPurchaseItem($clause, ($item->quantity_balance - $item->quantity));
                }
            }
        }
        return $ostatus;
    }

    public function syncTransderdSavedItems($product_id, $warehouse_id, $batch_no, $quantity, $option_id = null, $status, $type)
    {
        if ($pis = $this->site->getPurchasedItemsWithBatch($product_id, $warehouse_id, $batch_no, $option_id)) {
            if (($status == "sent" && $type == 'edit')) {
                $balance_qty = $quantity;
                foreach ($pis as $pi) {
                    if ($balance_qty <= $quantity && $quantity > 0) {
                        if ($pi->quantity_balance >= $quantity) {
                            $balance_qty = $pi->quantity_balance - $quantity;
                            $this->db->update('purchase_items', ['quantity_balance' => $balance_qty], ['id' => $pi->id]);
                            $quantity = 0;
                        } elseif ($quantity > 0) {
                            $quantity = $quantity - $pi->quantity_balance;
                            $balance_qty = $quantity;
                            $this->db->update('purchase_items', ['quantity_balance' => 0], ['id' => $pi->id]);
                        }
                    }
                    if ($quantity == 0) {
                        break;
                    }
                }
            }

        }
        /* This block seems with no real use */
        /*else {
            $clause = ['purchase_id' => null, 'transfer_id' => null, 'product_id' => $product_id, 'warehouse_id' => $warehouse_id, 'batchno' => $batch_no, 'option_id' => $option_id];
            $this->site->setPurchaseItem($clause, (0 - $quantity));
        }*/
        $this->site->syncQuantity(null, null, null, $product_id, $batch_no);
    }

    public function syncTransderdItemFromInvoice($product_id, $warehouse_id, $batch_no, $quantity, $option_id = null, $status, $type)
    {
        if ($pis = $this->site->getPurchasedItemsWithBatch($product_id, $warehouse_id, $batch_no, $option_id)) {
            if (($status == "sent" && $type == 'add') || ($status == "completed" && $type == 'add')) {
                $balance_qty = $quantity;
                foreach ($pis as $pi) {
                    if ($balance_qty <= $quantity && $quantity > 0) {
                        if ($pi->quantity_balance >= $quantity) {
                            $balance_qty = $pi->quantity_balance - $quantity;
                            $this->db->update('purchase_items', ['quantity_balance' => $balance_qty], ['id' => $pi->id]);
                            $quantity = 0;
                        } elseif ($quantity > 0) {
                            $quantity = $quantity - $pi->quantity_balance;
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
        if ($pis = $this->site->getPurchasedItemsWithBatch($product_id, $warehouse_id, $batch_no, $option_id)) {
            if (($status == "sent" && $type == 'add') || ($status == "completed" && $type == 'add')) {
                $balance_qty = $quantity;
                foreach ($pis as $pi) {
                    if ($balance_qty <= $quantity && $quantity > 0) {
                        if ($pi->quantity_balance >= $quantity) {
                            $balance_qty = $pi->quantity_balance - $quantity;
                            $this->db->update('purchase_items', ['quantity_balance' => $balance_qty], ['id' => $pi->id]);
                            $quantity = 0;
                        } elseif ($quantity > 0) {
                            $quantity = $quantity - $pi->quantity_balance;
                            $balance_qty = $quantity;
                            $this->db->update('purchase_items', ['quantity_balance' => 0], ['id' => $pi->id]);
                        }
                    }
                    if ($quantity == 0) {
                        break;
                    }
                }
            }

        }
        /* This block seems useless with no valid use case */
        /*else {
            $clause = ['purchase_id' => null, 'transfer_id' => null, 'product_id' => $product_id, 'warehouse_id' => $warehouse_id, 'batchno' => $batch_no, 'option_id' => $option_id];
            $this->site->setPurchaseItem($clause, (0 - $quantity));
        }*/
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
        $ostatus = $this->resetTransferActions($id);
        $transfer = $this->getTransferByID($id);
        $items = $this->getAllTransferItems($id, $transfer->status);
        if ($this->db->update('transfers', ['status' => $status, 'note' => $note], ['id' => $id])) {
            $tbl = $ostatus == 'completed' ? 'purchase_items' : 'transfer_items';
            $this->db->delete($tbl, ['transfer_id' => $id]);

            foreach ($items as $item) {
                $item = (array) $item;
                $item['transfer_id'] = $id;
                $item['option_id'] = !empty($item['option_id']) && is_numeric($item['option_id']) ? $item['option_id'] : null;
                unset($item['id'], $item['variant'], $item['unit'], $item['hsn_code'], $item['second_name']);
                if ($status == 'completed') {
                    $item['date'] = date('Y-m-d');
                    $item['warehouse_id'] = $transfer->to_warehouse_id;
                    $item['status'] = 'received';
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

            if ($status == 'completed') {
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

    public function updateStatus1($id, $status, $approval, $note)
    {
        $this->db->trans_start();
        $ostatus = $this->resetTransferActions($id);
        $transfer = $this->getTransferByID($id);
        $items = $this->getAllTransferItems($id, $transfer->status);
        if ($this->db->update('transfers', ['status' => $status, 'note' => $note, 'approval' => $approval], ['id' => $id])) {
            $tbl = $ostatus == 'completed' ? 'purchase_items' : 'transfer_items';
            $this->db->delete($tbl, ['transfer_id' => $id]);

            foreach ($items as $item) {
                $item = (array) $item;
                $item['transfer_id'] = $id;
                $item['option_id'] = !empty($item['option_id']) && is_numeric($item['option_id']) ? $item['option_id'] : null;
                unset($item['id'], $item['variant'], $item['unit'], $item['hsn_code'], $item['second_name']);
                if ($status == 'completed') {
                    $item['date'] = date('Y-m-d');
                    $item['warehouse_id'] = $transfer->to_warehouse_id;
                    $item['status'] = 'received';
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
        $status = $data['status'];
        if ($this->db->update('transfers', $data, ['id' => $id])) {
            $tbl = $ostatus == 'completed' ? 'purchase_items' : 'transfer_items';
            $this->db->delete('purchase_items', ['transfer_id' => $id]);
            $this->db->delete('transfer_items', ['transfer_id' => $id]);

            /* Delete Inventory */
            $this->db->where('reference_id', $id);
            $this->db->where_in('type', ['transfer_in', 'transfer_out']);
            $this->db->delete('inventory_movements');

            foreach ($items as $item) {
                $item['transfer_id'] = $id;
                $real_cost = $item['real_cost'];
                unset($item['real_cost']);
                $item['option_id'] = !empty($item['option_id']) && is_numeric($item['option_id']) ? $item['option_id'] : null;
                if ($status == 'completed') {
                    $item['date'] = date('Y-m-d');
                    $item['warehouse_id'] = $data['to_warehouse_id'];
                    $item['status'] = 'received';
                    //$this->db->insert('purchase_items', $item);
                } else {
                    $this->db->insert('transfer_items', $item);
                }
                $this->db->insert('purchase_items', $item);

                if ($ostatus == 'save' && $data['status'] == 'sent') {
                    //Inventory Movement - Transfer Out
                    $this->Inventory_model->add_movement($item['product_id'], $item['batchno'], 'transfer_out', $item['quantity'], $data['from_warehouse_id'], $id, $item['net_unit_cost'], $item['expiry'], $item['sale_price'], $real_cost, $item['avz_item_code'], NULL, NULL, $item['sale_price'], $data['date']);
                } else if ($ostatus == 'save' && $data['status'] == 'completed') {
                    //Inventory Movement - Transfer IN
                    $this->Inventory_model->add_movement($item['product_id'], $item['batchno'], 'transfer_in', $item['quantity'], $data['to_warehouse_id'], $id, $item['net_unit_cost'], $item['expiry'], $item['sale_price'], $real_cost, $item['avz_item_code'], NULL, NULL, $item['sale_price'], $data['date']);
                    //Inventory Movement - Transfer Out
                    $this->Inventory_model->add_movement($item['product_id'], $item['batchno'], 'transfer_out', $item['quantity'], $data['from_warehouse_id'], $id, $item['net_unit_cost'], $item['expiry'], $item['sale_price'], $real_cost, $item['avz_item_code'], NULL, NULL, $item['sale_price'], $data['date']);
                } else if ($ostatus == 'sent' && $data['status'] == 'completed') {
                    //Inventory Movement - Transfer OUT
                    $this->Inventory_model->add_movement($item['product_id'], $item['batchno'], 'transfer_out', $item['quantity'], $data['from_warehouse_id'], $id, $item['net_unit_cost'], $item['expiry'], $item['sale_price'], $real_cost, $item['avz_item_code'], NULL, NULL, $item['sale_price'], $data['date']);
                    //Inventory Movement - Transfer IN
                    $this->Inventory_model->add_movement($item['product_id'], $item['batchno'], 'transfer_in', $item['quantity'], $data['to_warehouse_id'], $id, $item['net_unit_cost'], $item['expiry'], $item['sale_price'], $real_cost, $item['avz_item_code'], NULL, NULL, $item['sale_price'], $data['date']);
                } else if ($ostatus == 'sent' && $data['status'] == 'sent') {
                    //Inventory Movement - Transfer OUT
                    $this->Inventory_model->add_movement($item['product_id'], $item['batchno'], 'transfer_out', $item['quantity'], $data['from_warehouse_id'], $id, $item['net_unit_cost'], $item['expiry'], $item['sale_price'], $real_cost, $item['avz_item_code'], NULL, NULL, $item['sale_price'], $data['date']);
                }
            }

            if (!empty($attachments)) {
                foreach ($attachments as $attachment) {
                    $attachment['subject_id'] = $id;
                    $attachment['subject_type'] = 'transfer';
                    $this->db->insert('attachments', $attachment);
                }
            }

            if ($status == 'completed') {
                $this->addTransferAccountEntries($id);
            }

        }
        $this->db->trans_complete();
        if ($this->db->trans_status() === false) {
            log_message('error', 'An errors has been occurred while adding the sale (Update:Transfers_model.php)');
        } else {
            return $id;
        }

        return false;
    }

    public function getAllPurchaseItemsWithQuantity($purchase_id)
    {
        $sql = "SELECT pi.*, im.*, SUM(im.quantity) AS current_quantity 
        FROM sma_purchase_items pi 
        LEFT JOIN sma_inventory_movements im ON pi.avz_item_code = im.avz_item_code 
        AND im.location_id = 32 
        AND im.product_id = pi.product_id
        AND im.type IN ('purchase', 'transfer_out') 
        WHERE pi.purchase_id = $purchase_id GROUP BY pi.id HAVING current_quantity > 0 ORDER BY pi.id DESC
     ";
        $q = $this->db->query($sql);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }       
        return false;
    }
}
