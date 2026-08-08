<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<style>
  @import url('https://fonts.googleapis.com/css?family=Open+Sans&display=swap');

  /* #2B9B48 */
  .track {
    position: relative;
    background-color: #ddd;
    height: 7px;
    display: -webkit-box;
    display: -ms-flexbox;
    display: flex;
    margin-bottom: 60px;
    margin-top: 50px
  }

  .track .step {
    -webkit-box-flex: 1;
    -ms-flex-positive: 1;
    flex-grow: 1;
    width: 25%;
    margin-top: -18px;
    text-align: center;
    position: relative
  }

  .track .step.done:before {
    background: #2B9B48
  }

  .track .step.active:before {
    background: #FF5722
  }

  .track .step::before {
    height: 7px;
    position: absolute;
    content: "";
    width: 100%;
    left: 0;
    top: 18px
  }

  .track .step.done .icon {
    background: #2B9B48;
    color: #fff
  }

  .track .step.active .icon {
    background: #ee5435;
    color: #fff
  }

  

  .track .icon {
    display: inline-block;
    width: 40px;
    height: 40px;
    line-height: 40px;
    position: relative;
    border-radius: 100%;
    background: #ddd
  }

  .track .step.active .text {
    font-weight: 400;
    color: #000
  }

  .track .text {
    display: block;
    margin-top: 7px
  }

  .itemside {
    position: relative;
    display: -webkit-box;
    display: -ms-flexbox;
    display: flex;
    width: 100%
  }

  .itemside .aside {
    position: relative;
    -ms-flex-negative: 0;
    flex-shrink: 0
  }

  .img-sm {
    width: 80px;
    height: 80px;
    padding: 7px
  }


  .itemside .info {
    padding-left: 15px;
    padding-right: 7px
  }

  .itemside .title {
    display: block;
    margin-bottom: 5px;
    color: #212529
  }

  p {
    margin-top: 0;
    margin-bottom: 1rem
  }

  .btn-warning {
    color: #ffffff;
    background-color: #ee5435;
    border-color: #ee5435;
    border-radius: 1px
  }

  .btn-warning:hover {
    color: #ffffff;
    background-color: #ff2b00;
    border-color: #ff2b00;
    border-radius: 1px
  }
</style>
<section class="page-contents">
    <div class="container">
        <div class="row">
            <div class="col-xs-12">

                <div class="row">
                    <div class="col-sm-12 col-md-12">

                        <div class="panel panel-default margin-top-lg">
                            <div class="panel-heading text-bold">
                                <i class="fa fa-list-alt margin-right-sm"></i> <?= lang('Track_Order'); ?>
                            </div>
                            <div class="panel-body p-0">
                            <?php  $attributes = array('class' =>'smart-form', 'id' => 'track-form','name' => 'track-form','enctype'=>"multipart/form-data");
echo form_open(base_url().'shop/track_order/'.$id, $attributes); ?> 
                              <div class="card">
                              <div class="card-body ">
                              <div class="row mb-2">
                                        <label class="form-label  col-lg-2 col-md-2 col-sm-12  mt-2 pt-2">Enter Order Number</label>
                                        <div class="col-lg-4 col-md-4 col-sm-12 mt-2" > 
                                            <div><input name="order_number" class="form-control" value="<?php echo set_value('order_number');?>" placeholder="Please Enter Order Number" required="">  </div>  
					                              </div> 
                                        <div class="col-lg-4 col-md-4 col-sm-12 mt-2"> <button type="submit" class="btn btn-info btn-sm1 view " style="background-color: #662d91;color: #fff;border-color: #662d91;" >Submit</button></div>
                                        
                              </div> 
                              <?php echo form_close();?> 
                              </div>
                           </div> <!-- pannel body div end here -->




                           <?php 
                            
                           if( empty($order) and $order_number!='' ) { ?>
                            
                                <div class="row">
                                <div class="col-md-12 mt-5">
                                    <h3 class="purpColor fw-semibold text-center">No Order Found!</h3>
                                </div>
                                </div>
                            

                            <?php } if(!empty($order)){
                                
                                    $order_status = 'Order Confirmed';
                                    $last_step = 'Delivered';
                                    $step = 1;
                                    if($order->courier_id != '' && $order->courier_order_status == '') {
                                    $step = 2;
                                    $order_status = 'Sent to Courier' ;
                                    }
                                    else if($order->courier_order_status == 'Under processing' || $order->courier_order_status == 'Sending scan' || $order->courier_order_status == 'Pending') {
                                        $order_status = 'On the way' ;
                                        $step = 3;
                                    }
                                    else if($order->courier_order_status == 'Delivered' || $order->courier_order_status == 'Sign scan') {
                                    $order_status = 'Delivered';
                                    $step = 4;
                                    }
                                    else if($order->courier_order_status != ''){
                                    $step = 4;
                                    $order_status = $order->courier_order_status;
                                    $last_step =  $order->courier_order_status;
                                    }

                                ?>


                                
                                    <div class="row">
                                    <div class="col-md-12 mt-5">
                                        <h3 class="purpColor fw-semibold">My Order Tracking</h3>
                                        <h4 class="mt-3 fw-semibold fs-5">Order no: <?php echo $order->id;?> <?php ?>
                                        <?= $inv->id; ?>
                                        </h4>

                                        <div class="my-3 p-3 border rounded shadow">
                                        <div class="card-body row">
                                            <div class="col"> <strong>Estimated Delivery time:</strong> <br>Within 3 to 5 days</div>
                                            <div class="col"> <strong>Shipping BY:</strong> <br> <?php if($order->courier_id == 1) {echo "Run-X";} else {echo "J&T";}?> | <i class="fa fa-phone"></i> +966115203838
                                            </div>
                                            <div class="col"> <strong>Current Status:</strong> <br> <?php echo $order_status;?> </div>
                                            <div class="col"> <strong>Tracking #:</strong> <br> <?php echo ($order->courier_order_tracking_id > 0 ? $order->courier_order_tracking_id: 'N/A');?> </div>
                                        </div>

                                        <div class="track">
                                            <div class="step done"> <span class="icon"> <i class="fa fa-check"></i> </span> <span class="text">Order
                                                confirmed</span> </div>
                                            <div class="step <?php if($step == 2 ) {echo "active";} elseif($step > 2) {echo "done";}?>"> <span class="icon"> <i class="fa fa-user"></i> </span> <span class="text"> Sent
                                                to courier</span> </div>
                                            <div class="step <?php if($step == 3 ) {echo "active";} elseif($step > 3) {echo "done";}?>"> <span class="icon"> <i class="fa fa-truck"></i> </span> <span class="text"> On the way
                                            </span> </div>
                                            <div class="step  <?php if($step >= 4 ) {echo "done";} ?>"> <span class="icon"> <i class="fa fa-thumbs-up"></i> </span> <span class="text"><?php echo  $last_step;?></span> </div>
                                        </div>

                                        </div>


                                    </div>
                                    </div>
                                 
                                 


                           <?php  } ?>







                        </div>
                    </div>

                    
                </div>
            </div>
        </div>
    </div>
</section>
