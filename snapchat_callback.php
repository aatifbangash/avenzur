<?php
session_start();
function get_access_token($code) {
    $client_id = '7bcc6ae7-1685-42af-8bff-6ef9e4ec71a2';
    $client_secret = '475ee01b6b1aa9d41484';
    $redirect_uri = 'https://avenzur.com/snapchat_callback.php';
    $oauth_url = 'https://accounts.snapchat.com/login/oauth2';

    $token_url = "{$oauth_url}/access_token";

    $data = array(
        'grant_type' => 'authorization_code',
        'client_id' => $client_id,
        'client_secret' => $client_secret,
        'code' => $code,
        'redirect_uri' => $redirect_uri
    );

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $token_url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($ch);
    if (curl_errno($ch)) {
        echo 'Error:' . curl_error($ch);
        curl_close($ch);
        return null;
    } else {
        curl_close($ch);
        return json_decode($response, true);
    }
}

function refresh_token($refresh_token) {
    $client_id = '7bcc6ae7-1685-42af-8bff-6ef9e4ec71a2';
    $client_secret = '475ee01b6b1aa9d41484';
    $oauth_url = 'https://accounts.snapchat.com/login/oauth2';

    $token_url = "{$oauth_url}/access_token";

    $data = array(
        'grant_type' => 'refresh_token',
        'client_id' => $client_id,
        'client_secret' => $client_secret,
        'refresh_token' => $refresh_token
    );

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $token_url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($ch);
    if (curl_errno($ch)) {
        echo 'Error:' . curl_error($ch);
        curl_close($ch);
        return null;
    } else {
        curl_close($ch);
        return json_decode($response, true);
    }
}

// function create_product_feed($access_token, $catalog_id, $product_feed_data) {
//     $api_url = "https://adsapi.snapchat.com/v1/catalogs/{$catalog_id}/product_feeds";

//     $json_data = json_encode($product_feed_data);

//     $ch = curl_init();
//     curl_setopt($ch, CURLOPT_URL, $api_url);
//     curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
//     curl_setopt($ch, CURLOPT_POST, 1);
//     curl_setopt($ch, CURLOPT_POSTFIELDS, $json_data);

//     $headers = array(
//         'Authorization: Bearer ' . $access_token,
//         'Content-Type: application/json'
//     );
//     curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

//     $response = curl_exec($ch);

//     if (curl_errno($ch)) {
//         $error_msg = curl_error($ch);
//         return 'Error: ' . $error_msg;
//     } else {
//         curl_close($ch);
//         return 'Response: ' . $response;
//     }
// }


function get_catalogs($access_token, $organization_id) {
    $api_url = "https://adsapi.snapchat.com/v1/organizations/{$organization_id}/catalogs";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $api_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

    $headers = array(
        'Authorization: Bearer ' . $access_token,
        'Content-Type: application/json'
    );
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    $response = curl_exec($ch);

    if (curl_errno($ch)) {
        $error_msg = curl_error($ch);
        return 'Error: ' . $error_msg;
    } else {
        curl_close($ch);
        return json_decode($response, true);
    }
}

function get_catalog_products($access_token, $catalog_id) {
    $api_url = "https://adsapi.snapchat.com/v1/catalogs/{$catalog_id}/products";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $api_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

    $headers = array(
        'Authorization: Bearer ' . $access_token,
        'Content-Type: application/json'
    );
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    $response = curl_exec($ch);

    if (curl_errno($ch)) {
        $error_msg = curl_error($ch);
        return 'Error: ' . $error_msg;
    } else {
        curl_close($ch);
        return json_decode($response, true);
    }
}

