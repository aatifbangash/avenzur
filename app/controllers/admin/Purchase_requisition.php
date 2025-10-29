<?php
defined('BASEPATH') OR exit('No direct script access allowed');
use Mpdf\Mpdf;

class Purchase_requisition extends MY_Controller {

    public function __construct()
    { 
        error_reporting(E_ALL);        // Report all errors
        ini_set('display_errors', 1); 
        parent::__construct();
        $this->load->admin_model('users_model');
        $this->load->admin_model('pr_audit_logs_model');
        $this->load->admin_model('purchase_requisition_model');
        $this->load->admin_model('companies_model');
         $this->load->admin_model('products_model');
    }

    public function create()
    {
        if ($this->input->post()) {
            
            
            $data = [
                'pr_number' => 'PR-' . time(),
                'requested_by' => $this->session->userdata('user_id'),
                'warehouse_id' => $this->input->post('warehouse_id'),
                
                'remarks' => $this->input->post('remarks'),
                'status' => 'submitted'
            ];

            $items = $this->input->post('items');
            $id = $this->purchase_requisition_model->create_requisition($data, $items);


            if ($id) {
                $this->session->set_flashdata('message', 'Purchase Requisition created successfully!');
                admin_redirect('purchase_requisition/');
            } else {
                $this->session->set_flashdata('error', 'Error while creating requisition.');
            }
        }

        $this->data['warehouses'] = $this->site->getAllWarehouses();
        $this->data['reference_no'] = 'PR-' . time();
        //print_r($this->data['warehouses']); exit;
        
        $bc = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('purchases'), 'page' => lang('purchases')], ['link' => '#', 'page' => lang('add_purchase')]];
        $meta = ['page_title' => lang('add_purchase'), 'bc' => $bc];
        $this->page_construct('purchase_requisition/add', $meta, $this->data);
        
    }

    public function edit($id = null)
{
    if (!$id) {
        show_404();
    }

    // Get existing requisition
    $requisition = $this->purchase_requisition_model->get_by_id($id);
    if (!$requisition) {
        $this->session->set_flashdata('error', 'Purchase Requisition not found.');
        admin_redirect('purchase_requisition');
    }

    if ($this->input->post()) {

        $data = [
            'warehouse_id' => $this->input->post('warehouse_id'),
            'remarks' => $this->input->post('remarks'),
            'updated_at' => date('Y-m-d H:i:s'),
            'updated_by' => $this->session->userdata('user_id'),
        ];

        $items = $this->input->post('items'); // array of items same as in create

        $updated = $this->purchase_requisition_model->update_requisition($id, $data, $items);

        if ($updated) {
            $this->session->set_flashdata('message', 'Purchase Requisition updated successfully!');
            admin_redirect('purchase_requisition');
        } else {
            $this->session->set_flashdata('error', 'Error while updating requisition.');
        }
    }

    // Load warehouses and existing items for the edit form
    $this->data['warehouses'] = $this->site->getAllWarehouses();
    $this->data['requisition'] = $requisition;
    $this->data['items'] = $this->purchase_requisition_model->get_items($id);

    $bc = [['link' => base_url(), 'page' => lang('home')],
           ['link' => admin_url('purchase_requisition'), 'page' => lang('purchase_requisitions')],
           ['link' => '#', 'page' => lang('edit_purchase_requisition')]];

    $meta = ['page_title' => lang('edit_purchase_requisition'), 'bc' => $bc];
    $this->page_construct('purchase_requisition/edit', $meta, $this->data);
}


     public function index() {
        //error_reporting(E_ALL);        // Report all errors
        //ini_set('display_errors', 1); 
        $this->data['requisitions'] = $this->purchase_requisition_model->get_all();
      
        $meta = ['page_title' => 'Purchase Requisitions', 'bc' => [['link' => '#', 'page' => 'Purchase Requisitions']]];
        $this->page_construct('purchase_requisition/index', $meta, $this->data);
    }



public function save($id = null)
{
    $isEdit = !empty($id);

    if ($this->input->post()) {
        // Common data preparation
        $data = [
            'warehouse_id' => $this->input->post('warehouse_id'),
            'remarks' => $this->input->post('remarks'),
            'expected_date' => $this->input->post('expected_date'),
            'priority' => $this->input->post('priority'),
            'department' => $this->input->post('department')        ];
     
        

        $items = $this->input->post('items');

        // Route to appropriate function
        if ($isEdit) {
            $this->update_requisition($id, $data, $items);
        } else {
            $this->create_requisition($data, $items);
        }
    } else {
        // Load form for create or edit
        $this->load_form($id, $isEdit);
    }
}

