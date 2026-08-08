<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Wasfaty Model
 * 
 * Handles database operations for Wasfaty prescription management
 * 
 * @package    Avenzur ERP
 * @subpackage Models
 * @category   Wasfaty Integration
 * @author     Avenzur Development Team
 */
class Wasfaty_model extends CI_Model {

    public function __construct() {
        parent::__construct();
    }

    /**
     * Get prescription by phone and code
     * 
     * @param string $phone Patient phone number
     * @param string $code Prescription code
     * @return object|null Prescription object or null if not found
     */
    public function get_prescription($phone, $code) {
        return $this->db
            ->where('patient_phone', $phone)
            ->where('prescription_code', $code)
            ->where('status', 'PENDING')
            ->get('wasfaty_prescriptions')
            ->row();
    }

    /**
     * Get prescription by code only
     * 
     * @param string $code Prescription code
     * @return object|null Prescription object or null if not found
     */
    public function get_prescription_by_code($code) {
        return $this->db
            ->where('prescription_code', $code)
            ->where('status', 'PENDING')
            ->get('wasfaty_prescriptions')
            ->row();
    }

    /**
     * Get prescription items
     * 
     * @param int $prescription_id Prescription ID
     * @return array Array of prescription items
     */
    public function get_prescription_items($prescription_id) {
        return $this->db
            ->where('prescription_id', $prescription_id)
            ->get('wasfaty_prescription_items')
            ->result();
    }

    /**
     * Update prescription status
     * 
     * @param int $prescription_id Prescription ID
     * @param string $status New status (PENDING, DISPENSED, CANCELLED)
     * @param int|null $order_id Associated order ID (optional)
     * @return bool Success status
     */
    public function update_prescription_status($prescription_id, $status, $order_id = null) {
        $data = [
            'status' => $status,
            'fetched_at' => date('Y-m-d H:i:s')
        ];

        if ($order_id) {
            $data['order_id'] = $order_id;
        }

        return $this->db
            ->where('id', $prescription_id)
            ->update('wasfaty_prescriptions', $data);
    }
}
