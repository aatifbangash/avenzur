<?php
/**
 * CodeIgniter 3 View: sales/zatka_invoice.php
 * 
 * Pure HTML table-based Zatka-compliant invoice
 * Follows zatka_invoice_pure_tables.html structure exactly
 * 
 * Variables passed from controller:
 * @param string $invoice_number
 * @param string $invoice_date
 * @param string $invoice_date_hijri
 * @param array  $items - Array of line items
 * @param array  $seller - Seller information
 * @param array  $customer - Customer information
 * @param array  $totals - Summary totals
 * @param array  $aging - Aging report data
 * @param string $notes - Invoice notes
 * @param string $qr_code_image - QR code HTML/image
 */
?><!DOCTYPE html>
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
        .page {
            width: 210mm;
            min-height: 297mm;
            margin: 0 auto;
            padding: 10mm;
            background: white;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        .border-solid {
            border: 1px solid #333;
        }
        .border-bottom {
            border-bottom: 2px solid #333;
        }
        .center {
            text-align: center;
        }
        .right {
            text-align: right;
        }
        .left {
            text-align: left;
        }
        .bold {
            font-weight: bold;
        }
        .bg-light {
            background-color: #f0f0f0;
        }
        .underline {
            text-decoration: underline;
        }
        td {
            padding: 6px;
            vertical-align: top;
        }
        th {
            padding: 8px;
            text-align: center;
        }
        @media print {
            body {
                margin: 0;
                padding: 0;
            }
            .page {
                width: 100%;
                min-height: 100%;
                margin: 0;
                padding: 10mm;
            }
        }
    </style>
</head>
<body>
    <div class="page">
        
        <!-- ===== HEADER SECTION ===== -->
        <table style="margin-bottom: 20px;">
            <tr>
                <!-- QR Code Column -->
                <td style="width: 80px; padding: 0;">
                    <?php if (isset($qr_code_image) && !empty($qr_code_image)): ?>
                        <?php echo $qr_code_image; ?>
                    <?php else: ?>
                        <div style="width: 60px; height: 60px; border: 1px solid #ccc;"></div>
                    <?php endif; ?>
                </td>
                <!-- Spacer Column -->
                <td style="width: 10px; padding: 0;"></td>
                <!-- Title and Company Info Column -->
                <td style="padding: 0;">
                    <!-- Invoice Title Box -->
                    <table style="margin: 0 auto; width: 350px; margin-bottom: 10px;">
                        <tr>
                            <td class="border-solid center bold" style="font-size: 16px; padding: 8px;">
                                فاتورة ضريبية / TAX INVOICE<br>
                                <span style="font-size: 12px;">Invoice</span>
                            </td>
                        </tr>
                    </table>
                    
                    <!-- Invoice Number and Date -->
                    <table style="margin: 0 auto; width: 350px; margin-bottom: 8px;">
                        <tr>
                            <td class="center bold" style="padding: 3px;">
                                رقم الفاتورة<br>
                                <span style="font-size: 10px;">Invoice Number</span>
                            </td>
                            <td class="center bold" style="padding: 3px;">
                                التاريخ<br>
                                <span style="font-size: 10px;">Date</span>
                            </td>
                        </tr>
                        <tr>
                            <td class="center bold" style="padding: 3px;">
                                <?php echo $invoice_number; ?>
                            </td>
                            <td class="center bold" style="padding: 3px;">
                                <?php echo $invoice_date; ?><br>
                                <span style="font-size: 9px;"><?php echo $invoice_date_hijri; ?></span>
                            </td>
                        </tr>
                    </table>

                    <!-- Company Name -->
                    <table style="margin: 0 auto; width: 350px;">
                        <tr>
                            <td class="center bold" style="font-size: 12px; padding: 3px;">
                                <?php echo $seller['name_ar']; ?>
                            </td>
                        </tr>
                        <tr>
                            <td class="center" style="font-size: 9px; padding: 3px;">
                                <?php echo $seller['name_en']; ?>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>

        <hr style="border: none; border-bottom: 2px solid #333; margin: 15px 0;">

        <!-- ===== COMPANY AND CUSTOMER INFO SECTION ===== -->
        <table style="margin-bottom: 20px; width: 100%;">
            <tr>
                <!-- Seller Info -->
                <td style="width: 50%; padding-right: 15px; padding-left: 0;">
                    <table class="border-solid" style="width: 100%;">
                        <tr>
                            <td class="bg-light center bold" style="padding: 8px;">
                                بيانات البائع / Seller Information
                            </td>
                        </tr>
                        <tr>
                            <td style="padding: 10px; font-size: 10px;">
                                <div style="margin-bottom: 5px;">
                                    <span class="bold">اسم الشركة / Company Name:</span><br>
                                    <?php echo $seller['name_ar']; ?> / <?php echo $seller['name_en']; ?>
                                </div>
                                <div style="margin-bottom: 5px;">
                                    <span class="bold">الرقم الضريبي / Tax ID:</span><br>
                                    <?php echo $seller['tax_id']; ?>
                                </div>
                                <div style="margin-bottom: 5px;">
                                    <span class="bold">رقم السجل التجاري / Commercial Reg:</span><br>
                                    <?php echo $seller['commercial_reg']; ?>
                                </div>
                                <div style="margin-bottom: 5px;">
                                    <span class="bold">الهاتف / Phone:</span><br>
                                    <?php echo $seller['phone']; ?>
                                </div>
                                <?php if (!empty($seller['international_id'])): ?>
                                <div style="margin-bottom: 5px;">
                                    <span class="bold">المعرف الدولي / International ID:</span><br>
                                    <?php echo $seller['international_id']; ?>
                                </div>
                                <?php endif; ?>
                            </td>
                        </tr>
                    </table>
                </td>

                <!-- Customer Info -->
                <td style="width: 50%; padding-left: 15px; padding-right: 0;">
                    <table class="border-solid" style="width: 100%;">
                        <tr>
                            <td class="bg-light center bold" style="padding: 8px;">
                                بيانات العميل / Customer Information
                            </td>
                        </tr>
                        <tr>
                            <td style="padding: 10px; font-size: 10px;">
                                <div style="margin-bottom: 5px;">
                                    <span class="bold">اسم العميل / Customer Name:</span><br>
                                    <?php echo $customer['name_ar']; ?> / <?php echo $customer['name_en']; ?>
                                </div>
                                <div style="margin-bottom: 5px;">
                                    <span class="bold">الرقم الضريبي / Tax ID:</span><br>
                                    <?php echo $customer['tax_id']; ?>
                                </div>
                                <div style="margin-bottom: 5px;">
                                    <span class="bold">رقم السجل التجاري / Commercial Reg:</span><br>
                                    <?php echo $customer['commercial_reg']; ?>
                                </div>
                                <div style="margin-bottom: 5px;">
                                    <span class="bold">الهاتف / Phone:</span><br>
                                    <?php echo $customer['phone']; ?>
                                </div>
                                <?php if (!empty($customer['address_city'])): ?>
                                <div style="margin-bottom: 5px;">
                                    <span class="bold">المدينة / City:</span><br>
                                    <?php echo $customer['address_city']; ?>
                                </div>
                                <?php endif; ?>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>

        <!-- ===== ITEMS TABLE SECTION ===== -->
        <table class="border-solid" style="margin-bottom: 20px; font-size: 10px;">
            <thead>
                <tr class="bg-light">
                    <th class="border-solid" style="font-size: 9px;">وصف المادة<br>Description</th>
                    <th class="border-solid" style="font-size: 9px; width: 50px;">الكمية<br>Quantity</th>
                    <th class="border-solid" style="font-size: 9px; width: 60px;">السعر<br>Unit Price</th>
                    <th class="border-solid" style="font-size: 9px; width: 60px;">الإجمالي<br>Total</th>
                    <th class="border-solid" style="font-size: 9px; width: 50px;">خصم 1<br>Discount 1 %</th>
                    <th class="border-solid" style="font-size: 9px; width: 50px;">خصم 2<br>Discount 2 %</th>
                    <th class="border-solid" style="font-size: 9px; width: 60px;">إجمالي الخصم<br>Total Discount</th>
                    <th class="border-solid" style="font-size: 9px; width: 70px;">الصافي بعد الخصم<br>Net After Discount</th>
                    <th class="border-solid" style="font-size: 9px; width: 50px;">نسبة الضريبة<br>Tax Rate %</th>
                    <th class="border-solid" style="font-size: 9px; width: 60px;">قيمة الضريبية<br>Tax Amount</th>
                    <th class="border-solid" style="font-size: 9px; width: 60px;">الإجمالي<br>Grand Total</th>
                </tr>
            </thead>
            <tbody>
                <?php if (isset($items) && is_array($items) && count($items) > 0): ?>
                    <?php foreach ($items as $item): ?>
                        <tr>
                            <td class="border-solid left" style="font-size: 10px;">
                                <div class="bold"><?php echo $item['description_ar']; ?></div>
                                <div><?php echo $item['description_en']; ?></div>
                                <?php if (!empty($item['item_code']) || !empty($item['lot_number']) || !empty($item['expiry_date'])): ?>
                                    <small style="font-size: 8px; display: block; margin-top: 2px;">
                                        <?php if (!empty($item['item_code'])): ?>
                                            <?php echo $item['item_code']; ?>
                                        <?php endif; ?>
                                        <?php if (!empty($item['lot_number'])): ?>
                                            | Lot: <?php echo $item['lot_number']; ?>
                                        <?php endif; ?>
                                        <?php if (!empty($item['expiry_date'])): ?>
                                            | EXP: <?php echo $item['expiry_date']; ?>
                                        <?php endif; ?>
                                    </small>
                                <?php endif; ?>
                            </td>
                            <td class="border-solid center"><?php echo number_format($item['quantity'], 2); ?></td>
                            <td class="border-solid right"><?php echo number_format($item['unit_price'], 2); ?></td>
                            <td class="border-solid right"><?php echo number_format($item['total'], 2); ?></td>
                            <td class="border-solid center">
                                <div><?php echo $item['discount_1_percent']; ?>%</div>
                                <small><?php echo number_format($item['discount_1_amount'], 2); ?></small>
                            </td>
                            <td class="border-solid center">
                                <div><?php echo $item['discount_2_percent']; ?>%</div>
                                <small><?php echo number_format($item['discount_2_amount'], 2); ?></small>
                            </td>
                            <td class="border-solid right"><?php echo number_format($item['total_discount'], 2); ?></td>
                            <td class="border-solid right"><?php echo number_format($item['net_after_discount'], 2); ?></td>
                            <td class="border-solid center"><?php echo $item['tax_rate_percent']; ?>%</td>
                            <td class="border-solid right"><?php echo number_format($item['tax_amount'], 2); ?></td>
                            <td class="border-solid right"><?php echo number_format($item['line_total'], 2); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>

        <!-- ===== SUMMARY AND AGING SECTION ===== -->
        <table style="margin-bottom: 20px; width: 100%;">
            <tr>
                <!-- LEFT: Aging Report -->
                <td style="width: 55%; padding-right: 15px; padding-left: 0; vertical-align: top;">
                    <div class="bold" style="margin-bottom: 10px; font-size: 11px;">تقرير الصيد (Aging Report)</div>
                    
                    <table class="border-solid" style="margin-bottom: 10px; font-size: 10px;">
                        <tr class="bg-light">
                            <th class="border-solid">أقل من 30 يوم<br>Less than 30</th>
                            <th class="border-solid">30-60 يوم<br>30-60</th>
                            <th class="border-solid">60-90 يوم<br>60-90</th>
                            <th class="border-solid">90-120 يوم<br>90-120</th>
                            <th class="border-solid">أكثر من 120<br>More than 120</th>
                        </tr>
                        <tr>
                            <td class="border-solid center"><?php echo number_format($aging['less_30'], 2); ?></td>
                            <td class="border-solid center"><?php echo number_format($aging['thirty_to_sixty'], 2); ?></td>
                            <td class="border-solid center"><?php echo number_format($aging['sixty_to_ninety'], 2); ?></td>
                            <td class="border-solid center"><?php echo number_format($aging['ninety_to_one_twenty'], 2); ?></td>
                            <td class="border-solid center"><?php echo number_format($aging['more_than_one_twenty'], 2); ?></td>
                        </tr>
                    </table>

                    <?php if (!empty($notes)): ?>
                        <div style="font-size: 9px; line-height: 1.6;">
                            <strong>ملاحظات / Notes:</strong><br>
                            <?php echo $notes; ?>
                        </div>
                    <?php endif; ?>
                </td>

                <!-- RIGHT: Summary Totals -->
                <td style="width: 45%; padding-left: 15px; padding-right: 0; vertical-align: top;">
                    <!-- Invoice Total -->
                    <table class="border-solid" style="margin-bottom: 10px; font-size: 10px;">
                        <tr>
                            <td class="bold" style="padding: 6px;">إجمالي الفاتورة / Invoice Total</td>
                            <td class="right bold" style="padding: 6px;"><?php echo number_format($totals['invoice_total'], 2); ?></td>
                        </tr>
                    </table>

                    <!-- Total Discounts -->
                    <table class="border-solid" style="margin-bottom: 10px; font-size: 10px;">
                        <tr>
                            <td class="bold" style="padding: 6px;">إجمالي الخصم / Total Discounts</td>
                            <td class="right bold" style="padding: 6px;"><?php echo number_format($totals['total_discounts'], 2); ?></td>
                        </tr>
                    </table>

                    <!-- Subtotal Before Tax -->
                    <table class="border-solid" style="margin-bottom: 10px; font-size: 10px;">
                        <tr>
                            <td class="bold" style="padding: 6px;">الإجمالي قبل الضريبة / Subtotal Before Tax</td>
                            <td class="right bold" style="padding: 6px;"><?php echo number_format($totals['subtotal'], 2); ?></td>
                        </tr>
                    </table>

                    <!-- Tax Amount -->
                    <table class="border-solid" style="margin-bottom: 10px; font-size: 10px;">
                        <tr>
                            <td class="bold" style="padding: 6px;">قيمة الضريبة / Tax Amount</td>
                            <td class="right bold" style="padding: 6px;"><?php echo number_format($totals['tax_amount'], 2); ?></td>
                        </tr>
                    </table>

                    <!-- Grand Total (Highlighted) -->
                    <table class="border-solid bg-light" style="font-size: 10px;">
                        <tr>
                            <td class="bold" style="padding: 6px;">الإجمالي النهائي / Grand Total</td>
                            <td class="right bold" style="padding: 6px; font-size: 12px;"><?php echo number_format($totals['grand_total'], 2); ?> SAR</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>

        <!-- ===== FOOTER SECTION ===== -->
        <table style="margin-top: 30px; font-size: 10px;">
            <tr>
                <td style="width: 25%; text-align: center; padding: 0;">
                    <div style="padding: 20px 5px 0 5px; border-top: 1px solid #333; min-height: 40px;">
                        أعده<br>Prepared by
                    </div>
                </td>
                <td style="width: 25%; text-align: center; padding: 0;">
                    <div style="padding: 20px 5px 0 5px; border-top: 1px solid #333; min-height: 40px;">
                        راجعها<br>Reviewed by
                    </div>
                </td>
                <td style="width: 25%; text-align: center; padding: 0;">
                    <div style="padding: 20px 5px 0 5px; border-top: 1px solid #333; min-height: 40px;">
                        اسم المستلم<br>Receiver Name
                    </div>
                </td>
                <td style="width: 25%; text-align: center; padding: 0;">
                    <div style="padding: 20px 5px 0 5px; border-top: 1px solid #333;">
                        <div>ختم المستلم</div>
                        <div style="height: 40px;"></div>
                        <div>Receiver Stamp</div>
                    </div>
                </td>
            </tr>
        </table>

        <!-- ===== SIGNATURE LINE ===== -->
        <table style="margin-top: 20px; font-size: 10px;">
            <tr>
                <td class="center" style="padding: 15px 0; border-top: 1px solid #333;">
                    التوقيع: ........................... <br>
                    Signature: ...........................
                </td>
            </tr>
        </table>

    </div>
</body>
</html>
