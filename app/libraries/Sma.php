<?php

defined('BASEPATH') or exit('No direct script access allowed');

/*
 *  ==============================================================================
 *  Author    : Mian Saleem
 *  Email     : saleem@tecdiary.com
 *  For       : Stock Manager Advance
 *  Web       : http://tecdiary.com
 *  ==============================================================================
 */

class Sma
{
    public function __construct()
    {
        require_once 'vendor/autoload.php';
    }

    public function __get($var)
    {
        return get_instance()->$var;
    }

    public function actionPermissions($action = null, $module = null)
    {
        if ($this->Owner || $this->Admin) {
            if ($this->Admin && stripos($action, 'delete') !== false) {
                return false;
            }
            return true;
        } elseif ($this->Customer || $this->Supplier) {
            return false;
        }
        if (!$module) {
            $module = $this->m;
        }
        if (!$action) {
            $action = $this->v;
        }
        //$gp = $this->site->checkPermissions();
        if ($this->GP[$module . '-' . $action] == 1) {
            return true;
        }
        return false;
    }
    
    public function checkPermissionsForRequest($action = null) // $this->sma->checkPermissionsForRequest('truck_registration')
    {
        if($this->GP[$action] == 1){
             return true;
        }
         return false;
    }

    public function analyze_term($term)
    {
        //2111111250008
        $spos = strpos($term, $this->Settings->barcode_separator);
        if ($spos !== false) {
            $st        = explode($this->Settings->barcode_separator, $term);
            $sr        = trim($st[0]);
            $option_id = trim($st[1]);
        } else {

            if(substr($term, 0, 2 ) == "01") //&& substr($term, 16, 2 ) == "17")
            {
               $sr        = substr($term, 2, 14 ); 
            }else{
                
                $sr        = $term;
            }
            
            $option_id = false;
        }
        $barcode = $this->parse_scale_barcode($sr);
        if (!is_array($barcode)) {
            return ['term' => $sr, 'option_id' => $option_id];
        }
        return ['term' => $barcode['item_code'], 'option_id' => $option_id, 'quantity' => $barcode['weight'], 'price' => $barcode['price'], 'strict' => $barcode['strict'] ? ($this->site->getProductByCode($barcode['item_code']) ? false : true) : false];
    }

    public function barcode($text = null, $bcs = 'code128', $height = 74, $stext = 1, $get_be = false, $re = false)
    {
        $drawText = ($stext != 1) ? false : true;
        $this->load->library('tec_barcode', '', 'bc');
        return $this->bc->generate($text, $bcs, $height, $drawText, $get_be, $re);
    }

    public function base64url_decode($data)
    {
        return base64_decode(str_replace(['-', '_'], ['+', '/'], $data));
    }

    public function base64url_encode($data, $pad = null)
    {
        $data = str_replace(['+', '/'], ['-', '_'], base64_encode($data));
        if (!$pad) {
            $data = rtrim($data, '=');
        }
        return $data;
    }

    public function checkPermissions($action = null, $js = null, $module = null)
    {
        if (!$this->actionPermissions($action, $module)) {
            $this->session->set_flashdata('error', lang('access_denied'));
            if ($js) {
                die("<script type='text/javascript'>setTimeout(function(){ window.top.location.href = '" . ($_SERVER['HTTP_REFERER'] ?? site_url('welcome')) . "'; }, 10);</script>");
            }
            redirect($_SERVER['HTTP_REFERER'] ?? 'welcome');
        }
    }

    public function clear_tags($str)
    {
        return htmlentities(
            strip_tags(
                $str,
                '<span><div><a><br><p><b><i><u><img><blockquote><small><ul><ol><li><hr><big><pre><code><strong><em><table><tr><td><th><tbody><thead><tfoot><h3><h4><h5><h6>'
            ),
            ENT_QUOTES | ENT_XHTML | ENT_HTML5,
            'UTF-8'
        );
    }

    public function convertMoney($amount, $format = true, $symbol = true)
    {
        if ($this->Settings->selected_currency != $this->Settings->default_currency) {
            $amount = $this->formatDecimal(($amount * $this->selected_currency->rate), 4);
        }
        return ($format ? $this->formatMoney($amount, $this->selected_currency->symbol) : $amount);
    }

    public function decode_html($str)
    {
        return stripslashes(html_entity_decode($str, ENT_QUOTES | ENT_XHTML | ENT_HTML5, 'UTF-8'));
    }

