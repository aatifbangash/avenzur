<?php 
defined('BASEPATH') OR exit('No direct script access allowed');

use Mpdf\Mpdf;

class Entries extends MY_Controller 
{
	public function __construct() {
        parent::__construct();
        $this->load->library('form_validation');

		$this->digital_upload_path = 'files/';
        $this->upload_path         = 'assets/uploads/';
        $this->thumbs_path         = 'assets/uploads/thumbs/';
        $this->image_types         = 'gif|jpg|jpeg|png|tif';
        $this->digital_file_types  = 'zip|psd|ai|rar|pdf|doc|docx|xls|xlsx|ppt|pptx|gif|jpg|jpeg|png|tif|txt';
        $this->allowed_file_size   = '1024000';
        $this->data['logo']        = true;
        $this->load->library('attachments', [
            'path'     => $this->digital_upload_path,
            'types'    => $this->digital_file_types,
            'max_size' => $this->allowed_file_size,
        ]);
    }   
    
	public function index() {

		$this->load->library('pagination'); 
		$config['base_url'] = admin_url('entries'); 
		$config['total_rows'] = $this->count_entries();
		$config['per_page'] = 100; 
		$config['reuse_query_string'] = TRUE;
		$this->pagination->initialize($config); 
        $this->data['pagination_links']=  $this->pagination->create_links();  
		
		$eid         = $this->input->get('eid');
        $tran_number   = $this->input->get('tran_number');
		$start_date     = $this->input->get('start_date');
        $end_date     = $this->input->get('end_date');

		if ($start_date) {
			$start_date = $this->sma->fld($start_date);
			$end_date   = $this->sma->fld($end_date);
		}
		if ($start_date && $start_date != "0000-00-00") {
            $this->db->where('date >=', $start_date);       

            if ($end_date  && $end_date != "0000-00-00") {
                $this->db->where('date <=', $end_date);
            }
        }
		if(!empty($eid)){
			$this->db->where("id LIKE '%$eid%'");	
		}
		if(!empty($tran_number)){
			$this->db->where("number LIKE '%$tran_number%'");	
		}

		$this->db->where("transaction_type NOT IN ('purchase_invoice', 'sales_invoice')");

		// Order by newest first
		$this->db->order_by('id', 'DESC');

		// select all entries
		// 	$query = $this->db->get('sma_accounts_entries');
		$page = ($this->uri->segment(3)) ? $this->uri->segment(3) : 0;
		$query = $this->db->limit($config['per_page'], $page)->get('accounts_entries');
		 
		// pass an array of all entries to view
		$this->data['entries'] = $query->result_array();
		
		// render page
		$bc  = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('Entries'), 'page' => lang('Entries')], ['link' => '#', 'page' => lang('Entries')]];
        $meta = ['page_title' => lang('Entries'), 'bc' => $bc];
        $this->page_construct('accounts/entries_index', $meta, $this->data);
	}

	public function upload_trial_balance_by_csv(){
		$this->form_validation->set_rules('ledger', $this->lang->line('ledger'), 'required');
		$this->form_validation->set_rules('csvfile', $this->lang->line('upload_file'), 'xss_clean');

		if ($this->form_validation->run() == true) {
			$ledger = $this->input->post('ledger');
			if (isset($_FILES['csvfile'])) {
                $this->load->library('upload');

                $config['upload_path'] = $this->digital_upload_path;
                $config['allowed_types'] = 'csv';
                $config['max_size'] = $this->allowed_file_size;
                $config['overwrite'] = true;

                $this->upload->initialize($config);
				
                if (!$this->upload->do_upload('csvfile')) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    admin_redirect('entries');
                }
                $csv = $this->upload->file_name;

                $arrResult = [];
                $handle = fopen($this->digital_upload_path . $csv, 'r');
                if ($handle) {
                    while (($row = fgetcsv($handle, 5000, ',')) !== false) {
                        $arrResult[] = $row;
                    }
                    fclose($handle);
                }
                $arr_length = count($arrResult);
                if ($arr_length > 5000000) {
                    $this->session->set_flashdata('error', lang('too_many_records'));
                    redirect($_SERVER['HTTP_REFERER']);
                    exit();
                }

				$parsed = $arrResult;

				$transactions = [];
				
				foreach ($parsed as $row) {
					// Skip rows that look like headers
					if (
						isset($row[0]) &&
						strtolower(trim($row[0])) === 'date' &&
						isset($row[4]) &&
						strtolower(trim($row[4])) === 'debit'
					) {
						continue;
					}
				
					// Skip completely empty rows
					if (empty(array_filter($row))) {
						continue;
					}
				
					// Skip rows that only have account info (e.g., acc no.)
					if (strtolower(trim($row[0])) === 'acc no.') {
						continue;
					}
				
					// Optional: check if first column looks like a date
					if (!preg_match('/\d{1,2}\/\d{1,2}\/\d{4}/', $row[0])) {
						continue;
					}
				
					// Now extract the transaction row
					$transactions[] = [
						'date'        => $row[0] ?? '',
						'jl_no'       => $row[1] ?? '',
						'description' => $row[2] ?? '',
						'doc_no'      => $row[3] ?? '',
						'debit'       => $row[4] ?? '',
						'credit'      => $row[5] ?? '',
						'balance'     => $row[6] ?? '',
					];

				}
				
				foreach ($transactions as $transaction){
					if($transaction['credit']){
						$amount = $transaction['credit'];
						$dc = 'C';
					}else{
						$amount = $transaction['debit'];
						$dc = 'D';
					}
					
					$this->db->where('id', $transaction['doc_no']);
					$exists = $this->db->get('sma_accounts_entries')->row();
					
					if($transaction['doc_no']){
						if (!$exists) {
							// Only insert if it doesn't already exist
							$insert_trs = [
								'id' => $transaction['doc_no'],
								'entrytype_id' => 4,
								'transaction_type' => 'balanceupload',
								'number' => 'TBU-' . $transaction['doc_no'],
								'date' => date('Y-m-d', strtotime($transaction['date'])),
								'notes' => 'Trial Balance Upload, Dated: ' . date('Y-m-d')
							];
							$this->db->insert('sma_accounts_entries', $insert_trs);
							$account_entry_id = $this->db->insert_id();
						} else {
							// Record already exists, do nothing or log if needed
							$account_entry_id = $exists->id;
						}

						$insert_id = $transaction['doc_no'];
					}else{
						$baseTimestamp = strtotime($transaction['date']);

						$insert_trs = [
							'entrytype_id' => 4,
							'transaction_type' => 'balanceupload',
							'number' => 'TBU-' . $transaction['doc_no'],
							'date' => date('Y-m-d', strtotime('-1 day', $baseTimestamp)),
							'notes' => 'Trial Balance Upload, Dated: ' . date('Y-m-d')
						];
						$this->db->insert('sma_accounts_entries', $insert_trs);
						$account_entry_id = $this->db->insert_id();

						$insert_id = $account_entry_id;
					}

					$insert_entry_item = [
						'entry_id' => $insert_id,
						'ledger_id' => $ledger,
						'amount' => $amount,
						'dc' => $dc,
						'narration' => $transaction['description']
					];
					
					$account_entry_item_id = $this->db->insert('sma_accounts_entryitems', $insert_entry_item);
				}
			
				admin_redirect('entries');
				
			}
		} else {
            $data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));

            admin_redirect('entries');
        }
	}

	public function upload_trial_balance(){
		$this->data['ledgers'] = $this->site->getCompanyLedgers();

		//$this->data['warehouses'] = $this->site->getAllWarehouses();
        //$this->data['suppliers'] = $this->site->getAllCompanies('supplier');
        $this->load->view($this->theme . 'accounts/uploadCsvTrialBalance', $this->data);
	}

	public function count_entries(){

		$eid         = $this->input->get('eid');
        $tran_number   = $this->input->get('tran_number');
		$start_date     = $this->input->get('start_date');
        $end_date     = $this->input->get('end_date');
		
		$this->db->select(' COUNT(id) as total_record');
		$this->db->from('sma_accounts_entries');  

		if ($start_date) {
			$start_date = $this->sma->fld($start_date);
			$end_date   = $this->sma->fld($end_date);
		}
		if ($start_date && $start_date != "0000-00-00") {
            $this->db->where('date >=', $start_date);       

            if ($end_date  && $end_date != "0000-00-00") {
                $this->db->where('date <=', $end_date);
            }
        }
		if(!empty($eid)){
			$this->db->where("id LIKE '%$eid%'");	
		}
		if(!empty($tran_number)){
			$this->db->where("number LIKE '%$tran_number%'");	
		}

		$this->db->where("transaction_type NOT IN ('purchase_invoice', 'sales_invoice')");

		$query = $this->db->get();
		 // echo $this->db->last_query(); exit;
		$row = $query->row();
		$count = $row->total_record;
		return $count;   

	}

	public function ledgerList($entrytypeLabel, $searchTerm = null, $selectedLedgers = array()) {

		if ($this->input->post('searchTerm')) {
			$searchTerm = $this->input->post('searchTerm');
		}

		if ($this->input->post('selectedLedgers')) {
			$selectedLedgers = $this->input->post('selectedLedgers');
		}

		if (!is_array($selectedLedgers)) {
			return false;
		}

		echo $this->functionscore->ledgerList($entrytypeLabel, $searchTerm, $selectedLedgers);
	}

	public function at_least_one_selected($value){
		// Check if at least one of the fields is provided
		$product_id = $this->input->post('product_id');
		$customer_id = $this->input->post('customer_id');
		$supplier_id = $this->input->post('supplier_id');
		$department_id = $this->input->post('department_id');
		$employee_id = $this->input->post('employee_id');
	
		// if (empty($product_id) && empty($customer_id) && empty($supplier_id) && empty($department_id) && empty($employee_id)) {
		// 	$this->form_validation->set_message('at_least_one_selected', 'At least one Dimensions (item, customer, supplier, department or employee) field must be selected.');
		// 	return false;
		// }
	
		return true;
	}

	/**
	* add method
	*
	* @param string $entrytypeLabel
	* @return void
	*/
	public function add($entrytypeLabel = null) {
		
		/* Check for valid entry type */
		if (!$entrytypeLabel) {
			// show 404 error page
			show_404();
		}

		// load entry model
		$this->load->admin_model('entry_model');

		$this->data['entrytypeLabel'] = $entrytypeLabel;

		$this->data['customers'] = $this->site->getAllCompanies('customer');
		$this->data['suppliers'] = $this->site->getAllCompanies('supplier');
		$this->data['departments'] = $this->site->getAllDepartments();
		$this->data['employees'] = $this->site->getAllEmployees();

		
		
		// Select from entrytypes table in db where label = $entrytypeLabel
		$entrytype = $this->db->query("SELECT * FROM ".$this->db->dbprefix('accounts_entrytypes')." WHERE label='$entrytypeLabel'");
		// create array of select data from db - [entrytypes] table
		$entrytype = $entrytype->row_array();
		
		// check if entry type exists
		if (!$entrytype) {
			// set error message if entry type do not exist
			$this->session->set_flashdata('error', lang('entries_cntrler_entrytype_not_found_error'));
			// redirect to index page
			admin_redirect('entries');
		}

		// get allowed decimal place from account settings
		$allowed = $this->mAccountSettings->decimal_places;

		// form validation rules 
		//$this->form_validation->set_rules('number', lang('entries_cntrler_add_form_validation_number_label'), 'is_numeric');
		$this->form_validation->set_rules('date', lang('entries_cntrler_add_form_validation_date_label'), 'required');
		// $this->form_validation->set_rules('tag_id', lang('entries_cntrler_add_form_validation_tag_label'), 'required');

		$this->form_validation->set_rules('product_id', 'Product ID', 'callback_at_least_one_selected');
		$this->form_validation->set_rules('customer_id', 'Customer ID', 'callback_at_least_one_selected');
		$this->form_validation->set_rules('supplier_id', 'Supplier ID', 'callback_at_least_one_selected');
		$this->form_validation->set_rules('department_id', 'Department ID', 'callback_at_least_one_selected');
		$this->form_validation->set_rules('employee_id', 'Employee ID', 'callback_at_least_one_selected');


		/*$q = $this->db->get_where('sma_accounts_entries', array('number' => $this->input->post('number')));
		if ($q->num_rows() > 0) {
			$this->form_validation->set_rules('number', lang('entries_cntrler_add_form_validation_number_label'), 'is_numeric|is_unique[sma_accounts_entries.number]');
			$this->form_validation->set_message('is_unique', lang('form_validation_is_db_unique'));
		}*/
		
		$dc_valid = false; 	// valid debit or credit ledger
		$dr_total = 0;		// total dr amount initially 0
		$cr_total = 0;		// total cr amount initially 0

		// check if $_POST['Entryitem'] is set and is an array
		if (isset($_POST['Entryitem']) && is_array($_POST['Entryitem'])) 
		{
			// loop for all $_POST['Entryitem']
		    foreach ($_POST['Entryitem'] as $key => $value)
		    {	
		    	// check if $value['ledger_id'] less then or equal to 0
		    	if ($value['ledger_id'] <= 0)
		    	{
		    		// continue to next Entryitem
					continue;
				}
				
				// array of selected ledger
		    	$ledger = $this->db->get_where('sma_accounts_ledgers', array('id' => $value['ledger_id']))->row_array();

		    	// check if $ledger is Empty
				if (!$ledger)
				{
					// set form validation for Entryitem to be required with error alert
    				$this->form_validation->set_rules('Entryitem', '', 'required', array('required' => lang('entries_cntrler_invalid_ledger_form_validation_alert')));
				}
				// check if Only Bank or Cash account is present on both Debit and Credit side
				if ($entrytype['restriction_bankcash'] == 4)
				{
					// check if ledger is [NOT] a Bank or Cash Account
					if ($ledger['type'] != 1) {
    					$this->form_validation->set_rules('Entryitem', '', 'required', array('required' => lang('entries_cntrler_restriction_bankcash_4_form_validation_alert')));
					}
				}
				// check if Only NON Bank or Cash account is present on both Debit and Credit side
				if ($entrytype['restriction_bankcash'] == 5)
				{
					// check if ledger is a Bank or Cash Account
					if ($ledger['type'] == 1) {
    					$this->form_validation->set_rules('Entryitem', '', 'required', array('required' => lang('entries_cntrler_restriction_bankcash_5_form_validation_alert')));
					}
				}

				// check if ledger is Debit
				if ($value['dc'] == 'D')
				{
					// check if Atleast one Bank or Cash account must be present on Debit side
					if ($entrytype['restriction_bankcash'] == 2)
					{
						// check if ledger is a Bank or Cash Account
						if ($ledger['type'] == 1)
						{
							// set dc_valid = true
							$dc_valid = true;
						}
					}
				} else if ($value['dc'] == 'C') // check if ledger is Credit 
				{
					// check if Atleast 1 Bank or Cash account is present on Credit side
					if ($entrytype['restriction_bankcash'] == 3)
					{
						// check if ledger is Bank or Cash Account
						if ($ledger['type'] == 1)
						{
							// set dc_valid = true
							$dc_valid = true;
						}
					}
				}

				// some more form validation rules
		        $this->form_validation->set_rules('Entryitem['.$key.'][dc]', lang('entries_cntrler_add_form_validation_entryitem_dc_label'), 'required'); // Any validation you need
		        $this->form_validation->set_rules('Entryitem['.$key.'][ledger_id]', lang('entries_cntrler_add_form_validation_entryitem_ledger_id_label'), 'required'); // Any validation you need

		        // if Debit selected
		        if ($value['dc'] == 'D')
		        {
		        	// if dr_amount not empty
		        	if (!empty($value['dr_amount']))
		        	{
		        		// set form validation rules form dr_amount
		        		$this->form_validation->set_rules('Entryitem['.$key.'][dr_amount]', '', "greater_than[0]|amount_okay[$allowed]");

		        		// calculate total debit amount
						$dr_total = $this->functionscore->calculate($dr_total, $value['dr_amount'], '+');
		        	}
		        }else // if credit selected
		        {
		        	// if cr_amount if not empty
		        	if (!empty($value['cr_amount']))
		        	{
		        		// set form validation rules form cr_amount
			        	$this->form_validation->set_rules('Entryitem['.$key.'][cr_amount]', '', "greater_than[0]|amount_okay[$allowed]");

			        	// calculate total credit amount
						$cr_total = $this->functionscore->calculate($cr_total, $value['cr_amount'], '+');
		        	}
		        }
		    }

		    // check if total dr or cr amount is not equal
		    if ($this->functionscore->calculate($dr_total, $cr_total, '!='))
		    {
		    	// set form validation error
        		$this->form_validation->set_rules('Entryitem', '', 'required', array('required' => lang('entries_cntrler_dr_cr_total_not_equal_form_validation_alert')));
			}
		}

		// check if restriction_bankcash = 2
		if ($entrytype['restriction_bankcash'] == 2)
		{
			// check if Atleast one Bank or Cash account is present on Debit side
			if (!$dc_valid)
			{
				// set form validation error
        		$this->form_validation->set_rules('Entryitem', '', 'required', array('required' => lang('entries_cntrler_restriction_bankcash_2_not_valid_dc_form_validation_alert')));
			}
		}
		
		// check if Atleast one Bank or Cash account is present on Credit side
		if ($entrytype['restriction_bankcash'] == 3)
		{
			// check if no Bank or Cash account is present on Credit side
			if (!$dc_valid) {
				// set form validation error
        		$this->form_validation->set_rules('Entryitem', '', 'required', array('required' => lang('entries_cntrler_restriction_bankcash_3_not_valid_dc_form_validation_alert')));
			}
		}

		/***** Check if entry type numbering is auto ******/
		if ($entrytype['numbering'] == 1)
		{
			/* check if $_POST['number'] is empty */
			if (empty($this->input->post('number')))
			{
				// set entry number to next entry number
				$number = $this->entry_model->nextNumber($entrytype['id']);
			}else // if not empty
			{
				// set entry number to $_POST['number']
				$number = $this->input->post('number');
			}
		}else if ($entrytype['numbering'] == 2) // Check if entry type numbering is manual and required
		{
			/* Manual + Required - Check if $_POST['number'] is empty */
			if (empty($this->input->post('number')))
			{
				//  set form validation rule
        		$this->form_validation->set_rules('number', '', 'required', array('required' => lang('entries_cntrler_entry_number_required_form_validation_alert')));
			} else // if not empty
			{
				// set entry number to $_POST['number']
				$number = $this->input->post('number');
			}
		} else // if entry type numbering is manual and not required
		{
			/* Manual + Optional - set entry number to $_POST['number'] */
			$number = $this->input->post('number');
		}

		// check if form is NOT Validated
		if ($this->form_validation->run() == FALSE) {

			$this->data['entrytype'] = $entrytype; // pass entrytype array to view
			// pass page title to view

			$this->data['title'] = sprintf(lang('entries_cntrler_add_title'), $entrytype['name']);
			// pass tag_options array to view
			$this->data['tag_options'] = $this->db->select('id, title')->get('sma_accounts_tags')->result_array();
			
			//$this->load->library('LedgerTree');
			/* Ledger selection */
			$ledgers = new LedgerTree(); // initilize ledgers array - LedgerTree Lib
			$ledgers->Group = &$this->Group; // initilize selected ledger groups in ledgers array
			$ledgers->Ledger = &$this->Ledger; // initilize selected ledgers in ledgers array
			$ledgers->current_id = -1; // initilize current group id
			// set restriction_bankcash from entrytype
			$ledgers->restriction_bankcash = $entrytype['restriction_bankcash'];
			$ledgers->build(0); // set ledger id to [NULL] and ledger name to [None] 
			$ledgers->toList($ledgers, -1); // create a list of ledgers array
			$this->data['ledger_options'] = $ledgers->ledgerList; // pass ledger list to view
			
			/*  Check if input method is post */
			if ($this->input->method() == 'post') {
				// initilize current entry items array
				$curEntryitems = array();

				if (isset($_POST['Entryitem']) && !empty($_POST['Entryitem'])) {
					// loop to save post data to current entry items array
					foreach ($_POST['Entryitem'] as $row => $entryitem)
					{
						if (isset($entryitem['ledger_balance'])) {
							$curEntryitems[$row] = array
							(
								'dc' => $entryitem['dc'],
								'ledger_id' => $entryitem['ledger_id'],
								// if dr_amount isset save it else save empty string
								'dr_amount' => isset($entryitem['dr_amount']) ? $entryitem['dr_amount'] : '',
								 // if cr_amount isset save it else save empty string
								'cr_amount' => isset($entryitem['cr_amount']) ? $entryitem['cr_amount'] : '',
								'narration' => $entryitem['narration'],
								'ledger_balance' => $entryitem['ledger_balance'],
								'ledgername' => $this->ledger_model->getName($entryitem['ledger_id']),
								'customer_id' => $entryitem['customer_id'],
								'supplier_id' => $entryitem['supplier_id'],
								'department_id' => $entryitem['department_id'],
								'employee_id'  => $entryitem['employee_id']
							);
						}else{
							$curEntryitems[$row] = array
							(
								'dc' => $entryitem['dc'],
								'ledger_id' => $entryitem['ledger_id'],
								// if dr_amount isset save it else save empty string
								'dr_amount' => isset($entryitem['dr_amount']) ? $entryitem['dr_amount'] : '',
								 // if cr_amount isset save it else save empty string
								'cr_amount' => isset($entryitem['cr_amount']) ? $entryitem['cr_amount'] : '',
								'narration' => $entryitem['narration'],
								'customer_id' => $entryitem['customer_id'],
								'supplier_id' => $entryitem['supplier_id'],
								'department_id' => $entryitem['department_id'],
								'employee_id'  => $entryitem['employee_id']

							);
						}
					}
				}
				
				// pass current entry items array to view
				$this->data['curEntryitems'] = $curEntryitems;
			} else { // if method is NOT post
				$curEntryitems = array(); // initilize current entry items array 

				/* Special case if atleast one Bank or Cash on credit side (3) then 1st item is Credit */
				if ($entrytype['restriction_bankcash'] == 3){
					$curEntryitems[0] = array('dc' => 'C');
					$curEntryitems[1] = array('dc' => 'D');
				} else { /* else 1st item is Debit */
					$curEntryitems[0] = array('dc' => 'D');
					$curEntryitems[1] = array('dc' => 'C');
				}

				// pass current entry items array to view
				$this->data['curEntryitems'] = $curEntryitems;
			}
			
			// render page
			if ($this->mSettings->entry_form) {

				$bc  = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('entries'), 'page' => lang('Entries')], ['link' => '#', 'page' => lang('Entries')]];
		        $meta = ['page_title' => lang('Add Entry'), 'bc' => $bc];
		        $this->page_construct('accounts/entries_add2', $meta, $this->data);

			}else{
				$this->data['error']      = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
				$bc  = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('entries'), 'page' => lang('Entries')], ['link' => '#', 'page' => lang('Entries')]];
		        $meta = ['page_title' => lang('Add Entry'), 'bc' => $bc];
		        $this->page_construct('accounts/entries_add', $meta, $this->data);
			}
			
		} else { // if form is Validated
		
			/***************************************************************************/
			/*********************************** ENTRY *********************************/
			/***************************************************************************/
			$entrydata = null; // create entry data array to insert in [entries] table - db
			$entrydata['Entry']['number'] = $number; // set entry number in entry data array
			$entrydata['Entry']['entrytype_id'] = $entrytype['id']; // set entrytype_id in entry data array
			
			// check if $_POST['tag_id'] is empty
			if (empty($this->input->post('tag_id')))
			{
				// set entry tag id in entry data array to [NULL]
				$entrydata['Entry']['tag_id'] = null;

			}else // if $_POST['tag_id'] is NOT empty
			{
				// set entry tag id in entry data array to $_POST['tag_id']
				$entrydata['Entry']['tag_id'] = $this->input->post('tag_id');
			}

			/***** Check if $_POST['notes'] is empty *****/
			if (empty($this->input->post('notes')))
			{
				// set entry note in entry data array to [NULL]
				$entrydata['Entry']['notes'] = '';
			}else // if NOT empty
			{
				// set entry note in entry data array to $_POST['notes']
				$entrydata['Entry']['notes'] = $this->input->post('notes');
			}

			/***** Set entry date to $_POST['date'] after converting to sql format(dateToSql function) *****/
			$entrydata['Entry']['date'] = $this->functionscore->dateToSql($this->input->post('date'));

			
			/***************************************************************************/
			/***************************** ENTRY ITEMS *********************************/
			/***************************************************************************/
			/* Check ledger restriction */

			$entrydata['Entry']['dr_total'] = $dr_total; // set entry dr_total in entry data array as $dr_total
			$entrydata['Entry']['cr_total'] = $cr_total; // set entry cr_total in entry data array as $cr_total
			
			/* Add item to entry item data array if everything is ok */
			$entryitemdata = array(); // create entry items data array to insert in [entryitems] table - db

			// loop for all Entryitems from post data
			foreach ($this->input->post('Entryitem') as $row => $entryitem)
			{	 
				 
	 
				// check if $entryitem['ledger_id'] less then or equal to 0
				if ($entryitem['ledger_id'] <= 0)
				{
					// continue to next entryitem
					continue;
				}

				// if entryitem is debit
				if ($entryitem['dc'] == 'D')
				{
					// save entry item data array with dr_amount
					$entryitemdata[] = array(
						'Entryitem' => array(
							'dc' => $entryitem['dc'],
							'ledger_id' => $entryitem['ledger_id'],
							'amount' => $entryitem['dr_amount'],
							'narration' => $entryitem['narration'],
							 'customer_id' => $entryitem['customer_id'],
						 	'supplier_id' => $entryitem['supplier_id'],
								'department_id' => $entryitem['department_id'],
								'employee_id'  => $entryitem['employee_id']
						)
					);
				}else // if entrytype is credit
				{
					// save entry item data array with cr_amount
					$entryitemdata[] = array(
						'Entryitem' => array(
							'dc' => $entryitem['dc'],
							'ledger_id' => $entryitem['ledger_id'],
							'amount' => $entryitem['cr_amount'],
							'narration' => $entryitem['narration'],
							 'customer_id' => $entryitem['customer_id'],
							'supplier_id' => $entryitem['supplier_id'],
							'department_id' => $entryitem['department_id'],
							'employee_id'  => $entryitem['employee_id']
							 
						)
					);
				}
			}

			// Dimensions
			$entrydata['Entry']['item_id'] = $this->input->post('product_id') ? $this->input->post('product_id') : 0;
			$entrydata['Entry']['customer_id'] = $this->input->post('customer_id') ? $this->input->post('customer_id') : 0;
			$entrydata['Entry']['supplier_id'] = $this->input->post('supplier_id') ? $this->input->post('supplier_id') : 0;
			$entrydata['Entry']['department_id'] = $this->input->post('department_id') ? $this->input->post('department_id') : 0;
			$entrydata['Entry']['employee_id'] = $this->input->post('employee_id') ? $this->input->post('employee_id') : 0;
			$entrydata['Entry']['transaction_type'] = 'journal';

			/* insert entry data array to entries table - db */
			$add  = $this->db->insert('sma_accounts_entries', $entrydata['Entry']);

			// if entry data is inserted
			if ($add)
			{
			   	$insert_id = $this->db->insert_id(); // get inserted entry id


				$attachments        = $this->attachments->upload();				
				if (!empty($attachments)) {
					foreach ($attachments as $attachment) {
						$attachment['subject_id']   = $insert_id;
						$attachment['subject_type'] = 'journal';
						$this->db->insert('attachments', $attachment);
					}
				}

			   	// loop for inserting entry item data array to [entryitems] table - db
				foreach ($entryitemdata as $row => $itemdata)
				{
					// entry_id for each entry item as id of last entry
					$itemdata['Entryitem']['entry_id'] = $insert_id;

					// insert item data to entryitems table - db
					$this->db->insert('sma_accounts_entryitems' ,$itemdata['Entryitem']);
				}

				// set entry number as per prefix, suffix and zero padding for that entry type for logging
				$entryNumber = $this->functionscore->toEntryNumber($entrydata['Entry']['number'], $entrytype['id']);


				// insert log if logging is enabled
				//$this->settings_model->add_log(sprintf(lang('entries_cntrler_add_log'),$entrytype['name'], $entryNumber), 1);

				// set success alert message
				$this->session->set_flashdata('message', sprintf(lang('entries_cntrler_add_entry_created_successfully'),$entrytype['name'], $entryNumber));
				// redirect to index page
				admin_redirect('entries');
			}else
			{
				// set error alert message
				$this->session->set_flashdata('error', lang('entries_cntrler_add_entry_not_created_error'));
				// redirect to index page
				admin_redirect('entries');
			}
		}
	}

	/**
	* edit method
	*
	* @param string $entrytypeLabel
	* @param string $id
	* @return void
	*/
	public function edit($entrytypeLabel = null, $id = null)
	{
		// load model - entry_model
		$this->load->admin_model('entry_model');

		/* Check for valid entry type */
		if (!$entrytypeLabel)
		{
			// show 404 error page
			show_404();
		}

		$this->data['entrytypeLabel'] = $entrytypeLabel;

		$this->data['customers'] = $this->site->getAllCompanies('customer');
		$this->data['suppliers'] = $this->site->getAllCompanies('supplier');
		$this->data['departments'] = $this->site->getAllDepartments();
		$this->data['employees'] = $this->site->getAllEmployees();


		// create entry type array where label = [$entrytypeLabel]
		$entrytype = $this->db->query("SELECT * FROM ".$this->db->dbprefix('accounts_entrytypes')." WHERE label='$entrytypeLabel'")->row_array();

		// if no entry type found
		if (!$entrytype) {
			// set error message
			$this->session->set_flashdata('error', lang('entries_cntrler_entrytype_not_found_error'));
			// redirect to index page
			admin_redirect('entries');
		}

		// get allowed decimal place from account settings
		$allowed = $this->mAccountSettings->decimal_places;

		// form validation rules
		$this->form_validation->set_rules('number', lang('entries_cntrler_edit_form_validarion_number'), 'required');
		$this->form_validation->set_rules('date', lang('entries_cntrler_edit_form_validarion_date'), 'required');
		//$this->form_validation->set_rules('tag_id', lang('entries_cntrler_edit_form_validarion_tag'), 'required');

		$this->form_validation->set_rules('product_id', 'Product ID', 'callback_at_least_one_selected');
		$this->form_validation->set_rules('customer_id', 'Customer ID', 'callback_at_least_one_selected');
		$this->form_validation->set_rules('supplier_id', 'Supplier ID', 'callback_at_least_one_selected');
		$this->form_validation->set_rules('department_id', 'Department ID', 'callback_at_least_one_selected');
		$this->form_validation->set_rules('employee_id', 'Employee ID', 'callback_at_least_one_selected');

		$q = $this->db->get_where('sma_accounts_entries', array('id' => $id))->row();
		if ($this->input->post('number') != $q->number) {
			$this->form_validation->set_rules('number', lang('entries_cntrler_add_form_validation_number_label'), 'is_db1_unique[sma_accounts_entries.number]');
			$this->form_validation->set_message('is_db1_unique', lang('form_validation_is_db_unique'));
			
        }

		$dc_valid = false; 	// valid Debit or Credit
		$dr_total = 0;		// total Debit amount
		$cr_total = 0;		// total credit amount

		// if Entryitem present in post data and is an array
		if (isset($_POST['Entryitem']) && is_array($_POST['Entryitem']))
		{
			// loop for all entry items
		    foreach ($_POST['Entryitem'] as $key => $value)
		    {
		    	// check if $value['ledger_id'] less then or equal to 0
		    	if ($value['ledger_id'] <= 0)
		    	{
		    		// continue to next Entry item
					continue;
				}

				// ledgers array where id = selected entry items ledger id
		    	$ledger = $this->db->get_where('sma_accounts_ledgers', array('id' => $value['ledger_id']))->row_array();

		    	// if ledger not found
				if (!$ledger)
				{
					// set form validation for Entryitem to be required with error alert
    				$this->form_validation->set_rules('Entryitem', '', 'required', array('required' => lang('entries_cntrler_invalid_ledger_form_validation_alert')));
				}
				// check if Only Bank or Cash account is present on both Debit and Credit side
				if ($entrytype['restriction_bankcash'] == 4)
				{
					// check if ledger is [NOT] a Bank or Cash Account
					if ($ledger['type'] != 1)
					{
						// set form validation for Entryitem to be required with error alert
    					$this->form_validation->set_rules('Entryitem', '', 'required', array('required' => lang('entries_cntrler_restriction_bankcash_4_form_validation_alert')));
					}
				}
				
				// check if Only NON Bank or Cash account is present on both Debit and Credit side
				if ($entrytype['restriction_bankcash'] == 5)
				{
					if ($ledger['type'] == 1)
					{
						// set form validation for Entryitem to be required with error alert
    					$this->form_validation->set_rules('Entryitem', '', 'required', array('required' => lang('entries_cntrler_restriction_bankcash_5_form_validation_alert')));
					}
				}

				// check if ledger is Debit
				if ($value['dc'] == 'D') {
					// check if Atleast one Bank or Cash account must be present on Debit side
					if ($entrytype['restriction_bankcash'] == 2)
					{
						// check if ledger is a Bank or Cash Account
						if ($ledger['type'] == 1)
						{
							// set dc_valid = true
							$dc_valid = true;
						}
					}
				} else if ($value['dc'] == 'C') // check if ledger is Credit 
				{
					// check if Atleast 1 Bank or Cash account is present on Credit side
					if ($entrytype['restriction_bankcash'] == 3)
					{
						// check if ledger is Bank or Cash Account
						if ($ledger['type'] == 1)
						{
							// set dc_valid = true
							$dc_valid = true;
						}
					}
				}

				// some more form validation rules
		        $this->form_validation->set_rules('Entryitem['.$key.'][dc]', lang('entries_cntrler_edit_form_validation_entryitem_dc_label'), 'required'); // Any validation you need
		        $this->form_validation->set_rules('Entryitem['.$key.'][ledger_id]', lang('entries_cntrler_edit_form_validation_entryitem_ledger_id_label'), 'required'); // Any validation you need

		        // if Debit selected
		        if ($value['dc'] == 'D')
		        {
		        	// if dr_amount if not empty
		        	if (!empty($value['dr_amount']))
		        	{
						// set form validation rules form dr_amount
		        		$this->form_validation->set_rules('Entryitem['.$key.'][dr_amount]', '', "greater_than[0]|amount_okay[$allowed]"); // Any validation you need

		        		// calculate total debit amount
						$dr_total = $this->functionscore->calculate($dr_total, $value['dr_amount'], '+');
		        	}
		        }else // if credit selected
		        {
		        	// if cr_amount if not empty
		        	if (!empty($value['cr_amount']))
		        	{
						// set form validation rules form cr_amount
			        	$this->form_validation->set_rules('Entryitem['.$key.'][cr_amount]', '', "greater_than[0]|amount_okay[$allowed]"); // Any validation you need

			        	// calculate total credit amount
						$cr_total = $this->functionscore->calculate($cr_total, $value['cr_amount'], '+');
		        	}
		        }
		    }

		   	// check if total dr or cr amount is not equal
		    if ($this->functionscore->calculate($dr_total, $cr_total, '!='))
		    {
		    	// set form validation error
        		$this->form_validation->set_rules('Entryitem', '', 'required', array('required' => lang('entries_cntrler_dr_cr_total_not_equal_form_validation_alert')));
			}

		}

		// check if one Bank or Cash account is present on Debit side
		if ($entrytype['restriction_bankcash'] == 2)
		{
			// check if dc_valid is [NOT] true
			if (!$dc_valid)
			{
				// set form validation error
        		$this->form_validation->set_rules('Entryitem', '', 'required', array('required' => lang('entries_cntrler_restriction_bankcash_2_not_valid_dc_form_validation_alert')));
			}
		}

		// check if Atleast one Bank or Cash account is present on Credit side
		if ($entrytype['restriction_bankcash'] == 3)
		{
			// check if dc_valid is [NOT] true
			if (!$dc_valid)
			{
				// set form validation error
        		$this->form_validation->set_rules('Entryitem', '', 'required', array('required' => lang('entries_cntrler_restriction_bankcash_3_not_valid_dc_form_validation_alert')));
			}
		}

		/***** Check if entry type numbering is auto ******/
		if ($entrytype['numbering'] == 1)
		{
			/* check if $_POST['number'] is empty */
			if (empty($this->input->post('number')))
			{
				// set entry number to next entry number
				$number = $this->entry_model->nextNumber($entrytype['id']);
			}else // if not empty
			{
				// set entry number to $_POST['number']
				$number = $this->input->post('number');
			}
		}else if ($entrytype['numbering'] == 2) // Check if entry type numbering is manual and required
		{
			/* Manual + Required - Check if $_POST['number'] is empty */
			if (empty($this->request->data['Entry']['number']))
			{
				// set form validation rule with error
        		$this->form_validation->set_rules('number', '', 'required', array('required' => lang('entries_cntrler_entry_number_required_form_validation_alert')));
			}else // if not empty
			{
				// set entry number to $_POST['number']
				$number = $this->input->post('number');
			}
		}else// if entry type numbering is manual and not required
		{
			/* Manual + Optional - set entry number to $_POST['number'] */
			$number = $this->input->post('number');
		}

		// check if form is NOT Validated
		if ($this->form_validation->run() == FALSE)
		{
			// pass page title to view
			$this->data['title'] = sprintf(lang('entries_cntrler_edit_title'), $entrytype['name']); 
			$this->data['entrytype'] = $entrytype; // pass entrytype array to view
			// pass tag_options array to view
			$this->data['tag_options'] = $this->db->select('id, title')->get('sma_accounts_tags')->result_array();

			/* Ledger selection */
			$ledgers = new LedgerTree(); // initilize ledgers array - LedgerTree Lib
			$ledgers->Group = &$this->Group; // initilize selected ledger groups in ledgers array
			$ledgers->Ledger = &$this->Ledger; // initilize selected ledgers in ledgers array
			$ledgers->current_id = -1; // initilize current group id
			// set restriction_bankcash from entrytype
			$ledgers->restriction_bankcash = $entrytype['restriction_bankcash'];
			$ledgers->build(0); // set ledger id to [NULL] and ledger name to [None]
			$ledgers->toList($ledgers, -1);	// create a list of ledgers array
			$this->data['ledger_options'] = $ledgers->ledgerList; // pass ledger list to view

			// if ($this->mSettings->entry_form) {
			// 	$this->db->where('id', $id);
			// 	$this->data['ledger'] = $this->db->get('ledgers')->row_array();
			// }
			
			/* Check for valid entry id */
			if (!$entrytypeLabel)
			{
				// set error alert
				$this->session->set_flashdata('error', lang('entries_cntrler_entrytype_not_specified_error'));
				// redirect to index page
				admin_redirect('entries');
			}

			// select data from entries table where id equals $id(passed id to edit function) and create array
			$entry = $this->db->where('id', $id)->get('sma_accounts_entries')->row_array();

			// if no entries found
			if (!$entry)
			{
				// set error alert
				$this->session->set_flashdata('error', lang('entries_cntrler_entry_not_found_error'));
				// redirect to index page
				admin_redirect('entries');
			}

			/* Check if input method is post */
			if ($this->input->method() == 'post') {
				// initilize current entry items array
				$curEntryitems = array(); 
				$EntryItems = $this->input->post('Entryitem');

				// loop to save post data to current entry items array
				foreach ($EntryItems as $row => $entryitem) {
					if($this->mSettings->entry_form){
						$curEntryitems[$row] = array(
							'dc' => $entryitem['dc'],
							'ledger_id' => $entryitem['ledger_id'],
							// if dr_amount isset save it else save empty string
							'dr_amount' => isset($entryitem['dr_amount']) ? $entryitem['dr_amount'] : '',
							 // if cr_amount isset save it else save empty string
							'cr_amount' => isset($entryitem['cr_amount']) ? $entryitem['cr_amount'] : '',
							'narration' => $entryitem['narration'],
							'ledger_balance' => $entryitem['ledger_balance'],
							'ledgername' => $this->ledger_model->getName($entryitem['ledger_id']),
							'customer_id' => $entryitem['customer_id'],
						 	'supplier_id' => $entryitem['supplier_id'],
							'department_id' => $entryitem['department_id'],
							'employee_id'  => $entryitem['employee_id']
						);
					} else {
						$curEntryitems[$row] = array(
							'dc' => $entryitem['dc'],
							'ledger_id' => $entryitem['ledger_id'],
							// if dr_amount isset save it else save empty string
							'dr_amount' => isset($entryitem['dr_amount']) ? $entryitem['dr_amount'] : '',
							// if cr_amount isset save it else save empty string
							'cr_amount' => isset($entryitem['cr_amount']) ? $entryitem['cr_amount'] : '',
							'narration' => $entryitem['narration'],
							'customer_id' => $entryitem['customer_id'],
						 	'supplier_id' => $entryitem['supplier_id'],
							'department_id' => $entryitem['department_id'],
							'employee_id'  => $entryitem['employee_id']
						);
					}
				}
				// pass current entry items array to view
				$this->data['curEntryitems'] = $curEntryitems;
			
			} else { // if method is [NOT] post
				
				$curEntryitems = array(); // initilize current entry items array 
				$selectedLedgers = array(); // initilize current entry items array 

				// get entry items where entry_id equals $id(passed id to edit function) and store to [curEntryitemsData] array
				$curEntryitemsData = $this->db->where('entry_id', $id)->get('sma_accounts_entryitems')->result_array();
				// loop for storing current entry items in current entry items array 
				foreach ($curEntryitemsData as $row => $data)
				{
					if($this->mSettings->entry_form) {
						$ledger_balance = $this->curLedgerBalance($data['ledger_id']);

						// if entry item is debit
						if ($data['dc'] == 'D')
						{
							$curEntryitems[$row] = array
							(
								'dc' => $data['dc'],
								'ledger_id' => $data['ledger_id'],
								'dr_amount' => $this->sma->formatDecimal($data['amount']),
								'cr_amount' => '',
								'narration' => $data['narration'],
								'ledgername' => $this->ledger_model->getName($data['ledger_id']),
								'ledger_balance' => $ledger_balance,
								'customer_id' => $data['customer_id'],
						 	'supplier_id' => $data['supplier_id'],
								'department_id' => $data['department_id'],
								'employee_id'  => $data['employee_id']


							);
						} else {// if entry item is credit
							$curEntryitems[$row] = array
							(
								'dc' => $data['dc'],
								'ledger_id' => $data['ledger_id'],
								'dr_amount' => '',
								'cr_amount' => $this->sma->formatDecimal($data['amount']),
								'narration' => $data['narration'],
								'ledgername' => $this->ledger_model->getName($data['ledger_id']),
								'ledger_balance' => $ledger_balance,
								'customer_id' => $data['customer_id'],
						 	'supplier_id' => $data['supplier_id'],
								'department_id' => $data['department_id'],
								'employee_id'  => $data['employee_id']
							);
						}
					} else {
						$selectedLedgers[$row] = $data['ledger_id'];
						// if entry item is debit
						if ($data['dc'] == 'D')
						{
							$curEntryitems[$row] = array
							(
								'dc' => $data['dc'],
								'ledger_id' => $data['ledger_id'],
								'dr_amount' => $this->sma->formatDecimal($data['amount']),
								'cr_amount' => '',
								'narration' => $data['narration'],
								'customer_id' => $data['customer_id'],
						 		'supplier_id' => $data['supplier_id'],
								'department_id' => $data['department_id'],
								'employee_id'  => $data['employee_id']
							);
						}else // if entry item is credit
						{
							$curEntryitems[$row] = array
							(
								'dc' => $data['dc'],
								'ledger_id' => $data['ledger_id'],
								'dr_amount' => '',
								'cr_amount' => $this->sma->formatDecimal($data['amount']),
								'narration' => $data['narration'],
								'customer_id' => $data['customer_id'],
						 		'supplier_id' => $data['supplier_id'],
								'department_id' => $data['department_id'],
								'employee_id'  => $data['employee_id']
							);
						}
					}
				}

				// pass current entry items array to view
				$this->data['curEntryitems'] = $curEntryitems;
				$this->data['selectedLedgers'] = $selectedLedgers;
			}

			/***** store entry date after converting from sql format(dateFromSql function) *****/
			$entry['date'] = $this->functionscore->dateFromSql($entry['date']);
			// pass entry array to view
			$this->data['entry'] = $entry;

			// render page
			if ($this->mSettings->entry_form) {

				$bc  = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('entries'), 'page' => lang('Entries')], ['link' => '#', 'page' => lang('Entries')]];
		        $meta = ['page_title' => lang('Entries'), 'bc' => $bc];
		        $this->page_construct('accounts/entries_edit2', $meta, $this->data);
			}else{
				$this->data['error']      = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
				$bc  = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('entries'), 'page' => lang('Entries')], ['link' => '#', 'page' => lang('Entries')]];
		        $meta = ['page_title' => lang('Entries'), 'bc' => $bc];
		        $this->page_construct('accounts/entries_edit', $meta, $this->data);
			}
		} else { // if form is Validated
			/* Check if acccount is locked */
			if ($this->mAccountSettings->account_locked == 1)
			{
				// set error alert
				$this->session->set_flashdata('error', lang('entries_cntrler_edit_account_locked_error'));
				// redirect to index page
				admin_redirect('entries');
			}

			/***************************************************************************/
			/*********************************** ENTRY *********************************/
			/***************************************************************************/

			$entrydata = null; // entry data array to insert into entries table - db

			/***** Entry number ******/
			$entrydata['Entry']['number'] = $number;

			/***** Entry id ******/
			$entrydata['Entry']['id'] = $id;

			/****** Entrytype remains the same *****/
			$entrydata['Entry']['entrytype_id'] = $entrytype['id'];

			/****** Check tag ******/
			if (empty($this->input->post('tag_id'))) {
				// null if empty
				$entrydata['Entry']['tag_id'] = null;
			} else {
				// else $_POST['tag_id']
				$entrydata['Entry']['tag_id'] = $this->input->post('tag_id');
			}

			/***** Notes *****/
			$entrydata['Entry']['notes'] = $this->input->post('notes');

			/***** Date after converting to sql format *****/
			$entrydata['Entry']['date'] = $this->functionscore->dateToSql($this->input->post('date'));

			
			/***************************************************************************/
			/***************************** ENTRY ITEMS *********************************/
			/***************************************************************************/


			$entrydata['Entry']['dr_total'] = $dr_total; // total debit amount
			$entrydata['Entry']['cr_total'] = $cr_total; // total credit amount

			/* Add item to entryitemdata array if everything is ok */
			$entryitemdata = array();

			// loop for entry items array according to debit or credit
			foreach ($this->input->post('Entryitem') as $row => $entryitem)
			{
				// check if $entryitem['ledger_id'] less then or equal to 0
				if ($entryitem['ledger_id'] <= 0)
				{
					// continue to next entryitem
					continue;
				}

				// if entry item is debit
				if ($entryitem['dc'] == 'D')
				{
					$entryitemdata[] = array
					(
						'Entryitem' => array(
							'dc' => $entryitem['dc'],
							'ledger_id' => $entryitem['ledger_id'],
							'amount' => $entryitem['dr_amount'],
							'narration' => $entryitem['narration'],
							'customer_id' => $entryitem['customer_id'],
						 	'supplier_id' => $entryitem['supplier_id'],
							'department_id' => $entryitem['department_id'],
							'employee_id'  => $entryitem['employee_id']

						)
					);
				} else // if entry item is credit
				{
					$entryitemdata[] = array
					(
						'Entryitem' => array(
							'dc' => $entryitem['dc'],
							'ledger_id' => $entryitem['ledger_id'],
							'amount' => $entryitem['cr_amount'],
							'narration' => $entryitem['narration'],
							'customer_id' => $entryitem['customer_id'],
						 	'supplier_id' => $entryitem['supplier_id'],
							'department_id' => $entryitem['department_id'],
							'employee_id'  => $entryitem['employee_id']
						)
					);
				}
			}

			// Dimensions
			$entrydata['Entry']['item_id'] = $this->input->post('product_id') ? $this->input->post('product_id') : 0;
			$entrydata['Entry']['customer_id'] = $this->input->post('customer_id') ? $this->input->post('customer_id') : 0;
			$entrydata['Entry']['supplier_id'] = $this->input->post('supplier_id') ? $this->input->post('supplier_id') : 0;
			$entrydata['Entry']['department_id'] = $this->input->post('department_id') ? $this->input->post('department_id') : 0;
			$entrydata['Entry']['employee_id'] = $this->input->post('employee_id') ? $this->input->post('employee_id') : 0;

			// select where id from [entries] table equals passed id
			$this->db->where('id', $id);
			// update entries table
			$update = $this->db->update('sma_accounts_entries', $entrydata['Entry']);
			
			// if update successfull
			if ($update)
			{

				$attachments        = $this->attachments->upload();				
				if (!empty($attachments)) {
					foreach ($attachments as $attachment) {
						$attachment['subject_id']   = $id;
						$attachment['subject_type'] = 'journal';
						$this->db->insert('attachments', $attachment);
					}
				}

			   	/* Delete all original entryitems */
				$this->db->where('entry_id', $id); // select all entry items where entry_id equals passed id
				$this->db->delete('sma_accounts_entryitems'); // delete selected entry items

				// loop to insert entry item data to entryitems table
				foreach ($entryitemdata as $row => $itemdata)
				{
					$itemdata['Entryitem']['entry_id'] = $id; // entry_id equals passed id
					$this->db->insert('sma_accounts_entryitems' ,$itemdata['Entryitem']); // insert data to entryitems table
				}

				// set entry number as per prefix, suffix and zero padding for that entry type for logging
				$entryNumber = ($this->functionscore->toEntryNumber($entrydata['Entry']['number'], $entrytype['Entrytype']['id']));

				// insert log if logging is enabled
				//$this->settings_model->add_log(sprintf(lang('entries_cntrler_edit_log'),$entrytype['name'], $entryNumber), 1);

				// set success alert message
				$this->session->set_flashdata('message', sprintf(lang('entries_cntrler_edit_entry_updated_successfully'), $entrytype['name'], $entryNumber));
				// redirect to index page
				admin_redirect('entries/index');
			} else {
				// set error alert message
				$this->session->set_flashdata('error', lang('entries_cntrler_edit_entry_not_updated_error'));
				// redirect to index page
				admin_redirect('entries/index');
			}
		}
	}

	private function curLedgerBalance($id)
	{
		$this->db->where('id', $id);
		$this->data['curledger'] = $this->db->get('sma_accounts_ledgers')->row_array();
		$cl = $this->ledger_model->closingBalance($id);
		$status = 'ok';
		$ledger_balance = '';
		if ($this->data['curledger']['type'] == 1) {
			if ($cl['dc'] == 'C') {
				$status = 'neg';
			}
		}

		/* Return closing balance */
		$cl = array('cl' => 
				array(
					'dc' => $cl['dc'],
					'amount' => $cl['amount'],
					'status' => $status,
				)
		);

		$ledger_bal = $cl['cl']['amount'];
		$prefix = '';
		$suffix = '';
		if ($cl['cl']['status'] == 'neg') {
			$this->data['prefix'] = '<span class="error-text">';
			$this->data['suffix'] = '</span>';
		}
		if ($cl['cl']['dc'] == 'D') {
			$ledger_balance = "Dr " . $ledger_bal;
		} else if ($cl['cl']['dc'] == 'C') {
			$ledger_balance = "Cr " . $ledger_bal;
		} else {
			$ledger_balance = '-';
		}
		return $ledger_balance;
	}


	/**
	* delete method
	*
	* @throws MethodNotAllowedException
	* @param string $entrytypeLabel
	* @param string $id
	* @return void
	*/
	public function delete($entrytypeLabel = null, $id = null)
	{
		/* Check for valid entry type */
		if (empty($entrytypeLabel))
		{
			// set error alert
			$this->session->set_flashdata('error', lang('entries_cntrler_entrytype_not_specified_error'));
			// redirect to index page
			admin_redirect('entries');
		}

		// select entry type where label equals $entrytypeLabel and store to array
		$entrytype = $this->db->where('label',$entrytypeLabel)->get('sma_accounts_entrytypes')->row_array();

		// if entry type [NOT] found
		if (!$entrytype)
		{
			// set error alert
			$this->session->set_flashdata('error', lang('entries_cntrler_entrytype_not_found_error'));
			// redirect to index page
			admin_redirect('entries');
		}

		/* Check if valid id */
		if (empty($id))
		{
			// set error alert
			$this->session->set_flashdata('error', lang('entries_cntrler_edit_entry_not_found_error'));
			// redirect to index page
			admin_redirect('entries');
		}
		
		// select entry where id equals $id and store to array
		$entry = $this->db->where('id',$id)->get('sma_accounts_entries')->row_array();

		/* if entry [NOT] found */
		if (!$entry)
		{	
			// set error alert
			$this->session->set_flashdata('error', lang('entries_cntrler_edit_entry_not_found_error'));
			// redirect to index page
			admin_redirect('entries');
		}

		/* Delete entry items */
		$this->db->delete('sma_accounts_entryitems', array('entry_id' => $id));
		/* Delete entry */
		$this->db->delete('sma_accounts_entries', array('id' => $id));

		// set entry number as per prefix, suffix and zero padding for that entry type for logging
		$entryNumber = ($this->functionscore->toEntryNumber($entry['number'], $entrytype['id']));

		// set success alert
		$this->session->set_flashdata('message', sprintf(lang('entries_cntrler_delete_entry_deleted_successfully'), $entrytype['name'], $entryNumber));

		// insert log if logging is enabled
		//$this->settings_model->add_log(sprintf(lang('entries_cntrler_delete_log'),$entrytype['name'], $entryNumber), 1);

		// redirect to index page
		admin_redirect('entries');

	}
	/**
	* view method
	*
	* @param string $entrytypeLabel
	* @param string $id
	* @return void
	*/
	public function view($entrytypeLabel = null, $id = null) {
		
		/* Check for valid entry type */
		if (empty($entrytypeLabel))
		{
			// set error alert
			$this->session->set_flashdata('error', lang('entries_cntrler_entrytype_not_specified_error'));
			// redirect to index page
			admin_redirect('accounts/entries_index');
		}

		// select entry type where label equals $entrytypeLabel and store to array
		$entrytype = $this->db->where('label',$entrytypeLabel)->get('sma_accounts_entrytypes')->row_array();
		
		// if entry type [NOT] found
		if (!$entrytype)
		{
			// set error alert
			$this->session->set_flashdata('error', lang('entries_cntrler_entrytype_not_found_error'));
			// redirect to index page
			admin_redirect('accounts/entries_index');
		}

		// pass entrytype to view
		$this->data['entrytype'] = $entrytype;

		$pid = $this->input->get('pid') ? $this->input->get('pid') : null;
		$sid = $this->input->get('sid') ? $this->input->get('sid') : null;
		$rid = $this->input->get('rid') ? $this->input->get('rid') : null;
		$rsid = $this->input->get('rsid') ? $this->input->get('rsid') : null;
		$tid = $this->input->get('tid') ? $this->input->get('tid') : null; 
		if ($id=== null && $pid === null && $sid === null && $rid === null && $rsid === null && $tid === null) {
			// Redirect if all variables are null // set error alert 
			$this->session->set_flashdata('error', lang('entries_cntrler_edit_entry_not_found_error')); 
			admin_redirect('accounts/entries_index');// redirect to index page
		} 
		if(!empty($id)){
			$this->db->where('id',$id); 
		}
		if(!empty($pid)){
			$this->db->where('pid',$pid); 
		}
		if(!empty($sid)){
			$this->db->where('sid',$sid); 
		}
		if(!empty($rid)){
			$this->db->where('rid',$rid); 
		}
		if(!empty($rsid)){
			$this->db->where('rsid',$rsid); 
		}
		if(!empty($tid)){
			$this->db->where('tid',$tid); 
		} 
		//$entry = $this->db->where('id',$id)->get('sma_accounts_entries')->row_array();
		 $entry = $this->db->get('sma_accounts_entries')->row_array(); 
        
		/* if entry [NOT] found */
		if (!$entry)
		{  
			
			$this->session->set_flashdata('error', lang('Error! Journal entry not found'));
			$referrer = $this->input->server('HTTP_REFERER', TRUE);
			if (!empty($referrer)) {
				redirect($referrer);
			} else { 
				  redirect('admin/entries');
				//admin_redirect('accounts/entries_index'); 
			}
		}

		// Get payment Reference
		$q = $this->db->get_where('payment_reference', ['journal_id' => $entry['id']], 1);
		if ($q->num_rows() > 0) {
			$payment_reference =  $q->row();
			$payement_reference_id = $payment_reference->id;
		}	

		$this->data['payement_reference_id'] = isset($payement_reference_id) ? $payement_reference_id : 0;
		
		/* Initial data */
		$curEntryitems = array(); // initilize current entry items array
		$this->db->where('entry_id', $entry['id']); // select where entry_id equals $id

		// store selected data to $curEntryitemsData
		$curEntryitemsData = $this->db->get('sma_accounts_entryitems')->result_array();

		$dr_amount_total=0;
		$cr_amount_total=0;
		// loop to store selected entry items to current entry items array
		foreach ($curEntryitemsData as $row => $data)
		{
			$company_name = "";
			$supplier_name = "";
			$department_name = "";
			$employee_name = "";
			if($data['customer_id']){
				 $q = $this->db->get_where('companies', ['id' => $data['customer_id']], 1);
				 if ($q->num_rows() > 0) {
					$customer =  $q->row();
					$company_name = $customer->name;
				}	
			 
			}
			if($data['department_id']){
				$q = $this->db->get_where('departments', ['id' => $data['department_id']]);
				if ($q->num_rows() > 0) {
					$department =  $q->row();
					$department_name = $department->name;
				}				
			}
			if($data['supplier_id']){
				 
				$q = $this->db->get_where('companies', ['id' => $data['supplier_id']], 1);
				 if ($q->num_rows() > 0) {
					$customer =  $q->row();
					$supplier_name = $customer->name;
				}				
			}
			if($data['employee_id']){
				$q = $this->db->get_where('employees', ['id' => $data['employee_id']]);
				if ($q->num_rows() > 0) {
					$employee =  $q->row();
					$employee_name = $employee->name;
				}				
			}

			// if debit entry
			if ($data['dc'] == 'D')
			{
				$curEntryitems[$row] = array
				(
					'dc' => $data['dc'],
					'ledger_id' => $data['ledger_id'],
					'ledger_name' => $this->ledger_model->getName($data['ledger_id']),
					'dr_amount' => $data['amount'],
					'cr_amount' => '',
					'narration' => $data['narration'],
					'customer_id' => $data['customer_id'],
					'customer_name' =>  $company_name,
					'supplier_id' => $data['supplier_id'],
					'supplier_name' => $supplier_name,
					'department_id' => $data['department_id'],
					'department_name' => $department_name,
					'employee_id'  => $data['employee_id'],
					'employee_name' => $employee_name
				);
				$dr_amount_total =($dr_amount_total)+($data['amount']);
			}else // if credit entry
			{
				$curEntryitems[$row] = array
				(
					'dc' => $data['dc'],
					'ledger_id' => $data['ledger_id'],
					'ledger_name' => $this->ledger_model->getName($data['ledger_id']),
					'dr_amount' => '',
					'cr_amount' => $data['amount'],
					'narration' => $data['narration'],
					'customer_id' => $data['customer_id'],
					'customer_name' =>  $company_name,
					'supplier_id' => $data['supplier_id'],
					'supplier_name' => $supplier_name,
					'department_id' => $data['department_id'],
					'department_name' => $department_name,
					'employee_id'  => $data['employee_id'],
					'employee_name' => $employee_name


				);
			
                $cr_amount_total =($cr_amount_total)+($data['amount']);
			}
		}

		

		$this->data['defaultAttachments']     = $this->site->getAttachments($entry['id'], 'journal');

		// Load JL Entry specific attachments from sma_accounts_entry_attachments table
		$jl_attachments = $this->db->where('entry_id', $entry['id'])
		                            ->get('sma_accounts_entry_attachments')
		                            ->result_array();
		$this->data['jl_attachments'] = $jl_attachments;

		if($entry['pid'] > 0){
			$this->data['purchasesAttachments']  = $this->site->getAttachments($entry['pid'], 'purchase');
		}

		if($entry['sid']> 0){
			$this->data['saleAttachments']     = $this->site->getAttachments($entry['sid'], 'sale');
		}

		if($entry['tid']> 0){
			$this->data['transferAttachments']     = $this->site->getAttachments($entry['tid'], 'transfer');
		}

		if($entry['transaction_type']=='purchaseorder' and $entry['pid'] > 0){ 
			$purchase = $this->db->where('id',$entry['pid'])->get('sma_purchases')->row_array();
			$this->data['purchase'] = $purchase; 
			// $this->data['supplier'] = $this->db->where('id',$entry['supplier_id'])->get('sma_companies')->row_array(); 
			$this->data['supplier'] = $this->db->where('id',$purchase['supplier_id'])->get('sma_companies')->row_array(); 
						  
		}
		if($entry['sid']> 0){ 
			$sales = $this->db->where('id',$entry['sid'])->get('sma_sales')->row_array();
			$this->data['sales']= $sales ; 
			$this->data['customer'] = $this->db->where('id',$sales['customer_id'])->get('sma_companies')->row_array();
		}

		if($entry['tid']> 0){ 
			$transfer = $this->db->where('id',$entry['tid'])->get('sma_transfers')->row_array();
			$this->data['transfer']= $transfer ; 
			 
		} 

		$this->data['curEntryitems'] = $curEntryitems; // pass current entry items to view
		$this->data['allTags'] = $this->db->get('sma_accounts_tags')->result_array(); // fetch all tags and pass to view
		$this->data['entry'] = $entry; // pass entry to view
		
		$this->data['dr_amount_total'] = $dr_amount_total;
		$this->data['cr_amount_total'] = $cr_amount_total;
		// render page
		$bc  = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('entries'), 'page' => lang('Entries')], ['link' => '#', 'page' => lang('Entries')]];
        $meta = ['page_title' => lang('Accounts'), 'bc' => $bc];
        $this->page_construct('accounts/entries_view', $meta, $this->data);
	}

	/**
	 * Add a row in the entry via ajax
	 *
	 * @param string $addType
	 * @return void
	 */
	function addrow($restriction_bankcash) {

		// $this->layout = null; 

		/* Ledger selection */
		// $ledgers = new LedgerTree(); // initilize ledgers array - LedgerTree Lib
		// $ledgers->Group = &$this->Group; // initilize selected ledger groups in ledgers array
		// $ledgers->Ledger = &$this->Ledger; // initilize selected ledgers in ledgers array
		// $ledgers->current_id = -1; // initilize current group id
		// // set restriction_bankcash from entrytype
		// $ledgers->restriction_bankcash = $restriction_bankcash;
		// $ledgers->build(0); // set ledger id to [NULL] and ledger name to [None] 
		// $ledgers->toList($ledgers, -1); // create a list of ledgers array
		// $data['ledger_options'] = $ledgers->ledgerList; // pass ledger list to view
		// $this->load->view('entries/addrow', $data); // load view
		$items['customers'] = $this->site->getAllCompanies('customer');
		$items['suppliers'] = $this->site->getAllCompanies('supplier');
		$items['departments'] = $this->site->getAllDepartments();
		$items['employees'] = $this->site->getAllEmployees();
		$this->load->view($this->theme .'accounts/entries_addrow', ['items'=> $items]); // load view
	}

	/**
	 * Add a row in the entry via ajax
	 *
	 * @param string $addType
	 * @return void
	 */
	function addentry() {
		if (isset($_POST) && !empty($_POST)) {
			$data['entryitem'] = $_POST;
			$this->load->view($this->theme.'accounts/entries_addentry', $data); // load view
		}else{
			return FALSE;
		}
	}

	public function export($entrytypeLabel, $id, $type='xls')
	{
		ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);
		/* Check for valid entry type */
		if (empty($entrytypeLabel))
		{
			// set error alert
			$this->session->set_flashdata('error', lang('entries_cntrler_entrytype_not_specified_error'));
			// redirect to index page
			admin_redirect('entries/index');
		}

		// select entry type where label equals $entrytypeLabel and store to array
		$entrytype = $this->db->where('label',$entrytypeLabel)->get('sma_accounts_entrytypes')->row_array();
		
		// if entry type [NOT] found
		if (!$entrytype)
		{
			// set error alert
			$this->session->set_flashdata('error', lang('entries_cntrler_entrytype_not_found_error'));
			// redirect to index page
			admin_redirect('entries/index');
		}

		// pass entrytype to view
		$this->data['entrytype'] = $entrytype;

		/* Check if valid id */
		if (empty($id))
		{
			// set error alert
			$this->session->set_flashdata('error', lang('entries_cntrler_edit_entry_not_found_error'));
			// redirect to index page
			admin_redirect('entries/index');
		}

		// select entry where id equals $id and store to array
		$entry = $this->db->where('id',$id)->get('sma_accounts_entries')->row_array();

		/* if entry [NOT] found */
		if (!$entry)
		{
			// set error alert
			$this->session->set_flashdata('error', lang('entries_cntrler_edit_entry_not_found_error'));
			// redirect to index page
			admin_redirect('entries/index');
		}

		
		/* Initial data */
		$curEntryitems = array(); // initilize current entry items array
		$this->db->where('entry_id', $id); // select where entry_id equals $id

		// store selected data to $curEntryitemsData
		$curEntryitemsData = $this->db->get('sma_accounts_entryitems')->result_array();

		// loop to store selected entry items to current entry items array
		foreach ($curEntryitemsData as $row => $data)
		{
			// if debit entry
			if ($data['dc'] == 'D')
			{
				$curEntryitems[$row] = array
				(
					'dc' => $data['dc'],
					'ledger_id' => $data['ledger_id'],
					'ledger_name' => $this->ledger_model->getName($data['ledger_id']),
					'dr_amount' => $data['amount'],
					'cr_amount' => '',
					'narration' => $data['narration']
				);
			}else // if credit entry
			{
				$curEntryitems[$row] = array
				(
					'dc' => $data['dc'],
					'ledger_id' => $data['ledger_id'],
					'ledger_name' => $this->ledger_model->getName($data['ledger_id']),
					'dr_amount' => '',
					'cr_amount' => $data['amount'],
					'narration' => $data['narration']

				);
			}
		}


        if (!empty($data)) {
			
			// For PDF export, use Mpdf like customer_statement
			if ($type == 'pdf') {
				// Load JL Entry attachments
				$jl_attachments = $this->db->where('entry_id', $entry['id'])
				                            ->get('sma_accounts_entry_attachments')
				                            ->result_array();
				
				// Prepare data for view
				$view_data = array(
					'entrytype' => $entrytype,
					'entry' => $entry,
					'curEntryitems' => $curEntryitems,
					'Settings' => $this->Settings,
					'mSettings' => $this->mSettings,
					'jl_attachments' => $jl_attachments
				);
				
				// Generate filename
				$filename = ucfirst($entrytypeLabel) . '_Entry_' . $entry['number'] . '.pdf';
				
				// Load view and get HTML
				$html = $this->load->view($this->theme . 'accounts/entries_export_pdf', $view_data, true);
				
				// Create PDF using Mpdf
				$mpdf = new Mpdf([
					'format' => 'A4',
					'orientation' => 'P',
					'margin_top' => 10,
					'margin_bottom' => 10,
					'margin_left' => 10,
					'margin_right' => 10,
				]);
				
				$mpdf->WriteHTML($html);
				$mpdf->Output($filename, "D"); // D for download
				exit();
			}
			
			// For Excel export, keep the existing logic
			if ($type == 'xls') {
				// Load old PHPExcel library for Excel export
				require_once APPPATH . 'third_party/PHPExcel/PHPExcel.php';
				$this->excel = new PHPExcel();
				$this->excel->setActiveSheetIndex(0);
				
				if ($this->mSettings->drcr_toby == 'toby') {
					$drcr_toby = lang('entries_views_views_th_to_by');
				} else {
					$drcr_toby = lang('entries_views_views_th_dr_cr');
				}
				$this->excel->getActiveSheet()->setTitle(ucfirst($entrytypeLabel).lang('entry_title')."  #".$entry['number']);

				$this->excel->getActiveSheet()->SetCellValue('A1', ucfirst($entrytypeLabel).lang('entry_title')."  #".$entry['number']);
				$this->excel->getActiveSheet()->mergeCells('A1:E1');

				$this->excel->getActiveSheet()->SetCellValue('A2', lang('entries_views_add_label_date').": ".$entry['date']);
				$this->excel->getActiveSheet()->mergeCells('A2:E2');


				$this->excel->getActiveSheet()->SetCellValue('A3', $drcr_toby);
				$this->excel->getActiveSheet()->SetCellValue('B3', lang('entries_views_views_th_ledger'));
				$this->excel->getActiveSheet()->SetCellValue('C3', lang('entries_views_views_th_dr_amount'));
				$this->excel->getActiveSheet()->SetCellValue('D3', lang('entries_views_views_th_cr_amount'));
				$this->excel->getActiveSheet()->SetCellValue('E3', lang('entries_views_views_th_narration') );

				$row = 4;
				$ttotal = 0;
				$ttotal_tax = 0;
				$tgrand_total = 0;
				foreach ($curEntryitems as $entryitem) {
					$ir = $row + 1;
					if ($ir % 2 == 0) {
						$style_header = array(                  
							'fill' => array(
								'type' => PHPExcel_Style_Fill::FILL_SOLID,
								'color' => array('rgb'=>'CCCCCC'),
							),
						);
						$this->excel->getActiveSheet()->getStyle("A$row:E$row")->applyFromArray( $style_header );
					}

					if ($this->mSettings->drcr_toby == 'toby') {
						if ($entryitem['dc'] == 'D') {
							$dr_cr_rows = lang('entries_views_views_toby_D');
						} else {
							$dr_cr_rows = lang('entries_views_views_toby_C');
						}
					} else {
						if ($entryitem['dc'] == 'D') {
							$dr_cr_rows = lang('entries_views_views_drcr_D');
						} else {
							$dr_cr_rows = lang('entries_views_views_drcr_C');
						}
					}


				
					$this->excel->getActiveSheet()->SetCellValue('A' . $row, $dr_cr_rows);
					$this->excel->getActiveSheet()->SetCellValue('B' . $row, $entryitem['ledger_name']);
					$this->excel->getActiveSheet()->SetCellValue('C' . $row, $entryitem['dc'] == 'D' ? $entryitem['dr_amount'] : '');
					$this->excel->getActiveSheet()->SetCellValue('D' . $row, $entryitem['dc'] == 'C' ? $entryitem['cr_amount'] : '');
					$this->excel->getActiveSheet()->SetCellValue('E' . $row, $entryitem['narration']);
					$row++;
				}
				$style_header = array(                  
					'fill' => array(
						'type' => PHPExcel_Style_Fill::FILL_SOLID,
						'color' => array('rgb'=>'fdbf2d'),
					),
				);


				$this->excel->getActiveSheet()->getStyle("A$row:E$row")->applyFromArray( $style_header );

				$this->excel->getActiveSheet()->SetCellValue("A$row", lang('entries_views_views_td_total'));
				$this->excel->getActiveSheet()->mergeCells("A$row:B$row");
				$this->excel->getActiveSheet()->SetCellValue("C$row", $this->functionscore->toCurrency('D', $entry['dr_total']));
				$this->excel->getActiveSheet()->SetCellValue("D$row", $this->functionscore->toCurrency('C', $entry['cr_total']));


				if ($this->functionscore->calculate($entry['dr_total'], $entry['cr_total'], '==')) {
					/* Do nothing */
				} else {
					if ($this->functionscore->calculate($entry['dr_total'], $entry['cr_total'], '>')) {
						$this->excel->getActiveSheet()->SetCellValue("A$row", lang('entries_views_views_td_diff'));
						$this->excel->getActiveSheet()->mergeCells("A$row:B$row");
						$this->excel->getActiveSheet()->SetCellValue("C$row",  $this->functionscore->toCurrency('D', $this->functionscore->calculate($entry['dr_total'], $entry['cr_total'], '-')));
					} else {
						$this->excel->getActiveSheet()->SetCellValue("A$row", lang('entries_views_views_td_diff'));
						$this->excel->getActiveSheet()->mergeCells("A$row:C$row");
						$this->excel->getActiveSheet()->SetCellValue("D$row", $this->functionscore->toCurrency('C', $this->functionscore->calculate($entry['cr_total'], $entry['dr_total'], '-')));
					}
				}

				$this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(5);
				$this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(60);
				$this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(15);
				$this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(15);
				$this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(60);
			   
				$filename = 'entry_print';
				$this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);

			
				$this->excel->getActiveSheet()->getStyle('C2:C' . ($row))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
				$this->excel->getActiveSheet()->getStyle('D2:D' . ($row))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);

				$this->excel->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);

				$header = 'A1:E1';
				$this->excel->getActiveSheet()->getStyle($header)->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('94ce58');
				$style = array(
					'font' => array('bold' => true,),
					'alignment' => array('horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,),
				);
				$this->excel->getActiveSheet()->getStyle($header)->applyFromArray($style);
				
				$header = 'A2:E2';
				$this->excel->getActiveSheet()->getStyle($header)->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('fdbf2d');
				$style = array(
					'font' => array('bold' => true,),
					'alignment' => array('horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,),
				);
				$this->excel->getActiveSheet()->getStyle($header)->applyFromArray($style);

				header('Content-Type: application/vnd.ms-excel');
				header('Content-Disposition: attachment;filename="' . $filename . '.xls"');
				header('Cache-Control: max-age=0');
				$objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'Excel5');
				$objWriter->save('php://output');
				exit();
			}
        }
	}

	// =========================================================================
	// RECURRING JV TEMPLATES: Depreciation, Amortization & Custom
	// =========================================================================

	/**
	 * List all recurring JV templates.
	 */
	public function recurring_index()
	{
		$schedules = $this->db->order_by('id', 'DESC')->get('sma_jl_recurring_schedules')->result_array();

		foreach ($schedules as &$s) {
			$s['posted_count'] = $this->db->where(['schedule_id' => $s['id'], 'status' => 'posted'])->count_all_results('sma_jl_recurring_schedule_items');
			$s['line_count']   = $this->db->where('schedule_id', $s['id'])->count_all_results('sma_jl_recurring_schedule_lines');
		}
		unset($s);

		$this->data['schedules'] = $schedules;
		$bc   = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('entries'), 'page' => 'Entries'], ['link' => '#', 'page' => 'Recurring JV Templates']];
		$meta = ['page_title' => 'Recurring JV Templates'];
		$this->page_construct('accounts/recurring_index', $meta, $this->data);
	}

	/**
	 * Add a new recurring JV template (multi-account debit/credit).
	 */
	public function recurring_add()
	{
		$errors = [];

		if ($this->input->post()) {
			$name          = trim($this->input->post('name'));
			$type          = trim($this->input->post('type')) ?: 'depreciation';
			$periodType    = $this->input->post('period_type') ?: 'monthly';
			$narration     = $this->input->post('narration');
			$entrytypeId   = $this->input->post('entrytype_id') ?: null;
			$debitLedgers  = $this->input->post('debit_ledger_id')  ?: [];
			$creditLedgers = $this->input->post('credit_ledger_id') ?: [];

			if (!$name)             $errors[] = 'Template Name is required.';
			if (empty($debitLedgers))  $errors[] = 'At least one Debit account is required.';
			if (empty($creditLedgers)) $errors[] = 'At least one Credit account is required.';

			// Validate all ledger IDs
			$allIds = array_merge((array)$debitLedgers, (array)$creditLedgers);
			foreach ($allIds as $lid) {
				if (!ctype_digit((string)$lid) || (int)$lid <= 0) {
					$errors[] = 'One or more selected accounts are invalid.';
					break;
				}
			}

			if (empty($errors)) {
				$scheduleData = [
					'type'                => $type,
					'name'                => $name,
					'description'         => $this->input->post('description'),
					'period_type'         => $periodType,
					'narration'           => $narration,
					'entrytype_id'        => $entrytypeId,
					'status'              => 'active',
					'created_by'          => $this->session->userdata('user_id'),
					// Fields kept for schema compatibility; not used in JV template mode
					'total_amount'        => 0,
					'salvage_value'       => 0,
					'periods'             => 0,
					'amount_per_period'   => 0,
					'ledger_debit_id'     => (int)$debitLedgers[0],
					'ledger_credit_id'    => (int)$creditLedgers[0],
					'start_date'          => date('Y-m-d'),
				];
				$this->db->insert('sma_jl_recurring_schedules', $scheduleData);
				$scheduleId = $this->db->insert_id();

				// Insert debit template lines
				foreach ((array)$debitLedgers as $i => $lid) {
					if (!(int)$lid) continue;
					$this->db->insert('sma_jl_recurring_schedule_lines', [
						'schedule_id'   => $scheduleId,
						'ledger_id'     => (int)$lid,
						'dc'            => 'D',
						'display_order' => $i,
						'notes'         => (isset($_POST['debit_notes'][$i]) ? trim($_POST['debit_notes'][$i]) : null),
					]);
				}

				// Insert credit template lines
				foreach ((array)$creditLedgers as $i => $lid) {
					if (!(int)$lid) continue;
					$this->db->insert('sma_jl_recurring_schedule_lines', [
						'schedule_id'   => $scheduleId,
						'ledger_id'     => (int)$lid,
						'dc'            => 'C',
						'display_order' => $i,
						'notes'         => (isset($_POST['credit_notes'][$i]) ? trim($_POST['credit_notes'][$i]) : null),
					]);
				}

				$this->session->set_flashdata('message', 'JV Template "' . $name . '" created successfully.');
				admin_redirect('entries/recurring_view/' . $scheduleId);
			}
		}

		$this->data['ledger_options'] = $this->db->select('id, code, name')->order_by('code', 'asc')->get('sma_accounts_ledgers')->result_array();
		$this->data['entrytypes']     = $this->db->get('sma_accounts_entrytypes')->result_array();
		$this->data['error']          = !empty($errors) ? implode('<br>', $errors) : $this->session->flashdata('error');

		$bc   = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('entries/recurring_index'), 'page' => 'Recurring JV Templates'], ['link' => '#', 'page' => 'Add Template']];
		$meta = ['page_title' => 'Add Recurring JV Template', 'bc' => $bc];
		$this->page_construct('accounts/recurring_add', $meta, $this->data);
	}

	/**
	 * View a recurring JV template: shows account lines and posted vouchers.
	 */
	public function recurring_view($id = null)
	{
		if (!$id) { admin_redirect('entries/recurring_index'); }

		$schedule = $this->db->where('id', $id)->get('sma_jl_recurring_schedules')->row_array();
		if (!$schedule) {
			$this->session->set_flashdata('error', 'Template not found.');
			admin_redirect('entries/recurring_index');
		}

		// Load all template lines with ledger info
		$lines = $this->db
			->select('l.id, l.ledger_id, l.dc, l.display_order, l.notes, a.code as ledger_code, a.name as ledger_name')
			->from('sma_jl_recurring_schedule_lines l')
			->join('sma_accounts_ledgers a', 'a.id = l.ledger_id', 'left')
			->where('l.schedule_id', $id)
			->order_by('l.dc', 'ASC')
			->order_by('l.display_order', 'ASC')
			->get()->result_array();

		// Load posted vouchers (schedule items)
		$items = $this->db
			->where('schedule_id', $id)
			->order_by('period_number', 'DESC')
			->get('sma_jl_recurring_schedule_items')->result_array();

		$this->data['schedule']      = $schedule;
		$this->data['debit_lines']   = array_values(array_filter($lines, function($l){ return $l['dc'] === 'D'; }));
		$this->data['credit_lines']  = array_values(array_filter($lines, function($l){ return $l['dc'] === 'C'; }));
		$this->data['items']         = $items;

		$bc   = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('entries/recurring_index'), 'page' => 'Recurring JV Templates'], ['link' => '#', 'page' => $schedule['name']]];
		$meta = ['page_title' => 'JV Template: ' . $schedule['name'], 'bc' => $bc];
		$this->page_construct('accounts/recurring_view', $meta, $this->data);
	}

	/**
	 * Post a monthly voucher using the JV template.
	 * GET:  Shows form with template accounts; user enters amounts.
	 * POST: Validates balance (debit = credit), creates journal entry.
	 */
	public function recurring_post_voucher($scheduleId = null)
	{
		if (!$scheduleId) { admin_redirect('entries/recurring_index'); }

		$schedule = $this->db->where('id', $scheduleId)->get('sma_jl_recurring_schedules')->row_array();
		if (!$schedule) {
			$this->session->set_flashdata('error', 'Template not found.');
			admin_redirect('entries/recurring_index');
		}

		// Load template lines with ledger info
		$lines = $this->db
			->select('l.id, l.ledger_id, l.dc, l.display_order, l.notes, a.code as ledger_code, a.name as ledger_name')
			->from('sma_jl_recurring_schedule_lines l')
			->join('sma_accounts_ledgers a', 'a.id = l.ledger_id', 'left')
			->where('l.schedule_id', $scheduleId)
			->order_by('l.dc', 'ASC')
			->order_by('l.display_order', 'ASC')
			->get()->result_array();

		$debitLines  = array_values(array_filter($lines, function($l){ return $l['dc'] === 'D'; }));
		$creditLines = array_values(array_filter($lines, function($l){ return $l['dc'] === 'C'; }));

		$errors = [];

		if ($this->input->post()) {
			$voucherDate  = $this->functionscore->dateToSql($this->input->post('voucher_date'));
			$voucherMonth = trim($this->input->post('voucher_month'));
			$narration    = trim($this->input->post('narration')) ?: ($schedule['narration'] ?: $schedule['name']);
			$debitAmts    = $this->input->post('debit_amount')  ?: [];  // [line_id => amount]
			$creditAmts   = $this->input->post('credit_amount') ?: [];  // [line_id => amount]

			if (!$voucherDate) $errors[] = 'Voucher Date is required.';

			$totalDebit  = 0;
			$totalCredit = 0;
			foreach ($debitLines  as $l) { $totalDebit  += (float)($debitAmts[$l['id']]  ?? 0); }
			foreach ($creditLines as $l) { $totalCredit += (float)($creditAmts[$l['id']] ?? 0); }

			if ($totalDebit == 0 && $totalCredit == 0) {
				$errors[] = 'Please enter at least one amount.';
			} elseif (abs($totalDebit - $totalCredit) > 0.005) {
				$errors[] = sprintf(
					'Entry is not balanced. Debit total (%s) must equal Credit total (%s). Difference: %s',
					number_format($totalDebit, 2),
					number_format($totalCredit, 2),
					number_format(abs($totalDebit - $totalCredit), 2)
				);
			}

			if (empty($errors)) {
				// Resolve entry type
				$entrytypeId = $schedule['entrytype_id'];
				if (!$entrytypeId) {
					$et = $this->db->limit(1)->get('sma_accounts_entrytypes')->row_array();
					$entrytypeId = $et ? $et['id'] : 1;
				}

				// Auto-number the journal entry
				$maxNum  = $this->db->select('MAX(number) AS mx')->get('sma_accounts_entries')->row_array();
				$nextNum = ($maxNum['mx'] ?? 0) + 1;

				$fullNarration = $narration . ($voucherMonth ? ' – ' . $voucherMonth : '');

				$this->db->insert('sma_accounts_entries', [
					'entrytype_id'     => $entrytypeId,
					'number'           => $nextNum,
					'date'             => $voucherDate,
					'dr_total'         => $totalDebit,
					'cr_total'         => $totalCredit,
					'notes'            => $fullNarration,
					'transaction_type' => $schedule['type'],
				]);
				$entryId = $this->db->insert_id();

				// Insert debit entry items
				foreach ($debitLines as $line) {
					$amt = (float)($debitAmts[$line['id']] ?? 0);
					if ($amt <= 0) continue;
					$this->db->insert('sma_accounts_entryitems', [
						'entry_id'  => $entryId,
						'dc'        => 'D',
						'ledger_id' => $line['ledger_id'],
						'amount'    => $amt,
						'narration' => $fullNarration,
					]);
				}

				// Insert credit entry items
				foreach ($creditLines as $line) {
					$amt = (float)($creditAmts[$line['id']] ?? 0);
					if ($amt <= 0) continue;
					$this->db->insert('sma_accounts_entryitems', [
						'entry_id'  => $entryId,
						'dc'        => 'C',
						'ledger_id' => $line['ledger_id'],
						'amount'    => $amt,
						'narration' => $fullNarration,
					]);
				}

				// Record posting in schedule items
				$nextPeriod = $this->db->where('schedule_id', $scheduleId)->count_all_results('sma_jl_recurring_schedule_items') + 1;
				$this->db->insert('sma_jl_recurring_schedule_items', [
					'schedule_id'   => $scheduleId,
					'period_number' => $nextPeriod,
					'due_date'      => $voucherDate,
					'amount'        => $totalDebit,
					'entry_id'      => $entryId,
					'status'        => 'posted',
					'posted_at'     => date('Y-m-d H:i:s'),
				]);

				$this->session->set_flashdata('message', 'Voucher posted successfully as Journal Entry #' . $nextNum . '.');
				admin_redirect('entries/recurring_view/' . $scheduleId);
			}
		}

		$this->data['schedule']      = $schedule;
		$this->data['debit_lines']   = $debitLines;
		$this->data['credit_lines']  = $creditLines;
		$this->data['error']         = !empty($errors) ? implode('<br>', $errors) : null;
		// Next voucher number preview
		$maxNum = $this->db->select('MAX(number) AS mx')->get('sma_accounts_entries')->row_array();
		$this->data['next_entry_num'] = ($maxNum['mx'] ?? 0) + 1;
		$this->data['posted_count']   = $this->db->where('schedule_id', $scheduleId)->count_all_results('sma_jl_recurring_schedule_items');

		$bc   = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('entries/recurring_index'), 'page' => 'Recurring JV Templates'], ['link' => admin_url('entries/recurring_view/' . $scheduleId), 'page' => $schedule['name']], ['link' => '#', 'page' => 'Post Voucher']];
		$meta = ['page_title' => 'Post Voucher – ' . $schedule['name'], 'bc' => $bc];
		$this->page_construct('accounts/recurring_post_voucher', $meta, $this->data);
	}

	/**
	 * Delete a recurring JV template (and its items).
	 * Does NOT delete already-posted JL entries.
	 */
	public function recurring_delete($id = null)
	{
		if (!$id) { admin_redirect('entries/recurring_index'); }

		$schedule = $this->db->where('id', $id)->get('sma_jl_recurring_schedules')->row_array();
		if (!$schedule) {
			$this->session->set_flashdata('error', 'Template not found.');
			admin_redirect('entries/recurring_index');
		}

		// Delete template lines and schedule items (but not the actual JL entries)
		$this->db->where('schedule_id', $id)->delete('sma_jl_recurring_schedule_lines');
		$this->db->where('schedule_id', $id)->delete('sma_jl_recurring_schedule_items');
		$this->db->where('id', $id)->delete('sma_jl_recurring_schedules');

		$this->session->set_flashdata('message', 'Template "' . $schedule['name'] . '" deleted.');
		admin_redirect('entries/recurring_index');
	}

	// =========================================================================
	// SALARY RUNS
	// =========================================================================

	/**
	 * List all salary runs.
	 */
	public function salary_index()
	{
		$runs = $this->db->order_by('id', 'DESC')->get('sma_salary_runs')->result_array();
		$this->data['runs'] = $runs;

		$bc   = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('entries'), 'page' => 'Entries'], ['link' => '#', 'page' => 'Salary Runs']];
		$meta = ['page_title' => 'Salary Runs', 'bc' => $bc];
		$this->page_construct('accounts/salary_index', $meta, $this->data);
	}

	/**
	 * Create a new salary run (draft).
	 * Accepts employee rows via POST: rows[0][employee_id], rows[0][employee_name],
	 * rows[0][ledger_salary_exp_id], rows[0][ledger_payable_id],
	 * rows[0][gross_amount], rows[0][deductions], rows[0][narration], rows[0][department_id]
	 */
	public function salary_add()
	{
		$this->form_validation->set_rules('run_name',     'Run Name',    'required|trim');
		$this->form_validation->set_rules('period_month', 'Month',       'required|is_natural_no_zero|less_than[13]');
		$this->form_validation->set_rules('period_year',  'Year',        'required|is_natural_no_zero');
		$this->form_validation->set_rules('run_date',     'Run Date',    'required');

		if ($this->form_validation->run() == FALSE) {
			// Flat list of child ledgers — no tree, no groups
			$this->data['ledger_options'] = $this->db->select('id, code, name')->order_by('code', 'asc')->get('sma_accounts_ledgers')->result_array();
			$this->data['employees']      = $this->site->getAllEmployees();
			$this->data['departments']    = $this->site->getAllDepartments();
			$this->data['entrytypes']     = $this->db->get('sma_accounts_entrytypes')->result_array();
			$this->data['error']          = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));

			$bc   = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('entries/salary_index'), 'page' => 'Salary Runs'], ['link' => '#', 'page' => 'New Salary Run']];
			$meta = ['page_title' => 'New Salary Run', 'bc' => $bc];
			$this->page_construct('accounts/salary_add', $meta, $this->data);
			
			return;
		}

		$rows = $this->input->post('rows');
		if (empty($rows) || !is_array($rows)) {
			$this->session->set_flashdata('error', 'Please add at least one employee salary row.');
			admin_redirect('entries/salary_add');
		}

		// Calculate totals
		$totalGross      = 0;
		$totalDeductions = 0;
		$totalNet        = 0;
		$validRows       = [];

		foreach ($rows as $row) {
			if (empty($row['employee_name']) || empty($row['ledger_salary_exp_id']) || empty($row['ledger_payable_id'])) {
				continue;
			}
			$gross      = (float) ($row['gross_amount']  ?? 0);
			$deductions = (float) ($row['deductions']    ?? 0);
			$net        = $gross - $deductions;

			$totalGross      += $gross;
			$totalDeductions += $deductions;
			$totalNet        += $net;

			$validRows[] = [
				'employee_id'          => (int) ($row['employee_id'] ?? 0),
				'employee_name'        => $this->input->post('rows')[array_search($row, $rows)]['employee_name'] ?? $row['employee_name'],
				'ledger_salary_exp_id' => (int) $row['ledger_salary_exp_id'],
				'ledger_payable_id'    => (int) $row['ledger_payable_id'],
				'gross_amount'         => $gross,
				'deductions'           => $deductions,
				'net_amount'           => $net,
				'narration'            => $row['narration']     ?? '',
				'department_id'        => (int) ($row['department_id'] ?? 0),
			];
		}

		if (empty($validRows)) {
			$this->session->set_flashdata('error', 'No valid employee rows found. Please fill all required fields.');
			admin_redirect('entries/salary_add');
		}

		// Insert run header
		$runData = [
			'run_name'        => $this->input->post('run_name'),
			'period_month'    => (int) $this->input->post('period_month'),
			'period_year'     => (int) $this->input->post('period_year'),
			'run_date'        => $this->functionscore->dateToSql($this->input->post('run_date')),
			'description'     => $this->input->post('description'),
			'total_gross'     => $totalGross,
			'total_deductions' => $totalDeductions,
			'total_net'       => $totalNet,
			'entrytype_id'    => $this->input->post('entrytype_id') ?: null,
			'status'          => 'draft',
			'created_by'      => $this->session->userdata('user_id'),
		];
		$this->db->insert('sma_salary_runs', $runData);
		$runId = $this->db->insert_id();

		// Insert individual rows
		foreach ($validRows as $vr) {
			$vr['run_id'] = $runId;
			$this->db->insert('sma_salary_run_items', $vr);
		}

		$this->session->set_flashdata('message', 'Salary run "' . $runData['run_name'] . '" created as draft with ' . count($validRows) . ' employees.');
		admin_redirect('entries/salary_view/' . $runId);
	}

	/**
	 * View a salary run and its employee lines.
	 */
	public function salary_view($id = null)
	{
		if (!$id) { admin_redirect('entries/salary_index'); }

		$run = $this->db->where('id', $id)->get('sma_salary_runs')->row_array();
		if (!$run) {
			$this->session->set_flashdata('error', 'Salary run not found.');
			admin_redirect('entries/salary_index');
		}

		$items = $this->db->where('run_id', $id)->get('sma_salary_run_items')->result_array();

		// Attach ledger names for display
		foreach ($items as &$item) {
			$item['salary_exp_ledger_name'] = $this->ledger_model->getName($item['ledger_salary_exp_id']);
			$item['payable_ledger_name']    = $this->ledger_model->getName($item['ledger_payable_id']);
		}
		unset($item);

		$this->data['run']   = $run;
		$this->data['items'] = $items;

		// If already posted, attach JL entry info
		if ($run['entry_id']) {
			$this->data['jl_entry'] = $this->db->where('id', $run['entry_id'])->get('sma_accounts_entries')->row_array();
		}

		$bc   = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('entries/salary_index'), 'page' => 'Salary Runs'], ['link' => '#', 'page' => $run['run_name']]];
		$meta = ['page_title' => 'Salary Run: ' . $run['run_name'], 'bc' => $bc];
		$this->page_construct('accounts/salary_view', $meta, $this->data);
	}

	/**
	 * Post a salary run to the JL.
	 * Creates one combined JL entry: Dr each salary-expense ledger, Cr each salary-payable ledger.
	 */
	public function salary_post($id = null)
	{
		$run = $this->db->where('id', $id)->get('sma_salary_runs')->row_array();
		if (!$run) {
			$this->session->set_flashdata('error', 'Salary run not found.');
			admin_redirect('entries/salary_index');
		}

		if ($run['status'] === 'posted') {
			$this->session->set_flashdata('error', 'This salary run is already posted.');
			admin_redirect('entries/salary_view/' . $id);
		}

		$items = $this->db->where('run_id', $id)->get('sma_salary_run_items')->result_array();
		if (empty($items)) {
			$this->session->set_flashdata('error', 'No employee items to post.');
			admin_redirect('entries/salary_view/' . $id);
		}

		// Determine entry type
		$entrytypeId = $run['entrytype_id'];
		if (!$entrytypeId) {
			$et = $this->db->limit(1)->get('sma_accounts_entrytypes')->row_array();
			$entrytypeId = $et ? $et['id'] : 1;
		}

		$maxNum  = $this->db->select('MAX(number) AS mx')->get('sma_accounts_entries')->row_array();
		$nextNum = ($maxNum['mx'] ?? 0) + 1;

		$months      = ['', 'January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
		$periodLabel = $months[$run['period_month']] . ' ' . $run['period_year'];
		$notes       = 'Salary - ' . $run['run_name'] . ' (' . $periodLabel . ')';

		// Insert JL entry header — Dr total = Cr total = total_gross (net + deductions)
		$entryData = [
			'entrytype_id'     => $entrytypeId,
			'number'           => $nextNum,
			'date'             => $run['run_date'],
			'dr_total'         => $run['total_gross'],
			'cr_total'         => $run['total_gross'],
			'notes'            => $notes,
			'transaction_type' => 'salary',
		];
		$this->db->insert('sma_accounts_entries', $entryData);
		$entryId = $this->db->insert_id();

		// Insert Dr lines (Salary Expense) - gross per employee
		foreach ($items as $item) {
			$this->db->insert('sma_accounts_entryitems', [
				'entry_id'      => $entryId,
				'dc'            => 'D',
				'ledger_id'     => $item['ledger_salary_exp_id'],
				'amount'        => $item['gross_amount'],
				'narration'     => 'Salary: ' . $item['employee_name'] . ($item['narration'] ? ' - ' . $item['narration'] : ''),
				'employee_id'   => $item['employee_id'],
				'department_id' => $item['department_id'],
			]);
		}

		// Insert Cr lines (Salary Payable) per employee:
		//   Line 1 — net salary (what the employee receives)
		//   Line 2 — deductions (withheld from employee, held as payable until remitted)
		// Together they equal the gross, keeping Dr = Cr.
		foreach ($items as $item) {
			if ($item['net_amount'] > 0) {
				$this->db->insert('sma_accounts_entryitems', [
					'entry_id'      => $entryId,
					'dc'            => 'C',
					'ledger_id'     => $item['ledger_payable_id'],
					'amount'        => $item['net_amount'],
					'narration'     => 'Net Salary: ' . $item['employee_name'],
					'employee_id'   => $item['employee_id'],
					'department_id' => $item['department_id'],
				]);
			}
			if ($item['deductions'] > 0) {
				$this->db->insert('sma_accounts_entryitems', [
					'entry_id'      => $entryId,
					'dc'            => 'C',
					'ledger_id'     => $item['ledger_payable_id'],
					'amount'        => $item['deductions'],
					'narration'     => 'Deductions: ' . $item['employee_name'],
					'employee_id'   => $item['employee_id'],
					'department_id' => $item['department_id'],
				]);
			}
		}

		// Update run status
		$this->db->where('id', $id)->update('sma_salary_runs', [
			'status'   => 'posted',
			'entry_id' => $entryId,
		]);

		$this->session->set_flashdata('message', 'Salary run posted to JL as entry #' . $nextNum . '.');
		admin_redirect('entries/salary_view/' . $id);
	}

	/**
	 * Delete a salary run (only if still in draft status).
	 */
	public function salary_delete($id = null)
	{
		$run = $this->db->where('id', $id)->get('sma_salary_runs')->row_array();
		if (!$run) {
			$this->session->set_flashdata('error', 'Salary run not found.');
			admin_redirect('entries/salary_index');
		}

		if ($run['status'] === 'posted') {
			$this->session->set_flashdata('error', 'Cannot delete a posted salary run. Reverse the JL entry first.');
			admin_redirect('entries/salary_view/' . $id);
		}

		$this->db->where('run_id', $id)->delete('sma_salary_run_items');
		$this->db->where('id', $id)->delete('sma_salary_runs');

		$this->session->set_flashdata('message', 'Salary run "' . $run['run_name'] . '" deleted.');
		admin_redirect('entries/salary_index');
	}

}