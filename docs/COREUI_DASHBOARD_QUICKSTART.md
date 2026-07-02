# CoreUI Dashboard - Quick Start Guide

## 🚀 Quick Implementation (5 minutes)

### Step 1: Choose Your Theme

You have **two new dashboard files** to use:

```
themes/default/admin/views/dashboard_coreui.php
themes/blue/admin/views/dashboard_coreui.php
```

### Step 2: Update Your Dashboard Controller

Find your dashboard controller (likely in `app/controllers`) and update the view reference:

**Before:**

```php
public function index() {
    $data = array(
        'title' => lang('dashboard'),
        // ... other data
    );
    $this->load->view('dashboard', $data);
}
```

**After:**

```php
public function index() {
    $data = array(
        'title' => lang('dashboard'),
        'total_users' => $this->User_model->count_all(),
        'total_sales' => $this->Sales_model->get_total_sales(),
        'total_orders' => $this->Sales_model->count_all(),
        // ... other data
    );
    $this->load->view('dashboard_coreui', $data);  // ← Changed
}
```

### Step 3: Optional - Link External CSS

For better code organization, you can link the external CSS file instead of using inline styles:

In your dashboard file, add this to the `<head>`:

```html
<link
	rel="stylesheet"
	href="<?= base_url('assets/css/dashboard-coreui.css'); ?>"
/>
```

Then remove the `<style>` tag from the HTML file.

### Step 4: Test It!

Navigate to your dashboard URL and you should see the new CoreUI design with:

- ✅ Modern stat cards with metrics
- ✅ Responsive grid layout
- ✅ Color-coded badges
- ✅ Activity feed
- ✅ Performance metrics
- ✅ Tables with hover effects

---

## 📁 Files Created

### View Files

| File                   | Theme   | Path                          |
| ---------------------- | ------- | ----------------------------- |
| `dashboard_coreui.php` | Default | `themes/default/admin/views/` |
| `dashboard_coreui.php` | Blue    | `themes/blue/admin/views/`    |

### Assets

| File                   | Type       | Path          |
| ---------------------- | ---------- | ------------- |
| `dashboard-coreui.css` | Stylesheet | `assets/css/` |

### Documentation

| File                             | Description                  |
| -------------------------------- | ---------------------------- |
| `COREUI_DASHBOARD_GUIDE.md`      | Complete design system guide |
| `COREUI_DASHBOARD_QUICKSTART.md` | This file                    |

---

## 🎨 Design Features

### 4 Modern Stat Cards

- **Total Users** - with trend indicator
- **Total Revenue** - with trend indicator
- **Total Orders** - with trend indicator
- **Conversion Rate** - with trend indicator

### Color System

- 🔵 **Primary (Blue)** - Main actions
- 🟢 **Success (Green)** - Positive metrics
- 🔴 **Danger (Red)** - Alerts
- 🟡 **Warning (Yellow)** - Warnings
- 🔷 **Info (Cyan)** - Information

### Responsive Sections

- 📊 Sales Overview Chart
- 🚦 Traffic Sources Chart
- 📋 Recent Orders Table
- 🔔 Activity Feed
- 📈 Performance Metrics

---

## 🔧 Customization

### Change Primary Color

Edit the `<style>` section (or CSS file) and update:

```css
:root {
	--primary: #0d6efd; /* Change this to your color */
}
```

Examples:

- Green: `#10b981`
- Purple: `#8b5cf6`
- Orange: `#f97316`
- Pink: `#ec4899`

### Change the Number of Stat Cards

**For 3 Cards (instead of 4):**

Change grid in CSS:

```css
.stats-grid {
	grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
}
```

**For 2 Cards:**

```css
.stats-grid {
	grid-template-columns: repeat(auto-fit, minmax(500px, 1fr));
}
```

### Add New Metric Card

Copy this template and paste it after any existing stat card:

```html
<div class="card">
	<div class="stat-card">
		<div class="stat-content">
			<div class="stat-label">Your Metric Label</div>
			<div class="stat-value">
				<?= isset($your_metric) ? number_format($your_metric) : '0'; ?>
			</div>
			<div class="stat-change positive">
				<span class="arrow-icon">↑</span>
				<span>12.5% from last period</span>
			</div>
		</div>
		<div class="stat-icon primary">
			<i class="fa fa-your-icon-name"></i>
		</div>
	</div>
</div>
```

### Modify Icon Color

Change the `stat-icon` class to one of:

- `stat-icon primary` (blue)
- `stat-icon success` (green)
- `stat-icon danger` (red)
- `stat-icon warning` (yellow)
- `stat-icon info` (cyan)

### Change Icon

Replace `fa-your-icon-name` with any Font Awesome icon:

- Users: `fa-users`
- Dollar: `fa-dollar`
- Shopping Cart: `fa-shopping-cart`
- Percent: `fa-percent`
- Chart: `fa-chart-bar`
- Check: `fa-check-circle`

---

## 📱 Responsive Behavior

The dashboard automatically adapts to screen size:

### Desktop (>1024px)

```
[Card 1] [Card 2] [Card 3] [Card 4]
[Chart 1]         [Chart 2]
[Table 1]         [Activity]
```

### Tablet (768px - 1024px)

```
[Card 1] [Card 2]
[Card 3] [Card 4]
[Chart 1]
[Chart 2]
[Table 1]
[Activity]
```

### Mobile (<768px)

```
[Card 1]
[Card 2]
[Card 3]
[Card 4]
[Chart 1]
[Chart 2]
[Table 1]
[Activity]
```

---

## 📊 Adding Chart Data

### Using Chart.js (Recommended)

1. **Add Chart.js library** to your view:

```html
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
```

2. **Add chart initialization script** before closing `</body>`:

