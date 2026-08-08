<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.17.3/xlsx.full.min.js"></script>
<script>
    function exportTableToExcel(tableId, filename) {
        const table = document.getElementById(tableId);
        const wb = XLSX.utils.table_to_book(table, { sheet: 'Sheet 1' });
        XLSX.writeFile(wb, filename);
    }
    function generatePDF() {
        $('.viewtype').val('pdf');
        document.getElementById('searchForm').submit();
        $('.viewtype').val('');
    }
    $(document).ready(function () {
        $('#poTable tbody tr.advance-row').on('click', function () {
            var href = $(this).data('href');
            if (href) {
                window.location.href = href;
            }
        });
    });
</script>
<?php if (!empty($viewtype) && $viewtype == 'pdf') { ?>
<link href="<?= $assets ?>styles/pdf/pdf.css" rel="stylesheet">
<?php } ?>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-hand-o-up"></i><?= lang('customer_advances_report'); ?></h2>
        <?php if (empty($viewtype) || $viewtype != 'pdf') { ?>
        <div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown">
                    <a href="javascript:void(0);" onclick="exportTableToExcel('poTable', 'Customer_Advances_Balance.xlsx')" class="tip" title="<?= lang('download_xls') ?>">
                        <i class="icon fa fa-file-excel-o"></i>
                    </a>
                </li>
                <li class="dropdown">
                    <a href="javascript:void(0);" onclick="generatePDF()" class="tip" title="<?= lang('download_PDF') ?>">
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
                <?php if (empty($viewtype) || $viewtype != 'pdf') { ?>
                <?php
                $attrib = ['data-toggle' => 'validator', 'role' => 'form', 'id' => 'searchForm'];
                echo admin_form_open_multipart('reports/customer_advances', $attrib);
                ?>
                <input type="hidden" name="viewtype" class="viewtype" value="">
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <?= lang('From Date', 'fromdate'); ?>
                            <?php echo form_input('from_date', ($start_date ?? ''), 'class="form-control input-tip date" id="fromdate"'); ?>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <?= lang('To Date', 'todate'); ?>
                            <?php echo form_input('to_date', ($end_date ?? ''), 'class="form-control input-tip date" id="todate"'); ?>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <?= lang('Customers', 'customer_ids'); ?>
                            <select name="customer_ids[]" id="customer_ids" class="form-control select2" multiple="multiple" data-placeholder="<?= lang('select') . ' ' . lang('customer'); ?>" style="width:100%;">
                                <?php foreach ($customers as $customer): ?>
                                    <option value="<?= $customer->id; ?>" <?= (isset($selected_customers) && in_array($customer->id, $selected_customers)) ? 'selected' : ''; ?>>
                                        <?= ($customer->company ?? $customer->name) . ' - ' . $customer->sequence_code; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <button type="submit" style="margin-top: 28px;" class="btn btn-primary"><?= lang('Load Report') ?></button>
                    </div>
                </div>
                <hr>
                <?php echo form_close(); ?>
                <?php } ?>

                <?php if (empty($advance_ledger)): ?>
                <div class="alert alert-warning"><?= lang('customer_advance_ledger_not_configured'); ?></div>
                <?php endif; ?>

                <div class="row">
                    <div class="controls table-controls" style="font-size: 12px !important;">
                        <table id="poTable" class="table items table-striped table-bordered table-condensed table-hover sortable_table">
                            <thead>
                            <tr>
                                <th>#</th>
                                <th><?= lang('Code'); ?></th>
                                <th><?= lang('name'); ?></th>
                                <th><?= lang('OB Debit'); ?></th>
                                <th><?= lang('OB Credit'); ?></th>
                                <th><?= lang('Trs Debit'); ?></th>
                                <th><?= lang('Trs Credit'); ?></th>
                                <th><?= lang('EB Debit'); ?></th>
                                <th><?= lang('EB Credit'); ?></th>
                            </tr>
                            </thead>
                            <tbody style="text-align:center;">
                            <?php
                            $count = 0;
                            $totalObDebit = $totalObCredit = $totalTrsDebit = $totalTrsCredit = 0;
                            $totalFinalEndDebit = $totalFinalEndCredit = 0;
                            foreach (($trial_balance ?? []) as $data) {
                                if ($data['trsDebit'] == 0 && $data['trsCredit'] == 0 && $data['obDebit'] == 0 && $data['obCredit'] == 0) {
                                    continue;
                                }
                                $eb_credit = $data['obCredit'] + $data['trsCredit'];
                                $eb_debit = $data['obDebit'] + $data['trsDebit'];
                                $finalEndDebit = 0;
                                $finalEndCredit = 0;
                                if ($eb_credit >= $eb_debit) {
                                    $finalEndCredit = $eb_credit - $eb_debit;
                                } else {
                                    $finalEndDebit = $eb_debit - $eb_credit;
                                }

                                $totalObDebit += $data['obDebit'];
                                $totalObCredit += $data['obCredit'];
                                $totalTrsDebit += $data['trsDebit'];
                                $totalTrsCredit += $data['trsCredit'];
                                $totalFinalEndDebit += $finalEndDebit;
                                $totalFinalEndCredit += $finalEndCredit;

                                $detail_url = admin_url('reports/customer_advance_statement')
                                    . '?customer=' . (int) $data['party_id']
                                    . '&from_date=' . urlencode($start_date ?? '')
                                    . '&to_date=' . urlencode($end_date ?? '');
                                $count++;
                                ?>
                                <tr class="advance-row" style="cursor:pointer;" data-href="<?= $detail_url; ?>" title="<?= lang('click_to_view_history'); ?>">
                                    <td><?= $count; ?></td>
                                    <td><?= $data['sequence_code']; ?></td>
                                    <td><?= $data['name']; ?></td>
                                    <td><?= $data['obDebit'] > 0 ? number_format($data['obDebit'], 2, '.', ',') : '0.00'; ?></td>
                                    <td><?= $data['obCredit'] > 0 ? number_format($data['obCredit'], 2, '.', ',') : '0.00'; ?></td>
                                    <td><?= $data['trsDebit'] > 0 ? number_format($data['trsDebit'], 2, '.', ',') : '0.00'; ?></td>
                                    <td><?= $data['trsCredit'] > 0 ? number_format($data['trsCredit'], 2, '.', ',') : '0.00'; ?></td>
                                    <td><?= $finalEndDebit > 0 ? number_format($finalEndDebit, 2, '.', ',') : '0.00'; ?></td>
                                    <td><?= $finalEndCredit > 0 ? number_format($finalEndCredit, 2, '.', ',') : '0.00'; ?></td>
                                </tr>
                                <?php
                            }
                            ?>
                            </tbody>
                            <tfoot>
                            <tr>
                                <th>&nbsp;</th>
                                <th>&nbsp;</th>
                                <th>&nbsp;</th>
                                <th class="text-center"><?= number_format($totalObDebit, 2, '.', ','); ?></th>
                                <th class="text-center"><?= number_format($totalObCredit, 2, '.', ','); ?></th>
                                <th class="text-center"><?= number_format($totalTrsDebit, 2, '.', ','); ?></th>
                                <th class="text-center"><?= number_format($totalTrsCredit, 2, '.', ','); ?></th>
                                <th class="text-center"><?= number_format($totalFinalEndDebit, 2, '.', ','); ?></th>
                                <th class="text-center"><?= number_format($totalFinalEndCredit, 2, '.', ','); ?></th>
                            </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
