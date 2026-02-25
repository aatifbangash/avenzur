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
       var form = document.getElementById("searchForm");
       $('.viewtype').val('pdf_new');
       form.target = "_blank";
       form.submit();
       form.target = "";
       $('.viewtype').val(''); 
    } 
    $(document).ready(function() {

    });
</script>
<?php if($viewtype=='pdf' || $viewtype=='pdf_new'){ ?>
<link href="<?= $assets ?>styles/pdf/pdf.css" rel="stylesheet">
<style>
    body {
        padding-top: 10px !important;
        padding-bottom: 15px !important;
    }
</style>

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
            Supplier Statement
        </div>
    </div>

    <!-- Supplier Details -->
    <div style="width:100%;">
        <div style="font-size:13px; line-height:2.4;">

            <strong>Date From:</strong> <?= strip_tags($start_date ?? ''); ?>
            &nbsp;&nbsp;&nbsp;
            <strong>Date To:</strong> <?= strip_tags($end_date ?? ''); ?>
            <br>

            <strong>Supplier:</strong> <?php
                if(isset($supplier_id) && !empty($suppliers)) {
                    foreach($suppliers as $supplier) {
                        if($supplier->id == $supplier_id) {
                            echo $supplier->name . ' - ' . $supplier->sequence_code;
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
        <h2 class="blue"><i class="fa-fw fa fa-users"></i><?= lang('supplier_statement'); ?></h2>
        <?php } ?>
        <?php  if($viewtype!='pdf' && $viewtype!='pdf_new'){?>
        <div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown"><a href="javascript:void(0);" onclick="exportTableToExcel('poTable', 'Supplier_Statement_Report.xlsx')" id="xls" class="tip" title="<?= lang('download_xls') ?>"><i
                class="icon fa fa-file-excel-o"></i></a></li>
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
            if($viewtype!='pdf' && $viewtype!='pdf_new')
            {
            $attrib = ['data-toggle' => 'validator', 'role' => 'form','id' => 'searchForm'];
            echo admin_form_open_multipart('reports/supplier_statement', $attrib)
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
                                <?= lang('supplier', 'posupplier'); ?>
                                <?php
                                $sp[''] = '';
                                foreach ($suppliers as $supplier) {
                                    $sp[$supplier->id] = $supplier->company . ' (' . $supplier->name . ') - '. $supplier->sequence_code;

                                }
                                echo form_dropdown('supplier', $sp, $supplier_id, 'id="supplier_id" class="form-control input-tip select" data-placeholder="' . lang('select') . ' ' . lang('supplier') . '" required="required" style="width:100%;" ', null); ?>
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
                <?php echo form_close(); 
                } ?>
                <div class="row">
                    <div class="controls table-controls" style="font-size: 12px !important;">
                        <table id="poTable"
                               class="table items table-striped table-bordered table-condensed table-hover sortable_table tbl_pdf">
                            <thead>
                            <tr style="text-align:center;">
                                <?php if($viewtype!='pdf' && $viewtype!='pdf_new'){ ?>
                                <th style="width:5%; text-align:center;">#</th>
                                <?php } ?>
                                <th style="width:8%; text-align:center;"><?= lang('Reference No'); ?></th>
                                <th style="width:8%; text-align:center;"><?= lang('type'); ?></th>
                                <th style="width:10%; text-align:center;"><?= lang('date'); ?></th>
                                <th style="width:25%; text-align:center;"><?= lang('Description'); ?></th>
                                <th style="width:12%; text-align:center;"><?= lang('Debit'); ?></th>
                                <th style="width:12%; text-align:center;"><?= lang('Credit'); ?></th>
                                <th style="width:12%; text-align:center;"><?= lang('balance'); ?></th>
                            </tr>
                            </thead>
                            <tbody style="text-align:center;">
                            <tr>
                                <?php if($viewtype=='pdf' || $viewtype=='pdf_new'){ ?>
                                <td colspan="2">Opening Balance</td>
                                <td colspan="4">&nbsp;</td>
                                <?php }else{ ?>
                                <td colspan="2">Opening Balance</td>
                                <td colspan="5">&nbsp;</td>
                                <?php } ?>
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
                            $serialArray = array(
                                                'pos' => 'sid',
                                                'purchaseorder' => 'pid',
                                                'returnorder' => 'rsid',
                                                'saleorder' => 'sid',
                                                'transferorder' => 'tid',
                                                'returncustomerorder' => 'rid'
                                             );
                            foreach ($supplier_statement as $statement) {
                                if ($statement->dc == 'D') {
                                    $balance = $balance - $statement->amount;
                                } else {
                                    $balance = $balance + $statement->amount;
                                }

                                $count++;
                                $index = $serialArray[$statement->transaction_type];
                                
                                // Map transaction types to display labels (matching general_ledger_statement)
                                $transaction_type = '';
                                if($statement->transaction_type == 'journal'){
                                    $transaction_type = 'Journal';
                                }else if($statement->transaction_type == 'payment'){
                                    $transaction_type = 'Payment';
                                }else if($statement->transaction_type == 'receipt'){
                                    $transaction_type = 'Receipt';
                                }else if($statement->transaction_type == 'contra'){
                                    $transaction_type = 'Contra';
                                }else if($statement->transaction_type == 'sales_invoice' || $statement->transaction_type == 'saleorder'){
                                    $transaction_type = 'Sales';
                                }else if($statement->transaction_type == 'purchase_invoice' || $statement->transaction_type == 'purchaseorder'){
                                    $transaction_type = 'Purchase';
                                }else if($statement->transaction_type == 'supplierpayment'){
                                    $transaction_type = 'Payment';
                                }else if($statement->transaction_type == 'customerpayment'){
                                    $transaction_type = 'Customer Payment';
                                }else if($statement->transaction_type == 'creditmemo'){
                                    $transaction_type = 'Memo';
                                }else if($statement->transaction_type == 'debitmemo'){
                                    $transaction_type = 'Memo';
                                }else if($statement->transaction_type == 'serviceinvoice'){
                                    $transaction_type = 'Service Invoice';
                                }else if($statement->transaction_type == 'returncustomerorder'){
                                    $transaction_type = 'Customer Return';
                                }else if($statement->transaction_type == 'returnorder'){
                                    $transaction_type = 'Return';
                                }else{
                                    $transaction_type = ucfirst($statement->transaction_type);
                                }
                                ?>
                                <tr>
                                    <?php if($viewtype!='pdf' && $viewtype!='pdf_new'){ ?>
                                    <td><?= $count; ?></td>
                                    <?php } ?>
                                    <td><?= $statement->reference_no ? $statement->reference_no : '0'; ?></td>
                                    <td><?= $transaction_type; ?></td>
                                    <td><?= $statement->date; ?></td>
                                    <td><?= $statement->narration ? $statement->narration : '-'; ?></td>
                                    <td><?= $statement->dc == 'D' ? $this->sma->formatNumber($statement->amount) : '0.00';
                                        $statement->dc == 'D' ? $totalDebit = ($totalDebit + $statement->amount) : null ?>

                                    </td>
                                    <td><?php echo $statement->dc == 'C' ? $this->sma->formatNumber($statement->amount) : '0.00';
                                    $statement->dc == 'C' ?
                                        $totalCredit = $totalCredit + $statement->amount : null ?>

                                    </td>
                                    <td><?php 
                                        if($balance >= 0){
                                            echo '<span style="color:black;">' . $this->sma->formatNumber($balance) . '</span>';
                                        }else if($balance < 0){
                                            echo '<span style="color:red;">-' . $this->sma->formatNumber(abs($balance)) . '</span>';
                                        }
                                        ?></td>
                                </tr>
                                <?php
                                if ($statement->dc == 'D') {
                                    $openingBalance -= $statement->amount;
                                } else {
                                    $openingBalance += $statement->amount;
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
                                <th style="text-align:center;">&nbsp;</th>
                                <th style="text-align:center;"><?= $this->sma->formatNumber($totalDebit); ?></th>
                                <th style="text-align:center;"><?= $this->sma->formatNumber($totalCredit); ?></th>
                                <th style="text-align:center;">
                                    <?php 
                                        if($balance >= 0){
                                            echo '<span style="color:black;">' . $this->sma->formatNumber($balance) . '</span>';
                                        }else if($balance < 0){
                                            echo '<span style="color:red;">-' . $this->sma->formatNumber(abs($balance)) . '</span>';
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
