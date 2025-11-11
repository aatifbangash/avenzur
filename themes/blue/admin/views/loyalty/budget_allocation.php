<?php
/**
 * Loyalty Budget Allocation View
 * 
 * Purpose: Allow admins to allocate budgets from parent to child hierarchy levels
 * 
 * Features:
 * - Hierarchy navigation (Company → Pharmacy Group → Pharmacy → Branch)
 * - Distribution methods (Equal, Proportional by Spending, Proportional by Sales, Custom)
 * - Interactive sliders for allocation
 * - Real-time calculations and validation
 * - Preview before saving
 * - Allocation history and audit trail
 * 
 * Data Variables:
 * - $hierarchies: Hierarchy data with budget info
 * - $current_level: Current hierarchy level (company/group/pharmacy/branch)
 * - $current_node: Current node being allocated
 * - $parent_budget: Parent budget amount
 * - $children: Child entities to allocate to
 */
?>

<style>
/* ============================================================================
   BUDGET ALLOCATION - HORIZON UI DESIGN SYSTEM
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

.horizon-dashboard {
    background: #ffffff;
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
    padding: 0;
}

.horizon-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 24px;
    border-bottom: 1px solid var(--horizon-border);
    background: #ffffff;
}

.horizon-header-title h1 {
    margin: 0;
    font-size: 24px;
    font-weight: 600;
    color: var(--horizon-dark-text);
}

.horizon-header-title p {
    margin: 4px 0 0 0;
    font-size: 13px;
    color: var(--horizon-light-text);
}

.horizon-control-bar {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
}

.btn-primary, .btn-secondary, .btn-outline {
    padding: 8px 16px;
    border-radius: 6px;
    border: none;
    font-size: 13px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s ease;
}

.btn-primary {
    background: var(--horizon-primary);
    color: white;
}

.btn-primary:hover {
    background: #1557b0;
    box-shadow: 0 2px 8px rgba(26, 115, 232, 0.25);
}

.btn-secondary {
    background: var(--horizon-secondary);
    color: white;
}

.btn-secondary:hover {
    background: #5a4ba8;
}

.btn-outline {
    background: transparent;
    color: var(--horizon-primary);
    border: 1px solid var(--horizon-primary);
}

.btn-outline:hover {
    background: rgba(26, 115, 232, 0.05);
}

.hierarchy-breadcrumb {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 16px 24px;
    background: #f5f5f5;
    border-bottom: 1px solid var(--horizon-border);
    font-size: 13px;
    flex-wrap: wrap;
}

.breadcrumb-item {
    display: flex;
    align-items: center;
    gap: 6px;
    color: var(--horizon-light-text);
}

.breadcrumb-item.active {
    color: var(--horizon-dark-text);
    font-weight: 600;
}

.breadcrumb-item i {
    font-size: 12px;
}

.content-wrapper {
    padding: 24px;
}

.allocation-container {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 24px;
    margin-bottom: 24px;
}

@media (max-width: 1200px) {
    .allocation-container {
        grid-template-columns: 1fr;
    }
}

.allocation-panel {
    background: #ffffff;
    border: 1px solid var(--horizon-border);
    border-radius: 8px;
    padding: 20px;
    box-shadow: var(--horizon-shadow-sm);
}

.allocation-panel-title {
    font-size: 14px;
    font-weight: 600;
    color: var(--horizon-dark-text);
    margin-bottom: 16px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.allocation-panel-title i {
    color: var(--horizon-primary);
}

.form-group {
    margin-bottom: 16px;
}

.form-group label {
    display: block;
    font-size: 12px;
    font-weight: 500;
    color: var(--horizon-dark-text);
    margin-bottom: 6px;
}

.form-group input,
.form-group select {
    width: 100%;
    padding: 8px 12px;
    border: 1px solid var(--horizon-border);
    border-radius: 6px;
    font-size: 13px;
    font-family: 'Inter', sans-serif;
    transition: border-color 0.2s ease;
}

.form-group input:focus,
.form-group select:focus {
    outline: none;
    border-color: var(--horizon-primary);
    box-shadow: 0 0 0 3px rgba(26, 115, 232, 0.1);
}

.distribution-method {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.method-radio {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px;
    border-radius: 6px;
    cursor: pointer;
    transition: background 0.2s ease;
}

.method-radio:hover {
    background: rgba(26, 115, 232, 0.05);
}

.method-radio input[type="radio"] {
    width: auto;
    margin: 0;
    cursor: pointer;
    pointer-events: auto;
    -webkit-appearance: radio;
}

.method-radio label {
    margin: 0;
    font-weight: 400;
    cursor: pointer;
    flex: 1;
    pointer-events: auto;
}

/* Distribution Button Styles */
.distribution-btn {
    padding: 12px 16px;
    border: 2px solid var(--horizon-border);
    background: #ffffff;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.3s ease;
    text-align: left;
    font-family: inherit;
    font-size: 14px;
}

.distribution-btn strong {
    display: block;
    margin-bottom: 4px;
    color: var(--horizon-dark-text);
}

.distribution-btn small {
    display: block;
    color: var(--horizon-light-text);
    font-size: 12px;
}

