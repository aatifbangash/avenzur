<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-upload"></i> Import Inventory from Excel</h2>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">

                <?php if ($this->session->flashdata('message')): ?>
                    <div class="alert alert-success alert-dismissible" role="alert">
                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                        <?= $this->session->flashdata('message'); ?>
                    </div>
                <?php endif; ?>
                <?php if ($this->session->flashdata('error')): ?>
                    <div class="alert alert-danger alert-dismissible" role="alert">
                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                        <strong>Error:</strong> <?= $this->session->flashdata('error'); ?>
                    </div>
                <?php endif; ?>

                <?php
                $attrib = ['class' => 'form-horizontal', 'data-toggle' => 'validator', 'role' => 'form'];
                echo admin_form_open_multipart('products/import_inventory', $attrib);
                ?>

                <div class="row">
                    <div class="col-md-8">

                        <div class="well well-small">
                            <strong>Expected Excel columns (first row = header):</strong>
                            <ol class="text-info" style="margin:6px 0 0 0; padding-left:18px;">
                                <li>ASKON Code</li>
                                <li>GTIN</li>
                                <li>Item name</li>
                                <li>Batch No.</li>
                                <li>EXPIRY DATE</li>
                                <li>Qty</li>
                                <li>Sale Price</li>
                                <li>Purchase price</li>
                                <li>Cost Price</li>
                                <li>Vat (e.g. 0.15 or 0)</li>
                                <li>Supplier Name</li>
                                <li>Supplier Id(Code)</li>
                                <li>LOC_DESCRIPTION_A</li>
                            </ol>
                        </div>

                        <!-- Warehouse -->
                        <div class="form-group">
                            <label class="col-sm-3 control-label" for="warehouse_id">
                                Warehouse <span class="text-danger">*</span>
                            </label>
                            <div class="col-sm-9">
                                <select name="warehouse_id" id="warehouse_id" class="form-control select2" required="required">
                                    <option value="">-- Select Warehouse --</option>
                                    <?php foreach ($warehouses as $wh): ?>
                                        <option value="<?= $wh->id ?>"><?= htmlspecialchars($wh->name) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <!-- Supplier -->
                        <div class="form-group">
                            <label class="col-sm-3 control-label" for="supplier_id">
                                Supplier <span class="text-danger">*</span>
                            </label>
                            <div class="col-sm-9">
                                <select name="supplier_id" id="supplier_id" class="form-control select2" required="required">
                                    <option value="">-- Select Supplier --</option>
                                    <?php foreach ($suppliers as $sup): ?>
                                        <option value="<?= $sup->id ?>">
                                            <?= htmlspecialchars($sup->name) ?>
                                            <?php if (!empty($sup->cf1)): ?>(<?= htmlspecialchars($sup->cf1) ?>)<?php endif; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <!-- File Upload -->
                        <div class="form-group">
                            <label class="col-sm-3 control-label" for="excel_file">
                                Excel File (.xlsx) <span class="text-danger">*</span>
                            </label>
                            <div class="col-sm-9">
                                <input type="file"
                                       name="excel_file"
                                       id="excel_file"
                                       accept=".xlsx,.xls"
                                       class="form-control"
                                       required="required" />
                                <span class="help-block">Accepted formats: .xlsx, .xls — Max 50 MB</span>
                            </div>
                        </div>

                        <!-- Submit -->
                        <div class="form-group">
                            <div class="col-sm-offset-3 col-sm-9">
                                <?= form_submit('import', 'Import Inventory', 'class="btn btn-primary"'); ?>
                                <a href="<?= admin_url('products') ?>" class="btn btn-default">Cancel</a>
                            </div>
                        </div>

                    </div><!-- /.col-md-8 -->
                </div><!-- /.row -->

                <?= form_close(); ?>
            </div>
        </div>
    </div>
</div>
