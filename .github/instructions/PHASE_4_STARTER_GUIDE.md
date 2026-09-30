# PHASE 4: DATABASE MIGRATION & LANGUAGE SETUP - STARTER GUIDE

**Start Date:** October 24, 2025  
**Phase:** 4 of 4 (Final Phase)  
**Estimated Duration:** 1-2 hours  
**Status:** Ready to Execute ✅

---

## 🚀 QUICK START (5 MINUTE READ)

You are about to begin **Phase 4**, the final phase before production deployment. This guide will walk you through:

1. ✅ Execute database migration
2. ✅ Add language keys
3. ✅ Manual testing
4. ✅ Deployment readiness

All code is already complete. This phase is about **activation and validation**.

---

## 📊 CURRENT STATE

### What's Already Done

✅ Organization_setup controller (380+ lines)  
✅ pharmacy_hierarchy view (765 lines)  
✅ Menu integration (both themes)  
✅ AJAX endpoints configured  
✅ Complete documentation

### What's Left

⏳ Database migration execution  
⏳ Language keys addition  
⏳ Manual testing  
⏳ Production deployment

---

## 🔧 STEP 1: DATABASE MIGRATION (5 minutes)

### Step 1A: Verify Current State

```bash
# Check if tables already exist
mysql -u [your_user] -p
USE [your_database];
SHOW TABLES LIKE 'loyalty_%';
DESC sma_warehouses;
```

**Expected Result:**

- If tables exist: SKIP to Step 2
- If tables don't exist: Proceed with Step 1B

### Step 1B: Execute Migration

```bash
# Option 1: Direct command
mysql -u [your_user] -p [your_database] < /Users/rajivepai/Projects/Avenzur/V2/avenzur/db/migrations/20251024_pharmacy_hierarchy_setup.sql

# Option 2: From MySQL console
SOURCE /Users/rajivepai/Projects/Avenzur/V2/avenzur/db/migrations/20251024_pharmacy_hierarchy_setup.sql;
```

### Step 1C: Verify Migration Success

```sql
-- Run these queries to verify:

-- Check loyalty_pharmacies table
SHOW TABLES LIKE 'loyalty_pharmacies';
DESC loyalty_pharmacies;

-- Check loyalty_branches table
SHOW TABLES LIKE 'loyalty_branches';
DESC loyalty_branches;

-- Check parent_id column added
DESC sma_warehouses;
-- Should show 'parent_id' in the output

-- Check data is empty (as expected)
SELECT COUNT(*) FROM loyalty_pharmacies;  -- Should return 0
SELECT COUNT(*) FROM loyalty_branches;    -- Should return 0
```

**✅ Success Criteria:**

- Both tables created
- parent_id column exists in sma_warehouses
- All columns defined correctly
- No data (starting fresh)

---

## 📝 STEP 2: ADD LANGUAGE KEYS (20 minutes)

### Step 2A: Locate Language Files

```bash
# English language files
ls -la /Users/rajivepai/Projects/Avenzur/V2/avenzur/app/language/english/

# Check for these files:
# - lang.php
# - [module_name]_lang.php (may be split by module)
```

### Step 2B: Identify the Right File

```bash
# Search for similar keys to understand structure
grep -r "warehouse" /Users/rajivepai/Projects/Avenzur/V2/avenzur/app/language/english/

# This will show you where warehouse-related keys are defined
# Add pharmacy_hierarchy keys nearby (e.g., same file or system_lang.php)
```

### Step 2C: Add Language Keys

**Find the appropriate language file (typically `system_lang.php` or similar)**

**Add these language keys:**

