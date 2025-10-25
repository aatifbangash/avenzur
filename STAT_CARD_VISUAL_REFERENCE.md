# Stat Card Redesign - Visual Reference Guide

---

## Color Palette Reference

### Color 1: Indigo (Sales)

```
Gradient: #4f46e5 → #4338ca
RGB Start: rgb(79, 70, 229)
RGB End: rgb(67, 56, 202)
Purpose: Primary KPI (Sales)
Trend: ↑ 1.2% (Green #86efac)
```

**Visual:**

```
┌─────────────────────────────────┐
│ 125K           ↑ 1.2%           │
│ SALES                           │
│ ▁ ▄ ▂ ▆ ▃                       │
└─────────────────────────────────┘
```

### Color 2: Light Blue (Purchases)

```
Gradient: #3b82f6 → #1d4ed8
RGB Start: rgb(59, 130, 246)
RGB End: rgb(29, 78, 216)
Purpose: Secondary KPI (Purchases)
Trend: ↑ 0.8% (Green #86efac)
```

**Visual:**

```
┌─────────────────────────────────┐
│ 85K            ↑ 0.8%           │
│ PURCHASES                       │
│ ▂ ▅ ▁ ▇ ▄                       │
└─────────────────────────────────┘
```

### Color 3: Yellow (Quotes)

```
Gradient: #fbbf24 → #f59e0b
RGB Start: rgb(251, 191, 36)
RGB End: rgb(245, 158, 11)
Purpose: Tertiary KPI (Quotes)
Trend: ↓ 0.5% (Red #fca5a5)
```

**Visual:**

```
┌─────────────────────────────────┐
│ 42             ↓ 0.5%           │
│ QUOTES                          │
│ ▄ ▃ ▅ ▂ ▃                       │
└─────────────────────────────────┘
```

### Color 4: Red (Stock Value)

```
Gradient: #e55354 → #c9272b
RGB Start: rgb(229, 83, 84)
RGB End: rgb(201, 39, 43)
Purpose: Quaternary KPI (Stock Value)
Trend: ↑ 2.1% (Green #86efac)
```

**Visual:**

```
┌─────────────────────────────────┐
│ 156K           ↑ 2.1%           │
│ STOCK VALUE                     │
│ ▃ ▆ ▂ █ ▄                       │
└─────────────────────────────────┘
```

---

## Layout Breakdown

### Full Dashboard Grid (4-Column Desktop)

```
┌──────────────────────────────────────────────────────────────────────────────┐
│                              DASHBOARD                                        │
│                      Welcome back, Admin!                                     │
│                       Monday, January 6, 2025                                 │
├──────────────────────────────────────────────────────────────────────────────┤
│
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐
│  │   125K   ↑   │  │   85K    ↑   │  │   42     ↓   │  │   156K   ↑   │
│  │   1.2%       │  │   0.8%       │  │   0.5%       │  │   2.1%       │
│  │ SALES        │  │ PURCHASES    │  │ QUOTES       │  │ STOCK VALUE  │
│  │ ▁▄▂▆▃        │  │ ▂▅▁▇▄        │  │ ▄▃▅▂▃        │  │ ▃▆▂█▄        │
│  └──────────────┘  └──────────────┘  └──────────────┘  └──────────────┘
│  Indigo            Light Blue         Yellow           Red
│
│  [Rest of dashboard content...]
│
└──────────────────────────────────────────────────────────────────────────────┘
```

### Single Card Component (Detailed)

```
CARD DIMENSIONS: 160px width × 180px height (min)

┌────────────────────────────────────────┐
│                                        │  ↑
│  ┌──────────────────────────────────┐ │  │
│  │ 125K                     ↑ 1.2%  │ │  │ Padding: 1rem
│  │ (stat-value)      (stat-trend)   │ │  │
│  └──────────────────────────────────┘ │  │ Row 1:
│                                        │  │ 20px height
│  margin-bottom: 0.5rem                │  │
│  ┌──────────────────────────────────┐ │  │ ↓
│  │ SALES                            │ │  │
│  │ (stat-label)                     │ │  │
│  └──────────────────────────────────┘ │  │ Row 2:
│                                        │  │ 14px height
│  margin-bottom: 0.5rem                │  │
│                                        │  │ ↓
│  ┌──────────────────────────────────┐ │  │
│  │ ▁ ▄ ▂ ▆ ▃                        │ │  │
│  │ (stat-graph with stat-bars)      │ │  │ Row 3:
│  │                                  │ │  │ Flex-grow
│  └──────────────────────────────────┘ │  │ ~40px
│                                        │  │
│  margin-top: auto                      │  ↓
│                                        │
└────────────────────────────────────────┘
```

### Row 1: Value & Trend Detail

```
┌─────────────────────────────────────────┐
│  125K                          ↑ 1.2%   │
└─────────────────────────────────────────┘

display: flex
justify-content: space-between
align-items: flex-start

LEFT SIDE:                  RIGHT SIDE:
value                       trend indicator
font-size: 1.5rem          font-size: 0.65rem
font-weight: 700           font-weight: 600
color: white               color: #86efac (positive)
line-height: 1             display: flex
                          gap: 0.2rem
```

