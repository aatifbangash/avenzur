<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="modal-dialog modal-lg">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?php echo lang('edit_supplier'); ?></h4>
        </div>
        <?php $attrib = ['data-toggle' => 'validator', 'role' => 'form', 'id' => 'crud-supplier-form'];
        echo admin_form_open_multipart('suppliers/edit/' . $supplier->id, $attrib); ?>
        <div class="modal-body">
            <p><?= lang('enter_info'); ?></p>

            <!--<div class="form-group">
                    <?= lang('type', 'type'); ?>
                    <?php // $types = array('company' => lang('company'), 'person' => lang('person'));  echo form_dropdown('type', $types, $supplier->type, 'class="form-control select" id="type" required="required"');?>
                </div> -->
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group company">
                        <?= lang('company', 'company'); ?>
                        <?php echo form_input('company', $supplier->company, 'class="form-control tip" id="company" required="required"'); ?>
                    </div>
                    <div class="form-group person">
                        <?= lang('name', 'name'); ?>
                        <?php echo form_input('name', $supplier->name, 'class="form-control tip" id="name" required="required"'); ?>
                    </div>
                    <div class="form-group  ">
                        <?= lang('name_arabic', 'name_arabic'); ?>
                        <?php echo form_input('name_ar',  $supplier->name_ar, 'class="form-control tip" id="name" data-bv-notempty="true"'); ?>
                    </div>

                    <div class="form-group  ">  
                        <?= lang('category', 'category'); ?>
                        <?php $catogories_arr = [''=>'Please Select', 'service ' => lang('service'), 'trade' => lang('trade')];
                        echo form_dropdown('category', $catogories_arr, $supplier->category, 'class="form-control select" id="category" name="category" required="required"'); ?>
                    </div> 
                    <div class="form-group">
                        <?= lang('vat_no', 'vat_no'); ?>
                        <?php echo form_input('vat_no', $supplier->vat_no, 'class="form-control" id="vat_no"'); ?>
                    </div>
                    <div class="form-group">
                        <?= lang('gst_no', 'gst_no'); ?>
                        <?php echo form_input('gst_no', $supplier->gst_no, 'class="form-control" id="gst_no"'); ?>
                    </div>
                    <!--<div class="form-group company">
                    <?= lang('contact_person', 'contact_person'); ?>
                    <?php // echo form_input('contact_person', $supplier->contact_person, 'class="form-control" id="contact_person" required="required"');?>
                </div> -->
                    <div class="form-group">
                        <?= lang('email_address', 'email_address'); ?>
                        <input type="email" name="email" class="form-control" required="required" id="email_address"
                               value="<?= $supplier->email ?>"/>
                    </div>
                    <div class="form-group">
                        <?= lang('phone', 'phone'); ?>
                        <input type="tel" name="phone" class="form-control" required="required" id="phone"
                               value="<?= $supplier->phone ?>"/>
                    </div>
                    <div class="form-group">
                        <?= lang('address', 'address'); ?>
                        <?php echo form_input('address', $supplier->address, 'class="form-control" id="address" required="required"'); ?>
                    </div>
                    <div class="form-group">
                        <?= lang('city', 'city'); ?>
                        <?php echo form_input('city', $supplier->city, 'class="form-control" id="city" required="required"'); ?>
                    </div>
                    <div class="form-group">
                        <?= lang('state', 'state'); ?>
                        <?php
                        if ($Settings->indian_gst) {
                            $states = $this->gst->getIndianStates(true);
                            echo form_dropdown('state', $states, $supplier->state, 'class="form-control select" id="state" required="required"');
                        } else {
                            echo form_input('state', $supplier->state, 'class="form-control" id="state"');
                        }
                        ?>
                    </div>
                    <div class="form-group">
                        <?= lang('payment_term', 'popayment_term'); ?>
                        <?php echo form_input('payment_term', $supplier->payment_term, 'class="form-control tip" data-trigger="focus" data-placement="top" title="' . lang('payment_term_tip') . '" id="popayment_term"'); ?>
                    </div>
                    <div class="form-group">
                        <?= lang('Credit_limit', 'credit_limit'); ?>
                        <?php echo form_input('credit_limit', $supplier->credit_limit, 'class="form-control" id="credit_limit"'); ?>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <?= lang('postal_code', 'postal_code'); ?>
                        <?php echo form_input('postal_code', $supplier->postal_code, 'class="form-control" id="postal_code"'); ?>
                    </div>
                    <div class="form-group">
                        <?= lang('country', 'country'); ?>
                        <?php echo form_input('country', $supplier->country, 'class="form-control" id="country"'); ?>
                    </div>

                    <div class="form-group">
                        <?= lang('Ledger Account', 'ledger_account'); ?>
                        <?php 

                            echo form_dropdown('ledger_account', $LO,$supplier->ledger_account, 'id="ledger_account" class="ledger-dropdown form-control" required="required"',$DIS);  
                        ?>
                    </div>
                    
                    <div class="form-group">
                        <?= lang('scf1', 'cf1'); ?>
                        <?php echo form_input('cf1', $supplier->cf1, 'class="form-control" id="cf1"'); ?>
                    </div>
                    <div class="form-group">
                        <?= lang('scf2', 'cf2'); ?>
                        <?php echo form_input('cf2', $supplier->cf2, 'class="form-control" id="cf2"'); ?>

                    </div>
                    <div class="form-group">
                        <?= lang('scf3', 'cf3'); ?>
                        <?php echo form_input('cf3', $supplier->cf3, 'class="form-control" id="cf3"'); ?>
                    </div>
                    <div class="form-group">
                        <?= lang('scf4', 'cf4'); ?>
                        <?php echo form_input('cf4', $supplier->cf4, 'class="form-control" id="cf4"'); ?>

                    </div>
                    <div class="form-group">
                        <?= lang('scf5', 'cf5'); ?>
                        <?php echo form_input('cf5', $supplier->cf5, 'class="form-control" id="cf5"'); ?>

                    </div>
                    <div class="form-group">
                        <?= lang('scf6', 'cf6'); ?>
                        <?php echo form_input('cf6', $supplier->cf6, 'class="form-control" id="cf6"'); ?>
                    </div>
                </div>
            </div>


        </div>
        <div class="modal-footer">
            <?php echo form_submit('edit_supplier', lang('edit_supplier'), 'class="btn btn-primary"'); ?>
        </div>
    </div>
    <?php echo form_close(); ?>
