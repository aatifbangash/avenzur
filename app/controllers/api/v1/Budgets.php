<?php
/**
 * Budget API Controller
 * 
 * Endpoints for budget management:
 * - POST   /api/v1/budgets/allocate (create/update allocation)
 * - GET    /api/v1/budgets/allocated (get allocations)
 * - GET    /api/v1/budgets/tracking (get budget vs actual)
 * - GET    /api/v1/budgets/forecast (get forecast)
 * - GET    /api/v1/budgets/alerts (get alerts)
 * - POST   /api/v1/budgets/alerts/configure (configure alerts)
 * - POST   /api/v1/budgets/alerts/{id}/acknowledge (acknowledge alert)
 * 
 * Date: 2025-10-25
 */

require_once APPPATH . 'controllers/api/v1/Base_api.php';

class Budgets extends Base_api {

    public function __construct() {
        parent::__construct();
        $this->load->model('admin/Budget_model', 'budget');
    }

    /**
     * POST /api/v1/budgets/allocate
     * 
     * Create or update budget allocation
     * 
     * Request Body:
     * {
     *   "parent_warehouse_id": 1,
     *   "parent_hierarchy": "company|pharmacy|branch",
     *   "allocations": [
     *     {
     *       "child_warehouse_id": 101,
     *       "child_hierarchy": "pharmacy|branch",
     *       "allocated_amount": 50000,
     *       "allocation_method": "equal|proportional|custom"
     *     }
     *   ],
     *   "period": "2025-10"
     * }
     */
    public function allocate_post() {
        try {
            $user_id = $this->current_user['id'];
            $user_role = $this->current_user['role'];

            // Check permission
            if (!in_array($user_role, ['admin', 'finance', 'pharmacy_manager'])) {
                return $this->response([
                    'success' => false,
                    'message' => 'Permission denied',
                    'status' => 403
                ]);
            }

            // Validate input
            $parent_warehouse_id = $this->post('parent_warehouse_id');
            $allocations = $this->post('allocations');
            $period = $this->post('period') ?: date('Y-m');

            if (!$parent_warehouse_id || !is_array($allocations) || empty($allocations)) {
                return $this->response([
                    'success' => false,
                    'message' => 'Invalid request: parent_warehouse_id and allocations required',
                    'status' => 400
                ]);
            }

            // Check permission: User can only allocate to entities they have access to
            if ($user_role === 'pharmacy_manager' && !$this->check_pharmacy_access($parent_warehouse_id, $user_id)) {
                return $this->response([
                    'success' => false,
                    'message' => 'Permission denied: Can only allocate within your pharmacy',
                    'status' => 403
                ]);
            }

            // Get parent entity info
            $parent_info = $this->get_entity_info($parent_warehouse_id);
            if (!$parent_info) {
                return $this->response([
                    'success' => false,
                    'message' => 'Parent entity not found',
                    'status' => 404
                ]);
            }

            $created_allocations = [];
            $total_allocated = 0;

            // Process each allocation
            foreach ($allocations as $allocation_data) {
                $child_warehouse_id = $allocation_data['child_warehouse_id'];
                $allocated_amount = $allocation_data['allocated_amount'];

                // Get child entity info
                $child_info = $this->get_entity_info($child_warehouse_id);
                if (!$child_info) {
                    continue; // Skip invalid entities
                }

                // Create allocation
                $alloc_data = [
                    'hierarchy_level' => $child_info['hierarchy_level'],
                    'parent_hierarchy' => $parent_info['hierarchy_level'],
                    'parent_warehouse_id' => $parent_warehouse_id,
                    'parent_name' => $parent_info['name'],
                    'child_hierarchy' => $child_info['hierarchy_level'],
                    'child_warehouse_id' => $child_warehouse_id,
                    'child_name' => $child_info['name'],
                    'period' => $period,
                    'allocated_amount' => $allocated_amount,
                    'allocation_method' => $allocation_data['allocation_method'] ?? 'custom',
                    'pharmacy_id' => $child_info['pharmacy_id'] ?? null,
                    'branch_id' => $child_info['branch_id'] ?? null
                ];

                $allocation_id = $this->budget->create_allocation($alloc_data, $user_id);
                
                if ($allocation_id) {
                    // Calculate tracking & forecast immediately
                    $this->budget->calculate_tracking($allocation_id);
                    $this->budget->calculate_forecast($allocation_id);

                    $created_allocations[] = [
                        'allocation_id' => $allocation_id,
                        'entity_name' => $child_info['name'],
                        'allocated_amount' => $allocated_amount
                    ];

                    $total_allocated += $allocated_amount;
                }
            }

            return $this->response([
                'success' => true,
                'message' => 'Budget allocated successfully',
                'allocations_created' => count($created_allocations),
                'allocations' => $created_allocations,
                'total_allocated' => $total_allocated,
                'period' => $period,
                'status' => 201
            ]);

        } catch (Exception $e) {
            log_message('error', 'Budget allocate error: ' . $e->getMessage());
            return $this->response([
                'success' => false,
                'message' => 'Error allocating budget',
                'error' => $e->getMessage(),
                'status' => 500
            ]);
        }
    }

