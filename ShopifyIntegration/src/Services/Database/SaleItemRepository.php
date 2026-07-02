<?php

namespace App\Services\Database;

use PDO;
use App\Services\Database\ProductRepository;

class SaleItemRepository
{
    private $pdo;
    private $productRepo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
        $this->productRepo = new ProductRepository($pdo);
    }

    /**
     * Save sale item to sma_sale_items table
     */
    public function saveSaleItem($item, $saleId)
    {
        $productId = $this->productRepo->getProductIdBySku($item['sku'] ?? null);
        
        if (!$productId) {
            $productId = 1; // Default product ID
        }

        $warehouseId = $this->getDefaultWarehouseId();

        $sql = "INSERT INTO sma_sale_items (
                    sale_id,
                    product_id,
                    product_code,
                    product_name,
                    product_type,
                    net_cost,
                    net_unit_price,
                    unit_price,
                    quantity,
                    warehouse_id,
                    item_tax,
                    tax,
                    discount,
                    item_discount,
                    subtotal,
                    serial_number,
                    unit_quantity,
                    subtotal2,
                    bonus,
                    discount1,
                    discount2,
                    totalbeforevat,
                    main_net,
                    avz_item_code
                ) VALUES (
                    ?, ?, ?, ?, 'standard', ?, ?, ?, ?, ?, 0.00000, 0.00000, '', 
                    ?, ?, '', ?, 0.00000, 0.00, 0.00000, 0.00000, 0.00000, 0.00000, ?
                )";

        $unitPrice = floatval($item['price'] ?? 0);
        $quantity = floatval($item['quantity'] ?? 1);
        $discount = floatval($item['total_discount'] ?? 0);
        $subtotal = ($unitPrice * $quantity) - $discount;

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            $saleId,
            $productId,
            $item['sku'] ?? '',
            $item['title'] ?? $item['name'] ?? 'Unknown Product',
            $unitPrice,
            $unitPrice,
            $unitPrice,
            $quantity,
            $warehouseId,
            $discount,
            $subtotal,
            $quantity,
            $item['sku'] ?? ''
        ]);

        return $this->pdo->lastInsertId();
    }

    /**
     * Get sale items by sale ID
     */
    public function getSaleItemsBySaleId($saleId)
    {
        $sql = "SELECT * FROM sma_sale_items WHERE sale_id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$saleId]);
        
        return $stmt->fetchAll();
    }

    /**
     * Delete sale item
     */
    public function deleteSaleItem($itemId)
    {
        $sql = "DELETE FROM sma_sale_items WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$itemId]);
        
        return $stmt->rowCount();
    }

    /**
     * Get default warehouse ID
     */
    private function getDefaultWarehouseId()
    {
        $sql = "SELECT id FROM sma_warehouses LIMIT 1";
        $stmt = $this->pdo->query($sql);
        $result = $stmt->fetch();

        return $result ? $result['id'] : 1;
    }
}