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
        <h2 class="blue"><i class="fa-fw fa fa-users"></i><?= lang('general_ledger_trial_balance'); ?></h2>
        <?php  if($viewtype!='pdf'){?>
        <div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown">
                    <a href="javascript:void(0);" onclick="exportTableToExcel('poTable', 'GL_Trial_Balance_Report.xlsx')" id="xls" class="tip" title="<?= lang('download_xls') ?>"><i class="icon fa fa-file-excel-o"></i></a>
                </li>
                <li class="dropdown"> <a href="javascript:void(0);" onclick="generatePDF()" id="pdf" class="tip" title="<?= lang('download_PDF') ?>"><i class="icon fa fa-file-pdf-o"></i></a></li>
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
            echo admin_form_open_multipart('reports/general_ledger_trial_balance', $attrib)
        ?> <input type="hidden" name="viewtype" id="viewtype" class="viewtype" value="" > 
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
                            <?= lang('department', 'podepartment'); ?>
                            <?php
                            $selected_department_id[] = isset($department) ? $department : '';
                            $dp[''] = '';
                            foreach ($departments as $department) {
                                $dp[$department->id] = $department->name;
                            }
                            echo form_dropdown('department', $dp, $selected_department_id, 'id="department_id" class="form-control input-tip select" data-placeholder="' . lang('select') . ' ' . lang('department') . '" required="required" style="width:100%;" ', null); ?>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                            <?= lang('employee', 'poemployee'); ?>
                            <?php
                            $selected_employee_id[] = isset($employee) ? $employee : '';
                            $em[''] = '';
                            foreach ($employees as $employee) {
                                $em[$employee->id] = $employee->name;
                            }
                            echo form_dropdown('employee', $em, $selected_employee_id, 'id="employee_id" class="form-control input-tip select" data-placeholder="' . lang('select') . ' ' . lang('employee') . '" required="required" style="width:100%;" ', null); ?>
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
                                <th><?= lang('code'); ?></th>
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
                                    $total_ob_debit = 0;
                                    $total_ob_credit = 0;
                                    $total_trs_debit = 0;
                                    $total_trs_credit = 0;
                                    $total_eb_debit = 0;
                                    $total_eb_credit = 0;
                                    foreach ($trial_balance as $data){
                                        $ob_debit = $data->ob_debit;
                                        $ob_credit = $data->ob_credit;

                                        $eb_debit = $data->eb_debit;
                                        $eb_credit = $data->eb_credit;
                                        $count++;

                                        $total_ob_debit += $ob_debit;
                                        $total_ob_credit += $ob_credit;
                                        $total_trs_debit += $data->trs_debit;
                                        $total_trs_credit += $data->trs_credit;
                                        if($eb_debit > 0){
                                            $total_eb_debit += $eb_debit;
                                        }

                                        if($eb_credit > 0){
                                            $total_eb_credit += $eb_credit;
                                        }
                                    

                                        ?>
                                            <tr>
                                                <td><?= $count; ?></td>
                                                <td><?= $data->code; ?></td>
                                                <td><?= $data->name; ?></td>
                                                <td><?= $ob_debit > 0 ? $ob_debit : '-'; ?></td>
                                                <td><?= $ob_credit > 0 ? $ob_credit : '-'; ?></td>
                                                <td><?= $data->trs_debit > 0 ? $data->trs_debit : '-'; ?></td>
                                                <td><?= $data->trs_credit >0 ? $data->trs_credit : '-'; ?></td>
                                                <td><?= $eb_debit > 0 ? $eb_debit : '-'; ?></td>
                                                <td><?= $eb_credit > 0 ? $eb_credit : '-'; ?></td>
                                            </tr>
                                        <?php
                                    }
                                ?>
                                <tr>
                                    <td colspan="3"><strong>Totals: </strong></td>
                                    <td colspan="1"><strong><?= $total_ob_debit; ?></strong></td>
                                    <td colspan="1"><strong><?= $total_ob_credit; ?></strong></td>
                                    <td colspan="1"><strong><?= $total_trs_debit; ?></strong></td>
                                    <td colspan="1"><strong><?= $total_trs_credit; ?></strong></td>
                                    <td colspan="1"><strong><?= $total_eb_debit; ?></strong></td>
                                    <td colspan="1"><strong><?= $total_eb_credit; ?></strong></td>
                                </tr>
                            </tbody>
                            <tfoot></tfoot>
                        </table>
                    </div> 
            </div> 
        </div>
    </div> 
</div>
