<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<style>
 .redcls{ background-color: #f6eaea;}
 .redcls td { background-color: #f6eaea; }

</style>

<?php

$v = '';
/* if($this->input->post('name')){
  $v .= "&product=".$this->input->post('product');
  } */
/* if ($this->input->post('product')) {
    $v .= '&product=' . $this->input->post('product');
} 
 */ 
?>

<script>

function integerFormat(data, type, row) {
        if (type === 'display' || type === 'filter') {
            return parseInt(data).toLocaleString();
        }
        return data;
    }



    $(document).ready(function () {
        oTable = $('#SlRData').dataTable({
            'bFilter': false,
            "aaSorting": [[3, "desc"]],
            "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
            "iDisplayLength": <?= $Settings->rows_per_page ?>, 
            'bProcessing': true, 'bServerSide': true,
            'sAjaxSource': '<?= admin_url('reports/get_out_of_stock_products/?v=1' . $v) ?>',
            'fnServerData': function (sSource, aoData, fnCallback) {
                aoData.push({
                    "name": "<?= $this->security->get_csrf_token_name() ?>",
                    "value": "<?= $this->security->get_csrf_hash() ?>"
                }); 
                aoData.push( { "name": "status", "value": $('#status').val() } ); 
                aoData.push( { "name": "keyword", "value": $('#keyword').val() } );
                $.ajax({'dataType': 'json', 'type': 'POST', 'url': sSource, 'data': aoData, 'success': fnCallback});
            },
            'fnRowCallback': function (nRow, aData, iDisplayIndex) {  
             //   nRow.id = aData[9];
              //  nRow.className = (aData[5] > 0) ? "invoice_link2" : "invoice_link2 warning"; 
                var alertQty= parseInt(aData[3]); 
                var qty= parseInt(aData[4]);
                 nRow.className = (alertQty == qty) ? "danger" : " "; 
                return nRow;
            },
            "aoColumns": [{"bSortable": false,"mRender": img_hl},null, null, {"mRender": integerFormat}, null],
            "fnFooterCallback": function (nRow, aaData, iStart, iEnd, aiDisplay) {
                // var gtotal_promo_price = 0, gprice = 0, balance = 0;
                // for (var i = 0; i < aaData.length; i++) {
                //      gtotal_promo_price += parseFloat(aaData[aiDisplay[i]][7]);
                //      gprice += parseFloat(aaData[aiDisplay[i]][8]);
                    
                // }
                // var nCells = nRow.getElementsByTagName('th');
                //  nCells[7].innerHTML = currencyFormat(gtotal_promo_price);
                //  nCells[8].innerHTML = currencyFormat(parseFloat(gprice));
                // nCells[7].innerHTML = currencyFormat(parseFloat(balance));
            }
        }).fnSetFilteringDelay().dtFilter([
           
            {column_number: 1, filter_default_label: "[<?=lang('product_code');?>]", filter_type: "text", data: []},
            {column_number: 2, filter_default_label: "[<?=lang('product_name');?>]", filter_type: "text", data: []},
            {column_number: 3, filter_default_label: "[<?=lang('alert_quantity');?>]", filter_type: "text", data: []},
            {column_number: 4, filter_default_label: "[<?=lang('quantity');?>]", filter_type: "text", data: []},
            
        ], "footer");

        // Redraw the table based on the custom input
        $('.btn_search').bind("click", function(){  
                    oTable.fnDraw();   
        }); 

        // Redraw the table based on the custom input
        $('.btn_reset').bind("click", function(){ 
            
               $('#keyword').val('');
               $('#status').val('All');  
               $('#status').val('All').trigger("change");
              // $('#status').val(null).trigger("change");
               oTable.fnDraw();   
        });


    });

 




</script>
<script type="text/javascript">
    $(document).ready(function () {
        $('#form').hide();
        
        $('.toggle_down').click(function () {
            $("#form").slideDown();
            return false;
        });
        $('.toggle_up').click(function () {
            $("#form").slideUp();
            return false;
        });
        $("#form").slideDown();
    });
</script> 
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-heart"></i><?= lang('Out_of_stock_products_dashboard'); ?> <?php
            if ($this->input->post('start_date')) {
                echo 'From ' . $this->input->post('start_date') . ' to ' . $this->input->post('end_date');
            }
            ?>
        </h2>

        <div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown">
                    <a href="#" class="toggle_up tip" title="<?= lang('hide_form') ?>">
                        <i class="icon fa fa-toggle-up"></i>
                    </a>
                </li>
                <li class="dropdown">
                    <a href="#" class="toggle_down tip" title="<?= lang('show_form') ?>">
                        <i class="icon fa fa-toggle-down"></i>
                    </a>
                </li>
            </ul>
        </div>
        <div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown">
                   <a href="#" id="xls" class="tip" title="<?= lang('download_xls') ?>">
                        <i class="icon fa fa-file-excel-o"></i>
                    </a>  
                </li>
                <li class="dropdown">
                    <a href="#" id="image" class="tip" title="<?= lang('save_image') ?>">
                        <i class="icon fa fa-file-picture-o"></i>
                    </a>
                </li>
            </ul>
        </div>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">

                <p class="introtext"><?= lang('customize_report'); ?></p>

                <div id="form" class="searchForm" style="display:none">

                    <?php // echo admin_form_open('reports/fast_moving_items', 'autocomplete="off"'); ?>
                    <div class="row">
                        <!-- 
                        <div class="col-sm-4">
                            <div class="form-group">
                                <?= lang('product', 'suggest_product'); ?>
                                <?php echo form_input('sproduct', (isset($_POST['sproduct']) ? $_POST['sproduct'] : ''), 'class="form-control" id="suggest_product"'); ?>
                                <input type="hidden" name="product" value="<?= isset($_POST['product']) ? $_POST['product'] : '' ?>" id="report_product_id"/>
                            </div>
                        </div> 
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label class="control-label" for="warehouse"><?= lang('warehouse'); ?></label>
                                <?php
                                $wh[''] = lang('select') . ' ' . lang('warehouse');
                                foreach ($warehouses as $warehouse) {
                                    $wh[$warehouse->id] = $warehouse->name;
                                }
                                echo form_dropdown('warehouse', $wh, (isset($_POST['warehouse']) ? $_POST['warehouse'] : ''), 'class="form-control" id="warehouse" data-placeholder="' . $this->lang->line('select') . ' ' . $this->lang->line('warehouse') . '"');
                                ?>
                            </div>
                        </div> 
                            --> 
                             
                            <div class="col-sm-3">
                                <div class="form-group">
                                <?= lang('keyword', 'keyword'); ?>
                                <?php echo form_input('keyword', (isset($_POST['keyword']) ? $_POST['keyword'] : ''), 'class="form-control " id="keyword"'); ?>
                                </div>
                            </div>
                            <div class="col-sm-3">
                                <div class="form-group">
                                <?= lang('status', 'status'); 
                                $options_arr = array('All'=>'All', 'out_of_stock'=>'Out of Stock')?>
                                <?php echo form_dropdown('status', $options_arr, (isset($_POST['status']) ? $_POST['status'] : ''), 'class="form-control cls_status" id="status" data-placeholder="' . $this->lang->line('select') . ' ' . $this->lang->line('status') . '"'); ?>
                                </div>
                            </div> 
                            <div class="col-sm-3">
                                <div class="form-group"> <label> &nbsp </label>
                                    <div class="controls">  
                                    <button type="button" class="btn btn-primary btn_search">Search </button>  <button type="button" class="btn btn-default btn_reset">Reset </button>    
                                    <?php // echo form_submit('submit_report', $this->lang->line('Search'), 'class="btn btn-primary btn_search"'); ?> 
                                </div>
                                </div> 
                             </div> 
                            </div>
                   
                    <?php // echo form_close(); ?>

                </div>
                <div class="clearfix"></div>

                <div class="table-responsive">
                    <table id="SlRData"
                           class="table table-bordered table-hover table-striped table-condensed reports-table">
                        <thead>
                        <tr>
                        <th style="min-width:40px; width: 40px; text-align: center;"><?php echo $this->lang->line('image'); ?></th>
                            <th><?= lang('product_code'); ?></th>
                            <th><?= lang('product_name'); ?></th>
                            <th><?= lang('alert_quantity'); ?></th>
                            <th><?= lang('quantity'); ?></th> 
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td colspan="9" class="dataTables_empty"><?= lang('loading_data_from_server') ?></td>
                        </tr>
                        </tbody>
                        <tfoot class="dtFilter">
                        <tr class="active">
                            <th><?= lang('image'); ?></th>
                            <th><?= lang('product_code'); ?></th>
                            <th><?= lang('product_name'); ?></th>
                            <th><?= lang('alert_quantity'); ?></th>
                            <th><?= lang('quantity'); ?></th> 
                        </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript" src="<?= $assets ?>js/html2canvas.min.js"></script>
<script type="text/javascript">
    $(document).ready(function () {
        // $('#pdf').click(function (event) {
        //     event.preventDefault();
        //     window.location.href = "<?=admin_url('reports/get_out_of_stock_products/pdf/?v=1' . $v)?>";
        //     return false;
        // });
        $('#xls').click(function (event) {
            event.preventDefault(); 
            var status=$('#status').val();  
            var keyword=$('#keyword').val(); 
            var queryString = '';
            if(keyword){ 
                queryString += '&keyword=' + keyword ;
            } 
            if(status){ 
                queryString += '&status=' + status ;
            }  
            window.location.href = "<?=admin_url('reports/get_out_of_stock_products/0/xls/?v=1')?>"+queryString;
            return false;
        });
        $('#image').click(function (event) {
            event.preventDefault();
            html2canvas($('.box'), {
                onrendered: function (canvas) {
                    openImg(canvas.toDataURL());
                }
            });
            return false;
        });
    });
</script>
