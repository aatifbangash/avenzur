<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<style>
/* ============================================================================
   PHARMACIST MANAGEMENT - HORIZON UI DESIGN SYSTEM
   ============================================================================ */

:root {
    --horizon-primary: #1a73e8;
    --horizon-success: #05cd99;
    --horizon-error: #f34235;
    --horizon-warning: #ff9a56;
    --horizon-secondary: #6c5ce7;
    --horizon-dark-text: #111111;
    --horizon-light-text: #7a8694;
    --horizon-bg-light: #f5f5f5;
    --horizon-bg-neutral: #e0e0e0;
    --horizon-border: #e0e0e0;
    --horizon-shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
    --horizon-shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    --horizon-shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
}

.pharmacist-container {
    background: #ffffff;
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
    padding: 24px;
}

.pharmacist-header {
    margin-bottom: 32px;
}

.pharmacist-header h1 {
    margin: 0 0 8px 0;
    font-size: 28px;
    font-weight: 600;
    color: var(--horizon-dark-text);
}

.pharmacist-header p {
    margin: 0;
    font-size: 14px;
    color: var(--horizon-light-text);
}

.filter-section {
    background: white;
    border: 1px solid var(--horizon-border);
    border-radius: 12px;
    padding: 24px;
    margin-bottom: 24px;
    box-shadow: var(--horizon-shadow-sm);
}

.filter-section h3 {
    margin: 0 0 20px 0;
    font-size: 16px;
    font-weight: 600;
    color: var(--horizon-dark-text);
}

.filter-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 16px;
}

.form-group {
    display: flex;
    flex-direction: column;
}

.form-group label {
    margin-bottom: 8px;
    font-size: 13px;
    font-weight: 500;
    color: var(--horizon-dark-text);
}

.form-group select {
    padding: 10px 12px;
    border: 1px solid var(--horizon-border);
    border-radius: 8px;
    font-size: 14px;
    color: var(--horizon-dark-text);
    background: white;
    transition: all 0.2s ease;
}

.form-group select:focus {
    outline: none;
    border-color: var(--horizon-primary);
    box-shadow: 0 0 0 3px rgba(26, 115, 232, 0.1);
}

.form-group select:disabled {
    background: var(--horizon-bg-light);
    cursor: not-allowed;
    opacity: 0.6;
}

.results-section {
    background: white;
    border: 1px solid var(--horizon-border);
    border-radius: 12px;
    padding: 24px;
    box-shadow: var(--horizon-shadow-sm);
    display: none;
}

.results-section.active {
    display: block;
}

.results-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

.results-header h3 {
    margin: 0;
    font-size: 18px;
    font-weight: 600;
    color: var(--horizon-dark-text);
}

.results-count {
    padding: 6px 12px;
    background: var(--horizon-bg-light);
    border-radius: 20px;
    font-size: 13px;
    font-weight: 500;
    color: var(--horizon-light-text);
}

.pharmacist-table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
}

.pharmacist-table thead {
    background: var(--horizon-bg-light);
}

