<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<style>
    table#poTable td input.form-control {
        font-size: 10px !important;
        padding: 5px 2px !important;
    }

    .table td {
        height: 70px !important;
    }
</style>
<script type="text/javascript">
    localStorage.removeItem('poitems');
    
    <?php 
    if (!empty($purchase_requesition_items)) : ?>


        try {
            let poitems = <?= $purchase_requesition_items; ?>;
            if (poitems && Object.keys(poitems).length > 0) {
                localStorage.setItem('poitems', JSON.stringify(poitems));
                console.log("PO items loaded from PR" + JSON.stringify(poitems));
            } else {
                console.log("No PO items found");
            }
        } catch (e) {
            console.log("Invalid PR JSON:", e);
        }

    <?php endif; ?>


    var count = 1,
        an = 1,
        po_edit = false,
        product_variant = 0,
        DT = <?= $Settings->default_tax_rate ?>,
        DC = '<?= $default_currency->code ?>',
        shipping = 0,
        product_tax = 0,
        invoice_tax = 0,
        total_discount = 0,
        total = 0,
        tax_rates = <?php echo json_encode($tax_rates); ?>,
        poitems = {},
        audio_success = new Audio('<?= $assets ?>sounds/sound2.mp3'),
        audio_error = new Audio('<?= $assets ?>sounds/sound3.mp3');
    $(document).ready(function() {
        <?php if ($this->input->get('supplier')) {
        ?>
            if (!localStorage.getItem('poitems')) {
                localStorage.setItem('posupplier', <?= $this->input->get('supplier'); ?>);
            }
        <?php
        } ?>
        if (!localStorage.getItem('potax2')) {
            localStorage.setItem('potax2', <?= $Settings->default_tax_rate2; ?>);
            setTimeout(function() {
                $('#extras').iCheck('check');
            }, 1000);
        }
        ItemnTotals();
        $("#add_item").autocomplete({
            // source: '<?= admin_url('purchases/suggestions'); ?>',
            source: function(request, response) {
                $.ajax({
                    type: 'get',
                    url: '<?= admin_url('purchases/suggestions'); ?>',
                    dataType: "json",
                    data: {
                        term: request.term,
                        supplier_id: $("#posupplier").val()
                    },
                    success: function(data) {
                        $(this).removeClass('ui-autocomplete-loading');
                        response(data);
                    }
                });
            },
            minLength: 1,
            autoFocus: false,
            delay: 250,
            response: function(event, ui) {
                if ($(this).val().length >= 16 && ui.content[0].id == 0) {
                    //audio_error.pla y();
                    // bootbox.alert('<?= lang('no_match_found') ?>', function () {
                    //     $('#add_item').focus();
                    // });
                    $(this).removeClass('ui-autocomplete-loading');
                    $(this).val('');
                } else if (ui.content.length == 1 && ui.content[0].id != 0) {
                    ui.item = ui.content[0];
                    $(this).data('ui-autocomplete')._trigger('select', 'autocompleteselect', ui);
                    $(this).autocomplete('close');
                    $(this).removeClass('ui-autocomplete-loading');
                } else if (ui.content.length == 1 && ui.content[0].id == 0) {
                    //audio_error.play();
                    // bootbox.alert('<?= lang('no_match_found') ?>', function () {
                    //     $('#add_item').focus();
                    // });
                    $(this).removeClass('ui-autocomplete-loading');
                    $(this).val('');
                }
            },
            select: function(event, ui) {
                event.preventDefault();
                if (ui.item.id !== 0) {
                    var row = add_purchase_item(ui.item);
                    if (row)
                        $(this).val('');
                } else {
                    //audio_error.play();
                    //bootbox.alert('<?= lang('no_match_found') ?>');
                }
            }
        });

        $(document).on('click', '#addItemManually', function(e) {
            if (!$('#mcode').val()) {
                $('#mError').text('<?= lang('product_code_is_required') ?>');
                $('#mError-con').show();
                return false;
            }
            if (!$('#mname').val()) {
                $('#mError').text('<?= lang('product_name_is_required') ?>');
                $('#mError-con').show();
                return false;
            }
            if (!$('#mcategory').val()) {
                $('#mError').text('<?= lang('product_category_is_required') ?>');
                $('#mError-con').show();
                return false;
            }
            if (!$('#munit').val()) {
                $('#mError').text('<?= lang('product_unit_is_required') ?>');
                $('#mError-con').show();
                return false;
            }
            if (!$('#mcost').val()) {
                $('#mError').text('<?= lang('product_cost_is_required') ?>');
                $('#mError-con').show();
                return false;
            }
            if (!$('#mprice').val()) {
                $('#mError').text('<?= lang('product_price_is_required') ?>');
                $('#mError-con').show();
                return false;
            }

            var msg, row = null,
                product = {
                    type: 'standard',
                    code: $('#mcode').val(),
                    name: $('#mname').val(),
                    tax_rate: $('#mtax').val(),
                    tax_method: $('#mtax_method').val(),
                    category_id: $('#mcategory').val(),
                    unit: $('#munit').val(),
                    cost: $('#mcost').val(),
                    price: $('#mprice').val()
                };

            $.ajax({
                type: "get",
                async: false,
                url: site.base_url + "products/addByAjax",
                data: {
                    token: "<?= $csrf; ?>",
                    product: product
                },
                dataType: "json",
                success: function(data) {
                    if (data.msg == 'success') {
                        row = add_purchase_item(data.result);
                    } else {
                        msg = data.msg;
                    }
                }
            });
            if (row) {
                $('#mModal').modal('hide');
                //audio_success.play();
            } else {
                $('#mError').text(msg);
                $('#mError-con').show();
            }
            return false;

        });

        let selectedItem = null;

        //  NEW CHANGE FOR ADDING ITEM
        $("#add_item_new").autocomplete({
            // source: '<?= admin_url('purchases/suggestions'); ?>',
            source: function(request, response) {
                $.ajax({
                    type: 'get',
                    url: '<?= admin_url('purchases/suggestions'); ?>',
                    dataType: "json",
                    data: {
                        term: request.term,
                        supplier_id: $("#posupplier").val()
                    },
                    success: function(data) {
                        $(this).removeClass('ui-autocomplete-loading');
                        response(data);
                    }
                });
            },
            minLength: 1,
            autoFocus: false,
            delay: 250,
            response: function(event, ui) {
                if ($(this).val().length >= 16 && ui.content[0].id == 0) {

                    $(this).removeClass('ui-autocomplete-loading');
                    $(this).val('');
                } else if (ui.content.length == 1 && ui.content[0].id != 0) {
                    ui.item = ui.content[0];
                    $(this).data('ui-autocomplete')._trigger('select', 'autocompleteselect', ui);
                    $(this).autocomplete('close');
                    $(this).removeClass('ui-autocomplete-loading');
                } else if (ui.content.length == 1 && ui.content[0].id == 0) {
                    //audio_error.play();
                    // bootbox.alert('<?= lang('no_match_found') ?>', function () {
                    //     $('#add_item').focus();
                    // });
                    $(this).removeClass('ui-autocomplete-loading');
                    $(this).val('');
                }
            },
            select: function(event, ui) {
                event.preventDefault();
                if (ui.item.id !== 0) {
                    console.log("uitem = " + JSON.stringify(ui.item));
                    selectedItem = ui.item;
                    //var row = add_purchase_item(ui.item);
                    $("input[name='item_name']").val(selectedItem.label || selectedItem.row.name);
                    //$("input[name='avz_code']").val(ui.item.row.code || '');
                    $("input[name='sale_price']").val(selectedItem.row.sale_price || '');
                    $("input[name='purchase_price']").val(selectedItem.row.cost || '');
                    $("input[name='batch']").val(selectedItem.row.batchno || '');
                    $("input[name='expiry_date']").val(selectedItem.row.expiry || '');
                    $("input[name='qty']").val(selectedItem.row.qty || 1);
                    $("input[name='bonus']").val(selectedItem.row.bonus || 0);
                    $("input[name='discount1']").val(selectedItem.row.dis1 || 0);
                    $("input[name='discount2']").val(selectedItem.row.dis2 || 0);
                    $("input[name='discount3']").val(selectedItem.row.discount || 0);
                    $("input[name='deal']").val(selectedItem.row.deal || 0);
                    $("input[name='vat']").val(selectedItem.tax_rate.rate || 15);
                    $("input[name='total_purchases']").val(selectedItem.row.base_unit_cost || '');
                    $("input[name='total_sales']").val(selectedItem.row.sale_price || '');
                    $("input[name='net_purchases']").val(selectedItem.row.real_unit_cost || '');
                    $("input[name='unit_cost']").val(selectedItem.row.unit_cost || selectedItem.row.cost || '');


                } else {
                    //audio_error.play();
                    //bootbox.alert('<?= lang('no_match_found') ?>');
                }
            }
        });


        $("#saveButton").on("keydown", function(e) {
            if (e.key === "Enter") {
                e.preventDefault();
                $(this).click();
            }
        });

         $("#clearItems").on("keydown", function(e) {
            if (e.key === "Enter") {
                e.preventDefault();
                $(this).click();
            }
        });
        
          $("#clearItems").on("click", function() {
              $('.new_item_cls').val('');
          });


        $("#saveButton").on("click", function() {
            if (!selectedItem || !selectedItem.row) {
                alert("No item selected.");
                return;
            }
            //console.log($("input[name='expiry_date']").val());
            let expiryInput = $("input[name='expiry_date']").val(); // e.g., "2026-12-12"

            if (expiryInput) {
                let parts = expiryInput.split('-'); // ["2026", "12", "12"]
                selectedItem.row.expiry = `${parts[2]}/${parts[1]}/${parts[0]}`; // "12/12/2026"
            } else {
                selectedItem.row.expiry = "";
            }
            // Update ui.item.row with new input field values
            selectedItem.row.batchno = $("input[name='batch']").val();
            //selectedItem.row.expiry = $("input[name='expiry_date']").val();
            selectedItem.row.qty = parseFloat($("input[name='qty']").val()) || 0;
            selectedItem.row.bonus = parseFloat($("input[name='bonus']").val()) || 0;
            selectedItem.row.dis1 = parseFloat($("input[name='discount1']").val()) || 0;
            selectedItem.row.dis2 = parseFloat($("input[name='discount2']").val()) || 0;
            selectedItem.row.dis3 = parseFloat($("input[name='discount3']").val()) || 0;
            selectedItem.row.deal = parseFloat($("input[name='deal']").val()) || 0;
            selectedItem.row.cost = parseFloat($("input[name='purchase_price']").val()) || 0;
            selectedItem.row.sale_price = parseFloat($("input[name='sale_price']").val()) || 0;
            selectedItem.row.vat = parseFloat($("input[name='vat']").val()) || 0;
            selectedItem.row.unit_cost = parseFloat($("input[name='unit_cost']").val()) || 0;

            // Now pass updated object
            add_purchase_item(selectedItem);

            $('.new_item_cls').val('');
        });

    });
