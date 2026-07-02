<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Users_model extends CI_Model {

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Get user by ID
     * 
     * @param int $id
     * @return object|null
     */
    public function get_by_id($id)
    {
        $this->db->where('id', $id);
        $query = $this->db->get('sma_users');
        return $query->row();
    }


    /**
     * Get all users
     * 
     * @return array
     */
    public function get_all()
    {
        $this->db->order_by('first_name', 'ASC');
        $query = $this->db->get('sma_users');
        return $query->result();
    }

    /**
     * Search users by username or email
     * 
     * @param string $search
     * @return array
     */
    public function search($search)
    {
        $this->db->where("(username LIKE '%$search%' OR email LIKE '%$search%')");
        $query = $this->db->get('sma_users');
        return $query->result();
    }

}