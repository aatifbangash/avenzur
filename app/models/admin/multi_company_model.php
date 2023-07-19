<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Multi_company_model extends CI_Model
{
//    protected string $table = 'multi_company';


    public function __construct()
    {
        parent::__construct();
    }

    public function setDbIsUsed($db)
    {

        $this->db->set('is_used', 1);
        $this->db->where('id', $db->id);
        return $this->db->update('dbs');
    }

    public function getAllDatabase()
    {
        return $this->db->select('t2.id as companyId, t1.id as dbId, t1.db_name, t1.db_user, t1.db_pass ')
            ->from('dbs as t1')
            ->where('t1.is_used', 1)
            ->join('multi_company  as t2', 't1.id = t2.db_id')
            ->get()->result();
    }

    public function insertCompany($data)
    {
        $insertArray = [
            "name" => $data["company_name"],
            "slug" => $data["slug"],
            "db_id" => $data["db_id"],
            "owner_id" => $data["owner_id"],
        ];

        if ($this->db->insert('multi_company', $insertArray))
            return $this->db->insert_id();

        return false;
    }

    public function getUnUsedDb()
    {
        return $this->db->get_where('dbs', ['is_used' => 0])->row();
    }

    public function getSingleCompany($companyId)
    {
        return $this->db->get_where("multi_company", ['id' => $companyId])->row();
    }
}