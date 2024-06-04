<?php
// $secret_key = '';
// echo php_sapi_name();

require 'vendor/autoload.php';

$mail = new PHPMailer\PHPMailer\PHPMailer();

// Set up your mailer here as needed


// Assuming you have variables like $orderID, $orderTotal, $productName, etc.
$orderID = 'NSAG30073449315';
$orderTotal = 'SAR 340.95';
$paymentMethod = 'Tabby';
$productName = 'Bluetooth 5.3 Health and Fitness Tracker with Media Storage';
$productPrice = 'SAR 299.00';
$shippingAddress = 'Aleem Nawaz, Al muruj floor 3 flate 31, Riyadh, Saudi Arabia';
$reviewLink = 'http://avenzur.com/review'; // Link to your product review page

//$mail->Body = "
// $message = "<html>
// <head>
//     <style>
//         body { font-family: Arial, sans-serif; }
//         .container { width: 600px; margin: 0 auto; border: 1px solid #f4f4f4 }
//         .header { background: #f4f4f4; padding: 10px; text-align: center; }
//         .content { margin: 20px 0; padding: 25px; }
//         .footer { background: #f4f4f4; padding: 10px; text-align: center; }
//         .button { padding: 10px 20px; color: #fff; background: #007bff; text-decoration: none; }
//         .info { margin-bottom: 10px; }
//     </style>
// </head>
// <body>
//     <div class='container'>
//         <div class='header'>
//             <h2>Order Status</h2>
//         </div>
//         <div class='content'>
//             <p>Dear Hala Aleem,</p>
//             <p>Your order <strong>$orderID</strong> is on the way. We hope you enjoy your purchase. Click on the review button below to let us know how we can deliver better products and services.</p>
            
//             <table class='info'>
//                 <tr><td>Order No:</td><td>$orderID</td></tr>
//                 <tr><td>Order Total:</td><td>$orderTotal</td></tr>
//                 <tr><td>Payment:</td><td>$paymentMethod</td></tr>
//             </table>

//             <div class='footer'>
//                 <p>$productName - $productPrice</p>
//                 <p><strong>SHIPPING ADDRESS</strong></p>
//                 <p>$shippingAddress</p>
//                 <a href='$reviewLink' class='button'>REVIEW ALL ITEMS</a>
//             </div>
//         </div>
//     </div>
// </body>
// </html>
// ";

// $mail->Body =  '<!DOCTYPE html>
// <html lang="en">
// <head>
// <meta charset="UTF-8">
// <meta name="viewport" content="width=device-width, initial-scale=1.0">
// <title>Email Template</title>
// <style>
//     body {
//         font-family: Arial, sans-serif;
//         background-color: #ffffff;
//         margin: 0;
//         padding: 0;
//     }
//     .container {
//         width: 100%;
//         max-width: 600px;
//         margin: auto;
//         background-color: #fff;
        
//         border: 1px solid #f8f9fb; 
//     }
//     .header {
//         background-color: #f8f9fb;
//         padding: 10px;
//         text-align: center;
//     }
//     .main-content {
//         padding: 20px;
//         text-align: left;
//     }
//     .footer {
//         font-size: 12px;
//         text-align: left;
//         background-color: #f8f9fb;
//         padding: 20px;
//     }
//     .button {
//         background-color: #008000;
//         color: white;
//         padding: 10px;
//         text-decoration: none;
//         display: inline-block;
//         margin: 15px 0;
//         color: #fff !important;
//     }
//     .row {
//         display: flex;
//         justify-content: space-between;
//         margin-bottom: 10px;
//         background-color: #f8f9fb;
//       }
//       .column {
//         padding: 10px;
//         width: 48%; /* Slightly less than half to account for padding */
//         box-sizing: border-box;
//       }
//       body p {
//         font-size: 12px;
//       }
//       .product-row {
//         display: flex;
//         justify-content: space-between;
//         align-items: center;
//         border-bottom: 1px solid #ccc;
//         padding-bottom: 10px;
//         margin-bottom: 10px;
//       }
//       .product-image {
//         width: 30%;
//       }
//       .product-description {
//         width: 50%;
//       }
//       .product-price {
//         width: 20%;
//         text-align: right;
//       }
//       .progress-bar {
//         width: 100%; /* Full width */
//         background-color: #ddd; /* Light grey background */
//         margin-bottom: 20px; /* Spacing below the bar */
//       }
//       .progress-bar-fill {
//         width: 100%; /* Could be dynamic based on delivery status */
//         height: 15px; /* Fixed height of the fill */
//         background-color: #4CAF50; /* Green background */
//         text-align: center; /* Center text in the fill */
//         line-height: 15px; /* Center text vertically */
//         color: white; /* Text color */
//       }

