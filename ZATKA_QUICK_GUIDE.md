# Zatka Invoice - Quick Access Guide

## 🚀 Quick Links

### Access from Sales View Page

1. Go to: **Sales → View Sale**
2. Click **Actions** dropdown
3. Select:
   - **Zatka Invoice (PDF)** - Download PDF
   - **Zatka Invoice (Preview)** - View in browser

### Direct URLs

**PDF Download:**

```
http://localhost:8080/avenzur/admin/sales/pdf_zatka_invoice/{sale_id}
```

**Browser Preview:**

```
http://localhost:8080/avenzur/admin/sales/pdf_zatka_invoice/{sale_id}/1
```

## 📋 Example Usage

```
Sale ID: 123

PDF: http://localhost:8080/avenzur/admin/sales/pdf_zatka_invoice/123
Preview: http://localhost:8080/avenzur/admin/sales/pdf_zatka_invoice/123/1
```

## ✅ What's Included

- ✅ Zatka-compliant invoice template
- ✅ QR code with invoice metadata
- ✅ Dual language (Arabic + English)
- ✅ Tax calculations (15% VAT)
- ✅ Seller VAT/Tax ID
- ✅ Customer information
- ✅ Line items with discounts
- ✅ Aging report
- ✅ PDF generation
- ✅ Browser preview

## 🎯 Files Created/Modified

**New Files:**

- `/themes/default/admin/views/sales/zatka_invoice.php`

**Modified Files:**

- `/app/models/admin/Sales_model.php` (added 4 methods)
- `/app/controllers/admin/Sales.php` (added pdf_zatka_invoice method)
- `/themes/*/admin/views/sales/view.php` (3 theme files - added menu links)

## 📊 Invoice Structure

1. **Header** - QR code, invoice number, date, company name
2. **Seller Info** - Name, Tax ID, Commercial Reg, Phone
3. **Customer Info** - Name, Tax ID, Commercial Reg, Phone
4. **Items Table** - 11 columns with all details
5. **Aging Report** - Payment terms breakdown
6. **Summary** - Totals, discounts, tax, grand total
7. **Footer** - Signatures and stamps

## 🔧 Key Functions

### Sales_model.php

```php
getZatkaInvoiceData($sale_id)      // Main data fetcher
gregorianToHijri($date)            // Date conversion
numberToArabicWords($number)       // Amount to words
getInvoiceItems($sale_id)          // Fetch sale items
```

### Sales.php Controller

```php
pdf_zatka_invoice($id, $view, $save_buffer)
// $view = 1: Preview in browser
// $view = null: Download PDF
```

## 🎨 Template Structure

Exactly follows `zatka_invoice_pure_tables.html`:

- Pure HTML tables (no flexbox/grid)
- A4 page size (210mm x 297mm)
- Print-optimized CSS
- Arabic text support
- All styling inline for PDF compatibility

## 💡 Testing Tips

1. **Test with real sale data**
2. **Check calculations** - discounts should cascade correctly
3. **Verify Arabic text** - displays properly in PDF
4. **Test QR code** - scan with mobile device
5. **Print test** - ensure A4 format fits properly

## 🐛 Troubleshooting

**PDF not generating?**

- Check PHP memory limit (256MB+)
- Verify mPDF is installed
- Check error logs

**Arabic text not showing?**

- Ensure UTF-8 encoding in database
- Check mPDF font support

**Empty data?**

- Verify sale exists in database
- Check company (seller/customer) data
- Ensure products have required fields

## 📞 Support

For issues:

1. Check `/app/logs/` for errors
2. Verify database tables exist
3. Ensure mPDF library loaded
4. Test with different sale IDs

---

**Quick Start:** Navigate to any sale view page and click "Zatka Invoice (Preview)" to see it in action!
