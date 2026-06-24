<?php
/**
 * Simple Shopify Order Sync Test
 * Tests if Shopify orders are being saved to Avenzur database
 *
 * Usage: http://localhost/avenzur/ShopifyIntegration/test_sync_simple.php
 */

// Database configuration (from .env)
$db_config = [
    'host' => 'localhost',
    'database' => 'avnzor',
    'username' => 'root',
    'password' => ''
];

// Sample Shopify order data
$shopify_order = [
    "id" => rand(820982911946154508, 999999999999999999),
    "name" => "#TEST-" . rand(1000, 9999),
    "email" => "test" . rand(100, 999) . "@example.com",
    "order_number" => rand(10000, 99999),
    "created_at" => date('Y-m-d H:i:s'),
    "updated_at" => date('Y-m-d H:i:s'),
    "currency" => "USD",
    "total_price" => "359.97",
    "subtotal_price" => "369.97",
    "total_tax" => "54.00",
    "total_discounts" => "20.00",
    "financial_status" => "paid",
    "fulfillment_status" => "fulfilled",
    "tags" => "test-sync",
    "customer" => [
        "id" => rand(115310627314723954, 999999999999999999),
        "email" => "customer" . rand(100, 999) . "@example.com",
        "first_name" => "Test",
        "last_name" => "Customer",
        "verified_email" => true,
        "tax_exempt" => false,
        "phone" => "+1-555-" . rand(100, 999) . "-" . rand(1000, 9999)
    ],
    "billing_address" => [
        "first_name" => "Test",
        "last_name" => "Customer",
        "company" => "Test Company",
        "address1" => "123 Test Street",
        "address2" => "Suite 100",
        "city" => "Test City",
        "province" => "California",
        "province_code" => "CA",
        "country" => "United States",
        "country_code" => "US",
        "zip" => "90210",
        "phone" => "+1-555-TEST"
    ],
    "shipping_address" => [
        "first_name" => "Test",
        "last_name" => "Customer",
        "company" => "Test Company",
        "address1" => "123 Test Street",
        "address2" => "Suite 100",
        "city" => "Test City",
        "province" => "California",
        "province_code" => "CA",
        "country" => "United States",
        "country_code" => "US",
        "zip" => "90210",
        "phone" => "+1-555-TEST"
    ],
    "line_items" => [
        [
            "id" => rand(487817672276298554, 999999999999999999),
            "product_id" => rand(788032119674292922, 999999999999999999),
            "variant_id" => rand(457924702898157455, 999999999999999999),
            "name" => "Test Product 1",
            "title" => "Test Product 1",
            "sku" => "TEST-SKU-" . rand(1000, 9999),
            "price" => "129.99",
            "quantity" => 2,
            "grams" => 500,
            "total_discount" => "10.00",
            "requires_shipping" => true,
            "taxable" => true
        ],
        [
            "id" => rand(976318377106520349, 999999999999999999),
            "product_id" => rand(788032119674292922, 999999999999999999),
            "variant_id" => rand(457924702898157455, 999999999999999999),
            "name" => "Test Product 2",
            "title" => "Test Product 2",
            "sku" => "TEST-SKU-" . rand(1000, 9999),
            "price" => "99.99",
            "quantity" => 1,
            "grams" => 300,
            "total_discount" => "0.00",
            "requires_shipping" => true,
            "taxable" => true
        ]
    ],
    "shipping_lines" => [
        [
            "id" => rand(271878346596884015, 999999999999999999),
            "title" => "Standard Shipping",
            "price" => "10.00",
            "code" => "STANDARD",
            "source" => "shopify"
        ]
    ]
];

$results = [];
$errors = [];

