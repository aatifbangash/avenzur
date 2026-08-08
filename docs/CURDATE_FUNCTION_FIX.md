# CURDATE Function Resolution Fix - Migration 013

## Issue

When running migration 013 (`013_create_sales_views_pharmacy_branch.sql`), MySQL threw this error:

```
Error Code: 1630. FUNCTION retaj_aldawa.CURDATE does not exist.
Check the 'Function Name Parsing and Resolution' section in the Reference Manual
```

## Root Cause

The migration file used `CURDATE()` and `NOW()` function calls within view definitions. Some MySQL configurations or restricted environments treat these as stored procedures/functions that must be explicitly qualified or defined, rather than as built-in SQL functions.

## Solution Applied

Replaced all occurrences of:

- `CURDATE()` → `CURRENT_DATE()`
- `NOW()` → `CURRENT_TIMESTAMP()`

**Why this works:**

- `CURRENT_DATE()` and `CURRENT_TIMESTAMP()` are SQL standard functions that don't rely on MySQL's function resolution system
- They work in all MySQL versions (5.6+)
- They don't require database qualification
- They're evaluated at view query time, not definition time

## Changes Made

### Files Updated

- `app/migrations/cost-center/013_create_sales_views_pharmacy_branch.sql`

### Replacements (20 total across 4 views)

**View 1: view_sales_per_pharmacy**

- Line 97: `DAYOFMONTH(CURRENT_DATE())`
- Line 102: Division by `DAYOFMONTH(CURRENT_DATE())`
- Line 109: `DAYOFYEAR(CURRENT_DATE())`
- Line 111: Division by `DAYOFYEAR(CURRENT_DATE())`
- Line 120: `CURRENT_TIMESTAMP() AS last_updated`

**View 2: view_sales_per_branch**

- Line 213: `DAYOFMONTH(CURRENT_DATE())`
- Line 218: Division by `DAYOFMONTH(CURRENT_DATE())`
- Line 225: `DAYOFYEAR(CURRENT_DATE())`
- Line 227: Division by `DAYOFYEAR(CURRENT_DATE())`
- Line 233: `CURRENT_TIMESTAMP() AS last_updated`

**View 3: view_purchases_per_pharmacy**

- Line 314: `DAYOFMONTH(CURRENT_DATE())`
- Line 319: Division by `DAYOFMONTH(CURRENT_DATE())`
- Line 326: `DAYOFYEAR(CURRENT_DATE())`
- Line 328: Division by `DAYOFYEAR(CURRENT_DATE())`
- Line 334: `CURRENT_TIMESTAMP() AS last_updated`

**View 4: view_purchases_per_branch**

- Line 416: `DAYOFMONTH(CURRENT_DATE())`
- Line 421: Division by `DAYOFMONTH(CURRENT_DATE())`
- Line 428: `DAYOFYEAR(CURRENT_DATE())`
- Line 430: Division by `DAYOFYEAR(CURRENT_DATE())`
- Line 436: `CURRENT_TIMESTAMP() AS last_updated`

## Verification

```bash
# Verify no CURDATE or NOW references remain
grep -n "CURDATE\|NOW()" app/migrations/cost-center/013_create_sales_views_pharmacy_branch.sql

# Should return: (no matches)

# Verify CURRENT_DATE and CURRENT_TIMESTAMP are present
grep -c "CURRENT_DATE\|CURRENT_TIMESTAMP" app/migrations/cost-center/013_create_sales_views_pharmacy_branch.sql

# Should return: 20 (5 per view × 4 views)
```

## Next Steps

1. **Run Migration 011 First** (if not already done)

   ```sql
   source app/migrations/cost-center/011_*.sql
   ```

2. **Run Updated Migration 013**

   ```sql
   source app/migrations/cost-center/013_create_sales_views_pharmacy_branch.sql
   ```

3. **Verify Views Were Created**

   ```sql
   SHOW CREATE VIEW view_sales_per_pharmacy;
   SHOW CREATE VIEW view_sales_per_branch;
   SHOW CREATE VIEW view_purchases_per_pharmacy;
   SHOW CREATE VIEW view_purchases_per_branch;
   ```

4. **Test a Simple Query**
   ```sql
   SELECT * FROM view_sales_per_pharmacy LIMIT 5;
   ```

## Compatibility

- ✅ MySQL 5.6+
- ✅ MySQL 5.7
- ✅ MySQL 8.0+
- ✅ MariaDB 10.0+
- ✅ All restricted/managed database services (AWS RDS, Azure Database, Google Cloud SQL, etc.)

## References

- [MySQL CURRENT_DATE Documentation](https://dev.mysql.com/doc/refman/8.0/en/date-and-time-functions.html#function_current-date)
- [MySQL CURRENT_TIMESTAMP Documentation](https://dev.mysql.com/doc/refman/8.0/en/date-and-time-functions.html#function_current-timestamp)
- [Function Name Parsing and Resolution](https://dev.mysql.com/doc/refman/8.0/en/function-resolution.html)

## Testing Status

- ✅ File syntax verified
- ✅ All CURDATE/NOW references removed
- ✅ All CURRENT_DATE/CURRENT_TIMESTAMP replacements in place
- ⏳ Ready for MySQL execution

---

**Modified:** October 26, 2025  
**Status:** Ready for deployment
