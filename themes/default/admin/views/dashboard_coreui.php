<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= lang('dashboard') ?> - Avenzur</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --primary: #0d6efd;
            --success: #198754;
            --danger: #dc3545;
            --warning: #ffc107;
            --info: #0dcaf0;
            --light: #f8f9fa;
            --dark: #212529;
            --border-color: #dee2e6;
            --shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            --shadow-lg: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: var(--light);
            color: var(--dark);
        }

        .dashboard-container {
            padding: 1.5rem;
            max-width: 1400px;
            margin: 0 auto;
        }

        /* Header Section */
        .dashboard-header {
            margin-bottom: 2rem;
        }

        .dashboard-header h1 {
            font-size: 2rem;
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 0.5rem;
        }

        .dashboard-header p {
            font-size: 1rem;
            color: #6c757d;
        }

        /* Stats Cards Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        /* Card Styles */
        .card {
            background: white;
            border: 1px solid var(--border-color);
            border-radius: 0.5rem;
            box-shadow: var(--shadow);
            transition: var(--transition);
            overflow: hidden;
        }

        .card:hover {
            box-shadow: var(--shadow-lg);
            transform: translateY(-4px);
        }

        .card-header {
            padding: 1.5rem;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .card-header h5 {
            font-size: 1rem;
            font-weight: 600;
            margin: 0;
            color: #6c757d;
        }

        .card-header .badge {
            padding: 0.375rem 0.75rem;
            font-size: 0.75rem;
            font-weight: 500;
        }

        .card-body {
            padding: 1.5rem;
        }

        .card-footer {
            padding: 1rem 1.5rem;
            background: var(--light);
            border-top: 1px solid var(--border-color);
            font-size: 0.875rem;
            color: #6c757d;
        }

        /* Stat Card Specific */
        .stat-card {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1.5rem;
        }

        .stat-content {
            flex: 1;
        }

        .stat-label {
            font-size: 0.875rem;
            color: #6c757d;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 0.5rem;
            font-weight: 600;
        }

        .stat-value {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--dark);
            line-height: 1;
            margin-bottom: 0.5rem;
        }

        .stat-change {
            font-size: 0.875rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .stat-change.positive {
            color: var(--success);
        }

        .stat-change.negative {
            color: var(--danger);
        }

        .stat-icon {
            width: 80px;
            height: 80px;
            border-radius: 0.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            opacity: 0.2;
            margin-left: 1rem;
        }

        .stat-icon.primary {
            background: var(--primary);
            color: var(--primary);
        }

        .stat-icon.success {
            background: var(--success);
            color: var(--success);
        }

        .stat-icon.danger {
            background: var(--danger);
            color: var(--danger);
        }

        .stat-icon.warning {
            background: var(--warning);
            color: var(--warning);
        }

        /* Charts Section */
        .charts-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(450px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        @media (max-width: 1024px) {
            .charts-row {
                grid-template-columns: 1fr;
            }
        }

        /* Table Styles */
        .table-responsive {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        thead {
            background: var(--light);
            border-bottom: 2px solid var(--border-color);
        }

        th {
            padding: 1rem 1.5rem;
            text-align: left;
            font-weight: 600;
            font-size: 0.875rem;
            color: #6c757d;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        td {
            padding: 1rem 1.5rem;
            border-bottom: 1px solid var(--border-color);
        }

        tbody tr:hover {
            background: var(--light);
        }

        tbody tr:last-child td {
            border-bottom: none;
        }

        /* Badges */
        .badge {
            display: inline-block;
            padding: 0.5rem 0.75rem;
            font-size: 0.75rem;
            font-weight: 600;
            border-radius: 0.25rem;
        }

        .badge-primary {
            background: #cfe2ff;
            color: #084298;
        }

        .badge-success {
            background: #d1e7dd;
            color: #0a3622;
        }

        .badge-danger {
            background: #f8d7da;
            color: #842029;
        }

        .badge-warning {
            background: #fff3cd;
            color: #664d03;
        }

        .badge-info {
            background: #cff4fc;
            color: #055160;
        }

        .badge-secondary {
            background: #e2e3e5;
            color: #41464b;
        }

        /* Progress Bars */
        .progress {
            height: 0.5rem;
            background: var(--light);
            border-radius: 0.25rem;
            overflow: hidden;
            margin-top: 0.5rem;
        }

        .progress-bar {
            height: 100%;
            background: var(--primary);
            transition: width 0.3s ease;
        }

        .progress-bar.success {
            background: var(--success);
        }

        .progress-bar.danger {
            background: var(--danger);
        }

        /* Activity List */
        .activity-list {
            list-style: none;
        }

        .activity-item {
            padding: 1rem 0;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            gap: 1rem;
        }

        .activity-item:last-child {
            border-bottom: none;
        }

        .activity-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--light);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            font-weight: 600;
            font-size: 0.875rem;
            color: var(--primary);
        }

        .activity-content h6 {
            font-size: 0.875rem;
            font-weight: 600;
            margin: 0 0 0.25rem 0;
        }

        .activity-content p {
            font-size: 0.75rem;
            color: #6c757d;
            margin: 0;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .dashboard-container {
                padding: 1rem;
            }

            .stats-grid {
                grid-template-columns: 1fr;
                gap: 1rem;
            }

            .stat-value {
                font-size: 2rem;
            }

            .stat-icon {
                width: 60px;
                height: 60px;
                font-size: 2rem;
                margin-left: 0.5rem;
            }

            .dashboard-header h1 {
                font-size: 1.5rem;
            }

            th, td {
                padding: 0.75rem;
                font-size: 0.75rem;
            }
        }

        /* Utility Classes */
        .text-muted {
            color: #6c757d;
        }

        .text-primary {
            color: var(--primary);
        }

        .text-success {
            color: var(--success);
        }

        .text-danger {
            color: var(--danger);
        }

        .text-warning {
            color: #856404;
        }

        .text-center {
            text-align: center;
        }

        .flex {
            display: flex;
        }

        .flex-between {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .mt-3 { margin-top: 1rem; }
        .mb-3 { margin-bottom: 1rem; }
        .mb-2 { margin-bottom: 0.5rem; }

        /* Arrow Icons */
        .arrow-icon {
            display: inline-block;
            font-size: 0.75rem;
            margin-right: 0.25rem;
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <!-- Header -->
        <div class="dashboard-header">
            <h1><?= lang('dashboard') ?></h1>
            <p>Welcome back, <?= $this->session->userdata('username'); ?>!</p>
        </div>

        <!-- Stats Grid -->
        <div class="stats-grid">
            <!-- Users Card -->
            <div class="card">
                <div class="stat-card">
                    <div class="stat-content">
                        <div class="stat-label">Total Users</div>
                        <div class="stat-value"><?= isset($total_users) ? number_format($total_users) : '0'; ?></div>
                        <div class="stat-change positive">
                            <span class="arrow-icon">↑</span>
                            <span>12.5% from last month</span>
                        </div>
                    </div>
                    <div class="stat-icon primary">
                        <i class="fa fa-users"></i>
                    </div>
                </div>
            </div>

            <!-- Revenue Card -->
            <div class="card">
                <div class="stat-card">
                    <div class="stat-content">
                        <div class="stat-label">Total Revenue</div>
                        <div class="stat-value"><?= isset($total_sales) ? number_format($total_sales, 2) : '0'; ?></div>
                        <div class="stat-change positive">
                            <span class="arrow-icon">↑</span>
                            <span>8.2% from last month</span>
                        </div>
                    </div>
                    <div class="stat-icon success">
                        <i class="fa fa-dollar"></i>
                    </div>
                </div>
            </div>

            <!-- Orders Card -->
            <div class="card">
                <div class="stat-card">
                    <div class="stat-content">
                        <div class="stat-label">Total Orders</div>
                        <div class="stat-value"><?= isset($total_orders) ? number_format($total_orders) : '0'; ?></div>
                        <div class="stat-change negative">
                            <span class="arrow-icon">↓</span>
                            <span>3.1% from last month</span>
                        </div>
                    </div>
                    <div class="stat-icon warning">
                        <i class="fa fa-shopping-cart"></i>
                    </div>
                </div>
            </div>

            <!-- Conversion Rate Card -->
            <div class="card">
                <div class="stat-card">
                    <div class="stat-content">
                        <div class="stat-label">Conversion Rate</div>
                        <div class="stat-value">2.49%</div>
                        <div class="stat-change positive">
                            <span class="arrow-icon">↑</span>
                            <span>84.7% from last month</span>
                        </div>
                    </div>
                    <div class="stat-icon info">
                        <i class="fa fa-percent"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts Section -->
        <div class="charts-row">
            <!-- Sales Chart -->
            <div class="card">
                <div class="card-header">
                    <h5>Sales Overview</h5>
                    <span class="badge badge-primary">This Month</span>
                </div>
                <div class="card-body">
                    <div id="sales-chart" style="height: 300px; display: flex; align-items: center; justify-content: center; background: var(--light);">
                        <p class="text-muted">Chart placeholder - integrate Chart.js or Recharts</p>
                    </div>
                </div>
                <div class="card-footer">
                    Lorem ipsum dolor sit amet, consectetur adipiscing elit.
                </div>
            </div>

            <!-- Traffic Chart -->
            <div class="card">
                <div class="card-header">
                    <h5>Traffic Sources</h5>
                    <span class="badge badge-success">Active</span>
                </div>
                <div class="card-body">
                    <div id="traffic-chart" style="height: 300px; display: flex; align-items: center; justify-content: center; background: var(--light);">
                        <p class="text-muted">Chart placeholder - integrate Chart.js or Recharts</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tables Section -->
        <div class="charts-row">
            <!-- Recent Orders -->
            <div class="card">
                <div class="card-header">
                    <h5>Recent Orders</h5>
                    <span class="badge badge-secondary">Latest</span>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table>
                            <thead>
                                <tr>
                                    <th>Order ID</th>
                                    <th>Customer</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>#ORD001</td>
                                    <td>Ahmed Hassan</td>
                                    <td>$2,500</td>
                                    <td><span class="badge badge-success">Completed</span></td>
                                </tr>
                                <tr>
                                    <td>#ORD002</td>
                                    <td>Fatima Khan</td>
                                    <td>$1,850</td>
                                    <td><span class="badge badge-warning">Processing</span></td>
                                </tr>
                                <tr>
                                    <td>#ORD003</td>
                                    <td>Mohammed Ali</td>
                                    <td>$3,200</td>
                                    <td><span class="badge badge-info">Pending</span></td>
                                </tr>
                                <tr>
                                    <td>#ORD004</td>
                                    <td>Layla Ahmed</td>
                                    <td>$1,650</td>
                                    <td><span class="badge badge-success">Completed</span></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Activity Feed -->
            <div class="card">
                <div class="card-header">
                    <h5>Recent Activity</h5>
                    <span class="badge badge-secondary">Today</span>
                </div>
                <div class="card-body">
                    <ul class="activity-list">
                        <li class="activity-item">
                            <div class="activity-avatar" style="background: #cfe2ff; color: #084298;">AH</div>
                            <div class="activity-content">
                                <h6>Ahmed Hassan</h6>
                                <p>Placed new order #ORD001</p>
                                <p class="text-muted">5 minutes ago</p>
                            </div>
                        </li>
                        <li class="activity-item">
                            <div class="activity-avatar" style="background: #d1e7dd; color: #0a3622;">FK</div>
                            <div class="activity-content">
                                <h6>Fatima Khan</h6>
                                <p>Updated profile information</p>
                                <p class="text-muted">1 hour ago</p>
                            </div>
                        </li>
                        <li class="activity-item">
                            <div class="activity-avatar" style="background: #fff3cd; color: #664d03;">MA</div>
                            <div class="activity-content">
                                <h6>Mohammed Ali</h6>
                                <p>Made payment for invoice #INV123</p>
                                <p class="text-muted">3 hours ago</p>
                            </div>
                        </li>
                        <li class="activity-item">
                            <div class="activity-avatar" style="background: #cff4fc; color: #055160;">LA</div>
                            <div class="activity-content">
                                <h6>Layla Ahmed</h6>
                                <p>Submitted support ticket</p>
                                <p class="text-muted">5 hours ago</p>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Performance Metrics -->
        <div class="card" style="margin-bottom: 2rem;">
            <div class="card-header">
                <h5>Performance Metrics</h5>
            </div>
            <div class="card-body">
                <div style="margin-bottom: 1.5rem;">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                        <span style="font-weight: 500;">Product A Sales</span>
                        <span style="font-weight: 600; color: var(--primary);">75%</span>
                    </div>
                    <div class="progress">
                        <div class="progress-bar success" style="width: 75%;"></div>
                    </div>
                </div>

                <div style="margin-bottom: 1.5rem;">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                        <span style="font-weight: 500;">Product B Sales</span>
                        <span style="font-weight: 600; color: var(--primary);">50%</span>
                    </div>
                    <div class="progress">
                        <div class="progress-bar" style="width: 50%;"></div>
                    </div>
                </div>

                <div style="margin-bottom: 1.5rem;">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                        <span style="font-weight: 500;">Product C Sales</span>
                        <span style="font-weight: 600; color: var(--primary);">90%</span>
                    </div>
                    <div class="progress">
                        <div class="progress-bar danger" style="width: 90%;"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Placeholder for chart initialization
        // Integrate with Chart.js or Recharts here
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Dashboard loaded');
            // TODO: Initialize charts
        });
    </script>
</body>
</html>
