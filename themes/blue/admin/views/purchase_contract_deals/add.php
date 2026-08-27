<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-plus"></i> <?= lang('Add Contract Deal') ?></h2>
    </div>
    <div class="box-content">
        <?= form_open_multipart(admin_url('purchase_contract_deals/add'), ['id' => 'purchaseDealForm']) ?>

        <div class="row">
            <div class="col-md-3">
                <div class="form-group">
                    <label for="date">Date</label>
                    <input type="text" name="date" id="date" class="form-control" value="<?= date('d/m/Y H:i') ?>">
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label for="supplier">Supplier</label>
                    <select name="supplier" id="csupplier" class="form-control">
                        <?php foreach ($suppliers as $s) :
                            if( $s->level != 2) continue;
                            ?>
                            <option value="<?= $s->id ?>"><?= $s->name ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="col-md-3">
                <div class="form-group">
                    <label for="note">Note</label>
                    <textarea name="note" id="note" class="form-control"></textarea>
                </div>
            </div>
        </div>

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
                        <label class="col-md-2 control-label">Deal Type</label>
                        <div class="col-md-2">
                            <select name="deal_type" id="deal_type" class="form-control">
                                <option value="">Select Deal Type</option>
                                <option value="quantity">Quantity</option>
                                <option value="amount">Amount</option>
                            </select>
                        </div>

                        <label class="col-md-2 control-label">Threshold</label>
                        <div class="col-md-2">
                            <input type="number" class="form-control new_item_cls" name="threshold" step="0.01" placeholder="0.00">
                        </div>

                        <label class="col-md-2 control-label">Deal %</label>
                        <div class="col-md-2">
                             <input type="number" class="form-control new_item_cls" name="deal_percentage" max="100" placeholder="">
                       </div>
                    </div>


                    <div class="form-group">
                        <label class="col-md-2 control-label">Discount 1 (%)</label>
                        <div class="col-md-2">
                             <input type="number" class="form-control new_item_cls" name="dis1_percentage" max="100" placeholder="">
                        </div>

                        <label class="col-md-2 control-label">Discount 2 (%)</label>
                        <div class="col-md-2">
                             <input type="number" class="form-control new_item_cls" name="dis2_percentage" max="100" placeholder="">
                       </div>

                        <label class="col-md-2 control-label">Discount 3 (%)</label>
                        <div class="col-md-2">
                             <input type="number" class="form-control new_item_cls" name="dis3_percentage" max="100" placeholder="">
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


        <!-- <fieldset class="scheduler-border">
            <legend class="scheduler-border">Add new Item</legend>
            <div class="row">
                <div class="col-lg-12">

                    <div class="form-group">
                        <label class="col-md-2 control-label">Item Name</label>
                        <div class="col-md-6">
                            <input type="text" class="form-control new_item_cls" id="add_item_new" name="item_name" placeholder="Enter item name">
                        </div>
                    </div>

                    <div class="clearfix"></div>


                    <div class="form-group">
                        <label class="col-md-2 control-label">Deal Type</label>
                        <div class="col-md-2">
                            <select name="deal_type" id="deal_type" class="form-control">
                                <option value="">Select Deal Type</option>
                                <option value="quantity">Quantity</option>
                                <option value="amount">Amount</option>

                            </select>
                        </div>

                        <label class="col-md-1 control-label">Threshold</label>
                        <div class="col-md-2">
                            <input type="number" class="form-control new_item_cls" name="threshold" step="0.01" placeholder="0.00">
                        </div>

                        <label class="col-md-1 control-label">Discount 1 %</label>
                        <div class="col-md-2">
                            <input type="number" class="form-control new_item_cls" name="dis1_percentage" max="100" placeholder="">
                        </div>

                    </div>
                    <div class="form-group">

                        <label class="col-md-1 control-label">Discount 2 %</label>
                        <div class="col-md-2">
                            <input type="number" class="form-control new_item_cls" name="dis2_percentage" max="100" placeholder="">
                        </div>

                        <label class="col-md-1 control-label">Discount 3 %</label>
                        <div class="col-md-2">
                            <input type="number" class="form-control new_item_cls" name="dis3_percentage" max="100" placeholder="">
                        </div>

                        <label class="col-md-1 control-label">Deal %</label>
                        <div class="col-md-2">
                            <input type="number" class="form-control new_item_cls" name="deal_percentage" max="100" placeholder="">
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="col-md-4">
                            <button type="button" id="saveButton" class="btn btn-success">Save Item</button>
                            <button type="button" id="clearItems" class="btn btn-default">Clear</button>
                        </div>

                    </div>

                </div>
            </div>
        </fieldset> -->

        <!-- List of Added Items -->
        <div class="table-responsive" style="margin-top: 20px;">
            <table class="table table-bordered" id="addedItemsTable">
                <thead>
                    <tr>
                        <th>Item Name</th>
                        <th>Deal Type</th>
                        <th>Threshold</th>
                        <th>Discount 1 %</th>
                        <th>Discount 2 %</th>
                        <th>Discount 3 %</th>
                        <th>Deal %</th>
                        <th style="width: 80px;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Rows will be added here dynamically -->
                </tbody>
            </table>
        </div>


        <button class="btn btn-primary">Save/Update</button>
        <?= form_close() ?>
    </div>
