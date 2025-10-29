# Quick Reference: Code Snippets for Modern Admin UI

## How to Use This Document

Copy each section and paste it directly into GitHub Copilot's chat or prompt window for quick code generation.

---

## 1. CSS Framework (Copy to Copilot)

```
Create a modern admin dashboard CSS file for CodeIgniter ERP with these specifications:

File: /assets/styles/modern-admin-ui.css

Requirements:
1. CSS Variables for light theme with colors: #3B82F6, #FFFFFF, #F9FAFB, #1F2937, #6B7280, #E5E7EB
2. Sidebar styles:
   - Default width: 260px, minimized: 60px
   - White background with right border
   - Menu items with padding 12px, icons 24px
   - Active state: light blue background (#EFF6FF), blue text (#3B82F6)
   - Hover state: light gray background (#F3F4F6)
   - Transitions: 300ms ease-in-out
   - Submenu with 20px left padding, chevron icon rotates on expand
3. Top bar (60px height):
   - White background, bottom border, subtle shadow
   - Layout: toggle button (40x40), logo/brand, search bar, right actions
   - Sticky position, z-index: 100
4. Mobile drawer (< 768px):
   - Sidebar becomes overlay drawer (280px width)
   - Semi-transparent backdrop (rgba(0, 0, 0, 0.5))
   - Slide-in animation 300ms ease-in-out
5. Responsive breakpoints: <576px, 576-767px, ≥768px
6. Smooth animations: sidebar 300ms, submenu 250ms, hover 150ms
7. Use flexbox and CSS Grid where appropriate
```

---

## 2. Top Bar Template (Copy to Copilot)

```
Refactor the CodeIgniter admin header template with these requirements:

File: /themes/blue/admin/views/header.php

Create a modern top bar with:
1. Structure:
   - <header class="topbar">
   - Left: Toggle button (☰) + Logo/Brand
   - Center: Search input (optional, hidden on mobile)
   - Right: Notification bell + User profile dropdown + Logout
2. Toggle button:
   - data-toggle="sidebar" attribute
   - Hamburger icon (Font Awesome fa-bars)
   - Visible only on desktop
3. Logo section:
   - Show full text on desktop
   - Show icon only on mobile
4. User profile dropdown:
   - Avatar image (32x32px, rounded)
   - Username (hidden on mobile)
   - Dropdown menu with Profile, Change Password, Logout
5. Integration:
   - Keep all CodeIgniter functions: admin_url(), lang(), site_url()
   - Use session data: $this->session->userdata()
   - Support RTL via $Settings->user_rtl
6. Responsive:
   - Desktop (≥768px): Full layout
   - Tablet: Condensed
   - Mobile: Icon only
7. Accessibility:
   - ARIA labels on buttons
   - Semantic HTML
   - Keyboard navigation support
```

---

## 3. Sidebar Menu Template (Copy to Copilot)

```
Refactor the CodeIgniter admin sidebar menu with these requirements:

File: /themes/blue/admin/views/new_customer_menu.php

Create a modern collapsible sidebar with:
1. Main structure:
   - <aside class="sidebar-wrapper" role="navigation">
   - Nested <ul><li> menu structure (keep existing items)
2. Menu item styling:
   - <li class="sidebar-nav-item">
   - <a class="sidebar-nav-link"> with icon and text
   - Icon: <i class="sidebar-icon fa fa-*">
   - Text: <span class="sidebar-label">
3. Submenu handling:
   - Parent item: class="sidebar-nav-item has-children"
   - Toggle link: class="sidebar-nav-toggle" with data-toggle="submenu"
   - Chevron icon: rotates on expand/collapse
   - Submenu: <ul class="sidebar-submenu"> (nested items)
4. Active state:
   - Add class "active" to current page item
   - Highlight with blue background and text
   - Also add "active" to parent if submenu is expanded
5. Icons:
   - Continue using Font Awesome (fa fa-*)
   - Keep existing icon assignments
6. CodeIgniter integration:
   - Use admin_url() for all href links
   - Use lang() for all text labels
   - Keep all existing menu items from current file
7. Mobile responsive:
   - On < 768px: sidebar is drawer overlay
   - Menu items should work same way as desktop
8. Accessibility:
   - aria-expanded for submenu toggles
   - aria-current="page" for active item
   - Proper heading hierarchy
```

