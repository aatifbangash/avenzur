<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<style>
.jl-entry-form {
    max-width: 1200px;
    margin: 0 auto;
}

.jl-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
}

.jl-table th, .jl-table td {
    border: 1px solid #ddd;
    padding: 8px;
    text-align: left;
}

.jl-table th {
    background-color: #f2f2f2;
    font-weight: bold;
}

.amount-input {
    width: 100px;
    text-align: right;
}

.description-input {
    width: 100%;
}

.type-select {
    width: 100%;
    max-width: 100px;
}

.ledger-select {
    width: 100%;
    min-width: 250px;
}

.department-select, .employee-select {
    width: 100%;
    min-width: 150px;
}

.add-row-btn {
    margin-top: 10px;
    padding: 5px 10px;
    background-color: #007bff;
    color: white;
    border: none;
    border-radius: 3px;
    cursor: pointer;
}

.add-row-btn:hover {
    background-color: #0056b3;
}

.remove-row-btn {
    padding: 2px 5px;
    background-color: #dc3545;
    color: white;
    border: none;
    border-radius: 3px;
    cursor: pointer;
    font-size: 12px;
}

.totals-section {
    margin-top: 20px;
    padding: 15px;
    background-color: #f8f9fa;
    border-radius: 5px;
}

.total-dr, .total-cr {
    font-size: 18px;
    font-weight: bold;
    padding: 5px 10px;
    border-radius: 3px;
    display: inline-block;
    margin: 0 10px;
}

.total-dr {
    background-color: #d4edda;
    color: #155724;
}

.total-cr {
    background-color: #d1ecf1;
    color: #0c5460;
}

.balanced {
    background-color: #d4edda;
    color: #155724;
}

.unbalanced {
    background-color: #f8d7da;
    color: #721c24;
}

.submit-btn {
    margin-top: 20px;
    padding: 10px 20px;
    background-color: #28a745;
    color: white;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-size: 16px;
}

.submit-btn:disabled {
    background-color: #6c757d;
    cursor: not-allowed;
}

.attachments-section {
    margin-bottom: 20px;
}

.file-input {
    margin-bottom: 10px;
}

.remove-file-btn {
    margin-left: 10px;
    padding: 2px 5px;
    background-color: #dc3545;
    color: white;
    border: none;
    border-radius: 3px;
    cursor: pointer;
    font-size: 12px;
}
</style>

