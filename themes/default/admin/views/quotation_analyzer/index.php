<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-search"></i>Quotation Analyzer</h2>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-md-12">
                <div class="form-group">
                    <label for="pr_select">Select Purchase Requisition:</label>
                    <select id="pr_select" class="form-control select2" style="width:100%;">
                        <option value="">Select PR</option>
                        <?php foreach ($purchase_requisitions as $pr): ?>
                            <option value="<?= $pr->id ?>"><?= $pr->pr_number ?> - <?= date('d/m/Y', strtotime($pr->created_at)) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>

        <div id="pr_details" style="display:none;">
            <hr>
            <div class="row">
                <div class="col-md-6">
                    <h4>PR Details</h4>
                    <table class="table table-bordered">
                        <tr>
                            <td width="150"><strong>PR Number:</strong></td>
                            <td id="pr_number"></td>
                        </tr>
                        <tr>
                            <td><strong>Created Date:</strong></td>
                            <td id="pr_date"></td>
                        </tr>
                        <tr>
                            <td><strong>Status:</strong></td>
                            <td id="pr_status"></td>
                        </tr>
                    </table>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <h4>Supplier Comparison Chart (Click on bars for details)</h4>
                    <div id="supplier_chart" style="width: 100%; height: 500px;"></div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <h4>Requested Items</h4>
                    <div class="table-responsive">
                        <table id="items_table" class="table table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th>Item</th>
                                    <th>Quantity</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Items will be added here dynamically -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <h4>Supplier Responses</h4>
                    <div class="table-responsive">
                        <table id="suppliers_table" class="table table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th>Supplier Name</th>
                                    <th>Remarks</th>
                                    <th>Items & Discounts</th>
                                    <th>Total Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Supplier responses will be added here dynamically -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Supplier Details Modal -->
<div class="modal fade" id="supplierModal" tabindex="-1" role="dialog" aria-labelledby="supplierModalLabel">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title" id="supplierModalLabel">Supplier Details</h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Supplier Name:</label>
                            <p id="modal_supplier_name" class="form-control-static"></p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Total Amount:</label>
                            <p id="modal_total_amount" class="form-control-static text-primary" style="font-size: 1.5em; font-weight: bold;"></p>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label>Remarks:</label>
                            <p id="modal_remarks" class="form-control-static"></p>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <h5><strong>Items & Pricing Details</strong></h5>
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped" id="modal_items_table">
                                <thead>
                                    <tr>
                                        <th>Item Name</th>
                                        <th>Quantity</th>
                                        <th>Unit Price</th>
                                        <th>Discount 1 (%)</th>
                                        <th>Discount 2 (%)</th>
                                        <th>Discount 3 (%)</th>
                                        <th>Deal (%)</th>
                                        <th>Item Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Items will be populated here -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/echarts@5.4.3/dist/echarts.min.js"></script>

