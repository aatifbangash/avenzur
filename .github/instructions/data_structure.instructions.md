---
applyTo: "**"
---

# Zatka Invoice Template - Data Structure Reference

## Complete Data Array Structure to Pass to CodeIgniter View

```php
$invoice_data = array(

    // ===== INVOICE HEADER =====
    'invoice_number' => '0001',
    'invoice_date' => '2025/01/28 03:48 ص',
    'invoice_date_hijri' => '1446/07/28',
    'qr_code_image' => '<img src="..." />', // QR code HTML/image

    // ===== COMPANY (SELLER) INFORMATION =====
    'company_name' => 'شركة روابي الأعمال للدواء',
    'company_name_en' => 'Rawabi Business Medical CO.',
    'company_name_ar' => 'شركة روابي الأعمال للدواء',

    'seller_company_name' => 'شركة روابي الأعمال للدواء',
    'seller_company_name_en' => 'Rawabi Business Medical CO.',
    'seller_tax_id' => '311392835500003',
    'seller_commercial_reg' => '4030372922',
    'seller_phone' => '0126230079 - 0126230049 - 0126320039',
    'seller_international_id' => '6286517000014',
    'seller_address' => '',
    'seller_address_city' => 'جدة',
    'seller_branch_code' => '00000',
    'seller_branch_name' => '',

    // ===== CUSTOMER INFORMATION =====
    'customer_name' => 'حقنة الجل الطبية - لصيلة',
    'customer_name_en' => 'Medical Gel Injection',
    'customer_tax_id' => '300732535400003',
    'customer_commercial_reg' => '5906037541',
    'customer_phone' => '',
    'customer_international_id' => '',
    'customer_address' => '',
    'customer_address_city' => 'زجان',
    'customer_branch_code' => '2427',
    'customer_branch_name' => '6281',

    // ===== LINE ITEMS ARRAY =====
    'items' => array(
        array(
            'description' => 'نيرفاميني',
            'description_en' => 'Nervamine',
            'item_code' => '00010002',
            'lot_number' => '231106F',
            'expiry_date' => '05/11/2028',
            'quantity' => 100.00,
            'unit_price' => 75.00,
            'total' => 7500.00,
            'discount_1_percent' => 5,
            'discount_1_amount' => 375.00,
            'discount_2_percent' => 2,
            'discount_2_amount' => 150.00,
            'total_discount' => 525.00,
            'net_after_discount' => 6975.00,
            'tax_rate_percent' => 15,
            'tax_amount' => 1046.25,
            'line_total' => 8021.25
        ),
        array(
            'description' => 'نيرفاميني',
            'description_en' => 'Nervamine',
            'item_code' => '00010002',
            'lot_number' => '231106F',
            'expiry_date' => '05/11/2028',
            'quantity' => 25.00,
            'unit_price' => 75.00,
            'total' => 1875.00,
            'discount_1_percent' => 5,
            'discount_1_amount' => 93.75,
            'discount_2_percent' => 2,
            'discount_2_amount' => 37.50,
            'total_discount' => 131.25,
            'net_after_discount' => 1743.75,
            'tax_rate_percent' => 15,
            'tax_amount' => 261.56,
            'line_total' => 2005.31
        ),
        // ... more items following same structure
    ),

    // ===== TOTALS SECTION =====
    'invoice_total_value' => 125818.75,
    'total_discounts' => 11548.91,
    'subtotal_before_tax' => 111168.48,
    'total_tax' => 16675.27,
    'total_after_tax' => 127843.75,

    // ===== AGING REPORT DATA =====
    'aging_less_30' => 125818.75,
    'aging_30_60' => 0.00,
    'aging_60_90' => 0.00,
    'aging_90_120' => 0.00,
    'aging_more_120' => 0.00,

    // ===== NOTES & FOOTER =====
    'invoice_notes' => 'فقط مائة وسبعة وعشرون الف وثمانمائة وثلاثة واربعون ريال سعودي وخمسة وسبعون هللة',
    // Additional notes about exemptions, conditions, etc.
);
```

## CodeIgniter 3 View Usage

