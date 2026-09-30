<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa fa-chart-line"></i> <?= lang('Revenue Report') ?></h2>
    </div>
    <div class="box-content">
        <?= admin_form_open('reports/revenue_report'); ?>
        <div class="row">
            <!-- Pharmacy -->
            <div class="col-md-3">
                <div class="form-group">
                    <label><?= lang('Pharmacy') ?></label>
                    <select name="pharmacy" id="pharmacy" class="form-control select2" style="width:100%;">
                        <option value=""><?= lang('All') ?></option>
                        <?php foreach ($warehouses as $wh): ?>
                            <option value="<?= $wh->id ?>"
                                <?= isset($filters['pharmacy']) && $filters['pharmacy'] == $wh->id ? 'selected' : '' ?>>
                                <?= $wh->name ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <!-- Invoice -->
            <div class="col-md-3">
                <div class="form-group">
                    <label>Invoice #</label>
                    <input type="text" name="invoice_no" value="<?= $filters['invoice_no']; ?>" class="form-control" />
                </div>
            </div>

            <!-- Product -->
            <div class="col-md-3">
                <div class="form-group">
                    <label>Product</label>
                    <input type="text" name="product" value="<?= $filters['product']; ?>" class="form-control" />
                </div>
            </div>

            <!-- Supplier(s) -->
            <div class="col-md-3">
                <div class="form-group">
                    <label>Supplier(s)</label>
                    <select name="supplier_ids[]" class="form-control select2" multiple style="width:100%;">
                        <?php foreach ($suppliers as $s): ?>
                            <option value="<?= $s->id ?>" <?= in_array($s->id, $filters['supplier_ids']) ? 'selected' : '' ?>>
                                <?= $s->name ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Customer -->
            <div class="col-md-3">
                <div class="form-group">
                    <label>Customer</label>
                    <select name="customer_id" class="form-control select2" style="width:100%;">
                        <option value="">All</option>
                        <?php foreach ($customers as $c): ?>
                            <option value="<?= $c->id ?>" <?= ($filters['customer_id'] == $c->id) ? 'selected' : '' ?>>
                                <?= $c->name ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <!-- Period -->
            <div class="col-md-3">
                <div class="form-group">
                    <label>Period</label>
                    <select name="period" class="form-control">
                        <option value="today" <?= $filters['period']=='today'?'selected':''; ?>>Today</option>
                        <option value="month" <?= $filters['period']=='month'?'selected':''; ?>>This Month</option>
                        <option value="ytd" <?= $filters['period']=='ytd'?'selected':''; ?>>Year To Date</option>
                    </select>
                </div>
            </div>

            <!-- Group By -->
            <div class="col-md-3">
                <div class="form-group">
                    <label>Group By</label>
                    <select name="group_by" class="form-control">
                        <option value="invoice" <?= $filters['group_by']=='invoice'?'selected':''; ?>>By Invoice</option>
                        <option value="pharmacy" <?= $filters['group_by']=='pharmacy'?'selected':''; ?>>By Pharmacy</option>
                        <option value="supplier" <?= $filters['group_by']=='supplier'?'selected':''; ?>>By Supplier</option>
                        <option value="customer" <?= $filters['group_by']=='customer'?'selected':''; ?>>By Customer</option>
                        <option value="all" <?= $filters['group_by']=='all'?'selected':''; ?>>All</option>
                    </select>
                </div>
            </div>

            <!-- Generate Button -->
            <div class="col-md-3 d-flex align-items-end">
                <div class="form-group">
                    <button type="submit" name="submit" class="btn btn-primary w-100 mb-2">
                        Generate
                    </button>
                    <button type="submit" name="export_excel" value="1" class="btn btn-success w-100">
                        <i class="fa fa-file-excel-o"></i> Export to Excel
                    </button>
                </div>
            </div>
            
        </div>
        
        <?= form_close(); ?>

        <hr>

        <?php if (!empty($report_data)): ?>
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Pharmacy</th>
                            <th>Invoice #</th>
                            <th>Product</th>
                            <th>Sale Price</th>
                            <th>Cost Price</th>
                            <th>Profit Amt</th>
                            <th>Margin %</th>
                            <th>Supplier</th>
                            <th>Customer</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($report_data as $row): ?>
                            <tr>
                                <td><?= $this->sma->hrsd($row['sale_date']); ?></td>
                                <td><?= $row['pharmacy']; ?></td>
                                <td><?= $row['invoice_no']; ?></td>
                                <td><?= $row['product_name']; ?></td>
                                <td class="text-right"><?= number_format($row['sale_price'], 2); ?></td>
                                <td class="text-right"><?= number_format($row['cost_price'], 2); ?></td>
                                <td class="text-right"><?= number_format($row['profit_amount'], 2); ?></td>
                                <td class="text-right"><?= number_format($row['margin_percent'], 2); ?>%</td>
                                <td><?= $row['supplier_name']; ?></td>
                                <td><?= $row['customer_name']; ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <?php if ($this->input->post('submit')): ?>
                <div class="alert alert-info mt-3">No data found for selected filters.</div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>
