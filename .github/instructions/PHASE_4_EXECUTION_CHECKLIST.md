# PHASE 4 EXECUTION CHECKLIST

**Date:** October 24, 2025  
**Phase:** 4 of 4 (Final Phase - Activation & Testing)  
**Status:** Ready to Execute

---

## PRE-EXECUTION VERIFICATION

### ✅ Verify All Phase 3 Deliverables Are In Place

**Step 1: Verify Controller Exists**

```bash
ls -lah /Users/rajivepai/Projects/Avenzur/V2/avenzur/app/controllers/admin/Organization_setup.php
# Should show: -rw-r--r-- (readable file)
```

**Step 2: Verify View File Exists**

```bash
ls -lah /Users/rajivepai/Projects/Avenzur/V2/avenzur/themes/blue/admin/views/settings/pharmacy_hierarchy.php
# Should show: -rw-r--r-- (readable file)
```

**Step 3: Verify Migration File Exists**

```bash
ls -lah /Users/rajivepai/Projects/Avenzur/V2/avenzur/db/migrations/20251024_pharmacy_hierarchy_setup.sql
# Should show: -rw-r--r-- (readable file)
```

**Step 4: Verify Menu Items Added**

```bash
# Blue theme
grep -n "organization_setup_pharmacy_hierarchy" /Users/rajivepai/Projects/Avenzur/V2/avenzur/themes/blue/admin/views/header.php
# Should show: Line containing "organization_setup_pharmacy_hierarchy"

# Default theme
grep -n "organization_setup_pharmacy_hierarchy" /Users/rajivepai/Projects/Avenzur/V2/avenzur/themes/default/admin/views/header.php
# Should show: Line containing "organization_setup_pharmacy_hierarchy"
```

**Step 5: Verify Docker Is Running**

```bash
docker ps
# Should show running containers from docker-compose
```

---

## EXECUTION WORKFLOW

### ✅ CHECKPOINT 1: DATABASE MIGRATION

- [ ] Backup database (optional but recommended)
- [ ] Execute migration script
- [ ] Verify all 3 tables created
- [ ] Verify parent_id column added
- [ ] Verify indexes created
- [ ] Check for any SQL errors

### ✅ CHECKPOINT 2: LANGUAGE SETUP

- [ ] Locate correct language file
- [ ] Add ~30 language keys
- [ ] Verify syntax (no PHP errors)
- [ ] Save file
- [ ] Clear application cache

### ✅ CHECKPOINT 3: MANUAL TESTING

- [ ] Access feature from menu
- [ ] Test Case 1: Feature Access
- [ ] Test Case 2: Add Pharmacy
- [ ] Test Case 3: Add Branch
- [ ] Test Case 4: Hierarchy View
- [ ] Test Case 5: Delete Operations
- [ ] Test Case 6: Form Validation
- [ ] Test Case 7: Mobile Responsive
- [ ] Test Case 8: No Console Errors

### ✅ CHECKPOINT 4: PRODUCTION READINESS

- [ ] All tests passed
- [ ] No errors in logs
- [ ] Feature fully functional
- [ ] Data persisting correctly
- [ ] Menu items visible
- [ ] Language keys displaying

---

## TASK BREAKDOWN

### TASK 1: Execute Database Migration (5 minutes)

**Substeps:**

1. [ ] Connect to database
2. [ ] Run migration SQL
3. [ ] Verify loyalty_pharmacies table
4. [ ] Verify loyalty_branches table
5. [ ] Verify sma_warehouses.parent_id column
6. [ ] Verify indexes created
7. [ ] Check data is empty (fresh start)

**Commands to Use:**

```bash
# From terminal
mysql -u [USER] -p [DATABASE] < /Users/rajivepai/Projects/Avenzur/V2/avenzur/db/migrations/20251024_pharmacy_hierarchy_setup.sql

# Verify (from MySQL console)
mysql -u [USER] -p [DATABASE]
SHOW TABLES LIKE 'loyalty_%';
DESC loyalty_pharmacies;
DESC loyalty_branches;
DESC sma_warehouses;
```

**Estimated Time:** 5 minutes

---

### TASK 2: Add Language Keys (20 minutes)

**Substeps:**

1. [ ] Identify language file location
2. [ ] Search for similar keys (e.g., "warehouse")
3. [ ] Add ~30 pharmacy hierarchy keys
4. [ ] Save file without errors
5. [ ] Clear application cache
6. [ ] Verify keys display correctly

**Keys to Add (~30 total):**

