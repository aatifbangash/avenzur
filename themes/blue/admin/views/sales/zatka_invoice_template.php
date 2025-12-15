<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<style>
body {
    font-family: 'Cairo', 'DejaVu Sans', sans-serif;
    font-size: 9px;
    margin: 0;
    padding: 0;
    line-height: 1.2;
    letter-spacing: 0.1px;
}

.page {
    width: 100%;
    padding: 2px 10px;
    margin: 0;
}

/* MAIN TABLES */
.table-main {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 5px;
}

.table-main td {
    vertical-align: top;
    padding: 2px;
    line-height: 1.2;
}

/* Customer/Supplier detail tables */
.table-main table {
    border-collapse: collapse;
    width: 100%;
}

.table-main table td {
    vertical-align: middle;
    padding: 1px;
    line-height: 1.1;
    height: 18px;
}

/* TITLE */
.title {
    font-size: 18px;
    font-weight: bold;
    text-align: center;
    border: 2px solid #000;
    border-radius: 10px;
    padding: 6px 20px;
    display: inline-block;
}

/* ITEMS TABLE */
.items-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 10px;
    table-layout: fixed;
}

.items-table th, .items-table td {
    text-align: center;
    vertical-align: middle;
    padding: 2px;
    
}

.items-table th div{ 
    padding: 4px 2px;
    height: 70px;
    box-sizing: border-box;
    text-align: center;
    line-height: 1.3;
    overflow: hidden;
}

.items-table td div {
    padding: 4px 2px;
    height: 70px;
    box-sizing: border-box;
    text-align: center;
    line-height: 1.3;
    overflow: hidden;
    white-space: nowrap;
}

.items-table th {
    font-weight: bold;
}

.header-line {
    border-bottom: 1px solid #000;
    width: 100%;
    display: block;
    padding: 2px 0;
    margin: 0;
}

div.box {
    border: 1px solid #000;
    border-radius: 8px;
    height: 70px;
    padding: 4px 2px;
    width: 100%;
    text-align: center;
    box-sizing: border-box;
    overflow: hidden;
}

div.box-content {
    display: block;
    width: 100%;
    height: 100%;
}

