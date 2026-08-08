# PHASE 4 - MANUAL TESTING GUIDE

**Status:** 🟢 Ready for Testing  
**Database:** ✅ Migration Complete  
**Language Keys:** ✅ Added (45 keys)  
**Time Estimate:** 45 minutes  
**Date:** October 24, 2025

---

## 📋 PRE-TESTING CHECKLIST

Before starting tests, verify these prerequisites:

```
□ Docker is running: docker compose ps
  Expected: All containers UP

□ MySQL accessible: mysql -h localhost -u admin -pR00tr00t retaj_aldawa
  Expected: mysql> prompt

□ Browser ready: Chrome/Firefox with F12 console open
  Expected: Console tab visible

□ Admin user logged in: http://localhost/admin/dashboard
  Expected: Authenticated dashboard visible

□ Application is up: curl -s http://localhost/admin/dashboard | grep -q dashboard
  Expected: No error response
```

---

## 🧪 TEST CASES (8 Total)

### TEST 1: Access Pharmacy Hierarchy Feature

**Duration:** 5 minutes  
**Objective:** Verify feature page loads correctly

**Steps:**

```
1. Log in as admin user
   Expected: Dashboard loads

2. Navigate to Settings menu
   Expected: Settings menu visible with options

3. Click "Pharmacy Hierarchy Setup" or "Organization Setup"
   Expected: Feature page loads without error

4. Verify page structure:
   - Title: "Pharmacy Hierarchy Setup"
   - Three tabs visible: "Pharmacies" | "Branches" | "Hierarchy View"
   - Buttons visible: "Add Pharmacy", "Add Branch"
   - Tables present with columns

5. Check browser console (F12)
   Expected: No JavaScript errors (red text)
```

**Success Criteria:**

```
✅ Page loads successfully
✅ All tabs visible and accessible
✅ No JavaScript errors in console
✅ No PHP warnings/errors in server logs
✅ Page styling looks correct (not broken layout)
```

**Troubleshooting:**

```
❌ Page not found (404)
   → Check menu item added to header.php
   → Verify Organization_setup controller exists
   → Clear browser cache and refresh

❌ Menu item not visible
   → Check blue theme header.php for pharmacy_hierarchy_setup menu item
   → Verify user is Admin role
   → Check for permission issues

❌ JavaScript errors in console
   → Check for jQuery/Bootstrap loading issues
   → Verify AJAX URLs are correct
   → Check for missing JavaScript files
```

---

### TEST 2: Add Pharmacy

**Duration:** 10 minutes  
**Objective:** Create new pharmacy record

**Steps:**

```
1. On "Pharmacies" tab, click "Add Pharmacy" button
   Expected: Modal dialog appears with form

2. Fill form:
   - Pharmacy Code: "PH001" (or unique code)
   - Pharmacy Name: "Test Pharmacy Alpha"
   - Select Pharmacy Group: Choose any group from dropdown
   Expected: Form fields accept input

3. Click "Save" button
   Expected: Modal closes, new row appears in table

4. Verify in table:
   - New row shows at top or bottom
   - Code and Name display correctly
   - Can see Edit/Delete action buttons

5. Check database directly:
   mysql> SELECT * FROM loyalty_pharmacies WHERE code='PH001'\G
   Expected: Record exists with correct data

6. Check console for AJAX response
   Expected: 200 status (success)
```

**Success Criteria:**

```
✅ Modal opens without error
✅ Form validation works (required fields highlighted if empty)
✅ Record created in database
✅ Table updates immediately to show new record
✅ AJAX returns success (200 status)
✅ No console JavaScript errors
```

**Troubleshooting:**

```
❌ Modal doesn't open
   → Check jQuery and Bootstrap loading
   → Verify JavaScript in view file is correct
   → Check for JavaScript syntax errors in console

❌ Form submission fails
   → Check AJAX URL: should be admin_url('organization_setup/add_pharmacy')
   → Verify CSRF token is included
   → Check server logs for PHP errors
   → Verify Database permissions

❌ Record not appearing in table
   → Check database for record existence
   → Verify AJAX response contains data
   → Clear page cache
   → Manually refresh page
```

---

### TEST 3: Add Branch

**Duration:** 10 minutes  
**Objective:** Create branch under pharmacy

**Steps:**