<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa <?= isset($entry) && $entry ? 'fa-edit' : 'fa-plus' ?>"></i><?= isset($entry) && $entry ? 'Edit' : 'Add' ?> Journal Ledger (JL) Entry</h2>
    </div>
    <div class="box-content">
        <div class="jl-entry-form">
            <?php 
            $form_action = isset($entry) && $entry ? 'accounts/jl_entry_edit/' . $entry->id : 'accounts/jl_entry';
            echo admin_form_open($form_action, 'id="jlEntryForm" enctype="multipart/form-data"');
            ?>
                <!-- Date Input -->
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="entry_date">Entry Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="entry_date" name="entry_date" value="<?= isset($entry) && $entry ? date('Y-m-d', strtotime($entry->date)) : date('Y-m-d') ?>" required>
                        </div>
                    </div>
                </div>

                <!-- Attachments -->
                <div class="attachments-section">
                    <label>Attachments</label>
                    
                    <?php if (isset($entry) && $entry && !empty($entry->attachments)): ?>
                    <div class="existing-attachments" style="margin-bottom: 10px;">
                        <strong>Existing Attachments:</strong>
                        <ul class="list-unstyled">
                            <?php foreach($entry->attachments as $attachment): ?>
                            <li style="padding: 5px 0;">
                                <i class="fa fa-file"></i> 
                                <a href="<?= base_url($attachment->file_path) ?>" target="_blank"><?= $attachment->file_name ?></a>
                                (<?= number_format($attachment->file_size / 1024, 2) ?> KB)
                                <button type="button" class="btn btn-xs btn-danger remove-existing-attachment" data-id="<?= $attachment->id ?>" style="margin-left: 10px;">
                                    <i class="fa fa-trash"></i> Remove
                                </button>
                                <input type="hidden" name="remove_attachments[]" id="remove_attachment_<?= $attachment->id ?>" value="">
                            </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <?php endif; ?>
                    
                    <div id="attachments-container">
                        <div class="file-input-wrapper" style="margin-bottom: 10px;">
                            <input type="file" class="form-control file-input-field" name="attachments[]" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.xls,.xlsx">
                        </div>
                    </div>
                    <button type="button" class="btn btn-sm btn-info" id="addFileBtn">
                        <i class="fa fa-plus"></i> Add Another File
                    </button>
                </div>

                <!-- Journal Entries Table -->
                <table class="jl-table" id="jlTable">
                    <thead>
                        <tr>
                            <th width="5%">#</th>
                            <th width="10%">Type</th>
                            <th width="20%">Ledger Account</th>
                            <th width="10%">Dr Amount</th>
                            <th width="10%">Cr Amount</th>
                            <th width="20%">Description</th>
                            <th width="10%">Department</th>
                            <th width="10%">Employee</th>
                            <th width="5%">Action</th>
                        </tr>
                    </thead>
                    <tbody id="jlTableBody">
                        <!-- Default rows will be added by JavaScript -->
                    </tbody>
                </table>

                <button type="button" class="add-row-btn" id="addRowBtn">
                    <i class="fa fa-plus"></i> Add Row
                </button>

                <!-- Totals Section -->
                <div class="totals-section">
                    <div class="row">
                        <div class="col-md-4">
                            <strong>Total Debit: <span id="totalDr" class="total-dr">0.00</span></strong>
                        </div>
                        <div class="col-md-4 text-center">
                            <strong>Status: <span id="balanceStatus" class="balanced">Balanced</span></strong>
                        </div>
                        <div class="col-md-4 text-right">
                            <strong>Total Credit: <span id="totalCr" class="total-cr">0.00</span></strong>
                        </div>
                    </div>
                </div>

                <!-- Note -->
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="entry_note">Note</label>
                            <textarea class="form-control" id="entry_note" name="entry_note" rows="2" placeholder="Enter note for this journal entry"><?= isset($entry) && $entry ? $entry->notes : '' ?></textarea>
                        </div>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="text-center">
                    <button type="submit" class="submit-btn" id="submitBtn" disabled>
                        <i class="fa fa-save"></i> <?= isset($entry) && $entry ? 'Update' : 'Submit' ?> JL Entry
                    </button>
                </div>
            <?php echo form_close(); ?>
        </div>
    </div>
</div>

<script>
// Global variables
let rowCount = 0;

// Global functions that can be called from HTML attributes
function updateAmounts(select) {
    const row = $(select).closest('tr');
    const type = $(select).val();
    const drInput = row.find('.dr-amount');
    const crInput = row.find('.cr-amount');

    if (type === 'Dr') {
        drInput.prop('disabled', false);
        crInput.prop('disabled', true).val('');
    } else {
        crInput.prop('disabled', false);
        drInput.prop('disabled', true).val('');
    }

    calculateTotals();
    validateForm();
}

function calculateTotals() {
    let totalDr = 0;
    let totalCr = 0;

    $('.dr-amount:not(:disabled)').each(function() {
        const val = parseFloat($(this).val()) || 0;
        console.log('DR amount:', val, 'from element:', this);
        totalDr += val;
    });

    $('.cr-amount:not(:disabled)').each(function() {
        const val = parseFloat($(this).val()) || 0;
        console.log('CR amount:', val, 'from element:', this);
        totalCr += val;
    });

    console.log('Total DR:', totalDr, 'Total CR:', totalCr);

    $('#totalDr').text(totalDr.toFixed(2));
    $('#totalCr').text(totalCr.toFixed(2));

    const isBalanced = Math.abs(totalDr - totalCr) < 0.01;
    const statusElement = $('#balanceStatus');

    console.log('Is balanced:', isBalanced);

    if (isBalanced) {
        statusElement.removeClass('unbalanced').addClass('balanced').text('Balanced');
        console.log('Set status to BALANCED');
    } else {
        statusElement.removeClass('balanced').addClass('unbalanced').text('Unbalanced');
        console.log('Set status to UNBALANCED');
    }

    validateForm();
}

function removeRow(btn) {
    $(btn).closest('tr').remove();
    rowCount--;
    recalculateRowNumbers();
    calculateTotals();
    validateForm();
}

