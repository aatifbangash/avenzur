<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<title>Invoice Layout</title>
<style>
  body {
    font-family: Arial, sans-serif;
    margin: 0;
    padding: 20px;
    background: #f5f5f5;
  }
  .page {
    width: 850px;
    margin: 0 auto 40px auto;
    background: #fff;
    padding: 30px;
  }

  /* --- HEADER SECTION --- */
  .top-section {
    display: flex;
    justify-content: space-between;
    align-items: center;
  }
  .qr-box {
    width: 120px;
    height: 120px;
    background: #e0e0e0;
    border-radius: 10px;
  }
  .center-title-box {
    text-align: center;
  }
  .rounded-title {
    display: inline-block;
    padding: 10px 25px;
    border: 2px solid #000;
    border-radius: 20px;
    font-size: 22px;
    font-weight: bold;
    margin-bottom: 10px;
  }
  .invoice-details-box {
    border: 2px solid #000;
    border-radius: 15px;
    padding: 10px 20px;
    margin-top: 10px;
    display: inline-block;
  }

  /* --- SUPPLIER / CUSTOMER SECTION --- */
  .two-box-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
    margin-top: 25px;
  }
  .big-rounded-box {
    border: 1px solid #000;
    border-radius: 20px;
    padding: 20px;
  }
  .big-title {
    font-size: 18px;
    font-weight: bold;
    margin-bottom: 15px;
    text-align: center;
  }

  .inner-field {
    display: flex;
    margin-bottom: 10px;
  }
  .inner-label {
    width: 150px;
    padding: 6px;
    border: 1px solid #000;
    border-radius: 10px;
    font-weight: bold;
    margin-right: 10px;
  }
  .inner-label-description{
    /* width: 150px; */
    padding: 6px;
    border: 1px solid #000;
    border-radius: 10px;
    font-weight: bold;
    padding: 27px;
    /* margin-right: 10px; */
  }
  .inner-value {
    flex: 1;
    padding: 6px;
    border: 1px solid #000;
    border-radius: 10px;
  }

  /* TABLE */
  table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 25px;
    font-size: 12px;
  }
  table th, table td {
    border: none;
    /* padding: 4px; */
    text-align: center;
  }
</style>
</head>
<body>

