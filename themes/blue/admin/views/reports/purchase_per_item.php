<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.17.3/xlsx.full.min.js"></script>
<script>
    function exportTableToExcel(tableId, filename = 'table.xlsx') {
        const table = document.getElementById(tableId);
        const wb = XLSX.utils.table_to_book(table, {
            sheet: 'Sheet 1'
        });
        XLSX.writeFile(wb, filename);
    }

    // ── Pagination ────────────────────────────────────────────────────
    var PPI_PAGE_SIZE = 100;
    var PPI_current   = 1;

    function ppiGetRows() {
        return $('#purchaseItemTable tbody tr.ppi-data-row');
    }

    function ppiRender() {
        var rows  = ppiGetRows();
        var total = rows.length;
        var pages = Math.max(1, Math.ceil(total / PPI_PAGE_SIZE));
        if (PPI_current > pages) PPI_current = pages;

        rows.hide();
        var start = (PPI_current - 1) * PPI_PAGE_SIZE;
        rows.slice(start, start + PPI_PAGE_SIZE).show();

        // Info
        var from = total === 0 ? 0 : start + 1;
        var to   = Math.min(start + PPI_PAGE_SIZE, total);
        $('#ppi-page-info').text('Showing ' + from + '–' + to + ' of ' + total + ' rows');

        // Buttons
        $('#ppi-prev').prop('disabled', PPI_current <= 1);
        $('#ppi-next').prop('disabled', PPI_current >= pages);
        $('#ppi-page-num').text('Page ' + PPI_current + ' of ' + pages);
    }

    $(document).ready(function () {
        if ($('.ppi-data-row').length) { ppiRender(); }

        $(document).on('click', '#ppi-prev', function () {
            if (PPI_current > 1) { PPI_current--; ppiRender(); }
        });
        $(document).on('click', '#ppi-next', function () {
            var pages = Math.max(1, Math.ceil(ppiGetRows().length / PPI_PAGE_SIZE));
            if (PPI_current < pages) { PPI_current++; ppiRender(); }
        });
    });
</script>

