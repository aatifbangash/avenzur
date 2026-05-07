<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-eye"></i><?php echo $page_title; ?></h2>
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
                <?php if ($this->session->flashdata('message')) { ?>
                    <div class="alert alert-success">
                        <button data-dismiss="alert" class="close" type="button">×</button>
                        <?php echo $this->session->flashdata('message'); ?>
                    </div>
                <?php } ?>


                <?php if (!empty($rows)) { ?>
                    <?php echo form_open("admin/purchase_order_upload/submit"); ?>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="excel_date">Date</label>
                            <input type="text" name="date" id="excel_date" class="form-control datetime" required>
                        </div>
                    </div>
                    <div class="col-md-4">
                            <div class="form-group">
                                <label for="supplier_id"><?php echo $this->lang->line("supplier"); ?> *</label>
                                <?php
                                $sup[""] = "";
                                foreach ($child_suppliers as $supplier) {
                                    $sup[$supplier->id] = $supplier->name;
                                }
                                echo form_dropdown('supplier', $sup, '', 'id="excel_supplier" class="form-control select" required style="width:100%;"');
                                ?>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="warehouse_id"><?php echo $this->lang->line("warehouse"); ?> *</label>
                                <?php
                                    $wh[""] = "";
                                    foreach ($warehouses as $warehouse) {
                                        $wh[$warehouse->id] = $warehouse->name . ' (' . $warehouse->code . ')';
                                    }
                                    echo form_dropdown('warehouse', $wh, '', 'id="excel_warehouse" class="form-control select" required style="width:100%;"');
                                ?>
                            </div>
                        </div>
                    <div class="row">
                        
                    </div>

                    <div class="table-responsive">
                        <form action="<?php echo site_url('admin/purchase_order_upload/save_excel'); ?>" method="post">
                        <table class="table table-bordered table-hover table-striped">
                            <thead>
                            <tr>
                                <th><?php echo $this->lang->line("row_id"); ?></th>
                                <th><?php echo $this->lang->line("item_barcode"); ?></th>
                                <th><?php echo $this->lang->line("item_name"); ?></th>
                                <th><?php echo $this->lang->line("variant_barcode"); ?></th>
                                <th><?php echo $this->lang->line("variant_name"); ?></th>
                                <th><?php echo $this->lang->line("brand"); ?></th>
                                <th><?php echo $this->lang->line("batch_no"); ?></th>
                                <th><?php echo $this->lang->line("expiry_date"); ?></th>
                                <th><?php echo $this->lang->line("quantity"); ?></th>
                                <th><?php echo $this->lang->line("sale_price_inc_vat"); ?></th>
                                <th><?php echo $this->lang->line("purchase_price"); ?></th>
                                <th><?php echo $this->lang->line("cost_price"); ?></th>
                                <th><?php echo $this->lang->line("tax_rate"); ?> (%)</th>
                                <th><?php echo $this->lang->line("discount_1"); ?> (%)</th>
                                <th><?php echo $this->lang->line("discount_1"); ?> <?php echo $this->lang->line("value"); ?></th>
                                <th><?php echo $this->lang->line("discount_2"); ?> (%)</th>
                                <th><?php echo $this->lang->line("discount_2"); ?> <?php echo $this->lang->line("value"); ?></th>
                                <th><?php echo $this->lang->line("discount_3"); ?> (%)</th>
                                <th><?php echo $this->lang->line("discount_3"); ?> <?php echo $this->lang->line("value"); ?></th>
                                <th><?php echo $this->lang->line("description"); ?></th>
                                <th><?php echo $this->lang->line("image"); ?></th>
                                <th><?php echo $this->lang->line("Shelf Life"); ?></th>
                                <th><?php echo $this->lang->line("subtotal"); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($rows as $i => $row) { ?>
                                <tr>
                                    <td><?php echo $i + 1; ?></td>
                                    <td><?php echo htmlspecialchars($row['item_barcode'] ?? ''); ?></td>
                                    <td><?php echo word_limiter($row['item_name'] ?? '', 4); ?></td>
                                    <td><?php echo htmlspecialchars($row['variant_barcode'] ?? ''); ?></td>
                                    <td><?php echo htmlspecialchars($row['variant_name'] ?? ''); ?></td>
                                    <td><?php echo htmlspecialchars($row['brand_name'] ?? ''); ?></td>
                                    <td><?php echo htmlspecialchars($row['batch_number'] ?? ''); ?></td>
                                    <td><?php echo htmlspecialchars($row['expiry_date'] ?? ''); ?></td>
                                    <td><?php echo number_format($row['quantity'] ?? 0, 2); ?></td>
                                    <td><?php echo number_format($row['sale_price'] ?? 0, 2); ?></td>
                                    <td><?php echo number_format($row['purchase_price'] ?? 0, 2); ?></td>
                                    <td><?php echo number_format($row['cost_price'] ?? 0, 2); ?></td>
                                    <td><?php echo number_format($row['vat_percent'] ?? 0, 2); ?></td>
                                    <td><?php echo number_format($row['discount1_percent'] ?? 0, 2); ?></td>
                                    <td><?php echo number_format($row['discount1_value'] ?? 0, 2); ?></td>
                                    <td><?php echo number_format($row['discount2_percent'] ?? 0, 2); ?></td>
                                    <td><?php echo number_format($row['discount2_value'] ?? 0, 2); ?></td>
                                    <td><?php echo number_format($row['discount3_percent'] ?? 0, 2); ?></td>
                                    <td><?php echo number_format($row['discount3_value'] ?? 0, 2); ?></td>
                                    <td><?php echo word_limiter($row['description_en'] ?? '', 4); ?></td>
                                    <td>
                                        <?php if (!empty($row['image_link'])): ?>
                                            <img src="<?php echo htmlspecialchars($row['image_link']); ?>" alt="product image" style="height:32px;width:auto">
                                        <?php else: ?>
                                            —
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($row['shelf_life'] ?? '—'); ?></td>
                                    <td><?php echo number_format($row['subtotal'] ?? 0, 2); ?></td>
                                </tr>
                            <?php } ?>
                        </tbody>

                        </table>
                    </div>
                    <input type="hidden" name="file_token" value="<?php echo $file_token; ?>">
                    <div class="form-group">
                        <a href="<?php echo admin_url('purchase_order_upload'); ?>" class="btn btn-warning"><?php echo $this->lang->line('back'); ?></a>
                        <?php echo form_submit('submit', $this->lang->line('submit'), 'class="btn btn-primary" id="submitBtn"'); ?>
                    </div>

                    <?php echo form_close(); ?>
                <?php } else { ?>
                    <div class="alert alert-warning">
                        <h4><?php echo $this->lang->line('no_data'); ?></h4>
                        <p><?php echo $this->lang->line('no_data_info'); ?></p>
                        <a href="<?php echo admin_url('purchase_order_upload'); ?>" class="btn btn-primary"><?php echo $this->lang->line('upload_again'); ?></a>
                    </div>
                <?php } ?>
            </div>
        </div>
    </div>
</div>
<script>
$(document).ready(function () {
    $('form').on('submit', function (e) {
        var btn = $(this).find('input[type=submit], button[type=submit]');
        btn.prop('disabled', true).val('Processing...');
    });
});
</script>