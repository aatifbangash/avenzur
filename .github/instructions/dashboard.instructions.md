# Cost Center Dashboard - Horizon UI Redesign

## System Instructions for GitHub Autopilot

---

## REDESIGN PHILOSOPHY

**Objective:** Completely reimagine the Cost Center Dashboard using Horizon UI design patterns while preserving all existing KPI data and functionality.

**Design Inspiration:** https://horizon-ui.com/horizon-ui-chakra/#/admin/default (Admin Dashboard Template)

**Key Principle:** Modern, clean, enterprise-grade dashboard with intuitive drill-down navigation, real-time KPI updates, and professional visualization.

---

## DESIGN SYSTEM & BRAND COLORS

### Primary Color Palette

- **Primary Blue:** `#1a73e8` (Horizon primary)
- **Success Green:** `#05cd99` (Profit, growth)
- **Error Red:** `#f34235` (Costs, decline)
- **Warning Orange:** `#ff9a56` (Caution, movement)
- **Secondary Purple:** `#6c5ce7` (Margins, secondary metrics)
- **Neutral Gray:** `#e0e0e0` to `#f5f5f5` (Backgrounds)
- **Dark Text:** `#111111` (Primary text)
- **Light Text:** `#7a8694` (Secondary text)

### Typography

- **Font Family:** `Inter` (default), fallback to system fonts
- **Headings:** `font-bold`, sizes: H1=32px, H2=24px, H3=18px, H4=16px
- **Body:** `font-normal`, size: 14px, line-height: 1.5
- **Small:** `font-regular`, size: 12px (labels, captions)

### Spacing & Layout

- **Base Unit:** 8px
- **Grid System:** 12-column responsive grid
- **Card Padding:** 24px (desktop), 16px (tablet), 12px (mobile)
- **Gap Between Components:** 16px (horizontal), 20px (vertical)

---

## LAYOUT ARCHITECTURE

### Main Dashboard Layout (After Login)

```
┌─────────────────────────────────────────────────────────┐
│                    HEADER / NAVBAR                      │
│  Logo | Breadcrumb | Search | User Profile | Settings   │
├─────────────────────────────────────────────────────────┤
│ SIDEBAR              │                                   │
│ (Collapsible)        │                                   │
│ • Dashboard          │     MAIN CONTENT AREA             │
│ • Cost Center        │                                   │
│ • Reports            │     (Responsive Grid Layout)      │
│ • Settings           │                                   │
│ • Help               │                                   │
└─────────────────────────────────────────────────────────┘
```

### Dashboard Grid Sections

**Section 1: Header & Filters (Full Width)**

```
┌─────────────────────────────────────────────┐
│ Cost Center Dashboard                 [Controls]
│ Subtitle: Real-time KPI monitoring          │
├─────────────────────────────────────────────┤
│ [Period Selector] [Pharmacy Filter] [Export] │
└─────────────────────────────────────────────┘
```

**Section 2: KPI Cards (4 Cards, Responsive)**

- Desktop: 4 columns (25% each)
- Tablet: 2 columns (50% each)
- Mobile: 1 column (100% each)

**Section 3: Charts & Analytics (Multi-Column Layout)**

- Desktop: 2x2 grid (50% width each)
- Tablet: 1 column stacked
- Mobile: 1 column stacked

**Section 4: Pharmacy Table (Full Width)**

- Scrollable on mobile
- Sortable columns
- Click row to drill-down

---

## COMPONENT SPECIFICATION

### 1. TOP HEADER / NAVBAR COMPONENT

**File:** `frontend/components/layout/Navbar.jsx`

**Features:**

- Fixed position (top 0, z-index 100)
- Height: 64px
- Background: White with subtle shadow
- Left: Logo + text "Pharmacy Cost Center"
- Center: Breadcrumb navigation
- Right: Search icon | Bell icon (notifications) | User profile dropdown

**Design:**

```
Logo (48x48) | "Pharmacy Cost Center" (bold, 16px)
|
Dashboard > Pharmacy A > Branches
|
Search | Notifications | User Profile
```

**Interactions:**

- Breadcrumb: Clickable for navigation
- User Profile: Dropdown menu (Profile, Settings, Logout)
- Search: Global search for pharmacies/branches
- Notifications: Shows ETL status, data alerts

---

