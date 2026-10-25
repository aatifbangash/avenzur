<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Wasfaty Controller
 * 
 * Handles mock Wasfaty prescription integration
 * Allows pharmacists to retrieve prescriptions via phone number and prescription code,
 * then convert them to orders with automatic batch selection and customer discount application.
 * 
 * @package    Avenzur ERP
 * @subpackage Controllers
 * @category   Wasfaty Integration
 * @author     Avenzur Development Team
 */
class Wasfaty extends MY_Controller {

    public function __construct() {
        parent::__construct();
        
        // Load required models
        $this->load->model('Wasfaty_model');
        $this->load->model('Batch_model');
        // Note: Wasfaty_service library removed - not needed for mock integration

        // Check if user is logged in and has permission
        if (!$this->session->userdata('user_id')) {
            redirect('login');
        }
    }

    /**
     * Fetch prescription by phone and code
     * AJAX endpoint
     */
    public function fetch_prescription() {
        // Simulate 1 second delay
        sleep(1);

        $phone = $this->input->post('phone');
        $code = $this->input->post('prescription_code');

        // Validation
        if (!$this->_validate_phone($phone)) {
            echo json_encode([
                'success' => false,
                'message' => 'Invalid phone number format. Must be Saudi format (05XXXXXXXX)'
            ]);
            return;
        }

        if (!$this->_validate_prescription_code($code)) {
            echo json_encode([
                'success' => false,
                'message' => 'Invalid prescription code format'
            ]);
            return;
        }

        // Fetch prescription
        $prescription = $this->Wasfaty_model->get_prescription($phone, $code);

        if (!$prescription) {
            echo json_encode([
                'success' => false,
                'message' => 'Prescription not found or already dispensed'
            ]);
            return;
        }

        // Get prescription items
        $items = $this->Wasfaty_model->get_prescription_items($prescription->id);

        // Check stock availability for each item
        $stock_check = $this->_check_stock_availability($items);

        if (!$stock_check['available']) {
            echo json_encode([
                'success' => false,
                'message' => 'Insufficient stock for: ' . implode(', ', $stock_check['unavailable_items'])
            ]);
            return;
        }

        echo json_encode([
            'success' => true,
            'prescription' => $prescription,
            'items' => $items,
            'customer_type' => $prescription->customer_type
        ]);
    }

    /**
     * Convert prescription to order
     * AJAX endpoint
     */
    public function convert_to_order() {
        $prescription_code = $this->input->post('prescription_code');

        if (!$prescription_code) {
            echo json_encode(['success' => false, 'message' => 'Prescription code required']);
            return;
        }

        $prescription = $this->Wasfaty_model->get_prescription_by_code($prescription_code);

        if (!$prescription) {
            echo json_encode(['success' => false, 'message' => 'Prescription not found']);
            return;
        }

        $items = $this->Wasfaty_model->get_prescription_items($prescription->id);

        // Prepare cart items with batch selection
        $cart_items = [];
        foreach ($items as $item) {
            $total_quantity = $item->quantity * $item->duration_days;

            // Select batch with earliest expiry
            $batch = $this->Batch_model->get_earliest_expiry_batch(
                $item->medicine_id,
                $total_quantity
            );

            if (!$batch) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Insufficient stock for ' . $item->medicine_name
                ]);
                return;
            }

            $cart_items[] = [
                'medicine_id' => $item->medicine_id,
                'medicine_name' => $item->medicine_name,
                'quantity' => $total_quantity,
                'batch_id' => $batch->id,
                'batch_number' => $batch->batch_number,
                'price' => $batch->selling_price,
                'expiry_date' => $batch->expiry_date
            ];
        }

        // Calculate discount based on customer type
        $discount_percentage = $this->_get_discount_percentage($prescription->customer_type);

        echo json_encode([
            'success' => true,
            'cart_items' => $cart_items,
            'customer_type' => $prescription->customer_type,
            'discount_percentage' => $discount_percentage,
            'prescription_id' => $prescription->id,
            'prescription_code' => $prescription_code
        ]);
    }

    /**
     * Validate Saudi phone number format
     */
    private function _validate_phone($phone) {
        return preg_match('/^05\d{8}$/', $phone);
    }

    /**
     * Validate prescription code format
     */
    private function _validate_prescription_code($code) {
        return preg_match('/^\d{6}$/', $code);
    }

    /**
     * Check stock availability for all items
     */
    private function _check_stock_availability($items) {
        $unavailable = [];

        foreach ($items as $item) {
            $total_quantity = $item->quantity * $item->duration_days;
            $batch = $this->Batch_model->get_earliest_expiry_batch(
                $item->medicine_id,
                $total_quantity
            );

            if (!$batch) {
                $unavailable[] = $item->medicine_name;
            }
        }

        return [
            'available' => empty($unavailable),
            'unavailable_items' => $unavailable
        ];
    }

    /**
     * Get discount percentage based on customer type
     */
    private function _get_discount_percentage($customer_type) {
        $discounts = [
            'REGULAR' => 0,
            'SILVER' => 5,
            'GOLD' => 15,
            'PLATINUM' => 20
        ];

        return isset($discounts[$customer_type]) ? $discounts[$customer_type] : 0;
    }
}