</script>

<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-plus"></i><?= lang('create_purchase_order'); ?></h2>

        <!-- CSV upload icon -->
        <div class="box-icon">

        </div>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">

                <!-- <p class="introtext"><?php echo lang('enter_info'); ?></p> -->
                <?php
                $attrib = ['data-toggle' => 'validator', 'role' => 'form'];
                echo admin_form_open_multipart('purchase_order/add', $attrib)
                ?>
                <?php if (isset($action) && $action == 'create_po' && isset($pr_id)) { ?>
                    <input type="hidden" name="action" value="<?= $action; ?>">
                    <input type="hidden" name="pr_id" value="<?= $pr_id; ?>">
                <?php } ?>

                <fieldset class="scheduler-border">
                    <legend class="scheduler-border">Purchase Info</legend>

                    <div class="row">

                        <div class="col-md-2">
                            <div class="form-group">
                                <?= lang('date', 'podate'); ?>
                                <?php echo form_input('date', ($_POST['date'] ?? ''), 'class="form-control input-tip datetime" id="podate" required="required"'); ?>
                            </div>
                        </div>

                        <div class="col-md-2">
                            <div class="form-group">
                                <?= lang('Supplier Inv No.', 'poref'); ?>
                                <?php echo form_input('reference_no', ($_POST['reference_no'] ?? $ponumber), 'class="form-control input-tip" id="poref"'); ?>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="form-group">
                                <?= lang('warehouse', 'powarehouse'); ?>
                                <?php
                                $wh[''] = '';
                                foreach ($warehouses as $warehouse) {
                                    $wh[$warehouse->id] = $warehouse->name . ' (' . $warehouse->code . ')';
                                }
                                echo form_dropdown('warehouse', $wh, ($_POST['warehouse'] ?? $Settings->default_warehouse), 'id="powarehouse" class="form-control input-tip select" data-placeholder="' . lang('select') . ' ' . lang('warehouse') . '" required="required" style="width:100%;" '); ?>
                            </div>
                        </div>


                        <div class="col-md-3">
                            <div class="form-group">
                                <?= lang('attachments', 'document') ?>
                                <input id="document" type="file" data-browse-label="<?= lang('browse'); ?>" name="attachment" multiple data-show-upload="false" data-show-preview="false" class="form-control file">
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="form-group">

                                <input type="hidden" name="supplier" value="" id="posupplier"
                                    class="form-control" style="width:100%;"
                                    placeholder="<?= lang('select') . ' ' . lang('supplier') ?>">
                                <input type="hidden" name="supplier_id" value="" id="supplier_id"
                                    class="form-control">

                            </div>
                        </div>

                        

                    </div>
                </fieldset>

                <fieldset class="scheduler-border">
                    <legend class="scheduler-border">Add new Item</legend>
                    <div class="row">
                        <div class="col-lg-12">

                            <div class="col-md-12" id="sticker">
                                <div class="well well-sm" style="display:none">
                                    <div class="form-group" style="margin-bottom:0;">
                                        <div class="input-group wide-tip">
                                            <div class="input-group-addon" style="padding-left: 10px; padding-right: 10px;">
                                                <i class="fa fa-2x fa-barcode addIcon"></i></a>
                                            </div>
                                            <?php echo form_input('add_item', '', 'class="form-control input-lg" id="add_item_old" placeholder="' . $this->lang->line('add_product_to_order') . '"'); ?>
                                            <?php if ($Owner || $Admin || $GP['products-add']) {
                                            ?>
                                                <div class="input-group-addon" style="padding-left: 10px; padding-right: 10px;">
                                                    <a href="<?= admin_url('products/add') ?>" id="addManually1"><i
                                                            class="fa fa-2x fa-plus-circle addIcon" id="addIcon"></i></a>
                                                </div>
                                            <?php
                                            } ?>
                                        </div>
                                    </div>
                                    <div class="clearfix"></div>
                                </div>
                            </div>


                            <div class="form-group">
                                <label class="col-md-2 control-label">Item Name</label>
                                <div class="col-md-6">
                                    <input type="text" class="form-control new_item_cls" id="add_item_new" name="item_name" placeholder="Enter item name">
                                </div>
                            </div>

                            <div class="clearfix"></div>


                            <div class="form-group">
                                <label class="col-md-2 control-label">Sale Price</label>
                                <div class="col-md-2">
                                    <input type="number" class="form-control new_item_cls" name="sale_price" readonly step="0.01" placeholder="0.00">
                                </div>

                                <label class="col-md-2 control-label">Purchase Price</label>
                                <div class="col-md-2">
                                    <input type="number" class="form-control new_item_cls" name="purchase_price" step="0.01" placeholder="0.00">
                                </div>

                                <label class="col-md-2 control-label">Batch</label>
                                <div class="col-md-2">
                                    <input type="text" class="form-control new_item_cls" name="batch" placeholder="Batch no.">
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-md-2 control-label">Expiry Date</label>
                                <div class="col-md-2">
                                    <input type="date" class="form-control new_item_cls" name="expiry_date">
                                </div>

                                <label class="col-md-2 control-label">Quantity</label>
                                <div class="col-md-2">
                                    <input type="number" class="form-control new_item_cls" name="qty" placeholder="0">
                                </div>

                                <label class="col-md-2 control-label">Bonus</label>
                                <div class="col-md-2">
                                    <input type="number" class="form-control new_item_cls" name="bonus" placeholder="0">
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-md-2 control-label">Discount 1 (%)</label>
                                <div class="col-md-2">
                                    <input type="number" class="form-control new_item_cls" name="discount1" step="0.01" placeholder="0%">
                                </div>

                                <label class="col-md-2 control-label">Discount 2 (%)</label>
                                <div class="col-md-2">
                                    <input type="number" class="form-control new_item_cls" name="discount2" step="0.01" placeholder="0%">
                                </div>

                                <label class="col-md-2 control-label">Discount 3 (%)</label>
                                <div class="col-md-2">
                                    <input type="number" class="form-control" name="discount3 new_item_cls" step="0.01" placeholder="0%">
                                </div>
                            </div>

                            <div class="form-group">


                                <label class="col-md-2 control-label">VAT (15%)</label>
                                <div class="col-md-2">
                                    <input type="number" class="form-control new_item_cls" name="vat" step="0.01" readonly placeholder="15%">
                                </div>

                                <label class="col-md-2 control-label">Deal (%)</label>
                                <div class="col-md-2">
                                    <input type="number" class="form-control new_item_cls" name="deal" step="0.01" placeholder="0%">
                                </div>


                            </div>


                            <div class="form-group">
                                <div class="col-md-2">
                                    <button type="button" id="saveButton" class="btn btn-success">Save Item</button>
                                    <button type="button" id="clearItems" class="btn btn-default">Clear</button>
                                </div>

                            </div>

                        </div>
                    </div>
                </fieldset>



                <div class="row">

                    <div class="col-md-12">
                        <div class="control-group table-group">
                            <label class="table-label"><?= lang('order_items'); ?></label>

                            <div class="controls table-controls" style="font-size: 12px !important;">
                                <table id="poTable"
                                    class="table items table-striped table-bordered table-condensed table-hover sortable_table">
                                    <thead>
                                        <tr>
                                            <th class="col-md-1" style="width:3%">#</th>
                                            <th class="col-md-2">item name</th>
                                            <th class="col-md-1">avz code</th>
                                            <th class="col-md-1">sale price</th>
                                            <th class="col-md-1">purchase price</th>
                                            <!--<th class="col-md-1">Serial No.</th>-->
                                            <th class="col-md-1">Batch</th>
                                            <?php
                                            if ($Settings->product_expiry) {
                                                echo '<th class="col-md-1">' . $this->lang->line('expiry_date') . '</th>';
                                            }
                                            ?>


                                            <th class="col-md-1" style="width: 5%">qty</th>
                                            <th class="col-md-1" style="width: 5%">bonus</th>
                                            <th class="col-md-1" style="width: 5%">dis 1%</th>
                                            <th class="col-md-1" style="width: 5%">dis 2%</th>
                                            <th class="col-md-1" style="width: 5%">Vat 15%</th>
                                            <th class="col-md-1" style="width: 5%">dis 3%</th>
                                            <th class="col-md-1" style="width: 5%">deal%</th>
                                            <?php
                                            /*if ($Settings->product_discount) {
                                                echo '<th class="col-md-1">' . $this->lang->line('discount') . '</th>';
                                            }*/
                                            ?>
                                            <?php
                                            /*if ($Settings->tax1) {
                                                echo '<th class="col-md-1">' . $this->lang->line('product_tax') . '</th>';
                                            }*/
                                            ?>

                                            <th class="col-md-1">Total Purchases</th>
                                            <th class="col-md-1">Total Sales</th>
                                            <th class="col-md-1">Net Purchases</th>
                                            <th class="col-md-1">Unit Cost</th>

                                            <th style="width: 30px !important; text-align: center;"><i
                                                    class="fa fa-trash-o"
                                                    style="opacity:0.5; filter:alpha(opacity=50);"></i></th>

                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                    <tfoot></tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="clearfix"></div>
                    <input type="hidden" name="total_items" value="" id="total_items" required="required" />

                    <div class="col-md-12">

                        <div class="row" id="extras-con" style="display: none;">
                            <?php if ($Settings->tax2) {
                            ?>

                            <?php
                            } ?>




                        </div>
                        <div class="clearfix"></div>
                        <div class="form-group">
                            <?= lang('note', 'ponote'); ?>
                            <?php echo form_textarea('note', ($_POST['note'] ?? ''), 'class="form-control" id="ponote" style="margin-top: 10px; height: 100px;"'); ?>
                        </div>

                    </div>
                    <div class="col-md-12">
                        <?php
                        $data = array(
                            'name' => 'add_pruchase',
                            'onclick' => "return confirm('Are you sure to proceed?')"
                        );
                        ?>
                        <div
                            class="from-group"><?php echo form_submit($data, $this->lang->line('submit'), 'id="add_pruchase" class="btn btn-primary" style="padding: 6px 15px; margin:15px 0;"'); ?>

                        </div>
                    </div>
                </div>
            </div>
            <div id="bottom-total" class="well well-sm" style="margin-bottom: 0;">
                <table class="table table-bordered table-condensed totals" style="margin-bottom:0;">
                    <tr class="warning">
                        <td><?= lang('items') ?> <span class="totals_val pull-right" id="titems">0</span></td>
                        <td><?= lang('total') ?> <span class="totals_val pull-right" id="total">0.00</span></td>
                        <td><?= lang('order_discount') ?> <span class="totals_val pull-right" id="tds">0.00</span></td>
                        <?php if ($Settings->tax2) {
                        ?>
                            <!-- <td><?= lang('order_tax') ?> <span class="totals_val pull-right" id="ttax2">0.00</span></td> -->
                            <td><?= lang('VAT') ?> <span class="totals_val pull-right" id="grand_vat">0.00</span></td>
                        <?php
                        } ?>
                        <td><?= lang('shipping') ?> <span class="totals_val pull-right" id="tship">0.00</span></td>
                        <td><?= lang('grand_total') ?> <span class="totals_val pull-right" id="gtotal">0.00</span></td>
                    </tr>
                </table>
            </div>

            <?php echo form_close(); ?>

        </div>

    </div>
