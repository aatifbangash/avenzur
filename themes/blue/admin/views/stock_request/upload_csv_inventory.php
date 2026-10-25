<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true" id="close_button"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?php echo lang('Upload Csv Inventory'); ?></h4>
        </div>

        <?php $attrib = ['role' => 'form'];
            echo admin_form_open_multipart('stock_request/upload_csv_inventory_request', $attrib); 
            // echo admin_form_open_multipart('purchases/purchase_by_csv', $attrib); 
        ?>

        <div class="modal-body">
            <p><?= lang('enter_info'); ?></p>

            <div class="col-md-12">
                <div class="form-group">
                    <?php echo lang('Warehouse *', 'warehouse_id'); ?>

                    <div class="controls">
                        <?php
                            $sp[''] = '';
                            foreach ($warehouses as $warehouse) {
                                $sp[$warehouse->id] = $warehouse->name;
                            }
                            
                            echo form_dropdown('warehouse', $sp, ($_POST['warehouse']), 'id="warehouse_id" class="form-control input-tip select" data-placeholder="' . $this->lang->line('select') . ' ' . $this->lang->line('warehouse') . '" required="required" style="width:100%;" ');
                        ?>
                    </div>
                </div>
            </div>
              
            <div class="col-md-8">
                <div class="form-group">
                    <?= lang('csv_file', 'csv_file') ?>
                    <input id="csv_file" type="file" data-browse-label="<?= lang('browse'); ?>" name="csvfile" required="required"
                            data-show-upload="false" data-show-preview="false" class="form-control file">
                </div>
            </div>

            <div class="clearfix"></div>
            <div class="modal-footer">
                <?php
                $data = array(
                    'name' => 'add_pruchase',
                    'onclick'=>"return confirm('Are you sure to proceed?')"
                );
                ?>
                <div
                    class="from-group"><?php echo form_submit($data, $this->lang->line('submit'), 'id="add_pruchase" class="btn btn-primary" style="padding: 6px 15px; margin:15px 0;"'); ?>
                </div>
            </div>
        </div>     
    </div>
    <?php echo form_close(); ?>
</div>
