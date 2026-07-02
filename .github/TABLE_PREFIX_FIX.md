# Table Prefix Issue & Fix - Pharma Group Feature

## Problem

### Error Message
```
Query error: Table 'rawabi_jeddah.sma_loyalty_pharmacy_groups' doesn't exist

Invalid query: SELECT *
FROM `sma_loyalty_pharmacy_groups`
WHERE `name` = 'Test Group'
LIMIT 1
```

### Root Cause

The Avenzur system uses **CodeIgniter 3** which has a database prefix configuration:

```php
// app/config/database.php
'dbprefix' => 'sma_',  // Adds 'sma_' prefix to all table names
```

When using CodeIgniter's form validation `is_unique` rule like this:
```php
$this->form_validation->set_rules(
    'name', 
    'Pharmacy Group Name', 
    'required|is_unique[loyalty_pharmacy_groups.name]'  // ← Problem here
);
```

CodeIgniter automatically adds the prefix, creating:
```sql
SELECT * FROM `sma_loyalty_pharmacy_groups` WHERE `name` = 'Test Group'
```

### Why This Failed

The loyalty-related tables **do NOT have the `sma_` prefix**:
- ✅ `loyalty_pharmacy_groups` (correct, no prefix)
- ✅ `loyalty_companies` (correct, no prefix)
- ✅ `sma_warehouses` (correct, has prefix)

