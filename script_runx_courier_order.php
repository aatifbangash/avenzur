<?php 
// $secret_key = '';
// echo php_sapi_name();

// Host: 81.208.168.52
// User: remote_user
// Password: re$Pa1msee$ot_ur
// database: directpa_pharma

$hostname = "localhost";
$username = "root";
$password = '';
$database = "avenzur";

// Create connection
$conn = new mysqli($hostname, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Run-X
$id = 4; 
// Prepare and bind
$stmt = $conn->prepare("SELECT * FROM sma_courier WHERE id = ? LIMIT 1");
$stmt->bind_param("i", $id); // 'i' specifies the parameter type is integer

$stmt->execute();

$result = $stmt->get_result();

$courier = array();
if ($result->num_rows > 0) {
    // Fetch associative array for the row
    $courier = $result->fetch_object();
    // Process your row here
} else {
    echo "0 results";
    exit;
}

print_r($courier);

exit;

// get bearer token
$token  = getBearerToken($courier);
$orderResponse = makeOrderRequest($courier->url, $token);

$response = json_decode($orderResponse);
// echo "<pre>";
// print_r($response->data->data);
if(!isset($response->data->data)) {
    echo "No data found";exit;
}

$data = $response->data;
echo "<pre>";
print_r($data);
exit;

foreach($data as $key => $order) {
   $tracking_id = $order->id;
   $tracking_status = $order->order_status->en_name;
   $order_id = $order->order_number;

//    $stmt = $conn->prepare("UPDATE sma_sales SET courier_order_tracking_id = ?, courier_order_status = ? WHERE id = ?");
//    $stmt->bind_param("ssi", $tracking_id, $tracking_status, $order_id);
//    if ($stmt->execute() === TRUE) {
//     echo "Record updated successfully";
// } else {
//     echo "Error updating record: " . $stmt->error;
// }

}

 function getBearerToken($courier) {
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

function makeOrderRequest($apiUrl, $token) {
    $headers = array(
        'Authorization: Bearer ' . $token,
        'Accept: application/json',
        'Content-Type: application/json',
    );

    $ch = curl_init($apiUrl . 'orders');

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
