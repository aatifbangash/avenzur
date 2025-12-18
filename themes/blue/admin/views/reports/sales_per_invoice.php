<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-file-text-o"></i><?= lang('sales_per_invoice_report'); ?></h2>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
                <?php
$attrib = ['data-toggle' => 'validator', 'role' => 'form'];
echo admin_form_open('reports/sales_per_invoice', $attrib);
?>

<div class="row">
    <!-- Period -->
    <div class="col-md-2 mb-3">
        <label><?= lang('Period') ?></label>
        <select name="period" class="form-control" id="period">
            <option value="today" <?= (isset($period) && $period=='today') ? 'selected' : '' ?>>Today</option>
            <option value="month" <?= (isset($period) && $period=='month') ? 'selected' : '' ?>>This Month</option>
            <option value="ytd" <?= (isset($period) && $period=='ytd') ? 'selected' : '' ?>>This Year</option>
        </select>
    </div>

    <!-- Customer -->
    <div class="col-md-3 mb-3">
        <label><?= lang('customer') ?></label>
        <select name="customer_id" class="form-control select2" id="customer_id">
            <option value=""><?= lang('all') ?></option>
            <?php if (isset($customers) && is_array($customers)): ?>
                <?php foreach ($customers as $customer): ?>
                    <option value="<?= $customer->id ?>" <?= (isset($customer_id) && $customer_id == $customer->id) ? 'selected' : '' ?>>
                        <?= $customer->name ?>
                    </option>
                <?php endforeach; ?>
            <?php endif; ?>
        </select>
    </div>

    <!-- Pharmacy -->
    <div class="col-md-3 mb-3">
        <label><?= lang('warehouse') ?></label>
        <select name="pharmacy_id" class="form-control select2" id="pharmacy_id">
            <option value=""><?= lang('all') ?></option>
            <?php if (isset($warehouses) && is_array($warehouses)): ?>
                <?php foreach ($warehouses as $warehouse): ?>
                    <option value="<?= $warehouse->id ?>" <?= (isset($pharmacy_id) && $pharmacy_id == $warehouse->id) ? 'selected' : '' ?>>
                        <?= $warehouse->name ?>
                    </option>
                <?php endforeach; ?>
            <?php endif; ?>
        </select>
    </div>
</div>

<hr>

<!-- Buttons Row -->
<div class="row">
    <div class="col-md-2 mb-3">
        <button type="submit" name="submit" class="btn btn-primary btn-block">
            <i class="fa fa-search"></i> <?= lang('generate') ?>
        </button>
    </div>

    <div class="col-md-2 mb-3">
        <button type="button" class="btn btn-success btn-block" onclick="exportToExcel()">
            <i class="fa fa-file-excel-o"></i> <?= lang('export_to_excel') ?>
        </button>
    </div>
</div>

