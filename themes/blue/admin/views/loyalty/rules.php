<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<style>
/* ============================================================================
   LOYALTY RULES - HORIZON UI DESIGN SYSTEM
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

.btn-primary {
    padding: 10px 20px;
    border-radius: 8px;
    border: none;
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s ease;
    background: var(--horizon-primary);
    color: white;
}

.btn-primary:hover {
    background: #1557b0;
    box-shadow: 0 2px 8px rgba(26, 115, 232, 0.25);
}

.content-wrapper {
    padding: 24px;
}

/* Right Drawer Styles */
.drawer-overlay {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.5);
    z-index: 1000;
    opacity: 0;
    transition: opacity 0.3s ease;
}

.drawer-overlay.active {
    display: block;
    opacity: 1;
}

.drawer-container {
    position: fixed;
    top: 0;
    right: -700px;
    width: 700px;
    max-width: 90vw;
    height: 100vh;
    background: white;
    box-shadow: -4px 0 24px rgba(0, 0, 0, 0.15);
    z-index: 1001;
    transition: right 0.3s ease;
    display: flex;
    flex-direction: column;
}

.drawer-overlay.active .drawer-container {
    right: 0;
}

.drawer-header {
    padding: 24px;
    border-bottom: 1px solid var(--horizon-border);
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.drawer-header h2 {
    margin: 0;
    font-size: 18px;
    font-weight: 600;
}

.drawer-close {
    background: rgba(255, 255, 255, 0.2);
    border: none;
    font-size: 24px;
    cursor: pointer;
    color: white;
    width: 36px;
    height: 36px;
    border-radius: 6px;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s ease;
}

.drawer-close:hover {
    background: rgba(255, 255, 255, 0.3);
}

.drawer-body {
    flex: 1;
    overflow-y: auto;
    padding: 24px;
}

.drawer-footer {
    padding: 16px 24px;
    border-top: 1px solid var(--horizon-border);
    display: flex;
    gap: 12px;
    justify-content: flex-end;
    background: var(--horizon-bg-light);
}

.form-section {
    background: white;
    border: 1px solid var(--horizon-border);
    border-radius: 8px;
    padding: 20px;
    margin-bottom: 20px;
}

.form-section-title {
    font-size: 14px;
    font-weight: 600;
    color: var(--horizon-dark-text);
    margin-bottom: 16px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.form-section-title i {
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
.form-group select,
.form-group textarea {
    width: 100%;
    padding: 8px 12px;
    border: 1px solid var(--horizon-border);
    border-radius: 6px;
    font-size: 13px;
    font-family: 'Inter', sans-serif;
    transition: border-color 0.2s ease;
}

.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus {
    outline: none;
    border-color: var(--horizon-primary);
    box-shadow: 0 0 0 3px rgba(26, 115, 232, 0.1);
}

#hierarchyNodeGroup {
    width: 100% !important;
}

#hierarchyNodeId {
    width: 100% !important;
    min-width: 100% !important;
    max-width: 100% !important;
    display: block !important;
    box-sizing: border-box !important;
}

#hierarchyNodeGroup .form-group {
    width: 100% !important;
}

/* Hide Select2 elements for hierarchy dropdowns */
.select2-container--open .select2-dropdown--below,
.select2-container--open .select2-dropdown--above {
    display: none !important;
}

#select2-hierarchyLevel-container,
#select2-hierarchyNodeId-container,
.select2-drop-mask {
    display: none !important;
}

/* Ensure native select is visible */
#hierarchyLevel.select2-hidden-accessible,
#hierarchyNodeId.select2-hidden-accessible {
    display: block !important;
    position: static !important;
    width: 100% !important;
    height: auto !important;
    clip: auto !important;
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 12px;
}

.btn-outline {
    padding: 8px 16px;
    border-radius: 6px;
    font-size: 13px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s ease;
    background: transparent;
    color: var(--horizon-primary);
    border: 1px solid var(--horizon-primary);
}

.btn-outline:hover {
    background: rgba(26, 115, 232, 0.05);
}

.btn-success {
    padding: 10px 20px;
    border-radius: 8px;
    border: none;
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s ease;
    background: var(--horizon-success);
    color: white;
}

.btn-success:hover {
    background: #04b586;
}

.condition-item {
    background: var(--horizon-bg-light);
    border: 1px solid var(--horizon-border);
    border-radius: 6px;
    padding: 12px;
    margin-bottom: 12px;
    position: relative;
}

.condition-item-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 12px;
}

.condition-item-header strong {
    font-size: 12px;
    color: var(--horizon-dark-text);
}

.btn-remove {
    background: var(--horizon-error);
    color: white;
    border: none;
    padding: 4px 12px;
    border-radius: 4px;
    font-size: 11px;
    cursor: pointer;
    transition: all 0.2s ease;
}

.btn-remove:hover {
    background: #d32f2f;
}

.btn-add {
    background: var(--horizon-primary);
    color: white;
    border: none;
    padding: 8px 16px;
    border-radius: 6px;
    font-size: 13px;
    cursor: pointer;
    transition: all 0.2s ease;
    width: 100%;
    margin-top: 8px;
}

.btn-add:hover {
    background: #1557b0;
}

.tier-selector {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
    margin-bottom: 12px;
}

.tier-btn {
    padding: 8px 16px;
    border: 2px solid var(--horizon-border);
    background: white;
    border-radius: 6px;
    cursor: pointer;
    font-size: 12px;
    font-weight: 500;
    transition: all 0.2s ease;
    flex: 1;
    min-width: 80px;
}

.tier-btn:hover {
    border-color: var(--horizon-primary);
}

.tier-btn.active {
    border-color: var(--horizon-primary);
    background: rgba(26, 115, 232, 0.1);
    color: var(--horizon-primary);
}

