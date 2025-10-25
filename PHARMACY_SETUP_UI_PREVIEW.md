# Pharmacy Hierarchy Setup - UI Preview & Specifications

## 📐 UI Layout Breakdown

---

## 1️⃣ MAIN PAGE LAYOUT

```
┌────────────────────────────────────────────────────────────────────┐
│  Header: Pharmacy Hierarchy Setup                      [×] [-] [□] │
├────────────────────────────────────────────────────────────────────┤
│                                                                    │
│  📋 Navigation Tabs                                              │
│  ┌─────────────────┬──────────────────┬─────────────────┐        │
│  │ 🏥 Pharmacies  │ 📍 Branches      │ 🗂️ Hierarchy   │        │
│  └─────────────────┴──────────────────┴─────────────────┘        │
│                                                                    │
├────────────────────────────────────────────────────────────────────┤
│                      TAB CONTENT AREA                             │
│                    (See below for each tab)                       │
└────────────────────────────────────────────────────────────────────┘
```

---

## 2️⃣ PHARMACIES TAB

```
┌────────────────────────────────────────────────────────────────┐
│  Manage Pharmacies                                             │
│  Add and manage pharmacy locations within your pharmacy group. │
├────────────────────────────────────────────────────────────────┤
│                                                                │
│  Pharmacy Group:  [Select Pharmacy Group ▼]                  │
│                                    [+ Add Pharmacy]           │
│                                                                │
├────────────────────────────────────────────────────────────────┤
│                          PHARMACIES TABLE                       │
│  ┌──────┬────────┬──────────┬────────────┬──────┬───────┬──┐  │
│  │ ID   │ Code   │ Name     │ Address    │Phone │Type   │⚙ │  │
│  ├──────┼────────┼──────────┼────────────┼──────┼───────┼──┤  │
│  │ 1    │PHARM001│Main Store│Dubai, UAE  │+971  │Pharmacy│  │  │
│  │ 2    │PHARM002│North     │Sharjah,UAE │+971  │Pharmacy│  │  │
│  │ 3    │PHARM003│South     │Abu Dhabi   │+971  │Pharmacy│  │  │
│  ├──────┼────────┼──────────┼────────────┼──────┼───────┼──┤  │
│  │      │        │          │            │      │       │  │  │
│  └──────┴────────┴──────────┴────────────┴──────┴───────┴──┘  │
│                                                                │
│  ℹ️  Click on pharmacy row to edit or delete                 │
└────────────────────────────────────────────────────────────────┘
```

---

## 3️⃣ BRANCHES TAB

```
┌────────────────────────────────────────────────────────────────┐
│  Manage Branches                                               │
│  Add and manage branches under pharmacies.                    │
├────────────────────────────────────────────────────────────────┤
│                                                                │
│  Pharmacy:  [Select Pharmacy ▼]                              │
│                              [+ Add Branch]                   │
│                                                                │
├────────────────────────────────────────────────────────────────┤
│                         BRANCHES TABLE                          │
│  ┌──────┬────────┬──────────┬────────────┬──────────┬──────┬──┐ │
│  │ ID   │ Code   │ Name     │ Pharmacy   │ Address  │Phone │⚙ │ │
│  ├──────┼────────┼──────────┼────────────┼──────────┼──────┼──┤ │
│  │ 1    │BR001   │Branch 1  │Main Store  │Dubai-1   │+971  │  │ │
│  │ 2    │BR002   │Branch 2  │North Pharm │Dubai-2   │+971  │  │ │
│  ├──────┼────────┼──────────┼────────────┼──────────┼──────┼──┤ │
│  │      │        │          │            │          │      │  │ │
│  └──────┴────────┴──────────┴────────────┴──────────┴──────┴──┘ │
│                                                                │
│  ℹ️  Select a pharmacy to view its branches                 │
└────────────────────────────────────────────────────────────────┘
```

---

## 4️⃣ HIERARCHY VIEW TAB

