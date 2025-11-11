<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<style>
:root {
    --horizon-primary: #1a73e8;
    --horizon-success: #05cd99;
    --horizon-error: #f34235;
    --horizon-warning: #ff9a56;
    --horizon-secondary: #6c5ce7;
    --horizon-dark-text: #111111;
    --horizon-light-text: #7a8694;
    --horizon-bg-light: #f5f5f5;
    --horizon-border: #e0e0e0;
}

.incentive-container {
    background: white;
    padding: 24px;
}

.incentive-header {
    margin-bottom: 24px;
}

.incentive-header h2 {
    margin: 0 0 8px 0;
    font-size: 24px;
    font-weight: 600;
    color: var(--horizon-dark-text);
}

.pharmacist-info {
    background: var(--horizon-bg-light);
    padding: 16px;
    border-radius: 8px;
    margin-bottom: 24px;
}

.pharmacist-info h3 {
    margin: 0 0 8px 0;
    font-size: 16px;
    font-weight: 600;
}

.search-section {
    margin-bottom: 24px;
}

.search-section label {
    display: block;
    margin-bottom: 8px;
    font-weight: 500;
    font-size: 14px;
}

#product_search {
    width: 100%;
    padding: 12px;
    border: 1px solid var(--horizon-border);
    border-radius: 8px;
    font-size: 14px;
}

.incentive-table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
    border: 1px solid var(--horizon-border);
    border-radius: 8px;
    overflow: hidden;
}

.incentive-table thead {
    background: var(--horizon-bg-light);
}

.incentive-table th {
    padding: 12px;
    text-align: left;
    font-size: 13px;
    font-weight: 600;
    color: var(--horizon-dark-text);
    border-bottom: 2px solid var(--horizon-border);
}

.incentive-table td {
    padding: 12px;
    font-size: 14px;
    border-bottom: 1px solid var(--horizon-border);
}

.incentive-table tbody tr:last-child td {
    border-bottom: none;
}

.incentive-table input[type="text"],
.incentive-table input[type="date"],
.incentive-table input[type="number"] {
    width: 100%;
    padding: 8px;
    border: 1px solid var(--horizon-border);
    border-radius: 6px;
    font-size: 14px;
}

.btn-remove {
    background: var(--horizon-error);
    color: white;
    border: none;
    padding: 6px 12px;
    border-radius: 6px;
    cursor: pointer;
    font-size: 13px;
}

.btn-remove:hover {
    opacity: 0.9;
}

.empty-state {
    text-align: center;
    padding: 40px;
    color: var(--horizon-light-text);
}

.btn-save {
    background: var(--horizon-success);
    color: white;
    border: none;
    padding: 12px 24px;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    margin-top: 24px;
}

.btn-save:hover {
    opacity: 0.9;
}

.btn-cancel {
    background: var(--horizon-light-text);
    color: white;
    border: none;
    padding: 12px 24px;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    margin-top: 24px;
    margin-left: 12px;
}

.btn-cancel:hover {
    opacity: 0.9;
}
</style>

<div class="incentive-container">
    <div class="incentive-header">
        <h2>Edit Incentive</h2>
        <p>Modify products with incentive percentages for the pharmacist</p>
    </div>

    <div class="pharmacist-info">
        <h3>Pharmacist Information</h3>
        <p><strong>Name:</strong> <?php echo $pharmacist->first_name . ' ' . $pharmacist->last_name; ?></p>
        <p><strong>Email:</strong> <?php echo $pharmacist->email; ?></p>
        <p><strong>Warehouse:</strong> <?php echo $pharmacist->warehouse_name; ?> (<?php echo $pharmacist->warehouse_code; ?>)</p>
    </div>

    <div class="search-section">
        <label for="product_search">Search Product</label>
        <input type="text" 
               id="product_search" 
               placeholder="Type product name or code to search..."
               autocomplete="off">
    </div>

    <div class="table-section">
        <table class="incentive-table">
            <thead>
                <tr>
                    <th>Product Name</th>
                    <th>Batch Number</th>
                    <th>Expiry Date</th>
                    <th>Supplier</th>
                    <th style="width: 150px;">Incentive %</th>
                    <th style="width: 100px; text-align: center;">Actions</th>
                </tr>
            </thead>
            <tbody id="incentive-tbody">
                <tr>
                    <td colspan="6" class="empty-state">
                        No products added yet. Use the search bar above to add products.
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <div style="margin-top: 24px;">
        <button type="button" class="btn-save" onclick="saveIncentives(event)">Update Incentives</button>
        <a href="<?php echo site_url('admin/pharmacist'); ?>" class="btn-cancel">Cancel</a>
    </div>