    public function fld($ldate)
    {
        if ($ldate) {
            $date     = explode(' ', $ldate);
            $jsd      = $this->dateFormats['js_sdate'];
            $inv_date = $date[0];
            $time     = $date[1];
            if ($jsd == 'dd-mm-yyyy' || $jsd == 'dd/mm/yyyy' || $jsd == 'dd.mm.yyyy') {
                $date = substr($inv_date, -4) . '-' . substr($inv_date, 3, 2) . '-' . substr($inv_date, 0, 2) . ' ' . $time;
            } elseif ($jsd == 'mm-dd-yyyy' || $jsd == 'mm/dd/yyyy' || $jsd == 'mm.dd.yyyy') {
                $date = substr($inv_date, -4) . '-' . substr($inv_date, 0, 2) . '-' . substr($inv_date, 3, 2) . ' ' . $time;
            } else {
                $date = $inv_date;
            }
            return $date;
        }
        return '0000-00-00 00:00:00';
    }

    public function formatDecimalFunc($number, $decimals = 2)
    {
        if (!is_numeric($number)) {
            return null;
        }
        $decimals = $this->Settings->decimals;
        // if (!$decimals && $decimals !== 0) {
        //     $decimals = $this->Settings->decimals;
        // }
        $truncated = intval($number * 100) / 100;

    // Now format the number to two decimal places
    // number_format($truncated, 2, '.', '');
    //     echo '<br>'.$number;
    //     echo '<br>'.number_format($number, $decimals, '.', '');
    //    echo '<br>'. number_format($truncated, 2, '.', '');
       return number_format($truncated, 2, '.', '');
       // return number_format($number, $decimals, '.', '');
    }

    public function formatDecimal($number, $decimals = 2)
    {
        return $number;
        if (!is_numeric($number)) {
            return null;
        }
        $decimals = $this->Settings->decimals;
        // if (!$decimals && $decimals !== 0) {
        //     $decimals = $this->Settings->decimals;
        // }
        $truncated = intval($number * 100) / 100;

    // Now format the number to two decimal places
    // number_format($truncated, 2, '.', '');
    //     echo '<br>'.$number;
    //     echo '<br>'.number_format($number, $decimals, '.', '');
    //    echo '<br>'. number_format($truncated, 2, '.', '');
       return number_format($truncated, 2, '.', '');
       // return number_format($number, $decimals, '.', '');
    }

    public function formatMoney($number, $symbol = false)
    {
        if ($symbol !== 'none') {
            $symbol = $symbol ? ' '.$symbol : ' '.$this->Settings->symbol;
        } else {
            
            $symbol = null;
        }
        $symbol .=' ';
        if ($this->Settings->sac) {
            return ((($this->Settings->display_symbol == 1 && $symbol) && $this->Settings->display_symbol != 2) ? $symbol : '') .
            $this->formatSAC($this->formatDecimal($number)) .
            ($this->Settings->display_symbol == 2 ? $symbol : '');
        }
        $decimals = $this->Settings->decimals;
        $ts       = $this->Settings->thousands_sep == '0' ? ' ' : $this->Settings->thousands_sep;
        $ds       = $this->Settings->decimals_sep;
        return ((($this->Settings->display_symbol == 1 && $symbol && $number != 0) && $this->Settings->display_symbol != 2) ? $symbol : '') .
        number_format($number, $decimals, $ds, $ts) .
        ($this->Settings->display_symbol == 2 && $number != 0 ? $symbol : '');
    }

    public function formatNumberold($number, $decimals = null)
    {
        if (!$decimals) {
            $decimals = $this->Settings->decimals;
        }
        if ($this->Settings->sac) {
            return $this->formatSAC($this->formatDecimal($number, $decimals));
        }
        $ts = $this->Settings->thousands_sep == '0' ? ' ' : $this->Settings->thousands_sep;
        $ds = $this->Settings->decimals_sep;
        // echo "number".$number;
        // echo "#decimal:".$decimals;
        return number_format($number, $decimals, $ds, $ts);
    }

    public function formatNumber($number, $decimals = null)
{
    if (!is_numeric($number)) {
        return null;
    }
    $decimals = $this->Settings->decimals;
    // if (!$decimals && $decimals !== 0) {
    //     $decimals = $this->Settings->decimals;
    // }
    //$truncated = intval($number * 100) / 100;

// Now format the number to two decimal places
// number_format($truncated, 2, '.', '');
    
   return number_format($number, $decimals, '.', '');
//    echo '<br>'. number_format($truncated, 2, '.', '');
//    return number_format($truncated, 2, '.', '');
}


