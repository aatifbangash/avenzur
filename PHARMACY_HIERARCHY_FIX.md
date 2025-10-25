# Pharmacy Hierarchy Dropdown Fix

## Problem

Pharmacy group dropdown was not showing any data on the organization_setup/pharmacy_hierarchy page.

**Symptom**: Empty dropdown on page load
**Browser Error**: `Table 'retaj_aldawa.sma_loyalty_pharmacy_groups' doesn't exist`

## Root Cause

CodeIgniter's `dbprefix = 'sma_'` configuration automatically adds the prefix to all queries using the query builder. However, the loyalty tables (`loyalty_pharmacy_groups`, `loyalty_pharmacies`, `loyalty_branches`) in the database do NOT have this prefix.

When CodeIgniter tried to access `loyalty_pharmacy_groups`, it was looking for `sma_loyalty_pharmacy_groups` which doesn't exist.

## Solution Implemented

### 1. Database Table Names

- Renamed `sma_loyalty_pharmacy_groups` back to `loyalty_pharmacy_groups`
- Confirmed all loyalty\_\* tables exist without prefix:
  - `loyalty_pharmacy_groups`
  - `loyalty_pharmacies`
  - `loyalty_branches`
  - Plus 7 other loyalty\_\* tables

### 2. Controller Updates

**File**: `/app/controllers/admin/Organization_setup.php`

Converted all 8 methods to use **raw SQL queries** instead of CodeIgniter's query builder to bypass the prefix mechanism:

#### GET Methods (Data Retrieval) - ✅ FIXED

- **get_pharmacy_groups()** (Lines 55-67)
  - Uses: `$this->db->query("SELECT id, code, name FROM loyalty_pharmacy_groups ORDER BY name ASC")`
- **get_pharmacies()** (Lines 74-97)
  - Uses: `$this->db->query($query, [$group_id])` with parameterized queries
- **get_branches()** (Lines 128-153)
  - Uses: `$this->db->query($query, [$pharmacy_id])` with parameterized queries
- **get_hierarchy_tree()** (Lines 157-202)
  - Uses: Multiple `$this->db->query()` calls in loop

#### Write Methods (CREATE/DELETE) - ✅ FIXED

- **add_pharmacy()** (Lines 275-285)
  - Old: `$this->db->insert('`loyalty_pharmacies`', $data)`
  - New: `$this->db->query("INSERT INTO loyalty_pharmacies (...) VALUES (?, ?, ...)", [...])`
- **add_branch()** (Lines 357-363)
  - Old: `$this->db->insert('`loyalty_branches`', $data)`
  - New: `$this->db->query("INSERT INTO loyalty_branches (...) VALUES (?, ?, ...)", [...])`
- **delete_pharmacy()** (Lines 410-420)
  - Old: `$this->db->delete('`loyalty_pharmacies`', ['warehouse_id' => $id])`
  - New: `$this->db->query("DELETE FROM loyalty_pharmacies WHERE warehouse_id = ?", [$id])`
- **delete_branch()** (Lines 450-462)
  - Old: `$this->db->delete('`loyalty_branches`', ['warehouse_id' => $id])`
  - New: `$this->db->query("DELETE FROM loyalty_branches WHERE warehouse_id = ?", [$id])`

### 3. Parameterized Queries

All INSERT, SELECT, and DELETE queries now use parameterized queries with `?` placeholders:

```php
$this->db->query($sql, [$param1, $param2, $param3])
```

This prevents SQL injection and maintains data integrity.

### 4. View File Enhancements

**File**: `/themes/blue/admin/views/settings/pharmacy_hierarchy.php`

Added comprehensive JavaScript debugging statements to troubleshoot AJAX calls:

- Lines 390-391: Script load confirmation
- Lines 395-427: `initPharmacySetup()` with initialization logging
- Lines 429-473: `loadPharmacyGroups()` with detailed console logs:
  ```javascript
  console.log("loadPharmacyGroups() called");
  console.log("Calling URL:", url);
  console.log("AJAX Success - Data received:", data);
  console.log("Adding " + data.data.length + " options");
  console.log("AJAX Error - Status:", status);
  console.log("Response Text:", xhr.responseText);
  ```

