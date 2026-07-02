<?php
require_once __DIR__ . '/../vendor/autoload.php';
?>
<!DOCTYPE html>
<html>
<head>
    <title>Shopify Integration - Test Dashboard</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 20px;
            min-height: 100vh;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        .header {
            background: white;
            padding: 40px;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            margin-bottom: 30px;
            text-align: center;
        }
        .header h1 {
            font-size: 36px;
            color: #2d3748;
            margin-bottom: 10px;
        }
        .header p {
            color: #718096;
            font-size: 18px;
        }
        .test-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 25px;
            margin-bottom: 30px;
        }
        .test-card {
            background: white;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            transition: transform 0.2s, box-shadow 0.2s;
            cursor: pointer;
        }
        .test-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }
        .test-card.featured {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        .test-card.featured h2,
        .test-card.featured p {
            color: white;
        }
        .test-card.featured .test-button {
            background: white;
            color: #667eea;
        }
        .test-icon {
            font-size: 48px;
            margin-bottom: 20px;
        }
        .test-card h2 {
            color: #2d3748;
            font-size: 22px;
            margin-bottom: 10px;
        }
        .test-card p {
            color: #718096;
            line-height: 1.6;
            margin-bottom: 20px;
        }
        .test-button {
            display: inline-block;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 12px 24px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            transition: opacity 0.2s;
        }
        .test-button:hover {
            opacity: 0.9;
        }
        .info-section {
            background: white;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        .info-section h2 {
            color: #2d3748;
            margin-bottom: 15px;
        }
        .info-section ul {
            list-style: none;
        }
        .info-section li {
            padding: 10px 0;
            border-bottom: 1px solid #e2e8f0;
            color: #4a5568;
        }
        .info-section li:last-child {
            border-bottom: none;
        }
        .badge {
            display: inline-block;
            background: #e6f7ff;
            color: #0050b3;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
            margin-left: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🧪 Shopify Integration Test Dashboard</h1>
            <p>Modular testing suite for order processing</p>
        </div>

        <div class="test-grid">
            <!-- Run Integration Test (Featured) -->
            <div class="test-card featured" onclick="window.location.href='tests/test-run-integration.php'">
                <div class="test-icon">🚀</div>
                <h2>Run Integration</h2>
                <p>Test the complete end-to-end integration: send JSON to runShopifyIntegration.php and verify database save.</p>
                <a href="tests/test-run-integration.php" class="test-button">Run Test</a>
            </div>

            <!-- Entity Extraction Test -->
            <div class="test-card" onclick="window.location.href='tests/test-entity-extraction.php'">
                <div class="test-icon">🔍</div>
                <h2>Entity Extraction</h2>
                <p>Test the extraction of entities from order payloads without database interaction.</p>
                <a href="tests/test-entity-extraction.php" class="test-button">Run Test</a>
            </div>

            <!-- Database Save Test -->
            <div class="test-card" onclick="window.location.href='tests/test-database-save.php'">
                <div class="test-icon">💾</div>
                <h2>Database Save</h2>
                <p>Test complete order save flow including customer, addresses, and line items.</p>
                <a href="tests/test-database-save.php" class="test-button">Run Test</a>
            </div>

            <!-- Customer Query -->
            <div class="test-card" onclick="window.location.href='query-customer.php'">
                <div class="test-icon">👤</div>
                <h2>Customer Query</h2>
                <p>View customer details, orders, and addresses from the database.</p>
                <a href="query-customer.php" class="test-button">Query</a>
            </div>

            <!-- phpMyAdmin -->
            <div class="test-card" onclick="window.open('http://localhost/phpmyadmin', '_blank')">
                <div class="test-icon">📊</div>
                <h2>Database Admin</h2>
                <p>Access phpMyAdmin to view and manage database records directly.</p>
                <a href="http://localhost/phpmyadmin" target="_blank" class="test-button">Open</a>
            </div>
        </div>

        <div class="info-section">
            <h2>📋 System Information</h2>
            <ul>
                <li>
                    <strong>Database:</strong> avnzor
                    <span class="badge">MySQL</span>
                </li>
                <li>
                    <strong>Architecture:</strong> Modular Repository Pattern
                    <span class="badge">PSR-4</span>
                </li>
                <li>
                    <strong>Endpoint:</strong> /runShopifyIntegration.php
                    <span class="badge">JSON POST</span>
                </li>
                <li>
                    <strong>Repositories:</strong> Customer, Address, Sale, SaleItem, Product
                    <span class="badge">5 Active</span>
                </li>
            </ul>
        </div>
    </div>
</body>
</html>