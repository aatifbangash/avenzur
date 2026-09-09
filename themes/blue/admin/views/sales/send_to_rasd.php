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
            <button type="button" class="close text-white" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i></button>
            <h4 class="modal-title" id="myModalLabel"><?php echo lang('send_to_rasd'); ?></h4>
        </div>

        <?php 
        $attrib = ['data-toggle' => 'validator', 'role' => 'form', 'id' => "crud-label-form"];
        echo admin_form_open_multipart('sales/send_to_rasd', $attrib); 
        ?>

        <div class="modal-body">
            <!-- Visual Flow -->
            <div class="conversion-flow">
                <div class="flow-step">
                    <div class="flow-circle active"><i class="fa fa-shopping-cart"></i></div>
                    <span><?= lang('sale_order'); ?></span>
                </div>
                <div class="arrow"><i class="fa fa-arrow-right"></i></div>
                <div class="flow-step">
                    <div class="flow-circle"><i class="fa fa-file-invoice"></i></div>
                    <span><?= lang('send_to_rasd'); ?></span>
                </div>
                
            </div>

            <!-- Form -->
            <div class="form-group">
                <label class="control-label" for="send_to_rasd"><?= lang('send_to_rasd'); ?></label>
                <select name="send_to_rasd" class="form-control" required>
                    <option value="1"><?= lang('Confirm'); ?></option>
                    <option value="2"><?= lang('Skip'); ?></option>
                </select>
            </div>

            <input type="hidden" name="sale_id" value="<?= $sale_id; ?>" />
        </div>

        <div class="modal-footer">
            <?php echo form_submit('submit', lang('submit'), 'class="btn btn-primary"'); ?>
        </div>

        <?php echo form_close(); ?>
    </div>
</div>