.distribution-btn:hover {
    border-color: var(--horizon-primary);
    background: rgba(26, 115, 232, 0.05);
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.distribution-btn.active {
    border-color: var(--horizon-primary);
    background: rgba(26, 115, 232, 0.1);
    box-shadow: 0 2px 12px rgba(26, 115, 232, 0.2);
}

.distribution-btn.active strong {
    color: var(--horizon-primary);
}

.budget-summary {
    background: rgba(26, 115, 232, 0.05);
    border: 1px solid rgba(26, 115, 232, 0.2);
    border-radius: 6px;
    padding: 12px;
    margin-bottom: 16px;
}

.budget-summary-row {
    display: flex;
    justify-content: space-between;
    font-size: 13px;
    margin-bottom: 6px;
}

.budget-summary-row:last-child {
    margin-bottom: 0;
}

.budget-summary-label {
    color: var(--horizon-light-text);
    font-weight: 500;
}

.budget-summary-value {
    color: var(--horizon-dark-text);
    font-weight: 600;
}

.allocation-item {
    background: #f5f5f5;
    border: 1px solid var(--horizon-border);
    border-radius: 6px;
    padding: 14px;
    margin-bottom: 12px;
    display: flex;
    align-items: center;
    gap: 12px;
}

.allocation-item-info {
    flex: 1;
}

.allocation-item-name {
    font-size: 13px;
    font-weight: 600;
    color: var(--horizon-dark-text);
    margin-bottom: 4px;
}

.allocation-item-details {
    display: flex;
    gap: 16px;
    font-size: 12px;
    color: var(--horizon-light-text);
}

.allocation-item-slider {
    flex: 1.5;
    display: flex;
    align-items: center;
    gap: 8px;
}

.allocation-item-slider input[type="range"] {
    flex: 1;
    height: 4px;
    border-radius: 2px;
    background: var(--horizon-border);
    outline: none;
    -webkit-appearance: none;
    appearance: none;
    transition: all 0.2s ease;
}

.allocation-item-slider input[type="range"]:not(:disabled) {
    background: linear-gradient(to right, var(--horizon-primary), var(--horizon-primary)) var(--horizon-border);
    background-size: var(--value) 100%;
    background-position: left center;
    background-repeat: no-repeat;
}

.allocation-item-slider input[type="range"]:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.allocation-item-slider input[type="range"]::-webkit-slider-thumb {
    -webkit-appearance: none;
    appearance: none;
    width: 18px;
    height: 18px;
    border-radius: 50%;
    background: var(--horizon-primary);
    cursor: pointer;
    box-shadow: 0 2px 6px rgba(26, 115, 232, 0.4);
    transition: all 0.2s ease;
}

.allocation-item-slider input[type="range"]::-webkit-slider-thumb:hover:not(:disabled) {
    width: 20px;
    height: 20px;
    box-shadow: 0 4px 12px rgba(26, 115, 232, 0.6);
}

.allocation-item-slider input[type="range"]::-webkit-slider-thumb:active:not(:disabled) {
    background: #1557b0;
}

.allocation-item-slider input[type="range"]:disabled::-webkit-slider-thumb {
    background: #ccc;
    cursor: not-allowed;
    box-shadow: none;
}

.allocation-item-slider input[type="range"]:disabled {
    background: linear-gradient(to right, #f0f0f0, #f0f0f0) 0/100% 50% no-repeat;
}

.allocation-item-slider input[type="range"]::-moz-range-thumb {
    width: 18px;
    height: 18px;
    border-radius: 50%;
    background: var(--horizon-primary);
    cursor: pointer;
    border: none;
    box-shadow: 0 2px 6px rgba(26, 115, 232, 0.4);
    transition: all 0.2s ease;
}

.allocation-item-slider input[type="range"]::-moz-range-thumb:hover:not(:disabled) {
    width: 20px;
    height: 20px;
    box-shadow: 0 4px 12px rgba(26, 115, 232, 0.6);
}

.allocation-item-slider input[type="range"]::-moz-range-thumb:active:not(:disabled) {
    background: #1557b0;
}

.allocation-item-slider input[type="range"]::-moz-range-thumb:disabled {
    background: #ccc;
    cursor: not-allowed;
}

.allocation-item-slider input[type="range"]:disabled::-moz-range-thumb {
    opacity: 0.6;
}

.allocation-item-slider input[type="range"]::-moz-range-track {
    background: transparent;
    border: none;
}

.allocation-item-slider input[type="range"]::-moz-range-progress {
    background: var(--horizon-primary);
    height: 4px;
}


.allocation-item-input {
    width: 100px;
    padding: 6px 8px;
    border: 1px solid var(--horizon-border);
    border-radius: 4px;
    font-size: 12px;
    text-align: right;
}

.allocation-item-percent {
    width: 50px;
    text-align: right;
    font-size: 12px;
    font-weight: 600;
    color: var(--horizon-primary);
}

.allocation-visualization {
    margin-top: 16px;
}

.allocation-bar {
    display: flex;
    height: 32px;
    border-radius: 6px;
    overflow: hidden;
    margin-bottom: 8px;
    box-shadow: var(--horizon-shadow-sm);
}

.allocation-segment {
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 11px;
    font-weight: 600;
    transition: all 0.3s ease;
    position: relative;
}

.allocation-segment:hover {
    opacity: 0.9;
}

.allocation-segment-label {
    position: absolute;
    white-space: nowrap;
    font-size: 10px;
    font-weight: 500;
}

.allocation-legend {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
    font-size: 12px;
}

.legend-item {
    display: flex;
    align-items: center;
    gap: 6px;
}

.legend-color {
    width: 12px;
    height: 12px;
    border-radius: 2px;
}

.allocation-status {
    margin-top: 16px;
    padding: 12px;
    border-radius: 6px;
    font-size: 13px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.allocation-status.success {
    background: rgba(5, 205, 153, 0.1);
    color: var(--horizon-success);
    border: 1px solid rgba(5, 205, 153, 0.2);
}

.allocation-status.warning {
    background: rgba(255, 154, 86, 0.1);
    color: var(--horizon-warning);
    border: 1px solid rgba(255, 154, 86, 0.2);
}

.allocation-status.error {
    background: rgba(243, 66, 53, 0.1);
    color: var(--horizon-error);
    border: 1px solid rgba(243, 66, 53, 0.2);
}

.allocation-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
}

.allocation-table thead {
    background: #f5f5f5;
    border-bottom: 1px solid var(--horizon-border);
}

.allocation-table th {
    padding: 12px;
    text-align: left;
    font-size: 12px;
    font-weight: 600;
    color: var(--horizon-dark-text);
}

.allocation-table td {
    padding: 12px;
    border-bottom: 1px solid var(--horizon-border);
    font-size: 13px;
}

.allocation-table tr:hover {
    background: #f5f5f5;
}

.action-buttons {
    display: flex;
    gap: 12px;
    justify-content: flex-end;
    margin-top: 24px;
    padding-top: 20px;
    border-top: 1px solid var(--horizon-border);
}

.modal-overlay {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    z-index: 1000;
    align-items: center;
    justify-content: center;
}

.modal-overlay.active {
    display: flex;
}

.modal-content {
    background: white;
    border-radius: 8px;
    max-width: 500px;
    width: 90%;
    max-height: 80vh;
    overflow-y: auto;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
}

.modal-header {
    padding: 20px;
    border-bottom: 1px solid var(--horizon-border);
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.modal-header h2 {
    margin: 0;
    font-size: 16px;
    font-weight: 600;
}

.modal-close {
    background: none;
    border: none;
    font-size: 20px;
    cursor: pointer;
    color: var(--horizon-light-text);
    padding: 0;
}

.modal-body {
    padding: 20px;
}

.modal-footer {
    padding: 16px 20px;
    border-top: 1px solid var(--horizon-border);
    display: flex;
    gap: 8px;
    justify-content: flex-end;
}

@media (max-width: 768px) {
    .horizon-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 12px;
    }

    .allocation-container {
        grid-template-columns: 1fr;
    }

    .allocation-item {
        flex-direction: column;
        align-items: stretch;
    }

    .allocation-item-slider {
        flex-direction: column;
    }

    .allocation-item-input {
        width: 100%;
    }

    .action-buttons {
        flex-direction: column;
    }

    .btn-primary, .btn-secondary, .btn-outline {
        width: 100%;
    }
}

.badge {
    display: inline-block;
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 11px;
    font-weight: 500;
}

.badge-info {
    background: rgba(26, 115, 232, 0.15);
    color: var(--horizon-primary);
}

.badge-success {
    background: rgba(5, 205, 153, 0.15);
    color: var(--horizon-success);
}

.badge-warning {
    background: rgba(255, 154, 86, 0.15);
    color: var(--horizon-warning);
}

.badge-danger {
    background: rgba(243, 66, 53, 0.15);
    color: var(--horizon-error);
}
</style>

<!-- Main Dashboard Container -->
<div class="horizon-dashboard">
    <!-- Header Section -->
    <div class="horizon-header">
        <div class="horizon-header-title">
            <h1>Budget Allocation</h1>
            <p>Allocate budgets from parent to child hierarchy levels</p>
        </div>
        <div class="horizon-control-bar">
            <button class="btn-outline" onclick="resetAllocation()">
                <i class="fa fa-undo"></i> Reset
            </button>
            <button class="btn-primary" onclick="saveAllocation()">
                <i class="fa fa-save"></i> Save Allocation
            </button>
        </div>
    </div>

    <!-- Hierarchy Breadcrumb -->
    <div class="hierarchy-breadcrumb">
        <span class="breadcrumb-item">
            <i class="fa fa-sitemap"></i>
            <strong>Hierarchy Level:</strong>
        </span>
        <span class="breadcrumb-item" id="breadcrumb-company">
            <i class="fa fa-building"></i> Company
        </span>
        <span class="breadcrumb-item" id="breadcrumb-group" style="display: none;">
            <i class="fa fa-chevron-right"></i>
            <a href="#" onclick="changeHierarchyLevel('group'); return false;" style="color: var(--horizon-primary);">Pharmacy Group</a>
        </span>
        <span class="breadcrumb-item" id="breadcrumb-pharmacy" style="display: none;">
            <i class="fa fa-chevron-right"></i>
            <a href="#" onclick="changeHierarchyLevel('pharmacy'); return false;" style="color: var(--horizon-primary);">Pharmacy</a>
        </span>
        <span class="breadcrumb-item" id="breadcrumb-branch" style="display: none;">
            <i class="fa fa-chevron-right"></i>
            <a href="#" onclick="changeHierarchyLevel('branch'); return false;" style="color: var(--horizon-primary);">Branch</a>
        </span>
    </div>

    <!-- Main Content -->
    <div class="content-wrapper">
        <!-- Parent Budget Summary -->
        <div class="allocation-panel" style="margin-bottom: 24px;">
            <div class="allocation-panel-title">
                <i class="fa fa-wallet"></i> Parent Budget Summary
            </div>
            <div class="budget-summary">
                <div class="budget-summary-row">
                    <span class="budget-summary-label">Hierarchy Level:</span>
                    <span class="budget-summary-value" id="parent-level">Company</span>
                </div>
                <div class="budget-summary-row">
                    <span class="budget-summary-label">Entity Name:</span>
                    <span class="budget-summary-value" id="parent-name">Avenzur (Default)</span>
                </div>
                <div class="budget-summary-row">
                    <span class="budget-summary-label">Total Allocated:</span>
                    <span class="budget-summary-value" id="parent-budget">500,000 SAR</span>
                </div>
                <div class="budget-summary-row">
                    <span class="budget-summary-label">Period:</span>
                    <span class="budget-summary-value" id="parent-period">November 2025</span>
                </div>
                <div class="budget-summary-row">
                    <span class="budget-summary-label">Available to Allocate:</span>
                    <span class="budget-summary-value" id="parent-available">500,000 SAR</span>
                </div>
            </div>
        </div>

        <!-- Two Column Layout -->
        <div class="allocation-container">
            <!-- Left Panel: Distribution Method Selection -->
            <div class="allocation-panel">
                <div class="allocation-panel-title">
                    <i class="fa fa-chart-pie"></i> Distribution Method
                </div>
                <div class="form-group">
                    <label>Select how to distribute the budget:</label>
                    <div class="distribution-method" style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                        <button class="distribution-btn active" data-method="equal" onclick="switchMethod('equal')">
                            <strong>Equal Split</strong>
                            <small>Divide evenly among all children</small>
                        </button>
                        <button class="distribution-btn" data-method="spending" onclick="switchMethod('spending')">
                            <strong>Proportional to Spending</strong>
                            <small>Weight by past 30-day spending</small>
                        </button>
                        <button class="distribution-btn" data-method="sales" onclick="switchMethod('sales')">
                            <strong>Proportional to Sales</strong>
                            <small>Weight by transaction count</small>
                        </button>
                        <button class="distribution-btn" data-method="custom" onclick="switchMethod('custom')">
                            <strong>Custom</strong>
                            <small>Manually adjust each allocation</small>
                        </button>
                    </div>
                </div>

                <!-- Allocation Summary -->
                <div style="margin-top: 20px; padding-top: 20px; border-top: 1px solid var(--horizon-border);">
                    <div class="allocation-panel-title">
                        <i class="fa fa-info-circle"></i> Allocation Summary
                    </div>
                    <div class="budget-summary">
                        <div class="budget-summary-row">
                            <span class="budget-summary-label">Total Allocated:</span>
                            <span class="budget-summary-value" id="total-allocated">0 SAR</span>
                        </div>
                        <div class="budget-summary-row">
                            <span class="budget-summary-label">Remaining:</span>
                            <span class="budget-summary-value" id="total-remaining">500,000 SAR</span>
                        </div>
                        <div class="budget-summary-row">
                            <span class="budget-summary-label">Percentage Used:</span>
                            <span class="budget-summary-value" id="percentage-used">0%</span>
                        </div>
                    </div>
                    <div id="allocation-status-message" class="allocation-status success" style="display: none;">
                        <i class="fa fa-check-circle"></i>
                        <span id="status-text">All allocations are within limits</span>
                    </div>
                    
                    <!-- Debug Display -->
                    <div style="margin-top: 12px; padding: 8px; background: #f5f5f5; border-radius: 4px; font-size: 12px; font-family: monospace;">
                        <strong>Current Method:</strong> <span id="debug-method">equal</span>
                    </div>
                </div>
            </div>

            <!-- Right Panel: Child Allocations -->
            <div class="allocation-panel">
                <div class="allocation-panel-title">
                    <i class="fa fa-bars"></i> Allocate to Children
                </div>

                <div id="allocation-items-container" style="display: flex; flex-direction: column; gap: 12px;">
                    <!-- Allocation items will be dynamically inserted here -->
                    <div style="padding: 20px; text-align: center; color: var(--horizon-light-text);">
                        <i class="fa fa-inbox" style="font-size: 32px; margin-bottom: 12px; display: block;"></i>
                        <p>Select a distribution method to get started</p>
                    </div>
                </div>

                <!-- Allocation Visualization -->
                <div class="allocation-visualization">
                    <div style="font-size: 12px; font-weight: 500; color: var(--horizon-dark-text); margin-bottom: 8px;">
                        Budget Distribution Visual
                    </div>
                    <div class="allocation-bar" id="allocation-bar-container">
                        <div class="allocation-segment" style="width: 100%; background: #e0e0e0; display: flex; align-items: center; justify-content: center;">
                            <span style="color: var(--horizon-light-text);">No allocation yet</span>
                        </div>
                    </div>
                    <div class="allocation-legend" id="allocation-legend-container" style="display: none;">
                        <!-- Legend items will be dynamically inserted here -->
                    </div>
                </div>
            </div>
        </div>

        <!-- Previous Allocations Table -->
        <div class="allocation-panel" style="margin-top: 24px;">
            <div class="allocation-panel-title">
                <i class="fa fa-history"></i> Allocation History
            </div>
            <table class="allocation-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Period</th>
                        <th>From Level</th>
                        <th>To Level</th>
                        <th>Total Amount</th>
                        <th>Method</th>
                        <th>Allocated By</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="allocation-history-tbody">
                    <tr>
                        <td colspan="8" style="text-align: center; padding: 40px; color: var(--horizon-light-text);">
                            <i class="fa fa-inbox" style="font-size: 24px; margin-bottom: 8px; display: block;"></i>
                            Loading allocation history...
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Action Buttons -->
        <div class="action-buttons">
            <button class="btn-outline" onclick="cancelAllocation()">
                <i class="fa fa-times"></i> Cancel
            </button>
            <button class="btn-secondary" onclick="previewAllocation()">
                <i class="fa fa-eye"></i> Preview
            </button>
            <button class="btn-primary" onclick="saveAllocation()">
                <i class="fa fa-save"></i> Save & Allocate
            </button>
        </div>
    </div>
</div>

<!-- Preview Modal -->
<div class="modal-overlay" id="preview-modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Preview Allocation</h2>
            <button class="modal-close" onclick="closePreviewModal()">
                <i class="fa fa-times"></i>
            </button>
        </div>
        <div class="modal-body" id="preview-content">
            <!-- Preview content will be inserted here -->
        </div>
        <div class="modal-footer">
            <button class="btn-outline" onclick="closePreviewModal()">Close</button>
            <button class="btn-primary" onclick="confirmSaveAllocation()">Confirm & Save</button>
        </div>
    </div>
</div>

<script>
/**
 * Budget Allocation Management
 */

// Sample hierarchy data (would come from backend)
// Replaced with live API data
let budgetData = null;
let pharmaciesData = [];

let currentAllocation = {
    method: 'equal',
    allocations: []
};

// API Configuration
const API_BASE_URL = 'http://81.208.174.52:4000/api/v1';

/**
 * Load budget and hierarchy data on page load
 */
async function loadBudgetData() {
    try {
        const response = await fetch(`${API_BASE_URL}/discounts/budget/config/level/COMPANY`, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
            }
        });

        if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }

        const result = await response.json();
        console.log('Budget data loaded:', result);
        
        if (result && result.length > 0) {
            budgetData = result[0]; // Get first company budget
            pharmaciesData = budgetData.children || [];
            
            console.log('Pharmacies loaded:', pharmaciesData.length);
            console.log('Total budget:', budgetData.monthlyLimit);
        } else {
            alert('No budget configuration found for company');
        }
        
    } catch (error) {
        console.error('Error loading budget data:', error);
        alert('Error loading budget data: ' + error.message + '\n\nMake sure the API server is running on http://81.208.174.52:3000');
    }
}

