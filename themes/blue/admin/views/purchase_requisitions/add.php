<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-file-text"></i><?= lang('purchase_requisitions'); ?></h2>
        <div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown">
                    <a href="<?= admin_url('purchase_requisitions/add') ?>" class="tip" title="<?= lang('add_requisition') ?>">
                        <i class="icon fa fa-plus"></i>
                    </a>
                </li>
            </ul>
        </div>
    </div>

    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
                <div class="table-responsive">
                    <table id="PRQData" class="table table-bordered table-hover table-striped">
                        <thead>
                        <tr>
                            <th><?= lang('id'); ?></th>
                            <th><?= lang('item_name'); ?></th>
                            <th><?= lang('quantity'); ?></th>
                            <th><?= lang('unit_price'); ?></th>
                            <th><?= lang('amount'); ?></th>
                            <th><?= lang('tax_vat'); ?></th>
                            <th><?= lang('status'); ?></th>
                            <th><?= lang('supplier_name'); ?></th>
                            <th><?= lang('created_by'); ?></th>
                            <th><?= lang('created_date'); ?></th>
                            <th><?= lang('updated_date'); ?></th>
                            <th><?= lang('actions'); ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td colspan="12" class="dataTables_empty"><?= lang('loading_data_from_server'); ?></td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function () {
        oTable = $('#PRQData').dataTable({
            "aaSorting": [[0, "desc"]],
            "aLengthMenu": [[10, 25, 50, 100, -1],
                [10, 25, 50, 100, "<?= lang('all') ?>"]],
            "iDisplayLength": <?= $Settings->rows_per_page ?>,
            'bProcessing': true,
            'bServerSide': true,
            'sAjaxSource': '<?= admin_url('purchase_requisitions/getPurchaseRequisitions') ?>',
            'fnServerData': function (sSource, aoData, fnCallback) {
                aoData.push({
                    "name": "<?= $this->security->get_csrf_token_name() ?>",
                    "value": "<?= $this->security->get_csrf_hash() ?>"
                });
                $.ajax({
                    'dataType': 'json',
                    'type': 'GET',
                    'url': sSource,
                    'data': aoData,
                    'success': fnCallback
                });
            }
        });
    });
</script>
