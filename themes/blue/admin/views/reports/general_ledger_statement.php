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
       // Use new mPDF method for better portrait control
       $('.viewtype').val('pdf_new');
       document.getElementById("searchForm").submit();
       $('.viewtype').val('');
    }
    $(document).ready(function() {

    });
</script>
<?php if($viewtype=='pdf' || $viewtype=='pdf_new'){ ?>
<link href="<?= $assets ?>styles/pdf/pdf.css" rel="stylesheet">


<!-- PDF Header -->
<div style="width:100%; margin-bottom:5px; margin-top:0; padding-top:0; position:relative;">

    <!-- Title and Date Row -->
    <div style="width:100%; margin-bottom:10px; overflow:hidden;">
        <!-- RIGHT: Print Date (put right first so it floats to right) -->
        <div style="float:right; font-size:10px; color:#666; text-align:right;">
            Printed on: <?= date('d/m/Y H:i:s'); ?>
        </div>
        
        <!-- LEFT: Title -->
        <div style="font-size:14px; font-weight:bold; color:#333;">
            General Ledger Statement
        </div>
    </div>

    <!-- Ledger Details -->
    <div style="width:100%;">
        <div style="font-size:13px; line-height:2.4;">

            <strong>Date From:</strong> <?= strip_tags($start_date ?? ''); ?>
            &nbsp;&nbsp;&nbsp;
            <strong>Date To:</strong> <?= strip_tags($end_date ?? ''); ?>
            <br>

            <strong>Ledger:</strong> <?php
                if(isset($ledger_id) && !empty($ledgers)) {
                    foreach($ledgers as $ledger) {
                        if($ledger->id == $ledger_id) {
                            echo $ledger->name . ' (' . $ledger->code . ')';
                            break;
                        }
                    }
                }
            ?>

        </div>
    </div>

    <!-- CLEAR -->
    <div style="clear:both;"></div>

</div>

<?php } ?>
<div class="box">
    <div class="box-header">
        <?php  if($viewtype!='pdf' && $viewtype!='pdf_new'){?>
        <h2 class="blue"><i class="fa-fw fa fa-users"></i><?= lang('general_ledger_statement'); ?></h2>
        <?php } ?>
        <?php  if($viewtype!='pdf' && $viewtype!='pdf_new'){?>
        <div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown"><a href="javascript:void(0);" onclick="exportTableToExcel('poTable', 'GL_Statement_Report.xlsx')" id="xls" class="tip" title="<?= lang('download_xls') ?>"><i class="icon fa fa-file-excel-o"></i></a></li>
                <li class="dropdown"> <a href="javascript:void(0);" onclick="generatePDF()" id="pdf" class="tip" title="<?= lang('download_PDF') ?>"><i class="icon fa fa-file-pdf-o"></i></a></li>
            </ul>
        </div>
        <?php } ?>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
                <?php
                if($viewtype!='pdf' && $viewtype!='pdf_new')
                {
                    $attrib = ['data-toggle' => 'validator', 'role' => 'form','id' => 'searchForm'];
                    echo admin_form_open_multipart('reports/general_ledger_statement', $attrib)
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
                                <?= lang('Ledger', 'posupplier'); ?>
                                <?php
                                $selected_ledger_id[] = isset($ledger_id) ? $ledger_id : '';
                                $sp[''] = '';
                                foreach ($ledgers as $ledger) {
                                    $sp[$ledger->id] = $ledger->name;
                                }
                                echo form_dropdown('ledger', $sp, $selected_ledger_id, 'id="supplier_id" class="form-control input-tip select" data-placeholder="' . lang('select') . ' ' . lang('ledger') . '" required="required" style="width:100%;" ', null); ?>
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
                <?php echo form_close(); 
                } ?>
                <hr/>
                <div class="row">
                    <div class="controls table-controls" style="font-size: 12px !important;">
                        <table id="poTable"
                               class="table items table-striped table-bordered table-condensed table-hover sortable_table tbl_pdf">
                            <thead>
                            <tr style="text-align:center;">
                                <?php if($viewtype!='pdf' && $viewtype!='pdf_new'){ ?>
                                <th style="width:5%; text-align:center;">#</th>
                                <?php } ?>
                                <th style="width:10%; text-align:center;"><?= lang('date'); ?></th>
                                <th style="width:5%; text-align:center;"><?= lang('type'); ?></th>

                                <!--<th><?= lang('name'); ?></th>-->
                                <th style="width:25%; text-align:center;"><?= lang('Note'); ?></th>

                                <th style="width:15%; text-align:center;"><?= lang('Debit'); ?></th>
                                <th style="width:15%; text-align:center;"><?= lang('Credit'); ?></th>
                                <th style="width:15%; text-align:center;"><?= lang('balance'); ?></th>
                            </tr>
                            </thead>
                            <tbody style="text-align:center;">
                                <tr>
                                    <td colspan="2">Opening Balance<td>
                                    <td colspan="2">&nbsp;</td>
                                    <td><?php 
                                        if($total_ob >= 0){
                                            echo '<span style="color:black;">' . $this->sma->formatNumber($total_ob) . '</span>';
                                        }else{
                                            echo '<span style="color:red;">-' . $this->sma->formatNumber(abs($total_ob)) . '</span>';
                                        }
                                    ?></td>
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


                                $transaction_type = '';
                                $transaction_id = 0;
                                $note = '';
                                if($statement->transaction_type == 'journal'){
                                    $link = admin_url('entries/view/journal/' . $statement->entry_id);
                                    $transaction_type = 'Journal';
                                    $transaction_id = $statement->code;
                                    $note = $statement->narration;
                                }else if($statement->transaction_type == 'payment'){
                                    $link = admin_url('entries/view/payment/' . $statement->entry_id);
                                    $transaction_type = 'Payment';
                                    $transaction_id = $statement->code;
                                    $note = $statement->narration;
                                }else if($statement->transaction_type == 'receipt'){
                                    $link = admin_url('entries/view/receipt/' . $statement->entry_id);
                                    $transaction_type = 'Receipt';
                                    $transaction_id = $statement->code;
                                    $note = $statement->narration;
                                }else if($statement->transaction_type == 'contra'){
                                    $link = admin_url('entries/view/contra/' . $statement->entry_id);
                                    $transaction_type = 'Contra';
                                    $transaction_id = $statement->code;
                                    $note = $statement->narration;
                                }else if($statement->transaction_type == 'sales_invoice' || $statement->transaction_type == 'saleorder'){
                                    $link = admin_url('sales?sid=' . $statement->sale_id);
                                    $transaction_type = 'Sales';
                                    $transaction_id = $statement->sale_id;
                                    $note = strip_tags(html_entity_decode($statement->narration));
                                }else if($statement->transaction_type == 'purchase_invoice' || $statement->transaction_type == 'purchaseorder'){
                                    $link = admin_url('purchases?pid=' . $statement->purchase_id);
                                    $transaction_type = 'Purchase';
                                    $transaction_id = $statement->purchase_id;
                                    $note = strip_tags(html_entity_decode($statement->narration));
                                }else if($statement->transaction_type == 'supplierpayment'){
                                    $link = admin_url('suppliers/view_payment/' . $statement->payment_id);
                                    $transaction_type = 'Supplier Payment';
                                    $transaction_id = $statement->payment_id;
                                }else if($statement->transaction_type == 'customerpayment'){
                                    $link = admin_url('customers/view_payment/' . $statement->payment_id);
                                    $transaction_type = 'Customer Payment';
                                    $transaction_id = $statement->payment_id;
                                }else if($statement->transaction_type == 'creditmemo'){
                                    $link = admin_url('customers/view_credit_memo/' . $statement->memo_id);
                                    $transaction_type = 'Memo';
                                    $transaction_id = $statement->memo_id;
                                    $note = strip_tags(html_entity_decode($statement->memo_note));
                                }else if($statement->transaction_type == 'debitmemo'){
                                    $link = admin_url('customers/view_credit_memo/' . $statement->memo_id);
                                    $transaction_type = 'Memo';
                                    $transaction_id = $statement->memo_id;
                                    $note = strip_tags(html_entity_decode($statement->memo_note));
                                }else if($statement->transaction_type == 'serviceinvoice'){
                                    $link = admin_url('customers/list_service_invoice');
                                    $transaction_type = 'Service Invoice';
                                    $transaction_id = $statement->memo_id;
                                    $note = strip_tags(html_entity_decode($statement->memo_note));
                                }else if($statement->transaction_type == 'returncustomerorder'){
                                    $link = admin_url('returns?rid=' . $statement->return_id);
                                    $transaction_type = 'Customer Return';
                                    $transaction_id = $statement->return_id;
                                    $note = strip_tags(html_entity_decode($statement->narration));
                                }else if($statement->transaction_type == 'returnorder'){
                                    $link = admin_url('returns_supplier?rsid=' . $statement->return_id);
                                    $transaction_type = 'Supplier Return';
                                    $transaction_id = $statement->return_id;
                                    $note = strip_tags(html_entity_decode($statement->narration));
                                }else{
                                    $link = admin_url('entries/view/journal/' . $statement->entry_id);
                                    $transaction_type = $statement->transaction_type;
                                    $transaction_id = $statement->code;
                                    $note = $statement->narration;
                                }

                                ?>
                                    <tr>
                                        <?php if($viewtype!='pdf' && $viewtype!='pdf_new'){ ?>
                                        <td><?= $count; ?></td>
                                        <?php } ?>
                                        <td><?= $statement->date; ?></td>
                                        <td><a target="_blank" href="<?= $link; ?>"><?= $transaction_type; ?></a></td>


                                        <!--<td><?= $statement->name; ?></td>-->
                                        <td><?= $note; ?></td>

                                        <td><?= $statement->dc == 'D' ? number_format($statement->amount, 2, '.', ',') : '0.00';
                                            $statement->dc == 'D' ? $totalDebit = ($totalDebit + $statement->amount) : null ?>

                                        </td>
                                        <td><?php echo $statement->dc == 'C' ? '-' . number_format($statement->amount, 2, '.', ',') : '0.00';
                                        $statement->dc == 'C' ?
                                            $totalCredit = $totalCredit + $statement->amount : null ?>

                                        </td>
                                        <td><?php
                                            if($balance >= 0){
                                                echo '<span style="color:black;">' . number_format($balance, 2, '.', ',') . '</span>';
                                            }else if($balance < 0){
                                                echo '<span style="color:red;">-' . number_format(abs($balance), 2, '.', ',') . '</span>';
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

                            <tr style="text-align:center;">
                                <?php if($viewtype!='pdf' && $viewtype!='pdf_new'){ ?>
                                <th style="text-align:center;">&nbsp;</th>
                                <?php } ?>
                                <th style="text-align:center;">&nbsp;</th>
                                <th style="text-align:center;">&nbsp;</th>
                                <th style="text-align:center;">&nbsp;</th>
                                

                                <th style="text-align:center;"><?= number_format($totalDebit, 2, '.', ','); ?></th>
                                <th style="text-align:center;"><?= '-' . number_format($totalCredit, 2, '.', ','); ?></th>
                                <th style="text-align:center;">
                                    <?php

                                        if($balance >= 0){
                                            echo '<span style="color:black;">' . number_format($balance, 2, '.', ',') . '</span>';
                                        }else if($balance < 0){
                                            echo '<span style="color:red;">-' . number_format(abs($balance), 2, '.', ',') . '</span>';
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