private function enrich_items_with_product_names($items)
{
    if (empty($items)) {
        return $items;
    }

    $this->load->admin_model('products_model');

    $product_ids = array_unique(array_column($items, 'product_id'));
    
    if (empty($product_ids)) {
        return $items;
    }

    $products = $this->products_model->get_products_by_ids($product_ids);
    
    if (!$products) {
        return $items;
    }

    $product_map = [];
    foreach ($products as $product) {
        $product_map[$product->id] = [
            'name' => $product->name,
            'code' => $product->code
        ];
    }

    foreach ($items as $key => $item) {
        if (isset($item['product_id']) && isset($product_map[$item['product_id']])) {
            $items[$key]['product_name'] = $product_map[$item['product_id']]['name'];
            $items[$key]['product_code'] = $product_map[$item['product_id']]['code'];
        }
    }

    return $items;
}




/**
 * Create new purchase requisition
 */
private function create_requisition($data, $items)
{
    

    // Generate PR number
    $pr_number_temp = 'PR-' . time();
    $data['pr_number'] = $pr_number_temp;
    $data['requested_by'] = $this->session->userdata('user_id');
    $data['status'] = 'submitted';
    $data['created_at'] = date('Y-m-d H:i:s');

    // Insert requisition and items
    $pr_id = $this->purchase_requisition_model->create_requisition($data, $items);

    if ($pr_id) {
        
        // Generate PDF
        $this->generate_pr_pdf($pr_id, $data, $items);

        // Create audit log
        $this->log_audit($pr_id, 'Purchase Requisition Created', 'Request generated by ' . $this->session->userdata('username'));

        $this->session->set_flashdata('message', 'Purchase Requisition created successfully!');
        admin_redirect('purchase_requisition');
    } else {
        $this->session->set_flashdata('error', 'Error while creating requisition.');
        admin_redirect('purchase_requisition/add');
    }
}

/**
 * Update existing purchase requisition
 */
private function update_requisition($id, $data, $items)
{
    
    // Add update metadata
    $data['updated_at'] = date('Y-m-d H:i:s');
    $data['updated_by'] = $this->session->userdata('user_id');

    // Update requisition and items
    $result = $this->purchase_requisition_model->update_requisition($id, $data, $items);
    
    $data['pr_number'] = $this->purchase_requisition_model->get_by_id($id)->pr_number;

    if ($result) {
        // Regenerate PDF
        $this->generate_pr_pdf($id, $data, $items);

        // Create audit log
        $this->log_audit($id, 'Purchase Requisition Updated', 'Request updated by ' . $this->session->userdata('username'));

        $this->session->set_flashdata('message', 'Purchase Requisition updated successfully!');
        admin_redirect('purchase_requisition');
    } else {
        $this->session->set_flashdata('error', 'Error while updating requisition.');
        admin_redirect('purchase_requisition/edit/' . $id);
    }
}

/**
 * Generate PDF for purchase requisition
 */
private function generate_pr_pdf($pr_id, $data, $items)
{
    $items = $this->enrich_items_with_product_names($items);

    // Setup PDF directory
    $pdfDir = FCPATH . 'assets/uploads/pr_pdfs/';
    if (!is_dir($pdfDir)) {
        mkdir($pdfDir, 0755, true);
    }

    $prFile = $pdfDir . 'PR_' . $pr_id . '.pdf';

    // Delete old PDF if exists
    if (file_exists($prFile)) {
        unlink($prFile);
    }

    // Prepare data for PDF view
    $this->data['pr'] = $data;
    $this->data['items'] = $items;

    // Generate HTML from view
    $html = $this->load->view($this->theme . 'purchase_requisition/pdf_template', $this->data, true);

    // Generate PDF
    $mpdf = new Mpdf([
        'format' => 'A4',
        'margin_top' => 80,
        'margin_bottom' => 70,
    ]);

    $mpdf->WriteHTML($html);
    $mpdf->Output($prFile, \Mpdf\Output\Destination::FILE);

    return $prFile;
}

/**
 * Create audit log entry
 */
private function log_audit($pr_id, $action, $details)
{
    $audit_log = [
        'pr_id' => $pr_id,
        'action' => $action,
        'details' => $details,
        'done_by' => $this->session->userdata('user_id'),
        'created_at' => date('Y-m-d H:i:s')
    ];

    $this->db->insert('pr_audit_logs', $audit_log);
}

/**
 * Load form for create or edit
 */