.summary-cards {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 16px;
    margin-bottom: 24px;
}

.summary-card {
    background: white;
    border: 1px solid var(--horizon-border);
    border-radius: 8px;
    padding: 16px;
    display: flex;
    gap: 12px;
}

.summary-card-icon {
    width: 48px;
    height: 48px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    flex-shrink: 0;
}

.summary-card-content h6 {
    font-size: 11px;
    font-weight: 600;
    color: var(--horizon-light-text);
    text-transform: uppercase;
    margin: 0;
}

.summary-card-content .value {
    font-size: 24px;
    font-weight: 700;
    color: var(--horizon-dark-text);
    margin: 8px 0 0 0;
}

.allocation-panel {
    background: white;
    border: 1px solid var(--horizon-border);
    border-radius: 8px;
    padding: 20px;
    box-shadow: var(--horizon-shadow-sm);
    margin-bottom: 24px;
}

.allocation-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 13px;
}

.allocation-table thead {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.allocation-table th {
    padding: 12px;
    text-align: left;
    font-weight: 600;
    border: none;
    font-size: 12px;
    text-transform: uppercase;
}

.allocation-table td {
    padding: 12px;
    border-bottom: 1px solid var(--horizon-border);
    color: var(--horizon-dark-text);
}

.allocation-table tbody tr:hover {
    background: rgba(26, 115, 232, 0.02);
}

.badge {
    display: inline-block;
    padding: 4px 10px;
    border-radius: 4px;
    font-size: 11px;
    font-weight: 500;
}

.badge-success {
    background: rgba(5, 205, 153, 0.15);
    color: var(--horizon-success);
}

.badge-warning {
    background: rgba(255, 154, 86, 0.15);
    color: var(--horizon-warning);
}

.badge-error {
    background: rgba(243, 66, 53, 0.15);
    color: var(--horizon-error);
}

.badge-info {
    background: rgba(26, 115, 232, 0.15);
    color: var(--horizon-primary);
}

.action-btn {
    padding: 6px 10px;
    margin: 0 2px;
    border: none;
    background: transparent;
    cursor: pointer;
    color: var(--horizon-primary);
    font-size: 14px;
    transition: all 0.2s ease;
    border-radius: 4px;
}

.action-btn:hover {
    background: rgba(26, 115, 232, 0.1);
}

.help-text {
    font-size: 11px;
    color: var(--horizon-light-text);
    margin-top: 4px;
    display: block;
}

.required {
    color: var(--horizon-error);
}

.alert {
    padding: 12px 16px;
    border-radius: 8px;
    margin-top: 12px;
    font-size: 13px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.alert-info {
    background: #e3f2fd;
    border: 1px solid #90caf9;
    color: #1565c0;
}

.alert-info i {
    color: #1976d2;
}

@media (max-width: 768px) {
    .drawer-container {
        width: 100%;
        right: -100%;
    }
    
    .form-row {
        grid-template-columns: 1fr;
    }
    
    .summary-cards {
        grid-template-columns: 1fr;
    }
}
</style>

<div class="horizon-dashboard">
    <!-- Page Header -->
    <div class="horizon-header">
        <div class="horizon-header-title">
            <h1><i class="fa fa-sliders"></i> Loyalty Rules</h1>
            <p>Create and manage discount/promotion rules with conditions and actions</p>
        </div>
        <button class="btn-primary" onclick="openRuleDrawer()">
            <i class="fa fa-plus"></i> New Rule
        </button>
    </div>


    <!-- Main Content -->
    <div class="content-wrapper">
        <!-- Summary Cards -->
        <div class="summary-cards">
            <div class="summary-card">
                <div class="summary-card-icon" style="background: linear-gradient(135deg, #3b82f6 0%, #1e40af 100%);">
                    <i class="fa fa-list" style="font-size: 20px;"></i>
                </div>
                <div class="summary-card-content">
                    <h6>Total Rules</h6>
                    <div class="value" id="totalRules">0</div>
                </div>
            </div>

            <div class="summary-card">
                <div class="summary-card-icon" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);">
                    <i class="fa fa-check" style="font-size: 20px;"></i>
                </div>
                <div class="summary-card-content">
                    <h6>Active</h6>
                    <div class="value" id="activeRules">0</div>
                </div>
            </div>

            <div class="summary-card">
                <div class="summary-card-icon" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);">
                    <i class="fa fa-calendar" style="font-size: 20px;"></i>
                </div>
                <div class="summary-card-content">
                    <h6>Scheduled</h6>
                    <div class="value" id="scheduledRules">0</div>
                </div>
            </div>

            <div class="summary-card">
                <div class="summary-card-icon" style="background: linear-gradient(135deg, #8b5cf6 0%, #6d28d9 100%);">
                    <i class="fa fa-clock-o" style="font-size: 20px;"></i>
                </div>
                <div class="summary-card-content">
                    <h6>Expired</h6>
                    <div class="value" id="expiredRules">0</div>
                </div>
            </div>
        </div>

        <!-- Rules Table -->
        <div class="allocation-panel">
            <table class="allocation-table">
                <thead>
                    <tr>
                        <th>Rule Name</th>
                        <th>Level</th>
                        <th>Tier</th>
                        <th>Action Type</th>
                        <th>Priority</th>
                        <th>Status</th>
                        <th>Valid Period</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="rulesTableBody">
                    <tr>
                        <td colspan="8" style="text-align: center; padding: 40px; color: var(--horizon-light-text);">
                            <i class="fa fa-inbox" style="font-size: 32px; margin-bottom: 12px; display: block;"></i>
                            <p>No rules found. Click "New Rule" to create your first loyalty rule.</p>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Right Drawer for Rule Creation/Edit -->
