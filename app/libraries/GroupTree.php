<?php

/**
 * Class to store the entire group tree
 */
class GroupTree
{
	var $id = 0;
	var $name = '';
	var $code = '';
	var $children_groups = array();
	var $counter = 0;
	var $current_id = -1;
	var $Group = null;

	public function __construct()
	{
		$this->_ci = &get_instance();
	}
	/**
	 * Initializer
	 */
	function GroupTree()
	{
		return;
	}

	/**
	 * Setup which group id to start from
	 */
	function build($id)
	{
		if ($this->current_id == $id) {
			return;
		}

		if ($id == 0) {
			$this->id = NULL;
			$this->name = "None";
		} else {
			$this->_ci->db->where('id', $id);
			$group = $this->_ci->db->get('sma_accounts_groups')->row_array();
			$this->id = $group['id'];
			$this->name = $group['name'];
			$this->code = $group['code'];
		}

		$this->add_sub_groups();
		// unset($this->_ci);

	}

	/**
	 * Find and add subgroups as objects
	 */
	function add_sub_groups()
	{
		$conditions = array('sma_accounts_groups.parent_id' => $this->id);

		/* If primary group sort by id else sort by name */
		if ($this->id == NULL) {
			$this->_ci->db->where($conditions);
			// $this->_ci->DB1->order_by('groups.id', "asc");
			$this->_ci->db->order_by('sma_accounts_groups.code', 'asc');
			$child_group_q = $this->_ci->db->get('sma_accounts_groups')->result_array();
		} else {
			$this->_ci->db->where($conditions);
			// $this->_ci->DB1->order_by('groups.name', "asc");
			$this->_ci->db->order_by('sma_accounts_groups.code', 'asc');
			$child_group_q = $this->_ci->db->get('sma_accounts_groups')->result_array();
		}

		$counter = 0;
		foreach ($child_group_q as $row) {
			/* Create new AccountList object */
			$this->children_groups[$counter] = new GroupTree();

			/* Initial setup */
			$this->children_groups[$counter]->Group = &$this->Group;
			$this->children_groups[$counter]->current_id = $this->current_id;

			$this->children_groups[$counter]->build($row['id']);

			$counter++;
		}
	}

	var $groupList = array();

	/* Convert group tree to a list */
	public function toList($tree, $c = 0)
	{
		$counter = $c;

		if ($tree->id != 0) {
			$this->groupList[$tree->id] = $this->space($counter) .
				($this->_ci->functionscore->toCodeWithName($tree->code, $tree->name));
		}

		/* Process child groups recursively */
		foreach ($tree->children_groups as $id => $data) {
			$counter++;
			$this->toList($data, $counter);
			$counter--;
		}
	}

	function space($count)
	{
		$str = '';
		for ($i = 1; $i <= $count; $i++) {
			$str .= '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
		}
		return $str;
	}


	/**
	 * This function return 3 main arrays used in Groups & Ledger Create + Update Page
	 */
	function accountGroupLedgerOptions()
	{

		$typeOneArr = ["" => " - ", "B/S" => "B/S", "P/L" => "P/L"];

		$typeTwoArr = [
			"" => " - ",
			"Assets" => "Assets",
			"Cost Of Sales" => "Cost Of Sales",
			"Equity" => "Equity",
			"Liabilities" => "Liabilities",
			"Operating Expenses" => "Operating Expenses",
			"Other Expenses" => "Other Expenses",
			"Other Income" => "Other Income",
			"Revenue" => "Revenue"
		];


		$this->_ci->db->order_by('sma_accounts_ledgers_cats.name', 'asc');
		$groupCategories = $this->_ci->db->get('sma_accounts_ledgers_cats')->result_array();
		$categoriesArr[] = " - ";
		foreach ($groupCategories as $cats) {
			$categoriesArr[$cats['name']] = $cats['name'];
		}

		return ['accountTypeOne' => $typeOneArr, 'accountTypeTwo' => $typeTwoArr, 'accountCategories' => $categoriesArr];
	}
}
