<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Integrations {
    protected $CI;
    protected $base_url;
    protected $headers;
    protected $body;

    public function __construct() {
        $this->CI =& get_instance();
        $this->CI->load->library('curl');
        
        $this->headers = array(
            'Content-Type: application/json',
            'Accept: application/json'
        );
        $this->body = array();
    }

    public function set_base_url($url) {
        $this->base_url = rtrim($url, '/');
        return $this;
    }

    public function set_headers($headers) {
        $this->headers = array_merge($this->headers, $headers);
        return $this;
    }

    public function set_body($body) {
        $this->body = $body;
        return $this;
    }

   protected function make_request($method, $endpoint, $params = array()) {
        $url = $this->base_url . '/' . ltrim($endpoint, '/');

        $this->CI->curl->create($url);
        $this->CI->curl->option(CURLOPT_RETURNTRANSFER, TRUE);
        $this->CI->curl->option(CURLOPT_HTTPHEADER, $this->headers);

        // Initialize an array to store response headers
        $response_headers = array();

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
                if (!empty($params)) {
                    $this->CI->curl->option(CURLOPT_URL, $url . '?' . http_build_query($params));
                }
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
}