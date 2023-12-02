<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?php echo lang('edit_warehouse'); ?></h4>
        </div>
        <?php $attrib = ['data-toggle' => 'validator', 'role' => 'form', 'id'=>'crud-warehouse-form'];
        echo admin_form_open_multipart('system_settings/edit_warehouse/' . $id, $attrib); ?>
        <div class="modal-body">
            <p><?= lang('enter_info'); ?></p>

            <div class="form-group">
                <label class="control-label" for="code"><?php echo $this->lang->line('code'); ?></label>
                <?php echo form_input('code', $warehouse->code, 'class="form-control" id="code" required="required"'); ?>
            </div>
            <div class="form-group">
                <label class="control-label" for="name"><?php echo $this->lang->line('name'); ?></label>
                <?php echo form_input('name', $warehouse->name, 'class="form-control" id="name" required="required"'); ?>
            </div>
            <div class="form-group">
                <label class="control-label" for="price_group"><?php echo $this->lang->line('price_group'); ?></label>
                <?php
                $pgs[''] = lang('select') . ' ' . lang('price_group');
                foreach ($price_groups as $price_group) {
                    $pgs[$price_group->id] = $price_group->name;
                }
                echo form_dropdown('price_group', $pgs, $warehouse->price_group_id, 'class="form-control tip select" id="price_group" style="width:100%;"');
                ?>
            </div>
            <div class="form-group">
                <label class="control-label" for="phone"><?php echo $this->lang->line('phone'); ?></label>
                <?php echo form_input('phone', $warehouse->phone, 'class="form-control" id="phone"'); ?>
            </div>
            <div class="form-group">
                <label class="control-label" for="email"><?php echo $this->lang->line('email'); ?></label>
                <?php echo form_input('email', $warehouse->email, 'class="form-control" id="email"'); ?>
            </div>
            <div class="form-group">
                <label class="control-label" for="address"><?php echo $this->lang->line('address'); ?></label>
                <?php echo form_textarea('address', $warehouse->address, 'class="form-control" id="address" required="required"'); ?>
            </div>
            <div class="form-group">
                <label class="control-label" for="type"><?php echo $this->lang->line('type'); ?></label>
                <?php

                $pgs2['warehouse'] = 'Warehouse';
                $pgs2['pharmacy'] = 'Pharmacy';

                echo form_dropdown('type', $pgs2, $warehouse->warehouse_type, 'class="form-control tip select" id="type" style="width:100%;" onchange="loadLedgers(this);"');
                ?>
            </div>
            <div class="form-group">
                <label class="control-label" for="country"><?php echo $this->lang->line('country'); ?></label>
                <?php
                //$pgs1[''] = lang('select') . ' ' . lang('country');
                foreach ($country as $c) {
                    $pgs1[$c->id] = $c->name;
                }
                echo form_dropdown('country', $pgs1, $warehouse->country, 'class="form-control tip select" id="country" style="width:100%;"');
                ?>
            </div>

            <div class="form-group">
                <?= lang('Inventory Account', 'inventory_ledger'); ?>
                <?php
                echo form_dropdown('inventory_ledger', $LO, $warehouse->inventory_ledger, 'id="inventory_ledger" class="ledger-dropdown form-control" required="required"', $DIS);
                ?>
            </div>



            <div class="form-group ledgers_group" style="display: <?= $warehouse->warehouse_type == 'warehouse' ? 'none' : 'block' ?>;">
                <?= lang('Fund Books Account', 'fund_books_ledger'); ?>
                <?php

                echo form_dropdown('fund_books_ledger', $LO, $warehouse->fund_books_ledger, 'id="fund_books_ledger" class="ledger-dropdown form-control" required="required"', $DIS);
                ?>
            </div>
            <div class="form-group ledgers_group" style="display: <?= $warehouse->warehouse_type == 'warehouse' ? 'none' : 'block' ?>;">
                <?= lang('Cogs Account', 'cogs_ledger'); ?>
                <?php

                echo form_dropdown('cogs_ledger', $LO, $warehouse->cogs_ledger, 'id="cogs_ledger" class="ledger-dropdown form-control" required="required"', $DIS);
                ?>
            </div>
            <div class="form-group ledgers_group" style="display: <?= $warehouse->warehouse_type == 'warehouse' ? 'none' : 'block' ?>;">
                <?= lang('Sales Account', 'sales_ledger'); ?>
                <?php

                echo form_dropdown('sales_ledger', $LO, $warehouse->sales_ledger, 'id="sales_ledger" class="ledger-dropdown form-control" required="required"', $DIS);
                ?>
            </div>
            <div class="form-group ledgers_group" style="display: <?= $warehouse->warehouse_type == 'warehouse' ? 'none' : 'block' ?>;">
                <?= lang('Discount Account', 'discount_ledger'); ?>
                <?php

                echo form_dropdown('discount_ledger', $LO, $warehouse->discount_ledger, 'id="discount_ledger" class="ledger-dropdown form-control" required="required"', $DIS);
                ?>
            </div>

            <div class="form-group ledgers_group" style="display: <?= $warehouse->warehouse_type == 'warehouse' ? 'none' : 'block' ?>;">
                <?= lang('Credit Card Account', 'credit_card_ledger'); ?>
                <?php

                echo form_dropdown('credit_card_ledger', $LO, $warehouse->credit_card_ledger, 'id="credit_card_ledger" class="ledger-dropdown form-control" required="required"', $DIS);
                ?>
            </div>

            <!--<div class="form-group ledgers_group" style="display: <?= $warehouse->warehouse_type == 'warehouse' ? 'none' : 'block' ?>;">
                <?php //echo lang('Price Difference Account', 'Price Difference Account'); 
                ?>
                <?php

                //echo form_dropdown('price_difference_ledger', $LO, $warehouse->price_difference_ledger, 'id="price_difference_ledger" class="ledger-dropdown form-control" required="required"',$DIS);  
                ?>
            </div>-->
            <div class="form-group ledgers_group" style="display: <?= $warehouse->warehouse_type == 'warehouse' ? 'none' : 'block' ?>;">
                <?= lang('Vat Account', 'vat_on_sales_ledger'); ?>
                <?php

                echo form_dropdown('vat_on_sales_ledger', $LO, $warehouse->vat_on_sales_ledger, 'id="vat_on_sales_ledger" class="ledger-dropdown form-control" required="required"', $DIS);
                ?>
            </div>

            <!--<div class="form-group">
                <?= lang('warehouse_map', 'image') ?>
                <input id="image" type="file" data-browse-label="<?= lang('browse'); ?>" name="userfile" data-show-upload="false" data-show-preview="false"
                       class="form-control file">
            </div>-->
        </div>
        <div class="modal-footer">
            <?php echo form_submit('edit_warehouse', lang('edit_warehouse'), 'class="btn btn-primary"'); ?>
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