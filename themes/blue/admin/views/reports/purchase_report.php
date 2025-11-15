<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-file-text-o"></i><?= lang('purchase_report'); ?></h2>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
                <?php
$attrib = ['data-toggle' => 'validator', 'role' => 'form', 'id' => 'searchForm'];
echo admin_form_open('reports/purchase_report', $attrib);
?>

<div class="row">

    <!-- Supplier -->
    <div class="col-md-3 mb-3">
        <label><?= lang('Supplier(s)') ?></label>
        <select name="supplier_ids[]" class="form-control select2" multiple>
            <?php foreach ($suppliers as $s): ?>
                <option value="<?= $s->id ?>" <?= in_array($s->id, $filters['supplier_ids']) ? 'selected' : '' ?>>
                    <?= $s->name ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <!-- Pharmacy -->
    <div class="col-md-3 mb-3">
        <label><?= lang('Pharmacy(s)') ?></label>
        <select name="pharmacy_ids[]" class="form-control select2" multiple>
            <?php foreach ($warehouses as $w): ?>
                <option value="<?= $w->id ?>" <?= in_array($w->id, $filters['pharmacy_ids']) ? 'selected' : '' ?>>
                    <?= $w->name ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <!-- Invoice -->
    <div class="col-md-2 mb-3">
        <label><?= lang('Invoice #') ?></label>
        <input type="text" name="invoice_no" value="<?= $filters['invoice_no'] ?>" class="form-control">
    </div>

    <!-- Product -->
    <div class="col-md-4 mb-3">
        <label><?= lang('Product') ?></label>
        <select name="product_id" class="form-control select2">
            <option value="">All</option>
            <?php foreach ($products as $p): ?>
                <option value="<?= $p->id ?>" <?= ($filters['product_id'] == $p->id) ? 'selected' : '' ?>>
                    <?= $p->name ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

</div>

<!-- Second Row -->
<div class="row">

    <!-- Period -->
    <div class="col-md-2 mb-3">
        <label><?= lang('Period') ?></label>
        <select name="period" class="form-control">
            <option value="today" <?= $filters['period']=='today'?'selected':'' ?>>Today</option>
            <option value="month" <?= $filters['period']=='month'?'selected':'' ?>>This Month</option>
            <option value="ytd" <?= $filters['period']=='ytd'?'selected':'' ?>>YTD</option>
        </select>
    </div>

    <!-- Group By -->
    <div class="col-md-2 mb-3">
        <label><?= lang('Group By') ?></label>
        <select name="group_by" class="form-control">
            <option value="item" <?= $filters['group_by']=='item'?'selected':'' ?>>By Item</option>
            <option value="supplier" <?= $filters['group_by']=='supplier'?'selected':'' ?>>By Supplier</option>
        </select>
    </div>

</div>
<hr>
<!-- Buttons Row -->
<div class="row">
    <div class="col-md-2 mb-3">
        <button type="submit" name="submit" class="btn btn-primary btn-block">Generate</button>
    </div>

    <div class="col-md-2 mb-3">
        <button type="submit" name="export_excel" value="1" class="btn btn-success btn-block">Export Excel</button>
    </div>
</div>

<?php echo form_close(); ?>
<hr>


                <!-- REPORT TABLE -->
                <?php if (!empty($reportData)) : ?>
                    <div class="row">
                        <div class="col-lg-12 table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead>
                                <?php if ($filters['group_by'] == 'item') : ?>
                                    <tr>
                                        <th><?= lang('Date') ?></th>
                                        <th><?= lang('Invoice #') ?></th>
                                        <th><?= lang('Product') ?></th>
                                        <th><?= lang('Qty') ?></th>
                                        <th><?= lang('Batch #') ?></th>
                                        <th><?= lang('Sale Price') ?></th>
                                        <th><?= lang('Purchase Price') ?></th>
                                        <th><?= lang('Discount') ?></th>
                                        <th><?= lang('Cost Price') ?></th>
                                        <th><?= lang('Margin %') ?></th>
                                        <th><?= lang('Supplier') ?></th>
                                        <th><?= lang('Pharmacy') ?></th>
                                    </tr>
                                <?php else: ?>
                                    <tr>
                                        <th><?= lang('Supplier') ?></th>
                                        <th><?= lang('Total Sale Price') ?></th>
                                        <th><?= lang('Total Purchase') ?></th>
                                        <th><?= lang('Total Discount') ?></th>
                                        <th><?= lang('Total Cost Price') ?></th>
                                        <th><?= lang('Total Margin %') ?></th>
                                        <th><?= lang('Total Margin Amount') ?></th>
                                    </tr>
                                <?php endif; ?>
                                </thead>
                                <tbody>
                                <?php if ($filters['group_by'] == 'item'): ?>
                                    <?php foreach ($reportData as $row): ?>
                                        <tr>
                                            <td><?= $row['date'] ?></td>
                                            <td><?= $row['invoice_no'] ?></td>
                                            <td><?= $row['product_name'] ?></td>
                                            <td><?= $row['quantity'] ?></td>
                                            <td><?= $row['batch_no'] ?></td>
                                            <td><?= $this->sma->formatMoney($row['sale_price'], 'none') ?></td>
                                            <td><?= $this->sma->formatMoney($row['purchase_price'], 'none') ?></td>
                                            <td><?= $this->sma->formatMoney($row['discount'], 'none') ?></td>
                                            <td><?= $this->sma->formatMoney($row['cost_price'], 'none') ?></td>
                                            <td><?= $row['margin_percent'] ?>%</td>
                                            <td><?= $row['supplier_name'] ?></td>
                                            <td><?= $row['pharmacy_name'] ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <?php foreach ($reportData as $row): ?>
                                        <tr>
                                            <td><?= $row['supplier_name'] ?></td>
                                            <td><?= $this->sma->formatMoney($row['total_sale_price'], 'none') ?></td>
                                            <td><?= $this->sma->formatMoney($row['total_purchase'], 'none') ?></td>
                                            <td><?= $this->sma->formatMoney($row['total_discount'], 'none') ?></td>
                                            <td><?= $this->sma->formatMoney($row['total_cost_price'], 'none') ?></td>
                                            <td><?= $row['total_margin_percent'] ?>%</td>
                                            <td><?= $this->sma->formatMoney($row['total_margin_amount'], 'none') ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                <?php else: ?>
                    <p class="text-center"><?= lang('no_data_available') ?></p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
