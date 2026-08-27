<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<style>
    /* Overall modal styling */
    .modal-content {
        border-radius: 16px;
        border: none;
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.2);
    }

    .modal-header {
        background: linear-gradient(135deg, #007bff, #00a8ff);
        color: #fff;
        border-top-left-radius: 16px;
        border-top-right-radius: 16px;
    }

    .modal-title {
        font-weight: 600;
        font-size: 1.25rem;
        text-transform: capitalize;
    }

    .modal-body {
        background-color: #f9fafc;
        padding: 25px 30px;
    }

    .form-group label {
        font-weight: 600;
        color: #333;
    }

    .form-control {
        border-radius: 10px;
        border: 1px solid #ccc;
        transition: 0.3s;
    }

    .form-control:focus {
        border-color: #007bff;
        box-shadow: 0 0 5px rgba(0, 123, 255, 0.4);
    }

    .modal-footer {
        background: #f1f3f6;
        border-top: 1px solid #ddd;
        padding: 15px 25px;
        border-bottom-left-radius: 16px;
        border-bottom-right-radius: 16px;
    }

    /* Flow section */
    .conversion-flow {
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 25px;
        gap: 20px;
    }

    .flow-step {
        text-align: center;
        position: relative;
    }

    .flow-circle {
        width: 55px;
        height: 55px;
        border-radius: 50%;
        background-color: #e0e7ff;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #007bff;
        font-size: 20px;
        margin: 0 auto;
        transition: 0.3s ease;
    }

    .flow-circle.active {
        background-color: #28a745;
        color: white;
        box-shadow: 0 0 8px rgba(40, 167, 69, 0.5);
    }

    .flow-step span {
        display: block;
        font-size: 13px;
        color: #555;
        margin-top: 8px;
        font-weight: 500;
    }

    .arrow {
        font-size: 22px;
        color: #aaa;
    }

    /* Button styling */
    .btn-primary {
        border-radius: 8px;
        font-weight: 600;
        padding: 10px 20px;
        transition: all 0.3s ease;
    }

    .btn-primary:hover {
        background-color: #0069d9;
        transform: translateY(-2px);
    }
</style>
<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?php echo lang('add_driver'); ?></h4>
        </div>
        <?php $attrib = ['data-toggle' => 'validator', 'role' => 'form', 'id' => "crud-label-form"];
        echo admin_form_open_multipart('sales/add_driver', $attrib); ?>
        <div class="modal-body">
            <p><?= lang('enter_info'); ?></p>
            <!-- Driver Dropdown -->
            <div class="form-group">
                <label class="control-label" for="driver_id"><?= lang('select_driver'); ?></label>
                <select name="driver_id" id="driver_id" class="form-control" required>
                    <option value=""><?= lang('select_driver'); ?></option>
                    <?php if (!empty($driver)) : ?>
                        <?php foreach ($driver as $d) : ?>
                            <option value="<?= $d->id; ?>"><?= $d->first_name . ' ' . $d->last_name; ?></option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>
            <div class="form-group">
                <label class="control-label" for="address"><?php echo $this->lang->line('address'); ?></label>
                <?php echo form_textarea('address', '', 'class="form-control" id="address" required="required"'); ?>
            </div>
            <input type="hidden" name="sale_id" value="<?= $sale_id; ?>" />
            
        </div>
        <div class="modal-footer">
            <?php echo form_submit('add_driver', lang('add_driver'), 'class="btn btn-primary"'); ?>
        </div>
    </div>
    <?php echo form_close(); ?>
</div>