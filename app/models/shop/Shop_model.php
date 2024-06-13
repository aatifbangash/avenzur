<?php

use JetBrains\PhpStorm\Internal\ReturnTypeContract;

defined('BASEPATH') or exit('No direct script access allowed');

class Shop_model extends CI_Model
{
    protected $table = 'products';

    public function __construct()
    {
        parent::__construct();
    }


    public function get_count()
    {
        return $this->db->count_all($this->table);
    }

    public function get_authors($limit, $start)
    {
        $this->db->limit($limit, $start);
        $query = $this->db->get($this->table);

        return $query->result();
    }

    public function addOTPData($data)
    {
        $uniqueColumns = array('medium' => $data['medium'], 'userid' => $data['userid'], 'identifier' => $data['identifier']);

        $query = $this->db->get_where('customer_otp', ['medium' => $data['medium'], 'identifier' => $data['identifier'], 'userid' => $data['userid']]);

        if ($query->num_rows() > 0) {
            $this->db->where($uniqueColumns);
            $this->db->update('customer_otp', $data);

            $this->db->where($uniqueColumns);
            $query = $this->db->get('customer_otp');
            $result = $query->result_array();
            $updated_id = $result[0]['id'];

            return $updated_id;
        } else {
            $this->db->insert('customer_otp', $data);
        }

        return ($this->db->affected_rows() > 0) ? $this->db->insert_id() : false;
    }

    public function activate_user($email)
    {
        $valid_email = filter_var($email, FILTER_VALIDATE_EMAIL) ;
        $identity_column = ($valid_email) ? 'email' : 'username';
        return $this->db->update('users', ['active' => 1], [$identity_column => $email]);
    }

    public function verify_success_mobile($company_id)
    {
        return $this->db->update('companies', ['mobile_verified' => 1], ['id' => $company_id]);
    }

    public function get_company_details($address_id){
        return $this->db->get_where('companies', ['id' => $address_id], 1)->row();
    }

    public function get_activate_phone($company_id, $mobile, $address_id){
        return $this->db->get_where('addresses', ['id' => $address_id, 'company_id' => $company_id, 'phone' => $mobile], 1)->row();
    }

    public function activate_phone($company_id, $mobile, $address_id){
        return $this->db->update('addresses', ['mobile_verified' => 1], ['id' => $address_id, 'company_id' => $company_id, 'phone' => $mobile]);
    }

    public function validate_otp($identifer, $otp)
    {
        $uniqueColumns = array('identifier' => $identifer, 'otp' => $otp);
        $this->db->where($uniqueColumns);
        $query = $this->db->get('customer_otp');
        $result = $query->result_array();
        if ($result) {
            return true;
        } else {
            return false;
        }
    }

    public function getUniqueCustomer($type, $identifier)
    {
        if ($type == 'email') {
            return $this->db->get_where('companies', ['email' => $identifier], 1)->row();
        } else {
            return $this->db->get_where('companies', ['phone' => $identifier], 1)->row();
        }

    }

    public function addUniqueCustomer($data)
    {
        $uniqueColumns = array('email');
        $query = $this->db->get_where('companies', ['email' => $data['email']]);

        if ($query->num_rows() > 0) {
            $row = $this->db->get_where('companies', ['email' => $data['email']], 1)->row();
            return $row->id;
        } else {
            $this->db->insert('companies', $data);
            return ($this->db->affected_rows() > 0) ? $this->db->insert_id() : false;
        }
    }

    public function addCustomer($data)
    {
        if ($this->db->insert('companies', $data)) {
            return $this->db->insert_id();
        }
        return false;
    }

    public function addContactUsRecord($data)
    {
        if ($this->db->insert('contact_us', $data)) {
            return $this->db->insert_id();
        }
        return false;
    }

    public function addAramexShippment($data)
    {
        $this->db->insert('aramex_shippment', $data);

    }

    public function addSale($data, $items, $customer, $address)
    {

        // Sequence-Code
        $this->load->library('SequenceCode');
        $this->sequenceCode = new SequenceCode();
        $data['sequence_code'] = $this->sequenceCode->generate('SL', 5);

       // $cost = $this->site->costing($items);
       // $this->sma->print_arrays($cost);

        if (is_array($customer) && !empty($customer)) {
            $this->db->insert('companies', $customer);
            $data['customer_id'] = $this->db->insert_id();
        }

        if (is_array($address) && !empty($address)) {
            $address['company_id'] = $data['customer_id'];
            $this->db->insert('addresses', $address);
            $data['address_id'] = $this->db->insert_id();
        }
    
        $this->db->trans_start();
        if ($this->db->insert('sales', $data)) {
            $sale_id = $this->db->insert_id();
            $this->site->updateReference('so');

            foreach ($items as $item) {
                $item['sale_id'] = $sale_id;
                $this->db->insert('sale_items', $item);
                $sale_item_id = $this->db->insert_id();
                if ($data['sale_status'] == 'completed') {
                    $item_costs = $this->site->item_costing($item);
                    foreach ($item_costs as $item_cost) {
                        if (isset($item_cost['date']) || isset($item_cost['pi_overselling'])) {
                            $item_cost['sale_item_id'] = $sale_item_id;
                            $item_cost['sale_id'] = $sale_id;
                            $item_cost['date'] = date('Y-m-d', strtotime($data['date']));
                            if (!isset($item_cost['pi_overselling'])) {
                                $this->db->insert('costing', $item_cost);
                            }
                        } else {
                            foreach ($item_cost as $ic) {
                                $ic['sale_item_id'] = $sale_item_id;
                                $ic['sale_id'] = $sale_id;
                                $ic['date'] = date('Y-m-d', strtotime($data['date']));
                                if (!isset($ic['pi_overselling'])) {
                                    $this->db->insert('costing', $ic);
                                }
                            }
                        }
                    }
                }
            }

            // $this->site->syncQuantity($sale_id);
            $this->sma->update_award_points($data['grand_total'], $data['customer_id'], $data['created_by']);
            // return $sale_id;
        }
        $this->db->trans_complete();
        if ($this->db->trans_status() === false) {
            log_message('error', 'An errors has been occurred while adding the sale (Shop_model.php)');
        } else {
            return $sale_id;
        }

        return false;
    }

    public function addWishlist($product_id)
    {
        $user_id = $this->session->userdata('user_id');
        if (!$this->getWishlistItem($product_id, $user_id)) {
            return $this->db->insert('wishlist', ['product_id' => $product_id, 'user_id' => $user_id]);
        }
        return false;
    }

    public function getAddressByID($id)
    {
        if($id == 'default' || $id == 0) {
            $address_row = $this->db->get_where('companies', ['id' => $this->session->userdata('company_id')])->row();
            if (isset($address_row->address)) {
                $address_row->line1 = $address_row->address;
                unset($address_row->address);
            }
            return $address_row;
        }
        return $this->db->get_where('addresses', ['id' => $id], 1)->row();
    }

    public function getAddresses()
    {
        return $this->db->get_where('addresses', ['company_id' => $this->session->userdata('company_id')])->result();
    }

    public function getDefaultChechoutAddress($address_id = null)
    {
        if ($address_id != 'default' && is_numeric($address_id)) {
           return $this->db->get_where('addresses', ['id' => $address_id, 'company_id' => $this->session->userdata('company_id')])->row();
        }
        return $this->db->get_where('companies', ['id' => $this->session->userdata('company_id')])->row();
    }

    public function getCustomerVerifiedNumbers() {
       $verify_address =  $this->db->get_where('addresses', ['company_id' => $this->session->userdata('company_id'), 'mobile_verified' => 1])->result();
       $verify_row     = $this->db->get_where('companies', ['id' => $this->session->userdata('company_id'), 'mobile_verified' => 1])->row();
       $verify_phone_numbers = array() ;
       if(count($verify_address) > 0) { 
        foreach ($verify_address as $key => $value) {
            $verify_phone_numbers[] = $value->phone;
         }
       }
       if(!empty($verify_row)) {
            $verify_phone_numbers[] = $verify_row->phone;
       }
       return $verify_phone_numbers;
    }

    public function getAllBrands($data)
    {
        $category_slug = isset($data['category_slug']) ? $data['category_slug'] : null;
        $promo = isset($data['filters']['promo']) ? $data['filters']['promo'] : null;

        if ($this->shop_settings->hide0) {
            $pc = "(SELECT count(*) FROM {$this->db->dbprefix('products')} WHERE {$this->db->dbprefix('products')}.brand = {$this->db->dbprefix('brands')}.id)";
            $this->db->select("{$this->db->dbprefix('brands')}.*, {$pc} AS product_count", false)->order_by('name');
            if ($category_slug) {
                $this->db->join('sma_categories', 'sma_categories.id = sma_products.category_id', 'left')
                         ->where('sma_categories.slug', $category_slug);
            }
            $this->db->having('product_count >', 0);
        }
        if ($category_slug  || $promo) {
            // Start the query to select brands and their product counts
            $pc = "(SELECT count(*) FROM {$this->db->dbprefix('products')} AS p1 WHERE p1.brand = {$this->db->dbprefix('brands')}.id)";
            $this->db->select("{$this->db->dbprefix('brands')}.*, {$pc} AS product_count", false)
                    ->from('brands')->order_by('brands.name')
                    ->group_by('brands.id');

            // Ensure that each brand appears only once
            $this->db->join("{$this->db->dbprefix('products')} AS p2", 'p2.brand = brands.id', 'left');

            // If category_slug is provided, join with products and categories tables and filter by category slug
            if ($category_slug) {
                $this->db->join("{$this->db->dbprefix('products')} AS p3", 'p3.brand = brands.id', 'left')
                        ->join('categories', 'categories.id = p3.category_id', 'left')
                        ->where('categories.slug', $category_slug);
            }

            if ($promo) {
                $this->db->where("p2.promotion", 1); // Adjusted this line to reference the correct alias
            }

            // Execute the query
            return $this->db->get()->result();
        }
        return $this->db->get('brands')->result();
    }

