<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-cubes"></i><?= lang('Hills Inventory Check - Manual Entry'); ?></h2>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
                <p class="introtext">Select warehouse and shelf to view products for inventory checking.</p>

                <div class="well well-sm">
                    <div class="form-group col-md-3">
                        <label for="warehouse_select"><?= lang('warehouse'); ?> *</label>
                        <?php
                        $wh[''] = lang('select') . ' ' . lang('warehouse');
                        foreach ($warehouses as $warehouse) {
                            $wh[$warehouse->id] = $warehouse->name;
                        }
                        echo form_dropdown('warehouse_id', $wh, '', 'id="warehouse_select" class="form-control select2" required style="width:100%;"');
                        ?>
                    </div>

                    <div class="form-group col-md-3">
                        <label for="shelf_select"><?= lang('Shelf'); ?> *</label>
                        <select name="shelf" id="shelf_select" class="form-control select2" required style="width:100%;" disabled>
                            <option value=""><?= lang('select_warehouse_first'); ?></option>
                        </select>
                        <span id="closed_shelf_warning" style="display:none;color:red;font-weight:bold;"></span>
                    </div>

                    <div class="form-group col-md-3">
                        <label for="product_select"><?= lang('Product'); ?> (<?= lang('optional'); ?>)</label>
                        <select name="product_id" id="product_select" class="form-control select2" style="width:100%;" disabled>
                            <option value=""><?= lang('select_shelf_first'); ?></option>
                        </select>
                    </div>

                    <div class="form-group col-md-3">
                        <label>&nbsp;</label><br>
                        <button type="button" id="load_products_btn" class="btn btn-primary" disabled>
                            <i class="fa fa-search"></i> <?= lang('Load Products'); ?>
                        </button>
                    </div>

                    <div class="form-group col-md-3">
                        <label>&nbsp;</label><br>
                        <button type="button" id="add_move_product_btn" class="btn btn-warning" disabled>
                            <i class="fa fa-plus"></i> <?= lang('Add/Move Product to Shelf'); ?>
                        </button>
                    </div>
                    <div class="clearfix"></div>
                </div>


                <!-- Modal: Add or Move Product to Shelf -->
                <div id="moveProductModal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                                <h4 class="modal-title"><?= lang('Add / Move Product to Shelf'); ?></h4>
                            </div>
                            <div class="modal-body">
                                <form id="move_product_form">
                                    <div class="alert alert-info">
                                        <strong>Warehouse:</strong> <span id="modal_warehouse_name"></span><br>
                                        <strong>Shelf:</strong> <span id="modal_shelf_name"></span>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label><?= lang('Product'); ?> *</label>
                                        <select id="modal_product_select" class="form-control select2-modal" style="width:100%;">
                                            <option value=""><?= lang('Select') . ' ' . lang('product'); ?></option>
                                        </select>
                                    </div>
                                    
                                    <input type="hidden" id="modal_warehouse_id" name="modal_warehouse_id" value="">
                                    <input type="hidden" id="modal_shelf" name="modal_shelf" value="">
                                </form>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-default" data-dismiss="modal"><?= lang('Close'); ?></button>
                                <button type="button" id="confirm_move_product_btn" class="btn btn-primary"><?= lang('Move / Add Product'); ?></button>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="products_section" style="display:none;">
                    <?php $attrib = ['role' => 'form', 'id' => 'inventory_form'];
                    echo admin_form_open_multipart('stock_request/save_hills_inventory_check', $attrib);
                    ?>
                    
                    <input type="hidden" name="warehouse_id" id="hidden_warehouse_id" value="" />
                    <input type="hidden" name="shelf" id="hidden_shelf" value="" />

                    <div class="table-responsive">
                        <h4><b style="background: #f0f0f0;color: #333;padding:10px;border: 1px solid #ddd;display:inline-block;">
                            <span id="selected_warehouse_name"></span> - Shelf: <span id="selected_shelf_name"></span>
                        </b>
                        <span id="request_status" style="margin-left: 15px; padding: 5px 10px; border-radius: 3px; font-size: 12px; display:none;"></span>
                        </h4>
                        <table id="products_table" class="table table-bordered table-hover table-striped">
                            <thead>
                                <tr>
                                    <th style="width: 3%;">#</th>
                                    <th style="width: 13%;"><?= lang('Product Code'); ?></th>
                                    <th style="width: 11%;"><?= lang('AVZ Code'); ?></th>
                                    <th style="width: 11%;"><?= lang('Old Code'); ?></th>
                                    <th style="width: 25%;"><?= lang('Product Name'); ?></th>
                                    <th style="width: 10%;"><?= lang('Expiry Date'); ?></th>
                                    <th style="width: 10%;">Actual Expiry</th>
                                    <th style="width: 10%;"><?= lang('Batch Number'); ?></th>
                                    <th style="width: 10%;">Actual Batch</th>
                                    <th style="width: 12%;"><?= lang('Actual Quantity'); ?> *</th>
                                </tr>
                            </thead>
                            <tbody id="products_tbody">
                                <tr>
                                    <td colspan="10" class="text-center">
                                        <em><?= lang('No products loaded'); ?></em>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="form-group text-center" style="margin-top: 20px;">
                        <button type="submit" class="btn btn-success btn-lg" id="submit_btn" disabled>
                            <i class="fa fa-save"></i> <?= lang('Save Inventory Check'); ?>
                        </button>
                        <button type="button" class="btn btn-danger btn-lg" id="close_shelf_btn" style="margin-left:10px;" disabled>
                            <i class="fa fa-lock"></i> Close Shelf
                        </button>
                        <a href="<?= admin_url('stock_request/inventory_check'); ?>" class="btn btn-default btn-lg">
                            <i class="fa fa-arrow-left"></i> <?= lang('Back to List'); ?>
                        </a>
                    </div>

                    <?php echo form_close(); ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
