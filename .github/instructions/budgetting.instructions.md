---
applyTo: "**"
---

# GitHub Copilot System Instructions
## Avnzor ERP: Customer Loyalty & Promotion Engine - Budgeting UI

**Version:** 1.0  
**Purpose:** Build 10X better budgeting interfaces for discount/promotion management  
**Stack:** React 18+, TypeScript, Tailwind CSS, Recharts, Zustand  
**Design Philosophy:** Jobs-like simplicity with institutional-grade control  
**Date:** October 2025

---

## I. VISION & PRINCIPLES FOR BUDGETING UI

### Mission Statement
Create **intuitive, powerful budgeting interfaces** that empower finance teams and branch managers to:
- Visualize discount/promotion spend in real-time
- Allocate budgets across hierarchy levels with granular control
- Make data-driven decisions with predictive insights
- Maintain compliance and auditability with zero manual effort

### Core Principles

**1. Clarity Over Features**
- One number should tell the whole story (% of budget used)
- Hide complexity in progressive disclosure
- Use color psychology: Green (safe), Yellow (warning), Red (exceeded)

**2. Trust Through Transparency**
- Every number is traceable to source transaction
- Drill-down to individual discount records
- Show assumptions (e.g., "Based on last 7 days")

**3. Jobs-Like Design (Simple But Powerful)**
- Make 80% of tasks accomplishable in < 30 seconds
- 20% of power features available in advanced mode
- Keyboard shortcuts for power users
- Natural language search and filtering

**4. Real-Time Reactivity**
- Live budget updates without page refresh
- Instant feedback on allocation changes
- WebSocket-based notifications for budget alerts
- Historical trends updating in real-time

**5. Hierarchical Intelligence**
- Parent budgets cascade to children with override capability
- Conflict detection (e.g., "Exceeds group budget by 5%")
- Permission-aware UI (show only what user can edit)

---

## II. BUDGETING UI COMPONENTS ARCHITECTURE

### Component Hierarchy

```
BudgetingModule/
├── Pages/
│   ├── BudgetDashboard.tsx
│   │   └── Company-level overview
│   ├── BudgetAllocation.tsx
│   │   └── Allocate budgets across hierarchy
│   ├── BudgetTracking.tsx
│   │   └── Real-time expense tracking
│   ├── BudgetForecasting.tsx
│   │   └── Predictive analytics
│   └── BudgetCompliance.tsx
│       └── Audit trail, alerts, rules
│
├── Components/
│   ├── BudgetCard/
│   │   ├── BudgetCard.tsx (primary component)
│   │   ├── BudgetCard.module.css
│   │   ├── BudgetCardLarge.tsx
│   │   └── BudgetCard.stories.tsx
│   │
│   ├── BudgetMeter/
│   │   ├── BudgetMeter.tsx (circular progress)
│   │   ├── LinearBudgetMeter.tsx (horizontal progress)
│   │   └── BudgetMeter.stories.tsx
│   │
│   ├── BudgetChart/
│   │   ├── TrendChart.tsx (line chart)
│   │   ├── SpendingBreakdown.tsx (pie/stacked bar)
│   │   ├── AllocationTree.tsx (hierarchy visualization)
│   │   └── BudgetChart.stories.tsx
│   │
│   ├── BudgetForm/
│   │   ├── AllocateBudgetForm.tsx
│   │   ├── AdjustBudgetForm.tsx
│   │   ├── SetAlertThresholdForm.tsx
│   │   └── BudgetForm.stories.tsx
│   │
│   ├── BudgetTable/
│   │   ├── BudgetBreakdownTable.tsx
│   │   ├── TransactionDetailTable.tsx
│   │   ├── BudgetHistoryTable.tsx
│   │   └── BudgetTable.stories.tsx
│   │
│   ├── BudgetAlert/
│   │   ├── BudgetWarning.tsx
│   │   ├── BudgetExceeded.tsx
│   │   ├── BudgetForecast.tsx
│   │   └── BudgetAlert.stories.tsx
│   │
│   └── HierarchyBudgetManager/
│       ├── HierarchyTree.tsx
│       ├── BudgetComparison.tsx
│       └── HierarchyBudgetManager.stories.tsx
│
├── Hooks/
│   ├── useBudget.ts (fetches budget data)
│   ├── useBudgetAllocation.ts (manages allocations)
│   ├── useBudgetTracking.ts (real-time tracking)
│   ├── useBudgetForecast.ts (predictive)
│   ├── useBudgetAlerts.ts (WebSocket alerts)
│   └── useBudgetValidation.ts (constraint checking)
│
├── State/
│   ├── budgetStore.ts (Zustand store)
│   ├── budgetSelectors.ts
│   └── budgetActions.ts
│
├── Types/
│   ├── budget.types.ts
│   ├── allocation.types.ts
│   ├── spending.types.ts
│   └── hierarchy.types.ts
│
├── API/
│   ├── budgetAPI.ts (fetch/update budgets)
│   ├── allocationAPI.ts (allocate budgets)
│   ├── trackingAPI.ts (real-time tracking)
│   ├── forecastingAPI.ts (predictions)
│   └── alertsAPI.ts (WebSocket)
│
├── Utils/
│   ├── budgetCalculations.ts
│   ├── budgetFormatting.ts
│   ├── budgetValidation.ts
│   ├── forecastAlgorithm.ts
│   └── budgetChartData.ts
│
└── Assets/
    ├── icons/
    └── illustrations/
```

---

## III. CORE BUDGETING UI COMPONENTS

### Component 1: BudgetCard (Primary Card Component)

```typescript
/**
 * COMPONENT: BudgetCard
 * 
 * Purpose: Display single budget summary with status
 * Props: BudgetCardProps {
 *   title: string              // "Q4 Discount Budget"
 *   allocated: number          // 50,000 (SAR)
 *   spent: number              // 12,500 (SAR)
 *   remaining: number          // 37,500 (SAR)
 *   percentage: number         // 25 (%)
 *   status: 'safe' | 'warning' | 'exceeded' | 'not-started'
 *   trend: number              // +5 (% change from last period)
 *   hierarchyLevel?: string    // "PHARMACY_GROUP"
 *   currency?: string          // "SAR"
 *   showDetails?: boolean
 *   onDetailClick?: () => void
 *   onEditClick?: () => void
 * }
 * 
 * Features:
 * - Circular progress indicator (color-coded)
 * - SAR display with separator (50,000 SAR)
 * - Trend indicator (up/down arrow)
 * - Status badge (Safe/Warning/Exceeded)
 * - Detail drill-down button
 * - Jobs-like simplicity: One glance tells the story
 * 
 * Design:
 * - Card width: 300px (fits 3-column grid)
 * - Metric: Progress ring 80px diameter
 * - Responsive: Stack on mobile
 * - Animations: Smooth progress fill (0.5s ease)
 */

// Copilot Prompt:
// "Create BudgetCard component that:
// 1. Shows allocated/spent/remaining in prominent display
// 2. Circular progress meter (0-100%)
// 3. Color-coded: Green <50%, Yellow 50-80%, Orange 80-95%, Red >95%
// 4. Shows trend indicator (↑↓) with percentage change
// 5. Status badge (Safe / Warning / Exceeded / Not Started)
// 6. Clickable to show detail breakdown
// 7. Edit button to adjust budget
// 8. Responsive: 300px on desktop, full-width on mobile
// 9. Dark mode support using Tailwind
// 10. Storybook stories for all states
// 
// Props interface with full JSDoc.
// Use Recharts for progress indicator or CSS circles.
// Include loading skeleton."
```

