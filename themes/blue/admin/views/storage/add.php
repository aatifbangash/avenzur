<div class="box">
    <div class="box-header">
        <h2 class="blue"><i
                class="fa-fw fa fa-star"></i><?= lang('Add Storage Location'); ?>
        </h2>
    </div>
        <div class="box-content">
            <?php if($error): ?>
                <div class="alert alert-danger"><?= $error ?></div>
            <?php endif; ?>

            <?php echo admin_form_open('storage/add', 'id="action-form"'); ?>
                
                <!-- Warehouse Selector (Required) -->
                <div class="form-group">
                    <label>
                        <i class="fa fa-warehouse"></i> Warehouse <span class="text-danger">*</span>
                    </label>
                    <select name="warehouse_id" id="warehouse_id" class="form-control select2" required style="width: 100%;">
                        <option value="">-- Select Warehouse --</option>
                        <?php if(isset($warehouses) && !empty($warehouses)): ?>
                            <?php foreach($warehouses as $warehouse): ?>
                                <option value="<?= $warehouse->id ?>" <?= (isset($selected_warehouse) && $selected_warehouse == $warehouse->id) ? 'selected' : '' ?>>
                                    <?= $warehouse->name ?> <?= isset($warehouse->code) ? '(' . $warehouse->code . ')' : '' ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                    <small class="text-muted">Select the warehouse for this storage location</small>
                </div>

                <div class="form-group">
                    <label>Type <span class="text-danger">*</span></label>
                    <select name="type" class="form-control" required>
                        <option value="">Select Type</option>
                        <option value="rack">Rack</option>
                        <option value="level">Level</option>
                        <option value="shelf">Shelf</option>
                        <option value="box">Box</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Name <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control" required>
                </div>

                <div class="form-group">
                    <label>Parent Location (optional)</label>
                    <select name="parent_id" id="parent_id" class="form-control">
                        <option value="">-- None (Top Level) --</option>
                        <?php if(isset($hierarchy) && !empty($hierarchy)): ?>
                            <?php
                            function renderParentOptions($locations, $prefix=''){
                                foreach($locations as $loc){
                                    echo '<option value="'.$loc['id'].'">'.$prefix.$loc['type'].' - '.$loc['name'].'</option>';
                                    if(!empty($loc['children'])){
                                        renderParentOptions($loc['children'], $prefix.'&nbsp;&nbsp;&nbsp;');
                                    }
                                }
                            }
                            renderParentOptions($hierarchy);
                            ?>
                        <?php endif; ?>
                    </select>
                    <small class="text-muted">Leave empty for top-level rack, or select parent for nested locations</small>
                </div>

                <div class="form-group">
                    <label>Capacity (for boxes)</label>
                    <input type="number" name="capacity" class="form-control" min="1" placeholder="e.g., 100">
                    <small class="text-muted">Maximum number of items this location can hold (mainly for boxes)</small>
                </div>

                <div class="form-group">
                    <label>Assign Product (optional)</label>
                    <select name="product_id" class="form-control select2" style="width: 100%;">
                        <option value="">-- None --</option>
                        <?php if(isset($products) && !empty($products)): ?>
                            <?php foreach($products as $product): ?>
                                <option value="<?= $product->id ?>"><?= $product->name ?> (<?= $product->code ?>)</option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                    <small class="text-muted">Optionally assign a product to this location immediately</small>
                </div>

                <div class="form-group">
                    <label>Quantity</label>
                    <input type="number" name="quantity" class="form-control" min="1" placeholder="e.g., 50">
                    <small class="text-muted">Quantity of product to assign (if product selected above)</small>
                </div>

                <div class="form-group">
                    <button type="submit" class="btn btn-success">
                        <i class="fa fa-save"></i> Save Storage Location
                    </button>
                    <a href="<?= admin_url('storage') ?>" class="btn btn-default">
                        <i class="fa fa-times"></i> Cancel
                    </a>
                </div>
            <?= form_close() ?>
        </div>
    
</div>

<script>
$(document).ready(function() {
    // Warehouse change handler - reload parent options
    $('#warehouse_id').on('change', function() {
        let warehouseId = $(this).val();
        if (warehouseId) {
            // Reload page with selected warehouse to get correct parent options
            window.location.href = "<?= admin_url('storage/add') ?>?warehouse_id=" + warehouseId;
        } else {
            $('#parent_id').html('<option value="">-- Select Warehouse First --</option>');
        }
    });
});
</script>