```php
<!-- In your view file: application/views/invoice/zatka_invoice_template.php -->

<div class="container">
    <div class="header">
        <!-- Use variables like this: -->
        <div><?php echo $invoice_number; ?></div>
        <div><?php echo $invoice_date; ?></div>
        <div><?php echo $company_name; ?></div>
    </div>

    <!-- For items loop: -->
    <table class="items-table">
        <tbody>
            <?php foreach($items as $item): ?>
                <tr class="item-row">
                    <td class="text-left">
                        <div><?php echo $item['description']; ?></div>
                        <div><?php echo $item['description_en']; ?></div>
                        <small><?php echo $item['item_code']; ?> | Lot: <?php echo $item['lot_number']; ?> | EXP: <?php echo $item['expiry_date']; ?></small>
                    </td>
                    <td><?php echo number_format($item['quantity'], 2); ?></td>
                    <td><?php echo number_format($item['unit_price'], 2); ?></td>
                    <td><?php echo number_format($item['total'], 2); ?></td>
                    <td><?php echo $item['discount_1_percent']; ?>%<br><?php echo number_format($item['discount_1_amount'], 2); ?></td>
                    <td><?php echo $item['discount_2_percent']; ?>%<br><?php echo number_format($item['discount_2_amount'], 2); ?></td>
                    <td><?php echo number_format($item['total_discount'], 2); ?></td>
                    <td><?php echo number_format($item['net_after_discount'], 2); ?></td>
                    <td><?php echo $item['tax_rate_percent']; ?>%</td>
                    <td><?php echo number_format($item['tax_amount'], 2); ?></td>
                    <td><?php echo number_format($item['line_total'], 2); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
```

## Key Data Calculation Rules

### For Each Line Item:

```
1. Total = Quantity × Unit Price
2. Discount 1 Amount = Total × (Discount 1 % / 100)
3. Discount 2 Amount = (Total - Discount 1 Amount) × (Discount 2 % / 100)
4. Total Discount = Discount 1 Amount + Discount 2 Amount
5. Net After Discount = Total - Total Discount
6. Tax Amount = Net After Discount × (Tax Rate % / 100)
7. Line Total = Net After Discount + Tax Amount
```

### For Invoice Summary:

```
1. Total Invoice Value = SUM(Total) for all items
2. Total Discounts = SUM(Total Discount) for all items
3. Subtotal Before Tax = SUM(Net After Discount) for all items
4. Total Tax = SUM(Tax Amount) for all items
5. Total After Tax = Subtotal Before Tax + Total Tax
```

## Numeric Formatting Rules

- **Currency**: Always 2 decimal places
- **Percentages**: Show as whole numbers (5, 15, etc.)
- **Quantities**: 2 decimal places
- **Currency Symbol**: None in calculations, format as SAR if needed in display

## Required Format Validations

1. **Tax ID Format**: 15 digits (Saudi format)
2. **Commercial Registration**: Numeric
3. **Phone Numbers**: Include country code (+966) or local format
4. **Date Format**: DD/MM/YYYY HH:MM
5. **Amounts**: Must be positive, 2 decimal places

## Sample CodeIgniter Controller Method

```php
public function get_invoice_data($invoice_id) {
    $this->load->model('Invoice_model');
    $this->load->helper('invoice');

    // Fetch invoice header
    $invoice = $this->Invoice_model->get_invoice($invoice_id);

    // Fetch items
    $items = $this->Invoice_model->get_invoice_items($invoice_id);

    // Calculate totals
    $totals = $this->Invoice_model->calculate_totals($invoice_id);

    // Aging report
    $aging = $this->Invoice_model->get_aging_report($invoice_id);

    // Prepare data array
    $data = array(
        'invoice_number' => $invoice['number'],
        'invoice_date' => format_date($invoice['created_at']),
        'invoice_date_hijri' => format_hijri_date($invoice['created_at']),
        'items' => $items,
        'invoice_total_value' => $totals['invoice_total'],
        'total_discounts' => $totals['total_discounts'],
        'subtotal_before_tax' => $totals['subtotal'],
        'total_tax' => $totals['tax_amount'],
        'total_after_tax' => $totals['grand_total'],
        'aging_less_30' => $aging['less_30'],
        'aging_30_60' => $aging['thirty_to_sixty'],
        // ... other data
    );

    return $data;
}
```

## Notes

- All monetary values should be in Saudi Riyal (SAR)
- Maintain exact decimal precision for accounting accuracy
- Validate all calculations before passing to view
- Store original data in database, calculate display values in controller/model
- Document any custom tax rate logic or exemptions