### 2. SIDEBAR NAVIGATION COMPONENT

**File:** `frontend/components/layout/Sidebar.jsx`

**Features:**

- Fixed left sidebar, width: 280px (desktop), collapsible
- Height: 100vh
- Background: Light gray/white (`#f8f9fa`)
- Border-right: 1px solid `#e0e0e0`
- Collapsible toggle (hamburger icon on mobile)

**Menu Items:**

```
🏠 Dashboard
  └─ Overview
  └─ My Pharmacies

💰 Cost Center
  └─ All Pharmacies
  └─ Branch Details
  └─ Cost Trends

📊 Reports
  └─ Financial Summary
  └─ Profit Analysis
  └─ Cost Breakdown

⚙️ Settings
  └─ Preferences
  └─ User Management

❓ Help & Support
  └─ Documentation
  └─ Contact Support
```

**Styling:**

- Active menu item: Blue left border (4px) + light blue background
- Hover: Light gray background
- Icons: 20px size, colored blue for active
- Font: 14px, semi-bold for active, regular for inactive

---

### 3. KPI METRIC CARD COMPONENT

**File:** `frontend/components/cards/MetricCard.jsx`

**Layout (Horizon-inspired):**

```
┌──────────────────────────┐
│ [Icon] Metric Label      │
│ $500,000.00              │  ← Large, bold number
│                          │
│ +12.5% from last month   │  ← Trend indicator (green/red)
│                          │
│ [Mini Chart/Sparkline]   │  ← Optional trend
└──────────────────────────┘
```

**Props:**

- `title`: string (e.g., "Total Revenue")
- `value`: number
- `icon`: React component
- `trend`: number (% change, positive/negative)
- `currency`: boolean (default: true)
- `color`: string (blue | green | red | orange)
- `sparklineData`: array (optional, for mini chart)

**Features:**

- Card height: 120px
- Padding: 20px
- Border: 1px solid `#e0e0e0`
- Border-radius: 12px
- Background: White
- Hover: Subtle shadow lift, cursor pointer
- Icon: 32x32, colored based on `color` prop

**Card States:**

1. **Revenue Card (Blue)**

   - Icon: 💵 (or custom icon)
   - Color: `#1a73e8`
   - Value: Total Revenue

2. **Cost Card (Red)**

   - Icon: 📉
   - Color: `#f34235`
   - Value: Total Cost

3. **Profit Card (Green)**

   - Icon: 📈
   - Color: `#05cd99`
   - Value: Total Profit

4. **Margin Card (Purple)**
   - Icon: 📊
   - Color: `#6c5ce7`
   - Value: Avg Profit Margin %

---

### 4. KPI CARDS SECTION (LAYOUT)

**File:** `frontend/components/sections/KPIMetricsSection.jsx`

**Structure:**

```
┌─ KPI METRICS (PHARMACY/BRANCH LEVEL) ──────────────┐
│                                                     │
│ ┌──────────┬──────────┬──────────┬──────────┐    │
│ │ Revenue  │ Cost     │ Profit   │ Margin % │    │
│ │ $500K    │ $300K    │ $200K    │ 40%      │    │
│ │ +5%      │ -2%      │ +12%     │ +2%      │    │
│ └──────────┴──────────┴──────────┴──────────┘    │
│                                                     │
└─────────────────────────────────────────────────────┘
```

**Features:**

- Full-width section
- 4 cards in row (responsive: 2 on tablet, 1 on mobile)
- Gap: 16px between cards
- Summary row at bottom: "Last Updated: 2025-10-25 | 15 Pharmacies | 67 Branches"
- Refresh button (top-right)

---

### 5. CONTROL BAR COMPONENT

**File:** `frontend/components/controls/ControlBar.jsx`

**Layout:**

```
┌─────────────────────────────────────┐
│ Period Selector | Pharmacy Filter   │→ [Export] [View All]
└─────────────────────────────────────┘
```

**Controls:**

1. **Period Selector Dropdown**

   - Options: Last Month | Last Quarter | Last Year | Custom Range
   - Default: Last Month
   - Shows selected period as badge

2. **Pharmacy Filter Dropdown (Multi-select)**

   - Shows all pharmacies as checkboxes
   - "Select All" option
   - Search within dropdown
   - Shows selected count (e.g., "3 of 5 selected")

