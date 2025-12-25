<?php
require_once __DIR__ . '/../../autoload.php';

use App\Services\OrderEntityExtractor;
use App\Services\DatabaseService;

$result = null;
$error = null;
$dbResult = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $payload = [
            "id" => rand(1000000, 9999999),
            "order_number" => rand(1000, 9999),
            "name" => "#DBTEST-" . rand(1000, 9999),
            "email" => "dbtest" . rand(100, 999) . "@example.com",
            "phone" => "+1-555-" . rand(100, 999) . "-" . rand(1000, 9999),
            "total_price" => "459.97",
            "subtotal_price" => "449.97",
            "total_tax" => "10.00",
            "currency" => "USD",
            "financial_status" => "paid",
            "created_at" => date('Y-m-d H:i:s'),
            "customer" => [
                "id" => rand(1000000, 9999999),
                "first_name" => "Database",
                "last_name" => "Test",
                "email" => "dbtest" . rand(100, 999) . "@example.com",
                "phone" => "+1-555-111-2222",
                "verified_email" => true
            ],
            "billing_address" => [
                "first_name" => "Database",
                "last_name" => "Test",
                "address1" => "789 Test St",
                "city" => "Test City",
                "province" => "Test State",
                "country" => "United States",
                "zip" => "99999"
            ],
            "shipping_address" => [
                "first_name" => "Database",
                "last_name" => "Test",
                "address1" => "789 Test St",
                "city" => "Test City",
                "province" => "Test State",
                "country" => "United States",
                "zip" => "99999"
            ],
            "line_items" => [
                [
                    "id" => rand(1000000, 9999999),
                    "title" => "Test Product A",
                    "sku" => "TEST-A-" . rand(1000, 9999),
                    "price" => "229.99",
                    "quantity" => 1,
                    "requires_shipping" => true
                ],
                [
                    "id" => rand(1000000, 9999999),
                    "title" => "Test Product B",
                    "sku" => "TEST-B-" . rand(1000, 9999),
                    "price" => "219.98",
                    "quantity" => 2,
                    "requires_shipping" => true
                ]
            ],
            "shipping_lines" => [
                [
                    "id" => rand(1000000, 9999999),
                    "title" => "Express Shipping",
                    "price" => "15.00"
                ]
            ]
        ];

        $extractor = new OrderEntityExtractor();
        $entities = $extractor->extractAll($payload);

        $db = new DatabaseService();
        $dbResult = $db->saveCompleteOrder($entities);
        
        $result = true;
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Database Save Test</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 20px;
            min-height: 100vh;
        }
        .container {
            max-width: 1000px;
            margin: 0 auto;
            background: white;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            overflow: hidden;
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .header h1 { font-size: 28px; }
        .back-button {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            padding: 10px 20px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 600;
        }
        .content { padding: 30px; }
        .btn {
            padding: 14px 32px;
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
        }
        .success-box {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            padding: 30px;
            border-radius: 12px;
            color: white;
            margin: 20px 0;
        }
        .error-box {
            background: linear-gradient(135deg, #eb3349 0%, #f45c43 100%);
            padding: 30px;
            border-radius: 12px;
            color: white;
            margin: 20px 0;
        }
        .db-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
            margin: 20px 0;
        }
        .db-item {
            background: rgba(255, 255, 255, 0.2);
            padding: 15px;
            border-radius: 8px;
        }
        .db-item label {
            font-size: 11px;
            opacity: 0.9;
            text-transform: uppercase;
        }
        .db-item value {
            display: block;
            font-size: 24px;
            font-weight: 700;
            margin-top: 5px;
        }
        .link-button {
            display: inline-block;
            background: rgba(255, 255, 255, 0.2);
            padding: 10px 20px;
            border-radius: 6px;
            text-decoration: none;
            color: white;
            font-weight: 600;
            margin: 10px 10px 0 0;
        }
        .info-box {
            background: #e6f7ff;
            border-left: 4px solid #1890ff;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
        }
        .info-box ul {
            margin-left: 20px;
            color: #0050b3;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>💾 Database Save Test</h1>
            <a href="../index.php" class="back-button">← Back to Dashboard</a>
        </div>

        <div class="content">
            <?php if (!$result): ?>
                <div class="info-box">
                    <h3 style="color: #0050b3; margin-bottom: 10px;">📋 What This Test Does</h3>
                    <ul>
                        <li>Generates random test order data</li>
                        <li>Extracts entities using OrderEntityExtractor</li>
                        <li>Saves customer to sma_users table</li>
                        <li>Saves addresses to sma_addresses table</li>
                        <li>Saves order to sma_sales table</li>
                        <li>Saves line items to sma_sale_items table</li>
                    </ul>
                </div>

                <form method="POST">
                    <button type="submit" class="btn">💾 Save Test Order to Database</button>
                </form>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="error-box">
                    <h2>❌ Error Occurred</h2>
                    <p><?= htmlspecialchars($error) ?></p>
                </div>
                <form method="POST">
                    <button type="submit" class="btn">🔄 Try Again</button>
                </form>
            <?php endif; ?>

            <?php if ($result && $dbResult): ?>
                <div class="success-box">
                    <h2>✅ Order Saved Successfully!</h2>
                    <p>All data has been saved to the database using modular repositories</p>

                    <div class="db-grid">
                        <div class="db-item">
                            <label>Sale ID</label>
                            <value><?= $dbResult['sale_id'] ?></value>
                        </div>
                        <div class="db-item">
                            <label>Customer ID</label>
                            <value><?= $dbResult['customer_id'] ?></value>
                        </div>
                        <div class="db-item">
                            <label>Billing Address ID</label>
                            <value><?= $dbResult['billing_address_id'] ?? 'N/A' ?></value>
                        </div>
                        <div class="db-item">
                            <label>Shipping Address ID</label>
                            <value><?= $dbResult['shipping_address_id'] ?? 'N/A' ?></value>
                        </div>
                    </div>

                    <div style="margin-top: 20px;">
                        <strong>Repositories Used:</strong><br>
                        <span style="font-size: 13px; opacity: 0.9;">
                            CustomerRepository → AddressRepository → SaleRepository → SaleItemRepository
                        </span>
                    </div>

                    <a href="http://localhost/phpmyadmin/index.php?route=/sql&db=avnzor&table=sma_sales" 
                       class="link-button" target="_blank">
                        📊 View in phpMyAdmin
                    </a>

                    <a href="../query-customer.php?id=<?= $dbResult['customer_id'] ?>" 
                       class="link-button">
                        👤 View Customer Details
                    </a>
                </div>

                <form method="POST" style="text-align: center; margin-top: 20px;">
                    <button type="submit" class="btn">💾 Save Another Order</button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>