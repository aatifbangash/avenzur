<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<script>
    $(document).ready(function () {

        if (!localStorage.getItem('psdate')) {
            $("#psdate").datetimepicker({
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

        $(document).on('change', '#psdate', function (e) {
            localStorage.setItem('psdate', $(this).val());
        });
        if (psdate = localStorage.getItem('psdate')) {
            $('#psdate').val(psdate);
        }

        $(document).on('change', '#psref', function (e) {
            localStorage.setItem('psref', $(this).val());
        });
        if (psref = localStorage.getItem('psref')) {
            $('#psref').val(psref);
        }

        $(document).on('change', '#pspayment', function (e) {
            localStorage.setItem('pspayment', $(this).val());

            loadInvoices($('#pssupplier').val());
        });
        if (pspayment = localStorage.getItem('pspayment')) {
            $('#pspayment').val(pspayment);

            loadInvoices($('#pssupplier').val());
        }

        $(document).on('change', '#pssupplier', function (e) {
            localStorage.setItem('pssupplier', $(this).val());

            loadInvoices($('#pssupplier').val());
        });

        if (pssupplier = localStorage.getItem('pssupplier')) {
            $('#pssupplier').val(pssupplier);

            loadInvoices($('#pssupplier').val());
        }

        $(document).on('change', '#psledger', function (e) {
            localStorage.setItem('psledger', $(this).val());
        });
        if (psledger = localStorage.getItem('psledger')) {
            $('#psledger').val(psledger);
        }

        $(document).on('change', '#psbankcharges', function (e) {
            localStorage.setItem('psbankcharges', $(this).val());
        });
        if (psbankcharges = localStorage.getItem('psbankcharges')) {
            $('#psbankcharges').val(psbankcharges);
        }

        $(document).on('change', '#psbankchargesamt', function (e) {
            localStorage.setItem('psbankchargesamt', $(this).val());
        });
        if (psbankchargesamt = localStorage.getItem('psbankchargesamt')) {
            $('#psbankchargesamt').val(psbankchargesamt);
        }

        $(document).on('change', '#psnote', function (e) {
            localStorage.setItem('psnote', $(this).val());
        });
        if (psnote = localStorage.getItem('psnote')) {
            $('#psnote').val(psnote);
        }

        function loadInvoices(supplier_id){
            var v = supplier_id;
            var payment_amount = parseFloat($('#pspayment').val());
            if (supplier_id) {
                $.ajax({
                    url: '<?= admin_url('suppliers/pending_invoices?supplier_id=') ?>' + v,
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        <?= $this->security->get_csrf_token_name() ?>: '<?= $this->security->get_csrf_hash() ?>'
                    },
                    success: function (response) {
                        $('#poTable tbody').empty();
                        if(response == null){
                            
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
                                tr_html += '<td>'+due_amount.toFixed(2)+'<input name="due_amount[]" data-item-id="' + response[i].id + '" value="'+due_amount+'" type="hidden" class="rid" /></td>';
                                tr_html += '<td><input name="payment_amount[]" data-item-id="' + response[i].id + '" value="'+to_pay.toFixed(2)+'" type="text" class="rid" /><input name="item_id[]" type="hidden" value="' + response[i].id + '"></td>';
                                newTr.html(tr_html);
                                newTr.prependTo('#poTable');
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
        if (localStorage.getItem('psdate')) {
            localStorage.removeItem('psdate');
            $('#psdate').val('');
        }

        if (localStorage.getItem('psref')) {
            localStorage.removeItem('psref');
            $('#psref').val('');
        }

        if (localStorage.getItem('pspayment')) {
            localStorage.removeItem('pspayment');
            $('#pspayment').val('');
        }

        if (localStorage.getItem('pssupplier')) {
            localStorage.removeItem('pssupplier');
            $('#pssupplier').val('');
        }

        if (localStorage.getItem('psledger')) {
            localStorage.removeItem('psledger');
            $('#psledger').val('');
        }

        if (localStorage.getItem('psbankcharges')) {
            localStorage.removeItem('psbankcharges');
            $('#psbankcharges').val('');
        }

        if (localStorage.getItem('psbankchargesamt')) {
            localStorage.removeItem('psbankchargesamt');
            $('#psbankchargesamt').val('');
        }

        window.location.reload();
    }
</script>

<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-info-circle"></i><?= lang('supplier_payments'); ?></h2>

        <div class="box-icon">
            <ul class="btn-tasks">
                
            </ul>
        </div>
    </div>
    <div class="box-content">
        <div class="row">
            <?php
            $attrib = ['data-toggle' => 'validator', 'role' => 'form'];
            echo admin_form_open_multipart('suppliers/add_payment', $attrib)
            ?>
            <div class="col-lg-12">
                
                <div class="row">
                    <div class="col-lg-12">
                        <?php if ($Owner || $Admin) {
                            ?>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <?= lang('date', 'psdate'); ?>
                                        <?php echo form_input('date', ($_POST['date'] ?? ''), 'class="form-control input-tip date" id="psdate" required="required"'); ?>
                                    </div>
                                </div>
                            <?php
                        } ?>

                        <div class="col-md-4">
                            <div class="form-group">
                                <?= lang('reference_no', 'psref'); ?>
                                <?php echo form_input('reference_no', ($_POST['reference_no'] ?? $_POST['reference_no']), 'class="form-control input-tip" id="psref"'); ?>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                            <?= lang('supplier', 'pssupplier'); ?>
                            <?php
                            $sp[''] = '';
                            foreach ($suppliers as $supplier) {
                                $sp[$supplier->id] = $supplier->company. ' ('. $supplier->name.')';
                            }
                            echo form_dropdown('supplier', $sp, '', 'id="pssupplier" class="form-control input-tip select" data-placeholder="' . lang('select') . ' ' . lang('supplier') . '" required="required" style="width:100%;" '); ?>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <?= lang('Payment Amount', 'pspayment'); ?>
                                <?php echo form_input('payment_total', ($_POST['payment_amount'] ?? $_POST['payment_total']), 'class="form-control input-tip" id="pspayment"'); ?>
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
                            <?= lang('Transfer From', 'psledger'); ?>
                            <?php 

                                echo form_dropdown('ledger_account', $LO, ($_POST['ledger_account'] ?? $purchase->ledger_account), 'id="psledger" class="ledger-dropdown form-control" required="required"',$DIS);  

                            ?>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                            <?= lang('Bank Charges', 'psbankcharges'); ?>
                            <?php 

                                echo form_dropdown('bank_charges_account', $LO, ($_POST['bank_charges_account'] ?? $purchase->bank_charges_account), 'id="psbankcharges" class="ledger-dropdown form-control" required="required"',$DIS);  

                            ?>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <?= lang('Bank Charges Amount', 'psbankchargesamt'); ?>
                                <?php echo form_input('bank_charges', ($_POST['bank_charges'] ?? $_POST['bank_charges']), 'class="form-control input-tip" id="psbankchargesamt"'); ?>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <?= lang('Note', 'psnote'); ?>
                                <?php echo form_input('note', ($_POST['note'] ?? $_POST['note']), 'class="form-control input-tip" id="psnote"'); ?>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="from-group">
                                <button type="submit" style="margin-top: 28px;" class="btn btn-primary" id="add_payment"><?= lang('Add Payments') ?></button>
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

