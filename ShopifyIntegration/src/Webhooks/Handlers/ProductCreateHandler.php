<?php

namespace App\Webhooks\Handlers;

use PDO;
use Exception;

class ProductCreateHandler
{
    private $pdo;
    private $defaultCategoryId = 14; // Default category
    private $defaultBrandId = 3; // Default brand
    private $defaultUnit = 6; // Default unit

    public function __construct()
    {
        // Initialize database connection
        $this->initDatabase();
    }

    /**
     * Initialize database connection
     */
    private function initDatabase()
    {
        $db_config = [
            'host' => $_ENV['DB_HOST'] ?? getenv('DB_HOST') ?? 'localhost',
            'database' => $_ENV['DB_NAME'] ?? getenv('DB_NAME') ?? 'avnzor',
            'username' => $_ENV['DB_USER'] ?? getenv('DB_USER') ?? 'root',
            'password' => $_ENV['DB_PASS'] ?? getenv('DB_PASS') ?? ''
        ];

        try {
            $this->pdo = new PDO(
                "mysql:host={$db_config['host']};dbname={$db_config['database']};charset=utf8mb4",
                $db_config['username'],
                $db_config['password'],
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                ]
            );
        } catch (\PDOException $e) {
            throw new Exception("Database connection failed: " . $e->getMessage());
        }
    }

    /**
     * Handle products/create webhook
     *
     * @param array $data Shopify product data
     * @return array Result of operation
     */
    public function handle($data)
    {
        error_log("Processing product webhook: " . json_encode(['id' => $data['id'], 'title' => $data['title']]));

        try {
            // Start transaction
            $this->pdo->beginTransaction();

            // Extract product data
            $shopifyProductId = $data['id'];
            $title = $data['title'] ?? 'Untitled Product';
            $bodyHtml = $data['body_html'] ?? '';
            $productType = $data['product_type'] ?? '';
            $vendor = $data['vendor'] ?? '';
            $tags = $data['tags'] ?? '';
            $status = $data['status'] ?? 'active';

            // Check if product already exists
            $existingProduct = $this->getProductByShopifyId($shopifyProductId);

            if ($existingProduct) {
                error_log("Product already exists, updating: $shopifyProductId");
                $productId = $this->updateProduct($existingProduct['id'], $data);
            } else {
                error_log("Creating new product: $shopifyProductId");
                $productId = $this->createProduct($data);
            }

            // Commit transaction
            $this->pdo->commit();

            return [
                'status' => 'success',
                'message' => $existingProduct ? 'Product updated' : 'Product created',
                'product_id' => $productId,
                'shopify_id' => $shopifyProductId
            ];

        } catch (Exception $e) {
            // Rollback on error
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            error_log("Error processing product webhook: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get product by Shopify ID
     */
    private function getProductByShopifyId($shopifyId)
    {
        // Check if shopify_id column exists, if not, search by code
        $sql = "SELECT * FROM sma_products WHERE cf1 = ? LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$shopifyId]);
        return $stmt->fetch();
    }

    /**
     * Create new product in database
     */
    private function createProduct($data)
    {
        // Get first variant data
        $variant = $this->getFirstVariant($data);

        // Generate product code
        $code = $variant['sku'] ?? 'SHOP-' . substr($data['id'], -8);

        // Map category
        $categoryId = $this->mapCategory($data['product_type'] ?? '');

        // Map brand
        $brandId = $this->mapBrand($data['vendor'] ?? '');

        // Prepare product data
        $productData = [
            'code' => $code,
            'name' => $data['title'],
            'second_name' => $this->stripHtml($data['body_html'] ?? ''),
            'unit' => $this->defaultUnit,
            'cost' => floatval($variant['price'] ?? 0),
            'price' => floatval($variant['price'] ?? 0),
            'alert_quantity' => 20,
            'image' => $this->getProductImage($data),
            'category_id' => $categoryId,
            'brand' => $brandId,
            'quantity' => floatval($variant['inventory_quantity'] ?? 0),
            'tax_rate' => $variant['taxable'] ? 1 : null,
            'track_quantity' => 1,
            'product_details' => $this->stripHtml($data['body_html'] ?? ''),
            'tax_method' => 1,
            'type' => 'standard',
            'barcode_symbology' => 'code128',
            'cf1' => $data['id'], // Store Shopify ID
            'cf2' => $data['status'] ?? 'active',
            'cf3' => $data['tags'] ?? '',
            'hide' => ($data['status'] ?? 'active') === 'active' ? 0 : 1,
            'slug' => $data['handle'] ?? $this->generateSlug($data['title'])
        ];

        // Insert product
        $sql = "INSERT INTO sma_products (
            code, name, second_name, unit, cost, price, alert_quantity, image,
            category_id, brand, quantity, tax_rate, track_quantity, product_details,
            tax_method, type, barcode_symbology, cf1, cf2, cf3, hide, slug
        ) VALUES (
            :code, :name, :second_name, :unit, :cost, :price, :alert_quantity, :image,
            :category_id, :brand, :quantity, :tax_rate, :track_quantity, :product_details,
            :tax_method, :type, :barcode_symbology, :cf1, :cf2, :cf3, :hide, :slug
        )";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($productData);

        $productId = $this->pdo->lastInsertId();
        error_log("Product created with ID: $productId");

        return $productId;
    }

    /**
     * Update existing product
     */
    private function updateProduct($productId, $data)
    {
        // Get first variant data
        $variant = $this->getFirstVariant($data);

        // Map category
        $categoryId = $this->mapCategory($data['product_type'] ?? '');

        // Map brand
        $brandId = $this->mapBrand($data['vendor'] ?? '');

        // Update product
        $sql = "UPDATE sma_products SET
            name = :name,
            second_name = :second_name,
            price = :price,
            cost = :cost,
            category_id = :category_id,
            brand = :brand,
            quantity = :quantity,
            product_details = :product_details,
            cf2 = :cf2,
            cf3 = :cf3,
            hide = :hide,
            slug = :slug
        WHERE id = :id";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'name' => $data['title'],
            'second_name' => $this->stripHtml($data['body_html'] ?? ''),
            'price' => floatval($variant['price'] ?? 0),
            'cost' => floatval($variant['price'] ?? 0),
            'category_id' => $categoryId,
            'brand' => $brandId,
            'quantity' => floatval($variant['inventory_quantity'] ?? 0),
            'product_details' => $this->stripHtml($data['body_html'] ?? ''),
            'cf2' => $data['status'] ?? 'active',
            'cf3' => $data['tags'] ?? '',
            'hide' => ($data['status'] ?? 'active') === 'active' ? 0 : 1,
            'slug' => $data['handle'] ?? $this->generateSlug($data['title']),
            'id' => $productId
        ]);

        error_log("Product updated with ID: $productId");

        return $productId;
    }

    /**
     * Get first variant from product data
     */
    private function getFirstVariant($data)
    {
        if (isset($data['variants']) && is_array($data['variants']) && count($data['variants']) > 0) {
            return $data['variants'][0];
        }
        return [];
    }

    /**
     * Map product type to category ID
     */
    private function mapCategory($productType)
    {
        if (empty($productType)) {
            return $this->defaultCategoryId;
        }

        // Try to find matching category
        $sql = "SELECT id FROM sma_categories WHERE name LIKE ? LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(["%$productType%"]);
        $result = $stmt->fetch();

        if ($result) {
            return $result['id'];
        }

        // Create new category if not found
        try {
            $sql = "INSERT INTO sma_categories (code, name, slug) VALUES (?, ?, ?)";
            $stmt = $this->pdo->prepare($sql);
            $code = 'CAT-' . time();
            $slug = $this->generateSlug($productType);
            $stmt->execute([$code, $productType, $slug]);
            return $this->pdo->lastInsertId();
        } catch (Exception $e) {
            error_log("Could not create category, using default: " . $e->getMessage());
            return $this->defaultCategoryId;
        }
    }

    /**
     * Map vendor to brand ID
     */
    private function mapBrand($vendor)
    {
        if (empty($vendor)) {
            return $this->defaultBrandId;
        }

        // Try to find matching brand
        $sql = "SELECT id FROM sma_brands WHERE name LIKE ? LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(["%$vendor%"]);
        $result = $stmt->fetch();

        if ($result) {
            return $result['id'];
        }

        // Create new brand if not found
        try {
            $sql = "INSERT INTO sma_brands (code, name, slug, image) VALUES (?, ?, ?, ?)";
            $stmt = $this->pdo->prepare($sql);
            $code = 'BRD-' . time();
            $slug = $this->generateSlug($vendor);
            $stmt->execute([$code, $vendor, $slug, 'no_image.png']);
            return $this->pdo->lastInsertId();
        } catch (Exception $e) {
            error_log("Could not create brand, using default: " . $e->getMessage());
            return $this->defaultBrandId;
        }
    }

    /**
     * Get product image URL or filename
     */
    private function getProductImage($data)
    {
        if (isset($data['image']['src'])) {
            // TODO: Download image and store locally
            // For now, just use default
            return 'no_image.png';
        }
        return 'no_image.png';
    }

    /**
     * Strip HTML tags and decode entities
     */
    private function stripHtml($html)
    {
        $text = strip_tags($html);
        $text = html_entity_decode($text, ENT_QUOTES, 'UTF-8');
        return substr($text, 0, 1000); // Limit length
    }

    /**
     * Generate slug from text
     */
    private function generateSlug($text)
    {
        $slug = strtolower($text);
        $slug = preg_replace('/[^a-z0-9-]/', '-', $slug);
        $slug = preg_replace('/-+/', '-', $slug);
        $slug = trim($slug, '-');
        return substr($slug, 0, 55);
    }
}