<div class="drawer-overlay" id="ruleDrawer">
    <div class="drawer-container">
        <div class="drawer-header">
            <h2><i class="fa fa-sliders"></i> <span id="drawerTitle">Create New Loyalty Rule</span></h2>
            <button class="drawer-close" onclick="closeRuleDrawer()">&times;</button>
        </div>
        
        <div class="drawer-body">
            <form id="ruleForm">
                <!-- Basic Information Section -->
                <div class="form-section">
                    <div class="form-section-title">
                        <i class="fa fa-info-circle"></i> Basic Information
                    </div>
                    
                    <div class="form-group">
                        <label>Rule Name <span class="required">*</span></label>
                        <input type="text" id="ruleName" name="rule_name" placeholder="e.g., Gold Tier Weekend Bonus" required>
                        <span class="help-text">A descriptive name for this rule</span>
                    </div>

                    <div class="form-group">
                        <label>Hierarchy Level <span class="required">*</span></label>
                        <select id="hierarchyLevel" name="hierarchy_level" required onchange="loadHierarchyNodes()">
                            <option value="">Select Level</option>
                            <option value="PHARMA_GROUP">Pharmacy Group</option>
                            <option value="PHARMACY">Pharmacy</option>
                            <option value="BRANCH">Branch</option>
                        </select>
                        <span class="help-text">Where this rule applies</span>
                    </div>

                    <div class="form-group" id="hierarchyNodeGroup" style="display: none;">
                        <label>Select <span id="hierarchyNodeLabel">Node</span> <span class="required">*</span></label>
                        <select id="hierarchyNodeId" name="hierarchy_node_id" required></select>
                    </div>

                    <div class="form-group">
                        <label>Customer Tier</label>
                        <div class="tier-selector">
                            <button type="button" class="tier-btn" data-tier="all" onclick="selectTier('all')">All Tiers</button>
                            <button type="button" class="tier-btn" data-tier="bronze" onclick="selectTier('bronze')">Bronze</button>
                            <button type="button" class="tier-btn" data-tier="silver" onclick="selectTier('silver')">Silver</button>
                            <button type="button" class="tier-btn" data-tier="gold" onclick="selectTier('gold')">Gold</button>
                            <button type="button" class="tier-btn" data-tier="platinum" onclick="selectTier('platinum')">Platinum</button>
                        </div>
                        <input type="hidden" id="customerTier" name="customer_tier" value="all">
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Start Date <span class="required">*</span></label>
                            <input type="date" id="startDate" name="start_date" required>
                        </div>
                        <div class="form-group">
                            <label>End Date</label>
                            <input type="date" id="endDate" name="end_date">
                            <span class="help-text">Leave blank for no expiry</span>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Priority <span class="required">*</span></label>
                            <select id="priority" name="priority" required>
                                <option value="1">1 (Highest)</option>
                                <option value="2">2</option>
                                <option value="3" selected>3 (Medium)</option>
                                <option value="4">4</option>
                                <option value="5">5 (Lowest)</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Status <span class="required">*</span></label>
                            <select id="status" name="status" required>
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Description</label>
                        <textarea id="description" name="description" rows="2" placeholder="Optional description of this rule"></textarea>
                    </div>
                </div>

                <!-- Conditions Section -->
                <div class="form-section">
                    <div class="form-section-title">
                        <i class="fa fa-filter"></i> Rule Conditions
                    </div>
                    
                    <div id="conditionsContainer">
                        <!-- Conditions will be added dynamically -->
                    </div>
                    
                    <button type="button" class="btn-add" onclick="addCondition()">
                        <i class="fa fa-plus"></i> Add Condition
                    </button>
                </div>

                <!-- Actions Section -->
                <div class="form-section">
                    <div class="form-section-title">
                        <i class="fa fa-bolt"></i> Actions (What happens when conditions are met)
                    </div>
                    
                    <div class="form-group">
                        <label>Action Type <span class="required">*</span></label>
                        <select id="actionType" name="action_type" required onchange="updateActionFields()">
                            <option value="">Select Action</option>
                            <option value="DISCOUNT_PERCENTAGE">Percentage Discount</option>
                            <option value="DISCOUNT_FIXED">Fixed Amount Discount</option>
                            <option value="DISCOUNT_BOGO">Buy X Get Y Free (BOGO)</option>
                            <option value="LOYALTY_POINTS">Award Loyalty Points</option>
                            <option value="TIER_UPGRADE">Tier Upgrade</option>
                            <option value="FREE_ITEM">Free Item/Product</option>
                            <option value="NOTIFICATION">Send Notification</option>
                            <option value="CUSTOM_ACTION">Custom Action</option>
                        </select>
                    </div>

                    <div id="actionFieldsContainer">
                        <!-- Action-specific fields will be added here -->
                    </div>
                </div>

                <!-- Constraints Section -->
                <div class="form-section">
                    <div class="form-section-title">
                        <i class="fa fa-shield"></i> Constraints & Limits
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>Minimum Purchase Amount (SAR)</label>
                            <input type="number" id="minPurchaseAmount" name="min_purchase_amount" step="0.01" min="0" placeholder="0.00">
                            <span class="help-text">Minimum cart value required</span>
                        </div>
                        <div class="form-group">
                            <label>Maximum Discount (SAR)</label>
                            <input type="number" id="maxDiscountAmount" name="max_discount_amount" step="0.01" min="0" placeholder="Unlimited">
                            <span class="help-text">Cap the discount amount</span>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Max Uses Per Customer</label>
                            <input type="number" id="maxUsesPerCustomer" name="max_uses_per_customer" min="0" placeholder="Unlimited">
                        </div>
                        <div class="form-group">
                            <label>Max Total Uses</label>
                            <input type="number" id="maxTotalUses" name="max_total_uses" min="0" placeholder="Unlimited">
                        </div>
                    </div>

                    <div class="form-group">
                        <label>
                            <input type="checkbox" id="allowCombined" name="allow_combined" value="1">
                            Allow combination with other discounts
                        </label>
                    </div>

                    <div class="form-group">
                        <label>
                            <input type="checkbox" id="excludeSaleItems" name="exclude_sale_items" value="1">
                            Exclude items already on sale
                        </label>
                    </div>

                    <div class="form-group">
                        <label>Loyalty Points Per SAR</label>
                        <input type="number" id="pointsPerSar" name="points_per_sar" step="0.01" min="0" placeholder="0">
                        <span class="help-text">Points awarded per SAR spent (in addition to action)</span>
                    </div>

                    <div class="form-group">
                        <label>Days of Week</label>
                        <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                            <label style="display: flex; align-items: center; gap: 4px; font-weight: normal;">
                                <input type="checkbox" name="days[]" value="1" checked> Mon
                            </label>
                            <label style="display: flex; align-items: center; gap: 4px; font-weight: normal;">
                                <input type="checkbox" name="days[]" value="2" checked> Tue
                            </label>
                            <label style="display: flex; align-items: center; gap: 4px; font-weight: normal;">
                                <input type="checkbox" name="days[]" value="3" checked> Wed
                            </label>
                            <label style="display: flex; align-items: center; gap: 4px; font-weight: normal;">
                                <input type="checkbox" name="days[]" value="4" checked> Thu
                            </label>
                            <label style="display: flex; align-items: center; gap: 4px; font-weight: normal;">
                                <input type="checkbox" name="days[]" value="5" checked> Fri
                            </label>
                            <label style="display: flex; align-items: center; gap: 4px; font-weight: normal;">
                                <input type="checkbox" name="days[]" value="6" checked> Sat
                            </label>
                            <label style="display: flex; align-items: center; gap: 4px; font-weight: normal;">
                                <input type="checkbox" name="days[]" value="0" checked> Sun
                            </label>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Time From</label>
                            <input type="time" id="timeFrom" name="time_from">
                        </div>
                        <div class="form-group">
                            <label>Time To</label>
                            <input type="time" id="timeTo" name="time_to">
                        </div>
                    </div>
                </div>
            </form>
        </div>
        
        <div class="drawer-footer">
            <button class="btn-outline" onclick="closeRuleDrawer()">Cancel</button>
            <button class="btn-success" onclick="saveRule()">
                <i class="fa fa-save"></i> Save Rule
            </button>
        </div>
    </div>
