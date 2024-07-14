<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<script>
    $(document).ready(function () {
        
    });
</script>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-users"></i><?= lang('general_ledger_trial_balance'); ?></h2>

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
            echo admin_form_open_multipart('reports/general_ledger_trial_balance', $attrib)
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
                                                <td><?= $ob_debit > 0 ? $this->sma->formatDecimal($ob_debit) : '-'; ?></td>
                                                <td><?= $ob_credit > 0 ? $this->sma->formatDecimal($ob_credit) : '-'; ?></td>
                                                <td><?= $data->trs_debit > 0 ? $this->sma->formatDecimal($data->trs_debit) : '-'; ?></td>
                                                <td><?= $data->trs_credit >0 ? $this->sma->formatDecimal($data->trs_credit) : '-'; ?></td>
                                                <td><?= $eb_debit > 0 ? $this->sma->formatDecimal($eb_debit) : '-'; ?></td>
                                                <td><?= $eb_credit > 0 ? $this->sma->formatDecimal($eb_credit) : '-'; ?></td>
                                            </tr>
                                        <?php
                                    }
                                ?>
                                <tr>
                                    <td colspan="3">Totals: </td>
                                    <td colspan="1"><?= number_format($total_ob_debit, 2, '.', ''); ?></td>
                                    <td colspan="1"><?= number_format($total_ob_credit, 2, '.', ''); ?></td>
                                    <td colspan="1"><?= number_format($total_trs_debit, 2, '.', ''); ?></td>
                                    <td colspan="1"><?= number_format($total_trs_credit, 2, '.', ''); ?></td>
                                    <td colspan="1"><?= number_format($total_eb_debit, 2, '.', ''); ?></td>
                                    <td colspan="1"><?= number_format($total_eb_credit, 2, '.', ''); ?></td>
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
