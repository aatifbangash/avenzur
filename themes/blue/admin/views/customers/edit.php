<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="modal-dialog modal-lg">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?php echo lang('edit_customer'); ?></h4>
        </div>
        <?php $attrib = ['data-toggle' => 'validator', 'role' => 'form', 'id' => 'crud-customer-form'];
        echo admin_form_open_multipart('customers/edit/' . $customer->id, $attrib); ?>
        <div class="modal-body">
            <p><?= lang('enter_info'); ?></p>

            <!--<div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="control-label" for="customer_group"><?php echo $this->lang->line('customer_group'); ?></label>
                        <?php
                        foreach ($customer_groups as $customer_group) {
                            $cgs[$customer_group->id] = $customer_group->name;
                        }
                        echo form_dropdown('customer_group', $cgs, $customer->customer_group_id, 'class="form-control select" id="customer_group" style="width:100%;" required="required"');
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
                        echo form_dropdown('price_group', $pgs, $customer->price_group_id, 'class="form-control select" id="price_group" style="width:100%;"');
                        ?>
                    </div>
                </div>
            </div>-->

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group company">
                        <?= lang('company', 'company'); ?>
                        <?php echo form_input('company', $customer->company, 'class="form-control tip" id="company" required="required"'); ?>
                    </div>
                    <div class="form-group person">
                        <?= lang('name', 'name'); ?>
                        <?php echo form_input('name', $customer->name, 'class="form-control tip" id="name" required="required"'); ?>
                    </div>
                    <div class="form-group  ">
                        <?= lang('name_arabic', 'name_arabic'); ?>
                        <?php echo form_input('name_ar',  $customer->name_ar, 'class="form-control tip" id="name" data-bv-notempty="true"'); ?>
                    </div> 
                    <div class="form-group">
                        <?= lang('vat_no', 'vat_no'); ?>
                        <?php echo form_input('vat_no', $customer->vat_no, 'class="form-control" id="vat_no"'); ?>
                    </div>

                    <div class="form-group">
                        <?= lang('gln', 'gln'); ?>
                        <?php echo form_input('gln', $customer->gln, 'class="form-control" id="gln"'); ?>
                    </div>
                    <!--<div class="form-group company">
                    <?= lang('contact_person', 'contact_person'); ?>
                    <?php //echo form_input('contact_person', $customer->contact_person, 'class="form-control" id="contact_person" required="required"');
                    ?>
                </div> -->
                    
                    <div class="form-group">
                        <?= lang('cr', 'cr'); ?>
                        <?php echo form_input('cr', $customer->cr, 'class="form-control" id="cr"'); ?>
                    </div>
                    <div class="form-group">
                        <?= lang('phone', 'phone'); ?>
                        <input type="tel" name="phone" class="form-control" id="phone" value="<?= $customer->phone ?>" />
                    </div>
                    <div class="form-group">
                        <?= lang('email_address', 'email_address'); ?>
                        <input type="email" name="email" class="form-control" id="email_address" value="<?= $customer->email ?>" />
                    </div>
                    <div class="form-group">
                        <?= lang('city', 'city'); ?>
                        <?php echo form_input('city', $customer->city, 'class="form-control" id="city" required="required"'); ?>
                    </div>
                    <div class="form-group">
                        <?= lang('state', 'state'); ?>
                        <?php
                        if ($Settings->indian_gst) {
                            $states = $this->gst->getIndianStates(true);
                            echo form_dropdown('state', $states, $customer->state, 'class="form-control select" id="state" required="required"');
                        } else {
                            echo form_input('state', $customer->state, 'class="form-control" id="state"');
                        }
                        ?>
                    </div>
                    <div class="form-group">
                        <?= lang('postal_code', 'postal_code'); ?>
                        <?php echo form_input('postal_code', $customer->postal_code, 'class="form-control" id="postal_code"'); ?>
                    </div>
                    <div class="form-group">
                        <?= lang('country', 'country'); ?>
                        <?php echo form_input('country', $customer->country, 'class="form-control" id="country"'); ?>
                    </div>
                    <div class="form-group">
                        <?= lang('payment_term', 'popayment_term'); ?>
                        <?php echo form_input('payment_term', $customer->payment_term, 'class="form-control tip" data-trigger="focus" data-placement="top" title="' . lang('payment_term_tip') . '" id="popayment_term"'); ?>
                    </div>
                    <div class="form-group">
                        <?= lang('Credit_limit', 'credit_limit'); ?>
                        <?php echo form_input('credit_limit', $customer->credit_limit, 'class="form-control" id="credit_limit"'); ?>
                    </div> 
                    <div class="form-group">
                        <?= lang('Cr Expiration', 'cr_expiration'); ?>
                        <?php echo form_input('cr_expiration', $customer->cr_expiration, 'class="form-control" id="cr_expiration"'); ?>
                    </div> 
                </div>
                <div class="col-md-6">
                    <?php
                        $categories = array('Pharmacy Client' => 'Pharmacy Client', 'Clinic Client' => 'Clinic Client', 'Hospital Client' => 'Hospital Client', 'Rent Client' => 'Rent Client', 'Warehouse Client' => 'Warehouse Client');
                    ?>
                    <div class="form-group">
                        <?= lang('Category', 'category'); ?>
                        <?php echo form_dropdown('category', $categories, $customer->category, 'class="form-control select" id="category" required="required"'); ?>
                    </div>
                    <div class="form-group">
                        <?= lang('Ledger Account', 'ledger_account'); ?>
                        <?php

                        echo form_dropdown('ledger_account', $LO, $customer->ledger_account, 'id="ledger_account" class="ledger-dropdown form-control" required="required"', $DIS);
                        ?>
                    </div>

                    <div class="form-group">
                        <?= lang('Sales Account', 'sales_ledger'); ?>
                        <?php

                        echo form_dropdown('sales_ledger', $LO, $customer->sales_ledger, 'id="sales_ledger" class="ledger-dropdown form-control" required="required"', $DIS);
                        ?>
                    </div>

                    <div class="form-group">
                        <?= lang('COGS Account', 'cogs_ledger'); ?>
                        <?php

                        echo form_dropdown('cogs_ledger', $LO, $customer->cogs_ledger, 'id="cogs_ledger" class="ledger-dropdown form-control" required="required"', $DIS);
                        ?>
                    </div>

                    <div class="form-group">
                        <?= lang('Discount Account', 'discount_ledger'); ?>
                        <?php

                        echo form_dropdown('discount_ledger', $LO, $customer->discount_ledger, 'id="discount_ledger" class="ledger-dropdown form-control" required="required"', $DIS);
                        ?>
                    </div>

                    <div class="form-group">
                        <?= lang('Return Account', 'return_ledger'); ?>
                        <?php

                        echo form_dropdown('return_ledger', $LO, $customer->return_ledger, 'id="return_ledger" class="ledger-dropdown form-control" required="required"', $DIS);
                        ?>
                    </div>

                    <div class="form-group">
                        <?= lang('Short Address', 'short_address'); ?>
                        <?php echo form_input('short_address', $customer->short_address, 'class="form-control" id="short_address"'); ?>

                    </div>
                    <div class="form-group">
                        <?= lang('address', 'address'); ?>
                        <?php echo form_input('address', $customer->address, 'class="form-control" id="address" required="required"'); ?>
                    </div>
                    
                    <div class="form-group">
                        <?= lang('Building Number', 'building_number'); ?>
                        <?php echo form_input('building_number', $customer->building_number, 'class="form-control" id="building_number"'); ?>
                    </div>
                    <div class="form-group">
                        <?= lang('Unit Number', 'unit_number'); ?>
                        <?php echo form_input('unit_number', $customer->unit_number, 'class="form-control" id="unit_number"'); ?>

                    </div>
                    <div class="form-group">
                        <?= lang('Additional Number', 'additional_number'); ?>
                        <?php echo form_input('additional_number', $customer->additional_number, 'class="form-control" id="additional_number"'); ?>

                    </div>
                    <div class="form-group">
                        <?= lang('Promessory Note Amount', 'promessory_note_amount'); ?>
                        <?php echo form_input('promessory_note_amount', $customer->promessory_note_amount, 'class="form-control" id="promessory_note_amount"'); ?>
                    </div>
                    
                    <?php 
                    $sm[''] = '';
                    foreach ($company_sales_man as $sales_man) {
                        $sm[$sales_man->name] = $sales_man->name;
                    }
                    ?>
                    <div class="form-group">
                        <?= lang('Sales Man', 'sales_man'); ?>
                        <?php echo form_dropdown('sales_agent', $sm, $customer->sales_agent, 'class="form-control select" id="sales_agent" required="required"'); ?>
                    </div>
                    <?php
                        $sfda_certificate = array('yes' => 'yes', 'no' => 'no');
                    ?>
                    <div class="form-group">
                        <?= lang('SFDA Certificate', 'sfda_certificate'); ?>
                        <?php echo form_dropdown('sfda_certificate', $sfda_certificate, $customer->sfda_certificate, 'class="form-control select" id="sfda_certificate" required="required"'); ?>
                    </div>
                </div>
            </div>
            <!--<div class="form-group">
                <?= lang('award_points', 'award_points'); ?>
                <?= form_input('award_points', set_value('award_points', $customer->award_points), 'class="form-control tip" id="award_points"  required="required"'); ?>
            </div>-->

        </div>
        <div class="modal-footer">
            <?php echo form_submit('edit_customer', lang('edit_customer'), 'class="btn btn-primary"'); ?>
        </div>
    </div>
    <?php echo form_close(); ?>
