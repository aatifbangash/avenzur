<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Shop_admin_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function addPage($data)
    {
        if ($this->db->insert('pages', $data)) {
            return true;
        }
        return false;
    }

    public function deletePage($id)
    {
        if ($this->db->delete('pages', ['id' => $id])) {
            return true;
        }
        return false;
    }

    public function getAllBrands()
    {
        return $this->db->get('brands')->result();
    }

    public function getAllCategories()
    {
        $this->db->where('parent_id', null)->or_where('parent_id', 0)->order_by('name');
        return $this->db->get('categories')->result();
    }

    public function getAllPages()
    {
        $q = $this->db->get('pages');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getAllProducts()
    {
        $this->db->select('id, LOWER(name) as name, slug');
        $q = $this->db->get('products');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getAllActiveProducts()
    {
        $q = $this->db->where('hide', 0)
                  ->where('draft', 0)
                  ->get("products");
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getPageByID($id)
    {
        $q = $this->db->get_where('pages', ['id' => $id]);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getPageBySlug($slug)
    {
        $q = $this->db->get_where('pages', ['slug' => $slug]);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getShopSettings()
    {
        $q = $this->db->get('shop_settings');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getSubCategories($parent_id)
    {
        $this->db->where('parent_id', $parent_id)->order_by('name');
        return $this->db->get('categories')->result();
    }

    public function assignTag($record_id, $tag_id, $field_type){
        $data = array('type' => $field_type, 'type_id' => $record_id, 'tag_id' => $tag_id);
        if ($this->db->insert('assigned_tags', $data)) {
            return true;
        }
        return false;
    }

    public function updateTagStatus($tag_id){
        if ($this->db->update('tags', ['status' => 1], ['id' => $tag_id])) {
            return true;
        }
        return false;
    }

    public function executeTag($table_name, $field_name, $operator, $value, $tag_id){
        $this->db->delete('assigned_tags', ['tag_id' => $tag_id]);
        $q = $this->db->where($field_name.' '.$operator, $value)
                ->get($table_name);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        
        return false;
    }

    public function addTag($post_array){
        unset($post_array['add_tag']);
        $post_array['date_created'] = date('Y-m-d h:i:s');
        if ($this->db->insert('tags', $post_array)) {
            return true;
        }
        return false;
    }

    public function getTagById($id){
        $q = $this->db->get_where('tags', ['id' => $id]);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }   

    public function getAllTags(){
        $this->db->select('sma_tags.*');
        $this->db->from('sma_tags');
        $this->db->order_by('sma_tags.id', 'ASC');
        return $this->db->get()->result();
    }

    public function getAbandonedCart($start_date, $end_date){
        $this->db->select('cart.*, users.email, companies.phone');
        $this->db->from('cart');
        $this->db->join('users', 'cart.user_id = users.id', 'left');
        $this->db->join('companies', 'users.company_id = companies.id', 'left');
        $this->db->order_by('cart.time', 'DESC');
        return $this->db->get()->result();
    }

    public function updatePage($id, $data)
    {
        if ($this->db->update('pages', $data, ['id' => $id])) {
            return true;
        }
        return false;
    }

    public function updateShopSettings($data)
    {
        if ($this->db->update('shop_settings', $data, ['shop_id' => 1])) {
            return true;
        }
        return false;
    }

    public function updateSlider($data)
    {
        if ($this->db->update('shop_settings', ['slider' => json_encode($data)], ['shop_id' => 1])) {
            return true;
        }
        return false;
    }

    public function updateSmsSettings($data)
    {
        if ($this->db->update('sms_settings', $data, ['id' => 1])) {
            return true;
        }
        return false;
    }
}
