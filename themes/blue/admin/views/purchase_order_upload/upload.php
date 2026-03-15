<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-upload"></i><?php echo $page_title; ?></h2>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">

                <?php if ($this->session->flashdata('error')) { ?>
                    <div class="alert alert-danger">
                        <button data-dismiss="alert" class="close" type="button">×</button>
                        <?php echo $this->session->flashdata('error'); ?>
                    </div>
                <?php } ?>

                <?php if ($this->session->flashdata('errors')) { ?>
                    <div class="alert alert-danger">
                        <button data-dismiss="alert" class="close" type="button">×</button>
                        <strong><?php echo lang('the_following_errors'); ?>:</strong>
                        <ul style="margin:6px 0 0 0;padding-left:18px">
                            <?php foreach ($this->session->flashdata('errors') as $err) { ?>
                                <li><?php echo htmlspecialchars($err); ?></li>
                            <?php } ?>
                        </ul>
                    </div>
                <?php } ?>

                <?php if ($this->session->flashdata('message')) { ?>
                    <div class="alert alert-success">
                        <button data-dismiss="alert" class="close" type="button">×</button>
                        <?php echo $this->session->flashdata('message'); ?>
                    </div>
                <?php } ?>

                <?php $attrib = array('data-toggle' => 'validator', 'role' => 'form');
                echo form_open_multipart("admin/purchase_order_upload/parse", $attrib); ?>

                <div class="row">
                    <div class="col-lg-12">
                        <div class="well well-small">
                            <a href="<?php echo base_url(); ?>assets/csv/sample_purchase_order.xlsx"
                               class="btn btn-primary pull-right">
                                <i class="fa fa-download"></i> <?php echo lang('Download Sample File'); ?>
                            </a>
                            <span class="text-info"><?php echo $this->lang->line("download_excel"); ?></span>
                        </div>
                    </div>
                </div>
                <div class="box">
                    <div class="box-header">
                        <h3 class="box-title">Excel File Format</h3>
                    </div>

                    <div class="box-content">

                        <p>Please upload the Excel file using the following column format.</p>

                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">

                                <thead>
                                    <tr>
                                        <th>Item Barcode *</th>
                                        <th>Item Name *</th>
                                        <th>Variant Barcode</th>
                                        <th>Variant Name</th>
                                        <th>Brand Name*</th>
                                        <th>Batch Number</th>
                                        <th>Expiry Date</th>
                                        <th>Quantity *</th>
                                        <th>Sale Price (without VAT) *</th>
                                        <th>Purchase Price (without VAT)</th>
                                        <th>Cost Price (without VAT)</th>
                                        <th>VAT %</th>
                                        <th>Discount 1 (%)</th>
                                        <th>Discount 1 Value</th>
                                        <th>Discount 2 (%)</th>
                                        <th>Discount 2 Value</th>
                                        <th>Discount 3 (%)</th>
                                        <th>Discount 3 Value</th>
                                        <th>Description</th>
                                        <th>Image URL *</th>
                                        <th>Shelf Life</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    <tr>
                                        <td>8004759189</td>  <!-- Item Number -->
                                        <td>Name of Product</td> <!-- Product Name -->
                                        <td>PRD-475</td>       <!-- Product Code -->
                                        <td>Product Variant</td> <!-- Variant -->
                                        <td>Brand Name</td> <!-- Brand Name -->
                                        <td>BW2409A</td>       <!-- Batch Number -->
                                        <td>2027-12-01</td>    <!-- Expiry Date -->
                                        <td>12</td>            <!-- Quantity -->
                                        <td>138.00</td>        <!-- Sale Price -->
                                        <td>120.00</td>        <!-- Purchase Price -->
                                        <td>75.00</td>         <!-- Cost Price -->
                                        <td>15</td>            <!-- VAT Percentage -->
                                        <td>5</td>             <!-- Discount 1 (%) -->
                                        <td>45.00</td>         <!-- Discount 1 Value -->
                                        <td>0</td>             <!-- Discount 2 (%) -->
                                        <td>0</td>             <!-- Discount 2 Value -->
                                        <td>0</td>             <!-- Discount 3 (%) -->
                                        <td>0</td>             <!-- Discount 3 Value -->
                                        <td>Product Description.</td> <!-- Product Description -->
                                        <td>https://image.com/products/total-war-blue-lemonade.png</td> <!-- Product Image URL -->
                                        <td>one week</td>           <!-- Shelf Life -->
                                    </tr>
                                </tbody>

                            </table>
                        </div>

                        <div class="alert alert-info">
                            <strong>Important Notes:</strong>

                            <ul>
                                <li>Fields marked with <strong>*</strong> are mandatory.</li>
                                <li>Image URL must be a valid link.</li>
                                <li>Quantity must be greater than 0.</li>
                                <li>Every row should have:Batch Number Expiry Date or Shelf Life</li>
                                <li>Use the sample file if unsure about the format.</li>
                            </ul>

                        </div>

                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="excel_file">
                                <?php echo $this->lang->line("upload_file"); ?> *
                            </label>
                            <input type="file"
                                   data-browse-label="<?php echo $this->lang->line('browse'); ?>"
                                   name="excel_file"
                                   class="form-control file"
                                   data-show-upload="false"
                                   data-show-preview="false"
                                   id="excel_file"
                                   required="required"
                                   accept=".xlsx,.xls"/>
                        </div>
                        <div class="form-group">
                            <?php echo form_submit('upload', $this->lang->line('upload'), 'class="btn btn-primary"'); ?>
                            <a href="<?php echo admin_url('purchase_orders'); ?>" class="btn btn-default">
                                <?php echo lang('cancel'); ?>
                            </a>
                        </div>
                    </div>
                </div>

                <?php echo form_close(); ?>

            </div>
        </div>
    </div>
</div>