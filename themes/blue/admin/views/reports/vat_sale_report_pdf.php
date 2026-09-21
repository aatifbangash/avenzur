<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $this->lang->line('vat_sale_report') . ' ' ; ?></title>
    <link href="<?= $assets ?>styles/pdf/pdf.css" rel="stylesheet">
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
        }

        tbody {
            border: 1px solid black;
        }

        td {
            font-size: 13px;
            border: 1px solid;
            padding: 5px !important;
        }

        th {
            font-size: 13px;
            border: 1px solid;
            padding: 5px !important;

        }

        .header {
            text-align: center;
            margin-bottom: 20px;
        }

        .content {
            margin: 20px;
        }

        .footer {
            text-align: center;
            margin-top: 20px;
        }

        .container {
            width: 100%;
        }

        .row {
            display: flex;
            width: 100%;
            font-size: 13px;
        }

        .col-half {
            width: 40%;
            padding: 3px;
            float: left;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .text-left {
            text-align: left;
        }

        .bold {
            font-weight: bold;
        }

        /* .well { border: 1px solid #ddd; padding: 10px; border-radius: 5px; background-color: #f7f7f7; } */
        .well {
            border: 1px solid #ddd;
            background-color: #f6f6f6;
            box-shadow: none;
            border-radius: 0px;
            font-size: 13px;
            height: auto;
        }

        .clearfix {
            clear: both;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .table th,
        .table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }

        .table th {
            background-color: #f2f2f2;
        }

        .table thead {
            background-color: #007bff;
            color: #fff;
        }
    </style>
</head>

<body>  <div class="container"> 
                <div class="row">
                    <div class="controls table-controls" style="font-size: 12px !important;">
                        <table id="poTable"
                                class="table items table-striped table-bordered table-condensed table-hover sortable_table">
                            <thead>
                            <tr>
                                <th>SR</th>
                                <th><?= lang('Trx Type'); ?></th>
                                <th><?= lang('Branch'); ?></th>
                                <th><?= lang('INV. NO'); ?></th>
                                <th><?= lang('INV DATE.'); ?></th>

                                <th><?= lang('TOTAL INV.')//lang('Total Before Discount.'); ?></th>
                                <th><?= lang('T.DIS')//lang('Total Discount.'); ?></th>
                                <th><?= lang('T.AFTER DIS')//lang('Total After Discount.'); ?></th>

                                <th><?= lang('15% VAT VALUE')//lang('Total Items with VAT.'); ?></th>
                                <th><?= lang('0% VAT VALUE')//lang('Total Items Zero Vat.'); ?></th>

                                <!-- <th><?= lang('Total Purchases Value'); ?></th> -->
                                <th><?= lang('VAT Amount')//lang('VAT on Purchases'); ?></th>
                                <th><?= lang('NET INV.')//lang('Total with VAT'); ?></th>

                                

                                <!-- <th><?= lang('Legal No.'); ?></th>
                                <th><?= lang('Vendor Code'); ?></th> -->
                                <th><?= lang('CUSTOMER NAME'); ?></th>
                                <th><?= lang('CUSTOMER VAT NO.'); ?></th>
                                <th><?= lang('G/L NO.'); ?></th>
                                
                                <!-- <th><?= lang('Qty'); ?></th> -->
                                <!-- <th><?= lang('Tax'); ?></th> -->
                              
                            </tr>
                            </thead>
                            <tbody style="text-align:center;">
                                <?php
                                    $count = 1;
                                    $totalQty = 0;
                                    $totalTax = 0;
                                    $totalWithoutTax = 0;
                                    $totalWithTax = 0;

                                    $totalTotalBeforeDiscount = 0;
                                    $totalTotalDiscount = 0;
                                    $totalTotalAfterDiscount = 0;


                                    $totalItemWithVAT = 0;
                                    $totalItemWithZeroVAT = 0;

                                    $totalWithTax = 0;
                                    // echo "<pre>";
                                    // print_r($vat_purchase);
                                    foreach ($vat_purchase as $data){
                                        
                                        $rowClass = '';
                                        $sign = "";
                                        
                                        if($data->trans_type == "returnCustomer"){
                                            $rowClass = 'oreturn_link';
                                            $sign = "";
                                        }else if($data->trans_type == "sale"){
                                            $rowClass = 'invoice_link';
                                        }


                                        $totalTax += $sign.$data->total_tax;
                                        $totalWithoutTax += $sign.($data->grand_total - $data->total_tax);
                                        $totalWithTax += $sign.$data->grand_total;

                                        $totalTotalBeforeDiscount += $sign.($data->grand_total + $data->total_discount);
                                        $totalTotalDiscount += $sign.$data->total_discount;
                                        $totalTotalAfterDiscount += $sign.$data->grand_total;


                                        $totalItemWithVAT += $data->total_item_with_vat;
                                        $totalItemWithOutVAT += $data->total_item_without_tax;

                                        

                                        ?>
                                            <tr id="<?= $data->trans_ID; ?>" class="<?=$rowClass;?>">
                                                <td><?= $data->trans_ID; ?></td>
                                                <td><?=$data->trans_type?></td>
                                                <td><?= $data->warehouse; ?></td>
                                                <td><?= $data->reference_no; ?></td>
                                                <td><?= $data->trans_date; ?></td>
                                                
                                                <td><?= $sign.$this->sma->formatMoney($data->grand_total+$data->total_discount,'none'); ?></td>
                                                <td><?= $sign.$this->sma->formatMoney($data->total_discount,'none'); ?></td>
                                                <td><?= $sign.$this->sma->formatMoney($data->grand_total,'none'); ?></td>

                                                <td><?= $sign.$this->sma->formatMoney($data->total_item_with_vat,'none'); ?></td>
                                                <td><?= $sign.$this->sma->formatMoney($data->total_item_without_tax,'none'); ?></td>


                                                <!-- <td><?= $sign.$this->sma->formatMoney($data->grand_total - $data->total_tax,'none'); ?></td> -->
                                                <td><?= $sign.$this->sma->formatMoney($data->total_tax,'none'); ?></td>
                                                <td><?= $sign.$this->sma->formatMoney($data->grand_total,'none'); ?></td>



                                                <td><?= $data->customer_name; ?></td>
                                                <td><?= $data->customer_vat_no; ?></td>
                                                <td><?= $data->ledger_entry_number; ?></td>
                                                
                                                
                                            </tr>
                                        <?php
                                        $count++;
                                    }
                                ?>
                                
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th>&nbsp;</th>
                                    <th>&nbsp;</th>
                                    <th>&nbsp;</th>
                                    <th>&nbsp;</th>
                                    <th>&nbsp;</th>

                                    <th class="text-center"><?=$this->sma->formatMoney($totalTotalBeforeDiscount,'none')?></th>
                                    <th class="text-center"><?=$this->sma->formatMoney($totalTotalDiscount,'none')?></th>
                                    <th class="text-center"><?=$this->sma->formatMoney($totalTotalAfterDiscount,'none')?></th>


                                    <th class="text-center"><?=$this->sma->formatMoney($totalItemWithVAT,'none')?></th>
                                    <th class="text-center"><?=$this->sma->formatMoney($totalItemWithOutVAT,'none')?></th>

                                    <!-- <th class="text-center"><?= $this->sma->formatMoney($totalWithoutTax,'none'); ?></th> -->
                                    <th class="text-center"><?= $this->sma->formatMoney($totalTax,'none'); ?></th>
                                    <th class="text-center"><?= $this->sma->formatMoney($totalWithTax,'none'); ?></th>



                                    <th>&nbsp;</th>
                                    <th>&nbsp;</th>
                                    <th>&nbsp;</th>
                                    <!-- <th>&nbsp;</th> -->
                                    <!-- <th>&nbsp;</th>
                                    <th>&nbsp;</th> -->
                                    <!-- <th class="text-center"><?= $this->sma->formatQuantity($totalQty); ?></th> -->
                                    <!-- <th>&nbsp;</th> -->
                                   
                                </tr>
                            </tfoot>
                        </table>
                    </div> 
            </div>

       
</body>
</html>