### Component 2: BudgetMeter (Progress Indicator)

```typescript
/**
 * COMPONENT: BudgetMeter
 * 
 * Variations:
 * 1. Circular Meter (default)
 *    - Center: Percentage (25%)
 *    - Ring: Color-coded progress
 *    - Outer: Label (e.g., "Q4 Spend")
 * 
 * 2. Linear Meter
 *    - Horizontal bar
 *    - Stacked sections for allocated/spent/remaining
 *    - Labels: Left (allocated), Center (spent %), Right (remaining)
 * 
 * Features:
 * - Animated fill (smooth transition)
 * - Hover tooltip showing exact amounts
 * - Click to expand detail
 * - Color states: safe, warning, exceeded
 * 
 * Colors:
 * - 0-50%: Green (#10B981)
 * - 50-80%: Yellow (#F59E0B)
 * - 80-95%: Orange (#FB923C)
 * - 95-100%: Red (#EF4444)
 * - >100%: Dark Red (#991B1B)
 */

// Copilot Prompt:
// "Create BudgetMeter components (circular + linear) that:
// 1. Circular version:
//    - 120px diameter by default, configurable
//    - Center text: percentage (e.g., '35%')
//    - SVG-based with smooth animation (CSS Transition)
//    - Color coded: green < 50%, yellow 50-80%, orange 80-95%, red >95%
//    - Hover shows tooltip: 'Spent: 17,500 SAR of 50,000 SAR'
// 
// 2. Linear version:
//    - Full width horizontal bar
//    - Left: allocated amount
//    - Middle: spent (colored bar)
//    - Right: remaining amount
//    - Stacked bars for visual breakdown
// 
// 3. Both versions:
//    - Accept size prop (sm/md/lg)
//    - Support dark/light mode
//    - Animate on mount (0.6s ease-out)
//    - Include loading state (skeleton)
// 
// Use pure CSS + Tailwind or Recharts.
// Include TypeScript types with JSDoc.
// Add Storybook stories for all variants."
```

### Component 3: BudgetChart (Visualization)

```typescript
/**
 * COMPONENT: BudgetChart (Multi-purpose)
 * 
 * Variations:
 * 1. TrendChart
 *    - Line chart: Daily spending trend over 30 days
 *    - X-axis: Dates
 *    - Y-axis: Amount spent (SAR)
 *    - Horizontal line: Budget allocation average
 *    - Shaded area: Safe zone vs warning zone
 * 
 * 2. SpendingBreakdown
 *    - Pie chart: Spending by category
 *    - Or: Stacked bar chart by branch/pharmacy/group
 *    - Donut chart: Discount types (percentage, fixed, BOGO)
 * 
 * 3. AllocationTree
 *    - Treemap: Hierarchy visualization
 *    - Size: Allocated budget per node
 *    - Color: Usage percentage
 *    - Parent (Company) → Children (Groups) → Grandchildren (Pharmacies)
 * 
 * Features:
 * - Interactive tooltips on hover
 * - Click to drill-down into detail
 * - Legend with toggle visibility
 * - Export as PNG/PDF
 * - Responsive (mobile: single metric, desktop: full chart)
 * 
 * Responsive Behavior:
 * - Desktop (>1024px): Full width chart with legend
 * - Tablet (768-1024px): Smaller chart, rotated legend
 * - Mobile (<768px): Key metric only, tap for full chart
 */

// Copilot Prompt:
// "Create budget chart components using Recharts:
// 
// 1. TrendChart (LineChart + AreaChart):
//    - Props: data[], period (7d/30d/90d), budgetLine
//    - X-axis: Dates (formatted as 'Oct 15')
//    - Y-axis: Amount in SAR with thousand separator
//    - Reference line: budgetLine color (dashed, gray)
//    - Area fill gradient: Safe zone (green) to warning (red)
//    - Tooltip: Date, Amount, % of budget
//    - Responsive: Full height on desktop, reduced on mobile
// 
// 2. SpendingBreakdown (PieChart or BarChart):
//    - Props: breakdown[] { category, amount, percentage }
//    - Colors: Different hue for each category
//    - Labels: Category name + percentage
//    - Tooltip: Category, Amount, % of total
//    - Pie: Donut chart by default
//    - Bar: Stacked by category or grouped
// 
// 3. AllocationTree (Treemap):
//    - Props: hierarchy nodes with budget allocation
//    - Size: Represents allocated budget
//    - Color: Green (0-50%), Yellow (50-80%), Orange (80-95%), Red (>95%)
//    - Click: Drill-down to children
//    - Breadcrumb: Show current level
// 
// All charts:
// - Responsive container (ResponsiveContainer)
// - Dark mode: Invert colors
// - Export button: Download as PNG
// - Legend toggles series visibility
// - Loading state: Skeleton loaders"
```

### Component 4: BudgetAllocationForm

```typescript
/**
 * COMPONENT: BudgetAllocationForm
 * 
 * Purpose: Allocate/reallocate budgets across hierarchy
 * 
 * Workflow:
 * 1. User selects hierarchy level (Company/Group/Pharmacy/Branch)
 * 2. System shows available budget from parent
 * 3. User distributes to children (sliders or text inputs)
 * 4. Real-time validation: Total cannot exceed parent
 * 5. Confirm and publish
 * 
 * Features:
 * - Drag-to-adjust sliders for each child
 * - Percentage split view (easier mental model)
 * - Smart allocation presets:
 *   * Equal distribution (divide evenly)
 *   * Proportional to past spending
 *   * Proportional to transaction count
 *   * Custom manual
 * - Allocation visualization (horizontal bars)
 * - Conflict warnings (e.g., "Exceeds company budget by 5,000")
 * - Undo/Reset buttons
 * - Preview: Show impact before confirming
 * 
 * Form Fields:
 * - Parent Budget (read-only): "Company Budget: 500,000 SAR"
 * - Period: Dropdown (Monthly / Quarterly / Annual)
 * - Distribution Method: Radio buttons
 *   * Equal Split
 *   * Proportional to Spending
 *   * Proportional to Sales
 *   * Custom
 * - Child Allocations: Dynamic list of sliders/inputs
 * - Remaining Unallocated: Display at bottom
 * - Conflict Messages: Red banner if exceeding parent
 */

// Copilot Prompt:
// "Create BudgetAllocationForm component:
// 
// 1. Hierarchy Navigation:
//    - Breadcrumb showing current level: Company > Group > Pharmacy
//    - Dropdowns to jump to different hierarchy nodes
//    - Show parent budget prominently at top
// 
// 2. Distribution Method Selector:
//    - Radio group: Equal | Proportional (Spending) | Proportional (Sales) | Custom
//    - On selection, auto-populate allocations
//    - Equal: Each child gets parent/childCount
//    - Proportional spending: Weight by past 30-day spending
//    - Proportional sales: Weight by transaction count
// 
// 3. Allocation Inputs (per child):
//    - Row for each child: Name | Slider | Input | %
//    - Slider: 0-100% of parent budget
//    - Input: Accept numbers (comma-separated)
//    - Percentage: Auto-calculated
//    - Drag to adjust proportionally
// 
// 4. Visualization:
//    - Stacked horizontal bar showing allocations
//    - Colors: Different hue per child
//    - Remaining: Gray section at right
//    - Hover: Show name + amount + % of parent
// 
// 5. Validation & Warnings:
//    - Real-time calculation: Total allocated
//    - Warning banner if total < 100% (unallocated)
//    - Error banner if total > parent budget (highlight in red)
//    - Disable Save button until valid
// 
// 6. Actions:
//    - Reset button: Revert to previous allocations
//    - Undo button: Cancel changes
//    - Preview button: Show impact simulation
//    - Save button: Confirm and publish
// 
// Use React Hook Form for form state.
// Implement real-time validation.
// Include TypeScript types and JSDoc."
```

