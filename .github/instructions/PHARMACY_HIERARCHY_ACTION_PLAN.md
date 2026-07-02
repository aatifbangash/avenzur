# PHARMACY HIERARCHY - QUICK ACTION PLAN

**Current Status:** Architecture refactoring complete ✅  
**Next Phase:** Database, Language Keys & Testing

---

## 🎯 IMMEDIATE ACTIONS (Next Session)

### ACTION 1: Execute Database Migration

**Effort:** 5 minutes  
**Risk:** Low

```bash
# Connect to database
mysql -u [user] -p [database] < /path/to/db/migrations/20251024_pharmacy_hierarchy_setup.sql

# Verify tables created
SHOW TABLES LIKE 'loyalty_%';
DESC sma_warehouses;  # Check for parent_id column
```

**Verification:**

- [ ] `loyalty_pharmacies` table exists
- [ ] `loyalty_branches` table exists
- [ ] `sma_warehouses` has `parent_id` column

---

### ACTION 2: Add Language Keys

**Effort:** 10 minutes  
**Files to Update:**

1. `/app/language/english/` - All en\_\* files
2. `/app/language/arabic/` - All ar\_\* files (if needed)

**Keys needed (~30):**

- pharmacy_hierarchy_setup
- manage_pharmacies
- manage_branches
- add_pharmacy
- add_branch
- pharmacy_group
- pharmacy_code
- pharmacy_name
- branch_code
- branch_name
- main_warehouse
- main_warehouse_description
- warehouse_type
- click_node_to_view_details
- no_hierarchy_data
- select_pharmacy_group
- select_pharmacy
- enter_pharmacy_info
- enter_branch_info
- unique_code
- unique_warehouse_code
- select_parent_company
- select_parent_pharmacy
- (+ more as needed)

**Reference:** Complete list in `PHARMACY_SETUP_REFACTORING_SUMMARY.md`

---

### ACTION 3: Manual Testing (Comprehensive)

**Effort:** 30-45 minutes  
**Test Environment:** Staging or Development

**Test Cases:**

#### T1: Access Feature

- [ ] Login as Admin
- [ ] Navigate to Settings → Organization Setup → Pharmacy Hierarchy
- [ ] Page loads without errors
- [ ] 3 tabs display: Pharmacies, Branches, Hierarchy View

#### T2: Add Pharmacy

- [ ] Click "Add Pharmacy" button
- [ ] Modal opens with form
- [ ] Fill all required fields
- [ ] Submit form
- [ ] Success notification appears
- [ ] Pharmacy appears in table
- [ ] Warehouse created (verify in Warehouses)
- [ ] Main warehouse created (verify in Warehouses)

#### T3: Add Branch

- [ ] Select pharmacy from dropdown
- [ ] Branches list loads
- [ ] Click "Add Branch" button
- [ ] Modal opens with form
- [ ] Select parent pharmacy
- [ ] Fill all required fields
- [ ] Submit form
- [ ] Success notification appears
- [ ] Branch appears in table
- [ ] Branch warehouse created

#### T4: Hierarchy View

- [ ] Click Hierarchy View tab
- [ ] Tree structure renders
- [ ] All pharmacies visible
- [ ] All branches visible
- [ ] Proper indentation showing hierarchy

#### T5: Delete Operations

- [ ] Delete branch from table
- [ ] Confirm delete dialog appears
- [ ] After confirmation, branch removed from table
- [ ] Delete pharmacy from table
- [ ] Confirm delete dialog appears
- [ ] After confirmation, pharmacy removed
- [ ] Branch automatically deleted (cascade)

#### T6: Form Validation

- [ ] Leave required fields empty → error message
- [ ] Enter invalid characters → validation error
- [ ] Try duplicate code → error message
- [ ] Try invalid data → server validation error

#### T7: Responsive Design

- [ ] Desktop (1200px+): Full layout displays correctly
- [ ] Tablet (768px-1200px): Proper stacking and spacing
- [ ] Mobile (< 768px): Single column, readable text, tappable buttons

#### T8: Data Integrity

- [ ] Create pharmacy → Check warehouse table for new entries
- [ ] Create branch → Check warehouse table, verify parent_id
- [ ] Delete pharmacy → Check warehouses deleted
- [ ] Delete branch → Check warehouse deleted

---

## 📋 WORKFLOW SEQUENCE