/**
 * Switch distribution method - called from button click
 */
function switchMethod(method) {
    console.log('Switching to method:', method);
    
    // Update button states
    document.querySelectorAll('.distribution-btn').forEach(btn => {
        if (btn.dataset.method === method) {
            btn.classList.add('active');
        } else {
            btn.classList.remove('active');
        }
    });
    
    // Update current allocation method
    currentAllocation.method = method;
    
    // Update and render
    updateAllocationMethod();
}

/**
 * Initialize allocation page
 */
async function initAllocationPage() {
    console.log('Page initialized - loading budget data...');
    
    try {
        // Load budget data from API
        await loadBudgetData();
        
        // Load allocation history
        await loadAllocationHistory();
        
        // Check if data was loaded successfully
        if (!budgetData) {
            console.error('Failed to load budget data');
            return;
        }
        
        // Initial render with equal distribution
        updateAllocationMethod();
    } catch (error) {
        console.error('Error initializing page:', error);
        alert('Failed to initialize the page. Please check the console for details.');
    }
}

/**
 * Update allocation based on selected method
 */
function updateAllocationMethod() {
    try {
        if (!budgetData || !pharmaciesData.length) {
            console.log('No budget data available yet');
            return;
        }

        const method = currentAllocation.method;
        console.log('Distribution method:', method);
        
        // Update debug display
        const debugElement = document.getElementById('debug-method');
        if (debugElement) {
            debugElement.textContent = method;
            debugElement.style.color = method === 'custom' ? '#05cd99' : '#1a73e8';
            console.log('Debug display updated to:', method);
        }
        
        const totalBudget = budgetData.monthlyLimit || 0;
        const allocations = [];

        if (method === 'equal') {
            // Equal split among all pharmacies
            const perPharmacy = totalBudget / pharmaciesData.length;
            pharmaciesData.forEach((pharmacy) => {
                allocations.push({
                    id: pharmacy.id,
                    name: pharmacy.name,
                    code: pharmacy.code,
                    amount: perPharmacy,
                    percentage: (perPharmacy / totalBudget) * 100,
                    branches: pharmacy.children || []
                });
            });
        } else if (method === 'spending' || method === 'sales') {
            // For now, fall back to equal (would need spending/sales data from API)
            const perPharmacy = totalBudget / pharmaciesData.length;
            pharmaciesData.forEach((pharmacy) => {
                allocations.push({
                    id: pharmacy.id,
                    name: pharmacy.name,
                    code: pharmacy.code,
                    amount: perPharmacy,
                    percentage: (perPharmacy / totalBudget) * 100,
                    branches: pharmacy.children || []
                });
            });
        } else if (method === 'custom') {
            // Start with equal split that can be customized
            const perPharmacy = totalBudget / pharmaciesData.length;
            pharmaciesData.forEach((pharmacy) => {
                allocations.push({
                    id: pharmacy.id,
                    name: pharmacy.name,
                    code: pharmacy.code,
                    amount: perPharmacy,
                    percentage: (perPharmacy / totalBudget) * 100,
                    branches: pharmacy.children || []
                });
            });
        }

        currentAllocation.allocations = allocations;
        console.log('Allocations set, calling renderAllocationItems');
        renderAllocationItems();
        updateAllocationVisualization();
    } catch (error) {
        console.error('ERROR in updateAllocationMethod:', error);
        alert('ERROR: ' + error.message);
    }
}

