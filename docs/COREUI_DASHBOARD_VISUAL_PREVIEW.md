# CoreUI Dashboard - Visual Preview & Component Showcase

## 📱 Dashboard Layout Overview

```
┌─────────────────────────────────────────────────────────────────┐
│  Dashboard                                    Welcome back, User! │
├─────────────────────────────────────────────────────────────────┤
│                                                                   │
│  ┌──────────────┬──────────────┬──────────────┬──────────────┐  │
│  │ Total Users  │   Revenue    │   Orders     │ Conversion   │  │
│  │              │              │              │              │  │
│  │   26K        │   $6,200     │    44K       │   2.49%      │  │
│  │   ↑ 12.5%    │   ↑ 8.2%     │   ↓ 3.1%     │   ↑ 84.7%    │  │
│  └──────────────┴──────────────┴──────────────┴──────────────┘  │
│                                                                   │
│  ┌────────────────────────────┬────────────────────────────────┐ │
│  │   Sales Overview           │   Traffic Sources              │ │
│  │   [Chart Placeholder]      │   [Chart Placeholder]          │ │
│  │   Lorem ipsum dolor        │                                │ │
│  └────────────────────────────┴────────────────────────────────┘ │
│                                                                   │
│  ┌────────────────────────────┬────────────────────────────────┐ │
│  │   Recent Orders            │   Recent Activity              │ │
│  │  ┌──────────────────────┐  │  ┌──────────────────────────┐ │ │
│  │  │ Order │ Customer │... │  │  │ [AH] Ahmed Hassan      │ │ │
│  │  │ #ORD1 │ Ahmed    │... │  │  │ Placed new order #ORD1 │ │ │
│  │  │ #ORD2 │ Fatima   │... │  │  │ 5 minutes ago          │ │ │
│  │  │ #ORD3 │Mohammed  │... │  │  │                        │ │ │
│  │  │ #ORD4 │ Layla    │... │  │  │ [FK] Fatima Khan       │ │ │
│  │  └──────────────────────┘  │  │ Updated profile info   │ │ │
│  │                            │  │ 1 hour ago             │ │ │
│  └────────────────────────────┴────────────────────────────────┘ │
│                                                                   │
│  ┌─────────────────────────────────────────────────────────────┐ │
│  │   Performance Metrics                                       │ │
│  │  Product A Sales        75%  [████████░]                   │ │
│  │  Product B Sales        50%  [█████░░░░░]                  │ │
│  │  Product C Sales        90%  [█████████░]                  │ │
│  └─────────────────────────────────────────────────────────────┘ │
│                                                                   │
└─────────────────────────────────────────────────────────────────┘
```

---

## 🎨 Color System & Usage

### Primary Colors

#### Blue (Primary)

```
Hex: #0d6efd
Usage: Main actions, highlights, primary metrics
Components: Stat icons, progress bars, links
```

#### Green (Success)

```
Hex: #198754
Usage: Positive metrics, completed status, success actions
Components: Stat icons, badges, checkmarks
```

#### Red (Danger)

```
Hex: #dc3545
Usage: Alerts, errors, negative metrics
Components: Error badges, warning indicators
```

#### Yellow (Warning)

```
Hex: #ffc107
Usage: Cautions, warnings, pending states
Components: Warning badges, alert icons
```

#### Cyan (Info)

```
Hex: #0dcaf0
Usage: Information, secondary metrics
Components: Info badges, secondary stat icons
```

---

## 📦 Component Examples

### 1. Stat Card

```html
┌─────────────────────────────────┐ │ Total Users │ │ │ │ 26,000 👥 │ │ ↑ 12.5%
from last month │ └─────────────────────────────────┘
```

**Features:**

- Large metric value
- Descriptive label
- Trend indicator
- Icon background

**Hover Effect:**

```
┌─────────────────────────────────┐
│◄──── Lifts up slightly ────►    │
│         Total Users             │
│                                 │
│  26,000                       👥 │
│  ↑ 12.5% from last month        │
└─────────────────────────────────┘
 (Shadow gets darker & larger)
```

### 2. Badge Component

