<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-eye"></i> <?= lang('View Contract Deal') ?></h2>
    </div>
    <div class="box-content">
        <table class="table table-bordered">
            <tr>
                <th>Supplier</th>
                <td><?= $deal->supplier_name ?></td>
            </tr>
            <tr>
                <th>Date</th>
                <td><?= $deal->date ?></td>
            </tr>
            <tr>
                <th>Note</th>
                <td><?= $deal->note ?></td>
            </tr>
        </table>
        <?php if (!empty($items)) : ?>
            <h4>Items</h4>
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Product</th>
                        <th>Deal Type</th>
                        <th>Threshold</th>
                        <th>Dis1 Percentage</th>
                        <th>Dis2 Percentage</th>
                        <th>Dis3 Percentage</th>
                        <th>Deal Percentage</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $it) : ?>
                        <tr>
                            <td><?= $it->id ?></td>
                            <td><?= $it->product_name ?? $it->product_name ?? 'N/A' ?></td>
                            <td><?= $it->deal_type ?? $it->deal_type ?? 'N/A' ?></td>
                            <td><?= $it->threshold ?? $it->threshold ?? '' ?></td>
                            <td><?= $it->dis1_percentage . '%' ?? '' ?></td>
                            <td><?= $it->dis2_percentage . '%' ?? '' ?></td>
                            <td><?= $it->dis3_percentage . '%' ?? '' ?></td>
                            <td><?= $it->deal_percentage . '%' ?? '' ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>