<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-plus"></i><?= lang('create purchase requisition'); ?></h2>

        <!-- CSV upload icon -->
        
    </div>
    <div class="box-content">

    <div class="row">
                    <div class="col-lg-12">
<div class="container mt-4">
    <div class="">
        
        <div class="card-body">
<?= form_open('admin/purchase_requisition/save' . (!empty($requisition) ? '/' . $requisition->id : ''), ['class' => 'needs-validation', 'novalidate' => true]); ?>

<div class="row mb-3">
    <div class="col-md-4">
        <?= form_label('Requisition No', 'reference_no'); ?>
        <?= form_input([
            'name' => 'reference_no',
            'id' => 'reference_no',
            'class' => 'form-control',
            'readonly' => true,
            'value' => $requisition->pr_number ?? $reference_no
        ]); ?>
    </div>

    <div class="col-md-4">
        <?= form_label('Requested By', 'requested_by'); ?>
        <input type="text" name="requested_by" class="form-control" 
               value="<?= $this->session->userdata('username'); ?>" readonly />
    </div>

    <div class="col-md-4">
        <?= form_label('Department', 'department'); ?>
        <?= form_input([
            'name' => 'department',
            'id' => 'department',
            'class' => 'form-control',
            'value' => $requisition->department ?? 'Procurement'
        ]); ?>
    </div>
</div>

<div class="row mb-3">
    <div class="col-md-4">
        <?= form_label('Expected Delivery Date', 'expected_date'); ?>
        <input type="date" name="expected_date" id="expected_date" class="form-control"
               value="<?= $requisition->expected_date ?? '' ?>">
    </div>

    <div class="col-md-4">
        <?= form_label('Priority', 'priority'); ?>
        <?= form_dropdown('priority', [
            '' => 'Select Priority',
            'Low' => 'Low',
            'Medium' => 'Medium',
            'High' => 'High',
            'Urgent' => 'Urgent'
        ], $requisition->priority ?? '', ['class' => 'form-control']); ?>
    </div>

    <div class="col-md-4">
        <?= form_label('Warehouse', 'warehouse_id'); ?>
        <?php
        $warehouse_options = [];
        foreach ($warehouses as $wh) {
            $warehouse_options[$wh->id] = $wh->name;
        }
        ?>
        <?= form_dropdown('warehouse_id', $warehouse_options, $requisition->warehouse_id ?? '', ['class' => 'form-control']); ?>
    </div>
</div>

<div class="mb-3">
    <?= form_label('Remarks', 'remarks'); ?>
    <textarea name="remarks" class="form-control"><?= $requisition->remarks ?? '' ?></textarea>
</div>

<hr>
 <div class="col-md-12" id="sticker">
                            <div class="well well-sm">
                                <div class="form-group" style="margin-bottom:0;">
                                    <div class="input-group wide-tip">
                                        <div class="input-group-addon" style="padding-left: 10px; padding-right: 10px;">
                                            <i class="fa fa-2x fa-barcode addIcon"></i></a></div>
                                        <?php echo form_input('productSearch', '', 'class="form-control input-lg" id="productSearch" placeholder="' . $this->lang->line('add_product_to_order') . '"'); ?>
                                     
                                    </div>
                                </div>
                                <div class="clearfix"></div>
                            </div>
                        </div>
<h5>Requisition Items</h5>

<table class="table table-bordered" id="itemsTable">
    <thead><tr><th>No</th><th>Item Name</th><th>Quantity</th><th></th></tr></thead>
    <tbody>
        <?php if (!empty($items)) : ?>
            <?php foreach ($items as $i => $item): ?>
                <tr>
                    <td><?= $i+1 ?></td>
                    <td><?= $item->product_name ?>
                        <input type="hidden" name="items[<?= $i ?>][product_id]" value="<?= $item->product_id ?>">
                        <input type="hidden" name="items[<?= $i ?>][product_name]" value="<?= $item->product_name ?>">
                    </td>
                    <td><input type="number" name="items[<?= $i ?>][quantity]" value="<?= $item->quantity ?>" class="form-control"></td>
                    <td><button type="button" class="btn btn-danger btn-sm removeRow">&times;</button></td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </tbody>
</table>

<div class="text-end">
    <button type="submit" class="btn btn-success"><?= !empty($requisition) ? 'Update' : 'Submit' ?> Requisition</button>
</div>

<?= form_close(); ?>

      </div>
    </div>
    </div>
    </div>
</div>

</div>

<script>

$(document).ready(function() {
    $('#productSearch').autocomplete({
        source: function(request, response) {
            $.ajax({
                url: "<?= admin_url('purchase_requisition/search_product'); ?>",  // Adjust your controller path
                type: "GET",
                dataType: "json",
                data: { q: request.term },
                success: function(data) {
                    response($.map(data, function(item) {
                        return {
                            label: item.name + ' (' + item.code + ')',
                            value: item.name,
                            id: item.id,
                            cost: item.cost
                        };
                    }));
                }
            });
        },
        minLength: 2,
        select: function(event, ui) {
            // Add selected product to your table dynamically
            addProductToTable(ui.item);
            $(this).val(''); // Clear search box
            return false;
        }
    });

    function addProductToTable(product) {
        const table = $('#itemsTable tbody');
        const index = table.find('tr').length;
        const row = `
            <tr>
                <td>${index + 1}</td>
                <td>${product.label}<input type="hidden" name="items[${index}][product_id]" value="${product.id}"></td>
               
                <td><input type="text" name="items[${index}][quantity]" class="form-control"></td>
                <td><button type="button" class="btn btn-danger btn-sm removeRow">&times;</button></td>
            </tr>`;
        table.append(row);
    }

    // Remove row
    $(document).on('click', '.removeRow', function() {
        $(this).closest('tr').remove();
    });
});

</script>