</div>
</div>

<div class="modal" id="prModal" role="dialog" aria-labelledby="prModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true"><i
                            class="fa fa-2x">&times;</i></span><span class="sr-only"><?= lang('close'); ?></span></button>
                <h4 class="modal-title" id="prModalLabel"></h4>
            </div>
            <div class="modal-body" id="pr_popover_content">
                <form class="form-horizontal" role="form">
                    <?php if ($Settings->tax1) {
                    ?>
                        <div class="form-group">
                            <label class="col-sm-4 control-label"><?= lang('product_tax') ?></label>
                            <div class="col-sm-8">
                                <?php
                                $tr[''] = '';
                                foreach ($tax_rates as $tax) {
                                    $tr[$tax->id] = $tax->name;
                                }
                                echo form_dropdown('ptax', $tr, '', 'id="ptax" class="form-control pos-input-tip" style="width:100%;"'); ?>
                            </div>
                        </div>
                    <?php
                    } ?>
                    <div class="form-group">
                        <label for="pquantity" class="col-sm-4 control-label"><?= lang('quantity') ?></label>

                        <div class="col-sm-8">
                            <input type="text" class="form-control" id="pquantity">
                        </div>
                    </div>
                    <?php if ($Settings->product_expiry) {
                    ?>
                        <div class="form-group">
                            <label for="pexpiry" class="col-sm-4 control-label"><?= lang('product_expiry') ?></label>

                            <div class="col-sm-8">
                                <input type="text" class="form-control date" id="pexpiry">
                            </div>
                        </div>
                    <?php
                    } ?>
                    <div class="form-group">
                        <label for="punit" class="col-sm-4 control-label"><?= lang('product_unit') ?></label>
                        <div class="col-sm-8">
                            <div id="punits-div"></div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="poption" class="col-sm-4 control-label"><?= lang('product_option') ?></label>
                        <div class="col-sm-8">
                            <div id="poptions-div"></div>
                        </div>
                    </div>
                    <?php /*if ($Settings->product_discount) {
                                                ?>
                        <div class="form-group">
                            <label for="pdiscount" class="col-sm-4 control-label"><?= lang('product_discount') ?></label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control" id="pdiscount">
                            </div>
                        </div>
                    <?php
                                            } */ ?>
                    <div class="form-group">
                        <label for="pcost" class="col-sm-4 control-label"><?= lang('unit_cost') ?></label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control" id="pcost">
                        </div>
                    </div>
                    <table class="table table-bordered table-striped">
                        <tr>
                            <th style="width:25%;"><?= lang('net_unit_cost'); ?></th>
                            <th style="width:25%;"><span id="net_cost"></span></th>
                            <th style="width:25%;"><?= lang('product_tax'); ?></th>
                            <th style="width:25%;"><span id="pro_tax"></span></th>
                        </tr>
                    </table>
                    <div class="panel panel-default">
                        <div class="panel-heading"><?= lang('calculate_unit_cost'); ?></div>
                        <div class="panel-body">

                            <div class="form-group">
                                <label for="pcost" class="col-sm-4 control-label"><?= lang('subtotal') ?></label>
                                <div class="col-sm-8">
                                    <div class="input-group">
                                        <input type="text" class="form-control" id="psubtotal">
                                        <div class="input-group-addon" style="padding: 2px 8px;">
                                            <a href="#" id="calculate_unit_price" class="tip" title="<?= lang('calculate_unit_cost'); ?>">
                                                <i class="fa fa-calculator"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <input type="hidden" id="punit_cost" value="" />
                    <input type="hidden" id="old_tax" value="" />
                    <input type="hidden" id="old_qty" value="" />
                    <input type="hidden" id="old_cost" value="" />
                    <input type="hidden" id="row_id" value="" />
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" id="editItem"><?= lang('submit') ?></button>
            </div>
        </div>
    </div>
