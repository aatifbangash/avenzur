<?php defined('BASEPATH') or exit('No direct script access allowed');

class Editor extends MY_Controller {

    public function image_upload() { 
        // Load the file helper
        // echo '<pre>'; print_r($this->input->post());  exit;  
        $this->load->helper('file');  
        $upload_path= './assets/uploads/editor_upload/';
        if (!is_dir($upload_path)) {
            mkdir($upload_path, 0777, TRUE);
        }
        $config['upload_path'] =  $upload_path;   
        $config['allowed_types'] = 'gif|jpg|jpeg|png';
        $config['max_size'] = 2048; // 2MB 
        $this->load->library('upload', $config);  
        if (!$this->upload->do_upload('file')) {
            $error = array(
                'error' => $this->upload->display_errors(),
                "csrfName" =>$this->security->get_csrf_token_name(),
                "csrfHash" => $this->security->get_csrf_hash(), 
           );
           header('Content-Type: application/json; charset=utf-8');
           echo json_encode($error);exit;
           // echo json_encode(['error' => $error]);
        }else {
            $data = $this->upload->data();
            $response = array(
                'filelink' => base_url($upload_path . $data['file_name']),
                'filename' => $data['file_name'],
                "csrfName" =>$this->security->get_csrf_token_name(),
                "csrfHash" => $this->security->get_csrf_hash(), 
            );
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode($response);exit;  
        }
    
    }
}