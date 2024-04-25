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
                    <a href="<?php echo admin_url('shop_settings/addTag'); ?>" id="add_tag" data-toggle="modal" data-target="#myModal" class="tip" title="<?= lang('add_tag') ?>">
                        <i class="icon fa fa-plus"></i>
                    </a>
                </li>
            </ul>
        </div>
    </div>
    <div class="box-content">
        <div class="row">
            
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
                                    <th><?= lang('actions'); ?></th>

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
                                        <td><?= $rec->date_created; ?></td>
                                        <td>
                                            <?php 
                                                if($rec->status == 0){
                                                    ?>
                                                        <button onclick="runUpdate('<?= $rec->id;  ?>');">Run Update</button>
                                                    <?php
                                                }else{
                                                    ?>
                                                        <button>De-Activate Tag</button>
                                                    <?php
                                                }
                                            ?>
                                        </td>
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
        <script type="text/javascript">
            function runUpdate(id){
                var $form = $('<form>', {
                    'action': site.base_url + 'shop_settings/activate_tag',
                    'method': 'POST'
                });

                var $inputId = $('<input>', {
                    'type': 'hidden',
                    'name': 'id',
                    'value': id
                });

                var $inputCsrf = $('<input>', {
                    'type': 'hidden',
                    'name': '<?= $this->security->get_csrf_token_name() ?>',
                    'value': '<?= $this->security->get_csrf_hash() ?>'
                });

                $form.append($inputCsrf);
                $form.append($inputId);

                $('body').append($form);
                $form.submit();
            }
        </script>
    </div>