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
        <h2 class="blue"><i class="fa-fw fa fa-info-circle"></i><?= lang('supplier_advance'); ?></h2>

        <div class="box-icon">
            <ul class="btn-tasks">
                
            </ul>
        </div>
    </div>
    <div class="box-content">
        <div class="row">
            <?php
            $attrib = ['data-toggle' => 'validator', 'role' => 'form'];
            echo admin_form_open_multipart('suppliers/advance_to_supplier', $attrib)
            ?>
            <div class="col-lg-12">
                
                <div class="row">
                    <div class="col-lg-12">
                        <?php if ($Owner || $Admin) {
                            ?>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <?= lang('date', 'podate'); ?>
                                        <?php echo form_input('date', ($memo_data->date ?? date("d/m/Y H:i")), 'class="form-control input-tip date" id="podate"'); ?>
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
                                <?= lang('Payment Amount', 'poref'); ?>
                                <?php echo form_input('payment_total', ($memo_data->payment_amount ?? $memo_data->payment_total), 'class="form-control input-tip" id="payment_amount"'); ?>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                            <?= lang('supplier', 'posupplier'); ?>
                            <?php
                            $sp[''] = '';
                            foreach ($suppliers as $supplier) {
                                $sp[$supplier->id] = $supplier->company. ' ('. $supplier->name.')';
                            }
                            echo form_dropdown('supplier', $sp, ($memo_data->supplier_id ?? $memo_data->supplier_id), 'id="supplier_id" class="form-control input-tip select" data-placeholder="' . lang('select') . ' ' . lang('supplier') . '" required="required" style="width:100%;" '); ?>
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
                            <?= lang('Bank Charges', 'posupplier'); ?>
                            <?php 

                                echo form_dropdown('bank_charges_account', $LO, ($memo_data->bank_charges_account ?? $memo_data->bank_charges_account), 'id="bank_charges_account" class="ledger-dropdown form-control" required="required"',$DIS);  

                            ?>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <?= lang('Bank Charges Amount', 'poref'); ?>
                                <?php echo form_input('bank_charges', ($memo_data->bank_charges ?? $memo_data->bank_charges), 'class="form-control input-tip" id="bank_charges"'); ?>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="from-group">
                                <button type="submit" style="margin-top: 28px;" class="btn btn-primary" id="add_payment"><?= lang('Add Payments') ?></button>
                            </div>
                        </div>
                    </div>



                    <div class="controls table-controls" style="font-size: 12px !important;">
                        <table id="poTable"
                                class="table items table-striped table-bordered table-condensed table-hover sortable_table">
                            <thead>
                            <tr>
                                <th><?php echo $this->lang->line('Description'); ?></th>
                                <th><?php echo $this->lang->line('Payment Amount') ?></th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php 
                                    if(isset($memo_entries_data) && !empty($memo_entries_data)){
                                        foreach($memo_entries_data as $memo){
                                        ?>
                                            <tr>
                                                <td><input type="text" placeholder="Enter Description" class="form-control" value="<?= $memo->description; ?>" name="description[]" /></td>
                                                <td><input type="text" placeholder="Enter Amount" class="form-control" value="<?= $memo->payment_amount; ?>" name="payment_amount[]" /></td>
                                            </tr>
                                        <?php
                                        }
                                    }else{
                                        ?>
                                        <tr>
                                            <td><input type="text" placeholder="Enter Description" class="form-control" name="description[]" /></td>
                                            <td><input type="text" placeholder="Enter Amount" class="form-control" name="payment_amount[]" /></td>
                                        </tr>
                                        <?php
                                    }
                                ?>
                            </tbody>
                            <tfoot></tfoot>
                        </table>
                        <button id="addRowBtn" class="btn btn-primary mt-2">+</button>
                        <?php 
                            if(isset($memo_entries_data) && !empty($memo_entries_data)){
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

