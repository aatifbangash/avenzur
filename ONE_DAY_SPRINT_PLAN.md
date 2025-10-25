# ONE-DAY SPRINT: Phase 1 + Phase 2 Complete Implementation

**Date:** October 25, 2025  
**Duration:** 1 Day (8 hours)  
**Goal:** Full budgeting system with forecast, alerts, and role-based access

---

## 🎯 SCOPE: What We're Building in 1 Day

### ✅ Requirements Approved

- ✅ Include forecasting & alerts
- ✅ Centralized budget allocation (Admin allocates)
- ✅ Role-based data access:
  - Admin: All data
  - Finance: Company-level only
  - Pharmacy Manager: Own pharmacy only
  - Branch Manager: Own branch only
- ✅ Timeline: 1 day delivery

### 🗂️ Components to Deliver

**Database:**

- New budget tables (allocation, tracking, alerts, forecast)
- New views for budget vs actual
- Modified views for role-based filtering

**API:**

- Budget allocation endpoints
- Budget tracking endpoints
- Forecast endpoints
- Alert management endpoints

**UI:**

- Dashboard: Allocate budget form
- Dashboard: Budget vs actual cards
- Dashboard: Forecast section
- Dashboard: Alerts section
- Role-based visibility

---

## 📋 DETAILED IMPLEMENTATION PLAN

### HOUR 1-2: Database Schema (9:00-11:00 AM)

**Tasks:**

1. Create budget dimension table
2. Create budget allocation table
3. Create budget tracking table
4. Create budget forecast table
5. Create budget alert configuration table
6. Create budget audit trail table
7. Modify views for budget vs actual

**Deliverables:**

- `003_create_budget_tables.php` (Migration)
- SQL views with role-based filtering
- Test data inserted

---

### HOUR 3: API Endpoints (11:00 AM-12:00 PM)

**Tasks:**

1. Create Budget_model.php
2. Create /api/v1/Budgets.php controller
3. Build 4 main endpoint groups:
   - POST /api/v1/budgets/allocate (allocation)
   - GET /api/v1/budgets/allocated (tracking)
   - GET /api/v1/budgets/forecast (forecasting)
   - GET/POST /api/v1/budgets/alerts (alert management)

**Deliverables:**

- All endpoints working with proper auth
- Error handling implemented
- Response structures consistent

---

### HOUR 4: Dashboard Phase 1 Connection (12:00-1:00 PM)

**Tasks:**

1. Replace mock data with real API calls
2. Connect KPI cards to /api/v1/cost-center/summary
3. Connect pharmacy list to /api/v1/cost-center/pharmacies
4. Test all 4 KPI cards with real data

**Deliverables:**

- Dashboard showing real cost center data
- No mock data
- Date filtering working

---

### HOUR 5: Budget Allocation Form (1:00-2:00 PM)

**Tasks:**

1. Add Budget Allocation Form to dashboard
2. Implement hierarchy selector (Company → Pharmacy → Branch)
3. Distribution methods: Equal, Proportional, Custom
4. Real-time validation
5. Submit to API

**Deliverables:**

- Working allocation form
- Validates: Total <= Parent
- Prevents: Over-allocation

---

### HOUR 6: Budget Tracking Section (2:00-3:00 PM)

**Tasks:**

1. Add "Budget vs Actual" cards
   - Allocated / Spent / Remaining / % Used
2. Add color coding (green/yellow/red)
3. Add drill-down to detail
4. Add edit budget button

**Deliverables:**

- Budget tracking cards
- Real data from /api/v1/budgets/allocated
- Status indicators working

---

### HOUR 7: Forecast & Alerts (3:00-4:00 PM)

**Tasks:**

1. Add Forecast section
   - Burn rate calculation
   - End-of-month projection
   - Days until runout
2. Add Alerts section
   - Show triggered alerts
   - Alert threshold configuration
   - Dismiss/Acknowledge alerts

**Deliverables:**

- Forecast cards showing projections
- Alerts list with badges
- Configuration modal for thresholds

---

### HOUR 8: Testing & Deployment (4:00-5:00 PM)

**Tasks:**

1. End-to-end testing
2. Role-based access verification
3. Data accuracy check
4. Performance verification
5. Bug fixes
6. Demo & documentation

**Deliverables:**

- Fully working system
- All roles tested
- Ready for production

---

## 📊 DATABASE SCHEMA

I'll create these tables:

