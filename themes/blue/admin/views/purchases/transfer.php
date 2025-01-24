<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php 
    if($inv->invoice_number != ''){
?>
<div class="modal-dialog modal-lg">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?php echo lang('Transfer To Pharmacy'); ?></h4>
        </div>
        <?php $attrib = ['data-toggle' => 'validator', 'role' => 'form', 'id' => 'pharmacy-transfer-form'];
        echo admin_form_open_multipart('purchases/transfer_stock', $attrib); ?>

        <div class="modal-body">
            <p><?= lang('enter_info'); ?></p>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="control-label" for="warehouse"><?php echo $this->lang->line('warehouse'); ?></label>
                        <?php
                        foreach ($warehouses as $warehouse) {
                            $cgs[$warehouse->id] = $warehouse->name;
                        }
                        echo form_dropdown('warehouse', $cgs, '', 'class="form-control select" id="warehouse" style="width:100%;" required="required"');
                        ?>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <?= lang('Exclude Products', 'Exclude Products') ?>
                        <select class="form-control" id="cf1" name="product_ids[]" multiple="multiple" >
                                <?php
                                    foreach($purchase_items as $pr)
                                    {
                                        echo '<option value="'.$pr->avz_item_code.'">'.' ('.$pr->avz_item_code.') '.$pr->product_name.'</option>';
                                    }
                                ?>                  
                        </select><br /><br />
                        
                    </div>
                </div>
            </div>
        </div>
        <input type="hidden" type="purchase_id" name="purchase_id" value="<?= $purchase_id; ?>" />
        <div class="modal-footer">
            <?php 
            $data = array(
                'name' => 'add_stock',
                'onclick' => "if(confirm('Are you sure to proceed?')) { this.style.pointerEvents = 'none'; this.value = 'Processing...'; return true; } else { return false; }"
            );
            echo form_submit($data, lang('Transfer Stock'), 'class="btn btn-primary" id="transfer_stock"'); ?>
        </div>
        <?php echo form_close(); ?>
    </div>
</div>

<?php } else { ?>

<script>
    var invoiced = '<?= $inv->invoice_number; ?>';
    if(invoiced == ''){
        window.location.href = 'https://avenzur.com/admin/purchases';
    }

</script>

<?php } ?>
