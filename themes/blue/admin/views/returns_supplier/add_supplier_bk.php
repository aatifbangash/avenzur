<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<script type="text/javascript">
    var count = 1, an = 1, product_variant = 0, DT = <?= $Settings->default_tax_rate ?>,
        product_tax = 0, invoice_tax = 0, product_discount = 0, order_discount = 0, total_discount = 0, total = 0, allow_discount = <?= ($Owner || $Admin || $this->session->userdata('allow_discount')) ? 1 : 0; ?>,
        tax_rates = <?php echo json_encode($tax_rates); ?>;

    $(document).ready(function () {
        ItemnTotals();
        $('.bootbox').on('hidden.bs.modal', function (e) {
            $('#add_item').focus();
        });
        $("#add_item").autocomplete({
            source: function (request, response) {
                $.ajax({
                    type: 'get',
                    url: '<?= admin_url('returns/suggestions'); ?>',
                    dataType: "json",
                    data: { term: request.term },
                    success: function (data) {
                        $(this).removeClass('ui-autocomplete-loading');
                        response(data);
                    }
                });
            },
            minLength: 1,
            autoFocus: false,
            delay: 250,
            response: function (event, ui) {
                if ($(this).val().length >= 16 && ui.content[0].id == 0) {
                    bootbox.alert('<?= lang('no_match_found') ?>', function () {
                        $('#add_item').focus();
                    });
                    $(this).removeClass('ui-autocomplete-loading');
                    $(this).removeClass('ui-autocomplete-loading');
                    $(this).val('');
                }
                else if (ui.content.length == 1 && ui.content[0].id != 0) {
                    ui.item = ui.content[0];
                    $(this).data('ui-autocomplete')._trigger('select', 'autocompleteselect', ui);
                    $(this).autocomplete('close');
                    $(this).removeClass('ui-autocomplete-loading');
                }
                else if (ui.content.length == 1 && ui.content[0].id == 0) {
                    bootbox.alert('<?= lang('no_match_found') ?>', function () {
                        $('#add_item').focus();
                    });
                    $(this).removeClass('ui-autocomplete-loading');
                    $(this).val('');
                }
            },
            select: function (event, ui) {
                event.preventDefault();
                if (ui.item.id !== 0) {
                    var row = add_return_item(ui.item);
                    if (row)
                        $(this).val('');
                } else {
                    bootbox.alert('<?= lang('no_match_found') ?>');
                }
            }
        });
    });
</script>