function create_product_feed($access_token, $catalog_id) {
    $baseUrl = 'https://avenzur.com';
    // $baseUrl = 'http://localhost:8001';

    $api_url = "https://adsapi.snapchat.com/v1/catalogs/{$catalog_id}/product_feeds";

    // Set the payload
    $data = array(
        'product_feeds' => array(
            array(
                'catalog_id' => "{$catalog_id}", // skin catalog id
                'name' => 'Badger Burrow supplies',
                'default_currency' => 'SAR',
                'status' => 'ACTIVE',
                'schedule' => array(
                    'url' => $baseUrl . '/snapchat-product-feed.csv',
                    'username' => '',
                    'password' => '',
                    'interval_type' => 'HOURLY',
                    'interval_count' => '1',
                    'timezone' => 'PST',
                    'minute' => '15'
                )
            )
        )
    );

    // Convert the payload to JSON
    $json_data = json_encode($data);

    // Initialize cURL
    $ch = curl_init();

    // Set cURL options
    curl_setopt($ch, CURLOPT_URL, $api_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $json_data);

    // Set headers
    $headers = array(
        'Authorization: Bearer ' . $access_token,
        'Content-Type: application/json'
    );
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    // Execute the request and get the response
    $response = curl_exec($ch);

    // Check for errors
    if (curl_errno($ch)) {
        $error_msg = curl_error($ch);
        echo 'Error: ' . $error_msg;
    } else {
        // Close the cURL session
        curl_close($ch);

        // Output the response
        echo 'Response: ' . $response;
    }
}

function create_catalog($access_token, $organization_id) {
    $api_url = "https://adsapi.snapchat.com/v1/organizations/{$organization_id}/catalogs";

    // Set the payload
    $data = array(
        'catalogs' => array(
            array(
                'organization_id' => "{$organization_id}",
                'name' => 'Honeybear Catalog',
                'vertical' => 'COMMERCE',
                'event_sources' => array(
                    array(
                        'id' => 'aa1a11aa-a111-1a11-1a1a-a1aa1aa1aaa',
                        'type' => 'PIXEL'
                    ),
                    array(
                        'id' => '2bbb22b2-b22bb-2bb2-22b2-222b22b222bb',
                        'type' => 'MOBILE_APP'
                    )
                )
            )
        )
    );

    // Convert the payload to JSON
    $json_data = json_encode($data);

    // Initialize cURL
    $ch = curl_init();

    // Set cURL options
    curl_setopt($ch, CURLOPT_URL, $api_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $json_data);

    // Set headers
    $headers = array(
        'Authorization: Bearer ' . $access_token,
        'Content-Type: application/json'
    );
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    // Execute the request and get the response
    $response = curl_exec($ch);

    // Check for errors
    if (curl_errno($ch)) {
        $error_msg = curl_error($ch);
        echo 'Error: ' . $error_msg;
    } else {
        // Decode the response
        $response_data = json_decode($response, true);
        
        // Check if catalog was created successfully
        if (!empty($response_data['catalogs'][0]['id'])) {
            $catalog_id = $response_data['catalogs'][0]['id'];
            echo 'Catalog created successfully. Catalog ID: ' . $catalog_id;
            
            // Create product feed for the new catalog
            create_product_feed($access_token, $catalog_id);
        } else {
            echo 'Failed to create catalog. Response: ' . $response;
        }
        
        // Close the cURL session
        curl_close($ch);
    }
}

function get_product_feeds($access_token, $catalog_id) {
    $api_url = "https://adsapi.snapchat.com/v1/catalogs/{$catalog_id}/product_feeds";

    // Initialize cURL
    $ch = curl_init();

    // Set cURL options
    curl_setopt($ch, CURLOPT_URL, $api_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HTTPGET, 1);

    // Set headers
    $headers = array(
        'Authorization: Bearer ' . $access_token,
        'Content-Type: application/json'
    );
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    // Execute the request and get the response
    $response = curl_exec($ch);

    // Check for errors
    if (curl_errno($ch)) {
        $error_msg = curl_error($ch);
        echo 'Error: ' . $error_msg;
    } else {
        // Close the cURL session
        curl_close($ch);

        // Decode and return the response
        $response_data = json_decode($response, true);
        return $response_data;
    }
}

