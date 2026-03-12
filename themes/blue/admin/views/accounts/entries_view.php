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

                                    if($payement_reference_id > 0){
                                        $link = admin_url('customers/view_payment/' . $payement_reference_id);
                                        echo (lang('Payment_Reference')) . ' : <a href="' . $link . '" target ="_blank">' . ($payement_reference_id) . '</a><br>';

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
                                    echo '<th>' . lang('entries_views_views_th_dr_amount') . '</th>';
                                    echo '<th>' . lang('entries_views_views_th_cr_amount') . '</th>';
                                    echo '<th>' . lang('entries_views_views_th_narration') . '</th>';    
                                    $customer_exists = false;
                                    $supplier_exists = false;
                                    $department_exists = false;
                                    $employee_exists = false;
                                    foreach ($curEntryitems as $row => $entryitem) {
                                        if($entryitem['customer_id']>0){
                                            $customer_exists = true;
                                            break;
                                        }
                                    }
                                    foreach ($curEntryitems as $row => $entryitem) {
                                        if($entryitem['supplier_id'] > 0){
                                            $supplier_exists = true;
                                            break;
                                        }
                                    }
                                    foreach ($curEntryitems as $row => $entryitem) {
                                        if($entryitem['department_id']  > 0){
                                            $department_exists = true;
                                            break;
                                        }
                                    }
                                    foreach ($curEntryitems as $row => $entryitem) {
                                        if($entryitem['employee_id'] >  0){
                                            $employee_exists = true;
                                            break;
                                        }
                                    }
                                    if($customer_exists)   {
                                        echo '<th>' . (lang('Customer', 'customer_id')) . '</th>';

                                    }         
                                     if($supplier_exists)   {
                                       echo '<th>' . (lang('Supplier', 'supplier_id')) . '</th>';

                                    }  
                                     if($department_exists)   {
                                       echo '<th>' . (lang('Departments', 'department_id')) . '</th>';

                                    }  
                                     if($employee_exists)   {
                                       echo '<th>' . (lang('Employees', 'employee_id')) . '</th>';
                                    }                        
                                    
                                    
                                    
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
                                        if($customer_exists){
                                            echo '<td>';
                                            if($entryitem['customer_id']){
                                                echo  $entryitem['customer_name'];
                                            }else{
                                                echo '';
                                            }
                                            echo '</td>';
                                        }
                                        if($supplier_exists){
                                            echo '<td>';
                                            if($entryitem['supplier_id']){
                                                echo  $entryitem['supplier_name'];
                                            }else{
                                                echo '';
                                            }
                                            echo '</td>';
                                        }
                                        if($department_exists){
                                            echo '<td>';
                                            if($entryitem['department_id']){
                                                echo  $entryitem['department_name'];
                                            }else{
                                                echo '';
                                            }
                                            echo '</td>';
                                        }
                                        if($employee_exists){
                                            echo '<td>';
                                            if($entryitem['employee_id']){
                                                echo  $entryitem['employee_name'];
                                            }else{
                                                echo '';
                                            }
                                            echo '</td>';
                                        }
                                        
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

                                <?php
                                if (!empty($jl_attachments)) {
                                    echo '<div class="clearfix"></div>';
                                    echo '<h4>JL Entry Attachments</h4>';
                                    echo '<div class="table-responsive">';
                                    echo '<table class="table table-bordered table-striped">';
                                    echo '<thead><tr>';
                                    echo '<th>File Name</th>';
                                    echo '<th>File Size</th>';
                                    echo '<th>Uploaded</th>';
                                    echo '<th>Action</th>';
                                    echo '</tr></thead><tbody>';
                                    
                                    foreach ($jl_attachments as $attachment) {
                                        $file_size_kb = round($attachment['file_size'] / 1024, 2);
                                        $download_url = admin_url('accounts/download_attachment/' . $attachment['id']);
                                        echo '<tr>';
                                        echo '<td><i class="fa fa-file"></i> ' . $attachment['file_name'] . '</td>';
                                        echo '<td>' . $file_size_kb . ' KB</td>';
                                        echo '<td>' . date('Y-m-d H:i', strtotime($attachment['uploaded_at'])) . '</td>';
                                        echo '<td><a href="' . $download_url . '" class="btn btn-sm btn-primary"><i class="fa fa-download"></i> Download</a></td>';
                                        echo '</tr>';
                                    }
                                    
                                    echo '</tbody></table></div>';
                                }
                                ?>

                                <!--<a href="<?= admin_url('entries/edit/') . $entrytype['label'] . '/' . $entry['id']; ?>"
                                    class="btn btn-primary"><?= lang('entries_views_views_td_actions_edit_btn'); ?></a>
                                <a href="<?= admin_url('entries/delete/') . $entrytype['label'] . '/' . $entry['id']; ?>"
                                    class="btn btn-danger"><?= lang('entries_views_views_td_actions_delete_btn'); ?></a>-->
                                <a href="<?= admin_url('entries/') ?>"
                                    class="btn btn-default"><?= lang('entries_views_views_td_actions_cancel_btn'); ?></a>
                                <a href="<?= admin_url('entries/export/') . $entrytype['label'] . '/' . $entry['id']; ?>/pdf" class="btn btn-primary"><i class="fa fa-file-pdf-o"></i> <?= lang('export_to_pdf'); ?></a>
                                <?php
                                ?>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

</script>