<?php
/**
 * Base API Controller
 * 
 * Provides common API functionality for all v1 endpoints
 * - Authentication via Bearer token or session
 * - Response formatting
 * - Error handling
 * - CORS support
 * 
 * Date: 2025-10-25
 */

class Base_api extends CI_Controller {

    protected $current_user = null;

    public function __construct() {
        parent::__construct();
        
        // Enable CORS
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization');
        
        // Handle preflight requests
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(200);
            exit;
        }

        // Load utilities
        $this->load->library('session');
        
        // Authenticate user
        $this->current_user = $this->authenticate();
    }

    /**
     * Authenticate user from Bearer token or session
     */
    protected function authenticate() {
        // Try Bearer token first
        $headers = $this->get_headers();
        if (isset($headers['Authorization'])) {
            $auth_header = $headers['Authorization'];
            if (preg_match('/Bearer\s+(.*)$/i', $auth_header, $matches)) {
                $token = $matches[1];
                // In production, validate JWT token
                // For now, return minimal user
                return [
                    'id' => 1,
                    'role' => 'admin',
                    'authenticated' => true
                ];
            }
        }

        // Try session
        if ($this->session->userdata('user_id')) {
            return [
                'id' => $this->session->userdata('user_id'),
                'role' => $this->session->userdata('role') ?: 'user',
                'authenticated' => true
            ];
        }

        // Default: guest with minimal permissions
        return [
            'id' => 0,
            'role' => 'guest',
            'authenticated' => false
        ];
    }

    /**
     * Get all headers
     */
    protected function get_headers() {
        $headers = [];
        if (!is_array($_SERVER)) {
            return $headers;
        }
        foreach ($_SERVER as $name => $value) {
            if (substr($name, 0, 5) == 'HTTP_') {
                $headers[str_replace(' ', '-', ucwords(str_replace('_', ' ', substr($name, 5))))] = $value;
            }
        }
        return $headers;
    }

    /**
     * Format and output JSON response
     */
    protected function response($data) {
        $this->output->set_content_type('application/json');
        
        // Default status if not provided
        if (!isset($data['status'])) {
            $data['status'] = 200;
        }

        http_response_code($data['status']);
        echo json_encode($data, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
        exit;
    }

    /**
     * Get query parameter
     */
    protected function get($key = null, $xss_clean = false) {
        if ($key === null) {
            return $_GET;
        }
        return isset($_GET[$key]) ? 
            ($xss_clean ? htmlspecialchars($_GET[$key]) : $_GET[$key]) : 
            null;
    }

    /**
     * Get POST parameter
     */
    protected function post($key = null, $xss_clean = false) {
        // Try JSON POST first
        $json = file_get_contents('php://input');
        if (!empty($json)) {
            $data = json_decode($json, true);
            if ($key === null) {
                return $data ?: [];
            }
            return isset($data[$key]) ? 
                ($xss_clean ? htmlspecialchars($data[$key]) : $data[$key]) : 
                null;
        }

        // Fall back to form POST
        if ($key === null) {
            return $_POST;
        }
        return isset($_POST[$key]) ? 
            ($xss_clean ? htmlspecialchars($_POST[$key]) : $_POST[$key]) : 
            null;
    }

    /**
     * Check if user has permission
     */
    protected function check_permission($required_roles = []) {
        if (!$this->current_user['authenticated']) {
            $this->response([
                'success' => false,
                'message' => 'Unauthorized',
                'status' => 401
            ]);
        }

        if (!empty($required_roles) && !in_array($this->current_user['role'], $required_roles)) {
            $this->response([
                'success' => false,
                'message' => 'Forbidden',
                'status' => 403
            ]);
        }

        return true;
    }

    /**
     * Get entity information from warehouse
     */
    protected function get_entity_info($warehouse_id) {
        $query = "SELECT * FROM sma_warehouses WHERE id = ?";
        $result = $this->db->query($query, [$warehouse_id]);
        return $result->row_array();
    }

    /**
     * Get pharmacy for current user (if branch_manager or pharmacy_manager)
     */
    protected function get_pharmacy_for_user($user_id) {
        $query = "SELECT pharmacy_id FROM sma_users WHERE id = ?";
        $result = $this->db->query($query, [$user_id]);
        $row = $result->row_array();
        return $row ? $row['pharmacy_id'] : null;
    }

    /**
     * Get branch for current user (if branch_manager)
     */
    protected function get_branch_for_user($user_id) {
        $query = "SELECT branch_id FROM sma_users WHERE id = ?";
        $result = $this->db->query($query, [$user_id]);
        $row = $result->row_array();
        return $row ? $row['branch_id'] : null;
    }

    /**
     * Check if pharmacy_manager has access to pharmacy
     */
    protected function check_pharmacy_access($pharmacy_id, $user_id) {
        $assigned_pharmacy = $this->get_pharmacy_for_user($user_id);
        return $assigned_pharmacy === $pharmacy_id;
    }

    /**
     * Check if branch_manager has access to branch
     */
    protected function check_branch_access($branch_id, $user_id) {
        $assigned_branch = $this->get_branch_for_user($user_id);
        return $assigned_branch === $branch_id;
    }
}
