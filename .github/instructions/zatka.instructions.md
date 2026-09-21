---
applyTo: "**"
---

# GitHub Copilot Instructions: Zatka-Compliant Invoice in CodeIgniter 3

## Project Overview

Generate a CodeIgniter 3 invoice module that renders Zatka-compliant invoices using the provided HTML template with dynamic data variables.

## File Structure to Create

```
application/
├── controllers/
│   └── Invoices.php
├── models/
│   └── Invoice_model.php
├── views/
│   └── invoice/
│       ├── zatka_invoice_template.php
│       └── invoice_list.php
├── helpers/
│   └── invoice_helper.php
└── libraries/
    └── Invoice_generator.php
```

## Copilot Prompt 1: Controller Creation

```
Context: CodeIgniter 3 framework
Task: Create an Invoices controller that handles invoice generation and rendering

Requirements:
1. Method load_invoice($invoice_id): Load invoice data from database and render template
2. Method generate_pdf($invoice_id): Generate PDF using invoice HTML
3. Method preview($invoice_id): Display invoice preview in browser
4. Method send_email($invoice_id): Email invoice to customer

Data to pass to view:
- Invoice number, date, customer info, seller info
- Array of line items with: description, quantity, price, discounts, tax
- Summary totals: subtotal, discounts, tax, grand total
- Aging report data

Template variables to replace:
{invoice_number}, {invoice_date}, {invoice_date_hijri}
{company_name}, {company_name_ar}
{seller_company_name}, {seller_tax_id}, {seller_commercial_reg}, {seller_phone}, {seller_international_id}
{customer_name}, {customer_tax_id}, {customer_commercial_reg}, {customer_phone}, {customer_international_id}
{items_rows}, {invoice_total_value}, {total_discounts}, {subtotal_before_tax}, {total_tax}, {total_after_tax}
{aging_less_30}, {aging_30_60}, {aging_60_90}, {aging_90_120}, {aging_more_120}
{invoice_notes}

Create the controller with proper error handling and Zatka validation.
```

## Copilot Prompt 2: Model Creation

```
Context: CodeIgniter 3 Invoice Model

Task: Create an Invoice_model to handle database operations

Requirements:
1. get_invoice($invoice_id): Fetch complete invoice with items
2. get_invoice_items($invoice_id): Get line items with calculations
3. calculate_totals($invoice_id): Calculate subtotal, discounts, taxes, grand total
4. get_aging_report($invoice_id): Get payment aging information
5. validate_zatka_requirements($invoice_data): Validate invoice meets Zatka compliance

Fields needed:
- Invoice: id, number, date, customer_id, seller_id, status, total_value, tax_amount
- Items: id, invoice_id, description, quantity, unit_price, discount_1, discount_2, tax_rate, tax_amount
- Customer: id, name, name_ar, tax_id, commercial_reg, phone, international_id
- Seller: id, name, name_ar, tax_id, commercial_reg, phone, international_id

Include proper JOIN queries to fetch related data efficiently.
```

## Copilot Prompt 3: Invoice Helper

```
Context: CodeIgniter 3 Invoice Helper

Task: Create helper functions for invoice processing

Functions needed:
1. format_currency($value): Format numbers as Saudi Riyal with 2 decimals
2. format_date($date): Convert date to Saudi format (d/m/Y H:i)
3. format_hijri_date($gregorian_date): Convert Gregorian to Hijri calendar
4. calculate_discount($original, $discount_1_pct, $discount_2_pct): Apply cascading discounts
5. calculate_tax($amount, $tax_rate): Calculate tax amount
6. generate_invoice_number(): Create unique invoice number format
7. validate_tax_id($tax_id): Validate Saudi tax ID format
8. format_phone_number($phone): Format phone number

Include currency formatting for SAR (Saudi Riyal).
```

## Copilot Prompt 4: View Template Integration