</div>

<script>
let conditionCounter = 0;
let currentRuleId = null;

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    loadRulesData();
    selectTier('all');
    
    // Prevent Select2 from being applied to hierarchy dropdowns
    if (typeof $.fn.select2 !== 'undefined') {
        // Add a class to exclude from Select2
        $('#hierarchyLevel, #hierarchyNodeId').addClass('no-select2');
        
        // If Select2 is already applied, destroy it
        try {
            $('#hierarchyLevel').select2('destroy');
            $('#hierarchyNodeId').select2('destroy');
        } catch(e) {
            // Ignore if not initialized
        }
    }
});

// Load rules data
function loadRulesData() {
    fetch('<?php echo admin_url("loyalty/get_rules"); ?>')
        .then(r => r.json())
        .then(d => {
            if (d.success && d.rules) {
                renderRulesTable(d.rules);
                updateSummaryCards(d.rules);
            }
        })
        .catch(e => console.error('Error loading rules:', e));
}

// Render rules table
function renderRulesTable(rules) {
    const tbody = document.getElementById('rulesTableBody');
    if (!rules || rules.length === 0) {
        tbody.innerHTML = `<tr><td colspan="8" style="text-align: center; padding: 40px; color: var(--horizon-light-text);">
            <i class="fa fa-inbox" style="font-size: 32px; margin-bottom: 12px; display: block;"></i>
            <p>No rules found. Click "New Rule" to create your first loyalty rule.</p>
        </td></tr>`;
        return;
    }

    tbody.innerHTML = rules.map(r => {
        const statusBadge = r.status ? 
            '<span class="badge badge-success">Active</span>' : 
            '<span class="badge badge-error">Inactive</span>';
        
        const tierBadge = r.customer_tier ? 
            `<span class="badge badge-info">${r.customer_tier}</span>` : 
            '<span class="badge badge-info">All</span>';
        
        return `<tr>
            <td><strong>${r.name || 'Unnamed Rule'}</strong></td>
            <td><span class="badge badge-info">${r.hierarchy_level || 'Company'}</span></td>
            <td>${tierBadge}</td>
            <td>${r.action_type || 'N/A'}</td>
            <td><span class="badge badge-warning">Priority ${r.priority || 3}</span></td>
            <td>${statusBadge}</td>
            <td style="font-size: 11px;">${r.start_date || 'N/A'} - ${r.end_date || 'No Expiry'}</td>
            <td>
                <button class="action-btn" onclick="editRule(${r.id})" title="Edit">
                    <i class="fa fa-edit"></i>
                </button>
                <button class="action-btn" onclick="viewRule(${r.id})" title="View">
                    <i class="fa fa-eye"></i>
                </button>
                <button class="action-btn" onclick="deleteRule(${r.id})" title="Delete">
                    <i class="fa fa-trash"></i>
                </button>
            </td>
        </tr>`;
    }).join('');
}