div.box-content span {
    display: block;
    width: 100%;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

/* Description column can wrap */
.items-table td:first-child div.box-content span,
.items-table th:first-child div.box-content span {
    white-space: normal;
    font-size: 10px;
    line-height: 1.2;
}

/* Keep the second line (code, lot, expiry) on one line */
.items-table td:first-child div.box-content span:last-child {
    white-space: nowrap;
    font-size: 8px;
    overflow: hidden;
    text-overflow: ellipsis;
}
</style>
</head>

<body>
<div class="page">

<!-- HEADER -->
<table class="table-main">
<tr>
    <td width="20%" align="center">
        <div style="height:100px; display:flex; align-items:center; justify-content:center;">
            <?php
            // Custom readable QR code format
            $company_name = $biller->name_ar && $biller->name_ar != '-' ? $biller->name_ar : $biller->name;
            $vat_number = $biller->vat_no ?: $biller->get_no;
            $invoice_no = $inv->reference_no;
            $invoice_date = date('d/m/Y H:i:s', strtotime($inv->date));
            $grand_total = $return_sale ? ($inv->grand_total + $return_sale->grand_total) : $inv->grand_total;
            $total_tax = $return_sale ? ($inv->total_tax + $return_sale->total_tax) : $inv->total_tax;
            $invoice_id = $inv->id;
            
            $qr_data = "#" . $vat_number . "_" . $company_name . "_" . $invoice_no . "_" . $invoice_date . "_" . number_format($grand_total, 2, '.', '') . "_SAR_Tax:" . number_format($total_tax, 2, '.', '') . "#" . $invoice_id . "#";
            
            echo $this->sma->qrcode('text', $qr_data, 2);
            ?>
        </div>
    </td>
    <td width="60%" align="center" height="100">
        <div class="title">
            <span>الفاتورة الضريبية</span><br />
            <span>Tax Invoice</span>
        </div><br><br>
        <strong>Invoice No: <?= $inv->id ?></strong><br>
        <strong>Date: <?= $this->sma->hrld($inv->date) ?></strong>
    </td>
    <td width="20%" height="100">
        <div style="text-align:center; margin-bottom:5px;">
            <img src="<?= base_url() . 'assets/uploads/logos/' . $biller->logo ?>"
                alt="Avenzur" style="max-width:120px; height:auto;">
            
        </div>
    </td>
</tr>
</table>

<!-- SUPPLIER / CUSTOMER -->
<table class="table-main" style="margin-top:10px;">
<tr>

<!-- CUSTOMER -->
<td width="50%">
    <div style="border:1px solid #000; border-radius:12px; padding:4px; line-height:1.1;">
        <div style="text-align:center; font-weight:bold; border-radius:6px; padding:3px; margin-bottom:3px; border:1px solid #000; line-height:1.1; height:14px;">
            بيانات العميل
        </div>

        <table width="100%" cellpadding="0" cellspacing="0" style="border-collapse:separate; border-spacing:2px;">
            <tr>
                <td colspan="2" align="center" style="border:1px solid #000; border-radius:8px; padding:2px; line-height:1.1; height:22px;">
                    <?= $customer->name ?>
                </td>
                <td colspan="2" align="center" style="border:1px solid #000; border-radius:8px; padding:2px; line-height:1.1; height:22px;">اسم العميل:</td>
            </tr>

            <tr>
                <td width="32.5%" align="center" style="border:1px solid #000; border-radius:8px; padding:2px; line-height:1.1; height:16px;">
                    <?= $customer->cr ?>
                </td>
                <td width="17.5%" align="center" style="border:1px solid #000; border-radius:8px; padding:2px; line-height:1.1; height:16px;">السجل التجاري:</td>
                <td width="32.5%" align="center" style="border:1px solid #000; border-radius:8px; padding:2px; line-height:1.1; height:16px;">
                    <?= $customer->vat_no ?>
                </td>
                <td width="17.5%" align="center" style="border:1px solid #000; border-radius:8px; padding:2px; line-height:1.1; height:16px;">الرقم الضريبي:</td>
            </tr>

            <tr>
                <td colspan="2" align="center" style="border:1px solid #000; border-radius:8px; padding:2px; line-height:1.1; height:16px;">
                    <?= $customer->phone ?>
                </td>
                <td colspan="2" align="center" style="border:1px solid #000; border-radius:8px; padding:2px; line-height:1.1; height:16px;">الهاتف:</td>
            </tr>

            <tr>
                <td width="32.5%" align="center" style="border:1px solid #000; border-radius:8px; padding:2px; line-height:1.1; height:16px;">
                    <?= $customer->city ?>
                </td>
                <td width="17.5%" align="center" style="border:1px solid #000; border-radius:8px; padding:2px; line-height:1.1; height:16px;">المدينة:</td>
                <td width="32.5%" align="center" style="border:1px solid #000; border-radius:8px; padding:2px; line-height:1.1; height:16px;">
                    <?= $customer->address ?? '' ?>
                </td>
                <td width="17.5%" align="center" style="border:1px solid #000; border-radius:8px; padding:2px; line-height:1.1; height:16px;">الحي :</td>
            </tr>

            <tr>
                <td width="32.5%" align="center" style="border:1px solid #000; border-radius:8px; padding:2px; line-height:1.1; height:16px;">
                    <?= $customer->building_number ?? '00000' ?>
                </td>
                <td width="17.5%" align="center" style="border:1px solid #000; border-radius:8px; padding:2px; line-height:1.1; height:16px;">رقم المبنى:</td>
                <td width="32.5%" align="center" style="border:1px solid #000; border-radius:8px; padding:2px; line-height:1.1; height:16px;">
                    <?= $customer->additional_number ?? '00000' ?>
                </td>
                <td width="17.5%" align="center" style="border:1px solid #000; border-radius:8px; padding:2px; line-height:1.1; height:16px;">رقم إضافي:</td>
            </tr>

            <tr>
                <td width="32.5%" align="center" style="border:1px solid #000; border-radius:8px; padding:2px; line-height:1.1; height:16px;">
                    <?= $customer->postal_code ?? '00000' ?>
                </td>
                <td width="17.5%" align="center" style="border:1px solid #000; border-radius:8px; padding:2px; line-height:1.1; height:16px;">الرمز البريدي:</td>
                <td width="32.5%" align="center" style="border:1px solid #000; border-radius:8px; padding:2px; line-height:1.1; height:16px;">
                    <?= $customer->gln ?? '00000' ?>
                </td>
                <td width="17.5%" align="center" style="border:1px solid #000; border-radius:8px; padding:2px; line-height:1.1; height:16px;">الرقم العالمي:</td>
            </tr>
        </table>
    </div>
</td>

<!-- COMPANY -->
<td width="50%">
    <div style="border:1px solid #000; border-radius:12px; padding:4px; line-height:1.1;">
        <div style="text-align:center; font-weight:bold; border-radius:6px; padding:3px; margin-bottom:3px; border:1px solid #000; line-height:1.1; height:14px;">
            بيانات الشركة
        </div>

        <table width="100%" cellpadding="0" cellspacing="0" style="border-collapse:separate; border-spacing:2px;">
            <tr>
                <td colspan="2" align="center" style="border:1px solid #000; border-radius:8px; padding:2px; line-height:1.1; height:22px;">
                    <?= $biller->name ?><br><?= $biller->name_ar ?>
                </td>
                <td colspan="2" align="center" style="border:1px solid #000; border-radius:8px; padding:2px; line-height:1.1; height:22px;">اسم الشركة:</td>
            </tr>

            <tr>
                <td width="32.5%" align="center" style="border:1px solid #000; border-radius:8px; padding:2px; line-height:1.1; height:16px;">
                    <?= $biller->cr ?>
                </td>
                <td width="17.5%" align="center" style="border:1px solid #000; border-radius:8px; padding:2px; line-height:1.1; height:16px;">السجل التجاري:</td>
                <td width="32.5%" align="center" style="border:1px solid #000; border-radius:8px; padding:2px; line-height:1.1; height:16px;">
                    <?= $biller->vat_no ?>
                </td>
                <td width="17.5%" align="center" style="border:1px solid #000; border-radius:8px; padding:2px; line-height:1.1; height:16px;">الرقم الضريبي:</td>
            </tr>

            <tr>
                <td colspan="2" align="center" style="border:1px solid #000; border-radius:8px; padding:2px; line-height:1.1; height:16px;">
                    <?= $biller->phone ?>
                </td>
                <td colspan="2" align="center" style="border:1px solid #000; border-radius:8px; padding:2px; line-height:1.1; height:16px;">الهاتف:</td>
            </tr>

            <tr>
                <td width="32.5%" align="center" style="border:1px solid #000; border-radius:8px; padding:2px; line-height:1.1; height:16px;">
                    <?= $biller->city ?>
                </td>
                <td width="17.5%" align="center" style="border:1px solid #000; border-radius:8px; padding:2px; line-height:1.1; height:16px;">المدينة:</td>
                <td width="32.5%" align="center" style="border:1px solid #000; border-radius:8px; padding:2px; line-height:1.1; height:16px;">
                    <?= $biller->address ?? '' ?>
                </td>
                <td width="17.5%" align="center" style="border:1px solid #000; border-radius:8px; padding:2px; line-height:1.1; height:16px;"> الحي:</td>
            </tr>

            <tr>
                <td width="32.5%" align="center" style="border:1px solid #000; border-radius:8px; padding:2px; line-height:1.1; height:16px;">
                    <?= $biller->building_number ?? '00000' ?>
                </td>
                <td width="17.5%" align="center" style="border:1px solid #000; border-radius:8px; padding:2px; line-height:1.1; height:16px;">رقم المبنى:</td>
                <td width="32.5%" align="center" style="border:1px solid #000; border-radius:8px; padding:2px; line-height:1.1; height:16px;">
                    <?= $biller->additional_number ?? '00000' ?>
                </td>
                <td width="17.5%" align="center" style="border:1px solid #000; border-radius:8px; padding:2px; line-height:1.1; height:16px;">رقم إضافي:</td>
            </tr>

            <tr>
                <td width="32.5%" align="center" style="border:1px solid #000; border-radius:8px; padding:2px; line-height:1.1; height:16px;">
                    <?= $biller->postal_code ?? '00000' ?>
                </td>
                <td width="17.5%" align="center" style="border:1px solid #000; border-radius:8px; padding:2px; line-height:1.1; height:16px;">الرمز البريدي:</td>
                <td width="32.5%" align="center" style="border:1px solid #000; border-radius:8px; padding:2px; line-height:1.1; height:16px;">
                    <?= $biller->gln ?? '00000' ?>
                </td>
                <td width="17.5%" align="center" style="border:1px solid #000; border-radius:8px; padding:2px; line-height:1.1; height:16px;">الرقم العالمي:</td>
            </tr>
        </table>
    </div>
</td>

</tr>
</table>


<!-- ITEMS TABLE -->
<table class="items-table">
<thead>
<tr>
    <th width="39%">
        <div class="box">
            <div class="box-content">
                <span> وصف </span>
                <span>Description</span>
            </div>
        </div>
    </th>
    <th width="10%">
        <div class="box">
            <div class="box-content">
                <span class="header-line">الكمية</span>
                <span class="header-line">السعر</span>
                <span>الإجمالي</span>
            </div>
        </div>
    </th>
    <th width="15%">
        <div class="box">
            <div class="box-content"> 
                <span class="header-line">خصم 1</span>
                <span class="header-line">خصم 2</span>
                <span>إجمالي الخصم</span>
            </div>
        </div>
    </th>
    <th width="16%">
        <div class="box">
            <div class="box-content">
                <span class="header-line">الكمية</span>
                <span class="header-line">السعر بعد الخصم</span>
                <span>الإجمالي بعد الخصم</span>
            </div>
        </div>
    </th>
    <th width="20%">
        <div class="box">
            <div class="box-content">
                <span class="header-line">نسبة الضريبة % | قيمة الضريبة</span>
                <span>الإجمالي</span>
            </div>
        </div>
    </th>
</tr>
</thead>

<tbody>
<?php foreach ($rows as $row): 
    ?>
<tr>
    <td>
        <div class="box">
            <div class="box-content">
                <span class="header-line" style="padding-top:25px;">
                    <?= $row->product_name ?>
                </span>
                <span style="margin-top:5px;">
                    <?= $row->product_code ?> | Lot: <?= $row->batch_no ?> | EXP: <?= $row->expiry ?>
                </span>
            </div>
        </div>
    </td>
    <td>
        <div class="box">
            <div class="box-content">
                <span class="header-line"><?= $this->sma->formatQuantity($row->unit_quantity) ?></span>
                <span class="header-line"><?= $this->sma->formatNumber($row->real_unit_price) ?></span>
                <span><?= $this->sma->formatNumber($row->subtotal) ?></span>
            </div>
        </div>
    </td>
    <td>
        <div class="box">
            <div class="box-content">
                <span class="header-line">
                    <?= $this->sma->formatNumber($row->discount1) ?> % | <?= $this->sma->formatNumber($row->item_discount) ?>
                </span>
                <span class="header-line">
                    <?= $this->sma->formatNumber($row->discount2) ?> % | <?= $this->sma->formatNumber($row->second_discount_value) ?>
                </span>
                <span>
                    <?= $this->sma->formatNumber($row->discount1 + $row->discount2) ?> % | <?= $this->sma->formatNumber($row->item_discount + $row->second_discount_value) ?>
                </span>
            </div>
        </div>
    </td>
    <td>
        <div class="box">
            <div class="box-content">
                <span class="header-line">
                <?= $this->sma->formatQuantity($row->unit_quantity) ?>
                </span>
                <span class="header-line">
                <?= $this->sma->formatNumber($row->net_unit_price) ?>
                </span>
                <span>
                <?= $this->sma->formatNumber($row->totalbeforevat) ?>
                </span>
            </div>
        </div>
    </td>
    <td>
        <div class="box">
            <div class="box-content">
                <span class="header-line">
                <?= $row->tax_rate ?> % | <?= $this->sma->formatNumber($row->item_tax) ?>
                </span>
                <span>
                <?= $this->sma->formatNumber($row->totalbeforevat + $row->item_tax) ?>
                </span>
            </div>
        </div>
    </td>
</tr>
<?php endforeach; ?>
</tbody>
</table>

<!-- TOTALS SECTION -->
<table class="table-main" style="margin-top:8px;">
<tr>
    <td width="65%"></td>
    <td width="35%">
        <div style="border:1px solid #000; border-radius:8px; padding:4px; line-height:1.1;">
            <table width="100%" cellpadding="0" cellspacing="0" style="border-collapse:separate; border-spacing:2px;">
                <tr>
                    
                    <td width="40%" align="center" style="border:1px solid #000; border-radius:6px; padding:3px; line-height:1.1; height:16px;">
                        <?= $this->sma->formatNumber($inv->total) ?>
                    </td>
                    <td width="60%" align="right" style="border:1px solid #000; border-radius:6px; padding:3px; line-height:1.1; height:16px; font-weight:bold;">
                        اجمالي قيمة الفاتورة:
                    </td>
                </tr>
                
                <tr>
                    
                    <td align="center" style="border:1px solid #000; border-radius:6px; padding:3px; line-height:1.1; height:16px;">
                        <?= $this->sma->formatNumber($inv->total_discount) ?>
                    </td>
                    <td align="right" style="border:1px solid #000; border-radius:6px; padding:3px; line-height:1.1; height:16px; font-weight:bold;">
                       اجمالي الخصومات:
                    </td>
                </tr>
                
                <tr>
                    
                    <td align="center" style="border:1px solid #000; border-radius:6px; padding:3px; line-height:1.1; height:16px;">
                        <?= $this->sma->formatNumber($inv->total - $inv->total_discount) ?>
                    </td>
                    <td align="right" style="border:1px solid #000; border-radius:6px; padding:3px; line-height:1.1; height:16px; font-weight:bold;">
                        الاجمالي (قبل الضريبة):
                    </td>
                </tr>
                
                <tr>
                    
                    <td align="center" style="border:1px solid #000; border-radius:6px; padding:3px; line-height:1.1; height:16px;">
                        <?= $this->sma->formatNumber($inv->total_tax) ?>
                    </td>
                    <td align="right" style="border:1px solid #000; border-radius:6px; padding:3px; line-height:1.1; height:16px; font-weight:bold;">
                        الضريبة المضافة:
                    </td>
                </tr>
                
                <tr>
                    
                    <td align="center" style="border:1px solid #000; border-radius:6px; padding:3px; line-height:1.1; height:18px; font-weight:bold; font-size:10px; background-color:#e8f4f8;">
                        <?= $this->sma->formatNumber($inv->grand_total) ?> SAR
                    </td>
                    <td align="right" style="border:1px solid #000; border-radius:6px; padding:3px; line-height:1.1; height:18px; font-weight:bold; font-size:10px; background-color:#e8f4f8;">
                        الصافي بعد الضريبة:
                    </td>
                </tr>
            </table>
        </div>
    </td>
</tr>
</table>

</div>
</body>
</html>