```php
// Copy this block to your language file

// === PHARMACY HIERARCHY SETUP ===
$lang['pharmacy_hierarchy_setup'] = 'Pharmacy Hierarchy Setup';
$lang['manage_pharmacies'] = 'Manage Pharmacies';
$lang['manage_branches'] = 'Manage Branches';
$lang['organization_hierarchy'] = 'Organization Hierarchy';
$lang['pharmacy_description'] = 'Create and manage pharmacy locations';
$lang['branch_description'] = 'Create and manage pharmacy branches';
$lang['hierarchy_view_description'] = 'View your organization hierarchy';
$lang['main_warehouse_description'] = 'Each pharmacy automatically creates a main warehouse';
$lang['add_pharmacy'] = 'Add Pharmacy';
$lang['add_branch'] = 'Add Branch';
$lang['pharmacy_group'] = 'Pharmacy Group';
$lang['pharmacy_code'] = 'Pharmacy Code';
$lang['pharmacy_name'] = 'Pharmacy Name';
$lang['branch_code'] = 'Branch Code';
$lang['branch_name'] = 'Branch Name';
$lang['main_warehouse'] = 'Main Warehouse';
$lang['warehouse_code'] = 'Warehouse Code';
$lang['warehouse_name'] = 'Warehouse Name';
$lang['warehouse_type'] = 'Warehouse Type';
$lang['pharmacy'] = 'Pharmacy';
$lang['select_pharmacy_group'] = 'Select Pharmacy Group';
$lang['select_pharmacy'] = 'Select Pharmacy';
$lang['enter_pharmacy_info'] = 'Enter pharmacy information below';
$lang['enter_branch_info'] = 'Enter branch information below';
$lang['unique_code'] = 'Unique code for this warehouse';
$lang['unique_warehouse_code'] = 'Unique warehouse code for inventory tracking';
$lang['select_parent_company'] = 'This pharmacy will belong to the selected group';
$lang['select_parent_pharmacy'] = 'This branch will belong to the selected pharmacy';
$lang['click_node_to_view_details'] = 'Click on any node to view details';
$lang['no_hierarchy_data'] = 'No hierarchy data available';
$lang['pharmacies'] = 'Pharmacies';
$lang['branches'] = 'Branches';
$lang['hierarchy_view'] = 'Hierarchy View';
```

**File Locations:**

- Primary: `/Users/rajivepai/Projects/Avenzur/V2/avenzur/app/language/english/`
- Check for: `system_lang.php`, `warehouse_lang.php`, or similar

**Cache Clear:**

```bash
rm -rf /Users/rajivepai/Projects/Avenzur/V2/avenzur/app/cache/*
```

**Estimated Time:** 20 minutes

---

### TASK 3: Manual Testing (45 minutes)

**Test Environment Setup:**

- [ ] Browser: Chrome (or Firefox/Safari)
- [ ] Login: Admin user
- [ ] URL: `/admin/` (your admin URL)
- [ ] Console: Open F12 for error checking
- [ ] Cache: Cleared before testing

**Test Execution:**

**Test 1: Feature Access (5 min)**

```
[ ] Login as Admin
[ ] Navigate to Settings menu
[ ] Find "Organization Setup" or "Pharmacy Hierarchy Setup"
[ ] Click to open
[ ] Page loads without errors
[ ] 3 tabs visible
[ ] No console errors
```

**Test 2: Add Pharmacy (10 min)**

```
[ ] Click "Add Pharmacy" button
[ ] Modal opens
[ ] Fill form with test data:
    - Group: Select any
    - Code: PHARM_TEST_001
    - Name: Test Pharmacy
    - Address: Test Address
    - Phone: 123456
    - Email: test@test.com
    - WH Code: WH_TEST_001
    - WH Name: Test Main WH
[ ] Submit form
[ ] Success notification appears
[ ] Modal closes
[ ] Pharmacy appears in table
[ ] Check database (see below)
```

**Database Verification:**

```sql
SELECT * FROM loyalty_pharmacies WHERE code = 'PHARM_TEST_001';
SELECT * FROM sma_warehouses WHERE code LIKE 'WH%TEST%';
-- Should see: 1 pharmacy + 1 mainwarehouse
```

**Test 3: Add Branch (10 min)**

```
[ ] Go to Branches tab
[ ] Select created pharmacy from dropdown
[ ] Click "Add Branch"
[ ] Fill form:
    - Pharmacy: Selected pharmacy
    - Code: BR_TEST_001
    - Name: Test Branch
    - Address: Test Address
    - Phone: 987654
    - Email: branch@test.com
[ ] Submit
[ ] Success notification
[ ] Branch appears in table
[ ] Check database (see below)
```

**Database Verification:**

```sql
SELECT * FROM loyalty_branches WHERE code = 'BR_TEST_001';
SELECT * FROM sma_warehouses WHERE parent_id IS NOT NULL;
-- Should see branch warehouse with parent_id
```

**Test 4: Hierarchy View (5 min)**

```
[ ] Go to Hierarchy View tab
[ ] Tree structure renders
[ ] Shows pharmacy + branch in hierarchy
[ ] Proper indentation
```