function validateForm() {
    const dateSelected = $('#entry_date').val().trim() !== '';
    const isBalanced = $('#balanceStatus').hasClass('balanced');
    let allRowsValid = true;
    let hasAnyAmount = false;

    // Debug logging
    console.log('Validation check:');
    console.log('Date selected:', dateSelected, 'Value:', $('#entry_date').val());
    console.log('Is balanced:', isBalanced, 'Classes:', $('#balanceStatus').attr('class'));

    // Check each row: if amount is entered, ledger must be selected
    $('#jlTableBody tr').each(function(index) {
        const row = $(this);
        const drInput = row.find('.dr-amount');
        const crInput = row.find('.cr-amount');
        const drAmount = parseFloat(drInput.val()) || 0;
        const crAmount = parseFloat(crInput.val()) || 0;
        const drEnabled = !drInput.prop('disabled');
        const crEnabled = !crInput.prop('disabled');
        // Find only the actual select element, not the Select2 container
        const ledgerSelect = row.find('select.ledger-select');
        const ledgerValue = ledgerSelect.val();
        const ledgerSelected = ledgerValue !== '' && ledgerValue !== null;

        console.log(`Row ${index + 1} check - DR: ${drAmount} (enabled: ${drEnabled}) CR: ${crAmount} (enabled: ${crEnabled}) Ledger selected: ${ledgerSelected} Ledger value: "${ledgerValue}"`);
        console.log('Ledger select element:', ledgerSelect);
        console.log('Ledger select options count:', ledgerSelect.find('option').length);
        console.log('First few options:', ledgerSelect.find('option').slice(0, 3).map((i, opt) => $(opt).val() + ': ' + $(opt).text()).get());

        // Check if this row has any amount in ENABLED fields only
        if ((drEnabled && drAmount > 0) || (crEnabled && crAmount > 0)) {
            hasAnyAmount = true;
            // If any amount is entered, ledger must be selected
            if (!ledgerSelected) {
                allRowsValid = false;
                console.log(`Row ${index + 1} FAILED validation: has amount but no ledger`);
                return false; // Break out of each loop
            }
        }
    });

    console.log('Has any amount:', hasAnyAmount);
    console.log('All rows valid:', allRowsValid);

    const submitBtn = $('#submitBtn');
    const shouldEnable = dateSelected && isBalanced && allRowsValid && hasAnyAmount;

    console.log('Should enable button:', shouldEnable);

    if (shouldEnable) {
        submitBtn.prop('disabled', false);
        console.log('Button ENABLED');
        return true;
    } else {
        submitBtn.prop('disabled', true);
        console.log('Button DISABLED');
        return false;
    }
}

function recalculateRowNumbers() {
    $('#jlTableBody tr').each(function(index) {
        $(this).find('td:first').text(index + 1);
        $(this).attr('data-row', index + 1);
    });
}

