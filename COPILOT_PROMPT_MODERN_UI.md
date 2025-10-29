# GitHub Copilot Prompt: Modern Collapsible Admin UI for CodeIgniter ERP

## Context

You are working on a CodeIgniter ERP Admin Portal (`/themes/blue/admin/views/`). The current UI uses Bootstrap with traditional flat design. You need to transform it into a modern, responsive admin interface inspired by **TailAdmin** (https://demo.tailadmin.com/) with:

- A **collapsible sidebar** (expanded/minimized states)
- A **clean top navigation bar** with user profile & notifications
- **Light theme** with soft colors, subtle shadows, and rounded corners
- **Smooth animations** and transitions
- **Mobile responsive** design

## Current Structure

- **Header**: `/themes/blue/admin/views/header.php` (Bootstrap navbar with user dropdown)
- **Sidebar Menu**: `/themes/blue/admin/views/new_customer_menu.php` (Bootstrap nav with dropdown submenus)
- **CSS Files**: `/assets/styles/theme.css`, `/assets/styles/style.css`
- **JS**: Using jQuery (jQuery 2.0.3)

## Design Requirements

### 1. **Sidebar Specifications**

- **Default State**: Expanded (260px width) on desktop
- **Minimized State**: Collapsed to 60px (icon-only) on desktop
- **Mobile**: Drawer-style overlay on screens < 768px
- **Toggle Button**: In the top-left corner of top bar
- **Styling**:
  - Background: Clean white (#FFFFFF) with subtle border (1px #E5E7EB)
  - Text: Dark gray (#1F2937)
  - Icons: 24px, light gray (#6B7280)
  - Active item: Light blue background (#EFF6FF) with blue text (#3B82F6)
  - Hover effect: Light gray background (#F3F4F6) with smooth transition
  - Submenu indicator: Chevron icon that rotates on expand/collapse
  - Nested submenus: Indented with toggle functionality

### 2. **Top Navigation Bar Specifications**

- **Height**: 60px
- **Background**: White with subtle shadow (0 1px 3px rgba(0,0,0,0.1))
- **Position**: Sticky/Fixed at top
- **Layout**:
  - Left: Logo/Brand name (hidden when sidebar is minimized, shows icon)
  - Center: Breadcrumb navigation (optional, show current page path)
  - Right: Search bar, notifications, user profile dropdown, logout

### 3. **Color Scheme (Light Theme)**

- **Primary**: #3B82F6 (Blue - for active states, buttons)
- **Background**: #FFFFFF (White for main areas)
- **Secondary Background**: #F9FAFB (Light gray for sections)
- **Text Primary**: #1F2937 (Dark gray)
- **Text Secondary**: #6B7280 (Medium gray)
- **Borders**: #E5E7EB (Light border gray)
- **Shadows**: Subtle (0 1px 2px rgba(0,0,0,0.05), 0 1px 3px rgba(0,0,0,0.1))

### 4. **Animation & Transitions**

- **Sidebar Toggle**: 300ms ease-in-out
- **Submenu Expand**: 250ms ease
- **Hover Effects**: 150ms ease
- **Icon Rotation**: 200ms ease-in-out

### 5. **Mobile Responsiveness**

- **Desktop (≥768px)**: Full sidebar visible or collapsible to icon-only
- **Tablet (≥576px, <768px)**: Drawer overlay sidebar with semi-transparent backdrop
- **Mobile (<576px)**: Full-width drawer overlay

---

## Implementation Tasks

### Phase 1: Create Modern CSS Framework

**Prompt for Copilot:**

```
You are creating modern admin dashboard styling for a CodeIgniter ERP using Bootstrap 3 as a base.
Create a new file `/assets/styles/modern-admin-ui.css` with:

1. CSS Variables (for light theme):
   - Primary colors: #3B82F6, #FFFFFF, #F9FAFB, #1F2937, #6B7280, #E5E7EB
   - Spacing: 4px, 8px, 12px, 16px, 24px base units
   - Border radius: 6px, 8px, 12px
   - Shadows: subtle elevation levels

2. Modern Sidebar Styles:
   - .sidebar-wrapper: 260px width (desktop), smooth transitions, white background with border
   - .sidebar-minimized: 60px width with hidden text labels
   - .sidebar-nav-item: proper padding, hover effects, transitions
   - .sidebar-nav-item.active: blue background (#EFF6FF), blue text (#3B82F6)
   - .sidebar-nav-submenu: indented nested items, collapse/expand chevron
   - .sidebar-nav-submenu.show: expanded state with rotated chevron

3. Modern Top Bar Styles:
   - .topbar: 60px height, sticky position, white background, subtle shadow
   - .topbar-brand: responsive logo area
   - .topbar-search: search input with icon, light gray background
   - .topbar-actions: right-aligned icons and dropdown
   - .topbar-profile-dropdown: clean user profile dropdown with avatar

4. Responsive Breakpoints:
   - Desktop (≥768px): Full sidebar with toggle option
   - Mobile (<768px): Drawer overlay with backdrop
   - Smooth transitions for all state changes

5. Light Theme:
   - Use soft colors and subtle shadows
   - Smooth rounded corners (6-8px)
   - Proper contrast ratios for accessibility
```

### Phase 2: Refactor Header Template

**Prompt for Copilot:**

```
You are refactoring the CodeIgniter admin header template for a modern UI.
Update `/themes/blue/admin/views/header.php`:

1. Replace Bootstrap navbar with modern top bar structure:
   - Add toggle button for sidebar (left side, only on desktop)
   - Keep logo/brand name on left (responsive: hide text on mobile)
   - Add search input in center area (optional, can be hidden on mobile)
   - Keep user profile dropdown on right with modern styling
   - Add notification bell icon (if notifications table exists)

2. HTML Structure:
   - Wrap in <div class="topbar"> instead of <header class="navbar">
   - Use semantic HTML with proper ARIA labels for accessibility
   - Ensure all user actions (profile, logout) work with new structure

3. Responsive Design:
   - Top bar should remain visible on all screen sizes
   - Sidebar toggle should only show on desktop (≥768px)
   - Mobile menu icon already exists, but hide sidebar toggle on mobile

4. Keep all CodeIgniter integration:
   - Admin URLs with admin_url()
   - Session data with $this->session->userdata()
   - Language translations with lang()
   - Avatar images from assets/uploads/avatars/thumbs/

5. Add data attributes for JavaScript:
   - data-toggle="sidebar" on toggle button
   - data-action="logout" on logout button
   - data-action="profile" on profile link
```

### Phase 3: Refactor Sidebar/Menu Template

**Prompt for Copilot:**

```
You are refactoring the CodeIgniter admin sidebar menu for a modern collapsible design.
Update `/themes/blue/admin/views/new_customer_menu.php`:

1. Modern Sidebar Structure:
   - Wrap entire menu in <aside class="sidebar-wrapper">
   - Add data attributes for state management: data-collapsed="false"
   - Use nested <ul> structure for hierarchy (already exists)

2. Menu Item Styling:
   - Each main menu item: <li class="sidebar-nav-item"> with proper spacing
   - Menu link: <a class="sidebar-nav-link">
   - Icon: <i class="sidebar-icon"> (Font Awesome icons)
   - Text: <span class="sidebar-label"> (hidden when sidebar minimized)
   - Active state: Add class "active" to current page's menu item

3. Submenu Enhancements:
   - Submenu toggle: <a class="sidebar-nav-toggle"> with chevron icon
   - Chevron icon rotates on expand/collapse (using CSS transform)
   - Submenus slide open/closed with animation
   - Show submenu items when parent is expanded

4. Mobile Drawer:
   - On mobile: sidebar should appear as overlay drawer
   - Add backdrop/overlay element
   - Close on item click or backdrop click

5. CodeIgniter Integration:
   - Detect current page and add "active" class to menu item
   - Use admin_url() for all links
   - Use lang() for all text labels
   - Keep Font Awesome icons (fa fa-*)

6. Accessibility:
   - Add role="navigation" to aside element
   - Add aria-expanded for submenu toggles
   - Add aria-current="page" to active menu item
```

### Phase 4: Create JavaScript for Interactivity

**Prompt for Copilot:**

```
You are creating JavaScript for modern admin dashboard interactions.
Create new file `/assets/js/modern-admin-ui.js` with:

1. Sidebar Toggle Functionality:
   - Toggle sidebar between expanded (260px) and minimized (60px) states
   - Store preference in localStorage as 'sidebarCollapsed'
   - Apply saved state on page load
   - Animate width transition using CSS transitions

2. Submenu Toggle Functionality:
   - Click on submenu parent to toggle children visibility
   - Rotate chevron icon on toggle
   - Prevent default link behavior for toggle items
   - Smooth slide-in/slide-out animation for submenus

3. Mobile Drawer:
   - On screen width <768px: Sidebar becomes drawer
   - Click toggle button to show/hide drawer
   - Click backdrop to close drawer
   - Click menu item to close drawer
   - Handle window resize events

4. Active Menu Item:
   - Detect current URL
   - Find matching menu item based on href
   - Add 'active' class to current item and parents
   - Update on page navigation

5. Search Functionality (optional):
   - Filter sidebar menu items by search term
   - Show/hide matching items
   - Clear search on ESC key

6. jQuery Compatibility:
   - Use jQuery for DOM manipulation (already loaded)
   - Use document.ready for initialization
   - Ensure no conflicts with existing code

7. Data Persistence:
   - Save sidebar state in localStorage
   - Restore on page reload
   - Sync across tabs if needed
```

### Phase 5: Integration with CodeIgniter Controller

**Prompt for Copilot:**

```
You are integrating the modern UI into CodeIgniter admin controllers.
Update controllers to pass required data to views:

1. Required view variables:
   - $current_page: Current page identifier (used for menu highlighting)
   - $page_title: Page title
   - $breadcrumbs: Array of breadcrumb items (optional)
   - $notifications: Count of unread notifications (if exists)
   - $has_submenu: Boolean to show submenu toggle

2. In your main controller's _output() or in admin base controller:
   - Detect current controller/method
   - Set $current_page based on routing
   - Determine which menu item should be active
   - Pass to views via $this->load->view()

3. Example implementation:
   - Get current URL segments
   - Match against menu structure
   - Pass 'active_menu' variable to header.php and menu.php
   - Use in view to set active class on menu items

4. Ensure backward compatibility:
   - Keep all existing variables and functions
   - Don't break current auth/permission system
   - Support RTL languages if currently used ($Settings->user_rtl)
```

---

## File Modifications Summary

| File                                             | Changes                           | Priority |
| ------------------------------------------------ | --------------------------------- | -------- |
| `/assets/styles/modern-admin-ui.css`             | **New** - Modern CSS framework    | High     |
| `/themes/blue/admin/views/header.php`            | Refactor to modern top bar        | High     |
| `/themes/blue/admin/views/new_customer_menu.php` | Refactor to modern sidebar        | High     |
| `/assets/js/modern-admin-ui.js`                  | **New** - JavaScript interactions | High     |
| Admin Base Controller                            | Add current page tracking         | Medium   |
| `/assets/styles/style.css`                       | Optional - Migrate legacy styles  | Low      |

---

## Testing Checklist

- [ ] Sidebar expands/collapses with toggle button
- [ ] Submenu items expand/collapse with chevron rotation
- [ ] Active menu item highlighted correctly
- [ ] Mobile drawer opens/closes
- [ ] Sidebar state persists on page reload
- [ ] All links and buttons work correctly
- [ ] Responsive design on 320px, 768px, 1200px widths
- [ ] No console errors or warnings
- [ ] Accessibility: keyboard navigation works
- [ ] Backward compatibility: existing features still work

---

## Additional Resources

- **TailAdmin Reference**: https://demo.tailadmin.com/
- **Modern Color Palette**: Consider using Tailwind colors or Material Design
- **Icons**: Continue using Font Awesome or consider Feather Icons
- **Animations**: Use CSS transforms and transitions (no external animation library)

---

## Notes for Copilot

1. **Framework**: CodeIgniter (PHP MVC) - views use PHP with helper functions
2. **Current Tech Stack**: Bootstrap 3, jQuery, Font Awesome
3. **Constraints**: Must maintain backward compatibility with existing menu structure
4. **Language**: Multi-language support via lang() helper
5. **RTL Support**: Code currently supports RTL languages - maintain this
6. **Browser Support**: Modern browsers (Chrome, Firefox, Safari, Edge) - ES6+ JavaScript is OK
