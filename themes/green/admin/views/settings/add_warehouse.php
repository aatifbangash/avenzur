<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<style>
    .ledgers_group {
        display: none;
    }
</style>
<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?php echo lang('add_warehouse'); ?></h4>
        </div>
        <?php $attrib = ['data-toggle' => 'validator', 'role' => 'form', 'id' => "crud-warehouse-form"];
        echo admin_form_open_multipart('system_settings/add_warehouse', $attrib); ?>
        <div class="modal-body">
            <p><?= lang('enter_info'); ?></p>

            <div class="form-group">
                <label class="control-label" for="code"><?php echo $this->lang->line('code'); ?></label>
                <?php echo form_input('code', '', 'class="form-control" id="code" required="required"'); ?>
            </div>
            <div class="form-group">
                <label class="control-label" for="name"><?php echo $this->lang->line('name'); ?></label>
                <?php echo form_input('name', '', 'class="form-control" id="name" required="required"'); ?>
            </div>
            <div class="form-group">
                <!--<label class="control-label" for="price_group"><?php echo $this->lang->line('price_group'); ?></label>-->
                <?php
                //  $pgs[''] = lang('select') . ' ' . lang('price_group');
                //  foreach ($price_groups as $price_group) {
                //      $pgs[$price_group->id] = $price_group->name;
                //  }
                //  echo form_dropdown('price_group', $pgs, $Settings->price_group, 'class="form-control tip select" id="price_group" style="width:100%;"');
                ?>
            </div>
            <div class="form-group">
                <label class="control-label" for="phone"><?php echo $this->lang->line('phone'); ?></label>
                <?php echo form_input('phone', '', 'class="form-control" id="phone"'); ?>
            </div>
            <div class="form-group">
                <label class="control-label" for="email"><?php echo $this->lang->line('email'); ?></label>
                <?php echo form_input('email', '', 'class="form-control" id="email"'); ?>
            </div>
            <div class="form-group">
                <label class="control-label" for="address"><?php echo $this->lang->line('address'); ?></label>
                <?php echo form_textarea('address', '', 'class="form-control" id="address" required="required"'); ?>
            </div>
            <div class="form-group">
                <label class="control-label" for="type"><?php echo $this->lang->line('Type'); ?></label>
                <select class="form-control" id="type" name="type" onchange="loadLedgers(this);">
                    <option value="warehouse">Warehouse</option>
                    <option value="pharmacy">Pharmacy</option>
                </select>
            </div>
            <div class="form-group">
                <label class="control-label" for="country"><?php echo $this->lang->line('country'); ?></label>
                <select class="form-control" id="country" name="country">
                    <!--<option value="both">Both</option>-->

                    <?php
                    foreach ($country as $country) {
                        echo '<option value="' . $country->id . '">' . $country->name . '</option>';
                    }
                    ?>
                </select>
                <?php
                //  $pgss[''] = lang('select') . ' ' . lang('country');
                //  foreach ($country as $country) {
                //      $pgss[$country->name] = $country->name;
                //  }
                //  echo form_dropdown('country', $pgss, 'class="form-control" id="country" ');
                ?>
            </div>

            <div class="form-group">
                <?= lang('Inventory Account', 'inventory_ledger'); ?>
                <?php

                echo form_dropdown('inventory_ledger', $LO, '', 'id="inventory_ledger" class="ledger-dropdown form-control" required="required"', $DIS);
                ?>
            </div>



            <div class="form-group ledgers_group">
                <?= lang('Fund Books Account', 'fund_books_ledger'); ?>
                <?php

                echo form_dropdown('fund_books_ledger', $LO, '', 'id="fund_books_ledger" class="ledger-dropdown form-control" ', $DIS);
                ?>
            </div>
            <div class="form-group ledgers_group">
                <?= lang('Cogs Account', 'cogs_ledger'); ?>
                <?php

                echo form_dropdown('cogs_ledger', $LO, '', 'id="cogs_ledger" class="ledger-dropdown form-control" ', $DIS);
                ?>
            </div>
            <div class="form-group ledgers_group">
                <?= lang('Sales Account', 'sales_ledger'); ?>
                <?php

                echo form_dropdown('sales_ledger', $LO, '', 'id="sales_ledger" class="ledger-dropdown form-control" ', $DIS);
                ?>
            </div>
            <div class="form-group ledgers_group">
                <?= lang('Discount Account', 'discount_ledger'); ?>
                <?php

                echo form_dropdown('discount_ledger', $LO, '', 'id="discount_ledger" class="ledger-dropdown form-control" ', $DIS);
                ?>
            </div>

            <div class="form-group ledgers_group">
                <?= lang('Credit Card Account', 'credit_card_ledger'); ?>
                <?php

                echo form_dropdown('credit_card_ledger', $LO, '', 'id="credit_card_ledger" class="ledger-dropdown form-control" ', $DIS);
                ?>
            </div>

            <div class="form-group ledgers_group">
                <?php echo lang('Halala Account', 'Halala Account'); 
                ?>
                <?php

                echo form_dropdown('price_difference_ledger', $LO,'', 'id="price_difference_ledger" class="ledger-dropdown form-control" required="required"',$DIS);  
                ?>
            </div>
            <div class="form-group ledgers_group">
                <?= lang('Vat Account', 'vat_on_sales_ledger'); ?>
                <?php

                echo form_dropdown('vat_on_sales_ledger', $LO, '', 'id="vat_on_sales_ledger" class="ledger-dropdown form-control"', $DIS);
                ?>
            </div>

            <!--<div class="form-group">-->
            <!--    <?= lang('warehouse_map', 'image') ?>-->
            <!--    <input id="image" type="file" data-browse-label="<?= lang('browse'); ?>" name="userfile" data-show-upload="false" data-show-preview="false"-->
            <!--           class="form-control file">-->
            <!--</div>-->
        </div>
        <div class="modal-footer">
            <?php echo form_submit('add_warehouse', lang('add_warehouse'), 'class="btn btn-primary"'); ?>
        </div>
    </div>
    <?php echo form_close(); ?>
