<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<script>
    $(document).ready(function () {
        oTable = $('#POData').dataTable({
            "aaSorting": [[1, "desc"], [2, "desc"]],
            "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
            "iDisplayLength": <?= $Settings->rows_per_page ?>,
            'bProcessing': true, 'bServerSide': true,
            'sAjaxSource': '<?= admin_url('purchases/getPurchases' . ($warehouse_id ? '/' . $warehouse_id : '') .'?pid='.$pid. '&from=' .$pfromDate. '&to=' .$ptoDate) ?>',
            'fnServerData': function (sSource, aoData, fnCallback) {
                aoData.push({
                    "name": "<?= $this->security->get_csrf_token_name() ?>",
                    "value": "<?= $this->security->get_csrf_hash() ?>"
                });
                $.ajax({ 'dataType': 'json', 'type': 'POST', 'url': sSource, 'data': aoData, 'success': fnCallback });
            },
            "aoColumns": [{ "bSortable": false, "mRender": checkbox }, { "mRender": fld }, null, null, null, { "mRender": row_status_p }, { "mRender": currencyFormat }, { "mRender": currencyFormat }, { "mRender": currencyFormat }, { "mRender": pay_status }, { "bSortable": false, "mRender": attachment }, { "bSortable": false }],
            'fnRowCallback': function (nRow, aData, iDisplayIndex) {
                var oSettings = oTable.fnSettings();
                nRow.id = aData[0];
                nRow.className = "purchase_link";
                return nRow;
            },
            "fnFooterCallback": function (nRow, aaData, iStart, iEnd, aiDisplay) {
                var total = 0, paid = 0, balance = 0;
                for (var i = 0; i < aaData.length; i++) {
                    total += parseFloat(aaData[aiDisplay[i]][6]);
                    paid += parseFloat(aaData[aiDisplay[i]][7]);
                    balance += parseFloat(aaData[aiDisplay[i]][8]);
                }
                var nCells = nRow.getElementsByTagName('th');
                console.log(nCells)
                nCells[6].innerHTML = currencyFormat(total);
                nCells[7].innerHTML = currencyFormat(paid);
                nCells[8].innerHTML = currencyFormat(balance);
            }
        }).fnSetFilteringDelay().dtFilter([
            { column_number: 1, filter_default_label: "[<?= lang('date'); ?> (yyyy-mm-dd)]", filter_type: "text", data: [] },
            { column_number: 2, filter_default_label: "[<?= lang('ref_no'); ?>]", filter_type: "text", data: [] },
            { column_number: 3, filter_default_label: "[<?= lang('Sequence Code'); ?>]", filter_type: "text", data: [] },
            { column_number: 4, filter_default_label: "[<?= lang('supplier'); ?>]", filter_type: "text", data: [] },
            { column_number: 5, filter_default_label: "[<?= lang('purchase_status'); ?>]", filter_type: "text", data: [] },
            { column_number: 9, filter_default_label: "[<?= lang('payment_status'); ?>]", filter_type: "text", data: [] },
        ], "footer");

        <?php if ($this->session->userdata('remove_pols')) {
            ?>
            if (localStorage.getItem('poitems')) {
                localStorage.removeItem('poitems');
            }
            if (localStorage.getItem('podiscount')) {
                localStorage.removeItem('podiscount');
            }
            if (localStorage.getItem('potax2')) {
                localStorage.removeItem('potax2');
            }
            if (localStorage.getItem('poshipping')) {
                localStorage.removeItem('poshipping');
            }
            if (localStorage.getItem('poref')) {
                localStorage.removeItem('poref');
            }
            if (localStorage.getItem('powarehouse')) {
                localStorage.removeItem('powarehouse');
            }
            if (localStorage.getItem('ponote')) {
                localStorage.removeItem('ponote');
            }
            if (localStorage.getItem('posupplier')) {
                localStorage.removeItem('posupplier');
            }
            if (localStorage.getItem('pocurrency')) {
                localStorage.removeItem('pocurrency');
            }
            if (localStorage.getItem('poextras')) {
                localStorage.removeItem('poextras');
            }
            if (localStorage.getItem('podate')) {
                localStorage.removeItem('podate');
            }
            if (localStorage.getItem('postatus')) {
                localStorage.removeItem('postatus');
            }
            if (localStorage.getItem('popayment_term')) {
                localStorage.removeItem('popayment_term');
            }
            <?php $this->sma->unset_data('remove_pols');
        }
        ?>
    });


    $(document).ready(function () {
        var lastInsertedId = '<?= $last_inserted_id; ?>';

        function openModalForLastInsertedId(id) {

            $('#myModal').modal({
                remote: site.base_url + 'purchases/modal_view/' + lastInsertedId,
            });
            $('#myModal').modal('show');
        }
        lastInsertedId = '<?php echo $lastInsertedId; ?>';
        if (lastInsertedId) {
            openModalForLastInsertedId(lastInsertedId);
        }
    });

