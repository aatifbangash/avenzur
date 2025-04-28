<?php

define('BASEPATH', true);
$hostname = "81.208.168.52";
$username =  "remote_user";
$password = 're$Pa1msee$ot_ur';
$database = "rawabi"; 

$conn = new mysqli($hostname, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$current_date = date('Y-m-d');
$start_date = date('Y-m-d', strtotime('-10 days'));
$end_date = date('Y-m-d');

$stmt = $conn->prepare("SELECT sr.*, s.warehouse_id, w.gln as pharmacy_gln, w.rasd_user, w.rasd_pass
                        FROM sma_serial_numbers sr
                        INNER JOIN sma_sales s ON sr.sale_id = s.id
                        INNER JOIN sma_warehouses w ON w.id = s.warehouse_id
                        WHERE sr.is_pushed = 0 AND sr.`date_created` BETWEEN ? AND ?"
                      );
$stmt->bind_param("ss", $start_date, $end_date);
$stmt->execute();
$result_unprocessed_sales = $stmt->get_result();
$serial_ids = array();
$failed_serial_ids = array();

if ($result_unprocessed_sales->num_rows > 0) {
    while ($serial_no = $result_unprocessed_sales->fetch_assoc()) {
        $auth_token = authenticate($serial_no['rasd_user'], $serial_no['rasd_pass']);
        echo $serial_no['rasd_user'].'-'.$serial_no['rasd_pass'].$auth_token;exit;

        if ($auth_token) {
            $headers = array(
                'FunctionName:APIReq',
                'Token: ' . $auth_token,
                'Accept: */*',
                "Accept-Encoding: gzip, deflate, br"
            );

            $item = array();
            $item[] = (object)[
                'batchno' => $serial_no['batchno'],
                'serial_number' => $serial_no['serial_number'],
                'gtin' => $serial_no['gtin'],
            ];

            $payload = create_payload_for_gln($serial_no['pharmacy_gln'], $item);
            $response = api_call($payload, $headers);
            
            $response_body = $response['body'];
            $payload_used =  [
                'source_gln' => '',
                'destination_gln' => $serial_no['pharmacy_gln'],
                'warehouse_id' => $serial_no['warehouse_id']
            ];

            if (isset($response_body['DicOfDic']['MR']['TRID']) && $response_body['DicOfDic']['MR']['TRID']) {
                $serial_ids[] = $serial_no['id'];

                add_rasd_transactions($conn, $payload_used,'pharmacy_sale_product',1, $response,$payload);
                echo 'Script Executed Successfully...';
            } else {
                $failed_serial_ids[] = $serial_no['id'];

                add_rasd_transactions($conn, $payload_used,'pharmacy_sale_product',0, $response,$payload);
                echo "Error Calling API";
            }
        } 
    }


    if (!empty($serial_ids)) {
        $serial_ids_str = implode(',', $serial_ids);
        $update_sql = "UPDATE sma_serial_numbers SET is_pushed = 1 WHERE id IN ($serial_ids_str)";
        if ($conn->query($update_sql) === TRUE) {
            echo "Records updated successfully.";
        } else {
            echo "Error updating records: " . $conn->error;
        }
    }

    if (!empty($failed_serial_ids)) {
        $failed_serial_ids_str = implode(',', $failed_serial_ids);
        $update_sql = "UPDATE sma_serial_numbers SET is_pushed = 2 WHERE id IN ($failed_serial_ids_str)";
        if ($conn->query($update_sql) === TRUE) {
            echo "Records updated successfully.";
        } else {
            echo "Error updating records: " . $conn->error;
        }
    }

}

function add_rasd_transactions($conn,$payload_used, $function, $is_success, $response, $request)
{
    $source_gln = $payload_used['source_gln'];
    $destination_gln = $payload_used['destination_gln'];
    $warehouse_id = $payload_used['warehouse_id'];
    $warehouse_type = 'pharmacy';
    $gtin = "";
    $batch = "";

    $transaction = [
        "date" => date("Y-m-d"),
        "function" => $function,
        "source_gln" => $source_gln,
        "destination_gln" => $destination_gln,
        "gtin" => $gtin,
        "batch" => $batch,
        "warehouse_id" => $warehouse_id,
        "warehouse_type" => $warehouse_type,
        "response" => json_encode($response, true),
        "is_success" => $is_success,
        "request" => json_encode($request, true),
    ];

    $query = "INSERT INTO sma_rasd_transactions 
                (`date`, `function`, `source_gln`, `destination_gln`, `gtin`, `batch`, `warehouse_id`, `warehouse_type`, `response`, `is_success`, `request`) 
                VALUES 
                (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt2 = $conn->prepare($query);

    if ($stmt2 === false) {
        die('MySQL prepare error: ' . $conn->error);
    }

    // Bind parameters (adjust the types as needed)
    $stmt2->bind_param(
        'ssssssissis', // Define the types: all strings in this case
        $transaction['date'],
        $transaction['function'],
        $transaction['source_gln'],
        $transaction['destination_gln'],
        $transaction['gtin'],
        $transaction['batch'],
        $transaction['warehouse_id'],
        $transaction['warehouse_type'],
        $transaction['response'],
        $transaction['is_success'],
        $transaction['request']
    );

    // Execute the query
    if ($stmt2->execute()) {
        return true; // Successfully inserted
    } else {
        return false; // Failed to insert
    }
}

function authenticate($UEmail, $UPass) {
    $url = 'https://qdttsbe.qtzit.com:10100/api/web'; // Make sure to use the correct API URL
    $headers = array(
        'UEmail: ' . $UEmail,
        'UPass: ' . $UPass,
        'FunctionName: Login',
        'Accept: */*',
        'Accept-Encoding: gzip, deflate, br'
    );

    $response_data = make_post_request($url, $headers);
    echo $response_data;exit;
    if ($response_data) {
        $response = $response_data['body'];
        $response_headers = $response_data['headers'];
        
        preg_match('/token:\s*([^\r\n]+)/i', $response_headers, $matches);
        
        if (isset($matches[1])) {
            $token = $matches[1];
            return $token;
        } else {
            return null;
        }
    }
}

function create_payload_for_gln($gln, $items) {
    $payload = [
        'DicOfDic' => [
            '202' => [
                "167" => "",
                "166"=> "",
                "168"=> "",
                "169"=> ""   
            ],
            'MH' => [
                'MN' => '160',
                '222' => (string) $gln
            ]
        ],
        'DicOfDT' => [
            '202' => []
        ]
    ];

    foreach ($items as $item) {
        $payload['DicOfDT']['202'][] = [
            '223' => $item->gtin,
            '219' => $item->batchno,
            '214' => $item->serial_number
        ];
    }

    return $payload;
}

function api_call($payload, $headers) {
    $url = 'https://qdttsbe.qtzit.com:10100/api/web'; // The API URL for the product sale
    return make_post_request($url, $headers, $payload);
}


function make_post_request($url, $headers, $data = null) {
    $ch = curl_init();

    // Set the URL for the request
    curl_setopt($ch, CURLOPT_URL, $url);
    
    // Set the request method to POST
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    
    // Explicitly set to POST
    curl_setopt($ch, CURLOPT_POST, true);
    
    // If we have data to send, set POST and include the data
    if ($data) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data)); // Send data as form data
    }

    // Set option to capture the response headers as well
    curl_setopt($ch, CURLOPT_HEADER, true); // To get the headers
    curl_setopt($ch, CURLOPT_NOBODY, false); // To get the body

    // Execute the request and capture the response
    $response = curl_exec($ch);
    $status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    
    curl_close($ch);

    // If the request was successful
    if ($status_code == 200) {
        // Split the headers and body
        $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $response_headers = substr($response, 0, $header_size);
        $response_body = substr($response, $header_size);

        return [
            'body' => $response_body,
            'headers' => $response_headers
        ];
    } else {
        echo "Error: " . $error . "\n"; // Show the cURL error
        echo "HTTP Status Code: " . $status_code . "\n"; // Show HTTP status code
        echo "Response: " . $response . "\n"; // Show the response body
        return null;
    }
}