// Update summary cards
function updateSummaryCards(rules) {
    const total = rules.length;
    const active = rules.filter(r => r.status == 1).length;
    const scheduled = rules.filter(r => new Date(r.start_date) > new Date()).length;
    const expired = rules.filter(r => r.end_date && new Date(r.end_date) < new Date()).length;

    document.getElementById('totalRules').textContent = total;
    document.getElementById('activeRules').textContent = active;
    document.getElementById('scheduledRules').textContent = scheduled;
    document.getElementById('expiredRules').textContent = expired;
}

// Open rule drawer
function openRuleDrawer() {
    currentRuleId = null;
    document.getElementById('drawerTitle').textContent = 'Create New Loyalty Rule';
    document.getElementById('ruleForm').reset();
    document.getElementById('conditionsContainer').innerHTML = '';
    document.getElementById('actionFieldsContainer').innerHTML = '';
    conditionCounter = 0;
    selectTier('all');
    document.getElementById('ruleDrawer').classList.add('active');
    
    // Destroy Select2 on hierarchy dropdowns to use native selects
    setTimeout(function() {
        if (typeof $.fn.select2 !== 'undefined') {
            $('#hierarchyLevel').select2('destroy');
            $('#hierarchyNodeId').select2('destroy');
        }
    }, 100);
}

// Close rule drawer
function closeRuleDrawer() {
    document.getElementById('ruleDrawer').classList.remove('active');
}

// Close drawer when clicking overlay
document.getElementById('ruleDrawer').addEventListener('click', function(e) {
    if (e.target === this) {
        closeRuleDrawer();
    }
});

// Select tier
function selectTier(tier) {
    document.querySelectorAll('.tier-btn').forEach(btn => {
        btn.classList.remove('active');
    });
    document.querySelector(`[data-tier="${tier}"]`).classList.add('active');
    document.getElementById('customerTier').value = tier;
}

// Load hierarchy nodes based on level
function loadHierarchyNodes() {
    const level = document.getElementById('hierarchyLevel').value;
    const nodeGroup = document.getElementById('hierarchyNodeGroup');
    const nodeSelect = document.getElementById('hierarchyNodeId');
    const nodeLabel = document.getElementById('hierarchyNodeLabel');
    
    if (!level) {
        nodeGroup.style.display = 'none';
        return;
    }
    
    nodeGroup.style.display = 'block';
    
    // Update label based on level
    const labelMap = {
        'PHARMA_GROUP': 'Pharmacy Group',
        'PHARMACY': 'Pharmacy',
        'BRANCH': 'Branch'
    };
    nodeLabel.textContent = labelMap[level] || 'Node';
    
    // Show loading state
    nodeSelect.innerHTML = '<option value="">Loading...</option>';
    
    // Fetch nodes based on level
    fetch(`<?php echo admin_url("loyalty/get_hierarchy_nodes/"); ?>${level}`)
        .then(r => r.json())
        .then(d => {
            if (d.success && d.nodes) {
                nodeSelect.innerHTML = '<option value="">Select ' + nodeLabel.textContent + '</option>' +
                    d.nodes.map(n => `<option value="${n.id}">${n.name}</option>`).join('');
            } else {
                nodeSelect.innerHTML = '<option value="">No ' + nodeLabel.textContent + ' available</option>';
            }
        })
        .catch(e => {
            console.error('Error loading nodes:', e);
            nodeSelect.innerHTML = '<option value="">Error loading data</option>';
        });
}

// Add condition
function addCondition() {
    conditionCounter++;
    const container = document.getElementById('conditionsContainer');
    const conditionHtml = `
        <div class="condition-item" id="condition_${conditionCounter}">
            <div class="condition-item-header">
                <strong>Condition ${conditionCounter}</strong>
                <button type="button" class="btn-remove" onclick="removeCondition(${conditionCounter})">
                    <i class="fa fa-times"></i> Remove
                </button>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Condition Type <span class="required">*</span></label>
                    <select name="conditions[${conditionCounter}][type]" onchange="updateConditionFields(${conditionCounter}, this.value)" required>
                        <option value="">Select Type</option>
                        <option value="PURCHASE_AMOUNT">Purchase Amount Threshold</option>
                        <option value="FREQUENCY">Transaction Frequency/Count</option>
                        <option value="CLV">Customer Lifetime Value</option>
                        <option value="CATEGORY">Product Categories</option>
                        <option value="TIME_BASED">Time/Temporal Based</option>
                        <option value="CUSTOMER_TIER">Customer Loyalty Tier</option>
                        <option value="INVENTORY">Inventory/Stock Levels (Future)</option>
                        <option value="WEATHER">Weather/Climate Based (Future)</option>
                        <option value="CUSTOM">Custom Condition (Future)</option>
                    </select>
                </div>
                <div class="form-group" id="conditionValueField_${conditionCounter}">
                    <label>Value</label>
                    <input type="text" name="conditions[${conditionCounter}][value]" placeholder="Enter value">
                </div>
            </div>
            <div id="conditionExtraFields_${conditionCounter}"></div>
        </div>
    `;
    container.insertAdjacentHTML('beforeend', conditionHtml);
}

// Remove condition
function removeCondition(id) {
    document.getElementById(`condition_${id}`).remove();
}

