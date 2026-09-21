<?php
/**
 * Budget Model
 * 
 * Handles all budget-related data operations:
 * - Budget allocations
 * - Budget tracking (actual vs budget)
 * - Forecasting
 * - Alert management
 * - Audit trails
 * 
 * Date: 2025-10-25
 */

defined('BASEPATH') OR exit('No direct script access allowed');

class Budget_model extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    // =====================================================================
    // BUDGET ALLOCATION FUNCTIONS
    // =====================================================================

    /**
     * Create new budget allocation
     * 
     * @param array $data Allocation data
     * @param int $user_id User creating allocation
     * @return int|false allocation_id or false
     */
    public function create_allocation($data, $user_id) {
        $allocation = [
            'hierarchy_level' => $data['hierarchy_level'],
            'parent_hierarchy' => $data['parent_hierarchy'] ?? null,
            'parent_warehouse_id' => $data['parent_warehouse_id'] ?? null,
            'parent_name' => $data['parent_name'] ?? null,
            'child_hierarchy' => $data['child_hierarchy'] ?? null,
            'child_warehouse_id' => $data['child_warehouse_id'] ?? null,
            'child_name' => $data['child_name'] ?? null,
            'period' => $data['period'],
            'allocated_amount' => $data['allocated_amount'],
            'allocation_method' => $data['allocation_method'] ?? 'custom',
            'pharmacy_id' => $data['pharmacy_id'] ?? null,
            'branch_id' => $data['branch_id'] ?? null,
            'allocated_by_user_id' => $user_id,
            'allocated_by_user_name' => $this->get_user_name($user_id),
            'notes' => $data['notes'] ?? null
        ];

        if (!$this->db->insert('sma_budget_allocation', $allocation)) {
            log_message('error', 'Budget allocation insert failed: ' . $this->db->error()['message']);
            return false;
        }

        $allocation_id = $this->db->insert_id();
        
        // Audit trail
        $this->log_audit($allocation_id, 'created', null, $allocation, $user_id, 'Budget allocation created');

        return $allocation_id;
    }

    /**
     * Update budget allocation
     * 
     * @param int $allocation_id
     * @param array $data
     * @param int $user_id
     * @return bool
     */
    public function update_allocation($allocation_id, $data, $user_id) {
        $old_data = $this->db->get_where('sma_budget_allocation', ['allocation_id' => $allocation_id])->row_array();
        
        $update = array_intersect_key($data, array_flip([
            'allocated_amount',
            'allocation_method',
            'notes',
            'is_active'
        ]));
        $update['updated_by_user_id'] = $user_id;

        if (!$this->db->where('allocation_id', $allocation_id)->update('sma_budget_allocation', $update)) {
            return false;
        }

        // Audit trail
        $this->log_audit($allocation_id, 'updated', $old_data, $update, $user_id, 'Budget allocation updated');

        // Recalculate tracking & forecast
        $this->calculate_tracking($allocation_id);
        $this->calculate_forecast($allocation_id);

        return true;
    }

    /**
     * Get allocation by ID
     * 
     * @param int $allocation_id
     * @return array|null
     */
    public function get_allocation($allocation_id) {
        $query = "
            SELECT ba.*, 
                   bt.actual_spent,
                   bt.remaining_amount,
                   bt.percentage_used,
                   bt.status as tracking_status,
                   bf.burn_rate_daily,
                   bf.projected_end_of_month,
                   bf.risk_level
            FROM sma_budget_allocation ba
            LEFT JOIN sma_budget_tracking bt ON ba.allocation_id = bt.allocation_id
            LEFT JOIN sma_budget_forecast bf ON ba.allocation_id = bf.allocation_id
            WHERE ba.allocation_id = ?
        ";
        
        $result = $this->db->query($query, [$allocation_id]);
        return $result->row_array();
    }

    /**
     * Get all allocations for entity
     * 
     * @param int $warehouse_id
     * @param string $period YYYY-MM
     * @param string $as_parent|child (optional)
     * @return array
     */
    public function get_allocations_for_entity($warehouse_id, $period = null, $role = null) {
        if (!$period) $period = date('Y-m');

        $query = "
            SELECT ba.*, 
                   bt.actual_spent,
                   bt.percentage_used,
                   bt.status as tracking_status
            FROM sma_budget_allocation ba
            LEFT JOIN sma_budget_tracking bt ON ba.allocation_id = bt.allocation_id
            WHERE ba.period = ?
            AND (ba.parent_warehouse_id = ? OR ba.child_warehouse_id = ?)
            AND ba.is_active = 1
            ORDER BY ba.allocated_at DESC
        ";

        $result = $this->db->query($query, [$period, $warehouse_id, $warehouse_id]);
        return $result->result_array();
    }

    /**
     * Get all allocations for period
     * 
     * @param string $period YYYY-MM
     * @param string $hierarchy_level (optional)
     * @return array
     */
    public function get_allocations_by_period($period, $hierarchy_level = null) {
        $query = "
            SELECT ba.*, 
                   bt.actual_spent,
                   bt.percentage_used,
                   bt.status as tracking_status,
                   bf.risk_level
            FROM sma_budget_allocation ba
            LEFT JOIN sma_budget_tracking bt ON ba.allocation_id = bt.allocation_id
            LEFT JOIN sma_budget_forecast bf ON ba.allocation_id = bf.allocation_id
            WHERE ba.period = ?
            AND ba.is_active = 1
        ";

        $params = [$period];

        if ($hierarchy_level) {
            $query .= " AND ba.hierarchy_level = ?";
            $params[] = $hierarchy_level;
        }

        $query .= " ORDER BY ba.allocated_at DESC";

        $result = $this->db->query($query, $params);
        return $result->result_array();
    }

    // =====================================================================
    // BUDGET TRACKING FUNCTIONS
    // =====================================================================

    /**
     * Calculate budget tracking (actual vs budget)
     * 
     * @param int $allocation_id
     * @return bool
     */
    public function calculate_tracking($allocation_id) {
        $allocation = $this->get_allocation($allocation_id);
        if (!$allocation) return false;

        // Get actual spent from fact_cost_center
        $actual_query = "
            SELECT COALESCE(SUM(total_cost), 0) as actual_spent
            FROM sma_fact_cost_center
            WHERE warehouse_id IN (
                SELECT warehouse_id FROM sma_dim_branch 
                WHERE pharmacy_id = ? AND MONTH(created_at) = ? AND YEAR(created_at) = ?
            )
            OR warehouse_id = ? 
        ";

        // Parse period
        list($year, $month) = explode('-', $allocation['period']);

        $actual_result = $this->db->query($actual_query, [
            $allocation['pharmacy_id'],
            $month,
            $year,
            $allocation['child_warehouse_id']
        ])->row_array();

        $actual_spent = $actual_result['actual_spent'] ?? 0;
        $allocated = $allocation['allocated_amount'];
        $percentage = ($allocated > 0) ? ($actual_spent / $allocated) * 100 : 0;

        // Determine status
        if ($percentage <= 50) {
            $status = 'safe';
        } elseif ($percentage <= 80) {
            $status = 'warning';
        } elseif ($percentage < 100) {
            $status = 'danger';
        } else {
            $status = 'exceeded';
        }

        // Update or insert tracking
        $tracking = [
            'allocation_id' => $allocation_id,
            'hierarchy_level' => $allocation['hierarchy_level'],
            'warehouse_id' => $allocation['child_warehouse_id'] ?? $allocation['parent_warehouse_id'],
            'entity_name' => $allocation['child_name'] ?? $allocation['parent_name'],
            'period' => $allocation['period'],
            'allocated_amount' => $allocated,
            'actual_spent' => $actual_spent,
            'status' => $status,
            'calculated_at' => date('Y-m-d H:i:s'),
            'last_sync_at' => date('Y-m-d H:i:s')
        ];

        $existing = $this->db->get_where('sma_budget_tracking', [
            'allocation_id' => $allocation_id
        ])->row_array();

        if ($existing) {
            $this->db->where('allocation_id', $allocation_id)->update('sma_budget_tracking', $tracking);
        } else {
            $this->db->insert('sma_budget_tracking', $tracking);
        }

        // Check if alert should trigger
        $this->check_alert_thresholds($allocation_id, $percentage);

        return true;
    }

    /**
     * Get budget tracking record
     * 
     * @param int $allocation_id
     * @return array|null
     */
    public function get_tracking($allocation_id) {
        $result = $this->db->get_where('sma_budget_tracking', ['allocation_id' => $allocation_id]);
        return $result->row_array();
    }

    /**
     * Get all tracking records for period
     * 
     * @param string $period YYYY-MM
     * @return array
     */
    public function get_tracking_by_period($period) {
        $query = "
            SELECT bt.*, ba.allocation_method, ba.allocated_by_user_name
            FROM sma_budget_tracking bt
            JOIN sma_budget_allocation ba ON bt.allocation_id = ba.allocation_id
            WHERE bt.period = ?
            ORDER BY bt.percentage_used DESC
        ";

        $result = $this->db->query($query, [$period]);
        return $result->result_array();
    }

    // =====================================================================
    // FORECAST FUNCTIONS
    // =====================================================================

    /**
     * Calculate budget forecast
     * 
     * @param int $allocation_id
     * @return bool
     */
    public function calculate_forecast($allocation_id) {
        $allocation = $this->get_allocation($allocation_id);
        if (!$allocation) return false;

        // Parse period
        list($year, $month) = explode('-', $allocation['period']);
        $days_in_month = cal_days_in_month(CAL_GREGORIAN, $month, $year);
        $today = date('d');
        $days_used = min($today, $days_in_month);
        $days_remaining = max(0, $days_in_month - $days_used);

        $current_spent = $allocation['actual_spent'] ?? 0;
        $allocated = $allocation['allocated_amount'];
        $burn_rate_daily = ($days_used > 0) ? $current_spent / $days_used : 0;
        $projected_end = ($days_remaining > 0) ? $current_spent + ($burn_rate_daily * $days_remaining) : $current_spent;

        // Determine risk level
        if ($projected_end <= $allocated * 0.8) {
            $risk_level = 'low';
        } elseif ($projected_end <= $allocated) {
            $risk_level = 'medium';
        } elseif ($projected_end <= $allocated * 1.2) {
            $risk_level = 'high';
        } else {
            $risk_level = 'critical';
        }

        $will_exceed = ($projected_end > $allocated) ? 1 : 0;

        // Generate recommendation
        $recommendation = $this->generate_forecast_recommendation(
            $projected_end,
            $allocated,
            $burn_rate_daily,
            $days_remaining
        );

        // Calculate confidence score (higher with more data)
        $confidence = min(100, ($days_used / 5) * 100); // Full confidence after 5 days

        $forecast = [
            'allocation_id' => $allocation_id,
            'hierarchy_level' => $allocation['hierarchy_level'],
            'warehouse_id' => $allocation['child_warehouse_id'] ?? $allocation['parent_warehouse_id'],
            'entity_name' => $allocation['child_name'] ?? $allocation['parent_name'],
            'period' => $allocation['period'],
            'allocated_amount' => $allocated,
            'current_spent' => $current_spent,
            'days_used' => $days_used,
            'days_remaining' => $days_remaining,
            'burn_rate_daily' => $burn_rate_daily,
            'burn_rate_weekly' => $burn_rate_daily * 7,
            'projected_end_of_month' => $projected_end,
            'confidence_score' => $confidence,
            'risk_level' => $risk_level,
            'will_exceed_budget' => $will_exceed,
            'recommendation' => $recommendation,
            'calculated_at' => date('Y-m-d H:i:s'),
            'based_on_days' => $days_used
        ];

        $existing = $this->db->get_where('sma_budget_forecast', [
            'allocation_id' => $allocation_id
        ])->row_array();

        if ($existing) {
            $this->db->where('allocation_id', $allocation_id)->update('sma_budget_forecast', $forecast);
        } else {
            $this->db->insert('sma_budget_forecast', $forecast);
        }

        return true;
    }

    /**
     * Generate forecast recommendation text
     * 
     * @param float $projected
     * @param float $allocated
     * @param float $burn_rate
     * @param int $days_remaining
     * @return string
     */
    private function generate_forecast_recommendation($projected, $allocated, $burn_rate, $days_remaining) {
        if ($projected <= $allocated * 0.7) {
            return "On track with 30%+ headroom. Monitor and adjust allocations if needed.";
        } elseif ($projected <= $allocated) {
            return "On track to stay within budget. Continue monitoring.";
        } elseif ($projected <= $allocated * 1.15) {
            $reduction = (($projected - $allocated) / $allocated) * 100;
            $daily_reduction = (($projected - $allocated) / $days_remaining);
            return "Will exceed budget by " . round($reduction) . "%. Need to reduce spending by SAR " . round($daily_reduction) . " daily.";
        } else {
            $excess = $projected - $allocated;
            return "Alert: Projected to exceed budget by SAR " . round($excess) . " (" . round(($excess/$allocated)*100) . "%). Immediate action required.";
        }
    }

    /**
     * Get forecast
     * 
     * @param int $allocation_id
     * @return array|null
     */
    public function get_forecast($allocation_id) {
        $result = $this->db->get_where('sma_budget_forecast', ['allocation_id' => $allocation_id]);
        return $result->row_array();
    }

    // =====================================================================
    // ALERT FUNCTIONS
    // =====================================================================

    /**
     * Configure alert thresholds
     * 
     * @param int $allocation_id
     * @param array $thresholds [50, 75, 90, 100]
     * @param array $recipient_user_ids
     * @param array $notification_channels
     * @param int $user_id
     * @return bool
     */
    public function configure_alerts($allocation_id, $thresholds, $recipient_user_ids, $notification_channels, $user_id) {
        // Delete existing alerts for this allocation
        $this->db->delete('sma_budget_alert_config', ['allocation_id' => $allocation_id]);

        // Create new alerts
        foreach ($thresholds as $threshold) {
            $alert_config = [
                'allocation_id' => $allocation_id,
                'threshold_percentage' => $threshold,
                'alert_type' => 'budget_threshold',
                'recipient_user_ids' => json_encode($recipient_user_ids),
                'notification_channels' => json_encode($notification_channels),
                'is_active' => 1,
                'created_by_user_id' => $user_id,
                'trigger_count' => 0
            ];

            $this->db->insert('sma_budget_alert_config', $alert_config);
        }

        return true;
    }

    /**
     * Check if alert thresholds should trigger
     * 
     * @param int $allocation_id
     * @param float $current_percentage
     * @return void
     */
    private function check_alert_thresholds($allocation_id, $current_percentage) {
        // Get active alerts for this allocation
        $alerts = $this->db->get_where('sma_budget_alert_config', [
            'allocation_id' => $allocation_id,
            'is_active' => 1
        ])->result_array();

        foreach ($alerts as $alert) {
            if ($current_percentage >= $alert['threshold_percentage']) {
                // Check if already triggered
                $existing_event = $this->db->query(
                    "SELECT * FROM sma_budget_alert_events 
                     WHERE alert_config_id = ? 
                     AND DATE(triggered_at) = CURDATE()
                     AND event_type = 'threshold_exceeded'",
                    [$alert['alert_config_id']]
                )->row_array();

                if (!$existing_event) {
                    // Create event
                    $event = [
                        'alert_config_id' => $alert['alert_config_id'],
                        'allocation_id' => $allocation_id,
                        'event_type' => 'threshold_exceeded',
                        'percentage_at_trigger' => $current_percentage,
                        'status' => 'active',
                        'notification_sent' => 0
                    ];

                    $this->db->insert('sma_budget_alert_events', $event);

                    // Update alert trigger count
                    $this->db->where('alert_config_id', $alert['alert_config_id'])
                             ->update('sma_budget_alert_config', [
                                 'trigger_count' => $alert['trigger_count'] + 1,
                                 'last_triggered_at' => date('Y-m-d H:i:s')
                             ]);
                }
            }
        }
    }

    /**
     * Get active alerts
     * 
     * @param string $period YYYY-MM
     * @return array
     */
    public function get_active_alerts($period) {
        $query = "
            SELECT bae.*, ba.allocated_amount, bt.actual_spent, bt.percentage_used
            FROM sma_budget_alert_events bae
            JOIN sma_budget_allocation ba ON bae.allocation_id = ba.allocation_id
            LEFT JOIN sma_budget_tracking bt ON ba.allocation_id = bt.allocation_id
            WHERE ba.period = ?
            AND bae.status IN ('active', 'acknowledged')
            ORDER BY bae.triggered_at DESC
        ";

        $result = $this->db->query($query, [$period]);
        return $result->result_array();
    }

    /**
     * Acknowledge alert
     * 
     * @param int $event_id
     * @param int $user_id
     * @return bool
     */
    public function acknowledge_alert($event_id, $user_id) {
        return $this->db->where('event_id', $event_id)->update('sma_budget_alert_events', [
            'status' => 'acknowledged',
            'acknowledged_by_user_id' => $user_id,
            'acknowledged_at' => date('Y-m-d H:i:s')
        ]);
    }

    // =====================================================================
    // UTILITY FUNCTIONS
    // =====================================================================

    /**
     * Log audit trail
     * 
     * @param int $allocation_id
     * @param string $action
     * @param array $old_value
     * @param array $new_value
     * @param int $user_id
     * @param string $reason
     * @return bool
     */
    private function log_audit($allocation_id, $action, $old_value, $new_value, $user_id, $reason) {
        $audit = [
            'allocation_id' => $allocation_id,
            'action' => $action,
            'old_value' => json_encode($old_value),
            'new_value' => json_encode($new_value),
            'user_id' => $user_id,
            'user_name' => $this->get_user_name($user_id),
            'changed_at' => date('Y-m-d H:i:s'),
            'change_reason' => $reason
        ];

        return $this->db->insert('sma_budget_audit_trail', $audit);
    }

    /**
     * Get user name
     * 
     * @param int $user_id
     * @return string|null
     */
    private function get_user_name($user_id) {
        // Adapt to your user table structure
        $this->db->select('name')->from('sma_users')->where('id', $user_id);
        $result = $this->db->get()->row_array();
        return $result['name'] ?? 'Unknown';
    }

    /**
     * Delete allocation (soft delete - mark inactive)
     * 
     * @param int $allocation_id
     * @param int $user_id
     * @return bool
     */
    public function delete_allocation($allocation_id, $user_id) {
        $old_data = $this->db->get_where('sma_budget_allocation', ['allocation_id' => $allocation_id])->row_array();
        
        $this->db->where('allocation_id', $allocation_id)->update('sma_budget_allocation', [
            'is_active' => 0,
            'updated_by_user_id' => $user_id
        ]);

        $this->log_audit($allocation_id, 'deleted', $old_data, ['is_active' => 0], $user_id, 'Budget allocation deleted');
        
        return true;
    }

    /**
     * Get audit trail for allocation
     * 
     * @param int $allocation_id
     * @return array
     */
    public function get_audit_trail($allocation_id) {
        $query = "
            SELECT * FROM sma_budget_audit_trail
            WHERE allocation_id = ?
            ORDER BY changed_at DESC
            LIMIT 50
        ";

        $result = $this->db->query($query, [$allocation_id]);
        return $result->result_array();
    }
}