// </style>
// </head>
// <body>
// <div class="container">
//     <div class="header">
//         <!-- Header Content -->
//         <p style="text-align: left"><img src="https://avenzur.com/assets/uploads/logos/avenzur-logov2-024.png" alt="Logo" width="100"></p>
//         <h1>Great news!</h1>
//     </div>
//     <div class="main-content">
       
//         <!-- Main Content -->
//         <p>Hala Aleem,</p>
//         <p>An item from your order has been delivered. We hope you enjoy your purchase.</p>
//         <p>Click on the review button below to let us know how we can deliver better products and services.</p>
//         <div class="progress-bar">
//           <div class="progress-bar-fill">Delivered</div>
//         </div>
//         <!-- Order Summary -->
//         <div class="row">
//         <div class="column">
//           <p><strong>Order Summary</strong></p>
//           <p>Order No: NSAG0010625251</p>
//           <p>Order Total: SAR 72.00</p>
//           <p>Payment: Standar</p>
//         </div>
//         <div class="column">
//           <p><strong>Shipping Address</strong></p>
//           <p>Aleem Nawaz</p>
//           <p>Al muniraj floor 3 flat 31, Riyadh, Saudi Arabia</p>
//         </div>
//       </div>

//     <p style="text-align: right"> <a href="link_to_review" class="button">REVIEW THIS PURCHASE</a></p>  
//     <div class=" product-row">
//       <div class="product-image">
//         <img src="https://avenzur.com/assets/uploads/5591ad342846b98a9ffb87772f4610cd.jpg" alt="Product Image"  height="100">
//       </div>
//       <div class="product-description">
//         <p>Musical Baby Doll With swinging Chair for kids Toy Play set</p>
//       </div>
//       <div class="product-price">
//         <p>SAR 38.00</p>
//       </div>
//     </div>

//     <div class=" product-row">
//     <div class="product-image">
//       <img src="https://avenzur.com/assets/uploads/0b6ad17732186af8dbb7be66f583364e.webp" alt="Product Image"  height="100">
//     </div>
//     <div class="product-description">
//       <p>Baby doll</p>
//       <p>Musical Baby Doll With swinging Chair for kids Toy Play set</p>
//       <p>Quantity: 1</p>
//     </div>
//     <div class="product-price">
//       <p>SAR 38.00</p>
//     </div>
//   </div>


//     </div>
//     <div class="footer">
//         <!-- Footer Content -->
//         <p>You are receiving this email as you register on <a href="avenzor.com">Avenzor.com</a></p>
//         <p><strong>2024 Avenzor E-Commerce</strong> </p>
//     </div>
// </div>
// </body>
// </html>
// ';



// if (!$mail->send()) {
//     echo 'Message could not be sent. Mailer Error: ', $mail->ErrorInfo;
// } else {
//     echo 'Message has been sent';
// }

// exit;


$hostname = "81.208.168.52";
$username =  "remote_user";
$password = 're$Pa1msee$ot_ur';
$database = "retaj";


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
// $mail->isHTML(true); // Set email format to HTML

// $hostname = "localhost";
// $username = "root";
// $password = '';
// $database = "avenzur";

