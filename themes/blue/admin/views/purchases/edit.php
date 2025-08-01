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
    var count = 1,
        an = 1,
        po_edit = true,
        ws_edit = false,
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
    $(window).bind("load", function() {
        <?php //($inv->status == 'received' || $inv->status == 'partial') ? '$(".rec_con").show(); $("#temp_lot").show();' : '$(".rec_con").hide(); $("#temp_lot").hide();'; 
        ?>
        <?php if ($GP["purchase_receiving_supervisor"] || ($inv->status == 'arrived' || $inv->status == 'received' || $inv->status == 'partial' || $inv->status == 'rejected')) { ?>

            $(".rec_con").show();
            $("#temp_lot").show();
            po_edit = true;
            loadItems();
        <?php } else { ?>

            $(".rec_con").hide();
            $("#temp_lot").hide();
            po_edit = false;
            loadItems();
        <?php } ?>

        <?php if ($GP["purchase_warehouse_supervisor"] || $GP["purchase_supervisor"]) { ?>
            ws_edit = true;
            $('input, textbox, select:not(#warehouse_shelf)').attr('readonly', 'readonly');
            $('[title=Remove]').removeClass('podel');
            $('#postatus').prop("disabled", false);
            //$('#warehouse_shelf').prop("disabled", false);
            $('#postatus').attr('readonly', 'readonly');

        <?php } ?>
    });
    $(document).ready(function() {
        <?php // ($inv->status == 'received' || $inv->status == 'partial') ? '$(".rec_con").show();' : '$(".rec_con").hide();'; 
        ?>


        $('#postatus').change(function() {
            var st = $(this).val();
            if (st == 'received' || st == 'partial') {
                $(".rec_con").show();
                $("#temp_lot").show();
                po_edit = true;
                loadItems();
            } else {
                $(".rec_con").hide();
                $("#temp_lot").hide();
                po_edit = false;
                loadItems();
            }
        });
        <?php if ($inv) {
        ?>
            localStorage.setItem('podate', '<?= date($dateFormats['php_ldate'], strtotime($inv->date)) ?>');
            localStorage.setItem('posupplier', '<?= $inv->supplier_id ?>');
            localStorage.setItem('poref', '<?= $inv->reference_no ?>');
            localStorage.setItem('powarehouse', '<?= $inv->warehouse_id ?>');
            localStorage.setItem('postatus', '<?= $inv->status ?>');
            localStorage.setItem('ponote', '<?= str_replace(["\r", "\n"], '', $this->sma->decode_html($inv->note)); ?>');
            localStorage.setItem('podiscount', '<?= $inv->order_discount_id ?>');
            localStorage.setItem('potax2', '<?= $inv->order_tax_id ?>');
            localStorage.setItem('poshipping', '<?= $inv->shipping ?>');
            localStorage.setItem('popayment_term', '<?= $inv->payment_term ?>');
            if (parseFloat(localStorage.getItem('potax2')) >= 1 || localStorage.getItem('podiscount').length >= 1 || parseFloat(localStorage.getItem('poshipping')) >= 1) {
                localStorage.setItem('poextras', '1');
            }
            localStorage.setItem('poitems', JSON.stringify(<?= $inv_items; ?>));
        <?php
        } ?>

        <?php if ($Owner || $Admin) {
        ?>
            $(document).on('change', '#podate', function(e) {
                localStorage.setItem('podate', $(this).val());
            });
            if (podate = localStorage.getItem('podate')) {
                $('#podate').val(podate);
            }
        <?php
        } ?>
        ItemnTotals();
        $("#add_item").autocomplete({
            //source: '<?= admin_url('purchases/suggestions'); ?>',
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
                    bootbox.alert('<?= lang('no_match_found') ?>', function() {
                        $('#add_item').focus();
                    });
                    $(this).removeClass('ui-autocomplete-loading');
                    $(this).val('');
                } else if (ui.content.length == 1 && ui.content[0].id != 0) {
                    ui.item = ui.content[0];
                    $(this).data('ui-autocomplete')._trigger('select', 'autocompleteselect', ui);
                    $(this).autocomplete('close');
                    $(this).removeClass('ui-autocomplete-loading');
                } else if (ui.content.length == 1 && ui.content[0].id == 0) {
                    bootbox.alert('<?= lang('no_match_found') ?>', function() {
                        $('#add_item').focus();
                    });
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
                    bootbox.alert('<?= lang('no_match_found') ?>');
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
            } else {
                $('#mError').text(msg);
                $('#mError-con').show();
            }
            return false;

        });
        $(window).bind('beforeunload', function(e) {
            $.get('<?= admin_url('welcome/set_data/remove_pols/1'); ?>');
            if (count > 1) {
                var message = "You will loss data!";
                return message;
            }
        });
        $('#reset').click(function(e) {
            $(window).unbind('beforeunload');
        });
        $('#edit_pruchase').click(function() {
            $(window).unbind('beforeunload');
            $('form.edit-po-form').submit();
        });
        $('#postatus1').click(function() {
            $(window).unbind('beforeunload');
            $('form.edit-po-form').submit();
        });
        $('#postatus2').click(function() {
            $(window).unbind('beforeunload');
            $('form.edit-po-form').submit();
        });
        $('#postatus3').click(function() {
            $(window).unbind('beforeunload');
            $('form.edit-po-form').submit();
        });

    });
