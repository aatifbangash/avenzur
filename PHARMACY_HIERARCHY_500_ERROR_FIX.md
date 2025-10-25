# Pharmacy Hierarchy 500 Error Fix

## Problem

When selecting a pharmacy group in the organization_setup/pharmacy_hierarchy page, the system returned a **500 Internal Server Error** when trying to load pharmacies for that group.

**Error Details:**

```
GET http://localhost:8080/avenzur/admin/organization_setup/get_pharmacies?group_id=c1ddbc5c-8c80-4519-be06-e15bee2bc441 500 (Internal Server Error)
```

## Root Cause

The controller methods were using JOIN queries that referenced columns that don't exist in the loyalty tables:

1. **get_pharmacies()** - Tried to JOIN `loyalty_pharmacies` on `warehouse_id` but that column doesn't exist
2. **get_branches()** - Tried to JOIN `loyalty_branches` on `warehouse_id` but that column doesn't exist
3. **get_hierarchy_tree()** - Used same incorrect JOIN logic

## Database Structure Discovery

### loyalty_pharmacy_groups

```sql
- id (UUID)
- code
- name
- created_at
- updated_at
```

### loyalty_pharmacies

```sql
- id (UUID) - PRIMARY KEY
- code (UNIQUE)
- name
- pharmacy_group_id (references loyalty_pharmacy_groups.id) ← KEY FIELD
- created_at
- updated_at
```

### loyalty_branches

```sql
- id (UUID) - PRIMARY KEY
- code (UNIQUE)
- name
- pharmacy_id (references loyalty_pharmacies.id) ← KEY FIELD
- created_at
- updated_at
```

**Key Finding**: Loyalty tables store INDEPENDENT data, NOT as references to sma_warehouses. They have their own id fields and foreign keys between each other.

## Solutions Implemented

### 1. Fixed get_pharmacy_groups() ✅

**Before**: Query builder with backticks  
**After**: Raw SQL without warehouse JOINs

```php
$query = "SELECT id, code, name FROM loyalty_pharmacy_groups ORDER BY name ASC";
$groups = $this->db->query($query)->result_array();
```

### 2. Fixed get_pharmacies() ✅

**Before**:

```php
SELECT sw.id, sw.code, sw.name FROM sma_warehouses sw
LEFT JOIN loyalty_pharmacies lp ON sw.id = lp.warehouse_id
WHERE lp.pharmacy_group_id = ? AND sw.warehouse_type = 'pharmacy'
// ERROR: lp.warehouse_id doesn't exist!
```

**After**:

```php
SELECT id, code, name FROM loyalty_pharmacies
WHERE pharmacy_group_id = ?
ORDER BY name ASC
```

✅ **Tested**: Returns 2 pharmacies for group `c1ddbc5c-8c80-4519-be06-e15bee2bc441`:

- PHR-001: Avenzur Downtown Pharmacy
- PHR-002: Avenzur Northgate Pharmacy

### 3. Fixed get_branches() ✅

**Before**:

```php
SELECT sw.id, sw.code, sw.name FROM sma_warehouses sw
LEFT JOIN loyalty_branches lb ON sw.id = lb.warehouse_id
WHERE lb.pharmacy_id = ?
// ERROR: lb.warehouse_id doesn't exist!
```

**After**:

```php
SELECT id, code, name FROM loyalty_branches
WHERE pharmacy_id = ?
ORDER BY name ASC
```

### 4. Fixed get_hierarchy_tree() ✅

**Before**: Used complex JOINs with sma_warehouses and incorrect field references  
**After**: Direct queries from loyalty tables with proper foreign keys:

```php
// Groups
SELECT id, code, name FROM loyalty_pharmacy_groups

// Pharmacies for each group
SELECT id, code, name FROM loyalty_pharmacies
WHERE pharmacy_group_id = ?

// Branches for each pharmacy
SELECT id, code, name FROM loyalty_branches
WHERE pharmacy_id = ?
```

### 5. Fixed add_pharmacy() ✅

**Before**: Tried to insert `warehouse_id` column that doesn't exist

```php
INSERT INTO loyalty_pharmacies (pharmacy_group_id, warehouse_id, name, code, ...)
// ERROR: warehouse_id column doesn't exist!
```

**After**: Generates UUID and inserts correct columns:

```php
$loyalty_pharmacy_data = [
    'id' => $this->sma->generate_uuid(),
    'pharmacy_group_id' => $group_id,
    'name' => $name,
    'code' => $code,
    'created_at' => now(),
    'updated_at' => now()
];

INSERT INTO loyalty_pharmacies (id, pharmacy_group_id, name, code, created_at, updated_at)
VALUES (?, ?, ?, ?, ?, ?)
```

### 6. Fixed add_branch() ✅

**Before**: Tried to insert `warehouse_id` column that doesn't exist
**After**: Generates UUID and inserts with `pharmacy_id` foreign key:

```php
$loyalty_branch_data = [
    'id' => $this->sma->generate_uuid(),
    'pharmacy_id' => $pharmacy_id,
    'code' => $code,
    'name' => $name,
    'created_at' => now(),
    'updated_at' => now()
];

INSERT INTO loyalty_branches (id, pharmacy_id, code, name, created_at, updated_at)
VALUES (?, ?, ?, ?, ?, ?)
```

### 7. Fixed delete_pharmacy() ✅

**Before**:

