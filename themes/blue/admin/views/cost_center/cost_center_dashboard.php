<?php defined('BASEPATH') or exit('No direct script access allowed');

// Handle budget API requests
if (isset($_GET['budget_api'])) {
    $action = isset($_GET['action']) ? $_GET['action'] : '';
    $period = isset($_GET['period']) ? $_GET['period'] : date('Y-m');
    
    $response = [
        'success' => false,
        'data' => [],
        'message' => 'Unknown action',
        'debug' => [
            'action' => $action,
            'period' => $period,
            'get_instance_check' => false,
            'db_check' => false
        ]
    ];

    try {
        // Get the CI instance from global scope
        $CI = &get_instance();
        $response['debug']['get_instance_check'] = $CI !== null;
        
        // Check if database connection is available
        if (!$CI || !isset($CI->db)) {
            throw new Exception('Database connection not available. CI=' . ($CI ? 'OK' : 'NULL'));
        }
        
        $response['debug']['db_check'] = true;
        
        switch ($action) {
            case 'allocated':
                // Get budget allocations
                $query = "SELECT * FROM sma_budget_allocation WHERE period = ? AND is_active = 1 ORDER BY allocated_at DESC";
                $result = $CI->db->query($query, [$period]);
                $response = [
                    'success' => true,
                    'data' => $result->result_array() ?: [],
                    'query' => $query,
                    'period_param' => $period
                ];
                break;

            case 'tracking':
                // Get budget tracking data
                $query = "SELECT * FROM sma_budget_tracking WHERE period = ? ORDER BY calculated_at DESC";
                $result = $CI->db->query($query, [$period]);
                $response = [
                    'success' => true,
                    'data' => $result->result_array() ?: [],
                    'query' => $query,
                    'period_param' => $period,
                    'count' => $result->num_rows()
                ];
                break;

            case 'forecast':
                // Get forecast data
                $query = "SELECT * FROM sma_budget_forecast WHERE period = ? ORDER BY calculated_at DESC";
                $result = $CI->db->query($query, [$period]);
                $response = [
                    'success' => true,
                    'data' => $result->result_array() ?: [],
                    'query' => $query,
                    'period_param' => $period
                ];
                break;

            case 'alerts':
                // Get alerts (joined with allocation for period)
                $query = "SELECT ae.*, ba.period FROM sma_budget_alert_events ae 
                         JOIN sma_budget_allocation ba ON ae.allocation_id = ba.allocation_id 
                         WHERE ba.period = ? AND ae.status = 'active' 
                         ORDER BY ae.triggered_at DESC";
                $result = $CI->db->query($query, [$period]);
                $response = [
                    'success' => true,
                    'data' => $result->result_array() ?: [],
                    'query' => $query,
                    'period_param' => $period
                ];
                break;
                
            default:
                throw new Exception("Unknown action: $action");
        }
        
    } catch (Throwable $e) {
        $response = [
            'success' => false,
            'message' => 'Error: ' . $e->getMessage(),
            'error_file' => $e->getFile(),
            'error_line' => $e->getLine(),
            'debug' => $response['debug']
        ];
        error_log('Budget dashboard error: ' . $e->getMessage() . ' at ' . $e->getFile() . ':' . $e->getLine());
    }

    // Output JSON and exit
    header('Content-Type: application/json');
    http_response_code($response['success'] ? 200 : 500);
    echo json_encode($response);
    exit;
}

?>

