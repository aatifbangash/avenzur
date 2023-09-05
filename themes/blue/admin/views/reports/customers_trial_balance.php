<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<script>
    $(document).ready(function () {
        
    });
</script>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-users"></i><?= lang('customers_trial_balance'); ?></h2>

        <div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown"><a href="#" id="xls" class="tip" title="<?= lang('download_xls') ?>"><i class="icon fa fa-file-excel-o"></i></a></li>
                <li class="dropdown"><a href="#" id="image" class="tip" title="<?= lang('save_image') ?>"><i class="icon fa fa-file-picture-o"></i></a></li>
            </ul>
        </div>
    </div>
    <div class="box-content">
        <div class="row">
        <?php
            $attrib = ['data-toggle' => 'validator', 'role' => 'form'];
            echo admin_form_open_multipart('reports/customers_trial_balance', $attrib)
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
                                <th><?= lang('Sequence Code'); ?></th>
                                <th><?= lang('name'); ?></th>
                                <th><?= lang('Company'); ?></th>
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

                                    $totalObDebit = 0;
                                    $totalObCredit = 0;
                                    $totalTrsDebit = 0;
                                    $totalTrsCredit = 0;
                                    $totalFinalEndDebit = 0;
                                    $totalFinalEndCredit = 0;


                                    foreach ($trial_balance as $data){
                                        //sale_total + payment_total

                                        // $ob_debit = $data->ob_debit > $data->ob_credit ? $data->ob_debit - $data->ob_credit : 0;
                                        // $ob_credit = $data->ob_credit > $data->ob_debit ? $data->ob_credit - $data->ob_debit : 0;

                                        // $eb_debit = $ob_debit - $data->trs_credit + $data->trs_debit;
                                         $eb_credit = $data['obCredit'] + $data['trsCredit'];
                                         $eb_debit = $data['obDebit'] + $data['trsDebit'];

                                         $finalEndDebit = "-";
                                         $finalEndCredit = "-";
                                         if( $eb_credit >= $eb_debit){
                                            $finalEndCredit = $eb_credit - $eb_debit;
                                         }else{
                                            $finalEndDebit = $eb_debit - $eb_credit;
                                         }

                                         $totalObDebit += $data['obDebit'];
                                         $totalObCredit += $data['obCredit'];
                                         $totalTrsDebit += $data['trsDebit'];
                                         $totalTrsCredit += $data['trsCredit'];
                                         $totalFinalEndDebit += $finalEndDebit;
                                         $totalFinalEndCredit += $finalEndCredit;

                                        $count++;
                                        ?>
                                            <tr>
                                                <td><?= $count; ?></td>
                                                <td><?= $data['sequence_code']; ?></td>
                                                <td><?= $data['name']; ?></td>
                                                <td><?= $data['company']; ?></td>
                                                <td><?= $data['obDebit'] > 0 ? $this->sma->formatDecimal($data['obDebit']) : '-'; ?></td>
                                                <td><?= $data['obCredit'] > 0 ? $this->sma->formatDecimal($data['obCredit']) : '-'; ?></td>
                                                <td><?= $data['trsDebit'] > 0 ? $this->sma->formatDecimal($data['trsDebit']) : '-'; ?></td>
                                                <td><?= $data['trsCredit'] >0 ? $this->sma->formatDecimal($data['trsCredit']) : '-'; ?></td>
                                                <td><?= $finalEndDebit > 0 ? $this->sma->formatDecimal($finalEndDebit) : '-'; ?></td>
                                                <td><?= $finalEndCredit > 0 ? $this->sma->formatDecimal($finalEndCredit) : '-'; ?></td>
                                            </tr>
                                        <?php
                                    }
                                ?>
                                
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th>&nbsp;</th>
                                    <th>&nbsp;</th>
                                    <th>&nbsp;</th>
                                    <th>&nbsp;</th>

                                 
                                    <th class="text-center"><?=$this->sma->formatDecimal($totalObDebit)?></th>
                                    <th class="text-center"><?=$this->sma->formatDecimal($totalObCredit)?></th>

                                    <th class="text-center"><?=$this->sma->formatDecimal($totalTrsDebit)?></th>
                                    <th class="text-center"><?=$this->sma->formatDecimal($totalTrsCredit)?></th>

                                    <th class="text-center"><?=$this->sma->formatDecimal($totalFinalEndDebit)?></th>
                                    <th class="text-center"><?= $this->sma->formatDecimal($totalFinalEndCredit); ?></th>

                                </tr>
                            </tfoot>
                        </table>
                    </div>
                
            </div>

        </div>
    </div>
    <?php echo form_close(); ?>
</div>
