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
                                    <tr>
                                        <td colspan="2">Opening Balance</td>
                                        <td colspan="9">&nbsp;</td>
                                        <!--<td><?php echo $this->sma->formatMoney(($itemOpenings['total_opening_qty'] && $itemOpenings['cost_price'] > 0 ? $itemOpenings['cost_price'] / $itemOpenings['total_opening_qty'] : 0.0), 'none'); ?></td>-->
                                        
                                        <td><?php echo $this->sma->formatQuantity(($itemOpenings['total_opening_qty'] ? $itemOpenings['total_opening_qty'] : 0.00)); ?></td>
                                        <td><?php echo $this->sma->formatQuantity(($itemOpenings['total_opening_qty'] ? $itemOpenings['total_opening_qty'] : 0.00)); ?></td>
                                        <td><?php echo $this->sma->formatMoney(($itemOpenings['total_opening_qty'] && $itemOpenings['cost_price'] != 0 ? ($itemOpenings['cost_price'] * $itemOpenings['total_opening_qty']) : 0.00), 'none'); ?></td>

                                    </tr>

                                    <?php
                                    $count = 1;
                                    $balanceQantity = 0;
                                    $totalValueOfItem  = 0;
                                    $openingTotal = ($itemOpenings['cost_price'] * $itemOpenings['total_opening_qty']);

                                    foreach ($reportData as $rp) {

                                        if ($rp->trs_type == 'adjustment_increase' || $rp->trs_type == 'purchase' || $rp->trs_type == 'customer_return' || ($rp->trs_type == 'transfer_in' && $warehouse->id) ) {
                                            if($count == 1){
                                                $balanceQantity = $itemOpenings['total_opening_qty'] + $rp->quantity;
                                            }else{
                                                $balanceQantity += $rp->quantity;
                                            }

                                            if($count == 1){
                                                $totalValueOfItem = $openingTotal + ($rp->quantity * $rp->net_unit_cost);
                                            }else{
                                                $totalValueOfItem+= ($rp->quantity * $rp->net_unit_cost);
                                            }
                                        }

                                        if ($rp->trs_type == 'adjustment_decrease' || $rp->trs_type == 'sale' || $rp->trs_type == 'pharmacy sale' || $rp->trs_type == 'return_to_supplier' || ($rp->trs_type == 'transfer_out' && $warehouse->id)) {
                                            if($count == 1){
                                                $balanceQantity = $itemOpenings['total_opening_qty'] + $rp->quantity;
                                            }else{
                                                $balanceQantity += $rp->quantity;
                                            }

                                            if($count == 1){
                                                $totalValueOfItem = $openingTotal + ($rp->quantity * $rp->net_unit_cost);
                                            }else{
                                                $totalValueOfItem+= ($rp->quantity * $rp->net_unit_cost);
                                            }
                                        }
                                        

                                    ?>
                                        <tr>
                                            <td><?= $count; ?></td>
                                            <td><?= $rp->movement_date; ?></td>
                                            <td><?= $rp->reference_number != '' ? $rp->reference_number : '-'; ?></td>
                                            <td><?= $rp->avz_item_code; ?></td>
                                            <td><?= $rp->trs_type; ?></td>
                                            <td><?= $rp->counterparty; ?></td>
                                            <td><?= $rp->expiry; ?></td>
                                            <td><?= $rp->batch_no; ?></td>
                                            <td><?= $rp->net_unit_sale; ?></td>
                                            <td><?= $rp->net_unit_cost; ?></td>
                                            <td><?= $rp->real_unit_cost; ?></td>
                                            <td><?= $this->sma->formatQuantity($rp->quantity); ?></td>
                                            <td><?= $this->sma->formatQuantity($balanceQantity); ?></td>
                                            <td><?= $this->sma->formatMoney(($totalValueOfItem), 'none'); ?></td>
                                        </tr>
                                    <?php
                                        $count++;
                                    }


                                    ?>

                                    <tr>
                                        <td colspan="2">Closing</td>
                                        <td colspan="9">&nbsp;</td>
                                        <td><?php echo $this->sma->formatQuantity($balanceQantity); ?></td>
                                        <td><?php echo $this->sma->formatQuantity($balanceQantity); ?></td>
                                        <td><?php echo $this->sma->formatMoney($totalValueOfItem, 'none'); ?></td>

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