### Component 5: BudgetTracking (Real-time)

```typescript
/**
 * COMPONENT: BudgetTracking (Live Dashboard)
 * 
 * Purpose: Monitor real-time spending against budget
 * Refresh: Live updates via WebSocket (every 5-10 seconds)
 * 
 * Layout:
 * - Header: Time range selector (Today / Week / Month / Custom)
 * - KPI Row: 4 key metrics
 * - Breakdown: Tables and charts
 * - Alerts: Alert section
 * 
 * KPI Metrics:
 * 1. Total Budget (Selected Period)
 *    - Display: "500,000 SAR"
 *    - Comparison: "vs 450,000 last month (-10%)"
 * 
 * 2. Total Spent
 *    - Display: "125,000 SAR"
 *    - Percentage of budget: "25%"
 *    - Comparison: "vs 100,000 last month (+25%)"
 * 
 * 3. Remaining Budget
 *    - Display: "375,000 SAR"
 *    - Days remaining in period
 *    - Burn rate (daily)
 * 
 * 4. Forecast (End of Period)
 *    - Based on current burn rate
 *    - "Projected: 295,000 SAR spent by month-end"
 *    - Over/under budget indicator
 * 
 * Breakdown Section:
 * - By Branch: Table of all branches, spending, budget, %
 * - By Category: Discount type (percentage, fixed, BOGO)
 * - By Rule: Top spending rules
 * - By Product: Top products receiving discounts
 * 
 * Chart:
 * - TrendChart: Daily spending over selected period
 * - Benchmark line: Budget allocation / days in period
 * - Shaded zones: Safe (green), warning (yellow), danger (red)
 * 
 * Alerts:
 * - Budget exceeded warnings
 * - High burn rate alerts
 * - Unusual spending patterns
 */

// Copilot Prompt:
// "Create BudgetTracking dashboard component:
// 
// 1. Time Range Selector:
//    - Radio buttons: Today | This Week | This Month | Custom
//    - Custom: Date picker (from/to)
//    - On change: Re-fetch data, animate charts
// 
// 2. KPI Cards (4 cards):
//    - Card 1: Total Budget (500,000 SAR, trend arrow, % change)
//    - Card 2: Total Spent (125,000 SAR, 25% of budget, trend)
//    - Card 3: Remaining (375,000 SAR, days left, daily burn rate)
//    - Card 4: Forecast (295,000 SAR projected, on/over budget flag)
//    - Use BudgetCard component with specific sizes
//    - Smooth number transitions (0.3s ease-out)
// 
// 3. Breakdown Section (Tabs):
//    - Tab 1: By Branch (table with sort/filter)
//    - Tab 2: By Category (pie chart + table)
//    - Tab 3: By Rule (top 10 rules table)
//    - Tab 4: By Product (top products spending)
//    - Each tab: Search, sort columns, export
// 
// 4. Trend Chart:
//    - TrendChart component (LineChart)
//    - Period: Daily data points
//    - Reference line: Budget allocation / period days
//    - Shaded: Green zone (0-50%), Yellow (50-80%), Orange (80-95%), Red (>95%)
//    - Interaction: Hover shows exact values
// 
// 5. Alerts Section:
//    - Alert banner if budget exceeded
//    - Warning if >80% of budget spent
//    - Info: High burn rate detected
//    - Actions: Drill-down to detail
// 
// 6. Real-time Updates:
//    - WebSocket connection for live data
//    - useEffect to listen to budget updates
//    - Animated transitions when data changes
//    - Last updated timestamp
// 
// Use custom hooks:
// - useBudgetTracking(period)
// - useBudgetAlerts()
// Implement WebSocket integration.
// Include TypeScript types."
```

### Component 6: BudgetForecast (Predictive)

```typescript
/**
 * COMPONENT: BudgetForecast (Predictive Analytics)
 * 
 * Purpose: Predict end-of-period spending and provide insights
 * 
 * Features:
 * - Burn rate calculation (daily/weekly average)
 * - Extrapolation: Project to end of period
 * - Scenario modeling:
 *   * Best case: Assuming 20% reduction
 *   * Current trend: Based on actual burn rate
 *   * Worst case: Assuming 20% increase
 * - Confidence score (based on data quality)
 * - Recommendations:
 *   * "At current rate, you'll spend X by end of month"
 *   * "You can safely allocate 20,000 more"
 *   * "Reduce spending by 5% to stay under budget"
 * 
 * Visualization:
 * - Area chart with three scenarios (best/current/worst)
 * - Horizontal line: Budget cap
 * - Shaded zones: Safe vs exceeded
 * - Hover: Show exact projected amount
 * - Click scenario: See detailed breakdown
 * 
 * Metrics:
 * - Current Burn Rate: 5,000 SAR/day
 * - Days Used: 10 of 30
 * - Days Remaining: 20
 * - Projected Total (current): 100,000 SAR
 * - Budget Cap: 150,000 SAR
 * - Remaining Capacity: 50,000 SAR
 */

// Copilot Prompt:
// "Create BudgetForecast component for predictive analytics:
// 
// 1. Burn Rate Calculation:
//    - Collect spending data for period so far
//    - Calculate daily average
//    - Calculate weekly average
//    - Display: '5,000 SAR/day'
// 
// 2. Scenario Projections:
//    - Best case: Burn rate - 20%
//    - Current trend: Actual burn rate
//    - Worst case: Burn rate + 20%
//    - Calculate end-of-period projections for each
// 
// 3. Visualization (AreaChart with 3 series):
//    - X-axis: Days of period (0-30)
//    - Y-axis: Cumulative spending (SAR)
//    - Series 1 (Worst, red): Highest projection
//    - Series 2 (Current, blue): Mid projection
//    - Series 3 (Best, green): Lowest projection
//    - Reference line: Budget cap (dashed black)
//    - Today marker: Current day position (vertical line)
// 
// 4. Metrics Display:
//    - Burn rate (daily, weekly)
//    - Days used / Days remaining
//    - Projected total @ current rate
//    - Budget remaining
//    - Confidence score (% based on data points)
// 
// 5. Recommendations (AI-driven):
//    - If projected > budget: 'Reduce spending by X% to stay under'
//    - If projected < budget: 'Opportunity to allocate X more'
//    - If burn rate trending up: 'Spending accelerating, monitor daily'
//    - If burn rate trending down: 'Good control, maintain current pace'
// 
// 6. Interactive Elements:
//    - Click scenario to see breakdown
//    - Toggle scenarios on/off
//    - Hover for exact values
//    - Export forecast as PDF
// 
// Use Recharts AreaChart with multiple series.
// Implement forecast algorithm in utils/forecastAlgorithm.ts
// Include confidence score calculation.
// Add TypeScript types and JSDoc."
```

### Component 7: BudgetCompliance (Audit Trail)

