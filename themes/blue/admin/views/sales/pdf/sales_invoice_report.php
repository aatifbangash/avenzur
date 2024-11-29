<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $this->lang->line('sale') . ' ' . $inv->reference_no; ?></title>
    <link href="<?= $assets ?>styles/pdf/pdf.css" rel="stylesheet">
    <style>
        tbody {
            border: 1px solid black;
        }

        td {
            font-size: 13px;
            border: 1px solid;
            padding: 5px !important;
        }

        th {
            font-size: 13px;
            border: 1px solid;
            padding: 5px !important;

        }

        .header {
            text-align: center;
            margin-bottom: 20px;
        }

        .content {
            margin: 20px;
        }

        .footer {
            text-align: center;
            margin-top: 20px;
        }

        .container {
            width: 100%;
        }

        .row {
            display: flex;
            width: 100%;
            font-size: 13px;
        }

        .col-half {
            width: 40%;
            padding: 3px;
            float: left;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .text-left {
            text-align: left;
        }

        .bold {
            font-weight: bold;
        }

        /* .well { border: 1px solid #ddd; padding: 10px; border-radius: 5px; background-color: #f7f7f7; } */
        .well {
            border: 1px solid #ddd;
            background-color: #f6f6f6;
            box-shadow: none;
            border-radius: 0px;
            font-size: 13px;
            height: auto;
        }

        .clearfix {
            clear: both;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .table th,
        .table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }

        .table th {
            background-color: #f2f2f2;
        }

        .table thead {
            background-color: #007bff;
            color: #fff;
        }
    </style>
</head>

<body>


    <div class="container">

        <div class="header">
            <img src="data:image/png;base64,<?php echo base64_encode(file_get_contents(base_url() . 'assets/uploads/logos/avenzur-logov2-024.png')); ?>"
                alt="Avenzur" style="max-width: 200px;">
        </div>

        <div class="well" style="width: 96%; height: 8%; background-color: #f6f6f6; padding: 15px;">
            <div style="width: 50%; float: left;">
                <p>Date: <?= $this->sma->hrld($inv->date); ?></p>
                <p>Reference: <?= $inv->reference_no; ?></p>
                <p>Sale Status: <?= $inv->sale_status; ?></p>
                <p>Payment Status: <?= $inv->payment_status; ?></p>
            </div>
            <div style="width: 50%; float:left; text-align: right; padding-top: 5px;">
                <!-- <img src="data:image/png;base64,<?php echo base64_encode(file_get_contents('http://localhost/avenzur/admin/misc/barcode/MjEz/code128/74/0/1')); ?>"
                    alt="QR Code" style="max-width: 100px;"> -->
                <img src="<?= admin_url('misc/barcode/' . $this->sma->base64url_encode($inv->reference_no) . '/code128/74/0/1'); ?>"
                    alt="<?= $inv->reference_no; ?>" class="bcimg" />
                <?php
                if ($Settings->ksa_qrcode) {
                    $qrtext = $this->inv_qrcode->base64([
                        'seller' => $biller->company && $biller->company != '-' ? $biller->company : $biller->name,
                        'vat_no' => $biller->vat_no ?: $biller->get_no,
                        'date' => $inv->date,
                        'grand_total' => $return_sale ? ($inv->grand_total + $return_sale->grand_total) : $inv->grand_total,
                        'total_tax_amount' => $return_sale ? ($inv->total_tax + $return_sale->total_tax) : $inv->total_tax,
                    ]);
                    echo $this->sma->qrcode('text', $qrtext, 2);
                } else {
                    echo $this->sma->qrcode('link', urlencode(site_url('view/sale/' . $inv->hash)), 2);
                }
                ?>
            </div>
        </div>
        <div style="clear: both"></div>
        <div class="row text-center">
            <h3>TAX INVOICE</h3>
        </div>

        <div class="row">
            <div class="col-half">
                <p class="bold">To:</p>
                <p><?= $customer->company && $customer->company != '-' ? $customer->company : $customer->name; ?></p>
                <?= $customer->company && $customer->company != '-' ? '' : 'Attn: ' . $customer->name ?>

                <?php
                echo $customer->address . '<br>' . $customer->city . ' ' . $customer->postal_code . ' ' . $customer->state . '<br>' . $customer->country;

                echo '<p>';
                if ($customer->sequence_code != '-' && $customer->sequence_code != '') {
                    echo '<br>' . lang('sequence_code') . ': ' . $customer->sequence_code;
                }
                if ($customer->vat_no != '-' && $customer->vat_no != '') {
                    echo '<br>' . lang('vat_no') . ': ' . $customer->vat_no;
                }
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
            <div class="col-half">
                <p class="bold">From:</p>
                <p><?= $biller->company && $biller->company != '-' ? $biller->company : $biller->name; ?></p>
                <?= $biller->company ? '' : 'Attn: ' . $biller->name ?>

                <?php
                echo $biller->address . '<br>' . $biller->city . ' ' . $biller->postal_code . ' ' . $biller->state . '<br>' . $biller->country;

                echo '<p>';

                if ($biller->vat_no != '-' && $biller->vat_no != '') {
                    echo '<br>' . lang('vat_no') . ': ' . $biller->vat_no;
                }
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
        </div>
        <div style="clear: both"></div>

        <?php
                    $col = $Settings->indian_gst ? 5 : 4;
            if ($Settings->product_discount && $inv->product_discount != 0) {
                $col++;
            }
            if ($Settings->tax1 && $inv->product_tax > 0) {
                $col++;
            }
            if ($Settings->product_discount && $inv->product_discount != 0 && $Settings->tax1 && $inv->product_tax > 0) {
                $tcol = $col - 2;
            } elseif ($Settings->product_discount && $inv->product_discount != 0) {
                $tcol = $col - 1;
            } elseif ($Settings->tax1 && $inv->product_tax > 0) {
                $tcol = $col - 1;
            } else {
                $tcol = $col;
            }
            ?>

        <div class="table-responsive">
            <table class="table table-bordered table-hover table-striped">
                <thead>
                    <tr>
                        <th><?= lang('no.'); ?></th>
                        <th><?= lang('description'); ?></th>
                        <?php if ($Settings->indian_gst) {
                            ?>
                            <th><?= lang('hsn_sac_code'); ?></th>
                            <?php
                        } ?>
                        <th><?= lang('quantity'); ?></th>
                        <th><?= lang('unit_price'); ?></th>
                        <?php
                        if ($Settings->tax1 && $inv->product_tax > 0) {
                            echo '<th>' . lang('tax') . '</th>';
                        }
                        if ($Settings->product_discount && $inv->product_discount != 0) {
                            echo '<th>' . lang('discount') . '</th>';
                        }
                        ?>
                        <th><?= lang('subtotal'); ?></th>
                    </tr>

                </thead>
                <tbody>
                    <!-- <tr>
                        <td style="text-align:center; vertical-align:middle;">1</td>
                        <td style="vertical-align:middle;">06281080012044 - Zertazine 10mg 10tab</td>
                        <td style="text-align:center; vertical-align:middle;">10.00</td>
                        <td style="text-align:right; vertical-align:middle;">7.50SR</td>
                        <td style="text-align:right; vertical-align:middle;">75.00SR</td>
                    </tr> -->
                    <?php $r = 1;
                    foreach ($rows as $row):
                        ?>
                        <tr>
                            <td style="text-align:center; width:40px; vertical-align:middle;"><?= $r; ?></td>
                            <td style="vertical-align:middle;">
                                <?= $row->product_name . ($row->variant ? ' (' . $row->variant . ')' : ''); ?>
                                <?= $row->second_name ? '<br>' . $row->second_name : ''; ?>
                                <?php // $row->details ? '<br>' . $row->details : ''; ?>
                                <?= $row->serial_no ? '<br>' . $row->serial_no : ''; ?>
                            </td>
                            <?php if ($Settings->indian_gst) {
                                ?>
                                <td style="width: 80px; text-align:center; vertical-align:middle;"><?= $row->hsn_code ?: ''; ?>
                                </td>
                                <?php
                            } ?>
                            <td style="width: 80px; text-align:center; vertical-align:middle;">
                                <?= $this->sma->formatQuantity($row->unit_quantity); ?></td>
                            <td style="text-align:right; width:100px;">
                                <?= $row->unit_price != $row->real_unit_price && $row->item_discount > 0 ? '<del>' . $this->sma->formatMoney($row->real_unit_price) . '</del>' : ''; ?>
                                <?= $this->sma->formatMoney($row->unit_price); ?>
                            </td>
                            <?php
                            if ($Settings->tax1 && $inv->product_tax > 0) {
                                echo '<td style="width: 100px; text-align:right; vertical-align:middle;">' . ($row->item_tax != 0 ? '<small>(' . ($Settings->indian_gst ? $row->tax : $row->tax_code) . ')</small>' : '') . ' ' . $this->sma->formatMoney($row->item_tax) . '</td>';
                            }
                            if ($Settings->product_discount && $inv->product_discount != 0) {
                                echo '<td style="width: 100px; text-align:right; vertical-align:middle;">' . ($row->discount != 0 ? '<small>(' . $row->discount . ')</small> ' : '') . $this->sma->formatMoney($row->item_discount) . '</td>';
                            }
                            ?>
                            <td style="text-align:right; width:120px;"><?= $this->sma->formatMoney($row->subtotal); ?></td>
                        </tr>
                        <?php
                        $r++;
                    endforeach;
                    if ($return_rows) {
                        echo '<tr class="warning"><td colspan="100%" class="no-border"><strong>' . lang('returned_items') . '</strong></td></tr>';
                        foreach ($return_rows as $row):
                            ?>
                            <tr class="warning">
                                <td style="text-align:center; width:40px; vertical-align:middle;"><?= $r; ?></td>
                                <td style="vertical-align:middle;">
                                    <?= $row->product_code . ' - ' . $row->product_name . ($row->variant ? ' (' . $row->variant . ')' : ''); ?>
                                    <?= $row->second_name ? '<br>' . $row->second_name : ''; ?>
                                    <?= $row->details ? '<br>' . $row->details : ''; ?>
                                    <?= $row->serial_no ? '<br>' . $row->serial_no : ''; ?>
                                </td>
                                <?php if ($Settings->indian_gst) {
                                    ?>
                                    <td style="width: 80px; text-align:center; vertical-align:middle;"><?= $row->hsn_code ?: ''; ?>
                                    </td>
                                    <?php
                                } ?>
                                <td style="width: 80px; text-align:center; vertical-align:middle;">
                                    <?= $this->sma->formatQuantity($row->quantity) . ' ' . $row->base_unit_code; ?></td>
                                <td style="text-align:right; width:100px;"><?= $this->sma->formatMoney($row->unit_price); ?>
                                </td>
                                <?php
                                if ($Settings->tax1 && $inv->product_tax > 0) {
                                    echo '<td style="width: 100px; text-align:right; vertical-align:middle;">' . ($row->item_tax != 0 ? '<small>(' . ($Settings->indian_gst ? $row->tax : $row->tax_code) . ')</small>' : '') . ' ' . $this->sma->formatMoney($row->item_tax) . '</td>';
                                }
                                if ($Settings->product_discount && $inv->product_discount != 0) {
                                    echo '<td style="width: 100px; text-align:right; vertical-align:middle;">' . ($row->discount != 0 ? '<small>(' . $row->discount . ')</small> ' : '') . $this->sma->formatMoney($row->item_discount) . '</td>';
                                } ?>
                                <td style="text-align:right; width:120px;"><?= $this->sma->formatMoney($row->subtotal); ?></td>
                            </tr>
                            <?php
                            $r++;
                        endforeach;
                    }
                    ?>
                </tbody>
                <!-- <tfoot>
                    <tr>
                        <td colspan="4" style="text-align:right; font-weight:bold;">Total Amount (SAR)</td>
                        <td style="text-align:right; font-weight:bold;">75.00SR</td>
                    </tr>
                    <tr>
                        <td colspan="4" style="text-align:right; font-weight:bold;">Paid (SAR)</td>
                        <td style="text-align:right; font-weight:bold;">0.00</td>
                    </tr>
                    <tr>
                        <td colspan="4" style="text-align:right; font-weight:bold;">Balance (SAR)</td>
                        <td style="text-align:right; font-weight:bold;">75.00SR</td>
                    </tr>
                </tfoot> -->
                <tfoot>
                    <?php if ($inv->grand_total != $inv->total) {
                        ?>
                        <tr>
                            <td colspan="<?= $tcol; ?>" style="text-align:right; padding-right:10px;"><?= lang('total'); ?>
                                (<?= $default_currency->code; ?>)
                            </td>
                            <?php
                            if ($Settings->tax1 && $inv->product_tax > 0) {
                                echo '<td style="text-align:right;">' . $this->sma->formatMoney($return_sale ? ($inv->product_tax + $return_sale->product_tax) : $inv->product_tax) . '</td>';
                            }
                            if ($Settings->product_discount && $inv->product_discount != 0) {
                                echo '<td style="text-align:right;">' . $this->sma->formatMoney($return_sale ? ($inv->product_discount + $return_sale->product_discount) : $inv->product_discount) . '</td>';
                            } ?>
                            <td style="text-align:right; padding-right:10px;">
                                <?= $this->sma->formatMoney($return_sale ? (($inv->total + $inv->product_tax) + ($return_sale->total + $return_sale->product_tax)) : ($inv->total + $inv->product_tax)); ?>
                            </td>
                        </tr>
                        <?php
                    } ?>
                    <?php
                    if ($return_sale) {
                        echo '<tr><td colspan="' . $col . '" style="text-align:right; padding-right:10px;;">' . lang('return_total') . ' (' . $default_currency->code . ')</td><td style="text-align:right; padding-right:10px;">' . $this->sma->formatMoney($return_sale->grand_total) . '</td></tr>';
                    }
                    if ($inv->surcharge != 0) {
                        echo '<tr><td colspan="' . $col . '" style="text-align:right; padding-right:10px;;">' . lang('return_surcharge') . ' (' . $default_currency->code . ')</td><td style="text-align:right; padding-right:10px;">' . $this->sma->formatMoney($inv->surcharge) . '</td></tr>';
                    }
                    ?>

                    <?php if ($Settings->indian_gst) {
                        if ($inv->cgst > 0) {
                            $cgst = $return_sale ? $inv->cgst + $return_sale->cgst : $inv->cgst;
                            echo '<tr><td colspan="' . $col . '" class="text-right">' . lang('cgst') . ' (' . $default_currency->code . ')</td><td class="text-right">' . ($Settings->format_gst ? $this->sma->formatMoney($cgst) : $cgst) . '</td></tr>';
                        }
                        if ($inv->sgst > 0) {
                            $sgst = $return_sale ? $inv->sgst + $return_sale->sgst : $inv->sgst;
                            echo '<tr><td colspan="' . $col . '" class="text-right">' . lang('sgst') . ' (' . $default_currency->code . ')</td><td class="text-right">' . ($Settings->format_gst ? $this->sma->formatMoney($sgst) : $sgst) . '</td></tr>';
                        }
                        if ($inv->igst > 0) {
                            $igst = $return_sale ? $inv->igst + $return_sale->igst : $inv->igst;
                            echo '<tr><td colspan="' . $col . '" class="text-right">' . lang('igst') . ' (' . $default_currency->code . ')</td><td class="text-right">' . ($Settings->format_gst ? $this->sma->formatMoney($igst) : $igst) . '</td></tr>';
                        }
                    } ?>

                    <?php if ($inv->order_discount != 0) {
                        echo '<tr><td colspan="' . $col . '" style="text-align:right; padding-right:10px;;">' . lang('order_discount') . ' (' . $default_currency->code . ')</td><td style="text-align:right; padding-right:10px;">' . ($inv->order_discount_id ? '<small>(' . $inv->order_discount_id . ')</small> ' : '') . $this->sma->formatMoney($return_sale ? ($inv->order_discount + $return_sale->order_discount) : $inv->order_discount) . '</td></tr>';
                    }
                    ?>
                    <?php if ($Settings->tax2 && $inv->order_tax != 0) {
                        echo '<tr><td colspan="' . $col . '" style="text-align:right; padding-right:10px;">' . lang('order_tax') . ' (' . $default_currency->code . ')</td><td style="text-align:right; padding-right:10px;">' . $this->sma->formatMoney($return_sale ? ($inv->order_tax + $return_sale->order_tax) : $inv->order_tax) . '</td></tr>';
                    }
                    ?>
                    <?php if ($inv->shipping != 0) {
                        echo '<tr><td colspan="' . $col . '" style="text-align:right; padding-right:10px;;">' . lang('shipping') . ' (' . $default_currency->code . ')</td><td style="text-align:right; padding-right:10px;">' . $this->sma->formatMoney($inv->shipping - ($return_sale && $return_sale->shipping ? $return_sale->shipping : 0)) . '</td></tr>';
                    }
                    ?>
                    <tr>
                        <td colspan="<?= $col; ?>" style="text-align:right; font-weight:bold;">
                            <?= lang('total_amountS'); ?>
                            (<?= $default_currency->code; ?>)
                        </td>
                        <td style="text-align:right; padding-right:10px; font-weight:bold;">
                            <?= $this->sma->formatMoney($return_sale ? ($inv->grand_total + $return_sale->grand_total) : $inv->grand_total); ?>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="<?= $col; ?>" style="text-align:right; font-weight:bold;"><?= lang('paid'); ?>
                            (<?= $default_currency->code; ?>)
                        </td>
                        <td style="text-align:right; font-weight:bold;">
                            <?= $this->sma->formatMoney($return_sale ? ($inv->paid + $return_sale->paid) : $inv->paid); ?>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="<?= $col; ?>" style="text-align:right; font-weight:bold;"><?= lang('balance'); ?>
                            (<?= $default_currency->code; ?>)
                        </td>
                        <td style="text-align:right; font-weight:bold;">
                            <?= $this->sma->formatMoney(($return_sale ? ($inv->grand_total + $return_sale->grand_total) : $inv->grand_total) - ($return_sale ? ($inv->paid + $return_sale->paid) : $inv->paid)); ?>
                        </td>
                    </tr>

                </tfoot>
            </table>
        </div>
        <?= $Settings->invoice_view > 0 ? $this->gst->summary($rows, $return_rows, ($return_sale ? $inv->product_tax + $return_sale->product_tax : $inv->product_tax)) : ''; ?>
        <!-- <div class="table-responsive">
            <h3>Tax Summary</h3>
            <table class="table table-bordered table-striped table-condensed">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Code</th>
                        <th>Qty/Weight</th>
                        <th>Tax Excl Amt</th>
                        <th>Tax Amt</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>No Tax</td>
                        <td style="text-align:center;">NT</td>
                        <td style="text-align:center;">10.00</td>
                        <td style="text-align:right;">75.00SR</td>
                        <td style="text-align:right;">0.00</td>
                    </tr>
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="4" style="text-align:right;">Total Tax Amount</th>
                        <th style="text-align:right;">0.00</th>
                    </tr>
                </tfoot>
            </table>
        </div> -->

        <div class="well" style="width: 30%; float: right; padding: 10px;">
            <p>
                <?= lang('created_by'); ?>:
                <?= $inv->created_by ? $created_by->first_name . ' ' . $created_by->last_name : $customer->name; ?> <br>
                <?= lang('date'); ?>: <?= $this->sma->hrld($inv->date); ?>
            </p>
            <?php if ($inv->updated_by) {
                ?>
                <p>
                    <?= lang('updated_by'); ?>: <?= $updated_by->first_name . ' ' . $updated_by->last_name; ?><br>
                    <?= lang('update_at'); ?>: <?= $this->sma->hrld($inv->updated_at); ?>
                </p>
                <?php
            } ?>
        </div>

    </div>

</body>

</html>