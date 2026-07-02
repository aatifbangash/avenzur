<?php
/**
 * Test the order processing endpoint
 * Usage: php testWebhook.php
 * Or access via browser: http://localhost/ShopifyIntegration/testWebhook.php
 */

require_once __DIR__ . '/vendor/autoload.php';

// Official Shopify orders/create sample payload
$testPayload = [
    "id" => 820982911946154508,
    "name" => "#9999",
    "email" => "jon@example.com",
    "order_number" => 1234,
    "created_at" => "2021-12-31T19:00:00-05:00",
    "updated_at" => "2021-12-31T19:00:00-05:00",
    "currency" => "USD",
    "total_price" => "388.00",
    "subtotal_price" => "388.00",
    "total_tax" => "0.00",
    "total_discounts" => "20.00",
    "financial_status" => "voided",
    "fulfillment_status" => "pending",
    "tags" => "tag1, tag2",
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
];

// Convert payload to JSON
$payloadJson = json_encode($testPayload);

// Determine URL
$webhookUrl = 'http://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . '/ShopifyIntegration/runShopifyIntegration.php';

if (php_sapi_name() === 'cli') {
    echo "Testing order processing endpoint...\n";
    echo "URL: $webhookUrl\n";
    echo "Order: {$testPayload['name']}\n";
    echo "Order Number: {$testPayload['order_number']}\n";
    echo "Email: {$testPayload['email']}\n";
    echo "Total: {$testPayload['currency']} {$testPayload['total_price']}\n";
    echo "Status: {$testPayload['financial_status']}\n";
    echo "----------------------------------------\n";
}

// Send request
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

if (php_sapi_name() === 'cli') {
    echo "\nHTTP Code: $httpCode\n";
    
    if ($curlError) {
        echo "cURL Error: $curlError\n";
    } else {
        echo "Response:\n";
        echo $response;
        echo "\n";
    }
    
    if ($httpCode === 200) {
        echo "\n✅ Test passed! Order processed successfully.\n";
        echo "Check your database for the new order.\n";
    } else {
        echo "\n❌ Test failed! Check the error above.\n";
    }
} else {
    // Browser output
    header('Content-Type: text/html; charset=utf-8');
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>Order Processing Test</title>
        <style>
            body { 
                font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
                padding: 20px; 
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: #e2e8f0; 
            }
            .container {
                max-width: 1200px;
                margin: 0 auto;
                background: white;
                color: #2d3748;
                border-radius: 16px;
                padding: 30px;
                box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            }
            .success { color: #48bb78; }
            .error { color: #f56565; }
            pre { 
                background: #1a202c; 
                color: #e2e8f0;
                padding: 20px; 
                border-radius: 8px; 
                overflow-x: auto;
                font-size: 13px;
                max-height: 500px;
                overflow-y: auto;
            }
            .info-grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
                gap: 15px;
                margin: 20px 0;
            }
            .info-card {
                background: #f7fafc;
                padding: 15px;
                border-radius: 8px;
                border-left: 4px solid #667eea;
            }
            .info-label {
                font-size: 12px;
                color: #718096;
                text-transform: uppercase;
                letter-spacing: 1px;
            }
            .info-value {
                font-size: 18px;
                font-weight: 600;
                margin-top: 5px;
            }
            h1 { color: #2d3748; margin-bottom: 10px; }
            h2 { color: #2d3748; margin: 20px 0 10px; }
        </style>
    </head>
    <body>
        <div class="container">
            <h1>🧪 Order Processing Test</h1>
            <p style="color: #718096; margin-bottom: 20px;">Testing order processing with Shopify sample data</p>
            
            <div class="info-grid">
                <div class="info-card">
                    <div class="info-label">Order Name</div>
                    <div class="info-value"><?= htmlspecialchars($testPayload['name']) ?></div>
                </div>
                <div class="info-card">
                    <div class="info-label">Order Number</div>
                    <div class="info-value"><?= htmlspecialchars($testPayload['order_number']) ?></div>
                </div>
                <div class="info-card">
                    <div class="info-label">Email</div>
                    <div class="info-value"><?= htmlspecialchars($testPayload['email']) ?></div>
                </div>
                <div class="info-card">
                    <div class="info-label">Total Price</div>
                    <div class="info-value"><?= htmlspecialchars($testPayload['currency']) ?> <?= htmlspecialchars($testPayload['total_price']) ?></div>
                </div>
                <div class="info-card">
                    <div class="info-label">Status</div>
                    <div class="info-value"><?= htmlspecialchars($testPayload['financial_status']) ?></div>
                </div>
                <div class="info-card">
                    <div class="info-label">HTTP Code</div>
                    <div class="info-value">
                        <span class="<?= $httpCode === 200 ? 'success' : 'error' ?>"><?= $httpCode ?></span>
                    </div>
                </div>
            </div>
            
            <?php if ($curlError): ?>
                <p class="error"><strong>cURL Error:</strong> <?= htmlspecialchars($curlError) ?></p>
            <?php endif; ?>
            
            <h2>Response:</h2>
            <pre><?= htmlspecialchars($response) ?></pre>
            
            <?php if ($httpCode === 200): ?>
                <p class="success" style="font-size: 18px; margin-top: 20px;">
                    ✅ Test passed! Order processed successfully. Check your database for the new order.
                </p>
            <?php else: ?>
                <p class="error" style="font-size: 18px; margin-top: 20px;">
                    ❌ Test failed! Check the error above.
                </p>
            <?php endif; ?>
        </div>
    </body>
    </html>
    <?php
}