```php
// === PHARMACY HIERARCHY SETUP KEYS ===

// Main Feature
$lang['pharmacy_hierarchy_setup'] = 'Pharmacy Hierarchy Setup';
$lang['manage_pharmacies'] = 'Manage Pharmacies';
$lang['manage_branches'] = 'Manage Branches';
$lang['organization_hierarchy'] = 'Organization Hierarchy';

// Descriptions
$lang['pharmacy_description'] = 'Create and manage pharmacy locations';
$lang['branch_description'] = 'Create and manage pharmacy branches';
$lang['hierarchy_view_description'] = 'View your organization hierarchy';
$lang['main_warehouse_description'] = 'Each pharmacy automatically creates a main warehouse';

// Buttons & Actions
$lang['add_pharmacy'] = 'Add Pharmacy';
$lang['add_branch'] = 'Add Branch';

// Form Fields
$lang['pharmacy_group'] = 'Pharmacy Group';
$lang['pharmacy_code'] = 'Pharmacy Code';
$lang['pharmacy_name'] = 'Pharmacy Name';
$lang['branch_code'] = 'Branch Code';
$lang['branch_name'] = 'Branch Name';
$lang['main_warehouse'] = 'Main Warehouse';
$lang['warehouse_code'] = 'Warehouse Code';
$lang['warehouse_name'] = 'Warehouse Name';

// Table Headers
$lang['warehouse_type'] = 'Warehouse Type';
$lang['pharmacy'] = 'Pharmacy';

// Placeholders & Help Text
$lang['select_pharmacy_group'] = 'Select Pharmacy Group';
$lang['select_pharmacy'] = 'Select Pharmacy';
$lang['enter_pharmacy_info'] = 'Enter pharmacy information below';
$lang['enter_branch_info'] = 'Enter branch information below';
$lang['unique_code'] = 'Unique code for this warehouse';
$lang['unique_warehouse_code'] = 'Unique warehouse code for inventory tracking';
$lang['select_parent_company'] = 'This pharmacy will belong to the selected group';
$lang['select_parent_pharmacy'] = 'This branch will belong to the selected pharmacy';

// Messages
$lang['click_node_to_view_details'] = 'Click on any node to view details';
$lang['no_hierarchy_data'] = 'No hierarchy data available';
$lang['no_data'] = 'No data found';
$lang['confirm_delete'] = 'Are you sure you want to delete this?';

// Tab Names
$lang['pharmacies'] = 'Pharmacies';
$lang['branches'] = 'Branches';
$lang['hierarchy_view'] = 'Hierarchy View';
```

### Step 2D: Clear Application Cache

```bash
# Clear CodeIgniter cache
rm -rf /Users/rajivepai/Projects/Avenzur/V2/avenzur/app/cache/*

# Or from the application:
# Navigate to admin and the cache will auto-clear
```

**✅ Success Criteria:**

- All keys added without errors
- Can access feature and see labels (not [key_name])
- Cache cleared

---

## ✅ STEP 3: MANUAL TESTING (45 minutes)

### Pre-Test Setup

```bash
# 1. Clear cache (done above)
# 2. Restart web server if needed
# 3. Login as Admin user
# 4. Navigate to Settings → Organization Setup → Pharmacy Hierarchy
```

### Test Case 1: Access Feature

```
Steps:
1. Login to admin panel
2. Click Settings menu
3. Find "Organization Setup" (or "Pharmacy Hierarchy Setup")
4. Click to open

Expected:
✓ Page loads without errors
✓ 3 tabs visible (Pharmacies, Branches, Hierarchy View)
✓ No console JavaScript errors
✓ Page styled correctly
✓ All buttons visible and clickable
```

### Test Case 2: Add Pharmacy (Main Test)

```
Steps:
1. Ensure on Pharmacies tab
2. Click "Add Pharmacy" button
3. Modal opens with form:
   - Pharmacy Group dropdown
   - Pharmacy Code field
   - Pharmacy Name field
   - Address textarea
   - Phone field
   - Email field
   - Warehouse Code field
   - Warehouse Name field
4. Fill all required fields:
   - Select any group from dropdown
   - Code: "PHARM_TEST_001"
   - Name: "Test Pharmacy"
   - Address: "123 Main St"
   - Phone: "555-1234"
   - Email: "test@pharmacy.com"
   - Warehouse Code: "WH_TEST_001"
   - Warehouse Name: "Test Main Warehouse"
5. Click "Add Pharmacy" button

Expected:
✓ Form validates (no errors if filled)
✓ Success notification appears
✓ Modal closes
✓ New pharmacy appears in Pharmacies table
✓ Can see pharmacy code, name, address, phone
✓ Status shows as "pharmacy"

Database Check:
SELECT * FROM loyalty_pharmacies WHERE code = 'PHARM_TEST_001';
SELECT * FROM sma_warehouses WHERE code LIKE 'WH%TEST%';
-- Should show 2 records: pharmacy + mainwarehouse
```

