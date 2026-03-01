<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-info-circle"></i><?= lang('Petty Cash List'); ?></h2>

        <div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown">
                    <a data-toggle="dropdown" class="dropdown-toggle" href="#">
                        <i class="icon fa fa-tasks tip" data-placement="left" title="<?= lang('actions') ?>"></i>
                    </a>
                    <ul class="dropdown-menu pull-right tasks-menus" role="menu" aria-labelledby="dLabel">
                        <li>
                            <a href="<?= admin_url('suppliers/petty_cash') ?>">
                                <i class="fa fa-plus-circle"></i> <?= lang('Add Petty Cash') ?>
                            </a>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">

                <div class="row">
                    <div class="controls table-controls" style="font-size: 12px !important;">
                        <table id="poTable"
                                class="table items table-striped table-bordered table-condensed table-hover sortable_table">
                            <thead>
                            <tr>
                                <th>#</th>
                                <th><?php echo $this->lang->line('Reference No.'); ?></th>
                                <th><?php echo $this->lang->line('Payment Amount') ?></th>
                                <th><?php echo $this->lang->line('VAT Amount') ?></th>
                                <th><?php echo $this->lang->line('Date') ?></th>
                                <th><?php echo $this->lang->line('Actions') ?></th>
                            </tr>
                            </thead>
                            <tbody style="text-align:center;">
                                <?php
                                    $count = 0;
                                    foreach($petty_cash_entries as $entry){
                                        $count++;
                                        ?>
                                            <tr>
                                                <td><?= $count; ?></td>
                                                <td><?= $entry->reference_no; ?></td>
                                                <td><?= number_format($entry->payment_amount - $entry->vat_value, 2); ?> SAR</td>
                                                <td><?= number_format($entry->vat_value, 2); ?> SAR</td>
                                                <td><?= date('d/m/Y', strtotime($entry->date)); ?></td>
                                                <td>
                                                    <a href="<?php echo admin_url('suppliers/petty_cash_pdf/' . $entry->id); ?>" class="tip" title="Download PDF">
                                                        <i class="fa fa-file-pdf-o"></i>
                                                    </a>
                                                    <!--<a href="<?php echo admin_url('suppliers/edit_petty_cash/' . $entry->id); ?>" class="tip" title="Edit Petty Cash">
                                                        <i class="fa fa-edit"></i>
                                                    </a>-->
                                                </td>
                                            </tr>
                                        <?php
                                    }
                                ?>
                            </tbody>
                            <tfoot></tfoot>
                        </table>
                    </div>

                </div>

            </div>

        </div>
    </div>
</div>