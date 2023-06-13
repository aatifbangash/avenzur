<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Calendar_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function addEvent($data = [])
    {
        $business_id = $this->ion_auth->user()->row()->business_id;
        $data['business_id'] = $business_id;
        if ($this->db->insert('calendar', $data)) {
            return true;
        }
        return false;
    }

    public function deleteEvent($id)
    {
        $business_id = $this->ion_auth->user()->row()->business_id;
        $this->db->where("business_id", $business_id);
        if ($this->db->delete('calendar', ['id' => $id])) {
            return true;
        }
        return false;
    }

    public function getEventByID($id)
    {
        $business_id = $this->ion_auth->user()->row()->business_id;
        $this->db->where("business_id", $business_id);
        $q = $this->db->get_where('calendar', ['id' => $id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getEvents($start, $end)
    {
        $business_id = $this->ion_auth->user()->row()->business_id;
        $this->db->select('id, title, start, end, description, color');
        $this->db->where("business_id", $business_id);
        $this->db->where('start >=', $start)->where('start <=', $end);
        if ($this->Settings->restrict_calendar) {
            $this->db->where('user_id', $this->session->userdata('user_id'));
        }

        $q = $this->db->get('calendar');

        if ($q->num_rows() > 0) {
            foreach (($q->result_array()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function updateEvent($id, $data = [])
    {
        $business_id = $this->ion_auth->user()->row()->business_id;
        $this->db->where("business_id", $business_id);
        if ($this->db->update('calendar', $data, ['id' => $id])) {
            return true;
        }
        return false;
    }
}
