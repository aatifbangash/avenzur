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

                <div class="col-md-6 col-md-offset-3">

                    <!-- Item Code -->
                    <div class="form-group">
                        <label for="item_code" style="font-weight: 600; margin-bottom: 8px;">
                            Item Code <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <?= form_input('item_code', set_value('item_code'), 'class="form-control" id="item_code" required="required" placeholder="Enter item code or scan barcode"') ?>
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
                        <?= form_input('name', set_value('name'), 'class="form-control" id="name" required="required" placeholder="Enter product name"'); ?>
                    </div>

                    <!-- Cost and Sale Price Row -->
                    <div class="row" style="margin-top: 20px;">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="cost" style="font-weight: 600; margin-bottom: 8px;">
                                    Cost <span class="text-danger">*</span>
                                </label>
                                <?= form_input('cost', set_value('cost'), 'class="form-control" id="cost" required="required" step="0.01" type="number" placeholder="0.00"') ?>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="sale_price" style="font-weight: 600; margin-bottom: 8px;">
                                    Sale Price <span class="text-danger">*</span>
                                </label>
                                <?= form_input('sale_price', set_value('sale_price'), 'class="form-control" id="sale_price" required="required" step="0.01" type="number" placeholder="0.00"') ?>
                            </div>
                        </div>
                    </div>

                    <!-- Description -->
                    <div class="form-group" style="margin-top: 20px;">
                        <label for="description" style="font-weight: 600; margin-bottom: 8px;">
                            Description <span class="text-danger">*</span>
                        </label>
                        <?= form_textarea('description', set_value('description'), 'class="form-control" id="description" rows="5" required="required" placeholder="Enter product description"'); ?>
                    </div>

                    <!-- Product Image -->
                    <div class="form-group" style="margin-top: 20px;">
                        <label for="product_image" style="font-weight: 600; margin-bottom: 8px;">
                            Product Image <span style="font-weight: 400; color: #999;">(Optional)</span>
                        </label>
                        <input id="product_image" type="file" data-browse-label="<?= lang('browse'); ?>"
                               name="product_image" data-show-upload="false"
                               data-show-preview="false" accept="image/*" class="form-control file">
                        <small class="help-block" style="margin-top: 5px; color: #999;">Image should be square (e.g., 800x800px) for best results</small>
                    </div>

                    <!-- Submit Button -->
                    <div class="form-group" style="margin-top: 30px;">
                        <?php echo form_submit('add_product', 'Add Product', 'class="btn btn-primary btn-block" style="padding: 12px; font-size: 16px;"'); ?>
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
            $('#item_code').val(randomCode);
        });

        // Prevent form submission on Enter in item_code field
        $('#item_code').bind('keypress', function (e) {
            if (e.keyCode == 13) {
                e.preventDefault();
                return false;
            }
        });
    });
</script>
