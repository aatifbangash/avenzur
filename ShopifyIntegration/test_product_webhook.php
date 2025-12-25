<?php
/**
 * Shopify Product Webhook Test
 * Tests if Shopify product webhooks are being processed correctly
 *
 * Usage: http://localhost/avenzur/ShopifyIntegration/test_product_webhook.php
 */

// Database configuration (from .env)
$db_config = [
    'host' => 'localhost',
    'database' => 'avnzor',
    'username' => 'root',
    'password' => ''
];

// Sample Shopify product webhook payload
$shopify_product = [
    "admin_graphql_api_id" => "gid://shopify/Product/788032119674292922",
    "body_html" => "<p>This is a high-quality <strong>Example T-Shirt</strong> made from 100% cotton. Perfect for everyday wear!</p>",
    "created_at" => date('Y-m-d\TH:i:sP'),
    "handle" => "example-t-shirt-" . rand(1000, 9999),
    "id" => rand(788032119674292922, 999999999999999999),
    "product_type" => "T-Shirts",
    "published_at" => date('Y-m-d\TH:i:sP'),
    "template_suffix" => null,
    "title" => "Example T-Shirt #" . rand(100, 999),
    "updated_at" => date('Y-m-d\TH:i:sP'),
    "vendor" => "Test Vendor " . rand(1, 10),
    "status" => "active",
    "published_scope" => "web",
    "tags" => "example, mens, t-shirt, test",
    "variants" => [
        [
            "admin_graphql_api_id" => "gid://shopify/ProductVariant/642667041472713922",
            "barcode" => "TEST-" . rand(100000, 999999),
            "compare_at_price" => "24.99",
            "created_at" => date('Y-m-d\TH:i:sP'),
            "id" => rand(642667041472713922, 999999999999999999),
            "inventory_policy" => "deny",
            "position" => 1,
            "price" => "19.99",
            "product_id" => 788032119674292922,
            "sku" => "TEST-SKU-" . rand(1000, 9999),
            "taxable" => true,
            "title" => "Small",
            "updated_at" => date('Y-m-d\TH:i:sP'),
            "option1" => "Small",
            "option2" => null,
            "option3" => null,
            "image_id" => null,
            "inventory_item_id" => null,
            "inventory_quantity" => rand(50, 200),
            "old_inventory_quantity" => 0
        ],
        [
            "admin_graphql_api_id" => "gid://shopify/ProductVariant/757650484644203962",
            "barcode" => "TEST-" . rand(100000, 999999),
            "compare_at_price" => "24.99",
            "created_at" => date('Y-m-d\TH:i:sP'),
            "id" => rand(757650484644203962, 999999999999999999),
            "inventory_policy" => "deny",
            "position" => 2,
            "price" => "19.99",
            "product_id" => 788032119674292922,
            "sku" => null,
            "taxable" => true,
            "title" => "Medium",
            "updated_at" => date('Y-m-d\TH:i:sP'),
            "option1" => "Medium",
            "option2" => null,
            "option3" => null,
            "image_id" => null,
            "inventory_item_id" => null,
            "inventory_quantity" => rand(30, 150),
            "old_inventory_quantity" => 0
        ]
    ],
    "options" => [
        [
            "id" => rand(1000, 9999),
            "product_id" => 788032119674292922,
            "name" => "Size",
            "position" => 1,
            "values" => ["Small", "Medium", "Large"]
        ]
    ],
    "images" => [
        [
            "id" => rand(1000000, 9999999),
            "product_id" => 788032119674292922,
            "position" => 1,
            "created_at" => date('Y-m-d\TH:i:sP'),
            "updated_at" => date('Y-m-d\TH:i:sP'),
            "src" => "https://cdn.shopify.com/s/files/example.jpg"
        ]
    ],
    "image" => [
        "id" => rand(1000000, 9999999),
        "product_id" => 788032119674292922,
        "position" => 1,
        "created_at" => date('Y-m-d\TH:i:sP'),
        "updated_at" => date('Y-m-d\TH:i:sP'),
        "src" => "https://cdn.shopify.com/s/files/example.jpg"
    ],
    "has_variants_that_requires_components" => false,
    "category" => null
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

// Step 2: Check if sma_products table exists
try {
    $stmt = $pdo->query("SHOW TABLES LIKE 'sma_products'");
    if ($stmt->rowCount() > 0) {
        $results['table_check'] = '✅ Table sma_products exists';
    } else {
        $errors[] = '❌ Table sma_products not found';
    }
} catch (PDOException $e) {
    $errors[] = '❌ Error checking tables: ' . $e->getMessage();
}

// Step 3: Get products count before webhook
try {
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM sma_products");
    $countBefore = $stmt->fetchColumn();
    $results['products_before'] = "📊 Products in database before: $countBefore";
} catch (PDOException $e) {
    $errors[] = '❌ Error counting products: ' . $e->getMessage();
}

// Step 4: Send product webhook to webhook endpoint
$webhook_url = 'http://localhost/avenzur/ShopifyIntegration/public/webhooks.php';
$payload_json = json_encode($shopify_product);
$results['test_product'] = "📤 Sending test product webhook: {$shopify_product['title']}";
$results['shopify_id'] = "🆔 Shopify Product ID: {$shopify_product['id']}";

// Calculate HMAC (if needed)
$hmac = base64_encode(hash_hmac('sha256', $payload_json, 'test-secret', true));

$ch = curl_init($webhook_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $payload_json);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Content-Type: application/json",
    "X-Shopify-Topic: products/create",
    "X-Shopify-Hmac-Sha256: $hmac",
    "X-Shopify-Shop-Domain: test-store.myshopify.com"
]);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curl_error = curl_error($ch);
curl_close($ch);

