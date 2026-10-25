<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-list"></i> <?= lang('Contract Deals') ?></h2>
        <div class="box-icon">
            <a class="btn btn-primary" href="<?= admin_url('purchase_contract_deals/add') ?>">Add/Edit Deal</a>
        </div>
    </div>
    <div class="box-content">
        <?php if (!empty($deals)) : ?>
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Supplier</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $i=1;
                    foreach ($deals as $d) : ?>
                        <tr>
                            <td><?= $i ?></td>
                          <td><?= $d->supplier_name ?></td>
                            <td><?= $d->date ?></td>
                            <td>
                                <a href="<?= admin_url('purchase_contract_deals/view/' . $d->id) ?>" class="btn btn-xs btn-default">View</a>
                                <a href="<?= admin_url('purchase_contract_deals/delete/' . $d->id) ?>" class="btn btn-xs btn-danger" onclick="return confirm('Are you sure?')">Delete</a>
                            </td>
                        </tr>
                    <?php $i=$i+1; endforeach; ?>
                </tbody>
            </table>
            <div class="text-center"><?= $pagination ?></div>
        <?php else : ?>
            <p>No records found.</p>
        <?php endif; ?>
    </div>
</div>
