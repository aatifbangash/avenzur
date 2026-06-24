<?php
require_once __DIR__ . '/../autoload.php';

use App\Services\OrderEntityExtractor;
use App\Services\DatabaseService;
use App\Config\Config;

$result = null;
$error = null;
$entities = null;
$dbResult = null;
$apiTest = null;

// Check if testing Shopify API
if (isset($_POST['test_shopify_api'])) {
    try {
        $config = Config::getInstance();
        $shopDomain = $config->get('SHOPIFY_SHOP_DOMAIN', 'your-shop.myshopify.com');
        $accessToken = $config->get('SHOPIFY_ACCESS_TOKEN', '');
        
        if (empty($accessToken)) {
            throw new Exception("SHOPIFY_ACCESS_TOKEN not configured in .env");
        }
        
        // Test API connection by fetching shop info
        $url = "https://$shopDomain/admin/api/2024-01/shop.json";
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "X-Shopify-Access-Token: $accessToken",
            "Content-Type: application/json"
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode === 200) {
            $shopData = json_decode($response, true);
            $apiTest = [
                'success' => true,
                'shop_name' => $shopData['shop']['name'] ?? 'Unknown',
                'shop_email' => $shopData['shop']['email'] ?? 'Unknown',
                'shop_domain' => $shopData['shop']['domain'] ?? 'Unknown',
                'plan' => $shopData['shop']['plan_name'] ?? 'Unknown'
            ];
        } else {
            throw new Exception("API returned HTTP $httpCode: $response");
        }
        
    } catch (Exception $e) {
        $apiTest = ['success' => false, 'error' => $e->getMessage()];
    }
}