if ($curl_error) {
    $errors[] = "❌ cURL Error: $curl_error";
} else {
    $results['webhook_call'] = "✅ Webhook endpoint responded with HTTP $http_code";

    // Try to decode response
    $response_data = json_decode($response, true);
    if ($response_data) {
        $results['webhook_response'] = $response_data;
    } else {
        $results['webhook_raw_response'] = $response;
    }
}

// Step 5: Wait a moment and check if product was saved
sleep(1);

try {
    // Search for the product in sma_products by Shopify ID (stored in cf1)
    $stmt = $pdo->prepare("
        SELECT 
            id,
            code,
            name,
            price,
            quantity,
            category_id,
            brand,
            cf1 as shopify_id,
            cf2 as status,
            cf3 as tags,
            created_at
        FROM sma_products 
        WHERE cf1 = ?
        LIMIT 1
    ");
    $stmt->execute([$shopify_product['id']]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($product) {
        $results['database_save'] = '✅ Product found in database!';
        $results['product_details'] = [
            'ID' => $product['id'],
            'Code/SKU' => $product['code'],
            'Name' => $product['name'],
            'Price' => $product['price'],
            'Quantity' => $product['quantity'],
            'Category ID' => $product['category_id'],
            'Brand ID' => $product['brand'],
            'Shopify ID' => $product['shopify_id'],
            'Status' => $product['status'],
            'Tags' => $product['tags']
        ];
    } else {
        $errors[] = '❌ Product not found in database after webhook';

        // Try to find recent products
        $stmt = $pdo->query("
            SELECT 
                id,
                code,
                name,
                price,
                cf1 as shopify_id
            FROM sma_products 
            ORDER BY id DESC 
            LIMIT 5
        ");
        $recent_products = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (count($recent_products) > 0) {
            $results['recent_products'] = '⚠️ Product not found, but here are recent products:';
            $results['recent_products_list'] = $recent_products;
        }
    }
} catch (PDOException $e) {
    $errors[] = '❌ Error querying database: ' . $e->getMessage();
}

// Step 6: Get products count after webhook
try {
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM sma_products");
    $countAfter = $stmt->fetchColumn();
    $results['products_after'] = "📊 Products in database after: $countAfter";

    if ($countAfter > $countBefore) {
        $results['products_diff'] = "✅ New products added: " . ($countAfter - $countBefore);
    } else {
        $results['products_diff'] = "⚠️ No new products added (product may have been updated)";
    }
} catch (PDOException $e) {
    $errors[] = '❌ Error counting products: ' . $e->getMessage();
}

// Step 7: Check categories
try {
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM sma_categories");
    $category_count = $stmt->fetchColumn();
    $results['categories'] = "📊 Total categories in database: $category_count";
} catch (PDOException $e) {
    $errors[] = '❌ Error counting categories: ' . $e->getMessage();
}

// Step 8: Check brands
try {
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM sma_brands");
    $brand_count = $stmt->fetchColumn();
    $results['brands'] = "📊 Total brands in database: $brand_count";
} catch (PDOException $e) {
    $errors[] = '❌ Error counting brands: ' . $e->getMessage();
}

// Display results
display_results($results, $errors, $shopify_product);

function display_results($results, $errors, $product = null) {
    header('Content-Type: text/html; charset=utf-8');
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Shopify Product Webhook Test</title>
        <style>
            * { margin: 0; padding: 0; box-sizing: border-box; }
            body {
                font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                padding: 20px;
                min-height: 100vh;
            }
            .container {
                max-width: 1200px;
                margin: 0 auto;
                background: white;
                border-radius: 12px;
                box-shadow: 0 10px 40px rgba(0,0,0,0.2);
                overflow: hidden;
            }
            .header {
                background: linear-gradient(135deg, #5f27cd 0%, #341f97 100%);
                color: white;
                padding: 30px;
                text-align: center;
            }
            .header h1 {
                font-size: 28px;
                margin-bottom: 10px;
            }
            .header p {
                opacity: 0.9;
                font-size: 14px;
            }
            .content {
                padding: 30px;
            }
            .section {
                margin-bottom: 30px;
                padding: 20px;
                background: #f8f9fa;
                border-radius: 8px;
                border-left: 4px solid #5f27cd;
            }
            .section h2 {
                color: #2d3436;
                margin-bottom: 15px;
                font-size: 20px;
                display: flex;
                align-items: center;
            }
            .section h2::before {
                content: '▶';
                margin-right: 10px;
                color: #5f27cd;
            }
            .result-item {
                padding: 12px;
                margin: 8px 0;
                background: white;
                border-radius: 6px;
                border-left: 3px solid #00b894;
                font-size: 14px;
            }
            .error-item {
                padding: 12px;
                margin: 8px 0;
                background: #fff5f5;
                border-radius: 6px;
                border-left: 3px solid #e74c3c;
                color: #c0392b;
                font-size: 14px;
            }
            .details-grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
                gap: 15px;
                margin-top: 15px;
            }
            .detail-card {
                background: white;
                padding: 15px;
                border-radius: 6px;
                border: 1px solid #e1e8ed;
            }
            .detail-card .label {
                font-weight: 600;
                color: #5f27cd;
                font-size: 12px;
                text-transform: uppercase;
                margin-bottom: 5px;
            }
            .detail-card .value {
                font-size: 16px;
                color: #2d3436;
                word-break: break-word;
            }
            .json-block {
                background: #2d3436;
                color: #dfe6e9;
                padding: 15px;
                border-radius: 6px;
                overflow-x: auto;
                font-family: 'Courier New', monospace;
                font-size: 13px;
                margin-top: 10px;
            }
            .status-badge {
                display: inline-block;
                padding: 5px 12px;
                border-radius: 20px;
                font-size: 12px;
                font-weight: 600;
                margin-left: 10px;
            }
            .badge-success {
                background: #00b894;
                color: white;
            }
            .badge-error {
                background: #e74c3c;
                color: white;
            }
            .badge-warning {
                background: #f39c12;
                color: white;
            }
            table {
                width: 100%;
                border-collapse: collapse;
                margin-top: 10px;
                background: white;
                border-radius: 6px;
                overflow: hidden;
            }
            th {
                background: #5f27cd;
                color: white;
                padding: 12px;
                text-align: left;
                font-weight: 600;
                font-size: 13px;
            }
            td {
                padding: 12px;
                border-bottom: 1px solid #e1e8ed;
                font-size: 13px;
            }
            tr:last-child td {
                border-bottom: none;
            }
            .footer {
                text-align: center;
                padding: 20px;
                color: #636e72;
                font-size: 13px;
                border-top: 1px solid #e1e8ed;
            }
            .refresh-btn {
                display: inline-block;
                margin-top: 20px;
                padding: 12px 30px;
                background: #5f27cd;
                color: white;
                text-decoration: none;
                border-radius: 6px;
                font-weight: 600;
                transition: background 0.3s;
            }
            .refresh-btn:hover {
                background: #341f97;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="header">
                <h1>🛍️ Shopify Product Webhook Test</h1>
                <p>Testing product sync from Shopify to Avenzur ERP</p>
                <?php if (count($errors) == 0 && isset($results['database_save'])): ?>
                    <span class="status-badge badge-success">✅ TEST PASSED</span>
                <?php elseif (count($errors) > 0): ?>
                    <span class="status-badge badge-error">❌ TEST FAILED</span>
                <?php else: ?>
                    <span class="status-badge badge-warning">⚠️ PARTIAL SUCCESS</span>
                <?php endif; ?>
            </div>

            <div class="content">
                <?php if (count($errors) > 0): ?>
                    <div class="section">
                        <h2>❌ Errors</h2>
                        <?php foreach ($errors as $error): ?>
                            <div class="error-item"><?php echo htmlspecialchars($error); ?></div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <div class="section">
                    <h2>📋 Test Results</h2>
                    <?php foreach ($results as $key => $result): ?>
                        <?php if (is_array($result)): ?>
                            <?php if ($key === 'product_details'): ?>
                                <div class="details-grid">
                                    <?php foreach ($result as $label => $value): ?>
                                        <div class="detail-card">
                                            <div class="label"><?php echo htmlspecialchars($label); ?></div>
                                            <div class="value"><?php echo htmlspecialchars($value); ?></div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php elseif ($key === 'recent_products_list'): ?>
                                <table>
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Code/SKU</th>
                                            <th>Name</th>
                                            <th>Price</th>
                                            <th>Shopify ID</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($result as $prod): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($prod['id']); ?></td>
                                                <td><?php echo htmlspecialchars($prod['code']); ?></td>
                                                <td><?php echo htmlspecialchars($prod['name']); ?></td>
                                                <td><?php echo htmlspecialchars($prod['price']); ?></td>
                                                <td><?php echo htmlspecialchars($prod['shopify_id']); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            <?php else: ?>
                                <div class="json-block"><?php echo json_encode($result, JSON_PRETTY_PRINT); ?></div>
                            <?php endif; ?>
                        <?php else: ?>
                            <div class="result-item"><?php echo htmlspecialchars($result); ?></div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>

                <?php if ($product): ?>
                    <div class="section">
                        <h2>📦 Sent Product Data</h2>
                        <div class="details-grid">
                            <div class="detail-card">
                                <div class="label">Product ID</div>
                                <div class="value"><?php echo htmlspecialchars($product['id']); ?></div>
                            </div>
                            <div class="detail-card">
                                <div class="label">Title</div>
                                <div class="value"><?php echo htmlspecialchars($product['title']); ?></div>
                            </div>
                            <div class="detail-card">
                                <div class="label">Product Type</div>
                                <div class="value"><?php echo htmlspecialchars($product['product_type']); ?></div>
                            </div>
                            <div class="detail-card">
                                <div class="label">Vendor</div>
                                <div class="value"><?php echo htmlspecialchars($product['vendor']); ?></div>
                            </div>
                            <div class="detail-card">
                                <div class="label">Price</div>
                                <div class="value"><?php echo htmlspecialchars($product['variants'][0]['price']); ?></div>
                            </div>
                            <div class="detail-card">
                                <div class="label">Inventory</div>
                                <div class="value"><?php echo htmlspecialchars($product['variants'][0]['inventory_quantity']); ?></div>
                            </div>
                        </div>

                        <h3 style="margin-top: 20px; color: #2d3436;">Variants (<?php echo count($product['variants']); ?>)</h3>
                        <table>
                            <thead>
                                <tr>
                                    <th>Title</th>
                                    <th>SKU</th>
                                    <th>Price</th>
                                    <th>Inventory</th>
                                    <th>Barcode</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($product['variants'] as $variant): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($variant['title']); ?></td>
                                        <td><?php echo htmlspecialchars($variant['sku'] ?? 'N/A'); ?></td>
                                        <td><?php echo htmlspecialchars($variant['price']); ?></td>
                                        <td><?php echo htmlspecialchars($variant['inventory_quantity']); ?></td>
                                        <td><?php echo htmlspecialchars($variant['barcode'] ?? 'N/A'); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>

                <div style="text-align: center;">
                    <a href="?" class="refresh-btn">🔄 Run Test Again</a>
                </div>
            </div>

            <div class="footer">
                <p>Avenzur ERP - Shopify Integration Test Suite</p>
                <p>Generated: <?php echo date('Y-m-d H:i:s'); ?></p>
            </div>
        </div>
    </body>
    </html>
    <?php
}