// Step 1: Connect to database
try {
    $pdo = new PDO(
        "mysql:host={$db_config['host']};dbname={$db_config['database']};charset=utf8mb4",
        $db_config['username'],
        $db_config['password'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    $results['database_connection'] = '✅ Connected to database: ' . $db_config['database'];
} catch (PDOException $e) {
    $errors[] = '❌ Database connection failed: ' . $e->getMessage();
    display_results($results, $errors);
    exit;
}

// Step 2: Check if sma_sales table exists
try {
    $stmt = $pdo->query("SHOW TABLES LIKE 'sma_sales'");
    if ($stmt->rowCount() > 0) {
        $results['table_check'] = '✅ Table sma_sales exists';
    } else {
        $errors[] = '❌ Table sma_sales not found';
    }
} catch (PDOException $e) {
    $errors[] = '❌ Error checking tables: ' . $e->getMessage();
}

// Step 3: Send order to integration endpoint
$integration_url = 'http://localhost/avenzur/ShopifyIntegration/run-integration.php';
$payload_json = json_encode($shopify_order);
$results['test_order'] = "📤 Sending test order: {$shopify_order['name']}";

$ch = curl_init($integration_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $payload_json);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Content-Type: application/json"
]);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curl_error = curl_error($ch);
curl_close($ch);

if ($curl_error) {
    $errors[] = "❌ cURL Error: $curl_error";
} else {
    $results['integration_call'] = "✅ Integration endpoint responded with HTTP $http_code";
    $results['response'] = $response;
}

// Step 4: Check if order was saved to database
sleep(1); // Wait a moment for processing

