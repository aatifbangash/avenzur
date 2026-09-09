<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<script>
    $(document).ready(function () {
        var lastInsertedId = '<?= $last_inserted_id; ?>';

        function openModalForLastInsertedId(id) {

            $('#myModal').modal({
                remote: site.base_url + 'purchase_order/modal_view/' + lastInsertedId,
            });
            $('#myModal').modal('show');
        }
        lastInsertedId = '<?php echo $lastInsertedId; ?>';
        if (lastInsertedId) {
            openModalForLastInsertedId(lastInsertedId);
        }
    });

</script>
<style>
    .sync-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 14px;
    }

    .sync-table th,
    .sync-table td {
        border: 1px solid #dcdcdc;
        padding: 8px 10px;
        vertical-align: top;
    }

    .sync-table th {
        background: #428bca;
        color: #fff;
        text-align: left;
    }

    .row-danger {
        background: #f8d7da !important;
    }

    .row-warning {
        background: #fff3cd !important;
    }

    .row-info {
        background: #d9edf7 !important;
    }

    .cell-danger {
        background: #f2b8bd !important;
        font-weight: 600;
    }

    .cell-warning {
        background: #ffe69c !important;
        font-weight: 600;
    }

    .issue-list {
        margin: 0;
        padding-left: 18px;
        color: #a94442;
    }

    .product-thumb {
        width: 55px;
        height: 55px;
        object-fit: cover;
        border: 1px solid #ddd;
        border-radius: 4px;
    }

    .badge-create {
        display: inline-block;
        padding: 3px 8px;
        background: #f0ad4e;
        color: #fff;
        border-radius: 10px;
        font-size: 12px;
    }

    .badge-update {
        display: inline-block;
        padding: 3px 8px;
        background: #5cb85c;
        color: #fff;
        border-radius: 10px;
        font-size: 12px;
    }

    .text-muted {
        color: #777;
    }
</style>

