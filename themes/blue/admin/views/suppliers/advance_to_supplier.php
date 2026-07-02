<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<script>
    $(document).ready(function () {
        $('#addRowBtn').click(function() {
            var newRow = '<tr>' +
                '<td><input type="text" placeholder="Enter Description" class="form-control" name="description[]" /></td>' +
                '<td><input type="text" placeholder="Enter Amount" class="form-control" name="payment_amount[]" /></td>' +
                '</tr>';
            $('#poTable tbody').append(newRow);
        });
    });
</script>

<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-info-circle"></i><?= lang('supplier_advance'); ?></h2>

        <div class="box-icon">
            <ul class="btn-tasks">
                
            </ul>
        </div>
    </div>
    <div class="box-content">
        <div class="row">
            <?php
            $attrib = ['data-toggle' => 'validator', 'role' => 'form'];
            echo admin_form_open_multipart('suppliers/advance_to_supplier', $attrib)
            ?>
            <div class="col-lg-12">
                
                <div class="row">
                    <div class="col-lg-12">
                        <?php if ($Owner || $Admin) {
                            ?>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <?= lang('date', 'podate'); ?>
                                        <?php echo form_input('date', ($memo_data->date ?? ''), 'class="form-control input-tip date" id="podate"'); ?>
                                    </div>
                                </div>
                            <?php
                        } ?>

                        <div class="col-md-4">
                            <div class="form-group">
                                <?= lang('reference_no', 'poref'); ?>
                                <?php echo form_input('reference_no', ($memo_data->reference_no ?? $memo_data->reference_no), 'class="form-control input-tip" id="poref"'); ?>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <?= lang('Payment Amount', 'poref'); ?>
                                <?php echo form_input('payment_total', ($memo_data->payment_amount ?? $memo_data->payment_total), 'class="form-control input-tip" id="payment_amount"'); ?>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <?= lang('Parent Supplier', 'passupplier'); ?>
                                <?php if ($Owner || $Admin || $GP['suppliers-add'] || $GP['suppliers-index']) {
                                    ?><div class="input-group"><?php
                                } ?>
                                    <input type="hidden" name="supplier" value="" id="passupplier"
                                            class="form-control" style="width:100%;"
                                            placeholder="<?= lang('select') . ' ' . lang('supplier') ?>">
                                    <input type="hidden" name="supplier_id" value="" id="supplier_id"
                                            class="form-control">
                                    <?php if ($Owner || $Admin || $GP['suppliers-index']) {
                                    ?>
                                        <div class="input-group-addon no-print" style="padding: 2px 5px; border-left: 0;">
                                            <a href="#" id="view-supplier" class="external" data-toggle="modal" data-target="#myModal">
                                                <i class="fa fa-2x fa-user" id="addIcon"></i>
                                            </a>
                                        </div>
                                    <?php
                                    } ?>
                                    <?php if ($Owner || $Admin || $GP['suppliers-add']) {
                                    ?>
                                    <div class="input-group-addon no-print" style="padding: 2px 5px;">
                                        <a href="<?= admin_url('suppliers/add'); ?>" id="add-supplier" class="external" data-toggle="modal" data-target="#myModal">
                                            <i class="fa fa-2x fa-plus-circle" id="addIcon"></i>
                                        </a>
                                    </div>
                                    <?php
                                    } ?>
                                    <?php if ($Owner || $Admin || $GP['suppliers-add'] || $GP['suppliers-index']) {
                                    ?></div><?php
                                    } ?>
                            </div>
                        </div>

                        <!-- Child Suppliers -->

                        <div class="col-md-4">
                            <div class="form-group">
                                <?= lang('Child Supplier', 'passupplier'); ?>
                                <?php
                                $childSupArr[''] = '';
                                
                                echo form_dropdown('childsupplier', $childSupArr, $_POST['childsupplier'], 'id="pachildsupplier" class="form-control input-tip select" data-placeholder="' . lang('select') . ' ' . lang('child supplier') . '" required="required" style="width:100%;" '); ?>
                            </div>
                        </div>
                            
                        <div class="col-md-4">
                            <div class="form-group">
                            <?= lang('Opposite Account', 'posupplier'); ?>
                            <?php 

                                echo form_dropdown('ledger_account', $LO, ($memo_data->ledger_account ?? $memo_data->ledger_account), 'id="ledger_account" class="ledger-dropdown form-control" required="required"',$DIS);  

                            ?>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                            <?= lang('Bank Charges', 'posupplier'); ?>
                            <?php 

                                echo form_dropdown('bank_charges_account', $LO, ($memo_data->bank_charges_account ?? $memo_data->bank_charges_account), 'id="bank_charges_account" class="ledger-dropdown form-control" required="required"',$DIS);  

                            ?>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <?= lang('Bank Charges Amount', 'poref'); ?>
                                <?php echo form_input('bank_charges', ($memo_data->bank_charges ?? $memo_data->bank_charges), 'class="form-control input-tip" id="bank_charges"'); ?>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="from-group">
                                <button type="submit" style="margin-top: 28px;" class="btn btn-primary" id="add_payment"><?= lang('Add Payments') ?></button>
                                <button type="button" style="margin-top: 28px;" class="btn btn-danger" id="reset" onclick="resetValues();"><?= lang('reset') ?></button>
                            </div>
                        </div>
                    </div>



                    <div class="controls table-controls" style="font-size: 12px !important;">
                        <table id="poTable"
                                class="table items table-striped table-bordered table-condensed table-hover sortable_table">
                            <thead>
                            <tr>
                                <th><?php echo $this->lang->line('Description'); ?></th>
                                <th><?php echo $this->lang->line('Payment Amount') ?></th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php 
                                    if(isset($memo_entries_data) && !empty($memo_entries_data)){
                                        foreach($memo_entries_data as $memo){
                                        ?>
                                            <tr>
                                                <td><input type="text" placeholder="Enter Description" class="form-control" value="<?= $memo->description; ?>" name="description[]" /></td>
                                                <td><input type="text" placeholder="Enter Amount" class="form-control" value="<?= $memo->payment_amount; ?>" name="payment_amount[]" /></td>
                                            </tr>
                                        <?php
                                        }
                                    }else{
                                        ?>
                                        <tr>
                                            <td><input type="text" placeholder="Enter Description" class="form-control" name="description[]" /></td>
                                            <td><input type="text" placeholder="Enter Amount" class="form-control" name="payment_amount[]" /></td>
                                        </tr>
                                        <?php
                                    }
                                ?>
                            </tbody>
                            <tfoot></tfoot>
                        </table>
                        <button id="addRowBtn" class="btn btn-primary mt-2">+</button>
                        <?php 
                            if(isset($memo_entries_data) && !empty($memo_entries_data)){
                                ?>
                                    <input type="hidden" name="memo_id" value="<?= $memo_data->id; ?>" />
                                    <input type="hidden" name="request_type" value="update" />
                                <?php
                            }else{
                                ?>
                                    <input type="hidden" name="request_type" value="add" />
                                <?php
                            }
                        ?>
                    </div>
                
            </div>

            <?php echo form_close(); ?>

        </div>
    </div>
