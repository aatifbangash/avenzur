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
        <h2 class="blue"><i class="fa-fw fa fa-users"></i><?= lang('Sales_Per_Invoice'); ?></h2>
    </div>
    <div class="box-content">
        <div class="row">
        <div class="col-lg-12">
        <?php
        if($viewtype!='pdf')
        {
            $attrib = ['data-toggle' => 'validator', 'role' => 'form','id' => 'searchForm', 'method' => 'get'];
            echo admin_form_open_multipart('reports/payment_by_invoice', $attrib)
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
                            <?= lang('warehouse', 'pharmacy'); ?>
                            <?php
                            $wh_label = !empty($canAccessOverseas) ? 'All Local Warehouses' : lang('all_warehouses');
                            $dp[''] = $wh_label;
                            foreach ($warehouses as $warehouse) {
                                $dp[$warehouse->id] = $warehouse->name;
                            }
                            echo form_dropdown('pharmacy', $dp, ($warehouse ?? ''), 'id="warehouse_id" class="form-control input-tip select" data-placeholder="' . lang('select') . ' ' . lang('warehouse') . '" style="width:100%;" ', null); ?>
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
                                <th><?= lang('Sale #'); ?></th>
                                <th><?= lang('Invoice Date'); ?></th>
                                <th><?= lang('Invoice Amount'); ?></th>
                                <th><?= lang('Sale Amount'); ?></th>
                            </tr>
                            </thead>
                            <tbody style="text-align:center;">
                                <?php
                                    $count = 0;
                                    
                                    $grand_total_sale = 0;
                                    $grand_total_payment = 0;
                                    foreach (($payments_data ?? []) as $data){
                                        $count ++ ;
                                        $grand_total_sale += $data->grand_total;
                                        $grand_total_payment += $data->paid_amount;
                                        ?>
                                            <tr>
                                                <td><?= $count; ?></td>
                                                <td><?= (!empty($data->collection_type) && $data->collection_type === 'service_invoice') ? lang('service_invoice') : lang('Sale'); ?></td>
                                                <td><?= $data->area; ?></td>
                                                <td><?= $data->sales_agent; ?></td>
                                                <td><?= $data->sequence_code ?? $data->customer_id; ?></td>
                                                <td><?= $data->customer_name; ?></td>
                                                <td><?= $data->sale_id; ?></td>
                                                <td><?= $data->sale_date ? date('d-M-Y', strtotime($data->sale_date)) : '' ?></td>
                                                <td><?= number_format($data->grand_total, 2); ?></td>
                                                <td><?= number_format($data->paid_amount, 2); ?></td>
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
                                    <td colspan="1"><strong>-</strong></td>
                                    <td colspan="1"><strong><?= number_format($grand_total_payment, 2); ?></strong></td>
                                </tr>
                            </tbody>
                            <tfoot></tfoot>
                        </table>
                    </div> 
            </div> 
        </div>
    </div> 
</div>