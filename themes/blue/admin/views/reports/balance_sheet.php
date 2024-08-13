<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php if($viewtype!='pdf'){ ?>
<style>
    .tablewrap{
        font-size: 14px;
    }

    .leftwrap{
        width: 50%;
        float: left;
    }

    .rightwrap{
        width: 50%;
        float: left;
        
    }

    .table-head{
        background-color: #428bca;
        color: white;
        border-color: #357ebd;
        border-top: 1px solid #357ebd;
        text-align: center;
        border: 1px solid white;
    }

    .table-content{
        padding: 15px;
    }
</style>
<?php } ?>
<script>
     function generatePDF1(){
       $('.viewtype').val('pdf');  
       document.getElementById("searchForm").submit();
       $('.viewtype').val(''); 
    }
    function generatePDF() {
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF({
        orientation: 'p',
        unit: 'pt',
        format: 'a4',
        putOnlyUsedFonts: true
    });

    // Load your logo from a URL (replace with the actual URL of your logo)
    const logoUrl = 'assets/uploads/logos/avenzur-logov2-024.png'; // Replace with your logo URL

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

        doc.setFont("Amiri-Regular"); // Set font if required
        doc.setFontSize(12);
      //  doc.text(textContent, 10, startingY);

        // Render HTML content below the text
        var elementHTML = document.querySelector('#pdfHtmlContent');
        doc.html(elementHTML, {
            callback: function (_doc) {
                _doc.save('balance-sheet.pdf');
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
    $(document).ready(function () {
        
    });
</script>
<?php if($viewtype=='pdf'){ ?>
    <link href="<?= $assets ?>styles/pdf/pdf.css" rel="stylesheet">  
  <?php  } ?>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-users"></i><?= lang('balance_sheet'); ?></h2>
        <?php  if($viewtype!='pdf'){?>
        <div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown"><a href="#" id="xls" class="tip" title="<?= lang('download_xls') ?>"><i class="icon fa fa-file-excel-o"></i></a></li>
                <li class="dropdown"><a href="#" id="image" class="tip" title="<?= lang('save_image') ?>"><i class="icon fa fa-file-picture-o"></i></a></li>
                <li class="dropdown"> <a href="javascript:void(0);" onclick="generatePDF()" id="pdf" class="tip" title="<?= lang('download_PDF') ?>"><i class="icon fa fa-file-pdf-o"></i></a></li>
            </ul>
        </div>
        <?php } ?>
    </div>
    <div class="box-content">
        <div class="row">
        <div class="col-lg-12">
        <?php
        if($viewtype!='pdf')
        {
            $attrib = ['data-toggle' => 'validator', 'role' => 'form','id' => 'searchForm'];
            echo admin_form_open_multipart('reports/balance_sheet', $attrib)
        ?>
         <input type="hidden" name="viewtype" id="viewtype" class="viewtype" value="" > 
                <div class="row">
                    <div class="col-lg-12">
                       
                        <div class="col-md-4">
                            <div class="form-group">
                                <?= lang('Date', 'date'); ?>
                                <?php echo form_input('date', ($date ?? ''), 'class="form-control input-tip date" id="date"'); ?>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="from-group">
                                <button type="submit" style="margin-top: 28px;" class="btn btn-primary" id="load_report"><?= lang('Load Report') ?></button>
                            </div>
                        </div>
                            
                    </div>
                </div>
                <hr />
                <?php echo form_close(); 
                } ?>
                <div class="row" id="pdfHtmlContent">
                    <div class="controls table-controls" style="font-size: 12px !important;">
                        <?php 
                            $total_assets = 0;
                            $total_liabilities = 0;

                            if(isset($balance_sheet['ledger_groups'])){
                        ?>
                        <div class="tablewrap" style="overflow:hidden;">
                            
                            <div class="leftwrap">
                                <div class="table-head"><?= lang('ASSETS'); ?></div>
                                <div class="table-content">
                                <?php
                                    foreach($balance_sheet['ledger_groups'] as $ledger_group){
                                        if($ledger_group->type2 == 'Assets'){
                                            ?>
                                                <div>
                                                    <span><b><?php echo $ledger_group->name; ?></b></span>
                                                    <span style="float:right;"><?php echo '-'; ?></span>
                                                </div>
                                            <?php
                                            foreach($ledger_group->ledgers as $ledger){
                                                $total_assets = $total_assets + ($ledger->credit_sum - $ledger->debit_sum);
                                                ?>
                                                <div style="margin-left: 20px;">
                                                    <span><?php echo $ledger->name; ?></span>
                                                    <span style="float:right;"><?php echo $ledger->credit_sum - $ledger->debit_sum; ?></span>
                                                </div>
                                                <?php
                                            }
                                        }
                                    }
                                ?>
                                </div>
                            </div>
                            <div class="rightwrap">
                                <div class="table-head"><?= lang('LIABILITIES'); ?></div>
                                <div class="table-content">
                                <?php
                                    foreach($balance_sheet['ledger_groups'] as $ledger_group){
                                        if($ledger_group->type2 == 'Liabilities'){
                                            ?>
                                                <div>
                                                    <span><b><?php echo $ledger_group->name; ?></b></span>
                                                    <span style="float:right;"><?php echo '-'; ?></span>
                                                </div>
                                            <?php
                                            foreach($ledger_group->ledgers as $ledger){
                                                $total_liabilities = $total_liabilities + ($ledger->credit_sum - $ledger->debit_sum);
                                                ?>
                                                <div style="margin-left: 20px;">
                                                    <span><?php echo $ledger->name; ?></span>
                                                    <span style="float:right;"><?php echo $ledger->credit_sum - $ledger->debit_sum; ?></span>
                                                </div>
                                                <?php
                                            }
                                        }
                                    }
                                ?>
                                </div>
                                <div class="table-head"><?= lang('EQUITY'); ?></div>
                                <div class="table-content">
                                <?php
                                    foreach($balance_sheet['ledger_groups'] as $ledger_group){
                                        if($ledger_group->type2 == 'Equity'){
                                            ?>
                                                <div>
                                                    <span><b><?php echo $ledger_group->name; ?></b></span>
                                                    <span style="float:right;"><?php echo '-'; ?></span>
                                                </div>
                                            <?php
                                            foreach($ledger_group->ledgers as $ledger){
                                                $total_liabilities = $total_liabilities + ($ledger->credit_sum - $ledger->debit_sum);
                                                ?>
                                                <div style="margin-left: 20px;">
                                                    <span><?php echo $ledger->name; ?></span>
                                                    <span style="float:right;"><?php echo $ledger->credit_sum - $ledger->debit_sum; ?></span>
                                                </div>
                                                <?php
                                            }
                                        }
                                    }
                                ?>
                                </div>
                            </div>

                        </div>
                        <div class="bottom-wrap" style="overflow: hidden;">
                            <div class="table-head" style="width: 50%;float:left;"><span style="float:left;margin-left:20px;"><?= lang('TOTAL ASSETS'); ?></span><span style="float:right;margin-right:20px;"><?= number_format($total_assets, 2, '.', ''); ?></span></div>
                            <div class="table-head" style="width: 50%;float:left;"><span style="float:left;margin-left:20px;"><?= lang('TOTAL LIABILITIES'); ?></span><span style="float:right;margin-right:20px;"><?= number_format($total_liabilities, 2, '.', ''); ?></span></div>
                        </div>

                        <?php }else{
                            echo "No results to show";
                        }
                        ?>
                    </div>
                
            </div>

        </div>
    </div>
</div>