/**
 * Render allocation items
 */
function renderAllocationItems() {
    const container = document.getElementById('allocation-items-container');
    if (!container) {
        console.error('ERROR: allocation-items-container not found!');
        return;
    }
    
    const method = currentAllocation.method;
    console.log('Rendering items with method:', method, 'isCustom:', method === 'custom');
    console.log('Allocations count:', currentAllocation.allocations.length);
    
    if (!currentAllocation.allocations.length) {
        container.innerHTML = '<div style="padding: 20px; text-align: center; color: var(--horizon-light-text);"><i class="fa fa-inbox" style="font-size: 32px; margin-bottom: 12px; display: block;"></i><p>No pharmacies to allocate</p></div>';
        return;
    }

    const totalBudget = budgetData.monthlyLimit || 0;

    container.innerHTML = currentAllocation.allocations.map((alloc, index) => {
        const isCustom = method === 'custom';
        const disabledAttr = isCustom ? '' : 'disabled';
        const opacityStyle = isCustom ? '1' : '0.5';
        const cursorStyle = isCustom ? 'pointer' : 'not-allowed';
        
        // Calculate per-branch amount
        const branchCount = alloc.branches.length || 0;
        const perBranch = branchCount > 0 ? alloc.amount / branchCount : 0;
        
        return `
        <div class="allocation-item">
            <div class="allocation-item-info">
                <div class="allocation-item-name">
                    ${alloc.name}
                    <span style="color: var(--horizon-light-text); font-size: 12px; margin-left: 8px;">
                        (Code: ${alloc.code})
                    </span>
                </div>
                <div class="allocation-item-details">
                    <span>Pharmacy Budget: <strong>${formatCurrency(alloc.amount)}</strong></span>
                    <span>Percentage: <strong>${alloc.percentage.toFixed(2)}%</strong></span>
                    <span>Branches: <strong>${branchCount}</strong></span>
                    ${branchCount > 0 ? `<span>Per Branch: <strong>${formatCurrency(perBranch)}</strong></span>` : ''}
                </div>
            </div>
            <div class="allocation-item-slider">
                <input type="range" min="0" max="${totalBudget}" value="${alloc.amount}" 
                       oninput="updateAllocationAmount(${index}, this.value)"
                       onchange="updateAllocationAmount(${index}, this.value)"
                       ${disabledAttr}
                       style="opacity: ${opacityStyle}; cursor: ${cursorStyle}; --value: ${(alloc.amount / totalBudget * 100)}%;">
                <input type="number" class="allocation-item-input" value="${alloc.amount.toFixed(0)}" 
                       onchange="updateAllocationAmount(${index}, this.value)"
                       oninput="updateAllocationAmount(${index}, this.value)"
                       ${disabledAttr}
                       style="opacity: ${opacityStyle}; cursor: ${cursorStyle};">
                <span class="allocation-item-percent">${alloc.percentage.toFixed(1)}%</span>
            </div>
        </div>
    `;
    }).join('');

    console.log('Setting container HTML, method:', method, 'isCustom:', method === 'custom');
    updateTotals();
    
    // Log the actual disabled state of the first slider
    setTimeout(() => {
        const firstSlider = document.querySelector('input[type="range"]');
        if (firstSlider) {
            console.log('First slider disabled state:', firstSlider.disabled, 'isCustom:', method === 'custom');
        }
    }, 0);
}

