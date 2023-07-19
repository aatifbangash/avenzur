<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Company extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        if (!$this->loggedIn || !$this->Owner) {
            $this->session->set_userdata('requested_page', $this->uri->uri_string());
            admin_redirect('login');
        }

        $this->load->admin_model('db_model');
        $this->load->admin_model('multi_company_model');
    }

    public function login()
    {
        echo 'heerrr';exit;
    }

    public function index()
    {
        $this->data['payload'] = [
            'error' => null,
            'company_added' => null
        ];
        if (!empty($_POST)) {
            if (empty($_POST['company_name'])) $this->data['payload']['error'] = true;

            if (!isset($this->data['payload']['error'])) {
                $this->data['payload']['row'] = [
                    'company_name' => $_POST['company_name'],
                    'slug' => $this->sma->slug($_POST["company_name"]),
                ];

                $database = $this->multi_company_model->getUnUsedDb();
                if (empty($database)) die("Database limit exceeded. Contact the administrator.");

                $this->data['payload']['row']['db_id'] = $database->id;
                $this->data['payload']['row']['owner_id'] = 0; //TODO('will be updated with real owner id')

                $companyId = $this->multi_company_model->insertCompany($this->data['payload']['row']);
                if ($companyId) {
                    $this->multi_company_model->setDbIsUsed($database);
                    $this->data['payload']['row']["company_id"] = $companyId;
                    $this->data['payload']["company_added"] = true;

                    $this->load->library('ion_auth');
                    $this->ion_auth->logout();
                }
            }
        }
        $this->load->view($this->theme . 'add_company', $this->data);

    }

}