```
1. On "Branches" tab, click "Add Branch" button
   Expected: Modal dialog appears

2. Fill form:
   - Branch Code: "BR001"
   - Branch Name: "Test Branch Beta"
   - Parent Pharmacy: Select "Test Pharmacy Alpha" (from TEST 2)
   Expected: Dropdown populated with pharmacies

3. Click "Save" button
   Expected: Modal closes, new row appears in table

4. Verify in table:
   - Code, Name, Parent Pharmacy display correctly
   - Action buttons (Edit/Delete) visible

5. Check database:
   mysql> SELECT * FROM loyalty_branches WHERE code='BR001'\G
   Expected: Record exists with correct pharmacy_id

6. Check console
   Expected: No errors, AJAX success response
```

**Success Criteria:**

```
✅ Modal opens correctly
✅ Pharmacy dropdown populates
✅ Record created in database with correct parent
✅ Table shows new branch immediately
✅ AJAX returns success
✅ No console errors
```

**Troubleshooting:**

```
❌ Pharmacy dropdown empty
   → Run: SELECT * FROM loyalty_pharmacies;
   → If empty, complete TEST 2 first
   → Check AJAX endpoint get_pharmacies returns data

❌ Record created but wrong parent
   → Check form field binding
   → Verify POST data includes pharmacy_id
   → Check database insert statement in controller

❌ AJAX fails with 404
   → Verify organization_setup/add_branch endpoint exists
   → Check controller method name spelling
   → Check routing configuration
```

---

### TEST 4: Hierarchy View

**Duration:** 10 minutes  
**Objective:** Verify tree structure visualization

**Steps:**

```
1. Click on "Hierarchy View" tab
   Expected: Tree structure displays

2. Verify tree shows:
   - Company name at root level
   - Pharmacy groups as children (if any)
   - Pharmacies under groups
   - Branches under pharmacies

3. Expand/collapse tree nodes by clicking
   Expected: Tree nodes expand/collapse smoothly

4. Verify each node shows:
   - Correct name
   - Correct hierarchy level
   - Proper indentation
   - Expand/collapse icon

5. Check console
   Expected: AJAX call to get_hierarchy_tree completes
   Expected: No JavaScript errors

6. Test on different screen sizes (responsive)
   Expected: Tree remains readable on smaller screens
```

**Success Criteria:**

```
✅ Tree structure displays correctly
✅ All hierarchy levels visible
✅ Expand/collapse works
✅ No JavaScript errors
✅ AJAX data loads successfully
✅ Responsive on different screen sizes
```

**Troubleshooting:**

```
❌ Tree not displaying
   → Check browser console for JavaScript errors
   → Verify get_hierarchy_tree AJAX returns data
   → Check tree rendering JavaScript

❌ Expand/collapse not working
   → Verify jQuery event handlers attached
   → Check for JavaScript syntax errors
   → Test on different browser

❌ Incomplete hierarchy shown
   → Verify database has pharmacy group, pharmacy, branch records
   → Check hierarchical relationships are correct
   → Query: SELECT * FROM loyalty_pharmacy_groups;
```

---

### TEST 5: Delete Pharmacy

**Duration:** 5 minutes  
**Objective:** Delete pharmacy and verify cascade

**Steps:**

```
1. From "Pharmacies" tab, find "Test Pharmacy Alpha" (from TEST 2)
   Expected: Row visible in table

2. Click "Delete" action button
   Expected: Confirmation dialog appears

3. Click "Confirm" button
   Expected: Row disappears from table

4. Verify in database:
   mysql> SELECT * FROM loyalty_pharmacies WHERE code='PH001'\G
   Expected: No records returned (deleted)

5. Also verify cascade delete worked:
   mysql> SELECT * FROM loyalty_branches WHERE pharmacy_id = [deleted_id]\G
   Expected: No records returned (branches also deleted)

6. Check console
   Expected: Success message, no errors
```

**Success Criteria:**

```
✅ Confirmation dialog appears before delete
✅ Record deleted from database
✅ Table updates immediately
✅ Cascade delete removes related branches
✅ No console errors
✅ AJAX returns success
```

**Troubleshooting:**

```
❌ Delete fails / record still exists
   → Check database DELETE permissions
   → Verify transaction rollback not triggered
   → Check for foreign key constraint errors
   → Review controller delete method

❌ Branches not deleted (cascade failed)
   → Check foreign key constraints in database
   → Verify CASCADE DELETE configured
   → Review database migration
   → Query: SHOW CREATE TABLE loyalty_branches\G

❌ Confirmation dialog not appearing
   → Check jQuery confirmation handler
   → Verify onclick handler attached to delete button
   → Check for JavaScript errors in console
```

