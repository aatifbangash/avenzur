<?php 
// $secret_key = '';
// echo php_sapi_name();
require 'vendor/autoload.php';
$mail = new PHPMailer\PHPMailer\PHPMailer();

$hostname = "81.208.168.52";
$username =  "remote_user";
$password = 're$Pa1msee$ot_ur';
$database = "retaj";

// $hostname = "localhost";
// $username = "root";
// $password = '';
// $database = "avenzur";

$mail->isSMTP();
$mail->Host = 'smtp.gmail.com'; // Specify main and backup SMTP servers
$mail->SMTPAuth = true; // Enable SMTP authentication
$mail->Username = 'info@avenzur.com'; // SMTP username
$mail->Password = 'aheoyqmsowyhclea'; //'bpnzmdrbhwbrxclc'; // SMTP password
$mail->SMTPSecure = 'ssl'; // Enable TLS encryption, `ssl` also accepted
$mail->Port = 465; // TCP port to connect to

// $mail->isSMTP();
// $mail->Host = 'smtp.gmail.com'; // Specify main and backup SMTP servers
// $mail->SMTPAuth = true; // Enable SMTP authentication
// $mail->Username = 'aleemktk@gmail.com'; // SMTP username
// $mail->Password = 'bpnzmdrbhwbrxclc'; //'bpnzmdrbhwbrxclc'; // SMTP password
// $mail->SMTPSecure = 'tls'; // Enable TLS encryption, `ssl` also accepted
// $mail->Port = 587; // TCP port to connect to

$mail->isHTML(true); // Set email format to HTML

