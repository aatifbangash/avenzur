# Delivery Module Implementation - Blue Theme

## Summary of Changes

### 1. Database Schema (`create_delivery_table.sql`)

- **sma_deliveries**: Modified with ALTER TABLE to add necessary columns for delivery management

  - date_string, driver_name, truck_number, status
  - total_items_in_delivery_package, out_time, odometer
  - total_refrigerated_items, assigned_by
  - created_at, updated_at timestamps with indexes

- **sma_delivery_items**: New mapping table for invoices to deliveries

  - Links deliveries to sales invoices (many-to-many)
  - Tracks quantity_items and refrigerated_items per invoice

- **sma_delivery_prints**: Audit log for delivery print history

  - Records who printed, when, and how many copies
  - Links to delivery and users tables

- **sma_delivery_audit_logs**: Complete audit trail
  - Tracks all actions (created, updated, deleted, printed)
  - Records user who performed action and timestamp

### 2. Model (`app/models/admin/Delivery_model.php`)

Complete CRUD model with:

- `add_delivery()` - Create delivery with multiple invoices
- `get_all_deliveries()` - List all with filters
- `get_delivery_by_id()` - Get specific delivery
- `get_delivery_items()` - Get invoices in delivery
- `update_delivery()` - Modify delivery info
- `update_delivery_status()` - Change status
- `add_items_to_delivery()` - Add more invoices
- `remove_item_from_delivery()` - Remove specific invoice
- `delete_delivery()` - Remove delivery
- `log_delivery_print()` - Record print action
- `get_delivery_print_history()` - Print audit trail
- `get_delivery_audit_logs()` - Full audit history
- `get_delivery_status_count()` - Dashboard stats
- `search_deliveries()` - Search functionality
- `get_deliveries_by_driver()` - Filter by driver

### 3. Controller (`app/controllers/admin/Delivery.php`)

Full REST-like controller with:

- `index()` - List deliveries
- `get_deliveries()` - AJAX DataTable endpoint
- `add()` - Create form
- `save()` - Save new delivery
- `edit()` - Edit form
- `update()` - Update delivery
- `view()` - View details with audit logs
- `update_status()` - AJAX status update
- `add_items()` - AJAX add invoices
- `remove_item()` - AJAX remove invoice
- `print_delivery()` - Print delivery note
- `pdf()` - PDF export
- `delete()` - Delete delivery
- `get_statistics()` - Dashboard stats
- `search()` - Search deliveries

### 4. Views in Blue Theme (`themes/blue/admin/views/delivery/`)

#### index.php

- DataTable list with sorting and filtering
- Date, driver, truck, status filters
- Action buttons: View, Edit, Print, Delete

#### add.php

- Form for creating new delivery
- Driver name and truck number (required)
- Date picker, status selector
- Invoice selection table with multi-select checkbox
- Refrigerated items counter per invoice

#### edit.php

- Modify delivery information
- View current invoices with remove button
- Add additional invoices to existing delivery
- Bulk invoice selection

#### view.php

- Complete delivery details display
- List of all invoices with amounts
- Action buttons: Edit, Print, Mark Out for Delivery, Mark Completed
- Print history sidebar
- Complete audit log with user and timestamp

#### print.php

- Professional print-ready delivery note
- Delivery information header
- Invoice summary table with totals
- Signature fields (driver, receiver, date/time)
- Auto-print trigger

#### pdf.php

- PDF-ready delivery note format
- Same layout as print version
- Company header and document ID
- Complete invoice list with amounts
- Footer with timestamp and printed by user

### 5. Header Navigation Updates

#### Blue Theme Header (`themes/blue/admin/views/header.php`)

- Added new top-level "Deliveries" menu item with truck icon
- Two instances for different responsive breakpoints:
  1. First menu section (lines 769-787)
  2. Second menu section (lines 1751-1769)
- Submenu items:
  - List Deliveries → `admin_url('delivery')`
  - Add Delivery → `admin_url('delivery/add')`

## File Structure Created

```
/themes/blue/admin/views/delivery/
├── index.html (security file)
├── index.php (list view)
├── add.php (create form)
├── edit.php (edit form)
├── view.php (detail view with audit)
├── print.php (print-ready note)
└── pdf.php (PDF export)
```

## Features

✅ Multi-invoice packaging into single delivery
✅ Driver and truck assignment
✅ Status management (pending, assigned, out for delivery, completed)
✅ Professional print/PDF generation
✅ Complete audit logging of all actions
✅ Print history tracking with user attribution
✅ Refrigerated items tracking
✅ Odometer reading recording
✅ Out-time tracking
✅ AJAX operations for dynamic updates
✅ Comprehensive filtering and search
✅ Bootstrap-styled UI for blue theme

## Database Execution

Run the `create_delivery_table.sql` script to:

1. Alter sma_deliveries table with new columns
2. Create sma_delivery_items table
3. Create sma_delivery_prints table
4. Create sma_delivery_audit_logs table

All tables include proper indexes for performance and foreign key constraints for data integrity.
