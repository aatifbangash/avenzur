# Major Costs - Actual Data Derivation Logic

**Date:** October 25, 2025  
**Purpose:** Show how to calculate REAL Major Costs from database instead of hardcoded mock data

---

## Overview

The Major Costs need to be derived from actual transaction data in your database. Here's the complete logic:

---

## Data Sources Needed

### 1. **Transaction/Purchase Data Table**

The primary source for costs is the purchase/transaction table:

```sql
-- Table: sma_purchases or sma_transactions
-- Contains: purchase_id, warehouse_id, transaction_date,
--           total_amount, tax_amount, shipping, status
-- Key fields for costs:
--   - total_amount (what was purchased)
--   - tax_amount
--   - shipping_cost
--   - transaction_date (for filtering by date range)
```

### 2. **Purchase Item Details Table**

Breaks down what items were purchased:

```sql
-- Table: sma_purchase_items or sma_transaction_items
-- Contains: item_id, purchase_id, product_id, quantity,
--           unit_cost, line_total, tax_value
-- Key fields:
--   - product_id (to categorize into cost types)
--   - quantity
--   - line_total (cost of this item)
```

### 3. **Product/Item Master Table**

Categorizes items into cost types:

```sql
-- Table: sma_products or sma_items
-- Contains: product_id, product_name, category_id,
--           product_type, unit_cost
-- Key fields:
--   - category_id (to group into COGS, Supplies, etc.)
--   - product_type (Inventory, Service, etc.)
```

### 4. **Product Category Table**

Maps products to cost categories:

```sql
-- Table: sma_product_categories or sma_categories
-- Contains: category_id, category_name, category_type
-- Example categories:
--   - Medicines & Drugs → COGS
--   - Office Supplies → Operating Expenses
--   - Utilities → Facility Costs
--   - Salaries → Personnel Costs
--   - Transport → Logistics Costs
```

### 5. **Expense/Bills Table**

Captures non-inventory expenses:

```sql
-- Table: sma_expenses or sma_bills
-- Contains: expense_id, warehouse_id, expense_type,
--           amount, transaction_date, description
-- Key fields:
--   - expense_type (Rent, Utilities, Salaries, etc.)
--   - amount (expense amount)
--   - transaction_date (for filtering)
```

### 6. **Employee/Salary Table**

Tracks personnel costs:

```sql
-- Table: sma_employees or sma_salaries
-- Contains: employee_id, employee_name, salary,
--           deductions, payment_date, warehouse_id
-- Key fields:
--   - salary (monthly salary)
--   - deductions
--   - payment_date (to accumulate for period)
```

---

## Logic to Calculate Major Costs

### Step 1: Identify Cost Categories

Define the major cost categories in your system:

```php
$costCategories = [
    'COGS' => [
        'query' => 'SELECT SUM(...) FROM purchases WHERE category = "Medicines"',
        'description' => 'Cost of Goods Sold (Medicines & Inventory)'
    ],
    'Staff Salaries' => [
        'query' => 'SELECT SUM(salary) FROM employees WHERE payment_date BETWEEN ? AND ?',
        'description' => 'Employee salaries & compensation'
    ],
    'Rent & Utilities' => [
        'query' => 'SELECT SUM(amount) FROM expenses WHERE type IN ("Rent", "Utilities")',
        'description' => 'Facility costs'
    ],
    'Delivery & Transport' => [
        'query' => 'SELECT SUM(amount) FROM expenses WHERE type = "Delivery"',
        'description' => 'Logistics & transportation'
    ],
    'Marketing' => [
        'query' => 'SELECT SUM(amount) FROM expenses WHERE type = "Marketing"',
        'description' => 'Marketing & promotions'
    ]
];
```

### Step 2: Query for Each Cost Category

For each category, sum the relevant transaction amounts within the date range:

