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
        <h2 class="blue"><i class="fa-fw fa fa-users"></i><?= lang('Sales_by_Category'); ?></h2>
        <?php  if($viewtype!='pdf'){?>
        <div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown">
                    <a href="javascript:void(0);" onclick="exportTableToExcel('poTable', 'sales_by_categories.xlsx')" id="xls" class="tip" title="<?= lang('download_xls') ?>"><i class="icon fa fa-file-excel-o"></i></a>
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
            echo admin_form_open_multipart('reports/sales_by_category', $attrib)
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
                                <th><?= lang('Category No'); ?></th>
                                <th><?= lang('Category Name'); ?></th>
                                <th><?= lang('Sales'); ?></th>
                                <th><?= lang('Sales %'); ?></th>
                                <th><?= lang('Vat'); ?></th>
                                <th><?= lang('Returns'); ?></th>
                                <th><?= lang('Returns %'); ?></th>
                                <th><?= lang('VAT'); ?></th>
                                <th><?= lang('Net'); ?></th>
                                <th><?= lang('Net %'); ?></th>
                            </tr>
                            </thead>
                            <tbody style="text-align:center;">
                                <?php
                                    $count = 0;
                                    $grand_sales = 0;
                                    $grand_returns = 0;
                                    $grand_net_total = 0;
                                    $grand_vat = 0;
                                    foreach ($sales_data['sales'] as $key => $data){
                                       
                                        $count ++ ; 
                                        
                                        $grand_sales += $data->total_sales ;
                                        $grand_returns += 0;
                                      
                                        $total_returns = 0;
                                        $total_returns_percentage = 0;
                                        $total_returns_vat = 0;
                                        $total_return_main_net = 0;
                                        if(isset($sales_data['returns'][$key])) {
                                            $return_data = $sales_data['returns'][$key] ;
                                            $total_returns = $return_data->total_sales;
                                            $total_returns_percentage = $return_data->sales_percentage;
                                            $total_return_vat = $return_data->total_vat;
                                            $total_return_main_net = $return_data->total_main_net;
                                        }
                                        $total_main_net = $data->total_main_net - $total_return_main_net;
                                        $grand_net_total += $total_main_net;
                                        $grand_vat += $data->total_vat;

                                        ?>
                                            <tr>
                                                
                                                <td><?= $data->category_code; ?></td>
                                                <td><?= $data->category_name; ?></td>
                                                <td><?= $data->total_sales; ?></td>
                                                <td><?= $data->sales_percentage; ?></td>
                                                <td><?= $data->total_vat; ?></td>
                                                <td><?= $total_returns; ?></td>
                                                <td><?= $total_returns_percentage;?> </td>
                                                <td><?= $total_return_vat; ?></td>
                                                <td><?= $total_main_net ; ?></td>
                                                <td><?= $data->main_net_percentage; ?></td>
                                                
                                               
                                            </tr>
                                        <?php
                                    }
                                ?>
                                <tr>
                                    <td colspan="2"><strong>Totals: </strong></td>
                                    <td colspan="1"><strong><?= $this->sma->formatNumber($grand_sales); ?></strong></td>
                                    <td colspan="1"></td>
                                    <td colspan="1"><strong><?= $grand_vat;?></strong></td>
                                    <td colspan="1"><strong></strong></td>
                                    <td colspan="1"></td>
                                    <td colspan="1"></td>
                                    <td colspan="1"><strong><?= $this->sma->formatNumber($grand_net_total); ?></strong></td>
                                    <td colspan="1"></td>
                                </tr>
                                 
                                <tr>
                                    <td colspan="8"><strong>Discount on Overall Sales By Value: </strong></td>
                                    <td colspan="1"><?=$grand_sale_discount = ($response_data['grand_sales_discount']->grand_sales_discount - $response_data['grand']->grand_discount);?></td>
                                    <td colspan="1"></td>
                                </tr>
                                <tr>
                                    <td colspan="8"><strong>Total Net Sale: </strong></td>
                                    <td colspan="1"><?=$grand_net_total - $grand_sale_discount;?></td>
                                    <td colspan="1"></td>
                                </tr>
    

                            </tbody>
                            <tfoot></tfoot>
                        </table>
                    </div> 
            </div> 
        </div>
    </div> 
</div>
