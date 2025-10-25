# External ID Mapping - Loyalty & Warehouse Synchronization

## Overview

The `external_id` field in loyalty tables now links directly to corresponding warehouse IDs in `sma_warehouses`. This creates a bidirectional relationship between the loyalty system and warehouse system.

## Database Schema

### Loyalty Tables with External ID

#### loyalty_pharmacy_groups

```sql
- id (UUID) - Primary key
- code (VARCHAR, unique)
- name (VARCHAR)
- company_id (UUID)
- external_id (INT) - Foreign key to sma_warehouses.id (links to first pharmacy warehouse in group)
- created_at (TIMESTAMP)
- updated_at (TIMESTAMP)
```

#### loyalty_pharmacies

```sql
- id (UUID) - Primary key
- code (VARCHAR, unique)
- name (VARCHAR)
- pharmacy_group_id (UUID) - Foreign key to loyalty_pharmacy_groups
- external_id (INT) - Foreign key to sma_warehouses.id (links to pharmacy warehouse)
- created_at (TIMESTAMP)
- updated_at (TIMESTAMP)
```

#### loyalty_branches

```sql
- id (VARCHAR(36)) - Primary key
- code (VARCHAR, unique)
- name (VARCHAR)
- pharmacy_id (VARCHAR(36)) - Foreign key to loyalty_pharmacies
- external_id (INT) - Foreign key to sma_warehouses.id (links to branch warehouse)
- created_at (TIMESTAMP)
- updated_at (TIMESTAMP)
```

## External ID Mapping Completed

### Status

✅ **All external IDs populated** (Date: October 24, 2025)

### Summary

| Entity          | Count | With External ID | Coverage |
| --------------- | ----- | ---------------- | -------- |
| Pharmacy Groups | 4     | 4                | 100%     |
| Pharmacies      | 6     | 6                | 100%     |
| Branches        | 10    | 10               | 100%     |

## Mapping Details

### Pharmacy Groups → Warehouses

Each group's `external_id` points to the minimum warehouse ID of its first pharmacy warehouse.

```
AVNZR-NORTH (Group)
└── external_id: 55
    └── Avenzur Downtown Pharmacy (PHR-001)

AVNZR-SOUTH (Group)
└── external_id: 57
    └── Avenzur Southside Pharmacy (PHR-003)

EANDM-CENTRAL (Group)
└── external_id: 52
    └── E&M Central Plaza Pharmacy (PHR-004)

HLTHP-METRO (Group)
└── external_id: 53
    └── HealthPlus Main Street Pharmacy (PHR-006)
```

### Pharmacies → Warehouses

Each pharmacy's `external_id` directly points to its corresponding pharmacy warehouse ID.

```
PHR-001: Avenzur Downtown Pharmacy
└── external_id: 55 (sma_warehouses.id)
    └── warehouse_type: 'pharmacy'

PHR-002: Avenzur Northgate Pharmacy
└── external_id: 56 (sma_warehouses.id)
    └── warehouse_type: 'pharmacy'

PHR-003: Avenzur Southside Pharmacy
└── external_id: 57 (sma_warehouses.id)
    └── warehouse_type: 'pharmacy'

PHR-004: E&M Central Plaza Pharmacy
└── external_id: 52 (sma_warehouses.id)
    └── warehouse_type: 'pharmacy'

PHR-005: E&M Midtown Pharmacy
└── external_id: 54 (sma_warehouses.id)
    └── warehouse_type: 'pharmacy'

PHR-006: HealthPlus Main Street Pharmacy
└── external_id: 53 (sma_warehouses.id)
    └── warehouse_type: 'pharmacy'
```

### Branches → Warehouses

Each branch's `external_id` directly points to its corresponding branch warehouse ID with proper parent linking.