```sql
-- COGS (Cost of Goods Sold)
SELECT SUM(pi.line_total) as cogs_amount
FROM sma_purchase_items pi
JOIN sma_products p ON pi.product_id = p.product_id
JOIN sma_product_categories pc ON p.category_id = pc.category_id
JOIN sma_purchases pu ON pi.purchase_id = pu.purchase_id
WHERE pc.category_type = 'COGS'
  AND pu.transaction_date BETWEEN '2025-09-30' AND '2025-10-25'
  AND pu.warehouse_id IN (selected warehouses)
  AND pu.status = 'completed';

-- Staff Salaries
SELECT SUM(salary) as salary_amount
FROM sma_salaries
WHERE payment_date BETWEEN '2025-09-30' AND '2025-10-25'
  AND warehouse_id IN (selected warehouses)
  AND status = 'paid';

-- Rent & Utilities
SELECT SUM(amount) as facility_costs
FROM sma_expenses
WHERE expense_type IN ('Rent', 'Utilities', 'Electricity', 'Water')
  AND transaction_date BETWEEN '2025-09-30' AND '2025-10-25'
  AND warehouse_id IN (selected warehouses)
  AND status = 'approved';

-- Delivery & Transport
SELECT SUM(amount) as delivery_costs
FROM sma_expenses
WHERE expense_type IN ('Delivery', 'Transport', 'Courier', 'Logistics')
  AND transaction_date BETWEEN '2025-09-30' AND '2025-10-25'
  AND warehouse_id IN (selected warehouses)
  AND status = 'approved';

-- Marketing & Promotions
SELECT SUM(amount) as marketing_costs
FROM sma_expenses
WHERE expense_type IN ('Marketing', 'Advertising', 'Promotion', 'Digital Marketing')
  AND transaction_date BETWEEN '2025-09-30' AND '2025-10-25'
  AND warehouse_id IN (selected warehouses)
  AND status = 'approved';
```

### Step 3: Calculate Total Expenses

```sql
-- Get total of all expenses
SELECT SUM(total_cost) as total_expenses FROM (
    SELECT SUM(pi.line_total) as total_cost FROM sma_purchase_items pi
    JOIN sma_purchases pu ON pi.purchase_id = pu.purchase_id
    WHERE pu.transaction_date BETWEEN ? AND ? AND pu.status = 'completed'

    UNION ALL

    SELECT SUM(amount) as total_cost FROM sma_expenses
    WHERE transaction_date BETWEEN ? AND ? AND status = 'approved'

    UNION ALL

    SELECT SUM(salary) as total_cost FROM sma_salaries
    WHERE payment_date BETWEEN ? AND ? AND status = 'paid'
) as all_costs;
```

### Step 4: Calculate Percentages

```php
$total_expenses = $cogs + $salaries + $rent + $delivery + $marketing;

$percentages = [
    'COGS' => ($cogs / $total_expenses) * 100,
    'Staff Salaries' => ($salaries / $total_expenses) * 100,
    'Rent & Utilities' => ($rent / $total_expenses) * 100,
    'Delivery & Transport' => ($delivery / $total_expenses) * 100,
    'Marketing' => ($marketing / $total_expenses) * 100,
];
```

### Step 5: Sort by Amount (Descending)

```php
// Sort to show largest costs first
usort($majorCosts, function($a, $b) {
    return $b['amount'] <=> $a['amount'];
});
```

---

## Implementation in Model

Here's how to implement this in `Cost_center_model.php`:

