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
                    <div class="form-group">
                        <?= lang('category', 'category'); ?>
                        <?php echo form_input('category', $supplier->category, 'class="form-control" id="category"'); ?>

                    </div>
                    <div class="form-group">
                        <?= lang('vat_no', 'vat_no'); ?>
                        <?php echo form_input('vat_no', $supplier->vat_no, 'class="form-control" id="vat_no"'); ?>
                    </div>
                    <div class="form-group">
                        <?= lang('gln', 'gln'); ?>
                        <?php echo form_input('gln', $supplier->gln, 'class="form-control" id="gln"'); ?>
                    </div>
                    <!--<div class="form-group company">
                    <?= lang('contact_person', 'contact_person'); ?>
                    <?php // echo form_input('contact_person', $supplier->contact_person, 'class="form-control" id="contact_person" required="required"');?>
                </div> -->
                    <div class="form-group">
                        <?= lang('email_address', 'email_address'); ?>
                        <input type="email" name="email" class="form-control" id="email_address"
                               value="<?= $supplier->email ?>"/>
                    </div>
                    <div class="form-group">
                        <?= lang('phone', 'phone'); ?>
                        <input type="tel" name="phone" class="form-control" id="phone"
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
                        <?= lang('payment_term', 'popayment_term'); ?>
                        <?php echo form_input('payment_term', $supplier->payment_term, 'class="form-control tip" data-trigger="focus" data-placement="top" title="' . lang('payment_term_tip') . '" id="popayment_term"'); ?>
                    </div>
                    <div class="form-group">
                        <?= lang('Credit_limit', 'credit_limit'); ?>
                        <?php echo form_input('credit_limit', $supplier->credit_limit, 'class="form-control" id="credit_limit"'); ?>
                    </div>  
                    <div class="form-group">
                        <?= lang('Ledger Account', 'ledger_account'); ?>
                        <?php 

                            echo form_dropdown('ledger_account', $LO,$supplier->ledger_account, 'id="ledger_account" class="ledger-dropdown form-control" required="required"',$DIS);  
                        ?>
                    </div>
                    
                    <div class="form-group">
                        <?= lang('cr', 'cr'); ?>
                        <?php echo form_input('cr', $supplier->cr, 'class="form-control" id="cr"'); ?>
                    </div>
                    <div class="form-group">
                        <?= lang('Short Address', 'short_address'); ?>
                        <?php echo form_input('short_address', $supplier->short_address, 'class="form-control" id="short_address"'); ?>

                    </div>
                    <div class="form-group">
                        <?= lang('Building Number', 'building_number'); ?>
                        <?php echo form_input('building_number', $supplier->building_number, 'class="form-control" id="building_number"'); ?>
                    </div>
                    <div class="form-group">
                        <?= lang('Unit Number', 'unit_number'); ?>
                        <?php echo form_input('unit_number', $supplier->unit_number, 'class="form-control" id="unit_number"'); ?>

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

