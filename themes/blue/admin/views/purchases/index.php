<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<script>
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
                        <input type="text" id="pid" name="pid" class="form-control input-tip"
                            placeholder="Purchase Number">
                    </div>

                    <div style="flex: 1;">
                        <input type="date" name="date" class="form-control input-tip" id="pfromDate"
                            placeholder="From Date">
                    </div>

                    <div style="flex: 0; margin: 0 10px; font-size: 18px; font-weight: bold;">
                        -
                    </div>

                    <div style="flex: 1; margin-right: 20px;">
                        <input type="date" name="date" class="form-control input-tip" id="ptoDate"
                            placeholder="To Date">
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
                                <th style="min-width:30px; width: 30px; text-align: center;">No</th>
                                <th><?= lang('date'); ?></th>
                                <th style="width: 20px"><?= lang('ref_no'); ?></th>
                                <!-- <th><?= lang('Sequence Code'); ?></th> -->
                                <th><?= lang('supplier'); ?></th>
                                <th style="width: 20px"><?= lang('Status'); ?></th>
                                <th style="width: 20px"><?= lang('grand_total'); ?></th>
                                <!-- <th><?= lang('paid'); ?></th> -->
                                <!-- <th><?= lang('balance'); ?></th> -->
                                <!-- <th><?= lang('payment_status'); ?></th> -->
                                <!-- <th style="min-width:30px; width: 30px; text-align: center;"><i class="fa fa-chain"></i>
                                </th> -->
                                <th>Transfer Status</th>
                                <th>Transfer Id</th>
                                <th>Transfer At</th>

                                <th style="width:100px;"><?= lang('actions'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if (!empty($purchases)): ?>
                                <?php foreach ($purchases as $purchase):
                                    $pid = $purchase->id;
                                    $detail_link = anchor('admin/purchases/view/'.$pid, '<i class="fa fa-file-text-o"></i> ' . lang('purchase_details'));
                                    $payments_link = anchor('admin/purchases/payments/'.$pid, '<i class="fa fa-money"></i> ' . lang('view_payments'), 'data-toggle="modal" data-target="#myModal"');
                                    $transfer_link = anchor('admin/purchases/transfer/'.$pid, '<i class="fa fa-money"></i> ' . lang('Transfer to Pharmacy'), 'data-toggle="modal" data-target="#myModal"');
                                    $journal_entry_link = anchor('admin/entries/view/journal/?pid='.$pid, '<i class="fa fa-eye"></i> ' . lang('Journal Entry'));
                                    
                                    $add_payment_link = anchor('admin/purchases/add_payment/'.$pid, '<i class="fa fa-money"></i> ' . lang('add_payment'), 'data-toggle="modal" data-target="#myModal"');

                                    $email_link = anchor('admin/purchases/email/'.$pid, '<i class="fa fa-envelope"></i> ' . lang('email_purchase'), 'data-toggle="modal" data-target="#myModal"');
                                    $edit_link = anchor('admin/purchases/edit/'.$pid, '<i class="fa fa-edit"></i> ' . lang('edit_purchase'));
                                    $pdf_link = anchor('admin/purchases/pdf/'.$pid, '<i class="fa fa-file-pdf-o"></i> ' . lang('download_pdf'));
                                    $print_barcode = anchor('admin/products/print_barcodes/?purchase='.$pid, '<i class="fa fa-print"></i> ' . lang('print_barcodes'));
                                    $return_link = anchor('admin/returns_supplier/add/?purchase='.$pid, '<i class="fa fa-angle-double-left"></i> ' . lang('return_purchase'));
                                    $delete_link = "<a href='#' class='po' title='<b>" . $this->lang->line('delete_purchase') . "</b>' data-content=\"<p>"
                                        . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . admin_url('purchases/delete/'.$pid) . "'>"
                                        . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
                                        . lang('delete_purchase') . '</a>';

                                    ?>
                                    <tr class="purchase_link" id="<?=$pid?>">
                                        <td><?= $purchase->id ?></td>
                                        <td><?= $purchase->date ?></td>
                                        <td><?= $purchase->reference_no ?></td>
                                        <!-- <td><?= $purchase->sequence_code ?></td> -->
                                        <td><?= $purchase->supplier ?></td>
                                        <td><?= $purchase->status ?></td>
                                        <td><?= number_format($purchase->grand_total, 2) ?></td>
                                        <!-- <td><?= number_format($purchase->paid, 2) ?></td> -->
                                        <!-- <td><?= number_format($purchase->grand_total - $purchase->paid, 2) ?></td> -->
                                        <!-- <td><?= $purchase->payment_status ?></td> -->
                                        <!-- <td><?= $purchase->attachment ?></td> -->
                                        <td> <?= $purchase->is_transfer == 1 ? "Transferred" : "Pending"; ?> </td>
                                        <td> <?= $purchase->transfer_id > 0 ? $purchase->transfer_id : ''; ?></td>
                                        <td> <?= $purchase->transfer_at ?> </td>
                                        <td>
                                            <div class="text-center">
                                                <div class="btn-group text-left">
                                                    <button type="button"
                                                        class="btn btn-default btn-xs btn-primary dropdown-toggle"
                                                        data-toggle="dropdown">Action<span class="caret"></span></button>
                                                    <ul class="dropdown-menu pull-right" role="menu">
                                                        <li><?= $detail_link ?> </li>
                                                        <li><?= $payments_link ?></li>
                                                        <li><?= $add_payment_link ?></li>
                                                        <?php if($purchase->status != 'received') {?>
                                                        <li><?= $edit_link ?></li>
                                                        <?php }?>
                                                        <!-- <li><?= $pdf_link ?></li>
                                                        <li><?= $email_link ?></li> -->
                                                        <li><?= $print_barcode ?></li>
                                                        <?php if($purchase->status == 'received') {?>
                                                        <li><?= $return_link ?></li>
                                                        <?php } ?>
                                                        <?php if($purchase->status != 'received') {?>
                                                        <li><?= $delete_link ?></li>
                                                        <?php }?>
                                                        <?php if($purchase->is_transfer !=1 && $purchase->status == 'received') {?>
                                                            <li><?= $transfer_link ?></li>
                                                        <?php }?>
                                                        <?php if($purchase->status == 'received') {?>
                                                        <li><?= $journal_entry_link ?></li>
                                                        <?php } ?>
                                                    </ul>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="8" class="text-center">No purchases found</td>
                                </tr>
                            <?php endif; ?>

                        </tbody>
                        <!-- <tfoot class="dtFilter">
                            <tr class="active">
                                <th style="min-width:30px; width: 30px; text-align: center;">
                                    
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
                        </tfoot> -->
                    </table>
                    <div class="mt-3">
                        <?= $pagination ?>
                    </div>
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
    document.getElementById('searchByNumber').addEventListener('click', function () {
        var pid = document.getElementById('pid').value;
        var pfromDate = document.getElementById('pfromDate').value;
        var ptoDate = document.getElementById('ptoDate').value;

        if (is_numeric(pid) || pid == '') {
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