```php
<?php
class Cost_center_model extends CI_Model {

    /**
     * Get Major Costs Breakdown
     *
     * @param string $from_date (YYYY-MM-DD)
     * @param string $to_date (YYYY-MM-DD)
     * @param array $warehouse_ids
     * @return array Major costs with amounts and percentages
     */
    public function get_major_costs($from_date, $to_date, $warehouse_ids = []) {

        // Get COGS (Cost of Goods Sold)
        $cogs_query = $this->db
            ->select('SUM(pi.line_total) as amount')
            ->from('sma_purchase_items pi')
            ->join('sma_products p', 'pi.product_id = p.product_id', 'left')
            ->join('sma_product_categories pc', 'p.category_id = pc.category_id', 'left')
            ->join('sma_purchases pu', 'pi.purchase_id = pu.purchase_id')
            ->where('pc.category_type', 'COGS')
            ->where('pu.transaction_date >=', $from_date)
            ->where('pu.transaction_date <=', $to_date)
            ->where('pu.status', 'completed');

        if (!empty($warehouse_ids)) {
            $this->db->where_in('pu.warehouse_id', $warehouse_ids);
        }

        $cogs_result = $this->db->get()->row();
        $cogs = $cogs_result ? $cogs_result->amount : 0;

        // Get Staff Salaries
        $salary_query = $this->db
            ->select('SUM(salary) as amount')
            ->from('sma_salaries')
            ->where('payment_date >=', $from_date)
            ->where('payment_date <=', $to_date)
            ->where('status', 'paid');

        if (!empty($warehouse_ids)) {
            $this->db->where_in('warehouse_id', $warehouse_ids);
        }

        $salary_result = $this->db->get()->row();
        $salaries = $salary_result ? $salary_result->amount : 0;

        // Get Facility Costs (Rent, Utilities)
        $facility_query = $this->db
            ->select('SUM(amount) as amount')
            ->from('sma_expenses')
            ->where_in('expense_type', ['Rent', 'Utilities', 'Electricity', 'Water'])
            ->where('transaction_date >=', $from_date)
            ->where('transaction_date <=', $to_date)
            ->where('status', 'approved');

        if (!empty($warehouse_ids)) {
            $this->db->where_in('warehouse_id', $warehouse_ids);
        }

        $facility_result = $this->db->get()->row();
        $facility_costs = $facility_result ? $facility_result->amount : 0;

        // Get Delivery Costs
        $delivery_query = $this->db
            ->select('SUM(amount) as amount')
            ->from('sma_expenses')
            ->where_in('expense_type', ['Delivery', 'Transport', 'Courier', 'Logistics'])
            ->where('transaction_date >=', $from_date)
            ->where('transaction_date <=', $to_date)
            ->where('status', 'approved');

        if (!empty($warehouse_ids)) {
            $this->db->where_in('warehouse_id', $warehouse_ids);
        }

        $delivery_result = $this->db->get()->row();
        $delivery_costs = $delivery_result ? $delivery_result->amount : 0;

        // Get Marketing Costs
        $marketing_query = $this->db
            ->select('SUM(amount) as amount')
            ->from('sma_expenses')
            ->where_in('expense_type', ['Marketing', 'Advertising', 'Promotion'])
            ->where('transaction_date >=', $from_date)
            ->where('transaction_date <=', $to_date)
            ->where('status', 'approved');

        if (!empty($warehouse_ids)) {
            $this->db->where_in('warehouse_id', $warehouse_ids);
        }

        $marketing_result = $this->db->get()->row();
        $marketing_costs = $marketing_result ? $marketing_result->amount : 0;

        // Build cost array
        $costs = [
            ['name' => 'COGS', 'amount' => $cogs],
            ['name' => 'Staff Salaries', 'amount' => $salaries],
            ['name' => 'Rent & Utilities', 'amount' => $facility_costs],
            ['name' => 'Delivery & Transport', 'amount' => $delivery_costs],
            ['name' => 'Marketing', 'amount' => $marketing_costs],
        ];

        // Calculate total
        $total = array_sum(array_column($costs, 'amount'));

        // Add percentages and sort
        foreach ($costs as &$cost) {
            $cost['percentage'] = $total > 0 ? round(($cost['amount'] / $total) * 100, 1) : 0;
        }

        // Sort by amount (highest first)
        usort($costs, function($a, $b) {
            return $b['amount'] <=> $a['amount'];
        });

        return [
            'costs' => $costs,
            'total' => $total,
            'period' => [
                'from' => $from_date,
                'to' => $to_date,
            ]
        ];
    }

    /**
     * Alternative: Get Major Costs Using Single Query
     * More efficient but requires proper SQL
     */
    public function get_major_costs_optimized($from_date, $to_date, $warehouse_ids = []) {

        $warehouse_filter = '';
        if (!empty($warehouse_ids)) {
            $warehouse_filter = ' AND pu.warehouse_id IN (' . implode(',', $warehouse_ids) . ')';
        }

        $sql = "
            SELECT
                'COGS' as name,
                SUM(pi.line_total) as amount,
                ROUND((SUM(pi.line_total) / (
                    SELECT SUM(total_amount)
                    FROM sma_purchases
                    WHERE transaction_date BETWEEN ? AND ?
                    AND status = 'completed'
                    {$warehouse_filter}
                )) * 100, 1) as percentage
            FROM sma_purchase_items pi
            JOIN sma_purchases pu ON pi.purchase_id = pu.purchase_id
            WHERE pu.transaction_date BETWEEN ? AND ?
            AND pu.status = 'completed'
            {$warehouse_filter}

            UNION ALL

            SELECT
                'Staff Salaries' as name,
                SUM(salary) as amount,
                ROUND((SUM(salary) / (
                    SELECT SUM(total_amount)
                    FROM sma_purchases
                    WHERE transaction_date BETWEEN ? AND ?
                    AND status = 'completed'
                    {$warehouse_filter}
                )) * 100, 1) as percentage
            FROM sma_salaries
            WHERE payment_date BETWEEN ? AND ?
            AND status = 'paid'
            {$warehouse_filter}

            UNION ALL

            SELECT
                'Rent & Utilities' as name,
                SUM(amount) as amount,
                ROUND((SUM(amount) / (
                    SELECT SUM(total_amount)
                    FROM sma_purchases
                    WHERE transaction_date BETWEEN ? AND ?
                    AND status = 'completed'
                    {$warehouse_filter}
                )) * 100, 1) as percentage
            FROM sma_expenses
            WHERE expense_type IN ('Rent', 'Utilities', 'Electricity', 'Water')
            AND transaction_date BETWEEN ? AND ?
            AND status = 'approved'
            {$warehouse_filter}

            ORDER BY amount DESC
        ";

        $result = $this->db->query($sql, [
            $from_date, $to_date,  // COGS query
            $from_date, $to_date,  // COGS date filter
            $from_date, $to_date,  // Salary query
            $from_date, $to_date,  // Salary date filter
            $from_date, $to_date,  // Rent query
            $from_date, $to_date,  // Rent date filter
        ]);

        return $result->result_array();
    }
}
```

