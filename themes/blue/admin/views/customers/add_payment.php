<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<script>
    $(document).ready(function () {

        $('#customer_id').change(function () {
            var v = $(this).val();
            if (v) {
                $.ajax({
                    url: '<?= admin_url('customers/pending_invoices?customer_id=') ?>' + v,
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        <?= $this->security->get_csrf_token_name() ?>: '<?= $this->security->get_csrf_hash() ?>'
                    },
                    success: function (response) {
                        $('#poTable tbody').empty();
                        for(var i=0;i<response.length;i++){
                            var purchase_date = response[i].date;
                            var reference_id = response[i].reference_no;
                            var total_amount = response[i].grand_total;
                            var paid_amount = response[i].paid;
                            var due_amount = response[i].grand_total - response[i].paid;
                            var newTr = $('<tr id="row_' + response[i].id + '" class="row_' + response[i].id + '" data-item-id="' + response[i].id + '"></tr>');
                            tr_html = '<td>'+purchase_date+'</td>';
                            tr_html += '<td>'+reference_id+'</td>';
                            tr_html += '<td>'+total_amount+'</td>';
                            tr_html += '<td>'+due_amount+'<input name="due_amount[]" data-item-id="' + response[i].id + '" value="'+due_amount+'" type="hidden" class="rid" /></td>';
                            tr_html += '<td><input name="payment_amount[]" data-item-id="' + response[i].id + '" type="text" class="rid" /><input name="item_id[]" type="hidden" value="' + response[i].id + '"></td>';
                            newTr.html(tr_html);
                            newTr.prependTo('#poTable');
                        }
                    }
                });
            }
        });
    });
</script>

<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-info-circle"></i><?= lang('customer_payments'); ?></h2>

        <div class="box-icon">
            <ul class="btn-tasks">
                
            </ul>
        </div>
    </div>
    <div class="box-content">
        <div class="row">
            <?php
            $attrib = ['data-toggle' => 'validator', 'role' => 'form'];
            echo admin_form_open_multipart('customers/payment_from_customer', $attrib)
            ?>
            <div class="col-lg-12">
                
                <div class="row">
                    <div class="col-lg-12">
                        <?php if ($Owner || $Admin) {
                            ?>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <?= lang('date', 'podate'); ?>
                                        <?php echo form_input('date', ($_POST['date'] ?? ''), 'class="form-control input-tip date" id="podate" required="required"'); ?>
                                    </div>
                                </div>
                            <?php
                        } ?>

                        <div class="col-md-4">
                            <div class="form-group">
                                <?= lang('reference_no', 'poref'); ?>
                                <?php echo form_input('reference_no', ($_POST['reference_no'] ?? $_POST['reference_no']), 'class="form-control input-tip" id="poref"'); ?>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <?= lang('Invoice Amount', 'poref'); ?>
                                <?php echo form_input('payment_total', ($_POST['payment_amount'] ?? $_POST['payment_total']), 'class="form-control input-tip" id="payment_amount"'); ?>
                            </div>
                        </div>

                        <?php if ($Owner || $Admin || !$this->session->userdata('warehouse_id')) {
                        ?>
                            <!--<div class="col-md-4">
                                <div class="form-group">-->
                                <?php //echo lang('warehouse', 'powarehouse'); ?>
                                <?php
                                //$wh[''] = '';
                                //foreach ($warehouses as $warehouse) {
                                //    $wh[$warehouse->id] = $warehouse->name;
                                //}
                                //echo form_dropdown('warehouse', $wh, ($_POST['warehouse'] ?? $Settings->default_warehouse), 'id="powarehouse" class="form-control input-tip select" data-placeholder="' . lang('select') . ' ' . lang('warehouse') . '" required="required" style="width:100%;" '); ?>
                                <!--</div>
                            </div>-->
                            <?php
                        } else {
                            /*$warehouse_input = [
                                'type'  => 'hidden',
                                'name'  => 'warehouse',
                                'id'    => 'slwarehouse',
                                'value' => $this->session->userdata('warehouse_id'),
                            ];

                            echo form_input($warehouse_input);*/
                        }

                        /*$warehouse_status = [
                            'type'  => 'hidden',
                            'name'  => 'status',
                            'id'    => 'postatus',
                            'value' => 'pending',
                        ];

                        echo form_input($warehouse_status);*/
                        ?>


                        <div class="col-md-4">
                            <div class="form-group">
                            <?= lang('customer', 'pocustomer'); ?>
                            <?php
                            $sp[''] = '';
                            foreach ($customers as $customer) {
                                $sp[$customer->id] = $customer->company. ' ('. $customer->name.')';
                            }
                            echo form_dropdown('customer', $sp, '', 'id="customer_id" class="form-control input-tip select" data-placeholder="' . lang('select') . ' ' . lang('customer') . '" required="required" style="width:100%;" '); ?>
                            </div>
                        </div>
                            
                        <div class="col-md-4">
                            <div class="form-group">
                            <?= lang('Transfer To', 'pocustomer'); ?>
                            <?php 

                                echo form_dropdown('ledger_account', $LO, ($_POST['ledger_account'] ?? $purchase->ledger_account), 'id="ledger_account" class="ledger-dropdown form-control" required="required"',$DIS);  

                            ?>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <?= lang('Vat', 'poref'); ?>
                                <?php echo form_input('vat_charges', ($_POST['vat_charges'] ?? $_POST['vat_charges']), 'class="form-control input-tip" id="vat_charges"'); ?>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                            <?= lang('Vat Account', 'pocustomer'); ?>
                            <?php 

                                echo form_dropdown('vat_account', $LO, ($_POST['vat_account'] ?? $purchase->vat_account), 'id="vat_account" class="ledger-dropdown form-control" required="required"',$DIS);  

                            ?>
                            </div>
                        </div>

                        <div class="col-md-4" style="margin-bottom: 20px;">
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
                                <th><?php echo $this->lang->line('Date'); ?></th>
                                <th><?php echo $this->lang->line('Reference no') ?></th>
                                <th><?php echo $this->lang->line('Orig. Amt.') ?></th>
                                <th><?php echo $this->lang->line('Amt. Due.'); ?></th>
                                <th><?php echo $this->lang->line('Payment'); ?></th>
                            </tr>
                            </thead>
                            <tbody></tbody>
                            <tfoot></tfoot>
                        </table>
                    </div>
                
            </div>

            <?php echo form_close(); ?>

        </div>
    </div>
</div>