</div>
<script type="text/javascript" src="<?= $assets ?>js/custom.js"></script>
<script type="text/javascript">
    function loadLedgers(obj) {
        return true;
        // if (obj.value == 'pharmacy') {
        //     var elements = document.getElementsByClassName('ledgers_group');
        //     for (var i = 0; i < elements.length; i++) {
        //         elements[i].style.display = "block";
        //     }
        // } else {
        //     var elements = document.getElementsByClassName('ledgers_group');
        //     for (var i = 0; i < elements.length; i++) {
        //         elements[i].style.display = "none";
        //     }
        // }
    }
</script>
<!-- <?= $modal_js ?> -->

<script type="text/javascript">
    $(document).ready(function(e) {

        var validator = $('#crud-warehouse-form').bootstrapValidator({
            feedbackIcons: {
                valid: 'fa fa-check',
                invalid: 'fa fa-times',
                validating: 'fa fa-refresh'
            },
            excluded: [':disabled'],
            fields: {

            }
        }).data('bootstrapValidator');
        updateValidationRules('warehouse');

        $('select.select').select2({
            minimumResultsForSearch: 7
        });
        $('select.ledger-dropdown').select2({
            minimumResultsForSearch: 7
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

        $('#type').on('change', function() {
            var selectedValue = $(this).val();

            if (selectedValue == 'pharmacy') {
                $('.ledgers_group').show();
                $('.ledgers_group select.select2').prop('disabled', false).trigger('change');
            } else {
                $('.ledgers_group').hide();
                $('.ledgers_group select.select2').prop('disabled', true).trigger('change');
            }
            // Reset the form
            validator.resetForm();
            validator = $('#crud-warehouse-form').data('bootstrapValidator');
            updateValidationRules(selectedValue);
        });

        function updateValidationRules(selectedValue) {

            $('#crud-warehouse-form').bootstrapValidator('resetForm');
            var dynamicFields = {};

            if (selectedValue === 'pharmacy') {
                dynamicFields = {
                    inventory_ledger: {
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
                    fund_books_ledger: {
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
                    credit_card_ledger: {
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
                    vat_on_sales_ledger: {
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
                };
            } else if (selectedValue === 'warehouse') {

                dynamicFields = {
                    inventory_ledger: {
                        validators: {
                            notEmpty: {
                                message: 'Please select a Ledger'
                            },
                            callback: {
                                message: 'Please select a valid Ledger',
                                callback: function(value, validator) {
                                    console.log(validator);
                                    return value > 0; // Check if value is greater than 0
                                }
                            }
                        }
                    }
                }

            } else {
                dynamicFields = {
                    inventory_ledger: {
                        validators: {
                            notEmpty: {
                                message: 'Please select a Ledger'
                            }
                        }
                    }
                };
            }

            $.each(dynamicFields, function(fieldName, fieldRules) {
                $('#' + fieldName).attr('data-bv-custome-validate', fieldName);
                validator.addField(fieldName, fieldRules);

            });
        }


    });
</script>