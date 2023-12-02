// <?php

// defined('BASEPATH') or exit('No direct script access allowed');

// class Country extends MY_Controller {
//      public function __construct()
//     {
//         parent::__construct();

//         if (!$this->loggedIn) {
//             $this->session->set_userdata('requested_page', $this->uri->uri_string());
//             $this->sma->md('login');
//         }

//         if (!$this->Owner) {
//             $this->session->set_flashdata('warning', lang('access_denied'));
//             redirect('admin');
//         }
//         $this->lang->admin_load('front_end', $this->Settings->user_language);
//         $this->load->library('form_validation');
//           $this->load->admin_model('Country_model');
//       // $this->load->admin_model('Blog_categories_model');
//         $this->upload_path       = 'assets/uploads/';
//         $this->image_types       = 'jpg|jpeg|png';
//         $this->allowed_file_size = '1024';
//     }
//       public function allCountry()
//     {
//         $this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');

//         $bc   = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('country'), 'page' => lang('country')], ['link' => '#', 'page' => lang('country')]];
//         $meta = ['page_title' => lang('country'), 'bc' => $bc];
//         $this->page_construct('country/list_country', $meta, $this->data);
//       // $this->page_construct('pages/blog_page', $meta, $this->data);
//     }
 


//     public function add_country()
//     {
//           $this->form_validation->set_rules('name', lang('name'), 'required');
//         $this->form_validation->set_rules('title', lang('title'), 'required');
      
//         if ($this->form_validation->run() == true) {
//             $data = [
//                 'name'        => $this->input->post('name'),
//                 'title'       => $this->input->post('title'),
//                 'description' => $this->input->post('description'),
//                 'body'        => $this->input->post('body', true),
//                 'slug'        => $this->input->post('slug'),
//                 'category'    => $this->input->post('category'),
//                 // 'active'      => $this->input->post('active') ? $this->input->post('active') : 0,
//                 'updated_at'  => date('Y-m-d H:i:s'),
//             ];
            
//           if ($_FILES['userfile']['size'] > 0) {
//                 $this->load->library('upload');
//                 $config['upload_path']   = $this->upload_path;
//                 $config['allowed_types'] = $this->image_types;
//                 $config['max_size']      = $this->allowed_file_size;
//                 // $config['max_width']     = $this->Settings->iwidth;
//                 // $config['max_height']    = $this->Settings->iheight;
//                 $config['overwrite']     = false;
//                 $config['encrypt_name']  = true;
//                 $config['max_filename']  = 25;
//                 $this->upload->initialize($config);
//                 if (!$this->upload->do_upload()) {
//                     $error = $this->upload->display_errors();
//                     $this->session->set_flashdata('error', $error);
//                     redirect($_SERVER['HTTP_REFERER']);
//                 }
//                 $photo         = $this->upload->file_name;
//                 $data['image'] = $photo;
//                 $this->load->library('image_lib');
//                 $config['image_library']  = 'gd2';
//                 $config['source_image']   = $this->upload_path . $photo;
//                 $config['new_image']      = $this->thumbs_path . $photo;
//                 $config['maintain_ratio'] = true;
//                 // $config['width']          = $this->Settings->twidth;
//                 // $config['height']         = $this->Settings->theight;
//                 $this->image_lib->clear();
//                 $this->image_lib->initialize($config);
        
//             }
//             }elseif ($this->input->post('add_country')) {
//             $this->session->set_flashdata('error', validation_errors());
//             admin_redirect('Country/allCountry');
//         }
        
//                 if ($this->form_validation->run() == true && $this->Blog_model->insertData($data)) {
//             $this->session->set_flashdata('message', lang('blog_added'));
//             admin_redirect('Country/allCountry');
//         } else {
//             $this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');
//             $bc                  = [['link' => base_url(), 'page' => lang('home')], ['link' => '#', 'page' => lang('add_country')]];
//             $meta                = ['page_title' => lang('add_country'), 'bc' => $bc];
//           //  $this->data['result']=$this->Blog_categories_model->display_records();
//             $this->page_construct('country/add_country', $meta, $this->data);
//           //  $this->load->view("blog/add_blog",$result);
//         }
// //}
//     }
// }