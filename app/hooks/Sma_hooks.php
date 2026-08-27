<?php

class Sma_hooks {
    protected $CI;
    public function __construct() {
        $this->CI =& get_instance();
    }
    public function check() {
        $this->corsHeaders();
        if(! ($this->CI->db->conn_id)) {
            header("Location: install/index.php");
            die();
        }
    }
    private function corsHeaders() {
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
        header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, *");

        if ($_SERVER['REQUEST_METHOD'] == "OPTIONS") {
            http_response_code(200);
            exit();
        }
    }

}
