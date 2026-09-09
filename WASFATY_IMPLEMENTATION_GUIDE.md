# Wasfaty Mock Integration - Implementation Guide

## Overview

Mock implementation of Wasfaty (government-backed prescription service) integration in the ERP POS system. Allows pharmacists to retrieve prescriptions via phone number and prescription code, then convert them to orders with automatic batch selection and customer discount application.

## Files Created

### Backend (Controllers & Models)

1. ✅ `/app/controllers/admin/Wasfaty.php` - Main Wasfaty controller
2. ✅ `/app/models/Wasfaty_model.php` - Prescription database operations
3. ✅ `/app/models/Batch_model.php` - Batch management with FEFO logic

### Frontend (Views & Assets)

4. ✅ `/themes/default/admin/views/pos/add.php` - Modified (added Wasfaty button & modal)
5. ✅ `/assets/js/wasfaty.js` - Wasfaty JavaScript module
6. ✅ `/assets/css/wasfaty.css` - Wasfaty styling

### Database & Helpers

7. ✅ `/wasfaty_migration.sql` - Database schema & test data
8. ✅ `/test_wasfaty_products.php` - Helper to find product IDs

## Installation Steps

### Step 1: Update Test Data Product IDs

**IMPORTANT**: Before running the migration, you need to update the product IDs in the test data.

1. **Access the helper script** in your browser:

   ```
   http://yoursite.com/test_wasfaty_products.php
   ```

2. **Choose 2 products** from the results (preferably medicine/pharmacy products)

3. **Open `wasfaty_migration.sql`** and find these sections:

   ```sql
   -- Line ~70-80: Update medicine_id values
   INSERT INTO `wasfaty_prescription_items`
   (`prescription_id`, `medicine_id`, `medicine_name`, ...)
   SELECT @prescription_id, 1, 'EXYLIN 100ML SYRUP', ...  -- Change '1' to actual product ID

   SELECT @prescription_id, 2, 'Panadol Cold Flu...', ...  -- Change '2' to actual product ID
   ```

4. **Update batch data** (lines ~90-130):

   ```sql
   INSERT INTO `inventory_batches`
   (`medicine_id`, `batch_number`, ...)
   SELECT 1, 'BATCH-EXY-001', ...  -- Change '1' to match first product

   SELECT 2, 'BATCH-PAN-001', ...  -- Change '2' to match second product
   ```

5. **Update product names** to match your actual products

### Step 2: Run Database Migration

Execute the SQL file in your database:

**Option A: Via phpMyAdmin**

1. Open phpMyAdmin
2. Select your database
3. Go to "Import" tab
4. Choose `wasfaty_migration.sql`
5. Click "Go"

**Option B: Via MySQL Command Line**

```bash
mysql -u your_username -p your_database < wasfaty_migration.sql
```

**Option C: Using the Adminer tool** (if available at `/adminer.php`)

1. Navigate to http://yoursite.com/adminer.php
2. Login to your database
3. Click "SQL command"
4. Paste the contents of `wasfaty_migration.sql`
5. Click "Execute"

### Step 3: Verify Installation

1. **Check tables created**:

   ```sql
   SHOW TABLES LIKE 'wasfaty%';
   SHOW TABLES LIKE 'inventory_batches';
   ```

   Should show:

   - `wasfaty_prescriptions`
   - `wasfaty_prescription_items`
   - `inventory_batches`

2. **Verify test data**:
   ```sql
   SELECT * FROM wasfaty_prescriptions WHERE prescription_code = '190583';
   SELECT * FROM wasfaty_prescription_items WHERE prescription_id = 1;
   SELECT * FROM inventory_batches WHERE medicine_id IN (1, 2);
   ```

### Step 4: Test the Integration

1. **Access POS**:

   ```
   http://yoursite.com/admin/pos
   ```

2. **Look for the blue "Wasfaty" button** in the top navigation bar

3. **Click the Wasfaty button** to open the prescription lookup modal

4. **Enter test credentials**:

   - Phone: `0554712260`
   - Prescription Code: `190583`

5. **Click "Fetch Prescription"**

   - Should show prescription details with 2 medications
   - Customer type should show "GOLD"
   - Total quantities should be calculated (Qty × Days)

6. **Click "Add to Cart"**

   - Items should be added to POS cart
   - 15% discount should be automatically applied (GOLD customer)
   - Customer type badge should appear

7. **Complete the sale** as normal in POS

## Features

### ✅ Implemented Features

1. **Prescription Lookup**

   - Saudi phone number validation (05XXXXXXXX)
   - 6-digit prescription code validation
   - 1-second simulated API delay
   - Stock availability check

2. **Customer Type & Discounts**

   - REGULAR: 0% discount
   - SILVER: 5% discount
   - GOLD: 15% discount
   - PLATINUM: 20% discount

3. **Batch Selection (FEFO)**

   - Automatic selection of batch with earliest expiry date
   - Ensures non-expired batches only
   - Sufficient quantity validation

4. **POS Integration**

   - Seamless cart integration
   - Automatic discount application
   - Customer type badge display
   - Prescription code tracking

5. **Data Tracking**
   - Prescription status updates (PENDING → DISPENSED)
   - Order linkage to prescription
   - Audit trail (fetched_at timestamp)

## API Endpoints

### 1. Fetch Prescription

```
POST /admin/wasfaty/fetch_prescription
```

**Request:**

```json
{
	"phone": "0554712260",
	"prescription_code": "190583"
}
```

**Response (Success):**

