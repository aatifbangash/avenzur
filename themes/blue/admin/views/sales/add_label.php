<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<style>
    .ledgers_group {
        display: none;
    }
</style>
<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?php echo lang('add_label'); ?></h4>
        </div>
        <?php $attrib = ['data-toggle' => 'validator', 'role' => 'form', 'id' => "crud-label-form"];
        echo admin_form_open_multipart('sales/add_label', $attrib); ?>
        <div class="modal-body">
            <p><?= lang('enter_info'); ?></p>

            
            <div class="form-group">
                <label class="control-label" for="number_of_cartons"><?php echo $this->lang->line('number_of_cartons'); ?></label>
                <?php echo form_input('number_of_cartons', '', 'class="form-control" id="number_of_cartons" required="required"'); ?>
            </div>
            
            <div class="form-group">
                <label class="control-label" for="refrigirated_items"><?php echo $this->lang->line('refrigirated_items'); ?></label>
                <?php echo form_input('refrigirated_items', '', 'class="form-control" id="refrigirated_items"'); ?>
            </div>
            <input type="hidden" name="sale_id" value="<?= $sale_id; ?>" />
            
        </div>
        <div class="modal-footer">
            <?php echo form_submit('add_label', lang('add_label'), 'class="btn btn-primary"'); ?>
        </div>
    </div>
    <?php echo form_close(); ?>
</div>