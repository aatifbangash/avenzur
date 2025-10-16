<?php
defined('BASEPATH') OR exit('No direct script access allowed');
use Mpdf\Mpdf;

class Purchase_requisition extends MY_Controller {

    public function __construct()
    { 
        //error_reporting(E_ALL);        // Report all errors
        //ini_set('display_errors', 1); 
        parent::__construct();
        $this->load->admin_model('users_model');
        $this->load->admin_model('pr_audit_logs_model');
        $this->load->admin_model('purchase_requisition_model');
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
    //error_reporting(E_ALL);        // Report all errors
       // ini_set('display_errors', 1); 
    $pdfDir = FCPATH . 'assets/uploads/pr_pdfs/';
    if (!is_dir($pdfDir)) {
        mkdir($pdfDir, 0755, true);
    }

    $pr_id = $isEdit ? $id : $id; 
    $prFile = $pdfDir . 'PR_' . $pr_id . '.pdf';

    // Delete old PDF if exists (Edit case)
    if ($isEdit && file_exists($prFile)) {
        unlink($prFile);
    }


    $isEdit = !empty($id);

    if ($this->input->post()) {
         //error_reporting(E_ALL);        // Report all errors
         //ini_set('display_errors', 1); 
        
        $data = [
            'warehouse_id' => $this->input->post('warehouse_id'),
            'remarks' => $this->input->post('remarks'),
            'expected_date' => $this->input->post('expected_date'),
            'priority' => $this->input->post('priority'),
            'department' => $this->input->post('department'),
            'pr_number' => $this->input->post('pr_number')
        ];

        $this->data['pr'] = $data;
        $this->data['items'] = $this->input->post('items');


         $html = $this->load->view($this->theme . 'purchase_requisition/pdf_template', $this->data, true);

        $mpdf = new Mpdf([
            'format' => 'A4',
            'margin_top' => 80,
            'margin_bottom' => 70,
            ]);

         $mpdf->WriteHTML($html);
         $mpdf->Output($prFile, \Mpdf\Output\Destination::FILE);    



        if ($isEdit) {
           //  error_reporting(E_ALL);        // Report all errors
        //ini_set('display_errors', 1); 
            // For update
            $data['updated_at'] = date('Y-m-d H:i:s');
            $data['updated_by'] = $this->session->userdata('user_id');
            $items = $this->input->post('items');

            $result = $this->purchase_requisition_model->update_requisition($id, $data, $items);

            if ($result) {
                $this->session->set_flashdata('message', 'Purchase Requisition updated successfully!');
                admin_redirect('purchase_requisition');
            } else {
                $this->session->set_flashdata('error', 'Error while updating requisition.');
            }

        } else {
            // For create
            $pr_number_temp = 'PR-' . time();
            $data['pr_number'] = $pr_number_temp;
            $data['requested_by'] = $this->session->userdata('user_id');
            $data['status'] = 'submitted';
            $items = $this->input->post('items');

            $id = $this->purchase_requisition_model->create_requisition($data, $items);

            if ($id) {
                 $user_id = $this->session->userdata('user_id') ;

            $log_result = $this->pr_audit_logs_model->log($pr_number_temp, 'created', $user_id);

            if ($log_result) {
                log_message('info', 'Audit log created successfully for PR: ' . $pr_number_temp . ' by user: ' . $user_id);
            } else {
                log_message('error', 'Failed to create audit log for PR: ' . $pr_number_temp . ' by user: ' . $user_id);
            }

                $this->session->set_flashdata('message', 'Purchase Requisition created successfully!');
                admin_redirect('purchase_requisition');
            } else {
                $this->session->set_flashdata('error', 'Error while creating requisition.');
            }
        }
    }

    // Load form for create or edit
    $this->data['warehouses'] = $this->site->getAllWarehouses();
    $this->data['reference_no'] = $isEdit ? '' : 'PR-' . time();

    if ($isEdit) {
        //  error_reporting(E_ALL);        // Report all errors
        // ini_set('display_errors', 1); 
        $this->data['requisition'] = $this->purchase_requisition_model->get_by_id($id);
        $this->data['items'] = $this->purchase_requisition_model->get_items($id);
        
    } else {
        $this->data['requisition'] = null;
        $this->data['items'] = [];
    }

    $page = 'purchase_requisition/form'; // renamed your add view to form.php
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
    
        $this->data['suppliers'] = $this->site->getParentCompanyByGroupAndId('supplier', 63);
// $this->data['logs'] = $this->pr_audit_logs_model->get_by_pr_number($this->data['requisition']->pr_number);
$logs = $this->pr_audit_logs_model->get_by_pr_number($this->data['requisition']->pr_number);



$this->data['logs'] = $logs;
        $this->data['id'] = $id;
        //print_r( $this->data['suppliers']); exit;
        $meta = ['page_title' => 'View Requisition', 'bc' => [['link' => admin_url('purchase_requisition'), 'page' => 'Purchase Requisitions'], ['link' => '#', 'page' => 'View Requisition']]];
        $this->page_construct('purchase_requisition/view', $meta, $this->data);

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

    // Save who sent PR to which supplier
    // $this->db->insert('pr_supplier', [
    //     'pr_id' => $pr_id,
    //     'supplier_id' => $supplier_id,
    //     'sent_by' => $this->session->userdata('user_id'),
    //     'sent_at' => date('Y-m-d H:i:s'),
    //     'email_body' => $email_body,
    //     'pdf_path' => isset($pdf_file) ? $pdf_file : null
    // ]);
    $supplier_ids = $this->input->post('supplier_id'); // email or phone
    $subject = $this->input->post('subject');
    $message = $this->input->post('remarks');
    $pr_id = $this->input->post('pr_id');
    $attachment = FCPATH . 'assets/uploads/pr_pdfs/PR_' . $pr_id . '.pdf'; 

    if (!file_exists($attachment)) {
        $this->session->set_flashdata('error', 'PDF attachment not found. Please regenerate it first.');
        admin_redirect('purchase_requisition/view/' . $pr_id);
   }

   foreach ($supplier_ids as $supplier_id) {
    // Fetch supplier details
    $supplier = $this->purchase_requisition_model->getSupplierById($supplier_id);
    if ($supplier && !empty($supplier->email)) {

        $email_to = $supplier->email;
        $email_subject = $subject ?: 'Purchase Requisition - ' . $pr_id;
        $email_message = "
            Dear {$supplier->name},<br><br>
            {$message}<br><br>
            Please find attached the Purchase Requisition (PR #{$pr_id}).<br><br>
            Regards,<br>
            Procurement Team
        ";

        // Send email later
        // $this->sma->send_email(
        //     $email_to, 
        //     $email_subject, 
        //     $email_message, 
        //     null,                // from
        //     null,                // from name
        //     $attachment          // attachment
        // );

        // Log activity (optional)
        $logData = [
            'pr_id' => $pr_id,
            'supplier_id' => $supplier_id,
            'sent_by' => $this->session->userdata('user_id'),
            'sent_at' => date('Y-m-d H:i:s'),
            'email_body' => $email_message,
            'pdf_path' => $attachment
        ];
        $this->purchase_requisition_model->log_pr_sent($logData);
    }
}

 
admin_redirect('purchase_requisition/view/' . $pr_id);
    
    
}


}
