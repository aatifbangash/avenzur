<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Department_model extends CI_Model
{

    public function addDepartment($data)
    {
        return $this->db->insert('sma_departments', $data);
    }


    public function getAllDepartments()
    {

        $data = [];
        $departments = $this->db->select('id, name, code')->order_by('id ASC')
            ->get('departments');

        if ($departments->num_rows() > 0) {
            foreach (($departments->result()) as $row) {
                $data[] = $row;
            }
        }
        return $data;
    }

    public function getDepartmentById($id)
    {
        $q = $this->db->get_where('departments', ['id' => $id]);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function UpdateDepartment($id, $data)
    {
        if ($this->db->update('departments', $data, ['id' => $id])) {
            return true;
        }
        return false;
    }

    public function deleteDepartment($id)
    {
        if ($this->db->delete('departments', ['id' => $id])) {
            return true;
        }
        return false;
    }
}
