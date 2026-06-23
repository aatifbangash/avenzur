<?php defined('BASEPATH') or exit('No direct script access allowed');
$is_supplier = ($balance_type ?? '') === 'supplier';
$party_id = $is_supplier ? ($supplier_id ?? null) : ($customer_id ?? null);
$party_list = $is_supplier ? ($suppliers ?? []) : ($customers ?? []);
$report_title = $is_supplier ? lang('supplier_advance_statement') : lang('customer_advance_statement');
$back_url = $is_supplier ? admin_url('reports/supplier_advances') : admin_url('reports/customer_advances');
$form_action = $is_supplier ? 'reports/supplier_advance_statement' : 'reports/customer_advance_statement';
$party_field = $is_supplier ? 'supplier' : 'customer';
?>
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
</script>
<?php if (!empty($viewtype) && $viewtype == 'pdf') { ?>
<link href="<?= $assets ?>styles/pdf/pdf.css" rel="stylesheet">
<?php } ?>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-list-alt"></i><?= $report_title; ?></h2>
        <?php if (empty($viewtype) || $viewtype != 'pdf') { ?>
        <div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown">
                    <a href="<?= $back_url; ?>" class="tip" title="<?= lang('back'); ?>">
                        <i class="icon fa fa-arrow-left"></i>
                    </a>
                </li>
                <li class="dropdown">
                    <a href="javascript:void(0);" onclick="exportTableToExcel('poTable', 'Advance_Statement.xlsx')" class="tip" title="<?= lang('download_xls') ?>">
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
                echo admin_form_open_multipart($form_action, $attrib);
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
                            <?= $is_supplier ? lang('supplier') : lang('customer'); ?>
                            <?php
                            $opts = ['' => ''];
                            foreach ($party_list as $party) {
                                $opts[$party->id] = ($party->company ?? $party->name) . ' - ' . $party->sequence_code;
                            }
                            echo form_dropdown($party_field, $opts, $party_id, 'class="form-control select" required="required" style="width:100%;"');
                            ?>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <button type="submit" style="margin-top: 28px;" class="btn btn-primary"><?= lang('Load Report') ?></button>
                    </div>
                </div>
                <hr>
                <?php echo form_close(); ?>
                <?php } ?>

                <div class="row">
                    <div class="controls table-controls" style="font-size: 12px !important;">
                        <table id="poTable" class="table items table-striped table-bordered table-condensed table-hover sortable_table">
                            <thead>
                            <tr style="text-align:center;">
                                <?php if (empty($viewtype) || $viewtype != 'pdf') { ?><th>#</th><?php } ?>
                                <th><?= lang('Reference No'); ?></th>
                                <th><?= lang('type'); ?></th>
                                <th><?= lang('date'); ?></th>
                                <th><?= lang('Description'); ?></th>
                                <th><?= lang('Debit'); ?></th>
                                <th><?= lang('Credit'); ?></th>
                                <th><?= lang('balance'); ?></th>
                            </tr>
                            </thead>
                            <tbody style="text-align:center;">
                            <tr>
                                <?php if (empty($viewtype) || $viewtype != 'pdf') { ?>
                                <td colspan="2">Opening Balance</td>
                                <td colspan="4">&nbsp;</td>
                                <?php } else { ?>
                                <td colspan="2">Opening Balance</td>
                                <td colspan="3">&nbsp;</td>
                                <?php } ?>
                                <td style="text-align:right;">
                                    <?php
                                    $ob = (float) ($total_ob ?? 0);
                                    if ($ob >= 0) {
                                        echo number_format($ob, 2, '.', ',');
                                    } else {
                                        echo '<span style="color:red;">-' . number_format(abs($ob), 2, '.', ',') . '</span>';
                                    }
                                    ?>
                                </td>
                            </tr>
                            <?php
                            $count = 0;
                            $balance = (float) ($total_ob ?? 0);
                            $totalDebit = 0;
                            $totalCredit = 0;
                            foreach (($advance_statement ?? []) as $statement) {
                                if ($is_supplier) {
                                    if ($statement->dc == 'D') {
                                        $balance += $statement->amount;
                                    } else {
                                        $balance -= $statement->amount;
                                    }
                                } else {
                                    if ($statement->dc == 'C') {
                                        $balance += $statement->amount;
                                    } else {
                                        $balance -= $statement->amount;
                                    }
                                }

                                $count++;
                                $transaction_type = ucfirst($statement->transaction_type ?? '');
                                $link = null;
                                if ($statement->transaction_type == 'journal' || $statement->transaction_type == 'payment' || $statement->transaction_type == 'receipt' || $statement->transaction_type == 'contra') {
                                    $link = admin_url('entries/view/' . $statement->transaction_type . '/' . $statement->entry_id);
                                    $transaction_type = ucfirst($statement->transaction_type);
                                } elseif ($statement->transaction_type == 'supplieradvance') {
                                    $transaction_type = lang('supplier_advance');
                                    if (!empty($statement->memo_id)) {
                                        $link = admin_url('suppliers/edit_advance_to_supplier/' . $statement->memo_id);
                                    } elseif (!empty($statement->entry_id)) {
                                        $link = admin_url('entries/view/payment/' . $statement->entry_id);
                                    }
                                } elseif ($statement->transaction_type == 'customeradvance') {
                                    $transaction_type = lang('customer_advance');
                                    if (!empty($statement->entry_id)) {
                                        $link = admin_url('entries/view/payment/' . $statement->entry_id);
                                    }
                                } elseif ($statement->transaction_type == 'advancesettlement') {
                                    $transaction_type = 'Advance Settlement';
                                    if (!empty($statement->entry_id)) {
                                        $link = admin_url('entries/view/payment/' . $statement->entry_id);
                                    }
                                } elseif ($statement->transaction_type == 'supplierpayment') {
                                    $transaction_type = lang('payment');
                                    if (!empty($statement->payment_reference)) {
                                        $link = admin_url('suppliers/view_payment/' . $statement->payment_reference);
                                    } elseif (!empty($statement->entry_id)) {
                                        $link = admin_url('entries/view/payment/' . $statement->entry_id);
                                    }
                                } elseif ($statement->transaction_type == 'customerpayment') {
                                    $transaction_type = lang('payment');
                                    if (!empty($statement->entry_id)) {
                                        $link = admin_url('entries/view/receipt/' . $statement->entry_id);
                                    }
                                } elseif (!empty($statement->entry_id)) {
                                    $link = admin_url('entries/view/payment/' . $statement->entry_id);
                                }
                                ?>
                                <tr>
                                    <?php if (empty($viewtype) || $viewtype != 'pdf') { ?><td><?= $count; ?></td><?php } ?>
                                    <td><?= !empty($statement->reference_no) ? $statement->reference_no : '-'; ?></td>
                                    <td>
                                        <?php if ($link && (empty($viewtype) || $viewtype != 'pdf')): ?>
                                            <a target="_blank" href="<?= $link; ?>"><?= $transaction_type; ?></a>
                                        <?php else: ?>
                                            <?= $transaction_type; ?>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= $this->sma->hrsd($statement->date); ?></td>
                                    <td><?= !empty($statement->narration) ? $statement->narration : '-'; ?></td>
                                    <td style="text-align:right;">
                                        <?= $statement->dc == 'D' ? number_format($statement->amount, 2, '.', ',') : '0.00'; ?>
                                        <?php if ($statement->dc == 'D') { $totalDebit += $statement->amount; } ?>
                                    </td>
                                    <td style="text-align:right;">
                                        <?= $statement->dc == 'C' ? number_format($statement->amount, 2, '.', ',') : '0.00'; ?>
                                        <?php if ($statement->dc == 'C') { $totalCredit += $statement->amount; } ?>
                                    </td>
                                    <td style="text-align:right;">
                                        <?php
                                        if ($balance >= 0) {
                                            echo number_format($balance, 2, '.', ',');
                                        } else {
                                            echo '<span style="color:red;">-' . number_format(abs($balance), 2, '.', ',') . '</span>';
                                        }
                                        ?>
                                    </td>
                                </tr>
                                <?php
                            }
                            ?>
                            <tr>
                                <?php if (empty($viewtype) || $viewtype != 'pdf') { ?><th>&nbsp;</th><?php } ?>
                                <th>&nbsp;</th>
                                <th>&nbsp;</th>
                                <th>&nbsp;</th>
                                <th>&nbsp;</th>
                                <th style="text-align:right;"><?= number_format($totalDebit, 2, '.', ','); ?></th>
                                <th style="text-align:right;"><?= number_format($totalCredit, 2, '.', ','); ?></th>
                                <th style="text-align:right;">
                                    <?php
                                    if ($balance >= 0) {
                                        echo number_format($balance, 2, '.', ',');
                                    } else {
                                        echo '<span style="color:red;">-' . number_format(abs($balance), 2, '.', ',') . '</span>';
                                    }
                                    ?>
                                </th>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
