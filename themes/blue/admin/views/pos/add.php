<?php defined('BASEPATH') or exit('No direct script access allowed'); ?><!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?=lang('pos_module') . ' | ' . $Settings->site_name;?></title>
    <script type="text/javascript">if(parent.frames.length !== 0){top.location = '<?=admin_url('pos')?>';}</script>
    <base href="<?=base_url()?>"/>
    <meta http-equiv="cache-control" content="max-age=0"/>
    <meta http-equiv="cache-control" content="no-cache"/>
    <meta http-equiv="expires" content="0"/>
    <meta http-equiv="pragma" content="no-cache"/>
    <link rel="shortcut icon" href="<?=$assets?>images/icon.png"/>
    <link rel="stylesheet" href="<?=$assets?>styles/theme.css" type="text/css"/>
    <link rel="stylesheet" href="<?=$assets?>styles/style.css" type="text/css"/>
    <link rel="stylesheet" href="<?=$assets?>pos/css/posajax.css" type="text/css"/>
    <link rel="stylesheet" href="<?=$assets?>pos/css/print.css" type="text/css" media="print"/>
    <link href="<?= base_url('assets/custom/pos.css') ?>" rel="stylesheet"/>
    <script type="text/javascript" src="<?=$assets?>js/jquery-2.0.3.min.js"></script>
    <script type="text/javascript" src="<?=$assets?>js/jquery-migrate-1.2.1.min.js"></script>
    <script type="text/javascript" src="<?= $assets ?>js/plugins/decimal/decimal.js"></script>
    <!--[if lt IE 9]>
    <script src="<?=$assets?>js/jquery.js"></script>
    <![endif]-->
    <?php if ($Settings->user_rtl) {
        ?>
        <link href="<?=$assets?>styles/helpers/bootstrap-rtl.min.css" rel="stylesheet"/>
        <link href="<?=$assets?>styles/style-rtl.css" rel="stylesheet"/>
        <script type="text/javascript">
            $(document).ready(function () {
                $('.pull-right, .pull-left').addClass('flip');
            });
        </script>
        <?php
    }
    ?>
<style>
    .ui-widget-content{ z-index:9999;}
    .modal{z-index:1085 !important;}
</style>    
    
</head>
<body>
<noscript>
    <div class="global-site-notice noscript">
        <div class="notice-inner">
            <p><strong>JavaScript seems to be disabled in your browser.</strong><br>You must have JavaScript enabled in
                your browser to utilize the functionality of this website.</p>
        </div>
    </div>
</noscript>

<div class="modal fade" id="itemModal" tabindex="-1" role="dialog" aria-labelledby="itemModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content" style="min-width:800px !important;">
            <div class="modal-header">
                <h5 class="modal-title" id="itemModalLabel">Select an Item</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <!-- The content will be dynamically generated here -->
            </div>
        </div>
    </div>
</div>

<div id="wrapper">
    <header id="header" class="navbar">
        <div class="container">
            <a class="navbar-brand" href="<?=admin_url()?>"><span class="logo"><span class="pos-logo-lg"><?=$Settings->site_name?></span><span class="pos-logo-sm"><?=lang('pos')?></span></span></a>

            <div class="header-nav">
                <ul class="nav navbar-nav pull-right">
                    <li class="dropdown">
                        <a class="btn account dropdown-toggle" data-toggle="dropdown" href="#">
                            <img alt="" src="<?=$this->session->userdata('avatar') ? base_url() . 'assets/uploads/avatars/thumbs/' . $this->session->userdata('avatar') : $assets . 'images/' . $this->session->userdata('gender') . '.png';?>" class="mini_avatar img-rounded">

                            <div class="user hidden-small">
                                <span><?=lang('welcome')?>! <?=$this->session->userdata('username');?></span>
                            </div>
                        </a>
                        <ul class="dropdown-menu pull-right">
                            <li>
                                <a href="<?=admin_url('auth/profile/' . $this->session->userdata('user_id'));?>">
                                    <i class="fa fa-user"></i> <?=lang('profile');?>
                                </a>
                            </li>
                            <li>
                                <a href="<?=admin_url('auth/profile/' . $this->session->userdata('user_id') . '/#cpassword');?>">
                                    <i class="fa fa-key"></i> <?=lang('change_password');?>
                                </a>
                            </li>
                            <li class="divider"></li>
                            <li>
                                <a href="<?=admin_url('auth/logout');?>">
                                    <i class="fa fa-sign-out"></i> <?=lang('logout');?>
                                </a>
                            </li>
                        </ul>
                    </li>
                </ul>

                <ul class="nav navbar-nav pull-right">
                    <li class="dropdown">
                        <a class="btn bblue pos-tip" title="<?=lang('dashboard')?>" data-placement="bottom" href="<?=admin_url('welcome')?>">
                            <i class="fa fa-dashboard"></i>
                        </a>
                    </li>
                    <?php if ($Owner) {
                        ?>
                        <li class="dropdown hidden-sm hidden-small">
                            <a class="btn pos-tip" title="<?=lang('settings')?>" data-placement="bottom" href="<?=admin_url('pos/settings')?>">
                                <i class="fa fa-cogs"></i>
                            </a>
                        </li>
                        <?php
                    }
                    ?>
                    <li class="dropdown hidden-xs hidden-small">
                        <a class="btn pos-tip" title="<?=lang('calculator')?>" data-placement="bottom" href="#" data-toggle="dropdown">
                            <i class="fa fa-calculator"></i>
                        </a>
                        <ul class="dropdown-menu pull-right calc">
                            <li class="dropdown-content">
                                <span id="inlineCalc"></span>
                            </li>
                        </ul>
                    </li>
                    <li class="dropdown hidden-sm hidden-small">
                        <a class="btn pos-tip" title="<?=lang('shortcuts')?>" data-placement="bottom" href="#" data-toggle="modal" data-target="#sckModal">
                            <i class="fa fa-key"></i>
                        </a>
                    </li>
                    <li class="dropdown hidden-sm hidden-small">
                        <a class="btn pos-tip" title="<?=lang('Check Stock')?>" data-placement="bottom" href="#" data-toggle="modal" data-target="#srModal">
                            <i class="fa fa-laptop"></i> 
                        </a>
                    </li>
                    <li class="dropdown hidden-small">
                        <a type="button" class="btn pos-tip" title="<?=lang('pole_display')?>" data-placement="bottom" id="rfd-pole-connect">
                            <i class="fa fa-play"></i>
                        </a>
                    </li>
                    <!--<li class="dropdown hidden-small">
                        <a class="btn pos-tip" title="<?=lang('view_bill_screen')?>" data-placement="bottom" href="<?=admin_url('pos/view_bill')?>" target="_blank">
                            <i class="fa fa-laptop"></i>
                        </a>
                    </li>-->
                    <li class="dropdown">
                        <a class="btn blightOrange pos-tip" id="opened_bills" title="<span><?=lang('suspended_sales')?></span>" data-placement="bottom" data-html="true" href="<?=admin_url('pos/opened_bills')?>" data-toggle="ajax">
                            <i class="fa fa-th"></i>
                        </a>
                    </li>
                    <li class="dropdown hidden-small">
                        <a class="btn bdarkGreen pos-tip" id="register_details" title="<span><?=lang('register_details')?></span>" data-placement="bottom" data-html="true" href="<?=admin_url('pos/register_details')?>" data-toggle="modal" data-target="#myModal">
                            <i class="fa fa-check-circle"></i>
                        </a>
                    </li>
                    <li class="dropdown">
                        <a class="btn borange pos-tip" id="close_register" title="<span><?=lang('close_register')?></span>" data-placement="bottom" data-html="true" data-backdrop="static" href="<?=admin_url('pos/close_register')?>" data-toggle="modal" data-target="#myModal">
                            <i class="fa fa-times-circle"></i>
                        </a>
                    </li>
                    <li class="dropdown hidden-small">
                        <a class="btn borange pos-tip" id="add_expense" title="<span><?=lang('add_expense')?></span>" data-placement="bottom" data-html="true" href="<?=admin_url('purchases/add_expense')?>" data-toggle="modal" data-target="#myModal">
                            <i class="fa fa-dollar"></i>
                        </a>
                    </li>
                    <?php if ($Owner) {
                        ?>
                        <li class="dropdown">
                            <a class="btn bdarkGreen pos-tip" id="today_profit" title="<span><?=lang('today_profit')?></span>" data-placement="bottom" data-html="true" href="<?=admin_url('reports/profit')?>" data-toggle="modal" data-target="#myModal">
                                <i class="fa fa-hourglass-half"></i>
                            </a>
                        </li>
                        <?php
                    }
                    ?>
                    <?php if ($Owner || $Admin) {
                        ?>
                        <li class="dropdown">
                            <a class="btn bdarkGreen pos-tip" id="today_sale" title="<span><?=lang('today_sale')?></span>" data-placement="bottom" data-html="true" href="<?=admin_url('pos/today_sale')?>" data-toggle="modal" data-target="#myModal">
                                <i class="fa fa-heart"></i>
                            </a>
                        </li>
                        <li class="dropdown hidden-xs">
                            <a class="btn bblue pos-tip" title="<?=lang('list_open_registers')?>" data-placement="bottom" href="<?=admin_url('pos/registers')?>">
                                <i class="fa fa-list"></i>
                            </a>
                        </li>
                        <?php
                    }
                    ?>
                    <li class="dropdown hidden-xs">
                        <a class="btn bred pos-tip" title="<?=lang('clear_ls')?>" data-placement="bottom" id="clearLS" href="#">
                            <i class="fa fa-eraser"></i>
                        </a>
                    </li>
                </ul>

                <ul class="nav navbar-nav pull-right hidden-smallest">
                    <li class="dropdown">
                        <a class="btn bblack" style="cursor: default;"><span id="display_time"></span></a>
                    </li>
                </ul>
            </div>
        </div>
    </header>

    <div id="content">
        <div class="c1">
            <div class="pos">
                <?php
                if ($error) {
                    echo '<div class="alert alert-danger"><button type="button" class="close fa-2x" data-dismiss="alert">&times;</button>' . $error . '</div>';
                }
                ?>
                <?php
                if ($message) {
                    echo '<div class="alert alert-success"><button type="button" class="close fa-2x" data-dismiss="alert">&times;</button>' . $message . '</div>';
                }
                ?>
                <div id="pos">
                    <?php $attrib = ['data-toggle' => 'validator', 'role' => 'form', 'id' => 'pos-sale-form'];
                    echo admin_form_open('pos', $attrib);?>
                    
                    <input type="hidden" id="grand_total_sale" name="grand_total_sale" value="">
                    <input type="hidden" id="grand_total_net_sale" name="grand_total_net_sale" value="">
                    <input type="hidden" id="grand_total_discount" name="grand_total_discount" value="">
                    <input type="hidden" id="grand_total_vat" name="grand_total_vat" value="">
                    <input type="hidden" id="grand_total" name="grand_total" value="">	
                    <input type="hidden" id="cost_goods_sold" name="cost_goods_sold" value="">

                    <div id="leftdiv">
                        <div id="printhead">
                            <h4 style="text-transform:uppercase;"><?php echo $Settings->site_name; ?></h4>
                            <?php
                                echo '<h5 style="text-transform:uppercase;">' . $this->lang->line('order_list') . '</h5>';
                                echo $this->lang->line('date') . ' ' . $this->sma->hrld(date('Y-m-d H:i:s'));
                            ?>
                        </div>
                        <div id="left-top">
                            <div
                                style="position: absolute; <?=$Settings->user_rtl ? 'right:-9999px;' : 'left:-9999px;';?>"><?php echo form_input('test', '', 'id="test" class="kb-pad"'); ?></div>
                            <div class="form-group">
                                <div class="input-group" style="z-index:1;">
                                    <?php
                                    echo form_input('customer', ($_POST['customer'] ?? ''), 'id="poscustomer" data-placeholder="' . $this->lang->line('select') . ' ' . $this->lang->line('customer') . '" required="required" class="form-control pos-input-tip" style="width:100%;"');
                                    ?>
                                    <div class="input-group-addon no-print" style="padding: 2px 8px; border-left: 0;">
                                        <a href="#" id="toogle-customer-read-attr" class="external">
                                            <i class="fa fa-pencil" id="addIcon" style="font-size: 1.2em;"></i>
                                        </a>
                                    </div>
                                    <div class="input-group-addon no-print" style="padding: 2px 7px; border-left: 0;">
                                        <a href="#" id="view-customer" class="external" data-toggle="modal" data-target="#myModal">
                                            <i class="fa fa-eye" id="addIcon" style="font-size: 1.2em;"></i>
                                        </a>
                                    </div>
                                <?php if ($Owner || $Admin || $GP['customers-add']) {
                                    ?>
                                    <div class="input-group-addon no-print" style="padding: 2px 8px;">
                                        <a href="<?=admin_url('customers/add'); ?>" id="add-customer" class="external" data-toggle="modal" data-target="#myModal">
                                            <i class="fa fa-plus-circle" id="addIcon" style="font-size: 1.5em;"></i>
                                        </a>
                                    </div>
                                    <?php
                                } ?>
                                </div>
                                <div style="clear:both;"></div>
                            </div>
                            <div class="no-print">
                                <?php if ($Owner || $Admin || !$this->session->userdata('warehouse_id')) {
                                    ?>
                                    <div class="form-group">
                                        <?php
                                            $wh[''] = '';
                                        foreach ($pharmacies as $warehouse) {
                                            $wh[$warehouse->id] = $warehouse->name.' ('.$warehouse->code.')';
                                        }
                                        echo form_dropdown('warehouse', $wh, ($_POST['warehouse'] ?? $Settings->default_warehouse), 'id="poswarehouse" class="form-control pos-input-tip" data-placeholder="' . $this->lang->line('select') . ' ' . $this->lang->line('warehouse') . '" required="required" style="width:100%;" '); ?>
                                    </div>
                                    <?php
                                } else {
                                    $warehouse_input = [
                                        'type'  => 'hidden',
                                        'name'  => 'warehouse',
                                        'id'    => 'poswarehouse',
                                        'value' => $this->session->userdata('warehouse_id'),
                                    ];

                                    echo form_input($warehouse_input);
                                }
                                ?>
                                <div class="form-group" id="ui">
                                    <?php if ($Owner || $Admin || $GP['products-add']) {
                                        ?>
                                    <div class="input-group">
                                        <?php
                                    } ?>
                                    <?php echo form_input('add_item', '', 'class="form-control pos-tip" id="add_item" data-placement="top" data-trigger="focus" placeholder="' . $this->lang->line('search_product_by_name_code') . '" title="' . $this->lang->line('au_pr_name_tip') . '"'); ?>
                                    <?php if ($Owner || $Admin || $GP['products-add']) {
                                        ?>
                                        <div class="input-group-addon" style="padding: 2px 8px;">
                                            <a href="#" id="addManually">
                                                <i class="fa fa-plus-circle" id="addIcon" style="font-size: 1.5em;"></i>
                                            </a>
                                        </div>
                                    </div>
                                        <?php
                                    } ?>
                                    <div style="clear:both;"></div>
                                </div>
                            </div>
                        </div>
                        <div id="print">
                            <div id="left-middle">
                                <div id="product-list">
                                    <table class="table items table-striped table-bordered table-condensed table-hover sortable_table"
                                           id="posTable" style="margin-bottom: 0;">
                                        <thead>
                                        <tr>
                                            <th width="40%"><?=lang('product');?></th>
                                            <th width="10%"><?=lang('price');?></th> 
                                            <th width="10%"><?=lang('VAT');?></th>
                                            <th width="15%"><?=lang('qty');?></th>
                                            <th width="18%"><?=lang('Nearest Expiry');?></th>
                                            <th width="13%"><?=lang('subtotal');?></th>
                                            <th style="width: 5%; text-align: center;">
                                                <i class="fa fa-trash-o" style="opacity:0.5; filter:alpha(opacity=50);"></i>
                                            </th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        </tbody>
                                    </table>
                                    <div style="clear:both;"></div>
                                </div>
                            </div>
                            <div style="clear:both;"></div>
                            <div id="left-bottom">
                                <table id="totalTable"
                                       style="width:100%; float:right; padding:5px; color:#000; background: #FFF;">
                                    <tr>
                                        <td style="padding: 5px 10px;border-top: 1px solid #DDD;"><?=lang('Total');?></td>
                                        <td class="text-right" style="padding: 5px 10px;font-size: 14px; font-weight:bold;border-top: 1px solid #DDD;">
                                            <span id="titems">0</span>
                                        </td>
                                        <td style="padding: 5px 10px;border-top: 1px solid #DDD;"><?=lang('Net Total');?></td>
                                        <td class="text-right" style="padding: 5px 10px;font-size: 14px; font-weight:bold;border-top: 1px solid #DDD;">
                                            <span id="total">0.00</span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 5px 10px;"><?=lang('order_tax');?>
                                            <!--<a href="#" id="pptax2">
                                                <i class="fa fa-edit"></i>
                                            </a>-->
                                        </td>
                                        <td class="text-right" style="padding: 5px 10px;font-size: 14px; font-weight:bold;">
                                            <span id="ttax2">0.00</span>
                                        </td>
                                        <td style="padding: 5px 10px;"><?=lang('discount');?>
                                            <?php if ($Owner || $Admin || $this->session->userdata('allow_discount')) {
                                                ?>
                                            <a href="#" id="ppdiscount">
                                                <i class="fa fa-edit"></i>
                                            </a>
                                                <?php
                                            } ?>
                                        </td>
                                      
                                        

                                        <td class="text-right" style="padding: 5px 10px;font-weight:bold;">
                                            <span id="tds">0.00</span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 5px 10px; border-top: 1px solid #666; border-bottom: 1px solid #333; font-weight:bold; background:#333; color:#FFF;" colspan="2">
                                            <?=lang('total_payable');?>
                                            <a href="#" id="pshipping">
                                                <i class="fa fa-plus-square"></i>
                                            </a>
                                            <span id="tship"></span>
                                        </td>
                                        <td class="text-right" style="padding:5px 10px 5px 10px; font-size: 14px;border-top: 1px solid #666; border-bottom: 1px solid #333; font-weight:bold; background:#333; color:#FFF;" colspan="2">
                                            <span id="gtotal">0.00</span>
                                        </td>
                                    </tr>
                                </table>

                                <div class="clearfix"></div>
                                <div id="botbuttons" class="col-xs-12 text-center">
                                    <input type="hidden" name="biller" id="biller" value="<?= ($Owner || $Admin || !$this->session->userdata('biller_id')) ? $pos_settings->default_biller : $this->session->userdata('biller_id')?>"/>
                                    <div class="row">
                                        <div class="col-xs-4" style="padding: 0;">
                                            <div class="btn-group-vertical btn-block">
                                                <button type="button" class="btn btn-warning btn-block btn-flat"
                                                id="suspend">
                                                    <?=lang('suspend'); ?>
                                                </button>
                                                <button type="button" class="btn btn-danger btn-block btn-flat"
                                                id="reset">
                                                    <?= lang('cancel'); ?>
                                                </button>
                                            </div>

                                        </div>
                                        <div class="col-xs-4" style="padding: 0;">
                                            <div class="btn-group-vertical btn-block">
                                                <button type="button" class="btn btn-info btn-block" id="print_order">
                                                    <?=lang('order');?>
                                                </button>

                                                <button type="button" class="btn btn-primary btn-block" id="print_bill">
                                                    <?=lang('bill');?>
                                                </button>
                                            </div>
                                        </div>
                                        <div class="col-xs-4" style="padding: 0;">
                                            <button type="button" class="btn btn-success btn-block" id="payment" style="height:67px;">
                                                <i class="fa fa-money" style="margin-right: 5px;"></i><?=lang('payment');?>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div style="clear:both; height:5px;"></div>
                                <div id="num">
                                    <div id="icon"></div>
                                </div>
                                <span id="hidesuspend"></span>
                                <input type="hidden" name="pos_note" value="" id="pos_note">
                                <input type="hidden" name="staff_note" value="" id="staff_note">

                                <div id="payment-con">
                                    <?php for ($i = 1; $i <= 5; $i++) {
                                        ?>
                                        <input type="hidden" name="amount[]" id="amount_val_<?=$i?>" value=""/>
                                        <input type="hidden" name="balance_amount[]" id="balance_amount_<?=$i?>" value=""/>
                                        <input type="hidden" name="paid_by[]" id="paid_by_val_<?=$i?>" value="cash"/>
                                        <input type="hidden" name="cc_no[]" id="cc_no_val_<?=$i?>" value=""/>
                                        <input type="hidden" name="paying_gift_card_no[]" id="paying_gift_card_no_val_<?=$i?>" value=""/>
                                        <input type="hidden" name="cc_holder[]" id="cc_holder_val_<?=$i?>" value=""/>
                                        <input type="hidden" name="cheque_no[]" id="cheque_no_val_<?=$i?>" value=""/>
                                        <input type="hidden" name="cc_month[]" id="cc_month_val_<?=$i?>" value=""/>
                                        <input type="hidden" name="cc_year[]" id="cc_year_val_<?=$i?>" value=""/>
                                        <input type="hidden" name="cc_type[]" id="cc_type_val_<?=$i?>" value=""/>
                                        <input type="hidden" name="cc_cvv2[]" id="cc_cvv2_val_<?=$i?>" value=""/>
                                        <input type="hidden" name="payment_note[]" id="payment_note_val_<?=$i?>" value=""/>
                                        <?php
                                    }
                                    ?>
                                </div>
                                <input name="order_tax" type="hidden" value="<?=$suspend_sale ? $suspend_sale->order_tax_id : ($old_sale ? $old_sale->order_tax_id : $Settings->default_tax_rate2);?>" id="postax2">
                                <input name="discount" type="hidden" value="<?=$suspend_sale ? $suspend_sale->order_discount_id : ($old_sale ? $old_sale->order_discount_id : '');?>" id="posdiscount">
                                <input name="shipping" type="hidden" value="<?=$suspend_sale ? $suspend_sale->shipping : ($old_sale ? $old_sale->shipping : '0');?>" id="posshipping">
                                <input type="hidden" name="rpaidby" id="rpaidby" value="cash" style="display: none;"/>
                                <input type="hidden" name="total_items" id="total_items" value="0" style="display: none;"/>
                                <input type="submit" id="submit_sale" value="Submit Sale" style="display: none;"/>
                            </div>
                        </div>

                    </div>
                    <?php echo form_close(); ?>
                    <div id="cp">
                        <div id="cpinner">
                            <div class="quick-menu">
                                <div id="proContainer">
                                    <div id="ajaxproducts">
                                        <div id="item-list" style="overflow: scroll;">
                                            <?php echo $products; ?>
                                        </div>
                                        <div class="btn-group btn-group-justified pos-grid-nav">
                                            <div class="btn-group">
                                                <button style="z-index:10002;" class="btn btn-primary pos-tip" title="<?=lang('previous')?>" type="button" id="previous">
                                                    <i class="fa fa-chevron-left"></i>
                                                </button>
                                            </div>
                                            <?php if ($Owner || $Admin || $GP['sales-add_gift_card']) {
                                                ?>
                                            <div class="btn-group">
                                                <button style="z-index:10003;" class="btn btn-primary pos-tip" type="button" id="sellGiftCard" title="<?=lang('sell_gift_card')?>">
                                                    <i class="fa fa-credit-card" id="addIcon"></i> <?=lang('sell_gift_card')?>
                                                </button>
                                            </div>
                                                <?php
                                            }
                                            ?>
                                            <div class="btn-group">
                                                <button style="z-index:10004;" class="btn btn-primary pos-tip" title="<?=lang('next')?>" type="button" id="next">
                                                    <i class="fa fa-chevron-right"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    <div style="clear:both;"></div>
                                </div>
                            </div>
                        </div>
                        <div style="clear:both;"></div>
                    </div>
                    <div style="clear:both;"></div>
                </div>
                <div style="clear:both;"></div>
            </div>
        </div>
    </div>
