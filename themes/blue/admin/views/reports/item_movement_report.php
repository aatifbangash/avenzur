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
    function generatePDF(){
       $('.viewtype').val('pdf');  
       document.getElementById("searchForm").submit();
       $('.viewtype').val(''); 
    }
    $(document).ready(function() {

    });
</script>
<script>
    $(document).ready(function() {

        $("#warehouse").select2().select2('val', <?= $warehouse; ?>);
        $('#warehouse').select2().trigger('change');
    });
</script>
<?php if($viewtype=='pdf'){ ?>
    <link href="<?= $assets ?>styles/pdf/pdf.css" rel="stylesheet"> 
  <?php  } ?>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-users"></i><?= lang('Item Movement Report'); ?></h2>
        <?php  if($viewtype!='pdf'){?>
        <div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown"><a href="javascript:void(0);" onclick="exportTableToExcel('poTable', 'item_movement_report.xlsx')" id="xls" class="tip" title="<?= lang('download_xls') ?>"><i class="icon fa fa-file-excel-o"></i></a></li>
                <li class="dropdown"> <a href="javascript:void(0);" onclick="generatePDF()" id="pdf" class="tip" title="<?= lang('download_PDF') ?>"><i class="icon fa fa-file-pdf-o"></i></a></li>
            </ul>
        </div>
        <?php } ?>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
                <?php
                if($viewtype!='pdf')
                {
                    $attrib = ['data-toggle' => 'validator', 'role' => 'form','id' => 'searchForm'];
                    echo admin_form_open_multipart('reports/item_movement_report', $attrib)
                    ?>
                    <input type="hidden" name="viewtype" id="viewtype" class="viewtype" value="" >
            
                <div class="row">

                    <div class="col-lg-12">

                        <div class="col-md-4">
                            <div class="form-group">
                                <?= lang('Product', 'product'); ?>
                                <?php // echo form_dropdown('product', $allProducts, set_value('product',$product),array('class' => 'form-control', 'id'=>'product'));
                                ?>
                                <?php echo form_input('sgproduct', (isset($_POST['sgproduct']) ? $_POST['sgproduct'] : ''), 'class="form-control" id="suggest_product2" data-bv-notempty="true"'); ?>
                                <input type="hidden" name="product" value="<?= isset($_POST['product']) ? $_POST['product'] : 0 ?>" id="report_product_id2" />
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <?= lang('Type', 'Type'); ?>
                                <?php echo form_dropdown('filterOnType', $filterOnTypeArr, set_value('filterOnType', $_POST['filterOnType']), array('class' => 'form-control', 'data-placeholder' => "-- Select Type --", 'id' => 'filterOnType'),  array('none')); ?>

                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <?= lang('Document Number', 'document_number'); ?>
                                <?php echo form_input('document_number', ($document_number ?? ''), 'class="form-control input-tip" '); ?>
                            </div>
                        </div>

                    </div>

                    <div class="col-lg-12">
                        <div class="col-md-4">
                            <div class="form-group">
                                <?= lang('Store', 'warehouse'); ?>
                                <?php
                                $optionsWarehouse[0] = 'Select';
                                if (!empty($warehouses)) {
                                    foreach ($warehouses as $warehouse) {
                                        $optionsWarehouse[$warehouse->id] = $warehouse->name;
                                    }
                                }

                                ?>
                                <?php echo form_dropdown('warehouse', $optionsWarehouse, set_value('warehouse'), array('class' => 'form-control disable-select'), array('none')); ?>

                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <?= lang('From Date', 'fromdate'); ?>
                                <?php echo form_input('from_date', ($start_date ?? ''), 'class="form-control input-tip date" id="fromdate"'); ?>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <?= lang('To Date', 'todate'); ?>
                                <?php echo form_input('to_date', ($end_date ?? ''), 'class="form-control input-tip date" id="todate"'); ?>
                            </div>
                        </div>

                        

                    </div>

                    <div class="col-lg-12">
                        <div class="col-md-4">
                            <div class="from-group">
                                <button type="submit" style="margin-top: 28px;" class="btn btn-primary" id="load_report"><?= lang('Load Report') ?></button>
                            </div>
                        </div>
                    </div>
                </div>
                <hr />
                <?php echo form_close(); 
                } ?>
                <div class="row">
                    <div class="controls table-controls" style="font-size: 12px !important;">
                        <table id="poTable" class="table items table-striped table-bordered table-condensed table-hover tbl_order">
                            <thead>
                                <tr>
                                    <th><?= lang('SN'); ?></th>
                                    <th><?= lang('Date'); ?></th>
                                    <th><?= lang('Document No'); ?></th>
                                    <th><?= lang('Item Code'); ?></th>
                                    <th><?= lang('Type'); ?></th>
                                    <th><?= lang('Name Of'); ?></th>
                                    <th><?= lang('Expire Date'); ?></th>
                                    <th><?= lang('Batch No.'); ?></th>
                                    <th><?= lang('Sale Price'); ?></th>
                                    <th><?= lang('Purchase Price'); ?></th>
                                    <th><?= lang('Cost Price'); ?></th>
                                    <th><?= lang('Quantity'); ?></th>
                                    <th><?= lang('Item balance quantity'); ?></th>
                                    <th><?= lang('Value of item current balance'); ?></th>
                                </tr>
                            </thead>

                            <?php if ($reportData) { ?>
                                <tbody style="text-align:center;">

                                <?php
                                /* =========================
                                INITIAL TOTAL VARIABLES
                                ========================= */
                                $totalPurchased = 0;
                                $totalSold = 0;
                                $totalTransferIn = 0;
                                $totalTransferOut = 0;
                                $totalCustomerReturn = 0;
                                $totalSupplierReturn = 0;
                                $totalAdjustmentPlus = 0;
                                $totalAdjustmentMinus = 0;

                                $count = 1;
                                $balanceQantity = $itemOpenings['total_opening_qty'] ?? 0;
                                $totalValueOfItem = $itemOpenings['total_opening_value'] ?? 0;
                                ?>

                                <tr>
                                    <td colspan="2"><strong>Opening Balance</strong></td>
                                    <td colspan="9"></td>
                                    <td><?= $this->sma->formatQuantity($balanceQantity); ?></td>
                                    <td><?= $this->sma->formatQuantity($balanceQantity); ?></td>
                                    <td><?= $this->sma->formatMoney($totalValueOfItem, 'none'); ?></td>
                                </tr>

                                <?php foreach ($reportData as $rp) {

                                    /* =========================
                                    CLASSIFY MOVEMENTS
                                    ========================= */
                                    switch ($rp->trs_type) {

                                        case 'purchase':
                                            $totalPurchased += $rp->quantity;
                                            break;

                                        case 'sale':
                                        case 'pharmacy sale':
                                            $totalSold += abs($rp->quantity);
                                            break;

                                        case 'transfer_in':
                                            $totalTransferIn += $rp->quantity;
                                            break;

                                        case 'transfer_out':
                                            $totalTransferOut += abs($rp->quantity);
                                            break;

                                        case 'customer_return':
                                            $totalCustomerReturn += $rp->quantity;
                                            break;

                                        case 'return_to_supplier':
                                            $totalSupplierReturn += abs($rp->quantity);
                                            break;

                                        case 'adjustment_increase':
                                            $totalAdjustmentPlus += $rp->quantity;
                                            break;

                                        case 'adjustment_decrease':
                                            $totalAdjustmentMinus += abs($rp->quantity);
                                            break;
                                    }

                                    /* =========================
                                    RUNNING BALANCE
                                    ========================= */
                                    $balanceQantity += $rp->quantity;
                                    $totalValueOfItem += ($rp->quantity * $rp->net_unit_cost);

                                    $link = '';
                                    if($rp->trs_type == 'sale'){
                                       $link = admin_url('sales?sid='.$rp->reference_number); 
                                    }else if($rp->trs_type == 'purchase'){
                                       $link = admin_url('purchases?pid='.$rp->reference_number);
                                    }else if($rp->trs_type == 'transfer_in' || $rp->trs_type == 'transfer_out'){
                                       $link = admin_url('transfers?tid='.$rp->reference_number);
                                    }else if($rp->trs_type == 'customer_return'){
                                       $link = admin_url('returns');  
                                    }else if($rp->trs_type == 'return_to_supplier'){
                                       $link = admin_url('returns_supplier');
                                    }
                                ?>

                                <tr>
                                    <td><?= $count; ?></td>
                                    <td><?= $rp->movement_date; ?></td>
                                    <td><a target="_blank" href="<?= $link; ?>"><?= $rp->reference_number ?: '-'; ?></a></td>
                                    <td><?= $rp->avz_item_code; ?></td>
                                    <td><?= $rp->trs_type; ?></td>
                                    <td><?= $rp->counterparty; ?></td>
                                    <td><?= $rp->expiry; ?></td>
                                    <td><?= $rp->batch_no; ?></td>
                                    <td><?= $this->sma->formatMoney($rp->real_unit_sale, 'none'); ?></td>
                                    <td><?= $rp->real_unit_cost; ?></td>
                                    <td><?= $rp->net_unit_cost; ?></td>
                                    <td><?= $this->sma->formatQuantity($rp->quantity); ?></td>
                                    <td><?= $this->sma->formatQuantity($balanceQantity); ?></td>
                                    <td><?= $this->sma->formatMoney($totalValueOfItem, 'none'); ?></td>
                                </tr>

                                <?php $count++; } ?>

                                <!-- =========================
                                    SUMMARY TOTALS SECTION
                                ========================= -->
                                <tr class="info">
                                    <td colspan="14"><strong>Movement Summary</strong></td>
                                </tr>

                                <tr>
                                    <td colspan="4">Total Purchased</td>
                                    <td colspan="3"><?= $this->sma->formatQuantity($totalPurchased); ?></td>

                                    <td colspan="4">Total Sold</td>
                                    <td colspan="3"><?= $this->sma->formatQuantity($totalSold); ?></td>
                                </tr>

                                <tr>
                                    <td colspan="4">Transfer In</td>
                                    <td colspan="3"><?= $this->sma->formatQuantity($totalTransferIn); ?></td>

                                    <td colspan="4">Transfer Out</td>
                                    <td colspan="3"><?= $this->sma->formatQuantity($totalTransferOut); ?></td>
                                </tr>

                                <tr>
                                    <td colspan="4">Customer Returns</td>
                                    <td colspan="3"><?= $this->sma->formatQuantity($totalCustomerReturn); ?></td>

                                    <td colspan="4">Supplier Returns</td>
                                    <td colspan="3"><?= $this->sma->formatQuantity($totalSupplierReturn); ?></td>
                                </tr>

                                <tr>
                                    <td colspan="4">Adjustments (+)</td>
                                    <td colspan="3"><?= $this->sma->formatQuantity($totalAdjustmentPlus); ?></td>

                                    <td colspan="4">Adjustments (−)</td>
                                    <td colspan="3"><?= $this->sma->formatQuantity($totalAdjustmentMinus); ?></td>
                                </tr>

                                <tr class="success">
                                    <td colspan="2"><strong>Closing Balance</strong></td>
                                    <td colspan="9"></td>
                                    <td><strong><?= $this->sma->formatQuantity($balanceQantity); ?></strong></td>
                                    <td><strong><?= $this->sma->formatQuantity($balanceQantity); ?></strong></td>
                                    <td><strong><?= $this->sma->formatMoney($totalValueOfItem, 'none'); ?></strong></td>
                                </tr>

                                </tbody>

                            <?php } ?>
                            <tfoot></tfoot>
                        </table>
                    </div>

                </div>

            </div>
        </div> 
    </div>

    <?php
    // $productId = ($_POST['product'] ? $_POST['product'] : 0);
    // $type = ($_POST['filterOnType'] ? $_POST['filterOnType'] : 'all');
    // $startDate = ($_POST['from_date'] ? trim($this->sma->fld($_POST['from_date'])) : null);
    // $endDate = ($_POST['to_date'] ? trim($this->sma->fld($_POST['to_date'])) : null);
    // 
    ?>

    <script type="text/javascript" src="<?= $assets ?>js/html2canvas.min.js"></script>
    <script type="text/javascript">
        // $(document).ready(function() {
        //     $('#xls').click(function(event) {
        //         var prod = $('#report_product_id2').val();
        //         var fromdate = $('#fromdate').val();
        //         var todate = $('#todate').val();
        //         if (prod && fromdate && todate) {
        //             event.preventDefault();
        //             window.location.href = "<?= admin_url("reports/item_movement_report_xls/$productId/$type/$startDate/$endDate/xls") ?>";
        //             return false;
        //         } else {
        //             return false;
        //         }
        //     });
        //     $('#image').click(function(event) {
        //         event.preventDefault();
        //         html2canvas($('.box'), {
        //             onrendered: function(canvas) {
        //                 openImg(canvas.toDataURL());
        //             }
        //         });
        //         return false;
        //     });
        // });
    </script>