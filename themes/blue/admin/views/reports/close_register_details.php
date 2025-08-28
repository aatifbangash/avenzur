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
<?php 
$registerIdsJson = '';
?>
<?php if($viewtype=='pdf'){ ?>
    <link href="<?= $assets ?>styles/pdf/pdf.css" rel="stylesheet"> 
  <?php  } ?>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-users"></i><?= lang('Close Register Date Wise'); ?></h2>
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
            echo admin_form_open_multipart('reports/close_register_details', $attrib)
        ?> <input type="hidden" name="viewtype" id="viewtype" class="viewtype" value="" > 
                <div class="row">
                    <div class="col-lg-12">
                       
                        <div class="col-md-3">
                            <div class="form-group">
                                <?= lang('From Date', 'podate'); ?>
                                <?php echo form_input('from_date', ($start_date ?? ''), 'class="form-control input-tip date" id="fromdate"'); ?>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="form-group">
                                <?= lang('To Date', 'podate'); ?>
                                <?php echo form_input('to_date', ($end_date ?? ''), 'class="form-control input-tip date" id="todate"'); ?>
                            </div>
                        </div>

                        <div class="col-md-3">
                            
                            
                            <?php if ($Owner || $Admin || $PurchaseManager) {
                                ?>
                                <?= lang('Pharmacy', 'popharmacy'); ?>
                                <div class="form-group">
                                    <?php
                                    $selected_warehouse_id[] = isset($warehouse_id) ? $warehouse_id : '';
                                    //$dp['all'] = 'All';
                                    foreach ($warehouses as $warehouse) {
                                        $dp[$warehouse->id] = $warehouse->name.' ('.$warehouse->code.')';
                                    }
                                    echo form_dropdown('pharmacy', $dp, ($warehouse_id), 'id="warehouse_id" class="form-control input-tip select" data-placeholder="' . lang('select') . ' ' . lang('pharmacy') . '" required="required" style="width:100%;" ', null); ?>
                                </div>
                                <?php
                            } else {
                                $warehouse_input = [
                                    'type'  => 'hidden',
                                    'name'  => 'pharmacy',
                                    'id'    => 'warehouse_id',
                                    'value' => $this->session->userdata('warehouse_id'),
                                ];

                                echo form_input($warehouse_input);
                            }?>
                        </div>

                        <div class="col-md-3">
                            
                            
                            <?php if ($Owner || $Admin  || $PurchaseManager) {
                                ?>
                                <?= lang('Pharmacist', 'popharmacist'); ?>
                                <div class="form-group">
                                    <?php
                                    $selected_pharmacist_id[] = isset($user_id) ? $user_id : '';
                                    $dpp['all'] = 'All';
                                    foreach ($pharmacists as $pharmacist) {
                                        $dpp[$pharmacist->id] = $pharmacist->first_name.' '.$pharmacist->last_name;
                                    }
                                    echo form_dropdown('pharmacist_id', $dpp, $selected_pharmacist_id, 'id="pharmacist_id" class="form-control input-tip select" data-placeholder="' . lang('select') . ' ' . lang('pharmacist') . '" required="required" style="width:100%;" ', null); ?>
                                </div>
                                <?php
                            }?>
                        </div>

                         <div class="col-md-3">                            
                              <label>  Register Numbers </label>
                                <div class="form-group">
                                    
                                    <select name="registerId" id="registerId" class="form-control">
                                    <option value="">Please select number</option>
                                    <?php if( isset($registerIds) && !empty($registerIds) ) {
                                           ?>
                                        <?php foreach($registerIds as $key => $val) {
                                           
                                            ?>
                                            <option value="<?php echo $val->id;?>" <?php if($val->id == $register_id) {?> selected="selected" <?php }?>> <?php echo $val->register_id;?> </option>
                                        <?php }
                                        //$registerIdsJson = json_encode($registerOpenCloseDateTime);
                                     }?>
                                    </select>        
                            </div>
                             
                        </div>

                          <div class="col-md-3">                            
                                <label>Register Open Date&Time</label>
                                <div class="form-group">
                                    <input type="text" class="form-control" name="register_open_date_time" id="register_open_date_time" readonly value="<?php echo isset($register_open_date_time) ? $register_open_date_time : ''; ?>" >       
                            </div>
                             
                        </div>

                          <div class="col-md-3">                            
                               <label> Register Close Date&Time </label>
                                <div class="form-group">
                                    <input type="text" class="form-control" name="register_close_date_time" id="register_close_date_time" readonly value="<?php echo isset($register_close_date_time) ? $register_close_date_time : ''; ?>" >       
                            </div>
                             
                        </div>

                        <div class="col-md-2" >
                            <div class="from-group">
                                <button type="submit" style="margin-top: 28px;" class="btn btn-primary" id="load_report"><?= lang('Load Report') ?></button>
                            </div>
                        </div>
                            
                    </div>
                </div>
                <?php echo form_close(); 
                }
                $total_sale = $cashsales->total + $ccsales->total;
                $total_sale_with_halala = $cashsales->total_with_halala + $ccsales->total;
                ?>
                <hr />
                <div class="row">
                    <div class="controls table-controls" style="font-size: 12px !important;">
                    <table width="100%" class="stable">
                <!-- <tr>
                    <td style="border-bottom: 1px solid #EEE;"><h4><?= lang('cash_in_hand'); ?>:</h4></td>
                    <td style="text-align:right; border-bottom: 1px solid #EEE;"><h4>
                            <span><?= $this->sma->formatMoney($this->session->userdata('cash_in_hand')); ?></span></h4>
                    </td>
                </tr> -->
                
                <tr>
                    <td style="border-bottom: 1px solid #EEE;"><h4><?= lang('cash_sale_with_halala'); ?>:</h4></td>
                    <td style="text-align:right; border-bottom: 1px solid #EEE;"><h4>
                            <span><?= $cashsales->total_with_halala ? $cashsales->total_with_halala : '0.00' ; ?></span>
                        </h4></td>
                </tr>
                <tr>
                    <td style="border-bottom: 1px solid #EEE;"><h4><?= lang('cash_sale'); ?>:</h4></td>
                    <td style="text-align:right; border-bottom: 1px solid #EEE;"><h4>
                            <span><?= $cashsales->total ? $cashsales->total : '0.00' ; ?></span>
                        </h4></td>
                </tr>

                 <tr>
                      <td style="border-bottom: 1px solid #EEE;"><h4><?= lang('total_returns'); ?>:</h4></td>
                    <td style="text-align:right;border-bottom: 1px solid #EEE;"><h4>
                            <span><?=  $totalreturns->total ? $totalreturns->total : '0.00'; ?></span>
                        </h4></td>
                </tr>

                 <tr>
                     <td style="border-bottom: 1px solid #EEE;"><h4><?= lang('Net Cash'); ?>:</h4></td>
                    <td style="text-align:right;border-bottom: 1px solid #EEE;"><h4>
                            <span><?=  $cashsales->total - $totalreturns->total; ?></span>
                        </h4></td>
                </tr>
                <!-- <tr>
                    <td style="border-bottom: 1px solid #EEE;"><h4><?= lang('ch_sale'); ?>:</h4></td>
                    <td style="text-align:right;border-bottom: 1px solid #EEE;"><h4>
                            <span><?= $this->sma->formatMoney($chsales->paid ? $chsales->paid : '0.00') . ' (' . $this->sma->formatMoney($chsales->total ? $chsales->total : '0.00') . ')'; ?></span>
                        </h4></td>
                </tr> -->
                <tr>
                    <td style="border-bottom: 1px solid #EEE;"><h4>Network Sales:</h4></td>
                    <td style="text-align:right;border-bottom: 1px solid #EEE;"><h4>
                            <span><?= $ccsales->total ? $ccsales->total : '0.00'; ?></span>
                        </h4></td>
                </tr>
                <!-- <tr>
                    <td style="border-bottom: 1px solid #DDD;"><h4><?= lang('gc_sale'); ?>:</h4></td>
                    <td style="text-align:right;border-bottom: 1px solid #DDD;"><h4>
                            <span><?= $this->sma->formatMoney($gcsales->paid ? $gcsales->paid : '0.00') . ' (' . $this->sma->formatMoney($gcsales->total ? $gcsales->total : '0.00') . ')'; ?></span>
                        </h4></td>
                </tr> -->
                <tr>
                    <td style="border-bottom: 1px solid #EEE;"><h4><?= lang('other'); ?>:</h4></td>
                    <td style="text-align:right;border-bottom: 1px solid #EEE;"><h4>
                            <span><?= $this->sma->formatMoney($othersales->paid ? $othersales->paid : '0.00') . ' (' . $this->sma->formatMoney($othersales->total ? $othersales->total : '0.00') . ')'; ?></span>
                        </h4></td>
                </tr>
                
               
                <tr>
                      <td style="border-bottom: 1px solid #EEE;"><h4><?= lang('total_sales'); ?>:</h4></td>
                    <td style="text-align:right;border-bottom: 1px solid #EEE;"><h4>
                            <span><?=  $total_sale; ?></span>
                        </h4></td>
                </tr>

                <tr>
                      <td style="border-bottom: 1px solid #EEE;"><h4><?= lang('total_sales_with_halala'); ?>:</h4></td>
                    <td style="text-align:right;border-bottom: 1px solid #EEE;"><h4>
                            <span><?=  $total_sale_with_halala; ?></span>
                        </h4></td>
                </tr>

               

                <tr>
                     <td style="border-bottom: 1px solid #EEE;"><h4><?= lang('Net Sale'); ?>:</h4></td>
                    <td style="text-align:right;border-bottom: 1px solid #EEE;"><h4>
                            <span><?=  $total_sale ? $total_sale - $totalreturns->total : '0.00'; ?></span>
                        </h4></td>
                </tr>

                 <tr>
                      <td style="border-bottom: 1px solid #EEE;"><h4><?= lang('Net Sale with halala'); ?>:</h4></td>
                   <td style="text-align:right;border-bottom: 1px solid #EEE;"><h4>
                            <span><?=  $total_sale_with_halala ? $total_sale_with_halala - $totalreturns->total : '0.00'; ?></span>
                        </h4></td>
                </tr>

                <!-- <tr>
                    <td width="300px;" style="font-weight:bold;"><h4><?= lang('Variance'); ?>:</h4></td>
                    <td width="200px;" style="font-weight:bold;text-align:right;"><h4>
                    <span><?=  round($totalsales->total - ($cashsales->total + $ccsales->total),2); ?></span>
                        </h4></td>
                </tr>  -->

                <tr>
                      <td style="border-bottom: 1px solid #EEE;"><h4><?= lang('Total Invoice Qty'); ?>:</h4></td>
                    <td style="text-align:right;border-bottom: 1px solid #EEE;"><h4>
                            <span><?php echo $totalsales->total_sales; ?></span>
                        </h4></td>
                </tr>
               
               
            </table>
                    </div> 
            </div> 
        </div>
    </div> 