</div>
<div class="rotate btn-cat-con">
   <!-- <button type="button" id="open-pharmacy-request" class="btn btn-danger open-pharmacy-request"><?= lang('pharmacy Req'); ?></button>-->
    <?php if ($Owner || $GP['stock_request_view']) { ?>
    <button type="button" id="open-warehouse-request" class="btn btn-success open-warehouse-request"><?= lang('Warehouse Req'); ?></button>
    <?php } ?>
    <button type="button" id="open-brands" class="btn btn-info open-brands"><?= lang('brands'); ?></button>
    <button type="button" id="open-subcategory" class="btn btn-warning open-subcategory"><?= lang('subcategories'); ?></button>
    <button type="button" id="open-category" class="btn btn-primary open-category"><?= lang('categories'); ?></button>
</div>
<div id="warehouse-slider">
    <div id="warehouse-list">
      <?php include 'warehouse_request.php';  ?>
    </div>
</div>
<!--<div id="pharmacy-slider">
    <div id="pharmacy-list">
        <?php
            // for ($i = 1; $i <= 40; $i++) {
        /*foreach ($brands as $brand) {
            echo '<button id="brand-' . $brand->id . "\" type=\"button\" value='" . $brand->id . "' class=\"btn-prni brand\" ><img src=\"assets/uploads/thumbs/" . ($brand->image ? $brand->image : 'no_image.png') . "\" class='img-rounded img-thumbnail' /><span>" . $brand->name . '</span></button>';
        }*/
            // }
        ?>
    </div>
</div>-->
<div id="brands-slider">
    <div id="brands-list">
        <?php
            // for ($i = 1; $i <= 40; $i++) {
        foreach ($brands as $brand) {
            echo '<button id="brand-' . $brand->id . "\" type=\"button\" value='" . $brand->id . "' class=\"btn-prni brand\" ><img src=\"assets/uploads/thumbs/" . ($brand->image ? $brand->image : 'no_image.png') . "\" class='img-rounded img-thumbnail' /><span>" . $brand->name . '</span></button>';
        }
            // }
        ?>
    </div>
</div>
<div id="category-slider">
    <!--<button type="button" class="close open-category"><i class="fa fa-2x">&times;</i></button>-->
    <div id="category-list">
        <?php
            //for ($i = 1; $i <= 40; $i++) {
        foreach ($categories as $category) {
            echo '<button id="category-' . $category->id . "\" type=\"button\" value='" . $category->id . "' class=\"btn-prni category\" ><img src=\"assets/uploads/thumbs/" . ($category->image ? $category->image : 'no_image.png') . "\" class='img-rounded img-thumbnail' /><span>" . $category->name . '</span></button>';
        }
            //}
        ?>
    </div>
</div>
<div id="subcategory-slider">
    <!--<button type="button" class="close open-category"><i class="fa fa-2x">&times;</i></button>-->
    <div id="subcategory-list">
        <?php
        if (!empty($subcategories)) {
            foreach ($subcategories as $category) {
                echo '<button id="subcategory-' . $category->id . "\" type=\"button\" value='" . $category->id . "' class=\"btn-prni subcategory\" ><img src=\"assets/uploads/thumbs/" . ($category->image ? $category->image : 'no_image.png') . "\" class='img-rounded img-thumbnail' /><span>" . $category->name . '</span></button>';
            }
        }
        ?>
    </div>