```typescript
/**
 * COMPONENT: BudgetCompliance (Audit & Rules)
 * 
 * Purpose: Track all budget changes, approvals, and violations
 * 
 * Features:
 * 1. Audit Trail Table
 *    - Columns: Date | User | Action | Old Value | New Value | Status
 *    - Actions: Budget allocated, allocation adjusted, alert threshold changed
 *    - Sortable, filterable
 *    - Export as CSV/PDF
 * 
 * 2. Budget Rules / Constraints
 *    - Company budget: Fixed cap
 *    - Group budgets: Sum <= company budget
 *    - Pharmacy budgets: Sum <= group budget
 *    - Branch budgets: Sum <= pharmacy budget
 *    - Display violations prominently
 * 
 * 3. Approval Workflows
 *    - Show pending approvals (if applicable)
 *    - Who approved what and when
 *    - Approval chain: Branch → Pharmacy → Group → Company
 * 
 * 4. Alert Rules
 *    - Budget exceeds 50%, 75%, 90%, 100%
 *    - Unusual spending patterns (deviation > 2 std dev)
 *    - Alert recipients: Pharmacy manager, group manager, finance team
 *    - Alert channels: Email, SMS, in-app
 * 
 * 5. Compliance Report
 *    - Summary: All budgets on track
 *    - Violations: List of rule breaches
 *    - Overdue approvals: Pending decisions
 *    - Risk assessment: High-risk branches/groups
 */

// Copilot Prompt:
// "Create BudgetCompliance component for audit & compliance:
// 
// 1. Audit Trail Table:
//    - Props: auditEvents[] with Date, User, Action, OldValue, NewValue, Status
//    - Columns sortable: Date (desc default), User, Action, Change
//    - Filters: Date range, User, Action type
//    - Expandable rows: Show full details
//    - Export: CSV, PDF formats
//    - Pagination: 50 rows per page
// 
// 2. Budget Hierarchy Rules Display:
//    - Tree view showing rules
//    - Company: 'Max 500,000 SAR/month'
//    - Group: 'Sum of children <= parent budget'
//    - Pharmacy: 'Sum of branches <= pharmacy budget'
//    - Branch: 'Cannot exceed pharmacy allocation'
//    - Violations highlighted in red with details
// 
// 3. Approval Workflow (if multi-level approvals):
//    - Show pending approvals: 'Awaiting Group approval'
//    - Approval chain: Branch → Pharmacy → Group → Company
//    - Include approver, date, status
//    - Approve/Reject buttons (if user is approver)
// 
// 4. Alert Configuration:
//    - Table of alert rules:
//    - Threshold | Recipients | Channel | Status (enabled/disabled)
//    - Add/Edit/Delete alert rules
//    - Test alert: Send sample notification
//    - Recipient groups: Pharmacy Manager, Finance, Admin
// 
// 5. Compliance Summary Section:
//    - 3-box summary:
//      * Total Budgets: X on track, Y violations
//      * Pending Approvals: N items
//      * Risk Level: Green/Yellow/Red
//    - Detailed report exportable as PDF
//    - Risk score for each entity
// 
// 6. Notifications Panel:
//    - Recent alerts: Budget exceeded, threshold breached
//    - Timestamps, severity badges
//    - Mark as read/dismissed
//    - View full audit trail
// 
// Use Table component with sorting/filtering.
// Implement tree component for hierarchy rules.
// WebSocket for real-time audit log updates.
// Include TypeScript types for all entities."
```

---

## IV. COPILOT PROMPTING PATTERNS FOR BUDGETING UI

### Pattern 1: Create Complete Budget Component with States

```
Prompt:
"Create a [COMPONENT_NAME] component for budgeting that:

1. Visual Design (Jobs-like simplicity):
   - Use Tailwind CSS for styling
   - Specific color palette:
     * Green: #10B981 (safe, 0-50%)
     * Yellow: #F59E0B (warning, 50-80%)
     * Orange: #FB923C (alert, 80-95%)
     * Red: #EF4444 (danger, 95-100%)
     * Dark Red: #991B1B (exceeded)
   - Font sizes: sm/md/lg/xl/2xl, weights: 400/500/700
   - Spacing: Use Tailwind 8px grid (p-4, p-8, gap-4, etc)

2. Component States:
   - Loading: Show skeleton loader
   - Loaded: Display data with transitions
   - Error: Show error message with retry button
   - Empty: Show empty state with helpful message

3. Interactivity:
   - Hover effects on clickable elements
   - Click handlers: [SPECIFY ACTIONS]
   - Keyboard navigation: Tab order, Enter/Space triggers
   - Accessibility: ARIA labels, role attributes

4. Responsiveness:
   - Desktop (>1024px): Full layout
   - Tablet (768-1024px): Adjusted layout
   - Mobile (<768px): Single column, optimized spacing

5. Animation:
   - Entrance: Fade in + slide up (0.3s ease-out)
   - Data updates: Smooth transitions (0.4s ease-out)
   - Hover: Color change (0.2s ease-out)

6. TypeScript Types:
   - Create interface [COMPONENT_NAME]Props
   - Include JSDoc for all props
   - Use strict mode, no 'any' types

7. Testing & Storybook:
   - Create Storybook stories for all states
   - Include responsive canvas decorator
   - Dark mode toggle in toolbar

Use React 18 with TypeScript strict mode.
Implement with Tailwind CSS.
Include comprehensive JSDoc comments.
No external UI libraries (headless only).
"
```

### Pattern 2: Create Budget Data Hook

```
Prompt:
"Create a custom React hook useBudget[ACTION] that:

1. Data Fetching:
   - Endpoint: GET [API_ENDPOINT]
   - Params: [PARAMETERS]
   - Headers: Authorization, X-Branch-Id, X-Trace-Id
   - Timeout: 5000ms with graceful fallback

2. State Management:
   - Use Zustand store: [STORE_NAME]
   - Actions: fetch, update, reset
   - Selectors: select[METRIC]
   - Store persists to localStorage (optional)

3. Caching Strategy:
   - Cache duration: [DURATION]
   - Invalidation on: [EVENTS]
   - Background refresh: [INTERVAL]

4. Error Handling:
   - Network error: Show toast notification
   - Validation error: Return validation details
   - Server error (5xx): Retry with exponential backoff

5. Real-time Updates (if applicable):
   - WebSocket connection
   - Listen to events: [EVENT_LIST]
   - Update state on event
   - Reconnection logic with exponential backoff

6. Return Value:
   - data: T
   - loading: boolean
   - error: Error | null
   - refetch: () => Promise<void>
   - [ADDITIONAL_METHODS]

Include TypeScript generics.
Add JSDoc with usage examples.
Handle edge cases and race conditions.
"
```

### Pattern 3: Create Complex Form with Validation

```
Prompt:
"Create form component [FORM_NAME] with React Hook Form:

1. Form Fields:
   [LIST FIELDS WITH TYPES]
   - Field: type, validation rules, placeholder, help text
   - Use input | select | textarea | date-picker | slider

2. Validation Rules:
   - Client-side: zod schema validation
   - Server-side: Validation errors from API
   - Real-time: Validate as user types
   - Submission: Prevent submit if invalid

3. User Experience:
   - Clear error messages below each field
   - Show required indicator (*)
   - Auto-focus first invalid field on submit
   - Disable submit button while submitting
   - Show loading spinner on button during submission
   - Success toast after submission

4. Advanced Features:
   - Conditional field display: Show if [CONDITION]
   - Dynamic field arrays: Add/remove items
   - Cross-field validation: Field A must be > Field B
   - Smart defaults: Pre-populate from context

5. Accessibility:
   - Label every input
   - Use htmlFor on labels
   - Error messages with aria-describedby
   - Keyboard navigation (Tab order)
   - Screen reader support

6. Responsiveness:
   - Desktop: 2 columns
   - Tablet: 1 column with wider inputs
   - Mobile: Full width, large touch targets

Include TypeScript types.
Use React Hook Form best practices.
Add JSDoc and usage examples.
"
```

### Pattern 4: Create Real-time Data Component

