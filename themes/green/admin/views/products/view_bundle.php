<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="modal-dialog modal-lg no-modal-header">
    <div class="modal-content">
        <div class="modal-body">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                <i class="fa fa-2x">&times;</i>
            </button>
            <button type="button" class="btn btn-xs btn-default no-print pull-right" style="margin-right:15px;" onclick="window.print();">
                <i class="fa fa-print"></i> <?= lang('print'); ?>
            </button>
            <div class="text-center" style="margin-bottom:20px;">
                <img src="<?= base_url() . 'assets/uploads/logos/' . $Settings->logo; ?>" alt="<?= $Settings->site_name; ?>">
            </div>
            <div class="well well-sm">
                <div class="row  ">
                    <div class="col-xs-5">
                    
                     <span class="bold"><?= lang('Bundle_Name'); ?>: </span><?=  $bundle->bundle_name?><br>
                       
                    
                    </div>
                   
                    <div class="clearfix"></div>
                </div>
                <div class="clearfix"></div>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-hover table-striped print-table order-table">

                    <thead>

                    <tr>
                        <th><?= lang('no'); ?></th>
                        <th><?= lang('product'); ?></th>
                        <th><?= lang('price'); ?></th>
                        <th><?= lang('dicount'); ?>%</th>
                        <!-- <th><?= lang('quantity'); ?></th> -->
                    </tr>

                    </thead>

                    <tbody>

                    <?php $r = 1;
                    foreach ($rows as $row):
                    ?>
                        <tr>
                            <td style="text-align:center; width:40px; vertical-align:middle;"><?= $r; ?></td>
                            <td style="vertical-align:middle;">
                                <?= $row->product_code . ' - ' . $row->product_name; ?> 
                            </td>
                            <th><?= $this->sma->formatMoney($row->price); ?></th>
                            <th><?= lang($row->discount); ?> %</th>
                            <!-- <td style="width: 80px; text-align:center; vertical-align:middle;"><?= $this->sma->formatQuantity($row->quantity); ?></td> -->
                        </tr>
                        <?php
                        $r++;
                    endforeach;
                    ?>
                    </tbody>
                </table>
            </div>

            <div class="row">
                <div class="col-xs-7">
                    <?php if ($bundle->bundle_description || $bundle->bundle_description != '') {
                        ?>
                        <div class="well well-sm">
                            <p class="bold"><?= lang('bundle_description'); ?>:</p>
                            <div><?= $this->sma->decode_html($bundle->bundle_description); ?></div>
                        </div>
                    <?php
                    } ?>
                </div>

                <div class="col-xs-5 pull-right">
                    <div class="well well-sm">
                        <p>
                            <?= lang('created_by'); ?>: <?= $created_by->first_name . ' ' . $created_by->last_name; ?> <br>
                            <?= lang('date_created'); ?>: <?= $this->sma->hrld($bundle->date_created); ?>
                        </p>
                        <?php if ($bundle->updated_by) {
                        ?>
                        <p>
                            <?= lang('updated_by'); ?>: <?= $updated_by->first_name . ' ' . $updated_by->last_name; ?><br>
                            <!-- <?= lang('update_at'); ?>: <?= $this->sma->hrld($bundle->updated_at); ?> -->
                        </p>
                        <?php
                    } ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