    public function formatQuantity($number, $decimals = null)
    {
        if (!$decimals) {
            $decimals = $this->Settings->qty_decimals;
        }
        if ($this->Settings->sac) {
            return $this->formatSAC($this->formatDecimal($number, $decimals));
        }
        $ts = $this->Settings->thousands_sep == '0' ? ' ' : $this->Settings->thousands_sep;
        $ds = $this->Settings->decimals_sep;
        return number_format($number, $decimals, $ds, $ts);
    }

    public function formatQuantityDecimal($number, $decimals = null)
    {
        if (!$decimals) {
            $decimals = $this->Settings->qty_decimals;
        }
        return number_format($number, $decimals, '.', '');
    }

    public function formatSAC($num)
    {
        $pos = strpos((string) $num, '.');
        if ($pos === false) {
            $decimalpart = '00';
        } else {
            $decimalpart = substr($num, $pos + 1, 2);
            $num         = substr($num, 0, $pos);
        }

        if (strlen($num) > 3 & strlen($num) <= 12) {
            $last3digits         = substr($num, -3);
            $numexceptlastdigits = substr($num, 0, -3);
            $formatted           = $this->makecomma($numexceptlastdigits);
            $stringtoreturn      = $formatted . ',' . $last3digits . '.' . $decimalpart;
        } elseif (strlen($num) <= 3) {
            $stringtoreturn = $num . '.' . $decimalpart;
        } elseif (strlen($num) > 12) {
            $stringtoreturn = number_format($num, 2);
        }

        if (substr($stringtoreturn, 0, 2) == '-,') {
            $stringtoreturn = '-' . substr($stringtoreturn, 2);
        }

        return $stringtoreturn;
    }

    public function generateUUIDv4() {
        // $prefix = 'AVZ';  // Your custom prefix
        // $timestamp = time();  // Current timestamp
        // $randomString = substr(md5(uniqid(mt_rand(), true)), 0, 7);  // Generate a random string of 7 characters

        // // Combine them to form the unique code
        // $uniqueCode = $prefix . $timestamp . $randomString;

        $timestamp = microtime(true) * 10000;  
        $randomNumber = mt_rand(100, 999);     
        $uniqueCode = substr($timestamp . $randomNumber, -6);

        return $uniqueCode;
    }

    public function fsd($inv_date)
    {
        if ($inv_date) {
            $jsd = $this->dateFormats['js_sdate'];
            if ($jsd == 'dd-mm-yyyy' || $jsd == 'dd/mm/yyyy' || $jsd == 'dd.mm.yyyy') {
                $date = substr($inv_date, -4) . '-' . substr($inv_date, 3, 2) . '-' . substr($inv_date, 0, 2);
            } elseif ($jsd == 'mm-dd-yyyy' || $jsd == 'mm/dd/yyyy' || $jsd == 'mm.dd.yyyy') {
                $date = substr($inv_date, -4) . '-' . substr($inv_date, 0, 2) . '-' . substr($inv_date, 3, 2);
            } else {
                $date = $inv_date;
            }
            return $date;
        }
        return '0000-00-00';
    }

    public function generate_pdf($content, $name = 'download.pdf', $output_type = null, $footer = null, $margin_bottom = null, $header = null, $margin_top = null, $orientation = 'P')
    {
        if ($this->Settings->pdf_lib == 'dompdf') {
            $this->load->library('tec_dompdf', '', 'pdf');
        } else {
            $this->load->library('tec_mpdf', '', 'pdf');
        }

        return $this->pdf->generate($content, $name, $output_type, $footer, $margin_bottom, $header, $margin_top, $orientation);
    }

    public function getCardBalance($number)
    {
        if ($card = $this->site->getGiftCardByNO($number)) {
            return $card->balance;
        }
        return 0;
    }

    public function hrld($ldate)
    {
        if ($ldate) {
            return date($this->dateFormats['php_ldate'], strtotime($ldate));
        }
        return '0000-00-00 00:00:00';
    }

    public function hrsd($sdate)
    {
        if ($sdate) {
            return date($this->dateFormats['php_sdate'], strtotime($sdate));
        }
        return '0000-00-00';
    }