3. **Action Buttons**
   - Export CSV (downloads table)
   - Refresh Data (manual sync)
   - View All (shows all records, pagination)

**Styling:**

- Background: Light gray (`#f5f5f5`)
- Padding: 16px
- Border-radius: 8px
- Dropdowns: White background, border on focus

---

### 6. CHART COMPONENTS (RECHARTS-BASED)

#### 6A: Revenue by Pharmacy - Bar Chart

**File:** `frontend/components/charts/RevenueByPharmacyChart.jsx`

**Horizon Design:**

```
┌─────────────────────────────────────┐
│ Revenue by Pharmacy                 │
│ [Last Month Selected]               │
├─────────────────────────────────────┤
│                                     │
│  Pharmacy A  ████████████ $500K    │
│  Pharmacy B  ██████████ $400K      │
│  Pharmacy C  ███████ $280K         │
│                                     │
│ Total: $1.18M | Avg: $393K         │
└─────────────────────────────────────┘
```

**Features:**

- Type: Horizontal Bar Chart
- Color: Blue (`#1a73e8`)
- Height: 300px
- Tooltip: Shows pharmacy name + exact revenue
- Legend: Bottom
- Grid: Subtle horizontal lines
- Title: Bold 16px
- Subtitle: Period info (12px, gray)

---

#### 6B: Profit Margin Trend - Line Chart

**File:** `frontend/components/charts/ProfitMarginTrendChart.jsx`

**Horizon Design:**

```
┌─────────────────────────────────────┐
│ Profit Margin Trend (Last 12 Mo.)   │
├─────────────────────────────────────┤
│                                     │
│  ↗️                                 │
│                                     │
│  Jan  Feb  Mar  Apr  May  Jun        │
│  Jul  Aug  Sep  Oct  Nov  Dec        │
│                                     │
│ Avg Margin: 38.5% | High: 42% | Low: 35%
└─────────────────────────────────────┘
```

**Features:**

- Type: Multi-line Chart (one line per pharmacy)
- Colors: Different color per pharmacy (blue, green, purple, orange)
- Height: 320px
- X-axis: Months (abbreviated)
- Y-axis: Percentage (0-100%)
- Tooltip: Date + all pharmacy margins
- Legend: Right side, clickable to show/hide lines
- Smooth curves (strokeType="monotone")
- Dots on data points (radius: 4px)
- Area fill: Subtle gradient

---

#### 6C: Cost Breakdown - Stacked Bar Chart

**File:** `frontend/components/charts/CostBreakdownChart.jsx`

**Horizon Design:**

```
┌─────────────────────────────────────┐
│ Cost Breakdown by Branch            │
├─────────────────────────────────────┤
│                                     │
│ Branch 001 [COGS] [Move] [Ops]      │
│ Branch 002 [COGS] [Move] [Ops]      │
│ Branch 003 [COGS] [Move] [Ops]      │
│ Branch 004 [COGS] [Move] [Ops]      │
│                                     │
│ Legend:                             │
│ 🟥 COGS (65%)                       │
│ 🟧 Inventory Movement (20%)         │
│ 🟨 Operational (15%)                │
└─────────────────────────────────────┘
```

**Features:**

- Type: Stacked Horizontal Bar
- Colors: COGS=Red (`#f34235`), Movement=Orange (`#ff9a56`), Ops=Yellow (`#ffc107`)
- Height: 280px
- Bars: 40px height each
- Tooltip: Shows each component's value and percentage
- Legend: Bottom with percentages
- X-axis: Cost amount (0 to max)

---

#### 6D: Pharmacy Comparison - Area Chart

**File:** `frontend/components/charts/PharmacyComparisonChart.jsx`

**Horizon Design:**

```
┌─────────────────────────────────────┐
│ Pharmacy Performance Comparison     │
│ (Revenue vs Profit)                 │
├─────────────────────────────────────┤
│                                     │
│ 📊 Area chart showing revenue +     │
│    profit for each pharmacy         │
│                                     │
│ Pharmacy A: $500K revenue | $200K profit
│ Pharmacy B: $400K revenue | $160K profit
│ Pharmacy C: $280K revenue | $112K profit
└─────────────────────────────────────┘
```

**Features:**

