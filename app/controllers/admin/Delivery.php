<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Delivery extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->admin_model('delivery_model');
        $this->load->admin_model('sales_model');
        $this->load->library('form_validation');
        $this->load->library('datatables');
    }

    /**
     * Display all deliveries list
     */
    public function index()
    {
        $bc = [
            ['link' => base_url(), 'page' => lang('home')],
            ['link' => '#', 'page' => 'Deliveries']
        ];
        $meta = ['page_title' => 'Deliveries', 'bc' => $bc];
        $this->page_construct('delivery/index', $meta, $this->data);
    }

    /**
     * Get deliveries data for DataTable
     */
    public function get_deliveries()
    {
        $this->load->library('datatables');

        $this->datatables
            ->select('id, date_string, driver_name, truck_number, status, total_items_in_delivery_package, assigned_by')
            ->from('sma_deliveries')
            ->add_column('Actions', 
                "<div class='text-center'>" .
                "<a href='" . admin_url('delivery/view/$1') . "' class='tip' title='" . lang('view') . "'><i class='fa fa-eye'></i></a> " .
                "<a href='" . admin_url('delivery/edit/$1') . "' class='tip' title='" . lang('edit_notification') . "'><i class='fa fa-edit'></i></a> " .
                "<a href='" . admin_url('delivery/print/$1') . "' class='tip' title='Print Delivery Note'><i class='fa fa-print'></i></a> " .
                "<a href='#' class='tip po' title='<b>" . lang('delete_notification') . "</b>' data-content=\"<p>" . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . admin_url('delivery/delete/$1') . "'>" . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\" rel='popover'><i class='fa fa-trash-o'></i></a>" .
                "</div>", 
                'id')
            ->unset_column('id');

        echo $this->datatables->generate();
    }

    /**
     * Create a new delivery
     */
    public function add()
    {
        // Get all pending sales invoices
        $this->data['invoices'] = $this->sales_model->get_pending_invoices();
        
        $bc = [
            ['link' => base_url(), 'page' => lang('home')],
            ['link' => admin_url('delivery'), 'page' => 'Deliveries'],
            ['link' => '#', 'page' => 'Add Delivery']
        ];
        $meta = ['page_title' => 'Add Delivery', 'bc' => $bc];
        $this->page_construct('delivery/add', $meta, $this->data);
    }

    /**
     * Save a new delivery
     */
    public function save()
    {
        $this->form_validation->set_rules('driver_name', 'Driver Name', 'required|trim');
        $this->form_validation->set_rules('truck_number', 'Truck Number', 'required|trim');
        $this->form_validation->set_rules('status', 'Status', 'required');

        if ($this->form_validation->run() === false) {
            $this->session->set_flashdata('error', validation_errors());
            redirect(admin_url('delivery/add'));
            return;
        }

        $delivery_data = [
            'driver_name' => $this->input->post('driver_name'),
            'truck_number' => $this->input->post('truck_number'),
            'status' => $this->input->post('status', true) ?: 'pending',
            'date_string' => !empty($this->input->post('date_string')) ? $this->sma->fld($this->input->post('date_string')) : date('Y-m-d H:i:s'),
            'odometer' => $this->input->post('odometer'),
            'total_refrigerated_items' => $this->input->post('total_refrigerated_items', true) ?: 0
        ];

        // Get selected invoices
        $invoice_ids = $this->input->post('invoice_ids');
        $items = [];

        if (!empty($invoice_ids)) {
            foreach ($invoice_ids as $invoice_id) {
                $invoice = $this->sales_model->get_sale_by_id($invoice_id);
                if ($invoice) {
                    $items[] = [
                        'invoice_id' => $invoice_id,
                        'quantity_items' => $invoice->total_items ?? 0,
                        'refrigerated_items' => $this->input->post('refrigerated_items_' . $invoice_id, true) ?: 0
                    ];
                }
            }
        }

        $data = [
            'delivery' => $delivery_data,
            'items' => $items,
            'assigned_by' => $this->session->userdata('user_id')
        ];

        $delivery_id = $this->delivery_model->add_delivery($data);

        if ($delivery_id) {
            $this->session->set_flashdata('message', 'Delivery created successfully');
            redirect(admin_url('delivery/view/' . $delivery_id));
        } else {
            $this->session->set_flashdata('error', 'Failed to create delivery');
            redirect(admin_url('delivery/add'));
        }
    }

    /**
     * Edit an existing delivery
     */
    public function edit($delivery_id = null)
    {
        if (!$delivery_id) {
            $delivery_id = $this->input->post('id');
        }

        if (!$delivery_id) {
            $this->session->set_flashdata('error', 'Delivery ID not found');
            redirect(admin_url('delivery'));
        }

        $this->data['delivery'] = $this->delivery_model->get_delivery_by_id($delivery_id);
        $this->data['items'] = $this->delivery_model->get_delivery_items($delivery_id);
        $this->data['available_invoices'] = $this->sales_model->get_pending_invoices();

        if (!$this->data['delivery']) {
            $this->session->set_flashdata('error', 'Delivery not found');
            redirect(admin_url('delivery'));
        }

        $bc = [
            ['link' => base_url(), 'page' => lang('home')],
            ['link' => admin_url('delivery'), 'page' => 'Deliveries'],
            ['link' => '#', 'page' => 'Edit Delivery']
        ];
        $meta = ['page_title' => 'Edit Delivery', 'bc' => $bc];
        $this->page_construct('delivery/edit', $meta, $this->data);
    }

    /**
     * Update delivery information
     */
    public function update($delivery_id = null)
    {
        if (!$delivery_id) {
            $delivery_id = $this->input->post('id');
        }

        if (!$delivery_id) {
            $this->session->set_flashdata('error', 'Delivery ID not found');
            redirect(admin_url('delivery'));
        }

        $this->form_validation->set_rules('driver_name', 'Driver Name', 'required|trim');
        $this->form_validation->set_rules('truck_number', 'Truck Number', 'required|trim');

        if ($this->form_validation->run() === false) {
            $this->session->set_flashdata('error', validation_errors());
            redirect(admin_url('delivery/edit/' . $delivery_id));
            return;
        }

        $update_data = [
            'driver_name' => $this->input->post('driver_name'),
            'truck_number' => $this->input->post('truck_number'),
            'date_string' => !empty($this->input->post('date_string')) ? $this->sma->fld($this->input->post('date_string')) : null,
            'odometer' => $this->input->post('odometer'),
            'total_refrigerated_items' => $this->input->post('total_refrigerated_items', true) ?: 0
        ];

        if ($this->delivery_model->update_delivery($delivery_id, $update_data, $this->session->userdata('user_id'))) {
            $this->session->set_flashdata('message', 'Delivery updated successfully');
            redirect(admin_url('delivery/view/' . $delivery_id));
        } else {
            $this->session->set_flashdata('error', 'Failed to update delivery');
            redirect(admin_url('delivery/edit/' . $delivery_id));
        }
    }

    /**
     * View delivery details
     */
    public function view($delivery_id = null)
    {
        if (!$delivery_id) {
            $delivery_id = $this->input->post('id');
        }

        if (!$delivery_id) {
            $this->session->set_flashdata('error', 'Delivery ID not found');
            redirect(admin_url('delivery'));
        }

        $this->data['delivery'] = $this->delivery_model->get_delivery_by_id($delivery_id);
        $this->data['items'] = $this->delivery_model->get_delivery_items($delivery_id);
        $this->data['audit_logs'] = $this->delivery_model->get_delivery_audit_logs($delivery_id);
        $this->data['print_history'] = $this->delivery_model->get_delivery_print_history($delivery_id);

        if (!$this->data['delivery']) {
            $this->session->set_flashdata('error', 'Delivery not found');
            redirect(admin_url('delivery'));
        }

        $bc = [
            ['link' => base_url(), 'page' => lang('home')],
            ['link' => admin_url('delivery'), 'page' => 'Deliveries'],
            ['link' => '#', 'page' => 'View Delivery']
        ];
        $meta = ['page_title' => 'View Delivery', 'bc' => $bc];
        $this->page_construct('delivery/view', $meta, $this->data);
    }

    /**
     * Update delivery status
     */
    public function update_status($delivery_id = null)
    {
        if (!$delivery_id) {
            $delivery_id = $this->input->post('delivery_id');
        }

        $status = $this->input->post('status');

        if (!$delivery_id || !$status) {
            $this->sma->send_json(['error' => 1, 'msg' => 'Invalid parameters']);
        }

        if ($this->delivery_model->update_delivery_status($delivery_id, $status, $this->session->userdata('user_id'))) {
            $this->sma->send_json(['error' => 0, 'msg' => 'Status updated successfully']);
        } else {
            $this->sma->send_json(['error' => 1, 'msg' => 'Failed to update status']);
        }
    }

    /**
     * Add items to existing delivery
     */
    public function add_items($delivery_id = null)
    {
        if (!$delivery_id) {
            $delivery_id = $this->input->post('delivery_id');
        }

        if (!$delivery_id) {
            $this->sma->send_json(['error' => 1, 'msg' => 'Delivery ID not found']);
        }

        $invoice_ids = $this->input->post('invoice_ids');
        
        if (empty($invoice_ids)) {
            $this->sma->send_json(['error' => 1, 'msg' => 'No invoices selected']);
        }

        $items = [];
        foreach ($invoice_ids as $invoice_id) {
            $items[] = [
                'invoice_id' => $invoice_id,
                'quantity_items' => $this->input->post('quantity_items_' . $invoice_id, true) ?: 0,
                'refrigerated_items' => $this->input->post('refrigerated_items_' . $invoice_id, true) ?: 0
            ];
        }

        if ($this->delivery_model->add_items_to_delivery($delivery_id, $items, $this->session->userdata('user_id'))) {
            $this->sma->send_json(['error' => 0, 'msg' => 'Items added successfully']);
        } else {
            $this->sma->send_json(['error' => 1, 'msg' => 'Failed to add items']);
        }
    }

    /**
     * Remove an item from delivery
     */
    public function remove_item($delivery_id = null)
    {
        if (!$delivery_id) {
            $delivery_id = $this->input->post('delivery_id');
        }

        $invoice_id = $this->input->post('invoice_id');

        if (!$delivery_id || !$invoice_id) {
            $this->sma->send_json(['error' => 1, 'msg' => 'Invalid parameters']);
        }

        if ($this->delivery_model->remove_item_from_delivery($delivery_id, $invoice_id, $this->session->userdata('user_id'))) {
            $this->sma->send_json(['error' => 0, 'msg' => 'Item removed successfully']);
        } else {
            $this->sma->send_json(['error' => 1, 'msg' => 'Failed to remove item']);
        }
    }

    /**
     * Print delivery note and log the print action
     */
    public function print_delivery($delivery_id = null)
    {
        if (!$delivery_id) {
            $delivery_id = $this->input->get('id');
        }

        if (!$delivery_id) {
            $this->session->set_flashdata('error', 'Delivery ID not found');
            redirect(admin_url('delivery'));
        }

        $this->data['delivery'] = $this->delivery_model->get_delivery_by_id($delivery_id);
        $this->data['items'] = $this->delivery_model->get_delivery_items($delivery_id);

        if (!$this->data['delivery']) {
            $this->session->set_flashdata('error', 'Delivery not found');
            redirect(admin_url('delivery'));
        }

        // Log the print action
        $print_count = $this->input->post('print_count', true) ?: 1;
        $this->delivery_model->log_delivery_print($delivery_id, $this->session->userdata('user_id'), $print_count);

        // Load print view
        $this->load->view('delivery/print', $this->data);
    }

    /**
     * Export delivery to PDF (optional)
     */
    public function pdf($delivery_id = null)
    {
        if (!$delivery_id) {
            $delivery_id = $this->input->get('id');
        }

        if (!$delivery_id) {
            $this->session->set_flashdata('error', 'Delivery ID not found');
            redirect(admin_url('delivery'));
        }

        $this->data['delivery'] = $this->delivery_model->get_delivery_by_id($delivery_id);
        $this->data['items'] = $this->delivery_model->get_delivery_items($delivery_id);

        if (!$this->data['delivery']) {
            $this->session->set_flashdata('error', 'Delivery not found');
            redirect(admin_url('delivery'));
        }

        // Log the print action
        $this->delivery_model->log_delivery_print($delivery_id, $this->session->userdata('user_id'), 1);

        // Load PDF view
        $this->load->view('delivery/pdf', $this->data);
    }

    /**
     * Delete a delivery
     */
    public function delete($delivery_id = null)
    {
        if (!$delivery_id) {
            $delivery_id = $this->input->get('id');
        }

        if (!$delivery_id) {
            $this->sma->send_json(['error' => 1, 'msg' => 'Delivery ID not found']);
        }

        if ($this->delivery_model->delete_delivery($delivery_id, $this->session->userdata('user_id'))) {
            $this->sma->send_json(['error' => 0, 'msg' => 'Delivery deleted successfully']);
            $this->session->set_flashdata('message', 'Delivery deleted successfully');
            redirect(admin_url('delivery'));
        } else {
            $this->sma->send_json(['error' => 1, 'msg' => 'Failed to delete delivery']);
        }
    }

    /**
     * Get dashboard statistics
     */
    public function get_statistics()
    {
        $stats = $this->delivery_model->get_delivery_status_count();
        
        $this->sma->send_json(['error' => 0, 'data' => $stats]);
    }

    /**
     * Search deliveries
     */
    public function search()
    {
        $search_term = $this->input->get('q');
        
        if (empty($search_term)) {
            $this->sma->send_json(['error' => 1, 'msg' => 'Search term required']);
        }

        $results = $this->delivery_model->search_deliveries($search_term);
        
        $this->sma->send_json(['error' => 0, 'data' => $results]);
    }
}
