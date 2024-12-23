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
        <h2 class="blue"><i class="fa-fw fa fa-users"></i><?= lang('supplier_statement'); ?></h2>
        <?php  if($viewtype!='pdf'){?>
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
            if($viewtype!='pdf')
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
                            <tr>
                                <th>#</th>
                                <th><?= lang('Serial No'); ?></th>
                                <th><?= lang('type'); ?></th>
                                <th><?= lang('date'); ?></th>
                                <th><?= lang('Num'); ?></th>
                                <th><?= lang('Description'); ?></th>
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
                                ?>
                                <tr>
                                    <td><?= $count; ?></td>
                                    <td><?= $statement->$index ? $statement->$index : '-'; ?></td>
                                    <td><?= $statement->transaction_type; ?></td>
                                    <td><?= $statement->date; ?></td>
                                    <td><?= $statement->code; ?></td>
                                    <td><?= $statement->narration ? $statement->narration : '-'; ?></td>
                                    <td><?= $statement->dc == 'D' ? $this->sma->formatNumber($statement->amount) : '-';
                                        $statement->dc == 'D' ? $totalDebit = ($totalDebit + $statement->amount) : null ?>

                                    </td>
                                    <td><?php echo $statement->dc == 'C' ? $this->sma->formatNumber($statement->amount) : '-';
                                    $statement->dc == 'C' ?
                                        $totalCredit = $totalCredit + $statement->amount : null ?>

                                    </td>
                                    <td><?php 
                                        echo $this->sma->formatNumber($balance); 
                                        if($balance >= 0){
                                            echo ' Cr';
                                        }else if($balance < 0){
                                            echo ' Dr';
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
                            <tr>
                                <th>&nbsp;</th>
                                <th>&nbsp;</th>
                                <th>&nbsp;</th>
                                <th>&nbsp;</th>
                                <th>&nbsp;</th>
                                <th>&nbsp;</th>
                                <th><?= $this->sma->formatNumber($totalDebit).' Dr'; ?></th>
                                <th><?= $this->sma->formatNumber($totalCredit).' Cr'; ?></th>
                                <th>
                                    <?php 
                                        echo $this->sma->formatNumber($balance); 
                                        if($balance >= 0){
                                            echo ' Cr';
                                        }else if($balance < 0){
                                            echo ' Dr';
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