    /**
     * GET /api/v1/budgets/allocated
     * 
     * Get budget allocations
     * 
     * Query Parameters:
     * - period: YYYY-MM (default: current)
     * - warehouse_id: Filter by entity
     * - hierarchy: company|pharmacy|branch
     * - limit: results per page
     * - offset: pagination offset
     */
    public function allocated_get() {
        try {
            $user_id = $this->current_user['id'];
            $user_role = $this->current_user['role'];
            
            $period = $this->get('period') ?: date('Y-m');
            $warehouse_id = $this->get('warehouse_id');
            $hierarchy = $this->get('hierarchy');
            $limit = min((int)$this->get('limit', true) ?: 100, 500);
            $offset = (int)$this->get('offset', true) ?: 0;

            // Build query
            $query = "
                SELECT ba.*, 
                       bt.actual_spent,
                       bt.percentage_used,
                       bt.status as tracking_status
                FROM sma_budget_allocation ba
                LEFT JOIN sma_budget_tracking bt ON ba.allocation_id = bt.allocation_id
                WHERE ba.period = ?
                AND ba.is_active = 1
            ";

            $params = [$period];

            // Role-based filtering
            if ($user_role === 'pharmacy_manager') {
                $query .= " AND (ba.pharmacy_id = ? OR ba.parent_warehouse_id = ?)";
                $assigned_pharmacy_id = $this->get_pharmacy_for_user($user_id);
                $params[] = $assigned_pharmacy_id;
                $params[] = $assigned_pharmacy_id;
            } elseif ($user_role === 'branch_manager') {
                $query .= " AND (ba.branch_id = ? OR ba.child_warehouse_id = ?)";
                $assigned_branch_id = $this->get_branch_for_user($user_id);
                $params[] = $assigned_branch_id;
                $params[] = $assigned_branch_id;
            } elseif ($user_role === 'finance') {
                $query .= " AND ba.hierarchy_level = 'company'";
            }
            // Admin sees all

            if ($warehouse_id) {
                $query .= " AND (ba.parent_warehouse_id = ? OR ba.child_warehouse_id = ?)";
                $params[] = $warehouse_id;
                $params[] = $warehouse_id;
            }

            if ($hierarchy) {
                $query .= " AND ba.hierarchy_level = ?";
                $params[] = $hierarchy;
            }

            $query .= " ORDER BY ba.allocated_at DESC LIMIT ? OFFSET ?";
            $params[] = $limit;
            $params[] = $offset;

            $result = $this->db->query($query, $params);
            $allocations = $result->result_array();

            // Get total count
            $count_query = str_replace(
                ['SELECT ba.*, bt.actual_spent, bt.percentage_used, bt.status as tracking_status', 'LIMIT ? OFFSET ?'],
                ['SELECT COUNT(*) as total', ''],
                $query
            );
            $count_params = array_slice($params, 0, -2);
            $count_result = $this->db->query($count_query, $count_params);
            $total = $count_result->row_array()['total'] ?? 0;

            return $this->response([
                'success' => true,
                'data' => $allocations,
                'pagination' => [
                    'total' => $total,
                    'limit' => $limit,
                    'offset' => $offset,
                    'pages' => ceil($total / $limit)
                ],
                'status' => 200
            ]);

        } catch (Exception $e) {
            log_message('error', 'Budget allocated get error: ' . $e->getMessage());
            return $this->response([
                'success' => false,
                'message' => 'Error fetching allocations',
                'error' => $e->getMessage(),
                'status' => 500
            ]);
        }
    }

