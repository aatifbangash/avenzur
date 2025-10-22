<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-plus"></i><?= lang('Procurement Dashboard'); ?></h2>

        
    </div>
    
</div>

<!-- Quick Lookup Inputs -->
<div class="box mt-3">
    <div class="box-content">
        <div class="row">
            <div class="col-md-4">
                <div class="form-group">
                    <label for="prNumber">PR Number</label>
                    <div class="input-group">
                        <input id="prNumber" class="form-control" placeholder="Enter PR number">
                        <span class="input-group-btn">
                            <button class="btn btn-primary" type="button" onclick="viewByNumber('pr')">View</button>
                        </span>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label for="poNumber">PO Number</label>
                    <div class="input-group">
                        <input id="poNumber" class="form-control" placeholder="Enter PO number">
                        <span class="input-group-btn">
                            <button class="btn btn-primary" type="button" onclick="viewByNumber('po')">View</button>
                        </span>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label for="piNumber">PI Number</label>
                    <div class="input-group">
                        <input id="piNumber" class="form-control" placeholder="Enter PI number">
                        <span class="input-group-btn">
                            <button class="btn btn-primary" type="button" onclick="viewByNumber('pi')">View</button>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Charts Grid (3x2) with vertical metrics box -->
<div class="box mt-4">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-bar-chart"></i> <?= lang('Procurement Charts') ?></h2>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-md-3">
                <div style="olor:#fff;padding:15px;border-radius:6px;">
                    <h5 style="color:#fff;font-weight:700;margin-top:0;">Totals</h5>
                    <div style="background-color:#145214;color:#fff; padding:8px 0;border-top:1px solid rgba(255,255,255,0.08);">
                        <strong>Total Suppliers:</strong>
                        <div style="font-size:18px;font-weight:700;"><?= isset($total_suppliers) ? (int)$total_suppliers : 0 ?></div>
                    </div>
                    <div style="background:#0b3d91;color:#fff;padding:8px 0;border-top:1px solid rgba(255,255,255,0.08);">
                        <strong>Total PR:</strong>
                        <div style="font-size:18px;font-weight:700;"><?= isset($total_pr) ? (int)$total_pr : 0 ?></div>
                    </div>
                    <div style="background:#c69500;color:#fff;padding:8px 0;border-top:1px solid rgba(255,255,255,0.08);">
                        <strong>Total PO:</strong>
                        <div style="font-size:18px;font-weight:700;"><?= isset($total_po) ? (int)$total_po : 0 ?></div>
                    </div>
                    <div style="background:#d35400;color:#fff;padding:8px 0;border-top:1px solid rgba(255,255,255,0.08);">
                        <strong>Total PI:</strong>
                        <div style="font-size:18px;font-weight:700;"><?= isset($total_pi) ? (int)$total_pi : 0 ?></div>
                    </div>
                </div>
            </div>
            <div class="col-md-9">
                <div class="row">
                    <div class="col-md-6">
                        <div class="panel panel-default">
                            <div class="panel-heading" style="background:transparent;border-bottom:0;padding-bottom:0;"><strong>1. Invoice & Amount (Supplier-wise)</strong></div>
                            <div class="panel-body" style="padding-top:5px;">
                                <div id="chart1" style="min-height:220px;"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="panel panel-default">
                            <div class="panel-heading" style="background:transparent;border-bottom:0;padding-bottom:0;"><strong>2. Invoices (Monthly)</strong></div>
                            <div class="panel-body" style="padding-top:5px;">
                                <div id="chart2" style="min-height:220px;"></div>
                            </div>
                        </div>
                    </div>
                    <!-- <div class="col-md-4">
                        <div class="panel panel-default">
                            <div class="panel-heading" style="background:transparent;border-bottom:0;padding-bottom:0;"><strong>3. VAT (Monthly)</strong></div>
                            <div class="panel-body" style="padding-top:5px;">
                                <div id="chart3" style="min-height:220px;"></div>
                            </div>
                        </div>
                    </div> -->
                </div>

            </div>
        </div>


                <div class="row" style="margin-top:10px;">
                    <div class="col-md-4">
                        <div class="panel panel-default">
                            <div class="panel-heading" style="background:transparent;border-bottom:0;padding-bottom:0;"><strong>4. Other Dashboard</strong></div>
                            <div class="panel-body" style="padding-top:5px;">
                                <div id="chart4" style="min-height:220px;"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="panel panel-default">
                            <div class="panel-heading" style="background:transparent;border-bottom:0;padding-bottom:0;"><strong>5. Top Suppliers (Amount)</strong></div>
                            <div class="panel-body" style="padding-top:5px;">
                                <div id="chart5" style="min-height:220px;"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="panel panel-default">
                            <div class="panel-heading" style="background:transparent;border-bottom:0;padding-bottom:0;"><strong>6. Additional Metric</strong></div>
                            <div class="panel-body" style="padding-top:5px;">
                                <div id="chart6" style="min-height:220px;"></div>
                            </div>
                        </div>
                    </div>
                </div>
    </div>