```
1. DATABASE MIGRATION
   └─ Execute SQL script
   └─ Verify tables/columns

2. LANGUAGE KEYS
   └─ Add to English language file
   └─ Add to Arabic language file (if needed)
   └─ Clear cache

3. MANUAL TESTING
   ├─ Access feature (smoke test)
   ├─ Pharmacies CRUD
   ├─ Branches CRUD
   ├─ Hierarchy visualization
   ├─ Form validation
   ├─ Mobile responsive
   └─ Data persistence

4. BUG FIXES (if any)
   └─ Fix issues found during testing
   └─ Re-test fixed issues

5. DEPLOYMENT
   ├─ Code review
   ├─ Merge to main branch
   ├─ Push to production
   └─ Post-deployment verification
```

---

## 🔍 FILES TO TEST

### View File

- `/themes/blue/admin/views/settings/pharmacy_hierarchy.php` (765 lines)
  - 3 tabs working
  - Modals functioning
  - JavaScript events firing
  - AJAX calls executing

### Controller

- `/app/controllers/admin/Organization_setup.php` (380+ lines)
  - All 10 methods callable
  - Authorization checks working
  - Form validation running
  - Database operations successful
  - Error handling effective

### Database

- `loyalty_pharmacies` table (new)
- `loyalty_branches` table (new)
- `sma_warehouses` table (modified - parent_id added)

### Menu Items

- Blue theme header.php: Organization Setup menu visible
- Default theme header.php: Organization Setup menu visible

---

## ✅ COMPLETION CHECKLIST

### Pre-Deployment

- [ ] Database migration executed
- [ ] Language keys added
- [ ] Manual testing passed (8 test cases)
- [ ] No JavaScript errors in console
- [ ] No PHP errors in logs
- [ ] No SQL errors in database

### Deployment

- [ ] Code merged to main branch
- [ ] Code pushed to production
- [ ] Database migration run on production
- [ ] Cache cleared
- [ ] Language keys available
- [ ] Feature accessible in production

### Post-Deployment

- [ ] Access feature in production
- [ ] Create pharmacy (success)
- [ ] Create branch (success)
- [ ] Create warehouse (auto-created)
- [ ] Delete operations work
- [ ] No errors in production logs

---

## 🐛 COMMON ISSUES & SOLUTIONS

### Issue 1: Language keys show as [pharmacy_hierarchy_setup]

**Solution:** Ensure language keys added to correct language file and cache cleared

### Issue 2: JavaScript errors in console

**Solution:** Verify jQuery, AdminLTE, and custom JS files loaded correctly

### Issue 3: AJAX calls failing

**Solution:** Check controller methods exist, verify admin_url() helper returns correct URLs

### Issue 4: Database migration fails

**Solution:** Verify database user has CREATE TABLE permissions, check SQL syntax

### Issue 5: Menu item not showing

**Solution:** Verify header.php modified correctly, check $Owner variable is true

### Issue 6: Warehouse not created automatically

**Solution:** Check Organization_setup controller method creates warehouses, verify transaction commits

---

## 📞 TROUBLESHOOTING

### Check Error Logs

```bash
tail -f /path/to/app/logs/log-*.txt
```

### Check Database

```sql
-- Check tables exist
SELECT * FROM information_schema.TABLES
WHERE TABLE_SCHEMA = 'database_name'
AND TABLE_NAME LIKE 'loyalty_%';

-- Check columns
DESC sma_warehouses;

-- Check data
SELECT * FROM loyalty_pharmacies;
SELECT * FROM loyalty_branches;
```

### Check Controllers

```bash
# Verify file exists
ls -la /app/controllers/admin/Organization_setup.php

# Check PHP syntax
php -l /app/controllers/admin/Organization_setup.php
```

### Check Views

```bash
# Verify file exists
ls -la /themes/blue/admin/views/settings/pharmacy_hierarchy.php

# Check for syntax errors (manual review)
```

---

## 🚀 SUCCESS CRITERIA

Feature is **production-ready** when:

✅ Database migration executed without errors  
✅ All language keys display correctly  
✅ All 8 test cases pass  
✅ No JavaScript console errors  
✅ No PHP error logs  
✅ Feature accessible from Settings menu  
✅ CRUD operations work correctly  
✅ Mobile responsive layout works  
✅ Data persists correctly to database  
✅ Hierarchy displays correctly

---

## 📞 QUESTIONS?

Refer to:

1. `PHARMACY_SETUP_REFACTORING_SUMMARY.md` - Full architectural overview
2. `PHARMACY_SETUP_UI_DOCUMENTATION.md` - Technical details
3. `PHARMACY_SETUP_IMPLEMENTATION.md` - Step-by-step implementation
4. Organization_setup.php - Source code and comments
5. pharmacy_hierarchy.php - View code and structure

---

**Next Steps:** Execute DATABASE MIGRATION first, then ADD LANGUAGE KEYS, then MANUAL TESTING
