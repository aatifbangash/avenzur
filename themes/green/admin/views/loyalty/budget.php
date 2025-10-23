<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<style>
/* Budget Color System - Following instruction guidelines */
:root {
    --budget-safe: #10B981;
    --budget-warning: #F59E0B;
    --budget-alert: #FB923C;
    --budget-danger: #EF4444;
    --budget-exceeded: #991B1B;
    --budget-company: #3B82F6;
    --budget-group: #8B5CF6;
    --budget-pharmacy: #EC4899;
    --budget-branch: #06B6D4;
}

/* Budget Card Styles */
.budget-card {
    background: white;
    border-radius: 8px;
    padding: 20px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    transition: all 0.3s ease-out;
    height: 100%;
}

.budget-card:hover {
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    transform: translateY(-2px);
}

.budget-card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 16px;
}

.budget-card-title {
    font-size: 14px;
    font-weight: 500;
    color: #6B7280;
    margin: 0;
}

.budget-card-status {
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
}

.status-safe { background: #D1FAE5; color: #065F46; }
.status-warning { background: #FEF3C7; color: #92400E; }
.status-alert { background: #FED7AA; color: #9A3412; }
.status-danger { background: #FEE2E2; color: #991B1B; }
.status-exceeded { background: #991B1B; color: white; }

/* Budget Meter - Circular Progress */
.budget-meter-container {
    display: flex;
    flex-direction: column;
    align-items: center;
    margin: 20px 0;
}

.budget-meter-circle {
    position: relative;
    width: 120px;
    height: 120px;
}

.budget-meter-circle svg {
    transform: rotate(-90deg);
}

.budget-meter-percentage {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    font-size: 24px;
    font-weight: 700;
    color: #1F2937;
}

/* Budget Amounts */
.budget-amounts {
    display: flex;
    flex-direction: column;
    gap: 8px;
    margin-top: 16px;
}

.budget-amount-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 13px;
}

.budget-amount-label {
    color: #6B7280;
    font-weight: 500;
}

.budget-amount-value {
    font-weight: 600;
    color: #1F2937;
}

.budget-amount-value.positive {
    color: var(--budget-safe);
}

.budget-amount-value.negative {
    color: var(--budget-danger);
}

/* Hierarchy Selector */
.hierarchy-selector {
    background: white;
    border-radius: 8px;
    padding: 20px;
    margin-bottom: 20px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.hierarchy-breadcrumb {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 16px;
    font-size: 14px;
    color: #6B7280;
}

.hierarchy-breadcrumb .active {
    color: #1F2937;
    font-weight: 600;
}

.hierarchy-level-tabs {
    display: flex;
    gap: 8px;
    border-bottom: 2px solid #E5E7EB;
    margin-bottom: 20px;
}

.hierarchy-tab {
    padding: 10px 20px;
    border: none;
    background: transparent;
    color: #6B7280;
    font-weight: 500;
    cursor: pointer;
    border-bottom: 2px solid transparent;
    margin-bottom: -2px;
    transition: all 0.2s ease-out;
}

.hierarchy-tab:hover {
    color: #1F2937;
}

.hierarchy-tab.active {
    color: #3B82F6;
    border-bottom-color: #3B82F6;
}

/* Budget Allocation Form */
.allocation-section {
    background: white;
    border-radius: 8px;
    padding: 24px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    margin-bottom: 20px;
}

.allocation-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    padding-bottom: 16px;
    border-bottom: 2px solid #E5E7EB;
}

.allocation-header h3 {
    margin: 0;
    font-size: 18px;
    font-weight: 600;
    color: #1F2937;
}

.parent-budget-info {
    background: #F3F4F6;
    padding: 12px 16px;
    border-radius: 6px;
    margin-bottom: 20px;
}

.parent-budget-info .label {
    font-size: 12px;
    color: #6B7280;
    font-weight: 500;
}

.parent-budget-info .amount {
    font-size: 20px;
    font-weight: 700;
    color: #1F2937;
    margin-top: 4px;
}

/* Distribution Method Selector */
.distribution-methods {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 12px;
    margin-bottom: 24px;
}

.distribution-method {
    position: relative;
    display: flex;
    align-items: center;
    padding: 12px 16px;
    border: 2px solid #E5E7EB;
    border-radius: 6px;
    cursor: pointer;
    transition: all 0.2s ease-out;
}

.distribution-method:hover {
    border-color: #3B82F6;
    background: #EFF6FF;
}

.distribution-method input[type="radio"] {
    margin-right: 10px;
}

.distribution-method.active {
    border-color: #3B82F6;
    background: #EFF6FF;
}

.distribution-method-label {
    display: flex;
    flex-direction: column;
}

.distribution-method-label .title {
    font-weight: 600;
    color: #1F2937;
    font-size: 14px;
}

.distribution-method-label .desc {
    font-size: 11px;
    color: #6B7280;
    margin-top: 2px;
}

/* Child Allocation Inputs */
.child-allocations {
    margin-bottom: 24px;
}

.allocation-item {
    display: grid;
    grid-template-columns: 2fr 3fr 150px 80px;
    gap: 16px;
    align-items: center;
    padding: 16px;
    border: 1px solid #E5E7EB;
    border-radius: 6px;
    margin-bottom: 12px;
    transition: all 0.2s ease-out;
}

.allocation-item:hover {
    background: #F9FAFB;
    border-color: #3B82F6;
}

.allocation-item-name {
    font-weight: 600;
    color: #1F2937;
    font-size: 14px;
}

.allocation-slider-container {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.allocation-slider {
    width: 100%;
    height: 6px;
    border-radius: 3px;
    background: #E5E7EB;
    outline: none;
    -webkit-appearance: none;
}

.allocation-slider::-webkit-slider-thumb {
    -webkit-appearance: none;
    appearance: none;
    width: 18px;
    height: 18px;
    border-radius: 50%;
    background: #3B82F6;
    cursor: pointer;
}

.allocation-slider::-moz-range-thumb {
    width: 18px;
    height: 18px;
    border-radius: 50%;
    background: #3B82F6;
    cursor: pointer;
    border: none;
}

.allocation-slider-value {
    font-size: 11px;
    color: #6B7280;
}

.allocation-input-container {
    position: relative;
}

.allocation-input {
    width: 100%;
    padding: 8px 12px;
    border: 1px solid #D1D5DB;
    border-radius: 6px;
    font-size: 14px;
    font-weight: 600;
    color: #1F2937;
}

.allocation-input:focus {
    outline: none;
    border-color: #3B82F6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.allocation-percentage {
    font-size: 16px;
    font-weight: 700;
    color: #1F2937;
    text-align: center;
}

/* Allocation Visualization */
.allocation-visualization {
    margin: 24px 0;
    padding: 20px;
    background: #F9FAFB;
    border-radius: 8px;
}

.allocation-viz-label {
    font-size: 12px;
    font-weight: 600;
    color: #6B7280;
    margin-bottom: 12px;
    text-transform: uppercase;
}

.allocation-bar {
    display: flex;
    height: 40px;
    border-radius: 6px;
    overflow: hidden;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.allocation-bar-segment {
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 12px;
    font-weight: 600;
    transition: all 0.3s ease-out;
    cursor: pointer;
}

.allocation-bar-segment:hover {
    opacity: 0.85;
}

.allocation-bar-remaining {
    background: #D1D5DB;
    color: #6B7280;
}

.allocation-legend {
    display: flex;
    flex-wrap: wrap;
    gap: 16px;
    margin-top: 12px;
}

.allocation-legend-item {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 12px;
}

.allocation-legend-color {
    width: 16px;
    height: 16px;
    border-radius: 3px;
}

/* Summary and Actions */
.allocation-summary {
    background: #F9FAFB;
    padding: 16px;
    border-radius: 6px;
    margin-bottom: 20px;
}

.summary-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 8px 0;
    font-size: 14px;
}

.summary-row.total {
    font-weight: 700;
    font-size: 16px;
    border-top: 2px solid #D1D5DB;
    padding-top: 12px;
    margin-top: 8px;
}

.summary-label {
    color: #6B7280;
}

.summary-value {
    font-weight: 600;
    color: #1F2937;
}

.summary-value.warning {
    color: var(--budget-warning);
}

.summary-value.error {
    color: var(--budget-danger);
}

/* Validation Messages */
.validation-message {
    padding: 12px 16px;
    border-radius: 6px;
    margin-bottom: 16px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.validation-message.error {
    background: #FEE2E2;
    color: #991B1B;
    border-left: 4px solid #EF4444;
}

.validation-message.warning {
    background: #FEF3C7;
    color: #92400E;
    border-left: 4px solid #F59E0B;
}

.validation-message.success {
    background: #D1FAE5;
    color: #065F46;
    border-left: 4px solid #10B981;
}

/* Action Buttons */
.budget-actions {
    display: flex;
    justify-content: flex-end;
    gap: 12px;
    padding-top: 20px;
    border-top: 2px solid #E5E7EB;
}

.btn-budget {
    padding: 10px 24px;
    border-radius: 6px;
    font-weight: 600;
    font-size: 14px;
    border: none;
    cursor: pointer;
    transition: all 0.2s ease-out;
}

.btn-budget-secondary {
    background: #F3F4F6;
    color: #374151;
}

.btn-budget-secondary:hover {
    background: #E5E7EB;
}

.btn-budget-primary {
    background: #3B82F6;
    color: white;
}

.btn-budget-primary:hover {
    background: #2563EB;
}

.btn-budget-primary:disabled {
    background: #9CA3AF;
    cursor: not-allowed;
}

/* Period Selector */
.period-selector {
    display: flex;
    gap: 8px;
    margin-bottom: 20px;
}

.period-selector .btn {
    padding: 8px 16px;
    border: 1px solid #D1D5DB;
    background: white;
    color: #6B7280;
    font-weight: 500;
    border-radius: 6px;
    cursor: pointer;
    transition: all 0.2s ease-out;
}

.period-selector .btn:hover {
    border-color: #3B82F6;
    color: #3B82F6;
}

.period-selector .btn.active {
    background: #3B82F6;
    color: white;
    border-color: #3B82F6;
}

/* Budget Limits Table */
.limit-input {
    width: 100%;
    padding: 6px 10px;
    border: 1px solid #D1D5DB;
    border-radius: 4px;
    font-size: 13px;
    font-weight: 500;
}

.limit-input:focus {
    outline: none;
    border-color: #3B82F6;
    box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.1);
}

.limit-select {
    width: 100%;
    padding: 6px 10px;
    border: 1px solid #D1D5DB;
    border-radius: 4px;
    font-size: 13px;
    background: white;
}

.limit-select:focus {
    outline: none;
    border-color: #3B82F6;
}

#budgetLimitsTable thead th {
    background: #F3F4F6;
    font-weight: 600;
    color: #374151;
    font-size: 13px;
    padding: 12px;
}

#budgetLimitsTable tbody td {
    padding: 12px;
    vertical-align: middle;
}

.limit-row {
    transition: background 0.2s ease-out;
}

.limit-row:hover {
    background: #F9FAFB;
}

.limit-badge {
    display: inline-block;
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
}

.limit-badge.company { background: #DBEAFE; color: #1E40AF; }
.limit-badge.group { background: #EDE9FE; color: #6B21A8; }
.limit-badge.pharmacy { background: #FCE7F3; color: #BE185D; }
.limit-badge.branch { background: #CFFAFE; color: #0E7490; }

/* Responsive Design */
@media (max-width: 768px) {
    .allocation-item {
        grid-template-columns: 1fr;
        gap: 12px;
    }
    
    .distribution-methods {
        grid-template-columns: 1fr;
    }
    
    .budget-actions {
        flex-direction: column;
    }
    
    .btn-budget {
        width: 100%;
    }
    
    #budgetLimitsTable {
        font-size: 12px;
    }
    
    .limit-input, .limit-select {
        font-size: 12px;
        padding: 4px 6px;
    }
}

/* Loading State */
.budget-skeleton {
    background: linear-gradient(90deg, #F3F4F6 25%, #E5E7EB 50%, #F3F4F6 75%);
    background-size: 200% 100%;
    animation: loading 1.5s ease-in-out infinite;
    border-radius: 6px;
    height: 20px;
    margin: 8px 0;
}

@keyframes loading {
    0% { background-position: 200% 0; }
    100% { background-position: -200% 0; }
}
</style>

<div class="box">
    <div class="box-header">
        <h2 class="blue">
            <i class="fa fa-money"></i> <?= lang('Budget Management'); ?>
        </h2>
        <div class="box-icon">
            <ul class="btn-tasks">
                <li>
                    <a href="#" class="btn btn-primary tip" title="Refresh" id="refreshBudget">
                        <i class="fa fa-refresh"></i>
                    </a>
                </li>
            </ul>
        </div>
    </div>
    
    <div class="box-content">
        
        <!-- Hierarchy Selector -->
        <div class="hierarchy-selector">
            <div class="hierarchy-breadcrumb">
                <span>Company</span>
                <i class="fa fa-angle-right"></i>
                <span id="currentHierarchyLevel" class="active">All Levels</span>
            </div>
            
            <div class="hierarchy-level-tabs">
                <button class="hierarchy-tab active" data-level="company">
                    <i class="fa fa-building"></i> Company
                </button>
                <button class="hierarchy-tab" data-level="group">
                    <i class="fa fa-object-group"></i> Pharmacy Groups
                </button>
                <button class="hierarchy-tab" data-level="pharmacy">
                    <i class="fa fa-hospital-o"></i> Pharmacies
                </button>
                <button class="hierarchy-tab" data-level="branch">
                    <i class="fa fa-map-marker"></i> Branches
                </button>
            </div>
            
            <!-- Period Selector -->
            <div class="period-selector">
                <button class="btn" data-period="monthly">Monthly</button>
                <button class="btn active" data-period="quarterly">Quarterly</button>
                <button class="btn" data-period="annual">Annual</button>
                <button class="btn" data-period="custom">Custom Range</button>
            </div>
        </div>

        <!-- Budget Limits Configuration Section -->
        <div class="allocation-section" style="margin-bottom: 30px;">
            <div class="allocation-header">
                <h3><i class="fa fa-sliders"></i> Define Budget Limits</h3>
                <div>
                    <button class="btn btn-sm btn-primary" id="btnSaveLimits">
                        <i class="fa fa-save"></i> Save Limits
                    </button>
                </div>
            </div>

            <div class="alert alert-info" style="margin-bottom: 20px;">
                <i class="fa fa-info-circle"></i> 
                <strong>Important:</strong> The <strong>Company-level Monthly Limit</strong> will be used to calculate the budget for allocation:
                <ul style="margin: 10px 0 0 20px;">
                    <li><strong>Monthly Period:</strong> Budget = Monthly Limit</li>
                    <li><strong>Quarterly Period:</strong> Budget = Monthly Limit × 3</li>
                    <li><strong>Annual Period:</strong> Budget = Monthly Limit × 12</li>
                </ul>
                <span class="text-muted">Other limits (Daily, Weekly, and limits for Groups/Pharmacies/Branches) control spending enforcement.</span>
            </div>

            <!-- Limits Definition Table -->
            <div class="table-responsive">
                <table class="table table-bordered" id="budgetLimitsTable">
                    <thead>
                        <tr>
                            <th width="20%">Hierarchy Level</th>
                            <th width="20%">Entity Name</th>
                            <th width="15%">Daily Limit (SAR)</th>
                            <th width="15%">Weekly Limit (SAR)</th>
                            <th width="15%">Monthly Limit (SAR)</th>
                            <th width="15%">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="limitsTableBody">
                        <!-- Dynamic rows will be inserted here -->
                    </tbody>
                </table>
            </div>

            <!-- Add Limit Button -->
            <div style="margin-top: 16px;">
                <button class="btn btn-success" id="btnAddLimit">
                    <i class="fa fa-plus"></i> Add New Limit
                </button>
            </div>

            <!-- Validation Info -->
            <div id="limitsValidationMessages" style="margin-top: 16px;"></div>
        </div>

        <!-- Current Budget Overview - KPI Cards -->
        <div class="row" id="budgetKPICards">
            <div class="col-md-3 col-sm-6">
                <div class="budget-card">
                    <div class="budget-card-header">
                        <h4 class="budget-card-title">Total Allocated</h4>
                        <span class="budget-card-status status-safe">Active</span>
                    </div>
                    <div class="budget-meter-container">
                        <div class="budget-meter-circle">
                            <svg width="120" height="120">
                                <circle cx="60" cy="60" r="54" stroke="#E5E7EB" stroke-width="12" fill="none"/>
                                <circle cx="60" cy="60" r="54" stroke="#10B981" stroke-width="12" fill="none" 
                                        stroke-dasharray="339.29" stroke-dashoffset="0" id="allocatedCircle"/>
                            </svg>
                            <div class="budget-meter-percentage" id="allocatedPercentage">100%</div>
                        </div>
                    </div>
                    <div class="budget-amounts">
                        <div class="budget-amount-row">
                            <span class="budget-amount-label">Amount</span>
                            <span class="budget-amount-value" id="allocatedAmount">500,000 SAR</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3 col-sm-6">
                <div class="budget-card">
                    <div class="budget-card-header">
                        <h4 class="budget-card-title">Total Spent</h4>
                        <span class="budget-card-status status-warning">Monitoring</span>
                    </div>
                    <div class="budget-meter-container">
                        <div class="budget-meter-circle">
                            <svg width="120" height="120">
                                <circle cx="60" cy="60" r="54" stroke="#E5E7EB" stroke-width="12" fill="none"/>
                                <circle cx="60" cy="60" r="54" stroke="#F59E0B" stroke-width="12" fill="none" 
                                        stroke-dasharray="339.29" stroke-dashoffset="254.47" id="spentCircle"/>
                            </svg>
                            <div class="budget-meter-percentage" id="spentPercentage">25%</div>
                        </div>
                    </div>
                    <div class="budget-amounts">
                        <div class="budget-amount-row">
                            <span class="budget-amount-label">Amount</span>
                            <span class="budget-amount-value" id="spentAmount">125,000 SAR</span>
                        </div>
                        <div class="budget-amount-row">
                            <span class="budget-amount-label">Trend</span>
                            <span class="budget-amount-value positive" id="spentTrend">
                                <i class="fa fa-arrow-up"></i> +5%
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3 col-sm-6">
                <div class="budget-card">
                    <div class="budget-card-header">
                        <h4 class="budget-card-title">Remaining</h4>
                        <span class="budget-card-status status-safe">Healthy</span>
                    </div>
                    <div class="budget-meter-container">
                        <div class="budget-meter-circle">
                            <svg width="120" height="120">
                                <circle cx="60" cy="60" r="54" stroke="#E5E7EB" stroke-width="12" fill="none"/>
                                <circle cx="60" cy="60" r="54" stroke="#10B981" stroke-width="12" fill="none" 
                                        stroke-dasharray="339.29" stroke-dashoffset="84.82" id="remainingCircle"/>
                            </svg>
                            <div class="budget-meter-percentage" id="remainingPercentage">75%</div>
                        </div>
                    </div>
                    <div class="budget-amounts">
                        <div class="budget-amount-row">
                            <span class="budget-amount-label">Amount</span>
                            <span class="budget-amount-value positive" id="remainingAmount">375,000 SAR</span>
                        </div>
                        <div class="budget-amount-row">
                            <span class="budget-amount-label">Days Left</span>
                            <span class="budget-amount-value" id="daysRemaining">20 days</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3 col-sm-6">
                <div class="budget-card">
                    <div class="budget-card-header">
                        <h4 class="budget-card-title">Forecast</h4>
                        <span class="budget-card-status status-safe">On Track</span>
                    </div>
                    <div class="budget-meter-container">
                        <div class="budget-meter-circle">
                            <svg width="120" height="120">
                                <circle cx="60" cy="60" r="54" stroke="#E5E7EB" stroke-width="12" fill="none"/>
                                <circle cx="60" cy="60" r="54" stroke="#3B82F6" stroke-width="12" fill="none" 
                                        stroke-dasharray="339.29" stroke-dashoffset="169.65" id="forecastCircle"/>
                            </svg>
                            <div class="budget-meter-percentage" id="forecastPercentage">50%</div>
                        </div>
                    </div>
                    <div class="budget-amounts">
                        <div class="budget-amount-row">
                            <span class="budget-amount-label">Projected</span>
                            <span class="budget-amount-value" id="forecastAmount">250,000 SAR</span>
                        </div>
                        <div class="budget-amount-row">
                            <span class="budget-amount-label">Burn Rate</span>
                            <span class="budget-amount-value" id="burnRate">6,250 SAR/day</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Budget Allocation Section -->
        <div class="allocation-section" style="margin-top: 30px;">
            <div class="allocation-header">
                <h3><i class="fa fa-sliders"></i> Allocate Budget</h3>
                <div>
                    <button class="btn btn-sm btn-default" id="btnResetAllocation">
                        <i class="fa fa-undo"></i> Reset
                    </button>
                </div>
            </div>

            <!-- Parent Budget Information -->
            <div class="parent-budget-info">
                <div class="label" id="parentBudgetLabel">Company Budget - Q4 2025</div>
                <div class="amount" id="parentBudgetAmount">0 SAR</div>
                <div style="margin-top: 8px; font-size: 12px; color: #6B7280;">
                    <span id="budgetSourceInfo">Please set budget limits above to define the budget</span>
                </div>
            </div>

            <!-- Distribution Method Selector -->
            <div class="distribution-methods">
                <label class="distribution-method active">
                    <input type="radio" name="distributionMethod" value="equal" checked>
                    <div class="distribution-method-label">
                        <span class="title">Equal Split</span>
                        <span class="desc">Divide evenly among all children</span>
                    </div>
                </label>
                <label class="distribution-method">
                    <input type="radio" name="distributionMethod" value="spending">
                    <div class="distribution-method-label">
                        <span class="title">By Spending</span>
                        <span class="desc">Proportional to past 30-day spending</span>
                    </div>
                </label>
                <label class="distribution-method">
                    <input type="radio" name="distributionMethod" value="sales">
                    <div class="distribution-method-label">
                        <span class="title">By Sales</span>
                        <span class="desc">Proportional to transaction count</span>
                    </div>
                </label>
                <label class="distribution-method">
                    <input type="radio" name="distributionMethod" value="custom">
                    <div class="distribution-method-label">
                        <span class="title">Custom</span>
                        <span class="desc">Manual allocation per entity</span>
                    </div>
                </label>
            </div>

            <!-- Child Allocations -->
            <div id="childAllocationsContainer">
                <!-- This will be populated dynamically based on hierarchy level -->
            </div>

            <!-- Allocation Visualization -->
            <div class="allocation-visualization">
                <div class="allocation-viz-label">Budget Distribution</div>
                <div class="allocation-bar" id="allocationBar">
                    <!-- Dynamic segments -->
                </div>
                <div class="allocation-legend" id="allocationLegend">
                    <!-- Dynamic legend items -->
                </div>
            </div>

            <!-- Allocation Summary -->
            <div class="allocation-summary">
                <div class="summary-row">
                    <span class="summary-label">Parent Budget:</span>
                    <span class="summary-value" id="summaryParentBudget">500,000 SAR</span>
                </div>
                <div class="summary-row">
                    <span class="summary-label">Total Allocated:</span>
                    <span class="summary-value" id="summaryTotalAllocated">0 SAR</span>
                </div>
                <div class="summary-row">
                    <span class="summary-label">Remaining Unallocated:</span>
                    <span class="summary-value" id="summaryRemaining">500,000 SAR</span>
                </div>
                <div class="summary-row total">
                    <span class="summary-label">Allocation Percentage:</span>
                    <span class="summary-value" id="summaryPercentage">0%</span>
                </div>
            </div>

            <!-- Validation Messages -->
            <div id="validationMessages"></div>

            <!-- Action Buttons -->
            <div class="budget-actions">
                <button class="btn-budget btn-budget-secondary" id="btnCancelAllocation">
                    <i class="fa fa-times"></i> Cancel
                </button>
                <button class="btn-budget btn-budget-secondary" id="btnPreviewAllocation">
                    <i class="fa fa-eye"></i> Preview Impact
                </button>
                <button class="btn-budget btn-budget-primary" id="btnSaveAllocation" disabled>
                    <i class="fa fa-save"></i> Save Allocation
                </button>
            </div>
        </div>

    </div>
</div>

<script type="text/javascript">
$(document).ready(function() {
    
    // Sample data structure
    const hierarchyData = {
        company: {
            id: 1,
            name: 'Avenzur Pharmacy',
            budget: 500000,
            children: []
        },
        groups: [
            { id: 1, name: 'Riyadh Group', budget: 0, color: '#8B5CF6' },
            { id: 2, name: 'Jeddah Group', budget: 0, color: '#EC4899' },
            { id: 3, name: 'Dammam Group', budget: 0, color: '#06B6D4' }
        ],
        pharmacies: [
            { id: 1, name: 'Riyadh Main Pharmacy', groupId: 1, budget: 0, color: '#3B82F6' },
            { id: 2, name: 'Riyadh North Pharmacy', groupId: 1, budget: 0, color: '#8B5CF6' },
            { id: 3, name: 'Jeddah Central Pharmacy', groupId: 2, budget: 0, color: '#EC4899' },
            { id: 4, name: 'Dammam East Pharmacy', groupId: 3, budget: 0, color: '#06B6D4' }
        ],
        branches: [
            { id: 1, name: 'Riyadh Branch 1', pharmacyId: 1, budget: 0, color: '#10B981' },
            { id: 2, name: 'Riyadh Branch 2', pharmacyId: 1, budget: 0, color: '#F59E0B' },
            { id: 3, name: 'Jeddah Branch 1', pharmacyId: 3, budget: 0, color: '#FB923C' },
            { id: 4, name: 'Dammam Branch 1', pharmacyId: 4, budget: 0, color: '#EF4444' }
        ]
    };

    let currentLevel = 'company';
    let currentPeriod = 'quarterly';
    let parentBudget = 0;
    let allocations = {};

    // Note: Initialize limits first, then budget UI (order matters!)

    function initializeBudgetUI() {
        updateBudgetFromLimits(); // This will render allocations as well
        updateAllocationVisualization();
        updateSummary();
    }

    // Hierarchy tab switching
    $('.hierarchy-tab').on('click', function() {
        $('.hierarchy-tab').removeClass('active');
        $(this).addClass('active');
        currentLevel = $(this).data('level');
        $('#currentHierarchyLevel').text($(this).text());
        renderChildAllocations(currentLevel);
    });

    // Period selector
    $('.period-selector .btn').on('click', function() {
        $('.period-selector .btn').removeClass('active');
        $(this).addClass('active');
        currentPeriod = $(this).data('period');
        updateBudgetFromLimits();
        loadBudgetData();
    });

    // Distribution method change
    $('input[name="distributionMethod"]').on('change', function() {
        $('.distribution-method').removeClass('active');
        $(this).closest('.distribution-method').addClass('active');
        
        const method = $(this).val();
        applyDistributionMethod(method);
    });

    function renderChildAllocations(level) {
        // Check if budget is set
        if (parentBudget <= 0) {
            $('#childAllocationsContainer').html(`
                <div class="alert alert-warning text-center" style="padding: 40px;">
                    <i class="fa fa-exclamation-triangle" style="font-size: 32px; margin-bottom: 15px;"></i>
                    <h4>No Budget Defined</h4>
                    <p>Please add a <strong>Company-level limit</strong> in the "Define Budget Limits" section above to set the budget.</p>
                    <p class="text-muted">The monthly limit will be used to calculate your ${currentPeriod} budget.</p>
                </div>
            `);
            return;
        }
        
        let children = [];
        
        switch(level) {
            case 'company':
                children = hierarchyData.groups;
                break;
            case 'group':
                children = hierarchyData.pharmacies;
                break;
            case 'pharmacy':
                children = hierarchyData.branches;
                break;
            case 'branch':
                // Branches don't have children
                $('#childAllocationsContainer').html('<p class="text-muted text-center">Branches are the lowest level and cannot allocate to children.</p>');
                return;
        }

        let html = '<div class="child-allocations">';
        
        children.forEach((child, index) => {
            const percentage = allocations[child.id] ? (allocations[child.id] / parentBudget * 100).toFixed(1) : 0;
            const amount = allocations[child.id] || 0;
            
            html += `
                <div class="allocation-item" data-child-id="${child.id}">
                    <div class="allocation-item-name">
                        <i class="fa fa-circle" style="color: ${child.color}"></i>
                        ${child.name}
                    </div>
                    <div class="allocation-slider-container">
                        <input type="range" class="allocation-slider" 
                               min="0" max="100" value="${percentage}" step="0.1"
                               data-child-id="${child.id}">
                        <div class="allocation-slider-value">${percentage}% of parent budget</div>
                    </div>
                    <div class="allocation-input-container">
                        <input type="text" class="allocation-input" 
                               value="${formatNumber(amount)}"
                               data-child-id="${child.id}"
                               placeholder="0">
                    </div>
                    <div class="allocation-percentage">${percentage}%</div>
                </div>
            `;
        });
        
        html += '</div>';
        $('#childAllocationsContainer').html(html);
        
        // Attach event listeners
        attachAllocationListeners();
    }

    function attachAllocationListeners() {
        // Slider change
        $('.allocation-slider').on('input', function() {
            const childId = $(this).data('child-id');
            const percentage = parseFloat($(this).val());
            const amount = (parentBudget * percentage / 100).toFixed(2);
            
            allocations[childId] = parseFloat(amount);
            updateAllocationDisplay(childId);
            updateAllocationVisualization();
            updateSummary();
            validateAllocation();
        });

        // Input change
        $('.allocation-input').on('input', function() {
            const childId = $(this).data('child-id');
            const value = $(this).val().replace(/,/g, '');
            const amount = parseFloat(value) || 0;
            
            allocations[childId] = amount;
            updateAllocationDisplay(childId);
            updateAllocationVisualization();
            updateSummary();
            validateAllocation();
        });
    }

    function updateAllocationDisplay(childId) {
        const amount = allocations[childId] || 0;
        const percentage = (amount / parentBudget * 100).toFixed(1);
        
        const $item = $(`.allocation-item[data-child-id="${childId}"]`);
        $item.find('.allocation-slider').val(percentage);
        $item.find('.allocation-input').val(formatNumber(amount));
        $item.find('.allocation-percentage').text(percentage + '%');
        $item.find('.allocation-slider-value').text(percentage + '% of parent budget');
    }

    function applyDistributionMethod(method) {
        let children = getCurrentChildren();
        
        switch(method) {
            case 'equal':
                const equalAmount = parentBudget / children.length;
                children.forEach(child => {
                    allocations[child.id] = equalAmount;
                    updateAllocationDisplay(child.id);
                });
                break;
                
            case 'spending':
                // Mock proportional by spending
                const totalSpending = 100000;
                const spendingData = {
                    1: 35000, 2: 25000, 3: 20000, 4: 20000
                };
                children.forEach(child => {
                    const proportion = (spendingData[child.id] || 0) / totalSpending;
                    allocations[child.id] = parentBudget * proportion;
                    updateAllocationDisplay(child.id);
                });
                break;
                
            case 'sales':
                // Mock proportional by sales
                const totalSales = 2000;
                const salesData = {
                    1: 800, 2: 600, 3: 400, 4: 200
                };
                children.forEach(child => {
                    const proportion = (salesData[child.id] || 0) / totalSales;
                    allocations[child.id] = parentBudget * proportion;
                    updateAllocationDisplay(child.id);
                });
                break;
                
            case 'custom':
                // Keep current values for custom
                break;
        }
        
        updateAllocationVisualization();
        updateSummary();
        validateAllocation();
    }

    function getCurrentChildren() {
        switch(currentLevel) {
            case 'company': return hierarchyData.groups;
            case 'group': return hierarchyData.pharmacies;
            case 'pharmacy': return hierarchyData.branches;
            default: return [];
        }
    }

    function updateAllocationVisualization() {
        const children = getCurrentChildren();
        let barHTML = '';
        let legendHTML = '';
        let totalAllocated = 0;
        
        children.forEach(child => {
            const amount = allocations[child.id] || 0;
            totalAllocated += amount;
            const percentage = (amount / parentBudget * 100).toFixed(2);
            
            if (percentage > 0) {
                barHTML += `
                    <div class="allocation-bar-segment" 
                         style="width: ${percentage}%; background: ${child.color};"
                         title="${child.name}: ${formatNumber(amount)} SAR (${percentage}%)">
                        ${percentage > 5 ? percentage + '%' : ''}
                    </div>
                `;
                
                legendHTML += `
                    <div class="allocation-legend-item">
                        <div class="allocation-legend-color" style="background: ${child.color};"></div>
                        <span>${child.name}: ${formatNumber(amount)} SAR</span>
                    </div>
                `;
            }
        });
        
        // Add remaining section
        const remaining = parentBudget - totalAllocated;
        const remainingPercentage = (remaining / parentBudget * 100).toFixed(2);
        
        if (remainingPercentage > 0) {
            barHTML += `
                <div class="allocation-bar-segment allocation-bar-remaining" 
                     style="width: ${remainingPercentage}%;"
                     title="Unallocated: ${formatNumber(remaining)} SAR (${remainingPercentage}%)">
                    ${remainingPercentage > 5 ? 'Unallocated' : ''}
                </div>
            `;
        }
        
        $('#allocationBar').html(barHTML);
        $('#allocationLegend').html(legendHTML);
    }

    function updateSummary() {
        let totalAllocated = 0;
        Object.values(allocations).forEach(amount => {
            totalAllocated += amount;
        });
        
        const remaining = parentBudget - totalAllocated;
        const percentage = (totalAllocated / parentBudget * 100).toFixed(1);
        
        $('#summaryParentBudget').text(formatNumber(parentBudget) + ' SAR');
        $('#summaryTotalAllocated').text(formatNumber(totalAllocated) + ' SAR');
        $('#summaryRemaining').text(formatNumber(remaining) + ' SAR');
        $('#summaryPercentage').text(percentage + '%');
        
        // Color coding
        if (remaining < 0) {
            $('#summaryRemaining').addClass('error').removeClass('warning');
        } else if (remaining < parentBudget * 0.1) {
            $('#summaryRemaining').addClass('warning').removeClass('error');
        } else {
            $('#summaryRemaining').removeClass('error warning');
        }
    }

    function validateAllocation() {
        let totalAllocated = 0;
        Object.values(allocations).forEach(amount => {
            totalAllocated += amount;
        });
        
        const remaining = parentBudget - totalAllocated;
        let messages = '';
        let isValid = true;
        
        if (totalAllocated > parentBudget) {
            messages += `
                <div class="validation-message error">
                    <i class="fa fa-exclamation-circle"></i>
                    <span>Total allocation exceeds parent budget by ${formatNumber(totalAllocated - parentBudget)} SAR</span>
                </div>
            `;
            isValid = false;
        } else if (totalAllocated < parentBudget && totalAllocated > 0) {
            messages += `
                <div class="validation-message warning">
                    <i class="fa fa-exclamation-triangle"></i>
                    <span>You have ${formatNumber(remaining)} SAR unallocated (${(remaining/parentBudget*100).toFixed(1)}%)</span>
                </div>
            `;
        } else if (totalAllocated === parentBudget) {
            messages += `
                <div class="validation-message success">
                    <i class="fa fa-check-circle"></i>
                    <span>Budget fully allocated. Ready to save.</span>
                </div>
            `;
        }
        
        $('#validationMessages').html(messages);
        $('#btnSaveAllocation').prop('disabled', !isValid || totalAllocated === 0);
    }

    // Action buttons
    $('#btnResetAllocation').on('click', function() {
        if (confirm('Are you sure you want to reset all allocations?')) {
            allocations = {};
            renderChildAllocations(currentLevel);
            updateAllocationVisualization();
            updateSummary();
            validateAllocation();
        }
    });

    $('#btnCancelAllocation').on('click', function() {
        if (confirm('Discard all changes?')) {
            location.reload();
        }
    });

    $('#btnPreviewAllocation').on('click', function() {
        // TODO: Show modal with impact preview
        alert('Preview functionality will show the impact of this allocation on child entities.');
    });

    $('#btnSaveAllocation').on('click', function() {
        if (confirm('Save this budget allocation? This will update all child budgets.')) {
            saveBudgetAllocation();
        }
    });

    function saveBudgetAllocation() {
        // Prepare data
        const data = {
            level: currentLevel,
            period: currentPeriod,
            parentBudget: parentBudget,
            allocations: allocations
        };
        
        // Show loading
        $('#btnSaveAllocation').html('<i class="fa fa-spinner fa-spin"></i> Saving...').prop('disabled', true);
        
        // Simulate API call
        setTimeout(function() {
            console.log('Saving allocation:', data);
            alert('Budget allocation saved successfully!');
            $('#btnSaveAllocation').html('<i class="fa fa-save"></i> Save Allocation').prop('disabled', false);
        }, 1000);
        
        // TODO: Replace with actual AJAX call
        /*
        $.ajax({
            url: '<?= admin_url("loyalty/save_budget_allocation"); ?>',
            type: 'POST',
            data: data,
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    alert('Budget allocation saved successfully!');
                    location.reload();
                } else {
                    alert('Error: ' + response.message);
                }
            },
            error: function() {
                alert('An error occurred while saving the allocation.');
            },
            complete: function() {
                $('#btnSaveAllocation').html('<i class="fa fa-save"></i> Save Allocation').prop('disabled', false);
            }
        });
        */
    }

    function loadBudgetData() {
        // TODO: Load actual budget data from API
        console.log('Loading budget data for period:', currentPeriod);
    }

    function formatNumber(num) {
        return Math.round(num).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
    }

    // Refresh button
    $('#refreshBudget').on('click', function(e) {
        e.preventDefault();
        location.reload();
    });

    // ============================================
    // BUDGET LIMITS FUNCTIONALITY
    // ============================================

    let budgetLimits = [];
    let limitIdCounter = 1;

    // All entities across hierarchy levels
    const allEntities = {
        company: [
            { id: 'company_1', name: 'Avenzur Pharmacy', level: 'company' }
        ],
        group: [
            { id: 'group_1', name: 'Riyadh Group', level: 'group' },
            { id: 'group_2', name: 'Jeddah Group', level: 'group' },
            { id: 'group_3', name: 'Dammam Group', level: 'group' }
        ],
        pharmacy: [
            { id: 'pharmacy_1', name: 'Riyadh Main Pharmacy', level: 'pharmacy' },
            { id: 'pharmacy_2', name: 'Riyadh North Pharmacy', level: 'pharmacy' },
            { id: 'pharmacy_3', name: 'Jeddah Central Pharmacy', level: 'pharmacy' },
            { id: 'pharmacy_4', name: 'Dammam East Pharmacy', level: 'pharmacy' }
        ],
        branch: [
            { id: 'branch_1', name: 'Riyadh Branch 1', level: 'branch' },
            { id: 'branch_2', name: 'Riyadh Branch 2', level: 'branch' },
            { id: 'branch_3', name: 'Jeddah Branch 1', level: 'branch' },
            { id: 'branch_4', name: 'Dammam Branch 1', level: 'branch' }
        ]
    };

    // Initialize limits table first, then budget UI
    initializeLimitsTable();
    initializeBudgetUI();

    function initializeLimitsTable() {
        // Load sample data or empty table
        renderLimitsTable();
    }

    function renderLimitsTable() {
        let html = '';
        
        if (budgetLimits.length === 0) {
            html = `
                <tr>
                    <td colspan="6" class="text-center text-muted" style="padding: 40px;">
                        <i class="fa fa-info-circle" style="font-size: 24px; margin-bottom: 10px;"></i><br>
                        No budget limits defined yet. Click "Add New Limit" to define limits for entities.
                    </td>
                </tr>
            `;
        } else {
            budgetLimits.forEach((limit, index) => {
                const badgeClass = limit.level.replace('_', '-');
                html += `
                    <tr class="limit-row" data-limit-id="${limit.id}">
                        <td>
                            <span class="limit-badge ${badgeClass}">${getLevelLabel(limit.level)}</span>
                        </td>
                        <td>
                            <strong>${limit.entityName}</strong>
                        </td>
                        <td>
                            <input type="text" class="limit-input" 
                                   value="${limit.dailyLimit || ''}" 
                                   data-limit-id="${limit.id}" 
                                   data-field="dailyLimit"
                                   placeholder="0">
                        </td>
                        <td>
                            <input type="text" class="limit-input" 
                                   value="${limit.weeklyLimit || ''}" 
                                   data-limit-id="${limit.id}" 
                                   data-field="weeklyLimit"
                                   placeholder="0">
                        </td>
                        <td>
                            <input type="text" class="limit-input" 
                                   value="${limit.monthlyLimit || ''}" 
                                   data-limit-id="${limit.id}" 
                                   data-field="monthlyLimit"
                                   placeholder="0">
                        </td>
                        <td>
                            <button class="btn btn-xs btn-danger btn-delete-limit" data-limit-id="${limit.id}">
                                <i class="fa fa-trash"></i> Delete
                            </button>
                        </td>
                    </tr>
                `;
            });
        }
        
        $('#limitsTableBody').html(html);
        attachLimitEventHandlers();
    }

    function attachLimitEventHandlers() {
        // Input changes
        $('.limit-input').on('input', function() {
            const limitId = $(this).data('limit-id');
            const field = $(this).data('field');
            const value = parseFloat($(this).val().replace(/,/g, '')) || 0;
            
            updateLimitField(limitId, field, value);
            validateLimits();
        });

        // Delete button
        $('.btn-delete-limit').on('click', function() {
            const limitId = $(this).data('limit-id');
            deleteLimitRow(limitId);
        });
    }

    function updateLimitField(limitId, field, value) {
        const limit = budgetLimits.find(l => l.id === limitId);
        if (limit) {
            limit[field] = value;
            updateBudgetFromLimits();
        }
    }

    function updateBudgetFromLimits() {
        // Find company level limit
        const companyLimit = budgetLimits.find(l => l.level === 'company');
        
        if (companyLimit) {
            // Determine budget based on current period
            let budget = 0;
            let periodLabel = '';
            let sourceInfo = '';
            
            switch(currentPeriod) {
                case 'monthly':
                    budget = companyLimit.monthlyLimit || 0;
                    periodLabel = 'Monthly Budget';
                    sourceInfo = budget > 0 ? `Based on company monthly limit: ${formatNumber(budget)} SAR` : 'Set monthly limit to define budget';
                    break;
                case 'quarterly':
                    // Quarterly = Monthly * 3
                    budget = (companyLimit.monthlyLimit || 0) * 3;
                    periodLabel = 'Quarterly Budget';
                    sourceInfo = budget > 0 ? `Based on company monthly limit (${formatNumber(companyLimit.monthlyLimit || 0)} × 3 months)` : 'Set monthly limit to define quarterly budget';
                    break;
                case 'annual':
                    // Annual = Monthly * 12
                    budget = (companyLimit.monthlyLimit || 0) * 12;
                    periodLabel = 'Annual Budget';
                    sourceInfo = budget > 0 ? `Based on company monthly limit (${formatNumber(companyLimit.monthlyLimit || 0)} × 12 months)` : 'Set monthly limit to define annual budget';
                    break;
                default:
                    budget = companyLimit.monthlyLimit || 0;
                    periodLabel = 'Custom Budget';
                    sourceInfo = budget > 0 ? `Based on company monthly limit: ${formatNumber(budget)} SAR` : 'Set monthly limit to define budget';
            }
            
            parentBudget = budget;
            $('#parentBudgetLabel').text(`Company ${periodLabel} - ${getCurrentPeriodDateRange()}`);
            $('#parentBudgetAmount').text(formatNumber(budget) + ' SAR');
            $('#budgetSourceInfo').text(sourceInfo);
            
            // Update KPI cards
            updateKPICards();
            
            // Always re-render allocations to show proper UI
            renderChildAllocations(currentLevel);
            
            updateAllocationVisualization();
            updateSummary();
            validateAllocation();
        } else {
            parentBudget = 0;
            $('#parentBudgetLabel').text('Company Budget - No Limit Set');
            $('#parentBudgetAmount').text('0 SAR');
            $('#budgetSourceInfo').html('<span class="text-danger"><i class="fa fa-exclamation-triangle"></i> Please add a Company-level budget limit above</span>');
            
            // Reset allocations
            allocations = {};
            renderChildAllocations(currentLevel);
            updateKPICards();
            updateAllocationVisualization();
            updateSummary();
        }
    }

    function getCurrentPeriodDateRange() {
        const now = new Date();
        const year = now.getFullYear();
        const month = now.getMonth();
        
        switch(currentPeriod) {
            case 'monthly':
                const monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
                return monthNames[month] + ' ' + year;
            case 'quarterly':
                const quarter = Math.floor(month / 3) + 1;
                return 'Q' + quarter + ' ' + year;
            case 'annual':
                return year.toString();
            default:
                return '';
        }
    }

    function updateKPICards() {
        // Update all KPI cards based on current budget
        const allocated = parentBudget;
        const spent = parentBudget * 0.25; // Mock 25% spent
        const remaining = parentBudget - spent;
        const forecast = parentBudget * 0.50; // Mock 50% forecast
        
        // Update Allocated Card
        $('#allocatedAmount').text(formatNumber(allocated) + ' SAR');
        $('#allocatedPercentage').text('100%');
        
        // Update Spent Card
        $('#spentAmount').text(formatNumber(spent) + ' SAR');
        const spentPercent = allocated > 0 ? (spent / allocated * 100).toFixed(0) : 0;
        $('#spentPercentage').text(spentPercent + '%');
        updateCircleProgress('spentCircle', spentPercent);
        
        // Update Remaining Card
        $('#remainingAmount').text(formatNumber(remaining) + ' SAR');
        const remainingPercent = allocated > 0 ? (remaining / allocated * 100).toFixed(0) : 0;
        $('#remainingPercentage').text(remainingPercent + '%');
        updateCircleProgress('remainingCircle', remainingPercent);
        
        // Update Forecast Card
        $('#forecastAmount').text(formatNumber(forecast) + ' SAR');
        const forecastPercent = allocated > 0 ? (forecast / allocated * 100).toFixed(0) : 0;
        $('#forecastPercentage').text(forecastPercent + '%');
        updateCircleProgress('forecastCircle', forecastPercent);
        
        // Update burn rate
        const daysInPeriod = currentPeriod === 'monthly' ? 30 : (currentPeriod === 'quarterly' ? 90 : 365);
        const burnRate = allocated > 0 ? (allocated / daysInPeriod).toFixed(0) : 0;
        $('#burnRate').text(formatNumber(burnRate) + ' SAR/day');
        
        // Update days remaining (mock)
        $('#daysRemaining').text('20 days');
    }

    function updateCircleProgress(circleId, percentage) {
        const circle = document.getElementById(circleId);
        if (circle) {
            const circumference = 339.29; // 2 * PI * 54
            const offset = circumference - (percentage / 100 * circumference);
            circle.setAttribute('stroke-dashoffset', offset);
        }
    }

    function deleteLimitRow(limitId) {
        if (confirm('Are you sure you want to delete this limit?')) {
            budgetLimits = budgetLimits.filter(l => l.id !== limitId);
            renderLimitsTable();
            validateLimits();
            updateBudgetFromLimits();
        }
    }

    function getLevelLabel(level) {
        const labels = {
            'company': 'Company',
            'group': 'Pharmacy Group',
            'pharmacy': 'Pharmacy',
            'branch': 'Branch'
        };
        return labels[level] || level;
    }

    // Add New Limit
    $('#btnAddLimit').on('click', function() {
        showAddLimitModal();
    });

    function showAddLimitModal() {
        // Build entity options
        let entityOptions = '<option value="">-- Select Entity --</option>';
        
        Object.keys(allEntities).forEach(level => {
            entityOptions += `<optgroup label="${getLevelLabel(level)}">`;
            allEntities[level].forEach(entity => {
                // Check if already has a limit
                const hasLimit = budgetLimits.some(l => l.entityId === entity.id);
                if (!hasLimit) {
                    entityOptions += `<option value="${entity.id}" data-level="${level}" data-name="${entity.name}">${entity.name}</option>`;
                }
            });
            entityOptions += '</optgroup>';
        });

        const modalHTML = `
            <div class="modal fade" id="addLimitModal" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                            <h4 class="modal-title"><i class="fa fa-plus"></i> Add Budget Limit</h4>
                        </div>
                        <div class="modal-body">
                            <form id="addLimitForm">
                                <div class="form-group">
                                    <label>Select Entity <span class="text-danger">*</span></label>
                                    <select class="form-control" id="limitEntitySelect" required>
                                        ${entityOptions}
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Daily Limit (SAR)</label>
                                    <input type="text" class="form-control" id="limitDailyInput" placeholder="Enter daily limit">
                                    <small class="text-muted">Maximum spending allowed per day</small>
                                </div>
                                <div class="form-group">
                                    <label>Weekly Limit (SAR)</label>
                                    <input type="text" class="form-control" id="limitWeeklyInput" placeholder="Enter weekly limit">
                                    <small class="text-muted">Maximum spending allowed per week</small>
                                </div>
                                <div class="form-group">
                                    <label>Monthly Limit (SAR)</label>
                                    <input type="text" class="form-control" id="limitMonthlyInput" placeholder="Enter monthly limit">
                                    <small class="text-muted">Maximum spending allowed per month</small>
                                </div>
                                <div class="alert alert-info">
                                    <i class="fa fa-info-circle"></i> 
                                    You must define at least one limit (Daily, Weekly, or Monthly).
                                </div>
                            </form>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                            <button type="button" class="btn btn-primary" id="btnConfirmAddLimit">
                                <i class="fa fa-plus"></i> Add Limit
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;

        // Remove existing modal if any
        $('#addLimitModal').remove();
        
        // Append and show
        $('body').append(modalHTML);
        $('#addLimitModal').modal('show');

        // Handle confirm button
        $('#btnConfirmAddLimit').on('click', function() {
            addNewLimit();
        });
    }

    function addNewLimit() {
        const entitySelect = $('#limitEntitySelect');
        const entityId = entitySelect.val();
        
        if (!entityId) {
            alert('Please select an entity');
            return;
        }

        const selectedOption = entitySelect.find('option:selected');
        const level = selectedOption.data('level');
        const entityName = selectedOption.data('name');
        
        const dailyLimit = parseFloat($('#limitDailyInput').val().replace(/,/g, '')) || 0;
        const weeklyLimit = parseFloat($('#limitWeeklyInput').val().replace(/,/g, '')) || 0;
        const monthlyLimit = parseFloat($('#limitMonthlyInput').val().replace(/,/g, '')) || 0;

        // Validate at least one limit is set
        if (dailyLimit === 0 && weeklyLimit === 0 && monthlyLimit === 0) {
            alert('Please set at least one limit (Daily, Weekly, or Monthly)');
            return;
        }

        // Add to array
        const newLimit = {
            id: 'limit_' + limitIdCounter++,
            entityId: entityId,
            entityName: entityName,
            level: level,
            dailyLimit: dailyLimit,
            weeklyLimit: weeklyLimit,
            monthlyLimit: monthlyLimit
        };

        budgetLimits.push(newLimit);
        
        // Close modal
        $('#addLimitModal').modal('hide');
        
        // Re-render table
        renderLimitsTable();
        validateLimits();
        updateBudgetFromLimits();
    }

    function validateLimits() {
        let messages = '';
        let hasErrors = false;

        // Validate that daily <= weekly <= monthly
        budgetLimits.forEach(limit => {
            const daily = limit.dailyLimit || 0;
            const weekly = limit.weeklyLimit || 0;
            const monthly = limit.monthlyLimit || 0;

            if (weekly > 0 && daily > 0 && daily * 7 > weekly) {
                messages += `
                    <div class="validation-message warning">
                        <i class="fa fa-exclamation-triangle"></i>
                        <span><strong>${limit.entityName}:</strong> Daily limit (${formatNumber(daily)} × 7 = ${formatNumber(daily * 7)}) exceeds weekly limit (${formatNumber(weekly)})</span>
                    </div>
                `;
            }

            if (monthly > 0 && daily > 0 && daily * 30 > monthly) {
                messages += `
                    <div class="validation-message warning">
                        <i class="fa fa-exclamation-triangle"></i>
                        <span><strong>${limit.entityName}:</strong> Daily limit (${formatNumber(daily)} × 30 = ${formatNumber(daily * 30)}) exceeds monthly limit (${formatNumber(monthly)})</span>
                    </div>
                `;
            }

            if (monthly > 0 && weekly > 0 && weekly * 4 > monthly) {
                messages += `
                    <div class="validation-message warning">
                        <i class="fa fa-exclamation-triangle"></i>
                        <span><strong>${limit.entityName}:</strong> Weekly limit (${formatNumber(weekly)} × 4 = ${formatNumber(weekly * 4)}) exceeds monthly limit (${formatNumber(monthly)})</span>
                    </div>
                `;
            }
        });

        if (budgetLimits.length > 0 && !hasErrors) {
            messages += `
                <div class="validation-message success">
                    <i class="fa fa-check-circle"></i>
                    <span>${budgetLimits.length} budget limit(s) configured. Click "Save Limits" to apply.</span>
                </div>
            `;
        }

        $('#limitsValidationMessages').html(messages);
    }

    // Save Limits
    $('#btnSaveLimits').on('click', function() {
        if (budgetLimits.length === 0) {
            alert('Please add at least one budget limit before saving.');
            return;
        }

        if (confirm('Save these budget limits? This will apply the spending limits to the selected entities.')) {
            saveBudgetLimits();
        }
    });

    function saveBudgetLimits() {
        const data = {
            limits: budgetLimits,
            period: currentPeriod
        };

        // Show loading
        $('#btnSaveLimits').html('<i class="fa fa-spinner fa-spin"></i> Saving...').prop('disabled', true);

        // Simulate API call
        setTimeout(function() {
            console.log('Saving limits:', data);
            alert('Budget limits saved successfully!');
            $('#btnSaveLimits').html('<i class="fa fa-save"></i> Save Limits').prop('disabled', false);
        }, 1000);

        // TODO: Replace with actual AJAX call
        /*
        $.ajax({
            url: '<?= admin_url("loyalty/save_budget_limits"); ?>',
            type: 'POST',
            data: data,
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    alert('Budget limits saved successfully!');
                    // Optionally reload or update UI
                } else {
                    alert('Error: ' + response.message);
                }
            },
            error: function() {
                alert('An error occurred while saving the limits.');
            },
            complete: function() {
                $('#btnSaveLimits').html('<i class="fa fa-save"></i> Save Limits').prop('disabled', false);
            }
        });
        */
    }
});
</script>