```
Prompt:
"Create real-time component [COMPONENT_NAME] with WebSocket:

1. Data Source:
   - WebSocket URL: [URL]
   - Events: [EVENT_LIST]
   - Subscription: [SUBSCRIPTION_PARAMS]
   - Reconnection: Exponential backoff (1s, 2s, 4s, max 30s)

2. State Management:
   - Current data: [STRUCTURE]
   - Connected status: boolean
   - Last update: timestamp
   - Update queue: Handle rapid updates

3. Update Handling:
   - On event: [ACTION]
   - Merge with existing state
   - Animate transitions
   - Keep history for trends (last 60 points)

4. Error Handling:
   - Connection lost: Show banner, retry automatically
   - Invalid data: Log, fallback to last known good state
   - Server error: Graceful degradation

5. Performance Optimization:
   - Debounce updates: [INTERVAL]ms
   - Throttle renders: [INTERVAL]ms
   - Unsubscribe on unmount
   - Cleanup connections

6. Metrics:
   - Message count per second
   - Latency (server time to client render)
   - Memory usage (prevent memory leaks)

Include TypeScript strict mode.
Implement WebSocket connection class.
Add JSDoc with usage examples.
Include error scenarios in tests.
"
```

### Pattern 5: Create Responsive Chart

```
Prompt:
"Create responsive chart component [CHART_NAME] with Recharts:

1. Chart Type:
   - Type: [LineChart/BarChart/PieChart/AreaChart]
   - Props: data[], [CUSTOM_PROPS]
   - Size: ResponsiveContainer (100% width)

2. Responsive Behavior:
   - Desktop (>1024px): Full chart with legend on right
   - Tablet (768-1024px): Full chart, legend below
   - Mobile (<768px): Single metric card (tap to expand chart)

3. Data Visualization:
   - Series: [LIST] (e.g., actual, budget, forecast)
   - Colors: [PALETTE] (consistent with app theme)
   - Tooltip: Show all values on hover
   - Legend: Toggleable series visibility

4. Interactivity:
   - Click: Drill-down or show details
   - Hover: Highlight data point
   - Drag: Pan chart (if applicable)
   - Zoom: Optional zoom controls

5. Accessibility:
   - Chart title and description
   - Keyboard navigation (arrow keys)
   - Screen reader support (data table fallback)
   - High contrast mode support

6. Performance:
   - Memoize component: React.memo()
   - Lazy load large datasets
   - Virtualize long series
   - Debounce resize events

Use Recharts library.
Include TypeScript types.
Add JSDoc and storybook stories.
"
```

---

## V. BUDGETING UI IMPLEMENTATION WORKFLOW

### Week 1: Foundation Components (Days 1-3)

#### Day 1: Core Components
```
Prompt for Copilot:
"Create core budgeting components in this order:

1. BudgetCard.tsx
   - Show allocated/spent/remaining
   - Circular progress meter
   - Color-coded status badges
   - Responsive grid layout
   - Accept onClick for detail view

2. BudgetMeter.tsx (circular variant)
   - 120px diameter by default
   - Center text: percentage
   - SVG-based animation
   - Configurable colors by percentage
   - Hover tooltip

3. LinearBudgetMeter.tsx
   - Horizontal bar layout
   - Left: allocated, Center: spent, Right: remaining
   - Stacked visualization
   - Color-coded sections
   
For each:
- Create component file
- Create TypeScript types file
- Create Storybook stories (3 states: loading, loaded, error)
- Add unit tests (happy path + edge cases)
- Include JSDoc comments

Use Tailwind CSS for styling.
Target browser support: Chrome 90+, Firefox 88+, Safari 14+.
"
```

#### Day 2: Data Hooks & State Management
```
Prompt:
"Create budget data layer:

1. Zustand Store (budgetStore.ts)
   - State:
     * budgets: Budget[]
     * allocations: Allocation[]
     * spending: Spending[]
     * alerts: Alert[]
   - Actions:
     * setBudgets(), setAllocations(), setSpending()
     * addAlert(), removeAlert()
     * updateBudget(), resetBudgets()
   - Selectors: 
     * selectBudgetByHierarchy()
     * selectTotalSpent()
     * selectRemainingBudget()

2. useB budgetTracking() hook
   - Fetches budget data from API
   - Caches with 5-minute TTL
   - Supports period selection (today/week/month)
   - Returns: { data, loading, error, refetch }
   - WebSocket integration for real-time updates

3. useBudgetAllocation() hook
   - Manages form state for budget allocation
   - Validates: Total <= parent budget
   - Calculates: Equal/proportional splits
   - Returns: { allocations, errors, onchange, onsubmit }

For each:
- Implement in TypeScript with strict mode
- Add error handling and logging
- Include JSDoc with usage examples
- Create unit tests (happy path + error cases)
"
```

#### Day 3: Charts & Visualization
```
Prompt:
"Create chart components using Recharts:

1. TrendChart.tsx
   - LineChart with budget reference line
   - Period selector: 7d/30d/90d
   - Shaded zones: Safe (green), Warning (yellow), Danger (red)
   - Responsive to mobile
   - Export as PNG

2. SpendingBreakdown.tsx
   - PieChart (donut variant) for categories
   - BarChart (stacked) for branches
   - Dynamic data display
   - Legend with toggle

3. AllocationTree.tsx
   - Treemap visualization of hierarchy
   - Click to drill-down
   - Breadcrumb navigation
   - Color by usage %

For each:
- Responsive container design
- Dark mode support
- Tooltip with detailed information
- Export/download functionality
- Skeleton loading state
- Add Storybook stories
"
```

### Week 2: Forms & Pages (Days 4-7)

#### Day 4: Allocation Form
```
Prompt:
"Create BudgetAllocationForm with React Hook Form:

Features:
- Hierarchy navigation (Company → Group → Pharmacy)
- Distribution methods:
  * Equal split
  * Proportional to spending
  * Proportional to sales
  * Custom manual

Components:
- HierarchySelector: Breadcrumb + dropdown
- DistributionMethodRadio: 4 options
- ChildAllocationSliders: Slider for each child
- AllocationVisualization: Stacked bar chart
- AllocationSummary: Total, remaining, conflicts

Validation:
- Total allocated <= parent budget
- All children > 0 (no zero allocations)
- Show warnings for conflicts
- Real-time calculation

Actions:
- Reset: Revert to initial state
- Preview: Show impact before saving
- Save: Submit to API
- Undo: Revert last change

Use React Hook Form for state.
Zod for validation schema.
Include all edge cases.
"
```

#### Day 5: Budget Tracking Dashboard
```
Prompt:
"Create BudgetTracking dashboard page:

Layout:
- Header: Period selector, refresh button
- KPI Row: 4 cards (Budget, Spent, Remaining, Forecast)
- Tabs: By Branch | By Category | By Rule | By Product
- Chart: TrendChart showing spend over time
- Alerts: Alert section with warnings

Features:
- Real-time updates via WebSocket
- Time period selection (Today/Week/Month/Custom)
- Drill-down from summary to detail
- Table sorting, filtering, search
- Export functionality

Responsive:
- Desktop: Full dashboard
- Tablet: Compact view, stacked tabs
- Mobile: Key metrics only, tap for details

Use custom hooks:
- useBudgetTracking()
- useBudgetAlerts()
- useWebSocket()
"
```

#### Day 6: Forecast Component
```
Prompt:
"Create BudgetForecast predictive component:

Features:
- Calculate daily burn rate
- Project 3 scenarios: Best/Current/Worst
- Confidence score
- Recommendations
- Export forecast

Visualization:
- AreaChart with 3 series
- Reference line: Budget cap
- Today marker
- Shaded safe/warning zones

Metrics:
- Burn rate (daily/weekly)
- Days used/remaining
- Projected total
- Budget headroom
- Risk assessment

Recommendations:
- Automated insights based on trend
- Actionable guidance
- Export as PDF report
"
```