// Update condition fields based on type
function updateConditionFields(id, type) {
    const valueField = document.getElementById(`conditionValueField_${id}`);
    const extraFieldsContainer = document.getElementById(`conditionExtraFields_${id}`);
    let fieldHtml = '';
    let extraHtml = '';
    
    switch(type) {
        case 'PURCHASE_AMOUNT':
            fieldHtml = `<label>Minimum Amount (SAR) <span class="required">*</span></label>
                <input type="number" name="conditions[${id}][value]" placeholder="e.g., 500" step="0.01" min="0" required>
                <span class="help-text">Minimum purchase amount to qualify</span>`;
            extraHtml = `
                <div class="form-group">
                    <label>Operator</label>
                    <select name="conditions[${id}][operator]">
                        <option value=">=">Greater than or equal (>=)</option>
                        <option value=">">Greater than (>)</option>
                        <option value="=">Equal to (=)</option>
                        <option value="<">Less than (<)</option>
                        <option value="<=">Less than or equal (<=)</option>
                    </select>
                </div>`;
            break;
            
        case 'FREQUENCY':
            fieldHtml = `<label>Transaction Count <span class="required">*</span></label>
                <input type="number" name="conditions[${id}][value]" placeholder="e.g., 5" min="1" step="1" required>
                <span class="help-text">Number of transactions required</span>`;
            extraHtml = `
                <div class="form-row">
                    <div class="form-group">
                        <label>Time Period</label>
                        <select name="conditions[${id}][period]">
                            <option value="LIFETIME">Lifetime</option>
                            <option value="YEAR">Past Year</option>
                            <option value="MONTH">Past Month</option>
                            <option value="WEEK">Past Week</option>
                            <option value="DAY">Today</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Operator</label>
                        <select name="conditions[${id}][operator]">
                            <option value=">=">At least (>=)</option>
                            <option value=">">More than (>)</option>
                            <option value="=">Exactly (=)</option>
                        </select>
                    </div>
                </div>`;
            break;
            
        case 'CLV':
            fieldHtml = `<label>Lifetime Value (SAR) <span class="required">*</span></label>
                <input type="number" name="conditions[${id}][value]" placeholder="e.g., 10000" step="0.01" min="0" required>
                <span class="help-text">Minimum customer lifetime value</span>`;
            extraHtml = `
                <div class="form-group">
                    <label>Operator</label>
                    <select name="conditions[${id}][operator]">
                        <option value=">=">At least (>=)</option>
                        <option value=">">More than (>)</option>
                        <option value="=">Exactly (=)</option>
                    </select>
                </div>`;
            break;
            
        case 'CATEGORY':
            fieldHtml = `<label>Category IDs (comma-separated) <span class="required">*</span></label>
                <input type="text" name="conditions[${id}][value]" placeholder="e.g., 12,45,67 or Category Name" required>
                <span class="help-text">Product categories to include</span>`;
            extraHtml = `
                <div class="form-group">
                    <label>Match Type</label>
                    <select name="conditions[${id}][match_type]">
                        <option value="ANY">Any category (OR)</option>
                        <option value="ALL">All categories (AND)</option>
                        <option value="EXCLUDE">Exclude categories</option>
                    </select>
                </div>`;
            break;
            
        case 'TIME_BASED':
            fieldHtml = `<label>Time Condition Type <span class="required">*</span></label>
                <select name="conditions[${id}][value]" required>
                    <option value="">Select Time Type</option>
                    <option value="WEEKDAY">Specific Weekday</option>
                    <option value="WEEKEND">Weekend Only</option>
                    <option value="DATE_RANGE">Date Range</option>
                    <option value="TIME_RANGE">Time Range</option>
                    <option value="HOUR">Specific Hours</option>
                    <option value="MONTH">Specific Month</option>
                </select>
                <span class="help-text">Type of time-based condition</span>`;
            extraHtml = `
                <div id="timeBasedFields_${id}">
                    <!-- Time-specific fields will be added dynamically -->
                </div>`;
            break;
            
        case 'CUSTOMER_TIER':
            fieldHtml = `<label>Customer Tier <span class="required">*</span></label>
                <select name="conditions[${id}][value]" required>
                    <option value="">Select Tier</option>
                    <option value="BRONZE">Bronze</option>
                    <option value="SILVER">Silver</option>
                    <option value="GOLD">Gold</option>
                    <option value="PLATINUM">Platinum</option>
                </select>
                <span class="help-text">Required customer loyalty tier</span>`;
            extraHtml = `
                <div class="form-group">
                    <label>Match Type</label>
                    <select name="conditions[${id}][match_type]">
                        <option value="EXACT">Exact tier match</option>
                        <option value="MIN">Minimum tier (this or higher)</option>
                        <option value="MAX">Maximum tier (this or lower)</option>
                    </select>
                </div>`;
            break;
            
        case 'INVENTORY':
            fieldHtml = `<label>Stock Level Threshold <span class="required">*</span></label>
                <input type="number" name="conditions[${id}][value]" placeholder="e.g., 100" min="0" step="1" required>
                <span class="help-text">Inventory threshold (Future Feature)</span>`;
            extraHtml = `
                <div class="form-row">
                    <div class="form-group">
                        <label>Product ID/SKU</label>
                        <input type="text" name="conditions[${id}][product_id]" placeholder="Product ID">
                    </div>
                    <div class="form-group">
                        <label>Condition</label>
                        <select name="conditions[${id}][operator]">
                            <option value="<">Below threshold</option>
                            <option value=">">Above threshold</option>
                            <option value="=">Exactly</option>
                        </select>
                    </div>
                </div>
                <div class="alert alert-info">
                    <i class="fa fa-info-circle"></i> This is a future feature
                </div>`;
            break;
            
        case 'WEATHER':
            fieldHtml = `<label>Weather Condition <span class="required">*</span></label>
                <select name="conditions[${id}][value]" required>
                    <option value="">Select Condition</option>
                    <option value="SUNNY">Sunny</option>
                    <option value="RAINY">Rainy</option>
                    <option value="CLOUDY">Cloudy</option>
                    <option value="HOT">Hot (>35°C)</option>
                    <option value="COLD">Cold (<15°C)</option>
                </select>
                <span class="help-text">Weather-based trigger (Future Feature)</span>`;
            extraHtml = `
                <div class="form-group">
                    <label>Location</label>
                    <input type="text" name="conditions[${id}][location]" placeholder="City/Region">
                </div>
                <div class="alert alert-info">
                    <i class="fa fa-info-circle"></i> This is a future feature
                </div>`;
            break;
            
        case 'CUSTOM':
            fieldHtml = `<label>Custom Condition Value <span class="required">*</span></label>
                <input type="text" name="conditions[${id}][value]" placeholder="Enter custom value" required>
                <span class="help-text">Extensible custom condition (Future Feature)</span>`;
            extraHtml = `
                <div class="form-group">
                    <label>Custom Metadata (JSON)</label>
                    <textarea name="conditions[${id}][metadata]" rows="3" placeholder='{"key": "value"}'></textarea>
                    <span class="help-text">Additional metadata in JSON format</span>
                </div>
                <div class="alert alert-info">
                    <i class="fa fa-info-circle"></i> This is a future feature for custom extensibility
                </div>`;
            break;
            
        default:
            fieldHtml = `<label>Value</label>
                <input type="text" name="conditions[${id}][value]" placeholder="Enter value">`;
    }
    
    valueField.innerHTML = fieldHtml;
    if (extraFieldsContainer) {
        extraFieldsContainer.innerHTML = extraHtml;
    }
}