```php
DELETE FROM loyalty_pharmacies WHERE warehouse_id = ?
DELETE FROM sma_warehouses WHERE id = ?
// ERROR: warehouse_id doesn't exist, shouldn't delete warehouse!
```

**After**: Only deletes the loyalty record:

```php
DELETE FROM loyalty_pharmacies WHERE id = ?
// Loyalty pharmacies are INDEPENDENT records, not tied to warehouses
```

### 8. Fixed delete_branch() ✅

**Before**:

```php
DELETE FROM loyalty_branches WHERE warehouse_id = ?
DELETE FROM sma_warehouses WHERE id = ?
// ERROR: warehouse_id doesn't exist, shouldn't delete warehouse!
```

**After**: Only deletes the loyalty record:

```php
DELETE FROM loyalty_branches WHERE id = ?
// Loyalty branches are INDEPENDENT records, not tied to warehouses
```

## Architecture Understanding

The loyalty system is **INDEPENDENT** from sma_warehouses:

```
┌─────────────────────────────────────────┐
│  Loyalty System (Independent)           │
├─────────────────────────────────────────┤
│                                         │
│  loyalty_pharmacy_groups                │
│  ├── id, code, name                     │
│                                         │
│  loyalty_pharmacies                     │
│  ├── id, code, name                     │
│  ├── pharmacy_group_id → (FK)           │
│                                         │
│  loyalty_branches                       │
│  ├── id, code, name                     │
│  ├── pharmacy_id → (FK)                 │
│                                         │
└─────────────────────────────────────────┘

  SEPARATE FROM (NOT linked to)

┌─────────────────────────────────────────┐
│  Warehouse System (sma_warehouses)      │
├─────────────────────────────────────────┤
│                                         │
│  sma_warehouses                         │
│  ├── id, code, name                     │
│  ├── warehouse_type                     │
│  ├── parent_id                          │
│                                         │
└─────────────────────────────────────────┘
```

The loyalty module:

- Maintains its own hierarchy independently
- Does NOT reference sma_warehouses
- Stores pharmacy/branch info in loyalty\_\* tables
- Has its own id and foreign key relationships

## Testing Instructions

1. **Navigate to**: http://localhost:8080/avenzur/admin/organization_setup/pharmacy_hierarchy

2. **Test Load**:

   - Page should load the main organization hierarchy view
   - Pharmacy Groups dropdown should populate

3. **Test Select Pharmacy Group**:

   - Click pharmacy group dropdown
   - Select "AVNZR-SOUTH" (or another group)
   - Should see NO MORE 500 error
   - Pharmacies dropdown should populate with pharmacies in that group

4. **Browser Console**:

   - Open F12 → Console
   - Look for AJAX success messages
   - Verify loadPharmacies() completes successfully
   - No JavaScript errors

5. **Test Full Flow**:
   - Select a pharmacy group
   - Select a pharmacy from the populated list
   - Branches should appear (or empty if none exist)
   - Try adding a new pharmacy via modal
   - Try adding a new branch via modal
   - Test delete operations

## Files Modified

- `/app/controllers/admin/Organization_setup.php` - All 8 methods fixed

## Summary of Changes

| Method                | Type | Issue                                     | Fix                                  |
| --------------------- | ---- | ----------------------------------------- | ------------------------------------ |
| get_pharmacy_groups() | GET  | Wrong query builder usage                 | Raw SQL direct query                 |
| get_pharmacies()      | GET  | Non-existent `warehouse_id` JOIN          | Direct query from loyalty_pharmacies |
| get_branches()        | GET  | Non-existent `warehouse_id` JOIN          | Direct query from loyalty_branches   |
| get_hierarchy_tree()  | GET  | Complex incorrect JOINs                   | Direct queries with proper FKs       |
| add_pharmacy()        | POST | Non-existent column in INSERT             | Generate UUID, use correct columns   |
| add_branch()          | POST | Non-existent column in INSERT             | Generate UUID, use correct columns   |
| delete_pharmacy()     | POST | Non-existent column in WHERE, wrong logic | Delete only loyalty record           |
| delete_branch()       | POST | Non-existent column in WHERE, wrong logic | Delete only loyalty record           |

## Key Learnings

1. **Loyalty module is independent** - Not tied to sma_warehouses
2. **Each loyalty table is self-contained** with its own id field
3. **Foreign keys are between loyalty tables** (not to warehouses)
4. **DELETE should not touch warehouses** - They're separate systems
5. **Raw SQL queries are necessary** for loyalty tables since they don't follow the sma\_ prefix pattern

## Verification Queries

```sql
-- Verify group exists
SELECT * FROM loyalty_pharmacy_groups LIMIT 5;

-- Verify pharmacies in group
SELECT id, code, name FROM loyalty_pharmacies
WHERE pharmacy_group_id = 'c1ddbc5c-8c80-4519-be06-e15bee2bc441';

-- Verify branches in pharmacy
SELECT id, code, name FROM loyalty_branches
WHERE pharmacy_id = '4e16a26a-1de4-421e-a881-d0f365c1337b';

-- Full hierarchy check
SELECT COUNT(*) as group_count FROM loyalty_pharmacy_groups;
SELECT COUNT(*) as pharmacy_count FROM loyalty_pharmacies;
SELECT COUNT(*) as branch_count FROM loyalty_branches;
```

## Performance Notes

- Queries are optimized with WHERE clauses
- Use parameterized queries (prevents SQL injection)
- No unnecessary JOINs or subqueries
- Direct lookups by id (indexed fields)