#### Day 7: Compliance Page
```
Prompt:
"Create BudgetCompliance audit & compliance page:

Sections:
1. Audit Trail Table
   - Date, User, Action, Change
   - Filter, sort, export
   - Expandable detail rows

2. Hierarchy Rules Display
   - Tree showing constraints
   - Violations highlighted

3. Approval Workflow (if applicable)
   - Pending approvals
   - Approval chain
   - Approve/Reject actions

4. Alert Configuration
   - Alert rules table
   - Add/edit/delete rules
   - Test alert functionality

5. Compliance Summary
   - Status overview
   - Risk assessment
   - Export report

Use Table component.
Implement audit log real-time updates.
WebSocket for new audit events.
"
```

### Week 3: Integration & Polish (Days 8-10)

#### Day 8: Integration Testing
```
Prompt:
"Create integration tests for budgeting workflows:

Test Scenarios:
1. Allocate budget from company to groups
   - Verify sum doesn't exceed parent
   - Verify all children updated
   - Verify audit trail recorded

2. Apply discount transaction
   - Verify budget tracking updated
   - Verify forecast recalculated
   - Verify alerts triggered if threshold breached

3. Real-time tracking
   - Simulate WebSocket updates
   - Verify dashboard updates
   - Verify performance (< 100ms update)

4. Forecasting
   - Test burn rate calculation
   - Test scenario projections
   - Test confidence score

Use Jest + React Testing Library.
Mock API calls and WebSocket.
Cover happy path + error cases.
"
```

#### Day 9: Performance Optimization
```
Prompt:
"Optimize budgeting UI for performance:

Targets:
- Initial load: < 2s
- Dashboard render: < 500ms
- Real-time update: < 100ms
- Chart render: < 300ms

Optimizations:
1. Code splitting: Lazy load chart libraries
2. Memoization: React.memo() on expensive components
3. State optimization: Normalize Zustand store
4. API caching: 5-minute TTL for budgets
5. Image optimization: SVG icons, no PNG
6. Bundle analysis: Remove unused dependencies
7. Virtualization: Long tables use windowing

Measure with:
- Chrome DevTools Performance tab
- Lighthouse CI
- Bundle size analyzer

Create performance benchmarks.
Document optimization strategies.
"
```

#### Day 10: Documentation & Polish
```
Prompt:
"Complete budgeting UI documentation:

Deliverables:
1. Component library docs
   - Storybook stories for all components
   - Usage examples
   - API documentation

2. Feature guides
   - Budget allocation workflow
   - Real-time tracking guide
   - Forecasting explanation

3. Integration guide
   - API endpoints
   - WebSocket events
   - Error handling

4. Performance guide
   - Optimization techniques
   - Caching strategies
   - Monitoring metrics

5. Accessibility compliance
   - WCAG 2.1 AA audit
   - Keyboard navigation guide
   - Screen reader testing

Create README.md with quick start.
Add ADRs (Architecture Decision Records).
Document design tokens & color palette.
"
```

---

## VI. BUDGETING UI DESIGN TOKENS

### Color Palette (Tailwind)

```typescript
// colors.ts
export const BUDGET_COLORS = {
  // Status indicators
  safe: '#10B981',      // Emerald-500 (0-50%)
  warning: '#F59E0B',   // Amber-500 (50-80%)
  alert: '#FB923C',     // Orange-500 (80-95%)
  danger: '#EF4444',    // Red-500 (95-100%)
  exceeded: '#991B1B',  // Red-900 (>100%)
  
  // Neutral
  light: '#F9FAFB',     // Gray-50
  medium: '#E5E7EB',    // Gray-200
  dark: '#1F2937',      // Gray-800
  
  // Hierarchy visualization
  company: '#3B82F6',    // Blue-500
  group: '#8B5CF6',      // Violet-500
  pharmacy: '#EC4899',   // Pink-500
  branch: '#06B6D4',     // Cyan-500
};

// Typography
export const FONT_SIZES = {
  xs: '0.75rem',   // 12px
  sm: '0.875rem',  // 14px
  md: '1rem',      // 16px
  lg: '1.125rem',  // 18px
  xl: '1.25rem',   // 20px
  '2xl': '1.5rem', // 24px
};

// Spacing (8px grid)
export const SPACING = {
  xs: '0.25rem',  // 4px
  sm: '0.5rem',   // 8px
  md: '1rem',     // 16px
  lg: '1.5rem',   // 24px
  xl: '2rem',     // 32px
  '2xl': '3rem',  // 48px
};

// Border radius
export const RADIUS = {
  sm: '0.25rem',  // 4px
  md: '0.5rem',   // 8px
  lg: '1rem',     // 16px
  full: '9999px',
};

// Shadows
export const SHADOWS = {
  sm: '0 1px 2px rgba(0, 0, 0, 0.05)',
  md: '0 4px 6px rgba(0, 0, 0, 0.1)',
  lg: '0 10px 15px rgba(0, 0, 0, 0.1)',
};

// Animations
export const TRANSITIONS = {
  fast: '0.15s ease-out',
  normal: '0.3s ease-out',
  slow: '0.5s ease-out',
};
```

---

## VII. BUDGETING UI TYPES & INTERFACES

```typescript
// types/budget.types.ts

export interface BudgetAllocation {
  id: string;
  hierarchyLevel: 'COMPANY' | 'GROUP' | 'PHARMACY' | 'BRANCH';
  hierarchyNodeId: string;
  hierarchyNodeName: string;
  parentId?: string;
  allocatedAmount: number;
  periodStart: Date;
  periodEnd: Date;
  periodType: 'MONTHLY' | 'QUARTERLY' | 'ANNUAL';
  currency: string;
  createdBy: string;
  createdAt: Date;
  updatedAt: Date;
  version: number;
  status: 'ACTIVE' | 'ARCHIVED' | 'PENDING_APPROVAL';
}

export interface BudgetSpending {
  id: string;
  budgetAllocationId: string;
  transactionId: string;
  discountTransactionId: string;
  customerId: string;
  branchId: string;
  amount: number;
  spentAt: Date;
  category: string;
  ruleId: string;
}

export interface BudgetSummary {
  hierarchyLevel: 'COMPANY' | 'GROUP' | 'PHARMACY' | 'BRANCH';
  hierarchyNodeId: string;
  hierarchyNodeName: string;
  allocatedAmount: number;
  spentAmount: number;
  remainingAmount: number;
  percentageUsed: number;
  status: 'SAFE' | 'WARNING' | 'DANGER' | 'EXCEEDED';
  trend: number; // percentage change from last period
  projectedEnd: number; // forecast amount at end of period
  daysUsed: number;
  daysRemaining: number;
  burnRate: number; // amount per day
  lastUpdated: Date;
  children?: BudgetSummary[]; // for hierarchy
}

export interface BudgetAlert {
  id: string;
  budgetAllocationId: string;
  thresholdPercent: number; // 50, 75, 90, 100
  triggerCount: number; // how many times triggered
  lastTriggered: Date;
  status: 'ACTIVE' | 'RESOLVED' | 'SNOOZED';
  recipients: string[];
  channels: ('EMAIL' | 'SMS' | 'IN_APP')[];
}

export interface BudgetForecast {
  budgetAllocationId: string;
  projectionDate: Date;
  scenarios: {
    best: number;      // best case (-20% burn rate)
    current: number;   // current trend
    worst: number;     // worst case (+20% burn rate)
  };
  burnRate: {
    daily: number;
    weekly: number;
    trend: 'INCREASING' | 'STABLE' | 'DECREASING';
  };
  confidence: number;  // 0-100 score
  recommendations: string[];
  riskLevel: 'LOW' | 'MEDIUM' | 'HIGH';
}

export interface BudgetAuditEvent {
  id: string;
  aggregateId: string;
  eventType: 'ALLOCATION_CREATED' | 'ALLOCATION_UPDATED' | 'SPENDING_RECORDED' | 'ALERT_TRIGGERED';
  changedBy: string;
  changes: {
    field: string;
    oldValue: any;
    newValue: any;
  }[];
  timestamp: Date;
  status: 'SUCCESS' | 'FAILED';
  reason?: string;
}

export interface BudgetValidationError {
  field: string;
  message: string;
  value: any;
  rule: string;
}
```

