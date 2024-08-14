<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"
    integrity="sha512-GsLlZN/3F2ErC5ifS5QtgpiJtWd43JWSuIgh7mbzZ8zBps+dvLusV+eNQATqgA/HdeKFVgA5v3S/cIrLF7QnIg=="
    crossorigin="anonymous" referrerpolicy="no-referrer"></script>

<script type="text/javascript">
    $(document).ready(function () {

        var entryId = 0;
        $("button#send").click(function () {
            $(".modal-body").hide();
            $(".modal-footer").hide();
            $(".modal-ajax").show();
            $.ajax({
                type: "POST",
                url: '<?php echo admin_url("entries/email"); ?>/' + entryId,
                data: $('form#emailSubmit').serialize(),
                success: function (response) {
                    msg = JSON.parse(response); console.log(msg);
                    if (msg['status'] == 'success') {
                        $(".modal-error-msg").html("");
                        $(".modal-error-msg").hide();
                        $(".modal-body").show();
                        $(".modal-footer").show();
                        $(".modal-ajax").hide();
                        $("#emailModal").modal('hide');
                    } else {
                        $(".modal-error-msg").html(msg['msg']);
                        $(".modal-error-msg").show();
                        $(".modal-body").show();
                        $(".modal-footer").show();
                        $(".modal-ajax").hide();
                    }
                },
                error: function () {
                    $(".modal-error-msg").html("<?= lang('entries_views_views_email_not_sent_msg') ?>");
                    $(".error-msg").show();
                    $(".modal-body").show();
                    $(".modal-footer").show();
                    $(".modal-ajax").hide();
                }
            });
        });

        $('#emailModal').on('show.bs.modal', function (e) {
            $(".modal-error-msg").html("");
            $(".modal-ajax").hide();
            $(".modal-error-msg").hide();
            entryId = $(e.relatedTarget).data('id');
            var entryType = $(e.relatedTarget).data('type');
            var entryNumber = $(e.relatedTarget).data('number');
            $("#emailModelType").html(entryType);
            $("#emailModelNumber").html(entryNumber);
        });
    });