```
┌────────────────────────────────────────────────────────────────┐
│  Organization Hierarchy                                        │
│  Visual representation of your organization structure.         │
├────────────────────────────────────────────────────────────────┤
│                                                                │
│  ┌──────────────────────────────────────────────────────────┐ │
│  │                                                          │ │
│  │     ┌─────────────────────────────────────────────┐    │ │
│  │     │  🏢 Pharmacy Group 1 (GROUP001)             │    │ │
│  │     └─────────────────────────────────────────────┘    │ │
│  │                        │                               │ │
│  │      ┌─────────────────┼─────────────────┐            │ │
│  │      │                 │                 │            │ │
│  │  ┌───────────┐   ┌──────────┐   ┌──────────┐         │ │
│  │  │🏥 Pharmacy│   │🏥 Pharmacy│   │🏥 Pharmacy│        │ │
│  │  │(PHARM001) │   │(PHARM002) │   │(PHARM003) │        │ │
│  │  └───────────┘   └──────────┘   └──────────┘         │ │
│  │        │               │               │              │ │
│  │    ┌───┴────┐      ┌───┴────┐    ┌────┴────┐         │ │
│  │    │        │      │        │    │        │          │ │
│  │ ┌──────┐ ┌──────┐ ┌──────┐ ┌──────┐ ┌──────┐ ┌──────┐ │ │
│  │ │BR001 │ │BR002 │ │BR003 │ │BR004 │ │BR005 │ │BR006 │ │ │
│  │ └──────┘ └──────┘ └──────┘ └──────┘ └──────┘ └──────┘ │ │
│  │                                                          │ │
│  └──────────────────────────────────────────────────────────┘ │
│                                                                │
│  🟪 Purple = Pharmacy      🟪 Pink = Branch                  │
│  ℹ️  Click node to view details                             │
└────────────────────────────────────────────────────────────────┘
```

---

## 5️⃣ ADD PHARMACY MODAL

```
┌─────────────────────────────────────────────────────────────────┐
│  🏥 Add Pharmacy                                           [×]  │
├─────────────────────────────────────────────────────────────────┤
│                                                                 │
│  Enter pharmacy information                                    │
│                                                                 │
│  Pharmacy Group *                                              │
│  ┌──────────────────────────────────────────────────────────┐  │
│  │ Select parent company...                                │  │
│  └──────────────────────────────────────────────────────────┘  │
│  Select parent company/pharmacy group                         │
│                                                                 │
│  Pharmacy Code *                                               │
│  ┌──────────────────────────────────────────────────────────┐  │
│  │ PHARM001                                                 │  │
│  └──────────────────────────────────────────────────────────┘  │
│  Unique code for this pharmacy                                 │
│                                                                 │
│  Pharmacy Name *                                               │
│  ┌──────────────────────────────────────────────────────────┐  │
│  │ Main Pharmacy Store                                      │  │
│  └──────────────────────────────────────────────────────────┘  │
│                                                                 │
│  Address *                                                     │
│  ┌──────────────────────────────────────────────────────────┐  │
│  │ Sheikh Zayed Road, Dubai, UAE                            │  │
│  │                                                          │  │
│  │                                                          │  │
│  └──────────────────────────────────────────────────────────┘  │
│                                                                 │
│  Phone *                                                       │
│  ┌──────────────────────────────────────────────────────────┐  │
│  │ +971 4 123 4567                                          │  │
│  └──────────────────────────────────────────────────────────┘  │
│                                                                 │
│  Email                                                         │
│  ┌──────────────────────────────────────────────────────────┐  │
│  │ info@pharmacy.ae                                         │  │
│  └──────────────────────────────────────────────────────────┘  │
│                                                                 │
│  ┌─────────────────────────────────────────────────────────┐   │
│  │ ⚙️ Main Warehouse                              [▼ Hide] │   │
│  ├─────────────────────────────────────────────────────────┤   │
│  │ One main warehouse is created. More can be added later. │   │
│  │                                                         │   │
│  │ Warehouse Code *                                        │   │
│  │ ┌──────────────────────────────────────────────────┐   │   │
│  │ │ WH001                                            │   │   │
│  │ └──────────────────────────────────────────────────┘   │   │
│  │ Unique code for main warehouse                         │   │
│  │                                                         │   │
│  │ Warehouse Name *                                       │   │
│  │ ┌──────────────────────────────────────────────────┐   │   │
│  │ │ Main Warehouse                                   │   │   │
│  │ └──────────────────────────────────────────────────┘   │   │
│  └─────────────────────────────────────────────────────────┘   │
│                                                                 │
├─────────────────────────────────────────────────────────────────┤
│  [Cancel]                                    [💾 Add Pharmacy] │
└─────────────────────────────────────────────────────────────────┘
```