$(document).ready(function() {
    <?php if (isset($entry) && $entry && !empty($entry->items)): ?>
    // Load existing entry items for editing
    <?php foreach($entry->items as $item): ?>
    addRow('<?= $item->dc == 'D' ? 'Dr' : 'Cr' ?>', {
        ledger_id: '<?= $item->ledger_id ?>',
        amount: '<?= $item->amount ?>',
        description: '<?= addslashes($item->narration) ?>',
        department_id: '<?= $item->department_id ?? '' ?>',
        employee_id: '<?= $item->employee_id ?? '' ?>'
    });
    <?php endforeach; ?>
    <?php else: ?>
    // Initialize with 2 default rows for new entry
    addRow('Dr');
    addRow('Cr');
    <?php endif; ?>

    // Add row button
    $('#addRowBtn').click(function() {
        addRow('Dr'); // Default to Dr for new rows
    });

    // Form submission - now uses normal form submission, not AJAX
    $('#jlEntryForm').submit(function(e) {
        if (!validateForm()) {
            e.preventDefault();
            alert('Please ensure all fields are valid and the entry is balanced.');
            return false;
        }
        // Let the form submit normally
        return true;
    });

    function addRow(defaultType, data) {
        rowCount++;
        data = data || {};
        
        const rowHtml = `
            <tr data-row="${rowCount}">
                <td>${rowCount}</td>
                <td>
                    <select class="form-control type-select" name="entries[${rowCount}][type]" onchange="updateAmounts(this)">
                        <option value="Dr" ${defaultType === 'Dr' ? 'selected' : ''}>Dr</option>
                        <option value="Cr" ${defaultType === 'Cr' ? 'selected' : ''}>Cr</option>
                    </select>
                </td>
                <td>
                    <select class="form-control ledger-select" name="entries[${rowCount}][ledger_id]" required>
                        <option value="">Select Ledger</option>
                        <?php foreach($ledgers as $ledger): ?>
                        <option value="<?= is_array($ledger) ? $ledger['id'] : $ledger->id ?>" ${data.ledger_id == '<?= is_array($ledger) ? $ledger['id'] : $ledger->id ?>' ? 'selected' : ''}><?= is_array($ledger) ? $ledger['name'] : $ledger->name ?> (<?= is_array($ledger) ? $ledger['code'] : $ledger->code ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </td>
                <td>
                    <input type="number" class="form-control amount-input dr-amount" name="entries[${rowCount}][dr_amount]"
                           step="0.00001" min="0" placeholder="0.00" value="${defaultType === 'Dr' && data.amount ? data.amount : ''}">
                </td>
                <td>
                    <input type="number" class="form-control amount-input cr-amount" name="entries[${rowCount}][cr_amount]"
                           step="0.00001" min="0" placeholder="0.00" value="${defaultType === 'Cr' && data.amount ? data.amount : ''}">
                </td>
                <td>
                    <input type="text" class="form-control description-input" name="entries[${rowCount}][description]"
                           placeholder="Enter description" value="${data.description || ''}">
                </td>
                <td>
                    <select class="form-control department-select" name="entries[${rowCount}][department_id]">
                        <option value="">Select Department</option>
                        <?php foreach($departments as $dept): ?>
                        <option value="<?= $dept->id ?>" ${data.department_id == '<?= $dept->id ?>' ? 'selected' : ''}><?= $dept->name ?></option>
                        <?php endforeach; ?>
                    </select>
                </td>
                <td>
                    <select class="form-control employee-select" name="entries[${rowCount}][employee_id]">
                        <option value="">Select Employee</option>
                        <?php foreach($employees as $emp): ?>
                        <option value="<?= $emp->id ?>" ${data.employee_id == '<?= $emp->id ?>' ? 'selected' : ''}><?= $emp->name ?></option>
                        <?php endforeach; ?>
                    </select>
                </td>
                <td>
                    ${rowCount > 2 ? '<button type="button" class="remove-row-btn" onclick="removeRow(this)"><i class="fa fa-trash"></i></button>' : ''}
                </td>
            </tr>
        `;

        $('#jlTableBody').append(rowHtml);
        updateAmounts($(`tr[data-row="${rowCount}"] .type-select`)[0]);
    }

    console.log('Available ledgers:', <?php echo json_encode($ledgers); ?>);
    console.log('Ledgers count:', <?php echo count($ledgers); ?>);
    $('#entry_date').on('change', function() {
        validateForm();
    });

    $(document).on('change keyup', '.amount-input', function() {
        calculateTotals();
        validateForm();
    });

    $(document).on('change', '.ledger-select', function() {
        console.log('Ledger changed to:', $(this).val(), 'in row:', $(this).closest('tr').attr('data-row'));
        validateForm();
    });

    // Add another file input
    $('#addFileBtn').click(function() {
        const newFileInput = `
            <div class="file-input-wrapper" style="margin-bottom: 10px;">
                <input type="file" class="form-control file-input-field" name="attachments[]" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.xls,.xlsx">
                <button type="button" class="btn btn-xs btn-danger remove-file-input" style="margin-left: 5px;">
                    <i class="fa fa-trash"></i>
                </button>
            </div>
        `;
        $('#attachments-container').append(newFileInput);
    });

    // Remove newly added file input
    $(document).on('click', '.remove-file-input', function() {
        $(this).closest('.file-input-wrapper').remove();
    });

    // Mark existing attachment for removal
    $(document).on('click', '.remove-existing-attachment', function() {
        const attachmentId = $(this).data('id');
        $('#remove_attachment_' + attachmentId).val(attachmentId);
        $(this).closest('li').fadeOut(300, function() {
            $(this).remove();
        });
    });

    // Initial setup
    calculateTotals();
    validateForm();
});
</script>