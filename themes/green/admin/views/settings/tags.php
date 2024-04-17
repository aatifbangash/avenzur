<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<script>
    $(document).ready(function() {

    });
</script>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-tag"></i><?= lang('Tags'); ?></h2>

        <div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown">
                    <a href="#" id="add_tag" class="tip" title="<?= lang('add_tag') ?>"><i class="icon fa fa-plus"></i></a>
                </li>
            </ul>
        </div>
    </div>
    <div class="box-content">
        <div class="row">
            <?php
            $attrib = ['data-toggle' => 'validator', 'role' => 'form'];
            echo admin_form_open_multipart('reports/abandoned_cart', $attrib)
            ?>
            <div class="col-lg-12">
                
                <div class="row">
                    <div class="controls table-controls" style="font-size: 12px !important;">
                        <table id="poTable" class="table items table-striped table-bordered table-condensed table-hover sortable_table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th><?= lang('name'); ?></th>
                                    <th><?= lang('field'); ?></th>
                                    <th><?= lang('operator'); ?></th>
                                    <th><?= lang('value'); ?></th>
                                    <th><?= lang('date'); ?></th>
                                </tr>
                            </thead>
                            <tbody style="text-align:center;">
                                <?php
                                $count = 1;
                                foreach ($tags_array as $rec) {
                                ?>
                                    <tr>
                                        <td><?= $count; ?></td>
                                        <td><?= $rec->name; ?></td>
                                        <td><?= $rec->field; ?></td>
                                        <td><?= $rec->operator; ?></td>
                                        <td><?= $rec->value; ?></td>
                                        <td><?= date('Y-m-d H:i:s', $rec->date_created); ?></td>
                                    </tr>
                                <?php
                                    $count++;
                                }
                                ?>

                            </tbody>
                            <tfoot></tfoot>
                        </table>
                    </div>

                </div>

            </div>
        </div>
        <?php echo form_close(); ?>
    </div>