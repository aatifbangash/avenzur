<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Seo_model extends CI_Model {
    public function __construct()
    {
        parent::__construct();
    }
    public function getSeoSettings() {
       return $q = $this->db->get('seo_settings')->row();
        
    }
    public function updateSeoSettings($data){
        $data = array(
            'title' => $data['title'],
            'description' => $data['description'],
            'keywords' => $data['keywords'],
        );
        $this->db->update('seo_settings', $data);
    }
}

?>