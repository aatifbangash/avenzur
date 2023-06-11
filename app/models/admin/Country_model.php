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
            $business_id = $this->ion_auth->user()->row()->business_id;
            $data["business_id"] = $business_id;
        if ($this->db->insert('countries', $data)) {
            return true;
        }
        return false;
    
	}
	
}