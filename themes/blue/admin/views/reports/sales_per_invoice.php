<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<script>
    // ── Pagination ───────────────────────────────────────────────────
    var SPINV_PAGE_SIZE = 100;
    var SPINV_current   = 1;

    function spinvGetRows() {
        return $('#invoiceTable tbody tr.spinv-data-row');
    }

    function spinvRender() {
        var rows  = spinvGetRows();
        var total = rows.length;
        var pages = Math.max(1, Math.ceil(total / SPINV_PAGE_SIZE));
        if (SPINV_current > pages) SPINV_current = pages;

        rows.hide();
        var start = (SPINV_current - 1) * SPINV_PAGE_SIZE;
        rows.slice(start, start + SPINV_PAGE_SIZE).show();

        var from = total === 0 ? 0 : start + 1;
        var to   = Math.min(start + SPINV_PAGE_SIZE, total);
        $('#spinv-page-info').text('Showing ' + from + '–' + to + ' of ' + total + ' rows');

        $('#spinv-prev').prop('disabled', SPINV_current <= 1);
        $('#spinv-next').prop('disabled', SPINV_current >= pages);
        $('#spinv-page-num').text('Page ' + SPINV_current + ' of ' + pages);
    }

    $(document).ready(function () {
        if ($('.spinv-data-row').length) { spinvRender(); }

        $(document).on('click', '#spinv-prev', function () {
            if (SPINV_current > 1) { SPINV_current--; spinvRender(); }
        });
        $(document).on('click', '#spinv-next', function () {
            var pages = Math.max(1, Math.ceil(spinvGetRows().length / SPINV_PAGE_SIZE));
            if (SPINV_current < pages) { SPINV_current++; spinvRender(); }
        });
    });
</script>

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

    <!-- Customer -->
    <div class="col-md-3 mb-3">
        <div class="form-group">
            <?= lang('customer', 'customer_id'); ?>
            <select name="customer_id" class="form-control select2" id="customer_id" data-placeholder="<?= lang('select') . ' ' . lang('customer') ?>">
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
    </div>

    <!-- Pharmacy -->
    <div class="col-md-3 mb-3">
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

