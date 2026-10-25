<style>
    .inventory-info {
        background: #f8f9fa;
        padding: 12px;
        border-radius: 4px;
        margin-bottom: 15px;
    }
    .inventory-info p {
        margin: 5px 0;
        font-size: 13px;
    }
    .warehouse-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 15px;
        margin-top: 15px;
    }
    .warehouse-card {
        background: #fff;
        border: 1px solid #dee2e6;
        border-radius: 5px;
        padding: 15px;
        transition: all 0.2s;
    }
    .warehouse-card:hover {
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        transform: translateY(-2px);
    }
    .warehouse-name {
        font-weight: bold;
        color: #333;
        margin-bottom: 8px;
        font-size: 15px;
        border-bottom: 2px solid #007bff;
        padding-bottom: 5px;
    }
    .warehouse-location {
        color: #666;
        font-size: 12px;
        margin-bottom: 12px;
    }
    .stock-info {
        display: flex;
        justify-content: space-between;
        padding: 8px 10px;
        background: #f8f9fa;
        border-radius: 4px;
        margin-bottom: 6px;
    }
    .stock-label {
        color: #666;
        font-size: 13px;
    }
    .stock-value {
        font-weight: bold;
        font-size: 14px;
    }
    .stock-value.available {
        color: #28a745;
    }
    .stock-value.low {
        color: #ffc107;
    }
    .stock-value.out {
        color: #dc3545;
    }
    .total-summary {
        background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
        color: white;
        padding: 15px;
        border-radius: 5px;
        margin-bottom: 15px;
        text-align: center;
    }
    .total-summary h5 {
        margin: 0 0 5px 0;
        font-weight: normal;
        font-size: 14px;
    }
    .total-summary .total-value {
        font-size: 28px;
        font-weight: bold;
        margin: 0;
    }
    .contact-info {
        margin-top: 8px;
        padding-top: 8px;
        border-top: 1px solid #e9ecef;
        font-size: 12px;
        color: #666;
    }
</style>

<div class="inventory-info">
    <p>
        <strong>Product Code:</strong> <?= $product->code; ?> 
        <?php if($product->avz_item_code): ?>
        | <strong>AVZ Code:</strong> <?= $product->avz_item_code; ?>
        <?php endif; ?>
    </p>
</div>

<?php 
$total_quantity = 0;
$total_balance = 0;
foreach($warehouses as $wh) {
    $total_quantity += $wh->quantity;
    $total_balance += $wh->quantity_balance;
}
?>

<div class="total-summary">
    <h5>Total System Stock</h5>
    <p class="total-value"><?= number_format($total_quantity, 2); ?></p>
    <small>Units Available</small>
    <?php if($total_balance > 0): ?>
    <br><small style="opacity: 0.9;">Balance: <?= number_format($total_balance, 2); ?></small>
    <?php endif; ?>
</div>

<div class="warehouse-grid">
    <?php foreach($warehouses as $warehouse): ?>
    <div class="warehouse-card">
        <div class="warehouse-name">
            <i class="fa fa-building"></i> <?= $warehouse->name; ?>
        </div>
        <?php if($warehouse->map_address): ?>
        <div class="warehouse-location">
            <i class="fa fa-map-marker"></i> <?= $warehouse->map_address; ?>
        </div>
        <?php endif; ?>
        
        <div class="stock-info">
            <span class="stock-label">Total Quantity:</span>
            <span class="stock-value <?= $warehouse->quantity > 0 ? 'available' : 'out'; ?>">
                <?= number_format($warehouse->quantity, 2); ?>
            </span>
        </div>
        
        <div class="stock-info">
            <span class="stock-label">Available Balance:</span>
            <span class="stock-value <?= $warehouse->quantity_balance > 10 ? 'available' : ($warehouse->quantity_balance > 0 ? 'low' : 'out'); ?>">
                <?= number_format($warehouse->quantity_balance, 2); ?>
            </span>
        </div>

        <?php if($warehouse->phone): ?>
        <div class="contact-info">
            <i class="fa fa-phone"></i> <?= $warehouse->phone; ?>
        </div>
        <?php endif; ?>
    </div>
    <?php endforeach; ?>
</div>

<?php if(empty($warehouses)): ?>
<div class="alert alert-info" style="margin-top: 15px; text-align: center;">
    <i class="fa fa-info-circle"></i> No warehouse data available for this product.
</div>
<?php endif; ?>
