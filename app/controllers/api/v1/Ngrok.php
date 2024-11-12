<?php
defined('BASEPATH') or exit('No direct script access allowed');

require APPPATH . '/libraries/REST_Controller.php';
use Restserver\Libraries\REST_Controller;

class Ngrok extends REST_Controller
{
    public function __construct()
    {
        parent::__construct();
        //$this->load->model('Some_model');  // Load your models if needed
    }

    public function index_get()
    {
        // Prepare some data to return
        $data = [
            'message' => 'Hello from CodeIgniter!',
            'status' => 'success'
        ];

        // Respond with the data and 200 OK HTTP code
        $this->response($data, REST_Controller::HTTP_OK);
    }
}
