<?php
/**
 * Performance Dashboard View - Horizon UI Design
 * 
 * Purpose: Display company/pharmacy/branch-level performance metrics
 * - Summary Metrics (Total Sales, Total Margin, Total Customers, Items Sold, etc.)
 * - Best Moving Products (Top 5 by sales volume)
 * - Period and level filtering
 * 
 * Data passed from controller:
 * - $level: 'company', 'pharmacy', or 'branch'
 * - $period: YYYY-MM format
 * - $summary_metrics: Object with all KPIs
 * - $best_products: Array of top products
 * - $periods: Available period options
 * - $pharmacies, $branches: For filtering (if applicable)
 */

defined('BASEPATH') OR exit('No direct script access allowed');
?>

<!-- Horizon UI Modern Performance Dashboard -->
<style>
/* ============================================================================
   HORIZON UI Design System - CSS Variables & Global Styles
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

* {
    box-sizing: border-box;
}

.horizon-dashboard {
    background: #ffffff;
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
    padding: 0;
}

/* ============================================================================
   HEADER / NAVBAR SECTION
   ============================================================================ */

.horizon-header {
    background: #ffffff;
    border-bottom: 1px solid var(--horizon-border);
    padding: 20px 24px;
    margin-bottom: 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 16px;
}

.horizon-header-title {
    display: flex;
    align-items: center;
    gap: 12px;
}

.horizon-header-title h1 {
    margin: 0;
    font-size: 28px;
    font-weight: 700;
    color: var(--horizon-dark-text);
}

.horizon-header-title p {
    margin: 0;
    font-size: 14px;
    color: var(--horizon-light-text);
    font-weight: 400;
}

.horizon-header-controls {
    display: flex;
    gap: 12px;
    align-items: center;
}

/* ============================================================================
   CONTROL BAR SECTION
   ============================================================================ */

.horizon-control-bar {
    background: var(--horizon-bg-light);
    border-radius: 8px;
    padding: 16px;
    margin-bottom: 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 16px;
}

.horizon-controls-left {
    display: flex;
    gap: 12px;
    align-items: center;
    flex-wrap: wrap;
}

.horizon-controls-right {
    display: flex;
    gap: 12px;
}

.horizon-select-group {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.horizon-select-group label {
    font-size: 12px;
    font-weight: 600;
    color: var(--horizon-light-text);
    text-transform: uppercase;
}

.horizon-select-group select,
.horizon-select-group input {
    padding: 8px 12px;
    border: 1px solid var(--horizon-border);
    border-radius: 6px;
    font-size: 14px;
    font-family: inherit;
    background: white;
    cursor: pointer;
    transition: all 0.2s ease;
}

.horizon-select-group select:hover,
.horizon-select-group input:hover {
    border-color: var(--horizon-primary);
}

.horizon-select-group select:focus,
.horizon-select-group input:focus {
    outline: none;
    border-color: var(--horizon-primary);
    box-shadow: 0 0 0 3px rgba(26, 115, 232, 0.1);
}

/* ============================================================================
   KPI METRIC CARDS
   ============================================================================ */

.kpi-cards-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 16px;
    margin-bottom: 30px;
}

.metric-card {
    background: white;
    border: 1px solid var(--horizon-border);
    border-radius: 12px;
    padding: 24px;
    position: relative;
    overflow: hidden;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
}

.metric-card:hover {
    box-shadow: var(--horizon-shadow-lg);
    transform: translateY(-2px);
}

.metric-card-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 16px;
}

.metric-card-icon {
    width: 48px;
    height: 48px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
}

.metric-card-icon.blue {
    background: rgba(26, 115, 232, 0.1);
    color: var(--horizon-primary);
}

.metric-card-icon.green {
    background: rgba(5, 205, 153, 0.1);
    color: var(--horizon-success);
}

.metric-card-icon.red {
    background: rgba(243, 66, 53, 0.1);
    color: var(--horizon-error);
}

.metric-card-icon.purple {
    background: rgba(108, 92, 231, 0.1);
    color: var(--horizon-secondary);
}

.metric-card-icon.orange {
    background: rgba(255, 154, 86, 0.1);
    color: var(--horizon-warning);
}

.metric-card-label {
    font-size: 14px;
    font-weight: 500;
    color: var(--horizon-light-text);
    margin-bottom: 8px;
}

