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
        $this->session->unset_userdata('coupon_details');
        if ($rowid) {
            return $this->cart->remove($rowid);
        }
        if ($this->input->is_ajax_request()) {
            if ($rowid = $this->input->post('rowid', true)) {
                if ($this->cart->remove($rowid)) {
                    $this->sma->send_json(['cart' => $this->cart->cart_data(true), 'status' => lang('success'), 'message' => lang('cart_item_deleted')]);
                }
            }
        }
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
                array_push($cart_arr, $data);
            }

            $this->cart->update($cart_arr);

            $this->session->unset_userdata('coupon_details');
            //echo json_encode(array('status' => 'success', 'action' => 'subtract', 'total' => $this->cart->total(), 'discount' => 0));
            $this->session->set_flashdata('success', 'Coupon Code Removed');
            redirect('cart');
        }
    }

    public function apply_coupon(){
        $coupon_arr = array('mpay' => 10, 'alf10' => 10, 'mc24' => 10, 'neqaty10' => 10, 'enbd24' => 10, 'anb10' => 10, 'eid10' => 10);
        $coupon_cap_arr = array('mpay' => 100, 'alf10' => 50, 'mc24' => 50, 'neqaty10' => 50, 'enbd24' => 50, 'anb10' => 50, 'eid10' => 50);
        $pattern_match = 0;

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

        }else{
            $this->session->set_flashdata('error', 'Invalid Coupon Code');
            redirect('cart');
        }
    }

    public function add($product_id)
    {
        if ($this->input->is_ajax_request() || $this->input->post('quantity')) {
            
            $product = $this->shop_model->getProductForCart($product_id);
            $product_quantity_onhold =  $this->shop_model->getProductOnholdQty($product_id);
            $quantity_in_stock =  intval($product->quantity) - $product_quantity_onhold;

            $product_to_add_quantity = 0;
            $cart_contents = $this->cart->contents();
            foreach ($cart_contents as $item) {

                if($product->code == $item['code']){
                    $product_to_add_quantity += $item['qty'];
                }
            }

            $quantity_added = $this->input->get('qty') + $product_to_add_quantity;

            if($quantity_added > 3){
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
                $discounted_quantity = floor($total_sulfad / 3);

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
                    'discount'   => 0,
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
                    if($wh_quantity->warehouse_id == '6' || $wh_quantity->warehouse_id == '7'){
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

                if($this->input->post('qty', true) > 3){
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
                    $discounted_quantity = floor($sulfad_new_quantity / 3);

                    $data = [
                        'rowid'  => $rowid,
                        'price'  => $unit_price,
                        'tax'    => $tax,
                        'qty'    => $this->input->post('qty', true),
                        'disc_qty'   => $discounted_quantity,
                        'option' => $selected,
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
            $quantity =  intval($product->quantity) - $product_quantity;
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
                    $quantity =  intval($product->quantity) - $product_quantity;
                    $chcek[] = (($combo_item->qty * $qty) <= $quantity);
                }
            }
        }
      
        return empty($chcek) || in_array(false, $chcek);
    }
}
