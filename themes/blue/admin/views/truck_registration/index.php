<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<script>
    $(document).ready(function () {

        oTable = $('#NTTable').dataTable({
            "aaSorting": [[1, "asc"], [2, "asc"]],
            "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
            "iDisplayLength": <?= $Settings->rows_per_page ?>,
            'bProcessing': true, 'bServerSide': true,
            'sAjaxSource': '<?= admin_url('truck_registration/getTrucks') ?>',
            'fnServerData': function (sSource, aoData, fnCallback) {
                aoData.push({
                    "name": "<?= $this->security->get_csrf_token_name() ?>",
                    "value": "<?= $this->security->get_csrf_hash() ?>"
                });
                $.ajax({'dataType': 'json', 'type': 'POST', 'url': sSource, 'data': aoData, 'success': fnCallback});
            },
            // "aoColumns": [null, {"mRender": fsd}, {"mRender": fsd}, {"mRender": fsd}, {"bSortable": false}]

            "columnDefs": [
            {
                render: function (aoData, type, row) {
                return aoData + ' (' + row[3] + ')';
            },
            targets: 0,
            },
            { visible: false, targets: [3] },
            ],

        });

    });
</script>

<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-info-circle"></i><?= lang('notifications'); ?></h2>

        <div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown"><a href="<?= admin_url('truck_registration/add'); ?>"><i class="icon fa fa-plus"></i></a></li>
            </ul>
        </div>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">

                <p class="introtext"><?= lang('list_results'); ?></p>

                <div class="table-responsive">
                    <table id="NTTable" cellpadding="0" cellspacing="0" border="0"
                           class="table table-bordered table-hover table-striped">
                        <thead>
                        <tr style="text-align:center;">
                           <!-- <th style="min-width:30px; width: 30px; text-align: center;">
                                <input class="checkbox checkth" type="checkbox" name="check"/>
                            </th> -->
                            <th><?php echo $this->lang->line('Truck No'); ?> </th>
                            <th><?php echo $this->lang->line('Reference no') ?></th>
                            <th><?php echo $this->lang->line('Date'); ?></th>
                            <th><?php echo $this->lang->line('Time'); ?></th>
                            <th><?php echo $this->lang->line('actions'); ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td  class="dataTables_empty"><?= lang('loading_data_from_server') ?></td>
                        </tr>

                        </tbody>
                    </table>
                </div>
                <!--<p><a href="<?php echo admin_url('notifications/add'); ?>" class="btn btn-primary" data-toggle="modal" data-target="#myModal"><?php echo $this->lang->line('add_notification'); ?></a></p>-->
            </div>
        </div>
    </div>
</div>

