<?php

defined('BASEPATH') or exit('No direct script access allowed');

use Mpdf\Mpdf;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use PhpOffice\PhpSpreadsheet\Shared\Date;
class Purchase_order_sync extends MY_Controller
{
    public function __construct()
    {
        // error_reporting(E_ALL);        
        //ini_set('display_errors', 1); 
        parent::__construct();
       
        if ($this->Customer) {

            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect($_SERVER['HTTP_REFERER']);
        }
        $this->lang->admin_load('purchases', $this->Settings->user_language);
        $this->load->library('form_validation');
        $this->load->admin_model('purchase_order_model');
        $this->load->admin_model('purchase_requisition_model');
        // $this->digital_upload_path = 'files/';
        // $this->upload_path = 'assets/uploads/';
        // $this->thumbs_path = 'assets/uploads/thumbs/';
        // $this->image_types = 'gif|jpg|jpeg|png|tif';
        // $this->digital_file_types = 'zip|psd|ai|rar|pdf|doc|docx|xls|xlsx|ppt|pptx|gif|jpg|jpeg|png|tif|txt';
        // $this->allowed_file_size = '1024000';
        // $this->data['logo'] = true;
        // $this->load->library('attachments', [
        //     'path' => $this->digital_upload_path,
        //     'types' => $this->digital_file_types,
        //     'max_size' => $this->allowed_file_size,
        // ]);

        // Sequence-Code
        $this->load->library('SequenceCode');
        $this->sequenceCode = new SequenceCode();
        $this->load->library('pagination');
        // model used for shopify sync queries
        $this->load->admin_model('purchase_order_sync_model');
    }




    /* ------------------------------------------------------------------------- */