---

## 6️⃣ ADD BRANCH MODAL

```
┌─────────────────────────────────────────────────────────────────┐
│  📍 Add Branch                                              [×]  │
├─────────────────────────────────────────────────────────────────┤
│                                                                 │
│  Enter branch information                                      │
│                                                                 │
│  Pharmacy *                                                    │
│  ┌──────────────────────────────────────────────────────────┐  │
│  │ Main Pharmacy Store (PHARM001)                           │  │
│  └──────────────────────────────────────────────────────────┘  │
│  Select parent pharmacy                                        │
│                                                                 │
│  Branch Code *                                                 │
│  ┌──────────────────────────────────────────────────────────┐  │
│  │ BR001                                                    │  │
│  └──────────────────────────────────────────────────────────┘  │
│  Unique code for this branch                                   │
│                                                                 │
│  Branch Name *                                                 │
│  ┌──────────────────────────────────────────────────────────┐  │
│  │ Downtown Branch                                          │  │
│  └──────────────────────────────────────────────────────────┘  │
│                                                                 │
│  Address *                                                     │
│  ┌──────────────────────────────────────────────────────────┐  │
│  │ Downtown Dubai, UAE                                      │  │
│  │                                                          │  │
│  │                                                          │  │
│  └──────────────────────────────────────────────────────────┘  │
│                                                                 │
│  Phone *                                                       │
│  ┌──────────────────────────────────────────────────────────┐  │
│  │ +971 4 234 5678                                          │  │
│  └──────────────────────────────────────────────────────────┘  │
│                                                                 │
│  Email                                                         │
│  ┌──────────────────────────────────────────────────────────┐  │
│  │ downtown@pharmacy.ae                                     │  │
│  └──────────────────────────────────────────────────────────┘  │
│                                                                 │
├─────────────────────────────────────────────────────────────────┤
│  [Cancel]                                     [💾 Add Branch]   │
└─────────────────────────────────────────────────────────────────┘
```

---

## 7️⃣ COLOR SCHEME

| Element        | Color           | Hex             | Usage                             |
| -------------- | --------------- | --------------- | --------------------------------- |
| Primary Header | Dark Blue       | #3c8dbc         | Main section headers, Add buttons |
| Pharmacy Modal | Light Blue      | #3c8dbc         | Add Pharmacy modal header         |
| Branch Modal   | Teal            | #0097bc         | Add Branch modal header           |
| Success        | Green           | #00a65a         | Success notifications             |
| Danger         | Red             | #dd4b39         | Delete buttons, errors            |
| Warning        | Orange          | #f39c12         | Warnings, alerts                  |
| Info           | Blue            | #0073b7         | Information messages              |
| Pharmacy Node  | Purple Gradient | #667eea-#764ba2 | Hierarchy tree                    |
| Branch Node    | Pink Gradient   | #f093fb-#f5576c | Hierarchy tree                    |
| Background     | Light Gray      | #f5f5f5         | Container backgrounds             |

---

## 8️⃣ RESPONSIVE BREAKPOINTS

### Desktop (>1024px)

```
┌─────────────────────────────────────────────────────────────────┐
│  Full Width Layout                                              │
│  ┌───────────────┬───────────────┬───────────────┐              │
│  │ Select Group  │               │ [+ Add]       │              │
│  ├───────────────┴───────────────┴───────────────┤              │
│  │                   DATA TABLE                  │              │
│  │  (Multiple columns visible)                  │              │
│  └────────────────────────────────────────────────┘              │
└─────────────────────────────────────────────────────────────────┘
```

### Tablet (768-1024px)