---

## Usage in Controller

```php
<?php
class Cost_center extends MY_Controller {

    public function dashboard() {

        $from_date = $this->input->get('from') ?: date('Y-m-01');
        $to_date = $this->input->get('to') ?: date('Y-m-d');

        // Get warehouse IDs for current user
        $warehouse_ids = $this->_get_user_warehouses();

        // Get actual major costs from database
        $major_costs_data = $this->cost_center->get_major_costs(
            $from_date,
            $to_date,
            $warehouse_ids
        );

        // Pass to view
        $this->data['major_costs'] = $major_costs_data['costs'];
        $this->data['total_costs'] = $major_costs_data['total'];

        $this->load->view($this->theme . 'cost_center_dashboard', $this->data);
    }
}
```

---

## JavaScript Update (Replace Mock Data)

In `cost_center_dashboard.php`, replace mock data:

```javascript
// BEFORE (Mock Data)
function generateMockData() {
	return {
		majorCosts: [
			{ name: "COGS", amount: 450000, percentage: 60 },
			// ... hardcoded data
		],
	};
}

// AFTER (Real Data from PHP)
function loadMajorCosts() {
	const fromDate = document.getElementById("fromDate").value;
	const toDate = document.getElementById("toDate").value;

	fetch(`/api/cost-center/major-costs?from=${fromDate}&to=${toDate}`)
		.then((response) => response.json())
		.then((data) => {
			updateMajorCostsList(data.costs);
		})
		.catch((error) => console.error("Error loading costs:", error));
}

function updateMajorCostsList(costs) {
	let html = "";
	let total = costs.reduce((sum, cost) => sum + cost.amount, 0);

	costs.forEach((cost) => {
		const percentage =
			cost.percentage || ((cost.amount / total) * 100).toFixed(1);
		const progressColor =
			percentage > 50 ? "#ff6b6b" : percentage > 30 ? "#fbbf24" : "#10b981";

		html += `
            <div style="margin-bottom: 15px;">
                <div style="display: flex; justify-content: space-between; margin-bottom: 5px;">
                    <strong>${cost.name}</strong>
                    <span>${Math.round(percentage)}%</span>
                </div>
                <div style="width: 100%; height: 20px; background: #f0f0f0; border-radius: 3px;">
                    <div style="width: ${percentage}%; height: 100%; background: ${progressColor};"></div>
                </div>
                <small style="color: #999;">SAR ${formatNumber(
									cost.amount
								)}</small>
            </div>
        `;
	});

	document.getElementById("majorCostsList").innerHTML = html;
}
```

