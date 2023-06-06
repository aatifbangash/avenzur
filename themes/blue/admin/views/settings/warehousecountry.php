<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<script>
    $(document).ready(function () {
        function tax_type(x) {
            return (x == 1) ? "<?=lang('percentage')?>" : "<?=lang('fixed')?>";
        }

        oTable = $('#CURData').dataTable({
            "aaSorting": [[2, "asc"], [3, "asc"]],
            "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
            "iDisplayLength": <?= $Settings->rows_per_page ?>,
            'bProcessing': true, 'bServerSide': true,
            'sAjaxSource': '<?= admin_url('system_settings/getWarehouses') ?>',
            'fnServerData': function (sSource, aoData, fnCallback) {
                aoData.push({
                    "name": "<?= $this->security->get_csrf_token_name() ?>",
                    "value": "<?= $this->security->get_csrf_hash() ?>"
                });
                $.ajax({'dataType': 'json', 'type': 'POST', 'url': sSource, 'data': aoData, 'success': fnCallback});
            },
            "aoColumns": [{"bSortable": false, "mRender": checkbox}, { "bSortable": false, "mRender": img_hl }, null, null, null, null, null, null, {"bSortable": false}]
        });
    });
</script>

<div class="box">
    <!--<div class="box-header">-->
    <!--    <h2 class="blue"><i class="fa-fw fa fa-building-o"></i><?= $page_title ?></h2>-->

    <!--    <div class="box-icon">-->
    <!--        <ul class="btn-tasks">-->
    <!--            <li class="dropdown">-->
    <!--                <a data-toggle="dropdown" class="dropdown-toggle" href="#">-->
    <!--                    <i class="icon fa fa-tasks tip" data-placement="left" title="<?= lang('actions') ?>"></i>-->
    <!--                </a>-->
    <!--                <ul class="dropdown-menu pull-right tasks-menus" role="menu" aria-labelledby="dLabel">-->
    <!--                    <li>-->
    <!--                        <a href="<?php echo admin_url('system_settings/add_warehouse'); ?>" data-toggle="modal" data-target="#myModal">-->
    <!--                            <i class="fa fa-plus"></i> <?= lang('add_warehouse') ?>-->
    <!--                        </a>-->
    <!--                        </li>-->
    <!--                    <li>-->
    <!--                        <a href="#" id="excel" data-action="export_excel">-->
    <!--                            <i class="fa fa-file-excel-o"></i> <?= lang('export_to_excel') ?>-->
    <!--                        </a>-->
    <!--                    </li>-->
    <!--                    <li class="divider"></li>-->
    <!--                    <li>-->
    <!--                        <a href="#" id="delete" data-action="delete">-->
    <!--                            <i class="fa fa-trash-o"></i> <?= lang('delete_warehouses') ?>-->
    <!--                        </a>-->
    <!--                    </li>-->

    <!--                </ul>-->
    <!--            </li>-->
    <!--        </ul>-->
    <!--    </div>-->
    <!--</div>-->
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
                <p class="introtext"><?= lang('warehouse_with_country'); ?></p>
 <?php $attrib = ['data-toggle' => 'validator', 'role' => 'form'];
                echo admin_form_open_multipart('system_settings/add_warehouse_country', $attrib );
                ?>
                
                
            <?php
           
            // print_r($warehousecountries[0]->warehouses_id);
             // var_dump($warehousecountries);
            
            $counter = 0;           
                         
            foreach($countries as $cdata){
                      
              //  var_dump($cdata );
            
            ?>
                <div class="row">
                   <div class="col-sm-6">
                        <div class="form-group">
                                 <input type="hidden" class="form-control" value="<?=$cdata->id?>"  name="country_id[]" ><?=$cdata->name?>
                        </div>
                   </div>
                   <div class="col-sm-6">
                       <div class="form-group">
                           <?php  // $counter = 0;   echo $warehousecountries[$counter]->country_id; ?>
                           <select class="form-control" id="warehouses" name="warehouses_id[]" >
                        <?php
                       
                               
                             //    print_r($warehousecountries[$counter]->country_id);
                                       
                                foreach($warehouses as $u) //a b 1-pharma 2-dubai
                                {
                                    
                                    $s='';
                                    
                                        if($warehousecountries[$counter]->warehouses_id == $u->id )
                                        {
                                            $s = 'selected="selected"';
                                            echo '<option value="'.$u->id.'" selected="selected">'.$u->name.'</option>';
                                            
                                        }else{
                                           echo '<option value="'.$u->id.'">'.$u->name.'</option>';
                                        }
                                      
                                }
                                 
                        ?>
                        </select>
                        </div>
                   </div>
                   
                   
                </div>   
                   
                   <?php  $counter++; } 
                   
                                
                   ?>
                    
                   
                   <div class="col-md-12">
                      
                        <?php echo form_submit('add_warehouse', lang('Save'), 'class="btn btn-primary"'); ?>
                   </div>
                     
               
               <?php echo form_close(); ?>
               
            </div>

        </div>
    </div>
</div>

<div style="display: none;">
    <input type="hidden" name="form_action" value="" id="form_action"/>
    <?= form_submit('submit', 'submit', 'id="action-form-submit"') ?>
</div>
<?= form_close() ?>
<script language="javascript">
    $(document).ready(function () {

        $('#delete').click(function (e) {
            e.preventDefault();
            $('#form_action').val($(this).attr('data-action'));
            $('#action-form-submit').trigger('click');
        });

        $('#excel').click(function (e) {
            e.preventDefault();
            $('#form_action').val($(this).attr('data-action'));
            $('#action-form-submit').trigger('click');
        });

        $('#pdf').click(function (e) {
            e.preventDefault();
            $('#form_action').val($(this).attr('data-action'));
            $('#action-form-submit').trigger('click');
        });

    });
</script>

