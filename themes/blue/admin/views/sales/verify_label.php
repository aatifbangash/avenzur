<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="modal-dialog modal-lg">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i></button>
            <h4 class="modal-title">Label Preview</h4>
        </div>
        <div class="modal-body">
            <?php
            // ----- CONSTANT TEST DATA -----
            $invoice_no    = '37412';
            $customer_name = 'Maimonah Alkhair Medical Pharmacy - Makkah';
            $region        = 'Makkah';
            $region_no     = '2';
            $num_cartons   = 3; // change to how many labels you want
            $print_date    = '12/10/25 4:52 PM';
            $barcode_image = 'https://placehold.co/80x80/png';
            ?>

            <style>
            .label-container {
                background: #2a2a2a;
                padding: 20px;
                border-radius: 4px;
                max-height: 500px;
                overflow-y: auto;
            }
            .ticket {
                width: 100%;
                max-width: 650px;
                height: 430px;
                background: white;
                padding: 20px;
                position: relative;
                direction: rtl;
                margin: 15px auto;
                box-shadow: 0 0 10px rgba(0,0,0,0.3);
            }
            .header {
                width: 100%;
                margin-bottom: 15px;
            }
            .header table {
                width: 100%;
                border-collapse: collapse;
            }
            .logo {
                width: 60px;
                height: 60px;
                background: #1a1a1a;
            }
            .ticket-number {
                font-size: 36px;
                font-weight: bold;
                letter-spacing: 4px;
                background-color: #1a1a1a;
                color: white;
                padding: 4px 8px;
                text-align: center;
            }
            .info-section {
                margin: 20px 0;
                text-align: center;
            }
            .info-section-below {
                margin: 20px 0;
                width: 100%;
            }
            .info-section-below table {
                width: 100%;
                border-collapse: collapse;
            }
            .value {
                font-weight: bold;
                font-size: 28px;
                text-align: center;
            }
            .big-display {
                margin: 10px 0;
                text-align: center;
            }
            .big-display table {
                margin: 0 auto;
                border-collapse: collapse;
            }
            .digit-box {
                width: 80px;
                height: 40px;
                background: #1a1a1a;
                color: white;
                font-size: 30px;
                font-weight: bold;
                text-align: center;
                vertical-align: middle;
                padding: 5px;
            }
            .digit-box-text {
                color: black;
                font-size: 30px;
                font-weight: bold;
                text-align: center;
                vertical-align: middle;
                padding: 5px 10px;
            }
            .of-label {
                font-size: 24px;
                font-weight: bold;
                text-align: center;
                vertical-align: middle;
                padding: 0 10px;
            }
            .qr-code {
                width: 60px;
                height: 60px;
                background: #1a1a1a;
                color: white;
                font-size: 8px;
                text-align: center;
                vertical-align: middle;
                margin: 0 0 0 40px;
            }
            .datetime {
                text-align: center;
                font-size: 12px;
            }
            .region-label {
                font-size: 22px;
                text-align: center;
            }
            .spacer {
                width: 68px;
            }
            </style>

            <div class="label-container">
                <?php for ($i = 1; $i <= $num_cartons; $i++): ?>
                <div class="ticket">
                    <div class="header">
                        <table>
                            <tr>
                                <td style="width: 60px;"></td>
                                <td style="text-align: center;">
                                    <div class="ticket-number"><?= $invoice_no ?></div>
                                </td>
                                <td style="width: 60px; margin-right: 20px;"></td>
                                <td><img src="https://placehold.co/100x40/png" alt="Logo"></td>
                            </tr>
                        </table>
                    </div>

                    <div class="info-section">
                        <div class="value"><?= htmlspecialchars($customer_name) ?></div>
                    </div>

                    <div class="big-display">
                        <table>
                            <tr>
                                <td class="digit-box-text">عدد كرتون</td>
                                <td class="digit-box"><?= $i ?></td>
                                <td class="of-label">OF</td>
                                <td class="digit-box"><?= $num_cartons ?></td>
                            </tr>
                        </table>
                    </div>

                    <div class="big-display" style="margin-right:0px !important">
                        <table>
                            <tr>
                                <td class="digit-box-text">ربطة ثلاجة</td>
                                <td class="digit-box">0</td>
                                <td class="spacer"></td>
                                <td class="spacer"></td>
                            </tr>
                        </table>
                    </div>

                    <div class="info-section-below">
                        <table>
                            <tr>
                                <td style="width: 100px; vertical-align: bottom;">
                                    <img src="<?= $barcode_image ?>" width="80" height="80" alt="QR">
                                </td>
                                <td class="spacer"></td>
                                <td class="spacer"></td>
                                <td class="spacer"></td>
                                <td style="text-align: center; vertical-align: bottom;">
                                    <div class="region-label"><?= $region . ' ' . $region_no ?></div>
                                    <div class="datetime"><?= $print_date ?></div>
                                </td>
                                <td style="width: 100px;"></td>
                            </tr>
                        </table>
                    </div>
                </div>
                <?php endfor; ?>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        </div>
    </div>
</div>