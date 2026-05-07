<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<script>
    $(document).ready(function () {
        oTable = $('#CSData').dataTable({
            "aaSorting": [[1, "desc"], [2, "desc"]],
            "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?=lang('all')?>"]],
            "iDisplayLength": <?=$Settings->rows_per_page?>,
            'bProcessing': true, 'bServerSide': true,
            'sAjaxSource': '<?=admin_url('sales/getCompletedSales' . ($warehouse_id ? '/' . $warehouse_id : '') . '?sid=' . ($sid ?? '') . '&v=1' . ($this->input->get('from_date') ? '&from_date=' . urlencode($this->input->get('from_date')) : '') . ($this->input->get('to_date') ? '&to_date=' . urlencode($this->input->get('to_date')) : '')); ?>',
            'fnServerData': function (sSource, aoData, fnCallback) {
                aoData.push({
                    "name": "<?=$this->security->get_csrf_token_name()?>",
                    "value": "<?=$this->security->get_csrf_hash()?>"
                });
                $.ajax({'dataType': 'json', 'type': 'POST', 'url': sSource, 'data': aoData, 'success': fnCallback});
            },
            'fnRowCallback': function (nRow, aData, iDisplayIndex) {
                nRow.id = aData[0];
                nRow.setAttribute('data-return-id', aData[11]);
                nRow.className = "invoice_link re" + aData[11];
                return nRow;
            },
            "aoColumns": [
                {"bSortable": false, "mRender": checkbox},
                null,
                {"mRender": fld},
                null, null, null, null,
                {"mRender": row_status},
                {"mRender": currencyFormat},
                {"mRender": currencyFormat},
                {"mRender": currencyFormat},
                {"mRender": pay_status},
                {"bSortable": false, "mRender": attachment},
                {"bVisible": false},
                {"bSortable": false}
            ],
            "fnFooterCallback": function (nRow, aaData, iStart, iEnd, aiDisplay) {
                var gtotal = 0, paid = 0, balance = 0;
                for (var i = 0; i < aaData.length; i++) {
                    gtotal  += parseFloat(aaData[aiDisplay[i]][8]);
                    paid    += parseFloat(aaData[aiDisplay[i]][9]);
                    balance += parseFloat(aaData[aiDisplay[i]][10]);
                }
                var nCells = nRow.getElementsByTagName('th');
                nCells[8].innerHTML  = currencyFormat(parseFloat(gtotal));
                nCells[9].innerHTML  = currencyFormat(parseFloat(paid));
                nCells[10].innerHTML = currencyFormat(parseFloat(balance));
            }
        }).fnSetFilteringDelay().dtFilter([
            {column_number: 1,  filter_default_label: "[<?=lang('number');?>]",        filter_type: "text", data: []},
            {column_number: 2,  filter_default_label: "[<?=lang('date');?> (yyyy-mm-dd)]", filter_type: "text", data: []},
            {column_number: 3,  filter_default_label: "[<?=lang('reference_no');?>]",  filter_type: "text", data: []},
            {column_number: 4,  filter_default_label: "[<?=lang('biller');?>]",        filter_type: "text", data: []},
            {column_number: 5,  filter_default_label: "[<?=lang('customer');?>]",      filter_type: "text", data: []},
            {column_number: 10, filter_default_label: "[<?=lang('payment_status');?>]",filter_type: "text", data: []},
        ], "footer");
    });
</script>

<div class="box">
    <div class="box-header">
        <h2 class="blue">
            <i class="fa-fw fa fa-check-circle"></i>
            Completed Sales <?= $warehouse_id ? '(' . $warehouse->name . ')' : '(' . lang('all_warehouses') . ')' ?>
        </h2>
        <div class="box-icon"></div>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">

                <!-- Filters -->
                <div class="row" style="margin-bottom:8px;">
                    <div class="col-md-2">
                        <label><?=lang('from_date')?></label>
                        <input type="text" id="filterFromDate" class="form-control input-tip date" placeholder="From Date" value="<?=htmlspecialchars($this->input->get('from_date') ?? '')?>">
                    </div>
                    <div class="col-md-2">
                        <label><?=lang('to_date')?></label>
                        <input type="text" id="filterToDate" class="form-control input-tip date" placeholder="To Date" value="<?=htmlspecialchars($this->input->get('to_date') ?? '')?>">
                    </div>
                    <div class="col-md-3">
                        <label><?=lang('Sale #')?></label>
                        <input type="text" id="sid" class="form-control input-tip" placeholder="Serial #" value="<?=htmlspecialchars($sid ?? '')?>">
                    </div>
                    <div class="col-md-2">
                        <label>&nbsp;</label><br>
                        <input type="button" id="searchByNumber" class="btn btn-primary btn-block" value="<?=lang('search')?>">
                    </div>
                </div>

                <div class="table-responsive">
                    <table id="CSData" class="table table-bordered table-hover table-striped" cellpadding="0" cellspacing="0" border="0">
                        <thead>
                        <tr>
                            <th style="min-width:30px; width:30px; text-align:center;">
                                <input class="checkbox checkft" type="checkbox" name="check"/>
                            </th>
                            <th><?=lang('Sale #')?></th>
                            <th><?=lang('date')?></th>
                            <th><?=lang('reference_no')?></th>
                            <th>Code</th>
                            <th><?=lang('biller')?></th>
                            <th><?=lang('customer')?></th>
                            <th><?=lang('sale_status')?></th>
                            <th><?=lang('grand_total')?></th>
                            <th><?=lang('paid')?></th>
                            <th><?=lang('balance')?></th>
                            <th><?=lang('payment_status')?></th>
                            <th style="min-width:30px; width:30px; text-align:center;"><i class="fa fa-chain"></i></th>
                            <th></th>
                            <th style="width:80px; text-align:center;"><?=lang('actions')?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td colspan="12" class="dataTables_empty"><?=lang('loading_data')?></td>
                        </tr>
                        </tbody>
                        <tfoot class="dtFilter">
                        <tr class="active">
                            <th style="min-width:30px; width:30px; text-align:center;">
                                <input class="checkbox checkft" type="checkbox" name="check"/>
                            </th>
                            <th></th><th></th><th></th><th></th><th></th><th></th><th></th>
                            <th><?=lang('grand_total')?></th>
                            <th><?=lang('paid')?></th>
                            <th><?=lang('balance')?></th>
                            <th></th>
                            <th style="min-width:30px; width:30px; text-align:center;"><i class="fa fa-chain"></i></th>
                            <th></th>
                            <th style="width:80px; text-align:center;"><?=lang('actions')?></th>
                        </tr>
                        </tfoot>
                    </table>
                </div>

            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function () {
    if ($.fn.select2) {
        // no multi-selects on this page currently
    }
});

document.getElementById('searchByNumber').addEventListener('click', function () {
    var sidValue = document.getElementById('sid').value.trim();
    var fromDate = document.getElementById('filterFromDate').value.trim();
    var toDate   = document.getElementById('filterToDate').value.trim();

    var params = [];
    if (sidValue) params.push('sid='       + encodeURIComponent(sidValue));
    if (fromDate) params.push('from_date=' + encodeURIComponent(fromDate));
    if (toDate)   params.push('to_date='   + encodeURIComponent(toDate));

    var baseUrl = window.location.href.split('?')[0];
    window.location.href = params.length ? baseUrl + '?' + params.join('&') : baseUrl;
});
</script>