</div>

<div class="modal" id="mModal" role="dialog" aria-labelledby="mModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true"><i
                            class="fa fa-2x">&times;</i></span><span class="sr-only"><?= lang('close'); ?></span></button>
                <h4 class="modal-title" id="mModalLabel"><?= lang('add_standard_product') ?></h4>
            </div>
            <div class="modal-body" id="pr_popover_content">
                <div class="alert alert-danger" id="mError-con" style="display: none;">
                    <!--<button data-dismiss="alert" class="close" type="button">×</button>-->
                    <span id="mError"></span>
                </div>
                <div class="row">
                    <div class="col-md-6 col-sm-6">
                        <div class="form-group">
                            <?= lang('product_code', 'mcode') ?> *
                            <input type="text" class="form-control" id="mcode">
                        </div>
                        <div class="form-group">
                            <?= lang('product_name', 'mname') ?> *
                            <input type="text" class="form-control" id="mname">
                        </div>
                        <div class="form-group">
                            <?= lang('category', 'mcategory') ?> *
                            <?php
                            $cat[''] = '';
                            foreach ($categories as $category) {
                                $cat[$category->id] = $category->name;
                            }
                            echo form_dropdown('category', $cat, '', 'class="form-control select" id="mcategory" placeholder="' . lang('select') . ' ' . lang('category') . '" style="width:100%"')
                            ?>
                        </div>
                        <div class="form-group">
                            <?= lang('unit', 'munit') ?> *
                            <input type="text" class="form-control" id="munit">
                        </div>
                    </div>
                    <div class="col-md-6 col-sm-6">
                        <div class="form-group">
                            <?= lang('cost', 'mcost') ?> *
                            <input type="text" class="form-control" id="mcost">
                        </div>
                        <div class="form-group">
                            <?= lang('price', 'mprice') ?> *
                            <input type="text" class="form-control" id="mprice">
                        </div>

                        <?php if ($Settings->tax1) {
                        ?>
                            <div class="form-group">
                                <?= lang('product_tax', 'mtax') ?>
                                <?php
                                $tr[''] = '';
                                foreach ($tax_rates as $tax) {
                                    $tr[$tax->id] = $tax->name;
                                }
                                echo form_dropdown('mtax', $tr, '', 'id="mtax" class="form-control input-tip select" style="width:100%;"'); ?>
                            </div>
                            <div class="form-group all">
                                <?= lang('tax_method', 'mtax_method') ?>
                                <?php
                                $tm = ['0' => lang('inclusive'), '1' => lang('exclusive')];
                                echo form_dropdown('tax_method', $tm, '', 'class="form-control select" id="mtax_method" placeholder="' . lang('select') . ' ' . lang('tax_method') . '" style="width:100%"')
                                ?>
                            </div>
                        <?php
                        } ?>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" id="addItemManually"><?= lang('submit') ?></button>
            </div>
        </div>
    </div>
</div>