```html
<script>
	// Sales Chart
	const salesCtx = document.getElementById("sales-chart").getContext("2d");
	const salesChart = new Chart(salesCtx, {
		type: "line",
		data: {
			labels: ["Jan", "Feb", "Mar", "Apr", "May", "Jun"],
			datasets: [
				{
					label: "Sales",
					data: [12000, 15000, 13000, 18000, 16000, 19000],
					borderColor: "#0d6efd",
					backgroundColor: "rgba(13, 110, 253, 0.1)",
					tension: 0.4,
				},
			],
		},
		options: {
			responsive: true,
			maintainAspectRatio: true,
			plugins: {
				legend: {
					display: false,
				},
			},
		},
	});
</script>
```

### Using Recharts (React)

If using React, replace the chart placeholder divs with Recharts components:

```jsx
import { LineChart, Line, XAxis, YAxis } from "recharts";

const data = [
	{ month: "Jan", sales: 12000 },
	{ month: "Feb", sales: 15000 },
	// ...
];

<LineChart width={500} height={300} data={data}>
	<XAxis dataKey='month' />
	<YAxis />
	<Line type='monotone' dataKey='sales' stroke='#0d6efd' />
</LineChart>;
```

---

## 🎯 Best Practices

### 1. **Keep Data Fresh**

Update your controller to fetch latest data:

```php
$data['total_users'] = $this->db->query("SELECT COUNT(*) as count FROM users")->row()->count;
```

### 2. **Use Consistent Icons**

Stick to one icon library (Font Awesome is recommended):

```html
<i class="fa fa-icon-name"></i>
```

### 3. **Color Meaning**

- 🟢 Green = Good, Success, Positive
- 🔵 Blue = Primary, Information
- 🟡 Yellow = Warning, Caution
- 🔴 Red = Danger, Error

### 4. **Responsive Testing**

Test on:

- Desktop (1920px, 1440px, 1024px)
- Tablet (768px, 834px)
- Mobile (375px, 480px)

### 5. **Performance**

- Minimize database queries in controller
- Cache static data (trends, benchmarks)
- Load charts asynchronously

---

## 🐛 Troubleshooting

### Charts Not Showing?

- Verify Chart.js is loaded
- Check browser console for errors
- Ensure data is being passed correctly

### Cards Look Misaligned?

- Clear browser cache (Cmd+Shift+R)
- Check viewport meta tag
- Ensure CSS is not overridden

### Icons Not Displaying?

- Verify Font Awesome is included in your theme
- Check icon class names are correct (e.g., `fa fa-users`)
- Look for CSS conflicts

### Data Not Updating?

- Verify controller is passing variables
- Check PHP variable names match template names
- Test directly: `<?= var_dump($total_users); ?>`

---

## 🔗 Resources

- **CoreUI Docs**: https://coreui.io/react/docs/
- **Font Awesome Icons**: https://fontawesome.com/icons
- **Bootstrap Colors**: https://getbootstrap.com/docs/5.0/utilities/colors/
- **CSS Grid Guide**: https://developer.mozilla.org/en-US/docs/Web/CSS/CSS_Grid_Layout

---

## 📝 Example: Complete Implementation

Here's a complete example of updating your dashboard:

**Controller (`app/controllers/Dashboard.php`):**

```php
<?php
class Dashboard extends Admin_Controller {
    public function __construct() {
        parent::__construct();
        $this->load->model('User_model');
        $this->load->model('Sales_model');
    }

    public function index() {
        // Fetch dashboard data
        $data['title'] = lang('dashboard');
        $data['total_users'] = $this->User_model->count_users();
        $data['total_sales'] = $this->Sales_model->get_total_revenue();
        $data['total_orders'] = $this->Sales_model->count_orders();
        $data['recent_orders'] = $this->Sales_model->get_recent_orders(5);
        $data['activity'] = $this->Activity_model->get_recent_activity(10);

        // Load the new dashboard view
        $this->load->view('dashboard_coreui', $data);
    }
}
```

**View (`themes/default/admin/views/dashboard_coreui.php`):**

```php
<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<!DOCTYPE html>
<html>
<head>
    <title><?= $title ?></title>
    <link rel="stylesheet" href="<?= base_url('assets/css/dashboard-coreui.css'); ?>">
</head>
<body>
    <div class="dashboard-container">
        <div class="dashboard-header">
            <h1><?= $title ?></h1>
            <p>Welcome back!</p>
        </div>

        <div class="stats-grid">
            <div class="card">
                <div class="stat-card">
                    <div class="stat-content">
                        <div class="stat-label">Total Users</div>
                        <div class="stat-value"><?= number_format($total_users) ?></div>
                    </div>
                    <div class="stat-icon primary">
                        <i class="fa fa-users"></i>
                    </div>
                </div>
            </div>
            <!-- More cards... -->
        </div>
    </div>
</body>
</html>
```

---

## ✅ Implementation Checklist

- [ ] Choose theme (default or blue)
- [ ] Copy new dashboard file to your theme
- [ ] Update controller to load new view
- [ ] Update data variables in controller
- [ ] Test responsive design
- [ ] Customize colors (if desired)
- [ ] Add chart library (Chart.js or Recharts)
- [ ] Integrate real data
- [ ] Test on mobile devices
- [ ] Verify performance
- [ ] Deploy to production

---

## 📞 Support

For issues or customizations:

1. Check the `COREUI_DASHBOARD_GUIDE.md` file
2. Review CSS variables in `assets/css/dashboard-coreui.css`
3. Inspect browser console for errors
4. Verify all dependencies are included

---

**Version**: 1.0  
**Last Updated**: October 2025  
**Status**: ✅ Ready for Production
