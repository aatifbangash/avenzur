<?php defined('BASEPATH') or exit('No direct script access allowed'); 
?>

<?php if ($Owner || ($GP && $GP['bulk_actions'])) {
           // echo admin_form_open('sales/qty_onhold_actions', 'id="action-form"');
}
?>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i
                class="fa-fw fa fa-heart"></i><?=lang('Quantity Requests');?>
        </h2>

    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">

                <p class="introtext"><?=lang('list_results');?></p>

                <div class="table-responsive">
                    <table id="SLData" class="table table-bordered table-hover table-striped" cellpadding="0" cellspacing="0" border="0">
                        <thead>
                        <tr>
                            <th style="min-width:30px; width: 30px; text-align: center;">
                                <input class="checkbox checkft" type="checkbox" name="check"/>
                            </th>
                            
                            <th>Sale ID</th>
                            <th>Product Code</th>
                            <th>Product Name</th>
                            <th>Product Customer</th>
                            <th>Quantity</th>
                            <th>Status</th>
                            
                            <th style="width:80px; text-align:center;"><?= lang('actions'); ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php 
                        if(isset($results) && !empty($results) ) {
                        foreach($results as $item) { ?>
                        <tr>
                           
                        <td style="min-width:30px; width: 30px; text-align: center;">
                                <input class="checkbox" type="checkbox" name="requests[]" value="<?php echo $item->id;?>"/>
                            </td>
                            <td><?php echo $item->sale_id;?></td>
                            <td><?php echo $item->product_code;?></td>
                            <td><?php echo $item->product_name;?></td>
                            <td><?php echo $item->customer_name;?></td>
                            <td><?php echo $item->quantity;?></td>
                            <td><?php echo $item->status;?></td>
                            <td>Action</td>
                           
                        </tr>
                        <?php } }else {?>
                            <tr>
                           
                           <td colspan="6">No Onhold Record found</td>
                           
                       </tr>
                            <?php }?>
                        </tbody>
                        
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?php if ($Owner || ($GP && $GP['bulk_actions'])) {
    ?>
    <div style="display: none;">
        <input type="hidden" name="form_action" value="" id="form_action"/>
        <?=form_submit('performAction', 'performAction', 'id="action-form-submit"')?>
    </div>
    <?=form_close()?>
    <?php
}
?>
