<?php
Class Entry_model extends CI_Model {
	/**
	 * Show the entry ledger details
	 */
	public function entryLedgers($id) {
		/* Load the Entryitem model */
		$this->load->admin_model('EntryItem_model');
		$Entryitem = new EntryItem_model();

		/* Load the Ledger model */
		$this->load->admin_model('Ledger_model');
		$Ledger = new Ledger_model();

		$this->db->where('sma_accounts_entryitems.entry_id', $id);
		$this->db->order_by('sma_accounts_entryitems.id', "desc");
		$rawentryitems = $this->db->get('sma_accounts_entryitems')->result_array();
		

		/* Get dr and cr ledger id and count */
		$dr_count = 0;
		$cr_count = 0;
		$dr_ledger_id = '';
		$cr_ledger_id = '';
		foreach ($rawentryitems as $row => $entryitem) {
			if ($entryitem['dc'] == 'D') {
				$dr_ledger_id = $entryitem['ledger_id'];
				$dr_count++;
			} else {
				$cr_ledger_id = $entryitem['ledger_id'];
				$cr_count++;
			}
		}

		/* Get ledger name */
		$dr_name = $Ledger->getName($dr_ledger_id);
		$cr_name = $Ledger->getName($cr_ledger_id);

		if (strlen($dr_name) > 15) {
			$dr_name = substr($dr_name, 0, 15) . '...';
		}
		if (strlen($cr_name) > 15) {
			$cr_name = substr($cr_name, 0, 15) . '...';
		}

		/* if more than one ledger on dr / cr then add [+] sign */
		if ($dr_count > 1) {
			$dr_name = $dr_name . ' [+]';
		}
		if ($cr_count > 1) {
			$cr_name = $cr_name . ' [+]';
		}

		if ($this->mSettings->drcr_toby == 'toby') {
			$ledgerstr = 'By ' . $dr_name . ' / ' . 'To ' . $cr_name;
		} else {
			$ledgerstr = 'Dr ' . $dr_name . ' / ' . 'Cr ' . $cr_name;
		}
		return $ledgerstr;
	}

	/**
	 * Calculate the next number for a entry based on entry type
	 */
		public function nextNumber($id)	{

			$this->db->where('entrytype_id', $id);
			$max = $this->db->select('MAX(number) AS max')->get('sma_accounts_entries')->row_array();
			if (empty($max['max'])) {
				$maxNumber = 0;
			} else {
				$maxNumber = $max['max'];
			}
			return (int)$maxNumber + 1;
		}

}