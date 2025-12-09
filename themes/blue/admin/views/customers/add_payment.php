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
            var checked = $(this).is(':checked') ? '1' : '0';
            localStorage.setItem('settle-with-advance', checked);
            // Keep hidden input in sync
            $('#settle_with_advance_hidden').val(checked);
            updateAdvanceSettlementCalculation();
        });

        $(document).on('change input', 'input[name="payment_amount[]"]', function (e) {
            // When a user edits a payment_amount[] manually, treat it as cash allocation
            var val = parseFloat($(this).val()) || 0;
            $(this).attr('data-cash', val.toFixed(4));

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
            
            // Update calculations including excess advance (this will recompute advance allocations)
            updateAdvanceSettlementCalculation();
        });

        // Handle additional discount percentage changes
        $(document).on('input change', '.additional-discount-pct', function (e) {
            var $row = $(this).closest('tr');
            var due_amount = parseFloat($row.find('input[name="due_amount[]"]').val()) || 0;
            var return_amount = parseFloat($row.find('input[name="return_amount[]"]').val()) || 0;
            var discount_pct = parseFloat($(this).val()) || 0;
            
            // Validate percentage range
            if (discount_pct < 0) discount_pct = 0;
            if (discount_pct > 100) discount_pct = 100;
            $(this).val(discount_pct);
            
            // Calculate discount amount based on due amount after returns
            var amount_after_returns = due_amount - return_amount;
            var discount_amount = (amount_after_returns * discount_pct) / 100;
            
            // Display the calculated discount amount
            $row.find('.discount-amount-display').text(discount_amount.toFixed(4));

            // Update total payable display for this row (due - returns - discount)
            var payable_after_discount = amount_after_returns - discount_amount;
            $row.find('.total-payable').val(payable_after_discount.toFixed(4));

            // Re-distribute the entered Amount Received across invoices after discount change
            distributePaymentSequentially();
            // Recalculate advance settlement and summary to respect "Settle with Advance" checkbox
            updateAdvanceSettlementCalculation();
        });

        // Function to recalculate and update the totals row
        function updateTotalsRow() {
            var total_original = 0;
            var total_due = 0;
            var total_returns = 0;
            var total_discount = 0;
            var total_payment = 0;
            var total_payable = 0; // sum of (due - returns - discount) per row

            // Calculate totals from all rows
            $('#poTable tbody tr').each(function() {
                if ($(this).find('input[name="original_amount[]"]').length > 0) {
                    total_original += parseFloat($(this).find('input[name="original_amount[]"]').val()) || 0;
                    total_due += parseFloat($(this).find('input[name="due_amount[]"]').val()) || 0;
                    total_returns += parseFloat($(this).find('input[name="return_amount[]"]').val()) || 0;
                    
                    var due = parseFloat($(this).find('input[name="due_amount[]"]').val()) || 0;
                    var returns = parseFloat($(this).find('input[name="return_amount[]"]').val()) || 0;
                    var discount_pct = parseFloat($(this).find('.additional-discount-pct').val()) || 0;
                    var discount_amt = ((due - returns) * discount_pct) / 100;
                    total_discount += discount_amt;

                    var payable = (due - returns) - discount_amt;
                    total_payable += payable;

                    total_payment += parseFloat($(this).find('input[name="payment_amount[]"]').val()) || 0;
                }
            });

            // Update the totals row (find the row with "Totals:" text)
            $('#poTable tbody tr').each(function() {
                if ($(this).find('td:first').text().trim() === 'Totals:') {
                    $(this).find('td').eq(1).html('<b>' + total_original.toFixed(4) + '</b>');
                    // $(this).find('td').eq(2).html('<b>' + total_due.toFixed(2) + '</b>');
                    $(this).find('td').eq(3).html('<small style="color:#e65100;">-' + total_returns.toFixed(4) + '</small>');
                    $(this).find('td').eq(4).html('<small style="color:#d32f2f;">-' + total_discount.toFixed(4) + '</small>');
                    $(this).find('td').eq(6).html('<b>' + total_payment.toFixed(4) + '</b>');
                }
            });
            
            // Update customer limit info if it exists (use total_payable)
            // NOTE: pass total_payable (outstanding) not total_payment (already-paid)
            updateCustomerLimitDisplay(total_payable);

            // Set global total_invoice_amount to the computed total payable (outstanding)
            total_invoice_amount = total_payable;

            // Update payment summary table: pass total outstanding payable
            updatePaymentSummary(total_due, total_returns, total_discount, total_payable);
        }

        // Function to update payment summary table
        function updatePaymentSummary(total_invoice, total_returns, total_discount, total_payable) {
            var amount_received = parseFloat($('#cspayment').val()) || 0;
            // Calculate excess payment (advance) if amount received > total payable
            var excess_payment = 0;
            var balance_due = 0;
            var settle_with_advance_checked = $('#settle-with-advance-table').is(':checked');

            // Compute advance used by summing per-row data-advance attributes (fallback to 0 if not present)
            var advance_used = 0;
            $('input[name="payment_amount[]"]').each(function() {
                var adv = parseFloat($(this).attr('data-advance')) || 0;
                advance_used += adv;
            });

            if ($('#park-as-advance').is(':checked')) {
                // User chose to park entire received amount as advance
                excess_payment = amount_received;
                balance_due = total_payable; // nothing is paid against invoices
            } else if (settle_with_advance_checked && advance_used > 0) {
                // When settling with advance, total settlement = cash received + advance used
                var total_settled = amount_received + advance_used;
                // If total settled covers payable, balance due = 0, excess is 0 (advance used reduces advance balance separately)
                if (total_settled >= total_payable) {
                    balance_due = 0;
                    excess_payment = Math.max(0, total_settled - total_payable);
                } else {
                    balance_due = total_payable - total_settled;
                }
            } else if (amount_received > total_payable) {
                // Excess payment will be parked as advance
                excess_payment = amount_received - total_payable;
                balance_due = 0;
            } else {
                // Amount received is less than or equal to total payable
                balance_due = total_payable - amount_received;
            }

            $('#summary-total-invoice').text(total_invoice.toFixed(4));
            $('#summary-total-returns').text(total_returns.toFixed(4));
            $('#summary-total-discount').text(total_discount.toFixed(4));
            $('#summary-total-payable').text(total_payable.toFixed(4));
            // Show amount received (cash) and indicate if advance used
            if (settle_with_advance_checked && advance_used > 0) {
                // Display total settlement (cash + advance) to give a clearer picture
                var total_settlement_display = amount_received + advance_used;
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
                updateTotalsRow();
                updateAdvanceSettlementCalculation();
            } else {
                // First, distribute the entered amount across invoices
                distributePaymentSequentially();
                // Then update advance/settlement calculations and totals
                updateAdvanceSettlementCalculation();
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
                updateTotalsRow();
                updateAdvanceSettlementCalculation();
            } else {
                // Re-distribute payment to invoices
                // Re-enable allocation inputs and redistribute
                $('#poTable tbody tr').each(function() {
                    var $row = $(this);
                    if ($row.find('input[name="payment_amount[]"]').length > 0) {
                        $row.find('input[name="payment_amount[]"]').prop('disabled', false);
                    }
                });
                distributePaymentSequentially();
                updateAdvanceSettlementCalculation();
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
                loadInvoices($('#cscustomer').val());
                loadCustomerLimitInfo($('#cscustomer').val());
                loadCustomerDiscountLedger($('#cscustomer').val());
            });
        });

        if (cscustomer = localStorage.getItem('cscustomer')) {
            $('#cscustomer').val(cscustomer);

            // Ensure advance balance is fetched before loading invoices
            loadCustomerAdvanceBalance($('#cscustomer').val(), function() {
                loadInvoices($('#cscustomer').val());
                loadCustomerLimitInfo($('#cscustomer').val());
                loadCustomerDiscountLedger($('#cscustomer').val());
            });
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
                                var return_amount = parseFloat(response[i].return_total) || 0;
                                var additional_discount = parseFloat(response[i].additional_discount) || 0;
                                var due_amount = parseFloat(response[i].grand_total) - parseFloat(response[i].paid) - return_amount - additional_discount;
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

                                // Calculate credit period remaining
                                var credit_period_html = '';
                                if (response[i].due_date) {
                                    var due_date = new Date(response[i].due_date);
                                    var today = new Date();
                                    today.setHours(0,0,0,0);
                                    due_date.setHours(0,0,0,0);
                                    var days_diff = Math.ceil((due_date - today) / (1000 * 60 * 60 * 24));
                                    
                                    if (days_diff < 0) {
                                        credit_period_html = '<span style="color:red; font-weight:bold;">' + Math.abs(days_diff) + ' days overdue</span>';
                                    } else if (days_diff === 0) {
                                        credit_period_html = '<span style="color:orange; font-weight:bold;">Due today</span>';
                                    } else {
                                        credit_period_html = '<span style="color:#333;">' + days_diff + ' days remaining</span>';
                                    }
                                } else {
                                    credit_period_html = '<span style="color:#999;">N/A</span>';
                                }

                                var newTr = $('<tr id="row_' + response[i].id + '" class="row_' + response[i].id + '" data-item-id="' + response[i].id + '"></tr>');
                                var original_due = parseFloat(response[i].grand_total) - parseFloat(response[i].paid) - additional_discount;
                                tr_html = '<td>'+purchase_date+'</td>';
                                tr_html += '<td>'+reference_id+'</td>';
                                tr_html += '<td>'+total_amount.toFixed(4)+'<input name="original_amount[]" data-item-id="' + response[i].id + '" value="'+total_amount.toFixed(4)+'" type="hidden" /></td>';
                                tr_html += '<td>'+original_due.toFixed(4)+'<input name="due_amount[]" data-item-id="' + response[i].id + '" value="'+original_due.toFixed(4)+'" type="hidden" class="rid" /></td>';
                                tr_html += '<td style="color:#e65100;">'+return_amount.toFixed(4)+'<input name="return_amount[]" data-item-id="' + response[i].id + '" value="'+return_amount.toFixed(4)+'" type="hidden" /></td>';
                                tr_html += '<td><div class="input-group" style="width:120px;"><input name="additional_discount[]" data-item-id="' + response[i].id + '" type="number" step="0.01" min="0" max="100" class="form-control input-sm additional-discount-pct" style="width:80px;" value="0" placeholder="0" /><span class="input-group-addon">%</span></div><small class="discount-amount-display" style="color:#666; display:block; margin-top:2px;">0.0000</small></td>';
                                tr_html += '<td>'+credit_period_html+'</td>';
                                // Calculate payable after returns and discounts (initially discount is 0)
                                var payable_after_returns = due_amount - return_amount - additional_discount;
                                var initial_alloc = to_pay; // allocation determined sequentially above
                                // Render two fields: readonly Total Payable and Allocated Payment (will be updated when Amount Received changes)
                                tr_html += '<td>';
                                tr_html += '<input name="total_payable_display[]" data-item-id="' + response[i].id + '" type="text" class="form-control input-sm total-payable" value="'+payable_after_returns.toFixed(4)+'" readonly style="width:110px; display:inline-block;" />';
                                tr_html += ' <input name="payment_amount[]" data-item-id="' + response[i].id + '" type="text" class="form-control input-sm allocated-payment" value="'+initial_alloc.toFixed(4)+'" style="width:110px; display:inline-block; margin-left:6px;" />';
                                tr_html += '<input name="item_id[]" type="hidden" value="' + response[i].id + '">';
                                tr_html += '<input name="return_total[]" type="hidden" value="'+return_amount.toFixed(4)+'">';
                                tr_html += '</td>';
                                newTr.html(tr_html);
                                newTr.appendTo('#poTable');
                            }

                            // Update global variable for advance settlement calculation
                            total_invoice_amount = total_due;

                            // var newTr = $('<tr class="row"></tr>');
                            //     tr_html = '<td colspan="2"><b>Totals:</b> </td>';
                            //     tr_html += '<td><b>'+total_amt.toFixed(2)+'</b></td>';
                            //     // tr_html += '<td><b>'+total_due.toFixed(2)+'</b></td>';
                            //     tr_html += '<td><small style="color:#d32f2f;">-0.00</small></td>';
                            //     tr_html += '<td></td>';
                            //     tr_html += '<td><b>'+total_due.toFixed(2)+'</b></td>';
                            //     newTr.html(tr_html);
                            //     newTr.appendTo('#poTable');
                            
                            // Initialize payment summary with no discount
                            var amount_received = parseFloat($('#cspayment').val()) || 0;
                            //updatePaymentSummary(total_due, 0, total_due);

                            // Add advance adjustment row (for settling with existing advance)
                            if (customer_advance_ledger_configured && current_advance_balance > 0) {
                                var shortage = total_due - original_payment_amount;
                                var is_checked = $('#settle-with-advance-table').is(':checked');
                                var adjustable_amount = is_checked && shortage > 0 ? Math.min(shortage, current_advance_balance) : 0;
                                
                                var advanceAdjustTr = $('<tr id="advance-adjustment-row" class="row" style="background-color: #e3f2fd;"></tr>');
                                var advance_adjust_html = '<td colspan="2"><b>Available Advance to Adjust:</b> <span style="color: #28a745;">' + current_advance_balance.toFixed(4) + '</span></td>';
                                advance_adjust_html += '<td><label class="checkbox-inline" style="margin-top: 5px;"><input type="checkbox" id="settle-with-advance-table" style="margin-right: 5px;"> Settle with Advance</label></td>';
                                advance_adjust_html += '<td colspan="2"></td>';
                                advance_adjust_html += '<td><b id="advance-adjustment-amount">' + adjustable_amount.toFixed(4) + '</b></td>';
                                advanceAdjustTr.html(advance_adjust_html);
                                advanceAdjustTr.appendTo('#poTable');
                                
                                // Set the checkbox state from localStorage
                                $('#settle-with-advance-table').prop('checked', is_checked);
                            }
                            
                            // Show excess payment as advance (if payment > total due)
                            if (customer_advance_ledger_configured && original_payment_amount > total_due) {
                                var excess_amount = original_payment_amount - total_due;
                                var excessTr = $('<tr id="excess-advance-row" class="row" style="background-color: #fff3cd;"></tr>');
                                var excess_html = '<td colspan="5"><b>Excess Payment (Will be parked as Advance):</b></td>';
                                excess_html += '<td colspan="2"><b id="excess-advance-amount" style="color: #856404;">' + excess_amount.toFixed(4) + '</b></td>';
                                excessTr.html(excess_html);
                                excessTr.appendTo('#poTable');
                            }
                            
                            // Update the settlement calculation
                            updateAdvanceSettlementCalculation();

                            // After invoices are loaded, distribute the entered Amount Received across invoices
                            distributePaymentSequentially();
                            
                            // Load customer limit information
                            loadCustomerLimitInfo(customer_id);
                        }
                    }
                });
            }
        }

        function loadCustomerAdvanceBalance(customer_id, callback) {
            console.log('loadCustomerAdvanceBalance called with customer_id:', customer_id);
            
            if (!customer_id) {
                $('#advance-balance').text('Please select a customer');
                $('#settle-with-advance-table').prop('disabled', true);
                current_advance_balance = 0;
                    updateAdvanceSettlementCalculation();

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
                    
                    updateAdvanceSettlementCalculation();

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
                    updateAdvanceSettlementCalculation();

                    // Invoke callback even on error so caller can continue
                    if (typeof callback === 'function') {
                        try { callback(); } catch (e) { console.error('advance callback error', e); }
                    }
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
                var remaining_advance = advance_to_use;
                var cash_payment = payment_amount;
                var total_advance_allocated = 0;

                // Iterate invoice rows top-to-bottom and allocate available advance to shortages
                $('#poTable tbody tr').each(function() {
                    var $row = $(this);
                    if ($row.find('input[name="payment_amount[]"]').length === 0) return;

                    var due = parseFloat($row.find('input[name="due_amount[]"]').val()) || 0;
                    var returns = parseFloat($row.find('input[name="return_amount[]"]').val()) || 0;
                    var discount_pct = parseFloat($row.find('.additional-discount-pct').val()) || 0;
                    var payable = (due - returns) - ((due - returns) * discount_pct / 100);

                    // Cash-only allocation stored in data-cash (set by distributePaymentSequentially or manual edit)
                    var cash_alloc = parseFloat($row.find('input[name="payment_amount[]"]').attr('data-cash')) || 0;
                    var shortage = payable - cash_alloc;
                    var advance_alloc = 0;
                    if (remaining_advance > 0 && shortage > 0) {
                        advance_alloc = Math.min(remaining_advance, shortage);
                        remaining_advance -= advance_alloc;
                    }

                    var total_for_row = cash_alloc + advance_alloc;
                    // Update the input to reflect cash + advance allocation
                    $row.find('input[name="payment_amount[]"]').val(total_for_row.toFixed(4));
                    // Store advance allocation for debugging/reference
                    $row.find('input[name="payment_amount[]"]').attr('data-advance', advance_alloc.toFixed(4));

                    total_advance_allocated += advance_alloc;
                });

                var total_settlement = cash_payment + total_advance_allocated;

                // Update settlement display
                $('#cash-payment-amount').text(cash_payment.toFixed(4));
                $('#advance-settlement-amount').text(total_advance_allocated.toFixed(4));
                $('#total-settlement-amount').text(total_settlement.toFixed(4));
                $('#advance-settlement-info').show();

                // Update the advance adjustment row in the table if it exists
                updateAdvanceRowInTable(total_advance_allocated);
            } else {
                // No advance used - ensure inputs reflect cash-only allocations
                $('#poTable tbody tr').each(function() {
                    var $row = $(this);
                    if ($row.find('input[name="payment_amount[]"]').length === 0) return;
                    var cash_only = parseFloat($row.find('input[name="payment_amount[]"]').attr('data-cash')) || 0;
                    $row.find('input[name="payment_amount[]"]').val(cash_only.toFixed(4));
                    $row.find('input[name="payment_amount[]"]').attr('data-advance', '0.0000');
                });
                
                $('#cash-payment-amount').text(payment_amount.toFixed(4));
                $('#advance-settlement-amount').text('0.0000');
                $('#total-settlement-amount').text(payment_amount.toFixed(4));
                $('#advance-settlement-info').hide();

                // Update the advance adjustment row to show 0 when unchecked
                updateAdvanceRowInTable(0);
            }
            
            // Update excess advance row if payment > total invoice amount
            updateExcessAdvanceRow();

            // Recompute totals and payment summary after advance allocations change
            updateTotalsRow();
        }

        // Distribute the entered Amount Received sequentially across invoices
        function distributePaymentSequentially() {
            // If park-as-advance is selected, do not allocate to invoices
            if ($('#park-as-advance').is(':checked')) {
                // Clear all allocations
                $('#poTable tbody tr').each(function() {
                    var $row = $(this);
                    if ($row.find('input[name="payment_amount[]"]').length === 0) return;
                    $row.find('.total-payable').each(function() {
                        // ensure total-payable is up to date
                        var due = parseFloat($row.find('input[name="due_amount[]"]').val()) || 0;
                        var returns = parseFloat($row.find('input[name="return_amount[]"]').val()) || 0;
                        var discount_pct = parseFloat($row.find('.additional-discount-pct').val()) || 0;
                        var payable = (due - returns) - ((due - returns) * discount_pct / 100);
                            $row.find('.total-payable').val(payable.toFixed(4));
                    });
                        $row.find('input[name="payment_amount[]"]').val('0.0000').attr('data-cash', '0.0000');
                });
                updateTotalsRow();
                return;
            }
            var remaining = parseFloat($('#cspayment').val()) || 0;
            // Iterate rows in table order and allocate up to each row's payable amount
            $('#poTable tbody tr').each(function() {
                var $row = $(this);
                if ($row.find('input[name="payment_amount[]"]').length === 0) return;
                var due = parseFloat($row.find('input[name="due_amount[]"]').val()) || 0;
                var returns = parseFloat($row.find('input[name="return_amount[]"]').val()) || 0;
                var discount_pct = parseFloat($row.find('.additional-discount-pct').val()) || 0;
                var payable = (due - returns) - ((due - returns) * discount_pct / 100);
                // Update the readonly total-payable display
                $row.find('.total-payable').val(payable.toFixed(4));

                var alloc = 0;
                if (remaining > 0) {
                    alloc = Math.min(payable, remaining);
                    remaining = remaining - alloc;
                } else {
                    alloc = 0;
                }
                // Set cash-only allocation and store it in data-cash so advance allocation can be applied later
                $row.find('input[name="payment_amount[]"]').attr('data-cash', alloc.toFixed(4));
                $row.find('input[name="payment_amount[]"]').val(alloc.toFixed(4));
            });
            // Recalculate totals after distribution
            updateTotalsRow();
        }

        function updateExcessAdvanceRow() {
            var payment_amount = parseFloat($('#cspayment').val()) || 0;
            var excess_amount = payment_amount - total_invoice_amount;
            // If park-as-advance selected, entire payment_amount is parked as advance
            if ($('#park-as-advance').is(':checked')) {
                excess_amount = payment_amount;
            }
            
            // Remove existing excess row
            $('#excess-advance-row').remove();
            
            // Only show excess row if there's excess and ledger is configured
            if (customer_advance_ledger_configured && excess_amount > 0 && total_invoice_amount > 0) {
                var excessTr = $('<tr id="excess-advance-row" class="row" style="background-color: #fff3cd;"></tr>');
                var excess_html = '<td colspan="3"><b>Excess Payment (Will be parked as Advance):</b></td>';
                excess_html += '<td><b id="excess-advance-amount" style="color: #856404;">' + excess_amount.toFixed(4) + '</b></td>';
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
                advanceRow.find('#advance-adjustment-amount').text(advance_amount.toFixed(4));
            }
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

        function loadCustomerDiscountLedger(customer_id) {
            console.log('loadCustomerDiscountLedger called with customer_id:', customer_id);
            
            if (!customer_id) {
                $('#discount-ledger-balance').text('Please select a customer');
                return;
            }

            $.ajax({
                url: '<?= admin_url('customers/get_customer_discount_ledger?customer_id=') ?>' + customer_id,
                type: 'GET',
                dataType: 'json',
                success: function (response) {
                    console.log('Customer Discount Ledger Response:', response);
                    
                    if (response.error && !response.discount_ledger_configured) {
                        console.error('Error loading customer discount ledger:', response.error);
                        $('#discount-ledger-balance').html('<span style="color: #dc3545;">Not configured</span>');
                        return;
                    }
                    
                    console.log('Ledger Configured:', response.discount_ledger_configured);
                    console.log('Ledger Name:', response.ledger_name);
                    
                    // Show ledger name if configured
                    if (response.discount_ledger_configured && response.ledger_name) {
                        $('#discount-ledger-balance').html('<span style="color: #1976d2; font-weight: bold;">' + response.ledger_name + '</span>');
                    } else {
                        console.log('Not showing discount ledger - ledger not configured');
                        $('#discount-ledger-balance').html('<span style="color: #dc3545;">Not configured</span>');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error loading customer discount ledger:', error);
                    console.error('Status:', status);
                    console.error('XHR:', xhr);
                    
                    var errorMsg = 'Error loading discount ledger';
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
                    
                    $('#discount-ledger-balance').html('<span style="color: #dc3545;">' + errorMsg + '</span>');
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

                        <!-- Customer Discount Ledger Display -->
                        <div class="col-md-4">
                            <div class="form-group">
                                <label><?= lang('Customer Discount Ledger'); ?></label>
                                <div id="customer-discount-ledger-info" style="padding: 8px; background-color: #f8f9fa; border: 1px solid #dee2e6; border-radius: 4px;">
                                    <span id="discount-ledger-balance">Please select a customer</span>
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

                        <div class="col-md-4">
                            <div class="form-group">
                                <?= lang('Note', 'csnote'); ?>
                                <?php echo form_input('note', ($_POST['note'] ?? $_POST['note']), 'class="form-control input-tip" id="csnote"'); ?>
                            </div>
                        </div>

                        <!-- Hidden field to pass settle_with_advance to backend -->
                        <input type="hidden" name="settle_with_advance" id="settle_with_advance_hidden" value="0">
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
                                <th><?php echo $this->lang->line('Returns'); ?> (-)</th>
                                <th><?php echo $this->lang->line('Additional Discount'); ?> (%)</th>
                                <th><?php echo $this->lang->line('Credit Period'); ?></th>
                                <th><?php echo $this->lang->line('Total Payable'); ?></th>
                            </tr>
                            </thead>
                            <tbody></tbody>
                            <tfoot></tfoot>
                        </table>
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

