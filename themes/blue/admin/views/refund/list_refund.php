<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<script>
    $(document).ready(function () {
        function active_yn(x) {
           // return x == 1 ? '<div class="text-center"><span class="label label-success"><?= lang('yes'); ?></span></div>' : '<div class="text-center"><span class="label label-default"><?= lang('no'); ?></span></div>';  
        }
        function order_center(x) {
            return '<div class="text-center">'+x+'</div>';  
        }
        
  
        oTable = $('#PgData').dataTable({
            "aaSorting": [[4, "asc"]],
            "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
            "iDisplayLength": <?= $Settings->rows_per_page ?>,
            'bProcessing': true, 'bServerSide': true,
            'sAjaxSource': '<?= admin_url('Refund/refundDisplay') ?>',
            'fnServerData': function (sSource, aoData, fnCallback) {
                aoData.push({
                    "name": "<?= $this->security->get_csrf_token_name() ?>",
                    "value": "<?= $this->security->get_csrf_hash() ?>"
                });
                $.ajax({'dataType': 'json', 'type': 'POST', 'url': sSource, 'data': aoData, 'success': fnCallback});
            },
            "aoColumns": [
                {"bSortable": false, "mRender": checkbox}, 
                null, 
                null, 
                null,
                {"mRender": order_center}, 
                null, 
 
                {"mRender": row_status
                    
                },
                
         
                {"bSortable": false}
                ]
        });
        

        

    });
</script>
<?= admin_form_open('system_settings/currency_actions', 'id="action-form"') ?>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-money"></i><?= lang('Refund_Request'); ?></h2>

        <div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown">
                    <a data-toggle="dropdown" class="dropdown-toggle" href="#">
                        <i class="icon fa fa-tasks tip" data-placement="left" title="<?= lang('actions') ?>"></i>
                    </a>
                    <!--<ul class="dropdown-menu pull-right tasks-menus" role="menu" aria-labelledby="dLabel">-->
                    <!--    <li>-->
                    <!--        <a href="<?= admin_url('Blog/add_blog'); ?>">-->
                    <!--            <i class="fa fa-plus"></i> <?= lang('add_blog') ?>-->
                    <!--        </a>-->
                    <!--    </li>-->
                    <!--    <li class="divider"></li>-->
                    <!--    <li>-->
                    <!--        <a href="#" id="delete" data-action="delete">-->
                    <!--        <i class="fa fa-trash-o"></i> <?= lang('delete_blog') ?>-->
                    <!--        </a>-->
                    <!--    </li>-->
                    <!--</ul>-->
                </li>
            </ul>
        </div>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">

                <p class="introtext"><?= lang('list_results'); ?></p>
   
                <div class="table-responsive">
                    
                    <table id="" class="table table-bordered table-hover table-striped">
                        <thead>
                        <tr>
                           
                            <th><?= lang('Reference No'); ?></th>
                            <th ><?= lang('Customer Name'); ?></th>
                            <th><?= lang('Reason Refund'); ?></th>
                            <th ><?= lang('Notes'); ?></th>
                            <th><?= lang('Request Dates'); ?></th>
                            <th ><?= lang('Status'); ?></th>
                            <th><?= lang('actions'); ?></th>
                        </tr>
                        </thead>
                         <?php  foreach($refund as $row)
                            {
                                $oId=$row->order_id;
                                 $id=$row->id;
                       // dd($refund);
    ?> 
                        <tbody>
       




            <tr>
               
                <td><? echo $row->reference_no;?></td>
                <td><? echo $row->customer;?></td>
                <td><? echo $row->reason_refund;?></td>
                <td><? echo $row->notes;?></td>
                <td><? echo $row->req_dates;?></td>
                <td><? echo $row->refund_status;?></td>
                <td colspan="4">
                      <?php
                
                if($row->refund_status=="success"){?>
              
                       <button><a onclick="return confirm('Are you sure You want to Delete?')"  href="<?php  echo  admin_url('Refund/delete_refund/'.$id) ?>"target=""><i class="fa fa-trash-o"></i>Refund</a></button> 
                
                 
                        <button>     <a href="<?php  echo  admin_url('Refund/cancel_refund/'.$id) ?>"   onclick="return confirm('Are you sure you want to cancel?')" target=""><i class="fa fa-close"></i>Cancel</a></button> 
   
                
                   
                    
            <?php   }
                else{   ?>
                   <button> <a href="<?php echo base_url('admin/payments/directpayRefund/'.$oId.'/'.$id)  ?>"><i id='refund' class="fa fa-money"></i>Refund</a></button>
                  
                   <button><a class="" onclick="return confirm('Are you sure You want to Delete? ')"   target=""> 
                   
                    <i class="fa fa-trash-o"></i>Delete</a></button>
                   <button>  <a href="<?php  echo  admin_url('Refund/cancel_refund/'.$id) ?>"  onclick="return confirm('Are you sure you want to cancel?')" target=""><i class="fa fa-close"></i>Cancel</a></button>
             
               <?php }
                
                ?>
                   
              
                
                    
                </td>
            </tr>
    <?php }?>

                      
                       
                        </tbody>
                    </table>
                </div>

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

  
        

    });
  
    
</script>

