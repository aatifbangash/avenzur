<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<div class="modal-dialog modal-lg">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?php echo lang('Assign To Courier'); ?></h4>
        </div>
        <?php $attrib = ['data-toggle' => 'validator', 'role' => 'form', 'id' => 'assign-courier-form'];
        echo admin_form_open_multipart('sales/add_to_courier', $attrib); ?>

        <div class="modal-body">
            <p><?= lang('enter_info'); ?></p>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="control-label" for="courier"><?php echo $this->lang->line('Courier'); ?></label>
                        <?php
                        foreach ($couriers as $courier) {
                            $cgs[$courier->id] = $courier->name;
                        }
                        echo form_dropdown('Courier', $cgs, '', 'class="form-control select" id="courier" style="width:100%;" required="required"');
                        ?>
                    </div>
                </div>
            </div>
        </div>
        <input type="hidden" type="sale_id" name="sale_id" value="<?= $sale_id; ?>" />
        <div class="modal-footer">
            <?php echo form_submit('add_courier', lang('Assign Courier'), 'class="btn btn-primary"'); ?>
        </div>
        <?php echo form_close(); ?>
    </div>
</div>

