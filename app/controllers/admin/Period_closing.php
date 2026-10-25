<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Period Closing — close / reopen calendar months per module.
 * Access: Admin, Owner, Finance Manager only.
 * Module enforcement is not wired yet (control UI only).
 */
class Period_closing extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();

        if (!$this->loggedIn) {
            $this->session->set_userdata('requested_page', $this->uri->uri_string());
            $this->sma->md('login');
        }

        if ($this->Customer || $this->Supplier) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect('/');
        }

        if (!$this->canManagePeriodClosing()) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            admin_redirect('welcome');
        }

        $this->load->admin_model('period_closing_model');
        $this->load->library('form_validation');
    }

    protected function canManagePeriodClosing()
    {
        return !empty($this->Owner)
            || !empty($this->Admin)
            || !empty($this->FinanceManager)
            || $this->sma->in_group('financemanager');
    }

    public function index()
    {
        $year = (int) ($this->input->get('year') ?: date('Y'));
        if ($year < 2000 || $year > 2100) {
            $year = (int) date('Y');
        }

        $this->data['error']    = $this->session->flashdata('error');
        $this->data['message']  = $this->session->flashdata('message');
        $this->data['warning']  = $this->session->flashdata('warning');
        $this->data['year']     = $year;
        $this->data['modules']  = Period_closing_model::MODULES;
        $this->data['closures'] = $this->period_closing_model->getClosuresForYear($year);
        $this->data['months']   = [
            1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
            5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
            9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December',
        ];

        $bc   = [
            ['link' => base_url(), 'page' => lang('home')],
            ['link' => '#', 'page' => lang('Settings')],
            ['link' => '#', 'page' => 'Period Closing'],
        ];
        $meta = ['page_title' => 'Period Closing', 'bc' => $bc];
        $this->page_construct('settings/period_closing', $meta, $this->data);
    }

    public function close()
    {
        if (!$this->input->post()) {
            admin_redirect('period_closing');
        }

        $module = $this->input->post('module');
        $year   = (int) $this->input->post('period_year');
        $month  = (int) $this->input->post('period_month');
        $note   = trim((string) $this->input->post('note'));

        if (!$this->period_closing_model->isValidModule($module) || $month < 1 || $month > 12) {
            $this->session->set_flashdata('error', 'Invalid module or period.');
            admin_redirect('period_closing?year=' . $year);
        }

        $existing = $this->period_closing_model->getClosure($module, $year, $month);
        if ($existing && $existing->status === 'closed') {
            $this->session->set_flashdata('warning', 'This period is already closed.');
            admin_redirect('period_closing?year=' . $year);
        }

        if ($this->period_closing_model->closePeriod($module, $year, $month, $this->session->userdata('user_id'), $note ?: null)) {
            $label = Period_closing_model::MODULES[$module];
            $this->session->set_flashdata('message', sprintf('%s closed for %s %d.', $label, date('F', mktime(0, 0, 0, $month, 1)), $year));
        } else {
            $this->session->set_flashdata('error', 'Could not close period.');
        }

        admin_redirect('period_closing?year=' . $year);
    }

    public function reopen()
    {
        if (!$this->input->post()) {
            admin_redirect('period_closing');
        }

        $module = $this->input->post('module');
        $year   = (int) $this->input->post('period_year');
        $month  = (int) $this->input->post('period_month');
        $note   = trim((string) $this->input->post('note'));

        if (!$this->period_closing_model->isValidModule($module) || $month < 1 || $month > 12) {
            $this->session->set_flashdata('error', 'Invalid module or period.');
            admin_redirect('period_closing?year=' . $year);
        }

        $existing = $this->period_closing_model->getClosure($module, $year, $month);
        if (!$existing || $existing->status !== 'closed') {
            $this->session->set_flashdata('warning', 'This period is not closed.');
            admin_redirect('period_closing?year=' . $year);
        }

        if ($this->period_closing_model->reopenPeriod($module, $year, $month, $this->session->userdata('user_id'), $note ?: null)) {
            $label = Period_closing_model::MODULES[$module];
            $this->session->set_flashdata('message', sprintf('%s reopened for %s %d.', $label, date('F', mktime(0, 0, 0, $month, 1)), $year));
        } else {
            $this->session->set_flashdata('error', 'Could not reopen period.');
        }

        admin_redirect('period_closing?year=' . $year);
    }

    /**
     * Close all four modules for a given month (convenience).
     */
    public function close_all()
    {
        if (!$this->input->post()) {
            admin_redirect('period_closing');
        }

        $year  = (int) $this->input->post('period_year');
        $month = (int) $this->input->post('period_month');
        $note  = trim((string) $this->input->post('note'));
        $uid   = $this->session->userdata('user_id');

        if ($month < 1 || $month > 12) {
            $this->session->set_flashdata('error', 'Invalid period.');
            admin_redirect('period_closing?year=' . $year);
        }

        $ok = 0;
        foreach (array_keys(Period_closing_model::MODULES) as $module) {
            if ($this->period_closing_model->closePeriod($module, $year, $month, $uid, $note ?: null)) {
                $ok++;
            }
        }

        $this->session->set_flashdata('message', sprintf('Closed %d module(s) for %s %d.', $ok, date('F', mktime(0, 0, 0, $month, 1)), $year));
        admin_redirect('period_closing?year=' . $year);
    }
}