/**
 * Update allocation amount for a specific item
 */
function updateAllocationAmount(index, value) {
    const amount = parseFloat(value) || 0;
    const totalBudget = budgetData.monthlyLimit || 0;
    const maxAmount = Math.min(amount, totalBudget);
    
    currentAllocation.allocations[index].amount = maxAmount;
    currentAllocation.allocations[index].percentage = (maxAmount / totalBudget) * 100;
    
    // Update totals without full re-render for better performance
    updateTotals();
    updateAllocationVisualization();
    
    // Only re-render the specific item's display (not all items)
    updateAllocationItemDisplay(index);
    
    // Update the slider's CSS variable for visual feedback
    const sliders = document.querySelectorAll('input[type="range"]');
    if (sliders[index]) {
        sliders[index].style.setProperty('--value', `${(maxAmount / totalBudget * 100)}%`);
    }
}

/**
 * Update allocation item display without re-rendering all items
 */
function updateAllocationItemDisplay(index) {
    const alloc = currentAllocation.allocations[index];
    const item = document.querySelectorAll('.allocation-item')[index];
    
    if (item) {
        // Calculate per-branch amount
        const branchCount = alloc.branches.length || 0;
        const perBranch = branchCount > 0 ? alloc.amount / branchCount : 0;
        
        // Update the budget amount display
        const details = item.querySelector('.allocation-item-details');
        if (details) {
            details.innerHTML = `
                <span>Pharmacy Budget: <strong>${formatCurrency(alloc.amount)}</strong></span>
                <span>Percentage: <strong>${alloc.percentage.toFixed(2)}%</strong></span>
                <span>Branches: <strong>${branchCount}</strong></span>
                ${branchCount > 0 ? `<span>Per Branch: <strong>${formatCurrency(perBranch)}</strong></span>` : ''}
            `;
        }
        
        // Update slider and input values
        const slider = item.querySelector('input[type="range"]');
        const input = item.querySelector('input[type="number"]');
        const percent = item.querySelector('.allocation-item-percent');
        
        if (slider) slider.value = alloc.amount.toFixed(0);
        if (input) input.value = alloc.amount.toFixed(0);
        if (percent) percent.textContent = alloc.percentage.toFixed(1) + '%';
    }
}

