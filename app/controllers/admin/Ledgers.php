<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Ledgers extends MY_Controller {
	public function __construct() {
        parent::__construct();
         $this->load->library('form_validation');
    }   

    /**
 * add method
 *
 * @return void
 */
	public function add() {

		$allowed = $this->mAccountSettings->decimal_places;
		$this->form_validation->set_rules('name', lang('ledgers_cntrler_add_form_validation_label_name'), 'required');
		$this->form_validation->set_rules('group_id', lang('ledgers_cntrler_add_form_validation_label_group_id'), 'required');
		$this->form_validation->set_rules('op_balance_dc', lang('ledgers_cntrler_add_form_validation_label_op_balance_dc'), 'required');
		$this->form_validation->set_rules('op_balance', lang('ledgers_cntrler_add_form_validation_label_op_balance'), "amount_okay[$allowed]");
		$this->form_validation->set_rules('code', lang('ledgers_cntrler_add_form_validation_label_code'), 'is_unique[sma_accounts_ledgers.code]');

		if ($this->form_validation->run() == FALSE) {
			$this->load->library('GroupTree');
			/* Create list of parent groups */
			$parentGroups = new GroupTree();
			$parentGroups->Group = &$this->Group;
			$parentGroups->current_id = -1;
			$parentGroups->build(0);
			$parentGroups->toList($parentGroups, -1);
			$this->data['parents'] = $parentGroups->groupList;
			// render page

			$bc  = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('accounts'), 'page' => lang('accounts')], ['link' => '#', 'page' => lang('Add Legder')]];
	        $meta = ['page_title' => lang('Accounts'), 'bc' => $bc];
	        $this->page_construct('accounts/ledger_add', $meta, $this->data);


        } else {
        	$data = array(
				'code' => NULL,
				'op_balance' => 0,
				'name' => $this->input->post('name'),
				'group_id' => $this->input->post('group_id'),
				'op_balance_dc' => $this->input->post('op_balance_dc'),
				'notes' => $this->input->post('notes'),
				'reconciliation' => 0,
				'type' => 0,
			);

			if (!empty($this->input->post('reconciliation'))) {
				$data['reconciliation'] = 1;
			}
			if (!empty($this->input->post('code'))) {
				$data['code'] = $this->input->post('code');
			}
			if (!empty($this->input->post('type'))) {
				$data['type'] = 1;
			}
			if (!empty($this->input->post('op_balance'))) {
				$data['op_balance'] = $this->input->post('op_balance');
			}
			/* Count number of decimal places */
			
			$this->db->insert('sma_accounts_ledgers', $data);
			//$this->settings_model->add_log(lang('ledgers_cntrler_add_label_add_log') . $this->input->post('name'), 1);
			
			$this->session->set_flashdata('message', sprintf(lang('ledgers_cntrler_add_ledger_created_successfully'), $this->input->post('name')));
			admin_redirect('accounts');
        }
	}

