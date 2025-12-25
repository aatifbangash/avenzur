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
    <!-- From Date -->
    <div class="col-md-3 mb-3">
        <div class="form-group">
            <?= lang('from_date', 'from_date'); ?>
            <?php echo form_input('from_date', ($start_date ?? ''), 'class="form-control input-tip date" id="from_date"'); ?>
        </div>
    </div>

    <!-- To Date -->
    <div class="col-md-3 mb-3">
        <div class="form-group">
            <?= lang('to_date', 'to_date'); ?>
            <?php echo form_input('to_date', ($end_date ?? ''), 'class="form-control input-tip date" id="to_date"'); ?>
        </div>
    </div>

    <!-- Purchase ID -->
    <div class="col-md-2 mb-3">
        <div class="form-group">
            <label><?= lang('Purchase ID') ?></label>
            <input type="text" name="purchase_id" class="form-control" id="purchase_id" 
                   value="<?= isset($purchase_id) ? $purchase_id : '' ?>" 
                   placeholder="<?= lang('Enter Purchase ID') ?>">
        </div>
    </div>

    <!-- Supplier -->
    <div class="col-md-2 mb-3">
        <div class="form-group">
            <?= lang('supplier', 'supplier_id'); ?>
            <select name="supplier_id" class="form-control select2" id="supplier_id" data-placeholder="<?= lang('select') . ' ' . lang('supplier') ?>">
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
    </div>

    <!-- Pharmacy -->
    <div class="col-md-2 mb-3">
        <div class="form-group">
            <?= lang('warehouse', 'pharmacy_id'); ?>
            <select name="pharmacy_id" class="form-control select2" id="pharmacy_id" data-placeholder="<?= lang('select') . ' ' . lang('warehouse') ?>">
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
                                            <td><?= date('d M y', strtotime($invoice->date)) ?></td>
                                            <td><?= isset($invoice->invoice) ? $invoice->invoice : '' ?></td>
                                            <td><?= isset($invoice->return_inv) ? $invoice->return_inv : '0' ?></td>
                                            <td><?= isset($invoice->agent_name) ? $invoice->agent_name : '' ?></td>
                                            <td><?= isset($invoice->supplier_no) ? $invoice->supplier_no : '' ?></td>
                                            <td><?= isset($invoice->supplier_name) ? $invoice->supplier_name : '' ?></td>
                                            <td class="text-right"><?= number_format(isset($invoice->purchase) ? $invoice->purchase : 0, 2) ?></td>
                                            <td class="text-right"><?= number_format(isset($invoice->vat) ? $invoice->vat : 0, 2) ?></td>
                                            <td class="text-right"><?= number_format(isset($invoice->payable) ? $invoice->payable : 0, 2) ?></td>
                                            <td class="text-right"><?= number_format(isset($invoice->payment) ? $invoice->payment : 0, 2) ?></td>
                                            <td class="text-right"><?= number_format(isset($invoice->return_amount) ? $invoice->return_amount : 0, 2) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                                <tfoot>
                                    <tr class="active">
                                        <th colspan="7" class="text-right"><?= lang('total') ?>:</th>
                                        <th class="text-right"><?= number_format(isset($totals['total_purchase']) ? $totals['total_purchase'] : 0, 2) ?></th>
                                        <th class="text-right"><?= number_format(isset($totals['total_vat']) ? $totals['total_vat'] : 0, 2) ?></th>
                                        <th class="text-right"><?= number_format(isset($totals['total_payable']) ? $totals['total_payable'] : 0, 2) ?></th>
                                        <th class="text-right"><?= number_format(isset($totals['total_payment']) ? $totals['total_payment'] : 0, 2) ?></th>
                                        <th class="text-right"><?= number_format(isset($totals['total_return']) ? $totals['total_return'] : 0, 2) ?></th>
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
    // Initialize select2
    $('.select2').select2({
        placeholder: '<?= lang('select') ?>',
        allowClear: true
    });
});

// Export to Excel function with proper UTF-8 encoding for Arabic
function exportToExcel() {
    var table = document.getElementById('invoiceTable');
    if (!table) {
        alert('<?= lang('no_data_to_export') ?>');
        return;
    }

    // Clone the table and format for export
    var tableClone = table.cloneNode(true);
    
    // Get HTML content
    var html = tableClone.outerHTML;
    
    // Create proper Excel format with UTF-8 BOM for Arabic support
    var excelContent = '\uFEFF' + html; // UTF-8 BOM
    
    // Create blob with proper encoding
    var blob = new Blob([excelContent], {
        type: 'application/vnd.ms-excel;charset=utf-8;'
    });
    
    // Create download link
    var link = document.createElement('a');
    if (link.download !== undefined) {
        var url = URL.createObjectURL(blob);
        link.setAttribute('href', url);
        link.setAttribute('download', 'purchase_per_invoice_' + new Date().getTime() + '.xls');
        link.style.visibility = 'hidden';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }
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