### 1. Budget Allocation Table

```
Stores budget allocations by hierarchy level
- allocation_id (PK)
- parent_hierarchy (company/pharmacy/branch)
- parent_id (warehouse_id)
- child_hierarchy (pharmacy/branch/none)
- child_id (warehouse_id)
- period (YYYY-MM)
- allocated_amount (SAR)
- allocation_method (equal/proportional/custom)
- allocated_by_user
- allocated_at
- is_active
```

### 2. Budget Tracking Table

```
Tracks actual spending vs budget
- tracking_id (PK)
- allocation_id (FK)
- actual_spent (SAR from fact table)
- percentage_used
- status (safe/warning/danger)
- calculated_at
```

### 3. Budget Forecast Table

```
Stores forecast calculations
- forecast_id (PK)
- allocation_id (FK)
- burn_rate_daily (SAR/day)
- projected_total (end of period)
- days_remaining
- confidence_score
- calculated_at
```

### 4. Budget Alert Configuration Table

```
Stores alert thresholds
- alert_id (PK)
- allocation_id (FK)
- threshold_pct (50/75/90/100)
- trigger_count
- is_active
- recipients (JSON)
- channels (email/sms/in-app)
```

### 5. Budget Audit Trail Table

```
Tracks all budget changes
- audit_id (PK)
- allocation_id (FK)
- action (created/updated/deleted)
- old_value / new_value
- changed_by_user
- changed_at
```

### 6. View: view_budget_vs_actual

```
Shows budget vs actual comparison
- hierarchy_level
- entity_id
- entity_name
- period
- allocated_amount
- actual_spent
- remaining
- percentage_used
- status
- profit_margin (from fact table)
```

---

## 🔐 ROLE-BASED ACCESS CONTROL

```sql
Admin Role (user_role = 'admin'):
├─ Can see: ALL data
├─ Can allocate: Any level to any level
└─ Can delete: Any allocation

Finance Role (user_role = 'finance'):
├─ Can see: Company level only
├─ Can allocate: Company to Pharmacy only
└─ Cannot delete

Pharmacy Manager (user_role = 'pharmacy_manager', assigned_pharmacy_id):
├─ Can see: Own pharmacy only
├─ Can allocate: Pharmacy to Branch only
└─ Cannot delete

Branch Manager (user_role = 'branch_manager', assigned_branch_id):
├─ Can see: Own branch only
├─ Can allocate: NO (read-only)
└─ Cannot delete
```

---

## 🚀 API ENDPOINTS

### 1. Budget Allocation

```
POST /api/v1/budgets/allocate
├─ Body: {
├─   allocation_method: 'equal|proportional|custom',
├─   from_hierarchy: 'company|pharmacy|branch',
├─   from_id: warehouse_id,
├─   to_hierarchy: 'pharmacy|branch',
├─   period: '2025-10',
├─   allocations: [
├─     { child_id: 101, amount: 50000 },
├─     { child_id: 102, amount: 50000 }
├─   ]
├─ }
└─ Returns: { success, allocation_id, message }

GET /api/v1/budgets/allocated
├─ Query: ?period=2025-10&hierarchy=pharmacy
├─ Returns: [
├─   {
├─     allocation_id,
├─     parent_name, child_name,
├─     allocated_amount,
├─     period,
├─     allocation_method,
├─     allocated_at
├─   }
├─ ]
```

### 2. Budget Tracking

```
GET /api/v1/budgets/tracking
├─ Query: ?period=2025-10&hierarchy=pharmacy&entity_id=1
├─ Returns: {
├─   entity_name,
├─   allocated_amount,
├─   actual_spent (from fact table),
├─   remaining,
├─   percentage_used,
├─   status: 'safe|warning|danger',
├─   trend: { last_month, prev_month, % change }
├─ }

PUT /api/v1/budgets/tracking/{allocation_id}
├─ Body: { actual_spent, status }
└─ Returns: { success, message }
```

### 3. Forecasting

```
GET /api/v1/budgets/forecast
├─ Query: ?allocation_id=1&period=2025-10
├─ Returns: {
├─   allocation_id,
├─   period,
├─   burn_rate_daily,
├─   days_used,
├─   days_remaining,
├─   projected_total,
├─   status: 'on-track|warning|danger',
├─   confidence_score,
├─   recommendation: "text"
├─ }
```

### 4. Alert Management

