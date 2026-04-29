<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.17.3/xlsx.full.min.js"></script>
<script>
    function exportTableToExcel(tableId, filename = 'table.xlsx') {
        const table = document.getElementById(tableId);
        const wb = XLSX.utils.table_to_book(table, {
            sheet: 'Sheet 1'
        });
        XLSX.writeFile(wb, filename);
    }

    // ── Pagination ────────────────────────────────────────────────────
    var SPI_PAGE_SIZE = 100;
    var SPI_current   = 1;

    function spiGetRows() {
        return $('#salesItemTable tbody tr.spi-data-row');
    }

    function spiRender() {
        var rows  = spiGetRows();
        var total = rows.length;
        var pages = Math.max(1, Math.ceil(total / SPI_PAGE_SIZE));
        if (SPI_current > pages) SPI_current = pages;

        rows.hide();
        var start = (SPI_current - 1) * SPI_PAGE_SIZE;
        rows.slice(start, start + SPI_PAGE_SIZE).show();

        // Info
        var from = total === 0 ? 0 : start + 1;
        var to   = Math.min(start + SPI_PAGE_SIZE, total);
        $('#spi-page-info').text('Showing ' + from + '\u2013' + to + ' of ' + total + ' rows');

        // Buttons
        $('#spi-prev').prop('disabled', SPI_current <= 1);
        $('#spi-next').prop('disabled', SPI_current >= pages);
        $('#spi-page-num').text('Page ' + SPI_current + ' of ' + pages);
    }

    $(document).ready(function () {
        if ($('.spi-data-row').length) { spiRender(); }

        $(document).on('click', '#spi-prev', function () {
            if (SPI_current > 1) { SPI_current--; spiRender(); }
        });
        $(document).on('click', '#spi-next', function () {
            var pages = Math.max(1, Math.ceil(spiGetRows().length / SPI_PAGE_SIZE));
            if (SPI_current < pages) { SPI_current++; spiRender(); }
        });
    });
</script>