// Update action fields based on action type
function updateActionFields() {
    const actionType = document.getElementById('actionType').value;
    const container = document.getElementById('actionFieldsContainer');
    let fieldsHtml = '';
    
    switch(actionType) {
        case 'DISCOUNT_PERCENTAGE':
            fieldsHtml = `
                <div class="form-group">
                    <label>Discount Percentage (5% - 50%) <span class="required">*</span></label>
                    <input type="number" name="action_value" id="actionValue" step="0.01" min="5" max="50" placeholder="e.g., 15" required>
                    <span class="help-text">Percentage to discount (5-50)</span>
                </div>
            `;
            break;
        case 'DISCOUNT_FIXED':
            fieldsHtml = `
                <div class="form-group">
                    <label>Discount Amount (1 - 1000 SAR) <span class="required">*</span></label>
                    <input type="number" name="action_value" id="actionValue" step="0.01" min="1" max="1000" placeholder="e.g., 50.00" required>
                    <span class="help-text">Fixed amount to discount (1-1000 SAR)</span>
                </div>
            `;
            break;
        case 'DISCOUNT_BOGO':
            fieldsHtml = `
                <div class="form-row">
                    <div class="form-group">
                        <label>Buy Quantity (X) <span class="required">*</span></label>
                        <input type="number" name="buy_quantity" id="buyQuantity" min="1" step="1" placeholder="e.g., 2" required>
                        <span class="help-text">Minimum 1</span>
                    </div>
                    <div class="form-group">
                        <label>Get Quantity (Y) Free <span class="required">*</span></label>
                        <input type="number" name="get_quantity" id="getQuantity" min="1" step="1" placeholder="e.g., 1" required>
                        <span class="help-text">Minimum 1</span>
                    </div>
                </div>
            `;
            break;
        case 'LOYALTY_POINTS':
            fieldsHtml = `
                <div class="form-group">
                    <label>Points to Award (Minimum 1) <span class="required">*</span></label>
                    <input type="number" name="action_value" id="actionValue" min="1" step="1" placeholder="e.g., 100" required>
                    <span class="help-text">Number of loyalty points to award (must be >= 1)</span>
                </div>
            `;
            break;
        case 'TIER_UPGRADE':
            fieldsHtml = `
                <div class="form-group">
                    <label>Target Tier <span class="required">*</span></label>
                    <select name="action_value" id="actionValue" required>
                        <option value="">Select Tier</option>
                        <option value="BRONZE">Bronze</option>
                        <option value="SILVER">Silver</option>
                        <option value="GOLD">Gold</option>
                        <option value="PLATINUM">Platinum</option>
                    </select>
                    <span class="help-text">Tier to upgrade customer to</span>
                </div>
            `;
            break;
        case 'FREE_ITEM':
            fieldsHtml = `
                <div class="form-group">
                    <label>Product ID/SKU <span class="required">*</span></label>
                    <input type="text" name="action_value" id="actionValue" placeholder="Enter product ID or SKU" required>
                    <span class="help-text">Product to give for free (must be non-empty)</span>
                </div>
                <div class="form-group">
                    <label>Product Name (Optional)</label>
                    <input type="text" name="product_name" id="productName" placeholder="Display name">
                    <span class="help-text">Display name for the free product</span>
                </div>
            `;
            break;
        case 'NOTIFICATION':
            fieldsHtml = `
                <div class="form-group">
                    <label>Notification Message <span class="required">*</span></label>
                    <textarea name="action_value" id="actionValue" rows="3" placeholder="Enter notification message" required></textarea>
                    <span class="help-text">Message to send to customer (must be non-empty)</span>
                </div>
                <div class="form-group">
                    <label>Notification Channel</label>
                    <select name="notification_channel" id="notificationChannel">
                        <option value="IN_APP">In-App</option>
                        <option value="EMAIL">Email</option>
                        <option value="SMS">SMS</option>
                        <option value="ALL">All Channels</option>
                    </select>
                </div>
            `;
            break;
        case 'CUSTOM_ACTION':
            fieldsHtml = `
                <div class="form-group">
                    <label>Custom Action Value <span class="required">*</span></label>
                    <input type="text" name="action_value" id="actionValue" placeholder="Enter custom action value" required>
                    <span class="help-text">Flexible value for custom action (can be any type)</span>
                </div>
                <div class="form-group">
                    <label>Custom Metadata (JSON)</label>
                    <textarea name="custom_metadata" id="customMetadata" rows="4" placeholder='{"key": "value"}'></textarea>
                    <span class="help-text">Optional JSON metadata for custom action</span>
                </div>
            `;
            break;
    }
    
    container.innerHTML = fieldsHtml;
}

