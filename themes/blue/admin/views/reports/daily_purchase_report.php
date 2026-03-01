<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.17.3/xlsx.full.min.js"></script>
<script>
    function exportTableToExcel(tableId, filename = 'table.xlsx') {
        const table = document.getElementById(tableId);
        const wb = XLSX.utils.table_to_book(table, { sheet: 'Sheet 1' });
        XLSX.writeFile(wb, filename);
    }
    function generatePDF(){
       $('.viewtype').val('pdf');  
       document.getElementById("searchForm").submit();
       $('.viewtype').val(''); 
    } 
    $(document).ready(function () {

    });
</script>
<?php if($viewtype=='pdf'){ ?>
    <link href="<?= $assets ?>styles/pdf/pdf.css" rel="stylesheet"> 
  <?php  } ?>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-users"></i><?= lang('Daily Purchase Report'); ?></h2>
        <?php  if($viewtype!='pdf'){?>
        <div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown"><a href="javascript:void(0);" onclick="exportTableToExcel('poTable', 'vat_purchase.xlsx')" id="xls" class="tip" title="<?= lang('download_xls') ?>"><i
                class="icon fa fa-file-excel-o"></i></a></li>
                <li class="dropdown"> <a href="javascript:void(0);" onclick="generatePDF()" id="pdf" class="tip" title="<?= lang('download_PDF') ?>"><i
                class="icon fa fa-file-pdf-o"></i></a></li>
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
            echo admin_form_open_multipart('reports/daily_purchase_report', $attrib)
        ?>
        <input type="hidden" name="viewtype" id="viewtype" class="viewtype" value="" > 
                <div class="row"> 
                <div class="col-lg-12"> 
                        
                        <div class="col-md-4">
                            <div class="form-group">
                            <?= lang('supplier', 'posupplier'); ?>
                            <?php
                            $selected_supplier_id[] = isset($supplier_id) ? $supplier_id : '';
                            $sp[''] = '';
                            foreach ($suppliers as $supplier) {
                                $sp[$supplier->id] = $supplier->company. ' ('. $supplier->name.')'.' - '.$supplier->sequence_code;
                            }
                            echo form_dropdown('supplier', $sp, $selected_supplier_id, 'id="supplier_id" class="form-control input-tip select" data-placeholder="' . lang('select') . ' ' . lang('supplier') . '" required="required" style="width:100%;" ', null); ?>
                            </div>
                        </div>

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
                        <table id="poTable"
                                class="table items table-striped table-bordered table-condensed table-hover sortable_table tbl_pdf tbl_vat_purchase">
                            <thead></thead>
                            <tbody style="text-align:center;">
                                <?php 
                                $count = 0;
                                $previous_purchase = 0;
                                foreach($daily_purchase_data as $row){
                                    $count++;
                                ?>

                                    <?php if($previous_purchase != $row->id){
                                        $previous_purchase = $row->id;
                                    ?>
                                    <tr>
                                        <th><?= lang('Sr.'); ?></th>
                                        <th><?= lang('Item Code'); ?></th>
                                        <th><?= lang('Item Name'); ?></th>
                                        <th><?= lang('Avz Code'); ?></th>
                                        <th><?= lang('Date'); ?></th>
                                        <th><?= lang('P.Id'); ?></th>
                                        <th><?= lang('Qty'); ?></th>
                                        <th><?= lang('Bonus'); ?></th>
                                        <th><?= lang('T.Qty'); ?></th>
                                        <th><?= lang('Pur.Price'); ?></th>
                                        <th><?= lang('Discount'); ?></th>
                                        <th><?= lang('Vat'); ?></th>
                                        <th><?= lang('Cost'); ?></th>
                                        <th><?= lang('T.Cost'); ?></th>
                                        <th><?= lang('Sale'); ?></th>
                                        <th><?= lang('T.Sale'); ?></th>
                                    </tr>
                                    <?php 
                                        if($previous_purchase == 0){
                                            $previous_purchase = $row->id;
                                        }
                                        
                                    } ?>
                                    <tr>
                                        
                                        <td><?= $count; ?></td>
                                        
                                        <td><?= $row->item_code; ?></td>
                                        <td><?= $row->product_name; ?></td>
                                        <td><?= $row->avz_item_code; ?></td>
                                        <td><?= $row->inv_date; ?></td>
                                        <td><?= $row->id; ?></td>
                                        <td><?= $row->quantity; ?></td>
                                        <td><?= $row->bonus; ?></td>
                                        <td><?= ($row->quantity + $row->bonus); ?></td>
                                        <td><?= $this->sma->formatNumber($row->purchase_price); ?></td>
                                        <td><?= $this->sma->formatNumber($row->item_discount); ?></td>
                                        <td><?= $this->sma->formatNumber($row->item_tax); ?></td>
                                        <td><?= $this->sma->formatNumber($row->net_unit_cost); ?></td>
                                        <td><?= $this->sma->formatNumber($row->net_unit_cost * ($row->quantity + $row->bonus)); ?></td>
                                        <td><?= $this->sma->formatNumber($row->sale_price); ?></td>
                                        <td><?= $this->sma->formatNumber($row->sale_price * ($row->quantity + $row->bonus)); ?></td>
                                    </tr>
                                    <?php
                                    }
                                ?>
                                
                            </tbody>
                            <tfoot>
                                
                            </tfoot>
                        </table>
                    </div>
                
            </div>

        </div>
    </div>
   
</div>
