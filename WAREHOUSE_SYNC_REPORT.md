# Warehouse Sync Completion Report

## Sync Summary

Successfully synced `sma_warehouses` table with existing loyalty data.

### Data Summary

| Entity                           | Count |
| -------------------------------- | ----- |
| **Loyalty Groups**               | 4     |
| **Loyalty Pharmacies**           | 6     |
| **Loyalty Branches**             | 10    |
| **Total Loyalty Records**        | 20    |
|                                  |       |
| **Total Warehouses**             | 19    |
| **Pharmacy Warehouses (synced)** | 6     |
| **Branch Warehouses (synced)**   | 10    |
| **Other Warehouses (existing)**  | 3     |

### Pharmacy Warehouses Created

| ID  | Code    | Name                            | Type     |
| --- | ------- | ------------------------------- | -------- |
| 55  | PHR-001 | Avenzur Downtown Pharmacy       | pharmacy |
| 56  | PHR-002 | Avenzur Northgate Pharmacy      | pharmacy |
| 57  | PHR-003 | Avenzur Southside Pharmacy      | pharmacy |
| 52  | PHR-004 | E&M Central Plaza Pharmacy      | pharmacy |
| 54  | PHR-005 | E&M Midtown Pharmacy            | pharmacy |
| 53  | PHR-006 | HealthPlus Main Street Pharmacy | pharmacy |

### Branch Warehouses Created

| ID  | Code      | Name                                       | Parent ID | Parent Name                     |
| --- | --------- | ------------------------------------------ | --------- | ------------------------------- |
| 59  | BR-001-01 | Avenzur Downtown - Main Branch             | 55        | Avenzur Downtown Pharmacy       |
| 65  | BR-001-02 | Avenzur Downtown - Express Branch          | 55        | Avenzur Downtown Pharmacy       |
| 63  | BR-002-01 | Avenzur Northgate - Main Branch            | 56        | Avenzur Northgate Pharmacy      |
| 62  | BR-003-01 | Avenzur Southside - Main Branch            | 57        | Avenzur Southside Pharmacy      |
| 60  | BR-003-02 | Avenzur Southside - Mall Branch            | 57        | Avenzur Southside Pharmacy      |
| 68  | BR-004-01 | E&M Central Plaza - Main Branch            | 52        | E&M Central Plaza Pharmacy      |
| 61  | BR-005-01 | E&M Midtown - Main Branch                  | 54        | E&M Midtown Pharmacy            |
| 67  | BR-005-02 | E&M Midtown - 24/7 Branch                  | 54        | E&M Midtown Pharmacy            |
| 64  | BR-006-01 | HealthPlus Main Street - Main Branch       | 53        | HealthPlus Main Street Pharmacy |
| 66  | BR-006-02 | HealthPlus Main Street - Drive-Thru Branch | 53        | HealthPlus Main Street Pharmacy |

## Two System Architecture

Now we have a dual-system approach:

### Loyalty System (Independent)

```
loyalty_pharmacy_groups
├── 4 groups (AVNZR-SOUTH, EANDM-CENTRAL, AVNZR-NORTH, HLTHP-METRO)

loyalty_pharmacies
├── 6 pharmacies with UUID IDs
├── Linked to groups via pharmacy_group_id
└── Independent data model

loyalty_branches
├── 10 branches with UUID IDs
├── Linked to pharmacies via pharmacy_id
└── Independent data model
```

### Warehouse System (sma_warehouses)

```
sma_warehouses (auto-increment integer IDs)
├── pharmacy warehouses (6) - type = 'pharmacy'
│   ├── linked to loyalty_pharmacies by code
│   └── parent_id = NULL
├── branch warehouses (10) - type = 'branch'
│   ├── linked to loyalty_branches by code
│   ├── parent_id = pharmacy warehouse ID
│   └── Creates hierarchy structure
└── other warehouses (3) - existing stock/transit
```

## Database Relationships

```
Loyalty Tables                  Warehouse Table
──────────────────             ───────────────

loyalty_pharmacy_groups
    ↓
loyalty_pharmacies ←────────→ sma_warehouses (type=pharmacy)
    ↓
loyalty_branches ←──────────→ sma_warehouses (type=branch)
                              ↓
                           parent_id references pharmacy warehouse
```

## Key Mappings

### Pharmacy Warehouse Links

- Code matches between `loyalty_pharmacies.code` and `sma_warehouses.code`
- Example: PHR-001 in both tables

### Branch Warehouse Links

- Code matches between `loyalty_branches.code` and `sma_warehouses.code`
- Parent ID links branch warehouse to pharmacy warehouse via `pharmacy_id`

## Usage in Application

### When Adding a Pharmacy

1. Create entry in `loyalty_pharmacies` (UUID)
2. Create corresponding entry in `sma_warehouses` (auto-increment)
3. Link them by matching codes