---

## 4. JavaScript Interactivity (Copy to Copilot)

```
Create JavaScript for modern admin dashboard interactions:

File: /assets/js/modern-admin-ui.js

Implement:
1. Sidebar toggle (Desktop only, ≥768px):
   - Toggle between 260px and 60px width
   - Store state in localStorage as 'sidebarCollapsed'
   - Restore state on page load
   - Smooth CSS transition
2. Submenu expand/collapse:
   - Click on .sidebar-nav-toggle to toggle submenu
   - Rotate chevron icon 180°
   - Slide submenu open/closed (max-height animation)
   - Prevent default link behavior
3. Mobile drawer (< 768px):
   - Click toggle button to show drawer
   - Click backdrop to hide drawer
   - Click menu item to close drawer
   - Handle window resize from desktop to mobile and vice versa
4. Active menu item detection:
   - Get current URL
   - Match against menu item hrefs
   - Add 'active' class to matching item and all parents
   - Update on page navigation if using AJAX
5. Dropdown menus:
   - User profile dropdown in top bar
   - Click to toggle open/closed
   - Close on outside click
6. Framework:
   - Use jQuery (already loaded: jQuery 2.0.3)
   - Use document.ready() for initialization
   - Add event listeners for click, resize, etc.
7. Performance:
   - Debounce resize events
   - Minimize DOM reflows
   - Use data attributes for state management
```

---

## 5. CodeIgniter Controller Integration (Copy to Copilot)

```
Update CodeIgniter admin controller to detect active menu:

Requirements:
1. In your admin base controller or main controller:
   - Get current URL segments using $this->router->fetch_class() and $this->router->fetch_method()
   - Create array mapping of controller/method to menu item IDs
   - Set $data['current_page'] = detected_menu_id
   - Pass to all admin views

2. Example mapping:
   $menu_mapping = array(
       'products/index' => 'products_index',
       'products/add' => 'products_add',
       'reports/stock' => 'reports_stocks',
       'dashboard/index' => 'mm_welcome',
       // ... more mappings
   )

3. In the view (new_customer_menu.php):
   - Check if menu item ID matches $current_page
   - Add class="active" to matching <li>
   - Also add "active" to parent <li> if it's a submenu item

4. Alternative approach:
   - Use current URL path matching in JavaScript
   - Match against href attributes of menu items
   - Add 'active' class dynamically

5. Ensure backward compatibility:
   - Don't break existing permission/role system
   - Keep all current variables and functions
   - Support RTL if currently enabled
```

---

## 6. Minimal MVP Implementation (Copy to Copilot)

```
Create a minimal viable product for modern admin UI:

Step 1 - CSS (most important):
- Create /assets/styles/modern-admin-ui.css with:
  - CSS variables for colors
  - .topbar styles (60px, white, shadow)
  - .sidebar-wrapper styles (260px/60px toggle, white, border)
  - .sidebar-nav-item, .sidebar-nav-link styles
  - .sidebar-nav-item.active styles (blue highlight)
  - Transitions and hover effects
  - Mobile breakpoints

Step 2 - HTML updates (minimal changes):
- Update header.php: wrap content in <header class="topbar">
- Update new_customer_menu.php: wrap in <aside class="sidebar-wrapper">
- Add class names to existing elements (sidebar-nav-item, sidebar-nav-link, etc.)
- Keep all content the same, just change structure slightly

Step 3 - JavaScript (basic interactivity):
- Create /assets/js/modern-admin-ui.js
- Add toggle button click handler
- Toggle 'collapsed' class on sidebar
- Toggle CSS width via class
- Save state to localStorage

Result: Modern looking UI with collapsible sidebar and minimal code changes.
```

---

## 7. Copy-Paste Ready: Modern Admin UI CSS

