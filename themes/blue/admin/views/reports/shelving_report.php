<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-archive"></i>Warehouse Shelving Report</h2>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">

                <?php
                $attrib = ['data-toggle' => 'validator', 'role' => 'form', 'id' => 'shelvingForm'];
                echo admin_form_open('reports/shelving_report', $attrib);
                ?>

                <div class="row">

                    <div class="col-md-3 mb-3">
                        <label>Product Code / Name</label>
                        <input type="text" name="product_code" value="<?= htmlspecialchars($filters['product_code']) ?>" class="form-control" placeholder="Search product...">
                    </div>

                    <div class="col-md-2 mb-3">
                        <label>Status</label>
                        <select name="status" class="form-control">
                            <option value="">All</option>
                            <option value="active"   <?= $filters['status'] == 'active'   ? 'selected' : '' ?>>Active</option>
                            <option value="restock"  <?= $filters['status'] == 'restock'  ? 'selected' : '' ?>>Restock</option>
                            <option value="inactive" <?= $filters['status'] == 'inactive' ? 'selected' : '' ?>>Inactive</option>
                        </select>
                    </div>

                    <div class="col-md-2 mb-3">
                        <label>Expiry Date From</label>
                        <input type="date" name="expiry_from" value="<?= htmlspecialchars($filters['expiry_from']) ?>" class="form-control">
                    </div>

                    <div class="col-md-2 mb-3">
                        <label>Expiry Date To</label>
                        <input type="date" name="expiry_to" value="<?= htmlspecialchars($filters['expiry_to']) ?>" class="form-control">
                    </div>

                    <div class="col-md-2 mb-3">
                        <label>Shelving ID</label>
                        <input type="number" name="shelving_id" value="<?= htmlspecialchars($filters['shelving_id']) ?>" class="form-control" placeholder="e.g. 7239">
                    </div>

                </div>

                <hr>

                <div class="row">
                    <div class="col-md-2 mb-3">
                        <button type="submit" name="submit" value="1" class="btn btn-primary btn-block">
                            <i class="fa fa-search"></i> Generate
                        </button>
                    </div>
                    <div class="col-md-2 mb-3">
                        <button type="submit" name="export_excel" value="1" class="btn btn-success btn-block">
                            <i class="fa fa-file-excel-o"></i> Export Excel
                        </button>
                    </div>
                    <div class="col-md-2 mb-3">
                        <button type="submit" name="export_pdf" value="1" class="btn btn-danger btn-block">
                            <i class="fa fa-file-pdf-o"></i> Export PDF
                        </button>
                    </div>
                </div>

                <?php echo form_close(); ?>

                <hr>

                <?php if (!empty($reportData)): ?>

                    <div class="row" style="margin-bottom:10px;">
                        <div class="col-lg-12">
                            <p class="text-muted" style="margin:0;">
                                Showing <strong><?= count($reportData) ?></strong> record(s)
                                <?php if (count($reportData) == 500): ?>
                                    &nbsp;<span class="label label-warning">Limit 500 — apply more filters to narrow results</span>
                                <?php endif; ?>
                            </p>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-lg-12 table-responsive">
                            <table class="table table-bordered table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>PO Date</th>
                                        <th>Product Code</th>
                                        <th>Product Name</th>
                                        <th>Batch #</th>
                                        <th>Expiry Date</th>
                                        <th>Qty</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($reportData as $i => $row): ?>
                                        <tr>
                                            <td><?= $i + 1 ?></td>
                                            <td><?= htmlspecialchars($row['po_date']) ?></td>
                                            <td><?= htmlspecialchars($row['product_code']) ?></td>
                                            <td><?= htmlspecialchars($row['product_name']) ?></td>
                                            <td><?= htmlspecialchars($row['batch_no']) ?></td>
                                            <td><?= htmlspecialchars($row['expiry_date']) ?></td>
                                            <td><?= htmlspecialchars($row['qty']) ?></td>
                                            <td>
                                                <?php
                                                $statusColors = ['active' => 'success', 'restock' => 'warning', 'inactive' => 'default'];
                                                $color = $statusColors[$row['status']] ?? 'default';
                                                ?>
                                                <span class="label label-<?= $color ?>">
                                                    <?= htmlspecialchars($row['status']) ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                <?php elseif ($this->input->post('submit') || $this->input->post('export_excel') || $this->input->post('export_pdf')): ?>
                    <p class="text-center text-muted" style="padding:20px 0;">No records found for the selected filters.</p>
                <?php else: ?>
                    <p class="text-center text-muted" style="padding:20px 0;">
                        Apply at least one filter (Status, Shelving ID, Product Code, or Expiry Date) and click <strong>Generate</strong>.
                    </p>
                <?php endif; ?>

            </div>
        </div>
    </div>
</div>
