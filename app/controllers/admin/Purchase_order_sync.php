<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Purchase_order_sync extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();

        if ($this->Customer) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect($_SERVER['HTTP_REFERER']);
        }
        $this->lang->admin_load('purchases', $this->Settings->user_language);
        $this->load->library('form_validation');
        $this->load->admin_model('purchase_order_model');
        $this->load->admin_model('purchase_requisition_model');

        // Sequence-Code
        $this->load->library('SequenceCode');
        $this->sequenceCode = new SequenceCode();
        $this->load->library('pagination');
        // model used for shopify sync queries
        $this->load->admin_model('purchase_order_sync_model');
    }

    /**
     * Index page for purchase order sync
     * Displays aggregated Shopify data for selected purchase orders
     */
    public function index()
    {
        // Get aggregated Shopify data for the current page of purchases
        $purchase_ids = $this->input->get('pid');
        $this->data['lastInsertedId'] = $this->input->get('lastInsertedId');
        if (!empty($purchase_ids)) {
            $this->data['products'] = $this->purchase_order_sync_model->get_shopify_aggregates($purchase_ids);
        } else {
            $this->data['products'] = [];
        }

        $this->data['pid'] = $purchase_ids;
        $bc = [['link' => base_url(), 'page' => lang('home')], ['link' => '#', 'page' => lang('purchases')]];
        $meta = ['page_title' => lang('purchases'), 'bc' => $bc];
        $this->page_construct('purchase_order_sync/index', $meta, $this->data);
    }

    /**
     * View page for shelved purchase orders with pagination
     * Displays purchase orders that are shelved and synced
     */
    public function view()
    {
        $limit  = $this->input->get('limit') ?? 100;
        $page   = $this->input->get('page') ?? 1;
        $offset = ($page - 1) * $limit;

        // count only shelved purchase orders
        $total_rows = $this->purchase_order_sync_model->count_shelved_purchases();

        // Pagination config
        $config['base_url'] = admin_url('purchase_order_sync/view');
        $config['total_rows'] = $total_rows;
        $config['per_page'] = $limit;
        $config['page_query_string'] = TRUE;
        $config['query_string_segment'] = 'page';
        $config['use_page_numbers'] = TRUE;

        $config['full_tag_open'] = '<ul class="pagination">';
        $config['full_tag_close'] = '</ul>';
        $config['num_tag_open'] = '<li class="page-item"><span class="page-link">';
        $config['num_tag_close'] = '</span></li>';
        $config['cur_tag_open'] = '<li class="page-item active"><span class="page-link">';
        $config['cur_tag_close'] = '</span></li>';

        $this->pagination->initialize($config);

        $this->data['pagination'] = $this->pagination->create_links();

        // get only shelved purchase orders
        $this->data['purchases'] = $this->purchase_order_sync_model->get_shelved_purchases($limit, $offset);

        $bc = [
            ['link' => base_url(), 'page' => lang('home')],
            ['link' => '#', 'page' => lang('purchase_order_sync')]
        ];

        $meta = [
            'page_title' => 'PO Sync to Shopify',
            'bc' => $bc
        ];

        $this->page_construct('purchase_order_sync/view', $meta, $this->data);
    }

    /**
     * AJAX endpoint - perform validation and trigger Shopify sync
     * Expects POST purchase_id & optionally action=preview
     */
    public function sync()
    {
        $purchase_id = $this->input->post('purchase_id');
        $action = $this->input->post('action');

        if (!$purchase_id) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid purchase id']);
            return;
        }
        $inv = $this->purchase_order_model->getPurchaseByID($purchase_id);

        if (!$inv) {
            echo json_encode(['status' => 'error', 'message' => 'Purchase order not found']);
            return;
        }
        // validation: check if PO is shelved
        if (empty($inv->shelf_status) || $inv->shelf_status == 'NA') {
            echo json_encode([
                'status' => 'error',
                'message' => 'PO is not shelved yet.'
            ]);
            return;
        }

        // validation: check if already synced
        if ($this->purchase_order_sync_model->is_synced($purchase_id)) {
            echo json_encode([
                'status' => 'error',
                'message' => 'PO already synced to Shopify.'
            ]);
            return;
        }

        // fetch the item data required for Shopify
        $items = $this->purchase_order_sync_model->get_shopify_data($purchase_id);

        if (empty($items)) {
            echo json_encode([
                'status' => 'error',
                'message' => 'No PO items found for sync.'
            ]);
            return;
        }
        // if the database has the shopify_synced column we update it to avoid duplicate syncs
        $fields = $this->db->list_fields('sma_purchase_orders');
        if (in_array('shopify_synced', $fields)) {
            // simple update so we don't disturb the PO items
            $this->db->update('sma_purchase_orders', ['shopify_synced' => 1], ['id' => $purchase_id]);
        }

        echo json_encode(['status' => 'success', 'message' => 'PO synced to Shopify successfully.', 'data' => $items]);
        return;
    }
}
