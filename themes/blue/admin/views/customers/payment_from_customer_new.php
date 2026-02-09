<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<style>
/* Compact table styling for slimmer rows */
#advances-table td, #advances-table th,
#invoices-table td, #invoices-table th,
#returns-table td, #returns-table th,
#creditmemo-table td, #creditmemo-table th {
    padding: 3px 6px !important;
    line-height: 1.1 !important;
    vertical-align: middle !important;
    font-size: 12px !important;
}

#advances-table tbody tr,
#invoices-table tbody tr,
#returns-table tbody tr,
#creditmemo-table tbody tr {
    height: 28px !important;
}

/* Make input fields in tables more compact */
#invoices-table .payment-amount {
    height: 24px !important;
    padding: 1px 4px !important;
    font-size: 11px !important;
}

/* Compact checkbox styling */
#advances-table input[type="checkbox"],
#invoices-table input[type="checkbox"],
#returns-table input[type="checkbox"],
#creditmemo-table input[type="checkbox"] {
    margin: 0 !important;
    transform: scale(0.8);
}

/* Compact table headers */
#advances-table thead th,
#invoices-table thead th,
#returns-table thead th,
#creditmemo-table thead th {
    font-size: 11px !important;
    font-weight: bold !important;
    padding: 4px 6px !important;
}

/* Compact table footers */
#advances-table tfoot td,
#invoices-table tfoot td,
#returns-table tfoot td,
#creditmemo-table tfoot td {
    padding: 3px 6px !important;
    line-height: 1.1 !important;
    font-size: 12px !important;
    vertical-align: middle !important;
}

