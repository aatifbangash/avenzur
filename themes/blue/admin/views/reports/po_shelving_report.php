<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-shopping-cart"></i> PO Shelving Report</h2>
        <div class="box-icon">
            <a href="javascript:void(0);" onclick="exportTableToExcel('poShelvingTable','po_shelving_report.xlsx')" class="tip btn btn-xs btn-default" title="Download XLS">
                <i class="fa fa-file-excel-o"></i> XLS
            </a>
        </div>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">

                <div class="table-responsive">
                    <table id="poShelvingTable" class="table table-bordered table-striped table-condensed table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>PO ID</th>
                                <th>Reference No</th>
                                <th>Date</th>
                                <th>Supplier</th>
                                <th class="text-right">SKU Ordered</th>
                                <th class="text-right">SKU Received</th>
                                <th class="text-right">Ordered Qty</th>
                                <th class="text-right">Shelved Qty</th>
                                <th class="text-right">Remaining Qty</th>
                                <th class="text-right">Shelved %</th>
                                <th class="text-right">PO Value</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($rows)):
                                $i = 0;
                                $t_ordered = $t_shelved = $t_value = $t_sku_o = $t_sku_r = 0;
                                foreach ($rows as $row):
                                    $i++;
                                    $remaining  = $row->ordered_qty - $row->shelved_qty;
                                    $pct        = $row->ordered_qty > 0 ? round($row->shelved_qty / $row->ordered_qty * 100, 1) : 0;
                                    $t_ordered += $row->ordered_qty;
                                    $t_shelved += $row->shelved_qty;
                                    $t_value   += $row->po_value;
                                    $t_sku_o   += $row->sku_ordered;
                                    $t_sku_r   += $row->sku_received;
                                    $pct_class  = $pct >= 100 ? 'success' : ($pct >= 50 ? 'warning' : 'danger');
                                    ?>
                                    <tr>
                                        <td><?= $i ?></td>
                                        <td>
                                            <a href="#" class="po-detail-link" data-po-id="<?= (int)$row->po_id ?>"
                                               data-toggle="modal" data-target="#poDetailModal">
                                                <strong><?= (int)$row->po_id ?></strong>
                                            </a>
                                        </td>
                                        <td><?= htmlspecialchars($row->reference_no) ?></td>
                                        <td><?= htmlspecialchars($row->date) ?></td>
                                        <td><?= htmlspecialchars($row->supplier_name) ?></td>
                                        <td class="text-right"><?= number_format($row->sku_ordered) ?></td>
                                        <td class="text-right"><?= number_format($row->sku_received) ?></td>
                                        <td class="text-right"><?= number_format($row->ordered_qty, 2) ?></td>
                                        <td class="text-right"><?= number_format($row->shelved_qty, 2) ?></td>
                                        <td class="text-right <?= $remaining > 0 ? 'text-danger' : '' ?>"><?= number_format($remaining, 2) ?></td>
                                        <td class="text-right">
                                            <span class="label label-<?= $pct_class ?>"><?= $pct ?>%</span>
                                        </td>
                                        <td class="text-right"><?= number_format($row->po_value, 2) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                                <tr style="font-weight:bold; background-color:#f5f5f5;">
                                    <td colspan="5" class="text-right">Total</td>
                                    <td class="text-right"><?= number_format($t_sku_o) ?></td>
                                    <td class="text-right"><?= number_format($t_sku_r) ?></td>
                                    <td class="text-right"><?= number_format($t_ordered, 2) ?></td>
                                    <td class="text-right"><?= number_format($t_shelved, 2) ?></td>
                                    <td class="text-right"><?= number_format($t_ordered - $t_shelved, 2) ?></td>
                                    <td></td>
                                    <td class="text-right"><?= number_format($t_value, 2) ?></td>
                                </tr>
                            <?php else: ?>
                                <tr>
                                    <td colspan="12" class="text-center"><?= lang('no_data_available') ?></td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>
</div>

<!-- PO Detail Modal -->
<div class="modal fade" id="poDetailModal" tabindex="-1" role="dialog" aria-labelledby="poDetailModalLabel">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                <h4 class="modal-title" id="poDetailModalLabel"><i class="fa fa-list"></i> PO Detail</h4>
            </div>
            <div class="modal-body" id="poDetailModalBody">
                <div class="text-center"><i class="fa fa-spinner fa-spin fa-2x"></i></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function () {
    $(document).on('click', '.po-detail-link', function (e) {
        e.preventDefault();
        var poId = $(this).data('po-id');
        $('#poDetailModalLabel').html('<i class="fa fa-list"></i> PO #' + poId + ' Detail');
        $('#poDetailModalBody').html('<div class="text-center"><i class="fa fa-spinner fa-spin fa-2x"></i></div>');
        $('#poDetailModal').modal('show');
        $.get('<?= admin_url('reports/po_shelving_detail') ?>/' + poId, function (html) {
            $('#poDetailModalBody').html(html);
        }).fail(function () {
            $('#poDetailModalBody').html('<div class="alert alert-danger">Failed to load details.</div>');
        });
    });
});
</script>
