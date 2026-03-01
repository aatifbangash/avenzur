<?php

$hostname = "81.208.168.52";
$username =  "remote_user";
$password = 're$Pa1msee$ot_ur';
$database = "retaj";

$conn = new mysqli($hostname, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$current_date = date('Y-m-d');
$start_date = date('Y-m-d', strtotime('-10 days'));
$end_date = date('Y-m-d');

$stmt = $conn->prepare("SELECT * FROM sma_sales WHERE payment_status = 'paid' AND shop = 1 AND sale_status = 'completed' AND `date` BETWEEN ? AND ?");
$stmt->bind_param("ss", $start_date, $end_date);

//$stmt = $conn->prepare("SELECT * FROM sma_sales WHERE payment_status = 'paid' AND id = 1986 AND shop = 1 AND sale_status = 'completed'");

$stmt->execute();
$result_sales = $stmt->get_result();

if ($result_sales->num_rows > 0) {
    while ($sale = $result_sales->fetch_assoc()) {
        $courier_order_tracking_id = null;
        $pickup_time = '';
        $delivery_time = '';

        $stmt = $conn->prepare("SELECT * FROM sma_courier WHERE id = ? LIMIT 1");
        $stmt->bind_param("i", $sale['courier_id']);
        $stmt->execute();
        $courier_result = $stmt->get_result();

        if ($courier_result->num_rows > 0) {
            $courier = $courier_result->fetch_object();
        }

        if($sale['courier_id'] == 1){
            $courier_name = $courier->name;
            $token  = getRunXBearerToken($courier);
            $orderResponse = json_decode(runXGetOrder($courier->url, $token, $sale['courier_order_tracking_id']));
            if ($orderResponse != '') {
                if($orderResponse->data->order_status->en_name == 'Delivered'){
                    $delivery_time = new DateTime($orderResponse->data->updated_at);

                    $stmt_update = $conn->prepare("UPDATE sma_sales SET courier_delivery_time = ? WHERE id = ?");
                    $stmt_update->bind_param("si", $delivery_time->format('Y-m-d H:i:s'), $sale['id']);
                    $stmt_update->execute() ;
                    $stmt_update->close();
                }
            }
        }else if($sale['courier_id'] == 3){
            $courier_name = $courier->name;
            $orderResponse = trackJTOrders($courier, $sale['courier_order_tracking_id']);
            $orderDetails = $orderResponse->data[0]->details;

            foreach($orderDetails as $orderDetail){
                if($orderDetail->scanType == 'Pickup scan'){
                    $pickup_time = $orderDetail->scanTime;

                    $stmt_update = $conn->prepare("UPDATE sma_sales SET courier_pickup_time = ? WHERE id = ?");
                    $stmt_update->bind_param("si", $pickup_time, $sale['id']);
                    $stmt_update->execute() ;
                    $stmt_update->close();
                }

                if($orderDetail->scanType == 'Sign scan'){
                    $delivery_time = $orderDetail->scanTime;

                    $stmt_update = $conn->prepare("UPDATE sma_sales SET courier_delivery_time = ? WHERE id = ?");
                    $stmt_update->bind_param("si", $delivery_time, $sale['id']);
                    $stmt_update->execute() ;
                    $stmt_update->close();
                }
            }
        }else if($sale['courier_id'] == 4){
            $courier_name = $courier->name;
            /*$url = 'https://dal.channels.com.sa/aggregator/shipment/getShipmentStatus/'.$sale['courier_order_tracking_id'];
            $apiKey = '55511b89cebb7edb480f450f78e139f9';
            $ch = curl_init($url);

            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // Return response as a string
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Api-Key: ' . $apiKey
            ]);

            // Execute the request
            $response = curl_exec($ch);

            // Check for errors
            if (curl_errno($ch)) {
                echo 'Error:' . curl_error($ch);
            } else {
                // Decode and print the response
                $responseData = json_decode($response, true);
                echo "<pre>";
                print_r($responseData);
            }

            // Close the cURL session
            curl_close($ch);*/
        }

    }

    echo 'Script executed successfully...';
}else{
    echo 'No orders found for Today';
}

// Get J&T Tracking

function get_post_data($customerCode, $pwd, $key, $waybillinfo)
{

    $postdate = json_decode($waybillinfo, true);
    $postdate['customerCode'] = $customerCode;
    $postdate['digest'] = get_content_digest($customerCode, $pwd, $key);

    return json_encode($postdate);
}

function get_header_digest($post, $key)
{
    $digest = base64_encode(pack('H*', strtoupper(md5($post . $key))));
    return $digest;
}

function get_content_digest($customerCode, $pwd, $key)
{
    $str = strtoupper($customerCode . md5($pwd . 'jadada236t2')) . $key;

    return base64_encode(pack('H*', strtoupper(md5($str))));
}

function trackJTOrders($courier, $billCode)
{

    // Test API endpoint URL
    $url = $courier->url . 'logistics/trace';

    $privateKey = $courier->auth_key;
    $customerCode = $courier->username;
    $pwd = $courier->password;
    $account = $courier->api_account;

    $str = strtoupper($customerCode . md5($pwd . 'jadada236t2')) . $privateKey;

    $body_digest = base64_encode(pack('H*', strtoupper(md5($str))));

    $bizContent = '{
        "command": "1",
        "billCodes": "' . $billCode . '",
        "digest": "' . $body_digest . '"
    }';

    $post_data = get_post_data($customerCode, $pwd, $privateKey, $bizContent);
    $head_dagest = get_header_digest($post_data, $privateKey);
    $post_content = array(
        'bizContent' => $post_data
    );

    $postdata = http_build_query($post_content);

    $options = array(
        'http' => array(
            'method' => 'POST',
            'header' =>
                array(
                    'Content-type: application/x-www-form-urlencoded',
                    'apiAccount:' . $account,
                    'digest:' . $head_dagest,
                    'timestamp: ' . time()
                ),
            'content' => $postdata,
            'timeout' => 15 * 60
        )
    );
    $context = stream_context_create($options);

    $result = file_get_contents($url, false, $context);
    $response = json_decode($result);
    return $response;
}


// Get Run Tracking

function runXGetOrder($apiUrl, $token, $order_tracking_id) {
    $headers = array(
        'Authorization: Bearer ' . $token,
        'Accept: application/json',
        'Content-Type: application/json',
    );

    $ch = curl_init($apiUrl . 'orders/'.$order_tracking_id);

    // Set up your order request data here if needed
    // $orderData = ...

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    // Set up other CURLOPT options as needed
    // ...

    // You can set CURLOPT_POSTFIELDS if you have data to send in the request body

    $response = curl_exec($ch);
    if (curl_errno($ch)) {
        echo 'cURL error: ' . curl_error($ch);
    }

    curl_close($ch);

    return $response;
}

function getRunXBearerToken($courier) {
    $headers = array(
        'Accept: application/json',
        'Content-Type: application/json',
    );

    $data = array(
        'email' => $courier->username,
        'password' => $courier->password,
    );

    $ch = curl_init($courier->url . 'login');

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

    $response = curl_exec($ch);
    if (curl_errno($ch)) {
        echo 'cURL error: ' . curl_error($ch);
    }

    curl_close($ch);

    if($respArr = json_decode($response)){
        if(isset($respArr->success)){
            $token = $respArr->success->token;
        }else{
            $token = false;
        }
    }else{
        $token = false;
    }
    return $token; // Assuming the token is in 'access_token' field
}