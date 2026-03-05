<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-list"></i><?= lang('purchase_requisitions'); ?></h2>
        <div class="box-icon">
            <a href="<?= admin_url('purchase_requisition/create'); ?>" class="btn btn-primary">
                <i class="fa fa-plus"></i> <?= lang('create_purchase_requisition'); ?>
            </a>
        </div>
    </div>

    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover" id="requisitionsTable">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Requisition No</th>
                                <th>Requested By</th>
                                <th>Department</th>
                                <th>Warehouse</th>
                                <th>Expected Date</th>
                                <th>Status</th>
                                <th>Created At</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($requisitions)) : ?>
                                <?php foreach ($requisitions as $index => $req) : ?>
                                    <tr>
                                        <td><?= $index + 1; ?></td>
                                        <td><?= $req->pr_number; ?></td>
                                        <td><?= $req->requested_by_name; ?></td>
                                        <td><?= $req->department; ?></td>
                                        <td><?= $req->warehouse_name; ?></td>
                                        <td><?= date('Y-m-d', strtotime($req->expected_date)); ?></td>
                                        <td><?= $req->status; ?></td>
                                        <td><?= date('Y-m-d H:i', strtotime($req->created_at)); ?></td>
                                        <td>
                                            <a href="<?= admin_url('purchase_requisition/view/' . $req->id); ?>" class="btn btn-sm btn-info">View</a>
                                            <a href="<?= admin_url('purchase_requisition/save/' . $req->id); ?>" class="btn btn-sm btn-warning">Edit</a>
                                            <a href="<?= admin_url('purchase_requisition/delete/' . $req->id); ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this requisition?');">Delete</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else : ?>
                                <tr>
                                    <td colspan="9" class="text-center">No requisitions found.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // $(document).ready(function() {
    //     $('#requisitionsTable').DataTable({
    //         "order": [[ 0, "desc" ]],
    //         "columnDefs": [
    //             { "orderable": false, "targets": 8 } // Disable ordering on Actions column
    //         ]
    //     });
    // });
</script>