</script>

<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-edit"></i><?= lang('edit_purchase'); ?></h2>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">

                <p class="introtext"><?php echo lang('enter_info'); ?></p>
                <?php
                $attrib = ['data-toggle' => 'validator', 'role' => 'form', 'class' => 'edit-po-form'];
                echo admin_form_open_multipart('purchases/edit/' . $inv->id, $attrib)
                ?>


                <div class="row">
                    <div class="col-lg-12">

                        <?php if ($Owner || $Admin) {
                        ?>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <?= lang('date', 'podate'); ?>
                                    <?php echo form_input('date', ($_POST['date'] ?? $this->sma->hrld($purchase->date)), 'class="form-control input-tip datetime" id="podate" required="required"'); ?>
                                </div>
                            </div>

                        <?php
                        } ?>
                        <div class="col-md-4">
                            <div class="form-group">
                                <?= lang('Supplier Reference Number', 'poref'); ?>
                                <?php echo form_input('reference_no', ($_POST['reference_no'] ?? $purchase->reference_no), 'class="form-control input-tip" id="poref" required="required"'); ?>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <?= lang('warehouse', 'powarehouse'); ?>
                                <?php
                                $wh[''] = '';
                                foreach ($warehouses as $warehouse) {
                                    $wh[$warehouse->id] = $warehouse->name.' ('.$warehouse->code.')';
                                }     
                                echo form_dropdown('warehouse', $wh, ($_POST['warehouse'] ?? $purchase->warehouse_id), 'id="powarehouse" class="form-control input-tip select" data-placeholder="' . $this->lang->line('select') . ' ' . $this->lang->line('warehouse') . '" required="required" style="width:100%;" ');
                                ?>
                            </div>
                        </div>
                        <?php if ($Owner || $Admin) {
                        ?>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <?= lang('status', 'postatus'); ?>
                                    <?php
                                    //$post = ['received' => lang('received'), 'partial' => lang('partial'), 'pending' => lang('pending'), 'ordered' => lang('ordered')];
                                    $post = ['received' => lang('received'), 'rejected' => lang('rejected'), 'pending' => lang('pending')];

                                    echo form_dropdown('status', $post, ($_POST['status'] ?? $purchase->status), ' class="form-control input-tip select" data-placeholder="' . $this->lang->line('select') . ' ' . $this->lang->line('status') . '" id="postatus"  style="width:100%;" ');
                                    ?>
                                </div>
                            </div>
                        <?php } else { ?>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <?= lang('status', 'postatus'); ?>
                                    <?php
                                    //$post = ['received' => lang('received'), 'partial' => lang('partial'), 'pending' => lang('pending'), 'ordered' => lang('ordered')];
                                    $post = ['received' => lang('received'),  'rejected' => lang('rejected'), 'pending' => lang('pending')];

                                    echo form_dropdown('status', $post, ($_POST['status'] ?? $purchase->status), ' class="form-control input-tip select" data-placeholder="' . $this->lang->line('select') . ' ' . $this->lang->line('status') . ' id="postatus"  style="width:100%;" ');
                                    ?>
                                </div>
                            </div>

                        <?php } ?>

                        <div class="col-md-4">
                            <div class="form-group">
                                <?= lang('attachments', 'document') ?>
                                <input id="document" type="file" data-browse-label="<?= lang('browse'); ?>" name="attachments[]" multiple data-show-upload="false" data-show-preview="false" class="form-control file">
                            </div>
                        </div>
                        <?php //if($inv->shelf_status != NULL || $inv->shelf_status == NULL){ 
                        if (!is_null($inv->shelf_status)) {
                        ?>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <?= lang('Shelf Status', 'Shelf Status') ?>
                                    <input type="text" name="shelf_status" class="form-control input-tip" readonly="readonly" value="<?= $inv->shelf_status; ?>">
                                </div>
                            </div>
                        <?php } ?>

                        <?php //if($inv->validate != NULL || $inv->validate == NULL){ 
                        if (!is_null($inv->validate)) {
                        ?>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <?= lang('Validation Status', 'Validation Status') ?>
                                    <input type="text" name="validate" class="form-control input-tip" readonly="readonly" value="<?= $inv->validate; ?>">
                                </div>
                            </div>
                        <?php } ?>

                        <div class="row" id="temp_lot">
                            <div class="col-lg-12">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <?= lang('Temperature', 'Temperature'); ?>
                                        <?php

                                        $temp = ['accepted' => lang('Accepted'), 'rejected' => lang('Rejected')];
                                        echo form_dropdown('tempstatus', $temp, ($_POST['temp'] ?? ''), 'id="tempstatus" class="form-control input-tip select" data-placeholder="' . $this->lang->line('select') . ' ' . $this->lang->line('temperature') . '"  style="width:100%;" ');
                                        ?>

                                    </div>
                                </div>
                                <!--<div class="col-md-4">
                                <div class="form-group">
                                <?php //lang('Lot Number', 'Lot Number'); 
                                ?>
                                    <?php //echo form_input('lotnumber', ($_POST['lotnumber'] ?? $purchase->lotnumber), 'class="form-control input-tip" id="lotnumber"'); 
                                    ?>
                                </div>
                           </div>-->
                            </div>
                        </div>

                        <div class="col-md-12">
                            <div class="panel panel-warning">
                                <div class="panel-heading"><?= lang('please_select_these_before_adding_product') ?></div>
                                <div class="panel-body" style="padding: 5px;">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <?= lang('supplier', 'posupplier'); ?>
                                            <div class="input-group">
                                                <input type="hidden" name="supplier" value="" id="posupplier" class="form-control" style="width:100%;" placeholder="<?= lang('select') . ' ' . lang('supplier') ?>">

                                                <div class="input-group-addon" style="padding-left: 10px; padding-right: 10px;">
                                                    <a href="#" id="removeReadonly">
                                                        <i class="fa fa-unlock" id="unLock"></i>
                                                    </a>
                                                </div>
                                            </div>
                                            <input type="hidden" name="supplier_id" value="" id="supplier_id" class="form-control">
                                        </div>
                                    </div>

                                </div>
                            </div>
                            <div class="clearfix"></div>
                        </div>

                        <div class="col-md-12" id="sticker">
                            <div class="well well-sm">
                                <div class="form-group" style="margin-bottom:0;">
                                    <div class="input-group wide-tip">
                                        <div class="input-group-addon" style="padding-left: 10px; padding-right: 10px;">
                                            <i class="fa fa-2x fa-barcode addIcon"></i></a>
                                        </div>
                                        <?php echo form_input('add_item', '', 'class="form-control input-lg" id="add_item" placeholder="' . $this->lang->line('add_product_to_order') . '"'); ?>
                                        <div class="input-group-addon" style="padding-left: 10px; padding-right: 10px;">
                                            <a href="#" id="addManually"><i class="fa fa-2x fa-plus-circle addIcon" id="addIcon"></i></a>
                                        </div>
                                    </div>
                                </div>
                                <div class="clearfix"></div>
                            </div>
                        </div>

                        <div class="col-md-12">
                            <div class="control-group table-group">
                                <label class="table-label"><?= lang('order_items'); ?></label>

                                <div class="controls table-controls" style="font-size: 12px;">
                                    <table id="poTable" class="table items table-striped table-bordered table-condensed table-hover sortable_table">
                                        <thead>
                                            <tr>
                                                <th class="col-md-1">#</th>
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
                                                <th class="col-md-1">qty</th>
                                                <th class="col-md-1">bonus</th>
                                                <th class="col-md-1">dis 1</th>
                                                <th class="col-md-1">dis 2</th>
                                                <th class="col-md-1">Vat 15%</th>
                                                <th class="col-md-1">Total Purchases</th>
                                                <th class="col-md-1">Total Sales</th>
                                                <th class="col-md-1">Net Purchases</th>
                                                <th class="col-md-1">Unit Cost</th>
                                                <th style="width: 30px !important; text-align: center;">
                                                    <i class="fa fa-trash-o" style="opacity:0.5; filter:alpha(opacity=50);"></i>
                                                </th>
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
                            <div class="form-group">
                                <input type="checkbox" class="checkbox" id="extras" value="" />
                                <label for="extras" class="padding05"><?= lang('more_options') ?></label>
                            </div>
                            <div class="row" id="extras-con" style="display: none;">
                                <?php if ($Settings->tax2) {
                                ?>
                                    <!-- <div class="col-md-4">
                                        <div class="form-group">
                                            <?= lang('order_tax', 'potax2') ?>
                                            <?php
                                            $tr[''] = '';
                                            foreach ($tax_rates as $tax) {
                                                $tr[$tax->id] = $tax->name;
                                            }
                                            echo form_dropdown('order_tax', $tr, '', 'id="potax2" class="form-control input-tip select" style="width:100%;"'); ?>
                                        </div>
                                    </div> -->
                                <?php
                                } ?>

                                <div class="col-md-4">
                                    <div class="form-group">
                                        <?= lang('discount_label', 'podiscount'); ?>
                                        <?php echo form_input('discount', '', 'class="form-control input-tip" id="podiscount"'); ?>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group">
                                        <?= lang('shipping', 'poshipping'); ?>
                                        <?php echo form_input('shipping', '', 'class="form-control input-tip" id="poshipping"'); ?>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group">
                                        <?= lang('payment_term', 'popayment_term'); ?>
                                        <?php echo form_input('payment_term', '', 'class="form-control tip" data-trigger="focus" data-placement="top" title="' . lang('payment_term_tip') . '" id="popayment_term"'); ?>
                                    </div>
                                </div>
                            </div>
                            <div class="clearfix"></div>
                            <div class="form-group">
                                <?= lang('note', 'ponote'); ?>
                                <?php echo form_textarea('note', ($_POST['note'] ?? ''), 'class="form-control" id="ponote" style="margin-top: 10px; height: 100px;"'); ?>
                            </div>

                        </div>


                        <?php

                        if ($Owner) {

                            // OWNER 
                            /*if ($purchase->status == 'pending' || $purchase->status == 'ordered' || $purchase->status == 'rejected') {

                                echo '<div class="col-md-12"><div class="fprom-group">
                                            <input type="submit" class="btn btn-primary" id="postatus1" name="status" value="ordered" style="margin:15px 0;"/>
                                     </div></div>
                                    <div class="col-md-12"><div class="fprom-group">
                                            <input type="submit" class="btn btn-warning" id="postatus2" name="status" value="rejected" style="margin:15px 0;"/>
                                    </div></div>';
                            }

                            if ($inv->status == 'received' || $inv->status == 'partial') {
                                echo '<div class="col-md-12"><div class="fprom-group">';
                                echo form_submit('shelf_status', 'Shelves Added', 'id="edit_pruchase" class="btn btn-primary" style="padding: 6px 15px; margin:15px 0;"');
                                echo '</div></div>';
                            } else {
                                echo '<div class="col-md-12"><div class="fprom-group">
                                            <input type="submit" class="btn btn-primary" id="postatus1" name="status" value="received" style="margin:15px 0;"/>
                                        </div></div>
                                        <div class="col-md-12"><div class="fprom-group">
                                            <input type="submit" class="btn btn-warning" id="postatus2" name="status" value="partial" style="margin:15px 0;"/>
                                        </div></div>
                                        <div class="col-md-12"><div class="fprom-group">
                                            <input type="submit" class="btn btn-danger" id="postatus3" name="status" value="rejected" style="margin:15px 0;"/>
                                        </div></div>';
                            }

                            if (($inv->shelf_status != NULL)) {

                                if ($inv->validate != NULL) {
                                } else {
                                    echo '<div class="col-md-12"><div class="fprom-group">';
                                    echo form_submit('validate', 'validate', 'id="edit_pruchase" class="btn btn-primary" style="padding: 6px 15px; margin:15px 0;"');
                                    echo "</div></div>";
                                }
                            }*/
                            echo '<div class="col-md-12"><div class="fprom-group">';
                            echo form_submit('edit_pruchase', $this->lang->line('submit'), 'id="edit_pruchase" class="btn btn-primary" style="padding: 6px 15px; margin:15px 0;"');
                            echo "</div></div>";
                        } else {

                            // OLD Flow

                            if ($GP["purchase_manager"] && ($purchase->status == 'pending' || $purchase->status == 'ordered' || $purchase->status == 'rejected')) {
                                echo '<div class="col-md-12"><div class="fprom-group">
                                        <input type="submit" class="btn btn-primary" id="postatus1" name="status" value="ordered" style="margin:15px 0;"/>
                                     </div></div>
                                     <div class="col-md-12"><div class="fprom-group">
                                        <input type="submit" class="btn btn-warning" id="postatus2" name="status" value="rejected" style="margin:15px 0;"/>
                                    </div></div>';
                            } else if ($GP["purchase_receiving_supervisor"]) {

                                if ($inv->status == 'received' || $inv->status == 'partial') {
                                } else {

                                    echo '<div class="col-md-12"><div class="fprom-group">
                                            <input type="submit" class="btn btn-primary" id="postatus1" name="status" value="received" style="margin:15px 0;"/>
                                        </div></div>
                                        <div class="col-md-12"><div class="fprom-group">
                                            <input type="submit" class="btn btn-warning" id="postatus2" name="status" value="partial" style="margin:15px 0;"/>
                                        </div></div>
                                        <div class="col-md-12"><div class="fprom-group">
                                            <input type="submit" class="btn btn-danger" id="postatus3" name="status" value="rejected" style="margin:15px 0;"/>
                                        </div></div>';
                                }
                            } else if ($GP["purchase_warehouse_supervisor"] && ($inv->status == 'received' || $inv->status == 'partial')) {

                                echo '<div class="col-md-12"><div class="fprom-group">';
                                echo form_submit('shelf_status', 'Shelves Added', 'id="edit_pruchase" class="btn btn-primary" style="padding: 6px 15px; margin:15px 0;"');
                                echo '</div></div>';
                            } else if ($GP["purchase_supervisor"] && ($inv->shelf_status != NULL)) {

                                if ($inv->validate != NULL) {
                                } else {

                                    echo '<div class="col-md-12"><div class="fprom-group">';
                                    echo form_submit('validate', 'validate', 'id="edit_pruchase" class="btn btn-primary" style="padding: 6px 15px; margin:15px 0;"');
                                    echo "</div></div>";
                                }
                            } else {
                                echo '<div class="col-md-12"><div class="fprom-group">';
                                echo form_submit('edit_pruchase', $this->lang->line('submit'), 'id="edit_pruchase" class="btn btn-primary" style="padding: 6px 15px; margin:15px 0;"');
                                echo "</div></div>";
                            }
                        }

                        ?>
                        <!-- <button type="button" class="btn btn-danger" id="reset"><?= lang('reset') ?></button>-->

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

