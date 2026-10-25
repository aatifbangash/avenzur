<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true" id="close_button"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?php echo lang('Notifcation Mapping'); ?></h4>
        </div>
        <?php $attrib = ['role' => 'form'];
        echo admin_form_open_multipart('notifications/mapNotifications', $attrib); ?>
        <div class="modal-body">
            <p><?= lang('enter_info'); ?></p>
            <div class="form-group">
                <?php echo lang('Notification Id*', 'notification_id'); ?>
                <div class="controls">
                    <?php echo form_input('notification_id', '', 'class="form-control" id="notification_id" required="required"'); ?>
                </div>
            </div>
            <div class="form-group">
                <?= lang('attachments', 'document') ?>
                <input id="attachment" type="file" data-browse-label="<?= lang('browse'); ?>" name="attachment" data-show-upload="false" data-show-preview="false" class="form-control file">
            </div>
              
            <div class="clearfix"></div>
            <div class="modal-footer">
                <?php echo form_submit('submit_map_notification', lang('Submit'), 'class="btn btn-primary"'); ?>
            <!-- <div class="btn  btn-primary" id="submitAcceptDispatch" >Upload</div> -->
            </div>
        </div>

        </div>
        
    </div>
    <?php echo form_close(); ?>
</div>
<?= $modal_js ?>
<script type="text/javascript" src="<?= $assets ?>js/custom.js"></script>
<script type="text/javascript" charset="UTF-8">
    $.fn.datetimepicker.dates['sma'] = <?=$dp_lang?>;
</script>

