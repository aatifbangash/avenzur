<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Purchase_requisitions extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        if (!$this->loggedIn) {
            $this->session->set_userdata('requested_page', $this->uri->uri_string());
            $this->sma->md('admin/login');
        }
        $this->lang->admin_load('purchases', $this->Settings->user_language);
        $this->load->library('form_validation');
        $this->load->admin_model('purchase_requisitions_model');
        $this->load->admin_model('purchases_model');
        $this->load->library('SequenceCode');
        $this->sequenceCode = new SequenceCode();
    }

    public function index()
    {
        $this->sma->checkPermissions();
        $this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');

        $bc = [
            ['link' => base_url(), 'page' => lang('home')],
            ['link' => '#', 'page' => lang('purchase_requisitions')]
        ];
        $meta = ['page_title' => lang('purchase_requisitions'), 'bc' => $bc];
        $this->page_construct('purchase_requisitions/index', $meta, $this->data);
    }

    public function getPurchaseRequisitions()
    {
        $this->sma->checkPermissions('index');

        $detail_link = anchor('admin/purchase_requisitions/view/$1', '<i class="fa fa-file-text-o"></i> ' . lang('requisition_details'));
        $edit_link = anchor('admin/purchase_requisitions/edit/$1', '<i class="fa fa-edit"></i> ' . lang('edit_requisition'));
        $delete_link = "<a href='#' class='po' title='<b>" . lang('delete_requisition') . "</b>' data-content=\"<p>"
            . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . admin_url('purchase_requisitions/delete/$1') . "'>"
            . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
            . lang('delete_requisition') . '</a>';

        $action = '<div class="text-center"><div class="btn-group text-left">'
            . '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
            . lang('actions') . ' <span class="caret"></span></button>
        <ul class="dropdown-menu pull-right" role="menu">
            <li>' . $detail_link . '</li>
            <li>' . $edit_link . '</li>
            <li>' . $delete_link . '</li>
        </ul>
        </div></div>';

        $this->load->library('datatables');
        $this->datatables
            ->select("id, DATE_FORMAT(date, '%Y-%m-%d') as date, reference_no, department, requested_by, status, total_amount")
            ->from('purchase_requisitions')
            ->add_column('Actions', $action, 'id');

        echo $this->datatables->generate();
    }

    public function add()
    {
        $this->sma->checkPermissions();

        $this->form_validation->set_rules('department', lang('department'), 'required');
        $this->form_validation->set_rules('requested_by', lang('requested_by'), 'required');

        if ($this->form_validation->run() == true) {
            $reference = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->sequenceCode->generate('PR', 5);
            $date = $this->sma->fld(trim($this->input->post('date')));

            $data = [
                'reference_no' => $reference,
                'date' => $date,
                'department' => $this->input->post('department'),
                'requested_by' => $this->input->post('requested_by'),
                'status' => 'pending',
                'note' => $this->input->post('note'),
                'created_by' => $this->session->userdata('user_id'),
                'sequence_code' => $this->sequenceCode->generate('PRQ', 5)
            ];

            // Process items
            $i = sizeof($_POST['product']);
            $items = [];
            $total = 0;

            for ($r = 0; $r < $i; $r++) {
                $product_id = $_POST['product_id'][$r];
                $item_code = $_POST['product'][$r];
                $quantity = $_POST['quantity'][$r];
                $unit_cost = $_POST['unit_cost'][$r];

                $product_details = $this->purchases_model->getProductByCode($item_code);

                $item = [
                    'product_id' => $product_details->id,
                    'product_code' => $item_code,
                    'product_name' => $product_details->name,
                    'quantity' => $quantity,
                    'unit_cost' => $unit_cost,
                    'total' => $quantity * $unit_cost
                ];

                $items[] = $item;
                $total += ($quantity * $unit_cost);
            }

            $data['total_amount'] = $total;

            if ($this->purchase_requisitions_model->addRequisition($data, $items)) {
                $this->session->set_flashdata('message', lang('requisition_added'));
                admin_redirect('purchase_requisitions');
            }
        }

        $this->data['warehouses'] = $this->site->getAllWarehouses();
        $this->data['suppliers'] = $this->site->getAllCompanies('supplier');

        $bc = [
            ['link' => base_url(), 'page' => lang('home')],
            ['link' => admin_url('purchase_requisitions'), 'page' => lang('purchase_requisitions')],
            ['link' => '#', 'page' => lang('add_requisition')]
        ];
        $meta = ['page_title' => lang('add_requisition'), 'bc' => $bc];
        $this->page_construct('purchase_requisitions/add', $meta, $this->data);
    }
}