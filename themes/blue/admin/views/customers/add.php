<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="modal-dialog modal-lg">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?php echo lang('add_customer'); ?></h4>
        </div>
        <?php $attrib = ['data-toggle' => 'validator', 'role' => 'form', 'id' => 'crud-customer-form'];
        echo admin_form_open_multipart('customers/add', $attrib); ?>
        <div class="modal-body">
            <p><?= lang('enter_info'); ?></p>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                    <label class="control-label" for="customer_group"><?php echo $this->lang->line('customer_group'); ?></label>
                        <?php
                        foreach ($customer_groups as $customer_group) {
                            $cgs[$customer_group->id] = $customer_group->name;
                        }
                        echo form_dropdown('customer_group', $cgs, $Settings->customer_group, 'class="form-control select" id="customer_group" style="width:100%;" required="required"');
                        ?>
                    </div>
                </div>
               
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="control-label" for="price_group"><?php echo $this->lang->line('price_group'); ?></label>
                        <?php
                        $pgs[''] = lang('select') . ' ' . lang('price_group');
                        foreach ($price_groups as $price_group) {
                            $pgs[$price_group->id] = $price_group->name;
                        }
                        echo form_dropdown('price_group', $pgs, $Settings->price_group, 'class="form-control select" id="price_group" style="width:100%;"');
                        ?>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group company">
                        <?= lang('company', 'company'); ?>
                        <?php echo form_input('company', '', 'class="form-control tip" id="company" data-bv-notempty="true"'); ?>
                    </div>
                    <div class="form-group person">
                        <?= lang('name', 'name'); ?>
                        <?php echo form_input('name', '', 'class="form-control tip" id="name" data-bv-notempty="true"'); ?>
                    </div>
                    <div class="form-group  ">
                        <?= lang('name_arabic', 'name_arabic'); ?>
                        <?php echo form_input('name_ar', '', 'class="form-control tip" id="name_ar" data-bv-notempty="true"'); ?>
                    </div>
                    <div class="form-group">
                        <?= lang('vat_no', 'vat_no'); ?>
                        <?php echo form_input('vat_no', '', 'class="form-control" id="vat_no"'); ?>
                    </div>

                    <div class="form-group">
                        <?= lang('cr_number', 'cr'); ?>
                        <?php echo form_input('cr', '', 'class="form-control" id="cr"'); ?>
                    </div>

                    <div class="form-group">
                        <?= lang('cr_expiration', 'cr_expiration'); ?>
                        <?php echo form_input('cr_expiration', '', 'class="form-control date-picker" id="cr_expiration"'); ?>
                    </div>

                    <div class="form-group">
                        <?= lang('gln_number', 'gln'); ?>
                        <?php echo form_input('gln', '', 'class="form-control" id="gln"'); ?>
                    </div>

                    <div class="form-group">
                        <label for="sfda_certificate">
                            <input type="checkbox" name="sfda_certificate" id="sfda_certificate" value="1">
                            <?= lang('sfda_certificate'); ?>
                        </label>
                    </div>

                    <div class="form-group">
                        <?= lang('email_address', 'email_address'); ?>
                        <input type="email" name="email" class="form-control" required="required" id="email_address"/>
                    </div>
                    <div class="form-group">
                        <?= lang('phone', 'phone'); ?>
                        <input type="tel" name="phone" class="form-control" required="required" id="phone"/>
                    </div>
                    <div class="form-group">
                        <?= lang('address', 'address'); ?>
                        <?php echo form_input('address', '', 'class="form-control" id="address" required="required"'); ?>
                    </div>

                    <div class="form-group">
                        <?= lang('short_address', 'short_address'); ?>
                        <?php echo form_input('short_address', '', 'class="form-control" id="short_address"'); ?>
                    </div>

                    <div class="form-group">
                        <?= lang('building_number', 'building_number'); ?>
                        <?php echo form_input('building_number', '', 'class="form-control" id="building_number"'); ?>
                    </div>

                    <div class="form-group">
                        <?= lang('unit_number', 'unit_number'); ?>
                        <?php echo form_input('unit_number', '', 'class="form-control" id="unit_number"'); ?>
                    </div>

                    <div class="form-group">
                        <?= lang('additional_number', 'additional_number'); ?>
                        <?php echo form_input('additional_number', '', 'class="form-control" id="additional_number"'); ?>
                    </div>

                    <div class="form-group">
                        <?= lang('city', 'city'); ?>
                        <?php echo form_input('city', '', 'class="form-control" id="city" required="required"'); ?>
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
                        <?= lang('postal_code', 'postal_code'); ?>
                        <?php echo form_input('postal_code', '', 'class="form-control" id="postal_code"'); ?>
                    </div>
                    <div class="form-group">
                        <?= lang('country', 'country'); ?>
                        <?php echo form_input('country', '', 'class="form-control" id="country"'); ?>
                    </div>

                </div>
                <div class="col-md-6">

                    <div class="form-group">
                        <?= lang('contact_name', 'contact_name'); ?>
                        <?php echo form_input('contact_name', '', 'class="form-control" id="contact_name"'); ?>
                    </div>

                    <div class="form-group">
                        <?= lang('contact_number', 'contact_number'); ?>
                        <?php echo form_input('contact_number', '', 'class="form-control" id="contact_number"'); ?>
                    </div>

                    <div class="form-group">
                        <?= lang('sales_agent', 'sales_agent'); ?>
                        <?php 
                            $salesmen = ['' => lang('select') . ' ' . lang('sales_agent')];
                            if (!empty($sales_agents)) {
                                foreach ($sales_agents as $agent) {
                                    $salesmen[$agent->id] = $agent->name;
                                }
                            }
                            echo form_dropdown('sales_agent', $salesmen, '', 'class="form-control select" id="sales_agent"');
                        ?>
                        <input type="hidden" name="sales_agent_name" id="sales_agent_name" value="">
                    </div>

                    <div class="form-group">
                        <?= lang('payment_term', 'popayment_term'); ?>
                        <?php echo form_input('payment_term', '', 'class="form-control tip" data-trigger="focus"   data-placement="top" title="' . lang('payment_term_tip') . '" id="popayment_term"'); ?>
                    </div>

                    <div class="form-group">
                        <?= lang('Credit_limit', 'credit_limit'); ?>
                        <?php echo form_input('credit_limit', '', 'class="form-control" id="credit_limit"'); ?>
                    </div>

                    <div class="form-group">
                        <?= lang('customer_balance', 'balance'); ?>
                        <?php echo form_input('balance', '', 'class="form-control" id="balance"'); ?>
                    </div>

                    <div class="form-group">
                        <?= lang('promissory_note_amount', 'promessory_note_amount'); ?>
                        <?php echo form_input('promessory_note_amount', '', 'class="form-control" id="promessory_note_amount"'); ?>
                    </div>
                  
                    <div class="form-group">
                        <?= lang('Ledger Account', 'ledger_account'); ?>
                        <?php 
                            echo form_dropdown('ledger_account', $LO,'', 'id="ledger_account" class="ledger-dropdown form-control" required="required"',$DIS);  
                        ?>
                    </div>

                    <div class="form-group">
                        <?= lang('Sales Account', 'sales_ledger'); ?>
                        <?php 
                            echo form_dropdown('sales_ledger', $LO,'', 'id="sales_ledger" class="ledger-dropdown form-control" required="required"',$DIS);  
                        ?>
                    </div>

                    <div class="form-group">
                        <?= lang('COGS Account', 'cogs_ledger'); ?>
                        <?php 
                            echo form_dropdown('cogs_ledger', $LO,'', 'id="cogs_ledger" class="ledger-dropdown form-control" required="required"',$DIS);  
                        ?>
                    </div>

                    <div class="form-group">
                        <?= lang('Discount Account', 'discount_ledger'); ?>
                        <?php 
                            echo form_dropdown('discount_ledger', $LO,'', 'id="discount_ledger" class="ledger-dropdown form-control" required="required"',$DIS);  
                        ?>
                    </div>

                    <div class="form-group">
                        <?= lang('Return Account', 'return_ledger'); ?>
                        <?php 
                            echo form_dropdown('return_ledger', $LO,'', 'id="return_ledger" class="ledger-dropdown form-control" required="required"',$DIS);  
                        ?>
                    </div>
                    
                    <div class="form-group">
                        <?= lang('note', 'note'); ?>
                        <?php echo form_textarea('note', '', 'class="form-control" id="note" rows="3"'); ?>
                    </div>
                </div>
            </div>


        </div>
        <div class="modal-footer">
            <?php echo form_submit('add_customer', lang('add_customer'), 'class="btn btn-primary"'); ?>
        </div>
    </div>
    <?php echo form_close(); ?>
</div>

<script type="text/javascript">
    $(document).ready(function (e) {
        $('#crud-customer-form').bootstrapValidator({
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
                sales_ledger: {
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
                cogs_ledger: {
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
                discount_ledger: {
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
                return_ledger: {
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
            }
        });
        $('select.select').select2({minimumResultsForSearch: 7});
        $('select.ledger-dropdown').select2({minimumResultsForSearch: 7});

        // Update sales agent name when selection changes
        $('#sales_agent').on('change', function() {
            var selectedText = $(this).find('option:selected').text();
            $('#sales_agent_name').val(selectedText);
        });

        // Initialize date picker for CR expiration
        $('.date-picker').datetimepicker({
            format: 'YYYY-MM-DD',
            fontAwesome: true,
            language: 'en',
            pickTime: false
        });

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