<?php

defined('BASEPATH') or exit('No direct script access allowed');
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;

class Accounts extends MY_Controller
{
    public function __construct() {
        parent::__construct(); 

		$this->load->library('form_validation');
    } 
    
    	public function index() {
		
		$this->load->library('AccountList');
		
		
		$accountlist = new AccountList();
		$accountlist->Group = &$this->Group;
		$accountlist->Ledger = &$this->Ledger;
		$accountlist->only_opening = false;
		$accountlist->start_date = null;
		$accountlist->end_date = null;
		$accountlist->affects_gross = -1;
		$accountlist->start(0);

		$this->data['accountlist'] = $accountlist;
		$opdiff = $this->ledger_model->getOpeningDiff();
		$this->data['opdiff'] = $opdiff;

		$bc  = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('accounts'), 'page' => lang('accounts')], ['link' => '#', 'page' => lang('Accounts')]];
        $meta = ['page_title' => lang('Accounts'), 'bc' => $bc];
        $this->page_construct('accounts/index', $meta, $this->data);
	}

	public function mapper()
	{
		$this->load->library('reader');
		if ($this->input->method() == 'post') {
			$keys = array();
			for($i = 0;$i < $_POST['number_of_keys'];$i++){
				$keys[$_POST['default'.$i]] = $_POST['current'.$i];
			}
			$result = $this->reader->parse_file($_POST['file_path']);
			$this->import($result, $keys);
		}		
	}

	public function uploader()
	{
        if (isset($_FILES['accountcsv'])) {
        	if ($_FILES['accountcsv']['size'] > 0) {
			    $this->load->library('reader');

				$uploadPath = 'assets/uploads/temp/';

	            $config['upload_path'] = $uploadPath;
	            $config['allowed_types'] = 'text|csv';
	            $config['file_name'] = $_FILES['accountcsv']['name'];
	            $config['overwrite'] = TRUE;
	            $this->load->library('upload', $config);
	            $this->upload->initialize($config);
	            if($this->upload->do_upload('accountcsv')){
	                $fileData = $this->upload->data();
					if ($keys = $this->check_keys($fileData['full_path'])) {
						$result = $this->reader->parse_file($fileData['full_path']);
						$this->import($result, $keys);
					}
	            }else{
	            	if ($this->upload->display_errors()) {
	            		$this->session->set_flashdata('error', $this->upload->display_errors());
	            	}else{
		            	$this->session->set_flashdata('error', lang('admin_cntrler_uploadprofilepicture_error'));
	            	}
	            }
			}
		}else{
			//$this->render('accounts/uploader');
			$bc  = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('accounts/uploader'), 'page' => lang('Import Chart of Account')], ['link' => '#', 'page' => lang('Accounts')]];
        $meta = ['page_title' => lang('Import Chart of Account'), 'bc' => $bc];
        $this->page_construct('accounts/uploader', $meta, $this->data);
		}
	}


	public function check_keys($file_path)
	{

		$default_keys = $this->reader->parse_file(base_url('assets/csv/import_chart_of_accounts.csv'), true);
		$current_keys = $this->reader->parse_file($file_path, true);
    	if ($default_keys != $current_keys) {
    		$this->data['default_keys'] = $default_keys;
			$this->data['current_keys'] = $current_keys;
			$this->data['file_path'] = $file_path;
    		//$this->render('accounts/mapper');

    	$bc  = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('accounts/mapper'), 'page' => lang('Map Chart of Account')], ['link' => '#', 'page' => lang('Accounts')]];
        $meta = ['page_title' => lang('Map Chart of Account'), 'bc' => $bc];
        $this->page_construct('accounts/mapper', $meta, $this->data);

    	}else{
    		$keys = array();
    		foreach ($default_keys as $key => $value) {
    			$keys[$key] = $key;
    		}
    		return $keys;
    	}
	}

	/*public function import_excel()
	{
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

				if (!$this->upload->do_upload('excel_file')) {
					$error = $this->upload->display_errors();
					$this->session->set_flashdata('error', $error);
					admin_redirect('accounts');
				}

				$upload_data = $this->upload->data();
				$filePath = $upload_data['full_path'];

				// Load spreadsheet
				$reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
				$reader->setReadDataOnly(true);
				$spreadsheet = $reader->load($filePath);
				$sheet = $spreadsheet->getActiveSheet();
				$rows = $sheet->toArray(null, true, true, true);

				$this->db->trans_start();

				$groupCache = []; // cache to avoid duplicate group creation
				$opening_ledgers = []; // collect ledger balances for entries

				foreach ($rows as $index => $row) {
					if ($index == 1) continue; // skip header row

					$type                = isset($row['A']) ? trim($row['A']) : '';
					$col1                = isset($row['B']) ? trim($row['B']) : '';
					$finMain             = isset($row['C']) ? trim($row['C']) : '';
					$finSub              = isset($row['D']) ? trim($row['D']) : '';
					$account_code        = isset($row['E']) ? trim($row['E']) : '';
					$account_name_arabic = isset($row['F']) ? trim($row['F']) : '';
					$debit               = isset($row['G']) ? (float) str_replace(',', '', $row['G']) : 0.0;
					$credit              = isset($row['H']) ? (float) str_replace(',', '', $row['H']) : 0.0;

					if (empty($account_name_arabic)) continue;

					// Only Financial Report Main and Sub will form the group hierarchy
					$levels = array_filter([$finMain, $finSub]);
					$parent_id = NULL;
					$path = '';

					foreach ($levels as $level) {
						$level = trim($level);
						if ($level === '') continue;
						$path .= '>' . $level;

						if (!isset($groupCache[$path])) {
							$existing = $this->db->get_where('sma_accounts_groups', [
								'name' => $level,
								'parent_id' => $parent_id
							])->row();

							if ($existing) {
								$group_id = $existing->id;
							} else {
								$this->db->insert('sma_accounts_groups', [
									'parent_id'     => $parent_id,
									'name'          => $level,
									'name_arabic'   => $level,
									'affects_gross' => 0
								]);
								$group_id = $this->db->insert_id();

								if ($group_id) {
									$group_code = 'GRP-' . str_pad($group_id, 5, '0', STR_PAD_LEFT);
									$this->db->where('id', $group_id)->update('sma_accounts_groups', ['code' => $group_code]);
								}
							}
							$groupCache[$path] = $group_id;
						}

						$parent_id = $groupCache[$path];
					}

					// Determine opening balance and DC
					if ($debit > 0) {
						$op_balance    = $debit;
						$op_balance_dc = 'D';
					} else {
						$op_balance    = $credit;
						$op_balance_dc = 'C';
					}

					// Insert or update ledger
					$ledger = [
						'group_id'       => ($parent_id ? $parent_id : 0),
						'name'           => $account_name_arabic,
						'name_arabic'    => $account_name_arabic,
						'type1'          => $type,
						'type2'          => $col1,
						'category'       => $finMain,
						'code'           => $account_code,
						'op_balance'     => $op_balance,
						'op_balance_dc'  => $op_balance_dc,
						'type'           => 0,
						'reconciliation' => 0,
						'notes'          => ''
					];

					if (!empty($account_code)) {
						$exists = $this->db->get_where('sma_accounts_ledgers', ['code' => $account_code])->row();
					} else {
						$exists = $this->db->get_where('sma_accounts_ledgers', [
							'name_arabic' => $account_name_arabic,
							'group_id' => ($parent_id ? $parent_id : 0)
						])->row();
					}

					if ($exists) {
						$this->db->where('id', $exists->id)->update('sma_accounts_ledgers', $ledger);
						$ledger_id = $exists->id;
					} else {
						$this->db->insert('sma_accounts_ledgers', $ledger);
						$ledger_id = $this->db->insert_id();
					}

					// Collect opening balance for entry
					if ($op_balance > 0) {
						$opening_ledgers[] = [
							'ledger_id'      => $ledger_id,
							'op_balance'     => $op_balance,
							'op_balance_dc'  => $op_balance_dc
						];
					}
				}

				// ---- Insert Opening Balance Entry ----
				if (!empty($opening_ledgers)) {
					$dr_total = 0;
					$cr_total = 0;
					foreach ($opening_ledgers as $item) {
						if ($item['op_balance_dc'] == 'D') {
							$dr_total += $item['op_balance'];
						} else {
							$cr_total += $item['op_balance'];
						}
					}

					$entry_data = [
						'entrytype_id'     => 1, // define a type for opening balance
						'transaction_type' => 'opening_balance',
						'number'           => 'OB-' . date('YmdHis'),
						'date'             => date('Y-m-d'),
						'dr_total'         => $dr_total,
						'cr_total'         => $cr_total,
						'notes'            => 'Opening balances import',
						//'created_by'       => $this->session->userdata('user_id'),
					];
					$this->db->insert('sma_accounts_entries', $entry_data);
					$entry_id = $this->db->insert_id();

					foreach ($opening_ledgers as $item) {
						$item_data = [
							'entry_id' => $entry_id,
							'ledger_id' => $item['ledger_id'],
							'dc' => $item['op_balance_dc'],  // D or C
							'amount' => $item['op_balance'],
							'narration' => 'Opening balance',
						];
						$this->db->insert('sma_accounts_entryitems', $item_data);
					}
				}

				$this->db->trans_complete();

				if ($this->db->trans_status() === false) {
					$this->session->set_flashdata('error', 'Import failed! Please check data.');
				} else {
					$this->session->set_flashdata('message', 'Chart of Accounts imported successfully!');
				}

				if (file_exists($filePath)) unlink($filePath);

				redirect(admin_url('accounts'));
			}

		} else {
			$this->data['error']    = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
			$this->data['modal_js'] = $this->site->modal_js();
			$this->load->view($this->theme . 'accounts/import_excel', $this->data);
		}
	}*/

	public function import_excel()
	{
		$this->load->helper('security');
		$this->form_validation->set_rules('excel_file', lang('upload_file'), 'xss_clean');

		if ($this->form_validation->run() != true) {
			$this->data['error']    = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
			$this->data['modal_js'] = $this->site->modal_js();
			$this->load->view($this->theme . 'accounts/import_excel', $this->data);
			return;
		}

		$this->load->library('excel');
		if (!isset($_FILES['excel_file']) || $_FILES['excel_file']['size'] <= 0) {
			$this->session->set_flashdata('error', lang('no_file_selected'));
			admin_redirect('accounts');
		}

		$this->load->library('upload');
		$config['upload_path']   = 'files/';
		$config['allowed_types'] = 'xlsx|xls|csv';
		$config['max_size']      = '10000';
		$config['overwrite']     = false;
		$config['encrypt_name']  = true;
		$this->upload->initialize($config);

		if (!$this->upload->do_upload('excel_file')) {
			$error = $this->upload->display_errors();
			$this->session->set_flashdata('error', $error);
			admin_redirect('accounts');
		}

		$upload_data = $this->upload->data();
		$filePath = $upload_data['full_path'];

		$reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
		$reader->setReadDataOnly(true);
		$spreadsheet = $reader->load($filePath);
		$sheet = $spreadsheet->getActiveSheet();
		$rows = $sheet->toArray(null, true, true, true);

		// --- Helper closures ---
		$get_next_suffix = function ($table, $code_field, $parent_code, $suffix_len) {
			// Only consider codes with same parent prefix length
			$parent_len = strlen($parent_code);
			$this->db->select("MAX(CAST(SUBSTRING($code_field, " . ($parent_len + 1) . ", $suffix_len) AS UNSIGNED)) as maxcode", false);
			$this->db->like($code_field, $parent_code, 'after');
			$row = $this->db->get($table)->row();
			$maxcode = ($row && $row->maxcode) ? intval($row->maxcode) : 0;

			$suffix = $maxcode + 1;
			return str_pad($suffix, $suffix_len, '0', STR_PAD_LEFT);
		};

		$create_or_get_group = function ($parent_id, $parent_code, $name, $level) use ($get_next_suffix) {
			$existing = $this->db->get_where('sma_accounts_groups', [
				'name' => $name,
				'parent_id' => $parent_id
			])->row();

			if ($existing) {
				return ['id' => $existing->id, 'code' => $existing->code];
			}

			// --- Suffix length rules based on level ---
			switch ($level) {
				case 1:
					$suffix_len = 1; // 1,2,3
					break;
				case 2:
					$suffix_len = 1; // 01,02,03
					break;
				case 3:
					$suffix_len = 1; // 01,02,03
					break;
				case 4:
					$suffix_len = 2; // 01,02
					break;
				default:
					$suffix_len = 2;
			}

			$suffix = $get_next_suffix('sma_accounts_groups', 'code', $parent_code, $suffix_len);
			$new_code = $parent_code . $suffix;

			$data = [
				'parent_id' => $parent_id,
				'name' => $name,
				'name_arabic' => $name,
				'code' => $new_code,
				'affects_gross' => 0
			];
			$this->db->insert('sma_accounts_groups', $data);
			$gid = $this->db->insert_id();

			return ['id' => $gid, 'code' => $new_code];
		};

		// Start transaction
		$this->db->trans_start();

		$groupCacheByPath = [];

		foreach ($rows as $index => $row) {
			if ($index == 1) continue; // skip header

			$l1_name = isset($row['A']) ? trim($row['A']) : '';
			$l2_name = isset($row['B']) ? trim($row['B']) : '';
			$l3_name = isset($row['C']) ? trim($row['C']) : '';
			$l4_name = isset($row['D']) ? trim($row['D']) : '';
			$ledger_name = isset($row['E']) ? trim($row['E']) : '';
			$ledger_name_ar = isset($row['F']) ? trim($row['F']) : '';
			$old_code  = isset($row['G']) ? trim($row['G']) : '';

			if ($l1_name === '' && $l2_name === '' && $l3_name === '' && $l4_name === '' && $ledger_name === '') continue;

			// Determine type1/type2 based on Level1
			switch(strtolower($l1_name)) {
				case 'assets':
					$type1 = 'B/S'; $type2 = 'Asset'; break;
				case 'liabilities':
					$type1 = 'B/S'; $type2 = 'Liability'; break;
				case 'equity':
					$type1 = 'B/S'; $type2 = 'Equity'; break;
				case 'revenue':
					$type1 = 'P/L'; $type2 = 'Revenue'; break;
				case 'expenses':
					$type1 = 'P/L'; $type2 = 'Expense'; break;
				default:
					$type1 = 'B/S'; $type2 = 'Asset';
			}

			// --- Build hierarchy ---
			$levels = [$l1_name, $l2_name, $l3_name, $l4_name];
			$parent_id = null;
			$parent_code = '';
			$path = '';
			$level_num = 1;

			foreach ($levels as $level_name) {
				if (!$level_name) continue;

				$path .= ($path ? '>' : '') . $level_name;

				if (!isset($groupCacheByPath[$path])) {
					$g = $create_or_get_group($parent_id, $parent_code, $level_name, $level_num);
					$groupCacheByPath[$path] = $g;
				}

				$parent_id = $groupCacheByPath[$path]['id'];
				$parent_code = $groupCacheByPath[$path]['code'];
				$level_num++;
			}

			// --- Ledger creation ---
			if ($ledger_name) {
				$suffix = $get_next_suffix('sma_accounts_ledgers', 'code', $parent_code, 5);
				$ledger_code = $parent_code . $suffix;

				$ledger_data = [
					'group_id'      => $parent_id,
					'name'          => $ledger_name,
					'name_arabic'   => $ledger_name_ar,
					'type1'         => $type1,
					'type2'         => $type2,
					'category'      => '',
					'code'          => $ledger_code,
					'old_code'      => $old_code,
					'op_balance'    => 0,
					'op_balance_dc' => 0,
					'type'          => 0,
					'reconciliation'=> 0,
					'notes'         => ''
				];

				//echo '<pre>';print_r($ledger_data);

				$exists = $this->db->get_where('sma_accounts_ledgers', [
					'group_id' => $parent_id,
					'name' => $ledger_name
				])->row();

				if ($exists) {
					$this->db->where('id', $exists->id)->update('sma_accounts_ledgers', $ledger_data);
				} else {
					$this->db->insert('sma_accounts_ledgers', $ledger_data);
				}
			}
		}

		$this->db->trans_complete();

		if ($this->db->trans_status() === false) {
			$this->session->set_flashdata('error', 'Import failed! Please check data.');
		} else {
			$this->session->set_flashdata('message', 'Chart of Accounts imported successfully!');
		}

		if (file_exists($filePath)) unlink($filePath);
		redirect(admin_url('accounts'));
	}

	
	public function import($result, $keys)
	{
		if (count($result) > 1) {
	    	$g_counter = 0;
	    	$l_counter = 0;
	    	$parent_code = NULL;
	    	$parent_id = NULL;
	    	
	    	foreach ($result as $data) {
	    		$code = explode('-', $data[$keys['code']]);
				$group_count = count($code);

				if ($group_count > 1) {
					for ($i = 0; $i < $group_count-1; $i++) {
						if ($i == 0) {
							$parent_code = $code[$i];
						}else{
							$parent_code .= '-'.$code[$i];
						}
					}
				}

				if ($parent_code) {
					$this->db->where('code', $parent_code);
					$query = $this->db->get('sma_accounts_groups', 1);
					if ($query->num_rows() == 1) {
						$parent_group = $query->row_array();
						$parent_id = $parent_group['id'];
					}
				}

	    		if(strtolower($data[$keys['account_type']]) == 'group'){
					$insertdata = array(
						'parent_id' => $parent_id,
						'name' => $data[$keys['name']],
						'code' => $data[$keys['code']],
						'affects_gross' => $data[$keys['affects_gross']]
					);
					// /* Save group */
					if ($this->db->insert('sma_accounts_groups', $insertdata)) {
						$g_counter++;
						//$this->settings_model->add_log(lang('groups_cntrler_add_label_add_log') . $data[$keys['name']], 1);
					}
				}
				if (strtolower($data[$keys['account_type']]) == 'ledger') {
					$insertdata = array(
						'code' => $data[$keys['code']],
						'op_balance' => $data[$keys['opening_balance']],
						'name' => $data[$keys['name']],
						'group_id' => $parent_id,
						'op_balance_dc' => $data[$keys['debit_credit']],
						'notes' => $data[$keys['notes']],
						'reconciliation' => $data[$keys['reconciliation']],
						'type' => $data[$keys['bank_cash']],
					);
					/* Count number of decimal places */
					if($this->db->insert('sma_accounts_ledgers', $insertdata)){
						//$this->settings_model->add_log(lang('ledgers_cntrler_add_label_add_log') . $data[$keys['name']], 1);
						$l_counter++;
					}
				}
	    	}
	    	$this->session->set_flashdata('message', sprintf(lang('accounts_exporter_exported_successfully'), $g_counter, $l_counter));
	    	admin_redirect('accounts');
		}
	}

	public function download($file_path=null)
	{
		if ($file_path == null) {
			$file_path = 'import.csv';
		}

		$this->load->helper('download'); //load helper
        $download_path = $file_path;

        if(!empty($download_path)){
		    $data = file_get_contents(base_url() ."assets/uploads/temp/".$download_path); // Read the file's contents
		    $name = $download_path;
		 
		    force_download($name, $data);
		}
	}
}