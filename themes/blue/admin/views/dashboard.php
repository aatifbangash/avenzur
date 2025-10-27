<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
function row_status($x)
{
    if ($x == null) {
        return '';
    } elseif ($x == 'pending') {
        return '<div class="text-center"><span class="label label-warning">' . lang($x) . '</span></div>';
    } elseif ($x == 'completed' || $x == 'paid' || $x == 'sent' || $x == 'received') {
        return '<div class="text-center"><span class="label label-success">' . lang($x) . '</span></div>';
    } elseif ($x == 'partial' || $x == 'transferring') {
        return '<div class="text-center"><span class="label label-info">' . lang($x) . '</span></div>';
    } elseif ($x == 'due') {
        return '<div class="text-center"><span class="label label-danger">' . lang($x) . '</span></div>';
    }
    return '<div class="text-center"><span class="label label-default">' . lang($x) . '</span></div>';
}

?>
<?php  if ($chatData) {
    foreach ($chatData as $month_sale) {
        $months[]     = date('M-Y', strtotime($month_sale->month));
        $msales[]     = $month_sale->sales;
        $mtax1[]      = $month_sale->tax1;
        $mtax2[]      = $month_sale->tax2;
        $mpurchases[] = $month_sale->purchases;
        $mtax3[]      = $month_sale->ptax;
    } }?>
    <div class="box" style="margin-bottom: 15px;">
        <div class="box-header">
            <h2 class="blue"><i class="fa-fw fa fa-bar-chart-o"></i><?= lang('Item_Movement_History_Report'); ?></h2>
        </div>
        <div class="box-content">
            <!-- <div class="row">
                <div class="col-md-12">
                    <p class="introtext"><?php echo lang('overview_chart_heading'); ?></p>

                    <div id="ov-chart" style="width:100%; height:450px;"></div>
                    <p class="text-center"><?= lang('chart_lable_toggle'); ?></p>

                </div>
            </div> -->

            <div class="row">
                <?php
                $attrib = ['data-toggle' => 'validator', 'role' => 'form'];
                echo admin_form_open_multipart('', $attrib)
                ?>
                <div class="col-lg-12">
                    <div class="row">

                        <div class="col-lg-12">

                            <div class="col-md-4">
                                <div class="form-group">
                                    <?= lang('Product', 'product'); ?>
                                    <?php // echo form_dropdown('product', $allProducts, set_value('product',$product),array('class' => 'form-control', 'id'=>'product'));
                                    ?>
                                    <?php echo form_input('sgproduct', (isset($_POST['sgproduct']) ? $_POST['sgproduct'] : ''), 'class="form-control" id="suggest_product2" data-bv-notempty="true"'); ?>
                                    <input type="hidden" name="product" value="<?= isset($_POST['product']) ? $_POST['product'] : 0 ?>" id="report_product_id2" />
                                </div>
                            </div>

                            <div class="col-md-2">
                                <div class="form-group">
                                    <?= lang('Type', 'Type'); ?>
                                    <?php echo form_dropdown('filterOnType', $filterOnTypeArr, set_value('filterOnType', $_POST['filterOnType']), array('class' => 'form-control', 'data-placeholder' => "-- Select Type --", 'id' => 'filterOnType'),  array('none')); ?>

                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label class="control-label" for="warehouse"><?= lang('warehouse'); ?></label>
                                    <?php
                                    $wh[''] = lang('select') . ' ' . lang('warehouse');
                                    foreach ($warehouses as $warehouse) {
                                        $wh[$warehouse->id] = $warehouse->name;
                                    }
                                    echo form_dropdown('warehouse', $wh, set_value('warehouse', $_POST['warehouse']), array('class' => 'form-control', 'data-placeholder' => "-- Select Type --", 'id' => 'warehouse'),  array('none'));
                                    ?>

                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <?= lang('start_date', 'start_date'); ?>
                                    <?php echo form_input('start_date', (isset($_POST['start_date']) ? $_POST['start_date'] : ''), 'class="form-control date" id="start_date"'); ?>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <?= lang('end_date', 'end_date'); ?>
                                    <?php echo form_input('end_date', (isset($_POST['end_date']) ? $_POST['end_date'] : ''), 'class="form-control date" id="end_date"'); ?>
                                </div>
                            </div>


                            <div class="col-md-2">
                                <div class="from-group">
                                    <button type="submit" style="" class="btn btn-primary" id="load_report"><?= lang('Load Report') ?></button>
                                </div>
                            </div>

                        </div>


                    </div>
                    <hr />
                    <div class="row">
                        <div class="controls table-controls" style="font-size: 12px !important;">
                            <table id="poTable" class="table items table-striped table-bordered table-condensed table-hover">
                                <thead>
                                <tr>
                                    <th><?= lang('SN'); ?></th>
                                    <th><?= lang('Date'); ?></th>
                                    <th><?= lang('Item Name'); ?></th>
                                    <th><?= lang('Item Code'); ?></th>
                                    <th><?= lang('Type'); ?></th>
                                    <th><?= lang('Warehouse/Pharmacy'); ?></th>

                                    <th><?= lang('Batch No.'); ?></th>

                                    <th><?= lang('Quantity'); ?></th>

                                </tr>
                                </thead>

                                <?php if ($reportData) { ?>
                                    <tbody style="text-align:center;">


                                    <?php
                                    $count = 1;
                                    $balanceQantity = 0;
                                    $totalValueOfItem  = 0;
                                    foreach ($reportData as $rp) {
                                        $balanceQantity += $rp->quantity;


                                        ?>
                                        <tr>
                                            <td><?= $count; ?></td>
                                            <td><?= $rp->movement_date; ?></td>
                                            <td><?= $rp->product_name; ?></td>
                                            <td><?= $rp->item_code; ?></td>
                                            <td><?= $rp->type; ?></td>
                                            <td><?= $rp->warehouse_name; ?></td>

                                            <td><?= $rp->batch_number; ?></td>


                                            <td><?= $this->sma->formatQuantity($rp->quantity); ?></td>

                                        </tr>
                                        <?php
                                        $count++;
                                    }


                                    ?>

                                    <tr>
                                        <td colspan="6"><stong>Total Quantity</strong></td>

                                        <td><?php echo $this->sma->formatQuantity($balanceQantity); ?></td>


                                    </tr>

                                    </tbody>
                                <?php } ?>
                                <tfoot></tfoot>
                            </table>
                        </div>

                    </div>

                </div>
            </div>
            <?php echo form_close(); ?>


        </div>
    </div>

    <div class="box" style="margin-bottom: 15px;">
        <div class="box-header">
            <h2 class="blue"><i class="fa-fw fa fa-bar-chart-o"></i><?= lang('Item_Current_Quantity_Pharmacy_Wise_Report'); ?></h2>
        </div>
        <div class="box-content">
            <!-- <div class="row">
                <div class="col-md-12">
                    <p class="introtext"><?php echo lang('overview_chart_heading'); ?></p>

                    <div id="ov-chart" style="width:100%; height:450px;"></div>
                    <p class="text-center"><?= lang('chart_lable_toggle'); ?></p>

                </div>
            </div> -->

            <div class="row">
                <?php
                // $attrib = ['data-toggle' => 'validator', 'role' => 'form'];
                // echo admin_form_open_multipart('', $attrib)
                ?>
                <div class="col-lg-12">
                    <div class="row">
                        <div class="controls table-controls" style="font-size: 12px !important;">
                            <table id="poTable" class="table items table-striped table-bordered table-condensed table-hover">
                                <thead>
                                <tr>
                                    <th><?= lang('SN'); ?></th>
                                    <th><?= lang('Product Name'); ?></th>
                                    <?php foreach($warehouses as $warehouse) {?>
                                        <th><?= lang($warehouse->name); ?></th>
                                    <?php }?>

                                    <th><?= lang('Total Quantity'); ?></th>

                                </tr>
                                </thead>

                                <?php if ($locationWiseData) { ?>
                                    <tbody style="text-align:center;">


                                    <?php
                                    $count = 1;
                                    $balanceQantity = 0;
                                    $totalValueOfItem  = 0;
                                    foreach ($locationWiseData as $row_loc) {

                                        ?>
                                        <tr>
                                            <td><?= $count; ?></td>
                                            <td><?= $row_loc->product_name; ?></td>
                                            <?php foreach($warehouses as $warehouse) {
                                                $col = "loc_".$warehouse->id; ?>
                                                <td><?= $row_loc->$col; ?></td>
                                            <?php } ?>

                                            <td><?= $this->sma->formatQuantity($row_loc->total_quantity); ?></td>

                                        </tr>
                                        <?php
                                        $count++;
                                    }


                                    ?>


                                    </tbody>
                                <?php } ?>
                                <tfoot></tfoot>
                            </table>
                        </div>

                    </div>

                </div>
            </div>
            <?php
            // echo form_close();
            ?>


        </div>
    </div>