</script>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-barcode"></i><?= lang('entries_views_views_title') ?>
        </h2>

        <div class="box-icon">

        </div>
    </div>
    <div class="box-content">

        <div class="row">
            <div class="col-xs-12">
                <div class="box">

                    <!-- /.box-header -->
                    <div class="box-body" style="padding:5px">
                        <div>
                            <div>
                                <div id="pdfTextContent">
                                    <?php
                                    echo (lang('Entry_Id')) . ' : ' . ($entry['id']) . '<br>';
                                    if ($entry['pid'] > 0) {
                                        echo (lang('Supplier_name')) . ' : ' . ($supplier['name']) . '<br>';
                                        echo (lang('Supplier_Sequence_code')) . ' : ' . ($supplier['sequence_code']) . '<br>';

                                        echo (lang('Purchase_id')) . ' : ' . ($purchase['id']) . '<br>';
                                        echo (lang('invoice_number')) . ' : ' . ($purchase['invoice_number']) . '<br>';
                                        echo (lang('sequence_code')) . ' : ' . ($purchase['sequence_code']) . '<br>';
                                    }
                                    if ($entry['sid'] > 0) {

                                        echo (lang('Sale_id')) . ' : ' . ($sales['id']) . '<br>';
                                        echo (lang('invoice_number')) . ' : ' . ($sales['invoice_number']) . '<br>';
                                        echo (lang('sequence_code')) . ' : ' . ($sales['sequence_code']) . '<br>';
                                        //   echo (lang('Customer')) . ' : ' . ($customer['name']) .'<br>';
                                        echo (lang('Customer')) . ' : ' . ($sales['customer']) . '<br>';
                                    }

                                    if ($entry['tid'] > 0) {
                                        echo (lang('Transfer_ID')) . ' : ' . ($entry['tid']) . '<br>';
                                        echo (lang('invoice_number')) . ' : ' . ($transfer['invoice_number']) . '<br>';
                                        echo (lang('sequence_code')) . ' : ' . ($transfer['sequence_code']) . '<br>';
                                        echo (lang('transfer_no')) . ' : ' . ($transfer['transfer_no']) . '<br>';
                                    }

                                    //echo (lang('entries_views_views_label_number')) . ' : ' . ($this->functionscore->toEntryNumber($entry['number'], $entry['entrytype_id']));
                                    //echo '<br /><br />';
                                    echo (lang('entries_views_views_label_date')) . ' : ' . ($this->functionscore->dateFromSql($entry['date']));
                                    echo '<br /><br /> </div>';

                                    echo '<div id="pdfHtmlContent"><table  class="table table-striped">';

                                    /* Header */
                                    echo '<tr>';
                                    if ($this->mSettings->drcr_toby == 'toby') {
                                        echo '<th>' . lang('entries_views_views_th_to_by') . '</th>';
                                    } else {
                                        echo '<th>' . lang('entries_views_views_th_dr_cr') . '</th>';
                                    }
                                    echo '<th>' . lang('entries_views_views_th_ledger') . '</th>';
                                    echo '<th>' . lang('entries_views_views_th_dr_amount') . ' (' . $this->mAccountSettings->currency_symbol . ')' . '</th>';
                                    echo '<th>' . lang('entries_views_views_th_cr_amount') . ' (' . $this->mAccountSettings->currency_symbol . ')' . '</th>';
                                    echo '<th>' . lang('entries_views_views_th_narration') . '</th>';
                                    echo '</tr>';

                                    /* Intial rows */
                                    foreach ($curEntryitems as $row => $entryitem) {
                                        echo '<tr>';

                                        echo '<td>';
                                        if ($this->mSettings->drcr_toby == 'toby') {
                                            if ($entryitem['dc'] == 'D') {
                                                echo lang('entries_views_views_toby_D');
                                            } else {
                                                echo lang('entries_views_views_toby_C');
                                            }
                                        } else {
                                            if ($entryitem['dc'] == 'D') {
                                                echo lang('entries_views_views_drcr_D');
                                            } else {
                                                echo lang('entries_views_views_drcr_C');
                                            }
                                        }
                                        echo '</td>';

                                        echo '<td>';
                                        echo ($entryitem['ledger_name']);
                                        echo '</td>';

                                        echo '<td>';
                                        if ($entryitem['dc'] == 'D') {
                                            echo $entryitem['dr_amount'];
                                        } else {
                                            echo '';
                                        }
                                        echo '</td>';

                                        echo '<td>';
                                        if ($entryitem['dc'] == 'C') {
                                            echo $entryitem['cr_amount'];
                                        } else {
                                            echo '';
                                        }
                                        echo '</td>';
                                        echo '<td>';
                                        echo $entryitem['narration'];
                                        echo '</td>';
                                        echo '</tr>';
                                    }

                                    /* Total */
                                    // echo '<tr class="bold-text">' . '<td></td>' . '<td>' . lang('entries_views_views_td_total') . '</td>' . '<td id="dr-total">' . $this->functionscore->toCurrency('D', $entry['dr_total']) . '</td>' . '<td id="cr-total">' . $this->functionscore->toCurrency('C', $entry['cr_total']) . '</td>' . '<td></td>' . '</tr>';
                                    
                                    echo '<tr class="bold-text">' . '<td></td>' . '<td>' . lang('entries_views_views_td_total') . '</td>' . '<td id="dr-total">' . $this->functionscore->toCurrency('D', $dr_amount_total) . '</td>' . '<td id="cr-total">' . $this->functionscore->toCurrency('C', $cr_amount_total) . '</td>' . '<td></td>' . '</tr>';

                                    /* Difference */
                                    if ($this->functionscore->calculate($entry['dr_total'], $entry['cr_total'], '==')) {
                                        /* Do nothing */
                                    } else {
                                        if ($this->functionscore->calculate($entry['dr_total'], $entry['cr_total'], '>')) {
                                            echo '<tr class="error-text">' . '<td></td>' . '<td>' . lang('entries_views_views_td_diff') . '</td>' . '<td id="dr-diff">' . $this->functionscore->toCurrency('D', $this->functionscore->calculate($entry['dr_total'], $entry['cr_total'], '-')) . '</td>' . '<td></td>' . '</tr>';
                                        } else {
                                            echo '<tr class="error-text">' . '<td></td>' . '<td>' . lang('entries_views_views_td_diff') . '</td>' . '<td></td>' . '<td id="cr-diff">' . $this->functionscore->toCurrency('C', $this->functionscore->calculate($entry['cr_total'], $entry['dr_total'], '-')) . '</td>' . '</tr>';

                                        }
                                    }
                                    echo '</table> </div>';

                                    echo '<br />';
                                    echo lang('entries_views_views_td_tag') . ' : ' . $this->functionscore->showTag($entry['tag_id']);

                                    echo '<br /><br />';
                                    ?>
                                </div>

                                <?php
                                echo 'Journal Attachments<br />';
                                $attachments = $defaultAttachments;
                                include (dirname(__FILE__) . '/../partials/attachments.php');
                                ?>

                                <?php
                                if (!empty($purchasesAttachments)) {
                                    echo 'Purchase Attachments<br />';
                                    $attachments = $purchasesAttachments;
                                    $doNotShowDelete = 1;
                                    include (dirname(__FILE__) . '/../partials/attachments.php');
                                }
                                ?>

                                <?php
                                if (!empty($saleAttachments)) {
                                    echo 'Sale Attachments<br />';
                                    $attachments = $saleAttachments;
                                    $doNotShowDelete = 1;
                                    include (dirname(__FILE__) . '/../partials/attachments.php');
                                }
                                ?>

                                <?php
                                if (!empty($transferAttachments)) {
                                    echo 'Transfer Attachments<br />';
                                    $attachments = $transferAttachments;
                                    $doNotShowDelete = 1;
                                    include (dirname(__FILE__) . '/../partials/attachments.php');
                                }
                                ?>

                                <a href="<?= admin_url('entries/edit/') . $entrytype['label'] . '/' . $entry['id']; ?>"
                                    class="btn btn-primary"><?= lang('entries_views_views_td_actions_edit_btn'); ?></a>
                                <a href="<?= admin_url('entries/delete/') . $entrytype['label'] . '/' . $entry['id']; ?>"
                                    class="btn btn-danger"><?= lang('entries_views_views_td_actions_delete_btn'); ?></a>
                                <a href="<?= admin_url('entries/') ?>"
                                    class="btn btn-default"><?= lang('entries_views_views_td_actions_cancel_btn'); ?></a>
                                <!-- <a href="<?= admin_url('entries/export/') . $entrytype['label'] . '/' . $entry['id']; ?>/xls" class="btn btn-primary"><?= lang('export_to_xls'); ?>
                    <a href="<?= admin_url('entries/export/') . $entrytype['label'] . '/' . $entry['id']; ?>/pdf" class="btn btn-primary"><?= lang('export_to_pdf'); ?></a></a> -->
                                <button onclick="generatePDF()"
                                    class="btn btn-primary"><?= lang('export_to_pdf'); ?></button>
                                <?php
                                ?>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

 
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
        var elementText = document.querySelector('#pdfTextContent');
        var textContent = elementText.innerText || elementText.textContent;

        doc.setFont("Amiri-Regular"); // Set font if required
        doc.setFontSize(12);
        doc.text(textContent, 10, startingY);

        // Render HTML content below the text
        var elementHTML = document.querySelector('#pdfHtmlContent');
        doc.html(elementHTML, {
            callback: function (_doc) {
                _doc.save('generated-document.pdf');
            },
            margin: [startingY + 10, 10, 10, 10], // Adjust top margin to account for the text
            x: 0,
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


 function generatePDFCorrect() {
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF({
        orientation: 'p',
        unit: 'pt',
        format: 'a4',
        putOnlyUsedFonts: true
    });

    // Ensure the font is correctly loaded
    const fontList = doc.getFontList();
    console.log('Available fonts:', fontList);

    // Set the font to Amiri-Regular if it's available
    if (fontList['Amiri-Regular']) {
        doc.setFont("Amiri-Regular");
    } else {
        console.error('Amiri-Regular font is not available.');
    }

    // Set the font size
    doc.setFontSize(16);

    // Extract the text content from the HTML element
    var elementText = document.querySelector('#pdfTextContent');
    var textContent = elementText.innerText || elementText.textContent;

    // Add text to the PDF with specified coordinates
    doc.text(textContent, 10, 50);

    // Render the HTML content in the PDF
    var elementHTML = document.querySelector('#pdfHtmlContent');

    doc.html(elementHTML, {
        callback: function (_doc) {
            // Add text after HTML content has been rendered
            _doc.save('generated-document.pdf');
        },
        margin: [10, 10, 10, 10],
        x: 0,
        y: 180, // Start the HTML content below the text added earlier
        width: 600,
        windowWidth: 675,
        html2canvas: {
            useCORS: true,
            allowTaint: true,
            scale: 72 / 96
        }
    });
}


        function generatePDFBlah() {

            const { jsPDF } = window.jspdf;
            const doc = new jsPDF({
                orientation: 'p',
                unit: 'pt',
                format: 'a4',
                putOnlyUsedFonts: true
            });

            const fontList = doc.getFontList();

            // Log the font list to the console
            console.log(fontList);
            doc.setFont("Amiri-Regular");
            //             // Set the font size
            doc.setFontSize(16);

            var elementText = document.querySelector('#pdfTextContent');
            var textContent = elementText.innerText || elementText.textContent;
            // Set the font to Amiri
            doc.text(textContent);

            // add image - logo
            // Adjust these to manage left and top margins
            var elementHTML = document.querySelector('#pdfHtmlContent');

            doc.html(elementHTML, {
                callback: function (_doc) {
                    _doc.save('generated-document.pdf');
                },
                margin: [10, 10, 10, 10],
                x: 0, // Left margin
                y: 0, // Top margin
                width: 600, // Adjust width to manage the right margin
                windowWidth: 675,
                html2canvas: {
                    useCORS: true,
                    allowTaint: true,
                    scale: 72 / 96 //scaling for pt to px
                }
            });

            //         var elementHTML = document.querySelector('#pdfContent');
            // var arabicText = elementHTML.innerText || elementHTML.textContent;

            // // Set RTL direction and add text manually
            // doc.setFontSize(16);
            // doc.text(arabicText, 10, 10);

            // // Save the PDF
            // doc.save('generated-document.pdf');

            //         doc.text("مرحبا بكم في مشروع CodeIgniter باستخدام خط أميري", 10, 10);

            // // Save the PDF
            //doc.save("example.pdf");
        }

    </script>