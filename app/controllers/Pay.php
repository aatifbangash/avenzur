<?php

use Stripe\Charge;
use Stripe\Stripe;

defined('BASEPATH') or exit('No direct script access allowed');

class Pay extends MY_Shop_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('pay_model');
        $this->load->model('shop_model');
    }

    public function index()
    {
        if (!SHOP) {
            redirect('admin');
        }
        redirect();
    }
    
     // Function to get the user IP address
          public function getclientIP() {
          $ipaddress = '';
          if (isset($_SERVER['HTTP_CLIENT_IP']))
              $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
          else if(isset($_SERVER['HTTP_X_FORWARDED_FOR']))
              $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
          else if(isset($_SERVER['HTTP_X_FORWARDED']))
              $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
          else if(isset($_SERVER['HTTP_X_CLUSTER_CLIENT_IP']))
              $ipaddress = $_SERVER['HTTP_X_CLUSTER_CLIENT_IP'];
          else if(isset($_SERVER['HTTP_FORWARDED_FOR']))
              $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
          else if(isset($_SERVER['HTTP_FORWARDED']))
              $ipaddress = $_SERVER['HTTP_FORWARDED'];
          else if(isset($_SERVER['REMOTE_ADDR']))
              $ipaddress = $_SERVER['REMOTE_ADDR'];
          else
              $ipaddress = 'UNKNOWN';
          return $ipaddress;
        }
        
        /*
            * Generate hash from request 
            * Inputs: request parameters and authentication token
            * Output: generate secure hashcode based on there parameters using sha256
            */
            public function setSecureHash($data, $authenticationToken)
            {

                //Sort result alphabatically
                ksort($data);

                // start the string with the authentication token
                $hashString = $authenticationToken;

                foreach ($data as $key => $value) {
                    $hashString .= $value;
                }

                $hashString . chr(10);

                //Generate SecureHash with SHA256
                $secureHash = hash('sha256', $hashString, false);

                return $secureHash;
            }
            
    public function directpayRefund($id,$refund_id)
    {
         $dp = $this->pay_model->getDirectPaySettings();

            if($dp->activation == 1)
            {
                $refundLink = $dp->refund_link;
                $auth_token = $dp->authentication_token;
                $mId = $dp->merchant_id;
                


            }else
            {
                $refundLink = $dp->test_refund_link;
                $auth_token = $dp->test_auth_token;
                $mId = $dp->test_Merchant_id;
              
            }

            $ver = $dp->version;
            $currencyCode = $dp->currencyISOCode;
            $refundMsg = $dp->refund_message_id;

        if ($inv = $this->pay_model->getSaleByID($id)) 
        {
            if ($inv->sale_status == 'completed' && $inv->payment_status == 'paid' )
            {
                 $inquiry = [];
                 
                 $messageId = $refundMsg;//4;
                 $merchantId = $mId;//'DP00000017';
                 $authenticationToken = $auth_token;//'MGQ5YjY4NWRhYjA5ZmQyYjBmZjAzYzE3' ;//'NDc5NGZiMjk2ODJlOGIyZTNlOGFkOGM2';
                 $inquiryURL = $refundLink;//'https://paytest.directpay.sa/SmartRoutePaymentWeb/SRMsgHandler'; //'https://pay.directpay.sa/SmartRoutePaymentWeb/SRMsgHandler';
                 $version = $ver;//'1.0';
                 $transactionId = (int)(microtime(true) * 1000) ;
                 $OriginalTransactionID = $id;
                 $totalAmount = intval(number_format($inv->grand_total, 2,'',''));
                 $currencyCode = $currencyCode;//'682';
                 
                 $inquiry['MessageID'] = $messageId;
                 $inquiry['OriginalTransactionID'] = $OriginalTransactionID;
                 $inquiry["MerchantID"] = $merchantId;
                 $inquiry["Version"] = $version;
                 $inquiry['TransactionID'] = $transactionId ;
                 $inquiry["Amount"] = $totalAmount;
                 $inquiry["CurrencyISOCode"] = $currencyCode;
                 $secureHash = $this->setSecureHash($inquiry, $authenticationToken);
                 
                 $requestData = [
                            "TransactionID" => $transactionId,
                            "OriginalTransactionID" => $OriginalTransactionID,
                            "MerchantID" => $merchantId,
                            "MessageID" => $messageId,
                            "Amount" => $totalAmount,
                            "CurrencyISOCode" => $currencyCode,
                            "Version" => $version,
                            "SecureHash" => $secureHash,
                        ];
                
                $requestData = http_build_query($requestData);       
                $ch = curl_init(); 
                curl_setopt($ch, CURLOPT_URL,$inquiryURL);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $requestData);
                curl_setopt($ch, CURLOPT_HEADER, true);
                $data = curl_exec($ch);  
                $this->sma->log_payment('INFO', 'DirectPay Refund Request', $data);
                
                $responseData = array();
                
                $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
                
                $body = substr($data, $headerSize);
                
                parse_str($body, $responseData);
                //var_dump($responseData);
                $req = 'Response String: ';
                  foreach ($responseData as $key => $value) {
            
                        $value = urlencode(stripslashes($value));
                        $req .= "&$key=$value";
                    }
                $this->sma->log_payment('INFO', 'DirectPay Refund Request', $req);  
                curl_close($ch);
                
                if($responseData['Response_StatusCode'] == '00000')
                {
                    $results = array(
                                "refund_status" => "success",
                                "responseStatusCode" => $responseData['Response_StatusCode'],
                                "responseStatusDescription" => $responseData['Response_StatusDescription'],
                                "responseTransactionId" => $responseData['Response_TransactionID'],
                                "responseOriginalTransactionID" => $responseData['Response_OriginalTransactionID'],
                                "responseMerchantId" => $responseData['Response_MerchantID'],
                                "responseMessageId" => $responseData['Response_MessageID'],
                                "responseAmount" => $responseData['Response_Amount'],
                                "responseCurrencyISOCode" => $responseData['Response_CurrencyISOCode'],
                                "responseSecureHash" => $responseData['Response_SecureHash'],
                                'refund_datetime' => date('Y-m-d H:i:s')
                            );
                 $this->pay_model->updateRefundStatus($refund_id, $results); 
                 $payment = [
                                'date'           => date('Y-m-d H:i:s'),
                                'sale_id'        => $OriginalTransactionID,
                                'reference_no'   => $this->site->getReference('pay'),
                                'amount'         => -1 * abs($totalAmount),
                                'paid_by'        => 'DirectPay',
                                'transaction_id' => $responseData['Response_TransactionID'],
                                'type'           => 'returned',
                                'note'           => $responseData['Response_CurrencyISOCode'] . ' ' . $responseData['Response_Amount'] . ' had been refund for the Sale Reference No ' . $inv->reference_no,
                            ];
                            if ($this->pay_model->addPayment($payment)) {
                                 $this->sma->log_payment('SUCCESS', 'Payment has been refunded Reference #' . $responseData['Response_OriginalTransactionID'] . ' via DirectPay (' . $responseData['Response_TransactionID'] . ').', json_encode($responseData));
                            }
                 
                }else
                {
                    $this->sma->log_payment('ERROR', 'Refund failed Reference #' . $responseData['Response_OriginalTransactionID'] . ' via DirectPay (' . $responseData['Response_TransactionID'] . ').', json_encode($_POST));
                    $this->session->set_flashdata('error', lang('payment_failed'));
                    redirect(site_url('admin/refund'));
                }
            }else
            {
               
                $this->session->set_flashdata('error', lang('payment_failed'));
                redirect(site_url('admin/refund'));
            }
        }else
        {
            $this->session->set_flashdata('error', lang('payment_failed'));
                redirect(site_url('admin/refund'));
        }
    }        

    public function directpay($id)
    {
        $dp = $this->pay_model->getDirectPaySettings();

            if($dp->activation == 1)
            {
                $paymentLink = $dp->payment_link;
                $auth_token = $dp->authentication_token;
                $merchantId = $dp->merchant_id;
                


            }else
            {
                $paymentLink = $dp->test_payment_link;
                $auth_token = $dp->test_auth_token;
                $merchantId = $dp->test_Merchant_id;
              
            }

            $ver = $dp->version;
            $currencyCode = $dp->currencyISOCode;
            $paymentMsg = $dp->payment_message_id;

        if ($inv = $this->pay_model->getSaleByID($id)) {
            //$paypal = $this->pay_model->getPaypalSettings();
            if ((($inv->grand_total - $inv->paid) > 0)) {
                
                
                $hashData = array();
                $finalData = array();
                $tid = (int)(microtime(true) * 1000);
                $trasnid = (string)$tid.(string)$id;
                
                
                //Get data from the configyration page
                $version = $ver;//'1.0';
                $redirectURL = $paymentLink;
                //'https://paytest.directpay.sa/SmartRoutePaymentWeb/SRPayMsgHandler';//'https://pay.directpay.sa/SmartRoutePaymentWeb/SRPayMsgHandler';
                //$directURL = $this->getPaymentURL();
                $authenticationToken = $auth_token;//'MGQ5YjY4NWRhYjA5ZmQyYjBmZjAzYzE3';//$this->getAuthenticationToken();'NDc5NGZiMjk2ODJlOGIyZTNlOGFkOGM2';
                $transactionId = $trasnid;
                $MerchantID = $merchantId;//'DP00000017';//'DP00000018';//$this->getMerchantId();
                $itemId = $id;//$this->getItemId();
                $responseBackURL =urldecode(site_url('pay/RedirectPaymentResponsePage'));
                
                $quantity =$inv->total_items;
                $themeId = '1000000001';
                $currencyCode = $currencyCode;//'682';
                $totalAmount = intval(number_format($inv->grand_total, 2,'',''));
                $channel = 0; //E-Commerce channel in STS
                $messageId = $paymentMsg;//'1'; 
                
                $clientIp = $this->getclientIP();
                $generateToken = "yes";
                $paymentDescription = "Payment From Avenzur";
                $paymentMethod = 1;
                
                //Required parameters to generate hash code 
                $hashData['TransactionID'] = $transactionId;
                $hashData['MerchantID'] = $MerchantID;
                $hashData['Amount'] = $totalAmount;
                $hashData['CurrencyISOCode'] = $currencyCode;
                $hashData['MessageID'] = $messageId;
                $hashData['Quantity'] = $quantity;
                $hashData['Channel'] = $channel;

                //optional parameters to generate hash code
                $hashData['ThemeID'] = $themeId;
                $hashData['ResponseBackURL'] = $responseBackURL;
                $hashData['Version'] = $version;
                $hashData['ItemID'] = $itemId;
                $hashData['PaymentDescription'] = urlencode($paymentDescription);
                $hashData['GenerateToken'] = $generateToken;
                $hashData['PaymentMethod'] = $paymentMethod;

                // prepare Payment Request parameters and Send It to Redirect handle Page
                $finalData["TransactionID"] = $transactionId;
                $finalData["MerchantID"] = $MerchantID;
                $finalData["Amount"] = $totalAmount;
                $finalData["CurrencyISOCode"] = $currencyCode;
                $finalData["MessageID"] = $messageId;
                $finalData["Quantity"] = $quantity;
                $finalData["Channel"] = $channel;
                $finalData["ThemeID"] = $themeId;
                $finalData["ItemID"] = $itemId;
                $finalData["ResponseBackURL"] = $responseBackURL;
                $finalData["Version"] = $version;
                $finalData['PaymentDescription'] = $paymentDescription;
                $finalData['GenerateToken'] = $generateToken;
                
                
                $redirectHandle = site_url('pay/RedirectPaymentResponsePage');
                
                $secureHash = $this->setSecureHash($hashData, $authenticationToken);
                
                 $finalData["RedirectURL"] = $redirectURL;
                 $finalData['PaymentMethod'] = $paymentMethod;
                 $finalData["SecureHash"] = $secureHash;
                 
                 
                            
                            $data["formdata"] = $finalData;
                        
                      
               $this->load->view('blue/directpay', $data);
            }
        }
        //$this->session->set_flashdata('error', lang('sale_x_found'));
        //redirect('/');
    }

    public function test_order(){
        $access_token_json = $this->oto_generate_token();
        $access_token_obj = json_decode($access_token_json);
        $access_token = $access_token_obj->access_token;
        //$access_token = 'eyJhbGciOiJSUzI1NiIsImtpZCI6ImFhMDhlN2M3ODNkYjhjOGFjNGNhNzJhZjdmOWRkN2JiMzk4ZjE2ZGMiLCJ0eXAiOiJKV1QifQ.eyJjb21wYW55SWQiOiIyNDY3MiIsImNsaWVudFR5cGUiOiJGcmVlUGFja2FnZSIsIm1hcmtldFBsYWNlTmFtZSI6Im90b2FwaSIsInVzYWdlTW9kZSI6InJlYWwiLCJzdG9yZU5hbWUiOiJBdmVuenVyIiwidXNlclR5cGUiOiJzYWxlc0NoYW5uZWwiLCJ1c2VySWQiOiIzMTQ4OCIsInNjY0lkIjoiNzkxNyIsImVtYWlsIjoiMjQ2NzItNzkxNy1vdG9hcGlAdHJ5b3RvLmNvbSIsImlzcyI6Imh0dHBzOi8vc2VjdXJldG9rZW4uZ29vZ2xlLmNvbS9vdG8tcmVzdC1hcGkiLCJhdWQiOiJvdG8tcmVzdC1hcGkiLCJhdXRoX3RpbWUiOjE2OTUwNDQxNzksInVzZXJfaWQiOiJNN05TeTlBNE4yWldXbVM5S0dHVUN3WlJiRzAzIiwic3ViIjoiTTdOU3k5QTROMlpXV21TOUtHR1VDd1pSYkcwMyIsImlhdCI6MTY5NTExNTIyNywiZXhwIjoxNjk1MTE4ODI3LCJlbWFpbF92ZXJpZmllZCI6ZmFsc2UsImZpcmViYXNlIjp7ImlkZW50aXRpZXMiOnsiZW1haWwiOlsiMjQ2NzItNzkxNy1vdG9hcGlAdHJ5b3RvLmNvbSJdfSwic2lnbl9pbl9wcm92aWRlciI6InBhc3N3b3JkIn19.YePIt-p_eT5lYtC19EKBRjAvOPkbMR7zW-MajM87pqQEqS3KZDOWoHiXrzfDMgoGOZPvOhBzJkvTqmIL19iG0L_mFKs4Poa1Dq9kXp7-mUVlTbMqG29dKJpkYbQIeXskHT3YVQDI0eI9nzDXYQCCnwbMhKi4UFsuPsndijPmC7T-pU-qIACENTqvgsFkuFM8QLIqjjhAA_mid5_EjfDqF1abi_LtJXhi57VDPs9YO-B-lkxhNPGojiMrw3lub1Fnhn03lXK9_YKL5mVfpGwyS7nGRauHx_bgqzCFqO5Z-S_XTMR_HkP3wo29nMU5YEreU75jAHp9MPP0-Ai5Xs-7xQ","refresh_token":"AMf-vBzJfXBOY4h5wotuOhzNC3eA4loOURyIMTH4LDV3M58nk7S38qZdYsG5v9FoLstJmHidWbDt6lkFVo7-ATfWrGgxJPUXud3rV0lphpMTdeEenuM_9g0NaywT1DqUss5CcL4Z_LASOb3JrELy0cFmX20AVlJxVCy6JepF2tBxjNLYqnJwLaXxVk01HoPIeVb8e-HRDkeK_C8CTfAFEtHyNeHSRRWq2Q';

        $order_json = '{"orderId":"10","ref1":"000000","createShipment":false,"payment_method":"paid","amount":254,"amount_due":0,"shippingAmount":"24.0000","currency":"SAR","orderDate":"20\/09\/2023 10:36","customer":{"name":"Faisal Abbas","email":"faisalabb67@gmail.com","mobile":"+966 541226217","address":"Qurtobah, Riyadh, KSA, Riyadh, Riyadh, SA","district":"","city":"Riyadh","country":"SA","postcode":"40000","lat":"","long":"","refID":"","W3WAddress":""},"items":[{"productId":"43","name":"SULFAD 1GM","price":"230.0000","rowTotal":"230.0000","taxAmount":"0.0000","quantity":"1.0000","serialnumber":"","sku":"PDS004","image":"https:\/\/tododev.xyz\/pharmacy\/assets\/uploads\/dd22fc4600e730f8e5cffb3985990f3c.jpg"}]}';
        // Initialize cURL session
        $ch = curl_init('https://api.tryoto.com/rest/v2/createOrder');

        // Set cURL options
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // Return response as a string
        curl_setopt($ch, CURLOPT_POST, true); // Set the request method to POST
        curl_setopt($ch, CURLOPT_POSTFIELDS, $order_json); // Send data as JSON

        // Set HTTP headers
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Accept: application/json',
            'Content-Type: application/json',
            'Authorization: Bearer ' . $access_token // Include your access token here
        ));

        $response = curl_exec($ch);
        if(curl_errno($ch)) {
            echo 'cURL Error: ' . curl_error($ch);
        }

        curl_close($ch);
        echo $response;
    }
    
    public function get_order_tracking_status(){
        $orderId = '12';

        $access_token_json = $this->oto_generate_token();
        $access_token_obj = json_decode($access_token_json);
        $access_token = $access_token_obj->access_token;

        $data = array(
            'orderId' => $orderId
        );

        $ch = curl_init('https://api.tryoto.com/rest/v2/orderStatus');
        // Set cURL options
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // Return response as a string
        curl_setopt($ch, CURLOPT_POST, true); // Set the request method to POST
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data)); // Send data as JSON

        // Set HTTP headers
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Accept: application/json',
            'Content-Type: application/json',
            'Authorization: Bearer ' . $access_token
        ));

        // Execute the cURL session and store the response in $response
        $response = curl_exec($ch);

        // Check for cURL errors
        if (curl_errno($ch)) {
            echo 'cURL Error: ' . curl_error($ch);
        }

        // Close cURL session
        curl_close($ch);

        // Output the API response
        echo $response; 
    }

    public function get_tracking_history(){
        $orderId = '12';

        $access_token_json = $this->oto_generate_token();
        $access_token_obj = json_decode($access_token_json);
        $access_token = $access_token_obj->access_token;

        $data = array(
            'orderIds' => [$orderId]
        );

        $ch = curl_init('https://api.tryoto.com/rest/v2/orderHistory');
        // Set cURL options
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // Return response as a string
        curl_setopt($ch, CURLOPT_POST, true); // Set the request method to POST
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data)); // Send data as JSON

        // Set HTTP headers
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Accept: application/json',
            'Content-Type: application/json',
            'Authorization: Bearer ' . $access_token
        ));

        // Execute the cURL session and store the response in $response
        $response = curl_exec($ch);

        // Check for cURL errors
        if (curl_errno($ch)) {
            echo 'cURL Error: ' . curl_error($ch);
        }

        // Close cURL session
        curl_close($ch);

        // Output the API response
        echo $response;
    }

    public function create_oto_order($order){
        $access_token_json = $this->oto_generate_token();
        $access_token_obj = json_decode($access_token_json);
        $access_token = $access_token_obj->access_token;
        //$access_token = 'eyJhbGciOiJSUzI1NiIsImtpZCI6ImFhMDhlN2M3ODNkYjhjOGFjNGNhNzJhZjdmOWRkN2JiMzk4ZjE2ZGMiLCJ0eXAiOiJKV1QifQ.eyJjb21wYW55SWQiOiIyNDY3MiIsImNsaWVudFR5cGUiOiJGcmVlUGFja2FnZSIsIm1hcmtldFBsYWNlTmFtZSI6Im90b2FwaSIsInVzYWdlTW9kZSI6InJlYWwiLCJzdG9yZU5hbWUiOiJBdmVuenVyIiwidXNlclR5cGUiOiJzYWxlc0NoYW5uZWwiLCJ1c2VySWQiOiIzMTQ4OCIsInNjY0lkIjoiNzkxNyIsImVtYWlsIjoiMjQ2NzItNzkxNy1vdG9hcGlAdHJ5b3RvLmNvbSIsImlzcyI6Imh0dHBzOi8vc2VjdXJldG9rZW4uZ29vZ2xlLmNvbS9vdG8tcmVzdC1hcGkiLCJhdWQiOiJvdG8tcmVzdC1hcGkiLCJhdXRoX3RpbWUiOjE2OTUwNDQxNzksInVzZXJfaWQiOiJNN05TeTlBNE4yWldXbVM5S0dHVUN3WlJiRzAzIiwic3ViIjoiTTdOU3k5QTROMlpXV21TOUtHR1VDd1pSYkcwMyIsImlhdCI6MTY5NTExNTIyNywiZXhwIjoxNjk1MTE4ODI3LCJlbWFpbF92ZXJpZmllZCI6ZmFsc2UsImZpcmViYXNlIjp7ImlkZW50aXRpZXMiOnsiZW1haWwiOlsiMjQ2NzItNzkxNy1vdG9hcGlAdHJ5b3RvLmNvbSJdfSwic2lnbl9pbl9wcm92aWRlciI6InBhc3N3b3JkIn19.YePIt-p_eT5lYtC19EKBRjAvOPkbMR7zW-MajM87pqQEqS3KZDOWoHiXrzfDMgoGOZPvOhBzJkvTqmIL19iG0L_mFKs4Poa1Dq9kXp7-mUVlTbMqG29dKJpkYbQIeXskHT3YVQDI0eI9nzDXYQCCnwbMhKi4UFsuPsndijPmC7T-pU-qIACENTqvgsFkuFM8QLIqjjhAA_mid5_EjfDqF1abi_LtJXhi57VDPs9YO-B-lkxhNPGojiMrw3lub1Fnhn03lXK9_YKL5mVfpGwyS7nGRauHx_bgqzCFqO5Z-S_XTMR_HkP3wo29nMU5YEreU75jAHp9MPP0-Ai5Xs-7xQ","refresh_token":"AMf-vBzJfXBOY4h5wotuOhzNC3eA4loOURyIMTH4LDV3M58nk7S38qZdYsG5v9FoLstJmHidWbDt6lkFVo7-ATfWrGgxJPUXud3rV0lphpMTdeEenuM_9g0NaywT1DqUss5CcL4Z_LASOb3JrELy0cFmX20AVlJxVCy6JepF2tBxjNLYqnJwLaXxVk01HoPIeVb8e-HRDkeK_C8CTfAFEtHyNeHSRRWq2Q';

        $order_json = json_encode($order);
        // Initialize cURL session
        $ch = curl_init('https://api.tryoto.com/rest/v2/createOrder');

        // Set cURL options
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // Return response as a string
        curl_setopt($ch, CURLOPT_POST, true); // Set the request method to POST
        curl_setopt($ch, CURLOPT_POSTFIELDS, $order_json); // Send data as JSON

        // Set HTTP headers
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Accept: application/json',
            'Content-Type: application/json',
            'Authorization: Bearer ' . $access_token // Include your access token here
        ));

        $response = curl_exec($ch);
        if(curl_errno($ch)) {
            echo 'cURL Error: ' . curl_error($ch);
        }

        curl_close($ch);
    }

    public function oto_generate_token(){
        // API endpoint URL
        $apiUrl = 'https://api.tryoto.com/rest/v2/refreshToken';

        // Your refresh token
        $refreshToken = '_refresh_token_';

        // Data to be sent in the request
        $data = array(
            'refresh_token' => 'AMf-vBzJfXBOY4h5wotuOhzNC3eA4loOURyIMTH4LDV3M58nk7S38qZdYsG5v9FoLstJmHidWbDt6lkFVo7-ATfWrGgxJPUXud3rV0lphpMTdeEenuM_9g0NaywT1DqUss5CcL4Z_LASOb3JrELy0cFmX20AVlJxVCy6JepF2tBxjNLYqnJwLaXxVk01HoPIeVb8e-HRDkeK_C8CTfAFEtHyNeHSRRWq2Q'
        );

        // Initialize cURL session
        $ch = curl_init($apiUrl);

        // Set cURL options
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // Return response as a string
        curl_setopt($ch, CURLOPT_POST, true); // Set the request method to POST
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data)); // Send data as JSON

        // Set HTTP headers
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
        ));

        // Execute the cURL session and store the response in $response
        $response = curl_exec($ch);

        // Check for cURL errors
        if(curl_errno($ch)) {
            echo 'cURL Error: ' . curl_error($ch);
        }

        // Close cURL session
        curl_close($ch);

        // Output the API response
        return $response;
    }
    
    public function RedirectPaymentResponsePage()
    {
        $paypal = $this->pay_model->getPaypalSettings();
        $this->sma->log_payment('INFO', 'DirectPay Payment URLL Called');
        $ipnstatus = false;

        $req = 'cmd=_notify-validate';
        foreach ($_POST as $key => $value) {
            // foreach ($_REQUEST as $key => $value) {
            $value = urlencode(stripslashes($value));
            $req .= "&$key=$value";
        }
        $this->sma->log_payment('INFO', 'DirectPay Payment Request', $req);
        $invoice_no = substr($_POST['Response_TransactionID'],13);
        $response_status = $_POST['Response_StatusCode'];
        
        if($response_status == '00000')
        {
            $amount = $_POST['Response_Amount'] / 100;
            $reference  = $_POST['Response_ApprovalCode'];
            /*if ($_POST['Response_CurrencyISOCode'] == '682') {
                            $amount = $_POST['Response_Amount'];
                        } else {
                            $currency = $this->site->getCurrencyByCode($_POST['Response_CurrencyISOCode']);
                            $amount   = $_POST['Response_Amount'] * (1 / $currency->rate);
                        }
              */          
            if ($inv = $this->pay_model->getSaleByID($invoice_no)) {
                $payment = [
                    'date'           => date('Y-m-d H:i:s'),
                    'sale_id'        => $invoice_no,
                    'reference_no'   => $this->site->getReference('pay'),
                    'amount'         => $amount,
                    'paid_by'        => 'DirectPay',
                    'transaction_id' => $_POST['Response_TransactionID'],
                    'type'           => 'received',
                    'note'           => $_POST['Response_CurrencyISOCode'] . ' ' . $_POST['Response_Amount'] . ' had been paid for the Sale Reference No ' . $inv->reference_no,
                ];
                if ($this->pay_model->addPayment($payment)) {
                    $customer = $this->pay_model->getCompanyByID($inv->customer_id);
                    $address = $this->pay_model->getCompanyAddress($customer->id);
                    $this->pay_model->updateStatus($inv->id, 'completed');
                    $ipnstatus = true;
                    $sale_items = $this->pay_model->getSaleItems($invoice_no);

                    $delivery_country = $address->country;
                    $lowercase_delivery_country = strtolower($delivery_country);

                    if (strpos($lowercase_delivery_country, 'saudi') > -1 || strpos($lowercase_delivery_country, 'ksa') > -1) {
                        echo 'Here in KSA Block...';exit;
                        /* OTO Order Generation Starts */
                        $customer_data = array('name' => $customer->name,
                                            'email' => $customer->email,
                                            'mobile' => $address->phone,
                                            'address' => $address->line1.', '.$address->line2.', '.$address->state.', '.$address->city.', '.$customer->country,
                                            'district' => '',
                                            'city' => $address->city,
                                            'country' => $address->country,
                                            'postcode' => $address->postal_code,
                                            'lat' => '',
                                            'long' => '',
                                            'refID' => '',
                                            'W3WAddress' => ''
                        );

                        $items_data = array();
                        foreach ($sale_items as $sale_item){
                            $items_data[] = array('productId' => $sale_item->product_id,
                                            'name' => $sale_item->product_name,
                                            'price' => $sale_item->net_unit_price,
                                            'rowTotal' => $sale_item->subtotal,
                                            'taxAmount' => $sale_item->item_tax,
                                            'quantity' => $sale_item->quantity,
                                            'serialnumber' => '',
                                            'sku' => $sale_item->product_code,
                                            'image' => get_instance()->config->site_url('assets/uploads/').$sale_item->image
                            );
                        }

                        $order = array(
                            'orderId' => $inv->id,
                            'ref1' => $reference,
                            'createShipment' => false,
                            'payment_method' => 'paid',
                            'amount' => $amount,
                            'amount_due' => 0,
                            'shippingAmount' => $inv->shipping,
                            'currency' => 'SAR',
                            'orderDate' => date('d/m/Y H:i'), // Use the current date and time
                            'customer' => $customer_data,
                            'items' => $items_data
                        );

                        $this->create_oto_order($order);
                        /* OTO Order Generation Ends */
                    }else{
                        echo 'Here in Foreign Block...';exit;
                        /* Shipway Order Generation Ends */

                        $license_key = 'E908g3oR7PP7DG0gZXcRG3x89VO228Ry';
                        $shipway_email = 'braphael@avenzur.com';

                        $token = base64_encode($shipway_email.":".$license_key);
                        $authHeaderString = 'Authorization: Basic ' . $token;

                        // API Endpoint URL
                        $url = 'https://app.shipway.com/api/v2orders';

                        // Request headers
                        $headers = array(
                            $authHeaderString,
                            'Content-Type: application/json'
                        );

                        // Request data
                        $data = array(
                            'order_id' => $inv->id,
                            //'ewaybill' => 'AD767435878734PR',
                            'products' => array(),
                            'discount' => $inv->total_discount,
                            'shipping' => $inv->shipping,
                            'order_total' => $amount,
                            'gift_card_amt' => '',
                            'taxes' => $inv->total_tax,
                            'payment_type' => 'P',
                            'email' => $customer->email,
                            'shipping_address' => $address->line1.', '.$address->line2.', '.$address->state.', '.$address->city.', '.$customer->country,
                            'shipping_address2' => '',
                            'shipping_city' => $address->city,
                            'shipping_state' => $address->state,
                            'shipping_country' => $address->country,
                            'shipping_firstname' => $customer->name,
                            'shipping_lastname' => '',
                            'shipping_phone' => $address->phone,
                            'shipping_zipcode' => $address->postal_code,
                            'shipping_latitude' => '',
                            'shipping_longitude' => '',
                            'order_weight' => '',
                            'box_length' => '20',
                            'box_breadth' => '15',
                            'box_height' => '10',
                            'order_date' => date('Y-m-d h:i:s'),
                        );

                        foreach ($sale_items as $sale_item){
                            $data['products'][] = array(
                                'product' => $sale_item->product_name,
                                'price' => $sale_item->net_unit_price,
                                'product_code' => $sale_item->product_code,
                                'amount' => $sale_item->subtotal,
                                'discount' => $sale_item->product_discount,
                                'tax_rate' => $sale_item->tax,
                                'tax_title' => $sale_item->tax,
                            );
                        }

                        // Initialize cURL session
                        $ch = curl_init();

                        // Set cURL options
                        curl_setopt($ch, CURLOPT_URL, $url);
                        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                        curl_setopt($ch, CURLOPT_POST, 1);
                        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

                        // Execute cURL session
                        $response = curl_exec($ch);

                        // Close cURL session
                        curl_close($ch);

                        /* Shipway Order Generation Ends */
                    }

                    $email = $this->order_received($invoice_no);
                    $this->sma->log_payment('SUCCESS', 'Payment has been made for Sale Reference #' . $reference . ' via DirectPay (' . $_POST['Response_TransactionID'] . ').', json_encode($_POST));
                    $this->session->set_flashdata('message', lang('payment_added'));
                    
                }
            }
        }else {
                $this->sma->log_payment('ERROR', 'Payment failed for Sale Reference #' . $reference . ' via DirectPay (' . $_POST['Response_TransactionID'] . ').', json_encode($_POST));
                $this->session->set_flashdata('error', lang('payment_failed'));
        }

        if ($inv->shop) {
            shop_redirect('orders/' . $inv->id . '/' . ($this->loggedIn ? '' : $inv->hash));
        }

        redirect(SHOP ? '/' : site_url($ipnstatus ? 'notify/payment_success' : 'notify/payment_failed'));
        exit();
    }
    
    public function order_received($id = null, $hash = null)
    {
        if ($inv = $this->shop_model->getOrder(['id' => $id])) {
            $user     = $inv->created_by ? $this->site->getUser($inv->created_by) : null;
            $customer = $this->site->getCompanyByID($inv->customer_id);
            $biller   = $this->site->getCompanyByID($inv->biller_id);
            $this->load->library('parser');
            $parse_data = [
                'reference_number' => $inv->reference_no,
                'contact_person'   => $customer->name,
                'company'          => $customer->company && $customer->company != '-' ? '(' . $customer->company . ')' : '',
                'order_link'       => shop_url('orders/' . $id . '/' . ($this->loggedIn ? '' : $inv->hash)),
                'site_link'        => base_url(),
                'site_name'        => $this->Settings->site_name,
                'logo'             => '<img src="' . base_url() . 'assets/uploads/logos/' . $biller->logo . '" alt="' . ($biller->company && $biller->company != '-' ? $biller->company : $biller->name) . '"/>',
            ];
            $msg     = file_get_contents('./themes/' . $this->Settings->theme . '/admin/views/email_templates/sale.html');
            $message = $this->parser->parse_string($msg, $parse_data);
            $this->load->model('pay_model');
            $paypal   = $this->pay_model->getPaypalSettings();
            $skrill   = $this->pay_model->getSkrillSettings();
            $btn_code = '<div id="payment_buttons" class="text-center margin010">';
            if (!empty($this->shop_settings->bank_details)) {
                $btn_code .= '<div style="width:100%;">' . $this->shop_settings->bank_details . '</div><hr class="divider or">';
            }
            if (!empty($paypal) && $paypal->active == '1' && $inv->grand_total != '0.00') {
                if (trim(strtolower($customer->country)) == $biller->country) {
                    $paypal_fee = $paypal->fixed_charges + ($inv->grand_total * $paypal->extra_charges_my / 100);
                } else {
                    $paypal_fee = $paypal->fixed_charges + ($inv->grand_total * $paypal->extra_charges_other / 100);
                }
                $btn_code .= '<a href="https://www.paypal.com/cgi-bin/webscr?cmd=_xclick&business=' . $paypal->account_email . '&item_name=' . $inv->reference_no . '&item_number=' . $inv->id . '&image_url=' . base_url() . 'assets/uploads/logos/' . $this->Settings->logo . '&amount=' . (($inv->grand_total - $inv->paid) + $paypal_fee) . '&no_shipping=1&no_note=1&currency_code=' . $this->default_currency->code . '&bn=BuyNow&rm=2&return=' . admin_url('sales/view/' . $inv->id) . '&cancel_return=' . admin_url('sales/view/' . $inv->id) . '&notify_url=' . admin_url('payments/paypalipn') . '&custom=' . $inv->reference_no . '__' . ($inv->grand_total - $inv->paid) . '__' . $paypal_fee . '"><img src="' . base_url('assets/images/btn-paypal.png') . '" alt="Pay by PayPal"></a> ';
            }
            if (!empty($skrill) && $skrill->active == '1' && $inv->grand_total != '0.00') {
                if (trim(strtolower($customer->country)) == $biller->country) {
                    $skrill_fee = $skrill->fixed_charges + ($inv->grand_total * $skrill->extra_charges_my / 100);
                } else {
                    $skrill_fee = $skrill->fixed_charges + ($inv->grand_total * $skrill->extra_charges_other / 100);
                }
                $btn_code .= ' <a href="https://www.moneybookers.com/app/payment.pl?method=get&pay_to_email=' . $skrill->account_email . '&language=EN&merchant_fields=item_name,item_number&item_name=' . $inv->reference_no . '&item_number=' . $inv->id . '&logo_url=' . base_url() . 'assets/uploads/logos/' . $this->Settings->logo . '&amount=' . (($inv->grand_total - $inv->paid) + $skrill_fee) . '&return_url=' . admin_url('sales/view/' . $inv->id) . '&cancel_url=' . admin_url('sales/view/' . $inv->id) . '&detail1_description=' . $inv->reference_no . '&detail1_text=Payment for the sale invoice ' . $inv->reference_no . ': ' . $inv->grand_total . '(+ fee: ' . $skrill_fee . ') = ' . $this->sma->formatMoney($inv->grand_total + $skrill_fee) . '&currency=' . $this->default_currency->code . '&status_url=' . admin_url('payments/skrillipn') . '"><img src="' . base_url('assets/images/btn-skrill.png') . '" alt="Pay by Skrill"></a>';
            }

            $btn_code .= '<div class="clearfix"></div></div>';
            $message    = $message . $btn_code;
            $attachment = $this->orders($id, $hash, true, 'S');
            $subject    = lang('new_order_received');
            $sent       = false;
            $error      = false;
            $cc         = [];
            $bcc        = [];
            /*if ($user) {
                $cc[] = $customer->email;
            }
            $cc[]      = $biller->email;*/
            $warehouse = $this->site->getWarehouseByID($inv->warehouse_id);
            /*if ($warehouse->email) {
                $cc[] = $warehouse->email;
            }*/
            try {
                if ($this->sma->send_email(($customer ? $customer->email : $user->email), $subject, $message, null, null, $attachment, $cc, $bcc)) {
                    $this->sma->send_email('ama@pharma.com.sa', 'New Order Generated On Avenzur', $message, null, null, $attachment, $cc, $bcc);
                    delete_files($attachment);
                    $sent = true;
                }
            } catch (Exception $e) {
                $error = $e->getMessage();
            }
            return ['sent' => $sent, 'error' => $error];
        }
    }

    // Customer order/orders page
    public function orders($id = null, $hash = null, $pdf = null, $buffer_save = null)
    {
        $hash = $hash ? $hash : $this->input->get('hash', true);
        if ($id && !$pdf) {
            if ($order = $this->shop_model->getOrder(['id' => $id, 'hash' => $hash])) {
                $this->load->library('inv_qrcode');
                $this->data['inv']         = $order;
                $this->data['rows']        = $this->shop_model->getOrderItems($id);
                $this->data['customer']    = $this->site->getCompanyByID($order->customer_id);
                $this->data['biller']      = $this->site->getCompanyByID($order->biller_id);
                $this->data['address']     = $this->shop_model->getAddressByID($order->address_id);
                $this->data['return_sale'] = $order->return_id ? $this->shop_model->getOrder(['id' => $id]) : null;
                $this->data['return_rows'] = $order->return_id ? $this->shop_model->getOrderItems($order->return_id) : null;
                $this->data['paypal']      = $this->shop_model->getPaypalSettings();
                $this->data['skrill']      = $this->shop_model->getSkrillSettings();
                $this->data['page_title']  = lang('view_order');
                $this->data['page_desc']   = '';

                $this->config->load('payment_gateways');
                $this->data['stripe_secret_key']      = $this->config->item('stripe_secret_key');
                $this->data['stripe_publishable_key'] = $this->config->item('stripe_publishable_key');
                $this->page_construct('pages/view_order', $this->data);
            } else {
                $this->session->set_flashdata('error', lang('access_denied'));
                redirect('/');
            }
        } elseif ($pdf || $this->input->get('download')) {
            $id                          = $pdf ? $id : $this->input->get('download', true);
            $hash                        = $hash ? $hash : $this->input->get('hash', true);
            $order                       = $this->shop_model->getOrder(['id' => $id, 'hash' => $hash]);
            $this->data['inv']           = $order;
            $this->data['rows']          = $this->shop_model->getOrderItems($id);
            $this->data['customer']      = $this->site->getCompanyByID($order->customer_id);
            $this->data['biller']        = $this->site->getCompanyByID($order->biller_id);
            $this->data['address']       = $this->shop_model->getAddressByID($order->address_id);
            $this->data['return_sale']   = $order->return_id ? $this->shop_model->getOrder(['id' => $id]) : null;
            $this->data['return_rows']   = $order->return_id ? $this->shop_model->getOrderItems($order->return_id) : null;
            $this->data['Settings']      = $this->Settings;
            $this->data['shop_settings'] = $this->shop_settings;
            $html                        = $this->load->view($this->Settings->theme . '/shop/views/pages/pdf_invoice', $this->data, true);
            if ($this->input->get('view')) {
                echo $html;
                exit;
            }
            $name = lang('invoice') . '_' . str_replace('/', '_', $order->reference_no) . '.pdf';
            if ($buffer_save) {
                return $this->sma->generate_pdf($html, $name, $buffer_save, $this->data['biller']->invoice_footer);
            }
            $this->sma->generate_pdf($html, $name, false, $this->data['biller']->invoice_footer);
        } elseif (!$id) {
            $page   = $this->input->get('page') ? $this->input->get('page', true) : 1;
            $limit  = 10;
            $offset = ($page * $limit) - $limit;
            $this->load->helper('pagination');
            $total_rows = $this->shop_model->getOrdersCount();
            $this->session->set_userdata('requested_page', $this->uri->uri_string());
            $this->data['error']      = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
            $this->data['orders']     = $this->shop_model->getOrders($limit, $offset);
            $this->data['pagination'] = pagination('shop/orders', $total_rows, $limit);
            $this->data['page_info']  = ['page' => $page, 'total' => ceil($total_rows / $limit)];
            $this->data['page_title'] = lang('my_orders');
            $this->data['page_desc']  = '';
            $this->page_construct('pages/orders', $this->data);
        }
    }

    public function paypal($id)
    {
        if ($inv = $this->pay_model->getSaleByID($id)) {
            $paypal = $this->pay_model->getPaypalSettings();
            if ($paypal->active && (($inv->grand_total - $inv->paid) > 0)) {
                $customer = $this->pay_model->getCompanyByID($inv->customer_id);
                $biller   = $this->pay_model->getCompanyByID($inv->biller_id);
                if (trim(strtolower($customer->country)) == $biller->country) {
                    $paypal_fee = $paypal->fixed_charges + ($inv->grand_total * $paypal->extra_charges_my / 100);
                } else {
                    $paypal_fee = $paypal->fixed_charges + ($inv->grand_total * $paypal->extra_charges_other / 100);
                }
                $data = [
                    'rm'            => 2,
                    'no_note'       => 1,
                    'no_shipping'   => 1,
                    'bn'            => 'BuyNow',
                    'item_number'   => $inv->id,
                    'item_name'     => $inv->reference_no,
                    'return'        => urldecode(site_url('pay/pipn')),
                    'notify_url'    => urldecode(site_url('pay/pipn')),
                    'currency_code' => $this->default_currency->code,
                    'cancel_return' => urldecode(site_url('pay/pipn')),
                    'amount'        => (($inv->grand_total - $inv->paid) + $paypal_fee),
                    'image_url'     => base_url() . 'assets/uploads/logos/' . $this->Settings->logo,
                    'business'      => (DEMO ? 'saleem-facilitator@tecdiary.com' : $paypal->account_email),
                    'custom'        => $inv->reference_no . '__' . ($inv->grand_total - $inv->paid) . '__' . $paypal_fee,
                ];
                $query = http_build_query($data, null, '&');
                redirect('https://www' . (DEMO ? '.sandbox' : '') . '.paypal.com/cgi-bin/webscr?cmd=_xclick&' . $query);
            }
        }
        $this->session->set_flashdata('error', lang('sale_x_found'));
        redirect('/');
    }

    public function pipn()
    {
        $paypal = $this->pay_model->getPaypalSettings();
        $this->sma->log_payment('INFO', 'Paypal IPN called');
        $ipnstatus = false;

        $req = 'cmd=_notify-validate';
        foreach ($_POST as $key => $value) {
            // foreach ($_REQUEST as $key => $value) {
            $value = urlencode(stripslashes($value));
            $req .= "&$key=$value";
        }
        $this->sma->log_payment('INFO', 'Paypal Payment Request', $req);

        $header = "POST /cgi-bin/webscr HTTP/1.1\r\n";
        $header .= "Content-Type: application/x-www-form-urlencoded\r\n";
        $header .= 'Host: www' . (DEMO ? '.sandbox' : '') . ".paypal.com\r\n";
        // $header .= "Host: www.paypal.com\r\n";
        $header .= 'Content-Length: ' . strlen($req) . "\r\n";
        $header .= "Connection: close\r\n\r\n";

        $fp = fsockopen('ssl://www' . (DEMO ? '.sandbox' : '') . '.paypal.com', 443, $errno, $errstr, 30);
        // $fp = fsockopen('ssl://www.paypal.com', 443, $errno, $errstr, 30);

        if (!$fp) {
            $this->sma->log_payment('ERROR', 'Paypal Payment Failed (IPN HTTP ERROR)', $errstr);
            $this->session->set_flashdata('error', lang('payment_failed'));
        } else {
            fputs($fp, $header . $req);
            while (!feof($fp)) {
                $res = fgets($fp, 1024);
                //log_message('error', 'Paypal IPN - fp handler -'.$res);
                if (stripos($res, 'VERIFIED') !== false) {
                    $this->sma->log_payment('INFO', 'Paypal IPN - VERIFIED');

                    // $custom      = explode('__', $_POST['custom']);
                    // $payer_email = $_POST['payer_email'];
                    $invoice_no = $_POST['item_number'] ?? $_POST['item_number1'];
                    $reference  = $_POST['item_name']   ?? $_POST['item_name1'];

                    if (($_POST['payment_status'] == 'Completed' || $_POST['payment_status'] == 'Processed' || $_POST['payment_status'] == 'Pending') && ($_POST['business'] == DEMO ? 'saleem-facilitator@tecdiary.com' : $paypal->account_email)) {
                        if ($_POST['mc_currency'] == $this->Settings->default_currency) {
                            $amount = $_POST['mc_gross'];
                        } else {
                            $currency = $this->site->getCurrencyByCode($_POST['mc_currency']);
                            $amount   = $_POST['mc_gross'] * (1 / $currency->rate);
                        }
                        if ($inv = $this->pay_model->getSaleByID($invoice_no)) {
                            $payment = [
                                'date'           => date('Y-m-d H:i:s'),
                                'sale_id'        => $invoice_no,
                                'reference_no'   => $this->site->getReference('pay'),
                                'amount'         => $amount,
                                'paid_by'        => 'paypal',
                                'transaction_id' => $_POST['txn_id'],
                                'type'           => 'received',
                                'note'           => $_POST['mc_currency'] . ' ' . $_POST['mc_gross'] . ' had been paid for the Sale Reference No ' . $inv->reference_no,
                            ];
                            if ($this->pay_model->addPayment($payment)) {
                                $customer = $this->pay_model->getCompanyByID($inv->customer_id);
                                $this->pay_model->updateStatus($inv->id, 'completed');
                                $this->sma->log_payment('SUCCESS', 'Payment has been made for Sale Reference #' . $reference . ' via Paypal (' . $_POST['txn_id'] . ').', json_encode($_POST));
                                $this->session->set_flashdata('message', lang('payment_added'));
                                $ipnstatus = true;

                                try {
                                    $this->load->library('parser');
                                    $parse_data = [
                                        'reference_number' => $reference,
                                        'contact_person'   => $customer->name,
                                        'company'          => $customer->company,
                                        'site_link'        => base_url(),
                                        'site_name'        => $this->Settings->site_name,
                                        'logo'             => '<img src="' . base_url('assets/uploads/logos/' . $this->Settings->logo) . '" alt="' . $this->Settings->site_name . '"/>',
                                    ];

                                    $msg     = file_get_contents('./themes/' . $this->Settings->theme . '/admin/views/email_templates/payment.html');
                                    $message = $this->parser->parse_string($msg, $parse_data);
                                    $this->sma->log_payment('SUCCESS', 'Payment has been made for Sale Reference #' . $reference . ' via Paypal (' . $_POST['txn_id'] . ').', json_encode($_POST));

                                    $this->sma->send_email($paypal->account_email, 'Payment made for sale ' . $inv->reference_no, $message);
                                } catch (\Exception $e) {
                                    $this->sma->log_payment('ERROR', 'Email Notification Failed: ' . $e->getMessage());
                                }

                                if ($inv->shop) {
                                    $this->load->library('sms');
                                    $this->sms->paymentReceived($inv->id, $payment['reference_no'], $payment['amount']);
                                }
                            }
                        }
                    } else {
                        $this->sma->log_payment('ERROR', 'Payment failed for Sale Reference #' . $reference . ' via Paypal (' . $_POST['txn_id'] . ').', json_encode($_POST));
                        $this->session->set_flashdata('error', lang('payment_failed'));
                    }
                } elseif (stripos($res, 'INVALID') !== false) {
                    $this->sma->log_payment('ERROR', 'INVALID response from Paypal. Payment failed via Paypal.', json_encode($_POST));
                    $this->session->set_flashdata('error', lang('payment_failed'));
                }
            }
            fclose($fp);
        }

        if ($inv->shop) {
            shop_redirect('orders/' . $inv->id . '/' . ($this->loggedIn ? '' : $inv->hash));
        }

        redirect(SHOP ? '/' : site_url($ipnstatus ? 'notify/payment_success' : 'notify/payment_failed'));
        exit();
    }

    public function sipn()
    {
        $skrill = $this->pay_model->getSkrillSettings();
        $this->sma->log_payment('INFO', 'Skrill IPN called', json_encode($_POST));
        $ipnstatus = false;

        if (isset($_POST['merchant_id']) && isset($_POST['md5sig'])) {
            $concatFields = $_POST['merchant_id'] . $_POST['transaction_id'] . strtoupper(md5($skrill->secret_word)) . $_POST['mb_amount'] . $_POST['mb_currency'] . $_POST['status'];

            if (strtoupper(md5($concatFields)) == $_POST['md5sig'] && $_POST['status'] == 2 && $_POST['pay_to_email'] == $skrill->account_email) {
                $invoice_no = $_POST['item_number'];
                $reference  = $_POST['item_name'];
                if ($_POST['mb_currency'] == $this->Settings->default_currency) {
                    $amount = $_POST['mb_amount'];
                } else {
                    $currency = $this->site->getCurrencyByCode($_POST['mb_currency']);
                    $amount   = $_POST['mb_amount'] * (1 / $currency->rate);
                }
                if ($inv = $this->pay_model->getSaleByID($invoice_no)) {
                    $payment = [
                        'date'           => date('Y-m-d H:i:s'),
                        'sale_id'        => $invoice_no,
                        'reference_no'   => $this->site->getReference('pay'),
                        'amount'         => $amount,
                        'paid_by'        => 'skrill',
                        'transaction_id' => $_POST['mb_transaction_id'],
                        'type'           => 'received',
                        'note'           => $_POST['mb_currency'] . ' ' . $_POST['mb_amount'] . ' had been paid for the Sale Reference No ' . $reference,
                    ];
                    if ($this->pay_model->addPayment($payment)) {
                        $customer = $this->site->getCompanyByID($inv->customer_id);
                        $this->pay_model->updateStatus($inv->id, 'completed');

                        $this->load->library('parser');
                        $parse_data = [
                            'reference_number' => $reference,
                            'contact_person'   => $customer->name,
                            'company'          => $customer->company,
                            'site_link'        => base_url(),
                            'site_name'        => $this->Settings->site_name,
                            'logo'             => '<img src="' . base_url('assets/uploads/logos/' . $this->Settings->logo) . '" alt="' . $this->Settings->site_name . '"/>',
                        ];

                        $msg     = file_get_contents('./themes/' . $this->Settings->theme . '/admin/views/email_templates/payment.html');
                        $message = $this->parser->parse_string($msg, $parse_data);
                        $this->sma->log_payment('SUCCESS', 'Payment has been made for Sale Reference #' . $_POST['item_name'] . ' via Skrill (' . $_POST['mb_transaction_id'] . ').', json_encode($_POST));
                        try {
                            $this->sma->send_email($skrill->account_email, 'Payment made for sale ' . $inv->reference_no, $message);
                        } catch (Exception $e) {
                            $this->sma->log_payment('Email Notification Failed: ' . $e->getMessage());
                        }
                        $this->session->set_flashdata('message', lang('payment_added'));
                        $ipnstatus = true;
                        if ($inv->shop) {
                            $this->load->library('sms');
                            $this->sms->paymentReceived($inv->id, $payment['reference_no'], $payment['amount']);
                        }
                    }
                }
            } else {
                $this->sma->log_payment('ERROR', 'Payment failed for via Skrill.', json_encode($_POST));
                $this->session->set_flashdata('error', lang('payment_failed'));
            }
        } else {
            redirect('notify/payment');
        }

        if ($inv->shop) {
            shop_redirect('orders/' . $inv->id . '/' . ($this->loggedIn ? '' : $inv->hash));
        }

        redirect(SHOP ? '/' : site_url($ipnstatus ? 'notify/payment_success' : 'notify/payment_failed'));
        exit();
    }

    public function skrill($id)
    {
        if ($inv = $this->pay_model->getSaleByID($id)) {
            $skrill = $this->pay_model->getSkrillSettings();
            if ($skrill->active && (($inv->grand_total - $inv->paid) > 0)) {
                $customer = $this->pay_model->getCompanyByID($inv->customer_id);
                $biller   = $this->pay_model->getCompanyByID($inv->biller_id);
                if (trim(strtolower($customer->country)) == $biller->country) {
                    $skrill_fee = $skrill->fixed_charges + ($inv->grand_total * $skrill->extra_charges_my / 100);
                } else {
                    $skrill_fee = $skrill->fixed_charges + ($inv->grand_total * $skrill->extra_charges_other / 100);
                }
                redirect('https://www.moneybookers.com/app/payment.pl?method=get&pay_to_email=' . $skrill->account_email . '&language=EN&merchant_fields=item_name,item_number&item_name=' . $inv->reference_no . '&item_number=' . $inv->id . '&logo_url=' . base_url() . 'assets/uploads/logos/' . $this->Settings->logo . '&amount=' . (($inv->grand_total - $inv->paid) + $skrill_fee) . '&return_url=' . shop_url('orders/' . $inv->id) . '&cancel_url=' . site_url('/') . '&detail1_description=' . $inv->reference_no . '&detail1_text=Payment for the sale invoice ' . $inv->reference_no . ': ' . $inv->grand_total . '(+ fee: ' . $skrill_fee . ') = ' . $this->sma->formatMoney($inv->grand_total + $skrill_fee) . '&currency=' . $this->default_currency->code . '&status_url=' . site_url('pay/sipn'));
            }
        }
        $this->session->set_flashdata('error', lang('sale_x_found'));
        redirect('/');
    }

    public function stripe($id = null)
    {
        $stripeToken = $this->input->post('stripeToken');
        $stripeEmail = $this->input->post('stripeEmail');
        if (!$id || !$stripeToken) {
            show_404();
        }

        $this->config->load('payment_gateways');
        $inv         = $this->pay_model->getSaleByID($id);
        $description = lang('sale') . ' ' . lang('no.') . ' ' . $id;
        $grand_total = ($inv->grand_total - $inv->paid);
        $amount      = ($grand_total * 100);
        if ($stripeToken) {
            Stripe::setApiKey($this->config->item('stripe_secret_key'));
            try {
                $charge = Charge::create([
                    'amount'      => $amount,
                    'card'        => $stripeToken,
                    'description' => $description,
                    'currency'    => $this->default_currency->code,
                ]);
                // return $charge;
                if (strtolower($charge->currency) == strtolower($this->default_currency->code)) {
                    $payment = [
                        'date'           => date('Y-m-d H:i:s'),
                        'sale_id'        => $inv->id,
                        'reference_no'   => $this->site->getReference('pay'),
                        'amount'         => ($charge->amount / 100),
                        'paid_by'        => 'stripe',
                        'transaction_id' => $charge->id,
                        'type'           => 'received',
                        'note'           => $charge->currency . ' ' . ($charge->amount / 100) . ' had been paid by Stripe for the Sale Reference No ' . $inv->reference_no,
                    ];
                    if ($this->pay_model->addPayment($payment)) {
                        $customer = $this->pay_model->getCompanyByID($inv->customer_id);
                        $this->pay_model->updateStatus($inv->id, 'completed');
                        $this->site->syncSalePayments($inv->id);

                        $this->load->library('parser');
                        $parse_data = [
                            'reference_number' => $payment['reference_no'],
                            'contact_person'   => $customer->name,
                            'company'          => $customer->company,
                            'site_link'        => base_url(),
                            'site_name'        => $this->Settings->site_name,
                            'logo'             => '<img src="' . base_url('assets/uploads/logos/' . $this->Settings->logo) . '" alt="' . $this->Settings->site_name . '"/>',
                        ];

                        $msg     = file_get_contents('./themes/' . $this->Settings->theme . '/admin/views/email_templates/payment.html');
                        $message = $this->parser->parse_string($msg, $parse_data);
                        $this->sma->log_payment('SUCCESS', 'Payment has been made for Sale Reference #' . $inv->reference_no . ' via Stripe (' . $charge->id . ').', json_encode($_POST));
                        try {
                            $this->sma->send_email($customer->email, 'Payment made for sale ' . $inv->reference_no, $message);
                        } catch (Exception $e) {
                            $this->sma->log_payment('Email Notification Failed: ' . $e->getMessage());
                        }
                        $this->session->set_flashdata('message', lang('payment_added'));
                        $payments_received = true;
                        if ($inv->shop) {
                            $this->load->library('sms');
                            $this->sms->paymentReceived($inv->id, $inv->reference_no, ($charge->amount / 100));
                        }
                    }
                }
            } catch (Exception $e) {
                $this->session->set_flashdata('error', $e->getMessage());
                $this->sma->log_payment('ERROR', 'Payment failed for via Stripe.', json_encode($_POST));
                shop_redirect('orders/' . $inv->id . '/' . ($this->loggedIn ? '' : $inv->hash));
            }
        } else {
            redirect('notify/payment');
        }

        if ($inv->shop) {
            shop_redirect('orders/' . $inv->id . '/' . ($this->loggedIn ? '' : $inv->hash));
        }

        redirect(SHOP ? '/' : site_url($payments_received ? 'notify/payment_success' : 'notify/payment_failed'));
        exit();
    }
    
    
    public function aramexshipment()
    {
        $soapClient = new SoapClient('https://ws.dev.aramex.net/ShippingAPI.V2/Shipping/Service_1_0.svc?wsdl');
            	echo '<pre>';
            	print_r($soapClient->__getFunctions());
            
        	$params = array(
        			'Shipments' => array(
        				'Shipment' => array(
        						'Shipper'	=> array(
        										'Reference1' 	=> 'Ref 111111',
        										'Reference2' 	=> 'Ref 222222',
        										'AccountNumber' => '71449672',
        										'PartyAddress'	=> array(
        											'Line1'					=> 'Test',
        											'Line2' 				=> '',
        											'Line3' 				=> '',
        											'City'					=> 'Riyadh',
        											'StateOrProvinceCode'	=> '',
        											'PostCode'				=> '',
        											'CountryCode'			=> 'SA'
        										),
        										'Contact'		=> array(
        											'Department'			=> '',
        											'PersonName'			=> 'aramex',
        											'Title'					=> '',
        											'CompanyName'			=> 'aramex',
        											'PhoneNumber1'			=> '009625515111',
        											'PhoneNumber1Ext'		=> '',
        											'PhoneNumber2'			=> '',
        											'PhoneNumber2Ext'		=> '',
        											'FaxNumber'				=> '',
        											'CellPhone'				=> '9677956000200',
        											'EmailAddress'			=> 'test@test.com',
        											'Type'					=> ''
        										),
        						),
        												
        						'Consignee'	=> array(
        										'Reference1'	=> 'Ref 333333',
        										'Reference2'	=> 'Ref 444444',
        										'AccountNumber' => '71449672',
        										'PartyAddress'	=> array(
        											'Line1'					=> '15 ABC St',
        											'Line2'					=> '',
        											'Line3'					=> '',
        											'City'					=> 'Riyadh',
        											'StateOrProvinceCode'	=> '',
        											'PostCode'				=> '',
        											'CountryCode'			=> 'SA'
        										),
        										
        										'Contact'		=> array(
        											'Department'			=> '',
        											'PersonName'			=> 'Mazen',
        											'Title'					=> '',
        											'CompanyName'			=> 'Aramex',
        											'PhoneNumber1'			=> '009625515111',
        											'PhoneNumber1Ext'		=> '',
        											'PhoneNumber2'			=> '',
        											'PhoneNumber2Ext'		=> '',
        											'FaxNumber'				=> '',
        											'CellPhone'				=> '9627956000200',
        											'EmailAddress'			=> 'test@test.com',
        											'Type'					=> ''
        										),
        						),
        						
        						'ThirdParty' => array(
        										'Reference1' 	=> '',
        										'Reference2' 	=> '',
        										'AccountNumber' => '',
        										'PartyAddress'	=> array(
        											'Line1'					=> '',
        											'Line2'					=> '',
        											'Line3'					=> '',
        											'City'					=> '',
        											'StateOrProvinceCode'	=> '',
        											'PostCode'				=> '',
        											'CountryCode'			=> ''
        										),
        										'Contact'		=> array(
        											'Department'			=> '',
        											'PersonName'			=> '',
        											'Title'					=> '',
        											'CompanyName'			=> '',
        											'PhoneNumber1'			=> '',
        											'PhoneNumber1Ext'		=> '',
        											'PhoneNumber2'			=> '',
        											'PhoneNumber2Ext'		=> '',
        											'FaxNumber'				=> '',
        											'CellPhone'				=> '',
        											'EmailAddress'			=> '',
        											'Type'					=> ''							
        										),
        						),
        						
        						'Reference1' 				=> 'Shpt 0001',
        						'Reference2' 				=> '',
        						'Reference3' 				=> '',
        						'ForeignHAWB'				=> 'ABC 123456',
        						'TransportType'				=> 0,
        						'ShippingDateTime' 			=> time(),
        						'DueDate'					=> time(),
        						'PickupLocation'			=> 'Reception',
        						'PickupGUID'				=> '',
        						'Comments'					=> 'Shpt 0001',
        						'AccountingInstrcutions' 	=> '',
        						'OperationsInstructions'	=> '',
        						
        						'Details' => array(
        										'Dimensions' => array(
        											'Length'				=> 10,
        											'Width'					=> 10,
        											'Height'				=> 10,
        											'Unit'					=> 'cm',
        											
        										),
        										
        										'ActualWeight' => array(
        											'Value'					=> 0.5,
        											'Unit'					=> 'Kg'
        										),
        										
        										'ProductGroup' 			=> 'EXP',
        										'ProductType'			=> 'PDX',
        										'PaymentType'			=> 'P',
        										'PaymentOptions' 		=> '',
        										'Services'				=> '',
        										'NumberOfPieces'		=> 1,
        										'DescriptionOfGoods' 	=> 'Docs',
        										'GoodsOriginCountry' 	=> 'Jo',
        										
        										'CashOnDeliveryAmount' 	=> array(
        											'Value'					=> 0,
        											'CurrencyCode'			=> ''
        										),
        										
        										'InsuranceAmount'		=> array(
        											'Value'					=> 0,
        											'CurrencyCode'			=> ''
        										),
        										
        										'CollectAmount'			=> array(
        											'Value'					=> 0,
        											'CurrencyCode'			=> ''
        										),
        										
        										'CashAdditionalAmount'	=> array(
        											'Value'					=> 0,
        											'CurrencyCode'			=> ''							
        										),
        										
        										'CashAdditionalAmountDescription' => '',
        										
        										'CustomsValueAmount' => array(
        											'Value'					=> 0,
        											'CurrencyCode'			=> ''								
        										),
        										
        										'Items' 				=> array(
        											
        										)
        						),
        				),
        		),
        		
        			'ClientInfo'  			=> array(
        										'AccountCountryCode'	=> 'SA',
        										'AccountEntity'		 	=> 'RUH',
        										'AccountNumber'		 	=> '71449672',
        										'AccountPin'		 	=> '107806',
        										'UserName'			 	=> 'testingapi@aramex.com',
        										'Password'			 	=> 'R123456789$r',
        										'Version'			 	=> '1.0'
        									),
        
        			'Transaction' 			=> array(
        										'Reference1'			=> '001',
        										'Reference2'			=> '', 
        										'Reference3'			=> '', 
        										'Reference4'			=> '', 
        										'Reference5'			=> '',									
        									),
        			'LabelInfo'				=> array(
        										'ReportID' 				=> 9201,
        										'ReportType'			=> 'URL',
        			),
        	);
        	
        	$params['Shipments']['Shipment']['Details']['Items'][] = array(
        		'PackageType' 	=> 'Box',
        		'Quantity'		=> 1,
        		'Weight'		=> array(
        				'Value'		=> 0.5,
        				'Unit'		=> 'Kg',		
        		),
        		'Comments'		=> 'Docs',
        		'Reference'		=> ''
        	);
        	
        //	print_r($params);
        	
        	/*$url = "https://ws.sbx.aramex.net/ShippingAPI.V2/Shipping/Service_1_0.svc/json/CreateShipments";
        	$ch = curl_init( $url );
                # Setup request to send json via POST.
                $payload = json_encode( $params );
                curl_setopt( $ch, CURLOPT_POSTFIELDS, $payload );
                curl_setopt( $ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
                # Return response instead of printing.
                curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
                # Send request.
                $result = curl_exec($ch);
                curl_close($ch);
                # Print response.
                
                print_r($payload);
                
                echo $result;*/
        	
        	try {
        		$auth_call = $soapClient->CreateShipments($params);
        		echo '<pre>';
        		var_dump((array)$auth_call);
        		$data = json_decode(json_encode($auth_call), true);//(array)$auth_call;
        		echo "------------------------------------------";
        		
        		var_dump($data);
        		
        		echo "Reference#1 ".$data["Transaction"]["Reference1"].'<br>';
        		echo "Reference#1 ".$data["Transaction"]["Reference2"].'<br>';
        		echo "HasErrors: ".$data["HasErrors"]."<br>";
        		echo "Shipments ID: ".$data['Shipments']["ProcessedShipment"]["ID"]."<br>";
        		echo "Shipments Label URL: ".$data['Shipments']["ProcessedShipment"]["ShipmentLabel"]["LabelURL"]."<br>";
        		echo "Shipment Origin: ".$data['Shipments']["ProcessedShipment"]["ShipmentDetails"]["Origin"]."<br>";
        		echo "Shipment Destination: ".$data['Shipments']["ProcessedShipment"]["ShipmentDetails"]["Destination"]."<br>";
        		
        		die();
        	} catch (SoapFault $fault) {
        		die('Error : ' . $fault->faultstring);
        	}
    }
}
