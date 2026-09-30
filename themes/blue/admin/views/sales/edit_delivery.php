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
<div class="modal-dialog modal-lg">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?= lang('delivery_note'); ?></h4>
        </div>
        <?php $attrib = ['data-toggle' => 'validator', 'role' => 'form'];
        echo admin_form_open_multipart('sales/edit_delivery/' . $delivery->id, $attrib); ?>
        <div class="modal-body">
            <p><?= lang('enter_info'); ?></p>
            <div class="row">
            <div class="col-md-6">

                <div class="form-group">
                    <?= lang('do_reference_no', 'do_reference_no'); ?>
                    <?= form_input('do_reference_no', (isset($_POST['do_reference_no']) ? $_POST['do_reference_no'] : $delivery->do_reference_no), 'class="form-control tip" id="do_reference_no" required="required"'); ?>
                </div>

                <div class="form-group">
                    <?= lang('received_by', 'received_by'); ?>
                    <?= form_input('received_by', (isset($_POST['received_by']) ? $_POST['received_by'] : $delivery->received_by), 'class="form-control" id="received_by"'); ?>
                </div>

                <div class="form-group">
                    <?= lang('attachment', 'attachment') ?>
                    <input id="attachment" type="file" data-browse-label="<?= lang('browse'); ?>" name="document" data-show-upload="false" data-show-preview="false" class="form-control file">
                </div>

                <input type="hidden" value="<?= $sale_id; ?>" name="sale_id"/>
            </div>
            <div class="col-md-6">

                <div class="form-group">
                    <?= lang('status', 'status'); ?>
                    <?php
                    $opts = ['delivered' => lang('delivered')];
                    ?>
                    <?= form_dropdown('status', $opts, (isset($_POST['status']) ? $_POST['status'] : $delivery->status), 'class="form-control" id="status" required="required" style="width:100%;"'); ?>
                </div>

                <div class="form-group">
                    <?= lang('note', 'note'); ?>
                    <?= form_textarea('note', (isset($_POST['note']) ? $_POST['note'] : $delivery->note), 'class="form-control" id="note"'); ?>
                </div>
            </div>
            </div>

        </div>
        <div class="modal-footer">
            <?= form_submit('edit_delivery', lang('complete_delivery'), 'class="btn btn-primary"'); ?>
        </div>
    </div>
    <?= form_close(); ?>
</div>
<script type="text/javascript" src="<?= $assets ?>js/custom.js"></script>
<script type="text/javascript" charset="UTF-8">
    $.fn.datetimepicker.dates['sma'] = <?=$dp_lang?>;
</script>
<?= $modal_js ?>
<script type="text/javascript" charset="UTF-8">
    $(document).ready(function () {
        $.fn.datetimepicker.dates['sma'] = <?=$dp_lang?>;
    });
</script>