// Save rule
function saveRule() {
    const form = document.getElementById('ruleForm');
    
    if (!form.checkValidity()) {
        alert('Please fill in all required fields');
        return;
    }
    
    const formData = new FormData(form);
    const data = {};
    
    // Convert FormData to object
    for (let [key, value] of formData.entries()) {
        if (key.includes('[')) {
            // Handle array/nested fields
            const matches = key.match(/(\w+)\[(\d+)\]\[(\w+)\]/);
            if (matches) {
                const [, name, index, field] = matches;
                if (!data[name]) data[name] = {};
                if (!data[name][index]) data[name][index] = {};
                data[name][index][field] = value;
            } else if (key.endsWith('[]')) {
                const name = key.slice(0, -2);
                if (!data[name]) data[name] = [];
                data[name].push(value);
            }
        } else {
            data[key] = value;
        }
    }
    
    // Handle BOGO metadata (buyQty and getQty)
    const actionType = document.getElementById('actionType').value;
    if (actionType === 'DISCOUNT_BOGO') {
        const buyQty = document.getElementById('buyQuantity')?.value;
        const getQty = document.getElementById('getQuantity')?.value;
        if (buyQty && getQty) {
            data.buy_quantity = buyQty;
            data.get_quantity = getQty;
        }
    }
    
    // Handle custom metadata for CUSTOM_ACTION
    if (actionType === 'CUSTOM_ACTION' && data.custom_metadata) {
        try {
            data.metadata = JSON.parse(data.custom_metadata);
        } catch (e) {
            console.warn('Invalid JSON metadata, using as string');
        }
    }
    
    // Add current rule ID if editing
    if (currentRuleId) {
        data.rule_id = currentRuleId;
    }

    console.log('Sending data to API:', data);
    
    // Send to server
    fetch('<?php echo admin_url("loyalty/save_rule"); ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify(data)
    })
    .then(response => {
        console.log('Response status:', response.status);
        console.log('Response headers:', response.headers);
        
        // Check if response is JSON
        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            throw new Error('Server returned non-JSON response. Check server logs.');
        }
        
        return response.json();
    })
    .then(d => {
        console.log('Response data:', d);
        
        if (d.success) {
            alert('Rule saved successfully!');
            closeRuleDrawer();
            loadRulesData();
        } else {
            alert('Error saving rule: ' + (d.message || 'Unknown error'));
        }
    })
    .catch(e => {
        console.error('Error:', e);
        alert('Error saving rule: ' + e.message);
    });
}

// Edit rule
function editRule(id) {
    currentRuleId = id;
    document.getElementById('drawerTitle').textContent = 'Edit Loyalty Rule';
    
    // Fetch rule data
    fetch(`<?php echo admin_url("loyalty/get_rule/"); ?>${id}`)
        .then(r => r.json())
        .then(d => {
            if (d.success && d.rule) {
                populateRuleForm(d.rule);
                document.getElementById('ruleDrawer').classList.add('active');
            }
        })
        .catch(e => console.error('Error loading rule:', e));
}

// Populate form with rule data
function populateRuleForm(rule) {
    document.getElementById('ruleName').value = rule.name || '';
    document.getElementById('hierarchyLevel').value = rule.hierarchy_level || 'company';
    document.getElementById('startDate').value = rule.start_date || '';
    document.getElementById('endDate').value = rule.end_date || '';
    document.getElementById('priority').value = rule.priority || 3;
    document.getElementById('status').value = rule.status || 1;
    document.getElementById('description').value = rule.description || '';
    
    if (rule.customer_tier) {
        selectTier(rule.customer_tier);
    }
    
    // Load hierarchy nodes if needed
    if (rule.hierarchy_level && rule.hierarchy_level !== 'company') {
        loadHierarchyNodes();
        setTimeout(() => {
            document.getElementById('hierarchyNodeId').value = rule.hierarchy_node_id || '';
        }, 500);
    }
    
    // Populate action type
    if (rule.action_type) {
        document.getElementById('actionType').value = rule.action_type;
        updateActionFields();
        setTimeout(() => {
            if (document.getElementById('actionValue')) {
                document.getElementById('actionValue').value = rule.action_value || '';
            }
        }, 100);
    }
    
    // Populate constraints
    if (rule.min_purchase_amount) {
        document.getElementById('minPurchaseAmount').value = rule.min_purchase_amount;
    }
    if (rule.max_discount_amount) {
        document.getElementById('maxDiscountAmount').value = rule.max_discount_amount;
    }
    if (rule.max_uses_per_customer) {
        document.getElementById('maxUsesPerCustomer').value = rule.max_uses_per_customer;
    }
    if (rule.max_total_uses) {
        document.getElementById('maxTotalUses').value = rule.max_total_uses;
    }
    if (rule.points_per_sar) {
        document.getElementById('pointsPerSar').value = rule.points_per_sar;
    }
    
    document.getElementById('allowCombined').checked = rule.allow_combined == 1;
    document.getElementById('excludeSaleItems').checked = rule.exclude_sale_items == 1;
}

// View rule details
function viewRule(id) {
    alert('View rule details - Feature coming soon');
}

// Delete rule
function deleteRule(id) {
    if (!confirm('Are you sure you want to delete this rule?')) {
        return;
    }
    
    fetch(`<?php echo admin_url("loyalty/delete_rule/"); ?>${id}`, {
        method: 'POST'
    })
    .then(r => r.json())
    .then(d => {
        if (d.success) {
            alert('Rule deleted successfully');
            loadRulesData();
        } else {
            alert('Error deleting rule');
        }
    })
    .catch(e => console.error('Error:', e));
}
</script>