```
GET /api/v1/budgets/alerts
├─ Query: ?period=2025-10
├─ Returns: [
├─   {
├─     alert_id,
├─     allocation_id,
├─     entity_name,
├─     threshold_pct,
├─     triggered_at,
├─     status: 'active|resolved'
├─   }
├─ ]

POST /api/v1/budgets/alerts/configure
├─ Body: {
├─   allocation_id,
├─   thresholds: [50, 75, 90, 100],
├─   channels: ['email', 'in-app']
├─ }
└─ Returns: { success, alert_id }
```

---

## 💾 DATABASE MIGRATION FILE

I'll create: `app/migrations/003_create_budget_tables.php`

This will:

1. Create all 6 budget tables
2. Add foreign keys to existing tables
3. Create views for budget vs actual
4. Add indexes for performance
5. Insert sample data for testing

---

## 🎨 DASHBOARD CHANGES

### Current State

```
Dashboard (Cost Center) with mock data
├─ KPI Cards (Sales, Expenses, etc.)
├─ Trend Chart
└─ Performance Insights
```

### After Phase 1 (Real Data)

```
Dashboard with REAL data
├─ KPI Cards (connected to API)
├─ Trend Chart (real data)
└─ Performance Insights
```

### After Phase 2 (Complete)

```
Enhanced Dashboard with Budget Management
├─ KPI Cards (real data) ✅
├─ Trend Chart (real data) ✅
├─ Budget Allocation Form (NEW)
├─ Budget vs Actual Cards (NEW)
│  ├─ Green card: Safe zone
│  ├─ Yellow card: Warning (>80%)
│  └─ Red card: Exceeded (>100%)
├─ Forecast Section (NEW)
│  ├─ Burn rate card
│  ├─ Projected total card
│  └─ Days remaining card
├─ Alerts Section (NEW)
│  ├─ Active alerts
│  ├─ Alert configuration
│  └─ Alert history
└─ Performance Insights ✅
```

---

## 🔄 IMPLEMENTATION SEQUENCE

### Phase 1 (Hour 1-4): Foundation

1. Database schema + migrations
2. Views + budget tracking logic
3. API model + basic endpoints
4. Dashboard real data connection

### Phase 2 (Hour 5-8): Budget Features

1. Allocation form + validation
2. Budget tracking cards
3. Forecast section
4. Alerts section
5. Testing + deployment

---

## 📁 FILES TO CREATE

1. **Migration:** `app/migrations/003_create_budget_tables.php`
2. **Model:** `app/models/admin/Budget_model.php`
3. **Controller:** `app/controllers/api/v1/Budgets.php`
4. **Helper:** `app/helpers/budget_helper.php`
5. **Updated View:** `themes/blue/admin/views/cost_center/cost_center_dashboard.php` (Phase 1)
6. **Documentation:** `PHASE1_DAY1_EXECUTION.md`

---

## ✅ DELIVERABLES BY END OF DAY

1. ✅ All budget tables created + populated
2. ✅ Budget views created
3. ✅ 4 API endpoint groups working
4. ✅ Dashboard showing real cost center data
5. ✅ Budget allocation form functional
6. ✅ Budget vs actual tracking working
7. ✅ Forecast calculations working
8. ✅ Alert thresholds configurable
9. ✅ Role-based access implemented
10. ✅ End-to-end tested
11. ✅ Demo ready for stakeholders
12. ✅ Production deployment completed

---

## 🎯 SUCCESS CRITERIA

**By 5:00 PM Today:**

- [ ] Dashboard shows real pharmacy KPIs (not mock)
- [ ] Budget allocation form submits successfully
- [ ] Budget vs actual cards show real data
- [ ] Forecast section calculates correctly
- [ ] Alerts trigger at correct thresholds
- [ ] Admin sees all data
- [ ] Pharmacy manager sees only own pharmacy
- [ ] Branch manager sees only own branch
- [ ] All 4 roles tested and working
- [ ] Zero mock data
- [ ] Ready for production

---

## 📞 QUESTIONS BEFORE WE START

1. Database backup taken?
2. Can we use test environment first?
3. Who approves schema changes?
4. Any existing budget data to migrate?
5. Should we keep old cost center dashboard or replace?

**I'm ready to start immediately. Let me know if you have any questions or need modifications to the plan.**

---

**Status:** Ready for execution  
**Start Time:** Whenever you confirm  
**Duration:** 8 hours (1 day)  
**Expected Completion:** End of day
