<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<style>
    :root {
        --primary: #0d6efd;
        --success: #198754;
        --danger: #dc3545;
        --warning: #ffc107;
        --info: #0dcaf0;
        --light: #f8f9fa;
        --dark: #212529;
        --gray-100: #f8f9fa;
        --gray-200: #e9ecef;
        --gray-300: #dee2e6;
        --gray-400: #ced4da;
        --gray-500: #adb5bd;
        --gray-600: #6c757d;
        --gray-700: #495057;
        --gray-800: #343a40;
        --gray-900: #212529;
    }

    * {
        box-sizing: border-box;
    }

    body {
        background-color: var(--gray-100);
    }

    .dashboard-wrapper {
        padding: 1.5rem;
        background-color: var(--gray-100);
    }

    /* Header Section */
    .dashboard-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 2rem;
        padding: 0;
    }

    .dashboard-header-left h1 {
        font-size: 2rem;
        font-weight: 700;
        color: var(--gray-900);
        margin: 0;
    }

    .dashboard-header-left p {
        font-size: 0.95rem;
        color: var(--gray-600);
        margin: 0.25rem 0 0 0;
    }

    .dashboard-header-right {
        text-align: right;
    }

    /* Stats Grid - Multi Row Smaller Cards */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
        gap: 1rem;
        margin-bottom: 2.5rem;
    }

    .stat-card {
        background-color: var(--stat-color);
        border-radius: 0.5rem;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
        overflow: hidden;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        padding: 1.5rem 1rem;
        color: white;
    }

    .stat-card:hover {
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.2);
        transform: translateY(-4px);
    }

    .stat-card.indigo {
        --stat-color: #4f46e5;
    }

    .stat-card.light-blue {
        --stat-color: #3b82f6;
    }

    .stat-card.yellow {
        --stat-color: #fbbf24;
        color: #1a1a1a;
    }

    .stat-card.red {
        --stat-color: #e55354;
    }

    .stat-card .fs-4 {
        font-size: 1.5rem !important;
    }

    .stat-card .fs-6 {
        font-size: 0.9rem !important;
    }

    .stat-card .icon {
        width: 12px;
        height: 12px;
        display: inline-block;
        margin: 0 0.2rem;
        vertical-align: middle;
    }

    /* Section Container */

    .stat-bar.positive {
        background: #86efac;
    }

    /* Section Container */
    .section {
        background: white;
        border-radius: 0.375rem;
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        margin-bottom: 1.5rem;
        overflow: hidden;
    }

    .section-header {
        background: linear-gradient(135deg, var(--gray-50) 0%, var(--gray-100) 100%);
        border-bottom: 1px solid var(--gray-300);
        padding: 1.5rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .section-title {
        font-size: 1.25rem;
        font-weight: 600;
        color: var(--gray-900);
        margin: 0;
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .section-title i {
        font-size: 1.5rem;
        color: var(--primary);
    }

    .section-body {
        padding: 1.5rem;
    }

    /* Quick Links Grid */
    .quick-links {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
        gap: 1rem;
        margin-bottom: 1rem;
    }

    .quick-link {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 1rem;
        border-radius: 0.375rem;
        border: 2px solid var(--gray-200);
        transition: all 0.3s ease;
        text-decoration: none;
        color: var(--gray-900);
        font-weight: 500;
        font-size: 0.85rem;
        text-align: center;
        gap: 0.5rem;
    }

    .quick-link i {
        font-size: 1.75rem;
    }

    .quick-link:hover {
        border-color: var(--primary);
        background-color: rgba(13, 110, 253, 0.05);
        color: var(--primary);
        transform: translateY(-2px);
    }

    /* Data Tables */
    .data-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 0.9rem;
    }

    .data-table thead {
        background-color: var(--gray-100);
        border-bottom: 2px solid var(--gray-300);
    }

    .data-table thead th {
        padding: 1rem;
        text-align: left;
        font-weight: 600;
        color: var(--gray-700);
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 0.5px;
    }

    .data-table tbody tr {
        border-bottom: 1px solid var(--gray-200);
        transition: background-color 0.2s ease;
    }

    .data-table tbody tr:hover {
        background-color: var(--gray-50);
    }

    .data-table tbody td {
        padding: 1rem;
        vertical-align: middle;
    }

    /* Badges */
    .badge {
        display: inline-block;
        padding: 0.35rem 0.65rem;
        font-size: 0.75rem;
        font-weight: 600;
        border-radius: 0.25rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .badge-primary {
        background-color: #cfe2ff;
        color: #084298;
    }

    .badge-success {
        background-color: #d1e7dd;
        color: #0f5132;
    }

    .badge-danger {
        background-color: #f8d7da;
        color: #842029;
    }

    .badge-warning {
        background-color: #fff3cd;
        color: #664d03;
    }

    .badge-info {
        background-color: #cff4fc;
        color: #055160;
    }

    .badge-secondary {
        background-color: #e2e3e5;
        color: #41464b;
    }

    /* Tabs */
    .nav-tabs {
        display: flex;
        border-bottom: 2px solid var(--gray-300);
        list-style: none;
        padding: 0;
        margin: 0 0 1.5rem 0;
        gap: 0;
    }

    .nav-tabs li {
        margin: 0;
    }

    .nav-tabs a {
        display: block;
        padding: 1rem 1.5rem;
        text-decoration: none;
        color: var(--gray-700);
        font-weight: 500;
        border-bottom: 2px solid transparent;
        transition: all 0.2s ease;
        cursor: pointer;
    }

    .nav-tabs a:hover {
        color: var(--primary);
        border-bottom-color: var(--primary);
    }

    .nav-tabs a.active {
        color: var(--primary);
        border-bottom-color: var(--primary);
    }

    /* Tab Content */
    .tab-content {
        display: none;
    }

    .tab-content.active {
        display: block;
    }

    /* Charts Section */
    .charts-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
        gap: 1.5rem;
        margin-bottom: 1.5rem;
    }

    .chart-container {
        background: white;
        border-radius: 0.375rem;
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        padding: 1.5rem;
        min-height: 400px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .chart-placeholder {
        width: 100%;
        height: 350px;
        background: linear-gradient(135deg, var(--gray-50) 0%, var(--gray-100) 100%);
        border-radius: 0.375rem;
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--gray-400);
        font-size: 0.9rem;
        text-align: center;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .stats-grid {
            grid-template-columns: 1fr;
        }

        .dashboard-header {
            flex-direction: column;
            align-items: flex-start;
            text-align: left;
        }

        .dashboard-header-right {
            margin-top: 1rem;
            text-align: left;
        }

        .charts-grid {
            grid-template-columns: 1fr;
        }

        .quick-links {
            grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
        }

        .data-table {
            font-size: 0.8rem;
        }

        .data-table thead th,
        .data-table tbody td {
            padding: 0.75rem 0.5rem;
        }

        .section-header {
            padding: 1rem;
        }

        .section-body {
            padding: 1rem;
        }
    }
</style>

<div class="dashboard-wrapper">
    <!-- Dashboard Header -->
    <div class="dashboard-header">
        <div class="dashboard-header-left">
            <h1><?= lang('dashboard') ?></h1>
            <p><?= lang('welcome_back') ?>, <strong><?= $this->session->userdata('username') ?></strong>!</p>
        </div>
        <div class="dashboard-header-right">
            <p style="font-size: 0.9rem; color: var(--gray-600);"><?= date('l, F j, Y') ?></p>
        </div>
    </div>

    <!-- Statistics Grid -->
    <div class="stats-grid">
        <!-- Sales Card - Indigo -->
        <div class="stat-card indigo">
            <div class="fs-4 fw-semibold"><?php 
                $total_sales = 0;
                if($sales) foreach($sales as $sale) $total_sales += $sale->total;
                echo round($total_sales / 1000, 1) . 'K';
            ?> <span class="fs-6 fw-normal">(+1.2% <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" class="icon" role="img" aria-hidden="true"><polygon fill="var(--ci-primary-color, currentColor)" points="367.997 338.75 271.999 434.747 271.999 17.503 239.999 17.503 239.999 434.745 144.003 338.75 121.376 361.377 256 496 390.624 361.377 367.997 338.75" class="ci-primary"></polygon></svg>)</span></div>
            <small class="text-white-75"><?= lang('sales') ?></small>
        </div>

        <!-- Purchases Card - Light Blue -->
        <div class="stat-card light-blue">
            <div class="fs-4 fw-semibold"><?php 
                $total_purchases = 0;
                if($purchases) foreach($purchases as $purchase) $total_purchases += $purchase->total;
                echo round($total_purchases / 1000, 1) . 'K';
            ?> <span class="fs-6 fw-normal">(+0.8% <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" class="icon" role="img" aria-hidden="true"><polygon fill="var(--ci-primary-color, currentColor)" points="367.997 338.75 271.999 434.747 271.999 17.503 239.999 17.503 239.999 434.745 144.003 338.75 121.376 361.377 256 496 390.624 361.377 367.997 338.75" class="ci-primary"></polygon></svg>)</span></div>
            <small class="text-white-75"><?= lang('purchases') ?></small>
        </div>

        <!-- Quotes Card - Yellow -->
        <div class="stat-card yellow">
            <div class="fs-4 fw-semibold"><?php echo isset($quotes) && is_array($quotes) ? count($quotes) : 0; ?> <span class="fs-6 fw-normal">(-0.5% <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" class="icon" role="img" aria-hidden="true"><polygon fill="var(--ci-primary-color, currentColor)" points="256 16 121.376 150.623 144.003 173.25 240 77.255 240 494.497 272 494.497 272 77.257 367.997 173.25 390.624 150.623 256 16" class="ci-primary"></polygon></svg>)</span></div>
            <small class="text-dark-75"><?= lang('quotes') ?></small>
        </div>

        <!-- Stock Value Card - Red -->
        <div class="stat-card red">
            <div class="fs-4 fw-semibold"><?php echo isset($stock) ? round($stock->total / 1000, 1) . 'K' : '0K'; ?> <span class="fs-6 fw-normal">(+2.1% <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" class="icon" role="img" aria-hidden="true"><polygon fill="var(--ci-primary-color, currentColor)" points="367.997 338.75 271.999 434.747 271.999 17.503 239.999 17.503 239.999 434.745 144.003 338.75 121.376 361.377 256 496 390.624 361.377 367.997 338.75" class="ci-primary"></polygon></svg>)</span></div>
            <small class="text-white-75"><?= lang('stock_value') ?></small>
        </div>
    </div>

    <!-- Charts Section -->
    <?php if (($Owner || $Admin) && $chatData) {
        foreach ($chatData as $month_sale) {
            $months[]     = date('M-Y', strtotime($month_sale->month));
            $msales[]     = $month_sale->sales;
            $mtax1[]      = $month_sale->tax1;
            $mtax2[]      = $month_sale->tax2;
            $mpurchases[] = $month_sale->purchases;
            $mtax3[]      = $month_sale->ptax;
        } ?>
    <div class="section" style="margin-bottom: 2rem;">
        <div class="section-header">
            <h2 class="section-title"><i class="fa fa-bar-chart-o"></i><?= lang('overview_chart') ?></h2>
        </div>
        <div class="section-body">
            <div class="chart-placeholder">
                <div id="ov-chart" style="width:100%; height:350px;"></div>
            </div>
            <p style="text-align: center; color: var(--gray-600); margin-top: 1rem; font-size: 0.9rem;"><?= lang('chart_lable_toggle') ?></p>
        </div>
    </div>
    <?php } ?>

    <!-- Quick Links Section -->
    <div class="section" style="margin-bottom: 2rem;">
        <div class="section-header">
            <h2 class="section-title"><i class="fa fa-bolt"></i><?= lang('quick_links') ?></h2>
        </div>
        <div class="section-body">
            <div class="quick-links">
                <?php if ($Owner || $Admin) { ?>
                    <?php if ($GP['products-index']) { ?>
                        <a href="<?= admin_url('products') ?>" class="quick-link">
                            <i class="fa fa-barcode"></i>
                            <span><?= lang('products') ?></span>
                        </a>
                    <?php } ?>
                    
                    <?php if ($GP['sales-index']) { ?>
                        <a href="<?= admin_url('sales') ?>" class="quick-link">
                            <i class="fa fa-shopping-cart"></i>
                            <span><?= lang('sales') ?></span>
                        </a>
                    <?php } ?>
                    
                    <?php if ($GP['quotes-index']) { ?>
                        <a href="<?= admin_url('quotes') ?>" class="quick-link">
                            <i class="fa fa-file-text"></i>
                            <span><?= lang('quotes') ?></span>
                        </a>
                    <?php } ?>
                    
                    <?php if ($GP['purchases-index']) { ?>
                        <a href="<?= admin_url('purchases') ?>" class="quick-link">
                            <i class="fa fa-inbox"></i>
                            <span><?= lang('purchases') ?></span>
                        </a>
                    <?php } ?>
                    
                    <?php if ($GP['transfers-index']) { ?>
                        <a href="<?= admin_url('transfers') ?>" class="quick-link">
                            <i class="fa fa-exchange"></i>
                            <span><?= lang('transfers') ?></span>
                        </a>
                    <?php } ?>
                    
                    <?php if ($GP['customers-index']) { ?>
                        <a href="<?= admin_url('customers') ?>" class="quick-link">
                            <i class="fa fa-users"></i>
                            <span><?= lang('customers') ?></span>
                        </a>
                    <?php } ?>
                    
                    <?php if ($GP['suppliers-index']) { ?>
                        <a href="<?= admin_url('suppliers') ?>" class="quick-link">
                            <i class="fa fa-building"></i>
                            <span><?= lang('suppliers') ?></span>
                        </a>
                    <?php } ?>
                    
                    <a href="<?= admin_url('notifications') ?>" class="quick-link">
                        <i class="fa fa-bell"></i>
                        <span><?= lang('notifications') ?></span>
                    </a>
                    
                    <?php if ($Owner) { ?>
                        <a href="<?= admin_url('auth/users') ?>" class="quick-link">
                            <i class="fa fa-key"></i>
                            <span><?= lang('users') ?></span>
                        </a>
                        <a href="<?= admin_url('system_settings') ?>" class="quick-link">
                            <i class="fa fa-cogs"></i>
                            <span><?= lang('settings') ?></span>
                        </a>
                    <?php } ?>
                <?php } else { ?>
                    <?php if ($GP['products-index']) { ?>
                        <a href="<?= admin_url('products') ?>" class="quick-link">
                            <i class="fa fa-barcode"></i>
                            <span><?= lang('products') ?></span>
                        </a>
                    <?php } ?>
                    
                    <?php if ($GP['sales-index']) { ?>
                        <a href="<?= admin_url('sales') ?>" class="quick-link">
                            <i class="fa fa-shopping-cart"></i>
                            <span><?= lang('sales') ?></span>
                        </a>
                    <?php } ?>
                    
                    <?php if ($GP['quotes-index']) { ?>
                        <a href="<?= admin_url('quotes') ?>" class="quick-link">
                            <i class="fa fa-file-text"></i>
                            <span><?= lang('quotes') ?></span>
                        </a>
                    <?php } ?>
                    
                    <?php if ($GP['purchases-index']) { ?>
                        <a href="<?= admin_url('purchases') ?>" class="quick-link">
                            <i class="fa fa-inbox"></i>
                            <span><?= lang('purchases') ?></span>
                        </a>
                    <?php } ?>
                    
                    <?php if ($GP['transfers-index']) { ?>
                        <a href="<?= admin_url('transfers') ?>" class="quick-link">
                            <i class="fa fa-exchange"></i>
                            <span><?= lang('transfers') ?></span>
                        </a>
                    <?php } ?>
                    
                    <?php if ($GP['customers-index']) { ?>
                        <a href="<?= admin_url('customers') ?>" class="quick-link">
                            <i class="fa fa-users"></i>
                            <span><?= lang('customers') ?></span>
                        </a>
                    <?php } ?>
                    
                    <?php if ($GP['suppliers-index']) { ?>
                        <a href="<?= admin_url('suppliers') ?>" class="quick-link">
                            <i class="fa fa-building"></i>
                            <span><?= lang('suppliers') ?></span>
                        </a>
                    <?php } ?>
                <?php } ?>
            </div>
        </div>
    </div>

    <!-- Latest Data Section -->
    <div class="section" style="margin-bottom: 2rem;">
        <div class="section-header">
            <h2 class="section-title"><i class="fa fa-list"></i><?= lang('latest_five') ?></h2>
        </div>
        <div class="section-body">
            <ul class="nav-tabs" id="dbTab">
                <?php if ($Owner || $Admin || $GP['sales-index']) { ?>
                    <li><a href="#sales" onclick="switchTab(event, 'sales')" class="active"><?= lang('sales') ?></a></li>
                <?php } ?>
                
                <?php if ($Owner || $Admin || $GP['quotes-index']) { ?>
                    <li><a href="#quotes" onclick="switchTab(event, 'quotes')"><?= lang('quotes') ?></a></li>
                <?php } ?>
                
                <?php if ($Owner || $Admin || $GP['purchases-index']) { ?>
                    <li><a href="#purchases" onclick="switchTab(event, 'purchases')"><?= lang('purchases') ?></a></li>
                <?php } ?>
                
                <?php if ($Owner || $Admin || $GP['suppliers-index']) { ?>
                    <li><a href="#suppliers" onclick="switchTab(event, 'suppliers')"><?= lang('suppliers') ?></a></li>
                <?php } ?>
                
                <?php if ($Owner || $Admin || $GP['customers-index']) { ?>
                    <li><a href="#customers" onclick="switchTab(event, 'customers')"><?= lang('customers') ?></a></li>
                <?php } ?>
                
                <?php if ($Owner || $Admin || $GP['transfers-index']) { ?>
                    <li><a href="#transfers" onclick="switchTab(event, 'transfers')"><?= lang('transfers') ?></a></li>
                <?php } ?>
            </ul>

            <!-- Sales Tab -->
            <?php if ($Owner || $Admin || $GP['sales-index']) { ?>
                <div id="sales" class="tab-content active">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th><?= lang('ref_no') ?></th>
                                <th><?= lang('customer') ?></th>
                                <th><?= lang('date') ?></th>
                                <th><?= lang('amount') ?></th>
                                <th><?= lang('status') ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($sales) {
                                foreach ($sales as $sale) { ?>
                                    <tr>
                                        <td><a href="<?= admin_url('sales/view/' . $sale->id) ?>" style="color: var(--primary); text-decoration: none;"><?= $sale->reference_no ?></a></td>
                                        <td><?= $sale->customer_name ?></td>
                                        <td><?= date('M d, Y', strtotime($sale->date)) ?></td>
                                        <td><?= $this->sma->convertNumber($sale->total) ?></td>
                                        <td><?= row_status($sale->status) ?></td>
                                    </tr>
                                <?php }
                            } else { ?>
                                <tr><td colspan="5" style="text-align: center; color: var(--gray-600);">No sales found</td></tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            <?php } ?>

            <!-- Quotes Tab -->
            <?php if ($Owner || $Admin || $GP['quotes-index']) { ?>
                <div id="quotes" class="tab-content">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th><?= lang('ref_no') ?></th>
                                <th><?= lang('customer') ?></th>
                                <th><?= lang('date') ?></th>
                                <th><?= lang('amount') ?></th>
                                <th><?= lang('status') ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($quotes) {
                                foreach ($quotes as $quote) { ?>
                                    <tr>
                                        <td><a href="<?= admin_url('quotes/view/' . $quote->id) ?>" style="color: var(--primary); text-decoration: none;"><?= $quote->reference_no ?></a></td>
                                        <td><?= $quote->customer_name ?></td>
                                        <td><?= date('M d, Y', strtotime($quote->date)) ?></td>
                                        <td><?= $this->sma->convertNumber($quote->total) ?></td>
                                        <td><?= row_status($quote->status) ?></td>
                                    </tr>
                                <?php }
                            } else { ?>
                                <tr><td colspan="5" style="text-align: center; color: var(--gray-600);">No quotes found</td></tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            <?php } ?>

            <!-- Purchases Tab -->
            <?php if ($Owner || $Admin || $GP['purchases-index']) { ?>
                <div id="purchases" class="tab-content">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th><?= lang('ref_no') ?></th>
                                <th><?= lang('supplier') ?></th>
                                <th><?= lang('date') ?></th>
                                <th><?= lang('amount') ?></th>
                                <th><?= lang('status') ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($purchases) {
                                foreach ($purchases as $purchase) { ?>
                                    <tr>
                                        <td><a href="<?= admin_url('purchases/view/' . $purchase->id) ?>" style="color: var(--primary); text-decoration: none;"><?= $purchase->reference_no ?></a></td>
                                        <td><?= $purchase->supplier_name ?></td>
                                        <td><?= date('M d, Y', strtotime($purchase->date)) ?></td>
                                        <td><?= $this->sma->convertNumber($purchase->total) ?></td>
                                        <td><?= row_status($purchase->status) ?></td>
                                    </tr>
                                <?php }
                            } else { ?>
                                <tr><td colspan="5" style="text-align: center; color: var(--gray-600);">No purchases found</td></tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            <?php } ?>

            <!-- Suppliers Tab -->
            <?php if ($Owner || $Admin || $GP['suppliers-index']) { ?>
                <div id="suppliers" class="tab-content">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th><?= lang('name') ?></th>
                                <th><?= lang('email') ?></th>
                                <th><?= lang('phone') ?></th>
                                <th><?= lang('city') ?></th>
                                <th><?= lang('status') ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($suppliers) {
                                foreach ($suppliers as $supplier) { ?>
                                    <tr>
                                        <td><a href="<?= admin_url('suppliers/view/' . $supplier->id) ?>" style="color: var(--primary); text-decoration: none;"><?= $supplier->company ?></a></td>
                                        <td><?= $supplier->email ?></td>
                                        <td><?= $supplier->phone ?></td>
                                        <td><?= $supplier->city ?></td>
                                        <td><?= row_status($supplier->status) ?></td>
                                    </tr>
                                <?php }
                            } else { ?>
                                <tr><td colspan="5" style="text-align: center; color: var(--gray-600);">No suppliers found</td></tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            <?php } ?>

            <!-- Customers Tab -->
            <?php if ($Owner || $Admin || $GP['customers-index']) { ?>
                <div id="customers" class="tab-content">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th><?= lang('name') ?></th>
                                <th><?= lang('email') ?></th>
                                <th><?= lang('phone') ?></th>
                                <th><?= lang('city') ?></th>
                                <th><?= lang('status') ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($customers) {
                                foreach ($customers as $customer) { ?>
                                    <tr>
                                        <td><a href="<?= admin_url('customers/view/' . $customer->id) ?>" style="color: var(--primary); text-decoration: none;"><?= $customer->name ?></a></td>
                                        <td><?= $customer->email ?></td>
                                        <td><?= $customer->phone ?></td>
                                        <td><?= $customer->city ?></td>
                                        <td><?= row_status($customer->status) ?></td>
                                    </tr>
                                <?php }
                            } else { ?>
                                <tr><td colspan="5" style="text-align: center; color: var(--gray-600);">No customers found</td></tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            <?php } ?>

            <!-- Transfers Tab -->
            <?php if ($Owner || $Admin || $GP['transfers-index']) { ?>
                <div id="transfers" class="tab-content">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th><?= lang('ref_no') ?></th>
                                <th><?= lang('from_warehouse') ?></th>
                                <th><?= lang('to_warehouse') ?></th>
                                <th><?= lang('date') ?></th>
                                <th><?= lang('status') ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($transfers) {
                                foreach ($transfers as $transfer) { ?>
                                    <tr>
                                        <td><a href="<?= admin_url('transfers/view/' . $transfer->id) ?>" style="color: var(--primary); text-decoration: none;"><?= $transfer->reference_no ?></a></td>
                                        <td><?= $transfer->from_warehouse ?></td>
                                        <td><?= $transfer->to_warehouse ?></td>
                                        <td><?= date('M d, Y', strtotime($transfer->date)) ?></td>
                                        <td><?= row_status($transfer->status) ?></td>
                                    </tr>
                                <?php }
                            } else { ?>
                                <tr><td colspan="5" style="text-align: center; color: var(--gray-600);">No transfers found</td></tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            <?php } ?>
        </div>
    </div>

    <!-- Best Sellers Section (for Admin) -->
    <?php if ($Owner || $Admin) { ?>
        <div class="charts-grid">
            <div class="section">
                <div class="section-header">
                    <h2 class="section-title"><i class="fa fa-trending-up"></i><?= lang('best_sellers') ?> (<?= date('M-Y', time()) ?>)</h2>
                </div>
                <div class="section-body">
                    <div id="bschart" style="width:100%; height:350px;"></div>
                </div>
            </div>
            <div class="section">
                <div class="section-header">
                    <h2 class="section-title"><i class="fa fa-trending-up"></i><?= lang('best_sellers') ?> (<?= date('M-Y', strtotime('-1 month')) ?>)</h2>
                </div>
                <div class="section-body">
                    <div id="lmbschart" style="width:100%; height:350px;"></div>
                </div>
            </div>
        </div>
    <?php } ?>
</div>

<script>
    function switchTab(e, tabName) {
        e.preventDefault();
        
        // Hide all tab contents
        const contents = document.querySelectorAll('.tab-content');
        contents.forEach(content => content.classList.remove('active'));
        
        // Remove active class from all links
        const links = document.querySelectorAll('#dbTab a');
        links.forEach(link => link.classList.remove('active'));
        
        // Show selected tab and mark link as active
        const selectedTab = document.getElementById(tabName);
        if (selectedTab) {
            selectedTab.classList.add('active');
        }
        e.target.classList.add('active');
    }
    
    // Set first tab as active on page load
    document.addEventListener('DOMContentLoaded', function() {
        const firstTab = document.querySelector('#dbTab a');
        if (firstTab) {
            firstTab.classList.add('active');
        }
    });
</script>
