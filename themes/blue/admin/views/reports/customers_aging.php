<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<script>
    $(document).ready(function () {
        
    });
</script>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-users"></i><?= lang('customer_aging_report'); ?></h2>

        <div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown"><a href="#" id="xls" class="tip" title="<?= lang('download_xls') ?>"><i class="icon fa fa-file-excel-o"></i></a></li>
                <li class="dropdown"><a href="#" id="image" class="tip" title="<?= lang('save_image') ?>"><i class="icon fa fa-file-picture-o"></i></a></li>
            </ul>
        </div>
    </div>
    <div class="box-content">
        <div class="row">
        <?php
            $attrib = ['data-toggle' => 'validator', 'role' => 'form'];
            
        ?>
        <div class="col-lg-12">
                <div class="row">
                    <div class="controls table-controls" style="font-size: 12px !important;">
                        <table id="poTable"
                                class="table items table-striped table-bordered table-condensed table-hover sortable_table">
                            <thead>
                            <tr>
                                <th>#</th>
                                <th><?= lang('Customer'); ?></th>
                                <th><?= lang('Current'); ?></th>
                                <th><?= lang('31-60'); ?></th>
                                <th><?= lang('61-90'); ?></th>
                                <th><?= lang('91-120'); ?></th>
                                <th><?= lang('>120'); ?></th>
                            </tr>
                            </thead>
                            <tbody style="text-align:center;">
                                <?php
                                    $count = 0;
                                    foreach ($supplier_aging as $key => $data){
                                        $data = (array)$data;
                                        $count++;
                                        ?>
                                            <tr>
                                                <td><?= $count; ?></td>
                                                <td><?= $data['customer_name']; ?></td>
                                                <td><?= $data['Current']; ?></td>
                                                <td><?= $data['31-60']; ?></td>
                                                <td><?= $data['61-90']; ?></td>
                                                <td><?= $data['91-120']; ?></td>
                                                <td><?= $data['>120']; ?></td>
                                            </tr>
                                        <?php
                                    }
                                ?>
                                
                            </tbody>
                            <tfoot></tfoot>
                        </table>
                    </div>
                
            </div>

        </div>
    </div>
    
</div>