</div>

<script>
    function viewByNumber(type) {
        var base = '<?= admin_url('') ?>';
        if (type === 'pr') {
            var v = document.getElementById('prNumber').value.trim();
            if (!v) return alert('Enter PR number');
            window.location.href = base + 'purchase_requisition/view/' + encodeURIComponent(v);
        } else if (type === 'po') {
            var v = document.getElementById('poNumber').value.trim();
            if (!v) return alert('Enter PO number');
            window.location.href = base + 'purchases/view/' + encodeURIComponent(v);
        } else if (type === 'pi') {
            var v = document.getElementById('piNumber').value.trim();
            if (!v) return alert('Enter PI number');
            window.location.href = base + 'purchases/view/' + encodeURIComponent(v);
        }
    }
</script>

<!-- ApexCharts CDN -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/apexcharts@3.36.0/dist/apexcharts.css">
<script src="https://cdn.jsdelivr.net/npm/apexcharts@3.36.0"></script>
<script>
    (function(){
        /*
         Expected PHP data structures (controller should pass):
         - $supplier_invoice_stats = [ ['name'=>'Supplier A','invoices'=>12,'amount'=>1500], ... ]
         - $monthly_invoices = [ ['period'=>'2025-01','invoices'=>120], ... ] // period labels
         - $monthly_vat = [ ['period'=>'2025-01','vat'=>250], ... ]
         - $other_dashboard = [ ['label'=>'Metric A','value'=>40], ... ] // for donut
        */

        var supplierInvoiceStats = <?= isset($supplier_invoice_stats) ? json_encode($supplier_invoice_stats) : 'null' ?>;
        var monthlyInvoices = <?= isset($monthly_invoices) ? json_encode($monthly_invoices) : 'null' ?>;
        var monthlyVat = <?= isset($monthly_vat) ? json_encode($monthly_vat) : 'null' ?>;
        var otherDashboard = <?= isset($other_dashboard) ? json_encode($other_dashboard) : 'null' ?>;

        // Fallback sample data
        if (!supplierInvoiceStats) {
            supplierInvoiceStats = [
                {name: 'Supplier A', invoices: 12, amount: 1500},
                {name: 'Supplier B', invoices: 9, amount: 980},
                {name: 'Supplier C', invoices: 6, amount: 700},
                {name: 'Supplier D', invoices: 4, amount: 420}
            ];
        }
        if (!monthlyInvoices) {
            monthlyInvoices = [
                {period: 'Jan', invoices: 120}, {period: 'Feb', invoices: 95}, {period: 'Mar', invoices: 110}, {period: 'Apr', invoices: 130}
            ];
        }
        if (!monthlyVat) {
            monthlyVat = [
                {period: 'Jan', vat: 250}, {period: 'Feb', vat: 200}, {period: 'Mar', vat: 230}, {period: 'Apr', vat: 270}
            ];
        }
        if (!otherDashboard) {
            otherDashboard = [ {label: 'Pending', value: 12}, {label: 'Approved', value: 30}, {label: 'Rejected', value: 5} ];
        }

        // Chart 1: supplier invoices (bar) and amount (line) - combo chart
        (function(){
            var categories = supplierInvoiceStats.map(function(s){ return s.name; });
            var invoicesSeries = supplierInvoiceStats.map(function(s){ return s.invoices; });
            var amountSeries = supplierInvoiceStats.map(function(s){ return s.amount; });

            var options = {
                series: [{ name: 'Invoices', type: 'column', data: invoicesSeries }, { name: 'Amount', type: 'line', data: amountSeries }],
                chart: { height: 320, type: 'line' },
                stroke: { width: [0, 3] },
                xaxis: { categories: categories },
                yaxis: [{ title: { text: 'Invoices' } }, { opposite: true, title: { text: 'Amount' } }],
                colors: ['#0d6efd', '#198754'],
                dataLabels: { enabled: false }
            };
            new ApexCharts(document.querySelector('#chart1'), options).render();
        })();

        // Chart 2: monthly invoices (bar)
        (function(){
            var cats = monthlyInvoices.map(function(m){ return m.period; });
            var data = monthlyInvoices.map(function(m){ return m.invoices; });
            var options = {
                chart: { type: 'bar', height: 320 },
                series: [{ name: 'Invoices', data: data }],
                xaxis: { categories: cats },
                plotOptions: { bar: { borderRadius: 6 } },
                colors: ['#0d6efd']
            };
            new ApexCharts(document.querySelector('#chart2'), options).render();
        })();

        // Chart 3: monthly VAT (area)
        (function(){
            var cats = monthlyVat.map(function(m){ return m.period; });
            var data = monthlyVat.map(function(m){ return m.vat; });
            var options = {
                chart: { type: 'area', height: 320 },
                series: [{ name: 'VAT', data: data }],
                xaxis: { categories: cats },
                colors: ['#ffc107'],
                dataLabels: { enabled: false }
            };
            new ApexCharts(document.querySelector('#chart3'), options).render();
        })();

        // Chart 4: other dashboard (donut)
        (function(){
            var labels = otherDashboard.map(function(o){ return o.label; });
            var values = otherDashboard.map(function(o){ return o.value; });
            var options = {
                chart: { type: 'donut', height: 220 },
                series: values,
                labels: labels,
                colors: ['#0d6efd','#198754','#ffc107','#dc3545']
            };
            new ApexCharts(document.querySelector('#chart4'), options).render();
        })();

        // Chart 5: Top suppliers by amount (bar)
        (function(){
            var topSup = <?= isset($top_suppliers_amount) ? json_encode($top_suppliers_amount) : 'null' ?>;
            if (!topSup) {
                topSup = [ {name:'Supplier A', amount:1500}, {name:'Supplier B', amount:980}, {name:'Supplier C', amount:700} ];
            }
            var cats = topSup.map(function(s){ return s.name; });
            var data = topSup.map(function(s){ return s.amount; });
            var options = {
                chart: { type: 'bar', height: 220 },
                series: [{ name: 'Amount', data: data }],
                xaxis: { categories: cats },
                colors: ['#20c997']
            };
            new ApexCharts(document.querySelector('#chart5'), options).render();
        })();

        // Chart 6: Additional metric (radialBar)
        (function(){
            var metric = <?= isset($chart6_data) ? json_encode($chart6_data) : 'null' ?>;
            if (!metric) { metric = {label:'Completion', value:65}; }
            var options = {
                chart: { type: 'radialBar', height: 220 },
                series: [metric.value],
                labels: [metric.label],
                colors: ['#6610f2']
            };
            new ApexCharts(document.querySelector('#chart6'), options).render();
        })();

    })();
</script>