**Badge Variants:**

```
Primary:    ┌─────────────┐
            │   Primary   │    (Blue background)
            └─────────────┘

Success:    ┌─────────────┐
            │   Success   │    (Green background)
            └─────────────┘

Danger:     ┌─────────────┐
            │   Danger    │    (Red background)
            └─────────────┘

Warning:    ┌─────────────┐
            │   Warning   │    (Yellow background)
            └─────────────┘
```

### 3. Progress Bar

**Without Label:**

```
Product A: [████████████████░░░░] 75%
Product B: [██████████░░░░░░░░░░] 50%
Product C: [██████████████████░░] 90%
```

**With Details:**

```
┌──────────────────────────────┐
│ Product A Sales        75%   │
│ [████████░░░░░░░░░░░░░░░░░] │
└──────────────────────────────┘
```

### 4. Table with Status Badges

```
┌──────────────────────────────────────────────────┐
│ Order ID | Customer    | Amount   | Status       │
├──────────────────────────────────────────────────┤
│ #ORD001  │ Ahmed       │ $2,500   │ ✓ Completed │
│ #ORD002  │ Fatima      │ $1,850   │ ⧗ Processing│
│ #ORD003  │ Mohammed    │ $3,200   │ ⏳ Pending   │
│ #ORD004  │ Layla       │ $1,650   │ ✓ Completed │
└──────────────────────────────────────────────────┘
```

### 5. Activity Feed

```
┌──────────────────────────────────────┐
│ [AH]  Ahmed Hassan                   │
│ ├─ Placed new order #ORD001          │
│ └─ 5 minutes ago                     │
│                                      │
│ [FK]  Fatima Khan                    │
│ ├─ Updated profile information       │
│ └─ 1 hour ago                        │
│                                      │
│ [MA]  Mohammed Ali                   │
│ ├─ Made payment for invoice #INV123  │
│ └─ 3 hours ago                       │
└──────────────────────────────────────┘
```

---

## 📐 Spacing & Layout Examples

### Card Spacing

```
┌─ Padding: 1.5rem ──────────────────┐
│  ┌─────────────────────────────┐   │
│  │  Card Header                │   │
│  ├─────────────────────────────┤   │
│  │  Card Body                  │   │
│  │  Content area with          │   │
│  │  1.5rem padding             │   │
│  ├─────────────────────────────┤   │
│  │  Card Footer                │   │
│  └─────────────────────────────┘   │
└─ Gap between cards: 1.5rem ────────┘
```

### Grid Gap

```
Desktop (1400px):
[Card] 1.5rem [Card] 1.5rem [Card] 1.5rem [Card]

Tablet (800px):
[Card] 1.5rem [Card]
[Card] 1.5rem [Card]

Mobile (400px):
[Card]
1.5rem
[Card]
1.5rem
[Card]
```

---

## 🎯 Typography Hierarchy

```
Dashboard
│
├─ Page Title (2rem, Bold 700)
│  └─ Subtitle (1rem, Regular 400)
│
├─ Card Header (1rem, Semibold 600)
│  └─ Card Body (1rem, Regular 400)
│     ├─ Stat Label (0.875rem, Semibold 600, Uppercase)
│     └─ Stat Value (2.5rem, Bold 700)
│
├─ Table Header (0.875rem, Semibold 600, Uppercase)
│  └─ Table Cell (1rem, Regular 400)
│
└─ Badge (0.75rem, Bold 600, Uppercase)
```

---

## 📱 Responsive Transitions

### Desktop → Tablet → Mobile

```
DESKTOP (1400px+):
┌─────────────────────────────────────────────────┐
│ [S1] [S2] [S3] [S4]                             │
│ [Chart 1]           [Chart 2]                   │
│ [Table]             [Activity]                  │
└─────────────────────────────────────────────────┘

TABLET (800px):
┌─────────────────────────────┐
│ [S1] [S2]                   │
│ [S3] [S4]                   │
│ [Chart 1]                   │
│ [Chart 2]                   │
│ [Table]                     │
│ [Activity]                  │
└─────────────────────────────┘

MOBILE (375px):
┌──────────────────┐
│ [S1]             │
│ [S2]             │
│ [S3]             │
│ [S4]             │
│ [Chart 1]        │
│ [Chart 2]        │
│ [Table]          │
│ [Activity]       │
└──────────────────┘
```

