<?php
require_once __DIR__ . '/../../vendor/autoload.php';

$result = null;
$error = null;

// Test scenarios
$scenarios = [
    'complete' => [
        'name' => 'Complete Order',
        'payload' => [
            "id" => 820982911946154508,
            "name" => "#9999",
            "email" => "jon@example.com",
            "order_number" => 1234,
            "created_at" => date('Y-m-d H:i:s'),
            "updated_at" => date('Y-m-d H:i:s'),
            "currency" => "USD",
            "total_price" => "388.00",
            "subtotal_price" => "388.00",
            "total_tax" => "0.00",
            "total_discounts" => "20.00",
            "financial_status" => "paid",
            "fulfillment_status" => "fulfilled",
            "tags" => "integration-test",
            "test" => true,
            "customer" => [
                "id" => 115310627314723954,
                "email" => "john@example.com",
                "first_name" => "John",
                "last_name" => "Smith",
                "verified_email" => true,
                "tax_exempt" => false,
                "phone" => "555-123-4567"
            ],
            "billing_address" => [
                "first_name" => "Steve",
                "last_name" => "Shipper",
                "company" => "Shipping Company",
                "address1" => "123 Shipping Street",
                "address2" => null,
                "city" => "Shippington",
                "province" => "Kentucky",
                "province_code" => "KY",
                "country" => "United States",
                "country_code" => "US",
                "zip" => "40003",
                "phone" => "555-555-SHIP"
            ],
            "shipping_address" => [
                "first_name" => "Steve",
                "last_name" => "Shipper",
                "company" => "Shipping Company",
                "address1" => "123 Shipping Street",
                "address2" => null,
                "city" => "Shippington",
                "province" => "Kentucky",
                "province_code" => "KY",
                "country" => "United States",
                "country_code" => "US",
                "zip" => "40003",
                "phone" => "555-555-SHIP"
            ],
            "line_items" => [
                [
                    "id" => 487817672276298554,
                    "product_id" => 788032119674292922,
                    "variant_id" => 457924702898157455,
                    "name" => "Aviator sunglasses",
                    "title" => "Aviator sunglasses",
                    "sku" => "SKU2006-001",
                    "price" => "89.99",
                    "quantity" => 1,
                    "grams" => 100,
                    "total_discount" => "0.00",
                    "requires_shipping" => true,
                    "taxable" => true
                ],
                [
                    "id" => 976318377106520349,
                    "product_id" => 788032119674292922,
                    "variant_id" => 457924702898157455,
                    "name" => "Mid-century lounger",
                    "title" => "Mid-century lounger",
                    "sku" => "SKU2006-020",
                    "price" => "159.99",
                    "quantity" => 1,
                    "grams" => 1000,
                    "total_discount" => "0.00",
                    "requires_shipping" => true,
                    "taxable" => true
                ],
                [
                    "id" => 315789986012684393,
                    "product_id" => 788032119674292922,
                    "variant_id" => 457924702898157455,
                    "name" => "Coffee table",
                    "title" => "Coffee table",
                    "sku" => "SKU2006-035",
                    "price" => "119.99",
                    "quantity" => 1,
                    "grams" => 500,
                    "total_discount" => "0.00",
                    "requires_shipping" => true,
                    "taxable" => true
                ]
            ],
            "shipping_lines" => [
                [
                    "id" => 271878346596884015,
                    "title" => "Generic Shipping",
                    "price" => "10.00",
                    "code" => "STANDARD",
                    "source" => "shopify"
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
        // Prepare the payload
        $payloadJson = json_encode($currentScenario['payload']);
        
        // Determine the webhook URL
        $webhookUrl = 'http://' . $_SERVER['HTTP_HOST'] . '/ShopifyIntegration/run-integration.php';
        
        // Send POST request to runShopifyIntegration.php
        $ch = curl_init($webhookUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payloadJson);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Content-Type: application/json"
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);
        
        if ($curlError) {
            throw new Exception("cURL Error: $curlError");
        }
        
        $result = [
            'http_code' => $httpCode,
            'response' => json_decode($response, true),
            'raw_response' => $response,
            'payload' => $currentScenario['payload']
        ];
        
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Run Integration Test</title>
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
            padding: 14px 32px;
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
        .info-box {
            background: #e6f7ff;
            border-left: 4px solid #1890ff;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
        }
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin: 20px 0;
        }
        .info-card {
            background: rgba(255, 255, 255, 0.2);
            padding: 15px;
            border-radius: 8px;
        }
        .info-card label {
            font-size: 11px;
            opacity: 0.9;
            text-transform: uppercase;
        }
        .info-card value {
            display: block;
            font-size: 20px;
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
        details {
            margin: 20px 0;
            background: #f7fafc;
            border-radius: 8px;
            padding: 10px;
        }
        summary {
            cursor: pointer;
            font-weight: 600;
            padding: 10px;
            color: #2d3748;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🚀 Run Integration Test</h1>
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
                <div class="info-box">
                    <h3 style="color: #0050b3; margin-bottom: 10px;">📋 What This Test Does</h3>
                    <ul style="margin-left: 20px; color: #0050b3;">
                        <li>Sends JSON POST to runShopifyIntegration.php</li>
                        <li>Tests complete end-to-end integration</li>
                        <li>Verifies entity extraction</li>
                        <li>Confirms database save</li>
                        <li>Returns detailed results</li>
                    </ul>
                </div>

                <form method="POST">
                    <button type="submit" class="btn">🚀 Run Integration Test</button>
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

            <?php if ($result): ?>
                <?php if ($result['http_code'] === 200 && $result['response']['success']): ?>
                    <div class="success-box">
                        <h2>✅ Integration Test Passed!</h2>
                        <p>Order processed successfully through runShopifyIntegration.php</p>

                        <div class="info-grid">
                            <div class="info-card">
                                <label>HTTP Code</label>
                                <value><?= $result['http_code'] ?></value>
                            </div>
                            <div class="info-card">
                                <label>Sale ID</label>
                                <value><?= $result['response']['result']['sale_id'] ?? 'N/A' ?></value>
                            </div>
                            <div class="info-card">
                                <label>Customer ID</label>
                                <value><?= $result['response']['result']['customer_id'] ?? 'N/A' ?></value>
                            </div>
                            <div class="info-card">
                                <label>Order Name</label>
                                <value><?= htmlspecialchars($result['response']['order_name'] ?? 'N/A') ?></value>
                            </div>
                        </div>

                        <div style="margin-top: 20px;">
                            <strong>Message:</strong> <?= htmlspecialchars($result['response']['message']) ?>
                        </div>

                        <?php if (isset($result['response']['result']['customer_id'])): ?>
                        <a href="../query-customer.php?id=<?= $result['response']['result']['customer_id'] ?>" 
                           class="link-button">
                            👤 View Customer Details
                        </a>
                        <?php endif; ?>

                        <a href="http://localhost/phpmyadmin/index.php?route=/sql&db=avnzor&table=sma_sales" 
                           class="link-button" target="_blank">
                            📊 View in phpMyAdmin
                        </a>
                    </div>
                <?php else: ?>
                    <div class="error-box">
                        <h2>❌ Integration Test Failed</h2>
                        <div class="info-grid">
                            <div class="info-card">
                                <label>HTTP Code</label>
                                <value><?= $result['http_code'] ?></value>
                            </div>
                        </div>
                        <div style="margin-top: 15px;">
                            <strong>Error:</strong> <?= htmlspecialchars($result['response']['error'] ?? 'Unknown error') ?>
                        </div>
                    </div>
                <?php endif; ?>

                <details>
                    <summary>📄 View Full Response</summary>
                    <pre><?= htmlspecialchars(json_encode($result['response'], JSON_PRETTY_PRINT)) ?></pre>
                </details>

                <details>
                    <summary>📤 View Sent Payload</summary>
                    <pre><?= htmlspecialchars(json_encode($result['payload'], JSON_PRETTY_PRINT)) ?></pre>
                </details>

                <form method="POST" style="text-align: center; margin-top: 20px;">
                    <button type="submit" class="btn">🔄 Test Again</button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>