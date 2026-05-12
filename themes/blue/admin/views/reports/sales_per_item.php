<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.17.3/xlsx.full.min.js"></script>
<style>
    #salesItemTable th,
    #salesItemTable td {
        white-space: nowrap;
        font-size: 12px;
        padding: 4px 6px;
        vertical-align: middle;
    }
    /* Allow wrapping only for long-text columns */
    #salesItemTable th:nth-child(10),
    #salesItemTable td:nth-child(10),
    #salesItemTable th:nth-child(12),
    #salesItemTable td:nth-child(12) {
        white-space: normal;
        min-width: 130px;
        max-width: 200px;
    }
</style>
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
                                <label><?= lang('rows_per_page'); ?></label>
                                <?php
                                $cur_pp = isset($_GET['per_page']) ? (int) $_GET['per_page'] : 500;
                                if ($cur_pp < 100 || $cur_pp > 5000) {
                                    $cur_pp = 500;
                                }
                                $pp_opts = [
                                    '250' => '250',
                                    '500' => '500',
                                    '1000' => '1,000',
                                    '2000' => '2,000',
                                    '5000' => '5,000',
                                ];
                                echo form_dropdown('per_page', $pp_opts, (string) $cur_pp, 'class="form-control" id="per_page"');
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
                                        <th><?= lang('Category'); ?></th>
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
                                    $spi_total_rows = isset($sales_data_total) ? (int) $sales_data_total : 0;
                                    $spi_per_page = isset($sales_per_page) ? (int) $sales_per_page : 500;
                                    $spi_page_num = isset($sales_page) ? (int) $sales_page : 1;
                                    $spi_total_pages = $spi_per_page > 0 ? (int) ceil($spi_total_rows / $spi_per_page) : 1;
                                    if ($spi_total_pages < 1) {
                                        $spi_total_pages = 1;
                                    }

                                    if (isset($sales_data) && !empty($sales_data)) {
                                        $count = ($spi_page_num - 1) * $spi_per_page;
                                        $use_sql_totals = !empty($sales_data_totals);

                                        foreach ($sales_data as $data) {
                                            $count++;
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
                                                <td><?= $data->category ?></td>
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

                                        if ($use_sql_totals) {
                                            $t = $sales_data_totals;
                                            ?>
                                        <tr style="background-color: #f0f0f0; font-weight: bold;">
                                            <td colspan="13" class="text-right"><strong><?= lang('Grand Total'); ?>:</strong></td>
                                            <td class="text-right"><strong><?= $this->sma->formatQuantity($t->sum_qty) ?></strong></td>
                                            <td class="text-right"><strong><?= number_format((float) $t->sum_bonus, 2, '.', ',') ?></strong></td>
                                            <td colspan="2"></td>
                                            <td class="text-right"><strong><?= number_format($t->sum_sales, 2, '.', ',') ?></strong></td>
                                            <td class="text-right"><strong><?= number_format($t->sum_discount, 2, '.', ',') ?></strong></td>
                                            <td class="text-right"><strong><?= number_format($t->sum_net_sales, 2, '.', ',') ?></strong></td>
                                            <td class="text-right"><strong><?= number_format($t->sum_vat, 2, '.', ',') ?></strong></td>
                                            <td class="text-right"><strong><?= number_format($t->sum_receivable, 2, '.', ',') ?></strong></td>
                                            <td class="text-right"><strong><?= number_format($t->sum_cogs, 2, '.', ',') ?></strong></td>
                                            <td class="text-right"><strong><?= number_format($t->sum_profit, 2, '.', ',') ?></strong></td>
                                        </tr>
                                            <?php
                                        }
                                    } else {
                                        ?>
                                        <tr>
                                            <td colspan="24" class="text-center"><?= lang('No records found. Please select filters and click Load Report.'); ?></td>
                                        </tr>
                                    <?php
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>

                        <?php
                        if (!empty($sales_data_total) && (int) $sales_data_total > 0) {
                            $spi_link = [];
                            foreach (['start_date', 'end_date', 'invoice_id', 'salesman', 'item_code', 'category'] as $__k) {
                                if (isset(${$__k}) && ${$__k} !== '' && ${$__k} !== null) {
                                    $spi_link[$__k] = ${$__k};
                                }
                            }
                            $spi_link['per_page'] = $spi_per_page;
                            $spi_from = $spi_total_rows ? (($spi_page_num - 1) * $spi_per_page) + 1 : 0;
                            $spi_to = min($spi_page_num * $spi_per_page, $spi_total_rows);
                            ?>
                        <div class="well well-sm" style="margin-top:10px;">
                            <span class="text-muted">Rows <?= (int) $spi_from ?>–<?= (int) $spi_to ?> of <?= (int) $spi_total_rows ?> (page <?= (int) $spi_page_num ?> / <?= (int) $spi_total_pages ?>)</span>
                            <?php if ($spi_total_pages > 1) {
                                $spi_link['spi_page'] = max(1, $spi_page_num - 1);
                                $prev_u = admin_url('reports/sales_per_item?' . http_build_query($spi_link));
                                $spi_link['spi_page'] = min($spi_total_pages, $spi_page_num + 1);
                                $next_u = admin_url('reports/sales_per_item?' . http_build_query($spi_link));
                                ?>
                            <div style="margin-top:8px;">
                                <a class="btn btn-default btn-sm<?= $spi_page_num <= 1 ? ' disabled' : '' ?>" href="<?= $spi_page_num <= 1 ? '#' : $prev_u ?>" title="Previous page"><i class="fa fa-chevron-left"></i></a>
                                <a class="btn btn-default btn-sm<?= $spi_page_num >= $spi_total_pages ? ' disabled' : '' ?>" href="<?= $spi_page_num >= $spi_total_pages ? '#' : $next_u ?>" title="Next page"><i class="fa fa-chevron-right"></i></a>
                            </div>
                            <?php } ?>
                        </div>
                        <?php } ?>

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