    public function in_group($check_group, $id = false)
    {
        if (!$this->logged_in()) {
            return false;
        }
        $id || $id = $this->session->userdata('user_id');
        $group     = $this->site->getUserGroup($id);
        if ($group->name === $check_group) {
            return true;
        }
        return false;
    }

    public function isPromo($product)
    {
        if (is_array($product)) {
            $product = json_decode(json_encode($product), false);
        }
        $today = date('Y-m-d');
        
        return !is_null($product->promotion) && 
        $product->promo_price && 
        $product->start_date <= $today && 
        $product->end_date >= $today;
        //return  !is_null($product->promotion) && $product->promotion && $product->promo_price;
        //return $product->promotion && $product->start_date <= $today && $product->end_date >= $today && $product->promo_price;
    }

    public function log_payment($type, $msg, $val = null)
    {
        $this->load->library('logs');
        return (bool) $this->logs->write($type, $msg, $val);
    }

    public function logged_in()
    {
        return (bool) $this->session->userdata('identity');
    }

    public function makecomma($input)
    {
        if (strlen($input) <= 2) {
            return $input;
        }
        $length          = substr($input, 0, strlen($input) - 2);
        $formatted_input = $this->makecomma($length) . ',' . substr($input, -2);
        return $formatted_input;
    }

    public function md($page = false)
    {
        die("<script type='text/javascript'>setTimeout(function(){ window.top.location.href = '" . ($page ? site_url($page) : ($_SERVER['HTTP_REFERER'] ?? 'welcome')) . "'; }, 10);</script>");
    }

    public function paid_opts($paid_by = null, $purchase = false, $empty_opt = false)
    {
        $opts = '';
        if ($empty_opt) {
            $opts .= '<option value="">' . lang('select') . '</option>';
        }
        $opts .= '
        <option value="cash"' . ($paid_by && $paid_by == 'cash' ? ' selected="selected"' : '') . '>' . lang('cash') . '</option>
        <option value="gift_card"' . ($paid_by && $paid_by == 'gift_card' ? ' selected="selected"' : '') . '>' . lang('gift_card') . '</option>
        <option value="CC"' . ($paid_by && $paid_by == 'CC' ? ' selected="selected"' : '') . '>' . lang('CC') . '</option>
        <option value="Cheque"' . ($paid_by && $paid_by == 'Cheque' ? ' selected="selected"' : '') . '>' . lang('cheque') . '</option>
        <option value="other"' . ($paid_by && $paid_by == 'other' ? ' selected="selected"' : '') . '>' . lang('other') . '</option>';
        if (!$purchase) {
            $opts .= '<option value="deposit"' . ($paid_by && $paid_by == 'deposit' ? ' selected="selected"' : '') . '>' . lang('deposit') . '</option>';
        }
        return $opts;
    }

    public function parse_scale_barcode($barcode)
    {
        if (strlen($barcode) == $this->Settings->ws_barcode_chars) {
            $product = $this->site->getProductByCode($barcode);
            if ($product) {
                return $barcode;
            }
            $price  = false;
            $weight = false;
            if ($this->Settings->ws_barcode_type == 'price') {
                try {
                    $price = substr($barcode, $this->Settings->price_start - 1, $this->Settings->price_chars);
                    $price = $this->Settings->price_divide_by ? $price / $this->Settings->price_divide_by : $price;
                } catch (\Exception $e) {
                    $price = 0;
                }
            } else {
                try {
                    $weight = substr($barcode, $this->Settings->weight_start - 1, $this->Settings->weight_chars);
                    $weight = $this->Settings->weight_divide_by ? $weight / $this->Settings->weight_divide_by : $weight;
                } catch (\Exception $e) {
                    $weight = 0;
                }
            }
            $item_code = substr($barcode, $this->Settings->item_code_start - 1, $this->Settings->item_code_chars);

            return ['item_code' => $item_code, 'price' => $price, 'weight' => $weight, 'strict' => true];
        }
        return $barcode;
    }

    public function print_arrays()
    {
        $args = func_get_args();
        echo '<pre>';
        foreach ($args as $arg) {
            print_r($arg);
        }
        echo '</pre>';
        die();
    }

    public function qrcode($type = 'text', $text = 'http://tecdiary.com', $size = 2, $level = 'H', $sq = null, $svg = false)
    {
        if ($type == 'link') {
            $text = urldecode($text);
        }
        $this->load->library('tec_qrcode');
        $svgData = $this->tec_qrcode->generate(['data' => $text]);
        if ($svg) {
            return $svgData;
        }
        return "<img src='data:image/svg+xml;base64," . base64_encode($svgData) . "' alt='{$text}' class='qrimg' width='100' height='100' style='max-width:" . ($size * 40) . 'px;max-height:' . ($size * 40) . "px;'' />";
    }

