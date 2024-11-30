<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<script>
    
    function generatePDF() {
        const { jsPDF } = window.jspdf;
        const doc = new jsPDF({
            orientation: 'p',
            unit: 'pt',
            format: 'a4',
            putOnlyUsedFonts: true
        });

        // Load your logo from a URL (replace with the actual URL of your logo)
        const logoUrl = 'https://avenzur.com/assets/uploads/logos/avenzur-logov2-024.png'; // Replace with your logo URL

        // Define logo dimensions
        const logoWidth = 196; // Width of the logo in points
        const logoHeight = 36; // Height of the logo in points

        // Calculate the x-coordinate to center the logo
        const pageWidth = doc.internal.pageSize.width || doc.internal.pageSize.getWidth();
        const logoXPosition = (pageWidth - logoWidth) / 2;
        const logoYPosition = 20; // Y-position from the top

        // Load the image from the URL and add it to the PDF
        const image = new Image();
        image.src = logoUrl;
        image.onload = function() {
            doc.addImage(image, 'PNG', logoXPosition, logoYPosition, logoWidth, logoHeight);

            // Set starting position for further content below the logo
            const startingY = logoYPosition + logoHeight + 20; // Adjust space below the logo

            // Continue with other content, like adding text or HTML
            /*var elementText = document.querySelector('#pdfTextContent');
            var textContent = elementText.innerText || elementText.textContent;

            doc.setFont("Amiri-Regular"); // Set font if required
            doc.setFontSize(12);
            doc.text(textContent, 10, startingY);*/

            // Render HTML content below the text
            var elementHTML = document.querySelector('#print_content');
            doc.html(elementHTML, {
                callback: function (_doc) {
                    _doc.save('generated-document.pdf');
                },
                margin: [startingY + 10, 10, 10, 10], // Adjust top margin to account for the text
                x: 30,
                y: startingY + 20,
                width: 600,
                windowWidth: 675,
                html2canvas: {
                    useCORS: true,
                    allowTaint: true,
                    scale: 72 / 96
                }
            });
        };
    }

    resetValues();
    function resetValues(){
        if (localStorage.getItem('csdate')) {
            localStorage.removeItem('csdate');
            $('#csdate').val('');
        }

        if (localStorage.getItem('csref')) {
            localStorage.removeItem('csref');
            $('#csref').val('');
        }

        if (localStorage.getItem('cspayment')) {
            localStorage.removeItem('cspayment');
            $('#cspayment').val('');
        }

        if (localStorage.getItem('cscustomer')) {
            localStorage.removeItem('cscustomer');
            $('#cscustomer').val('');
        }

        if (localStorage.getItem('csledger')) {
            localStorage.removeItem('csledger');
            $('#csledger').val('');
        }

        if (localStorage.getItem('csnote')) {
            localStorage.removeItem('csnote');
            $('#csnote').val('');
        }
    }
</script>

<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-info-circle"></i><?= lang('customer_payments'); ?></h2>

        <div class="box-icon">
            <ul class="btn-tasks">
            <li class="dropdown"> 
                <a href="admin/entries/view/journal/<?= $payment_ref->journal_id ?>" class="tip" title="<?= lang('Veiw JL') ?>">
                    <i class="icon fa fa-eye"></i>
                </a>

                <a href="javascript:void(0);" onclick="generatePDF('print_content')" id="pdf" class="tip" title="<?= lang('download_PDF') ?>">
                    <i class="icon fa fa-file-pdf-o"></i>
                </a>
            </li>
            </ul>
        </div>
    </div>
    <div class="box-content" id="print_content">
        <div class="row">
            <?php
            $attrib = ['data-toggle' => 'validator', 'role' => 'form'];
            echo admin_form_open_multipart('customers/add_payment', $attrib)
            ?>
            <div class="col-lg-12">
                
                <div class="row">
                    <div class="col-lg-12">
                        <?php if ($Owner || $Admin) {
                            ?>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <?= lang('date', 'psdate'); ?>
                                        <?php echo form_input('date', ($payment_ref->date ?? ''), 'class="form-control input-tip date" id="psdate" readonly required="required"'); ?>
                                    </div>
                                </div>
                            <?php
                        } ?>

                        <div class="col-md-4">
                            <div class="form-group">
                                <?= lang('reference_no', 'psref'); ?>
                                <?php echo form_input('reference_no', ($payment_ref->reference_no ?? $payment_ref->reference_no), 'class="form-control input-tip" readonly id="psref"'); ?>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <?= lang('Payment Amount', 'pspayment'); ?>
                                <?php echo form_input('payment_total', ($payment_ref->amount ?? $_POST['payment_total']), 'class="form-control input-tip" readonly id="pspayment"'); ?>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <?= lang('supplier', 'pssupplier'); ?>
                                <?php echo form_input('supplier', ($payment_ref->name ?? $payment_ref->name), 'class="form-control input-tip" readonly id="pssupplier"'); ?>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <?= lang('Transfer From', 'psledger'); ?>
                                <?php echo form_input('ledger_account', ($payment_ref->transfer_from ?? $payment_ref->transfer_from), 'class="form-control input-tip" readonly id="psledger"'); ?>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <?= lang('Note', 'psnote'); ?>
                                <?php echo form_input('note', ($payment_ref->note ?? $payment_ref->note), 'class="form-control input-tip" readonly id="psnote"'); ?>
                            </div>
                        </div>

                    </div>



                    <div class="controls table-controls" style="font-size: 12px !important;">
                        <table id="poTable"
                                class="table items table-striped table-bordered table-condensed table-hover sortable_table">
                            <thead>
                            <tr>
                                <th>#</th>
                                <th><?php echo $this->lang->line('Date'); ?></th>
                                <th><?php echo $this->lang->line('Reference no') ?></th>
                                <th><?php echo $this->lang->line('Type') ?></th>
                                <th><?php echo $this->lang->line('Orig. Amt.') ?></th>
                                <th><?php echo $this->lang->line('Amt. Due.'); ?></th>
                                <th><?php echo $this->lang->line('Payment'); ?></th>
                            </tr>
                            </thead>
                            <tbody>
                                <?php 
                                    $count = 0;
                                    foreach($payments as $payment){
                                        $count++;
                                        ?>
                                        <tr>
                                            <td><?= $count; ?></td>
                                            <td><?= $payment->sale_id != '' ? $payment->sale_date: $payment->date; ?></td>
                                            <td><?= $payment->sale_id != '' ? $payment->ref_no: $payment->reference_no; ?></td>
                                            <td><?= $payment->sale_id != '' ? 'Invoice Payment' : 'Advance Collection'; ?></td>
                                            <td><?= $payment->grand_total > 0 ? number_format($payment->grand_total, 2) : '0.00'; ?></td>
                                            <td><?= ($payment->grand_total - $payment->amount) > 0 ? number_format(($payment->grand_total - $payment->amount), 2) : '0.00'; ?></td>
                                            <td><?= number_format($payment->amount, 2); ?></td>
                                        </tr>
                                        <?php
                                    }
                                ?>
                                
                            </tbody>
                            <tfoot></tfoot>
                        </table>
                    </div>
                
            </div>

            <?php echo form_close(); ?>

        </div>
    </div>
</div>

