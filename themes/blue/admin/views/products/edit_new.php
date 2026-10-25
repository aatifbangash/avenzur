<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

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
</script>

<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-edit"></i><?= lang('edit_product'); ?></h2>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">

                <p class="introtext" style="margin-bottom: 25px;">Update product information below</p>

                <?php
                $attrib = ['data-toggle' => 'validator', 'role' => 'form'];
                echo admin_form_open_multipart('products/edit_new/' . $product->id, $attrib)
                ?>

                <div class="col-md-6 col-md-offset-3">

                    <!-- Item Code -->
                    <div class="form-group">
                        <label for="code" style="font-weight: 600; margin-bottom: 8px;">
                            Item Code <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <?= form_input('code', (isset($_POST['code']) ? $_POST['code'] : ($product ? $product->code : '')), 'class="form-control" id="code" required="required" placeholder="Enter item code or scan barcode"') ?>
                            <span class="input-group-addon pointer" id="random_num" style="padding: 1px 10px;" title="Generate Random Code">
                                <i class="fa fa-random"></i>
                            </span>
                        </div>
                        <small class="help-block" style="margin-top: 5px; color: #999;">You can scan your barcode or click the random icon to generate a code</small>
                    </div>

                    <!-- Product Name -->
                    <div class="form-group" style="margin-top: 20px;">
                        <label for="name" style="font-weight: 600; margin-bottom: 8px;">
                            Product Name <span class="text-danger">*</span>
                        </label>
                        <?= form_input('name', (isset($_POST['name']) ? $_POST['name'] : ($product ? $product->name : '')), 'class="form-control" id="name" required="required" placeholder="Enter product name"'); ?>
                    </div>

                    <!-- Cost and Sale Price Row -->
                    <div class="row" style="margin-top: 20px;">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="price" style="font-weight: 600; margin-bottom: 8px;">
                                    Cost <span class="text-danger">*</span>
                                </label>
                                <?= form_input('price', (isset($_POST['price']) ? $_POST['price'] : ($product ? $this->sma->formatDecimal($product->price) : '')), 'class="form-control" id="price" required="required" step="0.01" type="number" placeholder="0.0000"') ?>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="price" style="font-weight: 600; margin-bottom: 8px;">
                                    Sale Price <span class="text-danger">*</span>
                                </label>
                                <?= form_input('price', (isset($_POST['price']) ? $_POST['price'] : ($product ? $this->sma->formatDecimal($product->price) : '')), 'class="form-control" id="price" required="required" step="0.01" type="number" placeholder="0.0000"') ?>
                            </div>
                        </div>
                    </div>

                    <!-- Tax Rate Row -->
                    <div class="row" style="margin-top: 20px;">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="tax_rate" style="font-weight: 600; margin-bottom: 8px;">
                                    Tax Rate <span class="text-danger">*</span>
                                </label>
                                <?php
                                foreach ($tax_rates as $tax) {
                                    $tr[$tax->id] = $tax->name;
                                }
                                //echo '<pre>';print_r($tr);exit;
                                echo form_dropdown('tax_rate', $tr, ($product->tax_rate), 'class="form-control select" id="tax_rate" placeholder="' . lang('select') . ' ' . lang('product_tax') . '" style="width:100%"')
                                ?>
                            </div>
                        </div>
                    </div>

                    <!-- Description -->
                    <div class="form-group" style="margin-top: 20px;">
                        <label for="product_details" style="font-weight: 600; margin-bottom: 8px;">
                            Description <span class="text-danger">*</span>
                        </label>
                        <?= form_textarea('product_details', (isset($_POST['product_details']) ? $_POST['product_details'] : ($product ? $product->details : '')), 'class="form-control" id="product_details" rows="5" required="required" placeholder="Enter product description"'); ?>
                    </div>

                    <!-- Current Product Image Display -->
