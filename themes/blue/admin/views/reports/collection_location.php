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
        <h2 class="blue"><i class="fa-fw fa fa-users"></i><?= lang('Collections_by_Location'); ?></h2>
        <?php  if($viewtype!='pdf'){?>
        <div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown">
                    <a href="javascript:void(0);" onclick="exportTableToExcel('poTable', 'collection.xlsx')" id="xls" class="tip" title="<?= lang('download_xls') ?>"><i class="icon fa fa-file-excel-o"></i></a>
                </li>
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
            $attrib = ['data-toggle' => 'validator', 'role' => 'form','id' => 'searchForm', 'method' => 'get'];
            echo admin_form_open_multipart('reports/collections_by_location', $attrib)
        ?> <input type="hidden" name="viewtype" id="viewtype" class="viewtype" value="" > 
                <div class="row">
                    <div class="col-lg-12">
                       
                        <div class="col-md-4">
                            <div class="form-group">
                                <?= lang('From Date', 'podate'); ?>
                                <?php echo form_input('from_date', ($_GET['from_date'] ?? ''), 'class="form-control input-tip date" id="fromdate"'); ?>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <?= lang('To Date', 'podate'); ?>
                                <?php echo form_input('to_date', ($_GET['to_date'] ?? ''), 'class="form-control input-tip date" id="todate"'); ?>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                            <?= lang('Location', 'popharmacy'); ?>
                            <?php
                            $dp[''] = '';
                            foreach ($warehouses as $warehouse) {
                                $dp[$warehouse->id] = $warehouse->name;
                            }
                            echo form_dropdown('pharmacy', $dp, ($_GET['pharmacy'] ?? ''), 'id="warehouse_id" class="form-control input-tip select" data-placeholder="' . lang('select') . ' ' . lang('pharmacy') . '" style="width:100%;" ', null); ?>
                            </div>
                        </div>

                         <div class="col-md-3">
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
                                <th>#</th>
                                <th><?= lang('Type'); ?></th>
                                <th><?= lang('Area'); ?></th>
                                <th><?= lang('Sales Man'); ?></th>
                                <th><?= lang('Customer No.'); ?></th>
                                <th><?= lang('Customer Name'); ?></th>
                                <th><?= lang('Invoice #'); ?></th>
                                <th><?= lang('Invoice Date'); ?></th>
                                <th><?= lang('Invoice Amount'); ?></th>
                                <th><?= lang('Receipt #'); ?></th>
                                <th><?= lang('Collected Amount'); ?></th>
                                <th><?= lang('Collection Date'); ?></th>
                                <th><?= lang('Collection Days'); ?></th>
                                <th><?= lang('Cash Account'); ?></th>
                            </tr>
                            </thead>
                            <tbody style="text-align:center;">
                                <?php
                                    $count = 0;
                                    
                                    $grand_total_sale = 0;
                                    $grand_total_payment = 0;
                                    foreach ($collections_data as $data){
                                        $count ++ ;
                                        $grand_total_sale += $data->grand_total;
                                        $grand_total_payment += $data->amount;
                                        /*$net_cash = ($data->total_cash - $data->total_returns) ;
                                        $total = $data->total_cash + $data->total_credit_card ;
                                        $net_total = $net_cash + $data->total_credit_card;

                                        
                                        $grand_cash += $data->total_cash;
                                        $grand_returns += $data->total_returns;
                                        $grand_net_cash += $net_cash;
                                        $grand_discount += $data->total_discount;
                                        $grand_total += $total;
                                        $grand_net_total += $net_total;
                                        $grand_total_credit_card += $data->total_credit_card;*/

                                        ?>
                                            <tr>
                                                <td><?= $count; ?></td>
                                                <td>Collection</td>
                                                <td><?= $data->area; ?></td>
                                                <td><?= $data->sales_agent; ?></td>
                                                <td><?= $data->customer_id; ?></td>
                                                <td><?= $data->customer_name; ?></td>
                                                <td><?= $data->sale_id; ?></td>
                                                <td><?= $data->sale_date; ?></td>
                                                <td><?= $data->grand_total; ?></td>
                                                <td><?= $data->payment_id; ?></td>
                                                <td><?= $data->amount; ?></td>
                                                <td><?= $data->collection_date; ?></td>
                                                <td><?= $data->amount; ?></td>
                                                <td><?= $data->ledger_name; ?></td>
                                               
                                            </tr>
                                        <?php
                                    }
                                ?>
                                <tr>
                                    <td colspan="2"><strong>Totals: </strong></td>
                                    <td colspan="1"><strong>-</strong></td>
                                    <td colspan="1"><strong>-</strong></td>
                                    <td colspan="1"><strong>-</strong></td>
                                    <td colspan="1"><strong>-</strong></td>
                                    <td colspan="1"><strong>-</strong></td>
                                    <td colspan="1"><strong>-</strong></td>
                                    <td colspan="1"><strong><?= $this->sma->formatNumber($grand_total_sale); ?></strong></td>
                                    <td colspan="1"><strong>-</strong></td>
                                    <td colspan="1"><strong><?= $this->sma->formatNumber($grand_total_payment); ?></strong></td>
                                    <td colspan="1"><strong>-</strong></td>
                                    <td colspan="1"><strong>-</strong></td>
                                    <td colspan="1"><strong>-</strong></td>
                                </tr>
                            </tbody>
                            <tfoot></tfoot>
                        </table>
                    </div> 
            </div> 
        </div>
    </div> 
</div>