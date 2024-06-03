<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php

$v = '';
/* if($this->input->post('name')){
  $v .= "&product=".$this->input->post('product');
  } */
/* if ($this->input->post('product')) {
    $v .= '&product=' . $this->input->post('product');
}
if ($this->input->post('reference_no')) {
    $v .= '&reference_no=' . $this->input->post('reference_no');
}
if ($this->input->post('customer')) {
    $v .= '&customer=' . $this->input->post('customer');
}
if ($this->input->post('biller')) {
    $v .= '&biller=' . $this->input->post('biller');
}
if ($this->input->post('warehouse')) {
    $v .= '&warehouse=' . $this->input->post('warehouse');
}
if ($this->input->post('user')) {
    $v .= '&user=' . $this->input->post('user');
}
if ($this->input->post('serial')) {
    $v .= '&serial=' . $this->input->post('serial');
}
if ($this->input->post('start_date')) {
    $v .= '&start_date=' . $this->input->post('start_date');
}
if ($this->input->post('end_date')) {
    $v .= '&end_date=' . $this->input->post('end_date');
}
 */ 
?>

<script>
    $(document).ready(function () {
        oTable = $('#SlRData').dataTable({
            'bFilter': false,
            "aaSorting": [[3, "desc"]],
            "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
            "iDisplayLength": <?= $Settings->rows_per_page ?>,
            'bProcessing': true, 'bServerSide': true,
            'sAjaxSource': '<?= admin_url('reports/ecommerce_fast_moving_items/?v=1' . $v) ?>',
            'fnServerData': function (sSource, aoData, fnCallback) {
                aoData.push({
                    "name": "<?= $this->security->get_csrf_token_name() ?>",
                    "value": "<?= $this->security->get_csrf_hash() ?>"
                });
                aoData.push( { "name": "start_date", "value": $('#start_date').val() } );
                aoData.push( { "name": "end_date", "value": $('#end_date').val() } );
                aoData.push( { "name": "keyword", "value": $('#keyword').val() } );

                $.ajax({'dataType': 'json', 'type': 'POST', 'url': sSource, 'data': aoData, 'success': fnCallback});
            },
            'fnRowCallback': function (nRow, aData, iDisplayIndex) { 
               
             //   nRow.id = aData[9];
              //  nRow.className = (aData[5] > 0) ? "invoice_link2" : "invoice_link2 warning";
                return nRow;
            },
            "aoColumns": [{"bSortable": false,"mRender": img_hl},null, null, null, null],
            "fnFooterCallback": function (nRow, aaData, iStart, iEnd, aiDisplay) {
                var gtotal = 0, total_qty = 0, balance = 0;
                for (var i = 0; i < aaData.length; i++) {
                     total_qty += parseFloat(aaData[aiDisplay[i]][3]);
                     gtotal += parseFloat(aaData[aiDisplay[i]][4]);
                    // balance += parseFloat(aaData[aiDisplay[i]][7]);
                }
                var nCells = nRow.getElementsByTagName('th');
                 nCells[3].innerHTML = currencyFormat(parseFloat(total_qty));
                 nCells[4].innerHTML = currencyFormat(parseFloat(gtotal));
                // nCells[7].innerHTML = currencyFormat(parseFloat(balance));
            }
        }).fnSetFilteringDelay().dtFilter([
           // {column_number: 0, filter_default_label: "[<?=lang('date');?> (yyyy-mm-dd)]", filter_type: "text", data: []},
            {column_number: 1, filter_default_label: "[<?=lang('product_cod');?>]", filter_type: "text", data: []},
            {column_number: 2, filter_default_label: "[<?=lang('product_name');?>]", filter_type: "text", data: []},
            {column_number: 3, filter_default_label: "[<?=lang('total_quantities');?>]", filter_type: "text", data: []},
            {column_number: 4, filter_default_label: "[<?=lang('total_amount');?>]", filter_type: "text", data: []},
        ], "footer");

        // Redraw the table based on the custom input
        $('.btn_search').bind("click", function(){  
                    oTable.fnDraw();   
        }); 

        // Redraw the table based on the custom input
        $('.btn_reset').bind("click", function(){ 
            
               $('#keyword').val('');
               $('#start_date').val(''); 
               $('#end_date').val(''); 
               oTable.fnDraw();   
        });


    });

 