```
BR-001-01: Avenzur Downtown - Main Branch
├── external_id: 59 (sma_warehouses.id)
├── warehouse_type: 'branch'
└── parent_id: 55 (Avenzur Downtown Pharmacy)

BR-001-02: Avenzur Downtown - Express Branch
├── external_id: 65 (sma_warehouses.id)
├── warehouse_type: 'branch'
└── parent_id: 55 (Avenzur Downtown Pharmacy)

BR-002-01: Avenzur Northgate - Main Branch
├── external_id: 63 (sma_warehouses.id)
├── warehouse_type: 'branch'
└── parent_id: 56 (Avenzur Northgate Pharmacy)

BR-003-01: Avenzur Southside - Main Branch
├── external_id: 62 (sma_warehouses.id)
├── warehouse_type: 'branch'
└── parent_id: 57 (Avenzur Southside Pharmacy)

BR-003-02: Avenzur Southside - Mall Branch
├── external_id: 60 (sma_warehouses.id)
├── warehouse_type: 'branch'
└── parent_id: 57 (Avenzur Southside Pharmacy)

BR-004-01: E&M Central Plaza - Main Branch
├── external_id: 68 (sma_warehouses.id)
├── warehouse_type: 'branch'
└── parent_id: 52 (E&M Central Plaza Pharmacy)

BR-005-01: E&M Midtown - Main Branch
├── external_id: 61 (sma_warehouses.id)
├── warehouse_type: 'branch'
└── parent_id: 54 (E&M Midtown Pharmacy)

BR-005-02: E&M Midtown - 24/7 Branch
├── external_id: 67 (sma_warehouses.id)
├── warehouse_type: 'branch'
└── parent_id: 54 (E&M Midtown Pharmacy)

BR-006-01: HealthPlus Main Street - Main Branch
├── external_id: 64 (sma_warehouses.id)
├── warehouse_type: 'branch'
└── parent_id: 53 (HealthPlus Main Street Pharmacy)

BR-006-02: HealthPlus Main Street - Drive-Thru Branch
├── external_id: 66 (sma_warehouses.id)
├── warehouse_type: 'branch'
└── parent_id: 53 (HealthPlus Main Street Pharmacy)
```

## Query Patterns

### Get loyalty data with warehouse details

```sql
-- Get pharmacy with warehouse info
SELECT lp.id, lp.code, lp.name, lp.pharmacy_group_id,
       lp.external_id,
       sw.id as warehouse_id, sw.name as warehouse_name, sw.warehouse_type
FROM loyalty_pharmacies lp
LEFT JOIN sma_warehouses sw ON lp.external_id = sw.id;

-- Get branch with pharmacy and warehouse info
SELECT lb.id, lb.code, lb.name, lb.pharmacy_id,
       lb.external_id,
       sw.id as warehouse_id, sw.name as warehouse_name,
       sw.parent_id as parent_warehouse_id,
       parent_sw.name as parent_warehouse_name
FROM loyalty_branches lb
LEFT JOIN sma_warehouses sw ON lb.external_id = sw.id
LEFT JOIN sma_warehouses parent_sw ON sw.parent_id = parent_sw.id;
```

### Get warehouse data with loyalty info

```sql
-- Get pharmacy warehouse with loyalty info
SELECT sw.id, sw.code, sw.name, sw.warehouse_type,
       lp.id as loyalty_id, lp.name as loyalty_name
FROM sma_warehouses sw
LEFT JOIN loyalty_pharmacies lp ON sw.id = lp.external_id
WHERE sw.warehouse_type = 'pharmacy';

-- Get branch warehouse with loyalty info
SELECT sw.id, sw.code, sw.name, sw.warehouse_type, sw.parent_id,
       lb.id as loyalty_id, lb.name as loyalty_name
FROM sma_warehouses sw
LEFT JOIN loyalty_branches lb ON sw.id = lb.external_id
WHERE sw.warehouse_type = 'branch';
```

## Controller Integration

### When Adding a Pharmacy

```php
// 1. Create sma_warehouses entry
$pharmacy_warehouse_id = $this->db->insert_id();

// 2. Create loyalty_pharmacies with external_id
$loyalty_pharmacy_data = [
    'id' => $this->sma->generate_uuid(),
    'pharmacy_group_id' => $group_id,
    'name' => $name,
    'code' => $code,
    'external_id' => $pharmacy_warehouse_id,  // Link to warehouse
    'created_at' => date('Y-m-d H:i:s'),
    'updated_at' => date('Y-m-d H:i:s')
];

$this->db->query(
    "INSERT INTO loyalty_pharmacies (..., external_id, ...) VALUES (..., ?, ...)",
    [..., $pharmacy_warehouse_id, ...]
);
```

### When Adding a Branch