/**
 * Update total allocations and status
 */
function updateTotals() {
    const totalAllocated = currentAllocation.allocations.reduce((sum, a) => sum + a.amount, 0);
    const totalBudget = budgetData.monthlyLimit || 0;
    const remaining = totalBudget - totalAllocated;
    const percentageUsed = (totalAllocated / totalBudget) * 100;

    document.getElementById('total-allocated').textContent = formatCurrency(totalAllocated);
    document.getElementById('total-remaining').textContent = formatCurrency(remaining);
    document.getElementById('percentage-used').textContent = percentageUsed.toFixed(1) + '%';

    // Update status message
    const statusDiv = document.getElementById('allocation-status-message');
    if (totalAllocated > totalBudget) {
        statusDiv.className = 'allocation-status error';
        statusDiv.style.display = 'flex';
        document.getElementById('status-text').textContent = `Allocation exceeds budget by ${formatCurrency(totalAllocated - totalBudget)}`;
    } else if (percentageUsed > 90) {
        statusDiv.className = 'allocation-status warning';
        statusDiv.style.display = 'flex';
        document.getElementById('status-text').textContent = `High allocation (${percentageUsed.toFixed(1)}% of budget used)`;
    } else {
        statusDiv.className = 'allocation-status success';
        statusDiv.style.display = 'flex';
        document.getElementById('status-text').textContent = `Allocation is within limits (${percentageUsed.toFixed(1)}% of budget)`;
    }
}

/**
 * Update allocation visualization chart
 */
