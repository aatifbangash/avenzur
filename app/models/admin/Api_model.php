<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Api_model extends CI_Model
{
    private $Settings;

    public function __construct()
    {
        parent::__construct();
        $this->Settings = $this->getSettings();
        $this->load->config('rest');
    }

    protected function getSettings()
    {
        $business_id = $this->ion_auth->user()->row()->business_id;
        $this->db->where("business_id", $business_id);
        $q = $this->db->get('settings');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function addApiKey($data)
    {
        $business_id = $this->ion_auth->user()->row()->business_id;
        $$data["business_id"] = $business_id;
        return $this->db->insert('api_keys', $data);
    }

    public function deleteApiKey($id)
    {
        $business_id = $this->ion_auth->user()->row()->business_id;
        $this->db->where("business_id", $business_id);
        return $this->db->delete('api_keys', ['id' => $id]);
    }

    public function generateKey()
    {
        return $this->_generate_key();
    }

    public function getApiKey($value, $field = 'key')
    {
        $business_id = $this->ion_auth->user()->row()->business_id;
        $this->db->where("business_id", $business_id);
        return  $this->db->get_where('api_keys', [$field => $value])->row();
    }

    public function getApiKeys()
    {
        $business_id = $this->ion_auth->user()->row()->business_id;
        $this->db->where("business_id", $business_id);
        return $this->db->get('api_keys')->result();
    }

    public function getUser($value, $field = 'id')
    {
        $business_id = $this->ion_auth->user()->row()->business_id;
        $this->db->where("business_id", $business_id);
        $q = $this->db->get_where('users', [$field => $value]);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function updateUserApiKey($user_id, $data)
    {
        $business_id = $this->ion_auth->user()->row()->business_id;
        $this->db->where("business_id", $business_id);
        return $this->db->update('api_keys', $data, ['user_id' => $user_id]);
    }

    private function _delete_key($key)
    {
        $business_id = $this->ion_auth->user()->row()->business_id;
        $this->db->where("business_id", $business_id);
        return $this->db
            ->where($this->config->item('rest_key_column'), $key)
            ->delete($this->config->item('rest_keys_table'));
    }

    private function _generate_key()
    {
        do {
            $salt = base_convert(bin2hex($this->security->get_random_bytes(64)), 16, 36);

            if ($salt === false) {
                $salt = hash('sha256', time() . mt_rand());
            }

            $new_key = substr($salt, 0, $this->config->item('rest_key_length'));
        } while ($this->_key_exists($new_key));

        return $new_key;
    }

    private function _get_key($key)
    {
        $business_id = $this->ion_auth->user()->row()->business_id;
        $this->db->where("business_id", $business_id);
        return $this->db
            ->where($this->config->item('rest_key_column'), $key)
            ->get($this->config->item('rest_keys_table'))
            ->row();
    }

    private function _insert_key($key, $data)
    {
        $data[$this->config->item('rest_key_column')] = $key;
        $data['date_created']                         = function_exists('now') ? now() : time();

        return $this->db
            ->set($data)
            ->insert($this->config->item('rest_keys_table'));
    }

    private function _key_exists($key)
    {
        $business_id = $this->ion_auth->user()->row()->business_id;
        $this->db->where("business_id", $business_id);
        return $this->db
            ->where($this->config->item('rest_key_column'), $key)
            ->count_all_results($this->config->item('rest_keys_table')) > 0;
    }

    private function _update_key($key, $data)
    {
        $business_id = $this->ion_auth->user()->row()->business_id;
        $this->db->where("business_id", $business_id);
        return $this->db
            ->where($this->config->item('rest_key_column'), $key)
            ->update($this->config->item('rest_keys_table'), $data);
    }
}