### When Adding a Branch

1. Create entry in `loyalty_branches` (UUID, with pharmacy_id FK)
2. Create corresponding entry in `sma_warehouses` (auto-increment, type=branch)
3. Set warehouse parent_id to matching pharmacy warehouse ID

### Querying Data

**Get all pharmacies with warehouse info:**

```sql
SELECT lp.*, sw.id as warehouse_id, sw.address
FROM loyalty_pharmacies lp
LEFT JOIN sma_warehouses sw ON lp.code = sw.code AND sw.warehouse_type = 'pharmacy';
```

**Get branches with parent pharmacy:**

```sql
SELECT lb.*, sw.id as warehouse_id, parent_sw.id as pharmacy_warehouse_id, parent_sw.name as pharmacy_name
FROM loyalty_branches lb
LEFT JOIN loyalty_pharmacies lp ON lb.pharmacy_id = lp.id
LEFT JOIN sma_warehouses sw ON lb.code = sw.code AND sw.warehouse_type = 'branch'
LEFT JOIN sma_warehouses parent_sw ON sw.parent_id = parent_sw.id;
```

## Controller Updates Needed

The `Organization_setup` controller should be updated to:

1. **In add_pharmacy()** - Create warehouse entry after creating loyalty pharmacy

   ```php
   // After inserting into loyalty_pharmacies:
   $warehouse_data = [
       'code' => $code,
       'name' => $name,
       'address' => $address,
       'warehouse_type' => 'pharmacy',
       'country' => 1,
       'parent_id' => NULL
   ];
   $this->db->insert('sma_warehouses', $warehouse_data);
   ```

2. **In add_branch()** - Create warehouse entry after creating loyalty branch

   ```php
   // Get pharmacy warehouse ID:
   $pharmacy_wh = $this->db->query(
       "SELECT id FROM sma_warehouses WHERE code = ? AND warehouse_type = 'pharmacy'",
       [$pharmacy_code]
   )->row();

   // Create branch warehouse:
   $branch_warehouse_data = [
       'code' => $code,
       'name' => $name,
       'address' => $address,
       'warehouse_type' => 'branch',
       'country' => 1,
       'parent_id' => $pharmacy_wh->id
   ];
   $this->db->insert('sma_warehouses', $branch_warehouse_data);
   ```

3. **In delete_pharmacy()** - Delete both loyalty and warehouse records

   ```php
   $this->db->query("DELETE FROM loyalty_pharmacies WHERE id = ?", [$id]);
   $this->db->query("DELETE FROM sma_warehouses WHERE warehouse_type = 'pharmacy' AND code = ?", [$code]);
   ```

4. **In delete_branch()** - Delete both loyalty and warehouse records
   ```php
   $this->db->query("DELETE FROM loyalty_branches WHERE id = ?", [$id]);
   $this->db->query("DELETE FROM sma_warehouses WHERE warehouse_type = 'branch' AND code = ?", [$code]);
   ```

## Verification Queries

```sql
-- Verify sync integrity
SELECT 'Loyalty Pharmacies' as entity, COUNT(*) as count FROM loyalty_pharmacies
UNION ALL
SELECT 'Warehouse Pharmacies' as entity, COUNT(*) FROM sma_warehouses WHERE warehouse_type = 'pharmacy';

-- Verify all branches have parent warehouses
SELECT lb.code, lb.name, sw.id, sw.parent_id, sw2.name as parent_warehouse_name
FROM loyalty_branches lb
LEFT JOIN sma_warehouses sw ON lb.code = sw.code AND sw.warehouse_type = 'branch'
LEFT JOIN sma_warehouses sw2 ON sw.parent_id = sw2.id
ORDER BY sw.parent_id;

-- Identify any orphaned branches (no parent pharmacy warehouse)
SELECT lb.code, lb.name
FROM loyalty_branches lb
LEFT JOIN loyalty_pharmacies lp ON lb.pharmacy_id = lp.id
WHERE lp.code NOT IN (SELECT code FROM sma_warehouses WHERE warehouse_type = 'pharmacy');
```

## Benefits of Dual System

✅ **Loyalty Independence** - Loyalty data maintained separately in its own schema  
✅ **Warehouse Integration** - Pharmacy/branch hierarchy available for warehouse operations  
✅ **Code Linkage** - Simple code-based linking between systems  
✅ **Hierarchy Support** - Parent-child relationships in warehouse system  
✅ **Backward Compatibility** - Existing warehouse system continues to work  
✅ **Scalability** - Can add more warehouse-specific features independently

## Sync Completed Date

**Date**: October 24, 2025  
**Records Synced**: 16 (6 pharmacies + 10 branches)  
**Execution Time**: < 1 second  
**Status**: ✅ SUCCESS
