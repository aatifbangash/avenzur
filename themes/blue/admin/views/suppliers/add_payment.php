<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<style>
    .required-field::after {
        content: " *";
        color: red;
        font-weight: bold;
    }
    
    .form-control[required] {
        border-left: 3px solid #ff6b6b;
    }
    
    .form-control[required]:valid {
        border-left: 3px solid #51cf66;
    }
</style>
<script>
    // Supplier Advance Ledger configuration
    var supplier_advance_ledger_configured = <?= $supplier_advance_ledger ? 'true' : 'false' ?>;
    
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
        });
        if (pspayment = localStorage.getItem('pspayment')) {
            $('#pspayment').val(pspayment);

        }

        $(document).on('change', '#pssupplier', function (e) {
            localStorage.setItem('pssupplier', $(this).val());

            loadSupplierAdvanceBalance($(this).val());
        });

        if (pssupplier = localStorage.getItem('pssupplier')) {
            $('#pssupplier').val(pssupplier);
            
            // Load advance balance immediately for pre-selected supplier
            loadSupplierAdvanceBalance(pssupplier);
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
            // Auto-calculate VAT (15% of bank charges)
            var bankCharges = parseFloat($(this).val()) || 0;
            var vat = bankCharges * 0.15; // 15% VAT
            $('#psvat').val(vat.toFixed(2));
            localStorage.setItem('psvat', vat.toFixed(2));
        });
        
        // Also trigger VAT calculation on input (real-time)
        $(document).on('input', '#psbankchargesamt', function (e) {
            var bankCharges = parseFloat($(this).val()) || 0;
            var vat = bankCharges * 0.15; // 15% VAT
            $('#psvat').val(vat.toFixed(2));
        });
        
        if (psbankchargesamt = localStorage.getItem('psbankchargesamt')) {
            $('#psbankchargesamt').val(psbankchargesamt);
            // Recalculate VAT when loading from localStorage
            var bankCharges = parseFloat(psbankchargesamt) || 0;
            var vat = bankCharges * 0.15;
            $('#psvat').val(vat.toFixed(2));
        }

        $(document).on('change', '#psvat', function (e) {
            localStorage.setItem('psvat', $(this).val());
        });
        if (psvat = localStorage.getItem('psvat')) {
            $('#psvat').val(psvat);
        }

        $(document).on('change', '#psnote', function (e) {
            localStorage.setItem('psnote', $(this).val());
        });
        if (psnote = localStorage.getItem('psnote')) {
            $('#psnote').val(psnote);
        }
        
        // Restore payment mode from localStorage
        if (payment_mode = localStorage.getItem('payment_mode')) {
            $('#payment_mode').val(payment_mode);
            if (payment_mode === 'advance_only') {
                $('#poTable-holder').hide();
                $('label[for="pspayment"]').html('<?= lang("Payment Amount") ?> <span style="color: #28a745;">(Advance Payment)</span>');
            }
        }
        
        // Also update when invoice payment amounts change
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
            
        });

        // Global variable to store current advance balance
        var current_advance_balance = 0;
        var last_loaded_supplier_id = null; // Track which supplier's balance is currently loaded

        function loadSupplierAdvanceBalance(supplier_id) {
            if (!supplier_id) {
                $('#advance-balance').text('Please select a supplier');
                $('#settle-with-advance').prop('disabled', true);
                current_advance_balance = 0;
                last_loaded_supplier_id = null;
                return;
            }

            // Skip if we already have the balance for this supplier
            if (last_loaded_supplier_id == supplier_id) {
                console.log('Advance balance already loaded for supplier:', supplier_id, '- skipping');
                return;
            }

            console.log('Loading advance balance for supplier:', supplier_id, 'Previous supplier:', last_loaded_supplier_id);
            
            // Mark this supplier as being loaded BEFORE the AJAX call
            last_loaded_supplier_id = supplier_id;

            $.ajax({
                url: '<?= admin_url('suppliers/get_supplier_advance_balance?supplier_id=') ?>' + supplier_id,
                type: 'GET',
                dataType: 'json',
                success: function (response) {
                    console.log('Advance Balance Response:', response);
                    
                    current_advance_balance = parseFloat(response.advance_balance) || 0;
                    
                    if (response.error) {
                        $('#advance-balance').html('<span style="color: #dc3545;">' + response.error + '</span>');
                        $('#settle-with-advance').prop('disabled', true);
                        return;
                    }
                    
                    if (current_advance_balance > 0) {
                        console.log('Setting advance balance display to:', current_advance_balance.toFixed(2));
                        $('#advance-balance').html('<strong style="color: #28a745;">Available: ' + current_advance_balance.toFixed(2) + '</strong>');
                        $('#settle-with-advance').prop('disabled', false);
                        // The table checkbox will be created dynamically in loadInvoices
                    } else {
                        $('#advance-balance').html('<span style="color: #6c757d;">No advance available</span>');
                        $('#settle-with-advance').prop('disabled', true);
                        $('#settle-with-advance').prop('checked', false);
                        $('#settle-with-advance-table').prop('disabled', true);
                        $('#settle-with-advance-table').prop('checked', false);
                    }
                    
                    if (!response.advance_ledger_configured) {
                        $('#advance-balance').html('<span style="color: #dc3545;">Advance ledger not configured</span>');
                        $('#settle-with-advance').prop('disabled', true);
                        $('#settle-with-advance-table').prop('disabled', true);
                    }
                },
                error: function (xhr, status, error) {
                    console.log('AJAX Error:', xhr.responseText);
                    console.log('Status:', status);
                    console.log('Error:', error);
                    
                    var errorMsg = 'Error loading advance balance';
                    if (xhr.responseText) {
                        try {
                            var errorResponse = JSON.parse(xhr.responseText);
                            if (errorResponse.error) {
                                errorMsg += ': ' + errorResponse.error;
                            }
                        } catch (e) {
                            // If not JSON, show first 50 characters of response
                            errorMsg += ': ' + xhr.responseText.substring(0, 50);
                        }
                    }
                    
                    $('#advance-balance').html('<span style="color: #dc3545;">' + errorMsg + '</span>');
                    $('#settle-with-advance').prop('disabled', true);
                    current_advance_balance = 0;
                    last_loaded_supplier_id = null;
                }
            });
        }

        // Global variable to store total invoice amount
        var total_invoice_amount = 0;

        function updateAdvanceRowInTable(advance_amount) {
            // Update the advance adjustment amount in the table row
            var advanceRow = $('#advance-adjustment-row');
            if (advanceRow.length > 0) {
                advanceRow.find('#advance-adjustment-amount').text(advance_amount.toFixed(2));
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

        if (localStorage.getItem('psvat')) {
            localStorage.removeItem('psvat');
            $('#psvat').val('');
        }

        window.location.reload();
    }

    // Function to update bank charges ledger requirement based on amount
    function updateBankChargesLedgerRequirement() {
        var bankChargesAmount = parseFloat($('#bank_charges').val()) || 0;
        var bankChargesSelect = $('#psbankcharges');
        var bankChargesLabel = $('#bank-charges-label');
        
        if (bankChargesAmount > 0) {
            bankChargesSelect.attr('required', 'required');
            bankChargesLabel.addClass('required-field');
        } else {
            bankChargesSelect.removeAttr('required');
            bankChargesLabel.removeClass('required-field');
        }
    }

    // Form submission validation
    function validateAdvancePayment() {
        if (!supplier_advance_ledger_configured) {
            var payment_amount = parseFloat($('#pspayment').val()) || 0;
            var total_invoice_payments = 0;
            
            // Calculate total invoice payments entered (not due amounts)
            $('#poTable tbody tr').each(function() {
                var payment_input = $(this).find('input[name="payment_amount[]"]');
                if (payment_input.length > 0) {
                    total_invoice_payments += parseFloat(payment_input.val()) || 0;
                }
            });
            
            var advance_payment = payment_amount - total_invoice_payments;
            
            if (advance_payment > 0) {
                alert('Error: Cannot process advance payment. Supplier Advance Ledger is not configured in system settings.');
                return false;
            }
        }
        return true;
    }

    // Real-time validation for bank charges
    function updateBankChargesLedgerRequirement() {
        var bankCharges = parseFloat($('#psbankchargesamt').val()) || 0;
        var bankChargesLedgerSelect = $('#psbankcharges');
        
        // Auto-calculate VAT (15% of bank charges)
        var vat = bankCharges * 0.15;
        $('#psvat').val(vat.toFixed(2));
        
        if (bankCharges > 0) {
            // Make bank charges ledger mandatory
            bankChargesLedgerSelect.attr('required', 'required');
            bankChargesLedgerSelect.closest('.col-md-4').find('label').addClass('required-field');
        } else {
            // Remove mandatory requirement
            bankChargesLedgerSelect.removeAttr('required');
            bankChargesLedgerSelect.closest('.col-md-4').find('label').removeClass('required-field');
        }
    }

    // Add form validation on submit
    $(document).ready(function() {
        // Add real-time validation for bank charges field
        $('#psbankchargesamt').on('input change', function() {
            updateBankChargesLedgerRequirement();
        });

        // Initial check on page load
        updateBankChargesLedgerRequirement();

        // Form submission validation
        $('form').on('submit', function(e) {
            // Validate advance payment
            if (!validateAdvancePayment()) {
                e.preventDefault();
                return false;
            }
        });
    });
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
            
            <!-- Hidden field for supplier advance ledger -->
            <input type="hidden" name="supplier_advance_ledger" value="<?= $supplier_advance_ledger ?? '' ?>" id="supplier_advance_ledger_hidden">
            
            <div class="col-lg-12">
                
                <div class="row">
                    <div class="col-lg-12">
                        
                        <div class="col-md-4">
                            <div class="form-group">
                                <?= lang('date', 'psdate'); ?>
                                <?php echo form_input('date', ($_POST['date'] ?? ''), 'class="form-control input-tip date" id="psdate" required="required"'); ?>
                            </div>
                        </div>
                            
                        <div class="col-md-4">
                            <div class="form-group">
                                <?= lang('reference_no', 'psref'); ?>
                                <?php echo form_input('reference_no', ($_POST['reference_no'] ?? $_POST['reference_no']), 'class="form-control input-tip" id="psref"'); ?>
                            </div>
                        </div>

                        <!-- Supplier Selection -->
                        <div class="col-md-4">
                            <div class="form-group">
                                <?= lang('supplier', 'pssupplier'); ?>
                                <?php if ($Owner || $Admin || $GP['suppliers-add'] || $GP['suppliers-index']) {
                                    ?><div class="input-group"><?php
                                } ?>
                                    <input type="hidden" name="supplier" value="" id="pssupplier"
                                            class="form-control" style="width:100%;"
                                            placeholder="<?= lang('select') . ' ' . lang('supplier') ?>">
                                    <input type="hidden" name="supplier_id" value="" id="supplier_id"
                                            class="form-control">
                                    <?php if ($Owner || $Admin || $GP['suppliers-index']) {
                                    ?>
                                        <div class="input-group-addon no-print" style="padding: 2px 5px; border-left: 0;">
                                            <a href="#" id="view-supplier" class="external" data-toggle="modal" data-target="#myModal">
                                                <i class="fa fa-2x fa-user" id="addIcon"></i>
                                            </a>
                                        </div>
                                    <?php
                                    } ?>
                                    <?php if ($Owner || $Admin || $GP['suppliers-add']) {
                                    ?>
                                    <div class="input-group-addon no-print" style="padding: 2px 5px;">
                                        <a href="<?= admin_url('suppliers/add'); ?>" id="add-supplier" class="external" data-toggle="modal" data-target="#myModal">
                                            <i class="fa fa-2x fa-plus-circle" id="addIcon"></i>
                                        </a>
                                    </div>
                                    <?php
                                    } ?>
                                    <?php if ($Owner || $Admin || $GP['suppliers-add'] || $GP['suppliers-index']) {
                                    ?></div><?php
                                    } ?>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <?= lang('Payment Amount', 'pspayment'); ?>
                                <?php echo form_input('payment_total', ($_POST['payment_amount'] ?? $_POST['payment_total']), 'class="form-control input-tip" id="pspayment"'); ?>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="payment_mode">Payment Mode</label>
                                <select name="payment_mode" id="payment_mode" class="form-control" required="required">
                                    
                                    <option value="advance_only">Advance Only</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <label><?= lang('Available Advance'); ?></label>
                                <div id="supplier-advance-info" style="padding: 8px; background-color: #f8f9fa; border: 1px solid #dee2e6; border-radius: 4px;">
                                    <span id="advance-balance">Please select a supplier</span>
                                </div>
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
                            <label class="required-field"><?= lang('Transfer From', 'psledger'); ?></label>
                            <?php 

                                echo form_dropdown('ledger_account', $LO, ($_POST['ledger_account'] ?? $purchase->ledger_account), 'id="psledger" class="ledger-dropdown form-control" required="required"',$DIS);  

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
                                <?= lang('VAT on Bank Charges (15%)', 'psvat'); ?>
                                <?php echo form_input('vat', ($_POST['vat'] ?? ''), 'class="form-control input-tip" id="psvat" placeholder="Auto-calculated from bank charges" readonly'); ?>
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
                                <hr />
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

<script type="text/javascript" src="<?= $assets ?>js/select2.min.js"></script>
<script type="text/javascript">
    const hostname = "<?= base_url(); ?>";

    var $supplier = $("#pssupplier");

    $supplier.change(function (e) {
        localStorage.setItem("pssupplier", $(this).val());
        $("#supplier_id").val($(this).val());
    });

    if ((pssupplier = localStorage.getItem("pssupplier"))) {
        $supplier.val(pssupplier).select2({
            minimumInputLength: 1,
            data: [],
            initSelection: function (element, callback) {
                $.ajax({
                    type: "get",
                    async: false,
                    url: hostname + "/admin/suppliers/getSupplier/" + $(element).val(),
                    dataType: "json",
                    success: function (data) {
                        callback(data[0]);
                        // Trigger change after supplier is loaded to ensure advance balance is loaded
                        setTimeout(function() {
                            if ($('#pssupplier').val()) {
                                loadSupplierAdvanceBalance($('#pssupplier').val());
                            }
                        }, 100);
                    },
                });
            },
            ajax: {
                url: hostname + "/admin/suppliers/childsuggestions",
                dataType: "json",
                quietMillis: 15,
                data: function (term, page) {
                    return {
                        term: term,
                        limit: 10,
                    };
                },
                results: function (data, page) {
                    if (data.results != null) {
                        return { results: data.results };
                    } else {
                        return { results: [{ id: "", text: "No Match Found" }] };
                    }
                },
            },
        });
    } else {
        nsChildSupplier();
    }

    function nsChildSupplier() {
        $("#pssupplier").select2({
            minimumInputLength: 1,
            ajax: {
                url: hostname + "admin/suppliers/childsuggestions",
                dataType: "json",
                quietMillis: 15,
                data: function (term, page) {
                    return {
                        term: term,
                        limit: 10,
                    };
                },
                results: function (data, page) {
                    if (data.results != null) {
                        return { results: data.results };
                    } else {
                        return { results: [{ id: "", text: "No Match Found" }] };
                    }
                },
            },
        });
    }
</script>

