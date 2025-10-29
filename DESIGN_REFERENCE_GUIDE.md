# TailAdmin-Inspired Modern Admin UI - Design Reference Guide

## Visual Layout

```
┌─────────────────────────────────────────────────────────────────┐
│ ☰  AVENZUR                    Search...          🔔  👤  Logout  │ ← Top Bar (60px, #FFFFFF)
├──────────────────────────────────────────────────────────────────┤
│
│ Dashboard        │                                               │
│ ▼ Reports                                                        │
│   - Stock         │  Main Content Area                          │
│   - Item Mvmt     │  (Takes remaining space)                    │
│ ▼ Products        │                                              │
│   - List          │                                              │
│   - Add           │                                              │
│                   │                                              │
│ (Sidebar: 260px)  │                                              │
│                   │                                              │
└─────────────────────────────────────────────────────────────────┘

MINIMIZED STATE (Sidebar: 60px):
┌─────────────────────────────────────────────────────────────────┐
│ ☰  AVENZUR                    Search...          🔔  👤  Logout  │
├──┬───────────────────────────────────────────────────────────────┤
│🏠 │ Main Content Area                                            │
│📊 │ (Takes most of the space)                                   │
│📦 │                                                              │
│👥 │                                                              │
│⚙️ │                                                              │
└──┴───────────────────────────────────────────────────────────────┘

MOBILE STATE (< 768px):
┌──────────────────────────────────────────────────────────────────┐
│ ☰  AVENZUR         Search     🔔  👤                              │
├──────────────────────────────────────────────────────────────────┤
│                                                                   │
│  Main Content Area                                               │
│  (Full Width)                                                    │
│                                                                   │
│                                                                   │
└──────────────────────────────────────────────────────────────────┘
(Sidebar appears as overlay drawer when ☰ is clicked)
```

---

## Color Palette

### Light Theme Colors

```css
--color-primary: #3b82f6; /* Blue - Primary actions */
--color-primary-light: #eff6ff; /* Light blue - Background for active items */
--color-white: #ffffff; /* Main background */
--color-gray-50: #f9fafb; /* Light gray - Alternate backgrounds */
--color-gray-100: #f3f4f6; /* Light gray - Hover states */
--color-gray-200: #e5e7eb; /* Light gray - Borders */
--color-gray-600: #4b5563; /* Medium gray - Secondary text */
--color-gray-700: #374151; /* Dark gray - Body text */
--color-gray-900: #1f2937; /* Very dark gray - Primary text */
--color-red: #ef4444; /* Red - Danger/Delete actions */
--color-green: #10b981; /* Green - Success states */
--color-yellow: #f59e0b; /* Yellow - Warning states */
```

---

## Spacing System

```
Spacing scale (Base: 4px):
--space-1: 4px
--space-2: 8px
--space-3: 12px
--space-4: 16px
--space-6: 24px
--space-8: 32px
--space-12: 48px
--space-16: 64px
```

---

## Typography

```css
/* Headings */
h1: 28px, 700 weight, letter-spacing: -0.5px
h2: 24px, 600 weight
h3: 20px, 600 weight
h4: 16px, 600 weight

/* Body */
Body: 14px, 400 weight, line-height: 1.5
Body Small: 13px, 400 weight, line-height: 1.5
Label: 12px, 500 weight, text-transform: uppercase

/* Sidebar */
Menu Item: 14px, 500 weight
Menu Item Active: 14px, 600 weight, color: #3B82F6
```

---

## Shadows (Elevation)

```css
/* Subtle shadow for cards/panels */
--shadow-sm: 0 1px 2px rgba(0, 0, 0, 0.05);

/* Default shadow */
--shadow: 0 1px 3px rgba(0, 0, 0, 0.1), 0 1px 2px rgba(0, 0, 0, 0.06);

/* Medium shadow */
--shadow-md: 0 4px 6px rgba(0, 0, 0, 0.1), 0 2px 4px rgba(0, 0, 0, 0.06);

/* Large shadow (modals, dropdowns) */
--shadow-lg: 0 10px 15px rgba(0, 0, 0, 0.1), 0 4px 6px rgba(0, 0, 0, 0.05);
```

---

## Border Radius

```css
--radius-sm: 4px
--radius: 6px
--radius-md: 8px
--radius-lg: 12px
--radius-full: 9999px (for fully rounded elements)
```

---

## Component Specifications

### Top Bar Component