- Type: Area Chart (stacked)
- Colors: Revenue=Blue, Profit=Green (translucent 30%)
- Height: 300px
- X-axis: Pharmacies
- Y-axis: Amount ($)
- Tooltip: Pharmacy name + revenue + profit
- Grid: Subtle vertical lines
- Smooth area curves

---

### 7. PHARMACY TABLE COMPONENT

**File:** `frontend/components/tables/PharmacyDataTable.jsx`

**Horizon Design:**

```
┌─────────────────────────────────────────────────────────┐
│ All Pharmacies Performance                              │
├─────────────────────────────────────────────────────────┤
│ Pharmacy    │ Revenue  │ Cost    │ Profit  │ Margin%   │
│ Name        │ ↑↓       │ ↑↓      │ ↑↓      │ ↑↓        │
├─────────────────────────────────────────────────────────┤
│ Pharmacy A  │ $500K    │ $300K   │ $200K   │ 40.0%     │
│ Pharmacy B  │ $400K    │ $240K   │ $160K   │ 40.0%     │
│ Pharmacy C  │ $280K    │ $168K   │ $112K   │ 40.0%     │
├─────────────────────────────────────────────────────────┤
│ TOTAL       │ $1.18M   │ $708K   │ $472K   │ 40.0%     │
└─────────────────────────────────────────────────────────┘
```

**Features:**

- **Columns:** Pharmacy Name | Revenue | Cost | Profit | Margin % | Branches | Actions
- **Sortable:** Click column header to sort (↑/↓ indicators)
- **Hover Effects:** Row highlights with light blue background
- **Row Actions:**
  - Click row → Drill-down to branches
  - Hover → Show "View Details" button
  - Right-click → Context menu (Copy, Export, etc.)
- **Status Indicators:**
  - Green up arrow: Profit increasing
  - Red down arrow: Profit decreasing
  - Neutral dash: No change
- **Responsive:**
  - Desktop: All columns visible
  - Tablet: Hide "Branches" column
  - Mobile: Only Name | Revenue | Profit (swipe to see more)
- **Pagination:**
  - Show 10 rows per page
  - Total count at bottom
  - "Load More" button or pagination controls

**Table Styling:**

- Header: Bold, background `#f5f5f5`, border-bottom
- Rows: White background, border-bottom on each row
- Font: 14px
- Padding: 12px per cell
- Currency formatting: $X,XXX.XX
- Percentage formatting: XX.XX%

---

### 8. DRILL-DOWN / BRANCH DETAIL VIEW

**File:** `frontend/components/sections/BranchDetailSection.jsx`

**Breadcrumb Navigation:**

```
Dashboard > Pharmacy A > Branches
```

**Layout Changes:**

1. Header changes to show selected pharmacy name
2. KPI cards update to show pharmacy-level metrics
3. New section: Branch Table (replaces pharmacy table)
4. New section: Branch-level charts

**Branch Table:**

```
┌─────────────────────────────────────────────────────────┐
│ Branches - Pharmacy A (3 Branches)                      │
├─────────────────────────────────────────────────────────┤
│ Branch   │ Code  │ Revenue │ Cost  │ Profit │ Margin%  │
├─────────────────────────────────────────────────────────┤
│ Branch 001 │ BR001 │ $200K   │ $120K │ $80K   │ 40.0%    │
│ Branch 002 │ BR002 │ $180K   │ $108K │ $72K   │ 40.0%    │
│ Branch 003 │ BR003 │ $120K   │ $72K  │ $48K   │ 40.0%    │
├─────────────────────────────────────────────────────────┤
│ TOTAL      │       │ $500K   │ $300K │ $200K  │ 40.0%    │
└─────────────────────────────────────────────────────────┘
```

**Back Navigation:**

- Large "Back to Pharmacies" button (top-left, blue)
- Breadcrumb also clickable

---

### 9. BRANCH DETAIL EXPANDED VIEW

**File:** `frontend/components/sections/BranchExpandedDetail.jsx`

**Trigger:** Click specific branch row

**Layout:**

