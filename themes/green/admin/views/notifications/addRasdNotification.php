<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?php echo lang('add_notification'); ?></h4>
        </div>
        <?php $attrib = ['data-toggle' => 'validator', 'role' => 'form'];
        echo admin_form_open_multipart('notifications/addRasdNotification', $attrib); ?>
        <div class="modal-body">
            <p><?= lang('enter_info'); ?></p>
            <div class="form-group">
                <?php echo lang('Dispatch Id', 'dispatch_id'); ?>
                <div class="controls">
                    <?php echo form_input('dispatch_id', '', 'class="form-control" id="dispatch_id" required="required"'); ?>
                </div>
            </div>

            <div class="form-group">
                <?php echo lang('Invoice No.', 'invoice_no'); ?>
                <div class="controls">
                    <?php echo form_input('invoice_no', '', 'class="form-control" id="invoice_no" required="required"'); ?>
                </div>
            </div>

            
            <input id="csv_file_upload" type="file"  name="csv_file_upload" accept="*" />

        </div>
        <div class="modal-footer">
            <?php echo form_submit('add_notification', lang('add_notification'), 'class="btn btn-primary"'); ?>
        </div>
    </div>
    <?php echo form_close(); ?>
</div>
<?= $modal_js ?>
<script type="text/javascript" src="<?= $assets ?>js/custom.js"></script>
<script type="text/javascript" charset="UTF-8">
    $.fn.datetimepicker.dates['sma'] = <?=$dp_lang?>;
</script>

