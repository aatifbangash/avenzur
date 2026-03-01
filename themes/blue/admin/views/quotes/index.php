<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<script>
    $(document).ready(function () {
    var oTable = $('#QUData').dataTable({
        "aaSorting": [[1, "desc"], [2, "desc"]],
        "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
        "iDisplayLength": <?= $Settings->rows_per_page ?>,
        'bProcessing': true, 
        'bServerSide': true,
        'sAjaxSource': '<?= admin_url('quotes/getQuotes' . ($warehouse_id ? '/' . $warehouse_id : '')) ?>',
        'fnServerData': function (sSource, aoData, fnCallback) {
            aoData.push({
                "name": "<?= $this->security->get_csrf_token_name() ?>",
                "value": "<?= $this->security->get_csrf_hash() ?>"
            });
            $.ajax({'dataType': 'json', 'type': 'POST', 'url': sSource, 'data': aoData, 'success': fnCallback});
        },
        'fnRowCallback': function (nRow, aData, iDisplayIndex) {
            nRow.id = aData[0];
            nRow.className = "quote_link";
            return nRow;
        },
        "aoColumns": [
            {"bSortable": false, "mRender": checkbox}, 
            {"mRender": fld}, 
            null, null, null, 
            {"mRender": currencyFormat}, 
            {"mRender": currencyFormat}, 
            {"mRender": currencyFormat}, 
            {"mRender": currencyFormat}, 
            {
                "mRender": function(data, type, row) {
                    if(data === 'converted_to_sale'){
                        return '<span class="badge badge-success" style="background-color: #28a745 !important; color: white;">Converted</span>';
                    } else {
                        return '<span class="badge badge-secondary" style="color:white; background-color:orange;">'+data+'</span>';
                    }
                }
            }, 
            {"bSortable": false, "mRender": attachment2}, 
            {"bSortable": false}
        ]
    }).fnSetFilteringDelay().dtFilter([
        {column_number: 1, filter_default_label: "[<?=lang('date');?> (yyyy-mm-dd)]", filter_type: "text", data: []},
        {column_number: 2, filter_default_label: "[<?=lang('reference_no');?>]", filter_type: "text", data: []},
        {column_number: 3, filter_default_label: "[<?=lang('biller');?>]", filter_type: "text", data: []},
        {column_number: 4, filter_default_label: "[<?=lang('customer');?>]", filter_type: "text", data: []},
        {column_number: 5, filter_default_label: "[<?=lang('total');?>]", filter_type: "text", data: []},
        {column_number: 6, filter_default_label: "[<?=lang('total_discount');?>]", filter_type: "text", data: []},
        {column_number: 7, filter_default_label: "[<?=lang('total_tax');?>]", filter_type: "text", data: []},
        {column_number: 8, filter_default_label: "[<?=lang('grand_total');?>]", filter_type: "text", data: []},
        {column_number: 9, filter_default_label: "[<?=lang('status');?>]", filter_type: "text", data: []},
    ], "footer");
});

