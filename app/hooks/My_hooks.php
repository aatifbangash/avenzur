<?php

class My_hooks {
    protected $CI;
    public function __construct() {
        $this->CI =& get_instance();
    }
    public function check_hook($a) {
        //  // Access the CI_Controller instance
        //  $CI =& get_instance();

        //  // Access the active database connection
        //  $db = $CI->db;
 
        //  // Modify the query here
        //  $db->where('status', 'active');
    }

}
