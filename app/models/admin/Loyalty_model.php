<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Loyalty_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Get Budget Status - Mock Data
     * Real API: GET /api/v1/discounts/budget/status
     */
    public function getBudgetStatus($scopeLevel = 'company', $scopeId = 1)
    {
        // TODO: Replace with actual API call
        // $apiUrl = base_url("api/v1/discounts/budget/status?scopeLevel={$scopeLevel}&scopeId={$scopeId}");
        // return $this->makeApiCall($apiUrl);
        
        return [
            'success' => true,
            'data' => [
                'total_budget' => 500000,
                'spent' => 325000,
                'remaining' => 175000,
                'percentage_used' => 65,
                'status' => 'warning', // 'good', 'warning', 'critical'
                'scope_level' => $scopeLevel,
                'scope_id' => $scopeId,
                'currency' => 'SAR'
            ]
        ];
    }

    /**
     * Get Burn Rate Analysis - Mock Data
     * Real API: GET /api/v1/discounts/budget/burn-rate
     */
    public function getBurnRate($scopeLevel = 'company', $scopeId = 1)
    {
        // TODO: Replace with actual API call
        
        return [
            'success' => true,
            'data' => [
                'daily_burn_rate' => 5400,
                'weekly_burn_rate' => 37800,
                'monthly_burn_rate' => 162000,
                'projected_depletion_date' => '2025-12-15',
                'days_remaining' => 32,
                'trend' => 'increasing', // 'increasing', 'stable', 'decreasing'
                'historical_data' => [
                    ['date' => '2025-10-01', 'burn_rate' => 4800],
                    ['date' => '2025-10-08', 'burn_rate' => 5200],
                    ['date' => '2025-10-15', 'burn_rate' => 5100],
                    ['date' => '2025-10-22', 'burn_rate' => 5400],
                ]
            ]
        ];
    }

    /**
     * Get Budget Projections - Mock Data
     * Real API: GET /api/v1/discounts/budget/projections
     */
    public function getBudgetProjections($scopeLevel = 'company', $scopeId = 1)
    {
        // TODO: Replace with actual API call
        
        return [
            'success' => true,
            'data' => [
                'current_month_projection' => 162000,
                'next_month_projection' => 168000,
                'quarter_projection' => 486000,
                'year_projection' => 1944000,
                'confidence_level' => 85, // percentage
                'projections_by_month' => [
                    ['month' => '2025-11', 'projected' => 168000, 'lower_bound' => 155000, 'upper_bound' => 180000],
                    ['month' => '2025-12', 'projected' => 175000, 'lower_bound' => 162000, 'upper_bound' => 188000],
                    ['month' => '2026-01', 'projected' => 170000, 'lower_bound' => 158000, 'upper_bound' => 182000],
                    ['month' => '2026-02', 'projected' => 165000, 'lower_bound' => 153000, 'upper_bound' => 177000],
                ]
            ]
        ];
    }

    /**
     * Get Budget Alerts - Mock Data
     * Real API: GET /api/v1/discounts/budget/alerts
     */
    public function getBudgetAlerts($scopeLevel = 'company', $scopeId = 1)
    {
        // TODO: Replace with actual API call
        
        return [
            'success' => true,
            'data' => [
                'total_alerts' => 3,
                'critical' => 1,
                'warning' => 2,
                'info' => 0,
                'alerts' => [
                    [
                        'id' => 1,
                        'level' => 'critical',
                        'title' => 'Budget 65% Depleted',
                        'message' => 'Company-wide discount budget has exceeded 65% threshold',
                        'timestamp' => '2025-10-23 09:30:00',
                        'scope_level' => 'company',
                        'action_required' => true
                    ],
                    [
                        'id' => 2,
                        'level' => 'warning',
                        'title' => 'Burn Rate Increasing',
                        'message' => 'Daily burn rate increased by 12% in the last week',
                        'timestamp' => '2025-10-22 14:15:00',
                        'scope_level' => 'company',
                        'action_required' => false
                    ],
                    [
                        'id' => 3,
                        'level' => 'warning',
                        'title' => 'Branch Budget Low',
                        'message' => 'Riyadh Branch 1 has only 15 days of budget remaining',
                        'timestamp' => '2025-10-21 10:00:00',
                        'scope_level' => 'branch',
                        'action_required' => true
                    ]
                ]
            ]
        ];
    }

    /**
     * Get Spending Trend - Mock Data
     * Real API: GET /api/v1/discounts/budget/spending-trend
     */
    public function getSpendingTrend($scopeLevel = 'company', $scopeId = 1, $period = 'monthly')
    {
        // TODO: Replace with actual API call
        
        if ($period === 'daily') {
            return [
                'success' => true,
                'data' => [
                    'period' => 'daily',
                    'trend_data' => [
                        ['date' => '2025-10-17', 'spending' => 5100],
                        ['date' => '2025-10-18', 'spending' => 5300],
                        ['date' => '2025-10-19', 'spending' => 4900],
                        ['date' => '2025-10-20', 'spending' => 5500],
                        ['date' => '2025-10-21', 'spending' => 5200],
                        ['date' => '2025-10-22', 'spending' => 5400],
                        ['date' => '2025-10-23', 'spending' => 5600],
                    ]
                ]
            ];
        }
        
        return [
            'success' => true,
            'data' => [
                'period' => 'monthly',
                'trend_data' => [
                    ['month' => '2025-04', 'spending' => 145000, 'budget' => 500000],
                    ['month' => '2025-05', 'spending' => 152000, 'budget' => 500000],
                    ['month' => '2025-06', 'spending' => 148000, 'budget' => 500000],
                    ['month' => '2025-07', 'spending' => 158000, 'budget' => 500000],
                    ['month' => '2025-08', 'spending' => 162000, 'budget' => 500000],
                    ['month' => '2025-09', 'spending' => 155000, 'budget' => 500000],
                    ['month' => '2025-10', 'spending' => 167000, 'budget' => 500000],
                ],
                'average_monthly_spending' => 155286,
                'trend_direction' => 'increasing',
                'growth_rate' => 3.2 // percentage
            ]
        ];
    }

    /**
     * Get Budget Summary - Mock Data
     * Real API: GET /api/v1/discounts/budget/summary
     */
    public function getBudgetSummary($scopeLevel = 'company', $scopeId = 1)
    {
        // TODO: Replace with actual API call
        
        return [
            'success' => true,
            'data' => [
                'scope_level' => $scopeLevel,
                'scope_id' => $scopeId,
                'total_budget' => 500000,
                'allocated' => 500000,
                'spent' => 325000,
                'committed' => 45000,
                'available' => 130000,
                'breakdown_by_type' => [
                    ['type' => 'Percentage Discount', 'spent' => 185000, 'percentage' => 56.9],
                    ['type' => 'Fixed Amount', 'spent' => 95000, 'percentage' => 29.2],
                    ['type' => 'Buy One Get One', 'spent' => 45000, 'percentage' => 13.9],
                ],
                'breakdown_by_branch' => [
                    ['branch' => 'Riyadh Branch 1', 'spent' => 145000, 'budget' => 200000],
                    ['branch' => 'Jeddah Branch 1', 'spent' => 105000, 'budget' => 150000],
                    ['branch' => 'Dammam Branch 1', 'spent' => 75000, 'budget' => 150000],
                ],
                'top_discount_categories' => [
                    ['category' => 'Seasonal Promotions', 'amount' => 125000],
                    ['category' => 'Customer Loyalty', 'amount' => 98000],
                    ['category' => 'Clearance Sales', 'amount' => 67000],
                    ['category' => 'Bundle Offers', 'amount' => 35000],
                ]
            ]
        ];
    }

    /**
     * Insert new Pharmacy Group (Company)
     * 
     * Creates a new pharmacy group and inserts into:
     * 1. sma_warehouses (warehouse_type = 'pharmaGroup')
     * 2. loyalty_companies
     * 3. loyalty_pharmacy_groups
     * 
     * @param array $data Pharmacy group data
     * @return array Result with pharmacy_group_id and company_id on success
     */
    public function insertPharmGroup($data = [])
    {
        if (empty($data)) {
            return [
                'success' => false,
                'message' => 'No data provided'
            ];
        }

        $this->db->trans_start();

        try {
            $code = isset($data['code']) ? $data['code'] : '';
            $name = isset($data['name']) ? $data['name'] : '';
            $address = isset($data['address']) ? $data['address'] : '';
            $phone = isset($data['phone']) ? $data['phone'] : '';
            $email = isset($data['email']) ? $data['email'] : '';
            $country = isset($data['country']) ? $data['country'] : 8;  // Default: Saudi Arabia

            // Validate required fields
            if (empty($code) || empty($name) || empty($address) || empty($phone)) {
                throw new Exception('Missing required fields: code, name, address, phone');
            }

            // Check for duplicate code
            $existing = $this->db->select('id')->from('sma_warehouses')->where('code', $code)->get()->row();
            if ($existing) {
                throw new Exception('Pharmacy group code already exists');
            }

            // Check for duplicate name
            $existing_name = $this->db->select('id')->from('loyalty_pharmacy_groups')->where('name', $name)->get()->row();
            if ($existing_name) {
                throw new Exception('Pharmacy group name already exists');
            }

            // STEP 1: Insert into sma_warehouses
            $warehouse_data = [
                'code' => $code,
                'name' => $name,
                'address' => $address,
                'phone' => $phone,
                'email' => $email,
                'warehouse_type' => 'pharmaGroup',
                'country' => $country,
                'is_main' => 0,
                'parent_id' => null
            ];

            $this->db->insert('sma_warehouses', $warehouse_data);
            $warehouse_id = $this->db->insert_id();

            if (!$warehouse_id) {
                throw new Exception('Failed to create pharmacy group warehouse');
            }

            // Generate UUIDs
            $company_id = $this->generateUUID();
            $pharma_group_id = $this->generateUUID();
            $timestamp = date('Y-m-d H:i:s');

            // STEP 2: Insert into loyalty_companies
            $this->db->query(
                "INSERT INTO loyalty_companies (id, code, name, created_at, updated_at) VALUES (?, ?, ?, ?, ?)",
                [$company_id, $code, $name, $timestamp, $timestamp]
            );

            // STEP 3: Insert into loyalty_pharmacy_groups
            $this->db->query(
                "INSERT INTO loyalty_pharmacy_groups (id, code, name, company_id, external_id, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?)",
                [$pharma_group_id, $code, $name, $company_id, $warehouse_id, $timestamp, $timestamp]
            );

            $this->db->trans_complete();

            if ($this->db->trans_status() === false) {
                return [
                    'success' => false,
                    'message' => 'Database transaction failed'
                ];
            }

            return [
                'success' => true,
                'message' => 'Pharmacy Group created successfully',
                'data' => [
                    'pharmacy_group_id' => $pharma_group_id,
                    'company_id' => $company_id,
                    'warehouse_id' => $warehouse_id,
                    'code' => $code,
                    'name' => $name
                ]
            ];

        } catch (Exception $e) {
            $this->db->trans_rollback();
            log_message('error', 'InsertPharmGroup error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Get Pharmacy Group by ID
     * 
     * @param string $id Pharmacy group ID
     * @return array Pharmacy group data with warehouse details
     */
    public function getPharmGroup($id = '')
    {
        if (empty($id)) {
            return null;
        }

        $query = "SELECT 
                    lpg.id,
                    lpg.code,
                    lpg.name,
                    lpg.company_id,
                    lpg.external_id,
                    COALESCE(sw.address, '') as address,
                    COALESCE(sw.phone, '') as phone,
                    COALESCE(sw.email, '') as email,
                    sw.country
                  FROM loyalty_pharmacy_groups lpg
                  LEFT JOIN sma_warehouses sw ON lpg.external_id = sw.id
                  WHERE lpg.id = ?";
        
        return $this->db->query($query, [$id])->row_array();
    }

    /**
     * Get all Pharmacy Groups
     * 
     * @return array Array of pharmacy groups
     */
    public function getAllPharmGroups()
    {
        $query = "SELECT 
                    lpg.id,
                    lpg.code,
                    lpg.name,
                    lpg.company_id,
                    COALESCE(sw.address, '') as address,
                    COALESCE(sw.phone, '') as phone,
                    COALESCE(sw.email, '') as email
                  FROM loyalty_pharmacy_groups lpg
                  LEFT JOIN sma_warehouses sw ON lpg.external_id = sw.id
                  ORDER BY lpg.name ASC";
        
        return $this->db->query($query)->result_array();
    }

    /**
     * Get Company ID (first record from loyalty_companies table)
     * 
     * @return string|null Company ID or null if no company exists
     */
    public function getCompanyId()
    {
        $query = "SELECT id FROM loyalty_companies ORDER BY created_at ASC LIMIT 1";
        $result = $this->db->query($query)->row();
        
        return $result ? $result->id : null;
    }

    /**
     * Update Pharmacy Group
     * 
     * @param string $id Pharmacy group ID
     * @param array $data Data to update
     * @return array Success/failure response
     */
    public function updatePharmGroup($id = '', $data = [])
    {
        if (empty($id) || empty($data)) {
            return [
                'success' => false,
                'message' => 'ID and data required'
            ];
        }

        $this->db->trans_start();

        try {
            // Get existing pharmacy group
            $query = "SELECT external_id, company_id FROM loyalty_pharmacy_groups WHERE id = ?";
            $existing = $this->db->query($query, [$id])->row_array();
            
            if (!$existing) {
                throw new Exception('Pharmacy Group not found');
            }

            $warehouse_id = $existing['external_id'];
            $company_id = $existing['company_id'];

            $code = isset($data['code']) ? $data['code'] : '';
            $name = isset($data['name']) ? $data['name'] : '';
            $address = isset($data['address']) ? $data['address'] : '';
            $phone = isset($data['phone']) ? $data['phone'] : '';
            $email = isset($data['email']) ? $data['email'] : '';
            $timestamp = date('Y-m-d H:i:s');

            // Update sma_warehouses
            $this->db->query(
                "UPDATE sma_warehouses SET code = ?, name = ?, address = ?, phone = ?, email = ? WHERE id = ?",
                [$code, $name, $address, $phone, $email, $warehouse_id]
            );

            // Update loyalty_companies
            $this->db->query(
                "UPDATE loyalty_companies SET code = ?, name = ?, updated_at = ? WHERE id = ?",
                [$code, $name, $timestamp, $company_id]
            );

            // Update loyalty_pharmacy_groups
            $this->db->query(
                "UPDATE loyalty_pharmacy_groups SET code = ?, name = ?, updated_at = ? WHERE id = ?",
                [$code, $name, $timestamp, $id]
            );

            $this->db->trans_complete();

            if ($this->db->trans_status() === false) {
                return [
                    'success' => false,
                    'message' => 'Database transaction failed'
                ];
            }

            return [
                'success' => true,
                'message' => 'Pharmacy Group updated successfully'
            ];

        } catch (Exception $e) {
            $this->db->trans_rollback();
            log_message('error', 'UpdatePharmGroup error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Delete Pharmacy Group
     * 
     * @param string $id Pharmacy group ID
     * @return array Success/failure response
     */
    public function deletePharmGroup($id = '')
    {
        if (empty($id)) {
            return [
                'success' => false,
                'message' => 'ID required'
            ];
        }

        $this->db->trans_start();

        try {
            // Get pharmacy group details
            $query = "SELECT external_id, company_id FROM loyalty_pharmacy_groups WHERE id = ?";
            $pharma_group = $this->db->query($query, [$id])->row_array();

            if (!$pharma_group) {
                throw new Exception('Pharmacy Group not found');
            }

            $warehouse_id = $pharma_group['external_id'];
            $company_id = $pharma_group['company_id'];

            // Get all pharmacies in this group
            $pharmacies_query = "SELECT id, external_id FROM loyalty_pharmacies WHERE pharmacy_group_id = ?";
            $pharmacies = $this->db->query($pharmacies_query, [$id])->result_array();

            // Delete branches and branch warehouses for each pharmacy
            foreach ($pharmacies as $pharmacy) {
                $this->db->query(
                    "DELETE FROM loyalty_branches WHERE pharmacy_id = ?",
                    [$pharmacy['id']]
                );

                $this->db->query(
                    "DELETE FROM sma_warehouses WHERE parent_id = ? AND warehouse_type = 'branch'",
                    [$pharmacy['external_id']]
                );
            }

            // Delete loyalty_pharmacies
            $this->db->query(
                "DELETE FROM loyalty_pharmacies WHERE pharmacy_group_id = ?",
                [$id]
            );

            // Delete pharmacy warehouses
            $this->db->query(
                "DELETE FROM sma_warehouses WHERE parent_id = ? AND warehouse_type = 'pharmacy'",
                [$warehouse_id]
            );

            // Delete pharma group warehouse
            $this->db->query(
                "DELETE FROM sma_warehouses WHERE id = ?",
                [$warehouse_id]
            );

            // Delete loyalty_pharmacy_groups
            $this->db->query(
                "DELETE FROM loyalty_pharmacy_groups WHERE id = ?",
                [$id]
            );

            // Delete loyalty_companies
            $this->db->query(
                "DELETE FROM loyalty_companies WHERE id = ?",
                [$company_id]
            );

            $this->db->trans_complete();

            if ($this->db->trans_status() === false) {
                throw new Exception('Transaction failed');
            }

            return [
                'success' => true,
                'message' => 'Pharmacy Group deleted successfully'
            ];

        } catch (Exception $e) {
            $this->db->trans_rollback();
            log_message('error', 'DeletePharmGroup error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Generate UUID v4
     * 
     * @return string UUID v4 format
     */
    private function generateUUID()
    {
        return sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
    }

    /**
     * Get All Budget Allocations
     */
    public function getAllBudgetAllocations()
    {
        return $this->db
            ->where('is_active', 1)
            ->order_by('allocated_at', 'DESC')
            ->get('sma_budget_allocation')
            ->result_array();
    }

    /**
     * Get Burn Rate Summary
     */
    public function getBurnRateSummary($period = 'week')
    {
        $startDate = $this->_getPeriodStartDate($period);
        $endDate = date('Y-m-d H:i:s');
        
        $result = $this->db
            ->select_sum('actual_spent', 'total_spent')
            ->where('calculated_at >=', $startDate)
            ->where('calculated_at <=', $endDate)
            ->get('sma_budget_tracking')
            ->row_array();
        
        $totalSpent = $result['total_spent'] ?? 0;
        $days = $this->_getPeriodDays($period);
        $dailyBurnRate = $days > 0 ? $totalSpent / $days : 0;
        
        return [
            'total_budget' => 500000,
            'total_spent' => $totalSpent,
            'daily_burn_rate' => $dailyBurnRate,
            'days_remaining' => 30 - ceil((date('d') - 1)),
            'projected_end' => $totalSpent + ($dailyBurnRate * $this->_getPeriodDays($period))
        ];
    }

    /**
     * Get Daily Burn Trend Data
     */
    public function getDailyBurnTrendData($period = 'week')
    {
        $startDate = $this->_getPeriodStartDate($period);
        
        $result = $this->db
            ->select("DATE(calculated_at) as date, SUM(actual_spent) as amount", false)
            ->where('calculated_at >=', $startDate)
            ->group_by("DATE(calculated_at)")
            ->order_by('calculated_at', 'ASC')
            ->get('sma_budget_tracking')
            ->result_array();
        
        return $result ?: [];
    }

    /**
     * Get Burn Rate Trend Data
     */
    public function getBurnRateTrendData($period = 'week')
    {
        return $this->getDailyBurnTrendData($period);
    }

    /**
     * Get Pharmacy Breakdown
     */
    public function getPharmacyBreakdown($period = 'week')
    {
        $startDate = $this->_getPeriodStartDate($period);
        
        $result = $this->db
            ->select("w.name, SUM(sbt.actual_spent) as amount, COUNT(sbt.tracking_id) as count", false)
            ->from('sma_budget_tracking sbt')
            ->join('sma_warehouses w', 'sbt.warehouse_id = w.id', 'left')
            ->where('sbt.calculated_at >=', $startDate)
            ->group_by('sbt.warehouse_id')
            ->order_by('amount', 'DESC')
            ->get()
            ->result_array();
        
        return $result ?: [];
    }

    /**
     * Get Forecast Data
     */
    public function getForecastData($period = 'week')
    {
        $summary = $this->getBurnRateSummary($period);
        $dailyRate = $summary['daily_burn_rate'];
        
        $forecast = [];
        for ($i = 0; $i < 7; $i++) {
            $date = date('Y-m-d', strtotime("+{$i} days"));
            $forecast[] = [
                'date' => $date,
                'best' => $summary['total_spent'] + ($dailyRate * 0.8 * $i),
                'current' => $summary['total_spent'] + ($dailyRate * $i),
                'worst' => $summary['total_spent'] + ($dailyRate * 1.2 * $i)
            ];
        }
        
        return $forecast;
    }

    /**
     * Get Active Alerts
     */
    public function getActiveAlerts()
    {
        return $this->db
            ->where('status', 'active')
            ->order_by('triggered_at', 'DESC')
            ->get('sma_budget_alert_events')
            ->result_array();
    }

    /**
     * Get Pharmacy Spending History
     */
    public function getPharmacySpendingHistory($pharmacyId)
    {
        return $this->db
            ->where('warehouse_id', $pharmacyId)
            ->order_by('calculated_at', 'DESC')
            ->get('sma_budget_tracking')
            ->result_array();
    }

    /**
     * Helper: Get Period Start Date
     */
    private function _getPeriodStartDate($period = 'week')
    {
        switch ($period) {
            case 'today':
                return date('Y-m-d 00:00:00');
            case 'week':
                return date('Y-m-d 00:00:00', strtotime('last Monday'));
            case 'month':
                return date('Y-m-01 00:00:00');
            case 'quarter':
                $month = ceil(date('m') / 3) * 3 - 2;
                return date('Y-' . str_pad($month, 2, '0', STR_PAD_LEFT) . '-01 00:00:00');
            default:
                return date('Y-m-d 00:00:00', strtotime('-30 days'));
        }
    }

    /**
     * Helper: Get Period Days
     */
    private function _getPeriodDays($period = 'week')
    {
        switch ($period) {
            case 'today':
                return 1;
            case 'week':
                return 7;
            case 'month':
                return date('t');
            case 'quarter':
                return 90;
            default:
                return 30;
        }
    }

    /**
     * Helper method for future API integration
     * 
     * @param string $url The API endpoint URL
     * @param string $method HTTP method (GET, POST, etc.)
     * @param array $data Request data for POST/PUT
     * @return array Response data
     */
    private function makeApiCall($url, $method = 'GET', $data = [])
    {
        $ch = curl_init();
        
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        
        if (!empty($data)) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Content-Length: ' . strlen(json_encode($data))
            ]);
        }
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode === 200) {
            return json_decode($response, true);
        }
        
        return [
            'success' => false,
            'error' => 'API call failed',
            'http_code' => $httpCode
        ];
    }
}