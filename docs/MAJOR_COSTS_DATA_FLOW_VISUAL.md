# Major Costs Data Flow - Visual Guide

---

## Complete Data Flow Diagram

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                         USER INPUTS DATE RANGE                              │
│                    From: 30/09/2025  To: 25/10/2025                         │
└────────────────────────────┬────────────────────────────────────────────────┘
                             │
                             ▼
┌─────────────────────────────────────────────────────────────────────────────┐
│                    CONTROLLER: Cost_center.php                              │
│  dashboard($from_date, $to_date, $warehouse_ids)                           │
└────────────────────────────┬────────────────────────────────────────────────┘
                             │
                             ▼
┌─────────────────────────────────────────────────────────────────────────────┐
│                MODEL: Cost_center_model.php                                 │
│           get_major_costs($from, $to, $warehouse_ids)                      │
└────────────────────────────┬────────────────────────────────────────────────┘
                             │
        ┌────────────────────┼────────────────────┐
        │                    │                    │
        ▼                    ▼                    ▼
   ┌─────────┐          ┌──────────┐         ┌──────────┐
   │  QUERY  │          │  QUERY   │         │  QUERY   │
   │  COGS   │          │Salaries  │         │ Facility │
   │  Data   │          │  Data    │         │  Costs   │
   └────┬────┘          └────┬─────┘         └────┬─────┘
        │                    │                    │
        │    Joins Tables    │    Sums Data       │
        │    Filters Data    │    Converts       │
        │    Groups Data     │    Caches         │
        │                    │                    │
        ▼                    ▼                    ▼
   ┌─────────────────────────────────────────────────┐
   │     Build Cost Array                            │
   │ [                                               │
   │   {name: 'COGS', amount: 450000},              │
   │   {name: 'Salaries', amount: 180000},          │
   │   {name: 'Rent & Utilities', amount: 80000},   │
   │   ...                                           │
   │ ]                                               │
   └────────────────────┬────────────────────────────┘
                        │
                        ▼
   ┌─────────────────────────────────────────────────┐
   │  CALCULATE PERCENTAGES                          │
   │  Total = Sum of all costs                       │
   │  Percentage = (Cost / Total) × 100              │
   │                                                 │
   │  Example:                                       │
   │  COGS: 450000 / 750000 × 100 = 60%             │
   │  Salaries: 180000 / 750000 × 100 = 24%         │
   │  ...                                            │
   └────────────────────┬────────────────────────────┘
                        │
                        ▼
   ┌─────────────────────────────────────────────────┐
   │  SORT BY AMOUNT (Descending)                    │
   │  1. COGS: 450000 (60%)                          │
   │  2. Salaries: 180000 (24%)                      │
   │  3. Rent & Utilities: 80000 (11%)              │
   │  4. Delivery & Transport: 25000 (3%)            │
   │  5. Marketing: 15000 (2%)                       │
   └────────────────────┬────────────────────────────┘
                        │
                        ▼
   ┌─────────────────────────────────────────────────┐
   │  RETURN TO CONTROLLER                           │
   │  [                                              │
   │    'costs' => [...],                            │
   │    'total' => 750000,                           │
   │    'period' => [from, to]                       │
   │  ]                                              │
   └────────────────────┬────────────────────────────┘
                        │
                        ▼
   ┌─────────────────────────────────────────────────┐
   │  PASS TO VIEW                                   │
   │  $data['major_costs'] = $costs_array            │
   └────────────────────┬────────────────────────────┘
                        │
                        ▼
   ┌─────────────────────────────────────────────────┐
   │  RENDER IN BROWSER                              │
   │  Loop through costs:                            │
   │  - Show name                                    │
   │  - Show amount (SAR)                            │
   │  - Show percentage                              │
   │  - Draw progress bar                            │
   │  - Color-code by percentage                     │
   └─────────────────────────────────────────────────┘
```

---

## Database Query Sequence

### Query 1: Get COGS (Cost of Goods Sold)

```
USER SELECTS: From 30/09/2025 to 25/10/2025
              |
              ▼
QUERY FINDS: All purchases in this date range
              |
              ▼
