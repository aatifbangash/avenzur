<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Delivery_model extends CI_Model
{
    /**
     * Add a new delivery with associated items
     * 
     * @param array $data - Contains 'delivery' and 'items' arrays
     *        $data['delivery'] = array of delivery properties
     *        $data['items'] = array of invoice items to add to delivery
     *        $data['assigned_by'] = user_id of person assigning
     * @return bool|int - Delivery ID on success, false on failure
     */
    public function add_delivery($data)
    {
        if (!isset($data['delivery']) || empty($data['delivery'])) {
            return false;
        }

        $this->db->trans_start();

        // Prepare delivery data
        $delivery_data = $data['delivery'];
        $delivery_data['assigned_by'] = isset($data['assigned_by']) ? $data['assigned_by'] : null;
        $delivery_data['created_at'] = date('Y-m-d H:i:s');
        $delivery_data['updated_at'] = date('Y-m-d H:i:s');

        // Insert delivery record
        $this->db->insert('sma_deliveries', $delivery_data);
        $delivery_id = $this->db->insert_id();

        if ($delivery_id && isset($data['items']) && !empty($data['items'])) {
            // Insert delivery items (invoices)
            foreach ($data['items'] as $item) {
                $item['delivery_id'] = $delivery_id;
                $item['created_at'] = date('Y-m-d H:i:s');
                $this->db->insert('sma_delivery_items', $item);
            }
        }

        // Log the action
        if ($delivery_id && isset($data['assigned_by'])) {
            $this->log_delivery_action($delivery_id, 'created', $data['assigned_by']);
        }

        $this->db->trans_complete();

        if ($this->db->trans_status() === false) {
            return false;
        }

        return $delivery_id;
    }

    /**
     * Get all deliveries with optional filters
     * 
     * @param array $filters - Optional filters (status, driver_name, date_range, etc)
     * @param int $limit - Limit results
     * @param int $offset - Offset for pagination
     * @return array
     */
    public function get_all_deliveries($filters = array(), $limit = null, $offset = 0)
    {
        $this->db->select('sd.id, sd.date_string, sd.driver_name, sd.truck_number, sd.status, sd.assigned_by,
                           COUNT(DISTINCT sdi.invoice_id) as invoice_count,
                           SUM(sdi.quantity_items) as total_items,
                           CONCAT(u.first_name, " ", u.last_name) as assigned_by_name');
        $this->db->from('sma_deliveries sd');
        $this->db->join('sma_delivery_items sdi', 'sd.id = sdi.delivery_id', 'left');
        $this->db->join('sma_users u', 'sd.assigned_by = u.id', 'left');

        // Apply filters
        if (!empty($filters)) {
            if (isset($filters['status']) && !empty($filters['status'])) {
                $this->db->where('sd.status', $filters['status']);
            }
            if (isset($filters['driver_name']) && !empty($filters['driver_name'])) {
                $this->db->like('sd.driver_name', $filters['driver_name']);
            }
            if (isset($filters['truck_number']) && !empty($filters['truck_number'])) {
                $this->db->where('sd.truck_number', $filters['truck_number']);
            }
            if (isset($filters['date_from']) && !empty($filters['date_from'])) {
                $this->db->where('DATE(sd.date_string) >=', $filters['date_from']);
            }
            if (isset($filters['date_to']) && !empty($filters['date_to'])) {
                $this->db->where('DATE(sd.date_string) <=', $filters['date_to']);
            }
        }

        $this->db->group_by('sd.id');
        $this->db->order_by('sd.date_string', 'DESC');

        if ($limit) {
            $this->db->limit($limit, $offset);
        }

        $query = $this->db->get();
        return $query->result();
    }

    /**
     * Get delivery by ID with all details
     * 
     * @param int $delivery_id
     * @return object|false
     */
    public function get_delivery_by_id($delivery_id)
    {
        $this->db->select('sd.id, sd.date_string, sd.driver_name, sd.truck_number, sd.status, sd.assigned_by,
                           CONCAT(u.first_name, " ", u.last_name) as assigned_by_name,
                           COUNT(DISTINCT sdi.invoice_id) as invoice_count,
                           SUM(sdi.quantity_items) as total_items');
        $this->db->from('sma_deliveries sd');
        $this->db->join('sma_users u', 'sd.assigned_by = u.id', 'left');
        $this->db->join('sma_delivery_items sdi', 'sd.id = sdi.delivery_id', 'left');
        $this->db->where('sd.id', $delivery_id);
        $this->db->group_by('sd.id');

        $query = $this->db->get();
        return $query->row();
    }

    /**
     * Get all items (invoices) in a delivery
     * 
     * @param int $delivery_id
     * @return array
     */
    public function get_delivery_items($delivery_id)
    {
        $this->db->select('sdi.*, s.reference_no, s.date as sale_date, s.grand_total as total_amount, 
                           c.name as customer_name');
        $this->db->from('sma_delivery_items sdi');
        $this->db->join('sma_sales s', 'sdi.invoice_id = s.id', 'left');
        $this->db->join('sma_companies c', 's.customer_id = c.id', 'left');
        $this->db->where('sdi.delivery_id', $delivery_id);
        $this->db->order_by('sdi.created_at', 'ASC');

        $query = $this->db->get();
        return $query->result();
    }

    /**
     * Update delivery information
     * 
     * @param int $delivery_id
     * @param array $data - Updated delivery data
     * @param int $updated_by - User ID performing the update
     * @return bool
     */
    public function update_delivery($delivery_id, $data, $updated_by = null)
    {
        $data['updated_at'] = date('Y-m-d H:i:s');

        if ($this->db->update('sma_deliveries', $data, ['id' => $delivery_id])) {
            if ($updated_by) {
                $this->log_delivery_action($delivery_id, 'updated', $updated_by);
            }
            return true;
        }

        return false;
    }

    /**
     * Update delivery status
     * 
     * @param int $delivery_id
     * @param string $status - New status
     * @param int $updated_by - User ID performing the update
     * @return bool
     */
    public function update_delivery_status($delivery_id, $status, $updated_by = null)
    {
        $data = [
            'status' => $status,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        if ($status === 'out_for_delivery' && !isset($data['out_time'])) {
            $data['out_time'] = date('Y-m-d H:i:s');
        }

        if ($this->db->update('sma_deliveries', $data, ['id' => $delivery_id])) {
            if ($updated_by) {
                $this->log_delivery_action($delivery_id, 'status_changed_to_' . $status, $updated_by);
            }
            return true;
        }

        return false;
    }

    /**
     * Add items to an existing delivery
     * 
     * @param int $delivery_id
     * @param array $items - Array of items to add
     * @param int $updated_by - User ID performing the action
     * @return bool
     */
    public function add_items_to_delivery($delivery_id, $items, $updated_by = null)
    {
        if (empty($items) || !is_array($items)) {
            return false;
        }

        $this->db->trans_start();

        foreach ($items as $item) {
            // Check if item already exists in this delivery
            $existing = $this->db->get_where('sma_delivery_items', 
                ['delivery_id' => $delivery_id, 'invoice_id' => $item['invoice_id']]);
            
            if ($existing->num_rows() === 0) {
                $item['delivery_id'] = $delivery_id;
                $item['created_at'] = date('Y-m-d H:i:s');
                $this->db->insert('sma_delivery_items', $item);
            }
        }

        // Update total items count
        $total_items = $this->db->select('SUM(quantity_items) as total')
            ->from('sma_delivery_items')
            ->where('delivery_id', $delivery_id)
            ->get()
            ->row()
            ->total;

        $this->db->update('sma_deliveries', 
            ['total_items_in_delivery_package' => $total_items, 'updated_at' => date('Y-m-d H:i:s')],
            ['id' => $delivery_id]);

        $this->db->trans_complete();

        if ($this->db->trans_status() === false) {
            return false;
        }

        if ($updated_by) {
            $this->log_delivery_action($delivery_id, 'items_added', $updated_by);
        }

        return true;
    }

    /**
     * Remove an item from delivery
     * 
     * @param int $delivery_id
     * @param int $invoice_id
     * @param int $updated_by - User ID performing the action
     * @return bool
     */
    public function remove_item_from_delivery($delivery_id, $invoice_id, $updated_by = null)
    {
        $this->db->trans_start();

        if ($this->db->delete('sma_delivery_items', 
            ['delivery_id' => $delivery_id, 'invoice_id' => $invoice_id])) {
            
            // Update total items count
            $total_items = $this->db->select('SUM(quantity_items) as total')
                ->from('sma_delivery_items')
                ->where('delivery_id', $delivery_id)
                ->get()
                ->row()
                ->total;

            $this->db->update('sma_deliveries', 
                ['total_items_in_delivery_package' => $total_items ?? 0, 'updated_at' => date('Y-m-d H:i:s')],
                ['id' => $delivery_id]);

            $this->db->trans_complete();

            if ($this->db->trans_status() === false) {
                return false;
            }

            if ($updated_by) {
                $this->log_delivery_action($delivery_id, 'item_removed', $updated_by);
            }

            return true;
        }

        return false;
    }

    /**
     * Delete a delivery and its associated items
     * 
     * @param int $delivery_id
     * @param int $deleted_by - User ID performing the deletion
     * @return bool
     */
    public function delete_delivery($delivery_id, $deleted_by = null)
    {
        $this->db->trans_start();

        // Delete delivery items first
        $this->db->delete('sma_delivery_items', ['delivery_id' => $delivery_id]);

        // Delete delivery
        if ($this->db->delete('sma_deliveries', ['id' => $delivery_id])) {
            $this->db->trans_complete();

            if ($this->db->trans_status() === false) {
                return false;
            }

            if ($deleted_by) {
                $this->log_delivery_action($delivery_id, 'deleted', $deleted_by);
            }

            return true;
        }

        return false;
    }

    /**
     * Record when a delivery note is printed (Audit Log)
     * 
     * @param int $delivery_id
     * @param int $printed_by - User ID who printed
     * @param int $print_count - Number of copies printed (default 1)
     * @return bool|int - ID of print log entry on success
     */
    public function log_delivery_print($delivery_id, $printed_by, $print_count = 1)
    {
        $data = [
            'delivery_id' => $delivery_id,
            'printed_by' => $printed_by,
            'print_count' => $print_count,
            'printed_at' => date('Y-m-d H:i:s')
        ];

        if ($this->db->insert('sma_delivery_prints', $data)) {
            $print_log_id = $this->db->insert_id();
            // Also log this as a delivery action
            $this->log_delivery_action($delivery_id, 'delivery_note_printed', $printed_by);
            return $print_log_id;
        }

        return false;
    }

    /**
     * Get all print history for a delivery
     * 
     * @param int $delivery_id
     * @return array
     */
    public function get_delivery_print_history($delivery_id)
    {
        $this->db->select('sdp.*, CONCAT(u.first_name, " ", u.last_name) as printed_by_name');
        $this->db->from('sma_delivery_prints sdp');
        $this->db->join('sma_users u', 'u.id = sdp.printed_by', 'left');
        $this->db->where('sdp.delivery_id', $delivery_id);
        $this->db->order_by('sdp.printed_at', 'DESC');

        $query = $this->db->get();
        return $query->result();
    }

    /**
     * Get all print logs within a date range
     * 
     * @param string $date_from - YYYY-MM-DD format
     * @param string $date_to - YYYY-MM-DD format
     * @return array
     */
    public function get_print_logs_by_date_range($date_from, $date_to)
    {
        $this->db->select('sdp.*, sd.truck_number, CONCAT(u.first_name, " ", u.last_name) as printed_by_name');
        $this->db->from('sma_delivery_prints sdp');
        $this->db->join('sma_deliveries sd', 'sd.id = sdp.delivery_id', 'left');
        $this->db->join('sma_users u', 'u.id = sdp.printed_by', 'left');
        $this->db->where('DATE(sdp.printed_at) >=', $date_from);
        $this->db->where('DATE(sdp.printed_at) <=', $date_to);
        $this->db->order_by('sdp.printed_at', 'DESC');

        $query = $this->db->get();
        return $query->result();
    }

    /**
     * Internal method to log delivery actions (Audit Log)
     * 
     * @param int $delivery_id
     * @param string $action - Action performed
     * @param int $done_by - User ID who performed the action
     * @return bool
     */
    private function log_delivery_action($delivery_id, $action, $done_by)
    {
        $data = [
            'delivery_id' => $delivery_id,
            'action' => $action,
            'done_by' => $done_by,
            'created_at' => date('Y-m-d H:i:s')
        ];

        return $this->db->insert('sma_delivery_audit_logs', $data);
    }

    /**
     * Get audit logs for a specific delivery
     * 
     * @param int $delivery_id
     * @return array
     */
    public function get_delivery_audit_logs($delivery_id)
    {
        $this->db->select('sdal.*, CONCAT(u.first_name, " ", u.last_name) as done_by_name');
        $this->db->from('sma_delivery_audit_logs sdal');
        $this->db->join('sma_users u', 'u.id = sdal.done_by', 'left');
        $this->db->where('sdal.delivery_id', $delivery_id);
        $this->db->order_by('sdal.created_at', 'DESC');

        $query = $this->db->get();
        return $query->result();
    }

    /**
     * Get count of deliveries by status
     * 
     * @return object
     */
    public function get_delivery_status_count()
    {
        $this->db->select('status, COUNT(*) as count');
        $this->db->from('sma_deliveries');
        $this->db->group_by('status');

        $query = $this->db->get();
        $result = $query->result_array();

        $counts = [
            'pending' => 0,
            'assigned' => 0,
            'out_for_delivery' => 0,
            'completed' => 0,
            'cancelled' => 0
        ];

        foreach ($result as $row) {
            if (isset($counts[$row['status']])) {
                $counts[$row['status']] = $row['count'];
            }
        }

        return (object)$counts;
    }

    /**
     * Search deliveries by multiple criteria
     * 
     * @param string $search_term
     * @return array
     */
    public function search_deliveries($search_term)
    {
        $this->db->select('sd.*, 
                           COUNT(DISTINCT sdi.invoice_id) as invoice_count,
                           CONCAT(u.first_name, " ", u.last_name) as assigned_by_name');
        $this->db->from('sma_deliveries sd');
        $this->db->join('sma_delivery_items sdi', 'sd.id = sdi.delivery_id', 'left');
        $this->db->join('sma_users u', 'sd.assigned_by = u.id', 'left');

        $this->db->group_start();
        $this->db->like('sd.driver_name', $search_term);
        $this->db->or_like('sd.truck_number', $search_term);
        $this->db->or_like('sd.status', $search_term);
        $this->db->group_end();

        $this->db->group_by('sd.id');
        $this->db->order_by('sd.created_at', 'DESC');

        $query = $this->db->get();
        return $query->result();
    }

    /**
     * Get deliveries assigned to a specific driver
     * 
     * @param string $driver_name
     * @param string $status - Optional status filter
     * @return array
     */
    public function get_deliveries_by_driver($driver_name, $status = null)
    {
        $this->db->select('sd.*, 
                           COUNT(DISTINCT sdi.invoice_id) as invoice_count,
                           SUM(sdi.quantity_items) as total_items');
        $this->db->from('sma_deliveries sd');
        $this->db->join('sma_delivery_items sdi', 'sd.id = sdi.delivery_id', 'left');
        $this->db->where('sd.driver_name', $driver_name);

        if ($status) {
            $this->db->where('sd.status', $status);
        }

        $this->db->group_by('sd.id');
        $this->db->order_by('sd.created_at', 'DESC');

        $query = $this->db->get();
        return $query->result();
    }

    /**
     * Get assignment status for an invoice
     * 
     * @param int $invoice_id
     * @return array|null - Contains driver_name and delivery_status if assigned, null otherwise
     */
    public function get_invoice_assignment_status($invoice_id)
    {
        $this->db->select('d.driver_name, d.status as delivery_status');
        $this->db->from('sma_delivery_items di');
        $this->db->join('sma_deliveries d', 'di.delivery_id = d.id', 'left');
        $this->db->where('di.invoice_id', $invoice_id);
        $this->db->limit(1);
        
        $query = $this->db->get();
        $result = $query->row();
        
        return $result ? (array) $result : null;
    }

}