#advances-table tfoot tr,
#invoices-table tfoot tr,
#returns-table tfoot tr,
#creditmemo-table tfoot tr {
    height: 28px !important;
}
</style>
<script>
    $(document).ready(function() {
        // Initialize date picker
        $('#date').datetimepicker({
            format: 'dd/mm/yyyy',
            autoclose: true,
            todayHighlight: true
        });

        // Handle customer selection change
        $('#customer').change(function() {
            var customer_id = $(this).val();
            if (customer_id) {
                // Fetch customer info via AJAX
                $.ajax({
                    url: '<?= admin_url('customers/customer_limit_info') ?>',
                    type: 'GET',
                    data: { customer_id: customer_id },
                    dataType: 'json',
                    success: function(response) {
                        if (response.customer_name) {
                            // Clear checked invoices order when customer changes
                            checkedInvoicesOrder = [];
                            
                            $('#customer-info').show();
                            $('#customer-name').text(response.customer_name);
                            $('#credit-limit').text('<?= $this->sma->formatMoney(0) ?>'.replace('0.00', response.credit_limit.toFixed(2)));
                            $('#current-balance').text('<?= $this->sma->formatMoney(0) ?>'.replace('0.00', response.current_balance.toFixed(2)));
                            $('#remaining-limit').text('<?= $this->sma->formatMoney(0) ?>'.replace('0.00', response.remaining_limit.toFixed(2)));
                            $('#available-advance').text('<?= $this->sma->formatMoney(0) ?>'.replace('0.00', response.available_advance.toFixed(2)));
                            $('#payment-term').text(response.payment_term || 'N/A');
                            
                            // Load customer advances
                            loadCustomerAdvances(customer_id);
                            // Load customer invoices
                            loadCustomerInvoices(customer_id);
                            // Load customer returns
                            loadCustomerReturns(customer_id);
                            // Load customer credit memos
                            loadCustomerCreditMemos(customer_id);
                        } else {
                            $('#customer-info').hide();
                            $('#advances-section').hide();
                            $('#invoices-section').hide();
                            $('#returns-section').hide();
                            $('#creditmemo-section').hide();
                        }
                    },
                    error: function() {
                        $('#customer-info').hide();
                        $('#invoices-section').hide();
                        alert('Error fetching customer information');
                    }
                });
            } else {
                $('#customer-info').hide();
                $('#advances-section').hide();
                $('#invoices-section').hide();
                $('#returns-section').hide();
                $('#creditmemo-section').hide();
            }
        });

        // Function to load customer invoices
        function loadCustomerInvoices(customer_id) {
            $.ajax({
                url: '<?= admin_url('customers/get_customer_invoices') ?>',
                type: 'GET',
                data: { customer_id: customer_id },
                dataType: 'json',
                success: function(invoices) {
                    displayInvoices(invoices);
                },
                error: function() {
                    $('#invoices-section').hide();
                    alert('Error loading customer invoices');
                }
            });
        }

        // Function to display invoices in table
        function displayInvoices(invoices) {
            var tbody = $('#invoices-table tbody');
            tbody.empty();
            
            // Reset select all checkbox
            $('#select-all-invoices').prop('checked', false);
            
            // Initialize totals
            var totalAmount = 0;
            var totalPaid = 0;
            var totalOutstanding = 0;
            
            if (invoices.length > 0) {
                $.each(invoices, function(index, invoice) {
                    var row = '<tr>' +
                        '<td><input type="checkbox" class="invoice-checkbox" name="invoice_ids[]" value="' + invoice.id + '" data-amount="' + invoice.outstanding_amount + '"></td>' +
                        '<td>' + invoice.date + '</td>' +
                        '<td>' + invoice.id + '</td>' +
                        '<td>' + invoice.type + '</td>' +
                        '<td class="text-right">' + parseFloat(invoice.grand_total).toFixed(2) + '</td>' +
                        '<td class="text-right">' + parseFloat(invoice.total_paid).toFixed(2) + '</td>' +
                        '<td class="text-right">' + parseFloat(invoice.outstanding_amount).toFixed(2) + '</td>' +
                        '<td class="text-right"><span class="invoice-payment-amount" id="invoice-payment-' + invoice.id + '">0.00</span>' +
                        '<input type="hidden" name="invoice_amounts[' + invoice.id + ']" value="0.00" id="invoice-amount-' + invoice.id + '"></td>' +
                    '</tr>';
                    tbody.append(row);
                    
                    // Accumulate totals
                    totalAmount += parseFloat(invoice.grand_total);
                    totalPaid += parseFloat(invoice.total_paid);
                    totalOutstanding += parseFloat(invoice.outstanding_amount);
                });
                $('#invoices-section').show();
            } else {
                tbody.append('<tr><td colspan="8" class="text-center">No invoices found for this customer</td></tr>');
                $('#invoices-section').show();
            }
            
            // Update total summary
            $('#total-amount').text(totalAmount.toFixed(2));
            $('#total-paid').text(totalPaid.toFixed(2));
            $('#total-outstanding').text(totalOutstanding.toFixed(2));
            $('#total-payment-summary').text('0.00'); // Reset payment summary
            $('#total-payment-summary').text('0.00'); // Reset payment summary
        }

        // Function to load customer returns
        function loadCustomerReturns(customer_id) {
            $.ajax({
                url: '<?= admin_url('customers/get_customer_returns') ?>',
                type: 'GET',
                data: { customer_id: customer_id },
                dataType: 'json',
                success: function(returns) {
                    displayReturns(returns);
                },
                error: function() {
                    $('#returns-section').hide();
                    alert('Error loading customer returns');
                }
            });
        }

        // Function to display returns in table
        function displayReturns(returns) {
            var tbody = $('#returns-table tbody');
            tbody.empty();
            
            // Reset select all checkbox
            $('#select-all-returns').prop('checked', false);
            
            // Initialize totals
            var totalAmount = 0;
            var totalPaid = 0;
            var totalOutstanding = 0;
            
            if (returns.length > 0) {
                $.each(returns, function(index, return_item) {
                    var row = '<tr>' +
                        '<td><input type="checkbox" class="return-checkbox" name="return_ids[]" value="' + return_item.id + '" data-amount="' + return_item.outstanding_amount + '"></td>' +
                        '<td>' + return_item.date + '</td>' +
                        '<td>' + return_item.reference_no + '</td>' +
                        '<td>' + return_item.type + '</td>' +
                        '<td class="text-right">' + parseFloat(return_item.grand_total).toFixed(2) + '</td>' +
                        '<td class="text-right">' + parseFloat(return_item.total_paid || 0).toFixed(2) + '</td>' +
                        '<td class="text-right">' + parseFloat(return_item.outstanding_amount || 0).toFixed(2) + '</td>' +
                        '<td class="text-right"><span class="return-used-amount" id="return-used-' + return_item.id + '">0.00</span>' +
                        '<input type="hidden" name="return_amounts[' + return_item.id + ']" value="0.00" id="return-amount-' + return_item.id + '"></td>' +
                    '</tr>';
                    tbody.append(row);
                    
                    // Accumulate totals
                    totalAmount += parseFloat(return_item.grand_total);
                    totalPaid += parseFloat(return_item.total_paid || 0);
                    totalOutstanding += parseFloat(return_item.outstanding_amount || 0);
                });
                $('#returns-section').show();
            } else {
                tbody.append('<tr><td colspan="8" class="text-center">No returns found for this customer</td></tr>');
                $('#returns-section').show();
            }
            
            // Update total summary
            $('#returns-total-amount').text(totalAmount.toFixed(2));
            $('#returns-total-paid').text(totalPaid.toFixed(2));
            $('#returns-total-outstanding').text(totalOutstanding.toFixed(2));
            $('#returns-total-used').text('0.00');
        }

        // Function to load customer credit memos
        function loadCustomerCreditMemos(customer_id) {
            $.ajax({
                url: '<?= admin_url('customers/get_customer_credit_memos') ?>',
                type: 'GET',
                data: { customer_id: customer_id },
                dataType: 'json',
                success: function(creditmemos) {
                    displayCreditMemos(creditmemos);
                },
                error: function() {
                    $('#creditmemo-section').hide();
                    alert('Error loading customer credit memos');
                }
            });
        }

        // Function to display credit memos in table
        function displayCreditMemos(creditmemos) {
            var tbody = $('#creditmemo-table tbody');
            tbody.empty();
            
            // Reset select all checkbox
            $('#select-all-creditmemos').prop('checked', false);
            
            // Initialize totals
            var totalAmount = 0;
            var totalUsed = 0;
            var totalAvailable = 0;
            
            if (creditmemos.length > 0) {
                $.each(creditmemos, function(index, creditmemo) {
                    var row = '<tr>' +
                        '<td><input type="checkbox" class="creditmemo-checkbox" name="creditmemo_ids[]" value="' + creditmemo.id + '" data-amount="' + creditmemo.available_balance + '"></td>' +
                        '<td>' + creditmemo.date + '</td>' +
                        '<td>' + creditmemo.reference_no + '</td>' +
                        '<td>' + creditmemo.type + '</td>' +
                        '<td class="text-right">' + parseFloat(creditmemo.amount).toFixed(2) + '</td>' +
                        '<td class="text-right">' + parseFloat(creditmemo.used_amount || 0).toFixed(2) + '</td>' +
                        '<td class="text-right">' + parseFloat(creditmemo.available_balance || 0).toFixed(2) + '</td>' +
                        '<td class="text-right"><span class="creditmemo-applied-amount" id="creditmemo-applied-' + creditmemo.id + '">0.00</span>' +
                        '<input type="hidden" name="creditmemo_amounts[' + creditmemo.id + ']" value="0.00" id="creditmemo-amount-' + creditmemo.id + '"></td>' +
                    '</tr>';
                    tbody.append(row);
                    
                    // Accumulate totals
                    totalAmount += parseFloat(creditmemo.amount);
                    totalUsed += parseFloat(creditmemo.used_amount || 0);
                    totalAvailable += parseFloat(creditmemo.available_balance || 0);
                });
                $('#creditmemo-section').show();
            } else {
                tbody.append('<tr><td colspan="8" class="text-center">No credit memos found for this customer</td></tr>');
                $('#creditmemo-section').show();
            }
            
            // Update total summary
            $('#creditmemo-total-amount').text(totalAmount.toFixed(2));
            $('#creditmemo-total-used').text(totalUsed.toFixed(2));
            $('#creditmemo-total-available').text(totalAvailable.toFixed(2));
            $('#creditmemo-total-applied').text('0.00');
        }

        // Function to load customer advances
        function loadCustomerAdvances(customer_id) {
            $.ajax({
                url: '<?= admin_url('customers/get_customer_advances') ?>',
                type: 'GET',
                data: { customer_id: customer_id },
                dataType: 'json',
                success: function(advances) {
                    displayAdvances(advances);
                },
                error: function() {
                    $('#advances-section').hide();
                    alert('Error loading customer advances');
                }
            });
        }

        // Function to display advances in table
        function displayAdvances(advances) {
            var tbody = $('#advances-table tbody');
            tbody.empty();
            
            // Reset select all checkbox
            $('#select-all-advances').prop('checked', false);
            
            // Initialize totals
            var totalAmount = 0;
            var totalUsed = 0;
            var totalAvailable = 0;
            
            if (advances.length > 0) {
                $.each(advances, function(index, advance) {
                    var row = '<tr>' +
                        '<td><input type="checkbox" class="advance-checkbox" name="advance_ids[]" value="' + advance.id + '" data-amount="' + advance.available_balance + '"></td>' +
                        '<td>' + advance.date + '</td>' +
                        '<td>' + advance.reference_no + '</td>' +
                        '<td>' + advance.type + '</td>' +
                        '<td class="text-right">' + parseFloat(advance.amount).toFixed(2) + '</td>' +
                        '<td class="text-right">' + parseFloat(advance.used_amount || 0).toFixed(2) + '</td>' +
                        '<td class="text-right">' + parseFloat(advance.available_balance || 0).toFixed(2) + '</td>' +
                        '<td class="text-right"><span class="advance-applied-amount" id="advance-applied-' + advance.id + '">0.00</span>' +
                        '<input type="hidden" name="advance_amounts[' + advance.id + ']" value="0.00" id="advance-amount-' + advance.id + '"></td>' +
                    '</tr>';
                    tbody.append(row);
                    
                    // Accumulate totals
                    totalAmount += parseFloat(advance.amount);
                    totalUsed += parseFloat(advance.used_amount || 0);
                    totalAvailable += parseFloat(advance.available_balance || 0);
                });
                $('#advances-section').show();
            } else {
                tbody.append('<tr><td colspan="8" class="text-center">No advances found for this customer</td></tr>');
                $('#advances-section').show();
            }
            
            // Update total summary
            $('#advances-total-amount').text(totalAmount.toFixed(2));
            $('#advances-total-used').text(totalUsed.toFixed(2));
            $('#advances-total-available').text(totalAvailable.toFixed(2));
            $('#advances-total-applied').text('0.00');
        }

        // Global array to track order of checked invoices
        var checkedInvoicesOrder = [];

        // Handle invoice checkbox changes
        $(document).on('change', '.invoice-checkbox', function() {
            var isChecked = $(this).is(':checked');
            var invoiceId = $(this).val();
            var row = $(this).closest('tr');
            var paymentInput = row.find('.payment-amount');
            var invoiceOutstanding = parseFloat($(this).data('amount')) || 0;

            if (isChecked) {
                // Add to order tracking if not already present
                if (checkedInvoicesOrder.indexOf(invoiceId) === -1) {
                    checkedInvoicesOrder.push(invoiceId);
                }
                paymentInput.prop('disabled', false);
                paymentInput.val(invoiceOutstanding.toFixed(2));
            } else {
                // Remove from order tracking
                var index = checkedInvoicesOrder.indexOf(invoiceId);
                if (index > -1) {
                    checkedInvoicesOrder.splice(index, 1);
                }
                paymentInput.prop('disabled', true);
                paymentInput.val('');
                // Explicitly reset hidden input for unchecked invoice
                $('#invoice-amount-' + invoiceId).val('0.00');
            }

            calculateTotalPayment();
        });

        // Handle return checkbox changes
        $(document).on('change', '.return-checkbox', function() {
            var isChecked = $(this).is(':checked');
            var returnId = $(this).val();
            
            if (!isChecked) {
                // Explicitly reset hidden input for unchecked return
                $('#return-amount-' + returnId).val('0.00');
            }
            
            calculateTotalPayment();
        });

        // Handle credit memo checkbox changes
        $(document).on('change', '.creditmemo-checkbox', function() {
            var isChecked = $(this).is(':checked');
            var creditmemoId = $(this).val();
            
            if (!isChecked) {
                // Explicitly reset hidden input for unchecked credit memo
                $('#creditmemo-amount-' + creditmemoId).val('0.00');
            }
            
            calculateTotalPayment();
        });

        // Handle advance checkbox changes
        $(document).on('change', '.advance-checkbox', function() {
            var isChecked = $(this).is(':checked');
            var advanceId = $(this).val();
            
            if (!isChecked) {
                // Explicitly reset hidden input for unchecked advance
                $('#advance-amount-' + advanceId).val('0.00');
            }
            
            calculateTotalPayment();
        });

        // Handle payment amount changes
        $(document).on('input', '#payment_amount', function() {
            // Clear all selections and data when payment amount changes
            // This prevents calculation errors and ensures clean slate

            // Clear all checkboxes
            $('.invoice-checkbox').prop('checked', false);
            $('.advance-checkbox').prop('checked', false);
            $('.return-checkbox').prop('checked', false);
            $('.creditmemo-checkbox').prop('checked', false);

            // Clear select all checkboxes
            $('#select-all-invoices').prop('checked', false);
            $('#select-all-advances').prop('checked', false);
            $('#select-all-returns').prop('checked', false);
            $('#select-all-creditmemos').prop('checked', false);

            // Reset order tracking
            checkedInvoicesOrder = [];

            // Reset all individual row amounts (but keep the loaded data visible)
            $('.invoice-payment-amount').text('0.00');
            $('.invoice-amount').val('0.00');
            $('.return-used-amount').text('0.00');
            $('.return-amount').val('0.00');
            $('.creditmemo-applied-amount').text('0.00');
            $('.creditmemo-amount').val('0.00');
            $('.advance-applied-amount').text('0.00');
            $('.advance-amount').val('0.00');

            $('#total-display').text('0.00');
            $('#total-outstanding-selected').text('0.00');
            $('#remaining-amount').text('0.00');
            $('#priority-total-display').text('0.00');
            $('#advance-from-payment').text('0.00');

            $('#advances-total-applied').text('0.00');
            $('#returns-total-used').text('0.00');
            $('#creditmemo-total-applied').text('0.00');
            $('#total-payment-summary').text('0.00');

            // Reset total summary
            $('#total-amount').text('0.00');
            $('#total-paid').text('0.00');
            $('#total-outstanding').text('0.00');
            $('#total-payment-summary').text('0.00');

            // Update payment amount display immediately with entered amount
            var currentPaymentAmount = parseFloat($('#payment_amount').val()) || 0;
            $('#payment-amount-display').text(currentPaymentAmount.toFixed(2));

            calculateTotalPayment();
        });

        // Function to calculate total payment
        function calculateTotalPayment() {
            var paymentAmount = parseFloat($('#payment_amount').val()) || 0;

            // Reset all individual row amounts
            $('.invoice-payment-amount').text('0.00');
            $('.invoice-amount').val('0.00');
            $('.return-used-amount').text('0.00');
            $('.return-amount').val('0.00');
            $('.creditmemo-applied-amount').text('0.00');
            $('.creditmemo-amount').val('0.00');
            $('.advance-applied-amount').text('0.00');
            $('.advance-amount').val('0.00');

            // Get selected items
            var selectedInvoices = $('.invoice-checkbox:checked');
            var selectedAdvances = $('.advance-checkbox:checked');
            var selectedReturns = $('.return-checkbox:checked');
            var selectedCreditMemos = $('.creditmemo-checkbox:checked');

            // Calculate totals
            var totalInvoiceOutstanding = 0;
            selectedInvoices.each(function() {
                var amount = parseFloat($(this).data('amount')) || 0;
                totalInvoiceOutstanding += isNaN(amount) ? 0 : amount;
            });

            var totalAdvancesAvailable = 0;
            selectedAdvances.each(function() {
                var amount = parseFloat($(this).data('amount')) || 0;
                totalAdvancesAvailable += isNaN(amount) ? 0 : amount;
            });

            var totalReturnsAvailable = 0;
            selectedReturns.each(function() {
                var amount = parseFloat($(this).data('amount')) || 0;
                totalReturnsAvailable += isNaN(amount) ? 0 : amount;
            });

            var totalCreditMemosAvailable = 0;
            selectedCreditMemos.each(function() {
                var amount = parseFloat($(this).data('amount')) || 0;
                totalCreditMemosAvailable += isNaN(amount) ? 0 : amount;
            });

            var totalPriorityAvailable = totalAdvancesAvailable + totalReturnsAvailable + totalCreditMemosAvailable;

            // Priority settlement: Apply advances, returns, credit memos first (up to available amounts)
            var priorityApplied = Math.min(totalPriorityAvailable || 0, totalInvoiceOutstanding || 0);
            var remainingOutstanding = (totalInvoiceOutstanding || 0) - priorityApplied;

            // Payment amount applied to remaining outstanding (but we use the new remaining logic above)
            var paymentApplied = Math.min(paymentAmount || 0, remainingOutstanding);

            // Total applied to invoices
            var totalAppliedToInvoices = priorityApplied + paymentApplied;

            // Apply settlement amounts sequentially to selected invoices (in checking order)
            // Allow excess to be posted as advance
            if (totalAppliedToInvoices > 0 && checkedInvoicesOrder.length > 0) {
                var remainingSettlement = totalAppliedToInvoices;

                // Apply settlement to invoices in the order they were checked
                for (var i = 0; i < checkedInvoicesOrder.length && remainingSettlement > 0; i++) {
                    var invoiceId = checkedInvoicesOrder[i];
                    var checkbox = $('.invoice-checkbox[value="' + invoiceId + '"]');
                    var invoiceOutstanding = parseFloat(checkbox.data('amount')) || 0;
                    var amountToApply = Math.min(invoiceOutstanding, remainingSettlement);
                    $('#invoice-payment-' + invoiceId).text(amountToApply.toFixed(2));
                    $('#invoice-amount-' + invoiceId).val(amountToApply.toFixed(2));
                    remainingSettlement -= amountToApply;
                }

                // Any remaining settlement becomes advance
                var excessAsAdvance = remainingSettlement;
                $('#advance-from-payment').text(excessAsAdvance.toFixed(2));
            } else {
                // No invoices selected, all settlement becomes advance
                $('#advance-from-payment').text(totalAppliedToInvoices.toFixed(2));
            }

            // Update priority item displays (show how much of each priority item was used)
            var priorityRemaining = priorityApplied;

            // Update advance applied amounts
            selectedAdvances.each(function() {
                var available = parseFloat($(this).data('amount')) || 0;
                var applied = Math.min(available, priorityRemaining);
                $('#advance-applied-' + $(this).val()).text(applied.toFixed(2));
                $('#advance-amount-' + $(this).val()).val(applied.toFixed(2));
                priorityRemaining -= applied;
            });

            // Update return used amounts
            selectedReturns.each(function() {
                var available = parseFloat($(this).data('amount')) || 0;
                var applied = Math.min(available, priorityRemaining);
                $('#return-used-' + $(this).val()).text(applied.toFixed(2));
                $('#return-amount-' + $(this).val()).val(applied.toFixed(2));
                priorityRemaining -= applied;
            });

            // Update credit memo applied amounts
            selectedCreditMemos.each(function() {
                var available = parseFloat($(this).data('amount')) || 0;
                var applied = Math.min(available, priorityRemaining);
                $('#creditmemo-applied-' + $(this).val()).text(applied.toFixed(2));
                $('#creditmemo-amount-' + $(this).val()).val(applied.toFixed(2));
                priorityRemaining -= applied;
            });

            // Update displays
            $('#total-display').text(isNaN(totalAppliedToInvoices) ? '0.00' : totalAppliedToInvoices.toFixed(2));
            $('#total-outstanding-selected').text(isNaN(totalInvoiceOutstanding) ? '0.00' : totalInvoiceOutstanding.toFixed(2));
            
            // Update priority breakdown display
            $('#priority-total-display').text(isNaN(totalPriorityAvailable) ? '0.00' : totalPriorityAvailable.toFixed(2));
            // $('#payment-amount-display').text(paymentApplied.toFixed(2)); // Moved below

            // Update tfoot totals
            var totalAdvancesApplied = 0;
            $('.advance-applied-amount').each(function() {
                totalAdvancesApplied += parseFloat($(this).text()) || 0;
            });
            $('#advances-total-applied').text(totalAdvancesApplied.toFixed(2));

            var totalReturnsUsed = 0;
            $('.return-used-amount').each(function() {
                totalReturnsUsed += parseFloat($(this).text()) || 0;
            });
            $('#returns-total-used').text(totalReturnsUsed.toFixed(2));

            var totalCreditMemosApplied = 0;
            $('.creditmemo-applied-amount').each(function() {
                totalCreditMemosApplied += parseFloat($(this).text()) || 0;
            });
            $('#creditmemo-total-applied').text(totalCreditMemosApplied.toFixed(2));

            var totalInvoicesPaid = 0;
            $('.invoice-payment-amount').each(function() {
                totalInvoicesPaid += parseFloat($(this).text()) || 0;
            });
            $('#total-payment-summary').text(totalInvoicesPaid.toFixed(2));

            // Update payment amount display: entered payment + selected priority items
            var totalPaymentAmount = (paymentAmount || 0) + (totalPriorityAvailable || 0);
            $('#payment-amount-display').text(isNaN(totalPaymentAmount) ? '0.00' : totalPaymentAmount.toFixed(2));

            // Calculate remaining amount: total applied to invoices - total settlement amount
            var remainingAmount = (totalAppliedToInvoices || 0) - (totalPaymentAmount || 0);

            $('#remaining-amount').text(isNaN(remainingAmount) ? '0.00' : remainingAmount.toFixed(2));
            // Update remaining amount styling and button state
            updateRemainingAmountStyling(remainingAmount);
        }

        // Function to update remaining amount styling and button state
        function updateRemainingAmountStyling(remainingAmount) {
            var remainingElement = $('#remaining-amount');
            var submitBtn = $('#submit-btn');
            var selectedInvoices = $('.invoice-checkbox:checked');

            // Remove existing classes
            remainingElement.removeClass('remaining-zero remaining-nonzero');

            // Enable submit only when remaining amount is exactly zero (exact settlement)
            if (selectedInvoices.length > 0 && Math.abs(remainingAmount) < 0.01) { // Use small epsilon for floating point comparison
                // Green background for exact settlement
                remainingElement.css({
                    'background-color': '#d4edda',
                    'color': '#155724',
                    'padding': '2px 8px',
                    'border-radius': '3px',
                    'display': 'inline-block'
                });
                submitBtn.prop('disabled', false);
            } else {
                // Red background if remaining is not zero (over or under payment)
                remainingElement.css({
                    'background-color': '#f8d7da',
                    'color': '#721c24',
                    'padding': '2px 8px',
                    'border-radius': '3px',
                    'display': 'inline-block'
                });
                submitBtn.prop('disabled', true);
            }
        }

        // Handle select all invoices checkbox
        $(document).on('change', '#select-all-invoices', function() {
            var isChecked = $(this).is(':checked');
            $('.invoice-checkbox').prop('checked', isChecked).trigger('change');
        });

        // Handle select all returns checkbox
        $(document).on('change', '#select-all-returns', function() {
            var isChecked = $(this).is(':checked');
            $('.return-checkbox').prop('checked', isChecked).trigger('change');
        });

        // Handle select all credit memos checkbox
        $(document).on('change', '#select-all-creditmemos', function() {
            var isChecked = $(this).is(':checked');
            $('.creditmemo-checkbox').prop('checked', isChecked).trigger('change');
        });

        // Handle select all advances checkbox
        $(document).on('change', '#select-all-advances', function() {
            var isChecked = $(this).is(':checked');
            $('.advance-checkbox').prop('checked', isChecked).trigger('change');
        });

        // Hide customer info initially
        $('#customer-info').hide();
        
        // Initialize remaining amount styling
        updateRemainingAmountStyling(0);
    });
