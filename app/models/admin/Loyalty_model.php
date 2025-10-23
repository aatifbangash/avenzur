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
