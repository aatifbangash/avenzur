<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Blog extends MY_Controller {
     public function __construct()
    {
        parent::__construct();

        if (!$this->loggedIn) {
            $this->session->set_userdata('requested_page', $this->uri->uri_string());
            $this->sma->md('login');
        }

        if (!$this->Owner) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect('admin');
        }
        $this->lang->admin_load('front_end', $this->Settings->user_language);
        $this->load->library('form_validation');
           $this->load->admin_model('Blog_model');
        $this->load->admin_model('Blog_categories_model');
        $this->upload_path       = 'assets/uploads/';
        $this->image_types       = 'jpg|jpeg|png';
        $this->allowed_file_size = '1024';
    }
      public function allBlogs()
    {
        $this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');

        $bc   = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('Blog'), 'page' => lang('Blog')], ['link' => '#', 'page' => lang('Blog')]];
        $meta = ['page_title' => lang('Blog'), 'bc' => $bc];
        $this->page_construct('blog/list_blog', $meta, $this->data);
       // $this->page_construct('pages/blog_page', $meta, $this->data);
    }
 
    public function getBlogs()
    {
        $this->load->library('datatables');
        $this->datatables
            ->select('id, name, slug, category,description, title')
            ->from('blog')
            ->add_column('Actions', "<div class=\"text-center\"><a href='" . admin_url('blog/edit_blog/$1') . "' class='tip' title='" . lang('edit_page') . "'><i class=\"fa fa-edit\"></i></a> <a href='#' class='tip po' title='<b>" . lang('delete_blog') . "</b>' data-content=\"<p>" . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . admin_url('Blog/delete_blog/$1') . "'>" . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i></a></div>", 'id');
        //->unset_column('id');

        echo $this->datatables->generate();
    }

 public function add_blog()
    {
        $this->form_validation->set_rules('name', lang('name'), 'required');
        $this->form_validation->set_rules('title', lang('title'), 'required');
        $this->form_validation->set_rules('description', lang('description'), 'required');
        $this->form_validation->set_rules('body', lang('body'), 'required');
        $this->form_validation->set_rules('category', lang('category'), 'required');
        $this->form_validation->set_rules('userfile', lang('featured_image','xss_clean'));
        $this->form_validation->set_rules('slug', lang('slug'), 'trim|required|is_unique[pages.slug]');
        if ($this->form_validation->run() == true) {
            $data = [
                'name'        => $this->input->post('name'),
                'title'       => $this->input->post('title'),
                'description' => $this->input->post('description'),
                'body'        => $this->input->post('body', true),
                'slug'        => $this->input->post('slug'),
                'category'    => $this->input->post('category'),
                // 'active'      => $this->input->post('active') ? $this->input->post('active') : 0,
                'updated_at'  => date('Y-m-d H:i:s'),
            ];
            
           if ($_FILES['userfile']['size'] > 0) {
                $this->load->library('upload');
                $config['upload_path']   = $this->upload_path;
                $config['allowed_types'] = $this->image_types;
                $config['max_size']      = $this->allowed_file_size;
                // $config['max_width']     = $this->Settings->iwidth;
                // $config['max_height']    = $this->Settings->iheight;
                $config['overwrite']     = false;
                $config['encrypt_name']  = true;
                $config['max_filename']  = 25;
                $this->upload->initialize($config);
                if (!$this->upload->do_upload()) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    redirect($_SERVER['HTTP_REFERER']);
                }
                $photo         = $this->upload->file_name;
                $data['image'] = $photo;
                $this->load->library('image_lib');
                $config['image_library']  = 'gd2';
                $config['source_image']   = $this->upload_path . $photo;
                $config['new_image']      = $this->thumbs_path . $photo;
                $config['maintain_ratio'] = true;
                // $config['width']          = $this->Settings->twidth;
                // $config['height']         = $this->Settings->theight;
                $this->image_lib->clear();
                $this->image_lib->initialize($config);
                if (!$this->image_lib->resize()) {
                    echo $this->image_lib->display_errors();
                }
                if ($this->Settings->watermark) {
                    $this->image_lib->clear();
                    $wm['source_image']     = $this->upload_path . $photo;
                    $wm['wm_text']          = 'Copyright ' . date('Y') . ' - ' . $this->Settings->site_name;
                    $wm['wm_type']          = 'text';
                    $wm['wm_font_path']     = 'system/fonts/texb.ttf';
                    $wm['quality']          = '100';
                    $wm['wm_font_size']     = '16';
                    $wm['wm_font_color']    = '999999';
                    $wm['wm_shadow_color']  = 'CCCCCC';
                    $wm['wm_vrt_alignment'] = 'top';
                    $wm['wm_hor_alignment'] = 'left';
                    $wm['wm_padding']       = '10';
                    $this->image_lib->initialize($wm);
                    $this->image_lib->watermark();
                }
                $this->image_lib->clear();
                $config = null;
            }
            }elseif ($this->input->post('add_blog')) {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect('Blog/allBlogs');
        }
        
                if ($this->form_validation->run() == true && $this->Blog_model->insertData($data)) {
            $this->session->set_flashdata('message', lang('blog_added'));
            admin_redirect('Blog/allBlogs');
        } else {
            $this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');
            $bc                  = [['link' => base_url(), 'page' => lang('home')], ['link' => '#', 'page' => lang('add_blog')]];
            $meta                = ['page_title' => lang('add_blog'), 'bc' => $bc];
            $this->data['result']=$this->Blog_categories_model->display_records();
            $this->page_construct('blog/add_blog', $meta, $this->data);
          //  $this->load->view("blog/add_blog",$result);
        }
            
}
  public function edit_blog($id = null)
    {
        $page = $this->Blog_model->getBlogByID($id);
        $this->form_validation->set_rules('name', lang('name'), 'required|max_length[60]');
        $this->form_validation->set_rules('title', lang('title'), 'required|max_length[60]');
        $this->form_validation->set_rules('description', lang('description'), 'required');
        $this->form_validation->set_rules('body', lang('body'), 'required');
        $this->form_validation->set_rules('category', lang('category'), 'required');
        $this->form_validation->set_rules('slug', lang('slug'), 'trim|required|alpha_dash');
        $this->form_validation->set_rules('userfile', lang('featured_image','xss_clean'));
        if ($page->slug != $this->input->post('slug')) {
            $this->form_validation->set_rules('slug', lang('slug'), 'is_unique[blog.slug]');
        }
        if ($this->form_validation->run() == true) {
            $data = [
                'name'        => $this->input->post('name'),
                'title'       => $this->input->post('title'),
                'description' => $this->input->post('description'),
                'body'        => $this->input->post('body'),
                'slug'        => $this->input->post('slug'),
                'category'    => $this->input->post('category'),
                 //'userfile' =>$this->input->post('userfile'),
               
                'updated_at'  => date('Y-m-d H:i:s'),
            ];
            
      if ($_FILES['userfile']['size'] > 0) {
                $this->load->library('upload');
                $config['upload_path']   = $this->upload_path;
                $config['allowed_types'] = $this->image_types;
                $config['max_size']      = $this->allowed_file_size;
                // $config['max_width']     = $this->Settings->iwidth;
                // $config['max_height']    = $this->Settings->iheight;
                $config['overwrite']     = false;
                $config['encrypt_name']  = true;
                $config['max_filename']  = 25;
                $this->upload->initialize($config);
                if (!$this->upload->do_upload()) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    redirect($_SERVER['HTTP_REFERER']);
                }
                $photo         = $this->upload->file_name;
                $data['image'] = $photo;
                $this->load->library('image_lib');
                $config['image_library']  = 'gd2';
                $config['source_image']   = $this->upload_path . $photo;
                $config['new_image']      = $this->thumbs_path . $photo;
                $config['maintain_ratio'] = true;
                // $config['width']          = $this->Settings->twidth;
                // $config['height']         = $this->Settings->theight;
                $this->image_lib->clear();
                $this->image_lib->initialize($config);
                if (!$this->image_lib->resize()) {
                    echo $this->image_lib->display_errors();
                }
                if ($this->Settings->watermark) {
                    $this->image_lib->clear();
                    $wm['source_image']     = $this->upload_path . $photo;
                    $wm['wm_text']          = 'Copyright ' . date('Y') . ' - ' . $this->Settings->site_name;
                    $wm['wm_type']          = 'text';
                    $wm['wm_font_path']     = 'system/fonts/texb.ttf';
                    $wm['quality']          = '100';
                    $wm['wm_font_size']     = '16';
                    $wm['wm_font_color']    = '999999';
                    $wm['wm_shadow_color']  = 'CCCCCC';
                    $wm['wm_vrt_alignment'] = 'top';
                    $wm['wm_hor_alignment'] = 'left';
                    $wm['wm_padding']       = '10';
                    $this->image_lib->initialize($wm);
                    $this->image_lib->watermark();
                }
                $this->image_lib->clear();
                $config = null;
            }
            }elseif ($this->input->post('add_blog')) {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect('Blog/allBlogs');
        }
        
        

        if ($this->form_validation->run() == true && $this->Blog_model->updateBlog($id, $data)) {
            $this->session->set_flashdata('message', lang('Blog_updated'));
            admin_redirect('Blog/allBlogs');
        } else {
            $this->data['page']  = $page;
            $this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');
            $bc                  = [['link' => base_url(), 'page' => lang('home')], ['link' => '#', 'page' => lang('edit_blog')]];
            $meta                = ['page_title' => lang('edit_blog'), 'bc' => $bc];
            $this->page_construct('blog/edit_blog', $meta, $this->data);
        }
    }
    
        public function delete_blog($id = null)
    {
        if (!$id) {
            $this->sma->send_json(['error' => 1, 'msg' => lang('id_not_found')]);
        }
        if ($this->Blog_model->deleteBlog($id)) {
            $this->sma->send_json(['error' => 0, 'msg' => lang('Blog_deleted')]);
        }
    }
    
    
    public function add_bcategory(){
        $this->form_validation->set_rules('code', lang('category_code'), 'trim|is_unique[categories.code]|required');
        $this->form_validation->set_rules('name', lang('name'), 'required|min_length[3]');
        $this->form_validation->set_rules('slug', lang('slug'), 'required|is_unique[categories.slug]|alpha_dash');
        $this->form_validation->set_rules('userfile', lang('category_image'), 'xss_clean');
        $this->form_validation->set_rules('description', lang('description'), 'trim|required');

        if ($this->form_validation->run() == true) {
            $data = [
                'name'        => $this->input->post('name'),
                'code'        => $this->input->post('code'),
                'slug'        => $this->input->post('slug'),
                'description' => $this->input->post('description'),
                'parent_id'   => $this->input->post('parent'),
            ];

            if ($_FILES['userfile']['size'] > 0) {
                $this->load->library('upload');
                $config['upload_path']   = $this->upload_path;
                $config['allowed_types'] = $this->image_types;
                $config['max_size']      = $this->allowed_file_size;
                $config['max_width']     = $this->Settings->iwidth;
                $config['max_height']    = $this->Settings->iheight;
                $config['overwrite']     = false;
                $config['encrypt_name']  = true;
                $config['max_filename']  = 25;
                $this->upload->initialize($config);
                if (!$this->upload->do_upload()) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    redirect($_SERVER['HTTP_REFERER']);
                }
                $photo         = $this->upload->file_name;
                $data['image'] = $photo;
                $this->load->library('image_lib');
                $config['image_library']  = 'gd2';
                $config['source_image']   = $this->upload_path . $photo;
                $config['new_image']      = $this->thumbs_path . $photo;
                $config['maintain_ratio'] = true;
                $config['width']          = $this->Settings->twidth;
                $config['height']         = $this->Settings->theight;
                $this->image_lib->clear();
                $this->image_lib->initialize($config);
                if (!$this->image_lib->resize()) {
                    echo $this->image_lib->display_errors();
                }
                if ($this->Settings->watermark) {
                    $this->image_lib->clear();
                    $wm['source_image']     = $this->upload_path . $photo;
                    $wm['wm_text']          = 'Copyright ' . date('Y') . ' - ' . $this->Settings->site_name;
                    $wm['wm_type']          = 'text';
                    $wm['wm_font_path']     = 'system/fonts/texb.ttf';
                    $wm['quality']          = '100';
                    $wm['wm_font_size']     = '16';
                    $wm['wm_font_color']    = '999999';
                    $wm['wm_shadow_color']  = 'CCCCCC';
                    $wm['wm_vrt_alignment'] = 'top';
                    $wm['wm_hor_alignment'] = 'left';
                    $wm['wm_padding']       = '10';
                    $this->image_lib->initialize($wm);
                    $this->image_lib->watermark();
                }
                $this->image_lib->clear();
                $config = null;
            }
        } elseif ($this->input->post('add_bcategory')) {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect('Blog/show_bcategory');
        }

        if ($this->form_validation->run() == true && $this->Blog_categories_model->addBcategory($data)) {
            $this->session->set_flashdata('message', lang('category_added'));
            admin_redirect('Blog/show_bcategory');
        } else {
            $this->data['error']      = validation_errors() ? validation_errors() : $this->session->flashdata('error');
            $this->data['bcategories'] = $this->Blog_categories_model->getParentBCategories();
            $this->data['modal_js']   = $this->site->modal_js();
            $this->load->view($this->theme . 'blog/add_bcategory', $this->data);
        }
    }
          public function show_bcategory()
                {
                    $this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');
                    $bc                  = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('Blog'), 'page' => lang('Blog')], ['link' => '#', 'page' => lang(' Blog categories')]];
                    $meta                = ['page_title' => lang(' Blog categories'), 'bc' => $bc];
                    $this->page_construct('blog/show_bcategory', $meta, $this->data);
                }
                public function getBlogCategory()
                {
                    $print_barcode = anchor('admin/products/print_barcodes/?category=$1', '<i class="fa fa-print"></i>', 'title="' . lang('print_barcodes') . '" class="tip"');
            
                    $this->load->library('datatables');
                    $this->datatables
                        ->select("{$this->db->dbprefix('blog_categories')}.id as id, {$this->db->dbprefix('blog_categories')}.image, {$this->db->dbprefix('blog_categories')}.code, {$this->db->dbprefix('blog_categories')}.name, {$this->db->dbprefix('blog_categories')}.slug, c.name as parent", false)
                        ->from('blog_categories')
                        ->join('blog_categories c', 'c.id=blog_categories.parent_id', 'left')
                        ->group_by('blog_categories.id')
                        ->add_column('Actions', '<div class="text-center">'  . " <a href='" . admin_url('blog/edit_bcategory/$1') . "' data-toggle='modal' data-target='#myModal' class='tip' title='" . lang('edit_blog_category') . "'><i class=\"fa fa-edit\"></i></a> <a href='#' class='tip po' title='<b>" . lang('delete_category') . "</b>' data-content=\"<p>" . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . admin_url('blog/delete_bcategory/$1') . "'>" . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i></a></div>", 'id');
            
                    echo $this->datatables->generate();
                }
            
                public function delete_bcategory($id = null)
                {
                    if (!$id) {
                        $this->sma->send_json(['error' => 1, 'msg' => lang('id_not_found')]);
                    }
                    if ($this->Blog_categories_model->deleteBlogCategory($id)) {
                        $this->sma->send_json(['error' => 0, 'msg' => lang('Category_deleted')]);
                    }
                }
                
                 public function edit_bcategory($id = null)
                {
                    $this->form_validation->set_rules('code', lang('category_code'), 'trim|required');
                    $pr_details = $this->Blog_categories_model->getBlogCategoryByID($id);
                    if ($this->input->post('code') != $pr_details->code) {
                        $this->form_validation->set_rules('code', lang('category_code'), 'required|is_unique[categories.code]');
                    }
                    $this->form_validation->set_rules('slug', lang('slug'), 'required|alpha_dash');
                    if ($this->input->post('slug') != $pr_details->slug) {
                        $this->form_validation->set_rules('slug', lang('slug'), 'required|alpha_dash|is_unique[categories.slug]');
                    }
                    $this->form_validation->set_rules('name', lang('category_name'), 'required|min_length[3]');
                    $this->form_validation->set_rules('userfile', lang('category_image'), 'xss_clean');
                    $this->form_validation->set_rules('description', lang('description'), 'trim|required');
            
                    if ($this->form_validation->run() == true) {
                        $data = [
                            'name'        => $this->input->post('name'),
                            'code'        => $this->input->post('code'),
                            'slug'        => $this->input->post('slug'),
                            'description' => $this->input->post('description'),
                            'parent_id'   => $this->input->post('parent'),
                        ];
            
                        if ($_FILES['userfile']['size'] > 0) {
                            $this->load->library('upload');
                            $config['upload_path']   = $this->upload_path;
                            $config['allowed_types'] = $this->image_types;
                            $config['max_size']      = $this->allowed_file_size;
                            $config['max_width']     = $this->Settings->iwidth;
                            $config['max_height']    = $this->Settings->iheight;
                            $config['overwrite']     = false;
                            $config['encrypt_name']  = true;
                            $config['max_filename']  = 25;
                            $this->upload->initialize($config);
                            if (!$this->upload->do_upload()) {
                                $error = $this->upload->display_errors();
                                $this->session->set_flashdata('error', $error);
                                redirect($_SERVER['HTTP_REFERER']);
                            }
                            $photo         = $this->upload->file_name;
                            $data['image'] = $photo;
                            $this->load->library('image_lib');
                            $config['image_library']  = 'gd2';
                            $config['source_image']   = $this->upload_path . $photo;
                            $config['new_image']      = $this->thumbs_path . $photo;
                            $config['maintain_ratio'] = true;
                            $config['width']          = $this->Settings->twidth;
                            $config['height']         = $this->Settings->theight;
                            $this->image_lib->clear();
                            $this->image_lib->initialize($config);
                            if (!$this->image_lib->resize()) {
                                echo $this->image_lib->display_errors();
                            }
                            if ($this->Settings->watermark) {
                                $this->image_lib->clear();
                                $wm['source_image']     = $this->upload_path . $photo;
                                $wm['wm_text']          = 'Copyright ' . date('Y') . ' - ' . $this->Settings->site_name;
                                $wm['wm_type']          = 'text';
                                $wm['wm_font_path']     = 'system/fonts/texb.ttf';
                                $wm['quality']          = '100';
                                $wm['wm_font_size']     = '16';
                                $wm['wm_font_color']    = '999999';
                                $wm['wm_shadow_color']  = 'CCCCCC';
                                $wm['wm_vrt_alignment'] = 'top';
                                $wm['wm_hor_alignment'] = 'left';
                                $wm['wm_padding']       = '10';
                                $this->image_lib->initialize($wm);
                                $this->image_lib->watermark();
                            }
                            $this->image_lib->clear();
                            $config = null;
                        }
                    } elseif ($this->input->post('edit_bcategory')) {
                        $this->session->set_flashdata('error', validation_errors());
                        admin_redirect('blog/show_bcategory');
                    }
            
                    if ($this->form_validation->run() == true && $this->Blog_categories_model->updateBlogCategory($id, $data)) {
                        $this->session->set_flashdata('message', lang('category_updated'));
                        admin_redirect('blog/show_bcategory');
                    } else {
                        $this->data['error']      = validation_errors() ? validation_errors() : $this->session->flashdata('error');
                        $this->data['category']   = $this->Blog_categories_model->getBlogCategoryByID($id);
                        $this->data['categories'] = $this->Blog_categories_model->getParentBCategories();
                        $this->data['modal_js']   = $this->site->modal_js();
                        $this->load->view($this->theme . 'blog/edit_bcategory', $this->data);
                    }
                }
                
    // public function displaydata()
    //       {
            
    //           $this->load->view('blog/add_blog',$result);
    //       }
}