    public function roundMoney($num, $nearest = 0.05)
    {
        return round($num * (1 / $nearest)) * $nearest;
    }

    public function roundNumber($number, $toref = null)
    {
        switch ($toref) {
            case 1:
                $rn = round($number * 20) / 20;
                break;
            case 2:
                $rn = round($number * 2) / 2;
                break;
            case 3:
                $rn = round($number);
                break;
            case 4:
                $rn = ceil($number);
                break;
            default:
                $rn = $number;
        }
        return $rn;
    }

    public function send_whatsapp_notify($receiver_number, $variable){
        $service = $this->site->getSMSServiceByName('unifonic-whatsapp');
        $publicId = $service->api_key;
        $secret = $service->api_secret;

        $whatsappApiUrl = 'https://apis.unifonic.com/v1/messages';

        $payload = [
            "recipient" => [
                "contact" => $receiver_number,
                "channel" => "whatsapp"
            ],
            "content" => [
                "type" => "template",
                "name" => "otp_whatsapp",
                "language" => ["code" => "en"],
                "components" => [
                    [
                        "type" => "body",
                        "parameters" => [
                            [
                                "type" => "text",
                                "text" => (string) $variable
                            ]
                        ]
                    ],
                    [
                        "type" => "options",
                        "parameters" => [
                            [
                                "value" => (string) $variable,
                                "subType" => "url",
                                "index" => 0
                            ]
                        ]
                    ]
                ]
            ]
        ];

        $headers = [
            'PublicId: ' . $publicId,
            'Secret: ' . $secret,
            'Content-Type: application/json'
        ];

        // Initialize cURL session
        $ch = curl_init($whatsappApiUrl);

        // Set cURL options
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        // Execute cURL session and get the response
        $response = curl_exec($ch);

        // Check for cURL errors
        if (curl_errno($ch)) {
            echo 'Curl error: ' . curl_error($ch);
        }

        // Close cURL session
        curl_close($ch);

        return $response;
    }

    public function whatsapp_order_confirmation($receiver_number, $order_number, $invoice_url){
        $service = $this->site->getSMSServiceByName('unifonic-whatsapp');
        $publicId = $service->api_key;
        $secret = $service->api_secret;

        $whatsappApiUrl = 'https://apis.unifonic.com/v1/messages';
        if (strpos($receiver_number, '+966') === false) {
            $receiver_number = '+966' . $receiver_number;
        }

        //$receiver_number = '+923469122590';

        $payload = [
            "recipient" => [
                "contact" => $receiver_number,
                "channel" => "whatsapp"
            ],
            "content" => [
                "type" => "template",
                "name" => "order_confirmation",
                "language" => ["code" => "en"],
                "components" => [
                    [
                        "type" => "body",
                        "parameters" => [
                            [
                                "type" => "text",
                                "text" => (string) $order_number
                            ],
                            [
                                "type" => "text",
                                "text" => (string) $invoice_url
                            ]
                        ]
                    ]
                ]
            ]
        ];

        $headers = [
            'PublicId: ' . $publicId,
            'Secret: ' . $secret,
            'Content-Type: application/json'
        ];

        // Initialize cURL session
        $ch = curl_init($whatsappApiUrl);

        // Set cURL options
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        // Execute cURL session and get the response
        $response = curl_exec($ch);

        // Check for cURL errors
        if (curl_errno($ch)) {
            echo 'Curl error: ' . curl_error($ch);
        }

        // Close cURL session
        curl_close($ch);

        return $response;
    }

