<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa fa-trophy"></i> <?= lang('Loyalty Dashboard'); ?></h2>
        
        <div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown"><i class="fa fa-filter"></i></a>
                    <ul class="dropdown-menu" role="menu">
                        <li><a href="#" data-scope="company">Company Level</a></li>
                        <li><a href="#" data-scope="pharmacy_group">Pharmacy Group Level</a></li>
                        <li><a href="#" data-scope="pharmacy">Pharmacy Level</a></li>
                        <li><a href="#" data-scope="branch">Branch Level</a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
    <div class="box-content">
        <div class="row">
            <!-- Budget Status Cards -->
            <div class="col-lg-12">
                <div class="row">
                    <div class="col-lg-3 col-sm-6 col-xs-12">
                        <div class="info-box blue-bg">
                            <i class="fa fa-money"></i>
                            <div class="info-box-content">
                                <span class="info-box-text">Total Budget</span>
                                <span class="info-box-number" id="total_budget">-</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-sm-6 col-xs-12">
                        <div class="info-box orange-bg">
                            <i class="fa fa-shopping-cart"></i>
                            <div class="info-box-content">
                                <span class="info-box-text">Spent</span>
                                <span class="info-box-number" id="budget_spent">-</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-sm-6 col-xs-12">
                        <div class="info-box green-bg">
                            <i class="fa fa-check-circle"></i>
                            <div class="info-box-content">
                                <span class="info-box-text">Remaining</span>
                                <span class="info-box-number" id="budget_remaining">-</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-sm-6 col-xs-12">
                        <div class="info-box red-bg">
                            <i class="fa fa-fire"></i>
                            <div class="info-box-content">
                                <span class="info-box-text">Daily Burn Rate</span>
                                <span class="info-box-number" id="burn_rate">-</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Budget Status Gauge -->
            <div class="col-lg-6 col-md-12">
                <div class="box">
                    <div class="box-header">
                        <h4><i class="fa fa-tachometer"></i> Budget Utilization</h4>
                    </div>
                    <div class="box-content">
                        <div id="budgetGauge" style="width: 100%; height: 350px;"></div>
                    </div>
                </div>
            </div>

            <!-- Burn Rate Trend -->
            <div class="col-lg-6 col-md-12">
                <div class="box">
                    <div class="box-header">
                        <h4><i class="fa fa-line-chart"></i> Burn Rate Trend</h4>
                    </div>
                    <div class="box-content">
                        <div id="burnRateChart" style="width: 100%; height: 350px;"></div>
                    </div>
                </div>
            </div>

            <!-- Spending Trend -->
            <div class="col-lg-12">
                <div class="box">
                    <div class="box-header">
                        <h4><i class="fa fa-area-chart"></i> Monthly Spending Trend</h4>
                    </div>
                    <div class="box-content">
                        <div id="spendingTrendChart" style="width: 100%; height: 400px;"></div>
                    </div>
                </div>
            </div>

            <!-- Budget Breakdown by Type -->
            <div class="col-lg-6 col-md-12">
                <div class="box">
                    <div class="box-header">
                        <h4><i class="fa fa-pie-chart"></i> Spending by Discount Type</h4>
                    </div>
                    <div class="box-content">
                        <div id="discountTypeChart" style="width: 100%; height: 350px;"></div>
                    </div>
                </div>
            </div>

            <!-- Budget Breakdown by Branch -->
            <div class="col-lg-6 col-md-12">
                <div class="box">
                    <div class="box-header">
                        <h4><i class="fa fa-bar-chart"></i> Spending by Branch</h4>
                    </div>
                    <div class="box-content">
                        <div id="branchSpendingChart" style="width: 100%; height: 350px;"></div>
                    </div>
                </div>
            </div>

            <!-- Budget Projections -->
            <div class="col-lg-12">
                <div class="box">
                    <div class="box-header">
                        <h4><i class="fa fa-line-chart"></i> Budget Projections (Next 4 Months)</h4>
                    </div>
                    <div class="box-content">
                        <div id="projectionsChart" style="width: 100%; height: 400px;"></div>
                    </div>
                </div>
            </div>

            <!-- Alerts Panel -->
            <div class="col-lg-12">
                <div class="box">
                    <div class="box-header">
                        <h4><i class="fa fa-exclamation-triangle"></i> Budget Alerts</h4>
                    </div>
                    <div class="box-content">
                        <div id="alertsContainer" class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>Level</th>
                                        <th>Title</th>
                                        <th>Message</th>
                                        <th>Timestamp</th>
                                        <th>Action Required</th>
                                    </tr>
                                </thead>
                                <tbody id="alertsTableBody">
                                    <tr>
                                        <td colspan="5" class="text-center">Loading alerts...</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.info-box {
    display: block;
    min-height: 90px;
    background: #fff;
    width: 100%;
    box-shadow: 0 1px 1px rgba(0,0,0,0.1);
    border-radius: 2px;
    margin-bottom: 15px;
    padding: 10px;
}