</script>

<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-plus"></i><?= lang('add_customer_payment_new'); ?></h2>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
                <?php
                $attrib = ['data-toggle' => 'validator', 'role' => 'form'];
                echo admin_form_open_multipart('customers/payment_from_customer_new', $attrib);
                ?>
                <!--<form action="<?= admin_url('customers/payment_from_customer_new') ?>" method="post" enctype="multipart/form-data" data-toggle="validator" role="form">-->

                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <?= lang('Date', 'date'); ?>
                            <?php echo form_input('date', ($date ?? date('d/m/Y')), 'class="form-control input-tip date" id="date"'); ?>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            <?= lang('reference_no', 'reference_no'); ?>
                            <?php echo form_input('reference_no', set_value('reference_no'), 'class="form-control input-tip" id="reference_no" required="required"'); ?>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            <?= lang('customer', 'customer'); ?>
                            <?php
                            $customer_options = ['' => lang('select_customer')];
                            foreach ($customers as $customer) {
                                $customer_options[$customer->id] = $customer->company . ' (' . $customer->name . ') - ' . $customer->sequence_code;
                            }
                            echo form_dropdown('customer', $customer_options, set_value('customer'), 'class="form-control input-tip select" id="customer" required="required" style="width:100%;"');
                            ?>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <?= lang('Ledger', 'pledger'); ?>
                            <?php
                            $selected_ledger_id = isset($ledger_id) ? $ledger_id : '';
                            $sp[''] = '';
                            foreach ($ledgers as $ledger) {
                                $sp[$ledger->id] = $ledger->name;
                            }
                            echo form_dropdown('ledger', $sp, $selected_ledger_id, 'id="ledger_id" class="form-control input-tip select" data-placeholder="' . lang('select') . ' ' . lang('ledger') . '" required="required" style="width:100%;" ', null); ?>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <?= lang('payment_amount', 'payment_amount'); ?>
                            <?php echo form_input('payment_amount', set_value('payment_amount', '0.00'), 'class="form-control input-tip" id="payment_amount" step="0.01" min="0" placeholder="0.00"'); ?>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            <?= lang('note', 'note'); ?>
                            <?php echo form_input('note', set_value('note'), 'class="form-control input-tip" id="note" placeholder="Enter payment notes..."'); ?>
                        </div>
                    </div>
                </div>

                <!-- Customer Information Box -->
                <div id="customer-info" class="row" style="display: none;">
                    <div class="col-md-12">
                        <div class="box box-primary" style="border-color: #dbd2d2;padding:10px;">
                            
                            <div class="box-body" style="background-color: #f8f9fa;">
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="info-item" style="margin-bottom: 10px;">
                                            <strong style="color: #1976d2;"><?= lang('customer'); ?>:</strong><br>
                                            <span id="customer-name" style="font-size: 14px; color: #333;"></span>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="info-item" style="margin-bottom: 10px;">
                                            <strong style="color: #388e3c;"><?= lang('credit_limit'); ?>:</strong><br>
                                            <span id="credit-limit" style="font-size: 14px; color: #388e3c; font-weight: bold;"></span>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="info-item" style="margin-bottom: 10px;">
                                            <strong style="color: #f57c00;"><?= lang('current_balance'); ?>:</strong><br>
                                            <span id="current-balance" style="font-size: 14px; color: #f57c00; font-weight: bold;"></span>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="info-item" style="margin-bottom: 10px;">
                                            <strong style="color: #7b1fa2;"><?= lang('remaining_limit'); ?>:</strong><br>
                                            <span id="remaining-limit" style="font-size: 14px; color: #7b1fa2; font-weight: bold;"></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="row" style="margin-top: 10px;">
                                    <div class="col-md-3">
                                        <div class="info-item">
                                            <strong style="color: #0097a7;"><?= lang('available_advance'); ?>:</strong><br>
                                            <span id="available-advance" style="font-size: 14px; color: #0097a7; font-weight: bold;"></span>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="info-item">
                                            <strong style="color: #c2185b;"><?= lang('payment_term'); ?>:</strong><br>
                                            <span id="payment-term" style="font-size: 14px; color: #c2185b; font-weight: bold;"></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Customer Advances Section -->
                <div id="advances-section" class="row" style="display: none; margin-top: 20px;">
                    <div class="col-md-12">
                        <div class="box box-info">
                            <div class="box-header with-border">
                                <h3 class="box-title">Customer Advances</h3>
                            </div>
                            <div class="box-body">
                                <div class="table-responsive">
                                    <table id="advances-table" class="table table-striped table-bordered">
                                        <thead style="background-color: #d1ecf1;">
                                            <tr>
                                                <th width="5%"><input type="checkbox" id="select-all-advances"></th>
                                                <th>Date</th>
                                                <th>Reference No</th>
                                                <th>Type</th>
                                                <th class="text-right">Advance Amount</th>
                                                <th class="text-right">Used Amount</th>
                                                <th class="text-right">Available Balance</th>
                                                <th class="text-right">Applied Amount</th>
                                            </tr>
                                        </thead>
                                        <tfoot style="background-color: #bee5eb; font-weight: bold;">
                                            <tr>
                                                <td colspan="4"><strong>Advances Total</strong></td>
                                                <td class="text-right" id="advances-total-amount">0.00</td>
                                                <td class="text-right" id="advances-total-used">0.00</td>
                                                <td class="text-right" id="advances-total-available">0.00</td>
                                                <td class="text-right" id="advances-total-applied">0.00</td>
                                            </tr>
                                        </tfoot>
                                        <tbody>
                                            <!-- Advances will be loaded here -->
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Customer Invoices Section -->
                <div id="invoices-section" class="row" style="display: none; margin-top: 20px;">
                    <div class="col-md-12">
                        <div class="box box-primary">
                            
                            <div class="box-body">
                                <div class="table-responsive">
                                    <table id="invoices-table" class="table table-striped table-bordered">
                                        <thead style="background-color: #f5f5f5;">
                                            <tr>
                                                <th width="5%"><input type="checkbox" id="select-all-invoices"></th>
                                                <th>Date</th>
                                                <th>Reference No</th>
                                                <th>Type</th>
                                                <th class="text-right">Total Amount</th>
                                                <th class="text-right">Paid Amount</th>
                                                <th class="text-right">Outstanding</th>
                                                <th class="text-right">Payment Amount</th>
                                            </tr>
                                        </thead>
                                        <tfoot style="background-color: #e8f5e8; font-weight: bold;">
                                            <tr>
                                                <td colspan="4"><strong>Invoices Total</strong></td>
                                                <td class="text-right" id="total-amount">0.00</td>
                                                <td class="text-right" id="total-paid">0.00</td>
                                                <td class="text-right" id="total-outstanding">0.00</td>
                                            </tr>
                                        </tfoot>
                                        <tbody>
                                            <!-- Invoices will be loaded here -->
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Customer Returns Section -->
                <div id="returns-section" class="row" style="display: none; margin-top: 20px;">
                    <div class="col-md-12">
                        <div class="box box-warning">
                            <div class="box-header with-border">
                                <h3 class="box-title">Customer Returns</h3>
                            </div>
                            <div class="box-body">
                                <div class="table-responsive">
                                    <table id="returns-table" class="table table-striped table-bordered">
                                        <thead style="background-color: #fff3cd;">
                                            <tr>
                                                <th width="5%"><input type="checkbox" id="select-all-returns"></th>
                                                <th>Date</th>
                                                <th>Reference No</th>
                                                <th>Type</th>
                                                <th class="text-right">Return Amount</th>
                                                <th class="text-right">Paid Amount</th>
                                                <th class="text-right">Outstanding</th>
                                                <th class="text-right">Used Amount</th>
                                            </tr>
                                        </thead>
                                        <tfoot style="background-color: #ffeaa7; font-weight: bold;">
                                            <tr>
                                                <td colspan="4"><strong>Returns Total</strong></td>
                                                <td class="text-right" id="returns-total-amount">0.00</td>
                                                <td class="text-right" id="returns-total-paid">0.00</td>
                                                <td class="text-right" id="returns-total-outstanding">0.00</td>
                                                <td class="text-right" id="returns-total-used">0.00</td>
                                            </tr>
                                        </tfoot>
                                        <tbody>
                                            <!-- Returns will be loaded here -->
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Customer Credit Memo Section -->
                <div id="creditmemo-section" class="row" style="display: none; margin-top: 20px;">
                    <div class="col-md-12">
                        <div class="box box-success">
                            <div class="box-header with-border">
                                <h3 class="box-title">Customer Credit Memos</h3>
                            </div>
                            <div class="box-body">
                                <div class="table-responsive">
                                    <table id="creditmemo-table" class="table table-striped table-bordered">
                                        <thead style="background-color: #d4edda;">
                                            <tr>
                                                <th width="5%"><input type="checkbox" id="select-all-creditmemos"></th>
                                                <th>Date</th>
                                                <th>Reference No</th>
                                                <th>Type</th>
                                                <th class="text-right">Credit Amount</th>
                                                <th class="text-right">Used Amount</th>
                                                <th class="text-right">Available Balance</th>
                                                <th class="text-right">Applied Amount</th>
                                            </tr>
                                        </thead>
                                        <tfoot style="background-color: #c3e6cb; font-weight: bold;">
                                            <tr>
                                                <td colspan="4"><strong>Credit Memo Total</strong></td>
                                                <td class="text-right" id="creditmemo-total-amount">0.00</td>
                                                <td class="text-right" id="creditmemo-total-used">0.00</td>
                                                <td class="text-right" id="creditmemo-total-available">0.00</td>
                                                <td class="text-right" id="creditmemo-total-applied">0.00</td>
                                            </tr>
                                        </tfoot>
                                        <tbody>
                                            <!-- Credit memos will be loaded here -->
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <div class="well" style="background-color: #f8f9fa; border: 1px solid #dee2e6; border-radius: 5px; padding: 15px; margin-bottom: 20px;">
                            <div class="row">
                                <div class="col-md-12" style="margin-bottom: 15px;">
                                    <div class="form-group">
                                        <label class="control-label" style="font-weight: bold; font-size: 14px;">
                                            Total Outstanding (Selected): <span id="total-outstanding-selected" style="color: #f39c12; font-size: 16px; font-weight: bold;">0.00</span>
                                        </label>
                                        <br>
                                        <small class="text-muted">Sum of selected invoice outstanding minus selected returns/credit memos</small>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-12" style="margin-bottom: 15px;">
                                    <div class="form-group">
                                        <label class="control-label" style="font-weight: bold; font-size: 14px;">
                                            Payment Breakdown:
                                        </label>
                                        <br>
                                        <div style="background-color: #f8f9fa; padding: 10px; border-radius: 5px; margin-top: 5px;">
                                            <div class="row">
                                                <div class="col-md-3">
                                                    <small class="text-muted">Priority Amount (Advances/Returns/Credit Memos):</small><br>
                                                    <span id="priority-total-display" style="color: #28a745; font-size: 16px; font-weight: bold;">0.00</span>
                                                </div>
                                                <div class="col-md-3">
                                                    <small class="text-muted">Total Settlement Amount:</small><br>
                                                    <span id="payment-amount-display" style="color: #007bff; font-size: 16px; font-weight: bold;">0.00</span>
                                                </div>
                                                <div class="col-md-3">
                                                    <small class="text-muted">Total Applied:</small><br>
                                                    <span id="total-display" style="color: #2196F3; font-size: 16px; font-weight: bold;">0.00</span>
                                                </div>
                                                <div class="col-md-3">
                                                    <small class="text-muted">Advance Created:</small><br>
                                                    <span id="advance-from-payment" style="color: #ff9800; font-size: 16px; font-weight: bold;">0.00</span>
                                                </div>
                                            </div>
                                        </div>
                                        <small class="text-muted">Priority amounts are applied first, then payment amount covers remaining balance</small>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label class="control-label" style="font-weight: bold; font-size: 14px;">
                                            Remaining Amount: <span id="remaining-amount" style="font-size: 16px; font-weight: bold;">0.00</span>
                                        </label>
                                        <br>
                                        <small class="text-muted">Outstanding amount minus payment amount (must be zero to submit)</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <button type="submit" id="submit-btn" class="btn btn-primary" disabled>
                                <?= lang('submit'); ?>
                            </button>
                            <a href="<?= admin_url('customers/list_payments'); ?>" class="btn btn-default">
                                <?= lang('cancel'); ?>
                            </a>
                        </div>
                    </div>
                </div>

                <?php echo form_close(); ?>
                <!--</form>-->
            </div>
        </div>
    </div>
</div>
