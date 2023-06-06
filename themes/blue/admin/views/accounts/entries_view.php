<script type="text/javascript">
$(document).ready(function() {

    var entryId = 0;
    $("button#send").click(function() {
        $(".modal-body").hide();
        $(".modal-footer").hide();
        $(".modal-ajax").show();
        $.ajax({
            type: "POST",
            url: '<?php echo admin_url("entries/email"); ?>/' + entryId,
            data: $('form#emailSubmit').serialize(),
                success: function(response) {
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
                error: function() {
                    $(".modal-error-msg").html("<?= lang('entries_views_views_email_not_sent_msg') ?>");
                    $(".error-msg").show();
                    $(".modal-body").show();
                    $(".modal-footer").show();
                    $(".modal-ajax").hide();
                }
        });
    });

    $('#emailModal').on('show.bs.modal', function(e) {
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
        <h2 class="blue"><i
                class="fa-fw fa fa-barcode"></i><?= lang('entries_views_views_title') ?>
        </h2>

        <div class="box-icon">
            
        </div>
    </div>
    <div class="box-content">
     	<div class="row">
              <div class="col-xs-12">
          <div class="box">
            
            <!-- /.box-header -->
            <div class="box-body">
              <div>
                <?php
                    echo (lang('entries_views_views_label_number')) . ' : ' . ($this->functionscore->toEntryNumber($entry['number'], $entry['entrytype_id']));
                    echo '<br /><br />';
                    echo (lang('entries_views_views_label_date')) . ' : ' . ($this->functionscore->dateFromSql($entry['date']));
                    echo '<br /><br />';

                    echo '<table class="table table-striped">';

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
                    echo '</table>';

                    echo '<br />';
                    echo lang('entries_views_views_td_tag') . ' : ' . $this->functionscore->showTag($entry['tag_id']);

                    echo '<br /><br />';
                    ?>
                    
                    <a href="<?= admin_url('entries/edit/').$entrytype['label'].'/'.$entry['id'];?>" class="btn btn-primary"><?= lang('entries_views_views_td_actions_edit_btn'); ?></a>
                    <a href="<?= admin_url('entries/delete/').$entrytype['label'].'/'.$entry['id'];?>" class="btn btn-danger"><?= lang('entries_views_views_td_actions_delete_btn'); ?></a>
                    <a href="<?= admin_url('entries/')?>" class="btn btn-default"><?= lang('entries_views_views_td_actions_cancel_btn'); ?></a>
                   <!-- <a href="<?= admin_url('entries/export/').$entrytype['label'].'/'.$entry['id'];?>/xls" class="btn btn-primary"><?=lang('export_to_xls');?></a>
                    <a href="<?= admin_url('entries/export/').$entrytype['label'].'/'.$entry['id'];?>/pdf" class="btn btn-primary"><?=lang('export_to_pdf');?></a>-->
                    <?php
                ?>
                </div>
            </div>
          </div>
      </div>
     		
     	</div>
     </div>
</div>