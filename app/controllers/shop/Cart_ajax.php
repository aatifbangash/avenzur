<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Cart_ajax extends MY_Shop_Controller
{
    public function __construct()
    {
        parent::__construct();
        if ($this->Settings->mmode) {
            redirect('notify/offline');
        }
        if ($this->shop_settings->hide_price) {
            redirect('/');
        }
        if ($this->shop_settings->private && !$this->loggedIn) {
            redirect('/login');
        }
        $this->load->admin_model('settings_model');

       
        $is_customer_logged_in = $this->loggedIn;
        
        if($is_customer_logged_in){
            $id = $this->session->userdata('company_id');
             
            $auto_apply_result = $this->shop_model->is_eligible_for_auto_apply($id);
            if($auto_apply_result['can_apply']) {
                $this->auto_apply($auto_apply_result['coupon']);
               
            } 
          
        }

         
    }


    public function subscribe_newsletter(){
        if ($_GET['newsletterEmail']) {
            $subscription = $this->settings_model->add_newsletter_subscription($_GET['newsletterEmail']);
            if($subscription == 'failed'){
                $this->sma->send_json(['status' => lang('error'), 'message' => 'Unable to add subscription']);
            }else if($subscription == 'added'){
                $this->sma->send_json(['status' => lang('success'), 'message' => 'Subscription added successfully']);
            }else if($subscription == 'exists'){
                $this->sma->send_json(['status' => lang('error'), 'message' => 'Subscription already exists']);
            }
        }
    }

    public function remove($rowid = null)
    {
        //$this->session->unset_userdata('coupon_details');
        if ($rowid) {
            $item = $this->cart->get_item($rowid);
            if($item['code'] == '06285193000301'){

            }
            return $this->cart->remove($rowid);
        }
        if ($this->input->is_ajax_request()) {
            if ($rowid = $this->input->post('rowid', true)) {
                $item = $this->cart->get_item($rowid);
                if($item['code'] == '06285193000301'){

                }
                if ($this->cart->remove($rowid)) {
                    $this->sma->send_json(['cart' => $this->cart->cart_data(true), 'status' => lang('success'), 'message' => lang('cart_item_deleted')]);
                }
            }
        }
    }

    public function apply($code, $userId){
        $coupon = $this->shop_model->get_coupon_by_code($code);
        $is_valid = $this->is_valid_coupon($coupon);

        /**
         * Check for the Free Shipment Category
         */
        $is_free_shipping = $coupon['free_shipping'];
        $referrer = $coupon['referrer_code'];
        if($is_free_shipping && $referrer){
             $this->session->set_userdata('coupon_details', array(
                        'code' => $code,
                        'coupon' => $coupon,
                        'free_shipping' => true,
                        'dis_amount' => $this->cart->get_total_discount(),
                        'dis_percent' => $coupon_data->amount
                    ));
        }


         /**
         * Check if product Ids are added in the Coupon Code, 
         * If yes, check if the cart has these products.
         * Else, the coupon cant be applied.
         */
        $valid_product_ids = json_decode($coupon['product_ids'], true);
        if(isset($valid_product_ids) && $valid_product_ids.count > 0){
            $cart_contents = $this->cart->contents();
            $products_on_cart = array();
            foreach ($cart_contents as $item => $val) {
                $products_on_cart []= $val['product_id'];
            }
            $matching_products = array_intersect($products_on_cart, $valid_product_ids);
            
            if($coupon_data->discount_type == "percent"){
                    $cart_total = $cart_arr->cart_contents['cart_total'];
                    foreach ($cart_contents as $item => $val) {
                            
                        if(in_array($val['product_id'], $eligible_products)){
                            $data = [
                                'rowid'  => $val['rowid'],
                                'discount'  => ($val['price'] *$val['qty']* $coupon_data->amount) / 100
                            ];
                        }else{
                                $data = [
                                'rowid'  => $val['rowid'],
                                'discount'  => 0
                            ];
                        } 
                        array_push($cart_arr, $data);
                    }
                    $this->cart->update($cart_arr);
                    
                    $this->session->set_userdata('coupon_details', array(
                        'code' => $coupon_code,
                        'dis_amount' => $this->cart->get_total_discount(),
                        'dis_percent' => $coupon_data->amount
                    ));
                }                    

        }else{

            
            foreach ($cart_contents as $item => $val) {
                $data = [
                    'rowid'  => $val['rowid'],
                    'discount'  => ($val['price'] *$val['qty']* $coupon_data->amount) / 100
                ];
                array_push($cart_arr, $data);
            }
            $this->cart->update($cart_arr);
            $this->session->set_userdata('coupon_details', array(
                'code' => $coupon_code,
                'dis_amount' => $this->cart->get_total_discount(),
                'dis_percent' => $coupon_data->amount
            ));
        }
        
    }
    
    public function is_valid_coupon($coupon, $userId){
        $coupon_code = $coupon['code'];
        if(!$coupon_code){
            return false;
        }
        if (!$coupon['is_active']) {
            return false;
        }
        /**
         * Check : If coupon validity period.
         */
        $today = date('Y-m-d');
        $today_time =  strtotime($today);
        if(!$coupon['valid_from']){
            return false;
        }
        $coupon_valid_from = strtotime($coupon['valid_from']);
        $coupon_expire_at = strtotime($coupon['date_expires']);
        if($today_time < $coupon_valid_from){
            return false;
        }
        if($coupon_expire_at){
            if($coupon_expire_at < today_time){
                return false;
            }
        }

        /**Validity Period Check end */
       
        /**Check if the coupon has been used already by the user max_use times */
        $usage_limit = $coupon['usage_limit_per_user'];
        if($usage_limit){
             $is_customer_logged_in = $this->loggedIn;
            if(!$is_customer_logged_in){ // Since there is a user dependency, user must login first
                return false;
            }
            $id = $this->session->userdata('company_id');
            $usage_count = $this->shop_model->get_usage_by_user($id,$coupon_code );
            if($usage_count >= $usage_limit){
                return false;
            }
        }

        /**Minimum Cart Amount is met */
        $cart_arr = $this->cart;
        $cart_total = $cart_arr->cart_contents['cart_total'];
        $min_amount = $coupon['minimum_amount'];
        $max_amount = $coupon['max_amount'];
        if($min_amount){
            if($cart_total < $min_amount){
                return false;
            }
        }
        if($max_amount){
            if($cart_total > $max_amount){
                return false;
            }
        }
      return true;
    }



    public function remove_coupon_code(){
        $coupon_details = $this->session->userdata('coupon_details');
        
        // Remove any discount if no coupon is detected
        $cart_arr = $this->cart;
        $cart_total = $cart_arr->cart_contents['cart_total'];

        $coupon_disc = $this->cart->get_total_discount();
        $cart_total = $cart_total + $coupon_disc;

        $cart_contents = $this->cart->contents();
        $cart_arr = array();
        foreach ($cart_contents as $item => $val) {
            if($val['code'] != '06285193000301'){
                $data = [
                    'rowid'  => $val['rowid'],
                    'discount'  => 0
                ];
                array_push($cart_arr, $data);
            }
            
        }

        $this->cart->update($cart_arr);

        $this->session->unset_userdata('coupon_details');
    }

    public function remove_coupon(){
        $coupon_details = $this->session->userdata('coupon_details');
        if(isset($coupon_details['code'])){
            $c_code = $coupon_details['code'];

            // Remove any discount if no coupon is detected
            $cart_arr = $this->cart;
            $cart_total = $cart_arr->cart_contents['cart_total'];

            $coupon_disc = $this->cart->get_total_discount();
            $cart_total = $cart_total + $coupon_disc;

            $cart_contents = $this->cart->contents();
            $cart_arr = array();
            foreach ($cart_contents as $item => $val) {
                $data = [
                    'rowid'  => $val['rowid'],
                    'discount'  => 0
                ];

                if($c_code == 'fitness' && $val['code'] == '06285193000301'){
                    $data['qty'] = $val['qty'] / 2;
                    $data['disc_qty'] = 0;
                }
                
                array_push($cart_arr, $data);
            }

            $this->cart->update($cart_arr);
            $this->session->unset_userdata('coupon_details');
            //echo json_encode(array('status' => 'success', 'action' => 'subtract', 'total' => $this->cart->total(), 'discount' => 0));
            $this->session->set_flashdata('success', 'Coupon Code Removed');
            redirect('cart');
        }
    }
   

    public function auto_apply($coupon_data){  
         $cart_contents = $this->cart->contents();
         $cart_arr = array();
         foreach ($cart_contents as $item => $val) {
             $data = [
                    'rowid'  => $val['rowid'],
                    'discount'  => ($val['price'] *$val['qty']* $coupon_data->amount) / 100
                ];
            array_push($cart_arr, $data);
        }
         
        $this->cart->update($cart_arr);
        $this->session->set_userdata('coupon_details', array(
            'code' => $coupon_code,
            'dis_amount' => $this->cart->get_total_discount(),
            'dis_percent' => $coupon_data->amount
        ));
        $this->session->set_flashdata('message', 'Coupon Code Applied');
        // redirect('cart');

    }

    public function apply_coupon(){
        $cartId = $this->cart->cart_id;
         
        $coupon_arr = array('mpay' => 10, 'zaps10' => 10, 'welcome' => 5, 'alf10' => 10, 'mc24' => 10, 'neqaty10' => 10, 'enbd24' => 10, 'anb10' => 10, 'eid10' => 10);
        $coupon_cap_arr = array('mpay' => 100, 'zaps10' => 10, 'welcome' => 10, 'alf10' => 50, 'mc24' => 50, 'neqaty10' => 50, 'enbd24' => 50, 'anb10' => 50, 'eid10' => 50);
        $pattern_match = 0;

        $sulfad_coupon_code = 'fitness';

        if($this->input->post('card_number') && preg_match('/^510510/', $this->input->post('card_number'))){
            $coupon_code = 'enbd24';
            $pattern_match = 1;
        }else if($this->input->post('card_number') && preg_match('/^410685/', $this->input->post('card_number'))){
            $coupon_code = 'enbd24';
            $pattern_match = 1;
        }else if($this->input->post('card_number') && preg_match('/^410682/', $this->input->post('card_number'))){
            $coupon_code = 'enbd24';
            $pattern_match = 1;
        }else if($this->input->post('card_number') && preg_match('/^410683/', $this->input->post('card_number'))){
            $coupon_code = 'enbd24';
            $pattern_match = 1;
        }else if($this->input->post('card_number') && preg_match('/^410684/', $this->input->post('card_number'))){
            $coupon_code = 'enbd24';
            $pattern_match = 1;
        }else if($this->input->post('card_number') && preg_match('/^458263/', $this->input->post('card_number'))){
            $coupon_code = 'enbd24';
            $pattern_match = 1;
        }else{
            $coupon_code    = strtolower($this->input->post('coupon_code'));
        }

        $coupon_details = $this->session->userdata('coupon_details');
       
        if(isset($coupon_details['code'])){
            $c_code = $coupon_details['code'];
            
        }
    
        if(isset($coupon_arr[$coupon_code]) && $this->cart->get_total_discount() <= 0 && $pattern_match == 0 && $this->cart->total() >= $coupon_cap_arr[$coupon_code]){
            // Set All Coupon Discount except ENBD
            $cart_arr = $this->cart;
            $cart_total = $cart_arr->cart_contents['cart_total'];
            $discount = $coupon_arr[$coupon_code];
            $coupon_disc = ($cart_total*$discount)/100;
            //$cart_total = $cart_total - $coupon_disc;
            
            $cart_contents = $this->cart->contents();
            $cart_arr = array();
            foreach ($cart_contents as $item => $val) {
                $data = [
                    'rowid'  => $val['rowid'],
                    'discount'  => ($val['subtotal'] * $discount) / 100
                ];
                array_push($cart_arr, $data);
            }

            $this->cart->update($cart_arr);

            $this->session->set_userdata('coupon_details', array(
                'code' => $coupon_code,
                'dis_amount' => $coupon_disc,
                'dis_percent' => $coupon_arr[$coupon_code]
            ));

            //$this->cart->set_discount($coupon_disc);

            $this->session->set_flashdata('message', 'Coupon Code Applied');
            redirect('cart');

        }else if(isset($coupon_arr[$coupon_code]) && $coupon_code == 'enbd24' && $c_code == 'enbd24' && $this->cart->get_total_discount() <= 0){
            // Emirates NBD discount applied successfully
            $cart_arr = $this->cart;
            $cart_total = $cart_arr->cart_contents['cart_total'];
            $discount = $coupon_arr[$coupon_code];
            $coupon_disc = ($cart_total*$discount)/100;
            $cart_total = $cart_total - $coupon_disc;

            $cart_contents = $this->cart->contents();
            $cart_arr = array();
            foreach ($cart_contents as $item => $val) {
                $data = [
                    'rowid'  => $val['rowid'],
                    'discount'  => ($val['price'] * $discount) / 100
                ];
                array_push($cart_arr, $data);
            }

            $this->cart->update($cart_arr);

            $this->session->set_userdata('coupon_details', array(
                'code' => $coupon_code,
                'dis_amount' => $this->cart->get_total_discount(),
                'dis_percent' => $coupon_arr[$coupon_code]
            ));

            echo json_encode(array('status' => 'success', 'action' => 'add', 'total' => $this->cart->total(), 'discount' => $this->cart->get_total_discount()));
        }else if(!isset($coupon_arr[$coupon_code]) && isset($coupon_arr[$c_code]) && $this->input->post('coupon_code') === null && $c_code == 'enbd24' && $this->cart->get_total_discount() >= 0){
            // Remove any discount if no coupon is detected
            $cart_arr = $this->cart;
            $cart_total = $cart_arr->cart_contents['cart_total'];

            $coupon_disc = $this->cart->get_total_discount();
            $cart_total = $cart_total + $coupon_disc;

            $cart_contents = $this->cart->contents();
            $cart_arr = array();
            foreach ($cart_contents as $item => $val) {
                $data = [
                    'rowid'  => $val['rowid'],
                    'discount'  => 0
                ];
                array_push($cart_arr, $data);
            }

            $this->cart->update($cart_arr);

            echo json_encode(array('status' => 'success', 'action' => 'subtract', 'total' => $this->cart->total(), 'discount' => 0));
        }else if(isset($coupon_arr[$coupon_code]) && !isset($coupon_arr[$coupon_code]) && $this->cart->get_total_discount() <= 0){
            // Donot do anything if coupon code does not match and it is not applied already

            echo json_encode(array('status' => 'fail', 'action' => 'add', 'discount' => 0));

        }else if($coupon_code == $sulfad_coupon_code){
            // Set Sulfad Discounted Quantity Only
            $cart_contents = $this->cart->contents();
            $cart_arr = array();
            //$sulfad_promo_count = 0;
            foreach ($cart_contents as $item) {
                
                if($item['code'] == '06285193000301'){
                    $data = [
                        'rowid'  => $item['rowid'],
                        //'discount'  => 0
                    ];
                    if($item['qty'] >= 2) {
                        $data['discount'] = ($item['qty'] * 37.5);
                    }

                    $data['discount'] = 0;
                    $data['disc_qty'] = $item['qty'];
                    $data['qty'] = $item['qty'] * 2;
                }else{
                    /*$data = [
                        'rowid'  => $item['rowid'],
                        'discount'  => ($item['price'] * 10) / 100
                    ];*/
                    $data = [
                        'rowid'  => $item['rowid'],
                        'discount'  => 0
                    ];
                }
                
                array_push($cart_arr, $data);
                
            }
            
            $this->cart->update($cart_arr);

            $this->session->set_userdata('coupon_details', array(
                'code' => $coupon_code,
                'dis_amount' => 0,
                'dis_percent' => 0
            ));

            //$this->cart->set_discount($coupon_disc);

            $this->session->set_flashdata('message', 'Coupon Code Applied');
            redirect('cart');
        }else{
         
            if( $coupon_code  ){
                $cart_contents = $this->cart->contents();
                $is_customer_logged_in = $this->loggedIn;
                $userId = null;
                if($is_customer_logged_in){
                    $userId = $this->session->userdata('company_id');
                }
                  
                $response = $this->shop_model->can_apply_coupon($coupon_code,$userId, $cartId);
                if( $response != null){
                    $coupon_data = $response['coupon_data'];
                    $eligible_products = $response['eligible_products'];
                    $cart_arr = array();

                    $is_free_shipping = $coupon_data ->free_shipping;
                    $referrer = $coupon_data -> referrer_code;
                    $free_shipping_eligible = false;
                    $max_discount_amount = $coupon_data->max_discount_amount;
                    //$usage_limit_per_user = $coupon_data->usage_limit_per_user;

                    if(isset($coupon_data->usage_limit_per_user) && !empty($coupon_data->usage_limit_per_user)){
                        if($userId == null){
                            $this->session->set_flashdata(['error' => 1, 'message' => 'Please login to use this code']);
                            redirect('cart');
                        }else{
                            $code_usage_count = $this->shop_model->coupon_usage_count($userId, $coupon_code);
                            if($code_usage_count >= $coupon_data->usage_limit_per_user){
                                $this->session->set_flashdata(['error' => 1, 'message' => 'Coupon usage limit reached']);
                                redirect('cart');
                            }
                        }
                    }
                             
                    $applied_discount = 0;
                    $allowed_discount = 0;
                    if($coupon_data->discount_type == "percent"){
                        
                        
                        foreach ($cart_contents as $item => $val) {
                             
                            if(in_array($val['product_id'], $eligible_products)){
                                  
                                if($is_free_shipping && $referrer){
                                    $free_shipping_eligible = true;
                                    
                                }

                                $calculated_discount = ($val['price'] *$val['qty']* $coupon_data->amount) / 100;

                                if($max_discount_amount && ($applied_discount + $calculated_discount) > $max_discount_amount){
                                    $calculated_discount = $max_discount_amount - $applied_discount;
                                }

                                $data = [
                                    'rowid'  => $val['rowid'],
                                    'discount'  => $calculated_discount
                                ];

                                $applied_discount += $calculated_discount; 
                            }else{
                                   
                                 $data = [
                                    'rowid'  => $val['rowid'],
                                    'discount'  => 0
                                ];
                            } 
                            array_push($cart_arr, $data);
                        }
                       
                        $this->cart->update($cart_arr);
                        if($free_shipping_eligible){
                            $this->session->set_userdata('coupon_details', array(
                                'code' => $coupon_code,
                                'dis_amount' => $this->cart->get_total_discount(),
                                'dis_percent' => $coupon_data->amount,
                                'free_shipping' =>  true
                            ));
                            $this->session->set_flashdata('message', 'Free Shipping Eligible');
                            redirect('cart');
                        }else{
                                $this->session->set_userdata('coupon_details', array(
                                'code' => $coupon_code,
                                'dis_amount' => $this->cart->get_total_discount(),
                                'dis_percent' => $coupon_data->amount,
                                'free_shipping' => false
                            ));
                        }
                        
                        $this->session->set_flashdata('message', 'Coupon Code Applied');
                        redirect('cart');
                    }                    
                    
                }else{
                    $this->session->set_flashdata(['error' => 1, 'message' => 'Invalid Coupon Code']);
                //$this->sma->send_json(['error' => 1, 'message' => 'Invalid Coupon Code']);
                redirect('cart');
                }
                
            }else{
                $this->session->set_flashdata(['error' => 1, 'message' => 'Invalid Coupon Code']);
            //$this->sma->send_json(['error' => 1, 'message' => 'Invalid Coupon Code']);
            redirect('cart');
            }
            
        }
    }

    public function add($product_id)
    {
        if ($this->input->is_ajax_request() || $this->input->post('quantity')) {
            $this->load->admin_model('inventory_model');
            $product = $this->shop_model->getProductForCart($product_id);
            $product_quantity_onhold =  $this->shop_model->getProductOnholdQty($product_id);
            //$quantity_in_stock =  intval($product->quantity) - $product_quantity_onhold;
            $new_stock = $this->inventory_model->get_current_stock($product_id, 'null');
            $quantity_in_stock =  intval($new_stock) - $product_quantity_onhold;

            $product_to_add_quantity = 0;
            $cart_contents = $this->cart->contents();
            foreach ($cart_contents as $item) {

                if($product->code == $item['code']){
                    $product_to_add_quantity += $item['qty'];
                }
            }

            $quantity_added = $this->input->get('qty') + $product_to_add_quantity;

            if($quantity_added > 3 && $product->code != '06285193000301'){
                $this->sma->send_json(['error' => 1, 'message' => 'Maximum allowed order 3 pieces']);
                //return false;
            }

            $options = $this->shop_model->getProductVariants($product_id);
            $price   = $this->sma->setCustomerGroupPrice((isset($product->special_price) && !empty($product->special_price) ? $product->special_price : $product->price), $this->customer_group);
            $price   = $this->sma->isPromo($product) ? $product->promo_price : $price;
            $option  = false;
            if (!empty($options)) {
                if ($this->input->post('option')) {
                    foreach ($options as $op) {
                        if ($op['id'] == $this->input->post('option')) {
                            $option = $op;
                        }
                    }
                } else {
                    $option = array_values($options)[0];
                }
                $price = $option['price'] + $price;
            }
            $selected = $option ? $option['id'] : false;
            if (!$this->Settings->overselling && $this->checkProductStock($product, $quantity_added, $selected)) {
                if ($this->input->is_ajax_request()) {
                    if($quantity_in_stock > 0){
                        $this->sma->send_json(['error' => 1, 'message' => lang('Only '.$quantity_in_stock.' pieces remaining')]);
                        //return false;
                    }else{
                        //$this->sma->send_json(['error' => 1, 'message' => 'hereblablalblabla']);
                       $this->sma->send_json(['error' => 1, 'message' => lang('item_out_of_stock')]);
                        //return false;
                    } 
                } else {
                    $this->session->set_flashdata('error', lang('item_out_of_stock'));
                    redirect($_SERVER['HTTP_REFERER']);
                }
            }
            $tax_rate   = $this->site->getTaxRateByID($product->tax_rate);
            $ctax       = $this->site->calculateTax($product, $tax_rate, $price);
            $tax        = $this->sma->formatDecimal($ctax['amount']);
            $price      = $this->sma->formatDecimal($price);
            $unit_price = $this->sma->formatDecimal($product->tax_method ? $price + $tax : $price);
            $id         = $this->Settings->item_addition ? md5($product->id) : md5(microtime());

            $sulfad_count = 0;
            $sulfad_in_cart = 0;
            $sulfad_code = '06285193000301';

            $other_product_count = 0;
            $other_product_in_cart = 0;

            if($product->code == '06285193000301'){
                $sulfad_in_cart += ($this->input->get('qty') ? $this->input->get('qty') : ($this->input->post('quantity') ? $this->input->post('quantity') : 1));
                
                $cart_contents = $this->cart->contents();
                foreach ($cart_contents as $item) {
                    $product_code = $item['code'];
                    if($product_code == $sulfad_code){
                        $sulfad_count += $item['qty'];
                        $this->cart->remove($item['rowid']);
                    }
                }
            }else{
                $other_product_in_cart += ($this->input->get('qty') ? $this->input->get('qty') : ($this->input->post('quantity') ? $this->input->post('quantity') : 1));

                $cart_contents = $this->cart->contents();
                foreach ($cart_contents as $item) {
                    $product_code = $item['code'];
                    if($product_code == $product->code){
                        $other_product_count += $item['qty'];
                        $this->cart->remove($item['rowid']);
                    }
                }
            }

            if($product->code == $sulfad_code){
                $total_sulfad = $sulfad_in_cart + $sulfad_count;
                //$discounted_quantity = floor($total_sulfad / 3);
                //$discounted_quantity = 0;

                $coupon_details = $this->session->userdata('coupon_details');
                if($coupon_details && isset($coupon_details['code']) && $coupon_details['code'] == 'fitness'){
                    $discounted_quantity = $total_sulfad;
                }else{
                    if($total_sulfad > 1){
                        $discount_amt = 75 * (floor($total_sulfad/ 2) );
                    }  

                    $discount_amt = 0;
                }

                if($coupon_details && isset($coupon_details['code']) && $coupon_details['code'] == 'fitness'){
                    $total_sulfad = $total_sulfad * 2;
                }

                $data = [
                    'id'         => $id,
                    'product_id' => $product->id,
                    'qty'        => $total_sulfad,
                    'disc_qty'   => $discounted_quantity,
                    'name'       => $product->name,
                    'slug'       => $product->slug,
                    'code'       => $product->code,
                    'price'      => $unit_price,
                    'tax'        => $tax,
                    'image'      => $product->image,
                    'option'     => $selected,
                    'options'    => !empty($options) ? $options : null,
                    'discount'   => $discount_amt,
                ];

            }else{
                $total_other_product = $other_product_in_cart + $other_product_count;

                $data = [
                    'id'         => $id,
                    'product_id' => $product->id,
                    //'qty'        => ($this->input->get('qty') ? $this->input->get('qty') : ($this->input->post('quantity') ? $this->input->post('quantity') : 1)),
                    'qty'        => $total_other_product,
                    'disc_qty'   => 0,
                    'name'       => $product->name,
                    'slug'       => $product->slug,
                    'code'       => $product->code,
                    'price'      => $unit_price,
                    'tax'        => $tax,
                    'image'      => $product->image,
                    'option'     => $selected,
                    'options'    => !empty($options) ? $options : null,
                    'discount'   => 0,
                ];

            }

            if ($this->cart->insert($data)) {
                if ($this->input->post('quantity')) {
                    $this->session->set_flashdata('message', lang('item_added_to_cart'));
                    redirect($_SERVER['HTTP_REFERER']);
                } else {
                    $this->cart->cart_data();
                }
            }
            $this->session->set_flashdata('error', lang('unable_to_add_item_to_cart'));
            redirect($_SERVER['HTTP_REFERER']);
            
        }
    }

    public function add_wishlist($product_id)
    {
        $this->session->set_userdata('requested_page', $_SERVER['HTTP_REFERER']);
        if (!$this->loggedIn) {
            $this->sma->send_json(['redirect' => site_url('login')]);
        }
        if ($this->shop_model->getWishlist(true) >= 10) {
            $this->sma->send_json(['status' => lang('warning'), 'message' => lang('max_wishlist'), 'level' => 'warning']);
        }
        if ($this->shop_model->addWishlist($product_id)) {
            $total = $this->shop_model->getWishlist(true);
            $this->sma->send_json(['status' => lang('success'), 'message' => lang('added_wishlist'), 'total' => $total]);
        } else {
            $this->sma->send_json(['status' => lang('info'), 'message' => lang('product_exists_in_wishlist'), 'level' => 'info']);
        }
    }


    public function get_countries() {
        $countries = $this->settings_model->getCountries();
        echo json_encode($countries);
        exit;
    }

    public function get_cities_by_country_id($id) {
        $this->data['cities'] = $this->settings_model->getCities($id);
        echo json_encode($this->data['cities']);
        exit;
    }
    public function checkout_old()
    {
        $this->session->set_userdata('requested_page', $this->uri->uri_string());
        if ($this->cart->total_items() < 1) {
            $this->session->set_flashdata('reminder', lang('cart_is_empty'));
            shop_redirect('products');
        }

        $action = $this->input->get('action');
        $this->data['addresses']  = $this->loggedIn ? $this->shop_model->getAddresses() : false;
        $this->data['defaultAddress']  = $this->loggedIn ? $this->shop_model->getDefaultChechoutAddress() : false;
        
        //get dafault address
        if($this->loggedIn && ($action == 'changeaddress' || empty($this->data['defaultAddress'])) ) {
        //if($action == 'changeaddress' || empty($this->data['addresses']))  {  
            $this->page_construct('pages/checkout_address', $this->data);    
        }
        else{

            $this->data['paypal']     = $this->shop_model->getPaypalSettings();
            $this->data['skrill']     = $this->shop_model->getSkrillSettings();
            $this->data['country'] = $this->settings_model->getallCountry();
            $this->data['countries'] = $this->settings_model->getCountries();
    
    
    //        $this->data['cities'] = $this->settings_model->getCities();
    //
    //        dd($this->data['cities']);
            $this->data['page_title'] = lang('checkout');
            $this->data['all_categories']    = $this->shop_model->getAllCategories();
            $this->page_construct('pages/checkout', $this->data);

        }

    }

    // ----------test page
    public function checkout()
    {
        $this->session->set_userdata('requested_page', $this->uri->uri_string());
        if ($this->cart->total_items() < 1) {
            $this->session->set_flashdata('reminder', lang('cart_is_empty'));
             shop_redirect('products');
        }
        $this->data['address_id'] = '';
        $action = $this->input->get('action');

        
        $this->data['default_address']  = $this->loggedIn ? $this->shop_model->getDefaultChechoutAddress() : false;
        $this->data['addresses']  = $this->loggedIn ? $this->shop_model->getAddresses() : false;  
       // print_r($this->data['default_address']);exit;
        if($this->loggedIn && (  $this->data['default_address']->phone == '' || $this->data['default_address']->address == ''|| in_array($action, array('addnewaddress', 'editaddress')) ) ) {
        //if($action == 'changeaddress' || empty($this->data['addresses']))  {  
            // for edit address
            if($action == 'editaddress') {
                $address_id =   $this->input->get('id');
                if($address_id == 'default' || is_numeric($address_id)) {
                    $this->data['selected_address_info']  = $this->loggedIn ? $this->shop_model->getDefaultChechoutAddress($address_id) : false;
                    $this->data['address_id'] = $address_id;
                    if(empty($this->data['selected_address_info'])) {
                        redirect('cart/checkout') ;
                    }
                }
            }    
            //get customer verified numbers
            $verify_phone_numbers = $this->shop_model->getCustomerVerifiedNumbers();
            $this->data['verify_phone_numbers'] = $verify_phone_numbers;
            $this->data['action_type'] = $action;
            $this->page_construct('pages/checkout_address', $this->data);    
        }
        else if( $this->loggedIn && $action == 'changeaddress' ) {
            
            $this->page_construct('pages/delivery_address', $this->data);    
        }
        else{

            $referrer = $this->input->server('HTTP_REFERER');
            $urlComponents = parse_url($referrer);
      
        // Check if the query string is present
        if (isset($urlComponents['query'])) {
            // Parse the query string to get individual parameters
            parse_str($urlComponents['query'], $queryParams);
         
            // Check if the 'action' parameter is present
            if (isset($queryParams['action']) &&  trim($queryParams['action']) == 'changeaddress') {
               // $this->data['defaultAddress']  = $this->loggedIn ? $this->shop_model->getDefaultChechoutAddress($lastAddress = true) : false;
            }
        } 
         
           //$this->data['defaultAddress']  = $this->loggedIn ? $this->shop_model->getDefaultChechoutAddress() : false;

           if($this->session->get_userdata('changed_address')['changed_address']){
                $this->data['default_address']  = $this->session->get_userdata('changed_address')['changed_address'];
                //$this->data['default_address']  = $this->loggedIn ? $this->shop_model->getDefaultChechoutAddress() : false;
            }else{
                $this->data['default_address']  = $this->loggedIn ? $this->shop_model->getDefaultChechoutAddress() : false;
            }
            
          
            $this->data['paypal']     = $this->shop_model->getPaypalSettings();
            $this->data['skrill']     = $this->shop_model->getSkrillSettings();
            $this->data['country'] = $this->settings_model->getallCountry();
            $this->data['countries'] = $this->settings_model->getCountries();
    
    
    //        $this->data['cities'] = $this->settings_model->getCities();
    //
    //        dd($this->data['cities']);

            $virtual_pharmacy_items = 0;
            $cart_contents = $this->cart->contents();
            foreach ($cart_contents as $item) {
                $product_id = $item['product_id'];
                $warehouse_quantities = $this->shop_model->getProductQuantitiesInWarehouses($product_id);
                foreach ($warehouse_quantities as $wh_quantity){
                    // remove 6 and 1 after eid
                    if($wh_quantity->warehouse_id == '7' ){
                        $virtual_pharmacy_items += $wh_quantity->quantity;
                    }
                }
            }

            $this->data['page_title'] = lang('checkout');
            $this->data['all_categories']    = $this->shop_model->getAllCategories();
            $this->data['virtual_pharmacy_items'] = $virtual_pharmacy_items;
            $this->page_construct('pages/checkout-html', $this->data);

        }

    }
    // ------end


    public function destroy()
    {
        if ($this->input->is_ajax_request()) {
            if ($this->cart->destroy()) {
                $this->session->set_flashdata('message', lang('cart_items_deleted'));
                $this->sma->send_json(['redirect' => base_url()]);
            } else {
                $this->sma->send_json(['status' => lang('error'), 'message' => lang('error_occured')]);
            }
        }
    }

    public function index()
    {
        $this->session->set_userdata('requested_page', $this->uri->uri_string());
        if ($this->cart->total_items() < 1) {
            $this->session->set_flashdata('reminder', lang('cart_is_empty'));
            shop_redirect('products');
        }
        $this->data['page_title'] = lang('shopping_cart');
        $this->data['all_categories']    = $this->shop_model->getAllCategories();
        $this->page_construct('pages/cart', $this->data);
    }

    public function remove_wishlist($product_id)
    {
        $this->session->set_userdata('requested_page', $_SERVER['HTTP_REFERER']);
        if (!$this->loggedIn) {
            $this->sma->send_json(['redirect' => site_url('login')]);
        }
        if ($this->shop_model->removeWishlist($product_id)) {
            $total = $this->shop_model->getWishlist(true);
            $this->sma->send_json(['status' => lang('success'), 'message' => lang('removed_wishlist'), 'total' => $total]);
        } else {
            $this->sma->send_json(['status' => lang('error'), 'message' => lang('error_occured'), 'level' => 'error']);
        }
    }

    public function update($data = null)
    {

        $this->remove_coupon_code();
        if (is_array($data)) {
            return $this->cart->update($data);
        }
        if ($this->input->is_ajax_request()) {
            if ($rowid = $this->input->post('rowid', true)) {
                $item = $this->cart->get_item($rowid);
                // $product = $this->site->getProductByID($item['product_id']);
                $product = $this->shop_model->getProductForCart($item['product_id']);
                $options = $this->shop_model->getProductVariants($product->id);
                $price   = $this->sma->setCustomerGroupPrice(($product->special_price ?? $product->price), $this->customer_group);
                $price   = $this->sma->isPromo($product) ? $product->promo_price : $price;
                // $price = $this->sma->isPromo($product) ? $product->promo_price : $product->price;

                if($this->input->post('qty', true) > 3 && $product->code != '06285193000301'){
                    $this->sma->send_json(['error' => 1, 'message' => 'Maximum allowed order 3 pieces']);
                }

                if ($option = $this->input->post('option')) {
                    foreach ($options as $op) {
                        if ($op['id'] == $option) {
                            $price = $price + $op['price'];
                        }
                    }
                }
                $selected = $this->input->post('option') ? $this->input->post('option', true) : false;
                if ($this->checkProductStock($product, $this->input->post('qty', true), $selected)) {
                    if ($this->input->is_ajax_request()) {
                        $this->sma->send_json(['error' => 1, 'message' => lang('item_stock_is_less_then_order_qty')]);
                    } else {
                        $this->session->set_flashdata('error', lang('item_stock_is_less_then_order_qty'));
                        redirect($_SERVER['HTTP_REFERER']);
                    }
                }

                $tax_rate   = $this->site->getTaxRateByID($product->tax_rate);
                $ctax       = $this->site->calculateTax($product, $tax_rate, $price);
                $tax        = $this->sma->formatDecimal($ctax['amount']);
                $price      = $this->sma->formatDecimal($price);
                $unit_price = $this->sma->formatDecimal($product->tax_method ? $price + $tax : $price);

                /* Sulfad Code For Update Starts */
                $sulfad_code = '06285193000301';
                $sulfad_new_quantity = $this->input->post('qty', true);

                if($product->code == $sulfad_code){
                    //$discounted_quantity = floor($sulfad_new_quantity / 3);
                    $discounted_quantity = 0;
                    if($sulfad_new_quantity > 1){
                        //$discount_amt = 75 * (floor($sulfad_new_quantity/ 2) );
                        $discount_amt = 0;
                    }else{
                        $discount_amt = 0;
                    }

                    $data = [
                        'rowid'  => $rowid,
                        'price'  => $unit_price,
                        'tax'    => $tax,
                        'qty'    => $this->input->post('qty', true),
                        'disc_qty'   => $discounted_quantity,
                        'option' => $selected,
                        'discount' => $discount_amt
                    ];
                    if ($this->cart->update($data)) {
                        $this->sma->send_json(['cart' => $this->cart->cart_data(true), 'status' => lang('success'), 'message' => lang('cart_updated')]);
                    }
                }else{
                    $data = [
                        'rowid'  => $rowid,
                        'price'  => $unit_price,
                        'tax'    => $tax,
                        'qty'    => $this->input->post('qty', true),
                        'option' => $selected,
                    ];
                    if ($this->cart->update($data)) {
                        $this->sma->send_json(['cart' => $this->cart->cart_data(true), 'status' => lang('success'), 'message' => lang('cart_updated')]);
                    }
                }

                /* Sulfad Code For Update Ends */
            }
        }
    }

    private function checkProductStock($product, $qty, $option_id = null)
    {
        if ($product->type == 'service' || $product->type == 'digital') {
            return false;
        }
       
        $this->load->admin_model('inventory_model');

        $chcek = [];
        if ($product->type == 'standard') {
            $quantity = 0;
            /* GET QUANTITY FROM PRODUCT */
            // if ($pis = $this->site->getPurchasedItems($product->id, $this->shop_settings->warehouse, $option_id)) {
            //     foreach ($pis as $pi) {
            //         $quantity += $pi->quantity_balance;
            //     }
            // }
            $product_quantity =  $this->shop_model->getProductOnholdQty($product->id);
            $new_stock = $this->inventory_model->get_current_stock($product->id, 'null');
            //$quantity =  intval($product->quantity) - $product_quantity;
            $quantity =  intval($new_stock) - $product_quantity;
           //echo $quantity;exit;
            $chcek[] = ($qty <= $quantity);
        } elseif ($product->type == 'combo') {
            $combo_items = $this->site->getProductComboItems($product->id, $this->shop_settings->warehouse);
            foreach ($combo_items as $combo_item) {
                if ($combo_item->type == 'standard') {
                    $quantity = 0;
                    // if ($pis = $this->site->getPurchasedItems($combo_item->id, $this->shop_settings->warehouse, $option_id)) {
                    //     foreach ($pis as $pi) {
                    //         $quantity += $pi->quantity_balance;
                    //     }
                    // }
                    $product_quantity =  $this->shop_model->getProductOnholdQty($product->id);
                    $new_stock = $this->inventory_model->get_current_stock($product->id, 'null');
                    //$quantity =  intval($product->quantity) - $product_quantity;
                    $quantity =  intval($new_stock) - $product_quantity;
                    $chcek[] = (($combo_item->qty * $qty) <= $quantity);
                }
            }
        }
      
        return empty($chcek) || in_array(false, $chcek);
    }
}
