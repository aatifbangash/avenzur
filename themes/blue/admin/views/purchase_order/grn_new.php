<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-plus"></i><?= lang('add grn'); ?></h2>
    </div>
    <div class="box-content">

        <div class="row">
            <div class="col-lg-12">
                <div class="container mt-4">
                    <div class="">

                        <div class="card-body">
                            <?= form_open('admin/purchase_order/add_grn_new/' . $po_id, ['class' => 'needs-validation', 'novalidate' => true]); ?>

                            <!-- Supplier Information Section -->
                            <h5 class="mb-3">Supplier Information</h5>
                            <div class="form-group">
                                <p><strong>Supplier Name:</strong> <?php echo $po_info->supplier;?></p>
                                <p><strong>Total PO Items:</strong> <?= $po_info->total_items; ?></p>
                                <p><strong>Total PO Quantity:</strong> <?= $po_info->total_quantity; ?></p>
                            </div>
                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <?= form_label('Supplier Reference Number', 'supplier_reference'); ?>
                                    <?= form_input([
                                        'name' => 'supplier_reference',
                                        'id' => 'supplier_reference',
                                        'class' => 'form-control',
                                        'value' => $po_info->reference_no,
                                        'placeholder' => 'Enter Supplier Reference Number',
                                        'required' => true
                                    ]); ?>
                                </div>
                                <div class="form-group col-md-6">
                                    <?= form_label('Date Received', 'date_received'); ?>
                                    <?= form_input([
                                        'name' => 'date_received',
                                        'id' => 'date_received',
                                        'type' => 'datetime-local',
                                        'class' => 'form-control',
                                        'value' => date('Y-m-d\TH:i'),
                                        'required' => true
                                    ]); ?>
                                </div>
                            </div>

                            <h5 class="mb-3">Purchase Order Items</h5>

                            <table class="table table-bordered" id="itemsTable">
                                <thead class="thead-light">
                                    <tr>
                                        <th>No</th>
                                        <th style="width:30%">Item Name</th>
                                        <th style="width:15%">Actual Quantity</th>
                                        <th style="width:15%">Received Quantity</th>
                                        <th style="width:15%">Batch Number</th>
                                        <th style="width:15%">Expiry Date</th>
                                        <th style="width:20%">Comment</th>
                                        <th style="width:10%">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $i = 1;
                                    foreach ($rows as $key => $row) { ?>
                                        <tr data-row-index="<?php echo $key; ?>" data-row-id="<?php echo $row->id; ?>" data-product-id="<?php echo $row->product_id; ?>" data-product-name="<?php echo htmlspecialchars($row->product_name); ?>">
                                            <td>
                                                <?php echo $i; ?>
                                                <input type="hidden" name="items[<?php echo $key; ?>][item_id]" value="<?php echo $row->id; ?>">
                                                <input type="hidden" name="items[<?php echo $key; ?>][product_id]" value="<?php echo $row->product_id; ?>">
                                            </td>
                                            <td><?php echo $row->product_name; ?></td>
                                            <td>
                                                <input type="text"
                                                    name="items[<?php echo $key; ?>][actual_quantity]"
                                                    value="<?php echo $row->quantity; ?>"
                                                    class="form-control actual-quantity" readonly data-original-quantity="<?php echo $row->quantity; ?>">
                                            </td>
                                            <td>
                                                <input type="number"
                                                    name="items[<?php echo $key; ?>][quantity]"
                                                    class="form-control received-quantity" placeholder="" value="<?php echo $row->quantity; ?>" required
                                                    oninput="validateQuantity(this)">
                                            </td>
                                             <td>
                                                <input type="text"
                                                    name="items[<?php echo $key; ?>][batch_number]" value="<?php echo $row->batchno; ?>" 
                                                    class="form-control batch-number" placeholder="Enter Batch Number" required>
                                            </td>
                                          
                                            <td>
                                                <input type="date"
                                                    name="items[<?php echo $key; ?>][expiry_date]" value="<?php echo $row->expiry; ?>"
                                                    class="form-control expiry-date" required>
                                            </td>
                                            <td>
                                                <input type="text"
                                                    name="items[<?php echo $key; ?>][remarks]" value="<?php echo $row->grn_comments; ?>"
                                                    class="form-control remarks" placeholder="">
                                            </td>
                                            <td class="text-center">
                                                <button type="button" class="btn btn-sm btn-primary split-batch-btn" title="Split into another batch">
                                                    <i class="fa fa-plus"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    <?php
                                        $i++;
                                    }
                                    ?>

                                </tbody>
                            </table>



                            <div class="text-end">
                                <button type="submit" class="btn btn-success">Submit GRN</button>
                            </div>

                            <?= form_close(); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

