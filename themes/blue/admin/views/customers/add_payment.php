<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<script>
    var payment_amount = 0;

    $(document).ready(function () {

        if (!localStorage.getItem('csdate')) {
            $("#csdate").datetimepicker({
                format: site.dateFormats.js_ldate,
                fontAwesome: true,
                language: 'sma',
                weekStart: 1,
                todayBtn: 1,
                autoclose: 1,
                todayHighlight: 1,
                startView: 2,
                forceParse: 0
            }).datetimepicker('update', new Date());
        }

        $(document).on('change', '#csdate', function (e) {
            localStorage.setItem('csdate', $(this).val());
        });
        if (csdate = localStorage.getItem('csdate')) {
            $('#csdate').val(csdate);
        }

        $(document).on('change', '#csref', function (e) {
            localStorage.setItem('csref', $(this).val());
        });
        if (csref = localStorage.getItem('csref')) {
            $('#csref').val(csref);
        }

        $(document).on('change', '#cspayment', function (e) {
            localStorage.setItem('cspayment', $(this).val());

            loadInvoices($('#cscustomer').val());
        });
        if (cspayment = localStorage.getItem('cspayment')) {
            $('#cspayment').val(cspayment);

            loadInvoices($('#cscustomer').val());
        }

        $(document).on('change', '#cscustomer', function (e) {
            localStorage.setItem('cscustomer', $(this).val());

            loadInvoices($('#cscustomer').val());
        });

        if (cscustomer = localStorage.getItem('cscustomer')) {
            $('#cscustomer').val(cscustomer);

            loadInvoices($('#cscustomer').val());
        }

        $(document).on('change', '#csledger', function (e) {
            localStorage.setItem('csledger', $(this).val());
        });
        if (csledger = localStorage.getItem('csledger')) {
            $('#csledger').val(csledger);
        }

        $(document).on('change', '#csnote', function (e) {
            localStorage.setItem('csnote', $(this).val());
        });
        if (csnote = localStorage.getItem('csnote')) {
            $('#csnote').val(csnote);
        }

        function loadInvoices(customer_id){
            var v = customer_id;
            var payment_amount = parseFloat($('#cspayment').val());

            if (customer_id) {
                $.ajax({
                    url: '<?= admin_url('customers/pending_invoices?customer_id=') ?>' + v,
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        <?= $this->security->get_csrf_token_name() ?>: '<?= $this->security->get_csrf_hash() ?>'
                    },
                    success: function (response) {
                        $('#poTable tbody').empty();
                        if(response == null || response.length == 0){
                            var newTr = $('<tr class="row"><td colspan="4">No Pending Invoices Found</td></tr>');
                            newTr.prependTo('#poTable');

                        }else{
                            total_due = 0;
                            total_amt = 0;
                            total_paying = 0;
                            for(var i=0;i<response.length;i++){
                                var purchase_date = response[i].date;
                                var reference_id = response[i].reference_no;
                                var total_amount = parseFloat(response[i].grand_total);
                                var paid_amount = parseFloat(response[i].paid);
                                var due_amount = parseFloat(response[i].grand_total) - parseFloat(response[i].paid);
                                var to_pay = 0;

                                total_due += parseFloat(due_amount);
                                total_amt += parseFloat(total_amount);

                                if(payment_amount > due_amount){
                                    payment_amount = payment_amount - due_amount;
                                    to_pay = due_amount;
                                }else if(payment_amount <= due_amount){
                                    to_pay = payment_amount;
                                    payment_amount = 0;
                                }else{
                                    payment_amount = 0;
                                    to_pay = 0;
                                }

                                total_paying += parseFloat(to_pay);

                                var newTr = $('<tr id="row_' + response[i].id + '" class="row_' + response[i].id + '" data-item-id="' + response[i].id + '"></tr>');
                                tr_html = '<td>'+purchase_date+'</td>';
                                tr_html += '<td>'+reference_id+'</td>';
                                tr_html += '<td>'+total_amount.toFixed(2)+'</td>';
                                tr_html += '<td>'+due_amount.toFixed(2)+'<input name="due_amount[]" data-item-id="' + response[i].id + '" value="'+due_amount.toFixed(2)+'" type="hidden" class="rid" /></td>';
                                tr_html += '<td><input name="payment_amount[]" data-item-id="' + response[i].id + '" type="text" class="rid" value="'+to_pay.toFixed(2)+'" /><input name="item_id[]" type="hidden" value="' + response[i].id + '"></td>';
                                newTr.html(tr_html);
                                newTr.appendTo('#poTable');
                            }

                            var newTr = $('<tr class="row"></tr>');
                                tr_html = '<td colspan="1"><b>Totals:</b> </td>';
                                tr_html += '<td><b>'+total_amt.toFixed(2)+'</b></td>';
                                tr_html += '<td><b>'+total_due.toFixed(2)+'</b></td>';
                                tr_html += '<td><b>'+total_paying.toFixed(2)+'</b></td>';
                                newTr.html(tr_html);
                                newTr.appendTo('#poTable');
                        }
                    }
                });
            }
        }
    });

    function resetValues(){
        if (localStorage.getItem('csdate')) {
            localStorage.removeItem('csdate');
            $('#csdate').val('');
        }

        if (localStorage.getItem('csref')) {
            localStorage.removeItem('csref');
            $('#csref').val('');
        }

        if (localStorage.getItem('cspayment')) {
            localStorage.removeItem('cspayment');
            $('#cspayment').val('');
        }

        if (localStorage.getItem('cscustomer')) {
            localStorage.removeItem('cscustomer');
            $('#cscustomer').val('');
        }

        if (localStorage.getItem('csledger')) {
            localStorage.removeItem('csledger');
            $('#csledger').val('');
        }

        window.location.reload();
    }
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
                                        <?= lang('date', 'csdate'); ?>
                                        <?php echo form_input('date', ($_POST['date'] ?? ''), 'class="form-control input-tip date" id="csdate" required="required"'); ?>
                                    </div>
                                </div>
                            <?php
                        } ?>

                        <div class="col-md-4">
                            <div class="form-group">
                                <?= lang('reference_no', 'csref'); ?>
                                <?php echo form_input('reference_no', ($_POST['reference_no'] ?? $_POST['reference_no']), 'class="form-control input-tip" id="csref"'); ?>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                            <?= lang('customer', 'cscustomer'); ?>
                            <?php
                            $sp[''] = '';
                            foreach ($customers as $customer) {
                                $sp[$customer->id] = $customer->company. ' ('. $customer->name.')';
                            }
                            echo form_dropdown('customer', $sp, '', 'id="cscustomer" class="form-control input-tip select" data-placeholder="' . lang('select') . ' ' . lang('customer') . '" required="required" style="width:100%;" '); ?>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <?= lang('Invoice Amount', 'cspayment'); ?>
                                <?php echo form_input('payment_total', ($_POST['payment_amount'] ?? $_POST['payment_total']), 'class="form-control input-tip" id="cspayment"'); ?>
                            </div>
                        </div>
                            
                        <div class="col-md-4">
                            <div class="form-group">
                            <?= lang('Transfer To', 'csledger'); ?>
                            <?php 

                                echo form_dropdown('ledger_account', $LO, ($_POST['ledger_account'] ?? $purchase->ledger_account), 'id="csledger" class="ledger-dropdown form-control" required="required"',$DIS);  

                            ?>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <?= lang('Note', 'csnote'); ?>
                                <?php echo form_input('note', ($_POST['note'] ?? $_POST['note']), 'class="form-control input-tip" id="csnote"'); ?>
                            </div>
                        </div>

                        <div class="col-md-4" style="margin-bottom: 20px;">
                            <div class="from-group">
                                <button type="submit" style="margin-top: 28px;" class="btn btn-primary" id="add_payment"><?= lang('Receive Payments') ?></button>
                                <button type="button" style="margin-top: 28px;" class="btn btn-danger" id="reset" onclick="resetValues();"><?= lang('reset') ?></button>
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

