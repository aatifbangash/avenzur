<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Payments extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('pay_model');
    }

    public function index()
    {
        show_404();
    }

    public function paypalipn()
    {
        $this->load->admin_model('sales_model');
        $paypal = $this->sales_model->getPaypalSettings();
        $this->sma->log_payment('Paypal IPN called');

        $req = 'cmd=_notify-validate';
        foreach ($_POST as $key => $value) {
            $value = urlencode(stripslashes($value));
            $req .= "&$key=$value";
        }

        $header = "POST /cgi-bin/webscr HTTP/1.1\r\n";
        $header .= "Content-Type: application/x-www-form-urlencoded\r\n";
        $header .= "Host: www.paypal.com\r\n";  // www.sandbox.paypal.com for a test site
        $header .= 'Content-Length: ' . strlen($req) . "\r\n";
        $header .= "Connection: close\r\n\r\n";

        //$fp = fsockopen ('ssl://www.sandbox.paypal.com', 443, $errno, $errstr, 30);
        $fp = fsockopen('ssl://www.paypal.com', 443, $errno, $errstr, 30);

        if (!$fp) {
            $this->sma->log_payment('Paypal Payment Failed (IPN HTTP ERROR)', $errstr);
            $this->session->set_flashdata('error', lang('payment_failed'));
        } else {
            fputs($fp, $header . $req);
            while (!feof($fp)) {
                $res = fgets($fp, 1024);
                //log_message('error', 'Paypal IPN - fp handler -'.$res);
                if (stripos($res, 'VERIFIED') !== false) {
                    $this->sma->log_payment('Paypal IPN - VERIFIED');

                    $custom      = explode('__', $_POST['custom']);
                    $payer_email = $_POST['payer_email'];

                    if (($_POST['payment_status'] == 'Completed' || $_POST['payment_status'] == 'Processed' || $_POST['payment_status'] == 'Pending') && ($_POST['receiver_email'] == $paypal->account_email) && ($_POST['mc_gross'] == ($custom[1] + $custom[2]))
                    ) {
                        $invoice_no = $_POST['item_number'];
                        $reference  = $_POST['item_name'];
                        if ($_POST['mc_currency'] == $this->Settings->default_currency) {
                            $amount = $_POST['mc_gross'];
                        } else {
                            $currency = $this->site->getCurrencyByCode($_POST['mc_currency']);
                            $amount   = $_POST['mc_gross'] * (1 / $currency->rate);
                        }
                        if ($inv = $this->sales_model->getInvoiceByID($invoice_no)) {
                            $payment = [
                                'date'           => date('Y-m-d H:i:s'),
                                'sale_id'        => $invoice_no,
                                'reference_no'   => $this->site->getReference('pay'),
                                'amount'         => $amount,
                                'paid_by'        => 'paypal',
                                'transaction_id' => $_POST['txn_id'],
                                'type'           => 'received',
                                'note'           => $_POST['mc_currency'] . ' ' . $_POST['mc_gross'] . ' had been paid for the Sale Reference No ' . $reference,
                            ];
                            if ($this->sales_model->addPayment($payment)) {
                                $customer = $this->site->getCompanyByID($inv->customer_id);
                                $this->site->updateReference('pay');

                                $this->load->library('parser');
                                $parse_data = [
                                    'reference_number' => $reference,
                                    'contact_person'   => $customer->name,
                                    'company'          => $customer->company,
                                    'site_link'        => base_url(),
                                    'site_name'        => $this->Settings->site_name,
                                    'logo'             => '<img src="' . base_url() . 'assets/uploads/logos/' . $this->Settings->logo . '" alt="' . $this->Settings->site_name . '"/>',
                                ];
                                $temp_path = is_dir('./themes/' . $this->Settings->theme . '/admin/views/email_templates/');
                                $theme     = $temp_path ? $this->theme : 'default';
                                $msg       = file_get_contents('./themes/' . $theme . '/admin/views/email_templates/payment.html');
                                $message   = $this->parser->parse_string($msg, $parse_data);
                                $this->sma->log_payment('Payment has been made for Sale Reference #' . $_POST['item_name'] . ' via Paypal (' . $_POST['txn_id'] . ').', print_r($_POST, ture));
                                try {
                                    $this->sma->send_email($paypal->account_email, 'Payment has been made via Paypal', $message);
                                } catch (Exception $e) {
                                    $this->sma->log_payment('Email Notification Failed: ' . $e->getMessage());
                                }
                                $this->session->set_flashdata('message', lang('payment_added'));
                            }
                        }
                    } else {
                        $this->sma->log_payment('Payment failed for Sale Reference #' . $reference . ' via Paypal (' . $_POST['txn_id'] . ').', print_r($_POST, ture));
                        $this->session->set_flashdata('error', lang('payment_failed'));
                    }
                } elseif (stripos($res, 'INVALID') !== false) {
                    $this->sma->log_payment('INVALID response from Paypal. Payment failed via Paypal.', print_r($_POST, ture));
                    $this->session->set_flashdata('error', lang('payment_failed'));
                }
            }
            fclose($fp);
        }
        redirect('/');
        exit();
    }

    public function skrillipn()
    {
        $this->load->admin_model('sales_model');
        $skrill = $this->sales_model->getSkrillSettings();
        $this->sma->log_payment('Skrill IPN called');

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
            if ($inv = $this->sales_model->getInvoiceByID($invoice_no)) {
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
                if ($this->sales_model->addPayment($payment)) {
                    $customer = $this->site->getCompanyByID($inv->customer_id);
                    $this->site->updateReference('pay');

                    $this->load->library('parser');
                    $parse_data = [
                        'reference_number' => $reference,
                        'contact_person'   => $customer->name,
                        'company'          => $customer->company,
                        'site_link'        => base_url(),
                        'site_name'        => $this->Settings->site_name,
                        'logo'             => '<img src="' . base_url() . 'assets/uploads/logos/' . $this->Settings->logo . '" alt="' . $this->Settings->site_name . '"/>',
                    ];
                    $temp_path = is_dir('./themes/' . $this->Settings->theme . '/admin/views/email_templates/');
                    $theme     = $temp_path ? $this->theme : 'default';
                    $msg       = file_get_contents('./themes/' . $theme . '/admin/views/email_templates/payment.html');
                    $message   = $this->parser->parse_string($msg, $parse_data);
                    $this->sma->log_payment('Payment has been made for Sale Reference #' . $_POST['item_name'] . ' via Skrill (' . $_POST['mb_transaction_id'] . ').', print_r($_POST, ture));
                    try {
                        $this->sma->send_email($skrill->account_email, 'Payment has been made via Skrill', $message);
                    } catch (Exception $e) {
                        $this->sma->log_payment('Email Notification Failed: ' . $e->getMessage());
                    }
                    $this->session->set_flashdata('message', lang('payment_added'));
                }
            }
        } else {
            $this->sma->log_payment('Payment failed for via Skrill.', print_r($_POST, ture));
            $this->session->set_flashdata('error', lang('payment_failed'));
        }
        redirect('/');
        exit();
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
        if ($inv = $this->pay_model->getSaleByID($id)) 
        {
            if ($inv->sale_status == 'completed' && $inv->payment_status == 'paid' )
            {
                 $inquiry = [];
                 
                 $messageId = 4;
                 $merchantId = 'DP00000017';
                 $authenticationToken = 'MGQ5YjY4NWRhYjA5ZmQyYjBmZjAzYzE3' ;
                 $inquiryURL = 'https://paytest.directpay.sa/SmartRoutePaymentWeb/SRMsgHandler';
                 $version = '1.0';
                 $transactionId = (int)(microtime(true) * 1000) ;
                 $OriginalTransactionID = $id;
                 $totalAmount = intval(number_format($inv->grand_total, 2,'',''));
                 $currencyCode = '682';
                 
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
                            $this->session->set_flashdata('message', lang('payment_added'));
                            redirect(site_url('admin/refund'));
                            
                 
                }else
                {
                    $results = array(
                                "refund_status" => "failed");
                    $this->pay_model->updateRefundStatus($refund_id, $results); 
                    
                    $this->sma->log_payment('ERROR', 'Refund failed Reference #' . $responseData['Response_OriginalTransactionID'] . ' via DirectPay (' . $responseData['Response_TransactionID'] . ').', json_encode($_POST));
                    $this->session->set_flashdata('error', lang('payment_failed'));
                    redirect(site_url('admin/refund'));
                }
            }else
            {
               $results = array(
                                "refund_status" => "failed");
                $this->session->set_flashdata('error', lang('payment_failed'));
                redirect(site_url('admin/refund'));
            }
        }else
        {
            $results = array(
                                "refund_status" => "failed");
            $this->session->set_flashdata('error', lang('payment_failed'));
                redirect(site_url('admin/refund'));
        }
    }        

}