    /**
     * GET /api/v1/budgets/tracking
     * 
     * Get budget vs actual tracking
     * 
     * Query Parameters:
     * - period: YYYY-MM
     * - warehouse_id: Entity to track
     * - allocation_id: Specific allocation
     */
    public function tracking_get() {
        try {
            $period = $this->get('period') ?: date('Y-m');
            $allocation_id = $this->get('allocation_id');

            if ($allocation_id) {
                // Get specific tracking record
                $tracking = $this->budget->get_tracking($allocation_id);
                
                if (!$tracking) {
                    return $this->response([
                        'success' => false,
                        'message' => 'Tracking record not found',
                        'status' => 404
                    ]);
                }

                return $this->response([
                    'success' => true,
                    'data' => $tracking,
                    'status' => 200
                ]);
            }

            // Get all tracking for period
            $tracking = $this->budget->get_tracking_by_period($period);

            return $this->response([
                'success' => true,
                'data' => $tracking,
                'period' => $period,
                'count' => count($tracking),
                'status' => 200
            ]);

        } catch (Exception $e) {
            log_message('error', 'Budget tracking get error: ' . $e->getMessage());
            return $this->response([
                'success' => false,
                'message' => 'Error fetching tracking data',
                'error' => $e->getMessage(),
                'status' => 500
            ]);
        }
    }

    /**
     * GET /api/v1/budgets/forecast
     * 
     * Get budget forecast
     * 
     * Query Parameters:
     * - allocation_id: Required
     * - period: YYYY-MM
     */
    public function forecast_get() {
        try {
            $allocation_id = $this->get('allocation_id');

            if (!$allocation_id) {
                return $this->response([
                    'success' => false,
                    'message' => 'allocation_id required',
                    'status' => 400
                ]);
            }

            $forecast = $this->budget->get_forecast($allocation_id);

            if (!$forecast) {
                return $this->response([
                    'success' => false,
                    'message' => 'Forecast not found',
                    'status' => 404
                ]);
            }

            return $this->response([
                'success' => true,
                'data' => $forecast,
                'status' => 200
            ]);

        } catch (Exception $e) {
            log_message('error', 'Budget forecast get error: ' . $e->getMessage());
            return $this->response([
                'success' => false,
                'message' => 'Error fetching forecast',
                'error' => $e->getMessage(),
                'status' => 500
            ]);
        }
    }

    /**
     * GET /api/v1/budgets/alerts
     * 
     * Get active budget alerts
     * 
     * Query Parameters:
     * - period: YYYY-MM
     */
    public function alerts_get() {
        try {
            $period = $this->get('period') ?: date('Y-m');

            $alerts = $this->budget->get_active_alerts($period);

            return $this->response([
                'success' => true,
                'data' => $alerts,
                'period' => $period,
                'count' => count($alerts),
                'status' => 200
            ]);

        } catch (Exception $e) {
            log_message('error', 'Budget alerts get error: ' . $e->getMessage());
            return $this->response([
                'success' => false,
                'message' => 'Error fetching alerts',
                'error' => $e->getMessage(),
                'status' => 500
            ]);
        }
    }

    /**
     * POST /api/v1/budgets/alerts/configure
     * 
     * Configure alert thresholds
     * 
     * Request Body:
     * {
     *   "allocation_id": 1,
     *   "thresholds": [50, 75, 90, 100],
     *   "recipient_user_ids": [1, 2, 3],
     *   "notification_channels": ["email", "in-app"]
     * }
     */
    public function alerts_configure_post() {
        try {
            $user_id = $this->current_user['id'];
            $user_role = $this->current_user['role'];

            if (!in_array($user_role, ['admin', 'finance'])) {
                return $this->response([
                    'success' => false,
                    'message' => 'Permission denied',
                    'status' => 403
                ]);
            }

            $allocation_id = $this->post('allocation_id');
            $thresholds = $this->post('thresholds');
            $recipient_user_ids = $this->post('recipient_user_ids') ?: [];
            $notification_channels = $this->post('notification_channels') ?: ['email', 'in-app'];

            if (!$allocation_id || !is_array($thresholds)) {
                return $this->response([
                    'success' => false,
                    'message' => 'Invalid request',
                    'status' => 400
                ]);
            }

            $this->budget->configure_alerts(
                $allocation_id,
                $thresholds,
                $recipient_user_ids,
                $notification_channels,
                $user_id
            );

            return $this->response([
                'success' => true,
                'message' => 'Alert thresholds configured',
                'allocation_id' => $allocation_id,
                'thresholds' => $thresholds,
                'status' => 200
            ]);

        } catch (Exception $e) {
            log_message('error', 'Alert configure error: ' . $e->getMessage());
            return $this->response([
                'success' => false,
                'message' => 'Error configuring alerts',
                'error' => $e->getMessage(),
                'status' => 500
            ]);
        }
    }

