<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Sitemap extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->lang->admin_load('sma');
    }

    public function index()
    {
        $this->load->database();
        $query = $this->db->get("products");
        $data['products'] = $query->result();
        print_r($data['products']);
       // $this->page_construct('sitemap', $data);
    }
}