<div class="box">
    <div class="box-header">
        <h2 class="blue"><i
                class="fa-fw fa fa-star"></i><?= lang('Purchase order Sync') ; ?>
        </h2>

    </div>
    <div class="box-content">
        <div class="alert alert-info">
            <strong>Legend:</strong>
            <span class="label label-danger">Critical</span> Price issue / stock issue
            &nbsp;
            <span class="label label-warning">Warning</span> Missing required data
            &nbsp;
            <span class="label label-success">Ready</span> Valid for sync
        </div>
        <?php if (isset($pid) && $pid !== '' && !empty($products)) : ?>
            <li>
                <a href="#" onclick="syncPO(<?= $pid ?>); return false;">
                    <i class="fa fa-cloud-upload"></i> Sync to Shopify
                </a>
            </li>
        <?php endif; ?>
        <div class="row">
            <div class="col-lg-12">
                <!-- <p class="introtext"><?= lang('list_results'); ?></p> -->
                <?php
                // MARK: Filters
                ?>
                <div class="row" style="margin: 25px 0; display: flex; align-items: center;">
                    <div style="flex: 1; margin-right: 20px;">
                        <input type="text" id="pid" name="pid" class="form-control input-tip"
                            placeholder="Purchase Number" value="<?= isset($pid) ? $pid : '' ?>">
                    </div>

                    

                    <div style="flex: 0;">
                        <input type="button" id="searchByNumber" class="btn btn-primary" value="Search">
                    </div>
                </div>

                <?php
                // MARK: Table
                ?>
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped table-hover" id="syncTable">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Barcode</th>
                                <th>Name</th>
                                <th>Image</th>
                                <th>Description</th>
                                <th>Tags</th>
                                <th>Initial Stock</th>
                                <th>Tax Rate</th>
                                <th>Brand</th>
                                <th>Cost Price</th>
                                <th>Price</th>
                                <th>Status</th>
                                <th>Issues</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($products)) : ?>
                                <?php foreach ($products as $row) : ?>
                                    <?php
                                        $issues = [];

                                        $costPrice      = (float) $row->cost_price;
                                        $salePrice      = (float) $row->price;
                                        $stock          = (float) $row->initial_stock;

                                        $imageMissing   = empty($row->image);
                                        $barcodeMissing = empty($row->barcode);
                                        $nameMissing    = empty(trim($row->name ?? ''));
                                        $descMissing    = empty(trim($row->description ?? ''));
                                        $brandMissing   = empty(trim($row->brand ?? ''));
                                        $taxMissing     = ($row->tax_rate === null || $row->tax_rate === '');
                                        $stockIssue     = $stock <= 0;
                                        $priceIssue     = $salePrice < $costPrice;

                                        if ($priceIssue) {
                                            $issues[] = 'Sale price is less than cost price';
                                        }
                                        if ($imageMissing) {
                                            $issues[] = 'Image missing';
                                        }
                                        if ($barcodeMissing) {
                                            $issues[] = 'Barcode missing';
                                        }
                                        if ($nameMissing) {
                                            $issues[] = 'Name missing';
                                        }
                                        if ($descMissing) {
                                            $issues[] = 'Description missing';
                                        }
                                        if ($brandMissing) {
                                            $issues[] = 'Brand missing';
                                        }
                                        if ($taxMissing) {
                                            $issues[] = 'Tax rate missing';
                                        }
                                        if ($stockIssue) {
                                            $issues[] = 'Initial stock is zero';
                                        }

                                        $rowClass = '';
                                        if ($priceIssue || $stockIssue) {
                                            $rowClass = 'danger';
                                        } elseif ($imageMissing || $barcodeMissing || $nameMissing || $descMissing || $brandMissing || $taxMissing) {
                                            $rowClass = 'warning';
                                        } else {
                                            $rowClass = 'success';
                                        }

                                        $canSync = !($priceIssue || $stockIssue || $imageMissing || $barcodeMissing || $nameMissing);
                                    ?>
                                    <tr class="<?= $rowClass ?>">
                                        <td><?= (int) $row->id ?></td>

                                        <td>
                                            <?php if ($barcodeMissing) : ?>
                                                <span class="text-danger"><strong>Missing</strong></span>
                                            <?php else : ?>
                                                <?= htmlspecialchars($row->barcode) ?>
                                            <?php endif; ?>
                                        </td>

                                        <td>
                                            <?php if ($nameMissing) : ?>
                                                <span class="text-danger"><strong>Missing</strong></span>
                                            <?php else : ?>
                                                <?= htmlspecialchars($row->name) ?>
                                            <?php endif; ?>
                                        </td>

                                        <td class="text-center">
                                            <?php if ($imageMissing) : ?>
                                                <span class="label label-warning">No Image</span>
                                            <?php else : ?>
                                                <img src="<?= htmlspecialchars($row->image) ?>"
                                                    alt="<?= htmlspecialchars($row->name) ?>"
                                                    style="width:60px; height:60px; object-fit:cover; border:1px solid #ddd; padding:2px; background:#fff;">
                                            <?php endif; ?>
                                        </td>

                                        <td style="max-width: 260px;">
                                            <?php if ($descMissing) : ?>
                                                <span class="text-warning"><strong>Missing</strong></span>
                                            <?php else : ?>
                                                <?= nl2br(htmlspecialchars(substr($row->description, 0, 180))) ?>
                                                <?php if (strlen($row->description) > 180) : ?>
                                                    ...
                                                <?php endif; ?>
                                            <?php endif; ?>
                                        </td>

                                        <td>
                                            <?= !empty($row->tags) ? htmlspecialchars($row->tags) : '<span class="text-muted">-</span>' ?>
                                        </td>

                                        <td>
                                            <?php if ($stockIssue) : ?>
                                                <span class="text-danger"><strong><?= number_format($stock, 2) ?></strong></span>
                                            <?php else : ?>
                                                <?= number_format($stock, 2) ?>
                                            <?php endif; ?>
                                        </td>

                                        <td>
                                            <?php if ($taxMissing) : ?>
                                                <span class="text-warning"><strong>Missing</strong></span>
                                            <?php else : ?>
                                                <?= htmlspecialchars($row->tax_rate) ?>
                                            <?php endif; ?>
                                        </td>

                                        <td>
                                            <?php if ($brandMissing) : ?>
                                                <span class="text-warning"><strong>Missing</strong></span>
                                            <?php else : ?>
                                                <?= htmlspecialchars($row->brand) ?>
                                            <?php endif; ?>
                                        </td>

                                        <td><?= number_format($costPrice, 2) ?></td>

                                        <td>
                                            <?php if ($priceIssue) : ?>
                                                <span class="text-danger"><strong><?= number_format($salePrice, 2) ?></strong></span>
                                            <?php else : ?>
                                                <?= number_format($salePrice, 2) ?>
                                            <?php endif; ?>
                                        </td>

                                        <td>
                                            <?php if ($row->status === 'update') : ?>
                                                <span class="label label-primary">Update</span>
                                            <?php else : ?>
                                                <span class="label label-info">Create</span>
                                            <?php endif; ?>
                                        </td>

                                        <td style="min-width: 220px;">
                                            <?php if (!empty($issues)) : ?>
                                                <ul style="padding-left: 18px; margin-bottom: 0;">
                                                    <?php foreach ($issues as $issue) : ?>
                                                        <li><?= htmlspecialchars($issue) ?></li>
                                                    <?php endforeach; ?>
                                                </ul>
                                            <?php else : ?>
                                                <span class="label label-success">Ready for sync</span>
                                            <?php endif; ?>
                                        </td>

                                        <!-- <td class="text-center">
                                            <?php if ($canSync) : ?>
                                                <button type="button"
                                                        class="btn btn-xs btn-success sync-single-product"
                                                        data-barcode="<?= htmlspecialchars($row->barcode) ?>"
                                                        data-id="<?= (int) $row->id ?>">
                                                    <i class="fa fa-refresh"></i> Sync
                                                </button>
                                            <?php else : ?>
                                                <button type="button" class="btn btn-xs btn-danger" disabled>
                                                    <i class="fa fa-ban"></i> Invalid
                                                </button>
                                            <?php endif; ?>
                                        </td> -->
                                    </tr>
                                <?php endforeach; ?>
                            <?php else : ?>
                                <tr>
                                    <td colspan="14" class="text-center">No records found.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
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
    // search button behaviour
    $('#searchByNumber').on('click', function() {
        var pid = $('#pid').val();
        
        var url = '<?= admin_url('purchase_order_sync') ?>';
        var params = [];
        if (pid) params.push('pid=' + encodeURIComponent(pid));
        window.location.href = url + (params.length ? '?' + params.join('&') : '');
    });
    // allow enter key in pid textbox
    $('#pid').on('keypress', function(e) {
        if (e.which === 13) {
            $('#searchByNumber').click();
        }
    });

    function syncPO(id) {
        if (!id) return;
        $.ajax({
            url: '<?= admin_url('purchase_order_sync/sync') ?>',
            type: 'POST',
            data: {
                purchase_id: id,
                '<?= $this->security->get_csrf_token_name(); ?>': '<?= $this->security->get_csrf_hash(); ?>'
            },
            dataType: 'json',
            success: function(res) {
                if (res.status === 'error') {
                    showNotify('warning', res.message);
                } else if (res.status === 'success') {
                    showNotify('success', res.message);
                }
            },
            error: function(xhr, status, err) {
                console.error('Sync error:', err);
                console.error('Sync status:', status);
                console.error('Sync xhr:', xhr);
                alert('An error occurred while syncing.');
            }
        });
    }
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

    

    $(document).ready(function () {

         $('body').on('click', '.purchase_order_link td:not(:first-child, :nth-child(5), :nth-last-child(2), :last-child)', function () {
        $('#myModal').modal({
            remote: site.base_url + 'purchase_order/modal_view/' + $(this).parent('.purchase_order_link').attr('id'),
        });
        $('#myModal').modal('show');
        //window.location.href = site.base_url + 'purchases/view/' + $(this).parent('.purchase_link').attr('id');
    });
   
});



</script>