---

### TEST 6: Delete Branch

**Duration:** 5 minutes  
**Objective:** Delete branch record

**Steps:**

```
1. From "Branches" tab, find "Test Branch Beta" (from TEST 3)
   Expected: Row visible

2. Click "Delete" action button
   Expected: Confirmation dialog appears

3. Click "Confirm"
   Expected: Row disappears

4. Verify in database:
   mysql> SELECT * FROM loyalty_branches WHERE code='BR001'\G
   Expected: No records returned

5. Check console
   Expected: Success message
```

**Success Criteria:**

```
✅ Confirmation appears
✅ Record deleted from database
✅ Table updates immediately
✅ No console errors
✅ AJAX success response
```

---

### TEST 7: Mobile Responsive Design

**Duration:** 5 minutes  
**Objective:** Verify mobile layout works

**Steps:**

```
1. Open feature page on desktop (current state)
   Expected: All 3 tabs, tables, modals visible

2. Open browser DevTools (F12)
   Expected: DevTools panel opens

3. Click "Toggle Device Toolbar" (phone icon)
   Expected: View switches to mobile viewport

4. Select Mobile device preset (iPhone 12)
   Expected: View narrows to ~390px width

5. Test mobile layout:
   □ Tabs still accessible (stacked or scrollable)
   □ Tables readable (horizontal scroll if needed)
   □ Buttons clickable (48px+ touch targets)
   □ Form inputs large enough to tap
   □ Modal dialog fits on screen
   □ No horizontal scrolling on page level

6. Test buttons and forms on mobile
   Expected: Everything clickable and functional

7. Return to desktop view
   Expected: Layout returns to normal
```

**Success Criteria:**

```
✅ All tabs accessible on mobile
✅ Tables readable (scrollable if needed)
✅ Buttons have minimum 48px touch targets
✅ Forms are usable on mobile
✅ No unwanted horizontal scrolling
✅ Modal dialogs fit mobile screen
✅ Responsive breakpoints working
```

**Troubleshooting:**

```
❌ Layout broken on mobile
   → Check Tailwind CSS responsive classes
   → Verify CSS media queries
   → Check Bootstrap grid system
   → Test in real mobile device

❌ Touch targets too small
   → Check button/link CSS for padding
   → Verify minimum 48px x 48px size
   → Update CSS padding/margins

❌ Tables not scrollable
   → Add table wrapper with overflow-x: auto
   → Check CSS for hidden overflow
   → Test horizontal scroll on actual device
```

---

### TEST 8: Console & Error Checking

**Duration:** 5 minutes  
**Objective:** Verify no errors in browser/server logs

**Steps:**

```
1. Open browser DevTools (F12)
   Expected: Tabs visible: Console, Network, etc.

2. Go to "Console" tab
   Expected: Console panel shows any JavaScript errors

3. Perform actions (all tests 1-7):
   □ Click around the page
   □ Add pharmacy
   □ Add branch
   □ Delete pharmacy
   □ Delete branch
   □ Toggle tabs
   □ Expand hierarchy tree

4. Monitor console while performing actions
   Expected: No RED error messages

5. Acceptable console output:
   ✅ Network requests (gray text): 200/201 status codes
   ✅ Info messages: "[INFO] Loading..."
   ✅ Warning messages: CSS issues (yellow)

6. Check server logs:
   tail -f /path/to/app/logs/*.log
   Expected: No PHP errors (no Fatal/Parse errors)
   Expected: Only notices/warnings (acceptable)

7. Check Network tab
   Expected: All AJAX calls return 200 status
   Expected: No failed requests (404/500)
   Expected: Response times under 1 second
```

**Success Criteria:**

```
✅ No red JavaScript errors in console
✅ No 404 Not Found errors
✅ No 500 Server errors
✅ No PHP Fatal/Parse errors in logs
✅ All AJAX requests return 200/201
✅ Page performance acceptable (< 1s responses)
✅ No deprecated browser warnings
```

**Troubleshooting:**