<script type="text/javascript">
$(document).ready(function() {
    // Initialize Select2
    $('#pr_select').select2();
    
    var myChart = null;
    var supplierFullData = {}; // Store complete supplier data

    // Handle PR selection
    $('#pr_select').on('change', function() {
        var pr_id = $(this).val();
        if (!pr_id) {
            $('#pr_details').hide();
            return;
        }

        $.ajax({
            url: site.base_url + 'quotation_analyzer/get_pr_details/' + pr_id,
            type: 'GET',
            dataType: 'json',
            success: function(data) {
                displayPRDetails(data);
            },
            error: function(xhr, status, error) {
                bootbox.alert('Error loading PR details: ' + error);
            }
        });
    });

    function calculateTotalAmountSimple(unitCost, quantity, dis1, dis2, dis3, dealPercent) {
        const taxRate = 0; // Adjust if needed
        unitCost = parseFloat(unitCost) || 0;
        quantity = parseFloat(quantity) || 0;
        
        // Apply cascading discounts
        let price = unitCost;
        price = price * (1 - (parseFloat(dis1) || 0) / 100);
        price = price * (1 - (parseFloat(dis2) || 0) / 100);
        price = price * (1 - (parseFloat(dis3) || 0) / 100);
        price = price * (1 - (parseFloat(dealPercent) || 0) / 100);
        
        // Calculate subtotal
        const subtotal = price * quantity;
        
        // Apply tax
        const totalAmount = subtotal * (1 + (parseFloat(taxRate) || 0) / 100);
        
        return parseFloat(totalAmount.toFixed(2));
    }

    function displayPRDetails(data) {
        console.log(data);
        
        // Display PR info
        $('#pr_number').text(data.pr.pr_number);
        $('#pr_date').text(moment(data.pr.created_at).format('DD/MM/YYYY'));
        $('#pr_status').text(data.pr.status);
        $('#pr_details').show();

        // Build Items Table
        var $itemsTbody = $('#items_table tbody').empty();
        data.items.forEach(function(item) {
            var $row = $('<tr>');
            $row.append(`<td>${item.product_name}</td>`);
            $row.append(`<td>${parseFloat(item.quantity).toFixed(2)}</td>`);
            $itemsTbody.append($row);
        });

        // Prepare data for chart and table
        var supplierNames = [];
        var itemBreakdown = {}; // Store item costs per supplier
        var productNames = [];
        supplierFullData = {}; // Reset

        // Build Suppliers Table
        var $suppliersTbody = $('#suppliers_table tbody').empty();
        data.supplier_responses.forEach(function(response, index) {
            var $row = $('<tr>');
            $row.attr('id', 'supplier_row_' + index);
            
            // Supplier Name
            $row.append(`<td>${response.supplier_name}</td>`);
            
            // Remarks
            $row.append(`<td>${response.remarks || '-'}</td>`);
            
            // Items & Discounts (inner table)
            var itemsHtml = '<table class="table table-sm table-bordered mb-0">';
            itemsHtml += '<thead><tr><th>Item</th><th>Unit Price</th><th>Dis1 (%)</th><th>Dis2 (%)</th><th>Dis3 (%)</th><th>Deal (%)</th></tr></thead>';
            itemsHtml += '<tbody>';
            
            var totalAmount = 0;
            var itemsWithTotals = [];
            
            if (!itemBreakdown[response.supplier_name]) {
                itemBreakdown[response.supplier_name] = {};
            }
            
            response.items.forEach(function(item) {
                itemsHtml += '<tr>';
                itemsHtml += `<td>${item.product_name}</td>`;
                itemsHtml += `<td>${parseFloat(item.unit_price).toFixed(2)}</td>`;
                itemsHtml += `<td>${parseFloat(item.dis1).toFixed(2)}</td>`;
                itemsHtml += `<td>${parseFloat(item.dis2).toFixed(2)}</td>`;
                itemsHtml += `<td>${parseFloat(item.dis3).toFixed(2)}</td>`;
                itemsHtml += `<td>${parseFloat(item.deal).toFixed(2)}</td>`;
                itemsHtml += '</tr>';
                
                // Calculate total for this item
                var itemTotal = calculateTotalAmountSimple(
                    item.unit_price,
                    item.quantity,
                    item.dis1,
                    item.dis2,
                    item.dis3,
                    item.deal
                );
                totalAmount += itemTotal;
                
                // Store item breakdown for chart
                itemBreakdown[response.supplier_name][item.product_name] = itemTotal;
                
                // Collect unique product names
                if (!productNames.includes(item.product_name)) {
                    productNames.push(item.product_name);
                }
                
                // Store item with calculated total
                itemsWithTotals.push({
                    product_name: item.product_name,
                    quantity: item.quantity,
                    unit_price: item.unit_price,
                    dis1: item.dis1,
                    dis2: item.dis2,
                    dis3: item.dis3,
                    deal: item.deal,
                    item_total: itemTotal
                });
            });
            
            itemsHtml += '</tbody></table>';
            $row.append(`<td>${itemsHtml}</td>`);
            
            // Total Amount
            $row.append(`<td><strong>${totalAmount.toFixed(2)}</strong></td>`);
            
            $suppliersTbody.append($row);
            
            // Store complete supplier data for modal
            supplierFullData[response.supplier_name] = {
                supplier_name: response.supplier_name,
                remarks: response.remarks || '-',
                items: itemsWithTotals,
                total_amount: totalAmount.toFixed(2)
            };
            
            // Store supplier names
            supplierNames.push(response.supplier_name);
        });

        // Create/Update Chart
        createStackedBarChart(supplierNames, productNames, itemBreakdown);
    }

    function createStackedBarChart(supplierNames, productNames, itemBreakdown) {
        var chartDom = document.getElementById('supplier_chart');
        
        // Dispose existing chart if any
        if (myChart != null) {
            myChart.dispose();
        }
        
        myChart = echarts.init(chartDom);
        
        // Generate colors for each product
        var colors = [
            '#3498db', '#e74c3c', '#2ecc71', '#f39c12', '#9b59b6',
            '#1abc9c', '#34495e', '#e67e22', '#95a5a6', '#d35400'
        ];
        
        // Prepare series data for stacked bar
        var series = [];
        productNames.forEach(function(productName, index) {
            var seriesData = [];
            supplierNames.forEach(function(supplierName) {
                var value = itemBreakdown[supplierName][productName] || 0;
                seriesData.push(value.toFixed(2));
            });
            
            series.push({
                name: productName,
                type: 'bar',
                stack: 'total',
                emphasis: {
                    focus: 'series'
                },
                itemStyle: {
                    color: colors[index % colors.length]
                },
                data: seriesData
            });
        });
        
        var option = {
            title: {
                text: 'Supplier Comparison - Item Level Breakdown',
                left: 'center'
            },
            tooltip: {
                trigger: 'axis',
                axisPointer: {
                    type: 'shadow'
                },
                formatter: function(params) {
                    var result = params[0].axisValue + '<br/>';
                    var total = 0;
                    params.forEach(function(item) {
                        if (parseFloat(item.value) > 0) {
                            result += item.marker + ' ' + item.seriesName + ': ' + item.value + '<br/>';
                            total += parseFloat(item.value);
                        }
                    });
                    result += '<strong>Total: ' + total.toFixed(2) + '</strong>';
                    return result;
                }
            },
            legend: {
                data: productNames,
                top: 30,
                type: 'scroll'
            },
            grid: {
                left: '3%',
                right: '4%',
                bottom: '3%',
                containLabel: true
            },
            xAxis: {
                type: 'category',
                data: supplierNames,
                axisLabel: {
                    interval: 0,
                    rotate: 30
                }
            },
            yAxis: {
                type: 'value',
                name: 'Amount'
            },
            series: series
        };
        
        myChart.setOption(option);
        
        // Add click event to show modal with supplier details
        myChart.on('click', function(params) {
            var supplierName = params.name;
            showSupplierModal(supplierName);
        });
        
        // Resize chart on window resize
        $(window).on('resize', function() {
            if (myChart != null) {
                myChart.resize();
            }
        });
    }

    function showSupplierModal(supplierName) {
        var supplierData = supplierFullData[supplierName];
        
        if (!supplierData) {
            bootbox.alert('Supplier data not found!');
            return;
        }
        
        // Populate modal with supplier data
        $('#modal_supplier_name').text(supplierData.supplier_name);
        $('#modal_total_amount').text(supplierData.total_amount);
        $('#modal_remarks').text(supplierData.remarks);
        
        // Populate items table
        var $modalItemsTbody = $('#modal_items_table tbody').empty();
        supplierData.items.forEach(function(item) {
            var $row = $('<tr>');
            $row.append(`<td>${item.product_name}</td>`);
            $row.append(`<td>${parseFloat(item.quantity).toFixed(2)}</td>`);
            $row.append(`<td>${parseFloat(item.unit_price).toFixed(2)}</td>`);
            $row.append(`<td>${parseFloat(item.dis1).toFixed(2)}</td>`);
            $row.append(`<td>${parseFloat(item.dis2).toFixed(2)}</td>`);
            $row.append(`<td>${parseFloat(item.dis3).toFixed(2)}</td>`);
            $row.append(`<td>${parseFloat(item.deal).toFixed(2)}</td>`);
            $row.append(`<td><strong>${parseFloat(item.item_total).toFixed(2)}</strong></td>`);
            $modalItemsTbody.append($row);
        });
        
        // Show the modal
        $('#supplierModal').modal('show');
    }
});
</script>

<style>
    #items_table th, #items_table td,
    #suppliers_table th, #suppliers_table td {
        text-align: center;
        vertical-align: middle;
    }
    
    #suppliers_table .table-sm {
        font-size: 0.85em;
    }
    
    #suppliers_table .table-sm th,
    #suppliers_table .table-sm td {
        padding: 0.3rem;
    }
    
    #supplier_chart {
        margin-bottom: 30px;
        cursor: pointer;
    }
    
    .form-control-static {
        padding-top: 7px;
        padding-bottom: 7px;
        margin-bottom: 0;
        min-height: 34px;
    }
    
    #modal_items_table th,
    #modal_items_table td {
        text-align: center;
        vertical-align: middle;
    }
    
    .modal-lg {
        width: 90%;
        max-width: 1200px;
    }
    
    #modal_total_amount {
        color: #27ae60;
    }
</style>