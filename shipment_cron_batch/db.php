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
        $sql = "SELECT 
            s.id AS sale_id,
            GROUP_CONCAT(CONCAT(si.product_name, ' (', si.quantity, ')') SEPARATOR ', ') AS products,
            c.name AS customer_name,
            c.phone AS customer_phone,
            c.email AS customer_email,
            c.address AS customer_address,
            c.city AS customer_city,
            c.country AS customer_country,
            s.id
        FROM sma_sales s
        LEFT JOIN sma_sale_items si ON si.sale_id = s.id
        LEFT JOIN sma_companies c ON s.customer_id = c.id
        WHERE (s.courier_order_status IS NULL OR s.courier_order_status='pending' OR s.courier_order_status='')
        GROUP BY s.id
        ORDER BY s.id ASC
        LIMIT ?";
        
        $stmt = $this->mysqli->prepare($sql);
        if (!$stmt) {
            die("SQL prepare failed: " . $this->mysqli->error);
        }
        $stmt->bind_param("i", $limit);
        $stmt->execute();
        $res = $stmt->get_result();
        return $res->fetch_all(MYSQLI_ASSOC);
    }

    public function updateOrderStatus($orderId, $trackingNumber, $trackingStatus) {
        $sql = "UPDATE sma_sales 
                SET courier_status='shipped', courier_order_tracking_id=?, courier_order_status = ?, updated_at=NOW()
                WHERE id=?";
        $stmt = $this->mysqli->prepare($sql);
        if (!$stmt) {
            error_log("SQL prepare failed: " . $this->mysqli->error);
            return false;
        }
        $stmt->bind_param("ssi", $trackingNumber, $trackingStatus, $orderId);
        return $stmt->execute();
    }

    public function close() {
        $this->mysqli->close();
    }
}
