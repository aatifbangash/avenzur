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

function create_product_feed($access_token, $catalog_id, $product_feed_data) {
    $api_url = "https://adsapi.snapchat.com/v1/catalogs/{$catalog_id}/product_feeds";

    $json_data = json_encode($product_feed_data);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $api_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $json_data);

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
        return 'Response: ' . $response;
    }
}


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
        $catalog_id = $catalogs['catalogs'][0]['catalog_id'];
        $products = get_catalog_products($access_token, $catalog_id);
        echo '<pre>';
        print_r($products);
        echo '</pre>';
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