<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-file-text"></i><?= lang('Sales Per Item'); ?></h2>
        <div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown">
                    <a href="javascript:void(0);" onclick="exportTableToExcel('salesItemTable', 'sales_per_item.xlsx')" id="xls" class="tip" title="<?= lang('download_xls') ?>"><i class="icon fa fa-file-excel-o"></i></a>
                </li>
            </ul>
        </div>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
                <?php
                $attrib = ['data-toggle' => 'validator', 'role' => 'form', 'id' => 'searchForm', 'method' => 'get'];
                echo admin_form_open_multipart('reports/sales_per_item', $attrib)
                ?>
                <div class="row">
                    <div class="col-lg-12">
                        <div class="col-md-3">
                            <div class="form-group">
                                <?= lang('start_date', 'start_date'); ?>
                                <?php echo form_input('start_date', ($_GET['start_date'] ?? ''), 'class="form-control input-tip date" id="start_date"'); ?>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="form-group">
                                <?= lang('end_date', 'end_date'); ?>
                                <?php echo form_input('end_date', ($_GET['end_date'] ?? ''), 'class="form-control input-tip date" id="end_date"'); ?>
                            </div>
                        </div>

                        <div class="col-md-2">
                            <div class="form-group">
                                <?= lang('invoice', 'invoice_id'); ?>
                                <?php echo form_input('invoice_id', ($_GET['invoice_id'] ?? ''), 'class="form-control" id="invoice_id" placeholder="' . lang('invoice') . '"'); ?>
                            </div>
                        </div>

                        <div class="col-md-2">
                            <div class="form-group">
                                <?= lang('Salesman', 'salesman'); ?>
                                <?php
                                $sm[''] = lang('select') . ' ' . lang('Salesman');
                                foreach ($salesmen as $salesman_item) {
                                    $sm[$salesman_item->id] = $salesman_item->name;
                                }
                                echo form_dropdown('salesman', $sm, ($_GET['salesman'] ?? ''), 'id="salesman" class="form-control skip" data-placeholder="' . lang('select') . ' ' . lang('Salesman') . '" style="width:100%;"');
                                ?>
                            </div>
                        </div>

                        <div class="col-md-2">
                            <div class="form-group">
                                <?= lang('Item Code/Name', 'item_code'); ?>
                                <?php echo form_input('item_code', ($_GET['item_code'] ?? ''), 'class="form-control" id="item_code" placeholder="' . lang('Item') . '"'); ?>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-12">
                        <div class="col-md-3">
                            <div class="form-group">
                                <?= lang('Category', 'category'); ?>
                                <?php
                                $cat_opts = ['' => lang('All Categories')];
                                foreach ($customer_categories as $cat) {
                                    $cat_opts[$cat->category] = $cat->category;
                                }
                                echo form_dropdown('category', $cat_opts, ($_GET['category'] ?? ''), 'id="category" class="form-control skip" style="width:100%;"');
                                ?>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>&nbsp;</label><br>
                                <button type="submit" style="margin-top: 0px;" class="btn btn-primary" id="load_report"><?= lang('Load Report') ?></button>
                            </div>
                        </div>
                    </div>
                </div>
                <?php echo form_close(); ?>

                <hr />

                <div class="row">
                    <div class="col-lg-12">
                        <div class="table-responsive">
                            <table id="salesItemTable" class="table table-bordered table-striped table-condensed table-hover">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th><?= lang('Type'); ?></th>
                                        <th><?= lang('Date'); ?></th>
                                        <th><?= lang('Invoice'); ?></th>
                                        <th><?= lang('Return Inv#'); ?></th>
                                        <th><?= lang('Area'); ?></th>
                                        <th><?= lang('Sales Man'); ?></th>
                                        <th><?= lang('Agent'); ?></th>
                                        <th><?= lang('Customer No'); ?></th>
                                        <th><?= lang('Customer Name'); ?></th>
                                        <th><?= lang('Item No'); ?></th>
                                        <th><?= lang('Item Name'); ?></th>
                                        <th><?= lang('QTY'); ?></th>
                                        <th><?= lang('Bonus'); ?></th>
                                        <th><?= lang('Unit Cost'); ?></th>
                                        <th><?= lang('Unit Price'); ?></th>
                                        <th><?= lang('Sales'); ?></th>
                                        <th><?= lang('Discount'); ?></th>
                                        <th><?= lang('Net Sales'); ?></th>
                                        <th><?= lang('Vat'); ?></th>
                                        <th><?= lang('Receivable'); ?></th>
                                        <th><?= lang('COGS'); ?></th>
                                        <th><?= lang('Profit'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    if (isset($sales_data) && !empty($sales_data)) {
                                        $count = 0;
                                        // Initialize grand totals
                                        $grand_totals = [
                                            'qty' => 0,
                                            'bonus' => 0,
                                            'sales' => 0,
                                            'discount' => 0,
                                            'net_sales' => 0,
                                            'vat' => 0,
                                            'receivable' => 0,
                                            'cogs' => 0,
                                            'profit' => 0
                                        ];

                                        foreach ($sales_data as $data) {
                                            $count++;
                                            
                                            // Accumulate totals
                                            $grand_totals['qty'] += $data->qty;
                                            $grand_totals['bonus'] += $data->bonus;
                                            $grand_totals['sales'] += $data->sales;
                                            $grand_totals['discount'] += $data->discount;
                                            $grand_totals['net_sales'] += $data->net_sales;
                                            $grand_totals['vat'] += $data->vat;
                                            $grand_totals['receivable'] += $data->receivable;
                                            $grand_totals['cogs'] += $data->cogs;
                                            $grand_totals['profit'] += $data->profit;
                                            
                                            // Determine row class for returns (red)
                                            $row_class = ($data->type == 'Return') ? 'style="background-color: #ffe6e6;"' : '';
                                            ?>
                                            <tr class="spi-data-row" <?= $row_class ?>>
                                                <td><?= $count ?></td>
                                                <td><?= $data->type ?></td>
                                                <td><?= $data->date ?></td>
                                                <td><?= $data->invoice ?></td>
                                                <td><?= $data->return_inv ?></td>
                                                <td><?= $data->area ?></td>
                                                <td><?= $data->sales_man ?></td>
                                                <td><?= $data->agent ?></td>
                                                <td><?= $data->customer_no ?></td>
                                                <td><?= $data->customer_name ?></td>
                                                <td><?= $data->item_no ?></td>
                                                <td><?= $data->item_name ?></td>
                                                <td class="text-right"><?= $this->sma->formatQuantity($data->qty) ?></td>
                                                <td class="text-right"><?= $data->bonus ?></td>
                                                <td class="text-right"><?= number_format($data->unit_cost, 2, '.', ',') ?></td>
                                                <td class="text-right"><?= number_format($data->unit_price, 2, '.', ',') ?></td>
                                                <td class="text-right"><?= number_format($data->sales, 2, '.', ',') ?></td>
                                                <td class="text-right"><?= number_format($data->discount, 2, '.', ',') ?></td>
                                                <td class="text-right"><?= number_format($data->net_sales, 2, '.', ',') ?></td>
                                                <td class="text-right"><?= number_format($data->vat, 2, '.', ',') ?></td>
                                                <td class="text-right"><?= number_format($data->receivable, 2, '.', ',') ?></td>
                                                <td class="text-right"><?= number_format($data->cogs, 2, '.', ',') ?></td>
                                                <td class="text-right"><?= number_format($data->profit, 2, '.', ',') ?></td>
                                            </tr>
                                        <?php
                                        }

                                        // Display grand totals row
                                        ?>
                                        <tr style="background-color: #f0f0f0; font-weight: bold;">
                                            <td colspan="12" class="text-right"><strong><?= lang('Grand Total'); ?>:</strong></td>
                                            <td class="text-right"><strong><?= $this->sma->formatQuantity($grand_totals['qty']) ?></strong></td>
                                            <td class="text-right"><strong><?= $grand_totals['bonus'] ?></strong></td>
                                            <td colspan="2"></td>
                                            <td class="text-right"><strong><?= number_format($grand_totals['sales'], 2, '.', ',') ?></strong></td>
                                            <td class="text-right"><strong><?= number_format($grand_totals['discount'], 2, '.', ',') ?></strong></td>
                                            <td class="text-right"><strong><?= number_format($grand_totals['net_sales'], 2, '.', ',') ?></strong></td>
                                            <td class="text-right"><strong><?= number_format($grand_totals['vat'], 2, '.', ',') ?></strong></td>
                                            <td class="text-right"><strong><?= number_format($grand_totals['receivable'], 2, '.', ',') ?></strong></td>
                                            <td class="text-right"><strong><?= number_format($grand_totals['cogs'], 2, '.', ',') ?></strong></td>
                                            <td class="text-right"><strong><?= number_format($grand_totals['profit'], 2, '.', ',') ?></strong></td>
                                        </tr>
                                    <?php
                                    } else {
                                        ?>
                                        <tr>
                                            <td colspan="23" class="text-center"><?= lang('No records found. Please select filters and click Load Report.'); ?></td>
                                        </tr>
                                    <?php
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination controls -->
                        <?php if (isset($sales_data) && !empty($sales_data)): ?>
                        <div style="margin-top:10px; display:flex; align-items:center; gap:10px; flex-wrap:wrap;">
                            <button id="spi-prev" class="btn btn-default btn-sm" type="button">
                                <i class="fa fa-chevron-left"></i> Prev
                            </button>
                            <span id="spi-page-num" style="font-size:13px;"></span>
                            <button id="spi-next" class="btn btn-default btn-sm" type="button">
                                Next <i class="fa fa-chevron-right"></i>
                            </button>
                            <span id="spi-page-info" class="text-muted" style="font-size:12px; margin-left:8px;"></span>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
