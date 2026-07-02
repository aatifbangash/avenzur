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
            http_response_code(401);  
            $error_response = json_encode([
                'success' => false,
                'error' => 'Invalid or missing Api-Key'
            ]);
        
            header('Content-Type: application/json');
            echo $error_response;
            exit;
        }

        $shipmentNumber = $this->input->post('shipmentNumber');
        $statusCode = $this->input->post('statusCode');
        if($shipmentNumber == '' || $statusCode == '')
        {
            log_message('error', 'Validation errors: ShipmentNumnber-StatusCode:'.$shipmentNumber.'-'.$statusCode);
            http_response_code(401);  
            $error_response = json_encode([
                'success' => false,
                'error' => 'Shipment Numnber or Status Code is missing.'
            ]);
        
            header('Content-Type: application/json');
            echo $error_response;
            exit;
        }

        $this->db->where('courier_order_tracking_id', $shipmentNumber);
        $query = $this->db->get('sales');

         if($query->num_rows()  == 0)
         {
            http_response_code(401);  
            $error_response = json_encode([
                'success' => false,
                'error' => 'Shipment Number does not exist!'
            ]);
        
            header('Content-Type: application/json');
            $response =  'Shipment Number does not exist. Id: '.$shipmentNumber;
            log_message('debug', 'Response: ' .$response);
            echo $error_response;
            exit;
         }

       
        // Update the database with the new shipment status
        $update_success = $this->sales_model->updateSaleCourierStatus(4, $shipmentNumber, $statusCode);

        if ($update_success) {
            $response =  'Shipment status updated successfully.';
            log_message('debug', 'Response: ' .$response);
            http_response_code(200);  
            $success_response = json_encode([
                'success' => true,
                'message' => 'Shipment Status updated successfully.'
            ]);
        
            header('Content-Type: application/json');
            $response =  'Failed to update shipment status.';
            echo $success_response;
            exit;

        } else {
            http_response_code(401);  
            $error_response = json_encode([
                'success' => false,
                'error' => 'Something wrong!'
            ]);
        
            header('Content-Type: application/json');
            $response =  'Failed to update shipment status.';
            log_message('debug', 'Response: ' .$response);
            echo $error_response;
            exit;
           
        }

        //echo json_encode($response);
       
    }
}
