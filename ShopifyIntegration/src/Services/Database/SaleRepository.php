<?php

namespace App\Services\Database;

use PDO;
use App\Services\Mappers\ShopifyStatusMapper;

class SaleRepository
{
    private $pdo;
    private $statusMapper;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
        $this->statusMapper = new ShopifyStatusMapper();
    }

    public function saveSale($order, $customerId, $addressId = null)
    {
        $referenceNo = $order['name'] ?? 'SHOP-' . time();
        $billerId = $this->getDefaultBillerId();
        $warehouseId = $this->getDefaultWarehouseId();

        $sql = "INSERT INTO sma_sales (
                    date,
                    reference_no,
                    customer_id,
                    customer,
                    biller_id,
                    biller,
                    warehouse_id,
                    total,
                    product_discount,
                    total_discount,
                    order_discount,
                    product_tax,
                    total_tax,
                    grand_total,
                    sale_status,
                    payment_status,
                    created_by,
                    total_items,
                    pos,
                    paid,
                    address_id,
                    api,
                    shop,
                    sequence_code,
                    invoice_number,
                    customer_name,
                    mobile_number,
                    external_id,
                    sale_invoice
                ) VALUES (
                    NOW(), ?, ?, ?, ?, 'Shopify', ?, ?, 0.00000, ?, 0.00000, 0.00, ?, ?, 
                    ?, ?, 1, 0, 1, 0.00000, ?, 1, 1, ?, ?, ?, ?, ?, 1
                )";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            $referenceNo,
            $customerId,
            $order['email'] ?? 'customer@shopify.com',
            $billerId,
            $warehouseId,
            $order['subtotal_price'] ?? $order['total_price'] ?? 0,
            $order['total_discounts'] ?? 0,
            $order['total_tax'] ?? 0,
            $order['total_price'] ?? 0,
            $this->statusMapper->mapSaleStatus($order['financial_status'] ?? null),
            $this->statusMapper->mapPaymentStatus($order['financial_status'] ?? null),
            $addressId,
            $order['name'] ?? '',
            $order['order_number'] ?? '',
            $order['email'] ?? '',
            $order['phone'] ?? '',
            $order['id'] ?? 0
        ]);

        return $this->pdo->lastInsertId();
    }

    public function orderExists($shopifyOrderId)
    {
        $sql = "SELECT id FROM sma_sales WHERE external_id = ? LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$shopifyOrderId]);
        
        return $stmt->fetch() !== false;
    }

    public function getSaleById($saleId)
    {
        $sql = "SELECT * FROM sma_sales WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$saleId]);
        
        return $stmt->fetch();
    }

    public function getSalesByCustomerId($customerId)
    {
        $sql = "SELECT * FROM sma_sales WHERE customer_id = ? ORDER BY date DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$customerId]);
        
        return $stmt->fetchAll();
    }

    private function getDefaultBillerId()
    {
        $sql = "SELECT id FROM sma_users WHERE active = 1 ORDER BY id LIMIT 1";
        $stmt = $this->pdo->query($sql);
        $result = $stmt->fetch();
        return $result['id'] ?? 1;
    }

    private function getDefaultWarehouseId()
    {
        $sql = "SELECT id FROM sma_warehouses ORDER BY id LIMIT 1";
        $stmt = $this->pdo->query($sql);
        $result = $stmt->fetch();
        return $result['id'] ?? 1;
    }
}