if (localStorage.getItem('remove_slls')) {
    if (localStorage.getItem('qtitems')) {
        localStorage.removeItem('qtitems');
    }
    if (localStorage.getItem('sldiscount')) {
        localStorage.removeItem('sldiscount');
    }
    if (localStorage.getItem('sltax2')) {
        localStorage.removeItem('sltax2');
    }
    if (localStorage.getItem('slref')) {
        localStorage.removeItem('slref');
    }
    if (localStorage.getItem('slshipping')) {
        localStorage.removeItem('slshipping');
    }
    if (localStorage.getItem('slwarehouse')) {
        localStorage.removeItem('slwarehouse');
    }
    if (localStorage.getItem('slnote')) {
        localStorage.removeItem('slnote');
    }
    if (localStorage.getItem('slinnote')) {
        localStorage.removeItem('slinnote');
    }
    if (localStorage.getItem('qtcustomer')) {
        localStorage.removeItem('qtcustomer');
    }
    if (localStorage.getItem('slbiller')) {
        localStorage.removeItem('slbiller');
    }
    if (localStorage.getItem('slcurrency')) {
        localStorage.removeItem('slcurrency');
    }
    if (localStorage.getItem('sldate')) {
        localStorage.removeItem('sldate');
    }
    if (localStorage.getItem('qtquote_status')) {
        localStorage.removeItem('qtquote_status');
    }
    if (localStorage.getItem('slpayment_status')) {
        localStorage.removeItem('slpayment_status');
    }
    if (localStorage.getItem('paid_by')) {
        localStorage.removeItem('paid_by');
    }
    if (localStorage.getItem('amount_1')) {
        localStorage.removeItem('amount_1');
    }
    if (localStorage.getItem('paid_by_1')) {
        localStorage.removeItem('paid_by_1');
    }
    if (localStorage.getItem('pcc_holder_1')) {
        localStorage.removeItem('pcc_holder_1');
    }
    if (localStorage.getItem('pcc_type_1')) {
        localStorage.removeItem('pcc_type_1');
    }
    if (localStorage.getItem('pcc_month_1')) {
        localStorage.removeItem('pcc_month_1');
    }
    if (localStorage.getItem('pcc_year_1')) {
        localStorage.removeItem('pcc_year_1');
    }
    if (localStorage.getItem('pcc_no_1')) {
        localStorage.removeItem('pcc_no_1');
    }
    if (localStorage.getItem('cheque_no_1')) {
        localStorage.removeItem('cheque_no_1');
    }
    if (localStorage.getItem('payment_note_1')) {
        localStorage.removeItem('payment_note_1');
    }
    if (localStorage.getItem('slpayment_term')) {
        localStorage.removeItem('slpayment_term');
    }
    localStorage.removeItem('remove_slls');
}

<?php if ($this->session->userdata('remove_slls')) {
?>

if (localStorage.getItem('qtitems')) {
    localStorage.removeItem('qtitems');
}
if (localStorage.getItem('sldiscount')) {
    localStorage.removeItem('sldiscount');
}
if (localStorage.getItem('sltax2')) {
    localStorage.removeItem('sltax2');
}
if (localStorage.getItem('slref')) {
    localStorage.removeItem('slref');
}
if (localStorage.getItem('slshipping')) {
    localStorage.removeItem('slshipping');
}
if (localStorage.getItem('slwarehouse')) {
    localStorage.removeItem('slwarehouse');
}
if (localStorage.getItem('slnote')) {
    localStorage.removeItem('slnote');
}
if (localStorage.getItem('slinnote')) {
    localStorage.removeItem('slinnote');
}
if (localStorage.getItem('qtcustomer')) {
    localStorage.removeItem('qtcustomer');
}
if (localStorage.getItem('slbiller')) {
    localStorage.removeItem('slbiller');
}
if (localStorage.getItem('slcurrency')) {
    localStorage.removeItem('slcurrency');
}
if (localStorage.getItem('sldate')) {
    localStorage.removeItem('sldate');
}
if (localStorage.getItem('qtquote_status')) {
    localStorage.removeItem('qtquote_status');
}
if (localStorage.getItem('slpayment_status')) {
    localStorage.removeItem('slpayment_status');
}
if (localStorage.getItem('paid_by')) {
    localStorage.removeItem('paid_by');
}
if (localStorage.getItem('amount_1')) {
    localStorage.removeItem('amount_1');
}
if (localStorage.getItem('paid_by_1')) {
    localStorage.removeItem('paid_by_1');
}
if (localStorage.getItem('pcc_holder_1')) {
    localStorage.removeItem('pcc_holder_1');
}
if (localStorage.getItem('pcc_type_1')) {
    localStorage.removeItem('pcc_type_1');
}
if (localStorage.getItem('pcc_month_1')) {
    localStorage.removeItem('pcc_month_1');
}
if (localStorage.getItem('pcc_year_1')) {
    localStorage.removeItem('pcc_year_1');
}
if (localStorage.getItem('pcc_no_1')) {
    localStorage.removeItem('pcc_no_1');
}
if (localStorage.getItem('cheque_no_1')) {
    localStorage.removeItem('cheque_no_1');
}
if (localStorage.getItem('payment_note_1')) {
    localStorage.removeItem('payment_note_1');
}
if (localStorage.getItem('slpayment_term')) {
    localStorage.removeItem('slpayment_term');
}
localStorage.removeItem('remove_slls');

<?php $this->sma->unset_data('remove_slls'); } ?>

</script>