function update_product_feed($access_token, $catalog_id, $product_feed_id, $product_feed_details) {
    $api_url = "https://adsapi.snapchat.com/v1/catalogs/{$catalog_id}/product_feeds";

    // Set the payload
    $data = array(
        'product_feeds' => array(
            array(
                'id' => $product_feed_id,
                'catalog_id' => $catalog_id,
                'name' => $product_feed_details['name'],
                'default_currency' => $product_feed_details['default_currency'],
                'status' => $product_feed_details['status'],
                'schedule' => array(
                    'url' => $product_feed_details['url'],
                    'username' => $product_feed_details['username'],
                    'password' => $product_feed_details['password'],
                    'interval_type' => $product_feed_details['interval_type'],
                    'interval_count' => $product_feed_details['interval_count'],
                    'timezone' => $product_feed_details['timezone'],
                    'hour' => $product_feed_details['hour'],
                    'minute' => $product_feed_details['minute']
                )
            )
        )
    );

    // Convert the payload to JSON
    $json_data = json_encode($data);

    // Initialize cURL
    $ch = curl_init();

    // Set cURL options
    curl_setopt($ch, CURLOPT_URL, $api_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
    curl_setopt($ch, CURLOPT_POSTFIELDS, $json_data);

    // Set headers
    $headers = array(
        'Authorization: Bearer ' . $access_token,
        'Content-Type: application/json'
    );
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    // Execute the request and get the response
    $response = curl_exec($ch);

    // Check for errors
    if (curl_errno($ch)) {
        $error_msg = curl_error($ch);
        echo 'Error: ' . $error_msg;
    } else {
        // Close the cURL session
        curl_close($ch);

        // Output the response
        echo 'Response: ' . $response;
    }
}

function file_exists_curl($url) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_NOBODY, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return ($http_code >= 200 && $http_code < 300);
}


// $baseUrl = 'https://avenzur.com';
// $baseUrl = 'http://localhost:8001';
// Step 2: After user authorizes, handle the callback and get the access token
echo "<pre>";
echo "post"; print_r($_POST);
print_r($_GET);
if (isset($_GET['code'])) {
    $code = $_GET['code'];
    $token_response = get_access_token($code);
    print_r($token_response);
    if ($token_response) {
        $access_token = $token_response['access_token'];
        $refresh_token = $token_response['refresh_token'];

        // Store tokens in session or database for future use
        $_SESSION['access_token'] = $access_token;
        $_SESSION['refresh_token'] = $refresh_token;

        // Redirect to a secure page or show a success message
        echo "Access token obtained successfully.";
    } else {
        // Handle error in getting access token
        echo 'Error in getting access token.';
        exit;
    }
} else {
    // $_SESSION['access_token'] = $access_token = "eyJpc3MiOiJodHRwczpcL1wvYWNjb3VudHMuc25hcGNoYXQuY29tXC9hY2NvdW50c1wvb2F1dGgyXC90b2tlbiIsInR5cCI6IkpXVCIsImVuYyI6IkExMjhDQkMtSFMyNTYiLCJhbGciOiJkaXIiLCJraWQiOiJhY2Nlc3MtdG9rZW4tYTEyOGNiYy1oczI1Ni4wIn0..ojRT0MRRLLMtUP7wooSWXg.iqNMaCfIRJ5NbjZeq3s3IvtLkoZMet7RklNEW_qYQe3vq91kekiYirjQKcXt2zG16flsc0lw29N7aSHa3YLd7vwn1SRINw0Za2QKV-sTuvdZtrZKOLj8M7AzxgSTWBxkZGQ5Rf3rlHdKh5YTPWDXSQeaC_o_hdbfWgIIjCHZCqeI4z3lxioeNe-h8Eele3DqNxxRRYD8o7Q3cs0s3yHXtJoBvKokvIJ8nVo9JVz3JKbAonFPfAB7uBlQ0yWjVgYXOcLkhI6Y0bu-gx8DgfqLsnkF7Wp5HYKvGWFMW9uEVQxbUax9rLeZGBoFnAyDCOiqWqoW4-QMJZ8mRHc4IWxvY9UvelwHvPvXeX8fqpjIaeCzn-SXoPiHHwI_jfaqo5W1abQOoIft3x8pkrg3f5QOsFn2k7URpu4ljZew3lItniM1JJj5qM_HdRSPeWgTOkNYp60t7fCIADx4FT3O5ZoB9TCxFMrAUyNwoeQr1wGDPdQDKQfEoDN0qY5s_CECYbHQxN6oLhRIRsz8oOtazg02-hy_YscMSw1fR-KvtltIetTDge7daU3FYu9oc9TL4HGO0bWU1j0WZoKnBRkyjfhFCGUDi3qPlLiYF61sddQMFtRxpeEHGrNOymD5AtuLiw4Eya7DbOzpvckooymc1nXeEhA5PE6C8nF0L26O2ngTVn8Sqj6Dg-gZnhGUnoKS1Ac4bc6oGJpCtfUOeccpqRzJ90ebvfn8J4eo0cP4uSBzN9g.W3mGGCwKfY7hJXpuFhz41g";
    // $_SESSION['refresh_token'] = $refresh_token = "hCgwKCjE3MTc0Mzc2MDASpQEcKPYhfAuKff0XFwFzmJoZ4WLzVDO0i6PfwJ2jHjrE2qzjrOwWHWL6y-4evx2w4gaVMRhNabkQS8xQLqax0qEbWyZkGiNX7ZqBtlmxMBW-m2FXgNqmInDqdhrPECwRmSTJpOKu7uTAtuJcMfSRtuBb6OplpuD_GaemTa9qYZ0kmbf2TJfeWzQE8J4mQLv2f58qGpsylA5-SjMOhv4CTqlxgmeRzls";
    echo 'No authorization code found.';
}