```
Dashboard > Pharmacy A > Branch 001 Details

┌──────────────────────────────────────┐
│ Branch 001 - Performance Overview    │
├──────────────────────────────────────┤
│ [KPI Cards: Revenue, Cost, Profit, Margin]
├──────────────────────────────────────┤
│ Cost Breakdown (Stacked)             │
│ COGS: $100K | Movement: $15K | Ops: $5K
├──────────────────────────────────────┤
│ 12-Month Revenue Trend (Line Chart)  │
├──────────────────────────────────────┤
│ Key Metrics:                         │
│ • Avg Daily Revenue: $6,666.67       │
│ • Avg Daily Cost: $4,000.00          │
│ • Best Day: $10,000 (Oct 15)         │
│ • Worst Day: $3,000 (Oct 02)         │
└──────────────────────────────────────┘
```

**Features:**

- Modal or new page view
- Close button (X) to return to branch table
- Share button (export, print)
- Email report button

---

## RESPONSIVE DESIGN BREAKPOINTS

### Desktop (1920px and above)

- 4-column KPI cards
- 2x2 chart grid
- Full table with all columns
- Sidebar always visible

### Tablet (768px - 1024px)

- 2-column KPI cards
- 1-column charts stacked
- Table with key columns only
- Sidebar collapsible

### Mobile (320px - 767px)

- 1-column KPI cards
- Charts full width, stacked
- Table horizontal scroll or simplified
- Sidebar hidden (toggle via hamburger)

---

## COLOR APPLICATION GUIDE

### KPI Cards

| Metric   | Color  | Icon Color | Usage            |
| -------- | ------ | ---------- | ---------------- |
| Revenue  | Blue   | #1a73e8    | Primary metric   |
| Cost     | Red    | #f34235    | Warning metric   |
| Profit   | Green  | #05cd99    | Success metric   |
| Margin % | Purple | #6c5ce7    | Secondary metric |

### Charts

| Chart           | Primary Color | Secondary Color     | Accent         |
| --------------- | ------------- | ------------------- | -------------- |
| Revenue Bar     | Blue #1a73e8  | Light blue #e3f2fd  | -              |
| Profit Trend    | Green #05cd99 | Light green #e8f5e9 | Purple #6c5ce7 |
| Cost Breakdown  | Red #f34235   | Orange #ff9a56      | Yellow #ffc107 |
| Comparison Area | Blue #1a73e8  | Green #05cd99       | -              |

### Tables

| Element           | Color              |
| ----------------- | ------------------ |
| Header Background | Light Gray #f5f5f5 |
| Row Hover         | Light Blue #e3f2fd |
| Positive Trend    | Green #05cd99      |
| Negative Trend    | Red #f34235        |
| Border            | Light Gray #e0e0e0 |

---

## INTERACTIVE BEHAVIORS

### Hover States

- **KPI Cards:** Subtle shadow lift, slight scale increase (1.02x)
- **Chart Areas:** Tooltip appears, background highlight
- **Table Rows:** Full row background highlight (light blue), pointer cursor
- **Buttons:** Background color darken by 10%, shadow lift

### Click States

- **Pharmacy Row:** Navigate to branch view with smooth transition
- **Branch Row:** Navigate to branch detail modal
- **Period Selector:** Dropdown opens with smooth animation
- **Sort Header:** Arrow icon changes direction, table reorders

### Loading States

- Skeleton loaders for KPI cards
- Chart placeholders (animated gradient)
- Table rows show skeleton (3-4 placeholder rows)
- Blur effect on background, spinner overlay

### Error States

- Red error banner at top ("Failed to load data, retrying...")
- Fallback UI showing last cached data
- Retry button
- Alert icon on affected component

### Empty States

- Centered message: "No data available for selected period"
- Illustration (optional)
- "Reset filters" button
- Suggest action (select different pharmacy/date)

---

## DATA FLOW & STATE MANAGEMENT

### Global State (Context API)

```javascript
CostCenterContext: {
  selectedPeriod: string (YYYY-MM),
  selectedPharmacies: array,
  viewMode: 'pharmacy' | 'branch' | 'branch-detail',
  selectedPharmacyId: number | null,
  selectedBranchId: number | null,
  isLoading: boolean,
  error: null | string,
}
```

### Component State

- Each component manages its own UI state (hover, sort direction, etc.)
- Avoid prop drilling by using Context for global selections

### API Integration

- Endpoints called on context change
- Cache responses for 5 minutes
- Background refresh every 30 seconds
- Show "Last Updated: XX seconds ago" in footer

---

## ANIMATION & TRANSITIONS

