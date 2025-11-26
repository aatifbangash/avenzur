<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="modal-dialog modal-lg">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?php echo lang('add_supplier'); ?></h4>
        </div>
        <?php $attrib = ['data-toggle' => 'validator', 'role' => 'form', 'id' => 'crud-supplier-form'];
        echo admin_form_open_multipart('suppliers/add', $attrib); ?>
        <div class="modal-body">
            <p><?= lang('enter_info'); ?></p>

            <!--<div class="form-group">
                    <?= lang('type', 'type'); ?>
                    <?php $types = ['company' => lang('company'), 'person' => lang('person')];
            echo form_dropdown('type', $types, '', 'class="form-control select" id="type" required="required"'); ?>
                </div> -->

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group person">
                        <?= lang('name', 'name'); ?>
                        <?php echo form_input('name', '', 'class="form-control tip" id="name" data-bv-notempty="true"'); ?>
                    </div>
    
                    <div class="form-group  ">
                        <?= lang('name_arabic', 'name_arabic'); ?>
                        <?php echo form_input('name_ar', '', 'class="form-control tip" id="name" data-bv-notempty="true"'); ?>
                    </div>

                    <div class="form-group">
                        <?= lang('level', 'level'); ?>
                        <?php 
                        $level_options = [
                            '1' => lang('parent'),
                            '2' => lang('child')
                        ];
                        echo form_dropdown('level', $level_options, '1', 'class="form-control select" id="level"'); 
                        ?>
                    </div>

                    <!--<div class="form-group company">
                    <?= lang('contact_person', 'contact_person'); ?>
                    <?php echo form_input('contact_person', '', 'class="form-control" id="contact_person" data-bv-notempty="true"'); ?>
                </div>-->
                    <div class="form-group">
                        <?= lang('email_address', 'email_address'); ?>
                        <input type="email" name="email" class="form-control" id="email_address"/>
                    </div>
                    <div class="form-group">
                        <?= lang('Vat No', 'vat_no'); ?>
                        <?php echo form_input('vat_no', '', 'class="form-control" id="vat_no"'); ?>
                    </div>
                    
                    <div class="form-group">
                        <?= lang('Contact_Name', 'contact_name'); ?>
                        <?php echo form_input('contact_name', '', 'class="form-control" id="contact_name"'); ?>
                    </div>
                    <div class="form-group">
                        <?= lang('Contact_Number', 'contact_number'); ?>
                        <?php echo form_input('contact_number', '', 'class="form-control" id="contact_number"'); ?>
                    </div>
                    <div class="form-group">
                        <?= lang('address', 'address'); ?>
                        <?php echo form_input('address', '', 'class="form-control" id="address" required="required"'); ?>
                    </div>
                    <div class="form-group">
                        <?= lang('country', 'country'); ?>
                        <?php echo form_input('country', '', 'class="form-control" id="country"'); ?>
                    </div>
                    <div class="form-group">
                        <?= lang('city', 'city'); ?>
                        <?php echo form_input('city', '', 'class="form-control" id="city" required="required"'); ?>
                    </div>    
                    <div class="form-group">
                        <?= lang('Payment_Term', 'popayment_term'); ?>
                        <?php echo form_input('payment_term', '', 'class="form-control tip" data-trigger="focus"   data-placement="top" title="' . lang('payment_term_tip') . '" id="popayment_term"'); ?>
                    </div>
                    
                    <div class="form-group">
                        <?= lang('postal_code', 'postal_code'); ?>
                        <?php echo form_input('postal_code', '', 'class="form-control" id="postal_code"'); ?>
                    </div>
                    
                </div>
                <div class="col-md-6">
                    <div class="form-group company">
                        <?= lang('company', 'company'); ?>
                        <?php echo form_input('company', '', 'class="form-control tip" id="company" data-bv-notempty="true"'); ?>
                    </div>
                    
                    <div class="form-group  ">
                        <?= lang('category', 'category'); ?>
                        <?php $catogories_arr = [''=>'Please Select', 'خدمات ' => lang('خدمات'), 'مستودع' => lang('مستودع'), 'وكيل' => lang('وكيل')];
                        echo form_dropdown('category', $catogories_arr, '', 'class="form-control select" id="category" name="category" required="required"'); ?>
                    </div>

                    <div class="form-group" id="parent_company_group" style="display: none;">
                        <?= lang('parent_company', 'parent_company'); ?>
                        <?php 
                        echo form_dropdown('parent_id', $parent_suppliers, '', 'class="form-control select" id="parent_id"'); 
                        ?>
                    </div>
                    
                    <div class="form-group">
                        <?= lang('Ledger Account', 'Ledger Account'); ?>
                        <?php 

                            echo form_dropdown('ledger_account', $LO,'', 'id="ledger_account" class="ledger-dropdown form-control" required="required"',$DIS);  
                        ?>
                    </div>
                    <!--<div class="form-group">
                        <?= lang('balance', 'balance'); ?>
                        <?php echo form_input('balance', '', 'class="form-control" id="balance" step="0.01" type="number"'); ?>
                    </div>-->
                    <div class="form-group">
                        <?= lang('phone', 'phone'); ?>
                        <input type="tel" name="phone" class="form-control" id="phone"/>
                    </div>
                    <div class="form-group">
                        <?= lang('Cr', 'cr'); ?>
                        <?php echo form_input('cr', '', 'class="form-control" id="cr"'); ?>
                    </div>
                    <div class="form-group">
                        <?= lang('Cr Expiration', 'cr_expiration'); ?>
                        <?php echo form_input('cr_expiration', '', 'class="form-control" id="cr_expiration" placeholder="YYYY-MM-DD"'); ?>
                    </div>
                    <div class="form-group">
                        <?= lang('Gln', 'gln'); ?>
                        <?php echo form_input('gln', '', 'class="form-control" id="gln"'); ?>
                    </div>    
                    <div class="form-group">
                        <?= lang('Short_Address', 'short_address'); ?>
                        <?php echo form_input('short_address', '', 'class="form-control" id="short_address"'); ?>
                    </div>  
                    <div class="form-group">
                        <?= lang('state', 'state'); ?>
                        <?php
                        if ($Settings->indian_gst) {
                            $states = $this->gst->getIndianStates(true);
                            echo form_dropdown('state', $states, '', 'class="form-control select" id="state" required="required"');
                        } else {
                            echo form_input('state', '', 'class="form-control" id="state"');
                        }
                        ?>
                    </div>
                    <div class="form-group">
                        <?= lang('Building Number', 'building_number'); ?>
                        <?php echo form_input('building_number', '', 'class="form-control" id="building_number"'); ?>
                    </div>
                    <div class="form-group">
                        <?= lang('Credit_limit', 'credit_limit'); ?>
                        <?php echo form_input('credit_limit', '', 'class="form-control" id="credit_limit"'); ?>
                    </div>
                    <!--<div class="form-group">
                        <?= lang('note', 'note'); ?>
                        <?php echo form_textarea('note', '', 'class="form-control" id="note" rows="3"'); ?>
                    </div>-->
                   
                </div>
            </div>


        </div>
        <div class="modal-footer">
            <?php echo form_submit('add_supplier', lang('add_supplier'), 'class="btn btn-primary"'); ?>
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
                },
                parent_id: {
                    validators: {
                        notEmpty: {
                            message: 'Please select a Parent Company'
                        }
                    }
                }
            }
        });
        $('select.select').select2({minimumResultsForSearch: 7});
        $('select.ledger-dropdown').select2({minimumResultsForSearch: 7});

        // Show/hide parent company dropdown based on level selection
        $('#level').on('change', function() {
            var level = $(this).val();
            if (level == '2') {
                $('#parent_company_group').show();
                // Enable validation for parent_id
                $('#crud-supplier-form').bootstrapValidator('enableFieldValidators', 'parent_id', true);
            } else {
                $('#parent_company_group').hide();
                $('#parent_id').val('').trigger('change');
                // Disable validation for parent_id
                $('#crud-supplier-form').bootstrapValidator('enableFieldValidators', 'parent_id', false);
            }
        });

        // Trigger change on page load to set initial state
        $('#level').trigger('change');

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