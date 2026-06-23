<?php defined('BASEPATH') or exit('No direct script access allowed');
$spi_export_q = $_GET;
$spi_export_q['export_excel'] = '1';
unset($spi_export_q['spi_page']);
$spi_export_url = admin_url('reports/sales_per_item?' . http_build_query($spi_export_q));
?>
<style>
    .sales-pi-root .spi-table-block {
        min-width: 0;
        max-width: 100%;
    }
    .sales-pi-root .spi-table-block .table-responsive {
        width: 100%;
        max-width: 100%;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }
    .sales-pi-root #salesItemTable {
        margin-bottom: 0;
    }
    .sales-pi-root #salesItemTable th:nth-child(-n+5),
    .sales-pi-root #salesItemTable td:nth-child(-n+5),
    .sales-pi-root #salesItemTable th:nth-child(12),
    .sales-pi-root #salesItemTable td:nth-child(12),
    .sales-pi-root #salesItemTable th:nth-child(n+14),
    .sales-pi-root #salesItemTable td:nth-child(n+14) {
        white-space: nowrap;
        font-size: 12px;
        padding: 4px 6px;
        vertical-align: middle;
    }
    .sales-pi-root #salesItemTable th:nth-child(n+6):nth-child(-n+11),
    .sales-pi-root #salesItemTable td:nth-child(n+6):nth-child(-n+11) {
        white-space: normal;
        word-break: break-word;
        overflow-wrap: anywhere;
        font-size: 12px;
        padding: 4px 6px;
        vertical-align: middle;
        max-width: 9rem;
    }
    .sales-pi-root #salesItemTable th:nth-child(13),
    .sales-pi-root #salesItemTable td:nth-child(13) {
        white-space: normal;
        word-break: break-word;
        overflow-wrap: anywhere;
        font-size: 12px;
        padding: 4px 6px;
        vertical-align: middle;
        max-width: 16rem;
    }
    .sales-pi-root #salesItemTable th:nth-child(5),
    .sales-pi-root #salesItemTable td:nth-child(5) {
        min-width: 6.5rem;
        max-width: 8.5rem;
        width: 1%;
        padding: 4px 5px;
        font-size: 12px;
    }
    .sales-pi-root #salesItemTable th:nth-child(5) {
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .sales-pi-root .spi-pager-well {
        max-width: 100%;
        overflow-wrap: anywhere;
    }
</style>

<div class="box sales-pi-root">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-file-text"></i><?= lang('Sales Per Item'); ?></h2>
        <div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown">
                    <a href="<?= $spi_export_url ?>" class="tip" title="<?= lang('download'); ?> CSV (<?= lang('all'); ?>)" id="xls"><i class="icon fa fa-file-excel-o"></i></a>
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
                                <?php echo form_input('start_date', ($start_date ?? ''), 'class="form-control input-tip date" id="start_date"'); ?>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="form-group">
                                <?= lang('end_date', 'end_date'); ?>
                                <?php echo form_input('end_date', ($end_date ?? ''), 'class="form-control input-tip date" id="end_date"'); ?>
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
                        <?php $this->load->view($this->theme . 'reports/partials/warehouse_filter_field', ['wh_col' => 'col-md-3']); ?>
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

                <div class="row spi-table-block">
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
                                    $spi_per_page = isset($sales_per_page) ? (int) $sales_per_page : 100;
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
                                            <tr <?= $row_class ?>>
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
                            $spi_from = $spi_total_rows ? (($spi_page_num - 1) * $spi_per_page) + 1 : 0;
                            $spi_to = min($spi_page_num * $spi_per_page, $spi_total_rows);
                            ?>
                        <div class="well well-sm spi-pager-well" style="margin-top:10px; display:flex; align-items:center; flex-wrap:wrap; gap:10px;">
                            <span class="text-muted">Rows <?= (int) $spi_from ?>–<?= (int) $spi_to ?> of <?= (int) $spi_total_rows ?> · Page <?= (int) $spi_page_num ?> / <?= (int) $spi_total_pages ?></span>
                            <?php if ($spi_total_pages > 1) {
                                $spi_link['spi_page'] = max(1, $spi_page_num - 1);
                                $prev_u = admin_url('reports/sales_per_item?' . http_build_query($spi_link));
                                $spi_link['spi_page'] = min($spi_total_pages, $spi_page_num + 1);
                                $next_u = admin_url('reports/sales_per_item?' . http_build_query($spi_link));
                                ?>
                            <a class="btn btn-default btn-sm<?= $spi_page_num <= 1 ? ' disabled' : '' ?>" href="<?= $spi_page_num <= 1 ? '#' : $prev_u ?>"><i class="fa fa-chevron-left"></i> Prev</a>
                            <a class="btn btn-default btn-sm<?= $spi_page_num >= $spi_total_pages ? ' disabled' : '' ?>" href="<?= $spi_page_num >= $spi_total_pages ? '#' : $next_u ?>">Next <i class="fa fa-chevron-right"></i></a>
                            <?php } ?>
                        </div>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
