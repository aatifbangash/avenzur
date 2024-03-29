<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<section class="page-contents">
    <div class="container container-max-width order-page-k">
        <div class="row">
            <div class="col-xs-12">

                <div class="row">
                    <div class="col-md-12">

                        <div class="panel panel-default margin-top-lg">
                            <h3 class="fw-semibold">
                               <!-- <i class="fa fa-list-alt margin-right-sm"></i> --><?= lang('my_orders'); ?>
</h3>
                            <div class="panel-body py-0 px-0">
                                <?php
                                if (!empty($orders)) {
                                    echo '<div class="row">';
                                    echo '<div class="col-sm-12 text-bold mb-3">' . lang('click_to_view') . '</div>';
                                    echo '<div class="clearfix"></div>';
                                    $r = 1;
                                    foreach ($orders as $order) {
                                        ?>
                                        <div class="col-md-6 order-detail-cont mb-4 ">
                                          
                                           <!--<a href="<?= shop_url('orders/' . $order->id); ?>" class="link-address<?= $order->payment_status == 'paid' ? '' : ' active' ?>">-->
                                           <div class="table-custom-container">
                                            <table class="table table-borderless table-condensed order-table mb-0" >
                                                <?= '<tr><td>' . lang('Invoice No.') . '</td><td>' . $order->id . '</td></tr>'; ?>
                                                <?= '<tr><td>' . lang('date') . '</td><td>' . $this->sma->hrld($order->date) . '</td></tr>'; ?>
                                                <?= '<tr><td>' . lang('sale_status') . '</td><td>' . lang($order->sale_status) . '</td></tr>'; ?>
                                                <?= '<tr><td>' . lang('amount') . '</td><td>' . $this->sma->formatMoney($order->grand_total, $this->default_currency->symbol) . '</td></tr>'; ?>
                                                <?= '<tr><td>' . lang('payment_status') . '</td><td>' . ($order->payment_status ? lang($order->payment_status) : lang('no_payment')) . '</td></tr>'; ?>
                                                <?= '<tr><td>' . lang('delivery_status') . '</td><td><span class="label ' . ($order->delivery_status == 'delivered' ? 'label-success' : 'label-info') . '">' . ($order->delivery_status ? lang($order->delivery_status) : lang('verifying')) . '</span></td></tr>'; ?>
                                               
                                               <?= '<tr><td>' . lang('View_Order') . '</td><td><a href=orders/'.$order->id.'>  <input type="submit" class="btn btn-info btn-sm view" value="View Order" /></a>'; ?>
                                               <?= '<tr><td>' . lang('View Invoice') . '</td><td><a href=invoiceorders/'.$order->id.'>  <input type="submit" class="btn btn-info btn-sm view" value="View Order" /></a>'; ?>
                                               <?php if($order->payment_status == 'paid') {?>
                                               <?= '<a href=orders/'.$order->id.'?action=tracking>  <input type="button" class="btn btn-info btn-sm view" value="Track Order" /></a>'; ?>
                                            <?php }?>   
                                               <?= ' </td></tr>'; ?>
                                               <?php if($order->sale_status =='completed'){ ?>
                                               <?= '<tr><td>' . lang('Return') . '</td>'?>
                                               
                                               <?php  
                                                /*if(strlen($order->refund_status) > 0){  
                                                    echo '<td><span class="label '.($order->refund_status == 'success' ? 'label-success' : 'label-info').'">'.$order->refund_status.'</span></td>';
                                                    
                                                }else{ 
                                                    
                                                    echo '<td><button type="button" class="btn btn-info btn-sm refund" data-id='.$order->id.' data-toggle="modal" data-target="#myModal">Refund</button></td>';
                                                    
                                                }*/
                                                echo '<td><span class="label label-info"><a target="blank" href="https://api.whatsapp.com/send?phone=966551251997">Contact us on whatsapp</a></span></td>';
                                                echo '</tr>';} 
                                                ?>
                                             
                                            </table> </div>
                                                
                                                <!--<span class="count"><i><?= $order->id; ?></i></span>-->
                                                <!--<span class="edit"><i class="fa fa-eye"></i></span>-->
                                      
                                                                
            
                                                                   
                                        </div>
                                        <?php
                                        $r++;
                                    }
                                    echo '</div>'; ?>
                                      
                                    <!--<div class="row" style="margin-top:15px;">
                                        <div class="col-md-6">
                                            <span class="page-info line-height-xl hidden-xs hidden-sm">
                                                <?php //echo str_replace(['_page_', '_total_'], [$page_info['page'], $page_info['total']], lang('page_info')); ?>
                                            </span>
                                        </div>
                                        <div class="col-md-6">
                                        <div id="pagination" class="pagination-right"><?php //echo $pagination; ?></div>
                                        </div>
                                    </div>-->
                                    <?php
                                } else {
                                    echo '<strong>' . lang('no_data_to_display') . '</strong>';
                                }
                                ?>
                            </div>
                        </div>
                    </div>

                    <div class="col-sm-3 col-md-2">
                        <?php //include 'sidebar1.php'; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
                                                         <!-- Modal -->

                                                  <div id="myModal" class="modal fade" role="dialog">
                                                        <div class="modal-dialog">
                                                            <!-- Modal content-->
                                                                <div class="modal-content">
                                                                   <div class="modal-header">
                                                                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                                                                        <h4 class="modal-title">Refund Form</h4>
                                                                    </div>
                                                                    <div class="modal-body">
                                                                        <div class="form-group">
                                                                            <input type="hidden" class="form-control" value=""   id="order_id" name="order_id">
                                                                            <input type="hidden" class="form-control"  value="<?=$this->session->userdata('user_id')?>" id="customer_id" name="customer_id">
                                                                        </div>
                                                                        <div class="form-group">
                                                                            <label for="reason"> Reason for Refund :</label>
                                                                                    <select  id="reason_refund" name="reson_refund" class="form-control" required="true">
                                                                                        <option value="Excessive Amount">Excessive Amount   </option>
                                                                                        <option value="Wrong Product Delivery">Wrong Product Delivery</option>
                                                                                        <option value="Product doesn't work">Product doesn't work</option>
                                                                                    </select>
                                                                        </div>
                                                                        <div class="form-group">
                                                                             <label for="notes">  Additional Notes:</label>
                                                                                <textarea class="form-control" rows="5" name="notes" id="notes" required="true"></textarea>
                                                                        </div>
            
                                                                            <button type="submit" class="btn btn-primary" name="save" id="save">Submit</button>
                                                                    </div>
                                                                        <div class="modal-footer">
                                                                           <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                                                                        </div>
                                                                </div>
                                                                        
                                                        </div>
                                                    </div>
<script>
    
</script>