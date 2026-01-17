<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>


<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-barcode"></i><?= lang('Pos_List_Sale_Wise'); ?>

        </h2>

        <?php
        
        echo admin_form_open_multipart('pos/sales_date_wise', $attrib)
            ?> <input type="hidden" name="viewtype" id="viewtype" class="viewtype" value="">
        <div class="row" >
            <div class="col-lg-12" style="padding-top: 12px;">

                <div class="col-md-3">
                    <div class="form-group">
                        <?= lang('Select Date', 'podate'); ?>
                        <?php echo form_input('at_date', ($at_date ?? ''), 'class="form-control input-tip date" id="fromdate"'); ?>
                    </div>
                </div>

                <div class="col-md-2">
                    <div class="form-group">
                        <?= lang('Enter Sale Id', 'sale_id'); ?>
                        <?php echo form_input('sale_id', ($sale_id ?? ''), 'class="form-control input-tip" id=""'); ?>
                    </div>
                </div>

                <div class="col-md-2">
                    <div class="from-group">
                        <button type="submit" style="margin-top: 28px;" class="btn btn-primary"
                            id="load_report"><?= lang('Search Sales') ?></button>
                    </div>
                </div>

                <div class="col-md-4" style="margin-top: 28px;">
                    <div class="from-group">
                        <?php 
                         echo "Total Sales: ".$total_sales;
                        if ($prev_sale_id):
                            
                            ?>

                            <a href="<?= admin_url('pos/sales_date_wise?sale_id=' . $prev_sale_id); ?>"
                                class="btn btn-primary">Previous</a>
                        <?php endif; ?>
                        <?php if ($next_sale_id): ?>
                            <a href="<?= admin_url('pos/sales_date_wise?sale_id=' . $next_sale_id); ?>"
                                class="btn btn-primary">Next</a>
                        <?php endif; ?>
                    </div>
                </div>

            </div>



        </div>

        <?php echo form_close();?>

    </div>


</div>

