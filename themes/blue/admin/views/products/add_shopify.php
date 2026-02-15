<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<style>
    * { box-sizing: border-box; margin: 0; padding: 0; }
    
    body {
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
        background-color: #f1f1f1;
        color: #202223;
        line-height: 1.6;
    }
    
    .shopify-container {
        max-width: 1440px;
        margin: 0 auto;
        padding: 24px;
    }
    
    .page-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 24px;
        padding-bottom: 16px;
        border-bottom: 1px solid #e1e3e5;
    }
    
    .page-title {
        font-size: 28px;
        font-weight: 600;
        color: #202223;
        margin: 0;
    }
    
    .page-actions {
        display: flex;
        gap: 12px;
    }
    
    .btn {
        padding: 10px 16px;
        border: none;
        border-radius: 6px;
        font-size: 14px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }
    
    .btn-primary {
        background: #008060;
        color: white;
    }
    
    .btn-primary:hover {
        background: #006e52;
    }
    
    .btn-secondary {
        background: #ffffff;
        color: #202223;
        border: 1px solid #c9cccf;
    }
    
    .btn-secondary:hover {
        background: #f6f6f7;
    }
    
    .btn-ghost {
        background: transparent;
        color: #202223;
    }
    
    .btn-ghost:hover {
        background: #f6f6f7;
    }
    
    .product-form-layout {
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 20px;
    }
    
    .card {
        background: white;
        border-radius: 12px;
        box-shadow: 0 0 0 1px rgba(63,63,68,0.05), 0 1px 3px 0 rgba(63,63,68,0.15);
        margin-bottom: 20px;
        overflow: hidden;
    }
    
    .card-header {
        padding: 20px;
        border-bottom: 1px solid #e1e3e5;
    }
    
    .card-title {
        font-size: 16px;
        font-weight: 600;
        color: #202223;
        margin: 0;
    }
    
    .card-body {
        padding: 20px;
    }
    
    .form-group {
        margin-bottom: 20px;
    }
    
    .form-group:last-child {
        margin-bottom: 0;
    }
    
    .form-label {
        display: block;
        margin-bottom: 8px;
        font-size: 13px;
        font-weight: 500;
        color: #202223;
    }
    
    .required {
        color: #d82c0d;
        margin-left: 2px;
    }
    
    .form-control {
        width: 100%;
        padding: 10px 12px;
        border: 1px solid #c9cccf;
        border-radius: 6px;
        font-size: 14px;
        color: #202223;
        transition: border-color 0.2s, box-shadow 0.2s;
        font-family: inherit;
    }
    
    .form-control:focus {
        outline: none;
        border-color: #005bd3;
        box-shadow: 0 0 0 3px rgba(0,91,211,0.1);
    }
    
    textarea.form-control {
        min-height: 100px;
        resize: vertical;
    }
    
    .form-hint {
        margin-top: 6px;
        font-size: 12px;
        color: #6d7175;
    }
    
    .form-row {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 16px;
    }
    
    .variant-container {
        border: 1px solid #e1e3e5;
        border-radius: 8px;
        padding: 16px;
        margin-bottom: 16px;
        transition: all 0.2s;
    }
    
    .variant-container:hover {
        border-color: #c9cccf;
        box-shadow: 0 2px 6px rgba(0,0,0,0.08);
    }
    
    .variant-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 16px;
        padding-bottom: 12px;
        border-bottom: 1px solid #e1e3e5;
    }
    
    .variant-title {
        font-size: 14px;
        font-weight: 600;
        color: #202223;
        margin: 0;
    }
    
    .btn-remove {
        background: #fff;
        border: 1px solid #c9cccf;
        padding: 6px 12px;
        border-radius: 6px;
        font-size: 12px;
        cursor: pointer;
        color: #d82c0d;
    }
    
    .btn-remove:hover {
        background: #fef5f4;
        border-color: #d82c0d;
    }
    
    .btn-add-variant {
        width: 100%;
        padding: 12px;
        background: #f6f6f7;
        border: 1px dashed #c9cccf;
        border-radius: 6px;
        color: #202223;
        font-size: 14px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s;
    }
    
    .btn-add-variant:hover {
        background: #e3e5e7;
        border-color: #8c9196;
    }
    
    .status-badge {
        display: inline-flex;
        align-items: center;
        padding: 4px 10px;
        border-radius: 4px;
        font-size: 12px;
        font-weight: 500;
    }
    
    .status-draft {
        background: #fef3e7;
        color: #916a00;
    }
    
    .status-active {
        background: #e4f5f1;
        color: #005c4f;
    }
    
    .image-upload-zone {
        border: 2px dashed #c9cccf;
        border-radius: 8px;
        padding: 32px;
        text-align: center;
        cursor: pointer;
        transition: all 0.2s;
    }
    
    .image-upload-zone:hover {
        border-color: #8c9196;
        background: #f6f6f7;
    }
    
    .image-upload-icon {
        width: 48px;
        height: 48px;
        margin: 0 auto 12px;
        opacity: 0.5;
    }
    
    .checkbox-wrapper {
        display: flex;
        align-items: center;
        gap: 8px;
    }
    
    .checkbox-wrapper input[type="checkbox"] {
        width: 18px;
        height: 18px;
        cursor: pointer;
    }
    
    .variants-section {
        background: #f6f6f7;
        padding: 20px;
        border-radius: 8px;
        margin-top: 12px;
    }
    
    .option-row {
        display: grid;
        grid-template-columns: 1fr 2fr auto;
        gap: 12px;
        align-items: end;
        margin-bottom: 12px;
    }
    
    .sticky-sidebar {
        position: sticky;
        top: 24px;
        align-self: start;
    }
    
    .alert {
        padding: 12px 16px;
        border-radius: 8px;
        margin-bottom: 20px;
        font-size: 14px;
    }
    
    .alert-success {
        background: #e4f5f1;
        color: #005c4f;
        border: 1px solid #c1dfd8;
    }
    
    .alert-error {
        background: #fef5f4;
        color: #8a1f11;
        border: 1px solid #f0d0cc;
    }
    
    @media (max-width: 1024px) {
        .product-form-layout {
            grid-template-columns: 1fr;
        }
        
        .sticky-sidebar {
            position: static;
        }
    }
