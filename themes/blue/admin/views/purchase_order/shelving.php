<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-archive"></i> Shelving - PO #<span id="po-number">Loading...</span></h2>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">

                <!-- Loading Spinner -->
                <div id="loading-screen" class="text-center" style="padding: 50px;">
                    <i class="fa fa-spinner fa-spin fa-3x text-primary"></i>
                    <h4 class="mt-3">Loading Purchase Order...</h4>
                </div>

                <!-- Error Display -->
                <div id="error-screen" class="alert alert-danger" style="display: none;">
                    <h4><i class="fa fa-exclamation-triangle"></i> Error</h4>
                    <p id="error-message"></p>
                    <button type="button" class="btn btn-primary" onclick="location.reload()">
                        <i class="fa fa-refresh"></i> Retry
                    </button>
                </div>

                <!-- Main Content -->
                <div id="main-content" style="display: none;">

                    <!-- Progress Steps -->
                    <div class="shelving-steps" style="margin-bottom: 30px;">
                        <div class="step-container">
                            <div class="step active" id="step-indicator-1">
                                <div class="step-number">1</div>
                                <div class="step-label">Scan Rack</div>
                            </div>
                            <div class="step-line"></div>
                            <div class="step" id="step-indicator-2">
                                <div class="step-number">2</div>
                                <div class="step-label">Scan Box</div>
                            </div>
                            <div class="step-line"></div>
                            <div class="step" id="step-indicator-3">
                                <div class="step-number">3</div>
                                <div class="step-label">Scan Products</div>
                            </div>
                            <div class="step-line"></div>
                            <div class="step" id="step-indicator-4">
                                <div class="step-number">4</div>
                                <div class="step-label">Lock Box</div>
                            </div>
                        </div>
                    </div>

                    <!-- Step 1: Scan Rack -->
                    <div class="panel panel-primary" id="step-1" style="display: block;">
                        <div class="panel-heading">
                            <h3 class="panel-title"><i class="fa fa-warehouse"></i> Step 1: Scan Rack</h3>
                        </div>
                        <div class="panel-body">
                            <div class="alert alert-info">
                                <i class="fa fa-info-circle"></i> Please scan or enter the rack number where you'll be placing the items.
                            </div>
                            <div class="input-group input-group-lg">
                                <span class="input-group-addon"><i class="fa fa-barcode"></i></span>
                                <input type="text" 
                                       id="rack-input" 
                                       class="form-control" 
                                       placeholder="Scan rack barcode..." 
                                       autofocus>
                            </div>
                            <div id="rack-feedback" class="alert" style="display: none; margin-top: 15px;">
                                <span id="rack-feedback-message"></span>
                            </div>
                            <div class="text-right" style="margin-top: 20px;">
                                <button type="button" class="btn btn-default btn-lg" onclick="window.location.href='<?= admin_url('purchase_order'); ?>'">
                                    <i class="fa fa-times"></i> Cancel
                                </button>
                                <button type="button" class="btn btn-primary btn-lg" id="next-to-box-btn" disabled>
                                    <i class="fa fa-arrow-right"></i> Next: Scan Box
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Step 2: Scan Box -->
                    <div class="panel panel-primary" id="step-2" style="display: none;">
                        <div class="panel-heading">
                            <h3 class="panel-title"><i class="fa fa-box"></i> Step 2: Scan Box/Shelf</h3>
                        </div>
                        <div class="panel-body">
                            <div class="alert alert-success">
                                <i class="fa fa-check-circle"></i> <strong>Rack:</strong> <span id="rack-display">-</span>
                            </div>
                            <div class="alert alert-info">
                                <i class="fa fa-info-circle"></i> Please scan or enter the box/shelf number within this rack.
                            </div>
                            <div class="input-group input-group-lg">
                                <span class="input-group-addon"><i class="fa fa-barcode"></i></span>
                                <input type="text" 
                                       id="box-input" 
                                       class="form-control" 
                                       placeholder="Scan box barcode...">
                            </div>
                            <div id="box-feedback" class="alert" style="display: none; margin-top: 15px;">
                                <span id="box-feedback-message"></span>
                            </div>
                            <div class="text-right" style="margin-top: 20px;">
                                <button type="button" class="btn btn-default btn-lg" id="back-to-rack-btn">
                                    <i class="fa fa-arrow-left"></i> Back
                                </button>
                                <button type="button" class="btn btn-primary btn-lg" id="next-to-products-btn" disabled>
                                    <i class="fa fa-arrow-right"></i> Next: Scan Products
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Step 3: Scan Products -->
                    <div class="panel panel-primary" id="step-3" style="display: none;">
                        <div class="panel-heading">
                            <h3 class="panel-title"><i class="fa fa-barcode"></i> Step 3: Scan Products</h3>
                        </div>
                        <div class="panel-body">
                            <div class="alert alert-success" style="margin-bottom: 10px;">
                                <strong>Location:</strong> Zone A / Rack <span id="rack-display-2">-</span> / Box <span id="box-display">-</span>
                            </div>
                            <div class="input-group input-group-lg" style="margin-bottom: 20px;">
                                <span class="input-group-addon"><i class="fa fa-barcode"></i></span>
                                <input type="text" 
                                       id="product-barcode-input" 
                                       class="form-control" 
                                       placeholder="Scan product barcode...">
                            </div>
                            <div id="product-feedback" class="alert" style="display: none; margin-bottom: 15px;">
                                <span id="product-feedback-message"></span>
                            </div>

                            <!-- Scanned Products List -->
                            <div class="alert alert-info" id="no-products-message">
                                <i class="fa fa-info-circle"></i> No products scanned yet. Start scanning to add items to this location.
                            </div>
                            
                            <div id="scanned-products-list" style="display: none;">
                                <h4>Scanned Items <span class="badge" id="products-count" style="background: #3498db;">0</span></h4>
                                <div class="table-responsive">
                                    <table class="table table-bordered table-striped">
                                        <thead>
                                            <tr>
                                                <th width="5%">#</th>
                                                <th width="15%">Product Code</th>
                                                <th width="40%">Product Name</th>
                                                <th width="10%">Ordered</th>
                                                <th width="20%">Quantity</th>
                                                <th width="10%">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody id="scanned-products-body">
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <div class="text-right" style="margin-top: 20px;">
                                <button type="button" class="btn btn-default btn-lg" id="back-to-box-btn">
                                    <i class="fa fa-arrow-left"></i> Back
                                </button>
                                <button type="button" class="btn btn-success btn-lg" id="lock-box-btn" disabled>
                                    <i class="fa fa-lock"></i> Lock Box
                                </button>
                            </div>
                        </div>
                    </div>

                </div>

            </div>
        </div>
    </div>