    public function send_whatsapp_msg($receiver_number, $variable){
        $service = $this->site->getSMSServiceByName('unifonic-whatsapp');
        $publicId = $service->api_key;
        $secret = $service->api_secret;

        $whatsappApiUrl = 'https://apis.unifonic.com/v1/messages';
        if (strpos($receiver_number, '+966') === false) {
            $receiver_number = '+966' . $receiver_number;
        }

        //$receiver_number = '+923469122590';

        $payload = [
            "recipient" => [
                "contact" => $receiver_number,
                "channel" => "whatsapp"
            ],
            "content" => [
                "type" => "template",
                "name" => "otp_whatsapp",
                "language" => ["code" => "en"],
                "components" => [
                    [
                        "type" => "body",
                        "parameters" => [
                            [
                                "type" => "text",
                                "text" => (string) $variable
                            ]
                        ]
                    ],
                    [
                        "type" => "options",
                        "parameters" => [
                            [
                                "value" => (string) $variable,
                                "subType" => "url",
                                "index" => 0
                            ]
                        ]
                    ]
                ]
            ]
        ];

        $headers = [
            'PublicId: ' . $publicId,
            'Secret: ' . $secret,
            'Content-Type: application/json'
        ];

        // Initialize cURL session
        $ch = curl_init($whatsappApiUrl);

        // Set cURL options
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        // Execute cURL session and get the response
        $response = curl_exec($ch);

        // Check for cURL errors
        if (curl_errno($ch)) {
            echo 'Curl error: ' . curl_error($ch);
        }

        // Close cURL session
        curl_close($ch);

        return $response;

    }

    public function send_sms_new($receiver_number, $message){
        $service = $this->site->getSMSServiceByName('4jawaly');
        $app_id = $service->api_key;
        $app_sec = $service->api_secret;
        $app_hash = base64_encode("{$app_id}:{$app_sec}");
        $messages = [
            "messages" => [
                [
                    "text" => $message,
                    "numbers" => ['966'.$receiver_number],
                    "sender" => "PHMC"
                ]
            ]
        ];

        $url = "https://api-sms.4jawaly.com/api/v1/account/area/sms/send";
        $headers = [
            "Accept: application/json",
            "Content-Type: application/json",
            "Authorization: Basic {$app_hash}"
        ];

        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($messages));
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($curl);
        $status_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        $response_json = json_decode($response, true);

