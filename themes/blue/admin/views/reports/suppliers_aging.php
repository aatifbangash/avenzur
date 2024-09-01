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
        <h2 class="blue"><i class="fa-fw fa fa-users"></i><?= lang('supplier_aging_report'); ?></h2>
        <?php  if($viewtype!='pdf'){?>
        <div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown">
                    <a href="javascript:void(0);" onclick="exportTableToExcel('poTable', 'supplier_aging_report.xlsx')" id="xls" class="tip" title="<?= lang('download_xls') ?>"><i class="icon fa fa-file-excel-o"></i></a>
                </li>
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
            echo admin_form_open_multipart('reports/supplier_aging', $attrib);
        ?>
        <input type="hidden" name="viewtype" id="viewtype" class="viewtype" value="" > 
                <div class="row">
                    <div class="col-lg-12">
                        <div class="col-md-4">
                            <div class="form-group">
                                <?= lang('Duration', 'duration'); ?>
                                <select id="duration" name="duration" class="form-control input-tip select" required="required" style="width:100%;">
                                    <option value="30">30 Days</option>
                                    <option value="60">60 Days</option>
                                    <option value="90">90 Days</option>
                                    <option value="120" selected>120 Days</option>
                                    <option value="150">150 Days</option>
                                    <option value="180">180 Days</option>
                                    <option value="210">210 Days</option>
                                    <option value="240">240 Days</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <?= lang('Date', 'fromdate'); ?>
                                <?php echo form_input('from_date', ($start_date ?? ''), 'class="form-control input-tip date" id="fromdate"'); ?>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="form-group">
                                <?= lang('suppliers', 'posupplier'); ?>
                                <?php
                                if(empty($supplier_id_array)){
                                    $supplier_id_array=array();  
                                }
                                $sp = ['' => ''];
                                foreach ($suppliers as $supplier) {
                                    $sp[$supplier->id] = $supplier->company . ' (' . $supplier->name . ') - ' . $supplier->sequence_code;
                                } 
                                echo form_dropdown(
                                    'supplier[]', 
                                    $sp, 
                                    $supplier_id_array, 
                                    'id="supplier_id" class="form-control input-tip select" data-placeholder="' . lang('select') . ' ' . lang('supplier') . '" multiple required="required" style="width:100%;"'
                                );?>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="from-group">
                                <button type="submit" style="margin-top: 28px;" class="btn btn-primary"
                                        id="load_report"><?= lang('Load Report') ?></button>
                            </div>
                        </div>

                    </div>
                </div>
                <?php echo form_close(); 
                } ?>
                <hr/>
                <div class="row">
                    <div class="controls table-controls" style="font-size: 12px !important;">
                        <table id="poTable"
                                class="table items table-striped table-bordered table-condensed table-hover sortable_table tbl_pdf" >
                            <thead>
                            <tr>
                                <th>#</th>
                                <th><?= lang('Supplier'); ?></th>
                                <th><?= lang('Credit Term'); ?></th>
                                <?php
                                    $duration = $this->input->post('duration') ? $this->input->post('duration') : 120;
                                    $intervals = [30, 60, 90, 120, 150, 180, 210, 240];
                                    $previous_limit = 0;
                                    $count = 1;
                                    foreach ($intervals as $interval) {
                                        if ($interval > $duration) {
                                            break;
                                        }
                                        if($count == 1) {
                                            $start = $previous_limit;
                                        }else{
                                            $start = $previous_limit + 1;
                                        }
                                        //$start = $previous_limit + 1;
                                        $end = $interval;
                                        $previous_limit = $end;
                                        echo "<th>{$start}-{$end}</th>";
                                        $count = $count+1;
                                    }

                                    echo "<th>>{$duration}</th>";
                                ?>
                                <th><?= lang('Total'); ?></th>
                            </tr>
                            </thead>
                            <tbody style="text-align:center;">
                                <?php
                                    $count = 0;
                                    $totals = [
                                        //'Current' => 0,
                                        '0-30' => 0,
                                        '31-60' => 0,
                                        '61-90' => 0,
                                        '91-120' => 0,
                                        '121-150' => 0,
                                        '151-180' => 0,
                                        '181-210' => 0,
                                        '211-240' => 0,
                                        '>30' => 0,
                                        '>60' => 0,
                                        '>90' => 0,
                                        '>120' => 0,
                                        '>150' => 0,
                                        '>180' => 0,
                                        '>210' => 0,
                                        '>240' => 0,
                                        'total' => 0
                                    ];
                                 
                                    
                                    foreach ($supplier_aging as $key => $data){
                                        $data = (array)$data;
                                        
                                        $total_sum = 0;
                                        foreach ($data as $k1 => $value) {
                                            if ($k1 !== 'supplier_id' && $k1 !== 'supplier_name' && $k1 !== 'payment_term') {
                                                $total_sum += (float) $value;
                                                $totals[$k1] += (float) $value;
                                            }
                                        }
                                        $totals['total'] += $total_sum;

                                        $count++;
                                        ?>
                                            <tr>
                                                <td><?= $count; ?></td>
                                                <td><?= $data['supplier_name']; ?></td>
                                                <td><?= $data['payment_term']; ?></td>
                                                <?php
                                                    $i=1;
                                                    $previous_limit = 0;
                                                    foreach ($intervals as $interval) {
                                                        if ($interval > $duration) {
                                                            break;
                                                        }
                                                        if($i == 1) {
                                                            $start = $previous_limit;
                                                        }else{
                                                            $start = $previous_limit + 1;
                                                        }
                                                        
                                                        $end = $interval;
                                                        $previous_limit = $end;
                                                        echo "<td>{$this->sma->formatNumber($data["{$start}-{$end}"])}</td>";
                                                        $i = $i+1;   
                                                    }

                                                    echo "<td>{$this->sma->formatNumber($data[">{$duration}"])}</td>";
                                                ?>
                                                <td><?= $this->sma->formatNumber($total_sum); ?></td>
                                            </tr>
                                        <?php
                                    }
                                ?>
                            </tbody>
                            <tfoot style="text-align:center;">
                                <tr>
                                    <td colspan="2"><strong></strong></td>
                                    <td><strong></strong></td>
                                    <?php
                                        $previous_limit = 0;
                                        $i=1;
                                        foreach ($intervals as $interval) {
                                            if ($interval > $duration) {
                                                break;
                                            }
                                            if($i == 1) {
                                                $start = $previous_limit;
                                            }else{
                                                $start = $previous_limit + 1;
                                            }
                                            //$start = $previous_limit + 1;
                                            $end = $interval;
                                            $previous_limit = $end;
                                            echo "<td><strong>{$this->sma->formatNumber($totals["{$start}-{$end}"])}</strong></td>";
                                            $i=$i+1;
                                        }

                                        echo "<td><strong>{$this->sma->formatNumber($totals[">{$duration}"])}</strong></td>";
                                    ?>
                                    <td><strong><?= $this->sma->formatNumber($totals['total']); ?></strong></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                
            </div>

        </div>
    </div>
   
</div>