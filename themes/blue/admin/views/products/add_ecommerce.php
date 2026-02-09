<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<style>
    *,
*::before,
*::after {
  box-sizing: border-box;
}

body {
  margin: 0;
  padding: 20px;
  font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
  background-color: #f8f9fa;
  color: #202223;
}

.page-title {
  margin: 0 0 24px;
  font-size: 28px;
  font-weight: 700;
  color: #1a365d;
}

.product-form-page {
  max-width: 100%;
  width: 100%;
  margin: 0 auto;
  padding: 0;
}

.product-form__layout {
  display: grid;
  grid-template-columns: 2fr 1fr;
  gap: 24px;
  margin: 0;
}

/* Cards */

.card {
  background-color: #ffffff;
  border-radius: 6px;
  border: 1px solid #e2e8f0;
  margin-bottom: 20px;
  overflow: hidden;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
  transition: box-shadow 0.2s ease;
  width: 100% !important;
}

.card:hover {
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.12);
}

.card__header {
  padding: 16px 20px;
  border-bottom: 2px solid #f1f5f9;
  background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
}

.card__title {
  margin: 0;
  font-size: 17px;
  font-weight: 600;
  color: #1e293b;
  letter-spacing: -0.3px;
}

.card__title::before {
  content: '';
  display: inline-block;
  width: 4px;
  height: 20px;
  background: linear-gradient(135deg, #3b82f6 0%, #1e40af 100%);
  border-radius: 2px;
  margin-right: 10px;
  vertical-align: middle;
}

.card__body {
  padding: 20px;
}

.card__body + .card__body {
  border-top: 1px solid #f1f5f9;
  padding-top: 20px;
  margin-top: 0;
}

.card__body--columns {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
  gap: 20px;
}

.card--sticky {
  position: sticky;
  top: 24px;
}

.card__body--actions {
  display: flex;
  gap: 12px;
  justify-content: flex-end;
  padding-top: 16px;
  border-top: 1px solid #f1f5f9;
}

/* Form */

.form-group {
  margin-bottom: 20px;
}

.form-group:last-child {
  margin-bottom: 0;
}

.form-group--inline {
  display: flex;
  flex-direction: column;
}

label {
  display: inline-block;
  margin-bottom: 8px;
  font-size: 14px;
  font-weight: 600;
  color: #334155;
  letter-spacing: -0.2px;
}

.required {
  color: #e74c3c;
  font-weight: 700;
}

.form-control {
  width: 100%;
  border-radius: 5px;
  border: 1px solid #cbd5e1;
  padding: 10px 12px;
  font-size: 14px;
  line-height: 1.5;
  background-color: #ffffff;
  transition: all 0.2s ease;
  font-family: inherit;
}

.form-control:hover {
  border-color: #94a3b8;
  background-color: #f8fafc;
}

.form-control--textarea {
  resize: vertical;
  min-height: 140px;
  font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
}

.form-control:focus {
  outline: none;
  border-color: #3b82f6;
  background-color: #ffffff;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1), 0 0 0 1px #3b82f6;
}

.form-control::placeholder {
  color: #94a3b8;
}

.form-hint {
  margin: 6px 0 0;
  font-size: 12px;
  color: #64748b;
  line-height: 1.4;
}

.checkbox-label,
.radio-label {
  font-size: 14px;
  display: flex;
  align-items: center;
  gap: 10px;
  margin-bottom: 10px;
  font-weight: 500;
  cursor: pointer;
  color: #475569;
}

.checkbox-label input,
.radio-label input {
  margin: 0;
  cursor: pointer;
  width: 18px;
  height: 18px;
}

.checkbox-label:last-child,
.radio-label:last-child {
  margin-bottom: 0;
}

/* Buttons */

.btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  padding: 10px 20px;
  border-radius: 5px;
  border: 1px solid transparent;
  font-size: 14px;
  font-weight: 600;
  cursor: pointer;
  text-decoration: none;
  transition: all 0.2s ease;
  letter-spacing: -0.3px;
}

.btn--primary {
  background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
  border-color: #2563eb;
  color: #ffffff;
  box-shadow: 0 2px 4px rgba(37, 99, 235, 0.2);
}