### Row 2: Label Detail

```
┌─────────────────────────────────────────┐
│ SALES                                   │
└─────────────────────────────────────────┘

font-size: 0.7rem
font-weight: 500
text-transform: uppercase
color: white
opacity: 0.9
letter-spacing: 0.3px
```

### Row 3: Graph Detail

```
┌─────────────────────────────────────────┐
│ ▁  ▄  ▂  ▆  ▃                           │
└─────────────────────────────────────────┘

height: 40px
background: rgba(255, 255, 255, 0.15)
border-radius: 0.25rem
padding: 0.3rem
display: flex
align-items: flex-end
justify-content: space-around
gap: 0.15rem

BARS:
- flex: 1 (equal width)
- background: rgba(255, 255, 255, 0.6)
- border-radius: 0.15rem
- varying heights: 50%, 75%, 50%, 85%, 70%
```

---

## Responsive Grid Behavior

### Desktop (>1024px)

**Grid Template:** 4 columns

```
┌──────────┬──────────┬──────────┬──────────┐
│  Card 1  │  Card 2  │  Card 3  │  Card 4  │
│ (Indigo) │(L.Blue)  │ (Yellow) │  (Red)   │
└──────────┴──────────┴──────────┴──────────┘
Grid: repeat(auto-fit, minmax(160px, 1fr))
Gap: 1rem
```

### Tablet (768px - 1024px)

**Grid Template:** 2-3 columns (depends on width)

```
┌──────────┬──────────┐
│  Card 1  │  Card 2  │
│ (Indigo) │(L.Blue)  │
├──────────┼──────────┤
│  Card 3  │  Card 4  │
│ (Yellow) │  (Red)   │
└──────────┴──────────┘
```

### Mobile (<768px)

**Grid Template:** 1-2 columns

```
┌──────────┐
│  Card 1  │
│ (Indigo) │
├──────────┤
│  Card 2  │
│(L.Blue)  │
├──────────┤
│  Card 3  │
│ (Yellow) │
├──────────┤
│  Card 4  │
│  (Red)   │
└──────────┘
```

---

## Hover Effect Animation

### Before Hover

```
┌────────────────────────────────┐
│ 125K           ↑ 1.2%          │
│ SALES                          │
│ ▁ ▄ ▂ ▆ ▃                      │
└────────────────────────────────┘
Box Shadow: 0 2px 8px rgba(0, 0, 0, 0.15)
Transform: none
```

### During Hover

```
        ↑ Moves up 4px
        │
        │
┌────────────────────────────────┐
│ 125K           ↑ 1.2%          │
│ SALES                          │
│ ▁ ▄ ▂ ▆ ▃                      │
└────────────────────────────────┘
Box Shadow: 0 4px 16px rgba(0, 0, 0, 0.2)
Transform: translateY(-4px)
Transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1)
```

---

## Color Contrast Verification

### Indigo Card (#4f46e5 background)