<div class="box">
    <!-- Box Header with Period Selector -->
    <div class="box-header">
        <h2 class="blue"><i class="fa fa-pie-chart"></i> <?= lang('Cost Center Dashboard'); ?> & Budget Management</h2>
        
        <div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown"><i class="fa fa-calendar"></i></a>
                    <ul class="dropdown-menu" role="menu">
                        <li>
                            <div class="dropdown-content" style="padding: 10px; width: 300px;">
                                <div class="form-group">
                                    <label>Period (YYYY-MM):</label>
                                    <input type="month" id="budgetPeriod" class="form-control form-control-sm" />
                                </div>
                                <button class="btn btn-sm btn-primary" onclick="applyBudgetPeriod()" style="margin-right: 5px;">Apply</button>
                                <button class="btn btn-sm btn-secondary" onclick="resetBudgetPeriod()">Reset</button>
                                <hr style="margin: 10px 0;">
                                <div class="form-group">
                                    <label>Cost Center Date Range:</label>
                                    <div style="display: flex; gap: 10px;">
                                        <input type="date" id="fromDate" class="form-control form-control-sm" style="flex: 1;" />
                                        <span id="fromDateLabel" style="display: flex; align-items: center; padding: 8px; background: #f5f5f5; border-radius: 4px; min-width: 100px; text-align: center; font-weight: bold;">-</span>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label>To Date:</label>
                                    <div style="display: flex; gap: 10px;">
                                        <input type="date" id="toDate" class="form-control form-control-sm" style="flex: 1;" />
                                        <span id="toDateLabel" style="display: flex; align-items: center; padding: 8px; background: #f5f5f5; border-radius: 4px; min-width: 100px; text-align: center; font-weight: bold;">-</span>
                                    </div>
                                </div>
                                <button class="btn btn-sm btn-primary" onclick="applyDateFilter()" style="margin-right: 5px;">Apply</button>
                                <button class="btn btn-sm btn-secondary" onclick="resetDateFilter()">Reset</button>
                            </div>
                        </li>
                    </ul>
                </li>
                <li>
                    <a href="javascript:location.reload()" title="Refresh">
                        <i class="fa fa-refresh"></i>
                    </a>
                </li>
            </ul>
        </div>
    </div>

    <div class="box-content">
        <!-- Top KPI Cards Row -->
        <div class="row">
            <!-- Sales KPI with Trend (Simplified) -->
            <div class="col-lg-3 col-sm-6 col-xs-12">
                <div class="stat-card indigo">
                    <div class="fs-4 fw-semibold"><span id="totalSales">-</span> <span class="fs-6 fw-normal">(<span id="salesTrendValue">+1.2%</span> <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" class="icon" role="img" aria-hidden="true"><polygon fill="currentColor" points="367.997 338.75 271.999 434.747 271.999 17.503 239.999 17.503 239.999 434.745 144.003 338.75 121.376 361.377 256 496 390.624 361.377 367.997 338.75" class="ci-primary"></polygon></svg>)</span></div>
                    <small class="text-white-75">Total Sales</small>
                </div>
            </div>

            <!-- Expenses KPI with Trend (Simplified) -->
            <div class="col-lg-3 col-sm-6 col-xs-12">
                <div class="stat-card light-blue">
                    <div class="fs-4 fw-semibold"><span id="totalExpenses">-</span> <span class="fs-6 fw-normal">(<span id="expensesTrendValue">+0.8%</span> <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" class="icon" role="img" aria-hidden="true"><polygon fill="currentColor" points="367.997 338.75 271.999 434.747 271.999 17.503 239.999 17.503 239.999 434.745 144.003 338.75 121.376 361.377 256 496 390.624 361.377 367.997 338.75" class="ci-primary"></polygon></svg>)</span></div>
                    <small class="text-white-75">Total Expenses</small>
                </div>
            </div>

            <!-- Best Performing Pharmacy (Simplified) -->
            <div class="col-lg-3 col-sm-6 col-xs-12">
                <div class="stat-card green">
                    <div class="fs-4 fw-semibold"><span id="bestPharmacy">-</span> <span class="fs-6 fw-normal">(<span id="bestPharmacySales">-</span>)</span></div>
                    <small class="text-white-75">Best Pharmacy</small>
                </div>
            </div>

            <!-- Worst Performing Pharmacy (Simplified) -->
            <div class="col-lg-3 col-sm-6 col-xs-12">
                <div class="stat-card red">
                    <div class="fs-4 fw-semibold"><span id="worstPharmacy">-</span> <span class="fs-6 fw-normal">(<span id="worstPharmacySales">-</span>)</span></div>
                    <small class="text-white-75">Worst Pharmacy</small>
                </div>
            </div>
        </div>

        <!-- Budget Management Section -->
        <div class="row" style="margin-top: 30px; border-top: 2px solid #e0e0e0; padding-top: 20px;">
            <div class="col-lg-12">
                <h3 style="color: #333; margin-bottom: 20px;"><i class="fa fa-credit-card"></i> Budget Allocation & Tracking</h3>
            </div>
        </div>

        <div class="row">
            <!-- Budget Allocated KPI (Simplified) -->
            <div class="col-lg-3 col-sm-6 col-xs-12">
                <div class="stat-card indigo">
                    <div class="fs-4 fw-semibold"><span id="budgetAllocated">-</span> <span class="fs-6 fw-normal">(<span id="budgetPeriodLabel">-</span>)</span></div>
                    <small class="text-white-75">Total Budget Allocated</small>
                </div>
            </div>

            <!-- Budget Spent KPI (Simplified) -->
            <div class="col-lg-3 col-sm-6 col-xs-12">
                <div class="stat-card yellow">
                    <div class="fs-4 fw-semibold"><span id="budgetSpent">-</span> <span class="fs-6 fw-normal">(<span id="budgetPercentage">-</span>)</span></div>
                    <small class="text-dark-75">Budget Spent</small>
                </div>
            </div>

            <!-- Budget Remaining KPI (Simplified) -->
            <div class="col-lg-3 col-sm-6 col-xs-12">
                <div class="stat-card green">
                    <div class="fs-4 fw-semibold"><span id="budgetRemaining">-</span> <span class="fs-6 fw-normal">(<span id="budgetStatus">-</span>)</span></div>
                    <small class="text-white-75">Budget Remaining</small>
                </div>
            </div>

            <!-- Budget Forecast KPI (Simplified) -->
            <div class="col-lg-3 col-sm-6 col-xs-12">
                <div class="stat-card light-blue">
                    <div class="fs-4 fw-semibold"><span id="budgetForecast">-</span> <span class="fs-6 fw-normal">(<span id="riskLevel">Low Risk</span>)</span></div>
                    <small class="text-white-75">Projected End-of-Month</small>
                </div>
            </div>
        </div>

        <!-- Budget Alerts Section -->
        <div class="row" style="margin-top: 20px;">
            <div class="col-lg-12">
                <div class="box">
                    <div class="box-header">
                        <h4><i class="fa fa-bell"></i> Budget Alerts & Warnings</h4>
                    </div>
                    <div class="box-content">
                        <div id="budgetAlertsContainer" style="min-height: 60px;">
                            <div class="alert alert-info">
                                <i class="fa fa-info-circle"></i> Loading budget alerts...
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Budget Progress Meter -->
        <div class="row" style="margin-top: 20px;">
            <div class="col-lg-12">
                <div class="box">
                    <div class="box-header">
                        <h4><i class="fa fa-bar-chart"></i> Budget Usage Progress</h4>
                    </div>
                    <div class="box-content">
                        <div style="padding: 20px;">
                            <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                                <strong>Budget Utilization</strong>
                                <span id="budgetMeterPercentage" style="font-weight: bold;">0%</span>
                            </div>
                            <div style="width: 100%; height: 30px; background: #f0f0f0; border-radius: 15px; overflow: hidden; border: 2px solid #e0e0e0;">
                                <div id="budgetMeterBar" style="width: 0%; height: 100%; background: linear-gradient(90deg, #10B981 0%, #F59E0B 50%, #EF4444 100%); transition: width 0.5s ease; display: flex; align-items: center; justify-content: center;">
                                    <span id="budgetMeterText" style="color: white; font-weight: bold; font-size: 12px;">0%</span>
                                </div>
                            </div>
                            <div style="display: flex; justify-content: space-between; margin-top: 10px; font-size: 12px; color: #666;">
                                <span>0 SAR</span>
                                <span id="budgetMeterMax">150,000 SAR</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row" style="margin-top: 20px;">
            <!-- Sales vs Cost Trend Chart -->
            <div class="col-lg-8 col-md-12">
                <div class="box">
                    <div class="box-header">
                        <h4><i class="fa fa-line-chart"></i> Sales vs Expenses Monthly Trend</h4>
                    </div>
                    <div class="box-content">
                        <div id="trendChart" style="width: 100%; height: 400px;"></div>
                    </div>
                </div>
            </div>

            <!-- Balance Sheet Status -->
            <div class="col-lg-4 col-md-12">
                <div class="box">
                    <div class="box-header">
                        <h4><i class="fa fa-balance-scale"></i> Balance Sheet Status</h4>
                    </div>
                    <div class="box-content">
                        <div id="balanceStatus" style="padding: 20px; text-align: center;">
                            <div style="margin-bottom: 15px;">
                                <h3 id="balanceStatusText">Matching</h3>
                                <span id="balanceStatusIcon" style="font-size: 40px; color: green;">✓</span>
                            </div>
                            <div class="row" style="border-top: 1px solid #eee; padding-top: 15px;">
                                <div class="col-xs-6">
                                    <small>Total Assets</small><br>
                                    <strong id="totalAssets">-</strong>
                                </div>
                                <div class="col-xs-6">
                                    <small>Total Liabilities</small><br>
                                    <strong id="totalLiabilities">-</strong>
                                </div>
                            </div>
                            <div style="margin-top: 10px; padding-top: 10px; border-top: 1px solid #eee;">
                                <small>Variance</small><br>
                                <strong id="balanceVariance" style="color: green;">0 SAR</strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Major Costs & Insights Row -->
        <div class="row" style="margin-top: 20px;">
            <!-- Major Costs List -->
            <div class="col-lg-6 col-md-12">
                <div class="box">
                    <div class="box-header">
                        <h4><i class="fa fa-list"></i> Major Costs</h4>
                    </div>
                    <div class="box-content">
                        <div id="majorCostsList" style="max-height: 400px; overflow-y: auto;">
                            <p class="text-muted text-center">Loading...</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Performance Insights -->
            <div class="col-lg-6 col-md-12">
                <div class="box">
                    <div class="box-header">
                        <h4><i class="fa fa-lightbulb-o"></i> Performance Insights</h4>
                    </div>
                    <div class="box-content" style="max-height: 400px; overflow-y: auto;">
                        <div id="insightsPanel">
                            <div style="margin-bottom: 15px;">
                                <h5><i class="fa fa-check-circle" style="color: green;"></i> What's Going Well</h5>
                                <ul id="goingWellList" style="margin-left: 20px;">
                                    <li>Loading...</li>
                                </ul>
                            </div>
                            <div style="border-top: 1px solid #eee; padding-top: 15px;">
                                <h5><i class="fa fa-exclamation-circle" style="color: #ff6b6b;"></i> Areas to Improve</h5>
                                <ul id="toImproveList" style="margin-left: 20px;">
                                    <li>Loading...</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Underperforming Pharmacies Detail Table -->
        <div class="row" style="margin-top: 20px;">
            <div class="col-lg-12">
                <div class="box">
                    <div class="box-header">
                        <h4><i class="fa fa-warning"></i> Underperforming Pharmacies & Branches</h4>
                    </div>
                    <div class="box-content">
                        <table class="table table-hover table-bordered">
                            <thead>
                                <tr class="bg-light">
                                    <th>Pharmacy / Branch</th>
                                    <th class="text-right">Sales</th>
                                    <th class="text-right">Expenses</th>
                                    <th class="text-right">Profit Margin %</th>
                                    <th class="text-right">Status</th>
                                </tr>
                            </thead>
                            <tbody id="underperformingTable">
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">Loading...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="<?php echo $assets; ?>js/echarts.min.js"></script>

<script>
// Global Variables
let fromDate = null;
let toDate = null;
let budgetPeriod = null;

// Initialize dates and budget data on load
document.addEventListener('DOMContentLoaded', function() {
    initializeDateRange();
    initializeBudgetPeriod();
    loadDashboardData();
});

// Initialize budget period (default: current month)
function initializeBudgetPeriod() {
    const today = new Date();
    const month = String(today.getMonth() + 1).padStart(2, '0');
    const year = today.getFullYear();
    budgetPeriod = `${year}-${month}`;
    
    const periodInput = document.getElementById('budgetPeriod');
    if (periodInput) {
        periodInput.value = budgetPeriod;
    }
}

// Apply budget period
function applyBudgetPeriod() {
    budgetPeriod = document.getElementById('budgetPeriod').value;
    
    if (!budgetPeriod) {
        alert('Please select a budget period');
        return;
    }
    
    loadDashboardData();
    // Hide dropdown
    document.querySelector('.box-icon .dropdown-toggle').click();
}

// Reset budget period
function resetBudgetPeriod() {
    initializeBudgetPeriod();
    loadDashboardData();
}

// Initialize date range (default: current month)
function initializeDateRange() {
    const today = new Date();
    const firstDay = new Date(today.getFullYear(), today.getMonth(), 1);
    
    // Format dates as YYYY-MM-DD for input (internal format)
    const fromValue = firstDay.toISOString().split('T')[0];
    const toValue = today.toISOString().split('T')[0];
    
    const fromInput = document.getElementById('fromDate');
    const toInput = document.getElementById('toDate');
    
    fromInput.value = fromValue;
    toInput.value = toValue;
    
    fromDate = fromValue;
    toDate = toValue;
    
    // Display dates in DD/MM/YYYY format in labels
    updateDateLabels();
}

// Apply date filter
function applyDateFilter() {
    fromDate = document.getElementById('fromDate').value;
    toDate = document.getElementById('toDate').value;
    
    if (!fromDate || !toDate) {
        alert('Please select both dates');
        return;
    }
    
    if (fromDate > toDate) {
        alert('From date must be before To date');
        return;
    }
    
    updateDateLabels();
    loadDashboardData();
    // Hide dropdown
    document.querySelector('.box-icon .dropdown-toggle').click();
}

// Update date labels to show DD/MM/YYYY format
function updateDateLabels() {
    const fromValue = document.getElementById('fromDate').value;
    const toValue = document.getElementById('toDate').value;
    
    const fromLabel = document.getElementById('fromDateLabel');
    const toLabel = document.getElementById('toDateLabel');
    
    if (fromLabel && fromValue) {
        fromLabel.textContent = formatDateForDisplay(fromValue);
    }
    if (toLabel && toValue) {
        toLabel.textContent = formatDateForDisplay(toValue);
    }
}

// Format date from YYYY-MM-DD to DD/MM/YYYY
function formatDateForDisplay(dateStr) {
    if (!dateStr) return '-';
    const [year, month, day] = dateStr.split('-');
    return `${day}/${month}/${year}`;
}

// Reset date filter
function resetDateFilter() {
    initializeDateRange();
    loadDashboardData();
    // Hide dropdown
    document.querySelector('.box-icon .dropdown-toggle').click();
}

// Main function to load all dashboard data
function loadDashboardData() {
    console.log('Loading dashboard data...');
    console.log('Budget Period:', budgetPeriod);
    console.log('Cost Center Date Range:', fromDate, 'to', toDate);
    
    // Load Cost Center data (existing)
    const costCenterData = generateMockData();
    updateKPICards(costCenterData);
    initializeTrendChart(costCenterData);
    updateBalanceSheetStatus(costCenterData);
    updateMajorCostsList(costCenterData);
    updatePerformanceInsights(costCenterData);
    updateUnderperformingTable(costCenterData);
    
    // Load Budget data from API
    loadBudgetData();
}

// Load budget data from local endpoint
function loadBudgetData() {
    // Show loading state
    showBudgetLoading();
    
    // Use the built-in dashboard API handler via query parameters
    // The backend handler is in this same view file (lines 1-90)
    // Calls the dashboard page with budget_api parameter to trigger the handler
    const currentPageUrl = window.location.pathname + window.location.search;
    
    Promise.all([
        fetch(`${currentPageUrl}${currentPageUrl.includes('?') ? '&' : '?'}budget_api=1&action=allocated&period=${budgetPeriod}`)
            .then(r => {
                console.log('Allocated response:', r.status);
                if (!r.ok) {
                    return r.text().then(text => {
                        throw new Error(`HTTP ${r.status}: ${text.substring(0, 200)}`);
                    });
                }
                return r.json();
            })
            .catch(e => {
                console.error('Error fetching allocated budget:', e);
                return { data: [] };
            }),
        fetch(`${currentPageUrl}${currentPageUrl.includes('?') ? '&' : '?'}budget_api=1&action=tracking&period=${budgetPeriod}`)
            .then(r => {
                console.log('Tracking response:', r.status);
                if (!r.ok) {
                    return r.text().then(text => {
                        throw new Error(`HTTP ${r.status}: ${text.substring(0, 200)}`);
                    });
                }
                return r.json();
            })
            .catch(e => {
                console.error('Error fetching budget tracking:', e);
                return { data: [] };
            }),
        fetch(`${currentPageUrl}${currentPageUrl.includes('?') ? '&' : '?'}budget_api=1&action=forecast&period=${budgetPeriod}`)
            .then(r => {
                console.log('Forecast response:', r.status);
                if (!r.ok) {
                    return r.text().then(text => {
                        throw new Error(`HTTP ${r.status}: ${text.substring(0, 200)}`);
                    });
                }
                return r.json();
            })
            .catch(e => {
                console.error('Error fetching forecast:', e);
                return { data: [] };
            }),
        fetch(`${currentPageUrl}${currentPageUrl.includes('?') ? '&' : '?'}budget_api=1&action=alerts&period=${budgetPeriod}`)
            .then(r => {
                console.log('Alerts response:', r.status);
                if (!r.ok) {
                    return r.text().then(text => {
                        throw new Error(`HTTP ${r.status}: ${text.substring(0, 200)}`);
                    });
                }
                return r.json();
            })
            .catch(e => {
                console.error('Error fetching alerts:', e);
                return { data: [] };
            })
    ])
    .then(([allocatedData, trackingData, forecastData, alertsData]) => {
        console.log('Budget data loaded successfully:', { allocatedData, trackingData, forecastData, alertsData });
        
        // Process and display budget data
        const budgetInfo = processBudgetData(allocatedData, trackingData, forecastData, alertsData);
        
        if (!budgetInfo) {
            console.warn('Budget info processing failed, using fallback data');
            loadBudgetDataFallback();
            return;
        }
        
        updateBudgetKPICards(budgetInfo);
        updateBudgetMeter(budgetInfo);
        updateBudgetAlerts(budgetInfo);
        
        hideBudgetLoading();
    })
    .catch(error => {
        console.error('Failed to load budget data:', error);
        // Use fallback demo data if API fails
        loadBudgetDataFallback();
    });
}

// Process budget data from API responses
function processBudgetData(allocated, tracking, forecast, alerts) {
    try {
        console.log('Processing budget data:', { allocated, tracking, forecast, alerts });
        
        // Extract data arrays from responses
        // Handle both direct array and object with .data property
        const allocatedArray = allocated?.data || allocated || [];
        const trackingArray = tracking?.data || tracking || [];
        const forecastArray = forecast?.data || forecast || [];
        const alertsArray = alerts?.data || alerts || [];
        
        console.log('Extracted arrays:', { allocatedArray, trackingArray, forecastArray, alertsArray });
        
        // Find company-level tracking (first or matching hierarchy_level === 'company')
        let companyTracking = trackingArray.find(t => t.hierarchy_level === 'company') || 
                             trackingArray[0] || 
                             {};
        
        // Find company-level forecast
        let companyForecast = forecastArray.find(f => f.hierarchy_level === 'company') || 
                             forecastArray[0] || 
                             {};
        
        console.log('Company tracking:', companyTracking);
        console.log('Company forecast:', companyForecast);
        
        // Calculate percentages if not provided
        let percentageUsed = companyTracking.percentage_used || 0;
        if (!percentageUsed && companyTracking.allocated_amount && companyTracking.actual_spent) {
            percentageUsed = (companyTracking.actual_spent / companyTracking.allocated_amount * 100).toFixed(2);
        }
        
        let status = companyTracking.status || 'safe';
        if (percentageUsed > 100) {
            status = 'exceeded';
        } else if (percentageUsed > 80) {
            status = 'warning';
        } else if (percentageUsed > 50) {
            status = 'warning';
        }
        
        return {
            allocated: parseFloat(companyTracking.allocated_amount || 0),
            spent: parseFloat(companyTracking.actual_spent || 0),
            remaining: parseFloat(companyTracking.remaining_amount || companyTracking.allocated_amount - companyTracking.actual_spent || 0),
            percentageUsed: parseFloat(percentageUsed),
            status: status,
            projected: parseFloat(companyForecast.projected_end_of_month || 0),
            burnRate: parseFloat(companyForecast.burn_rate_daily || 0),
            riskLevel: companyForecast.risk_level || 'low',
            confidence: parseInt(companyForecast.confidence_score || 0),
            activeAlerts: alertsArray.filter(a => a.status === 'active') || [],
            period: budgetPeriod
        };
    } catch (e) {
        console.error('Error processing budget data:', e);
        console.error('Stack:', e.stack);
        console.error('Allocated:', allocated);
        console.error('Tracking:', tracking);
        console.error('Forecast:', forecast);
        console.error('Alerts:', alerts);
        return null;
    }
}

// Fallback budget data if API fails
function loadBudgetDataFallback() {
    console.log('Using fallback budget data...');
    
    const fallbackData = {
        allocated: 150000.00,
        spent: 975.00,
        remaining: 149025.00,
        percentageUsed: 0.65,
        status: 'safe',
        projected: 6435.00,
        burnRate: 97.50,
        riskLevel: 'low',
        confidence: 85,
        activeAlerts: [],
        period: budgetPeriod
    };
    
    updateBudgetKPICards(fallbackData);
    updateBudgetMeter(fallbackData);
    updateBudgetAlerts(fallbackData);
}

// Show budget loading state
function showBudgetLoading() {
    const budgetAllocatedEl = document.getElementById('budgetAllocated');
    const budgetSpentEl = document.getElementById('budgetSpent');
    const budgetRemainingEl = document.getElementById('budgetRemaining');
    const budgetForecastEl = document.getElementById('budgetForecast');
    
    if (budgetAllocatedEl) budgetAllocatedEl.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Loading...';
    if (budgetSpentEl) budgetSpentEl.innerHTML = '<i class="fa fa-spinner fa-spin"></i>';
    if (budgetRemainingEl) budgetRemainingEl.innerHTML = '<i class="fa fa-spinner fa-spin"></i>';
    if (budgetForecastEl) budgetForecastEl.innerHTML = '<i class="fa fa-spinner fa-spin"></i>';
}

// Hide budget loading state
function hideBudgetLoading() {
    // Charts will be updated by updateBudgetKPICards
}

// Update Budget KPI Cards
function updateBudgetKPICards(budgetData) {
    if (!budgetData) return;
    
    // Budget Allocated
    const budgetAllocatedEl = document.getElementById('budgetAllocated');
    if (budgetAllocatedEl) budgetAllocatedEl.textContent = formatNumber(budgetData.allocated / 1000) + 'K';
    const budgetPeriodLabelEl = document.getElementById('budgetPeriodLabel');
    if (budgetPeriodLabelEl) budgetPeriodLabelEl.textContent = budgetData.period;
    
    // Budget Spent
    const budgetSpentEl = document.getElementById('budgetSpent');
    if (budgetSpentEl) budgetSpentEl.textContent = formatNumber(budgetData.spent / 1000) + 'K';
    const budgetPercentageEl = document.getElementById('budgetPercentage');
    if (budgetPercentageEl) budgetPercentageEl.textContent = budgetData.percentageUsed.toFixed(2) + '%';
    
    // Budget Remaining
    const budgetRemainingEl = document.getElementById('budgetRemaining');
    if (budgetRemainingEl) budgetRemainingEl.textContent = formatNumber(budgetData.remaining / 1000) + 'K';
    
    const statusText = budgetData.status === 'safe' ? '✓ Safe' : 
                      (budgetData.status === 'warning' ? '⚠ Warning' :
                       (budgetData.status === 'danger' ? '⚠ Danger' : '✕ Exceeded'));
    const budgetStatusEl = document.getElementById('budgetStatus');
    if (budgetStatusEl) budgetStatusEl.textContent = statusText;
    
    // Budget Forecast
    const budgetForecastEl = document.getElementById('budgetForecast');
    if (budgetForecastEl) budgetForecastEl.textContent = formatNumber(budgetData.projected / 1000) + 'K';
    
    const riskLabel = budgetData.riskLevel === 'low' ? 'Low Risk ✓' :
                     (budgetData.riskLevel === 'medium' ? 'Medium Risk ⚠' :
                      (budgetData.riskLevel === 'high' ? 'High Risk ⚠' : 'Critical ✕'));
    const riskLevelEl = document.getElementById('riskLevel');
    if (riskLevelEl) riskLevelEl.textContent = riskLabel;
}

// Update Budget Meter
function updateBudgetMeter(budgetData) {
    if (!budgetData) return;
    
    const percentage = Math.min(budgetData.percentageUsed, 100);
    const meterBar = document.getElementById('budgetMeterBar');
    const meterText = document.getElementById('budgetMeterText');
    const meterPercentage = document.getElementById('budgetMeterPercentage');
    const meterMax = document.getElementById('budgetMeterMax');
    
    // Determine color based on percentage
    let color = '#10B981'; // Green (0-50%)
    if (percentage > 80) {
        color = '#EF4444'; // Red (>80%)
    } else if (percentage > 50) {
        color = '#F59E0B'; // Yellow (50-80%)
    }
    
    meterBar.style.width = percentage + '%';
    meterBar.style.background = color;
    meterText.textContent = percentage.toFixed(1) + '%';
    meterPercentage.textContent = percentage.toFixed(1) + '%';
    meterMax.textContent = 'SAR ' + formatNumber(budgetData.allocated);
}

// Update Budget Alerts
function updateBudgetAlerts(budgetData) {
    const container = document.getElementById('budgetAlertsContainer');
    
    if (!budgetData || budgetData.activeAlerts.length === 0) {
        container.innerHTML = '<div class="alert alert-success"><i class="fa fa-check-circle"></i> No active budget alerts. Budget is under control.</div>';
        return;
    }
    
    let alertsHTML = '';
    budgetData.activeAlerts.forEach(alert => {
        const alertClass = alert.risk_level === 'high' ? 'alert-danger' :
                          (alert.risk_level === 'medium' ? 'alert-warning' : 'alert-info');
        
        alertsHTML += `
            <div class="alert ${alertClass}">
                <i class="fa fa-bell"></i> 
                <strong>${alert.entity_name}</strong> - Threshold: ${alert.percentage_at_trigger}% 
                (Current: ${alert.current_percentage}%) 
                <small style="color: #666;">Triggered: ${new Date(alert.triggered_at).toLocaleString()}</small>
            </div>
        `;
    });
    
    container.innerHTML = alertsHTML;
}

// Generate mock data (replace with API call in production)
function generateMockData() {
    return {
        totalSales: 1250000,
        salesTrend: 12.5,
        totalExpenses: 750000,
        expensesTrend: -8.3,
        bestPharmacy: {
            name: 'Pharmacy A',
            sales: 450000
        },
        worstPharmacy: {
            name: 'Pharmacy C',
            sales: 180000
        },
        monthlyTrend: [
            { month: 'Oct 2024', sales: 1100000, expenses: 680000 },
            { month: 'Nov 2024', sales: 1180000, expenses: 720000 },
            { month: 'Dec 2024', sales: 1250000, expenses: 750000 },
            { month: 'Jan 2025', sales: 1200000, expenses: 740000 },
            { month: 'Feb 2025', sales: 1280000, expenses: 760000 },
        ],
        balanceSheet: {
            assets: 5000000,
            liabilities: 4999500,
            variance: 500
        },
        majorCosts: [
            { name: 'COGS', amount: 450000, percentage: 60 },
            { name: 'Staff Salaries', amount: 180000, percentage: 24 },
            { name: 'Rent & Utilities', amount: 80000, percentage: 11 },
            { name: 'Delivery & Transport', amount: 25000, percentage: 3 },
            { name: 'Marketing', amount: 15000, percentage: 2 }
        ],
        insights: {
            wellPerforming: [
                'Pharmacy A leading with 450K in sales (36% of total)',
                'Overall sales trend up 12.5% vs previous period',
                'Expense control improved - 8.3% reduction'
            ],
            needsImprovement: [
                'Pharmacy C underperforming - only 180K sales',
                'Branch 5 has negative profit margin of -2%',
                'Inventory movement costs exceeding budget by 15%'
            ]
        },
        underperforming: [
            {
                name: 'Pharmacy C - Branch 006',
                sales: 180000,
                expenses: 185000,
                margin: -2.7,
                status: 'Critical'
            },
            {
                name: 'Pharmacy B - Branch 005',
                sales: 220000,
                expenses: 215000,
                margin: 2.3,
                status: 'Warning'
            },
            {
                name: 'Branch 004',
                sales: 250000,
                expenses: 242000,
                margin: 3.2,
                status: 'Alert'
            }
        ]
    };
}

// Update KPI Cards
function updateKPICards(data) {
    // Sales KPI
    const totalSalesEl = document.getElementById('totalSales');
    if (totalSalesEl) totalSalesEl.textContent = formatNumber(data.totalSales / 1000) + 'K';
    
    const salesTrendEl = document.getElementById('salesTrendValue');
    if (salesTrendEl) salesTrendEl.textContent = (data.salesTrend >= 0 ? '+' : '') + Math.abs(data.salesTrend).toFixed(1) + '%';
    
    // Expenses KPI
    const totalExpensesEl = document.getElementById('totalExpenses');
    if (totalExpensesEl) totalExpensesEl.textContent = formatNumber(data.totalExpenses / 1000) + 'K';
    
    const expensesTrendEl = document.getElementById('expensesTrendValue');
    if (expensesTrendEl) expensesTrendEl.textContent = (data.expensesTrend >= 0 ? '+' : '') + Math.abs(data.expensesTrend).toFixed(1) + '%';
    
    // Best Pharmacy
    const bestPharmacyEl = document.getElementById('bestPharmacy');
    if (bestPharmacyEl) bestPharmacyEl.textContent = data.bestPharmacy.name;
    
    const bestPharmacySalesEl = document.getElementById('bestPharmacySales');
    if (bestPharmacySalesEl) bestPharmacySalesEl.textContent = 'SAR ' + formatNumber(data.bestPharmacy.sales);
    
    // Worst Pharmacy
    const worstPharmacyEl = document.getElementById('worstPharmacy');
    if (worstPharmacyEl) worstPharmacyEl.textContent = data.worstPharmacy.name;
    
    const worstPharmacySalesEl = document.getElementById('worstPharmacySales');
    if (worstPharmacySalesEl) worstPharmacySalesEl.textContent = 'SAR ' + formatNumber(data.worstPharmacy.sales);
}

// Initialize Trend Chart with ECharts
function initializeTrendChart(data) {
    const chartDom = document.getElementById('trendChart');
    const myChart = echarts.init(chartDom);
    
    const months = data.monthlyTrend.map(d => d.month);
    const sales = data.monthlyTrend.map(d => d.sales);
    const expenses = data.monthlyTrend.map(d => d.expenses);
    
    const option = {
        tooltip: {
            trigger: 'axis',
            formatter: function(params) {
                let result = params[0].axisValue + '<br>';
                params.forEach(p => {
                    result += p.marker + p.name + ': SAR ' + formatNumber(p.value) + '<br>';
                });
                return result;
            }
        },
        legend: {
            data: ['Sales', 'Expenses'],
            top: '3%'
        },
        grid: {
            left: '3%',
            right: '4%',
            bottom: '3%',
            top: '10%',
            containLabel: true
        },
        xAxis: {
            type: 'category',
            data: months,
            boundaryGap: false
        },
        yAxis: {
            type: 'value',
            axisLabel: {
                formatter: function(value) {
                    return 'SAR ' + (value / 100000).toFixed(0) + 'L';
                }
            }
        },
        series: [
            {
                name: 'Sales',
                type: 'line',
                data: sales,
                smooth: true,
                itemStyle: { color: '#3B82F6' },
                areaStyle: { color: 'rgba(59, 130, 246, 0.2)' }
            },
            {
                name: 'Expenses',
                type: 'line',
                data: expenses,
                smooth: true,
                itemStyle: { color: '#EF4444' },
                areaStyle: { color: 'rgba(239, 68, 68, 0.2)' }
            }
        ]
    };
    
    myChart.setOption(option);
    
    // Responsive resize
    window.addEventListener('resize', function() {
        myChart.resize();
    });
}

// Update Balance Sheet Status
function updateBalanceSheetStatus(data) {
    const variance = Math.abs(data.balanceSheet.variance);
    const isMatching = variance < 1000; // Less than 1000 SAR variance
    
    document.getElementById('balanceStatusText').textContent = isMatching ? 'Matching' : 'Variance Detected';
    const icon = document.getElementById('balanceStatusIcon');
    icon.textContent = isMatching ? '✓' : '✕';
    icon.style.color = isMatching ? 'green' : '#ff6b6b';
    
    document.getElementById('totalAssets').textContent = 'SAR ' + formatNumber(data.balanceSheet.assets);
    document.getElementById('totalLiabilities').textContent = 'SAR ' + formatNumber(data.balanceSheet.liabilities);
    
    const varianceElem = document.getElementById('balanceVariance');
    varianceElem.textContent = 'SAR ' + formatNumber(data.balanceSheet.variance);
    varianceElem.style.color = isMatching ? 'green' : '#ff6b6b';
}

// Update Major Costs List
function updateMajorCostsList(data) {
    let html = '';
    data.majorCosts.forEach(cost => {
        const progressColor = cost.percentage > 50 ? '#ff6b6b' : (cost.percentage > 30 ? '#fbbf24' : '#10b981');
        html += `
            <div style="margin-bottom: 15px;">
                <div style="display: flex; justify-content: space-between; margin-bottom: 5px;">
                    <strong>${cost.name}</strong>
                    <span>${cost.percentage}%</span>
                </div>
                <div style="width: 100%; height: 20px; background: #f0f0f0; border-radius: 3px; overflow: hidden;">
                    <div style="width: ${cost.percentage}%; height: 100%; background: ${progressColor};"></div>
                </div>
                <small style="color: #999;">SAR ${formatNumber(cost.amount)}</small>
            </div>
        `;
    });
    document.getElementById('majorCostsList').innerHTML = html;
}

// Update Performance Insights
function updatePerformanceInsights(data) {
    let wellHtml = data.insights.wellPerforming.map(insight => `<li>${insight}</li>`).join('');
    let improveHtml = data.insights.needsImprovement.map(insight => `<li>${insight}</li>`).join('');
    
    document.getElementById('goingWellList').innerHTML = wellHtml;
    document.getElementById('toImproveList').innerHTML = improveHtml;
}

// Update Underperforming Table
function updateUnderperformingTable(data) {
    let html = '';
    data.underperforming.forEach(item => {
        const statusBadge = item.status === 'Critical' ? '<span class="label label-danger">Critical</span>' :
                           (item.status === 'Warning' ? '<span class="label label-warning">Warning</span>' : 
                            '<span class="label label-info">Alert</span>');
        const marginColor = item.margin < 0 ? '#ef4444' : '#10b981';
        
        html += `
            <tr>
                <td><strong>${item.name}</strong></td>
                <td class="text-right">SAR ${formatNumber(item.sales)}</td>
                <td class="text-right">SAR ${formatNumber(item.expenses)}</td>
                <td class="text-right"><strong style="color: ${marginColor};">${item.margin.toFixed(1)}%</strong></td>
                <td class="text-right">${statusBadge}</td>
            </tr>
        `;
    });
    document.getElementById('underperformingTable').innerHTML = html;
}

// Helper function to format numbers
function formatNumber(num) {
    return Math.round(num).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',');
}
</script>

<style>
/* Info Box Styling */
.info-box {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-radius: 8px;
    padding: 20px;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    min-height: 120px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.info-box:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.info-box i {
    font-size: 36px;
    margin-right: 15px;
    opacity: 0.8;
}

.info-box-content {
    flex: 1;
}

.info-box-text {
    font-size: 13px;
    opacity: 0.95;
    margin-bottom: 8px;
    font-weight: 500;
}

.info-box-number {
    font-size: 24px;
    font-weight: bold;
    color: white;
    margin: 5px 0;
}

.info-box-number small {
    font-size: 12px;
    opacity: 0.9;
}

/* Stats Grid - New Simplified Cards */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
    gap: 1rem;
    margin-bottom: 2.5rem;
}

.stat-card {
    background-color: var(--stat-color);
    border-radius: 0.5rem;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
    overflow: hidden;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    padding: 1.5rem 1rem;
    color: white;
}

.stat-card:hover {
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.2);
    transform: translateY(-4px);
}