.pharmacist-table th {
    padding: 12px 16px;
    text-align: left;
    font-size: 13px;
    font-weight: 600;
    color: var(--horizon-dark-text);
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.pharmacist-table th:first-child {
    border-radius: 8px 0 0 8px;
}

.pharmacist-table th:last-child {
    border-radius: 0 8px 8px 0;
}

.pharmacist-table tbody tr {
    border-bottom: 1px solid var(--horizon-border);
    transition: background 0.2s ease;
}

.pharmacist-table tbody tr:hover {
    background: #f9fafb;
}

.pharmacist-table td {
    padding: 16px;
    font-size: 14px;
    color: var(--horizon-dark-text);
}

.pharmacist-name {
    font-weight: 500;
}

.pharmacist-email {
    color: var(--horizon-light-text);
    font-size: 13px;
}

.pharmacist-phone {
    color: var(--horizon-light-text);
}

.empty-state {
    text-align: center;
    padding: 60px 20px;
}

.empty-state-icon {
    font-size: 64px;
    color: var(--horizon-border);
    margin-bottom: 16px;
}

.empty-state h4 {
    margin: 0 0 8px 0;
    font-size: 18px;
    font-weight: 600;
    color: var(--horizon-dark-text);
}

.empty-state p {
    margin: 0;
    font-size: 14px;
    color: var(--horizon-light-text);
}

.loading-spinner {
    display: inline-block;
    width: 16px;
    height: 16px;
    border: 2px solid var(--horizon-border);
    border-top-color: var(--horizon-primary);
    border-radius: 50%;
    animation: spin 0.6s linear infinite;
    margin-left: 8px;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}

.alert {
    padding: 12px 16px;
    border-radius: 8px;
    margin-bottom: 16px;
    font-size: 14px;
}

.alert-info {
    background: #e3f2fd;
    color: #1565c0;
    border: 1px solid #90caf9;
}

.alert-error {
    background: #ffebee;
    color: #c62828;
    border: 1px solid #ef5350;
}

.alert-success {
    background: #e8f5e9;
    color: #2e7d32;
    border: 1px solid #66bb6a;
}
</style>

<div class="pharmacist-container">
    <div class="pharmacist-header">
        <h1>Pharmacist Management</h1>
        <p>View and manage pharmacists by pharmacy and branch</p>
    </div>

    <!-- Filter Section -->
    <div class="filter-section">
        <h3>Filter Pharmacists</h3>
        <div class="filter-grid">
            <div class="form-group">
                <label for="pharmacy-select">Select Pharmacy</label>
                <select id="pharmacy-select">
                    <option value="">-- Select Pharmacy --</option>
                </select>
            </div>

            <div class="form-group">
                <label for="branch-select">Select Branch</label>
                <select id="branch-select" disabled>
                    <option value="">-- Select Branch --</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Results Section -->
    <div class="results-section" id="results-section">
        <div class="results-header">
            <h3>Pharmacists</h3>
            <span class="results-count" id="results-count">0 pharmacists</span>
        </div>

        <div id="pharmacists-table-container">
            <!-- Table will be populated here -->
        </div>
    </div>
</div>

<script>
// Use jQuery since it's already loaded in the application
jQuery(document).ready(function($) {
    'use strict';

    console.log('Pharmacist script loaded with jQuery');

    const pharmacySelect = $('#pharmacy-select');
    const branchSelect = $('#branch-select');
    const resultsSection = $('#results-section');
    const resultsCount = $('#results-count');
    const tableContainer = $('#pharmacists-table-container');

    console.log('Elements found:', {
        pharmacySelect: pharmacySelect.length > 0,
        branchSelect: branchSelect.length > 0,
        resultsSection: resultsSection.length > 0
    });

    // Load pharmacies on page load
    loadPharmacies();

    // Event listeners using jQuery
    console.log('Attaching jQuery event listeners...');
    pharmacySelect.on('change', function() {
        console.log('Pharmacy change event fired (jQuery)!', $(this).val());
        onPharmacyChange();
    });
    
    branchSelect.on('change', function() {
        console.log('Branch change event fired (jQuery)!', $(this).val());
        onBranchChange();
    });
    console.log('Event listeners attached');

    /**
     * Load all pharmacies
     */
    function loadPharmacies() {
        fetch('<?php echo site_url("admin/pharmacist/get_pharmacies"); ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: '<?= $this->security->get_csrf_token_name(); ?>=<?= $this->security->get_csrf_hash(); ?>'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.pharmacies) {
                populatePharmacyDropdown(data.pharmacies);
            } else {
                console.error('Failed to load pharmacies');
            }
        })
        .catch(error => {
            console.error('Error loading pharmacies:', error);
        });
    }

    /**
     * Populate pharmacy dropdown
     */
    function populatePharmacyDropdown(pharmacies) {
        pharmacySelect.html('<option value="">-- Select Pharmacy --</option>');
        
        pharmacies.forEach(pharmacy => {
            const option = $('<option></option>')
                .val(pharmacy.id)
                .text(pharmacy.name);
            pharmacySelect.append(option);
        });
        
        console.log('Populated pharmacies:', pharmacies.length);
    }

    /**
     * Handle pharmacy selection change
     */
    function onPharmacyChange() {
        const pharmacyId = pharmacySelect.val();
        
        console.log('Pharmacy selected:', pharmacyId);
        
        // Reset branch dropdown
        branchSelect.html('<option value="">-- Select Branch --</option>');
        branchSelect.prop('disabled', true);
        
        // Hide results
        resultsSection.removeClass('active');
        
        if (!pharmacyId) {
            return;
        }

        console.log('Fetching branches for pharmacy:', pharmacyId);

        // Load branches for selected pharmacy
        fetch('<?php echo site_url("admin/pharmacist/get_branches_by_pharmacy"); ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: '<?= $this->security->get_csrf_token_name(); ?>=<?= $this->security->get_csrf_hash(); ?>&pharmacy_id=' + encodeURIComponent(pharmacyId)
        })
        .then(response => {
            console.log('Response received:', response);
            return response.json();
        })
        .then(data => {
            console.log('Data received:', data);
            if (data.success && data.branches) {
                populateBranchDropdown(data.branches);
            } else {
                console.error('Failed to load branches');
            }
        })
        .catch(error => {
            console.error('Error loading branches:', error);
        });
    }

    /**
     * Populate branch dropdown
     */
    function populateBranchDropdown(branches) {
        branchSelect.html('<option value="">-- Select Branch --</option>');
        
        if (branches.length === 0) {
            branchSelect.append('<option value="">No branches found</option>');
            return;
        }

        branches.forEach(branch => {
            const option = $('<option></option>')
                .val(branch.id)
                .text(branch.name)
                .data('code', branch.code);
            branchSelect.append(option);
        });
        
        branchSelect.prop('disabled', false);
    }

    /**
     * Handle branch selection change
     */
    function onBranchChange() {
        const branchId = branchSelect.val();
        
        if (!branchId) {
            resultsSection.removeClass('active');
            return;
        }

        // Show loading state
        tableContainer.html('<div style="text-align: center; padding: 40px;"><span class="loading-spinner"></span> Loading pharmacists...</div>');
        resultsSection.addClass('active');

        // Load pharmacists for selected branch
        fetch('<?php echo site_url("admin/pharmacist/get_pharmacists_by_branch"); ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: '<?= $this->security->get_csrf_token_name(); ?>=<?= $this->security->get_csrf_hash(); ?>&branch_id=' + encodeURIComponent(branchId)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.pharmacists) {
                displayPharmacists(data.pharmacists);
            } else {
                showError('Failed to load pharmacists');
            }
        })
        .catch(error => {
            console.error('Error loading pharmacists:', error);
            showError('An error occurred while loading pharmacists');
        });
    }

    /**
     * Display pharmacists in table
     */
    function displayPharmacists(pharmacists) {
        if (pharmacists.length === 0) {
            tableContainer.html(`
                <div class="empty-state">
                    <div class="empty-state-icon">👨‍⚕️</div>
                    <h4>No Pharmacists Found</h4>
                    <p>There are no pharmacists assigned to this branch.</p>
                </div>
            `);
            resultsCount.text('0 pharmacists');
            return;
        }

        // Update count
        resultsCount.text(`${pharmacists.length} pharmacist${pharmacists.length !== 1 ? 's' : ''}`);

        // Build table
        let tableHTML = `
            <table class="pharmacist-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Warehouse</th>
                        <th style="text-align: center; width: 200px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
        `;

        pharmacists.forEach(pharmacist => {
            const fullName = `${pharmacist.first_name || ''} ${pharmacist.last_name || ''}`.trim() || 'N/A';
            const email = pharmacist.email || 'N/A';
            const phone = pharmacist.phone || 'N/A';
            const warehouse = pharmacist.warehouse_name || 'N/A';
            const warehouseCode = pharmacist.warehouse_code ? `(${pharmacist.warehouse_code})` : '';

            tableHTML += `
                <tr>
                    <td>${pharmacist.id}</td>
                    <td class="pharmacist-name">${fullName}</td>
                    <td>${pharmacist.username}</td>
                    <td class="pharmacist-email">${email}</td>
                    <td class="pharmacist-phone">${phone}</td>
                    <td>${warehouse} ${warehouseCode}</td>
                    <td style="text-align: center;">
                        <a href="<?php echo site_url('admin/pharmacist/add_incentive/'); ?>${pharmacist.id}" 
                           class="btn btn-sm btn-primary" 
                           style="margin-right: 5px; background: var(--horizon-primary); color: white; padding: 6px 12px; border-radius: 6px; text-decoration: none; display: inline-block;">
                            Add Incentive
                        </a>
                        <a href="<?php echo site_url('admin/pharmacist/edit_incentive/'); ?>${pharmacist.id}" 
                           class="btn btn-sm btn-secondary" 
                           style="margin-right: 5px; background: var(--horizon-secondary); color: white; padding: 6px 12px; border-radius: 6px; text-decoration: none; display: inline-block;">
                            Edit Incentive
                        </a>
                        <a href="<?php echo site_url('admin/pharmacist/download_incentive_csv/'); ?>${pharmacist.id}" 
                           class="btn btn-sm btn-download download-incentive-btn" 
                           data-pharmacist-id="${pharmacist.id}"
                           style="margin-right: 5px; background: var(--horizon-success); color: white; padding: 6px 12px; border-radius: 6px; text-decoration: none; display: none;">
                            Download CSV
                        </a>
                        <button type="button"
                           class="btn btn-sm btn-danger delete-incentive-btn" 
                           data-pharmacist-id="${pharmacist.id}"
                           style="margin-right: 5px; background: #dc3545; color: white; padding: 6px 12px; border-radius: 6px; border: none; cursor: pointer; display: none;">
                            Delete Incentive
                        </button>
                    </td>
                </tr>
            `;
        });

        tableHTML += `
                </tbody>
            </table>
        `;

        tableContainer.html(tableHTML);
        
        // Check which pharmacists have incentives and show download button
        checkIncentivesAvailability();
    }

    /**
     * Check which pharmacists have incentives and show download buttons
     */
    function checkIncentivesAvailability() {
        $('.download-incentive-btn').each(function() {
            const downloadBtn = $(this);
            const pharmacistId = downloadBtn.data('pharmacist-id');
            const deleteBtn = $(`.delete-incentive-btn[data-pharmacist-id="${pharmacistId}"]`);
            
            $.ajax({
                type: 'POST',
                url: '<?php echo site_url("admin/pharmacist/has_incentive"); ?>',
                data: {
                    '<?= $this->security->get_csrf_token_name(); ?>': '<?= $this->security->get_csrf_hash(); ?>',
                    pharmacist_id: pharmacistId
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success && response.has_incentive) {
                        downloadBtn.css('display', 'inline-block');
                        deleteBtn.css('display', 'inline-block');
                    }
                }
            });
        });
    }

    /**
     * Show error message
     */
    function showError(message) {
        tableContainer.html(`
            <div class="alert alert-error">
                ${message}
            </div>
        `);
    }

    /**
     * Handle delete incentive button click
     */
    $(document).on('click', '.delete-incentive-btn', function() {
        const btn = $(this);
        const pharmacistId = btn.data('pharmacist-id');
        
        // Confirm before deleting
        if (!confirm('Are you sure you want to delete this pharmacist\'s incentive? This action cannot be undone.')) {
            return;
        }
        
        // Disable button during request
        btn.prop('disabled', true).text('Deleting...');
        
        $.ajax({
            type: 'POST',
            url: '<?php echo site_url("admin/pharmacist/delete_incentive"); ?>',
            data: {
                '<?= $this->security->get_csrf_token_name(); ?>': '<?= $this->security->get_csrf_hash(); ?>',
                pharmacist_id: pharmacistId
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    // Hide both delete and download buttons
                    btn.hide();
                    $(`.download-incentive-btn[data-pharmacist-id="${pharmacistId}"]`).hide();
                    
                    // Show success message
                    alert('Incentive deleted successfully');
                } else {
                    alert('Error: ' + response.message);
                    btn.prop('disabled', false).text('Delete Incentive');
                }
            },
            error: function() {
                alert('An error occurred while deleting the incentive');
                btn.prop('disabled', false).text('Delete Incentive');
            }
        });
    });

});
</script>