// Create connection
$conn = new mysqli($hostname, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// J&T
$id = 3;
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

/* GET J&T SALES WHICH ARE NOT DELIVERED*/
$stmt = $conn->prepare("SELECT * FROM sma_sales WHERE `courier_id` = '3'
and (courier_order_status != 'Sign scan' or courier_order_status is null) and payment_status = 'paid' and sale_status = 'completed' ");
//$stmt->bind_param("i", $id); // 'i' specifies the parameter type is integer

$stmt->execute();

$result_sales = $stmt->get_result();
//echo $result_sales->num_rows;

if ($result_sales->num_rows > 0) {
    // Fetch associative array for the row
    $orderIds = array();
    $messageBody = '';
    while ($sale = $result_sales->fetch_assoc()) {
        $orderIds[] = $sale['id'];
        $orderResponse = getJTOrders($courier, $sale['id']);
        $billCode = $orderResponse->data[0]->billCode;

        $billResponse = trackJTOrders($courier, $billCode);

        $order_id = $sale['id'];
        $tracking_id = $billCode;
        $tracking_status = $billResponse->data[0]->details[0]->scanType;
        echo $order_id.' $$ '.$tracking_status;

        //$stmt = $conn->prepare("UPDATE sma_sales SET courier_order_tracking_id = ?, courier_order_status = ? WHERE id = ?");
        //$stmt->bind_param("ssi", $tracking_id, $tracking_status, $order_id);
        //if ($stmt->execute() === TRUE) {
        if (1==1) {
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
                $stmt_customer_address = $stmt_customer->get_result();
                $stmt_customer_address = $result_customer->fetch_assoc();
                $stmt_customer_address->close();
                $customer_name = $result_customer_address['firstname'].' '.$result_customer_address['last_name'];
                $customer_address = $result_customer_address['line1'];
            }

            echo $customer_address.'<br />';

            $stmt_items = $conn->prepare("SELECT si.product_name, si.net_unit_price, si.quantity, si.subtotal, p.image FROM sma_sale_items AS si JOIN sma_products AS p ON si.product_id = p.id WHERE si.sale_id = ?");
            $stmt_items->bind_param("i", $order_id);
            $stmt_items->execute();
            //$result_sale_items = $stmt_items->fetch();
            $result_sales_items = $stmt_items->get_result();

            //sending delivered email for status update
            if ($tracking_status == 'Sign scan') { // Assuming 'Sign scan' indicates delivered

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
                        <p>You are receiving this email as you register on <a href="avenzur.com">Avenzur.com</a></p>
                        <p><strong>2024 Avenzur E-Commerce</strong> </p>
                    </div>
                </div>
                </body>
                </html>';

                echo "Cusotmer Email :".$customer_data['email'] ;
                echo '<br>'.$messageBody.'<br>';
            
               
                $mail->Subject = 'Your avenzur order has been delivered!';

                $mail->setFrom('info@avenzur.com', 'Avenzur');
                $mail->addAddress($customer_data['email'] , $customer_name ); // Add a recipient
                $mail->addAddress('fabbas@avenzur.com', 'Faisal Abbas');
                //$mail->addAddress('ama@pharma.com.sa','Dr Amr');

                $mail->Body = $messageBody;


                /*if (!$mail->send()) {
                    echo 'Message could not be sent. Mailer Error: ', $mail->ErrorInfo;
                } else {
                    $stmt = $conn->prepare("UPDATE sma_sales SET courier_order_tracking_id = ?, courier_order_status = ? WHERE id = ?");
                    $stmt->bind_param("ssi", $tracking_id, $tracking_status, $order_id);
                    $stmt->execute() ;
        
                    echo 'Message has been sent';
                }*/

                $mail->clearAddresses(); 
            

            } 
            $stmt_items->close();


          

            //echo "Record updated successfully";
        } else {
            echo "Error updating record: " . $stmt->error;
        }

    }
    $stmt->close();
    // Process your row here
} else {
    echo "0 sales";
    exit;
}

// if(count($orderIds) > 0)
// {
//     $orderResponse = getJTOrders($courier, $orderIds);
//     echo "<pre>";
//     print_r($orderResponse);
// }

exit;




$orderResponse = trackJTOrders($courier);
//var_dump($orderResponse);
//$response = json_decode($orderResponse);
//echo $response;
echo "<pre>";
//print_r($orderResponse);
if (!isset($orderResponse->data[0])) {
    echo "No data found";
    exit;
}

print_r($orderResponse->data[0]);
echo "yes";
exit;

foreach ($data as $key => $order) {
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

function getJTOrders($courier, $orderIds)
{
    $strOrderIds = $orderIds; //implode(",", $orderIds);
    // API endpoint URL
    $url = $courier->url . 'order/getOrders';

    $privateKey = $courier->auth_key;
    $customerCode = $courier->username;
    $pwd = $courier->password;
    $account = $courier->api_account;

    // echo $url;
    // echo '<br>'.$privateKey;
    // echo '<br>'.$customerCode;
    // echo '<br>'.$pwd;
    // echo '<br>'.$account;
    // exit;

    $str = strtoupper($customerCode . md5($pwd . 'jadada236t2')) . $privateKey;

    $body_digest = base64_encode(pack('H*', strtoupper(md5($str))));

    $bizContent = '{
        "command": "1",
        "serialNumber": [' . $strOrderIds . '],
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

    // echo $result;
}

function trackJTOrders($courier, $billCode)
{

    // Test API endpoint URL
    //$url = $courier->url . 'logistics/trace?uuid=3c201038f68747128c8a49c793747a02';
    $url = $courier->url . 'logistics/trace';

    $privateKey = $courier->auth_key;
    $customerCode = $courier->username;
    $pwd = $courier->password;
    $account = $courier->api_account;

    // echo $url;
    // echo '<br>'.$privateKey;
    // echo '<br>'.$customerCode;
    // echo '<br>'.$pwd;
    // echo '<br>'.$account;
    // exit;

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
    //echo $result;
}



function get_post_data($customerCode, $pwd, $key, $waybillinfo)
{

    $postdate = json_decode($waybillinfo, true);
    $postdate['customerCode'] = $customerCode;
    $postdate['digest'] = get_content_digest($customerCode, $pwd, $key);

    return json_encode($postdate);
}

function get_content_digest($customerCode, $pwd, $key)
{
    $str = strtoupper($customerCode . md5($pwd . 'jadada236t2')) . $key;

    return base64_encode(pack('H*', strtoupper(md5($str))));
}

function get_header_digest($post, $key)
{
    $digest = base64_encode(pack('H*', strtoupper(md5($post . $key))));
    return $digest;
}