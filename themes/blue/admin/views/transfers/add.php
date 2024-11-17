<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<script type="text/javascript">
    <?php if ($this->session->userdata('remove_tols')) {
        ?>
        if (localStorage.getItem('toitems')) {
            localStorage.removeItem('toitems');
        }
        if (localStorage.getItem('toshipping')) {
            localStorage.removeItem('toshipping');
        }
        if (localStorage.getItem('toref')) {
            localStorage.removeItem('toref');
        }
        if (localStorage.getItem('to_warehouse')) {
            localStorage.removeItem('to_warehouse');
        }
        if (localStorage.getItem('tonote')) {
            localStorage.removeItem('tonote');
        }
        if (localStorage.getItem('from_warehouse')) {
            localStorage.removeItem('from_warehouse');
        }
        if (localStorage.getItem('todate')) {
            localStorage.removeItem('todate');
        }
        if (localStorage.getItem('tostatus')) {
            localStorage.removeItem('tostatus');
        }
        if (localStorage.getItem('currentstatus')) {
            localStorage.removeItem('currentstatus');
        }
        <?php $this->sma->unset_data('remove_tols');
    } ?>
    if (localStorage.getItem('currentstatus')) {
        localStorage.removeItem('currentstatus');
    }
    var count = 1, an = 1, product_variant = 0, shipping = 0,
        product_tax = 0, total = 0,
        tax_rates = <?php echo json_encode($tax_rates); ?>, toitems = {},
        audio_success = new Audio('<?= $assets ?>sounds/sound2.mp3'),
        audio_error = new Audio('<?= $assets ?>sounds/sound3.mp3');
    $(document).ready(function () {
        <?php if ($Owner || $Admin) {
            ?>
            if (!localStorage.getItem('todate')) {
                $("#todate").datetimepicker({
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
            $(document).on('change', '#todate', function (e) {
                localStorage.setItem('todate', $(this).val());
            });
            if (todate = localStorage.getItem('todate')) {
                $('#todate').val(todate);
            }
            <?php
        } ?>
        ItemnTotals();
        $("#add_item").autocomplete({
            //source: '<?= admin_url('transfers/suggestions'); ?>',
            source: function (request, response) {
                console.log('Autocomplete triggered');
                if (!$('#from_warehouse').val()) {
                    $('#add_item').val('').removeClass('ui-autocomplete-loading');
                    bootbox.alert('<?= lang('select_above'); ?>');
                    $('#add_item').focus();
                    return false;
                }

                $.ajax({
                    type: 'get',
                    url: '<?= admin_url('transfers/bch_suggestions'); ?>',
                    dataType: "json",
                    data: {
                        term: request.term,
                        warehouse_id: $("#from_warehouse").val()
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
                                    warehouse_id: $("#from_warehouse").val()
                                },
                                success: function (data) {
                                    $(this).removeClass('ui-autocomplete-loading');
                                    if(data){
                                        add_transfer_item(data[0]);
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
                    //audio_error.play();
                    if ($('#from_warehouse').val()) {
                        // bootbox.alert('<?= lang('no_match_found') ?>', function () {
                        //     $('#add_item').focus();
                        // });
                    } else {
                        bootbox.alert('<?= lang('please_select_warehouse') ?>', function () {
                            $('#add_item').focus();
                        });
                    }
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
                    //audio_error.play();
                    // bootbox.alert('<?= lang('no_match_found') ?>', function () {
                    //     $('#add_item').focus();
                    // });
                    $(this).removeClass('ui-autocomplete-loading');
                    $(this).val('');

                }
            },
            select: function (event, ui) {
                event.preventDefault();
                if (ui.item.id !== 0) {
                    
                    openPopup(ui.item);
                    $(this).val('');
                    // var row = add_transfer_item(ui.item);
                    // if (row)
                    //     $(this).val('');
                } else {
                    //audio_error.play();
                    bootbox.alert('<?= lang('no_match_found') ?>');
                }
            }
        });
        $('#add_item').bind('keypress', function (e) {
            if (e.keyCode == 13) {
                e.preventDefault();
                //$(this).autocomplete("search");
            }
        });

        var to_warehouse;
        $('#to_warehouse').on("select2-focus", function (e) {
            to_warehouse = $(this).val();
        }).on("select2-close", function (e) {
            if ($(this).val() != '' && $(this).val() == $('#from_warehouse').val()) {
                $(this).select2('val', to_warehouse);
                bootbox.alert('<?= lang('please_select_different_warehouse') ?>');
            }
        });
        var from_warehouse;
        $('#from_warehouse').on("select2-focus", function (e) {
            from_warehouse = $(this).val();
        }).on("select2-close", function (e) {
            if ($(this).val() != '' && $(this).val() == $('#to_warehouse').val()) {
                $(this).select2('val', from_warehouse);
                bootbox.alert('<?= lang('please_select_different_warehouse') ?>');
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
                warehouse_id: $("#from_warehouse").val() // Optionally include warehouse ID if needed
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
                    var toitemsStorageValue = JSON.parse(localStorage.getItem('toitems'));
                    data.forEach(function (item) {
                        count++;

                        var avzItemCode = item.row.avz_item_code;
                        var found = false;

                        Object.keys(toitems).forEach(function (key) {
                            if (toitems[key].row && toitems[key].row.avz_item_code === avzItemCode) {
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
                            console.log(item.row.avz_item_code, clickedItemCode);
                            return String(item.row.avz_item_code).trim() === String(clickedItemCode).trim();
                        });
                     
                        if (selectedItem) {
                            $('#itemModal').modal('hide');
                            var available = $(this).data('available');
                            if(!available){
                                add_transfer_item(selectedItem);
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


    // $('#itemModal').on('shown.bs.modal', function () {
    //     $(this).find('.modal-item').first().focus(); 
    // });


    // $(document).on('keydown', '.modal-item', function (event) {
    //     if (event.key === 'Enter' || event.key === ' ') {
    //         event.preventDefault();

    //         $(this).click();
    //     }
    // });

    // $(document).on('click', '.modal-item', function () {
    //     var selectedItemId = $(this).data('item-id');
    //     console.log('Selected Item ID:', selectedItemId);
    //     $('#itemModal').modal('hide');
    //     var row = add_transfer_item(selectedRecord);
    //     // You can then call any function like add_transfer_item(selectedItemId) here
    // });

    function onSelectFromPopup(selectedRecord) {

        $('#itemModal').modal('hide');

        var row = add_transfer_item(selectedRecord);
        if (row) {
            // If the row was successfully added, you can do additional actions here
            
        }
    }

</script>

<div class="modal fade test" id="itemModal" tabindex="-1" role="dialog" aria-labelledby="itemModalLabel" aria-hidden="true">
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
        <h2 class="blue"><i class="fa-fw fa fa-plus"></i><?= lang('add_transfer'); ?></h2>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">

                <p class="introtext"><?php echo lang('enter_info'); ?></p>
                <?php
                $attrib = ['data-toggle' => 'validator', 'role' => 'form'];
                echo admin_form_open_multipart('transfers/add', $attrib)
                    ?>


                <div class="row">
                    <div class="col-lg-12">

                        <?php if ($Owner || $Admin) {
                            ?>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <?= lang('date', 'todate'); ?>
                                    <?php echo form_input('date', ($_POST['date'] ?? ''), 'class="form-control input-tip datetime" id="todate" required="required"'); ?>
                                </div>
                            </div>
                            <?php
                        } ?>
                        <div class="col-md-4">
                            <div class="form-group">
                                <?= lang('reference_no', 'ref'); ?>
                                <?php echo form_input('reference_no', ($_POST['reference_no'] ?? $rnumber), 'class="form-control input-tip" id="ref"'); ?>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <?= lang('to_warehouse', 'to_warehouse'); ?>
                                <?php
                                $wh[''] = '';
                                foreach ($warehouses as $warehouse) {
                                    $wh[$warehouse->id] = $warehouse->name . ' (' . $warehouse->code . ')';
                                }
                                echo form_dropdown('to_warehouse', $wh, ($_POST['to_warehouse'] ?? ''), 'id="to_warehouse" class="form-control input-tip select" data-placeholder="' . $this->lang->line('select') . ' ' . $this->lang->line('to_warehouse') . '" required="required" style="width:100%;" ');
                                ?>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <?= lang('status', 'tostatus'); ?>
                                <?php
                                $post = ['save' => lang('save'), 'completed' => lang('completed')];
                                echo form_dropdown('status', $post, ($_POST['status'] ?? ''), 'id="tostatus" class="form-control input-tip select" data-placeholder="' . $this->lang->line('select') . ' ' . $this->lang->line('status') . '" required="required" style="width:100%;" ');
                                ?>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group" style="margin-bottom:5px;">
                                <?= lang('shipping', 'toshipping'); ?>
                                <?php echo form_input('shipping', '', 'class="form-control input-tip" id="toshipping"'); ?>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <?= lang('attachments', 'document') ?>
                                <input id="document" type="file" data-browse-label="<?= lang('browse'); ?>"
                                    name="attachments[]" multiple data-show-upload="false" data-show-preview="false"
                                    class="form-control file">
                            </div>
                        </div>

                        <div class="col-md-12">
                            <?php if ($Owner || $Admin || !$this->session->userdata('warehouse_id')) {
                                ?>
                                <div class="panel panel-warning">
                                    <div class="panel-heading"><?= lang('please_select_these_before_adding_product') ?>
                                    </div>
                                    <div class="panel-body" style="padding: 5px;">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <?= lang('from_warehouse', 'from_warehouse'); ?>

                                                <?php
                                                echo form_dropdown('from_warehouse', $wh, ($_POST['from_warehouse'] ?? ''), 'id="from_warehouse" class="form-control input-tip select" data-placeholder="' . $this->lang->line('select') . ' ' . $this->lang->line('from_warehouse') . '" required="required" style="width:100%;" '); ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php
                            } else {
                                $warehouse_input = [
                                    'type' => 'hidden',
                                    'name' => 'from_warehouse',
                                    'id' => 'from_warehouse',
                                    'value' => $this->session->userdata('warehouse_id'),
                                ];
                                echo form_input($warehouse_input);
                            } ?>
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
                                    </div>
                                </div>
                                <div class="clearfix"></div>
                            </div>
                        </div>

                        <div class="clearfix"></div>
                        <div class="col-md-12">
                            <div class="control-group table-group">
                                <label class="table-label"><?= lang('order_items'); ?></label>

                                <div class="controls table-controls">
                                    <table id="toTable"
                                        class="table items table-striped table-bordered table-condensed table-hover sortable_table">
                                        <thead>

                                        <tr>
                                            <th class="col-md-4"><?= lang('product') . ' (' . lang('code') . ' - ' . lang('name') . ')'; ?></th>
                                            
                                            <th class="col-md-1">Batch </th>
                                            <?php
                                            if ($Settings->product_expiry) {
                                                echo '<th class="col-md-2">' . $this->lang->line('expiry_date') . '</th>';
                                            }
                                            ?>
                                            <th class="col-md-1"><?= lang('Sales Price'); ?></th>
                                            <th class="col-md-1"><?= lang('Cost Price'); ?></th>
                                            <th class="col-md-1"><?= lang('quantity'); ?></th>
                                            <!-- <th class="col-md-1"><?= lang('Actual Quantity'); ?></th> -->
                                            <?php
                                            if ($Settings->tax1) {
                                                echo '<th class="col-md-1">' . $this->lang->line('product_tax') . '</th>';
                                            }
                                            ?>
                                            <th><?= lang('subtotal'); ?> (<span
                                                    class="currency"><?= $default_currency->code ?></span>)
                                            </th>
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


                            <div class="from-group">
                                <?= lang('note', 'tonote'); ?>
                                <?php echo form_textarea('note', ($_POST['note'] ?? ''), 'id="tonote" class="form-control" style="margin-top: 10px; height: 100px;"'); ?>
                            </div>

                            <?php
                            $data = array(
                                'name' => 'add_transfer',
                                'onclick' => "return confirm('Are you sure to proceed?')"
                            );
                            ?>
                            <div class="from-group">
                                <?php echo form_submit($data, $this->lang->line('submit'), 'id="add_transfer" class="btn btn-primary" style="padding: 6px 15px; margin:15px 0;"'); ?>
                                <button type="button" class="btn btn-danger" id="reset"><?= lang('reset') ?></button>
                            </div>
                        </div>

                    </div>
                </div>

                <div id="bottom-total" class="well well-sm" style="margin-bottom: 0;">
                    <table class="table table-bordered table-condensed totals" style="margin-bottom:0;">
                        <tr class="warning">
                            <td><?= lang('items') ?> <span class="totals_val pull-right" id="titems">0</span></td>
                            <td><?= lang('total') ?> <span class="totals_val pull-right" id="total">0.00</span></td>
                            <td><?= lang('shipping') ?> <span class="totals_val pull-right" id="tship">0.00</span></td>
                            <td><?= lang('grand_total') ?> <span class="totals_val pull-right" id="gtotal">0.00</span>
                            </td>
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
                            class="fa fa-2x">&times;</i></span><span class="sr-only"><?= lang('close'); ?></span></button>
                <h4 class="modal-title" id="prModalLabel"></h4>
            </div>
            <div class="modal-body" id="pr_popover_content">
                <form class="form-horizontal" role="form">
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
                    <div class="form-group">
                        <label for="pprice" class="col-sm-4 control-label"><?= lang('cost') ?></label>

                        <div class="col-sm-8">
                            <input type="text" class="form-control" id="pprice">
                        </div>
                    </div>
                    <input type="hidden" id="old_tax" value="" />
                    <input type="hidden" id="old_qty" value="" />
                    <input type="hidden" id="old_price" value="" />
                    <input type="hidden" id="row_id" value="" />
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" id="editItem"><?= lang('submit') ?></button>
            </div>
        </div>
    </div>
</div>
<?php if (!$Owner || !$Admin || $this->session->userdata('warehouse_id')) {
    ?>
    <script>
        $(document).ready(function () {
            $("#to_warehouse option[value='<?= $this->session->userdata('warehouse_id'); ?>']").attr('disabled', 'disabled');
        });
    </script>
    <?php
} ?>