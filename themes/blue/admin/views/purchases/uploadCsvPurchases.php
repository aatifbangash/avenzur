<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true" id="close_button"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?php echo lang('add_purchase_by_csv'); ?></h4>
        </div>

        <?php $attrib = ['role' => 'form'];
            echo admin_form_open_multipart('purchases/mapPurchases', $attrib); 
            // echo admin_form_open_multipart('purchases/purchase_by_csv', $attrib); 
        ?>

        <div class="modal-body">
            <p><?= lang('enter_info'); ?></p>

            <div class="col-md-12">
                <div class="form-group">
                    <?= lang('date *', 'podate'); ?>
                    <?php echo form_input('mdate', ($_POST['mdate'] ?? date($dateFormats['php_ldate'], now())), 'class="form-control input-tip datetime" id="podate" required="required"'); ?>
                </div>
            </div>

            <div class="col-md-12">
                <div class="form-group">
                    <?php echo lang('Warehouse *', 'powarehouse2'); ?>
                    <div class="controls">
                        <?php
                            $wh[''] = '';
                            foreach ($warehouses as $warehouse) {
                                $wh[$warehouse->id] = $warehouse->name;
                            }
                            
                            echo form_dropdown('mwarehouse', $wh, ($_POST['mwarehouse'] ?? $Settings->default_warehouse), 'id="powarehouse2" class="form-control input-tip select" data-placeholder="' . $this->lang->line('select') . ' ' . $this->lang->line('warehouse') . '" required="required" style="width:100%;" ');
                        ?>
                    </div>
                </div>
            </div>

            <div class="col-md-12">
                <div class="form-group">
                    <?= lang('Supplier Reference Number', 'poref'); ?>
                    <?php echo form_input('mreference_no', ($_POST['mreference_no'] ?? ''), 'class="form-control input-tip" id="pomref"'); ?>
                </div>
            </div>

            <div class="col-md-12">
            <div class="form-group">
                <?= lang('Parent Supplier', 'pomsupplier'); ?>
                <?php if ($Owner || $Admin || $GP['suppliers-add'] || $GP['suppliers-index']) {
                    ?><div class="input-group"><?php
                } ?>
                    <input type="hidden" name="msupplier" value="" id="pomsupplier"
                            class="form-control" style="width:100%;"
                            placeholder="<?= lang('select') . ' ' . lang('supplier') ?>">
                    <input type="hidden" name="msupplier_id" value="" id="supplier_id"
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

            <div class="col-md-12">
                <div class="form-group">
                    <?= lang('Child Supplier', 'pomsupplier'); ?>
                    <?php
                    $childSupArr[''] = '';
                    
                    echo form_dropdown('mchildsupplier', $childSupArr, $_POST['mchildsupplier'], 'id="mchildsupplier" class="form-control input-tip select" data-placeholder="' . lang('select') . ' ' . lang('child supplier') . '" required="required" style="width:100%;" '); ?>
                </div>
            </div>
              
            <div class="col-md-8">
                <div class="form-group">
                    <?= lang('csv_file', 'csv_file') ?>
                    <input id="csv_file" type="file" data-browse-label="<?= lang('browse'); ?>" name="userfile" required="required"
                            data-show-upload="false" data-show-preview="false" class="form-control file">
                </div>
            </div>

            <div class="clearfix"></div>
            <div class="modal-footer">
            <!-- <div class="col-md-12"> -->
                            <?php
                            $data = array(
                                'name' => 'add_pruchase',
                                'onclick'=>"return confirm('Are you sure to proceed?')"
                            );
                            ?>
                            <div
                                class="from-group"><?php echo form_submit($data, $this->lang->line('submit'), 'id="add_pruchase" class="btn btn-primary" style="padding: 6px 15px; margin:15px 0;"'); ?>
                            </div>
                        <!-- </div> -->
                <!-- <?php echo form_submit('submit_map_notification', lang('Submit'), 'class="btn btn-primary"'); ?> -->
            <!-- <div class="btn  btn-primary" id="submitAcceptDispatch" >Upload</div> -->
            </div>
        </div>     
    </div>
    <?php echo form_close(); ?>
</div>

<script>
    var $supplier = $("#pomsupplier");
    var $mchildsupplierselectbox = $("#mchildsupplier");

    $supplier.change(function (e) {
		localStorage.setItem("pomsupplier", $(this).val());
		localStorage.setItem("mchildsupplier", null);
		$("#supplier_id").val($(this).val());

		populateChildSuppliers($(this).val());
	});

    $mchildsupplierselectbox.change(function (e) {
		localStorage.setItem("mchildsupplier", $(this).val());
		$("#child_supplier_id").val($(this).val());
	});

    function populateChildSuppliers(pid) {
		$.ajax({
			url: site.base_url + "suppliers/getChildById",
			data: { term: "", limit: 10, pid: pid },
			dataType: "json",
			success: function (data) {
				$mchildsupplierselectbox.empty();
				$.each(data.results, function (index, value) {
					$mchildsupplierselectbox.append(new Option(value.text, value.id));
				});

				if (localStorage.getItem("mchildsupplier")) {
					$mchildsupplierselectbox
						.val(localStorage.getItem("mchildsupplier"))
						.trigger("change");
				}
			},
		});
	}

    if ((pomsupplier = localStorage.getItem("pomsupplier"))) {
		$supplier.val(pomsupplier).select2({
			minimumInputLength: 1,
			data: [],
			initSelection: function (element, callback) {
				$.ajax({
					type: "get",
					async: false,
					url: site.base_url + "suppliers/getSupplier/" + $(element).val(),
					dataType: "json",
					success: function (data) {
						callback(data[0]);
					},
				});
			},
			ajax: {
				url: site.base_url + "suppliers/suggestions",
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

		populateChildSuppliers(pomsupplier);
	} else {
		nsSupplier();
		//nsChildSupplierByParentId($(this).val());
	}

    // hellper function for supplier if no localStorage value
    function nsChildSupplierByParentId(pid) {
        $("#mchildsupplier").select2({
            ajax: {
                url: site.base_url + "suppliers/getChildById",
                dataType: "json",
                quietMillis: 15,
                data: function (term, page) {
                    return {
                        pid: pid,
                        search: term,
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

    function nsChildSupplier() {
        $("#mchildsupplier").select2({
            minimumInputLength: 1,
            ajax: {
                url: site.base_url + "suppliers/childsuggestions",
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
        $("#pomsupplier").select2({
            minimumInputLength: 1,
            ajax: {
                url: site.base_url + "suppliers/suggestions",
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