try {
    // Search for the order in sma_sales
    $stmt = $pdo->prepare("
        SELECT 
            id,
            reference_no,
            customer,
            total,
            created_at
        FROM sma_sales 
        WHERE customer LIKE ? 
        ORDER BY id DESC 
        LIMIT 1
    ");
    $stmt->execute(["%{$shopify_order['customer']['email']}%"]);
    $sale = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($sale) {
        $results['database_save'] = '✅ Order found in database!';
        $results['sale_details'] = [
            'ID' => $sale['id'],
            'Reference No' => $sale['reference_no'],
            'Customer' => $sale['customer'],
            'Total' => $sale['total'],
            'Created At' => $sale['created_at']
        ];

        // Check for line items
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as count 
            FROM sma_sale_items 
            WHERE sale_id = ?
        ");
        $stmt->execute([$sale['id']]);
        $items_count = $stmt->fetchColumn();

        $results['line_items'] = "✅ Found $items_count line items";
    } else {
        // Try to find recent sales
        $stmt = $pdo->query("
            SELECT 
                id,
                reference_no,
                customer,
                total,
                created_at
            FROM sma_sales 
            ORDER BY id DESC 
            LIMIT 5
        ");
        $recent_sales = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (count($recent_sales) > 0) {
            $results['recent_sales'] = '⚠️ Order not found, but here are recent sales:';
            $results['recent_sales_list'] = $recent_sales;
        } else {
            $errors[] = '❌ No sales found in database';
        }
    }
} catch (PDOException $e) {
    $errors[] = '❌ Error querying database: ' . $e->getMessage();
}

// Step 5: Check for customers
try {
    $stmt = $pdo->query("
        SELECT COUNT(*) as count 
        FROM sma_companies 
        WHERE company_type = 'customer'
    ");
    $customer_count = $stmt->fetchColumn();
    $results['customers'] = "📊 Total customers in database: $customer_count";
} catch (PDOException $e) {
    $errors[] = '❌ Error counting customers: ' . $e->getMessage();
}

// Display results
display_results($results, $errors, $shopify_order);

function display_results($results, $errors, $order = null) {
    header('Content-Type: text/html; charset=utf-8');
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>Shopify Sync Test Results</title>
        <style>
            body {
                font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
                padding: 20px;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                min-height: 100vh;
            }
            .container {
                max-width: 1200px;
                margin: 0 auto;
                background: white;
                border-radius: 16px;
                padding: 40px;
                box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            }
            h1 {
                color: #2d3748;
                margin-bottom: 10px;
                font-size: 32px;
            }
            .subtitle {
                color: #718096;
                margin-bottom: 30px;
                font-size: 16px;
            }
            .section {
                background: #f7fafc;
                border-radius: 12px;
                padding: 20px;
                margin-bottom: 20px;
                border-left: 4px solid #667eea;
            }
            .section.error {
                border-left-color: #f56565;
                background: #fff5f5;
            }
            .section h2 {
                color: #2d3748;
                font-size: 18px;
                margin: 0 0 15px 0;
            }
            .result-item {
                padding: 10px 0;
                border-bottom: 1px solid #e2e8f0;
                color: #4a5568;
                line-height: 1.6;
            }
            .result-item:last-child {
                border-bottom: none;
            }
            .success { color: #48bb78; font-weight: 600; }
            .error-text { color: #f56565; font-weight: 600; }
            .warning { color: #ed8936; font-weight: 600; }
            pre {
                background: #1a202c;
                color: #e2e8f0;
                padding: 15px;
                border-radius: 8px;
                overflow-x: auto;
                font-size: 12px;
                max-height: 300px;
                overflow-y: auto;
            }
            table {
                width: 100%;
                border-collapse: collapse;
                margin-top: 10px;
            }
            table th {
                background: #edf2f7;
                padding: 10px;
                text-align: left;
                font-weight: 600;
                color: #2d3748;
            }
            table td {
                padding: 10px;
                border-bottom: 1px solid #e2e8f0;
                color: #4a5568;
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
            .actions {
                margin-top: 30px;
                display: flex;
                gap: 10px;
            }
            .btn {
                display: inline-block;
                padding: 12px 24px;
                border-radius: 8px;
                text-decoration: none;
                font-weight: 600;
                transition: opacity 0.2s;
            }
            .btn:hover {
                opacity: 0.8;
            }
            .btn-primary {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: white;
            }
            .btn-secondary {
                background: #edf2f7;
                color: #2d3748;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <h1>🧪 Shopify Sync Test Results</h1>
            <p class="subtitle">Testing Shopify order synchronization with Avenzur database</p>

            <?php if (!empty($errors)): ?>
            <div class="section error">
                <h2>❌ Errors Detected</h2>
                <?php foreach ($errors as $error): ?>
                    <div class="result-item error-text"><?= htmlspecialchars($error) ?></div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <?php if (!empty($results)): ?>
            <div class="section">
                <h2>✅ Test Results</h2>
                <?php foreach ($results as $key => $value): ?>
                    <?php if (is_array($value)): ?>
                        <div class="result-item">
                            <strong><?= htmlspecialchars(ucwords(str_replace('_', ' ', $key))) ?>:</strong>
                            <table>
                                <?php foreach ($value as $k => $v): ?>
                                    <?php if (is_array($v)): ?>
                                        <tr>
                                            <td colspan="2">
                                                <strong>Sale #<?= $k + 1 ?>:</strong><br>
                                                <?php foreach ($v as $field => $val): ?>
                                                    <?= htmlspecialchars($field) ?>: <?= htmlspecialchars($val) ?><br>
                                                <?php endforeach; ?>
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <tr>
                                            <td><strong><?= htmlspecialchars($k) ?></strong></td>
                                            <td><?= htmlspecialchars($v) ?></td>
                                        </tr>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="result-item"><?= $value ?></div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <?php if ($order): ?>
            <div class="section">
                <h2>📦 Test Order Details</h2>
                <div class="result-item">
                    <strong>Order Name:</strong> <?= htmlspecialchars($order['name']) ?>
                    <span class="badge">Test Order</span>
                </div>
                <div class="result-item">
                    <strong>Customer Email:</strong> <?= htmlspecialchars($order['email']) ?>
                </div>
                <div class="result-item">
                    <strong>Total Price:</strong> <?= htmlspecialchars($order['currency']) ?> <?= htmlspecialchars($order['total_price']) ?>
                </div>
                <div class="result-item">
                    <strong>Line Items:</strong> <?= count($order['line_items']) ?> products
                </div>
                <div class="result-item">
                    <strong>Status:</strong> <?= htmlspecialchars($order['financial_status']) ?> / <?= htmlspecialchars($order['fulfillment_status']) ?>
                </div>
            </div>
            <?php endif; ?>

            <div class="actions">
                <a href="test_sync_simple.php" class="btn btn-primary">🔄 Run Test Again</a>
                <a href="http://localhost/phpmyadmin/index.php?route=/sql&db=avnzor&table=sma_sales" target="_blank" class="btn btn-secondary">📊 View Sales in Database</a>
                <a href="public/index.php" class="btn btn-secondary">🏠 Back to Dashboard</a>
            </div>
        </div>
    </body>
    </html>
    <?php
}