</script>
<script type="text/javascript">
    $(document).ready(function () {
        $('#form').hide();
        <?php if ($this->input->post('customer') ) {
    ?>
       
       $('#customer').val(<?= $this->input->post('customer') ?>).select2({
            minimumInputLength: 1,
            data: [],
            initSelection: function (element, callback) {
                $.ajax({
                    type: "get", async: false,
                    url: site.base_url + "customers/suggestions/" + $(element).val(),
                    dataType: "json",
                    success: function (data) {
                        callback(data.results[0]);
                    }
                });
            },
            ajax: {
                url: site.base_url + "customers/suggestions",
                dataType: 'json',
                quietMillis: 15,
                data: function (term, page) {
                    return {
                        term: term,
                        limit: 10
                    };
                },
                results: function (data, page) {
                    if (data.results != null) {
                        return {results: data.results};
                    } else {
                        return {results: [{id: '', text: 'No Match Found'}]};
                    }
                }
            }
        });

        $('#customer').val(<?= $this->input->post('customer') ?>);
        <?php
} ?>
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
        <h2 class="blue"><i class="fa-fw fa fa-heart"></i><?= lang('Ecommerce_Fast_Moving_Items_Report'); ?> <?php
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

                <div id="form1">

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
                                <label class="control-label" for="reference_no"><?= lang('reference_no'); ?></label>
                                <?php echo form_input('reference_no', (isset($_POST['reference_no']) ? $_POST['reference_no'] : ''), 'class="form-control tip" id="reference_no"'); ?>

                            </div>
                        </div>

                        <div class="col-sm-4">
                            <div class="form-group">
                                <label class="control-label" for="user"><?= lang('created_by'); ?></label>
                                <?php
                                $us[''] = lang('select') . ' ' . lang('user');
                                foreach ($users as $user) {
                                    $us[$user->id] = $user->first_name . ' ' . $user->last_name;
                                }
                                echo form_dropdown('user', $us, (isset($_POST['user']) ? $_POST['user'] : ''), 'class="form-control" id="user" data-placeholder="' . $this->lang->line('select') . ' ' . $this->lang->line('user') . '"');
                                ?>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label class="control-label" for="customer"><?= lang('customer'); ?></label>
                                <?php echo form_input('customer', (isset($_POST['customer']) ? $_POST['customer'] : ''), 'class="form-control" id="customer" data-placeholder="' . $this->lang->line('select') . ' ' . $this->lang->line('customer') . '"'); ?>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label class="control-label" for="biller"><?= lang('biller'); ?></label>
                                <?php
                                $bl[''] = lang('select') . ' ' . lang('biller');
                                foreach ($billers as $biller) {
                                    $bl[$biller->id] = $biller->company && $biller->company != '-' ? $biller->company : $biller->name;
                                }
                                echo form_dropdown('biller', $bl, (isset($_POST['biller']) ? $_POST['biller'] : ''), 'class="form-control" id="biller" data-placeholder="' . $this->lang->line('select') . ' ' . $this->lang->line('biller') . '"');
                                ?>
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
                        <?php if ($Settings->product_serial) {
                                    ?>
                            <div class="col-sm-4">
                                <div class="form-group">
                                    <?= lang('serial_no', 'serial'); ?>
                                    <?= form_input('serial', '', 'class="form-control tip" id="serial"'); ?>
                                </div>
                            </div>
                        <?php
                                } ?> 
                            --> 
                             
                            <div class="col-sm-3">
                            <div class="form-group">
                                <?= lang('keyword', 'keyword'); ?>
                                <?php echo form_input('keyword', (isset($_POST['keyword']) ? $_POST['keyword'] : ''), 'class="form-control " id="keyword"'); ?>
                            </div>
                        </div>
                        <div class="col-sm-3">
                            <div class="form-group">
                                <?= lang('start_date', 'start_date'); ?>
                                <?php echo form_input('start_date', (isset($_POST['start_date']) ? $_POST['start_date'] : ''), 'class="form-control datetime" id="start_date"'); ?>
                            </div>
                        </div>
                        <div class="col-sm-3">
                            <div class="form-group">
                                <?= lang('end_date', 'end_date'); ?>
                                <?php echo form_input('end_date', (isset($_POST['end_date']) ? $_POST['end_date'] : ''), 'class="form-control datetime" id="end_date"'); ?>
                            </div>
                        </div>


                        <div class="col-sm-3">
                        <div class="form-group"> <label> &nbsp </label>
                         <div class="controls">  
                            <button type="button" class="btn btn-primary btn_search">Search </button>  <button type="button" class="btn btn-default btn_reset">Reset </button>    
                            <?php // echo form_submit('submit_report', $this->lang->line('Search'), 'class="btn btn-primary btn_search"'); ?> </div>
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
                            <th><?= lang('Total_Quantities_Sold'); ?></th>
                            <th><?= lang('total_amount'); ?></th>
                            
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
                            <th><?= lang('total_quantities'); ?></th>
                            <th><?= lang('total_amount'); ?></th>
                            
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
        //     window.location.href = "<?=admin_url('reports/getSalesReport/pdf/?v=1' . $v)?>";
        //     return false;
        // });
        $('#xls').click(function (event) {
            event.preventDefault();
           
            var start_date=$('#start_date').val(); 
            var end_date=$('#end_date').val(); 
            var keyword=$('#keyword').val(); 
            var queryString = '';
            if(start_date){ 
                queryString += '&keyword=' + keyword ;
            } 
            if(start_date){ 
                queryString += '&start_date=' + start_date ;
            } 
            if(end_date){ 
                queryString += '&end_date=' + end_date ;
            } 
            window.location.href = "<?=admin_url('reports/ecommerce_fast_moving_items/0/xls/?v=1')?>"+queryString;
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