    /**
     * POST /api/v1/budgets/alerts/{id}/acknowledge
     * 
     * Acknowledge alert event
     */
    public function alerts_acknowledge_post($event_id = null) {
        try {
            $user_id = $this->current_user['id'];

            if (!$event_id) {
                return $this->response([
                    'success' => false,
                    'message' => 'event_id required',
                    'status' => 400
                ]);
            }

            $this->budget->acknowledge_alert($event_id, $user_id);

            return $this->response([
                'success' => true,
                'message' => 'Alert acknowledged',
                'event_id' => $event_id,
                'status' => 200
            ]);

        } catch (Exception $e) {
            log_message('error', 'Alert acknowledge error: ' . $e->getMessage());
            return $this->response([
                'success' => false,
                'message' => 'Error acknowledging alert',
                'error' => $e->getMessage(),
                'status' => 500
            ]);
        }
    }

    // =====================================================================
    // HELPER FUNCTIONS
    // =====================================================================

    /**
     * Get entity info
     * 
     * @param int $warehouse_id
     * @return array|null
     */
    private function get_entity_info($warehouse_id) {
        // Check if pharmacy
        $pharmacy = $this->db->get_where('sma_dim_pharmacy', ['warehouse_id' => $warehouse_id])->row_array();
        if ($pharmacy) {
            return [
                'warehouse_id' => $warehouse_id,
                'hierarchy_level' => 'pharmacy',
                'name' => $pharmacy['pharmacy_name'],
                'pharmacy_id' => $pharmacy['pharmacy_id'],
                'branch_id' => null
            ];
        }

        // Check if branch
        $branch = $this->db->get_where('sma_dim_branch', ['warehouse_id' => $warehouse_id])->row_array();
        if ($branch) {
            return [
                'warehouse_id' => $warehouse_id,
                'hierarchy_level' => 'branch',
                'name' => $branch['branch_name'],
                'pharmacy_id' => $branch['pharmacy_id'],
                'branch_id' => $branch['branch_id']
            ];
        }

        return null;
    }

    /**
     * Check if user has access to pharmacy
     * 
     * @param int $warehouse_id
     * @param int $user_id
     * @return bool
     */
    private function check_pharmacy_access($warehouse_id, $user_id) {
        $assigned_pharmacy_id = $this->get_pharmacy_for_user($user_id);
        $pharmacy = $this->db->get_where('sma_dim_pharmacy', ['warehouse_id' => $warehouse_id])->row_array();
        
        return ($pharmacy && $pharmacy['pharmacy_id'] == $assigned_pharmacy_id);
    }

    /**
     * Get pharmacy assigned to user
     * 
     * @param int $user_id
     * @return int|null
     */
    private function get_pharmacy_for_user($user_id) {
        // Adapt to your user/assignment table structure
        $result = $this->db->select('pharmacy_id')
                          ->from('sma_user_assignments')
                          ->where('user_id', $user_id)
                          ->get()
                          ->row_array();
        
        return $result['pharmacy_id'] ?? null;
    }

    /**
     * Get branch assigned to user
     * 
     * @param int $user_id
     * @return int|null
     */
    private function get_branch_for_user($user_id) {
        $result = $this->db->select('branch_id')
                          ->from('sma_user_assignments')
                          ->where('user_id', $user_id)
                          ->get()
                          ->row_array();
        
        return $result['branch_id'] ?? null;
    }
}