.info-box-content {
    padding: 5px 10px;
    margin-left: 70px;
}

.info-box-text {
    text-transform: uppercase;
    display: block;
    font-size: 12px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.info-box-number {
    display: block;
    font-weight: bold;
    font-size: 18px;
}

.info-box > i {
    height: 70px;
    width: 70px;
    font-size: 40px;
    display: inline-block;
    text-align: center;
    line-height: 70px;
    border-radius: 2px;
    float: left;
}

.blue-bg {
    background-color: #3c8dbc !important;
    color: #fff !important;
}

.orange-bg {
    background-color: #ff851b !important;
    color: #fff !important;
}

.green-bg {
    background-color: #00a65a !important;
    color: #fff !important;
}

.red-bg {
    background-color: #dd4b39 !important;
    color: #fff !important;
}

.alert-badge {
    padding: 3px 8px;
    border-radius: 3px;
    color: #fff;
    font-size: 11px;
    font-weight: bold;
}

.alert-critical {
    background-color: #d9534f;
}

.alert-warning {
    background-color: #f0ad4e;
}

.alert-info {
    background-color: #5bc0de;
}
</style>

<script type="text/javascript">
$(document).ready(function() {
    var currentScope = 'company';
    var currentScopeId = 1;

    // Initialize all charts
    initializeDashboard();

    // Scope selector
    $('.dropdown-menu a[data-scope]').on('click', function(e) {
        e.preventDefault();
        currentScope = $(this).data('scope');
        initializeDashboard();
    });

    function initializeDashboard() {
        loadBudgetStatus();
        loadBurnRate();
        loadSpendingTrend();
        loadProjections();
        loadBudgetSummary();
        loadAlerts();
    }

    function loadBudgetStatus() {
        $.ajax({
            url: '<?= admin_url('loyalty/get_budget_status') ?>',
            type: 'GET',
            data: { scopeLevel: currentScope, scopeId: currentScopeId },
            success: function(response) {
                if (response.success) {
                    var data = response.data;
                    $('#total_budget').text(formatCurrency(data.total_budget));
                    $('#budget_spent').text(formatCurrency(data.spent));
                    $('#budget_remaining').text(formatCurrency(data.remaining));
                    
                    renderBudgetGauge(data.percentage_used);
                }
            }
        });
    }

    function loadBurnRate() {
        $.ajax({
            url: '<?= admin_url('loyalty/get_burn_rate') ?>',
            type: 'GET',
            data: { scopeLevel: currentScope, scopeId: currentScopeId },
            success: function(response) {
                if (response.success) {
                    var data = response.data;
                    $('#burn_rate').text(formatCurrency(data.daily_burn_rate));
                    renderBurnRateChart(data.historical_data);
                }
            }
        });
    }

    function loadSpendingTrend() {
        $.ajax({
            url: '<?= admin_url('loyalty/get_spending_trend') ?>',
            type: 'GET',
            data: { scopeLevel: currentScope, scopeId: currentScopeId, period: 'monthly' },
            success: function(response) {
                if (response.success) {
                    renderSpendingTrendChart(response.data.trend_data);
                }
            }
        });
    }

    function loadProjections() {
        $.ajax({
            url: '<?= admin_url('loyalty/get_projections') ?>',
            type: 'GET',
            data: { scopeLevel: currentScope, scopeId: currentScopeId },
            success: function(response) {
                if (response.success) {
                    renderProjectionsChart(response.data.projections_by_month);
                }
            }
        });
    }

    function loadBudgetSummary() {
        $.ajax({
            url: '<?= admin_url('loyalty/get_summary') ?>',
            type: 'GET',
            data: { scopeLevel: currentScope, scopeId: currentScopeId },
            success: function(response) {
                if (response.success) {
                    var data = response.data;
                    renderDiscountTypeChart(data.breakdown_by_type);
                    renderBranchSpendingChart(data.breakdown_by_branch);
                }
            }
        });
    }

    function loadAlerts() {
        $.ajax({
            url: '<?= admin_url('loyalty/get_alerts') ?>',
            type: 'GET',
            data: { scopeLevel: currentScope, scopeId: currentScopeId },
            success: function(response) {
                if (response.success) {
                    renderAlerts(response.data.alerts);
                }
            }
        });
    }

    // Chart rendering functions
    function renderBudgetGauge(percentage) {
        var chart = echarts.init(document.getElementById('budgetGauge'));
        var option = {
            series: [{
                type: 'gauge',
                startAngle: 180,
                endAngle: 0,
                min: 0,
                max: 100,
                splitNumber: 10,
                axisLine: {
                    lineStyle: {
                        width: 6,
                        color: [
                            [0.5, '#00a65a'],
                            [0.75, '#f0ad4e'],
                            [1, '#dd4b39']
                        ]
                    }
                },
                pointer: {
                    icon: 'path://M12.8,0.7l12,40.1H0.7L12.8,0.7z',
                    length: '12%',
                    width: 20,
                    offsetCenter: [0, '-60%'],
                    itemStyle: {
                        color: 'auto'
                    }
                },
                axisTick: {
                    length: 12,
                    lineStyle: {
                        color: 'auto',
                        width: 2
                    }
                },
                splitLine: {
                    length: 20,
                    lineStyle: {
                        color: 'auto',
                        width: 5
                    }
                },
                axisLabel: {
                    color: '#464646',
                    fontSize: 14,
                    distance: -60,
                    formatter: function (value) {
                        return value + '%';
                    }
                },
                title: {
                    offsetCenter: [0, '20%'],
                    fontSize: 20
                },
                detail: {
                    fontSize: 30,
                    offsetCenter: [0, '0%'],
                    valueAnimation: true,
                    formatter: function (value) {
                        return Math.round(value) + '%';
                    },
                    color: 'auto'
                },
                data: [{
                    value: percentage,
                    name: 'Budget Used'
                }]
            }]
        };
        chart.setOption(option);
        
        window.addEventListener('resize', function() {
            chart.resize();
        });
    }

    function renderBurnRateChart(data) {
        var chart = echarts.init(document.getElementById('burnRateChart'));
        var dates = data.map(item => item.date);
        var rates = data.map(item => item.burn_rate);
        
        var option = {
            tooltip: {
                trigger: 'axis',
                formatter: function(params) {
                    return params[0].name + '<br/>' + 
                           'Burn Rate: ' + formatCurrency(params[0].value);
                }
            },
            xAxis: {
                type: 'category',
                data: dates,
                axisLabel: {
                    rotate: 45
                }
            },
            yAxis: {
                type: 'value',
                axisLabel: {
                    formatter: function(value) {
                        return formatCurrency(value);
                    }
                }
            },
            series: [{
                data: rates,
                type: 'line',
                smooth: true,
                lineStyle: {
                    color: '#dd4b39',
                    width: 3
                },
                areaStyle: {
                    color: new echarts.graphic.LinearGradient(0, 0, 0, 1, [{
                        offset: 0,
                        color: 'rgba(221, 75, 57, 0.3)'
                    }, {
                        offset: 1,
                        color: 'rgba(221, 75, 57, 0.05)'
                    }])
                }
            }]
        };
        chart.setOption(option);
        
        window.addEventListener('resize', function() {
            chart.resize();
        });
    }

    function renderSpendingTrendChart(data) {
        var chart = echarts.init(document.getElementById('spendingTrendChart'));
        var months = data.map(item => item.month);
        var spending = data.map(item => item.spending);
        var budget = data.map(item => item.budget);
        
        var option = {
            tooltip: {
                trigger: 'axis',
                axisPointer: {
                    type: 'cross'
                }
            },
            legend: {
                data: ['Spending', 'Budget']
            },
            xAxis: {
                type: 'category',
                data: months
            },
            yAxis: {
                type: 'value',
                axisLabel: {
                    formatter: function(value) {
                        return (value / 1000) + 'K';
                    }
                }
            },
            series: [
                {
                    name: 'Spending',
                    type: 'bar',
                    data: spending,
                    itemStyle: {
                        color: '#3c8dbc'
                    }
                },
                {
                    name: 'Budget',
                    type: 'line',
                    data: budget,
                    lineStyle: {
                        color: '#00a65a',
                        width: 2,
                        type: 'dashed'
                    },
                    itemStyle: {
                        color: '#00a65a'
                    }
                }
            ]
        };
        chart.setOption(option);
        
        window.addEventListener('resize', function() {
            chart.resize();
        });
    }

    function renderProjectionsChart(data) {
        var chart = echarts.init(document.getElementById('projectionsChart'));
        var months = data.map(item => item.month);
        var projected = data.map(item => item.projected);
        var lowerBound = data.map(item => item.lower_bound);
        var upperBound = data.map(item => item.upper_bound);
        
        var option = {
            tooltip: {
                trigger: 'axis'
            },
            legend: {
                data: ['Projected', 'Lower Bound', 'Upper Bound']
            },
            xAxis: {
                type: 'category',
                data: months
            },
            yAxis: {
                type: 'value',
                axisLabel: {
                    formatter: function(value) {
                        return (value / 1000) + 'K';
                    }
                }
            },
            series: [
                {
                    name: 'Projected',
                    type: 'line',
                    data: projected,
                    smooth: true,
                    lineStyle: {
                        color: '#3c8dbc',
                        width: 3
                    }
                },
                {
                    name: 'Lower Bound',
                    type: 'line',
                    data: lowerBound,
                    smooth: true,
                    lineStyle: {
                        color: '#00a65a',
                        width: 2,
                        type: 'dashed'
                    }
                },
                {
                    name: 'Upper Bound',
                    type: 'line',
                    data: upperBound,
                    smooth: true,
                    lineStyle: {
                        color: '#dd4b39',
                        width: 2,
                        type: 'dashed'
                    }
                }
            ]
        };
        chart.setOption(option);
        
        window.addEventListener('resize', function() {
            chart.resize();
        });
    }

    function renderDiscountTypeChart(data) {
        var chart = echarts.init(document.getElementById('discountTypeChart'));
        var chartData = data.map(item => ({
            name: item.type,
            value: item.spent
        }));
        
        var option = {
            tooltip: {
                trigger: 'item',
                formatter: '{a} <br/>{b}: {c} ({d}%)'
            },
            legend: {
                orient: 'vertical',
                left: 'left'
            },
            series: [{
                name: 'Discount Type',
                type: 'pie',
                radius: ['40%', '70%'],
                avoidLabelOverlap: false,
                itemStyle: {
                    borderRadius: 10,
                    borderColor: '#fff',
                    borderWidth: 2
                },
                label: {
                    show: true,
                    formatter: '{b}: {d}%'
                },
                emphasis: {
                    label: {
                        show: true,
                        fontSize: '16',
                        fontWeight: 'bold'
                    }
                },
                data: chartData
            }]
        };
        chart.setOption(option);
        
        window.addEventListener('resize', function() {
            chart.resize();
        });
    }

    function renderBranchSpendingChart(data) {
        var chart = echarts.init(document.getElementById('branchSpendingChart'));
        var branches = data.map(item => item.branch);
        var spent = data.map(item => item.spent);
        var budgets = data.map(item => item.budget);
        
        var option = {
            tooltip: {
                trigger: 'axis',
                axisPointer: {
                    type: 'shadow'
                }
            },
            legend: {
                data: ['Spent', 'Budget']
            },
            xAxis: {
                type: 'value'
            },
            yAxis: {
                type: 'category',
                data: branches
            },
            series: [
                {
                    name: 'Spent',
                    type: 'bar',
                    data: spent,
                    itemStyle: {
                        color: '#ff851b'
                    }
                },
                {
                    name: 'Budget',
                    type: 'bar',
                    data: budgets,
                    itemStyle: {
                        color: '#3c8dbc'
                    }
                }
            ]
        };
        chart.setOption(option);
        
        window.addEventListener('resize', function() {
            chart.resize();
        });
    }

    function renderAlerts(alerts) {
        var html = '';
        if (alerts.length === 0) {
            html = '<tr><td colspan="5" class="text-center">No alerts at this time</td></tr>';
        } else {
            alerts.forEach(function(alert) {
                var badgeClass = 'alert-' + alert.level;
                var actionBadge = alert.action_required ? 
                    '<span class="label label-danger">Action Required</span>' : 
                    '<span class="label label-default">Info Only</span>';
                
                html += '<tr>' +
                    '<td><span class="alert-badge ' + badgeClass + '">' + alert.level.toUpperCase() + '</span></td>' +
                    '<td>' + alert.title + '</td>' +
                    '<td>' + alert.message + '</td>' +
                    '<td>' + alert.timestamp + '</td>' +
                    '<td>' + actionBadge + '</td>' +
                    '</tr>';
            });
        }
        $('#alertsTableBody').html(html);
    }

    function formatCurrency(amount) {
        return 'SAR ' + amount.toLocaleString('en-US', {
            minimumFractionDigits: 0,
            maximumFractionDigits: 0
        });
    }
});
</script>