<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-file-text"></i><?= lang('Purchase Per Item'); ?></h2>
        <div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown">
                    <a href="javascript:void(0);" onclick="exportTableToExcel('purchaseItemTable', 'purchase_per_item.xlsx')" id="xls" class="tip" title="<?= lang('download_xls') ?>"><i class="icon fa fa-file-excel-o"></i></a>
                </li>
            </ul>
        </div>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
                <?php
                $attrib = ['data-toggle' => 'validator', 'role' => 'form', 'id' => 'searchForm', 'method' => 'get'];
                echo admin_form_open_multipart('reports/purchase_per_item', $attrib)
                ?>
                <div class="row">
                    <div class="col-lg-12">
                        <div class="col-md-3">
                            <div class="form-group">
                                <?= lang('start_date', 'start_date'); ?>
                                <?php echo form_input('start_date', ($_GET['start_date'] ?? ''), 'class="form-control input-tip date" id="start_date"'); ?>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="form-group">
                                <?= lang('end_date', 'end_date'); ?>
                                <?php echo form_input('end_date', ($_GET['end_date'] ?? ''), 'class="form-control input-tip date" id="end_date"'); ?>
                            </div>
                        </div>

                        <div class="col-md-2">
                            <div class="form-group">
                                <?= lang('Purchase Ref', 'purchase_ref'); ?>
                                <?php echo form_input('purchase_ref', ($_GET['purchase_ref'] ?? ''), 'class="form-control" id="purchase_ref" placeholder="' . lang('Purchase Ref') . '"'); ?>
                            </div>
                        </div>

                        <div class="col-md-2">
                            <div class="form-group">
                                <?= lang('Supplier', 'supplier_filter'); ?>
                                <select name="supplier" id="supplier_filter" class="form-control select2" data-placeholder="<?= lang('select') . ' ' . lang('Supplier') ?>" style="width:100%;">
                                    <option value=""><?= lang('all') ?></option>
                                    <?php foreach ($suppliers as $supplier_item): ?>
                                        <option value="<?= $supplier_item->id ?>" <?= (isset($_GET['supplier']) && $_GET['supplier'] == $supplier_item->id) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($supplier_item->name) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-2">
                            <div class="form-group">
                                <?= lang('Product', 'product'); ?>
                                <?php // echo form_dropdown('product', $allProducts, set_value('product',$product),array('class' => 'form-control', 'id'=>'product'));
                                ?>
                                <?php echo form_input('sgproduct', (isset($_GET['sgproduct']) ? $_GET['sgproduct'] : (isset($sgproduct) ? $sgproduct : '')), 'class="form-control" id="suggest_product2" data-bv-notempty="true"'); ?>
                                <input type="hidden" name="product" value="<?= isset($_POST['product']) ? $_POST['product'] : 0 ?>" id="report_product_id2" />
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-12">
                        <div class="col-md-3">
                            <div class="form-group">
                                <button type="submit" style="margin-top: 0px;" class="btn btn-primary" id="load_report"><?= lang('Load Report') ?></button>
                            </div>
                        </div>
                    </div>
                </div>
                <?php echo form_close(); ?>

                <hr />

                <div class="row">
                    <div class="col-lg-12">
                        <div class="table-responsive">
                            <table id="purchaseItemTable" class="table table-bordered table-striped table-condensed table-hover">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th><?= lang('Type'); ?></th>
                                        <th><?= lang('Date'); ?></th>
                                        <th><?= lang('Purchase Ref'); ?></th>
                                        <th><?= lang('Return Ref'); ?></th>
                                        <th><?= lang('Agent'); ?></th>
                                        <th><?= lang('Supplier No'); ?></th>
                                        <th><?= lang('Supplier Name'); ?></th>
                                        <th><?= lang('Item No'); ?></th>
                                        <th><?= lang('Item Name'); ?></th>
                                        <th><?= lang('QTY'); ?></th>
                                        <th><?= lang('Bonus'); ?></th>
                                        <th><?= lang('Discount %'); ?></th>
                                        <th><?= lang('Total Discount'); ?></th>
                                        <th><?= lang('Deal Disc %'); ?></th>
                                        <th><?= lang('Deal Disc Value'); ?></th>
                                        <th><?= lang('Unit Cost'); ?></th>
                                        <th><?= lang('Purchase'); ?></th>
                                        <th><?= lang('Public Price'); ?></th>
                                        <th><?= lang('Vat'); ?></th>
                                        <th><?= lang('Payable'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    if (isset($purchase_data) && !empty($purchase_data)) {
                                        $count = 0;
                                        $grand_totals = [
                                            'qty'                  => 0,
                                            'bonus'                => 0,
                                            'total_discount_value' => 0,
                                            'deal_discount_value'  => 0,
                                            'purchase'             => 0,
                                            'vat'                  => 0,
                                            'payable'              => 0,
                                        ];

                                        foreach ($purchase_data as $data) {
                                            $count++;
                                            $grand_totals['qty']                  += $data->qty;
                                            $grand_totals['bonus']                += $data->bonus;
                                            $grand_totals['total_discount_value'] += $data->total_discount_value;
                                            $grand_totals['deal_discount_value']  += $data->deal_discount_value;
                                            $grand_totals['purchase']             += $data->purchase;
                                            $grand_totals['vat']                  += $data->vat;
                                            $grand_totals['payable']              += $data->payable;

                                            $row_class = ($data->type == 'Return') ? 'style="background-color: #ffe6e6;"' : '';
                                            ?>
                                            <tr class="ppi-data-row" <?= $row_class ?>>
                                                <td><?= $count ?></td>
                                                <td><?= $data->type ?></td>
                                                <td><?= $data->date ?></td>
                                                <td><?= $data->purchase_ref ?></td>
                                                <td><?= $data->return_ref ?></td>
                                                <td><?= $data->agent ?></td>
                                                <td><?= $data->supplier_no ?></td>
                                                <td><?= $data->supplier_name ?></td>
                                                <td><?= $data->item_no ?></td>
                                                <td><?= $data->item_name ?></td>
                                                <td class="text-right"><?= $this->sma->formatQuantity($data->qty) ?></td>
                                                <td class="text-right"><?= $data->bonus ?></td>
                                                <td class="text-right"><?= isset($data->discount_percent) ? number_format($data->discount_percent, 2) . '%' : '0.00%' ?></td>
                                                <td class="text-right"><?= number_format($data->total_discount_value, 2) ?></td>
                                                <td class="text-right"><?= number_format($data->deal_discount_percent, 2) ?>%</td>
                                                <td class="text-right"><?= number_format($data->deal_discount_value, 2) ?></td>
                                                <td class="text-right"><?= number_format($data->unit_cost, 2) ?></td>
                                                <td class="text-right"><?= number_format($data->purchase, 2) ?></td>
                                                <td class="text-right"><?= number_format($data->public_price, 2) ?></td>
                                                <td class="text-right"><?= number_format($data->vat, 2) ?></td>
                                                <td class="text-right"><?= number_format($data->payable, 2) ?></td>
                                            </tr>
                                        <?php
                                        }
                                        ?>
                                        <tr style="font-weight:bold; background-color:#f5f5f5;">
                                            <td colspan="10" class="text-right"><?= lang('Total') ?></td>
                                            <td class="text-right"><?= $this->sma->formatQuantity($grand_totals['qty']) ?></td>
                                            <td class="text-right"><?= $this->sma->formatQuantity($grand_totals['bonus']) ?></td>
                                            <td></td>
                                            <td class="text-right"><?= number_format($grand_totals['total_discount_value'], 2) ?></td>
                                            <td></td>
                                            <td class="text-right"><?= number_format($grand_totals['deal_discount_value'], 2) ?></td>
                                            <td></td>
                                            <td class="text-right"><?= number_format($grand_totals['purchase'], 2) ?></td>
                                            <td></td>
                                            <td class="text-right"><?= number_format($grand_totals['vat'], 2) ?></td>
                                            <td class="text-right"><?= number_format($grand_totals['payable'], 2) ?></td>
                                        </tr>
                                    <?php
                                    } else {
                                        ?>
                                        <tr>
                                            <td colspan="21" class="text-center"><?= lang('No records found. Please select filters and click Load Report.'); ?></td>
                                        </tr>
                                    <?php
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination controls -->
                        <?php if (isset($purchase_data) && !empty($purchase_data)): ?>
                        <div style="margin-top:10px; display:flex; align-items:center; gap:10px; flex-wrap:wrap;">
                            <button id="ppi-prev" class="btn btn-default btn-sm" type="button">
                                <i class="fa fa-chevron-left"></i> Prev
                            </button>
                            <span id="ppi-page-num" style="font-size:13px;"></span>
                            <button id="ppi-next" class="btn btn-default btn-sm" type="button">
                                Next <i class="fa fa-chevron-right"></i>
                            </button>
                            <span id="ppi-page-info" class="text-muted" style="font-size:12px; margin-left:8px;"></span>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
