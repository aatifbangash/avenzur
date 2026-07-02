<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-barcode"></i> Submit GRN - PO #<span id="po-number">Loading...</span></h2>
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

                    <!-- Section 1: Scan Barcode -->
                    <div class="panel panel-warning">
                        <div class="panel-heading">
                            <h3 class="panel-title"><i class="fa fa-barcode"></i> Scan Barcode</h3>
                        </div>
                        <div class="panel-body">
                            <div class="input-group input-group-lg">
                                <span class="input-group-addon"><i class="fa fa-barcode"></i></span>
                                <input type="text" 
                                       id="barcode-input" 
                                       class="form-control" 
                                       placeholder="Scan barcode here..." 
                                       autofocus>
                            </div>
                            <div id="scan-feedback" class="alert" style="display: none; margin-top: 15px;">
                                <span id="scan-feedback-message"></span>
                            </div>
                        </div>
                    </div>

                    <!-- Section 2: Scanned Items -->
                    <div class="panel panel-success">
                        <div class="panel-heading">
                            <h3 class="panel-title">
                                <i class="fa fa-list"></i> Scanned Items 
                                <span class="badge" id="scanned-count" style="background: white; color: #16a085;">0</span>
                            </h3>
                        </div>
                        <div class="panel-body">
                            <div class="alert alert-info" id="no-scans-message">
                                <i class="fa fa-info-circle"></i> No items scanned yet. Start scanning to add items.
                            </div>
                            
                            <div class="table-responsive" id="scanned-items-table" style="display: none;">
                                <table class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th width="5%">#</th>
                                            <th width="15%">Product Code</th>
                                            <th width="30%">Product Name</th>
                                            <th width="10%">Ordered</th>
                                            <th width="15%">Quantity</th>
                                            <th width="15%">Progress</th>
                                            <th width="10%">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody id="scanned-items-body">
                                    </tbody>
                                </table>
                            </div>

                            <div class="text-center" style="margin-top: 20px;">
                                <button type="button" class="btn btn-default btn-lg" onclick="window.location.href='<?= admin_url('purchase_order'); ?>'">
                                    <i class="fa fa-times"></i> Cancel
                                </button>
                                <button type="button" class="btn btn-success btn-lg" id="submit-grn-btn" disabled>
                                    <i class="fa fa-check"></i> Submit GRN
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
    #barcode-input {
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
    .progress {
        height: 24px;
        margin-bottom: 0;
    }
    .progress-bar {
        line-height: 24px;
        font-size: 12px;
        font-weight: bold;
    }
</style>