<?php //} ?>


    <style>
        /* Modern Quick Links Styling */
        .quick-links-modern {
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            padding: 25px;
        }

        .quick-links-header {
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid #f0f0f0;
        }

        .quick-links-header h2 {
            font-size: 20px;
            font-weight: 600;
            color: #2c3e50;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .quick-links-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }

        .quick-link-card {
            background: linear-gradient(135deg, var(--card-color-start) 0%, var(--card-color-end) 100%);
            border-radius: 12px;
            padding: 25px 15px;
            text-align: center;
            transition: all 0.3s ease;
            text-decoration: none;
            color: white;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 130px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            position: relative;
            overflow: hidden;
        }

        .quick-link-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, rgba(255,255,255,0.1) 0%, rgba(255,255,255,0) 100%);
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .quick-link-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.15);
            text-decoration: none;
            color: white;
        }

        .quick-link-card:hover::before {
            opacity: 1;
        }

        .quick-link-icon {
            font-size: 36px;
            margin-bottom: 12px;
            display: block;
            transition: transform 0.3s ease;
        }

        .quick-link-card:hover .quick-link-icon {
            transform: scale(1.1);
        }

        .quick-link-title {
            font-size: 14px;
            font-weight: 600;
            margin: 0;
            text-transform: capitalize;
            letter-spacing: 0.3px;
        }

        /* Color schemes for different cards */
        .quick-link-products {
            --card-color-start: #667eea;
            --card-color-end: #764ba2;
        }

        .quick-link-sales {
            --card-color-start: #00b09b;
            --card-color-end: #96c93d;
        }

        .quick-link-quotes {
            --card-color-start: #f2994a;
            --card-color-end: #f2c94c;
        }

        .quick-link-purchases {
            --card-color-start: #eb3349;
            --card-color-end: #f45c43;
        }

        .quick-link-transfers {
            --card-color-start: #e91e63;
            --card-color-end: #f06292;
        }

        .quick-link-customers {
            --card-color-start: #4a5568;
            --card-color-end: #718096;
        }

        .quick-link-suppliers {
            --card-color-start: #2d3748;
            --card-color-end: #4a5568;
        }

        .quick-link-notifications {
            --card-color-start: #4facfe;
            --card-color-end: #00f2fe;
        }

        .quick-link-users {
            --card-color-start: #5f27cd;
            --card-color-end: #341f97;
        }

        .quick-link-settings {
            --card-color-start: #0abde3;
            --card-color-end: #10ac84;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .quick-links-grid {
                grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
                gap: 15px;
            }

            .quick-link-card {
                padding: 20px 10px;
                min-height: 110px;
            }

            .quick-link-icon {
                font-size: 30px;
            }

            .quick-link-title {
                font-size: 13px;
            }
        }

        @media (max-width: 480px) {
            .quick-links-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
    </style>

<?php if($Owner || $Admin) { ?>

    <div class="row" style="margin-bottom: 15px;">
        <div class="col-lg-12">
            <div class="quick-links-modern">
                <div class="quick-links-header">
                    <h2><i class="fa fa-th"></i><?= lang('quick_links') ?></h2>
                </div>

                <div class="quick-links-grid">
                    <a href="<?= admin_url('products') ?>" class="quick-link-card quick-link-products">
                        <i class="fa fa-barcode quick-link-icon"></i>
                        <p class="quick-link-title"><?= lang('products') ?></p>
                    </a>

                    <a href="<?= admin_url('sales') ?>" class="quick-link-card quick-link-sales">
                        <i class="fa fa-shopping-cart quick-link-icon"></i>
                        <p class="quick-link-title"><?= lang('sales') ?></p>
                    </a>

                    <a href="<?= admin_url('quotes') ?>" class="quick-link-card quick-link-quotes">
                        <i class="fa fa-file-text-o quick-link-icon"></i>
                        <p class="quick-link-title"><?= lang('quotes') ?></p>
                    </a>

                    <a href="<?= admin_url('purchases') ?>" class="quick-link-card quick-link-purchases">
                        <i class="fa fa-shopping-bag quick-link-icon"></i>
                        <p class="quick-link-title"><?= lang('purchases') ?></p>
                    </a>

                    <a href="<?= admin_url('transfers') ?>" class="quick-link-card quick-link-transfers">
                        <i class="fa fa-exchange quick-link-icon"></i>
                        <p class="quick-link-title"><?= lang('transfers') ?></p>
                    </a>

                    <a href="<?= admin_url('customers') ?>" class="quick-link-card quick-link-customers">
                        <i class="fa fa-users quick-link-icon"></i>
                        <p class="quick-link-title"><?= lang('customers') ?></p>
                    </a>

                    <a href="<?= admin_url('suppliers') ?>" class="quick-link-card quick-link-suppliers">
                        <i class="fa fa-truck quick-link-icon"></i>
                        <p class="quick-link-title"><?= lang('suppliers') ?></p>
                    </a>

                    <a href="<?= admin_url('notifications') ?>" class="quick-link-card quick-link-notifications">
                        <i class="fa fa-bell quick-link-icon"></i>
                        <p class="quick-link-title"><?= lang('notifications') ?></p>
                    </a>

                    <?php if ($Owner) { ?>
                        <a href="<?= admin_url('auth/users') ?>" class="quick-link-card quick-link-users">
                            <i class="fa fa-user-circle quick-link-icon"></i>
                            <p class="quick-link-title"><?= lang('users') ?></p>
                        </a>

                        <a href="<?= admin_url('system_settings') ?>" class="quick-link-card quick-link-settings">
                            <i class="fa fa-cogs quick-link-icon"></i>
                            <p class="quick-link-title"><?= lang('settings') ?></p>
                        </a>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>

<?php } else { ?>

    <div class="row" style="margin-bottom: 15px;">
        <div class="col-lg-12">
            <div class="quick-links-modern">
                <div class="quick-links-header">
                    <h2><i class="fa fa-th"></i><?= lang('quick_links') ?></h2>
                </div>

                <div class="quick-links-grid">
                    <?php if ($GP['products-index']) { ?>
                        <a href="<?= admin_url('products') ?>" class="quick-link-card quick-link-products">
                            <i class="fa fa-barcode quick-link-icon"></i>
                            <p class="quick-link-title"><?= lang('products') ?></p>
                        </a>
                    <?php } ?>

                    <?php if ($GP['sales-index']) { ?>
                        <a href="<?= admin_url('sales') ?>" class="quick-link-card quick-link-sales">
                            <i class="fa fa-shopping-cart quick-link-icon"></i>
                            <p class="quick-link-title"><?= lang('sales') ?></p>
                        </a>
                    <?php } ?>

                    <?php if ($GP['quotes-index']) { ?>
                        <a href="<?= admin_url('quotes') ?>" class="quick-link-card quick-link-quotes">
                            <i class="fa fa-file-text-o quick-link-icon"></i>
                            <p class="quick-link-title"><?= lang('quotes') ?></p>
                        </a>
                    <?php } ?>

                    <?php if ($GP['purchases-index']) { ?>
                        <a href="<?= admin_url('purchases') ?>" class="quick-link-card quick-link-purchases">
                            <i class="fa fa-shopping-bag quick-link-icon"></i>
                            <p class="quick-link-title"><?= lang('purchases') ?></p>
                        </a>
                    <?php } ?>

                    <?php if ($GP['transfers-index']) { ?>
                        <a href="<?= admin_url('transfers') ?>" class="quick-link-card quick-link-transfers">
                            <i class="fa fa-exchange quick-link-icon"></i>
                            <p class="quick-link-title"><?= lang('transfers') ?></p>
                        </a>
                    <?php } ?>

                    <?php if ($GP['customers-index']) { ?>
                        <a href="<?= admin_url('customers') ?>" class="quick-link-card quick-link-customers">
                            <i class="fa fa-users quick-link-icon"></i>
                            <p class="quick-link-title"><?= lang('customers') ?></p>
                        </a>
                    <?php } ?>

                    <?php if ($GP['suppliers-index']) { ?>
                        <a href="<?= admin_url('suppliers') ?>" class="quick-link-card quick-link-suppliers">
                            <i class="fa fa-truck quick-link-icon"></i>
                            <p class="quick-link-title"><?= lang('suppliers') ?></p>
                        </a>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>

<?php } ?>

    <div class="row" style="margin-bottom: 15px;">
        <div class="col-md-12">
            <div class="box">
                <div class="box-header">
                    <h2 class="blue"><i class="fa-fw fa fa-tasks"></i> <?= lang('latest_five') ?></h2>
                </div>
                <div class="box-content">
                    <div class="row">
                        <div class="col-md-12">

                            <ul id="dbTab" class="nav nav-tabs">
                                <?php if ($Owner || $Admin || $GP['sales-index']) {
                                    ?>
                                    <li class=""><a href="#sales"><?= lang('sales') ?></a></li>
                                    <?php
                                } if ($Owner || $Admin || $GP['quotes-index']) {
                                    ?>
                                    <li class=""><a href="#quotes"><?= lang('quotes') ?></a></li>
                                    <?php
                                } if ($Owner || $Admin || $GP['purchases-index']) {
                                    ?>
                                    <li class=""><a href="#purchases"><?= lang('purchases') ?></a></li>
                                    <?php
                                } if ($Owner || $Admin || $GP['transfers-index']) {
                                    ?>
                                    <li class=""><a href="#transfers"><?= lang('transfers') ?></a></li>
                                    <?php
                                } if ($Owner || $Admin || $GP['customers-index']) {
                                    ?>
                                    <li class=""><a href="#customers"><?= lang('customers') ?></a></li>
                                    <?php
                                } if ($Owner || $Admin || $GP['suppliers-index']) {
                                    ?>
                                    <li class=""><a href="#suppliers"><?= lang('suppliers') ?></a></li>
                                    <?php
                                } ?>
                            </ul>

                            <div class="tab-content">
                                <?php if ($Owner || $Admin || $GP['sales-index']) {
                                    ?>

                                    <div id="sales" class="tab-pane fade in">
                                        <div class="row">
                                            <div class="col-sm-12">
                                                <div class="table-responsive">
                                                    <table id="sales-tbl" cellpadding="0" cellspacing="0" border="0"
                                                           class="table table-bordered table-hover table-striped"
                                                           style="margin-bottom: 0;">
                                                        <thead>
                                                        <tr>
                                                            <th style="width:30px !important;">#</th>
                                                            <th><?= $this->lang->line('date'); ?></th>
                                                            <th><?= $this->lang->line('reference_no'); ?></th>
                                                            <th><?= $this->lang->line('customer'); ?></th>
                                                            <th><?= $this->lang->line('status'); ?></th>
                                                            <th><?= $this->lang->line('total'); ?></th>
                                                            <th><?= $this->lang->line('payment_status'); ?></th>
                                                            <th><?= $this->lang->line('paid'); ?></th>
                                                        </tr>
                                                        </thead>
                                                        <tbody>
                                                        <?php if (!empty($sales)) {
                                                            $r = 1;
                                                            foreach ($sales as $order) {
                                                                echo '<tr id="' . $order->id . '" class="' . ($order->pos ? 'receipt_link' : 'invoice_link') . '"><td>' . $r . '</td>
                                            <td>' . $this->sma->hrld($order->date) . '</td>
                                            <td>' . $order->reference_no . '</td>
                                            <td>' . $order->customer . '</td>
                                            <td>' . row_status($order->sale_status) . '</td>
                                            <td class="text-right">' . $this->sma->formatMoney($order->grand_total) . '</td>
                                            <td>' . row_status($order->payment_status) . '</td>
                                            <td class="text-right">' . $this->sma->formatMoney($order->paid) . '</td>
                                            </tr>';
                                                                $r++;
                                                            }
                                                        } else {
                                                            ?>
                                                            <tr>
                                                                <td colspan="7"
                                                                    class="dataTables_empty"><?= lang('no_data_available') ?></td>
                                                            </tr>
                                                            <?php
                                                        } ?>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <?php
                                } if ($Owner || $Admin || $GP['quotes-index']) {
                                    ?>

                                    <div id="quotes" class="tab-pane fade">
                                        <div class="row">
                                            <div class="col-sm-12">
                                                <div class="table-responsive">
                                                    <table id="quotes-tbl" cellpadding="0" cellspacing="0" border="0"
                                                           class="table table-bordered table-hover table-striped"
                                                           style="margin-bottom: 0;">
                                                        <thead>
                                                        <tr>
                                                            <th style="width:30px !important;">#</th>
                                                            <th><?= $this->lang->line('date'); ?></th>
                                                            <th><?= $this->lang->line('reference_no'); ?></th>
                                                            <th><?= $this->lang->line('customer'); ?></th>
                                                            <th><?= $this->lang->line('status'); ?></th>
                                                            <th><?= $this->lang->line('amount'); ?></th>
                                                        </tr>
                                                        </thead>
                                                        <tbody>
                                                        <?php if (!empty($quotes)) {
                                                            $r = 1;
                                                            foreach ($quotes as $quote) {
                                                                echo '<tr id="' . $quote->id . '" class="quote_link"><td>' . $r . '</td>
                                                        <td>' . $this->sma->hrld($quote->date) . '</td>
                                                        <td>' . $quote->reference_no . '</td>
                                                        <td>' . $quote->customer . '</td>
                                                        <td>' . row_status($quote->status) . '</td>
                                                        <td class="text-right">' . $this->sma->formatMoney($quote->grand_total) . '</td>
                                                    </tr>';
                                                                $r++;
                                                            }
                                                        } else {
                                                            ?>
                                                            <tr>
                                                                <td colspan="6"
                                                                    class="dataTables_empty"><?= lang('no_data_available') ?></td>
                                                            </tr>
                                                            <?php
                                                        } ?>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <?php
                                } if ($Owner || $Admin || $GP['purchases-index']) {
                                    ?>

                                    <div id="purchases" class="tab-pane fade in">
                                        <div class="row">
                                            <div class="col-sm-12">
                                                <div class="table-responsive">
                                                    <table id="purchases-tbl" cellpadding="0" cellspacing="0" border="0"
                                                           class="table table-bordered table-hover table-striped"
                                                           style="margin-bottom: 0;">
                                                        <thead>
                                                        <tr>
                                                            <th style="width:30px !important;">#</th>
                                                            <th><?= $this->lang->line('date'); ?></th>
                                                            <th><?= $this->lang->line('reference_no'); ?></th>
                                                            <th><?= $this->lang->line('supplier'); ?></th>
                                                            <th><?= $this->lang->line('status'); ?></th>
                                                            <th><?= $this->lang->line('amount'); ?></th>
                                                        </tr>
                                                        </thead>
                                                        <tbody>
                                                        <?php if (!empty($purchases)) {
                                                            $r = 1;
                                                            foreach ($purchases as $purchase) {
                                                                echo '<tr id="' . $purchase->id . '" class="purchase_link"><td>' . $r . '</td>
                                                    <td>' . $this->sma->hrld($purchase->date) . '</td>
                                                    <td>' . $purchase->reference_no . '</td>
                                                    <td>' . $purchase->supplier . '</td>
                                                    <td>' . row_status($purchase->status) . '</td>
                                                    <td class="text-right">' . $this->sma->formatMoney($purchase->grand_total) . '</td>
                                                </tr>';
                                                                $r++;
                                                            }
                                                        } else {
                                                            ?>
                                                            <tr>
                                                                <td colspan="6"
                                                                    class="dataTables_empty"><?= lang('no_data_available') ?></td>
                                                            </tr>
                                                            <?php
                                                        } ?>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <?php
                                } if ($Owner || $Admin || $GP['transfers-index']) {
                                    ?>

                                    <div id="transfers" class="tab-pane fade">
                                        <div class="row">
                                            <div class="col-sm-12">
                                                <div class="table-responsive">
                                                    <table id="transfers-tbl" cellpadding="0" cellspacing="0" border="0"
                                                           class="table table-bordered table-hover table-striped"
                                                           style="margin-bottom: 0;">
                                                        <thead>
                                                        <tr>
                                                            <th style="width:30px !important;">#</th>
                                                            <th><?= $this->lang->line('date'); ?></th>
                                                            <th><?= $this->lang->line('reference_no'); ?></th>
                                                            <th><?= $this->lang->line('from'); ?></th>
                                                            <th><?= $this->lang->line('to'); ?></th>
                                                            <th><?= $this->lang->line('status'); ?></th>
                                                            <th><?= $this->lang->line('amount'); ?></th>
                                                        </tr>
                                                        </thead>
                                                        <tbody>
                                                        <?php if (!empty($transfers)) {
                                                            $r = 1;
                                                            foreach ($transfers as $transfer) {
                                                                echo '<tr id="' . $transfer->id . '" class="transfer_link"><td>' . $r . '</td>
                                                <td>' . $this->sma->hrld($transfer->date) . '</td>
                                                <td>' . $transfer->transfer_no . '</td>
                                                <td>' . $transfer->from_warehouse_name . '</td>
                                                <td>' . $transfer->to_warehouse_name . '</td>
                                                <td>' . row_status($transfer->status) . '</td>
                                                <td class="text-right">' . $this->sma->formatMoney($transfer->grand_total) . '</td>
                                            </tr>';
                                                                $r++;
                                                            }
                                                        } else {
                                                            ?>
                                                            <tr>
                                                                <td colspan="7"
                                                                    class="dataTables_empty"><?= lang('no_data_available') ?></td>
                                                            </tr>
                                                            <?php
                                                        } ?>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <?php
                                } if ($Owner || $Admin || $GP['customers-index']) {
                                    ?>

                                    <div id="customers" class="tab-pane fade in">
                                        <div class="row">
                                            <div class="col-sm-12">
                                                <div class="table-responsive">
                                                    <table id="customers-tbl" cellpadding="0" cellspacing="0" border="0"
                                                           class="table table-bordered table-hover table-striped"
                                                           style="margin-bottom: 0;">
                                                        <thead>
                                                        <tr>
                                                            <th style="width:30px !important;">#</th>
                                                            <th><?= $this->lang->line('company'); ?></th>
                                                            <th><?= $this->lang->line('name'); ?></th>
                                                            <th><?= $this->lang->line('email'); ?></th>
                                                            <th><?= $this->lang->line('phone'); ?></th>
                                                            <th><?= $this->lang->line('address'); ?></th>
                                                        </tr>
                                                        </thead>
                                                        <tbody>
                                                        <?php if (!empty($customers)) {
                                                            $r = 1;
                                                            foreach ($customers as $customer) {
                                                                echo '<tr id="' . $customer->id . '" class="customer_link pointer"><td>' . $r . '</td>
                                            <td>' . $customer->company . '</td>
                                            <td>' . $customer->name . '</td>
                                            <td>' . $customer->email . '</td>
                                            <td>' . $customer->phone . '</td>
                                            <td>' . $customer->address . '</td>
                                        </tr>';
                                                                $r++;
                                                            }
                                                        } else {
                                                            ?>
                                                            <tr>
                                                                <td colspan="6"
                                                                    class="dataTables_empty"><?= lang('no_data_available') ?></td>
                                                            </tr>
                                                            <?php
                                                        } ?>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <?php
                                } if ($Owner || $Admin || $GP['suppliers-index']) {
                                    ?>

                                    <div id="suppliers" class="tab-pane fade">
                                        <div class="row">
                                            <div class="col-sm-12">
                                                <div class="table-responsive">
                                                    <table id="suppliers-tbl" cellpadding="0" cellspacing="0" border="0"
                                                           class="table table-bordered table-hover table-striped"
                                                           style="margin-bottom: 0;">
                                                        <thead>
                                                        <tr>
                                                            <th style="width:30px !important;">#</th>
                                                            <th><?= $this->lang->line('company'); ?></th>
                                                            <th><?= $this->lang->line('name'); ?></th>
                                                            <th><?= $this->lang->line('email'); ?></th>
                                                            <th><?= $this->lang->line('phone'); ?></th>
                                                            <th><?= $this->lang->line('address'); ?></th>
                                                        </tr>
                                                        </thead>
                                                        <tbody>
                                                        <?php if (!empty($suppliers)) {
                                                            $r = 1;
                                                            foreach ($suppliers as $supplier) {
                                                                echo '<tr id="' . $supplier->id . '" class="supplier_link pointer"><td>' . $r . '</td>
                                        <td>' . $supplier->company . '</td>
                                        <td>' . $supplier->name . '</td>
                                        <td>' . $supplier->email . '</td>
                                        <td>' . $supplier->phone . '</td>
                                        <td>' . $supplier->address . '</td>
                                    </tr>';
                                                                $r++;
                                                            }
                                                        } else {
                                                            ?>
                                                            <tr>
                                                                <td colspan="6"
                                                                    class="dataTables_empty"><?= lang('no_data_available') ?></td>
                                                            </tr>
                                                            <?php
                                                        } ?>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <?php
                                } ?>

                            </div>


                        </div>

                    </div>

                </div>
            </div>
        </div>

    </div>

    <script type="text/javascript">
        $(document).ready(function () {
            $('.order').click(function () {
                window.location.href = '<?=admin_url()?>orders/view/' + $(this).attr('id') + '#comments';
            });
            $('.invoice').click(function () {
                window.location.href = '<?=admin_url()?>orders/view/' + $(this).attr('id');
            });
            $('.quote').click(function () {
                window.location.href = '<?=admin_url()?>quotes/view/' + $(this).attr('id');
            });
        });
    </script>

<?php if (($Owner || $Admin) && $chatData) {
    ?>
    <style type="text/css" media="screen">
        .tooltip-inner {
            max-width: 500px;
        }
    </style>
    <script src="<?= $assets; ?>js/hc/highcharts.js"></script>
    <script type="text/javascript">
        $(function () {
            Highcharts.getOptions().colors = Highcharts.map(Highcharts.getOptions().colors, function (color) {
                return {
                    radialGradient: {cx: 0.5, cy: 0.3, r: 0.7},
                    stops: [[0, color], [1, Highcharts.Color(color).brighten(-0.3).get('rgb')]]
                };
            });
            $('#ov-chart').highcharts({
                chart: {direction: '<?= $Settings->user_rtl ? 'rtl' : 'ltr'; ?>'},
                credits: {enabled: false},
                title: {text: ''},
                xAxis: {categories: <?= json_encode($months); ?>},
                yAxis: {min: 0, title: ""},
                tooltip: {
                    shared: true,
                    followPointer: true,
                    formatter: function () {
                        if (this.key) {
                            return '<div dir="<?= $Settings->user_rtl ? 'rtl' : 'ltr'; ?>" class="tooltip-inner hc-tip" style="margin-bottom:0;">' + this.key + '<br><strong>' + currencyFormat(this.y) + '</strong> (' + formatNumber(this.percentage) + '%)';
                        } else {
                            var s = '<div dir="<?= $Settings->user_rtl ? 'rtl' : 'ltr'; ?>" class="well well-sm hc-tip" style="margin-bottom:0;"><h2 style="margin-top:0;">' + this.x + '</h2><table class="table table-striped"  style="margin-bottom:0;">';
                            $.each(this.points, function () {
                                s += '<tr><td style="color:{series.color};padding:0;text-align:<?= $Settings->user_rtl ? 'right' : 'left'; ?>;">' + this.series.name + ': </td><td style="color:{series.color};padding:0;text-align:right;"> <b>' +
                                    currencyFormat(this.y) + '</b></td></tr>';
                            });
                            s += '</table></div>';
                            return s;
                        }
                    },
                    useHTML: true, borderWidth: 0, shadow: false, valueDecimals: site.settings.decimals,
                    style: {fontSize: '14px', padding: '0', color: '#000000'}
                },
                series: [{
                    type: 'column',
                    name: '<?= lang('sp_tax'); ?>',
                    data: [<?php
                        echo implode(', ', $mtax1); ?>]
                },
                    {
                        type: 'column',
                        name: '<?= lang('order_tax'); ?>',
                        data: [<?php
                            echo implode(', ', $mtax2); ?>]
                    },
                    {
                        type: 'column',
                        name: '<?= lang('sales'); ?>',
                        data: [<?php
                            echo implode(', ', $msales); ?>]
                    }, {
                        type: 'spline',
                        name: '<?= lang('purchases'); ?>',
                        data: [<?php
                            echo implode(', ', $mpurchases); ?>],
                        marker: {
                            lineWidth: 2,
                            states: {
                                hover: {
                                    lineWidth: 4
                                }
                            },
                            lineColor: Highcharts.getOptions().colors[3],
                            fillColor: 'white'
                        }
                    }, {
                        type: 'spline',
                        name: '<?= lang('pp_tax'); ?>',
                        data: [<?php
                            echo implode(', ', $mtax3); ?>],
                        marker: {
                            lineWidth: 2,
                            states: {
                                hover: {
                                    lineWidth: 4
                                }
                            },
                            lineColor: Highcharts.getOptions().colors[3],
                            fillColor: 'white'
                        }
                    }, {
                        type: 'pie',
                        name: '<?= lang('stock_value'); ?>',
                        data: [
                            ['', 0],
                            ['', 0],
                            ['<?= lang('stock_value_by_price'); ?>', <?php echo $stock->stock_by_price; ?>],
                            ['<?= lang('stock_value_by_cost'); ?>', <?php echo $stock->stock_by_cost; ?>],
                        ],
                        center: [80, 42],
                        size: 80,
                        showInLegend: false,
                        dataLabels: {
                            enabled: false
                        }
                    }]
            });
        });
    </script>

    <script type="text/javascript">
        $(function () {
            <?php if ($lmbs) {
            ?>
            $('#lmbschart').highcharts({
                chart: {type: 'column', direction: '<?= $Settings->user_rtl ? 'rtl' : 'ltr'; ?>'},
                title: {text: ''},
                credits: {enabled: false},
                xAxis: {type: 'category', labels: {rotation: -60, style: {fontSize: '13px'}}},
                yAxis: {min: 0, title: {text: ''}},
                legend: {enabled: false},
                tooltip: {
                    shared: true,
                    followPointer: true,
                    formatter: function () {
                        var s = '<div class="well well-sm hc-tip" style="margin-bottom:0;text-align:<?= $Settings->user_rtl ? 'right' : 'left'; ?>;">';
                        $.each(this.points, function () {
                            s += '<span style="color:{series.color};padding:0"><b>' + this.key + '</b><br /> ' + this.series.name + ' <b>' +
                                currencyFormat(this.y) + '</b></span>';
                        });
                        s += '</div>';
                        return s;
                    },
                    useHTML: true, borderWidth: 0, shadow: false, valueDecimals: site.settings.decimals,
                    style: {fontSize: '14px', padding: '0', color: '#000000'}
                },
                series: [{
                    name: '<?=lang('sold'); ?>',
                    data: [<?php
                        foreach ($lmbs as $r) {
                            if ($r->quantity > 0) {
                                echo "['" . addSlashes($r->product_name) . '<br>(' . $r->product_code . ")', " . $r->quantity . '],';
                            }
                        } ?>],
                    dataLabels: {
                        enabled: true,
                        rotation: -90,
                        color: '#000',
                        align: 'right',
                        y: -25,
                        style: {fontSize: '12px'}
                    }
                }]
            });
            <?php
            }
            if ($bs) {
            ?>
            $('#bschart').highcharts({
                chart: {type: 'column', direction: '<?= $Settings->user_rtl ? 'rtl' : 'ltr'; ?>'},
                title: {text: ''},
                credits: {enabled: false},
                xAxis: {type: 'category', labels: {rotation: -60, style: {fontSize: '13px'}}},
                yAxis: {min: 0, title: {text: ''}},
                legend: {enabled: false},
                tooltip: {
                    shared: true,
                    followPointer: true,
                    formatter: function () {
                        var s = '<div class="well well-sm hc-tip" style="margin-bottom:0;text-align:<?= $Settings->user_rtl ? 'right' : 'left'; ?>;">';
                        $.each(this.points, function () {
                            s += '<span style="color:{series.color};padding:0"><b>' + this.key + '</b><br /> ' + this.series.name + ' <b>' +
                                currencyFormat(this.y) + '</b></span>';
                        });
                        s += '</div>';
                        return s;
                    },
                    useHTML: true, borderWidth: 0, shadow: false, valueDecimals: site.settings.decimals,
                    style: {fontSize: '14px', padding: '0', color: '#000000'}
                },
                series: [{
                    name: '<?=lang('sold'); ?>',
                    data: [<?php
                        foreach ($bs as $r) {
                            if ($r->quantity > 0) {
                                echo "['" . addSlashes($r->product_name) . '<br>(' . $r->product_code . ")', " . $r->quantity . '],';
                            }
                        } ?>],
                    dataLabels: {
                        enabled: true,
                        rotation: -90,
                        color: '#000',
                        align: 'right',
                        y: -25,
                        style: {fontSize: '12px'}
                    }
                }]
            });
            <?php
            } ?>
        });
    </script>
    <div class="row" style="margin-bottom: 15px;">
        <div class="col-sm-6">
            <div class="box">
                <div class="box-header">
                    <h2 class="blue"><i
                                class="fa-fw fa fa-line-chart"></i><?= lang('best_sellers'), ' (' . date('M-Y', time()) . ')'; ?>
                    </h2>
                </div>
                <div class="box-content">
                    <div class="row">
                        <div class="col-md-12">
                            <div id="bschart" style="width:100%; height:450px;"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6">
            <div class="box">
                <div class="box-header">
                    <h2 class="blue"><i
                                class="fa-fw fa fa-line-chart"></i><?= lang('best_sellers') . ' (' . date('M-Y', strtotime('-1 month')) . ')'; ?>
                    </h2>
                </div>
                <div class="box-content">
                    <div class="row">
                        <div class="col-md-12">
                            <div id="lmbschart" style="width:100%; height:450px;"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php
} ?>