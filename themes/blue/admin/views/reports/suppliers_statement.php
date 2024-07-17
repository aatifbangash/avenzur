<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<script>
    $(document).ready(function () {

    });
</script>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-users"></i><?= lang('supplier_statement'); ?></h2>

        <div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown"><a href="#" id="xls" class="tip" title="<?= lang('download_xls') ?>"><i
                                class="icon fa fa-file-excel-o"></i></a></li>
                <li class="dropdown"><a href="#" id="image" class="tip" title="<?= lang('save_image') ?>"><i
                                class="icon fa fa-file-picture-o"></i></a></li>
            </ul>
        </div>
    </div>
    <div class="box-content">
        <div class="row">
            <?php
            $attrib = ['data-toggle' => 'validator', 'role' => 'form'];
            echo admin_form_open_multipart('reports/supplier_statement', $attrib)
            ?>
            <div class="col-lg-12">
                <div class="row">
                    <div class="col-lg-12">

                        <div class="col-md-4">
                            <div class="form-group">
                                <?= lang('From Date', 'podate'); ?>
                                <?php echo form_input('from_date', ($start_date ?? ''), 'class="form-control input-tip date" id="fromdate"'); ?>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <?= lang('To Date', 'podate'); ?>
                                <?php echo form_input('to_date', ($end_date ?? ''), 'class="form-control input-tip date" id="todate"'); ?>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <?= lang('supplier', 'posupplier'); ?>
                                <?php
                                $sp[''] = '';
                                foreach ($suppliers as $supplier) {
                                    $sp[$supplier->id] = $supplier->company . ' (' . $supplier->name . ')';
                                }
                                echo form_dropdown('supplier', $sp, ($supplier_id ?? $supplier_id), 'id="supplier_id" class="form-control input-tip select" data-placeholder="' . lang('select') . ' ' . lang('supplier') . '" required="required" style="width:100%;" '); ?>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="from-group">
                                <button type="submit" style="margin-top: 28px;" class="btn btn-primary"
                                        id="load_report"><?= lang('Load Report') ?></button>
                            </div>
                        </div>

                    </div>
                </div>
                <hr/>
                <div class="row">
                    <div class="controls table-controls" style="font-size: 12px !important;">
                        <table id="poTable"
                               class="table items table-striped table-bordered table-condensed table-hover sortable_table">
                            <thead>
                            <tr>
                                <th>#</th>
                                <th><?= lang('type'); ?></th>
                                <th><?= lang('date'); ?></th>
                                <th><?= lang('Num'); ?></th>
                                <th><?= lang('name'); ?></th>
                                <th><?= lang('Memo'); ?></th>
                                <th><?= lang('Opening Debit'); ?></th>
                                <th><?= lang('Opening Credit'); ?></th>
                                <th><?= lang('Debit'); ?></th>
                                <th><?= lang('Credit'); ?></th>
                                <th><?= lang('balance'); ?></th>
                            </tr>
                            </thead>
                            <tbody style="text-align:center;">
                            <?php
                            $count = 0;
                            $totalBalance = 0;
                            $opening_debit = 0;
                            $opening_credit = 0;
                            $total_debit = 0;
                            $total_credit = 0;

                            foreach ($supplier_statement as $statement) {

                                // OB Calculation
                                if($count == 0){
                                    if($total_ob_debit > $total_ob_credit){
                                        $opening_debit = $total_ob_debit - $total_ob_credit;
                                        $opening_credit = 0;
                                    }else{
                                        $opening_debit = 0;
                                        $opening_credit = $total_ob_credit - $total_ob_debit;
                                    } 
                                }else{
                                    if($totalBalance > 0){
                                        $opening_credit = $totalBalance;
                                        $opening_debit = 0;
                                    }else if($totalBalance < 0){
                                        $opening_credit = 0;
                                        $opening_debit = $totalBalance;
                                    }else{
                                        $opening_credit = 0;
                                        $opening_debit = 0;
                                    }
                                }

                                if($statement->dc == 'D'){
                                    $total_debit = $opening_debit + $statement->amount;
                                    $total_credit = $opening_credit;
                                }
                                
                                if($statement->dc == 'C'){
                                    $total_credit = $opening_credit + $statement->amount;
                                    $total_debit = $opening_debit;
                                }
                                
                                if($total_debit > $total_credit){
                                    $totalBalance = $total_debit - $total_credit;
                                }else{
                                    $totalBalance = $total_credit - $total_debit; 
                                }

                                $count++;

                                

                                ?>
                                <tr>
                                    <td><?= $count; ?></td>
                                    <td><?= $statement->transaction_type; ?></td>
                                    <td><?= $statement->date; ?></td>
                                    <td><?= $statement->code; ?></td>
                                    <td><?= $statement->company; ?></td>
                                    <td><?= $statement->narration; ?></td>
                                    <td><?= $opening_debit; ?></td>
                                    <td><?= $opening_credit; ?></td>
                                    <td><?= $statement->dc == 'D' ? $statement->amount : '-'; ?></td>
                                    <td><?= $statement->dc == 'C' ? $statement->amount : '-'; ?></td>
                                    <td><?= $totalBalance; ?></td>
                                </tr>
                                <?php
                            }
                            ?>
                            <tr>
                                <th>&nbsp;</th>
                                <th>&nbsp;</th>
                                <th>&nbsp;</th>
                                <th>&nbsp;</th>
                                <th>&nbsp;</th>
                                <th>&nbsp;</th>
                                <th>&nbsp;</th>
                                <th>&nbsp;</th>
                                <th><?= $totalDebit; ?></th>
                                <th><?= $totalCredit; ?></th>
                                <th><?= $totalBalance; ?></th>
                            </tr>
                            </tbody>
                            <tfoot></tfoot>
                        </table>
                    </div>

                </div>

            </div>
        </div>
        <?php echo form_close(); ?>
    </div>
