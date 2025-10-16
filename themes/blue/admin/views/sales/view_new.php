<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<style>
/* --- Stepper Container --- */
.progress-tracker {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin: 30px 0;
    padding: 0 20px;
    position: relative;
}

/* --- Step --- */
.progress-step {
    text-align: center;
    flex: 1;
    position: relative;
}

/* --- Connector Line --- */
.progress-step::after {
    content: "";
    position: absolute;
    top: 22px;
    left: 50%;
    height: 4px;
    width: 100%;
    background-color: #ccc;
    z-index: 1;
}

.progress-step:last-child::after {
    display: none;
}

/* --- Step Circle --- */
.step-circle {
    width: 40px;
    height: 40px;
    background-color: #ccc;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto;
    color: #fff;
    font-weight: bold;
    z-index: 2;
    position: relative;
}

/* --- Active Step --- */
.progress-step.active .step-circle {
    background-color: #28a745; /* Green */
}

/* --- Completed Step --- */
.progress-step.completed .step-circle {
    background-color: #007bff; /* Blue */
}

/* --- Step Text --- */
.step-label {
    margin-top: 8px;
    font-size: 14px;
    color: #333;
}

/* --- Responsive Fix --- */
@media (max-width: 768px) {
    .progress-tracker {
        flex-direction: column;
        align-items: flex-start;
    }
    .progress-step {
        margin-bottom: 20px;
    }
    .progress-step::after {
        display: none;
    }
}
</style>