function updateAllocationVisualization() {
    const colors = ['#1a73e8', '#6c5ce7', '#05cd99', '#ff9a56', '#f34235', '#00bcd4'];
    const barContainer = document.getElementById('allocation-bar-container');
    const legendContainer = document.getElementById('allocation-legend-container');
    
    if (!currentAllocation.allocations.length) {
        barContainer.innerHTML = '<div class="allocation-segment" style="width: 100%; background: #e0e0e0; display: flex; align-items: center; justify-content: center;"><span style="color: var(--horizon-light-text);">No allocation yet</span></div>';
        legendContainer.style.display = 'none';
        return;
    }

    barContainer.innerHTML = currentAllocation.allocations.map((alloc, index) => {
        const percentage = alloc.percentage;
        return `
            <div class="allocation-segment" style="width: ${percentage}%; background: ${colors[index % colors.length]};" title="${alloc.name}: ${percentage.toFixed(1)}%">
                <span class="allocation-segment-label">${percentage.toFixed(0)}%</span>
            </div>
        `;
    }).join('');

    legendContainer.innerHTML = currentAllocation.allocations.map((alloc, index) => `
        <div class="legend-item">
            <div class="legend-color" style="background: ${colors[index % colors.length]};"></div>
            <span>${alloc.name}</span>
        </div>
    `).join('');
    legendContainer.style.display = 'flex';
}

/**
 * Preview allocation
 */
function previewAllocation() {
    const totalAllocated = currentAllocation.allocations.reduce((sum, a) => sum + a.amount, 0);
    const totalBudget = budgetData ? budgetData.monthlyLimit || 0 : 0;
    
    if (totalAllocated === 0) {
        alert('Please allocate at least some budget');
        return;
    }

    const previewContent = document.getElementById('preview-content');
    previewContent.innerHTML = `
        <div class="budget-summary" style="margin-bottom: 16px;">
            <div class="budget-summary-row">
                <span class="budget-summary-label">Method:</span>
                <span class="budget-summary-value">${currentAllocation.method.replace('_', ' ').toUpperCase()}</span>
            </div>
            <div class="budget-summary-row">
                <span class="budget-summary-label">Total to Allocate:</span>
                <span class="budget-summary-value">${formatCurrency(totalBudget)}</span>
            </div>
            <div class="budget-summary-row">
                <span class="budget-summary-label">Total Allocated:</span>
                <span class="budget-summary-value">${formatCurrency(totalAllocated)}</span>
            </div>
        </div>
        <div style="margin-bottom: 16px;">
            <strong style="font-size: 13px;">Allocations:</strong>
            <table style="width: 100%; margin-top: 8px; font-size: 12px;">
                <tr style="border-bottom: 1px solid var(--horizon-border);">
                    <th style="text-align: left; padding: 8px 0;">Entity</th>
                    <th style="text-align: right; padding: 8px 0;">Amount</th>
                    <th style="text-align: right; padding: 8px 0;">%</th>
                </tr>
                ${currentAllocation.allocations.map(alloc => `
                    <tr style="border-bottom: 1px solid var(--horizon-border);">
                        <td style="padding: 8px 0;">${alloc.name}</td>
                        <td style="text-align: right; padding: 8px 0;">${formatCurrency(alloc.amount)}</td>
                        <td style="text-align: right; padding: 8px 0;">${alloc.percentage.toFixed(1)}%</td>
                    </tr>
                `).join('')}
            </table>
        </div>
    `;
    
    document.getElementById('preview-modal').classList.add('active');
}

/**
 * Close preview modal
 */
function closePreviewModal() {
    document.getElementById('preview-modal').classList.remove('active');
}

/**
 * Confirm and save allocation
 */
function confirmSaveAllocation() {
    saveAllocation();
    closePreviewModal();
}

/**
 * Save allocation - Generate allocation array and POST to API
 */
async function saveAllocation() {
    if (!budgetData || !currentAllocation.allocations.length) {
        alert('No allocations to save');
        return;
    }

    const totalBudget = budgetData.monthlyLimit || 0;
    const totalAllocated = currentAllocation.allocations.reduce((sum, a) => sum + a.amount, 0);
    
    if (totalAllocated === 0) {
        alert('Please allocate at least some budget');
        return;
    }

    if (totalAllocated > totalBudget) {
        alert('Total allocation exceeds parent budget. Please adjust allocations.');
        return;
    }

    // Get current period (YYYY-MM format)
    const currentDate = new Date();
    const currentPeriod = currentDate.toISOString().slice(0, 7); // "2025-11"
    
    // Get user ID from PHP session (fallback to 1 if not available)
    const userId = <?php echo !empty($this->session->userdata('user_id')) ? $this->session->userdata('user_id') : 1; ?>;
    const userName = "<?php echo !empty($this->session->userdata('username')) ? $this->session->userdata('username') : 'Admin'; ?>";
    
    const allocationArray = [];
    
    // Loop through each pharmacy allocation
    currentAllocation.allocations.forEach(pharmacyAlloc => {
        const branches = pharmacyAlloc.branches || [];
        const branchCount = branches.length;
        
        // Always create pharmacy-level allocation
        allocationArray.push({
            hierarchy_level: 'PHARMACY',
            parent_hierarchy: budgetData.scopeId, // company ID
            period: currentPeriod, // REQUIRED: current month
            allocated_amount: pharmacyAlloc.amount,
            allocation_method: currentAllocation.method,
            pharmacy_id: pharmacyAlloc.id,
            allocated_by_user_id: userId, // REQUIRED: user ID from session
            allocated_by_user_name: userName
        });
        
        // If pharmacy has branches, also create branch-level allocations
        if (branchCount > 0) {
            // Split pharmacy allocation equally among branches
            const perBranch = pharmacyAlloc.amount / branchCount;
            
            branches.forEach(branch => {
                allocationArray.push({
                    hierarchy_level: 'BRANCH',
                    parent_hierarchy: pharmacyAlloc.id, // pharmacy ID
                    period: currentPeriod, // REQUIRED: current month
                    allocated_amount: perBranch,
                    allocation_method: currentAllocation.method,
                    branch_id: branch.id,
                    allocated_by_user_id: userId, // REQUIRED: user ID from session
                    allocated_by_user_name: userName
                });
            });
        }
    });
    
    // POST to API endpoint
    try {
        const response = await fetch(`${API_BASE_URL}/discounts/budget/allocations/bulk`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                <?php if (!empty($_SESSION['auth_token'])): ?>
                'Authorization': 'Bearer <?php echo $_SESSION['auth_token']; ?>',
                <?php endif; ?>
            },
            body: JSON.stringify({allocations: allocationArray})
        });

        const result = await response.json();
        
        if (response.ok) {
            alert(`✓ Budget allocation saved successfully!

Total Allocated: ${formatCurrency(totalAllocated)}
Method: ${currentAllocation.method}
Pharmacies: ${currentAllocation.allocations.length}
Total Records: ${allocationArray.length}

The allocations have been saved to the system.`);
            
            // Reload allocation history to show the new allocation
            await loadAllocationHistory();
            
            // Optionally reload the page or redirect
            // window.location.reload();
        } else {
            const errorMsg = result.message || 'Failed to save allocations';
            alert('Error: ' + errorMsg);
            console.error('Server error:', result);
        }
    } catch (error) {
        alert('Network Error: ' + error.message + '\n\nPlease ensure the API server is running on http://81.208.174.52:3000');
        console.error('Request failed:', error);
    }
}

