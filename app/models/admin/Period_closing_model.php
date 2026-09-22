<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Period closing by module (AR, AP, Inventory, Finance) and calendar month.
 * Step 1: admin control UI + persistence. Module enforcement comes later.
 */
class Period_closing_model extends CI_Model
{
    public const MODULES = [
        'ar'        => 'Accounts Receivable',
        'ap'        => 'Accounts Payable',
        'inventory' => 'Inventory',
        'finance'   => 'Finance',
    ];

    public function __construct()
    {
        parent::__construct();
        $this->ensure_table();
    }

    public function ensure_table()
    {
        if ($this->db->table_exists('period_closures')) {
            return true;
        }

        $table = $this->db->dbprefix('period_closures');
        $this->db->query("CREATE TABLE IF NOT EXISTS `{$table}` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `module` enum('ar','ap','inventory','finance') NOT NULL,
            `period_year` smallint(4) NOT NULL,
            `period_month` tinyint(2) NOT NULL COMMENT '1-12',
            `status` enum('open','closed') NOT NULL DEFAULT 'closed',
            `closed_by` int(11) DEFAULT NULL,
            `closed_at` datetime DEFAULT NULL,
            `reopened_by` int(11) DEFAULT NULL,
            `reopened_at` datetime DEFAULT NULL,
            `note` varchar(255) DEFAULT NULL,
            `created_at` datetime DEFAULT NULL,
            `updated_at` datetime DEFAULT NULL,
            PRIMARY KEY (`id`),
            UNIQUE KEY `uq_module_period` (`module`,`period_year`,`period_month`),
            KEY `idx_period` (`period_year`,`period_month`),
            KEY `idx_module_status` (`module`,`status`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8");

        return $this->db->table_exists('period_closures');
    }

    /**
     * @return array keyed by "module-month" => row object
     */
    public function getClosuresForYear($year)
    {
        $year = (int) $year;
        $q = $this->db->get_where('period_closures', ['period_year' => $year]);
        $map = [];
        if ($q && $q->num_rows() > 0) {
            foreach ($q->result() as $row) {
                $map[$row->module . '-' . (int) $row->period_month] = $row;
            }
        }
        return $map;
    }

    public function getClosure($module, $year, $month)
    {
        if (!$this->isValidModule($module)) {
            return null;
        }
        $q = $this->db->get_where('period_closures', [
            'module'       => $module,
            'period_year'  => (int) $year,
            'period_month' => (int) $month,
        ], 1);
        return ($q && $q->num_rows() > 0) ? $q->row() : null;
    }

    /**
     * True when module+month is closed. No row = open.
     * Ready for later module enforcement.
     */
    public function isPeriodClosed($module, $date)
    {
        if (!$this->isValidModule($module) || empty($date)) {
            return false;
        }
        $ts = is_numeric($date) ? (int) $date : strtotime($date);
        if (!$ts) {
            return false;
        }
        $year  = (int) date('Y', $ts);
        $month = (int) date('n', $ts);
        $row   = $this->getClosure($module, $year, $month);
        return $row && ($row->status === 'closed');
    }

    public function closePeriod($module, $year, $month, $user_id, $note = null)
    {
        if (!$this->isValidModule($module)) {
            return false;
        }
        $year  = (int) $year;
        $month = (int) $month;
        if ($month < 1 || $month > 12 || $year < 2000) {
            return false;
        }

        $now = date('Y-m-d H:i:s');
        $existing = $this->getClosure($module, $year, $month);

        if ($existing) {
            return $this->db->update('period_closures', [
                'status'       => 'closed',
                'closed_by'    => (int) $user_id,
                'closed_at'    => $now,
                'note'         => $note,
                'updated_at'   => $now,
            ], ['id' => (int) $existing->id]);
        }

        return $this->db->insert('period_closures', [
            'module'       => $module,
            'period_year'  => $year,
            'period_month' => $month,
            'status'       => 'closed',
            'closed_by'    => (int) $user_id,
            'closed_at'    => $now,
            'note'         => $note,
            'created_at'   => $now,
            'updated_at'   => $now,
        ]);
    }

    public function reopenPeriod($module, $year, $month, $user_id, $note = null)
    {
        if (!$this->isValidModule($module)) {
            return false;
        }
        $existing = $this->getClosure($module, (int) $year, (int) $month);
        if (!$existing || $existing->status !== 'closed') {
            return false;
        }

        $now = date('Y-m-d H:i:s');
        return $this->db->update('period_closures', [
            'status'       => 'open',
            'reopened_by'  => (int) $user_id,
            'reopened_at'  => $now,
            'note'         => $note,
            'updated_at'   => $now,
        ], ['id' => (int) $existing->id]);
    }

    public function isValidModule($module)
    {
        return isset(self::MODULES[$module]);
    }

    /**
     * Human-readable closed-period message for flash / UI.
     */
    public function closedMessage($module, $date)
    {
        $ts = is_numeric($date) ? (int) $date : strtotime($date);
        if (!$ts) {
            return 'This period is closed for ' . (self::MODULES[$module] ?? $module) . '.';
        }
        $label = self::MODULES[$module] ?? $module;
        return sprintf(
            '%s is closed for %s %d. Add, edit and delete are not allowed.',
            $label,
            date('F', $ts),
            (int) date('Y', $ts)
        );
    }

    /**
     * Apply open/closed period filter on a date column (Query Builder).
     * $status: 'closed' | 'open' | empty
     */
    public function applyPeriodStatusFilter($status, $date_column = 'date', $module = 'finance')
    {
        if ($status !== 'closed' && $status !== 'open') {
            return;
        }
        if (!$this->isValidModule($module)) {
            return;
        }

        $pc = $this->db->dbprefix('period_closures');
        $exists = "EXISTS (
            SELECT 1 FROM `{$pc}` pc
            WHERE pc.module = " . $this->db->escape($module) . "
              AND pc.status = 'closed'
              AND pc.period_year = YEAR({$date_column})
              AND pc.period_month = MONTH({$date_column})
        )";

        if ($status === 'closed') {
            $this->db->where($exists, null, false);
        } else {
            $this->db->where('NOT ' . $exists, null, false);
        }
    }
}
