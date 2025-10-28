<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Supplier_response extends CI_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->load->library('form_validation');
        $this->load->helper('url');
        $this->load->model('purchase_requisition_model');
        
    }

    public function submit($token = null)
    {
        if (!$token) {
            show_404();
        }

        // Check if token exists and is valid (not used)
        $supplier_request = $this->db->get_where('pr_supplier', [
            'response_token' => $token,
            'response_submitted' => 0
        ])->row();

        if (!$supplier_request) {
            // Token invalid or already used
            $this->data['error'] = 'This link is invalid or has already been used.';
            $this->load->view('supplier_response/error', $this->data);
            return;
        }

        if ($this->input->post()) {
            $this->form_validation->set_rules('unit_price[]', 'Unit Price', 'required|numeric');
            $this->form_validation->set_rules('dis1[]', 'Discount 1', 'numeric');
            $this->form_validation->set_rules('dis2[]', 'Discount 2', 'numeric');
            $this->form_validation->set_rules('dis3[]', 'Discount 3', 'numeric');
            $this->form_validation->set_rules('deal[]', 'Deal Percentage', 'numeric');
            $this->form_validation->set_rules('remarks', 'Remarks', 'trim');
            
            if ($this->form_validation->run() == TRUE) {
                $items = array();
                $unit_prices = $this->input->post('unit_price');
                $pr_items = $this->input->post('pr_item_id');
                $dis1 = $this->input->post('dis1');
                $dis2 = $this->input->post('dis2');
                $dis3 = $this->input->post('dis3');
                $deal = $this->input->post('deal');

                foreach ($pr_items as $key => $item_id) {
                    $items[] = array(
                        'pr_item_id' => $item_id,
                        'unit_price' => $unit_prices[$key],
                        'dis1' => isset($dis1[$key]) ? $dis1[$key] : 0,
                        'dis2' => isset($dis2[$key]) ? $dis2[$key] : 0,
                        'dis3' => isset($dis3[$key]) ? $dis3[$key] : 0,
                        'deal' => isset($deal[$key]) ? $deal[$key] : 0
                    );
                }

                // Save response
                $response_data = array(
                    'pr_id' => $supplier_request->pr_id,
                    'supplier_id' => $supplier_request->supplier_id,
                    'remarks' => $this->input->post('remarks'),
                    'items' => $items,
                    'submitted_at' => date('Y-m-d H:i:s')
                );

                if ($this->save_response($response_data)) {
                    // Mark token as used
                    $this->db->where('response_token', $token)
                            ->update('pr_supplier', ['response_submitted' => 1]);

                    $this->data['success'] = 'Your response has been submitted successfully.';
                    $this->load->view('supplier_response/success', $this->data);
                    return;
                }
            }
        }

        // Load PR details and items
        $this->data['pr'] = $this->purchase_requisition_model->get_by_id($supplier_request->pr_id);
        $this->data['items'] = $this->purchase_requisition_model->get_items($supplier_request->pr_id);
        $this->data['supplier'] = $this->db->get_where('companies', ['id' => $supplier_request->supplier_id])->row();
        $this->data['token'] = $token;

        $this->load->view('supplier_response/form', $this->data);
    }

    private function save_response($data)
    {
        $this->db->trans_start();

        // Insert main response
        $this->db->insert('purchase_requisition_supplier_response', [
            'pr_id' => $data['pr_id'],
            'supplier_id' => $data['supplier_id'],
            'remarks' => $data['remarks'],
            'submitted_at' => $data['submitted_at']
        ]);

        $response_id = $this->db->insert_id();

        // Insert response items with new discount fields
        foreach ($data['items'] as $item) {
            $this->db->insert('purchase_requisition_supplier_response_items', [
                'response_id' => $response_id,
                'pr_item_id' => $item['pr_item_id'],
                'unit_price' => $item['unit_price'],
                'dis1' => $item['dis1'],
                'dis2' => $item['dis2'],
                'dis3' => $item['dis3'],
                'deal' => $item['deal']
            ]);
        }

        $this->db->trans_complete();

        return $this->db->trans_status();
    }
}