// Create connection
$conn = new mysqli($hostname, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Run-X
$id = 1; 
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

$token  = getBearerToken($courier);
$orderResponse = makeOrderRequest($courier->url, $token, '');
$response = json_decode($orderResponse);


/* GET J&T SALES WHICH ARE NOT DELIVERED*/
$stmt = $conn->prepare("SELECT * FROM sma_sales WHERE `courier_id` = '1'
and (courier_order_status != 'Delivered' or courier_order_status is null) and payment_status = 'paid' and sale_status = 'completed' and id > 666 ");

$stmt->execute();
 $result_sales = $stmt->get_result();
 //echo $result_sales->num_rows;
if ($response != '') {
    // Fetch associative array for the row
    $orderIds = array();
    $messageBody = '';
    //print_r($response);
    foreach($response->data->data as $order) {
    //   echo "<pre>";
    //     print_r($order);exit;
    //     $tracking_id = $order->id;
    //     $tracking_status = $order->order_status->en_name;
       
    //     echo "<pre>";
    //     print_r($response);
    //     echo "<br> Sale id: ".$order_id;
    //     echo "<br> Status:". $tracking_status;
        // exit;

       
        // $tracking_id = $billCode;
        // $tracking_status = $billResponse->data[0]->details[0]->scanType;
        // echo $order_id.' $$ '.$tracking_status;

        // $stmt = $conn->prepare("UPDATE sma_sales SET courier_order_tracking_id = ?, courier_order_status = ? WHERE id = ?");
        // $stmt->bind_param("ssi", $tracking_id, $tracking_status, $order_id);
        //if ($stmt->execute() === TRUE) {
        if (isset( $order->order_status->en_name)) {

            $tracking_id = $order->id;
            $tracking_status = $order->order_status->en_name;
            $order_id = $order->order_number;

            $stmt = $conn->prepare("SELECT * FROM sma_sales WHERE `courier_id` = '1'
                and (courier_order_status != 'Delivered' or courier_order_status is null) and payment_status = 'paid' and sale_status = 'completed' and id > 666 and id = ?  ");
              $stmt->bind_param("i", $order_id);
            $stmt->execute();
            $result_sales = $stmt->get_result();
            $sale = $result_sales->fetch_assoc();
            $stmt->close();

            if($sale) {
            //echo 'cusotmerid'.$sale['customer_id'];
            $stmt_customer = $conn->prepare("SELECT * FROM sma_companies WHERE `id` = ?");
            if (!$stmt_customer) {
                die('MySQL prepare error: ' . $conn->error);
            }
            $stmt_customer->bind_param("i", $sale['customer_id']);
            $stmt_customer->execute();
            $result_customer = $stmt_customer->get_result();
            $customer_data = $result_customer->fetch_assoc();
            $stmt_customer->close();

            if($sale['address_id'] == 0) {
                  $customer_name = $customer_data['first_name'].' '.$customer_data['last_name'];
                  $customer_address = $customer_data['address'];

            }else {
                $stmt_customer_address = $conn->prepare("SELECT * FROM sma_addresses WHERE `company_id` = ?");
                $stmt_customer_address->bind_param("i", $order_id);
                $stmt_customer_address->execute();
                $result_customer_address = $stmt_customer_address->get_result();
                $customer_address_data = $result_customer_address->fetch_assoc();
                $stmt_customer_address->close();
                $customer_name = $customer_address_data['firstname'].' '.$customer_address_data['last_name'];
                $customer_address = $customer_address_data['line1'];
            }

            $stmt_items = $conn->prepare("SELECT si.product_name, si.net_unit_price, si.quantity, si.subtotal, p.image FROM sma_sale_items AS si JOIN sma_products AS p ON si.product_id = p.id WHERE si.sale_id = ?");
            $stmt_items->bind_param("i", $order_id);
            $stmt_items->execute();
            //$result_sale_items = $stmt_items->fetch();
            $result_sales_items = $stmt_items->get_result();

            //sending delivered email for status update
            if ($tracking_status == 'Delivered') { // Assuming 'Sign scan' indicates delivered

                $messageBody = '
                <!DOCTYPE html>
                <html lang="en">
                <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <title>Email Template</title>
                <style>
                    body {
                        font-family: Arial, sans-serif;
                        background-color: #ffffff;
                        margin: 0;
                        padding: 0;
                    }
                    .container {
                        width: 100%;
                        max-width: 600px;
                        margin: auto;
                        background-color: #fff;
                        
                        border: 1px solid #f8f9fb; 
                    }
                    .header {
                        background-color: #f8f9fb;
                        padding: 10px;
                        text-align: center;
                    }
                    .main-content {
                        padding: 20px;
                        text-align: left;
                    }
                    .footer {
                        font-size: 12px;
                        text-align: left;
                        background-color: #f8f9fb;
                        padding: 20px;
                    }
                    .button {
                        background-color: #4062B9;
                        color: white;
                        padding: 10px;
                        text-decoration: none;
                        display: inline-block;
                        margin: 15px 0;
                        color: #fff !important;
                    }
                    .row {
                        display: flex;
                        justify-content: space-between;
                        margin-bottom: 10px;
                        background-color: #f8f9fb;
                      }
                      .column {
                        padding: 10px;
                        width: 48%; /* Slightly less than half to account for padding */
                        box-sizing: border-box;
                      }
                      body p {
                        font-size: 12px;
                      }
                      .product-row {
                        display: flex;
                        justify-content: space-between;
                        align-items: center;
                        border-bottom: 1px solid #ccc;
                        padding-bottom: 10px;
                        margin-bottom: 10px;
                      }
                      .product-image {
                        width: 30%;
                      }
                      .product-description {
                        width: 50%;
                      }
                      .product-price {
                        width: 20%;
                        text-align: right;
                      }
                      .progress-bar {
                        width: 100%; /* Full width */
                        background-color: #ddd; /* Light grey background */
                        margin-bottom: 20px; /* Spacing below the bar */
                      }
                      .progress-bar-fill {
                        width: 100%; /* Could be dynamic based on delivery status */
                        height: 18px; /* Fixed height of the fill */
                        background-color: #4CAF50; /* Green background */
                        text-align: center; /* Center text in the fill */
                        line-height: 15px; /* Center text vertically */
                        color: white; /* Text color */
                        font-weight: bold;
                        font-size: 15px;
                      }
                
                </style>
                </head>
                <body>
                <div class="container">
                    <div class="header" style="background-image: url(https://avenzur.com/assets/images/great_news.jpg); background-size: cover; background-position: center; background-repeat: no-repeat; color: white; padding: 20px 0;">
                        <!-- Header Content -->
                        <p style="text-align: left"></p>
                        <h1>&nbsp;</h1>
                    </div>
                    <div class="main-content">
                       
                        <!-- Main Content -->
                        <p>Dear '.$customer_name.',</p>
                        <p>An item(s) from your order has been delivered. We hope you enjoy your purchase.</p>
                        <p>Click on the review button below to let us know how we can deliver better products and services.</p>
                        <div class="progress-bar">
                          <div class="progress-bar-fill">Delivered</div>
                        </div>
                        <!-- Order Summary -->
                        <div class="row">
                        <div class="column">
                          <p><strong>Order Summary</strong></p>
                          <p>Order No: '.$order_id.'</p>
                          <p>Order Total: SAR '.number_format($sale['total'], 2, '.', '').'</p>
                          <p>Payment: DirectPay</p>
                        </div>
                        <div class="column">
                          <p><strong>Shipping Address</strong></p>
                          <p>'.$customer_name.'</p>
                          <p>'.$customer_address.'</p>
                        </div>
                      </div>
                
                    <p style="text-align: right"> <a href="https://avenzur.com/shop/rateAndReview" target="_blank" class="button">REVIEW THIS PURCHASE</a></p> '; 

                    while ($item = $result_sales_items->fetch_assoc()) {
          
                    $messageBody .= '<div class=" product-row">
                      <div class="product-image">
                        <img src="https://avenzur.com/assets/uploads/'.$item['image'].'" alt="Product Image"  height="100" width="100" style="object-fit: contain; margin: 0 auto">
                      </div>
                      <div class="product-description">
                        <p>'.$item['product_name'].'</p>
                        <p>Quantity: '.(int) $item['quantity'].'</p>
                      </div>
                      <div class="product-price">
                        <p>SAR '.number_format($item['subtotal'], 2, '.', '').'</p>
                      </div>
                    </div>';
                    } 
            
                    $messageBody .= '</div>
                    <div class="footer">
                        <!-- Footer Content --> 
                        <p>You are receiving this email as you register on <a href="https://avenzur.com" target="_blank">Avenzur.com</a></p>
                        <p><strong>2024 Avenzur E-Commerce</strong> </p>
                    </div>
                </div>
                </body>
                </html>';

                echo "Cusotmer Email :".$customer_data['email'] ;
                echo '<br>'.$messageBody.'<br>';
            
               
                $mail->Subject = 'Your avenzur order has been delivered!';

                $mail->setFrom('info@avenzur.com', 'Avenzur');
                $mail->addAddress($customer_data['email'] , $customer_name ); 
                
                // Add a recipient
                //$mail->addAddress('braphael@avenzur.com', 'Benoy');

                $mail->Body = $messageBody;


                if (!$mail->send()) {
                    echo 'Message could not be sent. Mailer Error: ', $mail->ErrorInfo;
                } else {
                 
                    echo 'Message has been sent';
                }

                $mail->clearAddresses(); 
            
            } 

            $stmt_update = $conn->prepare("UPDATE sma_sales SET courier_order_tracking_id = ?, courier_order_status = ? WHERE id = ?");
            $stmt_update->bind_param("ssi", $tracking_id, $tracking_status, $order_id);
            $stmt_update->execute() ;


           // $stmt_items->close();

        }else {
            "Order # ".$order_id." does notexist";
        }
          

            //echo "Record updated successfully";
        } else {
            echo "Error updating record: " . $stmt->error;
        }

    }
    // Process your row here
} else {
    echo "0 sales";
    exit;
}

exit;

// echo "<pre>";
// print_r($response->data->data);
if(!isset($response->data->data)) {
    echo "No data found";exit;
}


// $data = $response->data->data;
// echo "<pre>";
//   print_r($data);exit;


foreach($data as $key => $order) {
    // print_r($order);
    // exit;
//    $tracking_id = $order->id;
//    $tracking_status = $order->order_status->en_name;
//    $order_id = $order->order_number;

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

function makeOrderRequest($apiUrl, $token, $order_tracking_id) {
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
