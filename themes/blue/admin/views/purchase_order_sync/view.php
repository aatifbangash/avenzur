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
                class="fa-fw fa fa-star"></i><?= lang('Purchase Order Sync List') ; ?>
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

                    <div style="flex: 0;">
                        <input type="hidden" id="searchByNumber" value="<?= isset($pid) ? $pid : '' ?>" class="btn btn-primary" value="Search">
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
                                        
                                        <td><?= $purchase->shelf_status ? 'Shelved' : 'NA' ?></td>
                                        <td><?= number_format($purchase->grand_total, 2) ?></td>
                                        <td class="text-center"><?= !empty($purchase->shopify_synced) ? '<span class="text-success">Yes</span>' : '<span class="text-muted">No</span>' ?></td>
                                        <!-- Shopify Preview Fields -->
                                       
                                       
                                        <td>
                                            <div class="text-center">
                                                <div class="btn-group text-left">
                                                    <button type="button"
                                                        class="btn btn-default btn-xs btn-primary dropdown-toggle"
                                                        data-toggle="dropdown">Action<span class="caret"></span></button>
                                                    <ul class="dropdown-menu pull-right" role="menu">
                                                        
                                                        <li><?= $detail_link ?></li>
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