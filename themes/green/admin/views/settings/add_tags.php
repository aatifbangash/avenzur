<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<?php 
    $fields = array(
        'price' => '{
            "source_table": "sma_products",
            "source_field": "price",
            "destination_table": "sma_products",
            "destination_field": "id"
        }', 
        'locality' => '{
            "source_table": "sma_companies",
            "source_field": "city",
            "destination_table": "sma_companies",
            "destination_field": "id"
        }',
        'product_warehouse' => '{
            "source_table": "sma_warehouses_products",
            "source_field": "warehouse_id",
            "destination_table": "sma_products",
            "destination_field": "product_id"
        }'
    );

    $operators = array('=', '<', '>', '<=', '>=', '!=', 'LIKE');
?>

<div class="modal-dialog modal-lg">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?php echo lang('Add New Tag'); ?></h4>
        </div>
        <?php $attrib = ['data-toggle' => 'validator', 'role' => 'form', 'id' => 'add-tag-form'];
        echo admin_form_open_multipart('shop_settings/tags', $attrib); ?>

        <div class="modal-body">
            <p><?= lang('enter_info'); ?></p>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="control-label" for="name"><?php echo $this->lang->line('Tag'); ?></label>
                        <?= form_input('name', set_value('name'), 'class="form-control" id="name" required="required"'); ?>

                        <?= lang('description', 'description'); ?>
                        <?= form_input('description', set_value('description'), 'class="form-control tip" id="description" required="required"'); ?>

                        <label class="control-label" for="field"><?php echo $this->lang->line('Field'); ?></label>
                        <select class="form-control select" name="field" id="field" style="width:100%;" required="required">
                        <?php 
                            foreach($fields as $field => $value){
                                ?>
                                    <option value="<?= htmlspecialchars(json_encode($value)); ?>"><?= $field; ?></option>
                                <?php
                            }
                        ?>
                        </select>
                        
                        <label class="control-label" for="operator"><?php echo $this->lang->line('Operator'); ?></label>
                        <select class="form-control select" name="operator" id="operator" style="width:100%;" required="required">
                        <?php 
                            foreach($operators as $operator){
                                ?>
                                    <option value="<?= $operator; ?>"><?= $operator; ?></option>
                                <?php
                            }
                        ?>
                        </select>
                        
                        <label class="control-label" for="value"><?php echo $this->lang->line('Value'); ?></label>
                        <?= form_input('value', set_value('value'), 'class="form-control" id="value" required="required"'); ?>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="modal-footer">
            <?php echo form_submit('add_tag', lang('Add Tag'), 'class="btn btn-primary"'); ?>
        </div>
        <?php echo form_close(); ?>
    </div>
</div>

