<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Pr_audit_logs_model extends CI_Model {

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Insert a new audit log entry
     * 
     * @param string $pr_number
     * @param string $action ('created', 'edited', 'deleted')
     * @param int $done_by (user_id)
     * @return bool
     */
    public function log($pr_number, $action, $done_by)
    {
        $data = [
            'pr_number' => $pr_number,
            'action' => $action,
            'done_by' => $done_by
        ];

        return $this->db->insert('sma_pr_audit_logs', $data);
    }

    /**
     * Get all logs for a specific PR number
     * 
     * @param string $pr_number
     * @return array
     */
    public function get_by_pr_number($pr_number)
    {
        $this->db->select("pal.*, CONCAT(u.first_name, ' ', u.last_name) AS done_by_name");
        $this->db->from('sma_pr_audit_logs pal');
        $this->db->join('sma_users u', 'u.id = pal.done_by', 'left');
        $this->db->where('pal.pr_number', $pr_number);
        $this->db->order_by('pal.created_at', 'DESC');

        $query = $this->db->get();
        return $query->result();
    }

    /**
     * Get a single log entry by ID
     * 
     * @param int $id
     * @return object|null
     */
    public function get_by_id($id)
    {
        $this->db->select('pal.*, u.username as done_by_name');
        $this->db->from('sma_pr_audit_logs pal');
        $this->db->join('sma_users u', 'u.id = pal.done_by', 'left');
        $this->db->where('pal.id', $id);

        $query = $this->db->get();
        return $query->row();
    }

}