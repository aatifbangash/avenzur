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
        echo 'Here we are...';exit;
        $this->load->database();
        $query = $this->db->get("products");
        $data['products'] = $query->result();

        $this->load->view('sitemap', $data);
    }
}
