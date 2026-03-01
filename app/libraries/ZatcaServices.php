<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class ZatcaServices {
    protected $CI;
    protected $base_url;
    protected $api_key;
    protected $api_secret;
    public function __construct($params = array())
    {
        $this->CI =& get_instance();
        $this->CI->load->library('curl');
        
        // Load configuration from a config file
        $this->CI->config->load('zetca', TRUE);
        $this->base_url = isset($params['base_url']) ? $params['base_url'] : $this->CI->config->item('base_url', 'zetca');
        $this->api_key = isset($params['api_key']) ? $params['api_key'] : $this->CI->config->item('api_key', 'zetca');
        $this->api_secret = isset($params['api_secret']) ? $params['api_secret'] : $this->CI->config->item('api_secret', 'zetca');
    }

    protected function send_request($method, $endpoint, $data = NULL, $headers = array())
    {
        $param = "?appKey=" . $this->api_key . "&secretKey=" . $this->api_secret;
        $url = $this->base_url . $endpoint .  $param;

        // Set default headers
        $default_headers = array(
            'Content-Type: application/json'
        );
        $headers = array_merge($default_headers, $headers);

        $this->CI->curl->create($url);
        $this->CI->curl->option(CURLOPT_RETURNTRANSFER, TRUE);
        $this->CI->curl->option(CURLOPT_HTTPHEADER, $headers);

        switch ($method) {
            case 'GET':
                if ($data) {
                    $this->CI->curl->option(CURLOPT_URL, $url . '&' . http_build_query($data));
                }
                break;
            case 'POST':
                $this->CI->curl->option(CURLOPT_POST, TRUE);
                $this->CI->curl->option(CURLOPT_POSTFIELDS, json_encode($data));
                break;
            case 'PUT':
                $this->CI->curl->option(CURLOPT_CUSTOMREQUEST, 'PUT');
                $this->CI->curl->option(CURLOPT_POSTFIELDS, json_encode($data));
                break;
            case 'DELETE':
                $this->CI->curl->option(CURLOPT_CUSTOMREQUEST, 'DELETE');
                if ($data) {
                    $this->CI->curl->option(CURLOPT_POSTFIELDS, json_encode($data));
                }
                break;
        }

        $response = $this->CI->curl->execute();
        $http_code = $this->CI->curl->info['http_code'];

        if ($response === FALSE) {
            return array(
            'status' => $http_code,
            'body' => json_decode($response, TRUE)
            );
            //log_message('error', 'Zetca API request failed: ' . $this->CI->curl->error_string);
             
        }

        return array(
            'status' => $http_code,
            'body' => json_decode($response, TRUE)
        );
    }

    public function get($endpoint, $params = array())
    {
        return $this->send_request('GET', $endpoint, $params);
    }

    public function post($endpoint, $data)
    {
     
        return $this->send_request('POST', $endpoint, $data);
    }

    public function put($endpoint, $data)
    {
        return $this->send_request('PUT', $endpoint, $data);
    }

    public function delete($endpoint, $data = NULL)
    {
        return $this->send_request('DELETE', $endpoint, $data);
    }
}