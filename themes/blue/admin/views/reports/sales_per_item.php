<?php defined('BASEPATH') or exit('No direct script access allowed');
$spi_export_q = $_GET;
$spi_export_q['export_excel'] = '1';
unset($spi_export_q['spi_page']);
$spi_export_url = admin_url('reports/sales_per_item?' . http_build_query($spi_export_q));
$spi_total_rows = isset($sales_data_total) ? (int) $sales_data_total : 0;
$spi_per_page = isset($sales_per_page) ? (int) $sales_per_page : 100;
$spi_page_num = isset($sales_page) ? (int) $sales_page : 1;
$spi_total_pages = $spi_per_page > 0 ? (int) ceil($spi_total_rows / $spi_per_page) : 1;
if ($spi_total_pages < 1) {
    $spi_total_pages = 1;
}
$spi_from = $spi_total_rows ? (($spi_page_num - 1) * $spi_per_page) + 1 : 0;
$spi_to = min($spi_page_num * $spi_per_page, $spi_total_rows);
?>
<style>
    .sales-pi-root,
    .sales-pi-root .box-content,
    .sales-pi-root .spi-report-shell,
    .sales-pi-root .spi-table-card,
    .sales-pi-root .spi-table-card .col-lg-12 {
        min-width: 0;
        max-width: 100%;
        overflow-x: hidden;
    }
    .sales-pi-root {
        background: linear-gradient(180deg, #f7f9fc 0%, #eef3f8 100%);
    }
    .sales-pi-root .box-header {
        display: none;
    }
    .sales-pi-root .box-content {
        padding: 22px;
    }
    .sales-pi-root .spi-report-shell {
        display: grid;
        gap: 18px;
    }
    .sales-pi-root .spi-surface {
        background: #ffffff;
        border: 1px solid #dde5ef;
        border-radius: 14px;
        box-shadow: 0 10px 24px rgba(15, 23, 42, 0.06);
    }
    .sales-pi-root .spi-report-hero {
        padding: 22px 24px;
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 16px;
    }
    .sales-pi-root .spi-report-title {
        margin: 0;
        font-size: 24px;
        line-height: 1.2;
        font-weight: 700;
        color: #183153;
    }
    .sales-pi-root .spi-report-subtitle {
        margin: 6px 0 0;
        color: #60758b;
        font-size: 13px;
    }
    .sales-pi-root .spi-hero-actions {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
    }
    .sales-pi-root .spi-export-btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        border-radius: 10px;
        background: #1f7ae0;
        color: #fff;
        text-decoration: none;
        padding: 10px 14px;
        font-size: 13px;
        font-weight: 600;
        box-shadow: 0 8px 18px rgba(31, 122, 224, 0.2);
    }
    .sales-pi-root .spi-export-btn:hover,
    .sales-pi-root .spi-export-btn:focus {
        color: #fff;
        text-decoration: none;
        background: #1667c1;
    }
    .sales-pi-root .spi-kpis {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 12px;
        padding: 0 24px 22px;
    }
    .sales-pi-root .spi-kpi {
        border: 1px solid #e5ebf3;
        border-radius: 12px;
        background: #f8fbff;
        padding: 14px 16px;
    }
    .sales-pi-root .spi-kpi-label {
        display: block;
        color: #6b7c8f;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        margin-bottom: 6px;
    }
    .sales-pi-root .spi-kpi-value {
        display: block;
        color: #16263d;
        font-size: 18px;
        font-weight: 700;
    }
    .sales-pi-root .spi-filter-card {
        padding: 22px 24px 10px;
    }
    .sales-pi-root .spi-section-head {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
        margin-bottom: 16px;
    }
    .sales-pi-root .spi-section-title {
        margin: 0;
        color: #183153;
        font-size: 16px;
        font-weight: 700;
    }
    .sales-pi-root .spi-section-note {
        margin: 4px 0 0;
        color: #75879b;
        font-size: 12px;
    }
    .sales-pi-root .spi-filter-grid .col-md-3,
    .sales-pi-root .spi-filter-grid .col-md-2 {
        margin-bottom: 14px;
    }
    .sales-pi-root .spi-filter-card .form-group label,
    .sales-pi-root .spi-filter-card .form-group .control-label,
    .sales-pi-root .spi-filter-card .form-group > label {
        display: inline-block;
        margin-bottom: 6px;
        color: #344a60;
        font-size: 12px;
        font-weight: 700;
    }
    .sales-pi-root .spi-filter-card .form-control {
        height: 42px;
        border-radius: 10px;
        border-color: #d7e1ec;
        box-shadow: none;
        padding: 10px 12px;
    }
    .sales-pi-root .spi-filter-card .form-control:focus {
        border-color: #1f7ae0;
        box-shadow: 0 0 0 3px rgba(31, 122, 224, 0.12);
    }
    .sales-pi-root .spi-filter-actions {
        display: flex;
        align-items: flex-end;
    }
    .sales-pi-root .spi-load-btn {
        width: 100%;
        height: 42px;
        border-radius: 10px;
        font-weight: 700;
    }
    .sales-pi-root .spi-table-card {
        padding: 20px 0 0;
    }
    .sales-pi-root .spi-table-toolbar {
        padding: 0 24px 16px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
        flex-wrap: wrap;
    }
    .sales-pi-root .spi-table-toolbar .spi-section-note {
        margin: 0;
    }
    .sales-pi-root .spi-table-scroll {
        display: block;
        width: 100%;
        max-width: 100%;
        overflow-x: auto;
        overflow-y: hidden;
        -webkit-overflow-scrolling: touch;
        border-top: 1px solid #e5ebf3;
        border-bottom: 1px solid #e5ebf3;
    }
    .sales-pi-root #salesItemTable {
        width: 100%;
        min-width: 1760px;
        margin-bottom: 0;
        background: #fff;
    }
    .sales-pi-root #salesItemTable th,
    .sales-pi-root #salesItemTable td {
        white-space: nowrap;
        font-size: 12px;
        padding: 9px 10px;
        vertical-align: middle;
        border-color: #e7edf4;
    }
    .sales-pi-root #salesItemTable thead th {
        background: #1f7ae0;
        color: #ffffff;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.03em;
    }
    .sales-pi-root #salesItemTable tbody tr:hover td {
        background: #f9fbfe;
    }
    .sales-pi-root .spi-pager-wrap {
        padding: 16px 24px 20px;
    }
    .sales-pi-root .spi-pager-well {
        margin: 0;
        border-radius: 12px;
        border: 1px solid #dde6f0;
        background: #f8fbff;
        max-width: 100%;
        overflow-wrap: anywhere;
    }
    @media (max-width: 991px) {
        .sales-pi-root .box-content {
            padding: 14px;
        }
        .sales-pi-root .spi-report-hero,
        .sales-pi-root .spi-kpis,
        .sales-pi-root .spi-filter-card,
        .sales-pi-root .spi-table-toolbar,
        .sales-pi-root .spi-pager-wrap {
            padding-left: 16px;
            padding-right: 16px;
        }
        .sales-pi-root .spi-report-hero {
            flex-direction: column;
        }
        .sales-pi-root .spi-kpis {
            grid-template-columns: 1fr;
        }
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
        <div class="spi-report-shell">
            <div class="spi-surface">
                <div class="spi-report-hero">
                    <div>
                        <h1 class="spi-report-title"><?= lang('Sales Per Item'); ?></h1>
                    </div>
                    <div class="spi-hero-actions">
                       <a href="<?= $spi_export_url ?>" class="tip" title="<?= lang('download'); ?> CSV (<?= lang('all'); ?>)" id="xls"><i class="icon fa fa-file-excel-o"></i></a>
                    </div>
                </div>
            </div>

            <div class="spi-surface spi-filter-card">
                <?php
                $attrib = ['data-toggle' => 'validator', 'role' => 'form', 'id' => 'searchForm', 'method' => 'get'];
                echo admin_form_open_multipart('reports/sales_per_item', $attrib)
                ?>
                <div class="row spi-filter-grid">
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
                <div class="row spi-filter-grid">
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
                        <div class="col-md-3 spi-filter-actions">
                            <div class="form-group" style="width:100%;">
                                <label>&nbsp;</label><br>
                                <button type="submit" class="btn btn-primary spi-load-btn" id="load_report"><i class="fa fa-search"></i> <?= lang('Load Report') ?></button>
                            </div>
                        </div>
                    </div>
                </div>
                <?php echo form_close(); ?>
            </div>

            <div class="spi-surface spi-table-card">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="table-responsive spi-table-scroll">
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
                            ?>
                        <div class="spi-pager-wrap">
                            <div class="well well-sm spi-pager-well" style="display:flex; align-items:center; flex-wrap:wrap; gap:10px;">
                                <span class="text-muted">Rows <?= (int) $spi_from ?>-<?= (int) $spi_to ?> of <?= (int) $spi_total_rows ?> · Page <?= (int) $spi_page_num ?> / <?= (int) $spi_total_pages ?></span>
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
                        </div>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
