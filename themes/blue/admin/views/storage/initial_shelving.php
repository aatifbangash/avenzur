<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<style>
.allocation-row {
    transition: all 0.3s ease;
    border-left: 4px solid #ffc107;
    background: #fff9e6;
}

.allocation-row.confirmed {
    border-left-color: #28a745;
    background: #e8f5e9;
}

.allocation-row.confirming {
    opacity: 0.6;
    pointer-events: none;
}

.product-image-small {
    width: 50px;
    height: 50px;
    object-fit: cover;
    border-radius: 6px;
    border: 2px solid #ddd;
}

.product-placeholder-small {
    width: 50px;
    height: 50px;
    background: #f0f0f0;
    border-radius: 6px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #999;
}

.category-badge {
    display: inline-block;
    padding: 3px 10px;
    border-radius: 12px;
    font-size: 11px;
    font-weight: 600;
    background: #007bff;
    color: white;
}

.quantity-badge {
    display: inline-block;
    padding: 5px 12px;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 700;
    background: #17a2b8;
    color: white;
}

.storage-path {
    color: #6c757d;
    font-size: 12px;
    font-style: italic;
}

.storage-icon {
    color: #28a745;
    margin-right: 5px;
}

.confirm-btn {
    transition: all 0.3s;
}

.confirm-btn:hover {
    transform: scale(1.05);
}

.stats-card {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-radius: 10px;
    padding: 20px;
    margin-bottom: 20px;
}

.stat-item {
    text-align: center;
}

.stat-value {
    font-size: 32px;
    font-weight: 700;
}

.stat-label {
    font-size: 12px;
    opacity: 0.9;
    text-transform: uppercase;
}

.loading-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.7);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 9999;
}

.loading-content {
    background: white;
    padding: 40px;
    border-radius: 10px;
    text-align: center;
}

.checkmark-icon {
    color: #28a745;
    font-size: 18px;
    margin-left: 10px;
}

.warehouse-select {
    font-size: 16px;
}

.section-header {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 8px;
    margin-bottom: 20px;
    border-left: 4px solid #007bff;
}

.table-header-custom {
    background: #343a40;
    color: white;
}

.empty-state {
    text-align: center;
    padding: 60px 20px;
    color: #6c757d;
}

.empty-state i {
    font-size: 64px;
    margin-bottom: 20px;
    opacity: 0.5;
}

.action-buttons {
    position: sticky;
    top: 0;
    background: white;
    z-index: 100;
    padding: 15px 0;
    border-bottom: 2px solid #dee2e6;
    margin-bottom: 20px;
}
</style>

