<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Cmt_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function addNotification($data)
    {
        if ($this->db->insert('notifications', $data)) {
            return true;
        } else {
            return false;
        }
    }

    public function getRasdNotifications(){
        $this->db->where('status', 'pending');
        $notifications = $this->db->get('sma_rasd_notifications');
        if ($notifications->num_rows() > 0) {
            foreach (($notifications->result()) as $row) {
                $data[] = $row;
            }

            return $data;
        }else{
            return false;
        }
    }

    public function addRasdNotification($data, $batch_data){
        if ($this->db->insert('rasd_notifications', $data)) {

            if(sizeOf($batch_data) > 0){
                $this->db->insert_batch('sma_notification_serials', $batch_data);
            }

            return true;
        } else {
            return false;
        }
    }

    public function deleteRasdNotification($id){
        $notification = $this->db->get_where('rasd_notifications', ['id' => $id], 1);
        if ($notification->num_rows() > 0) {
            $notificationObj = $notification->row();
            //echo $notificationObj->dispatch_id;exit;
            $q = $this->db->get_where('sma_notification_serials', ['notification_id' => $notificationObj->dispatch_id], 1);
            if ($q->num_rows() > 0) {
                return false;
            }else{
                if ($this->db->delete('rasd_notifications', ['id' => $id])) {
                    return true;
                }
                return false;
            }  
        }else{
            return false;
        }
    }

    public function deleteComment($id)
    {
        if ($this->db->delete('notifications', ['id' => $id])) {
            return true;
        }
        return false;
    }

    public function getAllComments()
    {
        $q = $this->db->get('notifications');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }

            return $data;
        }
    }

    public function getCommentByID($id)
    {
        $q = $this->db->get_where('notifications', ['id' => $id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }

        return false;
    }

    public function getNotifications()
    {
        $date = date('Y-m-d H:i:s', time());
        $this->db->where('from_date <=', $date);
        $this->db->where('till_date >=', $date);
        if (!$this->Owner) {
            if ($this->Supplier) {
                $this->db->where('scope', 4);
            } elseif ($this->Customer) {
                $this->db->where('scope', 1)->or_where('scope', 3);
            } elseif (!$this->Customer && !$this->Supplier) {
                $this->db->where('scope', 2)->or_where('scope', 3);
            }
        }
        $q = $this->db->get('notifications');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }

            return $data;
        }
    }

    public function updateNotification($id, $data)
    {
        $this->db->where('id', $id);
        if ($this->db->update('notifications', $data)) {
            return true;
        } else {
            return false;
        }
    }
}

/* End of file pts_model.php */
/* Location: ./application/models/pts_types_model.php */
