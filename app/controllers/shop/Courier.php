<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Courier extends MY_Shop_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->admin_model('sales_model');
    }

    public function stc_callback() {

        // Retrieve Api-Key from the headers
        $headers = $this->input->request_headers();
        $courier = $this->site->getCourierById(4);
        //log_message('error', $headers);
        if (!isset($headers['Api-Key']) || $headers['Api-Key'] !==  $courier->auth_key) {
            // Invalid or missing Api-Key
            log_message('error', 'Invalid or missing Api-Key');
            exit;
        }

        $shipmentNumber = $this->input->post('shipmentNumber');
        $statusCode = $this->input->post('statusCode');
        if($shipmentNumber == '' || $statusCode == '')
        {
            log_message('error', 'Validation errors: ShipmentNumnber-StatusCode:'.$shipmentNumber.'-'.$statusCode);
            exit;
        }
       
        // Update the database with the new shipment status
        $update_success = $this->sales_model->updateSaleCourierStatus(4, $shipmentNumber, $statusCode);

        if ($update_success) {
            $response =  'Shipment status updated successfully.';
        } else {
            $response =  'Failed to update shipment status.';
        }

        //echo json_encode($response);
        log_message('debug', 'Response: ' .$response);
    }
}