```
Height: 60px
Background: #FFFFFF
Border: 1px solid #E5E7EB (bottom only)
Shadow: 0 1px 3px rgba(0, 0, 0, 0.1)
Position: sticky/fixed
Z-index: 100

Layout (Flexbox):
├─ Toggle Button (40x40px)
│  └─ Icon: ☰ (hamburger)
│  └─ Color: #6B7280
│  └─ Hover: Background #F3F4F6
│
├─ Brand (flex-grow: 1)
│  └─ Logo text: 16px, 600 weight, #1F2937
│  └─ Hidden on <768px screens
│
├─ Search Bar (optional, 300px max)
│  └─ Background: #F9FAFB
│  └─ Border: 1px #E5E7EB
│  └─ Placeholder: #6B7280
│  └─ Hidden on <576px screens
│
└─ Right Section (flexbox, gap: 16px)
   ├─ Notification Bell (24px icon)
   │  └─ Color: #6B7280
   │  └─ Badge: Red circle with count
   │
   ├─ User Profile Dropdown
   │  ├─ Avatar: 32x32px, rounded-full
   │  ├─ Text: "Welcome [Username]" (hidden on mobile)
   │  ├─ Icon: Dropdown chevron
   │  └─ Dropdown Menu:
   │     ├─ Profile
   │     ├─ Change Password
   │     ├─ Settings (if exists)
   │     ├─ Divider
   │     └─ Logout
   │
   └─ Logout Button
      └─ Icon + Text (text hidden on mobile)
```

### Sidebar Component (Desktop: 260px)

```
Width: 260px (expanded) / 60px (minimized)
Background: #FFFFFF
Border: 1px solid #E5E7EB (right only)
Transition: width 300ms ease-in-out
Z-index: 90
Overflow: auto

Padding: 16px 0
Gap between items: 4px

Menu Item Structure:
├─ <li class="sidebar-nav-item">
│  ├─ Icon (24x24px, #6B7280)
│  ├─ Text Label (14px, 500 weight, #1F2937)
│  ├─ On hover: Background #F3F4F6
│  ├─ On active:
│  │  ├─ Background: #EFF6FF
│  │  ├─ Text Color: #3B82F6
│  │  └─ Left Border: 3px solid #3B82F6
│  │
│  └─ If has children:
│     ├─ Chevron icon (rotate 180° when expanded)
│     ├─ Submenu items (indented 20px more):
│     │  ├─ Icon (20x20px, #9CA3AF)
│     │  ├─ Text (13px, 400 weight, #4B5563)
│     │  └─ On hover: Background #F9FAFB
│     │
│     └─ Submenu animation: max-height 250ms ease

When Minimized (60px):
├─ Icon visible, text hidden
├─ Tooltip on hover (optional)
├─ Chevron hidden
└─ Submenus collapsed (can click to expand in drawer mode)
```

### Sidebar - Mobile Drawer (<768px)

```
Position: fixed, full-screen overlay
Width: 280px (drawer) + Backdrop
Background (Drawer): #FFFFFF
Backdrop: rgba(0, 0, 0, 0.5)
Animation: slide-in 300ms ease-in-out
Z-index: 110

Interactions:
├─ Click toggle button → drawer slides in
├─ Click backdrop → drawer slides out
├─ Click menu item → drawer slides out
├─ ESC key → drawer slides out
└─ Scrollable content if menu exceeds viewport
```

---

## Transition/Animation Timings

```css
Sidebar Toggle: 300ms ease-in-out
Submenu Expand: 250ms ease
Chevron Rotation: 200ms ease-in-out
Hover Effects: 150ms ease
Dropdown Fade: 150ms ease-in
Backdrop Fade: 300ms ease
```

---

## Responsive Breakpoints

```
Mobile: < 576px
Tablet: 576px - 767px
Desktop: ≥ 768px

At Desktop (≥768px):
├─ Sidebar visible, collapsible
├─ Top bar full layout
├─ All search visible
└─ Both icon and text in navigation

At Tablet (576px - 767px):
├─ Sidebar hidden by default (drawer on toggle)
├─ Top bar condensed
├─ Search hidden, show search icon
└─ Avatar visible, text hidden

At Mobile (<576px):
├─ Sidebar hidden by default (drawer on toggle)
├─ Top bar minimal
├─ Only icons in top bar
└─ Full-screen drawer when opened
```

---

## Example HTML Structure

### Header