---

## 🎨 Visual Examples by Section

### Header Section

```
┌──────────────────────────────────────────────────┐
│                                                  │
│ Dashboard                                        │
│ Welcome back, User!                              │
│                                                  │
├──────────────────────────────────────────────────┤
```

### Statistics Grid

```
┌──────────────┬──────────────┬──────────────┬──────────────┐
│ Total Users  │   Revenue    │   Orders     │ Conversion   │
│              │              │              │              │
│   26,000     │   $6,200     │    44,000    │   2.49%      │
│   ↑12.5%     │   ↑8.2%      │   ↓3.1%      │   ↑84.7%     │
└──────────────┴──────────────┴──────────────┴──────────────┘
```

### Charts Section

```
┌────────────────────────────┬────────────────────────────┐
│  Sales Overview  [Primary] │ Traffic Sources [Success]  │
├────────────────────────────┼────────────────────────────┤
│                            │                            │
│      [CHART AREA]          │     [CHART AREA]           │
│      300px height          │     300px height           │
│                            │                            │
├────────────────────────────┴────────────────────────────┤
│ Lorem ipsum dolor sit amet, consectetur adipiscing elit │
└────────────────────────────────────────────────────────┘
```

### Data Section (2 Columns)

```
┌──────────────────────────┬──────────────────────────┐
│ Recent Orders [Secondary]│ Recent Activity [Today]  │
├──────────────────────────┼──────────────────────────┤
│ Order │ Customer │ Amt   │ [AH] Ahmed Hassan        │
│ #ORD1 │ Ahmed    │$2.5K  │ Placed order #ORD001     │
│ #ORD2 │ Fatima   │$1.8K  │ 5 min ago                │
│ #ORD3 │Mohammed  │$3.2K  │                          │
│ #ORD4 │ Layla    │$1.6K  │ [FK] Fatima Khan         │
│       │          │       │ Updated profile          │
│       │          │       │ 1 hour ago               │
└──────────────────────────┴──────────────────────────┘
```

### Performance Metrics

```
┌──────────────────────────────────────────┐
│ Performance Metrics                      │
├──────────────────────────────────────────┤
│ Product A Sales        75%               │
│ [████████░░░░░░░░░░░░░] 75%            │
│                                          │
│ Product B Sales        50%               │
│ [█████░░░░░░░░░░░░░░░░░] 50%           │
│                                          │
│ Product C Sales        90%               │
│ [█████████░░░░░░░░░░░░] 90%            │
└──────────────────────────────────────────┘
```

---

## 🔄 Interaction States

### Card Hover State

```
Before Hover:
┌─────────────────────────┐
│         Card            │
│        Content          │
└─────────────────────────┘
Shadow: Small

After Hover:
    ↗ Lifts up 4px
┌─────────────────────────┐
│         Card            │
│        Content          │
└─────────────────────────┘
Shadow: Large & darker
```

### Badge Variations

```
✓ Success Badge:
  └─ Green background (#d1e7dd)
     Dark green text (#0a3622)
     Use for: Completed, Paid, Active

⧗ Warning Badge:
  └─ Yellow background (#fff3cd)
     Dark yellow text (#664d03)
     Use for: Processing, Pending, Caution

⏳ Info Badge:
  └─ Cyan background (#cff4fc)
     Dark cyan text (#055160)
     Use for: Information, Secondary

✗ Danger Badge:
  └─ Red background (#f8d7da)
     Dark red text (#842029)
     Use for: Errors, Failed, Danger
```

### Progress Bar Fill

```
0%   ░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░
25%  ████░░░░░░░░░░░░░░░░░░░░░░░░░░
50%  ████████░░░░░░░░░░░░░░░░░░░░░░
75%  ████████████░░░░░░░░░░░░░░░░░░
100% ████████████████████████████████
```

---

## 📊 Chart Integration Examples

### Line Chart (Sales)