private function load_form($id, $isEdit)
{
    $this->data['warehouses'] = $this->site->getAllWarehouses();
    $this->data['reference_no'] = $isEdit ? '' : 'PR-' . time();

    if ($isEdit) {
        $this->data['requisition'] = $this->purchase_requisition_model->get_by_id($id);
        $this->data['items'] = $this->purchase_requisition_model->get_items($id);
    } else {
        $this->data['requisition'] = null;
        $this->data['items'] = [];
    }

    $page = 'purchase_requisition/form';
    $meta = ['page_title' => $isEdit ? 'Edit Requisition' : 'Create Requisition'];
    $this->page_construct($page, $meta, $this->data);
}



    /**
     * View single requisition
     */
    public function view($id) {
        $this->data['requisition'] = $this->purchase_requisition_model->get_by_id($id);
        if (!$this->data['requisition']) {
            show_404();
        }
        //   error_reporting(E_ALL);        // Report all errors
        // ini_set('display_errors', 1); 
        $this->data['items'] = $this->purchase_requisition_model->get_items($id);
    
        $this->data['suppliers'] = $this->companies_model->getAllSupplierCompanies();
// $this->data['logs'] = $this->pr_audit_logs_model->get_by_pr_number($this->data['requisition']->pr_number);
        $logs = $this->pr_audit_logs_model->get_by_pr_number($this->data['requisition']->pr_number);

        $requisitionSupplierList = $this->purchase_requisition_model->get_suppliers_for_requisition($id);

        $this->data['logs'] = $logs;
        $this->data['id'] = $id;
        $this->data['requisition_suppliers'] = $requisitionSupplierList;
        //print_r( $this->data['requisition_suppliers']); exit;
        $meta = ['page_title' => 'View Requisition', 'bc' => [['link' => admin_url('purchase_requisition'), 'page' => 'Purchase Requisitions'], ['link' => '#', 'page' => 'View Requisition']]];
        $this->page_construct('purchase_requisition/view', $meta, $this->data);

    }

    public function upload_supplier_docs() {
        // Only allow AJAX POST
        if (!$this->input->is_ajax_request()) {
            show_error('No direct script access allowed');
        }

        $pr_id = $this->input->post('pr_id');
        $supplier_id = $this->input->post('supplier_id');
        $supplier_name = $this->input->post('supplier_name');
        $doc_name = $this->input->post('doc_name'); 

        if (empty($supplier_id) || empty($doc_name) || empty($_FILES['file']['name'])) {
            echo json_encode(['status' => 'error', 'message' => 'Missing required data.']);
            return;
        }

        // Prepare upload directory per supplier
        $upload_path = './assets/uploads/pr_pdfs/supplier_docs/';
        if (!is_dir($upload_path)) {
            mkdir($upload_path, 0777, true);
        }

        $this->load->library('upload');

        $config['upload_path']   = $upload_path;
        $config['allowed_types'] = 'pdf';
        $config['max_size']      = 10240; // 10 MB max
        $config['file_name']     = time().'_'.$_FILES['file']['name'];

        $this->upload->initialize($config);

        if ($this->upload->do_upload('file')) {
            $file_data = $this->upload->data();
            $file_path = 'supplier_docs/'.$file_data['file_name'];

            // Save to database
            $data = [
                'pr_id'         => $pr_id,
                'supplier_id'   => $supplier_id,
                'doc_name'      => $doc_name,
                'pdf_path'      => $file_path,
                'uploaded_by'    => $this->session->userdata('user_id'),
                'uploaded_at'   => date('Y-m-d H:i:s')
            ];

            $this->db->insert('purchase_requisition_supplier_response', $data);

            // auidt log 
            $audit_log = array(
                'pr_id' => $pr_id,
                'action' => 'Supplier Document Uploaded',
                'details' => 'Document "'.$doc_name.'" uploaded for Supplier : '.$supplier_name,
                'done_by' => $this->session->userdata('user_id'),
                'created_at' => date('Y-m-d H:i:s')
            );

            $this->db->insert('pr_audit_logs', $audit_log);


            echo json_encode(['status' => 'success', 'message' => 'File uploaded successfully.', 'file_path' => base_url($file_path)]);
        } else {
            // Upload failed
            echo json_encode(['status' => 'error', 'message' => $this->upload->display_errors('','')]);
        }
    }

    /**
     * Delete requisition
     */
    public function delete($id) {
        $this->db->where('id', $id);
        $this->db->delete('purchase_requisitions');
        $this->session->set_flashdata('message', 'Requisition deleted successfully');
        redirect(admin_url('purchase_requisition'));
    }

    // public function view($id)
    // {
    //     $data['requisition'] = $this->purchase_requisition_model->get_requisition($id);
        
    //     $meta = ['page_title' => 'View Requisition', 'bc' => [['link' => admin_url('purchase_requisition'), 'page' => 'Purchase Requisitions'], ['link' => '#', 'page' => 'View Requisition']]];
    //     $this->page_construct('purchase_requisition/view', $meta, $this->data);
    // }

    public function view_pdf($id)
{
    $file_path = FCPATH . 'assets/uploads/pr_pdfs/PR_' . $id . '.pdf';

    if (file_exists($file_path)) {
        // Open in browser
        header('Content-Type: application/pdf');
        header('Content-Disposition: inline; filename="purchase_request_' . $id . '.pdf"');
        readfile($file_path);
    } 
}

