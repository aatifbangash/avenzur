<?php
require_once 'config.php';

class Database {
    private $mysqli;

    public function __construct() {
        $this->mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        if ($this->mysqli->connect_errno) {
            die("DB connection failed: " . $this->mysqli->connect_error);
        }
        $this->mysqli->set_charset("utf8");
    }

    public function getUnshippedOrders($limit = MAX_ORDERS_PER_RUN) {
        $sql = "SELECT si.sale_id, si.product_name, si.quantity, c.first_name as customer_name, c.phone as customer_phone,
                       c.email as customer_email, c.address as customer_address, c.city as cusotmer_city, c.country as customer_country, s.id
                 FROM sma_sales s
                 left join sma_sale_items si on si.sale_id = s.id
                 left join sma_companies c on s.customer_id = c.id
                WHERE s.courier_order_status IS NULL OR s.courier_order_status='pending' OR s.courier_order_status=''
            AND s.id=2620
                ORDER BY id ASC
                LIMIT ?";
        $stmt = $this->mysqli->prepare($sql);
        $stmt->bind_param("i", $limit);
        $stmt->execute();
        $res = $stmt->get_result();
        return $res->fetch_all(MYSQLI_ASSOC);
    }

    public function updateOrderStatus($orderId, $trackingNumber, $trackingStatus) {
        $sql = "UPDATE sma_sales 
                SET courier_status='shipped', courier_order_tracking_id=?, courier_order_status = ? , updated_at=NOW()
                WHERE id=?";
        $stmt = $this->mysqli->prepare($sql);
        $stmt->bind_param("ssi", $trackingNumber, $trackingStatus, $orderId);
        return $stmt->execute();
    }

    public function close() {
        $this->mysqli->close();
    }
}
