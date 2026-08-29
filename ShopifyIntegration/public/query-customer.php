<?php
require_once __DIR__ . '/../autoload.php';

use App\Services\DatabaseService;
use App\Services\Database\CustomerRepository;
use App\Services\Database\AddressRepository;
use App\Services\Database\SaleRepository;
use App\Services\Database\SaleItemRepository;

$customerId = isset($_GET['id']) ? intval($_GET['id']) : null;

$customer = null;
$addresses = [];
$orders = [];
$orderItems = [];

if ($customerId) {
    try {
        // Initialize database service
        $db = new DatabaseService();
        $pdo = $db->getConnection();
        
        // Initialize repositories
        $customerRepo = new CustomerRepository($pdo);
        $addressRepo = new AddressRepository($pdo);
        $saleRepo = new SaleRepository($pdo);
        $saleItemRepo = new SaleItemRepository($pdo);
        
        // Get customer details
        $customer = $customerRepo->getCustomerById($customerId);
        
        // Get customer addresses
        if ($customer) {
            $addresses = $addressRepo->getAddressesByCompanyId($customerId);
            
            // Get customer orders
            $orders = $saleRepo->getSalesByCustomerId($customerId);
            
            // Get order items for each order
            foreach ($orders as $order) {
                $orderItems[$order['id']] = $saleItemRepo->getSaleItemsBySaleId($order['id']);
            }
        }
        
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title><?= $customerId ? "Customer #$customerId" : "Customer Query" ?> - Details</title>
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
        }
        .header {
            background: white;
            color: #2d3748;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .header h1 {
            font-size: 32px;
            margin-bottom: 10px;
            color: #1a202c;
        }
        .header .subtitle {
            color: #718096;
        }
        .architecture-badge {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 15px 20px;
            border-radius: 8px;
            margin-top: 15px;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            font-size: 13px;
        }
        .search-box {
            background: white;
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .search-box input {
            padding: 12px;
            border: 2px solid #e2e8f0;
            border-radius: 6px;
            font-size: 16px;
            width: 200px;
        }
        .search-box button {
            padding: 12px 24px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            margin-left: 10px;
            font-size: 16px;
            font-weight: 600;
        }
        .search-box button:hover {
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
        }
        .card {
            background: white;
            border-radius: 12px;
            padding: 30px;
            margin-bottom: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .card h2 {
            color: #2d3748;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 3px solid #667eea;
            font-size: 24px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .not-found {
            background: white;
            border: 2px solid #fc8181;
            color: #c53030;
            padding: 40px;
            border-radius: 12px;
            text-align: center;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .not-found h2 {
            margin-bottom: 10px;
            border: none;
        }
        .row {
            display: grid;
            grid-template-columns: 200px 1fr;
            padding: 12px 0;
            border-bottom: 1px solid #e2e8f0;
        }
        .row:last-child {
            border-bottom: none;
        }
        .label {
            font-weight: 600;
            color: #4a5568;
        }
        .value {
            color: #2d3748;
        }
        .badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
        }
        .badge.active {
            background: #c6f6d5;
            color: #22543d;
        }
        .badge.inactive {
            background: #fed7d7;
            color: #742a2a;
        }
        .order-card {
            background: #f7fafc;
            border-left: 4px solid #667eea;
            padding: 20px;
            margin: 15px 0;
            border-radius: 8px;
        }
        .order-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            flex-wrap: wrap;
            gap: 10px;
        }
        .order-title {
            font-size: 18px;
            font-weight: 700;
            color: #2d3748;
        }
        .order-amount {
            font-size: 24px;
            font-weight: 700;
            color: #667eea;
        }
        .items-list {
            background: white;
            padding: 15px;
            border-radius: 6px;
            margin-top: 10px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th {
            background: #f7fafc;
            padding: 12px;
            text-align: left;
            font-weight: 600;
            color: #4a5568;
            border-bottom: 2px solid #e2e8f0;
        }
        td {
            padding: 12px;
            border-bottom: 1px solid #e2e8f0;
        }
        .summary {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }
        .summary-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
        }
        .summary-label {
            font-size: 14px;
            opacity: 0.9;
            margin-bottom: 8px;
        }
        .summary-value {
            font-size: 32px;
            font-weight: 700;
        }
        .back-button {
            display: inline-block;
            background: #e2e8f0;
            color: #2d3748;
            padding: 10px 20px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 600;
            margin-bottom: 20px;
        }
        .back-button:hover {
            background: #cbd5e0;
        }
        .repo-badge {
            display: inline-block;
            background: #f0f9ff;
            color: #0c4a6e;
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 11px;
            font-weight: 600;
            margin-left: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <?php if (isset($_GET['from_test'])): ?>
            <a href="test-complete.php" class="back-button">← Back to Test</a>
        <?php endif; ?>

        <div class="header">
            <h1>👤 Customer Query</h1>
            <p class="subtitle">View customer details using modular repository architecture</p>
            <div class="architecture-badge">
                <span>🏗️</span>
                <span>CustomerRepository • AddressRepository • SaleRepository • SaleItemRepository</span>
            </div>
        </div>

        <div class="search-box">
            <form method="GET">
                <input type="number" name="id" placeholder="Customer ID" value="<?= $customerId ?>" required>
                <button type="submit">🔍 Search Customer</button>
            </form>
        </div>

        <?php if (isset($error)): ?>
            <div class="not-found">
                <h2>❌ Error</h2>
                <p><?= htmlspecialchars($error) ?></p>
            </div>
        <?php elseif ($customerId && !$customer): ?>
            <div class="not-found">
                <h2>❌ Customer Not Found</h2>
                <p>No customer found with ID: <?= $customerId ?></p>
                <p style="margin-top: 10px; color: #718096;">
                    Repository used: <code>CustomerRepository::getCustomerById()</code>
                </p>
            </div>
        <?php elseif (!$customerId): ?>
            <div class="card">
                <h2>ℹ️ Instructions</h2>
                <p>Enter a customer ID in the search box above to view their details.</p>
            </div>
        <?php else: ?>
            
            <!-- Summary Cards -->
            <div class="summary">
                <div class="summary-card">
                    <div class="summary-label">Total Orders</div>
                    <div class="summary-value"><?= count($orders) ?></div>
                </div>
                <div class="summary-card">
                    <div class="summary-label">Total Spent</div>
                    <div class="summary-value">
                        $<?= number_format(array_sum(array_column($orders, 'grand_total')), 2) ?>
                    </div>
                </div>
                <div class="summary-card">
                    <div class="summary-label">Addresses</div>
                    <div class="summary-value"><?= count($addresses) ?></div>
                </div>
                <div class="summary-card">
                    <div class="summary-label">Account Status</div>
                    <div class="summary-value" style="font-size: 20px;">
                        <span class="badge <?= $customer['active'] ? 'active' : 'inactive' ?>">
                            <?= $customer['active'] ? 'Active' : 'Inactive' ?>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Customer Details -->
            <div class="card">
                <h2>
                    📋 Customer Information
                    <span class="repo-badge">CustomerRepository</span>
                </h2>
                <div class="row">
                    <span class="label">Customer ID:</span>
                    <span class="value"><?= htmlspecialchars($customer['id']) ?></span>
                </div>
                <div class="row">
                    <span class="label">Username:</span>
                    <span class="value"><?= htmlspecialchars($customer['username']) ?></span>
                </div>
                <div class="row">
                    <span class="label">Full Name:</span>
                    <span class="value">
                        <?= htmlspecialchars($customer['first_name']) ?> 
                        <?= htmlspecialchars($customer['last_name']) ?>
                    </span>
                </div>
                <div class="row">
                    <span class="label">Email:</span>
                    <span class="value"><?= htmlspecialchars($customer['email']) ?></span>
                </div>
                <div class="row">
                    <span class="label">Phone:</span>
                    <span class="value"><?= htmlspecialchars($customer['phone'] ?? 'N/A') ?></span>
                </div>
                <div class="row">
                    <span class="label">Country:</span>
                    <span class="value"><?= htmlspecialchars($customer['country'] ?? 'N/A') ?></span>
                </div>
                <div class="row">
                    <span class="label">Status:</span>
                    <span class="value">
                        <span class="badge <?= $customer['active'] ? 'active' : 'inactive' ?>">
                            <?= $customer['active'] ? 'Active' : 'Inactive' ?>
                        </span>
                    </span>
                </div>
                <div class="row">
                    <span class="label">Mobile Verified:</span>
                    <span class="value"><?= $customer['mobile_verified'] ? '✓ Yes' : '✗ No' ?></span>
                </div>
                <div class="row">
                    <span class="label">Created On:</span>
                    <span class="value"><?= htmlspecialchars($customer['created_on'] ?? 'N/A') ?></span>
                </div>
                <div class="row">
                    <span class="label">Group ID:</span>
                    <span class="value"><?= htmlspecialchars($customer['group_id']) ?></span>
                </div>
                <div class="row">
                    <span class="label">Company ID:</span>
                    <span class="value"><?= htmlspecialchars($customer['company_id'] ?? 'N/A') ?></span>
                </div>
            </div>

            <!-- Addresses -->
            <?php if ($addresses): ?>
            <div class="card">
                <h2>
                    📍 Addresses (<?= count($addresses) ?>)
                    <span class="repo-badge">AddressRepository</span>
                </h2>
                <?php foreach ($addresses as $address): ?>
                    <div class="order-card">
                        <div class="row">
                            <span class="label">Address ID:</span>
                            <span class="value"><?= $address['id'] ?></span>
                        </div>
                        <div class="row">
                            <span class="label">Name:</span>
                            <span class="value">
                                <?= htmlspecialchars($address['first_name'] ?? '') ?> 
                                <?= htmlspecialchars($address['last_name'] ?? '') ?>
                            </span>
                        </div>
                        <div class="row">
                            <span class="label">Address:</span>
                            <span class="value">
                                <?= htmlspecialchars($address['line1']) ?>
                                <?php if ($address['line2']): ?>
                                    , <?= htmlspecialchars($address['line2']) ?>
                                <?php endif; ?>
                            </span>
                        </div>
                        <div class="row">
                            <span class="label">City, State, ZIP:</span>
                            <span class="value">
                                <?= htmlspecialchars($address['city']) ?>, 
                                <?= htmlspecialchars($address['state']) ?> 
                                <?= htmlspecialchars($address['postal_code'] ?? '') ?>
                            </span>
                        </div>
                        <div class="row">
                            <span class="label">Country:</span>
                            <span class="value"><?= htmlspecialchars($address['country']) ?></span>
                        </div>
                        <div class="row">
                            <span class="label">Phone:</span>
                            <span class="value"><?= htmlspecialchars($address['phone'] ?? 'N/A') ?></span>
                        </div>
                        <div class="row">
                            <span class="label">Default:</span>
                            <span class="value"><?= $address['is_default'] ? '✓ Yes' : '✗ No' ?></span>
                        </div>
                        <div class="row">
                            <span class="label">Last Updated:</span>
                            <span class="value"><?= htmlspecialchars($address['updated_at']) ?></span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <!-- Orders -->
            <div class="card">
                <h2>
                    🛒 Order History (<?= count($orders) ?>)
                    <span class="repo-badge">SaleRepository</span>
                </h2>
                
                <?php if (empty($orders)): ?>
                    <p style="color: #718096; text-align: center; padding: 20px;">
                        No orders found for this customer.
                    </p>
                <?php else: ?>
                    <?php foreach ($orders as $order): ?>
                        <div class="order-card">
                            <div class="order-header">
                                <div>
                                    <span class="order-title">Order #<?= htmlspecialchars($order['reference_no']) ?></span>
                                    <span class="badge <?= $order['payment_status'] == 'paid' ? 'active' : 'inactive' ?>">
                                        <?= htmlspecialchars($order['payment_status']) ?>
                                    </span>
                                </div>
                                <div class="order-amount">$<?= number_format($order['grand_total'], 2) ?></div>
                            </div>

                            <div class="row">
                                <span class="label">Sale ID:</span>
                                <span class="value"><?= htmlspecialchars($order['id']) ?></span>
                            </div>
                            <div class="row">
                                <span class="label">Date:</span>
                                <span class="value"><?= htmlspecialchars($order['date']) ?></span>
                            </div>
                            <div class="row">
                                <span class="label">Sale Status:</span>
                                <span class="value"><?= htmlspecialchars($order['sale_status']) ?></span>
                            </div>
                            <div class="row">
                                <span class="label">Total Items:</span>
                                <span class="value"><?= htmlspecialchars($order['total_items']) ?></span>
                            </div>
                            <div class="row">
                                <span class="label">Subtotal:</span>
                                <span class="value">$<?= number_format($order['total'], 2) ?></span>
                            </div>
                            <div class="row">
                                <span class="label">Tax:</span>
                                <span class="value">$<?= number_format($order['total_tax'], 2) ?></span>
                            </div>
                            <div class="row">
                                <span class="label">Discount:</span>
                                <span class="value">$<?= number_format($order['total_discount'], 2) ?></span>
                            </div>
                            <?php if ($order['external_id']): ?>
                            <div class="row">
                                <span class="label">Shopify Order ID:</span>
                                <span class="value"><?= htmlspecialchars($order['external_id']) ?></span>
                            </div>
                            <?php endif; ?>

                            <?php if (isset($orderItems[$order['id']]) && !empty($orderItems[$order['id']])): ?>
                                <div class="items-list">
                                    <strong style="color: #2d3748; margin-bottom: 10px; display: block;">
                                        Order Items <span class="repo-badge">SaleItemRepository</span>
                                    </strong>
                                    <table>
                                        <thead>
                                            <tr>
                                                <th>Product</th>
                                                <th>SKU</th>
                                                <th>Quantity</th>
                                                <th>Price</th>
                                                <th>Subtotal</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($orderItems[$order['id']] as $item): ?>
                                                <tr>
                                                    <td><?= htmlspecialchars($item['product_name']) ?></td>
                                                    <td><?= htmlspecialchars($item['product_code']) ?></td>
                                                    <td><?= htmlspecialchars($item['quantity']) ?></td>
                                                    <td>$<?= number_format($item['unit_price'], 2) ?></td>
                                                    <td>$<?= number_format($item['subtotal'], 2) ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

        <?php endif; ?>
    </div>
</body>
</html>