</div>
<div class="modal fade in" id="paymentModal" tabindex="-1" role="dialog" aria-labelledby="payModalLabel" data-backdrop="static"
     aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true"><i
                            class="fa fa-2x">&times;</i></span><span class="sr-only"><?=lang('close');?></span></button>
                <h4 class="modal-title" id="payModalLabel"><?=lang('finalize_sale');?></h4>
            </div>
            <div class="modal-body" id="payment_content">
                <div class="row">
                    <div class="col-md-10 col-sm-9">
                        <?php if ($Owner || $Admin || !$this->session->userdata('biller_id')) {
                            ?>
                            <div class="form-group">
                                <?=lang('biller', 'biller'); ?>
                                <?php
                                foreach ($billers as $biller) {
                                    $btest           = ($biller->company && $biller->company != '-' ? $biller->company : $biller->name);
                                    $bl[$biller->id] = $btest;
                                    $posbillers[]    = ['logo' => $biller->logo, 'company' => $btest];
                                    if ($biller->id == $pos_settings->default_biller) {
                                        $posbiller = ['logo' => $biller->logo, 'company' => $btest];
                                    }
                                }
                                echo form_dropdown('biller', $bl, ($_POST['biller'] ?? $pos_settings->default_biller), 'class="form-control" id="posbiller" required="required"'); ?>
                            </div>
                            <?php
                        } else {
                            $biller_input = [
                            'type'  => 'hidden',
                            'name'  => 'biller',
                            'id'    => 'posbiller',
                            'value' => $this->session->userdata('biller_id'),
                            ];

                            echo form_input($biller_input);

                            foreach ($billers as $biller) {
                                $btest        = ($biller->company && $biller->company != '-' ? $biller->company : $biller->name);
                                $posbillers[] = ['logo' => $biller->logo, 'company' => $btest];
                                if ($biller->id == $this->session->userdata('biller_id')) {
                                    $posbiller = ['logo' => $biller->logo, 'company' => $btest];
                                }
                            }
                        }
                        ?>
                        <div class="form-group">
                            <div class="row">
                                <div class="col-sm-6">
                                    <?=form_textarea('sale_note', '', 'id="sale_note" class="form-control kb-text skip" style="height: 100px;" placeholder="' . lang('sale_note') . '" maxlength="250"');?>
                                </div>
                                <div class="col-sm-6">
                                    <?=form_textarea('staffnote', '', 'id="staffnote" class="form-control kb-text skip" style="height: 100px;" placeholder="' . lang('staff_note') . '" maxlength="250"');?>
                                </div>
                            </div>
                        </div>
                        <div class="clearfir"></div>
                        <div id="payments">
                            <div class="well well-sm well_1">
                                <div class="payment">
                                    <div class="row">
                                        <div class="col-sm-5">
                                            <div class="form-group">
                                                <?=lang('amount', 'amount_1');?>
                                                <input name="amount[]" type="text" id="amount_1"
                                                       class="pa form-control kb-pad1 amount"/>
                                            </div>
                                        </div>
                                        <div class="col-sm-5 col-sm-offset-1">
                                            <div class="form-group">
                                                <?=lang('paying_by', 'paid_by_1');?>
                                                <select name="paid_by[]" id="paid_by_1" class="form-control paid_by">
                                                    <?= $this->sma->paid_opts(); ?>
                                                    <?=$pos_settings->paypal_pro ? '<option value="ppp">' . lang('paypal_pro') . '</option>' : '';?>
                                                    <?=$pos_settings->stripe ? '<option value="stripe">' . lang('stripe') . '</option>' : '';?>
                                                    <?=$pos_settings->authorize ? '<option value="authorize">' . lang('authorize') . '</option>' : '';?>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-sm-5">
                                            <div class="form-group">
                                                <?=lang('Customer Name', 'customer_name');?>
                                                <input name="customer_name[]" type="text" id="customer_name"
                                                       class="pa form-control customer_name"/>
                                            </div>
                                        </div>
                                        <div class="col-sm-5 col-sm-offset-1">
                                            <div class="form-group">
                                                <?=lang('Mobile Number', 'mobile_number');?>
                                                <input name="mobile_number[]" type="text" id="mobile_number"
                                                       class="pa form-control mobile_number"/>
                                            </div>
                                        </div>
                                    </div>

                                    <div id="instructions-area"></div>

                                    <div class="row">
                                        <div class="col-sm-11">
                                            <div class="form-group gc_1" style="display: none;">
                                                <?=lang('gift_card_no', 'gift_card_no_1');?>
                                                <input name="paying_gift_card_no[]" type="text" id="gift_card_no_1"
                                                       class="pa form-control kb-pad gift_card_no"/>

                                                <div id="gc_details_1"></div>
                                            </div>
                                            <div class="pcc_1" style="display:none;">
                                                <span style="display:none;">
                                                <div class="form-group">
                                                    <input type="text" id="swipe_1" class="form-control swipe"
                                                           placeholder="<?=lang('swipe')?>"/>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <input name="cc_no[]" type="text" id="pcc_no_1"
                                                                   class="form-control"
                                                                   placeholder="<?=lang('cc_no')?>"/>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-group">

                                                            <input name="cc_holer[]" type="text" id="pcc_holder_1"
                                                                   class="form-control"
                                                                   placeholder="<?=lang('cc_holder')?>"/>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <div class="form-group">
                                                            <select name="cc_type[]" id="pcc_type_1"
                                                                    class="form-control pcc_type"
                                                                    placeholder="<?=lang('card_type')?>">
                                                                <option value="Visa"><?=lang('Visa');?></option>
                                                                <option
                                                                    value="MasterCard"><?=lang('MasterCard');?></option>
                                                                <option value="Amex"><?=lang('Amex');?></option>
                                                                <option
                                                                    value="Discover"><?=lang('Discover');?></option>
                                                            </select>
                                                            <!-- <input type="text" id="pcc_type_1" class="form-control" placeholder="<?=lang('card_type')?>" />-->
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <div class="form-group">
                                                            <input name="cc_month[]" type="text" id="pcc_month_1"
                                                                   class="form-control"
                                                                   placeholder="<?=lang('month')?>"/>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <div class="form-group">

                                                            <input name="cc_year" type="text" id="pcc_year_1"
                                                                   class="form-control"
                                                                   placeholder="<?=lang('year')?>"/>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <div class="form-group">

                                                            <input name="cc_cvv2" type="text" id="pcc_cvv2_1"
                                                                   class="form-control"
                                                                   placeholder="<?=lang('cvv2')?>"/>
                                                        </div>
                                                    </div>
                                                </div>
                                                </span>
                                            </div>
                                            <div class="pcheque_1" style="display:none;">
                                                <div class="form-group"><?=lang('cheque_no', 'cheque_no_1');?>
                                                    <input name="cheque_no[]" type="text" id="cheque_no_1"
                                                           class="form-control cheque_no"/>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <?=lang('payment_note', 'payment_note');?>
                                                <textarea name="payment_note[]" id="payment_note_1"
                                                          class="pa form-control kb-text payment_note"></textarea>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div id="multi-payment"></div>
                        <button type="button" class="btn btn-primary col-md-12 addButton"><i
                                class="fa fa-plus"></i> <?=lang('add_more_payments')?></button>
                        <div style="clear:both; height:15px;"></div>
                        <div class="font16">
                            <table class="table table-bordered table-condensed table-striped" style="margin-bottom: 0;">
                                <tbody>
                                <tr>
                                    <td width="25%"><?=lang('total_items');?></td>
                                    <td width="25%" class="text-right"><span id="item_count">0.00</span></td>
                                    <td width="25%"><?=lang('total_payable');?></td>
                                    <td width="25%" class="text-right"><span id="twt">0.00</span></td>
                                </tr>
                                <tr>
                                    <td><?=lang('total_paying');?></td>
                                    <td class="text-right"><span id="total_paying">0.00</span></td>
                                    <td><?=lang('balance');?></td>
                                    <td class="text-right"><span id="balance">0.00</span></td>
                                </tr>
                                </tbody>
                            </table>
                            <div class="clearfix"></div>
                        </div>
                    </div>
                    <div class="col-md-2 col-sm-3 text-center">
                        <span style="font-size: 1.2em; font-weight: bold;"><?=lang('quick_cash');?></span>

                        <div class="btn-group btn-group-vertical">
                            <button type="button" class="btn btn-lg btn-info quick-cash" id="quick-payable">0.00
                            </button>
                            <?php
                            foreach (lang('quick_cash_notes') as $cash_note_amount) {
                                echo '<button type="button" class="btn btn-lg btn-warning quick-cash">' . $cash_note_amount . '</button>';
                            }
                            ?>
                            <button type="button" class="btn btn-lg btn-danger"
                                    id="clear-cash-notes"><?=lang('clear');?></button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-block btn-lg btn-primary" id="submit-sale"><?=lang('submit');?></button>
            </div>
        </div>
    </div>
</div>

<div class="modal" id="cmModal" tabindex="-1" role="dialog" aria-labelledby="cmModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">
                    <i class="fa fa-2x">&times;</i></span>
                    <span class="sr-only"><?=lang('close');?></span>
                </button>
                <h4 class="modal-title" id="cmModalLabel"></h4>
            </div>
            <div class="modal-body" id="pr_popover_content">
                <div class="form-group">
                    <?= lang('comment', 'icomment'); ?>
                    <?= form_textarea('comment', '', 'class="form-control" id="icomment" style="height:80px;"'); ?>
                </div>
                <div class="form-group">
                    <?= lang('ordered', 'iordered'); ?>
                    <?php
                    $opts = [0 => lang('no'), 1 => lang('yes')];
                    ?>
                    <?= form_dropdown('ordered', $opts, '', 'class="form-control" id="iordered" style="width:100%;"'); ?>
                </div>
                <input type="hidden" id="irow_id" value=""/>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" id="editComment"><?=lang('submit')?></button>
            </div>
        </div>
    </div>
</div>

<div class="modal" id="prModal" tabindex="-1" role="dialog" aria-labelledby="prModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true"><i
                            class="fa fa-2x">&times;</i></span><span class="sr-only"><?=lang('close');?></span></button>
                <h4 class="modal-title" id="prModalLabel"></h4>
            </div>
            <div class="modal-body" id="pr_popover_content">
                <form class="form-horizontal" role="form">
                    <?php if ($Settings->tax1) {
                        ?>
                        <div class="form-group">
                            <label class="col-sm-4 control-label"><?=lang('product_tax')?></label>
                            <div class="col-sm-8">
                                <?php
                                    $tr[''] = '';
                                foreach ($tax_rates as $tax) {
                                    $tr[$tax->id] = $tax->name;
                                }
                                echo form_dropdown('ptax', $tr, '', 'id="ptax" class="form-control pos-input-tip" style="width:100%;"'); ?>
                            </div>
                        </div>
                        <?php
                    } ?>
                    <?php if ($Settings->product_serial) {
                        ?>
                        <div class="form-group">
                            <label for="pserial" class="col-sm-4 control-label"><?=lang('serial_no')?></label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control kb-text" id="pserial">
                            </div>
                        </div>
                        <?php
                    } ?>
                    <div class="form-group">
                        <label for="pquantity" class="col-sm-4 control-label"><?=lang('quantity')?></label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control kb-pad" id="pquantity">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="punit" class="col-sm-4 control-label"><?= lang('product_unit') ?></label>
                        <div class="col-sm-8">
                            <div id="punits-div"></div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="poption" class="col-sm-4 control-label"><?=lang('product_option')?></label>
                        <div class="col-sm-8">
                            <div id="poptions-div"></div>
                        </div>
                    </div>
                    <?php if ($Settings->product_discount && ($Owner || $Admin || $this->session->userdata('allow_discount'))) {
                        ?>
                        <div class="form-group">
                            <label for="pdiscount" class="col-sm-4 control-label"><?=lang('product_discount')?></label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control kb-pad" id="pdiscount">
                            </div>
                        </div>
                        <?php
                    } ?>
                    <div class="form-group"><label for="msp" class="col-sm-4 control-label" style="padding-top:0px;">Minimum Sale price</label>
                            <div class="col-sm-8">
                                <span id="prmsp" style="color:red;font-size:18px;">n/a</span>
                            </div>
                    </div> 
                    <div class="form-group">
                        <label for="pprice" class="col-sm-4 control-label"><?=lang('unit_price')?></label>

                        <div class="col-sm-8">
                            <input type="text" class="form-control kb-pad" id="pprice" <?= ($Owner || $Admin || $GP['edit_price']) ? '' : 'readonly'; ?>>
                        </div>
                    </div>
                    <table class="table table-bordered table-striped">
                        <tr>
                            <th style="width:25%;"><?=lang('net_unit_price');?></th>
                            <th style="width:25%;"><span id="net_price"></span></th>
                            <th style="width:25%;"><?=lang('product_tax');?></th>
                            <th style="width:25%;"><span id="pro_tax"></span></th>
                        </tr>
                    </table>
                    <?php if ($Settings->product_discount && ($Owner || $Admin || $this->session->userdata('allow_discount'))) {
                        ?>
                        <div class="form-group">
                            <label for="psubt"
                                   class="col-sm-4 control-label"><?= lang('subtotal') ?></label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control" id="psubt" disabled="disabled">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="pdiscount"
                                   class="col-sm-4 control-label"><?= lang('adjust_subtotal') ?></label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control" id="padiscount" placeholder="<?= lang('nearest_subtotal'); ?>">
                            </div>
                        </div>
                        <?php
                    } ?>
                    <input type="hidden" id="punit_price" value=""/>
                    <input type="hidden" id="old_tax" value=""/>
                    <input type="hidden" id="old_qty" value=""/>
                    <input type="hidden" id="old_price" value=""/>
                    <input type="hidden" id="row_id" value=""/>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" id="editItem"><?=lang('submit')?></button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade in" id="gcModal" tabindex="-1" role="dialog" aria-labelledby="mModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i
                        class="fa fa-2x">&times;</i></button>
                <h4 class="modal-title" id="myModalLabel"><?=lang('sell_gift_card');?></h4>
            </div>
            <div class="modal-body">
                <p><?=lang('enter_info');?></p>

                <div class="alert alert-danger gcerror-con" style="display: none;">
                    <button data-dismiss="alert" class="close" type="button">×</button>
                    <span id="gcerror"></span>
                </div>
                <div class="form-group">
                    <?=lang('card_no', 'gccard_no');?> *
                    <div class="input-group">
                        <?php echo form_input('gccard_no', '', 'class="form-control" id="gccard_no"'); ?>
                        <div class="input-group-addon" style="padding-left: 10px; padding-right: 10px;">
                            <a href="#" id="genNo"><i class="fa fa-cogs"></i></a>
                        </div>
                    </div>
                </div>
                <input type="hidden" name="gcname" value="<?=lang('gift_card')?>" id="gcname"/>

                <div class="form-group">
                    <?=lang('value', 'gcvalue');?> *
                    <?php echo form_input('gcvalue', '', 'class="form-control" id="gcvalue"'); ?>
                </div>
                <div class="form-group">
                    <?=lang('price', 'gcprice');?> *
                    <?php echo form_input('gcprice', '', 'class="form-control" id="gcprice"'); ?>
                </div>
                <div class="form-group">
                    <?=lang('customer', 'gccustomer');?>
                    <?php echo form_input('gccustomer', '', 'class="form-control" id="gccustomer"'); ?>
                </div>
                <div class="form-group">
                    <?=lang('expiry_date', 'gcexpiry');?>
                    <?php echo form_input('gcexpiry', $this->sma->hrsd(date('Y-m-d', strtotime('+2 year'))), 'class="form-control date" id="gcexpiry"'); ?>
                </div>

            </div>
            <div class="modal-footer">
                <button type="button" id="addGiftCard" class="btn btn-primary"><?=lang('sell_gift_card')?></button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade in" id="mModal" tabindex="-1" role="dialog" aria-labelledby="mModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true"><i
                            class="fa fa-2x">&times;</i></span><span class="sr-only"><?=lang('close');?></span></button>
                <h4 class="modal-title" id="mModalLabel"><?=lang('add_product_manually')?></h4>
            </div>
            <div class="modal-body" id="pr_popover_content">
                <form class="form-horizontal" role="form">
                    <div class="form-group">
                        <label for="mcode" class="col-sm-4 control-label"><?=lang('product_code')?> *</label>

                        <div class="col-sm-8">
                            <input type="text" class="form-control kb-text" id="mcode">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="mname" class="col-sm-4 control-label"><?=lang('product_name')?> *</label>

                        <div class="col-sm-8">
                            <input type="text" class="form-control kb-text" id="mname">
                        </div>
                    </div>
                    <?php if ($Settings->tax1) {
                        ?>
                        <div class="form-group">
                            <label for="mtax" class="col-sm-4 control-label"><?=lang('product_tax')?> *</label>

                            <div class="col-sm-8">
                                <?php
                                    $tr[''] = '';
                                foreach ($tax_rates as $tax) {
                                    $tr[$tax->id] = $tax->name;
                                }
                                echo form_dropdown('mtax', $tr, '', 'id="mtax" class="form-control pos-input-tip" style="width:100%;"'); ?>
                            </div>
                        </div>
                        <?php
                    }
                    ?>
                    <div class="form-group">
                        <label for="mquantity" class="col-sm-4 control-label"><?=lang('quantity')?> *</label>

                        <div class="col-sm-8">
                            <input type="text" class="form-control kb-pad" id="mquantity">
                        </div>
                    </div>
                    <?php if ($Settings->product_discount && ($Owner || $Admin || $this->session->userdata('allow_discount'))) {
                        ?>
                        <div class="form-group">
                            <label for="mdiscount"
                                   class="col-sm-4 control-label"><?=lang('product_discount')?></label>

                            <div class="col-sm-8">
                                <input type="text" class="form-control kb-pad" id="mdiscount">
                            </div>
                        </div>
                        <?php
                    }
                    ?>
                    <div class="form-group">
                        <label for="mprice" class="col-sm-4 control-label"><?=lang('unit_price')?> *</label>

                        <div class="col-sm-8">
                            <input type="text" class="form-control kb-pad" id="mprice">
                        </div>
                    </div>
                    <table class="table table-bordered table-striped">
                        <tr>
                            <th style="width:25%;"><?=lang('net_unit_price');?></th>
                            <th style="width:25%;"><span id="mnet_price"></span></th>
                            <th style="width:25%;"><?=lang('product_tax');?></th>
                            <th style="width:25%;"><span id="mpro_tax"></span></th>
                        </tr>
                    </table>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" id="addItemManually"><?=lang('submit')?></button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade in" id="sckModal" tabindex="-1" role="dialog" aria-labelledby="ModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">
                <i class="fa fa-2x">&times;</i></span><span class="sr-only"><?=lang('close');?></span>
                </button>
                <button type="button" class="btn btn-xs btn-default no-print pull-right" style="margin-right:15px;" onclick="window.print();">
                    <i class="fa fa-print"></i> <?= lang('print'); ?>
                </button>
                <h4 class="modal-title" id="mModalLabel"><?=lang('shortcut_keys')?></h4>
            </div>
            <div class="modal-body" id="pr_popover_content">
                <table class="table table-bordered table-striped table-condensed table-hover"
                       style="margin-bottom: 0px;">
                    <thead>
                    <tr>
                        <th><?=lang('shortcut_keys')?></th>
                        <th><?=lang('actions')?></th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr>
                        <td><?=$pos_settings->focus_add_item?></td>
                        <td><?=lang('focus_add_item')?></td>
                    </tr>
                    <tr>
                        <td><?=$pos_settings->add_manual_product?></td>
                        <td><?=lang('add_manual_product')?></td>
                    </tr>
                    <tr>
                        <td><?=$pos_settings->customer_selection?></td>
                        <td><?=lang('customer_selection')?></td>
                    </tr>
                    <tr>
                        <td><?=$pos_settings->add_customer?></td>
                        <td><?=lang('add_customer')?></td>
                    </tr>
                    <tr>
                        <td><?=$pos_settings->toggle_category_slider?></td>
                        <td><?=lang('toggle_category_slider')?></td>
                    </tr>
                    <tr>
                        <td><?=$pos_settings->toggle_subcategory_slider?></td>
                        <td><?=lang('toggle_subcategory_slider')?></td>
                    </tr>
                    <tr>
                        <td><?=$pos_settings->cancel_sale?></td>
                        <td><?=lang('cancel_sale')?></td>
                    </tr>
                    <tr>
                        <td><?=$pos_settings->suspend_sale?></td>
                        <td><?=lang('suspend_sale')?></td>
                    </tr>
                    <tr>
                        <td><?=$pos_settings->print_items_list?></td>
                        <td><?=lang('print_items_list')?></td>
                    </tr>
                    <tr>
                        <td><?=$pos_settings->finalize_sale?></td>
                        <td><?=lang('finalize_sale')?></td>
                    </tr>
                    <tr>
                        <td><?=$pos_settings->today_sale?></td>
                        <td><?=lang('today_sale')?></td>
                    </tr>
                    <tr>
                        <td><?=$pos_settings->open_hold_bills?></td>
                        <td><?=lang('open_hold_bills')?></td>
                    </tr>
                    <tr>
                        <td><?=$pos_settings->close_register?></td>
                        <td><?=lang('close_register')?></td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade in" id="srModal" tabindex="-1" role="dialog" aria-labelledby="ModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">
                <i class="fa fa-2x">&times;</i></span><span class="sr-only"><?=lang('close');?></span>
                </button>
                
                <h4 class="modal-title" id="mModalLabel"><?=lang('Check Stock')?></h4>
            </div>
            <div class="modal-body" id="pr_popover_content">
                
                            <div class="well well-sm">
                                <div class="form-group" style="margin-bottom:0;">
                                    <div class="input-group wide-tip">
                                        <div class="input-group-addon" style="padding-left: 10px; padding-right: 10px;">
                                            <i class="fa fa-2x fa-barcode addIcon"></i></div>
                                        <input type="text" name="add_item" value="" class="form-control input-lg ui-autocomplete-input" id="add_item_sr" placeholder="Please add products to order list" autocomplete="off">
                                    </div>
                                </div>
                                <div class="clearfix"></div>
                            </div>
                       
               <div id="show_requested_stock"></div>
            </div>
        </div>
    </div>
