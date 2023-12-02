<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<script type="text/javascript"></script>
<?php if ($Owner || ($GP && $GP['bulk_actions'])) {
    echo admin_form_open('stock_request/current_pr', 'id="action-form"');
} ?>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-star-o"></i><?= lang('Purchase Request'); ?>
    </h2>
        <div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown">
                    <a href="#" id="xls" class="tip" title="<?php echo lang('download_xls'); ?>">
                        <i class="icon fa fa-file-excel-o"></i>
                    </a>
                </li>
            </ul>
        </div>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">

                <div class="table-responsive">
                    <table id="TOData" cellpadding="0" cellspacing="0" border="0"
                           class="table table-bordered table-condensed table-hover table-striped">
                        <thead>
                        <tr class="active">
                            <th style="min-width:30px; width: 30px; text-align: center;">
                                #
                            </th>
                            <th><?= lang('code'); ?></th>
                            <th colspan="2"><?= lang('name'); ?></th>
                            <th><?= lang('Available Quantity'); ?></th>
                            <th><?= lang('Avg Consumption'); ?></th>
                            <th colspan="2"><?= lang('Q req'); ?></th>
                            <th><?= lang('Safety Stock'); ?></th>
                        </tr>
                        </thead>
                        <tbody>
                            <?php 
                                if($current_pr){
                                    $count = 0;
                                    foreach($current_pr as $pr){
                                        $count++;
                                        $months = isset($pr->months) ? $pr->months : 1;
                                        ?>
                                            <tr>
                                                <td class="dataTables_empty"><?= $count; ?></td>
                                                <td class="dataTables_empty"><?= $pr->code; ?></td>
                                                <td colspan="2" class="dataTables_empty"><?= $pr->name; ?></td>
                                                <td class="dataTables_empty"><?= number_format((float) $pr->total_warehouses_quantity, 2, '.', ''); ?></td>
                                                <td class="dataTables_empty"><?= isset($pr->total_avg_stock) ? number_format((float) ($pr->total_avg_stock), 2, '.', '') : '0.00'; ?></td>
                                                <td colspan="2" class="dataTables_empty"><?= number_format((float) $pr->qreq, 2, '.', ''); ?></td>
                                                <td class="dataTables_empty"><?= $months; ?> months</td>
                                            </tr>
                                        <?php
                                    }
                                                    
                                }else{
                            ?>
                                <tr><td colspan="11" class="dataTables_empty"><?= lang('No rows found.'); ?></td></tr>
                            <?php
                                }
                            ?>
                            
                        </tbody>
                        
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php echo form_close(); ?>

<script type="text/javascript">
    $(document).ready(function () {
        $('#xls').click(function (event) {
            event.preventDefault();
            window.location.href = "<?=admin_url('stock_request/view_purchase/'.$request_id.'/xls/?v=1')?>";
            return false;
        });
    });
</script>