// Check if testing webhook simulation
if (isset($_POST['test_webhook'])) {
    try {
        $config = Config::getInstance();
        $shopDomain = $config->get('SHOPIFY_SHOP_DOMAIN', 'your-shop.myshopify.com');
        $webhookSecret = $config->get('SHOPIFY_WEBHOOK_SECRET', '');
        
        if (empty($webhookSecret)) {
            throw new Exception("SHOPIFY_WEBHOOK_SECRET not configured in .env");
        }
        
        // Create test payload
        $testPayload = json_encode([
            "id" => rand(1000000, 9999999),
            "order_number" => rand(1000, 9999),
            "name" => "#TEST-" . rand(1000, 9999),
            "email" => "test" . rand(100, 999) . "@example.com",
            "phone" => "+1-555-" . rand(100, 999) . "-" . rand(1000, 9999),
            "total_price" => "359.97",
            "subtotal_price" => "369.97",
            "total_tax" => "0.00",
            "total_discounts" => "20.00",
            "currency" => "USD",
            "financial_status" => "paid",
            "fulfillment_status" => "fulfilled",
            "created_at" => date('Y-m-d H:i:s'),
            "updated_at" => date('Y-m-d H:i:s'),
            "tags" => "webhook-test",
            "customer" => [
                "id" => rand(1000000, 9999999),
                "first_name" => "Test",
                "last_name" => "Webhook",
                "email" => "webhook" . rand(100, 999) . "@example.com",
                "phone" => "+1-555-000-1111",
                "verified_email" => true
            ],
            "billing_address" => [
                "first_name" => "Test",
                "last_name" => "Webhook",
                "address1" => "123 Webhook St",
                "city" => "Test City",
                "province" => "Test State",
                "country" => "United States",
                "zip" => "12345"
            ],
            "shipping_address" => [
                "first_name" => "Test",
                "last_name" => "Webhook",
                "address1" => "123 Webhook St",
                "city" => "Test City",
                "province" => "Test State",
                "country" => "United States",
                "zip" => "12345"
            ],
            "line_items" => [
                [
                    "id" => rand(1000000, 9999999),
                    "title" => "Test Product",
                    "sku" => "TEST-" . rand(1000, 9999),
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
        ]);
        
        // Generate HMAC
        $hmac = base64_encode(hash_hmac('sha256', $testPayload, $webhookSecret, true));
        
        // Simulate webhook call
        $webhookUrl = "http://" . $_SERVER['HTTP_HOST'] . "/ShopifyIntegration/public/webhooks.php";
        
        $ch = curl_init($webhookUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $testPayload);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Content-Type: application/json",
            "X-Shopify-Hmac-SHA256: $hmac",
            "X-Shopify-Topic: orders/create",
            "X-Shopify-Shop-Domain: $shopDomain"
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);
        
        if ($curlError) {
            throw new Exception("cURL Error: $curlError");
        }
        
        $apiTest = [
            'success' => $httpCode === 200,
            'type' => 'webhook',
            'http_code' => $httpCode,
            'response' => $response,
            'payload' => json_decode($testPayload, true)
        ];
        
    } catch (Exception $e) {
        $apiTest = ['success' => false, 'error' => $e->getMessage()];
    }
}

// Define test scenarios
$scenarios = [
    'complete_order' => [
        'name' => 'Complete Order',
        'description' => 'Full order with all data',
        'payload' => [
            "id" => rand(1000000, 9999999),
            "order_number" => rand(1000, 9999),
            "name" => "#TEST-" . rand(1000, 9999),
            "email" => "test" . rand(100, 999) . "@example.com",
            "phone" => "+1-555-" . rand(100, 999) . "-" . rand(1000, 9999),
            "total_price" => "359.97",
            "subtotal_price" => "369.97",
            "total_tax" => "0.00",
            "total_discounts" => "20.00",
            "currency" => "USD",
            "financial_status" => "paid",
            "fulfillment_status" => "fulfilled",
            "created_at" => date('Y-m-d H:i:s'),
            "updated_at" => date('Y-m-d H:i:s'),
            "tags" => "test, complete",
            "test" => true,
            "customer" => [
                "id" => rand(1000000, 9999999),
                "first_name" => "John",
                "last_name" => "Doe",
                "email" => "john.doe" . rand(100, 999) . "@example.com",
                "phone" => "+1-555-999-8888",
                "verified_email" => true,
                "tax_exempt" => false
            ],
            "billing_address" => [
                "first_name" => "John",
                "last_name" => "Doe",
                "address1" => "123 Main Street",
                "address2" => "Apt 4B",
                "city" => "New York",
                "province" => "New York",
                "country" => "United States",
                "zip" => "10001",
                "phone" => "+1-555-123-4567"
            ],
            "shipping_address" => [
                "first_name" => "John",
                "last_name" => "Doe",
                "address1" => "456 Oak Avenue",
                "city" => "Brooklyn",
                "province" => "New York",
                "country" => "United States",
                "zip" => "11201",
                "phone" => "+1-555-987-6543"
            ],
            "line_items" => [
                [
                    "id" => rand(1000000, 9999999),
                    "product_id" => rand(1000, 9999),
                    "title" => "Aviator Sunglasses",
                    "name" => "Aviator Sunglasses",
                    "sku" => "SKU-" . rand(1000, 9999),
                    "price" => "89.99",
                    "quantity" => 1,
                    "total_discount" => "0.00",
                    "grams" => 100,
                    "requires_shipping" => true
                ],
                [
                    "id" => rand(1000000, 9999999),
                    "product_id" => rand(1000, 9999),
                    "title" => "Mid-century Lounger",
                    "name" => "Mid-century Lounger",
                    "sku" => "SKU-" . rand(1000, 9999),
                    "price" => "159.99",
                    "quantity" => 1,
                    "total_discount" => "0.00",
                    "grams" => 1000,
                    "requires_shipping" => true
                ],
                [
                    "id" => rand(1000000, 9999999),
                    "product_id" => rand(1000, 9999),
                    "title" => "Coffee Table",
                    "name" => "Coffee Table",
                    "sku" => "SKU-" . rand(1000, 9999),
                    "price" => "119.99",
                    "quantity" => 1,
                    "total_discount" => "0.00",
                    "grams" => 500,
                    "requires_shipping" => true
                ]
            ],
            "shipping_lines" => [
                [
                    "id" => rand(1000000, 9999999),
                    "title" => "Standard Shipping",
                    "price" => "10.00",
                    "code" => "STANDARD",
                    "source" => "shopify"
                ]
            ]
        ]
    ],
    'cancelled_order' => [
        'name' => 'Cancelled Order',
        'description' => 'Order cancelled by customer',
        'payload' => [
            "id" => rand(1000000, 9999999),
            "order_number" => rand(1000, 9999),
            "name" => "#CANCEL-" . rand(1000, 9999),
            "email" => "cancel" . rand(100, 999) . "@example.com",
            "phone" => "+1-555-" . rand(100, 999) . "-" . rand(1000, 9999),
            "total_price" => "199.99",
            "subtotal_price" => "199.99",
            "currency" => "USD",
            "financial_status" => "voided",
            "fulfillment_status" => null,
            "created_at" => date('Y-m-d H:i:s'),
            "tags" => "cancelled",
            "customer" => [
                "id" => rand(1000000, 9999999),
                "first_name" => "Jane",
                "last_name" => "Smith",
                "email" => "jane" . rand(100, 999) . "@example.com",
                "phone" => "+1-555-888-7777",
                "verified_email" => true
            ],
            "billing_address" => [
                "first_name" => "Jane",
                "last_name" => "Smith",
                "address1" => "789 Elm St",
                "city" => "Boston",
                "province" => "Massachusetts",
                "country" => "United States",
                "zip" => "02101"
            ],
            "shipping_address" => [
                "first_name" => "Jane",
                "last_name" => "Smith",
                "address1" => "789 Elm St",
                "city" => "Boston",
                "province" => "Massachusetts",
                "country" => "United States",
                "zip" => "02101"
            ],
            "line_items" => [
                [
                    "id" => rand(1000000, 9999999),
                    "title" => "Premium Headphones",
                    "sku" => "HDPH-" . rand(1000, 9999),
                    "price" => "199.99",
                    "quantity" => 1,
                    "requires_shipping" => true
                ]
            ],
            "shipping_lines" => [[
                "id" => rand(1000000, 9999999),
                "title" => "Express",
                "price" => "15.00"
            ]]
        ]
    ],
    'bulk_order' => [
        'name' => 'Bulk Order',
        'description' => 'Large order with multiple quantities',
        'payload' => [
            "id" => rand(1000000, 9999999),
            "order_number" => rand(1000, 9999),
            "name" => "#BULK-" . rand(1000, 9999),
            "email" => "bulk" . rand(100, 999) . "@example.com",
            "phone" => "+1-555-" . rand(100, 999) . "-" . rand(1000, 9999),
            "total_price" => "1250.00",
            "subtotal_price" => "1200.00",
            "total_tax" => "100.00",
            "total_discounts" => "50.00",
            "currency" => "USD",
            "financial_status" => "paid",
            "created_at" => date('Y-m-d H:i:s'),
            "tags" => "bulk, wholesale",
            "customer" => [
                "id" => rand(1000000, 9999999),
                "first_name" => "Business",
                "last_name" => "Owner",
                "email" => "business" . rand(100, 999) . "@example.com",
                "phone" => "+1-555-777-6666",
                "verified_email" => true,
                "tax_exempt" => true
            ],
            "billing_address" => [
                "first_name" => "Business",
                "last_name" => "Owner",
                "company" => "Corp Inc",
                "address1" => "100 Business Blvd",
                "city" => "Chicago",
                "province" => "Illinois",
                "country" => "United States",
                "zip" => "60601"
            ],
            "shipping_address" => [
                "first_name" => "Warehouse",
                "last_name" => "Manager",
                "company" => "Corp Warehouse",
                "address1" => "200 Industrial Park",
                "city" => "Chicago",
                "province" => "Illinois",
                "country" => "United States",
                "zip" => "60602"
            ],
            "line_items" => [
                [
                    "id" => rand(1000000, 9999999),
                    "title" => "Office Chair",
                    "sku" => "CHAIR-" . rand(1000, 9999),
                    "price" => "150.00",
                    "quantity" => 5,
                    "total_discount" => "25.00",
                    "requires_shipping" => true
                ],
                [
                    "id" => rand(1000000, 9999999),
                    "title" => "Desk Lamp",
                    "sku" => "LAMP-" . rand(1000, 9999),
                    "price" => "45.00",
                    "quantity" => 10,
                    "total_discount" => "25.00",
                    "requires_shipping" => true
                ]
            ],
            "shipping_lines" => [[
                "id" => rand(1000000, 9999999),
                "title" => "Freight",
                "price" => "50.00"
            ]]
        ]
    ]
];

$selectedScenario = $_GET['scenario'] ?? 'complete_order';
$currentScenario = $scenarios[$selectedScenario];

// Handle test actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['test_shopify_api']) && !isset($_POST['test_webhook'])) {
    try {
        $extractor = new OrderEntityExtractor();
        $entities = $extractor->extractAll($currentScenario['payload']);
        
        if (isset($_POST['save_to_db'])) {
            $db = new DatabaseService();
            $dbResult = $db->saveCompleteOrder($entities);
        }
        
        $result = true;
        
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Complete Integration Test - With Shopify API Testing</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 20px;
            min-height: 100vh;
        }
        .container {
            max-width: 1400px;
            margin: 0 auto;
            background: white;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            overflow: hidden;
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 40px;
            text-align: center;
        }
        .header h1 {
            font-size: 32px;
            margin-bottom: 10px;
        }
        .header p {
            opacity: 0.9;
            font-size: 16px;
        }
        .header .badge {
            display: inline-block;
            background: rgba(255, 255, 255, 0.2);
            padding: 6px 16px;
            border-radius: 20px;
            font-size: 13px;
            margin-top: 10px;
            backdrop-filter: blur(10px);
        }
        .content {
            padding: 40px;
        }
        
        /* API Test Section */
        .api-test-section {
            background: #f7fafc;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            padding: 25px;
            margin-bottom: 30px;
        }
        .api-test-section h2 {
            color: #2d3748;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .api-test-buttons {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }
        
        .scenario-tabs {
            display: flex;
            gap: 10px;
            margin-bottom: 30px;
            border-bottom: 2px solid #e2e8f0;
            overflow-x: auto;
        }
        .scenario-tab {
            padding: 12px 24px;
            background: none;
            border: none;
            border-bottom: 3px solid transparent;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            color: #718096;
            white-space: nowrap;
            transition: all 0.2s;
        }
        .scenario-tab:hover {
            color: #667eea;
        }
        .scenario-tab.active {
            color: #667eea;
            border-bottom-color: #667eea;
        }
        .button-group {
            display: flex;
            gap: 15px;
            margin: 30px 0;
            flex-wrap: wrap;
        }
        .btn {
            padding: 14px 32px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
            transition: transform 0.2s, box-shadow 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        .btn:hover {
            transform: translateY(-2px);
        }
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
        }
        .btn-primary:hover {
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.6);
        }
        .btn-success {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            color: white;
            box-shadow: 0 4px 15px rgba(17, 153, 142, 0.4);
        }
        .btn-success:hover {
            box-shadow: 0 6px 20px rgba(17, 153, 142, 0.6);
        }
        .btn-info {
            background: linear-gradient(135deg, #3182ce 0%, #2c5aa0 100%);
            color: white;
            box-shadow: 0 4px 15px rgba(49, 130, 206, 0.4);
        }
        .btn-info:hover {
            box-shadow: 0 6px 20px rgba(49, 130, 206, 0.6);
        }
        .btn-warning {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            color: white;
            box-shadow: 0 4px 15px rgba(245, 158, 11, 0.4);
        }
        .btn-warning:hover {
            box-shadow: 0 6px 20px rgba(245, 158, 11, 0.6);
        }
        .success-box {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            padding: 30px;
            border-radius: 12px;
            margin: 30px 0;
            color: white;
            box-shadow: 0 4px 15px rgba(17, 153, 142, 0.3);
        }
        .success-box h2 {
            margin-bottom: 20px;
        }
        .error-box {
            background: linear-gradient(135deg, #eb3349 0%, #f45c43 100%);
            padding: 30px;
            border-radius: 12px;
            margin: 30px 0;
            color: white;
            box-shadow: 0 4px 15px rgba(235, 51, 73, 0.3);
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin: 30px 0;
        }
        .stat-card {
            background: #f7fafc;
            padding: 20px;
            border-radius: 12px;
            border-left: 4px solid #667eea;
        }
        .stat-label {
            color: #718096;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 8px;
        }
        .stat-value {
            color: #2d3748;
            font-size: 28px;
            font-weight: 700;
        }
        .entity-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 20px;
            margin: 30px 0;
        }
        .entity-card {
            background: #f7fafc;
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            padding: 20px;
        }
        .entity-card h3 {
            color: #2d3748;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #cbd5e0;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .entity-row {
            display: flex;
            padding: 8px 0;
            border-bottom: 1px solid #e2e8f0;
        }
        .entity-row:last-child {
            border-bottom: none;
        }
        .entity-label {
            font-weight: 600;
            color: #4a5568;
            min-width: 150px;
        }
        .entity-value {
            color: #2d3748;
            flex: 1;
            word-break: break-all;
        }
        .db-results {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 25px;
            border-radius: 12px;
            margin: 20px 0;
        }
        .db-results h3 {
            margin-bottom: 15px;
            font-size: 20px;
        }
        .db-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-top: 15px;
        }
        .db-item {
            background: rgba(255, 255, 255, 0.2);
            padding: 12px;
            border-radius: 8px;
            backdrop-filter: blur(10px);
        }
        .db-item label {
            font-size: 11px;
            opacity: 0.9;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .db-item value {
            display: block;
            font-size: 18px;
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
            transition: background 0.2s;
        }
        .link-button:hover {
            background: rgba(255, 255, 255, 0.3);
        }
        .info-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
        }
        .badge-paid { background: #c6f6d5; color: #22543d; }
        .badge-pending { background: #feebc8; color: #7c2d12; }
        .badge-voided { background: #fed7d7; color: #742a2a; }
        .info-box {
            background: #e6f7ff;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
            border-left: 4px solid #1890ff;
        }
        .info-box h3 {
            color: #0050b3;
            margin-bottom: 10px;
        }
        .info-box p {
            color: #0050b3;
            margin: 5px 0;
        }
        .architecture-badge {
            background: #f0f9ff;
            border: 2px solid #3b82f6;
            color: #1e40af;
            padding: 15px 20px;
            border-radius: 8px;
            margin: 20px 0;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .architecture-badge strong {
            color: #1e40af;
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
        pre {
            background: #1a202c;
            color: #e2e8f0;
            padding: 20px;
            border-radius: 8px;
            overflow-x: auto;
            font-size: 13px;
            margin-top: 10px;
            max-height: 400px;
            overflow-y: auto;
        }
        .api-result {
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin-top: 20px;
        }
        .api-result h4 {
            color: #2d3748;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🧪 Complete Integration Test</h1>
            <p>Test entity extraction, database save, and Shopify API</p>
            <div class="badge">✨ Modular Repository Architecture + API Testing</div>
        </div>

        <div class="content">
            <!-- Shopify API Testing Section -->
            <div class="api-test-section">
                <h2>🔌 Shopify API Testing</h2>
                <p style="color: #718096; margin-bottom: 15px;">
                    Test your Shopify connection and webhook endpoint
                </p>
                
                <div class="api-test-buttons">
                    <form method="POST" style="display: inline;">
                        <button type="submit" name="test_shopify_api" class="btn btn-info">
                            🔗 Test Shopify API Connection
                        </button>
                    </form>
                    
                    <form method="POST" style="display: inline;">
                        <button type="submit" name="test_webhook" class="btn btn-warning">
                            📡 Simulate Webhook Call
                        </button>
                    </form>
                </div>

                <?php if ($apiTest): ?>
                    <?php if ($apiTest['success']): ?>
                        <div class="api-result">
                            <?php if (isset($apiTest['type']) && $apiTest['type'] === 'webhook'): ?>
                                <h4>✅ Webhook Test Result</h4>
                                <div class="entity-row">
                                    <span class="entity-label">HTTP Code:</span>
                                    <span class="entity-value"><?= $apiTest['http_code'] ?></span>
                                </div>
                                <div class="entity-row">
                                    <span class="entity-label">Response:</span>
                                    <span class="entity-value"><?= htmlspecialchars($apiTest['response']) ?></span>
                                </div>
                                <?php if (isset($apiTest['payload'])): ?>
                                <details style="margin-top: 15px;">
                                    <summary>📄 View Test Payload</summary>
                                    <pre><?= htmlspecialchars(json_encode($apiTest['payload'], JSON_PRETTY_PRINT)) ?></pre>
                                </details>
                                <?php endif; ?>
                            <?php else: ?>
                                <h4>✅ Shopify API Connected Successfully!</h4>
                                <div class="entity-row">
                                    <span class="entity-label">Shop Name:</span>
                                    <span class="entity-value"><?= htmlspecialchars($apiTest['shop_name']) ?></span>
                                </div>
                                <div class="entity-row">
                                    <span class="entity-label">Shop Email:</span>
                                    <span class="entity-value"><?= htmlspecialchars($apiTest['shop_email']) ?></span>
                                </div>
                                <div class="entity-row">
                                    <span class="entity-label">Domain:</span>
                                    <span class="entity-value"><?= htmlspecialchars($apiTest['shop_domain']) ?></span>
                                </div>
                                <div class="entity-row">
                                    <span class="entity-label">Plan:</span>
                                    <span class="entity-value"><?= htmlspecialchars($apiTest['plan']) ?></span>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <div class="api-result" style="border-left: 4px solid #fc8181;">
                            <h4 style="color: #c53030;">❌ API Test Failed</h4>
                            <p style="color: #c53030; margin-top: 10px;">
                                <?= htmlspecialchars($apiTest['error']) ?>
                            </p>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>

            <div class="architecture-badge">
                <span style="font-size: 24px;">🏗️</span>
                <div>
                    <strong>Modular Architecture Enabled:</strong> 
                    CustomerRepository • AddressRepository • SaleRepository • SaleItemRepository • ProductRepository
                </div>
            </div>

            <!-- Scenario Tabs -->
            <div class="scenario-tabs">
                <?php foreach ($scenarios as $key => $scenario): ?>
                    <button class="scenario-tab <?= $selectedScenario === $key ? 'active' : '' ?>"
                            onclick="window.location.href='?scenario=<?= $key ?>'">
                        <?= $scenario['name'] ?>
                    </button>
                <?php endforeach; ?>
            </div>

            <!-- Current Scenario Info -->
            <div class="info-box">
                <h3>📋 <?= $currentScenario['name'] ?></h3>
                <p><?= $currentScenario['description'] ?></p>
            </div>

            <?php if (!$result): ?>
                <!-- Test Buttons -->
                <form method="POST">
                    <div class="button-group">
                        <button type="submit" name="extract_only" class="btn btn-primary">
                            🔍 Extract Entities Only
                        </button>
                        <button type="submit" name="save_to_db" class="btn btn-success">
                            💾 Extract & Save to Database
                        </button>
                    </div>
                </form>
            <?php endif; ?>

            <!-- Error Display -->
            <?php if ($error): ?>
                <div class="error-box">
                    <h2>❌ Error Occurred</h2>
                    <p><strong>Message:</strong> <?= htmlspecialchars($error) ?></p>
                </div>
                <form method="POST">
                    <button type="submit" name="extract_only" class="btn btn-primary">
                        🔄 Try Again
                    </button>
                </form>
            <?php endif; ?>

            <!-- Results -->
            <?php if ($result && $entities): ?>
                <div class="success-box">
                    <h2>✅ Entities Extracted Successfully!</h2>
                    <p>All data has been parsed from the Shopify webhook payload using OrderEntityExtractor</p>
                </div>

                <!-- Database Results -->
                <?php if ($dbResult): ?>
                    <div class="db-results">
                        <h3>💾 Database Save Results (Modular Architecture)</h3>
                        <p>✓ Order successfully saved using modular repositories</p>
                        
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

                        <a href="http://localhost/phpmyadmin/index.php?route=/sql&db=avnzor&table=sma_sales&pos=0" 
                           class="link-button" target="_blank">
                            📊 View in phpMyAdmin
                        </a>
                        
                        <a href="query-customer.php?id=<?= $dbResult['customer_id'] ?>&from_test=1" 
                           class="link-button">
                            👤 View Customer Details
                        </a>
                    </div>
                <?php endif; ?>

                <!-- Stats -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-label">Order ID</div>
                        <div class="stat-value"><?= substr($entities['order']['id'], -6) ?></div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-label">Total Price</div>
                        <div class="stat-value">$<?= $entities['order']['total_price'] ?></div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-label">Line Items</div>
                        <div class="stat-value"><?= count($entities['items']['line_items']) ?></div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-label">Status</div>
                        <div class="stat-value" style="font-size: 16px;">
                            <span class="info-badge badge-<?= $entities['order']['financial_status'] ?? 'pending' ?>">
                                <?= strtoupper($entities['order']['financial_status'] ?? 'N/A') ?>
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Entity Details -->
                <div class="entity-grid">
                    <!-- Order -->
                    <div class="entity-card">
                        <h3>📦 Order</h3>
                        <div class="entity-row">
                            <span class="entity-label">Order Name:</span>
                            <span class="entity-value"><?= htmlspecialchars($entities['order']['name']) ?></span>
                        </div>
                        <div class="entity-row">
                            <span class="entity-label">Email:</span>
                            <span class="entity-value"><?= htmlspecialchars($entities['order']['email']) ?></span>
                        </div>
                        <div class="entity-row">
                            <span class="entity-label">Total:</span>
                            <span class="entity-value">$<?= htmlspecialchars($entities['order']['total_price']) ?></span>
                        </div>
                        <div class="entity-row">
                            <span class="entity-label">Status:</span>
                            <span class="entity-value"><?= htmlspecialchars($entities['order']['financial_status']) ?></span>
                        </div>
                    </div>

                    <!-- Customer -->
                    <div class="entity-card">
                        <h3>👤 Customer</h3>
                        <?php if ($entities['customer']): ?>
                            <div class="entity-row">
                                <span class="entity-label">Name:</span>
                                <span class="entity-value">
                                    <?= htmlspecialchars($entities['customer']['first_name']) ?> 
                                    <?= htmlspecialchars($entities['customer']['last_name']) ?>
                                </span>
                            </div>
                            <div class="entity-row">
                                <span class="entity-label">Email:</span>
                                <span class="entity-value"><?= htmlspecialchars($entities['customer']['email']) ?></span>
                            </div>
                            <div class="entity-row">
                                <span class="entity-label">Phone:</span>
                                <span class="entity-value"><?= htmlspecialchars($entities['customer']['phone'] ?? 'N/A') ?></span>
                            </div>
                        <?php else: ?>
                            <p style="color: #a0aec0;">No customer data</p>
                        <?php endif; ?>
                    </div>

                    <!-- Addresses -->
                    <div class="entity-card">
                        <h3>📍 Addresses</h3>
                        <?php if ($entities['addresses']['billing']): ?>
                            <div style="margin-bottom: 15px;">
                                <strong style="color: #4a5568;">Billing:</strong><br>
                                <?= htmlspecialchars($entities['addresses']['billing']['address1']) ?><br>
                                <?= htmlspecialchars($entities['addresses']['billing']['city']) ?>, 
                                <?= htmlspecialchars($entities['addresses']['billing']['province']) ?> 
                                <?= htmlspecialchars($entities['addresses']['billing']['zip']) ?>
                            </div>
                        <?php endif; ?>
                        <?php if ($entities['addresses']['shipping']): ?>
                            <div>
                                <strong style="color: #4a5568;">Shipping:</strong><br>
                                <?= htmlspecialchars($entities['addresses']['shipping']['address1']) ?><br>
                                <?= htmlspecialchars($entities['addresses']['shipping']['city']) ?>, 
                                <?= htmlspecialchars($entities['addresses']['shipping']['province']) ?> 
                                <?= htmlspecialchars($entities['addresses']['shipping']['zip']) ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Line Items -->
                    <div class="entity-card" style="grid-column: span 2;">
                        <h3>🛒 Line Items (<?= count($entities['items']['line_items']) ?>)</h3>
                        <?php foreach ($entities['items']['line_items'] as $item): ?>
                            <div style="background: white; padding: 12px; margin: 8px 0; border-radius: 6px; border-left: 4px solid #667eea;">
                                <strong><?= htmlspecialchars($item['title']) ?></strong><br>
                                <span style="font-size: 14px; color: #718096;">
                                    SKU: <?= $item['sku'] ?? 'N/A' ?> | 
                                    Price: $<?= $item['price'] ?> | 
                                    Qty: <?= $item['quantity'] ?>
                                </span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Full JSON -->
                <details>
                    <summary>📄 View Full Extracted JSON</summary>
                    <pre><?= htmlspecialchars(json_encode($entities, JSON_PRETTY_PRINT)) ?></pre>
                </details>

                <!-- Test Another -->
                <form method="POST" style="text-align: center; margin-top: 30px;">
                    <button type="submit" name="extract_only" class="btn btn-primary">
                        🔄 Test Another Order
                    </button>
                    <button type="submit" name="save_to_db" class="btn btn-success">
                        💾 Extract & Save Another
                    </button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>