</div>

<!-- Batch Selection Modal -->
<div class="modal fade" id="batchModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" style="min-width:800px !important;" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Select Batch</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <!-- Batch table will be populated here -->
            </div>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    'use strict';

    const incentiveId = <?php echo $incentive->id; ?>;
    const pharmacistId = <?php echo $pharmacist->id; ?>;
    const pharmacistWarehouseId = <?php echo $pharmacist->warehouse_id; ?>;
    const warehouseId = pharmacistWarehouseId; // Use branch warehouse directly
    let incentiveItems = [];
    let itemCounter = 0;

    console.log('Pharmacist warehouse ID:', pharmacistWarehouseId);

    // Pre-populate existing items
    <?php if (!empty($items)): ?>
    const existingItems = <?php echo json_encode($items); ?>;
    existingItems.forEach(function(item) {
        itemCounter++;
        incentiveItems.push({
            id: itemCounter,
            product_id: item.product_id,
            product_name: item.product_name,
            product_code: item.product_code,
            batch_number: item.batch_number || '',
            expiry_date: item.expiry_date || '',
            supplier_id: item.supplier_id,
            supplier_name: item.supplier_id || 'N/A',
            incentive_percentage: parseFloat(item.incentive_percentage) || 0
        });
    });
    renderTable();
    <?php endif; ?>

    // Initialize product search autocomplete
    $("#product_search").autocomplete({
        source: function(request, response) {
            $.ajax({
                url: '<?php echo site_url("admin/pharmacist/suggestions"); ?>',
                dataType: 'json',
                data: {
                    term: request.term
                },
                success: function(data) {
                    if(data[0].id != 0){
                        $(this).removeClass('ui-autocomplete-loading');
                        response(data);
                    }
                }
            });
        },
        minLength: 2,
        select: function(event, ui) {
            if (ui.item.id !== 0) {
                // Get batches for this product
                getBatchesForProduct(ui.item);
            }
            $(this).val('');
            return false;
        }
    });

    /**
     * Get batches for selected product
     */
    function getBatchesForProduct(item) {
        console.log('Getting batches for product:', item);
        console.log('Warehouse ID:', warehouseId);
        
        $.ajax({
            type: 'get',
            url: '<?= admin_url('products/get_avz_item_code_details'); ?>',
            dataType: "json",
            data: {
                item_id: item.item_id,
                warehouse_id: warehouseId
            },
            success: function (data) {
                console.log('Batch data received:', data);
                console.log('Data length:', data ? data.length : 'null');
                console.log('Data type:', typeof data);
                
                if (data && data.length > 1) {
                    // Multiple batches - show modal
                    showBatchModal(data);
                } else if (data && data.length == 1) {
                    // Single batch - add directly
                    addIncentiveItem(data[0]);
                } else {
                    // No items found in this warehouse
                    bootbox.alert('No records found for this product in warehouse ' + warehouseId + '.');
                }
                
                $('#product_search').val('');
            },
            error: function (xhr, status, error) {
                console.error('AJAX error:', error);
                console.error('Response:', xhr.responseText);
                bootbox.alert('An error occurred while fetching the item details.');
            }
        });
    }

    /**
     * Show batch selection modal
     */
    function showBatchModal(data) {
        var modalBody = $('#batchModal .modal-body');
        modalBody.empty();

        // Create table with batches
        var table = `
            <table class="table table-bordered table-hover">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Product</th>
                        <th>Supplier</th>
                        <th>Batch No</th>
                        <th>Expiry</th>
                        <th>Available Qty</th>
                    </tr>
                </thead>
                <tbody id="batchTableBody"></tbody>
            </table>
        `;

        modalBody.append(table);

        // Populate table with batches
        data.forEach(function (item, index) {
            var row = `
                <tr style="cursor:pointer;" class="batch-row" data-index="${index}">
                    <td>${index + 1}</td>
                    <td>${item.row.product_name} (${item.row.product_code})</td>
                    <td>${item.row.supplier || 'N/A'}</td>
                    <td>${item.row.batchno || ''}</td>
                    <td>${item.row.expiry || ''}</td>
                    <td>${item.total_quantity || 0}</td>
                </tr>
            `;
            $('#batchTableBody').append(row);
        });

        // Show modal
        $('#batchModal').modal('show');

        // Handle row click
        $('#batchTableBody').off('click').on('click', 'tr.batch-row', function () {
            var index = $(this).data('index');
            var selectedItem = data[index];
            
            $('#batchModal').modal('hide');
            addIncentiveItem(selectedItem);
        });
    }

    /**
     * Add incentive item to table
     */
    function addIncentiveItem(itemData) {
        console.log('Adding incentive item:', itemData);
        
        itemCounter++;
        
        const row = {
            id: itemCounter,
            product_id: itemData.row.product_id,
            product_name: itemData.row.product_name,
            product_code: itemData.row.product_code,
            batch_number: itemData.row.batchno || '',
            expiry_date: itemData.row.expiry || '',
            supplier_id: itemData.row.supplier_id || null,
            supplier_name: itemData.row.supplier || 'N/A',
            incentive_percentage: 0
        };
        
        incentiveItems.push(row);
        renderTable();
    }

    /**
     * Render incentive table
     */
    function renderTable() {
        const tbody = $('#incentive-tbody');
        
        if (incentiveItems.length === 0) {
            tbody.html(`
                <tr>
                    <td colspan="6" class="empty-state">
                        No products added yet. Use the search bar above to add products.
                    </td>
                </tr>
            `);
            return;
        }

        let html = '';
        incentiveItems.forEach((item, index) => {
            html += `
                <tr>
                    <td>${item.product_name} <small>(${item.product_code})</small></td>
                    <td>${item.batch_number || ''}</td>
                    <td>${item.expiry_date || ''}</td>
                    <td>${item.supplier_name || 'N/A'}</td>
                    <td>
                        <input type="number" 
                               step="0.01" 
                               min="0" 
                               max="100" 
                               value="${item.incentive_percentage || 0}" 
                               onchange="updateItem(${index}, 'incentive_percentage', this.value)"
                               placeholder="0.00">
                    </td>
                    <td style="text-align: center;">
                        <button type="button" class="btn-remove" onclick="removeItem(${index})">Remove</button>
                    </td>
                </tr>
            `;
        });

        tbody.html(html);
    }

    /**
     * Update item property
     */
    window.updateItem = function(index, field, value) {
        if (incentiveItems[index]) {
            incentiveItems[index][field] = value;
        }
    };

    /**
     * Remove item from table
     */
    window.removeItem = function(index) {
        if (confirm('Are you sure you want to remove this product?')) {
            incentiveItems.splice(index, 1);
            renderTable();
        }
    };

    /**
     * Save incentives (Update mode)
     */
    window.saveIncentives = function(event) {
        if (incentiveItems.length === 0) {
            alert('Please add at least one product before saving.');
            return;
        }

        // Validate that all items have incentive percentage
        const invalidItems = incentiveItems.filter(item => !item.incentive_percentage || item.incentive_percentage <= 0);
        if (invalidItems.length > 0) {
            alert('Please enter incentive percentage for all products.');
            return;
        }

        const data = {
            '<?= $this->security->get_csrf_token_name(); ?>': '<?= $this->security->get_csrf_hash(); ?>',
            incentive_id: incentiveId,
            pharmacist_id: pharmacistId,
            branch_code: '<?php echo $pharmacist->warehouse_code; ?>',
            warehouse_id: warehouseId,
            items: incentiveItems
        };

        console.log('Updating incentives:', data);
        
        // Disable button and show loading
        const saveBtn = event ? event.target : document.querySelector('.btn-save');
        saveBtn.disabled = true;
        saveBtn.textContent = 'Updating...';
        
        // Send to server (update endpoint)
        $.ajax({
            type: 'POST',
            url: '<?php echo site_url("admin/pharmacist/update_incentive"); ?>',
            dataType: 'json',
            data: data,
            success: function(response) {
                console.log('Update response:', response);
                
                if (response.success) {
                    alert('Incentives updated successfully!');
                    window.location.href = '<?php echo site_url("admin/pharmacist"); ?>';
                } else {
                    alert('Error: ' + (response.message || 'Failed to update incentives'));
                    saveBtn.disabled = false;
                    saveBtn.textContent = 'Update Incentives';
                }
            },
            error: function(xhr, status, error) {
                console.error('Update error:', error);
                alert('Error updating incentives: ' + error);
                saveBtn.disabled = false;
                saveBtn.textContent = 'Update Incentives';
            }
        });
    };
});
</script>