.stat-card.indigo {
    --stat-color: #4f46e5;
}

.stat-card.light-blue {
    --stat-color: #3b82f6;
}

.stat-card.yellow {
    --stat-color: #fbbf24;
    color: #1a1a1a;
}

.stat-card.red {
    --stat-color: #e55354;
}

.stat-card.green {
    --stat-color: #10b981;
}

.stat-card .fs-4 {
    font-size: 1.5rem !important;
}

.stat-card .fs-6 {
    font-size: 0.9rem !important;
}

.stat-card .icon {
    width: 12px;
    height: 12px;
    display: inline-block;
    margin-left: 4px;
    vertical-align: middle;
}

.stat-card .text-white-75 {
    color: rgba(255, 255, 255, 0.75);
    font-size: 0.875rem;
}

.stat-card .text-dark-75 {
    color: rgba(26, 26, 26, 0.75);
    font-size: 0.875rem;
}

/* Color variants */
.blue-bg {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
}

.orange-bg {
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%) !important;
}

.green-bg {
    background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%) !important;
}

.red-bg {
    background: linear-gradient(135deg, #fa709a 0%, #fee140 100%) !important;
}

.purple-bg {
    background: linear-gradient(135deg, #9b59b6 0%, #8e44ad 100%) !important;
}

/* Label Styling */
.label {
    padding: 6px 12px;
    border-radius: 4px;
    font-size: 11px;
    font-weight: bold;
    display: inline-block;
}

.label-danger {
    background-color: #ef4444;
    color: white;
}

.label-warning {
    background-color: #f59e0b;
    color: white;
}

.label-info {
    background-color: #3b82f6;
    color: white;
}

/* Chart Styling */
#trendChart {
    width: 100%;
    height: 400px;
}

/* Date Dropdown */
.dropdown-content {
    background: white;
    padding: 15px;
    border-radius: 4px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.dropdown-content .form-group {
    margin-bottom: 12px;
}

.dropdown-content label {
    font-size: 12px;
    margin-bottom: 5px;
    font-weight: 500;
    color: #333;
}

.dropdown-content input[type="date"] {
    width: 100%;
    padding: 8px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 13px;
}

.dropdown-content .btn {
    margin-right: 5px;
    margin-top: 10px;
}

/* Lists */
#majorCostsList, #insightsPanel {
    font-size: 13px;
}

/* Utility Classes */
.text-right {
    text-align: right;
}

.text-center {
    text-align: center;
}

.text-muted {
    color: #999;
}

.py-4 {
    padding-top: 16px;
    padding-bottom: 16px;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .info-box {
        min-height: 100px;
        padding: 15px;
    }
    
    .info-box i {
        font-size: 28px;
        margin-right: 10px;
    }
    
    .info-box-number {
        font-size: 20px;
    }
}
</style>
