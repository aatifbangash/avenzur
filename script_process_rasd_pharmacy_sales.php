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
$start_date = date('Y-m-d', strtotime('-5 days'));
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

if ($result_unprocessed_sales->num_rows > 0) {
    while ($serial_no = $result_unprocessed_sales->fetch_assoc()) {
        $auth_token = authenticate($serial_no['rasd_user'], $serial_no['rasd_pass']);
        echo '<pre>';print_r($auth_token);exit;

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

            if (isset($response_body['DicOfDic']['MR']['TRID']) && $response_body['DicOfDic']['MR']['TRID']) {
                echo 'Script Executed Successfully...';
            } else {
                echo "Error Calling API";
            }
        } 
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

    $response = make_post_request($url, $headers);

    if (isset($response['headers']['token'])) {
        return $response['headers']['token'];
    } else {
        return null;
    }
}

function make_post_request($url, $headers, $data = null) {
    $ch = curl_init();
    
    // Set CURL options
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    
    if ($data) {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data)); // Send JSON data if needed
    }

    // Execute the request and capture the response
    $response = curl_exec($ch);
    $status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    if ($status_code == 200) {
        return json_decode($response, true);  // Decode and return response
    } else {
        echo "Error: " . $error;
        return null;
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