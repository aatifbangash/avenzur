
<?php
	// Generate a random id to use in below form array
	$i = time() + rand  (0, time()) + rand  (0, time()) + rand  (0, time());

	echo '<tr class="ajax-add">';

	if ($this->mSettings->drcr_toby == 'toby') {
		echo '<td>' . '<div class="form-group-entryitem required"><select id="Entryitem' . $i . 'Dc" class="dc-dropdown form-control" name="Entryitem[' . $i . '][dc]"><option selected="selected" value="D">'.lang('entries_views_addrow_label_dc_toby_D').'</option><option value="C">'.lang('entries_views_addrow_label_dc_toby_C').'</option></select></div>' . '</td>';
	} else {
		echo '<td>' . '<div class="form-group-entryitem required"><select id="Entryitem' . $i . 'Dc" class="dc-dropdown form-control" name="Entryitem[' . $i . '][dc]"><option selected="selected" value="D">'.lang('entries_views_addrow_label_dc_drcr_D').'</option><option value="C">'.lang('entries_views_addrow_label_dc_drcr_C').'</option></select></div>' . '</td>';
	}

	echo '<td>' . '<div class="form-group-entryitem required"><select id="Entryitem' . $i . 'LedgerId" class="ledger-dropdown form-control" name="Entryitem[' . $i . '][ledger_id]">';
	// foreach ($ledger_options as $i => $data) {
	// 	if ($i >= 0) {
	// 		echo '<option value="' . $i . '">' . $data . '</option>';
	// 	} else {
	// 		echo '<option value="' . $i . '" disabled="disabled">' . $data . '</option>';
	// 	}
	// }
	echo '</select></div>' . '</td>';

	echo '<td>' . '<div class="form-group-entryitem"><input type="text" id="Entryitem' . $i . 'DrAmount" class="dr-item form-control" name="Entryitem[' . $i . '][dr_amount]" disabled=""></div>' . '</td>';

	echo '<td>' . '<div class="form-group-entryitem"><input type="text" id="Entryitem' . $i . 'CrAmount" class="cr-item form-control" name="Entryitem[' . $i . '][cr_amount]" disabled=""></div>' . '</td>';
	$data = array(
		'type'  => "text",
		'name'  => 'Entryitem[' . $i . '][narration]',
		'class' => 'form-control',
		'id' => 'Entryitem' . $i . 'Narration'
	);
	echo "<td><div class='form-group-entryitem'>";
	echo form_input($data);
	echo "</div></td>";
	echo '<td class="ledger-balance"><div></div></td>';
	/**add  customer here */
                                
	echo '<td><div class="form-group-entryitem">';
	$cus[] = "Select Customer";
	foreach ($items['customers'] as $customer) {
		 
		$cus[$customer->id] = $customer->company. ' ('. $customer->name.')';
	}
	echo form_dropdown('Entryitem[' . $i . '][customer_id]', $cus, ($_POST['Entryitem['.$i.'][customer_id]'  ] ?? $_POST['Entryitem['.$i.'][customer_id]']), 'class="form-control input-tip select" data-placeholder="' . lang('select') . ' ' . lang('customer') . '" ');
	echo '</div></td>';
	/**Add Supplier Here */
		echo '<td><div class="form-group-entryitem">';
		$suData = [];
			$suData[] = "Select Supplier";
		foreach($items['suppliers'] as $supplier){
			$suData[$supplier->id] = $supplier->name;
		}
		echo form_dropdown('Entryitem[' . $i . '][supplier_id]', $suData, ($_POST['Entryitem['.$i.'][supplier_id]'] ?? $_POST['Entryitem['.$i.'][supplier_id]']), 'class="form-control input-tip select" data-placeholder="' . lang('select') . ' ' . lang('Supplier') . '" "');
		echo '</div></td>';
			/**Add Dept Here */
		echo '<td><div class="form-group-entryitem">';
		$depthData = [];
		$depthData[] = "Select department";
		foreach($items['departments'] as $depart){
			$depthData[$depart->id] = $depart->name;
		}
		echo form_dropdown('Entryitem[' . $i . '][department_id]', $depthData, ($_POST['Entryitem['.$i.'][department_id]'] ?? $_POST['Entryitem['.$i.'][department_id]']) , 'class="form-control input-tip select" data-placeholder="' . lang('select') . ' ' . lang('Departments') . '" "');
		echo '</div></td>';
		/**
		 * Add Employees here
		 */
		echo '<td><div class="form-group-entryitem">';
		$empData = [];
		$empData[] = "Select employee";
		foreach($items['employees'] as $emp){
			$empData[$emp->id] = $emp->name;
		}
		echo form_dropdown('Entryitem[' . $i . '][employee_id]', $empData, ($_POST['Entryitem['.$i.'][employee_id]'] ?? $_POST['Entryitem['.$i.'][employee_id]']), 'class="form-control input-tip select" data-placeholder="' . lang('select') . ' ' . lang('Employee') . '" "');
	echo '</div></td>';
	echo '<td>';
	 
	echo '<span class="deleterow" escape="false"><i class="fa fa-trash"></i></span>';
	echo '</td>';
	echo '</tr>';
?>