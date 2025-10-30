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
            ->select("d.id, 
                  DATE_FORMAT(d.date_string, '%Y-%m-%d') as date, 
                  COALESCE(CONCAT(u.first_name, ' ', u.last_name), d.driver_name, 'N/A') as driver, 
                  COALESCE(CAST(dd.truck_id AS CHAR), d.truck_number, 'N/A') as truck", FALSE)
            ->from('sma_deliveries d')
            ->join('sma_delivery_driver dd', 'd.driver_id = dd.id', 'left')
            ->join('sma_users u', 'u.id = (SELECT id FROM sma_users WHERE group_id = dd.groupId LIMIT 1)', 'left')
            ->add_column('Actions',
                "<div class='text-center'>" .
                "<a href='" . admin_url('delivery/view/$1') . "' class='tip' title='View' data-toggle='tooltip'><i class='fa fa-eye'></i></a> " .
                "<a href='" . admin_url('delivery/edit/$1') . "' class='tip' title='Edit' data-toggle='tooltip'><i class='fa fa-edit'></i></a> " .
                "<a href='" . admin_url('delivery/print_delivery/$1') . "' target='_blank' class='tip' title='Print' data-toggle='tooltip'><i class='fa fa-print'></i></a> " .
                "<a href='#' onclick=\"if(confirm('Are you sure?')) window.location.href='" . admin_url('delivery/delete/$1') . "'\" class='tip' title='Delete' data-toggle='tooltip'><i class='fa fa-trash-o'></i></a>" .
                "</div>",
                'd.id');

        echo $this->datatables->generate();
    }

    /**
     * Create a new delivery
     */
    public function add()
    {
        // Get all pending sales invoices
        $this->db->select('s.id, s.reference_no, s.date as sale_date, s.grand_total as total_amount, s.total_items, c.name as customer_name');
        $this->db->from('sma_sales s');
        $this->db->join('sma_companies c', 's.customer_id = c.id', 'left');
        $this->db->where_in('s.payment_status', ['pending', 'due', 'partial']);
        $this->db->order_by('s.date', 'asc');
        $invoices_query = $this->db->get();
        if ($invoices_query === false) {
            $error = $this->db->error();
            log_message('error', 'Delivery add() DB error: ' . json_encode($error));
            $this->data['invoices'] = [];
        } else {
            $invoices = $invoices_query->result();
            // Add assignment status for each invoice
            foreach ($invoices as $invoice) {
                $assignment = $this->delivery_model->get_invoice_assignment_status($invoice->id);
                if ($assignment) {
                    $invoice->current_driver_name = $assignment['driver_name'];
                    $invoice->delivery_status = $assignment['delivery_status'];
                } else {
                    $invoice->current_driver_name = null;
                    $invoice->delivery_status = null;
                }
            }

            $this->data['invoices'] = $invoices;
        }

        // Get registered drivers from the driver group
        $this->data['drivers'] = $this->get_registered_drivers();

        $bc = [
            ['link' => base_url(), 'page' => lang('home')],
            ['link' => admin_url('delivery'), 'page' => 'Deliveries'],
            ['link' => '#', 'page' => 'Add Delivery']
        ];
        $meta = ['page_title' => 'Add Delivery', 'bc' => $bc];
        $this->data['page_title'] = 'Add Delivery';
        $this->page_construct('delivery/add', $meta, $this->data);
    }

    /**
     * Get registered drivers with their truck information
     * Only users in the 'driver' group
     */
    private function get_registered_drivers()
    {
        // First, get the group_id for 'driver' group
        $this->db->select('id');
        $this->db->from('sma_groups');
        $this->db->where('name', 'driver');
        $group_query = $this->db->get();

        if ($group_query->num_rows() === 0) {
            log_message('error', 'Driver group not found in sma_groups table');
            return [];
        }

        $driver_group_id = $group_query->row()->id;

        // Get all drivers with their truck information
        $this->db->select('dd.id, dd.groupId, dd.truck_id, dd.license_number, u.first_name, u.last_name, u.email, u.username');
        $this->db->from('sma_delivery_driver dd');
        $this->db->join('sma_users u', 'dd.groupId = u.group_id AND u.group_id = ' . $driver_group_id, 'inner');
        $this->db->where('dd.groupId', $driver_group_id);
        $this->db->order_by('u.first_name', 'asc');

        $drivers_query = $this->db->get();

        if ($drivers_query === false) {
            $error = $this->db->error();
            log_message('error', 'Failed to fetch drivers: ' . json_encode($error));
            return [];
        }

        return $drivers_query->result();
    }

    /**
     * Save a new delivery
     */
    public function save()
    {
        $this->form_validation->set_rules('driver_id', 'Driver', 'required|integer');
        $this->form_validation->set_rules('status', 'Status', 'required');

        if ($this->form_validation->run() === false) {
            $this->session->set_flashdata('error', validation_errors());
            redirect(admin_url('delivery/add'));
            return;
        }

        // Verify that the selected driver exists and belongs to the driver group
        $driver_id = $this->input->post('driver_id');
        $driver = $this->verify_driver($driver_id);

        if (!$driver) {
            $this->session->set_flashdata('error', 'Invalid driver selected. Please select a valid registered driver.');
            redirect(admin_url('delivery/add'));
            return;
        }

        $delivery_data = [
            'driver_id' => $driver_id,
            'status' => $this->input->post('status', true) ?: 'pending',
            'date_string' => !empty($this->input->post('date_string')) ? $this->sma->fld($this->input->post('date_string')) : date('Y-m-d H:i:s'),
            'assigned_by' => $this->session->userdata('user_id')
        ];

        // Add optional fields only if they are provided
        $odometer = $this->input->post('odometer');
        if (!empty($odometer)) {
            $delivery_data['odometer'] = $odometer;
        }

        $total_refrigerated = $this->input->post('total_refrigerated_items');
        if (!empty($total_refrigerated)) {
            $delivery_data['total_refrigerated_items'] = $total_refrigerated;
        }

        // Get selected invoices
        $invoice_ids = $this->input->post('invoice_ids');
        $items = [];

        if (!empty($invoice_ids)) {
            foreach ($invoice_ids as $invoice_id) {
                $invoice = $this->sales_model->getSaleByID($invoice_id);
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
            'items' => $items
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
     * Verify that a driver ID is valid and belongs to the driver group
     */
    private function verify_driver($driver_id)
    {
        // Get the driver group ID
        $this->db->select('id');
        $this->db->from('sma_groups');
        $this->db->where('name', 'driver');
        $group_query = $this->db->get();

        if ($group_query->num_rows() === 0) {
            return false;
        }

        $driver_group_id = $group_query->row()->id;

        // Verify the driver exists and belongs to the correct group
        $this->db->select('dd.id, dd.groupId, dd.truck_id, u.first_name, u.last_name');
        $this->db->from('sma_delivery_driver dd');
        $this->db->join('sma_users u', 'dd.groupId = u.group_id', 'inner');
        $this->db->where('dd.id', $driver_id);
        $this->db->where('dd.groupId', $driver_group_id);

        $query = $this->db->get();

        if ($query->num_rows() > 0) {
            return $query->row();
        }

        return false;
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

        // Get registered drivers for dropdown
        $this->data['drivers'] = $this->get_registered_drivers();

        // Get all pending sales invoices for adding new items
        $this->db->select('s.id, s.reference_no, s.date as sale_date, s.grand_total as total_amount, s.total_items, c.name as customer_name, 
                          IF(di.id IS NOT NULL, di.delivery_id, NULL) as assigned_delivery_id, 
                          IF(di.id IS NOT NULL, d.id, NULL) as current_delivery_id,
                          IF(di.id IS NOT NULL, d.driver_id, NULL) as current_driver_id,
                          IF(di.id IS NOT NULL, d.status, NULL) as delivery_status');
        $this->db->from('sma_sales s');
        $this->db->join('sma_companies c', 's.customer_id = c.id', 'left');
        $this->db->join('sma_delivery_items di', 's.id = di.invoice_id', 'left');
        $this->db->join('sma_deliveries d', 'di.delivery_id = d.id', 'left');
        $this->db->where_in('s.payment_status', ['pending', 'due', 'partial']);
        $this->db->order_by('s.date', 'asc');
        $invoices_query = $this->db->get();
        $this->data['available_invoices'] = $invoices_query->result();

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
     * Update an existing delivery
     */
    public function update($delivery_id = null)
    {
        if (!$delivery_id) {
            $delivery_id = $this->input->post('delivery_id');
        }

        if (!$delivery_id) {
            $this->session->set_flashdata('error', 'Delivery ID not found');
            redirect(admin_url('delivery'));
            return;
        }

        $this->form_validation->set_rules('driver_id', 'Driver', 'required|integer');
        $this->form_validation->set_rules('status', 'Status', 'required');

        if ($this->form_validation->run() === false) {
            $this->session->set_flashdata('error', validation_errors());
            redirect(admin_url('delivery/edit/' . $delivery_id));
            return;
        }

        // Verify driver
        $driver_id = $this->input->post('driver_id');
        $driver = $this->verify_driver($driver_id);

        if (!$driver) {
            $this->session->set_flashdata('error', 'Invalid driver selected.');
            redirect(admin_url('delivery/edit/' . $delivery_id));
            return;
        }

        $delivery_data = [
            'driver_id' => $driver_id,
            'status' => $this->input->post('status', true),
            'date_string' => $this->sma->fld($this->input->post('date_string')),
            'updated_by' => $this->session->userdata('user_id'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        if ($this->delivery_model->update_delivery($delivery_id, $delivery_data)) {
            $this->session->set_flashdata('message', 'Delivery updated successfully');
            redirect(admin_url('delivery/view/' . $delivery_id));
        } else {
            $this->session->set_flashdata('error', 'Failed to update delivery');
            redirect(admin_url('delivery/edit/' . $delivery_id));
        }
    }

    /**
     * View a single delivery with all details
     */
    public function view($delivery_id = null)
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

    /**
     * Get last odometer reading for a driver (AJAX)
     */
    public function get_last_odometer()
    {
        $driver_id = $this->input->post('driver_id');

        if (!$driver_id) {
            echo json_encode(['success' => false, 'message' => 'Driver ID required']);
            return;
        }

        $this->db->select('odometer, DATE_FORMAT(date_string, "%Y-%m-%d %H:%i") as date', FALSE);
        $this->db->from('sma_deliveries');
        $this->db->where('driver_id', $driver_id);
        $this->db->where('odometer IS NOT NULL');
        $this->db->where('odometer > 0');
        $this->db->order_by('date_string', 'DESC');
        $this->db->limit(1);

        $query = $this->db->get();

        if ($query && $query->num_rows() > 0) {
            $result = $query->row_array();
            echo json_encode([
                'success' => true,
                'data' => [
                    'odometer' => $result['odometer'],
                    'date' => $result['date']
                ]
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'No previous odometer reading found']);
        }
    }
}
