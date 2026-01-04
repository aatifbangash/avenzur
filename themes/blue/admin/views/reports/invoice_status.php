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
    $(document).ready(function() {
        // No need for manual Select2 value setting - GET method automatically persists values
    });
</script>

<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-file-text"></i><?= lang('invoice_status_report'); ?></h2>
        <div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown">
                    <a href="javascript:void(0);" onclick="exportTableToExcel('invoiceTable', 'invoice_status.xlsx')" id="xls" class="tip" title="<?= lang('download_xls') ?>"><i class="icon fa fa-file-excel-o"></i></a>
                </li>
            </ul>
        </div>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
                <?php
                $attrib = ['data-toggle' => 'validator', 'role' => 'form', 'id' => 'searchForm', 'method' => 'get'];
                echo admin_form_open_multipart('reports/invoice_status', $attrib)
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

                        <div class="col-md-3">
                            <div class="form-group">
                                <?= lang('invoice', 'invoice_id'); ?>
                                <?php echo form_input('invoice_id', ($_GET['invoice_id'] ?? ''), 'class="form-control" id="invoice_id" placeholder="' . lang('invoice') . '"'); ?>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="form-group">
                                <?= lang('customer', 'customer'); ?>
                                <?php echo form_input('customer', ($_GET['customer'] ?? ''), 'class="form-control" id="customer" placeholder="' . lang('customer') . '"'); ?>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="form-group">
                                <?= lang('sales_man', 'salesman'); ?>
                                <?php
                                $sm[''] = lang('select') . ' ' . lang('sales_man');
                                foreach ($salesmen as $salesman_item) {
                                    $sm[$salesman_item->id] = $salesman_item->name;
                                }
                                echo form_dropdown('salesman', $sm, ($_GET['salesman'] ?? ''), 'id="salesman" class="form-control input-tip select" data-placeholder="' . lang('select') . ' ' . lang('sales_man') . '" style="width:100%;"');
                                ?>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="form-group">
                                <?= lang('warehouse', 'warehouse'); ?>
                                <?php
                                $wh[''] = lang('select') . ' ' . lang('warehouse');
                                foreach ($warehouses as $warehouse_item) {
                                    $wh[$warehouse_item->id] = $warehouse_item->name;
                                }
                                echo form_dropdown('warehouse', $wh, ($_GET['warehouse'] ?? ''), 'id="warehouse" class="form-control input-tip select" data-placeholder="' . lang('select') . ' ' . lang('warehouse') . '" style="width:100%;"');
                                ?>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="from-group">
                                <button type="submit" style="margin-top: 28px;" class="btn btn-primary" id="load_report"><?= lang('Load Report') ?></button>
                            </div>
                        </div>
                    </div>
                </div>
                <?php echo form_close(); ?>
                <hr />
                <div class="row">
                    <div class="controls table-controls" style="font-size: 12px !important;">
                        <table id="invoiceTable" class="table items table-striped table-bordered table-condensed table-hover sortable_table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th><?= lang('date'); ?></th>
                                    <th><?= lang('invoice'); ?></th>
                                    <th><?= lang('area'); ?></th>
                                    <th><?= lang('sales_man'); ?></th>
                                    <th><?= lang('customer_no'); ?></th>
                                    <th><?= lang('customer_name'); ?></th>
                                    <th><?= lang('invoice'); ?></th>
                                    <th><?= lang('return'); ?></th>
                                    <th><?= lang('discount'); ?></th>
                                    <th><?= lang('paid'); ?></th>
                                    <th><?= lang('outstanding'); ?></th>
                                </tr>
                            </thead>
                            <tbody style="text-align:center;">
                                <?php
                                if (isset($invoice_data) && !empty($invoice_data)) {
                                    $count = 0;
                                    $grand_invoice_total = 0;
                                    $grand_return = 0;
                                    $grand_discount = 0;
                                    $grand_paid = 0;
                                    $grand_outstanding = 0;

                                    foreach ($invoice_data as $data) {
                                        $count++;
                                        $grand_invoice_total += $data->invoice_total;
                                        $grand_return += $data->return_amount;
                                        $grand_discount += $data->discount;
                                        $grand_paid += $data->paid;
                                        $grand_outstanding += $data->outstanding;
                                ?>
                                        <tr>
                                            <td><?= $count; ?></td>
                                            <td><?= $data->date; ?></td>
                                            <td><?= $data->invoice; ?></td>
                                            <td><?= $data->area; ?></td>
                                            <td><?= $data->sales_man; ?></td>
                                            <td><?= $data->customer_no; ?></td>
                                            <td><?= $data->customer_name; ?></td>
                                            <td><?= $this->sma->formatMoney($data->invoice_total); ?></td>
                                            <td><?= $this->sma->formatMoney($data->return_amount); ?></td>
                                            <td><?= $this->sma->formatMoney($data->discount); ?></td>
                                            <td><?= $this->sma->formatMoney($data->paid); ?></td>
                                            <td><?= $this->sma->formatMoney($data->outstanding); ?></td>
                                        </tr>
                                <?php
                                    }
                                ?>
                                    <tr style="font-weight: bold; background-color: #f0f0f0;">
                                        <td colspan="7" style="text-align: right;"><?= lang('total'); ?></td>
                                        <td><?= $this->sma->formatMoney($grand_invoice_total); ?></td>
                                        <td><?= $this->sma->formatMoney($grand_return); ?></td>
                                        <td><?= $this->sma->formatMoney($grand_discount); ?></td>
                                        <td><?= $this->sma->formatMoney($grand_paid); ?></td>
                                        <td><?= $this->sma->formatMoney($grand_outstanding); ?></td>
                                    </tr>
                                <?php
                                } else {
                                    echo '<tr><td colspan="12" class="text-center">' . lang('no_data_available') . '</td></tr>';
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