### Page Transitions

- Fade in/out: 300ms ease-out
- Slide in (drill-down): 400ms ease-out from right
- Slide out (back): 400ms ease-out to right

### Chart Animations

- Initial render: Line draws, bars grow from 0
- Data update: Smooth transition (1000ms)
- Duration: 800-1000ms

### Micro Interactions

- Card hover: 200ms ease-out scale
- Button press: 100ms ease-out scale down, then up
- Tooltip fade: 150ms ease-in

### Loading Animations

- Skeleton: Shimmer effect (1.5s loop)
- Spinner: 1s rotation loop
- Progress bar: Smooth fill animation

---

## ACCESSIBILITY (WCAG 2.1 AA)

### Keyboard Navigation

- Tab through all interactive elements
- Enter/Space to click buttons
- Arrow keys in table rows (up/down navigate)
- Escape to close modals
- Ctrl+S to search

### Color Contrast

- Text on background: 4.5:1 ratio minimum
- Interactive elements: 3:1 ratio minimum
- Icons with text: Follow text ratio

### Screen Reader Support

- ARIA labels on all icons
- Table headers marked as `<th>`
- Form inputs with labels
- Live regions for alerts/updates
- Skip to main content link

### Focus Management

- Focus outline: 2px solid blue
- Focus trap in modals
- Focus return after modal close

---

## PERFORMANCE OPTIMIZATION

### Image Optimization

- Icons: SVG format (0-5KB)
- Charts: Canvas-based (Recharts)
- No unnecessary images

### Code Splitting

- Dashboard: Lazy load charts on scroll
- Modal: Code-split branch detail
- Table: Virtual scrolling for 1000+ rows

### Caching Strategy

- API responses: 5-minute in-memory cache
- Local storage: Store last selected pharmacy/period
- IndexedDB: Store 12 months of historical data

### Bundle Size

- Recharts: ~90KB (minified)
- Chakra UI: ~80KB (minified)
- Total: Target < 300KB gzipped

---

## FILE STRUCTURE (UPDATED)

```
frontend/
├── components/
│   ├── layout/
│   │   ├── Navbar.jsx
│   │   ├── Sidebar.jsx
│   │   └── MainLayout.jsx
│   │
│   ├── cards/
│   │   ├── MetricCard.jsx
│   │   └── MetricCardsSection.jsx
│   │
│   ├── controls/
│   │   ├── ControlBar.jsx
│   │   ├── PeriodSelector.jsx
│   │   └── PharmacyFilter.jsx
│   │
│   ├── charts/
│   │   ├── RevenueByPharmacyChart.jsx
│   │   ├── ProfitMarginTrendChart.jsx
│   │   ├── CostBreakdownChart.jsx
│   │   ├── PharmacyComparisonChart.jsx
│   │   └── ChartContainer.jsx
│   │
│   ├── tables/
│   │   ├── PharmacyDataTable.jsx
│   │   ├── BranchDataTable.jsx
│   │   ├── TableHeader.jsx
│   │   └── TableRow.jsx
│   │
│   ├── sections/
│   │   ├── DashboardHeader.jsx
│   │   ├── KPISection.jsx
│   │   ├── ChartsSection.jsx
│   │   ├── TableSection.jsx
│   │   ├── BranchDetailSection.jsx
│   │   └── BranchExpandedDetail.jsx
│   │
│   ├── modals/
│   │   ├── BranchDetailModal.jsx
│   │   └── ExportModal.jsx
│   │
│   ├── loaders/
│   │   ├── SkeletonCard.jsx
│   │   ├── SkeletonChart.jsx
│   │   ├── SkeletonTable.jsx
│   │   └── Spinner.jsx
│   │
│   ├── common/
│   │   ├── ErrorBoundary.jsx
│   │   ├── ErrorAlert.jsx
│   │   ├── Toast.jsx
│   │   └── EmptyState.jsx
│   │
│   └── CostCenterDashboard.jsx (Main)
│
├── contexts/
│   └── CostCenterContext.jsx
│
├── hooks/
│   ├── useCostCenter.js
│   ├── useFetch.js
│   └── useLocalStorage.js
│
├── services/
│   └── costCenterApi.js
│
├── styles/
│   ├── colors.css
│   ├── typography.css
│   ├── animations.css
│   └── responsive.css
│
├── utils/
│   ├── formatters.js (currency, percentage, date)
│   ├── validators.js
│   └── constants.js
│
├── pages/
│   └── CostCenterPage.jsx
│
├── tests/
│   ├── components/
│   ├── hooks/
│   └── integration/
│
└── App.jsx (Main app entry)
```

