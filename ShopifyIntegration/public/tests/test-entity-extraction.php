<?php
require_once __DIR__ . '/../../autoload.php';

use App\Services\OrderEntityExtractor;

$result = null;
$error = null;
$entities = null;

// Test scenarios
$scenarios = [
    'complete' => [
        'name' => 'Complete Order',
        'payload' => [
            "id" => rand(1000000, 9999999),
            "order_number" => rand(1000, 9999),
            "name" => "#TEST-" . rand(1000, 9999),
            "email" => "test@example.com",
            "phone" => "+1-555-123-4567",
            "total_price" => "359.97",
            "subtotal_price" => "369.97",
            "total_tax" => "0.00",
            "total_discounts" => "20.00",
            "currency" => "USD",
            "financial_status" => "paid",
            "fulfillment_status" => "fulfilled",
            "created_at" => date('Y-m-d H:i:s'),
            "customer" => [
                "id" => rand(1000000, 9999999),
                "first_name" => "John",
                "last_name" => "Doe",
                "email" => "john@example.com",
                "phone" => "+1-555-999-8888",
                "verified_email" => true
            ],
            "billing_address" => [
                "first_name" => "John",
                "last_name" => "Doe",
                "address1" => "123 Main St",
                "city" => "New York",
                "province" => "NY",
                "country" => "United States",
                "zip" => "10001"
            ],
            "shipping_address" => [
                "first_name" => "John",
                "last_name" => "Doe",
                "address1" => "456 Oak Ave",
                "city" => "Brooklyn",
                "province" => "NY",
                "country" => "United States",
                "zip" => "11201"
            ],
            "line_items" => [
                [
                    "id" => rand(1000000, 9999999),
                    "title" => "Test Product",
                    "sku" => "SKU-" . rand(1000, 9999),
                    "price" => "359.97",
                    "quantity" => 1,
                    "requires_shipping" => true
                ]
            ],
            "shipping_lines" => [
                [
                    "id" => rand(1000000, 9999999),
                    "title" => "Standard",
                    "price" => "10.00"
                ]
            ]
        ]
    ],
    'minimal' => [
        'name' => 'Minimal Order',
        'payload' => [
            "id" => rand(1000000, 9999999),
            "name" => "#MIN-" . rand(1000, 9999),
            "email" => "minimal@example.com",
            "total_price" => "99.99",
            "currency" => "USD",
            "financial_status" => "paid",
            "line_items" => [
                [
                    "id" => rand(1000000, 9999999),
                    "title" => "Digital Product",
                    "price" => "99.99",
                    "quantity" => 1
                ]
            ]
        ]
    ]
];

$selectedScenario = $_GET['scenario'] ?? 'complete';
$currentScenario = $scenarios[$selectedScenario];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $extractor = new OrderEntityExtractor();
        $entities = $extractor->extractAll($currentScenario['payload']);
        $result = true;
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Entity Extraction Test</title>
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
        .scenario-tabs {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            border-bottom: 2px solid #e2e8f0;
        }
        .scenario-tab {
            padding: 10px 20px;
            background: none;
            border: none;
            border-bottom: 3px solid transparent;
            cursor: pointer;
            font-weight: 600;
            color: #718096;
        }
        .scenario-tab.active {
            color: #667eea;
            border-bottom-color: #667eea;
        }
        .btn {
            padding: 12px 32px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
        }
        .success-box {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            padding: 25px;
            border-radius: 12px;
            color: white;
            margin: 20px 0;
        }
        .error-box {
            background: linear-gradient(135deg, #eb3349 0%, #f45c43 100%);
            padding: 25px;
            border-radius: 12px;
            color: white;
            margin: 20px 0;
        }
        .entity-card {
            background: #f7fafc;
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            padding: 20px;
            margin: 15px 0;
        }
        .entity-card h3 {
            color: #2d3748;
            margin-bottom: 15px;
            border-bottom: 2px solid #cbd5e0;
            padding-bottom: 10px;
        }
        pre {
            background: #1a202c;
            color: #e2e8f0;
            padding: 20px;
            border-radius: 8px;
            overflow-x: auto;
            font-size: 13px;
            max-height: 400px;
            overflow-y: auto;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔍 Entity Extraction Test</h1>
            <a href="../index.php" class="back-button">← Back to Dashboard</a>
        </div>

        <div class="content">
            <div class="scenario-tabs">
                <?php foreach ($scenarios as $key => $scenario): ?>
                    <button class="scenario-tab <?= $selectedScenario === $key ? 'active' : '' ?>"
                            onclick="window.location.href='?scenario=<?= $key ?>'">
                        <?= $scenario['name'] ?>
                    </button>
                <?php endforeach; ?>
            </div>

            <?php if (!$result): ?>
                <form method="POST">
                    <button type="submit" class="btn">🔍 Extract Entities</button>
                </form>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="error-box">
                    <h2>❌ Error</h2>
                    <p><?= htmlspecialchars($error) ?></p>
                </div>
            <?php endif; ?>

            <?php if ($result && $entities): ?>
                <div class="success-box">
                    <h2>✅ Entities Extracted Successfully!</h2>
                    <p>All data has been parsed from the payload</p>
                </div>

                <div class="entity-card">
                    <h3>📦 Order Entity</h3>
                    <pre><?= htmlspecialchars(json_encode($entities['order'], JSON_PRETTY_PRINT)) ?></pre>
                </div>

                <div class="entity-card">
                    <h3>👤 Customer Entity</h3>
                    <pre><?= htmlspecialchars(json_encode($entities['customer'], JSON_PRETTY_PRINT)) ?></pre>
                </div>

                <div class="entity-card">
                    <h3>📍 Addresses Entity</h3>
                    <pre><?= htmlspecialchars(json_encode($entities['addresses'], JSON_PRETTY_PRINT)) ?></pre>
                </div>

                <div class="entity-card">
                    <h3>🛒 Items Entity</h3>
                    <pre><?= htmlspecialchars(json_encode($entities['items'], JSON_PRETTY_PRINT)) ?></pre>
                </div>

                <form method="POST" style="text-align: center; margin-top: 20px;">
                    <button type="submit" class="btn">🔄 Test Again</button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>