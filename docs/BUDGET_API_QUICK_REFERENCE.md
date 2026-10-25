# Budget API - Quick Reference Card

**Print this page for quick reference during 1-day sprint**

---

## API Endpoints Summary

| #   | Method | Endpoint                                  | Purpose                    | Auth     |
| --- | ------ | ----------------------------------------- | -------------------------- | -------- |
| 1   | POST   | `/api/v1/budgets/allocate`                | Create/update allocation   | Required |
| 2   | GET    | `/api/v1/budgets/allocated`               | List allocations           | Required |
| 3   | GET    | `/api/v1/budgets/tracking`                | Get budget vs actual       | Required |
| 4   | GET    | `/api/v1/budgets/forecast`                | Get forecast               | Required |
| 5   | GET    | `/api/v1/budgets/alerts`                  | Get alerts                 | Required |
| 6   | POST   | `/api/v1/budgets/alerts/configure`        | Configure alert thresholds | Required |
| 7   | POST   | `/api/v1/budgets/alerts/{id}/acknowledge` | Acknowledge alert          | Required |

---

## Endpoint Details

### 1. POST /api/v1/budgets/allocate

**Purpose:** Create or update budget allocation

**Request Body:**

```json
{
	"parent_warehouse_id": 1,
	"parent_hierarchy": "company|pharmacy|branch",
	"allocations": [
		{
			"child_warehouse_id": 101,
			"child_hierarchy": "pharmacy|branch",
			"allocated_amount": 50000,
			"allocation_method": "equal|proportional|custom"
		}
	],
	"period": "2025-10"
}
```

**Response:** 201 Created

```json
{
	"success": true,
	"allocations_created": 1,
	"total_allocated": 50000
}
```

**Permissions:** Admin, Finance, Pharmacy Manager

---

### 2. GET /api/v1/budgets/allocated

**Purpose:** List budget allocations

**Query Parameters:**

- `period` (optional): YYYY-MM, default=current
- `warehouse_id` (optional): Filter by entity
- `hierarchy` (optional): company|pharmacy|branch
- `limit` (optional): 1-500, default=100
- `offset` (optional): pagination, default=0

**Example:**

```
GET /api/v1/budgets/allocated?period=2025-10&hierarchy=pharmacy&limit=50
```

**Response:** 200 OK

```json
{
  "success": true,
  "data": [{...}, {...}],
  "pagination": {"total": 10, "limit": 50, "offset": 0, "pages": 1}
}
```

**Permissions:** Admin (all), Finance (company), PM (own pharmacy), BM (own branch)

---

### 3. GET /api/v1/budgets/tracking

**Purpose:** Get budget vs actual tracking

**Query Parameters:**

- `allocation_id` (optional): Specific allocation
- `period` (optional): YYYY-MM

**Example:**

```
GET /api/v1/budgets/tracking?allocation_id=42
```

**Response:** 200 OK

```json
{
	"success": true,
	"data": {
		"tracking_id": 156,
		"allocation_id": 42,
		"allocated_amount": 50000,
		"actual_spent": 12500,
		"percentage_used": 25,
		"status": "safe|warning|danger|exceeded"
	}
}
```

**Status Logic:**

- `safe`: 0-50% used
- `warning`: 50-80% used
- `danger`: 80-100% used
- `exceeded`: >100% used

---

### 4. GET /api/v1/budgets/forecast

**Purpose:** Get budget forecast and projections

**Query Parameters:**

- `allocation_id` (required)
- `period` (optional): YYYY-MM

**Example:**

```
GET /api/v1/budgets/forecast?allocation_id=42
```

**Response:** 200 OK

```json
{
	"success": true,
	"data": {
		"forecast_id": 89,
		"allocation_id": 42,
		"current_spent": 12500,
		"days_used": 5,
		"days_remaining": 26,
		"burn_rate_daily": 2500,
		"projected_end": 77500,
		"variance_amount": 27500,
		"variance_percent": 55,
		"risk_level": "low|medium|high|critical",
		"confidence_score": 80,
		"recommendation_text": "..."
	}
}
```

**Risk Levels:**

- `low`: Variance < 0% (under budget)
- `medium`: Variance 0-10%
- `high`: Variance 10-20%
- `critical`: Variance > 20%

