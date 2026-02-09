<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Sales_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->load->admin_model('Inventory_model');
    }

    public function get_rasd_required_fields($data ){
        //$notification_id = $data['notification_id'];
        $sale_id = $data['sale_id'];
        $this->db->select('sale_status');
        $this->db->from('sma_sales');
        $this->db->where("id", $sale_id);
        $query = $this->db->get();
        $status = "completed";
        if($query->num_rows() > 0){
            $status = $query -> row()->sale_status;
        }
        /*if($status != "completed"){
            return ['payload' => [], 'user' => "", 'pass' => "", 'status' => $status];
        }*/

        $source_warehouse_id = $data['source_warehouse_id'];
        $desitnation_customer_id = $data['destination_customer_id'];
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
        if($query->num_rows() > 0){
            $source_gln = $query -> row()->gln;
            $rasd_user = $query ->row()->rasd_user;
            $rasd_pass = $query ->row()->rasd_pass;
        }

         /**Get GLNs */
        $this->db->select("gln");
        $this->db->from("sma_companies");
        $this->db->where('id', $desitnation_customer_id);
        $query = $this->db->get();

        if($query->num_rows() > 0){
            $destination_gln = $query -> row()->gln;
        }

        $c_2762 = [];
        $c_2760  = [];
        $to_update = [];
        $count = 0;
      
        $batch_size = 20; // Max size per payload batch
        $payload_index = 0;
        $payloads = [];
        $payloads_accept_dispatch = [];
        foreach($products as $product){
            $qty = (int) $product['quantity'];
            $expiry = $product['expiry'] . " 00:00:00";

            $gtin = $product['product_code'];
            if (strlen($gtin) < 13) {  
                $gtin = str_pad($gtin, 13, "0", STR_PAD_LEFT); // Prepend zero if needed
            }

            $c_2762[] = [
                "223" => $gtin,
                "2766" => $product['batch_no'],
                "220" => $product['expiry'],
                "224" => (string) $qty
            ];

            $c_2760[] = [
                "223" => $gtin,
                "219" => $product['batch_no'],
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
                //$payloads_accept_dispatch[$payload_index] =  $this -> get_accept_dispatch_lot_params($destination_gln, $source_gln, $c_2760);

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
         
             //$payloads_accept_dispatch[$payload_index] = $this -> get_accept_dispatch_lot_params($destination_gln, $source_gln, $c_2760);
        }

        return ['payload' => $payloads, 
            'user' => $rasd_user,
            'pass' => $rasd_pass, 
            'status' => $status, 
            'source_gln'  =>$source_gln, 
            'destination_gln' => $destination_gln
            //'pharmacy_user' => $rasd_pharmacy_user,
            //'pharmacy_pass' => $rasd_pharmacy_password,
            //'payload_for_accept_dispatch' =>$payloads_accept_dispatch,
            //'update_map_table' =>   $to_update 
        ];
    }

    public function getSaleLabels($sale_id){
        $q = $this->db->get_where('sale_labels', ['sale_id' => $sale_id], 1);
        if ($q->num_rows() > 0) {
            $row = $q->row();
            
            return $row;
        }
        return false;
    }

    public function verifyLabel($sale_id){
        $this->db->update('sales', ['sale_status' => 'label_verifired'], ['id' => $sale_id]);

        return $sale_id;
    }

    public function updateSaleLabel($sale_id, $number_of_cartons, $refrigirated_items){
        $data = array();
        $data['sale_id'] = $sale_id;
        $data['number_of_cartons'] = $number_of_cartons;
        $data['refrigerated_items'] = $refrigirated_items;
        //$data['created_by'] = $this->session->userdata('user_id');
        $data['updated_by'] = $this->session->userdata('user_id');
        //$data['date_created'] = date('Y-m-d H:i:s');

        $this->db->trans_start();
        $this->db->where('sale_id', $sale_id);
        $this->db->update('sale_labels', $data);

        $this->db->update('sales', ['sale_status' => 'label_verifired'], ['id' => $sale_id]);

        $this->db->trans_complete();

        if ($this->db->trans_status() === false) {
            log_message('error', 'An error occurred while updating the label (updateSaleLabel: Sales_model.php)');
            return false;
        }

        return true; // Return the inserted label ID
    }

    public function addSaleLabel($sale_id, $number_of_cartons, $refrigirated_items){
        $data = array();
        $data['sale_id'] = $sale_id;
        $data['number_of_cartons'] = $number_of_cartons;
        $data['refrigerated_items'] = $refrigirated_items;
        $data['created_by'] = $this->session->userdata('user_id');
        $data['updated_by'] = $this->session->userdata('user_id');
        $data['date_created'] = date('Y-m-d H:i:s');

        $this->db->trans_start();
        $this->db->insert('sale_labels', $data);
        $label_id = $this->db->insert_id();

        $this->db->update('sales', ['sale_status' => 'added_label'], ['id' => $sale_id]);

        $this->db->trans_complete();

        if ($this->db->trans_status() === false) {
            log_message('error', 'An error occurred while adding the label (addSaleLabel: Sales_model.php)');
            return false;
        }

        return $label_id; // Return the inserted label ID
    }

    public function addDriver($sale_id, $driver_id, $address, $customer, $sale_ref_no){
        $data = array();
        $data['date'] = date('Y-m-d');
        $data['sale_id'] = $sale_id;
        $data['sale_reference_no'] = $sale_ref_no;
        $data['delivered_by'] = $driver_id;
        $data['customer'] = $customer;
        $data['address'] = $address;
        $data['status'] = 'in_delivery';
        $data['created_by'] = $this->session->userdata('user_id');
        $data['updated_by'] = $this->session->userdata('user_id');
        $data['updated_at'] = date('Y-m-d H:i:s');

        $this->db->trans_start();
        $this->db->insert('deliveries', $data);
        $delivery_id = $this->db->insert_id();
        $this->db->update('sales', ['sale_status' => 'driver_assigned'], ['id' => $sale_id]);
        
        $this->db->trans_complete();

        if ($this->db->trans_status() === false) {
            log_message('error', 'An error occurred while adding the driver (addDriver: Sales_model.php)');
            return false;
        }

        return $delivery_id;
    }

    public function addDelivery($data = [])
    {
        if ($this->db->insert('deliveries', $data)) {
            if ($this->site->getReference('do') == $data['do_reference_no']) {
                $this->site->updateReference('do');
            }
            return true;
        }
        return false;
    }

    public function update_sale_paid_amount($id, $amount){
        $q = $this->db->get_where('sales', ['id' => $id], 1);
        if ($q->num_rows() > 0) {
            $row = $q->row();
            $paid_amount = $row->paid;
            $new_amount = $paid_amount + $amount;
            
            $data = array(
                'paid' => $amount
            );
    
            $this->db->update('sales', $data, array('id' => $id));
        }
        return false;
    }

    public function getSaleInvoiceTotalPaid($customer_id, $sale_id)
    {
        $this->db->select('
            s.id AS sale_id,
            s.grand_total,
            IFNULL(SUM(p.amount), 0) AS total_paid
        ', false);

        $this->db->from('sma_sales s');
        $this->db->join('sma_payments p', 'p.sale_id = s.id', 'left');

        $this->db->where('s.id', $sale_id);
        $this->db->where('s.customer_id', $customer_id);

        $this->db->group_by('s.id');

        $q = $this->db->get();

        if ($q->num_rows() > 0) {
            return $q->row(); // grand_total + total_paid
        }

        return null;
    }

    public function getPendingInvoicesByCustomer($customer_id) {
        $this->db->select('s.id, s.date, s.reference_no, s.customer_id, s.customer, s.grand_total, s.paid, s.additional_discount, s.payment_status, s.due_date, 
                          COALESCE(SUM(r.grand_total), 0) as return_total', false);
        $this->db->from('sales s');
        $this->db->join('returns r', 'r.sale_id = s.id', 'left');
        $this->db->where('s.customer_id', $customer_id);
        $this->db->where('s.sale_invoice', 1);
        $this->db->where_in('s.payment_status', ['pending', 'due', 'partial']);
        $this->db->group_by('s.id');
        $this->db->order_by('s.date', 'asc');
        $q = $this->db->get();
    
        if ($q->num_rows() > 0) {
            return $q->result(); // Return the result directly as an array of objects
        } else {
            return []; // Return an empty array if no results
        }
    }

    public function getCustomerInvoicesWithPayments($customer_id) {
        $this->db->select('s.id, s.date, s.reference_no, s.customer_id, s.customer, s.grand_total, 
                          COALESCE(SUM(p.amount), 0) as total_paid,
                          s.payment_status, s.due_date', false);
        $this->db->from('sales s');
        $this->db->join('payments p', 'p.sale_id = s.id', 'left');
        $this->db->where('s.customer_id', $customer_id);
        $this->db->where('s.sale_invoice', 1);
        $this->db->group_by('s.id');
        $this->db->order_by('s.date', 'asc');
        $q = $this->db->get();
    
        if ($q->num_rows() > 0) {
            $invoices = $q->result();
            
            // Add calculated fields
            foreach ($invoices as $key => $invoice) {
                $invoice->outstanding_amount = $invoice->grand_total - $invoice->total_paid;
                $invoice->type = 'Sales Invoice';

                // Remove invoice if outstanding is zero or less
                if ($invoice->outstanding_amount == 0) {
                    unset($invoices[$key]);
                }
            }

            $invoices = array_values($invoices);
            //echo '<pre>';print_r($invoices);exit;
            return $invoices;
        } else {
            return [];
        }
    }

    public function getCustomerReturnsWithPayments($customer_id) {
        $this->db->select('r.id, r.date, r.reference_no, r.customer_id, r.customer, r.grand_total, 
                          COALESCE(SUM(r.paid), 0) as total_paid', false);
        $this->db->from('returns r');
        //$this->db->join('payments p', 'p.return_id = r.id', 'left');
        $this->db->where('r.customer_id', $customer_id);
        $this->db->group_by('r.id');
        $this->db->order_by('r.date', 'desc');
        $q = $this->db->get();
    
        if ($q->num_rows() > 0) {
            $returns = $q->result();
            
            // Add calculated fields
            foreach ($returns as $key => $return_item) {
                $return_item->outstanding_amount = $return_item->grand_total - $return_item->total_paid;
                $return_item->type = 'Return';

                // Remove invoice if outstanding is zero or less
                if ($return_item->outstanding_amount == 0) {
                    unset($returns[$key]);
                }
            }
            
            $returns = array_values($returns);
            return $returns;
        } else {
            return [];
        }
    }

    public function getCustomerCreditMemosWithUsage($customer_id) {
        $this->db->select('m.id, m.date, m.reference_no, m.customer_id, m.payment_amount as amount, m.used_amount', false);
        $this->db->from('memo m');
        $this->db->join('memo_entries me', 'me.memo_id = m.id', 'left');
        $this->db->where('m.customer_id', $customer_id);
        $this->db->group_by('m.id');
        $this->db->order_by('m.date', 'desc');
        $q = $this->db->get();
    
        if ($q->num_rows() > 0) {
            $creditmemos = $q->result();
            
            // Add calculated fields
            foreach ($creditmemos as $key => $creditmemo) {
                $creditmemo->available_balance = $creditmemo->amount - $creditmemo->used_amount;
                $creditmemo->type = 'Credit Memo';

                // Remove credit memo if available balance is zero or less
                if ($creditmemo->available_balance <= 0) {
                    unset($creditmemos[$key]);
                }   
                
            }
            
            $creditmemos = array_values($creditmemos);
            return $creditmemos;
        } else {
            return [];
        }
    }

    public function getCustomerAdvancesWithUsage($customer_id) {
        // Get customer advance ledger from settings
        $this->db->select('customer_advance_ledger');
        $this->db->from('settings');
        $this->db->where('setting_id', 1); // Assuming settings table has setting_id = 1 for main settings
        $settings_query = $this->db->get();
        
        if ($settings_query->num_rows() == 0) {
            return [];
        }
        
        $settings = $settings_query->row();
        $customer_advance_ledger = $settings->customer_advance_ledger;
        
        if (!$customer_advance_ledger) {
            return [];
        }
        
        // Calculate advance balance
        $this->db->select('
            COALESCE(SUM(CASE WHEN ei.dc = "C" THEN ei.amount ELSE 0 END), 0) as credit_total,
            COALESCE(SUM(CASE WHEN ei.dc = "D" THEN ei.amount ELSE 0 END), 0) as debit_total
        ');
        $this->db->from('sma_accounts_entryitems ei');
        $this->db->join('sma_accounts_entries e', 'e.id = ei.entry_id', 'inner');
        $this->db->where('ei.ledger_id', $customer_advance_ledger);
        $this->db->where('e.customer_id', $customer_id);
        
        // Check if deleted column exists before filtering
        if ($this->db->field_exists('deleted', 'sma_accounts_entries')) {
            $this->db->where('e.deleted', 0);
        }
        
        $balance_query = $this->db->get();
        
        if ($balance_query->num_rows() > 0) {
            $result = $balance_query->row();
            $available_balance = $result->credit_total - $result->debit_total;
            
            if ($available_balance > 0) {
                // Create a virtual advance record
                $advance = new stdClass();
                $advance->id = 'advance_' . $customer_id; // Virtual ID
                $advance->date = date('Y-m-d'); // Current date for display
                $advance->reference_no = 'ADVANCE';
                $advance->customer_id = $customer_id;
                $advance->amount = $available_balance;
                $advance->used_amount = 0; // Advances don't have a separate used amount field
                $advance->available_balance = $available_balance;
                $advance->type = 'Customer Advance';
                
                return [$advance];
            }
        }
        
        return [];
    }

    public function update_balance($id, $new_balance)
    {
        $data = array(
            'balance' => $new_balance
        );

        $this->db->update('companies', $data, array('id' => $id));

        return true;
    }

    public function update_return_paid($return_id, $return_amount){
        $this->db->update('sma_returns', ['paid' => $return_amount], ['id' => $return_id]);
    }

    public function update_credit_memo($creditmemo_id, $amount_used){
        $this->db->update('sma_memo', ['used_amount' => $amount_used], ['id' => $creditmemo_id]);
    }

    public function getCreditMemoByID($creditmemo_id){
        $this->db->select('memo.*, companies.company')
            ->join('companies', 'companies.id=memo.customer_id', 'left')
            ->where('memo.id =', $creditmemo_id);
        $q = $this->db->get('memo');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function update_payment_reference($payment_id, $journal_id){
        $this->db->update('sma_payment_reference', ['journal_id' => $journal_id], ['id' => $payment_id]);
    }

    public function get_sale_by_avzcode($avz_code)
    {
        $this->db->select('sale_items.*')
            ->join('sales', 'sales.id=sale_items.sale_id', 'left')
            ->where('sale_items.avz_item_code =', $avz_code)
            ->where('sales.pos =', 0);
        $q = $this->db->get('sale_items');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    /* ----------------- Gift Cards --------------------- */

    public function addGiftCard($data = [], $ca_data = [], $sa_data = [])
    {
        if ($this->db->insert('gift_cards', $data)) {
            if (!empty($ca_data)) {
                $this->db->update('companies', ['award_points' => $ca_data['points']], ['id' => $ca_data['customer']]);
            } elseif (!empty($sa_data)) {
                $this->db->update('users', ['award_points' => $sa_data['points']], ['id' => $sa_data['user']]);
            }
            return true;
        }
        return false;
    }

    public function addOptionQuantity($option_id, $quantity)
    {
        if ($option = $this->getProductOptionByID($option_id)) {
            $nq = $option->quantity + $quantity;
            if ($this->db->update('product_variants', ['quantity' => $nq], ['id' => $option_id])) {
                return true;
            }
        }
        return false;
    }

    public function addPaymentReference($data = [])
    {
        $this->db->insert('payment_reference', $data);
        return $this->db->insert_id();
    }
    public function get_unreported_sales($serial_numbers){
        $this->db->select('sma_serial_numbers.serial_number, sma_serial_numbers.batchno, sma_serial_numbers.gtin, sma_serial_numbers.sale_id as sale_id, sma_warehouses.gln');
        $this->db->from('sma_serial_numbers');
        $this->db->join('sma_sales', 'sma_serial_numbers.sale_id = sma_sales.id');
        $this->db->join('sma_warehouses', 'sma_sales.warehouse_id = sma_warehouses.id');
        $this->db->where_in('sma_serial_numbers.id',$serial_numbers);
        $query = $this->db->get();
        
        $results = [];
        foreach ($query->result() as $row) {
            $results[$row->gln][] = $row;
        }
        
        return $results;
    }
    public function get_rasd_credential($warehouse_id){
        $this->db->select("rasd_user, rasd_pass");
        $this->db->from("sma_warehouses");
        $this->db->where("sma_warehouses.id", $warehouse_id);
        $query = $this->db->get();
        $rasd_pharmacy_user = "";
        $rasd_pharmacy_password = "";
        if($query->num_rows() > 0){
            $rasd_pharmacy_user = $query ->row()->rasd_user;
            $rasd_pharmacy_password = $query ->row()->rasd_pass;
        }
        $res = [
            "user" => $rasd_pharmacy_user,
            "pass" => $rasd_pharmacy_password
        ];
        return $res;
    }
    public function mark_sales_as_reported($sale_ids){
       $this->db->where_in('sale_id', $sale_ids);
       $this->db->update('sma_serial_numbers', ['is_pushed' => 1]);
    }

    public function getPaymentReferenceByID($id){
        $this->db->select('payment_reference.*, companies.name, lb.name as transfer_from')
            ->join('companies', 'companies.id=payment_reference.customer_id', 'left')
            ->join('accounts_ledgers lb', 'lb.id=payment_reference.transfer_from_ledger', 'left')
            ->where('payment_reference.id =', $id);
        $q = $this->db->get('payment_reference');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function update_customer_balance($customer_id, $amount){
        $current_balance = $this->db->select('balance')
                                ->where('id', $customer_id)
                                ->get('sma_companies')
                                ->row('balance');

        $current_balance = $current_balance !== null ? $current_balance : 0;
        $new_balance = $current_balance + $amount;

        $this->db->update('sma_companies', ['balance' => $new_balance], ['id' => $customer_id]);
    }

    public function getPaymentByReferenceID($id)
    {
        $this->db->select('payments.*, companies.company, type, sales.grand_total, sales.paid, sales.additional_discount, sales.returns_total_deducted, sales.reference_no as ref_no, sales.date as sale_date')
            ->join('sales', 'sales.id=payments.sale_id', 'left')
            ->join('companies', 'companies.id=sales.customer_id', 'left')
            ->where('payments.payment_id =', $id);
        $q = $this->db->get('payments');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getPaymentReferences(){
        $this->db->select('payment_reference.*, companies.name as company')
                ->join('companies', 'companies.id=payment_reference.customer_id', 'left')
                ->where('customer_id <>', NULL);
        $q = $this->db->get('payment_reference');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function addPayment($data = [], $customer_id = null)
    {
        if ($this->db->insert('payments', $data)) {
            if ($this->site->getReference('pay') == $data['reference_no']) {
                $this->site->updateReference('pay');
            }
            //$this->site->syncSalePayments($data['sale_id']);
            if ($data['paid_by'] == 'gift_card') {
                $gc = $this->site->getGiftCardByNO($data['cc_no']);
                $this->db->update('gift_cards', ['balance' => ($gc->balance - $data['amount'])], ['card_no' => $data['cc_no']]);
            } elseif ($customer_id && $data['paid_by'] == 'deposit') {
                $customer = $this->site->getCompanyByID($customer_id);
                $this->db->update('companies', ['deposit_amount' => ($customer->deposit_amount - $data['amount'])], ['id' => $customer_id]);
            }
            return true;
        }
        return false;
    }

    public function addSaleNew($data = [], $items = [], $payment = [], $si_return = [], $attachments = [])
    {
        // Sequence-Code
        $this->load->library('SequenceCode');
        $this->sequenceCode = new SequenceCode();
        $data['sequence_code'] = $this->sequenceCode->generate('SL', 5);

        $this->db->trans_start();
        if ($this->db->insert('sales', $data)) {
            
            $sale_id = $this->db->insert_id();
            if ($this->site->getReference('so') == $data['reference_no']) {
                $this->site->updateReference('so');
            }

            foreach ($items as $item) {
                $item['sale_id'] = $sale_id;
                $real_cost = $item['real_cost'];
                //unset($item['real_cost']);
                $this->db->insert('sale_items', $item);

                $sale_item_id = $this->db->insert_id();

                if ($data['sale_status'] == 'ready'){ //handle inventory movement 
                    //$this->Inventory_model->add_movement($item['product_id'], $item['batch_no'], 'sale', $item['quantity'], $item['warehouse_id']); 
                    $this->Inventory_model->add_movement($item['product_id'], $item['batch_no'], 'sale', $item['quantity'], $item['warehouse_id'], $sale_id, $item['net_cost'], $item['expiry'], $item['net_unit_price'], $real_cost, $item['avz_item_code'], $item['bonus'], $data['customer_id'], $item['real_unit_price'], $data['date']);
                } 
                
            }

            if (!empty($attachments)) {
                foreach ($attachments as $attachment) {
                    $attachment['subject_id']   = $sale_id;
                    $attachment['subject_type'] = 'sale';
                    $this->db->insert('attachments', $attachment);
                }
            }

            //Check this for loyality later
            //$this->sma->update_award_points($data['grand_total'], $data['customer_id'], $data['created_by']);
        }
        $this->db->trans_complete();
        if ($this->db->trans_status() === false) {
            log_message('error', 'An errors has been occurred while adding the sale (Add:Sales_model.php)');
        } else {
            return $sale_id;
        }

        return false;
    }

    public function addSale($data = [], $items = [], $payment = [], $si_return = [], $attachments = [])
    {
        // Sequence-Code
        $this->load->library('SequenceCode');
        $this->sequenceCode = new SequenceCode();
        $data['sequence_code'] = $this->sequenceCode->generate('SL', 5);

        if (empty($si_return)) {
            $cost = $this->site->costing($items);
            // $this->sma->print_arrays($cost);
        }

        $this->db->trans_start();
        if ($this->db->insert('sales', $data)) {
            $sale_id = $this->db->insert_id();
            if ($this->site->getReference('so') == $data['reference_no']) {
                $this->site->updateReference('so');
            }
            foreach ($items as $item) {
                $item['sale_id'] = $sale_id;
                $real_cost = $item['real_cost'];
                //unset($item['real_cost']);
                $this->db->insert('sale_items', $item);

                // Code for serials here
                $serials_quantity = $item['quantity'];
                $serials_gtin = $item['product_code'];
                $serials_batch_no = $item['batch_no'];
                
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
                $this->db->limit(abs($serials_quantity));

                $notification_serials = $this->db->get();
                
                if ($notification_serials->num_rows() > 0) {
                    foreach (($notification_serials->result()) as $row) {
                        $this->db->update('sma_invoice_serials', ['sid' => $sale_id], ['serial_number' => $row->serial_number, 'batch_no' => $row->batch_no, 'gtin' => $row->gtin]);
                    }
                }
                // Code for serials end here

                $sale_item_id = $this->db->insert_id();

                if ($data['sale_status'] == 'completed'){ //handle inventory movement 
                    //$this->Inventory_model->add_movement($item['product_id'], $item['batch_no'], 'sale', $item['quantity'], $item['warehouse_id']); 
                    $this->Inventory_model->add_movement($item['product_id'], $item['batch_no'], 'sale', $item['quantity'], $item['warehouse_id'], $sale_id, $item['net_cost'], $item['expiry'], $item['net_unit_price'], $real_cost, $item['avz_item_code'], $item['bonus'], $data['customer_id'], $item['real_unit_price'], $data['date']);
                } 
                if ($data['sale_status'] == 'completed' && empty($si_return)) { 
                      
                    $item_costs = $this->site->item_costing($item);
                    foreach ($item_costs as $item_cost) {
                        if (isset($item_cost['date']) || isset($item_cost['pi_overselling'])) {
                            $item_cost['sale_item_id'] = $sale_item_id;
                            $item_cost['sale_id']      = $sale_id;
                            $item_cost['date']         = date('Y-m-d', strtotime($data['date']));
                            if (!isset($item_cost['pi_overselling'])) {
                                $this->db->insert('costing', $item_cost);
                            }
                        } else {
                            foreach ($item_cost as $ic) {
                                $ic['sale_item_id'] = $sale_item_id;
                                $ic['sale_id']      = $sale_id;
                                $ic['date']         = date('Y-m-d', strtotime($data['date']));
                                if (!isset($ic['pi_overselling'])) {
                                    $this->db->insert('costing', $ic);
                                }
                            }
                        }
                    }
                }
            }

            if (!empty($attachments)) {
                foreach ($attachments as $attachment) {
                    $attachment['subject_id']   = $sale_id;
                    $attachment['subject_type'] = 'sale';
                    $this->db->insert('attachments', $attachment);
                }
            }

            if ($data['sale_status'] == 'completed') {
                $this->site->syncPurchaseItems($cost);
            }

            if (!empty($si_return)) {
                foreach ($si_return as $return_item) {
                    $product = $this->site->getProductByID($return_item['product_id']);
                    if ($product->type == 'combo') {
                        $combo_items = $this->site->getProductComboItems($return_item['product_id'], $return_item['warehouse_id']);
                        foreach ($combo_items as $combo_item) {
                            $this->updateCostingAndPurchaseItem($return_item, $combo_item->id, ($return_item['quantity'] * $combo_item->qty));
                        }
                    } elseif ($product->type != 'service') {
                        $this->updateCostingAndPurchaseItem($return_item, $return_item['product_id'], $return_item['quantity']);
                    }
                }
                $this->db->update('sales', ['return_sale_ref' => $data['return_sale_ref'], 'surcharge' => $data['surcharge'], 'return_sale_total' => $data['grand_total'], 'return_id' => $sale_id], ['id' => $data['sale_id']]);
            }

            if ($data['payment_status'] == 'partial' || $data['payment_status'] == 'paid' && !empty($payment)) {
                if (empty($payment['reference_no'])) {
                    $payment['reference_no'] = $this->site->getReference('pay');
                }
                $payment['sale_id'] = $sale_id;
                if ($payment['paid_by'] == 'gift_card') {
                    $this->db->update('gift_cards', ['balance' => $payment['gc_balance']], ['card_no' => $payment['cc_no']]);
                    unset($payment['gc_balance']);
                    $this->db->insert('payments', $payment);
                } else {
                    if ($payment['paid_by'] == 'deposit') {
                        $customer = $this->site->getCompanyByID($data['customer_id']);
                        $this->db->update('companies', ['deposit_amount' => ($customer->deposit_amount - $payment['amount'])], ['id' => $customer->id]);
                    }
                    $this->db->insert('payments', $payment);
                }
                if ($this->site->getReference('pay') == $payment['reference_no']) {
                    $this->site->updateReference('pay');
                }
                $this->site->syncSalePayments($sale_id);
            }

            $this->site->syncQuantity($sale_id);
            $this->sma->update_award_points($data['grand_total'], $data['customer_id'], $data['created_by']);
        }
        $this->db->trans_complete();
        if ($this->db->trans_status() === false) {
            log_message('error', 'An errors has been occurred while adding the sale (Add:Sales_model.php)');
        } else {
            return $sale_id;
        }

        return false;
    }

    public function deleteDelivery($id)
    {
        $this->site->log('Delivery', ['model' => $this->getDeliveryByID($id)]);
        if ($this->db->delete('deliveries', ['id' => $id])) {
            return true;
        }
        return false;
    }

    public function deleteGiftCard($id)
    {
        $this->site->log('Gift card', ['model' => $this->site->getGiftCardByID($id)]);
        if ($this->db->delete('gift_cards', ['id' => $id])) {
            return true;
        }
        return false;
    }

    public function deletePayment($id)
    {
        $opay = $this->getPaymentByID($id);
        $this->site->log('Payment', ['model' => $opay]);
        if ($this->db->delete('payments', ['id' => $id])) {
            $this->site->syncSalePayments($opay->sale_id);
            if ($opay->paid_by == 'gift_card') {
                $gc = $this->site->getGiftCardByNO($opay->cc_no);
                $this->db->update('gift_cards', ['balance' => ($gc->balance + $opay->amount)], ['card_no' => $opay->cc_no]);
            } elseif ($opay->paid_by == 'deposit') {
                $sale     = $this->getInvoiceByID($opay->sale_id);
                $customer = $this->site->getCompanyByID($sale->customer_id);
                $this->db->update('companies', ['deposit_amount' => ($customer->deposit_amount + $opay->amount)], ['id' => $customer->id]);
            }
            return true;
        }
        return false;
    }

    public function delete_onhold_qty($id){
        if ($this->db->delete('sma_product_qty_onhold_request', ['id' => $id])) {
            return true;
        }else{
            return false;
        }
    }

    public function deleteSale($id)
    {
        $this->db->trans_start();
        $sale       = $this->getInvoiceByID($id);
        $sale_items = $this->resetSaleActions($id);
        $this->site->log('Sale', ['model' => $this->getInvoiceByID($id), 'items' => $sale_items]);
        if ($this->db->delete('sale_items', ['sale_id' => $id]) && $this->db->delete('sales', ['id' => $id]) && $this->db->delete('costing', ['sale_id' => $id]) && $sale->sale_status != 'completed') {
            $this->syncSaleCustomerBalance($id, $sale->customer_id);
            $this->db->delete('sales', ['sale_id' => $id]);
            $this->db->delete('payments', ['sale_id' => $id]);
            $this->site->syncQuantity(null, null, $sale_items);

            $this->db->update('sma_invoice_serials', ['sid' => 0], ['sid' => $id]);
        }else{
            return false;
        }
        $this->db->delete('attachments', ['subject_id' => $id, 'subject_type' => 'sale']);
        $this->db->trans_complete();
        if ($this->db->trans_status() === false) {
            log_message('error', 'An errors has been occurred while adding the sale (Delete:Sales_model.php)');
        } else {
            return true;
        }
        return false;
    }

    public function getAllGCTopups($card_id)
    {
        $this->db->select("{$this->db->dbprefix('gift_card_topups')}.*, {$this->db->dbprefix('users')}.first_name, {$this->db->dbprefix('users')}.last_name, {$this->db->dbprefix('users')}.email")
        ->join('users', 'users.id=gift_card_topups.created_by', 'left')
        ->order_by('id', 'desc')->limit(10);
        $q = $this->db->get_where('gift_card_topups', ['card_id' => $card_id]);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getAllPickerItems($sale_id)
    {
        // 1. Fetch sale items
        $this->db->select("
            sale_items.*,
            products.image,
            products.details,
            products.unit as base_unit_id,
            units.code as base_unit_code,
            tax_rates.name AS tax_name,
            tax_rates.rate AS tax_rate
        ")
        ->join('products', 'products.id = sale_items.product_id', 'left')
        ->join('tax_rates', 'tax_rates.id = sale_items.tax_rate_id', 'left')
        ->join('units', 'units.id = products.unit', 'left')
        ->where('sale_id', $sale_id);

        $saleItems = $this->db->get('sale_items')->result();
        if (!$saleItems) return false;

        $final = [];

        foreach ($saleItems as $item) {

            $neededQty = $item->quantity;
            $allocated  = [];

            // 2. Fetch ALL batches for product (with shelf)
            $batches = $this->db->select("
                im.id AS movement_id,
                im.avz_item_code,
                im.product_id,
                im.batch_number,
                im.expiry_date,
                im.quantity AS movement_qty,
                sps.box_number,
                sps.zone_number,
                sps.rack_number,
                im.shelf_id
            ")
            ->from("inventory_movements im")
            ->join("sma_purchase_order_shelving sps", "sps.id = im.shelf_id", "left")
            ->where("im.product_id", $item->product_id)
            ->where("im.quantity >", 0)  // only positive stock movements
            ->order_by("im.expiry_date", "ASC") // nearest expiry
            ->get()->result();
            
            // 3. Handle no batches
            if (!$batches) {
                $item->picker_batches = [];
                $final[] = $item;
                continue;
            }

            // 4. For each batch, calculate net available stock (= movement qty - sum sold)
            foreach ($batches as $b) {

                // Calculate sold qty for the same avz_item_code
                $sold = $this->db->select_sum('quantity')
                    ->from('sale_items')
                    ->where('product_id', $b->product_id)
                    ->get()->row()->quantity;

                $sold = $sold ? $sold : 0;
                //echo '<pre>';   print_r($sold);  exit;

                $available = $b->movement_qty - $sold;

                if ($available <= 0) continue;

                // 5. Allocate from this batch
                if ($neededQty > 0) {

                    $pick = min($neededQty, $available);

                    $allocated[] = (object) [
                        'avz_item_code' => $b->avz_item_code,
                        'batch_number'      => $b->batch_number,
                        'expiry_date'        => $b->expiry_date,
                        'pick_qty'      => $pick,
                        'box_number'    => $b->box_number,
                        'zone_number'   => $b->zone_number,
                        'rack_number'   => $b->rack_number,
                        'shelf_id'      => $b->shelf_id
                    ];

                    $neededQty -= $pick;
                }
            }

            // 6. Assign the allocated structure to item
            $item->picker_batches = $allocated;
            $final[] = $item;
        }
        // /echo '<pre>';   print_r($final);  exit;
        return $final;
    }

    public function getAllPickerInvoiceItems($sale_id, $return_id = null, $sale = null)
    {
        if($sale == null){
            $this->db->select('
            sale_items.*, 
            tax_rates.code as tax_code, tax_rates.name as tax_name, tax_rates.rate as tax_rate, item_code,
            products.image, products.details as details, product_variants.name as variant, products.hsn_code as hsn_code, products.second_name as second_name, products.unit as base_unit_id, 
            products.warehouse_shelf,
            units.code as base_unit_code')
            ->join('products', 'products.id=sale_items.product_id', 'left')
            ->join('product_variants', 'product_variants.id=sale_items.option_id', 'left')
            ->join('tax_rates', 'tax_rates.id=sale_items.tax_rate_id', 'left')
            ->join('units', 'units.id=products.unit', 'left')
            ->group_by('sale_items.id')
            ->order_by('products.warehouse_shelf', 'asc');
        }else{
            $this->db->select('
            sale_items.*, 
            tax_rates.code as tax_code, tax_rates.name as tax_name, tax_rates.rate as tax_rate, item_code,
            products.image, products.details as details, product_variants.name as variant, products.hsn_code as hsn_code, products.second_name as second_name, products.unit as base_unit_id, 
            products.warehouse_shelf,
            units.code as base_unit_code,
            SUM(IFNULL(CASE WHEN sma_inventory_movements.location_id = ' . $sale->warehouse_id . ' THEN sma_inventory_movements.quantity ELSE 0 END, 0)) as total_quantity')
            ->join('products', 'products.id=sale_items.product_id', 'left')
            ->join('inventory_movements', 'inventory_movements.avz_item_code=sale_items.avz_item_code', 'left')
            ->join('product_variants', 'product_variants.id=sale_items.option_id', 'left')
            ->join('tax_rates', 'tax_rates.id=sale_items.tax_rate_id', 'left')
            ->join('units', 'units.id=products.unit', 'left')
            ->group_by('sale_items.id')
            ->order_by('products.warehouse_shelf', 'asc');
        }
        
        if ($sale_id && !$return_id) {
            $this->db->where('sale_id', $sale_id);
        } elseif ($return_id) {
            $this->db->where('sale_id', $return_id);
        }
        $q = $this->db->get('sale_items');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getAllInvoiceItems($sale_id, $return_id = null, $sale = null)
    {
        if($sale == null){
            $this->db->select('
            sale_items.*, 
            tax_rates.code as tax_code, tax_rates.name as tax_name, tax_rates.rate as tax_rate, item_code,
            products.image, products.details as details, product_variants.name as variant, products.hsn_code as hsn_code, products.second_name as second_name, products.unit as base_unit_id, 
            products.warehouse_shelf,
            units.code as base_unit_code')
            ->join('products', 'products.id=sale_items.product_id', 'left')
            ->join('product_variants', 'product_variants.id=sale_items.option_id', 'left')
            ->join('tax_rates', 'tax_rates.id=sale_items.tax_rate_id', 'left')
            ->join('units', 'units.id=products.unit', 'left')
            ->group_by('sale_items.id')
            ->order_by('sale_items.id', 'desc');
        }else{
            $this->db->select('
            sale_items.*, 
            tax_rates.code as tax_code, tax_rates.name as tax_name, tax_rates.rate as tax_rate, item_code,
            products.image, products.details as details, product_variants.name as variant, products.hsn_code as hsn_code, products.second_name as second_name, products.unit as base_unit_id, 
            products.warehouse_shelf,
            units.code as base_unit_code,
            SUM(IFNULL(CASE WHEN sma_inventory_movements.location_id = ' . $sale->warehouse_id . ' THEN sma_inventory_movements.quantity ELSE 0 END, 0)) as total_quantity')
            ->join('products', 'products.id=sale_items.product_id', 'left')
            ->join('inventory_movements', 'inventory_movements.avz_item_code=sale_items.avz_item_code', 'left')
            ->join('product_variants', 'product_variants.id=sale_items.option_id', 'left')
            ->join('tax_rates', 'tax_rates.id=sale_items.tax_rate_id', 'left')
            ->join('units', 'units.id=products.unit', 'left')
            ->group_by('sale_items.id')
            ->order_by('sale_items.id', 'desc');
        }
        
        if ($sale_id && !$return_id) {
            $this->db->where('sale_id', $sale_id);
        } elseif ($return_id) {
            $this->db->where('sale_id', $return_id);
        }
        $q = $this->db->get('sale_items');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getAllInvoiceItemsWithDetails($sale_id)
    {
        $this->db->select('sale_items.*, products.details, product_variants.name as variant');
        $this->db->join('products', 'products.id=sale_items.product_id', 'left')
        ->join('product_variants', 'product_variants.id=sale_items.option_id', 'left')
        ->group_by('sale_items.id');
        $this->db->order_by('id', 'asc');
        $q = $this->db->get_where('sale_items', ['sale_id' => $sale_id]);
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

    public function getCostingLines($sale_item_id, $product_id, $sale_id = null)
    {
        if ($sale_id) {
            $this->db->where('sale_id', $sale_id);
        }
        $orderby = ($this->Settings->accounting_method == 1) ? 'asc' : 'desc';
        $this->db->order_by('id', $orderby);
        $q = $this->db->get_where('costing', ['sale_item_id' => $sale_item_id, 'product_id' => $product_id]);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getDeliveryByID($id)
    {
        $q = $this->db->get_where('deliveries', ['id' => $id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getDeliveryBySaleID($sale_id)
    {
        $q = $this->db->get_where('deliveries', ['sale_id' => $sale_id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getInvoiceByID($id)
    {
        $q = $this->db->get_where('sales', ['id' => $id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getInvoicePayments($sale_id)
    {
        $this->db->order_by('id', 'asc');
        $q = $this->db->get_where('payments', ['sale_id' => $sale_id]);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
    }

    public function getItemByID($id)
    {
        $q = $this->db->get_where('sale_items', ['id' => $id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }

        return false;
    }

    public function getItemRack($product_id, $warehouse_id)
    {
        $q = $this->db->get_where('warehouses_products', ['product_id' => $product_id, 'warehouse_id' => $warehouse_id], 1);
        if ($q->num_rows() > 0) {
            $wh = $q->row();
            return $wh->rack;
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
            ->join('sales', 'sales.id=payments.sale_id', 'left')
            ->join('companies', 'companies.id=sales.customer_id', 'left')
            ->join('users', 'users.id=payments.created_by', 'left')
            ->where('type', 'sent')
            ->where('payments.sale_id >', 0);
        $q = $this->db->get('payments');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getPaymentsForSale($sale_id)
    {
        $this->db->select('payments.date, payments.paid_by, payments.amount, payments.cc_no, payments.cheque_no, payments.reference_no, users.first_name, users.last_name, type')
            ->join('users', 'users.id=payments.created_by', 'left');
        $q = $this->db->get_where('payments', ['sale_id' => $sale_id]);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getPaypalSettings()
    {
        $q = $this->db->get_where('paypal', ['id' => 1]);
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

    public function getProductComboItems($pid, $warehouse_id = null)
    {
        $this->db->select('products.id as id, combo_items.item_code as code, combo_items.quantity as qty, products.name as name,products.type as type, warehouses_products.quantity as quantity')
            ->join('products', 'products.code=combo_items.item_code', 'left')
            ->join('warehouses_products', 'warehouses_products.product_id=products.id', 'left')
            ->group_by('combo_items.id');
        if ($warehouse_id) {
            $this->db->where('warehouses_products.warehouse_id', $warehouse_id);
        }
        $q = $this->db->get_where('combo_items', ['combo_items.product_id' => $pid]);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }

            return $data;
        }
        return false;
    }

    public function getProductNamesWithBatches($term, $warehouse_id, $pos = false, $limit = 5)
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
                $this->db->where("(({$this->db->dbprefix('inventory_movements')}.location_id  = '" . $warehouse_id . "') OR {$this->db->dbprefix('products')}.type != 'standard') AND "
                    . "({$this->db->dbprefix('products')}.name LIKE '%" . $term . "%' OR {$this->db->dbprefix('products')}.code LIKE '%" . $term . "%' OR  concat({$this->db->dbprefix('products')}.name, ' (', {$this->db->dbprefix('products')}.code, ')') LIKE '%" . $term . "%')");
            }
        $this->db->having("SUM(sma_inventory_movements.quantity)>0"); 
        $this->db->limit($limit);
        if ($pos) {
            $this->db->where('hide_pos !=', 1);
        }
        $q = $this->db->get('products');
        // echo  $this->db->last_query(); exit; 
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $row->serial_number=''; 
                $data[] = $row;
            }
            return $data;
        }  
    }

    public function getProductNamesWithBatches__BK($term, $warehouse_id, $pos = false, $limit = 5)
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

    // public function getProductNames($term, $warehouse_id, $pos = false, $limit = 5)
    // {
    //     $wp = "( SELECT product_id, warehouse_id, quantity as quantity from {$this->db->dbprefix('warehouses_products')} ) FWP";

    //     $this->db->select('products.*, FWP.quantity as quantity, categories.id as category_id, categories.name as category_name', false)
    //         ->join($wp, 'FWP.product_id=products.id', 'left')
    //         // ->join('warehouses_products FWP', 'FWP.product_id=products.id', 'left')
    //         ->join('categories', 'categories.id=products.category_id', 'left')
    //         ->group_by('products.id');
    //     if ($this->Settings->overselling) {
    //         $this->db->where("({$this->db->dbprefix('products')}.name LIKE '%" . $term . "%' OR {$this->db->dbprefix('products')}.code LIKE '%" . $term . "%' OR  concat({$this->db->dbprefix('products')}.name, ' (', {$this->db->dbprefix('products')}.code, ')') LIKE '%" . $term . "%')");
    //     } else {
    //         $this->db->where("((({$this->db->dbprefix('products')}.track_quantity = 0 OR FWP.quantity > 0) AND FWP.warehouse_id = '" . $warehouse_id . "') OR {$this->db->dbprefix('products')}.type != 'standard') AND "
    //             . "({$this->db->dbprefix('products')}.name LIKE '%" . $term . "%' OR {$this->db->dbprefix('products')}.code LIKE '%" . $term . "%' OR  concat({$this->db->dbprefix('products')}.name, ' (', {$this->db->dbprefix('products')}.code, ')') LIKE '%" . $term . "%')");
    //     }
    //     // $this->db->order_by('products.name ASC');
    //     if ($pos) {
    //         $this->db->where('hide_pos !=', 1);
    //     }
    //     $this->db->limit($limit);
    //     $q = $this->db->get('products');
    //     if ($q->num_rows() > 0) {
    //         foreach (($q->result()) as $row) {
    //             $data[] = $row;
    //         }
    //         return $data;
    //     }
    // }

    public function getProductNames($term, $warehouse_id, $pos = false, $limit = 5)
    {
       //  $wp = "( SELECT product_id, warehouse_id, quantity as quantity from {$this->db->dbprefix('warehouses_products')} ) FWP";

        $this->db->select('products.*, SUM(FWP.quantity) as quantity, FWP.expiry_date as expiry,  categories.id as category_id, categories.name as category_name', false)
        ->join("inventory_movements FWP", "FWP.product_id=products.id", "left")
        //  ->join($wp, 'FWP.product_id=products.id', 'left')
            // ->join('warehouses_products FWP', 'FWP.product_id=products.id', 'left')
            ->join('categories', 'categories.id=products.category_id', 'left')
            ->group_by('products.id');
        if ($this->Settings->overselling) {
            $this->db->where("({$this->db->dbprefix('products')}.name LIKE '%" . $term . "%' OR {$this->db->dbprefix('products')}.item_code LIKE '%" . $term . "%' OR {$this->db->dbprefix('products')}.code LIKE '%" . $term . "%' OR  concat({$this->db->dbprefix('products')}.name, ' (', {$this->db->dbprefix('products')}.code, ')') LIKE '%" . $term . "%')");
        } else { 
            $this->db->where("(( FWP.location_id = '" . $warehouse_id . "') OR {$this->db->dbprefix('products')}.type != 'standard') AND "
                . "({$this->db->dbprefix('products')}.name LIKE '%" . $term . "%' OR {$this->db->dbprefix('products')}.code LIKE '%" . $term . "%' OR  concat({$this->db->dbprefix('products')}.name, ' (', {$this->db->dbprefix('products')}.code, ')') LIKE '%" . $term . "%')");
        }
        // $this->db->order_by('products.name ASC');
        if ($pos) {
            $this->db->where('hide_pos !=', 1);
        }
        $this->db->limit($limit);
        $q = $this->db->get('products');
        // $this->output->enable_profiler(TRUE); 
        //   echo $this->db->last_query();  exit;
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
            //->join('warehouses', 'warehouses.id=product_variants.warehouse_id', 'left')
            ->where('product_variants.product_id', $product_id)
            ->group_by('product_variants.id');

        if (!$this->Settings->overselling && !$all) {
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

    public function getProductQuantity($product_id, $warehouse)
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

    public function getProductVariants($product_id)
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

    public function getProductWarehouseOptionQty($option_id, $warehouse_id)
    {
        $q = $this->db->get_where('warehouses_products_variants', ['option_id' => $option_id, 'warehouse_id' => $warehouse_id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getPurchaseItemByID($id)
    {
        $q = $this->db->get_where('purchase_items', ['id' => $id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getPurchaseItems($purchase_id)
    {
        return $this->db->get_where('purchase_items', ['purchase_id' => $purchase_id])->result();
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
        $q = $this->db->get_where('sales', ['id' => $id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getReturnBySID($sale_id)
    {
        $q = $this->db->get_where('sales', ['sale_id' => $sale_id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getSaleCosting($sale_id)
    {
        $q = $this->db->get_where('costing', ['sale_id' => $sale_id]);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getSaleItemByID($id)
    {
        $q = $this->db->get_where('sale_items', ['id' => $id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getSkrillSettings()
    {
        $q = $this->db->get_where('skrill', ['id' => 1]);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getStaff()
    {
        if (!$this->Owner) {
            $this->db->where('group_id !=', 1);
        }
        $this->db->where('group_id !=', 3)->where('group_id !=', 4);
        $q = $this->db->get('users');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
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
        return $this->db->get_where('purchase_items', ['transfer_id' => $transfer_id])->result();
    }

    public function getWarehouseProduct($pid, $wid)
    {
        $this->db->select($this->db->dbprefix('products') . '.*, ' . $this->db->dbprefix('warehouses_products') . '.quantity as quantity')
            ->join('warehouses_products', 'warehouses_products.product_id=products.id', 'left');
        $q = $this->db->get_where('products', ['warehouses_products.product_id' => $pid, 'warehouses_products.warehouse_id' => $wid]);
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

    public function resetSaleActions($id, $return_id = null, $check_return = null)
    {
        if ($sale = $this->getInvoiceByID($id)) {
            if ($check_return && $sale->sale_status == 'returned') {
                $this->session->set_flashdata('warning', lang('sale_x_action'));
                redirect($_SERVER['HTTP_REFERER'] ?? 'welcome');
            }
            if ($sale->sale_status == 'completed') {
                if ($costings = $this->getSaleCosting($id)) {
                    foreach ($costings as $costing) {
                        $pi = null;
                        if ($costing->purchase_id) {
                            $purchase_items = $this->getPurchaseItems($costing->purchase_id);
                            foreach ($purchase_items as $row) {
                                if ($row->product_id == $costing->product_id && $row->option_id == $costing->option_id) {
                                    $pi = $row;
                                }
                            }
                        } elseif ($costing->transfer_id) {
                            $purchase_items = $this->getTransferItems($costing->transfer_id);
                            foreach ($purchase_items as $row) {
                                if ($row->product_id == $costing->product_id && $row->option_id == $costing->option_id) {
                                    $pi = $row;
                                }
                            }
                        }
                        if ($pi) {
                            $this->site->setPurchaseItem(['id' => $pi->id, 'product_id' => $pi->product_id, 'option_id' => $pi->option_id], $costing->quantity);
                        } else {
                            $pi = $this->site->getPurchasedItem(['product_id' => $costing->product_id, 'option_id' => $costing->option_id ? $costing->option_id : null, 'purchase_id' => null, 'transfer_id' => null, 'warehouse_id' => $sale->warehouse_id]);
                            $this->site->setPurchaseItem(['id' => $pi->id, 'product_id' => $pi->product_id, 'option_id' => $pi->option_id], $costing->quantity);
                        }
                    }
                    $this->db->delete('costing', ['id' => $costing->id]);
                }
                $items = $this->getAllInvoiceItems($id);
                $this->site->syncQuantity(null, null, $items);
                $this->sma->update_award_points($sale->grand_total, $sale->customer_id, $sale->created_by, true);
                return $items;
            }
        }
    }

    public function syncQuantity($sale_id)
    {
        if ($sale_items = $this->getAllInvoiceItems($sale_id)) {
            foreach ($sale_items as $item) {
                $this->site->syncProductQty($item->product_id, $item->warehouse_id);
                if (isset($item->option_id) && !empty($item->option_id)) {
                    $this->site->syncVariantQty($item->option_id, $item->warehouse_id);
                }
            }
        }
    }

    public function syncSaleCustomerBalance($sale_id, $customer_id)
    {
        $payments = $this->site->getSalePayments($sale_id);
        $customer = $this->site->getCompanyByID($customer_id);
        foreach ($payments as $payment) {
            if ($payment->paid_by == 'deposit') {
                $this->db->update('companies', ['deposit_amount' => ($customer->deposit_amount + $payment->amount)], ['id' => $customer->id]);
            }
        }
    }

    public function topupGiftCard($data = [], $card_data = null)
    {
        if ($this->db->insert('gift_card_topups', $data)) {
            $this->db->update('gift_cards', $card_data, ['id' => $data['card_id']]);
            return true;
        }
        return false;
    }

    public function updateCostingAndPurchaseItem($return_item, $product_id, $quantity)
    {
        $bln_quantity = $quantity;
        if ($costings = $this->getCostingLines($return_item['id'], $product_id)) {
            foreach ($costings as $costing) {
                if ($costing->quantity > $bln_quantity && $bln_quantity != 0) {
                    $qty = $costing->quantity                                                                                     - $bln_quantity;
                    $bln = $costing->quantity_balance && $costing->quantity_balance >= $bln_quantity ? $costing->quantity_balance - $bln_quantity : 0;
                    $this->db->update('costing', ['quantity' => $qty, 'quantity_balance' => $bln], ['id' => $costing->id]);
                    $bln_quantity = 0;
                    break;
                } elseif ($costing->quantity <= $bln_quantity && $bln_quantity != 0) {
                    $this->db->delete('costing', ['id' => $costing->id]);
                    $bln_quantity = ($bln_quantity - $costing->quantity);
                }
            }
        }
        $clause = ['product_id' => $product_id, 'warehouse_id' => $return_item['warehouse_id'], 'purchase_id' => null, 'transfer_id' => null, 'option_id' => $return_item['option_id']];
        $this->site->setPurchaseItem($clause, $quantity);
        $this->site->syncQuantity(null, null, null, $product_id);
    }

    public function updateDelivery($id, $data = [])
    {
        if ($this->db->update('deliveries', $data, ['id' => $id])) {
            return true;
        }
        return false;
    }

    public function updateGiftCard($id, $data = [])
    {
        $this->db->where('id', $id);
        if ($this->db->update('gift_cards', $data)) {
            return true;
        }
        return false;
    }

    public function updateOptionQuantity($option_id, $quantity)
    {
        if ($option = $this->getProductOptionByID($option_id)) {
            $nq = $option->quantity - $quantity;
            if ($this->db->update('product_variants', ['quantity' => $nq], ['id' => $option_id])) {
                return true;
            }
        }
        return false;
    }

    public function updatePayment($id, $data = [], $customer_id = null)
    {
        $opay = $this->getPaymentByID($id);
        if ($this->db->update('payments', $data, ['id' => $id])) {
            $this->site->syncSalePayments($data['sale_id']);
            if ($opay->paid_by == 'gift_card') {
                $gc = $this->site->getGiftCardByNO($opay->cc_no);
                $this->db->update('gift_cards', ['balance' => ($gc->balance + $opay->amount)], ['card_no' => $opay->cc_no]);
            } elseif ($opay->paid_by == 'deposit') {
                if (!$customer_id) {
                    $sale        = $this->getInvoiceByID($opay->sale_id);
                    $customer_id = $sale->customer_id;
                }
                $customer = $this->site->getCompanyByID($customer_id);
                $this->db->update('companies', ['deposit_amount' => ($customer->deposit_amount + $opay->amount)], ['id' => $customer->id]);
            }
            if ($data['paid_by'] == 'gift_card') {
                $gc = $this->site->getGiftCardByNO($data['cc_no']);
                $this->db->update('gift_cards', ['balance' => ($gc->balance - $data['amount'])], ['card_no' => $data['cc_no']]);
            } elseif ($customer_id && $data['paid_by'] == 'deposit') {
                $customer = $this->site->getCompanyByID($customer_id);
                $this->db->update('companies', ['deposit_amount' => ($customer->deposit_amount - $data['amount'])], ['id' => $customer_id]);
            }
            return true;
        }
        return false;
    }

    public function updateProductOptionQuantity($option_id, $warehouse_id, $quantity, $product_id)
    {
        if ($option = $this->getProductWarehouseOptionQty($option_id, $warehouse_id)) {
            $nq = $option->quantity - $quantity;
            if ($this->db->update('warehouses_products_variants', ['quantity' => $nq], ['option_id' => $option_id, 'warehouse_id' => $warehouse_id])) {
                $this->site->syncVariantQty($option_id, $warehouse_id);
                return true;
            }
        } else {
            $nq = 0 - $quantity;
            if ($this->db->insert('warehouses_products_variants', ['option_id' => $option_id, 'product_id' => $product_id, 'warehouse_id' => $warehouse_id, 'quantity' => $nq])) {
                $this->site->syncVariantQty($option_id, $warehouse_id);
                return true;
            }
        }
        return false;
    }

    public function updateSaleWithCourier($id, $courier_id, $tracking_id, $pickup_location=null){
        $this->db->update('sales', ['courier_id' => $courier_id, 'courier_order_tracking_id' => $tracking_id, 'pickup_location_id' => $pickup_location, 'courier_assignment_time' => date('Y-m-d H:i:s')], ['id' => $id]);
    }

    public function updateSaleCourierStatus($courier_id, $tracking_id, $tracking_status){
       if( $this->db->update('sales', ['courier_order_status' => $tracking_status], 
        ['courier_id' => $courier_id, 'courier_order_tracking_id' =>$tracking_id ]) ){
            return true;
        }else{
            return false;
        }

    }

    public function updateSaleStatus($id, $status){
        $data = array();
        $data['sale_status'] = $status;

        $this->db->update('sales', $data, ['id' => $id]);
    }

    public function updateSale($id, $data, $items = [], $attachments = [])
    {
        // echo 'Items: <pre>'; print_r( $items);   echo 'Data: <pre>'; print_r( $data); exit;  
        $this->db->trans_start();
        $this->resetSaleActions($id, false, true);
        if ($data['sale_status'] == 'completed') {
            $this->Settings->overselling = true;
            $cost                        = $this->site->costing($items, true);
        }
         // $this->sma->print_arrays($cost); exit; 

        if ($this->db->update('sales', $data, ['id' => $id]) && $this->db->delete('sale_items', ['sale_id' => $id]) && $this->db->delete('costing', ['sale_id' => $id])) {
            $this->db->update('sma_invoice_serials', ['sid' => 0], ['sid' => $id]);
            foreach ($items as $item) {
                $item['sale_id'] = $id;
                $real_cost = $item['real_cost'];
                //unset($item['real_cost']);
                $this->db->insert('sale_items', $item);
                $sale_item_id = $this->db->insert_id();

                // Code for serials here
                $serials_quantity = $item['quantity'];
                $serials_gtin = $item['product_code'];
                $serials_batch_no = $item['batch_no'];
                
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
                        $this->db->update('sma_invoice_serials', ['sid' => $id], ['serial_number' => $row->serial_number, 'batch_no' => $row->batch_no, 'gtin' => $row->gtin]);
                    }
                }
                
                // Code for serials end here
                if ($data['sale_status'] == 'completed' && $this->site->getProductByID($item['product_id'])) {
                       //handle inventory movement
                    $this->Inventory_model->add_movement($item['product_id'], $item['batch_no'], 'sale', $item['quantity'], $item['warehouse_id'], $id,  $item['net_cost'], $item['expiry'], $item['net_unit_price'], $real_cost, $item['avz_item_code'], $item['bonus'], $data['customer_id'], $item['real_unit_price'], $data['date']); 
                    $item_costs = $this->site->item_costing($item);
                    /*foreach ($item_costs as $item_cost) {
                        if (isset($item_cost['date']) || isset($item_cost['pi_overselling'])) {
                            $item_cost['sale_item_id'] = $sale_item_id;
                            $item_cost['sale_id']      = $id;
                            $item_cost['date']         = date('Y-m-d', strtotime($data['date']));
                            if (!isset($item_cost['pi_overselling'])) {
                                $this->db->insert('costing', $item_cost);
                            }
                        } else {
                            foreach ($item_cost as $ic) {
                                $ic['sale_item_id'] = $sale_item_id;
                                $ic['sale_id']      = $id;
                                $item_cost['date']  = date('Y-m-d', strtotime($data['date']));
                                if (!isset($ic['pi_overselling'])) {
                                    $this->db->insert('costing', $ic);
                                }
                            }
                        }
                    }*/
                }
            }

            if (!empty($attachments)) {
                foreach ($attachments as $attachment) {
                    $attachment['subject_id']   = $id;
                    $attachment['subject_type'] = 'sale';
                    $this->db->insert('attachments', $attachment);
                }
            }

            if ($data['sale_status'] == 'completed') {
               
                //$this->site->syncPurchaseItems($cost);
            }

            $this->site->syncSalePayments($id);
            $this->site->syncQuantity($id);
            $sale = $this->getInvoiceByID($id);
            $this->sma->update_award_points($data['grand_total'], $data['customer_id'], $sale->created_by);
        }
        $this->db->trans_complete();
        if ($this->db->trans_status() === false) {
            log_message('error', 'An errors has been occurred while adding the sale (Update:Sales_model.php)');
        } else {
            return true;
        }
        return false;
    }

//    sale invoice
    public function saleToInvoice($id)
    {
        $this->db->update('sales', ['sale_invoice' => 1], ['id' => $id]);
        return true;
    }
    public function getSaleByID($id)
    {
        $q = $this->db->get_where('sales', ['id' => $id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getAllReturnInvoiceItems($sale_id, $customer_id)
    {
        $this->db->select('
            sale_items.*, 
            tax_rates.code as tax_code, 
            tax_rates.name as tax_name, 
            tax_rates.rate as tax_rate, 
            products.unit, 
            products.details as details, 
            products.hsn_code as hsn_code, 
            products.second_name as second_name, 
            SUM(IFNULL(CASE WHEN sma_inventory_movements.customer_id = ' . $customer_id . ' THEN sma_inventory_movements.quantity ELSE 0 END, 0)) as total_quantity, 
            SUM(IFNULL(CASE WHEN sma_inventory_movements.customer_id = ' . $customer_id . ' THEN sma_inventory_movements.bonus ELSE 0 END, 0)) as total_bonus
        ')
        ->join('products', 'products.id=sale_items.product_id', 'left')
        ->join('inventory_movements', 'inventory_movements.avz_item_code=sale_items.avz_item_code', 'left')
        ->join('tax_rates', 'tax_rates.id=sale_items.tax_rate_id', 'left')
        ->group_by('sale_items.id, sale_items.avz_item_code')
        ->having('total_quantity <>', 0)
        ->order_by('sale_items.id', 'asc');  // Make sure to order by the correct field here

        // Fetch the purchase items for the given purchase ID
        $q = $this->db->get_where('sale_items', ['sale_items.sale_id' => $sale_id]);
        //echo $this->db->last_query();exit;
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
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

    public function getRealAvgCost($item_batchno, $item_id){
        $avgCostQuery = "SELECT 
                    SUM(iv.quantity * iv.real_unit_cost) / SUM(iv.quantity) AS real_average_cost
                 FROM 
                    sma_inventory_movements iv
                 WHERE 
                    iv.product_id = '{$item_id}' 
                    AND iv.batch_number = '{$item_batchno}' 
                    AND iv.type IN ('purchase', 'adjustment_increase')";
        $avgCost = $this->db->query($avgCostQuery);
        $avgObj = $avgCost->row();
        if($avgObj){
            $average_cost = $avgObj->real_average_cost;
        }else{
            $average_cost = 0;
        }
        
        return $average_cost;
    }

    public function getAvgCost($item_batchno, $item_id){
        $avgCostQuery = "SELECT 
                    SUM(iv.quantity * iv.net_unit_cost) / SUM(iv.quantity) AS average_cost
                 FROM 
                    sma_inventory_movements iv
                 WHERE 
                    iv.product_id = '{$item_id}' 
                    AND iv.batch_number = '{$item_batchno}' 
                    AND iv.type IN ('purchase', 'adjustment_increase')";
        $avgCost = $this->db->query($avgCostQuery);
        $avgObj = $avgCost->row();
        if($avgObj){
            $average_cost = $avgObj->average_cost;
        }else{
            $average_cost = 0;
        }
        
        return $average_cost;
    }


    public function getAllSaleItems($sale_id)
    {
        $this->db->select('sale_items.*, tax_rates.code as tax_code, tax_rates.name as tax_name, tax_rates.rate as tax_rate, products.unit, products.details as details, product_variants.name as variant, products.hsn_code as hsn_code, products.second_name as second_name')
            ->join('products', 'products.id=sale_items.product_id', 'left')
            ->join('product_variants', 'product_variants.id=sale_items.option_id', 'left')
            ->join('tax_rates', 'tax_rates.id=sale_items.tax_rate_id', 'left')
            ->group_by('sale_items.id')
            ->order_by('id', 'asc');

        $q = $this->db->get_where('sale_items', ['sale_id' => $sale_id]);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }



    public function updateStatus($id, $status, $note)
    {
        $this->db->trans_start();
        $sale  = $this->getInvoiceByID($id);
        $items = $this->getAllInvoiceItems($id);
        $cost  = [];

        if ($status == 'completed' && $sale->sale_status != 'completed') {
            foreach ($items as $item) {
                $items_array[] = (array) $item;
            }
            $cost = $this->site->costing($items_array);
        }
        if ($status != 'completed' && $sale->sale_status == 'completed') {
            $this->resetSaleActions($id);
        }

        if ($this->db->update('sales', ['sale_status' => $status, 'note' => $note], ['id' => $id])) {
            if ($status == 'completed' && $sale->sale_status != 'completed') {
                $this->db->delete('costing', ['sale_id' => $id]);
                foreach ($items as $item) {
                    $item = (array) $item;
                    if ($this->site->getProductByID($item['product_id'])) {
                        $item_costs = $this->site->item_costing($item);
                        foreach ($item_costs as $item_cost) {
                            if (isset($item_cost['date']) || isset($item_cost['pi_overselling'])) {
                                $item_cost['sale_item_id'] = $item['id'];
                                $item_cost['sale_id']      = $id;
                                $item_cost['date']         = date('Y-m-d', strtotime($sale->date));
                                if (!isset($item_cost['pi_overselling'])) {
                                    $this->db->insert('costing', $item_cost);
                                }
                            } else {
                                foreach ($item_cost as $ic) {
                                    $ic['sale_item_id'] = $item['id'];
                                    $ic['sale_id']      = $id;
                                    $ic['date']         = date('Y-m-d', strtotime($sale->date));
                                    if (!isset($ic['pi_overselling'])) {
                                        $this->db->insert('costing', $ic);
                                    }
                                }
                            }
                        }
                    }
                }
                if (!empty($cost)) {
                    $this->site->syncPurchaseItems($cost);
                }
                $this->site->syncQuantity($id);
            }
        }
        $this->db->trans_complete();
        if ($this->db->trans_status() === false) {
            log_message('error', 'An errors has been occurred while adding the sale (UpdataStatus:Sales_model.php)');
        } else {
            return true;
        }
        return false;
    }

    public function getQtyOnholdRequests($id=null) {
        $q = $this->db->get_where('product_qty_onhold_request', ['status' => 'onhold']);
        if ($q->num_rows() > 0) {
            
            return $q->result();
        }
        return false;

    }

    /**
     * Get Zatka Invoice Data
     * Prepares all data needed for Zatka-compliant invoice
     */
    public function getZatkaInvoiceData($sale_id)
    {
        // Get sale details
        $sale = $this->getSaleByID($sale_id);
        if (!$sale) {
            return false;
        }

        // Get sale items with calculations
        $items = $this->getInvoiceItems($sale_id);
        
        // Get customer details
        $customer = $this->site->getCompanyByID($sale->customer_id);
        
        // Get seller/biller details
        $seller = $this->site->getCompanyByID($sale->biller_id);
        
        // Get warehouse details
        $warehouse = $this->site->getWarehouseByID($sale->warehouse_id);

        // Format items for Zatka invoice
        $formatted_items = [];
        $invoice_total = 0;
        $total_discounts = 0;
        $subtotal = 0;
        $total_tax = 0;

        if ($items) {
            foreach ($items as $item) {
                // Calculate values
                $quantity = floatval($item->quantity);
                $unit_price = floatval($item->real_unit_price);
                $total = $quantity * $unit_price;
                
                // Discounts
                $discount_1_percent = floatval($item->discount ?? 0);
                $discount_1_amount = $total * ($discount_1_percent / 100);
                
                $discount_2_percent = floatval($item->item_discount ?? 0);
                $remaining_after_d1 = $total - $discount_1_amount;
                $discount_2_amount = $remaining_after_d1 * ($discount_2_percent / 100);
                
                $total_discount = $discount_1_amount + $discount_2_amount;
                $net_after_discount = $total - $total_discount;
                
                // Tax
                $tax_rate_percent = floatval($item->tax_rate ?? 0);
                $tax_amount = $net_after_discount * ($tax_rate_percent / 100);
                
                // Line total
                $line_total = $net_after_discount + $tax_amount;

                // Accumulate totals
                $invoice_total += $total;
                $total_discounts += $total_discount;
                $subtotal += $net_after_discount;
                $total_tax += $tax_amount;

                $formatted_items[] = [
                    'description_ar' => $item->product_name ?? '',
                    'description_en' => $item->second_name ?? $item->product_name ?? '',
                    'item_code' => $item->product_code ?? '',
                    'lot_number' => $item->batchno ?? '',
                    'expiry_date' => !empty($item->expiry) ? date('d/m/Y', strtotime($item->expiry)) : '',
                    'quantity' => $quantity,
                    'unit_price' => $unit_price,
                    'total' => $total,
                    'discount_1_percent' => $discount_1_percent,
                    'discount_1_amount' => $discount_1_amount,
                    'discount_2_percent' => $discount_2_percent,
                    'discount_2_amount' => $discount_2_amount,
                    'total_discount' => $total_discount,
                    'net_after_discount' => $net_after_discount,
                    'tax_rate_percent' => $tax_rate_percent,
                    'tax_amount' => $tax_amount,
                    'line_total' => $line_total
                ];
            }
        }

        $grand_total = $subtotal + $total_tax;

        // Prepare aging data (payment terms)
        $aging = [
            'less_30' => $grand_total,
            'thirty_to_sixty' => 0.00,
            'sixty_to_ninety' => 0.00,
            'ninety_to_one_twenty' => 0.00,
            'more_than_one_twenty' => 0.00
        ];

        // Convert amount to Arabic words
        $notes = $this->numberToArabicWords($grand_total);

        // Prepare complete data array
        $data = [
            // Invoice header
            'invoice_number' => str_pad($sale->reference_no, 4, '0', STR_PAD_LEFT),
            'invoice_date' => date('Y/m/d h:i A', strtotime($sale->date)),
            'invoice_date_hijri' => $this->gregorianToHijri($sale->date),
            
            // Seller information
            'seller' => [
                'name_en' => $seller->company ?? $seller->name,
                'name_ar' => $seller->company ?? $seller->name,
                'tax_id' => $seller->vat_no ?? '',
                'commercial_reg' => $seller->cf1 ?? '',
                'phone' => $seller->phone ?? '',
                'international_id' => $seller->cf2 ?? '',
                'address' => $seller->address ?? '',
                'address_city' => $seller->city ?? '',
                'branch_code' => $warehouse->code ?? '',
                'branch_name' => $warehouse->name ?? ''
            ],
            
            // Customer information
            'customer' => [
                'name_en' => $customer->company ?? $customer->name,
                'name_ar' => $customer->company ?? $customer->name,
                'tax_id' => $customer->vat_no ?? '',
                'commercial_reg' => $customer->cf1 ?? '',
                'phone' => $customer->phone ?? '',
                'international_id' => $customer->cf2 ?? '',
                'address' => $customer->address ?? '',
                'address_city' => $customer->city ?? '',
                'branch_code' => '',
                'branch_name' => ''
            ],
            
            // Items array
            'items' => $formatted_items,
            
            // Totals
            'totals' => [
                'invoice_total' => $invoice_total,
                'total_discounts' => $total_discounts,
                'subtotal' => $subtotal,
                'tax_amount' => $total_tax,
                'grand_total' => $grand_total
            ],
            
            // Aging report
            'aging' => $aging,
            
            // Notes
            'notes' => $notes,
            
            // QR code (will be generated in controller)
            'qr_code_image' => ''
        ];

        return $data;
    }

    /**
     * Convert Gregorian date to Hijri
     */
    private function gregorianToHijri($date)
    {
        // Simple conversion (you may want to use a more accurate library)
        $timestamp = strtotime($date);
        $hijri_year = date('Y', $timestamp) - 579;
        $hijri_month = date('m', $timestamp);
        $hijri_day = date('d', $timestamp);
        
        return $hijri_year . '/' . $hijri_month . '/' . $hijri_day;
    }

    /**
     * Convert number to Arabic words
     */
    private function numberToArabicWords($number)
    {
        // Simplified version - returns formatted number
        // For full Arabic words conversion, use a dedicated library
        $rounded = number_format($number, 2);
        return "فقط " . $rounded . " ريال سعودي";
    }

    /**
     * Get invoice items for sale
     */
    public function getInvoiceItems($sale_id)
    {
        $this->db->select('sale_items.*, products.second_name, products.code as product_code, tax_rates.rate as tax_rate')
                 ->from('sale_items')
                 ->join('products', 'products.id = sale_items.product_id', 'left')
                 ->join('tax_rates', 'tax_rates.id = sale_items.tax_rate_id', 'left')
                 ->where('sale_items.sale_id', $sale_id)
                 ->order_by('sale_items.id', 'asc');
        
        $query = $this->db->get();
        
        if ($query->num_rows() > 0) {
            return $query->result();
        }
        
        return false;
    }

    /**
     * Apply advance payment to selected invoices with priority-based settlement
     */
    public function applyAdvanceToInvoices($advance_id, $invoice_ids, $amount, $payment_reference_id) {
        $this->db->trans_start();

        // Record advance usage
        $this->db->insert('customer_advance_usage', [
            'advance_id' => $advance_id,
            'payment_reference_id' => $payment_reference_id,
            'amount_used' => $amount,
            'created_at' => date('Y-m-d H:i:s')
        ]);

        // Update advance used amount
        $this->db->set('used_amount', 'used_amount + ' . $amount, FALSE)
                 ->where('id', $advance_id)
                 ->update('customer_advances');

        // Distribute amount across selected invoices
        $remaining_amount = $amount;
        foreach ($invoice_ids as $invoice_id) {
            if ($remaining_amount <= 0) break;

            $invoice = $this->getInvoiceByID($invoice_id);
            $outstanding = $invoice->grand_total - ($invoice->paid ?? 0);

            if ($outstanding > 0) {
                $apply_to_invoice = min($remaining_amount, $outstanding);

                // Add payment record
                $this->db->insert('payments', [
                    'date' => date('Y-m-d'),
                    'sale_id' => $invoice_id,
                    'reference_no' => 'ADV-' . $payment_reference_id,
                    'amount' => $apply_to_invoice,
                    'paid_by' => 'advance',
                    'note' => 'Applied from customer advance',
                    'created_by' => $this->session->userdata('user_id')
                ]);

                // Update sale paid amount
                $this->db->set('paid', 'paid + ' . $apply_to_invoice, FALSE)
                         ->where('id', $invoice_id)
                         ->update('sales');

                $remaining_amount -= $apply_to_invoice;
            }
        }

        $this->db->trans_complete();
        return $this->db->trans_status();
    }

    /**
     * Apply return credit to selected invoices with priority-based settlement
     */
    public function applyReturnToInvoices($return_id, $invoice_ids, $amount, $payment_reference_id) {
        $this->db->trans_start();

        // Record return usage
        $this->db->insert('return_usage', [
            'return_id' => $return_id,
            'payment_reference_id' => $payment_reference_id,
            'amount_used' => $amount,
            'created_at' => date('Y-m-d H:i:s')
        ]);

        // Update return paid amount
        $this->db->set('paid', 'paid + ' . $amount, FALSE)
                 ->where('id', $return_id)
                 ->update('sales');

        // Distribute amount across selected invoices
        $remaining_amount = $amount;
        foreach ($invoice_ids as $invoice_id) {
            if ($remaining_amount <= 0) break;

            $invoice = $this->getInvoiceByID($invoice_id);
            $outstanding = $invoice->grand_total - ($invoice->paid ?? 0);

            if ($outstanding > 0) {
                $apply_to_invoice = min($remaining_amount, $outstanding);

                // Add payment record
                $this->db->insert('payments', [
                    'date' => date('Y-m-d'),
                    'sale_id' => $invoice_id,
                    'reference_no' => 'RTN-' . $payment_reference_id,
                    'amount' => $apply_to_invoice,
                    'paid_by' => 'return',
                    'note' => 'Applied from customer return',
                    'created_by' => $this->session->userdata('user_id')
                ]);

                // Update sale paid amount
                $this->db->set('paid', 'paid + ' . $apply_to_invoice, FALSE)
                         ->where('id', $invoice_id)
                         ->update('sales');

                $remaining_amount -= $apply_to_invoice;
            }
        }

        $this->db->trans_complete();
        return $this->db->trans_status();
    }

    /**
     * Apply credit memo to selected invoices with priority-based settlement
     */
    public function applyCreditMemoToInvoices($creditmemo_id, $invoice_ids, $amount, $payment_reference_id) {
        $this->db->trans_start();

        // Record credit memo usage
        $this->db->insert('credit_memo_usage', [
            'credit_memo_id' => $creditmemo_id,
            'payment_reference_id' => $payment_reference_id,
            'amount_used' => $amount,
            'created_at' => date('Y-m-d H:i:s')
        ]);

        // Update credit memo used amount
        $this->db->set('used_amount', 'used_amount + ' . $amount, FALSE)
                 ->where('id', $creditmemo_id)
                 ->update('credit_memos');

        // Distribute amount across selected invoices
        $remaining_amount = $amount;
        foreach ($invoice_ids as $invoice_id) {
            if ($remaining_amount <= 0) break;

            $invoice = $this->getInvoiceByID($invoice_id);
            $outstanding = $invoice->grand_total - ($invoice->paid ?? 0);

            if ($outstanding > 0) {
                $apply_to_invoice = min($remaining_amount, $outstanding);

                // Add payment record
                $this->db->insert('payments', [
                    'date' => date('Y-m-d'),
                    'sale_id' => $invoice_id,
                    'reference_no' => 'CM-' . $payment_reference_id,
                    'amount' => $apply_to_invoice,
                    'paid_by' => 'credit_memo',
                    'note' => 'Applied from credit memo',
                    'created_by' => $this->session->userdata('user_id')
                ]);

                // Update sale paid amount
                $this->db->set('paid', 'paid + ' . $apply_to_invoice, FALSE)
                         ->where('id', $invoice_id)
                         ->update('sales');

                $remaining_amount -= $apply_to_invoice;
            }
        }

        $this->db->trans_complete();
        return $this->db->trans_status();
    }

    /**
     * Apply payment amount to selected invoices after priority settlement
     */
    public function applyPaymentToInvoices($invoice_ids, $amount, $payment_reference_id, $ledger_account) {
        $this->db->trans_start();

        // Distribute payment amount across selected invoices
        $remaining_amount = $amount;
        foreach ($invoice_ids as $invoice_id) {
            if ($remaining_amount <= 0) break;

            $invoice = $this->getInvoiceByID($invoice_id);
            $outstanding = $invoice->grand_total - ($invoice->paid ?? 0);

            if ($outstanding > 0) {
                $apply_to_invoice = min($remaining_amount, $outstanding);

                // Add payment record
                $this->db->insert('payments', [
                    'date' => date('Y-m-d'),
                    'sale_id' => $invoice_id,
                    'reference_no' => 'PAY-' . $payment_reference_id,
                    'amount' => $apply_to_invoice,
                    'paid_by' => 'cash',
                    'note' => 'Payment from customer',
                    'created_by' => $this->session->userdata('user_id')
                ]);

                // Update sale paid amount
                $this->db->set('paid', 'paid + ' . $apply_to_invoice, FALSE)
                         ->where('id', $invoice_id)
                         ->update('sales');

                $remaining_amount -= $apply_to_invoice;
            }
        }

        $this->db->trans_complete();
        return $this->db->trans_status();
    }

    public function addCustomerAdvance($data) {
        $this->db->insert('sma_customer_advances', $data);
        return $this->db->insert_id();
    }

}
