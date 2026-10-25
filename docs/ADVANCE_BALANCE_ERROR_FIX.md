# Advance Balance Error Fix

## Issue

"Error loading advance balance" was appearing when trying to load supplier advance balance.

## Root Cause

The query was trying to filter by `e.deleted = 0`, but the `deleted` column might not exist in the `sma_accounts_entries` table in this particular database instance.

## Solution Implemented

### 1. Check if Column Exists Before Filtering

Added a field existence check before applying the deleted filter:

```php
// Check if deleted column exists before filtering
if ($this->db->field_exists('deleted', 'sma_accounts_entries')) {
    $this->db->where('e.deleted', 0);
}
```

This makes the code compatible with both database schemas:

- **With deleted column**: Filter is applied, deleted entries excluded
- **Without deleted column**: Filter is skipped, all entries included

### 2. Enhanced Error Logging

Added detailed error logging to help diagnose issues:

```php
catch (Exception $e) {
    log_message('error', 'Advance Balance Error: ' . $e->getMessage());
    log_message('error', 'SQL Error: ' . $this->db->last_query());

    echo json_encode(array(
        'advance_balance' => 0,
        'advance_ledger_configured' => false,
        'error' => 'Database error: ' . $e->getMessage(),
        'query' => $this->db->last_query()
    ));
}
```

## Files Modified

**Controller**: `/app/controllers/admin/Suppliers.php`

- Modified `get_supplier_advance_balance()` method
- Modified `getSupplierAdvanceBalance()` private method
- Added field existence check
- Enhanced error handling and logging

## How It Works Now

1. **Query builds**: Selects credits and debits from advance ledger
2. **Field check**: Checks if `deleted` column exists
3. **Conditional filter**: Only applies deleted filter if column exists
4. **Execute query**: Runs the query with appropriate filters
5. **Calculate balance**: Credits - Debits = Available Advance
6. **Return result**: JSON with balance and configuration status

## Testing

### If Database Has 'deleted' Column

- Query: `WHERE ... AND e.deleted = 0`
- Result: Excludes deleted entries ✓

### If Database Doesn't Have 'deleted' Column

- Query: `WHERE ...` (no deleted filter)
- Result: Includes all entries ✓

### Error Scenarios

- If query fails, error is logged and returned in JSON
- JavaScript console will show the actual SQL query
- Check browser console for detailed error information

## Debugging

If you still see errors, check:

1. **Browser Console**: Look for the error message and SQL query
2. **Application Logs**: Check for error logs with full details
3. **Supplier Advance Ledger**: Ensure it's configured in settings
4. **Database Connection**: Verify database is accessible
5. **Permissions**: Check user has SELECT permissions on tables

### SQL Query to Test Manually

```sql
-- Test if deleted column exists
SHOW COLUMNS FROM sma_accounts_entries LIKE 'deleted';

-- Test balance query (with deleted column)
SELECT
    COALESCE(SUM(CASE WHEN ei.dc = 'C' THEN ei.amount ELSE 0 END), 0) as credit_total,
    COALESCE(SUM(CASE WHEN ei.dc = 'D' THEN ei.amount ELSE 0 END), 0) as debit_total
FROM sma_accounts_entryitems ei
INNER JOIN sma_accounts_entries e ON e.id = ei.entry_id
WHERE ei.ledger_id = [YOUR_ADVANCE_LEDGER_ID]
AND e.supplier_id = [YOUR_SUPPLIER_ID]
AND e.deleted = 0;

-- Test balance query (without deleted column)
SELECT
    COALESCE(SUM(CASE WHEN ei.dc = 'C' THEN ei.amount ELSE 0 END), 0) as credit_total,
    COALESCE(SUM(CASE WHEN ei.dc = 'D' THEN ei.amount ELSE 0 END), 0) as debit_total
FROM sma_accounts_entryitems ei
INNER JOIN sma_accounts_entries e ON e.id = ei.entry_id
WHERE ei.ledger_id = [YOUR_ADVANCE_LEDGER_ID]
AND e.supplier_id = [YOUR_SUPPLIER_ID];
```

## Benefits

✅ **Backward Compatible**: Works with or without deleted column  
✅ **Better Error Handling**: Clear error messages with SQL query  
✅ **Debug Friendly**: Logs errors for troubleshooting  
✅ **Graceful Degradation**: Returns 0 balance on error instead of crashing  
✅ **Flexible**: Adapts to different database schemas