/**
 * Reset allocation
 */
function resetAllocation() {
    if (confirm('Are you sure you want to reset all allocations?')) {
        currentAllocation.method = 'equal';
        switchMethod('equal');
    }
}

/**
 * Cancel allocation
 */
function cancelAllocation() {
    if (confirm('Discard changes and go back?')) {
        window.history.back();
    }
}

/**
 * Change hierarchy level
 */
function changeHierarchyLevel(level) {
    alert('Changing to ' + level + ' level - would navigate to appropriate data');
}

/**
 * Load allocation history from API
 */
async function loadAllocationHistory() {
    try {
        const response = await fetch(`${API_BASE_URL}/discounts/budget/allocations`, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
            }
        });

        if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }

        const allocations = await response.json();
        console.log('Allocation history loaded:', allocations);
        
        renderAllocationHistory(allocations);
        
    } catch (error) {
        console.error('Error loading allocation history:', error);
        const tbody = document.getElementById('allocation-history-tbody');
        if (tbody) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="8" style="text-align: center; padding: 40px; color: var(--horizon-light-text);">
                        <i class="fa fa-exclamation-triangle" style="font-size: 24px; margin-bottom: 8px; display: block;"></i>
                        Error loading allocation history
                    </td>
                </tr>
            `;
        }
    }
}

/**
 * Render allocation history table
 */
function renderAllocationHistory(allocations) {
    const tbody = document.getElementById('allocation-history-tbody');
    
    if (!tbody) {
        console.error('allocation-history-tbody not found');
        return;
    }
    
    if (!allocations || allocations.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="8" style="text-align: center; padding: 40px; color: var(--horizon-light-text);">
                    <i class="fa fa-inbox" style="font-size: 24px; margin-bottom: 8px; display: block;"></i>
                    No allocation history found
                </td>
            </tr>
        `;
        return;
    }
    
    // Group allocations by period and creation date
    const grouped = {};
    allocations.forEach(alloc => {
        const key = `${alloc.period}_${alloc.allocatedAt}_${alloc.allocationMethod}`;
        if (!grouped[key]) {
            grouped[key] = {
                period: alloc.period,
                createdAt: alloc.allocatedAt, // Use allocatedAt as the timestamp
                method: alloc.allocationMethod,
                allocatedBy: alloc.allocatedByUserName || 'Unknown',
                allocations: []
            };
        }
        grouped[key].allocations.push(alloc);
    });
    
    // Convert to array and sort by date (newest first)
    const groupedArray = Object.values(grouped).sort((a, b) => 
        new Date(b.createdAt) - new Date(a.createdAt)
    );
    
    tbody.innerHTML = groupedArray.map(group => {
        const totalAmount = group.allocations.reduce((sum, a) => sum + parseFloat(a.allocatedAmount || 0), 0);
        const levels = [...new Set(group.allocations.map(a => a.hierarchyLevel))].join(', ');
        const count = group.allocations.length;
        
        return `
            <tr>
                <td>${new Date(group.createdAt).toLocaleString()}</td>
                <td>${group.period}</td>
                <td>COMPANY</td>
                <td>${levels} (${count})</td>
                <td><strong>${formatCurrency(totalAmount)}</strong></td>
                <td><span class="badge badge-info">${group.method}</span></td>
                <td>${group.allocatedBy}</td>
                <td>
                    <button class="btn-outline" style="padding: 4px 8px; font-size: 11px;" onclick="viewAllocationDetails('${group.period}', '${group.createdAt}')">
                        <i class="fa fa-eye"></i> View
                    </button>
                </td>
            </tr>
        `;
    }).join('');
}

/**
 * View allocation details (stub for future implementation)
 */
function viewAllocationDetails(period, createdAt) {
    alert(`View details for allocation:\nPeriod: ${period}\nDate: ${new Date(createdAt).toLocaleString()}`);
}

/**
 * Format currency
 */
function formatCurrency(value) {
    return new Intl.NumberFormat('en-US', {
        minimumFractionDigits: 0,
        maximumFractionDigits: 0
    }).format(value) + ' SAR';
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', initAllocationPage);
</script>