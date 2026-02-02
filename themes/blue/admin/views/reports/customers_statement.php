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
    function generatePDF(){
       $('.viewtype').val('pdf');  
       document.getElementById("searchForm").submit();
       $('.viewtype').val(''); 
    } 
    $(document).ready(function() {

    });
</script>
<?php if($viewtype=='pdf'){ ?>
    <link href="<?= $assets ?>styles/pdf/pdf.css" rel="stylesheet"> 
  <?php  } ?>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-users"></i><?= lang('customer_statement'); ?></h2>
        <?php  if($viewtype!='pdf'){?>
        <div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown">
                    <a href="javascript:void(0);" onclick="exportTableToExcel('poTable', 'Customer_Statement_Report.xlsx')" id="xls" class="tip" title="<?= lang('download_xls') ?>"><i class="icon fa fa-file-excel-o"></i></a>
                </li> 
                <li class="dropdown"> <a href="javascript:void(0);" onclick="generatePDF()" id="pdf" class="tip" title="<?= lang('download_PDF') ?>"><i
                class="icon fa fa-file-pdf-o"></i></a></li>
            </ul>
        </div>
        <?php } ?>
    </div>
    <div class="box-content">
        <div class="row"> 
            <div class="col-lg-12">
                <?php
                if($viewtype!='pdf')
                {
                    $attrib = ['data-toggle' => 'validator', 'role' => 'form','id' => 'searchForm'];
                    echo admin_form_open_multipart('reports/customer_statement', $attrib)
                    ?>
                <input type="hidden" name="viewtype" id="viewtype" class="viewtype" value="" > 
       
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
                            foreach ($customers as $customer) {
                                $sp[$customer->id] = $customer->company. ' ('. $customer->name.')'.' - '.$customer->sequence_code;
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
                <?php echo form_close(); 
                } ?>
                <hr />
                <div class="row">
                    <div class="controls table-controls" style="font-size: 12px !important;">
                        <table id="poTable"
                                class="table items table-striped table-bordered table-condensed table-hover sortable_table tbl_pdf">
                            <thead>
                            <tr>
                                <th>#</th>
                                <th><?= lang('type'); ?></th>
                                <th><?= lang('date'); ?></th>
                                <th><?= lang('Num'); ?></th>
                                <th><?= lang('name'); ?></th>
                                <th><?= lang('Memo'); ?></th>
                               
                                <th><?= lang('Debit'); ?></th>
                                <th><?= lang('Credit'); ?></th>
                                <th><?= lang('balance'); ?></th>
                            </tr>
                            </thead>
                            <tbody style="text-align:center;">
                                <tr>
                                    <td colspan="2">Opening Balance<td>
                                    <td colspan="5">&nbsp;</td>
                                    <td><?= $this->sma->formatNumber($total_ob); ?></td>
                                </tr>
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

                                        if($statement->transaction_type == 'sales_invoice' || $statement->transaction_type == 'saleorder'){
                                            $link = admin_url('sales?sid=' . $statement->sale_id);
                                        }else if($statement->transaction_type == 'customerpayment'){
                                            $link = admin_url('sales/view_payment/' . $statement->payment_id);
                                        }else if($statement->transaction_type == 'creditmemo'){
                                            $link = admin_url('customers/view_credit_memo/' . $statement->memo_id);
                                        }

                                        ?>
                                            <tr>
                                                <td><?= $count; ?></td>
                                                <td><a target="_blank" href="<?= $link; ?>"><?= $statement->transaction_type; ?></a></td>
                                                <td><?= $statement->date; ?></td>
                                                <td><?= $statement->code; ?></td>
                                                <td><?= $statement->company; ?></td>
                                                <td><?= $statement->narration; ?></td>
                                                
                                                <td><?= $statement->dc == 'D' ? number_format($statement->amount, 2, '.', ',') : '0.00';
                                                    $statement->dc == 'D' ? $totalDebit = ($totalDebit + $statement->amount) : null ?>

                                                </td>
                                                <td><?php echo $statement->dc == 'C' ? number_format($statement->amount, 2, '.', ',') : '0.00';
                                                $statement->dc == 'C' ?
                                                    $totalCredit = $totalCredit + $statement->amount : null ?>

                                                </td>
                                                <td><?php 
                                                    if($balance >= 0){
                                                        echo number_format($balance, 2, '.', ','); 
                                                        echo ' Dr';
                                                    }else if($balance < 0){
                                                        echo number_format(abs($balance), 2, '.', ','); 
                                                        echo ' Cr';
                                                    }
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
                                <th><?= number_format($totalDebit, 2, '.', ',').' Dr'; ?></th>
                                <th><?= number_format($totalCredit, 2, '.', ',').' Cr'; ?></th>
                                <th>
                                    <?php 
                                        
                                        if($balance >= 0){
                                            echo number_format($balance, 2, '.', ','); 
                                            echo ' Dr';
                                        }else if($balance < 0){
                                            echo number_format($balance, 2, '.', ','); 
                                            echo ' Cr';
                                        }
                                    ?>
                                </th>
                            </tr>
                            </tbody>
                            <tfoot></tfoot>
                        </table>
                    </div>
                
            </div>

        </div>
    </div>

</div>