CALCULATION:
┌─────────────────────────────────────────────────┐
│ SELECT SUM(pi.line_total)                       │
│ FROM sma_purchase_items pi                      │
│ JOIN sma_purchases pu                           │
│ WHERE pu.transaction_date BETWEEN              │
│       '2025-09-30' AND '2025-10-25'            │
│ AND pu.status = 'completed'                    │
│ AND pu.warehouse_id IN (selected warehouses)   │
└─────────────────────────────────────────────────┘
              |
              ▼
RESULT: COGS = 450,000 SAR
```

### Query 2: Get Staff Salaries

```
USER SELECTS: From 30/09/2025 to 25/10/2025
              |
              ▼
QUERY FINDS: All salary payments in this period
              |
              ▼
CALCULATION:
┌──────────────────────────────────────────────────┐
│ SELECT SUM(salary)                               │
│ FROM sma_salaries                                │
│ WHERE payment_date BETWEEN                       │
│       '2025-09-30' AND '2025-10-25'             │
│ AND status = 'paid'                              │
│ AND warehouse_id IN (selected warehouses)        │
└──────────────────────────────────────────────────┘
              |
              ▼
RESULT: Salaries = 180,000 SAR
```

### Query 3: Get Rent & Utilities

```
USER SELECTS: From 30/09/2025 to 25/10/2025
              |
              ▼
QUERY FINDS: All facility expenses
              |
              ▼
CALCULATION:
┌──────────────────────────────────────────────────┐
│ SELECT SUM(amount)                               │
│ FROM sma_expenses                                │
│ WHERE expense_type IN                            │
│       ('Rent', 'Utilities', 'Electricity', etc)  │
│ AND transaction_date BETWEEN                     │
│       '2025-09-30' AND '2025-10-25'             │
│ AND status = 'approved'                          │
│ AND warehouse_id IN (selected warehouses)        │
└──────────────────────────────────────────────────┘
              |
              ▼
RESULT: Rent & Utilities = 80,000 SAR
```

### Query 4: Get Delivery & Transport

```
SIMILAR PATTERN:
SELECT SUM(amount) FROM sma_expenses
WHERE expense_type IN ('Delivery', 'Transport', 'Courier')
  AND transaction_date BETWEEN '2025-09-30' AND '2025-10-25'
  AND status = 'approved'

RESULT: Delivery = 25,000 SAR
```

### Query 5: Get Marketing

```
SIMILAR PATTERN:
SELECT SUM(amount) FROM sma_expenses
WHERE expense_type IN ('Marketing', 'Advertising', 'Promotion')
  AND transaction_date BETWEEN '2025-09-30' AND '2025-10-25'
  AND status = 'approved'

RESULT: Marketing = 15,000 SAR
```

---

## Percentage Calculation Flow

```
Step 1: Get All Costs
┌────────────────────────┐
│ COGS:           450,000│
│ Salaries:       180,000│
│ Rent:            80,000│
│ Delivery:        25,000│
│ Marketing:       15,000│
└────────────────────────┘
                  │
                  ▼
Step 2: Calculate Total
┌────────────────────────┐
│ Total = 750,000 SAR    │
└────────────────────────┘
                  │
                  ▼
Step 3: Calculate Each Percentage
┌────────────────────────────────────┐
│ COGS%       = (450,000/750,000)×100│
│             = 0.6 × 100 = 60%      │
│                                    │
│ Salaries%   = (180,000/750,000)×100│
│             = 0.24 × 100 = 24%     │
│                                    │
│ Rent%       = (80,000/750,000)×100 │
│             = 0.107 × 100 = 10.7%  │
│             ≈ 11%                  │
│                                    │
│ Delivery%   = (25,000/750,000)×100 │
│             = 0.033 × 100 = 3.3%   │
│             ≈ 3%                   │
│                                    │
│ Marketing%  = (15,000/750,000)×100 │
│             = 0.02 × 100 = 2%      │
└────────────────────────────────────┘
                  │
                  ▼
Step 4: Verify Total = 100%
┌────────────────────────┐
│ 60 + 24 + 11 + 3 + 2 = │
│ 100% ✓                 │
└────────────────────────┘
```

---

## Data Source Mapping

```
COST TYPE                  │ TABLE SOURCE           │ FIELD USED
───────────────────────────┼────────────────────────┼──────────────────
COGS                       │ sma_purchase_items +   │ line_total
(Cost of Goods Sold)       │ sma_purchases          │ SUM(amount)
                           │                        │
───────────────────────────┼────────────────────────┼──────────────────
Staff Salaries             │ sma_salaries           │ SUM(salary)
                           │                        │