```
Context: CodeIgniter 3 View

Task: Convert the zatka_invoice_template.html to zatka_invoice_template.php

Requirements:
1. Replace all {variable_name} with <?php echo $variable_name; ?>
2. Create loop for {items_rows} using foreach for line items array
3. Add item row template with proper formatting for each row
4. Ensure the HTML structure remains exactly as provided
5. Do NOT modify the original CSS or layout
6. Add PHP logic for conditional display of optional fields

Item row template structure:
- Description (Arabic + English)
- Lot number and expiry date
- Quantity
- Unit price
- Discounts (1 & 2)
- Total discount
- Net after discount
- Tax rate
- Tax amount
- Grand total

Each item should calculate and display:
total = quantity × unit_price
discount_amount = (total × discount_1%) + ((total - discount_1_amount) × discount_2%)
net = total - discount_amount
tax_value = net × tax_rate
line_total = net + tax_value
```

## Copilot Prompt 5: PDF Generation Library

```
Context: CodeIgniter 3 Custom Library

Task: Create Invoice_generator library for PDF generation

Requirements:
1. Use TCPDF or similar library for PDF generation
2. load_html($html_string): Load HTML and convert to PDF
3. save_pdf($filename): Save PDF to server
4. stream_pdf($filename): Stream PDF to browser
5. email_pdf($customer_email, $pdf_content): Email PDF to customer

Include:
- QR Code generation (if required by Zatka)
- Header/Footer support
- Page numbering
- Arabic text support (RTL)
- Proper font encoding for Arabic characters

Handle errors gracefully with user-friendly messages.
```

## Copilot Prompt 6: Database Migrations

```
Context: CodeIgniter 3 Database

Task: Create database migration files for invoice tables

Tables needed:
1. invoices: (id, number, date, customer_id, seller_id, status, total_value, tax_amount, created_at, updated_at)
2. invoice_items: (id, invoice_id, description, description_ar, quantity, unit_price, discount_1_pct, discount_2_pct, tax_rate, tax_amount, created_at)
3. customers: (id, name, name_ar, tax_id, commercial_reg, phone, international_id, created_at)
4. sellers: (id, name, name_ar, tax_id, commercial_reg, phone, international_id, created_at)

Include proper foreign keys, indexes, and constraints.
Create up() and down() migration methods.
```

## Key Variables Mapping

| Template Variable     | Source               | Type      |
| --------------------- | -------------------- | --------- |
| {invoice_number}      | invoices.number      | String    |
| {invoice_date}        | invoices.date        | DateTime  |
| {invoice_date_hijri}  | Converted via helper | String    |
| {company_name}        | sellers.name         | String    |
| {company_name_ar}     | sellers.name_ar      | String    |
| {seller_tax_id}       | sellers.tax_id       | String    |
| {customer_name}       | customers.name       | String    |
| {items_rows}          | invoice_items array  | HTML Loop |
| {total_discounts}     | SUM(discounts)       | Currency  |
| {subtotal_before_tax} | SUM(net_amount)      | Currency  |
| {total_tax}           | SUM(tax_amount)      | Currency  |
| {total_after_tax}     | subtotal + tax       | Currency  |

## CodeIgniter 3 Integration Checklist

- [ ] Create database tables
- [ ] Build Invoice_model with all CRUD operations
- [ ] Create invoice_helper with calculation functions
- [ ] Build Invoices controller with main methods
- [ ] Adapt HTML template to .php view with variables
- [ ] Create Invoice_generator library for PDF
- [ ] Add routes in config/routes.php
- [ ] Test invoice generation with sample data
- [ ] Test PDF rendering
- [ ] Test email delivery
- [ ] Validate Zatka compliance requirements
- [ ] Add error handling and logging
- [ ] Create user documentation

## Important Zatka Compliance Notes

Based on the provided invoice template, ensure:

1. Invoice must include both Arabic and English
2. Include seller tax ID (الرقم الضريبي)
3. Include customer tax ID for B2B invoices
4. Include commercial registration number (رقم السجل التجاري)
5. Apply proper VAT/TAX rates
6. Maintain invoice number sequence
7. Include QR code (if Zatka requires)
8. Store invoice in PDF format for audit trail

## Usage Example

```php
// In controller
$this->load->model('Invoice_model');
$invoice_data = $this->Invoice_model->get_invoice($invoice_id);
$this->load->view('invoice/zatka_invoice_template', $invoice_data);
```

## Security Considerations

1. Validate user has permission to view/generate invoice
2. Sanitize all input data
3. Protect PDF downloads with authentication
4. Log all invoice generation activities
5. Validate Zatka tax ID formats
6. Ensure data encryption for sensitive information
