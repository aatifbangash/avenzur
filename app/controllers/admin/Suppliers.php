<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Suppliers extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();

        if (!$this->loggedIn) {
            $this->session->set_userdata('requested_page', $this->uri->uri_string());
            $this->sma->md('login');
        }
        if ($this->Customer || $this->Supplier) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect($_SERVER['HTTP_REFERER']);
        }
        $this->lang->admin_load('suppliers', $this->Settings->user_language);
        $this->load->admin_model('purchases_model');
        $this->load->library('form_validation');
        $this->load->admin_model('companies_model');

        // Sequence-Code
        $this->load->library('SequenceCode');
        $this->sequenceCode = new SequenceCode();
    }

    public function edit_suppliers_script(){
        $csvFile = 'files/Retaj_customers_csv.csv';

        // Open the file in read mode
        if (($handle = fopen($csvFile, 'r')) !== false) {
            // Read the header row (if needed)
            $header = fgetcsv($handle);

            // Loop through each row of the file
            while (($data = fgetcsv($handle, 1000, ',')) !== false) {
                $ascon_code = $data[0];
                $arabic_name = $data[1];
                $english_name = $data[2];
                $credit_limit = $data[18];
                $payment_term = $data[25];
                $parent_code = '';
                $supplier_level = 1;
                $phone = $data[7];

                $company_detail = $this->companies_model->getCompanyByName($arabic_name);
                
                if($company_detail){
                    // Update existing Company
                    //echo 'Customer Found: '. $company_detail->name.'<br />';
                    $this->companies_model->updateCompanyNames($company_detail->id, ['name_ar' => $english_name, 'sequence_code' => $ascon_code, 'phone' => $phone, 'parent_code' => $parent_code, 'level' => $supplier_level, 'payment_term' => $payment_term, 'credit_limit' => $credit_limit]);
                }else{
                    // Insert New Company
                    echo 'Customer Not Found: '.$arabic_name.'<br />';
                    $this->companies_model->addCompany(['group_id' => 3, 'group_name' => 'customer', 'name' => $arabic_name, 'name_ar' => $english_name, 'logo' => 'logo.png', 'ledger_account' => 21, 'cogs_ledger' => 174, 'sales_ledger' => 157, 'discount_ledger' => 173, 'return_ledger' => 165, 'sequence_code' => $ascon_code, 'phone' => $phone, 'parent_code' => $parent_code, 'level' => $supplier_level, 'payment_term' => $payment_term, 'credit_limit' => $credit_limit]);
                }
                
            }

            echo 'Customer script executed successfully...';
            // Close the file handle
            fclose($handle);
        } else {
            echo "Error opening the file.";
        }
    }

    public function deleteFromAccounting($memo_id){
        $accouting_entries = $this->purchases_model->getMemoAccountingEntries($memo_id);
        foreach ($accouting_entries as $accouting_entry){
            $this->db->delete('sma_accounts_entryitems', ['entry_id' => $accouting_entry->id]);
            $this->db->delete('sma_accounts_entries', ['id' => $accouting_entry->id]);
        }
    }

    public function convert_supplier_payment_multiple_invoice($supplier_id, $ledger_account, $bank_charges_account, $payment_amount, $bank_charges, $reference_no, $type){
        $this->load->admin_model('companies_model');
        $supplier = $this->companies_model->getCompanyByID($supplier_id);

        /*Accounts Entries*/
        $entry = array(
            'entrytype_id' => 4,
            'transaction_type' => $type,
            'number'       => 'PM-'.$reference_no,
            'date'         => date('Y-m-d'), 
            'dr_total'     => $payment_amount + $bank_charges,
            'cr_total'     => $payment_amount + $bank_charges,
            'notes'        => 'Payment Reference: '.$reference_no.' Date: '.date('Y-m-d H:i:s'),
            'pid'          =>  '',
            'supplier_id'  => $supplier_id
            );
        $add  = $this->db->insert('sma_accounts_entries', $entry);
        $insert_id = $this->db->insert_id();

        //supplier
        $entryitemdata[] = array(
            'Entryitem' => array(
                'entry_id' => $insert_id,
                'dc' => 'D',
                'ledger_id' => $supplier->ledger_account,
                'amount' => $payment_amount,
                'narration' => ''
            )
        );

        //bank charges
        $entryitemdata[] = array(
            'Entryitem' => array(
                'entry_id' => $insert_id,
                'dc' => 'D',
                'ledger_id' => $bank_charges_account,
                'amount' => $bank_charges,
                'narration' => ''
            )
        );

        //transfer legdger
        $entryitemdata[] = array(
            'Entryitem' => array(
                'entry_id' => $insert_id,
                'dc' => 'C',
                'ledger_id' => $ledger_account,
                'amount' => $payment_amount + $bank_charges,
                'narration' => ''
            )
        );

        foreach ($entryitemdata as $row => $itemdata)
        {
            $this->db->insert('sma_accounts_entryitems' ,$itemdata['Entryitem']);
        }
    }

    public function convert_supplier_advance_invoice($memo_id, $supplier_id, $ledger_account, $bank_charges_account, $payment_amount, $bank_charges, $reference_no, $type){
        $this->load->admin_model('companies_model');
        $supplier = $this->companies_model->getCompanyByID($supplier_id);

        /*Accounts Entries*/
        $entry = array(
            'entrytype_id' => 4,
            'transaction_type' => $type,
            'number'       => 'SPADV-'.$reference_no,
            'date'         => date('Y-m-d'), 
            'dr_total'     => $payment_amount + $bank_charges,
            'cr_total'     => $payment_amount + $bank_charges,
            'notes'        => 'Supplier Advance Reference: '.$reference_no.' Date: '.date('Y-m-d H:i:s'),
            'pid'          =>  '',
            'memo_id'      => $memo_id
            );
        $add  = $this->db->insert('sma_accounts_entries', $entry);
        $insert_id = $this->db->insert_id();

        //supplier
        $entryitemdata[] = array(
            'Entryitem' => array(
                'entry_id' => $insert_id,
                'dc' => 'D',
                'ledger_id' => $supplier->ledger_account,
                'amount' => $payment_amount,
                'narration' => ''
            )
        );

        //bank charges
        $entryitemdata[] = array(
            'Entryitem' => array(
                'entry_id' => $insert_id,
                'dc' => 'D',
                'ledger_id' => $bank_charges_account,
                'amount' => $bank_charges,
                'narration' => ''
            )
        );

        //transfer legdger
        $entryitemdata[] = array(
            'Entryitem' => array(
                'entry_id' => $insert_id,
                'dc' => 'C',
                'ledger_id' => $ledger_account,
                'amount' => $payment_amount + $bank_charges,
                'narration' => ''
            )
        );

        foreach ($entryitemdata as $row => $itemdata)
        {
            $this->db->insert('sma_accounts_entryitems' ,$itemdata['Entryitem']);
        }
    }

    public function convert_debit_memo_invoice($memo_id, $supplier_id, $ledger_account, $bank_charges_account, $payment_amount, $bank_charges, $reference_no, $type){
        $this->load->admin_model('companies_model');
        $supplier = $this->companies_model->getCompanyByID($supplier_id);

        /*Accounts Entries*/
        $entry = array(
            'entrytype_id' => 4,
            'transaction_type' => $type,
            'number'       => 'DM-'.$reference_no,
            'date'         => date('Y-m-d'), 
            'dr_total'     => $payment_amount + $bank_charges,
            'cr_total'     => $payment_amount + $bank_charges,
            'notes'        => 'Debit Memo Reference: '.$reference_no.' Date: '.date('Y-m-d H:i:s'),
            'pid'          =>  '',
            'memo_id'      => $memo_id
            );
        $add  = $this->db->insert('sma_accounts_entries', $entry);
        $insert_id = $this->db->insert_id();

        //supplier
        $entryitemdata[] = array(
            'Entryitem' => array(
                'entry_id' => $insert_id,
                'dc' => 'D',
                'ledger_id' => $supplier->ledger_account,
                'amount' => $payment_amount,
                'narration' => ''
            )
        );

        //bank charges
        $entryitemdata[] = array(
            'Entryitem' => array(
                'entry_id' => $insert_id,
                'dc' => 'D',
                'ledger_id' => $bank_charges_account,
                'amount' => $bank_charges,
                'narration' => ''
            )
        );

        //transfer legdger
        $entryitemdata[] = array(
            'Entryitem' => array(
                'entry_id' => $insert_id,
                'dc' => 'C',
                'ledger_id' => $ledger_account,
                'amount' => $payment_amount + $bank_charges,
                'narration' => ''
            )
        );

        foreach ($entryitemdata as $row => $itemdata)
        {
            $this->db->insert('sma_accounts_entryitems' ,$itemdata['Entryitem']);
        }
    }

    public function make_supplier_payment($id, $amount, $reference_no, $date){
        $payment = [
            'date'         => $date,
            'purchase_id'  => $id,
            'reference_no' => $reference_no,
            'amount'       => $amount,
            'note'         => 'Multiple invoices payment',
            'created_by'   => $this->session->userdata('user_id'),
            'type'         => 'sent',
        ];

        $this->purchases_model->addPayment($payment);
    }

    public function update_purchase_order($id, $amount){
        $this->purchases_model->update_purchase_paid_amount($id, $amount);
    }

    public function add_debit_memo($memo_id, $supplier_id, $reference_no, $description, $payment_amount, $date){
        $memoData = array(
            'memo_id' => $memo_id,
            'supplier_id' => $supplier_id,
            'reference_no' => $reference_no,
            'description' => $description,
            'payment_amount' => $payment_amount,
            'type' => 'memo',
            'date' => $date
        );
        $this->db->insert('sma_memo_entries' ,$memoData);
    }

    public function add_advance_to_supplier($memo_id, $supplier_id, $reference_no, $description, $payment_amount, $date){
        $memoData = array(
            'memo_id' => $memo_id,
            'supplier_id' => $supplier_id,
            'reference_no' => $reference_no,
            'description' => $description,
            'payment_amount' => $payment_amount,
            'type' => 'supplieradvance',
            'date' => $date
        );
        $this->db->insert('sma_memo_entries' ,$memoData);
    }

    public function edit_advance_to_supplier($id=null){
        $this->sma->checkPermissions(false, true);

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }

        $debit_memo_data = $this->purchases_model->getDebitMemoData($id);
        $debit_memo_entries_data = $this->purchases_model->getDebitMemoEntriesData($id);
        
        $data = [];
        $this->data['memo_data'] = $debit_memo_data;
        
        $this->data['memo_entries_data'] = $debit_memo_entries_data;
        $this->data['suppliers']  = $this->site->getAllCompanies('supplier');
        $this->page_construct('suppliers/advance_to_supplier', $meta, $this->data);
    }

    public function advance_to_supplier(){
        $this->sma->checkPermissions(false, true);
        $this->form_validation->set_rules('supplier', $this->lang->line('supplier'), 'required');

        $data = [];
        $bc    = [['link' => base_url(), 'page' => lang('home')], ['link' => '#', 'page' => lang('Advance To Supplier')]];
        $meta = ['page_title' => lang('Advance To Supplier'), 'bc' => $bc];
        if ($this->form_validation->run() == true) {
            $request_type = $this->input->post('request_type');
            $supplier_id      = $this->input->post('supplier');
            $payments_array      = $this->input->post('payment_amount');
            $descriptions_array      = $this->input->post('description');
            $item_ids = $this->input->post('item_id');
            $reference_no = $this->input->post('reference_no');
            $payment_total = $this->input->post('payment_total');
            $ledger_account = $this->input->post('ledger_account');
            $bank_charges_account = $this->input->post('bank_charges_account');
            $bank_charges = $this->input->post('bank_charges');
            $date_fmt = $this->input->post('date');

            $formattedDate = DateTime::createFromFormat('Y-m-d', $date_fmt);
            $isDateValid = $formattedDate && $formattedDate->format('Y-m-d') === $date_fmt;
            
            if($isDateValid){
                $date = $date_fmt;
            }else{
                $formattedDate = DateTime::createFromFormat('d/m/Y', $date_fmt);
                $date = $formattedDate->format('Y-m-d');
            }

            if(array_sum($payments_array) == $payment_total){
                if($request_type == 'update'){
                    $memo_id2 = $this->input->post('memo_id');
                   
                    // Delete older data
                    $this->db->delete('sma_memo_entries', ['memo_id' => $memo_id2]);
                    $this->db->delete('sma_memo', ['id' => $memo_id2]);
                    $this->deleteFromAccounting($memo_id2);
                }

                $memoData = array(
                    'supplier_id' => $supplier_id,
                    'customer_id' => 0,
                    'reference_no' => $reference_no,
                    'payment_amount' => $payment_total,
                    'bank_charges' => $bank_charges,
                    'ledger_account' => $ledger_account,
                    'bank_charges_account' => $bank_charges_account,
                    'type' => 'supplieradvance',
                    'date' => $date
                );
               
                $this->db->insert('sma_memo' ,$memoData);
                $memo_id = $this->db->insert_id();

                for ($i = 0; $i < count($payments_array); $i++) {
                    $payment_amount = $payments_array[$i];
                    $description = $descriptions_array[$i];
                    if($payment_amount > 0){
                        $this->add_advance_to_supplier($memo_id, $supplier_id, $reference_no, $description, $payment_amount, $date);
                    }
                }

                $this->convert_supplier_advance_invoice($memo_id, $supplier_id, $ledger_account, $bank_charges_account, $payment_total, $bank_charges, $reference_no, 'supplieradvance');
                $this->session->set_flashdata('message', lang('Advance To Supplier added Successfully!'));
                admin_redirect('suppliers/list_advance_to_supplier');
            }else{
                $this->session->set_flashdata('error', 'Total Sum Of Amounts do not match');
                redirect($_SERVER['HTTP_REFERER']);
            }
        } else {
            $this->data['suppliers']  = $this->site->getAllCompanies('supplier');
            $this->data['warehouses'] = $this->site->getAllWarehouses();
            $this->page_construct('suppliers/advance_to_supplier', $meta, $this->data);
        }
    }

    public function list_advance_to_supplier(){
        $this->data['debit_memo'] = $this->purchases_model->getDebitMemo('supplieradvance');
        $this->data['suppliers']  = $this->site->getAllCompanies('supplier');
        $this->page_construct('suppliers/list_advance_to_supplier', $meta, $this->data);
    }

    public function list_debit_memo(){
        $this->data['debit_memo'] = $this->purchases_model->getDebitMemo('memo');
        $this->data['suppliers']  = $this->site->getAllCompanies('supplier');
        $this->page_construct('suppliers/list_debit_memo', $meta, $this->data);
    }

    public function edit_debit_memo($id = null){
        $this->sma->checkPermissions(false, true);

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }

        $debit_memo_data = $this->purchases_model->getDebitMemoData($id);
        $debit_memo_entries_data = $this->purchases_model->getDebitMemoEntriesData($id);
        
        $data = [];
        $this->data['memo_data'] = $debit_memo_data;
        
        $this->data['memo_entries_data'] = $debit_memo_entries_data;
        $this->data['suppliers']  = $this->site->getAllCompanies('supplier');
        $this->page_construct('suppliers/debit_memo', $meta, $this->data);
    }

    public function debit_memo(){
        $this->sma->checkPermissions(false, true);
        $this->form_validation->set_rules('supplier', $this->lang->line('supplier'), 'required');

        $data = [];
        $bc    = [['link' => base_url(), 'page' => lang('home')], ['link' => '#', 'page' => lang('debit memo')]];
        $meta = ['page_title' => lang('Debit Memo'), 'bc' => $bc];
        if ($this->form_validation->run() == true) {
            $request_type = $this->input->post('request_type');
            $supplier_id      = $this->input->post('supplier');
            $payments_array      = $this->input->post('payment_amount');
            $descriptions_array      = $this->input->post('description');
            $item_ids = $this->input->post('item_id');
            $reference_no = $this->input->post('reference_no');
            $payment_total = $this->input->post('payment_total');
            $ledger_account = $this->input->post('ledger_account');
            $bank_charges_account = $this->input->post('bank_charges_account');
            $bank_charges = $this->input->post('bank_charges');
            $date_fmt = $this->input->post('date');

            //$date_fmt = '2023-06-27';
            $formattedDate = DateTime::createFromFormat('Y-m-d', $date_fmt);
            $isDateValid = $formattedDate && $formattedDate->format('Y-m-d') === $date_fmt;
            
            if($isDateValid){
                $date = $date_fmt;
            }else{
                $formattedDate = DateTime::createFromFormat('d/m/Y', $date_fmt);
                $date = $formattedDate->format('Y-m-d');
            }
            
            if(array_sum($payments_array) == $payment_total){

                if($request_type == 'update'){
                    $memo_id2 = $this->input->post('memo_id');
                   
                    // Delete older data
                    $this->db->delete('sma_memo_entries', ['memo_id' => $memo_id2]);
                    $this->db->delete('sma_memo', ['id' => $memo_id2]);
                    $this->deleteFromAccounting($memo_id2);
                }

                $memoData = array(
                    'supplier_id' => $supplier_id,
                    'customer_id' => 0,
                    'reference_no' => $reference_no,
                    'payment_amount' => $payment_total,
                    'bank_charges' => $bank_charges,
                    'ledger_account' => $ledger_account,
                    'bank_charges_account' => $bank_charges_account,
                    'type' => 'memo',
                    'date' => $date
                );
               
                $this->db->insert('sma_memo' ,$memoData);
                $memo_id = $this->db->insert_id();
               
                for ($i = 0; $i < count($payments_array); $i++) {
                    $payment_amount = $payments_array[$i];
                    $description = $descriptions_array[$i];
                    if($payment_amount > 0){
                        $this->add_debit_memo($memo_id, $supplier_id, $reference_no, $description, $payment_amount, $date);
                    }
                }

                $this->convert_debit_memo_invoice($memo_id, $supplier_id, $ledger_account, $bank_charges_account, $payment_total, $bank_charges, $reference_no, 'debitmemo');
                $this->session->set_flashdata('message', lang('Debit Memo invoice added Successfully!'));
                admin_redirect('suppliers/list_debit_memo');
            }else{
                $this->session->set_flashdata('error', 'Total Sum Of Amounts do not match');
                redirect($_SERVER['HTTP_REFERER']);
            }
        } else {
            $this->data['suppliers']  = $this->site->getAllCompanies('supplier');
            $this->data['warehouses'] = $this->site->getAllWarehouses();
            $this->page_construct('suppliers/debit_memo', $meta, $this->data);
        }
    }

    public function edit_payment($id = null){
        $this->sma->checkPermissions(false, true);
        $this->form_validation->set_rules('payment_id', $this->lang->line('payment_id'), 'required');
        $this->form_validation->set_rules('amount', $this->lang->line('amount'), 'required');

        if ($this->form_validation->run() == true) {
            $payment_id      = $this->input->post('payment_id');
            $amount          = $this->input->post('amount');

            $payment_detail  = $this->purchases_model->getPaymentByID($payment_id);
            $purchase_detail = $this->purchases_model->getPurchaseByID($payment_detail->purchase_id);
            
            $old_amount = $payment_detail->amount;

            $difference = $amount - $old_amount;
            $accounts_reference_no = 'PM-'.$payment_detail->reference_no;
            $accounts_entry = $this->purchases_model->getAccountsEntryByReferenceNo($accounts_reference_no);
            $entry_id = $accounts_entry->id;
            $dr_total = $accounts_entry->dr_total;
            $cr_total = $accounts_entry->cr_total;

            $new_dr_total = $dr_total + $difference;
            $new_cr_total = $cr_total + $difference;

            $updated_price_for_purchase = $purchase_detail->paid + $difference;

            $debit_entry = null;
            $credit_entry = null;

            $this->db->update('payments', ['amount' => $amount], ['id' => $payment_id]);
            $this->db->update('purchases', ['paid' => $updated_price_for_purchase], ['id' => $payment_detail->purchase_id]);

            $this->db->update('sma_accounts_entries', ['dr_total' => $new_dr_total, 'cr_total' => $new_cr_total], ['id' => $entry_id]);

            // Debit Entry
            $this->db->select('*');
            $this->db->from('sma_accounts_entryitems');
            $this->db->where('entry_id', $entry_id);
            $this->db->where('dc', 'D');
            $this->db->order_by('amount', 'DESC');
            $this->db->limit(1);

            $query = $this->db->get();

            if ($query->num_rows() > 0) {
                $debit_entry = $query->row();
            }

            // Credit Entry
            $q = $this->db->get_where('sma_accounts_entryitems', ['entry_id' => $entry_id, 'dc' => 'C'], 1);
            if ($q->num_rows() > 0) {
                $credit_entry = $q->row();
            }

            if($debit_entry != null){
                $debit_entry_amount = $debit_entry->amount + $difference;
                $this->db->update('sma_accounts_entryitems', ['amount' => $debit_entry_amount], ['id' => $debit_entry->id]);
            }

            if($credit_entry != null){
                $credit_entry_amount = $credit_entry->amount + $difference;
                $this->db->update('sma_accounts_entryitems', ['amount' => $credit_entry_amount], ['id' => $credit_entry->id]);
            }

            admin_redirect('suppliers/list_payments');
        }else{
            if ($this->input->get('id')) {
                $id = $this->input->get('id');
            }
    
            $this->data['payment']  = $this->purchases_model->getPaymentByID($id);
            $this->page_construct('suppliers/edit_payment', $meta, $this->data);
        }
    }

    public function list_payments(){
        $this->data['payments'] = $this->purchases_model->getPayments();
        $this->page_construct('suppliers/list_payments', $meta, $this->data);
    }

    public function add_payment()
    {
        $this->sma->checkPermissions(false, true);
        $this->form_validation->set_rules('supplier', $this->lang->line('supplier'), 'required');

        $data = [];
        $bc    = [['link' => base_url(), 'page' => lang('home')], ['link' => '#', 'page' => lang('add payment')]];
        $meta = ['page_title' => lang('Supplier Payments'), 'bc' => $bc];

        if ($this->form_validation->run() == true) {
            $supplier_id      = $this->input->post('supplier');
            $payments_array      = $this->input->post('payment_amount');
            $item_ids = $this->input->post('item_id');
            $reference_no = $this->input->post('reference_no');
            $payment_total = $this->input->post('payment_total');
            $ledger_account = $this->input->post('ledger_account');
            $bank_charges_account = $this->input->post('bank_charges_account');
            $bank_charges = $this->input->post('bank_charges');
            //$date = $this->input->post('date');
            $due_amount_array = $this->input->post('due_amount');

            $date_fmt = $this->input->post('date');
            
            $formattedDate = DateTime::createFromFormat('Y-m-d', $date_fmt);
            $isDateValid = $formattedDate && $formattedDate->format('Y-m-d') === $date_fmt;
            
            if($isDateValid){
                $date = $date_fmt;
            }else{
                $formattedDate = DateTime::createFromFormat('d/m/Y', $date_fmt);
                $date = $formattedDate->format('Y-m-d');
            }
            
            for($i = 0; $i < count($payments_array); $i++){
                $payment_amount = $payments_array[$i];
                $item_id = $item_ids[$i];
                $due_amount = $due_amount_array[$i];
                if($payment_amount > $due_amount){
                    $this->session->set_flashdata('error', 'Amount paid cannot be greater than due amount');
                    redirect($_SERVER['HTTP_REFERER']);
                }
            }

            if(array_sum($payments_array) == $payment_total){
                for ($i = 0; $i < count($payments_array); $i++) {
                    $payment_amount = $payments_array[$i];
                    $item_id = $item_ids[$i];
                    $due_amount = $due_amount_array[$i];
                    if($payment_amount > 0){
                        $this->update_purchase_order($item_id, $payment_amount);
                        $this->make_supplier_payment($item_id, $payment_amount, $reference_no, $date);
                    }
                }

                $this->convert_supplier_payment_multiple_invoice($supplier_id, $ledger_account, $bank_charges_account, $payment_total, $bank_charges, $reference_no, 'supplierpayment');
                $this->session->set_flashdata('message', lang('Payment invoice added Successfully!'));
                admin_redirect($_SERVER['HTTP_REFERER']);
            }else{
                $this->session->set_flashdata('error', 'Total Sum Of Amounts do not match');
                redirect($_SERVER['HTTP_REFERER']);
            }
            
        } else {
            $this->data['suppliers']  = $this->site->getAllCompanies('supplier');
            $this->data['warehouses'] = $this->site->getAllWarehouses();
            $this->page_construct('suppliers/add_payment', $meta, $this->data);
        }
    }

    public function pending_invoices(){
        $supplier_id = $_GET['supplier_id'];
        if ($rows = $this->purchases_model->getPendingInvoicesBySupplier($supplier_id)) {
            $data = json_encode($rows);
        } else {
            $data = false;
        }
        echo $data;
    }

    public function add()
    {
        $this->sma->checkPermissions(false, true);

        $this->form_validation->set_rules('email', $this->lang->line('email_address'), 'is_unique[companies.email]');

        if ($this->form_validation->run('companies/add') == true) {
            $data = [
                'name'        => $this->input->post('name'),
                'name_ar'        => $this->input->post('name_ar'), 
                'category'        => $this->input->post('category'),
                'email'       => $this->input->post('email'),
                'group_id'    => '4',
                'group_name'  => 'supplier',
                'company'     => $this->input->post('company'),
                'address'     => $this->input->post('address'),
                'vat_no'      => $this->input->post('vat_no'),
                'city'        => $this->input->post('city'),
                'state'       => $this->input->post('state'),
                'postal_code' => $this->input->post('postal_code'),
                'country'     => $this->input->post('country'),
                'phone'       => $this->input->post('phone'),
                'cf1'         => $this->input->post('cf1'),
                'cf2'         => $this->input->post('cf2'),
                'cf3'         => $this->input->post('cf3'),
                'cf4'         => $this->input->post('cf4'),
                'cf5'         => $this->input->post('cf5'),
                'cf6'         => $this->input->post('cf6'),
                'gst_no'      => $this->input->post('gst_no'),
                'ledger_account' => $this->input->post('ledger_account'),
                'payment_term' => $this->input->post('payment_term'),
                'credit_limit'        => $this->input->post('credit_limit') ? $this->input->post('credit_limit') : '0',
                'sequence_code'  => $this->sequenceCode->generate('SUP', 5)
            ];
        } elseif ($this->input->post('add_supplier')) {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect('suppliers');
        }

        if ($this->form_validation->run() == true && $sid = $this->companies_model->addCompany($data)) {
            $this->session->set_flashdata('message', $this->lang->line('supplier_added'));
            $ref = isset($_SERVER['HTTP_REFERER']) ? explode('?', $_SERVER['HTTP_REFERER']) : null;
            admin_redirect($ref[0] . '?supplier=' . $sid);
        } else {
            $this->data['error']    = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'suppliers/add', $this->data);
        }
    }

    public function add_user($company_id = null)
    {
        $this->sma->checkPermissions(false, true);

        if ($this->input->get('id')) {
            $company_id = $this->input->get('id');
        }
        $company = $this->companies_model->getCompanyByID($company_id);

        $this->form_validation->set_rules('email', $this->lang->line('email_address'), 'is_unique[users.email]');
        $this->form_validation->set_rules('password', $this->lang->line('password'), 'required|min_length[8]|max_length[20]|matches[password_confirm]');
        $this->form_validation->set_rules('password_confirm', $this->lang->line('confirm_password'), 'required');

        if ($this->form_validation->run('companies/add_user') == true) {
            $active                  = $this->input->post('status');
            $notify                  = $this->input->post('notify');
            list($username, $domain) = explode('@', $this->input->post('email'));
            $email                   = strtolower($this->input->post('email'));
            $password                = $this->input->post('password');
            $additional_data         = [
                'first_name' => $this->input->post('first_name'),
                'last_name'  => $this->input->post('last_name'),
                'phone'      => $this->input->post('phone'),
                'gender'     => $this->input->post('gender'),
                'company_id' => $company->id,
                'company'    => $company->company,
                'group_id'   => 3,
            ];
            $this->load->library('ion_auth');
        } elseif ($this->input->post('add_user')) {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect('suppliers');
        }

        if ($this->form_validation->run() == true && $this->ion_auth->register($username, $password, $email, $additional_data, $active, $notify)) {
            $this->session->set_flashdata('message', $this->lang->line('user_added'));
            admin_redirect('suppliers');
        } else {
            $this->data['error']    = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['modal_js'] = $this->site->modal_js();
            $this->data['company']  = $company;
            $this->load->view($this->theme . 'suppliers/add_user', $this->data);
        }
    }

    public function delete($id = null)
    {
        $this->sma->checkPermissions(null, true);

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        if (!$id) {
            $this->sma->send_json(['error' => 1, 'msg' => lang('id_not_found')]);
        }

        if ($this->companies_model->deleteSupplier($id)) {
            $this->sma->send_json(['error' => 0, 'msg' => lang('supplier_deleted')]);
        } else {
            $this->sma->send_json(['error' => 1, 'msg' => lang('supplier_x_deleted_have_purchases')]);
        }
    }

    public function edit($id = null)
    {
        $this->sma->checkPermissions(false, true);

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }

        $company_details = $this->companies_model->getCompanyByID($id);
        //echo '<pre>';  print_r($company_details); exit;
        if ($this->input->post('email') != $company_details->email) {
            $this->form_validation->set_rules('code', lang('email_address'), 'is_unique[companies.email]');
        }

        if ($this->form_validation->run('companies/add') == true) {
            $data = [
                'name'        => $this->input->post('name'),
                'name_ar'        => $this->input->post('name_ar'), 
                'category'        => $this->input->post('category'),
                'email'       => $this->input->post('email'),
                'group_id'    => '4',
                'group_name'  => 'supplier',
                'company'     => $this->input->post('company'),
                'address'     => $this->input->post('address'),
                'vat_no'      => $this->input->post('vat_no'),
                'city'        => $this->input->post('city'),
                'state'       => $this->input->post('state'),
                'postal_code' => $this->input->post('postal_code'),
                'country'     => $this->input->post('country'),
                'phone'       => $this->input->post('phone'),
                'cf1'         => $this->input->post('cf1'),
                'cf2'         => $this->input->post('cf2'),
                'cf3'         => $this->input->post('cf3'),
                'cf4'         => $this->input->post('cf4'),
                'cf5'         => $this->input->post('cf5'),
                'cf6'         => $this->input->post('cf6'),
                'gst_no'      => $this->input->post('gst_no'),
                'ledger_account'      => $this->input->post('ledger_account'),
                'payment_term' => $this->input->post('payment_term'),
                'credit_limit'        => $this->input->post('credit_limit') ? $this->input->post('credit_limit') : '0',
            ];
            if(empty($company_details->sequence_code)){
                $data['sequence_code'] =$this->sequenceCode->generate('SUP', 5); 
            } 

        } elseif ($this->input->post('edit_supplier')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER['HTTP_REFERER']);
        }

        if ($this->form_validation->run() == true && $this->companies_model->updateCompany($id, $data)) {
            $this->session->set_flashdata('message', $this->lang->line('supplier_updated'));
            redirect($_SERVER['HTTP_REFERER']);
        } else {
            $this->data['supplier'] = $company_details;
            $this->data['error']    = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'suppliers/edit', $this->data);
        }
    }

    public function getSupplier($id = null)
    {
        // $this->sma->checkPermissions('index');
        $row = $this->companies_model->getCompanyByID($id);
        $this->sma->send_json([['id' => $row->id, 'text' => $row->company]]);
    }

    public function getSuppliers()
    {
        $this->sma->checkPermissions('index');

        $this->load->library('datatables');
        $this->datatables
            ->select('id, company, sequence_code, name, email, phone, city, country, vat_no, gst_no')
            ->from('companies')
            ->where('group_name', 'supplier')
            ->add_column('Actions', "<div class=\"text-center\"><a class=\"tip\" title='" . $this->lang->line('list_products') . "' href='" . admin_url('products?supplier=$1') . "'><i class=\"fa fa-list\"></i></a> <a class=\"tip\" title='" . $this->lang->line('list_users') . "' href='" . admin_url('suppliers/users/$1') . "' data-toggle='modal' data-target='#myModal'><i class=\"fa fa-users\"></i></a> <a class=\"tip\" title='" . $this->lang->line('add_user') . "' href='" . admin_url('suppliers/add_user/$1') . "' data-toggle='modal' data-target='#myModal'><i class=\"fa fa-plus-circle\"></i></a> <a class=\"tip\" title='" . $this->lang->line('edit_supplier') . "' href='" . admin_url('suppliers/edit/$1') . "' data-toggle='modal' data-target='#myModal'><i class=\"fa fa-edit\"></i></a> <a href='#' class='tip po' title='<b>" . $this->lang->line('delete_supplier') . "</b>' data-content=\"<p>" . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . admin_url('suppliers/delete/$1') . "'>" . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i></a></div>", 'id');
        //->unset_column('id');
        echo $this->datatables->generate();
    }

    public function import_csv()
    {
        $this->sma->checkPermissions('add', true);
        $this->load->helper('security');
        $this->form_validation->set_rules('csv_file', $this->lang->line('upload_file'), 'xss_clean');

        if ($this->form_validation->run() == true) {
            if (DEMO) {
                $this->session->set_flashdata('warning', $this->lang->line('disabled_in_demo'));
                redirect($_SERVER['HTTP_REFERER']);
            }

            if (isset($_FILES['csv_file'])) {
                $this->load->library('upload');

                $config['upload_path']   = 'files/';
                $config['allowed_types'] = 'csv';
                $config['max_size']      = '2000';
                $config['overwrite']     = false;
                $config['encrypt_name']  = true;

                $this->upload->initialize($config);

                if (!$this->upload->do_upload('csv_file')) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    admin_redirect('suppliers');
                }

                $csv = $this->upload->file_name;

                $arrResult = [];
                $handle    = fopen('files/' . $csv, 'r');
                if ($handle) {
                    while (($row = fgetcsv($handle, 5001, ',')) !== false) {
                        $arrResult[] = $row;
                    }
                    fclose($handle);
                }
                $titles  = array_shift($arrResult);
                $rw      = 2;
                $updated = '';
                $data    = [];
                foreach ($arrResult as $key => $value) {
                    $supplier = [
                        'company'     => isset($value[0]) ? trim($value[0]) : '',
                        'name'        => isset($value[1]) ? trim($value[1]) : '',
                        'name_ar'     => isset($value[2]) ? trim($value[2]) : '',
                        'category'    => isset($value[3]) ? trim($value[3]) : '',
                        'email'       => isset($value[4]) ? trim($value[4]) : '',
                        'phone'       => isset($value[5]) ? trim($value[5]) : '',
                        'address'     => isset($value[6]) ? trim($value[6]) : '',
                        'city'        => isset($value[7]) ? trim($value[7]) : '',
                        'state'       => isset($value[8]) ? trim($value[8]) : '',
                        'postal_code' => isset($value[9]) ? trim($value[9]) : '',
                        'country'     => isset($value[10]) ? trim($value[10]) : '',
                        'vat_no'      => isset($value[11]) ? trim($value[11]) : '',
                        'gst_no'      => isset($value[12]) ? trim($value[12]) : '',
                        'cf1'         => isset($value[13]) ? trim($value[13]) : '',
                        'cf2'         => isset($value[14]) ? trim($value[14]) : '',
                        'cf3'         => isset($value[15]) ? trim($value[15]) : '',
                        'cf4'         => isset($value[16]) ? trim($value[16]) : '',
                        'cf5'         => isset($value[17]) ? trim($value[17]) : '',
                        'cf6'         => isset($value[18]) ? trim($value[18]) : '',
                        'payment_term'        => isset($value[19]) ? trim($value[19]) : '',
                        'credit_limit'        => isset($value[20]) ? trim($value[20]) : '',
                        'group_id'    => 4,
                        'group_name'  => 'supplier',
                        'sequence_code'  => $this->sequenceCode->generate('SUP', 5)
                    ];
                    if (empty($supplier['company']) || empty($supplier['name']) || empty($supplier['email'])) {
                        $this->session->set_flashdata('error', lang('company') . ', ' . lang('name') . ', ' . lang('email') . ' ' . lang('are_required') . ' (' . lang('line_no') . ' ' . $rw . ')');
                        admin_redirect('suppliers');
                    } else {
                        if ($this->Settings->indian_gst && empty($supplier['state'])) {
                            $this->session->set_flashdata('error', lang('state') . ' ' . lang('is_required') . ' (' . lang('line_no') . ' ' . $rw . ')');
                            admin_redirect('suppliers');
                        }
                        if ($supplier_details = $this->companies_model->getCompanyByEmail($supplier['email'])) {
                            if ($supplier_details->group_id == 4) {
                                $updated .= '<p>' . lang('supplier_updated') . ' (' . $supplier['email'] . ')</p>';
                                if(!empty($supplier_details->sequence_code)){
                                    $supplier['sequence_code']=$supplier_details->sequence_code; 
                                }
                                $this->companies_model->updateCompany($supplier_details->id, $supplier);
                            }
                        } else {
                            $data[] = $supplier;
                        }
                        $rw++;
                    }
                }

                // $this->sma->print_arrays($data, $updated);
            }
        } elseif ($this->input->post('import')) {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect('suppliers');
        }

        if ($this->form_validation->run() == true && !empty($data)) {
            if ($this->companies_model->addCompanies($data)) {
                $this->session->set_flashdata('message', $this->lang->line('suppliers_added') . $updated);
                admin_redirect('suppliers');
            }
        } else {
            if (isset($data) && empty($data)) {
                if ($updated) {
                    $this->session->set_flashdata('message', $updated);
                } else {
                    $this->session->set_flashdata('warning', lang('data_x_suppliers'));
                }
                admin_redirect('suppliers');
            }

            $this->data['error']    = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'suppliers/import', $this->data);
        }
    }

    public function index($action = null)
    {
        $this->sma->checkPermissions();

        $this->data['error']  = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $this->data['action'] = $action;
        $bc                   = [['link' => base_url(), 'page' => lang('home')], ['link' => '#', 'page' => lang('suppliers')]];
        $meta                 = ['page_title' => lang('suppliers'), 'bc' => $bc];
        $this->page_construct('suppliers/index', $meta, $this->data);
    }

    public function suggestions($term = null, $limit = null)
    {
        // $this->sma->checkPermissions('index');
        if ($this->input->get('term')) {
            $term = $this->input->get('term', true);
        }
        $term            = addslashes($term);
        $limit           = $this->input->get('limit', true);
        $rows['results'] = $this->companies_model->getSupplierSuggestions($term, $limit);
        $this->sma->send_json($rows);
    }

    public function supplier_actions()
    {
        if (!$this->Owner && !$this->GP['bulk_actions']) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect($_SERVER['HTTP_REFERER']);
        }

        $this->form_validation->set_rules('form_action', lang('form_action'), 'required');

        if ($this->form_validation->run() == true) {
            if (!empty($_POST['val'])) {
                if ($this->input->post('form_action') == 'delete') {
                    $this->sma->checkPermissions('delete');
                    $error = false;
                    foreach ($_POST['val'] as $id) {
                        if (!$this->companies_model->deleteSupplier($id)) {
                            $error = true;
                        }
                    }
                    if ($error) {
                        $this->session->set_flashdata('warning', lang('suppliers_x_deleted_have_purchases'));
                    } else {
                        $this->session->set_flashdata('message', $this->lang->line('suppliers_deleted'));
                    }
                    redirect($_SERVER['HTTP_REFERER']);
                }

                if ($this->input->post('form_action') == 'export_excel') {
                    $this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle(lang('customer'));
                    $this->excel->getActiveSheet()->SetCellValue('A1', lang('company'));
                    $this->excel->getActiveSheet()->SetCellValue('B1', lang('sequence_code'));
                    $this->excel->getActiveSheet()->SetCellValue('C1', lang('name'));
                    $this->excel->getActiveSheet()->SetCellValue('D1', lang('name_arabic'));
                    $this->excel->getActiveSheet()->SetCellValue('E1', lang('category'));
                    $this->excel->getActiveSheet()->SetCellValue('F1', lang('email'));
                    $this->excel->getActiveSheet()->SetCellValue('G1', lang('phone'));
                    $this->excel->getActiveSheet()->SetCellValue('H1', lang('address'));
                    $this->excel->getActiveSheet()->SetCellValue('I1', lang('city'));
                    $this->excel->getActiveSheet()->SetCellValue('J1', lang('state'));
                    $this->excel->getActiveSheet()->SetCellValue('K1', lang('postal_code'));
                    $this->excel->getActiveSheet()->SetCellValue('L1', lang('country'));
                    $this->excel->getActiveSheet()->SetCellValue('M1', lang('vat_no'));
                    $this->excel->getActiveSheet()->SetCellValue('N1', lang('gst_no'));
                    $this->excel->getActiveSheet()->SetCellValue('O1', lang('scf1'));
                    $this->excel->getActiveSheet()->SetCellValue('P1', lang('scf2'));
                    $this->excel->getActiveSheet()->SetCellValue('Q1', lang('scf3'));
                    $this->excel->getActiveSheet()->SetCellValue('R1', lang('scf4'));
                    $this->excel->getActiveSheet()->SetCellValue('S1', lang('scf5'));
                    $this->excel->getActiveSheet()->SetCellValue('T1', lang('scf6'));

                    $row = 2;
                    foreach ($_POST['val'] as $id) {
                        $customer = $this->site->getCompanyByID($id);
                        $this->excel->getActiveSheet()->SetCellValue('A' . $row, $customer->company);
                        $this->excel->getActiveSheet()->SetCellValue('B' . $row, $customer->sequence_code);
                        $this->excel->getActiveSheet()->SetCellValue('C' . $row, $customer->name);
                        $this->excel->getActiveSheet()->SetCellValue('D' . $row, $customer->name_ar);
                        $this->excel->getActiveSheet()->SetCellValue('E' . $row, $customer->category);
                        $this->excel->getActiveSheet()->SetCellValue('F' . $row, $customer->email);
                        $this->excel->getActiveSheet()->SetCellValue('G' . $row, $customer->phone);
                        $this->excel->getActiveSheet()->SetCellValue('H' . $row, $customer->address);
                        $this->excel->getActiveSheet()->SetCellValue('I' . $row, $customer->city);
                        $this->excel->getActiveSheet()->SetCellValue('J' . $row, $customer->state);
                        $this->excel->getActiveSheet()->SetCellValue('K' . $row, $customer->postal_code);
                        $this->excel->getActiveSheet()->SetCellValue('L' . $row, $customer->country);
                        $this->excel->getActiveSheet()->SetCellValue('M' . $row, $customer->vat_no);
                        $this->excel->getActiveSheet()->SetCellValue('N' . $row, $customer->gst_no);
                        $this->excel->getActiveSheet()->SetCellValue('O' . $row, $customer->cf1);
                        $this->excel->getActiveSheet()->SetCellValue('P' . $row, $customer->cf2);
                        $this->excel->getActiveSheet()->SetCellValue('Q' . $row, $customer->cf3);
                        $this->excel->getActiveSheet()->SetCellValue('R' . $row, $customer->cf4);
                        $this->excel->getActiveSheet()->SetCellValue('S' . $row, $customer->cf5);
                        $this->excel->getActiveSheet()->SetCellValue('T' . $row, $customer->cf6);
                        //$this->excel->getActiveSheet()->SetCellValue('QR' . $row, $customer->cf6);
                        $row++;
                    }

                    $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
                    $this->excel->getDefaultStyle()->getAlignment()->setVertical('center');
                    $filename = 'suppliers_' . date('Y_m_d_H_i_s');
                    $this->load->helper('excel');
                    create_excel($this->excel, $filename);
                }
            } else {
                $this->session->set_flashdata('error', $this->lang->line('no_supplier_selected'));
                redirect($_SERVER['HTTP_REFERER']);
            }
        } else {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER['HTTP_REFERER']);
        }
    }

    public function users($company_id = null)
    {
        $this->sma->checkPermissions(false, true);

        if ($this->input->get('id')) {
            $company_id = $this->input->get('id');
        }

        $this->data['error']    = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
        $this->data['modal_js'] = $this->site->modal_js();
        $this->data['company']  = $this->companies_model->getCompanyByID($company_id);
        $this->data['users']    = $this->companies_model->getCompanyUsers($company_id);
        $this->load->view($this->theme . 'suppliers/users', $this->data);
    }

    public function view($id = null)
    {
        $this->sma->checkPermissions('index', true);
        $this->data['error']    = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $this->data['supplier'] = $this->companies_model->getCompanyByID($id);
        $this->load->view($this->theme . 'suppliers/view', $this->data);
    }

    public function list_service_invoice(){
        $this->data['service_invoices'] = $this->purchases_model->getDebitMemo('serviceinvoicesupplier');
        //$this->data['suppliers']  = $this->site->getAllCompanies('supplier');
        $this->page_construct('suppliers/list_service_invoice', $meta, $this->data);
    }

    public function edit_service_invoice($id = null){
        $this->sma->checkPermissions(false, true);

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }

        $service_invoice_data = $this->purchases_model->getDebitMemoData($id);
        $service_invoice_entries_data = $this->purchases_model->getDebitMemoEntriesData($id);

        $data = [];
        $this->data['memo_data'] = $service_invoice_data;

        $this->data['memo_entries_data'] = $service_invoice_entries_data;
        $this->data['suppliers']  = $this->site->getAllCompanies('supplier');
        $this->page_construct('suppliers/service_invoice', $meta, $this->data);
    }

    public function service_invoice(){
        $this->sma->checkPermissions(false, true);
        $this->form_validation->set_rules('supplier', $this->lang->line('supplier'), 'required');

        $data = [];
        $bc    = [['link' => base_url(), 'page' => lang('home')], ['link' => '#', 'page' => lang('Service Invoice')]];
        $meta = ['page_title' => lang('Service Invoice'), 'bc' => $bc];
        if ($this->form_validation->run() == true) {
            $request_type = $this->input->post('request_type');
            $supplier_id      = $this->input->post('supplier');
            //$payments_array      = $this->input->post('payment_amount');
            $descriptions_array      = $this->input->post('description');
            $item_ids = $this->input->post('item_id');
            $reference_no = $this->input->post('reference_no');
            $payment_total = $this->input->post('payment_total');
            $ledger_account = $this->input->post('ledger_account');
            $vat_account = $this->input->post('vat_account');
            $vat_charges = $this->input->post('vat_charges');
            $date_fmt = $this->input->post('date');

            $formattedDate = DateTime::createFromFormat('Y-m-d', $date_fmt);
            $isDateValid = $formattedDate && $formattedDate->format('Y-m-d') === $date_fmt;

            if($isDateValid){
                $date = $date_fmt;
            }else{
                $formattedDate = DateTime::createFromFormat('d/m/Y', $date_fmt);
                $date = $formattedDate->format('Y-m-d');
            }

            if($payment_total > 0){
                if($request_type == 'update'){
                    $memo_id2 = $this->input->post('memo_id');

                    // Delete older data
                    $this->db->delete('sma_memo_entries', ['memo_id' => $memo_id2]);
                    $this->db->delete('sma_memo', ['id' => $memo_id2]);
                    $this->deleteFromAccounting($memo_id2);
                }

                $memoData = array(
                    'supplier_id' => 0,
                    'supplier_id' => $supplier_id,
                    'reference_no' => $reference_no,
                    'payment_amount' => $payment_total,
                    'bank_charges' => $vat_charges,
                    'ledger_account' => $ledger_account,
                    'bank_charges_account' => $vat_account,
                    'type' => 'serviceinvoicesupplier',
                    'date' => $date
                );

                $this->db->insert('sma_memo' ,$memoData);
                $memo_id = $this->db->insert_id();

                $this->add_service_invoice($memo_id, $supplier_id, $reference_no, $description, $payment_total, $date);
            }

            $this->convert_service_invoice($memo_id, $supplier_id, $ledger_account, $vat_account, $payment_total, $vat_charges, $reference_no, 'serviceinvoice');
            $this->session->set_flashdata('message', lang('Service Invoice added Successfully!'));
            admin_redirect('suppliers/list_service_invoice');

        } else {
            $this->data['suppliers']  = $this->site->getAllCompanies('supplier');
            //$this->data['warehouses'] = $this->site->getAllWarehouses();
            $this->page_construct('suppliers/service_invoice', $meta, $this->data);
        }
    }

    public function add_service_invoice($memo_id, $supplier_id, $reference_no, $description, $payment_amount, $date){
        $memoData = array(
            'memo_id' => $memo_id,
            'supplier_id' => $supplier_id,
            'reference_no' => $reference_no,
            'description' => $description,
            'payment_amount' => $payment_amount,
            'type' => 'serviceinvoice',
            'date' => $date
        );
        $this->db->insert('sma_memo_entries' ,$memoData);
    }

    public function convert_service_invoice($memo_id, $supplier_id, $ledger_account, $vat_account, $payment_amount, $vat_charges, $reference_no, $type){
        $this->load->admin_model('companies_model');
        $supplier = $this->companies_model->getCompanyByID($supplier_id);

        /*Accounts Entries*/
        $entry = array(
            'entrytype_id' => 4,
            'transaction_type' => $type,
            'number'       => 'SIS-'.$reference_no,
            'date'         => date('Y-m-d'),
            'dr_total'     => $payment_amount + $vat_charges,
            'cr_total'     => $payment_amount + $vat_charges,
            'notes'        => 'Service Invoice Reference: '.$reference_no.' Date: '.date('Y-m-d H:i:s'),
            'pid'          =>  '',
            'memo_id'      => $memo_id
            );
        $add  = $this->db->insert('sma_accounts_entries', $entry);
        $insert_id = $this->db->insert_id();

        //Supplier
        $entryitemdata[] = array(
            'Entryitem' => array(
                'entry_id' => $insert_id,
                'dc' => 'C',
                'ledger_id' => $supplier->ledger_account,
                'amount' => $payment_amount + $vat_charges,
                'narration' => ''
            )
        );

        //vat charges
        $entryitemdata[] = array(
            'Entryitem' => array(
                'entry_id' => $insert_id,
                'dc' => 'D',
                'ledger_id' => $vat_account,
                'amount' => $vat_charges,
                'narration' => ''
            )
        );

        //transfer legdger
        $entryitemdata[] = array(
            'Entryitem' => array(
                'entry_id' => $insert_id,
                'dc' => 'D',
                'ledger_id' => $ledger_account,
                'amount' => $payment_amount,
                'narration' => ''
            )
        );

        foreach ($entryitemdata as $row => $itemdata)
        {
            $this->db->insert('sma_accounts_entryitems' ,$itemdata['Entryitem']);
        }
    }

}
