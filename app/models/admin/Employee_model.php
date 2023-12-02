<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Employee_model extends CI_Model
{

    public function addEmployee($data)
    {
        return $this->db->insert('sma_employees', $data);
    }


    public function getAllEmployees()
    {

        $data = [];
        $departments = $this->db->select('id, name, code')->order_by('id ASC')
            ->get('employees');

        if ($departments->num_rows() > 0) {
            foreach (($departments->result()) as $row) {
                $data[] = $row;
            }
        }
        return $data;
    }

    public function getEmployeeById($id)
    {
        $q = $this->db->get_where('employees', ['id' => $id]);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function UpdateEmployee($id, $data)
    {
        if ($this->db->update('employees', $data, ['id' => $id])) {
            return true;
        }
        return false;
    }

    public function deleteEmployee($id)
    {
        if ($this->db->delete('employees', ['id' => $id])) {
            return true;
        }
        return false;
    }
}