.metric-card-value {
    font-size: 28px;
    font-weight: 700;
    color: var(--horizon-dark-text);
    margin-bottom: 12px;
}

.metric-card-trend {
    display: flex;
    align-items: center;
    gap: 4px;
    font-size: 13px;
    font-weight: 600;
}

.metric-card-trend.positive {
    color: var(--horizon-success);
}

.metric-card-trend.negative {
    color: var(--horizon-error);
}

/* ============================================================================
   TABLE SECTION
   ============================================================================ */

.table-section {
    background: white;
    border: 1px solid var(--horizon-border);
    border-radius: 12px;
    overflow: hidden;
    margin-bottom: 30px;
}

.table-header-bar {
    background: var(--horizon-bg-light);
    padding: 16px 24px;
    border-bottom: 1px solid var(--horizon-border);
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.table-title {
    font-size: 16px;
    font-weight: 700;
    color: var(--horizon-dark-text);
}

.table-wrapper {
    overflow-x: auto;
}

.data-table {
    width: 100%;
    border-collapse: collapse;
}

.data-table thead {
    background: var(--horizon-bg-light);
}

.data-table th {
    padding: 12px 16px;
    text-align: left;
    font-size: 12px;
    font-weight: 600;
    color: var(--horizon-dark-text);
    border-bottom: 1px solid var(--horizon-border);
    text-transform: uppercase;
}

.data-table td {
    padding: 12px 16px;
    border-bottom: 1px solid var(--horizon-border);
    font-size: 14px;
}

.data-table tbody tr {
    transition: background 0.2s ease;
}

.data-table tbody tr:hover {
    background: var(--horizon-bg-light);
}

/* ============================================================================
   BUTTONS
   ============================================================================ */

.btn-horizon {
    padding: 8px 16px;
    border-radius: 6px;
    border: none;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    text-decoration: none;
}

.btn-horizon-primary {
    background: var(--horizon-primary);
    color: white;
}

.btn-horizon-primary:hover {
    background: #1557b0;
    box-shadow: var(--horizon-shadow-md);
}

.btn-horizon-secondary {
    background: var(--horizon-bg-light);
    color: var(--horizon-dark-text);
    border: 1px solid var(--horizon-border);
}

.btn-horizon-secondary:hover {
    background: #e0e0e0;
}

.btn-branch-view {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 12px;
    background: var(--horizon-primary);
    color: white;
    border-radius: 4px;
    border: none;
    text-decoration: none;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;
}

.btn-branch-view:hover {
    background: #1557b0;
    box-shadow: var(--horizon-shadow-md);
}

.btn-branch-view i {
    font-size: 12px;
}

/* ============================================================================
   BADGES
   ============================================================================ */

.badge-hot {
    background: rgba(243, 66, 53, 0.1);
    color: var(--horizon-error);
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 11px;
    font-weight: 600;
}

.badge-active {
    background: rgba(26, 115, 232, 0.1);
    color: var(--horizon-primary);
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 11px;
    font-weight: 600;
}

.badge-good {
    background: rgba(5, 205, 153, 0.1);
    color: var(--horizon-success);
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 11px;
    font-weight: 600;
}

.badge-rank {
    display: inline-block;
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 11px;
    font-weight: 600;
}

.badge-rank-1 {
    background: rgba(255, 154, 86, 0.1);
    color: var(--horizon-warning);
}

.badge-rank-2 {
    background: rgba(108, 92, 231, 0.1);
    color: var(--horizon-secondary);
}

.badge-rank-3 {
    background: rgba(243, 66, 53, 0.1);
    color: var(--horizon-error);
}

.badge-rank-other {
    background: var(--horizon-bg-light);
    color: var(--horizon-dark-text);
    border: 1px solid var(--horizon-border);
}

/* ============================================================================
   EMPTY STATE
   ============================================================================ */

.empty-state {
    text-align: center;
    padding: 40px 20px;
    color: var(--horizon-light-text);
}

.empty-state i {
    font-size: 48px;
    margin-bottom: 16px;
    opacity: 0.5;
}
</style>

<div class="horizon-dashboard">
    
    <!-- Header Section -->
    <div class="horizon-header">
        <div class="horizon-header-title">
            <h1><?php echo $level_label; ?></h1>
            <p>Comprehensive performance metrics and analytics</p>
        </div>
        <div class="horizon-header-controls">
            <button class="btn-horizon btn-horizon-secondary" id="refreshBtn" title="Refresh data">
                <i class="fa fa-refresh"></i> Refresh
            </button>
        </div>
    </div>

    <!-- Control Bar / Filters -->
    <div class="horizon-control-bar">
        <div class="horizon-controls-left">
            <div class="horizon-select-group">
                <label>Period</label>
                <select id="periodSelect">
                    <?php foreach ($periods as $p): ?>
                        <?php 
                            // Display label for special periods, or formatted month for regular periods
                            $display_text = isset($p['label']) && $p['label'] 
                                ? $p['label']
                                : date('M Y', strtotime($p['period'] . '-01'));
                            
                            $is_selected = ($p['period'] === $period) ? 'selected' : '';
                        ?>
                        <option value="<?php echo $p['period']; ?>" <?php echo $is_selected; ?>>
                            <?php echo $display_text; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Combined Warehouse & Pharmacy Dropdown (Hierarchical) -->
            <div class="horizon-select-group">
                <label>Location</label>
                <select id="entitySelect">
                    <option value="">-- Select Location --</option>
                    <?php if (!empty($warehouse_pharmacy_hierarchy)): ?>
                        <?php foreach ($warehouse_pharmacy_hierarchy as $entity): ?>
                            <?php if ($entity['warehouse_type'] === 'warehouse' && !empty($entity['children'])): ?>
                                <!-- Warehouse with child pharmacies -->
                                <optgroup label="📦 <?php echo htmlspecialchars($entity['name']); ?> (Warehouse)">
                                    <?php foreach ($entity['children'] as $pharmacy): ?>
                                        <option value="<?php echo $pharmacy['id']; ?>" data-type="pharmacy" <?php echo ($pharmacy['id'] == $selected_entity_id) ? 'selected' : ''; ?>>
                                            → <?php echo htmlspecialchars($pharmacy['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </optgroup>
                            <?php elseif ($entity['warehouse_type'] === 'pharmacy' && empty($entity['children'])): ?>
                                <!-- Standalone Pharmacy -->
                                <option value="<?php echo $entity['id']; ?>" data-type="pharmacy" <?php echo ($entity['id'] == $selected_entity_id) ? 'selected' : ''; ?>>
                                    🏥 <?php echo htmlspecialchars($entity['name']); ?>
                                </option>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>

            <!-- Branch Level Dropdown (Cascades from Location/Pharmacy selection) -->
            <?php if (!empty($branches)): ?>
                <div class="horizon-select-group">
                    <label>Branch</label>
                    <select id="branchSelect">
                        <option value="">-- Select Branch --</option>
                        <?php foreach ($branches as $branch): ?>
                            <option value="<?php echo $branch->id; ?>">
                                <?php echo htmlspecialchars($branch->name); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            <?php endif; ?>
        </div>
        <div class="horizon-controls-right">
            <button class="btn-horizon btn-horizon-primary" id="applyFiltersBtn">
                <i class="fa fa-filter"></i> Apply Filters
            </button>
        </div>
    </div>

    <!-- KPI Metrics Cards Grid -->
    <div class="kpi-cards-grid">
        
        <!-- Total Gross Sales -->
        <div class="metric-card">
            <div class="metric-card-header">
                <div>
                    <div class="metric-card-label">Gross Sales</div>
                    <div class="metric-card-value">
                        <?php 
                            $gross_sales = $summary_metrics ? ($summary_metrics->total_gross_sales ?? 0) : 0;
                            echo number_format($gross_sales, 0, '.', ',');
                        ?>
                    </div>
                </div>
                <div class="metric-card-icon blue">
                    <i class="fa fa-shopping-cart"></i>
                </div>
            </div>
            <div style="color: var(--horizon-light-text); font-size: 12px;">
                Before discounts & tax
            </div>
        </div>

        <!-- Total Net Sales -->
        <div class="metric-card">
            <div class="metric-card-header">
                <div>
                    <div class="metric-card-label">Net Sales</div>
                    <div class="metric-card-value">
                        <?php 
                            $net_sales = $summary_metrics ? ($summary_metrics->total_net_sales ?? 0) : 0;
                            echo number_format($net_sales, 0, '.', ',');
                        ?>
                    </div>
                </div>
                <div class="metric-card-icon green">
                    <i class="fa fa-money"></i>
                </div>
            </div>
            <div style="color: var(--horizon-light-text); font-size: 12px;">
                After discounts & tax
            </div>
        </div>

        <!-- Total Margin -->
        <div class="metric-card">
            <div class="metric-card-header">
                <div>
                    <div class="metric-card-label">Total Margin</div>
                    <div class="metric-card-value">
                        <?php 
                            $margin = $summary_metrics ? ($summary_metrics->total_margin ?? 0) : 0;
                            echo number_format($margin, 0, '.', ',');
                        ?>
                    </div>
                </div>
                <div class="metric-card-icon green">
                    <i class="fa fa-line-chart"></i>
                </div>
            </div>
            <div>
                <?php if ($summary_metrics && isset($summary_metrics->margin_percentage)): ?>
                    <small style="color: var(--horizon-light-text);">
                        <?php echo $summary_metrics->margin_percentage; ?>% Margin Ratio
                    </small>
                <?php endif; ?>
            </div>
        </div>

        <!-- Total Customers -->
        <div class="metric-card">
            <div class="metric-card-header">
                <div>
                    <div class="metric-card-label">Total Customers</div>
                    <div class="metric-card-value">
                        <?php 
                            $customers = $summary_metrics ? ($summary_metrics->total_customers ?? 0) : 0;
                            echo number_format($customers, 0, '.', ',');
                        ?>
                    </div>
                </div>
                <div class="metric-card-icon purple">
                    <i class="fa fa-users"></i>
                </div>
            </div>
            <div style="color: var(--horizon-light-text); font-size: 12px;">
                Active customers
            </div>
        </div>

        <!-- Total Items Sold -->
        <div class="metric-card">
            <div class="metric-card-header">
                <div>
                    <div class="metric-card-label">Items Sold</div>
                    <div class="metric-card-value">
                        <?php 
                            $items = $summary_metrics ? ($summary_metrics->total_items_sold ?? 0) : 0;
                            echo number_format((int)$items, 0, '.', ',');
                        ?>
                    </div>
                </div>
                <div class="metric-card-icon orange">
                    <i class="fa fa-cubes"></i>
                </div>
            </div>
            <div style="color: var(--horizon-light-text); font-size: 12px;">
                Total units sold
            </div>
        </div>

        <!-- Total Transactions -->
        <div class="metric-card">
            <div class="metric-card-header">
                <div>
                    <div class="metric-card-label">Transactions</div>
                    <div class="metric-card-value">
                        <?php 
                            $transactions = $summary_metrics ? ($summary_metrics->total_transactions ?? 0) : 0;
                            echo number_format($transactions, 0, '.', ',');
                        ?>
                    </div>
                </div>
                <div class="metric-card-icon blue">
                    <i class="fa fa-exchange"></i>
                </div>
            </div>
            <div style="color: var(--horizon-light-text); font-size: 12px;">
                <?php if ($summary_metrics && isset($summary_metrics->warehouses_with_sales)): ?>
                    <?php echo $summary_metrics->warehouses_with_sales; ?> active locations
                <?php endif; ?>
            </div>
        </div>

        <!-- Avg Transaction Value -->
        <?php if ($summary_metrics && isset($summary_metrics->average_transaction_value)): ?>
            <div class="metric-card">
                <div class="metric-card-header">
                    <div>
                        <div class="metric-card-label">Avg Transaction</div>
                        <div class="metric-card-value">
                            <?php echo number_format($summary_metrics->average_transaction_value, 0); ?>
                        </div>
                    </div>
                    <div class="metric-card-icon green">
                        <i class="fa fa-money"></i>
                    </div>
                </div>
                <div style="color: var(--horizon-light-text); font-size: 12px;">
                    SAR per transaction
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Branches Sales Table (shown when pharmacy selected) -->
    <?php if (!empty($branches_with_sales) && !empty($selected_pharmacy_id)): ?>
        <div class="table-section">
            <div class="table-header-bar">
                <div class="table-title">
                    <i class="fa fa-sitemap" style="color: var(--horizon-primary); margin-right: 8px;"></i>
                    Branch Performance
                </div>
            </div>

            <div class="table-wrapper">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th style="width: 25%;">Branch Name</th>
                            <th style="width: 15%; text-align: right;">Total Revenue</th>
                            <th style="width: 15%; text-align: right;">Net Revenue</th>
                            <th style="width: 15%; text-align: right;">Profit/Loss</th>
                            <th style="width: 12%; text-align: right;">Margin %</th>
                            <th style="width: 10%; text-align: center;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($branches_with_sales as $branch): 
                            $total_revenue = $branch->kpi_total_revenue ?? 0;
                            $net_revenue = $branch->kpi_net_revenue ?? 0;
                            $profit_loss = $branch->kpi_profit_loss ?? 0;
                            $margin_pct = $branch->kpi_profit_margin_pct ?? 0;
                            $is_negative = $profit_loss < 0;
                        ?>
                            <tr>
                                <td>
                                    <strong><?php echo htmlspecialchars($branch->branch_name); ?></strong>
                                    <div style="font-size: 11px; color: var(--horizon-light-text); margin-top: 2px;">
                                        <?php echo htmlspecialchars($branch->branch_code ?? 'N/A'); ?>
                                    </div>
                                </td>
                                <td style="text-align: right; font-weight: 600;">
                                    <?php echo number_format($total_revenue, 2, '.', ','); ?> SAR
                                </td>
                                <td style="text-align: right; font-weight: 600; color: var(--horizon-success);">
                                    <?php echo number_format($net_revenue, 2, '.', ','); ?> SAR
                                </td>
                                <td style="text-align: right; font-weight: 600; color: <?php echo $is_negative ? 'var(--horizon-error)' : 'var(--horizon-success)'; ?>;">
                                    <?php echo number_format($profit_loss, 2, '.', ','); ?> SAR
                                </td>
                                <td style="text-align: right;">
                                    <span style="padding: 4px 8px; background: <?php echo ($margin_pct >= 15) ? 'var(--horizon-success-light)' : (($margin_pct >= 10) ? 'var(--horizon-warning-light)' : 'var(--horizon-error-light)'); ?>; border-radius: 4px; color: <?php echo ($margin_pct >= 15) ? 'var(--horizon-success)' : (($margin_pct >= 10) ? 'var(--horizon-warning)' : 'var(--horizon-error)'); ?>; font-weight: 600;">
                                        <?php echo number_format($margin_pct, 1); ?>%
                                    </span>
                                </td>
                                <td style="text-align: center;">
                                    <a href="<?php echo base_url('admin/cost_center/performance?period=' . $period . '&level=branch&warehouse_id=' . $branch->warehouse_id); ?>" class="btn-branch-view">
                                        <i class="fa fa-eye"></i> View
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>

    <!-- Best Moving Products Table -->
    <div class="table-section">
        <div class="table-header-bar">
            <div class="table-title">
                <i class="fa fa-star" style="color: var(--horizon-warning); margin-right: 8px;"></i>
                Best Moving Products (Top 5)
            </div>
        </div>

        <?php if (!empty($best_products)): ?>
            <div class="table-wrapper">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th style="width: 8%;">Rank</th>
                            <th style="width: 18%;">Product</th>
                            <th style="width: 12%;">Code</th>
                            <th style="width: 10%; text-align: right;">Qty</th>
                            <th style="width: 13%; text-align: right;">Gross Revenue</th>
                            <th style="width: 13%; text-align: right;">Net Revenue</th>
                            <th style="width: 10%; text-align: right;">Margin</th>
                            <th style="width: 8%; text-align: center;">% Share</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $total_revenue = array_sum(array_map(function($p) { 
                            return $p->total_revenue ?? 0; 
                        }, $best_products));
                        
                        foreach ($best_products as $index => $product): 
                            $percentage = $total_revenue > 0 ? (($product->total_revenue ?? 0) / $total_revenue) * 100 : 0;
                            $rank = $index + 1;
                        ?>
                            <tr>
                                <td>
                                    <?php if ($rank === 1): ?>
                                        <span class="badge-rank badge-rank-1">🥇 #<?php echo $rank; ?></span>
                                    <?php elseif ($rank === 2): ?>
                                        <span class="badge-rank badge-rank-2">🥈 #<?php echo $rank; ?></span>
                                    <?php elseif ($rank === 3): ?>
                                        <span class="badge-rank badge-rank-3">🥉 #<?php echo $rank; ?></span>
                                    <?php else: ?>
                                        <span class="badge-rank badge-rank-other">#<?php echo $rank; ?></span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <strong><?php echo htmlspecialchars($product->product_name ?? 'N/A'); ?></strong>
                                </td>
                                <td>
                                    <small style="color: var(--horizon-light-text);">
                                        <?php echo htmlspecialchars($product->product_code ?? 'N/A'); ?>
                                    </small>
                                </td>
                                <td style="text-align: right;">
                                    <?php echo number_format((int)($product->total_quantity_sold ?? 0), 0, '.', ','); ?>
                                </td>
                                <td style="text-align: right; font-weight: 600;">
                                    <?php echo number_format($product->total_revenue ?? 0, 2, '.', ','); ?> SAR
                                </td>
                                <td style="text-align: right; font-weight: 600; color: var(--horizon-success);">
                                    <?php echo number_format($product->total_net_revenue ?? 0, 2, '.', ','); ?> SAR
                                </td>
                                <td style="text-align: right;">
                                    <div style="display: flex; align-items: center; justify-content: flex-end; gap: 4px;">
                                        <span><?php echo number_format($product->margin_percentage ?? 0, 1); ?>%</span>
                                        <span style="color: var(--horizon-success);"><?php echo number_format($product->total_margin ?? 0, 0, '.', ','); ?></span>
                                    </div>
                                </td>
                                <td style="text-align: center;">
                                    <div style="display: flex; align-items: center; justify-content: center; gap: 8px;">
                                        <small><?php echo number_format($percentage, 1); ?>%</small>
                                        <div style="width: 50px; height: 20px; background: var(--horizon-bg-light); border-radius: 4px; overflow: hidden;">
                                            <div style="width: <?php echo min($percentage * 2, 100); ?>%; height: 100%; background: <?php echo ($percentage >= 20) ? 'var(--horizon-error)' : (($percentage >= 10) ? 'var(--horizon-primary)' : 'var(--horizon-success)'); ?>;"></div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <i class="fa fa-inbox"></i>
                <p>No product data available for the selected period.</p>
            </div>
        <?php endif; ?>
    </div>

</div>

<!-- JavaScript for Interactions -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    
    // Refresh button
    document.getElementById('refreshBtn')?.addEventListener('click', function() {
        location.reload();
    });
    
    // Apply filters
    document.getElementById('applyFiltersBtn')?.addEventListener('click', function() {
        const period = document.getElementById('periodSelect').value;
        let url = '<?php echo base_url('admin/cost_center/performance'); ?>?period=' + period;
        
        const entitySelect = document.getElementById('entitySelect');
        const branchSelect = document.getElementById('branchSelect');
        
        const entityId = entitySelect?.value;
        const branchId = branchSelect?.value;
        
        // Build URL based on selection
        if (branchId && branchId !== '') {
            // Branch level selected
            url += '&entity_id=' + branchId;
        } else if (entityId && entityId !== '') {
            // Entity (warehouse or pharmacy) level selected
            url += '&entity_id=' + entityId;
        } else {
            // Company level (all)
            url += '&entity_id=';
        }
        
        window.location.href = url;
    });
    
    // Period change (auto-apply)
    document.getElementById('periodSelect')?.addEventListener('change', function() {
        document.getElementById('applyFiltersBtn').click();
    });
    
    // Entity (Location) change - reset branch, then apply
    document.getElementById('entitySelect')?.addEventListener('change', function() {
        const entityId = this.value;
        
        // Reset branch selection
        const branchSelect = document.getElementById('branchSelect');
        if (branchSelect) branchSelect.value = '';
        
        if (entityId) {
            // Apply filter - this will load data based on entity type (warehouse or pharmacy)
            document.getElementById('applyFiltersBtn').click();
        }
    });
    
    // Branch change - apply
    document.getElementById('branchSelect')?.addEventListener('change', function() {
        const branchId = this.value;
        if (branchId && branchId !== '') {
            document.getElementById('applyFiltersBtn').click();
        }
    });
});
</script>
