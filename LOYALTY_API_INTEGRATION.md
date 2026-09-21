# Loyalty Rules API Integration Guide

## Overview

The Loyalty Rules UI is now integrated with an external Loyalty Rules Engine API running on Node.js.

---

## Configuration

### Environment Variable

Set the Loyalty API URL using an environment variable:

```bash
export LOYALTY_API_URL="http://localhost:3000"
```

### PHP Configuration

Located in `/app/config/constants.php`:

```php
define('LOYALTY_API_URL', getenv('LOYALTY_API_URL') ?: 'http://localhost:3000');
define('LOYALTY_API_TIMEOUT', 30); // seconds
```

**For Production:**

```bash
export LOYALTY_API_URL="https://loyalty-api.yourdomain.com"
```

---

## API Endpoints

### 1. Create Rule

**Endpoint:** `POST /api/v1/rules`

**Request Body:**

```json
{
	"name": "Gold Tier 15% Discount",
	"description": "Permanent discount for Gold tier customers",
	"ruleType": "DISCOUNT",
	"scope": {
		"level": "COMPANY",
		"scopeId": "branch-001"
	},
	"conditions": [
		{
			"type": "CUSTOMER_TIER",
			"operator": "IN",
			"value": {
				"stringValue": ["GOLD", "PLATINUM"]
			}
		}
	],
	"action": {
		"type": "DISCOUNT_PERCENTAGE",
		"value": {
			"percentageValue": 15
		}
	},
	"priority": 10,
	"validFrom": "2025-01-01T00:00:00Z",
	"validUntil": "2025-12-31T23:59:59Z"
}
```

### 2. Get All Rules

**Endpoint:** `GET /api/v1/rules`

**Response:**

```json
[
  {
    "id": "rule-123",
    "name": "Gold Tier 15% Discount",
    "ruleType": "DISCOUNT",
    ...
  }
]
```

### 3. Get Single Rule

**Endpoint:** `GET /api/v1/rules/:id`

### 4. Delete Rule

**Endpoint:** `DELETE /api/v1/rules/:id`

---

## Data Transformation

### Form Data → API Format

The controller transforms the frontend form data to match the external API format:

#### Action Types Mapping

| Frontend Action Type | API Action Type     | Value Format                              |
| -------------------- | ------------------- | ----------------------------------------- |
| DISCOUNT_PERCENTAGE  | DISCOUNT_PERCENTAGE | `{percentageValue: 15}`                   |
| DISCOUNT_FIXED       | DISCOUNT_FIXED      | `{numberValue: 50}`                       |
| DISCOUNT_BOGO        | DISCOUNT_BOGO       | `{metadata: {buyQty: 2, getQty: 1}}`      |
| LOYALTY_POINTS       | LOYALTY_POINTS      | `{numberValue: 100}`                      |
| TIER_UPGRADE         | TIER_UPGRADE        | `{stringValue: "GOLD"}`                   |
| FREE_ITEM            | FREE_ITEM           | `{stringValue: "PROD-123"}`               |
| NOTIFICATION         | NOTIFICATION        | `{stringValue: "message"}`                |
| CUSTOM_ACTION        | CUSTOM_ACTION       | `{stringValue: "value", metadata: {...}}` |

#### Condition Types Mapping

| Frontend Condition | API Condition   | Value Format                                               |
| ------------------ | --------------- | ---------------------------------------------------------- |
| PURCHASE_AMOUNT    | PURCHASE_AMOUNT | `{numberValue: 500}`                                       |
| FREQUENCY          | FREQUENCY       | `{numberValue: 5, metadata: {period: "MONTH"}}`            |
| CLV                | CLV             | `{numberValue: 10000}`                                     |
| CATEGORY           | CATEGORY        | `{stringValue: ["12","45"], metadata: {matchType: "ANY"}}` |
| CUSTOMER_TIER      | CUSTOMER_TIER   | `{stringValue: ["GOLD"], metadata: {matchType: "MIN"}}`    |
| TIME_BASED         | TIME_BASED      | `{stringValue: "WEEKEND"}`                                 |

---

## PHP Controller Methods

### `save_rule()`

Receives form data, transforms it, and calls external API.

**Transformation Flow:**

1. Receive JSON from frontend
2. Validate required fields
3. Call `transform_to_api_format($data)`
4. Call `call_loyalty_api('/api/v1/rules', 'POST', $payload)`
5. Return success/error response

### `get_rules()`

Fetches all rules from external API and transforms them for frontend display.

