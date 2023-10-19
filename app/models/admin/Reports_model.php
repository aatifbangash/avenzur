<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Reports_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
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


    public function getCustomerAging($duration)
    {
        $response = array();

        $results = $this->db
            ->select('companies.id, company, companies.ledger_account, COALESCE(sum(sma_accounts_entryitems.amount), 0) as total_amount, sma_accounts_entryitems.dc, sma_accounts_entries.date')
            ->from('companies')
            ->join('sma_accounts_entryitems', 'sma_accounts_entryitems.ledger_id=companies.ledger_account')
            ->join('sma_accounts_entries', 'sma_accounts_entries.id=sma_accounts_entryitems.entry_id')
            ->where('companies.group_name', 'customer')
            ->group_by('companies.id, sma_accounts_entryitems.dc')
            ->order_by('
                    CASE
                        WHEN sma_accounts_entries.date >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN 1
                        WHEN sma_accounts_entries.date >= DATE_SUB(NOW(), INTERVAL 60 DAY) THEN 2
                        WHEN sma_accounts_entries.date >= DATE_SUB(NOW(), INTERVAL 90 DAY) THEN 3
                        ELSE 4
                    END
                ')
            ->order_by('companies.company asc')
            ->get()
            ->result();

        $organizedResults = array();
        foreach ($results as $result) {
            $timeRange = $this->getTimeRange($result->date); // Define this function based on your needs
            $organizedResults[$result->company][$timeRange][] = $result;
        }

        return $organizedResults;
    }

    public function getSupplierAging($duration)
    {
        $response = array();

        $results = $this->db
            ->select('companies.id, company, companies.ledger_account, COALESCE(sum(sma_accounts_entryitems.amount), 0) as total_amount, sma_accounts_entryitems.dc, sma_accounts_entries.date')
            ->from('companies')
            ->join('sma_accounts_entryitems', 'sma_accounts_entryitems.ledger_id=companies.ledger_account')
            ->join('sma_accounts_entries', 'sma_accounts_entries.id=sma_accounts_entryitems.entry_id')
            ->where('companies.group_name', 'supplier')
            ->group_by('companies.id, sma_accounts_entryitems.dc')
            ->order_by('
                    CASE
                        WHEN sma_accounts_entries.date >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN 1
                        WHEN sma_accounts_entries.date >= DATE_SUB(NOW(), INTERVAL 60 DAY) THEN 2
                        WHEN sma_accounts_entries.date >= DATE_SUB(NOW(), INTERVAL 90 DAY) THEN 3
                        ELSE 4
                    END
                ')
            ->order_by('companies.company asc')
            ->get()
            ->result();

        $organizedResults = array();
        foreach ($results as $result) {
            $timeRange = $this->getTimeRange($result->date); // Define this function based on your needs
            $organizedResults[$result->company][$timeRange][] = $result;
        }

        return $organizedResults;
    }

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

        $this->db
            ->select('COALESCE(sum(sma_accounts_entryitems.amount), 0) as total_amount, sma_accounts_entryitems.dc')
            ->from('sma_accounts_entryitems')
            //->join('sma_accounts_entryitems', 'sma_accounts_entryitems.ledger_id=companies.ledger_account')
            ->join('sma_accounts_entries', 'sma_accounts_entries.id=sma_accounts_entryitems.entry_id')
//                ->where('sma_accounts_entryitems.ledger_id', $ledger_account)
            ->where('sma_accounts_entries.sid', $supplier_id)
            // need to join with purchase and suppliers( company)
            ->where('sma_accounts_entries.date <', $start_date)
            ->group_by('sma_accounts_entryitems.dc');
        $q = $this->db->get();

        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data_res[] = $row;
            }
        } else {
            $data_res = array();
        }

        $this->db
            ->select('sma_accounts_entryitems.entry_id, sma_accounts_entryitems.amount, sma_accounts_entryitems.dc, sma_accounts_entryitems.narration, sma_accounts_entries.transaction_type, sma_accounts_entries.date, sma_accounts_ledgers.code,(select sum(amount) from sma_accounts_entryitems ei inner join sma_accounts_entries e on e.id =ei.entry_id where e.date < `sma_accounts_entries`.`date` and e.sid = ' . $supplier_id . ') as openingAmount, companies.company')
            ->from('sma_accounts_entryitems')
            ->join('sma_accounts_entries', 'sma_accounts_entries.id=sma_accounts_entryitems.entry_id')
            ->join('sma_accounts_ledgers', 'sma_accounts_ledgers.id=sma_accounts_entryitems.ledger_id')
            ->join('companies', 'companies.ledger_account=sma_accounts_entryitems.ledger_id')
            ->where('sma_accounts_entries.sid', $supplier_id)
            ->where('sma_accounts_entries.date >=', $start_date)
            ->where('sma_accounts_entries.date <=', $end_date)
            ->order_by('sma_accounts_entries.date asc');

        $q = $this->db->get();