</div>


<script>

    function loadRegisterIds() {
    let registerDropdown = $('#registerId');
    
    let fromDate = $('#fromdate').val();
    let toDate = $('#todate').val();
    let pharmacyId = $('#warehouse_id').val();
    let pharmacistId = $('#pharmacist_id').val();

 registerDropdown.val(null).trigger('change');
 registerDropdown.empty();
    if (fromDate && toDate && pharmacyId && pharmacistId) {
        $.ajax({
            url: '<?=admin_url('reports/get_register_ids')?>',
            type: 'GET',
            data: {
                fromDate: fromDate,
                toDate: toDate,
                pharmacyId: pharmacyId,
                closedBy: pharmacistId
            },
            dataType: "json",
            success: function(data) {
                console.log(data);
               
              
                if (data.length > 0) {
                     registerDropdown.val(null).trigger('change');
                      registerDropdown.append(`<option value="">Select Register Number</option>`);
                     data.forEach(function(item) {
                       
                            registerDropdown.append(`<option value="${item.id}">${item.register_id}</option>`);
                        });
                } else {
                    registerDropdown.append('<option>No registers found</option>');
                    $('#register_open_date_time').val('');
                    $('#register_close_date_time').val('');
                    registerDropdown.val(null).trigger('change');
                   
                }
            }
        });
    }
}

