<?php

defined('BASEPATH') or exit('No direct script access allowed');
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use Mpdf\Mpdf;
class Suppliers extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();

        if (!$this->loggedIn) {
            $this->session->set_userdata('requested_page', $this->uri->uri_string());
            $url = "admin/login";
            if( $this->input->server('QUERY_STRING') ){
                $url = $url.'?'.$this->input->server('QUERY_STRING').'&redirect='.$this->uri->uri_string();
            }
           
            $this->sma->md($url);
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
        $csvFile = 'files/retaj_supplier_list_new_csv.csv';

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

                $company_detail = $this->companies_model->getCompanyByNameNew($arabic_name);
                
                if($company_detail){
                    // Update existing Company
                    echo 'Customer Found: '. $company_detail->name.'<br />';
                    //$this->companies_model->updateCompanyNames($company_detail->id, ['name_ar' => $english_name, 'sequence_code' => $ascon_code, 'phone' => $phone, 'parent_code' => $parent_code, 'level' => $supplier_level, 'payment_term' => $payment_term, 'credit_limit' => $credit_limit]);
                }else{
                    // Insert New Company
                    echo 'Customer Not Found: '.$arabic_name.'<br />';
                    //$this->companies_model->addCompany(['group_id' => 3, 'group_name' => 'customer', 'name' => $arabic_name, 'name_ar' => $english_name, 'logo' => 'logo.png', 'ledger_account' => 21, 'cogs_ledger' => 174, 'sales_ledger' => 157, 'discount_ledger' => 173, 'return_ledger' => 165, 'sequence_code' => $ascon_code, 'phone' => $phone, 'parent_code' => $parent_code, 'level' => $supplier_level, 'payment_term' => $payment_term, 'credit_limit' => $credit_limit]);
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

    public function convert_supplier_payment_multiple_invoice($supplier_id, $ledger_account, $bank_charges_account, $payment_amount, $bank_charges, $reference_no, $type, $date){
        $this->load->admin_model('companies_model');
        $supplier = $this->companies_model->getCompanyByID($supplier_id);

        // Calculate VAT on bank charges (15%)
        $bank_charge_vat = 0;
        if ($bank_charges > 0) {
            $bank_charge_vat = $bank_charges * 0.15; // 15% VAT
        }
        
        // Total includes payment, bank charges, and VAT on bank charges
        $total_amount = $payment_amount + $bank_charges + $bank_charge_vat;

        /*Accounts Entries*/
        $entry = array(
            'entrytype_id' => 4,
            'transaction_type' => $type,
            'number'       => 'PM-'.$reference_no,
            'date'         => $date, 
            'dr_total'     => $total_amount,
            'cr_total'     => $total_amount,
            'notes'        => 'Payment Reference: '.$reference_no.' Date: '.date('Y-m-d H:i:s'),
            'pid'          =>  '',
            'supplier_id'  => $supplier_id
            );
        $add  = $this->db->insert('sma_accounts_entries', $entry);
        $insert_id = $this->db->insert_id();

        $entryitemdata = array(); // Initialize array

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

        //bank charges (only add if bank_charges > 0 and valid ledger account)
        if($bank_charges > 0 && $bank_charges_account > 0) {
            $entryitemdata[] = array(
                'Entryitem' => array(
                    'entry_id' => $insert_id,
                    'dc' => 'D',
                    'ledger_id' => $bank_charges_account,
                    'amount' => $bank_charges,
                    'narration' => ''
                )
            );
        }

        // VAT on bank charges (only add if bank_charge_vat > 0)
        if($bank_charge_vat > 0 && $bank_charges_account > 0) {
            // Get VAT ledger from system settings (you might need to configure this)
            $settings = $this->Settings;
            $vat_ledger = isset($settings->vat_ledger_id) ? $settings->vat_ledger_id : $bank_charges_account;
            
            $entryitemdata[] = array(
                'Entryitem' => array(
                    'entry_id' => $insert_id,
                    'dc' => 'D',
                    'ledger_id' => $vat_ledger,
                    'amount' => $bank_charge_vat,
                    'narration' => 'VAT on Bank Charges (15%)'
                )
            );
        }

        //transfer legdger (only add if valid ledger_id)
        if($ledger_account > 0) {
            $entryitemdata[] = array(
                'Entryitem' => array(
                    'entry_id' => $insert_id,
                    'dc' => 'C',
                    'ledger_id' => $ledger_account,
                    'amount' => $total_amount,
                    'narration' => ''
                )
            );
        }

        foreach ($entryitemdata as $row => $itemdata)
        {
            $this->db->insert('sma_accounts_entryitems' ,$itemdata['Entryitem']);
        }

        return $insert_id;
    }

    public function create_supplier_advance_journal_entry($supplier_id, $transfer_ledger, $advance_ledger, $bank_charges_account, $payment_amount, $bank_charges, $reference_no, $date){
        $this->load->admin_model('companies_model');
        $supplier = $this->companies_model->getCompanyByID($supplier_id);

        // Calculate VAT on bank charges (15%)
        $bank_charge_vat = 0;
        if ($bank_charges > 0) {
            $bank_charge_vat = $bank_charges * 0.15; // 15% VAT
        }
        
        // Total includes payment, bank charges, and VAT on bank charges
        $total_amount = $payment_amount + $bank_charges + $bank_charge_vat;

        /*Accounts Entries*/
        $entry = array(
            'entrytype_id' => 4,
            'transaction_type' => 'supplieradvance',
            'number'       => 'SPADV-'.$reference_no,
            'date'         => $date, 
            'dr_total'     => $total_amount,
            'cr_total'     => $total_amount,
            'notes'        => 'Supplier Advance Payment Reference: '.$reference_no.' Date: '.date('Y-m-d H:i:s'),
            'pid'          =>  '',
            'supplier_id'  => $supplier_id
            );
        $add  = $this->db->insert('sma_accounts_entries', $entry);
        $insert_id = $this->db->insert_id();

        $entryitemdata = array();

        // Debit: Transfer/Bank ledger (cash out)
        if($transfer_ledger > 0) {
            $entryitemdata[] = array(
                'Entryitem' => array(
                    'entry_id' => $insert_id,
                    'dc' => 'C',
                    'ledger_id' => $transfer_ledger,
                    'amount' => $total_amount,
                    'narration' => 'Advance payment to supplier'
                )
            );
        }

        // Debit: Bank charges (if any)
        if($bank_charges > 0 && $bank_charges_account > 0) {
            $entryitemdata[] = array(
                'Entryitem' => array(
                    'entry_id' => $insert_id,
                    'dc' => 'D',
                    'ledger_id' => $bank_charges_account,
                    'amount' => $bank_charges,
                    'narration' => 'Bank charges'
                )
            );
        }

        // Debit: VAT on bank charges (if any)
        if($bank_charge_vat > 0 && $bank_charges_account > 0) {
            $settings = $this->Settings;
            $vat_ledger = isset($settings->vat_ledger_id) ? $settings->vat_ledger_id : $bank_charges_account;
            
            $entryitemdata[] = array(
                'Entryitem' => array(
                    'entry_id' => $insert_id,
                    'dc' => 'D',
                    'ledger_id' => $vat_ledger,
                    'amount' => $bank_charge_vat,
                    'narration' => 'VAT on Bank Charges (15%)'
                )
            );
        }

        // Credit: Supplier Advance ledger (advance balance increases)
        $entryitemdata[] = array(
            'Entryitem' => array(
                'entry_id' => $insert_id,
                'dc' => 'D',
                'ledger_id' => $advance_ledger,
                'amount' => $payment_amount,
                'narration' => 'Advance payment received from supplier'
            )
        );

        foreach ($entryitemdata as $row => $itemdata)
        {
            $this->db->insert('sma_accounts_entryitems' ,$itemdata['Entryitem']);
        }

        return $insert_id;
    }

    public function convert_supplier_advance_invoice($memo_id, $supplier_id, $ledger_account, $bank_charges_account, $payment_amount, $bank_charges, $reference_no, $type){
        $this->load->admin_model('companies_model');
        $supplier = $this->companies_model->getCompanyByID($supplier_id);

        // Calculate VAT on bank charges (15%)
        $bank_charge_vat = 0;
        if ($bank_charges > 0) {
            $bank_charge_vat = $bank_charges * 0.15; // 15% VAT
        }
        
        // Total includes payment, bank charges, and VAT on bank charges
        $total_amount = $payment_amount + $bank_charges + $bank_charge_vat;

        /*Accounts Entries*/
        $entry = array(
            'entrytype_id' => 4,
            'transaction_type' => $type,
            'number'       => 'SPADV-'.$reference_no,
            'date'         => date('Y-m-d'), 
            'dr_total'     => $total_amount,
            'cr_total'     => $total_amount,
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
        if($bank_charges > 0) {
            $entryitemdata[] = array(
                'Entryitem' => array(
                    'entry_id' => $insert_id,
                    'dc' => 'D',
                    'ledger_id' => $bank_charges_account,
                    'amount' => $bank_charges,
                    'narration' => ''
                )
            );
        }

        // VAT on bank charges (only add if bank_charge_vat > 0)
        if($bank_charge_vat > 0 && $bank_charges_account > 0) {
            // Get VAT ledger from system settings (you might need to configure this)
            $settings = $this->Settings;
            $vat_ledger = isset($settings->vat_ledger_id) ? $settings->vat_ledger_id : $bank_charges_account;
            
            $entryitemdata[] = array(
                'Entryitem' => array(
                    'entry_id' => $insert_id,
                    'dc' => 'D',
                    'ledger_id' => $vat_ledger,
                    'amount' => $bank_charge_vat,
                    'narration' => 'VAT on Bank Charges (15%)'
                )
            );
        }

        //transfer legdger
        $entryitemdata[] = array(
            'Entryitem' => array(
                'entry_id' => $insert_id,
                'dc' => 'C',
                'ledger_id' => $ledger_account,
                'amount' => $total_amount,
                'narration' => ''
            )
        );

        foreach ($entryitemdata as $row => $itemdata)
        {
            $this->db->insert('sma_accounts_entryitems' ,$itemdata['Entryitem']);
        }
    }

    public function convert_debit_memo_invoice($memo_id, $supplier_id, $ledger_account, $payment_amount, $reference_no, $type, $date, $supplier_entry_type = 'D', $vat_account = null, $vat_percent = '0'){
        $this->load->admin_model('companies_model');
        $supplier = $this->companies_model->getCompanyByID($supplier_id);

        // Calculate VAT based on user-selected percentage
        $vat_decimal = floatval($vat_percent) / 100;
        $vat_amount = $payment_amount * $vat_decimal;
        $total_amount = $payment_amount + $vat_amount;

        // Use user-selected VAT account, or default if not provided
        $vat_ledger_id = $vat_account ?: (isset($this->Settings->vat_output_ledger) ? $this->Settings->vat_output_ledger : 69);

        /*Accounts Entries*/
        $entry = array(
            'entrytype_id' => 4,
            'transaction_type' => $type,
            'number'       => 'DM-'.$reference_no,
            'date'         => $date, 
            'dr_total'     => $total_amount,
            'cr_total'     => $total_amount,
            'notes'        => 'Debit Memo Reference: '.$reference_no.' Date: '.date('Y-m-d H:i:s'),
            'pid'          =>  '',
            'memo_id'      => $memo_id,
            'supplier_id'  => $supplier_id
            );
        $add  = $this->db->insert('sma_accounts_entries', $entry);
        $insert_id = $this->db->insert_id();

        // Determine entry direction: Normal (D) or Reversed (C)
        // If supplier is credited (C), reverse all other entries
        $vat_dc = ($supplier_entry_type == 'D') ? 'C' : 'D';
        $ledger_dc = ($supplier_entry_type == 'D') ? 'C' : 'D';

        if($supplier_entry_type == 'D'){
            $ledger_amount = $total_amount - $vat_amount;
            $supplier_amount = $total_amount;
        }else{
            $ledger_amount = $total_amount;
            $supplier_amount = $total_amount - $vat_amount;
        }

        //supplier - debit or credit based on selection
        $entryitemdata[] = array(
            'Entryitem' => array(
                'entry_id' => $insert_id,
                'dc' => $supplier_entry_type, // D or C based on user selection
                'ledger_id' => $supplier->ledger_account,
                'amount' => $supplier_amount,
                'narration' => ''
            )
        );

        //VAT - only add if VAT amount > 0
        if($vat_amount > 0){
            $entryitemdata[] = array(
                'Entryitem' => array(
                    'entry_id' => $insert_id,
                    'dc' => $vat_dc,
                    'ledger_id' => $vat_ledger_id,
                    'amount' => $vat_amount,
                    'narration' => 'VAT @ ' . $vat_percent . '%'
                )
            );
        }

        //transfer ledger - credit when normal, debit when reversed
        $entryitemdata[] = array(
            'Entryitem' => array(
                'entry_id' => $insert_id,
                'dc' => $ledger_dc,
                'ledger_id' => $ledger_account,
                'amount' => $ledger_amount,
                'narration' => ''
            )
        );

        foreach ($entryitemdata as $row => $itemdata)
        {
            $this->db->insert('sma_accounts_entryitems' ,$itemdata['Entryitem']);
        }
    }

    public function add_supplier_reference($amount, $reference_no, $date, $note, $supplier_id, $bank_charges, $bank_charges_account, $ledger_account){
        // Calculate VAT on bank charges (15%)
        $bank_charge_vat = 0;
        if ($bank_charges > 0) {
            $bank_charge_vat = $bank_charges * 0.15; // 15% VAT
        }
        
        $payment_reference = [
            'supplier_id' => $supplier_id,
            'date' => $date,
            'sequence_code' => $this->sequenceCode->generate('PAY', 5),
            'note' => $note,
            'reference_no'  => $reference_no,
            'amount' => $amount,
            'bank_charges' => $bank_charges,
            'bank_charge_vat' => $bank_charge_vat,
            'bank_charges_ledger' => $bank_charges_account,
            'transfer_from_ledger' => $ledger_account,
            'created_by'    => $this->session->userdata('user_id')
        ];

        $payment_id = $this->purchases_model->addPaymentReference($payment_reference);
        
        // Debug: Log if payment reference creation failed
        if (!$payment_id) {
            error_log('Failed to create payment reference. Data: ' . json_encode($payment_reference));
            error_log('Database error: ' . $this->db->last_query());
        }
        
        return $payment_id;
    }

    public function make_supplier_payment($id, $amount, $reference_no, $date, $note, $payment_id){
        $payment = [
            'date'          => $date,
            'purchase_id'   => $id,
            'reference_no'  => $reference_no,
            'amount'        => $amount,
            'note'          => $note,
            'created_by'    => $this->session->userdata('user_id'),
            'type'          => 'sent',
            'payment_id'    => $payment_id
        ];

        $this->purchases_model->addPayment($payment);
    }

    public function make_supplier_advance_payment($supplier_id, $amount, $reference_no, $date, $note, $payment_id){
        $payment = [
            'date'          => $date,
            'purchase_id'   => NULL, // No specific purchase for advance payment
            'reference_no'  => $reference_no,
            'amount'        => $amount,
            'note'          => $note . ' (Advance Payment)',
            'created_by'    => $this->session->userdata('user_id'),
            'type'          => 'advance',
            'payment_id'    => $payment_id
        ];

        $this->purchases_model->addPayment($payment);
    }

    public function make_service_invoice_payment($service_invoice_id, $amount, $reference_no, $date, $note, $payment_id){
        // Update the service invoice used_amount in sma_memo
        $this->purchases_model->update_service_invoice_used_amount($service_invoice_id, $amount);
        
        // Create payment record (with purchase_id = NULL since it's a service invoice)
        $payment = [
            'date'          => $date,
            'purchase_id'   => NULL, // Service invoices don't have purchase_id
            'memo_id'       => $service_invoice_id, // Link to the service invoice memo
            'reference_no'  => $reference_no,
            'amount'        => $amount,
            'note'          => $note . ' (Service Invoice #' . $service_invoice_id . ')',
            'created_by'    => $this->session->userdata('user_id'),
            'type'          => 'sent',
            'payment_id'    => $payment_id
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
        //$this->sma->checkPermissions(false, true);

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
        //$this->sma->checkPermissions(false, true);
        $this->form_validation->set_rules('supplier', $this->lang->line('supplier'), 'required');

        $data = [];
        $bc    = [['link' => base_url(), 'page' => lang('home')], ['link' => '#', 'page' => lang('Advance To Supplier')]];
        $meta = ['page_title' => lang('Advance To Supplier'), 'bc' => $bc];
        if ($this->form_validation->run() == true) {
            $request_type = $this->input->post('request_type');
            $parentsupplier_id      = $this->input->post('supplier');
            $childsupplier_id = $this->input->post('childsupplier');
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
            
            if($childsupplier_id){
                $supplier_id = $childsupplier_id;
            }else{
                $supplier_id = $parentsupplier_id;
            }

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
        //echo '<pre>';print_r($this->data['debit_memo']);exit;
        $this->data['company'] = $this->site->getCompanyByID($this->data['debit_memo'][0]->supplier_id);
        $this->page_construct('suppliers/list_advance_to_supplier', $meta, $this->data);
    }

    public function list_debit_memo(){
        $this->data['debit_memo'] = $this->purchases_model->getDebitMemo('memo');
        $this->data['suppliers']  = $this->site->getAllCompanies('supplier');
        $this->page_construct('suppliers/list_debit_memo', $meta, $this->data);
    }

    public function delete_debit_memo($id = null){
        //$this->sma->checkPermissions('delete');

        if ($id) {
            // Delete in correct order to avoid foreign key constraints
            // 1. Delete memo entries
            $this->db->where('memo_id', $id);
            $this->db->delete('sma_memo_entries');
            
            // 2. Delete accounting entries
            $this->deleteFromAccounting($id);
            
            // 3. Delete memo record
            $this->db->where('id', $id);
            $this->db->delete('sma_memo');
            
            $this->session->set_flashdata('message', lang('Debit Memo deleted successfully!'));
        } else {
            $this->session->set_flashdata('error', lang('No memo ID provided'));
        }
        
        admin_redirect('suppliers/list_debit_memo');
    }

    public function view_debit_memo($id = null){
        //$this->sma->checkPermissions();

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }

        $debit_memo_data = $this->purchases_model->getDebitMemoData($id);
        $debit_memo_entries_data = $this->purchases_model->getDebitMemoEntriesData($id);
        
        // Get ledger data
        $ledgers = $this->site->getCompanyLedgers();
        $ledger_options = [];
        foreach($ledgers as $ledger){
            $ledger_options[$ledger->id] = $ledger->name;
        }
        
        $bc    = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('suppliers/list_debit_memo'), 'page' => lang('Debit Memo List')], ['link' => '#', 'page' => lang('View Supplier Memo')]];
        $meta = ['page_title' => lang('View Supplier Memo'), 'bc' => $bc];
        
        $this->data['memo_data'] = $debit_memo_data;
        $this->data['memo_entries_data'] = $debit_memo_entries_data;
        $this->data['ledger_options'] = $ledger_options;
        
        $this->page_construct('suppliers/view_debit_memo', $meta, $this->data);
    }

    public function edit_debit_memo($id = null){
        //$this->sma->checkPermissions(false, true);

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }

        $debit_memo_data = $this->purchases_model->getDebitMemoData($id);
        $debit_memo_entries_data = $this->purchases_model->getDebitMemoEntriesData($id);
        
        $bc    = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('suppliers/list_debit_memo'), 'page' => lang('Debit Memo List')], ['link' => '#', 'page' => lang('Edit Debit Memo')]];
        $meta = ['page_title' => lang('Edit Debit Memo'), 'bc' => $bc];
        
        $this->data['memo_data'] = $debit_memo_data;
        
        $this->data['memo_entries_data'] = $debit_memo_entries_data;
        $this->data['suppliers']  = $this->site->getAllCompanies('supplier');
        $this->page_construct('suppliers/debit_memo', $meta, $this->data);
    }

    public function debit_memo(){
        //$this->sma->checkPermissions(false, true);
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
            $vat_account = $this->input->post('vat_account');
            $vat_percent = $this->input->post('vat_percent') ?: '0';
            $supplier_entry_type = $this->input->post('supplier_entry_type') ?: 'D';
            $date_fmt = $this->input->post('date');

            if($ledger_account == null || empty($ledger_account)){
                $this->session->set_flashdata('error', 'Please select a ledger account for the debit memo.');
                admin_redirect('suppliers/debit_memo');
            }

            if($vat_percent > 0 && ($vat_account == null || empty($vat_account))){
                $this->session->set_flashdata('error', 'Please select a VAT account for the debit memo since VAT percentage is greater than 0.');
                admin_redirect('suppliers/debit_memo');

            }

            //$date_fmt = '2023-06-27';
            $formattedDate = DateTime::createFromFormat('Y-m-d', $date_fmt);
            $isDateValid = $formattedDate && $formattedDate->format('Y-m-d') === $date_fmt;
            
            if($isDateValid){
                $date = $date_fmt;
            }else{
                $formattedDate = DateTime::createFromFormat('d/m/Y', $date_fmt);
                $date = $formattedDate->format('Y-m-d');
            }
            
            // Remove breakdown matching validation - not required
            if($request_type == 'update'){
                $old_memo_id = $this->input->post('memo_id');
               
                // Delete all older data completely in correct order
                // 1. First delete memo entries
                $this->db->where('memo_id', $old_memo_id);
                $this->db->delete('sma_memo_entries');
                
                // 2. Then delete accounting entries
                $this->deleteFromAccounting($old_memo_id);
                
                // 3. Finally delete the memo record itself
                $this->db->where('id', $old_memo_id);
                $this->db->delete('sma_memo');
            }
            
            // Always insert fresh memo record (new or after delete)
            $memoData = array(
                'supplier_id' => $supplier_id,
                'customer_id' => 0,
                'reference_no' => $reference_no,
                'payment_amount' => $payment_total,
                'bank_charges' => 0,
                'ledger_account' => $ledger_account,
                'bank_charges_account' => 0,
                'vat_account' => $vat_account,
                'vat_percent' => $vat_percent,
                'supplier_entry_type' => $supplier_entry_type,
                'type' => 'memo',
                'date' => $date
            );
           
            $this->db->insert('sma_memo', $memoData);
            $memo_id = $this->db->insert_id();
           
            // Insert memo entries only if valid
            for ($i = 0; $i < count($payments_array); $i++) {
                $payment_amount = isset($payments_array[$i]) ? trim($payments_array[$i]) : '';
                $description = isset($descriptions_array[$i]) ? trim($descriptions_array[$i]) : '';
                
                // Only insert if both description and amount are not empty
                if($payment_amount !== '' && $payment_amount > 0 && $description !== ''){
                    $this->add_debit_memo($memo_id, $supplier_id, $reference_no, $description, $payment_amount, $date);
                }
            }

            $this->convert_debit_memo_invoice($memo_id, $supplier_id, $ledger_account, $payment_total, $reference_no, 'debitmemo', $date, $supplier_entry_type, $vat_account, $vat_percent);
            $this->session->set_flashdata('message', lang('Debit Memo invoice added Successfully!'));
            admin_redirect('suppliers/list_debit_memo');
        } else {
            $this->data['suppliers']  = $this->site->getAllCompanies('supplier');
            $this->data['warehouses'] = $this->site->getAllWarehouses();
            $this->page_construct('suppliers/debit_memo', $meta, $this->data);
        }
    }

    public function edit_payment($id = null){
        //$this->sma->checkPermissions(false, true);
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

    public function print_payment_pdf($id = null)
    {
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }

        $payment_ref = $this->purchases_model->getPaymentReferenceByID($id);
        $payments    = $this->purchases_model->getPaymentByReferenceID($id);

        if (!$payment_ref) {
            show_error('Payment not found');
        }

        // Get supplier info
        $supplier_id = $payment_ref->supplier_id ?? $payment_ref->id;
        $this->load->admin_model('Reports_model');
        $this->load->admin_model('companies_model');
        $supplier = $this->companies_model->getCompanyByID($supplier_id);

        // Get supplier balance (trial balance as of today)
        $today = date('Y-m-d');
        $balances = $this->Reports_model->get_suppliers_trial_balance('2000-01-01', $today, [$supplier_id]);
        
        $supplier_balance = 0;
        $total_due = 0;
        if (!empty($balances)) {
            $b = $balances[$supplier_id];
            $total_debit = $b['obDebit'] + $b['trsDebit'];
            $total_credit = $b['obCredit'] + $b['trsCredit'];
            $supplier_balance = ($total_credit ?? 0) - ($total_debit ?? 0);
            $total_due = $supplier_balance; // For most cases, due = balance
        }

        $settings = $this->Settings;
        $supplier_advance_ledger = isset($settings->supplier_advance_ledger) && !empty($settings->supplier_advance_ledger) 
                                    ? $settings->supplier_advance_ledger 
                                    : null;

        $advance_balance = $this->getSupplierAdvanceBalance($supplier_id, $supplier_advance_ledger);
        $total_paid = $total_debit;
        //echo $total_due; exit;
        // Get supplier aging (180 days)
        $aging = $this->Reports_model->getSupplierAgingNew(180, $today, [$supplier_id]);
        $supplier_aging = !empty($aging) ? $aging[$supplier_id] : null;
        //echo '<pre>';print_r($supplier_aging);exit;

        $this->data['payment_ref'] = $payment_ref;
        $this->data['payments']    = $payments;
        $this->data['supplier_balance'] = $advance_balance;
        $this->data['total_due'] = $total_due;
        $this->data['total_paid_balance'] = $total_paid;
        $this->data['supplier_aging'] = $supplier_aging;

        $biller      = $this->site->getDefaultBiller();
        //echo '<pre>';print_r($this->data);exit;
        // Load HTML
        $html = $this->load->view(
            $this->theme . 'suppliers/print_payment_pdf',
            $this->data,
            true
        );

        // Remove XML declaration if any
        $html = preg_replace("'\<\?xml(.*)\?\>'", '', $html);

        // mPDF config (same style as your invoice)
        $mpdf = new \Mpdf\Mpdf([
            'format'        => 'A4',
            'margin_top'    => 70,
            'margin_bottom' => 60,
            'margin_left'   => 10,
            'margin_right'  => 10,
            'default_font'  => 'DejaVu Sans',
        ]);

        /* ================= HEADER ================= */
        $mpdf->SetHTMLHeader('
        <div style="width:100%; font-family: DejaVu Sans; font-size:11px;">
            <div style="text-align:right; font-size:10px; color:#666;">
                Page {PAGENO} of {nbpg}
            </div>

            <div style="text-align:center; margin:8px 0;">
                <img src="data:image/png;base64,' . base64_encode(file_get_contents(base_url() . 'assets/uploads/logos/' . $biller->logo)) . '"
            alt="Avenzur" style="max-width:120px; height:auto;">
                <h3 style="margin:5px 0;">SUPPLIER PAYMENT VOUCHER</h3>
            </div>

            <hr>
        </div>
        ');

        /* ================= FOOTER ================= */
        $mpdf->SetHTMLFooter('
        <hr>
        <div style="font-size:11px; width:100%; font-family: DejaVu Sans;">
            <table width="100%" cellspacing="0" cellpadding="5">
                <tr>
                    <td align="center">
                        _________________________<br>Prepared By
                    </td>
                    <td align="center">
                        _________________________<br>Checked By
                    </td>
                    <td align="center">
                        _________________________<br>Approved By
                    </td>
                </tr>
            </table>
        </div>
        ');

        $mpdf->WriteHTML($html);
        $mpdf->Output('Supplier_Payment_' . $payment_ref->reference_no . '.pdf', 'D');
        exit;
    }

    public function view_payment($id = null){
        //$this->sma->checkPermissions(false, true);
        $this->form_validation->set_rules('id', $this->lang->line('id'), 'required');

        $data = [];
        $bc    = [['link' => base_url(), 'page' => lang('home')], ['link' => '#', 'page' => lang('View Supplier Payments')]];
        $meta = ['page_title' => lang('View Supplier Payments'), 'bc' => $bc];

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }

        $this->data['suppliers']  = $this->site->getAllCompanies('supplier');
        $this->data['payment_ref']  = $this->purchases_model->getPaymentReferenceByID($id);
        $this->data['payments']  = $this->purchases_model->getPaymentByReferenceID($id);
        $this->page_construct('suppliers/view_payment', $meta, $this->data);
        
    }

    public function list_payments(){
        $data = [];
        $bc    = [['link' => base_url(), 'page' => lang('home')], ['link' => '#', 'page' => lang('Supplier Payments')]];
        $meta = ['page_title' => lang('Supplier Payments'), 'bc' => $bc];

        // Build filters from GET
        $filters = [
            'supplier_id' => $this->input->get('supplier_id') ?: $this->input->post('supplier_id'),
            'category'    => $this->input->get('category')    ?: $this->input->post('category'),
            'from_date'   => $this->input->get('from_date')   ?: $this->input->post('from_date'),
            'to_date'     => $this->input->get('to_date')     ?: $this->input->post('to_date'),
        ];
        // Convert display date (d/m/Y) to Y-m-d for the query
        foreach (['from_date', 'to_date'] as $f) {
            if (!empty($filters[$f])) {
                $d = DateTime::createFromFormat('d/m/Y', $filters[$f]);
                if ($d) { $filters[$f] = $d->format('Y-m-d'); }
            }
        }

        $this->data['payments']   = $this->purchases_model->getPaymentReferences($filters);
        $this->data['suppliers']  = $this->site->getAllCompanies('supplier');
        $this->data['filters']    = $filters;

        // Build distinct category list from suppliers
        $categories = [];
        if (!empty($this->data['suppliers'])) {
            foreach ($this->data['suppliers'] as $s) {
                if (!empty($s->category) && !in_array($s->category, $categories)) {
                    $categories[] = $s->category;
                }
            }
            sort($categories);
        }
        $this->data['supplier_categories'] = $categories;

        // Export to Excel
        if ($this->input->get('export_excel') || $this->input->post('export_excel')) {
            $payments = $this->data['payments'];
            $this->load->library('excel');
            $sheet = $this->excel->setActiveSheetIndex(0);
            $sheet->setTitle('Supplier Payments');

            $sheet->SetCellValue('A1', '#');
            $sheet->SetCellValue('B1', 'Reference No.');
            $sheet->SetCellValue('C1', 'Supplier Code');
            $sheet->SetCellValue('D1', 'Supplier');
            $sheet->SetCellValue('E1', 'Category');
            $sheet->SetCellValue('F1', 'Date');
            $sheet->SetCellValue('G1', 'Payment Amount');
            $sheet->SetCellValue('H1', 'Bank');
            $sheet->SetCellValue('I1', 'Payment Type');

            $row = 2;
            $count = 1;
            foreach ($payments as $payment) {
                $date_only = !empty($payment->date) ? date('d-M-Y', strtotime($payment->date)) : '';
                $sheet->SetCellValue('A' . $row, $count++);
                $sheet->SetCellValue('B' . $row, $payment->reference_no);
                $sheet->SetCellValue('C' . $row, $payment->sequence_code ?? '');
                $sheet->SetCellValue('D' . $row, $payment->company);
                $sheet->SetCellValue('E' . $row, $payment->supplier_group ?? '');
                $sheet->SetCellValue('F' . $row, $date_only);
                $sheet->SetCellValue('G' . $row, $payment->amount);
                $sheet->SetCellValue('H' . $row, $payment->ledger_name ?? '');
                $sheet->SetCellValue('I' . $row, ucfirst($payment->payment_type ?? 'standard'));
                $row++;
            }

            foreach (range('A', 'I') as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }

            $this->excel->getDefaultStyle()->getAlignment()->setVertical('center');
            $filename = 'Supplier_Payments_' . date('Y-m-d_H_i_s');
            $this->load->helper('excel');
            create_excel($this->excel, $filename);
            return;
        }

        $this->page_construct('suppliers/list_payments', $meta, $this->data);
    }

    public function add_payment()
    {
        $this->form_validation->set_rules('supplier',       $this->lang->line('supplier'),       'required');
        $this->form_validation->set_rules('ledger_account', $this->lang->line('ledger_account'), 'required');

        $data = [];
        $bc    = [['link' => base_url(), 'page' => lang('home')], ['link' => '#', 'page' => lang('add payment')]];
        $meta = ['page_title' => lang('Supplier Payments'), 'bc' => $bc];

        if ($this->form_validation->run() == true) {
            $supplier_id      = $this->input->post('supplier');
            $payments_array      = $this->input->post('payment_amount');
            $item_ids = $this->input->post('item_id');
            $invoice_types = $this->input->post('invoice_type'); // Get invoice types (purchase or service)
            $reference_no = $this->input->post('reference_no');
            $payment_total = $this->input->post('payment_total');
            $ledger_account = $this->input->post('ledger_account');
            //$bank_charges_account = $this->input->post('bank_charges_account');

            $bank_charges_account = $this->Settings->bank_fees_ledger ?? null; // Fallback to null if not set
            
            if($bank_charges_account == null || $bank_charges_account == '' || $bank_charges_account == 0){
                $this->session->set_flashdata('error', 'Bank Charges Ledger is not configured in system settings. Please set it up before adding payments with bank charges.');
                redirect($_SERVER['HTTP_REFERER']);
            }

            $bank_charges = $this->input->post('bank_charges');
            $note = $this->input->post('note');
            $vat = $this->input->post('vat');
            $supplier_advance_ledger = $this->input->post('supplier_advance_ledger');
            $settle_with_advance = $this->input->post('settle_with_advance');
            $payment_mode = $this->input->post('payment_mode'); // Get payment mode
            //echo '<pre>'; print_r($payment_mode); exit;
            if($payment_mode == 'invoice_settlement' || $payment_mode == 'advance_only'){
                if($ledger_account == '' || $ledger_account == 0){
                    $this->session->set_flashdata('error', 'Payment Ledger is required. Please select a ledger account.');
                    redirect($_SERVER['HTTP_REFERER']);

                }
            }

            if($bank_charges == '') {
                $bank_charges = 0;
            }

            $date_fmt = $this->input->post('date'); 
            $formattedDate = DateTime::createFromFormat('d/m/Y H:i', $date_fmt);

            if ($formattedDate) {
                $date = $formattedDate->format('Y-m-d');
            } else {
                echo 'Invalid date format!';
                $date = null; // Handle invalid input as needed
            }
            
            // Handle Advance Only payment mode
            if ($payment_mode == 'advance_only') {
                // Validate supplier advance ledger is configured
                $settings = $this->Settings;
                $supplier_advance_ledger = isset($settings->supplier_advance_ledger) && !empty($settings->supplier_advance_ledger) 
                                         ? $settings->supplier_advance_ledger 
                                         : null;
                
                if (!$supplier_advance_ledger) {
                    $this->session->set_flashdata('error', 'Cannot process advance payment. Supplier Advance Ledger is not configured in system settings.');
                    redirect($_SERVER['HTTP_REFERER']);
                }
                
                if ($payment_total > 0) {
                    // Create payment reference using supplier advance ledger
                    $payment_id = $this->add_supplier_reference($payment_total, $reference_no, $date, $note . ' (Advance Only)', $supplier_id, $bank_charges, $bank_charges_account, $supplier_advance_ledger);
                    
                    if (!$payment_id) {
                        $this->session->set_flashdata('error', 'Failed to create advance payment reference. Please check system configuration.');
                        redirect($_SERVER['HTTP_REFERER']);
                    }
                    
                    // Make advance payment - do NOT settle any invoices
                    $this->make_supplier_advance_payment($supplier_id, $payment_total, $reference_no, $date, $note, $payment_id);
                    
                    // Create journal entry for advance payment
                    // We need both: the transfer ledger (cash out) and advance ledger (advance in)
                    $journal_id = $this->create_supplier_advance_journal_entry($supplier_id, $ledger_account, $supplier_advance_ledger, $bank_charges_account, $payment_total, $bank_charges, $reference_no, $date);
                    $this->purchases_model->update_payment_reference($payment_id, $journal_id);
                    
                    $this->session->set_flashdata('message', lang('Advance payment added successfully!'));
                    admin_redirect('suppliers/view_payment/'.$payment_id);
                } else {
                    $this->session->set_flashdata('error', 'Please enter a payment amount.');
                    redirect($_SERVER['HTTP_REFERER']);
                }
            }
            
            if(!$payments_array || sizeOf($payments_array) == 0){
                // Server-side validation for advance payments (no invoices scenario)
                // Get supplier advance ledger from settings
                $settings = $this->Settings;
                $supplier_advance_ledger = isset($settings->supplier_advance_ledger) && !empty($settings->supplier_advance_ledger) 
                                         ? $settings->supplier_advance_ledger 
                                         : null;
                
                if (!$supplier_advance_ledger && $payment_total > 0) {
                    $this->session->set_flashdata('error', 'Cannot process advance payment. Supplier Advance Ledger is not configured in system settings.');
                    redirect($_SERVER['HTTP_REFERER']);
                }
                
                if($payment_total > 0 && $supplier_advance_ledger){
                    // Create payment reference using supplier advance ledger (NOT regular ledger_account)
                    $payment_id = $this->add_supplier_reference($payment_total, $reference_no, $date, $note . ' (Pure Advance)', $supplier_id, $bank_charges, $bank_charges_account, $supplier_advance_ledger);
                    
                    // Verify payment reference was created successfully
                    if (!$payment_id) {
                        $this->session->set_flashdata('error', 'Failed to create pure advance payment reference. Please check system configuration.');
                        redirect($_SERVER['HTTP_REFERER']);
                    }
                    
                    // Use advance payment method since there are no invoices - this is pure advance
                    $this->make_supplier_advance_payment($supplier_id, $payment_total, $reference_no, $date, $note, $payment_id);
                    
                    // Create journal entry using supplier advance ledger (NOT regular payment ledger)
                    $journal_id = $this->convert_supplier_payment_multiple_invoice($supplier_id, $supplier_advance_ledger, $bank_charges_account, $payment_total, $bank_charges, $reference_no, 'supplieradvance', $date);
                    $this->purchases_model->update_payment_reference($payment_id, $journal_id);
                    $this->session->set_flashdata('message', lang('Pure advance payment added Successfully!'));
                    admin_redirect('suppliers/view_payment/'.$payment_id);
                }
            }
            
        } else {
            // Check if supplier_advance_ledger is configured in settings
            $settings = $this->Settings;
           
            $supplier_advance_ledger = isset($settings->supplier_advance_ledger) && !empty($settings->supplier_advance_ledger) 
                                     ? $settings->supplier_advance_ledger 
                                     : null;
            
            // Only show child-level suppliers (level 2) in the payment dropdown
            $this->data['suppliers']  = $this->site->getAllChildCompanies('supplier');
            $this->data['warehouses'] = $this->site->getAllWarehouses();
            $this->data['supplier_advance_ledger'] = $supplier_advance_ledger;
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

    public function get_supplier_advance_balance(){
        try {
            $supplier_id = isset($_GET['supplier_id']) ? $_GET['supplier_id'] : null;
            
            if (!$supplier_id) {
                echo json_encode(array(
                    'advance_balance' => 0,
                    'advance_ledger_configured' => false,
                    'error' => 'No supplier ID provided'
                ));
                return;
            }
            
            // Get supplier advance ledger from settings
            $settings = $this->Settings;
            $supplier_advance_ledger = isset($settings->supplier_advance_ledger) && !empty($settings->supplier_advance_ledger) 
                                     ? $settings->supplier_advance_ledger 
                                     : null;
            
            $advance_balance = 0;
            
            if($supplier_advance_ledger && $supplier_id) {
                // Query for advance balance using supplier_id field
                $this->db->select('
                    COALESCE(SUM(CASE WHEN ei.dc = "C" THEN ei.amount ELSE 0 END), 0) as credit_total,
                    COALESCE(SUM(CASE WHEN ei.dc = "D" THEN ei.amount ELSE 0 END), 0) as debit_total
                ');
                $this->db->from('sma_accounts_entryitems ei');
                $this->db->join('sma_accounts_entries e', 'e.id = ei.entry_id', 'inner');
                $this->db->where('ei.ledger_id', $supplier_advance_ledger);
                $this->db->where('e.supplier_id', $supplier_id);
                
                // Check if deleted column exists before filtering
                if ($this->db->field_exists('deleted', 'sma_accounts_entries')) {
                    $this->db->where('e.deleted', 0);
                }
                
                $query = $this->db->get();
                
                if($query->num_rows() > 0) {
                    $result = $query->row();
                    // Advance balance = Credits (advances received) - Debits (advances used/settled)
                    $advance_balance = $result->debit_total - $result->credit_total;
                }
            }
            
            $data = array(
                'advance_balance' => $advance_balance,
                'advance_ledger_configured' => $supplier_advance_ledger ? true : false,
                'supplier_id' => $supplier_id,
                'advance_ledger' => $supplier_advance_ledger
            );
            
            echo json_encode($data);
            
        } catch (Exception $e) {
            // Log the full error for debugging
            log_message('error', 'Advance Balance Error: ' . $e->getMessage());
            log_message('error', 'SQL Error: ' . $this->db->last_query());
            
            echo json_encode(array(
                'advance_balance' => 0,
                'advance_ledger_configured' => false,
                'error' => 'Database error: ' . $e->getMessage(),
                'query' => $this->db->last_query()
            ));
        }
    }

    private function getSupplierAdvanceBalance($supplier_id, $supplier_advance_ledger) {
        if(!$supplier_advance_ledger || !$supplier_id) {
            return 0;
        }
        
        // Query for advance balance using supplier_id field
        $this->db->select('
            COALESCE(SUM(CASE WHEN ei.dc = "C" THEN ei.amount ELSE 0 END), 0) as credit_total,
            COALESCE(SUM(CASE WHEN ei.dc = "D" THEN ei.amount ELSE 0 END), 0) as debit_total
        ');
        $this->db->from('sma_accounts_entryitems ei');
        $this->db->join('sma_accounts_entries e', 'e.id = ei.entry_id', 'inner');
        $this->db->where('ei.ledger_id', $supplier_advance_ledger);
        $this->db->where('e.supplier_id', $supplier_id);
        
        // Check if deleted column exists before filtering
        if ($this->db->field_exists('deleted', 'sma_accounts_entries')) {
            $this->db->where('e.deleted', 0);
        }
        
        $query = $this->db->get();
        
        if($query->num_rows() > 0) {
            $result = $query->row();
            return $result->debit_total - $result->credit_total;
        }
        
        return 0;
    }

    private function create_advance_settlement_entry($supplier_id, $supplier_advance_ledger, $advance_amount, $reference_no, $date, $description) {
        // Get supplier details
        $this->load->admin_model('companies_model');
        $supplier = $this->companies_model->getCompanyByID($supplier_id);
        
        // Create journal entry for advance settlement
        $entry = array(
            'entrytype_id' => 4,
            'transaction_type' => 'advancesettlement',
            'number' => $reference_no,
            'date' => $date,
            'dr_total' => $advance_amount,
            'cr_total' => $advance_amount,
            'notes' => $description . ' for ' . $supplier->name,
            'supplier_id' => $supplier_id
        );
        
        $add = $this->db->insert('sma_accounts_entries', $entry);
        $entry_id = $this->db->insert_id();
        
        if ($add) {
            // Debit advance ledger (reducing advance balance)
            $entryitem1 = array(
                'entry_id' => $entry_id,
                'ledger_id' => $supplier_advance_ledger,
                'amount' => $advance_amount,
                'dc' => 'C',
                'reconciliation_date' => $date
            );
            $this->db->insert('sma_accounts_entryitems', $entryitem1);
            
            // Credit supplier ledger (reducing supplier payable)
            $entryitem2 = array(
                'entry_id' => $entry_id,
                'ledger_id' => $supplier->ledger_account,
                'amount' => $advance_amount,
                'dc' => 'D',
                'reconciliation_date' => $date
            );
            $this->db->insert('sma_accounts_entryitems', $entryitem2);
            
            return $entry_id;
        }
        
        return false;
    }

    public function add()
    {
        $parent_code = null;
        //$this->sma->checkPermissions(false, true);

        $this->form_validation->set_rules('email', $this->lang->line('email_address'), 'is_unique[companies.email]');

        if ($this->form_validation->run('companies/add') == true) {
              
            $level_na = $this->input->post('level_na') == 'on' ? true : false;
            
            if($level_na){
                $level = '2'; // Default to parent if N/A is checked
            }else{
                $level = $this->input->post('level');
            }

            //echo 'Level: '.$level;exit;
            //echo 'Level: '.$level;exit;
            // If level is 2 (child), get the parent company's sequence_code
            if ($level == '2' && !$level_na) {
                $parent_id = $this->input->post('parent_id');
                if ($parent_id) {
                    $parent_company = $this->companies_model->getCompanyByID($parent_id);
                    if ($parent_company) {
                        $parent_code = $parent_company->sequence_code;
                    }
                }
            }else{
                $parent_code = null;
            }

            $data = [
                'name'                => $this->input->post('name') ?? '',
                'name_ar'             => $this->input->post('name_ar') ?? '', 
                'category'            => $this->input->post('category') ?? '',
                'email'               => $this->input->post('email') ?? '',
                'group_id'            => '4',
                'group_name'          => 'supplier',
                'company'             => $this->input->post('company') ?? '',
                'address'             => $this->input->post('address') ?? '',
                'vat_no'              => $this->input->post('vat_no') ?? '',
                'cr'                  => $this->input->post('cr') ?? '',
                'cr_expiration'       => $this->input->post('cr_expiration') ?? '',
                'gln'                 => $this->input->post('gln') ?? '',
                'short_address'       => $this->input->post('short_address') ?? '',
                'building_number'     => $this->input->post('building_number') ?? '',
                'city'                => $this->input->post('city') ?? '',
                'state'               => $this->input->post('state') ?? '',
                'postal_code'         => $this->input->post('postal_code') ?? '',
                'country'             => $this->input->post('country') ?? '',
                'phone'               => $this->input->post('phone') ?? '',
                'contact_name'        => $this->input->post('contact_name') ?? '',
                'contact_number'      => $this->input->post('contact_number') ?? '',
                'ledger_account'      => $this->input->post('ledger_account') ?? 0,
                'payment_term'        => $this->input->post('payment_term'),
                'credit_limit'        => $this->input->post('credit_limit') ? $this->input->post('credit_limit') : '0',
                'balance'             => $this->input->post('balance') ?? 0,
                'note'                => $this->input->post('note') ?? '',
                'level'               => $level,
                'parent_code'         => $parent_code,
                'sequence_code'       => $this->sequenceCode->generate('SUP', 5)
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
            $this->data['parent_suppliers'] = $this->companies_model->getAllParentSuppliers();
        
            $this->load->view($this->theme . 'suppliers/add', $this->data);
        }
    }

    public function add_user($company_id = null)
    {
        //$this->sma->checkPermissions(false, true);

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
        //$this->sma->checkPermissions(null, true);

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
        //$this->sma->checkPermissions(false, true);

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }

        $this->form_validation->set_rules('name', $this->lang->line('name'), 'required');
        $this->form_validation->set_rules('name_ar', $this->lang->line('name_ar'), 'required');
        $this->form_validation->set_rules('address', $this->lang->line('address'), 'required');

        $company_details = $this->companies_model->getCompanyByID($id);
        //echo '<pre>';  print_r($company_details); exit;
        if ($this->input->post('email') != $company_details->email) {
            //$this->form_validation->set_rules('code', lang('email_address'), 'is_unique[companies.email]');
        }

        if ($this->form_validation->run() == true) {
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
                'gln'         => $this->input->post('gln'),
                'cr'          => $this->input->post('cr'),
                'short_address' => $this->input->post('short_address'),
                'building_number' => $this->input->post('building_number'),
                'unit_number' => $this->input->post('unit_number'),
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
        $this->sma->send_json([['id' => $row->id, 'text' => $row->name]]);
    }

    public function getChildSuppliers(){
        //$this->sma->checkPermissions('index');

        $action = "<div class=\"text-center\">";

        if($this->Owner || $this->Admin || $this->GP['suppliers-edit']){
            $actions .= "<a class=\"tip\" title='" . $this->lang->line('edit_supplier') . "' href='" . admin_url('suppliers/edit/$1') . "' data-toggle='modal' data-target='#myModal'><i class=\"fa fa-edit\"></i></a>";
        }

        if($this->Owner || $this->Admin || $this->GP['suppliers-delete']){
            $actions .= "<a href='#' class='tip po' title='<b>" . $this->lang->line('delete_supplier') . "</b>' data-content=\"<p>" . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . admin_url('suppliers/delete/$1') . "'>" . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i></a>";
        }
        $action .= "</div>";

        $this->load->library('datatables');
        $this->datatables
            ->select('id, sequence_code, name, vat_no, gln, cr, short_address, address, credit_limit, payment_term, category')
            ->from('companies')
            ->where('group_name', 'supplier')
            ->where('level', 2)
            ->add_column('Actions', $actions, 'id');
        //->unset_column('id');
        echo $this->datatables->generate();
    }

    public function getSuppliers()
    {
        //$this->sma->checkPermissions('index');

        $this->load->library('datatables');
        $this->datatables
            ->select('id, company, sequence_code, name, email, phone, city, country, vat_no, gst_no')
            ->from('companies')
            ->where('group_name', 'supplier')
            ->add_column('Actions', "<div class=\"text-center\"><a class=\"tip\" title='" . $this->lang->line('list_products') . "' href='" . admin_url('products?supplier=$1') . "'><i class=\"fa fa-list\"></i></a> <a class=\"tip\" title='" . $this->lang->line('list_users') . "' href='" . admin_url('suppliers/users/$1') . "' data-toggle='modal' data-target='#myModal'><i class=\"fa fa-users\"></i></a> <a class=\"tip\" title='" . $this->lang->line('add_user') . "' href='" . admin_url('suppliers/add_user/$1') . "' data-toggle='modal' data-target='#myModal'><i class=\"fa fa-plus-circle\"></i></a> <a class=\"tip\" title='" . $this->lang->line('edit_supplier') . "' href='" . admin_url('suppliers/edit/$1') . "' data-toggle='modal' data-target='#myModal'><i class=\"fa fa-edit\"></i></a> <a href='#' class='tip po' title='<b>" . $this->lang->line('delete_supplier') . "</b>' data-content=\"<p>" . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . admin_url('suppliers/delete/$1') . "'>" . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i></a></div>", 'id');
        //->unset_column('id');
        echo $this->datatables->generate();
    }

    private function _get_companies_table_fields()
    {
        return [
            'external_id' => 'Customer No',
            'name' => 'Customer Name',
            'company' => 'C R Name',
            'cr' => 'C R Number',
            'cr_expiration' => 'C R Expiration',
            'vat_no' => 'VAT No',
            'gln' => 'GLN No',
            'sfda_certificate' => 'SFDA Certificate',
            'short_address' => 'Short Address',
            'city' => 'City',
            'unit_number' => 'Unit Number',
            'building_number' => 'Building Number',
            'postal_code' => 'Postal Code',
            'additional_number' => 'Additional Number',
            'contact_name' => 'Contact Name',
            'contact_number' => 'Contact Number',
            'credit_limit' => 'Credit Limit',
            'payment_term' => 'Credit Period',
            'cf1' => 'Salesman Name',
            'promessory_note_amount' => 'Promissory Note Amount',
            'balance' => 'Customer Balance',
            'note' => 'Note',
            'sales_agent' => 'Sales Agent Name',
            'category' => 'Classification',
            'email' => 'Email'
        ];
    }

    public function process_import()
    {
        $mapping  = $this->input->post('mapping');    // mapping array: file column index => db field
        $filePath = $this->input->post('file_path');  // uploaded Excel file path

        $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
        $reader->setReadDataOnly(true);
        $spreadsheet = $reader->load($filePath);
        $sheet = $spreadsheet->getActiveSheet();

        $imported = 0;
        $errors   = 0;
        $i = 0;

        // ✅ Read all rows as an array (handles weird Excel formats properly)
        $rows = $sheet->toArray(null, true, true, false);
        //echo '<pre>';print_r($rows);exit;
        foreach ($rows as $rowIndex => $columns) {
            $i++;

            // Skip header row (first row)
            if ($rowIndex == 0) continue;

            // Convert associative row to numeric array
            $rowData = array_values($columns);
            //echo '<pre>';print_r($rowData);exit;
            // Map Excel columns to DB fields
            $data = [];
            foreach ($mapping as $index => $field) {
                if (!empty($field) && isset($rowData[$index])) {
                    $data[$field] = trim($rowData[$index]);
                }
            }
            //echo '<pre>';print_r($data);exit;
            // Skip invalid rows
            if (empty($data['name'])) {
                continue;
            }

            // Check if supplier already exists
            $exists = $this->db->get_where('companies', [
                'name'       => $data['name'],
                'group_name' => 'supplier'
            ])->row();

            if ($exists) {
                // Update existing record
                $this->db->where('id', $exists->id)->update('companies', $data);
            } else {
                // Generate sequence code like SUP-00001
                $seq_code = 'SUP-' . str_pad($i, 5, '0', STR_PAD_LEFT);

                // Insert new record with defaults
                $data['group_id']      = 4;
                $data['group_name']    = 'supplier';
                $data['country']       = 'Saudi Arabia';
                $data['sequence_code'] = $seq_code;
                $data['level']         = 1;

                $this->db->insert('companies', $data);
                $supplier_id = $this->db->insert_id();

                // This block is to add child supplier
                if($this->Settings->site_name == 'Hills Business Medical'){

                    $data['level']               = 2;
                    $data['parent_code'] = $seq_code;
                    //$parent_code = $seq_code = 'SUP-' . str_pad($i, 5, '0', STR_PAD_LEFT);
                    $i++;
                    $seq_code = 'SUP-' . str_pad($i, 5, '0', STR_PAD_LEFT);
                    $data['sequence_code'] = $seq_code;
                    $this->db->insert('companies', $data);
                }
            }

            if ($this->db->affected_rows() > 0) {
                $imported++;
            } else {
                $errors++;
            }
        }

        // Clean up
        if (file_exists($filePath)) {
            unlink($filePath);
        }

        $this->session->set_flashdata('message', "Imported: {$imported}, Errors: {$errors}");
        redirect(admin_url('suppliers'));
    }


    
    public function import_excel(){
        $this->load->helper('security');
        $this->form_validation->set_rules('excel_file', lang('upload_file'), 'xss_clean');

        if ($this->form_validation->run() == true) {

            $this->load->library('excel');
            if (isset($_FILES['excel_file']) && $_FILES['excel_file']['size'] > 0) {
                $this->load->library('upload');

                $config['upload_path']   = 'files/';
                $config['allowed_types'] = 'xlsx|xls|csv';
                $config['max_size']      = '10000';
                $config['overwrite']     = false;
                $config['encrypt_name']  = true;

                $this->upload->initialize($config);
                $this->upload->initialize($config);

                if (!$this->upload->do_upload('excel_file')) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    admin_redirect('suppliers');
                }

                $upload_data = $this->upload->data();
                $file_path = $upload_data['full_path'];

                $spreadsheet = IOFactory::load($file_path);
                $sheet = $spreadsheet->getActiveSheet();
                $rows = $sheet->toArray(null, true, true, true);
                
                 // Extract headers (first row)
                $headers = array_shift($rows);
               
                // Filter out empty headers
                $headers = array_filter($headers, function($value) {
                    return trim($value) !== '';
                });

                // Optionally reindex headers numerically
                $headers = array_values($headers);

                // Pass to view for mapping
                $this->data['headers']   = $headers;
                $this->data['rows']      = $rows;
                $this->data['file_path'] = $file_path;
                $this->data['db_fields'] = $this->_get_companies_table_fields();

                //$this->load->view($this->theme . 'customers/map_fields', $this->data);

                $this->session->set_userdata('user_csrf', $value);
                $this->data['csrf'] = $this->session->userdata('user_csrf');
                $bc = [['link' => base_url(), 'page' => lang('suppliers_mapper')], ['link' => admin_url('suppliers'), 'page' => lang('suppliers_mapper')], ['link' => '#', 'page' => lang('suppliers_mapper')]];
                $meta = ['page_title' => lang('suppliers_mapper'), 'bc' => $bc];
                $this->page_construct('suppliers/map_fields', $meta, $this->data);
                
            }
        }else{
            $this->data['error']    = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'suppliers/import_excel', $this->data);
        }
    }

    public function import_csv()
    {
        //$this->sma->checkPermissions('add', true);
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
        //$this->sma->checkPermissions();

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

    public function parentsuggestions($term = null, $limit = null)
    {
        // $this->sma->checkPermissions('index');
        if ($this->input->get('term')) {
            $term = $this->input->get('term', true);
        }
        $term            = addslashes($term);
        $limit           = $this->input->get('limit', true);
        $rows['results'] = $this->companies_model->getParentSupplierSuggestions($term, $limit);
        $this->sma->send_json($rows);
    }

    public function getChildById($term = null, $limit = null, $pid = null){
        if ($this->input->get('pid')) {
            $pid = $this->input->get('pid', true);
            $term = $this->input->get('term', true);
        }
        //echo 'Pid: '.$pid.' and term '.$term;exit;
        $term            = addslashes($term);
        //$limit           = $this->input->get('limit', true);
        $rows['results'] = $this->companies_model->getCompaniesByParentId($pid);
        $this->sma->send_json($rows);  
    }

    public function childsuggestions($term = null, $limit = null)
    {
        // $this->sma->checkPermissions('index');
        if ($this->input->get('term')) {
            $term = $this->input->get('term', true);
        }
        $term            = addslashes($term);
        $limit           = $this->input->get('limit', true);
        $rows['results'] = $this->companies_model->getChildSupplierSuggestions($term, $limit);
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
                    //$this->sma->checkPermissions('delete');
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
                    $this->excel->getActiveSheet()->SetCellValue('A1', lang('SEQUENCE CODE'));
                    $this->excel->getActiveSheet()->SetCellValue('B1', lang('NAME'));
                    $this->excel->getActiveSheet()->SetCellValue('C1', lang('VAT NUMBER'));
                    $this->excel->getActiveSheet()->SetCellValue('D1', lang('CR NUMBER'));
                    $this->excel->getActiveSheet()->SetCellValue('E1', lang('GLN NUMBER'));
                    $this->excel->getActiveSheet()->SetCellValue('F1', lang('SHORT ADDRESS'));
                    $this->excel->getActiveSheet()->SetCellValue('G1', lang('ADDRESS'));
                    $this->excel->getActiveSheet()->SetCellValue('H1', lang('CREDIT LIMIT'));
                    $this->excel->getActiveSheet()->SetCellValue('I1', lang('PAYMENT TERM'));
                    $this->excel->getActiveSheet()->SetCellValue('J1', lang('CATEGORY'));

                    $row = 2;
                    foreach ($_POST['val'] as $id) {
                        $customer = $this->site->getCompanyByID($id);
                        $this->excel->getActiveSheet()->SetCellValue('A' . $row, $customer->sequence_code);
                        $this->excel->getActiveSheet()->SetCellValue('B' . $row, $customer->name);
                        
                        // Set these cells as TEXT to prevent scientific notation and preserve leading zeros
                        $this->excel->getActiveSheet()->setCellValueExplicit('C' . $row, $customer->vat_no, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                        $this->excel->getActiveSheet()->setCellValueExplicit('D' . $row, $customer->cr, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                        $this->excel->getActiveSheet()->setCellValueExplicit('E' . $row, $customer->gln, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                        
                        $this->excel->getActiveSheet()->SetCellValue('F' . $row, $customer->short_address);
                        $this->excel->getActiveSheet()->SetCellValue('G' . $row, $customer->address);
                        $this->excel->getActiveSheet()->SetCellValue('H' . $row, $customer->credit_limit);
                        $this->excel->getActiveSheet()->SetCellValue('I' . $row, $customer->payment_term);
                        $this->excel->getActiveSheet()->SetCellValue('J' . $row, $customer->category);
                        
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
        //$this->sma->checkPermissions(false, true);

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
        //$this->sma->checkPermissions('index', true);
        $this->data['error']    = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $this->data['supplier'] = $this->companies_model->getCompanyByID($id);
        $this->load->view($this->theme . 'suppliers/view', $this->data);
    }

    public function list_service_invoice(){
        $bc   = [['link' => base_url(), 'page' => lang('home')], ['link' => '#', 'page' => lang('Supplier Service Invoice')]];
        $meta = ['page_title' => lang('Supplier Service Invoice'), 'bc' => $bc];

        // Build filters
        $filters = [
            'supplier_id' => $this->input->get('supplier_id') ?: $this->input->post('supplier_id'),
            'from_date'   => $this->input->get('from_date')   ?: $this->input->post('from_date'),
            'to_date'     => $this->input->get('to_date')     ?: $this->input->post('to_date'),
        ];
        // Convert display date (d/m/Y) to Y-m-d for queries
        foreach (['from_date', 'to_date'] as $f) {
            if (!empty($filters[$f])) {
                $d = DateTime::createFromFormat('d/m/Y', $filters[$f]);
                if ($d) { $filters[$f] = $d->format('Y-m-d'); }
            }
        }

        $service_invoices = $this->purchases_model->getDebitMemo('serviceinvoice', $filters);
        $suppliers        = $this->site->getAllCompanies('supplier');

        $this->data['service_invoices'] = $service_invoices;
        $this->data['suppliers']        = $suppliers;
        $this->data['filters']          = $filters;

        // Export to Excel
        if ($this->input->get('export_excel')) {
            $this->load->library('excel');
            $sheet = $this->excel->setActiveSheetIndex(0);
            $sheet->setTitle('Service Invoices');

            $sheet->SetCellValue('A1', '#');
            $sheet->SetCellValue('B1', 'Reference No.');
            $sheet->SetCellValue('C1', 'Supplier Code');
            $sheet->SetCellValue('D1', 'Supplier');
            $sheet->SetCellValue('E1', 'Date');
            $sheet->SetCellValue('F1', 'Payment Amount');
            $sheet->SetCellValue('G1', 'VAT Amount');

            $row = 2;
            $count = 1;
            foreach ($service_invoices as $inv) {
                $date_fmt = !empty($inv->date) ? date('d-M-Y', strtotime($inv->date)) : '';
                $sheet->SetCellValue('A' . $row, $count++);
                $sheet->SetCellValue('B' . $row, $inv->reference_no);
                $sheet->SetCellValue('C' . $row, $inv->sequence_code ?? '');
                $sheet->SetCellValue('D' . $row, $inv->company ?? '');
                $sheet->SetCellValue('E' . $row, $date_fmt);
                $sheet->SetCellValue('F' . $row, round((float)$inv->payment_amount, 2));
                $sheet->SetCellValue('G' . $row, round((float)($inv->vat_value ?? 0), 2));
                $row++;
            }

            foreach (range('A', 'G') as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }

            $this->excel->getDefaultStyle()->getAlignment()->setVertical('center');
            $filename = 'Service_Invoices_' . date('Y-m-d_H_i_s');
            $this->load->helper('excel');
            create_excel($this->excel, $filename);
            return;
        }

        $this->page_construct('suppliers/list_service_invoice', $meta, $this->data);
    }

    public function list_petty_cash(){
        $this->data['petty_cash_entries'] = $this->purchases_model->getPettyCash('pettycash');
        $bc    = [['link' => base_url(), 'page' => lang('home')], ['link' => '#', 'page' => lang('Petty Cash List')]];
        $meta = ['page_title' => lang('Petty Cash List'), 'bc' => $bc];
        $this->page_construct('suppliers/list_petty_cash', $meta, $this->data);
    }

    public function edit_petty_cash($id = null){
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }

        $petty_cash_data = $this->purchases_model->getDebitMemoData($id);
        $petty_cash_entries_data = $this->purchases_model->getDebitMemoEntriesData($id);

        $this->data['memo_data'] = $petty_cash_data;
        $this->data['memo_entries_data'] = $petty_cash_entries_data;
        $this->data['suppliers']  = $this->site->getAllCompanies('supplier');
        $this->data['ledgers'] = $this->site->getCompanyLedgers();

        $bc    = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('suppliers/list_petty_cash'), 'page' => lang('Petty Cash List')], ['link' => '#', 'page' => lang('Edit Petty Cash')]];
        $meta = ['page_title' => lang('Edit Petty Cash'), 'bc' => $bc];
        $this->page_construct('suppliers/petty_cash', $meta, $this->data);
    }

    public function edit_service_invoice($id = null){
        //$this->sma->checkPermissions(false, true);

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

    public function petty_cash(){
        //$this->sma->checkPermissions(false, true);
        $this->form_validation->set_rules('reference_no', $this->lang->line('reference_no'), 'required');
        $this->form_validation->set_rules('date', $this->lang->line('date'), 'required');
        

        $data = [];
        $bc    = [['link' => base_url(), 'page' => lang('home')], ['link' => '#', 'page' => lang('Petty Cash')]];
        $meta = ['page_title' => lang('Petty Cash'), 'bc' => $bc];
        if ($this->form_validation->run() == true) {
            $request_type = $this->input->post('request_type');
            $supplier_names = $this->input->post('supplier_name[]');
            $main_supplier_id = $this->input->post('main_supplier_id');
            $reference_no = $this->input->post('reference_no');
            $description = $this->input->post('note');
            $date_fmt = $this->input->post('date');
            $amounts = $this->input->post('amount[]');
            $vats = $this->input->post('vat[]');
            $totals = $this->input->post('total[]');
            $ledger_accounts = $this->input->post('ledger_account[]');
            $vat_numbers = $this->input->post('vat_number[]');
            $descriptions = $this->input->post('description[]');
            $invoice_nos = $this->input->post('invoice_no[]');
            $vat_rates = $this->input->post('vat_rate[]');

            if(empty($main_supplier_id) || $main_supplier_id == 0 || $main_supplier_id == '' || $main_supplier_id == null){
                $this->session->set_flashdata('error', 'Please select a main supplier before submitting petty cash.');
                admin_redirect('suppliers/petty_cash');
                return;
            }

            $rowCount = max(
                count((array) $supplier_names),
                count((array) $invoice_nos),
                count((array) $amounts),
                count((array) $vat_rates),
                count((array) $vats),
                count((array) $totals),
                count((array) $vat_numbers),
                count((array) $descriptions)
            );

            for ($index = 0; $index < $rowCount; $index++) {
                $supplierNameValue = trim((string)($supplier_names[$index] ?? ''));
                $invoiceNoValue = trim((string)($invoice_nos[$index] ?? ''));
                $amountValue = trim((string)($amounts[$index] ?? ''));
                $vatRateValue = trim((string)($vat_rates[$index] ?? ''));
                $vatValue = trim((string)($vats[$index] ?? ''));
                $totalValue = trim((string)($totals[$index] ?? ''));
                $vatNumberValue = trim((string)($vat_numbers[$index] ?? ''));
                $descriptionValue = trim((string)($descriptions[$index] ?? ''));

                $isRowSubmitted = (
                    $supplierNameValue !== '' ||
                    $invoiceNoValue !== '' ||
                    $amountValue !== '' ||
                    $vatRateValue !== '' ||
                    $vatValue !== '' ||
                    $totalValue !== '' ||
                    $vatNumberValue !== '' ||
                    $descriptionValue !== ''
                );

                if ($isRowSubmitted) {
                    $ledgerAccountValue = trim((string)($ledger_accounts[$index] ?? ''));
                    if ($ledgerAccountValue === '' || $ledgerAccountValue === '0') {
                        $this->session->set_flashdata('error', 'Please select ledger account for all rows before submitting petty cash.');
                        admin_redirect('suppliers/petty_cash');
                        return;
                    }
                }
            }


            $formattedDate = DateTime::createFromFormat('Y-m-d', $date_fmt);
            $isDateValid = $formattedDate && $formattedDate->format('Y-m-d') === $date_fmt;

            if($isDateValid){
                $date = $date_fmt;
            }else{
                $formattedDate = DateTime::createFromFormat('d/m/Y', $date_fmt);
                $date = $formattedDate->format('Y-m-d');
            }

            $payment_total = 0;
            $vat_charges = 0;
            $petty_cash_data = [];

            if (!empty($totals)) {
                foreach ($totals as $total) {
                    $payment_total += (float)$total;
                }
            }

            if (!empty($vats)) {
                foreach ($vats as $vat) {
                    $vat_charges += (float)$vat;
                }
            }

            // Prepare service data for storage
            if (!empty($ledger_accounts)) {
                foreach ($ledger_accounts as $index => $ledger_account) {
                    $petty_cash_data[] = [
                        'supplier_name' => $supplier_names[$index] ?? '',
                        'invoice_no' => $invoice_nos[$index] ?? '',
                        'ledger_account' => $ledger_account,
                        'amount' => (float)($amounts[$index] ?? 0),
                        'vat_rate' => (float)($vat_rates[$index] ?? 15),
                        'vat' => (float)($vats[$index] ?? 0),
                        'total' => (float)($totals[$index] ?? 0),
                        'vat_number' => $vat_numbers[$index] ?? '',
                        'description' => $descriptions[$index] ?? ''
                    ];
                }
            }

            if($payment_total > 0){

                if($request_type == 'update'){
                    $memo_id2 = $this->input->post('memo_id');
                   
                    // Delete older data
                    $this->db->delete('sma_memo_entries', ['memo_id' => $memo_id2]);
                    $this->db->delete('sma_memo', ['id' => $memo_id2]);
                    $this->deleteFromAccounting($memo_id2);
                }

                // Use main supplier from form for memo
                $main_supplier_id = $main_supplier_id ?: 0;
                $supplier_details = $this->companies_model->getCompanyByID($main_supplier_id);
                $supplier_ledger_account = $supplier_details ? $supplier_details->ledger_account : 0; // Default to supplier ledger for service invoice
                $vat_account = $this->Settings->vat_on_expense_ledger; // Default VAT account

                $memoData = array(
                    'supplier_id' => $main_supplier_id,
                    'customer_id' => 0,
                    'reference_no' => $reference_no,
                    'payment_amount' => $payment_total,
                    'vat_value' => $vat_charges,
                    'ledger_account' => $supplier_ledger_account,
                    'vat_account' => $vat_account,
                    'type' => 'pettycash',
                    'date' => $date,
                    'description' => $description,
                    'sequence_code' => $this->sequenceCode->generate('PCI', 5)
                );
                //echo '<pre>';print_r($memoData);exit;
                $this->db->insert('sma_memo' ,$memoData);
                $memo_id = $this->db->insert_id();
                //$memo_id = 0;
                $memoEntryData = [];
                if (!empty($petty_cash_data)) {
                    foreach ($petty_cash_data as $index => $petty_cash_data_row) {

                        $memoEntryData[] = [
                            'memo_id' => $memo_id,
                            'name' => $petty_cash_data_row['supplier_name'],
                            'reference_no' => $petty_cash_data_row['invoice_no'],
                            'description' => $petty_cash_data_row['description'] ?: $description,
                            'payment_amount' => (float)($petty_cash_data_row['total'] ?? 0),
                            'type' => 'pettycash',
                            'date' => $date,
                            //'vat_rate' => (float)($petty_cash_data_row['vat_rate'] ?? 15),
                            'vat' => (float)($petty_cash_data_row['vat'] ?? 0),
                            'ledger_account' => $petty_cash_data_row['ledger_account'] ?? '',
                            'vat_number' => $petty_cash_data_row['vat_number'] ?? ''
                        ];
                    }
                }
                
                $this->add_petty_cash($memoEntryData);
            }

            $this->convert_petty_cash($memo_id, $main_supplier_id, $vat_account, $payment_total, $vat_charges, $reference_no, 'pettycash', $date, $memoEntryData);
            $this->session->set_flashdata('message', lang($request_type == 'update' ? 'Petty Cash updated Successfully!' : 'Petty Cash added Successfully!'));
            admin_redirect('suppliers/list_petty_cash');

        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['suppliers']  = $this->site->getAllCompanies('supplier');
            $this->data['ledgers'] = $this->site->getCompanyLedgers();
            $this->page_construct('suppliers/petty_cash', $meta, $this->data);
        }
    }

    public function service_invoice(){
        //$this->sma->checkPermissions(false, true);
        $this->form_validation->set_rules('supplier', $this->lang->line('supplier'), 'required');
        $this->form_validation->set_rules('reference_no', $this->lang->line('reference_no'), 'required');
        $this->form_validation->set_rules('date', $this->lang->line('date'), 'required');
        

        $data = [];
        $bc    = [['link' => base_url(), 'page' => lang('home')], ['link' => '#', 'page' => lang('Service Invoice')]];
        $meta = ['page_title' => lang('Service Invoice'), 'bc' => $bc];
        if ($this->form_validation->run() == true) {
            $supplier_id = $this->input->post('supplier');
            $reference_no = $this->input->post('reference_no');
            $description = $this->input->post('description');
            $date_fmt = $this->input->post('date');
            $amounts = $this->input->post('amount[]');
            $vat_rates = $this->input->post('vat_rate[]');
            $vat_amounts = $this->input->post('vat_amount[]');
            $totals = $this->input->post('total[]');
            $ledger_accounts = $this->input->post('ledger_account[]');

            $rowCount = max(
                count((array) $amounts),
                count((array) $vat_rates),
                count((array) $vat_amounts),
                count((array) $totals)
            );

            for ($index = 0; $index < $rowCount; $index++) {
                $amountValue = trim((string)($amounts[$index] ?? ''));
                $vatRateValue = trim((string)($vat_rates[$index] ?? ''));
                $vatAmountValue = trim((string)($vat_amounts[$index] ?? ''));
                $totalValue = trim((string)($totals[$index] ?? ''));
                $isRowSubmitted = ($amountValue !== '' || $vatRateValue !== '' || $vatAmountValue !== '' || $totalValue !== '');

                if ($isRowSubmitted) {
                    $ledgerAccountValue = trim((string)($ledger_accounts[$index] ?? ''));
                    if ($ledgerAccountValue === '' || $ledgerAccountValue === '0') {
                        $this->session->set_flashdata('error', 'Please select ledger account for all rows before submitting service invoice.');
                        admin_redirect('suppliers/service_invoice');
                        return;
                    }
                }
            }

            $formattedDate = DateTime::createFromFormat('Y-m-d', $date_fmt);
            $isDateValid = $formattedDate && $formattedDate->format('Y-m-d') === $date_fmt;

            if($isDateValid){
                $date = $date_fmt;
            }else{
                $formattedDate = DateTime::createFromFormat('d/m/Y', $date_fmt);
                $date = $formattedDate->format('Y-m-d');
            }

            $payment_total = 0;
            $vat_charges = 0;
            $service_data = [];

            if (!empty($totals)) {
                foreach ($totals as $total) {
                    $payment_total += (float)$total;
                }
            }

            if (!empty($vat_amounts)) {
                foreach ($vat_amounts as $vat_amount) {
                    $vat_charges += (float)$vat_amount;
                }
            }

            // Prepare service data for storage
            if (!empty($ledger_accounts)) {
                foreach ($ledger_accounts as $index => $ledger_account) {
                    $service_data[] = [
                        'ledger_account' => $ledger_account,
                        'amount' => (float)($amounts[$index] ?? 0),
                        'vat_rate' => (float)($vat_rates[$index] ?? 15),
                        'vat_amount' => (float)($vat_amounts[$index] ?? 0),
                        'total' => (float)($totals[$index] ?? 0)
                    ];
                }
            }

            if($payment_total > 0){

                $request_type = $this->input->post('request_type');
                if($request_type == 'update'){
                    $memo_id2 = $this->input->post('memo_id');
                   
                    // Delete older data
                    $this->db->delete('sma_memo_entries', ['memo_id' => $memo_id2]);
                    $this->db->delete('sma_memo', ['id' => $memo_id2]);
                    $this->deleteFromAccounting($memo_id2);
                }

                $supplier_details = $this->companies_model->getCompanyByID($supplier_id);
                $supplier_ledger_account = $supplier_details->ledger_account; // Default to supplier ledger for service invoice
                $vat_account = $this->Settings->vat_on_expense_ledger; // Default VAT account

                $memoData = array(
                    'supplier_id' => $supplier_id,
                    'customer_id' => 0,
                    'reference_no' => $reference_no,
                    'payment_amount' => $payment_total,
                    'vat_value' => $vat_charges,
                    'ledger_account' => $supplier_ledger_account,
                    'vat_account' => $vat_account,
                    'type' => 'serviceinvoice',
                    'date' => $date,
                    'description' => $description,
                    'sequence_code' => $this->sequenceCode->generate('SSI', 5)
                );
                //echo '<pre>';print_r($memoData);exit;
                $this->db->insert('sma_memo' ,$memoData);
                $memo_id = $this->db->insert_id();
                $memoEntryData = [];
                if (!empty($service_data)) {
                    foreach ($service_data as $index => $service_data_row) {

                        $memoEntryData[] = [
                            'memo_id' => $memo_id,
                            'supplier_id' => $supplier_id,
                            'reference_no' => $reference_no,
                            'description' => $description,
                            'payment_amount' => (float)($service_data_row['total'] ?? 0),
                            'type' => 'serviceinvoice',
                            'date' => $date,
                            //'vat_rate' => (float)($service_data_row['vat_rate'] ?? 15),
                            'vat' => (float)($service_data_row['vat_amount'] ?? 0),
                            'ledger_account' => $service_data_row['ledger_account'] ?? ''
                        ];
                    }
                }

                $this->add_service_invoice($memoEntryData);
                //$this->add_service_invoice($memo_id, $supplier_id, $reference_no, $description, $payment_total, $date);
            }
            $this->convert_service_invoice($memo_id, $supplier_id, $vat_account, $payment_total, $vat_charges, $reference_no, 'serviceinvoice', $date, $memoEntryData);
            $this->session->set_flashdata('message', lang($request_type == 'update' ? 'Service Invoice updated Successfully!' : 'Service Invoice added Successfully!'));
            admin_redirect('suppliers/list_service_invoice');

        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['suppliers']  = $this->site->getAllCompanies('supplier');
            $this->data['ledgers'] = $this->site->getCompanyLedgers();
            $this->page_construct('suppliers/service_invoice', $meta, $this->data);
        }
    }

    public function add_petty_cash($petty_cash_data = array()){
        // Insert memo entries for each petty cash row
        foreach ($petty_cash_data as $entry) {
            $this->db->insert('sma_memo_entries' ,$entry);
        }
    }

    public function add_service_invoice($service_data = array()){
        // Insert memo entries for each service row
        foreach ($service_data as $entry) {
            $this->db->insert('sma_memo_entries' ,$entry);
        }
    }

    public function convert_petty_cash($memo_id, $supplier_id, $vat_account, $payment_amount, $vat_charges, $reference_no, $type, $date, $entry_data){
        $this->load->admin_model('companies_model');
        $supplier = $this->companies_model->getCompanyByID($supplier_id);

        /*Accounts Entries*/
        $entry = array(
            'entrytype_id' => 4,
            'transaction_type' => $type,
            'number'       => 'PCI-'.$reference_no,
            'date'         => date('Y-m-d', strtotime($date)),
            'dr_total'     => $payment_amount,
            'cr_total'     => $payment_amount,
            'notes'        => 'Petty Cash Reference: '.$reference_no,
            'pid'          =>  '',
            'memo_id'      => $memo_id,
            'supplier_id'  => $supplier_id
            );
        $add  = $this->db->insert('sma_accounts_entries', $entry);
        $insert_id = $this->db->insert_id();

        $entryitemdata[] = array(
            'Entryitem' => array(
                'entry_id' => $insert_id,
                'dc' => 'C',
                'ledger_id' => $supplier->ledger_account,
                'amount' => $payment_amount,
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
                'narration' => 'Vat Charges'
            )
        );

        foreach ($entry_data as $entry) {
            $entryitemdata[] = array(
                'Entryitem' => array(
                    'entry_id' => $insert_id,
                    'dc' => 'D',
                    'ledger_id' => $entry['ledger_account'],
                    'amount' => $entry['payment_amount'] - $entry['vat'],
                    'narration' => ''
                )
            );
        }

        foreach ($entryitemdata as $row => $itemdata)
        {
            $this->db->insert('sma_accounts_entryitems' ,$itemdata['Entryitem']);
        }
    }

    public function convert_service_invoice($memo_id, $supplier_id, $vat_account, $payment_amount, $vat_charges, $reference_no, $type, $date, $entry_data){
        $this->load->admin_model('companies_model');
        $supplier = $this->companies_model->getCompanyByID($supplier_id);

        /*Accounts Entries*/
        $entry = array(
            'entrytype_id' => 4,
            'transaction_type' => $type,
            'number'       => 'SI-'.$reference_no,
            'date'         => date('Y-m-d', strtotime($date)),
            'dr_total'     => $payment_amount,
            'cr_total'     => $payment_amount,
            'notes'        => 'Service Invoice Reference: '.$reference_no,
            'pid'          =>  '',
            'memo_id'      => $memo_id,
            'supplier_id'  => $supplier_id
            );
        $add  = $this->db->insert('sma_accounts_entries', $entry);
        $insert_id = $this->db->insert_id();

        //Supplier
        $entryitemdata[] = array(
            'Entryitem' => array(
                'entry_id' => $insert_id,
                'dc' => 'C',
                'ledger_id' => $supplier->ledger_account,
                'amount' => $payment_amount,
                'narration' => 'Supplier'
            )
        );

        //vat charges
        $entryitemdata[] = array(
            'Entryitem' => array(
                'entry_id' => $insert_id,
                'dc' => 'D',
                'ledger_id' => $vat_account,
                'amount' => $vat_charges,
                'narration' => 'Vat Charges'
            )
        );

        foreach ($entry_data as $entry) {
            //transfer legdger
            $entryitemdata[] = array(
                'Entryitem' => array(
                    'entry_id' => $insert_id,
                    'dc' => 'D',
                    'ledger_id' => $entry['ledger_account'],
                    'amount' => $entry['payment_amount'] - $entry['vat'],
                    'narration' => 'Service'
                )
            );
        }

        foreach ($entryitemdata as $row => $itemdata)
        {
            $this->db->insert('sma_accounts_entryitems' ,$itemdata['Entryitem']);
        }
    }

    public function service_invoice_pdf($id)
    {
        $service_invoice_data = $this->purchases_model->getDebitMemoData($id);
        $service_invoice_entries_data = $this->purchases_model->getDebitMemoEntriesData($id);

        if (!$service_invoice_data) {
            $this->session->set_flashdata('error', 'Service invoice not found');
            redirect($_SERVER['HTTP_REFERER']);
        }

        // Get supplier details
        $supplier = $this->companies_model->getCompanyByID($service_invoice_data->supplier_id);
        $this->data['supplier'] = $supplier;

        // Get biller details for logo
        $this->data['biller'] = $this->site->getDefaultBiller();

        $this->data['service_invoice'] = $service_invoice_data;
        $service_invoice_entries_data = $this->purchases_model->getDebitMemoEntriesData($id);

        // Add ledger names to entries
        foreach ($service_invoice_entries_data as $entry) {
            $ledger = $this->site->getLedgerByID($entry->ledger_account);
            $entry->ledger_name = $ledger ? $ledger->name : 'Unknown Ledger';
        }

        $this->data['service_invoice_entries'] = $service_invoice_entries_data;

        // Get the related ledger entry
        $this->db->select('*');
        $this->db->from('sma_accounts_entries');
        $this->db->where('memo_id', $id);
        $this->db->where('transaction_type', 'serviceinvoice');
        $ledger_entry = $this->db->get()->row();

        if ($ledger_entry) {
            $this->db->select('*');
            $this->db->from('sma_accounts_entryitems');
            $this->db->where('entry_id', $ledger_entry->id);
            $ledger_entryitems = $this->db->get()->result();

            // Add ledger names to entryitems
            foreach ($ledger_entryitems as $item) {
                $ledger = $this->site->getLedgerByID($item->ledger_id);
                $item->ledger_name = $ledger ? $ledger->name : 'Unknown Ledger';
            }

            $this->data['ledger_entry'] = $ledger_entry;
            $this->data['ledger_entryitems'] = $ledger_entryitems;
        }
        
        // Generate QR code for service invoice (similar to sales)
        if ($this->Settings->ksa_qrcode) {
            $biller = $this->data['biller'];
            $payload = [
                'seller' => $biller->company && $biller->company != '-' ? $biller->company : $biller->name,
                'vat_no' => $biller->vat_no ?: $biller->get_no,
                'date' => $service_invoice_data->date,
                'grand_total' => $service_invoice_data->payment_amount,
                'total_tax_amount' => $service_invoice_data->vat_value,
            ];

            // Convert to JSON directly
            $qrtext = json_encode($payload);
            $qr_code = $this->sma->qrcodepng('text', $qrtext, 2, $level = 'H', $sq = null, $svg = false);
            $this->data['qr_code_base64'] = base64_encode($qr_code);
        } else {
            $qr_code = $this->sma->qrcode('link', urlencode(site_url('view/service_invoice/' . $service_invoice_data->id)), 2);
            $this->data['qr_code_base64'] = base64_encode($qr_code);
        }

        // Generate PDF using mPDF (same as customer statement)
        $name = 'Supplier_Service_Invoice_' . $service_invoice_data->reference_no . '.pdf';
        $html = $this->load->view($this->theme . 'suppliers/service_invoice_pdf', $this->data, true);
        //echo $html;exit;
        try {
            // Use mPDF directly like customer statement
            $mpdf = new Mpdf([
                'format' => 'A4',
                'orientation' => 'P',       // Portrait
                'margin_top' => 10,
                'margin_bottom' => 10,
                'margin_left' => 10,
                'margin_right' => 10,
            ]);

            $mpdf->WriteHTML($html);
            $mpdf->Output($name, "D"); // Force download
        } catch (Exception $e) {
            // If PDF generation fails, show error
            echo "PDF Generation Error: " . $e->getMessage();
            exit;
        }
    }

    public function petty_cash_pdf($id)
    {
        $petty_cash_data = $this->purchases_model->getDebitMemoData($id);
        $petty_cash_entries_data = $this->purchases_model->getDebitMemoEntriesData($id);

        if (!$petty_cash_data) {
            $this->session->set_flashdata('error', 'Petty cash not found');
            redirect($_SERVER['HTTP_REFERER']);
        }

        // Get supplier details
        $supplier = $this->companies_model->getCompanyByID($petty_cash_data->supplier_id);
        $this->data['supplier'] = $supplier;

        // Get biller details for logo
        $this->data['biller'] = $this->site->getDefaultBiller();

        $this->data['petty_cash'] = $petty_cash_data;
        $petty_cash_entries_data = $this->purchases_model->getDebitMemoEntriesData($id);

        // Add ledger names and supplier names to entries
        foreach ($petty_cash_entries_data as $entry) {
            $ledger = $this->site->getLedgerByID($entry->ledger_account);
            $entry->ledger_name = $ledger ? $ledger->name : 'Unknown Ledger';
            
            // Use supplier_name directly if available, otherwise try to get from supplier_id
            if (!empty($entry->supplier_name)) {
                // supplier_name is already set from the database
            } else {
                $supplier_entry = $this->companies_model->getCompanyByID($entry->supplier_id);
                $entry->supplier_name = $supplier_entry ? $supplier_entry->name : 'Unknown Supplier';
            }
        }

        $this->data['petty_cash_entries'] = $petty_cash_entries_data;

        // Get the related ledger entry
        $this->db->select('*');
        $this->db->from('sma_accounts_entries');
        $this->db->where('memo_id', $id);
        $this->db->where('transaction_type', 'pettycash');
        $ledger_entry = $this->db->get()->row();

        if ($ledger_entry) {
            $this->db->select('*');
            $this->db->from('sma_accounts_entryitems');
            $this->db->where('entry_id', $ledger_entry->id);
            $ledger_entryitems = $this->db->get()->result();

            // Add ledger names to entryitems
            foreach ($ledger_entryitems as $item) {
                $ledger = $this->site->getLedgerByID($item->ledger_id);
                $item->ledger_name = $ledger ? $ledger->name : 'Unknown Ledger';
            }

            $this->data['ledger_entry'] = $ledger_entry;
            $this->data['ledger_entryitems'] = $ledger_entryitems;
        }

        // Generate QR code for petty cash (similar to sales)
        if ($this->Settings->ksa_qrcode) {
            $biller = $this->data['biller'];
            $payload = [
                'seller' => $biller->company && $biller->company != '-' ? $biller->company : $biller->name,
                'vat_no' => $biller->vat_no ?: $biller->get_no,
                'date' => $petty_cash_data->date,
                'grand_total' => $petty_cash_data->payment_amount,
                'total_tax_amount' => $petty_cash_data->vat_value,
            ];

            // Convert to JSON directly
            $qrtext = json_encode($payload);
            $qr_code = $this->sma->qrcodepng('text', $qrtext, 2, $level = 'H', $sq = null, $svg = false);
            $this->data['qr_code_base64'] = base64_encode($qr_code);
        } else {
            $qr_code = $this->sma->qrcode('link', urlencode(site_url('view/petty_cash/' . $petty_cash_data->id)), 2);
            $this->data['qr_code_base64'] = base64_encode($qr_code);
        }

        // Generate PDF using mPDF (same as customer statement)
        $name = 'Supplier_Petty_Cash_' . $petty_cash_data->reference_no . '.pdf';
        $html = $this->load->view($this->theme . 'suppliers/petty_cash_pdf', $this->data, true);
        //echo $html;exit;
        try {
            // Use mPDF directly like customer statement
            $mpdf = new Mpdf([
                'format' => 'A4',
                'orientation' => 'P',       // Portrait
                'margin_top' => 10,
                'margin_bottom' => 10,
                'margin_left' => 10,
                'margin_right' => 10,
            ]);

            $mpdf->WriteHTML($html);
            $mpdf->Output($name, "D"); // Force download
        } catch (Exception $e) {
            // If PDF generation fails, show error
            echo "PDF Generation Error: " . $e->getMessage();
            exit;
        }
    }

    public function get_supplier_vat_number() {
        $supplier_name = $this->input->post('supplier_name');

        if (empty($supplier_name)) {
            echo json_encode(['success' => false, 'message' => 'Supplier name is required']);
            return;
        }

        // Query to get the most recent VAT number for this supplier name
        $this->db->select('vat_number');
        $this->db->from('sma_memo_entries');
        $this->db->where('name', trim($supplier_name));
        $this->db->where('vat_number IS NOT NULL');
        $this->db->where('vat_number !=', '');
        $this->db->order_by('id', 'DESC'); // Get the most recent entry
        $this->db->limit(1);

        $query = $this->db->get();
        $result = $query->row();

        if ($result && !empty($result->vat_number)) {
            echo json_encode(['success' => true, 'vat_number' => $result->vat_number]);
        } else {
            echo json_encode(['success' => false, 'message' => 'No VAT number found for this supplier']);
        }
    }

    // ─── SUPPLIER INVOICE PAYMENT (NEW) ────────────────────────────────────────

    /**
     * AJAX: Return pending purchase invoices for a supplier.
     * GET param: supplier_id
     */
    public function get_supplier_invoices_for_payment()
    {
        $supplier_id = $this->input->get('supplier_id');
        if (!$supplier_id) {
            echo json_encode([]);
            return;
        }
        $invoices = $this->purchases_model->getPendingPurchaseInvoicesForPayment($supplier_id);
        echo json_encode($invoices);
    }

    /**
     * AJAX: Return available debit memos for a supplier.
     * GET param: supplier_id
     */
    public function get_supplier_debit_memos_for_payment()
    {
        $supplier_id = $this->input->get('supplier_id');
        if (!$supplier_id) {
            echo json_encode([]);
            return;
        }
        $memos = $this->purchases_model->getDebitMemosBySupplierForPayment($supplier_id);
        echo json_encode($memos);
    }

    public function get_supplier_service_invoices()
    {
        $supplier_id = $this->input->get('supplier_id');
        if (!$supplier_id) {
            header('Content-Type: application/json');
            echo json_encode([]);
            return;
        }
        $invoices = $this->purchases_model->getSupplierServiceInvoicesForPayment((int)$supplier_id);
        header('Content-Type: application/json');
        echo json_encode($invoices);
    }

    /**
     * Journal entry for new-style supplier invoice payment.
     * Dr  Supplier Payable  = cash_payment + advance_amount
     * Dr  Bank Charges      = bank_charges  (if any)
     * Dr  VAT on charges    = bank_charge_vat  (if any)
     * Cr  Bank/Cash ledger  = cash_payment + bank_charges + bank_charge_vat
     * Cr  Advance Ledger    = advance_amount  (if any)
     */
    public function convert_supplier_payment_multiple_invoice_new(
        $supplier_id, $ledger_account, $bank_charges_account,
        $payment_amount, $bank_charges, $reference_no, $type, $date,
        $supplier_advance_ledger = null, $advance_amount = 0
    ) {
        $this->load->admin_model('companies_model');
        $supplier = $this->companies_model->getCompanyByID($supplier_id);

        $bank_charge_vat   = ($bank_charges > 0) ? round($bank_charges * 0.15, 4) : 0;
        $total_payable_dr  = $payment_amount + $advance_amount;
        $total_bank_cr     = $payment_amount + $bank_charges + $bank_charge_vat;
        $journal_total     = $total_payable_dr + $bank_charges + $bank_charge_vat;

        $entry = [
            'entrytype_id'       => 4,
            'transaction_type'   => $type,
            'number'             => 'PM-' . $reference_no,
            'date'               => $date,
            'dr_total'           => $journal_total,
            'cr_total'           => $journal_total,
            'notes'              => 'Payment Reference: ' . $reference_no . ' Date: ' . date('Y-m-d H:i:s'),
            'pid'                => '',
            'supplier_id'        => $supplier_id,
        ];
        $this->db->insert('sma_accounts_entries', $entry);
        $insert_id = $this->db->insert_id();

        $items = [];

        // Dr Supplier Payable
        $items[] = ['entry_id' => $insert_id, 'dc' => 'D', 'ledger_id' => $supplier->ledger_account, 'amount' => $total_payable_dr, 'narration' => 'Supplier Payable Settlement'];

        // Dr Bank Charges
        if ($bank_charges > 0 && $bank_charges_account > 0) {
            $items[] = ['entry_id' => $insert_id, 'dc' => 'D', 'ledger_id' => $bank_charges_account, 'amount' => $bank_charges, 'narration' => 'Bank Charges'];
        }

        // Dr VAT on bank charges
        if ($bank_charge_vat > 0) {
            $vat_ledger = $this->Settings->vat_ledger_id ?? $bank_charges_account;
            $items[] = ['entry_id' => $insert_id, 'dc' => 'D', 'ledger_id' => $vat_ledger, 'amount' => $bank_charge_vat, 'narration' => 'VAT on Bank Charges (15%)'];
        }

        // Cr Bank/Cash
        if ($ledger_account > 0 && ($payment_amount + $bank_charges + $bank_charge_vat) > 0) {
            $items[] = ['entry_id' => $insert_id, 'dc' => 'C', 'ledger_id' => $ledger_account, 'amount' => $total_bank_cr, 'narration' => 'Payment Sent'];
        }

        // Cr Advance Ledger
        if ($advance_amount > 0 && $supplier_advance_ledger) {
            $items[] = ['entry_id' => $insert_id, 'dc' => 'C', 'ledger_id' => $supplier_advance_ledger, 'amount' => $advance_amount, 'narration' => 'Advance Settlement', 'reconciliation_date' => $date];
        }

        foreach ($items as $item) {
            $this->db->insert('sma_accounts_entryitems', $item);
        }

        return $insert_id;
    }

    /**
     * New-style supplier payment: invoice settlements only.
     * Sources: cash/bank payment + debit memos + advance balance.
     * No advance recording allowed here.
     */
    public function payment_to_supplier_new()
    {
        $this->form_validation->set_rules('supplier',      $this->lang->line('supplier'),     'required');
        $this->form_validation->set_rules('reference_no',  $this->lang->line('reference_no'), 'required');
        $this->form_validation->set_rules('date',          $this->lang->line('date'),         'required');

        $bc   = [['link' => base_url(), 'page' => lang('home')], ['link' => '#', 'page' => lang('Supplier Invoice Payment')]];
        $meta = ['page_title' => lang('Supplier Invoice Payment (New)'), 'bc' => $bc];

        if ($this->form_validation->run() == true) {
            $supplier_id    = $this->input->post('supplier');
            $reference_no   = $this->input->post('reference_no');
            $ledger_account = $this->input->post('ledger');
            $date_raw       = $this->input->post('date');
            $note           = $this->input->post('note');
            $bank_charges   = (float)($this->input->post('bank_charges') ?: 0);
            $payment_amount = (float)($this->input->post('payment_amount') ?: 0);
            $advance_amount = (float)($this->input->post('advance_amount') ?: 0);

            // Duplicate reference check
            /*$this->db->where('reference_no', $reference_no);
            $this->db->where('supplier_id IS NOT NULL', null, false);
            if ($this->db->get('payment_reference')->num_rows() > 0) {
                $this->session->set_flashdata('error', 'The reference number already exists. Please use a unique reference number.');
                admin_redirect('suppliers/payment_to_supplier_new');
            }*/

            if ($payment_amount > 0 && !$ledger_account) {
                $this->session->set_flashdata('error', 'Please select a ledger account when entering a cash/bank payment amount.');
                admin_redirect('suppliers/payment_to_supplier_new');
            }

            // Parse date
            $fmt = DateTime::createFromFormat('d/m/Y', $date_raw);
            if ($fmt === false) {
                $this->session->set_flashdata('error', 'Invalid date format. Use DD/MM/YYYY.');
                admin_redirect('suppliers/payment_to_supplier_new');
            }
            $date = $fmt->format('Y-m-d');

            $supplier_advance_ledger = isset($this->Settings->supplier_advance_ledger) && !empty($this->Settings->supplier_advance_ledger)
                ? $this->Settings->supplier_advance_ledger : null;
            $bank_charges_account = $this->Settings->bank_fees_ledger ?? null;

            // Invoice selections
            $invoice_ids     = $this->input->post('invoice_ids') ?: [];
            $invoice_amounts = $this->input->post('invoice_amounts') ?: [];

            // Debit-memo selections
            $debit_memo_amounts = $this->input->post('debit_memo_amounts') ?: [];

            // Service invoice selections
            $service_invoice_amounts = $this->input->post('service_invoice_amounts') ?: [];

            if (empty($invoice_ids) && empty($service_invoice_amounts)) {
                $this->session->set_flashdata('error', 'Please select at least one invoice to settle.');
                admin_redirect('suppliers/payment_to_supplier_new');
            }

            // Validate advance
            if ($advance_amount > 0) {
                $available_advance = $this->getSupplierAdvanceBalance($supplier_id, $supplier_advance_ledger);
                
                if ($advance_amount > $available_advance) {
                    $this->session->set_flashdata('error', 'Applied advance amount exceeds available supplier advance balance (' . number_format($available_advance, 2) . ').');
                    admin_redirect('suppliers/payment_to_supplier_new');
                }
            }
            
            // Collect invoice details
            $invoice_details             = [];
            $total_payments_from_invoices = 0;
            foreach ($invoice_amounts as $inv_id => $applied) {
                $applied = (float)$applied;
                if ($applied > 0) {
                    $purchase = $this->purchases_model->getPurchaseByID($inv_id);
                    if (!$purchase) {
                        $this->session->set_flashdata('error', 'Purchase invoice not found: ' . $inv_id);
                        admin_redirect('suppliers/payment_to_supplier_new');
                    }
                    $total_payments_from_invoices += $applied;
                    $invoice_details[$inv_id] = [
                        'grand_total'   => $purchase->grand_total,
                        'total_paid'    => $purchase->paid ?? 0,
                        'total_paying'  => $applied,
                    ];
                }
            }

            // Collect debit-memo details
            $debit_memo_details        = [];
            $total_applied_debit_memos = 0;
            foreach ($debit_memo_amounts as $memo_id => $applied) {
                $applied = (float)$applied;
                if ($applied > 0) {
                    $memo = $this->purchases_model->getDebitMemoData($memo_id);
                    if (!$memo) {
                        $this->session->set_flashdata('error', 'Debit memo not found: ' . $memo_id);
                        admin_redirect('suppliers/payment_to_supplier_new');
                    }
                    $available = $memo->payment_amount - ($memo->used_amount ?? 0);
                    if ($applied > $available) {
                        $this->session->set_flashdata('error', 'Applied debit-memo amount exceeds available balance for memo ID: ' . $memo_id);
                        admin_redirect('suppliers/payment_to_supplier_new');
                    }
                    $total_applied_debit_memos += $applied;
                    $debit_memo_details[$memo_id] = [
                        'used'   => $memo->used_amount ?? 0,
                        'paying' => $applied,
                    ];
                }
            }

            // Collect service-invoice details
            $service_invoice_details    = [];
            $total_service_inv_payments = 0;
            foreach ($service_invoice_amounts as $memo_id => $applied) {
                $applied = (float)$applied;
                
                if ($applied > 0) {
                    $memo = $this->purchases_model->getDebitMemoData($memo_id);
                    if (!$memo) {
                        $this->session->set_flashdata('error', 'Service invoice not found: ' . $memo_id);
                        admin_redirect('suppliers/payment_to_supplier_new');
                    }
                    $available = $memo->payment_amount - ($memo->used_amount ?? 0);
                    if ($applied > $available) {
                        $this->session->set_flashdata('error', 'Applied service invoice amount exceeds outstanding balance for service invoice ID: ' . $memo_id);
                        admin_redirect('suppliers/payment_to_supplier_new');
                    }
                    //echo 'Applied: '.$applied. ' Available: '.$available;exit;
                    $total_service_inv_payments += $applied;
                    $service_invoice_details[$memo_id] = [
                        'used'   => $memo->used_amount ?? 0,
                        'paying' => $applied,
                    ];
                }
            }

            // Verify supplier exists
            $this->load->admin_model('companies_model');
            $supplier = $this->companies_model->getCompanyByID($supplier_id);
            if (!$supplier) {
                $this->session->set_flashdata('error', 'Supplier not found.');
                admin_redirect('suppliers/payment_to_supplier_new');
            }

            // Total sources must cover total invoice applied amounts
            $total_sources = $payment_amount + $advance_amount + $total_applied_debit_memos;
            $total_invoices_to_settle = $total_payments_from_invoices + $total_service_inv_payments;
            if (round($total_invoices_to_settle, 2) > round($total_sources, 2)) {
                $this->session->set_flashdata('error', 'Total payment sources (' . number_format($total_sources, 2) . ') are insufficient to cover the applied invoice amounts (' . number_format($total_invoices_to_settle, 2) . ').');
                admin_redirect('suppliers/payment_to_supplier_new');
            }

            // Create payment reference (matches pattern used throughout add_payment())
            $payment_id = $this->add_supplier_reference(
                $total_sources, $reference_no, $date, $note,
                $supplier_id, $bank_charges, $bank_charges_account, $ledger_account
            );

            if (!$payment_id) {
                $this->session->set_flashdata('error', 'Failed to create payment reference.');
                admin_redirect('suppliers/payment_to_supplier_new');
            }

            // Record payment per invoice
            foreach ($invoice_details as $purchase_id => $detail) {
                $this->purchases_model->addPayment([
                    'date'         => $date,
                    'purchase_id'  => $purchase_id,
                    'reference_no' => $reference_no,
                    'amount'       => $detail['total_paying'],
                    'note'         => $note,
                    'created_by'   => $this->session->userdata('user_id'),
                    'type'         => 'sent',
                    'payment_id'   => $payment_id,
                ]);
                $this->purchases_model->update_purchase_paid_amount($purchase_id, $detail['total_paying']);
            }

            // Update debit-memo used amounts
            foreach ($debit_memo_details as $memo_id => $detail) {
                $this->db->where('id', $memo_id);
                $this->db->update('sma_memo', ['used_amount' => $detail['used'] + $detail['paying']]);
            }

            // Update service-invoice used amounts
            foreach ($service_invoice_details as $memo_id => $detail) {
                $this->purchases_model->update_service_invoice_used_amount($memo_id, $detail['paying']);
            }

            // Create journal entry
            $journal_id = $this->convert_supplier_payment_multiple_invoice_new(
                $supplier_id, $ledger_account, $bank_charges_account,
                $payment_amount, $bank_charges, $reference_no,
                'supplierpayment', $date, $supplier_advance_ledger, $advance_amount
            );
            $this->purchases_model->update_payment_reference($payment_id, $journal_id);

            $this->session->set_flashdata('message', 'Payment processed successfully.');
            admin_redirect('suppliers/view_payment/' . $payment_id);

        } else if ($this->input->post() && $this->form_validation->run() == false) {
            $this->session->set_flashdata('error', 'Please fill all mandatory fields.');
            admin_redirect('suppliers/payment_to_supplier_new');
        } else {
            $this->data['suppliers']              = $this->site->getAllChildCompanies('supplier');
            $this->data['warehouses']             = $this->site->getAllWarehouses();
            $this->data['supplier_advance_ledger'] = isset($this->Settings->supplier_advance_ledger) && !empty($this->Settings->supplier_advance_ledger)
                ? $this->Settings->supplier_advance_ledger : null;
            $this->data['ledgers'] = $this->site->getCompanyLedgersByGroupCode(['11101', '11102']);
            $this->page_construct('suppliers/payment_to_supplier_new', $meta, $this->data);
        }
    }

}
