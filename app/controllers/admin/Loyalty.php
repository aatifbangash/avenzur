<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Loyalty extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();

        if (!$this->loggedIn) {
            $this->session->set_userdata('requested_page', $this->uri->uri_string());
            admin_redirect('login');
        }

        if ($this->Customer || $this->Supplier) {
            redirect('/');
        }

        $this->load->library('form_validation');
        $this->load->admin_model('loyalty_model');
    }

    /**
     * Loyalty Dashboard (Default View)
     */
    public function index()
    {
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        
        $bc = [
            ['link' => admin_url(), 'page' => lang('home')],
            ['link' => '#', 'page' => lang('Loyalty Dashboard')]
        ];
        $meta = ['page_title' => lang('Loyalty Dashboard'), 'bc' => $bc];
        $this->page_construct('loyalty/dashboard', $meta, $this->data);
    }

    /**
     * Loyalty Rules Management
     */
    public function rules()
    {
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        
        $bc = [
            ['link' => admin_url(), 'page' => lang('home')],
            ['link' => admin_url('loyalty'), 'page' => lang('Loyalty')],
            ['link' => '#', 'page' => lang('Loyalty Rules')]
        ];
        $meta = ['page_title' => lang('Loyalty Rules'), 'bc' => $bc];
        $this->page_construct('loyalty/rules', $meta, $this->data);
    }

    /**
     * Budget Setup (Multi-level: Company/Pharmacy Group/Pharmacy/Branch)
     */
    public function budget_setup()
    {
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        
        $bc = [
            ['link' => admin_url(), 'page' => lang('home')],
            ['link' => admin_url('loyalty'), 'page' => lang('Loyalty')],
            ['link' => '#', 'page' => lang('Budget Setup')]
        ];
        $meta = ['page_title' => lang('Budget Setup'), 'bc' => $bc];
        $this->page_construct('loyalty/budget_setup', $meta, $this->data);
    }

    /**
     * Budget Dashboard - Main budgeting UI with tracking, forecasting, and compliance
     */
    public function budget()
    {
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        
        $bc = [
            ['link' => admin_url(), 'page' => lang('home')],
            ['link' => admin_url('loyalty'), 'page' => lang('Loyalty')],
            ['link' => '#', 'page' => lang('Budget')]
        ];
        $meta = ['page_title' => lang('Budget Management'), 'bc' => $bc];
        $this->page_construct('loyalty/budget', $meta, $this->data);
    }

    /**
     * API Endpoint to get budget status
     */
    public function get_budget_status()
    {
        $scopeLevel = $this->input->get('scopeLevel') ?: 'company';
        $scopeId = $this->input->get('scopeId') ?: 1;
        
        $data = $this->loyalty_model->getBudgetStatus($scopeLevel, $scopeId);
        $this->sma->send_json($data);
    }

    /**
     * API Endpoint to get burn rate
     */
    public function get_burn_rate()
    {
        $scopeLevel = $this->input->get('scopeLevel') ?: 'company';
        $scopeId = $this->input->get('scopeId') ?: 1;
        
        $data = $this->loyalty_model->getBurnRate($scopeLevel, $scopeId);
        $this->sma->send_json($data);
    }

    /**
     * API Endpoint to get budget projections
     */
    public function get_projections()
    {
        $scopeLevel = $this->input->get('scopeLevel') ?: 'company';
        $scopeId = $this->input->get('scopeId') ?: 1;
        
        $data = $this->loyalty_model->getBudgetProjections($scopeLevel, $scopeId);
        $this->sma->send_json($data);
    }

    /**
     * API Endpoint to get budget alerts
     */
    public function get_alerts()
    {
        $scopeLevel = $this->input->get('scopeLevel') ?: 'company';
        $scopeId = $this->input->get('scopeId') ?: 1;
        
        $data = $this->loyalty_model->getBudgetAlerts($scopeLevel, $scopeId);
        $this->sma->send_json($data);
    }

    /**
     * API Endpoint to get spending trend
     */
    public function get_spending_trend()
    {
        $scopeLevel = $this->input->get('scopeLevel') ?: 'company';
        $scopeId = $this->input->get('scopeId') ?: 1;
        $period = $this->input->get('period') ?: 'monthly';
        
        $data = $this->loyalty_model->getSpendingTrend($scopeLevel, $scopeId, $period);
        $this->sma->send_json($data);
    }

    /**
     * API Endpoint to get budget summary
     */
    public function get_summary()
    {
        $scopeLevel = $this->input->get('scopeLevel') ?: 'company';
        $scopeId = $this->input->get('scopeId') ?: 1;
        
        $data = $this->loyalty_model->getBudgetSummary($scopeLevel, $scopeId);
        $this->sma->send_json($data);
    }
}