```php
// 1. Create sma_warehouses entry
$branch_warehouse_id = $this->db->insert_id();

// 2. Create loyalty_branches with external_id
$loyalty_branch_data = [
    'id' => $this->sma->generate_uuid(),
    'pharmacy_id' => $pharmacy_id,
    'code' => $code,
    'name' => $name,
    'external_id' => $branch_warehouse_id,  // Link to warehouse
    'created_at' => date('Y-m-d H:i:s'),
    'updated_at' => date('Y-m-d H:i:s')
];

$this->db->query(
    "INSERT INTO loyalty_branches (..., external_id, ...) VALUES (..., ?, ...)",
    [..., $branch_warehouse_id, ...]
);
```

### When Updating Loyalty Data

```php
// Retrieve warehouse info via external_id
$warehouse = $this->db->query(
    "SELECT id, name, address, phone FROM sma_warehouses WHERE id = ?",
    [$loyalty_record->external_id]
)->row();

// Sync updates between systems
if ($warehouse->name !== $loyalty_record->name) {
    // Decide which system of record to use
    $this->db->update('sma_warehouses',
        ['name' => $loyalty_record->name],
        ['id' => $loyalty_record->external_id]
    );
}
```

## Consistency Checks

### Verify all external IDs

```sql
SELECT entity, total, with_external_id,
       (with_external_id / total * 100) as coverage_pct
FROM (
    SELECT 'Groups' as entity, COUNT(*) as total,
           SUM(CASE WHEN external_id IS NOT NULL THEN 1 ELSE 0 END) as with_external_id
    FROM loyalty_pharmacy_groups
    UNION ALL
    SELECT 'Pharmacies', COUNT(*),
           SUM(CASE WHEN external_id IS NOT NULL THEN 1 ELSE 0 END)
    FROM loyalty_pharmacies
    UNION ALL
    SELECT 'Branches', COUNT(*),
           SUM(CASE WHEN external_id IS NOT NULL THEN 1 ELSE 0 END)
    FROM loyalty_branches
) stats;
```

### Find orphaned records

```sql
-- Pharmacies with invalid external_id
SELECT lp.id, lp.code, lp.name, lp.external_id
FROM loyalty_pharmacies lp
LEFT JOIN sma_warehouses sw ON lp.external_id = sw.id
WHERE sw.id IS NULL AND lp.external_id IS NOT NULL;

-- Branches with invalid external_id
SELECT lb.id, lb.code, lb.name, lb.external_id
FROM loyalty_branches lb
LEFT JOIN sma_warehouses sw ON lb.external_id = sw.id
WHERE sw.id IS NULL AND lb.external_id IS NOT NULL;
```

## Files Updated

1. **Database**

   - `update_external_ids.sql` - SQL script for populating external IDs

2. **Controller**
   - `/app/controllers/admin/Organization_setup.php`
     - `add_pharmacy()` - Now sets external_id
     - `add_branch()` - Now sets external_id
     - `delete_pharmacy()` - Deletes from both loyalty and warehouse
     - `delete_branch()` - Deletes from both loyalty and warehouse

## Benefits

✅ **Bidirectional Navigation** - Can go from loyalty to warehouse and vice versa  
✅ **Data Consistency** - Single source of truth for IDs  
✅ **Query Efficiency** - Direct ID lookup instead of code matching  
✅ **Referential Integrity** - Foreign key relationships maintained  
✅ **System Integration** - Both systems can operate independently with proper linking

## Performance Impact

- **Query Optimization**: Direct integer ID lookups are faster than string code matching
- **Index Strategy**: external_id field should be indexed for fast joins
- **Storage**: Minimal overhead (4 bytes per INT field)

## Migration Notes

- All 20 existing records (4 groups + 6 pharmacies + 10 branches) have been populated
- New records created via the application will automatically get external_id set
- Code-based linking is no longer necessary for new records
- Backward compatibility maintained for code-based queries

## Verification Status

✅ All 4 pharmacy groups have external_id (100% coverage)  
✅ All 6 pharmacies have external_id (100% coverage)  
✅ All 10 branches have external_id (100% coverage)  
✅ All external_id values reference valid sma_warehouses records  
✅ All warehouse relationships verified and correct

---

**Completion Date**: October 24, 2025  
**Status**: ✅ COMPLETE