// $('#registerId').on('change', function() {
//     const selectedId = $(this).val();
//     console.log("ids here");
//     console.log(registerOpenCloseDateTime);
//     if (registerOpenCloseDateTime[selectedId]) {
//         $('#register_open_date_time').val(registerOpenCloseDateTime[selectedId].register_open_date_time);
//         $('#register_close_date_time').val(registerOpenCloseDateTime[selectedId].register_close_date_time);
//     } else {
//         $('#register_open_date_time').val('');
//         $('#register_close_date_time').val('');
//     }
// });

$('#registerId').on('change', function() {
    const selectedId = $(this).val();

    if (!selectedId) {
        $('#register_open_date_time').val('');
        $('#register_close_date_time').val('');
        return;
    }

    $.ajax({
        url: '<?=admin_url('reports/get_register_id_dates')?>', // replace with your API
        type: 'GET',
        data: { register_id: selectedId },
        dataType: 'json',
        success: function(response) {
            console.log(response);
            if (response && response.open_date_time && response.close_date_time) {
                $('#register_open_date_time').val(response.open_date_time);
                $('#register_close_date_time').val(response.close_date_time);
            } else {
                $('#register_open_date_time').val('');
                $('#register_close_date_time').val('');
            }
        },
        error: function() {
            $('#register_open_date_time').val('');
            $('#register_close_date_time').val('');
        }
    });
});


$('#fromdate, #todate, #warehouse_id, #pharmacist_id').change(loadRegisterIds);

</script>
