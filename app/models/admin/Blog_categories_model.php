<?php

defined('BASEPATH') or exit('No direct script access allowed');

class blog_categories_model  extends CI_Model
{
   
      protected $table = 'blog_categories';
      
         public function __construct()
    {
        parent::__construct();
    }


      public function addBcategory($data){
     if ($this->db->insert('blog_categories', $data)) {
            return true;
        }
        return false;
    

    }
     public function getParentBCategories()
    {
        $business_id = $this->ion_auth->user()->row()->business_id;
        $this->db->where('parent_id', null)->or_where('parent_id', 0);
        $this->db->where('business_id', $business_id);
        $q = $this->db->get('blog_categories');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }
     public function updateBlogCategory($id, $data)
    {
        if ($this->db->update('blog_categories', $data, ['id' => $id])) {
            return true;
        }
        return false;
    }
        public function getBlogCategoryByID($id)
    {
        $q = $this->db->get_where('blog_categories', ['id' => $id]);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }
      public function deleteBlogCategory($id)
    { 
        $business_id = $this->ion_auth->user()->row()->business_id;
        if ($this->db->delete('blog_categories', ['id' => $id, 'business_id' => $business_id])) {
            return true;
        }
        return false;
    }
      function display_records()
          {
            $business_id = $this->ion_auth->user()->row()->business_id;
            $this->db->where('business_id', $business_id);
            $query=$this->db->get("blog_categories");
            return $query->result();
          }

}