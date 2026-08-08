<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tax Invoice - <?php echo $invoice_number; ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            line-height: 1.4;
            color: #333;
            background: #fff;
        }
        .container {
            max-width: 210mm;
            min-height: 297mm;
            margin: 0 auto;
            padding: 10mm;
            background: white;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 20px;
            border-bottom: 2px solid #333;
            padding-bottom: 15px;
        }
        .header-left {
            flex: 1;
        }
        .header-right {
            text-align: center;
            flex: 1;
        }
        .company-logo {
            width: 80px;
            height: auto;
            margin-bottom: 10px;
        }
        .invoice-title {
            text-align: center;
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 10px;
            border: 2px solid #333;
            padding: 8px;
            border-radius: 8px;
        }
        .invoice-title-ar {
            font-size: 14px;
            margin-bottom: 5px;
        }
        .invoice-meta {
            display: flex;
            justify-content: center;
            gap: 30px;
            margin-top: 10px;
            font-weight: bold;
        }
        .info-box {
            border: 1px solid #333;
            padding: 12px;
            margin-bottom: 15px;
            border-radius: 4px;
        }
        .info-box-title {
            font-weight: bold;
            margin-bottom: 10px;
            text-decoration: underline;
        }
        .info-row {
            display: flex;
            margin-bottom: 4px;
            font-size: 10px;
            border: none;
        }
        .info-label {
            font-weight: bold;
            min-width: 120px;
        }
        .info-value {
            flex: 1;
        }
        .seller-buyer {
            display: flex;
            margin-bottom: 20px;
            width: 100%;
        }
        .seller-buyer > div {
            flex: 0 0 50%;
            width: 50%;
            padding: 0 10px;
        }
        .seller-buyer > div:first-child {
            padding-left: 0;
        }
        .seller-buyer > div:last-child {
            padding-right: 0;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            font-size: 10px;
        }
        .items-table thead {
            background-color: #f0f0f0;
            font-weight: bold;
        }
        .items-table th {
            border: 1px solid #333;
            padding: 8px;
            text-align: center;
        }
        .items-table td {
            border: 1px solid #333;
            padding: 6px;
            text-align: center;
        }
        .items-table td.text-left {
            text-align: left;
        }
        .item-row {
            page-break-inside: avoid;
        }
        .summary-section {
            margin: 20px 0;
            display: flex;
            gap: 20px;
        }
        .summary-left {
            flex: 1;
            font-size: 10px;
        }
        .summary-right {
            width: 200px;
        }
        .summary-box {
            border: 1px solid #333;
            padding: 10px;
            margin-bottom: 10px;
        }
        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
        }
        .summary-row-label {
            font-weight: bold;
        }
        .summary-row-value {
            text-align: right;
        }
        .aging-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            font-size: 9px;
        }
        .aging-table td {
            border: 1px solid #333;
            padding: 5px;
            text-align: center;
        }
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #333;
            display: flex;
            justify-content: space-around;
            font-size: 10px;
        }
        .footer-field {
            text-align: center;
            flex: 1;
        }
        .footer-line {
            border-top: 1px solid #333;
            margin-top: 30px;
            padding-top: 5px;
        }
        .text-center {
            text-align: center;
        }
        .text-right {
            text-align: right;
        }
        @media print {
            body {
                margin: 0;
                padding: 0;
            }
            .container {
                max-width: 100%;
                margin: 0;
                padding: 0;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header Section -->
        <div class="header">
            <div class="header-left">
                <!-- QR Code -->
                <div style="width: 80px; height: 80px; text-align: center; line-height: 80px;">
                    <?php echo $qr_code_image; ?>
                </div>
            </div>
            <div class="header-right">
                <div class="invoice-title">
                    <div class="invoice-title-ar">فاتورة ضريبية</div>
                    <div>Tax Invoice</div>
                </div>
                <div class="invoice-meta">
                    <div>No: <?php echo $invoice_number; ?></div>
                    <div>الرقم: <?php echo $invoice_number; ?></div>
                </div>
                <div class="invoice-meta">
                    <div>Date <?php echo $invoice_date; ?></div>
                    <div>التاريخ <?php echo $invoice_date_hijri; ?></div>
                </div>
                <div style="text-align: center; margin-top: 10px;">
                    <div style="font-weight: bold; font-size: 12px;"><?php echo $company_name; ?></div>
                    <div style="font-size: 9px;"><?php echo $company_name_ar; ?></div>
                </div>
            </div>
        </div>

        <!-- Company and Customer Info -->
        <table style="width: 100%; margin-bottom: 20px; border-collapse: collapse;" cellpadding="0" cellspacing="0">
            <tr>
                <!-- Seller Info -->
                <td style="width: 50%; vertical-align: top; padding-right: 10px;">
                    <div class="info-box">
                        <div class="info-box-title">بيانات الشركة / Company Information</div>
                        <div class="info-row">
                            <div class="info-label">اسم الشركة:</div>
                            <div class="info-value"><?php echo $seller_company_name; ?></div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">Company Name:</div>
                            <div class="info-value"><?php echo $seller_company_name_en; ?></div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">الرقم الضريبي:</div>
                            <div class="info-value"><?php echo $seller_tax_id; ?></div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">رقم السجل التجاري:</div>
                            <div class="info-value"><?php echo $seller_commercial_reg; ?></div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">الهاتف:</div>
                            <div class="info-value"><?php echo $seller_phone; ?></div>
                        </div>
                        <?php if (!empty($seller_international_id)): ?>
                        <div class="info-row">
                            <div class="info-label">الرقم العالمي:</div>
                            <div class="info-value"><?php echo $seller_international_id; ?></div>
                        </div>
                        <?php endif; ?>
                    </div>
                </td>

                <!-- Customer Info -->
                <td style="width: 50%; vertical-align: top; padding-left: 10px;">
                    <div class="info-box">
                        <div class="info-box-title">بيانات العميل / Customer Information</div>
                        <div class="info-row">
                            <div class="info-label">اسم العميل:</div>
                            <div class="info-value"><?php echo $customer_name; ?></div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">Customer Name:</div>
                            <div class="info-value"><?php echo $customer_name_en; ?></div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">الرقم الضريبي:</div>
                            <div class="info-value"><?php echo $customer_tax_id; ?></div>
                        </div>
                        <?php if (!empty($customer_commercial_reg)): ?>
                        <div class="info-row">
                            <div class="info-label">رقم السجل التجاري:</div>
                            <div class="info-value"><?php echo $customer_commercial_reg; ?></div>
                        </div>
                        <?php endif; ?>
                        <?php if (!empty($customer_phone)): ?>
                        <div class="info-row">
                            <div class="info-label">الهاتف:</div>
                            <div class="info-value"><?php echo $customer_phone; ?></div>
                        </div>
                        <?php endif; ?>
                        <?php if (!empty($customer_international_id)): ?>
                        <div class="info-row">
                            <div class="info-label">الرقم العالمي:</div>
                            <div class="info-value"><?php echo $customer_international_id; ?></div>
                        </div>
                        <?php endif; ?>
                    </div>
                </td>
            </tr>
        </table>

        <!-- Items Table -->
        <table class="items-table">
            <thead>
                <tr>
                    <th>وصف المادة<br>Description</th>
                    <th>الكمية<br>Quantity</th>
                    <th>السعر<br>Unit Price</th>
                    <th>الإجمالي<br>Total</th>
                    <th>خصم 1<br>Discount 1 %</th>
                    <th>خصم 2<br>Discount 2 %</th>
                    <th>إجمالي الخصم<br>Total Discount</th>
                    <th>الصافي بعد الخصم<br>Net After Discount</th>
                    <th>نسبة الضريبة<br>Tax Rate %</th>
                    <th>قيمة الضريبية<br>Tax Amount</th>
                    <th>الإجمالي<br>Grand Total</th>
                </tr>
            </thead>
            <tbody>
                <?php if (isset($items) && is_array($items)): ?>
                    <?php foreach ($items as $item): ?>
                    <tr class="item-row">
                        <td class="text-left">
                            <div><?php echo $item['description']; ?></div>
                            <div><?php echo $item['description_en']; ?></div>
                            <small>
                                <?php echo $item['item_code']; ?> 
                                <?php if (!empty($item['lot_number'])): ?>| Lot: <?php echo $item['lot_number']; ?><?php endif; ?>
                                <?php if (!empty($item['expiry_date'])): ?>| EXP: <?php echo $item['expiry_date']; ?><?php endif; ?>
                            </small>
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
                <?php endif; ?>
            </tbody>
        </table>

        <!-- Summary Section -->
        <div class="summary-section">
            <!-- Left: Aging & Notes -->
            <div class="summary-left">
                <div style="font-weight: bold; margin-bottom: 10px;">تقرير الصيد (Aging Report)</div>
                <table class="aging-table">
                    <tr>
                        <td>أقل من 30</td>
                        <td>من 30 إلى 60</td>
                        <td>من 60 إلى 90</td>
                        <td>من 90 إلى 120</td>
                        <td>أكثر من 120</td>
                    </tr>
                    <tr>
                        <td><?php echo number_format($aging_less_30, 2); ?></td>
                        <td><?php echo number_format($aging_30_60, 2); ?></td>
                        <td><?php echo number_format($aging_60_90, 2); ?></td>
                        <td><?php echo number_format($aging_90_120, 2); ?></td>
                        <td><?php echo number_format($aging_more_120, 2); ?></td>
                    </tr>
                </table>
                <div style="margin-top: 15px; font-size: 9px; line-height: 1.6;">
                    <?php echo $invoice_notes; ?>
                </div>
            </div>

            <!-- Right: Totals Summary -->
            <div class="summary-right">
                <div class="summary-box">
                    <div class="summary-row">
                        <div class="summary-row-label">الرصيد:</div>
                        <div class="summary-row-value"><?php echo number_format($invoice_total_value, 2); ?></div>
                    </div>
                </div>
                <div class="summary-box">
                    <div class="summary-row">
                        <div class="summary-row-label">إجمالي الخصومات:</div>
                        <div class="summary-row-value"><?php echo number_format($total_discounts, 2); ?></div>
                    </div>
                </div>
                <div class="summary-box">
                    <div class="summary-row">
                        <div class="summary-row-label">الإجمالي قبل الضريبة:</div>
                        <div class="summary-row-value"><?php echo number_format($subtotal_before_tax, 2); ?></div>
                    </div>
                </div>
                <div class="summary-box">
                    <div class="summary-row">
                        <div class="summary-row-label">الضريبة المضافة:</div>
                        <div class="summary-row-value"><?php echo number_format($total_tax, 2); ?></div>
                    </div>
                </div>
                <div class="summary-box" style="background-color: #f0f0f0; font-weight: bold;">
                    <div class="summary-row">
                        <div class="summary-row-label">الصافي بعد الضريبة:</div>
                        <div class="summary-row-value"><?php echo number_format($total_after_tax, 2); ?></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <div class="footer-field">
                <div>أعده: ...........................</div>
                <div>Prepared by: ...........................</div>
            </div>
            <div class="footer-field">
                <div>راجعها: ...........................</div>
                <div>Reviewed by: ...........................</div>
            </div>
            <div class="footer-field">
                <div>اسم المستلم: ...........................</div>
                <div>Receiver Name: ...........................</div>
            </div>
            <div class="footer-field">
                <div>ختم المستلم</div>
                <div style="height: 40px;"></div>
                <div>Receiver Stamp</div>
            </div>
        </div>

        <!-- Signature Line -->
        <div style="margin-top: 20px; text-align: center; padding-top: 20px; border-top: 1px solid #333;">
            <div>التوقيع: ...........................</div>
            <div>Signature: ...........................</div>
        </div>
    </div>
</body>
</html>