<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-plus"></i><?= lang('add_return'); ?></h2>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
                <p class="introtext"><?php echo lang('enter_info'); ?></p>
                <?php
                $attrib = ['data-toggle' => 'validator', 'role' => 'form'];
                echo admin_form_open_multipart('returns/add_return', $attrib);
                ?>

                <input type="hidden" name="return_screen"  value="supplier">
                <div class="row">
                    <div class="col-lg-12">
                        <?php if ($Owner || $Admin) {
                         ?>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <?= lang('date', 'redate'); ?>
                                    <?php echo form_input('date', (isset($_POST['date']) ? $_POST['date'] : ''), 'class="form-control input-tip datetime" id="redate" required="required"'); ?>
                                </div>
                            </div>
                        <?php
                       } ?>

                        <div class="col-md-4">
                            <div class="form-group">
                                <?= lang('reference_no', 'reref'); ?>
                                <?php echo form_input('reference_no', (isset($_POST['reference_no']) ? $_POST['reference_no'] : ''), 'class="form-control input-tip" id="reref"'); ?>
                            </div>
                        </div>



                 <?php 
                 if ($Owner || $Admin || !$this->session->userdata('biller_id')) {
             ?>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <?= lang('biller', 'rebiller'); ?>
                                    <?php
                                    $bl[''] = '';
                    foreach ($billers as $biller) {
                        $bl[$biller->id] = $biller->company && $biller->company != '-' ? $biller->company : $biller->name;
                    }
                    echo form_dropdown('biller', $bl, (isset($_POST['biller']) ? $_POST['biller'] : $Settings->default_biller), 'id="rebiller" data-placeholder="' . lang('select') . ' ' . lang('biller') . '" required="required" class="form-control input-tip select" style="width:100%;"'); ?>
                                </div>
                            </div>
                        <?php
                } else {
                    $biller_input = [
                        'type'  => 'hidden',
                        'name'  => 'biller',
                        'id'    => 'rebiller',
                        'value' => $this->session->userdata('biller_id'),
                    ];
                    echo form_input($biller_input);
                }
                
                ?>




                    <?php if ($Owner || $Admin || !$this->session->userdata('warehouse_id')) { ?>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <?= lang('warehouse', 'rewarehouse'); ?>
                                    <?php
                                    $wh[''] = '';
                    foreach ($warehouses as $warehouse) {
                        $wh[$warehouse->id] = $warehouse->name;
                    }
                    echo form_dropdown('warehouse', $wh, (isset($_POST['warehouse']) ? $_POST['warehouse'] : $Settings->default_warehouse), 'id="rewarehouse" class="form-control input-tip select" data-placeholder="' . lang('select') . ' ' . lang('warehouse') . '" required="required" style="width:100%;" '); ?>
                                </div>
                            </div>
                        <?php
                } else  {
                    $warehouse_input = [
                    'type'  => 'hidden',
                    'name'  => 'warehouse',
                    'id'    => 'rewarehouse',
                    'value' => $this->session->userdata('warehouse_id'),
                    ];
                    echo form_input($warehouse_input);
                    } 
                
                ?>

                        <!-- <div class="col-md-4">
                            <div class="form-group">
                                <?= lang('supplier', 'resupplier'); ?>
                                <?php
                                    echo form_input('supplier', (isset($_POST['supplier']) ? $_POST['supplier'] : ''), 'id="resupplier" data-placeholder="' . lang('select') . ' ' . lang('supplier') . '" required="required" class="form-control ssr-suppliers input-tip" style="width:100%;"');
                                ?>
                            </div>
                        </div> -->

                            <div class="col-lg-4">
                            <div class="form-group">
                            <?= lang('supplier', 'supplier'); ?>
                            <select class="form-control" name="customer" id="customer" required>
                            <?php
                            foreach ($suppliers as $supplier) {
                            echo "<option value='$supplier->id'>$supplier->name</option>";
                            }
                            ?>
                            </select>
                            </div>
                            </div>



                         <?php if ($Settings->tax2) {  ?>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <?= lang('order_tax', 'retax2'); ?>
                                    <?php
                                    $tr[''] = '';
                                    foreach ($tax_rates as $tax) {
                                        $tr[$tax->id] = $tax->name;
                                    }
                                    echo form_dropdown('order_tax', $tr, (isset($_POST['order_tax']) ? $_POST['order_tax'] : $Settings->default_tax_rate2), 'id="retax2" data-placeholder="' . lang('select') . ' ' . lang('order_tax') . '" class="form-control input-tip select" style="width:100%;"'); ?>
                                </div>
                            </div>

                         <?php
                            } 
                            ?>

                        <?php if ($Owner || $Admin || $this->session->userdata('allow_discount')) {
                                    ?>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <?= lang('order_discount', 'rediscount'); ?>
                                    <?php echo form_input('order_discount', '', 'class="form-control input-tip" id="rediscount"'); ?>
                                </div>
                            </div>
                        <?php } ?>
                        <div class="col-md-4">
                            <div class="form-group">
                                <?= lang('shipping', 'reshipping'); ?>
                                <?php echo form_input('shipping', '', 'class="form-control input-tip" id="reshipping"'); ?>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <?= lang('document', 'document') ?>
                                <input id="document" type="file" data-browse-label="<?= lang('browse'); ?>" name="document" data-show-upload="false"
                                       data-show-preview="false" class="form-control file">
                            </div>
                        </div>

                        <div class="clearfix"></div>

                        <div class="col-md-12" id="sticker">
                            <div class="well well-sm">
                                <div class="form-group" style="margin-bottom:0;">
                                    <div class="input-group wide-tip">
                                        <div class="input-group-addon" style="padding-left: 10px; padding-right: 10px;">
                                            <i class="fa fa-2x fa-barcode addIcon"></i></a></div>
                                        <?php echo form_input('add_item', '', 'class="form-control input-lg" id="add_item" placeholder="' . lang('add_product_to_order') . '"'); ?>
                                        <?php if ($Owner || $Admin || $GP['products-add']) { ?>
                                        <div class="input-group-addon" style="padding-left: 10px; padding-right: 10px;">
                                            <a href="#" id="addManually" class="tip" title="<?= lang('add_product_manually') ?>">
                                                <i class="fa fa-2x fa-plus-circle addIcon" id="addIcon"></i>
                                            </a>
                                        </div>
                                        <?php
                                           } ?>
                                    </div>
                                </div>
                                <div class="clearfix"></div>
                            </div>
                        </div>

                        <div class="col-md-12">
                            <div class="control-group table-group">
                                <label class="table-label"><?= lang('order_items'); ?> *</label>

                                <div class="controls table-controls">
                                    <table id="reTable" class="table items table-striped table-bordered table-condensed table-hover sortable_table">
                                        <thead>
                                        <tr>
                                            <th class="col-md-2"><?= lang('product') . ' (' . lang('code') . ' - ' . lang('name') . ')'; ?></th>
                                           
                                                <th class="col-md-1"><?= lang('Sale Price'); ?></th>
                                                <th class="col-md-1"><?= lang('Purchase Price'); ?></th>
                                                <th class="col-md-1"><?= lang('batch'); ?></th>
                                                <th class="col-md-1"><?= lang('expiry_date'); ?></th>
                                                <th class="col-md-1"><?= lang('qty'); ?></th>
                                                <th class="col-md-1"><?= lang('Bonus'); ?></th>
                                                <?php
                                                if ($Settings->product_discount && ($Owner || $Admin || $this->session->userdata('allow_discount'))) {
                                                echo '<th class="col-md-1">' . lang('dis 1') . '</th>';
                                                }
                                                ?>
                                                <?php
                                                if ($Settings->product_discount && ($Owner || $Admin || $this->session->userdata('allow_discount'))) {
                                                echo '<th class="col-md-1">' . lang('dis 2') . '</th>';
                                                }
                                                ?>
                                                <?php
                                                if ($Settings->tax1) {
                                                echo '<th class="col-md-1">' . lang('vat 15%') . '</th>';
                                                }
                                                ?>

                                                <th class="col-md-1"><?= lang('Total Purchase'); ?></th>
                                                <th>
                                                <?= lang('Total Sale'); ?>
                                                (<span class="currency"><?= $default_currency->code ?></span>)
                                                </th>
                                                <th class="col-md-1"><?= lang('Net Sales'); ?></th>
                                                <th class="col-md-1"><?= lang('Unit Sale'); ?></th>



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
                        <input type="hidden" name="total_items" value="" id="total_items" required="required"/>

                        <div class="row" id="bt">
                            <div class="col-md-12">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <?= lang('return_note', 'renote'); ?>
                                        <?php echo form_textarea('note', (isset($_POST['note']) ? $_POST['note'] : ''), 'class="form-control" id="renote" style="margin-top: 10px; height: 100px;"'); ?>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <?= lang('staff_note', 'reinnote'); ?>
                                        <?php echo form_textarea('staff_note', (isset($_POST['staff_note']) ? $_POST['staff_note'] : ''), 'class="form-control" id="reinnote" style="margin-top: 10px; height: 100px;"'); ?>

                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div
                                class="fprom-group"><?php echo form_submit('add_return', lang('submit'), 'id="add_return" class="btn btn-primary" style="padding: 6px 15px; margin:15px 0;"'); ?>
                                <button type="button" class="btn btn-danger" id="reset"><?= lang('reset') ?></div>
                        </div>
                    </div>
                </div>
                <div id="bottom-total" class="well well-sm" style="margin-bottom: 0;">
                    <table class="table table-bordered table-condensed totals" style="margin-bottom:0;">
                        <tr class="warning">
                            <td><?= lang('items') ?> <span class="totals_val pull-right" id="titems">0</span></td>
                            <td><?= lang('total') ?> <span class="totals_val pull-right" id="total">0.00</span></td>
                            <?php if ($Owner || $Admin || $this->session->userdata('allow_discount')) {
                                                ?>
                            <td><?= lang('order_discount') ?> <span class="totals_val pull-right" id="tds">0.00</span></td>
                            <?php
                                            }?>
                            <?php if ($Settings->tax2) {
                                                ?>
                                <td><?= lang('order_tax') ?> <span class="totals_val pull-right" id="ttax2">0.00</span></td>
                            <?php
                                            } ?>
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
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true"><i
                            class="fa fa-2x">&times;</i></span><span class="sr-only"><?=lang('close');?></span></button>
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
                    <?php if ($Settings->product_serial) {
                                                ?>
                        <div class="form-group">
                            <label for="pserial" class="col-sm-4 control-label"><?= lang('serial_no') ?></label>

                            <div class="col-sm-8">
                                <input type="text" class="form-control" id="pserial">
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
                    <?php if ($Settings->product_discount && ($Owner || $Admin || $this->session->userdata('allow_discount'))) {
                                                ?>
                        <div class="form-group">
                            <label for="pdiscount"
                                   class="col-sm-4 control-label"><?= lang('product_discount') ?></label>

                            <div class="col-sm-8">
                                <input type="text" class="form-control" id="pdiscount">
                            </div>
                        </div>
                    <?php
                                            } ?>
                    <div class="form-group">
                        <label for="pprice" class="col-sm-4 control-label"><?= lang('unit_price') ?></label>

                        <div class="col-sm-8">
                            <input type="text" class="form-control" id="pprice" <?= ($Owner || $Admin || $GP['edit_price']) ? '' : 'readonly'; ?>>
                        </div>
                    </div>
                    <table class="table table-bordered table-striped">
                        <tr>
                            <th style="width:25%;"><?= lang('net_unit_price'); ?></th>
                            <th style="width:25%;"><span id="net_price"></span></th>
                            <th style="width:25%;"><?= lang('product_tax'); ?></th>
                            <th style="width:25%;"><span id="pro_tax"></span></th>
                        </tr>
                    </table>
                    <input type="hidden" id="punit_price" value=""/>
                    <input type="hidden" id="old_tax" value=""/>
                    <input type="hidden" id="old_qty" value=""/>
                    <input type="hidden" id="old_price" value=""/>
                    <input type="hidden" id="row_id" value=""/>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" id="editItem"><?= lang('submit') ?></button>
            </div>
        </div>
    </div>
</div>

<div class="modal" id="mModal" tabindex="-1" role="dialog" aria-labelledby="mModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true"><i
                            class="fa fa-2x">&times;</i></span><span class="sr-only"><?=lang('close');?></span></button>
                <h4 class="modal-title" id="mModalLabel"><?= lang('add_product_manually') ?></h4>
            </div>
            <div class="modal-body" id="pr_popover_content">
                <form class="form-horizontal" role="form">
                    <div class="form-group">
                        <label for="mcode" class="col-sm-4 control-label"><?= lang('product_code') ?> *</label>

                        <div class="col-sm-8">
                            <input type="text" class="form-control" id="mcode">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="mname" class="col-sm-4 control-label"><?= lang('product_name') ?> *</label>

                        <div class="col-sm-8">
                            <input type="text" class="form-control" id="mname">
                        </div>
                    </div>
                    <?php if ($Settings->tax1) {
                                                ?>
                        <div class="form-group">
                            <label for="mtax" class="col-sm-4 control-label"><?= lang('product_tax') ?> *</label>

                            <div class="col-sm-8">
                                <?php
                                $tr[''] = '';
                                                foreach ($tax_rates as $tax) {
                                                    $tr[$tax->id] = $tax->name;
                                                }
                                                echo form_dropdown('mtax', $tr, '', 'id="mtax" class="form-control input-tip select" style="width:100%;"'); ?>
                            </div>
                        </div>
                    <?php
                                            } ?>
                    <div class="form-group">
                        <label for="mquantity" class="col-sm-4 control-label"><?= lang('quantity') ?> *</label>

                        <div class="col-sm-8">
                            <input type="text" class="form-control" id="mquantity">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="munit" class="col-sm-4 control-label"><?= lang('unit') ?> *</label>

                        <div class="col-sm-8">
                            <?php
                            $uts[''] = '';
                            foreach ($units as $unit) {
                                $uts[$unit->id] = $unit->name;
                            }
                            echo form_dropdown('munit', $uts, '', 'id="munit" class="form-control input-tip select" style="width:100%;"');
                            ?>
                        </div>
                    </div>
                    <?php if ($Settings->product_discount && ($Owner || $Admin || $this->session->userdata('allow_discount'))) {
                                ?>
                        <div class="form-group">
                            <label for="mdiscount"
                                   class="col-sm-4 control-label"><?= lang('product_discount') ?></label>

                            <div class="col-sm-8">
                                <input type="text" class="form-control" id="mdiscount">
                            </div>
                        </div>
                    <?php
                            } ?>
                    <div class="form-group">
                        <label for="mprice" class="col-sm-4 control-label"><?= lang('unit_price') ?> *</label>

                        <div class="col-sm-8">
                            <input type="text" class="form-control" id="mprice">
                        </div>
                    </div>
                    <table class="table table-bordered table-striped">
                        <tr>
                            <th style="width:25%;"><?= lang('net_unit_price'); ?></th>
                            <th style="width:25%;"><span id="mnet_price"></span></th>
                            <th style="width:25%;"><?= lang('product_tax'); ?></th>
                            <th style="width:25%;"><span id="mpro_tax"></span></th>
                        </tr>
                    </table>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" id="addItemManually"><?= lang('submit') ?></button>
            </div>
        </div>
    </div>
</div>






<script>
$(document).ready(function (e) {
    $('body a, body button').attr('tabindex', -1);
    check_add_item_val();
    if (site.settings.set_focus != 1) {
        $('#add_item').focus();
    }

    // $('#add_return, #edit_return').attr('disabled', true);
    $(document).on('change', '.rserial', function () {
        var item_id = $(this).closest('tr').attr('data-item-id');
        reitemsales[item_id].row.serial = $(this).val();
        localStorage.setItem('reitemsales', JSON.stringify(reitemsales));
    });

    // If there is any item in localStorage
    if (localStorage.getItem('reitemsales')) {
        loadItems();
    }

    $('#reset').click(function (e) {
        bootbox.confirm(lang.r_u_sure, function (result) {
            if (result) {
                if (localStorage.getItem('reitemsales')) {
                    localStorage.removeItem('reitemsales');
                }
                if (localStorage.getItem('rediscount')) {
                    localStorage.removeItem('rediscount');
                }
                if (localStorage.getItem('reshipping')) {
                    localStorage.removeItem('reshipping');
                }
                if (localStorage.getItem('retax2')) {
                    localStorage.removeItem('retax2');
                }
                if (localStorage.getItem('reref')) {
                    localStorage.removeItem('reref');
                }
                if (localStorage.getItem('rewarehouse')) {
                    localStorage.removeItem('rewarehouse');
                }
                if (localStorage.getItem('renote')) {
                    localStorage.removeItem('renote');
                }
                if (localStorage.getItem('reinnote')) {
                    localStorage.removeItem('reinnote');
                }
                if (localStorage.getItem('recustomer')) {
                    localStorage.removeItem('recustomer');
                }
                if (localStorage.getItem('redate')) {
                    localStorage.removeItem('redate');
                }
                if (localStorage.getItem('rebiller')) {
                    localStorage.removeItem('rebiller');
                }

                $('#modal-loading').show();
                location.reload();
            }
        });
    });

    $('#redate').change(function (e) {
        localStorage.setItem('redate', $(this).val());
    });
    if ((redate = localStorage.getItem('redate'))) {
        $('#redate').val(redate);
    }

    $('#reref').change(function (e) {
        localStorage.setItem('reref', $(this).val());
    });
    if ((reref = localStorage.getItem('reref'))) {
        $('#reref').val(reref);
    }

    $('#rebiller').change(function (e) {
        localStorage.setItem('rebiller', $(this).val());
    });
    if ((rebiller = localStorage.getItem('rebiller'))) {
        $('#rebiller').val(rebiller);
    }

    $('#rewarehouse').change(function (e) {
        localStorage.setItem('rewarehouse', $(this).val());
    });
    if ((rewarehouse = localStorage.getItem('rewarehouse'))) {
        $('#rewarehouse').select2('val', rewarehouse);
    }

    $('#recustomer').change(function (e) {
        localStorage.setItem('recustomer', $(this).val());
    });
    if ((recustomer = localStorage.getItem('recustomer'))) {
        $('#recustomer')
            .val(recustomer)
            .select2({
                minimumInputLength: 1,
                data: [],
                initSelection: function (element, callback) {
                    $.ajax({
                        type: 'get',
                        async: false,
                        url: site.base_url + 'customers/getCustomer/' + $(element).val(),
                        dataType: 'json',
                        success: function (data) {
                            callback(data[0]);
                        },
                    });
                },
                ajax: {
                    url: site.base_url + 'customers/suggestions',
                    dataType: 'json',
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
                            return { results: [{ id: '', text: 'No Match Found' }] };
                        }
                    },
                },
            });
    }

    $('#retax2').change(function (e) {
        localStorage.setItem('retax2', $(this).val());
        $('#retax2').val($(this).val());
    });
    if ((retax2 = localStorage.getItem('retax2'))) {
        $('#retax2').select2('val', retax2);
    }

    $('#rediscount').change(function (e) {
        localStorage.setItem('rediscount', $(this).val());
    });
    if ((rediscount = localStorage.getItem('rediscount'))) {
        $('#rediscount').val(rediscount);
    }
    $('#reshipping').change(function (e) {
        localStorage.setItem('reshipping', $(this).val());
    });
    if ((reshipping = localStorage.getItem('reshipping'))) {
        $('#reshipping').val(reshipping);
    }

    $('#renote').redactor('destroy');
    $('#renote').redactor({
        buttons: [
            'formatting',
            '|',
            'alignleft',
            'aligncenter',
            'alignright',
            'justify',
            '|',
            'bold',
            'italic',
            'underline',
            '|',
            'unorderedlist',
            'orderedlist',
            '|',
            'link',
            '|',
            'html',
        ],
        formattingTags: ['p', 'pre', 'h3', 'h4'],
        minHeight: 100,
        changeCallback: function (e) {
            var v = this.get();
            localStorage.setItem('renote', v);
        },
    });
    if ((renote = localStorage.getItem('renote'))) {
        $('#renote').redactor('set', renote);
    }

    $('#reinnote').redactor('destroy');
    $('#reinnote').redactor({
        buttons: [
            'formatting',
            '|',
            'alignleft',
            'aligncenter',
            'alignright',
            'justify',
            '|',
            'bold',
            'italic',
            'underline',
            '|',
            'unorderedlist',
            'orderedlist',
            '|',
            'link',
            '|',
            'html',
        ],
        formattingTags: ['p', 'pre', 'h3', 'h4'],
        minHeight: 100,
        changeCallback: function (e) {
            var v = this.get();
            localStorage.setItem('reinnote', v);
        },
    });
    
    if ((reinnote = localStorage.getItem('reinnote'))) {
        $('#reinnote').redactor('set', reinnote);
    }

    $('body').bind('keypress', function (e) {
        if ($(e.target).hasClass('redactor_editor')) {
            return true;
        }
        if (e.keyCode == 13) {
            e.preventDefault();
            return false;
        }
    });

    if (site.settings.tax2 != 0) {
        $('#retax2').change(function () {
            localStorage.setItem('retax2', $(this).val());
            loadItems();
            return;
        });
    }

    var old_rediscount;
    $('#rediscount')
        .focus(function () {
            old_rediscount = $(this).val();
        })
        .change(function () {
            var new_discount = $(this).val() ? $(this).val() : '0';
            if (is_valid_discount(new_discount)) {
                localStorage.removeItem('rediscount');
                localStorage.setItem('rediscount', new_discount);
                loadItems();
                return;
            } else {
                $(this).val(old_rediscount);
                bootbox.alert(lang.unexpected_value);
                return;
            }
        });
    $('#reshipping').change(function () {
        var shipping = $(this).val() ? parseFloat($(this).val()) : 0;
        localStorage.setItem('reshipping', shipping);
        loadItems();
        return;
    });

    $(document).on('click', '.redel', function () {
        var row = $(this).closest('tr');
        var item_id = row.attr('data-item-id');
        delete reitemsales[item_id];
        row.remove();
        if (reitemsales.hasOwnProperty(item_id)) {
        } else {
            localStorage.setItem('reitemsales', JSON.stringify(reitemsales));
            loadItems();
            return;
        }
    });

    $(document).on('click', '.edit', function () {
        var row = $(this).closest('tr');
        var row_id = row.attr('id');
        item_id = row.attr('data-item-id');
        item = reitemsales[item_id];
        var qty = row.children().children('.rquantity').val(),
            product_option = row.children().children('.roption').val(),
            unit_price = formatDecimal(row.children().children('.ruprice').val()),
            discount = row.children().children('.rdiscount').val();
        if (item.options !== false) {
            $.each(item.options, function () {
                if (this.id == item.row.option && this.price != 0 && this.price != '' && this.price != null) {
                    unit_price = parseFloat(item.row.real_unit_price) + parseFloat(this.price);
                }
            });
        }
        var real_unit_price = item.row.real_unit_price;
        var net_price = unit_price;
        $('#prModalLabel').text(item.row.name + ' (' + item.row.code + ')');
        if (site.settings.tax1) {
            $('#ptax').select2('val', item.row.tax_rate);
            $('#old_tax').val(item.row.tax_rate);
            var item_discount = 0,
                ds = discount ? discount : '0';
            if (ds.indexOf('%') !== -1) {
                var pds = ds.split('%');
                if (!isNaN(pds[0])) {
                    item_discount = formatDecimal(parseFloat((unit_price * parseFloat(pds[0])) / 100), 4);
                } else {
                    item_discount = parseFloat(ds);
                }
            } else {
                item_discount = parseFloat(ds);
            }
            net_price -= item_discount;
            var pr_tax = item.row.tax_rate,
                pr_tax_val = 0;
            if (pr_tax !== null && pr_tax != 0) {
                $.each(tax_rates, function () {
                    if (this.id == pr_tax) {
                        if (this.type == 1) {
                            if (reitemsales[item_id].row.tax_method == 0) {
                                pr_tax_val = formatDecimal((net_price * parseFloat(this.rate)) / (100 + parseFloat(this.rate)), 4);
                                pr_tax_rate = formatDecimal(this.rate) + '%';
                                net_price -= pr_tax_val;
                            } else {
                                pr_tax_val = formatDecimal((net_price * parseFloat(this.rate)) / 100, 4);
                                pr_tax_rate = formatDecimal(this.rate) + '%';
                            }
                        } else if (this.type == 2) {
                            pr_tax_val = parseFloat(this.rate);
                            pr_tax_rate = this.rate;
                        }
                    }
                });
            }
        }
        if (site.settings.product_serial !== 0) {
            $('#pserial').val(row.children().children('.rserial').val());
        }
        var opt = '<p style="margin: 12px 0 0 0;">n/a</p>';
        if (item.options !== false) {
            var o = 1;
            opt = $('<select id="poption" name="poption" class="form-control select" />');
            $.each(item.options, function () {
                if (o == 1) {
                    if (product_option == '') {
                        product_variant = this.id;
                    } else {
                        product_variant = product_option;
                    }
                }
                $('<option />', { value: this.id, text: this.name }).appendTo(opt);
                o++;
            });
        } else {
            product_variant = 0;
        }

        uopt = '<p style="margin: 12px 0 0 0;">n/a</p>';
        if (item.units) {
            uopt = $('<select id="punit" name="punit" class="form-control select" />');
            $.each(item.units, function () {
                if (this.id == item.row.unit) {
                    $('<option />', { value: this.id, text: this.name, selected: true }).appendTo(uopt);
                } else {
                    $('<option />', { value: this.id, text: this.name }).appendTo(uopt);
                }
            });
        }

        $('#poptions-div').html(opt);
        $('#punits-div').html(uopt);
        $('select.select').select2({ minimumResultsForSearch: 7 });
        $('#pquantity').val(qty);
        $('#old_qty').val(qty);
        $('#pprice').val(unit_price);
        $('#punit_price').val(formatDecimal(parseFloat(unit_price) + parseFloat(pr_tax_val)));
        $('#poption').select2('val', item.row.option);
        $('#old_price').val(unit_price);
        $('#row_id').val(row_id);
        $('#item_id').val(item_id);
        $('#pserial').val(row.children().children('.rserial').val());
        $('#pdiscount').val(discount);
        $('#net_price').text(formatMoney(net_price));
        $('#pro_tax').text(formatMoney(pr_tax_val));
        $('#prModal').appendTo('body').modal('show');
    });

    $('#prModal').on('shown.bs.modal', function (e) {
        if ($('#poption').select2('val') != '') {
            $('#poption').select2('val', product_variant);
            product_variant = 0;
        }
    });

    $(document).on('change', '#pprice, #ptax, #pdiscount', function () {
        var row = $('#' + $('#row_id').val());
        var item_id = row.attr('data-item-id');
        var unit_price = parseFloat($('#pprice').val());
        var item = reitemsales[item_id];
        var ds = $('#pdiscount').val() ? $('#pdiscount').val() : '0';
        if (ds.indexOf('%') !== -1) {
            var pds = ds.split('%');
            if (!isNaN(pds[0])) {
                item_discount = parseFloat((unit_price * parseFloat(pds[0])) / 100);
            } else {
                item_discount = parseFloat(ds);
            }
        } else {
            item_discount = parseFloat(ds);
        }
        unit_price -= item_discount;
        var pr_tax = $('#ptax').val(),
            item_tax_method = item.row.tax_method;
        var pr_tax_val = 0,
            pr_tax_rate = 0;
        if (pr_tax !== null && pr_tax != 0) {
            $.each(tax_rates, function () {
                if (this.id == pr_tax) {
                    if (this.type == 1) {
                        if (item_tax_method == 0) {
                            pr_tax_val = formatDecimal((unit_price * parseFloat(this.rate)) / (100 + parseFloat(this.rate)), 4);
                            pr_tax_rate = formatDecimal(this.rate) + '%';
                            unit_price -= pr_tax_val;
                        } else {
                            pr_tax_val = formatDecimal((unit_price * parseFloat(this.rate)) / 100, 4);
                            pr_tax_rate = formatDecimal(this.rate) + '%';
                        }
                    } else if (this.type == 2) {
                        pr_tax_val = parseFloat(this.rate);
                        pr_tax_rate = this.rate;
                    }
                }
            });
        }

        $('#net_price').text(formatMoney(unit_price));
        $('#pro_tax').text(formatMoney(pr_tax_val));
    });

    $(document).on('change', '#punit', function () {
        var row = $('#' + $('#row_id').val());
        var item_id = row.attr('data-item-id');
        var item = reitemsales[item_id];
        if (!is_numeric($('#pquantity').val()) || parseFloat($('#pquantity').val()) < 0) {
            $(this).val(old_row_qty);
            bootbox.alert(lang.unexpected_value);
            return;
        }
        var opt = $('#poption').val(),
            unit = $('#punit').val(),
            base_quantity = $('#pquantity').val(),
            aprice = 0;
        if (item.options !== false) {
            $.each(item.options, function () {
                if (this.id == opt && this.price != 0 && this.price != '' && this.price != null) {
                    aprice = parseFloat(this.price);
                }
            });
        }
        if (item.units && unit != reitemsales[item_id].row.base_unit) {
            $.each(item.units, function () {
                if (this.id == unit) {
                    base_quantity = unitToBaseQty($('#pquantity').val(), this);
                    $('#pprice')
                        .val(formatDecimal(parseFloat(item.row.base_unit_price + aprice) * unitToBaseQty(1, this), 4))
                        .change();
                }
            });
        } else {
            $('#pprice')
                .val(formatDecimal(item.row.base_unit_price + aprice))
                .change();
        }
    });

    /* -----------------------
     * Edit Row Method
     ----------------------- */
    $(document).on('click', '#editItem', function () {
        var row = $('#' + $('#row_id').val());
        var item_id = row.attr('data-item-id'),
            new_pr_tax = $('#ptax').val(),
            new_pr_tax_rate = false;
        if (new_pr_tax) {
            $.each(tax_rates, function () {
                if (this.id == new_pr_tax) {
                    new_pr_tax_rate = this;
                }
            });
        }
        var price = parseFloat($('#pprice').val());
        var unit = $('#punit').val();
        var base_quantity = parseFloat($('#pquantity').val());
        if (unit != reitemsales[item_id].row.base_unit) {
            $.each(reitemsales[item_id].units, function () {
                if (this.id == unit) {
                    base_quantity = unitToBaseQty($('#pquantity').val(), this);
                }
            });
        }
        if (item.options !== false) {
            var opt = $('#poption').val();
            $.each(item.options, function () {
                if (this.id == opt && this.price != 0 && this.price != '' && this.price != null) {
                    price = price - parseFloat(this.price);
                    // price = price - parseFloat(this.price) * parseFloat(base_quantity);
                }
            });
        }
        if (site.settings.product_discount == 1 && $('#pdiscount').val()) {
            if (!is_valid_discount($('#pdiscount').val()) || ($('#pdiscount').val() != 0 && $('#pdiscount').val() > price)) {
                bootbox.alert(lang.unexpected_value);
                return false;
            }
        }
        var discount = $('#pdiscount').val() ? $('#pdiscount').val() : '';
        if (!is_numeric($('#pquantity').val())) {
            $(this).val(old_row_qty);
            bootbox.alert(lang.unexpected_value);
            return;
        }
        var quantity = parseFloat($('#pquantity').val());
        // if (site.settings.product_discount == 1 && $('#padiscount').val()) {
        //     if (!is_numeric($('#padiscount').val()) || $('#padiscount').val() > price * quantity) {
        //         bootbox.alert(lang.unexpected_value);
        //         return false;
        //     }
        //     discount = formatDecimal(parseFloat($('#padiscount').val()) / quantity, 4);
        // }
        // console.log(discount);

        reitemsales[item_id].row.fup = 1;
        reitemsales[item_id].row.qty = quantity;
        reitemsales[item_id].row.base_quantity = parseFloat(base_quantity);
        reitemsales[item_id].row.real_unit_price = price;
        reitemsales[item_id].row.unit = unit;
        reitemsales[item_id].row.tax_rate = new_pr_tax;
        reitemsales[item_id].tax_rate = new_pr_tax_rate;
        reitemsales[item_id].row.discount = discount;
        reitemsales[item_id].row.option = $('#poption').val() ? $('#poption').val() : '';
        reitemsales[item_id].row.serial = $('#pserial').val();
        localStorage.setItem('reitemsales', JSON.stringify(reitemsales));
        $('#prModal').modal('hide');

        loadItems();
        return;
    });
    $(document).on('change', '#padiscount', function () {
        if (site.settings.product_discount == 1 && $(this).val()) {
            var row = $('#' + $('#row_id').val());
            var item_id = row.attr('data-item-id'),
                new_pr_tax = $('#ptax').val(),
                new_pr_tax_rate = false;
            var item = reitemsales[item_id];
            if (new_pr_tax) {
                $.each(tax_rates, function () {
                    if (this.id == new_pr_tax) {
                        new_pr_tax_rate = this;
                    }
                });
            }
            var quantity = parseFloat($('#pquantity').val());
            var price = parseFloat($('#pprice').val());
            var pr_tax = new_pr_tax_rate;
            var pr_tax_val = 0,
                pr_tax_rate = 0;
            var total_tax = 0;
            if (site.settings.tax1 == 1) {
                if (pr_tax !== false && pr_tax != 0 && pr_tax.rate != 0) {
                    if (pr_tax.type == 1) {
                        if (item.row.tax_method == 0) {
                            pr_tax_val = formatDecimal((price * parseFloat(pr_tax.rate)) / (100 + parseFloat(pr_tax.rate)), 4);
                            price = formatDecimal(price - parseFloat(pr_tax_val), 4);
                        } else {
                            pr_tax_val = formatDecimal((price * parseFloat(pr_tax.rate)) / 100, 4);
                            price = formatDecimal(price + parseFloat(pr_tax_val), 4);
                        }
                    } else if (pr_tax.type == 2) {
                        price =
                            item.row.tax_method == 0
                                ? formatDecimal(price - parseFloat(pr_tax.rate), 4)
                                : formatDecimal(price + parseFloat(pr_tax.rate), 4);
                    }
                }
            }
            var total = formatDecimal((price + parseFloat(pr_tax_val)) * quantity, 4);
            var expected_total = parseFloat($(this).val());
            var expected_discount = formatDecimal(((total - expected_total) / total) * 100, 4);
            $('#pdiscount').val(expected_discount + '%');
        }
    });

    /* -----------------------
     * Product option change
     ----------------------- */
    $(document).on('change', '#poption', function () {
        var row = $('#' + $('#row_id').val()),
            opt = $(this).val();
        var item_id = row.attr('data-item-id');
        var item = reitemsales[item_id];
        var unit = $('#punit').val(),
            base_quantity = parseFloat($('#pquantity').val()),
            base_unit_price = item.row.base_unit_price;
        if (unit != reitemsales[item_id].row.base_unit) {
            $.each(reitemsales[item_id].units, function () {
                if (this.id == unit) {
                    base_unit_price = formatDecimal(parseFloat(item.row.base_unit_price) * unitToBaseQty(1, this), 4);
                    base_quantity = unitToBaseQty($('#pquantity').val(), this);
                }
            });
        }
        $('#pprice').val(parseFloat(base_unit_price)).trigger('change');
        if (item.options !== false) {
            $.each(item.options, function () {
                if (this.id == opt && this.price != 0 && this.price != '' && this.price != null) {
                    $('#pprice')
                        .val(parseFloat(base_unit_price) + parseFloat(this.price))
                        .trigger('change');
                    // .val(parseFloat(base_unit_price) + parseFloat(this.price) * parseFloat(base_quantity))
                }
            });
        }
    });

    /* ------------------------------
     * Show manual item addition modal
     ------------------------------- */
    $(document).on('click', '#addManually', function (e) {
        if (count == 1) {
            reitemsales = {};
        }
        $('#mnet_price').text('0.00');
        $('#mpro_tax').text('0.00');
        $('#mModal').appendTo('body').modal('show');
        return false;
    });

    $(document).on('click', '#addItemManually', function (e) {
        var mid = new Date().getTime(),
            mcode = $('#mcode').val(),
            mname = $('#mname').val(),
            mtax = parseInt($('#mtax').val()),
            munit = parseInt($('#munit').val()),
            mqty = parseFloat($('#mquantity').val()),
            mdiscount = $('#mdiscount').val() ? $('#mdiscount').val() : '0',
            unit_price = parseFloat($('#mprice').val()),
            mtax_rate = {};
        if (mcode && mname && mqty && unit_price) {
            $.each(tax_rates, function () {
                if (this.id == mtax) {
                    mtax_rate = this;
                }
            });

            reitemsales[mid] = {
                id: mid,
                item_id: mid,
                label: mname + ' (' + mcode + ')',
                row: {
                    id: mid,
                    code: mcode,
                    name: mname,
                    quantity: mqty,
                    base_quantity: mqty,
                    price: unit_price,
                    unit_price: unit_price,
                    real_unit_price: unit_price,
                    tax_rate: mtax,
                    unit: munit,
                    tax_method: 0,
                    qty: mqty,
                    type: 'manual',
                    discount: mdiscount,
                    serial: '',
                    option: '',
                },
                tax_rate: mtax_rate,
                units: false,
                options: false,
            };
            localStorage.setItem('reitemsales', JSON.stringify(reitemsales));
            loadItems();
        }
        $('#mModal').modal('hide');
        $('#mcode').val('');
        $('#mname').val('');
        $('#mtax').val('');
        $('#munit').val('');
        $('#mquantity').val('');
        $('#mdiscount').val('');
        $('#mprice').val('');
        return false;
    });

    $(document).on('change', '#mprice, #mtax, #mdiscount', function () {
        var unit_price = parseFloat($('#mprice').val());
        var ds = $('#mdiscount').val() ? $('#mdiscount').val() : '0';
        if (ds.indexOf('%') !== -1) {
            var pds = ds.split('%');
            if (!isNaN(pds[0])) {
                item_discount = parseFloat((unit_price * parseFloat(pds[0])) / 100);
            } else {
                item_discount = parseFloat(ds);
            }
        } else {
            item_discount = parseFloat(ds);
        }
        unit_price -= item_discount;
        var pr_tax = $('#mtax').val(),
            item_tax_method = 0;
        var pr_tax_val = 0,
            pr_tax_rate = 0;
        if (pr_tax !== null && pr_tax != 0) {
            $.each(tax_rates, function () {
                if (this.id == pr_tax) {
                    if (this.type == 1) {
                        if (item_tax_method == 0) {
                            pr_tax_val = formatDecimal((unit_price * parseFloat(this.rate)) / (100 + parseFloat(this.rate)), 4);
                            pr_tax_rate = formatDecimal(this.rate) + '%';
                            unit_price -= pr_tax_val;
                        } else {
                            pr_tax_val = formatDecimal((unit_price * parseFloat(this.rate)) / 100, 4);
                            pr_tax_rate = formatDecimal(this.rate) + '%';
                        }
                    } else if (this.type == 2) {
                        pr_tax_val = parseFloat(this.rate);
                        pr_tax_rate = this.rate;
                    }
                }
            });
        }

        $('#mnet_price').text(formatMoney(unit_price));
        $('#mpro_tax').text(formatMoney(pr_tax_val));
    });

    /* --------------------------
     * Edit Row Quantity Method
     --------------------------- */
    var old_row_qty;
    $(document)
        .on('focus', '.rquantity', function () {
            old_row_qty = $(this).val();
        })
        .on('change', '.rquantity', function () {
            var row = $(this).closest('tr');
            if (!is_numeric($(this).val()) || parseFloat($(this).val()) < 0) {
                $(this).val(old_row_qty);
                bootbox.alert(lang.unexpected_value);
                return;
            }
            var new_qty = parseFloat($(this).val()),
                item_id = row.attr('data-item-id');
            reitemsales[item_id].row.base_quantity = new_qty;
            if (reitemsales[item_id].row.unit != reitemsales[item_id].row.base_unit) {
                $.each(reitemsales[item_id].units, function () {
                    if (this.id == reitemsales[item_id].row.unit) {
                        reitemsales[item_id].row.base_quantity = unitToBaseQty(new_qty, this);
                    }
                });
            }
            reitemsales[item_id].row.qty = new_qty;
            localStorage.setItem('reitemsales', JSON.stringify(reitemsales));
            loadItems();
        });

    /* --------------------------
     * Edit Row Price Method
     -------------------------- */
    var old_price;
    $(document)
        .on('focus', '.rprice', function () {
            old_price = $(this).val();
        })
        .on('change', '.rprice', function () {
            var row = $(this).closest('tr');
            if (!is_numeric($(this).val())) {
                $(this).val(old_price);
                bootbox.alert(lang.unexpected_value);
                return;
            }
            var new_price = parseFloat($(this).val()),
                item_id = row.attr('data-item-id');
            reitemsales[item_id].row.price = new_price;
            localStorage.setItem('reitemsales', JSON.stringify(reitemsales));
            loadItems();
        });
});