print_r($_SESSION);

if (isset($_SESSION['access_token'])) {
    // Example: Creating a product feed
    $access_token = $_SESSION['access_token'];
    $organization_id = '41da0cbf-5fd8-4862-811a-2091aa633d52';

    // Get catalogs
    $catalogs = get_catalogs($access_token, $organization_id);
    echo '<pre>';
    print_r($catalogs);
    echo '</pre>';
     // Get products in a specific catalog
     if (!empty($catalogs['catalogs'])) {
        $catalog_id = $catalogs['catalogs'][1]['catalog']['id'];
        $product_feeds = get_product_feeds($access_token, $catalog_id);
        echo '<pre>';
        print_r($product_feeds);
        echo '</pre>';
        if(!empty($product_feeds['product_feeds']))
        {
            // var_dump($product_feeds['product_feeds']); exit;
            $product_feed_id = $product_feeds['product_feeds'][0]['product_feed']['id'];

            $product_feed_details = array(
                'name' => $product_feeds['product_feeds'][0]['product_feed']['name'],
                'default_currency' => $product_feeds['product_feeds'][0]['product_feed']['default_currency'],
                'status' => 'ACTIVE',
                // 'url' => $baseUrl . '/snapchat-product-feed1.csv',
                'url' => 'https://avenzur.com/snapchat-product-feed.csv',
                'username' => '',
                'password' => '',
                'interval_type' => 'HOURLY',
                'interval_count' => 4,
                'timezone' => 'PST',
                'hour' => 12,
                'minute' => 30
            );

            update_product_feed($access_token, $catalog_id, $product_feed_id, $product_feed_details);
        }
        // create_product_feed($access_token, $catalog_id);
        
        // echo '</pre>';
        exit;
    } else {
        // Step 2: Create a New Catalog if None Exist
        create_catalog($access_token, $organization_id);
    }
}
// Step 3: Refresh token when needed
if (isset($_SESSION['refresh_token']) && !isset($_SESSION['access_token'])) {
    $refresh_token = $_SESSION['refresh_token'];
    $new_token_response = refresh_token($refresh_token);
    if ($new_token_response) {
        $_SESSION['access_token'] = $new_token_response['access_token'];
        $_SESSION['refresh_token'] = $new_token_response['refresh_token'];
    } else {
        // Handle error in refreshing token
        echo 'Error in refreshing token.';
        exit;
    }
}
?>