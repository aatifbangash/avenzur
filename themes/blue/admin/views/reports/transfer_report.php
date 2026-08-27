<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-exchange"></i> <?= lang('transfer_report'); ?></h2>
    </div>
    <div class="box-content">
        <div class="row"><div class="col-lg-12">
        <?php
        $attrib = ['data-toggle' => 'validator', 'role' => 'form', 'id' => 'searchForm'];
        echo admin_form_open('reports/transfer_report', $attrib);
        ?>

        <div class="row align-items-end">

            <!-- From Warehouse -->
            <div class="col-md-3 mb-3">
                <label><?= lang('From Warehouse') ?></label>
                <select name="from_wh" class="form-control select2">
                    <option value=""><?= lang('All') ?></option>
                    <?php foreach ($warehouses as $w): ?>
                        <option value="<?= $w->id ?>" <?= (isset($filters['from_wh']) && $filters['from_wh'] == $w->id) ? 'selected' : '' ?>>
                            <?= $w->name ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- To Warehouse -->
            <div class="col-md-3 mb-3">
                <label><?= lang('To Warehouse') ?></label>
                <select name="to_wh" class="form-control select2">
                    <option value=""><?= lang('All') ?></option>
                    <?php foreach ($warehouses as $w): ?>
                        <option value="<?= $w->id ?>" <?= (isset($filters['to_wh']) && $filters['to_wh'] == $w->id) ? 'selected' : '' ?>>
                            <?= $w->name ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- From Date -->
            <div class="col-md-2 mb-3">
                <label><?= lang('From Date') ?></label>
                <input type="text" name="start_date" value="<?= $filters['start_date'] ?>" class="form-control date" autocomplete="off" />
            </div>

            <!-- To Date -->
            <div class="col-md-2 mb-3">
                <label><?= lang('To Date') ?></label>
                <input type="text" name="end_date" value="<?= $filters['end_date'] ?>" class="form-control date" autocomplete="off" />
            </div>

            <!-- Period -->
            <div class="col-md-2 mb-3">
                <label><?= lang('Period') ?></label>
                <select name="period" class="form-control">
                    <option value="today" <?= $filters['period']=='today'?'selected':'' ?>>Today</option>
                    <option value="month" <?= $filters['period']=='month'?'selected':'' ?>>This Month</option>
                    <option value="ytd" <?= $filters['period']=='ytd'?'selected':'' ?>>YTD</option>
                </select>
            </div>
        </div>

        <div class="row align-items-end">

            <!-- Product -->
            <div class="col-md-4 mb-3">
                <label><?= lang('Product') ?></label>
                <select name="product_id" class="form-control select2">
                    <option value=""><?= lang('All') ?></option>
                    <?php foreach ($products as $p): ?>
                        <option value="<?= $p->id ?>" <?= (isset($filters['product_id']) && $filters['product_id'] == $p->id) ? 'selected' : '' ?>>
                            <?= $p->name ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Invoice # -->
            <div class="col-md-2 mb-3">
                <label><?= lang('Invoice #') ?></label>
                <input type="text" name="invoice_no" value="<?= $filters['invoice_no'] ?>" class="form-control">
            </div>

            <!-- By -->
            <div class="col-md-2 mb-3">
                <label><?= lang('By') ?></label>
                <select name="by" class="form-control">
                    <option value="invoice" <?= $filters['by']=='invoice'?'selected':'' ?>>Invoice</option>
                    <option value="item" <?= $filters['by']=='item'?'selected':'' ?>>Item</option>
                </select>
            </div>

            <!-- Buttons aligned properly -->
            <div class="col-md-4 mb-3">
                <!-- Invisible label to align buttons vertically -->
                <label class="d-block">&nbsp;</label>
                <div class="d-flex">
                    <button type="submit" name="submit" class="btn btn-primary mr-2">Generate</button>
                    <button type="submit" name="export_excel" value="1" class="btn btn-success">Export Excel</button>
                </div>
            </div>

        </div>

        <?php echo form_close(); ?>
        <hr>
        <!-- Report display -->
        <?php if (!empty($reportData)): ?>
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <?php if ($filters['by'] == 'invoice'): ?>
                            <tr>
                                <th><?= lang('Date') ?></th>
                                <th><?= lang('Invoice #') ?></th>
                                <th><?= lang('From Warehouse') ?></th>
                                <th><?= lang('To Warehouse') ?></th>
                                <th><?= lang('Total Sale Price') ?></th>
                                <th><?= lang('Total Cost Price') ?></th>
                                <th><?= lang('Profit Amt') ?></th>
                                <th><?= lang('Profit Margin %') ?></th>
                            </tr>
                        <?php else: ?>
                            <tr>
                                <th><?= lang('Date') ?></th>
                                <th><?= lang('Invoice #') ?></th>
                                <th><?= lang('Item') ?></th>
                                <th><?= lang('Qty') ?></th>
                                <th><?= lang('Cost Price') ?></th>
                                <th><?= lang('Sale Price') ?></th>
                                <th><?= lang('Profit Amt') ?></th>
                                <th><?= lang('Profit Margin %') ?></th>
                                <th><?= lang('From Warehouse') ?></th>
                                <th><?= lang('To Warehouse') ?></th>
                            </tr>
                        <?php endif; ?>
                    </thead>
                    <tbody>
                        <?php if ($filters['by'] == 'invoice'): ?>
                            <?php foreach ($reportData as $r): ?>
                                <tr>
                                    <td><?= $r['date'] ?></td>
                                    <td><?= $r['transfer_id'] ?></td>
                                    <td><?= $r['from_wh_name'] ?></td>
                                    <td><?= $r['to_wh_name'] ?></td>
                                    <td><?= $this->sma->formatMoney($r['total_sale'], 'none') ?></td>
                                    <td><?= $this->sma->formatMoney($r['total_cost'], 'none') ?></td>
                                    <td><?= $this->sma->formatMoney($r['total_profit'], 'none') ?></td>
                                    <td><?= $r['total_margin_percent'] ?>%</td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <?php foreach ($reportData as $r): ?>
                                <tr>
                                    <td><?= $r['date'] ?></td>
                                    <td><?= $r['transfer_id'] ?></td>
                                    <td><?= $r['product_name'] ?></td>
                                    <td><?= $r['quantity'] ?></td>
                                    <td><?= $this->sma->formatMoney($r['cost_price'], 'none') ?></td>
                                    <td><?= $this->sma->formatMoney($r['sale_price'], 'none') ?></td>
                                    <td><?= $this->sma->formatMoney($r['profit_amt'], 'none') ?></td>
                                    <td><?= $r['margin_percent'] ?>%</td>
                                    <td><?= $r['from_wh_name'] ?></td>
                                    <td><?= $r['to_wh_name'] ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p class="text-center"><?= lang('no_data_available') ?></p>
        <?php endif; ?>

        </div></div>
    </div>
</div>

<script>
$(document).ready(function(){
    if ($.fn.select2) { $('.select2').select2({ width: 'resolve' }); }
    if ($.fn.datepicker) { $('.date').datepicker({ autoclose: true, format: 'yyyy-mm-dd' }); }
});
</script>