.btn--primary:hover {
  background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
  border-color: #1d4ed8;
  box-shadow: 0 4px 8px rgba(37, 99, 235, 0.3);
  transform: translateY(-1px);
}

.btn--primary:active {
  transform: translateY(0);
  box-shadow: 0 2px 4px rgba(37, 99, 235, 0.2);
}

.btn--ghost {
  background-color: #f1f5f9;
  border-color: #e2e8f0;
  color: #475569;
}

.btn--ghost:hover {
  background-color: #e2e8f0;
  border-color: #cbd5e1;
  color: #334155;
}

/* Layout adjustments */

.product-form__main {
  min-width: 0;
}

.product-form__side {
  min-width: 0;
}

@media (max-width: 1024px) {
  .product-form__layout {
    grid-template-columns: 1fr;
    gap: 20px;
  }

  .card--sticky {
    position: static;
    margin-bottom: 20px;
  }

  .product-form-page {
    padding: 0;
  }
}

@media (max-width: 768px) {
  .product-form-page {
    padding: 0;
  }

  .page-title {
    font-size: 24px;
    margin-bottom: 20px;
  }

  .card {
    margin-bottom: 16px;
    width: 100% !important;
  }

  .card__title::before {
    width: 3px;
    height: 18px;
    margin-right: 8px;
  }

  .card__body {
    padding: 16px;
  }

  .card__body--columns {
    grid-template-columns: 1fr;
    gap: 16px;
  }

  .btn {
    padding: 9px 16px;
    font-size: 13px;
  }
}
    </style>