</div>

<!-- <?= $modal_js ?> -->
<script type="text/javascript">
    $(document).ready(function (e) {
        $('#crud-supplier-form').bootstrapValidator({
            feedbackIcons: {
                valid: 'fa fa-check',
                invalid: 'fa fa-times',
                validating: 'fa fa-refresh'
            }, excluded: [':disabled'],
            fields:{
                ledger_account: {
                    validators: {
                        notEmpty: {
                            message: 'Please select a Ledger'
                        },
                        callback: {
                            message: 'Please select a Ledger',
                            callback: function(value, validator) {
                                return value !== '0';
                            }
                        }
                    }
                }
            }
        });
        $('select.select').select2({minimumResultsForSearch: 7});
        $('select.ledger-dropdown').select2({minimumResultsForSearch: 7});

        fields = $('.modal-content').find('.form-control');
        $.each(fields, function () {
            var id = $(this).attr('id');
            var iname = $(this).attr('name');
            var iid = '#' + id;
            if (!!$(this).attr('data-bv-notempty') || !!$(this).attr('required')) {
                $("label[for='" + id + "']").append(' *');
                $(document).on('change', iid, function () {
                    $('form[data-toggle="validator"]').bootstrapValidator('revalidateField', iname);
                });
            }
        });
    });
</script>

