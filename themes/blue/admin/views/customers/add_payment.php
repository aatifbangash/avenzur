<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<script>
    // Customer Advance Ledger configuration
    var customer_advance_ledger_configured = <?= isset($customer_advance_ledger) && $customer_advance_ledger ? 'true' : 'false' ?>;
    
    console.log('Customer Advance Ledger Configured:', customer_advance_ledger_configured);
    
    var payment_amount = 0;
    // Global variable to store current advance balance
    var current_advance_balance = 0;
    // Global variable to store total invoice amount
    var total_invoice_amount = 0;

    $(document).ready(function () {

        // Form submission handler - update hidden field with checkbox state
        $('form').on('submit', function(e) {
            var settleWithAdvance = $('#settle-with-advance-table').is(':checked') ? '1' : '0';
            $('#settle_with_advance_hidden').val(settleWithAdvance);
        });

        // Event handler for settle-with-advance checkbox in the table
        $(document).on('change', '#settle-with-advance-table', function (e) {
            localStorage.setItem('settle-with-advance', $(this).is(':checked') ? '1' : '0');
            updateAdvanceSettlementCalculation();
        });

        $(document).on('change', 'input[name="payment_amount[]"]', function (e) {
            // Recalculate totals
            var total_paying = 0;
            $('input[name="payment_amount[]"]').each(function() {
                total_paying += parseFloat($(this).val()) || 0;
            });
            
            // Update total invoice amount from due amounts
            total_invoice_amount = 0;
            $('input[name="due_amount[]"]').each(function() {
                total_invoice_amount += parseFloat($(this).val()) || 0;
            });
            
            // Update calculations including excess advance
            updateAdvanceSettlementCalculation();
        });

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
            loadCustomerAdvanceBalance($('#cscustomer').val());
        });

        if (cscustomer = localStorage.getItem('cscustomer')) {
            $('#cscustomer').val(cscustomer);

            loadInvoices($('#cscustomer').val());
            loadCustomerAdvanceBalance($('#cscustomer').val());
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
                            var original_payment_amount = payment_amount;
                            
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

                            // Update global variable for advance settlement calculation
                            total_invoice_amount = total_due;

                            var newTr = $('<tr class="row"></tr>');
                                tr_html = '<td colspan="1"><b>Totals:</b> </td>';
                                tr_html += '<td><b>'+total_amt.toFixed(2)+'</b></td>';
                                tr_html += '<td><b>'+total_due.toFixed(2)+'</b></td>';
                                tr_html += '<td><b>'+total_paying.toFixed(2)+'</b></td>';
                                newTr.html(tr_html);
                                newTr.appendTo('#poTable');

                            // Add advance adjustment row (for settling with existing advance)
                            if (customer_advance_ledger_configured && current_advance_balance > 0) {
                                var shortage = total_due - original_payment_amount;
                                var is_checked = $('#settle-with-advance-table').is(':checked');
                                var adjustable_amount = is_checked && shortage > 0 ? Math.min(shortage, current_advance_balance) : 0;
                                
                                var advanceAdjustTr = $('<tr id="advance-adjustment-row" class="row" style="background-color: #e3f2fd;"></tr>');
                                var advance_adjust_html = '<td colspan="2"><b>Available Advance to Adjust:</b> <span style="color: #28a745;">' + current_advance_balance.toFixed(2) + '</span></td>';
                                advance_adjust_html += '<td><label class="checkbox-inline" style="margin-top: 5px;"><input type="checkbox" id="settle-with-advance-table" style="margin-right: 5px;"> Settle with Advance</label></td>';
                                advance_adjust_html += '<td><b id="advance-adjustment-amount">' + adjustable_amount.toFixed(2) + '</b></td>';
                                advanceAdjustTr.html(advance_adjust_html);
                                advanceAdjustTr.appendTo('#poTable');
                                
                                // Set the checkbox state from localStorage
                                $('#settle-with-advance-table').prop('checked', is_checked);
                            }
                            
                            // Show excess payment as advance (if payment > total due)
                            if (customer_advance_ledger_configured && original_payment_amount > total_due) {
                                var excess_amount = original_payment_amount - total_due;
                                var excessTr = $('<tr id="excess-advance-row" class="row" style="background-color: #fff3cd;"></tr>');
                                var excess_html = '<td colspan="3"><b>Excess Payment (Will be parked as Advance):</b></td>';
                                excess_html += '<td><b id="excess-advance-amount" style="color: #856404;">' + excess_amount.toFixed(2) + '</b></td>';
                                excessTr.html(excess_html);
                                excessTr.appendTo('#poTable');
                            }
                            
                            // Update the settlement calculation
                            updateAdvanceSettlementCalculation();
                        }
                    }
                });
            }
        }

        function loadCustomerAdvanceBalance(customer_id) {
            console.log('loadCustomerAdvanceBalance called with customer_id:', customer_id);
            
            if (!customer_id) {
                $('#advance-balance').text('Please select a customer');
                $('#settle-with-advance-table').prop('disabled', true);
                current_advance_balance = 0;
                updateAdvanceSettlementCalculation();
                return;
            }

            $.ajax({
                url: '<?= admin_url('customers/get_customer_advance_balance?customer_id=') ?>' + customer_id,
                type: 'GET',
                dataType: 'json',
                success: function (response) {
                    console.log('AJAX Response:', response);
                    
                    if (response.error) {
                        console.error('Error loading customer advance balance:', response.error);
                        $('#advance-balance').html('<span style="color: #dc3545;">Error loading advance balance</span>');
                        $('#settle-with-advance-table').prop('disabled', true);
                        current_advance_balance = 0;
                        updateAdvanceSettlementCalculation();
                        return;
                    }

                    var advance_balance = parseFloat(response.advance_balance) || 0;
                    current_advance_balance = advance_balance;
                    
                    console.log('Advance Balance:', advance_balance);
                    console.log('Ledger Configured:', response.advance_ledger_configured);
                    
                    // Only show advance balance info if ledger is configured
                    if (response.advance_ledger_configured) {
                        if (advance_balance > 0) {
                            // Customer has positive advance balance
                            $('#advance-balance').html('<span style="color: #28a745; font-weight: bold;">' + advance_balance.toFixed(2) + '</span>');
                            $('#settle-with-advance-table').prop('disabled', false);
                        } else if (advance_balance < 0) {
                            // Customer has negative balance (owes money)
                            $('#advance-balance').html('<span style="color: #dc3545;">Customer Owes: ' + Math.abs(advance_balance).toFixed(2) + '</span>');
                            $('#settle-with-advance-table').prop('disabled', true);
                            $('#settle-with-advance-table').prop('checked', false);
                        } else {
                            // Balance is exactly 0
                            $('#advance-balance').html('<span style="color: #666;">0.00 (No Advance Available)</span>');
                            $('#settle-with-advance-table').prop('disabled', true);
                            $('#settle-with-advance-table').prop('checked', false);
                        }
                    } else {
                        console.log('Not showing advance - ledger not configured');
                        $('#advance-balance').html('<span style="color: #dc3545;">Not configured</span>');
                        $('#settle-with-advance-table').prop('disabled', true);
                    }
                    
                    updateAdvanceSettlementCalculation();
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error loading customer advance balance:', error);
                    console.error('Status:', status);
                    console.error('XHR:', xhr);
                    
                    var errorMsg = 'Error loading advance balance';
                    if (xhr.responseText) {
                        try {
                            var errorData = JSON.parse(xhr.responseText);
                            if (errorData.error) {
                                errorMsg += ': ' + errorData.error;
                            }
                        } catch (e) {
                            // If not JSON, show first 50 characters of response
                            errorMsg += ': ' + xhr.responseText.substring(0, 50);
                        }
                    }
                    
                    $('#advance-balance').html('<span style="color: #dc3545;">' + errorMsg + '</span>');
                    $('#settle-with-advance-table').prop('disabled', true);
                    current_advance_balance = 0;
                    updateAdvanceSettlementCalculation();
                }
            });
        }

        function updateAdvanceSettlementCalculation() {
            var payment_amount = parseFloat($('#cspayment').val()) || 0;
            var use_advance = $('#settle-with-advance-table').is(':checked');
            
            // Calculate the shortage (invoice total - payment entered)
            var shortage_amount = total_invoice_amount - payment_amount;
            
            if (use_advance && current_advance_balance > 0 && shortage_amount > 0) {
                // Amount to adjust from advance = minimum of shortage or available advance
                var advance_to_use = Math.min(current_advance_balance, shortage_amount);
                var cash_payment = payment_amount;
                var total_settlement = cash_payment + advance_to_use;
                
                $('#cash-payment-amount').text(cash_payment.toFixed(2));
                $('#advance-settlement-amount').text(advance_to_use.toFixed(2));
                $('#total-settlement-amount').text(total_settlement.toFixed(2));
                $('#advance-settlement-info').show();
                
                // Update the advance adjustment row in the table if it exists
                updateAdvanceRowInTable(advance_to_use);
            } else {
                $('#cash-payment-amount').text(payment_amount.toFixed(2));
                $('#advance-settlement-amount').text('0.00');
                $('#total-settlement-amount').text(payment_amount.toFixed(2));
                $('#advance-settlement-info').hide();
                
                // Update the advance adjustment row to show 0 when unchecked
                updateAdvanceRowInTable(0);
            }
            
            // Update excess advance row if payment > total invoice amount
            updateExcessAdvanceRow();
        }

        function updateExcessAdvanceRow() {
            var payment_amount = parseFloat($('#cspayment').val()) || 0;
            var excess_amount = payment_amount - total_invoice_amount;
            
            // Remove existing excess row
            $('#excess-advance-row').remove();
            
            // Only show excess row if there's excess and ledger is configured
            if (customer_advance_ledger_configured && excess_amount > 0 && total_invoice_amount > 0) {
                var excessTr = $('<tr id="excess-advance-row" class="row" style="background-color: #fff3cd;"></tr>');
                var excess_html = '<td colspan="3"><b>Excess Payment (Will be parked as Advance):</b></td>';
                excess_html += '<td><b id="excess-advance-amount" style="color: #856404;">' + excess_amount.toFixed(2) + '</b></td>';
                excessTr.html(excess_html);
                
                // Insert before advance adjustment row if it exists, otherwise append to table
                var advanceRow = $('#advance-adjustment-row');
                if (advanceRow.length > 0) {
                    excessTr.insertBefore(advanceRow);
                } else {
                    excessTr.appendTo('#poTable');
                }
            }
        }

        function updateAdvanceRowInTable(advance_amount) {
            // Update the advance adjustment amount in the table row
            var advanceRow = $('#advance-adjustment-row');
            if (advanceRow.length > 0) {
                advanceRow.find('#advance-adjustment-amount').text(advance_amount.toFixed(2));
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

                        <!-- Customer Advance Balance Display -->
                        <div class="col-md-4">
                            <div class="form-group">
                                <label><?= lang('Available Advance'); ?></label>
                                <div id="customer-advance-info" style="padding: 8px; background-color: #f8f9fa; border: 1px solid #dee2e6; border-radius: 4px;">
                                    <span id="advance-balance">Please select a customer</span>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <?= lang('Amount Received', 'cspayment'); ?>
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
                                <!-- Hidden field to pass settle_with_advance to backend -->
                                <input type="hidden" name="settle_with_advance" id="settle_with_advance_hidden" value="0">
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

