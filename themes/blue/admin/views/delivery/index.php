<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<script>
    $(document).ready(function () {
        oTable = $('#deliveryData').dataTable({
            "aaSorting": [[1, "desc"]],
            "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?=lang('all')?>"]],
            "iDisplayLength": <?=$Settings->rows_per_page?>,
            'bProcessing': true, 'bServerSide': true,
            'sAjaxSource': '<?=admin_url('delivery/get_deliveries')?>',
            'fnServerData': function (sSource, aoData, fnCallback) {
                aoData.push({
                    "name": "<?=$this->security->get_csrf_token_name()?>",
                    "value": "<?=$this->security->get_csrf_hash()?>"
                });
                $.ajax({'dataType': 'json', 'type': 'POST', 'url': sSource, 'data': aoData, 'success': fnCallback});
            },
            "aoColumns": [null, null, null, null, {"mRender": function(data) {
                var map = {
                    'pending':            '<span class="label label-default">Pending</span>',
                    'driver_assigned':    '<span class="label label-info">Driver Assigned</span>',
                    'out_for_delivery':   '<span class="label label-warning">Out for Delivery</span>',
                    'delivered':          '<span class="label label-success">Delivered</span>',
                    'cancelled':          '<span class="label label-danger">Cancelled</span>'
                };
                return map[data] || '<span class="label label-default">' + (data || 'N/A') + '</span>';
            }}, {"mRender": function(data) {
                if (!data) return '<span class="text-muted">—</span>';
                return '<a href="<?=base_url('files/receipts/')?>'+data+'" target="_blank" class="btn btn-xs btn-info"><i class="fa fa-file"></i> View</a>';
            }, "bSortable": false}, {"bSortable": false}],
            'fnRowCallback': function (nRow, aData, iDisplayIndex) {
                var oSettings = oTable.fnSettings();
                nRow.id = aData[0];
                nRow.className = "delivery_link";
                return nRow;
            }
        }).fnSetFilteringDelay().dtFilter([
            {column_number: 1, filter_default_label: "[<?=lang('date');?> (yyyy-mm-dd)]", filter_type: "text", data: []},
            {column_number: 2, filter_default_label: "[<?=lang('driver');?>]", filter_type: "text", data: []},
            {column_number: 3, filter_default_label: "[<?=lang('truck');?>]", filter_type: "text", data: []},
            {column_number: 4, filter_default_label: "[Status]", filter_type: "select", data: ["pending", "driver_assigned", "out_for_delivery", "delivered", "cancelled"]},
        ], "footer");
    });
</script>

<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-truck"></i> Deliveries
    </div>
    <div class="box-content">
        <div class="table-responsive">
            <table class="table table-striped table-hover table-bordered" id="deliveryData">
                <thead>
                    <tr class="header">
                        <th><?=lang('id')?></th>
                        <th><?=lang('date')?></th>
                        <th><?=lang('driver')?></th>
                        <th><?=lang('truck')?></th>
                        <th>Status</th>
                        <th>Receipt</th>
                        <th><?=lang('actions')?></th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
                <tfoot>
                    <tr>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th></th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