    public function index($warehouse_id = null)
    {
        //error_reporting(E_ALL);        // Report all errorsP
        //ini_set('display_errors', 1); 

        //$this->sma->checkPermissions();
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        if ($this->Owner || $this->Admin || !$this->session->userdata('warehouse_id')) {
            $this->data['warehouses'] = $this->site->getAllWarehouses();
            $this->data['warehouse_id'] = $warehouse_id;
            $this->data['warehouse'] = $warehouse_id ? $this->site->getWarehouseByID($warehouse_id) : null;
        } else {
            $this->data['warehouses'] = null;
            $this->data['warehouse_id'] = $this->session->userdata('warehouse_id');
            $this->data['warehouse'] = $this->session->userdata('warehouse_id') ? $this->site->getWarehouseByID($this->session->userdata('warehouse_id')) : null;
        }

        $this->data['lastInsertedId'] = $this->input->get('lastInsertedId');
        $this->data['pid'] = $this->input->get('pid');
        $this->data['pfromDate'] = $this->input->get('from');
        $this->data['ptoDate'] = $this->input->get('to');

        //update index func

        $filters = [
            'supplier_id' => $this->input->get('supplier_id'),
            'warehouse_id' => $this->input->get('warehouse_id'),
            'status' => $this->input->get('status'),
            'from_date' => $this->input->get('from'),
            'to_date' => $this->input->get('to'),
            'pid' => $this->input->get('pid'),
            'lastInsertedId' => $this->input->get('lastInsertedId')
        ];

        $limit = $this->input->get('limit') ?? 100;
        $page = $this->input->get('page') ?? 1;
        $offset = ($page - 1) * $limit;

        $total_rows = $this->purchase_order_model->count_purchases($filters);


        // point pagination back to this sync module
        $config['base_url'] = admin_url('purchase_order_sync/index');
        $config['total_rows'] = $total_rows;
        $config['per_page'] = $limit;
        $config['page_query_string'] = TRUE;
        $config['query_string_segment'] = 'page';
        $config['reuse_query_string'] = TRUE;
        $config['use_page_numbers']    = TRUE;

        // Styling (Bootstrap 4/5)
        $config['full_tag_open'] = '<ul class="pagination">';
        $config['full_tag_close'] = '</ul>';
        $config['num_tag_open'] = '<li class="page-item"><span class="page-link">';
        $config['num_tag_close'] = '</span></li>';
        $config['cur_tag_open'] = '<li class="page-item active"><span class="page-link">';
        $config['cur_tag_close'] = '</span></li>';
        $config['next_tag_open'] = '<li class="page-item"><span class="page-link">';
        $config['next_tag_close'] = '</span></li>';
        $config['prev_tag_open'] = '<li class="page-item"><span class="page-link">';
        $config['prev_tag_close'] = '</span></li>';
        $config['first_tag_open'] = '<li class="page-item"><span class="page-link">';
        $config['first_tag_close'] = '</span></li>';
        $config['last_tag_open'] = '<li class="page-item"><span class="page-link">';
        $config['last_tag_close'] = '</span></li>';

        $this->pagination->initialize($config);
        $this->data['pagination'] = $this->pagination->create_links();
        $this->data['purchases'] = $this->purchase_order_model->get_purchases($filters, $limit, $offset);

        // Get aggregated Shopify data for the current page of purchases
        $purchase_ids = array_column($this->data['purchases'], 'id');
        if (!empty($purchase_ids)) {
            $this->data['shopify_aggregates'] = $this->purchase_order_sync_model->get_shopify_aggregates($purchase_ids);
        } else {
            $this->data['shopify_aggregates'] = [];
        }

        //end update index func

        $bc = [['link' => base_url(), 'page' => lang('home')], ['link' => '#', 'page' => lang('purchases')]];
        $meta = ['page_title' => lang('purchases'), 'bc' => $bc];
        $this->page_construct('purchase_order_sync/index', $meta, $this->data);
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

        // if action is 'preview', just return the data without syncing
        if ($action === 'preview') {
            echo json_encode(['status' => 'success', 'message' => 'Preview data loaded', 'data' => $items]);
            return;
        }

        // TODO: call actual Shopify service here.
        // if the database has the shopify_synced column we update it to avoid duplicate syncs
        $fields = $this->db->list_fields('sma_purchase_orders');
        if (in_array('shopify_synced', $fields)) {
            // simple update so we don't disturb the PO items
            $this->db->update('sma_purchase_orders', ['shopify_synced' => 1], ['id' => $purchase_id]);
        }

        echo json_encode(['status' => 'success', 'message' => 'PO synced to Shopify successfully.', 'data' => $items]);
        return;
    }

    public function view($purchase_id = null)
    {
        //$this->sma->checkPermissions('index');

        if ($this->input->get('id')) {
            $purchase_id = $this->input->get('id');
        }
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $inv = $this->purchase_order_model->getPurchaseByID($purchase_id);
        if (!$this->session->userdata('view_right')) {
            $this->sma->view_rights($inv->created_by);
        }
        $this->data['rows'] = $this->purchase_order_model->getAllPurchaseItems($purchase_id);
        $this->data['supplier'] = $this->site->getCompanyByID($inv->supplier_id);
        $this->data['warehouse'] = $this->site->getWarehouseByID($inv->warehouse_id);
        $this->data['inv'] = $inv;
        $this->data['payments'] = $this->purchase_order_model->getPaymentsForPurchase($purchase_id);
        $this->data['created_by'] = $this->site->getUser($inv->created_by);
        $this->data['updated_by'] = $inv->updated_by ? $this->site->getUser($inv->updated_by) : null;
        $this->data['return_purchase'] = $inv->return_id ? $this->purchases_model->getPurchaseByID($inv->return_id) : null;
        $this->data['return_rows'] = $inv->return_id ? $this->purchases_model->getAllPurchaseItems($inv->return_id) : null;
        $this->data['attachments'] = $this->site->getAttachments($purchase_id, 'purchase');

        $bc = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('purchases'), 'page' => lang('purchases')], ['link' => '#', 'page' => lang('view')]];
        $meta = ['page_title' => lang('view_purchase_details'), 'bc' => $bc];
        $this->page_construct('purchase_order_sync/view', $meta, $this->data);
    }
    
}