---

### 5. GET /api/v1/budgets/alerts

**Purpose:** Get active budget alerts

**Query Parameters:**

- `period` (optional): YYYY-MM, default=current

**Example:**

```
GET /api/v1/budgets/alerts?period=2025-10
```

**Response:** 200 OK

```json
{
	"success": true,
	"data": [
		{
			"event_id": 201,
			"allocation_id": 42,
			"entity_name": "Main Pharmacy",
			"threshold_percent": 50,
			"current_percentage": 55.2,
			"triggered_at": "2025-10-25T14:15:00Z",
			"status": "active|acknowledged|resolved",
			"risk_level": "high"
		}
	],
	"count": 1
}
```

---

### 6. POST /api/v1/budgets/alerts/configure

**Purpose:** Configure alert thresholds

**Request Body:**

```json
{
	"allocation_id": 42,
	"thresholds": [50, 75, 90, 100],
	"recipient_user_ids": [1, 5, 8],
	"notification_channels": ["email", "in-app", "sms"]
}
```

**Response:** 200 OK

```json
{
	"success": true,
	"message": "Alert thresholds configured",
	"allocation_id": 42,
	"thresholds": [50, 75, 90, 100]
}
```

**Permissions:** Admin, Finance

---

### 7. POST /api/v1/budgets/alerts/{id}/acknowledge

**Purpose:** Acknowledge alert event

**URL Parameter:**

- `{id}` (required): event_id

**Example:**

```
POST /api/v1/budgets/alerts/201/acknowledge
```

**Response:** 200 OK

```json
{
	"success": true,
	"message": "Alert acknowledged",
	"event_id": 201
}
```

---

## HTTP Status Codes

| Code    | Meaning      | Example                     |
| ------- | ------------ | --------------------------- |
| **200** | Success      | GET request successful      |
| **201** | Created      | POST allocation successful  |
| **400** | Bad Request  | Missing required field      |
| **401** | Unauthorized | Invalid/missing token       |
| **403** | Forbidden    | No permission for entity    |
| **404** | Not Found    | allocation_id doesn't exist |
| **500** | Server Error | Database error, etc.        |

---

## Error Response Format

```json
{
	"success": false,
	"message": "Human-readable error message",
	"error": "detailed_error_details",
	"status": 400
}
```

---

## Role-Based Access Quick Reference

### Admin

- ✅ See all budgets at all levels
- ✅ Create allocations at any level
- ✅ Configure alerts
- ✅ View all audit trails

### Finance

- ✅ See company-level budgets only
- ✅ Create company-to-pharmacy allocations
- ✅ Configure alerts
- ✅ View company audit trails

### Pharmacy Manager

- ✅ See own pharmacy only
- ✅ See own pharmacy's branches
- ✅ Create allocations within own pharmacy
- ✅ View own pharmacy audit trails
- ❌ Cannot configure alerts
- ❌ Cannot see other pharmacies

### Branch Manager

- ✅ See own branch only
- ✅ Read-only access
- ❌ Cannot create allocations
- ❌ Cannot configure alerts
- ❌ Cannot see other branches

---

## Database Tables Quick Reference

| Table                     | Purpose                | Key Fields                                                                       |
| ------------------------- | ---------------------- | -------------------------------------------------------------------------------- |
| `sma_budget_allocation`   | Core budget allocation | allocation_id, parent_warehouse_id, child_warehouse_id, allocated_amount, period |
| `sma_budget_tracking`     | Budget vs actual       | allocation_id, actual_spent, percentage_used, status                             |
| `sma_budget_forecast`     | Predictive analytics   | allocation_id, burn_rate_daily, projected_end, risk_level                        |
| `sma_budget_alert_config` | Alert thresholds       | allocation_id, threshold_percent, recipient_user_ids                             |
| `sma_budget_alert_events` | Alert triggers         | event_id, allocation_id, status, triggered_at                                    |
| `sma_budget_audit_trail`  | Change history         | audit_id, allocation_id, action, old_values, new_values                          |

---

## Helper Functions Quick Reference