```css
:root {
	/* Colors */
	--color-primary: #3b82f6;
	--color-primary-light: #eff6ff;
	--color-white: #ffffff;
	--color-gray-50: #f9fafb;
	--color-gray-100: #f3f4f6;
	--color-gray-200: #e5e7eb;
	--color-gray-600: #4b5563;
	--color-gray-700: #374151;
	--color-gray-900: #1f2937;

	/* Spacing */
	--space-2: 8px;
	--space-4: 16px;
	--space-6: 24px;

	/* Shadows */
	--shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
	--shadow-lg: 0 10px 15px rgba(0, 0, 0, 0.1);

	/* Border Radius */
	--radius: 6px;
}

/* Top Bar */
.topbar {
	height: 60px;
	background: var(--color-white);
	border-bottom: 1px solid var(--color-gray-200);
	box-shadow: var(--shadow);
	position: sticky;
	top: 0;
	z-index: 100;
	display: flex;
	align-items: center;
	padding: 0 var(--space-4);
}

.topbar-toggle {
	background: none;
	border: none;
	font-size: 20px;
	cursor: pointer;
	color: var(--color-gray-600);
	padding: 8px;
	transition: all 150ms ease;
}

.topbar-toggle:hover {
	background: var(--color-gray-100);
	border-radius: var(--radius);
	color: var(--color-gray-900);
}

/* Sidebar */
.sidebar-wrapper {
	width: 260px;
	background: var(--color-white);
	border-right: 1px solid var(--color-gray-200);
	padding: var(--space-4) 0;
	overflow-y: auto;
	transition: width 300ms ease-in-out;
	z-index: 90;
	position: fixed;
	left: 0;
	top: 60px;
	bottom: 0;
}

.sidebar-wrapper.collapsed {
	width: 60px;
}

.sidebar-nav-item {
	list-style: none;
	padding: 4px var(--space-2);
	margin: 0;
}

.sidebar-nav-link,
.sidebar-nav-toggle {
	display: flex;
	align-items: center;
	gap: var(--space-2);
	padding: 12px var(--space-4);
	color: var(--color-gray-700);
	text-decoration: none;
	border-radius: var(--radius);
	cursor: pointer;
	border: none;
	background: none;
	width: 100%;
	transition: all 150ms ease;
	font-size: 14px;
}

.sidebar-nav-link:hover,
.sidebar-nav-toggle:hover {
	background: var(--color-gray-100);
}

.sidebar-nav-item.active > .sidebar-nav-link {
	background: var(--color-primary-light);
	color: var(--color-primary);
	border-left: 3px solid var(--color-primary);
}

.sidebar-icon {
	width: 24px;
	height: 24px;
	font-size: 18px;
	flex-shrink: 0;
}

.sidebar-label {
	white-space: nowrap;
	overflow: hidden;
}

.sidebar-chevron {
	margin-left: auto;
	transition: transform 200ms ease-in-out;
}

.sidebar-nav-toggle[aria-expanded="true"] .sidebar-chevron {
	transform: rotate(180deg);
}

.sidebar-submenu {
	list-style: none;
	padding-left: 0;
	margin: 0;
	max-height: 0;
	overflow: hidden;
	transition: max-height 250ms ease;
}

.sidebar-nav-toggle[aria-expanded="true"] ~ .sidebar-submenu {
	max-height: 1000px;
}

/* Mobile */
@media (max-width: 767px) {
	.sidebar-wrapper {
		position: fixed;
		left: -280px;
		width: 280px;
		height: 100vh;
		top: 0;
		z-index: 110;
		transition: left 300ms ease-in-out;
		padding-top: 70px;
	}

	.sidebar-wrapper.show {
		left: 0;
	}

	.sidebar-backdrop {
		display: none;
		position: fixed;
		top: 0;
		left: 0;
		right: 0;
		bottom: 0;
		background: rgba(0, 0, 0, 0.5);
		z-index: 100;
	}

	.sidebar-backdrop.show {
		display: block;
	}
}
```

---

## 8. Quick Start: Paste This into Copilot

