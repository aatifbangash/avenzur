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

    });
</script>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-users"></i><?= lang('customer_statement'); ?></h2>

        <div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown">
                    <a href="javascript:void(0);" onclick="exportTableToExcel('poTable', 'Customer_Statement_Report.xlsx')" id="xls" class="tip" title="<?= lang('download_xls') ?>"><i class="icon fa fa-file-excel-o"></i></a>
                </li>
                
            </ul>
        </div>
    </div>
    <div class="box-content">
        <div class="row">
        <?php
            $attrib = ['data-toggle' => 'validator', 'role' => 'form'];
            echo admin_form_open_multipart('reports/customer_statement', $attrib)
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
                            <?= lang('customer', 'posupplier'); ?>
                            <?php
                            $selected_customer_id[] = isset($customer_id) ? $customer_id : '';
                            $sp[''] = '';
                            foreach ($suppliers as $supplier) {
                                $sp[$supplier->id] = $supplier->company. ' ('. $supplier->name.')';
                            }
                            echo form_dropdown('customer', $sp, $selected_customer_id, 'id="supplier_id" class="form-control input-tip select" data-placeholder="' . lang('select') . ' ' . lang('customer') . '" required="required" style="width:100%;" ', null); ?>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="from-group">
                                <button type="submit" style="margin-top: 28px;" class="btn btn-primary" id="load_report"><?= lang('Load Report') ?></button>
                            </div>
                        </div>
                            
                    </div>
                </div>
                <hr />
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
                                <th><?= lang('Opening Balance'); ?></th>
                                <th><?= lang('Debit'); ?></th>
                                <th><?= lang('Credit'); ?></th>
                                <th><?= lang('balance'); ?></th>
                            </tr>
                            </thead>
                            <tbody style="text-align:center;">
                                <?php
                                    $count = 0;
                                    $balance = $total_ob;

                                    $totalCredit = 0;
                                    $totalDebit = 0;
                                    $totalBalance = 0;
                                    $openingBalance = $total_ob;

                                    foreach($supplier_statement as $statement){
                                        
                                        if($statement->dc == 'C'){
                                            $balance =  $balance - $statement->amount;
                                        }else{
                                            $balance = $balance + $statement->amount;
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
                                                <td><?= $this->sma->formatNumber($openingBalance); ?></td>
                                                <td><?= $statement->dc == 'D' ? $this->sma->formatNumber($statement->amount) : '-';
                                                    $statement->dc == 'D' ? $totalDebit = ($totalDebit + $statement->amount) : null ?>

                                                </td>
                                                <td><?php echo $statement->dc == 'C' ? $this->sma->formatNumber($statement->amount) : '-';
                                                $statement->dc == 'C' ?
                                                    $totalCredit = $totalCredit + $statement->amount : null ?>

                                                </td>
                                                <td><?php echo $this->sma->formatNumber($balance);
                                                $totalBalance = $totalBalance + $balance;
                                                ?></td>
                                            </tr>
                                        <?php

                                        if ($statement->dc == 'D') {
                                            $openingBalance += $statement->amount;
                                        } else {
                                            $openingBalance -= $statement->amount;
                                        }

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
                                <th><?= $this->sma->formatNumber($totalDebit); ?></th>
                                <th><?= $this->sma->formatNumber($totalCredit); ?></th>
                                <th><?= $this->sma->formatNumber($balance); ?></th>
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
