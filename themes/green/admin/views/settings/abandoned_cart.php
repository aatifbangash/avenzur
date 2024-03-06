<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<script>
    $(document).ready(function() {

    });
</script>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-users"></i><?= lang('abandoned_cart'); ?></h2>

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
            echo admin_form_open_multipart('reports/abandoned_cart', $attrib)
            ?>
            <div class="col-lg-12">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="col-md-4">
                            <div class="form-group">
                                <?= lang('From Date', 'podate'); ?>
                                <?php echo form_input('from_date', ($start_date ?? ''), 'class="form-control input-tip date" id="fromdate"'); ?>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <?= lang('To Date', 'podate'); ?>
                                <?php echo form_input('to_date', ($end_date ?? ''), 'class="form-control input-tip date" id="todate"'); ?>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="from-group">
                                <button type="submit" style="margin-top: 28px;" class="btn btn-primary" id="load_report"><?= lang('Load Report') ?></button>
                            </div>
                        </div>

                    </div>
                </div>
                <hr />
                <div class="row">
                    <div class="controls table-controls" style="font-size: 12px !important;">
                        <table id="poTable" class="table items table-striped table-bordered table-condensed table-hover sortable_table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th><?= lang('customerId'); ?></th>
                                    <th><?= lang('email'); ?></th>
                                    <th><?= lang('time'); ?></th>
                                    <th><?= lang('cart_total'); ?></th>
                                    <th><?= lang('total_items'); ?></th>
                                    <th><?= lang('data'); ?></th>
                                </tr>
                            </thead>
                            <tbody style="text-align:center;">
                                <?php
                                $count = 1;
                                foreach ($abandoned_cart_array as $rec) {
                                    $cart_obj = json_decode($rec->data);
                                    $data_col = "";
                                    foreach ($cart_obj as $key => $value) {
                                        if (is_object($value)) {
                                            $data_col .= $value->name.' ('.$value->code.') <span style="color:blue;">Quantity:  '.$value->qty."</span><br />";
                                            
                                        }
                                    }
                                ?>
                                    <tr>
                                        <td><?= $count; ?></td>
                                        <td><?= $rec->user_id; ?></td>
                                        <td><?= $rec->email; ?></td>
                                        <td><?= date('Y-m-d H:i:s', $rec->time); ?></td>
                                        <td><?= $cart_obj->cart_total; ?></td>
                                        <td><?= $cart_obj->total_items; ?></td>
                                        <td><?= $data_col; ?></td>
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