//localStorage.clear();
function loadItems() {
    if (localStorage.getItem('reitemsales')) {
        total = 0;
        count = 1;
        an = 1;
        product_tax = 0;
        invoice_tax = 0;
        product_discount = 0;
        order_discount = 0;
        total_discount = 0;

        $('#reTable tbody').empty();
        reitemsales = JSON.parse(localStorage.getItem('reitemsales'));
        sortedItems =
            site.settings.item_addition == 1
                ? _.sortBy(reitemsales, function (o) {
                      return [parseInt(o.order)];
                  })
                : reitemsales;
        $('#add_sale, #edit_sale').attr('disabled', false);
        $.each(sortedItems, function () {
            var item = this;
            var item_id = site.settings.item_addition == 1 ? item.item_id : item.id;
            item.order = item.order ? item.order : new Date().getTime();
            var product_id = item.row.id,
                item_type = item.row.type,
                combo_items = item.combo_items,
                item_price = item.row.price,
                item_qty = item.row.qty,
                item_aqty = item.row.quantity,
                item_tax_method = item.row.tax_method,
                item_ds = item.row.discount,
                item_discount = 0,
                item_option = item.row.option,
                item_code = item.row.code,
                item_serial = item.row.serial,
                item_name = item.row.name.replace(/"/g, '&#034;').replace(/'/g, '&#039;');
            var product_unit = item.row.unit,
                base_quantity = item.row.base_quantity;

             var cost_price  = item.row.net_cost;  
             var batch_no    = item.row.batch_no;
             var bonus       = item.row.bonus;
             var expiry       = item.row.expiry;
             var discount1       = item.row.discount1;
             var discount2       = item.row.discount2;
             
            var unit_price = item.row.real_unit_price;
            if (item.units && item.row.fup != 1 && product_unit != item.row.base_unit) {
                $.each(item.units, function () {
                    if (this.id == product_unit) {
                        base_quantity = formatDecimal(unitToBaseQty(item.row.qty, this), 4);
                        unit_price = formatDecimal(parseFloat(item.row.base_unit_price) * unitToBaseQty(1, this), 4);
                    }
                });
            }
            var sel_opt = '';
            if (item.options !== false) {
                $.each(item.options, function () {
                    if (this.id == item_option) {
                        sel_opt = this.name;
                        if (this.price != 0 && this.price != '' && this.price != null) {
                            // item_price = unit_price + parseFloat(this.price) * parseFloat(base_quantity);
                            item_price = parseFloat(unit_price) + parseFloat(this.price);
                            unit_price = item_price;
                        }
                    }
                });
            }

            var ds = item_ds ? item_ds : '0';
            if (ds.indexOf('%') !== -1) {
                var pds = ds.split('%');
                if (!isNaN(pds[0])) {
                    item_discount = formatDecimal((unit_price * parseFloat(pds[0])) / 100, 4);
                } else {
                    item_discount = formatDecimal(ds);
                }
            } else {
                item_discount = formatDecimal(ds);
            }
            product_discount += formatDecimal(item_discount * item_qty, 4);

            unit_price = formatDecimal(unit_price - item_discount);
            var pr_tax = item.tax_rate;
            var pr_tax_val = 0,
                pr_tax_rate = 0;
            if (site.settings.tax1 == 1) {
                if (pr_tax !== false && pr_tax != 0) {
                    if (pr_tax.type == 1) {
                        if (item_tax_method == '0') {
                            pr_tax_val = formatDecimal((unit_price * parseFloat(pr_tax.rate)) / (100 + parseFloat(pr_tax.rate)), 4);
                            pr_tax_rate = formatDecimal(pr_tax.rate) + '%';
                        } else {
                            pr_tax_val = formatDecimal((unit_price * parseFloat(pr_tax.rate)) / 100, 4);
                            pr_tax_rate = formatDecimal(pr_tax.rate) + '%';
                        }
                    } else if (pr_tax.type == 2) {
                        pr_tax_val = parseFloat(pr_tax.rate);
                        pr_tax_rate = pr_tax.rate;
                    }
                }
            }
            pr_tax_val = formatDecimal(pr_tax_val);
            product_tax += formatDecimal(pr_tax_val * item_qty);
            item_price = item_tax_method == 0 ? formatDecimal(unit_price - pr_tax_val, 4) : formatDecimal(unit_price);
            unit_price = formatDecimal(unit_price + item_discount, 4);

            var row_no = item.id;
            var newTr = $('<tr id="row_' + row_no + '" class="row_' + item_id + '" data-item-id="' + item_id + '"></tr>');
            tr_html =
                '<td><input name="product_id[]" type="hidden" class="rid" value="' +
                product_id +
                '"><input name="product_type[]" type="hidden" class="rtype" value="' +
                item_type +
                '"><input name="product_code[]" type="hidden" class="rcode" value="' +
                item_code +
                '"><input name="product_name[]" type="hidden" class="rname" value="' +
                item_name +
                '"><input name="product_option[]" type="hidden" class="roption" value="' +
                item_option +
                '"><span class="sname" id="name_' +
                row_no +
                '">' +
                item_code +
                ' - ' +
                item_name +
                (sel_opt != '' ? ' (' + sel_opt + ')' : '') +
                '</span> <i class="pull-right fa fa-edit tip pointer edit" id="' +
                row_no +
                '" data-item="' +
                item_id +
                '" title="Edit" style="cursor:pointer;"></i></td>';

                tr_html +=
                '<td class="text-right"><input class="form-control input-sm text-right rprice" name="net_sale_price[]" type="text" id="net_sale_price_' +
                row_no +
                '" value="' +
                item_price +
                '"></td>';

                tr_html +=
                '<td class="text-right"><input class="form-control input-sm text-right rprice" name="cost_price[]" type="text" id="cost_price_' +
                row_no +
                '" value="' +
                cost_price +
                '"></td>';

                tr_html +=
                '<td class="text-right"><input class="form-control input-sm text-right rprice" name="batch_no[]" type="text" id="batch_no_' +
                row_no +
                '" value="' +
                batch_no +
                '"></td>';

                tr_html +=
                '<td><input class="form-control date rexpiry" name="expiry[]" type="text" value="' +
                expiry +
                '" data-id="' +
                row_no +
                '" data-item="' +
                item_id +
                '" id="expiry_' +
                row_no +
                '"></td>';

                


            // if (site.settings.product_serial == 1) {
            //     tr_html +=
            //         '<td class="text-right"><input class="form-control input-sm rserial" name="serial[]" type="text" id="serial_' +
            //         row_no +
            //         '" value="' +
            //         item_serial +
            //         '"></td>';
            // }



         


            tr_html +=
                '<td><input class="form-control text-center rquantity" tabindex="' +
                (site.settings.set_focus == 1 ? an : an + 1) +
                '" name="quantity[]" type="text" value="' +
                formatQuantity2(item_qty) +
                '" data-id="' +
                row_no +
                '" data-item="' +
                item_id +
                '" id="quantity_' +
                row_no +
                '" onClick="this.select();"><input name="product_unit[]" type="hidden" class="runit" value="' +
                product_unit +
                '"><input name="product_base_quantity[]" type="hidden" class="rbase_quantity" value="' +
                base_quantity +
                '"></td>';

                tr_html +=
                '<td class="text-right"><input class="form-control input-sm text-right rprice" name="bonus[]" type="text" id="bonus_' +
                row_no +
                '" value="' +
                bonus +
                '"></td>';

               
                    tr_html +=
                        '<td class="text-right"><input class="form-control input-sm rdiscount" name="product_discount[]" type="text" id="discount_' +
                        row_no +
                        '" value="' +
                        discount1 +
                        '"></td>';
            

            
                tr_html +=
                    '<td class="text-right"><input class="form-control input-sm rdiscount" name="product_discount[]" type="text" id="discount2_' +
                    row_no +
                    '" value="' +
                    discount2 +
                    '"></td>';
            

            // <span class="text-right sdiscount text-danger" id="sdiscount_' +
            // row_no +
            // '">' +
            // formatMoney(0 - item_discount * item_qty) +
            // '</span>



            if (site.settings.tax1 == 1) {
                tr_html +=
                    '<td class="text-right"><input class="form-control input-sm text-right rproduct_tax" name="product_tax[]" type="hidden" id="product_tax_' +
                    row_no +
                    '" value="' +
                    pr_tax.id +
                    '"><span class="text-right sproduct_tax" id="sproduct_tax_' +
                    row_no +
                    '">' +
                    (parseFloat(pr_tax_rate) != 0 ? '(' + formatDecimal(pr_tax_rate) + ')' : '') +
                    ' ' +
                    formatMoney(pr_tax_val * item_qty) +
                    '</span></td>';
            }

            tr_html +=
            '<td class="text-right"><input class="form-control input-sm text-right rprice" name="cost_price[]" type="hidden" id="cost_price_' +
            row_no +
            '" value="' +
            item_price +
            '"><span class="text-right sprice" id="sprice_' +
            row_no +
            '">' +
            formatMoney(cost_price) +
            '</span></td>';


            tr_html +=
            '<td class="text-right"><input class="form-control input-sm text-right rprice" name="net_price[]" type="hidden" id="price1_' +
            row_no +
            '" value="' +
            item_price +
            '"><input class="ruprice" name="unit_price1[]" type="hidden" value="' +
            unit_price +
            '"><input class="realuprice" name="real_unit_price1[]" type="hidden" value="' +
            item.row.real_unit_price +
            '"><span class="text-right sprice" id="sprice_' +
            row_no +
            '">' +
            formatMoney(item_price) +
            '</span></td>';

            tr_html +=
                '<td class="text-right"><span class="text-right ssubtotal" id="subtotal_' +
                row_no +
                '">' +
                formatMoney((parseFloat(item_price) + parseFloat(pr_tax_val)) * parseFloat(item_qty)) +
                '</span></td>';

                tr_html +=
                '<td class="text-right"><input class="form-control input-sm text-right rprice" name="net_price[]" type="hidden" id="price2_' +
                row_no +
                '" value="' +
                item_price +
                '"><input class="ruprice" name="unit_price2[]" type="hidden" value="' +
                unit_price +
                '"><input class="realuprice" name="real_unit_price2[]" type="hidden" value="' +
                item.row.real_unit_price +
                '"><span class="text-right sprice" id="sprice_' +
                row_no +
                '">' +
                formatMoney(item_price) +
                '</span></td>';

            tr_html +=
                '<td class="text-center"><i class="fa fa-times tip pointer redel" id="' +
                row_no +
                '" title="Remove" style="cursor:pointer;"></i></td>';
            newTr.html(tr_html);
            newTr.prependTo('#reTable');
            total += formatDecimal((parseFloat(item_price) + parseFloat(pr_tax_val)) * parseFloat(item_qty), 4);
            count += parseFloat(item_qty);
            an++;
        });

        var col = 2;
        if (site.settings.product_serial == 1) {
            col++;
        }
        var tfoot =
            '<tr id="tfoot" class="tfoot active"><th colspan="' +
            col +
            '">Total</th><th class="text-center">' +
            formatQty(parseFloat(count) - 1) +
            '</th>';
        if ((site.settings.product_discount == 1 && allow_discount == 1) || product_discount) {
            tfoot += '<th class="text-right">' + formatMoney(product_discount) + '</th>';
        }
        if (site.settings.tax1 == 1) {
            tfoot += '<th class="text-right">' + formatMoney(product_tax) + '</th>';
        }
        tfoot +=
            '<th class="text-right">' +
            formatMoney(total) +
            '</th><th class="text-center"><i class="fa fa-trash-o" style="opacity:0.5; filter:alpha(opacity=50);"></i></th></tr>';
        $('#reTable tfoot').html(tfoot);

        if ((rediscount = localStorage.getItem('rediscount'))) {
            var ds = rediscount;
            if (ds.indexOf('%') !== -1) {
                var pds = ds.split('%');
                if (!isNaN(pds[0])) {
                    order_discount = formatDecimal((total * parseFloat(pds[0])) / 100, 4);
                } else {
                    order_discount = formatDecimal(ds);
                }
            } else {
                order_discount = formatDecimal(ds);
            }
        }

        if (site.settings.tax2 != 0) {
            if ((retax2 = localStorage.getItem('retax2'))) {
                $.each(tax_rates, function () {
                    if (this.id == retax2) {
                        if (this.type == 2) {
                            invoice_tax = formatDecimal(this.rate);
                        } else if (this.type == 1) {
                            invoice_tax = formatDecimal(((total - order_discount) * this.rate) / 100, 4);
                        }
                    }
                });
            }
        }

        var shipping = parseFloat(localStorage.getItem('reshipping'));
        total_discount = parseFloat(order_discount + product_discount);
        var gtotal = parseFloat(total + invoice_tax + shipping - order_discount);
        $('#total').text(formatMoney(total));
        $('#titems').text(an - 1 + ' (' + formatQty(parseFloat(count) - 1) + ')');
        $('#total_items').val(parseFloat(count) - 1);
        $('#tds').text(formatMoney(order_discount));
        if (site.settings.tax2 != 0) {
            $('#ttax2').text(formatMoney(invoice_tax));
        }
        $('#gtotal').text(formatMoney(gtotal));
        if (an > parseInt(site.settings.bc_fix) && parseInt(site.settings.bc_fix) > 0) {
            $('html, body').animate({ scrollTop: $('#sticker').offset().top }, 500);
            $(window).scrollTop($(window).scrollTop() + 1);
        }
        set_page_focus();
    }
}

/* -----------------------------
 * Add Sale Order Item Function
 * @param {json} item
 * @returns {Boolean}
 ---------------------------- */
function add_return_item(item) {
    if (count == 1) {
        reitemsales = {};
    }
    if (item == null) return;

    var item_id = site.settings.item_addition == 1 ? item.item_id : item.id;
    if (reitemsales[item_id]) {
        var new_qty = parseFloat(reitemsales[item_id].row.qty) + 1;
        reitemsales[item_id].row.base_quantity = new_qty;
        if (reitemsales[item_id].row.unit != reitemsales[item_id].row.base_unit) {
            $.each(reitemsales[item_id].units, function () {
                if (this.id == reitemsales[item_id].row.unit) {
                    reitemsales[item_id].row.base_quantity = unitToBaseQty(new_qty, this);
                }
            });
        }
        reitemsales[item_id].row.qty = new_qty;
    } else {
        reitemsales[item_id] = item;
    }
    reitemsales[item_id].order = new Date().getTime();
    localStorage.setItem('reitemsales', JSON.stringify(reitemsales));
    loadItems();
    return true;
}

if (typeof Storage === 'undefined') {
    $(window).bind('beforeunload', function (e) {
        if (count > 1) {
            var message = 'You will loss data!';
            return message;
        }
    });
}

</script>


