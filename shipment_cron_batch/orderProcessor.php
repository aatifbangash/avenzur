<?php
require_once 'db.php';
require_once 'aramexClient.php';
require_once 'notifications.php';

class OrderProcessor {
    private $db;
    private $aramex;

    public function __construct() {
        $this->db = new Database();
        $this->aramex = new AramexClient();
    }

    public function processOrders() {
        $orders = $this->db->getUnshippedOrders();
        // echo "<pre>";
        // print_r($orders);exit;

        if (empty($orders)) {
            echo "No orders to process.\n";
            return 0;
        }

     

        // Create all shipments in a single batch call
        $results = $this->aramex->createShipmentsBatch($orders);
        
        $successCount = 0;
        $failCount = 0;
        
        // Process results
        foreach ($results as $result) {
            if ($result['success'] && $result['tracking_number']) {
                // Update database
                $this->db->updateOrderStatus(
                    $result['order_id'], 
                    $result['tracking_number'], 
                    'shipped'
                );
                
                // Find the original order data for notifications
                $order = null;
                foreach ($orders as $o) {
                    if ($o['id'] == $result['order_id']) {
                        $order = $o;
                        break;
                    }
                }
                
                if ($order) {
                    // Send customer notification
                    $customerMsg = "Dear {$order['customer_name']}, your order #{$order['id']} has been shipped. Tracking: {$result['tracking_number']}";
                    send_sms($order['customer_phone'], $customerMsg);
                    
                    // Send manager notification
                    $managerMsg = "Order #{$order['id']} shipped successfully. Tracking: {$result['tracking_number']}";
                    send_sms('00966540369101', $managerMsg);
                }
                
                echo "✓ Order {$result['order_id']}: Shipped - Tracking: {$result['tracking_number']}\n";
                $successCount++;
                
            } else {
                $errorMsg = $result['error'] ?? 'Unknown error';
                error_log("Order {$result['order_id']} shipment failed: {$errorMsg}");
                echo "✗ Order {$result['order_id']}: Failed - {$errorMsg}\n";
                $failCount++;
            }
        }
        
        echo "\n========== Batch Summary ==========\n";
        echo "Total Orders: " . count($orders) . "\n";
        echo "Successful: {$successCount}\n";
        echo "Failed: {$failCount}\n";
        echo "===================================\n";
        
        $this->db->close();
        
        return $successCount;
    }
}