### 5. Select2 Initialization Pattern

Fixed the JavaScript initialization to follow this pattern:

1. Fetch data via AJAX first
2. Remove old options
3. Add new options to select element
4. Initialize or reinitialize Select2

This ensures Select2 is initialized only after data is available.

## Verification

### Database Tables Confirmed

```
✅ loyalty_pharmacy_groups - 4 records
✅ loyalty_pharmacies - exists
✅ loyalty_branches - exists
✅ loyalty_warehouse_mappings - exists
✅ 6 other loyalty_* tables
```

### Code Changes Verified

```
✅ All 8 methods converted to raw SQL
✅ No backtick syntax remaining for loyalty tables
✅ All queries use parameterized approach
✅ JavaScript debugging statements added
✅ Select2 initialization fixed
```

## Testing Checklist

- [ ] Navigate to: `http://localhost:8080/avenzur/admin/organization_setup/pharmacy_hierarchy`
- [ ] Open Browser DevTools (F12 → Console tab)
- [ ] Verify console shows:
  - "pharmacy_hierarchy.php script loaded"
  - "initPharmacySetup() called"
  - "loadPharmacyGroups() called"
  - "AJAX Success - Data received: {...}"
- [ ] Pharmacy Group dropdown should populate with 4 groups:
  - AVNZR-SOUTH
  - EANDM-CENTRAL
  - AVNZR-NORTH
  - HLTHP-METRO
- [ ] Test adding a pharmacy via modal
- [ ] Test adding a branch via modal
- [ ] Test delete pharmacy/branch operations
- [ ] Verify data persists on page refresh
- [ ] Check for any console errors

## Troubleshooting Guide

### If dropdown is still empty:

1. Check browser console (F12 → Console) for errors
2. Look for "AJAX Error" message in console
3. Check Network tab → find AJAX request for `/Organization_setup/get_pharmacy_groups`
4. Verify response JSON contains data with `data` array
5. Check MySQL query: `SELECT * FROM loyalty_pharmacy_groups;` - should return 4 rows

### If data appears but Select2 doesn't show options:

1. Check console for "AJAX Success" message
2. Verify "Adding X options" message appears
3. Check if Select2 initialization log shows
4. Try refreshing page - sometimes timing issues

### If add/delete operations fail:

1. Check browser Network tab for failed AJAX request
2. Look for error message in response
3. Check MySQL error log for permission issues
4. Verify user has proper permissions for INSERT/DELETE operations

## Key Differences from Original Code

| Aspect        | Before                             | After                        |
| ------------- | ---------------------------------- | ---------------------------- |
| Query Method  | `$this->db->query_builder` methods | `$this->db->query()` raw SQL |
| Table Names   | Affected by `dbprefix`             | Direct SQL, no prefix        |
| Security      | Backtick escaping                  | Parameterized queries        |
| Compatibility | CodeIgniter's query builder        | Direct MySQL queries         |
| Flexibility   | Limited to query builder syntax    | Full SQL control             |

## Files Modified

- `/app/controllers/admin/Organization_setup.php` - 8 methods converted
- `/themes/blue/admin/views/settings/pharmacy_hierarchy.php` - Debugging added

## Configuration References

- **Theme**: Blue (location: `/themes/blue/`)
- **Controller**: Organization_setup (location: `/app/controllers/admin/`)
- **Database**: retaj_aldawa
- **Prefix Setting**: `dbprefix = 'sma_'` (config/database.php) - applies to query builder only

## Notes

- Raw SQL queries bypass CodeIgniter's prefix mechanism completely
- Parameterized queries are used throughout to prevent SQL injection
- All loyalty\_\* tables are expected to have NO prefix
- sma_warehouses table continues to use normal prefix (existing queries work fine)
- This solution maintains data integrity while allowing loyalty module independence
