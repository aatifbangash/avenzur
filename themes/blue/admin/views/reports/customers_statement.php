<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php $stmt_ledgers = isset($statement_ledger_options) && is_array($statement_ledger_options) ? $statement_ledger_options : []; ?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.17.3/xlsx.full.min.js"></script>
<script>
    function exportTableToExcel(tableId, filename = 'table.xlsx') {
        const table = document.getElementById(tableId);
        if (!table) {
            return;
        }
        const clone = table.cloneNode(true);
        clone.querySelectorAll('[data-xls-exclude="1"]').forEach(function (el) {
            if (el.parentNode) {
                el.parentNode.removeChild(el);
            }
        });
        const wb = XLSX.utils.table_to_book(clone, {
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
        $(document).on('change', '#statement_ledger_filter', function() {
            var v = $(this).val();
            $('#poTable tbody tr[data-stmt-row="1"]').each(function() {
                if (!v) {
                    $(this).show();
                } else {
                    $(this).toggle(String($(this).data('ledger-id')) === String(v));
                }
            });
        });
    });
</script>
<?php if($viewtype=='pdf' || $viewtype=='pdf_new'){ ?>
<link href="<?= $assets ?>styles/pdf/pdf.css" rel="stylesheet">

<!-- PDF Header -->
<div style="width:100%; margin-bottom:15px; position:relative;">

    <!-- RIGHT: Logo -->
    <div style="position:absolute; top:0; right:0; text-align:right;">
        <img src="<?= base_url('assets/uploads/logos/' . $biller->logo); ?>"
             style="max-width:150px; max-height:60px;">
        <div style="font-size: 12px; font-weight: bold; margin-top: 5px; color: #333;">
            <?= $biller->name ?? ''; ?>
        </div>
    </div>

    <!-- LEFT: Customer details -->
    <div style="width:70%;">
        
        <div style="font-size:10px; color:#666; margin-bottom:6px;">
            Printed on: <?= date('d/m/Y H:i:s'); ?>
        </div>

        <div style="font-size:13px; line-height:2.4;">
            
            <strong>Date From:</strong> <?= strip_tags($start_date ?? ''); ?>
            &nbsp;&nbsp;&nbsp;
            <strong>Date To:</strong> <?= strip_tags($end_date ?? ''); ?>
            <br>

            <strong>Customer ID:</strong> <?= strip_tags($customer_details->sequence_code ?? ''); ?>
            &nbsp;&nbsp;&nbsp;
            <strong>Customer Name:</strong> <?= strip_tags($customer_details->name ?? ''); ?>
            
            
        </div>

    </div>

    <!-- CLEAR -->
    <div style="clear:both;"></div>

</div>

<?php } ?>


<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-users"></i><?= lang('customer_statement'); ?></h2>
        <?php  if($viewtype!='pdf' && $viewtype!='pdf_new'){?>
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
                if($viewtype!='pdf' && $viewtype!='pdf_new')
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
                <?php if ($viewtype != 'pdf' && $viewtype != 'pdf_new' && count($stmt_ledgers) > 1) { ?>
                <div class="row" style="margin-bottom:12px;">
                    <div class="col-md-4">
                        <label class="control-label"><?= lang('customer_statement_ledger_filter'); ?></label>
                        <select id="statement_ledger_filter" class="form-control input-tip">
                            <option value=""><?= lang('all'); ?></option>
                            <?php foreach ($stmt_ledgers as $lid => $label) { ?>
                            <option value="<?= (int) $lid; ?>"><?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8'); ?></option>
                            <?php } ?>
                        </select>
                    </div>
                </div>
                <?php } ?>
                <div class="row">
                    <div class="controls table-controls" style="font-size: 12px !important;">
                        <table id="poTable"
                                class="table items table-striped table-bordered table-condensed table-hover tbl_pdf">
                            <thead>
                            <tr>
                                <?php if($viewtype!='pdf' && $viewtype!='pdf_new'){ ?>
                                <th>#</th>
                                <?php } ?>
                                <th><?= lang('date'); ?></th>
                                <th><?= lang('type'); ?></th>
                                <?php if ($viewtype != 'pdf' && $viewtype != 'pdf_new') { ?>
                                <th data-xls-exclude="1"><?= lang('customer_statement_ledger'); ?></th>
                                <?php } ?>
                                <th><?= lang('Num'); ?></th>
                                <th>Days</th>
                                <!--<th><?= lang('name'); ?></th>-->
                                <th><?= lang('Note'); ?></th>
                               
                                <th><?= lang('Debit'); ?></th>
                                <th><?= lang('Credit'); ?></th>
                                <th><?= lang('balance'); ?></th>
                            </tr>
                            </thead>
                            <tbody style="text-align:center;">
                                <?php if ($viewtype != 'pdf' && $viewtype != 'pdf_new') { ?>
                                <tr class="stmt-opening">
                                    <td></td>
                                    <td></td>
                                    <td style="text-align:left;font-weight:bold;">Opening Balance</td>
                                    <td data-xls-exclude="1"></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td><?= $this->sma->formatNumber($total_ob); ?></td>
                                </tr>
                                <?php } else { ?>
                                <tr class="stmt-opening">
                                    <td></td>
                                    <td style="text-align:left;font-weight:bold;">Opening Balance</td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td><?= $this->sma->formatNumber($total_ob); ?></td>
                                </tr>
                                <?php } ?>
                                <?php
                                    $count = 0;
                                    $balance = $total_ob;

                                    $totalCredit = 0;
                                    $totalDebit = 0;
                                    $totalBalance = 0;
                                    $openingBalance = $total_ob;

                                    foreach (isset($supplier_statement) ? $supplier_statement : [] as $statement) {
                                        $link = '#';
                                        $stmt_lid = isset($statement->statement_ledger_id) ? (int) $statement->statement_ledger_id : 0;
                                        $ledger_cell = '';
                                        if (!empty($statement->ledger_name)) {
                                            $ledger_cell = !empty($statement->code) ? $statement->code . ' — ' . $statement->ledger_name : $statement->ledger_name;
                                        } elseif (!empty($statement->code)) {
                                            $ledger_cell = $statement->code;
                                        }
                                        
                                        if($statement->dc == 'C'){
                                            $balance =  $balance - $statement->amount;
                                        }else{
                                            $balance = $balance + $statement->amount;
                                        }
                                        $count++;


                                        $transaction_type = '';
                                        $transaction_id = 0;
                                        $note = '';
                                        $sale_payment_term = $statement->company_payment_term;
                                        //$company_payment_term = $statement->company_payment_term;
                                        if($statement->transaction_type == 'sales_invoice' || $statement->transaction_type == 'saleorder'){
                                            $link = admin_url('sales?sid=' . $statement->sale_id);
                                            $transaction_type = 'Sales';
                                            $transaction_id = $statement->sale_id;
                                            $note = strip_tags(html_entity_decode($statement->sale_note));
                                            $sale_payment_term = $statement->sale_payment_term ?? $statement->company_payment_term;
                                        }else if($statement->transaction_type == 'customerpayment'){
                                            $link = admin_url('customers/view_payment/' . $statement->payment_id);
                                            $transaction_type = 'Payment';
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
                                            $transaction_type = 'Return';
                                            $transaction_id = $statement->return_id;
                                            $note = strip_tags(html_entity_decode($statement->return_note));
                                        }

                                        ?>
                                            <tr data-stmt-row="1" data-ledger-id="<?= $stmt_lid; ?>">
                                                <?php if($viewtype!='pdf' && $viewtype!='pdf_new'){ ?>
                                                <td><?= $count; ?></td>
                                                <?php } ?>
                                                <td><?= $statement->date; ?></td>
                                                <td><a target="_blank" href="<?= $link; ?>"><?= $transaction_type; ?></a></td>
                                                <?php if ($viewtype != 'pdf' && $viewtype != 'pdf_new') { ?>
                                                <td data-xls-exclude="1"><?= htmlspecialchars($ledger_cell, ENT_QUOTES, 'UTF-8'); ?></td>
                                                <?php } ?>
                                                
                                                <td><?= $transaction_id; ?></td>
                                                <td><?= $sale_payment_term; ?></td>
                                                <!--<td><?= $statement->company; ?></td>-->
                                                <td><?= $note; ?></td>
                                                
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
                                <?php if($viewtype!='pdf' && $viewtype!='pdf_new'){ ?>  
                                <th>&nbsp;</th>
                                <?php } ?>
                                <th>&nbsp;</th>
                                <th>&nbsp;</th>
                                <?php if ($viewtype != 'pdf' && $viewtype != 'pdf_new') { ?>
                                <th data-xls-exclude="1">&nbsp;</th>
                                <?php } ?>
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

        <?php if(($viewtype=='pdf' || $viewtype=='pdf_new') && !empty($aging_data) && 1==0): ?>
        <!-- PDF Footer - Customer Aging -->
        <div style="margin-top: 30px; border-top: 2px solid #000; padding-top: 10px;">
            <h3 style="margin-bottom: 15px; font-size: 16px; color: #333;">Customer Aging Summary</h3>
            <table class="table table-bordered" style="width: 100%; font-size: 12px;">
                <thead>
                    <tr style="background-color: #f5f5f5;">
                        <th style="text-align: center; font-weight: bold;">Current (0-30 days)</th>
                        <th style="text-align: center; font-weight: bold;">31-60 days</th>
                        <th style="text-align: center; font-weight: bold;">61-90 days</th>
                        <th style="text-align: center; font-weight: bold;">91-120 days</th>
                        <th style="text-align: center; font-weight: bold;">Over 120 days</th>
                        <th style="text-align: center; font-weight: bold;">Total Outstanding</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $customer_aging = $aging_data[0] ?? []; // Get first (and only) customer aging
                    
                    $aging_periods = ['0-30', '31-60', '61-90', '91-120', '>120'];
                    $total_aging = 0;
                    ?>
                    <tr>
                        <?php foreach($aging_periods as $key): 
                            $amount = $customer_aging[$key] ?? 0;
                            $total_aging += $amount;
                        ?>
                        <td style="text-align: center;"><?= number_format($amount, 2); ?></td>
                        <?php endforeach; ?>
                        <td style="text-align: center; font-weight: bold; background-color: #e9ecef;"><?= number_format($total_aging, 2); ?></td>
                    </tr>
                </tbody>
            </table>
        </div>
        <?php endif; ?>

    </div>

</div>