</div>
<div class="modal fade in" id="dsModal" tabindex="-1" role="dialog" aria-labelledby="dsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                    <i class="fa fa-2x">&times;</i>
                </button>
                <h4 class="modal-title" id="dsModalLabel"><?=lang('edit_order_discount');?></h4>
            </div>
            <div class="modal-body">
                 <?php 
                    $allow_discount_value= $this->session->userdata('allow_discount_value');
                   // $allow_discount_value= 5;   
                 ?>
                 <input type="hidden" id="allow_discount_value" value="<?php echo $allow_discount_value;?>">
                  <center><h3> Max Discount Allowed <?php echo  $allow_discount_value ?>%</h3>
                   <div id="notAllowError"></div>
                 </center>
                <div class="form-group">
                    <?=lang('order_discount', 'order_discount_input');?>
                    <?php echo form_input('order_discount_input', '%', 'class="form-control" onblur="allowDiscountValue()" type="number" id="order_discount_input"'); ?>
                </div>

            </div>
            <div class="modal-footer">
                <button type="button" id="updateOrderDiscount" class="btn btn-primary"><?=lang('update')?></button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade in" id="sModal" tabindex="-1" role="dialog" aria-labelledby="sModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                    <i class="fa fa-2x">&times;</i>
                </button>
                <h4 class="modal-title" id="sModalLabel"><?=lang('shipping');?></h4>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <?=lang('shipping', 'shipping_input');?>
                    <?php echo form_input('shipping_input', '', 'class="form-control kb-pad" id="shipping_input"'); ?>
                </div>

            </div>
            <div class="modal-footer">
                <button type="button" id="updateShipping" class="btn btn-primary"><?=lang('update')?></button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade in" id="txModal" tabindex="-1" role="dialog" aria-labelledby="txModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i
                        class="fa fa-2x">&times;</i></button>
                <h4 class="modal-title" id="txModalLabel"><?=lang('edit_order_tax');?></h4>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <?=lang('order_tax', 'order_tax_input');?>
                    <?php
                    $tr[''] = '';
                    foreach ($tax_rates as $tax) {
                        $tr[$tax->id] = $tax->name;
                    }
                        echo form_dropdown('order_tax_input', $tr, '', 'id="order_tax_input" class="form-control pos-input-tip" style="width:100%;"');
                    ?>
                </div>

            </div>
            <div class="modal-footer">
                <button type="button" id="updateOrderTax" class="btn btn-primary"><?=lang('update')?></button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade in" id="susModal" tabindex="-1" role="dialog" aria-labelledby="susModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i
                        class="fa fa-2x">&times;</i></button>
                <h4 class="modal-title" id="susModalLabel"><?=lang('suspend_sale');?></h4>
            </div>
            <div class="modal-body">
                <p><?=lang('type_reference_note');?></p>

                <div class="form-group">
                    <?=lang('reference_note', 'reference_note');?>
                    <?= form_input('reference_note', (!empty($reference_note) ? $reference_note : ''), 'class="form-control kb-text" id="reference_note"'); ?>
                </div>

            </div>
            <div class="modal-footer">
                <button type="button" id="suspend_sale" class="btn btn-primary"><?=lang('submit')?></button>
            </div>
        </div>
    </div>
</div>
<div id="order_tbl"><span id="order_span"></span>
    <table id="order-table" class="prT table table-striped" style="margin-bottom:0;" width="100%"></table>
</div>
<div id="bill_tbl"><span id="bill_span"></span>
    <table id="bill-table" width="100%" class="prT table table-striped" style="margin-bottom:0;"></table>
    <table id="bill-total-table" class="prT table" style="margin-bottom:0;" width="100%"></table>
    <span id="bill_footer"></span>
</div>
<div class="modal fade in" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
     aria-hidden="true"></div>
<div class="modal fade in" id="myModal2" tabindex="-1" role="dialog" aria-labelledby="myModalLabel2"
     aria-hidden="true"></div>
<div id="modal-loading" style="display: none;">
    <div class="blackbg"></div>
    <div class="loader"></div>
</div>
<?php unset($Settings->setting_id, $Settings->smtp_user, $Settings->smtp_pass, $Settings->smtp_port, $Settings->update, $Settings->reg_ver, $Settings->allow_reg, $Settings->default_email, $Settings->mmode, $Settings->timezone, $Settings->restrict_calendar, $Settings->restrict_user, $Settings->auto_reg, $Settings->reg_notification, $Settings->protocol, $Settings->mailpath, $Settings->smtp_crypto, $Settings->corn, $Settings->customer_group, $Settings->envato_username, $Settings->purchase_code);?>
<script type="text/javascript">
var site = <?=json_encode(['url' => base_url(), 'base_url' => admin_url('/'), 'assets' => $assets, 'settings' => $Settings, 'dateFormats' => $dateFormats])?>, pos_settings = <?=json_encode($pos_settings);?>;
var lang = {
    unexpected_value: '<?=lang('unexpected_value');?>',
    select_above: '<?=lang('select_above');?>',
    r_u_sure: '<?=lang('r_u_sure');?>',
    bill: '<?=lang('bill');?>',
    order: '<?=lang('order');?>',
    total: '<?=lang('total');?>',
    items: '<?=lang('items');?>',
    discount: '<?=lang('discount');?>',
    order_tax: '<?=lang('order_tax');?>',
    grand_total: '<?=lang('grand_total');?>',
    total_payable: '<?=lang('total_payable');?>',
    rounding: '<?=lang('rounding');?>',
    merchant_copy: '<?=lang('merchant_copy');?>'
};
</script>

