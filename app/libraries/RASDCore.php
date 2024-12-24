<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once(APPPATH.'libraries/Integrations.php');

class RASDCore extends Integrations {
    $token =  null;
    public function __construct() {
        parent::__construct();
    }

    public set_base_url($url){
        $this->set_base_url($url);
    }
  
     /***
      * @param string $UEmail
      * @param string $UPass
      * @returns Response Object
      * API To Authenticate with RASD system.
      */
    public function authenticate($UEmail, $UPass) {
        $headers = array(
            'UEmail' => $UEmail,
            'UPass'  => $UPass,
            'FunctionName' => 'Login',
            'KML' => ''
        );
        $this->set_headers($headers);
        $this->post('/api/web');
        if (isset($response['headers']['Token'])) {
            $this->set_auth_token($response['headers']['Token']);
        } 
        return array( "token" => $response['headers']['Token']);
    }

    /***
     * @param string $gln
     * @param string $gtin
     * @param string $batch_number
     * @param string $serial_number
     * @param string $auth_token
     * API to register pharmacy sale product.
     */

    public patient_pharmacy_sale_product_160($gln, $gtin ,$batch_number, $serial_number, $auth_token){
        $body = array(
            'DicOfDic' =>  array(
                "202"  =>  array(
                    "167" => "",
                    "166"=> "",
                    "168"=> "",
                    "169"=> ""	  
                ),
                "MH" => array(
                    "MN" => "160",
                    "222" => $gln
                )
            ),
            'DicOfDT' => array(
                "202" => array(
                    array(
                    "223" =>  $gtin,
                    "219" : $batch_number,
                    "214":  $serial_number	
                    )
                )
            )

        );
        $this->set_auth_token($auth_token);
        $this->set_body($body);
        return $this->post('api/web');
    }
 
}