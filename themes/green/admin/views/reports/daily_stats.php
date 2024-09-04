<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"
    integrity="sha512-GsLlZN/3F2ErC5ifS5QtgpiJtWd43JWSuIgh7mbzZ8zBps+dvLusV+eNQATqgA/HdeKFVgA5v3S/cIrLF7QnIg=="
    crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script>
    $(document).ready(function () {

    });
</script>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-users"></i><?= lang('daily_stats'); ?></h2>

        <div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown"><a href="#" id="xls" class="tip" title="<?= lang('download_xls') ?>"><i
                                class="icon fa fa-file-excel-o"></i></a></li>
                <li class="dropdown"><a  onclick="generatePDF()" id="image" class="tip" title="<?= lang('export_to_pdf') ?>"><i
                                class="icon fa fa-file-picture-o"></i></a></li>
            </ul>
        </div>
    </div>
    <div class="box-content">
        <div class="row">
            <?php
            $attrib = ['data-toggle' => 'validator', 'role' => 'form'];
            echo admin_form_open_multipart('reports/daily_stats', $attrib)
            ?>
            <div class="col-lg-12">
                <div class="row">
                    <div class="col-lg-12">

                        <div class="col-md-4">
                            <div class="form-group">
                                <?= lang('Date', 'podate'); ?>
                                <?php echo form_input('date', ($date ?? ''), 'class="form-control input-tip date" id="date"'); ?>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="from-group">
                                <button type="submit" style="margin-top: 28px;" class="btn btn-primary"
                                        id="load_report"><?= lang('Load Report') ?></button>
                            </div>
                        </div>

                    </div>
                </div>
                <hr/>
                <div class="row" id="pdfHtmlContent">
                    <div class="controls table-controls" style="font-size: 12px !important;">
                        <table id="dtsTable"
                               class="table items table-striped table-bordered table-condensed table-hover sortable_table">
                            <thead>
                                <h3>Daily Traffic Stats</h3>
                            <tr>
                                <th><?= lang('date'); ?></th>
                                <th><?= lang('PageViews'); ?></th>
                                <th><?= lang('Website Logins'); ?></th>
                                <th><?= lang('Orders'); ?></th>
                            </tr>
                            </thead>
                            <tbody style="text-align:center;">
                            
                            <tr>
                                <td><?= $date; ?></td>
                                <td><?= $daily_stats['page_views']; ?></td>
                                <td><?= $daily_stats['total_logins']; ?></td>
                                <td><?= $daily_stats['total_orders']; ?></td>
                                
                            </tr>

                            </tbody>
                            <tfoot></tfoot>
                        </table>

                        <table id="dobTable"
                               class="table items table-striped table-bordered table-condensed table-hover sortable_table">
                            <thead>
                                <h3>Daily Orders Breakdown</h3>
                            <tr>
                                <th>#</th>
                                <th><?= lang('Order Location'); ?></th>
                                <th><?= lang('Courier'); ?></th>
                                <th><?= lang('Order Time'); ?></th>
                                <th><?= lang('Order Value'); ?></th>
                                <th><?= lang('Courier Assignment'); ?></th>
                                <th><?= lang('Pickup Time'); ?></th>
                                <th><?= lang('Delivery Time'); ?></th>
                            </tr>
                            </thead>
                            <tbody style="text-align:center;">
                            <?php 
                                $count = 0;
                                foreach ($order_stats as $order){
                                    $count++;
                                    ?>
                                        <tr>
                                            <td><?= $count; ?></td>
                                            <td><?= $order->location; ?></td>
                                            <td><?= $order->courier_name; ?></td>
                                            <td><?= $order->order_time; ?></td>
                                            <td><?= $order->order_value; ?></td>
                                            <td><?= $order->assignment_time; ?></td>
                                            <td><?= $order->pickup_time; ?></td>
                                            <td><?= $order->delivery_time; ?></td>
                                        </tr>
                                    <?php
                                }
                            ?>

                            </tbody>
                            <tfoot></tfoot>
                        </table>

                        <table id="dtbTable"
                               class="table items table-striped table-bordered table-condensed table-hover sortable_table">
                            <thead>
                                <h3>Daily Traffic Breakdown</h3>
                            <tr>
                                <th>#</th>
                                <th><?= lang('Location'); ?></th>
                                <th><?= lang('date'); ?></th>
                                <th><?= lang('PageViews'); ?></th>
                            </tr>
                            </thead>
                            <tbody style="text-align:center;">
                            <?php
                            $count = 0;
                            foreach ($user_stats as $usr_stats) {
                                $count++;
                                ?>  
                                    <tr>
                                        <td><?= $count; ?></td>
                                        <td><?= $usr_stats->location; ?></td>
                                        <td><?= $date; ?></td>
                                        <td><?= $usr_stats->page_views; ?></td>
                                    </tr>
                                <?php
                            }
                            ?>
                            <tr>
                                <th>&nbsp;</th>
                                <th>&nbsp;</th>
                                <th>&nbsp;</th>
                                <th>&nbsp;</th>
                                
                            </tr>

                            </tbody>
                            <tfoot></tfoot>
                        </table>
                    </div>

                </div>

            </div>
        </div>
        <?php echo form_close(); ?>
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
            // var elementText = document.querySelector('#pdfTextContent');
            // var textContent = elementText.innerText || elementText.textContent;

            // doc.setFont("Amiri-Regular"); // Set font if required
            // doc.setFontSize(12);
            // doc.text(textContent, 10, startingY);

            // Render HTML content below the text
            var elementHTML = document.querySelector('#pdfHtmlContent');
            doc.html(elementHTML, {
                callback: function (_doc) {
                    _doc.save('generated-document.pdf');
                },
                margin: [startingY + 10, 10, 10, 10], // Adjust top margin to account for the text
                x: 20,
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
</script>