</div>
<script type="text/javascript" src="<?= $assets ?>js/select2.min.js"></script>
<script type="text/javascript">
    function resetValues(){
        if (localStorage.getItem('bank_charges')) {
            localStorage.removeItem('bank_charges');
            $('#bank_charges').val('');
        }

        if (localStorage.getItem('bank_charges_account')) {
            localStorage.removeItem('bank_charges_account');
            $('#bank_charges_account').val('');
        }

        if (localStorage.getItem('payment_amount')) {
            localStorage.removeItem('payment_amount');
            $('#payment_amount').val('');
        }

        if (localStorage.getItem('ledger_account')) {
            localStorage.removeItem('ledger_account');
            $('#ledger_account').val('');
        }

        if (localStorage.getItem('poref')) {
            localStorage.removeItem('poref');
            $('#poref').val('');
        }

        if (localStorage.getItem('passupplier')) {
            localStorage.removeItem('passupplier');
            $('#passupplier').val('');
        }

        if (localStorage.getItem('pachildsupplier')) {
            localStorage.removeItem('pachildsupplier');
            $('#pachildsupplier').val('');
        }

        

        window.location.reload();
    }


    const hostname = "<?= base_url(); ?>";

    var $supplier = $("#passupplier");
    var $childsupplierselectbox = $("#pachildsupplier");

    $supplier.change(function (e) {
		localStorage.setItem("passupplier", $(this).val());
		localStorage.setItem("pachildsupplier", null);
		$("#supplier_id").val($(this).val());
		populateChildSuppliers($(this).val());
	});

	$childsupplierselectbox.change(function (e) {
		localStorage.setItem("pachildsupplier", $(this).val());
		$("#child_supplier_id").val($(this).val());
	});

    function populateChildSuppliers(pid) {
		$.ajax({
			url: hostname + "/admin/suppliers/getChildById",
			data: { term: "", limit: 10, pid: pid },
			dataType: "json",
			success: function (data) {
				$childsupplierselectbox.empty();
				$.each(data.results, function (index, value) {
					$childsupplierselectbox.append(new Option(value.text, value.id));
				});

				if (localStorage.getItem("pachildsupplier")) {
					$childsupplierselectbox
						.val(localStorage.getItem("pachildsupplier"))
						.trigger("change");
				}
			},
		});
	}

    if ((passupplier = localStorage.getItem("passupplier"))) {
		$supplier.val(passupplier).select2({
			minimumInputLength: 1,
			data: [],
			initSelection: function (element, callback) {
				$.ajax({
					type: "get",
					async: false,
					url: hostname + "/admin/suppliers/getSupplier/" + $(element).val(),
					dataType: "json",
					success: function (data) {
						callback(data[0]);
					},
				});
			},
			ajax: {
				url: hostname + "/admin/suppliers/suggestions",
				dataType: "json",
				quietMillis: 15,
				data: function (term, page) {
					return {
						term: term,
						limit: 10,
					};
				},
				results: function (data, page) {
					if (data.results != null) {
						return { results: data.results };
					} else {
						return { results: [{ id: "", text: "No Match Found" }] };
					}
				},
			},
		});

		populateChildSuppliers(passupplier);
	} else {
		nsSupplier();
	}

    function nsChildSupplier() {
        $("#pachildsupplier").select2({
            minimumInputLength: 1,
            ajax: {
                url: hostname + "admin/suppliers/childsuggestions",
                dataType: "json",
                quietMillis: 15,
                data: function (term, page) {
                    return {
                        term: term,
                        limit: 10,
                    };
                },
                results: function (data, page) {
                    if (data.results != null) {
                        return { results: data.results };
                    } else {
                        return { results: [{ id: "", text: "No Match Found" }] };
                    }
                },
            },
        });
    }

    function nsSupplier() {
        $("#passupplier").select2({
            minimumInputLength: 1,
            ajax: {
                url: hostname + "admin/suppliers/suggestions",
                dataType: "json",
                quietMillis: 15,
                data: function (term, page) {
                    return {
                        term: term,
                        limit: 10,
                    };
                },
                results: function (data, page) {
                    if (data.results != null) {
                        return { results: data.results };
                    } else {
                        return { results: [{ id: "", text: "No Match Found" }] };
                    }
                },
            },
        });
    }
</script>

