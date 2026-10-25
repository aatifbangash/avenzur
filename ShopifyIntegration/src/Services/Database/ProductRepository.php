<?php

namespace App\Services\Database;

use PDO;

class ProductRepository
{
    private $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Get product ID by SKU
     */
    public function getProductIdBySku($sku)
    {
        if (!$sku) {
            return null;
        }

        $sql = "SELECT id FROM sma_products WHERE code = ? LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$sku]);
        $result = $stmt->fetch();

        return $result ? $result['id'] : null;
    }

    /**
     * Get product by ID
     */
    public function getProductById($productId)
    {
        $sql = "SELECT * FROM sma_products WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$productId]);
        
        return $stmt->fetch();
    }

    /**
     * Create a product if it doesn't exist
     */
    public function createProduct($productData)
    {
        $sql = "INSERT INTO sma_products (
                    code,
                    name,
                    type,
                    price,
                    cost
                ) VALUES (?, ?, 'standard', ?, ?)";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            $productData['sku'] ?? '',
            $productData['name'] ?? 'Unknown Product',
            $productData['price'] ?? 0,
            $productData['cost'] ?? 0
        ]);

        return $this->pdo->lastInsertId();
    }

    /**
     * Search products by name or SKU
     */
    public function searchProducts($query)
    {
        $sql = "SELECT * FROM sma_products WHERE code LIKE ? OR name LIKE ? LIMIT 10";
        $stmt = $this->pdo->prepare($sql);
        $searchTerm = "%{$query}%";
        $stmt->execute([$searchTerm, $searchTerm]);
        
        return $stmt->fetchAll();
    }
}