```
❌ Red errors in console
   → Read error message carefully
   → Check file/line number in error
   → Go to that line and fix JavaScript
   → Common: undefined variable, syntax error

❌ AJAX returning 404
   → Controller method doesn't exist
   → Check spelling in AJAX URL
   → Verify controller file name
   → Check routing configuration

❌ AJAX returning 500
   → PHP error in controller
   → Check server logs for details
   → Verify database connection
   → Test SQL query directly in MySQL

❌ Missing library errors
   → Verify jQuery loaded
   → Check Bootstrap included
   → Verify Select2 library loaded
   → Check script load order in HTML
```

---

## ✅ FINAL VERIFICATION CHECKLIST

```
After all 8 tests complete, verify:

FUNCTIONALITY:
□ All 8 tests passed
□ All features working as expected
□ No critical errors

DATABASE:
□ Pharmacy records created/deleted correctly
□ Branch records created/deleted correctly
□ Cascade deletes working (branches deleted with pharmacy)
□ Data integrity maintained

BROWSER:
□ No JavaScript errors in console
□ All AJAX calls successful (200 status)
□ Page responsive on desktop/mobile/tablet
□ Modals open/close correctly

SERVER:
□ No PHP fatal errors
□ No database connection errors
□ Language keys displaying correctly
□ Permission checks working

PERFORMANCE:
□ Page loads in < 2 seconds
□ AJAX requests respond in < 1 second
□ No memory leaks (console shows stable memory)
□ Smooth animations/transitions

UI/UX:
□ All buttons clickable
□ Forms validate input
□ Error messages clear
□ Success messages appear
□ Navigation intuitive
□ Colors consistent with theme
```

---

## 📊 TEST EXECUTION LOG

```
TEST 1: Access Feature
━━━━━━━━━━━━━━━━━━━━━━━
Start Time: ___:___
End Time:   ___:___
Status:     ✓ PASS / ✗ FAIL / ⚠ WARNING
Notes:      _________________________________


TEST 2: Add Pharmacy
━━━━━━━━━━━━━━━━━━━━━━━
Start Time: ___:___
End Time:   ___:___
Status:     ✓ PASS / ✗ FAIL / ⚠ WARNING
Notes:      _________________________________


TEST 3: Add Branch
━━━━━━━━━━━━━━━━━━━━━━━
Start Time: ___:___
End Time:   ___:___
Status:     ✓ PASS / ✗ FAIL / ⚠ WARNING
Notes:      _________________________________


TEST 4: Hierarchy View
━━━━━━━━━━━━━━━━━━━━━━━
Start Time: ___:___
End Time:   ___:___
Status:     ✓ PASS / ✗ FAIL / ⚠ WARNING
Notes:      _________________________________


TEST 5: Delete Pharmacy
━━━━━━━━━━━━━━━━━━━━━━━
Start Time: ___:___
End Time:   ___:___
Status:     ✓ PASS / ✗ FAIL / ⚠ WARNING
Notes:      _________________________________


TEST 6: Delete Branch
━━━━━━━━━━━━━━━━━━━━━━━
Start Time: ___:___
End Time:   ___:___
Status:     ✓ PASS / ✗ FAIL / ⚠ WARNING
Notes:      _________________________________


TEST 7: Mobile Responsive
━━━━━━━━━━━━━━━━━━━━━━━
Start Time: ___:___
End Time:   ___:___
Status:     ✓ PASS / ✗ FAIL / ⚠ WARNING
Notes:      _________________________________


TEST 8: Console/Errors
━━━━━━━━━━━━━━━━━━━━━━━
Start Time: ___:___
End Time:   ___:___
Status:     ✓ PASS / ✗ FAIL / ⚠ WARNING
Notes:      _________________________________
```

---

## 🎯 NEXT STEPS

**If All Tests Pass (8/8 ✅):**

1. Document results in test log above
2. Take screenshots of each feature working
3. Prepare for production deployment
4. Mark Phase 4 COMPLETE
5. Ready for production release

**If Any Tests Fail (< 8/8 ✗):**

1. Review troubleshooting section
2. Fix identified issues
3. Re-run failed test
4. Document fixes applied
5. Repeat until all pass

**Production Deployment Steps:**

1. Merge code to main branch
2. Pull code on production server
3. Run migration on production database
4. Add language keys to production
5. Verify in production environment
6. Monitor logs for errors

---

**Phase 4 Testing Guide**  
**Generated:** October 24, 2025  
**Status:** Ready for Testing ✅
