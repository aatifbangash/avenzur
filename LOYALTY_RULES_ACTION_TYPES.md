# Loyalty Rules - Action Types Reference

## Overview

This document describes all supported action types in the Loyalty Rules system, their validation rules, and how they're implemented in the UI.

## Action Types

### 1. DISCOUNT_PERCENTAGE

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

### 2. DISCOUNT_FIXED

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

### 3. DISCOUNT_BOGO

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

### 4. LOYALTY_POINTS

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

### 5. TIER_UPGRADE

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

### 6. FREE_ITEM

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

### 7. NOTIFICATION

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

### 8. CUSTOM_ACTION

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

## Frontend Validation

The frontend implements the following validation:

1. **HTML5 Validation:**

   - Required fields marked with `required` attribute
   - Number ranges enforced with `min`, `max` attributes
   - Input types matched to data types

2. **JavaScript Validation:**

   - BOGO metadata constructed automatically from buyQty and getQty inputs
   - Custom metadata parsed as JSON for CUSTOM_ACTION
   - Form validation before submission

3. **User Feedback:**
   - Help text under each field explaining constraints
   - Placeholder values showing examples
   - Clear labels with validation ranges in parentheses

## Backend Integration

The `saveRule()` JavaScript function:

- Collects all form data
- Constructs metadata for DISCOUNT_BOGO
- Parses custom_metadata JSON for CUSTOM_ACTION
- Sends data to `/admin/loyalty/save_rule` endpoint

The backend controller should validate:

1. Action type exists and is valid
2. Action value meets type-specific constraints
3. Metadata structure is correct for BOGO
4. All required fields are present

## Testing Checklist

- [ ] DISCOUNT_PERCENTAGE: Test with values 4 (should fail), 5 (pass), 50 (pass), 51 (fail)
- [ ] DISCOUNT_FIXED: Test with values 0 (fail), 1 (pass), 1000 (pass), 1001 (fail)
- [ ] DISCOUNT_BOGO: Test with buyQty=0 (fail), buyQty=1 getQty=1 (pass)
- [ ] LOYALTY_POINTS: Test with value 0 (fail), value 1 (pass)
- [ ] TIER_UPGRADE: Test with empty string (fail), "GOLD" (pass)
- [ ] FREE_ITEM: Test with empty string (fail), "PROD-123" (pass)
- [ ] NOTIFICATION: Test with empty message (fail), valid message (pass)
- [ ] CUSTOM_ACTION: Test with various value types

---

**Last Updated:** November 3, 2025
**Version:** 1.0