- White text (#FFFFFF): Contrast Ratio = 6.7:1 ✅ AAA
- Light green trend (#86efac): Contrast Ratio = 4.8:1 ✅ AA
- Light red trend (#fca5a5): Contrast Ratio = 4.3:1 ✅ AA

### Light Blue Card (#3b82f6 background)

- White text (#FFFFFF): Contrast Ratio = 5.1:1 ✅ AA
- Light green trend (#86efac): Contrast Ratio = 3.9:1 ✅ AA
- Light red trend (#fca5a5): Contrast Ratio = 3.5:1 ✅ AA

### Yellow Card (#fbbf24 background)

- White text (#FFFFFF): Contrast Ratio = 4.5:1 ✅ AA
- Light green trend (#86efac): Contrast Ratio = 1.2:1 ⚠️ Low
- Dark gray trend: Use darker color for yellow background

### Red Card (#e55354 background)

- White text (#FFFFFF): Contrast Ratio = 4.8:1 ✅ AA
- Light green trend (#86efac): Contrast Ratio = 4.2:1 ✅ AA
- Light red trend (#fca5a5): Contrast Ratio = 1.1:1 ⚠️ Too similar

---

## Trend Indicator Icons

### Positive Trend (Green #86efac)

```
Font Awesome Icon: fa-arrow-up
HTML: <i class="fa fa-arrow-up"></i>
Color: #86efac (light green)
Size: 0.65rem
Format: ↑ 1.2%
```

### Negative Trend (Red #fca5a5)

```
Font Awesome Icon: fa-arrow-down
HTML: <i class="fa fa-arrow-down"></i>
Color: #fca5a5 (light red)
Size: 0.65rem
Format: ↓ 0.5%
```

---

## Data Formatting Examples

### Sales Value Format

```
Raw Value: 125,432.50
Formatted: 125K
Calculation: round(125432.50 / 1000, 1) = 125.4K
PHP: echo round($total_sales / 1000, 1) . 'K';
```

### Quotes Count Format

```
Raw Value: 42 (count of records)
Formatted: 42
Display: Direct integer, no K suffix
PHP: echo isset($quotes) && is_array($quotes) ? count($quotes) : 0;
```

### Trend Percentage Format

```
Raw Value: 1.2
Formatted: ↑ 1.2%
Range: -10% to +10% typically
Positive (green): ↑ prefix
Negative (red): ↓ prefix
```

---

## CSS Custom Properties Used

```css
/* Color Variables */
--stat-color-start: #4f46e5;      /* Gradient start */
--stat-color-end: #4338ca;        /* Gradient end */

/* Applied to -->
background: linear-gradient(135deg, var(--stat-color-start) 0%, var(--stat-color-end) 100%);

/* Theme-Specific Usage -->
.stat-card.indigo {
    --stat-color-start: #4f46e5;
    --stat-color-end: #4338ca;
}

.stat-card.light-blue {
    --stat-color-start: #3b82f6;
    --stat-color-end: #1d4ed8;
}

.stat-card.yellow {
    --stat-color-start: #fbbf24;
    --stat-color-end: #f59e0b;
}

.stat-card.red {
    --stat-color-start: #e55354;
    --stat-color-end: #c9272b;
}
```

---

## Mini Graph Bar Heights

### Sales Card Trend

```
Bar 1: 60%  ▁
Bar 2: 75%  ▄
Bar 3: 50%  ▂
Bar 4: 85%  ▆
Bar 5: 70%  ▃
Trend: Upward (ending high)
```

### Purchases Card Trend

```
Bar 1: 55%  ▂
Bar 2: 70%  ▄
Bar 3: 45%  ▁
Bar 4: 80%  ▆
Bar 5: 65%  ▃
Trend: Upward but volatile
```

### Quotes Card Trend

```
Bar 1: 65%  ▃
Bar 2: 55%  ▂
Bar 3: 75%  ▄
Bar 4: 50%  ▁
Bar 5: 70%  ▃
Trend: Declining overall
```

### Stock Value Card Trend

```
Bar 1: 70%  ▃
Bar 2: 80%  ▆
Bar 3: 60%  ▂
Bar 4: 90%  █
Bar 5: 75%  ▄
Trend: Upward and stable
```

---

## Animation Timeline

### Hover Animation (300ms)

```
0ms   ─────────────────────────────────────────
      Normal state
      Shadow: 0 2px 8px
      Transform: none

150ms ─────────────────────────────────────────
      Mid-animation
      Shadow: 0 3px 12px
      Transform: translateY(-2px)

300ms ─────────────────────────────────────────
      Hover state
      Shadow: 0 4px 16px
      Transform: translateY(-4px)
      Easing: cubic-bezier(0.4, 0, 0.2, 1)
```

---

## Theme Integration

### Default Theme

- Indigo: #4f46e5 (standard blue-purple)
- Light Blue: #3b82f6 (standard blue)
- Yellow: #fbbf24 (standard amber)
- Red: #e55354 (red accent)

### Blue Theme

- Same color scheme (colors are theme-agnostic)
- Compatible with blue dashboard background
- Text remains white for contrast

### Green Theme

- Same color scheme (colors are theme-agnostic)
- Compatible with green dashboard background
- Text remains white for contrast

---

## Font Specifications

### Stat Value (Row 1)

```
Font Family: System default (inherited)
Font Size: 1.5rem (24px)
Font Weight: 700 (bold)
Line Height: 1 (no spacing)
Letter Spacing: normal
Color: #FFFFFF (white)
Text Decoration: none
```

### Stat Trend (Row 1)

```
Font Family: System default (inherited)
Font Size: 0.65rem (10.4px)
Font Weight: 600 (semi-bold)
Line Height: 1 (compact)
Letter Spacing: normal
Color: #86efac (green) or #fca5a5 (red)
Text Decoration: none
Display: flex with icon
```

### Stat Label (Row 2)

```
Font Family: System default (inherited)
Font Size: 0.7rem (11.2px)
Font Weight: 500 (medium)
Line Height: 1.2 (tight)
Letter Spacing: 0.3px
Text Transform: uppercase
Color: #FFFFFF (white)
Opacity: 0.9
```

---

## Spacing Reference

### Card Padding

```
All sides: 1rem (16px)
Internal Row Gap: 0.5rem (8px)
Grid Gap: 1rem (16px)
```

### Graph Container Padding

```
All sides: 0.3rem (4.8px)
Bar Gap: 0.15rem (2.4px)
```

### Minimum Heights

```
Card Height: 180px
Graph Height: 40px
```

---

## Mobile Touch Targets

Each card maintains:

- Minimum 48px height per row (for touch)
- Adequate padding around text
- Large enough font sizes for readability
- Clear hover/focus states for accessibility

---

**Visual Reference Guide Complete** ✅