```php
// Formatting
format_currency(50000)                    // "50,000 SAR"
format_percentage(25.5)                   // "25.5%"
get_budget_status(percentage)             // "safe"|"warning"|"danger"|"exceeded"

// Calculations
calculate_percentage_used(spent, allocated)
calculate_daily_burn_rate(current_spent, days_used)
calculate_trend(current, previous)        // % change
project_end_of_month(current, days_used, days_remaining)

// Status Info
get_status_color('safe')                  // "#10B981" (hex color)
get_status_badge_class('warning')         // Tailwind class string
get_risk_level(projected_end, allocated)  // "low"|"medium"|"high"|"critical"

// Periods
get_days_used_in_period('2025-10')        // 25
get_days_remaining_in_period('2025-10')   // 6
get_period_label('2025-10')               // "October 2025"

// Alerts
get_alert_thresholds()                    // [50, 75, 90, 100]
generate_alert_message(percentage, entity, threshold)
```

---

## Common Workflows

### Workflow 1: Allocate Budget

```
1. POST /api/v1/budgets/allocate
   → allocation_id returned
2. [Auto] calculate_tracking()
3. [Auto] calculate_forecast()
4. [Auto] create audit log
```

### Workflow 2: Monitor Budget

```
1. GET /api/v1/budgets/allocated
   → List allocations for period
2. GET /api/v1/budgets/tracking?allocation_id=X
   → Get status (safe/warning/danger/exceeded)
3. GET /api/v1/budgets/forecast?allocation_id=X
   → Get projection (on-track or over-budget)
4. GET /api/v1/budgets/alerts
   → Get triggered alerts
```

### Workflow 3: Configure Alerts

```
1. POST /api/v1/budgets/alerts/configure
   → Set thresholds [50, 75, 90, 100]
   → Set recipients [user_id1, user_id2]
   → Set channels ["email", "in-app"]
2. [Auto] create alert_config records for each threshold
3. [Auto] trigger alerts when thresholds crossed
```

### Workflow 4: Acknowledge Alert

```
1. GET /api/v1/budgets/alerts
   → See active alerts
2. POST /api/v1/budgets/alerts/201/acknowledge
   → Mark as acknowledged
3. Alert moves from "active" → "acknowledged" status
```

---

## Testing Checklist

### Unit Tests

- [ ] Budget status calculation (safe/warning/danger/exceeded)
- [ ] Percentage calculation (spent/allocated)
- [ ] Burn rate calculation (daily/weekly)
- [ ] Forecast calculation (projected end-of-month)
- [ ] Risk level assessment (low/medium/high/critical)

### Integration Tests

- [ ] Allocate → Tracking → Forecast workflow
- [ ] Alert configuration and triggering
- [ ] Audit trail recording
- [ ] Role-based access filtering

### API Tests

- [ ] All 7 endpoints respond with correct status codes
- [ ] Pagination works (limit/offset)
- [ ] Filtering works (period, warehouse_id, hierarchy)
- [ ] Error handling (400, 403, 404, 500)

### Role Tests

- [ ] Admin sees all data
- [ ] Finance sees company only
- [ ] Pharmacy Manager sees own pharmacy only
- [ ] Branch Manager sees own branch only

### Dashboard Tests

- [ ] KPI cards display real data
- [ ] Status colors correct (green/yellow/orange/red)
- [ ] Trend indicators working
- [ ] Alerts display and acknowledge

---

## Deployment Checklist

### Pre-Deployment

- [ ] All tests passing
- [ ] Database backup taken
- [ ] Code review completed
- [ ] Staging tested

### Deployment

- [ ] Run migration: `php spark migrate`
- [ ] Verify tables created
- [ ] Deploy API controller
- [ ] Deploy model and helper
- [ ] Test endpoints

### Post-Deployment

- [ ] Monitor error logs
- [ ] Verify role access
- [ ] Dashboard displays real data
- [ ] Announce to team

---

**Quick Links:**

- Model: `app/models/admin/Budget_model.php`
- Controller: `app/controllers/api/v1/Budgets.php`
- Helper: `app/helpers/budget_helper.php`
- Migration: `app/migrations/003_create_budget_tables.php`

**Support:** Check `README_BUDGET_MODULE.md` for detailed docs

**Last Updated:** 2025-10-25
