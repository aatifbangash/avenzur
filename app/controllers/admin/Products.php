<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Products extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        if (!$this->loggedIn) {
            $this->session->set_userdata('requested_page', $this->uri->uri_string());
            $url = "admin/login";
            if( $this->input->server('QUERY_STRING') ){
                $url = $url.'?'.$this->input->server('QUERY_STRING').'&redirect='.$this->uri->uri_string();
            }
           
            $this->sma->md($url);
        }
        $this->lang->admin_load('products', $this->Settings->user_language);
        $this->load->library('form_validation');
        $this->load->admin_model('products_model');
        $this->load->admin_model('settings_model');
        $this->load->admin_model('purchases_model');

        $this->digital_upload_path = 'files/';
        $this->upload_path = 'assets/uploads/';
        $this->thumbs_path = 'assets/uploads/thumbs/';
        $this->image_types = 'gif|jpg|jpeg|png|tif|webp';
        $this->digital_file_types = 'zip|psd|ai|rar|pdf|doc|docx|xls|xlsx|ppt|pptx|gif|jpg|jpeg|png|tif|txt|webp';
        $this->allowed_file_size = '1024000';
        $this->popup_attributes = ['width' => '900', 'height' => '600', 'window_name' => 'sma_popup', 'menubar' => 'yes', 'scrollbars' => 'yes', 'status' => 'no', 'resizable' => 'yes', 'screenx' => '0', 'screeny' => '0'];
    }

    public function oauth2callback()
    {
        $credentialsPath = 'assets/credentials/credentials.json';
        $client = new Google\Client();
        $client->setAuthConfigFile($credentialsPath);
        $client->setRedirectUri(admin_url() . 'products/oauth2callback');
        //$client->addScope(Google\Service\Drive::DRIVE_METADATA_READONLY);
        $client->setScopes(['https://www.googleapis.com/auth/content']);

        if (!isset($_GET['code'])) {
            // Get the product ID from the query parameters
            $auth_url = $client->createAuthUrl();
            header('Location: ' . filter_var($auth_url, FILTER_SANITIZE_URL));
        } else {
            $client->authenticate($_GET['code']);
            $_SESSION['google_access_token'] = $client->getAccessToken();
            $redirect_uri = admin_url() . 'products/google_merch_apis';
            header('Location: ' . filter_var($redirect_uri, FILTER_SANITIZE_URL));
        }
    }

    /*public function facebook_catalogue_push(){
        // Set the access token, product catalog ID, and API version
        $access_token = "EAAGF5LPatEwBO90n2xGJ2pZBOnMisRHxodMGMZABWb0e2RarluGu54VZAhdZCaYkQwfic9bfG7lj290r28zaryl5VTUkscrMplxXCeHpkhKJ8YJcZB3bWeoloB5ZC1X3SV6WUyW0zrKZAcufGKxEs66irz9XIDBY6yk3ntSKZArqvQ1Q3ZCxE1SFUrsjNFnOUnaG4";
        $product_catalog_id = "374060218547895";
        $api_version = "v19.0";

        $productCode = '076950450431';

        $productData = [
            'name' => 'Yogi Tea, Raspberry Leaf, Caffeine Free, 16 Tea Bags, 1.02 oz (29 g)',
            'description' => 'Supports the Reproductive System
            Caffeine Free
            Herbal Supplement
            Non-GMO Project Verified
            USDA Organic
            Certified Organic by QAI
            Kosher
            Vegan
            Certified B Corporation
            Find Support With Raspberry Leaf
            
            Raspberry Leaf has been traditionally used by midwives and Western herbalists during pregnancy as well as to help ease the discomfort of menstruation, and to support the uterus. Enjoy the pleasant, earthy-sweet flavor of Woman\'s Raspberry Leaf tea to support the female system.
            
            At Yogi, it\'s about more than creating deliciously purposeful teas.
            
            Yogi Principles
            
            We blend with intention. Our flavorful teas are created to support body and mind.
            
            We believe in the synergetic benefit of herbs, combining ingredients to enhance their wellness-supporting potential.
            
            We blend the best of what nature has to offer using the finest spices and botanicals from around the globe.',
            'availability' => 'in stock',
            'condition' => 'new',
            'price' => 2160,
            'sale_price' => 2160,
            'currency' => 'SAR',
            'url' => 'https://avenzur.com/product/yogi-tea-raspberry-leaf-caffeine-free-16-tea-bags-1-02-oz-29-g-IH-61',
            'image_url' => 'https://avenzur.com/assets/uploads/44d5a46bfc8759746b0e8c96440eb856.avif',
            'brand' => 'Yogi Tea',
            'gtin' => '076950450431',
            'retailer_id' => '076950450431',
            'inventory' => 3
        ];

        $filter = ["retailer_id" => ['eq' => $productCode]];
        //$queryUrl = "https://graph.facebook.com/{$api_version}/{$product_catalog_id}/products/{$productCode}?access_token={$access_token}";
        $queryUrl = "https://graph.facebook.com/{$api_version}/{$product_catalog_id}/products?fields=category,name,errors&filter=".urlencode(json_encode($filter))."&summary=true&access_token={$access_token}";

        $ch = curl_init($queryUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        if (curl_errno($ch)) {
            echo 'Curl error: ' . curl_error($ch);
        }else{
            $product = json_decode($response, true);
            if ($product && sizeOf($product['data']) > 0) {
                // Update if product exists
                $productID = $product['data'][0]['id'];
                $updateUrl = "https://graph.facebook.com/{$api_version}/{$product_catalog_id}/items_batch/{$productID}?access_token={$access_token}";
                $updateData = json_encode($productData);

                $ch = curl_init($updateUrl);
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT'); // Use PUT method for update
                curl_setopt($ch, CURLOPT_POSTFIELDS, $updateData);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

                $response = curl_exec($ch);
            }else{
                // Insert if product does not exit
                $url = "https://graph.facebook.com/{$api_version}/{$product_catalog_id}/products";

                $ch = curl_init($url);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($productData));
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, [
                    'Content-Type: application/x-www-form-urlencoded',
                    'Authorization: Bearer ' . $access_token
                ]);
                $response = curl_exec($ch);

            }
        }

        if(curl_errno($ch)){
            echo 'Curl error: ' . curl_error($ch);
        }

        curl_close($ch);
        if ($response === false) {
            echo "Error pushing product to Facebook catalog.";
        } else {
            $result = json_decode($response, true);
            print_r($result);
        }
    }*/

    public function facebook_catalogue_read()
    {
        // Set the parameters
        //$fields = ["category", "name", "errors"];
        $filter = ["name" => ["i_contains" => "sulfad"]];
        $summary = true;
        $access_token = "EAAGF5LPatEwBO90n2xGJ2pZBOnMisRHxodMGMZABWb0e2RarluGu54VZAhdZCaYkQwfic9bfG7lj290r28zaryl5VTUkscrMplxXCeHpkhKJ8YJcZB3bWeoloB5ZC1X3SV6WUyW0zrKZAcufGKxEs66irz9XIDBY6yk3ntSKZArqvQ1Q3ZCxE1SFUrsjNFnOUnaG4";
        $product_catalog_id = "374060218547895";
        //$api_version = "v19.0";
        $api_version = "v19.0";

        // Build the query string
        $query_params = http_build_query([
            'fields' => json_encode($fields),
            'filter' => json_encode($filter),
            'summary' => $summary,
            'access_token' => $access_token
        ]);

        // Construct the URL
        $url = "https://graph.facebook.com/{$api_version}/{$product_catalog_id}/products?{$query_params}";

        // Initialize cURL session
        $ch = curl_init($url);

        // Set cURL options
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        // Execute the request
        $response = curl_exec($ch);

        // Check for errors
        if (curl_errno($ch)) {
            echo 'Curl error: ' . curl_error($ch);
        }

        // Close cURL session
        curl_close($ch);

        // Handle the response
        if ($response === false) {
            // Request failed
            echo "Error fetching products.";
        } else {
            // Parse and process the JSON response
            $products = json_decode($response, true);
            // Handle the products data as needed
            print_r($products);
        }
        exit;
    }

    public function facebook_catalogue_push()
    {
        $product_id = $_POST['id'];
        // Set the access token, product catalog ID, and API version
        $access_token = "EAAGF5LPatEwBOZCVaah25RxvwPQxUjHYrgLN7a1clUAn8FHxalGnSBKVMVM1oEbbZAaVw2keCGKBtHBAzIemXLv1xxK5LlQa4mLhCzHAkhUjkQiViZAthQJklWSd0wFkplf27wZB8J8rHgKDPes2ZBcOZApxoOhgtZBjkaesZBdXEXj2Tgfn7QsF4YZAl8NUBscsK";
        $product_catalog_id = "374060218547895";
        $api_version = "v19.0";

        $product_photos = $this->products_model->getProductPhotos($product_id);
        $product_details = $this->products_model->getProductByID($product_id);
        $brand_details = $this->products_model->getBrandByID($product_details->brand);

        $photos_arr = array();

        foreach ($product_photos as $photo) {
            //array_push(base_url().'assets/uploads/'.$photo->photo, $photos_arr);
            array_push($photos_arr, site_url() . 'assets/uploads/' . $photo->photo);
        }

        $product_details->details = str_replace('<p><strong>Highlights:</strong></p>', '', $product_details->details);
        $product_details->details = str_replace('<p>', '', $product_details->details);
        $product_details->details = str_replace('</p>', '', $product_details->details);
        $product_details->details = str_replace('<ul>', '', $product_details->details);
        $product_details->details = str_replace('</ul>', '', $product_details->details);
        $product_details->details = str_replace('<li>', '', $product_details->details);
        $product_details->details = str_replace('</li>', '', $product_details->details);

        $tax_details = $this->site->getTaxRateByID($product_details->tax_rate);

        // Define the product data
        $productCode = $product_details->code; // Assuming this is the unique identifier for your product

        if ($product_details->quantity > 0) {
            $availibility = 'in stock';
        } else {
            $availibility = 'out of stock';
        }

        $productData = [
            'name' => $product_details->name,
            'description' => strip_tags($product_details->details),
            'availability' => $availibility,
            'condition' => 'new',
            'currency' => 'SAR',
            'url' => site_url() . 'product/' . $product_details->slug,
            'image_url' => site_url() . 'assets/uploads/' . $product_details->image,
            'brand' => $brand_details->name,
            'gtin' => $productCode,
            //'retailer_id' => $productCode,
            'inventory' => (int) $product_details->quantity
        ];

        // Consider tax in prices

        if ($product_details->tax_method == '1' && $tax_details->rate > 0) {
            $productTaxPercent = $tax_details->rate;

            if ($product_details->promotion == 1) {
                $productPromoPrice = $product_details->promo_price;
                $promoProductTaxAmount = $productPromoPrice * ($productTaxPercent / 100);
                $product_details->promo_price = $productPromoPrice + $promoProductTaxAmount;
            }

            $productPrice = $product_details->price;
            $productTaxAmount = $productPrice * ($productTaxPercent / 100);
            $product_details->price = $productPrice + $productTaxAmount;
        }

        // Calculate prices according to Facebook requirements
        $price = (int) ($product_details->price * 100); // Convert to cents
        $productData['price'] = $price;

        if ($product_details->promotion == 1) {
            $sale_price = (int) ($product_details->promo_price * 100); // Convert to cents
            $productData['sale_price'] = $sale_price;
        }

        $filter = ["retailer_id" => ['eq' => $productCode]];
        $queryUrl = "https://graph.facebook.com/{$api_version}/{$product_catalog_id}/products?fields=category,name,errors&filter=" . urlencode(json_encode($filter)) . "&summary=true&access_token={$access_token}";

        $ch = curl_init($queryUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        if (curl_errno($ch)) {
            echo 'Curl error: ' . curl_error($ch);
        } else {
            $product = json_decode($response, true);
            if ($product && sizeOf($product['data']) > 0) {
                // update existing date
                $requestData = [
                    [
                        'method' => 'UPDATE',
                        'retailer_id' => $productCode, // The retailer_id of the product to update
                        'data' => $productData
                    ]
                    // You can add more requests for other items here...
                ];

                $url = "https://graph.facebook.com/{$api_version}/{$product_catalog_id}/batch";
                $ch = curl_init($url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(['requests' => json_encode($requestData), 'access_token' => $access_token]));

                $response = curl_exec($ch);
                curl_close($ch);

                // Handle the response
                if ($response === false) {
                    $this->session->set_flashdata('error', lang('error connecting to meta'));
                    admin_redirect('products/edit/' . $product_id);
                } else {
                    $this->session->set_flashdata('message', lang('product pushed to meta'));
                    admin_redirect('products/edit/' . $product_id);
                }
            } else {
                // Insert if product does not exit
                $productData['retailer_id'] = $productCode;
                $url = "https://graph.facebook.com/{$api_version}/{$product_catalog_id}/products";

                $ch = curl_init($url);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($productData));
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, [
                    'Content-Type: application/x-www-form-urlencoded',
                    'Authorization: Bearer ' . $access_token
                ]);
                $response = curl_exec($ch);
                curl_close($ch);

                // Handle the response
                if ($response === false) {
                    $this->session->set_flashdata('error', lang('error connecting to meta'));
                    admin_redirect('products/edit/' . $product_id);
                } else {
                    $this->session->set_flashdata('message', lang('product pushed to meta'));
                    admin_redirect('products/edit/' . $product_id);
                }
            }
        }

    }

    public function google_merch_apis()
    {
        $product_id = $_REQUEST['id'];

        // Debugging: Ensure the product ID is being received correctly
        if (empty($product_id)) {
            $product_id = $this->session->userdata('merch_id');
            $this->session->unset_userdata('merch_id');
        } else {
            $this->session->set_userdata('merch_id', $product_id);
        }

        // Fetch product details and related information
        $product_photos = $this->products_model->getProductPhotos($product_id);
        $product_details = $this->products_model->getProductByID($product_id);
        $brand_details = $this->products_model->getBrandByID($product_details->brand);
        $tax_details = $this->site->getTaxRateByID($product_details->tax_rate);

        $photos_arr = array();
        foreach ($product_photos as $photo) {
            array_push($photos_arr, site_url() . 'assets/uploads/' . $photo->photo);
        }

        // Clean product details description
        $product_details->details = str_replace(['<p><strong>Highlights:</strong></p>', '<p>', '</p>', '<ul>', '</ul>', '<li>', '</li>'], '', $product_details->details);

        $clientId = '216256641186-ord7an72cbi6jhtrhmb1knb93jbera1p.apps.googleusercontent.com';
        $clientSecret = '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';

        // Initialize Google Client
        $credentialsPath = 'assets/credentials/credentials.json';
        $client = new Google\Client();
        $client->setAuthConfig($credentialsPath);
        $client->setAccessType('offline');
        $client->setScopes(['https://www.googleapis.com/auth/content']);

        if (isset($_SESSION['google_access_token']) && $_SESSION['google_access_token']) {
            $client->setAccessToken($_SESSION['google_access_token']);

            $contentService = new Google_Service_ShoppingContent($client);
            $merchantId = '5086892798';
            $productContentId = 'online:en:SA:' . $product_details->code;

            $productContent = new Google_Service_ShoppingContent_Product();

            if ($product_details->tax_method == '1' && $tax_details->rate > 0) {
                $productTaxPercent = $tax_details->rate;

                if ($product_details->promotion == 1) {
                    $productPromoPrice = $product_details->promo_price;
                    $promoProductTaxAmount = $productPromoPrice * ($productTaxPercent / 100);
                    $product_details->promo_price = $productPromoPrice + $promoProductTaxAmount;
                }

                $productPrice = $product_details->price;
                $productTaxAmount = $productPrice * ($productTaxPercent / 100);
                $product_details->price = $productPrice + $productTaxAmount;
            }

            if ($product_details->promotion == 1) {
                $salePriceFinal = $product_details->promo_price;
            } else {
                $salePriceFinal = $product_details->price;
            }

            $productData = [
                'channel' => 'online',
                'contentLanguage' => 'en',
                'targetCountry' => 'SA',
                'offerId' => $product_details->code,
                'title' => $product_details->name,
                'description' => $product_details->details,
                'link' => site_url() . 'product/' . $product_details->slug,
                'imageLink' => site_url() . 'assets/uploads/' . $product_details->image,
                'brand' => $brand_details->name,
                'price' => [
                    'value' => $product_details->price,
                    'currency' => 'SAR',
                ],
                'salePrice' => [
                    'value' => $salePriceFinal,
                    'currency' => 'SAR',
                ],
                'additionalImageLinks' => $photos_arr,
                'availability' => 'in stock',
            ];


            try {
                // Attempt to get the existing product
                $existingProduct = $contentService->products->get($merchantId, $productContentId);

                // If product exists, update the existing product
                if ($existingProduct) {
                    //$productContent->setOfferId($productData['offerId']);
                    //$productContent->setChannel($productData['channel']);
                    //$productContent->setContentLanguage($productData['contentLanguage']);
                    //$productContent->setTargetCountry($productData['targetCountry']);
                }
            } catch (Google\Service\Exception $e) {
                if ($e->getCode() == 404) {
                    // Product not found, prepare to insert a new one
                    $productContent->setOfferId($productData['offerId']);
                    $productContent->setChannel($productData['channel']);
                    $productContent->setContentLanguage($productData['contentLanguage']);
                    $productContent->setTargetCountry($productData['targetCountry']);
                } else {
                    // Other errors should be handled appropriately
                    echo "Error fetching product: " . $e->getMessage();
                    return;
                }
            }

            // Set remaining product data
            $productContent->setTitle($productData['title']);
            $productContent->setDescription($productData['description']);
            $productContent->setLink($productData['link']);
            $productContent->setImageLink($productData['imageLink']);
            $productContent->setBrand($productData['brand']);

            $price = new Google_Service_ShoppingContent_Price();
            $price->setValue($productData['price']['value']);
            $price->setCurrency($productData['price']['currency']);
            $productContent->setPrice($price);

            if (!empty($productData['salePrice']['value'])) {
                $salePrice = new Google_Service_ShoppingContent_Price();
                $salePrice->setValue($productData['salePrice']['value']);
                $salePrice->setCurrency($productData['salePrice']['currency']);
                $productContent->setSalePrice($salePrice);
            }

            $productContent->setAvailability($productData['availability']);
            $productContent->setAdditionalImageLinks($productData['additionalImageLinks']);

            try {
                if (isset($existingProduct->id)) {
                    // Update the product if it exists
                    $contentService->products->update($merchantId, $productContentId, $productContent);
                    $this->session->set_flashdata('message', lang('product_updated'));
                } else {
                    // Insert the new product
                    $contentService->products->insert($merchantId, $productContent);
                    $this->session->set_flashdata('message', lang('product_inserted'));
                }
                admin_redirect('products/edit/' . $product_id);
            } catch (Exception $e) {
                echo "Error inserting/updating product: " . $e->getMessage();
            }
        } else {
            $redirect_uri = admin_url() . 'products/oauth2callback';
            header('Location: ' . filter_var($redirect_uri, FILTER_SANITIZE_URL));
        }
    }
    public function snapchat_catalog()
    {
        $product_id = $_REQUEST['val'];
        // Debugging: Ensure the product ID is being received correctly
        if (empty($product_id)) {
            $product_id = $this->session->userdata('val');
            $this->session->unset_userdata('val');
        } else {
            $this->session->set_userdata('merch_id', $product_id[0]);
        }
        try {
            $response = $this->getCSVData();
            $csvData = $response['csvData'];
            $firstHeader = $response['firstHeader'];
            $secondHeader = $response['secondHeader'];
            $type = 'in stock';
            $this->process_csv_data($firstHeader, $secondHeader, $csvData, $type);
            $this->session->set_flashdata('message', $this->lang->line('Added in catalog'));
            admin_redirect('products/edit/' . $product_id[0]);
        } catch (Exception $e) {
            echo "Error inserting/updating product: " . $e->getMessage();
        }
    }

    public function update_product_codes()
    {
        $csvFile = $this->upload_path . 'csv/avenzur-code-and-retaj-code.csv';

        if (!file_exists($csvFile)) {
            echo 'CSV file not found.';
            return;
        }

        $handle = fopen($csvFile, 'r');

        if ($handle === false) {
            echo 'Error opening CSV file.';
            return;
        }

        $count = 0;

        while (($rowData = fgetcsv($handle)) !== false) {
            $avenzurCode = $rowData[1];
            $retajCode = $rowData[5];

            $this->db->select('*');
            $this->db->from('sma_products');
            $this->db->where('code', $avenzurCode);
            $query = $this->db->get();
            $product = $query->row();

            if ($product) {

                $dataToUpdate = [
                    'avenzur_code' => $avenzurCode,
                    'code' => $retajCode
                ];

                //$this->db->where('id', $product->id);
                //$this->db->update('sma_products', $dataToUpdate);

                echo "Product with code $avenzurCode has updated name now i.e $retajCode<br>";
            } else {
                echo "Product with code $avenzurCode was not found in database<br>";
            }

            $count++;
        }

        fclose($handle);
    }

    public function update_intl_barcode()
    {

        //$csvFile = 'https://avenzur.com/assets/uploads/temp/iherb_updated.csv';
        //$csvFile = '/var/www/backup25May2023/assets/uploads/temp/iherb_updated.csv';

        $csvFile = $this->upload_path . 'temp/localizer_to_be_checked.csv';

        if (!file_exists($csvFile)) {
            echo 'CSV file not found.';
            return;
        }

        // Read the CSV file
        $handle = fopen($csvFile, 'r');

        // Check if the file was opened successfully
        if ($handle === false) {
            echo 'Error opening CSV file.';
            return;
        }

        // Iterate through rows in the CSV file
        while (($rowData = fgetcsv($handle)) !== false) {
            // Assuming 'B' and 'C' are the columns for 'code' and 'ic' respectively
            $ibarCode = $rowData[0];
            $asconCode = $rowData[1];
            $itemName = $rowData[2];
            $itemPrice = $rowData[3];
            //$itemVat = $rowData[4];

            $tax_rate = $rowData[4] == 0 ? 1 : 5;

            // Find the product in the database based on the code
            $this->db->select('*');
            $this->db->from('sma_products');
            //$this->db->where('CAST(code AS UNSIGNED) = ' . (int)$ibarCode, NULL, FALSE);
            $this->db->where('code', $ibarCode);
            $query = $this->db->get();
            $product = $query->row();

            if ($product) {
                echo "Product found with IBC $ibarCode and will not be updated.<br>";
                //echo $ibarCode."<br>";
            } else {
                //echo "Product not found in system with IBC $ibarCode <br>";
                $this->db->select('*');
                $this->db->from('sma_products');
                $this->db->where('CAST(code AS UNSIGNED) = ' . (int) $asconCode, NULL, FALSE);
                $query_new = $this->db->get();
                $product_new = $query_new->row();

                if ($product_new) {
                    // Update the code in the database with the ic from CSV
                    $dataToUpdate = [
                        'code' => $ibarCode,
                        'ascon_code' => $asconCode
                    ];

                    $this->db->where('id', $product_new->id);
                    $this->db->update('sma_products', $dataToUpdate);
                    echo "Product with code $asconCode will be updated with the IBC $ibarCode<br>";
                } else {
                    echo "Product not found in system with IBC $ibarCode and Ascon Code $asconCode <br>";
                }
            }
        }

        // Close the file handle
        fclose($handle);
    }

    /*public function update_intl_barcode(){

        //$csvFile = 'https://avenzur.com/assets/uploads/temp/iherb_updated.csv';
        //$csvFile = '/var/www/backup25May2023/assets/uploads/temp/iherb_updated.csv';

        $csvFile = $this->upload_path.'temp/12-may-upload-file.csv';
        
        if (!file_exists($csvFile)) {
            echo 'CSV file not found.';
            return;
        }
    
        // Read the CSV file
        $handle = fopen($csvFile, 'r');
    
        // Check if the file was opened successfully
        if ($handle === false) {
            echo 'Error opening CSV file.';
            return;
        }
    
        // Iterate through rows in the CSV file
        while (($rowData = fgetcsv($handle)) !== false) {
            // Assuming 'B' and 'C' are the columns for 'code' and 'ic' respectively
            $excelCode = $rowData[1]; // CSV is 0-indexed
            //$excelCode = ltrim($excelCode, '0');

            $tax_rate = $rowData[6] == 0 ? 1 : 5;
            $ascon_code = isset($rowData[11]) ? $rowData[11] : '';
            $imported = 1;
            $source = isset($rowData[10]) ? $rowData[10] : '';
    
            // Find the product in the database based on the code
            $this->db->select('*');
            $this->db->from('sma_products');
            $this->db->where('CAST(code AS UNSIGNED) = ' . (int)$excelCode, NULL, FALSE);
            $query = $this->db->get();
            $product = $query->row();
            
            if ($product) {
                // Update the code in the database with the ic from CSV
                $dataToUpdate = [
                    'tax_rate' => $tax_rate,
                    'ascon_code' => $ascon_code,
                    'imported' => $imported
                    //'source' => $source
                ];
    
                $this->db->where('id', $product->id);
                $this->db->update('sma_products', $dataToUpdate);
                echo "Updated product with code $excelCode. Tax rate: $tax_rate<br>";
            } else {
                echo "Product with code $excelCode not found in the database.<br>";
            }
        }
    
        // Close the file handle
        fclose($handle);
    }*/

    public function setProductSlugs()
    {
        $products = $this->products_model->getAllProducts();

        foreach ($products as $product) {
            $slug = preg_replace('/[^a-zA-Z0-9]+/', '-', $product->name);
            $slug = strtolower($slug);
            $slug = trim($slug, '-');
            $slug = $slug . '-' . $product->code;

            $this->products_model->updateProductSlugs($slug, $product->id);
        }

        echo 'Products Slugs updated...';
    }

    public function setProductImages()
    {
        $images = "111004213, 121017310-a, 121017310, 121019636, 121019708, 121019754, 121019756, 121020664, 121020673, 121020722, 121020838, 121020904, 121020905, 131000049";
        $imgArr = explode(",", $images);

        $this->products_model->updateProductImages($imgArr);
    }

    public function convertImagesThumbnails()
    {
        $images = "23f464d2ca3d69f8f160003dcb22c11b.jpg,73d17cc2bdc0a0e3906469fc4842c62f.jpeg,1b6669198a4df7bcd386573e6a011ba5.jpeg,082f4eb07d3ec5686e196e36bf240f77.jpeg,3e83e39bc7b2df23f090e737d38abef4.jpeg,1abacbdb5e6b1815b428008d7334a9a5.jpeg,84d9ec341c62b8f443b9cdaa1f49a770.jpeg,acbb8bfe2179ea03ad66dbb086c8f72a.jpeg,da8b7250b5ca3c919bdbd0a444c3f9c9.jpeg,a67dd40afc0aeef53a1222cb80c01b27.jpeg,9b0ec372041c805dada856cd0ba83438.jpeg,2e2b4769f47726f01ee1ec487b5d5206.jpeg,121019357.jpg,121019397.jpg,121019395.jpg,121019247.jpg,121019379.jpg,121019289.jpg,121018812.jpg,121018815.jpg,121018811.jpg,121019238.jpg,121018813.jpg,121018445.jpg,121018439.jpg,121018440.jpg,121020653.jpg,121020665.jpg,121020664.jpg,121020661.jpg,121017353.jpg,121018192.jpg,121020652.jpg,121017354.jpg,121020651.jpg,121020656.jpg,121020650.jpg,121017300.jpg,121016350.jpg,142000018.jpg,121015629.jpg,121016545.jpg,142000026.jpg,121005959.jpg,121019636.jpg,121021031.jpg,121018302.jpg,121018936.jpg,121019215.jpg,121016424.jpg,121019217.jpg,121017317.jpg,121020918.jpg,121019642.jpg,121005288.jpg,121004766.jpg,143000349.jpg,121017723.jpg,121018789.jpg,121004326.jpg,121017711.jpg,143000301.jpg,121013897.jpg,121012967.jpg,131000005.jpg,131000224.jpg,121012563.jpg,121002237.jpg,121016365.jpg,121000237.jpg,121002190.jpg,121015829.jpg,121018761.jpg,121019061.jpg,121017751.jpg,121017154.jpg,121018483.jpg,121017014.jpg,121017779.jpg,121016783.jpg,131000049.jpg,121019754.jpg,121020774.jpg,121020722.jpg,121019143.jpg,121019146.jpg,121021028.jpg,121019213.jpg,121018788.jpg,121018201.jpg,121020003.jpg,121020762.jpg,121017524.jpg,121021016.jpg,121021022.jpg,121020154.jpg,121019708.jpg,121019097.jpg,121017787.jpg,121017526.jpg,121015797.jpg,121014506.jpg,121019756.jpg,121011337.jpg,121018882.jpg,121013745.jpg,121019802.jpg,121021013.jpg,121019622.jpg,121004780.jpg,121004824.jpg,121011052.jpg,121011051.jpg,121018751.jpg,121020838.jpg,121021005.jpg,121021035.jpg,121017310.jpg,121020002.jpg,121014960.jpg,121001580.jpg,121020834.jpg,121019741.jpg,121020835.jpg,121002526.jpg,121014092.jpg,121002546.jpg,121014081.jpg,111001698.jpg,111003332.jpg,121017616.jpg,121018498.jpg,121020726.jpg,121018883.jpg,121019155.jpg,121020625.jpg,111004003.jpg,111004178.jpg,111002372.jpg,111004444.jpg,111002659.jpg,111004398.jpg,121020809.jpg,111003308.jpg,111003893.jpg,111004390.jpg,ad7dfd0cf50d6f6dddd83f2f021be8ee.jpg,111004028.jpg,111004378.jpg,111004450.jpg,111004213.jpg,111001040.jpg,111003744.jpg,111004177.jpg,121020919.jpg,111003816.jpg,111001419.jpg,111004401.jpg,111004402.jpg,111004380.jpg,151001470.jpg,121019558.jpg,151000530.jpg,151001776.jpg,151001777.jpg,121020898.jpg,121020904.jpg,121020910.jpg,121021087.jpg,121021088.jpg,121021001.jpg,121020899.jpg,121019219.jpg,121020905.jpg,121020921.jpg,121020669.jpg,121020670.jpg,121020673.jpg";

        $this->load->library('image_lib');
        $imgArr = explode(",", $images);

        foreach ($imgArr as $imageFilename) {
            $config = null;
            $config['image_library'] = 'gd2';
            $config['maintain_ratio'] = true;
            $config['width'] = $this->Settings->twidth;
            $config['height'] = $this->Settings->theight;
            $data['image'] = $imageFilename;
            $config['source_image'] = $this->upload_path . $imageFilename;
            $config['new_image'] = $this->thumbs_path . $imageFilename;

            $this->image_lib->clear();
            $this->image_lib->initialize($config);
            if (!$this->image_lib->resize()) {
                echo $this->image_lib->display_errors();
            }
        }
    }

    public function convertImagesThumbs()
    {
        // Set the path to the directory containing your images
        $imageDirectory = $this->upload_path;

        // Get the list of files in the directory
        $files = scandir($imageDirectory);

        // Remove '.' and '..' from the list
        $files = array_diff($files, array('.', '..'));

        $this->load->library('image_lib');

        foreach ($files as $imageFilename) {
            // Process only image files (you may need to adjust this condition based on your file types)
            if (is_file($imageDirectory . $imageFilename) && in_array(pathinfo($imageFilename, PATHINFO_EXTENSION), ['jpg', 'jpeg', 'png', 'gif'])) {
                $config = null;
                $config['image_library'] = 'gd2';
                $config['maintain_ratio'] = true;
                $config['width'] = $this->Settings->twidth;
                $config['height'] = $this->Settings->theight;

                $config['source_image'] = $imageDirectory . $imageFilename;
                $config['new_image'] = $this->thumbs_path . $imageFilename;

                $this->image_lib->clear();
                $this->image_lib->initialize($config);

                if (!$this->image_lib->resize()) {
                    echo $this->image_lib->display_errors();
                }
            }
        }
    }


    /*public function convertImagesThumbs(){
        $images = "23f464d2ca3d69f8f160003dcb22c11b.jpg,73d17cc2bdc0a0e3906469fc4842c62f.jpeg";
 
        $this->load->library('image_lib');
        $imgArr = explode(",",$images);

        foreach ($imgArr as $imageFilename) {
            $config = null;
            $config['image_library']  = 'gd2';
            $config['maintain_ratio'] = true;
            $config['width']          = $this->Settings->twidth;
            $config['height']         = $this->Settings->theight;
            $data['image'] = $imageFilename;
            $config['source_image']   = $this->upload_path . $imageFilename;
            $config['new_image']      = $this->thumbs_path . $imageFilename;

            $this->image_lib->clear();
            $this->image_lib->initialize($config);
            if (!$this->image_lib->resize()) {
                echo $this->image_lib->display_errors();
            }
        }
    }*/

    /* ------------------------------------------------------- */
    public function add($id = null)
    {
        $this->sma->checkPermissions();
        $this->load->helper('security');
        $warehouses = $this->site->getAllWarehouses();
        $this->form_validation->set_rules('category', lang('category'), 'required|is_natural_no_zero');
        if ($this->input->post('type') == 'standard') {
            //$this->form_validation->set_rules('cost', lang('product_cost'), 'required');
            $this->form_validation->set_rules('unit', lang('product_unit'), 'required');
        }
        $this->form_validation->set_rules('code', lang('product_code'), 'is_unique[products.code]|alpha_dash');
        if (SHOP) {
            $this->form_validation->set_rules('slug', lang('slug'), 'required|is_unique[products.slug]|alpha_dash');
        }
        $this->form_validation->set_rules('weight', lang('weight'), 'numeric');
        $this->form_validation->set_rules('product_image', lang('product_image'), 'xss_clean');
        $this->form_validation->set_rules('digital_file', lang('digital_file'), 'xss_clean');
        $this->form_validation->set_rules('userfile', lang('product_gallery_images'), 'xss_clean');
        if ($this->form_validation->run() == true) {

            $product_countries = '';
            foreach ($this->input->post('cf1') as $pcountry) {
                if ($pcountry == '0') {
                    $product_countries = 0;
                    break;
                }
                $product_countries .= $pcountry . ',';
            }

            $product_countries = rtrim($product_countries, ',');

            $tax_rate = $this->input->post('tax_rate') ? $this->site->getTaxRateByID($this->input->post('tax_rate')) : null;
            $data = [
                'code' => $this->input->post('code'),
                'barcode_symbology' => $this->input->post('barcode_symbology'),
                'name' => $this->input->post('name'),
                'type' => $this->input->post('type'),
                'brand' => $this->input->post('brand'),
                'category_id' => $this->input->post('category'),
                'subcategory_id' => $this->input->post('subcategory') ? $this->input->post('subcategory') : null,
                'cost' => $this->sma->formatDecimal($this->input->post('price')),
                'price' => $this->sma->formatDecimal($this->input->post('price')),
                'unit' => $this->input->post('unit'),
                'sale_unit' => $this->input->post('default_sale_unit'),
                'purchase_unit' => $this->input->post('default_purchase_unit'),
                'tax_rate' => $this->input->post('tax_rate'),
                'tax_method' => $this->input->post('tax_method'),
                'alert_quantity' => $this->input->post('alert_quantity'),
                'track_quantity' => $this->input->post('track_quantity') ? $this->input->post('track_quantity') : '0',
                'details' => $_POST['details'], //$this->input->post('details',false),
                'product_details' => $_POST['product_details'], //$this->input->post('product_details',false),
                'incentive_qty' => $this->input->post('incentive_qty'),
                'incentive_value' => $this->input->post('incentive_value'),
                'supplier1' => $this->input->post('supplier'),
                'supplier1price' => $this->sma->formatDecimal($this->input->post('supplier_price')),
                'supplier2' => $this->input->post('supplier_2'),
                'supplier2price' => $this->sma->formatDecimal($this->input->post('supplier_2_price')),
                'supplier3' => $this->input->post('supplier_3'),
                'supplier3price' => $this->sma->formatDecimal($this->input->post('supplier_3_price')),
                'supplier4' => $this->input->post('supplier_4'),
                'supplier4price' => $this->sma->formatDecimal($this->input->post('supplier_4_price')),
                'supplier5' => $this->input->post('supplier_5'),
                'supplier5price' => $this->sma->formatDecimal($this->input->post('supplier_5_price')),
                'cf1' => $product_countries,//$this->input->post('cf1'),
                'cf2' => $this->input->post('cf2'),
                'cf3' => $this->input->post('cf3'),
                'cf4' => $this->input->post('cf4'),
                'cf5' => $this->input->post('cf5'),
                'cf6' => $this->input->post('cf6'),
                'promotion' => $this->input->post('promotion'),
                'promo_price' => $this->sma->formatDecimal($this->input->post('promo_price')),
                'start_date' => $this->input->post('start_date') ? $this->sma->fsd($this->input->post('start_date')) : null,
                'end_date' => $this->input->post('end_date') ? $this->sma->fsd($this->input->post('end_date')) : null,
                'supplier1_part_no' => $this->input->post('supplier_part_no'),
                'supplier2_part_no' => $this->input->post('supplier_2_part_no'),
                'supplier3_part_no' => $this->input->post('supplier_3_part_no'),
                'supplier4_part_no' => $this->input->post('supplier_4_part_no'),
                'supplier5_part_no' => $this->input->post('supplier_5_part_no'),
                'file' => $this->input->post('file_link'),
                'slug' => $this->input->post('slug'),
                'weight' => $this->input->post('weight'),
                'featured' => $this->input->post('featured'),
                'special_offer' => $this->input->post('special_offer'),
                'hsn_code' => $this->input->post('hsn_code'),
                'hide' => $this->input->post('hide') ? $this->input->post('hide') : 0,
                'second_name' => $this->input->post('second_name'),
                'trade_name' => $this->input->post('trade_name'),
                'manufacture_name' => $this->input->post('manufacture_name'),
                'main_agent' => $this->input->post('main_agent'),
                'draft' => $this->input->post('draft'),
                'special_product' => $this->input->post('special_product'),
                // 'purchase_account'   => $this->input->post('purchase_account'),
                // 'sale_account'       => $this->input->post('sale_account'),
                // 'inventory_account'  => $this->input->post('inventory_account'),
            ];

            if ($this->input->post('name_ar') != '') {
                $data['name_ar'] = $this->input->post('name_ar');
            }
            if ($this->input->post('product_details_ar') != '') {
                $data['product_details_ar'] = $this->input->post('product_details_ar');
            }
            $warehouse_qty = null;
            $product_attributes = null;
            $this->load->library('upload');
            if ($this->input->post('type') == 'standard') {
                $wh_total_quantity = 0;
                $pv_total_quantity = 0;
                for ($s = 2; $s > 5; $s++) {
                    $data['suppliers' . $s] = $this->input->post('supplier_' . $s);
                    $data['suppliers' . $s . 'price'] = $this->input->post('supplier_' . $s . '_price');
                }
                foreach ($warehouses as $warehouse) {
                    if ($this->input->post('wh_qty_' . $warehouse->id)) {
                        $warehouse_qty[] = [
                            'warehouse_id' => $this->input->post('wh_' . $warehouse->id),
                            'quantity' => $this->input->post('wh_qty_' . $warehouse->id),
                            'rack' => $this->input->post('rack_' . $warehouse->id) ? $this->input->post('rack_' . $warehouse->id) : null,
                        ];
                        $wh_total_quantity += $this->input->post('wh_qty_' . $warehouse->id);
                    }
                }

                if ($this->input->post('attributes')) {
                    $a = sizeof($_POST['attr_name']);
                    for ($r = 0; $r <= $a; $r++) {
                        if (isset($_POST['attr_name'][$r])) {
                            $product_attributes[] = [
                                'name' => $_POST['attr_name'][$r],
                                'warehouse_id' => $_POST['attr_warehouse'][$r],
                                'quantity' => $_POST['attr_quantity'][$r],
                                'price' => $_POST['attr_price'][$r],
                            ];
                            $pv_total_quantity += $_POST['attr_quantity'][$r];
                        }
                    }
                } else {
                    $product_attributes = null;
                }

                /*if ($wh_total_quantity != $pv_total_quantity && $pv_total_quantity != 0) {
                    $this->form_validation->set_rules('wh_pr_qty_issue', 'wh_pr_qty_issue', 'required');
                    $this->form_validation->set_message('required', lang('wh_pr_qty_issue'));
                }*/
            }

            if ($this->input->post('type') == 'service') {
                $data['track_quantity'] = 0;
            } elseif ($this->input->post('type') == 'combo') {
                $total_price = 0;
                $c = sizeof($_POST['combo_item_code']) - 1;
                for ($r = 0; $r <= $c; $r++) {
                    if (isset($_POST['combo_item_code'][$r]) && isset($_POST['combo_item_quantity'][$r]) && isset($_POST['combo_item_price'][$r])) {
                        $items[] = [
                            'item_code' => $_POST['combo_item_code'][$r],
                            'quantity' => $_POST['combo_item_quantity'][$r],
                            'unit_price' => $_POST['combo_item_price'][$r],
                        ];
                    }
                    $total_price += $_POST['combo_item_price'][$r] * $_POST['combo_item_quantity'][$r];
                }
                if ($this->sma->formatDecimal($total_price) != $this->sma->formatDecimal($this->input->post('price'))) {
                    $this->form_validation->set_rules('combo_price', 'combo_price', 'required');
                    $this->form_validation->set_message('required', lang('pprice_not_match_ciprice'));
                }
                $data['track_quantity'] = 0;
            } elseif ($this->input->post('type') == 'digital') {
                if ($_FILES['digital_file']['size'] > 0) {
                    $config['upload_path'] = $this->digital_upload_path;
                    $config['allowed_types'] = $this->digital_file_types;
                    $config['max_size'] = $this->allowed_file_size;
                    $config['overwrite'] = false;
                    $config['encrypt_name'] = true;
                    $config['max_filename'] = 25;
                    $this->upload->initialize($config);
                    if (!$this->upload->do_upload('digital_file')) {
                        $error = $this->upload->display_errors();
                        $this->session->set_flashdata('error', $error);
                        admin_redirect('products/add');
                    }
                    $file = $this->upload->file_name;
                    $data['file'] = $file;
                } else {
                    if (!$this->input->post('file_link')) {
                        $this->form_validation->set_rules('digital_file', lang('digital_file'), 'required');
                    }
                }
                $config = null;
                $data['track_quantity'] = 0;
            }
            if (!isset($items)) {
                $items = null;
            }
            if ($_FILES['product_image']['size'] > 0) {
                $config['upload_path'] = $this->upload_path;
                $config['allowed_types'] = $this->image_types;
                $config['max_size'] = $this->allowed_file_size;
                $config['max_width'] = $this->Settings->iwidth;
                $config['max_height'] = $this->Settings->iheight;
                $config['overwrite'] = false;
                $config['max_filename'] = 25;
                $config['encrypt_name'] = true;
                $this->upload->initialize($config);
                if (!$this->upload->do_upload('product_image')) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    admin_redirect('products/add');
                }
                $photo = $this->upload->file_name;
                $data['image'] = $photo;
                $this->load->library('image_lib');
                $config['image_library'] = 'gd2';
                $config['source_image'] = $this->upload_path . $photo;
                $config['new_image'] = $this->thumbs_path . $photo;
                $config['maintain_ratio'] = true;
                $config['width'] = $this->Settings->twidth;
                $config['height'] = $this->Settings->theight;
                $this->image_lib->clear();
                $this->image_lib->initialize($config);
                if (!$this->image_lib->resize()) {
                    echo $this->image_lib->display_errors();
                }
                if ($this->Settings->watermark) {
                    $this->image_lib->clear();
                    $wm['source_image'] = $this->upload_path . $photo;
                    $wm['wm_text'] = 'Copyright ' . date('Y') . ' - ' . $this->Settings->site_name;
                    $wm['wm_type'] = 'text';
                    $wm['wm_font_path'] = 'system/fonts/texb.ttf';
                    $wm['quality'] = '100';
                    $wm['wm_font_size'] = '16';
                    $wm['wm_font_color'] = '999999';
                    $wm['wm_shadow_color'] = 'CCCCCC';
                    $wm['wm_vrt_alignment'] = 'top';
                    $wm['wm_hor_alignment'] = 'left';
                    $wm['wm_padding'] = '10';
                    $this->image_lib->initialize($wm);
                    $this->image_lib->watermark();
                }
                $this->image_lib->clear();
                $config = null;
            } else if (!empty($this->input->post('product_image_link'))) {
                $product_image_link = $this->input->post('product_image_link');

                if (filter_var($product_image_link, FILTER_VALIDATE_URL) === false) {
                    $this->session->set_flashdata('error', 'Invalid image URL');
                    admin_redirect('products/add');
                }

                $image_data = file_get_contents($product_image_link);

                if ($image_data === false) {
                    $this->session->set_flashdata('error', 'Failed to retrieve image from URL');
                    admin_redirect('products/add');
                }

                $photo = md5(uniqid(rand(), true)) . '.jpg';

                file_put_contents($this->upload_path . $photo, $image_data);
                $data['image'] = $photo;


                $this->load->library('image_lib');
                $config['image_library'] = 'gd2';
                $config['source_image'] = $this->upload_path . $photo;
                $config['new_image'] = $this->thumbs_path . $photo;
                $config['maintain_ratio'] = true;
                //$config['width']          = $this->Settings->twidth;
                //$config['height']         = $this->Settings->theight;
                $config['width'] = 1200;
                $config['height'] = 1200;
                $this->image_lib->clear();
                $this->image_lib->initialize($config);
                if (!$this->image_lib->resize()) {
                    echo $this->image_lib->display_errors();
                }
                if ($this->Settings->watermark) {
                    $this->image_lib->clear();
                    $wm['source_image'] = $this->upload_path . $photo;
                    $wm['wm_text'] = 'Copyright ' . date('Y') . ' - ' . $this->Settings->site_name;
                    $wm['wm_type'] = 'text';
                    $wm['wm_font_path'] = 'system/fonts/texb.ttf';
                    $wm['quality'] = '100';
                    $wm['wm_font_size'] = '16';
                    $wm['wm_font_color'] = '999999';
                    $wm['wm_shadow_color'] = 'CCCCCC';
                    $wm['wm_vrt_alignment'] = 'top';
                    $wm['wm_hor_alignment'] = 'left';
                    $wm['wm_padding'] = '10';
                    $this->image_lib->initialize($wm);
                    $this->image_lib->watermark();
                }
                $this->image_lib->clear();
                $config = null;
            }

            // Another block

            if ($_FILES['userfile']['name'][0] != '') {
                $config['upload_path'] = $this->upload_path;
                $config['allowed_types'] = $this->image_types;
                $config['max_size'] = $this->allowed_file_size;
                $config['max_width'] = $this->Settings->iwidth;
                $config['max_height'] = $this->Settings->iheight;
                $config['overwrite'] = false;
                $config['encrypt_name'] = true;
                $config['max_filename'] = 25;
                $files = $_FILES;
                $cpt = count($_FILES['userfile']['name']);
                for ($i = 0; $i < $cpt; $i++) {
                    $_FILES['userfile']['name'] = $files['userfile']['name'][$i];
                    $_FILES['userfile']['type'] = $files['userfile']['type'][$i];
                    $_FILES['userfile']['tmp_name'] = $files['userfile']['tmp_name'][$i];
                    $_FILES['userfile']['error'] = $files['userfile']['error'][$i];
                    $_FILES['userfile']['size'] = $files['userfile']['size'][$i];

                    $this->upload->initialize($config);

                    if (!$this->upload->do_upload()) {
                        $error = $this->upload->display_errors();
                        $this->session->set_flashdata('error', $error);
                        admin_redirect('products/add');
                    } else {
                        $pho = $this->upload->file_name;

                        $photos[] = $pho;

                        $this->load->library('image_lib');
                        $config['image_library'] = 'gd2';
                        $config['source_image'] = $this->upload_path . $pho;
                        $config['new_image'] = $this->thumbs_path . $pho;
                        $config['maintain_ratio'] = true;
                        $config['width'] = $this->Settings->twidth;
                        $config['height'] = $this->Settings->theight;

                        $this->image_lib->initialize($config);

                        if (!$this->image_lib->resize()) {
                            echo $this->image_lib->display_errors();
                        }

                        if ($this->Settings->watermark) {
                            $this->image_lib->clear();
                            $wm['source_image'] = $this->upload_path . $pho;
                            $wm['wm_text'] = 'Copyright ' . date('Y') . ' - ' . $this->Settings->site_name;
                            $wm['wm_type'] = 'text';
                            $wm['wm_font_path'] = 'system/fonts/texb.ttf';
                            $wm['quality'] = '100';
                            $wm['wm_font_size'] = '16';
                            $wm['wm_font_color'] = '999999';
                            $wm['wm_shadow_color'] = 'CCCCCC';
                            $wm['wm_vrt_alignment'] = 'top';
                            $wm['wm_hor_alignment'] = 'left';
                            $wm['wm_padding'] = '10';
                            $this->image_lib->initialize($wm);
                            $this->image_lib->watermark();
                        }

                        $this->image_lib->clear();
                    }
                }
                $config = null;
            } else if (!empty($this->input->post('product_image_gallery'))) {
                $product_image_gallery = $this->input->post('product_image_gallery');
                foreach ($product_image_gallery as $image_link) {
                    if (!empty($image_link)) {
                        // Validate the URL
                        if (filter_var($image_link, FILTER_VALIDATE_URL) === false) {
                            $this->session->set_flashdata('error', 'Invalid image URL');
                            admin_redirect('products/add');
                        }

                        $image_data = file_get_contents($image_link);

                        if ($image_data === false) {
                            $this->session->set_flashdata('error', 'Failed to retrieve image from URL');
                            admin_redirect('products/add');
                        }

                        $pho = md5(uniqid(rand(), true)) . '.jpg';
                        file_put_contents($this->upload_path . $pho, $image_data);
                        //$this->processImage($pho);

                        $photos[] = $pho;

                        $this->load->library('image_lib');
                        $config['image_library'] = 'gd2';
                        $config['source_image'] = $this->upload_path . $pho;
                        $config['new_image'] = $this->thumbs_path . $pho;
                        $config['maintain_ratio'] = true;
                        $config['width'] = $this->Settings->twidth;
                        $config['height'] = $this->Settings->theight;

                        $this->image_lib->initialize($config);

                        if (!$this->image_lib->resize()) {
                            echo $this->image_lib->display_errors();
                        }

                        if ($this->Settings->watermark) {
                            $this->image_lib->clear();
                            $wm['source_image'] = $this->upload_path . $pho;
                            $wm['wm_text'] = 'Copyright ' . date('Y') . ' - ' . $this->Settings->site_name;
                            $wm['wm_type'] = 'text';
                            $wm['wm_font_path'] = 'system/fonts/texb.ttf';
                            $wm['quality'] = '100';
                            $wm['wm_font_size'] = '16';
                            $wm['wm_font_color'] = '999999';
                            $wm['wm_shadow_color'] = 'CCCCCC';
                            $wm['wm_vrt_alignment'] = 'top';
                            $wm['wm_hor_alignment'] = 'left';
                            $wm['wm_padding'] = '10';
                            $this->image_lib->initialize($wm);
                            $this->image_lib->watermark();
                        }

                        $this->image_lib->clear();
                    }
                }

            } else {
                $photos = null;
            }
            $data['quantity'] = $wh_total_quantity ?? 0;
            // $this->sma->print_arrays($data, $warehouse_qty, $product_attributes);
        }

        if ($this->form_validation->run() == true && $this->products_model->addProduct($data, $items, $warehouse_qty, $product_attributes, $photos)) {
            $this->session->set_flashdata('message', lang('product_added'));
            admin_redirect('products');
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));

            $this->data['categories'] = $this->site->getAllCategories();
            $this->data['tax_rates'] = $this->site->getAllTaxRates();
            $this->data['brands'] = $this->site->getAllBrands();
            $this->data['base_units'] = $this->site->getAllBaseUnits();
            $this->data['warehouses'] = $warehouses;
            $this->data['warehouses_products'] = $id ? $this->products_model->getAllWarehousesWithPQ($id) : null;
            $this->data['product'] = $id ? $this->products_model->getProductByID($id) : null;
            $this->data['variants'] = $this->products_model->getAllVariants();
            $this->data['country'] = $this->settings_model->getallCountry();
            $this->data['combo_items'] = ($id && $this->data['product']->type == 'combo') ? $this->products_model->getProductComboItems($id) : null;
            $this->data['product_options'] = $id ? $this->products_model->getProductOptionsWithWH($id) : null;
            $bc = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('products'), 'page' => lang('products')], ['link' => '#', 'page' => lang('add_product')]];
            $meta = ['page_title' => lang('add_product'), 'bc' => $bc];
            $this->page_construct('products/add', $meta, $this->data);
        }
    }

    public function add_combo($count_id = null)
    {

        $this->sma->checkPermissions('bundles', true);
        $this->form_validation->set_rules('combo_name', lang('combo_name'), 'required');
        $this->form_validation->set_rules('sg_primary_product', lang('primary_product'), 'required');
        $this->form_validation->set_rules('primary_product_id', lang('primary_product_id'), 'required');
        $this->form_validation->set_rules('buy_quantity', lang('buy_quantity'), 'required');
        if ($this->form_validation->run() == true) {
            $post = $this->input->post();
            $date = date('Y-m-d H:s:i');
            $combo_name = $this->input->post('combo_name');
            $primary_product_id = $this->input->post('primary_product_id');
            $buy_quantity = $this->input->post('buy_quantity');

            $i = isset($post['product_id']) ? sizeof($post['product_id']) : 0;
            for ($r = 0; $r < $i; $r++) {
                $product_id = $post['product_id'][$r];
                $quantity = $post['quantity'][$r];
                $discount = $post['discount'][$r];
                $products[] = [
                    'product_id' => $product_id,
                    'quantity' => $quantity,
                    'discount' => $discount,
                ];
            }

            if (empty($products)) {
                $this->form_validation->set_rules('product', lang('products'), 'required');
            } else {
                krsort($products);
            }

            $combo_name = $this->input->post('combo_name');
            $primary_product_id = $this->input->post('primary_product_id');
            $buy_quantity = $this->input->post('buy_quantity');

            $data = [
                'date_created' => $date,
                'date_updated' => $date,
                'combo_name' => $combo_name,
                'primary_product_id' => $primary_product_id,
                'buy_quantity' => $buy_quantity,
                'created_by' => $this->session->userdata('user_id'),
            ];
            // $this->sma->print_arrays($data, $products);
        }
        if ($this->form_validation->run() == true && $this->products_model->addCombo($data, $products)) {
            $this->session->set_userdata('remove_cbls', 1);
            $this->session->set_flashdata('message', lang('Combo_Created'));
            admin_redirect('products/product_combos');
        } else {
            // if ($count_id) {
            //     $stock_count = $this->products_model->getStouckCountByID($count_id);
            //     $items       = $this->products_model->getStockCountItems($count_id);
            //     foreach ($items as $item) {
            //         $c = sha1(uniqid(mt_rand(), true));
            //         if ($item->counted != $item->expected) {
            //             $product     = $this->site->getProductByID($item->product_id);
            //             $row         = json_decode('{}');
            //             $row->id     = $item->product_id;
            //             $row->code   = $product->code;
            //             $row->name   = $product->name;
            //             // $row->qty    = $item->counted - $item->expected;
            //             // $row->type   = $row->qty > 0 ? 'addition' : 'subtraction';
            //             // $row->qty    = $row->qty > 0 ? $row->qty : (0 - $row->qty);
            //             $options     = $this->products_model->getProductOptions($product->id);
            //             $row->option = $item->product_variant_id ? $item->product_variant_id : 0;
            //             $row->serial = '';
            //             $ri          = $this->Settings->item_addition ? $product->id : $c;

            //             $pr[$ri] = ['id' => $c, 'item_id' => $row->id, 'label' => $row->name . ' (' . $row->code . ')',
            //                 'row'        => $row, 'options' => $options, ];
            //             $c++;
            //         }
            //     }
            // }
            $this->data['combo_items'] = $count_id ? json_encode($pr) : false;
            $this->data['count_id'] = $count_id;
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            //$this->data['warehouses']       = $this->site->getAllWarehouses();
            $bc = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('products'), 'page' => lang('products')], ['link' => '#', 'page' => lang('add_combo')]];
            $meta = ['page_title' => lang('add_combo'), 'bc' => $bc];
            $this->page_construct('products/add_combo', $meta, $this->data);
        }
    }

    public function edit_combo($id)
    {
        $this->sma->checkPermissions('combos', true);
        $combo = $this->products_model->getComboByID($id);
        if (!$id || !$combo) {
            $this->session->set_flashdata('error', lang('combo_not_found'));
            $this->sma->md();
        }
        $this->form_validation->set_rules('combo_name', lang('combo_name'), 'required');
        $this->form_validation->set_rules('sg_primary_product', lang('primary_product'), 'required');
        $this->form_validation->set_rules('primary_product_id', lang('primary_product_id'), 'required');
        $this->form_validation->set_rules('buy_quantity', lang('buy_quantity'), 'required');

        if ($this->form_validation->run() == true) {
            $post = $this->input->post();
            $date = date('Y-m-d H:s:i');
            $combo_name = $this->input->post('combo_name');
            $primary_product_id = $this->input->post('primary_product_id');
            $buy_quantity = $this->input->post('buy_quantity');

            $i = isset($post['product_id']) ? sizeof($post['product_id']) : 0;
            for ($r = 0; $r < $i; $r++) {
                $product_id = $post['product_id'][$r];
                $quantity = $post['quantity'][$r];
                $discount = $post['discount'][$r];
                $products[] = [
                    'product_id' => $product_id,
                    'quantity' => $quantity,
                    'discount' => $discount,
                ];
            }
            if (empty($products)) {
                $this->form_validation->set_rules('product', lang('products'), 'required');
            } else {
                krsort($products);
            }
            $data = [
                'date_updated' => $date,
                'combo_name' => $combo_name,
                'primary_product_id' => $primary_product_id,
                'buy_quantity' => $buy_quantity,
                'updated_by' => $this->session->userdata('user_id'),
            ];
            // $this->sma->print_arrays($data, $products);
        }

        if ($this->form_validation->run() == true && $this->products_model->updateCombo($id, $data, $products)) {
            $this->session->set_userdata('remove_cbls', 1);
            $this->session->set_flashdata('message', lang('combo_updated'));
            admin_redirect('products/product_combos');
        } else {
            $inv_items = $this->products_model->getComboItems($id);
            //echo '<pre>';  print_r($inv_items); exit; 
            // krsort($inv_items);
            foreach ($inv_items as $item) {
                $c = sha1(uniqid(mt_rand(), true));
                $product = $this->site->getProductByID($item->product_id);
                $row = json_decode('{}');
                $row->id = $item->product_id;
                $row->code = $product->code;
                $row->name = $product->name;
                $row->price = $product->price;
                $row->quantity = $item->quantity;
                $row->discount = $item->discount;
                $ri = $this->Settings->item_addition ? $product->id : $c;
                $pr[$ri] = [
                    'id' => $c,
                    'item_id' => $row->id,
                    'label' => $row->name . ' (' . $row->code . ')',
                    'row' => $row,
                    'options' => $options,
                ];
                $c++;
            }

            $this->data['combo'] = $combo;
            $this->data['combo_items'] = json_encode($pr);
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            // $this->data['warehouses']       = $this->site->getAllWarehouses();
            $bc = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('products'), 'page' => lang('products')], ['link' => '#', 'page' => lang('edit_combot')]];
            $meta = ['page_title' => lang('edit_combo'), 'bc' => $bc];
            $this->page_construct('products/edit_combo', $meta, $this->data);
        }
    }
    //-------------------------------------
    public function product_combos($warehouse_id = null)
    {
        $this->sma->checkPermissions('combos');
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $bc = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('products'), 'page' => lang('products')], ['link' => '#', 'page' => lang('product_combos')]];
        $meta = ['page_title' => lang('product_combos'), 'bc' => $bc];
        $this->page_construct('products/product_combos', $meta, $this->data);
    }

    public function getCombos($warehouse_id = null)
    {
        $this->sma->checkPermissions('combos');

        $delete_link = "<a href='#' class='tip po' title='<b>" . $this->lang->line('delete_combo') . "</b>' data-content=\"<p>"
            . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . admin_url('products/delete_combo/$1') . "'>"
            . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i></a>";

        $this->load->library('datatables');
        $this->datatables
            ->select("{$this->db->dbprefix('combos')}.id as id, combo_name, {$this->db->dbprefix('products')}.name as primary_product,buy_quantity , date_created, CONCAT({$this->db->dbprefix('users')}.first_name, ' ', {$this->db->dbprefix('users')}.last_name) as created_by")
            ->from('combos')
            ->join('products', "products.id={$this->db->dbprefix('combos')}.primary_product_id", 'left')
            ->join('users', 'users.id=combos.created_by', 'left')
            ->group_by('combos.id');
        $this->datatables->add_column('Actions', "<div class='text-center'><a href='" . admin_url('products/edit_combo/$1') . "' class='tip' title='" . lang('edit_combo') . "'><i class='fa fa-edit'></i></a> " . $delete_link . '</div>', 'id');

        echo $this->datatables->generate();
    }
    public function delete_combo($id = null)
    {
        $this->sma->checkPermissions('delete', true);
        if (!$id) {
            $this->sma->send_json(['error' => 1, 'msg' => lang('id_not_found')]);
        }
        if ($this->products_model->deleteCombo($id)) {
            $this->sma->send_json(['error' => 0, 'msg' => lang('Combo_deleted')]);
        }
    }
    public function combo_actions()
    {
        if (!$this->Owner && !$this->GP['bulk_actions']) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect($_SERVER['HTTP_REFERER']);
        }

        $this->form_validation->set_rules('form_action', lang('form_action'), 'required');

        if ($this->form_validation->run() == true) {
            if (!empty($_POST['val'])) {
                if ($this->input->post('form_action') == 'delete') {
                    $this->sma->checkPermissions('delete');
                    foreach ($_POST['val'] as $id) {
                        $this->products_model->deleteCombo($id);
                    }
                    $this->session->set_flashdata('message', $this->lang->line('Combo_deleted'));
                    redirect($_SERVER['HTTP_REFERER']);
                }
            } else {
                $this->session->set_flashdata('error', $this->lang->line('no_record_selected'));
                redirect($_SERVER['HTTP_REFERER']);
            }
        } else {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER['HTTP_REFERER']);
        }
    }


    //-------------------------------------

    public function add_bundle($count_id = null)
    {
        $this->sma->checkPermissions('bundles', true);
        $this->form_validation->set_rules('bundle_name', lang('bundle_name'), 'required');
        //$this->form_validation->set_rules('discount', lang('discount'), 'required'); 
        if ($this->form_validation->run() == true) {

            $date = date('Y-m-d H:s:i');
            //  $discount = $this->input->post('discount');
            $bundle_name = $this->input->post('bundle_name');
            $bundle_description = $this->input->post('bundle_description');
            // $note         = $this->sma->clear_tags($this->input->post('note'));

            $i = isset($_POST['product_id']) ? sizeof($_POST['product_id']) : 0;
            for ($r = 0; $r < $i; $r++) {
                $product_id = $_POST['product_id'][$r];
                $discount = $_POST['discount'][$r];
                //$quantity   = $_POST['quantity'][$r];  
                $products[] = [
                    'product_id' => $product_id,
                    'discount' => $discount,
                ];
            }

            if (empty($products)) {
                $this->form_validation->set_rules('product', lang('products'), 'required');
            } else {
                krsort($products);
            }
            $data = [
                'date_created' => $date,
                'date_updated' => $date,
                'bundle_name' => $bundle_name,
                'bundle_description' => $bundle_description,
                'created_by' => $this->session->userdata('user_id'),
            ];
            // $this->sma->print_arrays($data, $products);
        }
        if ($this->form_validation->run() == true && $this->products_model->addBundle($data, $products)) {
            $this->session->set_userdata('remove_buls', 1);
            $this->session->set_flashdata('message', lang('Bundle_Created'));
            admin_redirect('products/product_bundles');
        } else {
            if ($count_id) {
                $stock_count = $this->products_model->getStouckCountByID($count_id);
                $items = $this->products_model->getStockCountItems($count_id);
                foreach ($items as $item) {
                    $c = sha1(uniqid(mt_rand(), true));
                    if ($item->counted != $item->expected) {
                        $product = $this->site->getProductByID($item->product_id);
                        $row = json_decode('{}');
                        $row->id = $item->product_id;
                        $row->code = $product->code;
                        $row->name = $product->name;
                        // $row->qty    = $item->counted - $item->expected;
                        // $row->type   = $row->qty > 0 ? 'addition' : 'subtraction';
                        // $row->qty    = $row->qty > 0 ? $row->qty : (0 - $row->qty);
                        $options = $this->products_model->getProductOptions($product->id);
                        $row->option = $item->product_variant_id ? $item->product_variant_id : 0;
                        $row->serial = '';
                        $ri = $this->Settings->item_addition ? $product->id : $c;

                        $pr[$ri] = [
                            'id' => $c,
                            'item_id' => $row->id,
                            'label' => $row->name . ' (' . $row->code . ')',
                            'row' => $row,
                            'options' => $options,
                        ];
                        $c++;
                    }
                }
            }
            $this->data['bundle_items'] = $count_id ? json_encode($pr) : false;
            $this->data['count_id'] = $count_id;
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            //$this->data['warehouses']       = $this->site->getAllWarehouses();
            $bc = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('products'), 'page' => lang('products')], ['link' => '#', 'page' => lang('add_bundle')]];
            $meta = ['page_title' => lang('add_bundle'), 'bc' => $bc];
            $this->page_construct('products/add_bundle', $meta, $this->data);
        }
    }
    public function edit_bundle($id)
    {
        $this->sma->checkPermissions('bundles', true);
        $bundle = $this->products_model->getBundleByID($id);
        if (!$id || !$bundle) {
            $this->session->set_flashdata('error', lang('bundle_not_found'));
            $this->sma->md();
        }
        $this->form_validation->set_rules('bundle_name', lang('bundle_name'), 'required');

        if ($this->form_validation->run() == true) {
            $date = date('Y-m-d H:s:i');
            $bundle_name = $this->input->post('bundle_name');
            $bundle_description = $this->input->post('bundle_description');
            $i = isset($_POST['product_id']) ? sizeof($_POST['product_id']) : 0;
            for ($r = 0; $r < $i; $r++) {
                $product_id = $_POST['product_id'][$r];
                $discount = $_POST['discount'][$r];
                $products[] = [
                    'product_id' => $product_id,
                    'discount' => $discount,
                ];
            }

            if (empty($products)) {
                $this->form_validation->set_rules('product', lang('products'), 'required');
            } else {
                krsort($products);
            }
            $data = [
                'date_updated' => $date,
                'bundle_name' => $bundle_name,
                'bundle_description' => $bundle_description,
                'updated_by' => $this->session->userdata('user_id'),
            ];
            // $this->sma->print_arrays($data, $products);
        }

        if ($this->form_validation->run() == true && $this->products_model->updateBundle($id, $data, $products)) {
            $this->session->set_userdata('remove_buls', 1);
            $this->session->set_flashdata('message', lang('bundle_updated'));
            admin_redirect('products/product_bundles');
        } else {
            $inv_items = $this->products_model->getBundleItems($id);
            //echo '<pre>';  print_r($inv_items); exit; 
            // krsort($inv_items);
            foreach ($inv_items as $item) {
                $c = sha1(uniqid(mt_rand(), true));
                $product = $this->site->getProductByID($item->product_id);
                $row = json_decode('{}');
                $row->id = $item->product_id;
                $row->code = $product->code;
                $row->name = $product->name;
                $row->price = $product->price;
                $row->discount = $item->discount;
                $ri = $this->Settings->item_addition ? $product->id : $c;
                $pr[$ri] = [
                    'id' => $c,
                    'item_id' => $row->id,
                    'label' => $row->name . ' (' . $row->code . ')',
                    'row' => $row,
                    'options' => $options,
                ];
                $c++;
            }

            $this->data['bundle'] = $bundle;
            $this->data['bundle_items'] = json_encode($pr);
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            // $this->data['warehouses']       = $this->site->getAllWarehouses();
            $bc = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('products'), 'page' => lang('products')], ['link' => '#', 'page' => lang('edit_bundlet')]];
            $meta = ['page_title' => lang('edit_bundle'), 'bc' => $bc];
            $this->page_construct('products/edit_bundle', $meta, $this->data);
        }
    }

    public function product_bundles($warehouse_id = null)
    {
        $this->sma->checkPermissions('bundles');

        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $bc = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('products'), 'page' => lang('products')], ['link' => '#', 'page' => lang('product_bundles')]];
        $meta = ['page_title' => lang('product_bundles'), 'bc' => $bc];
        $this->page_construct('products/product_bundles', $meta, $this->data);
    }

    public function getbundles($warehouse_id = null)
    {
        $this->sma->checkPermissions('bundles');

        $delete_link = "<a href='#' class='tip po' title='<b>" . $this->lang->line('delete_bundle') . "</b>' data-content=\"<p>"
            . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . admin_url('products/delete_bundle/$1') . "'>"
            . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i></a>";

        $this->load->library('datatables');
        $this->datatables
            ->select("{$this->db->dbprefix('bundles')}.id as id, bundle_name, date_created, CONCAT({$this->db->dbprefix('users')}.first_name, ' ', {$this->db->dbprefix('users')}.last_name) as created_by, bundle_description")
            ->from('bundles')
            ->join('users', 'users.id=bundles.created_by', 'left')
            ->group_by('bundles.id');
        $this->datatables->add_column('Actions', "<div class='text-center'><a href='" . admin_url('products/edit_bundle/$1') . "' class='tip' title='" . lang('edit_bundle') . "'><i class='fa fa-edit'></i></a> " . $delete_link . '</div>', 'id');

        echo $this->datatables->generate();
    }
    public function delete_bundle($id = null)
    {
        $this->sma->checkPermissions('delete', true);
        if (!$id) {
            $this->sma->send_json(['error' => 1, 'msg' => lang('id_not_found')]);
        }
        if ($this->products_model->deleteBundle($id)) {
            $this->sma->send_json(['error' => 0, 'msg' => lang('Bundle_deleted')]);
        }
    }
    public function add_adjustment($count_id = null)
    {
        $this->sma->checkPermissions('adjustments', true);
        $this->form_validation->set_rules('warehouse', lang('warehouse'), 'required');

        if ($this->form_validation->run() == true) {
            if ($this->Owner || $this->Admin) {
                $date = $this->sma->fld($this->input->post('date'));
            } else {
                $date = date('Y-m-d H:s:i');
            }

            $reference_no = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('qa');
            $warehouse_id = $this->input->post('warehouse');
            $note = $this->sma->clear_tags($this->input->post('note'));

            $i = isset($_POST['product_id']) ? sizeof($_POST['product_id']) : 0;
            for ($r = 0; $r < $i; $r++) {
                $product_id = $_POST['product_id'][$r];
                $type = $_POST['type'][$r];
                $quantity = $_POST['quantity'][$r];
                $serial = $_POST['serial'][$r];
                $expiry = $_POST['expiry'][$r];
                $batchno = $_POST['batchno'][$r];
                $sale_price = $_POST['sale_price'][$r];
                $unit_cost = $_POST['unit_cost'][$r];
                $variant = isset($_POST['variant'][$r]) && !empty($_POST['variant'][$r]) ? $_POST['variant'][$r] : null;

                if (!$this->Settings->overselling && $type == 'subtraction' && !$count_id) {
                    if ($variant) {
                        if ($op_wh_qty = $this->products_model->getProductWarehouseOptionQty($variant, $warehouse_id)) {
                            if ($op_wh_qty->quantity < $quantity) {
                                $this->session->set_flashdata('error', lang('warehouse_option_qty_is_less_than_damage'));
                                redirect($_SERVER['HTTP_REFERER']);
                            }
                        } else {
                            $this->session->set_flashdata('error', lang('warehouse_option_qty_is_less_than_damage'));
                            redirect($_SERVER['HTTP_REFERER']);
                        }
                    }
                    if ($wh_qty = $this->products_model->getProductQuantity($product_id, $warehouse_id)) {

                        if ($wh_qty['quantity'] < $quantity) {
                            $this->session->set_flashdata('error', lang('warehouse_qty_is_less_than_damage'));
                            redirect($_SERVER['HTTP_REFERER']);
                        }
                    } else {
                        $this->session->set_flashdata('error', lang('warehouse_qty_is_less_than_damage'));
                        redirect($_SERVER['HTTP_REFERER']);
                    }
                }

                $products[] = [
                    'product_id' => $product_id,
                    'type' => $type,
                    'quantity' => $quantity,
                    'expiry' => date('Y-m-d', strtotime($expiry)),
                    'batchno' => $batchno,
                    'sale_price' => $sale_price,
                    'unit_cost' => $unit_cost,
                    'warehouse_id' => $warehouse_id,
                    'option_id' => $variant,
                    'serial_no' => $serial,
                ];
            }

            if (empty($products)) {
                $this->form_validation->set_rules('product', lang('products'), 'required');
            } else {
                krsort($products);
            }

            $data = [
                'date' => $date,
                'reference_no' => $reference_no,
                'warehouse_id' => $warehouse_id,
                'note' => $note,
                'created_by' => $this->session->userdata('user_id'),
                'count_id' => $this->input->post('count_id') ? $this->input->post('count_id') : null,
            ];

            if ($_FILES['document']['size'] > 0) {
                $this->load->library('upload');
                $config['upload_path'] = $this->digital_upload_path;
                $config['allowed_types'] = $this->digital_file_types;
                $config['max_size'] = $this->allowed_file_size;
                $config['overwrite'] = false;
                $config['encrypt_name'] = true;
                $this->upload->initialize($config);
                if (!$this->upload->do_upload('document')) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    redirect($_SERVER['HTTP_REFERER']);
                }
                $photo = $this->upload->file_name;
                $data['attachment'] = $photo;
            }

            // $this->sma->print_arrays($data, $products);
        }
        if ($this->form_validation->run() == true && $this->products_model->addAdjustment($data, $products)) {
            $this->session->set_userdata('remove_qals', 1);
            $this->session->set_flashdata('message', lang('quantity_adjusted'));
            admin_redirect('products/quantity_adjustments');
        } else {
            if ($count_id) {
                $stock_count = $this->products_model->getStouckCountByID($count_id);
                $items = $this->products_model->getStockCountItems($count_id);
                foreach ($items as $item) {
                    $c = sha1(uniqid(mt_rand(), true));
                    if ($item->counted != $item->expected) {
                        $product = $this->site->getProductByID($item->product_id);
                        $row = json_decode('{}');
                        $row->id = $item->product_id;
                        $row->code = $product->code;
                        $row->name = $product->name;
                        $row->qty = $item->counted - $item->expected;
                        $row->type = $row->qty > 0 ? 'addition' : 'subtraction';
                        $row->qty = $row->qty > 0 ? $row->qty : (0 - $row->qty);
                        $options = $this->products_model->getProductOptions($product->id);
                        $row->option = $item->product_variant_id ? $item->product_variant_id : 0;
                        $row->serial = '';
                        $ri = $this->Settings->item_addition ? $product->id : $c;

                        $pr[$ri] = [
                            'id' => $c,
                            'item_id' => $row->id,
                            'label' => $row->name . ' (' . $row->code . ')',
                            'row' => $row,
                            'options' => $options,
                        ];
                        $c++;
                    }
                }
            }
            $this->data['adjustment_items'] = $count_id ? json_encode($pr) : false;
            $this->data['warehouse_id'] = $count_id ? $stock_count->warehouse_id : false;
            $this->data['count_id'] = $count_id;
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['warehouses'] = $this->site->getAllWarehouses();
            $bc = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('products'), 'page' => lang('products')], ['link' => '#', 'page' => lang('add_adjustment')]];
            $meta = ['page_title' => lang('add_adjustment'), 'bc' => $bc];
            $this->page_construct('products/add_adjustment', $meta, $this->data);
        }
    }

    public function add_adjustment_by_csv()
    {
        $this->sma->checkPermissions('adjustments', true);
        $this->form_validation->set_rules('warehouse', lang('warehouse'), 'required');

        if ($this->form_validation->run() == true) {
            if ($this->Owner || $this->Admin) {
                $date = $this->sma->fld($this->input->post('date'));
            } else {
                $date = date('Y-m-d H:s:i');
            }

            $reference_no = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('qa');
            $warehouse_id = $this->input->post('warehouse');
            $note = $this->sma->clear_tags($this->input->post('note'));
            $data = [
                'date' => $date,
                'reference_no' => $reference_no,
                'warehouse_id' => $warehouse_id,
                'note' => $note,
                'created_by' => $this->session->userdata('user_id'),
                'count_id' => null,
            ];

            if ($_FILES['csv_file']['size'] > 0) {
                $this->load->library('upload');
                $config['upload_path'] = $this->digital_upload_path;
                $config['allowed_types'] = 'csv';
                $config['max_size'] = $this->allowed_file_size;
                $config['overwrite'] = false;
                $config['encrypt_name'] = true;
                $this->upload->initialize($config);
                if (!$this->upload->do_upload('csv_file')) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    redirect($_SERVER['HTTP_REFERER']);
                }

                $csv = $this->upload->file_name;
                $data['attachment'] = $csv;

                $arrResult = [];
                $handle = fopen($this->digital_upload_path . $csv, 'r');
                if ($handle) {
                    while (($row = fgetcsv($handle, 5000, ',')) !== false) {

                        if ($row[0] != '') {
                            $quantity_int = str_replace(',', '', $row[1]);
                            $row[1] = $quantity_int;
                            $arrResult[] = $row;
                        }
                    }
                    fclose($handle);
                }
                // echo "<pre>";
                // print_r($arrResult);exit;
                $titles = array_shift($arrResult);
                $keys = ['code', 'quantity', 'saleprice', 'unitcost', 'batch', 'expiry', 'vat', 'variant'];
                $final = [];
                foreach ($arrResult as $key => $value) {
                    $final[] = array_combine($keys, $value);
                }

                //$this->sma->print_arrays($final);
                $rw = 2;
                $nonImportedProducts = array();
                foreach ($final as $pr) {
                    if ($product = $this->products_model->getProductByCode(trim($pr['code']))) {
                        $csv_variant = trim($pr['variant']);
                        $variant = !empty($csv_variant) ? $this->products_model->getProductVariantID($product->id, $csv_variant) : false;

                        $csv_quantity = trim($pr['quantity']);
                        $type = $csv_quantity > 0 ? 'addition' : 'subtraction';
                        $quantity = $csv_quantity > 0 ? $csv_quantity : (0 - $csv_quantity);
                        $batch = trim($pr['batch']);
                        $expiry = trim($pr['expiry']);
                        $expiry = date('Y-m-d', strtotime($expiry));
                        $vat = trim($pr['vat']);
                        $sale_price = trim($pr['saleprice']);
                        $unit_cost = trim($pr['unitcost']);

                        // if (!$this->Settings->overselling && $type == 'subtraction') {
                        //     if ($variant) {
                        //         if ($op_wh_qty = $this->products_model->getProductWarehouseOptionQty($variant, $warehouse_id)) {
                        //             if ($op_wh_qty->quantity < $quantity) {
                        //                 $this->session->set_flashdata('error', lang('warehouse_option_qty_is_less_than_damage') . ' - ' . lang('line_no') . ' ' . $rw);
                        //                 redirect($_SERVER['HTTP_REFERER']);
                        //             }
                        //         } else {
                        //             $this->session->set_flashdata('error', lang('warehouse_option_qty_is_less_than_damage') . ' - ' . lang('line_no') . ' ' . $rw);
                        //             redirect($_SERVER['HTTP_REFERER']);
                        //         }
                        //     }
                        //     if ($wh_qty = $this->products_model->getProductQuantity($product->id, $warehouse_id)) {
                        //         if ($wh_qty['quantity'] < $quantity) {
                        //             $this->session->set_flashdata('error', lang('warehouse_qty_is_less_than_damage') . ' - ' . lang('line_no') . ' ' . $rw);
                        //             redirect($_SERVER['HTTP_REFERER']);
                        //         }
                        //     } else {
                        //         $this->session->set_flashdata('error', lang('warehouse_qty_is_less_than_damage') . ' - ' . lang('line_no') . ' ' . $rw);
                        //         redirect($_SERVER['HTTP_REFERER']);
                        //     }
                        // }

                        $products[] = [
                            'product_id' => $product->id,
                            'type' => $type,
                            'quantity' => $quantity,
                            'batchno' => $batch,
                            'expiry' => $expiry,
                            'vat' => $vat,
                            'sale_price' => $sale_price,
                            'unit_cost' => $unit_cost,
                            'warehouse_id' => $warehouse_id,
                            'option_id' => $variant,
                        ];
                    } else {
                        //$this->session->set_flashdata('error', lang('check_product_code') . ' (' . $pr['code'] . '). ' . lang('product_code_x_exist') . ' ' . lang('line_no') . ' ' . $rw);
                        //redirect($_SERVER['HTTP_REFERER']);

                        $nonImportedProducts[] = trim($pr['code']);
                    }
                    $rw++;
                }
            } else {
                $this->form_validation->set_rules('csv_file', lang('upload_file'), 'required');
            }
            //     echo 'products:';print_r($products_not_exist);
            //     $this->sma->print_arrays($data, $products);
            //    exit;
        }

        if ($this->form_validation->run() == true && $this->products_model->addAdjustment($data, $products)) {
            $message = lang('quantity_adjusted');
            if (!empty($nonImportedProducts)) {
                $fileName = 'non_imported_products_' . $reference_no . '_' . date('Y-m-d') . '.txt';
                $fileLink = 'admin/products/download_adjusmtent_non_imported/non_imported_products_' . $reference_no . '_' . date('Y-m-d') . '.txt';
                $dataToWrite = implode("\n", $nonImportedProducts);
                $filePath = './files/' . $fileName;
                if (!write_file($filePath, $dataToWrite, 'w+')) {
                    //log_message('error', 'Unable to write non-imported product codes to file.');
                } else {
                    $message = $message . " <a href='" . site_url($fileLink) . "' target='_blank'>Download Non-Imported Product Codes</a>";
                }
            }
            $this->session->set_flashdata('message', $message);
            admin_redirect('products/quantity_adjustments');
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['warehouses'] = $this->site->getAllWarehouses();
            $bc = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('products'), 'page' => lang('products')], ['link' => '#', 'page' => lang('add_adjustment')]];
            $meta = ['page_title' => lang('add_adjustment_by_csv'), 'bc' => $bc];
            $this->page_construct('products/add_adjustment_by_csv', $meta, $this->data);
        }
    }

    public function download_adjusmtent_non_imported($fileName = '')
    {
        if (file_exists(FCPATH . 'files/' . $fileName)) {
            //echo "yes";
            $this->load->helper('download');
            force_download(FCPATH . 'files/' . $fileName, NULL);
            exit;
        } else {
            admin_redirect('products/quantity_adjustments');
        }
    }

    public function addByAjax()
    {
        if (!$this->mPermissions('add')) {
            exit(json_encode(['msg' => lang('access_denied')]));
        }
        if ($this->input->get('token') && $this->input->get('token') == $this->session->userdata('user_csrf') && $this->input->is_ajax_request()) {
            $product = $this->input->get('product');
            if (!isset($product['code']) || empty($product['code'])) {
                exit(json_encode(['msg' => lang('product_code_is_required')]));
            }
            if (!isset($product['name']) || empty($product['name'])) {
                exit(json_encode(['msg' => lang('product_name_is_required')]));
            }
            if (!isset($product['category_id']) || empty($product['category_id'])) {
                exit(json_encode(['msg' => lang('product_category_is_required')]));
            }
            if (!isset($product['unit']) || empty($product['unit'])) {
                exit(json_encode(['msg' => lang('product_unit_is_required')]));
            }
            if (!isset($product['price']) || empty($product['price'])) {
                exit(json_encode(['msg' => lang('product_price_is_required')]));
            }
            if (!isset($product['cost']) || empty($product['cost'])) {
                exit(json_encode(['msg' => lang('product_cost_is_required')]));
            }
            if ($this->products_model->getProductByCode($product['code'])) {
                exit(json_encode(['msg' => lang('product_code_already_exist')]));
            }
            if ($row = $this->products_model->addAjaxProduct($product)) {
                $tax_rate = $this->site->getTaxRateByID($row->tax_rate);
                $pr = ['id' => $row->id, 'label' => $row->name . ' (' . $row->code . ')', 'code' => $row->code, 'qty' => 1, 'cost' => $row->cost, 'name' => $row->name, 'tax_method' => $row->tax_method, 'tax_rate' => $tax_rate, 'discount' => '0'];
                $this->sma->send_json(['msg' => 'success', 'result' => $pr]);
            } else {
                exit(json_encode(['msg' => lang('failed_to_add_product')]));
            }
        } else {
            json_encode(['msg' => 'Invalid token']);
        }
    }

    public function bundle_actions()
    {
        if (!$this->Owner && !$this->GP['bulk_actions']) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect($_SERVER['HTTP_REFERER']);
        }

        $this->form_validation->set_rules('form_action', lang('form_action'), 'required');

        if ($this->form_validation->run() == true) {
            if (!empty($_POST['val'])) {
                if ($this->input->post('form_action') == 'delete') {
                    $this->sma->checkPermissions('delete');
                    foreach ($_POST['val'] as $id) {
                        $this->products_model->deleteBundle($id);
                    }
                    $this->session->set_flashdata('message', $this->lang->line('Bundle_deleted'));
                    redirect($_SERVER['HTTP_REFERER']);
                }
            } else {
                $this->session->set_flashdata('error', $this->lang->line('no_record_selected'));
                redirect($_SERVER['HTTP_REFERER']);
            }
        } else {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER['HTTP_REFERER']);
        }
    }
    public function adjustment_actions()
    {
        if (!$this->Owner && !$this->GP['bulk_actions']) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect($_SERVER['HTTP_REFERER']);
        }

        $this->form_validation->set_rules('form_action', lang('form_action'), 'required');

        if ($this->form_validation->run() == true) {
            if (!empty($_POST['val'])) {
                if ($this->input->post('form_action') == 'delete') {
                    $this->sma->checkPermissions('delete');
                    foreach ($_POST['val'] as $id) {
                        $this->products_model->deleteAdjustment($id);
                    }
                    $this->session->set_flashdata('message', $this->lang->line('adjustment_deleted'));
                    redirect($_SERVER['HTTP_REFERER']);
                } elseif ($this->input->post('form_action') == 'export_excel') {
                    $this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle('quantity_adjustments');
                    $this->excel->getActiveSheet()->SetCellValue('A1', lang('date'));
                    $this->excel->getActiveSheet()->SetCellValue('B1', lang('reference_no'));
                    $this->excel->getActiveSheet()->SetCellValue('C1', lang('warehouse'));
                    $this->excel->getActiveSheet()->SetCellValue('D1', lang('created_by'));
                    $this->excel->getActiveSheet()->SetCellValue('E1', lang('note'));
                    $this->excel->getActiveSheet()->SetCellValue('F1', lang('items'));

                    $row = 2;
                    foreach ($_POST['val'] as $id) {
                        $adjustment = $this->products_model->getAdjustmentByID($id);
                        $created_by = $this->site->getUser($adjustment->created_by);
                        $warehouse = $this->site->getWarehouseByID($adjustment->warehouse_id);
                        $items = $this->products_model->getAdjustmentItems($id);
                        $products = '';
                        if ($items) {
                            foreach ($items as $item) {
                                $products .= $item->product_name . '(' . $this->sma->formatQuantity($item->type == 'subtraction' ? -$item->quantity : $item->quantity) . ')' . "\n";
                            }
                        }

                        $this->excel->getActiveSheet()->SetCellValue('A' . $row, $this->sma->hrld($adjustment->date));
                        $this->excel->getActiveSheet()->SetCellValue('B' . $row, $adjustment->reference_no);
                        $this->excel->getActiveSheet()->SetCellValue('C' . $row, $warehouse->name);
                        $this->excel->getActiveSheet()->SetCellValue('D' . $row, $created_by->first_name . ' ' . $created_by->last_name);
                        $this->excel->getActiveSheet()->SetCellValue('E' . $row, $this->sma->decode_html($adjustment->note));
                        $this->excel->getActiveSheet()->SetCellValue('F' . $row, $products);
                        $row++;
                    }

                    $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(15);
                    $this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(40);
                    $this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(30);
                    $this->excel->getDefaultStyle()->getAlignment()->setVertical('center');
                    $filename = 'quantity_adjustments_' . date('Y_m_d_H_i_s');
                    $this->load->helper('excel');
                    create_excel($this->excel, $filename);
                }
            } else {
                $this->session->set_flashdata('error', $this->lang->line('no_record_selected'));
                redirect($_SERVER['HTTP_REFERER']);
            }
        } else {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER['HTTP_REFERER']);
        }
    }

    public function barcode($product_code = null, $bcs = 'code128', $height = 40)
    {
        if ($this->Settings->barcode_img) {
            header('Content-Type: image/png');
        } else {
            header('Content-type: image/svg+xml');
        }
        echo $this->sma->barcode($product_code, $bcs, $height, true, false, true);
    }

    public function count_stock($page = null)
    {
        $this->sma->checkPermissions('stock_count');
        $this->form_validation->set_rules('warehouse', lang('warehouse'), 'required');
        $this->form_validation->set_rules('type', lang('type'), 'required');

        if ($this->form_validation->run() == true) {
            $warehouse_id = $this->input->post('warehouse');
            $type = $this->input->post('type');
            $categories = $this->input->post('category') ? $this->input->post('category') : null;
            $brands = $this->input->post('brand') ? $this->input->post('brand') : null;
            $this->load->helper('string');
            $name = random_string('md5') . '.csv';
            $products = $this->products_model->getStockCountProducts($warehouse_id, $type, $categories, $brands);

            $pr = 0;
            $rw = 0;
            foreach ($products as $product) {
                /*if ($variants = $this->products_model->getStockCountProductVariants($warehouse_id, $product->id)) {
                    foreach ($variants as $variant) {
                        $items[] = [
                            'product_code' => $product->code,
                            'product_name' => $product->name,
                            'batch_no'      => $product->batchno,
                            'expiry'      => $product->expiry,
                            'balance'     => $product->quantity,
                            'purchase_price' => $product->purchase_cost,
                            'sale_price'  => $product->sale_price
                        ];
                        $rw++;
                    }
                } else {*/
                //                dd($product);
                $items[] = [
                    'product_code' => $product->code,
                    'product_name' => $product->name,
                    'batch_no' => $product->batchno,
                    'expiry' => $product->expiry,
                    'balance' => intval($product->quantity),
                    'purchase_price' => $product->purchase_cost,
                    'sale_price' => $this->sma->formatDecimal($product->sale_price),
                    'item_cost' => $this->sma->formatDecimal($product->item_cost),
                    'total_cost' => $this->sma->formatDecimal($product->item_cost) * (!empty(intval($product->quantity)) ? $product->quantity : 1),
                    'total_sale_price' => $this->sma->formatDecimal($product->sale_price) * (!empty(intval($product->quantity)) ? $product->quantity : 1)
                ];
                $rw++;
                //}
                $pr++;
            }

            if (!empty($items)) {
                $csv_file = fopen('./files/' . $name, 'w');
                fprintf($csv_file, chr(0xEF) . chr(0xBB) . chr(0xBF));
                fputcsv($csv_file, [lang('product_code'), lang('product_name'), lang('Batch'), lang('Expiry'), lang('Balance'), lang('Purchase Price'), lang('Sale Price'), 'Item Cost', 'Total Cost', 'Total Sale Price']);
                foreach ($items as $item) {
                    fputcsv($csv_file, $item);
                }
                // file_put_contents('./files/'.$name, $csv_file);
                // fwrite($csv_file, $txt);
                fclose($csv_file);
            } else {
                $this->session->set_flashdata('error', lang('no_product_found'));
                redirect($_SERVER['HTTP_REFERER']);
            }

            if ($this->Owner || $this->Admin) {
                $date = $this->sma->fld($this->input->post('date'));
            } else {
                $date = date('Y-m-d H:s:i');
            }
            $category_ids = '';
            $brand_ids = '';
            $category_names = '';
            $brand_names = '';
            if ($categories) {
                $r = 1;
                $s = sizeof($categories);
                foreach ($categories as $category_id) {
                    $category = $this->site->getCategoryByID($category_id);
                    if ($r == $s) {
                        $category_names .= $category->name;
                        $category_ids .= $category->id;
                    } else {
                        $category_names .= $category->name . ', ';
                        $category_ids .= $category->id . ', ';
                    }
                    $r++;
                }
            }
            if ($brands) {
                $r = 1;
                $s = sizeof($brands);
                foreach ($brands as $brand_id) {
                    $brand = $this->site->getBrandByID($brand_id);
                    if ($r == $s) {
                        $brand_names .= $brand->name;
                        $brand_ids .= $brand->id;
                    } else {
                        $brand_names .= $brand->name . ', ';
                        $brand_ids .= $brand->id . ', ';
                    }
                    $r++;
                }
            }
            $data = [
                'date' => $date,
                'warehouse_id' => $warehouse_id,
                'reference_no' => $this->input->post('reference_no'),
                'type' => $type,
                'categories' => $category_ids,
                'category_names' => $category_names,
                'brands' => $brand_ids,
                'brand_names' => $brand_names,
                'initial_file' => $name,
                'products' => $pr,
                'rows' => $rw,
                'created_by' => $this->session->userdata('user_id'),
            ];
        }

        if ($this->form_validation->run() == true && $this->products_model->addStockCount($data)) {
            $this->session->set_flashdata('message', lang('stock_count_intiated'));
            admin_redirect('products/stock_counts');
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['warehouses'] = $this->site->getAllWarehouses();
            $this->data['categories'] = $this->site->getAllCategories();
            $this->data['brands'] = $this->site->getAllBrands();
            $bc = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('products'), 'page' => lang('products')], ['link' => '#', 'page' => lang('count_stock')]];
            $meta = ['page_title' => lang('count_stock'), 'bc' => $bc];
            $this->page_construct('products/count_stock', $meta, $this->data);
        }
    }

    /* ------------------------------------------------------------------------------- */

    public function delete($id = null)
    {
        $this->sma->checkPermissions(null, true);

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        if (!$id) {
            $this->sma->send_json(['error' => 1, 'msg' => lang('id_not_found')]);
        }

        if ($this->products_model->deleteProduct($id)) {
            if ($this->input->is_ajax_request()) {
                $this->sma->send_json(['error' => 0, 'msg' => lang('product_deleted')]);
            }
            $this->session->set_flashdata('message', lang('product_deleted'));
            admin_redirect('welcome');
        }
    }

    public function delete_adjustment($id = null)
    {
        $this->sma->checkPermissions('delete', true);
        if (!$id) {
            $this->sma->send_json(['error' => 1, 'msg' => lang('id_not_found')]);
        }
        if ($this->products_model->deleteAdjustment($id)) {
            $this->sma->send_json(['error' => 0, 'msg' => lang('adjustment_deleted')]);
        }
    }
    public function delete_image($id = null)
    {
        $this->sma->checkPermissions('edit', true);
        if ($id && $this->input->is_ajax_request()) {
            header('Content-Type: application/json');
            $this->db->delete('product_photos', ['id' => $id]);
            $this->sma->send_json(['error' => 0, 'msg' => lang('image_deleted')]);
        }
        $this->sma->send_json(['error' => 1, 'msg' => lang('ajax_error')]);
    }

    /* -------------------------------------------------------- */

    public function getEnglishToArabic()
    {
        $term = $this->input->post('term');
        $term = strip_tags($term);
        // Set API endpoint and your API key
        $apiKey = 'wg_42c9daf242af8316a7b7d92e5a2aa0e55';
        $apiEndpoint = 'https://api.weglot.com/translate?api_key=' . $apiKey;

        // Prepare the JSON payload
        $data = [
            "l_to" => "ar",
            "l_from" => "en",
            "request_url" => "https://www.avenzur.com/",
            "words" => [
                ["w" => "$term", "t" => 1]
            ]
        ];

        // Convert the payload to JSON format
        $jsonData = json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        // Initialize cURL session
        $ch = curl_init();

        // Set cURL options
        curl_setopt_array($ch, [
            CURLOPT_URL => $apiEndpoint,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $jsonData,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Content-Length: ' . strlen($jsonData)
            ],
            CURLOPT_SSL_VERIFYPEER => 0,
            CURLOPT_SSL_VERIFYPEER => 0
        ]);
        // Execute the POST request
        $response = curl_exec($ch);
        // Check for errors
        if (curl_errno($ch)) {
            $status = 'Error';
            $message = 'Error:' . curl_error($ch);
            $to_words = null;
            curl_close($ch);
        } else {
            // Decode the response
            $responseData = json_decode($response, true);
            curl_close($ch);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $status = 'Error';
                $message = 'JSON decode error: ' . json_last_error_msg();
                $to_words = null;
            }
            if (isset($responseData['to_words']) && is_array($responseData['to_words'])) {
                $status = 'Success';
                $to_words = $responseData['to_words'];
            } else {
                // Handle the case where the response doesn't have the expected data
                $status = 'Error';
                $message = "Translation error or unexpected response format.";
                $to_words = null;
            }
        }

        $reponse = array(
            'csrfName' => $this->security->get_csrf_token_name(),
            'csrfHash' => $this->security->get_csrf_hash(),
            'to_words' => $to_words,
            'status' => $status,
            'message' => $message,

        );

        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($reponse));
        exit;
    }


    public function edit($id = null)
    {
        $this->sma->checkPermissions();
        $this->load->helper('security');
        if ($this->input->post('id')) {
            $id = $this->input->post('id');
        }
        $warehouses = $this->site->getAllWarehouses();
        $warehouses_products = $this->products_model->getAllWarehousesWithPQ($id);
        $product = $this->site->getProductByID($id);
        if (!$id || !$product) {
            $this->session->set_flashdata('error', lang('prduct_not_found'));
            redirect($_SERVER['HTTP_REFERER']);
        }
        // $this->form_validation->set_rules('category', lang('category'), 'required|is_natural_no_zero');
        if ($this->input->post('type') == 'standard') {
            //$this->form_validation->set_rules('cost', lang('product_cost'), 'required');
            // $this->form_validation->set_rules('unit', lang('product_unit'), 'required');
        }
        $this->form_validation->set_rules('code', lang('product_code'), 'alpha_dash');
        if ($this->input->post('code') !== $product->code) {
            // $this->form_validation->set_rules('code', lang('product_code'), 'is_unique[products.code]');
        }
        if (SHOP) {
            $this->form_validation->set_rules('slug', lang('slug'), 'required|alpha_dash');
            if ($this->input->post('slug') !== $product->slug) {
                $this->form_validation->set_rules('slug', lang('slug'), 'required|is_unique[products.slug]|alpha_dash');
            }
        }
        $this->form_validation->set_rules('weight', lang('weight'), 'numeric');
        // $this->form_validation->set_rules('product_image', lang('product_image'), 'xss_clean');
        $this->form_validation->set_rules('digital_file', lang('digital_file'), 'xss_clean');
        // $this->form_validation->set_rules('userfile', lang('product_gallery_images'), 'xss_clean');

        if ($this->form_validation->run('products/add') == true) {

            $product_countries = '';
            foreach ($this->input->post('cf1') as $pcountry) {
                if ($pcountry == '0') {
                    $product_countries = 0;
                    break;
                }
                $product_countries .= $pcountry . ',';
            }

            $product_countries = rtrim($product_countries, ',');

            $hide_product = $this->input->post('hide') ? $this->input->post('hide') : 0;

            $draft_set = $this->input->post('draft');
            if ($draft_set == 1) {
                $hide_product = 1;
            }

            $data = [
                'code' => $this->input->post('code'),
                'item_code' => $this->input->post('item_code'),
                'barcode_symbology' => $this->input->post('barcode_symbology'),
                'name' => $this->input->post('name'),
                'type' => $this->input->post('type'),
                'brand' => $this->input->post('brand'),
                'category_id' => $this->input->post('category'),
                'subcategory_id' => $this->input->post('subcategory') ? $this->input->post('subcategory') : null,
                'cost' => $this->sma->formatDecimal($this->input->post('price')),
                'price' => $this->sma->formatDecimal($this->input->post('price')),
                'unit' => $this->input->post('unit'),
                'sale_unit' => $this->input->post('default_sale_unit'),
                'purchase_unit' => $this->input->post('default_purchase_unit'),
                'tax_rate' => $this->input->post('tax_rate'),
                'tax_method' => $this->input->post('tax_method'),
                'alert_quantity' => $this->input->post('alert_quantity'),
                'track_quantity' => $this->input->post('track_quantity') ? $this->input->post('track_quantity') : '0',
                'details' => $_POST['details'], //$this->input->post('details',false),
                'product_details' => $_POST['product_details'], //$this->input->post('product_details',false),
                'supplier1' => $this->input->post('supplier'),
                'supplier1price' => $this->sma->formatDecimal($this->input->post('supplier_price')),
                'supplier2' => $this->input->post('supplier_2'),
                'supplier2price' => $this->sma->formatDecimal($this->input->post('supplier_2_price')),
                'supplier3' => $this->input->post('supplier_3'),
                'supplier3price' => $this->sma->formatDecimal($this->input->post('supplier_3_price')),
                'supplier4' => $this->input->post('supplier_4'),
                'supplier4price' => $this->sma->formatDecimal($this->input->post('supplier_4_price')),
                'supplier5' => $this->input->post('supplier_5'),
                'supplier5price' => $this->sma->formatDecimal($this->input->post('supplier_5_price')),
                'cf1' => $product_countries,//$this->input->post('cf1'),
                'cf2' => $this->input->post('cf2'),
                'cf3' => $this->input->post('cf3'),
                'cf4' => $this->input->post('cf4'),
                'cf5' => $this->input->post('cf5'),
                'cf6' => $this->input->post('cf6'),
                'promotion' => $this->input->post('promotion'),
                'promo_price' => $this->sma->formatDecimal($this->input->post('promo_price')),
                'start_date' => $this->input->post('start_date') ? $this->sma->fsd($this->input->post('start_date')) : null,
                'end_date' => $this->input->post('end_date') ? $this->sma->fsd($this->input->post('end_date')) : null,
                'supplier1_part_no' => $this->input->post('supplier_part_no'),
                'supplier2_part_no' => $this->input->post('supplier_2_part_no'),
                'supplier3_part_no' => $this->input->post('supplier_3_part_no'),
                'supplier4_part_no' => $this->input->post('supplier_4_part_no'),
                'supplier5_part_no' => $this->input->post('supplier_5_part_no'),
                'slug' => $this->input->post('slug'),
                'weight' => $this->input->post('weight'),
                'featured' => $this->input->post('featured'),
                'special_offer' => $this->input->post('special_offer'),
                'hsn_code' => $this->input->post('hsn_code'),
                'hide' => $hide_product,
                'hide_pos' => $this->input->post('hide_pos') ? $this->input->post('hide_pos') : 0,
                'second_name' => $this->input->post('second_name'),
                'trade_name' => $this->input->post('trade_name'),
                'manufacture_name' => $this->input->post('manufacture_name'),
                'main_agent' => $this->input->post('main_agent'),
                'draft' => $this->input->post('draft'),
                'special_product' => $this->input->post('special_product'),
                'google_merch' => $this->input->post('google_merch'),
                // 'purchase_account'       => $this->input->post('purchase_account'),
                // 'sale_account'       => $this->input->post('sale_account'),
                // 'inventory_account'       => $this->input->post('inventory_account'),
            ];

            if ($this->input->post('name_ar') != '') {
                $data['name_ar'] = $this->input->post('name_ar');
            }
            if ($this->input->post('product_details_ar') != '') {
                $data['product_details_ar'] = $this->input->post('product_details_ar');
            }

            $warehouse_qty = null;
            $product_attributes = null;
            $update_variants = [];
            $this->load->library('upload');
            if ($this->input->post('type') == 'standard') {
                if ($product_variants = $this->products_model->getProductOptions($id)) {
                    foreach ($product_variants as $pv) {
                        $update_variants[] = [
                            'id' => $this->input->post('variant_id_' . $pv->id),
                            'name' => $this->input->post('variant_name_' . $pv->id),
                            'cost' => $this->input->post('variant_cost_' . $pv->id),
                            'price' => $this->input->post('variant_price_' . $pv->id),
                        ];
                    }
                }
                for ($s = 2; $s > 5; $s++) {
                    $data['suppliers' . $s] = $this->input->post('supplier_' . $s);
                    $data['suppliers' . $s . 'price'] = $this->input->post('supplier_' . $s . '_price');
                }
                foreach ($warehouses as $warehouse) {
                    $warehouse_qty[] = [
                        'warehouse_id' => $this->input->post('wh_' . $warehouse->id),
                        'rack' => $this->input->post('rack_' . $warehouse->id) ? $this->input->post('rack_' . $warehouse->id) : null,
                    ];
                }

                if ($this->input->post('attributes')) {
                    $a = sizeof($_POST['attr_name']);
                    for ($r = 0; $r <= $a; $r++) {
                        if (isset($_POST['attr_name'][$r])) {
                            if ($product_variatnt = $this->products_model->getPrductVariantByPIDandName($id, trim($_POST['attr_name'][$r]))) {
                                $this->form_validation->set_message('required', lang('product_already_has_variant') . ' (' . $_POST['attr_name'][$r] . ')');
                                $this->form_validation->set_rules('new_product_variant', lang('new_product_variant'), 'required');
                            } else {
                                $product_attributes[] = [
                                    'name' => $_POST['attr_name'][$r],
                                    'warehouse_id' => $_POST['attr_warehouse'][$r],
                                    'quantity' => $_POST['attr_quantity'][$r],
                                    'price' => $_POST['attr_price'][$r],
                                ];
                            }
                        }
                    }
                } else {
                    $product_attributes = null;
                }
            }

            if ($this->input->post('type') == 'service') {
                $data['track_quantity'] = 0;
            } elseif ($this->input->post('type') == 'combo') {
                $total_price = 0;
                $c = sizeof($_POST['combo_item_code']) - 1;
                for ($r = 0; $r <= $c; $r++) {
                    if (isset($_POST['combo_item_code'][$r]) && isset($_POST['combo_item_quantity'][$r]) && isset($_POST['combo_item_price'][$r])) {
                        $items[] = [
                            'item_code' => $_POST['combo_item_code'][$r],
                            'quantity' => $_POST['combo_item_quantity'][$r],
                            'unit_price' => $_POST['combo_item_price'][$r],
                        ];
                    }
                    $total_price += $_POST['combo_item_price'][$r] * $_POST['combo_item_quantity'][$r];
                }
                if ($this->sma->formatDecimal($total_price) != $this->sma->formatDecimal($this->input->post('price'))) {
                    $this->form_validation->set_rules('combo_price', 'combo_price', 'required');
                    $this->form_validation->set_message('required', lang('pprice_not_match_ciprice'));
                }
                $data['track_quantity'] = 0;
            } elseif ($this->input->post('type') == 'digital') {
                if ($this->input->post('file_link')) {
                    $data['file'] = $this->input->post('file_link');
                }
                if ($_FILES['digital_file']['size'] > 0) {
                    $config['upload_path'] = $this->digital_upload_path;
                    $config['allowed_types'] = $this->digital_file_types;
                    $config['max_size'] = $this->allowed_file_size;
                    $config['overwrite'] = false;
                    $config['encrypt_name'] = true;
                    $config['max_filename'] = 25;
                    $this->upload->initialize($config);
                    if (!$this->upload->do_upload('digital_file')) {
                        $error = $this->upload->display_errors();
                        $this->session->set_flashdata('error', $error);
                        admin_redirect('products/add');
                    }
                    $file = $this->upload->file_name;
                    $data['file'] = $file;
                }
                $config = null;
                $data['track_quantity'] = 0;
            }
            if (!isset($items)) {
                $items = null;
            }
            if ($_FILES['product_image']['size'] > 0) {
                $config['upload_path'] = $this->upload_path;
                $config['allowed_types'] = $this->image_types;
                $config['max_size'] = $this->allowed_file_size;
                $config['max_width'] = $this->Settings->iwidth;
                $config['max_height'] = $this->Settings->iheight;
                $config['overwrite'] = false;
                $config['encrypt_name'] = true;
                $config['max_filename'] = 25;
                $this->upload->initialize($config);
                if (!$this->upload->do_upload('product_image')) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    admin_redirect('products/edit/' . $id);
                }
                $photo = $this->upload->file_name;
                $data['image'] = $photo;
                $this->load->library('image_lib');
                $config['image_library'] = 'gd2';
                $config['source_image'] = $this->upload_path . $photo;
                $config['new_image'] = $this->thumbs_path . $photo;
                $config['maintain_ratio'] = true;
                $config['width'] = $this->Settings->twidth;
                $config['height'] = $this->Settings->theight;
                $this->image_lib->clear();
                $this->image_lib->initialize($config);
                if (!$this->image_lib->resize()) {
                    echo $this->image_lib->display_errors();
                }
                if ($this->Settings->watermark) {
                    $this->image_lib->clear();
                    $wm['source_image'] = $this->upload_path . $photo;
                    $wm['wm_text'] = 'Copyright ' . date('Y') . ' - ' . $this->Settings->site_name;
                    $wm['wm_type'] = 'text';
                    $wm['wm_font_path'] = 'system/fonts/texb.ttf';
                    $wm['quality'] = '100';
                    $wm['wm_font_size'] = '16';
                    $wm['wm_font_color'] = '999999';
                    $wm['wm_shadow_color'] = 'CCCCCC';
                    $wm['wm_vrt_alignment'] = 'top';
                    $wm['wm_hor_alignment'] = 'left';
                    $wm['wm_padding'] = '10';
                    $this->image_lib->initialize($wm);
                    $this->image_lib->watermark();
                }
                $this->image_lib->clear();
                $config = null;
            } else if (!empty($this->input->post('product_image_link'))) {
                $product_image_link = $this->input->post('product_image_link');

                if (filter_var($product_image_link, FILTER_VALIDATE_URL) === false) {
                    $this->session->set_flashdata('error', 'Invalid image URL');
                    admin_redirect('products/add');
                }

                $image_data = file_get_contents($product_image_link);

                if ($image_data === false) {
                    $this->session->set_flashdata('error', 'Failed to retrieve image from URL');
                    admin_redirect('products/add');
                }

                $photo = md5(uniqid(rand(), true)) . '.jpg';

                file_put_contents($this->upload_path . $photo, $image_data);
                $data['image'] = $photo;


                $this->load->library('image_lib');
                $config['image_library'] = 'gd2';
                $config['source_image'] = $this->upload_path . $photo;
                $config['new_image'] = $this->thumbs_path . $photo;
                $config['maintain_ratio'] = true;
                $config['width'] = $this->Settings->twidth;
                $config['height'] = $this->Settings->theight;
                $this->image_lib->clear();
                $this->image_lib->initialize($config);
                if (!$this->image_lib->resize()) {
                    echo $this->image_lib->display_errors();
                }
                if ($this->Settings->watermark) {
                    $this->image_lib->clear();
                    $wm['source_image'] = $this->upload_path . $photo;
                    $wm['wm_text'] = 'Copyright ' . date('Y') . ' - ' . $this->Settings->site_name;
                    $wm['wm_type'] = 'text';
                    $wm['wm_font_path'] = 'system/fonts/texb.ttf';
                    $wm['quality'] = '100';
                    $wm['wm_font_size'] = '16';
                    $wm['wm_font_color'] = '999999';
                    $wm['wm_shadow_color'] = 'CCCCCC';
                    $wm['wm_vrt_alignment'] = 'top';
                    $wm['wm_hor_alignment'] = 'left';
                    $wm['wm_padding'] = '10';
                    $this->image_lib->initialize($wm);
                    $this->image_lib->watermark();
                }
                $this->image_lib->clear();
                $config = null;
            }

            if ($_FILES['userfile']['name'][0] != '') {
                $config['upload_path'] = $this->upload_path;
                $config['allowed_types'] = $this->image_types;
                $config['max_size'] = $this->allowed_file_size;
                $config['max_width'] = $this->Settings->iwidth;
                $config['max_height'] = $this->Settings->iheight;
                $config['overwrite'] = false;
                $config['encrypt_name'] = true;
                $config['max_filename'] = 25;
                $files = $_FILES;
                $cpt = count($_FILES['userfile']['name']);
                for ($i = 0; $i < $cpt; $i++) {
                    $_FILES['userfile']['name'] = $files['userfile']['name'][$i];
                    $_FILES['userfile']['type'] = $files['userfile']['type'][$i];
                    $_FILES['userfile']['tmp_name'] = $files['userfile']['tmp_name'][$i];
                    $_FILES['userfile']['error'] = $files['userfile']['error'][$i];
                    $_FILES['userfile']['size'] = $files['userfile']['size'][$i];

                    $this->upload->initialize($config);

                    if (!$this->upload->do_upload()) {
                        $error = $this->upload->display_errors();
                        $this->session->set_flashdata('error', $error);
                        admin_redirect('products/edit/' . $id);
                    } else {
                        $pho = $this->upload->file_name;

                        $photos[] = $pho;

                        $this->load->library('image_lib');
                        $config['image_library'] = 'gd2';
                        $config['source_image'] = $this->upload_path . $pho;
                        $config['new_image'] = $this->thumbs_path . $pho;
                        $config['maintain_ratio'] = true;
                        $config['width'] = $this->Settings->twidth;
                        $config['height'] = $this->Settings->theight;

                        $this->image_lib->initialize($config);

                        if (!$this->image_lib->resize()) {
                            echo $this->image_lib->display_errors();
                        }

                        if ($this->Settings->watermark) {
                            $this->image_lib->clear();
                            $wm['source_image'] = $this->upload_path . $pho;
                            $wm['wm_text'] = 'Copyright ' . date('Y') . ' - ' . $this->Settings->site_name;
                            $wm['wm_type'] = 'text';
                            $wm['wm_font_path'] = 'system/fonts/texb.ttf';
                            $wm['quality'] = '100';
                            $wm['wm_font_size'] = '16';
                            $wm['wm_font_color'] = '999999';
                            $wm['wm_shadow_color'] = 'CCCCCC';
                            $wm['wm_vrt_alignment'] = 'top';
                            $wm['wm_hor_alignment'] = 'left';
                            $wm['wm_padding'] = '10';
                            $this->image_lib->initialize($wm);
                            $this->image_lib->watermark();
                        }

                        $this->image_lib->clear();
                    }
                }
                $config = null;
            } else if (!empty($this->input->post('product_image_gallery'))) {
                $product_image_gallery = $this->input->post('product_image_gallery');
                foreach ($product_image_gallery as $image_link) {
                    if (!empty($image_link)) {
                        // Validate the URL
                        if (filter_var($image_link, FILTER_VALIDATE_URL) === false) {
                            $this->session->set_flashdata('error', 'Invalid image URL');
                            admin_redirect('products/edit/' . $id);
                        }

                        $image_data = file_get_contents($image_link);

                        if ($image_data === false) {
                            $this->session->set_flashdata('error', 'Failed to retrieve image from URL');
                            admin_redirect('products/edit/' . $id);
                        }

                        $pho = md5(uniqid(rand(), true)) . '.jpg';
                        file_put_contents($this->upload_path . $pho, $image_data);
                        $photos[] = $pho;
                        //$this->processImage($pho);

                        $this->load->library('image_lib');
                        $config['image_library'] = 'gd2';
                        $config['source_image'] = $this->upload_path . $pho;
                        $config['new_image'] = $this->thumbs_path . $pho;
                        $config['maintain_ratio'] = true;
                        $config['width'] = $this->Settings->twidth;
                        $config['height'] = $this->Settings->theight;

                        $this->image_lib->initialize($config);

                        if (!$this->image_lib->resize()) {
                            echo $this->image_lib->display_errors();
                        }

                        if ($this->Settings->watermark) {
                            $this->image_lib->clear();
                            $wm['source_image'] = $this->upload_path . $pho;
                            $wm['wm_text'] = 'Copyright ' . date('Y') . ' - ' . $this->Settings->site_name;
                            $wm['wm_type'] = 'text';
                            $wm['wm_font_path'] = 'system/fonts/texb.ttf';
                            $wm['quality'] = '100';
                            $wm['wm_font_size'] = '16';
                            $wm['wm_font_color'] = '999999';
                            $wm['wm_shadow_color'] = 'CCCCCC';
                            $wm['wm_vrt_alignment'] = 'top';
                            $wm['wm_hor_alignment'] = 'left';
                            $wm['wm_padding'] = '10';
                            $this->image_lib->initialize($wm);
                            $this->image_lib->watermark();
                        }

                        $this->image_lib->clear();
                    }
                }

            } else {
                $photos = null;
            }
            $data['quantity'] = $wh_total_quantity ?? 0;
            // $this->sma->print_arrays($data, $warehouse_qty, $update_variants, $product_attributes, $photos, $items);
        }

        if ($this->form_validation->run() == true && $this->products_model->updateProduct($id, $data, $items, $warehouse_qty, $product_attributes, $photos, $update_variants)) {
            /*if($data['google_merch'] == 1){
                $this->google_merch_apis($id, $data);
            }else{
                $this->session->set_flashdata('message', lang('product_updated'));
                //admin_redirect('products');
                admin_redirect('products/edit/' . $id);
            }*/

            $this->session->set_flashdata('message', lang('product_updated'));
            admin_redirect('products/edit/' . $id);

        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['images'] = $this->products_model->getProductPhotos($id);
            $this->data['categories'] = $this->site->getAllCategories();
            $this->data['tax_rates'] = $this->site->getAllTaxRates();
            $this->data['brands'] = $this->site->getAllBrands();
            $this->data['base_units'] = $this->site->getAllBaseUnits();
            $this->data['warehouses'] = $warehouses;
            $this->data['warehouses_products'] = $warehouses_products;
            $this->data['product'] = $product;
            $this->data['country'] = $this->settings_model->getallCountry();
            $this->data['variants'] = $this->products_model->getAllVariants();
            $this->data['subunits'] = $this->site->getUnitsByBUID($product->unit);
            $this->data['product_variants'] = $this->products_model->getProductOptions($id);
            $this->data['combo_items'] = $product->type == 'combo' ? $this->products_model->getProductComboItems($product->id) : null;
            $this->data['product_options'] = $id ? $this->products_model->getProductOptionsWithWH($id) : null;
            $bc = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('products'), 'page' => lang('products')], ['link' => '#', 'page' => lang('edit_product')]];
            $meta = ['page_title' => lang('edit_product'), 'bc' => $bc];
            $this->page_construct('products/edit', $meta, $this->data);
        }

    }

    // public function remove_image($id) {
    //     // Load the product model
    //     $this->load->model('Product_model');
    //     // Get the product by id
    //     $product = $this->products_model->getProductByID($id);

    //     if ($product) {
    //         // Path to the image file
    //         $image_path = './assets/uploads/'.$product->image;

    //         // Delete the image file from the server
    //         if (file_exists($image_path)) {
    //             unlink($image_path);
    //         }

    //         // Update the database to remove the image reference
    //         $this->Product_model->remove_image($id);

    //         // Set a success message
    //         echo json_encode(['status' => 'Image removed successfully.']);
    //     } else {
    //         // Set an error message
    //         echo json_encode(['status' => 'Product not found.']);
    //     }
    // }

    public function edit_adjustment($id)
    {
        $this->sma->checkPermissions('adjustments', true);
        $adjustment = $this->products_model->getAdjustmentByID($id);
        if (!$id || !$adjustment) {
            $this->session->set_flashdata('error', lang('adjustment_not_found'));
            $this->sma->md();
        }
        $this->form_validation->set_rules('warehouse', lang('warehouse'), 'required');

        if ($this->form_validation->run() == true) {
            if ($this->Owner || $this->Admin) {
                $date = $this->sma->fld($this->input->post('date'));
            } else {
                $date = $adjustment->date;
            }

            $reference_no = $this->input->post('reference_no');
            $warehouse_id = $this->input->post('warehouse');
            $note = $this->sma->clear_tags($this->input->post('note'));

            $i = isset($_POST['product_id']) ? sizeof($_POST['product_id']) : 0;
            for ($r = 0; $r < $i; $r++) {
                $product_id = $_POST['product_id'][$r];
                $type = $_POST['type'][$r];
                $quantity = $_POST['quantity'][$r];
                $equantity = $_POST['edit_quantity'][$r];
                $serial = $_POST['serial'][$r];
                $variant = isset($_POST['variant'][$r]) && !empty($_POST['variant'][$r]) ? $_POST['variant'][$r] : null;

                if (!$this->Settings->overselling && $type == 'subtraction') {
                    if ($variant) {
                        if ($op_wh_qty = $this->products_model->getProductWarehouseOptionQty($variant, $warehouse_id)) {
                            if (($equantity + $op_wh_qty->quantity) < $quantity) {
                                $this->session->set_flashdata('error', lang('warehouse_option_qty_is_less_than_damage'));
                                redirect($_SERVER['HTTP_REFERER']);
                            }
                        } elseif ($equantity < $quantity) {
                            $this->session->set_flashdata('error', lang('warehouse_option_qty_is_less_than_damage'));
                            redirect($_SERVER['HTTP_REFERER']);
                        }
                    }
                    if ($wh_qty = $this->products_model->getProductQuantity($product_id, $warehouse_id)) {
                        if (($equantity + $wh_qty['quantity']) < $quantity) {
                            $this->session->set_flashdata('error', lang('warehouse_qty_is_less_than_damage'));
                            redirect($_SERVER['HTTP_REFERER']);
                        }
                    } elseif ($equantity < $quantity) {
                        $this->session->set_flashdata('error', lang('warehouse_qty_is_less_than_damage'));
                        redirect($_SERVER['HTTP_REFERER']);
                    }
                }

                $products[] = [
                    'product_id' => $product_id,
                    'type' => $type,
                    'quantity' => $quantity,
                    'warehouse_id' => $warehouse_id,
                    'option_id' => $variant,
                    'serial_no' => $serial,
                ];
            }

            if (empty($products)) {
                $this->form_validation->set_rules('product', lang('products'), 'required');
            } else {
                krsort($products);
            }

            $data = [
                'date' => $date,
                'reference_no' => $reference_no,
                'warehouse_id' => $warehouse_id,
                'note' => $note,
                'created_by' => $this->session->userdata('user_id'),
            ];

            if ($_FILES['document']['size'] > 0) {
                $this->load->library('upload');
                $config['upload_path'] = $this->digital_upload_path;
                $config['allowed_types'] = $this->digital_file_types;
                $config['max_size'] = $this->allowed_file_size;
                $config['overwrite'] = false;
                $config['encrypt_name'] = true;
                $this->upload->initialize($config);
                if (!$this->upload->do_upload('document')) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    redirect($_SERVER['HTTP_REFERER']);
                }
                $photo = $this->upload->file_name;
                $data['attachment'] = $photo;
            }

            // $this->sma->print_arrays($data, $products);
        }

        if ($this->form_validation->run() == true && $this->products_model->updateAdjustment($id, $data, $products)) {
            $this->session->set_userdata('remove_qals', 1);
            $this->session->set_flashdata('message', lang('quantity_adjusted'));
            admin_redirect('products/quantity_adjustments');
        } else {
            $inv_items = $this->products_model->getAdjustmentItems($id);
            // krsort($inv_items);
            foreach ($inv_items as $item) {
                $c = sha1(uniqid(mt_rand(), true));
                $product = $this->site->getProductByID($item->product_id);
                $row = json_decode('{}');
                $row->id = $item->product_id;
                $row->code = $product->code;
                $row->name = $product->name;
                $row->qty = $item->quantity;
                $row->oqty = $item->quantity;
                $row->type = $item->type;
                $options = $this->products_model->getProductOptions($product->id);
                $row->option = $item->option_id ? $item->option_id : 0;
                $row->serial = $item->serial_no ? $item->serial_no : '';
                $ri = $this->Settings->item_addition ? $product->id : $c;

                $pr[$ri] = [
                    'id' => $c,
                    'item_id' => $row->id,
                    'label' => $row->name . ' (' . $row->code . ')',
                    'row' => $row,
                    'options' => $options,
                ];
                $c++;
            }

            $this->data['adjustment'] = $adjustment;
            $this->data['adjustment_items'] = json_encode($pr);
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['warehouses'] = $this->site->getAllWarehouses();
            $bc = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('products'), 'page' => lang('products')], ['link' => '#', 'page' => lang('edit_adjustment')]];
            $meta = ['page_title' => lang('edit_adjustment'), 'bc' => $bc];
            $this->page_construct('products/edit_adjustment', $meta, $this->data);
        }
    }

    public function finalize_count($id)
    {
        $this->sma->checkPermissions('stock_count');
        $stock_count = $this->products_model->getStouckCountByID($id);
        if (!$stock_count || $stock_count->finalized) {
            $this->session->set_flashdata('error', lang('stock_count_finalized'));
            admin_redirect('products/stock_counts');
        }

        $this->form_validation->set_rules('count_id', lang('count_stock'), 'required');

        if ($this->form_validation->run() == true) {
            if ($_FILES['csv_file']['size'] > 0) {
                $note = $this->sma->clear_tags($this->input->post('note'));
                $data = [
                    'updated_by' => $this->session->userdata('user_id'),
                    'updated_at' => date('Y-m-d H:s:i'),
                    'note' => $note,
                ];

                $this->load->library('upload');
                $config['upload_path'] = $this->digital_upload_path;
                $config['allowed_types'] = 'csv';
                $config['max_size'] = $this->allowed_file_size;
                $config['overwrite'] = false;
                $config['encrypt_name'] = true;
                $this->upload->initialize($config);
                if (!$this->upload->do_upload('csv_file')) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    redirect($_SERVER['HTTP_REFERER']);
                }

                $csv = $this->upload->file_name;

                $arrResult = [];
                $handle = fopen($this->digital_upload_path . $csv, 'r');
                if ($handle) {
                    while (($row = fgetcsv($handle, 5000, ',')) !== false) {
                        $arrResult[] = $row;
                    }
                    fclose($handle);
                }
                $titles = array_shift($arrResult);
                $keys = ['product_code', 'product_name', 'product_variant', 'expected', 'counted'];
                $final = [];
                foreach ($arrResult as $key => $value) {
                    $final[] = array_combine($keys, $value);
                }
                // $this->sma->print_arrays($final);
                $rw = 2;
                $differences = 0;
                $matches = 0;
                foreach ($final as $pr) {
                    if ($product = $this->products_model->getProductByCode(trim($pr['product_code']))) {
                        $pr['counted'] = !empty($pr['counted']) ? $pr['counted'] : 0;
                        if ($pr['expected'] == $pr['counted']) {
                            $matches++;
                        } else {
                            $pr['stock_count_id'] = $id;
                            $pr['product_id'] = $product->id;
                            $pr['cost'] = $product->cost;
                            $pr['product_variant_id'] = empty($pr['product_variant']) ? null : $this->products_model->getProductVariantID($pr['product_id'], $pr['product_variant']);
                            $products[] = $pr;
                            $differences++;
                        }
                    } else {
                        $this->session->set_flashdata('error', lang('check_product_code') . ' (' . $pr['product_code'] . '). ' . lang('product_code_x_exist') . ' ' . lang('line_no') . ' ' . $rw);
                        admin_redirect('products/finalize_count/' . $id);
                    }
                    $rw++;
                }

                $data['final_file'] = $csv;
                $data['differences'] = $differences;
                $data['matches'] = $matches;
                $data['missing'] = $stock_count->rows - ($rw - 2);
                $data['finalized'] = 1;
            }

            // $this->sma->print_arrays($data, $products);
        }

        if ($this->form_validation->run() == true && $this->products_model->finalizeStockCount($id, $data, $products)) {
            $this->session->set_flashdata('message', lang('stock_count_finalized'));
            admin_redirect('products/stock_counts');
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['stock_count'] = $stock_count;
            $this->data['warehouse'] = $this->site->getWarehouseByID($stock_count->warehouse_id);
            $bc = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('products'), 'page' => lang('products')], ['link' => admin_url('products/stock_counts'), 'page' => lang('stock_counts')], ['link' => '#', 'page' => lang('finalize_count')]];
            $meta = ['page_title' => lang('finalize_count'), 'bc' => $bc];
            $this->page_construct('products/finalize_count', $meta, $this->data);
        }
    }

    public function get_suggestions()
    {
        $term = $this->input->get('term', true);
        if (strlen($term) < 1 || !$term) {
            die("<script type='text/javascript'>setTimeout(function(){ window.top.location.href = '" . admin_url('welcome') . "'; }, 10);</script>");
        }
        $term = addslashes($term);
        $rows = $this->products_model->getProductsForPrinting($term);
        if ($rows) {
            foreach ($rows as $row) {
                $variants = $this->products_model->getProductOptions($row->id);
                $pr[] = ['id' => $row->id, 'label' => $row->name . ' (' . $row->code . ')' . ($row->sequence_code ? ' - ' . $row->sequence_code : ''), 'code' => $row->code, 'name' => $row->name, 'sequence_code' => $row->code, 'price' => $row->price, 'qty' => 1, 'variants' => $variants];
            }
            $this->sma->send_json($pr);
        } else {
            $this->sma->send_json([['id' => 0, 'label' => lang('no_match_found'), 'value' => $term]]);
        }
    }

    public function getadjustments($warehouse_id = null)
    {
        $this->sma->checkPermissions('adjustments');

        $delete_link = "<a href='#' class='tip po' title='<b>" . $this->lang->line('delete_adjustment') . "</b>' data-content=\"<p>"
            . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . admin_url('products/delete_adjustment/$1') . "'>"
            . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i></a>";

        $this->load->library('datatables');
        $this->datatables
            ->select("{$this->db->dbprefix('adjustments')}.id as id, date, reference_no, warehouses.name as wh_name, CONCAT({$this->db->dbprefix('users')}.first_name, ' ', {$this->db->dbprefix('users')}.last_name) as created_by, note, attachment")
            ->from('adjustments')
            ->join('warehouses', 'warehouses.id=adjustments.warehouse_id', 'left')
            ->join('users', 'users.id=adjustments.created_by', 'left')
            ->group_by('adjustments.id');
        if ($warehouse_id) {
            $this->datatables->where('adjustments.warehouse_id', $warehouse_id);
        }
        $this->datatables->add_column('Actions', "<div class='text-center'><a href='" . admin_url('products/edit_adjustment/$1') . "' class='tip' title='" . lang('edit_adjustment') . "'><i class='fa fa-edit'></i></a> " . $delete_link . '</div>', 'id');

        echo $this->datatables->generate();
    }

    public function getCounts($warehouse_id = null)
    {
        $this->sma->checkPermissions('stock_count', true);

        if ((!$this->Owner || !$this->Admin) && !$warehouse_id) {
            $user = $this->site->getUser();
            $warehouse_id = $user->warehouse_id;
        }
        $detail_link = anchor('admin/products/view_count/$1', '<label class="label label-primary pointer">' . lang('details') . '</label>', 'class="tip" title="' . lang('details') . '" data-toggle="modal" data-target="#myModal"');

        $this->load->library('datatables');
        $this->datatables
            ->select("{$this->db->dbprefix('stock_counts')}.id as id, date, reference_no, {$this->db->dbprefix('warehouses')}.name as wh_name, type, brand_names, category_names, initial_file, final_file")
            ->from('stock_counts')
            ->join('warehouses', 'warehouses.id=stock_counts.warehouse_id', 'left');
        if ($warehouse_id) {
            $this->datatables->where('warehouse_id', $warehouse_id);
        }

        $this->datatables->add_column('Actions', '<div class="text-center">' . $detail_link . '</div>', 'id');
        echo $this->datatables->generate();
    }

    public function getHiddenProducts($warehouse_id = null)
    {
        $this->sma->checkPermissions('index', true);
        $supplier = $this->input->get('supplier') ? $this->input->get('supplier') : null;

        if ((!$this->Owner && !$this->Admin) && !$warehouse_id) {
            $user = $this->site->getUser();
            $warehouse_id = $user->warehouse_id;
        }
        $detail_link = anchor('admin/products/view/$1', '<i class="fa fa-file-text-o"></i> ' . lang('product_details'));
        $delete_link = "<a href='#' class='tip po' title='<b>" . $this->lang->line('delete_product') . "</b>' data-content=\"<p>"
            . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete1' id='a__$1' href='" . admin_url('products/delete/$1') . "'>"
            . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
            . lang('delete_product') . '</a>';
        $single_barcode = anchor('admin/products/print_barcodes/$1', '<i class="fa fa-print"></i> ' . lang('print_barcode_label'));
        // $single_label = anchor_popup('products/single_label/$1/' . ($warehouse_id ? $warehouse_id : ''), '<i class="fa fa-print"></i> ' . lang('print_label'), $this->popup_attributes);
        $action = '<div class="text-center"><div class="btn-group text-left">'
            . '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
            . lang('actions') . ' <span class="caret"></span></button>
        <ul class="dropdown-menu pull-right" role="menu">
            <li>' . $detail_link . '</li>
            <li><a href="' . admin_url('products/add/$1') . '"><i class="fa fa-plus-square"></i> ' . lang('duplicate_product') . '</a></li>
            <li><a href="' . admin_url('products/edit/$1') . '"><i class="fa fa-edit"></i> ' . lang('edit_product') . '</a></li>';
        if ($warehouse_id) {
            $action .= '<li><a href="' . admin_url('products/set_rack/$1/' . $warehouse_id) . '" data-toggle="modal" data-target="#myModal"><i class="fa fa-bars"></i> '
                . lang('set_rack') . '</a></li>';
        }
        $action .= '<li><a href="' . base_url() . 'assets/uploads/$2" data-type="image" data-toggle="lightbox"><i class="fa fa-file-photo-o"></i> '
            . lang('view_image') . '</a></li>
            <li>' . $single_barcode . '</li>
            <li class="divider"></li>
            <li>' . $delete_link . '</li>
            </ul>
        </div></div>';
        $this->load->library('datatables');
        if ($warehouse_id) {
            $this->datatables
                ->select($this->db->dbprefix('products') . ".id as productid, {$this->db->dbprefix('products')}.image as image, {$this->db->dbprefix('products')}.code as code,{$this->db->dbprefix('products')}.sequence_code as sequence_code, {$this->db->dbprefix('products')}.name as name, {$this->db->dbprefix('brands')}.name as brand, {$this->db->dbprefix('categories')}.name as cname, cost as cost, price as price, SUM(wp.quantity) as quantity, {$this->db->dbprefix('units')}.code as unit, wp.rack as rack, alert_quantity", false)
                ->from('products');
            if ($this->Settings->display_all_products) {
                $this->datatables->join('warehouses_products wp', "wp.product_id=products.id AND wp.warehouse_id={$warehouse_id}", 'left');
                // $this->datatables->join("( SELECT product_id, quantity, rack from {$this->db->dbprefix('warehouses_products')} WHERE warehouse_id = {$warehouse_id}) wp", 'products.id=wp.product_id', 'left');
            } else {
                $this->datatables->join('warehouses_products wp', 'products.id=wp.product_id', 'left')
                    ->where('wp.warehouse_id', $warehouse_id)
                    ->where('wp.quantity !=', 0);
            }
            $this->datatables->join('categories', 'products.category_id=categories.id', 'left')
                ->join('units', 'products.unit=units.id', 'left')
                ->join('brands', 'products.brand=brands.id', 'left')
                ->where('products.hide', 1)
                ->where('products.draft', 0)
                ->where('products.category_id !=', 29)
                ->group_by("products.id");
        } else {

            $this->datatables
                ->select($this->db->dbprefix('products') . ".id as productid, {$this->db->dbprefix('products')}.image as image, {$this->db->dbprefix('products')}.code as code,{$this->db->dbprefix('products')}.sequence_code as sequence_code, {$this->db->dbprefix('products')}.name as name, {$this->db->dbprefix('brands')}.name as brand, {$this->db->dbprefix('categories')}.name as cname, cost as cost, price as price, SUM(wp.quantity) as quantity, {$this->db->dbprefix('units')}.code as unit, wp.rack as rack, alert_quantity", false)
                ->from('products');
            if ($this->Settings->display_all_products) {
                $this->datatables->join('warehouses_products wp', "wp.product_id=products.id", 'left');
            } else {
                $this->datatables->join('warehouses_products wp', 'products.id=wp.product_id', 'left')
                    ->where('wp.quantity !=', 0);
            }
            $this->datatables->join('categories', 'products.category_id=categories.id', 'left')
                ->join('units', 'products.unit=units.id', 'left')
                ->join('brands', 'products.brand=brands.id', 'left')
                ->where('products.hide', 1)
                ->where('products.draft', 0)
                ->where('products.category_id !=', 29)
                ->group_by("products.id");
        }
        if (!$this->Owner && !$this->Admin) {
            if (!$this->session->userdata('show_cost')) {
                $this->datatables->unset_column('cost');
            }
            if (!$this->session->userdata('show_price')) {
                $this->datatables->unset_column('price');
            }
        }
        if ($supplier) {
            $this->datatables->group_start()
                ->where('supplier1', $supplier)
                ->or_where('supplier2', $supplier)
                ->or_where('supplier3', $supplier)
                ->or_where('supplier4', $supplier)
                ->or_where('supplier5', $supplier)
                ->group_end();
        }
        $this->datatables->add_column('Actions', $action, 'productid, image, code, name');
        echo $this->datatables->generate();
    }

    public function getDraftProducts($warehouse_id = null)
    {
        $this->sma->checkPermissions('index', true);
        $supplier = $this->input->get('supplier') ? $this->input->get('supplier') : null;

        if ((!$this->Owner && !$this->Admin) && !$warehouse_id) {
            $user = $this->site->getUser();
            $warehouse_id = $user->warehouse_id;
        }
        $detail_link = anchor('admin/products/view/$1', '<i class="fa fa-file-text-o"></i> ' . lang('product_details'));
        $delete_link = "<a href='#' class='tip po' title='<b>" . $this->lang->line('delete_product') . "</b>' data-content=\"<p>"
            . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete1' id='a__$1' href='" . admin_url('products/delete/$1') . "'>"
            . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
            . lang('delete_product') . '</a>';
        $single_barcode = anchor('admin/products/print_barcodes/$1', '<i class="fa fa-print"></i> ' . lang('print_barcode_label'));
        // $single_label = anchor_popup('products/single_label/$1/' . ($warehouse_id ? $warehouse_id : ''), '<i class="fa fa-print"></i> ' . lang('print_label'), $this->popup_attributes);
        $action = '<div class="text-center"><div class="btn-group text-left">'
            . '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
            . lang('actions') . ' <span class="caret"></span></button>
        <ul class="dropdown-menu pull-right" role="menu">
            <li>' . $detail_link . '</li>
            <li><a href="' . admin_url('products/add/$1') . '"><i class="fa fa-plus-square"></i> ' . lang('duplicate_product') . '</a></li>
            <li><a href="' . admin_url('products/edit/$1') . '"><i class="fa fa-edit"></i> ' . lang('edit_product') . '</a></li>';
        if ($warehouse_id) {
            $action .= '<li><a href="' . admin_url('products/set_rack/$1/' . $warehouse_id) . '" data-toggle="modal" data-target="#myModal"><i class="fa fa-bars"></i> '
                . lang('set_rack') . '</a></li>';
        }
        $action .= '<li><a href="' . base_url() . 'assets/uploads/$2" data-type="image" data-toggle="lightbox"><i class="fa fa-file-photo-o"></i> '
            . lang('view_image') . '</a></li>
            <li>' . $single_barcode . '</li>
            <li class="divider"></li>
            <li>' . $delete_link . '</li>
            </ul>
        </div></div>';
        $this->load->library('datatables');
        if ($warehouse_id) {
            $this->datatables
                ->select($this->db->dbprefix('products') . ".id as productid, {$this->db->dbprefix('products')}.image as image, {$this->db->dbprefix('products')}.code as code,{$this->db->dbprefix('products')}.sequence_code as sequence_code, {$this->db->dbprefix('products')}.name as name, {$this->db->dbprefix('brands')}.name as brand, {$this->db->dbprefix('categories')}.name as cname, cost as cost, price as price, SUM(wp.quantity) as quantity, {$this->db->dbprefix('units')}.code as unit, wp.rack as rack, alert_quantity", false)
                ->from('products');
            if ($this->Settings->display_all_products) {
                $this->datatables->join('warehouses_products wp', "wp.product_id=products.id AND wp.warehouse_id={$warehouse_id}", 'left');
                // $this->datatables->join("( SELECT product_id, quantity, rack from {$this->db->dbprefix('warehouses_products')} WHERE warehouse_id = {$warehouse_id}) wp", 'products.id=wp.product_id', 'left');
            } else {
                $this->datatables->join('warehouses_products wp', 'products.id=wp.product_id', 'left')
                    ->where('wp.warehouse_id', $warehouse_id)
                    ->where('wp.quantity !=', 0);
            }
            $this->datatables->join('categories', 'products.category_id=categories.id', 'left')
                ->join('units', 'products.unit=units.id', 'left')
                ->join('brands', 'products.brand=brands.id', 'left')
                ->where('products.draft', 1)
                ->group_by("products.id");
        } else {

            $this->datatables
                ->select($this->db->dbprefix('products') . ".id as productid, {$this->db->dbprefix('products')}.image as image, {$this->db->dbprefix('products')}.code as code,{$this->db->dbprefix('products')}.sequence_code as sequence_code, {$this->db->dbprefix('products')}.name as name, {$this->db->dbprefix('brands')}.name as brand, {$this->db->dbprefix('categories')}.name as cname, cost as cost, price as price, SUM(wp.quantity) as quantity, {$this->db->dbprefix('units')}.code as unit, wp.rack as rack, alert_quantity", false)
                ->from('products');
            if ($this->Settings->display_all_products) {
                $this->datatables->join('warehouses_products wp', "wp.product_id=products.id", 'left');
            } else {
                $this->datatables->join('warehouses_products wp', 'products.id=wp.product_id', 'left')
                    ->where('wp.quantity !=', 0);
            }
            $this->datatables->join('categories', 'products.category_id=categories.id', 'left')
                ->join('units', 'products.unit=units.id', 'left')
                ->join('brands', 'products.brand=brands.id', 'left')
                ->where('products.draft', 1)
                ->group_by("products.id");
        }
        if (!$this->Owner && !$this->Admin) {
            if (!$this->session->userdata('show_cost')) {
                $this->datatables->unset_column('cost');
            }
            if (!$this->session->userdata('show_price')) {
                $this->datatables->unset_column('price');
            }
        }
        if ($supplier) {
            $this->datatables->group_start()
                ->where('supplier1', $supplier)
                ->or_where('supplier2', $supplier)
                ->or_where('supplier3', $supplier)
                ->or_where('supplier4', $supplier)
                ->or_where('supplier5', $supplier)
                ->group_end();
        }
        $this->datatables->add_column('Actions', $action, 'productid, image, code, name');
        echo $this->datatables->generate();
    }

    public function getShopProducts($warehouse_id = null)
    {
        $this->sma->checkPermissions('index', true);
        $supplier = $this->input->get('supplier') ? $this->input->get('supplier') : null;

        if ((!$this->Owner && !$this->Admin) && !$warehouse_id) {
            $user = $this->site->getUser();
            $warehouse_id = $user->warehouse_id;
        }
        $detail_link = anchor('admin/products/view/$1', '<i class="fa fa-file-text-o"></i> ' . lang('product_details'));
        $delete_link = "<a href='#' class='tip po' title='<b>" . $this->lang->line('delete_product') . "</b>' data-content=\"<p>"
            . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete1' id='a__$1' href='" . admin_url('products/delete/$1') . "'>"
            . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
            . lang('delete_product') . '</a>';
        $single_barcode = anchor('admin/products/print_barcodes/$1', '<i class="fa fa-print"></i> ' . lang('print_barcode_label'));
        // $single_label = anchor_popup('products/single_label/$1/' . ($warehouse_id ? $warehouse_id : ''), '<i class="fa fa-print"></i> ' . lang('print_label'), $this->popup_attributes);
        $action = '<div class="text-center"><div class="btn-group text-left">'
            . '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
            . lang('actions') . ' <span class="caret"></span></button>
        <ul class="dropdown-menu pull-right" role="menu">
            <li>' . $detail_link . '</li>
            <li><a href="' . admin_url('products/add/$1') . '"><i class="fa fa-plus-square"></i> ' . lang('duplicate_product') . '</a></li>
            <li><a href="' . admin_url('products/edit/$1') . '"><i class="fa fa-edit"></i> ' . lang('edit_product') . '</a></li>';
        if ($warehouse_id) {
            $action .= '<li><a href="' . admin_url('products/set_rack/$1/' . $warehouse_id) . '" data-toggle="modal" data-target="#myModal"><i class="fa fa-bars"></i> '
                . lang('set_rack') . '</a></li>';
        }
        $action .= '<li><a href="' . base_url() . 'assets/uploads/$2" data-type="image" data-toggle="lightbox"><i class="fa fa-file-photo-o"></i> '
            . lang('view_image') . '</a></li>
            <li>' . $single_barcode . '</li>
            <li class="divider"></li>
            <li>' . $delete_link . '</li>
            </ul>
        </div></div>';
        $this->load->library('datatables');
        if ($warehouse_id) {
            $this->datatables
                ->select($this->db->dbprefix('products') . ".id as productid, {$this->db->dbprefix('products')}.image as image, {$this->db->dbprefix('products')}.code as code,{$this->db->dbprefix('products')}.sequence_code as sequence_code, {$this->db->dbprefix('products')}.name as name, {$this->db->dbprefix('brands')}.name as brand, {$this->db->dbprefix('categories')}.name as cname, cost as cost, price as price, SUM(wp.quantity) as quantity, {$this->db->dbprefix('units')}.code as unit, wp.rack as rack, alert_quantity", false)
                ->from('products');
            if ($this->Settings->display_all_products) {
                $this->datatables->join('warehouses_products wp', "wp.product_id=products.id AND wp.warehouse_id={$warehouse_id}", 'left');
                // $this->datatables->join("( SELECT product_id, quantity, rack from {$this->db->dbprefix('warehouses_products')} WHERE warehouse_id = {$warehouse_id}) wp", 'products.id=wp.product_id', 'left');
            } else {
                $this->datatables->join('warehouses_products wp', 'products.id=wp.product_id', 'left')
                    ->where('wp.warehouse_id', $warehouse_id)
                    ->where('wp.quantity !=', 0);
            }
            $this->datatables->join('categories', 'products.category_id=categories.id', 'left')
                ->join('units', 'products.unit=units.id', 'left')
                ->join('brands', 'products.brand=brands.id', 'left')
                ->where('products.hide', 0)
                ->group_by("products.id");
        } else {

            $this->datatables
                ->select($this->db->dbprefix('products') . ".id as productid, {$this->db->dbprefix('products')}.image as image, {$this->db->dbprefix('products')}.code as code,{$this->db->dbprefix('products')}.sequence_code as sequence_code, {$this->db->dbprefix('products')}.name as name, {$this->db->dbprefix('brands')}.name as brand, {$this->db->dbprefix('categories')}.name as cname, cost as cost, price as price, SUM(wp.quantity) as quantity, {$this->db->dbprefix('units')}.code as unit, wp.rack as rack, alert_quantity", false)
                ->from('products');
            if ($this->Settings->display_all_products) {
                $this->datatables->join('warehouses_products wp', "wp.product_id=products.id", 'left');
            } else {
                $this->datatables->join('warehouses_products wp', 'products.id=wp.product_id', 'left')
                    ->where('wp.quantity !=', 0);
            }
            $this->datatables->join('categories', 'products.category_id=categories.id', 'left')
                ->join('units', 'products.unit=units.id', 'left')
                ->join('brands', 'products.brand=brands.id', 'left')
                ->where('products.hide', 0)
                ->group_by("products.id");
        }
        if (!$this->Owner && !$this->Admin) {
            if (!$this->session->userdata('show_cost')) {
                $this->datatables->unset_column('cost');
            }
            if (!$this->session->userdata('show_price')) {
                $this->datatables->unset_column('price');
            }
        }
        if ($supplier) {
            $this->datatables->group_start()
                ->where('supplier1', $supplier)
                ->or_where('supplier2', $supplier)
                ->or_where('supplier3', $supplier)
                ->or_where('supplier4', $supplier)
                ->or_where('supplier5', $supplier)
                ->group_end();
        }
        $this->datatables->add_column('Actions', $action, 'productid, image, code, name');
        echo $this->datatables->generate();
    }

    public function getProducts($warehouse_id = null)
    {
        $this->sma->checkPermissions('index', true);
        $supplier = $this->input->get('supplier') ? $this->input->get('supplier') : null;

        if ((!$this->Owner && !$this->Admin) && !$warehouse_id) {
            $user = $this->site->getUser();
            $warehouse_id = $user->warehouse_id;
        }
        $detail_link = anchor('admin/products/view/$1', '<i class="fa fa-file-text-o"></i> ' . lang('product_details'));
        $delete_link = "<a href='#' class='tip po' title='<b>" . $this->lang->line('delete_product') . "</b>' data-content=\"<p>"
            . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete1' id='a__$1' href='" . admin_url('products/delete/$1') . "'>"
            . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
            . lang('delete_product') . '</a>';
        $single_barcode = anchor('admin/products/print_barcodes/$1', '<i class="fa fa-print"></i> ' . lang('print_barcode_label'));
        // $single_label = anchor_popup('products/single_label/$1/' . ($warehouse_id ? $warehouse_id : ''), '<i class="fa fa-print"></i> ' . lang('print_label'), $this->popup_attributes);
        $action = '<div class="text-center"><div class="btn-group text-left">'
            . '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
            . lang('actions') . ' <span class="caret"></span></button>
        <ul class="dropdown-menu pull-right" role="menu">
            <li>' . $detail_link . '</li>';
        if ($this->Admin || $this->Owner) {
            $action .= '<li><a href="' . admin_url('products/add/$1') . '"><i class="fa fa-plus-square"></i> ' . lang('duplicate_product') . '</a></li>';
        }

        $action .= '<li><a href="' . admin_url('products/edit/$1') . '"><i class="fa fa-edit"></i> ' . lang('edit_product') . '</a></li>';
        if ($warehouse_id) {
            $action .= '<li><a href="' . admin_url('products/set_rack/$1/' . $warehouse_id) . '" data-toggle="modal" data-target="#myModal"><i class="fa fa-bars"></i> '
                . lang('set_rack') . '</a></li>';
        }
        $action .= '<li><a href="' . base_url() . 'assets/uploads/$2" data-type="image" data-toggle="lightbox"><i class="fa fa-file-photo-o"></i> '
            . lang('view_image') . '</a></li>';
        if ($this->Admin || $this->Owner) {
            $action .= '<li>' . $single_barcode . '</li>';
        }

        $action .= '<li class="divider"></li>';
        if ($this->Admin || $this->Owner) {
            $action .= '<li>' . $delete_link . '</li>';
        }
        $action .= '</ul>
        </div></div>';
        $this->load->library('datatables');
        if ($warehouse_id) {
            // wp.rack as rack replaced with 1 as rack
            $this->datatables
                ->select($this->db->dbprefix('products') . ".id as productid, {$this->db->dbprefix('products')}.image as image, {$this->db->dbprefix('products')}.code as code,{$this->db->dbprefix('products')}.sequence_code as sequence_code, {$this->db->dbprefix('products')}.name as name, {$this->db->dbprefix('brands')}.name as brand, {$this->db->dbprefix('categories')}.name as cname, cost as cost, price as price, SUM(wp.quantity) as quantity, {$this->db->dbprefix('units')}.code as unit, 1 as rack, alert_quantity", false)
                ->from('products');
            if ($this->Settings->display_all_products) {
                $this->datatables->join('inventory_movements wp', "wp.product_id=products.id AND wp.location_id={$warehouse_id}", 'left');
                //$this->datatables->join('warehouses_products wp', "wp.product_id=products.id AND wp.warehouse_id={$warehouse_id}", 'left');
                // $this->datatables->join("( SELECT product_id, quantity, rack from {$this->db->dbprefix('warehouses_products')} WHERE warehouse_id = {$warehouse_id}) wp", 'products.id=wp.product_id', 'left');
            } else {
                $this->datatables->join('inventory_movements wp', "wp.product_id=products.id", 'left')
                    ->where('wp.location_id', $warehouse_id)
                ;
                // $this->datatables->join('warehouses_products wp', 'products.id=wp.product_id', 'left')
                // ->where('wp.warehouse_id', $warehouse_id)
                // ->where('wp.quantity !=', 0);
            }
            $this->datatables->join('categories', 'products.category_id=categories.id', 'left')
                ->join('units', 'products.unit=units.id', 'left')
                ->join('brands', 'products.brand=brands.id', 'left')
                ->group_by("products.id");
        } else {
            // wp.rack as rack replaced with 1 as rack
            $this->datatables
                ->select($this->db->dbprefix('products') . ".id as productid, {$this->db->dbprefix('products')}.image as image, {$this->db->dbprefix('products')}.code as code,{$this->db->dbprefix('products')}.sequence_code as sequence_code, {$this->db->dbprefix('products')}.name as name, {$this->db->dbprefix('brands')}.name as brand, {$this->db->dbprefix('categories')}.name as cname, cost as cost, price as price, SUM(wp.quantity) as quantity, {$this->db->dbprefix('units')}.code as unit, 1 as rack, alert_quantity", false)
                ->from('products');
            $this->datatables->join('inventory_movements wp', "wp.product_id=products.id", 'left');
            if ($this->Settings->display_all_products) {
                //  $this->datatables->join('warehouses_products wp', "wp.product_id=products.id", 'left'); 
            } else {
                //  $this->datatables->join('warehouses_products wp', 'products.id=wp.product_id', 'left')->where('wp.quantity !=', 0);     
            }
            $this->datatables->join('categories', 'products.category_id=categories.id', 'left')
                ->join('units', 'products.unit=units.id', 'left')
                ->join('brands', 'products.brand=brands.id', 'left')
                ->group_by("products.id");
        }
        if (!$this->Owner && !$this->Admin) {
            if (!$this->session->userdata('show_cost')) {
                $this->datatables->unset_column('cost');
            }
            if (!$this->session->userdata('show_price')) {
                $this->datatables->unset_column('price');
            }
        }
        if ($supplier) {
            $this->datatables->group_start()
                ->where('supplier1', $supplier)
                ->or_where('supplier2', $supplier)
                ->or_where('supplier3', $supplier)
                ->or_where('supplier4', $supplier)
                ->or_where('supplier5', $supplier)
                ->group_end();
        }
        $this->datatables->add_column('Actions', $action, 'productid, image, code, name');
        echo $this->datatables->generate();
    }

    public function getSubCategories($category_id = null)
    {
        if ($rows = $this->products_model->getSubCategories($category_id)) {
            $data = json_encode($rows);
        } else {
            $data = false;
        }
        echo $data;
    }

    public function getSubUnits($unit_id)
    {
        // $unit = $this->site->getUnitByID($unit_id);
        // if ($units = $this->site->getUnitsByBUID($unit_id)) {
        //     array_push($units, $unit);
        // } else {
        //     $units = array($unit);
        // }
        $units = $this->site->getUnitsByBUID($unit_id);
        $this->sma->send_json($units);
    }

    /* ---------------------------------------------------------------- */

    public function import_csv()
    {
        //$this->sma->checkPermissions('csv');
        $this->load->helper('security');
        $this->form_validation->set_rules('userfile', lang('upload_file'), 'xss_clean');

        if ($this->form_validation->run() == true) {
            if (isset($_FILES['userfile'])) {
                $this->load->library('upload');
                $config['upload_path'] = $this->digital_upload_path;
                $config['allowed_types'] = 'csv';
                $config['max_size'] = $this->allowed_file_size;
                $config['overwrite'] = true;
                $config['encrypt_name'] = true;
                $config['max_filename'] = 25;
                $this->upload->initialize($config);

                if (!$this->upload->do_upload()) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    admin_redirect('products/import_csv');
                }

                $csv = $this->upload->file_name;

                $arrResult = [];
                $handle = fopen($this->digital_upload_path . $csv, 'r');
                if ($handle) {
                    while (($row = fgetcsv($handle, 5000, ',')) !== false) {
                        $arrResult[] = $row;
                    }
                    fclose($handle);
                }
                $arr_length = count($arrResult);
                if ($arr_length > 5000000) {
                    $this->session->set_flashdata('error', lang('too_many_products'));
                    redirect($_SERVER['HTTP_REFERER']);
                    exit();
                }
                $titles = array_shift($arrResult);
                $updated = 0;
                $items = [];
                foreach ($arrResult as $key => $value) {
                    $supplier_name1 = isset($value[26]) ? trim($value[26]) : '';
                    $supplier1 = $supplier_name1 ? $this->products_model->getSupplierByName($supplier_name1) : null;
                    $supplier_name2 = isset($value[29]) ? trim($value[29]) : '';
                    $supplier2 = $supplier_name2 ? $this->products_model->getSupplierByName($supplier_name2) : null;
                    $supplier_name3 = isset($value[32]) ? trim($value[32]) : '';
                    $supplier3 = $supplier_name3 ? $this->products_model->getSupplierByName($supplier_name3) : null;
                    $supplier_name4 = isset($value[35]) ? trim($value[35]) : '';
                    $supplier4 = $supplier_name4 ? $this->products_model->getSupplierByName($supplier_name4) : null;
                    $supplier_name5 = isset($value[38]) ? trim($value[38]) : '';
                    $supplier5 = $supplier_name5 ? $this->products_model->getSupplierByName($supplier_name5) : null;

                    $slug = preg_replace('/[^a-zA-Z0-9]+/', '-', trim($value[0]));
                    $slug = strtolower($slug);
                    $slug = trim($slug, '-');
                    $slug = $slug . '-' . trim($value[1]);

                    $item = [
                        'name' => isset($value[0]) ? trim($value[0]) : '',
                        'code' => isset($value[1]) ? trim($value[1]) : '',
                        'barcode_symbology' => isset($value[2]) ? mb_strtolower(trim($value[2]), 'UTF-8') : '',
                        'brand' => isset($value[3]) ? trim($value[3]) : '',
                        'category_code' => isset($value[4]) ? trim($value[4]) : '',
                        'unit' => isset($value[5]) ? trim($value[5]) : 1,
                        'sale_unit' => isset($value[6]) ? trim($value[6]) : 1,
                        'purchase_unit' => isset($value[7]) ? trim($value[7]) : 1,
                        'cost' => isset($value[8]) ? $this->sma->formatDecimal( trim($value[8]) ): '',
                        'price' => isset($value[9]) ? $this->sma->formatDecimal( trim($value[9]) ): '',
                        'alert_quantity' => isset($value[10]) ? trim($value[10]) : 2,
                        'tax_rate' => isset($value[11]) ? trim($value[11]) : '',
                        'tax_method' => isset($value[12]) ? (trim($value[12]) == 'exclusive' ? 1 : 0) : '',
                        //'image'             => isset($value[13]) ? trim($value[13]) : '',
                        'subcategory_code' => isset($value[14]) ? trim($value[14]) : '',
                        //'variants'          => isset($value[15]) ? trim($value[15]) : '',
                        'cf1' => isset($value[16]) ? trim($value[16]) : 8,
                        //'cf2'               => isset($value[17]) ? trim($value[17]) : '',
                        //'cf3'               => isset($value[18]) ? trim($value[18]) : '',
                        //'cf4'               => isset($value[19]) ? trim($value[19]) : '',
                        //'cf5'               => isset($value[20]) ? trim($value[20]) : '',
                        //'cf6'               => isset($value[21]) ? trim($value[21]) : '',
                        //'hsn_code'          => isset($value[22]) ? trim($value[22]) : '',
                        //'second_name'       => isset($value[23]) ? trim($value[23]) : '',
                        //'details'           => isset($value[24]) ? trim($value[24]) : '',
                        //'product_details'   => isset($value[25]) ? trim($value[25]) : '',
                        //'supplier1'         => $supplier1 ? $supplier1->id : null,
                        //'supplier1_part_no' => isset($value[27]) ? trim($value[27]) : '',
                        //'supplier1price'    => isset($value[28]) ? trim($value[28]) : '',
                        //'supplier2'         => $supplier2 ? $supplier2->id : null,
                        //'supplier2_part_no' => isset($value[30]) ? trim($value[30]) : '',
                        //'supplier2price'    => isset($value[31]) ? trim($value[31]) : '',
                        //'supplier3'         => $supplier3 ? $supplier3->id : null,
                        //'supplier3_part_no' => isset($value[33]) ? trim($value[33]) : '',
                        //'supplier3price'    => isset($value[34]) ? trim($value[34]) : '',
                        //'supplier4'         => $supplier4 ? $supplier4->id : null,
                        //'supplier4_part_no' => isset($value[36]) ? trim($value[36]) : '',
                        //'supplier4price'    => isset($value[37]) ? trim($value[37]) : '',
                        //'supplier5'         => $supplier5 ? $supplier5->id : null,
                        //'supplier5_part_no' => isset($value[39]) ? trim($value[39]) : '',
                        //'supplier5price'    => isset($value[40]) ? trim($value[40]) : '',
                        //'slug'              => $this->Settings->use_code_for_slug ? $this->sma->slug($value[1]) : $this->sma->slug($value[0]),
                        'slug' => $slug,
                        'hide' => isset($value[41]) ? trim($value[41]) : 1,
                        'draft' => isset($value[42]) ? trim($value[42]) : 1,
                        'item_code' => isset($value[43]) ? trim($value[43]) : NULL
                    ];

                    if ($catd = $this->products_model->getCategoryByCode($item['category_code'])) {
                        $tax_details = $this->products_model->getTaxRateByName($item['tax_rate']);
                        $prsubcat = $this->products_model->getCategoryByCode($item['subcategory_code']);
                        $brand = $this->products_model->getBrandByName($item['brand']);
                        $unit = $this->products_model->getUnitByCode($item['unit']);
                        $base_unit = $unit ? $unit->id : null;
                        $sale_unit = $base_unit;
                        $purcahse_unit = $base_unit;
                        if ($base_unit) {
                            $units = $this->site->getUnitsByBUID($base_unit);
                            foreach ($units as $u) {
                                if ($u->code == $item['sale_unit']) {
                                    $sale_unit = $u->id;
                                }
                                if ($u->code == $item['purchase_unit']) {
                                    $purcahse_unit = $u->id;
                                }
                            }
                        } else {
                            $this->session->set_flashdata('error', lang('check_unit') . ' (' . $item['unit'] . '). ' . lang('unit_code_x_exist') . ' ' . lang('line_no') . ' ' . ($key + 1));
                            admin_redirect('products/import_csv');
                        }

                        unset($item['category_code'], $item['subcategory_code']);
                        $item['unit'] = $base_unit;
                        $item['sale_unit'] = $sale_unit;
                        $item['category_id'] = $catd->id;
                        $item['purchase_unit'] = $purcahse_unit;
                        $item['brand'] = $brand ? $brand->id : null;
                        $item['tax_rate'] = $tax_details ? $tax_details->id : null;
                        $item['subcategory_id'] = $prsubcat ? $prsubcat->id : null;

                        if ($product = $this->products_model->getProductByCode($item['code'])) {
                            if ($product->type == 'standard') {
                                if ($item['variants']) {
                                    $vs = explode('|', $item['variants']);
                                    foreach ($vs as $v) {
                                        if (!empty(trim($v))) {
                                            $variants[] = ['product_id' => $product->id, 'name' => trim($v)];
                                        }
                                    }
                                }
                                unset($item['variants']);
                                if ($this->products_model->updateProduct($product->id, $item, null, null, null, null, $variants)) {
                                    $updated++;
                                }
                            }
                            $item = false;
                        }
                    } else {
                        $this->session->set_flashdata('error', lang('check_category_code') . ' (' . $item['category_code'] . '). ' . lang('category_code_x_exist') . ' ' . lang('line_no') . ' ' . ($key + 1));
                        admin_redirect('products/import_csv');
                    }

                    if ($item) {
                        $items[] = $item;
                    }
                }
            }

            // $this->sma->print_arrays($items);
        }

        if ($this->form_validation->run() == true && !empty($items)) {
            if ($this->products_model->add_products($items)) {
                $updated = $updated ? '<p>' . sprintf(lang('products_updated'), $updated) . '</p>' : '';
                $this->session->set_flashdata('message', sprintf(lang('products_added'), count($items)) . $updated);
                admin_redirect('products');
            }
        } else {
            if (isset($items) && empty($items)) {
                if ($updated) {
                    $this->session->set_flashdata('message', sprintf(lang('products_updated'), $updated));
                    admin_redirect('products');
                } else {
                    $this->session->set_flashdata('warning', lang('csv_issue'));
                }
                admin_redirect('products/import_csv');
            }

            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['userfile'] = [
                'name' => 'userfile',
                'id' => 'userfile',
                'type' => 'text',
                'value' => $this->form_validation->set_value('userfile'),
            ];

            $bc = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('products'), 'page' => lang('products')], ['link' => '#', 'page' => lang('import_products_by_csv')]];
            $meta = ['page_title' => lang('import_products_by_csv'), 'bc' => $bc];
            $this->page_construct('products/import_csv', $meta, $this->data);
        }
    }

    public function hidden($warehouse_id = null)
    {
        $this->sma->checkPermissions();

        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        if ($this->Owner || $this->Admin || !$this->session->userdata('warehouse_id')) {
            $this->data['warehouses'] = $this->site->getAllWarehouses();
            $this->data['warehouse_id'] = $warehouse_id;
            $this->data['warehouse'] = $warehouse_id ? $this->site->getWarehouseByID($warehouse_id) : null;
        } else {
            $this->data['warehouses'] = null;
            $this->data['warehouse_id'] = $this->session->userdata('warehouse_id');
            $this->data['warehouse'] = $this->session->userdata('warehouse_id') ? $this->site->getWarehouseByID($this->session->userdata('warehouse_id')) : null;
        }

        $this->data['supplier'] = $this->input->get('supplier') ? $this->site->getCompanyByID($this->input->get('supplier')) : null;
        $bc = [['link' => base_url(), 'page' => lang('home')], ['link' => '#', 'page' => lang('products')]];
        $meta = ['page_title' => lang('products'), 'bc' => $bc];
        $this->page_construct('products/hidden_products', $meta, $this->data);
    }

    public function draft($warehouse_id = null)
    {
        $this->sma->checkPermissions();

        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        if ($this->Owner || $this->Admin || !$this->session->userdata('warehouse_id')) {
            $this->data['warehouses'] = $this->site->getAllWarehouses();
            $this->data['warehouse_id'] = $warehouse_id;
            $this->data['warehouse'] = $warehouse_id ? $this->site->getWarehouseByID($warehouse_id) : null;
        } else {
            $this->data['warehouses'] = null;
            $this->data['warehouse_id'] = $this->session->userdata('warehouse_id');
            $this->data['warehouse'] = $this->session->userdata('warehouse_id') ? $this->site->getWarehouseByID($this->session->userdata('warehouse_id')) : null;
        }

        $this->data['supplier'] = $this->input->get('supplier') ? $this->site->getCompanyByID($this->input->get('supplier')) : null;
        $bc = [['link' => base_url(), 'page' => lang('home')], ['link' => '#', 'page' => lang('products')]];
        $meta = ['page_title' => lang('products'), 'bc' => $bc];
        $this->page_construct('products/draft_products', $meta, $this->data);
    }

    public function shop($warehouse_id = null)
    {
        $this->sma->checkPermissions();

        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        if ($this->Owner || $this->Admin || !$this->session->userdata('warehouse_id')) {
            $this->data['warehouses'] = $this->site->getAllWarehouses();
            $this->data['warehouse_id'] = $warehouse_id;
            $this->data['warehouse'] = $warehouse_id ? $this->site->getWarehouseByID($warehouse_id) : null;
        } else {
            $this->data['warehouses'] = null;
            $this->data['warehouse_id'] = $this->session->userdata('warehouse_id');
            $this->data['warehouse'] = $this->session->userdata('warehouse_id') ? $this->site->getWarehouseByID($this->session->userdata('warehouse_id')) : null;
        }

        $this->data['supplier'] = $this->input->get('supplier') ? $this->site->getCompanyByID($this->input->get('supplier')) : null;
        $bc = [['link' => base_url(), 'page' => lang('home')], ['link' => '#', 'page' => lang('products')]];
        $meta = ['page_title' => lang('products'), 'bc' => $bc];
        $this->page_construct('products/shop_products', $meta, $this->data);
    }

    public function index($warehouse_id = null)
    {
        $this->sma->checkPermissions();

        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        if ($this->Owner || $this->Admin || !$this->session->userdata('warehouse_id')) {
            $this->data['warehouses'] = $this->site->getAllWarehouses();
            $this->data['warehouse_id'] = $warehouse_id;
            $this->data['warehouse'] = $warehouse_id ? $this->site->getWarehouseByID($warehouse_id) : null;
        } else {
            $this->data['warehouses'] = null;
            $this->data['warehouse_id'] = $this->session->userdata('warehouse_id');
            $this->data['warehouse'] = $this->session->userdata('warehouse_id') ? $this->site->getWarehouseByID($this->session->userdata('warehouse_id')) : null;
        }

        $this->data['supplier'] = $this->input->get('supplier') ? $this->site->getCompanyByID($this->input->get('supplier')) : null;
        $bc = [['link' => base_url(), 'page' => lang('home')], ['link' => '#', 'page' => lang('products')]];
        $meta = ['page_title' => lang('products'), 'bc' => $bc];
        $this->page_construct('products/index', $meta, $this->data);
    }

    /* --------------------------------------------------------------------------------------------- */

    public function modal_view($id = null)
    {
        $this->sma->checkPermissions('index', true);

        $pr_details = $this->site->getProductByID($id);
        if (!$id || !$pr_details) {
            $this->session->set_flashdata('error', lang('prduct_not_found'));
            $this->sma->md();
        }
        $this->data['barcode'] = "<img src='" . admin_url('products/gen_barcode/' . $pr_details->code . '/' . $pr_details->barcode_symbology . '/40/0') . "' alt='" . $pr_details->code . "' class='pull-left' />";
        if ($pr_details->type == 'combo') {
            $this->data['combo_items'] = $this->products_model->getProductComboItems($id);
        }
        $this->data['product'] = $pr_details;
        $this->data['unit'] = $this->site->getUnitByID($pr_details->unit);
        $this->data['brand'] = $this->site->getBrandByID($pr_details->brand);
        $this->data['images'] = $this->products_model->getProductPhotos($id);
        $this->data['category'] = $this->site->getCategoryByID($pr_details->category_id);
        $this->data['subcategory'] = $pr_details->subcategory_id ? $this->site->getCategoryByID($pr_details->subcategory_id) : null;
        $this->data['tax_rate'] = $pr_details->tax_rate ? $this->site->getTaxRateByID($pr_details->tax_rate) : null;
        $this->data['warehouses'] = $this->products_model->getAllWarehousesWithPQ($id);
        $this->data['options'] = $this->products_model->getProductOptionsWithWH($id);
        $this->data['variants'] = $this->products_model->getProductOptions($id);

        $this->load->view($this->theme . 'products/modal_view', $this->data);
    }

    public function pdf($id = null, $view = null)
    {
        $this->sma->checkPermissions('index');

        $pr_details = $this->products_model->getProductByID($id);
        if (!$id || !$pr_details) {
            $this->session->set_flashdata('error', lang('prduct_not_found'));
            redirect($_SERVER['HTTP_REFERER']);
        }
        $this->data['barcode'] = "<img src='" . admin_url('products/gen_barcode/' . $pr_details->code . '/' . $pr_details->barcode_symbology . '/40/0') . "' alt='" . $pr_details->code . "' class='pull-left' />";
        if ($pr_details->type == 'combo') {
            $this->data['combo_items'] = $this->products_model->getProductComboItems($id);
        }
        $this->data['product'] = $pr_details;
        $this->data['unit'] = $this->site->getUnitByID($pr_details->unit);
        $this->data['brand'] = $this->site->getBrandByID($pr_details->brand);
        $this->data['images'] = $this->products_model->getProductPhotos($id);
        $this->data['category'] = $this->site->getCategoryByID($pr_details->category_id);
        $this->data['subcategory'] = $pr_details->subcategory_id ? $this->site->getCategoryByID($pr_details->subcategory_id) : null;
        $this->data['tax_rate'] = $pr_details->tax_rate ? $this->site->getTaxRateByID($pr_details->tax_rate) : null;
        $this->data['popup_attributes'] = $this->popup_attributes;
        $this->data['warehouses'] = $this->products_model->getAllWarehousesWithPQ($id);
        $this->data['options'] = $this->products_model->getProductOptionsWithWH($id);
        $this->data['variants'] = $this->products_model->getProductOptions($id);

        $name = $pr_details->code . '_' . str_replace('/', '_', $pr_details->name) . '.pdf';
        if ($view) {
            $this->load->view($this->theme . 'products/pdf', $this->data);
        } else {
            $html = $this->load->view($this->theme . 'products/pdf', $this->data, true);
            if (!$this->Settings->barcode_img) {
                $html = preg_replace("'\<\?xml(.*)\?\>'", '', $html);
            }
            $this->sma->generate_pdf($html, $name);
        }
    }

    public function print_barcodes_new()
    {
        if ($this->input->get('use') == 'command') {
            $filePath = base_url('assets/new_label.zpl');
            echo $filePath = FCPATH . 'assets' . DIRECTORY_SEPARATOR . 'new_label.zpl';
            $productName = 'sulfad';
            $avzCode = '123456';
            $price = 5;
            $zplCode = "^XA\n"
                . "^FO20,20^A0N,15,15^FD{$productName}^FS\n" // Product name at top with a smaller font
                . "^FO5,30\n"                               // Position barcode
                . "^BY2,2,50\n"                             // Bar width, space between bars, height
                . "^BCN,50,Y,N,N\n"                         // Code 128 Barcode, 50 dots tall, HRI off
                . "^FD{$avzCode}^FS\n"                   // GTIN Number (dynamic)
                . "^FO20,120\n"                             // Position price below the barcode
                . "^A0N,20,20\n"                            // Font size for price text
                . "^FD{$price}^FS\n"                        // Price (dynamic)
                . "^XZ";
            file_put_contents($filePath, $zplCode);
            // Check if the file actually exists before attempting to copy
            if (!file_exists($filePath)) {
                die("Error: File not found at path - $filePath");
            }
            // Network path of the Zebra printer
            $printerPath = "\\\\192.168.30.113\\Zebra_S4M"; // Double backslashes are necessary in PHP strings

            // Build the copy command
            $command = "copy /B \"$filePath\" \"$printerPath\" 2>&1";

            // Execute the cp command and capture output
            $output = shell_exec($command);

            // Display the output or an error message if there's an issue
            if ($output === null || strpos($output, 'No such file or directory') !== false) {
                echo "Error: " . $output;
            } else {
                echo "Command output: $output";
            }
        } else {

            $zplFilePath = base_url('assets/new_label.zpl');
            $host = "192.168.30.113";
            $port = 6101;              // Use port 9100 (or 6101 if specified for Zebra printers)
            // Send the ZPL data to the printer
            $result = $this->sendZplToPrinter($host, $port, $zplFilePath);
            echo $result;
        }


    }

    function sendZplToPrinter($host, $port, $zplFilePath)
    {
        $zpl = file_get_contents($zplFilePath);
        $fp = @fsockopen($host, $port, $errno, $errstr, 30);
        if (!$fp) {
            return "ERROR: $errstr ($errno)";
        } else {
            fwrite($fp, $zpl);
            fclose($fp);
            return 'ZPL data sent successfully';
        }
    }

    private function generate_zpl($productName, $quantity)
    {
        $zpl = '';
        for ($i = 0; $i < $quantity; $i++) {
            $zpl .= "^XA^FO100,100^BY3^BCN,100,Y,N,N^FD{$productName}^FS^XZ";
        }
        return $zpl;
    }

    public function getNgrokUrl() {
        // Try to fetch the ngrok tunnels via the ngrok API
        $ch = curl_init('http://localhost:4040/api/tunnels');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
        $response = curl_exec($ch);
      //  var_dump($response);exit;
        if ($response === false) {
            // If the request failed, ngrok is probably not running
           // echo "Ngrok is not running, starting ngrok...\n";
            exec('ngrok http 5000 > /dev/null &');  // Start ngrok in the background
            sleep(2);  // Give ngrok some time to initialize
            return getNgrokUrl();  // Recursive call to try getting the URL again
        }
    
        $data = json_decode($response, true);
    
        if (isset($data['tunnels']) && !empty($data['tunnels'])) {
            foreach ($data['tunnels'] as $tunnel) {
                if ($tunnel['proto'] === 'http' || $tunnel['proto'] === 'https') {  // Find the HTTP tunnel
                    return $tunnel['public_url'];
                }
            }
        }
    
        return null;
    }
    public function print_barcodes($product_id = null)
    {
        $this->sma->checkPermissions('barcode', true);
        $s = isset($_POST['product']) ? sizeof($_POST['product']) : 0;
        if ($s > 0) {
            $purchase_id =  $this->input->post('purchase_id') ;
            $transfer_id =  $this->input->post('transfer_id') ;
            // print barcodes
            $s = isset($_POST['product']) ? sizeof($_POST['product']) : 0;
            if ($s < 1) {
                $this->session->set_flashdata('error', lang('no_product_selected'));
                admin_redirect('products/print_barcodes');
            }

            if(isset($_POST['pharmacy_id']) && !empty($_POST['pharmacy_id'])) {
                $location_id = $_POST['pharmacy_id'];
                $location_details = $this->site->getWarehouseByID($location_id);

                if($location_details->printer_location && $location_details->printer_location != NULL){
                    $printer_location = $location_details->printer_location;
                    $print_method     = $location_details->print_method;
                    $printer_name     = $location_details->printer_name;
                }else{
                    $this->session->set_flashdata('error', lang('This location does not support printing'));
                    admin_redirect('products/print_barcodes');
                }
            }else{
                $this->session->set_flashdata('error', lang('No Print Location Selected'));
                admin_redirect('products/print_barcodes');
            }
            
            // $ngrokUrl = $this->getNgrokUrl();
            // if ($ngrokUrl) {
            //         echo "Ngrok URL: " . $ngrokUrl . "\n";
            //     } else {
            //         $ngrokUrl = '';
            //         echo "No HTTP tunnel found.\n";
            //     }
  
            $zplCode = '';
            for ($m = 0; $m < $s; $m++) {
                $item_id = $_POST['product'][$m];
                //get item details from purchase
                //$itemDetail = $this->purchases_model->getItemByID($item_id);
                $itemDetail = $this->products_model->getProductsBarcodeItems('','','',$item_id);
                $itemDetail = $itemDetail[0];
                $pid = $itemDetail->product_id;
                $quantity = abs($_POST['quantity'][$item_id]);

                //$product = $this->products_model->getProductWithCategory($pid);
                
                if($_POST['purchase_id']){
                    $product = $this->products_model->getProductWithPrice($purchase_id, 'purchase', $pid);
                }else if($_POST['transfer_id']){
                    $product = $this->products_model->getProductWithPrice($transfer_id, 'transfer_out', $pid);
                }else{
                    $product = $this->products_model->getProductWithCategory($pid);
                }
                $product->price = $this->input->post('check_promo') ? ($product->promotion ? $product->promo_price : $product->price) : $product->price;
                $pr_item_tax = 0;

                $item_tax_rate = $product->tax_rate;
                if (isset($item_tax_rate) && $item_tax_rate > 1) {
                    $tax_details = $this->site->getTaxRateByID($item_tax_rate);
                    $ctax = $this->site->calculateTax($product, $tax_details, $product->price);
                    $item_tax = $this->sma->formatDecimal($ctax['amount']);
                    $tax = $ctax['tax'];

                    $pr_item_tax = $this->sma->formatDecimal(($product->price * ($tax_details->rate / 100)), 4);
                }

                $productPrice = $product->price + $pr_item_tax;
                $productName = $product->name;//substr($product->name, 0, 80); 
                $avzCode = $itemDetail->avz_item_code ;//$this->products_model->getProductAvzCode($pid, $purchase_id);

                $maxLength = 30;
                $line1 = substr($productName, 0, $maxLength);
                $line2 = strlen($productName) > $maxLength ? substr($productName, $maxLength) : '';

                if($printer_name == 'zebra'){
                    // Generate the ZPL code
                    for ($i = 1; $i <= ceil($quantity); $i++) {

                        if(isset($_POST['print'])){
                            $zplCode .= "^XA\n"; 
                            $filePath = FCPATH . 'assets' . DIRECTORY_SEPARATOR . 'new_label.zpl';
                            $zplCode .= "^FO20,20^A0N,15,15^FD{$line1}^FS\n";

                            // Add second line if it exists
                            if ($line2) {
                                $zplCode .= "^FO20,40^A0N,15,15^FD{$line2}^FS\n";
                            }
                            $zplCode .=
                                "^FO20,60\n"                               // Position barcode
                                . "^BY2,2,50\n"                             // Bar width, space between bars, height
                                . "^BCN,50,Y,N,N\n"                         // Code 128 Barcode, 50 dots tall, HRI off
                                . "^FD{$avzCode}^FS\n"                   // GTIN Number (dynamic)
                                . "^FO20,135\n"                             // Position price below the barcode
                                . "^A0N,20,20\n"                            // Font size for price text
                                . "^FDitem#{$product->item_code}^FS\n"               // Item Number (dynamic)
                                . "^FO200,135\n"                            // Position price on the right side
                                . "^A0N,20,20\n"                            // Font size for price text
                                . "^FDSR{$productPrice}^FS\n";  // Price (formatted)
                                //. "^FD{$this->sma->formatMoney($productPrice)}^FS\n";
                                
                            
                            $zplCode .= "^XZ\n";
                        }
                    }
                }else if($printer_name == 'tsc'){
                    // Generate the TSPL code
                    for ($i = 1; $i <= ceil($quantity); $i++) {
                        if (isset($_POST['print'])) {
                            $zplCode = "";
                            
                            // Initialize the printer
                            $zplCode .= "SIZE 50 mm, 30 mm\n";  // Adjust size as per your label
                            $zplCode .= "GAP 3 mm, 0 mm\n";    // Set the gap between labels
                            $zplCode .= "CLS\n";               // Clear the buffer
                            
                            // Print the first text line
                            $zplCode .= "TEXT 20, 20, \"3\", 0, 1, 1, \"{$line1}\"\n";

                            // Add second line if it exists
                            if ($line2) {
                                $zplCode .= "TEXT 20, 40, \"3\", 0, 1, 1, \"{$line2}\"\n";
                            }

                            // Print the barcode
                            $zplCode .= "BARCODE 20, 60, \"128\", 50, 1, 0, 2, 2, \"{$avzCode}\"\n";

                            // Print item number
                            $zplCode .= "TEXT 20, 135, \"3\", 0, 1, 1, \"item#{$product->item_code}\"\n";

                            // Print price
                            $zplCode .= "TEXT 200, 135, \"3\", 0, 1, 1, \"SR{$productPrice}\"\n";

                            // Print command
                            $zplCode .= "PRINT 1\n";
                        }
                    }

                }else if($printer_name == 'hptest'){
                    if (isset($_POST['print'])) {
                        $zplCode = "This is test print job";
                    }
                }
            }
           
            // echo "<pre>" . htmlspecialchars($zplCode) . "</pre>";
            // exit;
            

            if(isset($_POST['print']) && $printer_location != 'script'){
                /*$url = $printer_location;
                $ch = curl_init($url);

            
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/octet-stream'));
                curl_setopt($ch, CURLOPT_POSTFIELDS, $zplCode);

                
                $response = curl_exec($ch);
                $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);

            
                curl_close($ch);*/

                $this->products_model->addPrintJob($zplCode, $location_details);
            }else if($printer_location == 'script'){
                $this->products_model->addPrintJob($zplCode, $location_details);
            }

          
            if ($http_status == 200) {
                //echo "Print request successful: " . $response;
            } else {
                //echo "Print request failed with status $http_status: " . $response;
            }
            $this->data['items'] = false;
            $bc = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('products'), 'page' => lang('products')], ['link' => '#', 'page' => lang('print_barcodes')]];
            $meta = ['page_title' => lang('print_barcodes'), 'bc' => $bc];
            $this->page_construct('products/print_barcodes', $meta, $this->data);
        } else {
            // if ($this->input->get('purchase') || $this->input->get('transfer')) {
            //     if ($this->input->get('purchase')) {
            //         $purchase_id = $this->input->get('purchase', true);
            //         $item_code = $this->input->get('item_code', true);
            //         $items = $this->products_model->getPurchaseItems($purchase_id, $item_code);
            //     } elseif ($this->input->get('transfer')) {
            //         $transfer_id = $this->input->get('transfer', true);
            //         $items = $this->products_model->getTransferItems($transfer_id);
            //     }

            if ( $this->input->get('item_code') || $this->input->get('purchase') || $this->input->get('transfer') ) {
                $purchase_id = $this->input->get('purchase', true);
                $transfer_id = $this->input->get('transfer', true);
                $item_code = $this->input->get('item_code', true);
                $warehouse_id = $this->input->get('pharmacy', true);
                if($purchase_id){
                    $items = $this->products_model->getProductsBarcodeItems($purchase_id, $item_code, $warehouse_id);
                }else if($transfer_id){
                    $items = $this->products_model->getProductsBarcodeItemsForTransfer($transfer_id, $item_code, $warehouse_id);
                }else {
                    $items = $this->products_model->getProductsBarcodeItems($purchase_id, $item_code, $warehouse_id);
                }
                
                if ($items) {
                    foreach ($items as $item) {
                        if ($row = $this->products_model->getProductByID($item->product_id)) {
                            $selected_variants = false;
                            if ($variants = $this->products_model->getProductOptions($row->id)) {
                                foreach ($variants as $variant) {
                                    $selected_variants[$variant->id] = isset($pr[$row->id]['selected_variants'][$variant->id]) && !empty($pr[$row->id]['selected_variants'][$variant->id]) ? 1 : ($variant->id == $item->option_id ? 1 : 0);
                                }
                            }
                            $pr[] = ['id' => $item->id, 'label' => $row->name . ' (' . $row->code . ')',
                                     'code' => $row->code, 
                                     'name' => $row->name, 
                                     'price' => $row->price, 
                                     'qty' => $item->quantity, 
                                     'avz_item_code' => $item->avz_item_code,
                                     'batchno' => $item->batchno,
                                    'expiry' => $item->expiry];
                        }
                    }
                    $this->data['message'] = lang('products_added_to_list');
                }
            }


            $this->data['purchase_id'] = $this->input->get('purchase', true) ;
            $this->data['transfer_id'] = $this->input->get('transfer', true) ;
            $this->data['item_code'] = $this->input->get('item_code', true) ;
            $this->data['pharmacy'] = $this->input->get('pharmacy', true) ;
            $this->data['warehouses'] = $this->site->getAllWarehouses();
            $this->data['items'] = isset($pr) ? json_encode($pr) : false;
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $bc = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('products'), 'page' => lang('products')], ['link' => '#', 'page' => lang('print_barcodes')]];
            $meta = ['page_title' => lang('print_barcodes'), 'bc' => $bc];
            $this->page_construct('products/print_barcodes', $meta, $this->data);
        }
    }

    private function process_csv_data($firstHeader, $secondHeader, $csvData, $type)
    {

        // Assume the first column of CSV contains the product ID
        $csvProductIds = array_column($csvData, 0);
        // Check product IDs from $_POST['val'] against CSV data
        foreach ($_POST['val'] as $id) {
            $product = $this->products_model->getProductByID($id);

            $productId = $product->id;
            $brand = $this->products_model->getBrandByID($product->brand);
            $newData = [
                $productId,
                $product->name,
                strip_tags($product->product_details),
                base_url() . "product/" . $product->slug,
                base_url() . 'assets/upload/' . $product->image,
                $type,
                // 'in stock',
                $product->price,
                $brand->name,
            ];

            // Check if product ID exists in CSV data
            $found = false;
            foreach ($csvData as $key => $row) {
                if ($row[0] == $productId) {
                    // Replace the row if the product ID exists
                    $csvData[$key] = $newData;
                    $found = true;
                    break;
                }
            }

            // Add a new row if the product ID does not exist
            if (!$found) {
                $csvData[] = $newData;
            }
        }

        // Write the updated CSV data back to the file
        array_unshift($csvData, $secondHeader); // Add the header back to the data
        array_unshift($csvData, $firstHeader); // Add the header back to the data
        $file = fopen(FCPATH . 'snapchat-product-feed.csv', 'w');
        foreach ($csvData as $row) {
            fputcsv($file, $row);
        }
        fclose($file);
    }

    public function getCSVData()
    {
        // Path to the CSV file
        $filePath = FCPATH . 'snapchat-product-feed.csv';

        // Check if the file exists
        if (!file_exists($filePath)) {
            echo "File not found.";
            return;
        }

        // Open the CSV file
        $file = fopen($filePath, 'r');
        if (!$file) {
            echo "Unable to open the file.";
            return;
        }

        // Read the first header row
        $firstHeader = fgetcsv($file);
        if ($firstHeader === FALSE) {
            echo "Unable to read the first header.";
            fclose($file);
            return;
        }

        // Read the second header row
        $secondHeader = fgetcsv($file);
        if ($secondHeader === FALSE) {
            echo "Unable to read the second header.";
            fclose($file);
            return;
        }

        // Read and process the CSV data
        $csvData = [];
        while (($row = fgetcsv($file)) !== FALSE) {
            $csvData[] = $row;
        }
        fclose($file);

        // Return the headers and the data
        return [
            'firstHeader' => $firstHeader,
            'secondHeader' => $secondHeader,
            'csvData' => $csvData,
        ];
    }

    public function product_actions($wh = null)
    {
        if (!$this->Owner && !$this->GP['bulk_actions']) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect($_SERVER['HTTP_REFERER']);
        }

        $this->form_validation->set_rules('form_action', lang('form_action'), 'required');

        if ($this->form_validation->run() == true) {
            if (!empty($_POST['val'])) {
                if ($this->input->post('form_action') == 'sync_quantity') {
                    foreach ($_POST['val'] as $id) {
                        $this->site->syncQuantity(null, null, null, $id);
                    }
                    $this->session->set_flashdata('message', $this->lang->line('products_quantity_sync'));
                    redirect($_SERVER['HTTP_REFERER']);
                } elseif ($this->input->post('form_action') == 'set_avg_cost') {
                    foreach ($_POST['val'] as $id) {
                        $this->products_model->setAvgCost($id);
                    }
                    $this->session->set_flashdata('message', $this->lang->line('products_avg_cost_set'));
                    redirect($_SERVER['HTTP_REFERER']);
                } elseif ($this->input->post('form_action') == 'delete') {
                    $this->sma->checkPermissions('delete');
                    foreach ($_POST['val'] as $id) {
                        $this->products_model->deleteProduct($id);
                    }
                    $this->session->set_flashdata('message', $this->lang->line('products_deleted'));
                    redirect($_SERVER['HTTP_REFERER']);
                } elseif ($this->input->post('form_action') == 'add_to_catalog') {
                    $response = $this->getCSVData();
                    $csvData = $response['csvData'];
                    $firstHeader = $response['firstHeader'];
                    $secondHeader = $response['secondHeader'];
                    $type = 'in stock';
                    $this->process_csv_data($firstHeader, $secondHeader, $csvData, $type);
                    $this->session->set_flashdata('message', $this->lang->line('Added in catalog'));
                    redirect($_SERVER['HTTP_REFERER']);
                } elseif ($this->input->post('form_action') == 'out_of_stock') {
                    $response = $this->getCSVData();
                    $csvData = $response['csvData'];
                    $firstHeader = $response['firstHeader'];
                    $secondHeader = $response['secondHeader'];
                    $type = 'in stock';
                    $this->process_csv_data($firstHeader, $secondHeader, $csvData, $type);
                    $this->session->set_flashdata('message', $this->lang->line('Added as out of stock'));
                    redirect($_SERVER['HTTP_REFERER']);
                } elseif ($this->input->post('form_action') == 'deactivated') {
                    $response = $this->getCSVData();
                    $csvData = $response['csvData'];
                    $firstHeader = $response['firstHeader'];
                    $secondHeader = $response['secondHeader'];
                    $type = 'in stock';
                    $this->process_csv_data($firstHeader, $secondHeader, $csvData, $type);
                    $this->session->set_flashdata('message', $this->lang->line('Added as deactivated'));
                    redirect($_SERVER['HTTP_REFERER']);
                } elseif ($this->input->post('form_action') == 'labels') {
                    foreach ($_POST['val'] as $id) {
                        $row = $this->products_model->getProductByID($id);
                        $selected_variants = false;
                        if ($variants = $this->products_model->getProductOptions($row->id)) {
                            foreach ($variants as $variant) {
                                $selected_variants[$variant->id] = $variant->quantity > 0 ? 1 : 0;
                            }
                        }
                        $pr[$row->id] = ['id' => $row->id, 'label' => $row->name . ' (' . $row->code . ')', 'code' => $row->code, 'name' => $row->name, 'price' => $row->price, 'qty' => $row->quantity, 'variants' => $variants, 'selected_variants' => $selected_variants];
                    }

                    $this->data['items'] = isset($pr) ? json_encode($pr) : false;
                    $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
                    $bc = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('products'), 'page' => lang('products')], ['link' => '#', 'page' => lang('print_barcodes')]];
                    $meta = ['page_title' => lang('print_barcodes'), 'bc' => $bc];
                    $this->page_construct('products/print_barcodes', $meta, $this->data);
                } elseif ($this->input->post('form_action') == 'export_excel') {
                    $this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle('Products');
                    $this->excel->getActiveSheet()->SetCellValue('A1', lang('name'));
                    $this->excel->getActiveSheet()->SetCellValue('B1', lang('code'));
                    $this->excel->getActiveSheet()->SetCellValue('C1', lang('barcode_symbology'));
                    $this->excel->getActiveSheet()->SetCellValue('D1', lang('brand'));
                    $this->excel->getActiveSheet()->SetCellValue('E1', lang('category_code'));
                    $this->excel->getActiveSheet()->SetCellValue('F1', lang('unit_code'));
                    $this->excel->getActiveSheet()->SetCellValue('G1', lang('sale') . ' ' . lang('unit_code'));
                    $this->excel->getActiveSheet()->SetCellValue('H1', lang('purchase') . ' ' . lang('unit_code'));
                    $this->excel->getActiveSheet()->SetCellValue('I1', lang('cost'));
                    $this->excel->getActiveSheet()->SetCellValue('J1', lang('price'));
                    $this->excel->getActiveSheet()->SetCellValue('K1', lang('alert_quantity'));
                    $this->excel->getActiveSheet()->SetCellValue('L1', lang('tax_rate'));
                    $this->excel->getActiveSheet()->SetCellValue('M1', lang('tax_method'));
                    $this->excel->getActiveSheet()->SetCellValue('N1', lang('image'));
                    $this->excel->getActiveSheet()->SetCellValue('O1', lang('subcategory_code'));
                    $this->excel->getActiveSheet()->SetCellValue('P1', lang('product_variants'));
                    $this->excel->getActiveSheet()->SetCellValue('Q1', lang('pcf1'));
                    $this->excel->getActiveSheet()->SetCellValue('R1', lang('pcf2'));
                    $this->excel->getActiveSheet()->SetCellValue('S1', lang('pcf3'));
                    $this->excel->getActiveSheet()->SetCellValue('T1', lang('pcf4'));
                    $this->excel->getActiveSheet()->SetCellValue('U1', lang('pcf5'));
                    $this->excel->getActiveSheet()->SetCellValue('V1', lang('pcf6'));
                    $this->excel->getActiveSheet()->SetCellValue('W1', lang('hsn_code'));
                    $this->excel->getActiveSheet()->SetCellValue('X1', lang('second_name'));
                    $this->excel->getActiveSheet()->SetCellValue('Y1', lang('details'));
                    $this->excel->getActiveSheet()->SetCellValue('Z1', lang('product_details'));
                    $this->excel->getActiveSheet()->SetCellValue('AA1', lang('supplier1_name'));
                    $this->excel->getActiveSheet()->SetCellValue('AB1', lang('supplier1_part_no'));
                    $this->excel->getActiveSheet()->SetCellValue('AC1', lang('supplier1_price'));
                    $this->excel->getActiveSheet()->SetCellValue('AD1', lang('supplier2_name'));
                    $this->excel->getActiveSheet()->SetCellValue('AE1', lang('supplier2_part_no'));
                    $this->excel->getActiveSheet()->SetCellValue('AF1', lang('supplier2_price'));
                    $this->excel->getActiveSheet()->SetCellValue('AG1', lang('supplier3_name'));
                    $this->excel->getActiveSheet()->SetCellValue('AH1', lang('supplier3_part_no'));
                    $this->excel->getActiveSheet()->SetCellValue('AI1', lang('supplier3_price'));
                    $this->excel->getActiveSheet()->SetCellValue('AJ1', lang('supplier4_name'));
                    $this->excel->getActiveSheet()->SetCellValue('AK1', lang('supplier4_part_no'));
                    $this->excel->getActiveSheet()->SetCellValue('AL1', lang('supplier4_price'));
                    $this->excel->getActiveSheet()->SetCellValue('AM1', lang('supplier5_name'));
                    $this->excel->getActiveSheet()->SetCellValue('AN1', lang('supplier5_part_no'));
                    $this->excel->getActiveSheet()->SetCellValue('AO1', lang('supplier5_price'));
                    $this->excel->getActiveSheet()->SetCellValue('AP1', lang('quantity'));

                    $row = 2;
                    foreach ($_POST['val'] as $id) {
                        $product = $this->products_model->getProductDetail($id);
                        $brand = $this->site->getBrandByID($product->brand);
                        $base_unit = $sale_unit = $purchase_unit = '';
                        if ($units = $this->site->getUnitsByBUID($product->unit)) {
                            foreach ($units as $u) {
                                if ($u->id == $product->unit) {
                                    $base_unit = $u->code;
                                }
                                if ($u->id == $product->sale_unit) {
                                    $sale_unit = $u->code;
                                }
                                if ($u->id == $product->purchase_unit) {
                                    $purchase_unit = $u->code;
                                }
                            }
                        }
                        $variants = $this->products_model->getProductOptions($id);
                        $product_variants = '';
                        if ($variants) {
                            $i = 1;
                            $v = count($variants);
                            foreach ($variants as $variant) {
                                $product_variants .= trim($variant->name) . ($i != $v ? '|' : '');
                                $i++;
                            }
                        }
                        $quantity = $product->quantity;
                        if ($wh) {
                            if ($wh_qty = $this->products_model->getProductQuantity($id, $wh)) {
                                $quantity = $wh_qty['quantity'];
                            } else {
                                $quantity = 0;
                            }
                        }

                        $supplier1 = $product->supplier1 ? $this->site->getCompanyByID($product->supplier1) : null;
                        $supplier2 = $product->supplier2 ? $this->site->getCompanyByID($product->supplier2) : null;
                        $supplier3 = $product->supplier3 ? $this->site->getCompanyByID($product->supplier3) : null;
                        $supplier4 = $product->supplier4 ? $this->site->getCompanyByID($product->supplier4) : null;
                        $supplier5 = $product->supplier5 ? $this->site->getCompanyByID($product->supplier5) : null;

                        $this->excel->getActiveSheet()->SetCellValue('A' . $row, $product->name);
                        $this->excel->getActiveSheet()->SetCellValue('B' . $row, $product->code);
                        $this->excel->getActiveSheet()->SetCellValue('C' . $row, $product->barcode_symbology);
                        $this->excel->getActiveSheet()->SetCellValue('D' . $row, ($brand ? $brand->name : ''));
                        $this->excel->getActiveSheet()->SetCellValue('E' . $row, $product->category_code);
                        $this->excel->getActiveSheet()->SetCellValue('F' . $row, $base_unit);
                        $this->excel->getActiveSheet()->SetCellValue('G' . $row, $sale_unit);
                        $this->excel->getActiveSheet()->SetCellValue('H' . $row, $purchase_unit);
                        if ($this->Owner || $this->Admin || $this->session->userdata('show_cost')) {
                            $this->excel->getActiveSheet()->SetCellValue('I' . $row, $product->cost);
                        }
                        if ($this->Owner || $this->Admin || $this->session->userdata('show_price')) {
                            $this->excel->getActiveSheet()->SetCellValue('J' . $row, $product->price);
                        }
                        $this->excel->getActiveSheet()->SetCellValue('K' . $row, $product->alert_quantity);
                        $this->excel->getActiveSheet()->SetCellValue('L' . $row, $product->tax_rate_name);
                        $this->excel->getActiveSheet()->SetCellValue('M' . $row, $product->tax_method ? lang('exclusive') : lang('inclusive'));
                        $this->excel->getActiveSheet()->SetCellValue('N' . $row, $product->image);
                        $this->excel->getActiveSheet()->SetCellValue('O' . $row, $product->subcategory_code);
                        $this->excel->getActiveSheet()->SetCellValue('P' . $row, $product_variants);
                        $this->excel->getActiveSheet()->SetCellValue('Q' . $row, $product->cf1);
                        $this->excel->getActiveSheet()->SetCellValue('R' . $row, $product->cf2);
                        $this->excel->getActiveSheet()->SetCellValue('S' . $row, $product->cf3);
                        $this->excel->getActiveSheet()->SetCellValue('T' . $row, $product->cf4);
                        $this->excel->getActiveSheet()->SetCellValue('U' . $row, $product->cf5);
                        $this->excel->getActiveSheet()->SetCellValue('V' . $row, $product->cf6);
                        $this->excel->getActiveSheet()->SetCellValue('W' . $row, $product->hsn_code);
                        $this->excel->getActiveSheet()->SetCellValue('X' . $row, $product->second_name);
                        $this->excel->getActiveSheet()->SetCellValue('Y' . $row, $product->details);
                        $this->excel->getActiveSheet()->SetCellValue('Z' . $row, $product->product_details);
                        $this->excel->getActiveSheet()->SetCellValue('AA' . $row, $supplier1 ? $supplier1->name : '');
                        $this->excel->getActiveSheet()->SetCellValue('AB' . $row, $supplier1 ? $product->supplier1_part_no : '');
                        $this->excel->getActiveSheet()->SetCellValue('AC' . $row, $supplier1 ? $product->supplier1price : '');
                        $this->excel->getActiveSheet()->SetCellValue('AD' . $row, $supplier2 ? $supplier2->name : '');
                        $this->excel->getActiveSheet()->SetCellValue('AE' . $row, $supplier2 ? $product->supplier2_part_no : '');
                        $this->excel->getActiveSheet()->SetCellValue('AF' . $row, $supplier2 ? $product->supplier2price : '');
                        $this->excel->getActiveSheet()->SetCellValue('AG' . $row, $supplier3 ? $supplier3->name : '');
                        $this->excel->getActiveSheet()->SetCellValue('AH' . $row, $supplier3 ? $product->supplier3_part_no : '');
                        $this->excel->getActiveSheet()->SetCellValue('AI' . $row, $supplier3 ? $product->supplier3price : '');
                        $this->excel->getActiveSheet()->SetCellValue('AJ' . $row, $supplier4 ? $supplier4->name : '');
                        $this->excel->getActiveSheet()->SetCellValue('AK' . $row, $supplier4 ? $product->supplier4_part_no : '');
                        $this->excel->getActiveSheet()->SetCellValue('AL' . $row, $supplier4 ? $product->supplier4price : '');
                        $this->excel->getActiveSheet()->SetCellValue('AM' . $row, $supplier5 ? $supplier5->name : '');
                        $this->excel->getActiveSheet()->SetCellValue('AN' . $row, $supplier5 ? $product->supplier5_part_no : '');
                        $this->excel->getActiveSheet()->SetCellValue('AO' . $row, $supplier5 ? $product->supplier5price : '');
                        $this->excel->getActiveSheet()->SetCellValue('AP' . $row, $quantity);
                        $row++;
                    }

                    $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(30);
                    $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(15);
                    $this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('N')->setWidth(40);
                    $this->excel->getActiveSheet()->getColumnDimension('O')->setWidth(30);
                    $this->excel->getActiveSheet()->getColumnDimension('P')->setWidth(30);
                    $this->excel->getActiveSheet()->getColumnDimension('AC')->setWidth(30);
                    $this->excel->getActiveSheet()->getColumnDimension('AD')->setWidth(40);
                    $this->excel->getDefaultStyle()->getAlignment()->setVertical('center');
                    $filename = 'products_' . date('Y_m_d_H_i_s');
                    $this->load->helper('excel');
                    create_excel($this->excel, $filename);
                }
            } else {
                $this->session->set_flashdata('error', $this->lang->line('no_product_selected'));
                redirect($_SERVER['HTTP_REFERER']);
            }
        } else {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER['HTTP_REFERER'] ?? 'admin/products');
        }
    }
    public function combo_suggestions()
    {
        $term = $this->input->get('term', true);

        if (strlen($term) < 1 || !$term) {
            die("<script type='text/javascript'>setTimeout(function(){ window.top.location.href = '" . admin_url('welcome') . "'; }, 10);</script>");
        }

        $analyzed = $this->sma->analyze_term($term);
        $sr = $analyzed['term'];
        $option_id = $analyzed['option_id'];
        $sr = addslashes($sr);

        $rows = $this->products_model->getComboSuggestions($sr);
        if ($rows) {
            foreach ($rows as $row) {
                $row->qty = 1;
                $row->discount = 100;
                $row->quantity = 1;
                $options = $this->products_model->getProductOptions($row->id);
                $row->option = $option_id;
                $row->serial = '';
                $c = sha1(uniqid(mt_rand(), true));
                $pr[] = [
                    'id' => $c,
                    'item_id' => $row->id,
                    'label' => $row->name . ' (' . $row->code . ')',
                    'row' => $row,
                    'options' => $options,
                ];
            }
            $this->sma->send_json($pr);
        } else {
            $this->sma->send_json([['id' => 0, 'label' => lang('no_match_found'), 'value' => $term]]);
        }
    }

    public function bu_suggestions()
    {
        $term = $this->input->get('term', true);

        if (strlen($term) < 1 || !$term) {
            die("<script type='text/javascript'>setTimeout(function(){ window.top.location.href = '" . admin_url('welcome') . "'; }, 10);</script>");
        }

        $analyzed = $this->sma->analyze_term($term);
        $sr = $analyzed['term'];
        $option_id = $analyzed['option_id'];
        $sr = addslashes($sr);

        $rows = $this->products_model->getBUSuggestions($sr);
        if ($rows) {
            foreach ($rows as $row) {
                $row->qty = 1;
                $row->discount = 1;
                $row->quantity = 1;
                $options = $this->products_model->getProductOptions($row->id);
                $row->option = $option_id;
                $row->serial = '';
                $c = sha1(uniqid(mt_rand(), true));
                $pr[] = [
                    'id' => $c,
                    'item_id' => $row->id,
                    'label' => $row->name . ' (' . $row->code . ')',
                    'row' => $row,
                    'options' => $options,
                ];
            }
            $this->sma->send_json($pr);
        } else {
            $this->sma->send_json([['id' => 0, 'label' => lang('no_match_found'), 'value' => $term]]);
        }
    }

    public function qa_suggestions()
    {
        $term = $this->input->get('term', true);

        if (strlen($term) < 1 || !$term) {
            die("<script type='text/javascript'>setTimeout(function(){ window.top.location.href = '" . admin_url('welcome') . "'; }, 10);</script>");
        }

        $analyzed = $this->sma->analyze_term($term);
        $sr = $analyzed['term'];
        $option_id = $analyzed['option_id'];
        $sr = addslashes($sr);

        $rows = $this->products_model->getQASuggestions($sr);
        if ($rows) {
            foreach ($rows as $row) {
                $row->qty = 1;
                $options = $this->products_model->getProductOptions($row->id);
                $row->option = $option_id;
                $row->serial = '';
                $c = sha1(uniqid(mt_rand(), true));
                $pr[] = [
                    'id' => $c,
                    'item_id' => $row->id,
                    'label' => $row->name . ' (' . $row->code . ')',
                    'row' => $row,
                    'options' => $options,
                ];
            }
            $this->sma->send_json($pr);
        } else {
            $this->sma->send_json([['id' => 0, 'label' => lang('no_match_found'), 'value' => $term]]);
        }
    }

    /* ----------------------------------------------------------------------------- */

    public function quantity_adjustments($warehouse_id = null)
    {
        $this->sma->checkPermissions('adjustments');

        if ($this->Owner || $this->Admin || !$this->session->userdata('warehouse_id')) {
            $this->data['warehouses'] = $this->site->getAllWarehouses();
            $this->data['warehouse'] = $warehouse_id ? $this->site->getWarehouseByID($warehouse_id) : null;
        } else {
            $this->data['warehouses'] = null;
            $this->data['warehouse'] = $this->session->userdata('warehouse_id') ? $this->site->getWarehouseByID($this->session->userdata('warehouse_id')) : null;
        }

        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $bc = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('products'), 'page' => lang('products')], ['link' => '#', 'page' => lang('quantity_adjustments')]];
        $meta = ['page_title' => lang('quantity_adjustments'), 'bc' => $bc];
        $this->page_construct('products/quantity_adjustments', $meta, $this->data);
    }

    public function set_rack($product_id = null, $warehouse_id = null)
    {
        $this->sma->checkPermissions('edit', true);

        $this->form_validation->set_rules('rack', lang('rack_location'), 'trim|required');

        if ($this->form_validation->run() == true) {
            $data = [
                'rack' => $this->input->post('rack'),
                'product_id' => $product_id,
                'warehouse_id' => $warehouse_id,
            ];
        } elseif ($this->input->post('set_rack')) {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect('products/' . $warehouse_id);
        }

        if ($this->form_validation->run() == true && $this->products_model->setRack($data)) {
            $this->session->set_flashdata('message', lang('rack_set'));
            admin_redirect('products/' . $warehouse_id);
        } else {
            $this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');
            $this->data['warehouse_id'] = $warehouse_id;
            $this->data['product'] = $this->site->getProductByID($product_id);
            $wh_pr = $this->products_model->getProductQuantity($product_id, $warehouse_id);
            $this->data['rack'] = $wh_pr['rack'];
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'products/set_rack', $this->data);
        }
    }

    public function stock_counts($warehouse_id = null)
    {
        $this->sma->checkPermissions('stock_count');

        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        if ($this->Owner || $this->Admin || !$this->session->userdata('warehouse_id')) {
            $this->data['warehouses'] = $this->site->getAllWarehouses();
            $this->data['warehouse_id'] = $warehouse_id;
            $this->data['warehouse'] = $warehouse_id ? $this->site->getWarehouseByID($warehouse_id) : null;
        } else {
            $this->data['warehouses'] = null;
            $this->data['warehouse_id'] = $this->session->userdata('warehouse_id');
            $this->data['warehouse'] = $this->session->userdata('warehouse_id') ? $this->site->getWarehouseByID($this->session->userdata('warehouse_id')) : null;
        }

        $bc = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('products'), 'page' => lang('products')], ['link' => '#', 'page' => lang('stock_counts')]];
        $meta = ['page_title' => lang('stock_counts'), 'bc' => $bc];
        $this->page_construct('products/stock_counts', $meta, $this->data);
    }

    public function suggestions()
    {
        $term = $this->input->get('term', true);
        if (strlen($term) < 1 || !$term) {
            die("<script type='text/javascript'>setTimeout(function(){ window.top.location.href = '" . admin_url('welcome') . "'; }, 10);</script>");
        }
        $term = addslashes($term);
        $rows = $this->products_model->getProductNames($term);
        if ($rows) {
            foreach ($rows as $row) {
                $pr[] = ['id' => $row->id, 'label' => $row->name . ' (' . $row->code . ')', 'code' => $row->code, 'name' => $row->name, 'price' => $row->price, 'qty' => 1];
            }
            $this->sma->send_json($pr);
        } else {
            $this->sma->send_json([['id' => 0, 'label' => lang('no_match_found'), 'value' => $term]]);
        }
    }

    /* ------------------------------------------------------------------ */

    public function update_price()
    {
        $this->sma->checkPermissions('csv');
        $this->load->helper('security');
        $this->form_validation->set_rules('userfile', lang('upload_file'), 'xss_clean');

        if ($this->form_validation->run() == true) {
            if (DEMO) {
                $this->session->set_flashdata('message', lang('disabled_in_demo'));
                admin_redirect('welcome');
            }

            if (isset($_FILES['userfile'])) {
                $this->load->library('upload');
                $config['upload_path'] = $this->digital_upload_path;
                $config['allowed_types'] = 'csv';
                $config['max_size'] = $this->allowed_file_size;
                $config['overwrite'] = true;
                $config['encrypt_name'] = true;
                $config['max_filename'] = 25;
                $this->upload->initialize($config);

                if (!$this->upload->do_upload()) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    admin_redirect('products');
                }

                $csv = $this->upload->file_name;

                $arrResult = [];
                $handle = fopen($this->digital_upload_path . $csv, 'r');
                if ($handle) {
                    while (($row = fgetcsv($handle, 1000, ',')) !== false) {
                        $arrResult[] = $row;
                    }
                    fclose($handle);
                }
                $titles = array_shift($arrResult);

                $keys = ['code', 'price'];

                $final = [];

                foreach ($arrResult as $key => $value) {
                    $final[] = array_combine($keys, $value);
                }
                $rw = 2;
                foreach ($final as $csv_pr) {
                    if (!$this->products_model->getProductByCode(trim($csv_pr['code']))) {
                        $this->session->set_flashdata('message', lang('check_product_code') . ' (' . $csv_pr['code'] . '). ' . lang('code_x_exist') . ' ' . lang('line_no') . ' ' . $rw);
                        admin_redirect('products');
                    }
                    $rw++;
                }
            }
        } elseif ($this->input->post('update_price')) {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect('system_settings/group_product_prices/' . $group_id);
        }

        if ($this->form_validation->run() == true && !empty($final)) {
            $this->products_model->updatePrice($final);
            $this->session->set_flashdata('message', lang('price_updated'));
            admin_redirect('products');
        } else {
            $this->data['userfile'] = [
                'name' => 'userfile',
                'id' => 'userfile',
                'type' => 'text',
                'value' => $this->form_validation->set_value('userfile'),
            ];
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'products/update_price', $this->data);
        }
    }

    public function view($id = null)
    {
        $this->sma->checkPermissions('index');

        $pr_details = $this->products_model->getProductByID($id);
        if (!$id || !$pr_details) {
            $this->session->set_flashdata('error', lang('prduct_not_found'));
            redirect($_SERVER['HTTP_REFERER']);
        }
        $this->data['barcode'] = "<img src='" . admin_url('products/gen_barcode/' . $pr_details->code . '/' . $pr_details->barcode_symbology . '/40/0') . "' alt='" . $pr_details->code . "' class='pull-left' />";
        if ($pr_details->type == 'combo') {
            $this->data['combo_items'] = $this->products_model->getProductComboItems($id);
        }
        $this->data['product'] = $pr_details;
        $this->data['unit'] = $this->site->getUnitByID($pr_details->unit);
        $this->data['brand'] = $this->site->getBrandByID($pr_details->brand);
        $this->data['images'] = $this->products_model->getProductPhotos($id);
        $this->data['category'] = $this->site->getCategoryByID($pr_details->category_id);
        $this->data['subcategory'] = $pr_details->subcategory_id ? $this->site->getCategoryByID($pr_details->subcategory_id) : null;
        $this->data['tax_rate'] = $pr_details->tax_rate ? $this->site->getTaxRateByID($pr_details->tax_rate) : null;
        $this->data['popup_attributes'] = $this->popup_attributes;
        $this->data['warehouses'] = $this->products_model->getAllWarehousesWithPQ($id);
        $this->data['options'] = $this->products_model->getProductOptionsWithWH($id);
        $this->data['variants'] = $this->products_model->getProductOptions($id);
        $this->data['sold'] = $this->products_model->getSoldQty($id);
        $this->data['purchased'] = $this->products_model->getPurchasedQty($id);

        $bc = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('products'), 'page' => lang('products')], ['link' => '#', 'page' => $pr_details->name]];
        $meta = ['page_title' => $pr_details->name, 'bc' => $bc];
        $this->page_construct('products/view', $meta, $this->data);
    }

    public function view_adjustment($id)
    {
        $this->sma->checkPermissions('adjustments', true);

        $adjustment = $this->products_model->getAdjustmentByID($id);
        if (!$id || !$adjustment) {
            $this->session->set_flashdata('error', lang('adjustment_not_found'));
            $this->sma->md();
        }

        $this->data['inv'] = $adjustment;
        $this->data['rows'] = $this->products_model->getAdjustmentItems($id);
        $this->data['created_by'] = $this->site->getUser($adjustment->created_by);
        $this->data['updated_by'] = $this->site->getUser($adjustment->updated_by);
        $this->data['warehouse'] = $this->site->getWarehouseByID($adjustment->warehouse_id);
        $this->load->view($this->theme . 'products/view_adjustment', $this->data);
    }

    public function view_bundle($id)
    {
        $this->sma->checkPermissions('bundles', true);

        $bundle = $this->products_model->getBundleByID($id);
        if (!$id || !$bundle) {
            $this->session->set_flashdata('error', lang('bundle_not_found'));
            $this->sma->md();
        }

        $this->data['bundle'] = $bundle;
        $this->data['rows'] = $this->products_model->getBundleItems($id);
        $this->data['created_by'] = $this->site->getUser($bundle->created_by);
        //$this->data['updated_by'] = $this->site->getUser($bundle->updated_by);
        // $this->data['warehouse']  = $this->site->getWarehouseByID($bundle->warehouse_id);
        $this->load->view($this->theme . 'products/view_bundle', $this->data);
    }

    public function view_count($id)
    {
        $this->sma->checkPermissions('stock_count', true);
        $stock_count = $this->products_model->getStouckCountByID($id);
        if (!$stock_count->finalized) {
            $this->sma->md('admin/products/finalize_count/' . $id);
        }

        $this->data['stock_count'] = $stock_count;
        $this->data['stock_count_items'] = $this->products_model->getStockCountItems($id);
        $this->data['warehouse'] = $this->site->getWarehouseByID($stock_count->warehouse_id);
        $this->data['adjustment'] = $this->products_model->getAdjustmentByCountID($id);
        $this->load->view($this->theme . 'products/view_count', $this->data);
    }

    public function get_item_by_gtin_batch_expiry(){
        $gtin = $this->input->get('gtin');
        $batch = $this->input->get('batch');
        $expiry = $this->input->get('expiry');
        $warehouse_id = $this->input->get('warehouse_id'); // Optionally filter by warehouse if needed
        $customer_id = $this->input->get('customer_id');
        $module = $this->input->get('module');

        $this->db->select("im.net_unit_sale, 
                        im.net_unit_cost, 
                        im.real_unit_cost,
                        im.product_id,
                        pr.name as product_name, im.batch_number as batchno, im.expiry_date as expiry,
                        p.supplier_id, p.supplier,
                        SUM(IFNULL(im.quantity, 0)) as total_quantity,
                        pr.tax_rate, pr.type, pr.unit, pr.code as product_code, im.avz_item_code", false);
        $this->db->from('sma_inventory_movements im');
        $this->db->join('sma_purchases p', 'p.id = im.reference_id AND im.type = "purchase"', 'left');
        $this->db->join('sma_products pr', 'pr.id = im.product_id', 'left');
        if ($warehouse_id) {
            $this->db->where('im.location_id', $warehouse_id);
        }
        if ($batch) {
            $this->db->where("LOCATE(im.batch_number, '{$batch}') >", 0);
        }
        if ($expiry) {
            // Extract year and month from the provided expiry
            $expiry_parts = explode(' ', $expiry); // e.g., '08 2025'
            if (count($expiry_parts) === 2) {
                $expiry_month = $expiry_parts[0];
                $expiry_year = $expiry_parts[1];
    
                // Use YEAR() and MONTH() functions to compare with the database expiry_date
                $this->db->where("YEAR(im.expiry_date)", $expiry_year);
                $this->db->where("MONTH(im.expiry_date)", $expiry_month);
            }
        }
        $this->db->where('pr.code', $gtin);

        $this->db->group_by(['pr.code', 'im.batch_number', 'im.expiry_date']);
        $this->db->having('total_quantity !=', 0);
        $query = $this->db->get();
        //echo $this->db->last_query();exit;
        if ($query->num_rows() > 0) {
            $rows = $query->result();

            $r = 0;
            $count = 0;

            foreach ($rows as $row) {
                $c = uniqid(mt_rand(), true);
                $option = false;
                $row->quantity = $row->total_quantity;
                $row->base_quantity = 0;
                $row->qty = $row->total_quantity;
                $row->discount = '0';

                $row->quantity_balance = 0;
                $row->ordered_quantity = 0;
                $row->cost = 0;

                $row->batch_no = $row->batchno;
                $row->batchQuantity = 0;
                $row->batchPurchaseCost = 0;

                $row->id = $row->product_id;
                $row->name = $row->product_name;
                $row->code = $row->product_code;

                $row->base_unit = $row->unit;

                $units = $this->site->getUnitsByBUID($row->base_unit);
                $tax_rate = $this->site->getTaxRateByID($row->tax_rate);

                $batches = [];
                $options = [];
                $total_quantity = $row->total_quantity;
                $count++;
                $row->serial_no = $count;
                $pr[] = [
                    'id' => sha1($c . $r),
                    'item_id' => $row->product_id,
                    'label' => $row->product_name . ' (' . $row->code . ')',
                    'row' => $row,
                    'tax_rate' => $tax_rate,
                    'units' => $units,
                    'options' => $options,
                    'batches' => $batches,
                    'total_quantity' => $total_quantity
                ];
                $r++;
            }
            $this->sma->send_json($pr);

        } else {
            // Return an error if no records found
            return [];
        }
    }

    public function get_items_by_avz_code()
    {
        $term = $this->input->get('term');
        $warehouse_id = $this->input->get('warehouse_id'); // Optionally filter by warehouse if needed
        $customer_id = $this->input->get('customer_id');
        $module = $this->input->get('module');

        if ($customer_id && $module != 'sales' && $module != 'pos' && $module != 'transfer') { // This block is for return by customer only
            $this->db->select("im.net_unit_sale, 
                            im.net_unit_cost, 
                            im.real_unit_cost,
                            im.customer_id,
                            im.product_id,
                            pr.name as product_name, im.batch_number as batchno, im.expiry_date as expiry,
                            pr.tax_rate, pr.type, pr.unit, pr.code as product_code, im.avz_item_code,
                            (SUM(CASE WHEN im.type = 'customer_return' AND im.customer_id = ".$customer_id." THEN -1*im.quantity ELSE 0 END) - SUM(CASE WHEN im.type IN ('sale', 'pos') AND im.customer_id = ".$customer_id." THEN im.quantity ELSE 0 END) ) AS total_quantity", false);
            $this->db->from('sma_inventory_movements im');
            $this->db->join('sma_products pr', 'pr.id = im.product_id', 'left');
            $this->db->where('im.location_id', $warehouse_id);
            $this->db->where('im.avz_item_code', $term);
            $this->db->where('im.customer_id', $customer_id);

            $this->db->group_by(['im.avz_item_code', 'im.batch_number', 'im.expiry_date']);
            //$this->db->having('total_quantity !=', 0);
            $query = $this->db->get();
            //echo $this->db->last_query();exit;
        } else {
            $this->db->select("im.net_unit_sale, 
                            im.net_unit_cost, 
                            im.real_unit_cost,
                            im.product_id,
                            pr.name as product_name, im.batch_number as batchno, im.expiry_date as expiry,
                            p.supplier_id, p.supplier,
                            SUM(IFNULL(im.quantity, 0)) as total_quantity,
                            pr.tax_rate, pr.type, pr.unit, pr.code as product_code, im.avz_item_code", false);
            $this->db->from('sma_inventory_movements im');
            $this->db->join('sma_purchases p', 'p.id = im.reference_id AND im.type = "purchase"', 'left');
            $this->db->join('sma_products pr', 'pr.id = im.product_id', 'left');
            if ($warehouse_id) {
                $this->db->where('im.location_id', $warehouse_id);
            }
            $this->db->where('im.avz_item_code', $term);

            $this->db->group_by(['im.avz_item_code', 'im.batch_number', 'im.expiry_date']);
            $this->db->having('total_quantity !=', 0);
            $query = $this->db->get();

            /*$this->db->select('
                pi.avz_item_code, 
                pi.product_code, 
                pur.net_unit_sale, 
                pur.net_unit_cost, 
                pur.real_unit_cost, 
                pr.tax_rate, 
                pr.type, 
                pr.unit, 
                p.supplier_id, 
                p.supplier, 
                pi.product_id, 
                pi.product_name, 
                pi.batchno, 
                pi.expiry, 
                SUM(IFNULL(im.quantity, 0)) as total_quantity
            ');
            $this->db->from('sma_purchase_items pi');
            $this->db->join('sma_purchases p', 'p.id = pi.purchase_id', 'left');
            $this->db->join('sma_inventory_movements im', 'pi.avz_item_code = im.avz_item_code', 'left');
            $this->db->join('sma_inventory_movements pur', 'pi.avz_item_code = pur.avz_item_code AND pur.type = "purchase"', 'left');
            $this->db->join('sma_products pr', 'pr.id = pi.product_id', 'left');
            $this->db->where('pi.avz_item_code', $term);

            if ($warehouse_id) {
                $this->db->where('pi.warehouse_id', $warehouse_id);
                $this->db->where('im.location_id', $warehouse_id);
            }

            $this->db->group_by(['pi.warehouse_id', 'pi.avz_item_code', 'pi.expiry']);
            $this->db->having('total_quantity >', 0);
            $query = $this->db->get();*/

        }

        if ($query->num_rows() > 0) {
            $rows = $query->result();

            $r = 0;
            $count = 0;

            foreach ($rows as $row) {
                $c = uniqid(mt_rand(), true);
                $option = false;
                $row->quantity = $row->total_quantity;
                //$row->item_tax_method  = $row->tax_method;
                $row->base_quantity = 0;
                //$row->net_unit_cost    = 0; // commented because coming in query
                //$row->base_unit        = $row->unit;
                //$row->base_unit_cost   = $row->cost;
                //$row->unit             = $row->purchase_unit ? $row->purchase_unit : $row->unit;
                $row->qty = $row->total_quantity;
                $row->discount = '0';

                $row->quantity_balance = 0;
                $row->ordered_quantity = 0;
                $row->cost = 0;

                $row->batch_no = $row->batchno;
                $row->batchQuantity = 0;
                $row->batchPurchaseCost = 0;
                //$row->expiry  = null;

                $row->id = $row->product_id;
                $row->name = $row->product_name;
                $row->code = $row->product_code;

                $row->base_unit = $row->unit;

                $units = $this->site->getUnitsByBUID($row->base_unit);
                $tax_rate = $this->site->getTaxRateByID($row->tax_rate);

                $batches = [];
                $options = [];
                $total_quantity = $row->total_quantity;
                $count++;
                $row->serial_no = $count;
                $pr[] = [
                    'id' => sha1($c . $r),
                    'item_id' => $row->product_id,
                    'label' => $row->product_name . ' (' . $row->code . ')',
                    'row' => $row,
                    'tax_rate' => $tax_rate,
                    'units' => $units,
                    'options' => $options,
                    'batches' => $batches,
                    'total_quantity' => $total_quantity
                ];
                $r++;
            }
            $this->sma->send_json($pr);

        } else {
            // Return an error if no records found
            return [];
        }
    }

    public function get_avz_item_code_details()
    {
        $item_id = $this->input->get('item_id');
        $warehouse_id = $this->input->get('warehouse_id'); // Optionally filter by warehouse if needed
        $customer_id = $this->input->get('customer_id');
        //echo json_encode(['status' => 'error', 'message' => $customer_id.'-'.$item_id.'-'.$warehouse_id]);
       // return;
        // Validate that avz_item_code is provided
        if (!$item_id) {
            echo json_encode(['status' => 'error', 'message' => 'No item code provided']);
            return;
        }

        if ($customer_id) {
            $this->db->select("im.net_unit_sale, 
                            im.net_unit_cost, 
                            im.real_unit_cost,
                            im.real_unit_sale,
                            im.customer_id,
                            im.product_id,
                            pr.name as product_name, im.batch_number as batchno, im.expiry_date as expiry,
                            pr.tax_rate, pr.type, pr.unit, pr.code as product_code, im.avz_item_code,
                            (SUM(CASE WHEN im.type = 'customer_return' AND im.customer_id = ".$customer_id." THEN -1*im.quantity ELSE 0 END) - SUM(CASE WHEN im.type IN ('sale','pos') AND im.customer_id = ".$customer_id." THEN im.quantity ELSE 0 END) ) AS total_quantity", false);
            $this->db->from('sma_inventory_movements im');
            $this->db->join('sma_products pr', 'pr.id = im.product_id', 'inner');
            $this->db->where('im.location_id', $warehouse_id);
            $this->db->where('im.product_id', $item_id);
            $this->db->where('im.customer_id', $customer_id);
            $this->db->group_by(['im.avz_item_code', 'im.batch_number', 'im.expiry_date']);
            $this->db->having('total_quantity !=', 0);
            $query = $this->db->get();
        } else {
            /*$this->db->select('pi.avz_item_code, pi.product_code, im.net_unit_sale, im.net_unit_cost, im.real_unit_cost, pr.tax_rate, pr.type, pr.unit, p.supplier_id, p.supplier, pi.product_id, pi.product_name, pi.batchno, pi.expiry, SUM(IFNULL(im.quantity, 0)) as total_quantity');
            $this->db->from('sma_purchase_items pi');
            $this->db->join('sma_purchases p', 'p.id = pi.purchase_id', 'inner');
            $this->db->join('sma_inventory_movements im', 'pi.avz_item_code = im.avz_item_code', 'inner');
            $this->db->join('sma_products pr', 'pr.id = pi.product_id', 'inner');
            $this->db->where('pi.product_id', $item_id);
            if ($warehouse_id) {
                $this->db->where('pi.warehouse_id', $warehouse_id);
                $this->db->where('im.location_id', $warehouse_id);
            }
            $this->db->group_by(['pi.warehouse_id', 'pi.avz_item_code', 'pi.expiry']);
            $this->db->having('total_quantity >', 0);
            $query = $this->db->get();*/


            $this->db->select("im.net_unit_sale, 
                            im.net_unit_cost, 
                            im.real_unit_cost,
                            im.product_id,
                            pr.name as product_name, im.batch_number as batchno, im.expiry_date as expiry,
                            p.supplier_id, p.supplier,
                            SUM(IFNULL(im.quantity, 0)) as total_quantity,
                            pr.tax_rate, pr.type, pr.unit, pr.code as product_code, im.avz_item_code", false);
            $this->db->from('sma_inventory_movements im');
            $this->db->join('sma_purchases p', 'p.id = im.reference_id AND im.type = "purchase"', 'left');
            $this->db->join('sma_products pr', 'pr.id = im.product_id', 'left');
            if ($warehouse_id) {
                $this->db->where('im.location_id', $warehouse_id);
            }
            $this->db->where('im.product_id', $item_id);

            $this->db->group_by(['im.avz_item_code', 'im.batch_number', 'im.expiry_date']);
            $this->db->having('total_quantity !=', 0);
            $query = $this->db->get();

        }

        if ($query->num_rows() > 0) {
            $rows = $query->result();

            $r = 0;
            $count = 0;

            foreach ($rows as $row) {
                $c = uniqid(mt_rand(), true);
                $option = false;
                $row->quantity = $row->total_quantity;
                //$row->item_tax_method  = $row->tax_method;
                $row->base_quantity = 0;
                //$row->net_unit_cost    = 0; // commented because coming in query
                //$row->base_unit        = $row->unit;
                //$row->base_unit_cost   = $row->cost;
                //$row->unit             = $row->purchase_unit ? $row->purchase_unit : $row->unit;
                $row->qty = $row->total_quantity;
                $row->discount = '0';

                $row->quantity_balance = 0;
                $row->ordered_quantity = 0;
                $row->cost = $row->real_unit_cost;

                $row->batch_no = $row->batchno;
                $row->batchQuantity = 0;
                $row->batchPurchaseCost = 0;
                //$row->expiry  = null;

                $row->id = $row->product_id;
                $row->name = $row->product_name;
                $row->code = $row->product_code;

                $row->base_unit = $row->unit;

                $units = $this->site->getUnitsByBUID($row->base_unit);
                $tax_rate = $this->site->getTaxRateByID($row->tax_rate);

                $batches = [];
                $options = [];
                $total_quantity = $row->total_quantity;
                $count++;
                $row->serial_no = $count;
                $pr[] = [
                    'id' => sha1($c . $r),
                    'item_id' => $row->product_id,
                    'label' => $row->product_name . ' (' . $row->code . ')',
                    'row' => $row,
                    'tax_rate' => $tax_rate,
                    'units' => $units,
                    'options' => $options,
                    'batches' => $batches,
                    'total_quantity' => $total_quantity
                ];
                $r++;
            }
            $this->sma->send_json($pr);

        } else {
            // Return an error if no records found
            echo json_encode(['status' => 'error', 'message' => 'No items found for this item code']);
        }

    }
}
