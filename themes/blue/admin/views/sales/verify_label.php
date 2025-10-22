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
            $invoice_no    = $sale_id;
            $customer_name = $sale->customer;
            $region        = $customer->address;
            $region_no     = '2';
            $num_cartons   = $label->number_of_cartons; // change to how many labels you want
            $print_date    = '12/10/25 4:52 PM';

            if ($Settings->ksa_qrcode) {
                $qrtext = $this->inv_qrcode->base64([
                    'seller' => $biller->company && $biller->company != '-' ? $biller->company : $biller->name,
                    'vat_no' => $biller->vat_no,
                    'date' => $sale->date,
                    'grand_total' => $sale->grand_total,
                    'total_tax_amount' => $sale->total_tax,
                ]);
                $barcode_image = $this->sma->qrcode('text', $qrtext, 2);
            } else {
                $barcode_image = $this->sma->qrcode('link', urlencode(site_url('view/sale/' . $sale->hash)), 2);
            }
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
                                <td><img src="https://retaj.avenzur.com/assets/uploads/logos/<?= $billler->logo; ?>" alt="Logo"></td>
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
                                <td class="digit-box"><?= $label->refrigerated_items; ?></td>
                                <td class="spacer"></td>
                                <td class="spacer"></td>
                            </tr>
                        </table>
                    </div>

                    <div class="info-section-below">
                        <table>
                            <tr>
                                <td style="width: 100px; vertical-align: bottom;">
                                    <!--<img src="<?= $barcode_image ?>" width="80" height="80" alt="QR">-->
                                </td>
                                <td class="spacer"></td>
                                <td class="spacer"></td>
                                <td class="spacer"></td>
                                <td style="text-align: center; vertical-align: bottom;">
                                    <div class="region-label"><?= $region; ?></div>
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
        <div class="modal-footer d-flex justify-content-between">
            <div>
                <!--<a href="<?= admin_url('sales/label_verification/' . $sale_id); ?>" 
                class="btn btn-success">
                <i class="fa fa-check"></i> Verify
                </a>-->
                <?php if($sale->sale_status == 'added_label') { ?>
                <a href="#" class="btn btn-success po"
                    title="<b>Confirm Verification</b>"
                    data-content="
                        <p>Are you sure you want to verify this label?</p>
                        <a href='#' class='btn btn-info btn-sm po-confirm' data-url='<?= admin_url('sales/label_verification/'.$sale_id) ?>'>Yes, Verify</a>
                        <button class='btn btn-secondary btn-sm po-close'>No</button>">
                    <i class="fa fa-check"></i> Verify
                </a>

                <a href="#" class="btn btn-primary edit-label" data-id="<?= $sale_id ?>">
                    <i class="fa fa-edit"></i> Edit
                </a>
                <?php }else{
                    ?>
                    <a href="#" class="btn btn-primary print-label" data-id="<?= $sale_id ?>">
                        <i class="fa fa-edit"></i> Print
                    </a>
                <?php
                } ?>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Handle the AJAX verification click
    $(document).on('click', '.po-confirm', function(e) {
        e.preventDefault();
        const url = $(this).data('url');
        const $btn = $(this);

        $btn.prop('disabled', true).text('Verifying...');

        $.ajax({
            type: 'GET',
            url: url,
            success: function(res) {
                
                $('.po').popover('hide');
                $('#myModal').modal('hide');
                setTimeout(() => location.reload(), 800);
            },
            error: function() {
                
                $('.po').popover('hide');
                $btn.prop('disabled', false).text('Yes, Verify');
            }
        });
    });
});

$(document).on('click', '.edit-label', function (e) {
    e.preventDefault();
    var saleId = $(this).data('id');

    // Close current modal
    $('#myModal').modal('hide');

    setTimeout(function () {
        $.ajax({
            url: "<?= admin_url('sales/edit_label/') ?>" + saleId,
            type: "GET",
            success: function (response) {
                // Remove any previously loaded edit modal
                $('#editLabelModal').remove();

                // Wrap the response inside a proper Bootstrap modal
                const modalHtml = `
                    <div class="modal fade" id="editLabelModal" tabindex="-1" role="dialog" aria-hidden="true">
                        ${response}
                    </div>
                `;

                // Append and show
                $('body').append(modalHtml);
                $('#editLabelModal').modal({
                    backdrop: 'static',
                    keyboard: true
                }).modal('show');

                // Clean up when closed
                $('#editLabelModal').on('hidden.bs.modal', function () {
                    $(this).remove();
                });
            },
            error: function () {
                toastr.error('Failed to load edit form.');
            }
        });
    }, 300);
});

$(document).on('click', '.print-label', function(e) {
    e.preventDefault();

    var saleId = $(this).data('id');
    var url = "<?= admin_url('sales/pdf_new_label/') ?>" + saleId;

    // Close the current modal first
    $('#myModal').modal('hide');

    // Optional small delay to let the modal close smoothly
    setTimeout(function() {
        // Open the PDF link in a new tab (or trigger download)
        window.open(url, '_blank');
    }, 400);
});

</script>
