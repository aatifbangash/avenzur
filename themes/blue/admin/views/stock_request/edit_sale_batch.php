<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<style>
    .batch-table th { white-space: nowrap; }
    .batch-table td { vertical-align: middle !important; }
    .batch-table input[type="text"],
    .batch-table input[type="date"] {
        width: 100%;
        min-width: 110px;
    }
    .sale-info-box {
        background: #f9f9f9;
        border: 1px solid #ddd;
        border-radius: 4px;
        padding: 12px 18px;
        margin-bottom: 16px;
    }
    .sale-info-box span { margin-right: 24px; font-size: 13px; }
    .sale-info-box strong { color: #333; }
</style>

<div class="box">
    <div class="box-header">
        <h2 class="blue">
            <i class="fa-fw fa fa-pencil-square-o"></i>
            Edit Sale Batch / Expiry / Shelf
        </h2>
    </div>

    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">

                <?php if ($error): ?>
                    <div class="alert alert-danger"><?= $error ?></div>
                <?php endif; ?>
                <?php if ($message): ?>
                    <div class="alert alert-success"><?= $message ?></div>
                <?php endif; ?>

                <!-- ── Search Form ─────────────────────────────────────────── -->
                <?php echo admin_form_open('stock_request/edit_sale_batch', 'id="search-form" class="form-inline"'); ?>
                    <div class="form-group" style="margin-bottom:16px;">
                        <label for="sale_ref" style="margin-right:8px; font-weight:600;">
                            Sale Reference / ID
                        </label>
                        <input type="text"
                               id="sale_ref"
                               name="sale_ref"
                               class="form-control"
                               placeholder="e.g. SO-0001 or 42"
                               value="<?= htmlspecialchars($search_ref, ENT_QUOTES, 'UTF-8') ?>"
                               style="width:260px; margin-right:8px;"
                               autofocus />
                        <button type="submit" name="search" value="1" class="btn btn-primary">
                            <i class="fa fa-search"></i> Load Sale
                        </button>
                    </div>
                <?php echo form_close(); ?>

                <?php if ($sale): ?>

                    <!-- ── Sale Header Info ────────────────────────────────── -->
                    <div class="sale-info-box">
                        <span><strong>Reference:</strong> <?= htmlspecialchars($sale->reference_no, ENT_QUOTES, 'UTF-8') ?></span>
                        <span><strong>Date:</strong> <?= htmlspecialchars($sale->date, ENT_QUOTES, 'UTF-8') ?></span>
                        <span><strong>Customer:</strong> <?= htmlspecialchars($sale->customer, ENT_QUOTES, 'UTF-8') ?></span>
                        <span><strong>Status:</strong> <?= htmlspecialchars($sale->sale_status, ENT_QUOTES, 'UTF-8') ?></span>
                        <span><strong>Total:</strong> <?= number_format((float)$sale->grand_total, 2) ?></span>
                    </div>

                    <?php if ($items): ?>

                        <!-- ── Edit Form ───────────────────────────────────── -->
                        <?php
                            $attrib = ['role' => 'form', 'id' => 'batch-edit-form'];
                            echo admin_form_open('stock_request/edit_sale_batch', $attrib);
                        ?>
                        <input type="hidden" name="save_batch" value="1" />
                        <input type="hidden" name="sale_id"   value="<?= (int)$sale->id ?>" />

                        <div class="table-responsive">
                            <table class="table table-bordered table-condensed table-hover batch-table">
                                <thead>
                                    <tr class="active">
                                        <th style="width:3%; text-align:center;">#</th>
                                        <th>Code</th>
                                        <th>Product Name</th>
                                        <th>Qty</th>
                                        <th style="min-width:120px;">Current Batch</th>
                                        <th style="min-width:130px;">New Batch</th>
                                        <th style="min-width:120px;">Current Expiry</th>
                                        <th style="min-width:130px;">New Expiry</th>
                                        <th style="min-width:100px;">Current Shelf</th>
                                        <th style="min-width:100px;">New Shelf</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php $i = 0; foreach ($items as $item): ?>
                                    <tr>
                                        <td class="text-center"><?= ++$i ?></td>
                                        <td><?= htmlspecialchars($item->product_code, ENT_QUOTES, 'UTF-8') ?></td>
                                        <td>
                                            <?= htmlspecialchars($item->product_name, ENT_QUOTES, 'UTF-8') ?>
                                            <?php if (!empty($item->second_name)): ?>
                                                <br><small class="text-muted"><?= htmlspecialchars($item->second_name, ENT_QUOTES, 'UTF-8') ?></small>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center"><?= number_format((float)$item->quantity, 2) ?></td>

                                        <!-- Current batch (read-only display) -->
                                        <td>
                                            <span class="text-muted"><?= htmlspecialchars($item->batch_no ?? '—', ENT_QUOTES, 'UTF-8') ?></span>
                                        </td>
                                        <!-- New batch input -->
                                        <td>
                                            <input type="hidden" name="item_id[]"      value="<?= (int)$item->id ?>" />
                                            <input type="hidden" name="product_id[]"   value="<?= (int)$item->product_id ?>" />
                                            <input type="hidden" name="avz_item_code[]" value="<?= htmlspecialchars($item->avz_item_code ?? '', ENT_QUOTES, 'UTF-8') ?>" />
                                            <input type="text"
                                                   name="batch_no[]"
                                                   class="form-control input-sm"
                                                   value="<?= htmlspecialchars($item->batch_no ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                                   placeholder="Batch No" />
                                        </td>

                                        <!-- Current expiry (read-only display) -->
                                        <td>
                                            <span class="text-muted">
                                                <?= $item->expiry ? htmlspecialchars($item->expiry, ENT_QUOTES, 'UTF-8') : '—' ?>
                                            </span>
                                        </td>
                                        <!-- New expiry input -->
                                        <td>
                                            <input type="date"
                                                   name="expiry[]"
                                                   class="form-control input-sm"
                                                   value="<?= $item->expiry ? htmlspecialchars($item->expiry, ENT_QUOTES, 'UTF-8') : '' ?>"
                                                   placeholder="YYYY-MM-DD" />
                                        </td>

                                        <!-- Current shelf (from product) -->
                                        <td>
                                            <span class="text-muted"><?= htmlspecialchars($item->warehouse_shelf ?? '—', ENT_QUOTES, 'UTF-8') ?></span>
                                        </td>
                                        <!-- New shelf input -->
                                        <td>
                                            <input type="text"
                                                   name="shelf[]"
                                                   class="form-control input-sm"
                                                   value="<?= htmlspecialchars($item->warehouse_shelf ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                                   placeholder="e.g. A1" />
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div><!-- /.table-responsive -->

                        <div style="margin-top:12px;">
                            <button type="submit"
                                    class="btn btn-success"
                                    onclick="return confirm('Save batch / expiry / shelf changes for this sale?')">
                                <i class="fa fa-save"></i> Save Changes
                            </button>
                            <a href="<?= admin_url('stock_request/edit_sale_batch') ?>"
                               class="btn btn-default" style="margin-left:8px;">
                                <i class="fa fa-times"></i> Cancel
                            </a>
                        </div>

                        <?php echo form_close(); ?>

                    <?php else: ?>
                        <div class="alert alert-warning">No items found for this sale.</div>
                    <?php endif; ?>

                <?php endif; /* $sale */ ?>

            </div><!-- /.col -->
        </div><!-- /.row -->
    </div><!-- /.box-content -->
</div><!-- /.box -->