<script type="text/javascript">
    $(document).ready(function () {
        $('form[data-toggle="validator"]').bootstrapValidator({ excluded: [':disabled'] });

        // Validate image dimensions (square)
        var _URL = window.URL || window.webkitURL;
        $("input#product_image").on('change', function () {
            var fileUpload = $("#product_image")[0];
            if (typeof (fileUpload.files[0]) != "undefined" && fileUpload.files != null) {
                var reader = new FileReader();
                reader.readAsDataURL(fileUpload.files[0]);

                reader.onload = function (e) {
                    var image = new Image();
                    image.src = e.target.result;
                    image.onload = function () {
                        var height = this.height;
                        var width = this.width;
                        if (height != width) {
                            alert("Please use same width and height image like 800px X 800px. Error! " + width + "px X " + height + "px");
                            $("#product_image").val('');
                            return false;
                        }
                        return true;
                    };
                }
            }
        });
    });

    // Variants Management
    function addVariant() {
        const container = document.getElementById('variants-container');
        const variantCount = container.querySelectorAll('.variant-item').length + 1;
        
        const variantHTML = `
            <div class="variant-item" style="border: 1px solid #e2e8f0; border-radius: 6px; padding: 16px; margin-bottom: 16px; background-color: #f8fafc;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                    <h3 style="margin: 0; font-size: 15px; font-weight: 600; color: #1e293b;">Variant ${variantCount}</h3>
                    <button type="button" class="btn--remove-variant" onclick="removeVariant(this)" style="background: none; border: none; color: #e74c3c; cursor: pointer; font-size: 18px; padding: 0;">×</button>
                </div>

                <div class="card__body--columns" style="padding: 0; margin: 0;">
                    <div class="form-group">
                        <label>Variant Name <span class="required">*</span></label>
                        <input type="text" name="variant_name[]" class="form-control" placeholder="e.g., Red - Size M" required>
                    </div>

                    <div class="form-group">
                        <label>Option 1 <span class="required">*</span></label>
                        <input type="text" name="variant_option1[]" class="form-control" placeholder="e.g., Color" required>
                    </div>

                    <div class="form-group">
                        <label>Option 1 Value <span class="required">*</span></label>
                        <input type="text" name="variant_option1_value[]" class="form-control" placeholder="e.g., Red" required>
                    </div>

                    <div class="form-group">
                        <label>Option 2</label>
                        <input type="text" name="variant_option2[]" class="form-control" placeholder="e.g., Size">
                    </div>

                    <div class="form-group">
                        <label>Option 2 Value</label>
                        <input type="text" name="variant_option2_value[]" class="form-control" placeholder="e.g., Medium">
                    </div>

                    <div class="form-group">
                        <label>Option 3</label>
                        <input type="text" name="variant_option3[]" class="form-control" placeholder="e.g., Material">
                    </div>
                </div>

                <div style="border-top: 1px solid #e2e8f0; margin: 16px 0; padding-top: 16px;">
                    <h4 style="margin: 0 0 16px 0; font-size: 14px; font-weight: 600; color: #475569;">Pricing & Inventory</h4>
                    
                    <div class="card__body--columns" style="padding: 0; margin: 0;">
                        <div class="form-group">
                            <label>Price <span class="required">*</span></label>
                            <input type="number" step="0.01" min="0" name="variant_price[]" class="form-control" placeholder="0.00" required>
                        </div>

                        <div class="form-group">
                            <label>Compare-at Price</label>
                            <input type="number" step="0.01" min="0" name="variant_compare_price[]" class="form-control" placeholder="0.00">
                        </div>

                        <div class="form-group">
                            <label>Cost</label>
                            <input type="number" step="0.01" min="0" name="variant_cost[]" class="form-control" placeholder="0.00">
                        </div>

                        <div class="form-group">
                            <label>SKU</label>
                            <input type="text" name="variant_sku[]" class="form-control" placeholder="e.g., RED-M-001">
                        </div>

                        <div class="form-group">
                            <label>Barcode</label>
                            <input type="text" name="variant_barcode[]" class="form-control" placeholder="e.g., 123456789">
                        </div>

                        <div class="form-group">
                            <label>Quantity <span class="required">*</span></label>
                            <input type="number" step="1" min="0" name="variant_quantity[]" class="form-control" value="0" required>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        container.insertAdjacentHTML('beforeend', variantHTML);
    }

    function removeVariant(button) {
        const container = document.getElementById('variants-container');
        const variantItems = container.querySelectorAll('.variant-item');
        
        // Don't allow removing if there's only one variant
        if (variantItems.length > 1) {
            button.closest('.variant-item').remove();
            // Update variant numbers
            updateVariantNumbers();
        } else {
            alert('You must have at least one variant');
        }
    }

    function updateVariantNumbers() {
        const container = document.getElementById('variants-container');
        const variantItems = container.querySelectorAll('.variant-item');
        
        variantItems.forEach((item, index) => {
            const title = item.querySelector('h3');
            title.textContent = `Variant ${index + 1}`;
        });
    }
</script>

<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-plus"></i><?= lang('add_product'); ?></h2>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">

                <p class="introtext" style="margin-bottom: 25px;">Enter product information below</p>

                <?php
                $attrib = ['data-toggle' => 'validator', 'role' => 'form'];
                echo admin_form_open_multipart('products/add_new', $attrib)
                ?>


<div class="product-form-page">


    <div class="product-form__layout">

      <!-- LEFT COLUMN -->
      <div class="product-form__main">

        <!-- Basic info -->
        <section class="card">
          <header class="card__header">
            <h2 class="card__title">Product details</h2>
          </header>
          <div class="card__body">
            <!-- Title -->
            <div class="form-group">
              <label for="title">Title <span class="required">*</span></label>
              <input
                type="text"
                id="title"
                name="title"
                class="form-control"
                required
                placeholder="Product title">
            </div>

            <!-- Description -->
            <div class="form-group">
              <label for="description">Description</label>
              <textarea
                id="description"
                name="description"
                class="form-control form-control--textarea"
                rows="6"
                placeholder="Describe your product"></textarea>
            </div>

            <!-- Status -->
            <div class="form-group form-group--inline">
              <label for="status">Status</label>
              <select id="status" name="status" class="form-control">
                <option value="DRAFT">Draft</option>
                <option value="ACTIVE">Active</option>
              </select>
            </div>
          </div>
        </section>

        <!-- Variants -->
        <section class="card">
          <header class="card__header">
            <h2 class="card__title">Variants</h2>
          </header>
          <div class="card__body">
            <div class="form-group">
              <label for="image_file">Image upload</label>
              <input type="file" id="image_file" name="image_file" class="form-control">
              <p class="form-hint">Upload a product image file (JPG, PNG, etc.).</p>
            </div>

            <div class="form-group">
              <label for="image_url">Or image URL</label>
              <input
                type="url"
                id="image_url"
                name="image_url"
                class="form-control"
                placeholder="https://example.com/image.jpg">
              <p class="form-hint">If you already host images on a CDN, you can paste the URL here.</p>
            </div>
          </div>
        </section>

        <!-- Pricing -->
        <section class="card">
          <header class="card__header">
            <h2 class="card__title">Pricing</h2>
          </header>
          <div class="card__body card__body--columns">

            <div class="form-group">
              <label for="price">Price <span class="required">*</span></label>
              <input
                type="number"
                step="0.01"
                min="0"
                id="price"
                name="price"
                class="form-control"
                required>
            </div>

            <div class="form-group">
              <label for="compare_at_price">Compare-at price</label>
              <input
                type="number"
                step="0.01"
                min="0"
                id="compare_at_price"
                name="compare_at_price"
                class="form-control">
              <p class="form-hint">Optional. Original price before discount.</p>
            </div>

            <div class="form-group">
              <label for="cost_price">Cost per item</label>
              <input
                type="number"
                step="0.01"
                min="0"
                id="cost_price"
                name="cost_price"
                class="form-control">
              <p class="form-hint">Your cost price. Used to set Shopify inventory item cost.</p>
            </div>

          </div>

          <div class="card__body">
            <div class="form-group form-group--inline">
              <label class="checkbox-label">
                <input type="checkbox" name="taxable" value="1" checked>
                Charge tax on this product
              </label>
            </div>
          </div>
        </section>

        <!-- Inventory -->
        <section class="card">
          <header class="card__header">
            <h2 class="card__title">Inventory</h2>
          </header>
          <div class="card__body card__body--columns">
            <div class="form-group">
              <label for="sku">SKU (Stock keeping unit)</label>
              <input
                type="text"
                id="sku"
                name="sku"
                class="form-control"
                placeholder="e.g. 305210044876">
            </div>

            <div class="form-group">
              <label for="barcode">Barcode</label>
              <input
                type="text"
                id="barcode"
                name="barcode"
                class="form-control"
                placeholder="e.g. 305210044876">
            </div>
          </div>

          <div class="card__body card__body--columns">
            <div class="form-group">
              <label class="checkbox-label">
                <input type="checkbox" name="track_quantity" value="1" checked>
                Track quantity
              </label>
            </div>

            <div class="form-group">
              <label for="initial_stock">Quantity</label>
              <input
                type="number"
                step="1"
                min="0"
                id="initial_stock"
                name="initial_stock"
                class="form-control"
                value="0">
            </div>
          </div>

          <div class="card__body">
            <div class="form-group">
              <label>Inventory policy</label>
              <label class="radio-label">
                <input type="radio" name="inventory_policy" value="DENY" checked>
                Don’t allow selling when out of stock
              </label>
              <label class="radio-label">
                <input type="radio" name="inventory_policy" value="CONTINUE">
                Continue selling when out of stock
              </label>
            </div>
          </div>
        </section>
        <!-- Variants -->
        <section class="card">
          <header class="card__header">
            <h2 class="card__title">Variants</h2>
          </header>
          <div class="card__body">
            <p class="form-hint" style="margin-bottom: 16px;">Add product variants with different sizes, colors, or other attributes</p>
            
            <div id="variants-container">
              <!-- Variant 1 (Default) -->
              <div class="variant-item" style="border: 1px solid #e2e8f0; border-radius: 6px; padding: 16px; margin-bottom: 16px; background-color: #f8fafc;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                  <h3 style="margin: 0; font-size: 15px; font-weight: 600; color: #1e293b;">Variant 1</h3>
                  <button type="button" class="btn--remove-variant" onclick="removeVariant(this)" style="background: none; border: none; color: #e74c3c; cursor: pointer; font-size: 18px; padding: 0;">×</button>
                </div>

                <div class="card__body--columns" style="padding: 0; margin: 0;">
                  <div class="form-group">
                    <label>Variant Name <span class="required">*</span></label>
                    <input type="text" name="variant_name[]" class="form-control" placeholder="e.g., Red - Size M" required>
                  </div>

                  <div class="form-group">
                    <label>Option 1 <span class="required">*</span></label>
                    <input type="text" name="variant_option1[]" class="form-control" placeholder="e.g., Color" required>
                  </div>

                  <div class="form-group">
                    <label>Option 1 Value <span class="required">*</span></label>
                    <input type="text" name="variant_option1_value[]" class="form-control" placeholder="e.g., Red" required>
                  </div>

                  <div class="form-group">
                    <label>Option 2</label>
                    <input type="text" name="variant_option2[]" class="form-control" placeholder="e.g., Size">
                  </div>

                  <div class="form-group">
                    <label>Option 2 Value</label>
                    <input type="text" name="variant_option2_value[]" class="form-control" placeholder="e.g., Medium">
                  </div>

                  <div class="form-group">
                    <label>Option 3</label>
                    <input type="text" name="variant_option3[]" class="form-control" placeholder="e.g., Material">
                  </div>
                </div>

                <div style="border-top: 1px solid #e2e8f0; margin: 16px 0; padding-top: 16px;">
                  <h4 style="margin: 0 0 16px 0; font-size: 14px; font-weight: 600; color: #475569;">Pricing & Inventory</h4>
                  
                  <div class="card__body--columns" style="padding: 0; margin: 0;">
                    <div class="form-group">
                      <label>Price <span class="required">*</span></label>
                      <input type="number" step="0.01" min="0" name="variant_price[]" class="form-control" placeholder="0.00" required>
                    </div>

                    <div class="form-group">
                      <label>Compare-at Price</label>
                      <input type="number" step="0.01" min="0" name="variant_compare_price[]" class="form-control" placeholder="0.00">
                    </div>

                    <div class="form-group">
                      <label>Cost</label>
                      <input type="number" step="0.01" min="0" name="variant_cost[]" class="form-control" placeholder="0.00">
                    </div>

                    <div class="form-group">
                      <label>SKU</label>
                      <input type="text" name="variant_sku[]" class="form-control" placeholder="e.g., RED-M-001">
                    </div>

                    <div class="form-group">
                      <label>Barcode</label>
                      <input type="text" name="variant_barcode[]" class="form-control" placeholder="e.g., 123456789">
                    </div>

                    <div class="form-group">
                      <label>Quantity <span class="required">*</span></label>
                      <input type="number" step="1" min="0" name="variant_quantity[]" class="form-control" value="0" required>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <button type="button" class="btn" onclick="addVariant()" style="background-color: #f1f5f9; border: 1px solid #cbd5e1; color: #475569; width: 100%; margin-top: 8px;">+ Add another variant</button>
          </div>
        </section>
      </div>

      <!-- RIGHT COLUMN -->
      <aside class="product-form__side">

        <!-- Organization -->
        <section class="card">
          <header class="card__header">
            <h2 class="card__title">Organization</h2>
          </header>
          <div class="card__body">
            <div class="form-group">
              <label for="vendor">Vendor</label>
              <input
                type="text"
                id="vendor"
                name="vendor"
                class="form-control"
                placeholder="Brand name">
            </div>

            <div class="form-group">
              <label for="product_type">Product type</label>
              <input
                type="text"
                id="product_type"
                name="product_type"
                class="form-control"
                placeholder="e.g. Lip Care">
            </div>

            <!-- Category could be added later as a select -->
            <!--
            <div class="form-group">
              <label for="category_id">Category</label>
              <select id="category_id" name="category_id" class="form-control">
                <option value="">Select category</option>
                ...
              </select>
            </div>
            -->

            <div class="form-group">
              <label for="tags">Tags</label>
              <input
                type="text"
                id="tags"
                name="tags"
                class="form-control"
                placeholder="Comma-separated, e.g. lip care, moisturising, shimmer">
              <p class="form-hint">Used for filtering and search in Shopify.</p>
            </div>
          </div>
        </section>

        <!-- Actions -->
        <section class="card card--sticky">
          <div class="card__body card__body--actions">
            <button type="submit" class="btn btn--primary">Save product</button>
            <a href="/products" class="btn btn--ghost">Cancel</a>
          </div>
        </section>

      </aside>

    </div>


</div>


            
                <?= form_close(); ?>

            </div>
        </div>
    </div>
</div>


