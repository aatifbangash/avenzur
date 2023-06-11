<?php

defined('BASEPATH') or exit('No direct script access allowed');

class blog_model extends CI_Model
{
     protected $table = 'blog';

     
    public function __construct()
    {
        parent::__construct();
    }


        public function insertdata($data){
            $business_id = $this->ion_auth->user()->row()->business_id;
            $data["business_id"] = $business_id;
        if ($this->db->insert('blog', $data)) {
            return true;
        }
        return false;
    
	}
	 public function updateBlog($id, $data)
    {
        if ($this->db->update('blog', $data, ['id' => $id])) {
            return true;
        }
        return false;
    }
        public function getBlogByID($id)
    {   
        $business_id = $this->ion_auth->user()->row()->business_id;
        $q = $this->db->get_where('blog', ['id' => $id, 'business_id' => $business_id]);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }
      public function deleteBlog($id)
    {
        $business_id = $this->ion_auth->user()->row()->business_id;
        if ($this->db->delete('blog', ['id' => $id,  'business_id' => $business_id])) {
            return true;
        }
        return false;
    }
      
    
}