</div>

<style>
    .panel {
        margin-bottom: 20px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    .panel-heading {
        padding: 15px;
        font-size: 16px;
    }
    .panel-body {
        padding: 20px;
    }
    #rack-input, #box-input, #product-barcode-input {
        font-size: 18px;
        height: 50px;
    }
    .qty-control {
        display: inline-flex;
        align-items: center;
        gap: 5px;
    }
    .qty-control input {
        width: 80px;
        text-align: center;
        font-size: 16px;
        height: 36px;
    }
    .qty-control .btn {
        width: 36px;
        height: 36px;
        padding: 0;
        font-size: 18px;
    }
    
    /* Step Progress Styles */
    .shelving-steps {
        background: #f8f9fa;
        padding: 20px;
        border-radius: 8px;
    }
    .step-container {
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .step {
        display: flex;
        flex-direction: column;
        align-items: center;
        position: relative;
    }
    .step-number {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        background: #bdc3c7;
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        font-weight: bold;
        margin-bottom: 10px;
    }
    .step.active .step-number {
        background: #3498db;
    }
    .step.completed .step-number {
        background: #27ae60;
    }
    .step-label {
        font-size: 14px;
        color: #7f8c8d;
        text-align: center;
    }
    .step.active .step-label {
        color: #3498db;
        font-weight: bold;
    }
    .step.completed .step-label {
        color: #27ae60;
    }
    .step-line {
        width: 80px;
        height: 3px;
        background: #bdc3c7;
        margin: 0 10px 35px 10px;
    }
    .step.completed ~ .step-line {
        background: #27ae60;
    }
</style>

<script type="text/javascript">
$(document).ready(function() {
    const apiBaseUrl = '<?= $api_base_url; ?>';
    const poId = '<?= $po_id; ?>';
    
    let purchaseOrder = null;
    let shelvingData = {
        po_id: poId,
        zone_number: "A", // Hardcoded as Zone A
        rack_number: "",
        rack_unlock_time: "",
        box_number: "",
        box_unlock_time: "",
        status: "active",
        items: {} // { item_id: { item_code, qty } }
    };

    // Fetch PO data on load
    fetchPurchaseOrder();

    // Step 1: Rack Input
    $('#rack-input').on('keypress', function(e) {
        if (e.which === 13) { // Enter key
            e.preventDefault();
            const rack = $(this).val().trim();
            if (rack) {
                processRackScan(rack);
            }
        }
    });

    $('#rack-input').on('input', function() {
        const rack = $(this).val().trim();
        $('#next-to-box-btn').prop('disabled', !rack);
    });

    $('#next-to-box-btn').on('click', function() {
        const rack = $('#rack-input').val().trim();
        if (rack) {
            processRackScan(rack);
        }
    });

    // Step 2: Box Input
    $('#box-input').on('keypress', function(e) {
        if (e.which === 13) { // Enter key
            e.preventDefault();
            const box = $(this).val().trim();
            if (box) {
                processBoxScan(box);
            }
        }
    });

    $('#box-input').on('input', function() {
        const box = $(this).val().trim();
        $('#next-to-products-btn').prop('disabled', !box);
    });

    $('#next-to-products-btn').on('click', function() {
        const box = $('#box-input').val().trim();
        if (box) {
            processBoxScan(box);
        }
    });

    $('#back-to-rack-btn').on('click', function() {
        goToStep(1);
    });

    // Step 3: Product Scanning
    $('#product-barcode-input').on('keypress', function(e) {
        if (e.which === 13) { // Enter key
            e.preventDefault();
            const barcode = $(this).val().trim();
            if (barcode) {
                processProductScan(barcode);
                $(this).val('');
            }
        }
    });

    $('#back-to-box-btn').on('click', function() {
        goToStep(2);
    });

    $('#lock-box-btn').on('click', function() {
        confirmLockBox();
    });

    function fetchPurchaseOrder() {
        $.ajax({
            url: apiBaseUrl + '/purchase-orders/' + poId,
            method: 'GET',
            dataType: 'json',
            success: function(response) {
                console.log('API Response:', response);
                
                let poData = null;
                if (response.success && response.data) {
                    poData = response.data;
                } else if (response.id && response.items) {
                    poData = response;
                } else if (response.success === false) {
                    showError('Failed to load purchase order: ' + (response.message || 'Unknown error'));
                    return;
                }
                
                if (poData) {
                    purchaseOrder = poData;
                    $('#po-number').text(purchaseOrder.reference_no || poId);
                    $('#loading-screen').hide();
                    $('#main-content').show();
                    $('#rack-input').focus();
                } else {
                    showError('Invalid purchase order data received');
                }
            },
            error: function(xhr, status, error) {
                console.error('API Error:', xhr, status, error);
                showError('Failed to load purchase order. Please check API connection.');
            }
        });
    }

    function processRackScan(rack) {
        shelvingData.rack_number = rack;
        shelvingData.rack_unlock_time = getCurrentTimestamp();
        
        showRackFeedback('success', 'Rack scanned: ' + rack);
        setTimeout(function() {
            goToStep(2);
        }, 500);
    }

    function processBoxScan(box) {
        shelvingData.box_number = box;
        shelvingData.box_unlock_time = getCurrentTimestamp();
        
        showBoxFeedback('success', 'Box scanned: ' + box);
        setTimeout(function() {
            goToStep(3);
        }, 500);
    }

    function processProductScan(barcode) {
        if (!purchaseOrder || !purchaseOrder.items) {
            showProductFeedback('error', 'Purchase order not loaded');
            return;
        }

        // Find item by product_code
        const item = purchaseOrder.items.find(i => i.product_code === barcode);
        
        if (!item) {
            showProductFeedback('error', 'Product not found in PO: ' + barcode);
            return;
        }

        const itemId = item.id.toString();

        // Check if already at max ordered quantity
        const orderedQty = parseFloat(item.quantity || 0);
        const currentQty = shelvingData.items[itemId] ? shelvingData.items[itemId].qty : 0;

        if (currentQty >= orderedQty) {
            showProductFeedback('error', 'Cannot exceed ordered quantity (' + orderedQty + ')');
            return;
        }

        // First scan: add with qty 1
        if (!shelvingData.items[itemId]) {
            shelvingData.items[itemId] = {
                item_id: item.id.toString(),
                item_code: item.product_code || '',
                product_name: item.product_name || '',
                ordered_qty: orderedQty,
                qty: 1
            };
            showProductFeedback('success', 'Added: ' + item.product_name + ' (Qty: 1)');
        } else {
            shelvingData.items[itemId].qty++;
            showProductFeedback('info', 'Updated: ' + item.product_name + ' (Qty: ' + shelvingData.items[itemId].qty + ')');
        }

        updateProductsDisplay();
    }

    function updateProductsDisplay() {
        const count = Object.keys(shelvingData.items).length;
        $('#products-count').text(count);

        if (count === 0) {
            $('#no-products-message').show();
            $('#scanned-products-list').hide();
            $('#lock-box-btn').prop('disabled', true);
        } else {
            $('#no-products-message').hide();
            $('#scanned-products-list').show();
            $('#lock-box-btn').prop('disabled', false);

            const tbody = $('#scanned-products-body');
            tbody.empty();

            let index = 1;
            for (const itemId in shelvingData.items) {
                const item = shelvingData.items[itemId];
                
                const row = $(`
                    <tr>
                        <td>${index}</td>
                        <td>${item.item_code}</td>
                        <td><strong>${item.product_name}</strong></td>
                        <td><span class="badge badge-info">${item.ordered_qty}</span></td>
                        <td>
                            <div class="qty-control">
                                <button type="button" class="btn btn-default qty-minus" data-item-id="${itemId}">
                                    <i class="fa fa-minus"></i>
                                </button>
                                <input type="number" 
                                       class="form-control qty-input" 
                                       data-item-id="${itemId}" 
                                       value="${item.qty}" 
                                       min="1"
                                       max="${item.ordered_qty}">
                                <button type="button" class="btn btn-default qty-plus" data-item-id="${itemId}"
                                        ${item.qty >= item.ordered_qty ? 'disabled' : ''}>
                                    <i class="fa fa-plus"></i>
                                </button>
                            </div>
                        </td>
                        <td>
                            <button type="button" class="btn btn-sm btn-danger remove-product" data-item-id="${itemId}">
                                <i class="fa fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                `);
                tbody.append(row);
                index++;
            }

            bindProductControls();
        }
    }

    function bindProductControls() {
        // Plus button
        $('.qty-plus').off('click').on('click', function() {
            const itemId = $(this).data('item-id');
            if (shelvingData.items[itemId]) {
                const item = shelvingData.items[itemId];
                if (item.qty < item.ordered_qty) {
                    item.qty++;
                    updateProductsDisplay();
                }
            }
        });

        // Minus button
        $('.qty-minus').off('click').on('click', function() {
            const itemId = $(this).data('item-id');
            if (shelvingData.items[itemId] && shelvingData.items[itemId].qty > 1) {
                shelvingData.items[itemId].qty--;
                updateProductsDisplay();
            }
        });

        // Manual input
        $('.qty-input').off('change').on('change', function() {
            const itemId = $(this).data('item-id');
            const newQty = parseInt($(this).val());
            if (shelvingData.items[itemId] && newQty > 0) {
                const maxQty = shelvingData.items[itemId].ordered_qty;
                shelvingData.items[itemId].qty = Math.min(newQty, maxQty);
                updateProductsDisplay();
            }
        });

        // Remove product
        $('.remove-product').off('click').on('click', function() {
            const itemId = $(this).data('item-id');
            if (confirm('Remove this product from the list?')) {
                delete shelvingData.items[itemId];
                updateProductsDisplay();
            }
        });
    }

    function confirmLockBox() {
        const itemCount = Object.keys(shelvingData.items).length;
        if (itemCount === 0) {
            alert('No products scanned. Please scan at least one product.');
            return;
        }

        const message = 'Lock box with ' + itemCount + ' product(s)?\n\n' +
                       'Location: Zone A / Rack ' + shelvingData.rack_number + ' / Box ' + shelvingData.box_number;
        
        if (confirm(message)) {
            submitLockBox();
        }
    }

    function submitLockBox() {
        // Build items array for API
        const items = [];
        for (const itemId in shelvingData.items) {
            const item = shelvingData.items[itemId];
            items.push({
                item_id: item.item_id,
                item_code: item.item_code,
                qty: item.qty
            });
        }

        const payload = {
            po_id: shelvingData.po_id,
            zone_number: shelvingData.zone_number,
            rack_number: shelvingData.rack_number,
            rack_unlock_time: shelvingData.rack_unlock_time,
            box_number: shelvingData.box_number,
            box_unlock_time: shelvingData.box_unlock_time,
            status: shelvingData.status,
            items: items
        };

        console.log('Submitting shelving payload:', payload);

        $('#lock-box-btn').prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Locking...');

        $.ajax({
            url: apiBaseUrl + '/purchase-orders/shelving/lock_box/' + poId,
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify(payload),
            dataType: 'json',
            success: function(response) {
                console.log('Lock Box Response:', response);
                alert('Box locked successfully!');
                window.location.href = '<?= admin_url('purchase_order'); ?>';
            },
            error: function(xhr, status, error) {
                console.error('Lock Box Error:', xhr.responseText);
                
                // Check if it's actually a success
                try {
                    const response = JSON.parse(xhr.responseText);
                    if (response.message && response.message.toLowerCase().includes('success')) {
                        alert('Box locked successfully!');
                        window.location.href = '<?= admin_url('purchase_order'); ?>';
                        return;
                    }
                } catch(e) {}
                
                alert('Failed to lock box. Check console for details.');
                $('#lock-box-btn').prop('disabled', false).html('<i class="fa fa-lock"></i> Lock Box');
            }
        });
    }

    function goToStep(stepNumber) {
        // Hide all steps
        $('#step-1, #step-2, #step-3').hide();
        
        // Update step indicators
        for (let i = 1; i <= 4; i++) {
            $('#step-indicator-' + i).removeClass('active completed');
            if (i < stepNumber) {
                $('#step-indicator-' + i).addClass('completed');
            } else if (i === stepNumber) {
                $('#step-indicator-' + i).addClass('active');
            }
        }

        // Show current step
        $('#step-' + stepNumber).show();

        // Update displays and focus
        if (stepNumber === 1) {
            $('#rack-input').focus();
        } else if (stepNumber === 2) {
            $('#rack-display').text(shelvingData.rack_number);
            $('#box-input').focus();
        } else if (stepNumber === 3) {
            $('#rack-display-2').text(shelvingData.rack_number);
            $('#box-display').text(shelvingData.box_number);
            $('#product-barcode-input').focus();
            updateProductsDisplay();
        }
    }

    function getCurrentTimestamp() {
        // Generate ISO timestamp with +03:00 timezone
        const now = new Date();
        const offset = 3 * 60; // +03:00 in minutes
        const localTime = now.getTime();
        const localOffset = now.getTimezoneOffset() * 60000;
        const utc = localTime + localOffset;
        const targetTime = utc + (offset * 60000);
        const targetDate = new Date(targetTime);
        
        const year = targetDate.getFullYear();
        const month = String(targetDate.getMonth() + 1).padStart(2, '0');
        const day = String(targetDate.getDate()).padStart(2, '0');
        const hours = String(targetDate.getHours()).padStart(2, '0');
        const minutes = String(targetDate.getMinutes()).padStart(2, '0');
        const seconds = String(targetDate.getSeconds()).padStart(2, '0');
        
        return `${year}-${month}-${day}T${hours}:${minutes}:${seconds}+03:00`;
    }

    function showRackFeedback(type, message) {
        showFeedback('#rack-feedback', '#rack-feedback-message', type, message);
    }

    function showBoxFeedback(type, message) {
        showFeedback('#box-feedback', '#box-feedback-message', type, message);
    }

    function showProductFeedback(type, message) {
        showFeedback('#product-feedback', '#product-feedback-message', type, message);
    }

    function showFeedback(feedbackId, messageId, type, message) {
        const feedback = $(feedbackId);
        const feedbackMsg = $(messageId);
        
        feedback.removeClass('alert-success alert-info alert-danger');
        
        if (type === 'success') {
            feedback.addClass('alert-success');
        } else if (type === 'info') {
            feedback.addClass('alert-info');
        } else {
            feedback.addClass('alert-danger');
        }
        
        const icon = type === 'error' ? 'exclamation-circle' : (type === 'success' ? 'check-circle' : 'info-circle');
        feedbackMsg.html('<i class="fa fa-' + icon + '"></i> ' + message);
        feedback.show();
        
        setTimeout(function() {
            feedback.fadeOut();
        }, 3000);
    }

    function showError(message) {
        $('#loading-screen').hide();
        $('#error-message').text(message);
        $('#error-screen').show();
    }
});
</script>