<!--                    <div class="form-group" style="margin-top: 20px;">-->
<!--                        <label style="font-weight: 600; margin-bottom: 8px;">Current Image</label>-->
<!--                        <div style="margin-bottom: 10px;">-->
<!--                            --><?php //if ($product && $product->image) { ?>
<!--                                <img src="--><?php //= site_url('assets/uploads/'.$product->image) ?><!--" width="150" height="150" style="border: 1px solid #ddd; padding: 5px; border-radius: 4px;" />-->
<!--                            --><?php //} else { ?>
<!--                                <div style="width: 150px; height: 150px; border: 1px solid #ddd; padding: 5px; border-radius: 4px; display: flex; align-items: center; justify-content: center; background: #f5f5f5;">-->
<!--                                    <span style="color: #999;">No image</span>-->
<!--                                </div>-->
<!--                            --><?php //} ?>
<!--                        </div>-->
<!--                    </div>-->

                    <div class="form-group" style="margin-top: 20px;">
                        <label style="font-weight: 600; margin-bottom: 8px;">Current Image</label>
                        <div style="margin-bottom: 10px;">
                            <?php
                            $image_src = '';
                            if ($product && $product->image) {
                                if (str_starts_with($product->image, 'http://') || str_starts_with($product->image, 'https://')) {
                                    $image_src = $product->image;
                                } else {
                                    $image_src = site_url('assets/uploads/' . $product->image);
                                }
                            }
                            ?>

                            <?php if ($image_src) { ?>
                                <img src="<?= $image_src ?>" width="150" height="150" style="border: 1px solid #ddd; padding: 5px; border-radius: 4px;" />
                            <?php } else { ?>
                                <div style="width: 150px; height: 150px; border: 1px solid #ddd; padding: 5px; border-radius: 4px; display: flex; align-items: center; justify-content: center; background: #f5f5f5;">
                                    <span style="color: #999;">No image</span>
                                </div>
                            <?php } ?>
                        </div>
                    </div>


                    <!-- NEW: Image URL Input -->
                    <!-- This saves into the same 'image' column in your DB -->
                    <div class="form-group" style="margin-top: 20px;">
                        <label style="font-weight: 600; margin-bottom: 8px;">Image URL</label>
                        <input
                                type="text"
                                name="product_image_link"
                                id="product_image_link"
                                class="form-control"
                                placeholder="Paste image URL here (e.g. https://cdn.shopify.com/...)"
                                value="<?php
                                // Pre-fill with current value only if it's an external URL
                                if ($product && $product->image && (str_starts_with($product->image, 'http://') || str_starts_with($product->image, 'https://'))) {
                                    echo htmlspecialchars($product->image);
                                }
                                ?>"
                                style="width: 100%; padding: 8px 10px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;"
                        />
                        <small style="color: #888; margin-top: 4px; display: block;">
                            If you paste a URL here, it will be saved as the product image (replaces any uploaded file).
                        </small>
                    </div>

                    <!-- Product Image Upload -->
                    <div class="form-group" style="margin-top: 20px;">
                        <label for="product_image" style="font-weight: 600; margin-bottom: 8px;">
                            Product Image <span style="font-weight: 400; color: #999;">(Optional)</span>
                        </label>
                        <input id="product_image" type="file" data-browse-label="<?= lang('browse'); ?>"
                               name="product_image" data-show-upload="false"
                               data-show-preview="false" accept="image/*" class="form-control file">
                        <small class="help-block" style="margin-top: 5px; color: #999;">Image should be square (e.g., 800x800px) for best results. Leave empty to keep current image.</small>
                    </div>

                    <!-- Hidden fields to maintain backend compatibility -->
                    <?= form_hidden('type', (isset($_POST['type']) ? $_POST['type'] : ($product ? $product->type : 'standard'))); ?>
                    <?= form_hidden('barcode_symbology', (isset($_POST['barcode_symbology']) ? $_POST['barcode_symbology'] : ($product ? $product->barcode_symbology : 'code128'))); ?>
                    <?= form_hidden('item_code', (isset($_POST['item_code']) ? $_POST['item_code'] : ($product ? $product->item_code : ''))); ?>
                    <?= form_hidden('brand', (isset($_POST['brand']) ? $_POST['brand'] : ($product ? $product->brand : ''))); ?>
                    <?= form_hidden('category', (isset($_POST['category']) ? $_POST['category'] : ($product ? $product->category_id : ''))); ?>
                    <?= form_hidden('subcategory', (isset($_POST['subcategory']) ? $_POST['subcategory'] : ($product ? $product->subcategory_id : ''))); ?>
                    <?= form_hidden('unit', (isset($_POST['unit']) ? $_POST['unit'] : ($product ? $product->unit : ''))); ?>
                    <?= form_hidden('tax_rate', (isset($_POST['tax_rate']) ? $_POST['tax_rate'] : ($product ? $product->tax_rate : ''))); ?>
                    <?= form_hidden('tax_method', (isset($_POST['tax_method']) ? $_POST['tax_method'] : ($product ? $product->tax_method : ''))); ?>

                    <!-- Submit Button -->
                    <div class="form-group" style="margin-top: 30px;">
                        <?php echo form_submit('edit_product', 'Update Product', 'class="btn btn-primary btn-block" style="padding: 12px; font-size: 16px;"'); ?>
                    </div>

                    <div class="form-group" style="margin-top: 10px;">
                        <a href="<?= admin_url('products'); ?>" class="btn btn-default btn-block" style="padding: 12px; font-size: 16px;">Cancel</a>
                    </div>

                </div>

                <?= form_close(); ?>

            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function() {
        // Generate random code
        $('#random_num').click(function() {
            var randomCode = Math.floor(Math.random() * 9000000000) + 1000000000;
            $('#code').val(randomCode);
        });

        // Prevent form submission on Enter in code field
        $('#code').bind('keypress', function (e) {
            if (e.keyCode == 13) {
                e.preventDefault();
                return false;
            }
        });
    });
</script>