```html
<header class="topbar" role="banner">
	<div class="topbar-container">
		<!-- Toggle Button -->
		<button
			class="topbar-toggle"
			data-toggle="sidebar"
			aria-label="Toggle Sidebar"
		>
			<i class="fa fa-bars"></i>
		</button>

		<!-- Brand -->
		<a href="/admin" class="topbar-brand">
			<span class="topbar-brand-logo">⬛</span>
			<span class="topbar-brand-text">AVENZUR</span>
		</a>

		<!-- Search -->
		<div class="topbar-search">
			<i class="fa fa-search"></i>
			<input type="text" placeholder="Search..." />
		</div>

		<!-- Right Section -->
		<div class="topbar-actions">
			<!-- Notifications -->
			<button class="topbar-action-btn" aria-label="Notifications">
				<i class="fa fa-bell"></i>
				<span class="badge">3</span>
			</button>

			<!-- User Profile Dropdown -->
			<div class="topbar-profile">
				<button class="topbar-profile-btn" data-toggle="dropdown">
					<img src="avatar.jpg" alt="User" class="topbar-avatar" />
					<span class="topbar-username">John Doe</span>
					<i class="fa fa-chevron-down"></i>
				</button>
				<ul class="dropdown-menu">
					<li><a href="/admin/profile">Profile</a></li>
					<li><a href="/admin/change-password">Change Password</a></li>
					<li class="divider"></li>
					<li><a href="/admin/logout">Logout</a></li>
				</ul>
			</div>
		</div>
	</div>
</header>
```

### Sidebar

```html
<aside class="sidebar-wrapper" data-collapsed="false" role="navigation">
	<ul class="sidebar-nav">
		<!-- Simple Item -->
		<li class="sidebar-nav-item">
			<a href="/admin/dashboard" class="sidebar-nav-link">
				<i class="sidebar-icon fa fa-dashboard"></i>
				<span class="sidebar-label">Dashboard</span>
			</a>
		</li>

		<!-- Collapsible Item -->
		<li class="sidebar-nav-item has-children">
			<a href="#" class="sidebar-nav-toggle" data-toggle="submenu">
				<i class="sidebar-icon fa fa-barcode"></i>
				<span class="sidebar-label">Products</span>
				<i class="sidebar-chevron fa fa-chevron-right"></i>
			</a>
			<ul class="sidebar-submenu">
				<li class="sidebar-nav-item">
					<a href="/admin/products" class="sidebar-nav-link">
						<i class="sidebar-icon fa fa-list"></i>
						<span class="sidebar-label">List Products</span>
					</a>
				</li>
				<li class="sidebar-nav-item">
					<a href="/admin/products/add" class="sidebar-nav-link">
						<i class="sidebar-icon fa fa-plus-circle"></i>
						<span class="sidebar-label">Add Product</span>
					</a>
				</li>
			</ul>
		</li>
	</ul>
</aside>
```

---

## Implementation Priority

### Phase 1 (MVP)

- Create CSS file with color variables and basic layout
- Update header.php with modern top bar HTML
- Update new_customer_menu.php with modern sidebar HTML
- Create basic JavaScript for toggle functionality

### Phase 2 (Enhancement)

- Add smooth animations and transitions
- Implement localStorage for sidebar state persistence
- Mobile drawer functionality
- Active menu item detection

### Phase 3 (Polish)

- Add notifications badge
- Search functionality
- Accessibility improvements
- RTL language support (if needed)

---

## Testing Matrix

| Feature        | Desktop         | Tablet    | Mobile  |
| -------------- | --------------- | --------- | ------- |
| Toggle Sidebar | Collapse/Expand | Drawer    | Drawer  |
| Top Bar        | Full            | Condensed | Minimal |
| Submenu Expand | Smooth          | Drawer    | Drawer  |
| Responsive     | ✓               | ✓         | ✓       |
| Mobile Drawer  | N/A             | ✓         | ✓       |
| Persist State  | ✓               | ✓         | ✓       |

---

## Browser Support

- Chrome 90+
- Firefox 88+
- Safari 14+
- Edge 90+
- Mobile browsers (iOS Safari 14+, Chrome Android)

---

## Performance Considerations

1. **CSS Transitions**: Use `transform` and `opacity` for animations (GPU accelerated)
2. **JavaScript**: Debounce resize events
3. **Avoid**: Animating `width` on sidebar directly, use `transform: scaleX()` or use CSS Grid
4. **LocalStorage**: Store only boolean state, not large objects
5. **Mobile**: Prevent layout thrashing by batching DOM updates
