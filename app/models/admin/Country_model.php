<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Country_model extends CI_Model
{
     protected $table = 'countries';

     
    public function __construct()
    {
        parent::__construct();
    }


        public function insertCountry($data){
            $business_id = $this->session->userdata['business_id'];  //TAG:-replaced
            $data["business_id"] = $business_id;
        if ($this->db->insert('countries', $data)) {
            return true;
        }
        return false;
    
	}
	
}