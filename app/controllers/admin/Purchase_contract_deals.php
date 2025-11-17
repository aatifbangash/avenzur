<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Purchase_contract_deals extends MY_Controller
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
        $this->load->admin_model('purchase_contract_deals_model');
        $this->load->admin_model('products_model');
        $this->load->admin_model('purchase_order_model');
        $this->load->admin_model('purchases_model');
        $this->data['logo'] = true;

    }

    public function index()
    {
        //$this->sma->checkPermissions();
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $filters = [
            'from' => $this->input->get('from'),
            'to' => $this->input->get('to'),
        ];
        $limit = $this->input->get('limit') ?? 100;
        $page = $this->input->get('page') ?? 1;
        $offset = ($page - 1) * $limit;

        $total_rows = $this->purchase_contract_deals_model->count_deals($filters);

        $this->load->library('pagination');
        $config['base_url'] = admin_url('purchase_contract_deals/index');
        $config['total_rows'] = $total_rows;
        $config['per_page'] = $limit;
        $config['page_query_string'] = TRUE;
        $config['query_string_segment'] = 'page';
        $config['reuse_query_string'] = TRUE;
        $config['use_page_numbers']    = TRUE;
        $this->pagination->initialize($config);
        $this->data['pagination'] = $this->pagination->create_links();

        $this->data['deals'] = $this->purchase_contract_deals_model->get_deals($filters, $limit, $offset);

        $bc = [['link' => base_url(), 'page' => lang('home')], ['link' => '#', 'page' => lang('Contract Deals')]];
        $meta = ['page_title' => lang('Contract Deals'), 'bc' => $bc];
        $this->page_construct('purchase_contract_deals/index', $meta, $this->data);
    }

    public function add()
    {
        //$this->sma->checkPermissions();
        $this->form_validation->set_rules('supplier', lang('supplier'), 'required');

        if ($this->form_validation->run() == true) {
            $supplier_id = $this->input->post('supplier');
            $date = $this->sma->fld($this->input->post('date'));

            // Prepare deal data
            $data = [
                'supplier_id' => $supplier_id,
                'date'        => $date,
                'note'        => $this->sma->clear_tags($this->input->post('note')),
                'created_by'  => $this->session->userdata('user_id'),
            ];

            $items = $this->input->post('items') ?? [];

            // 🔹 Check if a deal already exists for this supplier
            $existing_deal = $this->db
                ->get_where('purchase_contract_deals', ['supplier_id' => $supplier_id])
                ->row();

            if ($existing_deal) {
                // 🔸 UPDATE existing deal
                $this->db->where('id', $existing_deal->id)->update('purchase_contract_deals', $data);
                $deal_id = $existing_deal->id;


                // 🔹 Handle deal items (update or insert)
                foreach ($items as $itemJson) {
                    $item = json_decode($itemJson, true);
                    $item_data = [
                        'deal_id'        => $deal_id,
                        'deal_type'      => $item['deal_type'],
                        'item_id'        => $item['item_id'],
                        'threshold'      => $item['threshold'],
                        'dis1_percentage' => $item['dis1_percentage'],
                        'dis2_percentage' => $item['dis2_percentage'],
                        'dis3_percentage' => $item['dis3_percentage'],
                        'deal_percentage' => $item['deal_percentage']
                    ];

                    $existing_item = $this->db
                        ->get_where('purchase_contract_deal_items', [
                            'deal_id' => $deal_id,
                            'item_id' => $item['item_id']
                        ])
                        ->row();

                    if ($existing_item) {
                        // Update existing deal item
                        $this->db->where('id', $existing_item->id)
                            ->update('purchase_contract_deal_items', $item_data);
                    } else {
                        // Insert new deal item
                        $this->db->insert('purchase_contract_deal_items', $item_data);
                    }
                }

                $this->session->set_flashdata('message', lang('deal_updated'));
            } else {
                // 🔸 INSERT new deal if not exists
                $this->db->insert('purchase_contract_deals', $data);
                $deal_id = $this->db->insert_id();

                // Insert all deal items
                foreach ($items as $itemJson) {
                    $item = json_decode($itemJson, true);
                    $item_data = [
                        'deal_id'        => $deal_id,
                        'item_id'        => $item['item_id'],
                        'deal_type'      => $item['deal_type'],
                        'threshold'      => $item['threshold'],
                        'dis1_percentage' => $item['dis1_percentage'],
                        'dis2_percentage' => $item['dis2_percentage'],
                        'dis3_percentage' => $item['dis3_percentage'],
                        'deal_percentage' => $item['deal_percentage']
                    ];
                    $this->db->insert('purchase_contract_deal_items', $item_data);
                }

                $this->session->set_flashdata('message', lang('deal_added'));
            }

            admin_redirect('purchase_contract_deals');
        } else {
            // 🔸 Form validation failed — load add page
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['suppliers'] = $this->site->getAllCompanies('supplier');
            $bc = [
                ['link' => base_url(), 'page' => lang('home')],
                ['link' => admin_url('purchase_contract_deals'), 'page' => lang('Contract Deals')],
                ['link' => '#', 'page' => lang('add')]
            ];
            $meta = ['page_title' => lang('add_deal'), 'bc' => $bc];
            $this->page_construct('purchase_contract_deals/add', $meta, $this->data);
        }
    }


    // public function add()
    // {
    //     $this->sma->checkPermissions();
    //     $this->form_validation->set_rules('supplier', lang('supplier'), 'required');

    //     if ($this->form_validation->run() == true) {

    //         $data = [
    //             'supplier_id' => $this->input->post('supplier'),
    //             'date' => $this->sma->fld($this->input->post('date')),
    //             'note' => $this->sma->clear_tags($this->input->post('note')),
    //             'created_by' => $this->session->userdata('user_id'),
    //         ];
    //         $items = $this->input->post('items') ?? [];
    //     }

    //     if ($this->form_validation->run() == true && $id = $this->purchase_contract_deals_model->addDeal($data, $items)) {
    //         $this->session->set_flashdata('message', $this->lang->line('deal_added'));
    //         admin_redirect('purchase_contract_deals');
    //     } else {
    //         $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
    //         $this->data['suppliers'] = $this->site->getAllCompanies('supplier');
    //         $bc = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('purchase_contract_deals'), 'page' => lang('Contract Deals')], ['link' => '#', 'page' => lang('add')]];
    //         $meta = ['page_title' => lang('add_deal'), 'bc' => $bc];
    //         $this->page_construct('purchase_contract_deals/add', $meta, $this->data);
    //     }
    // }

    public function edit($id = null)
    {
        $this->sma->checkPermissions();
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $deal = $this->purchase_contract_deals_model->getDealByID($id);
        if (!$deal) {
            $this->session->set_flashdata('error', 'Deal not found');
            admin_redirect('purchase_contract_deals');
        }
        $this->form_validation->set_rules('reference_no', lang('ref_no'), 'required');
        if ($this->form_validation->run() == true) {
            $data = [
                'reference_no' => $this->input->post('reference_no'),
                'supplier_id' => $this->input->post('supplier'),
                'date' => $this->sma->fld($this->input->post('date')),
                'note' => $this->sma->clear_tags($this->input->post('note')),
                'updated_by' => $this->session->userdata('user_id'),
                'updated_at' => date('Y-m-d H:i:s'),
            ];
            $items = $this->input->post('items') ?? [];
        }

        if ($this->form_validation->run() == true && $this->purchase_contract_deals_model->updateDeal($id, $data, $items)) {
            $this->session->set_flashdata('message', $this->lang->line('deal_updated'));
            admin_redirect('purchase_contract_deals');
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['deal'] = $deal;
            $this->data['suppliers'] = $this->site->getAllCompanies('supplier');
            $bc = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('purchase_contract_deals'), 'page' => lang('Contract Deals')], ['link' => '#', 'page' => lang('edit')]];
            $meta = ['page_title' => lang('edit_deal'), 'bc' => $bc];
            $this->page_construct('purchase_contract_deals/edit', $meta, $this->data);
        }
    }

    public function view($id = null)
    {
        $this->sma->checkPermissions();
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $deal = $this->purchase_contract_deals_model->getDealByID($id);
        if (!$deal) {
            $this->session->set_flashdata('error', 'Deal not found');
            admin_redirect('purchase_contract_deals');
        }
        $this->data['deal'] = $deal;
        $this->data['items'] = $this->purchase_contract_deals_model->getDealItems($id);
        $bc = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('purchase_contract_deals'), 'page' => lang('Contract Deals')], ['link' => '#', 'page' => lang('view')]];
        $meta = ['page_title' => lang('view_deal'), 'bc' => $bc];
        $this->page_construct('purchase_contract_deals/view', $meta, $this->data);
    }

    public function delete($id = null)
    {
        $this->sma->checkPermissions();
        if ($this->input->post('id')) {
            $id = $this->input->post('id');
        }
        if ($this->purchase_contract_deals_model->deleteDeal($id)) {
            $this->session->set_flashdata('message', $this->lang->line('deal_deleted'));
        } else {
            $this->session->set_flashdata('error', $this->lang->line('action_failed'));
        }
        admin_redirect('purchase_contract_deals');
    }

    // AJAX: return product suggestions filtered by supplier
    public function supplier_products()
    {
        $term = $this->input->get('term', true);
        $supplier_id = $this->input->get('supplier_id');
        if (strlen($term) < 1 || !$term) {
            $this->sma->send_json([['id' => 0, 'label' => lang('no_match_found'), 'value' => $term]]);
        }
        $term = addslashes($term);
        // reuse products_model method but filter by supplier columns if your products have supplier1..supplier5
        $rows = $this->products_model->getProductNamesBySupplier($term, $supplier_id);
        $results = [];
        if ($rows) {
            foreach ($rows as $row) {
                // if supplier_id provided, try to check supplier columns on products table
                $results[] = [
                    'id' => $row->id,
                    'label' => $row->name . ' (' . $row->code . ')',
                    'code' => $row->code,
                    'name' => $row->name,
                    'price' => $row->price,
                    'qty' => 1,
                    'deal_type' => $row->deal_type,
                    'dis1_percentage' => $row->dis1_percentage,
                    'dis2_percentage' => $row->dis2_percentage,
                    'dis3_percentage' => $row->dis3_percentage,
                    'deal_percentage' => $row->deal_percentage,
                    'threshold' => $row->threshold
                ];

                //$results[] = $row;
            }
        }
        if (empty($results)) {
            $this->sma->send_json([['id' => 0, 'label' => lang('no_match_found'), 'value' => $term]]);
        } else {
            $this->sma->send_json($results);
        }
    }

    // AJAX: get saved deal metadata for a supplier + product (latest)
    public function get_deal_item()
    {
        $product_id = $this->input->get('product_id');
        $supplier_id = $this->input->get('supplier_id');
        if (!$product_id) {
            $this->sma->send_json(['found' => false]);
        }
        $this->db->select('purchase_contract_deal_items.*, purchase_contract_deals.supplier_id, purchase_contract_deals.date as deal_date')
            ->from('purchase_contract_deal_items')
            ->join('purchase_contract_deals', 'purchase_contract_deals.id = purchase_contract_deal_items.deal_id', 'left')
            ->where('purchase_contract_deal_items.product_id', $product_id);
        if ($supplier_id) {
            $this->db->where('purchase_contract_deals.supplier_id', $supplier_id);
        }
        $this->db->order_by('purchase_contract_deals.date', 'DESC')->limit(1);
        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            $row = $q->row();
            // prepare response only with known fields
            $data = [];
            if (isset($row->deal_type)) {
                $data['deal_type'] = $row->deal_type;
            }
            if (isset($row->threshold)) {
                $data['threshold'] = $row->threshold;
            }
            if (isset($row->deal_percentage)) {
                $data['deal_percentage'] = $row->deal_percentage;
            }
            $this->sma->send_json(['found' => true, 'data' => $data]);
        } else {
            $this->sma->send_json(['found' => false]);
        }
    }
}