```
$10K │     ╱╲
     │    ╱  ╲
$8K  │   ╱    ╲   ╱╲
     │  ╱      ╲ ╱  ╲
$6K  │ ╱        ╲╱    ╲
     │────────────────────→ Months
     Jan Feb Mar Apr May
```

### Pie Chart (Distribution)

```
      North: 30%
         ╱────╲
        │ 40%  │ East
     40%│      │ 30%
        │      │
         ╲────╱
      South: 20%
```

### Bar Chart (Comparison)

```
Product A │████████████│ 80%
Product B │████░░░░░░░░│ 40%
Product C │██████████░░│ 60%
          └──────────────
          0%          100%
```

---

## 🎯 Mobile View Optimization

### Mobile Header

```
┌─────────────────┐
│ Dashboard   [≡] │  (Hamburger menu)
│ Welcome!        │
└─────────────────┘
```

### Mobile Stats (Stacked)

```
┌─────────────────┐
│ Total Users     │
│ 26,000          │
│ ↑ 12.5%         │
└─────────────────┘

┌─────────────────┐
│ Total Revenue   │
│ $6,200          │
│ ↑ 8.2%          │
└─────────────────┘

(... etc)
```

### Mobile Tables (Scrollable)

```
┌─────────────────────────┐
│ Order│Cust │Amount│Stat │
├─────────────────────────┤
│#ORD1 │Ahmed│$2.5K │✓    │ ←[scroll]
│#ORD2 │Fati │$1.8K │⧗    │
│#ORD3 │Moha │$3.2K │⏳    │
│#ORD4 │Laya │$1.6K │✓    │
└─────────────────────────┘
```

---

## 🎨 Dark Mode Preview

```
Dark Background: #161b22
Card Background: #212529
Text: #f8f9fa (light)
Accent: Remains same

┌─────────────────────────┐ │
│  Dashboard              │ │
│  Welcome back!          │ │
├─────────────────────────┤ │
│ ┌────────┬────────────┐ │ │
│ │26,000  │ Revenue    │ │ │
│ │Users   │ $6,200     │ │ │
│ └────────┴────────────┘ │ │
│ ┌─────────────────────┐ │ │
│ │ [Chart Area]        │ │ │
│ └─────────────────────┘ │ │
└─────────────────────────┘ │
```

---

## ✨ Animation Specifications

### Card Hover

```
Duration: 300ms
Easing: cubic-bezier(0.4, 0, 0.2, 1)
Transform: translateY(-4px)
Shadow: 0 0.5rem 1rem rgba(0,0,0,0.15)
```

### Progress Bar Fill

```
Duration: 300ms
Easing: ease
Property: width
```

### Number Counter (Optional)

```
Duration: 400ms
Easing: ease-out
Property: transform
```

### Fade In

```
Duration: 300ms
Easing: ease-in
Property: opacity
From: 0
To: 1
```

---

## 🎯 Component Size Reference

| Component       | Width     | Height | Notes      |
| --------------- | --------- | ------ | ---------- |
| Stat Card       | min 280px | auto   | Responsive |
| Chart Card      | min 450px | 300px  | Responsive |
| Table Card      | 100%      | auto   | Scrollable |
| Stat Icon       | 80px      | 80px   | Desktop    |
| Stat Icon       | 60px      | 60px   | Mobile     |
| Badge           | auto      | 32px   | Inline     |
| Activity Avatar | 40px      | 40px   | Circle     |

---

## 📋 Checklist for Visual Verification

- [ ] Cards have proper shadows
- [ ] Hover effect lifts cards
- [ ] Colors match spec
- [ ] Typography hierarchy clear
- [ ] Badges display correctly
- [ ] Tables are aligned
- [ ] Progress bars fill correctly
- [ ] Activity feed shows avatars
- [ ] Responsive on mobile
- [ ] Dark mode looks good
- [ ] Animations are smooth
- [ ] No content overflow
- [ ] Spacing is consistent
- [ ] Icons display properly
- [ ] All text is readable

---

**Visual Design Complete** ✨
**Version**: 1.0
**Date**: October 2025
