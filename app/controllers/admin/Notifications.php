<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Notifications extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();

        if (!$this->loggedIn) {
            $this->session->set_userdata('requested_page', $this->uri->uri_string());
            $this->sma->md('login');
        }
        if (!$this->Owner && !$this->Admin) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect($_SERVER['HTTP_REFERER']);
        }
        $this->lang->admin_load('notifications', $this->Settings->user_language);
        $this->load->library('form_validation');
        $this->load->admin_model('cmt_model');
    }

    public function addRasdNotification(){
        $this->form_validation->set_rules('dispatch_id', lang('Dispatch Id'), 'required|min_length[3]');

        if ($this->form_validation->run() == true) {
            $data = [
                'dispatch_id'   => $this->input->post('dispatch_id'),
                'invoice_no'    => $this->input->post('invoice_no'),
                'status' => 'pending',
                'date' => date('Y-m-d')
            ];
        } elseif ($this->input->post('submit')) {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect('notifications/rasd');
        }

        if ($this->form_validation->run() == true && $this->cmt_model->addRasdNotification($data)) {
            $this->session->set_flashdata('message', lang('notification_added'));
            admin_redirect('notifications/rasd');
        } else {
            
            $this->data['error']    = validation_errors();
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'notifications/addRasdNotification', $this->data);
        }
    }

    public function add()
    {
        $this->form_validation->set_rules('comment', lang('comment'), 'required|min_length[3]');

        if ($this->form_validation->run() == true) {
            $data = [
                'comment'   => $this->input->post('comment'),
                'from_date' => $this->input->post('from_date') ? $this->sma->fld($this->input->post('from_date')) : null,
                'till_date' => $this->input->post('to_date') ? $this->sma->fld($this->input->post('to_date')) : null,
                'scope'     => $this->input->post('scope'),
            ];
        } elseif ($this->input->post('submit')) {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect('notifications');
        }

        if ($this->form_validation->run() == true && $this->cmt_model->addNotification($data)) {
            $this->session->set_flashdata('message', lang('notification_added'));
            admin_redirect('notifications');
        } else {
            $this->data['comment'] = ['name' => 'comment',
                'id'                         => 'comment',
                'type'                       => 'textarea',
                'class'                      => 'form-control',
                'required'                   => 'required',
                'value'                      => $this->form_validation->set_value('comment'),
            ];

            $this->data['error']    = validation_errors();
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'notifications/add', $this->data);
        }
    }

    public function deleteRasdNotification($id = null){
        if (!$this->Owner) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect($_SERVER['HTTP_REFERER']);
        }
        if (!$id) {
            $this->sma->send_json(['error' => 1, 'msg' => lang('id_not_found')]);
        }

        if ($this->cmt_model->deleteRasdNotification($id)) {
            $this->sma->send_json(['error' => 0, 'msg' => lang('rasd_notification_deleted')]);
        }else{
            $this->sma->send_json(['error' => 0, 'msg' => lang('Can not delete this notification id')]);
        }
    }

    public function delete($id = null)
    {
        if (!$this->Owner) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect($_SERVER['HTTP_REFERER']);
        }
        if (!$id) {
            $this->sma->send_json(['error' => 1, 'msg' => lang('id_not_found')]);
        }

        if ($this->cmt_model->deleteComment($id)) {
            $this->sma->send_json(['error' => 0, 'msg' => lang('notifications_deleted')]);
        }
    }

    public function edit($id = null)
    {
        if (!$this->Owner) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            die("<script type='text/javascript'>setTimeout(function(){ window.top.location.href = '" . ($_SERVER['HTTP_REFERER'] ?? site_url('welcome')) . "'; }, 10);</script>");
            exit;
        }

        if ($this->input->post('id')) {
            $id = $this->input->post('id');
        }

        $this->form_validation->set_rules('comment', lang('notifications'), 'required|min_length[3]');

        if ($this->form_validation->run() == true) {
            $data = [
                'comment'   => $this->input->post('comment'),
                'from_date' => $this->input->post('from_date') ? $this->sma->fld($this->input->post('from_date')) : null,
                'till_date' => $this->input->post('to_date') ? $this->sma->fld($this->input->post('to_date')) : null,
                'scope'     => $this->input->post('scope'),
            ];
        } elseif ($this->input->post('submit')) {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect('notifications');
        }

        if ($this->form_validation->run() == true && $this->cmt_model->updateNotification($id, $data)) {
            $this->session->set_flashdata('message', lang('notification_updated'));
            admin_redirect('notifications');
        } else {
            $comment = $this->cmt_model->getCommentByID($id);

            $this->data['comment'] = ['name' => 'comment',
                'id'                         => 'comment',
                'type'                       => 'textarea',
                'class'                      => 'form-control',
                'required'                   => 'required',
                'value'                      => $this->form_validation->set_value('comment', $comment->comment),
            ];

            $this->data['notification'] = $comment;
            $this->data['id']           = $id;
            $this->data['modal_js']     = $this->site->modal_js();
            $this->data['error']        = validation_errors();
            $this->load->view($this->theme . 'notifications/edit', $this->data);
        }
    }

    public function getRasdNotifications(){
        $this->load->library('datatables');
        $this->datatables
            ->select('id, dispatch_id, invoice_no, status, date')
            ->from('rasd_notifications')
            //->where('notification', 1)
            ->add_column('Actions', "<div class=\"text-center\"><a href='#' class='tip po' title='<b>" . $this->lang->line('delete_notification') . "</b>' data-content=\"<p>" . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . admin_url('notifications/deleteRasdNotification/$1') . "'>" . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i></a></div>", 'id');
        $this->datatables->unset_column('id');
        echo $this->datatables->generate();
    }

    public function getNotifications()
    {
        $this->load->library('datatables');
        $this->datatables
            ->select('id, comment, date, from_date, till_date')
            ->from('notifications')
            //->where('notification', 1)
            ->add_column('Actions', "<div class=\"text-center\"><a href='" . admin_url('notifications/edit/$1') . "' data-toggle='modal' data-target='#myModal' class='tip' title='" . lang('edit_notification') . "'><i class=\"fa fa-edit\"></i></a> <a href='#' class='tip po' title='<b>" . $this->lang->line('delete_notification') . "</b>' data-content=\"<p>" . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . admin_url('notifications/delete/$1') . "'>" . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i></a></div>", 'id');
        $this->datatables->unset_column('id');
        echo $this->datatables->generate();
    }

    public function index()
    {
        if (!$this->Owner && !$this->Admin) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect($_SERVER['HTTP_REFERER']);
        }

        $this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');
        $bc                  = [['link' => base_url(), 'page' => lang('home')], ['link' => '#', 'page' => lang('notifications')]];
        $meta                = ['page_title' => lang('notifications'), 'bc' => $bc];
        $this->page_construct('notifications/index', $meta, $this->data);
    }

    public function rasd(){
        if (!$this->Owner && !$this->Admin) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect($_SERVER['HTTP_REFERER']);
        }

        $this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');
        $bc                  = [['link' => base_url(), 'page' => lang('home')], ['link' => '#', 'page' => lang('Rasd Notifications')]];
        $meta                = ['page_title' => lang('Rasd Notifications'), 'bc' => $bc];
        $this->page_construct('notifications/rasd', $meta, $this->data);
    }

    public function sync_rasd_serials(){
        $rasd_notifications = $this->cmt_model->getRasdNotifications();
        print_r($rasd_notifications);exit;
    }
}