</div>

<script>
    $(document).ready(function() {

        document.getElementById('purchaseDealForm').addEventListener('keydown', function(event) {
            if (event.key === 'Enter') {
                event.preventDefault(); // stop form submission
            }
        });
        let selectedItem = null;
        let addedItems = [];

        //  NEW CHANGE FOR ADDING ITEM
        $("#add_item_new").autocomplete({

            source: function(request, response) {
                $.ajax({
                    type: 'get',
                    url: '<?= admin_url('purchase_contract_deals/supplier_products'); ?>',
                    dataType: "json",
                    data: {
                        term: request.term,
                        supplier_id: $("#supplier").val()
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
                    $("input[name='item_name']").val(selectedItem.label || selectedItem.name);
                    $("#deal_type").val(selectedItem.deal_type).trigger('change.select2');

                    $("input[name='threshold']").val(selectedItem.threshold || '');
                    $("input[name='dis1_percentage']").val(selectedItem.dis1_percentage || '');
                    $("input[name='dis2_percentage']").val(selectedItem.dis2_percentage || '');
                    $("input[name='dis3_percentage']").val(selectedItem.dis3_percentage || '');
                    $("input[name='deal_percentage']").val(selectedItem.deal_percentage || '');



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
            $("#deal_type").val('');
            selectedItem = null;
        });

        // Save and Add Item to Table
        $("#saveButton").on("click", function() {
            if (!selectedItem) {
                alert("Please select an item first.");
                return;
            }

            // Build item data from inputs
            const itemData = {
                id: selectedItem.id,
                name: $("input[name='item_name']").val(),
                deal_type: $("#deal_type").val(),
                threshold: $("input[name='threshold']").val(),
                dis1_percentage: $("input[name='dis1_percentage']").val(),
                dis2_percentage: $("input[name='dis2_percentage']").val(),
                dis3_percentage: $("input[name='dis3_percentage']").val(),
                deal_percentage: $("input[name='deal_percentage']").val()
            };

            // Push to global array (optional)
            addedItems.push(itemData);

            // Append to table
            const newRow = `
    <tr data-id="${itemData.id}">
        <td>
            ${itemData.name}
        </td>
        <td>
            ${itemData.deal_type || '-'}
        </td>
        <td>
            ${itemData.threshold || '-'}
        </td>
        <td>
            ${itemData.dis1_percentage || '-'}
        </td>
        <td>
            ${itemData.dis2_percentage || '-'}
        </td>
        <td>
            ${itemData.dis3_percentage || '-'}
        </td>
        <td>
            ${itemData.deal_percentage || '-'}
        </td>
        <td>
            <button type="button" class="btn btn-danger btn-sm deleteItem">
                <i class="fa fa-trash"></i>
            </button>
        </td>
    </tr>
    <input type="hidden" name="items[]" value='${JSON.stringify({
            item_id: itemData.id,
            deal_type: itemData.deal_type || "",
            threshold: itemData.threshold || "",
            dis1_percentage: itemData.dis1_percentage || "",
            dis2_percentage: itemData.dis2_percentage || "",
            dis3_percentage: itemData.dis3_percentage || "",
            deal_percentage: itemData.deal_percentage || ""
        })}'>
`;

            $("#addedItemsTable tbody").append(newRow);

            // Clear inputs
            $('.new_item_cls').val('');
            $("#deal_type").val('');
            selectedItem = null;
        });

        // Delete button handler
        $(document).on("click", ".deleteItem", function() {
            const row = $(this).closest("tr");
            const itemId = row.data("id");
            // Remove from global list if needed
            addedItems = addedItems.filter(item => item.id !== itemId);
            // Remove row from UI
            row.remove();
        });
    });
</script>