<script type="text/javascript">
$(document).ready(function() {
    const apiBaseUrl = '<?= $api_base_url; ?>';
    const poId = '<?= $po_id; ?>';
    
    let purchaseOrder = null;
    let scannedItems = {}; // { item_id: { item: {}, qty: number } }

    // Fetch PO data on load
    fetchPurchaseOrder();

    // Barcode scanning with Enter key
    $('#barcode-input').on('keypress', function(e) {
        if (e.which === 13) { // Enter key
            e.preventDefault();
            const barcode = $(this).val().trim();
            if (barcode) {
                processBarcodeScan(barcode);
                $(this).val('');
            }
        }
    });

    // Submit GRN
    $('#submit-grn-btn').on('click', function() {
        submitGRN();
    });

    function fetchPurchaseOrder() {
        $.ajax({
            url: apiBaseUrl + '/purchase-orders/' + poId,
            method: 'GET',
            dataType: 'json',
            success: function(response) {
                console.log('API Response:', response);
                
                // Handle both wrapped and direct responses
                let poData = null;
                if (response.success && response.data) {
                    poData = response.data;
                } else if (response.id && response.items) {
                    // Direct response without wrapper
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
                    $('#barcode-input').focus();
                } else {
                    showError('Invalid purchase order data received');
                }
            },
            error: function(xhr, status, error) {
                console.error('API Error:', xhr, status, error);
                console.error('Response Text:', xhr.responseText);
                showError('Failed to load purchase order. Please check API connection.');
            }
        });
    }

    function processBarcodeScan(barcode) {
        if (!purchaseOrder || !purchaseOrder.items) {
            showFeedback('error', 'Purchase order not loaded');
            return;
        }

        // Find item by product_code
        const item = purchaseOrder.items.find(i => i.product_code === barcode);
        
        if (!item) {
            showFeedback('error', 'Product not found in PO: ' + barcode);
            return;
        }

        const itemId = item.id;

        // First scan: add with qty starting from scanned_quantity or 1
        if (!scannedItems[itemId]) {
            const initialQty = (item.scanned_quantity && item.scanned_quantity > 0) ? parseInt(item.scanned_quantity) + 1 : 1;
            scannedItems[itemId] = {
                item: item,
                qty: initialQty
            };
            showFeedback('success', 'Added: ' + item.product_name + ' (Qty: ' + initialQty + ')');
        } else {
            scannedItems[itemId].qty++;
            showFeedback('info', 'Updated: ' + item.product_name + ' (Qty: ' + scannedItems[itemId].qty + ')');
        }

        updateScannedItemsDisplay();
    }

    function updateScannedItemsDisplay() {
        const count = Object.keys(scannedItems).length;
        $('#scanned-count').text(count);

        if (count === 0) {
            $('#no-scans-message').show();
            $('#scanned-items-table').hide();
            $('#submit-grn-btn').prop('disabled', true);
        } else {
            $('#no-scans-message').hide();
            $('#scanned-items-table').show();
            $('#submit-grn-btn').prop('disabled', false);

            const tbody = $('#scanned-items-body');
            tbody.empty();

            let index = 1;
            for (const itemId in scannedItems) {
                const scanned = scannedItems[itemId];
                const item = scanned.item;
                
                // Calculate progress
                const orderedQty = parseFloat(item.quantity || 0);
                const receivedQty = scanned.qty;
                const progressPercent = orderedQty > 0 ? Math.min(100, Math.round((receivedQty / orderedQty) * 100)) : 0;
                
                // Determine progress bar color
                let progressClass = 'progress-bar-success';
                if (progressPercent < 100) {
                    progressClass = 'progress-bar-warning';
                } else if (receivedQty > orderedQty) {
                    progressClass = 'progress-bar-danger';
                }

                const row = $(`
                    <tr>
                        <td>${index}</td>
                        <td>${item.product_code || ''}</td>
                        <td><strong>${item.product_name || ''}</strong></td>
                        <td><span class="badge badge-info">${orderedQty.toFixed(2)}</span></td>
                        <td>
                            <div class="qty-control">
                                <button type="button" class="btn btn-default qty-minus" data-item-id="${itemId}">
                                    <i class="fa fa-minus"></i>
                                </button>
                                <input type="number" 
                                       class="form-control qty-input" 
                                       data-item-id="${itemId}" 
                                       value="${scanned.qty}" 
                                       min="1">
                                <button type="button" class="btn btn-default qty-plus" data-item-id="${itemId}">
                                    <i class="fa fa-plus"></i>
                                </button>
                            </div>
                        </td>
                        <td>
                            <div class="progress">
                                <div class="progress-bar ${progressClass}" role="progressbar" 
                                     style="width: ${progressPercent}%"
                                     aria-valuenow="${progressPercent}" 
                                     aria-valuemin="0" 
                                     aria-valuemax="100">
                                    ${progressPercent}%
                                </div>
                            </div>
                        </td>
                        <td>
                            <button type="button" class="btn btn-sm btn-danger remove-item" data-item-id="${itemId}">
                                <i class="fa fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                `);
                tbody.append(row);
                index++;
            }

            bindItemControls();
        }
    }

    function bindItemControls() {
        // Plus button
        $('.qty-plus').off('click').on('click', function() {
            const itemId = $(this).data('item-id');
            if (scannedItems[itemId]) {
                scannedItems[itemId].qty++;
                updateScannedItemsDisplay();
            }
        });

        // Minus button
        $('.qty-minus').off('click').on('click', function() {
            const itemId = $(this).data('item-id');
            if (scannedItems[itemId] && scannedItems[itemId].qty > 1) {
                scannedItems[itemId].qty--;
                updateScannedItemsDisplay();
            }
        });

        // Manual input
        $('.qty-input').off('change').on('change', function() {
            const itemId = $(this).data('item-id');
            const newQty = parseInt($(this).val());
            if (scannedItems[itemId] && newQty > 0) {
                scannedItems[itemId].qty = newQty;
                updateScannedItemsDisplay();
            }
        });

        // Remove item
        $('.remove-item').off('click').on('click', function() {
            const itemId = $(this).data('item-id');
            if (confirm('Remove this item?')) {
                delete scannedItems[itemId];
                updateScannedItemsDisplay();
            }
        });
    }

    function submitGRN() {
        if (Object.keys(scannedItems).length === 0) {
            alert('No items scanned. Please scan at least one item.');
            return;
        }

        // Build payload with correct structure
        const items = [];
        for (const itemId in scannedItems) {
            const scanned = scannedItems[itemId];
            items.push({
                item_id: parseInt(itemId),
                item_code: scanned.item.product_code || '',
                received_qty: scanned.qty
            });
        }

        const payload = {
            po_id: parseInt(poId),
            items: items
        };

        console.log('Submitting payload:', payload);

        $('#submit-grn-btn').prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Submitting...');

        $.ajax({
            url: apiBaseUrl + '/purchase-orders/items/update-received-quantities',
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify(payload),
            dataType: 'json',
            success: function(response) {
                console.log('Submit Response:', response);
                
                // Handle success - API returns message property
                if (response.message && response.message.toLowerCase().includes('successfully')) {
                    alert('GRN submitted successfully!');
                    location.reload();
                } else if (response.success || response.success === true) {
                    alert('GRN submitted successfully!');
                    location.reload();
                } else {
                    alert('Error: ' + (response.message || 'Unknown error'));
                    $('#submit-grn-btn').prop('disabled', false).html('<i class="fa fa-check"></i> Submit GRN');
                }
            },
            error: function(xhr, status, error) {
                console.error('Submit Error:', xhr.responseText);
                
                // Check if it's actually a success message in error response
                try {
                    const response = JSON.parse(xhr.responseText);
                    if (response.message && response.message.toLowerCase().includes('successfully')) {
                        alert('GRN submitted successfully!');
                        location.reload();
                        return;
                    }
                } catch(e) {}
                
                alert('Failed to submit GRN. Check console for details.');
                $('#submit-grn-btn').prop('disabled', false).html('<i class="fa fa-check"></i> Submit GRN');
            }
        });
    }

    function showFeedback(type, message) {
        const feedback = $('#scan-feedback');
        const feedbackMsg = $('#scan-feedback-message');
        
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
