<style type="text/css">
    .summary {
    background-color: #FFFFCC;
    border: 1px solid #BBBBBB;
    border-collapse: collapse;
    text-align: left;
    width: 600px;
}
</style>
<script type="text/javascript">
$(document).ready(function() {

  /*  $("#accordion").accordion({
        collapsible: true,
        <?php
           /* if ($options == false) {
                echo 'active: false';
            }*/
        ?>
    });
*/
    /* Calculate date range in javascript */
    startDate = new Date(<?php echo strtotime($this->mAccountSettings->fy_start) * 1000; ?>  + (new Date().getTimezoneOffset() * 60 * 1000));
    endDate = new Date(<?php echo strtotime($this->mAccountSettings->fy_end) * 1000; ?>  + (new Date().getTimezoneOffset() * 60 * 1000));

    $(document.body).on("change","#ReportLedgerId",function(){
        if(this.value == 0){
            $('#ReportStartdate').prop('disabled', true);
            $('#ReportEnddate').prop('disabled', true);
        } else {
            $('#ReportStartdate').prop('disabled', false);
            $('#ReportEnddate').prop('disabled', false);
        }
    });
    $('#ReportLedgerId').trigger('change');

    /* Setup jQuery datepicker ui */
    $('#ReportStartdate').datepicker({
        minDate: startDate,
        maxDate: endDate,
        dateFormat: '<?php echo $this->mDateArray[1]; ?>',
        numberOfMonths: 1,
        onClose: function(selectedDate) {
            if (selectedDate) {
                $("#ReportEnddate").datepicker("option", "minDate", selectedDate);
            } else {
                $("#ReportEnddate").datepicker("option", "minDate", startDate);
            }
        }
    });
    $('#ReportEnddate').datepicker({
        minDate: startDate,
        maxDate: endDate,
        dateFormat: '<?php echo $this->mDateArray[1]; ?>',
        numberOfMonths: 1,
        onClose: function(selectedDate) {
            if (selectedDate) {
                $("#ReportStartdate").datepicker("option", "maxDate", selectedDate);
            } else {
                $("#ReportStartdate").datepicker("option", "maxDate", endDate);
            }
        }
    });

    
});
</script>
<div class="box">
	<div class="box-header">
        <h2 class="blue"><i
                class="fa-fw fa fa-barcode"></i><?= $title; ?>
        </h2>

        <div class="box-icon">
            
        </div>
    </div>
    <div class="box-content">
     	<div class="row">
            <div class="col-xs-12">
          <div class="box">
            <div class="box-header with-border">
              <h3 class="box-title"><?= $title; ?></h3>
            </div>
            <!-- /.box-header -->
            <div class="box-body">
                    <!-- <div id="accordion"> -->
                        <!-- <h3>Options</h3> -->
                        <div class="balancesheet form">
                            <?php echo form_open(); ?>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                    <label><?= lang('ledger_acc_name'); ?></label>
                                        <select class="form-control" id="ReportLedgerId" name="ledger_id">
                                            <?php foreach ($ledgers as $id => $ledger): ?>
                                                <option value="<?= $id; ?>" <?= ($id < 0) ? 'disabled' : "" ?> <?= (($this->input->post('ledger_id') == $id) or ($this->uri->segment(4) == $id)) ?'selected':''?>><?= $ledger; ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label><?= lang('start_date'); ?></label>
                                        <div class="input-group">
                                            <input id="ReportStartdate" type="text" name="startdate" class="form-control">
                                            <div class="input-group-addon">
                                                <i>
                                                    <div class="fa fa-info-circle" data-toggle="tooltip" title="<?= lang('start_date_span') ;?>">
                                                    </div>
                                                </i>
                                            </div>
                                        </div>
                                        <!-- /.input group -->
                                    </div>
                                    <!-- /.form group -->
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label><?= lang('end_date') ;?></label>

                                        <div class="input-group">
                                            <input id="ReportEnddate" type="text" name="enddate" class="form-control">
                                            <div class="input-group-addon">
                                                <i>
                                                    <div class="fa fa-info-circle" data-toggle="tooltip" title="<?= lang('end_date_span') ;?>">
                                                    </div>
                                                </i>
                                            </div>
                                        </div>
                                        <!-- /.input group -->
                                    </div>
                                    <!-- /.form group -->
                                </div>
                            </div>
                            <div class="form-group">
                                <input type="reset" name="reset" class="btn btn-primary pull-right" style="margin-left: 5px;" value="<?= lang('clear'); ?>">
                                <input type="submit" name="submit" class="btn btn-primary pull-right" value="<?=lang('submit');?>">
                                <?php
                                    if ($this->input->post('ledger_id')){
                                        $get = $this->input->post('ledger_id');

                                        if ($this->input->post('startdate')) {
                                            $get .= "?startdate=". $this->input->post('startdate');

                                        }
                                        if ($this->input->post('enddate')) {
                                            $get .= "&enddate=". $this->input->post('enddate');
                                        }
                                ?>
                                    <!--<a href="<?=base_url();?>/reports/export_ledgerstatement/xls/<?= $get; ?>" type="button" name="submit" class="btn btn-primary pull-right"><?=lang('export_to_xls');?></a>
                                    <a href="<?=base_url();?>/reports/export_ledgerstatement/pdf/<?= $get; ?>" type="button" name="submit" class="btn btn-primary pull-right"><?=lang('export_to_pdf');?></a>-->
                                <?php
                                    }
                                ?>
                            </div>
                            <?php form_close();  ?>
                        </div>
                    <!-- </div> -->
                <?php if ($showEntries) :  ?>
                    <div class="subtitle">
                        <?php echo $subtitle; ?>
                    </div>
                    <div class="row" style="margin-bottom: 10px;">
                        <div class="col-md-6">
                            <table class="table summary stripped table-condensed">
                            <tr>
                                <td class="td-fixwidth-summary"><?php echo ('Bank or cash account'); ?></td>
                                <td>

                                    <?php
                                        echo ($ledger_data['type'] == 1) ? 'Yes' : 'No';
                                    ?>
                                </td>
                            </tr>
                            <tr>
                                <td class="td-fixwidth-summary"><?php echo ('Notes'); ?></td>
                                <td><?php echo ($ledger_data['notes']); ?></td>
                            </tr>
                        </table>
                        </div>
                        <div class="col-md-6">
                            <table class="summary stripped table-condensed">
                            <tr>
                                <td class="td-fixwidth-summary"><?php echo $opening_title; ?></td>
                                <td><?php echo $this->functionscore->toCurrency($op['dc'], $op['amount']); ?></td>
                            </tr>
                            <tr>
                                <td class="td-fixwidth-summary"><?php echo $closing_title; ?></td>
                                <td><?php echo $this->functionscore->toCurrency($cl['dc'], $cl['amount']); ?></td>
                            </tr>
                        </table>
                        </div>
                    </div>
                    <table class="table table-striped">

                    <tr>
                    <th><?php echo lang('entries_views_add_label_date'); ?></th>
                    <th><?php echo lang('entries_views_add_label_number'); ?></th>
                    <th><?php echo lang('entries_views_add_items_th_ledger'); ?></th>
                    <th><?php echo lang('accounts_index_type'); ?></th>
                    <th><?php echo lang('entries_views_add_label_tag'); ?></th>
                    <th><?php echo lang('entries_views_add_items_th_dr_amount'); ?><?php echo ' (' . $this->mAccountSettings->currency_symbol . ')'; ?></th>
                    <th><?php echo lang('entries_views_add_items_th_cr_amount'); ?><?php echo ' (' . $this->mAccountSettings->currency_symbol . ')'; ?></th>
                    <th><?php echo lang('balance'); ?><?php echo ' (' . $this->mAccountSettings->currency_symbol . ')'; ?></th>
                    <th><?php echo lang('accounts_index_action'); ?></th>
                    </tr>

                    <?php
                        /* Current opening balance */
                        $entry_balance['amount'] = $current_op['amount'];
                        $entry_balance['dc'] = $current_op['dc'];
                        echo '<tr class="tr-highlight">';
                        echo '<td colspan="7">';
                        echo lang('curr_opening_balance');
                        echo '</td>';
                        echo '<td>' . $this->functionscore->toCurrency($current_op['dc'], $current_op['amount']) . '</td>';
                        echo '<td></td>';
                        echo '</tr>';
                    ?>

                    <?php
                    /* Show the entries table */
                    foreach ($entries as $entry) {
                        /* Calculate current entry balance */
                        $entry_balance = $this->functionscore->calculate_withdc(
                            $entry_balance['amount'], $entry_balance['dc'],
                            $entry['amount'], $entry['dc']
                        );

                        $et = $this->db->where('id', $entry['entrytype_id'])->get('sma_accounts_entrytypes')->row_array();
                        $entryTypeName = $et['name'];
                        $entryTypeLabel = $et['label'];

                        /* Negative balance if its a cash or bank account and balance is Cr */
                        if ($ledger_data['type'] == 1) {
                            if ($entry_balance['dc'] == 'C' && $entry_balance['amount'] != '0.00') {
                                echo '<tr class="error-text">';
                            } else {
                                echo '<tr>';
                            }
                        } else {
                            echo '<tr>';
                        }

                        echo '<td>' . $this->functionscore->dateFromSql($entry['date']) . '</td>';
                        echo '<td>' . ($this->functionscore->toEntryNumber($entry['number'], $entry['entrytype_id'])) . '</td>';
                        echo '<td>' . ($this->functionscore->entryLedgers($entry['id'])) . '</td>';
                        echo '<td>' . ($entryTypeName) . '</td>';
                        echo '<td>' . $this->functionscore->showTag($entry['tag_id'])  . '</td>';
                        if ($entry['dc'] == 'D') {
                            echo '<td>' . $this->functionscore->toCurrency('D', $entry['amount']) . '</td>';
                            echo '<td>' . '</td>';
                        } else if ($entry['dc'] == 'C') {
                            echo '<td>' . '</td>';
                            echo '<td>' . $this->functionscore->toCurrency('C', $entry['amount']) . '</td>';
                        } else {
                            echo '<td>' . lang('search_views_amounts_td_error') . '</td>';
                            echo '<td>' . lang('search_views_amounts_td_error') . '</td>';
                        }

                        echo '<td>' . $this->functionscore->toCurrency($entry_balance['dc'], $entry_balance['amount']) . '</td>';

                        echo '<td>';
                        ?>
                            <a href="<?= admin_url();?>entries/view/<?= ($entryTypeLabel); ?>/<?= $entry['id']; ?>" class="no-hover" escape="false"><i class="fa fa-log-in"></i> <?= lang('view');?></a>
                            <span class="link-pad"></span>
                            <a href="<?= admin_url();?>entries/edit/<?= ($entryTypeLabel); ?>/<?= $entry['id']; ?>" class="no-hover" escape="false"><i class="fa fa-edit"></i> <?= lang('edit');?></a>
                            <span class="link-pad"></span>
                            <a href="<?= admin_url();?>entries/delete/<?= ($entryTypeLabel); ?>/<?= $entry['id']; ?>" class="no-hover" escape="false"><i class="fa fa-trash"></i> <?= lang('delete');?></a>
                            
                        <?php
                        echo '</td>';
                        echo '</tr>';
                    }
                    ?>

                    <?php
                        /* Current closing balance */
                        echo '<tr class="tr-highlight">';
                        echo '<td colspan="7">';
                        echo lang('curr_closing_balance');
                        echo '</td>';
                        echo '<td>' . $this->functionscore->toCurrency($entry_balance['dc'], $entry_balance['amount']) . '</td>';
                        echo '<td></td>';
                        echo '</tr>';
                    ?>

                    </table>

                <?php endif; ?>
            </div>
          </div>
      </div>
     		
     	</div>
     </div>
</div>