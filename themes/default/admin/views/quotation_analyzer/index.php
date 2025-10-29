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
                    <h4>Supplier Responses Comparison</h4>
                    <div class="table-responsive">
                        <table id="comparison_table" class="table table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Quantity</th>
                                    <th id="suppliers_header" colspan="1">Suppliers</th>
                                </tr>
                                <tr id="supplier_names">
                                    <th colspan="2"></th>
                                    <!-- Supplier columns will be added here dynamically -->
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Comparison data will be added here dynamically -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
$(document).ready(function() {
    // Initialize Select2
    $('#pr_select').select2();

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
                bootbox.alert('Error loading PR details: ' + error + pr_id + "site:  " + site.base_url + 'quotation_analyzer/get_pr_details/' + pr_id);
            }
        });
    });

    function displayPRDetails(data) {
            // Display PR info
        $('#pr_number').text(data.pr.pr_number);
        $('#pr_date').text(moment(data.pr.created_at).format('DD/MM/YYYY'));
        $('#pr_status').text(data.pr.status);
        $('#pr_details').show();

        // Update suppliers header
        var supplierCount = data.supplier_responses.length;
        $('#suppliers_header').attr('colspan', supplierCount || 1);

        // Clear and rebuild supplier names row
        var $supplierNames = $('#supplier_names');
        $supplierNames.find('th:gt(0)').remove(); // Fixed: remove after first th (not second)
        data.supplier_responses.forEach(function(response) {
            $supplierNames.append(`<th>${response.supplier_name}</th>`);
        });

        // Build comparison table
        var $tbody = $('#comparison_table tbody').empty();
        data.items.forEach(function(item) {
            var $row = $('<tr>');
            $row.append(`<td>${item.product_name}</td>`);
            $row.append(`<td>${item.quantity}</td>`);

            // Add each supplier's quote for this item
            data.supplier_responses.forEach(function(response) {
                var supplierItem = response.items.find(ri => ri.pr_item_id === item.id);
                var cellContent = supplierItem ? `
                    <div>Price: ${supplierItem.unit_price}</div>
                    <div>Dis1: ${supplierItem.dis1}%</div>
                    <div>Dis2: ${supplierItem.dis2}%</div>
                    <div>Dis3: ${supplierItem.dis3}%</div>
                    <div>Deal: ${supplierItem.deal}%</div>
                    ${supplierItem.remarks ? `<div class="text-muted small">Note: ${supplierItem.remarks}</div>` : ''}
                ` : 'No quote';

                $row.append(`<td>${cellContent}</td>`);
            });

            $tbody.append($row);
        });
    }
});
</script>

<style>
    #comparison_table th, #comparison_table td {
        text-align: center;
        vertical-align: middle;
    }
    #comparison_table td div {
        margin: 3px 0;
    }
    .text-muted.small {
        font-size: 0.9em;
        font-style: italic;
    }
</style>