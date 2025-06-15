<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class RASDCore {
    private $token =  null;
    protected $CI;
    protected $base_url;
    protected $headers;
    protected $body;
    protected $auth_token;
    public function __construct($params = array()) {
        
        $this->CI =& get_instance();
        $this->CI->load->library('curl');
        
        $this->headers =[];
        $this->body = array();
        $this->auth_token = null;
         
         $this->set_base_url('https://qdttsbe.qtzit.com:10100/api/web');
    }

    public function set_auth_token($token) {
        $this->auth_token = $token;
        return $this;
    }

    public function set_base_url($url) {
        $this->base_url = rtrim($url, '/');
        return $this;
    }

    public function set_headers($headers) {
        $this->headers = [];
        $this->headers = array_merge($this->headers, $headers);
          
        return $this;
    }

    public function set_body($body) {
        $this->body = $body;
        return $this;
    }

   protected function make_request($method, $endpoint, $params = array()) {
        $url = $this->base_url . '/' . ltrim($endpoint, '/');
     
       $response_headers = array();
        $this->CI->curl->create($url);
        $this->CI->curl->option(CURLOPT_RETURNTRANSFER, TRUE);
     
         $headers = $this->headers;
        // if ($this->auth_token) {
        //    $headers[] = 'Token: ' . $this->auth_token;
        // }
        echo json_encode($this->headers);
        $this->CI->curl->option(CURLOPT_HTTPHEADER, $headers);
        // Initialize an array to store response headers
        
 
        
        // Set up a callback function to capture headers
        $this->CI->curl->option(CURLOPT_HEADERFUNCTION,
            function($curl, $header) use (&$response_headers) {
                 
                $len = strlen($header);
                
                $header = explode(':', $header, 2);
                if (count($header) < 2) // ignore invalid headers
                    return $len;

                $name = strtolower(trim($header[0]));
                $value = trim($header[1]);                
                $response_headers[$name] = $value;
                
                return $len;
            }
        );

        switch ($method) {
            case 'GET':
                 $this->CI->curl->option(CURLOPT_URL, $url );
                break;
            case 'POST':
                $this->CI->curl->option(CURLOPT_POST, TRUE);
                $this->CI->curl->option(CURLOPT_POSTFIELDS, json_encode($this->body));
                break;
            case 'PUT':
                $this->CI->curl->option(CURLOPT_CUSTOMREQUEST, 'PUT');
                $this->CI->curl->option(CURLOPT_POSTFIELDS, json_encode($this->body));
                break;
            case 'DELETE':
                $this->CI->curl->option(CURLOPT_CUSTOMREQUEST, 'DELETE');
                break;
        }

        $response = $this->CI->curl->execute();
        $http_code = $this->CI->curl->info['http_code'];
        
        return array(
            'status' => $http_code,
            'headers' => $response_headers,
            'body' => json_decode($response, TRUE)
        );
    }

    public function get($endpoint, $params = array()) {
        return $this->make_request('GET', $endpoint, $params);
    }

    public function post($endpoint) {
        return $this->make_request('POST', $endpoint);
    }

    public function put($endpoint) {
        return $this->make_request('PUT', $endpoint);
    }

    public function delete($endpoint) {
        return $this->make_request('DELETE', $endpoint);
    }
  
     /***
      * @param string $UEmail
      * @param string $UPass
      * @returns Response Object
      * API To Authenticate with RASD system.
      */
    public function authenticate($UEmail, $UPass) {
        $headers = array(
            'UEmail :'. $UEmail,
            'UPass :' .$UPass,
            'FunctionName: Login',
            'KML:'. '',
            'Accept :*/*',
            "Accept-Encoding : gzip, deflate, br",
            "Access-Control-Expose-Headers: Token",
            "Access-Control-Expose-Headers: UName",
            "Access-Control-Expose-Headers: UPRID"
        );
            
        $this->set_headers($headers);

        $response = $this->post('');
        echo '<pre>';print_r($response);exit;
        if (isset($response['headers']['token'])) {
            $this->set_auth_token($response['headers']['token']);
            return array( "token" => $response['headers']['token']);

        }else{
        return array( "token" => null);

        } 
    }

    public function dispatch_product_133($body, $auth_token){
         $headers = array(
            'FunctionName:APIReq',
            'Token: '.$auth_token,
            'Accept :*/*',
            "Accept-Encoding : gzip, deflate, br"
            );
        $this->set_headers([]);
        $this->set_headers($headers);
        $this->set_body($body);
        return $this->post('');
    }



    public function accept_dispatch_by_lot($params){
        $ph_user = $params['user'];
        $ph_pass = $params['pass'];
        $body = $params['body'];
        $this->set_base_url('https://qdttsbe.qtzit.com:10100/api/web');
        $auth_response = $this->authenticate($ph_user, $ph_pass);
        if(isset($auth_response['token'])){
            $auth_token = $auth_response['token'];
            $headers = array(
            'FunctionName:APIReq',
            'Token: '.$auth_token,
            'Accept :*/*',
            "Accept-Encoding : gzip, deflate, br"
            );
            $this->set_headers([]);
            $this->set_headers($headers);
            $this->set_body($body);
            return $this->post('');
        }
    }

    public function accept_dispatch_125($params, $auth_token){
        $gln = $params['supplier_gln'];
        $dispatchId = $params['notification_id'];
        $warehouse_gln = $params['warehouse_gln'];
         $headers = array(
            'FunctionName:APIReq',
            'Token: '.$auth_token,
            'Accept :*/*',
            "Accept-Encoding : gzip, deflate, br"
            );
        $this->set_headers([]);
        $this->set_headers($headers);

        $body = [
             "DicOfDic" => 
                [
                    "172" => [
                        "215" =>  $gln,
                        "232" =>  "",
                        "162" => $dispatchId	  
                    ],
                    "MH" => [
                        "MN" => "125",
                        "222" => $warehouse_gln
                    ]
                ],
                "DicOfDT" =>  [
                    "172" => [
                        
                    ]
                ]
            ];
        $this->set_body($body);
        return $this->post('');
    }

    /***
     * @param string $gln
     * @param string $gtin
     * @param string $batch_number
     * @param string $serial_number
     * @param string $auth_token
     * API to register pharmacy sale product.
     */

    public function patient_pharmacy_sale_product_160($body){
      
        // $this->set_auth_token($auth_token);
        $this->set_body($body);
        return $this->post('');
    }
 
}