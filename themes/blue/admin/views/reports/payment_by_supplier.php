<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.17.3/xlsx.full.min.js"></script>
<script>
    function exportTableToExcel(tableId, filename) {
        const table = document.getElementById(tableId);
        const wb = XLSX.utils.table_to_book(table, { sheet: 'Sheet 1' });
        XLSX.writeFile(wb, filename || 'supplier_payments.xlsx');
    }
    function generatePDF() {
        $('.viewtype').val('pdf');
        document.getElementById("searchForm").submit();
        $('.viewtype').val('');
    }
</script>
<?php if (isset($viewtype) && $viewtype == 'pdf') { ?>
    <link href="<?= $assets ?>styles/pdf/pdf.css" rel="stylesheet">
<?php } ?>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-truck"></i><?= lang('Payments by Supplier'); ?></h2>
        <?php if (!isset($viewtype) || $viewtype != 'pdf') { ?>
        <div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown">
                    <a href="javascript:void(0);" onclick="exportTableToExcel('paymentsTable', 'supplier_payments.xlsx')" id="xls" class="tip" title="<?= lang('download_xls') ?>">
                        <i class="icon fa fa-file-excel-o"></i>
                    </a>
                </li>
                <li class="dropdown">
                    <a href="javascript:void(0);" onclick="generatePDF()" id="pdf" class="tip" title="<?= lang('download_PDF') ?>">
                        <i class="icon fa fa-file-pdf-o"></i>
                    </a>
                </li>
            </ul>
        </div>
        <?php } ?>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
                <?php
                if (!isset($viewtype) || $viewtype != 'pdf') {
                    $attrib = ['data-toggle' => 'validator', 'role' => 'form', 'id' => 'searchForm'];
                    echo admin_form_open_multipart('reports/payments_by_supplier', $attrib);
                ?>
                <input type="hidden" name="viewtype" id="viewtype" class="viewtype" value="">
                <div class="row">
                    <div class="col-lg-12">

                        <div class="col-md-3">
                            <div class="form-group">
                                <?= lang('From Date', 'from_date'); ?>
                                <?php echo form_input('from_date', isset($start_date) ? $start_date : '', 'class="form-control input-tip date" id="fromdate"'); ?>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="form-group">
                                <?= lang('To Date', 'to_date'); ?>
                                <?php echo form_input('to_date', isset($end_date) ? $end_date : '', 'class="form-control input-tip date" id="todate"'); ?>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="form-group">
                                <?= lang('Supplier', 'supplier'); ?>
                                <?php
                                $selected_supplier = isset($supplier) ? $supplier : '';
                                $sup_dp = ['' => '-- ' . lang('All Suppliers') . ' --'];
                                foreach ($suppliers as $s) {
                                    $sup_dp[$s->id] = $s->company . (isset($s->name) && $s->name ? ' (' . $s->name . ')' : '');
                                }
                                echo form_dropdown('supplier', $sup_dp, $selected_supplier, 'id="supplier_id" class="form-control input-tip select" data-placeholder="' . lang('select') . ' ' . lang('supplier') . '" style="width:100%;"');
                                ?>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="form-group">
                                <?= lang('Pharmacy', 'pharmacy'); ?>
                                <?php
                                $selected_warehouse = isset($warehouse) ? $warehouse : '';
                                $wh_dp = ['' => '-- ' . lang('All Pharmacies') . ' --'];
                                foreach ($warehouses as $wh) {
                                    $wh_dp[$wh->id] = $wh->name;
                                }
                                echo form_dropdown('pharmacy', $wh_dp, $selected_warehouse, 'id="warehouse_id" class="form-control input-tip select" data-placeholder="' . lang('select') . ' ' . lang('pharmacy') . '" style="width:100%;"');
                                ?>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="form-group">
                                <button type="submit" style="margin-top: 28px;" class="btn btn-primary" id="load_report"><?= lang('Load Report') ?></button>
                            </div>
                        </div>

                    </div>
                </div>
                <?php echo form_close(); } ?>

                <hr />

                <div class="row">
                    <div class="controls table-controls" style="font-size: 12px !important;">
                        <table id="paymentsTable"
                               class="table items table-striped table-bordered table-condensed table-hover sortable_table tbl_pdf">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th><?= lang('Date'); ?></th>
                                    <th><?= lang('Payments'); ?></th>
                                    <th><?= lang('Total Amount'); ?></th>
                                    <th><?= lang('Bank Charges'); ?></th>
                                    <th><?= lang('Bank Charge VAT'); ?></th>
                                    <th><?= lang('Net Amount'); ?></th>
                                </tr>
                            </thead>
                            <tbody style="text-align:center;">
                                <?php
                                $count                    = 0;
                                $grand_total_amount       = 0;
                                $grand_bank_charges       = 0;
                                $grand_bank_charge_vat    = 0;
                                $grand_net_amount         = 0;
                                $grand_payment_count      = 0;

                                if (isset($payments_data) && !empty($payments_data)) {
                                    foreach ($payments_data as $row) {
                                        $count++;
                                        $grand_total_amount    += $row->total_amount;
                                        $grand_bank_charges    += $row->total_bank_charges;
                                        $grand_bank_charge_vat += $row->total_bank_charge_vat;
                                        $grand_net_amount      += $row->net_amount;
                                        $grand_payment_count   += $row->payment_count;
                                ?>
                                <tr>
                                    <td><?= $count; ?></td>
                                    <td><?= $row->transaction_date; ?></td>
                                    <td><?= $row->payment_count; ?></td>
                                    <td><?= $this->sma->formatNumber($row->total_amount); ?></td>
                                    <td><?= $this->sma->formatNumber($row->total_bank_charges); ?></td>
                                    <td><?= $this->sma->formatNumber($row->total_bank_charge_vat); ?></td>
                                    <td><?= $this->sma->formatNumber($row->net_amount); ?></td>
                                </tr>
                                <?php
                                    }
                                } ?>
                                <tr>
                                    <td colspan="2"><strong><?= lang('Totals'); ?></strong></td>
                                    <td><strong><?= $grand_payment_count; ?></strong></td>
                                    <td><strong><?= $this->sma->formatNumber($grand_total_amount); ?></strong></td>
                                    <td><strong><?= $this->sma->formatNumber($grand_bank_charges); ?></strong></td>
                                    <td><strong><?= $this->sma->formatNumber($grand_bank_charge_vat); ?></strong></td>
                                    <td><strong><?= $this->sma->formatNumber($grand_net_amount); ?></strong></td>
                                </tr>
                            </tbody>
                            <tfoot></tfoot>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