<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-file"></i><?= lang('sale_no') . ' ' . $inv->id; ?></h2>

        <div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown">
                    <a data-toggle="dropdown" class="dropdown-toggle" href="#">
                        <i class="icon fa fa-tasks tip" data-placement="left" title="<?= lang('actions') ?>">
                        </i>
                    </a>
                    <ul class="dropdown-menu pull-right tasks-menus" role="menu" aria-labelledby="dLabel">
                        <li>
                            <a href="<?= admin_url('sales/edit/' . $inv->id) ?>" class="sledit">
                                <i class="fa fa-edit"></i> <?= lang('edit_sale') ?>
                            </a>
                        </li>
                        <li>
                            <a href="<?= admin_url('sales/payments/' . $inv->id) ?>" data-target="#myModal" data-toggle="modal">
                                <i class="fa fa-money"></i> <?= lang('view_payments') ?>
                            </a>
                        </li>
                        <li>
                            <a href="<?= admin_url('sales/add_payment/' . $inv->id) ?>" data-target="#myModal" data-toggle="modal">
                                <i class="fa fa-dollar"></i> <?= lang('add_payment') ?>
                            </a>
                        </li>
                        <li>
                            <a href="<?= admin_url('sales/email/' . $inv->id) ?>" data-target="#myModal" data-toggle="modal">
                                <i class="fa fa-envelope-o"></i> <?= lang('send_email') ?>
                            </a>
                        </li>
                        <li>
                            <a href="<?= admin_url('sales/pdf/' . $inv->id) ?>">
                                <i class="fa fa-file-pdf-o"></i> <?= lang('export_to_pdf') ?>
                            </a>
                        </li>
                        <?php if (!$inv->sale_id) {
                            ?>
                        <li>
                            <a href="<?= admin_url('sales/add_delivery/' . $inv->id) ?>" data-target="#myModal" data-toggle="modal">
                                <i class="fa fa-truck"></i> <?= lang('add_delivery') ?>
                            </a>
                        </li>
                        <li>
                            <a href="<?= admin_url('sales/return_sale/' . $inv->id) ?>">
                                <i class="fa fa-angle-double-left"></i> <?= lang('return_sale') ?>
                            </a>
                        </li>
                            <?php
                        } ?>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
                <?php if (!empty($inv->return_sale_ref) && $inv->return_id) {
                    echo '<div class="alert alert-info no-print"><p>' . lang('sale_is_returned') . ': ' . $inv->return_sale_ref;
                    echo ' <a data-target="#myModal2" data-toggle="modal" href="' . admin_url('sales/modal_view/' . $inv->return_id) . '"><i class="fa fa-external-link no-print"></i></a><br>';
                    echo '</p></div>';
                } ?>
                <div class="print-only col-xs-12">
                    <img src="<?= base_url() . 'assets/uploads/logos/' . $biller->logo; ?>" alt="<?= $biller->company && $biller->company != '-' ? $biller->company : $biller->name; ?>">
                </div>
                <div class="well well-sm">

                    <div class="col-xs-4 border-right">

                        <div class="col-xs-2"><i class="fa fa-3x fa-user padding010 text-muted"></i></div>
                        <div class="col-xs-10">
                            <h2 class=""><?= $customer->company && $customer->company != '-' ? $customer->company : $customer->name; ?></h2>
                            <?= $customer->company              && $customer->company != '-' ? '' : 'Attn: ' . $customer->name ?>

                            <?php
                            echo $customer->address . '<br>' . $customer->city . ' ' . $customer->postal_code . ' ' . $customer->state . '<br>' . $customer->country;

                            echo '<p>';
                            if ($customer->sequence_code != '-' && $customer->sequence_code != '') {
                                echo '<br>' . lang('sequence_code') . ': ' . $customer->sequence_code;
                            }
                            //if ($customer->vat_no != '-' && $customer->vat_no != '') {
                                echo '<br>' . lang('vat_no') . ': ' . $customer->vat_no;
                            //}
                            if ($customer->gst_no != '-' && $customer->gst_no != '') {
                                echo '<br>' . lang('gst_no') . ': ' . $customer->gst_no;
                            }
                            if ($customer->cf1 != '-' && $customer->cf1 != '') {
                                echo '<br>' . lang('ccf1') . ': ' . $customer->cf1;
                            }
                            if ($customer->cf2 != '-' && $customer->cf2 != '') {
                                echo '<br>' . lang('ccf2') . ': ' . $customer->cf2;
                            }
                            if ($customer->cf3 != '-' && $customer->cf3 != '') {
                                echo '<br>' . lang('ccf3') . ': ' . $customer->cf3;
                            }
                            if ($customer->cf4 != '-' && $customer->cf4 != '') {
                                echo '<br>' . lang('ccf4') . ': ' . $customer->cf4;
                            }
                            if ($customer->cf5 != '-' && $customer->cf5 != '') {
                                echo '<br>' . lang('ccf5') . ': ' . $customer->cf5;
                            }
                            if ($customer->cf6 != '-' && $customer->cf6 != '') {
                                echo '<br>' . lang('ccf6') . ': ' . $customer->cf6;
                            }

                            echo '</p>';
                            echo lang('tel') . ': ' . $customer->phone . '<br>' . lang('email') . ': ' . $customer->email;
                            ?>
                        </div>
                        <div class="clearfix"></div>
                    </div>

                    <div class="col-xs-4 border-right">

                        <div class="col-xs-2"><i class="fa fa-3x fa-building padding010 text-muted"></i></div>
                        <div class="col-xs-10">
                            <h2 class="">biller <?= $biller->company && $biller->company != '-' ? $biller->company : $biller->name; ?></h2>
                            <?= $biller->company ? '' : 'Attn: ' . $biller->name ?>

                            <?php
                            echo $biller->address . '<br>' . $biller->city . ' ' . $biller->postal_code . ' ' . $biller->state . '<br>' . $biller->country;

                            echo '<p>';

                            //if ($biller->vat_no != '-' && $biller->vat_no != '') {
                                echo '<br>' . lang('vat_no') . ': ' . $biller->vat_no;
                            //}
                            if ($biller->gst_no != '-' && $biller->gst_no != '') {
                                echo '<br>' . lang('gst_no') . ': ' . $biller->gst_no;
                            }
                            if ($biller->cf1 != '-' && $biller->cf1 != '') {
                                echo '<br>' . lang('bcf1') . ': ' . $biller->cf1;
                            }
                            if ($biller->cf2 != '-' && $biller->cf2 != '') {
                                echo '<br>' . lang('bcf2') . ': ' . $biller->cf2;
                            }
                            if ($biller->cf3 != '-' && $biller->cf3 != '') {
                                echo '<br>' . lang('bcf3') . ': ' . $biller->cf3;
                            }
                            if ($biller->cf4 != '-' && $biller->cf4 != '') {
                                echo '<br>' . lang('bcf4') . ': ' . $biller->cf4;
                            }
                            if ($biller->cf5 != '-' && $biller->cf5 != '') {
                                echo '<br>' . lang('bcf5') . ': ' . $biller->cf5;
                            }
                            if ($biller->cf6 != '-' && $biller->cf6 != '') {
                                echo '<br>' . lang('bcf6') . ': ' . $biller->cf6;
                            }

                            echo '</p>';
                            echo lang('tel') . ': ' . $biller->phone . '<br>' . lang('email') . ': ' . $biller->email;
                            ?>
                        </div>
                        <div class="clearfix"></div>

                    </div>

                    <div class="col-xs-4">
                        <div class="col-xs-2"><i class="fa fa-3x fa-building-o padding010 text-muted"></i></div>
                        <div class="col-xs-10">
                            <h2 class=""><?= $Settings->site_name; ?></h2>
                            <?= $warehouse->name ?>

                            <?php
                            echo $warehouse->address . '<br>';
                            echo($warehouse->phone ? lang('tel') . ': ' . $warehouse->phone . '<br>' : '') . ($warehouse->email ? lang('email') . ': ' . $warehouse->email : '');
                            ?>
                        </div>
                        <div class="clearfix"></div>
                    </div>
                    <div class="clearfix"></div>
                </div>
                
                <div class="clearfix"></div>
                
            </div>
        </div>

        <?php 
            $ready_status = '';
            $printed_label_status = '';
            $label_verifired_status = '';
            $driver_assigned_status = '';
            $delivered = '';

            if($inv->sale_status == 'ready'){
                $ready_status = 'completed';
                $printed_label_status = 'active';
                $label_verifired_status = '';
                $driver_assigned_status = '';
                $delivered = '';
            }else if($inv->sale_status == 'added_label'){
                $ready_status = 'completed';
                $printed_label_status = 'completed';
                $label_verifired_status = 'active';
                $driver_assigned_status = '';
                $delivered = '';
            }else if($inv->sale_status == 'label_verifired'){
                $ready_status = 'completed';
                $printed_label_status = 'completed';
                $label_verifired_status = 'completed';
                $driver_assigned_status = 'active';
                $delivered = '';
            }else if($inv->sale_status == 'driver_assigned'){
                $ready_status = 'completed';
                $printed_label_status = 'completed';
                $label_verifired_status = 'completed';
                $driver_assigned_status = 'completed';
                $delivered = 'active';
            }else if($inv->sale_status == 'delivered'){
                $ready_status = 'completed';
                $printed_label_status = 'completed';
                $label_verifired_status = 'completed';
                $driver_assigned_status = 'completed';
                $delivered = 'completed';
            }   
        ?>

        <!-- Stepper HTML -->
        <div class="progress-tracker">
            <div class="progress-step <?php echo $ready_status; ?>">
                <div class="step-circle">1</div>
                <div class="step-label">Ready</div>
            </div>
            <div class="progress-step <?php echo $printed_label_status; ?>">
                <div class="step-circle">2</div>
                <div class="step-label">Added Label</div>
            </div>
            <div class="progress-step <?php echo $label_verifired_status; ?>">
                <div class="step-circle">3</div>
                <div class="step-label">Label Verified</div>
            </div>
            <div class="progress-step <?php echo $driver_assigned_status; ?>">
                <div class="step-circle">4</div>
                <div class="step-label">Driver Assigned</div>
            </div>
            <div class="progress-step <?php echo $delivered; ?>">
                <div class="step-circle">5</div>
                <div class="step-label">Delivered</div>
            </div>
        </div>

        <div class="clearfix"></div>
        <hr />

        <div class="buttons">
            <div class="btn-group btn-group-justified">
                <?php 
                    if($this->GP['sales-index']){
                ?>
                <div class="btn-group">
                    <a href="<?= admin_url('sales/add_label/' . $inv->id) ?>" data-toggle="modal" data-target="#myModal" class="tip btn btn-primary tip" title="<?= lang('add_label') ?>">
                        <i class="fa fa-money"></i> <span class="hidden-sm hidden-xs"><?= lang('add_label') ?></span>
                    </a>
                </div>
                <?php 
                    }
                ?>

                <div class="btn-group">
                    <a href="<?= admin_url('sales/verify_label/' . $inv->id) ?>" data-toggle="modal" data-target="#myModal" class="tip btn btn-primary tip" title="<?= lang('verify_label') ?>">
                        <i class="fa fa-money"></i> <span class="hidden-sm hidden-xs"><?= lang('verify_label') ?></span>
                    </a>
                </div>

                <div class="btn-group">
                    <a href="<?= admin_url('sales/add_driver/' . $inv->id) ?>" data-toggle="modal" data-target="#myModal" class="tip btn btn-primary tip" title="<?= lang('add_driver') ?>">
                        <i class="fa fa-money"></i> <span class="hidden-sm hidden-xs"><?= lang('add_driver') ?></span>
                    </a>
                </div>

                <div class="btn-group">
                    <a href="<?= admin_url('sales/add_delivery/' . $inv->id) ?>" data-toggle="modal" data-target="#myModal" class="tip btn btn-primary tip" title="<?= lang('add_delivery') ?>">
                        <i class="fa fa-truck"></i> <span class="hidden-sm hidden-xs"><?= lang('add_delivery') ?></span>
                    </a>
                </div>

                <div class="btn-group">
                    <a href="<?= admin_url('sales/pdf/' . $inv->id) ?>" class="tip btn btn-primary" title="<?= lang('sale_invoice') ?>">
                        <i class="fa fa-download"></i> <span class="hidden-sm hidden-xs"><?= lang('sale_invoice') ?></span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>