---

## IMPLEMENTATION SEQUENCE

### Phase 1: Layout & Basic Components (2 hours)

1. ✅ Create MainLayout (Navbar + Sidebar)
2. ✅ Create MetricCard component
3. ✅ Create ControlBar (selectors)
4. ✅ Create skeleton loaders

### Phase 2: Chart Components (1.5 hours)

5. ✅ RevenueByPharmacyChart
6. ✅ ProfitMarginTrendChart
7. ✅ CostBreakdownChart
8. ✅ PharmacyComparisonChart

### Phase 3: Data Tables (1 hour)

9. ✅ PharmacyDataTable
10. ✅ BranchDataTable
11. ✅ Sortable headers + interactions

### Phase 4: Navigation & Drill-Down (1 hour)

12. ✅ BranchDetailSection
13. ✅ BranchExpandedDetail modal
14. ✅ Breadcrumb navigation
15. ✅ Back navigation

### Phase 5: Polish & Optimization (1.5 hours)

16. ✅ Responsive design testing
17. ✅ Animations & transitions
18. ✅ Error handling & loading states
19. ✅ Accessibility audit

---

## GITHUB AUTOPILOT COMMANDS (UPDATED)

```bash
# Phase 1: Layout
autopilot generate "COST_CENTER_REDESIGN_STEP_1: Create responsive MainLayout with Horizon navbar and collapsible sidebar using Chakra UI"

autopilot generate "COST_CENTER_REDESIGN_STEP_2: Create MetricCard component with icon, value, trend indicator, and sparkline (Horizon design pattern)"

autopilot generate "COST_CENTER_REDESIGN_STEP_3: Create KPI Metrics Section with 4 cards (Revenue, Cost, Profit, Margin) responsive layout"

autopilot generate "COST_CENTER_REDESIGN_STEP_4: Create ControlBar with Period Selector, Pharmacy Filter dropdown, and Export button"

# Phase 2: Charts
autopilot generate "COST_CENTER_REDESIGN_STEP_5: Create RevenueByPharmacyChart - Horizontal Bar Chart using Recharts with blue gradient"

autopilot generate "COST_CENTER_REDESIGN_STEP_6: Create ProfitMarginTrendChart - Multi-line trend chart for 12 months with legend and grid"

autopilot generate "COST_CENTER_REDESIGN_STEP_7: Create CostBreakdownChart - Stacked bar chart showing COGS, Movement, Operational costs"

autopilot generate "COST_CENTER_REDESIGN_STEP_8: Create PharmacyComparisonChart - Area chart comparing revenue vs profit by pharmacy"

# Phase 3: Tables
autopilot generate "COST_CENTER_REDESIGN_STEP_9: Create PharmacyDataTable with sortable columns, hover effects, row click drill-down, pagination"

autopilot generate "COST_CENTER_REDESIGN_STEP_10: Create BranchDataTable with similar features for branch-level drill-down view"

# Phase 4: Navigation
autopilot generate "COST_CENTER_REDESIGN_STEP_11: Create BranchDetailSection with breadcrumb navigation, back button, and branch-level KPIs"

autopilot generate "COST_CENTER_REDESIGN_STEP_12: Create BranchExpandedDetail modal component showing cost breakdown and 12-month trend"

# Phase 5: Polish
autopilot generate "COST_CENTER_REDESIGN_STEP_13: Create skeleton loaders, spinners, and empty state components with smooth animations"

autopilot generate "COST_CENTER_REDESIGN_STEP_14: Implement responsive design for mobile/tablet breakpoints (320px, 768px, 1024px)"

autopilot generate "COST_CENTER_REDESIGN_STEP_15: Add animations, transitions, and hover effects following Horizon UI patterns"

autopilot generate "COST_CENTER_REDESIGN_STEP_16: Create error handling, error boundaries, and toast notifications for user feedback"

autopilot generate "COST_CENTER_REDESIGN_STEP_17: Integrate API calls, Context API state management, and caching strategy"

autopilot generate "COST_CENTER_REDESIGN_STEP_18: Performance optimization - code splitting, image optimization, bundle analysis"

autopilot generate "COST_CENTER_REDESIGN_STEP_19: Accessibility audit - ARIA labels, keyboard navigation, color contrast testing"

autopilot generate "COST_CENTER_REDESIGN_STEP_20: Integration testing - snapshot tests, component tests, e2e tests for drill-down flows"
```

