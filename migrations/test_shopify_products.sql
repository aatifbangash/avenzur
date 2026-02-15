-- Quick Start Test Data for Shopify Product System
-- Run this AFTER the migration to test the system

-- Create test collection
INSERT INTO `sma_product_collections` (`name`, `description`, `slug`, `status`) VALUES
('Featured Medicines', 'Top selling pharmaceutical products', 'featured-medicines', 'active'),
('Over The Counter', 'OTC products available without prescription', 'otc-products', 'active');

-- Sample Test Product 1: Basic product (no variants)
INSERT INTO `sma_products` (
    `code`, `name`, `name_ar`, `details`, `product_details`,
    `vendor`, `product_type`, `tags`, `handle`, `slug`,
    `category_id`, `brand`, `price`, `cost`, `compare_at_price`,
    `type`, `barcode_symbology`, `unit`, `tax_rate`, `tax_method`,
    `taxable`, `track_quantity`, `quantity`, `weight`, `requires_shipping`,
    `status`, `published_at`, `seo_title`, `seo_description`,
    `featured`, `hide`
) VALUES (
    'TEST-PARA-500',
    'Paracetamol 500mg Tablets',
    'باراسيتامول 500 ملغ',
    'Pain relief and fever reducer tablets',
    '<p>Paracetamol is used for temporary relief of pain and fever.</p><ul><li>Fast acting</li><li>Long lasting</li><li>Gentle on stomach</li></ul>',
    'Generic Pharma Co.',
    'Analgesics',
    'paracetamol, pain relief, fever, otc',
    'paracetamol-500mg-tablets',
    'paracetamol-500mg-tablets',
    1, -- Adjust category_id as needed
    1, -- Adjust brand as needed
    15.00, -- price
    8.00, -- cost
    20.00, -- compare_at_price (showing discount)
    'standard',
    'code128',
    1,
    1, -- tax rate
    0, -- inclusive
    1, -- taxable
    1, -- track quantity
    0, -- initial quantity
    0.05, -- weight (50g)
    1, -- requires shipping
    'active',
    NOW(),
    'Buy Paracetamol 500mg Tablets Online - Pain Relief',
    'Get fast pain relief with Paracetamol 500mg tablets. Available for sale online. Fast delivery.',
    1, -- featured
    0 -- not hidden
);

-- Get the product ID for variants
SET @product_id = LAST_INSERT_ID();

-- Create default variant for Test Product 1
INSERT INTO `sma_product_variants` (
    `product_id`, `name`, `sku`, `barcode`,
    `option1`, `option2`, `option3`,
    `price`, `compare_at_price`, `cost`,
    `weight`, `weight_unit`, `quantity`,
    `position`, `taxable`, `requires_shipping`,
    `inventory_management`, `inventory_policy`
) VALUES (
    @product_id,
    'Default',
    'TEST-PARA-500',
    '1234567890123',
    NULL, NULL, NULL,
    15.00,
    20.00,
    8.00,
    0.05,
    'kg',
    0,
    1,
    1,
    1,
    1,
    'deny'
);

-- Sample Test Product 2: Product with variants (pack sizes)
INSERT INTO `sma_products` (
    `code`, `name`, `name_ar`, `details`,
    `vendor`, `product_type`, `tags`, `handle`, `slug`,
    `category_id`, `brand`, `price`, `cost`,
    `type`, `barcode_symbology`, `unit`, `tax_rate`, `tax_method`,
    `taxable`, `track_quantity`, `quantity`, `weight`, `requires_shipping`,
    `status`, `published_at`,
    `options_json`,
    `featured`, `hide`
) VALUES (
    'TEST-VITC-PACK',
    'Vitamin C 1000mg',
    'فيتامين سي 1000 ملغ',
    'High strength vitamin C supplement tablets',
    'HealthPlus',
    'Vitamins & Supplements',
    'vitamin c, immunity, supplements',
    'vitamin-c-1000mg',
    'vitamin-c-1000mg',
    1,
    1,
    25.00, -- base price
    12.00, -- base cost
    'standard',
    'code128',
    1,
    1,
    0,
    1,
    1,
    0,
    0.08,
    1,
    'active',
    NOW(),
    '[{"name":"Pack Size","position":1}]', -- options as JSON
    0,
    0
);

-- Get product ID for variants
SET @product_id2 = LAST_INSERT_ID();

-- Create variants for different pack sizes
INSERT INTO `sma_product_variants` (
    `product_id`, `name`, `sku`, `barcode`,
    `option1`, `option2`, `option3`,
    `price`, `cost`, `weight`, `weight_unit`, `quantity`,
    `position`, `taxable`, `requires_shipping`,
    `inventory_management`, `inventory_policy`
) VALUES
-- Pack of 30
(
    @product_id2,
    '30 Tablets',
    'TEST-VITC-30',
    '2234567890123',
    '30 Tablets', NULL, NULL,
    25.00,
    12.00,
    0.05,
    'kg',
    0,
    1,
    1,
    1,
    1,
    'deny'
),
-- Pack of 60
(
    @product_id2,
    '60 Tablets',
    'TEST-VITC-60',
    '2234567890124',
    '60 Tablets', NULL, NULL,
    45.00,
    20.00,
    0.08,
    'kg',
    0,
    2,
    1,
    1,
    1,
    'deny'
),
-- Pack of 100
(
    @product_id2,
    '100 Tablets',
    'TEST-VITC-100',
    '2234567890125',
    '100 Tablets', NULL, NULL,
    70.00,
    30.00,
    0.12,
    'kg',
    0,
    3,
    1,
    1,
    1,
    'deny'
);

-- Add products to featured collection
INSERT INTO `sma_product_collection_items` (`collection_id`, `product_id`, `position`)
SELECT 1, @product_id, 1
UNION ALL
SELECT 1, @product_id2, 2;

-- Add to OTC collection
INSERT INTO `sma_product_collection_items` (`collection_id`, `product_id`, `position`)
SELECT 2, @product_id, 1;

-- Note: You'll need to manually create warehouse entries since we don't know your warehouse IDs
-- Example for warehouse_id = 1:
-- 
-- INSERT INTO `sma_warehouses_products` (`product_id`, `warehouse_id`, `quantity`, `avg_cost`)
-- VALUES 
--     (@product_id, 1, 0, 8.00),
--     (@product_id2, 1, 0, 12.00);
-- 
-- INSERT INTO `sma_warehouses_products_variants` (`option_id`, `product_id`, `warehouse_id`, `quantity`)
-- SELECT v.id, v.product_id, 1, 0
-- FROM sma_product_variants v
-- WHERE v.product_id IN (@product_id, @product_id2);

SELECT 'Test data created successfully!' AS message;
SELECT CONCAT('Product 1 ID: ', @product_id) AS product1;
SELECT CONCAT('Product 2 ID: ', @product_id2) AS product2;