    public function getAllCategories()
    {
        // if ($this->shop_settings->hide0) {
        //     $pc = "(SELECT count(*) FROM {$this->db->dbprefix('products')} WHERE {$this->db->dbprefix('products')}.category_id = {$this->db->dbprefix('categories')}.id)";
        //     $this->db->select("{$this->db->dbprefix('categories')}.*, {$pc} AS product_count", false);
        //     $this->db->having('product_count >', 0);
        // }
        // //$this->db->where('categories.id !=', 29);
        // $this->db->where_not_in('categories.id', array(29, 32));
        // $this->db->group_start()->where('parent_id', null)->or_where('parent_id', 0)->group_end()->order_by('name');
        // $categories = $this->db->get('categories')->result();

        // foreach ($categories as $category) {
        //     $category->name = ucfirst(strtolower($category->name));
        // }

        // return $categories;
        
        // new code.
        if ($this->shop_settings->hide0) {
            $pc = "(SELECT count(*) FROM {$this->db->dbprefix('products')} WHERE {$this->db->dbprefix('products')}.category_id = {$this->db->dbprefix('categories')}.id)";
            $this->db->select("{$this->db->dbprefix('categories')}.*, {$pc} AS product_count", false);
            $this->db->having('product_count >', 0);
        }
        $this->db->where_not_in('categories.id', array(29, 32));
        $this->db->order_by('name');
        $categories = $this->db->get('categories')->result();
        // $categories = $this->buildCategoryHierarchy($categories);
        // echo "<pre>"; var_dump($categories); exit;
        return $categories;
    }

    function buildCategoryHierarchy($categories, $parent_id = null) {
        $result = array();
        foreach ($categories as $category) {
            // var_dump($category); exit;

            if ($category->parent_id == $parent_id || ($parent_id === null || $category->parent_id === 0)) {
                $category->name = ucfirst(strtolower($category->name));
                $category->children = $this->buildCategoryHierarchy($categories, $category->id);
                $result[] = $category;
            }
        }
        return $result;
    }

    public function getAllCurrencies()
    {
        return $this->db->get('currencies')->result();
    }

    public function getAllPages()
    {
        $this->db->select('name, slug')->order_by('order_no asc');
        return $this->db->get_where('pages', ['active' => 1])->result();
    }

    public function getProductQuantitiesInWarehouses($product_id){
        $query = $this->db->select('warehouses_products.*')
            ->from('warehouses_products')
            ->where('warehouses_products.product_id', $product_id);
        /*$query = $this->db->select('location_id as warehouse_id, SUM(quantity) as total_quantity')
            ->from('inventory_movements')
            ->where('product_id', $product_id)
            ->group_by('location_id');*/
        $result = $query->get();

        if ($result) {
            return $resultArray = $result->result();
        } else {
            // Handle the error, for example, show an error message
            return false;
        }
    }

    public function getAllWarehouseWithPQ($product_id, $warehouse_id = null)
    {
        if (!$warehouse_id) {
            $warehouse_id = $this->shop_settings->warehouse;
        }
        $this->db->select('' . $this->db->dbprefix('warehouses') . '.*, SUM(' . $this->db->dbprefix('warehouses_products') . '.quantity) As quantity, ' . $this->db->dbprefix('warehouses_products') . '.rack')
            ->join('warehouses_products', 'warehouses_products.warehouse_id=warehouses.id', 'left')
            ->where('warehouses_products.product_id', $product_id)
            ->where('warehouses_products.warehouse_id', $warehouse_id)
            ->group_by('warehouses_products.warehouse_id');
        return $this->db->get('warehouses')->row();
    }

    public function getBrandBySlug($slug)
    {
        return $this->db->get_where('brands', ['slug' => $slug], 1)->row();
    }

    public function getCategoryBySlug($slug)
    {
        return $this->db->get_where('categories', ['slug' => $slug], 1)->row();
    }

