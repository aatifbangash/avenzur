# 🧪 WASFATY MODAL - QUICK TEST GUIDE

## ⚡ Quick Test (5 Minutes)

### 1️⃣ Open POS

```
Navigate to: http://your-domain.com/admin/pos
```

### 2️⃣ Click Wasfaty Button

```
Location: Top right of POS header
Icon: Blue button with heartbeat icon
```

### 3️⃣ Verify Initial State

```
✓ Modal opens with two columns
✓ Left: Search form with phone + code inputs
✓ Right: Empty state (document icon + message)
✓ Footer: Only "Cancel" button visible
```

### 4️⃣ Enter Test Data

```
Phone:  0554712260
Code:   190583
Click:  Fetch Prescription
```

### 5️⃣ Verify Loading State

```
✓ Right column shows spinner
✓ Message: "Fetching prescription from Wasfaty..."
✓ Fetch button disabled
✓ Wait ~1 second
```

### 6️⃣ Verify Prescription Details

```
✓ Right column shows prescription info
✓ Phone: 0554712260
✓ Code: 190583
✓ Customer Type: GOLD badge
✓ Discount: 🏷️ 15% discount
✓ Table shows 2 medications:
  - EXYLIN 100ML (Total: 15)
  - Panadol Cold Flu (Total: 15)
✓ "Add to Cart" button appears (green)
```

### 7️⃣ Click Add to Cart

```
⚠️ REQUIRES DATABASE MIGRATION FIRST
After migration:
✓ Button shows "Processing..."
✓ Success alert appears
✓ Items added to POS cart
✓ 15% discount applied
✓ Modal closes after 3 seconds
```

---

## 🐛 Common Issues & Solutions

### Issue 1: Modal Not Opening

```
Problem: Clicking Wasfaty button does nothing
Solution: Check browser console for JavaScript errors
         Verify jQuery and Bootstrap loaded
         Check: site.base_url is defined
```

### Issue 2: Empty Column on Right

```
Problem: Right column completely blank
Solution: Check CSS file loaded: /assets/css/wasfaty.css
         Verify #prescription-empty-state element exists
         Check browser console for CSS errors
```

### Issue 3: Fetch Button Does Nothing

```
Problem: Clicking Fetch has no effect
Solution: Check browser console for AJAX errors
         Verify endpoint: /admin/wasfaty/fetch_prescription
         Check PHP error logs
         Verify Wasfaty.php controller loaded
```

### Issue 4: "Failed to Fetch Prescription"

```
Problem: Error message after clicking Fetch
Causes:  - Database not migrated yet (most likely)
         - Phone/code combination not in database
         - Database connection issue

Solution: Run migration: mysql < wasfaty_migration.sql
         Check database tables exist
         Verify test data inserted
```

### Issue 5: Two Columns Stack on Desktop

```
Problem: Columns appear vertically instead of side-by-side
Solution: Check modal has class "modal-lg"
         Verify Bootstrap grid classes: col-md-5, col-md-7
         Check browser width > 992px
         Clear browser cache
```

---

## 📱 Responsive Testing

### Desktop (> 1200px)

```
✓ Two columns side-by-side
✓ Left: 40% width
✓ Right: 60% width
✓ Modal width: 95% of screen (max 1200px)
```

### Tablet (768px - 992px)

```
✓ Two columns side-by-side (narrower)
✓ Modal width: 90% of screen
✓ Fonts slightly smaller
```

### Mobile (< 768px)

```
✓ Columns stack vertically
✓ Left column (form) on top
✓ Right column (details) below
✓ Modal width: 95% of screen
✓ Larger touch targets (48px min)
```

---

## 🎨 Visual Checks

### Colors

```
✓ Header: Blue gradient (#2196F3 → #1976D2)
✓ Left column: Border-right (#E0E0E0)
✓ GOLD badge: Yellow/Gold (#F59E0B)
✓ Discount text: Green (#10B981)
✓ Primary button: Blue (#2196F3)
✓ Error messages: Red (#EF4444)
```

### Typography

```
✓ Headers: Bold, 16px
✓ Labels: Semi-bold, 14px
✓ Input text: 16px (large)
✓ Help text: 13px, gray
✓ Table text: 14px
```

### Spacing

```
✓ Left column padding-right: 20px
✓ Right column padding-left: 20px
✓ Empty state padding: 60px vertical
✓ Form groups margin-bottom: 15px
```

### Icons

```
✓ Modal header: fa-heartbeat
✓ Left column header: fa-search
✓ Right column header: fa-file-text-o
✓ Medications header: fa-pills
✓ Empty state: fa-file-text-o (80px, gray)
✓ Loading spinner: fa-spinner fa-spin (4x)
```

---

## 🔧 Developer Tools Checks

### Console (F12)