### Test Case 3: Add Branch

```
Steps:
1. Go to Branches tab
2. Select created pharmacy from dropdown
3. Branches list loads (should be empty)
4. Click "Add Branch" button
5. Fill form:
   - Pharmacy: Select the pharmacy created above
   - Branch Code: "BR_TEST_001"
   - Branch Name: "Test Branch"
   - Address: "456 Oak Ave"
   - Phone: "555-5678"
   - Email: "branch@pharmacy.com"
6. Submit form

Expected:
✓ Branch appears in table
✓ Parent pharmacy displayed correctly
✓ Success notification
✓ Branch warehouse created

Database Check:
SELECT * FROM loyalty_branches WHERE code = 'BR_TEST_001';
SELECT * FROM sma_warehouses WHERE parent_id IS NOT NULL;
-- Should show branch with correct parent_id
```

### Test Case 4: Hierarchy View

```
Steps:
1. Go to Hierarchy View tab
2. Page renders

Expected:
✓ Tree structure displays
✓ Shows Pharmacy Group (if created)
✓ Shows Pharmacy (created above)
✓ Shows Branch nested under Pharmacy
✓ Proper indentation/visual hierarchy
```

### Test Case 5: Delete Operations

```
Steps (Delete Branch):
1. Go to Branches tab
2. Click delete icon on test branch row
3. Confirm deletion dialog
4. Click yes/confirm

Expected:
✓ Branch removed from table
✓ Success notification
✓ Database: branch warehouse deleted
  SELECT * FROM sma_warehouses WHERE code = 'BR_TEST_001';
  -- Should return 0 rows

Steps (Delete Pharmacy):
1. Go to Pharmacies tab
2. Click delete icon on test pharmacy row
3. Confirm deletion
4. Click yes/confirm

Expected:
✓ Pharmacy removed from table
✓ Success notification
✓ Database: pharmacy AND mainwarehouse deleted
  SELECT * FROM loyalty_pharmacies WHERE code = 'PHARM_TEST_001';
  SELECT * FROM sma_warehouses WHERE code = 'WH_TEST_001';
  -- Both should return 0 rows (cascade delete)
```

### Test Case 6: Form Validation

```
Steps (Test Required Fields):
1. Click Add Pharmacy
2. Leave all fields empty
3. Click Submit

Expected:
✓ Error message appears below required fields
✓ Form doesn't submit
✓ Submit button disabled or shows error state

Steps (Test Duplicate Code):
1. Create pharmacy with code "PHARM_DUP"
2. Try to create another with same code

Expected:
✓ Server validation error: "Code already exists"
✓ Form doesn't submit
✓ Error message displayed
```

### Test Case 7: Mobile Responsiveness

```
Steps:
1. Open developer tools (F12)
2. Toggle device toolbar (Ctrl+Shift+M)
3. Test at different sizes:
   - Mobile (375px): iPhone
   - Tablet (768px): iPad
   - Desktop (1200px): Normal

Expected:
✓ Mobile (375px):
  - Single column layout
  - Tables scroll horizontally
  - Buttons are large (48px+)
  - Text is readable

✓ Tablet (768px):
  - 2-column where possible
  - Tables readable
  - Forms stack nicely

✓ Desktop (1200px):
  - Full layout displays
  - All elements visible
  - No horizontal scroll needed
```

### Test Case 8: No Console Errors

```
Steps:
1. Open Developer Tools (F12)
2. Go to Console tab
3. Perform all operations above
4. Check for red error messages

Expected:
✓ No JavaScript errors
✓ No AJAX errors
✓ No console warnings related to feature
✓ All AJAX calls show 200/success status
```

### Summary: Test Results Template

```
Test Case 1: Access Feature ................... [ ] PASS [ ] FAIL
Test Case 2: Add Pharmacy ..................... [ ] PASS [ ] FAIL
Test Case 3: Add Branch ....................... [ ] PASS [ ] FAIL
Test Case 4: Hierarchy View ................... [ ] PASS [ ] FAIL
Test Case 5: Delete Operations ................ [ ] PASS [ ] FAIL
Test Case 6: Form Validation .................. [ ] PASS [ ] FAIL
Test Case 7: Mobile Responsiveness ............ [ ] PASS [ ] FAIL
Test Case 8: No Console Errors ................ [ ] PASS [ ] FAIL

Overall Result: [ ] ALL PASS ✅ [ ] SOME FAILED ❌
```

