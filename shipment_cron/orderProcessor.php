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

        foreach ($orders as $order) {
      
            $trackingNumber = $this->aramex->createShipment($order);
            //$trackingNumber = false; // For testing failure case';
            if ($trackingNumber) {
                $this->db->updateOrderStatus($order['id'], $trackingNumber, 'shipped');

                // Send notifications
                $customerMsg = "Dear {$order['customer_name']}, your order #{$order['id']} has been shipped to Aramex. Tracking: $trackingNumber";
                send_sms($order['customer_phone'], $customerMsg);
                //send_email($order['customer_email'], "Order Shipped", $customerMsg);

                $managerMsg = "Order #{$order['id']} shipped. Tracking: $trackingNumber";
                send_sms('07777777', $managerMsg); // Warehouse manager
                //send_email('warehouse@company.com', "Order Shipped", $managerMsg);
            } else {
                error_log("Order {$order['id']} shipment failed.");
            }
        }

        $this->db->close();
    }
}
