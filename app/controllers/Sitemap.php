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
        $this->load->admin_model('shop_admin_model');
        $this->load->helper('url');
    }

    public function index()
    {
        $this->load->database();
        $query = $this->db->where('hide', 0)->get("products");

        $brands = $this->shop_admin_model->getAllBrands();
        $pages = $this->shop_admin_model->getAllPages();
        $categories = $this->shop_admin_model->getAllCategories();

        $this->data['products'] = $query->result();
        $this->data['brands'] = $brands;
        $this->data['pages'] = $pages;
        $this->data['categories'] = $categories;


        $this->load->helper('xml');
        $this->output->set_content_type('text/xml');

        //$this->page_construct('sitemap', $this->data);
        $this->load->view($this->theme . 'sitemap', $this->data);
    }
}
