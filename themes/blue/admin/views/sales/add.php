<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<style>
table#slTable td input.form-control {
    font-size: 10px !important;
    padding: 5px 2px !important;
}

.table td {
    height: 70px !important;
}
</style>
<script type="text/javascript">
    var count = 1, an = 1, product_variant = 0, DT = <?= $Settings->default_tax_rate ?>,
        product_tax = 0, invoice_tax = 0, product_discount = 0, slitems = {}, order_discount = 0, total_discount = 0, total = 0, allow_discount = <?= ($Owner || $Admin || $this->session->userdata('allow_discount')) ? 1 : 0; ?>,
        tax_rates = <?php echo json_encode($tax_rates); ?>;
    //var audio_success = new Audio('<?=$assets?>sounds/sound2.mp3');
    //var audio_error = new Audio('<?=$assets?>sounds/sound3.mp3');
    $(document).ready(function () {
        if (localStorage.getItem('remove_slls')) {
            if (localStorage.getItem('slitems')) {
                localStorage.removeItem('slitems');
            }
            if (localStorage.getItem('sldiscount')) {
                localStorage.removeItem('sldiscount');
            }
            if (localStorage.getItem('sltax2')) {
                localStorage.removeItem('sltax2');
            }
            if (localStorage.getItem('slref')) {
                localStorage.removeItem('slref');
            }
            if (localStorage.getItem('slshipping')) {
                localStorage.removeItem('slshipping');
            }
            if (localStorage.getItem('slwarehouse')) {
                localStorage.removeItem('slwarehouse');
            }
            if (localStorage.getItem('slnote')) {
                localStorage.removeItem('slnote');
            }
            if (localStorage.getItem('slinnote')) {
                localStorage.removeItem('slinnote');
            }
            if (localStorage.getItem('slcustomer')) {
                localStorage.removeItem('slcustomer');
            }
            if (localStorage.getItem('slbiller')) {
                localStorage.removeItem('slbiller');
            }
            if (localStorage.getItem('slcurrency')) {
                localStorage.removeItem('slcurrency');
            }
            if (localStorage.getItem('sldate')) {
                localStorage.removeItem('sldate');
            }
            if (localStorage.getItem('slsale_status')) {
                localStorage.removeItem('slsale_status');
            }
            if (localStorage.getItem('slpayment_status')) {
                localStorage.removeItem('slpayment_status');
            }
            if (localStorage.getItem('paid_by')) {
                localStorage.removeItem('paid_by');
            }
            if (localStorage.getItem('amount_1')) {
                localStorage.removeItem('amount_1');
            }
            if (localStorage.getItem('paid_by_1')) {
                localStorage.removeItem('paid_by_1');
            }
            if (localStorage.getItem('pcc_holder_1')) {
                localStorage.removeItem('pcc_holder_1');
            }
            if (localStorage.getItem('pcc_type_1')) {
                localStorage.removeItem('pcc_type_1');
            }
            if (localStorage.getItem('pcc_month_1')) {
                localStorage.removeItem('pcc_month_1');
            }
            if (localStorage.getItem('pcc_year_1')) {
                localStorage.removeItem('pcc_year_1');
            }
            if (localStorage.getItem('pcc_no_1')) {
                localStorage.removeItem('pcc_no_1');
            }
            if (localStorage.getItem('cheque_no_1')) {
                localStorage.removeItem('cheque_no_1');
            }
            if (localStorage.getItem('payment_note_1')) {
                localStorage.removeItem('payment_note_1');
            }
            if (localStorage.getItem('slpayment_term')) {
                localStorage.removeItem('slpayment_term');
            }
            localStorage.removeItem('remove_slls');
        }
        <?php if ($quote_id) {
    ?>
        // localStorage.setItem('sldate', '<?= $this->sma->hrld($quote->date) ?>');
        localStorage.setItem('slcustomer', '<?= $quote->customer_id ?>');
        localStorage.setItem('slbiller', '<?= $quote->biller_id ?>');
        localStorage.setItem('slwarehouse', '<?= $quote->warehouse_id ?>');
        localStorage.setItem('slnote', '<?= str_replace(["\r", "\n"], '', $this->sma->decode_html($quote->note)); ?>');
        localStorage.setItem('sldiscount', '<?= $quote->order_discount_id ?>');
        localStorage.setItem('sltax2', '<?= $quote->order_tax_id ?>');
        localStorage.setItem('slshipping', '<?= $quote->shipping ?>');
        localStorage.setItem('slitems', JSON.stringify(<?= $quote_items; ?>));
        <?php
} ?>
        <?php if ($this->input->get('customer')) {
        ?>
        if (!localStorage.getItem('slitems')) {
            localStorage.setItem('slcustomer', <?=$this->input->get('customer'); ?>);
        }
        <?php
    } ?>
        <?php if ($Owner || $Admin) {
        ?>
        if (!localStorage.getItem('sldate')) {
            $("#sldate").datetimepicker({
                format: site.dateFormats.js_ldate,
                fontAwesome: true,
                language: 'sma',
                weekStart: 1,
                todayBtn: 1,
                autoclose: 1,
                todayHighlight: 1,
                startView: 2,
                forceParse: 0
            }).datetimepicker('update', new Date());
        }
        $(document).on('change', '#sldate', function (e) {
            localStorage.setItem('sldate', $(this).val());
        });
        if (sldate = localStorage.getItem('sldate')) {
            $('#sldate').val(sldate);
        }
        <?php
    } ?>
        $(document).on('change', '#slbiller', function (e) {
            localStorage.setItem('slbiller', $(this).val());
        });
        if (slbiller = localStorage.getItem('slbiller')) {
            $('#slbiller').val(slbiller);
        }
        if (!localStorage.getItem('slref')) {
            localStorage.setItem('slref', '<?=$slnumber?>');
        }
        if (!localStorage.getItem('sltax2')) {
            localStorage.setItem('sltax2', <?=$Settings->default_tax_rate2;?>);
        }
        ItemnTotals();
        $('.bootbox').on('hidden.bs.modal', function (e) {
            $('#add_item').focus();
        });
        $("#add_item").autocomplete({
            source: function (request, response) {
                if (!$('#slcustomer').val()) {
                    $('#add_item').val('').removeClass('ui-autocomplete-loading');
                    bootbox.alert('<?=lang('select_above');?>');
                    $('#add_item').focus();
                    return false;
                }

                $.ajax({
                    type: 'get',
                    url: '<?= admin_url('sales/bch_suggestions'); ?>',
                    dataType: "json",
                    data: {
                        term: request.term,
                        warehouse_id: $("#slwarehouse").val(),
                        customer_id: $("#slcustomer").val()
                    },
                    success: function (data) {
                        if(data[0].id != 0){
                            $(this).removeClass('ui-autocomplete-loading');
                            response(data);
                        }else{
                            $.ajax({
                                type: 'get',
                                url: '<?=admin_url('products/get_items_by_avz_code');?>',
                                dataType: "json",
                                data: {
                                    term: request.term,
                                    warehouse_id: $("#slwarehouse").val(),
                                    customer_id: $("#slcustomer").val(),
                                    module: 'sales'
                                },
                                success: function (data) {
                                    $(this).removeClass('ui-autocomplete-loading');
                                    if(data){

                                        var avzItemCode = data[0].row.avz_item_code;
                                        var found = false;

                                        Object.keys(slitems).forEach(function (key) {
                                            if (slitems[key].row && slitems[key].row.avz_item_code === avzItemCode) {
                                                found = true;
                                            }
                                        });

                                        if(found == true){
                                            bootbox.alert('Row already exists for this code.');
                                        }else{
                                            add_invoice_item(data[0]);
                                        }
                                    }else{
                                        bootbox.alert('No records found for this item code.');
                                    }
                                    
                                }
                            });
                        }
                        
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
                    openPopup(ui.item);
                    //var row = add_invoice_item(ui.item);
                    //if (row)
                    //    $(this).val('');
                } else {
                    bootbox.alert('<?= lang('no_match_found') ?>');
                }
            }
        });
        $(document).on('change', '#gift_card_no', function () {
            var cn = $(this).val() ? $(this).val() : '';
            if (cn != '') {
                $.ajax({
                    type: "get", async: false,
                    url: site.base_url + "sales/validate_gift_card/" + cn,
                    dataType: "json",
                    success: function (data) {
                        if (data === false) {
                            $('#gift_card_no').parent('.form-group').addClass('has-error');
                            bootbox.alert('<?=lang('incorrect_gift_card')?>');
                        } else if (data.customer_id !== null && data.customer_id !== $('#slcustomer').val()) {
                            $('#gift_card_no').parent('.form-group').addClass('has-error');
                            bootbox.alert('<?=lang('gift_card_not_for_customer')?>');

                        } else {
                            $('#gc_details').html('<small>Card No: ' + data.card_no + '<br>Value: ' + data.value + ' - Balance: ' + data.balance + '</small>');
                            $('#gift_card_no').parent('.form-group').removeClass('has-error');
                        }
                    }
                });
            }
        }); 
    });

    function openPopup(selectedItem) {
        // Assuming selectedItem has avz_item_code as part of its data
        $.ajax({
            type: 'get',
            url: '<?= admin_url('products/get_avz_item_code_details'); ?>', // Adjust the URL as needed
            dataType: "json",
            data: {
                item_id: selectedItem.item_id, // Send the unique item code
                warehouse_id: $("#slwarehouse").val() // Optionally include warehouse ID if needed
            },
            success: function (data) {
                $(this).removeClass('ui-autocomplete-loading');

                // Populate the modal with the returned data
                if (data && data.length > 0) {
                    var modalBody = $('#itemModal .modal-body');
                    modalBody.empty();

                    // Loop through each item and create clickable entries in the modal
                    var table = `
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Avz Code</th>
                                    <th>Product</th>
                                    <th>Supplier</th>
                                    <th>Batch No</th>
                                    <th>Expiry</th>
                                    <th>Quantity</th>
                                    <th>Locked</th>
                                </tr>
                            </thead>
                            <tbody id="itemTableBody"></tbody>
                        </table>
                    `;

                    // Append the table to the modal body
                    modalBody.append(table);
                    
                    // Populate the table body with the data
                    var count = 0;
                    var toitemsStorageValue = JSON.parse(localStorage.getItem('slitems'));
                    data.forEach(function (item) {
                        count++;

                        var avzItemCode = item.row.avz_item_code;
                        var found = false;

                        Object.keys(slitems).forEach(function (key) {
                            if (slitems[key].row && slitems[key].row.avz_item_code === avzItemCode) {
                                found = true;
                            }
                        });

                        var tickOrCross = found ? '✔' : '✖';

                        var row = `
                            <tr style="cursor:pointer;" class="modal-item" tabindex="0" data-item-id="${item.row.avz_item_code}">
                                <td>${count}</td>
                                <td data-avzcode="${item.row.avz_item_code}">${item.row.avz_item_code}</td>
                                <td data-product="${item.row.name}">${item.row.name}</td>
                                <td data-supplier="${item.row.supplier}">${item.row.supplier}</td>
                                <td data-batchno="${item.row.batchno}">${item.row.batchno}</td>
                                <td data-expiry="${item.row.expiry}">${item.row.expiry}</td>
                                <td data-quantity="${item.total_quantity}">${item.total_quantity}</td>
                                <td>${tickOrCross}</td>
                            </tr>
                        `;
                        $('#itemTableBody').append(row);
                        $('#itemTableBody tr:last-child').data('available', found);
                    });

                    // Show the modal
                    $('#itemModal').modal('show');
                    $('#itemTableBody').on('click', 'tr', function () {
                        
                        var clickedItemCode = $(this).data('item-id');
                        var clickedItemExpiry = $(this).find('td[data-expiry]').data('expiry') || $(this).attr('data-expiry');
                        var selectedItem = data.find(function (item) {
                            //return item.row.avz_item_code === clickedItemCode;
                            return String(item.row.avz_item_code).trim() === String(clickedItemCode).trim();
                        });

                        var previousExpiryAvailable = data.find(function (item) {
                            
                            var itemExpiry = new Date(item.row.expiry); // Convert to Date object
                            var clickedExpiry = new Date(clickedItemExpiry); // Convert clickedItemExpiry to Date object
                            if(clickedExpiry > itemExpiry){
                                return true;
                            }
                        });

                        if(previousExpiryAvailable){
                            bootbox.alert('Previous Expiry available for this item');
                        }

                        if (selectedItem) {
                            $('#itemModal').modal('hide');
                            var available = $(this).data('available');
                            if(!available){
                                add_invoice_item(selectedItem);
                            }else{
                                bootbox.alert('Row already added');
                            }
                        }else{
                            console.log('Item not found');
                        }
                    });
                    
                } else {
                    bootbox.alert('No records found for this item code.');
                }
            },
            error: function (xhr, status, error) {
                console.error('AJAX error:', error);
                bootbox.alert('An error occurred while fetching the item details.');
            }
        });
    }

    function onSelectFromPopup(selectedRecord) {
        $('#itemModal').modal('hide');

        var row = add_invoice_item(selectedRecord);
        if (row) {
            // If the row was successfully added, you can do additional actions here
            
        }
    }
</script>

<div class="modal fade" id="itemModal" tabindex="-1" role="dialog" aria-labelledby="itemModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content" style="min-width:800px !important;">
            <div class="modal-header">
                <h5 class="modal-title" id="itemModalLabel">Select an Item</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <!-- The content will be dynamically generated here -->
            </div>
        </div>
    </div>
</div>


<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-plus"></i><?= lang('add_sale'); ?></h2>
        <!-- CSV upload icon -->
        <div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown">
                    <a href="<?= admin_url('Sales/showUploadSales'); ?>" data-toggle="modal"
                                        data-target="#myModal"><i class="icon fa fa-upload"></i></a></li>
            </ul>
        </div>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">

                <p class="introtext"><?php echo lang('enter_info'); ?></p>
                <?php
                $attrib = ['data-toggle' => 'validator', 'role' => 'form'];
                echo admin_form_open_multipart('sales/add', $attrib);
                if ($quote_id) {
                    echo form_hidden('quote_id', $quote_id);
                }
                ?>
                <div class="row">
                    <div class="col-lg-12">
                        <?php if ($Owner || $Admin) {
                    ?>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <?= lang('date', 'sldate'); ?>
                                    <?php echo form_input('date', ($_POST['date'] ?? ''), 'class="form-control input-tip datetime" id="sldate" required="required"'); ?>
                                </div>
                            </div>
                        <?php
                } ?>

                        <!--<div class="col-md-4">
                            <div class="form-group">
                                <?= lang('reference_no', 'slref'); ?>
                                <?php echo form_input('reference_no', ($_POST['reference_no'] ?? $slnumber), 'class="form-control input-tip" id="slref"'); ?>
                            </div>
                        </div>-->
                        <?php if ($Owner || $Admin || !$this->session->userdata('biller_id')) {
                    ?>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <?= lang('biller', 'slbiller'); ?>
                                    <?php
                                    $bl[''] = '';
                    foreach ($billers as $biller) {
                        $bl[$biller->id] = $biller->company && $biller->company != '-' ? $biller->company : $biller->name;
                    }
                    echo form_dropdown('biller', $bl, ($_POST['biller'] ?? $Settings->default_biller), 'id="slbiller" data-placeholder="' . lang('select') . ' ' . lang('biller') . '" required="required" class="form-control input-tip select" style="width:100%;"'); ?>
                                </div>
                            </div>
                        <?php
                } else {
                    $biller_input = [
                        'type'  => 'hidden',
                        'name'  => 'biller',
                        'id'    => 'slbiller',
                        'value' => $this->session->userdata('biller_id'),
                    ];

                    echo form_input($biller_input);
                } ?>

                        <div class="clearfix"></div>
                        <div class="col-md-12">
                            <div class="panel panel-warning">
                                <div
                                    class="panel-heading"><?= lang('please_select_these_before_adding_product') ?></div>
                                <div class="panel-body" style="padding: 5px;">
                                    <?php if ($Owner || $Admin || !$this->session->userdata('warehouse_id')) {
                    ?>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <?= lang('warehouse', 'slwarehouse'); ?>
                                                <?php
                                                $wh[''] = '';
                    foreach ($warehouses as $warehouse) {
                        $wh[$warehouse->id] = $warehouse->name.' ('.$warehouse->code.')';
                    }
                    echo form_dropdown('warehouse', $wh, ($_POST['warehouse'] ?? $Settings->default_warehouse), 'id="slwarehouse" class="form-control input-tip select" data-placeholder="' . lang('select') . ' ' . lang('warehouse') . '" required="required" style="width:100%;" '); ?>
                                            </div>
                                        </div>
                                    <?php
                } else {
                    $warehouse_input = [
                        'type'  => 'hidden',
                        'name'  => 'warehouse',
                        'id'    => 'slwarehouse',
                        'value' => $this->session->userdata('warehouse_id'),
                    ];

                    echo form_input($warehouse_input);
                } ?>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <?= lang('customer', 'slcustomer'); ?>
                                            <div class="input-group">
                                                <?php
                                                echo form_input('customer', ($_POST['customer'] ?? ''), 'id="slcustomer" data-placeholder="' . lang('select') . ' ' . lang('customer') . '" required="required" class="form-control input-tip" style="width:100%;"');
                                                ?>
                                                <div class="input-group-addon no-print" style="padding: 2px 8px; border-left: 0;">
                                                    <a href="#" id="toogle-customer-read-attr" class="external">
                                                        <i class="fa fa-pencil" id="addIcon" style="font-size: 1.2em;"></i>
                                                    </a>
                                                </div>
                                                <div class="input-group-addon no-print" style="padding: 2px 7px; border-left: 0;">
                                                    <a href="#" id="view-customer" class="external" data-toggle="modal" data-target="#myModal">
                                                        <i class="fa fa-eye" id="addIcon" style="font-size: 1.2em;"></i>
                                                    </a>
                                                </div>
                                                <?php if ($Owner || $Admin || $GP['customers-add']) {
                                                    ?>
                                                <div class="input-group-addon no-print" style="padding: 2px 8px;">
                                                    <a href="<?= admin_url('customers/add'); ?>" id="add-customer"class="external" data-toggle="modal" data-target="#myModal">
                                                        <i class="fa fa-plus-circle" id="addIcon"  style="font-size: 1.2em;"></i>
                                                    </a>
                                                </div>
                                                <?php
                                                } ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>


                        <div class="col-md-12" id="sticker">
                            <div class="well well-sm">
                                <div class="form-group" style="margin-bottom:0;">
                                    <div class="input-group wide-tip">
                                        <div class="input-group-addon" style="padding-left: 10px; padding-right: 10px;">
                                            <i class="fa fa-2x fa-barcode addIcon"></i></a></div>
                                        <?php echo form_input('add_item', '', 'class="form-control input-lg" id="add_item" placeholder="' . lang('add_product_to_order') . '"'); ?>
                                        <?php if ($Owner || $Admin || $GP['products-add']) {
                                                    ?>
                                        <div class="input-group-addon" style="padding-left: 10px; padding-right: 10px;">
                                            <a href="#" id="addManually" class="tip" title="<?= lang('add_product_manually') ?>">
                                                <i class="fa fa-2x fa-plus-circle addIcon" id="addIcon"></i>
                                            </a>
                                        </div>
                                        <?php
                                                } if ($Owner || $Admin || $GP['sales-add_gift_card']) {
                                                    ?>
                                        <div class="input-group-addon" style="padding-left: 10px; padding-right: 10px;">
                                            <a href="#" id="sellGiftCard" class="tip" title="<?= lang('sell_gift_card') ?>">
                                               <i class="fa fa-2x fa-credit-card addIcon" id="addIcon"></i>
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
                                    <table id="slTable" class="table items table-striped table-bordered table-condensed table-hover sortable_table">
                                        <thead>
                                        <tr>
                                            <th class="col-md-1">#</th>
                                            <th class="col-md-2">item name</th>
                                            <th class="col-md-1">sale price</th>
                                            <!-- <th class="col-md-1">purchase price</th> -->
                                            <!--<th class="col-md-1"><?= lang('Serial No.'); ?></th>-->
                                            <th class="col-md-1"><?= lang('Batch_No'); ?></th>
                                            <th class="col-md-1"><?= lang('Expiry Date'); ?></th>
                                            <!--<th class="col-md-1">
                                                <?php //lang('Lot_No'); ?>
                                            </th>-->
                                            <?php
                                            /*if ($Settings->product_serial) {
                                                echo '<th class="col-md-2">' . lang('serial_no') . '</th>';
                                            }*/
                                            ?>
                                            <!--<th class="col-md-1">-->
                                                <?php //lang('net_unit_price'); ?>
                                            <!--</th>-->
                                            <th class="col-md-1">qty</th>
                                            <th class="col-md-1">bonus</th>
                                            <th class="col-md-1">dis 1</th>
                                            <th class="col-md-1">dis 2</th>
                                            <th class="col-md-1">Vat 15%</th>
                                            <!--<th class="col-md-1">Total Purchases</th>-->
                                            <th class="col-md-1">Total Sales</th>
                                            <th class="col-md-1">Net Sales</th>
                                            <th class="col-md-1">Unit Sale</th>
                                            <?php
                                            //if ($Settings->product_discount && ($Owner || $Admin || $this->session->userdata('allow_discount'))) {
                                                //echo '<th class="col-md-1">' . lang('discount') . '</th>';
                                            //}
                                            ?>
                                            <?php
                                            //if ($Settings->tax1) {
                                            //    echo '<th class="col-md-1">' . lang('product_tax') . '</th>';
                                           // }
                                            ?>
                                            <!--<th>
                                                <?php //lang('subtotal'); ?>
                                                (<span class="currency"><?php //$default_currency->code ?></span>)
                                            </th>-->
                                            <!--<th class="col-md-1">Bonus</th>
                                            <th class="col-md-1">Dis 1</th>
                                            <th class="col-md-1">After Dis 1</th>
                                            <th class="col-md-1">Dis 2</th>
                                            <th class="col-md-1">Total b. Vat</th>
                                            <th class="col-md-1">Vat</th>
                                            <th class="col-md-1">Net</th>-->
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

                        <?php if ($Settings->tax2) {
                                                ?>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <?= lang('order_tax', 'sltax2'); ?>
                                    <?php
                                    $tr[''] = '';
                                                foreach ($tax_rates as $tax) {
                                                    $tr[$tax->id] = $tax->name;
                                                }
                                                echo form_dropdown('order_tax', $tr, ($_POST['order_tax'] ?? $Settings->default_tax_rate2), 'id="sltax2" data-placeholder="' . lang('select') . ' ' . lang('order_tax') . '" class="form-control input-tip select" style="width:100%;"'); ?>
                                </div>
                            </div>
                        <?php
                                            } ?>

                        <?php if ($Owner || $Admin || $this->session->userdata('allow_discount')) {
                                                ?>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <?= lang('order_discount', 'sldiscount'); ?>
                                    <?php echo form_input('order_discount', '', 'class="form-control input-tip" id="sldiscount"'); ?>
                                </div>
                            </div>
                        <?php

                         } ?>

                        <div class="col-md-4">
                            <div class="form-group">
                                <?= lang('shipping', 'slshipping'); ?>
                                <?php echo form_input('shipping', '', 'class="form-control input-tip" id="slshipping"'); ?>

                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <?= lang('attachments', 'document') ?>
                                <input id="document" type="file" data-browse-label="<?= lang('browse'); ?>" name="attachments[]" multiple data-show-upload="false" data-show-preview="false" class="form-control file">
                            </div>
                        </div>


                             <?php if ($Owner || $Admin || $GP['sales-coordinator']) { ?>
                                <?php echo form_input('sale_status', 'pending','hidden', 'class="form-control tip" data-trigger="focus" data-placement="top" title="' . lang('sale_status') . '" id="slsale_status"'); ?>
                             <?php  } ?>

                           <?php if ($Owner || $Admin || !$GP['sales-coordinator']) { ?>
                            
                             <div class="col-sm-4">
                                <div class="form-group">
                                <?= lang('sale_status', 'slsale_status'); ?>
                                <?php $sst = ['pending' => lang('pending')];
                                echo form_dropdown('sale_status', $sst, '', 'class="form-control input-tip" required="required" id="slsale_status"'); ?>
                                </div>
                            </div>
                            <?php  } ?>

                            <!-- 'completed' => lang('completed') -->


                           

                        <div class="col-sm-4">

                            <div class="form-group">
                                <?= lang('payment_term', 'payment_term'); ?>
                                <?php
                                $data[''] = '';
                                $data['30'] = '30';
                                $data['60'] = '60';
                                $data['90'] = '90';
                                $data['120'] = '120';
                                $data['>120'] = '>120';
                                echo form_dropdown('payment_term', $data, '', 'id="payment_term" data-placeholder="' . lang('select') . ' ' . lang('payment_term') . '" class="form-control input-tip select" style="width:100%;"'); ?>
                            </div>
                        </div>
                        <?php if ($Owner || $Admin || $GP['sales-payments']) {
                                    ?>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <?= lang('payment_status', 'slpayment_status'); ?>
                                <?php $pst = ['pending' => lang('pending'), 'due' => lang('due'), 'partial' => lang('partial'), 'paid' => lang('paid')];
                                    echo form_dropdown('payment_status', $pst, '', 'class="form-control input-tip" required="required" id="slpayment_status"'); ?>

                            </div>
                        </div>
                        <?php
                                } else {
                                    echo form_hidden('payment_status', 'pending');
                                }
                        ?>
                        <div class="clearfix"></div>

                        <div id="payments" style="display: none;">
                            <div class="col-md-12">
                                <div class="well well-sm well_1">
                                    <div class="col-md-12">
                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <?= lang('payment_reference_no', 'payment_reference_no'); ?>
                                                    <?= form_input('payment_reference_no', ($_POST['payment_reference_no'] ?? $payment_ref), 'class="form-control tip" id="payment_reference_no"'); ?>
                                                </div>
                                            </div>
                                            <div class="col-sm-4">
                                                <div class="payment">
                                                    <div class="form-group ngc">
                                                        <?= lang('amount', 'amount_1'); ?>
                                                        <input name="amount-paid" type="text" id="amount_1"
                                                               class="pa form-control kb-pad amount"/>
                                                    </div>
                                                    <div class="form-group gc" style="display: none;">
                                                        <?= lang('gift_card_no', 'gift_card_no'); ?>
                                                        <input name="gift_card_no" type="text" id="gift_card_no"
                                                               class="pa form-control kb-pad"/>

                                                        <div id="gc_details"></div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-sm-4">
                                                <div class="form-group">
                                                    <?= lang('paying_by', 'paid_by_1'); ?>
                                                    <select name="paid_by" id="paid_by_1" class="form-control paid_by">
                                                        <?= $this->sma->paid_opts(); ?>
                                                    </select>
                                                </div>
                                            </div>

                                        </div>
                                        <div class="clearfix"></div>
                                        <div class="pcc_1" style="display:none;">
                                            <div class="row">
                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        <input name="pcc_no" type="text" id="pcc_no_1"
                                                               class="form-control" placeholder="<?= lang('cc_no') ?>"/>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        <input name="pcc_holder" type="text" id="pcc_holder_1"
                                                               class="form-control"
                                                               placeholder="<?= lang('cc_holder') ?>"/>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        <select name="pcc_type" id="pcc_type_1"
                                                                class="form-control pcc_type"
                                                                placeholder="<?= lang('card_type') ?>">
                                                            <option value="Visa"><?= lang('Visa'); ?></option>
                                                            <option
                                                                value="MasterCard"><?= lang('MasterCard'); ?></option>
                                                            <option value="Amex"><?= lang('Amex'); ?></option>
                                                            <option value="Discover"><?= lang('Discover'); ?></option>
                                                        </select>
                                                        <!-- <input type="text" id="pcc_type_1" class="form-control" placeholder="<?= lang('card_type') ?>" />-->
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        <input name="pcc_month" type="text" id="pcc_month_1"
                                                               class="form-control" placeholder="<?= lang('month') ?>"/>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="form-group">

                                                        <input name="pcc_year" type="text" id="pcc_year_1"
                                                               class="form-control" placeholder="<?= lang('year') ?>"/>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="form-group">

                                                        <input name="pcc_ccv" type="text" id="pcc_cvv2_1"
                                                               class="form-control" placeholder="<?= lang('cvv2') ?>"/>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="pcheque_1" style="display:none;">
                                            <div class="form-group"><?= lang('cheque_no', 'cheque_no_1'); ?>
                                                <input name="cheque_no" type="text" id="cheque_no_1"
                                                       class="form-control cheque_no"/>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <?= lang('payment_note', 'payment_note_1'); ?>
                                            <textarea name="payment_note" id="payment_note_1"
                                                      class="pa form-control kb-text payment_note"></textarea>
                                        </div>
                                    </div>
                                    <div class="clearfix"></div>
                                </div>
                            </div>
                        </div>

                        <input type="hidden" name="total_items" value="" id="total_items" required="required"/>

                        <div class="row" id="bt">
                            <div class="col-md-12">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <?= lang('Note', 'warning_note'); ?>
                                        <span id="warning_message" style="color:red;font-size:12px;"></span>
                                        <input type="text" name="warning_note" class="form-control" value="" id="warning_note" style="margin-top: 10px; height: 100px;" />
                                    </div>
                                </div>
                                <!--<div class="col-md-6">
                                    <div class="form-group">
                                        <?= lang('sale_note', 'slnote'); ?>
                                        <?php //echo form_textarea('note', ($_POST['note'] ?? ''), 'class="form-control" id="slnote" required="required" style="margin-top: 10px; height: 100px;"'); ?>
                                        <input type="hidden" name="note" class="form-control" required="required" id="slnote" style="margin-top: 10px; height: 100px;" />
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <?= lang('staff_note', 'slinnote'); ?>
                                        <?php //echo form_textarea('staff_note', ($_POST['staff_note'] ?? ''), 'class="form-control" id="slinnote" style="margin-top: 10px; height: 100px;"'); ?>
                                        <input type="hidden" id="warning_note" name="warning_note" value=""  />
                                    </div>
                                </div>-->


                            </div>

                        </div>
                        <div class="col-md-12">
                            <?php
                            $data = array(
                                'name' => 'add_sale',
                                'onclick'=>"  return validate_confirm()"
                            );
                            ?>
                            <div
                                class="fprom-group"><?php echo form_submit($data, lang('submit'), 'id="add_sale" class="btn btn-primary" style="padding: 6px 15px; margin:15px 0;"'); ?>
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
                    <div class="form-group"><label for="msp" class="col-sm-4 control-label" style="padding-top:0px;">Minimum Sale price</label>
                            <div class="col-sm-8">
                                <span id="prmsp" style="color:red;font-size:18px;">n/a</span>
                            </div>
                    </div>    
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
                    <?php if ($Settings->product_discount && ($Owner || $Admin || $this->session->userdata('allow_discount'))) {
                            ?>
                        <div class="form-group">
                            <label for="psubt"
                                   class="col-sm-4 control-label"><?= lang('subtotal') ?></label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control" id="psubt" disabled="disabled">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="pdiscount"
                                   class="col-sm-4 control-label"><?= lang('adjust_subtotal') ?></label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control" id="padiscount" placeholder="<?= lang('nearest_subtotal'); ?>">
                            </div>
                        </div>
                    <?php
                        } ?>
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

<div class="modal" id="gcModal" tabindex="-1" role="dialog" aria-labelledby="mModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i
                        class="fa fa-2x">&times;</i></button>
                <h4 class="modal-title" id="myModalLabel"><?= lang('sell_gift_card'); ?></h4>
            </div>
            <div class="modal-body">
                <p><?= lang('enter_info'); ?></p>

                <div class="alert alert-danger gcerror-con" style="display: none;">
                    <button data-dismiss="alert" class="close" type="button">×</button>
                    <span id="gcerror"></span>
                </div>
                <div class="form-group">
                    <?= lang('card_no', 'gccard_no'); ?> *
                    <div class="input-group">
                        <?php echo form_input('gccard_no', '', 'class="form-control" id="gccard_no"'); ?>
                        <div class="input-group-addon" style="padding-left: 10px; padding-right: 10px;"><a href="#"
                                                                                                           id="genNo"><i
                                    class="fa fa-cogs"></i></a></div>
                    </div>
                </div>
                <input type="hidden" name="gcname" value="<?= lang('gift_card') ?>" id="gcname"/>

                <div class="form-group">
                    <?= lang('value', 'gcvalue'); ?> *
                    <?php echo form_input('gcvalue', '', 'class="form-control" id="gcvalue"'); ?>
                </div>
                <div class="form-group">
                    <?= lang('price', 'gcprice'); ?> *
                    <?php echo form_input('gcprice', '', 'class="form-control" id="gcprice"'); ?>
                </div>
                <div class="form-group">
                    <?= lang('customer', 'gccustomer'); ?>
                    <?php echo form_input('gccustomer', '', 'class="form-control" id="gccustomer"'); ?>
                </div>
                <div class="form-group">
                    <?= lang('expiry_date', 'gcexpiry'); ?>
                    <?php echo form_input('gcexpiry', $this->sma->hrsd(date('Y-m-d', strtotime('+2 year'))), 'class="form-control date" id="gcexpiry"'); ?>
                </div>

            </div>
            <div class="modal-footer">
                <button type="button" id="addGiftCard" class="btn btn-primary"><?= lang('sell_gift_card') ?></button>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
    $(document).ready(function () {
        $('#gccustomer').select2({
            minimumInputLength: 1,
            ajax: {
                url: site.base_url + "customers/suggestions",
                dataType: 'json',
                quietMillis: 15,
                data: function (term, page) {
                    return {
                        term: term,
                        limit: 10
                    };http://www.avenzur-pharma.com/admin/sales/edit/18
                },
                results: function (data, page) {
                    if (data.results != null) {
                        return {results: data.results};
                    } else {
                        return {results: [{id: '', text: 'No Match Found'}]};
                    }
                }
            }
        });
        $('#genNo').click(function () {
            var no = generateCardNo();
            $(this).parent().parent('.input-group').children('input').val(no);
            return false;
        });
    });
</script>