---

## 🐛 IF ISSUES OCCUR

### Issue: "Settings" menu doesn't show "Organization Setup"

**Solution:**

1. Check if you're logged in as Admin (Owner)
2. Verify header.php was modified correctly
3. Check: `/themes/blue/admin/views/header.php` line ~1415
4. Clear cache: `rm -rf app/cache/*`

### Issue: Language keys show as [pharmacy_hierarchy_setup]

**Solution:**

1. Verify language keys added to correct file
2. Check file saved without errors
3. Clear cache: `rm -rf app/cache/*`
4. Refresh browser (Ctrl+F5)

### Issue: AJAX calls failing (404 errors)

**Solution:**

1. Check Organization_setup controller exists
2. Path: `/app/controllers/admin/Organization_setup.php`
3. Check file permissions: `ls -la Organization_setup.php`
4. Verify no PHP syntax errors: `php -l Organization_setup.php`

### Issue: Database tables not created

**Solution:**

1. Check migration SQL file
2. Verify MySQL user has CREATE TABLE permission
3. Run migration again with output:
   ```bash
   mysql -u [user] -p [database] < migration.sql 2>&1 | tee migration_output.txt
   ```
4. Check migration_output.txt for errors

### Issue: Warehouse not created automatically

**Solution:**

1. Check Organization_setup.php controller
2. Verify `_create_warehouse()` method exists
3. Check database logs for transaction errors
4. Verify sma_warehouses table exists and is accessible

---

## ✅ DEPLOYMENT READINESS CHECKLIST

Before deploying to production:

- [ ] Database migration executed successfully
- [ ] All 3 tables verified to exist
- [ ] Language keys added to language files
- [ ] Cache cleared
- [ ] All 8 test cases PASSED
- [ ] No JavaScript console errors
- [ ] No PHP error logs
- [ ] Mobile responsive design verified
- [ ] Feature accessible from Settings menu
- [ ] CRUD operations working correctly
- [ ] Data persisting to database

**Once all items checked: READY FOR PRODUCTION ✅**

---

## 🚀 NEXT: PRODUCTION DEPLOYMENT

After all tests pass, proceed with:

1. Merge code to main branch
2. Push to production
3. Run migration on production DB
4. Add language keys to production language files
5. Verify feature works in production
6. Monitor logs for errors

---

## 📞 QUICK REFERENCE

**Docker is running?** (from context)
✅ Yes - `docker compose up -d` executed successfully

**Current working directory:**
`/Users/rajivepai/Projects/Avenzur/V2/avenzur`

**Key file paths:**

- Controller: `app/controllers/admin/Organization_setup.php`
- View: `themes/blue/admin/views/settings/pharmacy_hierarchy.php`
- Migration: `db/migrations/20251024_pharmacy_hierarchy_setup.sql`
- Language: `app/language/english/` (or relevant language)

---

## ⏱️ TIME ESTIMATE

| Task               | Time       | Status   |
| ------------------ | ---------- | -------- |
| Database Migration | 5 min      | ⏳ Ready |
| Language Keys      | 20 min     | ⏳ Ready |
| Manual Testing     | 45 min     | ⏳ Ready |
| **TOTAL**          | **70 min** | ⏳ Ready |

---

## 🎯 SUCCESS CRITERIA FOR PHASE 4

Feature is **production-ready** when:

✅ Database migration executed without errors  
✅ All 3 tables verified to exist  
✅ Language keys displaying correctly (not [key])  
✅ All 8 test cases passing  
✅ No JavaScript console errors  
✅ No PHP error logs  
✅ Feature accessible from Settings menu  
✅ CRUD operations working correctly  
✅ Mobile responsive design working  
✅ Data persisting correctly to database

**Current Status: 0/10 Ready (Awaiting execution)**

---

## 🎬 LET'S BEGIN!

**Next Action:** Execute Step 1 - Database Migration

Ready? Let's go! 🚀

---

**Phase 4 Starter Guide**  
Generated: October 24, 2025  
Status: Ready for Execution ✅
