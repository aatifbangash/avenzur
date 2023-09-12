<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<script type="text/javascript"></script>
<?php if ($Owner || ($GP && $GP['bulk_actions'])) {
    echo admin_form_open('transfers/transfer_actions', 'id="action-form"');
} ?>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-star-o"></i><?= lang('Stock Requests'); ?></h2>

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
                            <th><?= lang('warehouse'); ?></th>
                            <th><?= lang('total'); ?></th>
                            <th><?= lang('status'); ?></th>
                            <th><?= lang('actions'); ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php 
                                if($stock_requests_array){
                                    $count = 0;
                                    foreach($stock_requests_array as $stock_request){
                                        $count++;
                                        ?>
                                            <tr>
                                                <td class="dataTables_empty"><?= $count; ?></td>
                                                <td class="dataTables_empty" style="text-align: center;"><?= $stock_request->date; ?></td>
                                                <td class="dataTables_empty" style="text-align: center;"><?= $stock_request->warehouse; ?></td>
                                                <td class="dataTables_empty" style="text-align: center;"> <?= number_format((float) $stock_request->req_stock, 2, '.', ''); ?></td>
                                                <td class="dataTables_empty" style="text-align: center;"><?= $stock_request->status == 'deleted' ? 'rejected' : $stock_request->status; ?></td>
                                                <td class="dataTables_empty" style="text-align: center;">
                                                    <a href="<?php echo admin_url('stock_request/view/' . $stock_request->id); ?>" class="tip" title="" data-original-title="View Request"><i class="fa fa-file-text-o"></i></a>
                                                    <?php if($warehouse_id == $stock_request->warehouse_id && $stock_request->status == 'saved'){ ?>
                                                            <a href="<?php echo admin_url('stock_request/edit/' . $stock_request->id); ?>" class="tip" title="Edit Request"><i class="fa fa-edit"></i></a>
                                                            <a href="<?php echo admin_url('stock_request/delete/' . $stock_request->id); ?>" class="tip" title="" data-original-title="Delete Request"><i class="fa fa-trash-o"></i></a>
                                                    <?php } ?>
                                                </td>
                                            </tr>
                                            <?php
                                    }
                                }else{
                            ?>
                                <tr><td colspan="11" class="dataTables_empty"><?= lang('No records found.'); ?></td></tr>
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