---

## VIII. COMPLETE COPILOT PROMPT LIBRARY

### Prompt: Build Complete Budgeting Dashboard

```
Copilot Prompt (Copy-Paste Ready):

"Build complete BudgetDashboard page component with the following:

STRUCTURE:
├── Header
│   ├── Title: 'Budget Management'
│   ├── Period Selector: Today | Week | Month | Quarter | Custom
│   └── Refresh Button
├── KPI Cards (4 cards in grid)
│   ├── Card 1: Total Allocated Budget
│   ├── Card 2: Total Spent
│   ├── Card 3: Remaining Budget
│   └── Card 4: Forecast
├── Main Content (Tabs)
│   ├── Tab 1: Overview (Trend chart)
│   ├── Tab 2: By Branch (Table)
│   ├── Tab 3: By Category (Pie chart)
│   ├── Tab 4: By Rule (Top 10)
└── Alerts Section (If any)

STYLING:
- Use Tailwind CSS with custom color tokens
- Color scheme: Green (safe), Yellow (warning), Orange (alert), Red (danger)
- Responsive: Desktop (grid), Tablet (2-col), Mobile (1-col)
- Dark mode support with CSS variables

FUNCTIONALITY:
1. Period Selection
   - Radio buttons: Today | This Week | This Month | Quarter | Custom
   - Custom: Date picker from/to
   - On change: Fetch new data, update all visualizations
   - Preserve selection in URL params

2. KPI Cards
   - Show: Amount, % of total, trend (arrow + %), status badge
   - Number formatting: 50,000 SAR (comma separator)
   - Color indicator: Green/Yellow/Orange/Red based on %
   - Click: Drill-down to detail (show transactions)

3. Trend Chart
   - LineChart with Recharts
   - X-axis: Dates for selected period
   - Y-axis: Amount in SAR
   - Reference line: Budget allocation / period days
   - Shaded zones: Safe (green), Warning (yellow), Danger (red)
   - Hover tooltip: Date, amount, % of budget

4. Tables (Sortable, Filterable)
   - By Branch: Branch name, budget, spent, remaining, %
   - By Category: Category, amount, count, %
   - By Rule: Rule name, discount type, frequency, amount
   - Default sort: Descending by amount
   - Search box for filtering
   - Export button: CSV, PDF

5. Alerts
   - Banner at top if budget exceeded
   - Warning if >80% spent
   - Info if high burn rate detected
   - Actions: Dismiss, drill-down, adjust budget

DATA FETCHING:
- Use custom hook useBudgetTracking(period)
- Backend endpoint: GET /api/v1/budgets/tracking?period=MONTH
- Cache: 5 minutes with real-time WebSocket updates
- Loading state: Skeleton loaders for cards and charts
- Error state: Error message with retry button

REAL-TIME UPDATES:
- WebSocket endpoint: ws://api.avnzor.local/budget-updates
- Events: BudgetSpending recorded, threshold crossed
- Update: Smoothly animate new values
- Last updated timestamp

STATE MANAGEMENT:
- Use Zustand store for: budgets, spending, alerts
- Selectors: selectBudgetSummary(), selectTrendData(), selectByBranch()
- Actions: setBudgets(), addAlert(), updateSpending()

ACCESSIBILITY:
- ARIA labels on all interactive elements
- Color not only way to convey information (include text badges)
- Keyboard navigation (Tab order, Enter/Space triggers)
- Screen reader support (describe charts)

PERFORMANCE:
- Memoize components: React.memo() on cards, charts
- Lazy load: Charts imported dynamically
- Virtual scrolling: Long tables
- Debounce: resize events
- Target: Initial load < 2s, update < 100ms

TESTING:
- Unit tests: Calculations (50% of 100,000)
- Integration tests: Fetch data, render, interact
- Snapshot tests: Component structure
- E2E tests: Full workflow (select period → see updated data)

DELIVERABLES:
1. BudgetDashboard.tsx (main component)
2. useBudgetTracking.ts (data hook)
3. budgetStore.ts (Zustand store)
4. types/budget.types.ts (TypeScript interfaces)
5. BudgetDashboard.test.tsx (unit + integration tests)
6. BudgetDashboard.stories.tsx (Storybook)

CONSTRAINTS:
- No external UI libraries (Tailwind + Recharts only)
- TypeScript strict mode
- No 'any' types
- Accessibility: WCAG 2.1 AA
- Browser support: Chrome 90+, Firefox 88+, Safari 14+

Use React 18 hooks + TypeScript.
Create hooks with proper error handling.
Add comprehensive JSDoc comments.
Include edge case handling.
Smooth number transitions with react-transition-group."
```

### Prompt: Build Budget Allocation Form

```
"Create BudgetAllocationForm component with:

FORM FLOW:
1. Hierarchy Selection
   - Show: Company (total budget: 500,000 SAR)
   - Breadcrumb: Company > Group > Pharmacy > Branch
   - Button: Select different hierarchy node

2. Distribution Method
   - Radio group: 4 options
     * Equal: Each child = parent / childCount
     * By Spending: Weight by last 30-day spending
     * By Sales: Weight by transaction count
     * Custom: Manual entry per child
   - On select: Auto-populate allocations

3. Allocation Sliders
   - Per child: Name | Slider (0-100% of parent) | Input | %
   - Drag slider: Adjust value
   - Edit input: Type SAR amount
   - Drag handles: Adjust multiple proportionally

4. Visualization
   - Stacked horizontal bar: Show allocations
   - Different color per child
   - Remaining section: Gray
   - Hover: Show name + amount + %

5. Validation & Warnings
   - Total allocated display (bottom)
   - Warning: Total < 100% (unallocated)
   - Error: Total > parent budget (red highlight)
   - Disable Save if invalid
   - Show affected children in red

6. Actions
   - Reset: Revert to initial
   - Undo: Last 3 actions
   - Preview: Show impact (modal)
   - Save: Submit to API
   - Cancel: Close form

USE REACT HOOK FORM:
- Form state via useForm()
- Field registration: {...register('childAllocations.0.amount')}
- Validation: Zod schema
- Errors: Displayed below each input
- Submit: Prevent default, call API

CALCULATIONS:
- On value change: Recalculate totals
- Sync slider to input
- Display: SAR with comma separator (e.g., 50,000)
- Percentage: (amount / parent) * 100

RESPONSIVE:
- Desktop: 2-column grid (sliders + chart)
- Tablet: 1-column
- Mobile: Full width, large touch targets (48px min)

ACCESSIBILITY:
- Labels for inputs (sliders + text)
- ARIA labels
- Keyboard navigation

VALIDATION SCHEMA (Zod):
- Each allocation > 0
- Each allocation <= parent
- Total allocation <= parent
- Custom error messages

DELIVERABLES:
1. BudgetAllocationForm.tsx
2. useBudgetAllocation.ts hook
3. allocationValidation.ts (Zod schema)
4. BudgetAllocationForm.test.tsx
5. BudgetAllocationForm.stories.tsx

Include TypeScript types, JSDoc, tests."
```