</style>

<div class="shopify-container">
    
    <?php if ($error): ?>
        <div class="alert alert-error">
            <?= $error; ?>
        </div>
    <?php endif; ?>
    
    <?php if ($this->session->flashdata('message')): ?>
        <div class="alert alert-success">
            <?= $this->session->flashdata('message'); ?>
        </div>
    <?php endif; ?>
    
    <div class="page-header">
        <h1 class="page-title">Add Product</h1>
        <div class="page-actions">
            <a href="<?= admin_url('products'); ?>" class="btn btn-ghost">Cancel</a>
        </div>
    </div>
    
    <?php echo admin_form_open_multipart('products/add_shopify', ['id' => 'product-form']); ?>
    
    <div class="product-form-layout">
        
        <!-- LEFT COLUMN -->
        <div class="main-column">
            
            <!-- Product Details -->
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Product Details</h2>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label class="form-label">Title <span class="required">*</span></label>
                        <input type="text" name="title" class="form-control" placeholder="e.g. Paracetamol 500mg Tablets" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Title (Arabic)</label>
                        <input type="text" name="title_ar" class="form-control" placeholder="العنوان بالعربية" dir="rtl">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" placeholder="Enter product description..."></textarea>
                        <p class="form-hint">Brief description for listings and search</p>
                    </div>
                </div>
            </div>
            
            <!-- Media -->
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Media</h2>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label class="form-label">Product Image</label>
                        <div class="image-upload-zone" onclick="document.getElementById('product_image').click()">
                            <svg class="image-upload-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                            </svg>
                            <p style="margin: 0; color: #6d7175;">Click to upload or drag and drop</p>
                            <p style="margin: 4px 0 0 0; font-size: 12px; color: #8c9196;">PNG, JPG, GIF up to 10MB</p>
                        </div>
                        <input type="file" id="product_image" name="product_image" accept="image/*" style="display: none;">
                        <p class="form-hint">Or enter image URL: <input type="url" name="product_image_url" class="form-control" placeholder="https://..." style="margin-top: 8px;"></p>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Gallery Images (Optional)</label>
                        <input type="file" name="gallery_images[]" class="form-control" accept="image/*" multiple>
                        <p class="form-hint">Select multiple images for product gallery</p>
                    </div>
                </div>
            </div>
            
            <!-- Pricing -->
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Pricing</h2>
                </div>
                <div class="card-body">
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Price (SAR) <span class="required">*</span></label>
                            <input type="number" step="0.01" min="0" name="price" class="form-control" placeholder="0.00" required>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Compare at Price</label>
                            <input type="number" step="0.01" min="0" name="compare_at_price" class="form-control" placeholder="0.00">
                            <p class="form-hint">Original price for discounts</p>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Cost per Item</label>
                        <input type="number" step="0.01" min="0" name="cost" class="form-control" placeholder="0.00">
                        <p class="form-hint">Purchase cost (for profit margin)</p>
                    </div>
                </div>
            </div>
            
            <!-- Inventory -->
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Inventory</h2>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label class="form-label">SKU (Product Code) <span class="required">*</span></label>
                        <input type="text" name="code" class="form-control" placeholder="SKU-001" required>
                        <p class="form-hint">Unique identifier for this product</p>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Barcode</label>
                        <input type="text" name="barcode" class="form-control" placeholder="e.g. 123456789012">
                    </div>
                </div>
            </div>
            
            <!-- Variants -->
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Variants</h2>
                </div>
                <div class="card-body">
                    <div class="checkbox-wrapper">
                        <input type="checkbox" id="has_variants" onchange="toggleVariants()">
                        <label for="has_variants" style="margin: 0; cursor: pointer;">This product has variants (size, color, etc.)</label>
                    </div>
                    
                    <div id="variants-section" class="variants-section" style="display: none;">
                        <div style="margin-bottom: 16px;">
                            <label class="form-label">Options (e.g., Size, Color, Material)</label>
                            <div id="options-container">
                                <div class="option-row">
                                    <input type="text" name="option_names[]" class="form-control" placeholder="Option name (e.g., Size)">
                                    <input type="text" name="option_values[]" class="form-control" placeholder="Values (e.g., Small, Medium, Large)">
                                    <button type="button" class="btn btn-ghost" onclick="removeOption(this)">Remove</button>
                                </div>
                            </div>
                            <button type="button" class="btn btn-ghost" onclick="addOption()" style="margin-top: 8px; width: 100%;">+ Add Option</button>
                        </div>
                        
                        <hr style="margin: 20px 0; border: none; border-top: 1px solid #e1e3e5;">
                        
                        <div>
                            <label class="form-label">Variant Details</label>
                            <div id="variants-container">
                                <!-- Variants will be added dynamically -->
                                <div class="variant-container">
                                    <div class="variant-header">
                                        <h4 class="variant-title">Variant 1</h4>
                                        <button type="button" class="btn-remove" onclick="removeVariant(this)">Remove</button>
                                    </div>
                                    
                                    <div class="form-row">
                                        <div class="form-group">
                                            <label class="form-label">Option 1</label>
                                            <input type="text" name="variant_option1[]" class="form-control" placeholder="e.g., Small">
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label">Option 2</label>
                                            <input type="text" name="variant_option2[]" class="form-control" placeholder="e.g., Red">
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label">Option 3</label>
                                            <input type="text" name="variant_option3[]" class="form-control" placeholder="e.g., Cotton">
                                        </div>
                                    </div>
                                    
                                    <div class="form-row">
                                        <div class="form-group">
                                            <label class="form-label">Price (SAR) <span class="required">*</span></label>
                                            <input type="number" step="0.01" name="variant_price[]" class="form-control" placeholder="0.00">
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label">Compare Price</label>
                                            <input type="number" step="0.01" name="variant_compare_price[]" class="form-control" placeholder="0.00">
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label">Cost</label>
                                            <input type="number" step="0.01" name="variant_cost[]" class="form-control" placeholder="0.00">
                                        </div>
                                    </div>
                                    
                                    <div class="form-row">
                                        <div class="form-group">
                                            <label class="form-label">SKU</label>
                                            <input type="text" name="variant_sku[]" class="form-control" placeholder="SKU-V1">
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label">Barcode</label>
                                            <input type="text" name="variant_barcode[]" class="form-control" placeholder="123456789">
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label">Weight (kg)</label>
                                            <input type="number" step="0.01" name="variant_weight[]" class="form-control" placeholder="0.00">
                                        </div>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label class="form-label">Quantity</label>
                                        <input type="number" step="1" name="variant_quantity[]" class="form-control" value="0">
                                    </div>
                                </div>
                            </div>
                            <button type="button" class="btn-add-variant" onclick="addVariant()">+ Add New Variant</button>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Shipping -->
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Shipping</h2>
                </div>
                <div class="card-body">
                    <div class="checkbox-wrapper" style="margin-bottom: 16px;">
                        <input type="checkbox" id="requires_shipping" name="requires_shipping" value="1" checked>
                        <label for="requires_shipping" style="margin: 0; cursor: pointer;">This product requires shipping</label>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Weight (kg)</label>
                        <input type="number" step="0.01" name="weight" class="form-control" placeholder="0.00">
                        <p class="form-hint">Used to calculate shipping rates</p>
                    </div>
                </div>
            </div>
            
            <!-- SEO -->
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Search Engine Listing</h2>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label class="form-label">SEO Title</label>
                        <input type="text" name="seo_title" class="form-control" placeholder="Product Title for Search Engines">
                        <p class="form-hint">Recommended: 50-60 characters</p>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">SEO Description</label>
                        <textarea name="seo_description" class="form-control" placeholder="Brief description for search results"></textarea>
                        <p class="form-hint">Recommended: 155-160 characters</p>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">URL Handle</label>
                        <input type="text" name="handle" class="form-control" placeholder="product-name">
                        <p class="form-hint">Auto-generated from title if left blank</p>
                    </div>
                </div>
            </div>
            
        </div>
        
        <!-- RIGHT COLUMN -->
        <aside class="sticky-sidebar">
            
            <!-- Status -->
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Status</h2>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label class="form-label">Product Status</label>
                        <select name="status" class="form-control">
                            <option value="draft">Draft</option>
                            <option value="active">Active</option>
                            <option value="archived">Archived</option>
                        </select>
                    </div>
                </div>
            </div>
            
            <!-- Organization -->
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Organization</h2>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label class="form-label">Product Type</label>
                        <input type="text" name="product_type" class="form-control" placeholder="e.g. Analgesics">
                        <p class="form-hint">Custom product category</p>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Vendor/Manufacturer</label>
                        <input type="text" name="vendor" class="form-control" placeholder="Brand or supplier name">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Category</label>
                        <select name="category_id" class="form-control">
                            <option value="">Select Category</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?= $category->id; ?>"><?= $category->name; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Brand</label>
                        <select name="brand" class="form-control">
                            <option value="">Select Brand</option>
                            <?php foreach ($brands as $brand): ?>
                                <option value="<?= $brand->id; ?>"><?= $brand->name; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Tags</label>
                        <input type="text" name="tags" class="form-control" placeholder="medicine, pain-relief, otc">
                        <p class="form-hint">Comma-separated tags for search</p>
                    </div>
                </div>
            </div>
            
            <!-- Tax -->
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Tax</h2>
                </div>
                <div class="card-body">
                    <div class="checkbox-wrapper" style="margin-bottom: 16px;">
                        <input type="checkbox" id="taxable" name="taxable" value="1" checked>
                        <label for="taxable" style="margin: 0; cursor: pointer;">Charge tax on this product</label>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Tax Rate</label>
                        <select name="tax_rate" class="form-control">
                            <?php foreach ($tax_rates as $rate): ?>
                                <option value="<?= $rate->id; ?>"><?= $rate->name; ?> (<?= $rate->rate; ?>%)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Tax Method</label>
                        <select name="tax_method" class="form-control">
                            <option value="0">Inclusive</option>
                            <option value="1">Exclusive</option>
                        </select>
                    </div>
                </div>
            </div>
            
            <!-- Actions -->
            <div class="card">
                <div class="card-body">
                    <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center;">
                        <svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                            <path d="M10.97 4.97a.75.75 0 0 1 1.07 1.05l-3.99 4.99a.75.75 0 0 1-1.08.02L4.324 8.384a.75.75 0 1 1 1.06-1.06l2.094 2.093 3.473-4.425a.267.267 0 0 1 .02-.022z"/>
                        </svg>
                        Save Product
                    </button>
                </div>
            </div>
            
        </aside>
        
    </div>
    
    <?= form_close(); ?>
    
