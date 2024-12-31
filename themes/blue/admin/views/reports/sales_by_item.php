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
<?php if($viewtype=='pdf'){ ?>
    <link href="<?= $assets ?>styles/pdf/pdf.css" rel="stylesheet"> 
  <?php  } ?>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-users"></i><?= lang('Sales_by_Items'); ?></h2>
        <?php  if($viewtype!='pdf'){?>
        <div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown">
                    <a href="javascript:void(0);" onclick="exportTableToExcel('poTable', 'sales_by_items.xlsx')" id="xls" class="tip" title="<?= lang('download_xls') ?>"><i class="icon fa fa-file-excel-o"></i></a>
                </li>
                <!-- <li class="dropdown"> <a href="javascript:void(0);" onclick="generatePDF()" id="pdf" class="tip" title="<?= lang('download_PDF') ?>"><i class="icon fa fa-file-pdf-o"></i></a></li> -->
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
            echo admin_form_open_multipart('reports/sales_by_item', $attrib)
        ?> <input type="hidden" name="viewtype" id="viewtype" class="viewtype" value="" > 
                <div class="row">
                    <div class="col-lg-12">
                       
                        <div class="col-md-4">
                            <div class="form-group">
                                <?= lang('From Date', 'podate'); ?>
                                <?php echo form_input('from_date', ($start_date ?? ''), 'class="form-control input-tip date" id="fromdate"'); ?>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <?= lang('To Date', 'podate'); ?>
                                <?php echo form_input('to_date', ($end_date ?? ''), 'class="form-control input-tip date" id="todate"'); ?>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                            <?= lang('Pharmacy', 'popharmacy'); ?>
                            <?php
                            $selected_warehouse_id[] = isset($warehouse) ? $warehouse : '';
                            $dp['all'] = 'All';
                            foreach ($warehouses as $warehouse) {
                                $dp[$warehouse->id] = $warehouse->name;
                            }
                            echo form_dropdown('pharmacy', $dp, $selected_warehouse_id, 'id="warehouse_id" class="form-control input-tip select" data-placeholder="' . lang('select') . ' ' . lang('pharmacy') . '" required="required" style="width:100%;" ', null); ?>
                            </div>
                        </div>


                        <div class="col-md-4">
                            <div class="from-group">
                                <button type="submit" style="margin-top: 28px;" class="btn btn-primary" id="load_report"><?= lang('Load Report') ?></button>
                            </div>
                        </div>
                            
                    </div>
                </div>
                <?php echo form_close(); 
                } ?>
                <hr />
                <div class="row">
                    <div class="controls table-controls" style="font-size: 12px !important;">
                        <table id="poTable"
                                class="table items table-striped table-bordered table-condensed table-hover sortable_table tbl_pdf">
                            <thead>
                            <tr>
                                <th><?= lang('S.NO'); ?></th>
                                <th><?= lang('Item No'); ?></th>
                                <th><?= lang('Avz Code'); ?></th>
                                <th><?= lang('Item Name'); ?></th>
                                <th><?= lang('Date'); ?></th>
                                <th><?= lang('Inv. No'); ?></th>
                                <th><?= lang('Qty'); ?></th>
                                <th><?= lang('Cost'); ?></th>
                                <th><?= lang('Total Cost'); ?></th>
                                <th><?= lang('Sale'); ?></th>
                                <th><?= lang('Total Sale'); ?></th>
                                <th><?= lang('Total Discount'); ?></th>
                                <th><?= lang('Sale After Discount'); ?></th>
                                <th><?= lang('VAT'); ?></th>
                                <th><?= lang('Net Sale'); ?></th>
                                <th><?= lang('Customer Name'); ?></th>
                            </tr>
                            </thead>
                            <tbody style="text-align:center;">
                                <?php
                                    $count = 0;
                                    $grand_sales = 0;
                                    $grand_returns = 0;
                                    $grand_net_total = 0;
                                    foreach ($response_data['sales'] as $data){
                                        $count++;
                                        ?>
                                            <tr>
                                                <td><?= $count; ?></td>
                                                <td><?= $data->item_code; ?></td>
                                                <td><?= $data->avz_item_code; ?></td>
                                                <td><?= $data->name; ?></td>
                                                <td><?= $data->date; ?></td>
                                                <td><?= $data->id; ?></td>
                                                <td><?= $data->quantity; ?></td>
                                                <td><?= $data->cost_price;?> </td>
                                                <td><?= $data->cost_price * $data->quantity; ?></td>
                                                <td><?= $data->sale_price ; ?></td>
                                                <td><?= $data->total_sale; ?></td>
                                                <td><?= round($data->item_discount, 5); ?></td>
                                                <td><?= $data->totalbeforevat; ?></td>
                                                <td><?= $data->tax; ?></td>
                                                <td><?= $data->main_net; ?></td>
                                                <td><?= $data->customer; ?></td>
                                                
                                               
                                            </tr>
                                        <?php
                                    }
                                ?>
                              
                            </tbody>
                            <tfoot>
                            <tr>
                                    <td colspan="6"><strong>Totals: </strong></td>
                                    <td colspan="1"><?=$response_data['grand']->grand_quantity;?></td>
                                    <td colspan="1"></td>
                                    <td colspan="1"><strong><?php echo $response_data['grand']->grand_cost; ?></strong></td>
                                    <td colspan="1"></td>
                                    <td colspan="1"><?=$response_data['grand']->grand_sale;?></td>
                                    <td colspan="1"><?=$response_data['grand']->grand_discount;?></td>
                                    <td colspan="1"><?=$response_data['grand']->grand_beforvate;?></td>
                                    <td colspan="1"><?=$response_data['grand']->grand_vat;?></td>
                                    <td colspan="1"><?=$response_data['grand']->grand_main_net;?></td>
                                    
                                </tr>
                               
                                
                            </tfoot>
                        </table>
                    </div> 
            </div> 
        </div>
    </div> 
</div>