### `get_rule($id)`

Fetches single rule and transforms it for editing.

### `delete_rule($id)`

Calls DELETE endpoint on external API.

---

## Error Handling

### Connection Errors

If the external API is not reachable:

```json
{
	"success": false,
	"message": "API Connection Error: Failed to connect to localhost:3000"
}
```

### Validation Errors

If required fields are missing:

```json
{
	"success": false,
	"message": "<p>Rule Name is required</p><p>Action Type is required</p>"
}
```

### API Errors

If the external API returns an error:

```json
{
  "success": false,
  "message": "API Error (HTTP 400)",
  "debug": {
    "message": "Validation failed",
    "errors": [...]
  }
}
```

---

## Debugging

### Enable Debug Logging

Check `/app/logs/log-YYYY-MM-DD.php` for detailed logs:

```
DEBUG - YYYY-MM-DD HH:MM:SS --> Loyalty save_rule called
DEBUG - YYYY-MM-DD HH:MM:SS --> Raw input: {"rule_name":"Test",...}
DEBUG - YYYY-MM-DD HH:MM:SS --> API Payload: {"name":"Test",...}
DEBUG - YYYY-MM-DD HH:MM:SS --> API Result: {"success":true,...}
```

### Browser Console

The frontend now logs:

- Sending data to API
- Response status and headers
- Response data
- Any errors

---

## Testing

### 1. Test API Connectivity

```bash
curl -X GET http://localhost:3000/api/v1/rules
```

### 2. Test Rule Creation

```bash
curl -X POST http://localhost:3000/api/v1/rules \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Test Rule",
    "description": "Test",
    "ruleType": "DISCOUNT",
    "scope": {"level": "COMPANY", "scopeId": "company-001"},
    "conditions": [],
    "action": {"type": "DISCOUNT_PERCENTAGE", "value": {"percentageValue": 10}},
    "priority": 5,
    "validFrom": "2025-01-01T00:00:00Z",
    "validUntil": "2025-12-31T23:59:59Z"
  }'
```

### 3. Test via UI

1. Navigate to: `http://localhost:8080/avenzur/admin/loyalty/rules`
2. Click "New Rule"
3. Fill in form fields
4. Click "Save Rule"
5. Check browser console for detailed logs
6. Check PHP logs at `/app/logs/`

---

## Troubleshooting

### Issue: "API Connection Error"

**Cause:** External API is not running  
**Solution:** Start the Node.js Loyalty Rules Engine:

```bash
cd /path/to/loyalty-rules-engine
npm start
```

### Issue: "Invalid JSON data"

**Cause:** Form data not being sent correctly  
**Solution:** Check browser console for "Sending data to API" log

### Issue: "Server returned non-JSON response"

**Cause:** PHP error or redirect  
**Solution:** Check PHP error logs and ensure controller method exists

### Issue: "CORS Error"

**Cause:** Cross-origin request blocked  
**Solution:** Add CORS headers to Node.js API:

```javascript
app.use((req, res, next) => {
	res.header("Access-Control-Allow-Origin", "*");
	res.header("Access-Control-Allow-Headers", "Content-Type");
	next();
});
```

---

## Production Deployment

### 1. Set Environment Variable

```bash
export LOYALTY_API_URL="https://loyalty-api.production.com"
```

### 2. Update Configuration

Edit `/app/config/constants.php` if needed for specific environment.

### 3. Security Considerations

- Use HTTPS for production API
- Add API authentication (Bearer token, API key)
- Implement rate limiting
- Add request/response encryption
- Enable CORS only for trusted origins

### 4. Update `call_loyalty_api()` for Authentication

```php
private function call_loyalty_api($endpoint, $method = 'GET', $data = null)
{
    $url = LOYALTY_API_URL . $endpoint;

    $headers = [
        'Content-Type: application/json',
        'Accept: application/json',
        'Authorization: Bearer ' . LOYALTY_API_TOKEN // Add this
    ];

    // ... rest of the code
}
```

---

## Next Steps

1. **Start External API:**

   ```bash
   cd loyalty-rules-engine
   npm install
   npm start
   ```

2. **Test Integration:**

   - Create a test rule via UI
   - Verify it appears in external API
   - Test edit and delete operations

3. **Monitor Logs:**
   - Browser console for frontend errors
   - `/app/logs/` for backend errors
   - External API logs for request/response

---

**Last Updated:** November 3, 2025  
**Version:** 1.0  
**Status:** Integration Complete