/**
 * edit method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function edit($id = null) {
		/* Check for valid group */
		if (empty($id)) {
			$this->session->set_flashdata('error', lang('ledgers_cntrler_edit_ledger_not_specified_error'));
			admin_redirect('accounts');
		}
		$ledger = $this->db->where('id', $id)->get('sma_accounts_ledgers')->row_array();
		if (!$ledger) {
			$this->session->set_flashdata('error', lang('ledgers_cntrler_edit_ledger_not_found_error'));
			admin_redirect('accounts');
		}
		$original_value = $ledger['code'] ;
	    if($this->input->post('code') != $original_value) {
	       $is_unique =  'is_unique[sma_accounts_ledgers.code]';
	    } else {
	       $is_unique =  '';
	    }

		$allowed = $this->mAccountSettings->decimal_places;
		$this->form_validation->set_rules('name', 'ledgers_cntrler_edit_form_validation_label_name', 'required');
		$this->form_validation->set_rules('group_id', 'ledgers_cntrler_edit_form_validation_label_group_id', 'required');
		$this->form_validation->set_rules('op_balance_dc', 'ledgers_cntrler_edit_form_validation_label_op_balance_dc', 'required');
		$this->form_validation->set_rules('op_balance', 'ledgers_cntrler_edit_form_validation_label_op_balance', "amount_okay[$allowed]");
		$this->form_validation->set_rules('code', 'ledgers_cntrler_edit_form_validation_label_code', $is_unique);

		if ($this->form_validation->run() == FALSE) {
			$this->load->library('GroupTree');
			/* Create list of parent groups */
			$parentGroups = new GroupTree();
			$parentGroups->Group = &$this->Group;
			$parentGroups->current_id = -1;
			$parentGroups->build(0);
			$parentGroups->toList($parentGroups, -1);
			$this->data['parents'] = $parentGroups->groupList;
			$this->data['ledger'] = $ledger;
			// render page
		$bc  = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('accounts'), 'page' => lang('accounts')], ['link' => '#', 'page' => lang('Edit Legder')]];
	        $meta = ['page_title' => lang('Accounts'), 'bc' => $bc];
	        $this->page_construct('accounts/ledger_edit', $meta, $this->data);
        } else {
        	/* Check if acccount is locked */
			if ($this->mAccountSettings->account_locked == 1) {
				$this->session->set_flashdata('error', lang('ledgers_cntrler_edit_account_locked_error'));
				admin_redirect('accounts');
			}

			$data = array(
				'code' => NULL,
				'op_balance' => 0,
				'name' => $this->input->post('name'),
				'group_id' => $this->input->post('group_id'),
				'op_balance_dc' => $this->input->post('op_balance_dc'),
				'notes' => $this->input->post('notes'),
				'reconciliation' => 0,
				'type' => 0,
			);

			if (!empty($this->input->post('reconciliation'))) {
				$data['reconciliation'] = 1;
			}
			if (!empty($this->input->post('code'))) {
				$data['code'] = $this->input->post('code');
			}
			if (!empty($this->input->post('type'))) {
				$data['type'] = 1;
			}
			if (!empty($this->input->post('op_balance'))) {
				$data['op_balance'] = $this->input->post('op_balance');
			}
			
			$this->db->where('id', $id);
			$this->db->update('sma_accounts_ledgers', $data);
			//$this->settings_model->add_log(lang('ledgers_cntrler_edit_label_add_log') . $this->input->post('name'), 1);
			admin_redirect('accounts');
        }

	}


	/**
	 * delete method
	 *
	 * @throws NotFoundException
	 * @throws MethodNotAllowedException
	 * @param string $id
	 * @return void
	 */
	public function delete($id = null) {

		/* Check if valid id */
		if (empty($id)) {
			$this->session->set_flashdata('error', lang('ledgers_cntrler_delete_ledger_not_specified_error'));
			admin_redirect('accounts');
		}

		/* Check if ledger exists */
		$ledger = $this->db->where('id', $id)->get('sma_accounts_ledgers')->row_array();
		if (!$ledger) {
			$this->session->set_flashdata('error', lang('ledgers_cntrler_delete_ledger_not_found_error'));
			admin_redirect('accounts');
		}

		/* Check if any entry item using this ledger still exists */
		$this->db->where('sma_accounts_entryitems.ledger_id', $id);
		$q = $this->db->get('sma_accounts_entryitems');
		if ($q->num_rows() > 0) {
			$this->session->set_flashdata('error', lang('ledgers_cntrler_delete_entries_exist_error'));
			admin_redirect('accounts');
		}

		$this->db->delete('sma_accounts_ledgers', array('id' => $id));
		//$this->settings_model->add_log(lang('ledgers_cntrler_delete_label_add_log') . $ledger['name'], 1);

		$this->session->set_flashdata('message', sprintf(lang('ledgers_cntrler_delete_ledger_deleted_successfully'), $ledger['name']));
		admin_redirect('accounts');
	}

	/**
	 * closing balance method
	 *
	 * Return closing balance for the ledger
	 *
	 * @return void
	 */
	public function cl($id = null) {

		/* Read ledger id from url get request */
		if ($id == null) {
			$id = (int)$this->input->get('id');
		}

		/* Check if valid id */
		if (!$id) {
			// $this->data['cl'] = array('cl' => array('dc' => '', 'amount' => 0));
			echo json_encode(0);
			return;
		}

		/* Check if ledger exists */
		$this->db->where('id', $id);
		$ledger = $this->db->get('sma_accounts_ledgers')->row_array();
		if (!$ledger) {
			$cl = array('cl' => array('dc' => '', 'amount' => ''));
		}else{
			$cl = $this->ledger_model->closingBalance($id);
			$status = 'ok';
			if ($ledger['type'] == 1) {
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
		}
		echo json_encode($cl);
	}

	public function getNextCode() {
		$id = $_POST['id'];
		$this->db->where('id', $id);
		$p_group_code = $this->db->get('sma_accounts_groups')->row()->code;
		$this->db->where('group_id', $id);
		$q = $this->db->get('sma_accounts_ledgers')->result();
		if ($q) {
			$last = end($q);
			$last = $last->code;
			$l_array = explode('-', $last);
			$new_index = end($l_array);
			$new_index += 1;
			$new_index = sprintf("%04d", $new_index);
			echo $p_group_code."-".$new_index;
		}else{
			echo $p_group_code."-0001";
		}

	}


}



