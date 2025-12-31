<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<script type="text/javascript"></script>
<?php if ($Owner || ($GP && $GP['bulk_actions'])) {
    echo admin_form_open('transfers/transfer_actions', 'id="action-form"');
} ?>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-star-o"></i><?= lang('Inventory Check Requests'); ?></h2>
        <div class="box-icon">
            <ul class="btn-tasks">
                <?php
                    if($this->Settings->site_name != 'Hills Business Medical'){ ?>
                    <li class="dropdown">
                        <a href="<?= admin_url('stock_request/upload_csv_inventory'); ?>" data-toggle="modal" data-target="#myModal">
                            <i class="icon fa fa-upload"></i>
                        </a>
                    </li>
                <?php } ?>
            </ul>
        </div>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">

                <p class="introtext"><?= lang('list_results'); ?></p>

                <div class="table-responsive">
                    <table id="TOData" cellpadding="0" cellspacing="0" border="0"
                           class="table table-bordered table-condensed table-hover table-striped">
                        <thead>
                        <tr class="active">
                            <th style="width:2%;">#</th>
                            <th><?= lang('date'); ?></th>
                            <th><?= lang('Location'); ?></th>
                            <th><?= lang('status'); ?></th>
                            <th><?= lang('actions'); ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php 
                                if($inventory_check_requests_array){
                                    $count = 0;
                                    foreach($inventory_check_requests_array as $inventory_check_requests){
                                        $count++;
                                        ?>
                                            <tr>
                                                <td class="dataTables_empty"><?= $count; ?></td>
                                                <td class="dataTables_empty" style="text-align: center;"><?= $inventory_check_requests->date; ?></td>
                                                <td class="dataTables_empty" style="text-align: center;"> <?= $inventory_check_requests->name; ?></td>
                                                <td class="dataTables_empty" style="text-align: center;"><?= $inventory_check_requests->status; ?></td>
                                                <td class="dataTables_empty" style="text-align: center;">
                                                    
                                                    <?php 
                                                        if($inventory_check_requests->status != 'adjusted' && ($Admin || $Owner)){
                                                            ?>
                                                                <a href="<?php echo admin_url('stock_request/view_inventory_check/' . $inventory_check_requests->id); ?>" class="tip" title="" data-original-title="View Request"><i class="fa fa-file-text-o"></i></a>
                                                                <a href="<?php echo admin_url('stock_request/delete_inventory_check/' . $inventory_check_requests->id); ?>" class="tip" title="" data-original-title="Delete Request"><i class="fa fa-trash-o"></i></a>
                                                            <?php
                                                        }else{
                                                            ?>
                                                                <a href="<?php echo admin_url('stock_request/view_inventory_check_report/' . $inventory_check_requests->id); ?>" class="tip" title="" data-original-title="View Report"><i class="fa fa-file-text-o"></i></a>
                                                            <?php
                                                        }
                                                    ?>
                                                    
                                                </td>
                                            </tr>
                                            <?php
                                    }
                                }else{
                            ?>
                                <tr><td colspan="11" class="dataTables_empty"><?= lang('Could not load data'); ?></td></tr>
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
<script language="javascript">
    $(document).ready(function () {

        /*$('#delete').click(function (e) {
            e.preventDefault();
            $('#form_action').val($(this).attr('data-action'));
            $('#action-form-submit').trigger('click');
        });*/

    });
</script>