<!-- PAGE 1 -->
<div class="page">

  <!-- Top Section -->
  <div class="top-section">
    <div class="qr-box">QR</div>

    <div class="center-title-box">
      <div class="rounded-title">Tax Invoice</div>

      <div >
        Invoice No: 0001<br>
        Date: 28/01/2025
      </div>
    </div>

    <div style="width:120px"></div>
  </div>

  <!-- Supplier + Customer Boxes -->
  <div class="two-box-grid">

    <div class="big-rounded-box">
      <div class="big-title">Supplier</div>

      <div class="inner-field">
        <div class="inner-label">Name</div>
        <div class="inner-value">Rawabi Business Medical</div>
      </div>

      <div class="inner-field">
        <div class="inner-label">CR</div>
        <div class="inner-value">4030372922</div>
      </div>

      <div class="inner-field">
        <div class="inner-label">VAT</div>
        <div class="inner-value">311392835500003</div>
      </div>

      <div class="inner-field">
        <div class="inner-label">City</div>
        <div class="inner-value">Jeddah</div>
      </div>
    </div>

    <div class="big-rounded-box">
      <div class="big-title">Customer</div>

      <div class="inner-field">
        <div class="inner-label">Name</div>
        <div class="inner-value">Pharmacy</div>
      </div>

      <div class="inner-field">
        <div class="inner-label">CR</div>
        <div class="inner-value">5906037541</div>
      </div>

      <div class="inner-field">
        <div class="inner-label">VAT</div>
        <div class="inner-value">300732535400003</div>
      </div>

      <div class="inner-field">
        <div class="inner-label">City</div>
        <div class="inner-value">Al-Mazja</div>
      </div>
    </div>

  </div>

  <!-- ITEMS TABLE -->
  <table>
    <thead>
      <tr>
        <!-- Description -->
        <th >
          <div  class="inner-label-description" style="border-radius:8px; text-align:center;">Description</div>
        </th>

        <!-- Qty / Price / Total -->
        <th style="width:15%">
          <div style="border:1px solid #000; border-radius:10px; overflow:hidden;">
            <div style="padding:4px; border-bottom:1px solid #000;">Qty</div>
            <div style="padding:4px; border-bottom:1px solid #000;">Price</div>
            <div style="padding:4px;">Total</div>
          </div>
        </th>

        <!-- DIS1 / DIS2 / Total Discount -->
        <th style="width:15%">
          <div style="border:1px solid #000; border-radius:10px; overflow:hidden;">
            <div style="padding:4px; border-bottom:1px solid #000;">Dis 1</div>
            <div style="padding:4px; border-bottom:1px solid #000;">Dis 2</div>
            <div style="padding:4px;">Total Dis</div>
          </div>
        </th>

        <!-- Qty / Price After VAT / Total After VAT -->
        <th style="width:15%">
          <div style="border:1px solid #000; border-radius:10px; overflow:hidden;">
            <div style="padding:4px; border-bottom:1px solid #000;">Qty</div>
            <div style="padding:4px; border-bottom:1px solid #000;">Price After VAT</div>
            <div style="padding:4px;">Total After VAT</div>
          </div>
        </th>

        <!-- VAT% + VAT Value + Total (VAT% + Value horizontal, Total below) -->
        <th style="width:15%">
          <div style="border:1px solid #000; border-radius:10px; overflow:hidden;">
            <div style="display:flex; border-bottom:1px solid #000;">
              <div style="flex:1; padding:4px; border-right:1px solid #000;">VAT %</div>
              <div style="flex:1; padding:4px;">VAT Value</div>
            </div>
            <div style="padding:4px;">Total</div>
          </div>
        </th>
      </tr>
    </thead>

    <tbody>
      <?php for($i = 1; $i <= 5; $i++ ) {?>
    <tr>
        <!-- Description -->
        <td >
          <div  class="inner-label-description" style="border-radius:8px; text-align:center;">Nervamine</div>
        </td>

        <!-- Qty / Price / Total -->
        <td style="width:15%">
          <div style="border:1px solid #000; border-radius:10px; overflow:hidden;">
            <div style="padding:4px; border-bottom:1px solid #000;">100</div>
            <div style="padding:4px; border-bottom:1px solid #000;">75.00</div>
            <div style="padding:4px;">7,500.00</div>
          </div>
        </td>

        <!-- DIS1 / DIS2 / Total Discount -->
        <td style="width:15%">
          <div style="border:1px solid #000; border-radius:10px; overflow:hidden;">
            <div style="padding:4px; border-bottom:1px solid #000;">5 % | 375.00</div>
            <div style="padding:4px; border-bottom:1px solid #000;">2 % | 150.00</div>
            <div style="padding:4px;">7 % | 525.00</div>
          </div>
        </td>

        <!-- Qty / Price After VAT / Total After VAT -->
        <td style="width:15%">
          <div style="border:1px solid #000; border-radius:10px; overflow:hidden;">
            <div style="padding:4px; border-bottom:1px solid #000;">100.00</div>
            <div style="padding:4px; border-bottom:1px solid #000;">69.75</div>
            <div style="padding:4px;">6,975.00</div>
          </div>
        </td>

        <!-- VAT% + VAT Value + Total (VAT% + Value horizontal, Total below) -->
        <td style="width:15%">
          <div style="border:1px solid #000; border-radius:10px; overflow:hidden; padding: 10px">
            <div style="display:flex; border-bottom:1px solid #000;">
              <div style="flex:1; padding:4px; border-right:1px solid #000;">15 % </div>
              <div style="flex:1; padding:4px;">1,046.25</div>
            </div>
            <div style="padding:4px;">8,021.25</div>
          </div>
        </td>
      </tr>
      <?php }?>
    </tbody>
  </table>
</div>

</body>
</html>
