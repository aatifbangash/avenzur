# Loyalty Rules - Complete Reference Guide

## Overview

This document describes all supported **Condition Types** and **Action Types** in the Loyalty Rules system, their validation rules, and how they're implemented in the UI.

---

# PART 1: CONDITION TYPES

Conditions define **when** a loyalty rule should be triggered.

---

## 1. PURCHASE_AMOUNT

**Purpose:** Threshold purchase amount condition

**Description:** Triggers when customer's purchase amount meets the specified threshold.

**UI Fields:**

- **Value:** Number input (SAR) - min=0, step=0.01
- **Operator:** Dropdown with options:
  - `>=` Greater than or equal
  - `>` Greater than
  - `=` Equal to
  - `<` Less than
  - `<=` Less than or equal

**Example Use Case:** "Apply discount if purchase is >= 500 SAR"

**JSON Example:**

```json
{
	"type": "PURCHASE_AMOUNT",
	"value": 500,
	"operator": ">="
}
```

---

## 2. FREQUENCY

**Purpose:** Transaction count/frequency condition

**Description:** Triggers based on number of transactions within a time period.

**UI Fields:**

- **Transaction Count:** Number input - min=1, step=1
- **Time Period:** Dropdown
  - `LIFETIME` - All time
  - `YEAR` - Past 12 months
  - `MONTH` - Past 30 days
  - `WEEK` - Past 7 days
  - `DAY` - Today only
- **Operator:** Dropdown
  - `>=` At least
  - `>` More than
  - `=` Exactly

**Example Use Case:** "Apply rule if customer has made >= 5 purchases in the past month"

**JSON Example:**

```json
{
	"type": "FREQUENCY",
	"value": 5,
	"period": "MONTH",
	"operator": ">="
}
```

---

## 3. CLV

**Purpose:** Customer Lifetime Value condition

**Description:** Triggers based on total amount customer has spent over their lifetime.

**UI Fields:**

- **Lifetime Value:** Number input (SAR) - min=0, step=0.01
- **Operator:** Dropdown
  - `>=` At least
  - `>` More than
  - `=` Exactly

**Example Use Case:** "VIP benefits for customers with CLV >= 10,000 SAR"

**JSON Example:**

```json
{
	"type": "CLV",
	"value": 10000,
	"operator": ">="
}
```

---

## 4. CATEGORY

**Purpose:** Product categories condition

**Description:** Triggers when purchase includes specific product categories.

**UI Fields:**

- **Category IDs:** Text input (comma-separated) - e.g., "12,45,67" or "Electronics,Pharmacy"
- **Match Type:** Dropdown
  - `ANY` - Any category matches (OR logic)
  - `ALL` - All categories must be present (AND logic)
  - `EXCLUDE` - Exclude these categories

**Example Use Case:** "Apply discount if cart contains products from Electronics OR Pharmacy category"

**JSON Example:**

```json
{
	"type": "CATEGORY",
	"value": "12,45,67",
	"match_type": "ANY"
}
```

---

## 5. TIME_BASED

**Purpose:** Temporal/time-based condition

**Description:** Triggers based on time/date constraints.

**UI Fields:**

- **Time Condition Type:** Dropdown
  - `WEEKDAY` - Specific weekday
  - `WEEKEND` - Weekend only (Fri-Sat in Saudi Arabia)
  - `DATE_RANGE` - Between specific dates
  - `TIME_RANGE` - Between specific times of day
  - `HOUR` - Specific hours
  - `MONTH` - Specific month

**Dynamic Fields:** Additional fields appear based on selected type

**Example Use Case:** "Weekend special offer - applies on Friday and Saturday"

**JSON Example:**

```json
{
	"type": "TIME_BASED",
	"value": "WEEKEND"
}
```

---

## 6. CUSTOMER_TIER

**Purpose:** Customer loyalty tier condition

**Description:** Triggers based on customer's loyalty tier status.

**UI Fields:**

- **Tier:** Dropdown
  - `BRONZE`
  - `SILVER`
  - `GOLD`
  - `PLATINUM`
- **Match Type:** Dropdown
  - `EXACT` - Exact tier match only
  - `MIN` - This tier or higher
  - `MAX` - This tier or lower

**Example Use Case:** "Gold tier and above get 15% discount"

**JSON Example:**

```json
{
	"type": "CUSTOMER_TIER",
	"value": "GOLD",
	"match_type": "MIN"
}
```

---

## 7. INVENTORY (Future Feature)

**Purpose:** Stock/inventory levels condition

**Description:** Triggers based on product inventory levels (upcoming feature).

**UI Fields:**

- **Stock Level Threshold:** Number input - min=0, step=1
- **Product ID/SKU:** Text input
- **Operator:** Dropdown
  - `<` Below threshold
  - `>` Above threshold
  - `=` Exactly at threshold