---

## Database Schema Required

Ensure these tables/columns exist:

```sql
-- sma_purchases
CREATE TABLE sma_purchases (
    purchase_id INT PRIMARY KEY,
    warehouse_id INT,
    transaction_date DATETIME,
    total_amount DECIMAL(10,2),
    status VARCHAR(50),
    -- ... other columns
);

-- sma_purchase_items
CREATE TABLE sma_purchase_items (
    item_id INT PRIMARY KEY,
    purchase_id INT,
    product_id INT,
    line_total DECIMAL(10,2),
    -- ... other columns
);

-- sma_products
CREATE TABLE sma_products (
    product_id INT PRIMARY KEY,
    category_id INT,
    product_name VARCHAR(255),
    -- ... other columns
);

-- sma_product_categories
CREATE TABLE sma_product_categories (
    category_id INT PRIMARY KEY,
    category_name VARCHAR(255),
    category_type VARCHAR(50),  -- COGS, Supplies, etc.
);

-- sma_expenses
CREATE TABLE sma_expenses (
    expense_id INT PRIMARY KEY,
    warehouse_id INT,
    expense_type VARCHAR(100),
    amount DECIMAL(10,2),
    transaction_date DATETIME,
    status VARCHAR(50),
    -- ... other columns
);

-- sma_salaries
CREATE TABLE sma_salaries (
    salary_id INT PRIMARY KEY,
    warehouse_id INT,
    salary DECIMAL(10,2),
    payment_date DATETIME,
    status VARCHAR(50),
    -- ... other columns
);
```

---

## Benefits of Real Data

✅ **Accuracy:** Real database values, not hardcoded  
✅ **Dynamic:** Updates when new transactions are added  
✅ **Realistic:** Reflects actual business operations  
✅ **Auditable:** Can trace back to source transactions  
✅ **Drillable:** Users can click to see transaction details  
✅ **Responsive:** Changes with date range selection

---

## Migration Steps

1. **Verify Tables Exist**

   - Check if `sma_expenses`, `sma_salaries` tables exist
   - Check if `sma_purchases`, `sma_purchase_items` exist

2. **Add Model Method**

   - Implement `get_major_costs()` in Cost_center_model

3. **Update Controller**

   - Call model method instead of mock data
   - Pass real data to view

4. **Update JavaScript**

   - Fetch data from API endpoint
   - Remove mock data generation

5. **Test**
   - Verify costs display correctly
   - Test date range filtering
   - Verify percentages add up to 100%

---

**Status:** Implementation ready - use this logic to connect real database data
