<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<style>
    /* Modern Dashboard Styles with New Color Scheme */

    /* Container adjustments */
    .container-fluid {
        max-width: 100%;
        padding-left: 15px;
        padding-right: 15px;
    }

    .row {
        margin-left: -15px;
        margin-right: -15px;
    }

    .dashboard-header {
        background: linear-gradient(135deg, #1e88e5 0%, #1565c0 100%);
        padding: 30px;
        border-radius: 12px;
        margin-bottom: 25px;
        box-shadow: 0 10px 30px rgba(30, 136, 229, 0.25);
    }

    .dashboard-header h2 {
        color: #fff;
        margin: 0;
        font-size: 28px;
        font-weight: 700;
        text-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }

    .dashboard-header p {
        color: rgba(255,255,255,0.9);
        margin: 8px 0 0 0;
        font-size: 14px;
    }

    /* Quick Lookup Section */
    .quick-lookup-box {
        background: #fff;
        border-radius: 12px;
        padding: 25px;
        box-shadow: 0 2px 12px rgba(0,0,0,0.08);
        margin-bottom: 25px;
    }

    .quick-lookup-box h3 {
        font-size: 18px;
        font-weight: 600;
        color: #2d3748;
        margin: 0 0 20px 0;
        display: flex;
        align-items: center;
    }

    .quick-lookup-box h3 i {
        margin-right: 10px;
        color: #1e88e5;
    }

    .lookup-input-group {
        position: relative;
    }

    .lookup-input-group label {
        font-size: 13px;
        font-weight: 600;
        color: #4a5568;
        margin-bottom: 8px;
        display: block;
    }

    .lookup-input-group input {
        border: 2px solid #e2e8f0;
        border-radius: 8px;
        padding: 12px 15px;
        font-size: 14px;
        transition: all 0.3s ease;
    }

    .lookup-input-group input:focus {
        border-color: #1e88e5;
        box-shadow: 0 0 0 3px rgba(30, 136, 229, 0.1);
        outline: none;
    }

    .lookup-input-group .btn {
        border-radius: 8px;
        padding: 12px 20px;
        font-weight: 600;
        font-size: 14px;
        transition: all 0.3s ease;
    }

    .lookup-input-group .btn-primary {
        background: linear-gradient(135deg, #1e88e5 0%, #1565c0 100%);
        border: none;
    }

    .lookup-input-group .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(30, 136, 229, 0.3);
    }

    /* Metrics Sidebar - Left Column */
    .metrics-sidebar {
        background: transparent;
        padding: 0;
        height: 100%;
    }

    .metrics-sidebar-title {
        font-size: 16px;
        font-weight: 700;
        color: #2d3748;
        margin-bottom: 20px;
        padding-bottom: 12px;
        border-bottom: 2px solid #e2e8f0;
        display: flex;
        align-items: center;
    }

    .metrics-sidebar-title i {
        margin-right: 10px;
        color: #1e88e5;
    }

    /* Metrics Cards - Vertical Layout */
    .metric-card {
        padding: 18px;
        border-radius: 10px;
        margin-bottom: 15px;
        transition: all 0.3s ease;
        display: flex;
        justify-content: space-between;
        align-items: center;
        cursor: pointer;
        height: auto;
    }

    .metric-card:hover {
        transform: translateX(5px);
        box-shadow: 0 6px 16px rgba(0,0,0,0.12);
    }

    .metric-card.suppliers {
        background: linear-gradient(135deg, #66bb6a 0%, #43a047 100%);
    }

    .metric-card.pr {
        background: linear-gradient(135deg, #42a5f5 0%, #1e88e5 100%);
    }

    .metric-card.po {
        background: linear-gradient(135deg, #ffa726 0%, #fb8c00 100%);
    }

    .metric-card.pi {
        background: linear-gradient(135deg, #ef5350 0%, #e53935 100%);
    }

    .metric-info {
        flex: 1;
    }

    .metric-label {
        font-size: 12px;
        font-weight: 600;
        color: rgba(255,255,255,0.95);
        text-transform: uppercase;
        letter-spacing: 0.8px;
        margin-bottom: 6px;
    }

    .metric-value {
        font-size: 32px;
        font-weight: 800;
        color: #fff;
        line-height: 1;
    }

    .metric-icon {
        font-size: 40px;
        opacity: 0.25;
        color: #fff;
    }

    /* Chart Section */
    .charts-box {
        background: #fff;
        border-radius: 12px;
        padding: 25px;
        box-shadow: 0 2px 12px rgba(0,0,0,0.08);
        margin-bottom: 25px;
    }

    .charts-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 25px;
        padding-bottom: 15px;
        border-bottom: 2px solid #f7fafc;
    }

    .charts-header h3 {
        font-size: 20px;
        font-weight: 700;
        color: #2d3748;
        margin: 0;
        display: flex;
        align-items: center;
    }

    .charts-header h3 i {
        margin-right: 10px;
        color: #1e88e5;
    }

    .chart-panel {
        background: #f8f9fa;
        border-radius: 10px;
        padding: 20px;
        margin-bottom: 30px;
        border: 1px solid #e2e8f0;
        transition: all 0.3s ease;
        position: relative;
    }

    .chart-panel:hover {
        box-shadow: 0 5px 20px rgba(0,0,0,0.08);
        border-color: #1e88e5;
    }

    .chart-title {
        font-size: 14px;
        font-weight: 700;
        color: #2d3748;
        margin-bottom: 15px;
        display: flex;
        align-items: center;
    }

    .chart-title .badge {
        background: linear-gradient(135deg, #1e88e5 0%, #1565c0 100%);
        color: #fff;
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 11px;
        margin-right: 10px;
        font-weight: 600;
    }

    /* Custom Chart Toolbar - Positioned at Bottom Center */
    .custom-chart-toolbar {
        display: flex;
        gap: 8px;
        justify-content: center;
        margin-top: 15px;
        padding: 10px;
        background: rgba(255,255,255,0.95);
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }

    .custom-chart-toolbar button {
        width: 36px;
        height: 36px;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        transition: all 0.2s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 14px;
        font-weight: 600;
        color: #fff;
    }

    .custom-chart-toolbar button:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }

    .custom-chart-toolbar .btn-zoom-in {
        background: linear-gradient(135deg, #66bb6a 0%, #43a047 100%);
    }

    .custom-chart-toolbar .btn-zoom-out {
        background: linear-gradient(135deg, #ffa726 0%, #fb8c00 100%);
    }

    .custom-chart-toolbar .btn-reset {
        background: linear-gradient(135deg, #42a5f5 0%, #1e88e5 100%);
    }

    .custom-chart-toolbar .btn-download {
        background: linear-gradient(135deg, #ab47bc 0%, #8e24aa 100%);
    }

    .custom-chart-toolbar button i {
        font-size: 16px;
    }

    /* Tooltip for buttons */
    .custom-chart-toolbar button {
        position: relative;
    }

    .custom-chart-toolbar button::after {
        content: attr(data-tooltip);
        position: absolute;
        top: -35px;
        left: 50%;
        transform: translateX(-50%);
        background: #2d3748;
        color: #fff;
        padding: 6px 10px;
        border-radius: 6px;
        font-size: 11px;
        white-space: nowrap;
        opacity: 0;
        pointer-events: none;
        transition: opacity 0.2s ease;
    }

    .custom-chart-toolbar button:hover::after {
        opacity: 1;
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
        .dashboard-header {
            padding: 20px;
        }

        .dashboard-header h2 {
            font-size: 22px;
        }

        .metric-value {
            font-size: 28px;
        }

        .metric-icon {
            font-size: 36px;
        }

        .metric-card {
            margin-bottom: 15px;
        }

        .charts-box {
            padding: 15px;
        }

        .custom-chart-toolbar {
            gap: 6px;
            padding: 8px;
        }

        .custom-chart-toolbar button {
            width: 32px;
            height: 32px;
            font-size: 12px;
        }
    }

    /* Animation */
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .box, .quick-lookup-box, .charts-box, .metrics-container {
        animation: fadeInUp 0.5s ease-out;
    }

    /* Hide default ApexCharts toolbar */
    .apexcharts-toolbar {
        display: none !important;
    }
</style>

<!-- Dashboard Header -->
<div class="dashboard-header">
    <h2><i class="fa fa-dashboard"></i> <?= lang('Procurement Dashboard'); ?></h2>
    <p>Monitor and manage your procurement operations in real-time</p>
</div>

<!-- Quick Lookup Section -->
<div class="quick-lookup-box">
    <h3><i class="fa fa-search"></i> Quick Lookup</h3>
    <div class="row">
        <div class="col-md-4">
            <div class="lookup-input-group">
                <label for="prNumber"><i class="fa fa-file-text"></i> PR Number</label>
                <div class="input-group">
                    <input id="prNumber" class="form-control" placeholder="e.g., PR-2025-001">
                    <span class="input-group-btn">
                        <button class="btn btn-primary" type="button" onclick="viewByNumber('pr')">
                            <i class="fa fa-arrow-right"></i> View
                        </button>
                    </span>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="lookup-input-group">
                <label for="poNumber"><i class="fa fa-shopping-cart"></i> PO Number</label>
                <div class="input-group">
                    <input id="poNumber" class="form-control" placeholder="e.g., PO-2025-001">
                    <span class="input-group-btn">
                        <button class="btn btn-primary" type="button" onclick="viewByNumber('po')">
                            <i class="fa fa-arrow-right"></i> View
                        </button>
                    </span>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="lookup-input-group">
                <label for="piNumber"><i class="fa fa-file-invoice"></i> PI Number</label>
                <div class="input-group">
                    <input id="piNumber" class="form-control" placeholder="e.g., PI-2025-001">
                    <span class="input-group-btn">
                        <button class="btn btn-primary" type="button" onclick="viewByNumber('pi')">
                            <i class="fa fa-arrow-right"></i> View
                        </button>
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Metrics and Charts Section -->
<div class="row">
    <!-- Full Width: Charts with Metrics Inside -->
    <div class="col-md-12">
        <div class="charts-box">
            <div class="charts-header">
                <h3><i class="fa fa-chart-bar"></i> Analytics Overview</h3>
            </div>

            <div class="row">
                <!-- Left Column: Key Metrics (Vertical) -->
                <div class="col-md-3">
                    <div class="metrics-sidebar">
                        <h4 class="metrics-sidebar-title">
                            <i class="fa fa-chart-line"></i> Key Metrics
                        </h4>

                        <div class="metric-card suppliers">
                            <div class="metric-info">
                                <div class="metric-label">Total Suppliers</div>
                                <div class="metric-value"><?= isset($total_suppliers) ? number_format((int)$total_suppliers) : '0' ?></div>
                            </div>
                            <div class="metric-icon">
                                <i class="fa fa-users"></i>
                            </div>
                        </div>

                        <div class="metric-card pr">
                            <div class="metric-info">
                                <div class="metric-label">Total PR</div>
                                <div class="metric-value"><?= isset($total_pr) ? number_format((int)$total_pr) : '0' ?></div>
                            </div>
                            <div class="metric-icon">
                                <i class="fa fa-file-text"></i>
                            </div>
                        </div>

                        <div class="metric-card po">
                            <div class="metric-info">
                                <div class="metric-label">Total PO</div>
                                <div class="metric-value"><?= isset($total_po) ? number_format((int)$total_po) : '0' ?></div>
                            </div>
                            <div class="metric-icon">
                                <i class="fa fa-shopping-cart"></i>
                            </div>
                        </div>

                        <div class="metric-card pi">
                            <div class="metric-info">
                                <div class="metric-label">Total PI</div>
                                <div class="metric-value"><?= isset($total_pi) ? number_format((int)$total_pi) : '0' ?></div>
                            </div>
                            <div class="metric-icon">
                                <i class="fa fa-file-invoice"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column: All Charts in 2-Column Grid -->
                <div class="col-md-9">
                    <!-- First Row: 2 Charts -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="chart-panel" id="panel-chart1">
                                <div class="chart-title">
                                    <span class="badge">1</span>
                                    Invoice & Amount
                                </div>
                                <div id="chart1" style="min-height:320px;"></div>
                                <!-- Custom Toolbar -->
                                <div class="custom-chart-toolbar">
                                    <button class="btn-zoom-in" data-tooltip="Zoom In" onclick="chartAction('chart1', 'zoomIn')">
                                        <i class="fa fa-search-plus"></i>
                                    </button>
                                    <button class="btn-zoom-out" data-tooltip="Zoom Out" onclick="chartAction('chart1', 'zoomOut')">
                                        <i class="fa fa-search-minus"></i>
                                    </button>
                                    <button class="btn-reset" data-tooltip="Reset Zoom" onclick="chartAction('chart1', 'reset')">
                                        <i class="fa fa-undo"></i>
                                    </button>
                                    <button class="btn-download" data-tooltip="Download" onclick="chartAction('chart1', 'download')">
                                        <i class="fa fa-download"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="chart-panel" id="panel-chart2">
                                <div class="chart-title">
                                    <span class="badge">2</span>
                                    Monthly Invoices
                                </div>
                                <div id="chart2" style="min-height:320px;"></div>
                                <!-- Custom Toolbar -->
                                <div class="custom-chart-toolbar">
                                    <button class="btn-download" data-tooltip="Download" onclick="echartAction('chart2', 'download')">
                                        <i class="fa fa-download"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Second Row: 2 Charts -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="chart-panel" id="panel-chart4">
                                <div class="chart-title">
                                    <span class="badge">3</span>
                                    Status Distribution
                                </div>
                                <div id="chart4" style="min-height:320px;"></div>
                                <!-- Custom Toolbar -->
                                <div class="custom-chart-toolbar">
                                    <button class="btn-download" data-tooltip="Download" onclick="echartAction('chart4', 'download')">
                                        <i class="fa fa-download"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="chart-panel" id="panel-chart5">
                                <div class="chart-title">
                                    <span class="badge">4</span>
                                    Top Suppliers
                                </div>
                                <div id="chart5" style="min-height:320px;"></div>
                                <!-- Custom Toolbar -->
                                <div class="custom-chart-toolbar">
                                    <button class="btn-zoom-in" data-tooltip="Zoom In" onclick="chartAction('chart5', 'zoomIn')">
                                        <i class="fa fa-search-plus"></i>
                                    </button>
                                    <button class="btn-zoom-out" data-tooltip="Zoom Out" onclick="chartAction('chart5', 'zoomOut')">
                                        <i class="fa fa-search-minus"></i>
                                    </button>
                                    <button class="btn-reset" data-tooltip="Reset Zoom" onclick="chartAction('chart5', 'reset')">
                                        <i class="fa fa-undo"></i>
                                    </button>
                                    <button class="btn-download" data-tooltip="Download" onclick="chartAction('chart5', 'download')">
                                        <i class="fa fa-download"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Third Row: 1 Chart (Completion Rate) -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="chart-panel" id="panel-chart6">
                                <div class="chart-title">
                                    <span class="badge">5</span>
                                    Completion Rate
                                </div>
                                <div id="chart6" style="min-height:320px;"></div>
                                <!-- Custom Toolbar -->
                                <div class="custom-chart-toolbar">
                                    <button class="btn-download" data-tooltip="Download" onclick="chartAction('chart6', 'download')">
                                        <i class="fa fa-download"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function viewByNumber(type) {
        var base = '<?= admin_url('') ?>';
        var inputId = type + 'Number';
        var value = document.getElementById(inputId).value.trim();

        if (!value) {
            // Add visual feedback
            var input = document.getElementById(inputId);
            input.style.borderColor = '#dc3545';
            setTimeout(function() {
                input.style.borderColor = '';
            }, 2000);
            return alert('Please enter ' + type.toUpperCase() + ' number');
        }

        var url = '';
        if (type === 'pr') {
            url = base + 'purchase_requisition/view/' + encodeURIComponent(value);
        } else if (type === 'po' || type === 'pi') {
            url = base + 'purchases/view/' + encodeURIComponent(value);
        }

        window.location.href = url;
    }

    // Allow Enter key to submit
    ['prNumber', 'poNumber', 'piNumber'].forEach(function(id) {
        document.getElementById(id).addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                var type = id.replace('Number', '');
                viewByNumber(type);
            }
        });
    });
</script>

<!-- Apache ECharts CDN -->
<script src="https://cdn.jsdelivr.net/npm/echarts@5.4.3/dist/echarts.min.js"></script>

<!-- ApexCharts CDN -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/apexcharts@3.36.0/dist/apexcharts.css">
<script src="https://cdn.jsdelivr.net/npm/apexcharts@3.36.0"></script>
<script>
    (function(){
        // Store chart instances globally
        window.chartInstances = {};

        // Get data from PHP
        var supplierInvoiceStats = <?= isset($supplier_invoice_stats) ? json_encode($supplier_invoice_stats) : 'null' ?>;
        var monthlyInvoices = <?= isset($monthly_invoices) ? json_encode($monthly_invoices) : 'null' ?>;
        var monthlyVat = <?= isset($monthly_vat) ? json_encode($monthly_vat) : 'null' ?>;
        var otherDashboard = <?= isset($other_dashboard) ? json_encode($other_dashboard) : 'null' ?>;
        var topSuppliersAmount = <?= isset($top_suppliers_amount) ? json_encode($top_suppliers_amount) : 'null' ?>;
        var chart6Data = <?= isset($chart6_data) ? json_encode($chart6_data) : 'null' ?>;

        // Fallback sample data
        if (!supplierInvoiceStats) {
            supplierInvoiceStats = [
                {name: 'ABC Corporation', invoices: 18, amount: 2500},
                {name: 'XYZ Industries', invoices: 15, amount: 1980},
                {name: 'Global Supplies', invoices: 12, amount: 1450},
                {name: 'Tech Solutions', invoices: 8, amount: 890},
                {name: 'Metro Trading', invoices: 6, amount: 620}
            ];
        }

        if (!monthlyInvoices) {
            monthlyInvoices = [
                {period: 'Jan', invoices: 145},
                {period: 'Feb', invoices: 128},
                {period: 'Mar', invoices: 167},
                {period: 'Apr', invoices: 152},
                {period: 'May', invoices: 189},
                {period: 'Jun', invoices: 176}
            ];
        }

        if (!monthlyVat) {
            monthlyVat = [
                {period: 'Jan', vat: 290},
                {period: 'Feb', vat: 256},
                {period: 'Mar', vat: 334},
                {period: 'Apr', vat: 304},
                {period: 'May', vat: 378},
                {period: 'Jun', vat: 352}
            ];
        }

        if (!otherDashboard) {
            otherDashboard = [
                {label: 'Approved', value: 45},
                {label: 'Pending', value: 28},
                {label: 'In Progress', value: 18},
                {label: 'Rejected', value: 9}
            ];
        }

        if (!topSuppliersAmount) {
            topSuppliersAmount = [
                {name: 'ABC Corp', amount: 2500},
                {name: 'XYZ Industries', amount: 1980},
                {name: 'Global Supplies', amount: 1450},
                {name: 'Tech Solutions', amount: 890}
            ];
        }

        if (!chart6Data) {
            chart6Data = {label: 'Completion Rate', value: 78};
        }

        // Common chart options
        var commonOptions = {
            chart: {
                fontFamily: 'inherit',
                toolbar: {
                    show: false  // Hide default toolbar
                },
                zoom: {
                    enabled: true
                }
            },
            grid: {
                borderColor: '#e2e8f0',
                strokeDashArray: 4
            },
            tooltip: {
                theme: 'light',
                style: {
                    fontSize: '13px'
                }
            }
        };

        // Chart 1: Supplier Invoices & Amount (Combo Chart)
        (function(){
            var categories = supplierInvoiceStats.map(function(s){ return s.name; });
            var invoicesSeries = supplierInvoiceStats.map(function(s){ return s.invoices; });
            var amountSeries = supplierInvoiceStats.map(function(s){ return s.amount; });

            var options = Object.assign({}, commonOptions, {
                series: [
                    { name: 'Invoices', type: 'column', data: invoicesSeries },
                    { name: 'Amount', type: 'line', data: amountSeries }
                ],
                chart: Object.assign({}, commonOptions.chart, {
                    height: 320,
                    type: 'line'
                }),
                stroke: {
                    width: [0, 4],
                    curve: 'smooth'
                },
                plotOptions: {
                    bar: {
                        borderRadius: 8,
                        columnWidth: '55%'
                    }
                },
                xaxis: {
                    categories: categories,
                    labels: {
                        rotate: -45,
                        rotateAlways: true,
                        style: {
                            fontSize: '11px',
                            fontWeight: 500
                        },
                        offsetY: 5,
                        trim: false,
                        hideOverlappingLabels: false
                    },
                    tickPlacement: 'on'
                },
                yaxis: [
                    {
                        title: { text: 'Invoices', style: { fontSize: '12px', fontWeight: 600 } },
                        labels: {
                            style: { fontSize: '11px' },
                            formatter: function(val) {
                                return Math.round(val);
                            }
                        }
                    },
                    {
                        opposite: true,
                        title: { text: 'Amount', style: { fontSize: '12px', fontWeight: 600 } },
                        labels: {
                            style: { fontSize: '11px' },
                            formatter: function(val) {
                                return val.toFixed(0);
                            }
                        }
                    }
                ],
                colors: ['#1e88e5', '#66bb6a'],
                dataLabels: {
                    enabled: true,
                    enabledOnSeries: [0],
                    style: {
                        fontSize: '10px',
                        fontWeight: 600,
                        colors: ['#fff']
                    },
                    background: {
                        enabled: true,
                        foreColor: '#1e88e5',
                        borderRadius: 4,
                        padding: 3,
                        opacity: 0.9
                    }
                },
                legend: {
                    position: 'top',
                    horizontalAlign: 'left',
                    fontSize: '13px',
                    fontWeight: 600,
                    markers: {
                        width: 12,
                        height: 12,
                        radius: 3
                    }
                },
                grid: {
                    padding: {
                        bottom: 20,
                        left: 10,
                        right: 10
                    }
                },
                tooltip: {
                    shared: true,
                    intersect: false,
                    style: {
                        fontSize: '12px'
                    },
                    y: {
                        formatter: function(val, opts) {
                            if (opts.seriesIndex === 0) {
                                return val + ' invoices';
                            } else {
                                return '$' + val.toFixed(2);
                            }
                        }
                    }
                }
            });

            var chart = new ApexCharts(document.querySelector('#chart1'), options);
            chart.render();
            window.chartInstances['chart1'] = chart;
        })();

        // Chart 2: Monthly Invoices (ECharts Dynamic Bar Chart)
        (function(){
            var chartDom = document.getElementById('chart2');
            var myChart = echarts.init(chartDom);

            var cats = monthlyInvoices.map(function(m){ return m.period; });
            var data = monthlyInvoices.map(function(m){ return m.invoices; });

            var option = {
                xAxis: {
                    type: 'category',
                    data: cats,
                    axisLabel: {
                        fontSize: 12,
                        color: '#5a6c7d',
                        fontWeight: 500
                    },
                    axisLine: {
                        lineStyle: {
                            color: '#e2e8f0'
                        }
                    }
                },
                yAxis: {
                    type: 'value',
                    name: 'Invoice Count',
                    nameTextStyle: {
                        fontSize: 13,
                        fontWeight: 600,
                        color: '#2c3e50',
                        padding: [0, 0, 10, 0]
                    },
                    axisLabel: {
                        fontSize: 12,
                        color: '#5a6c7d'
                    },
                    splitLine: {
                        lineStyle: {
                            color: '#e2e8f0',
                            type: 'dashed'
                        }
                    }
                },
                series: [{
                    name: 'Invoices',
                    data: data,
                    type: 'bar',
                    barWidth: '60%',
                    itemStyle: {
                        color: '#42a5f5',
                        borderRadius: [8, 8, 0, 0]
                    },
                    label: {
                        show: true,
                        position: 'top',
                        fontSize: 12,
                        fontWeight: 700,
                        color: '#2d3748',
                        distance: 10
                    },
                    emphasis: {
                        itemStyle: {
                            color: '#1e88e5',
                            shadowBlur: 10,
                            shadowColor: 'rgba(30, 136, 229, 0.5)'
                        }
                    },
                    animationDelay: function (idx) {
                        return idx * 80;
                    }
                }],
                grid: {
                    left: '8%',
                    right: '5%',
                    bottom: '12%',
                    top: '18%',
                    containLabel: true
                },
                tooltip: {
                    trigger: 'axis',
                    axisPointer: {
                        type: 'shadow',
                        shadowStyle: {
                            color: 'rgba(30, 136, 229, 0.1)'
                        }
                    },
                    backgroundColor: 'rgba(255, 255, 255, 0.95)',
                    borderColor: '#1e88e5',
                    borderWidth: 1,
                    textStyle: {
                        color: '#2d3748',
                        fontSize: 13
                    },
                    formatter: function(params) {
                        return '<strong>' + params[0].name + '</strong><br/>' +
                            params[0].marker + ' Invoices: <strong>' + params[0].value + '</strong>';
                    }
                },
                animationEasing: 'elasticOut',
                animationDelayUpdate: function (idx) {
                    return idx * 10;
                }
            };

            myChart.setOption(option);
            window.chartInstances['chart2'] = myChart;

            // Auto-resize
            window.addEventListener('resize', function() {
                myChart.resize();
            });
        })();

        // Chart 4: Status Distribution (ECharts Nightingale Rose Chart)
        (function(){
            var chartDom = document.getElementById('chart4');
            var myChart = echarts.init(chartDom);

            var labels = otherDashboard.map(function(o){ return o.label; });
            var values = otherDashboard.map(function(o){ return o.value; });

            var data = labels.map(function(label, index) {
                return {
                    value: values[index],
                    name: label
                };
            });

            var option = {
                tooltip: {
                    trigger: 'item',
                    formatter: '{b}: {c} ({d}%)',
                    backgroundColor: 'rgba(255, 255, 255, 0.95)',
                    borderColor: '#1e88e5',
                    borderWidth: 2,
                    textStyle: {
                        color: '#2d3748',
                        fontSize: 13,
                        fontWeight: 600
                    },
                    padding: [8, 12]
                },
                legend: {
                    bottom: '5%',
                    left: 'center',
                    textStyle: {
                        fontSize: 12,
                        fontWeight: 600,
                        color: '#2d3748'
                    },
                    itemWidth: 14,
                    itemHeight: 14,
                    itemGap: 20
                },
                series: [{
                    name: 'Status',
                    type: 'pie',
                    radius: [40, 120],
                    center: ['50%', '45%'],
                    roseType: 'area',
                    itemStyle: {
                        borderRadius: 8,
                        borderColor: '#fff',
                        borderWidth: 2
                    },
                    label: {
                        fontSize: 12,
                        fontWeight: 700,
                        color: '#2d3748',
                        formatter: '{b}\n{d}%'
                    },
                    labelLine: {
                        length: 15,
                        length2: 8,
                        lineStyle: {
                            width: 2
                        }
                    },
                    emphasis: {
                        label: {
                            fontSize: 14,
                            fontWeight: 800
                        },
                        itemStyle: {
                            shadowBlur: 15,
                            shadowOffsetX: 0,
                            shadowColor: 'rgba(0, 0, 0, 0.4)'
                        }
                    },
                    data: data,
                    color: ['#66bb6a', '#ffa726', '#42a5f5', '#ef5350'],
                    animationType: 'scale',
                    animationEasing: 'elasticOut',
                    animationDelay: function (idx) {
                        return idx * 100;
                    }
                }]
            };

            myChart.setOption(option);
            window.chartInstances['chart4'] = myChart;

            // Auto-resize
            window.addEventListener('resize', function() {
                myChart.resize();
            });
        })();

        // Chart 5: Top Suppliers by Amount (Horizontal Bar)
        (function(){
            var cats = topSuppliersAmount.map(function(s){ return s.name; });
            var data = topSuppliersAmount.map(function(s){ return s.amount; });

            var options = Object.assign({}, commonOptions, {
                chart: Object.assign({}, commonOptions.chart, {
                    type: 'bar',
                    height: 320
                }),
                series: [{ name: 'Amount', data: data }],
                plotOptions: {
                    bar: {
                        borderRadius: 6,
                        horizontal: true,
                        barHeight: '65%'
                    }
                },
                xaxis: {
                    categories: cats,
                    labels: {
                        style: {
                            fontSize: '12px',
                            fontWeight: 500
                        },
                        formatter: function(val) {
                            return '$' + val;
                        }
                    }
                },
                yaxis: {
                    labels: {
                        style: {
                            fontSize: '12px',
                            fontWeight: 500
                        }
                    }
                },
                colors: ['#ffa726'],
                dataLabels: {
                    enabled: true,
                    style: {
                        fontSize: '12px',
                        fontWeight: 700,
                        colors: ['#fff']
                    },
                    formatter: function(val) {
                        return '$' + val;
                    }
                },
                tooltip: {
                    y: {
                        formatter: function(val) {
                            return '$' + val.toFixed(2);
                        }
                    }
                }
            });

            var chart = new ApexCharts(document.querySelector('#chart5'), options);
            chart.render();
            window.chartInstances['chart5'] = chart;
        })();

        // Chart 6: Completion Rate (Radial Bar)
        (function(){
            var options = {
                chart: {
                    type: 'radialBar',
                    height: 320,
                    toolbar: {
                        show: false
                    }
                },
                series: [chart6Data.value],
                labels: [chart6Data.label],
                colors: ['#ab47bc'],
                plotOptions: {
                    radialBar: {
                        hollow: {
                            size: '65%'
                        },
                        dataLabels: {
                            name: {
                                fontSize: '16px',
                                fontWeight: 600,
                                color: '#2d3748'
                            },
                            value: {
                                fontSize: '36px',
                                fontWeight: 700,
                                color: '#1a202c',
                                formatter: function(val) {
                                    return val + '%';
                                }
                            }
                        },
                        track: {
                            background: '#e2e8f0',
                            strokeWidth: '100%'
                        }
                    }
                }
            };

            var chart = new ApexCharts(document.querySelector('#chart6'), options);
            chart.render();
            window.chartInstances['chart6'] = chart;
        })();

    })();

    // Chart action handler for custom toolbar buttons
    function chartAction(chartId, action) {
        var chart = window.chartInstances[chartId];
        if (!chart) return;

        switch(action) {
            case 'zoomIn':
                chart.zoomX(
                    new Date(chart.w.globals.minX).getTime(),
                    new Date(chart.w.globals.maxX).getTime() * 0.8
                );
                break;
            case 'zoomOut':
                chart.zoomX(
                    new Date(chart.w.globals.minX).getTime() * 0.8,
                    new Date(chart.w.globals.maxX).getTime() * 1.2
                );
                break;
            case 'reset':
                chart.resetSeries();
                break;
            case 'download':
                chart.dataURI().then(function(uri) {
                    var link = document.createElement('a');
                    link.href = uri.imgURI;
                    link.download = chartId + '-chart.png';
                    link.click();
                });
                break;
        }
    }

    // ECharts action handler for download functionality
    function echartAction(chartId, action) {
        var chart = window.chartInstances[chartId];
        if (!chart) return;

        switch(action) {
            case 'download':
                var url = chart.getDataURL({
                    type: 'png',
                    pixelRatio: 2,
                    backgroundColor: '#fff'
                });
                var link = document.createElement('a');
                link.href = url;
                link.download = chartId + '-chart.png';
                link.click();
                break;
        }
    }
</script>