- **Info Alert:** "This is a future feature"

**Example Use Case:** "Clear stock discount when inventory < 100 units"

**JSON Example:**

```json
{
	"type": "INVENTORY",
	"value": 100,
	"product_id": "PROD-123",
	"operator": "<"
}
```

---

## 8. WEATHER (Future Feature)

**Purpose:** Weather/climate-based condition

**Description:** Triggers based on weather conditions (upcoming feature).

**UI Fields:**

- **Weather Condition:** Dropdown
  - `SUNNY`
  - `RAINY`
  - `CLOUDY`
  - `HOT` (>35°C)
  - `COLD` (<15°C)
- **Location:** Text input - City/Region
- **Info Alert:** "This is a future feature"

**Example Use Case:** "Hot beverage promotion on cold days"

**JSON Example:**

```json
{
	"type": "WEATHER",
	"value": "COLD",
	"location": "Riyadh"
}
```

---

## 9. CUSTOM (Future Feature)

**Purpose:** Extensible custom condition

**Description:** Flexible custom condition for future extensibility.

**UI Fields:**

- **Custom Value:** Text input
- **Custom Metadata:** Textarea (JSON format)
- **Info Alert:** "This is a future feature for custom extensibility"

**Example Use Case:** Custom business logic integration

**JSON Example:**

```json
{
	"type": "CUSTOM",
	"value": "CUSTOM_CONDITION_123",
	"metadata": {
		"custom_field": "custom_value"
	}
}
```

---

# PART 2: ACTION TYPES

Actions define **what happens** when rule conditions are met.

---

## 1. DISCOUNT_PERCENTAGE

**Purpose:** Apply a percentage discount to the transaction

**Validation Rules:**

- `value` must be a number
- `5 <= value <= 50`

**UI Implementation:**

- Input field: Number input with min=5, max=50, step=0.01
- Label: "Discount Percentage (5% - 50%)"
- Help text: "Percentage to discount (5-50)"

**Example:**

```json
{
	"action_type": "DISCOUNT_PERCENTAGE",
	"action_value": 15
}
```

---

## 2. DISCOUNT_FIXED

**Purpose:** Apply a fixed amount discount to the transaction

**Validation Rules:**

- `value` must be a number
- `1 <= value <= 1000`

**UI Implementation:**

- Input field: Number input with min=1, max=1000, step=0.01
- Label: "Discount Amount (1 - 1000 SAR)"
- Help text: "Fixed amount to discount (1-1000 SAR)"

**Example:**

```json
{
	"action_type": "DISCOUNT_FIXED",
	"action_value": 50
}
```

---

## 3. DISCOUNT_BOGO

**Purpose:** Buy X Get Y Free (Buy One Get One or variations)

**Validation Rules:**

- `metadata` must contain `buyQty` and `getQty`
- Both `buyQty` and `getQty` must be >= 1

**UI Implementation:**

- Two number inputs in a row:
  - Buy Quantity: min=1, step=1
  - Get Quantity (Free): min=1, step=1
- Help text: "Minimum 1"
- Metadata is automatically constructed from the two inputs

**Example:**

```json
{
	"action_type": "DISCOUNT_BOGO",
	"action_value": null,
	"metadata": {
		"buyQty": 2,
		"getQty": 1
	}
}
```

---

## 4. LOYALTY_POINTS

**Purpose:** Award loyalty points to the customer

**Validation Rules:**

- `value` must be a number
- `value >= 1`

**UI Implementation:**

- Input field: Number input with min=1, step=1
- Label: "Points to Award (Minimum 1)"
- Help text: "Number of loyalty points to award (must be >= 1)"

**Example:**

```json
{
	"action_type": "LOYALTY_POINTS",
	"action_value": 100
}
```

---

## 5. TIER_UPGRADE

**Purpose:** Upgrade customer to a higher tier

**Validation Rules:**

- `value` must be a non-empty string

**UI Implementation:**

- Select dropdown with tier options:
  - BRONZE
  - SILVER
  - GOLD
  - PLATINUM
- Help text: "Tier to upgrade customer to"

**Example:**

```json
{
	"action_type": "TIER_UPGRADE",
	"action_value": "GOLD"
}
```

---

## 6. FREE_ITEM

**Purpose:** Give a free product/item to the customer

**Validation Rules:**

- `value` must be a non-empty string (Product ID/SKU)

**UI Implementation:**

- Text input for Product ID/SKU (required)
- Optional text input for Product Name (display purposes)
- Help text: "Product to give for free (must be non-empty)"

**Example:**

```json
{
	"action_type": "FREE_ITEM",
	"action_value": "PROD-12345"
}
```

