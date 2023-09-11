<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Employees extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();

        if (!$this->loggedIn) {
            $this->session->set_userdata('requested_page', $this->uri->uri_string());
            $this->sma->md('login');
        }



        $this->load->library('form_validation');
        $this->load->admin_model('employee_model');

        // Sequence-Code
        $this->load->library('SequenceCode');
        $this->sequenceCode = new SequenceCode();
    }

    public function index()
    {

        $bc                     = [['link' => base_url(), 'page' => lang('home')], ['link' => '#', 'page' => lang('Employees')]];
        $meta                   = ['page_title' => lang('Employes'), 'bc' => $bc];
        $this->page_construct('employees/index', $meta, $this->data);
    }

    public function getEmployees()
    {
        $edit_link        = anchor('admin/employees/edit/$1', '<i class="fa fa-edit"></i> ' . lang('Edit Employee '));
        $delete_link      = "<a href='#' class='po' title='<b>" . $this->lang->line('Delete Employee') . "</b>' data-content=\"<p>"
            . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . admin_url('employees/delete/$1') . "'>"
            . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
            . lang('Delete Employee') . '</a>';
        $action = '<div class="text-center"><div class="btn-group text-left">'
            . '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
            . lang('actions') . ' <span class="caret"></span></button>
        <ul class="dropdown-menu pull-right" role="menu">
            <li>' . $edit_link . '</li>
            <li>' . $delete_link . '</li>
        </ul>
        </div></div>';

        $this->load->library('datatables');
        $this->datatables->select("{$this->db->dbprefix('employees')}.id as id, {$this->db->dbprefix('employees')}.id as empId, IFNULL(parent.name, 'N/A') AS parent_name, {$this->db->dbprefix('employees')}.name as name,{$this->db->dbprefix('employees')}.code as code ")
            ->from('sma_employees')
            ->join('sma_employees AS parent', "{$this->db->dbprefix('employees')}.parent_id = parent.id", 'left');
        $this->datatables->add_column('Actions', $action, 'empId');
        echo $this->datatables->generate();
    }

    public function add()
    {
        $this->form_validation->set_rules('name', lang('employee_name'), 'required');

        if ($this->form_validation->run() == true) {

            $data     = [
                'name'                  => $this->input->post('name'),
                'parent_id'             => 0, //$this->input->post('parent_department') ? $this->input->post('parent_department') : 0,
                'code'                  => $this->sequenceCode->generate('EM', 3)
            ];

            $this->employee_model->addEmployee($data);

            $this->session->set_flashdata('message', lang('Employee added'));
            admin_redirect('employees');
        } else {
            $this->data['employees']  = $this->employee_model->getAllEmployees();


            $bc = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('deals'), 'page' => lang('Deals')], ['link' => '#', 'page' => lang('Add Deals')]];
            $meta               = ['page_title' => lang('Add Deaks'), 'bc' => $bc];
            $this->page_construct('employees/add', $meta, $this->data);
        }
    }

    public function edit($id = null)
    {

        if ($this->input->post('id')) {
            $id = $this->input->post('id');
        }

        $this->form_validation->set_rules('name', lang('employee_name'), 'required');


        if ($this->form_validation->run() == true) {


            $data     = [
                'name'                  => $this->input->post('name'),
                'parent_id'             => 0 //$this->input->post('parent_department') ? $this->input->post('parent_department') : 0
            ];

            $this->employee_model->UpdateEmployee($id, $data);

            $this->session->set_flashdata('message', lang('Employee Updated'));
            admin_redirect('employees');
        } else {
            $this->data['employee'] = $this->employee_model->getEmployeeById($id);
            $this->data['employees']  = $this->employee_model->getAllEmployees();
            $this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');
            $bc                  = [['link' => base_url(), 'page' => lang('home')], ['link' => '#', 'page' => lang('Edit Employee')]];
            $meta                = ['page_title' => lang('Edit Employee'), 'bc' => $bc];
            $this->page_construct('employees/edit', $meta, $this->data);
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

        if ($this->employee_model->deleteEmployee($id)) {
            
                $this->sma->send_json(['error' => 0, 'msg' => lang('Employee Deleted')]);
                $this->session->set_flashdata('message', lang('Employee Deleted'));
            admin_redirect('employees');
        }
    }
}
