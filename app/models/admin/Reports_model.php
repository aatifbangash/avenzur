<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Reports_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();

        $this->load->admin_model('companies_model');
    }

    public function getCompanyLedgers()
    {
        $this->db
            ->select('sma_accounts_ledgers.*')
            ->from('sma_accounts_ledgers')
            ->order_by('sma_accounts_ledgers.name asc');
        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data_res[] = $row;
            }
        } else {
            $data_res = array();
        }

        return $data_res;
    }

    public function getLedgerGroups()
    {
        $this->db
            ->select('sma_accounts_groups.*')
            ->from('sma_accounts_groups')
            ->join('sma_accounts_ledgers', 'sma_accounts_ledgers.group_id = sma_accounts_groups.id')
            ->where('sma_accounts_groups.type1', 'B/S')
            ->group_by('sma_accounts_groups.id')
            ->order_by('sma_accounts_groups.id asc');
        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data_res[] = $row;
            }
        } else {
            $data_res = array();
        }

        return $data_res;
    }

    public function getPLLedgerGroups()
    {
        $this->db
            ->select('sma_accounts_groups.*')
            ->from('sma_accounts_groups')
            ->join('sma_accounts_ledgers', 'sma_accounts_ledgers.group_id = sma_accounts_groups.id')
            ->where('sma_accounts_groups.type1', 'P/L')
            ->group_by('sma_accounts_groups.id')
            ->order_by('sma_accounts_groups.id asc');
        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data_res[] = $row;
            }
        } else {
            $data_res = array();
        }

        return $data_res;
    }

    public function getIncome($date)
    {
        $response = array();

        $types = array('Revenue', 'Other Income');

        $this->db
            ->select('sma_accounts_ledgers.id, sma_accounts_ledgers.group_id, sma_accounts_ledgers.name, sma_accounts_ledgers.category,
                SUM(CASE WHEN sma_accounts_entryitems.dc = "C" THEN sma_accounts_entryitems.amount ELSE 0 END) AS credit_sum,
                SUM(CASE WHEN sma_accounts_entryitems.dc = "D" THEN sma_accounts_entryitems.amount ELSE 0 END) AS debit_sum')
            ->from('sma_accounts_ledgers')
            ->join('sma_accounts_entryitems', 'sma_accounts_entryitems.ledger_id = sma_accounts_ledgers.id', 'left')
            ->join('sma_accounts_entries', 'sma_accounts_entries.id = sma_accounts_entryitems.entry_id')
            ->where('sma_accounts_ledgers.type1', 'P/L')
            ->where_in('sma_accounts_ledgers.type2', $types)
            ->group_by('sma_accounts_ledgers.id');
        $q = $this->db->get();
        if (!empty($q)) {
            if ($q->num_rows() > 0) {
                foreach (($q->result()) as $row) {
                    $data_res[] = $row;
                }
            } else {
                $data_res = array();
            }
        } else {
            $data_res = array();
        }

        return $data_res;
    }

    public function getExpense($date)
    {
        $response = array();

        $types = array('Cost Of Sales', 'Operating Expenses', 'Other Expenses');

        $this->db
            ->select('sma_accounts_ledgers.id, sma_accounts_ledgers.group_id, sma_accounts_ledgers.name, sma_accounts_ledgers.category,
                SUM(CASE WHEN sma_accounts_entryitems.dc = "C" THEN sma_accounts_entryitems.amount ELSE 0 END) AS credit_sum,
                SUM(CASE WHEN sma_accounts_entryitems.dc = "D" THEN sma_accounts_entryitems.amount ELSE 0 END) AS debit_sum')
            ->from('sma_accounts_ledgers')
            ->join('sma_accounts_entryitems', 'sma_accounts_entryitems.ledger_id = sma_accounts_ledgers.id', 'left')
            ->join('sma_accounts_entries', 'sma_accounts_entries.id = sma_accounts_entryitems.entry_id')
            ->where('sma_accounts_ledgers.type1', 'P/L')
            ->where_in('sma_accounts_ledgers.type2', $types)
            ->group_by('sma_accounts_ledgers.id');
        $q = $this->db->get();
        if (!empty($q)) {
            if ($q->num_rows() > 0) {
                foreach (($q->result()) as $row) {
                    $data_res[] = $row;
                }
            } else {
                $data_res = array();
            }
        } else {
            $data_res = array();
        }

        return $data_res;
    }

    public function getEquityBalance($date)
    {
        $response = array();

        $this->db
            ->select('sma_accounts_ledgers.id, sma_accounts_ledgers.group_id, sma_accounts_ledgers.name, sma_accounts_ledgers.category,
                SUM(CASE WHEN sma_accounts_entryitems.dc = "C" THEN sma_accounts_entryitems.amount ELSE 0 END) AS credit_sum,
                SUM(CASE WHEN sma_accounts_entryitems.dc = "D" THEN sma_accounts_entryitems.amount ELSE 0 END) AS debit_sum')
            ->from('sma_accounts_ledgers')
            ->join('sma_accounts_entryitems', 'sma_accounts_entryitems.ledger_id = sma_accounts_ledgers.id', 'left')
            ->join('sma_accounts_entries', 'sma_accounts_entries.id = sma_accounts_entryitems.entry_id')
            ->where('sma_accounts_ledgers.type1', 'B/S')
            ->where('sma_accounts_ledgers.type2', 'Equity')
            ->group_by('sma_accounts_ledgers.id');
        $q = $this->db->get();
        if (!empty($q)) {
            if ($q->num_rows() > 0) {
                foreach (($q->result()) as $row) {
                    $data_res[] = $row;
                }
            } else {
                $data_res = array();
            }
        } else {
            $data_res = array();
        }

        return $data_res;
    }

    public function getLiabilitiesBalance($date)
    {
        $response = array();

        $this->db
            ->select('sma_accounts_ledgers.id, sma_accounts_ledgers.group_id, sma_accounts_ledgers.name, sma_accounts_ledgers.category,
                SUM(CASE WHEN sma_accounts_entryitems.dc = "C" THEN sma_accounts_entryitems.amount ELSE 0 END) AS credit_sum,
                SUM(CASE WHEN sma_accounts_entryitems.dc = "D" THEN sma_accounts_entryitems.amount ELSE 0 END) AS debit_sum')
            ->from('sma_accounts_ledgers')
            ->join('sma_accounts_entryitems', 'sma_accounts_entryitems.ledger_id = sma_accounts_ledgers.id', 'left')
            ->join('sma_accounts_entries', 'sma_accounts_entries.id = sma_accounts_entryitems.entry_id')
            ->where('sma_accounts_ledgers.type1', 'B/S')
            ->where('sma_accounts_ledgers.type2', 'Liabilities')
            ->group_by('sma_accounts_ledgers.id');
        $q = $this->db->get();
        if (!empty($q)) {
            if ($q->num_rows() > 0) {
                foreach (($q->result()) as $row) {
                    $data_res[] = $row;
                }
            } else {
                $data_res = array();
            }
        } else {
            $data_res = array();
        }

        return $data_res;
    }

    public function getAssetsBalance($date)
    {
        $response = array();
        $this->db
            ->select('sma_accounts_ledgers.id, sma_accounts_ledgers.group_id, sma_accounts_ledgers.name, sma_accounts_ledgers.category,
                SUM(CASE WHEN sma_accounts_entryitems.dc = "C" THEN sma_accounts_entryitems.amount ELSE 0 END) AS credit_sum,
                SUM(CASE WHEN sma_accounts_entryitems.dc = "D" THEN sma_accounts_entryitems.amount ELSE 0 END) AS debit_sum')
            ->from('sma_accounts_ledgers')
            ->join('sma_accounts_entryitems', 'sma_accounts_entryitems.ledger_id = sma_accounts_ledgers.id', 'left')
            ->join('sma_accounts_entries', 'sma_accounts_entries.id = sma_accounts_entryitems.entry_id')
            ->where('sma_accounts_ledgers.type1', 'B/S')
            ->where('sma_accounts_ledgers.type2', 'Assets')
            ->group_by('sma_accounts_ledgers.id');
        $q = $this->db->get();
        if (!empty($q)) {
            if ($q->num_rows() > 0) {
                foreach (($q->result()) as $row) {
                    $data_res[] = $row;
                }
            } else {
                $data_res = array();
            }
        } else {
            $data_res = array();
        }

        return $data_res;
    }

    public function getUserStats($date)
    {
        $response = array();
        $dateObj = DateTime::createFromFormat('d/m/Y', $date);
        if ($dateObj) {
            $date = $dateObj->format('Y-m-d');
        } else {
            // Handle error if the date format is incorrect
            return array(); // or some error message
        }
        $start_date = $date . ' 00:00:00';
        $end_date = $date . ' 23:59:59';

        // Adjust the time to account for the 3-hour difference
        $start_date = date('Y-m-d H:i:s', strtotime($start_date) - 3 * 3600);
        $end_date = date('Y-m-d H:i:s', strtotime($end_date) - 3 * 3600);

        $data_res = array();
        $this->db
            ->select('Count(DISTINCT landing_url) as page_views, location, is_bot, COUNT(DISTINCT ip_address) as unique_users, COUNT(*) as impressions, user_agent')
            ->from('sma_user_logs')
            ->where('is_bot', 0)
            ->where('user_agent NOT LIKE ', 'bot')
            ->where('access_time >=', $start_date)
            ->where('access_time <=', $end_date)
            ->group_by('location')
            ->order_by('unique_users', 'DESC');
        $q = $this->db->get();
        //echo $this->db->last_query();exit;
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data_res[] = $row;
            }
        } else {
            $data_res = array();
        }
        //echo '<pre>';print_r($data_res);exit;

        $response['user_stats'] = $data_res;

        // Social Media Campaigns 

        /*$this->db
            ->select("
                SUM(CASE WHEN landing_url LIKE '%fbclid%' THEN 1 ELSE 0 END) AS facebook_traffic,
                SUM(CASE WHEN landing_url LIKE '%utm_source=fb%' THEN 1 ELSE 0 END) AS facebook_click,
                SUM(CASE WHEN landing_url LIKE '%snapchat%' THEN 1 ELSE 0 END) AS snapchat_traffic,
                SUM(CASE WHEN landing_url LIKE '%wbraid%' THEN 1 ELSE 0 END) AS google_video_360_ad,
                SUM(CASE WHEN landing_url LIKE '%gbraid%' THEN 1 ELSE 0 END) AS google_ad_campaign,
                SUM(CASE WHEN landing_url LIKE '%gclid%' THEN 1 ELSE 0 END) AS google_click,
                SUM(CASE WHEN 
                    landing_url NOT LIKE '%fbclid%' AND
                    landing_url NOT LIKE '%utm_source=fb%' AND
                    landing_url NOT LIKE '%snapchat%' AND
                    landing_url NOT LIKE '%wbraid%' AND
                    landing_url NOT LIKE '%gbraid%' AND
                    landing_url NOT LIKE '%gclid%' 
                THEN 1 ELSE 0 END) AS other_traffic
            ")*/
        $this->db
            ->select("
                SUM(CASE WHEN landing_url LIKE '%fbclid%' THEN 1 ELSE 0 END) AS facebook_traffic,
                SUM(CASE WHEN landing_url LIKE '%utm_source=fb%' THEN 1 ELSE 0 END) AS facebook_click,
                SUM(CASE WHEN landing_url LIKE '%snapchat%' THEN 1 ELSE 0 END) AS snapchat_traffic,
                SUM(CASE WHEN landing_url LIKE '%wbraid%' THEN 1 ELSE 0 END) AS google_video_360_ad,
                SUM(CASE WHEN landing_url LIKE '%gbraid%' THEN 1 ELSE 0 END) AS google_ad_campaign,
                SUM(CASE WHEN landing_url LIKE '%gclid%' THEN 1 ELSE 0 END) AS google_click
            ")
            ->from('sma_user_logs')
            ->where('is_bot', 0)
            ->where('user_agent NOT LIKE ', 'bot')
            ->where('access_time >=', $start_date)
            ->where('access_time <=', $end_date);

        $q = $this->db->get();
        $data_res = array();
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data_res[] = $row;
            }
        } else {
            $data_res = array();
        }

        $response['social_stats'] = $data_res[0];
        //print_r($response['social_stats']);exit;
        // Prepare the SQL query
        $sql = "
        SELECT
        (SELECT COUNT(*) FROM sma_user_logs WHERE is_bot = 0 AND user_agent NOT LIKE 'bot' AND access_time >= ? AND access_time <= ?) AS impressions,
        (SELECT COUNT(DISTINCT landing_url) FROM sma_user_logs WHERE is_bot = 0 AND user_agent NOT LIKE 'bot' AND access_time >= ? AND access_time <= ?) AS page_views,
        (SELECT COUNT(DISTINCT ip_address) FROM sma_user_logs WHERE is_bot = 0 AND user_agent NOT LIKE 'bot' AND access_time >= ? AND access_time <= ?) AS unique_users,
        (SELECT COUNT(*) FROM sma_sales WHERE payment_status = 'paid' AND shop = 1 AND sale_status = 'completed' AND date >= ? AND date <= ?) AS total_orders,
        (SELECT COUNT(*) FROM sma_sales WHERE payment_status = 'paid' AND shop = 1 AND sale_status = 'completed' AND courier_delivery_time >= ? AND courier_delivery_time <= ?) AS total_orders_delivered,
        (SELECT COUNT(*) FROM sma_users WHERE group_id = 3 AND active = 1 AND FROM_UNIXTIME(last_login) >= ? AND FROM_UNIXTIME(last_login) <= ?) AS total_logins";
        $query = $this->db->query($sql, array($start_date, $end_date, $start_date, $end_date, $start_date, $end_date, $start_date, $end_date, $start_date, $end_date, $start_date, $end_date));

        // Fetch the result
        if ($query->num_rows() > 0) {
            $data_res = $query->row_array();
        } else {
            $data_res = array(
                'page_views' => 0,
                'total_orders' => 0,
                'total_orders_delivered' => 0,
                'total_logins' => 0,
                'unique_users' => 0,
                'impressions' => 0
            );
        }

        $response['daily_stats'] = $data_res;


        $data_res = array();
        $this->db
            ->select('
                sma_sales.id, 
                sma_sales.courier_id, 
                sma_sales.total as order_value, 
                DATE_ADD(sma_sales.date, INTERVAL 3 HOUR) as order_time,
                DATE_ADD(sma_sales.courier_assignment_time, INTERVAL 3 HOUR) as assignment_time, 
                DATE_ADD(sma_sales.courier_pickup_time, INTERVAL 3 HOUR) as pickup_time, 
                DATE_ADD(sma_sales.courier_delivery_time, INTERVAL 3 HOUR) as delivery_time, 
                sma_companies.city as location, 
                sma_courier.name as courier_name
            ')
            ->from('sma_sales')
            ->join('sma_companies', 'sma_companies.id=sma_sales.customer_id')
            ->join('sma_courier', 'sma_courier.id=sma_sales.courier_id', 'left')
            ->where('sma_sales.shop', 1)
            ->where('sma_sales.sale_status', 'completed')
            ->where('sma_sales.payment_status', 'paid')
            //->where('sma_sales.date >=', $start_date)
            //->where('sma_sales.date <=', $end_date)
            ->group_start() // Start a group for OR conditions
            ->where('sma_sales.date >=', $start_date)
            ->where('sma_sales.date <=', $end_date)
            ->or_group_start() // Start a nested group for the delivery time condition
            ->where('sma_sales.courier_delivery_time >=', $start_date)
            ->where('sma_sales.courier_delivery_time <=', $end_date)
            ->group_end() // End the nested group
            ->group_end() // End the main group
            ->order_by('sma_sales.id', 'DESC');
        $q = $this->db->get();
        //echo $this->db->last_query();exit;
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data_res[] = $row;
            }
        } else {
            $data_res = array();
        }

        $response['order_stats'] = $data_res;

        return $response;
    }

    public function getCustomerAging($duration, $start_date, $supplier_id_array)
    {

        $response = array();
        $intervals = [30, 60, 90, 120, 150, 180, 210, 240];
        $cases = [];
        $previous_limit = 0;

        // Always include the "Current" case
        /*$cases[] = "SUM(CASE 
            WHEN DATEDIFF(CURDATE(), ae.date) <= c.payment_term THEN 
                CASE WHEN ei.dc = 'D' THEN -ei.amount ELSE ei.amount END
            ELSE 0 
        END) AS 'Current'";*/

        if (empty($start_date)) {
            $start_date = date('Y-m-d');
        }
        $queryCondition = '';
        if (count($supplier_id_array) > 0) {
            $supplier_ids = implode(',', $supplier_id_array);
            $queryCondition = " AND c.id IN($supplier_ids)";
        }
        $count = 1;
        foreach ($intervals as $index => $interval) {
            if ($interval > $duration) {
                break;
            }
            if ($count == 1) {
                $start = $previous_limit;
            } else {
                $start = $previous_limit + 1;
            }
            $end = $interval;
            $previous_limit = $end;

            $cases[] = "SUM(CASE 
                WHEN DATEDIFF('$start_date', ae.date) BETWEEN ($start) AND ($end) THEN 
                    CASE WHEN ei.dc = 'D' THEN ei.amount ELSE -ei.amount END
                ELSE 0 
            END) AS '$start-$end'";
            $count = $count + 1;
        }

        // Add the "greater than" case for the selected duration
        $cases[] = "SUM(CASE 
            WHEN DATEDIFF('$start_date', ae.date) > ($duration) THEN 
                CASE WHEN ei.dc = 'D' THEN ei.amount ELSE -ei.amount END
            ELSE 0 
        END) AS '>$duration'";

        $cases_str = implode(",\n", $cases);

        $q = $this->db->query("SELECT 
            c.id AS customer_id,
            c.name AS customer_name,
            c.payment_term,
            $cases_str
        FROM 
            sma_companies c
        JOIN 
            sma_accounts_entries ae ON c.id = ae.customer_id
        JOIN 
            sma_accounts_entryitems ei ON ae.id = ei.entry_id
        JOIN 
            sma_accounts_ledgers al ON c.ledger_account = al.id
        WHERE 
            ei.ledger_id = c.ledger_account $queryCondition

        GROUP BY 
            c.id, c.name");

        //echo $this->db->last_query();exit;

        $data = array();
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
        }

        return $data;
    }

    public function getSupplierAging($duration, $start_date, $supplier_id_array)
    {
        $response = array();
        $intervals = [30, 60, 90, 120, 150, 180, 210, 240];
        $cases = [];
        $previous_limit = 0;
        if (empty($start_date)) {
            $start_date = date('Y-m-d');
        }
        $queryCondition = '';
        if (count($supplier_id_array) > 0) {
            $supplier_ids = implode(',', $supplier_id_array);
            $queryCondition = " AND c.id IN($supplier_ids)";
        }
        // Always include the "Current" case
        /*$cases[] = "SUM(CASE 
            WHEN DATEDIFF(CURDATE(), ae.date) <= c.payment_term THEN 
                CASE WHEN ei.dc = 'D' THEN -ei.amount ELSE ei.amount END
            ELSE 0 
        END) AS 'Current'";*/
        $count = 1;
        foreach ($intervals as $index => $interval) {
            if ($interval > $duration) {
                break;
            }

            if ($count == 1) {
                $start = $previous_limit;
            } else {
                $start = $previous_limit + 1;
            }
            $end = $interval;
            $previous_limit = $end;
            // replaced CURDATE() with   $start_date 
            $cases[] = "SUM(CASE 
                WHEN DATEDIFF('$start_date', ae.date) BETWEEN ($start) AND ($end) THEN 
                    CASE WHEN ei.dc = 'D' THEN -ei.amount ELSE ei.amount END
                ELSE 0 
            END) AS '$start-$end'";

            $count = $count + 1;
        }

        // Add the "greater than" case for the selected duration
        $cases[] = "SUM(CASE 
            WHEN DATEDIFF('$start_date', ae.date) > ($duration) THEN 
                CASE WHEN ei.dc = 'D' THEN -ei.amount ELSE ei.amount END
            ELSE 0 
        END) AS '>$duration'";

        $cases_str = implode(",\n", $cases);

        $q = $this->db->query("SELECT 
            c.id AS supplier_id,
            c.name AS supplier_name,
            c.payment_term,
            $cases_str
        FROM 
            sma_companies c
        JOIN 
            sma_accounts_entries ae ON c.id = ae.supplier_id
        JOIN 
            sma_accounts_entryitems ei ON ae.id = ei.entry_id
        JOIN 
            sma_accounts_ledgers al ON c.ledger_account = al.id
        WHERE 
            ei.ledger_id = c.ledger_account $queryCondition 
        GROUP BY 
            c.id, c.name");

        $data = array();
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
        }

        return $data;
    }

    /*public function getSupplierAging($duration)
    {
        $response = array();

        $q = $this->db->query("SELECT 
            c.id AS supplier_id,
            c.name AS supplier_name,
            SUM(CASE 
                    WHEN DATEDIFF(CURDATE(), ae.date) <= 30 THEN 
                        CASE WHEN ei.dc = 'D' THEN -ei.amount ELSE ei.amount END
                    ELSE 0 
                END) AS 'Current',
            SUM(CASE 
                    WHEN DATEDIFF(CURDATE(), ae.date) BETWEEN 31 AND 60 THEN 
                        CASE WHEN ei.dc = 'D' THEN -ei.amount ELSE ei.amount END
                    ELSE 0 
                END) AS '31-60',
            SUM(CASE 
                    WHEN DATEDIFF(CURDATE(), ae.date) BETWEEN 61 AND 90 THEN 
                        CASE WHEN ei.dc = 'D' THEN -ei.amount ELSE ei.amount END
                    ELSE 0 
                END) AS '61-90',
            SUM(CASE 
                    WHEN DATEDIFF(CURDATE(), ae.date) BETWEEN 91 AND 120 THEN 
                        CASE WHEN ei.dc = 'D' THEN -ei.amount ELSE ei.amount END
                    ELSE 0 
                END) AS '91-120',
            SUM(CASE 
                    WHEN DATEDIFF(CURDATE(), ae.date) > 120 THEN 
                        CASE WHEN ei.dc = 'D' THEN -ei.amount ELSE ei.amount END
                    ELSE 0 
                END) AS '>120'
        FROM 
            sma_companies c
        JOIN 
            sma_accounts_entries ae ON c.id = ae.supplier_id
        JOIN 
            sma_accounts_entryitems ei ON ae.id = ei.entry_id
        JOIN 
            sma_accounts_ledgers al ON c.ledger_account = al.id
         WHERE 
            ei.ledger_id = c.ledger_account
        
        GROUP BY 
            c.id, c.name");

        $data = array();
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
        }

        return $data;
    }*/

    public function getTimeRange($date)
    {
        $currentDate = new DateTime(); // Current date
        $entryDate = new DateTime($date); // Date of the entry

        $interval = $entryDate->diff($currentDate); // Calculate the difference between the current date and entry date

        if ($interval->days <= 30) {
            return 'Current';
        } elseif ($interval->days <= 60) {
            return '1-30';
        } elseif ($interval->days <= 90) {
            return '31-60';
        } elseif ($interval->days <= 120) {
            return '61-90';
        } elseif ($interval->days <= 150) {
            return '91-120';
        } else {
            return '>120';
        }
    }

    public function getGeneralLedgerStatement($start_date, $end_date, $supplier_id, $ledger_account)
    {
        $response = array();

        $this->db
            ->select('COALESCE(sum(sma_accounts_entryitems.amount), 0) as total_amount, sma_accounts_entryitems.dc')
            ->from('sma_accounts_entryitems')
            //->join('sma_accounts_entryitems', 'sma_accounts_entryitems.ledger_id=companies.ledger_account')
            ->join('sma_accounts_entries', 'sma_accounts_entries.id=sma_accounts_entryitems.entry_id')
            ->where('sma_accounts_entryitems.ledger_id', $ledger_account)
            ->where('sma_accounts_entries.date <', $start_date);
        //->group_by('sma_accounts_entryitems.dc');
        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data_res[] = $row;
            }
        } else {
            $data_res = array();
        }

        $this->db
            ->select('sma_accounts_entryitems.entry_id, sma_accounts_entryitems.amount, sma_accounts_entryitems.dc, sma_accounts_entryitems.narration, sma_accounts_entries.transaction_type, sma_accounts_entries.date, sma_accounts_ledgers.code, sma_accounts_ledgers.name, (select sum(amount) from sma_accounts_entryitems ei inner join sma_accounts_entries e on e.id =ei.entry_id where e.date < `sma_accounts_entries`.`date` and ei.ledger_id = ' . $ledger_account . ') as openingAmount,')
            ->from('sma_accounts_entryitems')
            ->join('sma_accounts_entries', 'sma_accounts_entries.id=sma_accounts_entryitems.entry_id')
            ->join('sma_accounts_ledgers', 'sma_accounts_ledgers.id=sma_accounts_entryitems.ledger_id')
            ->where('sma_accounts_entryitems.ledger_id', $ledger_account)
            ->where('sma_accounts_entries.date >=', $start_date)
            ->where('sma_accounts_entries.date <=', $end_date)
            ->order_by('sma_accounts_entries.date asc');

        $q = $this->db->get();

        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
        } else {
            $data = array();
        }

        $response_array = array('ob' => $data_res, 'report' => $data);
        //dd($response_array);
        return $response_array;
    }

    public function getSupplierStatement($start_date, $end_date, $supplier_id, $ledger_account)
    {
        $response = array();
        $supplier_info = $this->companies_model->getCompanyByID($supplier_id);

        if (!$supplier_info) {
            return array();
        }

        $supplier_ledger = $supplier_info->ledger_account;

        $this->db
            ->select('sma_accounts_entryitems.entry_id, sma_accounts_entryitems.amount, sma_accounts_entryitems.dc, 
            sma_accounts_entryitems.narration, sma_accounts_entries.transaction_type, 
            sma_accounts_entries.date,
            sma_accounts_entries.sid, 
            sma_accounts_entries.pid,
            sma_accounts_entries.tid,
            sma_accounts_entries.rsid,
            sma_accounts_entries.rid, 
            sma_accounts_ledgers.code, 
            companies.company')
            ->from('sma_accounts_entryitems')
            ->join('sma_accounts_entries', 'sma_accounts_entries.id=sma_accounts_entryitems.entry_id')
            ->join('companies', 'companies.id=sma_accounts_entries.supplier_id')
            ->join('sma_accounts_ledgers', 'sma_accounts_ledgers.id=companies.ledger_account')
            ->where('sma_accounts_entries.supplier_id', $supplier_id)
            ->where('sma_accounts_entries.date <', $start_date)
            ->where('sma_accounts_entryitems.ledger_id', $supplier_ledger)
            ->order_by('sma_accounts_entries.date asc');
        $q = $this->db->get();
        //lq($this);

        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data_res[] = $row;
            }
        } else {
            $data_res = array();
        }

        $this->db
            ->select('sma_accounts_entryitems.entry_id, sma_accounts_entryitems.amount, sma_accounts_entryitems.dc, 
            sma_accounts_entryitems.narration, 
            sma_accounts_entries.transaction_type, sma_accounts_entries.date,
            sma_accounts_entries.sid, 
            sma_accounts_entries.pid,
            sma_accounts_entries.tid,
            sma_accounts_entries.rsid,
            sma_accounts_entries.rid,
            sma_accounts_ledgers.code, 
            companies.company')
            ->from('sma_accounts_entryitems')
            ->join('sma_accounts_entries', 'sma_accounts_entries.id=sma_accounts_entryitems.entry_id')
            ->join('companies', 'companies.id=sma_accounts_entries.supplier_id')
            ->join('sma_accounts_ledgers', 'sma_accounts_ledgers.id=companies.ledger_account')
            ->where('sma_accounts_entries.supplier_id', $supplier_id)
            ->where('sma_accounts_entries.date >=', $start_date)
            ->where('sma_accounts_entries.date <=', $end_date)
            ->where('sma_accounts_entryitems.ledger_id', $supplier_ledger)
            ->order_by('sma_accounts_entries.date asc');

        $q = $this->db->get();

        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
        } else {
            $data = array();
        }

        $response_array = array('ob' => $data_res, 'report' => $data);
        //        dd($response_array);
        return $response_array;
    }

    public function getCustomerStatement($start_date, $end_date, $customer_id, $ledger_account)
    {
        // $response = array();
        // [entry_id] => 11
        // [amount] => 102.5000
        // [dc] => C
        // [narration] => 
        // [transaction_type] => creditmemo
        // [date] => 2024-07-15
        // [code] => 01-01-02-00-0001
        // [openingAmount] => 
        // [company] => 

        $this->db
            ->select('sma_accounts_entryitems.id as entry_id, COALESCE(sum(sma_accounts_entryitems.amount), 0) as amount, 
                    sma_accounts_entryitems.dc, sma_accounts_entryitems.narration, sma_accounts_entries.date, 
                    sma_accounts_ledgers.code, sma_companies.company, sma_accounts_entries.transaction_type')
            ->from('sma_accounts_entryitems')
            ->join('sma_accounts_entries', 'sma_accounts_entries.id=sma_accounts_entryitems.entry_id')
            ->join('sma_accounts_ledgers', 'sma_accounts_entryitems.ledger_id=sma_accounts_ledgers.id')
            ->join('sma_companies', 'sma_companies.id=sma_accounts_entries.customer_id')
            ->where('sma_accounts_entryitems.ledger_id', $ledger_account)
            ->where('sma_accounts_entries.customer_id', $customer_id)
            ->where('sma_accounts_entries.date >=', $start_date)
            ->where('sma_accounts_entries.date <=', $end_date)
            ->group_by('sma_accounts_entryitems.dc')
            ->group_by('sma_accounts_entries.date')
            ->group_by('sma_accounts_entries.transaction_type')
            ->order_by('sma_accounts_entries.date asc');
        $q = $this->db->get();
        //lq($this);

        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data_res[] = $row;
            }
        } else {
            $data_res = array();
        }


        $this->db
            ->select('sma_accounts_entryitems.id as entry_id, COALESCE(sum(sma_accounts_entryitems.amount), 0) as amount, 
                    sma_accounts_entryitems.dc, sma_accounts_entryitems.narration, sma_accounts_entries.date, 
                    sma_accounts_ledgers.code, sma_companies.company, sma_accounts_entries.transaction_type')
            ->from('sma_accounts_entryitems')
            ->join('sma_accounts_entries', 'sma_accounts_entries.id=sma_accounts_entryitems.entry_id')
            ->join('sma_accounts_ledgers', 'sma_accounts_entryitems.ledger_id=sma_accounts_ledgers.id')
            ->join('sma_companies', 'sma_companies.id=sma_accounts_entries.customer_id')
            ->where('sma_accounts_entryitems.ledger_id', $ledger_account)
            ->where('sma_accounts_entries.customer_id', $customer_id)
            ->where('sma_accounts_entries.date <', $start_date)
            ->group_by('sma_accounts_entryitems.dc')
            ->group_by('sma_accounts_entries.date')
            ->group_by('sma_accounts_entries.transaction_type')
            ->order_by('sma_accounts_entries.date asc');
        $q = $this->db->get();
        //lq($this);

        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
        } else {
            $data = array();
        }


        $response_array = array('ob' => $data, 'report' => $data_res);
        // dd($response_array);
        return $response_array;
    }

    public function getGeneralLedgerTrialBalance($start_date, $end_date, $department, $employee)
    {
        $response = array();

        $this->db
            ->select('accounts_ledgers.id, accounts_ledgers.name, accounts_ledgers.notes, accounts_ledgers.code, COALESCE(sum(sma_accounts_entryitems.amount), 0) as total_amount, sma_accounts_entryitems.dc')
            ->from('accounts_ledgers')
            ->join('sma_accounts_entryitems', 'sma_accounts_entryitems.ledger_id=accounts_ledgers.id')
            ->join('sma_accounts_entries', 'sma_accounts_entries.id=sma_accounts_entryitems.entry_id')
            ->where('sma_accounts_entries.date >=', $start_date)
            ->where('sma_accounts_entries.date <=', $end_date);

        if (!empty($employee)) {
            $this->db->where('sma_accounts_entryitems.employee_id', $employee);
        }

        if (!empty($department)) {
            $this->db->where('sma_accounts_entryitems.department_id', $department);
        }

        $this->db
            ->group_by('accounts_ledgers.id, sma_accounts_entryitems.dc')
            ->order_by('accounts_ledgers.name asc');
        $q = $this->db->get();
        //echo $this->db->last_query();exit;
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
        } else {
            $data = array();
        }

        $response['trs'] = $data;

        $this->db
            ->select('accounts_ledgers.id, accounts_ledgers.name, sma_accounts_entries.supplier_id, accounts_ledgers.notes, accounts_ledgers.code, COALESCE(sum(sma_accounts_entryitems.amount), 0) as total_amount, sma_accounts_entryitems.dc')
            ->from('accounts_ledgers')
            ->join('sma_accounts_entryitems', 'sma_accounts_entryitems.ledger_id=accounts_ledgers.id')
            ->join('sma_accounts_entries', 'sma_accounts_entries.id=sma_accounts_entryitems.entry_id')
            ->where('sma_accounts_entries.date <', $start_date);

        if (!empty($employee)) {
            $this->db->where('sma_accounts_entries.employee_id', $employee);
        }

        if (!empty($department)) {
            $this->db->where('sma_accounts_entries.department_id', $department);
        }

        $this->db
            ->group_by('accounts_ledgers.id, sma_accounts_entryitems.dc')
            ->order_by('accounts_ledgers.name asc');

        $q = $this->db->get();
        //echo $this->db->last_query();exit;
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                //print_r($row);exit;
                if ($row->id == 102) {
                    if ($row->supplier_id > 0) {
                        $data2[] = $row;
                    }

                } else {
                    $data2[] = $row;
                }

            }
        } else {
            $data2 = array();
        }

        $response['ob'] = $data2;

        return $response;
    }

    public function getSuppliersTrialBalance($start_date, $end_date)
    {

        $response = array();


        $q = $this->db->query("SELECT
                                c.id,
                                c.sequence_code,
                                c.name,
                                COALESCE(SUM(purchases.total), 0) AS totalPurchases,
                                COALESCE(SUM(purchases.total_tax), 0) AS totalTaxes,
                                COALESCE(SUM(ret.total), 0) AS totalReturn,
                                COALESCE(SUM(py.amount), 0) AS totalPayment,
                                COALESCE(SUM(memo.amount), 0) AS totalMemo
                            FROM sma_companies as c
                            LEFT JOIN (
                                    SELECT supplier_id, SUM(grand_total) AS total, SUM(product_tax) as total_tax
                                    FROM sma_purchases
                                    WHERE grand_total > 0
                                    AND date(date) >='{$start_date}' AND date(date) <='{$end_date}'
                                    GROUP BY supplier_id
                                ) purchases ON c.id = purchases.supplier_id
                            LEFT JOIN (
                                    SELECT supplier_id, SUM(abs(grand_total)) AS total, SUM(abs(product_tax)) as total_tax
                                    FROM sma_purchases
                                    WHERE grand_total < 0
                                    AND date(date) >='{$start_date}' AND date(date) <='{$end_date}'
                                    GROUP BY supplier_id
                                ) ret ON c.id = ret.supplier_id
                            LEFT JOIN (
                                    SELECT s.supplier_id ,SUM(p.amount) as amount
                                    FROM sma_payments p
                                    INNER JOIN sma_purchases s ON s.id = p.purchase_id
                                    WHERE p.purchase_id > 0
                                    AND date(p.date) >='{$start_date}' AND date(p.date) <='{$end_date}'
                                    GROUP BY s.supplier_id
                            ) py ON c.id = py.supplier_id
                            LEFT JOIN (
                                SELECT supplier_id, SUM(payment_amount) AS amount
                                FROM sma_memo
                                WHERE type='memo'
                                AND date(date) >='{$start_date}' AND date(date) <='{$end_date}'
                                GROUP BY supplier_id
                            ) memo ON c.id = memo.supplier_id
                            WHERE c.group_name = 'supplier' #and c.id = 101
                            GROUP BY
                                c.id
                            ORDER BY
                                c.name ASC");

        $data = array();
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
        }

        $response['trs'] = $data;


        $q = $this->db->query("SELECT
                                c.id,
                                c.sequence_code,
                                c.name,
                                COALESCE(SUM(purchases.total), 0) AS totalPurchases,
                                COALESCE(SUM(purchases.total_tax), 0) AS totalTaxes,
                                COALESCE(SUM(ret.total), 0) AS totalReturn,
                                COALESCE(SUM(py.amount), 0) AS totalPayment,
                                COALESCE(SUM(memo.amount), 0) AS totalMemo
                            FROM sma_companies as c
                            LEFT JOIN (
                                    SELECT supplier_id, SUM(grand_total) AS total, SUM(product_tax) as total_tax
                                    FROM sma_purchases
                                    WHERE grand_total > 0
                                    AND  date(date) < '{$start_date}'
                                    GROUP BY supplier_id
                                ) purchases ON c.id = purchases.supplier_id
                            LEFT JOIN (
                                    SELECT supplier_id, SUM(abs(grand_total)) AS total, SUM(abs(product_tax)) as total_tax
                                    FROM sma_purchases
                                    WHERE grand_total < 0
                                    AND  date(date) < '{$start_date}'
                                    GROUP BY supplier_id
                                ) ret ON c.id = ret.supplier_id
                            LEFT JOIN (
                                    SELECT s.supplier_id ,SUM(p.amount) as amount
                                    FROM sma_payments p
                                    INNER JOIN sma_purchases s ON s.id = p.purchase_id
                                    WHERE p.purchase_id > 0
                                    AND date(p.date) < '{$start_date}'
                                    GROUP BY s.supplier_id
                            ) py ON c.id = py.supplier_id
                            LEFT JOIN (
                                SELECT supplier_id, SUM(payment_amount) AS amount
                                FROM sma_memo
                                WHERE type='memo'
                                AND  date(date) < '{$start_date}'
                                GROUP BY supplier_id
                            ) memo ON c.id = memo.supplier_id
                            WHERE c.group_name = 'supplier' #and c.id = 101
                            GROUP BY
                                c.id
                            ORDER BY
                                c.name ASC");


        $data2 = array();
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data2[] = $row;
            }
        }
        $response['ob'] = $data2;
        //dd($response);
        return $response;
    }

    public function get_sales_report_with_promocode_by_order($start_date, $end_date)
    {
        $query = $this->db->query("SELECT 
                si.product_code,
                si.product_name,
                SUM(si.quantity) AS total_quantity_sold,
                SUM(si.subtotal) AS total_amount_sold,
                s.coupon_code,
                s.id,
                s.date,
                c.description
            FROM 
                sma_sale_items si
            JOIN 
                sma_sales s ON si.sale_id = s.id
            JOIN 
                sma_coupons c ON s.coupon_code = c.referrer_code
            WHERE 
                s.date >= '" . $start_date . "'   
                AND s.date <= '" . $end_date . "'  
                AND s.coupon_code IS NOT NULL
                AND s.payment_status = 'paid'
                AND si.product_code IS NOT NULL
                AND JSON_CONTAINS(c.product_ids, CAST(si.product_id AS JSON), '$')  -- Check if product_id is in product_ids
            GROUP BY 
                si.product_code, s.id, s.coupon_code
            HAVING 
                total_quantity_sold > 0
            ORDER BY 
                s.date DESC");

        $results = $query->result_array();
        return $results;
    }

    public function get_sales_report_with_promocode($start_date, $end_date)
    {
        $query = $this->db->query("SELECT 
                si.product_code,
                si.product_name,
                SUM(si.quantity) AS total_quantity_sold,
                SUM(si.subtotal) AS total_amount_sold,
                s.coupon_code,
                s.id
            FROM 
                sma_sale_items si
            JOIN 
                sma_sales s ON si.sale_id = s.id
            JOIN 
                sma_coupons c ON s.coupon_code = c.referrer_code
            WHERE 
                s.date >= '" . $start_date . "'   
                AND s.date <= '" . $end_date . "'  
                AND s.coupon_code IS NOT NULL
                AND si.product_code IS NOT NULL
                AND JSON_CONTAINS(c.product_ids, CAST(si.product_id AS JSON), '$')  -- Check if product_id is in product_ids
            GROUP BY 
                si.product_code, s.coupon_code
            HAVING 
                total_quantity_sold > 0
            ORDER BY 
                s.date DESC");

        $results = $query->result_array();
        return $results;
    }

    public function get_suppliers_trial_balance($start_date, $end_date)
    {
        // Calculate OB
        // $this->db->select('supplier_id, SUM(dr_total) as total_debit, SUM(cr_total) as total_credit');
        // $this->db->where('date <', $start_date);
        // $this->db->where('supplier_id IS NOT NULL', null, false);
        // $this->db->group_by('supplier_id');

        $this->db->select('sma_companies.id as supplier_id, sma_companies.name, sma_companies.sequence_code,
        SUM(CASE WHEN sma_accounts_entryitems.dc = "D" THEN sma_accounts_entryitems.amount ELSE 0 END) as total_debit, 
        SUM(CASE WHEN sma_accounts_entryitems.dc = "C" THEN sma_accounts_entryitems.amount ELSE 0 END) as total_credit');
        $this->db->from('sma_accounts_entries');
        $this->db->join('sma_accounts_entryitems', 'sma_accounts_entries.id = sma_accounts_entryitems.entry_id');
        $this->db->join('sma_companies', 'sma_accounts_entries.supplier_id = sma_companies.id');
        $this->db->where('sma_accounts_entries.date <', $start_date);
        $this->db->where('sma_companies.ledger_account=102');
        $this->db->where('sma_accounts_entryitems.ledger_id = sma_companies.ledger_account');
        $this->db->where('sma_accounts_entries.supplier_id IS NOT NULL', null, false);
        $this->db->group_by('sma_accounts_entries.supplier_id, sma_companies.name');

        $query_ob = $this->db->get();
        //echo $this->db->last_query();
        $ob_results = $query_ob->result_array();
        //print_r($ob_results);

        // Calculate transactions within period
        $this->db->select('sma_companies.id as supplier_id, sma_companies.name, sma_companies.sequence_code, 
        SUM(CASE WHEN sma_accounts_entryitems.dc = "D" THEN sma_accounts_entryitems.amount ELSE 0 END) as total_debit, 
        SUM(CASE WHEN sma_accounts_entryitems.dc = "C" THEN sma_accounts_entryitems.amount ELSE 0 END) as total_credit');
        $this->db->from('sma_accounts_entries');
        $this->db->join('sma_accounts_entryitems', 'sma_accounts_entries.id = sma_accounts_entryitems.entry_id');
        $this->db->join('sma_companies', 'sma_accounts_entries.supplier_id = sma_companies.id');
        $this->db->where('sma_accounts_entries.date >=', $start_date);
        $this->db->where('sma_accounts_entries.date <=', $end_date);
        $this->db->where('sma_accounts_entries.supplier_id IS NOT NULL', null, false);
        $this->db->where('sma_accounts_entryitems.ledger_id = sma_companies.ledger_account');
        $this->db->group_by('sma_accounts_entries.supplier_id, sma_companies.name');

        $query_period = $this->db->get();
        //echo $this->db->last_query();
        $period_results = $query_period->result_array();

        // Combine OB and period transactions to get EB
        $balances = [];
        foreach ($ob_results as $ob) {
            $supplier_id = $ob['supplier_id'];

            if ($ob['total_debit'] >= $ob['total_credit']) {
                $ob['total_debit'] = $ob['total_debit'] - $ob['total_credit'];
                $ob['total_credit'] = 0;
            } else if ($ob['total_credit'] > $ob['total_debit']) {
                $ob['total_credit'] = $ob['total_credit'] - $ob['total_debit'];
                $ob['total_debit'] = 0;
            }

            $balances[$supplier_id] = [
                'supplier_id' => $supplier_id,
                'name' => $ob['name'],
                'sequence_code' => $ob['sequence_code'],
                'obDebit' => $ob['total_debit'],
                'obCredit' => $ob['total_credit'],
                'trsDebit' => 0,
                'trsCredit' => 0
            ];
        }
        // print_r($balances);

        foreach ($period_results as $period) {
            $supplier_id = $period['supplier_id'];
            if (!isset($balances[$supplier_id])) {

                if ($period['total_debit'] >= $period['total_credit']) {
                    //$period['total_debit'] = $period['total_debit'] - $period['total_credit'];
                    //$period['total_credit'] = 0;
                } else if ($period['total_credit'] > $period['total_debit']) {
                    //$period['total_credit'] = $period['total_credit'] - $period['total_debit'];
                    //$period['total_debit'] = 0;
                }

                $balances[$supplier_id] = [
                    'supplier_id' => $supplier_id,
                    'name' => $period['name'],
                    'sequence_code' => $period['sequence_code'],
                    'obDebit' => 0,
                    'obCredit' => 0,
                    'trsDebit' => $period['total_debit'],
                    'trsCredit' => $period['total_credit'],
                ];
            } else {
                if ($period['total_debit'] >= $period['total_credit']) {
                    //$period['total_debit'] = $period['total_debit'] - $period['total_credit'];
                    //$period['total_credit'] = 0;
                } else if ($period['total_credit'] > $period['total_debit']) {
                    //$period['total_credit'] = $period['total_credit'] - $period['total_debit'];
                    //$period['total_debit'] = 0;
                }

                $balances[$supplier_id]['trsDebit'] = $period['total_debit'];
                $balances[$supplier_id]['trsCredit'] = $period['total_credit'];

            }
        }

        return $balances;
    }

    public function getCustomersTrialBalance($start_date, $end_date)
    {

        $response = array();


        $q = $this->db->query("SELECT
                                c.id,
                                c.sequence_code,
                                c.name,
                                `company`,
                                s.grand_total AS sale_total, 
                                r.grand_total as return_total,
                                m.payment_amount AS memo_total,
                                p.amount AS payment_total
                            FROM sma_companies as c
                            LEFT JOIN (SELECT customer_id,SUM(grand_total) as grand_total FROM `sma_sales` WHERE date(date) >='{$start_date}' AND date(date) <='{$end_date}' GROUP BY customer_id) as s ON c.id=s.customer_id
                            LEFT JOIN (SELECT customer_id,SUM(grand_total) as grand_total FROM `sma_returns` WHERE date(date) >='{$start_date}' AND date(date) <='{$end_date}' GROUP BY customer_id) as r ON c.id=r.customer_id
                            LEFT JOIN (SELECT customer_id,SUM(payment_amount) as payment_amount FROM `sma_memo`  WHERE date(date) >='{$start_date}' AND date(date) <='{$end_date}' AND type='creditmemo' GROUP BY customer_id) as m ON c.id=m.customer_id
                            LEFT JOIN(
                                    SELECT s.customer_id ,SUM(p.amount) as amount
                                        FROM sma_payments p
                                        INNER JOIN sma_sales s ON s.id = p.sale_id
                                        WHERE p.sale_id > 0 AND  date(p.date) >='{$start_date}' AND date(p.date) <='{$end_date}'
                                        GROUP BY s.customer_id
                            ) As p on c.id =p.customer_id
                            WHERE c.group_name = 'customer' 
                            GROUP BY
                                c.id
                            ORDER BY
                                c.name ASC");
        echo $this->db->last_query();

        $data = array();
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
        }

        $response['trs'] = $data;


        $q = $this->db->query("SELECT
                                c.id,
                                c.sequence_code,
                                c.name,
                                `company`,
                                s.grand_total AS sale_total, 
                                r.grand_total as return_total,
                                m.payment_amount AS memo_total,
                                p.amount AS payment_total
                            FROM sma_companies as c
                            LEFT JOIN (SELECT customer_id,SUM(grand_total) as grand_total FROM `sma_sales` WHERE date(date) < '{$start_date}'  GROUP BY customer_id) as s ON c.id=s.customer_id
                            LEFT JOIN (SELECT customer_id,SUM(grand_total) as grand_total FROM `sma_returns` WHERE date(date) < '{$start_date}' GROUP BY customer_id) as r ON c.id=r.customer_id
                            LEFT JOIN (SELECT customer_id,SUM(payment_amount) as payment_amount FROM `sma_memo`  WHERE date(date) < '{$start_date}' AND type='creditmemo' GROUP BY customer_id) as m ON c.id=m.customer_id
                            LEFT JOIN(
                                    SELECT s.customer_id ,SUM(p.amount) as amount
                                        FROM sma_payments p
                                        INNER JOIN sma_sales s ON s.id = p.sale_id
                                        WHERE p.sale_id > 0 AND  date(p.date) < '{$start_date}' 
                                        GROUP BY s.customer_id
                            ) As p on c.id =p.customer_id
                            WHERE c.group_name = 'customer' 
                            GROUP BY
                                c.id
                            ORDER BY
                                c.name ASC");

        echo $this->db->last_query();
        $data2 = array();
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data2[] = $row;
            }
        }
        $response['ob'] = $data2;

        return $response;
    }

    public function get_customer_trial_balance($start_date, $end_date)
    {
        $response = array();
        $q = $this->db->query("SELECT 
                sma_companies.id, 
                sma_companies.name,
                sma_companies.company, 
                sma_companies.sequence_code, 
                SUM(CASE WHEN sma_accounts_entryitems.dc = 'D' THEN sma_accounts_entryitems.amount ELSE 0 END) AS total_debit, 
                SUM(CASE WHEN sma_accounts_entryitems.dc = 'C' THEN sma_accounts_entryitems.amount ELSE 0 END) AS total_credit 
            FROM 
                sma_accounts_entries 
            JOIN 
                sma_accounts_entryitems ON sma_accounts_entries.id = sma_accounts_entryitems.entry_id 
            JOIN 
                sma_companies ON sma_accounts_entries.customer_id = sma_companies.id 
            WHERE 
                date(sma_accounts_entries.date) >= '{$start_date}' 
                AND date(sma_accounts_entries.date) <= '{$end_date}' 
                AND sma_accounts_entries.customer_id IS NOT NULL 
                AND sma_accounts_entryitems.ledger_id = sma_companies.ledger_account 
            GROUP BY 
                sma_accounts_entries.customer_id, 
                sma_companies.name");

        $data = array();
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
        }

        $response['trs'] = $data;

        $q = $this->db->query("SELECT 
                    sma_companies.id, 
                    sma_companies.name, 
                    sma_companies.company, 
                    sma_companies.sequence_code, 
                    SUM(CASE WHEN sma_accounts_entryitems.dc = 'D' THEN sma_accounts_entryitems.amount ELSE 0 END) AS total_debit, 
                    SUM(CASE WHEN sma_accounts_entryitems.dc = 'C' THEN sma_accounts_entryitems.amount ELSE 0 END) AS total_credit 
                    FROM 
                    sma_accounts_entries 
                    JOIN 
                    sma_accounts_entryitems ON sma_accounts_entries.id = sma_accounts_entryitems.entry_id 
                    JOIN 
                    sma_companies ON sma_accounts_entries.customer_id = sma_companies.id 
                    WHERE 
                    date(sma_accounts_entries.date) < '{$start_date}' 
                    AND sma_accounts_entryitems.ledger_id = sma_companies.ledger_account 
                    AND sma_accounts_entries.customer_id IS NOT NULL 
                    GROUP BY 
                    sma_accounts_entries.customer_id, 
                    sma_companies.name
                ");
        //echo $this->db->last_query();
        $data2 = array();
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data2[] = $row;
            }
        }
        $response['ob'] = $data2;

        return $response;

    }


    public function getBestSeller($start_date, $end_date, $warehouse_id = null)
    {
        $this->db
            ->select('product_name, product_code')->select_sum('quantity')
            ->join('sales', 'sales.id = sale_items.sale_id', 'left')
            ->where('date >=', $start_date)->where('date <=', $end_date)
            ->group_by('product_name, product_code')->order_by('sum(quantity)', 'desc')->limit(10);
        if ($warehouse_id) {
            $this->db->where('sale_items.warehouse_id', $warehouse_id);
        }
        $q = $this->db->get('sale_items');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    // public function getmonthlyPurchases()
    // {
    //     $myQuery = "SELECT (CASE WHEN date_format( date, '%b' ) Is Null THEN 0 ELSE date_format( date, '%b' ) END) as month, SUM( COALESCE( total, 0 ) ) AS purchases FROM purchases WHERE date >= date_sub( now( ) , INTERVAL 12 MONTH ) GROUP BY date_format( date, '%b' ) ORDER BY date_format( date, '%m' ) ASC";
    //     $q = $this->db->query($myQuery);
    //     if ($q->num_rows() > 0) {
    //         foreach (($q->result()) as $row) {
    //             $data[] = $row;
    //         }
    //         return $data;
    //     }
    //     return FALSE;
    // }

    public function getChartData()
    {
        $myQuery = "SELECT S.month,
        COALESCE(S.sales, 0) as sales,
        COALESCE( P.purchases, 0 ) as purchases,
        COALESCE(S.tax1, 0) as tax1,
        COALESCE(S.tax2, 0) as tax2,
        COALESCE( P.ptax, 0 ) as ptax
        FROM (  SELECT  date_format(date, '%Y-%m') Month,
                SUM(total) Sales,
                SUM(product_tax) tax1,
                SUM(order_tax) tax2
                FROM " . $this->db->dbprefix('sales') . "
                WHERE date >= date_sub( now( ) , INTERVAL 12 MONTH )
                GROUP BY date_format(date, '%Y-%m')) S
            LEFT JOIN ( SELECT  date_format(date, '%Y-%m') Month,
                        SUM(product_tax) ptax,
                        SUM(order_tax) otax,
                        SUM(total) purchases
                        FROM " . $this->db->dbprefix('purchases') . "
                        GROUP BY date_format(date, '%Y-%m')) P
            ON S.Month = P.Month
            ORDER BY S.Month";
        $q = $this->db->query($myQuery);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getCosting($date, $warehouse_id = null, $year = null, $month = null)
    {
        $this->db->select('SUM( COALESCE( purchase_unit_cost, 0 ) * quantity ) AS cost, SUM( COALESCE( sale_unit_price, 0 ) * quantity ) AS sales, SUM( COALESCE( purchase_net_unit_cost, 0 ) * quantity ) AS net_cost, SUM( COALESCE( sale_net_unit_price, 0 ) * quantity ) AS net_sales', false);
        if ($date) {
            $this->db->where('costing.date', $date);
        } elseif ($month) {
            $this->load->helper('date');
            $last_day = days_in_month($month, $year);
            $this->db->where('costing.date >=', $year . '-' . $month . '-01 00:00:00');
            $this->db->where('costing.date <=', $year . '-' . $month . '-' . $last_day . ' 23:59:59');
        }

        if ($warehouse_id) {
            $this->db->join('sales', 'sales.id=costing.sale_id')
                ->where('sales.warehouse_id', $warehouse_id);
        }

        $q = $this->db->get('costing');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getCustomerOpenReturns($customer_id)
    {
        $this->db->from('returns')->where('customer_id', $customer_id);
        return $this->db->count_all_results();
    }

    public function getCustomerQuotes($customer_id)
    {
        $this->db->from('quotes')->where('customer_id', $customer_id);
        return $this->db->count_all_results();
    }

    public function getCustomerReturns($customer_id)
    {
        return $this->getCustomerSaleReturns($customer_id) + $this->getCustomerOpenReturns($customer_id);
    }

    public function getCustomerSaleReturns($customer_id)
    {
        $this->db->from('sales')->where('customer_id', $customer_id)->where('sale_status', 'returned');
        return $this->db->count_all_results();
    }

    public function getCustomerSales($customer_id)
    {
        $this->db->from('sales')->where('customer_id', $customer_id);
        return $this->db->count_all_results();
    }

    public function getDailyPurchases($year, $month, $warehouse_id = null)
    {
        $myQuery = "SELECT DATE_FORMAT( date,  '%e' ) AS date, SUM( COALESCE( product_tax, 0 ) ) AS tax1, SUM( COALESCE( order_tax, 0 ) ) AS tax2, SUM( COALESCE( grand_total, 0 ) ) AS total, SUM( COALESCE( total_discount, 0 ) ) AS discount, SUM( COALESCE( shipping, 0 ) ) AS shipping
            FROM " . $this->db->dbprefix('purchases') . ' WHERE ';
        if ($warehouse_id) {
            $myQuery .= " warehouse_id = {$warehouse_id} AND ";
        }
        $myQuery .= " DATE_FORMAT( date,  '%Y-%m' ) =  '{$year}-{$month}'
            GROUP BY DATE_FORMAT( date,  '%e' )";
        $q = $this->db->query($myQuery, false);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getDailySales($year, $month, $warehouse_id = null)
    {
        $myQuery = "SELECT DATE_FORMAT( date,  '%e' ) AS date, SUM( COALESCE( product_tax, 0 ) ) AS tax1, SUM( COALESCE( order_tax, 0 ) ) AS tax2, SUM( COALESCE( grand_total, 0 ) ) AS total, SUM( COALESCE( total_discount, 0 ) ) AS discount, SUM( COALESCE( shipping, 0 ) ) AS shipping
            FROM " . $this->db->dbprefix('sales') . ' WHERE ';
        if ($warehouse_id) {
            $myQuery .= " warehouse_id = {$warehouse_id} AND ";
        }
        $myQuery .= " DATE_FORMAT( date,  '%Y-%m' ) =  '{$year}-{$month}'
            GROUP BY DATE_FORMAT( date,  '%e' )";
        $q = $this->db->query($myQuery, false);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getExpenseCategories()
    {
        $q = $this->db->get('expense_categories');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getExpenses($date, $warehouse_id = null, $year = null, $month = null)
    {
        $sdate = $date . ' 00:00:00';
        $edate = $date . ' 23:59:59';
        $this->db->select('SUM( COALESCE( amount, 0 ) ) AS total', false);
        if ($date) {
            $this->db->where('date >=', $sdate)->where('date <=', $edate);
        } elseif ($month) {
            $this->load->helper('date');
            $last_day = days_in_month($month, $year);
            $this->db->where('date >=', $year . '-' . $month . '-01 00:00:00');
            $this->db->where('date <=', $year . '-' . $month . '-' . $last_day . ' 23:59:59');
        }

        if ($warehouse_id) {
            $this->db->where('warehouse_id', $warehouse_id);
        }

        $q = $this->db->get('expenses');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getMonthlyPurchases($year, $warehouse_id = null)
    {
        $myQuery = "SELECT DATE_FORMAT( date,  '%c' ) AS date, SUM( COALESCE( product_tax, 0 ) ) AS tax1, SUM( COALESCE( order_tax, 0 ) ) AS tax2, SUM( COALESCE( grand_total, 0 ) ) AS total, SUM( COALESCE( total_discount, 0 ) ) AS discount, SUM( COALESCE( shipping, 0 ) ) AS shipping
            FROM " . $this->db->dbprefix('purchases') . ' WHERE ';
        if ($warehouse_id) {
            $myQuery .= " warehouse_id = {$warehouse_id} AND ";
        }
        $myQuery .= " DATE_FORMAT( date,  '%Y' ) =  '{$year}'
            GROUP BY date_format( date, '%c' ) ORDER BY date_format( date, '%c' ) ASC";
        $q = $this->db->query($myQuery, false);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getMonthlySales($year, $warehouse_id = null)
    {
        $myQuery = "SELECT DATE_FORMAT( date,  '%c' ) AS date, SUM( COALESCE( product_tax, 0 ) ) AS tax1, SUM( COALESCE( order_tax, 0 ) ) AS tax2, SUM( COALESCE( grand_total, 0 ) ) AS total, SUM( COALESCE( total_discount, 0 ) ) AS discount, SUM( COALESCE( shipping, 0 ) ) AS shipping
            FROM " . $this->db->dbprefix('sales') . ' WHERE ';
        if ($warehouse_id) {
            $myQuery .= " warehouse_id = {$warehouse_id} AND ";
        }
        $myQuery .= " DATE_FORMAT( date,  '%Y' ) =  '{$year}'
            GROUP BY date_format( date, '%c' ) ORDER BY date_format( date, '%c' ) ASC";
        $q = $this->db->query($myQuery, false);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getOrderDiscount($date, $warehouse_id = null, $year = null, $month = null)
    {
        $sdate = $date . ' 00:00:00';
        $edate = $date . ' 23:59:59';
        $this->db->select('SUM( COALESCE( order_discount, 0 ) ) AS order_discount', false);
        if ($date) {
            $this->db->where('date >=', $sdate)->where('date <=', $edate);
        } elseif ($month) {
            $this->load->helper('date');
            $last_day = days_in_month($month, $year);
            $this->db->where('date >=', $year . '-' . $month . '-01 00:00:00');
            $this->db->where('date <=', $year . '-' . $month . '-' . $last_day . ' 23:59:59');
        }

        if ($warehouse_id) {
            $this->db->where('warehouse_id', $warehouse_id);
        }

        $q = $this->db->get('sales');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getPOSSetting()
    {
        $q = $this->db->get('pos_settings');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getProductNames($term, $limit = 5)
    {
        $this->db->where("type = 'standard' AND (item_code LIKE '%" . $term . "%' OR name LIKE '%" . $term . "%' OR code LIKE '%" . $term . "%' OR supplier1_part_no LIKE '%" . $term . "%' OR supplier2_part_no LIKE '%" . $term . "%' OR supplier3_part_no LIKE '%" . $term . "%' OR supplier4_part_no LIKE '%" . $term . "%' OR supplier5_part_no LIKE '%" . $term . "%' OR  concat(name, ' (', code, ')') LIKE '%" . $term . "%')");
        $this->db->limit($limit);
        $q = $this->db->get('products');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    //=== New Item Movement Report Starts ===//
    public function getAllProducts()
    {
        $data[0] = "-- Select Product --";
        $this->db->select('id, code, name')
            ->from('sma_products')
            ->order_by('id asc');
        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[$row->id] = $row->name . ' (' . $row->code . ')';
            }
        }
        return $data;
    }

    public function getAllWareHouses()
    {
        $data[0] = "-- As Company --";
        $this->db->select('id, code, name')
            ->from('sma_warehouses')
            ->where('goods_in_transit = 0')
            ->order_by('id asc');
        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[$row->id] = $row->name . ' (' . $row->code . ')';
            }
        }
        return $data;
    }

    public function get_daily_purchase($supplier_id, $from_date, $to_date){
        if ($from_date) {
            // Convert from 'd/m/Y' to 'Y-m-d'
            $from_date_formatted = DateTime::createFromFormat('d/m/Y', $from_date);
            if ($from_date_formatted) {
                $from_date = $from_date_formatted->format('Y-m-d');
                $start_date = date('Y-m-d', strtotime($from_date));
            } else {
                echo "Invalid date format for to_date.";
                exit;
            }
        }

        if ($to_date) {
            // Convert from 'd/m/Y' to 'Y-m-d'
            $to_date_formatted = DateTime::createFromFormat('d/m/Y', $to_date);
            if ($to_date_formatted) {
                $to_date = $to_date_formatted->format('Y-m-d');
                $end_date = date('Y-m-d', strtotime($to_date . ' +1 day'));
            } else {
                echo "Invalid date format for to_date.";
                exit;
            }
        }

        $this->db->select("
                    pr.item_code, pi.product_name, pi.avz_item_code, pi.bonus, pi.net_unit_cost, pi.quantity,
                    pi.unit_cost as purchase_price, pi.sale_price, pi.batchno, pi.item_discount, pi.item_tax,
                    p.id, p.date as inv_date, 
                    p.supplier, 
                    p.total as total_purchase,
                    p.total_discount,
                    p.total_net_purchase,
                    p.total_sale, 
                    'purchase' as type, 
                    s.sequence_code as supplier_code,
                    ", false);

        $this->db->from('sma_purchase_items pi');
        $this->db->join('sma_products pr', 'pr.id = pi.product_id', 'left');
        $this->db->join('sma_purchases p', 'p.id = pi.purchase_id', 'left');
        $this->db->join('sma_companies s', 's.id = p.supplier_id', 'left');

        if ($supplier_id) {
            $this->db->where('p.supplier_id', $supplier_id);
        }

        if ($from_date && !$to_date) {
            $this->db->where("p.date >=", date('Y-m-d', strtotime($start_date)));
        }
        
        if ($to_date && !$from_date) {
            $this->db->where("p.date <=", date('Y-m-d', strtotime($end_date)));
        }
        
        if ($to_date && $from_date) {
            $this->db->where("p.date BETWEEN '$start_date' AND '$end_date'");
        }
        $this->db->order_by('p.id desc');

        $query = $this->db->get();
        //echo $this->db->last_query();exit;

        $pr = [];
        if ($query->num_rows() > 0) {
            $rows = $query->result();
            
            foreach ($rows as $row) {
                $pr[] = $row;
            }
        }
        
        return $pr;
    }

    public function get_total_income($supplier_id, $from_date, $to_date){
        if ($from_date) {
            // Convert from 'd/m/Y' to 'Y-m-d'
            $from_date_formatted = DateTime::createFromFormat('d/m/Y', $from_date);
            if ($from_date_formatted) {
                $from_date = $from_date_formatted->format('Y-m-d');
                $start_date = date('Y-m-d', strtotime($from_date));
            } else {
                echo "Invalid date format for to_date.";
                exit;
            }
        }

        if ($to_date) {
            // Convert from 'd/m/Y' to 'Y-m-d'
            $to_date_formatted = DateTime::createFromFormat('d/m/Y', $to_date);
            if ($to_date_formatted) {
                $to_date = $to_date_formatted->format('Y-m-d');
                $end_date = date('Y-m-d', strtotime($to_date . ' +1 day'));
            } else {
                echo "Invalid date format for to_date.";
                exit;
            }
        }


        $this->db->select("
            p.id, p.date as inv_date, 
            p.supplier, 
            p.total as total_purchase,
            p.total_discount,
            p.total_net_purchase,
            p.total_sale,
            SUM(pi.bonus) as total_bonus,   
            'purchase' as type, 
            s.sequence_code as supplier_code,
            ", false);

        $this->db->from('sma_purchases p');
        $this->db->join('sma_purchase_items pi', 'pi.purchase_id = p.id', 'left');
        $this->db->join('sma_companies s', 's.id = p.supplier_id', 'left');

        if ($supplier_id) {
            $this->db->where('p.supplier_id', $supplier_id);
        }

        if ($from_date && !$to_date) {
            $this->db->where("p.date >=", date('Y-m-d', strtotime($start_date)));
        }
        
        if ($to_date && !$from_date) {
            $this->db->where("p.date <=", date('Y-m-d', strtotime($end_date)));
        }
        
        if ($to_date && $from_date) {
            $this->db->where("p.date BETWEEN '$start_date' AND '$end_date'");
        }

        $this->db->group_by(['p.id']);

        $query = $this->db->get();
        //echo $this->db->last_query();exit;

        $pr = [];
        if ($query->num_rows() > 0) {
            $rows = $query->result();
            
            foreach ($rows as $row) {
                $pr[] = $row;
            }
        }

        return $pr;
    }

    public function getSupplierStockData($supplier_id, $warehouse_id, $from_date, $to_date){
        $this->db->select("
            pr.price, 
            im.product_id,
            pr.name as product_name,
            ps.supplier_id as supplier_id, 
            ps.supplier as supplier,
            SUM(IFNULL(im.quantity, 0)) as total_quantity,
            pr.tax_rate, pr.type, pr.unit, pr.code as product_code", false);

        $this->db->from('sma_inventory_movements im');
        $this->db->join('sma_purchase_items pi', 'pi.avz_item_code = im.avz_item_code AND `pi`.`purchase_id` IS NOT NULL', 'left');
        $this->db->join('sma_purchases ps', 'ps.id = pi.purchase_id', 'left'); // To get supplier from original purchase

        $this->db->join('sma_products pr', 'pr.id = im.product_id', 'left');

        if ($warehouse_id) {
            $this->db->where('im.location_id', $warehouse_id);
        }

        if ($supplier_id) {
            // Use the supplier_id filter on both conditions
            $this->db->where('(ps.supplier_id = ' . $supplier_id . ')');
        }

        if ($from_date) {
            // Convert from 'd/m/Y' to 'Y-m-d'
            $from_date_formatted = DateTime::createFromFormat('d/m/Y', $from_date);
            if ($from_date_formatted) {
                $from_date = $from_date_formatted->format('Y-m-d');
                $start_date = date('Y-m-d', strtotime($from_date));
            } else {
                echo "Invalid date format for to_date.";
                exit;
            }
        }

        if ($to_date) {
            // Convert from 'd/m/Y' to 'Y-m-d'
            $to_date_formatted = DateTime::createFromFormat('d/m/Y', $to_date);
            if ($to_date_formatted) {
                $to_date = $to_date_formatted->format('Y-m-d');
                $end_date = date('Y-m-d', strtotime($to_date . ' +1 day'));
            } else {
                echo "Invalid date format for to_date.";
                exit;
            }
        }
        
        if ($from_date && !$to_date) {
            $this->db->where("im.movement_date >=", date('Y-m-d', strtotime($start_date)));
        }
        
        if ($to_date && !$from_date) {
            $this->db->where("im.movement_date <=", date('Y-m-d', strtotime($end_date)));
        }
        
        if ($to_date && $from_date) {
            $this->db->where("im.movement_date BETWEEN '$start_date' AND '$end_date'");
        }

        $this->db->group_by(['im.product_id']);
        $this->db->having('total_quantity !=', 0);

        $query = $this->db->get();
        //echo $this->db->last_query();exit;

        $pr = [];
        if ($query->num_rows() > 0) {
            $rows = $query->result();
            
            foreach ($rows as $row) {
                $pr[] = $row;
            }
        }

        return $pr;
    }

    public function getPharmacyStockData($item = null)
    {
        $totalPurchases = [];
        $finalResponse = [];

        //if ($at_date) $at_date = $this->sma->fld($at_date);

        $totalPurchasesQuery = "SELECT 
                                    p.id, 
                                    p.code item_code, 
                                    p.name name, 
                                    w.name as warehouse_name,
                                    pi.batchno batch_no, 
                                    pi.expiry expiry, 
                                    round(sum(pi.quantity_balance)) quantity,
                                    round(avg(pi.sale_price), 2) sale_price,
                                    round(avg(pi.net_unit_cost), 2) cost_price,
                                    round(sum(pi.net_unit_cost * pi.quantity), 2) total_cost_price,
                                    round(avg(pi.unit_cost), 2) purchase_price
                                FROM sma_products p
                                INNER JOIN sma_purchase_items pi ON p.id = pi.product_id
                                LEFT JOIN sma_purchases pc ON pc.id = pi.purchase_id
                                INNER JOIN sma_warehouses w ON w.id = pi.warehouse_id
                                WHERE pi.purchase_item_id IS NULL AND (pc.status = 'received' OR pi.purchase_id IS NULL)";

        if ($item) {
            //$totalPurchasesQuery .= "AND (p.code = '{$item}' OR p.name LIKE '%{$item}%') ";
            $totalPurchasesQuery .= "AND (p.id = '{$item}') ";
        }

        $totalPurchasesQuery .= "GROUP BY p.code, p.name, pi.batchno, pi.warehouse_id
                                ORDER BY p.id ASC, w.id ASC";

        $totalPurchseResultSet = $this->db->query($totalPurchasesQuery);

        if ($totalPurchseResultSet->num_rows() > 0) {
            foreach ($totalPurchseResultSet->result() as $row) {
                $row->cost_price = ($row->total_cost_price / $row->quantity);
                $totalPurchases[] = $row;
            }

        }

        return $totalPurchases;
    }

    public function getStockDataTotals($at_date, $warehouse, $item_group, $type, $item){
        $stockArray = [];
        if ($at_date) {
            $at_date = $this->sma->fld($at_date);
        }

        $stockQuery = " SELECT p.id,
            p.code item_code, 
            p.name as name, 
            inv.avz_item_code,
            inv.batch_number as batch_no,
            inv.expiry_date as expiry,
            SUM(inv.quantity) as quantity,
            inv.net_unit_sale as sale_price,
            inv.net_unit_cost as cost_price,
            sum(inv.net_unit_cost * inv.quantity) as total_cost_price,
            inv.real_unit_cost as purchase_price  
            FROM `sma_inventory_movements` inv 
            INNER JOIN sma_products p on p.id=inv.product_id";
        if ($at_date) {
            $stockQuery .= " AND date(inv.movement_date)<= '{$at_date}' ";
        }
        if ($warehouse) {
            $stockQuery .= " AND inv.location_id = {$warehouse} ";
        }
        
        if ($item_group) {
            $stockQuery .= " AND p.category_id = '$item_group' ";
        }
        if ($item) {
            $stockQuery .= " AND inv.product_id = '{$item}' ";
        }
        if ($type) {
            $stockQuery .= " AND inv.type = '{$type}' ";
        }

        $stockQuery .= " GROUP BY inv.product_id, inv.avz_item_code HAVING quantity != 0";
        $stockResults = $this->db->query($stockQuery);
        //echo $this->db->last_query(); exit; 
        if ($stockResults->num_rows() > 0) {
            foreach ($stockResults->result() as $row) {
                $stockArray[] = $row;
            }
        }
        return $stockArray;
    }

    public function getStockDataGrandTotals($at_date, $warehouse, $item_group, $type, $item){
        $stockArray = [];
        if ($at_date) {
            $at_date = $this->sma->fld($at_date);
        }

        $stockQuery = " SELECT p.id,
            
            SUM(inv.quantity) as quantity,
            SUM(inv.net_unit_sale * inv.quantity) as total_sale_price,
            sum(inv.net_unit_cost * inv.quantity) as total_cost_price,
            SUM(inv.real_unit_cost * inv.quantity) as purchase_price  
            FROM sma_inventory_movements inv 
            INNER JOIN sma_products p on p.id=inv.product_id";
        if ($at_date) {
            $stockQuery .= " AND date(inv.movement_date)<= '{$at_date}' ";
        }
        if ($warehouse) {
            $stockQuery .= " AND inv.location_id = {$warehouse} ";
        }
        
        if ($item_group) {
            $stockQuery .= " AND p.category_id = '$item_group' ";
        }
        if ($item) {
            $stockQuery .= " AND inv.product_id = '{$item}' ";
        }
        if ($type) {
            $stockQuery .= " AND inv.type = '{$type}' ";
        }
          $stockResults = $this->db->query($stockQuery);
       // echo $this->db->last_query(); exit; 
        if ($stockResults->num_rows() > 0) {
            foreach ($stockResults->result() as $row) {
                $stockArray[] = $row;
            }
        }
        return $stockArray;
    }

    public function getStockData($at_date, $warehouse, $item_group, $type, $item, $page = '', $per_page = '')
    {
        
        $stockArray = [];
        if ($at_date) {
            $at_date = $this->sma->fld($at_date);
        }

        if($page != ''){
            $offset = $page;
        }

        $stockQuery = " SELECT p.id,
            p.code item_code, 
            p.name as name, 
            inv.avz_item_code,
            inv.batch_number as batch_no,
            inv.expiry_date as expiry,
            SUM(inv.quantity) as quantity,
            inv.net_unit_sale as sale_price,
            inv.net_unit_cost as cost_price,
            sum(inv.net_unit_cost * inv.quantity) as total_cost_price,
            inv.real_unit_cost as purchase_price  
            FROM `sma_inventory_movements` inv 
            INNER JOIN sma_products p on p.id=inv.product_id";
        if ($at_date) {
            $stockQuery .= " AND date(inv.movement_date)<= '{$at_date}' ";
        }
        if ($warehouse) {
            $stockQuery .= " AND inv.location_id = {$warehouse} ";
        }
        
        if ($item_group) {
            $stockQuery .= " AND p.category_id = '$item_group' ";
        }
        if ($item) {
            $stockQuery .= " AND inv.product_id = '{$item}' ";
        }
        if ($type) {
            $stockQuery .= " AND inv.type = '{$type}' ";
        }

        $stockQuery .= " GROUP BY inv.product_id, inv.avz_item_code HAVING quantity != 0 ORDER BY p.id DESC";
        
        if($page != ''){
            $stockQuery .= " LIMIT {$per_page} OFFSET {$offset}";
        }
        $stockResults = $this->db->query($stockQuery);
        // echo $this->db->last_query(); exit; 
        if ($stockResults->num_rows() > 0) {
            foreach ($stockResults->result() as $row) {
                $stockArray[] = $row;
            }
        }
        return $stockArray;
    }


    public function getStockData_BK($at_date, $warehouse, $supplier, $item_group, $item)
    {
        $totalPurchases = [];
        $finalResponse = [];

        if ($at_date)
            $at_date = $this->sma->fld($at_date);

        if ($supplier)
            $supplierJoin = " INNER JOIN sma_purchases pc ON pc.id = pi.purchase_id ";

        $totalPurchasesQuery = "SELECT 
                                    p.id, 
                                    p.code item_code, 
                                    p.name name, 
                                    pi.batchno batch_no, 
                                    pi.expiry expiry, 
                                    round(sum(pi.quantity)) quantity,
                                    round(avg(pi.sale_price), 2) sale_price,
                                    round(avg(pi.net_unit_cost), 2) cost_price,
                                    round(sum(pi.net_unit_cost * pi.quantity), 2) total_cost_price,
                                    round(avg(pi.unit_cost), 2) purchase_price
                                FROM sma_products p
                                INNER JOIN sma_purchase_items pi ON p.id = pi.product_id
                                INNER JOIN sma_purchases pc ON pc.id = pi.purchase_id
                                WHERE pi.purchase_item_id IS NULL AND pc.status = 'received'";
        if ($at_date) {
            $totalPurchasesQuery .= "AND pi.date <= '{$at_date}' ";
        }

        if ($warehouse) {
            $totalPurchasesQuery .= "AND pi.warehouse_id = {$warehouse} ";
        }

        if ($supplier) { //TODO: will be checked
            $totalPurchasesQuery .= "AND pc.supplier_id = {$supplier} ";
        }

        if ($item_group) {
            $totalPurchasesQuery .= "AND p.category_id = '$item_group' ";
        }

        if ($item) {
            //$totalPurchasesQuery .= "AND (p.code = '{$item}' OR p.name LIKE '%{$item}%') ";
            $totalPurchasesQuery .= "AND (p.id = '{$item}') ";
        }

        $totalPurchasesQuery .= "GROUP BY p.code, p.name, pi.batchno
                                ORDER BY p.id DESC";

        $totalPurchseResultSet = $this->db->query($totalPurchasesQuery);
        //echo  $this->db->last_query(); exit; 

        if ($totalPurchseResultSet->num_rows() > 0) {
            foreach ($totalPurchseResultSet->result() as $row) {
                $row->cost_price = ($row->total_cost_price / $row->quantity);
                $totalPurchases[] = $row;
            }

            //TODO sub sales from $totalPurchases
            $totalSalesQuery = "SELECT
                                    p.id,
                                    p.code item_code,
                                    p.name,
                                    si.batch_no batch_no,
                                    si.expiry expiry,
                                    round(sum(si.quantity)) quantity
                                FROM sma_products p 
                                INNER JOIN sma_sale_items si ON p.id = si.product_id
                                INNER JOIN sma_sales sl ON sl.id = si.sale_id 
                                WHERE sl.sale_status = 'completed' ";
            if ($at_date) {
                $totalSalesQuery .= "AND sl.date <= '{$at_date} 23:59:59' ";
            }

            if ($warehouse) {
                $totalSalesQuery .= "AND si.warehouse_id = {$warehouse} ";
            }

            if ($item_group) {
                $totalSalesQuery .= "AND p.category_id = '$item_group' ";
            }

            if ($item) {
                $totalSalesQuery .= "AND (p.id = '{$item}') ";
            }

            $totalSalesQuery .= "GROUP BY p.id, p.code, p.name, si.batch_no";

            $totalSalesResultSet = $this->db->query($totalSalesQuery);
            if ($totalSalesResultSet->num_rows() > 0) {
                foreach ($totalSalesResultSet->result() as $sale) {
                    array_map(function ($purchase) use ($sale) {
                        if (
                            $purchase->id == $sale->id
                            && $purchase->item_code == $sale->item_code
                            && $purchase->batch_no == $sale->batch_no
                            //&& $purchase->expiry == $sale->expiry
                        ) {
                            $purchase->quantity -= (int) $sale->quantity;
                        }
                    }, $totalPurchases);
                }
            }

            //TODO sub return supplier from $totalPurchases
            $totalReturnSupplerQuery = "SELECT
                                        p.id,
                                        p.code item_code,
                                        p.name,
                                        pi.batch_no,
                                        pi.expiry expiry,
                                        round(sum(pi.quantity)) quantity,
                                        round(avg(pi.net_unit_price), 2) cost_price
                                FROM sma_products p
                                INNER JOIN sma_return_supplier_items pi ON p.id = pi.product_id
                                INNER JOIN sma_returns_supplier rt ON pi.return_id = rt.id ";
            if ($at_date) {
                $totalReturnSupplerQuery .= "WHERE rt.date <= '{$at_date} 23:59:59' ";
            }

            if ($warehouse) {
                $totalReturnSupplerQuery .= "AND pi.warehouse_id = {$warehouse} ";
            }

            if ($item_group) {
                $totalReturnSupplerQuery .= "AND p.category_id = '$item_group' ";
            }

            if ($item) {
                $totalReturnSupplerQuery .= "AND (p.id = '{$item}') ";
            }

            $totalReturnSupplerQuery .= "GROUP BY p.id, p.code, p.name, pi.batch_no";

            $totalReturnSupplierResultSet = $this->db->query($totalReturnSupplerQuery);
            if ($totalReturnSupplierResultSet->num_rows() > 0) {
                foreach ($totalReturnSupplierResultSet->result() as $returnSupplier) {
                    array_map(function ($purchase) use ($returnSupplier) {
                        if (
                            $purchase->id == $returnSupplier->id
                            && $purchase->item_code == $returnSupplier->item_code
                            && $purchase->batch_no == $returnSupplier->batch_no
                            //&& $purchase->expiry == $returnSupplier->expiry
                        ) {
                            $purchase->quantity -= (int) abs($returnSupplier->quantity);
                            //$purchase->cost_price = ($purchase->cost_price + $returnSupplier->cost_price)/2;
                        }
                    }, $totalPurchases);
                }
            }

            //TODO add return customer to $totalPurchases.
            $totalReturnCustomerQuery = "select
                                            p.id,
                                            p.code item_code,
                                            p.name,
                                            rci.batch_no batch_no,
                                            rci.expiry expiry,
                                            round(sum(rci.quantity)) quantity
                                        from sma_products p
                                        inner join sma_return_items rci ON p.id = rci.product_id 
                                        INNER JOIN sma_returns rt ON rci.return_id = rt.id ";

            if ($at_date) {
                $totalReturnCustomerQuery .= "WHERE rt.date <= '{$at_date} 23:59:59' ";
            }

            if ($warehouse) {
                $totalReturnCustomerQuery .= "AND rci.warehouse_id = {$warehouse} ";
            }

            if ($item_group) {
                $totalReturnSupplerQuery .= "AND p.category_id = '$item_group' ";
            }

            if ($item) {
                $totalReturnSupplerQuery .= "AND (p.id = '{$item}') ";
            }

            $totalReturnCustomerQuery .= "group by p.id, p.code, p.name, rci.batch_no";

            $totalReturnCustomerResultSet = $this->db->query($totalReturnCustomerQuery);
            if ($totalReturnCustomerResultSet->num_rows() > 0) {
                foreach ($totalReturnCustomerResultSet->result() as $returnCustomer) {
                    array_map(function ($purchase) use ($returnCustomer) {
                        if (
                            $purchase->id == $returnCustomer->id
                            && $purchase->item_code == $returnCustomer->item_code
                            && $purchase->batch_no == $returnCustomer->batch_no
                            //&& $purchase->expiry == $returnCustomer->expiry
                        ) {
                            $purchase->quantity += (int) abs($returnCustomer->quantity);
                        }
                    }, $totalPurchases);
                }
            }

            //TODO transfer To warehouse
            $totalTransferQuery = "SELECT
                                        p.id,
                                        p.code item_code,
                                        p.name,
                                        pi.batchno batch_no,
                                        pi.expiry expiry,
                                        pi.warehouse_id,
                                        round(sum(pi.quantity)) quantity,
                                        t.from_warehouse_id,
                                        t.to_warehouse_id
                                FROM sma_products p
                                INNER JOIN sma_purchase_items pi ON p.id = pi.product_id
                                INNER JOIN sma_transfers t ON pi.transfer_id = t.id
                                WHERE pi.transfer_id IS NOT NULL ";
            if ($at_date) {
                $totalTransferQuery .= "AND pi.date <= '{$at_date}' ";
            }

            if ($warehouse) {
                $totalTransferQuery .= "AND pi.warehouse_id = {$warehouse} ";
            }

            if ($item_group) {
                $totalTransferQuery .= "AND p.category_id = '$item_group' ";
            }

            if ($item) {
                $totalTransferQuery .= "AND (p.id = '{$item}') ";
            }

            $totalTransferQuery .= "GROUP BY p.id, p.code, p.name, pi.batchno";
            $totalTransferResultSet = $this->db->query($totalTransferQuery);
            if ($totalTransferResultSet->num_rows() > 0) {
                foreach ($totalTransferResultSet->result() as $transfer) {
                    array_map(function ($purchase) use ($transfer, $warehouse) {
                        if (
                            $purchase->id == $transfer->id
                            && $purchase->item_code == $transfer->item_code
                            && $purchase->batch_no == $transfer->batch_no
                            && $warehouse == $transfer->to_warehouse_id
                            //&& $purchase->expiry == $transfer->expiry
                        ) {
                            $purchase->quantity = $purchase->quantity + (int) abs($transfer->quantity);
                        } else if (
                            $purchase->id == $transfer->id
                            && $purchase->item_code == $transfer->item_code
                            && $purchase->batch_no == $transfer->batch_no
                            && $warehouse == $transfer->from_warehouse_id
                        ) {
                            $purchase->quantity = $purchase->quantity - (int) abs($transfer->quantity);
                        }
                    }, $totalPurchases);
                }
            }

            //TODO transfer FROm warehouse
            $totalTransferQuery = "SELECT
                                        p.id,
                                        p.code item_code,
                                        p.name,
                                        pi.batchno batch_no,
                                        pi.expiry expiry,
                                        pi.warehouse_id,
                                        round(sum(pi.quantity)) quantity,
                                        t.from_warehouse_id,
                                        t.to_warehouse_id
                                FROM sma_products p
                                INNER JOIN sma_purchase_items pi ON p.id = pi.product_id
                                INNER JOIN sma_transfers t ON pi.transfer_id = t.id
                                WHERE pi.transfer_id IS NOT NULL ";
            if ($at_date) {
                $totalTransferQuery .= "AND pi.date <= '{$at_date}' ";
            }

            if ($warehouse) {
                $totalTransferQuery .= "AND t.from_warehouse_id = {$warehouse} ";
            }

            if ($item_group) {
                $totalTransferQuery .= "AND p.category_id = '$item_group' ";
            }

            if ($item) {
                $totalTransferQuery .= "AND (p.id = '{$item}') ";
            }

            $totalTransferQuery .= "GROUP BY p.id, p.code, p.name, pi.batchno";
            $totalTransferResultSet = $this->db->query($totalTransferQuery);
            if ($totalTransferResultSet->num_rows() > 0) {
                foreach ($totalTransferResultSet->result() as $transfer) {
                    array_map(function ($purchase) use ($transfer, $warehouse) {
                        if (
                            $purchase->id == $transfer->id
                            && $purchase->item_code == $transfer->item_code
                            && $purchase->batch_no == $transfer->batch_no
                            && $warehouse == $transfer->to_warehouse_id
                            //&& $purchase->expiry == $transfer->expiry
                        ) {
                            $purchase->quantity = $purchase->quantity + (int) abs($transfer->quantity);
                        } else if (
                            $purchase->id == $transfer->id
                            && $purchase->item_code == $transfer->item_code
                            && $purchase->batch_no == $transfer->batch_no
                            && $warehouse == $transfer->from_warehouse_id
                        ) {
                            $purchase->quantity = $purchase->quantity - (int) abs($transfer->quantity);
                        }
                    }, $totalPurchases);
                }
            }
        } else {
            $totalPurchases = [];

            $totalTransferQuery = "SELECT
                                        p.id,
                                        p.code item_code,
                                        p.name,
                                        pi.batchno batch_no,
                                        pi.expiry expiry,
                                        pi.warehouse_id,
                                        round(sum(pi.quantity)) quantity,
                                        round(avg(pi.sale_price), 2) sale_price,
                                        round(avg(pi.net_unit_cost), 2) cost_price,
                                        round(avg(pi.unit_cost), 2) purchase_price,
                                        t.from_warehouse_id,
                                        t.to_warehouse_id
                                FROM sma_products p
                                INNER JOIN sma_purchase_items pi ON p.id = pi.product_id
                                INNER JOIN sma_transfers t ON pi.transfer_id = t.id
                                WHERE pi.transfer_id IS NOT NULL ";
            if ($at_date) {
                $totalTransferQuery .= "AND pi.date <= '{$at_date}' ";
            }

            if ($warehouse) {
                $totalTransferQuery .= "AND pi.warehouse_id = {$warehouse} ";
            }

            if ($item_group) {
                $totalTransferQuery .= "AND p.category_id = '$item_group' ";
            }

            if ($item) {
                $totalTransferQuery .= "AND (p.id = '{$item}') ";
            }

            $totalTransferQuery .= "GROUP BY p.id, p.code, p.name, pi.batchno";
            $totalTransferResultSet = $this->db->query($totalTransferQuery);
            if ($totalTransferResultSet->num_rows() > 0) {

                foreach ($totalTransferResultSet->result() as $row) {
                    $totalPurchases[] = $row;
                }
            }

        }

        return $totalPurchases;
    }

    public function getItemOpeningBalance($productId, $start_date, $warehouseId = 0)
    {
        $reports_start_date = '2024-07-07';

        // Use the query builder to safely escape and build the query
        $this->db->select('
            SUM(IF(movement_date < "' . $start_date . '", quantity, 0)) AS total_opening_qty,
            ABS(SUM(IF(movement_date < "' . $start_date . '", net_unit_cost, 0)) / NULLIF(SUM(IF(movement_date < "' . $start_date . '", quantity, 0)), 0)) AS cost_price, 
            SUM(IF(movement_date < "' . $start_date . '", net_unit_cost * quantity, 0)) AS total_opening_value', FALSE);
        $this->db->from('sma_inventory_movements');
        $this->db->where('product_id', $productId);
        if ($warehouseId) {
            $this->db->where('location_id', $warehouseId);
        }
        $this->db->where('movement_date > ', $reports_start_date);

        $query = $this->db->get();
        //echo $this->db->last_query();exit;
        $response = array();
        if ($query->num_rows() > 0) {
            $response = $query->row_array();
        }

        return $response;
    }

    public function getItemMovementRecords($productId, $start_date, $end_date, $warehouseId, $filterOnType, $document_number)
    {
        $reports_start_date = '2024-07-07';

        $query = "SELECT 
                    CASE 
                        WHEN iv.trs_type = 'pos' THEN 'pharmacy sale'
                        ELSE iv.trs_type
                    END AS trs_type,
                    iv.movement_date,
                    iv.quantity,
                    iv.location_id,
                    iv.batch_number as batch_no,
                    iv.expiry_date as expiry,
                    iv.reference_id,
                    iv.net_unit_cost,
                    iv.net_unit_sale,
                    iv.real_unit_cost,
                    iv.real_unit_sale,
                    iv.avz_item_code,
                    CASE 
                        WHEN iv.trs_type = 'purchase' THEN sp.reference_no
                        WHEN iv.trs_type = 'sale' THEN ss.reference_no
                        WHEN iv.trs_type = 'pos' THEN ps.reference_no
                        WHEN iv.trs_type = 'transfer_out' THEN sto.transfer_no
                        WHEN iv.trs_type = 'transfer_in' THEN sti.transfer_no
                        ELSE NULL
                    END AS reference_number,
                    CASE 
                        WHEN iv.trs_type = 'purchase' THEN sp.supplier
                        WHEN iv.trs_type = 'sale' THEN ss.customer
                        WHEN iv.trs_type = 'pos' THEN sw.name
                        WHEN iv.trs_type = 'transfer_out' THEN sto.from_warehouse_name
                        WHEN iv.trs_type = 'transfer_in' THEN sti.to_warehouse_name
                        ELSE NULL
                    END AS counterparty
                FROM 
                    (SELECT 
                        product_id,
                        type as trs_type,
                        movement_date,
                        quantity,
                        location_id,
                        batch_number,
                        expiry_date,
                        reference_id,
                        net_unit_cost,
                        net_unit_sale,
                        real_unit_cost,
                        real_unit_sale,
                        avz_item_code
                    FROM sma_inventory_movements
                    WHERE product_id = " . $productId . " AND ";

        if ($filterOnType) {
            $query .= "type = '" . $filterOnType . "' AND ";
        }

        if ($warehouseId) {
            $query .= "location_id = '" . $warehouseId . "' AND ";
        }

        $query .= "movement_date >= '" . $reports_start_date . "' AND 
                    movement_date BETWEEN '" . date('Y-m-d', strtotime($start_date)) . "' AND '" . date('Y-m-d', strtotime($end_date . ' +1 day')) . "') iv
                    LEFT JOIN sma_purchases sp ON iv.reference_id = sp.id AND iv.trs_type = 'purchase'
                    LEFT JOIN sma_sales ss ON iv.reference_id = ss.id AND iv.trs_type = 'sale'
                    LEFT JOIN sma_sales ps ON iv.reference_id = ps.id AND iv.trs_type = 'pos'
                    LEFT JOIN sma_warehouses sw ON ps.warehouse_id = sw.id
                    LEFT JOIN sma_transfers sto ON iv.reference_id = sto.id AND iv.trs_type = 'transfer_out'
                    LEFT JOIN sma_transfers sti ON iv.reference_id = sti.id AND iv.trs_type = 'transfer_in'";

        if ($document_number) {
            $query .= " WHERE sp.reference_no like '%" . $document_number . "%' 
                        OR ss.reference_no like '%" . $document_number . "%' 
                        OR ps.reference_no like '%" . $document_number . "%'
                        OR sto.transfer_no like '%" . $document_number . "%' 
                        OR sti.transfer_no like '%" . $document_number . "%' ";
        }


        $q = $this->db->query($query);
        $response = array();
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $response[] = $row;
            }
        }
        return $response;
    }

    /*public function getItemOpeningBalance($productId, $start_date, $warehouseId = 0)
    {

        $q = $this->db->query("SELECT
        COALESCE(purchaseQuantity, 0) - COALESCE(saleQuantity, 0) - COALESCE(returnSupplierQuantity, 0) + COALESCE(returnQuantity, 0) + COALESCE(transferInQuantity, 0) - COALESCE(transferOutQuantity, 0) AS openingBalance,
        COALESCE(totalPurchases, 0) + COALESCE(totalCustomerReturns, 0) - COALESCE(totalSales, 0) - COALESCE(totalSupplierReturns, 0) AS totalAmtBalance,
        COALESCE(purchaseUnitPrice, 0) AS unitPrice
        FROM
        ( SELECT SUM(saleItem.quantity) AS saleQuantity, SUM(saleItem.quantity * saleItem.net_cost) AS totalSales FROM `sma_sales` AS `sale` 
            INNER JOIN `sma_sale_items` AS `saleItem` ON `saleItem`.`sale_id` = `sale`.`id`
            WHERE  `saleItem`.`product_id` = $productId AND DATE(sale.date) < '$start_date' AND `sale`.`sale_status` = 'completed' AND `saleItem`.`batch_no` != '' ) AS sales, 
        ( SELECT SUM(purItem.quantity) AS purchaseQuantity, SUM(purItem.quantity * purItem.net_unit_cost) AS totalPurchases FROM `sma_purchases` AS `purchase` 
          INNER JOIN `sma_purchase_items` AS `purItem` ON `purItem`.`purchase_id`=`purchase`.`id` 
          WHERE `purItem`.`product_id`=$productId AND DATE(purchase.date) < '$start_date' AND `purchase`.`status` = 'received' AND `purItem`.`purchase_item_id` IS NULL ) AS purchases,
        ( SELECT SUM(abs(purItem.quantity)) AS returnSupplierQuantity, SUM(purItem.quantity * purItem.net_cost) AS totalSupplierReturns FROM `sma_returns_supplier` AS `purchase`
            INNER JOIN `sma_return_supplier_items` AS `purItem` ON  `purItem`.`return_id` = `purchase`.`id`
            WHERE `purItem`.`product_id` = $productId AND DATE(purchase.date) < '$start_date' ) AS returnSupplier, 
        ( SELECT SUM(rtnItem.quantity) AS returnQuantity, SUM(rtnItem.quantity * rtnItem.net_cost) AS totalCustomerReturns FROM `sma_returns` AS `rtn` 
           INNER JOIN `sma_return_items` AS `rtnItem` ON `rtnItem`.`return_id`=`rtn`.`id` 
           WHERE `rtnItem`.`product_id`=$productId AND DATE(rtn.date) < '$start_date' ) AS returns, 
        ( SELECT trnf.id, IFNULL(SUM(titm.quantity), 0) + IFNULL(SUM(pitm.quantity), 0) AS transferInQuantity FROM `sma_transfers` AS `trnf` 
          LEFT JOIN( SELECT transfer_id, SUM(quantity) AS quantity FROM sma_transfer_items WHERE `product_id`=$productId AND DATE(`date`) < '$start_date' AND warehouse_id=$warehouseId GROUP BY transfer_id ) AS titm ON titm.transfer_id=trnf.id 
          LEFT JOIN( SELECT transfer_id, SUM(quantity) AS quantity FROM sma_purchase_items WHERE `product_id`=$productId AND DATE(`date`) < '$start_date' AND transfer_id IS NOT NULL GROUP BY warehouse_id ) AS pitm ON pitm.transfer_id=trnf.id 
          WHERE DATE(`trnf`.`date`) < '$start_date' AND `trnf`.`to_warehouse_id`=$warehouseId ) AS tranferIn, 
        ( SELECT trnf.id, IFNULL(SUM(titm.quantity), 0) + IFNULL(SUM(pitm.quantity), 0) AS transferOutQuantity FROM `sma_transfers` AS `trnf` 
          LEFT JOIN( SELECT transfer_id, SUM(quantity) AS quantity FROM sma_transfer_items WHERE `product_id`=$productId AND DATE(`date`) < '$start_date' GROUP BY transfer_id ) AS titm ON titm.transfer_id=trnf.id 
          LEFT JOIN( SELECT warehouse_id, SUM(abs(quantity)) AS quantity FROM sma_purchase_items WHERE `product_id`=$productId AND DATE(`date`) < '$start_date' AND transfer_id IS NULL AND purchase_id IS NULL AND quantity < 0 GROUP BY warehouse_id ) AS pitm ON pitm.warehouse_id=trnf.from_warehouse_id 
          WHERE DATE(`trnf`.`date`) < '$start_date' AND `trnf`.`from_warehouse_id`=$warehouseId AND trnf.id IN( SELECT DISTINCT transfer_id FROM sma_transfer_items WHERE `product_id`=$productId ) AND trnf.from_warehouse_id IN( SELECT DISTINCT warehouse_id FROM sma_purchase_items WHERE `product_id`=$productId AND DATE(`date`) < '$start_date' AND transfer_id IS NULL AND purchase_id IS NULL AND quantity < 0 GROUP BY warehouse_id ) ) AS transferOut, 
        (   SELECT IFNULL(purItemA.net_unit_cost, p.cost) AS purchaseUnitPrice  FROM sma_products AS p
            LEFT JOIN ( SELECT AVG(purItem.net_unit_cost) AS net_unit_cost, purItem.product_id FROM `sma_purchases` AS `purchase`
            INNER JOIN `sma_purchase_items` AS `purItem` ON `purItem`.`purchase_id`=`purchase`.`id` WHERE `purItem`.`product_id`=$productId AND DATE(purchase.date) < '$start_date' AND `purchase`.`invoice_number` IS NOT NULL AND `purchase`.`grand_total`> 0 GROUP BY  purItem.product_id ) as purItemA ON purItemA.product_id = p.id WHERE p.id = $productId
         ) AS purchaseUnitPrice;");
        // echo $this->db->last_query();
        $response = array();
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $row->unitPrice = ($row->totalAmtBalance / $row->openingBalance);
                $response = $row;
            }
        }
        return $response;
    }*/

    public function getInventoryItemMovementRecords($productId, $filterOnType)
    {
        $warehouse = $this->input->post('warehouse') ? $this->input->post('warehouse') : null;
        $start_date = $this->input->post('start_date') ? $this->input->post('start_date') : null;
        $end_date = $this->input->post('end_date') ? $this->input->post('end_date') : null;
        if ($start_date) {
            $start_date = $this->sma->fld($start_date);
        }
        if ($end_date) {
            $end_date = $this->sma->fld($end_date);
        }
        $where = '';
        if ($filterOnType != '') {

            if ($filterOnType == 'adjustment') {
                $where .= " AND (a.type = 'adjustment_increase' OR a.type ='adjustment_decrease') ";
            } else {
                $where .= " AND a.type = '" . $filterOnType . "'";
            }
        }
        if (!empty($warehouse)) {
            $where .= " AND a.location_id = '" . $warehouse . "'";
        }
        if (!empty($start_date) and !empty($end_date)) {
            $where .= ' AND DATE(a.movement_date) BETWEEN "' . $start_date . '" and "' . $end_date . '"';
        }
        $response = array();
        if ($productId > 0) {

            $q = $this->db->query(
                "SELECT a.batch_number, a.movement_date,a.type,a.quantity, b.name as product_name, b.code , b.item_code, c.name as warehouse_name
                                FROM `sma_inventory_movements` a 
                                LEFT JOIN sma_products b on a.product_id = b.id 
                                LEFT JOIN sma_warehouses c on a.location_id = c.id 
                                WHERE a.product_id = " . $productId . $where
            );

            if ($q->num_rows() > 0) {
                foreach (($q->result()) as $row) {
                    $response[] = $row;
                }
            }
        }
        return $response;
    }

    public function getInventoryItemMovementByPharmacy($productId, $warehouses)
    {
        $where = '';
        $response = array();
        if ($productId > 0) {
            $sumQuery = '';

            foreach ($warehouses as $warehouse) {
                $sumQuery .= 'SUM(CASE WHEN a.location_id = ' . $warehouse->id . ' THEN a.quantity ELSE 0 END) AS loc_' . $warehouse->id . ',';
            }
            $q = $this->db->query(
                "SELECT   p.name AS product_name,
                " . $sumQuery . "
                    SUM(a.quantity) as total_quantity
                FROM `sma_inventory_movements` a
                LEFT JOIN sma_products p ON a.product_id = p.id
                WHERE a.product_id = " . $productId . " GROUP BY p.name"
            );

            if ($q->num_rows() > 0) {
                foreach (($q->result()) as $row) {
                    $response[] = $row;
                }
            }
        }
        return $response;
    }

    public function getProductsQuantityUnitCost($start_date, $from_warehouse_id)
    {
        $qry = $this->db->query("SELECT
            product_id,
            SUM(totalPurchaseQuantity) AS total_in_quantity,
            SUM(purchaseUnitPrice) AS avg_unit_cost,
            SUM(totalSaleQuantity) AS total_out_quantity,
            SUM(saleUnitPrice) AS avgSaleUnitPrice
            FROM
            (
    
            SELECT
            purItem.product_id AS product_id,
            SUM(purItem.quantity) AS totalPurchaseQuantity,
            SUM(purItem.quantity * purItem.net_unit_cost) As purchaseUnitPrice,
            0 AS totalSaleQuantity,
            NULL AS saleUnitPrice
            FROM
            `sma_purchases` AS `purchase`
            INNER JOIN
            `sma_purchase_items` AS `purItem` ON `purItem`.`purchase_id` = `purchase`.`id`
            INNER JOIN
            sma_products AS p ON p.id = purItem.product_id
            WHERE
            DATE(purchase.date) < '$start_date'
            AND `purchase`.`grand_total` > 0
            AND `purchase`.`status` = 'received'
            GROUP BY
            purItem.product_id
    
            UNION ALL
    
            SELECT 
            rtnItem.product_id AS product_id,
            SUM(rtnItem.quantity) AS totalPurchaseQuantity,
            SUM(rtnItem.quantity * rtnItem.net_cost) As purchaseUnitPrice,
            0 AS totalSaleQuantity,
            NULL AS saleUnitPrice
    
            FROM 
            `sma_returns` AS `rtn` 
            INNER JOIN 
            `sma_return_items` AS `rtnItem` ON `rtnItem`.`return_id` = `rtn`.`id` 
            WHERE 
            DATE(rtn.date) < '$start_date'
            GROUP BY
            product_id
    
            UNION ALL 
    
            SELECT 
            titm.product_id AS product_id,
            IFNULL(SUM(titm.quantity), 0) + IFNULL(SUM(pitm.quantity), 0) AS totalPurchaseQuantity,
            NULL AS purchaseUnitPrice,
            0 AS totalSaleQuantity,
            NULL AS saleUnitPrice
    
            FROM 
            `sma_transfers` AS `trnf` 
            LEFT JOIN 
            (
            SELECT 
                product_id,
                transfer_id, 
                SUM(quantity) AS quantity 
            FROM 
                sma_transfer_items 
            WHERE 
                DATE(`date`) < '$start_date' 
            GROUP BY 
                transfer_id
            ) AS titm ON titm.transfer_id = trnf.id 
            LEFT JOIN 
            (
            SELECT 
                product_id,
                transfer_id, 
                SUM(quantity) AS quantity 
            FROM 
                sma_purchase_items 
            WHERE 
                DATE(`date`) < '$start_date' 
                AND transfer_id IS NOT NULL 
            GROUP BY 
                warehouse_id
            ) AS pitm ON pitm.transfer_id = trnf.id 
            WHERE 
            DATE(`trnf`.`date`) < '$start_date' 
            AND `trnf`.`to_warehouse_id` = $from_warehouse_id
            GROUP BY
            product_id
    
            UNION ALL
    
            SELECT
            saleItem.product_id AS product_id,
            0 AS totalPurchaseQuantity,
            NULL AS purchaseUnitPrice,
            SUM(saleItem.quantity) AS totalSaleQuantity,
            SUM(saleItem.quantity * saleItem.net_cost) As saleUnitPrice
            FROM
            `sma_sales` AS `sale`
            INNER JOIN
            `sma_sale_items` AS `saleItem` ON `saleItem`.`sale_id` = `sale`.`id`
            WHERE
            DATE(sale.date) < '$start_date'
            AND sale.sale_status = 'completed'
            GROUP BY
            saleItem.product_id
    
            UNION ALL
    
            SELECT
            purItem.product_id AS product_id,
            0 AS purchaseUnitPrice,
            NULL AS purchaseUnitPrice,
            SUM(ABS(purItem.quantity)) AS totalSaleQuantity,
            SUM(purItem.quantity * purItem.net_cost) As saleUnitPrice
            FROM
            `sma_returns_supplier` AS `purchase`
            INNER JOIN
            `sma_return_supplier_items` AS `purItem` ON `purItem`.`return_id` = `purchase`.`id`
            WHERE
            DATE(purchase.date) < '$start_date'
            GROUP BY
            purItem.product_id
    
            UNION ALL
    
            SELECT 
            product_id, 
            0 AS purchaseUnitPrice,
            NULL AS purchaseUnitPrice,
            SUM(quantity) AS totalSaleQuantity,
            NULL AS saleUnitPrice
            FROM
            (
                SELECT titm.product_id, titm.quantity
                FROM `sma_transfers` AS `trnf`
                LEFT JOIN
                (
                        SELECT product_id, transfer_id, SUM(quantity) AS quantity
                        FROM sma_transfer_items
                        WHERE DATE(`date`) < '$start_date'
                        GROUP BY product_id
                ) AS titm ON titm.transfer_id = trnf.id
                WHERE DATE(trnf.`date`) < '$start_date' AND titm.transfer_id IS NOT NULL
    
                UNION ALL
    
                SELECT pitm.product_id, pitm.qty
                FROM `sma_transfers` AS `trnf`
                LEFT JOIN
                (
                        SELECT pit.transfer_id, po.warehouse_id as warehouse_out, SUM(pit.quantity) AS qty, pit.product_id
                        FROM sma_purchase_items as pit
                        INNER JOIN sma_purchase_items as po ON po.product_id = pit.product_id AND po.warehouse_id = $from_warehouse_id AND po.quantity < 0
                        WHERE DATE(pit.`date`) < '$start_date' AND pit.transfer_id IS NOT NULL
                        GROUP BY pit.product_id
                ) AS pitm ON pitm.warehouse_out = trnf.from_warehouse_id AND pitm.transfer_id = trnf.id
                WHERE DATE(trnf.`date`) < '$start_date' AND pitm.transfer_id IS NOT NULL
            ) AS combined_product_ids
            GROUP BY product_id
    
    
            ) AS combinedData
            GROUP BY
            product_id");

        $resultSet = array();
        if ($qry->num_rows() > 0) {
            foreach (($qry->result()) as $row) {
                $resultSet[$row->product_id] = ["total_opening_qty" => $row->total_in_quantity - $row->total_out_quantity, "avg_unit_cost" => ($row->avg_unit_cost - $row->avgSaleUnitPrice) / ($row->total_in_quantity - $row->total_out_quantity), 'all_data' => $row];
            }
        }
        // echo $this->db->last_query();
        // echo '<pre>', print_r($resultSet), '</pre>';
        return $resultSet;
    }

    public function getInventoryTrialBalanceData($start_date, $end_date, $from_warehouse_id = 0, $to_warehouse_id = 0)
    {

        # Transfer-OUT
        // SUM(abs(PI.quantity)) AS movement_out_quantity,
        // AVG(PI.net_unit_cost) AS movement_out_cost

        # Transfer-IN
        // IFNULL(SUM(movement_in_quantity), 0) AS movement_in_quantity,
        // AVG(net_unit_cost) AS movement_in_cost


        $qry = $this->db->query("SELECT 
        prd.id AS product_id,
        prd.code AS product_code,
        prd.name AS product_name, 
        movement_out.movement_out_quantity,
        movement_out.movement_out_cost,
        movement_out.total_movement_out_cost,
        movement_in.movement_in_quantity,
        movement_in.movement_in_cost,
        movement_in.total_movement_in_cost
        
        FROM 
        sma_products AS prd
    
        LEFT JOIN (
        
        SELECT product_id, SUM(movement_out_quantity) AS movement_out_quantity, AVG(movement_out_cost) AS movement_out_cost, SUM(total_movement_out_cost) AS total_movement_out_cost
        
        FROM (
        
            SELECT product_id,
                    SUM(si.quantity) AS movement_out_quantity,
                    AVG(si.net_unit_price) AS movement_out_cost,
                    SUM(si.quantity * si.net_cost) AS total_movement_out_cost
                FROM
                    sma_sale_items si
                LEFT JOIN sma_sales AS s
                ON
                    s.id = si.sale_id
                WHERE
                    DATE(s.date) BETWEEN '$start_date' AND '$end_date' 
                    AND s.sale_status = 'completed'
                GROUP BY
                    si.product_id
        
                UNION ALL
        
                SELECT
                    ritems.product_id,
                    SUM(ritems.quantity) AS movement_out_quantity,
                    AVG(ritems.net_unit_price) AS movement_out_cost,
                    SUM(ritems.quantity * ritems.net_cost) AS total_movement_out_cost
                FROM
                    sma_return_supplier_items ritems
                LEFT JOIN sma_returns_supplier AS rt
                ON
                    rt.id = ritems.return_id
                WHERE
                    DATE(rt.date) BETWEEN '$start_date' AND '$end_date'
                GROUP BY
                    ritems.product_id
        
                UNION ALL 
        
                SELECT product_id, 0 AS movement_out_quantity, 
                0 AS movement_out_cost,
                0 AS total_movement_out_cost
                FROM
                (
                    SELECT trnItm.product_id, trnItm.quantity, trnItm.net_unit_cost
                    FROM `sma_transfers` AS `trnf`
                    LEFT JOIN
                    (
                        SELECT product_id, transfer_id, SUM(quantity) AS quantity, net_unit_cost
                        FROM sma_transfer_items
                        WHERE DATE(`date`) BETWEEN '$start_date' AND '$end_date'
                        GROUP BY product_id
                    ) AS trnItm ON trnItm.transfer_id = trnf.id
                    WHERE DATE(trnf.`date`) BETWEEN '$start_date' AND '$end_date' AND trnItm.transfer_id IS NOT NULL
        
                    UNION ALL
        
                    SELECT pitm.product_id, pitm.quantity, pitm.net_unit_cost
                    FROM `sma_transfers` AS `trnf`
                    LEFT JOIN
                    (
                        SELECT pit.transfer_id, po.warehouse_id as warehouse_out_id, SUM(pit.quantity) AS quantity, pit.product_id, pit.net_unit_cost
                        FROM sma_purchase_items as pit
                        INNER JOIN sma_purchase_items as po ON po.product_id = pit.product_id AND po.warehouse_id = $from_warehouse_id AND po.quantity < 0
                        WHERE DATE(pit.`date`) BETWEEN '$start_date' AND '$end_date' AND pit.transfer_id IS NOT NULL
                        GROUP BY pit.product_id
                    ) AS pitm ON pitm.warehouse_out_id = trnf.from_warehouse_id AND pitm.transfer_id = trnf.id
                    WHERE DATE(trnf.`date`) BETWEEN '$start_date' AND '$end_date' AND pitm.transfer_id IS NOT NULL
                ) AS combined_product_ids
                GROUP BY product_id
        
        
        
        ) as combined_sale_return_transfer_out GROUP BY product_id
        
        ) AS movement_out ON movement_out.product_id = prd.id
        
        LEFT JOIN(
        
        SELECT product_id, SUM(movement_in_quantity) AS movement_in_quantity, AVG(movement_in_cost) AS movement_in_cost, SUM(total_movement_in_cost) AS total_movement_in_cost
        
        FROM (
        
          SELECT
                    PI.product_id,
                    SUM(PI.quantity) AS movement_in_quantity,
                    AVG(PI.net_unit_cost) AS movement_in_cost,
                    SUM(PI.quantity * PI.net_unit_cost) AS total_movement_in_cost
                FROM
                    sma_purchase_items PI
                LEFT JOIN sma_purchases AS p
                ON
                    p.id = PI.purchase_id
                WHERE
                    DATE(p.date) BETWEEN '$start_date' AND '$end_date' AND p.grand_total > 0
                    AND PI.purchase_item_id IS NULL AND p.status = 'received'
                GROUP BY
                    PI.product_id
        
                UNION ALL
        
            SELECT
                    ri.product_id,
                    SUM(ri.quantity) AS movement_in_quantity,
                    AVG(ri.real_unit_price) AS movement_in_cost,
                    SUM(ri.quantity * ri.net_cost) AS total_movement_in_cost
            FROM
                    sma_return_items ri
                LEFT JOIN sma_returns AS r
                ON
                    r.id = ri.return_id
                WHERE
                    DATE(r.date) BETWEEN '$start_date' AND '$end_date'
                GROUP BY
                    ri.product_id
        
            UNION ALL 
        
        SELECT
            product_id,
            0 AS movement_in_quantity,
            0 AS movement_in_cost,
            0 AS total_movement_in_cost
            FROM
            (
            SELECT
                titm.product_id,
                IFNULL(SUM(titm.quantity), 0) + IFNULL(SUM(pitm.quantity), 0) AS movement_in_quantity,
                AVG(COALESCE(titm.net_unit_cost, 0) + COALESCE(pitm.net_unit_cost, 0)) AS net_unit_cost
            FROM
                `sma_transfers` AS `trnf`
            LEFT JOIN
            (
                SELECT
                    product_id,
                    transfer_id,
                    SUM(quantity) AS quantity,
                    AVG(net_unit_cost) AS net_unit_cost
                FROM
                    sma_transfer_items
                WHERE
                    DATE(`date`) BETWEEN '$start_date' AND '$end_date'
                GROUP BY
                    transfer_id, product_id
            ) AS titm ON titm.transfer_id = trnf.id
            LEFT JOIN
            (
                SELECT
                    product_id,
                    transfer_id,
                    SUM(quantity) AS quantity,
                    AVG(net_unit_cost) AS net_unit_cost
                FROM
                    sma_purchase_items
                WHERE
                    DATE(`date`) BETWEEN '$start_date' AND '$end_date'
                    AND transfer_id IS NOT NULL
                GROUP BY
                    warehouse_id, product_id
            ) AS pitm ON pitm.transfer_id = trnf.id
            WHERE
                DATE(`trnf`.`date`) BETWEEN '$start_date' AND '$end_date'
                AND `trnf`.`to_warehouse_id` = $from_warehouse_id
            GROUP BY
                product_id
            ) AS combined_data
            GROUP BY
            product_id
        
        ) AS combined_purchase_return_transfer_in GROUP BY product_id
        
        ) AS movement_in ON movement_in.product_id = prd.id
        
        WHERE movement_in.product_id IS NOT NULL AND movement_out.product_id IS NOT NULL");
        //echo $this->db->last_query(); exit;
        $resultSet = array();
        if ($qry->num_rows() > 0) {
            foreach (($qry->result()) as $row) {
                //echo '<pre>';
                //print_r($row);
                $row->movement_in_cost = ($row->total_movement_in_cost / $row->movement_in_quantity);
                $row->movement_out_cost = ($row->total_movement_out_cost / $row->movement_out_quantity);
                $resultSet[$row->product_id] = $row;
            }
        }
        //  echo $this->db->last_query();
        //echo '<pre>', print_r($resultSet), '</pre>';exit;
        return $resultSet;

    }

    public function getInventoryTrialBalanceOLD($start_date, $end_date, $from_warehouse_id = 0, $to_warehouse_id = 0)
    {
        // Opening subquery
        $openingSubquery = $this->db->select('PI.product_id AS product_id, SUM(PI.quantity) AS opening_quantity, AVG(PI.net_unit_cost) AS opening_cost')
            ->from('sma_purchase_items PI')
            ->join('sma_purchases AS p', 'p.id = PI.purchase_id', 'left')
            ->where('(DATE(p.date) < "' . $start_date . '" OR DATE(PI.date) < "' . $start_date . '") AND p.return_id IS NULL')
            ->group_by('PI.product_id', false)
            ->get_compiled_select();

        // Movement In subquery
        $movementInSubquery = $this->db->select('product_id, SUM(movement_in_quantity) AS movement_in_quantity, AVG(movement_in_cost) AS movement_in_cost')
            ->from('(SELECT
                PI.product_id,
                SUM(PI.quantity) AS movement_in_quantity,
                AVG(PI.net_unit_cost) AS movement_in_cost
            FROM
                sma_purchase_items PI
            LEFT JOIN sma_purchases AS p
            ON
                p.id = PI.purchase_id
            WHERE
                DATE(p.date) BETWEEN "' . $start_date . '" AND "' . $end_date . '" AND p.return_id IS NULL
            GROUP BY
                PI.product_id

            UNION ALL

                SELECT
                ri.product_id,
                SUM(ri.quantity) AS movement_in_quantity,
                AVG(ri.real_unit_price) AS movement_in_cost
        FROM
                sma_return_items ri
            LEFT JOIN sma_returns AS r
            ON
                r.id = ri.return_id
            WHERE
                DATE(r.date) BETWEEN "' . $start_date . '" AND "' . $end_date . '"
            GROUP BY
                ri.product_id

            UNION ALL
        
                SELECT
                ti.product_id,
                SUM(ti.quantity) AS movement_in_quantity,
                AVG(ti.unit_cost) AS movement_in_cost
            FROM
                sma_transfer_items ti
            LEFT JOIN
                sma_transfers t ON ti.transfer_id = t.id
            WHERE
                DATE(t.date) BETWEEN "' . $start_date . '" AND "' . $end_date . '" 
                AND (t.to_warehouse_id = 0 OR t.to_warehouse_id = ' . $from_warehouse_id . ')
            GROUP BY
                ti.product_id


        ) AS movement_in_combined')
            ->group_by('product_id', false)
            ->get_compiled_select();
        echo '<br>';

        // Movement Out subquery
        $movementOutSubquery = $this->db->select('product_id, SUM(movement_out_quantity) AS movement_out_quantity, AVG(movement_out_cost) AS movement_out_cost')
            ->from('(SELECT
                product_id,
                SUM(si.quantity) AS movement_out_quantity,
                AVG(si.net_unit_price) AS movement_out_cost
            FROM
                sma_sale_items si
            LEFT JOIN sma_sales AS s
            ON
                s.id = si.sale_id
            WHERE
                DATE(s.date) BETWEEN "' . $start_date . '" AND "' . $end_date . '" 
            GROUP BY
                si.product_id

            UNION ALL

            SELECT
                PI.product_id,
                SUM(PI.quantity) AS movement_out_quantity,
                AVG(PI.net_unit_cost) AS movement_out_cost
            FROM
                sma_purchase_items PI
            LEFT JOIN sma_purchases AS p
            ON
                p.id = PI.purchase_id
            WHERE
                DATE(p.date) BETWEEN "' . $start_date . '" AND "' . $end_date . '"  AND p.return_id IS NOT NULL
            GROUP BY
                PI.product_id

            UNION ALL

            SELECT
                ti.product_id,
                SUM(ti.quantity) AS movement_out_quantity,
                AVG(ti.unit_cost) AS movement_out_cost
            FROM
                sma_transfer_items ti
            LEFT JOIN
                sma_transfers t ON ti.transfer_id = t.id
            WHERE
                DATE(t.date) BETWEEN "' . $start_date . '" AND "' . $end_date . '"  
                AND ((t.from_warehouse_id = 0 OR t.from_warehouse_id =  ' . $from_warehouse_id . ') OR (t.to_warehouse_id = 0 OR t.to_warehouse_id =  ' . $to_warehouse_id . '))
            GROUP BY
                ti.product_id
                    
            ) AS movement_out_combined')
            ->group_by('product_id', false)
            ->get_compiled_select();

        $this->db->select('opening.product_id, prd.code, prd.name');
        $this->db->select('IFNULL(opening.opening_quantity, 0) AS opening_quantity, IFNULL(opening.opening_cost, 0) AS opening_cost, (IFNULL(opening.opening_quantity, 0) * IFNULL(opening.opening_cost, 0)) AS opening_total');
        $this->db->select('IFNULL(movement_in.movement_in_quantity, 0) AS movement_in_quantity, IFNULL(movement_in.movement_in_cost, 0) AS movement_in_cost, (IFNULL(movement_in.movement_in_quantity, 0) * IFNULL(movement_in.movement_in_cost, 0)) AS movement_in_total');
        $this->db->select('IFNULL(movement_out.movement_out_quantity, 0) AS movement_out_quantity, IFNULL(movement_out.movement_out_cost, 0) AS movement_out_cost, (IFNULL(movement_out.movement_out_quantity, 0) * IFNULL(movement_out.movement_out_cost, 0)) AS movement_out_total');
        $this->db->select('(IFNULL(opening.opening_quantity, 0) + IFNULL(movement_in.movement_in_quantity, 0) - IFNULL(movement_out.movement_out_quantity, 0)) AS closing_quantity');
        $this->db->select('(IFNULL(opening.opening_cost, 0) + IFNULL(movement_in.movement_in_cost, 0) - IFNULL(movement_out.movement_out_cost, 0)) AS closing_cost');
        $this->db->select('((IFNULL(opening.opening_quantity, 0) + IFNULL(movement_in.movement_in_quantity, 0) - IFNULL(movement_out.movement_out_quantity, 0)) * (IFNULL(opening.opening_cost, 0) + IFNULL(movement_in.movement_in_cost, 0) - IFNULL(movement_out.movement_out_cost, 0))) AS closing_total');
        $this->db->from('sma_products AS prd');

        $this->db->join('(' . $openingSubquery . ') AS opening', 'prd.id = opening.product_id', 'left');
        $this->db->join('(' . $movementInSubquery . ') AS movement_in', 'opening.product_id = movement_in.product_id', 'left');
        $this->db->join('(' . $movementOutSubquery . ') AS movement_out', 'opening.product_id = movement_out.product_id', 'left');

        $query = $this->db->get();
        // echo $this->db->last_query();
        // echo "<br>";
        if ($query->num_rows() > 0) {
            return $query->result();
        }
    }

    public function getInventoryTrialBalance($start_date, $end_date, $from_warehouse_id = 0, $to_warehouse_id = 0)
    {
        $start_date = $start_date . " 00:00:00";
        $end_date = $end_date . " 23:59:59";

        $qry = $this->db->query("SELECT b.id as product_id, b.code as product_code, b.name as product_name, SUM( IF ( movement_date < '" . $start_date . "',a.quantity,0)) as openning_qty,
            
        AVG(IF(movement_date < '" . $start_date . "',a.net_unit_cost,null)) as openning_cost,
            
        SUM(IF(a.type IN ('purchase', 'customer_return', 'adjustment_increase') AND movement_date BETWEEN '" . $start_date . "' and '" . $end_date . "', a.quantity,0)) as movement_in_qty,
            
        AVG(IF(a.type IN ('purchase', 'customer_return', 'adjustment_increase') AND movement_date BETWEEN '" . $start_date . "' and '" . $end_date . "', a.net_unit_cost,null)) as movement_in_cost,
        
        SUM(IF(a.type IN ('sale', 'pos', 'return_to_supplier','adjustment_decrease') AND movement_date BETWEEN '" . $start_date . "' and '" . $end_date . "', a.quantity,0)) as movement_out_qty,
        
        AVG(IF(a.type IN ('sale', 'pos', 'return_to_supplier','adjustment_decrease') AND movement_date BETWEEN '" . $start_date . "' and '" . $end_date . "', a.net_unit_cost,null)) as movement_out_cost,
            
        
        (SUM(IF(movement_date < '" . $start_date . "', a.quantity, 0)) + 
        
        SUM(IF(a.type IN ('purchase', 'customer_return', 'adjustment_increase') AND movement_date BETWEEN '" . $start_date . "' AND '" . $end_date . "', a.quantity, 0)) - 
        
        SUM( ABS( IF(a.type IN ('sale', 'pos', 'return_to_supplier', 'adjustment_decrease') AND movement_date BETWEEN '" . $start_date . "' AND '" . $end_date . "', a.quantity, 0)) )
        
        ) as closing_qty
              
       FROM `sma_inventory_movements` a
       INNER JOIN sma_products b on a.product_id = b.id
         WHERE a.location_id = '" . $from_warehouse_id . "' and a.net_unit_cost is not null
         AND movement_date BETWEEN '" . $start_date . "' and '" . $end_date . "'
        GROUP BY a.product_id");


        // echo $this->db->last_query();
        // echo "<br>";
        return $qry->result();

    }


    //=== New Item Movement Report Ends ===//

    public function getInventoryMovementReport($start_date = null, $end_date = null)
    {
        $response_array = array();
        $productIDs = $this->getProductIDsByDateRange($start_date, $end_date);
        // Purchased Items
        $data = array();
        if (!empty($productIDs)) {
            $this->db->select('id, code, name')
                ->from('sma_products')
                ->where_in('id', $productIDs)
                ->order_by('id asc')
                ->limit(500, 0);

            $q = $this->db->get();
            if ($q->num_rows() > 0) {
                foreach (($q->result()) as $row) {

                    $itemPurchased = $this->itemPurchased($row->id, $start_date, $end_date);
                    if ($itemPurchased) {
                        $row->item_purchased = $itemPurchased;
                    }

                    $itemReturnedByCustomer = $this->itemReturnedByCustomer($row->id, $start_date, $end_date);
                    if ($itemReturnedByCustomer) {
                        $row->item_return_by_customer = $itemReturnedByCustomer;

                    }

                    $itemPurchasedOpeningBlance = $this->itemPurchasedOpeningBlance($row->id, $start_date);
                    if ($itemPurchasedOpeningBlance) {
                        $row->item_purchased_opening_balance = $itemPurchasedOpeningBlance;
                    }

                    $itemSold = $this->itemSold($row->id, $start_date, $end_date);
                    if ($itemSold) {
                        $row->item_sold = $itemSold;
                    }

                    $itemReturnedToSupplier = $this->itemReturnedToSupplier($row->id, $start_date, $end_date);
                    if ($itemReturnedToSupplier) {
                        $row->item_returned_to_supplier = $itemReturnedToSupplier;
                    }

                    $itemSoldOpeningBalance = $this->itemSoldOpeningBalance($row->id, $start_date, $end_date);
                    if ($itemSoldOpeningBalance) {
                        $row->item_sold_opening_balance = $itemSoldOpeningBalance;
                    }

                    $itemReturnedByCustomerOpeningBlance = $this->itemReturnedByCustomerOpeningBlance($row->id, $start_date, $end_date);
                    if ($itemSoldOpeningBalance) {
                        $row->item_returned_by_customer_opening_blance = $itemReturnedByCustomerOpeningBlance;
                    }

                    $data[] = $row;
                }
            } else {
                $data = array();
            }
        }

        //  $sqlQuery = $this->db->last_query();
        //  echo "Generated SQL Query: " . $sqlQuery;exit;

        return $data;
    }

    private function getProductIDsByDateRange($start_date = null, $end_date = null)
    {
        $productIDs = array();

        // Fetch product IDs from sma_purchase_items
        $this->db
            ->select('DISTINCT (sma_purchase_items.product_id )')
            ->from('sma_purchase_items')
            ->join('sma_purchases', 'sma_purchases.id = sma_purchase_items.purchase_id')
            ->where('sma_purchase_items.date >=', $start_date)
            ->where('sma_purchase_items.date <=', $end_date)
            ->where('sma_purchases.return_id IS NULL');
        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            foreach ($q->result() as $row) {
                $productIDs[] = $row->product_id;
            }
        }

        // Fetch product IDs from sma_sale_items
        $this->db
            ->select('DISTINCT (sma_sale_items.product_id)')
            ->from('sma_sale_items')
            ->join('sma_sales', 'sma_sales.id = sma_sale_items.sale_id')
            ->where('date(sma_sales.date) >=', $start_date)
            ->where('date(sma_sales.date) <=', $end_date);
        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            foreach ($q->result() as $row) {
                $productIDs[] = $row->product_id;
            }
        }

        // Fetch product IDs from sma_return_items
        $this->db
            ->select('DISTINCT (sma_return_items.product_id)')
            ->from('sma_return_items')
            ->join('sma_returns', 'sma_returns.id = sma_return_items.return_id')
            ->where('date(sma_returns.date) >=', $start_date)
            ->where('date(sma_returns.date) <=', $end_date);
        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            foreach ($q->result() as $row) {
                $productIDs[] = $row->product_id;
            }
        }

        // Remove duplicates and return the array of unique product IDs
        $uniqueProductIDs = array_unique($productIDs);

        return $uniqueProductIDs;
    }


    private function itemPurchased($itemId, $start_date = null, $end_date = null)
    {
        $this->db
            ->select('SUM(sma_purchase_items.quantity) as quantity, sma_purchase_items.net_unit_cost')
            ->from('sma_purchase_items')
            ->join('sma_purchases', 'sma_purchases.id=sma_purchase_items.purchase_id')
            ->where('sma_purchase_items.product_id', $itemId)
            ->where('sma_purchase_items.date >=', $start_date)
            ->where('sma_purchase_items.date <=', $end_date)
            ->where('sma_purchases.return_id IS NULL')
            ->group_by('sma_purchase_items.product_id');

        $q = $this->db->get();

        if ($q->num_rows() > 0) {
            return $q->row(); // Return the single row
        } else {
            $notFoundObject = (object) [
                'quantity' => 0,
                'net_unit_cost' => 0.00,
            ];
            return $notFoundObject;
        }
    }

    private function itemReturnedByCustomer($itemId, $start_date = null, $end_date = null)
    {
        $this->db
            ->select('SUM(sma_return_items.quantity) as quantity, sma_return_items.net_unit_price')
            ->from('sma_return_items')
            ->join('sma_returns', 'sma_returns.id=sma_return_items.return_id')
            ->where('sma_return_items.product_id', $itemId)
            ->where('date(sma_returns.date) >=', $start_date)
            ->where('date(sma_returns.date) <=', $end_date)
            ->group_by('sma_return_items.product_id');

        $q = $this->db->get();

        if ($q->num_rows() > 0) {
            return $q->row(); // Return the single row
        } else {
            $notFoundObject = (object) [
                'quantity' => 0,
                'net_unit_price' => 0.00,
            ];
            return $notFoundObject;
        }
    }

    private function itemPurchasedOpeningBlance($itemId, $start_date = null)
    {
        $this->db
            ->select('SUM(sma_purchase_items.quantity) as quantity, sma_purchase_items.net_unit_cost')
            ->from('sma_purchase_items')
            ->join('sma_purchases', 'sma_purchases.id=sma_purchase_items.purchase_id')
            ->where('sma_purchase_items.product_id', $itemId)
            ->where('sma_purchase_items.date <', $start_date)
            ->where('sma_purchases.return_id IS NULL')
            ->group_by('sma_purchase_items.product_id');

        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            return $q->row(); // Return the single row
        } else {
            $notFoundObject = (object) [
                'quantity' => 0,
                'net_unit_cost' => 0.00,
            ];
            return $notFoundObject;
        }
    }

    private function itemSold($itemId, $start_date = null, $end_date = null)
    {
        $this->db
            ->select('SUM(sma_sale_items.quantity) as quantity, sma_sale_items.net_unit_price')
            ->from('sma_sale_items')
            ->join('sma_sales', 'sma_sales.id=sma_sale_items.sale_id')
            ->where('sma_sale_items.product_id', $itemId)
            ->where('date(sma_sales.date) >=', $start_date)
            ->where('date(sma_sales.date) <=', $end_date)
            //->where('sma_purchases.return_id IS NULL')
            ->group_by('sma_sale_items.product_id');

        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            return $q->row(); // Return the single row
        } else {
            $notFoundObject = (object) [
                'quantity' => 0,
                'net_unit_price' => 0.00,
            ];
            return $notFoundObject;
        }
    }

    private function itemReturnedToSupplier($itemId, $start_date = null, $end_date = null)
    {
        $this->db
            ->select('SUM(sma_purchase_items.quantity) as quantity, sma_purchase_items.net_unit_cost')
            ->from('sma_purchase_items')
            ->join('sma_purchases', 'sma_purchases.id=sma_purchase_items.purchase_id')
            ->where('sma_purchase_items.product_id', $itemId)
            ->where('sma_purchase_items.date >=', $start_date)
            ->where('sma_purchase_items.date <=', $end_date)
            ->where('sma_purchases.return_id IS NOT NULL')
            ->group_by('sma_purchase_items.product_id');

        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            return $q->row(); // Return the single row
        } else {
            $notFoundObject = (object) [
                'quantity' => 0,
                'net_unit_cost' => 0.00,
            ];
            return $notFoundObject;
        }
    }

    private function itemSoldOpeningBalance($itemId, $start_date = null)
    {
        $this->db
            ->select('SUM(sma_sale_items.quantity) as quantity, sma_sale_items.net_unit_price')
            ->from('sma_sale_items')
            ->join('sma_sales', 'sma_sales.id=sma_sale_items.sale_id')
            ->where('sma_sale_items.product_id', $itemId)
            ->where('date(sma_sales.date) <', $start_date)
            //->where('sma_sales.date <=', $end_date)
            //->where('sma_purchases.return_id IS NULL')
            ->group_by('sma_sale_items.product_id');

        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            return $q->row(); // Return the single row
        } else {
            $notFoundObject = (object) [
                'quantity' => 0,
                'net_unit_price' => 0.00,
            ];
            return $notFoundObject;
        }
    }

    private function itemReturnedByCustomerOpeningBlance($itemId, $start_date = null)
    {
        $this->db
            ->select('SUM(sma_return_items.quantity), sma_return_items.net_unit_price')
            ->from('sma_return_items')
            ->join('sma_returns', 'sma_returns.id=sma_return_items.return_id')
            ->where('sma_return_items.product_id', $itemId)
            ->where('date(sma_returns.date) <', $start_date)
            ->group_by('sma_return_items.product_id');

        $q = $this->db->get();

        if ($q->num_rows() > 0) {
            return $q->row(); // Return the single row
        } else {
            $notFoundObject = (object) [
                'quantity' => 0,
                'net_unit_price' => 0.00,
            ];
            return $notFoundObject;
        }
    }

    public function getInventoryMovementReportBK($start_date = null, $end_date = null)
    {

        $response_array = array();

        // Purchased Items
        $data = array();
        $this->db
            ->select('sma_products.code, sma_products.name, SUM(sma_purchase_items.quantity) as purchased_items, sma_purchase_items.net_unit_cost')
            ->from('sma_products')
            ->join('sma_purchase_items', 'sma_purchase_items.product_id=sma_products.id')
            ->join('sma_purchases', 'sma_purchases.id=sma_purchase_items.purchase_id')
            ->where('sma_purchase_items.date >=', $start_date)
            ->where('sma_purchase_items.date <=', $end_date)
            ->where('sma_purchases.return_id IS NULL')
            ->group_by('sma_products.id')
            ->order_by('sma_products.id asc');

        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
        } else {
            $data = array();
        }

        $response_array['period']['purchased_items'] = $data;

        // Sold Items
        $data = array();
        $this->db
            ->select('sma_products.code, sma_products.name, SUM(sma_sale_items.quantity) as sale_items, sma_sale_items.net_unit_price')
            ->from('sma_products')
            ->join('sma_sale_items', 'sma_sale_items.product_id=sma_products.id')
            ->join('sma_sales', 'sma_sales.id=sma_sale_items.sale_id')
            ->where('sma_sales.date >=', $start_date)
            ->where('sma_sales.date <=', $end_date)
            //->where('sma_purchases.return_id IS NULL')
            ->group_by('sma_products.id')
            ->order_by('sma_products.id asc');

        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
        } else {
            $data = array();
        }

        $response_array['period']['sale_items'] = $data;

        // Items returned to supplier
        $data = array();
        $this->db
            ->select('sma_products.code, sma_products.name, SUM(sma_purchase_items.quantity) as sreturn_items, sma_purchase_items.net_unit_cost')
            ->from('sma_products')
            ->join('sma_purchase_items', 'sma_purchase_items.product_id=sma_products.id')
            ->join('sma_purchases', 'sma_purchases.id=sma_purchase_items.purchase_id')
            ->where('sma_purchase_items.date >=', $start_date)
            ->where('sma_purchase_items.date <=', $end_date)
            ->where('sma_purchases.return_id IS NOT NULL')
            ->group_by('sma_products.id')
            ->order_by('sma_products.id asc');

        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
        } else {
            $data = array();
        }

        $response_array['period']['supplier_return_items'] = $data;

        // Items returned By Customer
        $data = array();
        $this->db
            ->select('sma_products.code, sma_products.name, SUM(sma_return_items.quantity) as creturn_items, sma_return_items.net_unit_price')
            ->from('sma_products')
            ->join('sma_return_items', 'sma_return_items.product_id=sma_products.id')
            ->join('sma_returns', 'sma_returns.id=sma_return_items.return_id')
            ->where('sma_returns.date >=', $start_date)
            ->where('sma_returns.date <=', $end_date)
            ->group_by('sma_products.id')
            ->order_by('sma_products.id asc');

        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
        } else {
            $data = array();
        }

        $response_array['period']['customer_return_items'] = $data;


        // Opening Balance Purchased Items
        $data = array();
        $this->db
            ->select('sma_products.code, sma_products.name, SUM(sma_purchase_items.quantity) as purchased_items, sma_purchase_items.net_unit_cost')
            ->from('sma_products')
            ->join('sma_purchase_items', 'sma_purchase_items.product_id=sma_products.id')
            ->join('sma_purchases', 'sma_purchases.id=sma_purchase_items.purchase_id')
            ->where('sma_purchase_items.date <', $start_date)
            //->where('sma_purchase_items.date <=', $end_date)
            ->where('sma_purchases.return_id IS NULL')
            ->group_by('sma_products.id')
            ->order_by('sma_products.id asc');

        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
        } else {
            $data = array();
        }

        $response_array['ob']['purchased_items'] = $data;

        // Opening Balance Sold Items
        $data = array();
        $this->db
            ->select('sma_products.code, sma_products.name, SUM(sma_sale_items.quantity) as sale_items, sma_sale_items.net_unit_price')
            ->from('sma_products')
            ->join('sma_sale_items', 'sma_sale_items.product_id=sma_products.id')
            ->join('sma_sales', 'sma_sales.id=sma_sale_items.sale_id')
            ->where('sma_sales.date <', $start_date)
            //->where('sma_sales.date <=', $end_date)
            //->where('sma_purchases.return_id IS NULL')
            ->group_by('sma_products.id')
            ->order_by('sma_products.id asc');

        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
        } else {
            $data = array();
        }

        $response_array['ob']['sale_items'] = $data;

        // Opening Balance Items returned to supplier
        $data = array();
        $this->db
            ->select('sma_products.code, sma_products.name, SUM(sma_purchase_items.quantity) as sreturn_items, sma_purchase_items.net_unit_cost')
            ->from('sma_products')
            ->join('sma_purchase_items', 'sma_purchase_items.product_id=sma_products.id')
            ->join('sma_purchases', 'sma_purchases.id=sma_purchase_items.purchase_id')
            ->where('sma_purchase_items.date <', $start_date)
            //->where('sma_purchase_items.date <=', $end_date)
            ->where('sma_purchases.return_id IS NOT NULL')
            ->group_by('sma_products.id')
            ->order_by('sma_products.id asc');

        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
        } else {
            $data = array();
        }

        $response_array['ob']['supplier_return_items'] = $data;

        // Opening Balance Items returned By Customer
        $data = array();
        $this->db
            ->select('sma_products.code, sma_products.name, SUM(sma_return_items.quantity) as creturn_items, sma_return_items.net_unit_price')
            ->from('sma_products')
            ->join('sma_return_items', 'sma_return_items.product_id=sma_products.id')
            ->join('sma_returns', 'sma_returns.id=sma_return_items.return_id')
            ->where('sma_returns.date <', $start_date)
            //->where('sma_returns.date <=', $end_date)
            ->group_by('sma_products.id')
            ->order_by('sma_products.id asc');

        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
        } else {
            $data = array();
        }

        $response_array['ob']['customer_return_items'] = $data;

        return $response_array;
    }

    public function getVatPurchaseLedgerReport($start_date = null, $end_date = null)
    {

        $this->db
            ->select('sma_purchases.id as purchase_id, SUM(sma_purchase_items.quantity) as total_quantity, sma_purchases.sequence_code as purchase_sequence_code,sma_accounts_entries.id as transaction_id, 
            sma_purchases.supplier, sma_accounts_entries.date, sma_purchases.invoice_number, sma_accounts_entries.number, sma_purchases.grand_total as total_with_vat, SUM(sma_accounts_entryitems.amount) as total_tax, sma_companies.vat_no, sma_companies.sequence_code as supplier_code')
            ->from('sma_accounts_ledgers')
            ->join('sma_accounts_entryitems', 'sma_accounts_entryitems.ledger_id=sma_accounts_ledgers.id')
            ->join('sma_accounts_entries', 'sma_accounts_entries.id=sma_accounts_entryitems.entry_id')
            ->join('sma_purchases', 'sma_purchases.id=sma_accounts_entries.pid', 'left')
            //->join('sma_tax_rates', 'sma_tax_rates.id=sma_purchases.order_tax_id', 'left')
            ->join('sma_companies', 'sma_companies.id=sma_purchases.supplier_id', 'left')
            ->join('sma_purchase_items', 'sma_purchase_items.purchase_id=sma_purchases.id', 'left')
            ->where('sma_accounts_entries.date >=', $start_date)
            ->where('sma_accounts_entries.date <=', $end_date)
            ->where('sma_accounts_ledgers.name =', 'VAT on Purchases')
            ->group_by('sma_accounts_entries.id')
            //->having('SUM(sma_purchase_items.quantity) >=', 0)
            ->order_by('sma_accounts_entries.date asc');

        $q = $this->db->get();
        $sqlQuery = $this->db->last_query();
        //echo "Generated SQL Query: " . $sqlQuery;exit;
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                if ($row->purchase_id == '') {
                    $row->type = 'Manual';
                    $row->total_quantity = '0';
                    $row->supplier_code = '-';
                    $row->vat_no = '-';
                    $row->supplier = 'Manual Journal Entry';
                    $row->total_with_vat = $row->total_tax;
                    // $row->tax_name = '-';
                } else {
                    $row->type = 'Purchase';
                }

                if ($row->total_quantity >= 0 || $row->purchase_id == '') {
                    $data[] = $row;
                }

            }
        } else {
            $data = array();
        }

        return $data;
    }

    public function getVatPurchaseReport($start_date = null, $end_date = null, $warehouse_id = null, $filterOnType = null)
    {


        // $this->db
        //     ->select('sma_purchases.id,withT.subtotal as total_item_with_vat, withOutT.subtotal as total_item_with_zero_tax, SUM(sma_purchase_items.quantity) as total_quantity, sma_purchases.sequence_code as transaction_id, sma_purchases.supplier, sma_purchases.date, sma_purchases.invoice_number, sma_purchases.total_discount,sma_purchases.grand_total as total_with_vat, sma_purchases.product_tax as total_tax, sma_companies.vat_no, sma_companies.sequence_code as supplier_code, sma_accounts_entries.number as account_number')
        //     ->from('sma_purchases')
        //     ->join('sma_companies', 'sma_companies.id=sma_purchases.supplier_id')
        //     ->join('sma_purchase_items', 'sma_purchase_items.purchase_id=sma_purchases.id')
        //     ->join('sma_accounts_entries', 'sma_accounts_entries.pid=sma_purchases.id', 'left')

        //     //->join('sma_purchase_items withT', 'withT.purchase_id=sma_purchases.id AND withT.tax > 0','left')
        //     //->join('sma_purchase_items withOutT', 'withOutT.purchase_id=sma_purchases.id AND withOutT.tax = 0','left')
        //     ->join('(SELECT purchase_id, SUM(subtotal) as subtotal FROM `sma_purchase_items` WHERE tax > 0 group by purchase_id ) withT', 'withT.purchase_id=sma_purchases.id', 'left')
        //     ->join('(SELECT purchase_id, SUM(subtotal) as subtotal FROM `sma_purchase_items` WHERE tax=0 group by purchase_id ) withOutT', 'withOutT.purchase_id=sma_purchases.id', 'left')

        //     //->join('sma_tax_rates', 'sma_tax_rates.id=sma_purchases.order_tax_id')
        //     ->where('DATE(sma_purchases.date) >=', $start_date)
        //     ->where('DATE(sma_purchases.date) <=', $end_date)
        //     //->where('sma_purchases.return_id IS NULL')
        //     ->group_by('sma_purchase_items.purchase_id')
        //     ->having('SUM(sma_purchase_items.quantity) >=', 0)
        //     ->order_by('sma_purchases.date asc');

        // $q = $this->db->get();

        $query = "SELECT * FROM(
                                 SELECT
        p.id AS trans_ID,
        'purchases' AS trans_type,
        p.warehouse_id,
        w.name AS warehouse,
        p.date AS trans_date,
        p.invoice_number AS trans_invoice_number,
        p.reference_no,
        p.supplier AS supplier_name,
        c.vat_no AS supplier_vat_no,
        p.total  AS total_invoice,
        p.total_net_purchase  AS total_after_discount,
        p.total_discount  AS total_discount,
        p.grand_total  AS grand_total,
        p.total_tax  AS total_tax,
        ae.number AS ledger_entry_number
    FROM
        sma_purchases AS p
    JOIN sma_companies AS c
    ON
        c.id = p.supplier_id
    LEFT JOIN sma_warehouses AS w
    ON
        p.warehouse_id = w.id
    LEFT JOIN sma_accounts_entries AS ae
    ON
        ae.pid = p.id
    UNION ALL
    SELECT
        r.id AS trans_ID,
        'returnSupplier' AS trans_type,
        r.warehouse_id,
        w.name AS warehouse,
        r.date AS trans_date,
        r.reference_no AS trans_invoice_number,
        r.reference_no,
        r.supplier AS supplier_name,
        c.vat_no AS supplier_vat_no,
        r.total * -1 AS total_invoice,
        r.total_net_purchase * -1 AS total_after_discount, 
        r.total_discount * -1 AS total_discount,
        r.grand_total * -1 AS grand_total,
        r.total_tax * -1 AS total_tax,
        ae.number AS ledger_entry_number
    FROM
        sma_returns_supplier AS r
    JOIN sma_companies AS c
    ON
        c.id = r.supplier_id
    LEFT JOIN sma_accounts_entries AS ae
    ON
        ae.rid = r.id
    LEFT JOIN sma_warehouses AS w
    ON
        r.warehouse_id = w.id
                           
    UNION ALL

    SELECT 
        m.id as trans_ID,  
        'serviceInvoice' as trans_type,   
        '-' as warehouse, 
        m.date as trans_date, 
        m.reference_no as trans_invoice_number,
        m.reference_no,
        0  as warehouse_id,

        
        c.company AS supplier_name,
        c.vat_no AS supplier_vat_no,  
        
        m.payment_amount as total_invoice,
        0 as total_discount,
        m.payment_amount AS grand_total,
        m.bank_charges AS total_tax,
        m.payment_amount AS total_after_discount,
        ae.number AS ledger_entry_number


    FROM sma_memo m
    JOIN sma_companies as c ON c.id = m.supplier_id
    LEFT JOIN sma_accounts_entries as ae ON ae.memo_id = m.id

    WHERE type = 'serviceinvoicesupplier'

    ) AS a
                    WHERE DATE(a.trans_date) >= '" . $start_date . "' AND DATE(a.trans_date) <= '" . $end_date . "'";

        if ($warehouse_id) {
            $query .= " AND a.warehouse_id= '" . $warehouse_id . "'";
        }

        if ($filterOnType) {
            $query .= " AND a.trans_type= '" . $filterOnType . "'";
        }
        // echo $query;

        $q = $this->db->query($query);
        //echo $this->db->last_query();
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
        } else {
            $data = array();
        }

        return $data;
    }


    public function getVatSaleReport($start_date = null, $end_date = null, $warehouse_id = null, $filterOnType = null)
    {

        $query = "SELECT * FROM(
                             SELECT 
                                s.id as trans_ID,  
                                'sale' as trans_type,    
                                s.date as trans_date, 

                                s.warehouse_id,
                                w.name as warehouse,
                                s.reference_no,

                                s.customer AS customer_name,
                                c.vat_no AS customer_vat_no,    
                                
                                s.total as total_invoice,
                                s.total_discount as total_discount,
                                s.grand_total AS grand_total,
                                s.total_tax AS total_tax,
                                s.total_net_sale as total_after_discount,
                                withT.subtotal AS total_item_with_vat,
                                withOutT.subtotal AS total_item_without_tax,
                                ae.number AS ledger_entry_number
                            
                            FROM sma_sales as s
                            JOIN sma_sale_items as si ON si.sale_id = s.id
                            JOIN sma_companies as c ON c.id = s.customer_id
                            LEFT JOIN sma_accounts_entries as ae ON ae.sid = s.id
                            LEFT JOIN sma_warehouses AS w on s.warehouse_id=w.id
                            LEFT JOIN(
                                SELECT
                                    sale_id,
                                    SUM(subtotal) AS subtotal
                                FROM
                                    sma_sale_items
                                WHERE
                                    tax != 0
                                GROUP BY
                                    sale_id
                            ) withT ON withT.sale_id = s.id

                            LEFT JOIN(
                                SELECT
                                    sale_id,
                                    SUM(subtotal) AS subtotal
                                FROM
                                    sma_sale_items
                                WHERE
                                    tax = 0
                                GROUP BY
                                    sale_id
                            ) withOutT ON withOutT.sale_id = s.id
                            GROUP BY
                                si.sale_id  

                            UNION ALL

                            SELECT 
                                r.id as trans_ID,  
                                'returnCustomer' as trans_type,    
                                r.date as trans_date, 

                                r.warehouse_id,
                                w.name as warehouse,
                                r.reference_no,

                                r.customer AS customer_name,
                                c.vat_no AS customer_vat_no,    
                                
                                r.total * -1 as total_invoice,
                                r.total_discount * -1 as total_discount,
                                r.grand_total * -1 AS grand_total,
                                r.total_tax * -1 AS total_tax,
                                r.total_net_return * -1 as total_after_discount,
                                withT.subtotal * -1 AS total_item_with_vat,
                                withOutT.subtotal AS total_item_without_tax,
                                ae.number AS ledger_entry_number
                            
                            FROM sma_returns as r
                            JOIN sma_return_items as ri ON ri.return_id = r.id
                            JOIN sma_companies as c ON c.id = r.customer_id
                            LEFT JOIN sma_accounts_entries as ae ON ae.rid = r.id
                            LEFT JOIN sma_warehouses AS w on r.warehouse_id=w.id
                            LEFT JOIN(
                                SELECT
                                    return_id,
                                    SUM(subtotal) AS subtotal
                                FROM
                                    sma_return_items
                                WHERE
                                    tax != 0
                                GROUP BY
                                    return_id
                            ) withT ON withT.return_id = r.id

                            LEFT JOIN(
                                SELECT
                                    return_id,
                                    SUM(subtotal) AS subtotal
                                FROM
                                    sma_return_items
                                WHERE
                                    tax = 0
                                GROUP BY
                                    return_id
                            ) withOutT ON withOutT.return_id = r.id
                            GROUP BY
                                ri.return_id 

                            UNION ALL

                            SELECT 
                                m.id as trans_ID,  
                                'serviceInvoice' as trans_type,    
                                m.date as trans_date, 

                                0  as warehouse_id,
                                '-' as warehouse,
                                m.reference_no,

                                c.name AS customer_name,
                                c.vat_no AS customer_vat_no,    
                                
                                0 as total_invoice,
                                0 as total_discount,
                                0 as total_after_discount,
                                m.payment_amount AS grand_total,
                                m.bank_charges AS total_tax,
                                0 AS total_item_with_vat,
                                0 AS total_item_without_tax,
                                ae.number AS ledger_entry_number


                            FROM sma_memo m
                            JOIN sma_companies as c ON c.id = m.customer_id
                            LEFT JOIN sma_accounts_entries as ae ON ae.memo_id = m.id

                            WHERE type = 'serviceinvoice'
                                
                                ) AS a ";

        $query .= " WHERE DATE(a.trans_date) >= '" . $start_date . "' AND DATE(a.trans_date) <= '" . $end_date . "'";

        if ($warehouse_id) {
            $query .= " AND a.warehouse_id= '" . $warehouse_id . "'";
        }

        if ($filterOnType) {
            $query .= " AND a.trans_type= '" . $filterOnType . "'";
        }

        $query .= " ORDER BY a.trans_date DESC";

        //echo $query;
        $q = $this->db->query($query);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
        } else {
            $data = array();
        }

        return $data;
    }


    public function getPurchasesTax($start_date = null, $end_date = null)
    {
        $this->db->select_sum('igst')->select_sum('cgst')->select_sum('sgst')
            ->select_sum('product_tax')->select_sum('order_tax')
            ->select_sum('grand_total')->select_sum('paid');
        if ($start_date) {
            $this->db->where('date >=', $start_date);
        }
        if ($end_date) {
            $this->db->where('date <=', $end_date);
        }
        $q = $this->db->get('purchases');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getPurchasesTotals($supplier_id)
    {
        $this->db->select('SUM(COALESCE(grand_total, 0)) as total_amount, SUM(COALESCE(paid, 0)) as paid', false)
            ->where('supplier_id', $supplier_id);
        $q = $this->db->get('purchases');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getReturns($date, $warehouse_id = null, $year = null, $month = null)
    {
        $sdate = $date . ' 00:00:00';
        $edate = $date . ' 23:59:59';
        $this->db->select('SUM( COALESCE( grand_total, 0 ) ) AS total', false)
            ->where('sale_status', 'returned');
        if ($date) {
            $this->db->where('date >=', $sdate)->where('date <=', $edate);
        } elseif ($month) {
            $this->load->helper('date');
            $last_day = days_in_month($month, $year);
            $this->db->where('date >=', $year . '-' . $month . '-01 00:00:00');
            $this->db->where('date <=', $year . '-' . $month . '-' . $last_day . ' 23:59:59');
        }

        if ($warehouse_id) {
            $this->db->where('warehouse_id', $warehouse_id);
        }

        $q = $this->db->get('sales');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getSalesTax($start_date = null, $end_date = null)
    {
        $this->db->select_sum('igst')->select_sum('cgst')->select_sum('sgst')
            ->select_sum('product_tax')->select_sum('order_tax')
            ->select_sum('grand_total')->select_sum('paid');
        if ($start_date) {
            $this->db->where('date >=', $start_date);
        }
        if ($end_date) {
            $this->db->where('date <=', $end_date);
        }
        $q = $this->db->get('sales');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getSalesTotals($customer_id)
    {
        $this->db->select('SUM(COALESCE(grand_total, 0)) as total_amount, SUM(COALESCE(paid, 0)) as paid', false)
            ->where('customer_id', $customer_id);
        $q = $this->db->get('sales');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getAllCategories()
    {
        return $this->db->get('categories')->result();
    }

    public function getStaff()
    {
        if ($this->Admin) {
            $this->db->where('group_id !=', 1);
        }
        $this->db->where('group_id !=', 3)->where('group_id !=', 4);
        $q = $this->db->get('users');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getStaffDailyPurchases($user_id, $year, $month, $warehouse_id = null)
    {
        $myQuery = "SELECT DATE_FORMAT( date,  '%e' ) AS date, SUM( COALESCE( product_tax, 0 ) ) AS tax1, SUM( COALESCE( order_tax, 0 ) ) AS tax2, SUM( COALESCE( grand_total, 0 ) ) AS total, SUM( COALESCE( total_discount, 0 ) ) AS discount, SUM( COALESCE( shipping, 0 ) ) AS shipping
            FROM " . $this->db->dbprefix('purchases') . ' WHERE ';
        if ($warehouse_id) {
            $myQuery .= " warehouse_id = {$warehouse_id} AND ";
        }
        $myQuery .= " created_by = {$user_id} AND DATE_FORMAT( date,  '%Y-%m' ) =  '{$year}-{$month}'
            GROUP BY DATE_FORMAT( date,  '%e' )";
        $q = $this->db->query($myQuery, false);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getStaffDailySales($user_id, $year, $month, $warehouse_id = null)
    {
        $myQuery = "SELECT DATE_FORMAT( date,  '%e' ) AS date, SUM( COALESCE( product_tax, 0 ) ) AS tax1, SUM( COALESCE( order_tax, 0 ) ) AS tax2, SUM( COALESCE( grand_total, 0 ) ) AS total, SUM( COALESCE( total_discount, 0 ) ) AS discount, SUM( COALESCE( shipping, 0 ) ) AS shipping
            FROM " . $this->db->dbprefix('sales') . ' WHERE ';
        if ($warehouse_id) {
            $myQuery .= " warehouse_id = {$warehouse_id} AND ";
        }
        $myQuery .= " created_by = {$user_id} AND DATE_FORMAT( date,  '%Y-%m' ) =  '{$year}-{$month}'
            GROUP BY DATE_FORMAT( date,  '%e' )";
        $q = $this->db->query($myQuery, false);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getStaffMonthlyPurchases($user_id, $year, $warehouse_id = null)
    {
        $myQuery = "SELECT DATE_FORMAT( date,  '%c' ) AS date, SUM( COALESCE( product_tax, 0 ) ) AS tax1, SUM( COALESCE( order_tax, 0 ) ) AS tax2, SUM( COALESCE( grand_total, 0 ) ) AS total, SUM( COALESCE( total_discount, 0 ) ) AS discount, SUM( COALESCE( shipping, 0 ) ) AS shipping
            FROM " . $this->db->dbprefix('purchases') . ' WHERE ';
        if ($warehouse_id) {
            $myQuery .= " warehouse_id = {$warehouse_id} AND ";
        }
        $myQuery .= " created_by = {$user_id} AND DATE_FORMAT( date,  '%Y' ) =  '{$year}'
            GROUP BY date_format( date, '%c' ) ORDER BY date_format( date, '%c' ) ASC";
        $q = $this->db->query($myQuery, false);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getStaffMonthlySales($user_id, $year, $warehouse_id = null)
    {
        $myQuery = "SELECT DATE_FORMAT( date,  '%c' ) AS date, SUM( COALESCE( product_tax, 0 ) ) AS tax1, SUM( COALESCE( order_tax, 0 ) ) AS tax2, SUM( COALESCE( grand_total, 0 ) ) AS total, SUM( COALESCE( total_discount, 0 ) ) AS discount, SUM( COALESCE( shipping, 0 ) ) AS shipping
            FROM " . $this->db->dbprefix('sales') . ' WHERE ';
        if ($warehouse_id) {
            $myQuery .= " warehouse_id = {$warehouse_id} AND ";
        }
        $myQuery .= " created_by = {$user_id} AND DATE_FORMAT( date,  '%Y' ) =  '{$year}'
            GROUP BY date_format( date, '%c' ) ORDER BY date_format( date, '%c' ) ASC";
        $q = $this->db->query($myQuery, false);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getStaffPurchases($user_id)
    {
        $this->db->select('count(id) as total, SUM(COALESCE(grand_total, 0)) as total_amount, SUM(COALESCE(paid, 0)) as paid', false)
            ->where('created_by', $user_id);
        $q = $this->db->get('purchases');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getStaffSales($user_id)
    {
        $this->db->select('count(id) as total, SUM(COALESCE(grand_total, 0)) as total_amount, SUM(COALESCE(paid, 0)) as paid', false)
            ->where('created_by', $user_id);
        $q = $this->db->get('sales');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getStockValue()
    {
        $q = $this->db->query('SELECT SUM(by_price) as stock_by_price, SUM(by_cost) as stock_by_cost FROM ( Select COALESCE(sum(' . $this->db->dbprefix('warehouses_products') . '.quantity), 0)*price as by_price, COALESCE(sum(' . $this->db->dbprefix('warehouses_products') . '.quantity), 0)*cost as by_cost FROM ' . $this->db->dbprefix('products') . ' JOIN ' . $this->db->dbprefix('warehouses_products') . ' ON ' . $this->db->dbprefix('warehouses_products') . '.product_id=' . $this->db->dbprefix('products') . '.id GROUP BY ' . $this->db->dbprefix('products') . '.id ) a');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getSupplierPurchases($supplier_id)
    {
        $this->db->from('purchases')->where('supplier_id', $supplier_id);
        return $this->db->count_all_results();
    }

    public function getTotalExpenses($start, $end, $warehouse_id = null)
    {
        $this->db->select('count(id) as total, sum(COALESCE(amount, 0)) as total_amount', false)
            ->where('date BETWEEN ' . $start . ' and ' . $end);
        if ($warehouse_id) {
            $this->db->where('warehouse_id', $warehouse_id);
        }
        $q = $this->db->get('expenses');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getTotalPaidAmount($start, $end)
    {
        $this->db->select('count(id) as total, SUM(COALESCE(amount, 0)) as total_amount', false)
            ->where('type', 'sent')
            ->where('date BETWEEN ' . $start . ' and ' . $end);
        $q = $this->db->get('payments');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getTotalPurchases($start, $end, $warehouse_id = null)
    {
        $this->db->select('count(id) as total, sum(COALESCE(grand_total, 0)) as total_amount, SUM(COALESCE(paid, 0)) as paid, SUM(COALESCE(total_tax, 0)) as tax', false)
            ->where('status !=', 'pending')
            ->where('date BETWEEN ' . $start . ' and ' . $end);
        if ($warehouse_id) {
            $this->db->where('warehouse_id', $warehouse_id);
        }
        $q = $this->db->get('purchases');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getTotalReceivedAmount($start, $end)
    {
        $this->db->select('count(id) as total, SUM(COALESCE(amount, 0)) as total_amount', false)
            ->where('type', 'received')
            ->where('date BETWEEN ' . $start . ' and ' . $end);
        $q = $this->db->get('payments');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getTotalReceivedCashAmount($start, $end)
    {
        $this->db->select('count(id) as total, SUM(COALESCE(amount, 0)) as total_amount', false)
            ->where('type', 'received')->where('paid_by', 'cash')
            ->where('date BETWEEN ' . $start . ' and ' . $end);
        $q = $this->db->get('payments');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getTotalReceivedCCAmount($start, $end)
    {
        $this->db->select('count(id) as total, SUM(COALESCE(amount, 0)) as total_amount', false)
            ->where('type', 'received')->where('paid_by', 'CC')
            ->where('date BETWEEN ' . $start . ' and ' . $end);
        $q = $this->db->get('payments');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getTotalReceivedChequeAmount($start, $end)
    {
        $this->db->select('count(id) as total, SUM(COALESCE(amount, 0)) as total_amount', false)
            ->where('type', 'received')->where('paid_by', 'Cheque')
            ->where('date BETWEEN ' . $start . ' and ' . $end);
        $q = $this->db->get('payments');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getTotalReceivedPPPAmount($start, $end)
    {
        $this->db->select('count(id) as total, SUM(COALESCE(amount, 0)) as total_amount', false)
            ->where('type', 'received')->where('paid_by', 'ppp')
            ->where('date BETWEEN ' . $start . ' and ' . $end);
        $q = $this->db->get('payments');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getTotalReceivedStripeAmount($start, $end)
    {
        $this->db->select('count(id) as total, SUM(COALESCE(amount, 0)) as total_amount', false)
            ->where('type', 'received')->where('paid_by', 'stripe')
            ->where('date BETWEEN ' . $start . ' and ' . $end);
        $q = $this->db->get('payments');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getTotalReturnedAmount($start, $end)
    {
        $this->db->select('count(id) as total, SUM(COALESCE(amount, 0)) as total_amount', false)
            ->where('type', 'returned')
            ->where('date BETWEEN ' . $start . ' and ' . $end);
        $q = $this->db->get('payments');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getTotalReturnSales($start, $end, $warehouse_id = null)
    {
        $this->db->select('count(id) as total, sum(COALESCE(grand_total, 0)) as total_amount, SUM(COALESCE(paid, 0)) as paid, SUM(COALESCE(total_tax, 0)) as tax', false)
            ->where('date BETWEEN ' . $start . ' and ' . $end);
        if ($warehouse_id) {
            $this->db->where('warehouse_id', $warehouse_id);
        }
        $q = $this->db->get('returns');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getTotalSales($start, $end, $warehouse_id = null)
    {
        $this->db->select('count(id) as total, sum(COALESCE(grand_total, 0)) as total_amount, SUM(COALESCE(paid, 0)) as paid, SUM(COALESCE(total_tax, 0)) as tax', false)
            ->where('sale_status !=', 'pending')
            ->where('date BETWEEN ' . $start . ' and ' . $end);
        if ($warehouse_id) {
            $this->db->where('warehouse_id', $warehouse_id);
        }
        $q = $this->db->get('sales');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getWarehouseStockValue($id)
    {
        $q = $this->db->query('SELECT SUM(by_price) as stock_by_price, SUM(by_cost) as stock_by_cost FROM ( Select sum(COALESCE(' . $this->db->dbprefix('warehouses_products') . '.quantity, 0))*price as by_price, sum(COALESCE(' . $this->db->dbprefix('warehouses_products') . '.quantity, 0))*cost as by_cost FROM ' . $this->db->dbprefix('products') . ' JOIN ' . $this->db->dbprefix('warehouses_products') . ' ON ' . $this->db->dbprefix('warehouses_products') . '.product_id=' . $this->db->dbprefix('products') . '.id WHERE ' . $this->db->dbprefix('warehouses_products') . '.warehouse_id = ? GROUP BY ' . $this->db->dbprefix('products') . '.id ) a', [$id]);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getWarehouseTotals($warehouse_id = null)
    {
        $this->db->select('sum(quantity) as total_quantity, count(id) as total_items', false);
        $this->db->where('quantity !=', 0);
        if ($warehouse_id) {
            $this->db->where('warehouse_id', $warehouse_id);
        }
        $q = $this->db->get('warehouses_products');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getCollectionsByPharmacy($start_date, $end_date, $warehouse)
    {
        // error_reporting(-1);
        // ini_set('display_errors', 1);
   /*     SELECT 
        e.date AS transaction_date,
        SUM(CASE WHEN e.transaction_type = 'pos' AND ei.narration = 'cash' THEN ei.amount ELSE 0 END) AS total_cash,
        SUM(CASE WHEN e.transaction_type = 'pos' AND ei.narration = 'Credit Card' THEN ei.amount ELSE 0 END) AS total_credit_card,
        SUM(CASE WHEN e.transaction_type = 'pos' AND ei.narration = 'discount' THEN ei.amount ELSE 0 END) AS total_discount,
        SUM(CASE WHEN e.transaction_type = 'returncustomerorder' AND ei.narration = 'customer' THEN ei.amount ELSE 0 END) AS total_returns
    FROM 
    sma_sales s
    JOIN 
        sma_accounts_entries e ON s.id = e.sid
    JOIN 
        sma_accounts_entryitems ei ON e.id = ei.entry_id
    WHERE 
        e.transaction_type IN( 'pos','returncustomerorder')
        AND DATE(s.date) >= '" . trim($start_date) . "' 
        AND DATE(s.date) <= '" . trim($end_date) . "'
        AND ei.narration IN('cash', 'Credit Card')
        AND s.warehouse_id = " . $warehouse . "
    GROUP BY 
    DATE(e.date)
    ORDER BY 
    DATE(e.date) */
        $sql = "
                   
              SELECT DATE(date) as transaction_date,
               sum(amount) , 
                sum( if(paid_by = 'cash' , amount, 0) ) as total_cash,
                sum( if(paid_by = 'CC' , amount, 0) ) as total_credit_card,
                0 AS total_discount,
                0 AS total_returns
                FROM `sma_payments`
                WHERE 
                 DATE(date) >= '" . trim($start_date) . "' 
                  AND DATE(date) <= '" . trim($end_date) . "'  
                 GROUP BY 
                    DATE(date)
                    ORDER BY 
                    DATE(date)

        ";

        $q = $this->db->query($sql);
        $data = array();
        //echo $this->db->last_query();
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
        }

        return $data;
    }

    public function getSalesByCategory($start_date, $end_date, $warehouse)
    {
        $where = '' ;
        if( $warehouse != '' ){
            $where = " AND s.warehouse_id = " . $warehouse ;
        }
        $sql = " SELECT 
                    c.category_code,
                    c.name as category_name,
                    ROUND(SUM(si.totalbeforevat),2) AS total_sales,
                    SUM(si.main_net) AS total_main_net,
                    ROUND(SUM(si.tax), 2) AS total_vat,
                    ROUND((SUM(si.totalbeforevat) / t.total_sales) * 100, 2) AS sales_percentage,
                    ROUND((SUM(si.tax) / t.total_vat) * 100, 2) AS vat_percentage,
                    ROUND((SUM(si.main_net) / t.total_main_net) * 100, 2) AS main_net_percentage
                FROM 
                    sma_sale_items si
                LEFT JOIN 
                    sma_products p ON si.product_id = p.id
                LEFT JOIN 
                    sma_categories c ON p.category_id = c.id
                LEFT JOIN 
                    sma_sales s ON si.sale_id = s.id     
                CROSS JOIN (
                    SELECT 
                        SUM(totalbeforevat) AS total_sales, 
                        SUM(main_net) AS total_main_net, 
                        SUM(tax) AS total_vat 
                    FROM 
                        sma_sale_items si
                    INNER JOIN sma_sales s ON si.sale_id = s.id
                    WHERE 
                    DATE(s.date) >= '" . trim($start_date) . "' 
                    AND DATE(s.date) <= '" . trim($end_date) . "'   
                ) t
            WHERE 
                DATE(s.date) >= '" . trim($start_date) . "' 
                AND DATE(s.date) <= '" . trim($end_date) . "'
                ".$where."
                GROUP BY 
                    c.name
                ORDER BY 
                    total_sales DESC ";
        $q = $this->db->query($sql);
        $sales = array();
        //echo $this->db->last_query();
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $sales[$row->category_code] = $row;
            }
        }


        /**
         * get returns
         */
        $sql = " SELECT 
            c.category_code,
            c.name as category_name,
            ROUND( COALESCE( SUM( si.totalbeforevat ),0 ),2 ) AS total_sales,
            COALESCE( SUM(si.subtotal), 0) AS total_main_net,
            ROUND( COALESCE( SUM(si.tax) ,0 ) , 2) AS total_vat,
            ROUND( COALESCE( (SUM(si.totalbeforevat) / t.total_sales) * 100 , 0), 2) AS sales_percentage,
            ROUND( COALESCE( (SUM(si.tax) / t.total_vat) * 100, 0), 2) AS vat_percentage,
            ROUND( COALESCE( (SUM(si.subtotal) / t.total_main_net) * 100, 0), 2) AS main_net_percentage
        FROM 
            sma_return_items si
        LEFT JOIN 
            sma_products p ON si.product_id = p.id
        LEFT JOIN 
            sma_categories c ON p.category_id = c.id
        LEFT JOIN 
            sma_returns s ON si.return_id = s.id     
        CROSS JOIN (
            SELECT 
                SUM(totalbeforevat) AS total_sales, 
                SUM(subtotal) AS total_main_net, 
                SUM(tax) AS total_vat 
            FROM 
                sma_return_items si
            INNER JOIN sma_returns s ON si.return_id = s.id
            WHERE 
            DATE(s.date) >= '" . trim($start_date) . "' 
            AND DATE(s.date) <= '" . trim($end_date) . "'   
        ) t
        WHERE 
        DATE(s.date) >= '" . trim($start_date) . "' 
        AND DATE(s.date) <= '" . trim($end_date) . "'
       ".$where."
        GROUP BY 
            c.category_code, c.name
        ORDER BY 
            total_sales DESC  ";
        $q = $this->db->query($sql);
        $returns = array();
        //echo $this->db->last_query();
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $returns[$row->category_code] = $row;
            }
        }

                     /**get grand discount */
                     $sql = " SELECT
                     ROUND( SUM( s.total_discount), 2) as grand_sales_discount
                       FROM  
                     sma_sales s 
                                         
                     WHERE 
                         DATE(s.date) >= '" . trim($start_date) . "' 
                         AND DATE(s.date) <= '" . trim($end_date) . "'
                         ".$where."
                     ";
     
             $q = $this->db->query($sql);
             $grand_sales_discount = array();
             //echo $this->db->last_query();
             if ($q->num_rows() > 0) {
                 $grand_sales_discount = $q->result();
             }
     


        return array('sales' => $sales, 'returns' => $returns, 'grand_sales_discount' => $grand_sales_discount[0]);
    }

    public function getSalesByItems($start_date, $end_date, $warehouse)
    {
        $where = '' ;
        if( $warehouse != '' ){
            $where = " AND s.warehouse_id = " . $warehouse ;
        }
         $sql = "SELECT
            p.item_code, 
            p.name,
            s.date,
            s.id,
            si.avz_item_code,
            si.product_name,
            si.quantity,
            si.net_cost as cost_price,
            si.net_unit_price as sale_price,
            si.subtotal as total_sale,
            si.item_discount,
            si.second_discount_value,
            si.totalbeforevat,
            si.tax,
            si.main_net,
            s.customer
     FROM 
      sma_sale_items si
      JOIN 
      sma_sales s ON s.id = si.sale_id
      JOIN 
        sma_products p ON si.product_id = p.id
        WHERE 
            DATE(s.date) >= '" . trim($start_date) . "' 
            AND DATE(s.date) <= '" . trim($end_date) . "'
            ".$where."
        ORDER BY 
        DATE(s.date)
";

        $q = $this->db->query($sql);
        $sales = array();
        //echo $this->db->last_query();
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $sales[] = $row;
            }
        }


        /**get grand total */
        $sql = "SELECT
                ROUND( SUM(si.quantity),2) as grand_quantity,
                ROUND( SUM(COALESCE(si.net_cost, 0) * COALESCE(si.quantity, 0)) , 2) AS grand_cost,
                ROUND( SUM(si.subtotal),2) as grand_sale,
                ROUND( SUM(COALESCE(si.item_discount, 0) + COALESCE(si.second_discount_value, 0)) , 2) AS grand_discount,
                ROUND( SUM( si.totalbeforevat), 2) as grand_beforvate,
                ROUND( SUM( si.tax),2) as grand_vat,
                ROUND( SUM( si.main_net), 2) as grand_main_net
            FROM 
            sma_sale_items si
            JOIN 
            sma_sales s ON s.id = si.sale_id
            JOIN 
                sma_products p ON si.product_id = p.id
            WHERE 
                DATE(s.date) >= '" . trim($start_date) . "' 
                AND DATE(s.date) <= '" . trim($end_date) . "'
                ".$where."
            ";

                $q = $this->db->query($sql);
                $grand = array();
                //echo $this->db->last_query();
                if ($q->num_rows() > 0) {
                    $grand = $q->result();
                }


                 /**get grand discount */
        $sql = " SELECT
                ROUND( SUM( s.total_discount), 2) as grand_sales_discount
                  FROM  
                sma_sales s 
                                    
                WHERE 
                    DATE(s.date) >= '" . trim($start_date) . "' 
                    AND DATE(s.date) <= '" . trim($end_date) . "'
                    ".$where."
                ";

        $q = $this->db->query($sql);
        $grand_sales_discount = array();
        //echo $this->db->last_query();
        if ($q->num_rows() > 0) {
            $grand_sales_discount = $q->result();
        }


        return array('sales'=> $sales, 'grand' => $grand[0], 'grand_sales_discount' => $grand_sales_discount[0]);
    }

    public function getPharmacistsCommission($start_date, $end_date, $warehouse, $pharmacist)
    {
         $sql = "SELECT
            si.product_code,
            si.product_name,
            si.quantity,
            si.main_net,
            ic.commission_value,
            s.created_by AS PharmacistID,
            s.id as invoice_number,
            ROUND(SUM(si.main_net * ic.commission_value / 100),2) AS TotalCommission
        FROM
            sma_sale_items si
        JOIN
            sma_sales s ON si.sale_id = s.id
        JOIN
            sma_items_commission ic ON si.product_code = ic.item_code

                WHERE 
                    DATE(s.date) >= '" . trim($start_date) . "' 
                    AND DATE(s.date) <= '" . trim($end_date) . "'
                    AND s.warehouse_id = " . $warehouse . "
                    AND s.created_by = " . $pharmacist . "
                GROUP BY si.id
            ";

        $q = $this->db->query($sql);
        $data = array();
        //echo $this->db->last_query();
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
        }

        return $data;
    }
    
    public function getTransferItemsMonthlyWise($start_date, $end_date, $from_warehouse, $to_warehouse)
    {
          $sql = "SELECT 
                YEAR(date) AS year,
                MONTH(date) AS month,
                MONTHNAME(date) AS month_name,
                SUM(total_cost) AS total_cost,
                SUM(grand_total) AS total_sales,
                SUM(grand_total - total_cost)  AS total_profit,
                 CASE 
                    WHEN SUM(total_cost) > 0 THEN 
                        ROUND((SUM(grand_total - total_cost) / SUM(total_cost)) * 100, 2)
                    ELSE 
                        0
                END AS profit_percentage
            FROM 
                sma_transfers a
                WHERE 
                    DATE(a.date) >= '" . trim($start_date) . "' 
                    AND DATE(a.date) <= '" . trim($end_date) . "'
                    AND a.from_warehouse_id = " . $from_warehouse . "
                    AND a.to_warehouse_id = " . $to_warehouse . "
                 GROUP BY 
                YEAR(date),
                MONTH(date)
            ORDER BY
                year DESC,
                month DESC
            ";

        $q = $this->db->query($sql);
        $data = array();
        //echo $this->db->last_query();
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
        }

        return $data;
    }

    public function getTransferItemsDetailsMonthlyWise($year, $month, $start_date, $end_date, $from_warehouse, $to_warehouse)
    {
           $sql = "SELECT 
                id,
                a.date as transfer_date,
                grand_total AS total_sales
               
            FROM 
                sma_transfers a
                WHERE 
                    YEAR(a.date) = '".trim($year)."' 
                    AND MONTH(a.date) = '".trim($month)."' 
                    AND DATE(a.date) >= '" . trim($start_date) . "' 
                    AND DATE(a.date) <= '" . trim($end_date) . "'
                    AND a.from_warehouse_id = " . $from_warehouse . "
                    AND a.to_warehouse_id = " . $to_warehouse . "
            ORDER BY
               a.date
            ";

        $q = $this->db->query($sql);
        $data = array();
        //echo $this->db->last_query();
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
        }

        return $data;
    }
    

}
