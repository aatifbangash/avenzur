<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<script>
    $(document).ready(function () {
       
        oTable = $('#CURData').dataTable();
    });
</script>

<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-building-o"></i><?= $warehouse->name ?> <a href="<?=admin_url('system_settings/warehouses') ?>">( Go Back )</a></h2>
        
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
                <p class="introtext"><?= lang('list_results'); ?></p>

                <div class="table-responsive">
                    <table id="CURData" class="table table-bordered table-hover table-striped table-condensed reports-table">
                        <thead>
                        <tr>
                            <th class="col-xs-3"><?= lang('Shelf Name'); ?></th>
                            <th style="width:65px;"><?= lang('actions'); ?></th>
                        </tr>
                        </thead>
                        <tbody>
                            
                            <?php  if(!empty($shelves))
                            {
                                foreach ($shelves as $shelf) 
                                {
                                    ?>

                                    <tr>
                                        <td><?= $shelf['shelf_name']?></td>
                                        <td><a class="tip"  href='<?php echo admin_url('system_settings');?>/delete_warehouse_shelf/<?=$shelf['id'] ?>/<?=$warehouse->id ?>'><i class="fa fa-trash-o"></i></a></td>
                                    </tr>


                           <?php         
                                }
                            } ?>

                        </tbody>
                    </table>
                </div>

            </div>

        </div>
    </div>
</div>


<?= form_close() ?>

