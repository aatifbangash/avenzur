<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-file-text-o"></i><?= lang('purchase_per_invoice_report'); ?></h2>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
                <?php
$attrib = ['data-toggle' => 'validator', 'role' => 'form'];
echo admin_form_open('reports/purchase_per_invoice', $attrib);
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

    <!-- Purchase ID -->
    <div class="col-md-2 mb-3">
        <label><?= lang('Purchase ID') ?></label>
        <input type="text" name="purchase_id" class="form-control" id="purchase_id" 
               value="<?= isset($purchase_id) ? $purchase_id : '' ?>" 
               placeholder="<?= lang('Enter Purchase ID') ?>">
    </div>

    <!-- Supplier -->
    <div class="col-md-3 mb-3">
        <label><?= lang('supplier') ?></label>
        <select name="supplier_id" class="form-control select2" id="supplier_id">
            <option value=""><?= lang('all') ?></option>
            <?php if (isset($suppliers) && is_array($suppliers)): ?>
                <?php foreach ($suppliers as $supplier): ?>
                    <option value="<?= $supplier->id ?>" <?= (isset($supplier_id) && $supplier_id == $supplier->id) ? 'selected' : '' ?>>
                        <?= $supplier->name ?>
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

<?php if (isset($period)): ?>
    <!-- Debug Info (remove after testing) -->
    <div class="alert alert-info" style="font-size: 11px;">
        <strong>Debug Info:</strong> Period: <?= $period ?> |
        Dates: <?= isset($start_date) ? $start_date : 'N/A' ?> to <?= isset($end_date) ? $end_date : 'N/A' ?> |
        Purchase ID: <?= isset($purchase_id) && $purchase_id ? $purchase_id : 'All' ?> |
        Supplier: <?= isset($supplier_id) && $supplier_id ? $supplier_id : 'All' ?> |
        Pharmacy: <?= isset($pharmacy_id) && $pharmacy_id ? $pharmacy_id : 'All' ?> |
        Invoices: <?= isset($invoices) ? (is_array($invoices) ? count($invoices) : 'Not array') : 'Not set' ?>
    </div>
<?php endif; ?>

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
                                        <th><?= lang('invoice') ?></th>
                                        <th><?= lang('return_inv_no') ?></th>
                                        <th><?= lang('agent_name') ?></th>
                                        <th><?= lang('supplier_no') ?></th>
                                        <th><?= lang('supplier_name') ?></th>
                                        <th><?= lang('purchase') ?></th>
                                        <th><?= lang('vat') ?></th>
                                        <th><?= lang('payable') ?></th>
                                        <th><?= lang('payment') ?></th>
                                        <th><?= lang('return') ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($invoices as $invoice): ?>
                                        <tr>
                                            <td><?= isset($invoice->type) ? $invoice->type : 'Purchase' ?></td>
                                            <td><?= $this->sma->hrld($invoice->date) ?></td>
                                            <td><?= isset($invoice->invoice) ? $invoice->invoice : '' ?></td>
                                            <td><?= isset($invoice->return_inv) ? $invoice->return_inv : '0' ?></td>
                                            <td><?= isset($invoice->agent_name) ? $invoice->agent_name : '' ?></td>
                                            <td><?= isset($invoice->supplier_no) ? $invoice->supplier_no : '' ?></td>
                                            <td><?= isset($invoice->supplier_name) ? $invoice->supplier_name : '' ?></td>
                                            <td class="text-right"><?= $this->sma->formatMoney(isset($invoice->purchase) ? $invoice->purchase : 0) ?></td>
                                            <td class="text-right"><?= $this->sma->formatMoney(isset($invoice->vat) ? $invoice->vat : 0) ?></td>
                                            <td class="text-right"><?= $this->sma->formatMoney(isset($invoice->payable) ? $invoice->payable : 0) ?></td>
                                            <td class="text-right"><?= $this->sma->formatMoney(isset($invoice->payment) ? $invoice->payment : 0) ?></td>
                                            <td class="text-right"><?= $this->sma->formatMoney(isset($invoice->return_amount) ? $invoice->return_amount : 0) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                                <tfoot>
                                    <tr class="active">
                                        <th colspan="7" class="text-right"><?= lang('total') ?>:</th>
                                        <th class="text-right"><?= $this->sma->formatMoney(isset($totals['total_purchase']) ? $totals['total_purchase'] : 0) ?></th>
                                        <th class="text-right"><?= $this->sma->formatMoney(isset($totals['total_vat']) ? $totals['total_vat'] : 0) ?></th>
                                        <th class="text-right"><?= $this->sma->formatMoney(isset($totals['total_payable']) ? $totals['total_payable'] : 0) ?></th>
                                        <th class="text-right"><?= $this->sma->formatMoney(isset($totals['total_payment']) ? $totals['total_payment'] : 0) ?></th>
                                        <th class="text-right"><?= $this->sma->formatMoney(isset($totals['total_return']) ? $totals['total_return'] : 0) ?></th>
                                    </tr>
                                    <tr class="info">
                                        <th colspan="12">
                                            <?= lang('total_invoices') ?>: <?= isset($totals) ? $totals['total_invoices'] : 0 ?> |
                                            <?= lang('total_items') ?>: <?= isset($totals) ? $totals['total_items'] : 0 ?> |
                                            <?= lang('total_quantity') ?>: <?= $this->sma->formatQuantity(isset($totals) ? $totals['total_quantity'] : 0) ?>
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
    link.download = 'purchase_per_invoice_' + new Date().getTime() + '.xls';
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
</style>