//        lq($this);
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

    public function getGeneralLedgerTrialBalance($start_date, $end_date)
    {
        $response = array();

        $this->db
            ->select('accounts_ledgers.id, accounts_ledgers.name, accounts_ledgers.notes, accounts_ledgers.code, COALESCE(sum(sma_accounts_entryitems.amount), 0) as total_amount, sma_accounts_entryitems.dc')
            ->from('accounts_ledgers')
            ->join('sma_accounts_entryitems', 'sma_accounts_entryitems.ledger_id=accounts_ledgers.id')
            ->join('sma_accounts_entries', 'sma_accounts_entries.id=sma_accounts_entryitems.entry_id')
            ->where('sma_accounts_entries.date >=', $start_date)
            ->where('sma_accounts_entries.date <=', $end_date)
            ->group_by('accounts_ledgers.id, sma_accounts_entryitems.dc')
            ->order_by('accounts_ledgers.name asc');
        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
        } else {
            $data = array();
        }

        $response['trs'] = $data;

        $this->db
            ->select('accounts_ledgers.id, accounts_ledgers.name, accounts_ledgers.notes, accounts_ledgers.code, COALESCE(sum(sma_accounts_entryitems.amount), 0) as total_amount, sma_accounts_entryitems.dc')
            ->from('accounts_ledgers')
            ->join('sma_accounts_entryitems', 'sma_accounts_entryitems.ledger_id=accounts_ledgers.id')
            ->join('sma_accounts_entries', 'sma_accounts_entries.id=sma_accounts_entryitems.entry_id')
            ->where('sma_accounts_entries.date <', $start_date)
            ->group_by('accounts_ledgers.id, sma_accounts_entryitems.dc')
            ->order_by('accounts_ledgers.name asc');

        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data2[] = $row;
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
        $this->db->select('id, code, name')
            ->like('name', $term, 'both')->or_like('code', $term, 'both');
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


    public function getItemOpeningBalance($productId, $start_date, $warehouseId = 0)
    {

        /*
        * SELECT AVG(purItem.net_unit_cost) AS purchaseUnitPrice FROM `sma_purchases` AS `purchase` 
        *  INNER JOIN `sma_purchase_items` AS `purItem` ON `purItem`.`purchase_id`=`purchase`.`id` WHERE `purItem`.`product_id`=$productId AND DATE(purchase.date) < '$start_date' AND `purchase`.`invoice_number` IS NOT NULL AND `purchase`.`grand_total`> 0
        */

        $q = $this->db->query("SELECT
        COALESCE(purchaseQuantity, 0) - COALESCE(saleQuantity, 0) - COALESCE(returnSupplierQuantity, 0) + COALESCE(returnQuantity, 0) + COALESCE(transferInQuantity, 0) - COALESCE(transferOutQuantity, 0) AS openingBalance,
        COALESCE(purchaseUnitPrice, 0) AS unitPrice
        FROM
        ( SELECT SUM(saleItem.quantity) AS saleQuantity FROM `sma_sales` AS `sale` 
            INNER JOIN `sma_sale_items` AS `saleItem` ON `saleItem`.`sale_id` = `sale`.`id`
            WHERE  `saleItem`.`product_id` = $productId AND DATE(sale.date) < '$start_date' AND `sale`.`sale_invoice`=1 ) AS sales, 
        ( SELECT SUM(purItem.quantity) AS purchaseQuantity FROM `sma_purchases` AS `purchase` 
          INNER JOIN `sma_purchase_items` AS `purItem` ON `purItem`.`purchase_id`=`purchase`.`id` 
          WHERE `purItem`.`product_id`=$productId AND DATE(purchase.date) < '$start_date' AND `purchase`.`invoice_number` IS NOT NULL AND `purchase`.`grand_total`> 0 ) AS purchases,
        ( SELECT SUM(abs(purItem.quantity)) AS returnSupplierQuantity FROM `sma_purchases` AS `purchase`
            INNER JOIN `sma_purchase_items` AS `purItem` ON  `purItem`.`purchase_id` = `purchase`.`id`
            WHERE `purItem`.`product_id` = $productId AND DATE(purchase.date) < '$start_date' AND `purchase`.`invoice_number` IS NOT NULL AND `purchase`.`grand_total` < 0 ) AS returnSupplier, 
        ( SELECT SUM(rtnItem.quantity) AS returnQuantity FROM `sma_returns` AS `rtn` 
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
                $response = $row;
            }
        }
        return $response;
    }

    public function getStockData($at_date, $warehouse, $supplier, $item_group, $item)
    {
        $totalPurchases = [];
        $finalResponse = [];

        if ($at_date) $at_date = $this->sma->fld($at_date);

        if ($supplier) $supplierJoin = " INNER JOIN sma_purchases pc ON pc.id = pi.purchase_id ";

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
            $totalPurchasesQuery .= "AND (p.code = '{$item}' OR p.name LIKE '%{$item}%') ";
        }

        $totalPurchasesQuery .= "GROUP BY p.code, p.name, pi.batchno
                                ORDER BY p.id DESC";

        $totalPurchseResultSet = $this->db->query($totalPurchasesQuery);
        
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
                $totalSalesQuery .= "AND (p.code = '{$item}' OR p.name LIKE '%{$item}%') ";
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
                            $purchase->quantity -= (int)$sale->quantity;
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
                $totalReturnSupplerQuery .= "AND (p.code = '{$item}' OR p.name LIKE '%{$item}%') ";
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
                            $purchase->quantity -= (int)abs($returnSupplier->quantity);
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
                $totalReturnSupplerQuery .= "AND (p.code = '{$item}' OR p.name LIKE '%{$item}%') ";
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
                            $purchase->quantity += (int)abs($returnCustomer->quantity);
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
                $totalTransferQuery .= "AND (p.code = '{$item}' OR p.name LIKE '%{$item}%') ";
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
                            $purchase->quantity = $purchase->quantity + (int)abs($transfer->quantity);
                        }else if(
                            $purchase->id == $transfer->id
                            && $purchase->item_code == $transfer->item_code
                            && $purchase->batch_no == $transfer->batch_no
                            && $warehouse == $transfer->from_warehouse_id
                        ){
                            $purchase->quantity = $purchase->quantity - (int)abs($transfer->quantity);
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
                $totalTransferQuery .= "AND (p.code = '{$item}' OR p.name LIKE '%{$item}%') ";
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
                            $purchase->quantity = $purchase->quantity + (int)abs($transfer->quantity);
                        }else if(
                            $purchase->id == $transfer->id
                            && $purchase->item_code == $transfer->item_code
                            && $purchase->batch_no == $transfer->batch_no
                            && $warehouse == $transfer->from_warehouse_id
                        ){
                            $purchase->quantity = $purchase->quantity - (int)abs($transfer->quantity);
                        }
                    }, $totalPurchases);
                }
            }
        }else{
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
                $totalTransferQuery .= "AND (p.code = '{$item}' OR p.name LIKE '%{$item}%') ";
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

    public function getItemMovementRecords($productId, $start_date, $end_date, $warehouseId, $filterOnType)
    {


        /* "purchases" => "Purchases",
            "sales" => "Sales",
            "returnCustomer"=>"Return-Customer",
            "returnSupplier"=>"Return-Supplier",
            "transfer" => "Transfer"
         */

        switch ($filterOnType) {

            case 'purchases':

                $q = $this->db->query("SELECT prd.id, prd.code, prd.name, data.entry_id, data.entry_date, data.type, data.document_no, data.name_of, data.batch_no, data.expiry_date, data.quantity, data.unit_cost, data.system_serial, 
                IFNULL(data.sale_price, prd.price) as sale_price, IFNULL(data.purchase_price, prd.cost) as purchase_price, data.product_id
                FROM sma_products as prd        
                LEFT JOIN ( 
                
                    SELECT purchase.id as entry_id, purchase.date as entry_date, 'Purchase' as type, purchase.reference_no as document_no, purchase.supplier as name_of, pitem.batchno as batch_no, 
                    pitem.expiry as expiry_date, pitem.quantity as quantity, pitem.net_unit_cost as unit_cost,
                    pitem.serial_number as system_serial, pitem.sale_price as sale_price, pitem.unit_cost as purchase_price, pitem.product_id as product_id

                    FROM sma_purchases as purchase

                    LEFT JOIN sma_purchase_items as pitem ON pitem.purchase_id = purchase.id

                    WHERE pitem.product_id = $productId AND DATE(purchase.date) >= '{$start_date}' AND DATE(purchase.date) <= '{$end_date}'  AND purchase.grand_total > 0 AND purchase.status = 'received'
                    
                )
                 as data ON data.product_id = prd.id 
                 WHERE prd.id = $productId AND data.product_id IS NOT NULL ORDER BY entry_date ");

                break;

            case 'sales':

                $q = $this->db->query("SELECT prd.id, prd.code, prd.name, data.entry_id, data.entry_date, data.type, data.document_no, data.name_of, data.batch_no, data.expiry_date, data.quantity, data.unit_cost, data.system_serial, 
                IFNULL(data.sale_price, prd.price) as sale_price, IFNULL(data.purchase_price, prd.cost) as purchase_price, data.product_id
                FROM sma_products as prd        
                LEFT JOIN ( 
                    
                    SELECT sale.id as entry_id, sale.date as entry_date, 'Sale' as type, sale.reference_no as document_no, 
                    
                    CASE WHEN sale.pos = 1 THEN 
                    CONCAT('POS',' - ',wrs.name)
                    ELSE
                    sale.customer
                    END AS name_of, 

                    saleItem.batch_no as batch_no,
                    saleItem.expiry as expiry_date, saleItem.quantity as quantity, saleItem.net_cost as unit_cost,
                    saleItem.serial_no as system_serial, NULL as sale_price, saleItem.net_cost as purchase_price, saleItem.product_id as product_id
                
                    FROM sma_sales as sale
                
                    LEFT JOIN sma_sale_items as saleItem ON saleItem.sale_id = sale.id
                    LEFT JOIN sma_warehouses as wrs ON wrs.id = sale.warehouse_id
                
                    WHERE saleItem.product_id = $productId AND DATE(sale.date) >= '{$start_date}' AND DATE(sale.date) <= '{$end_date}' AND sale.sale_status = 'completed' AND saleItem.batch_no <> ''
                )
                 AS data ON data.product_id = prd.id 
                 WHERE prd.id = $productId AND data.product_id IS NOT NULL ORDER BY entry_date");

                break;

            case 'returnCustomer':

                $q = $this->db->query("SELECT prd.id, prd.code, prd.name, data.entry_id, data.entry_date, data.type, data.document_no, data.name_of, data.batch_no, data.expiry_date, data.quantity, data.unit_cost, data.system_serial, 
                IFNULL(data.sale_price, prd.price) as sale_price, IFNULL(data.purchase_price, prd.cost) as purchase_price, data.product_id
                FROM sma_products as prd      
                LEFT JOIN ( 
                 
                    SELECT rtn.id as entry_id, rtn.date as entry_date, 'Return-Customer' as type, rtn.reference_no as document_no, rtn.customer as name_of, ritem.batch_no as batch_no, 
                    ritem.expiry as expiry_date, ritem.quantity as quantity, ritem.net_cost as unit_cost,
                    ritem.serial_no as system_serial, NULL as sale_price, ritem.net_cost as purchase_price, ritem.product_id as product_id

                    FROM sma_returns as rtn

                    LEFT JOIN sma_return_items as ritem ON ritem.return_id = rtn.id

                    WHERE ritem.product_id = $productId AND DATE(rtn.date) >= '$start_date' AND DATE(rtn.date) <= '$end_date' 

                )
                 AS data ON data.product_id = prd.id 
                 WHERE prd.id = $productId AND data.product_id IS NOT NULL ORDER BY entry_date");

                break;

            case 'returnSupplier':

                $q = $this->db->query("SELECT prd.id, prd.code, prd.name, data.entry_id, data.entry_date, data.type, data.document_no, data.name_of, data.batch_no, data.expiry_date, data.quantity, data.unit_cost, data.system_serial, 
                IFNULL(data.sale_price, prd.price) as sale_price, IFNULL(data.purchase_price, prd.cost) as purchase_price, data.product_id
                FROM sma_products as prd       
                LEFT JOIN ( 

                    SELECT rtn.id as entry_id, rtn.date as entry_date, 'Return-Supplier' as type, rtn.reference_no as document_no, rtn.supplier as name_of, ritem.batch_no, 
                    ritem.expiry as expiry_date, ritem.quantity as quantity, ritem.net_cost as unit_cost,
                    ritem.serial_number as system_serial, NULL as sale_price, ritem.net_cost as purchase_price, ritem.product_id as product_id

                    FROM sma_returns_supplier as rtn

                    LEFT JOIN sma_return_supplier_items as ritem ON ritem.return_id = rtn.id

                    WHERE ritem.product_id = $productId AND DATE(rtn.date) >= '$start_date' AND DATE(rtn.date) <= '$end_date'

                )
                AS data ON data.product_id = prd.id 
                WHERE prd.id = $productId AND data.product_id IS NOT NULL ORDER BY entry_date");


                break;

            case 'transfer':

                $q = $this->db->query("SELECT prd.id, prd.code, prd.name, data.entry_id, data.entry_date, data.type, data.document_no, data.name_of, data.batch_no, data.expiry_date, data.quantity, data.unit_cost, data.system_serial, 
                IFNULL(data.sale_price, prd.price) as sale_price, IFNULL(data.purchase_price, prd.cost) as purchase_price, data.product_id
                FROM sma_products as prd
                LEFT JOIN ( 
                
                    SELECT trnf.id as entry_id, trnf.date as entry_date, 'Transfer-In' as type,  trnf.transfer_no as document_no, CONCAT('Transfer from ',trnf.from_warehouse_name,' - to ',trnf.to_warehouse_name) as name_of, titm.batchno as batch_no, 
                    titm.expiry as expiry_date, titm.quantity as quantity, titm.net_unit_cost as unit_cost,
                    titm.serial_number as system_serial, NULL as sale_price, NULL as purchase_price, titm.product_id

                    FROM sma_transfers as trnf
                    
                    LEFT JOIN (

                        SELECT transfer_id, 
                                batchno, expiry, quantity, net_unit_cost, product_id, serial_number
                        
                        FROM (

                            SELECT transfer_id, 
                                batchno, expiry, quantity, net_unit_cost, product_id, serial_number
                        FROM sma_transfer_items 
                        WHERE  `product_id` = '$productId' 
                        AND warehouse_id = $warehouseId
                        AND DATE(`date`) >= '$start_date' AND DATE(`date`) <= '$end_date'
                        GROUP BY transfer_id

                        UNION ALL


                        SELECT transfer_id, 
                                    batchno, expiry, quantity, net_unit_cost, product_id, serial_number
                            FROM sma_purchase_items 
                            WHERE  `product_id` = '$productId' 
                            AND warehouse_id = $warehouseId
                            AND DATE(`date`) >= '$start_date' AND DATE(`date`) <= '$end_date'
                            AND transfer_id IS NOT NULL
                            GROUP BY transfer_id


                                ) AS combined_transfer_in

                        ) AS titm 
                        ON titm.transfer_id = trnf.id 

                        WHERE  DATE(trnf.date) >= '$start_date' AND DATE(trnf.date) <= '$end_date' AND titm.product_id = $productId


                    UNION ALL

                    
                    SELECT trnf.id as entry_id, trnf.date as entry_date, 'Transfer-Out' as type,  trnf.transfer_no as document_no, CONCAT('Transfer from ',trnf.from_warehouse_name,' - to ',trnf.to_warehouse_name) as name_of, titm.batchno as batch_no, 
                    titm.expiry as expiry_date, titm.quantity as quantity, titm.net_unit_cost as unit_cost,
                    titm.serial_number as system_serial, NULL as sale_price, NULL as purchase_price, titm.product_id

                    FROM sma_transfers as trnf

                    LEFT JOIN (

                        SELECT transfer_id, 
                                batchno, expiry, quantity, net_unit_cost, product_id, warehouse_id, serial_number
                        
                        FROM (

                            SELECT transfer_id, 
                                batchno, expiry, quantity, net_unit_cost, product_id, warehouse_id, serial_number
                        FROM sma_transfer_items 
                        WHERE  `product_id` = '$productId' 
                        AND DATE(`date`) >= '$start_date' AND DATE(`date`) <= '$end_date'
                        GROUP BY transfer_id

                        UNION ALL

                        SELECT transfer_id, 
                                        batchno, expiry, quantity, net_unit_cost, product_id, warehouse_id, serial_number
                                FROM sma_purchase_items 
                                WHERE  `product_id` = '$productId' 
                                AND DATE(`date`) >= '$start_date' AND DATE(`date`) <= '$end_date'
                                AND transfer_id IS NOT NULL
                                GROUP BY transfer_id


                            ) AS combained

                    ) AS titm 
                    ON titm.transfer_id = trnf.id
                    WHERE  DATE(trnf.date) >= '$start_date' AND DATE(trnf.date) <= '$end_date'  AND trnf.from_warehouse_id = $warehouseId AND titm.product_id = $productId


                )
                AS data ON data.product_id = prd.id 
                WHERE prd.id = $productId  AND data.product_id IS NOT NULL ORDER BY entry_date");


                break;

            default;

                $q = $this->db->query("SELECT prd.id, prd.code, prd.name, data.entry_id, data.entry_date, data.type, data.document_no, data.name_of, data.batch_no, data.expiry_date, data.quantity, data.unit_cost, data.system_serial, 
                CASE
                    WHEN data.sale_price IS NULL OR data.sale_price = 0 THEN prd.price
                    ELSE data.sale_price
                END AS sale_price, IFNULL(data.purchase_price, prd.cost) as purchase_price, data.product_id
                FROM sma_products as prd        
                LEFT JOIN ( 
            
                    SELECT purchase.id as entry_id, purchase.date as entry_date, 'Purchase' as type, purchase.reference_no as document_no, purchase.supplier as name_of, pitem.batchno as batch_no, 
                    pitem.expiry as expiry_date, pitem.quantity as quantity, pitem.net_unit_cost as unit_cost,
                    pitem.serial_number as system_serial, pitem.sale_price as sale_price, pitem.unit_cost as purchase_price, pitem.product_id

                    FROM sma_purchases as purchase

                    LEFT JOIN sma_purchase_items as pitem ON pitem.purchase_id = purchase.id

                    WHERE pitem.product_id = $productId AND DATE(purchase.date) >= '$start_date' AND DATE(purchase.date) <= '$end_date'  AND purchase.grand_total > 0 AND purchase.status = 'received'

                    UNION ALL 

                    SELECT sale.id as entry_id, sale.date as entry_date, 'Sale' as type, sale.reference_no as document_no, 
                    
                    CASE WHEN sale.pos = 1 THEN 
                    CONCAT('POS',' - ',wrs.name)
                    ELSE
                    sale.customer
                    END AS name_of, 
                    
                     saleItem.batch_no as batch_no,
                    saleItem.expiry as expiry_date, saleItem.quantity as quantity, saleItem.net_cost as unit_cost,
                    saleItem.serial_no as system_serial, NULL as sale_price, saleItem.net_cost as purchase_price, saleItem.product_id
                
                    FROM sma_sales as sale
                
                    LEFT JOIN sma_sale_items as saleItem ON saleItem.sale_id = sale.id
                    LEFT JOIN sma_warehouses as wrs ON wrs.id = sale.warehouse_id
                
                    WHERE saleItem.product_id = $productId AND DATE(sale.date) >= '$start_date' AND DATE(sale.date) <= '$end_date' AND sale.sale_status = 'completed' AND saleItem.batch_no <> ''

                    UNION ALL 

                    SELECT rtn.id as entry_id, rtn.date as entry_date, 'Return-Customer' as type, rtn.reference_no as document_no, rtn.customer as name_of, ritem.batch_no as batch_no, 
                    ritem.expiry as expiry_date, ritem.quantity as quantity, ritem.net_cost as unit_cost,
                    ritem.serial_no as system_serial, NULL as sale_price, ritem.net_cost as purchase_price, ritem.product_id

                    FROM sma_returns as rtn

                    LEFT JOIN sma_return_items as ritem ON ritem.return_id = rtn.id

                    WHERE ritem.product_id = $productId AND DATE(rtn.date) >= '$start_date' AND DATE(rtn.date) <= '$end_date' 

                    UNION ALL 

                    SELECT rtn.id as entry_id, rtn.date as entry_date, 'Return-Supplier' as type, rtn.reference_no as document_no, rtn.supplier as name_of, ritem.batchno as batch_no, 
                    ritem.expiry as expiry_date, ritem.quantity as quantity, ritem.net_cost as unit_cost,
                    ritem.serial_number as system_serial, NULL as sale_price, ritem.net_cost as purchase_price, ritem.product_id

                    FROM sma_returns_supplier as rtn

                    LEFT JOIN sma_return_supplier_items as ritem ON ritem.return_id = rtn.id

                    WHERE ritem.product_id = $productId AND DATE(rtn.date) >= '$start_date' AND DATE(rtn.date) <= '$end_date'

                    UNION ALL 


                    SELECT trnf.id as entry_id, trnf.date as entry_date, 'Transfer-In' as type,  trnf.transfer_no as document_no, CONCAT('Transfer from ',trnf.from_warehouse_name,' - to ',trnf.to_warehouse_name) as name_of, titm.batchno as batch_no, 
                    titm.expiry as expiry_date, titm.quantity as quantity, titm.net_unit_cost as unit_cost,
                    titm.serial_number as system_serial, NULL as sale_price, NULL as purchase_price, titm.product_id

                    FROM sma_transfers as trnf
                    
                    LEFT JOIN (

                        SELECT transfer_id, 
                                batchno, expiry, quantity, net_unit_cost, product_id, serial_number
                        
                        FROM (

                            SELECT transfer_id, 
                                batchno, expiry, quantity, net_unit_cost, product_id, serial_number
                        FROM sma_transfer_items 
                        WHERE  `product_id` = '$productId' 
                        AND warehouse_id = $warehouseId
                        AND DATE(`date`) >= '$start_date' AND DATE(`date`) <= '$end_date'
                        GROUP BY transfer_id

                        UNION ALL


                        SELECT transfer_id, 
                                    batchno, expiry, quantity, net_unit_cost, product_id, serial_number
                            FROM sma_purchase_items 
                            WHERE  `product_id` = '$productId' 
                            AND warehouse_id = $warehouseId
                            AND DATE(`date`) >= '$start_date' AND DATE(`date`) <= '$end_date'
                            AND transfer_id IS NOT NULL
                            GROUP BY transfer_id


                                ) AS combined_transfer_in

                        ) AS titm 
                        ON titm.transfer_id = trnf.id 

                        WHERE  DATE(trnf.date) >= '$start_date' AND DATE(trnf.date) <= '$end_date' AND titm.product_id = $productId


                    UNION ALL

                    
                    SELECT trnf.id as entry_id, trnf.date as entry_date, 'Transfer-Out' as type,  trnf.transfer_no as document_no, CONCAT('Transfer from ',trnf.from_warehouse_name,' - to ',trnf.to_warehouse_name) as name_of, titm.batchno as batch_no, 
                    titm.expiry as expiry_date, titm.quantity as quantity, titm.net_unit_cost as unit_cost,
                    titm.serial_number as system_serial, NULL as sale_price, NULL as purchase_price, titm.product_id

                    FROM sma_transfers as trnf

                    LEFT JOIN (

                        SELECT transfer_id, 
                                batchno, expiry, quantity, net_unit_cost, product_id, warehouse_id, serial_number
                        
                        FROM (

                            SELECT transfer_id, 
                                batchno, expiry, quantity, net_unit_cost, product_id, warehouse_id, serial_number
                        FROM sma_transfer_items 
                        WHERE  `product_id` = '$productId' 
                        AND DATE(`date`) >= '$start_date' AND DATE(`date`) <= '$end_date'
                        GROUP BY transfer_id

                        UNION ALL

                        SELECT transfer_id, 
                                        batchno, expiry, quantity, net_unit_cost, product_id, warehouse_id, serial_number
                                FROM sma_purchase_items 
                                WHERE  `product_id` = '$productId' 
                                AND DATE(`date`) >= '$start_date' AND DATE(`date`) <= '$end_date'
                                AND transfer_id IS NOT NULL
                                GROUP BY transfer_id


                            ) AS combained

                    ) AS titm 
                    ON titm.transfer_id = trnf.id
                    WHERE  DATE(trnf.date) >= '$start_date' AND DATE(trnf.date) <= '$end_date'  AND trnf.from_warehouse_id = $warehouseId AND titm.product_id = $productId
                
                )
                AS data ON data.product_id = prd.id 
                WHERE prd.id = $productId AND data.product_id IS NOT NULL ORDER BY entry_date");

        }

        $response = array();
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $response[] = $row;
            }
        }
        return $response;

    }

    public function getProductsQuantityUnitCost($start_date,$from_warehouse_id){
        $qry = $this->db->query("SELECT
            product_id,
            SUM(totalPurchaseQuantity) AS total_in_quantity,
            AVG(purchaseUnitPrice) AS avg_unit_cost,
            SUM(totalSaleQuantity) AS total_out_quantity,
            AVG(saleUnitPrice) AS avgSaleUnitPrice
            FROM
            (
    
            SELECT
            purItem.product_id AS product_id,
            SUM(purItem.quantity) AS totalPurchaseQuantity,
            IFNULL(AVG(purItem.net_unit_cost), p.cost) AS purchaseUnitPrice,
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
            GROUP BY
            purItem.product_id
    
            UNION ALL
    
            SELECT 
            rtnItem.product_id AS product_id,
            SUM(rtnItem.quantity) AS totalPurchaseQuantity,
            NULL AS purchaseUnitPrice,
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
            NULL AS saleUnitPrice
            FROM
            `sma_sales` AS `sale`
            INNER JOIN
            `sma_sale_items` AS `saleItem` ON `saleItem`.`sale_id` = `sale`.`id`
            WHERE
            DATE(sale.date) < '$start_date'
            GROUP BY
            saleItem.product_id
    
            UNION ALL
    
            SELECT
            purItem.product_id AS product_id,
            0 AS purchaseUnitPrice,
            NULL AS purchaseUnitPrice,
            SUM(ABS(purItem.quantity)) AS totalSaleQuantity,
            NULL AS saleUnitPrice
            FROM
            `sma_purchases` AS `purchase`
            INNER JOIN
            `sma_purchase_items` AS `purItem` ON `purItem`.`purchase_id` = `purchase`.`id`
            WHERE
            DATE(purchase.date) < '$start_date'
            AND `purchase`.`grand_total` < 0
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
                $resultSet[$row->product_id] = ["total_opening_qty"=>$row->total_in_quantity - $row->total_out_quantity, "avg_unit_cost"=>$row->avg_unit_cost, 'all_data'=>$row];
            }
        }
        // echo $this->db->last_query();
        // echo '<pre>', print_r($resultSet), '</pre>';
        return $resultSet;
    }

    public function getInventoryTrialBalanceData($start_date, $end_date, $from_warehouse_id = 0, $to_warehouse_id = 0){

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
        
        SELECT product_id, SUM(movement_out_quantity) AS movement_out_quantity, AVG(movement_out_cost) AS movement_out_cost, total_movement_out_cost
        
        FROM (
        
            SELECT product_id,
                    SUM(si.quantity) AS movement_out_quantity,
                    AVG(si.net_unit_price) AS movement_out_cost,
                    SUM(si.quantity * si.net_unit_price) AS total_movement_out_cost
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
                    PI.product_id,
                    SUM(abs(PI.quantity)) AS movement_out_quantity,
                    AVG(PI.net_unit_cost) AS movement_out_cost,
                    0 AS total_movement_out_cost
                FROM
                    sma_purchase_items PI
                LEFT JOIN sma_purchases AS p
                ON
                    p.id = PI.purchase_id
                WHERE
                    DATE(p.date) BETWEEN '$start_date' AND '$end_date'  AND p.grand_total < 0
                GROUP BY
                    PI.product_id
        
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

        $resultSet = array();
        if ($qry->num_rows() > 0) {
            foreach (($qry->result()) as $row) {
                //echo '<pre>';
                //print_r($row);
                $row->movement_in_cost = ($row->total_movement_in_cost / $row->movement_in_quantity);
                $resultSet[$row->product_id] = $row;
            }
        }
        //  echo $this->db->last_query();
        //echo '<pre>', print_r($resultSet), '</pre>';exit;
        return $resultSet;

    }

    public function getInventoryTrialBalance($start_date, $end_date, $from_warehouse_id = 0, $to_warehouse_id = 0)
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
            ->where('sma_sales.date >=', $start_date)
            ->where('sma_sales.date <=', $end_date);
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
            ->where('sma_returns.date >=', $start_date)
            ->where('sma_returns.date <=', $end_date);
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
            $notFoundObject = (object)[
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
            ->where('sma_returns.date >=', $start_date)
            ->where('sma_returns.date <=', $end_date)
            ->group_by('sma_return_items.product_id');

        $q = $this->db->get();

        if ($q->num_rows() > 0) {
            return $q->row(); // Return the single row
        } else {
            $notFoundObject = (object)[
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
            $notFoundObject = (object)[
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
            ->where('sma_sales.date >=', $start_date)
            ->where('sma_sales.date <=', $end_date)
            //->where('sma_purchases.return_id IS NULL')
            ->group_by('sma_sale_items.product_id');

        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            return $q->row(); // Return the single row
        } else {
            $notFoundObject = (object)[
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
            $notFoundObject = (object)[
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
            ->where('sma_sales.date <', $start_date)
            //->where('sma_sales.date <=', $end_date)
            //->where('sma_purchases.return_id IS NULL')
            ->group_by('sma_sale_items.product_id');

        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            return $q->row(); // Return the single row
        } else {
            $notFoundObject = (object)[
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
            ->where('sma_returns.date <', $start_date)
            ->group_by('sma_return_items.product_id');

        $q = $this->db->get();

        if ($q->num_rows() > 0) {
            return $q->row(); // Return the single row
        } else {
            $notFoundObject = (object)[
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
            ->select('sma_purchases.id as purchase_id, SUM(sma_purchase_items.quantity) as total_quantity, sma_purchases.sequence_code as purchase_sequence_code,sma_accounts_entries.id as transaction_id, sma_purchases.supplier, sma_accounts_entries.date, sma_purchases.invoice_number, sma_accounts_entries.number, sma_purchases.grand_total as total_with_vat, SUM(sma_accounts_entryitems.amount) as total_tax, sma_companies.vat_no, sma_companies.sequence_code as supplier_code')
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
                                    p.id as trans_ID,  
                                    CASE WHEN p.status = 'returned' THEN 'returnSupplier' ELSE 'purchases' END AS trans_type,
                                    w.name as warehouse,
                                    p.date as trans_date,
                                    p.invoice_number as trans_invoice_number,
                                    SUM(pi.quantity) AS total_quantity,
                                    p.warehouse_id,
                                    p.reference_no,
                                
                                    p.supplier AS supplier_name,
                                    c.vat_no AS supplier_vat_no,    
                                    
                                    abs(p.total_discount) as total_discount,
                                    abs(p.grand_total) AS grand_total,
                                    abs(p.product_tax) AS total_tax,
                                    abs(withT.subtotal) AS total_item_with_vat,
                                    abs(withOutT.subtotal) AS total_item_without_tax, 
                                
                                    ae.number AS ledger_entry_number
                                FROM sma_purchases as p
                                JOIN sma_companies as c ON c.id = p.supplier_id
                                JOIN sma_purchase_items as pi ON pi.purchase_id = p.id
                                LEFT JOIN sma_warehouses AS w on p.warehouse_id=w.id
                                LEFT JOIN sma_accounts_entries as ae ON ae.pid = p.id
                                LEFT JOIN(
                                    SELECT
                                        purchase_id,
                                        SUM(subtotal) AS subtotal
                                    FROM
                                        sma_purchase_items
                                    WHERE
                                        tax > 0
                                    GROUP BY
                                        purchase_id
                                ) withT ON withT.purchase_id = p.id
                                
                                LEFT JOIN(
                                    SELECT
                                        purchase_id,
                                        SUM(subtotal) AS subtotal
                                    FROM
                                        sma_purchase_items
                                    WHERE
                                        tax = 0
                                    GROUP BY
                                        purchase_id
                                ) withOutT ON withOutT.purchase_id = p.id
                                
                                GROUP BY
                                    pi.purchase_id
                                    
                                    UNION ALL

                                    SELECT 
                                        m.id as trans_ID,  
                                        'serviceInvoice' as trans_type,   
                                        '-' as warehouse, 
                                        m.date as trans_date, 
                                       
                                        
                                        m.reference_no as trans_invoice_number,
                                        0 as total_quantity,
                                        0  as warehouse_id,

                                        m.reference_no,

                                        c.company AS supplier_name,
                                        c.vat_no AS supplier_vat_no,  
                                       
                                        
                                        0 as total_discount,
                                        m.payment_amount AS grand_total,
                                        m.bank_charges AS total_tax,
                                        0 AS total_item_with_vat,
                                        0 AS total_item_without_tax,
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
        //echo $query;

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
                                
                                
                                s.total_discount as total_discount,
                                s.grand_total AS grand_total,
                                s.product_tax AS total_tax,
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
                                
                                
                                r.total_discount as total_discount,
                                r.grand_total AS grand_total,
                                r.product_tax AS total_tax,
                                withT.subtotal AS total_item_with_vat,
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
                                
                                
                                0 as total_discount,
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


}