---

## 7. NOTIFICATION

**Purpose:** Send a notification to the customer

**Validation Rules:**

- `value` must be a non-empty string (message)

**UI Implementation:**

- Textarea for notification message (required)
- Select dropdown for notification channel:
  - IN_APP
  - EMAIL
  - SMS
  - ALL
- Help text: "Message to send to customer (must be non-empty)"

**Example:**

```json
{
	"action_type": "NOTIFICATION",
	"action_value": "Congratulations! You've earned a special reward!",
	"metadata": {
		"channel": "EMAIL"
	}
}
```

---

## 8. CUSTOM_ACTION

**Purpose:** Flexible custom action for future extensions

**Validation Rules:**

- `value` can be any type (most flexible)

**UI Implementation:**

- Text input for custom action value (required)
- Optional textarea for custom metadata in JSON format
- Help text: "Flexible value for custom action (can be any type)"

**Example:**

```json
{
	"action_type": "CUSTOM_ACTION",
	"action_value": "CUSTOM_REWARD_123",
	"metadata": {
		"key": "value",
		"additional": "data"
	}
}
```

---

## Frontend Implementation Details

### Conditions Dynamic Form

- Conditions are added dynamically using `addCondition()` function
- Each condition gets a unique counter ID
- Fields update based on selected condition type via `updateConditionFields(id, type)`
- Conditions can be removed individually
- Extra fields container for additional metadata

### Actions Dynamic Form

- Single action per rule
- Action type selector updates available fields via `updateActionFields()`
- BOGO metadata automatically constructed from buyQty/getQty inputs
- Custom metadata parsed as JSON for CUSTOM_ACTION

### Form Validation

1. **HTML5 Validation:**

   - Required fields marked with `required` attribute
   - Number ranges enforced with `min`, `max` attributes
   - Input types matched to data types

2. **JavaScript Validation:**

   - Form validity checked before submission
   - Metadata construction for special types
   - Custom metadata JSON parsing

3. **User Feedback:**
   - Help text under each field
   - Placeholder values with examples
   - Clear labels with validation ranges
   - Info alerts for future features

---

## Complete Rule Example

Combining conditions and actions:

```json
{
	"rule_name": "Gold Tier Weekend Bonus",
	"hierarchy_level": "COMPANY",
	"customer_tier": "GOLD",
	"priority": 3,
	"status": 1,
	"start_date": "2025-01-01",
	"end_date": "2025-12-31",
	"conditions": [
		{
			"type": "PURCHASE_AMOUNT",
			"value": 300,
			"operator": ">="
		},
		{
			"type": "TIME_BASED",
			"value": "WEEKEND"
		},
		{
			"type": "CUSTOMER_TIER",
			"value": "GOLD",
			"match_type": "MIN"
		}
	],
	"action_type": "DISCOUNT_PERCENTAGE",
	"action_value": 20,
	"min_purchase_amount": 300,
	"max_discount_amount": 200,
	"allow_combined": false
}
```

**This rule reads as:**
"For Gold tier or higher customers, on weekends, when purchase amount is >= 300 SAR, apply 20% discount (max 200 SAR discount, cannot be combined with other offers)"

---

## Testing Checklist

### Conditions

- [ ] PURCHASE_AMOUNT: Test all operators with edge values
- [ ] FREQUENCY: Test all time periods with various counts
- [ ] CLV: Test with zero, small, and large values
- [ ] CATEGORY: Test ANY, ALL, EXCLUDE with single and multiple categories
- [ ] TIME_BASED: Test each time type option
- [ ] CUSTOMER_TIER: Test EXACT, MIN, MAX match types
- [ ] INVENTORY: Verify future feature alert displays
- [ ] WEATHER: Verify future feature alert displays
- [ ] CUSTOM: Test JSON metadata parsing

### Actions

- [ ] DISCOUNT_PERCENTAGE: Test values 4 (fail), 5 (pass), 50 (pass), 51 (fail)
- [ ] DISCOUNT_FIXED: Test values 0 (fail), 1 (pass), 1000 (pass), 1001 (fail)
- [ ] DISCOUNT_BOGO: Test buyQty=0 (fail), buyQty=1 getQty=1 (pass)
- [ ] LOYALTY_POINTS: Test value 0 (fail), value 1 (pass)
- [ ] TIER_UPGRADE: Test empty string (fail), "GOLD" (pass)
- [ ] FREE_ITEM: Test empty string (fail), "PROD-123" (pass)
- [ ] NOTIFICATION: Test empty message (fail), valid message (pass)
- [ ] CUSTOM_ACTION: Test various value types and JSON metadata

---

**Last Updated:** November 3, 2025  
**Version:** 2.0  
**Status:** Production Ready