<div class="box">
    <div class="box-header">
        <h2 class="blue">
            <i class="fa fa-cubes"></i> Initial Shelving - Auto Allocation
        </h2>
        
    </div>

    <div class="box-content">
        <?php if($error): ?>
            <div class="alert alert-danger">
                <i class="fa fa-exclamation-triangle"></i> <?= $error ?>
            </div>
        <?php endif; ?>

        <?php if($message): ?>
            <div class="alert alert-success">
                <i class="fa fa-check-circle"></i> <?= $message ?>
            </div>
        <?php endif; ?>

        <!-- Step 1: Select Warehouse -->
        <div class="section-header">
            <h4 style="margin: 0;">
                <i class="fa fa-warehouse" style="color: #007bff;"></i> Step 1: Select Warehouse
            </h4>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="warehouse_id">
                        <strong>Warehouse:</strong>
                    </label>
                    <select id="warehouse_id" class="form-control warehouse-select">
                        <option value="">-- Select Warehouse --</option>
                        <?php foreach($warehouses as $warehouse): ?>
                            <option value="<?= $warehouse->id ?>">
                                <?= $warehouse->name ?> (<?= $warehouse->code ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label>&nbsp;</label>
                    <button id="generateAllocationBtn" class="btn btn-lg btn-primary btn-block" disabled>
                        <i class="fa fa-magic"></i> Generate Smart Allocation
                    </button>
                </div>
            </div>
        </div>

        <!-- Statistics Card -->
        <div id="statsCard" style="display: none;">
            <div class="stats-card">
                <div class="row">
                    <div class="col-md-3">
                        <div class="stat-item">
                            <div class="stat-value" id="statTotalProducts">0</div>
                            <div class="stat-label">Total Products</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-item">
                            <div class="stat-value" id="statTotalAllocations">0</div>
                            <div class="stat-label">Allocations</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-item">
                            <div class="stat-value" id="statConfirmed">0</div>
                            <div class="stat-label">Confirmed</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-item">
                            <div class="stat-value" id="statPending">0</div>
                            <div class="stat-label">Pending</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Step 2: Review & Confirm Allocations -->
        <div id="allocationSection" style="display: none;">
            <div class="section-header">
                <h4 style="margin: 0;">
                    <i class="fa fa-list-alt" style="color: #007bff;"></i> Step 2: Review & Confirm Allocations
                </h4>
            </div>

            <!-- Action Buttons -->
            <div class="action-buttons">
                <button id="confirmAllBtn" class="btn btn-success btn-lg">
                    <i class="fa fa-check-circle"></i> Confirm All Allocations
                </button>
                <button id="resetBtn" class="btn btn-secondary btn-lg" style="margin-left: 10px;">
                    <i class="fa fa-undo"></i> Reset
                </button>
                <span id="progressText" style="margin-left: 20px; font-weight: 600; color: #28a745;"></span>
            </div>

            <!-- Allocations Table -->
            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="allocationsTable">
                    <thead class="table-header-custom">
                        <tr>
                            <th style="width: 60px;">Image</th>
                            <th>Product</th>
                            <th>Category</th>
                            <th style="width: 100px;">Quantity</th>
                            <th>Storage Location</th>
                            <th style="width: 150px;">Action</th>
                        </tr>
                    </thead>
                    <tbody id="allocationsBody">
                        <!-- Rows will be populated dynamically -->
                    </tbody>
                </table>
            </div>

            <!-- Empty State -->
            <div id="emptyState" class="empty-state" style="display: none;">
                <i class="fa fa-inbox"></i>
                <h4>No Allocations Generated</h4>
                <p>Select a warehouse and click "Generate Smart Allocation" to begin</p>
            </div>
        </div>
    </div>
</div>

<!-- Loading Overlay -->
<div id="loadingOverlay" class="loading-overlay" style="display: none;">
    <div class="loading-content">
        <i class="fa fa-spinner fa-spin fa-4x text-primary"></i>
        <h4 style="margin-top: 20px;" id="loadingText">Processing...</h4>
    </div>
</div>

<script>
let allocations = [];
let confirmedCount = 0;

$(document).ready(function() {
    // Enable generate button when warehouse is selected
    $('#warehouse_id').on('change', function() {
        if ($(this).val()) {
            $('#generateAllocationBtn').prop('disabled', false);
        } else {
            $('#generateAllocationBtn').prop('disabled', true);
        }
    });

    // Generate allocation
    $('#generateAllocationBtn').on('click', function() {
        let warehouse_id = $('#warehouse_id').val();
        
        if (!warehouse_id) {
            alert('Please select a warehouse first');
            return;
        }

        showLoading('Generating smart allocation...');

        $.ajax({
            url: "<?= admin_url('storage/generate_allocation'); ?>",
            type: "POST",
            dataType: "json",
            data: {
                warehouse_id: warehouse_id,
                <?= $this->security->get_csrf_token_name(); ?>: "<?= $this->security->get_csrf_hash(); ?>"
            },
            success: function(response) {
                hideLoading();
                
                if (response.status === 'success' || response.status === 'warning') {
                    allocations = response.allocations;
                    confirmedCount = 0;
                    displayAllocations();
                    updateStats();
                    $('#statsCard').fadeIn();
                    $('#allocationSection').fadeIn();
                    
                    if (response.status === 'warning') {
                        alert(response.msg);
                    }
                } else {
                    alert(response.msg || 'Failed to generate allocation');
                }
            },
            error: function(xhr, status, error) {
                hideLoading();
                alert('Network error occurred. Please try again.');
                console.error('AJAX Error:', error);
            }
        });
    });

    // Confirm single allocation
    $(document).on('click', '.confirm-single-btn', function() {
        let index = $(this).data('index');
        confirmSingleAllocation(index);
    });

    // Confirm all allocations
    $('#confirmAllBtn').on('click', function() {
        if (confirm('Are you sure you want to confirm all allocations?')) {
            confirmAllAllocations();
        }
    });

    // Reset
    $('#resetBtn').on('click', function() {
        if (confirm('Are you sure you want to reset? All confirmed allocations will remain but the list will be cleared.')) {
            allocations = [];
            confirmedCount = 0;
            $('#allocationsBody').empty();
            $('#statsCard').hide();
            $('#allocationSection').hide();
            $('#warehouse_id').val('');
            $('#generateAllocationBtn').prop('disabled', true);
        }
    });
});

function displayAllocations() {
    let html = '';
    
    allocations.forEach((allocation, index) => {
        let imageHtml = '';
        if (allocation.image) {
            imageHtml = `<img src="<?= base_url(); ?>assets/uploads/${allocation.image}" class="product-image-small" alt="${allocation.product_name}">`;
        } else {
            imageHtml = `<div class="product-placeholder-small"><i class="fa fa-image"></i></div>`;
        }

        let rowClass = allocation.status === 'confirmed' ? 'allocation-row confirmed' : 'allocation-row';
        let actionHtml = '';
        
        if (allocation.status === 'confirmed') {
            actionHtml = `<span class="text-success"><i class="fa fa-check-circle checkmark-icon"></i> Confirmed</span>`;
        } else {
            actionHtml = `<button class="btn btn-sm btn-success confirm-single-btn confirm-btn" data-index="${index}">
                <i class="fa fa-check"></i> Confirm
            </button>`;
        }

        html += `
            <tr class="${rowClass}" data-index="${index}">
                <td>${imageHtml}</td>
                <td>
                    <strong style="color: #007bff;">${allocation.product_name}</strong><br>
                    <small style="color: #6c757d;">Code: ${allocation.product_code}</small>
                </td>
                <td>
                    <span class="category-badge">${allocation.category_name}</span>
                </td>
                <td>
                    <span class="quantity-badge">${allocation.quantity}</span>
                </td>
                <td>
                    <i class="fa fa-map-marker storage-icon"></i>
                    <strong>${allocation.storage_location_name}</strong><br>
                    <span class="storage-path">${allocation.storage_path}</span>
                </td>
                <td style="text-align: center;">
                    ${actionHtml}
                </td>
            </tr>
        `;
    });

    $('#allocationsBody').html(html);
    
    if (allocations.length === 0) {
        $('#emptyState').show();
        $('#allocationsTable').hide();
    } else {
        $('#emptyState').hide();
        $('#allocationsTable').show();
    }
}

function confirmSingleAllocation(index) {
    let allocation = allocations[index];
    let $row = $(`tr[data-index="${index}"]`);
    
    $row.addClass('confirming');

    $.ajax({
        url: "<?= admin_url('storage/confirm_allocation'); ?>",
        type: "POST",
        dataType: "json",
        data: {
            allocation: allocation,
            <?= $this->security->get_csrf_token_name(); ?>: "<?= $this->security->get_csrf_hash(); ?>"
        },
        success: function(response) {
            if (response.status === 'success') {
                allocations[index].status = 'confirmed';
                confirmedCount++;
                
                $row.removeClass('confirming').addClass('confirmed');
                $row.find('td:last').html('<span class="text-success"><i class="fa fa-check-circle checkmark-icon"></i> Confirmed</span>');
                
                updateStats();
                updateProgress();
            } else {
                alert(response.msg || 'Failed to confirm allocation');
                $row.removeClass('confirming');
            }
        },
        error: function(xhr, status, error) {
            alert('Network error occurred. Please try again.');
            console.error('AJAX Error:', error);
            $row.removeClass('confirming');
        }
    });
}

function confirmAllAllocations() {
    let pendingAllocations = allocations.filter(a => a.status !== 'confirmed');
    
    if (pendingAllocations.length === 0) {
        alert('All allocations are already confirmed');
        return;
    }

    showLoading(`Confirming ${pendingAllocations.length} allocations...`);

    $.ajax({
        url: "<?= admin_url('storage/confirm_all_allocations'); ?>",
        type: "POST",
        dataType: "json",
        data: {
            allocations: pendingAllocations,
            <?= $this->security->get_csrf_token_name(); ?>: "<?= $this->security->get_csrf_hash(); ?>"
        },
        success: function(response) {
            hideLoading();
            
            if (response.status === 'success') {
                // Mark all as confirmed
                allocations.forEach(a => a.status = 'confirmed');
                confirmedCount = allocations.length;
                
                displayAllocations();
                updateStats();
                updateProgress();
                
                alert(response.msg);
            } else {
                alert(response.msg || 'Failed to confirm allocations');
            }
        },
        error: function(xhr, status, error) {
            hideLoading();
            alert('Network error occurred. Please try again.');
            console.error('AJAX Error:', error);
        }
    });
}

function updateStats() {
    let uniqueProducts = [...new Set(allocations.map(a => a.product_id))].length;
    let pending = allocations.filter(a => a.status !== 'confirmed').length;
    
    $('#statTotalProducts').text(uniqueProducts);
    $('#statTotalAllocations').text(allocations.length);
    $('#statConfirmed').text(confirmedCount);
    $('#statPending').text(pending);
}

function updateProgress() {
    if (confirmedCount === allocations.length) {
        $('#progressText').html('<i class="fa fa-check-circle"></i> All allocations confirmed!');
        $('#confirmAllBtn').prop('disabled', true);
    } else {
        $('#progressText').text(`${confirmedCount} of ${allocations.length} confirmed`);
    }
}

function showLoading(text) {
    $('#loadingText').text(text);
    $('#loadingOverlay').fadeIn();
}

function hideLoading() {
    $('#loadingOverlay').fadeOut();
}
</script>
