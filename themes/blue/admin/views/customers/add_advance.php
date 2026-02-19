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

    // Utility function to round numbers to 4 decimal places consistently
    function roundAmount(value) {
        return Math.round(value * 10000) / 10000;
    }

    // Utility function to safely parse float and round
    function parseAmount(value) {
        return roundAmount(parseFloat(value) || 0);
    }

    $(document).ready(function () {

        // Form submission handler - update hidden field with checkbox state
        $('form').on('submit', function(e) {
            var settleWithAdvance = $('#settle-with-advance-table').is(':checked') ? '1' : '0';
            $('#settle_with_advance_hidden').val(settleWithAdvance);
        });

        // Event handler for settle-with-advance checkbox in the table
        $(document).on('change', '#settle-with-advance-table', function (e) {
            var checked = $(this).is(':checked') ? '1' : '0';
            localStorage.setItem('settle-with-advance', checked);
            // Keep hidden input in sync
            $('#settle_with_advance_hidden').val(checked);
            //updateAdvanceSettlementCalculation();
        });

        $(document).on('change input', 'input[name="payment_amount[]"]', function (e) {
            // When a user edits a payment_amount[] manually, treat it as cash allocation
            var val = parseAmount($(this).val());
            $(this).attr('data-cash', val.toFixed(4));

            // Recalculate totals
            var total_paying = 0;
            $('input[name="payment_amount[]"]').each(function() {
                total_paying = roundAmount(total_paying + parseAmount($(this).val()));
            });
            
            // Update total invoice amount from due amounts
            total_invoice_amount = 0;
            $('input[name="due_amount[]"]').each(function() {
                total_invoice_amount = roundAmount(total_invoice_amount + parseAmount($(this).val()));
            });
            
            // Update calculations including excess advance (this will recompute advance allocations)
            //updateAdvanceSettlementCalculation();
        });

        // Handle additional discount percentage changes
        $(document).on('input change', '.additional-discount-pct', function (e) {
            var $row = $(this).closest('tr');
            var due_amount = parseAmount($row.find('input[name="due_amount[]"]').val());
            var return_amount = parseAmount($row.find('input[name="return_amount[]"]').val());
            var discount_pct = parseAmount($(this).val());
            
            // Validate percentage range
            if (discount_pct < 0) discount_pct = 0;
            if (discount_pct > 100) discount_pct = 100;
            $(this).val(discount_pct);
            
            // Calculate discount amount based on due amount after returns
            var amount_after_returns = roundAmount(due_amount - return_amount);
            var discount_amount = roundAmount((amount_after_returns * discount_pct) / 100);
            
            // Display the calculated discount amount
            $row.find('.discount-amount-display').text(discount_amount.toFixed(4));

            // Update total payable display for this row (due - returns - discount)
            var payable_after_discount = roundAmount(amount_after_returns - discount_amount);
            $row.find('.total-payable').val(payable_after_discount.toFixed(4));

            // Re-distribute the entered Amount Received across invoices after discount change
            //distributePaymentSequentially();
            // Recalculate advance settlement and summary to respect "Settle with Advance" checkbox
            //updateAdvanceSettlementCalculation();
        });

        // Function to update payment summary table
        function updatePaymentSummary(total_invoice, total_returns, total_discount, total_payable) {
            var amount_received = parseAmount($('#cspayment').val());
            // Calculate excess payment (advance) if amount received > total payable
            var excess_payment = 0;
            var balance_due = 0;
            var settle_with_advance_checked = $('#settle-with-advance-table').is(':checked');

            // Compute advance used by summing per-row data-advance attributes (fallback to 0 if not present)
            var advance_used = 0;
            $('input[name="payment_amount[]"]').each(function() {
                advance_used = roundAmount(advance_used + parseAmount($(this).attr('data-advance')));
            });

            if ($('#park-as-advance').is(':checked')) {
                // User chose to park entire received amount as advance
                excess_payment = amount_received;
                balance_due = total_payable; // nothing is paid against invoices
            } else if (settle_with_advance_checked && advance_used > 0) {
                // When settling with advance, total settlement = cash received + advance used
                var total_settled = roundAmount(amount_received + advance_used);
                // If total settled covers payable, balance due = 0, excess is 0 (advance used reduces advance balance separately)
                if (total_settled >= total_payable) {
                    balance_due = 0;
                    excess_payment = roundAmount(Math.max(0, total_settled - total_payable));
                } else {
                    balance_due = roundAmount(total_payable - total_settled);
                }
            } else if (amount_received > total_payable) {
                // Excess payment will be parked as advance
                excess_payment = roundAmount(amount_received - total_payable);
                balance_due = 0;
            } else {
                // Amount received is less than or equal to total payable
                balance_due = roundAmount(total_payable - amount_received);
            }

            $('#summary-total-invoice').text(total_invoice.toFixed(4));
            $('#summary-total-returns').text(total_returns.toFixed(4));
            $('#summary-total-discount').text(total_discount.toFixed(4));
            $('#summary-total-payable').text(total_payable.toFixed(4));
            // Show amount received (cash) and indicate if advance used
            if (settle_with_advance_checked && advance_used > 0) {
                // Display total settlement (cash + advance) to give a clearer picture
                var total_settlement_display = roundAmount(amount_received + advance_used);
                $('#summary-amount-received').text(total_settlement_display.toFixed(4));
            } else {
                $('#summary-amount-received').text(amount_received.toFixed(4));
            }
            $('#summary-advance-payment').text(excess_payment.toFixed(4));
            $('#summary-balance-due').text(balance_due.toFixed(4));
            
            // Update balance due color based on value
            if (balance_due === 0) {
                $('#summary-balance-due').css('color', '#388e3c'); // Green if fully paid
            } else {
                $('#summary-balance-due').css('color', '#d32f2f'); // Red if amount due
            }
            
            // Show/hide returns row based on whether there are returns
            if (total_returns > 0) {
                $('#summary-returns-row').show();
            } else {
                $('#summary-returns-row').hide();
            }
            
            // Show/hide excess advance row based on whether there's excess payment or park-as-advance is selected
            if (excess_payment > 0) {
                $('#summary-advance-row').show();
            } else {
                $('#summary-advance-row').hide();
            }
        }

        // Function to update customer limit display with new totals
        function updateCustomerLimitDisplay(total_payable) {
            var limitRow = $('#customer-limit-row');
            if (limitRow.length > 0) {
                var credit_limit = parseFloat(limitRow.attr('data-credit-limit')) || 0;
                var existing_balance = parseFloat(limitRow.attr('data-existing-balance')) || 0;
                
                // Get amount received from input
                var amount_received = parseFloat($('#cspayment').val()) || 0;

                // If settling with advance, include the advance allocated to invoices in received amount
                if ($('#settle-with-advance-table').is(':checked')) {
                    var advance_used = 0;
                    $('input[name="payment_amount[]"]').each(function() {
                        advance_used += parseFloat($(this).attr('data-advance')) || 0;
                    });
                    amount_received = amount_received + advance_used;
                }

                // If park-as-advance is selected, allocations are not applied to invoices so amount_received should not reduce customer's balance
                if ($('#park-as-advance').is(':checked')) {
                    amount_received = 0;
                }

                // If amount received equals or exceeds total payable, current balance is 0
                // Otherwise, current balance = total payable - amount received
                var current_balance = 0;
                if (amount_received >= total_payable) {
                    current_balance = 0;
                } else {
                    current_balance = total_payable - amount_received;
                }
                
                var remaining_limit = credit_limit - existing_balance;
                
                // Update the display
                $('#customer-current-balance').text(current_balance.toFixed(4));
                $('#customer-remaining-limit').text(remaining_limit.toFixed(4));
                
                // Update color based on limit status
                if (remaining_limit < 0) {
                    $('#customer-remaining-limit').css('color', '#d32f2f'); // Red if over limit
                } else if (remaining_limit < credit_limit * 0.2) {
                    $('#customer-remaining-limit').css('color', '#f57c00'); // Orange if less than 20%
                } else {
                    $('#customer-remaining-limit').css('color', '#388e3c'); // Green if good
                }
            }
        }

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
            // Update calculations without reloading invoices
            // If user wants to park as advance, do not allocate to invoices
            if ($('#park-as-advance').is(':checked')) {
                // Clear allocations on invoices
                $('#poTable tbody tr').each(function() {
                    var $row = $(this);
                    if ($row.find('input[name="payment_amount[]"]').length > 0) {
                        $row.find('input[name="payment_amount[]"]').val('0.0000').attr('data-cash', '0.0000');
                    }
                });
                // Recalculate totals and show advance as full amount
                //updateTotalsRow();
                //updateAdvanceSettlementCalculation();
            } else {
                // First, distribute the entered amount across invoices
                //distributePaymentSequentially();
                // Then update advance/settlement calculations and totals
                //updateAdvanceSettlementCalculation();
            }
        });

        // Handle Park as Advance checkbox change
        $(document).on('change', '#park-as-advance', function (e) {
            if ($(this).is(':checked')) {
                // Clear allocations and show advance
                $('#poTable tbody tr').each(function() {
                    var $row = $(this);
                    if ($row.find('input[name="payment_amount[]"]').length > 0) {
                        // Clear and disable the allocation input so it is not submitted
                        $row.find('input[name="payment_amount[]"]').val('0.0000').attr('data-cash', '0.0000').prop('disabled', true);
                    }
                });
                //updateTotalsRow();
                //updateAdvanceSettlementCalculation();
            } else {
                // Re-distribute payment to invoices
                // Re-enable allocation inputs and redistribute
                $('#poTable tbody tr').each(function() {
                    var $row = $(this);
                    if ($row.find('input[name="payment_amount[]"]').length > 0) {
                        $row.find('input[name="payment_amount[]"]').prop('disabled', false);
                    }
                });
                //distributePaymentSequentially();
                //updateAdvanceSettlementCalculation();
            }
        });
        
        if (cspayment = localStorage.getItem('cspayment')) {
            $('#cspayment').val(cspayment);

            // Ensure advance balance is loaded first so excess/settlement rows are accurate
            loadCustomerAdvanceBalance($('#cscustomer').val(), function() {
                loadInvoices($('#cscustomer').val());
            });
        }

        $(document).on('change', '#cscustomer', function (e) {
            localStorage.setItem('cscustomer', $(this).val());

            // Load advance balance first, then invoices and related info to avoid race conditions
            loadCustomerAdvanceBalance($('#cscustomer').val(), function() {
                //loadInvoices($('#cscustomer').val());
                loadCustomerLimitInfo($('#cscustomer').val());
                //loadCustomerDiscountLedger($('#cscustomer').val());
            });
        });

        if (cscustomer = localStorage.getItem('cscustomer')) {
            $('#cscustomer').val(cscustomer);

            // Ensure advance balance is fetched before loading invoices
            loadCustomerAdvanceBalance($('#cscustomer').val(), function() {
                //loadInvoices($('#cscustomer').val());
                loadCustomerLimitInfo($('#cscustomer').val());
                //loadCustomerDiscountLedger($('#cscustomer').val());
            });
        }

        $(document).on('change', '#csledger', function (e) {
            localStorage.setItem('csledger', $(this).val());
        });
        if (csledger = localStorage.getItem('csledger')) {
            $('#csledger').val(csledger);
        }

        function loadCustomerAdvanceBalance(customer_id, callback) {
            console.log('loadCustomerAdvanceBalance called with customer_id:', customer_id);
            
            if (!customer_id) {
                $('#advance-balance').text('Please select a customer');
                $('#settle-with-advance-table').prop('disabled', true);
                current_advance_balance = 0;
                    //updateAdvanceSettlementCalculation();

                    // Invoke callback after advance balance is loaded and calculations updated
                    if (typeof callback === 'function') {
                        try { callback(); } catch (e) { console.error('advance callback error', e); }
                    }
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
                        //updateAdvanceSettlementCalculation();
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
                            $('#advance-balance').html('<span style="color: #28a745; font-weight: bold;">' + advance_balance.toFixed(4) + '</span>');
                            $('#settle-with-advance-table').prop('disabled', false);
                        } else if (advance_balance < 0) {
                            // Customer has negative balance (owes money)
                            $('#advance-balance').html('<span style="color: #dc3545;">Customer Owes: ' + Math.abs(advance_balance).toFixed(4) + '</span>');
                            $('#settle-with-advance-table').prop('disabled', true);
                            $('#settle-with-advance-table').prop('checked', false);
                        } else {
                            // Balance is exactly 0
                            $('#advance-balance').html('<span style="color: #666;">0.0000 (No Advance Available)</span>');
                            $('#settle-with-advance-table').prop('disabled', true);
                            $('#settle-with-advance-table').prop('checked', false);
                        }
                    } else {
                        console.log('Not showing advance - ledger not configured');
                        $('#advance-balance').html('<span style="color: #dc3545;">Not configured</span>');
                        $('#settle-with-advance-table').prop('disabled', true);
                    }
                    
                    //updateAdvanceSettlementCalculation();

                    // Invoke callback after advance balance is loaded and calculations updated
                    if (typeof callback === 'function') {
                        try { callback(); } catch (e) { console.error('advance callback error', e); }
                    }
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
                    //updateAdvanceSettlementCalculation();

                    // Invoke callback even on error so caller can continue
                    if (typeof callback === 'function') {
                        try { callback(); } catch (e) { console.error('advance callback error', e); }
                    }
                }
            });
        }


        function loadCustomerLimitInfo(customer_id) {
            console.log('loadCustomerLimitInfo called with customer_id:', customer_id);
            
            if (!customer_id) {
                // Remove limit info rows if no customer selected
                $('#customer-limit-row').remove();
                return;
            }

            $.ajax({
                url: '<?= admin_url('customers/customer_limit_info?customer_id=') ?>' + customer_id,
                type: 'GET',
                dataType: 'json',
                success: function (response) {
                    console.log('Customer Limit Info Response:', response);
                    
                    // Remove existing limit row
                    $('#customer-limit-row').remove();
                    
                    if (response && response.credit_limit !== undefined) {
                        var credit_limit = parseFloat(response.credit_limit) || 0;
                        var existing_balance = parseFloat(response.current_balance) || 0;
                        
                        // Calculate total payable from current invoices (after discount)
                        var total_payable = 0;
                        $('#poTable tbody tr').each(function() {
                            if ($(this).find('input[name="payment_amount[]"]').length > 0) {
                                total_payable += parseFloat($(this).find('input[name="payment_amount[]"]').val()) || 0;
                            }
                        });
                        
                        // Get amount received from input
                        var amount_received = parseFloat($('#cspayment').val()) || 0;
                        
                        // If amount received equals total payable, current balance is 0
                        // Otherwise, current balance = total payable - amount received
                        var current_balance = 0;
                        if (amount_received >= total_payable) {
                            current_balance = 0;
                        } else {
                            current_balance = total_payable - amount_received;
                        }
                        
                        var remaining_limit = credit_limit - existing_balance;
                        
                        // Add customer limit info row after totals row
                        var limitTr = $('<tr id="customer-limit-row" class="row" style="background-color: #e8f5e9;"></tr>');
                        var limit_html = '<td colspan="2"><b>Customer Credit Limit:</b></td>';
                        limit_html += '<td><b style="color: #1976d2;">' + credit_limit.toFixed(4) + '</b></td>';
                        limit_html += '<td colspan="2"><b>Current Balance:</b> <span style="color: #d32f2f;" id="customer-current-balance">' + current_balance.toFixed(4) + '</span></td>';
                        limit_html += '<td colspan="2"><b>Remaining Limit:</b> <span style="color: #388e3c;" id="customer-remaining-limit">' + remaining_limit.toFixed(4) + '</span></td>';
                        limitTr.html(limit_html);
                        
                        // Store credit limit and existing balance in data attributes for recalculation
                        limitTr.attr('data-credit-limit', credit_limit);
                        limitTr.attr('data-existing-balance', existing_balance);
                        
                        // Insert after totals row (before advance adjustment row if exists)
                        var advanceRow = $('#advance-adjustment-row');
                        if (advanceRow.length > 0) {
                            limitTr.insertBefore(advanceRow);
                        } else {
                            var excessRow = $('#excess-advance-row');
                            if (excessRow.length > 0) {
                                limitTr.insertBefore(excessRow);
                            } else {
                                limitTr.appendTo('#poTable');
                            }
                        }
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error loading customer limit info:', error);
                    // Silently fail - just don't show the limit info
                }
            });
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
        <h2 class="blue"><i class="fa-fw fa fa-info-circle"></i><?= lang('Add Advance'); ?></h2>

        <div class="box-icon">
            <ul class="btn-tasks">
                
            </ul>
        </div>
    </div>
    <div class="box-content">
        <div class="row">
            <?php
            $attrib = ['data-toggle' => 'validator', 'role' => 'form'];
            echo admin_form_open_multipart('customers/add_advance', $attrib)
            ?>
            <div class="col-lg-12">
                
                <div class="row">
                    <div class="col-lg-12">
                        
                        <div class="col-md-4">
                            <div class="form-group">
                                <?= lang('date', 'csdate'); ?>
                                <?php echo form_input('date', ($_POST['date'] ?? ''), 'class="form-control input-tip date" id="csdate" required="required"'); ?>
                            </div>
                        </div>

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
                                <div style="margin-top:6px;">
                                    <label style="font-weight:normal; font-size:90%;">
                                        <input type="checkbox" id="park-as-advance" name="park_as_advance" value="1" style="margin-right:6px;" /> Park as Advance (do not allocate to invoices)
                                    </label>
                                </div>
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

                        <!-- Hidden field to pass settle_with_advance to backend -->
                        <input type="hidden" name="settle_with_advance" id="settle_with_advance_hidden" value="0">
                    </div>

                    <!-- Payment Summary Table -->
                    <div class="table-responsive" style="margin-top: 20px;">
                        <table class="table table-bordered" style="width: 50%; margin-left: auto; background-color: #f8f9fa;">
                            <thead style="background-color: #1976d2;">
                                <tr>
                                    <th colspan="2" class="text-center" style="font-size: 14px; font-weight: bold; color: #ffffff;">
                                        Payment Summary
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td style="width: 60%; font-weight: 600;">Total Invoice Amount:</td>
                                    <td class="text-right" style="font-weight: bold;" id="summary-total-invoice">0.0000</td>
                                </tr>
                                <tr id="summary-returns-row" style="display: none;">
                                    <td style="font-weight: 600;">Total Returns:</td>
                                    <td class="text-right" style="color: #e65100; font-weight: bold;" id="summary-total-returns">0.0000</td>
                                </tr>
                                <tr>
                                    <td style="font-weight: 600;">Total Discount:</td>
                                    <td class="text-right" style="color: #d32f2f; font-weight: bold;" id="summary-total-discount">0.0000</td>
                                </tr>
                                <tr style="background-color: #fff3cd;">
                                    <td style="font-weight: 600;">Total Payable:</td>
                                    <td class="text-right" style="font-weight: bold; color: #1976d2; font-size: 16px;" id="summary-total-payable">0.0000</td>
                                </tr>
                                <tr>
                                    <td style="font-weight: 600;">Amount Received:</td>
                                    <td class="text-right" style="font-weight: bold; color: #388e3c;" id="summary-amount-received">0.0000</td>
                                </tr>
                                <tr id="summary-advance-row" style="display: none; background-color: #fff3cd;">
                                    <td style="font-weight: 600;">Excess (Parked as Advance):</td>
                                    <td class="text-right" style="font-weight: bold; color: #856404;" id="summary-advance-payment">0.0000</td>
                                </tr>
                                <tr style="background-color: #e8f5e9; border-top: 2px solid #388e3c;">
                                    <td style="font-weight: 700; font-size: 14px;">Balance Due:</td>
                                    <td class="text-right" style="font-weight: bold; color: #d32f2f; font-size: 16px;" id="summary-balance-due">0.0000</td>
                                </tr>
                            </tbody>
                        </table>
                        
                        <!-- Buttons moved below Payment Summary -->
                        <div style="text-align: center; margin-top: 20px;">
                            <button type="submit" class="btn btn-primary btn-lg" id="add_payment" style="min-width: 180px; margin-right: 10px;"><?= lang('Receive Payments') ?></button>
                            <button type="button" class="btn btn-danger" id="reset" onclick="resetValues();" style="min-width: 120px;"><?= lang('reset') ?></button>
                        </div>
                    </div>
                
            </div>

            <?php echo form_close(); ?>

        </div>
    </div>
</div>