```
I need to modernize the admin UI for my CodeIgniter ERP. The current interface uses Bootstrap with a traditional navbar and sidebar. I want to transform it into a modern, collapsible design inspired by TailAdmin.

Current files:
- /themes/blue/admin/views/header.php (Bootstrap navbar)
- /themes/blue/admin/views/new_customer_menu.php (Bootstrap sidebar menu)
- /assets/styles/ (current CSS)

Requirements:
1. Modern collapsible sidebar:
   - Expanded: 260px with text labels and icons
   - Collapsed: 60px icon-only
   - Light blue highlight for active items
   - Smooth animations

2. Clean top bar:
   - 60px height, white background
   - Logo, toggle button, search (optional), user profile, notifications
   - Sticky position

3. Light theme:
   - Colors: #3B82F6 (primary), #FFFFFF (bg), #1F2937 (text)
   - Soft shadows, 6-8px border radius
   - Smooth 150-300ms transitions

4. Responsive:
   - Desktop: Full layout with collapsible sidebar
   - Tablet: Drawer overlay
   - Mobile: Full-screen drawer

5. Mobile drawer:
   - Slides in from left
   - Semi-transparent backdrop
   - Closes on item click or backdrop click

6. Maintain CodeIgniter compatibility:
   - Keep all admin_url(), lang(), session functions
   - Support multi-language via lang()
   - Preserve RTL support if enabled

Create a complete implementation including:
- CSS file: /assets/styles/modern-admin-ui.css
- Updated header template
- Updated sidebar menu template
- JavaScript for interactivity
- Integration guide for CodeIgniter controller

Use Bootstrap 3 as base if needed. Don't break existing functionality.
```

---

## 9. Testing Checklist (Copy to Manual QA)

```
[ ] Sidebar toggles between 260px and 60px on desktop
[ ] Sidebar state persists after page reload (localStorage)
[ ] Submenu items expand/collapse with smooth animation
[ ] Chevron icon rotates when submenu expands
[ ] Active menu item highlighted with blue background
[ ] Top bar remains visible and sticky when scrolling
[ ] User profile dropdown works correctly
[ ] Logout button functions
[ ] Mobile drawer opens/closes on toggle
[ ] Mobile drawer closes on menu item click
[ ] Mobile drawer closes on backdrop click
[ ] All links navigate correctly
[ ] No console errors or warnings
[ ] Responsive on 320px, 768px, 1200px widths
[ ] Keyboard navigation works (Tab, Enter)
[ ] Animations smooth (no jank)
[ ] Colors match specification (blues, grays, whites)
[ ] Shadows subtle and professional
[ ] RTL support maintained (if applicable)
```

---

## 10. File Checklist for Implementation

Required new files to create:

- [ ] `/assets/styles/modern-admin-ui.css` - Main stylesheet
- [ ] `/assets/js/modern-admin-ui.js` - JavaScript interactivity
- [ ] `/DESIGN_REFERENCE_GUIDE.md` - Design specifications (created)
- [ ] `/COPILOT_PROMPT_MODERN_UI.md` - Detailed prompt (created)

Files to update:

- [ ] `/themes/blue/admin/views/header.php` - Refactor top bar
- [ ] `/themes/blue/admin/views/new_customer_menu.php` - Refactor sidebar
- [ ] Admin base controller - Add page detection logic

Optional enhancements:

- [ ] Breadcrumb navigation in top bar
- [ ] Search functionality
- [ ] Notification system
- [ ] Theme toggle (dark mode later)

---

## 11. Troubleshooting Guide

**Issue**: Sidebar doesn't collapse on toggle button click

- Solution: Check if modern-admin-ui.js is loaded, check console for errors

**Issue**: Menu items not highlighting as active

- Solution: Verify current_page is being passed from controller, check URL matching logic

**Issue**: Mobile drawer doesn't appear

- Solution: Check if screen width < 768px, verify backdrop element exists

**Issue**: Animations feel slow or janky

- Solution: Use transform and opacity instead of width/height, check for layout thrashing in JS

**Issue**: Submenu chevron not rotating

- Solution: Verify aria-expanded attribute is being set/toggled, check CSS transition on chevron