</script>

<?php if ($Owner || ($GP && $GP['bulk_actions'])) {
    echo admin_form_open('purchases/purchase_actions', 'id="action-form"');
}
?>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i
                class="fa-fw fa fa-star"></i><?= lang('purchases') . ' (' . ($warehouse_id ? $warehouse->name : lang('all_warehouses')) . ')'; ?>
        </h2>

        <div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown">
                    <a data-toggle="dropdown" class="dropdown-toggle" href="#"><i class="icon fa fa-tasks tip"
                            data-placement="left" title="<?= lang('actions') ?>"></i></a>
                    <ul class="dropdown-menu pull-right tasks-menus" role="menu" aria-labelledby="dLabel">
                        <li>
                            <a href="<?= admin_url('purchases/add') ?>">
                                <i class="fa fa-plus-circle"></i> <?= lang('add_purchase') ?>
                            </a>
                        </li>
                        <li>
                            <a href="#" id="excel" data-action="export_excel">
                                <i class="fa fa-file-excel-o"></i> <?= lang('export_to_excel') ?>
                            </a>
                        </li>
                        <li>
                            <a href="#" id="combine" data-action="combine">
                                <i class="fa fa-file-pdf-o"></i> <?= lang('combine_to_pdf') ?>
                            </a>
                        </li>
                        <li class="divider"></li>
                        <li>
                            <a href="#" class="bpo" title="<b><?= lang('delete_purchases') ?></b>"
                                data-content="<p><?= lang('r_u_sure') ?></p><button type='button' class='btn btn-danger' id='delete' data-action='delete'><?= lang('i_m_sure') ?></a> <button class='btn bpo-close'><?= lang('no') ?></button>"
                                data-html="true" data-placement="left">
                                <i class="fa fa-trash-o"></i> <?= lang('delete_purchases') ?>
                            </a>
                        </li>
                    </ul>
                </li>
                <?php if (!empty($warehouses)) {
                    ?>
                    <li class="dropdown">
                        <a data-toggle="dropdown" class="dropdown-toggle" href="#"><i class="icon fa fa-building-o tip"
                                data-placement="left" title="<?= lang('warehouses') ?>"></i></a>
                        <ul class="dropdown-menu pull-right tasks-menus" role="menu" aria-labelledby="dLabel">
                            <li><a href="<?= admin_url('purchases') ?>"><i class="fa fa-building-o"></i>
                                    <?= lang('all_warehouses') ?></a></li>
                            <li class="divider"></li>
                            <?php
                            foreach ($warehouses as $warehouse) {
                                echo '<li ' . ($warehouse_id && $warehouse_id == $warehouse->id ? 'class="active"' : '') . '><a href="' . admin_url('purchases/' . $warehouse->id) . '"><i class="fa fa-building"></i>' . $warehouse->name . '</a></li>';
                            } ?>
                        </ul>
                    </li>
                    <?php
                }
                ?>
            </ul>
        </div>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
                <!-- <p class="introtext"><?= lang('list_results'); ?></p> -->
                <?php 
                    // MARK: Filters
                ?>
                <div class="row" style="margin: 25px 0; display: flex; align-items: center;">
                    <div style="flex: 1; margin-right: 20px;">
                        <input type="text" id="pid" name="pid" class="form-control input-tip" placeholder="Purchase Number">
                    </div>

                    <div style="flex: 1;">
                        <input type="date" name="date" class="form-control input-tip" id="pfromDate" placeholder="From Date">
                    </div>

                    <div style="flex: 0; margin: 0 10px; font-size: 18px; font-weight: bold;">
                        -
                    </div>

                    <div style="flex: 1; margin-right: 20px;">
                        <input type="date" name="date" class="form-control input-tip" id="ptoDate" placeholder="To Date">
                    </div>

                    <div style="flex: 0;">
                        <input type="button" id="searchByNumber" class="btn btn-primary" value="Search">
                    </div>
                </div>

                <?php 
                    // MARK: Table
                ?>
                <div class="table-responsive">
                    <table id="POData" cellpadding="0" cellspacing="0" border="0"
                        class="table table-bordered table-hover table-striped">
                        <thead>
                            <tr class="active">
                                <th style="min-width:30px; width: 30px; text-align: center;">
                                    <input class="checkbox checkft" type="checkbox" name="check" />
                                </th>
                                <th><?= lang('date'); ?></th>
                                <th><?= lang('ref_no'); ?></th>
                                <th><?= lang('Sequence Code'); ?></th>
                                <th><?= lang('supplier'); ?></th>
                                <th><?= lang('purchase_status'); ?></th>
                                <th><?= lang('grand_total'); ?></th>
                                <th><?= lang('paid'); ?></th>
                                <th><?= lang('balance'); ?></th>
                                <th><?= lang('payment_status'); ?></th>
                                <th style="min-width:30px; width: 30px; text-align: center;"><i class="fa fa-chain"></i>
                                </th>
                                <th style="width:100px;"><?= lang('actions'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="12" class="dataTables_empty"><?= lang('loading_data_from_server'); ?></td>
                            </tr>
                        </tbody>
                        <tfoot class="dtFilter">
                            <tr class="active">
                                <th style="min-width:30px; width: 30px; text-align: center;">
                                    <input class="checkbox checkft" type="checkbox" name="check" />
                                </th>
                                <th></th>
                                <th></th>
                                <th></th>
                                <th></th>
                                <th></th>
                                <th><?= lang('grand_total'); ?></th>
                                <th><?= lang('paid'); ?></th>
                                <th><?= lang('balance'); ?></th>
                                <th></th>
                                <th style="min-width:30px; width: 30px; text-align: center;"><i class="fa fa-chain"></i>
                                </th>
                                <th style="width:100px; text-align: center;"><?= lang('actions'); ?></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?php if ($Owner || ($GP && $GP['bulk_actions'])) {
    ?>
    <div style="display: none;">
        <input type="hidden" name="form_action" value="" id="form_action" />
        <?= form_submit('performAction', 'performAction', 'id="action-form-submit"') ?>
    </div>
    <?= form_close() ?>
    <?php
}
?>

<script>
    document.getElementById('searchByNumber').addEventListener('click', function() {
        var pid = document.getElementById('pid').value;
        var pfromDate = document.getElementById('pfromDate').value;
        var ptoDate = document.getElementById('ptoDate').value;

        if (is_numeric(pid) || pid == ''){
            var paramValues = [pid, pfromDate, ptoDate];
            var paramNames = ['pid', 'from', 'to'];

            var baseUrl = window.location.href.split('?')[0];
            var queryParams = [];

            for (let index = 0; index < paramValues.length; index++) {
                if (paramValues[index]) {
                    queryParams.push(paramNames[index] + '=' + encodeURIComponent(paramValues[index]));
                }
            }

            var newUrl = baseUrl + '?' + queryParams.join('&');
            window.location.href = newUrl;
        } else {
            alert("Please enter a valid purchase number."); 
        }
    });
</script>