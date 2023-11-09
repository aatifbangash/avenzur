<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Sitemap extends MY_Shop_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->lang->admin_load('sma');
        $this->load->library('ion_auth');
        $this->load->library('form_validation');
        $this->lang->admin_load('auth', $this->Settings->user_language);
        $this->load->helper('url');
    }

    public function index()
    {
        $this->load->database();
        $query = $this->db->get("products");
        $this->data['products'] = $query->result();

        $this->page_construct('sitemap', $this->data);
    }
}
