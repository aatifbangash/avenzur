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
<?php if (!isset($viewtype) || $viewtype != 'pdf') { ?>
<script>
    $(function () {
        if ($.fn.datetimepicker) {
            $('.date-picker-filter').datetimepicker({
                format: 'dd/mm/yyyy',
                autoclose: true,
                todayHighlight: true,
                minView: 2
            });
        }
        if ($.fn.select2) {
            $('#supplier_id').select2({
                width: '100%',
                allowClear: true,
                placeholder: '<?= addslashes(lang('All Suppliers')) ?>'
            });
        }
    });
</script>
<?php } ?>
<?php if($viewtype=='pdf'){ ?>
    <link href="<?= $assets ?>styles/pdf/pdf.css" rel="stylesheet"> 
  <?php  } ?>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-truck"></i>Supplier Payment by Invoice</h2>
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
                                <?php echo form_input('from_date', ($_GET['from_date'] ?? ''), 'class="form-control input-tip input-sm date-picker-filter" id="fromdate" autocomplete="off"'); ?>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <?= lang('To Date', 'podate'); ?>
                                <?php echo form_input('to_date', ($_GET['to_date'] ?? ''), 'class="form-control input-tip input-sm date-picker-filter" id="todate" autocomplete="off"'); ?>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <?= lang('Supplier', 'supplier'); ?>
                                <?php
                                $sup_dp = ['' => '-- ' . lang('All Suppliers') . ' --'];
                                foreach (($suppliers ?? []) as $s) {
                                    $label_name = !empty($s->name) ? $s->name : ($s->company ?? '');
                                    $label_code = !empty($s->sequence_code) ? ' (' . $s->sequence_code . ')' : '';
                                    $sup_dp[$s->id] = $s->id . ' - ' . $label_name . $label_code;
                                }
                                echo form_dropdown('supplier', $sup_dp, ($_GET['supplier'] ?? ($supplier ?? '')), 'id="supplier_id" class="form-control input-tip select" data-placeholder="' . lang('select') . ' ' . lang('supplier') . '" style="width:100%;"');
                                ?>
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
                                <th><?= lang('warehouse'); ?></th>
                                <th><?= lang('Supplier No.'); ?></th>
                                <th><?= lang('Supplier Name'); ?></th>
                                <th><?= lang('Purchase #'); ?></th>
                                <th><?= lang('Invoice ID'); ?></th>
                                <th><?= lang('Invoice Type'); ?></th>
                                <th><?= lang('Invoice Date'); ?></th>
                                <th><?= lang('payment_term'); ?></th>
                                <th><?= lang('due_date'); ?></th>
                                <th><?= lang('Invoice Amount'); ?></th>
                                <th><?= lang('payments'); ?></th>
                                <th><?= lang('balance'); ?></th>
                                <th><?= lang('payment_status'); ?></th>
                                <th>Last Payment Date</th>
                            </tr>
                            </thead>
                            <tbody style="text-align:center;">
                                <?php
                                    $count = 0;
                                    
                                    $grand_total_purchase = 0;
                                    $grand_total_payment = 0;
                                    foreach (($payments_data ?? []) as $data){
                                        $count ++ ;
                                        $grand_total_purchase += $data->grand_total;
                                        $grand_total_payment += $data->paid_amount;
                                        ?>
                                            <tr>
                                                <td><?= $count; ?></td>
                                                <td><?= $data->warehouse_name; ?></td>
                                                <td><?= $data->sequence_code ?? $data->supplier_id; ?></td>
                                                <td><?= $data->supplier_name; ?></td>
                                                <td><?= $data->invoice_no ?: $data->purchase_id; ?></td>
                                                <td><?= $data->purchase_id; ?></td>
                                                <td><?= 'Purchase Invoice'; ?></td>
                                                <td><?= $data->purchase_date ? date('d-M-Y', strtotime($data->purchase_date)) : '' ?></td>
                                                <td><?= (int) $data->payment_term; ?></td>
                                                <td><?= $data->due_date ? date('d-M-Y', strtotime($data->due_date)) : '' ?></td>
                                                <td><?= number_format($data->grand_total, 2); ?></td>
                                                <td><?= number_format($data->paid_amount, 2); ?></td>
                                                <td><?= number_format($data->balance_amount, 2); ?></td>
                                                <td><?= $data->payment_status; ?></td>
                                                <td><?= $data->last_payment_date ? date('d-M-Y', strtotime($data->last_payment_date)) : '' ?></td>
                                            </tr>
                                        <?php
                                    }
                                ?>
                                <tr>
                                    <td colspan="10"><strong>Totals: </strong></td>
                                    <td colspan="1"><strong><?= number_format($grand_total_purchase, 2); ?></strong></td>
                                    <td colspan="1"><strong><?= number_format($grand_total_payment, 2); ?></strong></td>
                                    <td colspan="1"><strong><?= number_format($grand_total_purchase - $grand_total_payment, 2); ?></strong></td>
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