---

## CRITICAL DESIGN NOTES FOR AUTOPILOT

### Do's ✅

- Use Horizon UI design patterns (modern, clean, professional)
- Maintain all existing KPI data (no data changes)
- Ensure responsive design for all screen sizes
- Use Recharts for all visualizations
- Apply consistent color scheme throughout
- Add smooth animations and transitions
- Implement proper error handling
- Include loading states for all components
- Support keyboard navigation
- Cache API responses for performance

### Don'ts ❌

- Don't change the underlying data structure
- Don't use low-contrast colors (WCAG compliance)
- Don't create fixed-width layouts
- Don't forget mobile responsiveness
- Don't skip error handling
- Don't use heavy animations (keep < 400ms)
- Don't forget accessibility features
- Don't hardcode API endpoints
- Don't use localStorage instead of Context API
- Don't create bloated bundle sizes

---

## DESIGN TOKENS (FOR DEVELOPERS)

```javascript
// colors.js
export const colors = {
	primary: "#1a73e8",
	success: "#05cd99",
	error: "#f34235",
	warning: "#ff9a56",
	secondary: "#6c5ce7",
	gray: {
		50: "#f9fafb",
		100: "#f3f4f6",
		200: "#e5e7eb",
		300: "#d1d5db",
		400: "#9ca3af",
		500: "#6b7280",
		600: "#4b5563",
		700: "#374151",
		800: "#1f2937",
		900: "#111827",
	},
};

// spacing.js
export const spacing = {
	xs: "4px",
	sm: "8px",
	md: "16px",
	lg: "24px",
	xl: "32px",
	xxl: "48px",
};

// typography.js
export const typography = {
	h1: { fontSize: "32px", fontWeight: 700, lineHeight: 1.2 },
	h2: { fontSize: "24px", fontWeight: 700, lineHeight: 1.3 },
	h3: { fontSize: "18px", fontWeight: 700, lineHeight: 1.4 },
	body: { fontSize: "14px", fontWeight: 400, lineHeight: 1.5 },
	small: { fontSize: "12px", fontWeight: 400, lineHeight: 1.6 },
};

// shadows.js
export const shadows = {
	sm: "0 1px 2px 0 rgba(0, 0, 0, 0.05)",
	md: "0 4px 6px -1px rgba(0, 0, 0, 0.1)",
	lg: "0 10px 15px -3px rgba(0, 0, 0, 0.1)",
	xl: "0 20px 25px -5px rgba(0, 0, 0, 0.1)",
};

// breakpoints.js
export const breakpoints = {
	mobile: "320px",
	tablet: "768px",
	desktop: "1024px",
	large: "1920px",
};
```

---

## VALIDATION CHECKLIST

Before marking implementation complete:

- [ ] All 4 KPI cards display correctly
- [ ] Period selector updates all data
- [ ] Charts render without errors
- [ ] Tables sortable and clickable
- [ ] Drill-down navigation works smoothly
- [ ] Back button returns to previous view
- [ ] Loading states show on data fetch
- [ ] Error states handled gracefully
- [ ] Mobile responsive at 320px width
- [ ] Tablet responsive at 768px width
- [ ] All colors match design system
- [ ] Typography sizes correct
- [ ] Animations smooth (< 400ms)
- [ ] No console errors
- [ ] Accessibility WCAG 2.1 AA compliant
- [ ] Bundle size < 300KB gzipped

---

## NEXT STEPS

1. **Approval:** Review design system with stakeholders
2. **Development:** Follow Autopilot commands in sequence
3. **Testing:** Run integration tests after each phase
4. **Review:** Code review before merging to main
5. **Deployment:** Stage → Production rollout

---

**End of Redesign Instructions**

_Version: 2.0_  
_Last Updated: 2025-10-25_  
_Design Reference: Horizon UI - https://horizon-ui.com/horizon-ui-chakra/_