public function download_pdf($id)
{
    $file_path = FCPATH . 'assets/uploads/pr_pdfs/PR_' . $id . '.pdf';
   
    if (file_exists($file_path)) {
        // Force download
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="purchase_request_' . $id . '.pdf"');
        readfile($file_path);
    } 
}


public function search_product()
    {
        $query = $this->input->get('q');
        $this->db->like('name', $query);
        $products = $this->db->get('products')->result();

        echo json_encode($products);
    }

public function send_to_supplier() {
    $supplier_ids = $this->input->post('supplier_id');
    $subject = $this->input->post('subject');
    $message = $this->input->post('remarks');
    $pr_id = $this->input->post('pr_id');
    $attachment = FCPATH . 'assets/uploads/pr_pdfs/PR_' . $pr_id . '.pdf';

    // Validate inputs
    if (empty($supplier_ids) || empty($pr_id)) {
        $this->session->set_flashdata('error', 'Invalid request. Missing required data.');
        admin_redirect('purchase_requisition/view/' . $pr_id);
        return;
    }

    if (!file_exists($attachment)) {
        $this->session->set_flashdata('error', 'PDF attachment not found. Please regenerate it first.');
        admin_redirect('purchase_requisition/view/' . $pr_id);
        return;
    }

    // Load email library
    $this->load->library('email');
    
    $success_count = 0;
    $failed_suppliers = [];

    foreach ($supplier_ids as $supplier_id) {
        // Fetch supplier details
        $supplier = $this->purchase_requisition_model->getSupplierById($supplier_id);
        
        if ($supplier && !empty($supplier->email)) {
            // Generate unique token
            $response_token = bin2hex(random_bytes(32));
            $response_url = site_url('supplier_response/submit/' . $response_token);
            
            $email_to = $supplier->email;
            $email_subject = $subject ?: 'Purchase Requisition - ' . $pr_id;
            $email_message = "
                <html>
                <body>
                    <p>Dear {$supplier->name},</p>
                    <p>{$message}</p>
                    <p>Please find attached the Purchase Requisition (PR #{$pr_id}).</p>
                    <p>Click the button below to submit your quotation:</p>
                    <p><a href='{$response_url}' style='background-color: #4CAF50; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Submit Quotation</a></p>
                    <p><small>Note: This link can only be used once to submit your response.</small></p>
                    <br>
                    <p>Best Regards,<br>Procurement Team</p>
                </body>
                </html>
            ";

            // Configure email
            $this->email->clear();
            $this->email->from($this->config->item('smtp_user'), 'Procurement Team');
            $this->email->to($email_to);
            $this->email->subject($email_subject);
            $this->email->message($email_message);
            $this->email->attach($attachment);

            // Send email
            if ($this->email->send()) {
                $success_count++;
                
                // Save to pr_supplier table with response token
                $this->db->insert('pr_supplier', [
                    'pr_id' => $pr_id,
                    'supplier_id' => $supplier_id,
                    'sent_by' => $this->session->userdata('user_id'),
                    'sent_at' => date('Y-m-d H:i:s'),
                    'email_body' => $email_message,
                    'pdf_path' => $attachment,
                    'response_token' => $response_token,
                    'response_submitted' => 0,
                    'status' => 'sent'
                ]);

                // Audit log
                $audit_log = array(
                    'pr_id' => $pr_id,
                    'action' => 'PR Sent to Supplier',
                    'details' => "PR sent to supplier: {$supplier->name}",
                    'done_by' => $this->session->userdata('user_id'),
                    'created_at' => date('Y-m-d H:i:s')
                );
                $this->db->insert('pr_audit_logs', $audit_log);
                
            } else {
                $failed_suppliers[] = $supplier->name;
                // Log error for debugging
                log_message('error', 'Failed to send email to ' . $email_to . ': ' . $this->email->print_debugger());
            }
        } else {
            $failed_suppliers[] = $supplier->name ?? 'Unknown Supplier';
        }
    }

    // Set flash messages
    if ($success_count > 0) {
        $this->session->set_flashdata('message', "Purchase Requisition sent successfully to {$success_count} supplier(s).");
    }
    
    if (!empty($failed_suppliers)) {
        $this->session->set_flashdata('warning', 'Failed to send to: ' . implode(', ', $failed_suppliers));
    }

    admin_redirect('purchase_requisition/view/' . $pr_id);
}


}