<div class="modal" id="prModal" tabindex="-1" role="dialog" aria-labelledby="prModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true"><i class="fa fa-2x">&times;</i></span><span class="sr-only"><?= lang('close'); ?></span></button>
                <h4 class="modal-title" id="prModalLabel"></h4>
            </div>
            <div class="modal-body" id="pr_popover_content">
                <form class="form-horizontal" role="form">

                    <?php if ($GP["purchase_warehouse_supervisor"] || $GP["purchase_supervisor"]) { ?>
                        <div class="form-group">
                            <label class="col-sm-4 control-label"><?= lang('Warehouse Shelf') ?></label>
                            <div class="col-sm-8">



                                <?php if (!empty($shelves)) {
                                    $sh[''] = '';
                                    foreach ($shelves as $shelf) {
                                        $sh[$shelf['id']] = $shelf['shelf_name'];
                                    }
                                    echo form_dropdown('warehouse_shelf', $sh, '', 'id="warehouse_shelf" class="form-control pos-input-tip" style="width:100%;"');
                                ?>





                                <?php

                                } ?>
                                </select>
                            </div>
                        </div>
                    <?php } ?>
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
                            <label for="pdiscount"
                                   class="col-sm-4 control-label"><?= lang('product_discount') ?></label>

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
                        <?php if ($GP["purchase_manager"]) { ?>
                            <tr>
                                <th style="width:75%;" colspan="2"><?= lang('3 Month Avarege Sale'); ?></th>
                                <th style="width:25%;" colspan="2"><span id="three_month_sale"></span></th>
                            </tr>
                        <?php } ?>
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

<div class="modal" id="mModal" tabindex="-1" role="dialog" aria-labelledby="mModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true"><i class="fa fa-2x">&times;</i></span><span class="sr-only"><?= lang('close'); ?></span></button>
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