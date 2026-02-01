<?php

defined('BASEPATH') or exit('No direct script access allowed');
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
class Customers extends MY_Controller
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
        $this->lang->admin_load('customers', $this->Settings->user_language);
        $this->load->admin_model('sales_model');
        $this->load->admin_model('purchases_model');
        $this->load->library('form_validation');
        $this->load->admin_model('companies_model');

        // Sequence-Code
        $this->load->library('SequenceCode');
        $this->sequenceCode = new SequenceCode();
    }

    public function deleteFromAccounting($memo_id){
        $accouting_entries = $this->purchases_model->getMemoAccountingEntries($memo_id);
        foreach ($accouting_entries as $accouting_entry){
            $this->db->delete('sma_accounts_entryitems', ['entry_id' => $accouting_entry->id]);
            $this->db->delete('sma_accounts_entries', ['id' => $accouting_entry->id]);
        }
    }

    public function convert_customer_payment_multiple_invoice($customer_id, $ledger_account, $payment_amount, $reference_no, $type, $total_additional_discount, $customer_discount_ledger, $date){
        $this->load->admin_model('companies_model');
        $customer = $this->companies_model->getCompanyByID($customer_id);

        if($total_additional_discount > 0){
            $receiveable_amount = $payment_amount + $total_additional_discount;
        }else{
            $receiveable_amount = $payment_amount;
        }

        /*Accounts Entries*/
        $entry = array(
            'entrytype_id' => 4,
            'transaction_type' => $type,
            'number'       => 'PMC-'.$reference_no,
            'date'         => $date,
            'dr_total'     => $receiveable_amount,
            'cr_total'     => $receiveable_amount,
            'notes'        => 'Payment Reference: '.$reference_no.' Date: '.date('Y-m-d H:i:s'),
            'sid'          =>  '',
            'customer_id'  => $customer_id
            );
        $add  = $this->db->insert('sma_accounts_entries', $entry);
        $insert_id = $this->db->insert_id();

        //customer - Credit to reduce receivable
        $entryitemdata[] = array(
            'Entryitem' => array(
                'entry_id' => $insert_id,
                'dc' => 'C',
                'ledger_id' => $customer->ledger_account,
                'amount' => $receiveable_amount,
                'narration' => 'Account Receivable'
            )
        );

        //payment ledger - Debit to increase cash/bank
        $entryitemdata[] = array(
            'Entryitem' => array(
                'entry_id' => $insert_id,
                'dc' => 'D',
                'ledger_id' => $ledger_account,
                'amount' => $payment_amount,
                'narration' => 'Payment Received'
            )
        );

        if($total_additional_discount > 0){
            //payment ledger - Debit to increase cash/bank
            $entryitemdata[] = array(
                'Entryitem' => array(
                    'entry_id' => $insert_id,
                    'dc' => 'D',
                    'ledger_id' => $customer_discount_ledger,
                    'amount' => $total_additional_discount,
                    'narration' => 'Additional Discount on Payment'
                )
            );
        }

        foreach ($entryitemdata as $row => $itemdata)
        {
            $this->db->insert('sma_accounts_entryitems' ,$itemdata['Entryitem']);
        }

        return $insert_id;
    }

    /**
     * Create accounting entry for customer advance payment
     * 
     * @param int $customer_id Customer ID
     * @param int $payment_ledger Bank/Cash ledger where payment is received
     * @param int $advance_ledger Customer advance ledger (liability account)
     * @param float $payment_amount Amount received
     * @param string $reference_no Payment reference
     * @param string $type Transaction type
     * @return int Entry ID
     */
    public function convert_customer_payment_advance($customer_id, $payment_ledger, $advance_ledger, $payment_amount, $reference_no, $type, $date){
        $this->load->admin_model('companies_model');
        $customer = $this->companies_model->getCompanyByID($customer_id);

        /*Accounts Entries*/
        $entry = array(
            'entrytype_id' => 4,
            'transaction_type' => $type,
            'number'       => 'ADV-'.$reference_no,
            'date'         => $date,
            'dr_total'     => $payment_amount,
            'cr_total'     => $payment_amount,
            'notes'        => 'Customer Advance Payment Reference: '.$reference_no.' Date: '.date('Y-m-d H:i:s'),
            'sid'          =>  '',
            'customer_id'  => $customer_id
            );
        $add  = $this->db->insert('sma_accounts_entries', $entry);
        $insert_id = $this->db->insert_id();

        $entryitemdata = array();

        // Debit: Bank/Payment account (asset increases - we received money)
        $entryitemdata[] = array(
            'Entryitem' => array(
                'entry_id' => $insert_id,
                'dc' => 'D',
                'ledger_id' => $payment_ledger,
                'amount' => $payment_amount,
                'narration' => 'Advance received from customer'
            )
        );

        // Credit: Customer Advance Ledger (liability increases - we owe customer)
        $entryitemdata[] = array(
            'Entryitem' => array(
                'entry_id' => $insert_id,
                'dc' => 'C',
                'ledger_id' => $advance_ledger,
                'amount' => $payment_amount,
                'narration' => 'Customer advance liability'
            )
        );

        foreach ($entryitemdata as $row => $itemdata)
        {
            $this->db->insert('sma_accounts_entryitems' ,$itemdata['Entryitem']);
        }

        return $insert_id;
    }

    /**
     * Create accounting entry for customer discount on invoice
     * Debit: Customer Discount Ledger (expense increases)
     * Credit: Customer Receivable (asset decreases - customer owes less)
     * 
     * @param int $customer_id Customer ID
     * @param int $sale_id Sale/Invoice ID
     * @param int $discount_ledger Customer discount ledger ID
     * @param float $discount_amount Discount amount
     * @param float $discount_percentage Discount percentage
     * @param string $reference_no Invoice reference
     * @param string $date Transaction date
     * @return int Entry ID
     */
    private function create_customer_discount_entry($customer_id, $sale_id, $discount_ledger, $discount_amount, $discount_percentage, $reference_no, $date) {
        // Get customer details
        $this->load->admin_model('companies_model');
        $customer = $this->companies_model->getCompanyByID($customer_id);
        
        if (!$customer || !$customer->ledger_account) {
            log_message('error', 'Customer ledger account not found for customer ID: ' . $customer_id);
            return false;
        }
        
        // Create journal entry for discount
        $entry = array(
            'entrytype_id' => 4,
            'transaction_type' => 'customerdiscount',
            'number' => 'DISC-' . $reference_no,
            'date' => $date,
            'dr_total' => $discount_amount,
            'cr_total' => $discount_amount,
            'notes' => 'Customer Discount ' . $discount_percentage . '% on invoice ' . $reference_no . ' for ' . $customer->name,
            'customer_id' => $customer_id,
            'sid' => $sale_id
        );
        
        $add = $this->db->insert('sma_accounts_entries', $entry);
        $entry_id = $this->db->insert_id();
        
        if ($add) {
            // Debit customer discount ledger (expense increases)
            $entryitem1 = array(
                'entry_id' => $entry_id,
                'ledger_id' => $discount_ledger,
                'amount' => $discount_amount,
                'dc' => 'D',
                'reconciliation_date' => $date,
                'narration' => 'Discount ' . $discount_percentage . '% given'
            );
            $this->db->insert('sma_accounts_entryitems', $entryitem1);
            
            // Credit customer receivable ledger (asset decreases - customer owes less)
            $entryitem2 = array(
                'entry_id' => $entry_id,
                'ledger_id' => $customer->ledger_account,
                'amount' => $discount_amount,
                'dc' => 'C',
                'reconciliation_date' => $date,
                'narration' => 'Receivable reduced by discount'
            );
            $this->db->insert('sma_accounts_entryitems', $entryitem2);
            
            return $entry_id;
        }
        
        return false;
    }

    public function pending_invoices(){
        $customer_id = $_GET['customer_id'];
        if ($rows = $this->sales_model->getPendingInvoicesByCustomer($customer_id)) {
            $data = json_encode($rows);
        } else {
            $rows = array();
            $data = json_encode($rows);
        }
        echo $data;
    }

    public function customer_limit_info(){
        $customer_id = $_GET['customer_id'];
        $response = array();
        
        if ($customer_id) {
            // Get customer data including credit limit
            $customer = $this->companies_model->getCompanyByID($customer_id);
            
            if ($customer) {
                // Get customer's current balance (total outstanding amount)
                $customer_balance = $this->companies_model->getCustomerBalance($customer_id);
                
                $credit_limit = floatval($customer->credit_limit ?? 0);
                $current_balance = floatval($customer_balance ?? 0);
                $remaining_limit = max(0, $credit_limit - $current_balance);
                
                $response = array(
                    'credit_limit' => $credit_limit,
                    'current_balance' => $current_balance,
                    'remaining_limit' => $remaining_limit,
                    'customer_name' => $customer->company != '-' ? $customer->company : $customer->name
                );
            }
        }
        
        header('Content-Type: application/json');
        echo json_encode($response);
    }

    public function get_customer_discount_ledger(){
        try {
            $customer_id = isset($_GET['customer_id']) ? $_GET['customer_id'] : null;
            
            if (!$customer_id) {
                echo json_encode(array(
                    'ledger_name' => '',
                    'discount_ledger_configured' => false,
                    'error' => 'No customer ID provided'
                ));
                return;
            }
            
            // Get customer discount ledger from settings
            $settings = $this->Settings;
            $customer_discount_ledger = isset($settings->customer_discount_ledger) && !empty($settings->customer_discount_ledger) 
                                      ? $settings->customer_discount_ledger 
                                      : null;
            
            $ledger_name = '';
            
            if($customer_discount_ledger) {
                // Get ledger name
                $this->db->select('name');
                $this->db->from('sma_accounts_ledgers');
                $this->db->where('id', $customer_discount_ledger);
                $query = $this->db->get();
                
                if ($query && $query->num_rows() > 0) {
                    $ledger = $query->row();
                    $ledger_name = $ledger->name;
                }
            }
            
            $data = array(
                'ledger_name' => $ledger_name,
                'discount_ledger_configured' => $customer_discount_ledger ? true : false,
                'customer_id' => $customer_id,
                'discount_ledger_id' => $customer_discount_ledger
            );
            
            echo json_encode($data);
            
        } catch (Exception $e) {
            // Log the full error for debugging
            log_message('error', 'Customer Discount Ledger Error: ' . $e->getMessage());
            log_message('error', 'SQL Error: ' . $this->db->last_query());
            
            echo json_encode(array(
                'ledger_name' => '',
                'discount_ledger_configured' => false,
                'error' => 'Database error: ' . $e->getMessage(),
                'query' => $this->db->last_query()
            ));
        }
    }

    public function add_customer_reference($amount, $reference_no, $date, $note, $customer_id, $ledger_account){
        $payment_reference = [
            'customer_id' => $customer_id,
            'date' => $date,
            'sequence_code' => $this->sequenceCode->generate('PAY', 5),
            'note' => $note,
            'reference_no'  => $reference_no,
            'amount' => $amount,
            'transfer_from_ledger' => $ledger_account,
            'created_by'    => $this->session->userdata('user_id')
        ];

        $payment_id = $this->sales_model->addPaymentReference($payment_reference);
        return $payment_id;
    }

    public function make_customer_payment($id, $amount, $reference_no, $date, $note, $payment_id, $additional_discount = 0){
        $payment = [
            'date'          => $date,
            'sale_id'   => $id,
            'reference_no'  => $reference_no,
            'amount'        => $amount,
            'additional_discount' => $additional_discount,
            'note'          => $note,
            'created_by'    => $this->session->userdata('user_id'),
            'type'          => 'sent',
            'payment_id'    => $payment_id
        ];

        $this->sales_model->addPayment($payment);
    }

    public function update_sale_order($id, $amount){
        $this->sales_model->update_sale_paid_amount($id, $amount);
    }

    public function edit_payment($id = null){
        //$this->sma->checkPermissions(false, true);
        $this->form_validation->set_rules('payment_id', $this->lang->line('payment_id'), 'required');
        $this->form_validation->set_rules('amount', $this->lang->line('amount'), 'required');

        if ($this->form_validation->run() == true) {
            $payment_id      = $this->input->post('payment_id');
            $amount          = $this->input->post('amount');

            $payment_detail  = $this->sales_model->getPaymentByID($payment_id);
            $sale_detail = $this->sales_model->getSaleByID($payment_detail->sale_id);

            $old_amount = $payment_detail->amount;

            $difference = $amount - $old_amount;
            $accounts_reference_no = 'PMC-'.$payment_detail->reference_no;
            $accounts_entry = $this->sales_model->getAccountsEntryByReferenceNo($accounts_reference_no);
            $entry_id = $accounts_entry->id;
            $dr_total = $accounts_entry->dr_total;
            $cr_total = $accounts_entry->cr_total;

            $new_dr_total = $dr_total + $difference;
            $new_cr_total = $cr_total + $difference;

            $updated_price_for_sale = $sale_detail->paid + $difference;

            $debit_entry = null;
            $credit_entry = null;

            $this->db->update('payments', ['amount' => $amount], ['id' => $payment_id]);
            $this->db->update('sales', ['paid' => $updated_price_for_sale], ['id' => $payment_detail->sale_id]);

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
            $this->db->select('*');
            $this->db->from('sma_accounts_entryitems');
            $this->db->where('entry_id', $entry_id);
            $this->db->where('dc', 'C');
            $this->db->order_by('amount', 'DESC');
            $this->db->limit(1);

            $query = $this->db->get();

            if ($query->num_rows() > 0) {
                $credit_entry = $query->row();
            }

            if($debit_entry != null){
                $debit_entry_amount = $debit_entry->amount + $difference;
                $this->db->update('sma_accounts_entryitems', ['amount' => $debit_entry_amount], ['id' => $debit_entry->id]);
            }

            if($credit_entry != null){
                $credit_entry_amount = $credit_entry->amount + $difference;
                $this->db->update('sma_accounts_entryitems', ['amount' => $credit_entry_amount], ['id' => $credit_entry->id]);
            }

            admin_redirect('customers/list_payments');
        }else{
            if ($this->input->get('id')) {
                $id = $this->input->get('id');
            }

            $this->data['payment']  = $this->sales_model->getPaymentByID($id);
            $this->page_construct('customers/edit_payment', $meta, $this->data);
        }
    }

    public function view_payment($id = null){
        //$this->sma->checkPermissions(false, true);
        $this->form_validation->set_rules('id', $this->lang->line('id'), 'required');

        $data = [];
        $bc    = [['link' => base_url(), 'page' => lang('home')], ['link' => '#', 'page' => lang('View Customer Payments')]];
        $meta = ['page_title' => lang('View Customer Payments'), 'bc' => $bc];

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }

        $this->data['suppliers']  = $this->site->getAllCompanies('customer');
        $this->data['payment_ref']  = $this->sales_model->getPaymentReferenceByID($id);
        $this->data['payments']  = $this->sales_model->getPaymentByReferenceID($id);
        $this->page_construct('customers/view_payment', $meta, $this->data);
        
    }

    public function list_payments(){
        $data = [];
        $bc    = [['link' => base_url(), 'page' => lang('home')], ['link' => '#', 'page' => lang('Customer Payments')]];
        $meta = ['page_title' => lang('Customer Payments'), 'bc' => $bc];
        $this->data['payments'] = $this->sales_model->getPaymentReferences();
        $this->page_construct('customers/list_payments', $meta, $this->data);
    }

    public function payment_from_customer(){
        //$this->sma->checkPermissions(false, true);
        $this->form_validation->set_rules('customer', $this->lang->line('customer'), 'required');

        $data = [];
        $bc    = [['link' => base_url(), 'page' => lang('home')], ['link' => '#', 'page' => lang('add payment')]];
        $meta = ['page_title' => lang('Customer Payments'), 'bc' => $bc];

        if ($this->form_validation->run() == true) {
            $customer_id      = $this->input->post('customer');
            $payments_array      = $this->input->post('payment_amount');
            $item_ids = $this->input->post('item_id');
            $reference_no = $this->input->post('reference_no');
            $payment_total = $this->input->post('payment_total');
            $ledger_account = $this->input->post('ledger_account');
            $due_amount_array = $this->input->post('due_amount');
            $original_amount_array = $this->input->post('original_amount');
            $additional_discount_array = $this->input->post('additional_discount');
            $return_total_array = $this->input->post('return_total');
            $note = $this->input->post('note');
            $date_fmt = $this->input->post('date'); 
            $formattedDate = DateTime::createFromFormat('d/m/Y H:i', $date_fmt);

            if ($formattedDate) {
                $date = $formattedDate->format('Y-m-d');
            } else {
                echo 'Invalid date format!';
                $date = null; // Handle invalid input as needed
            }

            // Get customer advance ledger from settings
            $settings = $this->Settings;
            $customer_advance_ledger = isset($settings->customer_advance_ledger) && !empty($settings->customer_advance_ledger) 
                                     ? $settings->customer_advance_ledger 
                                     : null;

            // If user explicitly chose to park entire payment as advance via the checkbox,
            // force treating this as a pure advance on the backend regardless of any
            // payment_amount[] inputs present (they may be disabled client-side, but
            // this is a defensive server-side safeguard).
            if ($this->input->post('park_as_advance')) {
                $payments_array = null;
            }

            if(!$payments_array || sizeOf($payments_array) == 0){
                // Pure advance payment scenario (no invoices selected)
                if (!$customer_advance_ledger && $payment_total > 0) {
                    $this->session->set_flashdata('error', 'Cannot process advance payment. Customer Advance Ledger is not configured in system settings.');
                    redirect($_SERVER['HTTP_REFERER']);
                }

                if ($payment_total > 0 && $customer_advance_ledger) {
                    // Create payment reference using payment ledger (bank/cash account)
                    $payment_id = $this->add_customer_reference($payment_total, $reference_no, $date, $note . ' (Pure Advance)', $customer_id, $ledger_account);

                    if (!$payment_id) {
                        $this->session->set_flashdata('error', 'Failed to create pure advance payment reference. Please check system configuration.');
                        redirect($_SERVER['HTTP_REFERER']);
                    }

                    // NOTE: For pure advances we should NOT create a payments row linked to a sale.
                    // The payment_reference record + accounting journal represent the advance.
                    // Update customer balance and create journal entry for the advance.
                    $this->sales_model->update_customer_balance($customer_id, $payment_total);

                    // Create journal entry: Pass payment ledger and customer advance ledger
                    $journal_id = $this->convert_customer_payment_advance($customer_id, $ledger_account, $customer_advance_ledger, $payment_total, $reference_no, 'customeradvance', $date);
                    $this->sales_model->update_payment_reference($payment_id, $journal_id);
                    // Also create a payments row so the advance appears in payment listings
                    // Use make_customer_payment with NULL sale_id to create an unlinked payment record
                    $this->make_customer_payment(NULL, $payment_total, $reference_no, $date, $note . ' (Pure Advance)', $payment_id);
                    $this->session->set_flashdata('message', lang('Pure advance payment received Successfully!'));
                    admin_redirect('customers/view_payment/' . $payment_id);
                }
            }else{
                // Invoice payment scenario
                // Get settle_with_advance checkbox value
                $settle_with_advance = $this->input->post('settle_with_advance') ? true : false;
                
                // Calculate total due amount
                $total_due = array_sum($due_amount_array);
                $total_original = array_sum($original_amount_array);
                
                // Check if payment exceeds total due - this would be advance
                if($payment_total > $total_due) {
                    if(!$customer_advance_ledger) {
                        $this->session->set_flashdata('error', 'Payment amount exceeds total due amount. Please configure Customer Advance Ledger in settings to allow advance payments.');
                        redirect($_SERVER['HTTP_REFERER']);
                    }
                }

                // Handle advance settlement if checkbox is checked
                // Calculate shortage between total invoice due and payment entered
                $cash_payment = $payment_total;
                $advance_settlement_amount = 0;
                
                if($settle_with_advance) {
                    if($customer_advance_ledger) {
                        // Get current advance balance
                        $current_advance_balance = $this->getCustomerAdvanceBalance($customer_id, $customer_advance_ledger);
                        
                        if($current_advance_balance > 0) {
                            // Calculate shortage (total invoice amount - payment entered)
                            $shortage_amount = $total_due - $payment_total;
                            
                            if($shortage_amount > 0) {
                                // Use advance to cover the shortage (minimum of shortage or available advance)
                                $advance_settlement_amount = min($current_advance_balance, $shortage_amount);
                                // Cash payment remains as entered
                                $cash_payment = $payment_total;
                                // Add note about settlement
                                $note .= " (Settlement: Cash {$cash_payment}, Advance {$advance_settlement_amount}, Total: " . ($cash_payment + $advance_settlement_amount) . ")";
                            }
                        }
                    }
                }
                
                // Validate payment amounts
                for($i = 0; $i < count($payments_array); $i++){
                    $payment_amount = $payments_array[$i];
                    $item_id = $item_ids[$i];
                    $due_amount = $due_amount_array[$i];
                    if($payment_amount > $due_amount){
                        $this->session->set_flashdata('error', 'Amount received cannot be greater than due amount');
                        redirect($_SERVER['HTTP_REFERER']);
                    }
                }
                
                // Case 4 Check: Ensure payment_total + any advance settlement can cover the selected invoice payments
                // Use round() to avoid floating-point precision issues
                $required_sum = round(array_sum($payments_array), 4);
                $available_sum = round($payment_total + $advance_settlement_amount, 4);
                
                // Use a small tolerance for floating-point comparison (0.01 = 1 cent tolerance)
                $tolerance = 0.01;
                if(($required_sum - $available_sum) > $tolerance){
                    $this->session->set_flashdata('error', 'Total payment amount is insufficient to cover selected invoice payments. Required: ' . number_format($required_sum, 4) . ', Provided: ' . number_format($available_sum, 4));
                    redirect($_SERVER['HTTP_REFERER']);
                }
                
                // Split payment into invoice payment and advance payment
                $total_invoice_payment = array_sum($payments_array); // Actual amount going to invoices
                $advance_payment = $cash_payment - $total_invoice_payment; // Excess cash amount
                
                $main_payment_id = null;
                
                // Process invoice payments (if there are any invoices OR if settling with advance)
                if($total_invoice_payment > 0 || $advance_settlement_amount > 0) {
                    // Validation: Ensure we have some payment method
                    if($total_invoice_payment <= 0 && $advance_settlement_amount <= 0) {
                        $this->session->set_flashdata('error', 'Total settlement amount must be greater than zero.');
                        redirect($_SERVER['HTTP_REFERER']);
                    }
                    
                    // Create payment reference ONLY for the actual cash payment to invoices (not including advance settlement)
                    // The advance settlement is tracked separately and should not be included in this payment reference
                    $combined_payment_id = $this->add_customer_reference($total_invoice_payment, $reference_no, $date, $note, $customer_id, $ledger_account);
                    
                    // Verify payment reference was created successfully
                    if (!$combined_payment_id) {
                        $this->session->set_flashdata('error', 'Failed to create payment reference. Please check system configuration.');
                        redirect($_SERVER['HTTP_REFERER']);
                    }
                    
                    // Get customer discount ledger from settings
                    $customer_discount_ledger = isset($settings->customer_discount_ledger) && !empty($settings->customer_discount_ledger) 
                                              ? $settings->customer_discount_ledger 
                                              : null;
                    
                    // Distribute payments to invoices (cash + advance settlement)
                    $remaining_advance = $advance_settlement_amount;
                    $total_additional_discount = 0;

                    for ($i = 0; $i < count($payments_array); $i++) {
                        $cash_payment_for_invoice = $payments_array[$i];
                        //echo '<pre>';print_r($cash_payment_for_invoice);exit;
                        $item_id = $item_ids[$i];
                        $due_amount = $due_amount_array[$i];
                        $original_amount = $original_amount_array[$i];
                        // Get return amount for this invoice
                        $return_amount = isset($return_total_array[$i]) ? $return_total_array[$i] : 0;
                        $outstanding_amount = $due_amount - $return_amount;
                        // Get discount percentage for this invoice
                        $discount_percentage = isset($additional_discount_array[$i]) ? $additional_discount_array[$i] : 0;
                        // Calculate discount amount based on due amount
                        $discount_amount = 0;
                        if ($discount_percentage > 0) {
                            $discount_amount = ($outstanding_amount * $discount_percentage) / 100;
                        }
                        $total_additional_discount += $discount_amount;
                        // Calculate payment amount after discount
                        $payment_amount_after_discount = $outstanding_amount - $discount_amount;
                        // Create discount journal entry if discount was given
                        /*if ($discount_amount > 0 && $customer_discount_ledger) {
                            $sale_info = $this->sales_model->getSaleByID($item_id);
                            if ($sale_info) {
                                $this->create_customer_discount_entry(
                                    $customer_id, 
                                    $item_id, 
                                    $customer_discount_ledger, 
                                    $discount_amount, 
                                    $discount_percentage, 
                                    $sale_info->reference_no, 
                                    $date
                                );
                            }
                        }*/
                        
                        // Calculate shortage for this invoice (after discount)
                        $invoice_shortage = $payment_amount_after_discount - $cash_payment_for_invoice;
                        // Determine how much advance to use for this invoice
                        $advance_for_this_invoice = 0;
                        if ($remaining_advance > 0 && $invoice_shortage > 0) {
                            $advance_for_this_invoice = min($remaining_advance, $invoice_shortage);
                            $remaining_advance -= $advance_for_this_invoice;
                        }
                        
                        // Total payment for this invoice (cash + advance)
                        $total_payment_for_invoice = $cash_payment_for_invoice + $advance_for_this_invoice;
                        
                        // Always record additional discount and returns on the sale so the outstanding
                        // amount reflects these deductions even when there's no cash payment.
                        $update_data = [];
                        if ($discount_amount > 0) {
                            $update_data['additional_discount'] = $discount_amount;
                        }
                        if ($return_amount > 0) {
                            $update_data['returns_total_deducted'] = $return_amount;
                        }
                        if ($cash_payment_for_invoice > 0) {
                            $update_data['paid'] = $cash_payment_for_invoice;
                        }
                        if (!empty($update_data)) {
                            $this->db->update('sales', $update_data, ['id' => $item_id]);
                        }

                        // If there is any cash or advance applied to this invoice, record them
                        if ($total_payment_for_invoice > 0) {
                            // Record cash payment (only if there is cash for this invoice)
                            if ($cash_payment_for_invoice > 0) {
                                $this->make_customer_payment($item_id, $cash_payment_for_invoice, $reference_no, $date, $note . ' (Cash)', $combined_payment_id, $discount_percentage);
                                // NOTE: addPayment() will update the sale paid amount
                            }

                            // Record advance settlement payment (if advance used for this invoice)
                            /*if ($advance_for_this_invoice > 0) {
                                $this->make_customer_payment($item_id, $advance_for_this_invoice, $reference_no . '-ADV', $date, $note . ' (Advance Settlement)', $combined_payment_id, $discount_percentage);
                            }*/
                        }
                        
                        // After updating discounts/returns and recording any payments, re-fetch sale info
                        // and determine whether the invoice should be marked as paid
                        $sale_info = $this->sales_model->getSaleByID($item_id);
                        if ($sale_info) {
                            $grand_total = $sale_info->grand_total;
                            $total_paid = $sale_info->paid;
                            //$total_paid = $payment_amount_after_discount;

                            // Use deductions stored on the sale (in case they were persisted earlier)
                            $sale_additional_discount = $total_additional_discount;
                            $sale_returns = $return_amount;
                            $total_deductions = $sale_additional_discount + $sale_returns;

                            $amount_due_after_deductions = $grand_total - $total_deductions;
                            //echo $total_paid.' -- '.$grand_total.' -- '.$sale_returns.' -- '.$sale_additional_discount;exit;
                            if ($total_paid >= $amount_due_after_deductions) {
                                $this->db->update('sales', ['payment_status' => 'paid'], ['id' => $item_id]);
                            }
                        }
                    }
                    
                    // Normalize payment_status for affected invoices: mark paid/partial appropriately
                    foreach ($item_ids as $item_id_to_check) {
                        $sale_check = $this->sales_model->getSaleByID($item_id_to_check);
                        if (!$sale_check) continue;

                        $grand_total_c = $sale_check->grand_total ?? 0;
                        $paid_c = $sale_check->paid ?? 0;
                        $deduction_discount = $sale_check->additional_discount ?? 0;
                        $deduction_returns = $sale_check->returns_total_deducted ?? 0;
                        $due_after_deductions = $grand_total_c - ($deduction_discount + $deduction_returns);
                        
                        if ($paid_c >= $due_after_deductions) {
                            $this->db->update('sales', ['payment_status' => 'paid'], ['id' => $item_id_to_check]);
                        } else if ($paid_c > 0) {
                            $this->db->update('sales', ['payment_status' => 'partial'], ['id' => $item_id_to_check]);
                        } else {
                            // leave as unpaid/default or set to 'due' if you prefer
                            $this->db->update('sales', ['payment_status' => 'due'], ['id' => $item_id_to_check]);
                        }
                    }
                    
                    // Create accounting journal entries
                    if($advance_settlement_amount > 0) {
                        // If we are settling with advance, create separate journal entries
                        
                        // Create cash payment journal entry (only if cash payment > 0)
                        if($cash_payment > 0) {
                            // Use the total amount applied to invoices for the cash journal entry
                            // (previously used $outstanding_amount which was the last invoice's outstanding)
                            $cash_journal_id = $this->convert_customer_payment_multiple_invoice($customer_id, $ledger_account, $total_invoice_payment, $reference_no . '-CASH', 'customerpayment', $total_additional_discount, $customer_discount_ledger, $date);
                        }
                        
                        // Create advance settlement journal entry (debit advance ledger, credit customer ledger)
                        // This reduces the advance balance and settles the customer receivable
                        $advance_journal_id = $this->create_customer_advance_settlement_entry($customer_id, $customer_advance_ledger, $advance_settlement_amount, $reference_no . '-ADV', $date, 'Advance Settlement');
                        
                        // Update payment reference with the journal ID (prefer cash journal if exists, otherwise advance journal)
                        $this->sales_model->update_payment_reference($combined_payment_id, isset($cash_journal_id) ? $cash_journal_id : $advance_journal_id);
                    } else {
                        // Regular payment without advance settlement (cash only)
                        if($cash_payment > 0) {
                            // For regular cash-only payments, use the total applied to invoices
                            $journal_id = $this->convert_customer_payment_multiple_invoice($customer_id, $ledger_account, $total_invoice_payment, $reference_no, 'customerpayment', $total_additional_discount, $customer_discount_ledger, $date);
                            $this->sales_model->update_payment_reference($combined_payment_id, $journal_id);
                        }
                    }
                    
                    $main_payment_id = $combined_payment_id;
                }
                
                //echo 'Advance Settlement Amount: '.$advance_settlement_amount;exit;
                // Process advance payment separately (if there is any)
                
                if($advance_payment > 0) {
                    
                    if($customer_advance_ledger) {
                        // Create separate payment reference for advance payment
                        $advance_reference_no = $reference_no . '-ADV';
                        $advance_payment_id = $this->add_customer_reference($advance_payment, $advance_reference_no, $date, $note . ' (Advance)', $customer_id, $ledger_account);
                        
                        // Verify payment reference was created successfully
                        if (!$advance_payment_id) {
                            $this->session->set_flashdata('error', 'Failed to create advance payment reference. Please check system configuration.');
                            redirect($_SERVER['HTTP_REFERER']);
                        }
                        
                        // Make advance payment entry  
                        //$this->make_customer_payment(NULL, $advance_payment, $advance_reference_no, $date, $note, $advance_payment_id);
                        
                        // Create journal entry for advance payment
                        $advance_journal_id = $this->convert_customer_payment_advance($customer_id, $ledger_account, $customer_advance_ledger, $advance_payment, $advance_reference_no, 'customeradvance', $date);
                        $this->sales_model->update_payment_reference($advance_payment_id, $advance_journal_id);
                        
                        // Set main payment id to advance if no invoice payment
                        if(!$main_payment_id) {
                            $main_payment_id = $advance_payment_id;
                        }
                    }
                }

                $this->session->set_flashdata('message', lang('Payment processed Successfully!'));
                admin_redirect('customers/view_payment/' . $main_payment_id);
            }

        } else {
            // Check if customer_advance_ledger is configured in settings
            $settings = $this->Settings;
           
            $customer_advance_ledger = isset($settings->customer_advance_ledger) && !empty($settings->customer_advance_ledger) 
                                     ? $settings->customer_advance_ledger 
                                     : null;
            
            $this->data['customers']  = $this->site->getAllCompanies('customer');
            $this->data['warehouses'] = $this->site->getAllWarehouses();
            $this->data['customer_advance_ledger'] = $customer_advance_ledger;
            $this->page_construct('customers/add_payment', $meta, $this->data);
        }
    }

    /**
     * Create accounting entry for customer advance settlement
     * This is used when customer's existing advance is used to pay invoices
     * 
     * @param int $customer_id Customer ID
     * @param int $customer_advance_ledger Customer advance ledger (asset account)
     * @param float $advance_amount Amount of advance being used
     * @param string $reference_no Payment reference
     * @param string $date Transaction date
     * @param string $description Transaction description
     * @return int Entry ID
     */
    private function create_customer_advance_settlement_entry($customer_id, $customer_advance_ledger, $advance_amount, $reference_no, $date, $description) {
        // Get customer details
        $this->load->admin_model('companies_model');
        $customer = $this->companies_model->getCompanyByID($customer_id);
        
        // Create journal entry for advance settlement
        $entry = array(
            'entrytype_id' => 4,
            'transaction_type' => 'advancesettlement',
            'number' => $reference_no,
            'date' => $date,
            'dr_total' => $advance_amount,
            'cr_total' => $advance_amount,
            'notes' => $description . ' for ' . $customer->name,
            'customer_id' => $customer_id
        );
        
        $add = $this->db->insert('sma_accounts_entries', $entry);
        $entry_id = $this->db->insert_id();
        
        if ($add) {
            // Debit customer advance ledger (reducing advance balance - liability decreases)
            $entryitem1 = array(
                'entry_id' => $entry_id,
                'ledger_id' => $customer_advance_ledger,
                'amount' => $advance_amount,
                'dc' => 'D',
                'reconciliation_date' => $date
            );
            $this->db->insert('sma_accounts_entryitems', $entryitem1);
            
            // Credit customer ledger (reducing customer receivable - asset decreases)
            $entryitem2 = array(
                'entry_id' => $entry_id,
                'ledger_id' => $customer->ledger_account,
                'amount' => $advance_amount,
                'dc' => 'C',
                'reconciliation_date' => $date
            );
            $this->db->insert('sma_accounts_entryitems', $entryitem2);
            
            return $entry_id;
        }
        
        return false;
    }

    public function add_credit_memo($memo_id, $customer_id, $reference_no, $description, $payment_amount, $date){
        $memoData = array(
            'memo_id' => $memo_id,
            'supplier_id' => 0,
            'customer_id' => $customer_id,
            'reference_no' => $reference_no,
            'description' => $description,
            'payment_amount' => $payment_amount,
            'type' => 'creditmemo',
            'date' => $date
        );
        $this->db->insert('sma_memo_entries' ,$memoData);
    }

    public function convert_credit_memo_invoice($memo_id, $customer_id, $ledger_account, $vat_account, $payment_amount, $vat_percent, $reference_no, $type, $date, $customer_entry_type = 'C'){
        $this->load->admin_model('companies_model');
        $customer = $this->companies_model->getCompanyByID($customer_id);

        // Calculate VAT amount based on percentage
        $vat_percent = (float)$vat_percent;
        $vat_amount = ($payment_amount * $vat_percent) / 100;
        
        /*Accounts Entries*/
        $entry = array(
            'entrytype_id' => 4,
            'transaction_type' => $type,
            'number'       => 'CM-'.$reference_no,
            'date'         => $date,
            'dr_total'     => $payment_amount + $vat_amount,
            'cr_total'     => $payment_amount + $vat_amount,
            'notes'        => 'Credit Memo Reference: '.$reference_no.' Date: '.date('Y-m-d H:i:s'),
            'pid'          =>  '',
            'memo_id'      => $memo_id,
            'customer_id'  => $customer_id
            );


        $add  = $this->db->insert('sma_accounts_entries', $entry);
        $insert_id = $this->db->insert_id();

        // Determine entry direction: Normal (C) or Reversed (D)
        // If customer is debited (D), reverse all other entries
        $vat_dc = ($customer_entry_type == 'C') ? 'D' : 'C';
        $ledger_dc = ($customer_entry_type == 'C') ? 'D' : 'C';

        //customer - credit or debit based on selection
        $entryitemdata[] = array(
            'Entryitem' => array(
                'entry_id' => $insert_id,
                'dc' => $customer_entry_type, // C or D based on user selection
                'ledger_id' => $customer->ledger_account,
                'amount' => $payment_amount + $vat_amount,
                'narration' => ''
            )
        );

        // Only create VAT entry if VAT amount > 0
        if($vat_amount > 0){
            //vat charges - debit when normal, credit when reversed
            $entryitemdata[] = array(
                'Entryitem' => array(
                    'entry_id' => $insert_id,
                    'dc' => $vat_dc,
                    'ledger_id' => $vat_account,
                    'amount' => $vat_amount,
                    'narration' => ''
                )
            );
        }

        //transfer ledger - debit when normal, credit when reversed
        $entryitemdata[] = array(
            'Entryitem' => array(
                'entry_id' => $insert_id,
                'dc' => $ledger_dc,
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

    public function list_credit_memo(){
        $this->data['credit_memo'] = $this->purchases_model->getCreditMemo('creditmemo');
        $this->data['suppliers']  = $this->site->getAllCompanies('supplier');
        $this->page_construct('customers/list_credit_memo', $meta, $this->data);
    }

    public function delete_credit_memo($id = null){
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
            
            $this->session->set_flashdata('message', lang('Credit Memo deleted successfully!'));
        } else {
            $this->session->set_flashdata('error', lang('No memo ID provided'));
        }
        
        admin_redirect('customers/list_credit_memo');
    }

    public function view_credit_memo($id = null){
        //$this->sma->checkPermissions();

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }

        $credit_memo_data = $this->purchases_model->getDebitMemoData($id);
        $credit_memo_entries_data = $this->purchases_model->getDebitMemoEntriesData($id);

        // Get ledger data
        $ledgers = $this->site->getCompanyLedgers();
        $ledger_options = [];
        foreach($ledgers as $ledger){
            $ledger_options[$ledger->id] = $ledger->name;
        }

        $bc    = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('customers/list_credit_memo'), 'page' => lang('Credit Memo List')], ['link' => '#', 'page' => lang('View Customer Memo')]];
        $meta = ['page_title' => lang('View Customer Memo'), 'bc' => $bc];
        
        $this->data['memo_data'] = $credit_memo_data;
        $this->data['memo_entries_data'] = $credit_memo_entries_data;
        $this->data['ledger_options'] = $ledger_options;
        
        $this->page_construct('customers/view_credit_memo', $meta, $this->data);
    }

    public function edit_credit_memo($id = null){
        //$this->sma->checkPermissions(false, true);

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }

        $credit_memo_data = $this->purchases_model->getDebitMemoData($id);
        $credit_memo_entries_data = $this->purchases_model->getDebitMemoEntriesData($id);

        $bc    = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('customers/list_credit_memo'), 'page' => lang('Credit Memo List')], ['link' => '#', 'page' => lang('Edit Credit Memo')]];
        $meta = ['page_title' => lang('Edit Credit Memo'), 'bc' => $bc];

        $this->data['memo_data'] = $credit_memo_data;

        $this->data['memo_entries_data'] = $credit_memo_entries_data;
        $this->data['customers']  = $this->site->getAllCompanies('customer');
        $this->page_construct('customers/credit_memo', $meta, $this->data);
    }

    public function credit_memo(){
        //$this->sma->checkPermissions(false, true);
        $this->form_validation->set_rules('customer', $this->lang->line('customer'), 'required');

        $data = [];
        $bc    = [['link' => base_url(), 'page' => lang('home')], ['link' => '#', 'page' => lang('credit memo')]];
        $meta = ['page_title' => lang('Credit Memo'), 'bc' => $bc];
        if ($this->form_validation->run() == true) {
            $request_type = $this->input->post('request_type');
            $customer_id      = $this->input->post('customer');
            $payments_array      = $this->input->post('payment_amount');
            $descriptions_array      = $this->input->post('description');
            $item_ids = $this->input->post('item_id');
            $reference_no = $this->input->post('reference_no');
            $payment_total = $this->input->post('payment_total');
            $ledger_account = $this->input->post('ledger_account');
            $vat_account = $this->input->post('vat_account');
            $vat_percent = $this->input->post('vat_percent');
            $customer_entry_type = $this->input->post('customer_entry_type') ?: 'C';
            $date_fmt = $this->input->post('date');

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
                'supplier_id' => 0,
                'customer_id' => $customer_id,
                'reference_no' => $reference_no,
                'payment_amount' => $payment_total,
                'vat_percent' => $vat_percent,
                'ledger_account' => $ledger_account,
                'vat_account' => $vat_account,
                'customer_entry_type' => $customer_entry_type,
                'type' => 'creditmemo',
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
                    $this->add_credit_memo($memo_id, $customer_id, $reference_no, $description, $payment_amount, $date);
                }
            }

            $this->convert_credit_memo_invoice($memo_id, $customer_id, $ledger_account, $vat_account, $payment_total, $vat_percent, $reference_no, 'creditmemo', $date, $customer_entry_type);
            $this->session->set_flashdata('message', lang('Credit Memo invoice added Successfully!'));
            admin_redirect('customers/list_credit_memo');
        } else {
            $this->data['customers']  = $this->site->getAllCompanies('customer');
            $this->data['warehouses'] = $this->site->getAllWarehouses();
            $this->page_construct('customers/credit_memo', $meta, $this->data);
        }
    }

    public function add_service_invoice($memo_id, $customer_id, $reference_no, $description, $payment_amount, $date){
        $memoData = array(
            'memo_id' => $memo_id,
            'customer_id' => $customer_id,
            'reference_no' => $reference_no,
            'description' => $description,
            'payment_amount' => $payment_amount,
            'type' => 'serviceinvoice',
            'date' => $date
        );
        $this->db->insert('sma_memo_entries' ,$memoData);
    }

    public function convert_service_invoice($memo_id, $customer_id, $ledger_account, $vat_account, $payment_amount, $vat_charges, $reference_no, $type){
        $this->load->admin_model('companies_model');
        $customer = $this->companies_model->getCompanyByID($customer_id);

        /*Accounts Entries*/
        $entry = array(
            'entrytype_id' => 4,
            'transaction_type' => $type,
            'number'       => 'SI-'.$reference_no,
            'date'         => date('Y-m-d'),
            'dr_total'     => $payment_amount + $vat_charges,
            'cr_total'     => $payment_amount + $vat_charges,
            'notes'        => 'Service Invoice Reference: '.$reference_no.' Date: '.date('Y-m-d H:i:s'),
            'pid'          =>  '',
            'memo_id'      => $memo_id
            );
        $add  = $this->db->insert('sma_accounts_entries', $entry);
        $insert_id = $this->db->insert_id();

        //customer
        $entryitemdata[] = array(
            'Entryitem' => array(
                'entry_id' => $insert_id,
                'dc' => 'D',
                'ledger_id' => $customer->ledger_account,
                'amount' => $payment_amount + $vat_charges,
                'narration' => ''
            )
        );

        //vat charges
        $entryitemdata[] = array(
            'Entryitem' => array(
                'entry_id' => $insert_id,
                'dc' => 'C',
                'ledger_id' => $vat_account,
                'amount' => $vat_charges,
                'narration' => ''
            )
        );

        //transfer legdger
        $entryitemdata[] = array(
            'Entryitem' => array(
                'entry_id' => $insert_id,
                'dc' => 'C',
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

    public function list_service_invoice(){
        $this->data['service_invoices'] = $this->purchases_model->getCreditMemo('serviceinvoice');
        $this->data['suppliers']  = $this->site->getAllCompanies('supplier');
        $this->page_construct('customers/list_service_invoice', $meta, $this->data);
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
        $this->data['customers']  = $this->site->getAllCompanies('customer');
        $this->page_construct('customers/service_invoice', $meta, $this->data);
    }

    public function service_invoice(){
        //$this->sma->checkPermissions(false, true);
        $this->form_validation->set_rules('customer', $this->lang->line('customer'), 'required');

        $data = [];
        $bc    = [['link' => base_url(), 'page' => lang('home')], ['link' => '#', 'page' => lang('Service Invoice')]];
        $meta = ['page_title' => lang('Service Invoice'), 'bc' => $bc];
        if ($this->form_validation->run() == true) {
            $request_type = $this->input->post('request_type');
            $customer_id      = $this->input->post('customer');
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
                    'customer_id' => $customer_id,
                    'reference_no' => $reference_no,
                    'payment_amount' => $payment_total,
                    'bank_charges' => $vat_charges,
                    'ledger_account' => $ledger_account,
                    'bank_charges_account' => $vat_account,
                    'type' => 'serviceinvoice',
                    'date' => $date
                );

                $this->db->insert('sma_memo' ,$memoData);
                $memo_id = $this->db->insert_id();

                $this->add_service_invoice($memo_id, $customer_id, $reference_no, $description, $payment_total, $date);
            }

            $this->convert_service_invoice($memo_id, $customer_id, $ledger_account, $vat_account, $payment_total, $vat_charges, $reference_no, 'serviceinvoice');
            $this->session->set_flashdata('message', lang('Service Invoice added Successfully!'));
            admin_redirect('customers/list_service_invoice');

        } else {
            $this->data['customers']  = $this->site->getAllCompanies('customer');
            $this->data['warehouses'] = $this->site->getAllWarehouses();
            $this->page_construct('customers/service_invoice', $meta, $this->data);
        }
    }

    public function add()
    {
        //$this->sma->checkPermissions(false, true);

        if (!$this->Owner && !$this->Admin && !$this->GP['customers-add']) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            admin_redirect($_SERVER['HTTP_REFERER']);
        }

        $this->form_validation->set_rules('name', $this->lang->line('name'), 'required');
        $this->form_validation->set_rules('name_ar', $this->lang->line('name_ar'), 'required');
        $this->form_validation->set_rules('address', $this->lang->line('address'), 'required');

        $company_sales_man = $this->companies_model->getAllSalesMan();

        //$this->form_validation->set_rules('email', lang('email_address'), 'is_unique[companies.email]');

        if ($this->form_validation->run() == true) {
            $cg   = $this->site->getCustomerGroupByID($this->input->post('customer_group'));
            $pg   = $this->site->getPriceGroupByID($this->input->post('price_group'));
            $data = [
                'name'                => $this->input->post('name'),
                'name_ar'                => $this->input->post('name_ar'),
                'email'               => $this->input->post('email'),
                'group_id'            => '3',
                'group_name'          => 'customer',
                'customer_group_id'   => $this->input->post('customer_group'),
                'customer_group_name' => $cg->name,
                'price_group_id'      => $this->input->post('price_group') ? $this->input->post('price_group') : null,
                'price_group_name'    => $this->input->post('price_group') ? $pg->name : null,
                'credit_limit'        => $this->input->post('credit_limit') ? $this->input->post('credit_limit') : '0',
                'payment_term'        => $this->input->post('payment_term') ? $this->input->post('payment_term') : '0', 
                'company'             => $this->input->post('company'),
                'address'             => $this->input->post('address'),
                'vat_no'              => $this->input->post('vat_no'),
                'city'                => $this->input->post('city'),
                'state'               => $this->input->post('state'),
                'postal_code'         => $this->input->post('postal_code'),
                'country'             => $this->input->post('country'),
                'phone'               => $this->input->post('phone'),
                'cf1'                 => $this->input->post('cf1'),
                'cf2'                 => $this->input->post('cf2'),
                'cf3'                 => $this->input->post('cf3'),
                'cf4'                 => $this->input->post('cf4'),
                'cf5'                 => $this->input->post('cf5'),
                'cf6'                 => $this->input->post('cf6'),
                'gst_no'              => $this->input->post('gst_no'),
                'ledger_account'      => $this->input->post('ledger_account'),
                //'fund_books_ledger'   => $this->input->post('fund_books_ledger'),
                //'credit_card_ledger'  => $this->input->post('credit_card_ledger'),
                'cogs_ledger'         => $this->input->post('cogs_ledger'),
                //'inventory_ledger'    => $this->input->post('inventory_ledger'),
                'sales_ledger'        => $this->input->post('sales_ledger'),
                //'price_difference_ledger'   => $this->input->post('price_difference_ledger'),
                'discount_ledger'     => $this->input->post('discount_ledger'),
                'return_ledger'     => $this->input->post('return_ledger'),
                //'vat_on_sales_ledger' => $this->input->post('vat_on_sales_ledger'),
                'sequence_code'       => $this->sequenceCode->generate('CUS', 5),
                'gln'                 => $this->input->post('gln'),
                'cr'                  => $this->input->post('cr'),
                'short_address'        => $this->input->post('short_address'),
                'building_number'   => $this->input->post('building_number'),
                'unit_number'  => $this->input->post('unit_number'),
                'additional_number'    => $this->input->post('additional_number'),
                'promessory_note_amount'    => $this->input->post('promessory_note_amount'),
                'category'    => $this->input->post('category'),
                'sales_agent'    => $this->input->post('sales_agent'),
                'cr_expiration'  => $this->input->post('cr_expiration'),
                'sfda_certificate' => $this->input->post('sfda_certificate')
            ];
        } elseif ($this->input->post('add_customer')) {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect('customers');
        }

        if ($this->form_validation->run() == true && $cid = $this->companies_model->addCompany($data)) {
            $this->session->set_flashdata('message', lang('customer_added'));
            $ref = isset($_SERVER['HTTP_REFERER']) ? explode('?', $_SERVER['HTTP_REFERER']) : null;
            admin_redirect($ref[0] . '?customer=' . $cid);
        } else {
            $this->data['error']           = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['modal_js']        = $this->site->modal_js();
            $this->data['customer_groups'] = $this->companies_model->getAllCustomerGroups();
            $this->data['company_sales_man'] = $company_sales_man;
            $this->data['price_groups']    = $this->companies_model->getAllPriceGroups();
            $this->load->view($this->theme . 'customers/add', $this->data);
        }
    }

    public function add_address($company_id = null)
    {
        //$this->sma->checkPermissions('add', true);
        $company = $this->companies_model->getCompanyByID($company_id);

        $this->form_validation->set_rules('line1', lang('line1'), 'required');
        $this->form_validation->set_rules('city', lang('city'), 'required');
        $this->form_validation->set_rules('state', lang('state'), 'required');
        $this->form_validation->set_rules('country', lang('country'), 'required');
        $this->form_validation->set_rules('phone', lang('phone'), 'required');

        if ($this->form_validation->run() == true) {
            $data = [
                'line1'       => $this->input->post('line1'),
                'line2'       => $this->input->post('line2'),
                'city'        => $this->input->post('city'),
                'postal_code' => $this->input->post('postal_code'),
                'state'       => $this->input->post('state'),
                'country'     => $this->input->post('country'),
                'phone'       => $this->input->post('phone'),
                'company_id'  => $company->id,
            ];
        } elseif ($this->input->post('add_address')) {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect('customers');
        }

        if ($this->form_validation->run() == true && $this->companies_model->addAddress($data)) {
            $this->session->set_flashdata('message', lang('address_added'));
            admin_redirect('customers');
        } else {
            $this->data['error']    = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['modal_js'] = $this->site->modal_js();
            $this->data['company']  = $company;
            $this->load->view($this->theme . 'customers/add_address', $this->data);
        }
    }

    public function add_deposit($company_id = null)
    {
        //$this->sma->checkPermissions('deposits', true);

        if ($this->input->get('id')) {
            $company_id = $this->input->get('id');
        }
        $company = $this->companies_model->getCompanyByID($company_id);

        if ($this->Owner || $this->Admin) {
            $this->form_validation->set_rules('date', lang('date'), 'required');
        }
        $this->form_validation->set_rules('amount', lang('amount'), 'required|numeric');

        if ($this->form_validation->run() == true) {
            if ($this->Owner || $this->Admin) {
                $date = $this->sma->fld(trim($this->input->post('date')));
            } else {
                $date = date('Y-m-d H:i:s');
            }
            $data = [
                'date'       => $date,
                'amount'     => $this->input->post('amount'),
                'paid_by'    => $this->input->post('paid_by'),
                'note'       => $this->input->post('note'),
                'company_id' => $company->id,
                'created_by' => $this->session->userdata('user_id'),
            ];

            $cdata = [
                'deposit_amount' => ($company->deposit_amount + $this->input->post('amount')),
            ];
        } elseif ($this->input->post('add_deposit')) {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect('customers');
        }

        if ($this->form_validation->run() == true && $this->companies_model->addDeposit($data, $cdata)) {
            $this->session->set_flashdata('message', lang('deposit_added'));
            admin_redirect('customers');
        } else {
            $this->data['error']    = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['modal_js'] = $this->site->modal_js();
            $this->data['company']  = $company;
            $this->load->view($this->theme . 'customers/add_deposit', $this->data);
        }
    }

    public function add_user($company_id = null)
    {
        //$this->sma->checkPermissions(false, true);

        if ($this->input->get('id')) {
            $company_id = $this->input->get('id');
        }
        $company = $this->companies_model->getCompanyByID($company_id);

        $this->form_validation->set_rules('email', lang('email_address'), 'is_unique[users.email]');
        $this->form_validation->set_rules('password', lang('password'), 'required|min_length[8]|max_length[20]|matches[password_confirm]');
        $this->form_validation->set_rules('password_confirm', lang('confirm_password'), 'required');

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
            admin_redirect('customers');
        }

        if ($this->form_validation->run() == true && $this->ion_auth->register($username, $password, $email, $additional_data, $active, $notify)) {
            $this->session->set_flashdata('message', lang('user_added'));
            admin_redirect('customers');
        } else {
            $this->data['error']    = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['modal_js'] = $this->site->modal_js();
            $this->data['company']  = $company;
            $this->load->view($this->theme . 'customers/add_user', $this->data);
        }
    }

    public function addresses($company_id = null)
    {
        //$this->sma->checkPermissions('index', true);
        $this->data['modal_js']  = $this->site->modal_js();
        $this->data['company']   = $this->companies_model->getCompanyByID($company_id);
        $this->data['addresses'] = $this->companies_model->getCompanyAddresses($company_id);
        $this->load->view($this->theme . 'customers/addresses', $this->data);
    }

    public function customer_actions()
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
                        if (!$this->companies_model->deleteCustomer($id)) {
                            $error = true;
                        }
                    }
                    if ($error) {
                        $this->session->set_flashdata('warning', lang('customers_x_deleted_have_sales'));
                    } else {
                        $this->session->set_flashdata('message', lang('customers_deleted'));
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
                    $this->excel->getActiveSheet()->SetCellValue('K1', lang('CR EXPIRATION'));
                    $this->excel->getActiveSheet()->SetCellValue('L1', lang('SFDA'));
                    $this->excel->getActiveSheet()->SetCellValue('M1', lang('PROMESSORY NOTE AMOUNT'));
                    $this->excel->getActiveSheet()->SetCellValue('N1', lang('SALES AGENT'));

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
                        $this->excel->getActiveSheet()->SetCellValue('K' . $row, $customer->cr_expiration);
                        
                        // Set SFDA as TEXT as well (might be numeric)
                        $this->excel->getActiveSheet()->setCellValueExplicit('L' . $row, $customer->sfda, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                        
                        $this->excel->getActiveSheet()->SetCellValue('M' . $row, $customer->promessory_note_amount);
                        $this->excel->getActiveSheet()->SetCellValue('N' . $row, $customer->sales_agent);
                        
                        $row++;
                    }

                    $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
                    $this->excel->getDefaultStyle()->getAlignment()->setVertical('center');
                    $filename = 'customers_' . date('Y_m_d_H_i_s');
                    $this->load->helper('excel');
                    create_excel($this->excel, $filename);
                }
            } else {
                $this->session->set_flashdata('error', lang('no_customer_selected'));
                redirect($_SERVER['HTTP_REFERER']);
            }
        } else {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER['HTTP_REFERER']);
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

        if ($this->input->get('id') == 1 || $id == 1) {
            $this->sma->send_json(['error' => 1, 'msg' => lang('customer_x_deleted')]);
        }

        if ($this->companies_model->deleteCustomer($id)) {
            $this->sma->send_json(['error' => 0, 'msg' => lang('customer_deleted')]);
        } else {
            $this->sma->send_json(['error' => 1, 'msg' => lang('customer_x_deleted_have_sales')]);
        }
    }

    public function delete_address($id)
    {
        //$this->sma->checkPermissions('delete', true);
        if (!$id) {
            $this->sma->send_json(['error' => 1, 'msg' => lang('id_not_found')]);
        }

        if ($this->companies_model->deleteAddress($id)) {
            $this->session->set_flashdata('message', lang('address_deleted'));
            admin_redirect('customers');
        }
    }

    public function delete_deposit($id)
    {
       // $this->sma->checkPermissions(null, true);
        if (!$id) {
            $this->sma->send_json(['error' => 1, 'msg' => lang('id_not_found')]);
        }
        if ($this->companies_model->deleteDeposit($id)) {
            $this->sma->send_json(['error' => 0, 'msg' => lang('deposit_deleted')]);
        }
    }

    public function deposit_note($id = null)
    {
        //$this->sma->checkPermissions('deposits', true);
        $deposit                  = $this->companies_model->getDepositByID($id);
        $this->data['customer']   = $this->companies_model->getCompanyByID($deposit->company_id);
        $this->data['deposit']    = $deposit;
        $this->data['page_title'] = $this->lang->line('deposit_note');
        $this->load->view($this->theme . 'customers/deposit_note', $this->data);
    }

    public function deposits($company_id = null)
    {
        //$this->sma->checkPermissions(false, true);

        if ($this->input->get('id')) {
            $company_id = $this->input->get('id');
        }

        $this->data['error']    = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
        $this->data['modal_js'] = $this->site->modal_js();
        $this->data['company']  = $this->companies_model->getCompanyByID($company_id);
        $this->load->view($this->theme . 'customers/deposits', $this->data);
    }

    public function edit($id = null)
    {
        //$this->sma->checkPermissions(false, true);

        if (!$this->Owner && !$this->Admin && !$this->GP['customers-edit']) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            admin_redirect($_SERVER['HTTP_REFERER']);
        }

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }

        $company_details = $this->companies_model->getCompanyByID($id);
        $company_sales_man = $this->companies_model->getAllSalesMan();
        $sales_man = $this->companies_model->getSalesManByName($company_details->sales_agent);

        $this->form_validation->set_rules('name', $this->lang->line('name'), 'required');
        $this->form_validation->set_rules('name_ar', $this->lang->line('name_ar'), 'required');
        $this->form_validation->set_rules('address', $this->lang->line('address'), 'required');

        if ($this->input->post('email') != $company_details->email) {
            //$this->form_validation->set_rules('code', lang('email_address'), 'is_unique[companies.email]');
        }

        if ($this->form_validation->run() == true) {
            $cg   = $this->site->getCustomerGroupByID($this->input->post('customer_group'));
            $pg   = $this->site->getPriceGroupByID($this->input->post('price_group'));
            $data = [
                'name'                => $this->input->post('name'),
                'name_ar'                => $this->input->post('name_ar'),
                'email'               => $this->input->post('email'),
                'group_id'            => '3',
                'group_name'          => 'customer',
                'customer_group_id'   => $this->input->post('customer_group'),
                'customer_group_name' => $cg->name,
                'price_group_id'      => $this->input->post('price_group') ? $this->input->post('price_group') : null,
                'price_group_name'    => $this->input->post('price_group') ? $pg->name : null,
                'credit_limit'        => $this->input->post('credit_limit') ? $this->input->post('credit_limit') : '0',
                'payment_term'        => $this->input->post('payment_term') ? $this->input->post('payment_term') : '0',
                'company'             => $this->input->post('company'),
                'address'             => $this->input->post('address'),
                'vat_no'              => $this->input->post('vat_no'),
                'city'                => $this->input->post('city'),
                'state'               => $this->input->post('state'),
                'postal_code'         => $this->input->post('postal_code'),
                'country'             => $this->input->post('country'),
                'phone'               => $this->input->post('phone'),
                'cf1'                 => $this->input->post('cf1'),
                'cf2'                 => $this->input->post('cf2'),
                'cf3'                 => $this->input->post('cf3'),
                'cf4'                 => $this->input->post('cf4'),
                'cf5'                 => $this->input->post('cf5'),
                'cf6'                 => $this->input->post('cf6'),
                'award_points'        => $this->input->post('award_points'),
                'gst_no'              => $this->input->post('gst_no'),
                'ledger_account'      => $this->input->post('ledger_account'),
                //'fund_books_ledger'   => $this->input->post('fund_books_ledger'),
                //'credit_card_ledger'  => $this->input->post('credit_card_ledger'),
                'cogs_ledger'         => $this->input->post('cogs_ledger'),
                //'inventory_ledger'    => $this->input->post('inventory_ledger'),
                'sales_ledger'        => $this->input->post('sales_ledger'),
                //'price_difference_ledger'   => $this->input->post('price_difference_ledger'),
                'discount_ledger'     => $this->input->post('discount_ledger'),
                //'vat_on_sales_ledger' => $this->input->post('vat_on_sales_ledger')
                'return_ledger'       => $this->input->post('return_ledger'),
                'gln'                 => $this->input->post('gln'),
                'cr'                  => $this->input->post('cr'),
                'short_address'        => $this->input->post('short_address'),
                'building_number'   => $this->input->post('building_number'),
                'unit_number'  => $this->input->post('unit_number'),
                'additional_number'    => $this->input->post('additional_number'),
                'promessory_note_amount'    => $this->input->post('promessory_note_amount'),
                'category'    => $this->input->post('category'),
                'sales_agent'    => $this->input->post('sales_agent'),
                'cr_expiration'  => $this->input->post('cr_expiration'),
                'sfda_certificate' => $this->input->post('sfda_certificate')

            ];
        } elseif ($this->input->post('edit_customer')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER['HTTP_REFERER']);
        }

        if ($this->form_validation->run() == true && $this->companies_model->updateCompany($id, $data)) {
            $this->session->set_flashdata('message', lang('customer_updated'));
            redirect($_SERVER['HTTP_REFERER']);
        } else {
            $this->data['company_sales_man'] = $company_sales_man; 
            $this->data['sales_man_info'] = $sales_man;
            //echo '<pre>';print_r($this->data['company_sales_man']);exit;
            $this->data['customer']        = $company_details;
            $this->data['error']           = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['modal_js']        = $this->site->modal_js();
            $this->data['customer_groups'] = $this->companies_model->getAllCustomerGroups();
            $this->data['price_groups']    = $this->companies_model->getAllPriceGroups();
            $this->load->view($this->theme . 'customers/edit', $this->data);
        }
    }

    public function edit_address($id = null)
    {
        //$this->sma->checkPermissions('edit', true);

        $this->form_validation->set_rules('line1', lang('line1'), 'required');
        $this->form_validation->set_rules('city', lang('city'), 'required');
        $this->form_validation->set_rules('state', lang('state'), 'required');
        $this->form_validation->set_rules('country', lang('country'), 'required');
        $this->form_validation->set_rules('phone', lang('phone'), 'required');

        if ($this->form_validation->run() == true) {
            $data = [
                'line1'       => $this->input->post('line1'),
                'line2'       => $this->input->post('line2'),
                'city'        => $this->input->post('city'),
                'postal_code' => $this->input->post('postal_code'),
                'state'       => $this->input->post('state'),
                'country'     => $this->input->post('country'),
                'phone'       => $this->input->post('phone'),
                'updated_at'  => date('Y-m-d H:i:s'),
            ];
        } elseif ($this->input->post('edit_address')) {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect('customers');
        }

        if ($this->form_validation->run() == true && $this->companies_model->updateAddress($id, $data)) {
            $this->session->set_flashdata('message', lang('address_updated'));
            admin_redirect('customers');
        } else {
            $this->data['modal_js'] = $this->site->modal_js();
            $this->data['address']  = $this->companies_model->getAddressByID($id);
            $this->load->view($this->theme . 'customers/edit_address', $this->data);
        }
    }

    public function edit_deposit($id = null)
    {
        //$this->sma->checkPermissions('deposits', true);

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $deposit = $this->companies_model->getDepositByID($id);
        $company = $this->companies_model->getCompanyByID($deposit->company_id);

        if ($this->Owner || $this->Admin) {
            $this->form_validation->set_rules('date', lang('date'), 'required');
        }
        $this->form_validation->set_rules('amount', lang('amount'), 'required|numeric');

        if ($this->form_validation->run() == true) {
            if ($this->Owner || $this->Admin) {
                $date = $this->sma->fld(trim($this->input->post('date')));
            } else {
                $date = $deposit->date;
            }
            $data = [
                'date'       => $date,
                'amount'     => $this->input->post('amount'),
                'paid_by'    => $this->input->post('paid_by'),
                'note'       => $this->input->post('note'),
                'company_id' => $deposit->company_id,
                'updated_by' => $this->session->userdata('user_id'),
                'updated_at' => $date = date('Y-m-d H:i:s'),
            ];

            $cdata = [
                'deposit_amount' => (($company->deposit_amount - $deposit->amount) + $this->input->post('amount')),
            ];
        } elseif ($this->input->post('edit_deposit')) {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect('customers');
        }

        if ($this->form_validation->run() == true && $this->companies_model->updateDeposit($id, $data, $cdata)) {
            $this->session->set_flashdata('message', lang('deposit_updated'));
            admin_redirect('customers');
        } else {
            $this->data['error']    = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['modal_js'] = $this->site->modal_js();
            $this->data['company']  = $company;
            $this->data['deposit']  = $deposit;
            $this->load->view($this->theme . 'customers/edit_deposit', $this->data);
        }
    }

    public function get_award_points($id = null)
    {
        //$this->sma->checkPermissions('index');
        $row = $this->companies_model->getCompanyByID($id);
        $this->sma->send_json(['ca_points' => $row->award_points]);
    }

    public function get_customer_details($id = null)
    {
        $this->sma->send_json($this->companies_model->getCompanyByID($id));
    }

    public function get_deposits($company_id = null)
    {
        //$this->sma->checkPermissions('deposits');
        $this->load->library('datatables');
        $this->datatables
            ->select("deposits.id as id, date, amount, paid_by, CONCAT({$this->db->dbprefix('users')}.first_name, ' ', {$this->db->dbprefix('users')}.last_name) as created_by", false)
            ->from('deposits')
            ->join('users', 'users.id=deposits.created_by', 'left')
            ->where($this->db->dbprefix('deposits') . '.company_id', $company_id)
            ->add_column('Actions', "<div class=\"text-center\"><a class=\"tip\" title='" . lang('deposit_note') . "' href='" . admin_url('customers/deposit_note/$1') . "' data-toggle='modal' data-target='#myModal2'><i class=\"fa fa-file-text-o\"></i></a> <a class=\"tip\" title='" . lang('edit_deposit') . "' href='" . admin_url('customers/edit_deposit/$1') . "' data-toggle='modal' data-target='#myModal2'><i class=\"fa fa-edit\"></i></a> <a href='#' class='tip po' title='<b>" . lang('delete_deposit') . "</b>' data-content=\"<p>" . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . admin_url('customers/delete_deposit/$1') . "'>" . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i></a></div>", 'id')
        ->unset_column('id');
        echo $this->datatables->generate();
    }

    public function getCustomer($id = null)
    {
        // $this->sma->checkPermissions('index');
        $row = $this->companies_model->getCompanyByID($id);
        $this->sma->send_json([['id' => $row->id, 'text' => ($row->company && $row->company != '-' ? $row->company : $row->name), 'payment_term' => $row->payment_term]]);
    }

    public function getCustomers()
    {

        $actions = "<div class=\"text-center\">"; 
        if($this->Owner || $this->Admin || $this->GP['customers-edit']){
            $actions .= "<a class=\"tip\" title='" . lang('edit_customer') . "' href='" . admin_url('customers/edit/$1') . "' data-toggle='modal' data-target='#myModal'><i class=\"fa fa-edit\"></i></a>";
        }

        if($this->Owner || $this->Admin || $this->GP['customers-delete']){
            $actions .= "<a href='#' class='tip po' title='<b>" . lang('delete_customer') . "</b>' data-content=\"<p>" . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . admin_url('customers/delete/$1') . "'>" . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i></a>";
        }
        $actions .= "</div>";

        //$this->sma->checkPermissions('index');
        $this->load->library('datatables');

        $this->datatables
            ->select('id, sequence_code, name, vat_no, gln, cr, short_address, address, credit_limit, payment_term, category')
            ->from('companies')
            ->where('group_name', 'customer')
            ->add_column('Actions', $actions, 'id');
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
            'category' => 'Classification'
        ];
    }

    public function import_excel()
    {
        //$this->sma->checkPermissions('add', true);
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
                    admin_redirect('customers');
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
                $bc = [['link' => base_url(), 'page' => lang('customers_mapper')], ['link' => admin_url('customers'), 'page' => lang('customers_mapper')], ['link' => '#', 'page' => lang('customers_mapper')]];
                $meta = ['page_title' => lang('customers_mapper'), 'bc' => $bc];
                $this->page_construct('customers/map_fields', $meta, $this->data);
                
            }
        }else{
             $this->data['error']    = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'customers/import_excel', $this->data);
        }
    }

    /*public function process_import()
    {
        //$this->sma->checkPermissions('add', true);
        $mapping = $this->input->post('mapping');
        $filePath = $this->input->post('file_path');
        $spreadsheet = IOFactory::load($filePath);
        $rows = $spreadsheet->getActiveSheet()->toArray();
    
        array_shift($rows); // remove header row

        $imported = 0;
        $errors = 0;
        
        foreach ($rows as $row) {
            echo '<pre>';print_r($row);exit;
            $data = [];
            foreach ($mapping as $index => $field) {
                if (!empty($field)) {
                    $data[$field] = trim($row[$index]);
                }
            }

            if (empty($data['name'])) continue; // skip invalid rows
            
            $exists = $this->db->get_where('companies', ['name' => $data['name'], 'group_name' => 'customer'])->row();

            if ($exists) {
                $this->db->where('id', $exists->id)->update('companies', $data);
            } else {
                // defaults
                $data['group_id'] = 3;
                $data['group_name'] = 'customer';
                $data['customer_group_id'] = 1;
                $data['customer_group_name'] = 'default';
                $data['country'] = 'Saudi Arabia';
                
                $this->db->insert('companies', $data);
            }

            if ($this->db->affected_rows() > 0) {
                $imported++;
            } else {
                $errors++;
            }
        }

        unlink($filePath);
        $this->session->set_flashdata('message', "Imported: {$imported}, Errors: {$errors}");
        redirect(admin_url('customers'));
    }*/

    public function process_import()
    {
        $mapping  = $this->input->post('mapping');    // mapping array: file column index => db field
        $filePath = $this->input->post('file_path');  // uploaded Excel file path

        $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
        $reader->setReadDataOnly(true);
        $spreadsheet = $reader->load($filePath);
        $sheet       = $spreadsheet->getActiveSheet();

        $imported = 0;
        $errors   = 0;
        $i = 0;

        foreach ($sheet->getRowIterator() as $row) {
            $i++;
            $rowIndex = $row->getRowIndex();
            // Skip header row
            if ($rowIndex == 1) continue;

            $cellIterator = $row->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells(true); // only actual cells

            $rowData = [];
            foreach ($cellIterator as $cell) {
                $value = $cell->getValue();

                // Convert very large numbers to string to avoid scientific notation
                if (is_numeric($value) && strlen((string)$value) > 10) {
                    $value = (string)$value;
                }

                $rowData[] = $value;
            }

            // Apply mapping: map Excel columns to DB fields
            $data = [];
            foreach ($mapping as $index => $field) {
                if (!empty($field) && isset($rowData[$index])) {
                    $data[$field] = trim($rowData[$index]);
                }
            }
           
            // Skip row if 'name' is missing
            if (empty($data['name'])) continue;

            // Check if company already exists
            $exists = $this->db->get_where('companies', [
                'name'       => $data['name'],
                'group_name' => 'customer'
            ])->row();

            if ($exists) {
                // Update existing record
                //echo '<pre>';print_r($data);exit;
                $this->db->where('id', $exists->id)->update('companies', $data);
            } else {
                $seq_code = 'CUS-' . str_pad($i, 5, '0', STR_PAD_LEFT);

                // Insert new record with defaults
                $data['group_id']            = 3;
                $data['group_name']          = 'customer';
                $data['customer_group_id']   = 1;
                $data['customer_group_name'] = 'default';
                $data['country']             = 'Saudi Arabia';
                $data['sequence_code']       = $seq_code;
                $data['level']               = 1;

                $this->db->insert('companies', $data);

                $customer_id = $this->db->insert_id(); // Get newly created customer ID

                // --- Link salesman if found ---
                if (!empty($data['sales_agent'])) {
                    $salesman = $this->db
                        ->select('id')
                        ->where('name', trim($data['sales_agent']))
                        ->get('sma_sales_man')
                        ->row();

                    if ($salesman) {
                        $this->db->insert('sma_customer_saleman', [
                            'customer_id' => $customer_id,
                            'salesman_id' => $salesman->id
                        ]);
                    }
                }
            }

            if ($this->db->affected_rows() > 0) {
                $imported++;
            } else {
                $errors++;
            }
        }

        // Delete uploaded file
        if (file_exists($filePath)) {
            unlink($filePath);
        }

        // Set flash message
        $this->session->set_flashdata('message', "Imported: {$imported}, Errors: {$errors}");
        redirect(admin_url('customers'));
    }


    public function import_csv()
    {
        //$this->sma->checkPermissions('add', true);
        $this->load->helper('security');
        $this->form_validation->set_rules('csv_file', lang('upload_file'), 'xss_clean');

        if ($this->form_validation->run() == true) {
            if (DEMO) {
                $this->session->set_flashdata('warning', lang('disabled_in_demo'));
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
                    admin_redirect('customers');
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
                $titles         = array_shift($arrResult);
                $rw             = 2;
                $updated        = '';
                $data           = [];
                $customer_group = $this->site->getCustomerGroupByID($this->Settings->customer_group);
                $price_group    = $this->site->getPriceGroupByID($this->Settings->price_group);
                foreach ($arrResult as $key => $value) {
                    $customer = [
                        'company'             => isset($value[0]) ? trim($value[0]) : '',
                        'name'                => isset($value[1]) ? trim($value[1]) : '',
                        'name_ar'             => isset($value[2]) ? trim($value[2]) : '',
                        'email'               => isset($value[3]) ? trim($value[3]) : '',
                        'phone'               => isset($value[4]) ? trim($value[4]) : '',
                        'address'             => isset($value[5]) ? trim($value[5]) : '',
                        'city'                => isset($value[6]) ? trim($value[6]) : '',
                        'state'               => isset($value[7]) ? trim($value[7]) : '',
                        'postal_code'         => isset($value[8]) ? trim($value[8]) : '',
                        'country'             => isset($value[9]) ? trim($value[9]) : '',
                        'vat_no'              => isset($value[10]) ? trim($value[10]) : '',
                        'gst_no'              => isset($value[11]) ? trim($value[11]) : '',
                        'cf1'                 => isset($value[12]) ? trim($value[12]) : '',
                        'cf2'                 => isset($value[13]) ? trim($value[13]) : '',
                        'cf3'                 => isset($value[14]) ? trim($value[14]) : '',
                        'cf4'                 => isset($value[15]) ? trim($value[15]) : '',
                        'cf5'                 => isset($value[16]) ? trim($value[16]) : '',
                        'cf6'                 => isset($value[17]) ? trim($value[17]) : '',
                        'payment_term'        => isset($value[18]) ? trim($value[18]) : '',
                        'credit_limit'        => isset($value[19]) ? trim($value[19]) : '', 
                        'group_id'            => 3,
                        'group_name'          => 'customer',
                        'customer_group_id'   => (!empty($customer_group)) ? $customer_group->id : null,
                        'customer_group_name' => (!empty($customer_group)) ? $customer_group->name : null,
                        'price_group_id'      => (!empty($price_group)) ? $price_group->id : null,
                        'price_group_name'    => (!empty($price_group)) ? $price_group->name : null,
                        'sequence_code'       => $this->sequenceCode->generate('CUS', 5)
                    ];
                    if (empty($customer['company']) || empty($customer['name']) || empty($customer['email'])) {
                        $this->session->set_flashdata('error', lang('company') . ', ' . lang('name') . ', ' . lang('email') . ' ' . lang('are_required') . ' (' . lang('line_no') . ' ' . $rw . ')');
                        admin_redirect('customers');
                    } else {
                        if ($this->Settings->indian_gst && empty($customer['state'])) {
                            $this->session->set_flashdata('error', lang('state') . ' ' . lang('is_required') . ' (' . lang('line_no') . ' ' . $rw . ')');
                            admin_redirect('customers');
                        }
                        if ($customer_details = $this->companies_model->getCompanyByEmail($customer['email'])) {
                            if ($customer_details->group_id == 3) {
                                $updated .= '<p>' . lang('customer_updated') . ' (' . $customer['email'] . ')</p>';
                                $this->companies_model->updateCompany($customer_details->id, $customer);
                            }
                        } else {
                            $data[] = $customer;
                        }
                        $rw++;
                    }
                }

                // $this->sma->print_arrays($data, $updated);
            }
        } elseif ($this->input->post('import')) {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect('customers');
        }

        if ($this->form_validation->run() == true && !empty($data)) {
            if ($this->companies_model->addCompanies($data)) {
                $this->session->set_flashdata('message', lang('customers_added') . $updated);
                admin_redirect('customers');
            }
        } else {
            if (isset($data) && empty($data)) {
                if ($updated) {
                    $this->session->set_flashdata('message', $updated);
                } else {
                    $this->session->set_flashdata('warning', lang('data_x_customers'));
                }
                admin_redirect('customers');
            }

            $this->data['error']    = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'customers/import', $this->data);
        }
    }

    public function index($action = null)
    {
        //$this->sma->checkPermissions();

        if (!$this->Owner && !$this->Admin && !$this->GP['customers-index']) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            admin_redirect($_SERVER['HTTP_REFERER']);
        }

        $this->data['error']  = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $this->data['action'] = $action;
        $bc                   = [['link' => base_url(), 'page' => lang('home')], ['link' => '#', 'page' => lang('customers')]];
        $meta                 = ['page_title' => lang('customers'), 'bc' => $bc];
        $this->page_construct('customers/index', $meta, $this->data);
    }

    public function suggestions($term = null, $limit = null, $a = null)
    {
        // $this->sma->checkPermissions('index');
        if ($this->input->get('term')) {
            $term = $this->input->get('term', true);
        }
        if (strlen($term) < 1) {
            return false;
        }
        $term   = addslashes($term);
        $limit  = $this->input->get('limit', true);
        $result = $this->companies_model->getCustomerSuggestions($term, $limit);
        if ($a) {
            $this->sma->send_json($result);
        }
        $rows['results'] = $result;
        $this->sma->send_json($rows);
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
        $this->load->view($this->theme . 'customers/users', $this->data);
    }

    public function view($id = null)
    {
        //$this->sma->checkPermissions('index', true);
        $this->data['error']    = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $this->data['customer'] = $this->companies_model->getCompanyByID($id);
        $this->load->view($this->theme . 'customers/view', $this->data);
    }

    /**
     * Get customer advance balance via AJAX
     * Used by the payment form to show current advance balance
     */
    public function get_customer_advance_balance(){
        try {
            $customer_id = isset($_GET['customer_id']) ? $_GET['customer_id'] : null;
            
            if (!$customer_id) {
                echo json_encode(array(
                    'advance_balance' => 0,
                    'advance_ledger_configured' => false,
                    'error' => 'No customer ID provided'
                ));
                return;
            }
            
            // Get customer advance ledger from settings
            $settings = $this->Settings;
            $customer_advance_ledger = isset($settings->customer_advance_ledger) && !empty($settings->customer_advance_ledger) 
                                     ? $settings->customer_advance_ledger 
                                     : null;
            
            $advance_balance = 0;
            
            if($customer_advance_ledger && $customer_id) {
                $advance_balance = $this->getCustomerAdvanceBalance($customer_id, $customer_advance_ledger);
            }
            
            $data = array(
                'advance_balance' => $advance_balance,
                'advance_ledger_configured' => $customer_advance_ledger ? true : false,
                'customer_id' => $customer_id,
                'advance_ledger' => $customer_advance_ledger
            );
            
            echo json_encode($data);
            
        } catch (Exception $e) {
            // Log the full error for debugging
            log_message('error', 'Customer Advance Balance Error: ' . $e->getMessage());
            log_message('error', 'SQL Error: ' . $this->db->last_query());
            
            echo json_encode(array(
                'advance_balance' => 0,
                'advance_ledger_configured' => false,
                'error' => 'Database error: ' . $e->getMessage(),
                'query' => $this->db->last_query()
            ));
        }
    }

    /**
     * Get customer advance balance from ledger
     * 
     * @param int $customer_id Customer ID
     * @param int $customer_advance_ledger Ledger ID for customer advance
     * @return float Advance balance (Credit - Debit)
     */
    private function getCustomerAdvanceBalance($customer_id, $customer_advance_ledger) {
        if(!$customer_advance_ledger || !$customer_id) {
            return 0;
        }
        
        // Query for advance balance using customer_id field
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
        
        $query = $this->db->get();
        
        if($query->num_rows() > 0) {
            $result = $query->row();
            // For customer advance: Credit means advance received, Debit means advance used
            return $result->credit_total - $result->debit_total;
        }
        
        return 0;
    }

    /**
     * Get ledgers by category
     * Fetch ledgers from existing customers with the same category
     */
    public function get_ledgers_by_category()
    {
        $category = $this->input->post('category');
        
        if (!$category) {
            echo json_encode(['success' => false, 'message' => 'Category is required']);
            return;
        }

        // Get the most recent customer with this category that has ledger data
        $this->db->select('ledger_account, sales_ledger, cogs_ledger, discount_ledger, return_ledger');
        $this->db->from('sma_companies');
        $this->db->where('category', $category);
        $this->db->where('group_name', 'customer');
        $this->db->where('ledger_account IS NOT NULL');
        $this->db->where('ledger_account !=', '');
        $this->db->order_by('id', 'DESC');
        $this->db->limit(1);
        
        $query = $this->db->get();
        
        if ($query->num_rows() > 0) {
            $result = $query->row();
            echo json_encode([
                'success' => true,
                'ledgers' => [
                    'ledger_account' => $result->ledger_account,
                    'sales_ledger' => $result->sales_ledger,
                    'cogs_ledger' => $result->cogs_ledger,
                    'discount_ledger' => $result->discount_ledger,
                    'return_ledger' => $result->return_ledger
                ]
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'No existing customer found with this category'
            ]);
        }
    }
}