But the validation rule was looking for:
- ❌ `sma_loyalty_pharmacy_groups` (WRONG - doesn't exist)
- ❌ `sma_loyalty_companies` (WRONG - doesn't exist)

---

## Solution

### Change 1: Remove the `is_unique` Rule

**File**: `/app/controllers/admin/Organization_setup.php`  
**Method**: `add_pharma_group()`

**Before**:
```php
$this->form_validation->set_rules(
    'name', 
    'Pharmacy Group Name', 
    'required|is_unique[loyalty_pharmacy_groups.name]'  // CodeIgniter adds prefix
);
```

**After**:
```php
$this->form_validation->set_rules(
    'name', 
    'Pharmacy Group Name', 
    'required'  // Removed is_unique to avoid prefix issue
);
```

### Change 2: Add Manual Duplicate Check

**File**: `/app/controllers/admin/Organization_setup.php`  
**Method**: `add_pharma_group()`

Added after form validation passes:
```php
// Additional validation: Check for duplicate name in loyalty_pharmacy_groups (no prefix)
$name = $this->input->post('name');
$existing_name = $this->db->query(
    "SELECT id FROM loyalty_pharmacy_groups WHERE name = ? LIMIT 1",
    [$name]
)->row();

if ($existing_name) {
    $this->sma->send_json([
        'success' => false,
        'message' => 'Pharmacy Group name already exists'
    ]);
    return;
}
```

### Why This Works

1. **Direct Query**: Uses raw SQL query with `$this->db->query()` instead of CodeIgniter's validation rule
2. **No Prefix**: The query explicitly specifies `loyalty_pharmacy_groups` without prefix
3. **Bypasses Config**: The `dbprefix` setting in config doesn't affect raw queries
4. **Identical Result**: Provides the same duplicate checking functionality

---

## Key Learnings

### Table Prefix Rules in CodeIgniter

| Scenario | Result |
|----------|--------|
| `is_unique[table.field]` form rule | Adds `dbprefix` (becomes `sma_table`) |
| `$this->db->query("FROM table")` | No prefix added (you specify exact name) |
| `$this->db->from('table')` | Adds `dbprefix` (becomes `sma_table`) |
| `$this->db->get('table')` | Adds `dbprefix` (becomes `sma_table`) |

### Two Table Naming Conventions in Avenzur

**Legacy Tables** (with prefix):
- `sma_warehouses` - Warehouse/location data
- `sma_pharmacies` - Pharmacy operational data
- `sma_branches` - Branch location data
- All `sma_*` tables

**Loyalty Tables** (without prefix):
- `loyalty_pharmacy_groups` - Groups/companies
- `loyalty_companies` - Company records
- `loyalty_pharmacies` - Pharmacy loyalty profiles
- All `loyalty_*` tables

### Migration Pattern

When the system migrated to add loyalty features, the designers chose:
- **Keep existing `sma_*` tables unchanged** (backward compatibility)
- **Create new `loyalty_*` tables without prefix** (clean separation)
- **Link via `external_id` field** (bridge between two systems)

---

## Code Architecture After Fix

### Three-Table Transaction

The Pharma Group feature creates/updates three tables in a transaction:

```
USER INPUT (code, name, phone, email, address)
    ↓
    └─→ VALIDATION (manual duplicate check on loyalty_pharmacy_groups)
        ↓
        └─→ TRANSACTION START
            ├─ INSERT INTO sma_warehouses (with prefix)
            │  └─ Result: warehouse_id
            │
            ├─ INSERT INTO loyalty_companies (no prefix)
            │  └─ Result: company_id (UUID)
            │
            ├─ INSERT INTO loyalty_pharmacy_groups (no prefix)
            │  └─ Links to both via company_id and external_id
            │
            └─ TRANSACTION COMMIT/ROLLBACK
                ↓
            RESPONSE JSON (success/error)
```

### Correct Table Names by Operation

```php
// ✅ CORRECT - sma_warehouses (has prefix)
$this->db->insert('sma_warehouses', $data);

// ✅ CORRECT - Raw query with exact table name (no prefix)
$this->db->query(
    "INSERT INTO loyalty_companies (id, code, name, ...) VALUES (?, ?, ?, ...)",
    [$company_id, $code, $name, ...]
);

// ✅ CORRECT - Raw query with exact table name (no prefix)
$this->db->query(
    "INSERT INTO loyalty_pharmacy_groups (id, code, name, company_id, external_id, ...) VALUES (?, ?, ?, ?, ?, ...)",
    [$pharma_group_id, $code, $name, $company_id, $warehouse_id, ...]
);

// ❌ WRONG - Would become sma_loyalty_pharmacy_groups
$this->db->from('loyalty_pharmacy_groups');

// ❌ WRONG - Would become sma_loyalty_companies
$this->db->get('loyalty_companies');
```

---

## Testing the Fix

### Test Case 1: Create Pharma Group
```
Input: code='PG-001', name='Test Group', phone='+966...', address='...'
Expected: ✅ Created successfully
Result: ✅ PASS
```

### Test Case 2: Duplicate Name Validation
```
Input: name='Test Group' (already exists)
Expected: ❌ Error message 'Pharmacy Group name already exists'
Result: ✅ PASS (manual check catches it, no DB error)
```

### Test Case 3: Duplicate Code Validation
```
Input: code='PG-001' (already exists in sma_warehouses)
Expected: ❌ Error message 'Pharmacy Group code already exists'
Result: ✅ PASS (form validation rule is_unique[sma_warehouses.code])
```

---

## Files Modified

1. **`/app/controllers/admin/Organization_setup.php`**
   - Removed `is_unique[loyalty_pharmacy_groups.name]` validation rule
   - Added manual duplicate name check using direct query
   - Added comment explaining why (table prefix issue)

---

## Deployment Notes

### No Database Changes Needed
✅ No new tables  
✅ No schema changes  
✅ No migrations required  
✅ Existing data unaffected  

### Backward Compatibility
✅ Code still validates duplicates  
✅ Same error messages to users  
✅ Same transaction safety  
✅ No API changes  

### Rollback
If needed, simply revert the commit:
```bash
git revert 791ead901
```

---

## Summary

| Aspect | Before | After |
|--------|--------|-------|
| Validation Method | CodeIgniter form rule | Manual SQL query |
| Table Name | `sma_loyalty_pharmacy_groups` (wrong) | `loyalty_pharmacy_groups` (correct) |
| Error | "Table doesn't exist" | ✅ No error |
| Duplicate Checking | ❌ Failed | ✅ Works |
| Transaction Safety | N/A (failed before) | ✅ Maintained |

---

## Reference

**Commit**: `791ead901`  
**Branch**: `add_pharma_group`  
**Date**: October 29, 2025  
**Status**: ✅ Fixed and Tested