</div>

<script>
    // Toggle variants section
    function toggleVariants() {
        const checkbox = document.getElementById('has_variants');
        const section = document.getElementById('variants-section');
        section.style.display = checkbox.checked ? 'block' : 'none';
    }
    
    // Add new option
    function addOption() {
        const container = document.getElementById('options-container');
        const html = `
            <div class="option-row">
                <input type="text" name="option_names[]" class="form-control" placeholder="Option name">
                <input type="text" name="option_values[]" class="form-control" placeholder="Values">
                <button type="button" class="btn btn-ghost" onclick="removeOption(this)">Remove</button>
            </div>
        `;
        container.insertAdjacentHTML('beforeend', html);
    }
    
    // Remove option
    function removeOption(button) {
        button.closest('.option-row').remove();
    }
    
    // Add new variant
    function addVariant() {
        const container = document.getElementById('variants-container');
        const count = container.querySelectorAll('.variant-container').length + 1;
        
        const html = `
            <div class="variant-container">
                <div class="variant-header">
                    <h4 class="variant-title">Variant ${count}</h4>
                    <button type="button" class="btn-remove" onclick="removeVariant(this)">Remove</button>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Option 1</label>
                        <input type="text" name="variant_option1[]" class="form-control" placeholder="e.g., Small">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Option 2</label>
                        <input type="text" name="variant_option2[]" class="form-control" placeholder="e.g., Red">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Option 3</label>
                        <input type="text" name="variant_option3[]" class="form-control" placeholder="e.g., Cotton">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Price (SAR) <span class="required">*</span></label>
                        <input type="number" step="0.01" name="variant_price[]" class="form-control" placeholder="0.00">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Compare Price</label>
                        <input type="number" step="0.01" name="variant_compare_price[]" class="form-control" placeholder="0.00">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Cost</label>
                        <input type="number" step="0.01" name="variant_cost[]" class="form-control" placeholder="0.00">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">SKU</label>
                        <input type="text" name="variant_sku[]" class="form-control" placeholder="SKU-V${count}">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Barcode</label>
                        <input type="text" name="variant_barcode[]" class="form-control" placeholder="123456789">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Weight (kg)</label>
                        <input type="number" step="0.01" name="variant_weight[]" class="form-control" placeholder="0.00">
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Quantity</label>
                    <input type="number" step="1" name="variant_quantity[]" class="form-control" value="0">
                </div>
            </div>
        `;
        
        container.insertAdjacentHTML('beforeend', html);
    }
    
    // Remove variant
    function removeVariant(button) {
        const container = document.getElementById('variants-container');
        const variants = container.querySelectorAll('.variant-container');
        
        if (variants.length > 1) {
            button.closest('.variant-container').remove();
            updateVariantNumbers();
        } else {
            alert('You must have at least one variant');
        }
    }
    
    // Update variant numbers
    function updateVariantNumbers() {
        const variants = document.querySelectorAll('.variant-container');
        variants.forEach((variant, index) => {
            const title = variant.querySelector('.variant-title');
            title.textContent = `Variant ${index + 1}`;
        });
    }
    
    // Form submission validation
    document.getElementById('product-form').addEventListener('submit', function(e) {
        const hasVariants = document.getElementById('has_variants').checked;
        
        if (hasVariants) {
            const variantPrices = document.querySelectorAll('input[name="variant_price[]"]');
            let allValid = true;
            
            variantPrices.forEach(input => {
                if (!input.value || parseFloat(input.value) <= 0) {
                    allValid = false;
                    input.style.borderColor = '#d82c0d';
                } else {
                    input.style.borderColor = '#c9cccf';
                }
            });
            
            if (!allValid) {
                e.preventDefault();
                alert('Please fill in all variant prices');
                return false;
            }
        }
    });
</script>
