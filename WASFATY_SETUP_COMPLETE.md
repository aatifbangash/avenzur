-- ============================================
-- WASFATY INTEGRATION - SETUP COMPLETE
-- Date: November 5, 2025
-- Database: demo
-- ============================================

-- SETUP SUMMARY:
-- ✅ Created sma_wasfaty_prescriptions table
-- ✅ Created sma_wasfaty_prescription_items table
-- ✅ Added source, prescription_code, customer_type columns to sma_sales
-- ✅ Populated sma_dim_pharmacy (12 pharmacies)
-- ✅ Created test prescription data

-- ============================================
-- TEST DATA READY
-- ============================================
-- Prescription Code: 190583
-- Patient Phone: 0554712269
-- Customer Type: GOLD (15% discount)
-- Status: PENDING
-- Items:
-- 1. Alcohol Swab 200p Box (Product ID: 2)
-- - Quantity: 3 per day
-- - Duration: 5 days
-- - Total: 15 units
-- - Available Stock: 59 units
-- - Price: 10.00 SAR
--
-- 2. S.S Pure Cod Liver Oil 120 Cap (Product ID: 1)
-- - Quantity: 2 per day
-- - Duration: 3 days
-- - Total: 6 units
-- - Available Stock: 14 units
-- - Price: 115.40 SAR

-- ============================================
-- DIMENSION TABLES POPULATED
-- ============================================
-- sma_dim_pharmacy: 12 records
-- - All pharmacies from sma_warehouses (parent_id IS NULL)
--
-- sma_dim_branch: 0 records
-- - No branches in current setup (all warehouses are top-level)

-- ============================================
-- NEXT STEPS FOR TESTING
-- ============================================

-- 1. Open POS page in browser
-- 2. Hard refresh (Cmd+Shift+R) to load wasfaty.js v3.0
-- 3. Open browser console to see debug logs
-- 4. Click "Wasfaty" button in POS
-- 5. Enter:
-- Phone: 0554712269
-- Code: 190583
-- 6. Click "Fetch Prescription"
-- 7. Verify prescription displays in right panel:
-- - Patient phone
-- - Prescription code
-- - 2 items shown with dosage and quantities
-- 8. Click "Add to Cart"
-- 9. Watch console logs:
-- - "Adding item 1: Alcohol Swab 200p Box..."
-- - "Calling add_invoice_item..."
-- - "Adding item 2: S.S Pure Cod Liver Oil 120 Cap..."
-- - "Calling add_invoice_item..."
-- - "All items added successfully"
-- 10. Verify in POS cart:
-- - Item 1: Alcohol Swab (15 units)
-- - Item 2: Cod Liver Oil (6 units)
-- - Batch auto-selected (earliest expiry)
-- - 15% GOLD discount applied
-- - Grand total calculated

-- ============================================
-- FILES MODIFIED/CREATED
-- ============================================

-- Backend (CodeIgniter):
-- /app/controllers/admin/Wasfaty.php - Prescription fetch controller
-- /app/models/Wasfaty_model.php - Prescription data access
-- /app/models/Batch_model.php - FEFO batch selection
-- /app/config/config.php - CSRF bypass for Wasfaty endpoints

-- Frontend (Blue Theme):
-- /themes/blue/admin/views/pos/add.php - Two-column modal, localStorage fixes
-- /assets/js/wasfaty.js - v3.0 (POS integration)
-- /assets/css/wasfaty.css - Blue theme styling

-- Database:
-- /wasfaty_setup_clean.sql - Complete setup script (executed)
-- /etl_populate_dimensions.sql - Dimension table ETL (executed)

-- ============================================
-- CUSTOMER DISCOUNT TYPES
-- ============================================
-- REGULAR: 0% discount
-- SILVER: 5% discount
-- GOLD: 15% discount (test prescription)
-- PLATINUM: 20% discount

-- ============================================
-- VERIFICATION QUERIES
-- ============================================

-- Check Wasfaty tables
SELECT _ FROM sma_wasfaty_prescriptions;
SELECT _ FROM sma_wasfaty_prescription_items;

-- Check sma_sales columns
SHOW COLUMNS FROM sma_sales LIKE '%source%';
SHOW COLUMNS FROM sma_sales LIKE '%prescription%';
SHOW COLUMNS FROM sma_sales LIKE '%customer_type%';

-- Check dimension tables
SELECT COUNT(_) FROM sma_dim_pharmacy;
SELECT COUNT(_) FROM sma_dim_branch;

-- After creating a Wasfaty order, verify:
SELECT
s.id,
s.source,
s.prescription_code,
s.customer_type,
s.grand_total,
s.date
FROM sma_sales s
WHERE s.source = 'WASFATY'
ORDER BY s.id DESC
LIMIT 5;

-- ============================================
-- TROUBLESHOOTING
-- ============================================

-- If prescription not found:
-- Check phone and code match exactly
SELECT \* FROM sma_wasfaty_prescriptions
WHERE prescription_code = '190583'
AND patient_phone = '0554712269';

-- If items not adding to cart:
-- Check console logs in browser (F12)
-- Verify wasfaty.js v3.0 loaded (check cache timestamp)
-- Verify products have stock
SELECT
p.id,
p.name,
(SELECT SUM(quantity) FROM sma_inventory_movements WHERE product_id = p.id) as stock
FROM sma_products p
WHERE p.id IN (1, 2);

-- If batch selection fails:
-- Check inventory movements for products
SELECT
product_id,
batch_number,
quantity,
expiry_date
FROM sma_inventory_movements
WHERE product_id IN (1, 2)
AND quantity > 0
ORDER BY product_id, expiry_date;

-- If discount not applying:
-- Check customer_type in prescription
SELECT customer_type FROM sma_wasfaty_prescriptions WHERE prescription_code = '190583';

-- ============================================
-- INTEGRATION ARCHITECTURE (v3.0)
-- ============================================

-- Flow:
-- 1. User enters phone + code in Wasfaty modal
-- 2. AJAX fetch: /admin/wasfaty/fetch_prescription
-- 3. Backend returns prescription + items
-- 4. Modal displays in right panel (col-md-7)
-- 5. User clicks "Add to Cart"
-- 6. JavaScript (wasfaty.js v3.0):
-- a. Auto-select first pharmacy (#poswarehouse)
-- b. For each item:
-- - AJAX search: /admin/sales/suggestions/1?term=PRODUCT_NAME
-- - Get product data with batch info
-- - Call existing POS function: add_invoice_item(product)
-- - Set quantity to total_quantity (qty × duration)
-- - Wait 500ms before next item
-- c. Apply customer discount (15% for GOLD)
-- d. Show success message
-- 7. POS handles:
-- - Batch selection (FEFO - First Expiry First Out)
-- - Price calculation
-- - Inventory validation
-- - Cart display
-- - Discount application
-- - Invoice generation

-- ============================================
-- SETUP COMPLETE - READY FOR TESTING
-- ============================================
