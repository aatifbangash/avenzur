<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?php echo lang('Add Shelves'); ?></h4>
        </div>
        <?php $attrib = ['data-toggle' => 'validator', 'role' => 'form'];
        echo admin_form_open_multipart('system_settings/add_shelf', $attrib); ?>
        <input type="hidden" name="warehouse_id" value="<?= $warehouse_id ?>">
        <div class="modal-body">
            <p><?= lang('enter_info'); ?></p>
            <p><button class="btn btn-warning" id="more_shelf">Add More</button></p>

            <table class="table table-bordered">
              <tbody id="shelf_body">
              <tr>
                <td>
                  <input type="text" name="shelf_name[]" class="form-control shelf_name" required="required" placeholder="Enter Shelf Name*">
                  </td>
                  <td class="text-center"><i class="fa fa-times tip shelf_del" title="Remove" style="cursor:pointer;"></i></td>
              </tr>
              </tbody>
            </table>
           
        </div>
        <div class="modal-footer">
            <?php echo form_submit('add_shelf', lang('Add Shelf'), 'class="btn btn-primary"'); ?>
        </div>
    </div>
    <?php echo form_close(); ?>
</div>



