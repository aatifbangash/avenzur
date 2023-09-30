<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<script>
    $(document).ready(function () {
        $('#addRowBtn').click(function() {
            var newRow = '<tr>' +
                '<td><input type="text" placeholder="Enter Description" class="form-control" name="description[]" /></td>' +
                '<td><input type="text" placeholder="Enter Amount" class="form-control" name="payment_amount[]" /></td>' +
                '</tr>';
            $('#poTable tbody').append(newRow);
        });
    });
</script>

<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-info-circle"></i><?= lang("supplier")." ".lang('service_invoice'); ?></h2>

        <div class="box-icon">
            <ul class="btn-tasks">
                
            </ul>
        </div>
    </div>
    <div class="box-content">
        <div class="row">
            <?php
            $attrib = ['data-toggle' => 'validator', 'role' => 'form'];
            echo admin_form_open_multipart('suppliers/service_invoice', $attrib)
            ?>
            <div class="col-lg-12">
                
                <div class="row">
                    <div class="col-lg-12">
                        <?php if ($Owner || $Admin) {
                            ?>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <?= lang('date', 'podate'); ?>
                                        <?php echo form_input('date', ($memo_data->date ?? ''), 'class="form-control input-tip date" id="podate"'); ?>
                                    </div>
                                </div>
                            <?php
                        } ?>

                        <div class="col-md-4">
                            <div class="form-group">
                                <?= lang('reference_no', 'poref'); ?>
                                <?php echo form_input('reference_no', ($memo_data->reference_no ?? $memo_data->reference_no), 'class="form-control input-tip" id="poref"'); ?>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <?= lang('Invoice Amount', 'poref'); ?>
                                <?php echo form_input('payment_total', ($memo_data->payment_amount ?? $memo_data->payment_total), 'class="form-control input-tip" id="payment_amount"'); ?>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                            <?= lang('Supplier', 'poref'); ?>
                            <?php
                            $sp[''] = '';
                            foreach ($suppliers as $supplier) {
                                $sp[$supplier->id] = $supplier->company. ' ('. $supplier->name.')';
                            }
                            echo form_dropdown('supplier', $sp, ($memo_data->supplier_id), 'id="supplier_id" class="form-control input-tip select" data-placeholder="' . lang('select') . ' ' . lang('Supplier') . '" required="required" style="width:100%;" '); ?>
                            </div>
                        </div>
                            
                        <div class="col-md-4">
                            <div class="form-group">
                            <?= lang('Opposite Account', 'posupplier'); ?>
                            <?php 

                                echo form_dropdown('ledger_account', $LO, ($memo_data->ledger_account ?? $memo_data->ledger_account), 'id="ledger_account" class="ledger-dropdown form-control" required="required"',$DIS);  

                            ?>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                            <?= lang('Vat Account', 'posupplier'); ?>
                            <?php 

                                echo form_dropdown('vat_account', $LO, ($memo_data->bank_charges_account ?? $memo_data->bank_charges_account), 'id="vat_account" class="ledger-dropdown form-control" required="required"',$DIS);  

                            ?>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <?= lang('Vat', 'poref'); ?>
                                <?php echo form_input('vat_charges', ($memo_data->bank_charges ?? $memo_data->bank_charges), 'class="form-control input-tip" id="vat_charges"'); ?>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="from-group">
                                <button type="submit" style="margin-top: 28px;" class="btn btn-primary" id="add_payment"><?= lang('Add Invoice') ?></button>
                            </div>
                        </div>
                        <?php 
                            if(isset($memo_data) && !empty($memo_data)){
                                ?>
                                    <input type="hidden" name="memo_id" value="<?= $memo_data->id; ?>" />
                                    <input type="hidden" name="request_type" value="update" />
                                <?php
                            }else{
                                ?>
                                    <input type="hidden" name="request_type" value="add" />
                                <?php
                            }
                        ?>
                    </div>
                
            </div>

            <?php echo form_close(); ?>

        </div>
    </div>
</div>

