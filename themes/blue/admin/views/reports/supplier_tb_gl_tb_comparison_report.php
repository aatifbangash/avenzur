<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-exchange"></i>Supplier TB vs GL TB Comparison</h2>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
                <?php
                $attrib = ['data-toggle' => 'validator', 'role' => 'form', 'id' => 'searchForm'];
                echo admin_form_open_multipart('reports/supplier_tb_gl_tb_comparison_report', $attrib);
                ?>
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <?= lang('From Date', 'podate'); ?>
                            <?= form_input('from_date', ($start_date ?? ''), 'class="form-control input-tip date" id="fromdate"'); ?>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <?= lang('To Date', 'podate'); ?>
                            <?= form_input('to_date', ($end_date ?? ''), 'class="form-control input-tip date" id="todate"'); ?>
                        </div>
                    </div>
                    <?php $this->load->view($this->theme . 'reports/partials/warehouse_filter_field', ['wh_col' => 'col-md-4']); ?>
                    <div class="col-md-4">
                        <div class="from-group">
                            <button type="submit" style="margin-top: 28px;" class="btn btn-primary" id="load_report"><?= lang('Load Report') ?></button>
                        </div>
                    </div>
                </div>
                <?php echo form_close(); ?>
                <hr />
            </div>
        </div>

        <?php if (!empty($comparison_result)): ?>
            <?php
            $summary = $comparison_result['summary'];
            $flagged_rows = $comparison_result['flagged_rows'];
            $supplier_differences = $comparison_result['supplier_differences'];
            $ledgers = implode(', ', $comparison_result['ledger_ids']);
            ?>

            <div class="alert alert-info">
                <strong>Ledgers compared:</strong> <?= htmlspecialchars($ledgers, ENT_QUOTES, 'UTF-8'); ?><br>
                <strong>GL (all lines) Debit/Credit:</strong>
                <?= number_format((float) $summary['gl_total_debit'], 2, '.', ','); ?> /
                <?= number_format((float) $summary['gl_total_credit'], 2, '.', ','); ?><br>
                <strong>GL lines included in Supplier TB logic Debit/Credit:</strong>
                <?= number_format((float) $summary['gl_in_tb_total_debit'], 2, '.', ','); ?> /
                <?= number_format((float) $summary['gl_in_tb_total_credit'], 2, '.', ','); ?><br>
                <strong>Supplier TB movement Debit/Credit:</strong>
                <?= number_format((float) $summary['supplier_tb_total_debit'], 2, '.', ','); ?> /
                <?= number_format((float) $summary['supplier_tb_total_credit'], 2, '.', ','); ?><br>
                <strong>Flagged GL rows for manual check:</strong> <?= (int) $summary['manual_flag_count']; ?><br>
                <strong>Suppliers with amount mismatch:</strong> <?= (int) $summary['supplier_mismatch_count']; ?>
            </div>

            <h3>1) GL Lines Missing from Supplier TB Logic (Manual Check)</h3>
            <div class="table-responsive">
                <table class="table table-striped table-bordered table-condensed table-hover">
                    <thead>
                        <tr>
                            <th>Entry</th>
                            <th>Date</th>
                            <th>Reference</th>
                            <th>Type</th>
                            <th>Supplier</th>
                            <th>Ledger</th>
                            <th>D/C</th>
                            <th>Amount</th>
                            <th>Reason</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($flagged_rows)): ?>
                            <?php foreach ($flagged_rows as $row): ?>
                                <tr>
                                    <td><?= (int) $row['entry_id']; ?></td>
                                    <td><?= htmlspecialchars($row['date'], ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><?= htmlspecialchars($row['reference_no'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><?= htmlspecialchars($row['transaction_type'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td>
                                        <?= htmlspecialchars(($row['supplier_code'] ?? '') . ' ' . ($row['supplier_name'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>
                                        <?= !empty($row['supplier_id']) ? ' (#' . (int) $row['supplier_id'] . ')' : ''; ?>
                                    </td>
                                    <td>
                                        <?= htmlspecialchars($row['ledger_code'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                                        (<?= (int) $row['ledger_id']; ?>)
                                        <?= htmlspecialchars($row['ledger_name'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                                    </td>
                                    <td><?= htmlspecialchars($row['dc'], ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td class="text-right"><?= number_format((float) $row['amount'], 2, '.', ','); ?></td>
                                    <td><?= htmlspecialchars($row['manual_check_reason'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="9" class="text-center">No flagged rows found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <h3>2) Supplier Movement Comparison (GL Included vs Supplier TB)</h3>
            <div class="table-responsive">
                <table class="table table-striped table-bordered table-condensed table-hover">
                    <thead>
                        <tr>
                            <th>Supplier</th>
                            <th>GL Trs Debit</th>
                            <th>Supplier TB Trs Debit</th>
                            <th>Diff Debit</th>
                            <th>GL Trs Credit</th>
                            <th>Supplier TB Trs Credit</th>
                            <th>Diff Credit</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($supplier_differences)): ?>
                            <?php foreach ($supplier_differences as $row): ?>
                                <tr>
                                    <td>
                                        <?= htmlspecialchars(($row['sequence_code'] ?? '') . ' ' . ($row['name'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>
                                        (#<?= (int) $row['supplier_id']; ?>)
                                    </td>
                                    <td class="text-right"><?= number_format((float) $row['gl_trs_debit'], 2, '.', ','); ?></td>
                                    <td class="text-right"><?= number_format((float) $row['supplier_tb_trs_debit'], 2, '.', ','); ?></td>
                                    <td class="text-right"><?= number_format((float) $row['diff_debit'], 2, '.', ','); ?></td>
                                    <td class="text-right"><?= number_format((float) $row['gl_trs_credit'], 2, '.', ','); ?></td>
                                    <td class="text-right"><?= number_format((float) $row['supplier_tb_trs_credit'], 2, '.', ','); ?></td>
                                    <td class="text-right"><?= number_format((float) $row['diff_credit'], 2, '.', ','); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center">No supplier-level amount differences found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="alert alert-warning">
                Select date range and load report to compare supplier trial balance against GL ledger lines (62, 63).
            </div>
        <?php endif; ?>
    </div>
</div>