<!-- ✅ Existing HTML below stays same -->
<?php if ($Owner || ($GP && $GP['bulk_actions'])) {
    echo admin_form_open('quotes/quote_actions', 'id="action-form"');
} ?>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-heart-o"></i><?= lang('quotes') . ' (' . ($warehouse_id ? $warehouse->name : lang('all_warehouses')) . ')'; ?></h2>
        <div class="box-icon">
            <ul class="btn-tasks">
                <!--<li class="dropdown">
                    <a data-toggle="dropdown" class="dropdown-toggle" href="#"><i class="icon fa fa-tasks tip" data-placement="left" title="<?= lang('actions') ?>"></i></a>
                    <ul class="dropdown-menu pull-right" class="tasks-menus" role="menu" aria-labelledby="dLabel">
                        <li><a href="<?= admin_url('quotes/add') ?>"><i class="fa fa-plus-circle"></i> <?= lang('add_quote') ?></a></li>
                        <li><a href="#" id="excel" data-action="export_excel"><i class="fa fa-file-excel-o"></i> <?= lang('export_to_excel') ?></a></li>
                        <li><a href="#" id="combine" data-action="combine"><i class="fa fa-file-pdf-o"></i> <?=lang('combine_to_pdf')?></a></li>
                        <li class="divider"></li>
                        <li><a href="#" class="bpo" title="<b><?= $this->lang->line('delete_quotes') ?></b>"
                            data-content="<p><?= lang('r_u_sure') ?></p>
                            <button type='button' class='btn btn-danger' id='delete' data-action='delete'><?= lang('i_m_sure') ?></button> 
                            <button class='btn bpo-close'><?= lang('no') ?></button>"
                            data-html="true" data-placement="left"><i class="fa fa-trash-o"></i> <?= lang('delete_quotes') ?></a></li>
                    </ul>
                </li>-->

                <?php if (!empty($warehouses)) { ?>
                <li class="dropdown">
                    <a data-toggle="dropdown" class="dropdown-toggle" href="#"><i class="icon fa fa-building-o tip" data-placement="left" title="<?= lang('warehouses') ?>"></i></a>
                    <ul class="dropdown-menu pull-right" class="tasks-menus" role="menu" aria-labelledby="dLabel">
                        <li><a href="<?= admin_url('quotes') ?>"><i class="fa fa-building-o"></i> <?= lang('all_warehouses') ?></a></li>
                        <li class="divider"></li>
                        <?php foreach ($warehouses as $warehouse) {
                            echo '<li><a href="' . admin_url('quotes/' . $warehouse->id) . '"><i class="fa fa-building"></i>' . $warehouse->name . '</a></li>';
                        } ?>
                    </ul>
                </li>
                <?php } ?>
            </ul>
        </div>
    </div>

    <div class="box-content">
        <div class="table-responsive">
            <table id="QUData" class="table table-bordered table-hover table-striped">
                <thead>
                    <tr class="active">
                        <th style="width:30px; text-align: center;"><input class="checkbox checkft" type="checkbox" name="check"/></th>
                        <th><?= lang('date'); ?></th>
                        <th><?= lang('reference_no'); ?></th>
                        <th><?= lang('biller'); ?></th>
                        <th><?= lang('customer'); ?></th>
                        <th><?= lang('total'); ?></th>
                        <th><?= lang('total_discount'); ?></th>
                        <th><?= lang('total_tax'); ?></th>
                        <th><?= lang('grand_total'); ?></th>
                        <th><?= lang('status'); ?></th>
                        <th style="width:30px; text-align: center;"><i class="fa fa-chain"></i></th>
                        <th style="width:115px; text-align:center;"><?= lang('actions'); ?></th>
                    </tr>
                </thead>
                <tbody><tr><td colspan="12" class="dataTables_empty"><?= lang('loading_data'); ?></td></tr></tbody>
                <tfoot class="dtFilter">
                    <tr class="active">
                        <th></th><th></th><th></th><th></th><th></th><th></th><th></th><th></th><th></th><th></th><th></th><th></th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

<?php if ($Owner || ($GP && $GP['bulk_actions'])) { ?>
<div style="display: none;">
    <input type="hidden" name="form_action" value="" id="form_action"/>
    <?= form_submit('performAction', 'performAction', 'id="action-form-submit"') ?>
</div>
<?= form_close() ?>
<?php } ?>
