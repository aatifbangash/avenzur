<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class SnapchatAPI extends CI_Controller {

    private $client_id = '667f27d7-7cbb-4952-a988-6058bb69e3c2';
    private $client_secret = 'your_client_secret';
    private $redirect_uri = 'https://avenzur.com/callback';
    private $oauth_url = 'https://accounts.snapchat.com/login/oauth2';
    private $access_token = null;

    public function __construct() {
        parent::__construct();
        $this->load->library('curl');
        $this->load->library('session');
    }

    public function authorize() {
        $auth_url = "{$this->oauth_url}/authorize?client_id={$this->client_id}&redirect_uri={$this->redirect_uri}&response_type=code&scope=snapchat-marketing-api";
        redirect($auth_url);
    }

    public function callback() {
        $code = $this->input->get('code');
        $token_response = $this->get_access_token($code);

        if ($token_response) {
            $this->session->set_userdata('access_token', $token_response['access_token']);
            $this->session->set_userdata('refresh_token', $token_response['refresh_token']);
            $this->session->set_userdata('expires_in', $token_response['expires_in']);
            redirect('SnapchatAPI/create_product_feed');
        } else {
            echo 'Failed to get access token';
        }
    }

    private function get_access_token($code) {
        $token_url = "{$this->oauth_url}/access_token";

        $data = array(
            'grant_type' => 'authorization_code',
            'client_id' => $this->client_id,
            'client_secret' => $this->client_secret,
            'code' => $code,
            'redirect_uri' => $this->redirect_uri
        );

        $response = $this->curl->simple_post($token_url, $data);
        return json_decode($response, true);
    }

    private function refresh_token() {
        $refresh_token = $this->session->userdata('refresh_token');
        $token_url = "{$this->oauth_url}/access_token";

        $data = array(
            'grant_type' => 'refresh_token',
            'client_id' => $this->client_id,
            'client_secret' => $this->client_secret,
            'refresh_token' => $refresh_token
        );

        $response = $this->curl->simple_post($token_url, $data);
        return json_decode($response, true);
    }

    public function create_product_feed() {
        $this->access_token = "eyJhbGciOiJIUzI1NiIsImtpZCI6IkNhbnZhc1MyU0hNQUNQcm9kIiwidHlwIjoiSldUIn0.eyJhdWQiOiJjYW52YXMtY2FudmFzYXBpIiwiaXNzIjoiY2FudmFzLXMyc3Rva2VuIiwibmJmIjoxNzE1MTc1NzcyLCJzdWIiOiI0MWRhMGNiZi01ZmQ4LTQ4NjItODExYS0yMDkxYWE2MzNkNTJ-UFJPRFVDVElPTn5hZTkzNzUxYS1lMzNhLTQ0ZDMtOTIxNy1mNDYyOTRhYWYyMGQifQ.kt-i2A9LHP_w8tJjKJoCn5rog0ftvJib4wbs3blwc9o";
        // $this->session->userdata('access_token');
        // if (!$this->access_token) {
        //     redirect('SnapchatAPI/authorize');
        //     return;
        // }

        // Set the API URL
        $api_url = 'https://adsapi.snapchat.com/v1/catalogs/419f565a-ae00-40b5-b159-3c5b051f742a/product_feeds';

        // Set the payload
        $data = array(
            'product_feeds' => array(
                array(
                    'catalog_id' => '9f9a3260-712c-4eb8-92d9-c33c0af3b88b', // skin catalog id
                    'name' => 'Badger Burrow supplies',
                    'default_currency' => 'USD',
                    'status' => 'ACTIVE',
                    'schedule' => array(
                        'url' => 'ftp://93.184.216.34/timber_product_feed.csv',
                        'username' => 'H0neyBadger',
                        'password' => 'BeezAreCool99',
                        'interval_type' => 'HOURLY',
                        'interval_count' => '1',
                        'timezone' => 'PST',
                        'minute' => '15'
                    )
                )
            )
        );

        // Convert the payload to JSON
        $json_data = json_encode($data);

        // Initialize cURL
        $ch = curl_init();

        // Set cURL options
        curl_setopt($ch, CURLOPT_URL, $api_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json_data);

        // Set headers
        $headers = array(
            'Authorization: Bearer ' . $this->access_token,
            'Content-Type: application/json'
        );
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        // Execute the request and get the response
        $response = curl_exec($ch);

        // Check for errors
        if (curl_errno($ch)) {
            $error_msg = curl_error($ch);
            echo 'Error: ' . $error_msg;
        } else {
            // Close the cURL session
            curl_close($ch);

            // Output the response
            echo 'Response: ' . $response;
        }
    }
}