<?php echo form_close(); ?>
<hr>

                <!-- REPORT TABLE -->
                <?php if (!empty($invoices)) : ?>
                    <div class="row">
                        <div class="col-lg-12 table-responsive">
                            <table id="invoiceTable" class="table table-bordered table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th><?= lang('type') ?></th>
                                        <th><?= lang('date') ?></th>
                                        <th>Sale Invoice No</th>
                                        <th>Return Inv No</th>
                                        <th>Area</th>
                                        <th>Sales Man</th>
                                        <th>Customer No</th>
                                        <th>Customer Name</th>
                                        <th class="text-right">Sales</th>
                                        <th class="text-right">Discount</th>
                                        <th class="text-right">Net Sales</th>
                                        <th class="text-right">COGS</th>
                                        <th class="text-right">Profit</th>
                                        <th class="text-right">VAT</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($invoices as $invoice): ?>
                                        <tr class="<?= (isset($invoice->type) && $invoice->type == 'Return') ? 'return-row' : '' ?>">
                                            <td><?= isset($invoice->type) ? $invoice->type : 'Sale' ?></td>
                                            <td><?= isset($invoice->date) ? $this->sma->hrld($invoice->date) : '' ?></td>
                                            <td><?= isset($invoice->sale_invoice_no) ? $invoice->sale_invoice_no : '' ?></td>
                                            <td><?= isset($invoice->return_inv_no) ? $invoice->return_inv_no : '' ?></td>
                                            <td><?= isset($invoice->area) ? $invoice->area : '' ?></td>
                                            <td><?= isset($invoice->sales_man) ? $invoice->sales_man : '' ?></td>
                                            <td><?= isset($invoice->customer_no) ? $invoice->customer_no : '' ?></td>
                                            <td><?= isset($invoice->customer_name) ? $invoice->customer_name : '' ?></td>
                                            <td class="text-right"><?= $this->sma->formatMoney(isset($invoice->sales) ? $invoice->sales : 0) ?></td>
                                            <td class="text-right"><?= $this->sma->formatMoney(isset($invoice->discount) ? $invoice->discount : 0) ?></td>
                                            <td class="text-right"><?= $this->sma->formatMoney(isset($invoice->net_sales) ? $invoice->net_sales : 0) ?></td>
                                            <td class="text-right"><?= $this->sma->formatMoney(isset($invoice->cogs) ? $invoice->cogs : 0) ?></td>
                                            <td class="text-right"><?= $this->sma->formatMoney(isset($invoice->profit) ? $invoice->profit : 0) ?></td>
                                            <td class="text-right"><?= $this->sma->formatMoney(isset($invoice->vat) ? $invoice->vat : 0) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                                <tfoot>
                                    <tr class="active">
                                        <th colspan="8" class="text-right"><?= lang('total') ?>:</th>
                                        <th class="text-right"><?= $this->sma->formatMoney(isset($totals['total_sales']) ? $totals['total_sales'] : 0) ?></th>
                                        <th class="text-right"><?= $this->sma->formatMoney(isset($totals['total_discount']) ? $totals['total_discount'] : 0) ?></th>
                                        <th class="text-right"><?= $this->sma->formatMoney(isset($totals['total_net_sales']) ? $totals['total_net_sales'] : 0) ?></th>
                                        <th class="text-right"><?= $this->sma->formatMoney(isset($totals['total_cogs']) ? $totals['total_cogs'] : 0) ?></th>
                                        <th class="text-right"><?= $this->sma->formatMoney(isset($totals['total_profit']) ? $totals['total_profit'] : 0) ?></th>
                                        <th class="text-right"><?= $this->sma->formatMoney(isset($totals['total_vat']) ? $totals['total_vat'] : 0) ?></th>
                                    </tr>
                                    <tr class="info">
                                        <th colspan="14" class="text-center">
                                            <?= lang('total_invoices') ?>: <?= isset($totals['total_invoices']) ? $totals['total_invoices'] : 0 ?> |
                                            <?= lang('total_items') ?>: <?= isset($totals['total_items']) ? $totals['total_items'] : 0 ?>
                                        </th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                <?php elseif(isset($invoices)): ?>
                    <div class="alert alert-info text-center">
                        <i class="fa fa-info-circle"></i> <?= lang('no_data_available') ?>
                    </div>
                <?php else: ?>
                    <div class="alert alert-warning text-center">
                        <i class="fa fa-search"></i> <?= lang('select_date_range_to_generate_report') ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
$(document).ready(function() {
    // Initialize date picker
    $('.date').datetimepicker({
        format: 'DD-MM-YYYY',
        fontAwesome: true
    });

    // Initialize select2
    $('.select2').select2({
        placeholder: '<?= lang('select') ?>',
        allowClear: true
    });
});

// Export to Excel function
function exportToExcel() {
    var table = document.getElementById('invoiceTable');
    if (!table) {
        alert('<?= lang('no_data_to_export') ?>');
        return;
    }

    var html = table.outerHTML;
    var url = 'data:application/vnd.ms-excel,' + escape(html);
    var link = document.createElement('a');
    link.href = url;
    link.download = 'sales_per_invoice_' + new Date().getTime() + '.xls';
    link.click();
}
</script>

<style>
    .table-responsive {
        overflow-x: auto;
    }
    .table th, .table td {
        white-space: nowrap;
    }
    .text-right {
        text-align: right;
    }
    .mb-3 {
        margin-bottom: 15px;
    }
    .return-row {
        background-color: #fff3cd !important;
        color: #856404;
    }
    .return-row:hover {
        background-color: #ffe69c !important;
    }
</style>

