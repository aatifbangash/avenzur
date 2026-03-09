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

<?php if ($Owner || ($GP && $GP['bulk_actions'])) {
    echo admin_form_open('purchase_order/purchase_actions', 'id="action-form"');
}
?>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i
                class="fa-fw fa fa-star"></i><?= lang('purchase orders') ; ?>
        </h2>

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
                            placeholder="Purchase Number" value="<?= isset($pid) ? $pid : '' ?>">
                    </div>

                    <!-- <div style="flex: 1;">
                        <input type="date" name="date" class="form-control input-tip" id="pfromDate"
                            placeholder="From Date" value="<?= isset($pfromDate) ? $pfromDate : '' ?>">
                    </div>

                    <div style="flex: 0; margin: 0 10px; font-size: 18px; font-weight: bold;">
                        -
                    </div>

                    <div style="flex: 1; margin-right: 20px;">
                        <input type="date" name="date" class="form-control input-tip" id="ptoDate"
                            placeholder="To Date" value="<?= isset($ptoDate) ? $ptoDate : '' ?>">
                    </div> -->

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
                                <th><?= lang('supplier'); ?></th>
                                <th style="width: 20px"><?= lang('Status'); ?></th>
                                <?php
                                if ($this->Settings->site_name != 'Hills Business Medical') {
                                ?>
                                <th style="width: 20px"><?= lang('Shelf Status'); ?></th>
                                <?php } ?>
                                <th style="width: 20px"><?= lang('grand_total'); ?></th>
                                <th style="width: 80px; text-align:center;">Synced</th>
                                <!-- Shopify Preview Fields -->
                                <th style="width: 60px; text-align:center;">Products</th>
                                <th style="width: 60px; text-align:center;">Total Qty</th>
                                <th style="width: 80px; text-align:center;">Total Cost</th>
                                <th style="width: 80px; text-align:center;">Total Value</th>
                                <th style="width: 10px !important">
                                    <i class="fa fa-chain"></i>
                                </th>
                                <th style="width:100px;"><?= lang('actions'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if (!empty($purchases)): ?>
                                <?php foreach ($purchases as $purchase):
                                    $pid = $purchase->id;
                                    $detail_link = anchor('admin/purchase_order/view/'.$pid, '<i class="fa fa-file-text-o"></i> ' . lang('purchase_order_details'));
                                    
                                  
                                    $edit_link = anchor('admin/purchase_order/edit/'.$pid, '<i class="fa fa-edit"></i> ' . lang('edit_purchase'));
                                   //$return_link = anchor('admin/returns_supplier/add/?purchase='.$pid, '<i class="fa fa-angle-double-left"></i> ' . lang('return_purchase'));
                                    // $delete_link = "<a href='#' class='po' title='<b>" . $this->lang->line('delete_purchase') . "</b>' data-content=\"<p>"
                                    //     . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . admin_url('purchases/delete/'.$pid) . "'>"
                                    //     . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
                                    //     . lang('delete_purchase') . '</a>';
                                    
                                    $transfer_status = "Pending";
                                    if($purchase->is_transfer == 1){
                                        $transfer_status = "Transferred";
                                    }else if   ($purchase->is_transfer == 2){
                                        $transfer_status = "Partially Transferred";
                                    }
                                    ?>
                                    <tr class="purchase_order_link" id="<?=$pid?>">
                                        <td><?= $purchase->id ?></td>
                                        <td><?= $purchase->date ?></td>
                                        <td><?= $purchase->supplier ?></td>
                                        <td><?= $purchase->status ?></td>
                                        <?php
                                        if ($this->Settings->site_name != 'Hills Business Medical') {
                                        ?>
                                        <td><?= $purchase->shelf_status ? 'Shelved' : 'NA' ?></td>
                                        <?php } ?>
                                        <td><?= number_format($purchase->grand_total, 2) ?></td>
                                        <td class="text-center"><?= !empty($purchase->shopify_synced) ? '<span class="text-success">Yes</span>' : '<span class="text-muted">No</span>' ?></td>
                                        <!-- Shopify Preview Fields -->
                                        <td class="text-center">
                                            <span class="badge badge-info" title="Click to view products">
                                                <?php
                                                $agg = isset($shopify_aggregates[$purchase->id]) ? $shopify_aggregates[$purchase->id] : null;
                                                echo $agg ? $agg['total_products'] : '0';
                                                ?>
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <?php
                                            echo $agg ? number_format($agg['total_quantity']) : '0';
                                            ?>
                                        </td>
                                        <td class="text-center">
                                            <?php
                                            echo $agg ? number_format($agg['total_cost'], 2) : '0.00';
                                            ?>
                                        </td>
                                        <td class="text-center">
                                            <?php
                                            echo $agg ? number_format($agg['total_value'], 2) : '0.00';
                                            ?>
                                        </td>
                                        <td style="width:10px !important;">
                                            <?php
                                                if($purchase->attachment){
                                                    echo '<a href="files/'.$purchase->attachment.'" class="tip" title="" data-original-title="Download"><i class="fa fa-file"></i></a>';
                                                }
                                            ?>
                                        </td>
                                       
                                        <td>
                                            <div class="text-center">
                                                <div class="btn-group text-left">
                                                    <button type="button"
                                                        class="btn btn-default btn-xs btn-primary dropdown-toggle"
                                                        data-toggle="dropdown">Action<span class="caret"></span></button>
                                                    <ul class="dropdown-menu pull-right" role="menu">
                                                        
                                                        <!-- <?php if($purchase->status == "pending" && ($Admin || $Owner || $this->GP['po-add'])) {?>
                                                        <li><?= $edit_link ?></li>
                                                        <?php }?>
                                                        <li><?= $detail_link ?></li> -->
                                                        <!-- sync action only when not already synced -->
                                                        <?php if (empty($purchase->shopify_synced)) : ?>
                                                        <li>
                                                            <a href="#" onclick="syncPO(<?= $pid ?>); return false;">
                                                                <i class="fa fa-cloud-upload"></i> Sync to Shopify
                                                            </a>
                                                        </li>
                                                        <?php else: ?>
                                                        <li><span class="text-success"><i class="fa fa-check"></i> Synced</span></li>
                                                        <?php endif; ?>
                                                        
                                                       
                                                    </ul>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="12" class="text-center">No purchases found</td>
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
                                if (res.status === 'success') {
                                    var message = res.message;
                                    if (res.data && Array.isArray(res.data)) {
                                        message += '\nItems returned: ' + res.data.length;
                                        console.log(res.data);
                                    }
                                    alert(message);
                                    window.location.reload();
                                } else {
                                    alert(res.message);
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
                </script>            </div>
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

    function deletePO(id, refNo) {
        if (confirm('Are you sure you want to delete Purchase Order ' + refNo + '?')) {
            $.ajax({
                url: site.base_url + 'purchase_order/delete/' + id,
                type: 'POST',
                dataType: 'json',
                data: {
                    <?= $this->security->get_csrf_token_name(); ?>: '<?= $this->security->get_csrf_hash(); ?>'
                },
                success: function(response) {
                    if (response.status === 'success') {
                        alert(response.message);
                        location.reload();
                    } else {
                        alert(response.message);
                    }
                },
                error: function() {
                    alert('An error occurred while deleting the purchase order.');
                }
            });
        }
        return false;
    }

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