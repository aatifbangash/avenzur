<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-bar-chart"></i> <?= lang('Procurement Dashboard'); ?></h2>
    </div>
</div>

<!-- Redesigned Quick Metrics Section with Background Colors -->
<div class="row mt-4 text-center">
    <div class="col-md-3">
        <div class="card shadow-sm" style="border: 1px solid #e3e3e3; height: 100px; background-color: #007bff; color: #fff;">
            <div class="card-body d-flex align-items-center justify-content-between">
                <div>
                    <h6 class="card-title">Total Suppliers</h6>
                    <h4 class="card-text font-weight-bold mb-0">
                        <?= isset($total_suppliers) ? (int)$total_suppliers : 0 ?>
                    </h4>
                </div>
                <i class="fa fa-users fa-2x"></i>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card shadow-sm" style="border: 1px solid #e3e3e3; height: 100px; background-color: #28a745; color: #fff;">
            <div class="card-body d-flex align-items-center justify-content-between">
                <div>
                    <h6 class="card-title">Total Purchases</h6>
                    <h4 class="card-text font-weight-bold mb-0">
                        <?= isset($total_po) ? (int)$total_po : 0 ?>
                    </h4>
                </div>
                <i class="fa fa-shopping-cart fa-2x"></i>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card shadow-sm" style="border: 1px solid #e3e3e3; height: 100px; background-color: #ffc107; color: #212529;">
            <div class="card-body d-flex align-items-center justify-content-between">
                <div>
                    <h6 class="card-title">Total Expenditure</h6>
                    <h4 class="card-text font-weight-bold mb-0">
                        $<?= isset($total_po_amount) ? number_format($total_po_amount, 2) : '0.00' ?>
                    </h4>
                </div>
                <i class="fa fa-dollar-sign fa-2x"></i>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card shadow-sm" style="border: 1px solid #e3e3e3; height: 100px; background-color: #dc3545; color: #fff;">
            <div class="card-body d-flex align-items-center justify-content-between">
                <div>
                    <h6 class="card-title">Invoices</h6>
                    <h4 class="card-text font-weight-bold mb-0">
                        <?= isset($total_invoices) ? (int)$total_invoices : 0 ?>
                    </h4>
                </div>
                <i class="fa fa-file-invoice fa-2x"></i>
            </div>
        </div>
    </div>
</div>

<!-- Enhanced Charts Section -->
<div class="row mt-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5>Supplier Performance</h5>
            </div>
            <div class="card-body">
                <div id="supplierPerformanceChart" style="height: 300px;"></div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-success text-white">
                <h5>Monthly Expenditure</h5>
            </div>
            <div class="card-body">
                <div id="monthlyExpenditureChart" style="height: 300px;"></div>
            </div>
        </div>
    </div>
</div>

<!-- Additional Charts Section -->
<div class="row mt-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5>Top Suppliers by Expenditure</h5>
            </div>
            <div class="card-body">
                <div id="topSuppliersChart" style="height: 300px;"></div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5>Purchase Trends (Quarterly)</h5>
            </div>
            <div class="card-body">
                <div id="purchaseTrendsChart" style="height: 300px;"></div>
            </div>
        </div>
    </div>
</div>

<!-- Item-Level Insights -->
<div class="row mt-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5>Top Purchased Items</h5>
            </div>
            <div class="card-body">
                <ul>
                    <li>Item A - 500 units</li>
                    <li>Item B - 300 units</li>
                    <li>Item C - 200 units</li>
                </ul>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5>Items Nearing Expiry</h5>
            </div>
            <div class="card-body">
                <ul>
                    <li>Item X - Expiry: 2025-11-01</li>
                    <li>Item Y - Expiry: 2025-12-15</li>
                    <li>Item Z - Expiry: 2026-01-10</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/echarts/dist/echarts.min.js"></script>
<script>
    // Supplier Performance Chart
    var supplierChart = echarts.init(document.getElementById('supplierPerformanceChart'));
    var supplierOption = {
        title: {
            text: 'Supplier Performance'
        },
        tooltip: {
            trigger: 'axis'
        },
        legend: {
            data: ['Purchases']
        },
        xAxis: {
            type: 'category',
            data: <?= json_encode(array_column($supplier_invoices, 'supplier')) ?>
        },
        yAxis: {
            type: 'value'
        },
        series: [{
            name: 'Purchases',
            type: 'bar',
            data: <?= json_encode(array_column($supplier_invoices, 'total_amount')) ?>,
            itemStyle: {
                color: '#007bff'
            }
        }]
    };
    supplierChart.setOption(supplierOption);

    // Monthly Expenditure Chart
    var expenditureChart = echarts.init(document.getElementById('monthlyExpenditureChart'));
    var expenditureOption = {
        title: {
            text: 'Monthly Expenditure'
        },
        tooltip: {
            trigger: 'axis'
        },
        legend: {
            data: ['Expenditure']
        },
        xAxis: {
            type: 'category',
            data: <?= json_encode(array_column($monthly_invoices, 'month')) ?>
        },
        yAxis: {
            type: 'value'
        },
        series: [{
            name: 'Expenditure',
            type: 'line',
            data: <?= json_encode(array_column($monthly_invoices, 'total_amount')) ?>,
            itemStyle: {
                color: '#28a745'
            }
        }]
    };
    expenditureChart.setOption(expenditureOption);

    // Top Suppliers by Expenditure Chart
    var topSuppliersChart = echarts.init(document.getElementById('topSuppliersChart'));
    var topSuppliersOption = {
        title: {
            text: 'Top Suppliers by Expenditure'
        },
        tooltip: {},
        xAxis: {
            type: 'category',
            data: <?= json_encode(array_column($top_suppliers, 'supplier')) ?>
        },
        yAxis: {
            type: 'value'
        },
        series: [{
            name: 'Expenditure',
            type: 'bar',
            data: <?= json_encode(array_column($top_suppliers, 'total_expenditure')) ?>
        }]
    };
    topSuppliersChart.setOption(topSuppliersOption);

    // Purchase Trends (Quarterly) Chart
    var purchaseTrendsChart = echarts.init(document.getElementById('purchaseTrendsChart'));
    var purchaseTrendsOption = {
        title: {
            text: 'Purchase Trends (Quarterly)'
        },
        tooltip: {},
        xAxis: {
            type: 'category',
            data: <?= json_encode(array_column($quarterly_trends, 'quarter')) ?>
        },
        yAxis: {
            type: 'value'
        },
        series: [{
            name: 'Purchases',
            type: 'line',
            data: <?= json_encode(array_column($quarterly_trends, 'total_amount')) ?>
        }]
    };
    purchaseTrendsChart.setOption(purchaseTrendsOption);
</script>