---

## IX. QUICK START CHECKLIST

### Component Checklist (25 Components Total)

**Phase 1: Foundation (Days 1-3)**
- [ ] BudgetCard.tsx (primary metric card)
- [ ] BudgetMeter.tsx (circular progress)
- [ ] LinearBudgetMeter.tsx (horizontal progress)
- [ ] BudgetMeterStack.tsx (stacked multi-value)
- [ ] BudgetMetric.tsx (single KPI display)

**Phase 1: State & Hooks (Days 1-3)**
- [ ] budgetStore.ts (Zustand)
- [ ] useBudgetTracking.ts (fetch/cache)
- [ ] useBudgetAlerts.ts (WebSocket alerts)
- [ ] budgetAPI.ts (API client)
- [ ] budgetSelectors.ts (Zustand selectors)

**Phase 2: Charts (Days 4-5)**
- [ ] TrendChart.tsx (line chart)
- [ ] SpendingBreakdown.tsx (pie/bar)
- [ ] AllocationTree.tsx (treemap)
- [ ] BudgetComparison.tsx (grouped bars)
- [ ] BudgetTimeline.tsx (timeline visualization)

**Phase 2: Forms (Days 4-6)**
- [ ] BudgetAllocationForm.tsx
- [ ] BudgetAdjustmentForm.tsx
- [ ] AlertThresholdForm.tsx
- [ ] BudgetImportForm.tsx

**Phase 3: Pages (Days 7-10)**
- [ ] BudgetDashboard.tsx (main page)
- [ ] BudgetTracking.tsx (tracking detail)
- [ ] BudgetForecast.tsx (prediction)
- [ ] BudgetCompliance.tsx (audit)
- [ ] BudgetAllocation.tsx (allocation page)

### Hook Checklist (8 Hooks)
- [ ] useBudgetTracking() - fetch budget data
- [ ] useBudgetAllocation() - manage allocation form
- [ ] useBudgetForecast() - calculate forecasts
- [ ] useBudgetAlerts() - WebSocket alerts
- [ ] useBudgetValidation() - form validation
- [ ] useBudgetChartData() - format chart data
- [ ] useBudgetHistorySync() - sync history
- [ ] useBudgetExport() - export data

### Utility Checklist (6 Utilities)
- [ ] budgetCalculations.ts - math functions
- [ ] budgetFormatting.ts - number/date formatting
- [ ] budgetValidation.ts - business rules
- [ ] budgetChartData.ts - prepare chart data
- [ ] forecastAlgorithm.ts - projection calculation
- [ ] budgetColorMap.ts - status to color

### Test File Checklist (25+ Tests)
- [ ] BudgetCard.test.tsx
- [ ] BudgetMeter.test.tsx
- [ ] TrendChart.test.tsx
- [ ] BudgetAllocationForm.test.tsx
- [ ] BudgetDashboard.integration.test.tsx
- [ ] budgetCalculations.test.ts
- [ ] useBudgetTracking.test.tsx
- [ ] budgetStore.test.ts
- [ ] And more...

### Storybook Checklist (25+ Stories)
- [ ] BudgetCard.stories.tsx (4 states)
- [ ] BudgetMeter.stories.tsx (4 states)
- [ ] TrendChart.stories.tsx (3 responsive)
- [ ] BudgetAllocationForm.stories.tsx (5 states)
- [ ] And more...

---

## X. PERFORMANCE TARGETS & MONITORING

### Performance Benchmarks

```
Component                | Target      | Actual | Status
─────────────────────────────────────────────────────
BudgetCard render        | <50ms       |        | ⏳
BudgetMeter animate      | <300ms      |        | ⏳
TrendChart render        | <300ms      |        | ⏳
BudgetDashboard init     | <2s         |        | ⏳
Dashboard update (RT)    | <100ms      |        | ⏳
Form validation          | <50ms       |        | ⏳
Allocation calculate     | <100ms      |        | ⏳
Bundle size              | <500KB      |        | ⏳
```

### Monitoring Metrics

```typescript
// Metrics to implement
export const METRICS = {
  // Performance
  componentRenderTime: histogram('budget_component_render_ms'),
  chartRenderTime: histogram('budget_chart_render_ms'),
  apiLatency: histogram('budget_api_latency_ms'),
  websocketLatency: histogram('budget_ws_latency_ms'),
  
  // Usage
  dashboardViews: counter('budget_dashboard_views'),
  allocationsForms: counter('budget_allocation_forms'),
  forecastsGenerated: counter('budget_forecasts_generated'),
  
  // Errors
  apiErrors: counter('budget_api_errors', ['endpoint', 'error_type']),
  validationErrors: counter('budget_validation_errors'),
  websocketErrors: counter('budget_ws_errors'),
  
  // Business
  budgetsExceeded: gauge('budget_budgets_exceeded'),
  alertsTriggered: counter('budget_alerts_triggered'),
  forecastAccuracy: gauge('budget_forecast_accuracy'),
};
```

---

## XI. ACCESSIBILITY CHECKLIST

- [ ] Color contrast ≥ 4.5:1 for text
- [ ] Interactive elements ≥ 48px (touch target)
- [ ] Keyboard navigation (Tab, Enter, Arrow keys)
- [ ] ARIA labels on all inputs
- [ ] ARIA roles on custom components
- [ ] Screen reader tested (NVDA, JAWS)
- [ ] Focus indicators visible
- [ ] Dark mode support
- [ ] High contrast mode support
- [ ] Reduced motion preference honored
- [ ] Charts have data table fallback
- [ ] Error messages semantic HTML (role="alert")
- [ ] Form labels <label> htmlFor
- [ ] Link text descriptive (not "click here")
- [ ] Images have alt text
- [ ] WCAG 2.1 AA compliance audit passed

---

## XII. DEPLOYMENT CHECKLIST

- [ ] All tests passing (Jest, E2E)
- [ ] TypeScript strict mode, no errors
- [ ] ESLint clean, no warnings
- [ ] Bundle size analyzed (<500KB gzipped)
- [ ] Performance budget met (Lighthouse CI)
- [ ] Accessibility audit passed (axe DevTools)
- [ ] Security audit passed (dependencies checked)
- [ ] Documentation complete (README, Storybook)
- [ ] API integration tested with staging backend
- [ ] WebSocket integration tested
- [ ] Error handling tested (network down, invalid data, etc.)
- [ ] Dark mode tested
- [ ] Mobile responsive tested (Chrome DevTools)
- [ ] Cross-browser tested (Chrome, Firefox, Safari)
- [ ] Performance profiled (DevTools, Lighthouse)
- [ ] Deployment plan documented

---

## XIII. NEXT STEPS

1. **Start with Day 1-3 Prompts** - Copy-paste Foundation prompts into Copilot
2. **Run Daily Prompts** - Follow implementation workflow
3. **Test Continuously** - Unit tests as you build
4. **Iterate on Design** - Use Storybook for feedback
5. **Integrate with Backend** - Once APIs ready
6. **Deploy to Staging** - Week 3 for testing
7. **Gather Feedback** - User acceptance testing
8. **Go Live** - Production deployment

---

**Budgeting UI System Instructions v1.0**  
**25+ Components | 8 Custom