<!-- Filter Row 2: Type + Salesman + Buttons -->
<div class="row">
    <div class="col-md-3 mb-3">
        <div class="form-group">
            <label for="record_type"><?= lang('Type') ?></label>
            <select name="record_type" id="record_type" class="form-control">
                <option value="all" <?= (isset($record_type) && $record_type == 'all') ? 'selected' : '' ?>>All</option>
                <option value="sale" <?= (isset($record_type) && $record_type == 'sale') ? 'selected' : '' ?>>Sales Only</option>
                <option value="return" <?= (isset($record_type) && $record_type == 'return') ? 'selected' : '' ?>>Returns Only</option>
            </select>
        </div>
    </div>

    <div class="col-md-3 mb-3">
        <div class="form-group">
            <label for="salesman"><?= lang('Salesman') ?></label>
            <select name="salesman" id="salesman" class="form-control select2" data-placeholder="<?= lang('select') . ' ' . lang('Salesman') ?>">
                <option value=""><?= lang('all') ?></option>
                <?php if (isset($salesmen) && is_array($salesmen)): ?>
                    <?php foreach ($salesmen as $sm): ?>
                        <option value="<?= $sm->id ?>" <?= (isset($salesman_id) && $salesman_id == $sm->id) ? 'selected' : '' ?>>
                            <?= $sm->name ?>
                        </option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select>
        </div>
    </div>

    <div class="col-md-2 mb-3">
        <div class="form-group">
            <label>&nbsp;</label><br>
            <button type="submit" name="submit" class="btn btn-primary btn-block">
                <i class="fa fa-search"></i> <?= lang('generate') ?>
            </button>
        </div>
    </div>

    <div class="col-md-2 mb-3">
        <div class="form-group">
            <label>&nbsp;</label><br>
            <button type="button" class="btn btn-success btn-block" onclick="exportToExcel()">
                <i class="fa fa-file-excel-o"></i> <?= lang('export_to_excel') ?>
            </button>
        </div>
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
                                        <th>Category</th>
                                        <th>Customer Code</th>
                                        <th>Customer Name</th>
                                        <th class="text-right">Sales</th>
                                        <th class="text-right">Discount</th>
                                        <th class="text-right">Net Sales</th>
                                        <th class="text-right">COGS</th>
                                        <th class="text-right">Profit</th>
                                        <th class="text-right">VAT</th>
                                        <th class="text-right">Receivable</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($invoices as $invoice): ?>
                                        <tr class="spinv-data-row <?= (isset($invoice->type) && $invoice->type == 'Return') ? 'return-row' : '' ?>">
                                            <td><?= isset($invoice->type) ? $invoice->type : 'Sale' ?></td>
                                            <td><?= isset($invoice->date) ? date('d M y', strtotime($invoice->date)) : '' ?></td>
                                            <td><?= isset($invoice->sale_invoice_no) ? $invoice->sale_invoice_no : '' ?></td>
                                            <td><?= isset($invoice->return_inv_no) ? $invoice->return_inv_no : '' ?></td>
                                            <td><?= isset($invoice->area) ? $invoice->area : '' ?></td>
                                            <td><?= isset($invoice->sales_man) ? $invoice->sales_man : '' ?></td>
                                            <td><?= isset($invoice->category) ? $invoice->category : '' ?></td>
                                            <td><?= isset($invoice->customer_sequence) ? $invoice->customer_sequence : '' ?></td>
                                            <td><?= isset($invoice->customer_name) ? $invoice->customer_name : '' ?></td>
                                            <td class="text-right"><?= number_format(isset($invoice->sales) ? $invoice->sales : 0, 2) ?></td>
                                            <td class="text-right"><?= number_format(isset($invoice->discount) ? $invoice->discount : 0, 2) ?></td>
                                            <td class="text-right"><?= number_format(isset($invoice->net_sales) ? $invoice->net_sales : 0, 2) ?></td>
                                            <td class="text-right"><?= number_format(isset($invoice->cogs) ? $invoice->cogs : 0, 2) ?></td>
                                            <td class="text-right"><?= number_format(isset($invoice->profit) ? $invoice->profit : 0, 2) ?></td>
                                            <td class="text-right"><?= number_format(isset($invoice->vat) ? $invoice->vat : 0, 2) ?></td>
                                            <td class="text-right"><?= number_format(isset($invoice->receivable) ? $invoice->receivable : 0, 2) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                                <tfoot>
                                    <tr class="active">
                                        <th colspan="9" class="text-right"><?= lang('total') ?>:</th>
                                        <th class="text-right"><?= number_format(isset($totals['total_sales']) ? $totals['total_sales'] : 0, 2) ?></th>
                                        <th class="text-right"><?= number_format(isset($totals['total_discount']) ? $totals['total_discount'] : 0, 2) ?></th>
                                        <th class="text-right"><?= number_format(isset($totals['total_net_sales']) ? $totals['total_net_sales'] : 0, 2) ?></th>
                                        <th class="text-right"><?= number_format(isset($totals['total_cogs']) ? $totals['total_cogs'] : 0, 2) ?></th>
                                        <th class="text-right"><?= number_format(isset($totals['total_profit']) ? $totals['total_profit'] : 0, 2) ?></th>
                                        <th class="text-right"><?= number_format(isset($totals['total_vat']) ? $totals['total_vat'] : 0, 2) ?></th>
                                        <th class="text-right"><?= number_format(isset($totals['total_receivable']) ? $totals['total_receivable'] : 0, 2) ?></th>
                                    </tr>
                                    <tr class="info">
                                        <th colspan="16" class="text-center">
                                            <?= lang('total_invoices') ?>: <?= isset($totals['total_invoices']) ? $totals['total_invoices'] : 0 ?> |
                                            <?= lang('total_items') ?>: <?= isset($totals['total_items']) ? $totals['total_items'] : 0 ?>
                                        </th>
                                    </tr>
                                </tfoot>
                            </table>

                        <!-- Pagination controls -->
                        <div style="margin-top:10px; display:flex; align-items:center; gap:10px; flex-wrap:wrap;">
                            <button id="spinv-prev" class="btn btn-default btn-sm" type="button">
                                <i class="fa fa-chevron-left"></i> Prev
                            </button>
                            <span id="spinv-page-num" style="font-size:13px;"></span>
                            <button id="spinv-next" class="btn btn-default btn-sm" type="button">
                                Next <i class="fa fa-chevron-right"></i>
                            </button>
                            <span id="spinv-page-info" class="text-muted" style="font-size:12px; margin-left:8px;"></span>
                        </div>
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
        link.setAttribute('download', 'sales_per_invoice_' + new Date().getTime() + '.xls');
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
    /* Scope sizing rules to this table only */
    #invoiceTable th,
    #invoiceTable td {
        white-space: nowrap;
        font-size: 12px;
        padding: 4px 6px;
        vertical-align: middle;
    }
    /* Customer Name column (9th) may wrap — it carries long text */
    #invoiceTable th:nth-child(9),
    #invoiceTable td:nth-child(9) {
        white-space: normal;
        min-width: 130px;
        max-width: 200px;
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