<script>
    let rowCounter = <?php echo count($rows); ?>;

    function validateQuantity(input) {
        const row = $(input).closest('tr');
        const actualQuantity = parseFloat(row.find('.actual-quantity').val()) || 0;
        const receivedQuantity = parseFloat(input.value) || 0;
        
        if (receivedQuantity > actualQuantity) {
            alert('Received quantity cannot be more than actual quantity.');
            input.value = actualQuantity;
        }
    }

    $(document).ready(function() {
        // Handle split batch button click
        $(document).on('click', '.split-batch-btn', function() {
            const currentRow = $(this).closest('tr');
            const rowId = currentRow.data('row-id');
            const productId = currentRow.data('product-id');
            const productName = currentRow.data('product-name');
            
            // Get current values
            const actualQuantityInput = currentRow.find('.actual-quantity');
            const receivedQuantityInput = currentRow.find('.received-quantity');
            
            const currentActualQuantity = parseFloat(actualQuantityInput.val()) || 0;
            const currentReceivedQuantity = parseFloat(receivedQuantityInput.val()) || 0;
            
            // Calculate remaining quantity for new row
            const remainingQuantity = currentActualQuantity - currentReceivedQuantity;
            
            if (remainingQuantity <= 0) {
                alert('No remaining quantity to split. The received quantity equals or exceeds the actual quantity.');
                return;
            }
            
            // Update current row's actual quantity to match received quantity
            actualQuantityInput.val(currentReceivedQuantity);
            
            // Create new row
            const newRowIndex = rowCounter++;
            const newRow = $('<tr>', {
                'data-row-index': newRowIndex,
                'data-row-id': rowId,
                'data-product-id': productId,
                'data-product-name': productName
            });
            
            // Build new row HTML
            newRow.html(`
                <td>
                    ${$('#itemsTable tbody tr').length + 1}
                    <input type="hidden" name="items[${newRowIndex}][item_id]" value="${rowId}">
                    <input type="hidden" name="items[${newRowIndex}][product_id]" value="${productId}">
                </td>
                <td>${productName}</td>
                <td>
                    <input type="text"
                        name="items[${newRowIndex}][actual_quantity]"
                        value="${remainingQuantity}"
                        class="form-control actual-quantity" readonly data-original-quantity="${remainingQuantity}">
                </td>
                <td>
                    <input type="number"
                        name="items[${newRowIndex}][quantity]"
                        class="form-control received-quantity" placeholder="" value="${remainingQuantity}" required
                        oninput="validateQuantity(this)">
                </td>
                <td>
                    <input type="text"
                        name="items[${newRowIndex}][batch_number]" value="" 
                        class="form-control batch-number" placeholder="Enter Batch Number" required>
                </td>
                <td>
                    <input type="date"
                        name="items[${newRowIndex}][expiry_date]" value=""
                        class="form-control expiry-date" required>
                </td>
                <td>
                    <input type="text"
                        name="items[${newRowIndex}][remarks]" value=""
                        class="form-control remarks" placeholder="">
                </td>
                <td class="text-center">
                    <button type="button" class="btn btn-sm btn-primary split-batch-btn" title="Split into another batch">
                        <i class="fa fa-plus"></i>
                    </button>
                    <button type="button" class="btn btn-sm btn-danger remove-batch-btn" title="Remove this batch">
                        <i class="fa fa-minus"></i>
                    </button>
                </td>
            `);
            
            // Insert new row after current row
            currentRow.after(newRow);
            
            // Renumber rows
            renumberRows();
        });
        
        // Handle remove batch button click
        $(document).on('click', '.remove-batch-btn', function() {
            if (confirm('Are you sure you want to remove this batch row?')) {
                $(this).closest('tr').remove();
                renumberRows();
            }
        });
        
        // Function to renumber rows
        function renumberRows() {
            $('#itemsTable tbody tr').each(function(index) {
                const hiddenInputs = $(this).find('td:first input[type="hidden"]');
                let hiddenHTML = '';
                hiddenInputs.each(function() {
                    hiddenHTML += $(this).prop('outerHTML');
                });
                $(this).find('td:first').html((index + 1) + hiddenHTML);
            });
        }
    });
</script>