$(document).ready(function() {
    'use strict';
    
    // Initialize select2
    $('.select2').select2();

    // CSRF Token for AJAX requests
    var csrfData = {};
    csrfData['<?= $this->security->get_csrf_token_name(); ?>'] = '<?= $this->security->get_csrf_hash(); ?>';

    // When warehouse is selected, load shelves
    $('#warehouse_select').change(function() {
        var warehouse_id = $(this).val();
        if(warehouse_id) {
            // Reset shelf and product dropdowns
            $('#shelf_select').html('<option value="">Loading shelves...</option>').prop('disabled', true);
            $('#product_select').html('<option value="">Select shelf first</option>').prop('disabled', true);
            $('#load_products_btn').prop('disabled', true);
            $('#products_section').hide();
            $('#closed_shelf_warning').hide();

            // AJAX call to get shelves and closed shelves
            $.ajax({
                url: '<?= admin_url("stock_request/get_warehouse_shelves"); ?>',
                type: 'POST',
                dataType: 'json',
                data: $.extend({}, csrfData, {warehouse_id: warehouse_id}),
                success: function(response) {
                    // Update CSRF token if provided
                    if(response.<?= $this->security->get_csrf_token_name(); ?>) {
                        csrfData['<?= $this->security->get_csrf_token_name(); ?>'] = response.<?= $this->security->get_csrf_token_name(); ?>;
                    }
                    // Get closed shelves for this warehouse/request
                    $.ajax({
                        url: '<?= admin_url("stock_request/get_closed_shelves"); ?>',
                        type: 'POST',
                        dataType: 'json',
                        data: $.extend({}, csrfData, {warehouse_id: warehouse_id}),
                        success: function(closedResp) {
                            var closedShelves = closedResp.closed_shelves || [];
                            var options = '<option value="">Select Shelf</option>';
                            var anyClosed = false;
                            $.each(response.shelves, function(index, shelf) {
                                if(closedShelves.indexOf(shelf.shelf) === -1) {
                                    options += '<option value="' + shelf.shelf + '">' + shelf.shelf + '</option>';
                                } else {
                                    anyClosed = true;
                                }
                            });
                            $('#shelf_select').html(options).prop('disabled', false);
                            if(anyClosed) {
                                $('#closed_shelf_warning').text('Some shelves are closed and cannot be selected.').show();
                            }
                        },
                        error: function() {
                            $('#shelf_select').html('<option value="">Error loading shelves</option>');
                        }
                    });
                },
                error: function() {
                    $('#shelf_select').html('<option value="">Error loading shelves</option>');
                    bootbox.alert('Error loading shelves. Please try again.');
                }
            });
        } else {
            $('#shelf_select').html('<option value="">Select warehouse first</option>').prop('disabled', true);
            $('#product_select').html('<option value="">Select shelf first</option>').prop('disabled', true);
            $('#load_products_btn').prop('disabled', true);
            $('#products_section').hide();
            $('#closed_shelf_warning').hide();
        }
    });

    // When shelf is selected, load products dropdown and enable load button
    $('#shelf_select').change(function() {
        var shelf = $(this).val();
        var warehouse_id = $('#warehouse_select').val();
        
        if(shelf && warehouse_id) {
            // Reset product dropdown
            $('#product_select').html('<option value="">Loading products...</option>').prop('disabled', true);
            $('#load_products_btn').prop('disabled', false);
            $('#add_move_product_btn').prop('disabled', false); // Enable Add/Move Product button
            $('#products_section').hide();

            // AJAX call to get products for dropdown
            $.ajax({
                url: '<?= admin_url("stock_request/get_shelf_products_dropdown"); ?>',
                type: 'POST',
                dataType: 'json',
                data: $.extend({}, csrfData, {
                    warehouse_id: warehouse_id,
                    shelf: shelf
                }),
                success: function(response) {
                    // Update CSRF token if provided
                    if(response.<?= $this->security->get_csrf_token_name(); ?>) {
                        csrfData['<?= $this->security->get_csrf_token_name(); ?>'] = response.<?= $this->security->get_csrf_token_name(); ?>;
                    }
                    
                    if(response.error == 0 && response.products) {
                        var options = '<option value="">All Products (Optional)</option>';
                        $.each(response.products, function(index, product) {
                            var productName = product.product_name;
                            var productCode = product.product_code || product.item_code || '';
                            if(productCode) {
                                productName = productCode + ' - ' + productName;
                            }
                            options += '<option value="' + product.product_id + '">' + productName + '</option>';
                        });
                        $('#product_select').html(options).prop('disabled', false);
                    } else {
                        $('#product_select').html('<option value="">No products found</option>');
                    }
                },
                error: function() {
                    $('#product_select').html('<option value="">Error loading products</option>');
                }
            });
        } else {
            $('#product_select').html('<option value="">Select shelf first</option>').prop('disabled', true);
            $('#load_products_btn').prop('disabled', true);
            $('#add_move_product_btn').prop('disabled', true); // Disable Add/Move Product button
            $('#products_section').hide();
        }
    });

    // Load products when button clicked
    $('#load_products_btn').click(function() {
        var warehouse_id = $('#warehouse_select').val();
        var shelf = $('#shelf_select').val();
        var product_id = $('#product_select').val();
        var warehouse_name = $('#warehouse_select option:selected').text();

        if(!warehouse_id || !shelf) {
            bootbox.alert('Please select both warehouse and shelf');
            return;
        }

        // Show loading
        $('#products_tbody').html('<tr><td colspan="10" class="text-center"><i class="fa fa-spinner fa-spin"></i> Loading products...</td></tr>');
        $('#products_section').show();
        $('#submit_btn').prop('disabled', true);

        // Set hidden fields
        $('#hidden_warehouse_id').val(warehouse_id);
        $('#hidden_shelf').val(shelf);
        $('#selected_warehouse_name').text(warehouse_name);
        $('#selected_shelf_name').text(shelf);

        // AJAX call to get products
        $.ajax({
            url: '<?= admin_url("stock_request/get_shelf_products"); ?>',
            type: 'POST',
            dataType: 'json',
            data: $.extend({}, csrfData, {
                warehouse_id: warehouse_id,
                shelf: shelf,
                product_id: product_id
            }),
            success: function(response) {
                // Update CSRF token if provided
                if(response.<?= $this->security->get_csrf_token_name(); ?>) {
                    csrfData['<?= $this->security->get_csrf_token_name(); ?>'] = response.<?= $this->security->get_csrf_token_name(); ?>;
                }
                
                if(response.error == 0 && response.products && response.products.length > 0) {
                    var html = '';
                    $.each(response.products, function(index, product) {
                        var rowNum = index + 1;
                        var productCode = product.product_code || '';
                        var avzCode = product.avz_item_code || 'N/A';
                        var itemCode = product.item_code || 'N/A';
                        var expiryDate = product.expiry_date ? product.expiry_date : 'N/A';
                        var batchNumber = product.batch_number || 'N/A';
                        var actualBatch = product.actual_batch || '';
                        var actualExpiry = product.actual_expiry || '';
                        
                        // Convert yyyy-mm-dd to dd/mm/yyyy for display
                        var displayExpiry = '';
                        if(actualExpiry && actualExpiry !== 'N/A'){
                            var dateParts = actualExpiry.split('-');
                            if(dateParts.length === 3){
                                displayExpiry = dateParts[2] + '/' + dateParts[1] + '/' + dateParts[0];
                            } else {
                                displayExpiry = actualExpiry;
                            }
                        }
                        
                        // Ensure quantity is integer (no decimals)
                        var savedQuantity = product.saved_quantity ? parseInt(product.saved_quantity) : '';
                        
                        html += '<tr>';
                        html += '<td>' + rowNum + '</td>';
                        html += '<td>' + productCode + '</td>';
                        html += '<td>' + avzCode + '</td>';
                        html += '<td>' + itemCode + '</td>';
                        html += '<td>' + product.product_name + '</td>';
                        html += '<td>' + expiryDate + '</td>';
                        html += '<td>';
                        html += '<input type="text" name="actual_expiry[]" class="form-control input-sm date" placeholder="Actual Expiry (DD/MM/YYYY)" value="' + displayExpiry + '">';
                        html += '</td>';
                        html += '<td>' + batchNumber + '</td>';
                        html += '<td>';
                        html += '<input type="text" name="actual_batch[]" class="form-control input-sm" placeholder="Actual Batch" value="' + actualBatch + '">';
                        html += '</td>';
                        html += '<td>';
                        html += '<input type="text" name="quantity[]" class="form-control input-sm text-right quantity_input" placeholder="Actual Quantity" pattern="[0-9]*" inputmode="numeric" oninput="this.value = this.value.replace(/[^0-9]/g, \'\');" value="' + savedQuantity + '">';
                        html += '</td>';
                        // Hidden fields to submit product data
                        html += '<input type="hidden" name="product_id[]" value="' + product.product_id + '">';
                        html += '<input type="hidden" name="batch_number[]" value="' + batchNumber + '">';
                        html += '<input type="hidden" name="expiry_date[]" value="' + expiryDate + '">';
                        html += '<input type="hidden" name="avz_code[]" value="' + avzCode + '">';
                        html += '</tr>';
                    });
                    
                    $('#products_tbody').html(html);
                    $('#submit_btn').prop('disabled', false);
                    $('#close_shelf_btn').prop('disabled', false);
                    // Initialize datepicker for actual_expiry fields with dd/mm/yyyy format
                    if ($.fn.datepicker) { $(".date").datepicker({ autoclose: true, format: 'dd/mm/yyyy' }); }
                    
                    // Show request status
                    if(response.is_existing_request) {
                        $('#request_status').show()
                            .css({'background': '#d9edf7', 'color': '#31708f', 'border': '1px solid #bce8f1'})
                            .html('<i class="fa fa-info-circle"></i> Editing Request #' + response.request_id);
                    } else {
                        $('#request_status').show()
                            .css({'background': '#dff0d8', 'color': '#3c763d', 'border': '1px solid #d6e9c6'})
                            .html('<i class="fa fa-plus-circle"></i> New Request');
                    }
                    
                    // Focus on first quantity input
                    setTimeout(function() {
                        $('.quantity_input').first().focus();
                    }, 100);
                    
                } else {
                    $('#products_tbody').html('<tr><td colspan="10" class="text-center"><em>No products found for this shelf</em></td></tr>');
                    $('#submit_btn').prop('disabled', true);
                    bootbox.alert(response.msg || 'No products found for this shelf');
                }
            },
            error: function() {
                $('#products_tbody').html('<tr><td colspan="10" class="text-center text-danger">Error loading products</td></tr>');
                $('#submit_btn').prop('disabled', true);
                bootbox.alert('Error loading products. Please try again.');
            }
        });
    });

    // Form validation before submit

    // Save Inventory Check (default submit)
    $('#inventory_form').submit(function(e) {
        var hasQuantity = false;
        $('.quantity_input').each(function() {
            if($(this).val() != '' && $(this).val() != null) {
                hasQuantity = true;
                return false; // break loop
            }
        });
        if(!hasQuantity) {
            e.preventDefault();
            bootbox.alert('Please enter at least one quantity before submitting.');
            return false;
        }
        
        // Convert date format from dd/mm/yyyy to yyyy-mm-dd before submission
        $('input[name="actual_expiry[]"]').each(function() {
            var dateValue = $(this).val();
            if(dateValue && dateValue.indexOf('/') > -1) {
                var parts = dateValue.split('/');
                if(parts.length === 3) {
                    // Convert dd/mm/yyyy to yyyy-mm-dd
                    $(this).val(parts[2] + '-' + parts[1] + '-' + parts[0]);
                }
            }
        });
        
        // Confirm submission
        e.preventDefault();
        var form = this;
        bootbox.confirm('Are you sure you want to save this inventory check?', function(result) {
            if(result) {
                form.submit();
            }
        });
    });

    // Save & Close Shelf button logic - Only closes shelf without saving inventory
    $('#close_shelf_btn').click(function() {
        var warehouse_id = $('#warehouse_select').val();
        var shelf = $('#shelf_select').val();
        
        if(!warehouse_id || !shelf) {
            bootbox.alert('Please select warehouse and shelf first.');
            return false;
        }
        
        bootbox.confirm('Are you sure you want to close this shelf? It will no longer be available for selection. This cannot be undone.', function(result) {
            if(result) {
                $.ajax({
                    url: '<?= admin_url("stock_request/close_shelf_only"); ?>',
                    type: 'POST',
                    dataType: 'json',
                    data: $.extend({}, csrfData, {
                        warehouse_id: warehouse_id,
                        shelf: shelf
                    }),
                    success: function(response) {
                        // Update CSRF token
                        if(response.<?= $this->security->get_csrf_token_name(); ?>) {
                            csrfData['<?= $this->security->get_csrf_token_name(); ?>'] = response.<?= $this->security->get_csrf_token_name(); ?>;
                        }
                        
                        if(response.error == 0) {
                            bootbox.alert(response.msg, function(){
                                // Reload the page to update closed shelves list
                                location.reload();
                            });
                        } else {
                            bootbox.alert(response.msg);
                        }
                    },
                    error: function() {
                        bootbox.alert('Error closing shelf. Please try again.');
                    }
                });
            }
        });
    });

    // Allow Enter key to move to next input
    $(document).on('keypress', '.quantity_input', function(e) {
        if(e.which == 13) { // Enter key
            e.preventDefault();
            var inputs = $('.quantity_input');
            var index = inputs.index(this);
            if(index < inputs.length - 1) {
                inputs.eq(index + 1).focus().select();
            }
        }
    });

    // Enable Add/Move button when shelf selected
    function updateAddMoveBtnState(){
        // Add/Move button should always be available; require warehouse/shelf on action instead
        $('#add_move_product_btn').prop('disabled', false);
    }

    // Initial check (in case a shelf is pre-selected)
    updateAddMoveBtnState();

    $('#shelf_select').on('change', function() {
        console.log('Shelf changed to:', $(this).val());
        updateAddMoveBtnState();
    });

    // Simple modal product dropdown (populated per-warehouse) - no Select2
    function populateModalProducts(warehouse_id) {
        $('#modal_product_select').html('<option>Loading...</option>');
        if(!warehouse_id) {
            $('#modal_product_select').html('<option value="">Select warehouse first</option>');
            return;
        }
        $.ajax({
            url: '<?= admin_url("stock_request/get_products_for_warehouse"); ?>',
            type: 'POST',
            dataType: 'json',
            data: $.extend({}, csrfData, { warehouse_id: warehouse_id }),
            success: function(resp) {
                if(resp.<?= $this->security->get_csrf_token_name(); ?>) {
                    csrfData['<?= $this->security->get_csrf_token_name(); ?>'] = resp.<?= $this->security->get_csrf_token_name(); ?>;
                }
                if(resp.error == 0 && resp.products) {
                    var opts = '<option value=""><?= lang('Select') . " " . lang('product'); ?></option>';
                    $.each(resp.products, function(i, p) {
                        var label = (p.product_code ? p.product_code + ' - ' : '') + p.product_name + (p.warehouse_shelf ? ' (Shelf: ' + p.warehouse_shelf + ')' : '');
                        opts += '<option value="' + p.product_id + '">' + label + '</option>';
                    });
                    $('#modal_product_select').html(opts);
                } else {
                    $('#modal_product_select').html('<option value="">No products found</option>');
                }
            },
            error: function() {
                $('#modal_product_select').html('<option value="">Error loading products</option>');
            }
        });
    }

    // When modal product select changes, store selected id
    $(document).on('change', '#modal_product_select', function(){
        var val = $(this).val() || '';
        $('#modal_selected_product_id').val(val);
        console.log('Modal product selected:', val);
    });

    // Open modal to add/move product (delegated binding to ensure handler exists even if button is re-rendered)
    $(document).on('click', '#add_move_product_btn', function(e) {
        var warehouse_id = $('#warehouse_select').val();
        var warehouse_name = $('#warehouse_select option:selected').text();
        var shelf = $('#shelf_select').val();
        
        if(!warehouse_id || !shelf) {
            bootbox.alert('Please select both warehouse and shelf first.');
            return;
        }
        
        // Reset modal fields
        $('#modal_product_select').val('').trigger('change');
        
        // Populate modal with warehouse and shelf info
        $('#modal_warehouse_id').val(warehouse_id);
        $('#modal_shelf').val(shelf);
        $('#modal_warehouse_name').text(warehouse_name);
        $('#modal_shelf_name').text(shelf);
        
        // Load products for this warehouse
        populateModalProducts(warehouse_id);
        
        $('#moveProductModal').modal('show');
    });




    // Confirm add/move product (use modal selects, not old Search2)
    $('#confirm_move_product_btn').click(function() {
        var productId = $('#modal_product_select').val();
        if(!productId){
            bootbox.alert('Please select a product');
            return;
        }
        var warehouse_id = $('#modal_warehouse_id').val();
        var target_shelf = $('#modal_shelf').val();

        $.ajax({
            url: '<?= admin_url("stock_request/add_move_product_to_shelf"); ?>',
            type: 'POST',
            dataType: 'json',
            data: $.extend({}, csrfData, {
                product_id: productId,
                warehouse_id: warehouse_id,
                shelf: target_shelf
            }),
            success: function(response) {
                // Update CSRF token
                if(response.<?= $this->security->get_csrf_token_name(); ?>){
                    csrfData['<?= $this->security->get_csrf_token_name(); ?>'] = response.<?= $this->security->get_csrf_token_name(); ?>;
                }

                if(response.error == 0 && response.product){
                    // Add a new row to products table
                    var rowNum = $('#products_tbody tr').length + 1;
                    var p = response.product;
                    var productCode = p.product_code || '';
                    var avzCode = p.avz_item_code || 'N/A';
                    var itemCode = p.item_code || 'N/A';
                    var expiryDate = response.expiry_date || 'N/A';
                    var savedQuantity = response.saved_quantity || '';

                    var html = '<tr>';
                    html += '<td>' + rowNum + '</td>';
                    html += '<td>' + productCode + '</td>';
                    html += '<td>' + avzCode + '</td>';
                    html += '<td>' + itemCode + '</td>';
                    html += '<td>' + p.product_name + '</td>';
                    html += '<td>' + (response.batch_number || 'N/A') + '</td>';
                    html += '<td>' + expiryDate + '</td>';
                    html += '<td>';
                    html += '<input type="hidden" name="product_id[]" value="' + p.product_id + '">';
                    html += '<input type="hidden" name="batch_number[]" value="' + (response.batch_number || '') + '">';
                    html += '<input type="hidden" name="expiry_date[]" value="' + (response.expiry_date || '') + '">';
                    html += '<input type="hidden" name="avz_code[]" value="' + (p.avz_item_code || '') + '">';
                    html += '<input type="number" name="quantity[]" class="form-control input-sm text-right quantity_input" placeholder="Enter quantity" step="0.01" min="0" value="' + savedQuantity + '">';
                    html += '</td>';
                    html += '<td>';
                    html += '<input type="text" name="actual_batch[]" class="form-control input-sm" placeholder="Actual Batch" value="' + (p.actual_batch ? p.actual_batch : '') + '">';
                    html += '</td>';
                    html += '<td>';
                    html += '<input type="text" name="actual_expiry[]" class="form-control input-sm date" placeholder="Actual Expiry (YYYY-MM-DD)" value="' + (p.actual_expiry ? p.actual_expiry : '') + '">';
                    html += '</td>';
                    html += '</tr>';
                    // After appending, initialize datepicker for new actual_expiry field
                    setTimeout(function(){ if ($.fn.datepicker) { $(".date").datepicker({ autoclose: true, format: 'yyyy-mm-dd' }); } }, 100);

                    // Check for existing product+batch row and update it to avoid duplicates
                    var existingInput = $('#products_tbody').find('input[name="product_id[]"][value="' + p.product_id + '"]');
                    var updated = false;
                    existingInput.each(function() {
                        var row = $(this).closest('tr');
                        var existingBatch = row.find('input[name="batch_number[]"]').val() || '';
                        if(existingBatch === (response.batch_number || '')){
                            // Update quantity input
                            row.find('input[name="quantity[]"]').val(savedQuantity);
                            updated = true;
                            return false; // break loop
                        }
                    });

                    if(!updated){
                        // If table has 'No products loaded' row, replace it
                        var firstRow = $('#products_tbody tr').first();
                        if(firstRow.find('em').length > 0) {
                            $('#products_tbody').html(html);
                        } else {
                            $('#products_tbody').append(html);
                        }
                    }

                    $('#submit_btn').prop('disabled', false);
                    $('#moveProductModal').modal('hide');
                    var msg = 'Product shelf updated successfully';
                    if(response.previous_shelf && response.previous_shelf !== '' && response.previous_shelf !== target_shelf){
                        msg = 'Product moved from ' + response.previous_shelf + ' to ' + target_shelf + ' successfully';
                    } else {
                        msg += ' to ' + target_shelf;
                    }
                    bootbox.alert(msg);
                } else {
                    bootbox.alert(response.msg || 'Error adding/moving product');
                }
            },
            error: function() {
                bootbox.alert('Error processing request. Please try again.');
            }
        });
    });
});
</script>
