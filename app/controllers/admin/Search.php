<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Search extends MY_Controller {
	public function __construct() {
        parent::__construct();
    }   
	

/**
 * index method
 *
 * @return void
 */
	public function index() {
				
		$this->data['showEntries'] = false;

		/* Ledger selection */
		$ledgers = new LedgerTree();
		$ledgers->Group = &$this->Group;
		$ledgers->Ledger = &$this->Ledger;
		$ledgers->current_id = -1;
		$ledgers->restriction_bankcash = 1;
		$ledgers->default_text = '(ALL)';
		$ledgers->build(0);
		$ledgers->toList($ledgers, -1);
		
		$this->data['ledger_options'] = $ledgers->ledgerList;

		/* Entrytypes */
		$entrytype_options = array();
		$entrytype_options[0] = '(ALL)';

		$rawentrytypes = $this->db->order_by('id', 'asc')->get('sma_accounts_entrytypes')->result_array();
		foreach ($rawentrytypes as $row => $rawentrytype) {
			$entrytype_options[$rawentrytype['id']] = ($rawentrytype['name']);
		}
		$this->data['entrytype_options'] = $entrytype_options;


		/* Tags */
		$tag_options = array();
		$tag_options[0] = '(ALL)';
		$rawtags = $this->db->order_by('title', 'asc')->get('sma_accounts_tags')->result_array();

		foreach ($rawtags as $row => $rawtag) {
			$tag_options[$rawtag['id']] = ($rawtag['title']);
		}
		$this->data['tag_options'] = $tag_options;

		
		if ($this->input->method() == 'post') {

			$ledger_ids = '';
			if (empty($this->input->post('ledger_ids'))) {
				$ledger_ids = '0';
			} else {
				if (in_array('0', $this->input->post('ledger_ids'))) {
					$ledger_ids = '0';
				} else {
					$ledger_ids = implode(',', $this->input->post('ledger_ids'));
				}
			}

			$entrytype_ids = '';
			if (empty($this->input->post('entrytype_ids'))) {
				$entrytype_ids = '0';
			} else {
				if (in_array('0', $this->input->post('entrytype_ids'))) {
					$entrytype_ids = '0';
				} else {
					$entrytype_ids = implode(',', $this->input->post('entrytype_ids'));
				}
			}

			$tag_ids = '';
			if (empty($this->input->post('tag_ids'))) {
				$tag_ids = '0';
			} else {
				if (in_array('0', $this->input->post('tag_ids'))) {
					$tag_ids = '0';
				} else {
					$tag_ids = implode(',', $this->input->post('tag_ids'));
				}
			}


			/* Setup search conditions */
			$conditions = array();

			if (!empty($this->input->post('ledger_ids'))) {
				if (!in_array('0', $this->input->post('ledger_ids'))) {
					$this->db->where_in('sma_accounts_entryitems.ledger_id', $this->input->post('ledger_ids'));
				}
			}

			if (!empty($this->input->post('entrytype_ids'))) {
				if (!in_array('0', $this->input->post('entrytype_ids'))) {
					$this->db->where_in('sma_accounts_entries.entrytype_id', $this->input->post('entrytype_ids'));
				}
			}

			if (!empty($this->input->post('entrynumber1'))) {
				if ($this->input->post('entrynumber_restriction') == 1) {
					/* Equal to */
					$conditions['sma_accounts_entries.number'] = $this->input->post('entrynumber1');
				} else if ($this->input->post('entrynumber_restriction') == 2) {
					/* Less than or equal to */
					$conditions['sma_accounts_entries.number <='] =  $this->input->post('entrynumber1');
				} else if ($this->input->post('entrynumber_restriction') == 3) {
					/* Greater than or equal to */
					$conditions['sma_accounts_entries.number >='] = $this->input->post('entrynumber1');
				} else if ($this->input->post('entrynumber_restriction') == 4) {
					/* In between */
					if (!empty($this->input->post('entrynumber2'))) {
						$conditions['sma_accounts_entries.number >='] = $this->input->post('entrynumber1');
						$conditions['sma_accounts_entries.number <='] = $this->input->post('entrynumber2');
					} else {
						$conditions['sma_accounts_entries.number >='] = $this->input->post('entrynumber1');
					}
				}
			}

			if ($this->input->post('amount_dc') == 'D') {
				/* Dr */
				$conditions['sma_accounts_entryitems.dc'] = 'D';
			} else if ($this->input->post('amount_dc') == 'C') {
				/* Cr */
				$conditions['sma_accounts_entryitems.dc'] = 'C';
			}

			if (!empty($this->input->post('amount1'))) {
				if ($this->input->post('amount_restriction') == 1) {
					/* Equal to */
					$conditions['sma_accounts_entryitems.amount'] = $this->input->post('amount1');
				} else if ($this->input->post('amount_restriction') == 2) {
					/* Less than or equal to */
					$conditions['sma_accounts_entryitems.amount <='] =  $this->input->post('amount1');
				} else if ($this->input->post('amount_restriction') == 3) {
					/* Greater than or equal to */
					$conditions['sma_accounts_entryitems.amount >='] = $this->input->post('amount1');
				} else if ($this->input->post('amount_restriction') == 4) {
					/* In between */
					if (!empty($this->input->post('amount2'))) {
						$conditions['sma_accounts_entryitems.amount >='] = $this->input->post('amount1');
						$conditions['sma_accounts_entryitems.amount <='] = $this->input->post('amount2');
					} else {
						$conditions['sma_accounts_entryitems.amount >='] = $this->input->post('amount1');
					}
				}
			}

			if (!empty($this->input->post('fromdate'))) {
				/* TODO : Validate date */
				$fromdate = $this->functionscore->dateToSql($this->input->post('fromdate'));
				$conditions['sma_accounts_entries.date >='] = $fromdate;
			}

			if (!empty($this->input->post('todate'))) {
				/* TODO : Validate date */
				$todate = $this->functionscore->dateToSql($this->input->post('todate'));
				$conditions['sma_accounts_entries.date <='] = $todate;
			}

			
			if (!empty($this->input->post('tag_ids'))) {
				if (!in_array('0', $this->input->post('tag_ids'))) {
					$this->db->where_in('sma_accounts_entries.tag_id', $this->input->post('tag_ids'));

				}
			}

			if (!empty($this->input->post('narration'))) {
				$conditions['sma_accounts_entryitems.narration LIKE'] = '%' . $this->input->post('narration') . '%';
			}

			/* Pass varaibles to view which are used in Helpers */
			$entries = $this->db->where($conditions)
			->select('sma_accounts_entries.id, sma_accounts_entries.tag_id, sma_accounts_entries.entrytype_id, sma_accounts_entries.number, sma_accounts_entries.date, sma_accounts_entries.dr_total, sma_accounts_entries.cr_total, sma_accounts_entryitems.narration, sma_accounts_entryitems.entry_id, sma_accounts_entryitems.ledger_id as ledger_ida, sma_accounts_entryitems.amount, sma_accounts_entryitems.dc, sma_accounts_entryitems.reconciliation_date')
			->order_by('sma_accounts_entries.date', 'asc')
			->join('sma_accounts_entryitems', 'sma_accounts_entries.id = sma_accounts_entryitems.entry_id', 'left')
			->get('sma_accounts_entries')->result_array();



			/* Setup pagination */
			$this->data['entries'] = $entries;
			$this->data['allTags'] = $this->db->get('sma_accounts_tags')->result_array();
			$this->data['showEntries'] = true;
		}
		// render page
		//$this->render('search');
		$bc  = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('search'), 'page' => lang('Search')], ['link' => '#', 'page' => lang('Search')]];
        $meta = ['page_title' => lang('Search Accounts'), 'bc' => $bc];
        $this->page_construct('accounts/search', $meta, $this->data);
	}

}
