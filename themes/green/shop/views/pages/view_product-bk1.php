<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<section class="page-contents">
    <div class="container">
        <div class="row">
            <div class="col-xs-12">

                <div class="row">
                    <div class="col-sm-12 col-md-12">

                        <div class="panel panel-default margin-top-lg">
                            <div class="panel-heading text-bold">
                                <i class="fa fa-list-alt margin-right-sm"></i> <?= $product->name . ' (' . $product->code . ')'; ?>
                                <a href="<?= shop_url('products'); ?>" class="pull-right"><i class="fa fa-share"></i> <?= lang('products'); ?></a>
                            </div>
                            <div class="panel-body mprint">

                                <div class="row">
                                    <div class="col-sm-7">

                                        <div class="photo-slider">
                                            <div class="carousel slide article-slide" id="photo-carousel">

                                                <div class="carousel-inner cont-slider">
                                                    <div class="item active">
                                                        <a href="#" data-toggle="modal" data-target="#lightbox">
                                                            <img src="<?= base_url() ?>assets/uploads/<?= $product->image ?>" alt="<?= $product->name ?>" class="img-responsive img-thumbnail"/>
                                                        </a>
                                                    </div>
                                                    <?php
                                                    if (!empty($images)) {
                                                        foreach ($images as $ph) {
                                                            echo '<div class="item"><a href="#" data-toggle="modal" data-target="#lightbox"><img class="img-responsive img-thumbnail" src="' . base_url('assets/uploads/' . $ph->photo) . '" alt="' . $ph->photo . '" /></a></div>';
                                                        }
                                                    }
                                                    ?>
                                                </div>

                                                <ol class="carousel-indicators">
                                                    <li class="active" data-slide-to="0" data-target="#photo-carousel">
                                                        <img class="img-thumbnail" alt="" src="<?= base_url() ?>assets/uploads/thumbs/<?= $product->image ?>">
                                                    </li>
                                                    <?php
                                                    $r = 1;
                                                    if (!empty($images)) {
                                                        foreach ($images as $ph) {
                                                            echo '<li class="" data-slide-to="' . $r . '" data-target="#photo-carousel"><img class="img-thumbnail" alt="" src="' . base_url('assets/uploads/thumbs/' . $ph->photo) . '"></li>';
                                                            $r++;
                                                        }
                                                    }
                                                    ?>

                                                </ol>
                                            </div>
                                            <div class="clearfix"></div>
                                        </div>

                                       
                                    </div>

                                    <div class="col-sm-5">
                                        <h1>
                                           
                                            <?= $product->name; ?>
                                        </h1>
                                        <h4>
                                            <?php if (!empty($product->second_name)) {
                                                        ?>
                                                  
                                                    
                                                        <td width="50%"><?= $product->second_name; ?></td>
                                                    </tr>
                                                    <?php
                                                    } ?>
                                        </h4>
                                        <h3>
                                            <?php 
                                                if ($product->promotion) {
                                                    echo '<del class="text-red" style="font-size: 16px;">' . $this->sma->convertMoney(isset($product->special_price) && !empty(isset($product->special_price)) ? $product->special_price : $product->price) . '</del><br>';
                                                    echo $this->sma->convertMoney($product->promo_price);
                                                } else{
                                                    echo $this->sma->convertMoney(isset($product->special_price) ? $product->special_price : $product->price);
                                                }
                                                
                                            ?>
                                        </h3>
                                          <?php if ($product->type == 'combo') {
                                                        ?>
                                            <strong><?= lang('combo_items') ?></strong>
                                            <div class="table-responsive">
                                                <table
                                                class="table table-bordered table-striped table-condensed dfTable two-columns">
                                                <thead>
                                                    <tr>
                                                        <th><?= lang('product_name') ?></th>
                                                        <th><?= lang('quantity') ?></th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($combo_items as $combo_item) {
                                                            echo '<tr><td>' . $combo_item->name . ' (' . $combo_item->code . ') </td><td>' . $this->sma->formatQuantity($combo_item->qty) . '</td></tr>';
                                                        } ?>
                                                </tbody>
                                            </table>
                                        </div>
                                        <?php
                                                    } ?>
                                                     <?php if (!$shop_settings->hide_price) {
                                                        ?>
                                        <?php if (($product->type != 'standard' || $warehouse->quantity > 0) || $Settings->overselling) {
                                                            ?>
                                        <?= form_open('cart/add/' . $product->id, 'class="validate"'); ?>
                                        <div class="form-group">
                                            <?php
                                            if ($variants) {
                                                foreach ($variants as $variant) {
                                                    $opts[$variant->id] = $variant->name . ($variant->price > 0 ? ' (+' . $this->sma->convertMoney($variant->price, true, false) . ')' : ($variant->price == 0 ? '' : ' (+' . $this->sma->convertMoney($variant->price, true, false) . ')'));
                                                }
                                                echo form_dropdown('option', $opts, '', 'class="form-control selectpicker mobile-device" required="required"');
                                            } ?>
                                        </div>

                                        <!--<div class="form-group">-->
                                        <!--    <div class="input-group">-->
                                        <!--        <span class="input-group-addon pointer btn-minus"><span class="fa fa-minus"></span></span>-->
                                        <!--        <input type="text" name="quantity" class="form-control text-center quantity-input" value="1" required="required">-->
                                        <!--        <span class="input-group-addon pointer btn-plus"><span class="fa fa-plus"></span></span>-->
                                        <!--    </div>-->
                                        <!--</div>-->
                                        <!-- <input type="hidden" name="quantity" class="form-control text-center" value="1"> -->

                                        <div class="form-group">
                                            <div class="row">
                                                <div class="col-md-4 col-xs-5" id="cart-quantity">
                                                    <div class="input-group">
                                                     <span class="input-group-addon pointer btn-minus"><span class="fa fa-minus"></span></span>
                                                     <input type="text" name="quantity" class="form-control text-center quantity-input" value="1" required="required">
                                                     <span class="input-group-addon pointer btn-plus"><span class="fa fa-plus"></span></span>
                                                </div>
                                                 </div>
                                                <div class="col-md-8 col-xs-7" id="cart-button">
                                                    <div class="btn-group" role="group" aria-label="...">
                                               
                                                <!--<button class="btn btn-info btn-lg add-to-wishlist" data-id="<?= $product->id; ?>"><i class="fa fa-heart-o"></i></button>-->
                                                <button type="submit" class="btn btn-theme btn-lg"><i class="fa fa-shopping-cart padding-right-md"></i> <?= lang('add_to_cart'); ?></button>
                                            </div>
                                                </div>
                                            </div>
                                            
                                                
                                           
                                            
                                        </div>
                                        <?= form_close(); ?>
                                        <?php
                                                        } else {
                                                            echo '<div class="well well-sm"><strong>' . lang('item_out_of_stock') . '</strong></div>';
                                                        } ?>
                                        <?php
                                                    } ?>
                                        <div class="clearfix"></div>
                        
                                </div>
                                <div class="clearfix"></div>

                                
                                <div class="row" style="padding:10px 10px 10px 10px !important;">
                                    <div class="col-md-8">    
                                    
                                        <?= $product->product_details ? '<div class=""><div class="">' . $product->product_details . '</div></div>' : ''; ?>
                                    </div>
                                    <div class="col-md-4" >
                                         <h2>Additional Information</h2>
                                         <div class="table-responsive">
                                            <table class="table table-bordered table-striped dfTable table-right-left">
                                                <tbody>
                                                   
                                                    <tr>
                                                        <td><?= lang('code'); ?></td>
                                                        <td><?= $product->code; ?></td>
                                                    </tr>
                                                    <tr>
                                                        <!--<td><?= lang('type'); ?></td>-->
                                                        <!--<td><?= lang($product->type); ?></td>-->
                                                    </tr>
                                                    <tr>
                                                        <td><?= lang('brand'); ?></td>
                                                        <td><?= $brand ? '<a href="' . site_url('brand/' . $brand->slug) . '" class="line-height-lg">' . $brand->name . '</a>' : ''; ?></td>
                                                    </tr>
                                                    <tr>
                                                        <td><?= lang('category'); ?></td>
                                                        <td><?= '<a href="' . site_url('category/' . $category->slug) . '" class="line-height-lg">' . $category->name . '</a>'; ?></td>
                                                    </tr>
                                                    <?php if ($product->subcategory_id) {
                                                        ?>
                                                    <tr>
                                                        <td><?= lang('subcategory'); ?></td>
                                                        <td><?= '<a href="' . site_url('category/' . $category->slug . '/' . $subcategory->slug) . '" class="line-height-lg">' . $subcategory->name . '</a>'; ?></td>
                                                    </tr>
                                                    <?php
                                                    } ?>

                                                    <?php if (!$shop_settings->hide_price) {
                                                        ?>
                                                   
                                                    <?php
                                                    } ?>

                                                    <?php
                                                    if ($product->promotion) {
                                                        echo '<tr><td>' . lang('promotion') . '</td><td><strong>' . $this->sma->convertMoney($product->promo_price) . '</strong><br>' . ($product->start_date && $product->start_date != '0000-00-00' ? lang('start_date') . ': <strong>' . $this->sma->hrsd($product->start_date) . '</strong><br>' : '') . ($product->end_date && $product->end_date != '0000-00-00' ? lang('end_date') . ': <strong>' . $this->sma->hrsd($product->end_date) . '</strong>' : '') . '</td></tr>';
                                                    }
                                                    ?>

                                                    <?php if ($product->tax_rate) {
                                                        ?>
                                                    <tr>
                                                        <!--<td><?= lang('tax_rate'); ?></td>-->
                                                        <!--<td><?= $tax_rate->name; ?></td>-->
                                                    </tr>
                                                    <tr>
                                                        <!--<td><?= lang('tax_method'); ?></td>-->
                                                        <!--<td><?= $product->tax_method == 0 ? lang('inclusive') : lang('exclusive'); ?></td>-->
                                                    </tr>
                                                    <?php
                                                    } ?>

                                                    <tr>
                                                        <td><?= lang('unit'); ?></td>
                                                        <td><?= $unit ? $unit->name . ' (' . $unit->code . ')' : ''; ?></td>
                                                    </tr>
                                                    <?php if (!empty($warehouse) && $product->type == 'standard') {
                                                        ?>
                                                    <tr>
                                                        <!--<td><?= lang('in_stock'); ?></td>-->
                                                        <!--<td><?= $warehouse->quantity > 0 ? lang('yes') : lang('no'); ?></td>-->
                                                        <!-- <td><?= $this->sma->formatQuantity($warehouse->quantity); ?></td> -->
                                                    </tr>
                                                    <?php
                                                    } ?>

                                                    <?php if ($variants) {
                                                        ?>
                                                    <tr>
                                                        <td><?= lang('product_variants'); ?></td>
                                                        <td><?php foreach ($variants as $variant) {
                                                            echo '<span class="label label-primary">' . $variant->name . '</span> ';
                                                        } ?></td>
                                                    </tr>
                                                    <?php
                                                    } ?>

                                                    <?php if (!empty($options)) {
                                                        foreach ($options as $option) {
                                                            if ($option->wh_qty != 0) {
                                                                echo '<tr><td colspan="2" class="bg-primary">' . $option->name . '</td></tr>';
                                                                // echo '<tr><td>' . lang('in_stock') . ': ' . $this->sma->formatQuantity($option->wh_qty) . '</td>';
                                                                if (!$shop_settings->hide_price) {
                                                                    echo '<tr><td>' . lang('in_stock') . ': ' . ($option->wh_qty ? lang('yes') : lang('no')) . '</td>';
                                                                    echo '<td>' . lang('price') . ': ' . $this->sma->convertMoney((isset($product->special_price) ? $product->special_price : $product->price) + $option->price) . '</td>';
                                                                    echo '</tr>';
                                                                } else {
                                                                    echo '<tr><td>' . lang('in_stock') . '</td><td>' . ($option->wh_qty ? lang('yes') : lang('no')) . '</td></tr>';
                                                                }
                                                            }
                                                        }
                                                    } ?>

                                                    <?php if ($product->cf1 || $product->cf2 || $product->cf3 || $product->cf4 || $product->cf5 || $product->cf6) {
                                                        //if ($product->cf1) {
                                                        //    echo '<tr><td>' . lang('Product_Country') . '</td><td>' . $product->cf1 . '</td></tr>';
                                                        //}
                                                       // if ($product->cf2) {
                                                       //     echo '<tr><td>' . lang('product_precription') . '</td><td>' . $product->cf2 . '</td></tr>';
                                                       // }
                                                        if ($product->cf3) {
                                                            echo '<tr><td>' . lang('pcf3') . '</td><td>' . $product->cf3 . '</td></tr>';
                                                        }
                                                        if ($product->cf4) {
                                                            echo '<tr><td>' . lang('pcf4') . '</td><td>' . $product->cf4 . '</td></tr>';
                                                        }
                                                        if ($product->cf5) {
                                                            echo '<tr><td>' . lang('pcf5') . '</td><td>' . $product->cf5 . '</td></tr>';
                                                        }
                                                        if ($product->cf6) {
                                                            echo '<tr><td>' . lang('pcf6') . '</td><td>' . $product->cf6 . '</td></tr>';
                                                        }
                                                    } ?>


                                                </tbody>
                                            </table>
                                          
                                    

                                </div>
                                
                                        </div>
                                     </div>
                                    </div>
                                </div>
                            <?php //include 'share.php'; ?>
                        </div>
                    </div>
                </div>

                
            </div>
        </div>
    </div>
</div>


</section>

<div id="lightbox" class="modal" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-middle">
        <div class="modal-content">
            <button type="button" class="close hidden" data-dismiss="modal" aria-hidden="true">×</button>
            <div class="modal-body">
                <img src="" alt="" />
            </div>
        </div>
    </div>
</div>