```json
{
	"success": true,
	"prescription": {
		"id": 1,
		"prescription_code": "190583",
		"patient_phone": "0554712260",
		"customer_type": "GOLD",
		"status": "PENDING"
	},
	"items": [
		{
			"id": 1,
			"medicine_id": 1,
			"medicine_name": "EXYLIN 100ML SYRUP",
			"quantity": 3,
			"dosage": "5ml twice daily",
			"duration_days": 5,
			"total_quantity": 15
		}
	],
	"customer_type": "GOLD"
}
```

**Response (Error):**

```json
{
	"success": false,
	"message": "Prescription not found or already dispensed"
}
```

### 2. Convert to Order

```
POST /admin/wasfaty/convert_to_order
```

**Request:**

```json
{
	"prescription_code": "190583"
}
```

**Response:**

```json
{
	"success": true,
	"cart_items": [
		{
			"medicine_id": 1,
			"medicine_name": "EXYLIN 100ML SYRUP",
			"quantity": 15,
			"batch_id": 1,
			"batch_number": "BATCH-EXY-001",
			"price": 25.5,
			"expiry_date": "2026-03-15"
		}
	],
	"customer_type": "GOLD",
	"discount_percentage": 15,
	"prescription_id": 1,
	"prescription_code": "190583"
}
```

## Database Schema

### wasfaty_prescriptions

```sql
id                  INT PRIMARY KEY AUTO_INCREMENT
prescription_code   VARCHAR(20) UNIQUE
patient_phone       VARCHAR(15)
customer_type       ENUM('REGULAR','SILVER','GOLD','PLATINUM')
fetched_at          DATETIME
order_id            INT (FK to orders)
status              ENUM('PENDING','DISPENSED','CANCELLED')
created_at          TIMESTAMP
```

### wasfaty_prescription_items

```sql
id                  INT PRIMARY KEY AUTO_INCREMENT
prescription_id     INT (FK to wasfaty_prescriptions)
medicine_id         INT
medicine_name       VARCHAR(255)
quantity            INT
dosage              VARCHAR(100)
duration_days       INT
total_quantity      INT GENERATED (quantity * duration_days)
```

### inventory_batches

```sql
id                  INT PRIMARY KEY AUTO_INCREMENT
medicine_id         INT
batch_number        VARCHAR(50)
quantity            DECIMAL(10,2)
expiry_date         DATE
cost_price          DECIMAL(10,2)
selling_price       DECIMAL(10,2)
created_at          TIMESTAMP
updated_at          TIMESTAMP
```

## Testing Checklist

- [ ] Database tables created successfully
- [ ] Test prescription data inserted
- [ ] Wasfaty button appears in POS header
- [ ] Modal opens when button is clicked
- [ ] Phone number validation works (10 digits, starts with 05)
- [ ] Prescription code validation works (6 digits)
- [ ] Fetch prescription shows loading state (1 second)
- [ ] Prescription details display correctly
- [ ] Customer type badge shows "GOLD"
- [ ] Both medications appear in table
- [ ] Total quantities calculated correctly (3 × 5 = 15)
- [ ] "Add to Cart" button appears
- [ ] Items added to POS cart successfully
- [ ] 15% discount applied automatically
- [ ] Customer type badge appears in POS
- [ ] Sale can be completed normally
- [ ] Prescription status updates to "DISPENSED"
- [ ] Order_id linked to prescription

## Adding More Test Prescriptions

```sql
-- Add new prescription
INSERT INTO `wasfaty_prescriptions`
(`prescription_code`, `patient_phone`, `customer_type`, `status`)
VALUES
('123456', '0501234567', 'SILVER', 'PENDING');

SET @new_prescription_id = LAST_INSERT_ID();

-- Add prescription items
INSERT INTO `wasfaty_prescription_items`
(`prescription_id`, `medicine_id`, `medicine_name`, `quantity`, `dosage`, `duration_days`)
VALUES
(@new_prescription_id, 3, 'Product Name', 2, '1 tablet twice daily', 7);

-- Add batches for the products
INSERT INTO `inventory_batches`
(`medicine_id`, `batch_number`, `quantity`, `expiry_date`, `selling_price`)
VALUES
(3, 'BATCH-XXX-001', 100.00, '2026-12-31', 15.00);
```

## Troubleshooting

### Issue: Wasfaty button not showing

**Solution:** Clear browser cache and refresh POS page

### Issue: Modal not opening

**Solution:** Check browser console for JavaScript errors. Ensure jQuery is loaded.

### Issue: Prescription not found

**Solution:**

1. Verify data exists: `SELECT * FROM wasfaty_prescriptions WHERE prescription_code = '190583'`
2. Check status is 'PENDING'
3. Verify phone number and code match exactly

### Issue: Items not adding to cart

**Solution:**

1. Check browser console for errors
2. Verify batch data exists with sufficient quantity
3. Check product IDs match actual products in database

### Issue: Discount not applying

**Solution:** Check customer type is set correctly and discount calculation logic

## Future Enhancements

### Phase 2 (Not Implemented Yet)

- [ ] Real API integration (replace mock data)
- [ ] Insurance verification
- [ ] Prescription history view
- [ ] Refill management
- [ ] Multi-language support
- [ ] SMS notifications
- [ ] Prescription image upload
- [ ] Digital signature capture

## Support

For issues or questions:

1. Check this README first
2. Review browser console for JavaScript errors
3. Check server logs for PHP errors
4. Verify database schema matches documentation

## Credits

- **Version:** 1.0
- **Created:** November 5, 2025
- **Framework:** CodeIgniter 3
- **Integration Type:** Mock (for testing/demo)

---

**Note:** This is a mock implementation for testing purposes. For production use, replace the mock data and endpoints with actual Wasfaty API integration.
