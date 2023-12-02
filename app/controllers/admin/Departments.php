<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Departments extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();

        if (!$this->loggedIn) {
            $this->session->set_userdata('requested_page', $this->uri->uri_string());
            $this->sma->md('login');
        }



        $this->load->library('form_validation');
        $this->load->admin_model('department_model');

        // Sequence-Code
        $this->load->library('SequenceCode');
        $this->sequenceCode = new SequenceCode();
    }

    public function index()
    {

        $bc                     = [['link' => base_url(), 'page' => lang('home')], ['link' => '#', 'page' => lang('Departments')]];
        $meta                   = ['page_title' => lang('Departments'), 'bc' => $bc];
        $this->page_construct('departments/index', $meta, $this->data);
    }

    public function getDepartments()
    {
        $edit_link        = anchor('admin/departments/edit/$1', '<i class="fa fa-edit"></i> ' . lang('Edit Departments '));
        $delete_link      = "<a href='#' class='po' title='<b>" . $this->lang->line('Delete Department') . "</b>' data-content=\"<p>"
            . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . admin_url('departments/delete/$1') . "'>"
            . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
            . lang('Delete Departments') . '</a>';
        $action = '<div class="text-center"><div class="btn-group text-left">'
            . '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
            . lang('actions') . ' <span class="caret"></span></button>
        <ul class="dropdown-menu pull-right" role="menu">
            <li>' . $edit_link . '</li>
            <li>' . $delete_link . '</li>
        </ul>
        </div></div>';

        $this->load->library('datatables');
        $this->datatables->select("{$this->db->dbprefix('departments')}.id as id, {$this->db->dbprefix('departments')}.id as departmentsId, IFNULL(parent.name, 'N/A') AS parent_name, {$this->db->dbprefix('departments')}.name as name,{$this->db->dbprefix('departments')}.code as code ")
            ->from('sma_departments')
            ->join('sma_departments AS parent', "{$this->db->dbprefix('departments')}.parent_id = parent.id", 'left');
        $this->datatables->add_column('Actions', $action, 'departmentsId');
        echo $this->datatables->generate();
    }

    public function add()
    {
        $this->form_validation->set_rules('name', lang('department_name'), 'required');

        if ($this->form_validation->run() == true) {

            $data     = [
                'name'                  => $this->input->post('name'),
                'parent_id'             => $this->input->post('parent_department') ? $this->input->post('parent_department') : 0,
                'code'                  => $this->sequenceCode->generate('DP', 3)
            ];

            $this->department_model->addDepartment($data);

            $this->session->set_flashdata('message', lang('Department added'));
            admin_redirect('departments');
        } else {
            $this->data['departments']  = $this->department_model->getAllDepartments();


            $bc = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('deals'), 'page' => lang('Deals')], ['link' => '#', 'page' => lang('Add Deals')]];
            $meta               = ['page_title' => lang('Add Deaks'), 'bc' => $bc];
            $this->page_construct('departments/add', $meta, $this->data);
        }
    }

    public function edit($id = null)
    {

        if ($this->input->post('id')) {
            $id = $this->input->post('id');
        }

        $this->form_validation->set_rules('name', lang('department_name'), 'required');


        if ($this->form_validation->run() == true) {


            $data     = [
                'name'                  => $this->input->post('name'),
                'parent_id'             => $this->input->post('parent_department') ? $this->input->post('parent_department') : 0
            ];

            $this->department_model->UpdateDepartment($id, $data);

            $this->session->set_flashdata('message', lang('Department Updated'));
            admin_redirect('departments');
        } else {
            $this->data['depart'] = $this->department_model->getDepartmentById($id);
            $this->data['departments']  = $this->department_model->getAllDepartments();
            $this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');
            $bc                  = [['link' => base_url(), 'page' => lang('home')], ['link' => '#', 'page' => lang('Edit Deal')]];
            $meta                = ['page_title' => lang('Edit Deal'), 'bc' => $bc];
            $this->page_construct('departments/edit', $meta, $this->data);
        }
    }


    public function delete($id = null)
    {
        //$this->sma->checkPermissions(null, true);

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        if (!$id) {
            $this->sma->send_json(['error' => 1, 'msg' => lang('id_not_found')]);
        }

        if ($this->department_model->deleteDepartment($id)) {
            
                $this->sma->send_json(['error' => 0, 'msg' => lang('Department Deleted')]);
                $this->session->set_flashdata('message', lang('Department Deleted'));
            admin_redirect('departments');
        }
    }
}
