<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<style>
    .tablewrap{
        font-size: 14px;
    }

    .leftwrap{
        width: 50%;
        float: left;
    }

    .rightwrap{
        width: 50%;
        float: left;
        
    }

    .table-head{
        background-color: #428bca;
        color: white;
        border-color: #357ebd;
        border-top: 1px solid #357ebd;
        text-align: center;
        border: 1px solid white;
    }

    .table-content{
        padding: 15px;
    }
</style>
<script>
    $(document).ready(function () {
        
    });
</script>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-users"></i><?= lang('financial_position'); ?></h2>

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
            echo admin_form_open_multipart('reports/financial_position', $attrib)
        ?>
        <div class="col-lg-12">
                <div class="row">
                    <div class="col-lg-12">
                       
                        <div class="col-md-4">
                            <div class="form-group">
                                <?= lang('Date', 'date'); ?>
                                <?php echo form_input('date', ($date ?? ''), 'class="form-control input-tip date" id="date"'); ?>
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
                        <?php 
                            $total_assets = 0;
                            $total_liabilities = 0;

                            if(isset($financial_position['ledger_groups'])){
                        ?>
                        <div class="tablewrap" style="overflow:hidden;">
                            
                            <div class="leftwrap">
                                <div class="table-head"><?= lang('INCOME'); ?></div>
                                <div class="table-content">
                                <?php
                                    foreach($financial_position['ledger_groups'] as $ledger_group){
                                        if($ledger_group->type2 == 'Revenue' || $ledger_group->type2 == 'Other Income'){
                                            ?>
                                                <div>
                                                    <span><b><?php echo $ledger_group->name; ?></b></span>
                                                    <span style="float:right;"><?php echo '-'; ?></span>
                                                </div>
                                            <?php
                                            foreach($ledger_group->ledgers as $ledger){
                                                $total_assets = $total_assets + ($ledger->credit_sum - $ledger->debit_sum);
                                                ?>
                                                <div style="margin-left: 20px;">
                                                    <span><?php echo $ledger->name; ?></span>
                                                    <span style="float:right;"><?php echo $ledger->credit_sum - $ledger->debit_sum; ?></span>
                                                </div>
                                                <?php
                                            }
                                        }
                                    }
                                ?>
                                </div>
                            </div>
                            <div class="rightwrap">
                                <div class="table-head"><?= lang('EXPENSE'); ?></div>
                                <div class="table-content">
                                <?php
                                    foreach($financial_position['ledger_groups'] as $ledger_group){
                                        if($ledger_group->type2 == 'Cost Of Sales' || $ledger_group->type2 == 'Operating Expenses' || $ledger_group->type2 == 'Other Expenses'){
                                            ?>
                                                <div>
                                                    <span><b><?php echo $ledger_group->name; ?></b></span>
                                                    <span style="float:right;"><?php echo '-'; ?></span>
                                                </div>
                                            <?php
                                            foreach($ledger_group->ledgers as $ledger){
                                                $total_liabilities = $total_liabilities + ($ledger->credit_sum - $ledger->debit_sum);
                                                ?>
                                                <div style="margin-left: 20px;">
                                                    <span><?php echo $ledger->name; ?></span>
                                                    <span style="float:right;"><?php echo $ledger->credit_sum - $ledger->debit_sum; ?></span>
                                                </div>
                                                <?php
                                            }
                                        }
                                    }
                                ?>
                                </div>
                                
                            </div>

                        </div>
                        <div class="bottom-wrap" style="overflow: hidden;">
                            <div class="table-head" style="width: 50%;float:left;"><span style="float:left;margin-left:20px;"><?= lang('TOTAL INCOME'); ?></span><span style="float:right;margin-right:20px;"><?= number_format($total_assets, 2, '.', ''); ?></span></div>
                            <div class="table-head" style="width: 50%;float:left;"><span style="float:left;margin-left:20px;"><?= lang('TOTAL EXPENSE'); ?></span><span style="float:right;margin-right:20px;"><?= number_format($total_liabilities, 2, '.', ''); ?></span></div>
                        </div>

                        <?php }else{
                            echo "No results to show";
                        }
                        ?>
                    </div>
                
            </div>

        </div>
    </div>
    <?php echo form_close(); ?>
</div>