</div>
<!-- <?= $modal_js ?> -->
<script type="text/javascript">
    $(document).ready(function(e) {
        $('#crud-customer-form').bootstrapValidator({
            feedbackIcons: {
                valid: 'fa fa-check',
                invalid: 'fa fa-times',
                validating: 'fa fa-refresh'
            },
            excluded: [':disabled'],
            fields: {
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
        $('select.select').select2({
            minimumResultsForSearch: 7
        });
        $('select.ledger-dropdown').select2({
            minimumResultsForSearch: 7
        });

        // Handle category change to auto-populate ledgers
        $('#category').on('change', function() {
            var category = $(this).val();
            
            if (!category) {
                return;
            }

            // Show loading indicator
            var ledgerFields = ['ledger_account', 'sales_ledger', 'cogs_ledger', 'discount_ledger', 'return_ledger'];
            $.each(ledgerFields, function(index, fieldId) {
                $('#' + fieldId).prop('disabled', true);
            });

            // AJAX call to get ledgers by category
            $.ajax({
                url: '<?= admin_url('customers/get_ledgers_by_category'); ?>',
                type: 'POST',
                dataType: 'json',
                data: {
                    category: category,
                    <?= $this->security->get_csrf_token_name(); ?>: '<?= $this->security->get_csrf_hash(); ?>'
                },
                success: function(response) {
                    // Re-enable fields
                    $.each(ledgerFields, function(index, fieldId) {
                        $('#' + fieldId).prop('disabled', false);
                    });

                    if (response.success && response.ledgers) {
                        // Populate ledger dropdowns
                        $.each(response.ledgers, function(ledgerField, ledgerValue) {
                            if (ledgerValue) {
                                $('#' + ledgerField).val(ledgerValue).trigger('change');
                                // Revalidate the field
                                $('form[data-toggle="validator"]').bootstrapValidator('revalidateField', ledgerField);
                            }
                        });

                        // Show success notification
                        toastr.success('Ledgers auto-populated based on category selection', 'Success');
                    } else {
                        // Show info message
                        toastr.info('No previous customer found with this category. Please select ledgers manually.', 'Info');
                    }
                },
                error: function(xhr, status, error) {
                    // Re-enable fields
                    $.each(ledgerFields, function(index, fieldId) {
                        $('#' + fieldId).prop('disabled', false);
                    });
                    
                    console.error('Error fetching ledgers:', error);
                    toastr.error('Failed to fetch ledgers. Please select manually.', 'Error');
                }
            });
        });

        fields = $('.modal-content').find('.form-control');
        $.each(fields, function() {
            var id = $(this).attr('id');
            var iname = $(this).attr('name');
            var iid = '#' + id;
            if (!!$(this).attr('data-bv-notempty') || !!$(this).attr('required')) {
                $("label[for='" + id + "']").append(' *');
                $(document).on('change', iid, function() {
                    $('form[data-toggle="validator"]').bootstrapValidator('revalidateField', iname);
                });
            }
        });
    });
</script>