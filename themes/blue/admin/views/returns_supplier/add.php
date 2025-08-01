<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<script type="text/javascript">
    var count = 1, an = 1, product_variant = 0, DT = <?= $Settings->default_tax_rate ?>,
        product_tax = 0, invoice_tax = 0, product_discount = 0, order_discount = 0, total_discount = 0, total = 0, allow_discount = <?= ($Owner || $Admin || $this->session->userdata('allow_discount')) ? 1 : 0; ?>,
        tax_rates = <?php echo json_encode($tax_rates); ?>;
    var rseitems = {};

    <?php if ($inv) {
    ?>
        //localStorage.setItem('redate', '<?= $this->sma->hrld($inv->date) ?>');
        localStorage.setItem('rseref', '<?= $reference ?>');
        localStorage.setItem('rsenote', '<?= $this->sma->decode_html($inv->note); ?>');
        localStorage.setItem('rseitems', JSON.stringify(<?= $inv_items; ?>));
        localStorage.setItem('rsediscount', '<?= $inv->order_discount_id ?>');
        localStorage.setItem('rsetax2', '<?= $inv->order_tax_id ?>');
        localStorage.setItem('return_surcharge', '0');
        <?php
    } ?>

    <?php if ($this->session->userdata('remove_rlls')) {
    ?>
        if (localStorage.getItem('rseitems')) {
            localStorage.removeItem('rseitems');
        }

        if (localStorage.getItem('rsediscount')) {
                localStorage.removeItem('rsediscount');
            }
            if (localStorage.getItem('rseshipping')) {
                localStorage.removeItem('rseshipping');
            }
            if (localStorage.getItem('rsetax2')) {
                localStorage.removeItem('rsetax2');
            }
            if (localStorage.getItem('rseref')) {
                localStorage.removeItem('rseref');
            }
            if (localStorage.getItem('rsewarehouse')) {
                localStorage.removeItem('rsewarehouse');
            }
            if (localStorage.getItem('rsenote')) {
                localStorage.removeItem('rsenote');
            }
            if (localStorage.getItem('rseinnote')) {
                localStorage.removeItem('rseinnote');
            }
            if (localStorage.getItem('rsesupplier')) {
                localStorage.removeItem('rsesupplier');
            }
            if(localStorage.getItem('childsupplier')) {
                localStorage.removeItem('childsupplier');
            }
            if (localStorage.getItem('rsedate')) {
                localStorage.removeItem('rsedate');
            }
            if (localStorage.getItem('rsebiller')) {
                localStorage.removeItem('rsebiller');
            }
            
        
    <?php $this->sma->unset_data('remove_rlls');
    } ?>

    $(document).ready(function () {

        if (!localStorage.getItem('rsedate')) {
            $("#rsedate").datetimepicker({
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

        ItemnTotals();
        $('.bootbox').on('hidden.bs.modal', function (e) {
            $('#add_item').focus();
        });
        $("#add_item").autocomplete({
            source: function (request, response) {    
                
                let supp_id = localStorage.getItem('childsupplier') !== null && localStorage.getItem('childsupplier') !== "null" ? localStorage.getItem('childsupplier') : localStorage.getItem('rsesupplier');
                
                $.ajax({
                    type: 'get',
                    url: '<?= admin_url('returns_supplier/bch_suggestions'); ?>',
                    dataType: "json",
                    data: { term: request.term, warehouse_id: $("#rsewarehouse").val(), supplier_id: supp_id, },
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
                                    warehouse_id: $("#rsewarehouse").val(),
                                    supplier_id: supp_id
                                },
                                success: function (data) {
                                    $(this).removeClass('ui-autocomplete-loading');
                                    if(data){
                                        add_return_item(data[0]);
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
                    //bootbox.alert('<?= lang('no_match_found') ?>', function () {
                    //    $('#add_item').focus();
                    //});
                    $(this).removeClass('ui-autocomplete-loading');
                    $(this).val('');
                }
            },
            select: function (event, ui) {
                event.preventDefault();
                if (ui.item.id !== 0) {
                    openPopup(ui.item);
                    $(this).val('');
                    /*var row = add_return_item(ui.item);
                    if (row)
                        $(this).val('');*/
                } else {
                    bootbox.alert('<?= lang('no_match_found') ?>');
                }
            }
        });
    });

    function openPopup(selectedItem) {
        let supp_id = localStorage.getItem('childsupplier') !== null && localStorage.getItem('childsupplier') !== "null" ? localStorage.getItem('childsupplier') : localStorage.getItem('rsesupplier');
        // Assuming selectedItem has avz_item_code as part of its data
        $.ajax({
            type: 'get',
            url: '<?= admin_url('products/get_avz_item_code_details'); ?>', // Adjust the URL as needed
            dataType: "json",
            data: {
                item_id: selectedItem.item_id, // Send the unique item code
                warehouse_id: $("#rsewarehouse").val(), // Optionally include warehouse ID if needed
                supplier_id: supp_id
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
                    var toitemsStorageValue = JSON.parse(localStorage.getItem('rseitems'));
                    data.forEach(function (item) {
                        count++;
                        var avzItemCode = item.row.avz_item_code;
                        var found = false;

                        Object.keys(rseitems).forEach(function (key) {
                            if (rseitems[key].row && rseitems[key].row.avz_item_code === avzItemCode) {
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
                        var selectedItem = data.find(function (item) {
                            //return item.row.avz_item_code === clickedItemCode;
                            return String(item.row.avz_item_code).trim() === String(clickedItemCode).trim();
                        });

                        if (selectedItem) {
                            $('#itemModal').modal('hide');
                            var available = $(this).data('available');
                            if(!available){
                                add_return_item(selectedItem);
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

        var row = add_return_item(selectedRecord);
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
        <h2 class="blue"><i class="fa-fw fa fa-plus"></i><?= lang('add_return'); ?></h2>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">

                <p class="introtext"><?php echo lang('enter_info'); ?></p>
                <?php
                $attrib = ['data-toggle' => 'validator', 'role' => 'form'];
                echo admin_form_open_multipart('returns_supplier/add', $attrib);
                ?>

                <input type="hidden" name="return_screen"  value="customer">
                <div class="row">
                    <div class="col-lg-12">
                        <?php if ($Owner || $Admin) {
                    ?>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <?= lang('date', 'rsedate'); ?>
                                    <?php echo form_input('date', (isset($_POST['date']) ? $_POST['date'] : ''), 'class="form-control input-tip datetime" id="rsedate" required="required"'); ?>
                                </div>
                            </div>
                        <?php
                } ?>
                        <input type="hidden" name="reference_no" id="rseref" value="<?= $reference; ?>" />
                        <?php /*if ($Owner || $Admin || !$this->session->userdata('biller_id')) {
                    */?><!--
                            <div class="col-md-4">
                                <div class="form-group">
                                    <?/*= lang('biller', 'rsebiller'); */?>
                                    <?php
/*                                    $bl[''] = '';
                    foreach ($billers as $biller) {
                        $bl[$biller->id] = $biller->company && $biller->company != '-' ? $biller->company : $biller->name;
                    }
                    echo form_dropdown('biller', $bl, (isset($_POST['biller']) ? $_POST['biller'] : $Settings->default_biller), 'id="rsebiller" data-placeholder="' . lang('select') . ' ' . lang('biller') . '" required="required" class="form-control input-tip select" style="width:100%;"'); */?>
                                </div>
                            </div>
                        --><?php
/*                } else {
                    $biller_input = [
                        'type'  => 'hidden',
                        'name'  => 'biller',
                        'id'    => 'rsebiller',
                        'value' => $this->session->userdata('biller_id'),
                    ];

                    echo form_input($biller_input);
                } */?>

                        <?php if ($Owner || $Admin || !$this->session->userdata('warehouse_id')) {
                    ?>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <?= lang('warehouse', 'rsewarehouse'); ?>
                                    <?php
                                    $wh[''] = '';
                    foreach ($warehouses as $warehouse) {
                        $wh[$warehouse->id] = $warehouse->name.' ('.$warehouse->code.')';
                    }
                    echo form_dropdown('warehouse', $wh, (isset($_POST['warehouse']) ? $_POST['warehouse'] : $Settings->default_warehouse), 'id="rsewarehouse" class="form-control input-tip select" data-placeholder="' . lang('select') . ' ' . lang('warehouse') . '" required="required" style="width:100%;" '); ?>
                                </div>
                            </div>
                        <?php
                } else {
                    $warehouse_input = [
                        'type'  => 'hidden',
                        'name'  => 'warehouse',
                        'id'    => 'rsewarehouse',
                        'value' => $this->session->userdata('warehouse_id'),
                    ];

                    echo form_input($warehouse_input);
                } ?>

                        <div class="col-md-4">
                            <div class="form-group">
                                <?= lang('Parent Supplier', 'rsesupplier'); ?>
                                <?php if ($Owner || $Admin || $GP['suppliers-add'] || $GP['suppliers-index']) {
                                    ?><div class="input-group"><?php
                                } ?>
                                    <input type="hidden" name="supplier" value="" id="rsesupplier"
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
                                <?= lang('Child Supplier', 'rsesupplier'); ?>
                                <?php
                                $childSupArr[''] = '';
                                
                                echo form_dropdown('childsupplier', $childSupArr, $_POST['childsupplier'], 'id="childsupplier" class="form-control input-tip select" data-placeholder="' . lang('select') . ' ' . lang('child supplier') . '" required="required" style="width:100%;" '); ?>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <?= lang('status', 'status'); ?>
                                <?php
                                $post = ['pending' => lang('pending')];
                                echo form_dropdown('status', $post, ($_POST['status'] ?? ''), 'id="status" class="form-control input-tip select" data-placeholder="' . $this->lang->line('select') . ' ' . $this->lang->line('status') . '" required="required" style="width:100%;" ');
                                ?>
                            </div>
                        </div>

                          <?php if ($Settings->tax2) {  ?>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <?= lang('order_tax', 'rsetax2'); ?>
                                    <?php
                                    $tr[''] = '';
                                    foreach ($tax_rates as $tax) {
                                        $tr[$tax->id] = $tax->name;
                                    }
                                    echo form_dropdown('order_tax', $tr, (isset($_POST['order_tax']) ? $_POST['order_tax'] : $Settings->default_tax_rate2), 'id="rsetax2" data-placeholder="' . lang('select') . ' ' . lang('order_tax') . '" class="form-control input-tip select" style="width:100%;"'); ?>
                                </div>
                            </div>

                            <?php
                              } 
                            ?>

                        <?php if ($Owner || $Admin || $this->session->userdata('allow_discount')) {
                                    ?>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <?= lang('order_discount', 'rsediscount'); ?>
                                    <?php echo form_input('order_discount', '', 'class="form-control input-tip" id="rsediscount"'); ?>
                                </div>
                            </div>
                        <?php
                                } ?>
                        <div class="col-md-4">
                            <div class="form-group">
                                <?= lang('shipping', 'rseshipping'); ?>
                                <?php echo form_input('shipping', '', 'class="form-control input-tip" id="rseshipping"'); ?>
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
                                        <?php if ($Owner || $Admin || $GP['products-add']) {
                                    ?>
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
                                            <!-- <th class="col-md-1"><?= lang('Purchase Price'); ?></th> -->
                                            <th class="col-md-1"><?= lang('batch'); ?></th>
                                            <th class="col-md-1"><?= lang('expiry_date'); ?></th>
                                            <th class="col-md-1"><?= lang('Quantity'); ?></th>  
                                            <th class="col-md-1"><?= lang('Bonus'); ?></th>
                                            <th class="col-md-1"><?= lang('Cost Price'); ?></th> 
                                            <?php
                                            /* if ($Settings->product_discount && ($Owner || $Admin || $this->session->userdata('allow_discount'))) {
                                                echo '<th class="col-md-1">' . lang('dis 1') . '</th>';
                                            } 
                                            if ($Settings->product_discount && ($Owner || $Admin || $this->session->userdata('allow_discount'))) {
                                                echo '<th class="col-md-1">' . lang('dis 2') . '</th>';
                                            } */ 
                                            ?>
                                            <?php
                                            if ($Settings->tax1) {
                                                echo '<th class="col-md-1">' . lang('vat 15%') . '</th>';
                                            }
                                            ?>

                                           <!-- <th class="col-md-1"><?= lang('Total Purchase'); ?></th> -->
                                           <th>
                                                <?= lang('Total Purchases'); ?>
                                               
                                            </th>
                                           <th class="col-md-1"><?= lang('Net Purchases'); ?></th>
                                           <th class="col-md-1"><?= lang('Unit Cost'); ?></th>
                                           
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
                                        <?= lang('return_note', 'rsenote'); ?>
                                        <?php echo form_textarea('note', (isset($_POST['note']) ? $_POST['note'] : ''), 'class="form-control" id="rsenote" style="margin-top: 10px; height: 100px;"'); ?>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <?= lang('staff_note', 'rseinnote'); ?>
                                        <?php echo form_textarea('staff_note', (isset($_POST['staff_note']) ? $_POST['staff_note'] : ''), 'class="form-control" id="rseinnote" style="margin-top: 10px; height: 100px;"'); ?>

                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <?php
                            $data = array(
                                'name' => 'add_return',
                                'onclick'=>"return confirm('Are you sure to proceed?')"
                            );
                            ?>
                            <div
                                class="fprom-group"><?php echo form_submit($data, lang('submit'), 'id="add_return" class="btn btn-primary" style="padding: 6px 15px; margin:15px 0;"'); ?>
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