```
Should NOT see:
✗ jQuery is not defined
✗ $ is not defined
✗ site is not defined
✗ 404 errors for CSS/JS files

Should see:
✓ "Wasfaty module initialized" (or similar)
✓ No errors on page load
✓ AJAX request logged on Fetch click
```

### Network Tab

```
On Fetch click, should see:
✓ POST request to: /admin/wasfaty/fetch_prescription
✓ Status: 200 OK
✓ Response: JSON with success: true
✓ Response time: ~1000ms (1 second delay)
```

### Elements Tab

```
Inspect modal:
✓ <div id="wasfatyModal" class="modal fade">
  ✓ <div class="modal-dialog modal-lg">
    ✓ <div class="modal-body">
      ✓ <div class="row">
        ✓ <div class="col-md-5" id="wasfaty-left-column">
        ✓ <div class="col-md-7" id="wasfaty-right-column">
```

---

## 📊 State Transitions

### Transition 1: Empty → Loading

```
Trigger: Click "Fetch Prescription"
Duration: 200ms
Effect:
  - #prescription-empty-state fadeOut
  - #wasfaty-loading fadeIn
  - #fetch-prescription-btn disabled
```

### Transition 2: Loading → Details

```
Trigger: AJAX success response
Duration: 400ms
Effect:
  - #wasfaty-loading fadeOut (200ms)
  - #prescription-details fadeIn (400ms)
  - #convert-to-order-btn fadeIn (400ms)
```

### Transition 3: Details → Reset

```
Trigger: Modal closes or Cancel clicked
Duration: 200ms
Effect:
  - #prescription-details hide
  - #prescription-empty-state fadeIn
  - #convert-to-order-btn hide
  - Form reset
```

---

## 🗃️ Database Check (After Migration)

### Verify Tables Created

```sql
SHOW TABLES LIKE 'wasfaty%';

Expected output:
+----------------------------------+
| Tables_in_db (wasfaty%)         |
+----------------------------------+
| wasfaty_prescriptions            |
| wasfaty_prescription_items       |
+----------------------------------+
```

### Verify Test Data

```sql
SELECT * FROM wasfaty_prescriptions
WHERE prescription_code = '190583';

Expected: 1 row with:
  - patient_phone: 0554712260
  - customer_type: GOLD
  - status: PENDING
```

### Verify Items

```sql
SELECT * FROM wasfaty_prescription_items
WHERE prescription_id = 1;

Expected: 2 rows (EXYLIN, Panadol)
```

---

## ✅ Success Criteria

### Frontend

- [x] Modal opens with two-column layout
- [x] Empty state visible initially
- [x] Form inputs accept phone and code
- [x] Loading spinner shows on Fetch
- [x] Prescription details appear in right column
- [x] Customer type badge displays correctly
- [x] Medications table shows with data
- [x] Total quantities calculated correctly
- [x] "Add to Cart" button appears only when loaded
- [x] Responsive on mobile (columns stack)

### Backend (Requires DB Migration)

- [ ] AJAX endpoint returns prescription data
- [ ] Phone validation (05XXXXXXXX)
- [ ] Code validation (6 digits)
- [ ] Customer type mapped correctly
- [ ] Discount percentage calculated (15% GOLD)
- [ ] Batch selection (FEFO logic)
- [ ] Items added to cart
- [ ] Discount applied to cart
- [ ] Prescription status updated to DISPENSED

---

## 🚀 Next Steps

1. **Execute Migration**

   ```bash
   mysql -h host.docker.internal -u admin -p rawabi_jeddah < wasfaty_migration.sql
   ```

2. **Find Product IDs**

   ```
   Run: test_wasfaty_products.php
   Update: Product IDs in migration SQL
   Re-insert: Test data with correct IDs
   ```

3. **Full Test**

   ```
   Open POS → Wasfaty → Fetch → Add to Cart → Complete Sale
   ```

4. **Verify Order**
   ```
   Check: Order saved with source = "WASFATY"
   Check: Prescription code stored
   Check: Customer type stored
   Check: 15% discount applied
   Check: Prescription status = "DISPENSED"
   ```

---

## 📞 Support

**Files:**

- Documentation: `/WASFATY_TWO_COLUMN_MODAL_COMPLETE.md`
- Visual Guide: `/WASFATY_MODAL_VISUAL_GUIDE.txt`
- Implementation: `/WASFATY_IMPLEMENTATION_GUIDE.md`
- Migration: `/wasfaty_migration.sql`

**Test Credentials:**

- Phone: `0554712260`
- Code: `190583`
- Type: `GOLD (15%)`

---

**Last Updated:** 2025  
**Status:** ✅ UI Complete | ⏳ Database Pending  
**Test Time:** ~5 minutes (after DB migration)