        if ($status_code == 200) {
            if (isset($response_json["messages"][0]["err_text"])) {
                return json_encode(['status' => 'error', 'message' => $response_json["messages"][0]["err_text"]]);
                //echo $response_json["messages"][0]["err_text"];
            } else {
                return json_encode(['status' => 'success', 'message' => $response_json["job_id"]]);
            }
        } elseif ($status_code == 400) {
            return json_encode(['status' => 'success', 'message' => $response_json["message"]]);
        } elseif ($status_code == 422) {
            return json_encode(['status' => 'error', 'message' => 'The message is empty']);
        } else {
            return json_encode(['status' => 'success', 'message' => $status_code]);
        }
    }

    public function send_sms($receiver_number, $variable){
        $service = $this->site->getSMSServiceByName('MSEGAT');

        $data = [
            'userName' => 'phmc',
            'numbers' => $receiver_number,
            'userSender' => 'phmc',
            'apiKey' => $service->api_key,
            'msg' => 'Your OTP verification code is '.$variable,
        ];
    
        // Convert the data to JSON format
        $jsonData = json_encode($data);
    
        // Initialize cURL session
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://www.msegat.com/gw/sendsms.php');
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
        ]);
    
        // Capture the cURL response
        $response = curl_exec($ch);
    
        // Check for cURL errors
        if (curl_errno($ch)) {
            // Handle cURL error here
            return ['code' => 'error', 'message' => 'cURL Error: ' . curl_error($ch)];
        }
    
        curl_close($ch);
    
        // Return the captured response
        return json_decode($response, true); // Assuming the response is in JSON format
    }

    public function send_email($to, $subject, $message, $from = null, $from_name = null, $attachment = null, $cc = null, $bcc = null)
    {
        // if (DEMO) {
        //     $this->session->set_flashdata('error', 'Emails are disabled in demo.');
        //     return false;
        // }
        list($user, $domain) = explode('@', $to);
        if ($domain != 'tecdiary.com' || DEMO) {
            $result = false;
            $this->load->library('tec_mail');
            try {
                $result = $this->tec_mail->send_mail($to, $subject, $message, $from, $from_name, $attachment, $cc, $bcc);
            } catch (\Exception $e) {
                $this->session->set_flashdata('error', 'Mail Error: ' . $e->getMessage());
                throw new \Exception($e->getMessage());
            }
            return $result;
        }
        return false;
    }

    public function send_json($data)
    {
        header('Content-Type: application/json');
        die(json_encode($data));
        exit;
    }

    public function setCustomerGroupPrice($price, $customer_group)
    {
        if (!isset($customer_group) || empty($customer_group)) {
            return $price;
        }
        return $this->formatDecimal($price + (($price * $customer_group->percent) / 100));
    }

    public function slug($title, $type = null, $r = 1)
    {
        $this->load->helper('text');
        $slug       = url_title(convert_accented_characters($title), '-', true);
        $check_slug = $this->site->checkSlug($slug, $type);
        if (!empty($check_slug)) {
            $slug = $slug . $r;
            $r++;
            $this->slug($slug, $type, $r);
        }
        return $slug;
    }

    public function unset_data($ud)
    {
        if ($this->session->userdata($ud)) {
            $this->session->unset_userdata($ud);
            return true;
        }
        return false;
    }

    public function unzip($source, $destination = './')
    {
        // @chmod($destination, 0777);
        $zip = new ZipArchive();
        if ($zip->open(str_replace('//', '/', $source)) === true) {
            $zip->extractTo($destination);
            $zip->close();
        }
        // @chmod($destination,0755);

        return true;
    }

    public function update_award_points($total, $customer, $user = null, $scope = null)
    {
        if (!empty($this->Settings->each_spent)) {
            $company = $this->site->getCompanyByID($customer);
            if ($total > 0 || $scope) {
                $points = floor(($total / $this->Settings->each_spent) * $this->Settings->ca_point);
            } else {
                $points = ceil(($total / $this->Settings->each_spent) * $this->Settings->ca_point);
            }
            $total_points = $scope ? $company->award_points - $points : $company->award_points + $points;
            $this->db->update('companies', ['award_points' => $total_points], ['id' => $customer]);
        }
        if ($user && !empty($this->Settings->each_sale) && !$this->Customer && !$this->Supplier) {
            $staff = $this->site->getUser($user);
            if ($total > 0 || $scope) {
                $points = floor(($total / $this->Settings->each_sale) * $this->Settings->sa_point);
            } else {
                $points = ceil(($total / $this->Settings->each_sale) * $this->Settings->sa_point);
            }
            $total_points = $scope ? $staff->award_points - $points : $staff->award_points + $points;
            $this->db->update('users', ['award_points' => $total_points], ['id' => $user]);
        }
        return true;
    }

    public function view_rights($check_id, $js = null)
    {
        if (!$this->Owner && !$this->Admin) {
            if ($check_id != $this->session->userdata('user_id') && !$this->session->userdata('view_right')) {
                $this->session->set_flashdata('warning', $this->data['access_denied']);
                if ($js) {
                    die("<script type='text/javascript'>setTimeout(function(){ window.top.location.href = '" . ($_SERVER['HTTP_REFERER'] ?? 'welcome') . "'; }, 10);</script>");
                }
                redirect($_SERVER['HTTP_REFERER'] ?? 'welcome');
            }
        }
        return true;
    }

    public function zip($source = null, $destination = './', $output_name = 'sma', $limit = 5000)
    {
        if (!$destination || trim($destination) == '') {
            $destination = './';
        }

        $this->_rglobRead($source, $input);
        $maxinput  = count($input);
        $splitinto = (($maxinput / $limit) > round($maxinput / $limit, 0)) ? round($maxinput / $limit, 0) + 1 : round($maxinput / $limit, 0);

        for ($i = 0; $i < $splitinto; $i++) {
            $this->_zip(array_slice($input, ($i * $limit), $limit, true), $i, $destination, $output_name);
        }

        unset($input);
    }

    private function _rglobRead($source, &$array = [])
    {
        if (!$source || trim($source) == '') {
            $source = '.';
        }
        foreach ((array) glob($source . '/*/') as $key => $value) {
            $this->_rglobRead(str_replace('//', '/', $value), $array);
        }
        $hidden_files = glob($source . '.*') and $htaccess = preg_grep('/\.htaccess$/', $hidden_files);
        $files        = array_merge(glob($source . '*.*'), $htaccess);
        foreach ($files as $key => $value) {
            $array[] = str_replace('//', '/', $value);
        }
    }

    private function _zip($array, $part, $destination, $output_name = 'sma')
    {
        $zip = new ZipArchive();
        @mkdir($destination, 0777, true);

        if ($zip->open(str_replace('//', '/', "{$destination}/{$output_name}" . ($part ? '_p' . $part : '') . '.zip'), ZipArchive::CREATE)) {
            foreach ((array) $array as $key => $value) {
                $zip->addFile($value, str_replace(['../', './'], null, $value));
            }
            $zip->close();
        }
    }
}