<script type="text/javascript">
    var pa_no = 1, product_variant = 0, shipping = 0, p_page = 0, per_page = 0, tcp = "<?=$tcp?>", pro_limit = <?= $pos_settings->pro_limit; ?>,
        brand_id = 0, obrand_id = 0, cat_id = "<?=$pos_settings->default_category?>", ocat_id = "<?=$pos_settings->default_category?>", sub_cat_id = 0, osub_cat_id,
        count = 1, an = 1, DT = <?=$Settings->default_tax_rate?>,
        product_tax = 0, invoice_tax = 0, product_discount = 0, order_discount = 0, total_discount = 0, total = 0, total_paid = 0, grand_total = 0,
        KB = <?=$pos_settings->keyboard?>, tax_rates =<?php echo json_encode($tax_rates); ?>;
    var protect_delete = <?= (!$Owner && !$Admin) ? ($pos_settings->pin_code ? '1' : '0') : '0'; ?>, billers = <?= json_encode($posbillers); ?>, biller = <?= json_encode($posbiller); ?>;
    var username = '<?=$this->session->userdata('username');?>', order_data = '', bill_data = '';
    var positems = [];
    function widthFunctions(e) {
        var wh = $(window).height(),
            lth = $('#left-top').height(),
            lbh = $('#left-bottom').height();
        $('#item-list').css("height", wh - 140);
        $('#item-list').css("min-height", 515);
        $('#left-middle').css("height", wh - lth - lbh - 107);
        $('#left-middle').css("min-height", 278);
        $('#product-list').css("height", wh - lth - lbh - 107);
        $('#product-list').css("min-height", 278);
    }
    $(window).bind("resize", widthFunctions);
    $(document).ready(function () {
        $('#view-customer').click(function(){
            $('#myModal').modal({remote: site.base_url + 'customers/view/' + $("input[name=customer]").val()});
            $('#myModal').modal('show');
        });
        $('textarea').keydown(function (e) {
            if (e.which == 13) {
               var s = $(this).val();
               $(this).val(s+'\n').focus();
               e.preventDefault();
               return false;
            }
        });
        <?php if ($sid) {
            ?>
        localStorage.setItem('positems', JSON.stringify(<?=$items; ?>));
            <?php
        } ?>

        <?php if ($oid) {
            ?>
        localStorage.setItem('positems', JSON.stringify(<?=$items; ?>));
            <?php
        } ?>

<?php if ($this->session->userdata('remove_posls')) {
    ?>
        if (localStorage.getItem('positems')) {
            localStorage.removeItem('positems');
        }
        if (localStorage.getItem('posdiscount')) {
            localStorage.removeItem('posdiscount');
        }
        if (localStorage.getItem('postax2')) {
            localStorage.removeItem('postax2');
        }
        if (localStorage.getItem('posshipping')) {
            localStorage.removeItem('posshipping');
        }
        if (localStorage.getItem('poswarehouse')) {
            localStorage.removeItem('poswarehouse');
        }
        if (localStorage.getItem('posnote')) {
            localStorage.removeItem('posnote');
        }
        if (localStorage.getItem('poscustomer')) {
            localStorage.removeItem('poscustomer');
        }
        if (localStorage.getItem('posbiller')) {
            localStorage.removeItem('posbiller');
        }
        if (localStorage.getItem('poscurrency')) {
            localStorage.removeItem('poscurrency');
        }
        if (localStorage.getItem('posnote')) {
            localStorage.removeItem('posnote');
        }
        if (localStorage.getItem('staffnote')) {
            localStorage.removeItem('staffnote');
        }
        <?php $this->sma->unset_data('remove_posls');
}
?>
        widthFunctions();
        <?php if ($suspend_sale) {
            ?>
        localStorage.setItem('postax2', '<?=$suspend_sale->order_tax_id; ?>');
        localStorage.setItem('posdiscount', '<?=$suspend_sale->order_discount_id; ?>');
        localStorage.setItem('poswarehouse', '<?=$suspend_sale->warehouse_id; ?>');
        localStorage.setItem('poscustomer', '<?=$suspend_sale->customer_id; ?>');
        localStorage.setItem('posbiller', '<?=$suspend_sale->biller_id; ?>');
        localStorage.setItem('posshipping', '<?=$suspend_sale->shipping; ?>');
            <?php
        }
        ?>
        <?php if ($old_sale) {
            ?>
        localStorage.setItem('postax2', '<?=$old_sale->order_tax_id; ?>');
        localStorage.setItem('posdiscount', '<?=$old_sale->order_discount_id; ?>');
        localStorage.setItem('poswarehouse', '<?=$old_sale->warehouse_id; ?>');
        localStorage.setItem('poscustomer', '<?=$old_sale->customer_id; ?>');
        localStorage.setItem('posbiller', '<?=$old_sale->biller_id; ?>');
        localStorage.setItem('posshipping', '<?=$old_sale->shipping; ?>');
            <?php
        }
        ?>

        <?php if ($this->input->get('customer')) {
            ?>
            if (!localStorage.getItem('positems')) {
                localStorage.setItem('poscustomer', <?=$this->input->get('customer'); ?>);
            } else if (!localStorage.getItem('poscustomer')) {
                localStorage.setItem('poscustomer', <?=$customer->id; ?>);
            }
            <?php
        } else {
            ?>
            if (!localStorage.getItem('poscustomer')) {
                localStorage.setItem('poscustomer', <?=$customer->id; ?>);
            }
            <?php
        }
        ?>
        if (!localStorage.getItem('postax2')) {
            localStorage.setItem('postax2', <?=$Settings->default_tax_rate2;?>);
        }
        $('.select').select2({minimumResultsForSearch: 7});
        // var customers = [{
        //     id: <?=$customer->id;?>,
        //     text: '<?=$customer->company == '-' ? $customer->name : $customer->company;?>'
        // }];
        $('#poscustomer').val(localStorage.getItem('poscustomer')).select2({
            minimumInputLength: 1,
            data: [],
            initSelection: function (element, callback) {
                $.ajax({
                    type: "get", async: false,
                    url: "<?=admin_url('customers/getCustomer')?>/" + $(element).val(),
                    dataType: "json",
                    success: function (data) {
                        callback(data[0]);
                    }
                });
            },
            ajax: {
                url: site.base_url + "customers/suggestions",
                dataType: 'json',
                quietMillis: 15,
                data: function (term, page) {
                    return {
                        term: term,
                        limit: 10
                    };
                },
                results: function (data, page) {
                    if (data.results != null) {
                        return {results: data.results};
                    } else {
                        return {results: [{id: '', text: 'No Match Found'}]};
                    }
                }
            }
        });
        if (KB) {
            display_keyboards();

            var result = false, sct = '';
            $('#poscustomer').on('select2-opening', function () {
                sct = '';
                $('.select2-input').addClass('kb-text');
                display_keyboards();
                $('.select2-input').bind('change.keyboard', function (e, keyboard, el) {
                    if (el && el.value != '' && el.value.length > 0 && sct != el.value) {
                        sct = el.value;
                    }
                    if(!el && sct.length > 0) {
                        $('.select2-input').addClass('select2-active');
                        setTimeout(function() {
                            $.ajax({
                                type: "get",
                                async: false,
                                url: "<?=admin_url('customers/suggestions')?>/?term=" + sct,
                                dataType: "json",
                                success: function (res) {
                                    if (res.results != null) {
                                        $('#poscustomer').select2({data: res}).select2('open');
                                        $('.select2-input').removeClass('select2-active');
                                    } else {
                                        // bootbox.alert('no_match_found');
                                        $('#poscustomer').select2('close');
                                        $('#test').click();
                                    }
                                }
                            });
                        }, 500);
                    }
                });
            });

            $('#poscustomer').on('select2-close', function () {
                $('.select2-input').removeClass('kb-text');
                $('#test').click();
                $('select, .select').select2('destroy');
                $('select, .select').select2({minimumResultsForSearch: 7});
            });
            $(document).bind('click', '#test', function () {
                var kb = $('#test').keyboard().getkeyboard();
                kb.close();
            });

        }

        $(document).on('change', '#posbiller', function () {
            var sb = $(this).val();
            $.each(billers, function () {
                if(this.id == sb) {
                    biller = this;
                }
            });
            $('#biller').val(sb);
        });

        <?php for ($i = 1; $i <= 5; $i++) {
            ?>
        $('#paymentModal').on('change', '#amount_<?=$i?>', function (e) {
            $('#amount_val_<?=$i?>').val($(this).val());
        });
        $('#paymentModal').on('blur', '#amount_<?=$i?>', function (e) {
            $('#amount_val_<?=$i?>').val($(this).val());
        });
        $('#paymentModal').on('select2-close', '#paid_by_<?=$i?>', function (e) {
            $('#paid_by_val_<?=$i?>').val($(this).val());
        });
        $('#paymentModal').on('change', '#pcc_no_<?=$i?>', function (e) {
            $('#cc_no_val_<?=$i?>').val($(this).val());
        });
        $('#paymentModal').on('change', '#pcc_holder_<?=$i?>', function (e) {
            $('#cc_holder_val_<?=$i?>').val($(this).val());
        });
        $('#paymentModal').on('change', '#gift_card_no_<?=$i?>', function (e) {
            $('#paying_gift_card_no_val_<?=$i?>').val($(this).val());
        });
        $('#paymentModal').on('change', '#pcc_month_<?=$i?>', function (e) {
            $('#cc_month_val_<?=$i?>').val($(this).val());
        });
        $('#paymentModal').on('change', '#pcc_year_<?=$i?>', function (e) {
            $('#cc_year_val_<?=$i?>').val($(this).val());
        });
        $('#paymentModal').on('change', '#pcc_type_<?=$i?>', function (e) {
            $('#cc_type_val_<?=$i?>').val($(this).val());
        });
        $('#paymentModal').on('change', '#pcc_cvv2_<?=$i?>', function (e) {
            $('#cc_cvv2_val_<?=$i?>').val($(this).val());
        });
        $('#paymentModal').on('change', '#cheque_no_<?=$i?>', function (e) {
            $('#cheque_no_val_<?=$i?>').val($(this).val());
        });
        $('#paymentModal').on('change', '#payment_note_<?=$i?>', function (e) {
            $('#payment_note_val_<?=$i?>').val($(this).val());
        });
            <?php
        }
        ?>

        $('#payment').click(function () {
            
            const postotalpayable = localStorage.getItem('postotalpayable') ;
            <?php if ($sid) {
                ?>
            suspend = $('<span></span>');
            suspend.html('<input type="hidden" name="delete_id" value="<?php echo $sid; ?>" />');
            suspend.appendTo("#hidesuspend");
                <?php
            }
            ?>
            var twt = formatDecimal((total + invoice_tax) - order_discount + shipping);
            if (count == 1) {
                bootbox.alert('<?=lang('x_total');?>');
                return false;
            }
            gtotal = postotalpayable;//formatDecimal(twt);
            var cart = {grand_total: gtotal};
            document.dispatchEvent(
                new CustomEvent('rfd.pole.display', {
                    detail: { cart },
                })
            );
            <?php if ($pos_settings->rounding) {
                ?>
            round_total = roundNumber(gtotal, <?=$pos_settings->rounding?>);
            var rounding = formatDecimal(0 - (gtotal - round_total));
            $('#twt').text(formatMoney(round_total) + ' (' + formatMoney(rounding) + ')');
            $('#quick-payable').text(round_total);
                <?php
            } else {
                ?>
            $('#twt').text(formatMoney(gtotal));
            $('#quick-payable').text(gtotal);
                <?php
            }
            ?>

            var instructions_html = '';
            var allItems = JSON.parse(localStorage.getItem('positems'));
            $.each(sortedItems, function () {
                var item = this;
                var item_name = item.row.name;

                instructions_html += '<div class="row"><div class="col-sm-11"><div class="form-group">';
                instructions_html += item_name+' - (<?=lang('Instructions', 'instructions'); ?>)';
                instructions_html += '<input type="hidden" class="medicinename" name="medicinename[]" value="' + item_name + '">';
                instructions_html += '<textarea name="instructions[]" id="instructions" data-medicinename="'+item_name+'" class="pa form-control kb-text instructions"></textarea>';
                instructions_html += '</div></div></div>';
            });

            $('#instructions-area').html(instructions_html);

            $('#item_count').text(count - 1);
            $('#paymentModal').appendTo("body").modal('show');
            $('#amount_1').focus();
        });
        $('#paymentModal').on('show.bs.modal', function(e) {
            $('#submit-sale').text('<?=lang('submit');?>').attr('disabled', false);
        });
        $('#paymentModal').on('shown.bs.modal', function(e) {
            $('#amount_1').focus().val(grand_total);
            $('#amount_1').focus().val(0);
            $('#quick-payable').click();
        });
        var pi = 'amount_'+pa_no, pa = 2;
        $(document).on('click', '.quick-cash', function (event) {
            if ($('#quick-payable').find('span.badge').length) {
                $('#clear-cash-notes').click();
            }
            var cl_id = event.target.id;
            if (cl_id !== 'quick-payable') {
                var $quick_cash = $(this);
                var amt = $quick_cash.contents().filter(function () {
                    return this.nodeType == 3;
                }).text();
                var th = ',';
                var $pi = $('#' + pi);
                amt = formatDecimal(amt.split(th).join("")) * 1 + $pi.val() * 1;
                $pi.val(formatDecimal(amt)).focus();
                var note_count = $quick_cash.find('span');
                if (note_count.length == 0) {
                    $quick_cash.append('<span class="badge">1</span>');
                } else {
                    note_count.text(parseInt(note_count.text()) + 1);
                }
            } else {
                // $('#clear-cash-notes').click();
                $('.quick-cash').find('.badge').remove();
                $(this).append('<span class="badge">1</span>');
            }
        });
        $(document).on('click', '#quick-payable', function () {
            $('#clear-cash-notes').click();
            $(this).append('<span class="badge">1</span>');
            $('#amount_1').val(grand_total);
            $('.close-payment').click();
            $('#amount_1').focus();
            calculateTotals();
        });
        $(document).on('click', '#clear-cash-notes', function () {
            $('.quick-cash').find('.badge').remove();
            $('#' + pi).val('0').focus();
        });

        $(document).on('change', '.gift_card_no', function () {
            var cn = $(this).val() ? $(this).val() : '';
            var payid = $(this).attr('id'),
                id = payid.substr(payid.length - 1);
            if (cn != '') {
                $.ajax({
                    type: "get", async: false,
                    url: site.base_url + "sales/validate_gift_card/" + cn,
                    dataType: "json",
                    success: function (data) {
                        if (data === false) {
                            $('#gift_card_no_' + id).parent('.form-group').addClass('has-error');
                            bootbox.alert('<?=lang('incorrect_gift_card')?>');
                        } else if (data.customer_id !== null && data.customer_id !== $('#poscustomer').val()) {
                            $('#gift_card_no_' + id).parent('.form-group').addClass('has-error');
                            bootbox.alert('<?=lang('gift_card_not_for_customer')?>');
                        } else {
                            $('#gc_details_' + id).html('<small>Card No: ' + data.card_no + '<br>Value: ' + data.value + ' - Balance: ' + data.balance + '</small>');
                            $('#gift_card_no_' + id).parent('.form-group').removeClass('has-error');
                            //calculateTotals();
                            $('#amount_' + id).val(gtotal >= data.balance ? data.balance : gtotal).focus();
                        }
                    }
                });
            }
        });

        $(document).on('click', '.addButton', function () {
            const toTwoDecimals = (value) => new Decimal(value).toDecimalPlaces(5, Decimal.ROUND_DOWN);
            if (pa <= 5) {
                var total_added = 0;
                for (let index = 1; index < 6; index++) {
                    total_added += parseFloat($('#amount_'+index).val() ? $('#amount_'+index).val() : 0);
                }
                
                var bal = toTwoDecimals(grand_total).minus(toTwoDecimals(total_added)) ;
                bal = bal.toNumber();
                
                //var bal = toTwoDecimals( toTwoDecimals(grand_total) - toTwoDecimals(total_added) ).toNumber(); //parseFloat(parseFloat(grand_total) - parseFloat(total_added));
                if (bal > 0) {
                    $('#paid_by_1, #pcc_type_1').select2('destroy');
                    var phtml = $('#payments').html(),
                        update_html = phtml.replace(/_1/g, '_' + pa);
                    pi = 'amount_' + pa;
                    $('#multi-payment').append('<button type="button" class="close close-payment" style="margin: -10px 0px 0 0;"><i class="fa fa-2x">&times;</i></button>' + update_html);
                    $('#paid_by_1, #pcc_type_1, #paid_by_' + pa + ', #pcc_type_' + pa).select2({minimumResultsForSearch: 7});
                    $('#amount_' + pa).val(bal).focus();
                    read_card();
                    pa++;
                    calculateTotals();
                }
                else{
                    bootbox.alert('Added amount already exceeds total payable, please review');
                    $('#amount_' + pa-1).focus();
                }
            } else {
                bootbox.alert('<?=lang('max_reached')?>');
                return false;
            }
            if (KB) { display_keyboards(); }
            $('#paymentModal').css('overflow-y', 'scroll');
        });

        $(document).on('click', '.close-payment', function () {
            $(this).next().remove();
            $(this).remove();
            pa--;
            calculateTotals();
        });

        $(document).on('focus', '.amount', function () {
            pi = $(this).attr('id');
            calculateTotals();
        }).on('blur', '.amount', function () {
            calculateTotals();
        });

        function calculateTotals() {
            var total_paying = 0;
            var ia = $(".amount");
            $.each(ia, function (i) {
                var this_amount = formatCNum($(this).val() ? $(this).val() : 0);
                total_paying += parseFloat(this_amount);
            });
            $('#total_paying').text(formatMoney(total_paying));
            <?php if ($pos_settings->rounding) {
                ?>
            $('#balance').text(formatMoney(total_paying - round_total));
            $('#balance_' + pi).val(formatDecimal(total_paying - round_total));
            total_paid = total_paying;
            grand_total = round_total;
                <?php
            } else {
                ?>
            $('#balance').text(formatMoney(total_paying - gtotal));
            $('#balance_' + pi).val(formatDecimal(total_paying - gtotal));
            total_paid = total_paying;
            grand_total = gtotal;
                <?php
            }
            ?>
        }

        function parseMedicineQRCode(qrCode) {
            const result = {
                GTIN: null,
                SerialNumber: null,
                BatchNumber: null,
                ExpiryDate: null,
                validData: false
            };

            // Extract GTIN (starts with 01 and is 14 digits long)
            const gtinMatch = qrCode.match(/01(\d{14})/);
            let validityCount = 0;
            if (gtinMatch) {
                result.GTIN = gtinMatch[1];
                // Remove GTIN and everything before it from the string
                qrCode = qrCode.substring(gtinMatch.index + gtinMatch[0].length);
                validityCount++;
            }

            // Extract Batch Number (starts with 10 after the GTIN)
            const batchNumberMatch = qrCode.match(/10([a-zA-Z0-9]+?)(?=17|21|$)/);
            if (batchNumberMatch) {
                result.BatchNumber = batchNumberMatch[1];
                validityCount++;
            }

            // Extract Expiry Date (starts with 17 and followed by 6 digits)
            const expiryDateMatch = qrCode.match(/17(\d{6})/);
            if (expiryDateMatch) {
                const expiryRaw = expiryDateMatch[1];
                const year = `20${expiryRaw.substring(0, 2)}`; // Prefix '20' for YY
                const month = expiryRaw.substring(2, 4); // Extract MM
                result.ExpiryDate = `${month} ${year}`; // Format as "MM YYYY"
                validityCount++;
            }

            // Extract Serial Number (starts with 21 and followed by alphanumeric characters)
            const serialNumberMatch = qrCode.match(/21([a-zA-Z0-9]+)/);
            if (serialNumberMatch) {
                result.SerialNumber = serialNumberMatch[1];
                validityCount++;
            }

            if(validityCount == 4){
                result.validData = true;
            }

            return result;
        }

        function extractGs1Data(input) {
            let data = {
                GTIN: null,
                BatchNumber: null,
                SerialNumber: null,
                ExpiryDate: null,
                validData: false
            };

            let validityCount = 0;
            // Extract GTIN (14 digits after (01))
            let gtinMatch = input.match(/\(01\)(\d{14})/);
            if (gtinMatch) {
                data.GTIN = gtinMatch[1];
                validityCount++;
            }

            // Extract Serial Number (variable length after (21), stops at next AI)
            let serialMatch = input.match(/\(21\)([^\(]+)/);
            if (serialMatch) {
                data.SerialNumber = serialMatch[1];
                validityCount++;
            }

            // Extract Batch Number (variable length after (10), stops at next AI)
            let batchMatch = input.match(/\(10\)([^\(]+)/);
            if (batchMatch) {
                data.BatchNumber = batchMatch[1];
                validityCount++;
            }

            // Extract Expiry Date (YYMMDD format after (17))
            let expiryMatch = input.match(/\(17\)(\d{6})/);
            if (expiryMatch) {
                let expiryRaw = expiryMatch[1]; // "270228"

                // Convert YYMMDD to YYYY-MM-DD
                let year = parseInt(expiryRaw.substring(0, 2), 10);
                let month = expiryRaw.substring(2, 4);
                let day = expiryRaw.substring(4, 6);

                // Assume year is in 2000s if below 50, otherwise in 1900s
                year = (year < 50) ? `20${year}` : `19${year}`;

                //data.ExpiryDate = `${day}/${month}/${year}`; // "28/02/2027"
                data.ExpiryDate = `${month} ${year}`;
                validityCount++;
            }

            if(validityCount == 4){
                data.validData = true;
            }

            return data;
        }

        $("#add_item").autocomplete({
            source: function (request, response) {
                if (!$('#poscustomer').val()) {
                    $('#add_item').val('').removeClass('ui-autocomplete-loading');
                    bootbox.alert('<?=lang('select_above');?>');
                    //response('');
                    $('#add_item').focus();
                    return false;
                }

                let parsed = extractGs1Data(request.term);
                if(parsed.validData == false){
                    parsed = parseMedicineQRCode(request.term);
                }

                $.ajax({
                    type: 'get',
                    url: '<?=admin_url('products/get_item_by_gtin_batch_expiry');?>',
                    dataType: "json",
                    data: {
                        gtin: parsed.GTIN,
                        batch: parsed.BatchNumber,
                        expiry: parsed.ExpiryDate,
                        warehouse_id: $("#poswarehouse").val(),
                        customer_id: $("#poscustomer").val(),
                        module: 'pos'
                    },
                    success: function (data) {
                        $(this).removeClass('ui-autocomplete-loading');
                        if(data){
                            var avzItemCode = data[0].row.avz_item_code;
                            var found = false;
                            var foundKey = '';

                            data[0].row.serial_number = parsed.SerialNumber;

                            Object.keys(positems).forEach(function (key) {
                                if (positems[key].row && positems[key].row.avz_item_code === avzItemCode) {
                                    found = true;
                                    foundKey = key;
                                }
                            });

                            if(found == true){

                                var available_qty = parseInt(positems[foundKey].row.quantity);
                                var new_qty = parseInt(positems[foundKey].row.qty) + 1;
                                //console.log(available_qty+' -- '+new_qty);
                                if(parseInt(new_qty) <= parseInt(available_qty)){
                                    positems[foundKey].row.qty = new_qty;

                                    if(positems[foundKey].row.serial_numbers){
                                        positems[foundKey].row.serial_numbers.push(parsed.SerialNumber);
                                    }else{
                                        positems[foundKey].row.serial_numbers = [parsed.SerialNumber];
                                    }

                                    localStorage.setItem('positems', JSON.stringify(positems));
                                    loadItems();
                                }else{
                                    bootbox.alert('No more quantity available.');
                                }
                                
                            }else{
                                
                                add_invoice_item(data[0]);
                            }
                        }else{
                            $.ajax({
                                type: 'get',
                                url: '<?=admin_url('sales/suggestions/1');?>',
                                dataType: "json",
                                data: {
                                    term: request.term,
                                    warehouse_id: $("#poswarehouse").val(),
                                    customer_id: $("#poscustomer").val()
                                },
                                success: function (data) {
                                    if(data[0].id != 0){
                                        $(this).removeClass('ui-autocomplete-loading');
                                        response(data);
                                    }else{
                                        $.ajax({
                                            type: 'get',
                                            url: '<?=admin_url('products/get_items_by_avz_code');?>',
                                            dataType: "json",
                                            data: {
                                                term: request.term,
                                                warehouse_id: $("#poswarehouse").val(),
                                                customer_id: $("#poscustomer").val(),
                                                module: 'pos'
                                            },
                                            success: function (data) {
                                                $(this).removeClass('ui-autocomplete-loading');
                                                if(data){
                                                    var avzItemCode = data[0].row.avz_item_code;
                                                    var found = false;
                                                    var foundKey = '';

                                                    Object.keys(positems).forEach(function (key) {
                                                        if (positems[key].row && positems[key].row.avz_item_code === avzItemCode) {
                                                            found = true;
                                                            foundKey = key;
                                                        }
                                                    });

                                                    if(found == true){

                                                        var available_qty = parseInt(positems[foundKey].row.quantity);
                                                        var new_qty = parseInt(positems[foundKey].row.qty) + 1;
                                                        //console.log(available_qty+' -- '+new_qty);
                                                        if(parseInt(new_qty) <= parseInt(available_qty)){
                                                            positems[foundKey].row.qty = new_qty;
                                                            localStorage.setItem('positems', JSON.stringify(positems));
                                                            loadItems();
                                                        }else{
                                                            bootbox.alert('No more quantity available.');
                                                        }
                                                        
                                                    }else{
                                                        add_invoice_item(data[0]);
                                                    }
                                                }else{
                                                    
                                                    bootbox.alert('No records found for this item code.');
                                                }
                                                
                                            }
                                        });
                                    }
                                    
                                }
                            });
                        }

                        /*if(data[0].id != 0){
                            $(this).removeClass('ui-autocomplete-loading');
                            response(data);
                        }else{
                            $.ajax({
                                type: 'get',
                                url: '<?=admin_url('products/get_items_by_avz_code');?>',
                                dataType: "json",
                                data: {
                                    term: request.term,
                                    warehouse_id: $("#poswarehouse").val(),
                                    customer_id: $("#poscustomer").val(),
                                    module: 'pos'
                                },
                                success: function (data) {
                                    $(this).removeClass('ui-autocomplete-loading');
                                    if(data){
                                        var avzItemCode = data[0].row.avz_item_code;
                                        var found = false;
                                        var foundKey = '';

                                        Object.keys(positems).forEach(function (key) {
                                            if (positems[key].row && positems[key].row.avz_item_code === avzItemCode) {
                                                found = true;
                                                foundKey = key;
                                            }
                                        });

                                        if(found == true){

                                            var available_qty = parseInt(positems[foundKey].row.quantity);
                                            var new_qty = parseInt(positems[foundKey].row.qty) + 1;
                                            //console.log(available_qty+' -- '+new_qty);
                                            if(parseInt(new_qty) <= parseInt(available_qty)){
                                                positems[foundKey].row.qty = new_qty;
                                                localStorage.setItem('positems', JSON.stringify(positems));
                                                loadItems();
                                            }else{
                                                bootbox.alert('No more quantity available.');
                                            }
                                            
                                        }else{
                                            add_invoice_item(data[0]);
                                        }
                                    }else{
                                        
                                        bootbox.alert('No records found for this item code.');
                                    }
                                    
                                }
                            });
                        }*/
                        
                    }
                });
                
            },
            minLength: 1,
            autoFocus: false,
            delay: 250,
            response: function (event, ui) {
                if ($(this).val().length >= 16 && ui.content[0].id == 0) {
                    bootbox.alert('<?=lang('no_match_found')?>', function () {
                        $('#add_item').focus();
                    });
                    $(this).val('');
                }
                else if (ui.content.length == 1 && ui.content[0].id != 0) {
                    ui.item = ui.content[0];
                    $(this).data('ui-autocomplete')._trigger('select', 'autocompleteselect', ui);
                    $(this).autocomplete('close');
                }
                // else if (ui.content.length == 1 && ui.content[0].id == 0) {
                //     bootbox.alert('<?=lang('no_match_found')?>', function () {
                //         $('#add_item').focus();
                //     });
                //     $(this).val('');
                // }
            },
            select: function (event, ui) {
                event.preventDefault();
                if (ui.item.id !== 0) {

                    openPopup(ui.item);
                    $(this).val('');
                   
                } else {
                    bootbox.alert('<?=lang('no_match_found')?>');
                }
            }
        });


        function openPopup(selectedItem) {
            // Assuming selectedItem has avz_item_code as part of its data
            $.ajax({
                type: 'get',
                url: '<?= admin_url('products/get_avz_item_code_details'); ?>', // Adjust the URL as needed
                dataType: "json",
                data: {
                    item_id: selectedItem.item_id, // Send the unique item code
                    warehouse_id: $('#poswarehouse').val() // Optionally include warehouse ID if needed
                },
                success: function (data) {
                    $(this).removeClass('ui-autocomplete-loading');

                    // Populate the modal with the returned data
                    if (data && data.length > 0) {
                        var modalBody = $('#itemModal .modal-body');
                        modalBody.empty();

                        // Loop through each item and create clickable entries in the modal
                        var table = `
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Avz Code</th>
                                        <th>Product</th>
                                        <th>Batch No</th>
                                        <th>Expiry</th>
                                        <th>Quantity</th>
                                        <th>Locked</th>
                                    </tr>
                                </thead>
                                <tbody id="itemTableBody"></tbody>
                            </table>
                        `;

                        // Append the table to the modal body
                        modalBody.append(table);
                        
                        // Populate the table body with the data
                        var count = 0;
                        var toitemsStorageValue = JSON.parse(localStorage.getItem('positems'));
                        data.forEach(function (item) {
                            count++;

                            var avzItemCode = item.row.avz_item_code;
                            var found = false;

                            Object.keys(positems).forEach(function (key) {
                                if (positems[key].row && positems[key].row.avz_item_code === avzItemCode) {
                                    found = true;
                                }
                            });

                            var tickOrCross = found ? '✔' : '✖';

                            var row = `
                                <tr style="cursor:pointer;" class="modal-item" tabindex="0" data-item-id="${item.row.avz_item_code}">
                                    <td>${count}</td>
                                    <td data-avzcode="${item.row.avz_item_code}">${item.row.avz_item_code}</td>
                                    <td data-product="${item.row.name}">${item.row.name}</td>
                                    <td data-batchno="${item.row.batchno}">${item.row.batchno}</td>
                                    <td data-expiry="${item.row.expiry}">${item.row.expiry}</td>
                                    <td data-quantity="${item.total_quantity}">${item.total_quantity}</td>
                                    <td>${tickOrCross}</td>
                                </tr>
                            `;
                            $('#itemTableBody').append(row);
                            $('#itemTableBody tr:last-child').data('available', found);
                        });

                        // Show the modal
                        $('#itemModal').modal('show');
                        /*$('#itemTableBody').on('click', 'tr', function () {
                            
                            var clickedItemCode = $(this).data('item-id');
                            var selectedItem = data.find(function (item) {
                                return item.row.avz_item_code === clickedItemCode;
                            });

                            if (selectedItem) {
                                $('#itemModal').modal('hide');
                                var available = $(this).data('available');
                                if(!available){
                                    add_invoice_item(selectedItem);
                                }else{
                                    bootbox.alert('Row already added');
                                }
                            }else{
                                console.log('Item not found');
                            }
                        });*/

                        $('#itemTableBody').on('click touchstart', 'tr', function (e) {
                            // Prevent the default action for touch events to avoid double triggers
                            e.preventDefault();

                            var clickedItemCode = $(this).data('item-id');
                            var clickedItemExpiry = $(this).find('td[data-expiry]').data('expiry') || $(this).attr('data-expiry');
                            var selectedItem = data.find(function (item) {
                                //return item.row.avz_item_code === clickedItemCode;
                                return String(item.row.avz_item_code).trim() === String(clickedItemCode).trim();
                            });

                            var previousExpiryAvailable = data.find(function (item) {
                            
                                var itemExpiry = new Date(item.row.expiry); // Convert to Date object
                                var clickedExpiry = new Date(clickedItemExpiry); // Convert clickedItemExpiry to Date object
                                if(clickedExpiry > itemExpiry){
                                    return true;
                                }
                            });

                            if(previousExpiryAvailable){
                                bootbox.alert('Previous Expiry available for this item');
                            }

                            if (selectedItem) {
                                $('#itemModal').modal('hide');
                                var available = $(this).data('available');
                                if (!available) {
                                    add_invoice_item(selectedItem);
                                } else {
                                    bootbox.alert('Row already added');
                                }
                            } else {
                                console.log('Item not found');
                            }
                        });
                        
                    } else {

                        //var row = add_invoice_item(selectedItem);
                        console.log(selectedItem);
                        var wh = $("#poswarehouse").val();
                        $.ajax({
                            type: "get",
                            url: "<?=admin_url('pos/getProductData');?>",
                            data: {product_id: selectedItem.item_id, warehouse_id: wh},
                            dataType: "json",
                            success: function (data) {
                                if (data) {
                                    //data.free = true;
                                    //data.parent = selectedItem.item_id;
                                    //console.log(data);
                                    add_invoice_item(data);
                                }
                                $("#add_item").removeClass('ui-autocomplete-loading');
                            }
                        }).done(function () {
                            $('#modal-loading').hide();
                        });
                        /*if (row)
                            $(this).val('');

                            bootbox.alert('No records found for this item code.');*/
                    }
                },
                error: function (xhr, status, error) {
                    console.error('AJAX error:', error);
                    bootbox.alert('An error occurred while fetching the item details.');
                }
            });
        }

        function onSelectFromPopup(selectedRecord) {
            $('#itemModal').modal('hide');

            var row = add_invoice_item(selectedRecord);
            if (row) {
                // If the row was successfully added, you can do additional actions here
                
            }
        }

        <?php if ($pos_settings->tooltips) {
                echo '$(".pos-tip").tooltip();';
        }
        ?>
        // $('#posTable').stickyTableHeaders({fixedOffset: $('#product-list')});
        $('#posTable').stickyTableHeaders({scrollableArea: $('#product-list')});
        $('#product-list, #category-list, #subcategory-list, #brands-list').perfectScrollbar({suppressScrollX: true});
        $('select, .select').select2({minimumResultsForSearch: 7});

        $(document).on('click', '.product', function (e) {
            $('#modal-loading').show();
            code = $(this).val(),
                wh = $('#poswarehouse').val(),
                cu = $('#poscustomer').val();
            $.ajax({
                type: "get",
                url: "<?=admin_url('pos/getProductDataByCode')?>",
                data: {code: code, warehouse_id: wh, customer_id: cu},
                dataType: "json",
                success: function (data) {
                    e.preventDefault();
                    if (data !== null) {
                        add_invoice_item(data);
                        var id = data.row.id;
                        $.ajax({
                            type: "get",
                            url: "<?=admin_url('pos/getProductPromo');?>",
                            data: {product_id: id, warehouse_id: wh},
                            dataType: "json",
                            success: function (data) {
                                if (data) {
                                    data.free = true;
                                    data.parent = id;
                                    add_invoice_item(data);
                                }
                            }
                        }).done(function () {
                            $('#modal-loading').hide();
                        });
                        // $('#modal-loading').hide();
                    } else {
                        bootbox.alert('<?=lang('no_match_found')?>');
                        $('#modal-loading').hide();
                    }
                }
            });
        });

        $(document).on('click', '.category', function () {
            if (cat_id != $(this).val()) {
                $('#open-category').click();
                $('#modal-loading').show();
                cat_id = $(this).val();
                $.ajax({
                    type: "get",
                    url: "<?=admin_url('pos/ajaxcategorydata');?>",
                    data: {category_id: cat_id},
                    dataType: "json",
                    success: function (data) {
                        $('#item-list').empty();
                        var newPrs = $('<div></div>');
                        newPrs.html(data.products);
                        newPrs.appendTo("#item-list");
                        $('#subcategory-list').empty();
                        var newScs = $('<div></div>');
                        newScs.html(data.subcategories);
                        newScs.appendTo("#subcategory-list");
                        tcp = data.tcp;
                        nav_pointer();
                    }
                }).done(function () {
                    p_page = 'n';
                    $('#category-' + cat_id).addClass('active');
                    $('#category-' + ocat_id).removeClass('active');
                    ocat_id = cat_id;
                    $('#modal-loading').hide();
                    nav_pointer();
                });
            }
        });
        $('#category-' + cat_id).addClass('active');

        $(document).on('click', '.brand', function () {
            if (brand_id != $(this).val()) {
                $('#open-brands').click();
                $('#modal-loading').show();
                brand_id = $(this).val();
                $.ajax({
                    type: "get",
                    url: "<?=admin_url('pos/ajaxbranddata');?>",
                    data: {brand_id: brand_id},
                    dataType: "json",
                    success: function (data) {
                        $('#item-list').empty();
                        var newPrs = $('<div></div>');
                        newPrs.html(data.products);
                        newPrs.appendTo("#item-list");
                        tcp = data.tcp;
                        nav_pointer();
                    }
                }).done(function () {
                    p_page = 'n';
                    $('#brand-' + brand_id).addClass('active');
                    $('#brand-' + obrand_id).removeClass('active');
                    obrand_id = brand_id;
                    $('#category-' + cat_id).removeClass('active');
                    $('#subcategory-' + sub_cat_id).removeClass('active');
                    cat_id = 0; sub_cat_id = 0;
                    $('#modal-loading').hide();
                    nav_pointer();
                });
            }
        });

        $(document).on('click', '.subcategory', function () {
            if (sub_cat_id != $(this).val()) {
                $('#open-subcategory').click();
                $('#modal-loading').show();
                sub_cat_id = $(this).val();
                $.ajax({
                    type: "get",
                    url: "<?=admin_url('pos/ajaxproducts');?>",
                    data: {category_id: cat_id, subcategory_id: sub_cat_id, per_page: p_page != 0 ? p_page : 'n' },
                    dataType: "html",
                    success: function (data) {
                        $('#item-list').empty();
                        var newPrs = $('<div></div>');
                        newPrs.html(data);
                        newPrs.appendTo("#item-list");
                    }
                }).done(function () {
                    p_page = 'n';
                    $('#subcategory-' + sub_cat_id).addClass('active');
                    $('#subcategory-' + osub_cat_id).removeClass('active');
                    $('#modal-loading').hide();
                });
            }
        });

        $('#next').click(function () {
            if (p_page == 'n') {
                p_page = 0
            }
            p_page = p_page + pro_limit;
            if (tcp >= pro_limit && p_page < tcp) {
                $('#modal-loading').show();
                $.ajax({
                    type: "get",
                    url: "<?=admin_url('pos/ajaxproducts');?>",
                    data: {category_id: cat_id, subcategory_id: sub_cat_id, per_page: p_page != 0 ? p_page : 'n'},
                    dataType: "html",
                    success: function (data) {
                        $('#item-list').empty();
                        var newPrs = $('<div></div>');
                        newPrs.html(data);
                        newPrs.appendTo("#item-list");
                        nav_pointer();
                    }
                }).done(function () {
                    $('#modal-loading').hide();
                });
            } else {
                p_page = p_page - pro_limit;
            }
        });

        $('#previous').click(function () {
            if (p_page == 'n') {
                p_page = 0;
            }
            if (p_page != 0) {
                $('#modal-loading').show();
                p_page = p_page - pro_limit;
                if (p_page == 0) {
                    p_page = 'n'
                }
                $.ajax({
                    type: "get",
                    url: "<?=admin_url('pos/ajaxproducts');?>",
                    data: {category_id: cat_id, subcategory_id: sub_cat_id, per_page: p_page != 0 ? p_page : 'n'},
                    dataType: "html",
                    success: function (data) {
                        $('#item-list').empty();
                        var newPrs = $('<div></div>');
                        newPrs.html(data);
                        newPrs.appendTo("#item-list");
                        nav_pointer();
                    }

                }).done(function () {
                    $('#modal-loading').hide();
                });
            }
        });

        $(document).on('change', '.paid_by', function () {
            // $('#clear-cash-notes').click();
            // $('#amount_1').val(grand_total);
            var p_val = $(this).val(),
                id = $(this).attr('id');
            pa_no = id.substr(id.length - 1);
            var total_added = 0;
            for (let index = 1; index < 6; index++) {
                if (pa_no != index) {
                    total_added += parseFloat($('#amount_'+index).val() ? $('#amount_'+index).val() : 0);
                }
            }
            var bal = parseFloat(parseFloat(grand_total) - parseFloat(total_added));
            if ($('#amount_' + pa_no).val() == '' || $('#amount_' + pa_no).val() == 0) {
                if (bal > 0) {
                    $('#amount_' + pa_no).val(bal);
                }
                else{
                    $('#amount_' + pa_no).val(0);
                    bootbox.alert('Added amount already exceeds total payable, please review');
                    $('#amount_' + pa_no).focus();
                }
            }
            calculateTotals();

            $('#rpaidby').val(p_val);
            if (p_val == 'cash' || p_val == 'other') {
                $('.pcheque_' + pa_no).hide();
                $('.pcc_' + pa_no).hide();
                $('.pcash_' + pa_no).show();
                $('#amount_' + pa_no).focus();
            } else if (p_val == 'CC' || p_val == 'stripe' || p_val == 'ppp' || p_val == 'authorize') {
                $('.pcheque_' + pa_no).hide();
                $('.pcash_' + pa_no).hide();
                $('.pcc_' + pa_no).show();
                $('#swipe_' + pa_no).focus();
            } else if (p_val == 'Cheque') {
                $('.pcc_' + pa_no).hide();
                $('.pcash_' + pa_no).hide();
                $('.pcheque_' + pa_no).show();
                $('#cheque_no_' + pa_no).focus();
            } else {
                $('.pcheque_' + pa_no).hide();
                $('.pcc_' + pa_no).hide();
                $('.pcash_' + pa_no).hide();
            }
            if (p_val == 'gift_card') {
                $('.gc_' + pa_no).show();
                $('.ngc_' + pa_no).hide();
                $('#gift_card_no_' + pa_no).focus();
            } else {
                $('.ngc_' + pa_no).show();
                $('.gc_' + pa_no).hide();
                $('#gc_details_' + pa_no).html('');
            }
        });

        $(document).on('click', '#submit-sale', function () {
            if (total_paid == 0 || total_paid < grand_total) {
                /*bootbox.confirm("<?=lang('paid_l_t_payable');?>", function (res) {
                    if (res == true) {
                        $('#pos_note').val(localStorage.getItem('posnote'));
                        $('#staff_note').val(localStorage.getItem('staffnote'));
                        $('#submit-sale').text('<?=lang('loading');?>').attr('disabled', true);
                        $('#pos-sale-form').submit();
                    }
                });*/
                bootbox.alert("<?=lang('Paid amount is less than the payable amount. Please press OK to fix the sale.');?>");
                return false;
            } else {
                $('#pos_note').val(localStorage.getItem('posnote'));
                $('#staff_note').val(localStorage.getItem('staffnote'));
                var customer_name = $('#customer_name').val();
                var mobile_number = $('#mobile_number').val();

                var instructionsArr = document.getElementsByClassName('instructions');
                var medicineArr = document.getElementsByClassName('medicinename');
                $('#pos-sale-form').append('<input type="hidden" name="customer_name" value="' + customer_name + '">');
                $('#pos-sale-form').append('<input type="hidden" name="mobile_number" value="' + mobile_number + '">');
                $('#pos-sale-form').append(instructionsArr);
                $('#pos-sale-form').append(medicineArr);

                $(this).text('<?=lang('loading');?>').attr('disabled', true);
                $('#pos-sale-form').submit();
            }
        });
        $('#suspend').click(function () {
            if (count <= 1) {
                bootbox.alert('<?=lang('x_suspend');?>');
                return false;
            } else {
                $('#susModal').modal();
            }
        });
        $('#suspend_sale').click(function () {
            ref = $('#reference_note').val();
            if (!ref || ref == '') {
                bootbox.alert('<?=lang('type_reference_note');?>');
                return false;
            } else {
                suspend = $('<span></span>');
                <?php if ($sid) {
                    ?>
                suspend.html('<input type="hidden" name="delete_id" value="<?php echo $sid; ?>" /><input type="hidden" name="suspend" value="yes" /><input type="hidden" name="suspend_note" value="' + ref + '" />');
                    <?php
                } else {
                    ?>
                suspend.html('<input type="hidden" name="suspend" value="yes" /><input type="hidden" name="suspend_note" value="' + ref + '" />');
                    <?php
                }
                ?>
                suspend.appendTo("#hidesuspend");
                $('#total_items').val(count - 1);
                $('#pos-sale-form').submit();

            }
        });
    });

    $(document).ready(function () {
        $('#print_order').click(function () {
            if (count == 1) {
                bootbox.alert('<?=lang('x_total');?>');
                return false;
            }
            <?php if ($pos_settings->remote_printing != 1) {
                ?>
                printOrder();
                <?php
            } else {
                ?>
                Popup($('#order_tbl').html());
                <?php
            } ?>
        });
        $('#print_bill').click(function () {
            if (count == 1) {
                bootbox.alert('<?=lang('x_total');?>');
                return false;
            }
            <?php if ($pos_settings->remote_printing != 1) {
                ?>
                printBill();
                <?php
            } else {
                ?>
                Popup($('#bill_tbl').html());
                <?php
            } ?>
        });
    });

    $(function () {
        $(".alert").effect("shake");
        setTimeout(function () {
            $(".alert").hide('blind', {}, 500)
        }, 15000);
        <?php if ($pos_settings->display_time) {
            ?>
        var now = new moment();
        $('#display_time').text(now.format((site.dateFormats.js_sdate).toUpperCase() + " HH:mm"));
        setInterval(function () {
            var now = new moment();
            $('#display_time').text(now.format((site.dateFormats.js_sdate).toUpperCase() + " HH:mm"));
        }, 1000);
            <?php
        }
        ?>
    });
    <?php if ($pos_settings->remote_printing == 1) {
        ?>
    function Popup(data) {
        var mywindow = window.open('', 'sma_pos_print', 'height=500,width=300');
        mywindow.document.write('<html><head><title>Print</title>');
        mywindow.document.write('<link rel="stylesheet" href="<?=$assets?>styles/helpers/bootstrap.min.css" type="text/css" />');
        mywindow.document.write('</head><body >');
        mywindow.document.write(data);
        mywindow.document.write('</body></html>');
        mywindow.print();
        mywindow.close();
        return true;
    }
        <?php
    }
    ?>
</script>
<?php
    $s2_lang_file = read_file('./assets/config_dumps/s2_lang.js');
foreach (lang('select2_lang') as $s2_key => $s2_line) {
    $s2_data[$s2_key] = str_replace(['{', '}'], ['"+', '+"'], $s2_line);
}
    $s2_file_date = $this->parser->parse_string($s2_lang_file, $s2_data, true);
?>
<script type="text/javascript" src="<?=$assets?>js/bootstrap.min.js"></script>
<script type="text/javascript" src="<?=$assets?>js/jquery-ui.min.js"></script>
<script type="text/javascript" src="<?=$assets?>js/perfect-scrollbar.min.js"></script>
<script type="text/javascript" src="<?=$assets?>js/select2.min.js"></script>
<script type="text/javascript" src="<?=$assets?>js/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="<?=$assets?>js/custom.js"></script>
<script type="text/javascript" src="<?=$assets?>js/jquery.calculator.min.js"></script>
<script type="text/javascript" src="<?=$assets?>js/bootstrapValidator.min.js"></script>
<script type="text/javascript" src="<?=$assets?>pos/js/plugins.min.js"></script>
<script type="text/javascript" src="<?=$assets?>pos/js/parse-track-data.js"></script>
<script type="text/javascript" src="<?=$assets?>pos/js/pos.ajax.js"></script>
<script type="text/javascript" src="<?=$assets?>pos/js/pole.js"></script>
<?php
if (!$pos_settings->remote_printing) {
    ?>
    <script type="text/javascript">
        var order_printers = <?= json_encode($order_printers); ?>;
        function printOrder() {
            $.each(order_printers, function() {
                var socket_data = { 'printer': this,
                'logo': (biller && biller.logo ? biller.logo : ''),
                'text': order_data };
                $.get('<?= admin_url('pos/p/order'); ?>', {data: JSON.stringify(socket_data)});
            });
            return false;
        }

        function printBill() {
            var socket_data = {
                'printer': <?= json_encode($printer); ?>,
                'logo': (biller && biller.logo ? biller.logo : ''),
                'text': bill_data
            };
            $.get('<?= admin_url('pos/p'); ?>', {data: JSON.stringify(socket_data)});
            return false;
        }
    </script>
    <?php
} elseif ($pos_settings->remote_printing == 2) {
    ?>
    <script src="<?= $assets ?>js/socket.io.min.js" type="text/javascript"></script>
    <script type="text/javascript">
        socket = io.connect('http://localhost:6440', {'reconnection': false});

        function printBill() {
            if (socket.connected) {
                var socket_data = {'printer': <?= json_encode($printer); ?>, 'text': bill_data};
                socket.emit('print-now', socket_data);
                return false;
            } else {
                bootbox.alert('<?= lang('pos_print_error'); ?>');
                return false;
            }
        }

        var order_printers = <?= json_encode($order_printers); ?>;
        function printOrder() {
            if (socket.connected) {
                $.each(order_printers, function() {
                    var socket_data = {'printer': this, 'text': order_data};
                    socket.emit('print-now', socket_data);
                });
                return false;
            } else {
                bootbox.alert('<?= lang('pos_print_error'); ?>');
                return false;
            }
        }
    </script>
    <?php
} elseif ($pos_settings->remote_printing == 3) {
    ?>
    <script type="text/javascript">
        try {
            socket = new WebSocket('ws://127.0.0.1:6441');
            socket.onopen = function () {
                console.log('Connected');
                return;
            };
            socket.onclose = function () {
                console.log('Not Connected');
                return;
            };
        } catch (e) {
            console.log(e);
        }

        var order_printers = <?= $pos_settings->local_printers ? "''" : json_encode($order_printers); ?>;
        function printOrder() {
            if (socket.readyState == 1) {

                if (order_printers == '') {

                    var socket_data = { 'printer': false, 'order': true,
                    'logo': (biller && biller.logo ? site.url+'assets/uploads/logos/'+biller.logo : ''),
                    'text': order_data };
                    socket.send(JSON.stringify({type: 'print-receipt', data: socket_data}));

                } else {

                $.each(order_printers, function() {
                    var socket_data = { 'printer': this,
                    'logo': (biller && biller.logo ? site.url+'assets/uploads/logos/'+biller.logo : ''),
                    'text': order_data };
                    socket.send(JSON.stringify({type: 'print-receipt', data: socket_data}));
                });

            }
                return false;
            } else {
                bootbox.alert('<?= lang('pos_print_error'); ?>');
                return false;
            }
        }

        function printBill() {
            if (socket.readyState == 1) {
                var socket_data = {
                    'printer': <?= $pos_settings->local_printers ? "''" : json_encode($printer); ?>,
                    'logo': (biller && biller.logo ? site.url+'assets/uploads/logos/'+biller.logo : ''),
                    'text': bill_data
                };
                socket.send(JSON.stringify({type: 'print-receipt', data: socket_data}));
                return false;
            } else {
                bootbox.alert('<?= lang('pos_print_error'); ?>');
                return false;
            }
        }
    </script>
    <?php
}
?>
<script type="text/javascript">
$('.sortable_table tbody').sortable({
    containerSelector: 'tr'
});

/*-----------------------------

Check Stock on Different warehouses

---------------------------------*/


$("#add_item_sr").autocomplete({
            //source: '<?= admin_url('transfers/suggestions'); ?>',
            source: function (request, response) {
                
                $.ajax({
                    type: 'get',
                    url: '<?= admin_url('stock_request/suggestions'); ?>',
                    dataType: "json",
                    data: {
                        term: request.term
                    },
                    success: function (data) {
                        $(this).removeClass('ui-autocomplete-loading');
                        response(data);
                    }
                });
            },
            minLength: 1,
            autoFocus: false,
            delay: 250,
            response: function (event, ui) {
                if ( ui.content[0].id != 0) {
                 console.log(ui.content[0].id);
                 $.ajax({
                     type: 'get',
                      url: '<?= admin_url('stock_request/checkstock'); ?>',
                    dataType: "html",
                    data: {
                        term: ui.content[0].id
                    },
                    success: function (data) {
                        $(this).removeClass('ui-autocomplete-loading');
                        $("#show_requested_stock").html(data);
                    }
                 });
                }else if(ui.content[0].id == 0){
                    
                    $("#show_requested_stock").html(ui.content[0].label);
                }
            },
            select: function (event, ui) {
                event.preventDefault();
                 console.log(ui.item);
                if (ui.item.id !== 0) {
                    //console.log(ui.item);
                    $.ajax({
                     type: 'get',
                      url: '<?= admin_url('stock_request/checkstock'); ?>',
                    dataType: "html",
                    data: {
                        term: ui.item.id
                    },
                    success: function (data) {
                        $(this).removeClass('ui-autocomplete-loading');
                        $("#show_requested_stock").html(data);
                    }
                 });
                    
                    
                    
                } else {
                    //audio_error.play();
                    bootbox.alert('<?= lang('no_match_found') ?>');
                }
            }
            
});

/*-------------------------------

Warehouse request form code

---------------------------------*/

var wh_count = 1, wh_an = 1, wh_total = 0, toitems = {};

  $("#add_item_wh").autocomplete({
            //source: '<?= admin_url('transfers/suggestions'); ?>',
            source: function (request, response) {
                if (!$('#from_warehouse').val()) {
                    $('#add_item_wh').val('').removeClass('ui-autocomplete-loading');
                    bootbox.alert('<?=lang('select_above');?>');
                    $('#add_item_wh').focus();
                    return false;
                }
                $.ajax({
                    type: 'get',
                    url: '<?= admin_url('transfers/wh_suggestions'); ?>',
                    dataType: "json",
                    data: {
                        term: request.term,
                        warehouse_id: $("#from_warehouse").val()
                    },
                    success: function (data) {
                        $(this).removeClass('ui-autocomplete-loading');
                        response(data);
                    }
                });
            },
            minLength: 1,
            autoFocus: false,
            delay: 250,
            response: function (event, ui) {
                if ($(this).val().length >= 16 && ui.content[0].id == 0) {
                    //audio_error.play();
                    if ($('#from_warehouse').val()) {
                        bootbox.alert('<?= lang('no_match_found') ?>', function () {
                            $('#add_item_wh').focus();
                        });
                    } else {
                        bootbox.alert('<?= lang('please_select_warehouse') ?>', function () {
                            $('#add_item_wh').focus();
                        });
                    }
                    $(this).removeClass('ui-autocomplete-loading');
                    $(this).val('');
                }
                else if (ui.content.length == 1 && ui.content[0].id != 0) {
                    ui.item = ui.content[0];
                    $(this).data('ui-autocomplete')._trigger('select', 'autocompleteselect', ui);
                    $(this).autocomplete('close');
                    $(this).removeClass('ui-autocomplete-loading');
                }
                else if (ui.content.length == 1 && ui.content[0].id == 0) {
                    //audio_error.play();
                    bootbox.alert('<?= lang('no_match_found') ?>', function () {
                        $('#add_item_wh').focus();
                    });
                    $(this).removeClass('ui-autocomplete-loading');
                    $(this).val('');

                }
            },
            select: function (event, ui) {
                event.preventDefault();
                if (ui.item.id !== 0) {
                    var row = add_invoice_item(ui.item);
                    if (row)
                        $(this).val('');
                } else {
                    //audio_error.play();
                    bootbox.alert('<?= lang('no_match_found') ?>');
                }
            }
        });
 /* ----------------------
     * Delete Row Method
     * ---------------------- */

    $(document).on('click', '.todel', function () {
        var row = $(this).closest('tr');
        var item_id = row.attr('data-item-id');
        delete toitems[item_id];
        row.remove();
        if (toitems.hasOwnProperty(item_id)) {
        } else {
            localStorage.setItem('toitems', JSON.stringify(toitems));
            wh_loadItems();
            return;
        }
    });
    
/* --------------------------
     * Edit Row Quantity Method
     --------------------------*/
    var wh_old_row_qty;
    $(document)
        .on('focus', '.wh_rquantity', function () {
            wh_old_row_qty = $(this).val();
        })
        .on('change', '.wh_rquantity', function () {
            var row = $(this).closest('tr');
            if (!is_numeric($(this).val()) || parseFloat($(this).val()) < 0) {
                $(this).val(wh_old_row_qty);
                bootbox.alert(lang.unexpected_value);
                return;
            }
            var new_qty = parseFloat($(this).val()),
                item_id = row.attr('data-item-id');
            toitems[item_id].row.base_quantity = new_qty;
            if (toitems[item_id].row.unit != toitems[item_id].row.base_unit) {
                $.each(toitems[item_id].units, function () {
                    if (this.id == toitems[item_id].row.unit) {
                        toitems[item_id].row.base_quantity = unitToBaseQty(new_qty, this);
                    }
                });
            }
            toitems[item_id].row.qty = new_qty;
            localStorage.setItem('toitems', JSON.stringify(toitems));
            wh_loadItems(); // wh_loadItems
        });

         
/* -----------------------------
 * Add Purchase Iten Function
 * @param {json} item
 * @returns {Boolean}
 ---------------------------- */
 function wh_loadItems() {
    if (localStorage.getItem('toitems')) {
        total = 0;
        wh_count = 1;
        wh_an = 1;
        product_tax = 0;
        $('#toTable tbody').empty();
        $('#add_transfer, #edit_transfer').attr('disabled', false);
        toitems = JSON.parse(localStorage.getItem('toitems'));
        sortedItems =
            site.settings.item_addition == 1
                ? _.sortBy(toitems, function (o) {
                      return [parseInt(o.order)];
                  })
                : toitems;

        var order_no = new Date().getTime();
        $.each(sortedItems, function () {
            var wh_item = this;
            var item_id = site.settings.item_addition == 1 ? wh_item.item_id : wh_item.id;
            wh_item.order = wh_item.order ? wh_item.order : order_no++;
            var from_warehouse = localStorage.getItem('from_warehouse'),
                check = false;
            var product_id = wh_item.row.id,
                item_type = wh_item.row.type,
                item_cost = wh_item.row.cost,
                item_sale_price = wh_item.row.price,
                item_qty = wh_item.row.qty,
                item_bqty = wh_item.row.quantity_balance,
                item_oqty = wh_item.row.ordered_quantity,
                item_expiry = wh_item.row.expiry,
                item_aqty = wh_item.row.quantity,
                item_tax_method = wh_item.row.tax_method,
                item_ds = wh_item.row.discount,
                item_discount = 0,
                item_option = wh_item.row.option,
                item_code = wh_item.row.code,
                item_serial = wh_item.row.serial,
                item_name = wh_item.row.name.replace(/"/g, '&#034;').replace(/'/g, '&#039;');

            var unit_cost = wh_item.row.real_unit_cost;
            var product_unit = wh_item.row.unit,
                base_quantity = wh_item.row.base_quantity;

            var pr_tax = wh_item.tax_rate;
            var pr_tax_val = 0,
                pr_tax_rate = 0;
            if (site.settings.tax1 == 1) {
                if (pr_tax !== false) {
                    if (pr_tax.type == 1) {
                        if (item_tax_method == '0') {
                            pr_tax_val = formatDecimal((unit_cost * parseFloat(pr_tax.rate)) / (100 + parseFloat(pr_tax.rate)), 4);
                            pr_tax_rate = formatDecimal(pr_tax.rate) + '%';
                        } else {
                            pr_tax_val = formatDecimal((unit_cost * parseFloat(pr_tax.rate)) / 100, 4);
                            pr_tax_rate = formatDecimal(pr_tax.rate) + '%';
                        }
                    } else if (pr_tax.type == 2) {
                        pr_tax_val = parseFloat(pr_tax.rate);
                        pr_tax_rate = pr_tax.rate;
                    }
                    product_tax += pr_tax_val * item_qty;
                    product_tax += formatDecimal(formatDecimal(pr_tax_val) * item_qty);
                }
            }
            item_cost = item_tax_method == 0 ? formatDecimal(unit_cost - pr_tax_val, 4) : formatDecimal(unit_cost);
            unit_cost = formatDecimal(unit_cost + item_discount, 4);
            var sel_opt = '';
            $.each(wh_item.options, function () {
                if (this.id == item_option) {
                    sel_opt = this.name;
                }
            });

            var row_no = wh_item.id;
            var newTr = $('<tr id="row_' + row_no + '" class="row_' + item_id + '" data-item-id="' + item_id + '"></tr>');
            tr_html =
                '<td><input name="product_id[]" type="hidden" class="rid" value="' +
                product_id +
                '"><input name="product_type[]" type="hidden" class="rtype" value="' +
                item_type +
                '"><input name="product_code[]" type="hidden" class="rcode" value="' +
                item_code +
                '"><input name="product_name[]" type="hidden" class="rname" value="' +
                item_name +
                '"><input name="product_option[]" type="hidden" class="roption" value="' +
                item_option +
                '"><span class="sname" id="name_' +
                row_no +
                '">' +
                item_code +
                ' - ' +
                item_name +
                (sel_opt != '' ? ' (' + sel_opt + ')' : '') +
                '</span> <i class="pull-right fa fa-edit tip tointer edit" id="' +
                row_no +
                '" data-item="' +
                item_id +
                '" title="Edit" style="cursor:pointer;"></i></td>';
            /*if (site.settings.product_expiry == 1) {
                tr_html +=
                    '<td><input class="form-control date rexpiry" name="expiry[]" type="text" value="' +
                    item_expiry +
                    '" data-id="' +
                    row_no +
                    '" data-item="' +
                    item_id +
                    '" id="expiry_' +
                    row_no +
                    '"></td>';
            }*/
            /*tr_html +=
                '<input class="form-control input-sm text-right rcost" name="net_cost[]" type="hidden" id="cost_' +
                row_no +
                '" value="' +
                formatDecimal(item_cost) +
                '"><input class="rucost" name="unit_cost[]" type="hidden" value="' +
                unit_cost +
                '"><input class="realucost" name="real_unit_cost[]" type="hidden" value="' +
                wh_item.row.real_unit_cost +
                '">';*/
            tr_html +=
                '<td class="text-right"><input class="form-control input-sm text-right rcost" name="net_cost[]" type="hidden" id="cost_' +
                row_no +
                '" value="' +
                formatDecimal(parseFloat(item_sale_price) * parseFloat(item_qty)) +
                '"><input class="rucost" name="unit_cost[]" type="hidden" value="' +
                item_sale_price +
                '"><input class="realucost" name="real_unit_cost[]" type="hidden" value="' +
                item_sale_price +
                '"><span class="text-right rsale_price" id="sale_' +
                row_no +
                '">' +
                formatMoney(item_sale_price) +
                '</span></td>';
            tr_html +=
                '<td><input name="quantity_balance[]" type="hidden" class="rbqty" value="' +
                formatDecimal(item_bqty, 4) +
                '"><input name="ordered_quantity[]" type="hidden" class="roqty" value="' +
                formatDecimal(item_oqty, 4) +
                '"><input class="form-control text-center wh_rquantity" tabindex="' +
                (site.settings.set_focus == 1 ? an : an + 1) +
                '" name="quantity[]" type="text" value="' +
                formatQuantity2(item_qty) +
                '" data-id="' +
                row_no +
                '" data-item="' +
                item_id +
                '" id="quantity_' +
                row_no +
                '" onClick="this.select();"><input name="product_unit[]" type="hidden" class="runit" value="' +
                product_unit +
                '"><input name="product_base_quantity[]" type="hidden" class="rbase_quantity" value="' +
                base_quantity +
                '"></td>';
           /* if (site.settings.tax1 == 1) {
                tr_html +=
                    '<td class="text-right"><input class="form-control input-sm text-right rproduct_tax" name="product_tax[]" type="hidden" id="product_tax_' +
                    row_no +
                    '" value="' +
                    pr_tax.id +
                    '"><span class="text-right sproduct_tax" id="sproduct_tax_' +
                    row_no +
                    '">' +
                    (pr_tax_rate ? '(' + formatDecimal(pr_tax_rate) + ')' : '') +
                    ' ' +
                    formatMoney(pr_tax_val * item_qty) +
                    '</span></td>';
            }*/
            tr_html +=
                '<td class="text-right"><span class="text-right ssubtotal" id="subtotal_' +
                row_no +
                '">' +
                //formatMoney((parseFloat(item_cost) - item_discount + parseFloat(pr_tax_val)) * parseFloat(item_qty)) +
                formatMoney(parseFloat(item_sale_price) * parseFloat(item_qty)) +
                '</span></td>';
            tr_html +=
                '<td class="text-center"><i class="fa fa-times tip todel" id="' +
                row_no +
                '" title="Remove" style="cursor:pointer;"></i></td>';
            newTr.html(tr_html);
            newTr.prependTo('#toTable');
            total += formatDecimal(parseFloat(item_sale_price) * parseFloat(item_qty), 4);
            wh_count += parseFloat(item_qty);
            an++;
            if (wh_item.options !== false) {
                $.each(wh_item.options, function () {
                    if (this.id == item_option && base_quantity > this.quantity) {
                        $('#row_' + row_no).addClass('danger');
                        $('#add_transfer, #edit_transfer').attr('disabled', false);
                    }
                });
            } else if (base_quantity > item_aqty) {
                $('#row_' + row_no).addClass('danger');
                $('#add_transfer, #edit_transfer').attr('disabled', false);
            }
        });

        var col = 2;
        /*if (site.settings.product_expiry == 1) {
            col++;
        }*/
        var tfoot =
            '<tr id="tfoot" class="tfoot active"><th colspan="' +
            col +
            '">Total</th><th class="text-center">' +
            formatQty(parseFloat(wh_count) - 1) +
            '</th>';
        /*if (site.settings.tax1 == 1) {
            tfoot += '<th class="text-right">' + formatMoney(product_tax) + '</th>';
        }*/
       tfoot +=
            '<th class="text-right">' +
            formatMoney(total) +
            '</th><th class="text-center"><i class="fa fa-trash-o" style="opacity:0.5; filter:alpha(opacity=50);"></i></th></tr>';
        $('#toTable tfoot').html(tfoot);

        // Totals calculations after item addition
        var gtotal = total + shipping;
        $('#wh_total').text(formatMoney(total));
        $('#wh_titems').text(wh_an - 1 + ' (' + formatQty(parseFloat(wh_count) - 1) + ')');
        if (site.settings.tax1) {
            $('#wh_ttax1').text(formatMoney(product_tax));
        }
        $('#wh_gtotal').text(formatMoney(gtotal));
        if (wh_an > parseInt(site.settings.bc_fix) && parseInt(site.settings.bc_fix) > 0) {
            $('html, body').animate({ scrollTop: $('#sticker').offset().top }, 500);
            $(window).scrollTop($(window).scrollTop() + 1);
        }
        //set_page_focus();
    }
}


/*function add_invoice_item(item) {
    
    if (wh_count == 1) {
        toitems = {};
        if ($('#poswarehouse').val()) {
            $('#poswarehouse').select2('readonly', true);
        } else {
            bootbox.alert(lang.select_above);
            item = null;
            return;
        }
    }
    if (item == null) return;

    var item_id = site.settings.item_addition == 1 ? item.item_id : item.id;
    if (toitems[item_id]) {
        var new_qty = parseFloat(toitems[item_id].row.qty) + 1;
        toitems[item_id].row.base_quantity = new_qty;
        if (toitems[item_id].row.unit != toitems[item_id].row.base_unit) {
            $.each(toitems[item_id].units, function () {
                if (this.id == toitems[item_id].row.unit) {
                    toitems[item_id].row.base_quantity = unitToBaseQty(new_qty, this);
                }
            });
        }
        toitems[item_id].row.qty = new_qty;
    } else {
        toitems[item_id] = item;
    }
    toitems[item_id].order = new Date().getTime();
    localStorage.setItem('toitems', JSON.stringify(toitems));
    wh_loadItems();
    return true;
}*/


function allowDiscountValue(){
    $("#notAllowError").html("");
    var  discount           = $('#order_discount_input').val();
    var  total_sale_val           = $('#titems').text();

    if (discount.includes('%')) {
        discount     = discount.replace(/\%/g,'');
        discount     = Number(discount);
        var allowDiscount     = $('#allow_discount_value').val(); 
        
    }else{
        discount     = parseFloat(discount);
        var  allowDiscount     = (50 * total_sale_val) / 100;
    }

    if(discount <= allowDiscount){
       console.log("allow");
    }else{
       
        document.getElementById("order_discount_input").value="";
        $("#notAllowError").html('<h4 style="color:red;">Not Allowed Maximum Discount!</h4>')
    }
    // allow_discount_value
}

</script>
<script type="text/javascript" charset="UTF-8"><?=$s2_file_date?></script>
<div id="ajaxCall"><i class="fa fa-spinner fa-pulse"></i></div>
<?php
if (isset($print) && !empty($print)) {
    /* include FCPATH.'themes'.DIRECTORY_SEPARATOR.$Settings->theme.DIRECTORY_SEPARATOR.'views'.DIRECTORY_SEPARATOR.'pos'.DIRECTORY_SEPARATOR.'remote_printing.php'; */
    include 'remote_printing.php';
}
?>

<script type="text/javascript" src="<?= base_url('assets/custom/pos.js') ?>"></script>
<script type="text/javascript" src="<?= base_url('assets/custom/pos.js') ?>"></script>
<script type="text/javascript" src="<?= $assets ?>js/plugins/decimal/decimal.js"></script>
<!--<script type="text/javascript" src="<?= base_url('themes/blue/admin/assets/js/transfers.js') ?>"></script>-->
</body>
</html>
