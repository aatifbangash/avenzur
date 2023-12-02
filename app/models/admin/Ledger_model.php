<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Ledger_model extends CI_Model
{

	/**
 * Calculate opening balance of specified ledger account for the given
 * date range
 *
 * @param1 int ledger id
 * @param2 date start date
 * @return array D/C, Amount
 */
	function openingBalance($id, $start_date = null) {
		
		/* Opening balance */
		$this->db->where('id', $id);
		$op = $this->db->get('sma_accounts_ledgers')->row_array();
		
		if (!$op) {
			$this->session->set_flashdata('error', lang('ledger_not_found_failed_op_balance'));
			
		}

		$op_total = 0;
		if (empty($op['op_balance'])) {
			$op_total = 0;
		} else {
			$op_total = $op['op_balance'];
		}
		$op_total_dc = $op['op_balance_dc'];

		/* If start date is not specified then return here */
		if (is_null($start_date)) {
			return array('dc' => $op_total_dc, 'amount' => $op_total);
		}

		/* Debit total */
		$dr_conditions = array(
			'sma_accounts_entryitems.ledger_id' => $id,
			'sma_accounts_entryitems.dc' => 'D'
		);
		if (!is_null($start_date)) {
			$dr_conditions['sma_accounts_entries.date <'] = $start_date;
		}
		$this->db->where($dr_conditions)->select('SUM('.$this->db->dbprefix('accounts_entryitems').'.amount) as total');
		$total = $this->db->from('sma_accounts_entryitems')->join('sma_accounts_entries', 'sma_accounts_entries.id = sma_accounts_entryitems.entry_id', 'left')->get()->row_array();
		

		if (empty($total['total'])) {
			$dr_total = 0;
		} else {
			$dr_total = $total['total'];
		}

		/* Credit total */
		$cr_conditions = array(
			'sma_accounts_entryitems.ledger_id' => $id,
			'sma_accounts_entryitems.dc' => 'C'
		);
		if (!is_null($start_date)) {
			$cr_conditions['sma_accounts_entries.date <'] = $start_date;
		}

		$total = $this->db->select('SUM('.$this->db->dbprefix('accounts_entryitems').'.amount) as total')
		->where($cr_conditions)
		->from('sma_accounts_entryitems')
		->join('sma_accounts_entries', 'sma_accounts_entries.id = sma_accounts_entryitems.entry_id', 'left')->get()->row_array();
		
		if (empty($total['total'])) {
			$cr_total = 0;
		} else {
			$cr_total = $total['total'];
		}

		/* Add opening balance */
		if ($op_total_dc == 'D') {
			$dr_total_final = $this->functionscore->calculate($op_total, $dr_total, '+');
			$cr_total_final = $cr_total;
		} else {
			$dr_total_final = $dr_total;
			$cr_total_final = $this->functionscore->calculate($op_total, $cr_total, '+');
		}

		/* Calculate final opening balance */
		if ($this->functionscore->calculate($dr_total_final, $cr_total_final, '>')) {
			$op_total = $this->functionscore->calculate($dr_total_final, $cr_total_final, '-');
			$op_total_dc = 'D';
		} else if ($this->functionscore->calculate($dr_total_final, $cr_total_final, '==')) {
			$op_total = 0;
			$op_total_dc = $op_total_dc;
		} else {
			$op_total = $this->functionscore->calculate($cr_total_final, $dr_total_final, '-');
			$op_total_dc = 'C';
		}

		return array('dc' => $op_total_dc, 'amount' => $op_total);
	}

	/* Return ledger name from id */
	public function getName($id) {
		$ledger = $this->db->where('id', $id);
		$ledger = $this->db->get('accounts_ledgers')->row_array();
		if ($ledger) {
			return $this->functionscore->toCodeWithName($ledger['code'],$ledger['name']);
		} else {
			return('ERROR');
		}
	}
  
  /* Calculate difference in opening balance */
	public function getOpeningDiff() {
		$total_op = 0;
		$ledgers =  $this->db->get('accounts_ledgers')->result_array();
		foreach ($ledgers as $row => $ledger)
		{
			if ($ledger['op_balance_dc'] == 'D')
			{
				$total_op = $this->functionscore->calculate($total_op, $ledger['op_balance'], '+');
			} else {
				$total_op = $this->functionscore->calculate($total_op, $ledger['op_balance'], '-');
			}
		}

		/* Dr is more ==> $total_op >= 0 ==> balancing figure is Cr */
		if ($this->functionscore->calculate($total_op, 0, '>=')) {
			return array('opdiff_balance_dc' => 'C', 'opdiff_balance' => $total_op);
		} else {
			return array('opdiff_balance_dc' => 'D', 'opdiff_balance' => $this->functionscore->calculate($total_op, 0, 'n'));
		}
	}

	function closingBalance($id, $start_date = null, $end_date = null) {

		if (empty($id)) {
			show_404();
		}
		
		$Entry = $this->load->admin_model('Entry_model');
		$Entryitem = $this->load->admin_model('EntryItem_model');

		/* Opening balance */
		$op = $this->db->where('id', $id)->get('sma_accounts_ledgers')->row_array();
		
		if (!$op) {
			return lang('ledger_not_found');
		}

		$op_total = 0;
		$op_total_dc = $op['op_balance_dc'];
		if (is_null($start_date)) {
			if (empty($op['op_balance'])) {
				$op_total = 0;
			} else {
				$op_total = $op['op_balance'];
			}
		}

		$dr_total = 0;
		$cr_total = 0;
		$dr_total_dc = 0;
		$cr_total_dc = 0;

		/* Debit total */
		$dr_conditions = array(
			'sma_accounts_entryitems.ledger_id' => $id,
			'sma_accounts_entryitems.dc' => 'D'
		);
		if (!is_null($start_date)) {
			$dr_conditions['sma_accounts_entries.date >='] = $start_date;
		}
		if (!is_null($end_date)) {
			$dr_conditions['sma_accounts_entries.date <='] = $end_date;
		}

		$this->db->where($dr_conditions);
		// $this->db->where('entryitems.ledger_id', $id);
		// $this->db->where('entryitems.dc', 'D');
		$this->db->select('SUM(' . $this->db->dbprefix('accounts_entryitems') . '.amount) as total');
		$this->db->join('sma_accounts_entries', 'sma_accounts_entries.id = sma_accounts_entryitems.entry_id', 'left');
		$total = $this->db->get('sma_accounts_entryitems')->row_array();
		
		if (empty($total['total'])) {
			$dr_total = 0;
		} else {
			$dr_total = $total['total'];
		}

		/* Credit total */
		$cr_conditions = array(
			'sma_accounts_entryitems.ledger_id' => $id,
			'sma_accounts_entryitems.dc' => 'C'
		);

		if (!is_null($start_date)) {
			$cr_conditions['sma_accounts_entries.date >='] = $start_date;
		}
		if (!is_null($end_date)) {
			$cr_conditions['sma_accounts_entries.date <='] = $end_date;
		}
		$this->db->where($cr_conditions);
		// $this->db->where('entryitems.ledger_id', $id);
		// $this->db->where('entryitems.dc', 'C');
		$this->db->select('SUM('.$this->db->dbprefix('accounts_entryitems').'.amount) as total');
		$this->db->join('sma_accounts_entries', 'sma_accounts_entries.id = sma_accounts_entryitems.entry_id', 'left');
		$total = $this->db->get('sma_accounts_entryitems')->row_array();
		
		if (empty($total['total'])) {
			$cr_total = 0;
		} else {
			$cr_total = $total['total'];
		}
		/* Add opening balance */
		if ($op_total_dc == 'D') {
			$dr_total_dc = $this->functionscore->calculate($op_total, $dr_total, '+');
			$cr_total_dc = $cr_total;
		} else {
			$dr_total_dc = $dr_total;
			$cr_total_dc = $this->functionscore->calculate($op_total, $cr_total, '+');
		}

		/* $this->calculate and update closing balance */
		$cl = 0;
		$cl_dc = '';
		if ($this->functionscore->calculate($dr_total_dc, $cr_total_dc, '>')) {
			$cl = $this->functionscore->calculate($dr_total_dc, $cr_total_dc, '-');
			$cl_dc = 'D';
		} else if ($this->functionscore->calculate($cr_total_dc, $dr_total_dc, '==')) {
			$cl = 0;
			$cl_dc = $op_total_dc;
		} else {
			$cl = $this->functionscore->calculate($cr_total_dc, $dr_total_dc, '-');
			$cl_dc = 'C';
		}

		return array('dc' => $cl_dc, 'amount' => $cl, 'dr_total' => $dr_total, 'cr_total' => $cr_total);
	}
	
	
}