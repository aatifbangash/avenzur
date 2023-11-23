<?php defined('BASEPATH') or exit('No direct script access allowed'); ?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $this->lang->line('sale') . ' ' . $inv->reference_no; ?></title>
    <link href="<?= $assets ?>styles/pdf/bootstrap.min.css" rel="stylesheet">
	  <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.1/dist/jquery.slim.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
   <link href="<?= $assets ?>styles/pdf/pdf.css" rel="stylesheet">
    <style>
tbody {
    border: 1px solid black;
}
td {
      font-size: 10px;
    border: 1px solid;
      padding-top:0px !important;
    padding-bottom:0px !important;
}
th {
      font-size: 10px;
    border: 1px solid;
      padding-top:0px !important;
    padding-bottom:0px !important;
}
.tab-head{
font-weight:bold;

}
.txt-col{
text-align:center;

}
td.tab-head {
    background: #efefef;
    font-size:10px;
    padding-top:0px !important;
    padding-bottom:0px !important;
}
th.tab-head {
    background: #efefef;
    font-size:10px;
     padding-top:0px !important;
    padding-bottom:0px !important;
}
table.table {
    margin: 0px !important;
}
.table>:not(caption)>*>* {
    padding: 0px 0px !important;
}

    </style>
</head>
<body>
<div id="wrap">

    <div class="container" style="padding:30px 30px;">
     
            <div class="row">
                <div class="col-xs-5">
				<img src="<?= base_url() . 'assets/uploads/logos/log.png' ?>" style="height:50px; width:200px;    margin-bottom: 30px;margin-top: 30px;">
                    
				<table  class="table" style="width:50%;">

							  <tr>
								<td class="tab-head">Invoice Number رقم الفاتورة</td>
								<td>   <?= $inv->reference_no; ?></td>
							
							  </tr>
							  <tr>
								<td class="tab-head">Invoice Date تاريخ الفاتورة</td>
								<td> <?= $this->sma->hrld($inv->date); ?></td>
								
							  </tr>
							  <tr>
								<td class="tab-head">DeliveryDate تاريخ التسليم او الوصول</td>
								<td>19-Dec-2022</td>
								
							  </tr>
							  <tr>
								<td class="tab-head">Due Date تاريخ الاستحقاق</td>
				
                        <td>
                        <?php 
                        
                            echo $this->sma->hrsd($inv->due_date);
                         ?>
							</td>
								
							  </tr>
						
							  <tr>
							 
								<td class="tab-head">Sales Rep مندول المبيعات</td>
								<td>	   <?php if (!empty($inv->return_sale_ref)) {
    
                            echo lang('return_ref') . ': ' . $inv->return_sale_ref . '<br>';
                        } ?>
							</td>
								
							  </tr>
							  <tr>
								<td class="tab-head"> P.O. No. رقم أمر الشراء</td>
								<td>1168115</td>
								
							  </tr>
							  <tr>
								<td class="tab-head">PO Date رقم أمر الشراء</td>
								<td> 18-DEC-2022</td>
								
							  </tr>
							 
					</table>
                </div>
                <div class="col-xs-3">
                   
                       
						<img src="<?= base_url() . 'assets/uploads/logos/log2.png' ?>" height="100px" width="150px" style="padding:10px 10px;margin-left:400px;">
						 <!--<h3 style="padding:50px 10px; ">Tax Invoice</h3>-->
						<img src="<?= base_url() . 'assets/uploads/logos/download.svg' ?>" height="100px" width="100px" style="padding:10px 10px;margin-left:400px;">
                        <img src="<?= base_url() . 'assets/uploads/logos/avenzur-logov2-024.png' ?>" height="50px" width="120px" style="margin-left:550px;margin-top:-100px;">
                    
                </div>
				  <div class="col-xs-4">

                    <img src="<?= base_url() . 'assets/uploads/logos/qrcodee.png' ?>" height="100px" width="100px" style="margin-top:60px;margin-left:600px;">
                       
                  
                </div>
            </div>
    



			<div class="row">
				<div class="col-xs-6">

				<table class="table">
						<tbody>
								<tr>
									<td colspan="2" class="txt-col" style="font-weight:bold;">Seller الباعة</td>
									<td colspan="2" class="txt-col" style="font-weight:bold;">Customer Name اسم العميل</td>
								</tr>
								<tr>
									<td colspan="2" class="txt-col"><?= $customer->company && $customer->company != '-' ? $customer->company : $customer->name; ?> شركة مخزن الأدوية فارما</td>
									<td colspan="2" class="txt-col"><?= $biller->company && $biller->company != '-' ? $biller->company : $biller->name; ?> شركة النهدي الطبية</td>
								</tr>
							  <tr>
								<td class="tab-head">VAT NO </td>
								<td><?php echo $customer->vat_no; ?></td>
								<td class="tab-head">VAT NO</td>
								<td><?php echo $biller->vat_no; ?></td>
							
							  </tr>
							  <tr>
								<td class="tab-head">Country-City البلد المدينة </td>
								<td> <?php
                        echo $customer->address . '<br />' . $customer->city . ' ' . $customer->postal_code . ' ' . $customer->state . '<br />' . $customer->country;
                        echo '<p>'; ?> المملكة العربية السعودية الرياض</td> 
								<td class="tab-head">Country-City  البلد المدينة</td>
								<td> <?php
                        echo $biller->address . '<br />' . $biller->city . ' ' . $biller->postal_code . ' ' . $biller->state . '<br />' . $biller->country;
                        echo '<p>'; ?> المملكة العربية السعودية الرياض</td>
								
							  </tr>
							  <tr>
								<td class="tab-head">Address العنوان</td>
								<td><?php echo $customer->address; ?></td>
								<td class="tab-head">Address العنوان</td>
								<td><?php echo $biller->address; ?></td>
								
							  </tr>
							  <tr>
								<td class="tab-head">Building & Street البناء والشارع</td>
								<td><?php  echo $customer->city; ?></td>
								<td class="tab-head">Building # Street name البناء والشارع</td>
								<td><?php  echo $biller->city; ?></td>
								
							  </tr>
							  <tr>
								<td class="tab-head">Postal code الرمز البريدي</td>
								<td><?php echo $customer->postal_code; ?></td>
								<td class="tab-head">Postal Code الرمز البريدي</td>
								<td><?php echo $biller->postal_code; ?></td>
								
							  </tr>
							  <tr>
								<td class="tab-head"> C.R.</td>
								<td>1010160412</td>
								<td class="tab-head">C.R</td>
								<td>1010160412</td>
								
							  </tr>
							  <tr>
								<td class="tab-head">Phone الهاتف</td>
								<td><?php echo $customer->phone; ?></td>
								<td class="tab-head">Phone الهاتف</td>
									<td><?php echo $biller->phone; ?></td>
								
							  </tr>
								<tr>
								<td class="tab-head">E-mail البريد الإلكتروني</td>
								<td> <?php  echo  $customer->email; ?></td>
								<td class="tab-head">E-mail البريد الإلكتروني</td>
									<td> <?php  echo  $biller->email; ?></td>
								
							  </tr>
							 
								
						</tbody>
							 
					</table>
				
					<table class="table">
						<thead>
						  <tr>
							<th  class="tab-head">Description الوصف</th>
							<th  class="tab-head" >Qty الكمية</th>
							<th  class="tab-head">UP</th>
							<th  class="tab-head">Batch الدفعة</th>
							<th  class="tab-head"> VAT% ضريبة القيمة المضافة</th>
							<th  class="tab-head">Total المجموع</th>
						  </tr>
						</thead>
						<tbody>
						    <?php
                    $r   = 1;
                    $qty = 0;
                    foreach ($rows as $row) { ?>
						  <tr>
							<td> <?= $row->product_code . ' - ' . $row->product_name . ($row->variant ? ' (' . $row->variant . ')' : ''); ?>
                                    <?= $row->second_name ? '<br>' . $row->second_name : ''; ?>
                                    <?= $row->details ? '<br>' . $row->details : ''; ?>
                                    <?= $row->serial_no ? '<br>' . $row->serial_no : ''; ?>
							<span class="dis">Discount</span>
						
						
							</td>
							<td><?= $this->sma->formatQuantity($row->quantity) . ' ' . $row->base_unit_code; ?> <br>
							<br>
							<br>
							<span class="dis"></span>
							
							</td>
							<td><?= $this->sma->formatMoney($row->unit_price); ?><br>
							<br><br>
							<br>
							<span class="dis"></span>
							
							
							</td>
							<td>vt276 <br>
							<br><br>
							<br>
							<span class="dis"></span>
							</td>
							<td>0% <br>
							<br><br>
							
							<span class="dis">0%</span></td>
							<td><?= $this->sma->formatMoney($row->subtotal); ?><br>
							<br><br>
							
							<span class="dis"><?php $this->sma->formatMoney($row->item_tax); ?></span></td>
						  </tr>
						<tr>
						<td colspan="2">
						
						
						</td>
						
						<td colspan="3"  class="tab-head">Total without VAT   المجموع بدون ضريبة القيمة المضافة</td>
							<td>SR <?= $this->sma->formatMoney($row->subtotal); ?></td>
							
						</tr>
								<tr>
						<td colspan="2">
						
						
						</td>
						
						<td colspan="3"  class="tab-head"> VAT ضريبة القيمة المضافة</td>
							<td>SR 0</td>
							
						</tr>
							<tr>
						<td colspan="2">
						
						
						</td>
						
						<td colspan="3" class="tab-head">Total with VAT المجموع مع ضريبة القيمة المضافة</td>
							<td>SR <?= $this->sma->formatMoney($row->subtotal); ?></td>
							
						</tr>
						 <?php
                                $r++;
                        
                    }
                    ?>
						</tbody>
					</table>
					
					
                </div>


			</div>

	</div>     
</div>
</body>
</html>