───────────────────────────┼────────────────────────┼──────────────────
Rent & Utilities           │ sma_expenses           │ SUM(amount)
                           │ (WHERE type IN         │ WHERE
                           │  'Rent', 'Utilities')  │ expense_type
                           │                        │
───────────────────────────┼────────────────────────┼──────────────────
Delivery & Transport       │ sma_expenses           │ SUM(amount)
                           │ (WHERE type IN         │ WHERE
                           │  'Delivery', etc)      │ expense_type
                           │                        │
───────────────────────────┼────────────────────────┼──────────────────
Marketing                  │ sma_expenses           │ SUM(amount)
                           │ (WHERE type IN         │ WHERE
                           │  'Marketing', etc)     │ expense_type
```

---

## Time Filter Application

```
USER SELECTS DATE RANGE: 30/09/2025 to 25/10/2025
                              │
                              ▼
CONVERT TO SQL FORMAT: '2025-09-30' to '2025-10-25'
                              │
         ┌────────────────────┼────────────────────┐
         │                    │                    │
         ▼                    ▼                    ▼
    ┌──────────┐         ┌──────────┐         ┌──────────┐
    │PURCHASES │         │ EXPENSES │         │SALARIES  │
    │TABLE     │         │  TABLE   │         │  TABLE   │
    │          │         │          │         │          │
    │Filter:   │         │Filter:   │         │Filter:   │
    │trans_    │         │trans_    │         │payment_  │
    │date>=    │         │date>=    │         │date>=    │
    │'2025-    │         │'2025-    │         │'2025-    │
    │09-30'    │         │09-30'    │         │09-30'    │
    │          │         │          │         │          │
    │trans_    │         │trans_    │         │payment_  │
    │date<=    │         │date<=    │         │date<=    │
    │'2025-    │         │'2025-    │         │'2025-    │
    │10-25'    │         │10-25'    │         │10-25'    │
    └────┬─────┘         └────┬─────┘         └────┬─────┘
         │                    │                    │
         └────────────────────┼────────────────────┘
                              │
                              ▼
                    RECORDS WITHIN RANGE:
                    30/09/2025 to 25/10/2025
                    (26 days total)
```

---

## Implementation Priority

```
PHASE 1: IMMEDIATE (This Week)
├── Verify database tables exist
├── Create Cost_center_model methods
├── Test queries with real data
└── Update controller to use model

PHASE 2: SHORT TERM (Next Week)
├── Create API endpoint for costs
├── Update JavaScript to fetch real data
├── Add error handling
└── Test date range filtering

PHASE 3: MEDIUM TERM (Next 2 Weeks)
├── Add drill-down to transactions
├── Add transaction-level details
├── Add export functionality
└── Performance optimization

PHASE 4: LONG TERM (Month+)
├── Predictive cost analysis
├── Cost anomaly detection
├── Budget vs actual comparison
└── Historical trending
```

---

## Validation Checks

```
BEFORE DISPLAYING COSTS:
├── ✓ Total expenses > 0 (else show "No data")
├── ✓ All percentages >= 0 (catch negative values)
├── ✓ Sum of percentages ≈ 100% (accounting for rounding)
├── ✓ Date range is valid (from < to)
├── ✓ Warehouse IDs are valid
├── ✓ At least 2 cost categories have data
└── ✓ No division by zero errors

ERROR HANDLING:
├── If query returns NULL → treat as 0
├── If date range invalid → show error message
├── If warehouse data missing → show company total
├── If percentage > 100% → investigate data quality
└── If negative costs → flag as data anomaly
```

---

## Key Takeaways

1. **Data comes from 3-5 tables:**

   - Purchases (for COGS)
   - Expenses (for OpEx)
   - Salaries (for Personnel)

2. **Each cost is a SUM of transactions:**

   - Filtered by date range
   - Filtered by expense type
   - Filtered by warehouse

3. **Percentages are calculated:**

   - After getting total of all costs
   - As (Cost / Total) × 100
   - Rounded to 1 decimal place

4. **Display sorted by amount:**

   - Largest costs first (descending order)
   - Makes it easy to see which categories dominate

5. **Must filter by:**
   - Date range (user input)
   - Warehouse (user permissions)
   - Status (only approved/completed)

---

**Next Step:** Implement the model methods in `Cost_center_model.php` using this logic