    public function getCompanyByEmail($email)
    {
        $q = $this->db->get_where('companies', ['email' => $email], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getCompanyByID($id)
    {
        return $this->db->get_where('companies', ['id' => $id])->row();
    }

    public function getCountryByCode($code)
    {
        return $this->db->get_where('countries', ['code' => trim($code)], 1)->row();
    }

    public function getCurrencyByCode($code)
    {
        return $this->db->get_where('currencies', ['code' => $code], 1)->row();
    }

    public function getCustomerGroup($id)
    {
        return $this->db->get_where('customer_groups', ['id' => $id])->row();
    }

    public function getDateFormat($id)
    {
        return $this->db->get_where('date_format', ['id' => $id], 1)->row();
    }

    public function getDownloads($limit, $offset, $product_id = null)
    {
        if ($this->loggedIn) {
            $this->db->select("{$this->db->dbprefix('sale_items')}.product_id, {$this->db->dbprefix('sale_items')}.product_code, {$this->db->dbprefix('sale_items')}.product_name, {$this->db->dbprefix('sale_items')}.product_type")
                ->distinct()
                ->join('sale_items', 'sales.id=sale_items.sale_id', 'left')
                ->where('sales.sale_status', 'completed')->where('sales.payment_status', 'paid')
                ->where('sales.customer_id', $this->session->userdata('company_id'))
                ->where('sale_items.product_type', 'digital')
                ->order_by('sales.id', 'desc')->limit($limit, $offset);
            if ($product_id) {
                $this->db->where('sale_items.product_id', $product_id);
            }
            return $this->db->get('sales')->result();
        }
        return false;
    }

    public function getDownloadsCount()
    {
        $this->db->select("{$this->db->dbprefix('sale_items')}.product_id, {$this->db->dbprefix('sale_items')}.product_code, {$this->db->dbprefix('sale_items')}.product_name, {$this->db->dbprefix('sale_items')}.product_type")
            ->distinct()
            ->join('sale_items', 'sales.id=sale_items.sale_id', 'left')
            ->where('sales.sale_status', 'completed')->where('sales.payment_status', 'paid')
            ->where('sales.customer_id', $this->session->userdata('company_id'))
            ->where('sale_items.product_type', 'digital');
        return $this->db->count_all_results('sales');
    }

    public function getFeaturedCategories($limit = 6, $promo = true)
    {

        $this->db->select("{$this->db->dbprefix('categories')}.id as id, {$this->db->dbprefix('categories')}.name as name, {$this->db->dbprefix('categories')}.code as code, {$this->db->dbprefix('categories')}.image as image, {$this->db->dbprefix('categories')}.slug as slug")
            ->limit($limit);
        $categories = $this->db->get('categories')->result();

        foreach ($categories as $category) {
            $category->name = ucfirst(strtolower($category->name));
        }

        return $categories;
    }

    public function getCustomersAlsoBought($product_id, $limit = 16){
        $query = $this->db->query("
            SELECT mapped.product_id, mapped.id, products.code, products.name
            FROM sma_sale_items main
            JOIN sma_sale_items mapped ON main.sale_id = mapped.sale_id
            JOIN sma_sales sales ON main.sale_id = sales.id
            JOIN sma_products products ON products.id = mapped.product_id
            WHERE main.product_id != mapped.product_id
            AND main.product_id = {$product_id}
            AND sales.shop = 1
            ORDER BY mapped.id DESC
            LIMIT 8
        ");

        $result = $query->result_array();

        // Extracting product IDs
        $similarProductIds = array_map(function($sale) {
            return $sale['product_id'];
        }, $result);

        if($similarProductIds){
            $this->db->select("
            {$this->db->dbprefix('products')}.id as id, 
            {$this->db->dbprefix('products')}.name as name, 
            {$this->db->dbprefix('products')}.code as code, 
            {$this->db->dbprefix('products')}.image as image, 
            {$this->db->dbprefix('products')}.slug as slug, 
            {$this->db->dbprefix('products')}.price, 
            {$this->db->dbprefix('products')}.quantity,
            (COALESCE(SUM(sma_im.quantity), 0) - COALESCE(SUM(sma_phqor.quantity), 0)) as product_quantity,
            {$this->db->dbprefix('products')}.type, 
            {$this->db->dbprefix('products')}.tax_rate as taxRateId, 
            {$this->db->dbprefix('products')}.tax_method,
            promotion, 
            promo_price, 
            start_date, 
            end_date, 
            b.name as brand_name, 
            b.slug as brand_slug, 
            c.name as category_name, 
            c.slug as category_slug,
            t.name as taxName,
            t.rate as taxPercentage,
            t.code as taxCode,
            CAST(ROUND(AVG(pr.rating), 1) AS UNSIGNED) as avg_rating")
                ->join('tax_rates t', 'products.tax_rate = t.id', 'left')
                ->join('brands b', 'products.brand=b.id', 'left')
                ->join('categories c', 'products.category_id=c.id', 'left')
                ->join('product_reviews pr', 'products.id=pr.product_id', 'left')
                ->join('(SELECT product_id, SUM(quantity) AS quantity FROM sma_inventory_movements GROUP BY product_id) AS sma_im', 'products.id=sma_im.product_id', 'left')
                ->join('(SELECT product_id, SUM(quantity) AS quantity FROM sma_product_qty_onhold_request GROUP BY product_id) AS sma_phqor', 'products.id=sma_phqor.product_id', 'left')
                ->where('products.quantity >', 0)
                ->where_in('products.id', $similarProductIds)
                ->where('hide !=', 1)
                ->limit($limit);

            $sp = $this->getSpecialPrice();
            if ($sp->cgp) {
                $this->db->select('cgp.price as special_price', false)->join($sp->cgp, 'products.id=cgp.product_id', 'left');
            } elseif ($sp->wgp) {
                $this->db->select('wgp.price as special_price', false)->join($sp->wgp, 'products.id=wgp.product_id', 'left');
            }

            $this->db->group_by('products.id');
            $this->db->order_by('RAND()');
            $result = $this->db->get('products')->result();
            //        dd($result);
            array_map(function ($row) {
                if ($row->tax_method == '1' && $row->taxPercentage > 0) { // tax_method = 0 means inclusiveTax
                    $productTaxPercent = $row->taxPercentage;

                    if ($row->promotion == 1) {
                        $productPromoPrice = $row->promo_price;
                        $promoProductTaxAmount = $productPromoPrice * ($productTaxPercent / 100);
                        $row->promo_price = $productPromoPrice + $promoProductTaxAmount;
                    }

                    $productPrice = $row->price;
                    $productTaxAmount = $productPrice * ($productTaxPercent / 100);
                    $row->price = $productPrice + $productTaxAmount;
                }
            }, $result);
        }else{
            return [];
        }

        return $result;
    }

    public function getCustomerAlsoViewed($category_id, $limit = 16)
    {
        $this->db->select("
        {$this->db->dbprefix('products')}.id as id, 
        {$this->db->dbprefix('products')}.name as name, 
        {$this->db->dbprefix('products')}.code as code, 
        {$this->db->dbprefix('products')}.image as image, 
        {$this->db->dbprefix('products')}.slug as slug, 
        {$this->db->dbprefix('products')}.price, 
        {$this->db->dbprefix('products')}.quantity,
        (COALESCE(SUM(sma_im.quantity), 0) - COALESCE(SUM(sma_phqor.quantity), 0)) as product_quantity,
        {$this->db->dbprefix('products')}.type, 
        {$this->db->dbprefix('products')}.tax_rate as taxRateId, 
        {$this->db->dbprefix('products')}.tax_method,
        promotion, 
        promo_price, 
        start_date, 
        end_date, 
        b.name as brand_name, 
        b.slug as brand_slug, 
        c.name as category_name, 
        c.slug as category_slug,
        t.name as taxName,
        t.rate as taxPercentage,
        t.code as taxCode,
        CAST(ROUND(AVG(pr.rating), 1) AS UNSIGNED) as avg_rating")
            ->join('tax_rates t', 'products.tax_rate = t.id', 'left')
            ->join('brands b', 'products.brand=b.id', 'left')
            ->join('categories c', 'products.category_id=c.id', 'left')
            ->join('product_reviews pr', 'products.id=pr.product_id', 'left')
            ->join('(SELECT product_id, SUM(quantity) AS quantity FROM sma_inventory_movements GROUP BY product_id) AS sma_im', 'products.id=sma_im.product_id', 'left')
            ->join('(SELECT product_id, SUM(quantity) AS quantity FROM sma_product_qty_onhold_request GROUP BY product_id) AS sma_phqor', 'products.id=sma_phqor.product_id', 'left')
            ->where('products.quantity >', 0)
            ->where('products.views >', 0)
            ->where('hide !=', 1)
            ->where('products.category_id', $category_id)
            ->limit($limit);

        $sp = $this->getSpecialPrice();
        if ($sp->cgp) {
            $this->db->select('cgp.price as special_price', false)->join($sp->cgp, 'products.id=cgp.product_id', 'left');
        } elseif ($sp->wgp) {
            $this->db->select('wgp.price as special_price', false)->join($sp->wgp, 'products.id=wgp.product_id', 'left');
        }

        $this->db->group_by('products.id');
        $this->db->order_by('RAND()');
        $result = $this->db->get('products')->result();
        //        dd($result);
        array_map(function ($row) {
            if ($row->tax_method == '1' && $row->taxPercentage > 0) { // tax_method = 0 means inclusiveTax
                $productTaxPercent = $row->taxPercentage;

                if ($row->promotion == 1) {
                    $productPromoPrice = $row->promo_price;
                    $promoProductTaxAmount = $productPromoPrice * ($productTaxPercent / 100);
                    $row->promo_price = $productPromoPrice + $promoProductTaxAmount;
                }

                $productPrice = $row->price;
                $productTaxAmount = $productPrice * ($productTaxPercent / 100);
                $row->price = $productPrice + $productTaxAmount;
            }
        }, $result);
        return $result;
    }

    public function getSpecialOffers($limit = 16, $promo = true)
    {
        $countryId = get_cookie('shop_country', true); //$this->session->userdata('country');
        $this->db->select("
        {$this->db->dbprefix('products')}.id as id, 
        {$this->db->dbprefix('products')}.name as name, 
        {$this->db->dbprefix('products')}.code as code, 
        {$this->db->dbprefix('products')}.image as image, 
        {$this->db->dbprefix('products')}.slug as slug, 
        {$this->db->dbprefix('products')}.price, 
        {$this->db->dbprefix('products')}.quantity,
        (COALESCE(SUM(sma_im.quantity), 0) - COALESCE(SUM(sma_phqor.quantity), 0)) as product_quantity, 
        {$this->db->dbprefix('products')}.type, 
        {$this->db->dbprefix('products')}.tax_rate as taxRateId, 
        {$this->db->dbprefix('products')}.tax_method,
        promotion, 
        promo_price, 
        start_date, 
        end_date, 
        b.name as brand_name, 
        b.slug as brand_slug, 
        c.name as category_name, 
        c.slug as category_slug,
        t.name as taxName,
        t.rate as taxPercentage,
        t.code as taxCode,
        CAST(ROUND(AVG(pr.rating), 1) AS UNSIGNED) as avg_rating")
            ->join('tax_rates t', 'products.tax_rate = t.id', 'left')
            ->join('brands b', 'products.brand=b.id', 'left')
            ->join('categories c', 'products.category_id=c.id', 'left')
            ->join('product_reviews pr', 'products.id=pr.product_id', 'left')
            ->join('(SELECT product_id, SUM(quantity) AS quantity FROM sma_inventory_movements GROUP BY product_id) AS sma_im', 'products.id=sma_im.product_id', 'left')
            ->join('(SELECT product_id, SUM(quantity) AS quantity FROM sma_product_qty_onhold_request GROUP BY product_id) AS sma_phqor', 'products.id=sma_phqor.product_id', 'left')
            ->where('products.special_offer', 1)
            ->where('products.quantity >', 0)
            ->where('hide !=', 1)
            //->where('products.cf1', $countryId)
            ->limit($limit);

        /*if($countryId != '0')
        {
           $this->db->where('products.cf1', $countryId);
        }*/

        $sp = $this->getSpecialPrice();
        if ($sp->cgp) {
            $this->db->select('cgp.price as special_price', false)->join($sp->cgp, 'products.id=cgp.product_id', 'left');
        } elseif ($sp->wgp) {
            $this->db->select('wgp.price as special_price', false)->join($sp->wgp, 'products.id=wgp.product_id', 'left');
        }

        if ($promo) {
            $this->db->order_by('promotion desc');
        }
        $this->db->group_by('products.id');
        $this->db->order_by('RAND()');
        $result = $this->db->get('products')->result();
        //        dd($result);
        array_map(function ($row) {
            if ($row->tax_method == '1' && $row->taxPercentage > 0) { // tax_method = 0 means inclusiveTax
                $productTaxPercent = $row->taxPercentage;

                if ($row->promotion == 1) {
                    $productPromoPrice = $row->promo_price;
                    $promoProductTaxAmount = $productPromoPrice * ($productTaxPercent / 100);
                    $row->promo_price = $productPromoPrice + $promoProductTaxAmount;
                }

                $productPrice = $row->price;
                $productTaxAmount = $productPrice * ($productTaxPercent / 100);
                $row->price = $productPrice + $productTaxAmount;
            }

            $warehouse_quantities = $this->getProductQuantitiesInWarehouses($row->id);
            foreach ($warehouse_quantities as $wh_quantity){
                if(($wh_quantity->warehouse_id == '7' && $wh_quantity->quantity > 0)){
                    //$virtual_pharmacy_items += $wh_quantity->quantity;
                    $row->global = 1;
                }
            }
        }, $result);
        return $result;
    }

    public function getPopularCategories($limit = 6)
    {
        $this->db->select("{$this->db->dbprefix('categories')}.id as id, {$this->db->dbprefix('categories')}.name as name, {$this->db->dbprefix('categories')}.code as code, {$this->db->dbprefix('categories')}.image as image, {$this->db->dbprefix('categories')}.slug as slug")
            ->where('categories.popular', 1)
            ->order_by('name asc')
            ->limit($limit);
        $popular_categories = $this->db->get('categories')->result();

        foreach ($popular_categories as $category) {
            $this->db->select("
            {$this->db->dbprefix('products')}.id as id, 
            {$this->db->dbprefix('products')}.name as name, 
            {$this->db->dbprefix('products')}.code as code, 
            {$this->db->dbprefix('products')}.image as image, 
            {$this->db->dbprefix('products')}.slug as slug, 
            {$this->db->dbprefix('products')}.price, 
            {$this->db->dbprefix('products')}.tax_rate as taxRateId, 
            {$this->db->dbprefix('products')}.tax_method,
            {$this->db->dbprefix('products')}.quantity,
            (COALESCE(SUM(sma_im.quantity), 0) - COALESCE(SUM(sma_phqor.quantity), 0)) as product_quantity, 
            {$this->db->dbprefix('products')}.type, 
            promotion, 
            promo_price, 
            start_date, 
            t.name as taxName,
            t.rate as taxPercentage,
            t.code as taxCode,
            CAST(ROUND(AVG(pr.rating), 1) AS UNSIGNED) as avg_rating,
            end_date, b.name as brand_name, b.slug as brand_slug, c.name as category_name, c.slug as category_slug")
                ->join('tax_rates t', 'products.tax_rate = t.id', 'left')
                ->join('brands b', 'products.brand=b.id', 'left')
                ->join('product_reviews pr', 'products.id=pr.product_id', 'left')
                ->join('(SELECT product_id, SUM(quantity) AS quantity FROM sma_inventory_movements GROUP BY product_id) AS sma_im', 'products.id=sma_im.product_id', 'left')
                ->join('(SELECT product_id, SUM(quantity) AS quantity FROM sma_product_qty_onhold_request GROUP BY product_id) AS sma_phqor', 'products.id=sma_phqor.product_id', 'left')
                ->join('categories c', 'products.category_id=c.id', 'left')
                ->where('products.category_id', $category->id)
                ->where('products.quantity >', 0)
                ->where('hide !=', 1)
                //->where('products.cf1', $countryId)
                ->limit(8);

            $sp = $this->getSpecialPrice();
            if ($sp->cgp) {
                $this->db->select('cgp.price as special_price', false)->join($sp->cgp, 'products.id=cgp.product_id', 'left');
            } elseif ($sp->wgp) {
                $this->db->select('wgp.price as special_price', false)->join($sp->wgp, 'products.id=wgp.product_id', 'left');
            }

            if ($promo) {
                $this->db->order_by('promotion desc');
            }
            $this->db->group_by('products.id');
            $this->db->order_by('products.promotion desc, RAND()');
            $products = $this->db->get('products')->result();

            array_map(function ($row) {
                if ($row->tax_method == '1' && $row->taxPercentage > 0) { // tax_method = 0 means inclusiveTax
                    $productTaxPercent = $row->taxPercentage;

                    if ($row->promotion == 1) {
                        $productPromoPrice = $row->promo_price;
                        $promoProductTaxAmount = $productPromoPrice * ($productTaxPercent / 100);
                        $row->promo_price = $productPromoPrice + $promoProductTaxAmount;
                    }

                    $productPrice = $row->price;
                    $productTaxAmount = $productPrice * ($productTaxPercent / 100);
                    $row->price = $productPrice + $productTaxAmount;
                }

                $warehouse_quantities = $this->getProductQuantitiesInWarehouses($row->id);
                foreach ($warehouse_quantities as $wh_quantity){
                    if(($wh_quantity->warehouse_id == '7' && $wh_quantity->quantity > 0)){
                        //$virtual_pharmacy_items += $wh_quantity->quantity;
                        $row->global = 1;
                    }
                }
            }, $products);
            $category->products = $products;
            $category->name = ucfirst(strtolower($category->name));
        }

        return $popular_categories;
    }

    public function getOrderItemsByCustomer($customer_id)
    {

        $query = $this->db->select('sale_items.product_id, sale_items.product_code, sale_items.product_name,sales.customer_id')
            ->from('sale_items')
            ->join('sales', 'sales.id = sale_items.sale_id', 'left')
            ->where('sales.customer_id', $customer_id)
            ->group_by('sale_items.product_id');
        $result = $query->get();

        if ($result) {
            return $resultArray = $result->result();
        } else {
            // Handle the error, for example, show an error message
            return false;
        }

    }

    public function getOrderItemsReviewByCustomer($customer_id)
    {
        $query = $this->db->select('product_reviews.*, products.code, products.name')
            ->from('product_reviews')
            ->join('products', 'products.id = product_reviews.product_id', 'left')
            ->where('product_reviews.customer_id', $customer_id)
        ;
        $result = $query->get();

        if ($result) {

            return $resultArray = $result->result();
        } else {
            return false;
        }

    }

    public function getBestSellers($limit = 16, $promo = true, $filters)
    {   
        $countryId = get_cookie('shop_country', true); //$this->session->userdata('country');
        $this->db->select("
        {$this->db->dbprefix('products')}.id as id, 
        {$this->db->dbprefix('products')}.name as name, 
        {$this->db->dbprefix('products')}.code as code, 
        {$this->db->dbprefix('products')}.image as image, 
        {$this->db->dbprefix('products')}.slug as slug, 
        {$this->db->dbprefix('products')}.price, 
        {$this->db->dbprefix('products')}.quantity, 
        {$this->db->dbprefix('products')}.type, 
        {$this->db->dbprefix('products')}.tax_rate as taxRateId, 
        {$this->db->dbprefix('products')}.tax_method,
        (COALESCE(SUM(sma_im.quantity), 0) - COALESCE(SUM(sma_phqor.quantity), 0)) as product_quantity, 
        promotion, 
        promo_price, 
        start_date, 
        end_date, 
        b.name as brand_name, 
        b.slug as brand_slug, 
        c.name as category_name, 
        c.slug as category_slug,
        t.name as taxName,
        t.rate as taxPercentage,
        t.code as taxCode,
        AVG(pr.rating) as avg_rating
        ")
            ->join('tax_rates t', 'products.tax_rate = t.id', 'left')
            ->join('brands b', 'products.brand=b.id', 'left')
            ->join('categories c', 'products.category_id=c.id', 'left')
            ->join('product_reviews pr', 'products.id=pr.product_id', 'left')
            ->join('(SELECT product_id, SUM(quantity) AS quantity FROM sma_inventory_movements GROUP BY product_id) AS sma_im', 'products.id=sma_im.product_id', 'left')
            ->join('(SELECT product_id, SUM(quantity) AS quantity FROM sma_product_qty_onhold_request GROUP BY product_id) AS sma_phqor', 'products.id=sma_phqor.product_id', 'left')
            ->where('products.best_seller', 1)
            ->where('products.quantity >', 0)
            ->where('hide !=', 1);
            //->where('products.cf1', $countryId)
            if (!empty($filters['brands'])) {
                $brandIds = explode(',', $filters['brands']);
                $this->db->where_in('brand', $brandIds);
            }
            if (!empty($filters['min_price'])) {
                $this->db->where('price >=', $filters['min_price']);
            }
            if (!empty($filters['max_price'])) {
                $this->db->where('price <=', $filters['max_price']);
            }
            $this->db->limit($limit);

        /*if($countryId != '0')
        {
           $this->db->where('products.cf1', $countryId);
        }*/

        $sp = $this->getSpecialPrice();
        if ($sp->cgp) {
            $this->db->select('cgp.price as special_price', false)->join($sp->cgp, 'products.id=cgp.product_id', 'left');
        } elseif ($sp->wgp) {
            $this->db->select('wgp.price as special_price', false)->join($sp->wgp, 'products.id=wgp.product_id', 'left');
        }

        if ($promo) {
            //$this->db->order_by('promotion desc');
        }
        $this->db->group_by('products.id');
        $this->db->order_by('products.promotion desc');
        $result = $this->db->get('products')->result();
            //    var_dump($result); exit;

        array_map(function ($row) {
            if ($row->tax_method == '1' && $row->taxPercentage > 0) { // tax_method = 0 means inclusiveTax
                $productTaxPercent = $row->taxPercentage;

                if ($row->promotion == 1) {
                    $productPromoPrice = $row->promo_price;
                    $promoProductTaxAmount = $productPromoPrice * ($productTaxPercent / 100);
                    $row->promo_price = $productPromoPrice + $promoProductTaxAmount;
                }

                $productPrice = $row->price;
                $productTaxAmount = $productPrice * ($productTaxPercent / 100);
                $row->price = $productPrice + $productTaxAmount;
            }

            $warehouse_quantities = $this->getProductQuantitiesInWarehouses($row->id);
            foreach ($warehouse_quantities as $wh_quantity){
                if(($wh_quantity->warehouse_id == '7' && $wh_quantity->quantity > 0)){
                    //$virtual_pharmacy_items += $wh_quantity->quantity;
                    $row->global = 1;
                }
            }
        }, $result);
        return $result;
    }

    public function getBestSellersAdditional($limit = 16, $promo = true)
    {
        $countryId = get_cookie('shop_country', true); //$this->session->userdata('country');
        $this->db->select("
        {$this->db->dbprefix('products')}.id as id, 
        {$this->db->dbprefix('products')}.name as name, 
        {$this->db->dbprefix('products')}.code as code, 
        {$this->db->dbprefix('products')}.image as image, 
        {$this->db->dbprefix('products')}.slug as slug, 
        {$this->db->dbprefix('products')}.price, 
        {$this->db->dbprefix('products')}.quantity, 
        {$this->db->dbprefix('products')}.type, 
        {$this->db->dbprefix('products')}.tax_rate as taxRateId, 
        {$this->db->dbprefix('products')}.tax_method, 
        (COALESCE(SUM(sma_im.quantity), 0) - COALESCE(SUM(sma_phqor.quantity), 0)) as product_quantity,
        promotion, 
        promo_price, 
        start_date, 
        end_date, 
        b.name as brand_name, 
        b.slug as brand_slug, 
        c.name as category_name, 
        c.slug as category_slug,
        t.name as taxName,
        t.rate as taxPercentage,
        t.code as taxCode,
        CAST(ROUND(AVG(pr.rating), 1) AS UNSIGNED) as avg_rating
        ")
            ->join('tax_rates t', 'products.tax_rate = t.id', 'left')
            ->join('brands b', 'products.brand=b.id', 'left')
            ->join('categories c', 'products.category_id=c.id', 'left')
            ->join('product_reviews pr', 'products.id=pr.product_id', 'left')
            ->join('(SELECT product_id, SUM(quantity) AS quantity FROM sma_inventory_movements GROUP BY product_id) AS sma_im', 'products.id=sma_im.product_id', 'left')
            ->join('(SELECT product_id, SUM(quantity) AS quantity FROM sma_product_qty_onhold_request GROUP BY product_id) AS sma_phqor', 'products.id=sma_phqor.product_id', 'left')
            ->where('products.best_seller', 1)
            ->where('products.quantity >', 0)
            ->where('hide !=', 1)
            //->where('products.cf1', $countryId)
            ->limit($limit);

        /*if($countryId != '0')
        {
           $this->db->where('products.cf1', $countryId);
        }*/

        $sp = $this->getSpecialPrice();
        if ($sp->cgp) {
            $this->db->select('cgp.price as special_price', false)->join($sp->cgp, 'products.id=cgp.product_id', 'left');
        } elseif ($sp->wgp) {
            $this->db->select('wgp.price as special_price', false)->join($sp->wgp, 'products.id=wgp.product_id', 'left');
        }

        if ($promo) {
            //$this->db->order_by('promotion desc');
        }
        $this->db->group_by('products.id');
        $this->db->order_by('id asc');
        $result = $this->db->get('products')->result();
        //        dd($result);

        array_map(function ($row) {
            if ($row->tax_method == '1' && $row->taxPercentage > 0) { // tax_method = 0 means inclusiveTax
                $productTaxPercent = $row->taxPercentage;

                if ($row->promotion == 1) {
                    $productPromoPrice = $row->promo_price;
                    $promoProductTaxAmount = $productPromoPrice * ($productTaxPercent / 100);
                    $row->promo_price = $productPromoPrice + $promoProductTaxAmount;
                }

                $productPrice = $row->price;
                $productTaxAmount = $productPrice * ($productTaxPercent / 100);
                $row->price = $productPrice + $productTaxAmount;
            }

            $warehouse_quantities = $this->getProductQuantitiesInWarehouses($row->id);
            foreach ($warehouse_quantities as $wh_quantity){
                if(($wh_quantity->warehouse_id == '7' && $wh_quantity->quantity > 0)){
                    //$virtual_pharmacy_items += $wh_quantity->quantity;
                    $row->global = 1;
                }
            }
        }, $result);
        return $result;
    }

    public function getFeaturedProducts($limit = 16, $promo = true)
    {
        $countryId = get_cookie('shop_country', true); //$this->session->userdata('country');
        $this->db->select("
        {$this->db->dbprefix('products')}.id as id, 
        {$this->db->dbprefix('products')}.name as name, 
        {$this->db->dbprefix('products')}.code as code, 
        {$this->db->dbprefix('products')}.image as image, 
        {$this->db->dbprefix('products')}.slug as slug, 
        {$this->db->dbprefix('products')}.price, 
        {$this->db->dbprefix('products')}.quantity, 
        {$this->db->dbprefix('products')}.type, 
        {$this->db->dbprefix('products')}.tax_rate as taxRateId, 
        {$this->db->dbprefix('products')}.tax_method, 
        (COALESCE(SUM(sma_im.quantity), 0) - COALESCE(SUM(sma_phqor.quantity), 0)) as product_quantity, 
        promotion, 
        promo_price, 
        start_date, 
        end_date, 
        b.name as brand_name, 
        b.slug as brand_slug, 
        c.name as category_name, 
        c.slug as category_slug,
        t.name as taxName,
        t.rate as taxPercentage,
        t.code as taxCode,
        CAST(ROUND(AVG(pr.rating), 1) AS UNSIGNED) as avg_rating
        ")
            ->join('tax_rates t', 'products.tax_rate = t.id', 'left')
            ->join('brands b', 'products.brand=b.id', 'left')
            ->join('categories c', 'products.category_id=c.id', 'left')
            ->join('product_reviews pr', 'products.id=pr.product_id', 'left')
            ->join('(SELECT product_id, SUM(quantity) AS quantity FROM sma_inventory_movements GROUP BY product_id) AS sma_im', 'products.id=sma_im.product_id', 'left')
            ->join('(SELECT product_id, SUM(quantity) AS quantity FROM sma_product_qty_onhold_request GROUP BY product_id) AS sma_phqor', 'products.id=sma_phqor.product_id', 'left')
            ->where('products.featured', 1)
            ->where('products.quantity >', 0)
            ->where('hide !=', 1)
            //->where('products.cf1', $countryId)
            ->limit($limit);

        /*if($countryId != '0')
        {
           $this->db->where('products.cf1', $countryId);
        }*/

        $sp = $this->getSpecialPrice();
        if ($sp->cgp) {
            $this->db->select('cgp.price as special_price', false)->join($sp->cgp, 'products.id=cgp.product_id', 'left');
        } elseif ($sp->wgp) {
            $this->db->select('wgp.price as special_price', false)->join($sp->wgp, 'products.id=wgp.product_id', 'left');
        }

        if ($promo) {
            $this->db->order_by('promotion desc');
        }
        $this->db->group_by('products.id');
        $this->db->order_by('RAND()');
        $result = $this->db->get('products')->result();
        //        dd($result);

        array_map(function ($row) {
            if ($row->tax_method == '1' && $row->taxPercentage > 0) { // tax_method = 0 means inclusiveTax
                $productTaxPercent = $row->taxPercentage;

                if ($row->promotion == 1) {
                    $productPromoPrice = $row->promo_price;
                    $promoProductTaxAmount = $productPromoPrice * ($productTaxPercent / 100);
                    $row->promo_price = $productPromoPrice + $promoProductTaxAmount;
                }

                $productPrice = $row->price;
                $productTaxAmount = $productPrice * ($productTaxPercent / 100);
                $row->price = $productPrice + $productTaxAmount;
            }

            $warehouse_quantities = $this->getProductQuantitiesInWarehouses($row->id);
            foreach ($warehouse_quantities as $wh_quantity){
                if(($wh_quantity->warehouse_id == '7' && $wh_quantity->quantity > 0)){
                    //$virtual_pharmacy_items += $wh_quantity->quantity;
                    $row->global = 1;
                }
            }
        }, $result);
        return $result;
    }

    public function getNotifications()
    {
        $date = date('Y-m-d H:i:s', time());
        $this->db->where('from_date <=', $date)
            ->where('till_date >=', $date)->where('scope !=', 2);
        return $this->db->get('notifications')->result();
    }

    public function getOrder($clause)
    {
        if ($this->loggedIn) {
            $this->db->order_by('id desc');
            $sale = $this->db->get_where('sales', ['id' => $clause['id']], 1)->row();
            return ($sale->customer_id == $this->session->userdata('company_id')) ? $sale : false;
        } elseif (!empty($clause['hash'])) {
            return $this->db->get_where('sales', $clause, 1)->row();
        } else {
            $this->db->order_by('id desc');
            $sale = $this->db->get_where('sales', ['id' => $clause['id']], 1)->row();
            return $sale;
        }
        return false;
    }

    public function getOrderItems($sale_id)
    {
        $this->db->select('sale_items.*, tax_rates.code as tax_code, tax_rates.name as tax_name, tax_rates.rate as tax_rate, products.image, products.details as details, product_variants.name as variant, products.hsn_code as hsn_code,  products.second_name as second_name')
            ->join('products', 'products.id=sale_items.product_id', 'left')
            ->join('product_variants', 'product_variants.id=sale_items.option_id', 'left')
            ->join('tax_rates', 'tax_rates.id=sale_items.tax_rate_id', 'left')
            ->group_by('sale_items.id')
            ->order_by('id', 'asc');

        return $this->db->get_where('sale_items', ['sale_id' => $sale_id])->result();
    }

    public function getOrders($limit, $offset)
    {
        if ($this->loggedIn) {
            $this->db->select('sales.*, deliveries.status as delivery_status, refund.refund_status as refund_status')
                ->join('deliveries', 'deliveries.sale_id=sales.id', 'left')
                ->join('refund', 'refund.order_id=sales.id', 'left')
                ->distinct('sales.id')
                ->order_by('id', 'desc')->limit($limit, $offset);
            return $this->db->get_where('sales', ['customer_id' => $this->session->userdata('company_id')])->result();
        }
        return false;
    }

    public function getOrdersCount()
    {
        $this->db->where('customer_id', $this->session->userdata('company_id'));
        return $this->db->count_all_results('sales');
    }

    public function getCustomerOrdersCount($customer_id)
    {
        $this->db->where('customer_id', $customer_id);
        return $this->db->count_all_results('sales');
    }

    public function getOtherProducts($id, $category_id, $brand)
    {
        $this->db->select("{$this->db->dbprefix('products')}.id as id, {$this->db->dbprefix('products')}.name as name, {$this->db->dbprefix('products')}.code as code, {$this->db->dbprefix('products')}.image as image, {$this->db->dbprefix('products')}.slug as slug, {$this->db->dbprefix('products')}.price, quantity, type, promotion, promo_price, start_date, end_date, b.name as brand_name, b.slug as brand_slug, c.name as category_name, c.slug as category_slug")
            ->join('brands b', 'products.brand=b.id', 'left')
            ->join('categories c', 'products.category_id=c.id', 'left')
            ->where('category_id', $category_id)->where('brand', $brand)
            ->where('products.id !=', $id)->where('hide !=', 1)
            ->order_by('rand()')->limit(4);

        $sp = $this->getSpecialPrice();
        if ($sp->cgp) {
            $this->db->select('cgp.price as special_price', false)->join($sp->cgp, 'products.id=cgp.product_id', 'left');
        } elseif ($sp->wgp) {
            $this->db->select('wgp.price as special_price', false)->join($sp->wgp, 'products.id=wgp.product_id', 'left');
        }
        return $this->db->get('products')->result();
    }

    public function getPageBySlug($slug)
    {
        return $this->db->get_where('pages', ['slug' => $slug], 1)->row();
    }

    public function getBlogBySlug($slug)
    {
        // $this->db->select("*");
        // $this->db->from('blog');

        // $query = $this->db->get();
        // if ($query->num_rows() > 0){
        //     return $query->result();
        // }else{
        //     return false;
        // }
        return $this->db->get_where('blog', ['slug' => $slug], 1)->row();

    }

    public function getPaypalSettings()
    {
        return $this->db->get_where('paypal', ['id' => 1])->row();
    }

    public function getPriceGroup($id)
    {
        return $this->db->get_where('price_groups', ['id' => $id])->row();
    }

    public function getProductByID($id)
    {
        $this->db->select("{$this->db->dbprefix('products')}.id as id, {$this->db->dbprefix('products')}.name as name, {$this->db->dbprefix('products')}.code as code, {$this->db->dbprefix('products')}.image as image, {$this->db->dbprefix('products')}.slug as slug, price, quantity, type, promotion, promo_price, start_date, end_date, product_details as details,weight");
        return $this->db->get_where('products', ['id' => $id], 1)->row();
    }

    public function getProductBySlug($slug)
    {
        $this->db->select("
        {$this->db->dbprefix('products')}.*," .
            $this->db->dbprefix('brands') . '.name as brand_name,
            t.name as taxName,
            t.rate as taxPercentage,
            t.code as taxCode,
            CAST(ROUND(AVG(pr.rating), 1) AS UNSIGNED) as avg_rating'
        )
            ->join('tax_rates t', 'products.tax_rate = t.id', 'left')
            ->join('product_reviews pr', 'products.id=pr.product_id', 'left');
        $sp = $this->getSpecialPrice();
        if ($sp->cgp) {
            $this->db->select('cgp.price as special_price', false)->join($sp->cgp, 'products.id=cgp.product_id', 'left');
        } elseif ($sp->wgp) {
            $this->db->select('wgp.price as special_price', false)->join($sp->wgp, 'products.id=wgp.product_id', 'left');
        }
        $this->db->join('brands', 'products.brand=' . $this->db->dbprefix('brands') . '.id', 'left');
        return $this->db->get_where('products', ['products.slug' => $slug, 'products.hide !=' => 1], 1)->row();
    }

    public function getProductComboItems($pid)
    {
        $this->db->select($this->db->dbprefix('products') . '.id as id, ' . $this->db->dbprefix('products') . '.code as code, ' . $this->db->dbprefix('combo_items') . '.quantity as qty, ' . $this->db->dbprefix('products') . '.name as name, ' . $this->db->dbprefix('combo_items') . '.unit_price as price')->join('products', 'products.code=combo_items.item_code', 'left')->group_by('combo_items.id');
        $q = $this->db->get_where('combo_items', ['product_id' => $pid]);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }

            return $data;
        }
        return false;
    }

    public function getProductForCart($id)
    {
        $this->db->select("{$this->db->dbprefix('products')}.*")->where('products.id', $id);
        $sp = $this->getSpecialPrice();
        if ($sp->cgp) {
            $this->db->select('cgp.price as special_price', false)->join($sp->cgp, 'products.id=cgp.product_id', 'left');
        } elseif ($sp->wgp) {
            $this->db->select('wgp.price as special_price', false)->join($sp->wgp, 'products.id=wgp.product_id', 'left');
        }
        $q = $this->db->get('products', 1);
        if ($q->num_rows() > 0) {
            $row = $q->row();
            //            Added by Atif and Commented by atif.
//            if($row->tax_method == '0') { // 0 = tax inclusive, 1 = tax exclusive
//                $tax = $this->db->get_where('tax_rates', ['id' => $row->tax_rate])->row();
//                if($tax){
//                    $productTaxPercent = $tax->rate;
//                    $productPrice = (int)$row->price;
//                    $productTaxAmount = $productPrice * ($productTaxPercent / 100);
//                    $row->price = $productPrice + $productTaxAmount;
//                }
//            }
            return $row;
        }
        return false;
    }

    public function getProductOptions($product_id)
    {
        return $this->db->get_where('product_variants', ['product_id' => $product_id])->result();
    }

    public function getProductOptionsWithWH($product_id, $warehouse_id = null)
    {
        if (!$warehouse_id) {
            $warehouse_id = $this->shop_settings->warehouse;
        }
        $this->db->select($this->db->dbprefix('product_variants') . '.*, ' . $this->db->dbprefix('warehouses') . '.name as wh_name, ' . $this->db->dbprefix('warehouses') . '.id as warehouse_id, ' . $this->db->dbprefix('warehouses_products_variants') . '.quantity as wh_qty')
            ->join('warehouses_products_variants', 'warehouses_products_variants.option_id=product_variants.id', 'left')
            ->join('warehouses', 'warehouses.id=warehouses_products_variants.warehouse_id', 'left')
            ->group_by(['' . $this->db->dbprefix('product_variants') . '.id', '' . $this->db->dbprefix('warehouses_products_variants') . '.warehouse_id'])
            ->order_by('product_variants.id');
        return $this->db->get_where('product_variants', ['product_variants.product_id' => $product_id, 'warehouses.id' => $warehouse_id, 'warehouses_products_variants.quantity !=' => null])->result();
    }

    public function getProductPhotos($id)
    {
        $q = $this->db->get_where('product_photos', ['product_id' => $id]);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
    }

    /* public function getProducts($filters = [])
     {
         $this->db->select("{$this->db->dbprefix('products')}.id as id,{$this->db->dbprefix('categories')}.name as category_name,{$this->db->dbprefix('categories')}.slug as category_slug,{$this->db->dbprefix('brands')}.name as brand_name,{$this->db->dbprefix('brands')}.slug as brand_slug, {$this->db->dbprefix('products')}.Price_in_Dollar, {$this->db->dbprefix('products')}.name as name, {$this->db->dbprefix('products')}.code as code, {$this->db->dbprefix('products')}.image as image, {$this->db->dbprefix('products')}.slug as slug, {$this->db->dbprefix('products')}.retail_price, {$this->db->dbprefix('products')}.price, {$this->db->dbprefix('products')}.Auther_1, {$this->db->dbprefix('warehouses_products')}.quantity as quantity, type, promotion, promo_price, start_date, end_date, product_details as details")
         ->from('products')
         ->join('warehouses_products', 'products.id=warehouses_products.product_id', 'left')
         ->join('publishers as pub1', 'products.publisher=pub1.id', 'left')
         ->join('categories', 'products.category_id=categories.id', 'left')
         ->join('brands', 'products.brand=brands.id', 'left');
         if($this->shop_settings->warehouse>0 ){
             $this->db->where('warehouses_products.warehouse_id', $this->shop_settings->warehouse);
         }
         $this->db->group_by('products.id');

         $sp = $this->getSpecialPrice();
         if ($sp->cgp) {
             $this->db->select('cgp.price as special_price', false)->join($sp->cgp, 'products.id=cgp.product_id', 'left');
         } elseif ($sp->wgp) {
             $this->db->select('wgp.price as special_price', false)->join($sp->wgp, 'products.id=wgp.product_id', 'left');
         }

         if ($this->shop_settings->hide0) {
             $this->db->group_start()->where('warehouses_products.quantity >', 0)->or_where('products.type !=', 'standard')->group_end();
         }
         $this->db->where('hide !=', 1)
         ->limit($filters['limit'], $filters['offset']);
         if (!empty($filters)) {
             if (!empty($filters['promo'])) {
                 $today = date('Y-m-d');
                 $this->db->where('promotion', 1)->where('start_date <=', $today)->where('end_date >=', $today);
             }
             if (!empty($filters['featured'])) {
                 $this->db->where('featured', 1);
             }
             if (!empty($filters['query'])) {
                 $this->db->group_start()->like('name', $filters['query'], 'both')->or_like('code', $filters['query'], 'both')->group_end();
             }
             if (!empty($filters['category'])) {
                 $this->db->where('category_id', $filters['category']['id']);
             }
             if (!empty($filters['subcategory'])) {
                 $this->db->where('subcategory_id', $filters['subcategory']['id']);
             }
             if (!empty($filters['brand'])) {
                 $this->db->where('brand', $filters['brand']['id']);
             }
             if (!empty($filters['min_price'])) {
                 $this->db->where('products.price >=', $filters['min_price']);
             }
             if (!empty($filters['max_price'])) {
                 $this->db->where('products.price <=', $filters['max_price']);
             }
             if (!empty($filters['in_stock'])) {
                 $this->db->group_start()->where('warehouses_products.quantity >=', 1)->or_where('type !=', 'standard')->group_end();
             }
             if (!empty($filters['sorting'])) {
                 $sort = explode('-', $filters['sorting']);
                 $this->db->order_by($sort[0], $this->db->escape_str($sort[1]));
             } else {
                 $this->db->order_by('name asc');
             }
         } else {
             $this->db->order_by('name asc');
         }
         return $this->db->get()->result_array();
     }*/

    public function getProducts($filters = [])
    {
        $this->db->select("
        {$this->db->dbprefix('products')}.id as id,
        {$this->db->dbprefix('categories')}.name as category_name,
        {$this->db->dbprefix('categories')}.slug as category_slug,
        {$this->db->dbprefix('brands')}.name as brand_name,
        {$this->db->dbprefix('brands')}.slug as brand_slug, 
        {$this->db->dbprefix('products')}.name as name, 
        {$this->db->dbprefix('products')}.code as code, 
        {$this->db->dbprefix('products')}.image as image, 
        {$this->db->dbprefix('products')}.slug as slug, 
        {$this->db->dbprefix('products')}.price,
        {$this->db->dbprefix('products')}.quantity as quantity, 
        {$this->db->dbprefix('products')}.type, 
        {$this->db->dbprefix('products')}.tax_rate as taxRateId, 
        {$this->db->dbprefix('products')}.tax_method,
        (COALESCE(SUM(sma_im.quantity), 0) - COALESCE(SUM(sma_phqor.quantity), 0)) as product_quantity,
        promotion, 
        promo_price, 
        start_date, 
        end_date, 
        product_details as details,
        t.name as taxName,
        t.rate as taxPercentage,
        t.code as taxCode,
        CAST(ROUND(AVG(pr.rating), 1) AS UNSIGNED) as avg_rating")
            ->from('products')
            ->join('tax_rates t', 'products.tax_rate = t.id', 'left')
            //->join('warehouses_products', 'products.id=warehouses_products.product_id', 'left')
            ->join('categories', 'products.category_id=categories.id', 'left')
            ->join('brands', 'products.brand=brands.id', 'left')
            ->join('product_reviews pr', 'products.id=pr.product_id', 'left')
            ->join('(SELECT product_id, SUM(quantity) AS quantity FROM sma_inventory_movements GROUP BY product_id) AS sma_im', 'products.id=sma_im.product_id', 'left')
            ->join('(SELECT product_id, SUM(quantity) AS quantity FROM sma_product_qty_onhold_request GROUP BY product_id) AS sma_phqor', 'products.id=sma_phqor.product_id', 'left');
        if ($this->shop_settings->warehouse > 0) {
           // $this->db->where('warehouses_products.warehouse_id', $this->shop_settings->warehouse);
        }
        $this->db->group_by('products.id');

        $sp = $this->getSpecialPrice();
        if ($sp->cgp) {
            $this->db->select('cgp.price as special_price', false)->join($sp->cgp, 'products.id=cgp.product_id', 'left');
        } elseif ($sp->wgp) {
            $this->db->select('wgp.price as special_price', false)->join($sp->wgp, 'products.id=wgp.product_id', 'left');
        }

        $this->db->where('hide !=', 1)->limit($filters['limit'], $filters['offset']);
        if (!empty($filters)) {
            if (!empty($filters['promo'])) {
                $today = date('Y-m-d');
                $this->db->where('promotion', 1)->where('start_date <=', $today)->where('end_date >=', $today);
            }
            if (!empty($filters['featured'])) {
                $this->db->where('featured', 1);
            }
            if (!empty($filters['special_product'])) {
                $this->db->where('special_product', 1);
            }
            $sortcase = '';
            if (!empty($filters['query'])) {
                $booksearch = strtolower($filters['query']);
                $sortcase = "CASE when (" . $this->db->dbprefix('products') . ".name LIKE '%" . $booksearch . "%') THEN 1 ELSE 0 END";

                $wheres = array();
                $searchquery = explode(' ', $booksearch);
                foreach ($searchquery as $booksearch) {
                    if (!empty(trim($booksearch))) {
                        $wheres[] = "( {$this->db->dbprefix('products')}.name LIKE '%" . $booksearch . "%' OR {$this->db->dbprefix('products')}.code LIKE '%" . $booksearch . "%')";
                    }
                }
                if (!empty($wheres)) {
                    $this->db->where("(" . implode(' AND ', $wheres) . ")");
                }
                /* Comment June 14, 2022 */
                /* $nameRegex = str_replace(' ','|', strtolower($filters['query'])); 
             
                $this->db->group_start()->where("LOWER({$this->db->dbprefix('products')}.name) REGEXP ", $nameRegex)->or_like("{$this->db->dbprefix('products')}.code", $filters['query'], 'both') ->or_like("{$this->db->dbprefix('products')}.Auther_1", $filters['query'], 'both')->or_like("{$this->db->dbprefix('products')}.Auther_2", $filters['query'], 'both')->or_like("{$this->db->dbprefix('products')}.Auther_3", $filters['query'], 'both') -> or_where("LOWER(pub1.name) REGEXP ", $filters['query'], 'both')->group_end();   */
                /* END */

                /* $this->db->group_start()->where("LOWER({$this->db->dbprefix('products')}.name) REGEXP ", $nameRegex)->or_like("{$this->db->dbprefix('products')}.code", $filters['query'], 'both') ->or_like("{$this->db->dbprefix('products')}.Auther_1", $filters['query'], 'both')->or_like("{$this->db->dbprefix('products')}.Auther_2", $filters['query'], 'both')->or_like("{$this->db->dbprefix('products')}.Auther_3", $filters['query'], 'both')->group_end();  */

                /* $this->db->group_start()->where('LOWER(name) REGEXP ', $nameRegex)->or_like('code', $filters['query'], 'both')->group_end(); */

                // $this->db->group_start()->like('name', $filters['query'], 'both')->or_like('code', $filters['query'], 'both')->group_end();
            }
            if (empty($filters['query'])) {
                if (!empty($filters['category'])) {
                    $this->db->where('category_id', $filters['category']['id']);
                }
                if (!empty($filters['subcategory'])) {
                    $this->db->where('subcategory_id', $filters['subcategory']['id']);
                }
            }
            if (!empty($filters['category_id'])) {
                $this->db->where('category_id', $filters['category_id']);
            }
            if (!empty($filters['subcategory_id'])) {
                $this->db->where_in('subcategory_id', $filters['subcategory_id']);
            }
            if (!empty($filters['brand'])) {
                $this->db->where('brand', $filters['brand']['id']);
            }
            if (!empty($filters['brands'])) {
                $brandIds = explode(',', $filters['brands']);
                $this->db->where_in('brand', $brandIds);
            }
            if (!empty($filters['min_price'])) {
                $this->db->where('price >=', $filters['min_price']);
            }
            if (!empty($filters['max_price'])) {
                $this->db->where('price <=', $filters['max_price']);
            }
            if (!empty($filters['in_stock'])) {
                //$this->db->group_start()->where('warehouses_products.quantity >=', 1)->or_where('type !=', 'standard')->group_end();
            }

            if(!empty($filters['promo']) || !empty($filters['special_product'])){
                $this->db->where_not_in('sma_products.code', array('HON002', 'HON007', 'HON008', 'HON010', 'HON006'));
            }

            if (empty($filters['query'])) {
                if (!empty($filters['sorting'])) {
                    $sort = explode('-', $filters['sorting']);
                    $this->db->order_by($sort[0], $this->db->escape_str($sort[1]));
                } else {
                    $this->db->order_by('promotion desc, id desc');
                }
            } else {
                $this->db->order_by($sortcase . ' desc');


            }
        } else {
            $this->db->order_by('id desc');
        }


        $results = $this->db->get();
        
        $data = array();

        if ($results !== FALSE && $results->num_rows() > 0) {
            $data = $results->result_array();

            // Sort the data so that products with brand 407 come last
            usort($data, function($a, $b) {
                if ($a['brand'] == 407 && $b['brand'] != 407) {
                    return 1;
                } elseif ($a['brand'] != 407 && $b['brand'] == 407) {
                    return -1;
                } else {
                    return 0;
                }
            });

            // If category_id is 25, fetch product_id 3 and add it to the result set
            if (!empty($filters['category']['id']) && $filters['category']['id'] == 25) {
                $this->db->select("
                {$this->db->dbprefix('products')}.id as id,
                {$this->db->dbprefix('categories')}.name as category_name,
                {$this->db->dbprefix('categories')}.slug as category_slug,
                {$this->db->dbprefix('brands')}.name as brand_name,
                {$this->db->dbprefix('brands')}.slug as brand_slug, 
                {$this->db->dbprefix('products')}.name as name, 
                {$this->db->dbprefix('products')}.code as code, 
                {$this->db->dbprefix('products')}.image as image, 
                {$this->db->dbprefix('products')}.slug as slug, 
                {$this->db->dbprefix('products')}.price,
                {$this->db->dbprefix('products')}.quantity as quantity, 
                {$this->db->dbprefix('products')}.type, 
                {$this->db->dbprefix('products')}.tax_rate as taxRateId, 
                {$this->db->dbprefix('products')}.tax_method,
                (COALESCE(SUM(sma_im.quantity), 0) - COALESCE(SUM(sma_phqor.quantity), 0)) as product_quantity,
                promotion, 
                promo_price, 
                start_date, 
                end_date, 
                product_details as details,
                t.name as taxName,
                t.rate as taxPercentage,
                t.code as taxCode,
                CAST(ROUND(AVG(pr.rating), 1) AS UNSIGNED) as avg_rating")
                ->from('products')
                ->join('tax_rates t', 'products.tax_rate = t.id', 'left')
                ->join('categories', 'products.category_id=categories.id', 'left')
                ->join('brands', 'products.brand=brands.id', 'left')
                ->join('product_reviews pr', 'products.id=pr.product_id', 'left')
                ->join('(SELECT product_id, SUM(quantity) AS quantity FROM sma_inventory_movements GROUP BY product_id) AS sma_im', 'products.id=sma_im.product_id', 'left')
                ->join('(SELECT product_id, SUM(quantity) AS quantity FROM sma_product_qty_onhold_request GROUP BY product_id) AS sma_phqor', 'products.id=sma_phqor.product_id', 'left')
                ->where('products.id', 3)
                ->group_by('products.id')
                ->limit(1);
            
                $additional_product = $this->db->get();
                if ($additional_product !== FALSE && $additional_product->num_rows() > 0) {
                    $additional_product_data = $additional_product->result_array();
                    $data = array_merge($additional_product_data, $data);
                }
            }

            $mapData = array_map(function ($row) {
                if ($row['tax_method'] == '1' && $row['taxPercentage'] > 0) { // tax_method = 0 means inclusiveTax
                    $productTaxPercent = $row['taxPercentage'];

                    if ($row['promotion'] == 1) {
                        $productPromoPrice = $row['promo_price'];
                        $promoProductTaxAmount = $productPromoPrice * ($productTaxPercent / 100);
                        $row['promo_price'] = $productPromoPrice + $promoProductTaxAmount;
                    }

                    $productPrice = $row['price'];
                    $productTaxAmount = $productPrice * ($productTaxPercent / 100);

                    $row['price'] = $productPrice + $productTaxAmount;
                    $row['name'] = stripslashes($row['name']);
                    return $row;
                }

                $warehouse_quantities = $this->getProductQuantitiesInWarehouses($row['id']);
                foreach ($warehouse_quantities as $wh_quantity){
                    if(($wh_quantity->warehouse_id == '7' && $wh_quantity->quantity > 0)){
                        //$virtual_pharmacy_items += $wh_quantity->quantity;
                        $row['global'] = 1;
                    }
                }

                $row['name'] = stripslashes($row['name']);
                return $row;
            }, $data);
        }
        /*  echo $this->db->last_query(); */
        //        dd($data);
        return $mapData;
    }

    public function getProductsCount($filters = [])
    {
        $this->db->select("{$this->db->dbprefix('products')}.id as id")
            ->join('warehouses_products', 'products.id=warehouses_products.product_id', 'left')
            //->where('warehouses_products.warehouse_id', $this->shop_settings->warehouse)
            ->group_by('products.id');

        $sp = $this->getSpecialPrice();
        if ($sp->cgp) {
            $this->db->select('cgp.price as special_price', false)->join($sp->cgp, 'products.id=cgp.product_id', 'left');
        } elseif ($sp->wgp) {
            $this->db->select('wgp.price as special_price', false)->join($sp->wgp, 'products.id=wgp.product_id', 'left');
        }

        if (!empty($filters)) {
            if (!empty($filters['promo'])) {
                $today = date('Y-m-d');
                $this->db->where('promotion', 1)->where('start_date <=', $today)->where('end_date >=', $today);
            }
            if (!empty($filters['special_product'])) {
                $this->db->where('special_product', 1);
            }
            if (!empty($filters['featured'])) {
                $this->db->where('featured', 1);
            }
            if (!empty($filters['query'])) {
                $this->db->group_start()->like('name', $filters['query'], 'both')->or_like('code', $filters['query'], 'both')->group_end();
            }
            if (!empty($filters['category'])) {
                $this->db->where('category_id', $filters['category']['id']);
            }
            if (!empty($filters['subcategory'])) {
                $this->db->where('subcategory_id', $filters['subcategory']['id']);
            }
            if (!empty($filters['brand'])) {
                $this->db->where('brand', $filters['brand']['id']);
            }
            if (!empty($filters['min_price'])) {
                $this->db->where('products.price >=', $filters['min_price']);
            }
            if (!empty($filters['max_price'])) {
                $this->db->where('products.price <=', $filters['max_price']);
            }
            if(!empty($filters['promo']) || !empty($filters['special_product'])){
                $this->db->where_not_in('sma_products.code', array('HON002', 'HON007', 'HON008', 'HON010', 'HON006'));
            }
            if (!empty($filters['in_stock'])) {
                $this->db->group_start()->where('warehouses_products.quantity >=', 1)->or_where('type !=', 'standard')->group_end();
            }
        }

        if ($this->shop_settings->hide0) {
            $this->db->group_start()->where('warehouses_products.quantity >', 0)->or_where('products.type !=', 'standard')->group_end();
        }
        $this->db->where('hide !=', 1);
        return $this->db->count_all_results('products');
    }

    public function getProductVariantByID($id)
    {
        return $this->db->get_where('product_variants', ['id' => $id])->row();
    }

    public function getProductVariants($product_id, $warehouse_id = null, $all = null)
    {
        if (!$warehouse_id) {
            $warehouse_id = $this->shop_settings->warehouse;
        }
        $wpv = "( SELECT option_id, warehouse_id, quantity from {$this->db->dbprefix('warehouses_products_variants')} WHERE product_id = {$product_id}) FWPV";
        $this->db->select('product_variants.id as id, product_variants.name as name, product_variants.price as price, product_variants.quantity as total_quantity, FWPV.quantity as quantity', false)
            ->join($wpv, 'FWPV.option_id=product_variants.id', 'left')
            //->join('warehouses', 'warehouses.id=product_variants.warehouse_id', 'left')
            ->where('product_variants.product_id', $product_id)
            ->group_by('product_variants.id');

        if (!$this->Settings->overselling && !$all) {
            $this->db->where('FWPV.warehouse_id', $warehouse_id);
            $this->db->where('FWPV.quantity >', 0);
        }
        return $this->db->get('product_variants')->result_array();
    }

    public function getProductVariantWarehouseQty($option_id, $warehouse_id)
    {
        return $this->db->get_where('warehouses_products_variants', ['option_id' => $option_id, 'warehouse_id' => $warehouse_id], 1)->row();
    }

    public function getQuote($clause)
    {
        if ($this->loggedIn) {
            $this->db->order_by('id desc');
            $sale = $this->db->get_where('quotes', ['id' => $clause['id']], 1)->row();
            return ($sale->customer_id == $this->session->userdata('company_id')) ? $sale : false;
        } elseif (!empty($clause['hash'])) {
            return $this->db->get_where('quotes', $clause, 1)->row();
        }
        return false;
    }

    public function getQuoteItems($quote_id)
    {
        $this->db->select('quote_items.*, tax_rates.code as tax_code, tax_rates.name as tax_name, tax_rates.rate as tax_rate, products.image, products.details as details, product_variants.name as variant, products.hsn_code as hsn_code,  products.second_name as second_name')
            ->join('products', 'products.id=quote_items.product_id', 'left')
            ->join('product_variants', 'product_variants.id=quote_items.option_id', 'left')
            ->join('tax_rates', 'tax_rates.id=quote_items.tax_rate_id', 'left')
            ->group_by('quote_items.id')
            ->order_by('id', 'asc');
        return $this->db->get_where('quote_items', ['quote_id' => $quote_id])->result();
    }

    public function getQuotes($limit, $offset)
    {
        if ($this->loggedIn) {
            $this->db->order_by('id', 'desc')->limit($limit, $offset);
            return $this->db->get_where('quotes', ['customer_id' => $this->session->userdata('company_id')])->result();
        }
        return false;
    }

    public function getQuotesCount()
    {
        $this->db->where('customer_id', $this->session->userdata('company_id'));
        return $this->db->count_all_results('quotes');
    }

    public function getSaleByID($id)
    {
        return $this->db->get_where('sales', ['id' => $id])->row();
    }

    public function getSettings()
    {
        return $this->db->get('settings')->row();
    }

    public function getShopSettings()
    {
        return $this->db->get('shop_settings')->row();
    }

    public function getSkrillSettings()
    {
        return $this->db->get_where('skrill', ['id' => 1])->row();
    }

    public function getSpecialPrice()
    {
        $sp = new stdClass();
        $sp->cgp = ($this->customer && $this->customer->price_group_id) ? "( SELECT {$this->db->dbprefix('product_prices')}.price as price, {$this->db->dbprefix('product_prices')}.product_id as product_id, {$this->db->dbprefix('product_prices')}.price_group_id as price_group_id from {$this->db->dbprefix('product_prices')} WHERE {$this->db->dbprefix('product_prices')}.price_group_id = {$this->customer->price_group_id} ) cgp" : null;

        $sp->wgp = ($this->warehouse && $this->warehouse->price_group_id && !$this->customer) ? "( SELECT {$this->db->dbprefix('product_prices')}.price as price, {$this->db->dbprefix('product_prices')}.product_id as product_id, {$this->db->dbprefix('product_prices')}.price_group_id as price_group_id from {$this->db->dbprefix('product_prices')} WHERE {$this->db->dbprefix('product_prices')}.price_group_id = {$this->warehouse->price_group_id} ) wgp" : null;

        return $sp;
    }

    public function getSubCategories($parent_id)
    {
        $this->db->where('parent_id', $parent_id)->order_by('name');
        return $this->db->get('categories')->result();
    }

    public function getUserByEmail($email)
    {
        return $this->db->get_where('users', ['email' => $email], 1)->row();
    }

    public function getWishlist($no = null)
    {
        $this->db->where('user_id', $this->session->userdata('user_id'));
        return $no ? $this->db->count_all_results('wishlist') : $this->db->get('wishlist')->result();
    }

    public function getWishlistItem($product_id, $user_id)
    {
        return $this->db->get_where('wishlist', ['product_id' => $product_id, 'user_id' => $user_id])->row();
    }

    public function isPromo()
    {
        $today = date('Y-m-d');
        $this->db->where('promotion', 1)->where('start_date <=', $today)->where('end_date >=', $today);
        return $this->db->count_all_results('products');
    }

    public function removeWishlist($product_id)
    {
        $user_id = $this->session->userdata('user_id');
        return $this->db->delete('wishlist', ['product_id' => $product_id, 'user_id' => $user_id]);
    }

    public function updateCompany($id, $data = [])
    {
        return $this->db->update('companies', $data, ['id' => $id]);
    }

    public function updateProductViews($id, $views)
    {
        $views = is_numeric($views) ? ($views + 1) : 1;
        return $this->db->update('products', ['views' => $views], ['id' => $id]);
    }

    public function getProductBrandsByName($term)
    {
        $wp = "( SELECT product_id, warehouse_id, quantity as quantity from {$this->db->dbprefix('warehouses_products')} ) FWP";

        $this->db->distinct();
        $this->db->select('brands.*, categories.id as category_id, categories.name as category_name', false)
            // ->join($wp, 'FWP.product_id=products.id', 'left')
            // ->join('warehouses_products FWP', 'FWP.product_id=products.id', 'left')
            ->join('categories', 'categories.id=products.category_id', 'left')
            ->group_by('products.id');


        $booksearch = strtolower($term);
        $wheres = array();
        $searchquery = explode(' ', $booksearch);
        foreach ($searchquery as $booksearch) {
            if (!empty(trim($booksearch))) {
                $wheres[] = "( {$this->db->dbprefix('products')}.name LIKE '%" . $booksearch . "%' OR  {$this->db->dbprefix('products')}.code LIKE '%" . $booksearch . "%')";
            }
        }

        if (!empty($wheres)) {
            $this->db->or_where("(" . implode(' AND ', $wheres) . ")");
        }

        if ($category_id != null && $category_id != 0) {
            $this->db->where('products.category_id', $category_id);
        }
        if ($pos) {
            $this->db->where('hide_pos !=', 1);
        }
        //$this->db->limit($limit);
        $q = $this->db->get('products');
        $checkCounter = 1;
        $oneString = '';
        if ($q !== FALSE && $q->num_rows() > 0) {
            foreach (($q->result()) as $row) {

                $data[] = $row;
            }
            return $data;
        }
    }

    public function getProductNames($term, $warehouse_id, $category_id, $pos = false, $limit = 8)
    {
        $wp = "( SELECT product_id, warehouse_id, quantity as quantity from {$this->db->dbprefix('warehouses_products')} ) FWP";

        $this->db->distinct();
        $this->db->select('products.*, categories.id as category_id, categories.name as category_name', false)
            // ->join($wp, 'FWP.product_id=products.id', 'left')
            // ->join('warehouses_products FWP', 'FWP.product_id=products.id', 'left')
            ->join('categories', 'categories.id=products.category_id', 'left')
            ->group_by('products.id');


        //if ($this->Settings->overselling) {
        $booksearch = strtolower($term);
        $wheres = array();
        $searchquery = explode(' ', $booksearch);
        foreach ($searchquery as $booksearch) {
            if (!empty(trim($booksearch))) {
                $wheres[] = "( {$this->db->dbprefix('products')}.name LIKE '%" . $booksearch . "%' OR  {$this->db->dbprefix('products')}.code LIKE '%" . $booksearch . "%'
                OR  {$this->db->dbprefix('products')}.product_details LIKE '%" . $booksearch . "%')";
            }
        }
        if (!empty($wheres)) {
            $this->db->or_where("(" . implode(' AND ', $wheres) . ")");
        }

        // $this->db->order_by('products.name ASC');
        if ($category_id != null && $category_id != 0) {
            $this->db->where('products.category_id', $category_id);
        }
        /*if ($pos) {
            $this->db->where('hide_pos !=', 1);
        }*/
        $this->db->where('hide !=', 1);
        $this->db->limit($limit);
        $q = $this->db->get('products');
        $checkCounter = 1;
        $oneString = '';
        if ($q !== FALSE && $q->num_rows() > 0) {
            foreach (($q->result()) as $row) {

                $data[] = $row;
            }
            return $data;
        }
    }

    public function get_all_records()
    {
        $this->db->select("*");
        $this->db->from('blog');

        $query = $this->db->get();
        if ($query->num_rows() > 0) {
            return $query->result_array();
        } else {
            return false;
        }
    }

    public function getProductLocation()
    {
        $this->db->select("cf2");
        $this->db->from('products');
        $query = $this->db->get();
        if ($query->num_rows() > 0) {
            return $query->result_array();
        } else {
            return false;
        }
    }

    public function saveRefundRecord($data)
    {
        $this->db->insert('refund', $data);
        return true;
    }

    public function getallCountryR()
    {

        $query = $this->db->get('countries');
        return $query->result();

    }

    public function getwharehouseID($cid)
    {
        $id = $this->db->where('country_id', $cid)
            ->get('warehouses_country')->row()->warehouses_id;


        $q = $this->db->get_where('warehouses', ['id' => $id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function salesarrAdd($id, $data = [])
    {
        return $this->db->update('sales', $data, ['id' => $id]);
    }

    public function getAramexSettings()
    {
        return $this->db->get_where('sma_aramex', ['id' => 1])->row();
    }

    public function getProductOnholdQty($product_id = null)
    {
        // return $product_id; 
        $this->db->select('SUM(quantity) AS total_quantity')
            ->where('product_id', $product_id)
            ->where('status', 'onhold');
        $row = $this->db->get('product_qty_onhold_request')->row();
        return $row->total_quantity;

    }

    public function get_notify_data($notify_email, $product_id) {
        // Check if the email already exists for the given product_id
        $this->db->where('email', $notify_email);
        $this->db->where('product_id', $product_id);
        $query = $this->db->get('out_of_stock_notify'); // 'sma_notify' is the name of your database table

        return $query->num_rows(); // Return the result as an associative array
    }

    public function insert_notify_data($data) {
        // Insert data into the database
        $this->db->insert('out_of_stock_notify', $data); // 'sma_notify' is the name of your database table

        return $this->db->insert_id(); // Return the ID of the inserted row
    }

}