```
┌──────────────────────────────┐
│  Adjusted Layout             │
│  ┌──────────────────────────┐│
│  │ Select Group             ││
│  │ [+ Add]                  ││
│  ├──────────────────────────┤│
│  │   DATA TABLE (scrolls)   ││
│  │                          ││
│  └──────────────────────────┘│
└──────────────────────────────┘
```

### Mobile (<768px)

```
┌─────────────────────────┐
│  Stack Layout           │
│  ┌─────────────────────┐│
│  │ Select Group        ││
│  └─────────────────────┘│
│  ┌─────────────────────┐│
│  │ [+ Add Button]      ││
│  └─────────────────────┘│
│  ┌─────────────────────┐│
│  │ TABLE (scrolls H)   ││
│  │ [Row] [Edit][Del]   ││
│  └─────────────────────┘│
└─────────────────────────┘
```

---

## 9️⃣ INTERACTION FLOWS

### Creating Pharmacy Flow

```
User on Pharmacies Tab
      ↓
Select Pharmacy Group
      ↓
Click [+ Add Pharmacy]
      ↓
Modal Opens → Fill Form
      ↓
Submit Form
      ↓
Server Validates
      ↓
Create:
  ├── sma_warehouses (pharmacy)
  ├── loyalty_pharmacies
  └── sma_warehouses (mainwarehouse)
      ↓
Success Notification
      ↓
Table Refreshes
      ↓
Hierarchy Updates
```

### Creating Branch Flow

```
User on Branches Tab
      ↓
Select Pharmacy from dropdown
      ↓
Click [+ Add Branch]
      ↓
Modal Opens → Fill Form
      ↓
Submit Form
      ↓
Server Validates
      ↓
Create:
  ├── sma_warehouses (branch)
  └── loyalty_branches
      ↓
Success Notification
      ↓
Table Refreshes
      ↓
Hierarchy Updates
```

---

## 🔟 ACTION BUTTONS

| Button           | Location       | Action                     | Color        |
| ---------------- | -------------- | -------------------------- | ------------ |
| [+ Add Pharmacy] | Pharmacies Tab | Open Add Pharmacy Modal    | Primary Blue |
| [+ Add Branch]   | Branches Tab   | Open Add Branch Modal      | Primary Blue |
| [Edit]           | Table Rows     | Edit item (future)         | Info         |
| [Delete]         | Table Rows     | Delete with confirmation   | Danger       |
| [Submit]         | Modals         | Create new item            | Primary Blue |
| [Cancel]         | Modals         | Close modal without saving | Secondary    |

---

## 1️⃣1️⃣ NOTIFICATIONS

### Success

```
✓ Pharmacy created successfully
✓ Branch created successfully
✓ Item deleted successfully
```

### Error

```
✗ Pharmacy Code already exists
✗ Required fields missing
✗ Database error occurred
```

### Info

```
ℹ Click on pharmacy row to edit or delete
ℹ Click node to view details
```

---

## 1️⃣2️⃣ ACCESSIBILITY FEATURES

- ✅ Keyboard navigation (Tab order)
- ✅ ARIA labels on form inputs
- ✅ Color-blind safe palette (with text labels)
- ✅ High contrast mode support
- ✅ Screen reader compatible
- ✅ Form validation messages
- ✅ Focus indicators on buttons

---

## 1️⃣3️⃣ PERFORMANCE METRICS

| Metric           | Target  | Status |
| ---------------- | ------- | ------ |
| Page Load        | < 2s    | ⏳     |
| Table Sort       | < 500ms | ⏳     |
| Modal Open       | < 300ms | ⏳     |
| Form Submit      | < 1s    | ⏳     |
| Hierarchy Render | < 1.5s  | ⏳     |

---

## 1️⃣4️⃣ BROWSER SUPPORT

| Browser | Version | Support    |
| ------- | ------- | ---------- |
| Chrome  | 90+     | ✅ Full    |
| Firefox | 88+     | ✅ Full    |
| Safari  | 14+     | ✅ Full    |
| Edge    | 90+     | ✅ Full    |
| IE 11   | 11      | ⚠️ Limited |

---

**End of UI Preview & Specifications**