**Test 5: Delete Branch (5 min)**

```
[ ] Go to Branches tab
[ ] Click delete on test branch
[ ] Confirm deletion
[ ] Branch removed from table
[ ] Success notification
[ ] Verify database:
    SELECT * FROM loyalty_branches WHERE code = 'BR_TEST_001';
    -- Should return 0 rows
```

**Test 6: Delete Pharmacy (5 min)**

```
[ ] Go to Pharmacies tab
[ ] Click delete on test pharmacy
[ ] Confirm deletion
[ ] Pharmacy removed from table
[ ] Check cascade delete:
    SELECT * FROM loyalty_pharmacies WHERE code = 'PHARM_TEST_001';
    SELECT * FROM sma_warehouses WHERE code = 'WH_TEST_001';
    -- Both should return 0 rows
```

**Estimated Time:** 45 minutes total

---

### TASK 4: Production Readiness Assessment (5 minutes)

**Verification Checklist:**

- [ ] Database migration successful
- [ ] All 3 tables exist and populated correctly
- [ ] Language keys added and displaying
- [ ] Feature accessible from Settings menu
- [ ] All 6 test cases passed
- [ ] No JavaScript errors in console
- [ ] No PHP errors in logs
- [ ] Mobile responsive (tested at 375px width)
- [ ] CRUD operations working
- [ ] Data persisting to database

**Success Criteria Met: [ ] YES ✅ [ ] NO ❌**

If YES: **Proceed to production deployment**  
If NO: **Identify issues, fix, and re-test**

---

## ISSUE RESOLUTION GUIDE

### Issue: Migration SQL Error

```
Error: Unknown column 'parent_id' in 'sma_warehouses'
```

**Solution:**

1. Check if column already exists: `DESC sma_warehouses | grep parent_id`
2. If exists, skip that line in migration
3. If not exists, verify MySQL user has ALTER TABLE permission

### Issue: Language Keys Not Showing

```
Page shows: [pharmacy_hierarchy_setup]
```

**Solution:**

1. Verify keys added to correct language file
2. Check file syntax: `php -l app/language/english/[lang_file].php`
3. Clear cache: `rm -rf app/cache/*`
4. Refresh browser with Ctrl+F5

### Issue: Menu Item Not Visible

```
Settings menu doesn't show "Organization Setup"
```

**Solution:**

1. Verify logged in as Owner/Admin
2. Check header.php modification: `grep -n "organization_setup" themes/blue/admin/views/header.php`
3. Verify line exists around 1415
4. Clear cache

### Issue: AJAX Error (404)

```
Console shows: POST /admin/organization_setup/add_pharmacy 404
```

**Solution:**

1. Verify controller file exists: `ls -la app/controllers/admin/Organization_setup.php`
2. Check file permissions: Should be readable by web server
3. Verify no PHP syntax errors: `php -l Organization_setup.php`
4. Restart web server (or Docker)

### Issue: Warehouse Not Auto-Created

```
When adding pharmacy, warehouse not created
```

**Solution:**

1. Check Organization_setup.php for `_create_warehouse()` method
2. Verify no transaction rollback occurring
3. Check database logs for errors
4. Verify sma_warehouses table accessible

---

## TIME TRACKING

| Task               | Estimated  | Actual         | Notes            |
| ------------------ | ---------- | -------------- | ---------------- |
| Database Migration | 5 min      | \_\_\_ min     | Verify 3 tables  |
| Language Keys      | 20 min     | \_\_\_ min     | ~30 keys total   |
| Manual Testing     | 45 min     | \_\_\_ min     | 6 test cases     |
| Issue Resolution   | Variable   | \_\_\_ min     | If needed        |
| **TOTAL**          | **70 min** | **\_\_\_ min** | Target: < 90 min |

---

## SIGN-OFF

**Phase 4 Execution Completed By:** ******\_\_\_******

**Date:** ******\_\_\_******

**Time Start:** ******\_\_\_******

**Time End:** ******\_\_\_******

**All Checkpoints Passed:** [ ] YES ✅ [ ] NO ❌

**Ready for Production:** [ ] YES ✅ [ ] NO ❌

**Issues Found:** **********************\_\_\_**********************

---

**Notes:** **********************\_\_\_**********************

---

---

## NEXT STEPS AFTER PHASE 4

Once Phase 4 is complete and all checkpoints passed:

1. **Merge to Main Branch**
2. **Push to Production**
3. **Execute Migration on Production**
4. **Add Language Keys to Production**
5. **Verify in Production**
6. **Monitor Logs**

---

**Phase 4 Execution Checklist**  
Generated: October 24, 2025  
Status: Ready for Execution ✅

**Use this checklist to ensure nothing is missed during Phase 4 activation!**