<div class="box-content" style="font-size: 11px;">
    <div class="table-responsive">
        <?php //print_r($inv);?>
        <div class="well well-sm">
                <div class="row bold">
                    <div class="col-xs-5">
                    <p class="bold">
                    
                       <?= lang('Serial Number'); ?>: <?= $sale_id; ?><br>
                        <?= lang('date'); ?>: <?= $this->sma->hrld($inv->date); ?><br>
                        <?= lang('ref'); ?>: <?= $inv->reference_no; ?><br>
                        <?php if (!empty($inv->return_sale_ref)) {
                            echo lang('return_ref') . ': ' . $inv->return_sale_ref;
                            if ($inv->return_id) {
                                echo ' <a data-target="#myModal2" data-toggle="modal" href="' . admin_url('sales/modal_view/' . $inv->return_id) . '"><i class="fa fa-external-link no-print"></i></a><br>';
                            } else {
                                echo '<br>';
                            }
                        } ?>
                        <?= lang('sale_status'); ?>: <?= lang($inv->sale_status); ?><br>
                        <?= lang('payment_status'); ?>: <?= lang($inv->payment_status); ?><br>
                        <?= $inv->payment_method ? lang('payment_method') . ': ' . lang($inv->payment_method) : ''; ?>
                        <?php
                        if ($inv->payment_status != 'paid' && $inv->due_date) {
                            echo '<br>' . lang('due_date') . ': ' . $this->sma->hrsd($inv->due_date);
                        } ?>
                    </p>
                    </div>
                   
                    <div class="clearfix"></div>
                </div>
                <div class="clearfix"></div>
            </div>

        <table class="table table-bordered table-hover table-striped print-table order-table">

            <thead>

                <tr>
                    <th><?= lang('no.'); ?></th>
                    <th><?= lang('description'); ?></th>
                    <th><?= lang('Avz Code'); ?></th>
                    <th><?= lang('Batch No'); ?></th>
                    <?php if ($Settings->indian_gst) {
                        $total_col += 1;
                        ?>
                        <th><?= lang('hsn_sac_code'); ?></th>
                        <?php
                    } ?>
                    <th><?= lang('Base Quantity'); ?></th>
                    <?php
                    if ($inv->status == 'partial') {
                        $total_col += 1;
                        echo '<th>' . lang('received') . '</th>';
                    }
                    ?>
                    <th><?= lang('Bonus'); ?></th>
                    <th><?= lang('Sale_price'); ?></th>

                    <th><?= lang('subtotal'); ?></th>
                    <?php if ($Settings->product_discount && $inv->product_discount != 0) {
                        $total_col += 2;
                        echo '<th>' . lang('Disc1 %') . '</th>';
                        echo '<th>' . lang('Disc1 Value') . '</th>';
                    }

                    if ($Settings->product_discount && $inv->product_discount != 0) {
                        $total_col += 2;
                        echo '<th>' . lang('Disc2 %') . '</th>';
                        echo '<th>' . lang('Disc2 Value') . '</th>';
                    } ?>
                    <th><?= lang('Total_without_VAT'); ?></th>
                    <?php
                    if ($Settings->tax1 && $inv->product_tax > 0) {
                        $total_col += 2;
                        echo '<th>' . lang('VAT%') . '</th>';
                        echo '<th>' . lang('VAT_value') . '</th>';
                    }
                    echo '<th>' . lang('Total_with_VAT') . '</th>';
                    ?>
                </tr>

            </thead>

            <tbody>

                <?php
                $r = 1;
                $tax_summary = [];
                $totalAmount = 0;
                // echo "<pre>";
                // print_r($inv);
                foreach ($rows as $row):
                    // echo "<pre>";
                    // print_r($row);
                    $subTotal = ($row->real_unit_price * $row->unit_quantity);
                    ?>
                    <tr>
                        <td style="text-align:center; width:40px; vertical-align:middle;"><?= $r; ?></td>
                        <td style="width: 30%; vertical-align:middle;">
                            <?= $row->product_code . ' - ' . $row->product_name . ($row->variant ? ' (' . $row->variant . ')' : ''); ?>
                            <?= $row->second_name ? '<br>' . $row->second_name : ''; ?>
                            <?= $row->supplier_part_no ? '<br>' . lang('supplier_part_no') . ': ' . $row->supplier_part_no : ''; ?>
                            <?= $row->details ? '<br>' . $row->details : ''; ?>
                            <?= ($row->expiry && $row->expiry != '0000-00-00') ? '<br>' . lang('EX') . ': ' . $this->sma->hrsd($row->expiry) : ''; ?>
                        </td>
                        <td style="width: 8%;text-align:center; vertical-align:middle;"><?= $row->avz_item_code ?: ''; ?>
                        </td>
                        <td style="width: 8%;text-align:center; vertical-align:middle;"><?= $row->batch_no ?: ''; ?></td>
                        <?php if ($Settings->indian_gst) {
                            ?>
                            <td style=" text-align:center; vertical-align:middle;"><?= $row->hsn_code ?: ''; ?></td>
                            <?php
                        } ?>
                        <td style="text-align:center; vertical-align:middle;">
                            <?= $this->sma->formatQuantity($row->unit_quantity); ?></td>
                        <?php
                        if ($inv->status == 'partial') {
                            echo '<td style="text-align:center;vertical-align:middle;width:80px;">' . $this->sma->formatQuantity($row->quantity_received) . '</td>';
                        }
                        ?>
                        <td style="text-align:right; width:100px;">
                            <?= $this->sma->formatNumber($row->bonus); ?>
                        </td>
                        <td style="text-align:right; width:100px;">
                            <!-- <?= $row->unit_cost != $row->real_unit_cost && $row->item_discount > 0 ? '<del>' . $this->sma->formatMoney($row->real_unit_price) . '</del>' : ''; ?> -->
                            <?= $this->sma->formatNumber($row->real_unit_price); ?>
                        </td>



                        <td style="text-align:right; width:120px;"><?= $this->sma->formatNumber($row->subtotal); ?></td>
                        <?php
                        if ($Settings->product_discount && $inv->product_discount != 0) {
                            echo '<td style=" text-align:right; vertical-align:middle;">' . ($row->discount1 != 0 ? $row->discount1 : '') . '</td>';

                            $unit_cost = $row->real_unit_price;
                            $pr_discount = $this->site->calculateDiscount($row->discount1 . '%', $row->real_unit_price);
                            $amount_after_dis1 = $unit_cost - $pr_discount;
                            $pr_discount2 = $this->site->calculateDiscount($row->discount2 . '%', $amount_after_dis1);
                            $pr_item_discount2 = $this->sma->formatDecimal($pr_discount2 * $row->unit_quantity);
                            $row->discount2 = $this->sma->formatNumber($row->discount2, null);
                            echo '<td style=" text-align:right; vertical-align:middle;">' . $this->sma->formatNumber($row->item_discount) . '</td>';

                            echo '<td style="width: 120px; text-align:right; vertical-align:middle;">' . ($row->discount2 != 0 ? $row->discount2 : '') . '</td>';
                            echo '<td style="width: 100px; text-align:right; vertical-align:middle;">' . $this->sma->formatNumber($row->second_discount_value) . '</td>';
                        }
                        ?>
                        <td style="text-align:right; width:120px;">
                            <?= $this->sma->formatNumber($row->totalbeforevat, null); ?></td>
                        <?php
                        $vat_value = 0;
                        if ($Settings->tax1 && $inv->product_tax > 0) {
                            $vat_value = $this->sma->formatNumber($row->item_tax);
                            echo '<td style="width: 100px; text-align:right; vertical-align:middle;">' . ($row->item_tax != 0 ? ($Settings->indian_gst ? $row->tax : $row->tax_code) : '') . '</td>';
                            echo '<td>' . $row->tax . '</td>';
                        }
                        ?>

                        <td style="text-align:right; width:120px;"><?= $this->sma->formatNumber($row->main_net); ?></td>

                    </tr>
                    <?php
                    //t-disc, net_before_vat, total_vat,total_after_vat
                

                    $r++;
                endforeach;


                ?>


                </tfoot>
        </table>
    </div>


    <div class="table-responsive table-summary" style="width:30%; float: right">
        <table class="table table-bordered table-hover table-striped print-table order-table">

            <tr>
                <td>Total</td>
                <td><?php echo $this->sma->formatNumber($inv->total); ?></td>
            </tr>
            <tr>
                <td>T-DISC</td>
                <td><?php echo $this->sma->formatNumber($inv->total_discount); ?></td>
            </tr>
            <tr>
                <td>Net Before VAT</td>
                <td><?php echo $this->sma->formatNumber($inv->total_net_sale); ?></td>
            </tr>
            <tr>
                <td>Total VAT</td>
                <td><?php echo $this->sma->formatNumber($inv->total_tax); ?></td>
            </tr>
            <tr>
                <td>Total After VAT</td>
                <td><?php echo $this->sma->formatNumber($inv->grand_total); ?></td>
            </tr>
        </table>
    </div>
</div>
</div>