<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Seo_setting extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        if (!$this->loggedIn) {
            $this->session->set_userdata('requested_page', $this->uri->uri_string());
            $this->sma->md('login');
        }
        $this->load->admin_model('seo_model');
    }

    public function index()
    {
        $this->data['seo_settings'] = $this->seo_model->getSeoSettings();
        //$this->load->view('admin/seo_settings', $data);
        $this->page_construct('settings/seo_settings', $meta = array(), $this->data);
        // $this->load->view($this->theme . 'settings/seo_settings', $this->data);
    }

    public function update()
    {
        // Handle form submission to update SEO settings in the database
        // ...
        if ($this->input->server('REQUEST_METHOD') === 'POST') {
            // Assuming you have form fields like title, description, keywords
            $data['title'] = $this->input->post('title');
            $data['description'] = $this->input->post('description');
            $data['keywords'] = $this->input->post('keywords');

            // Assuming you have an identifier for the SEO settings (replace 'identifier' with your actual column name)
            //$identifier = $this->input->post('identifier');

            // Call the model method to update SEO settings
            $this->seo_model->updateSeoSettings($data);

            // Optionally, you can redirect to the index page or show a success message
            admin_redirect('seo_setting/index');
        } else {
            // Invalid request method, handle accordingly (redirect, show error, etc.)
            show_error('Invalid Request', 400);
        }
    }
}


?>