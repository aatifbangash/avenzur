<div class="box">
	<div class="box-header">
        <h2 class="blue"><i
                class="fa-fw fa fa-barcode"></i><?= lang('search_views_title'); ?>
        </h2>

        <div class="box-icon">
            
        </div>
    </div>
    <div class="box-content">
     	<div class="row">
            <!-- Main content -->
    <section class="content">
      <!-- Small boxes (Stat box) -->
          <div class="box">
            
            <!-- /.box-header -->
            <div class="box-body">
              <div>
                <div class="search form">
                <?php echo form_open(); ?>
                <div class="row">
                    <div class="col-md-6">
                        <fieldset>
                            <legend><?= lang('search_views_legend_ledgers'); ?></legend>
                            <div class="form-group">
                                <select class="ledger-dropdown form-control" name="ledger_ids[]" multiple="multiple">
                                    <?php foreach ($ledger_options as $id => $ledger): ?>
                                        <option value="<?= $id; ?>" <?= ($id < 0) ? 'disabled' : "" ?>  <?php echo (isset($_POST['ledger_ids']) && in_array($id, $_POST['ledger_ids'])) ? 'selected' : ''; ?>><?= $ledger; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </fieldset>
                    </div>
                    <div class="col-md-6">
                        <fieldset>
                            <legend><?= lang('search_views_legend_entrytype') ?></legend>
                            <div class="form-group">
                                <select class="entrytype-dropdown form-control" name="entrytype_ids[]" multiple="multiple" >
                                    <?php foreach ($entrytype_options as $id => $et): ?>
                                        <option value="<?= $id; ?>" <?php echo (isset($_POST['entrytype_ids']) && in_array($id, $_POST['entrytype_ids'])) ? 'selected' : ''; ?>><?= $et; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </fieldset>
                    </div>
                </div>

                <br>

                <fieldset>
                    <legend><?= lang('search_views_legend_entry_number') ?></legend>
                    <div class="form-group">
                        <div class="row">
                            <div class="col-md-4">
                                <label><?= lang('search_views_label_condition') ?></label>
                                <select class="form-control" id="SearchEntrynumberRestriction" name="entrynumber_restriction">
                                    <option value="1" <?= ($this->input->post('entrynumber_restriction') == 1) ? 'selected' : '' ?> ><?= lang('search_views_entry_number_equal') ?></option>
                                    <option value="2" <?= ($this->input->post('entrynumber_restriction') == 2) ? 'selected' : '' ?> ><?= lang('search_views_entry_number_less_equal') ?></option>
                                    <option value="3" <?= ($this->input->post('entrynumber_restriction') == 3) ? 'selected' : '' ?> ><?= lang('search_views_entry_number_greater_equal') ?></option>
                                    <option value="4" <?= ($this->input->post('entrynumber_restriction') == 4) ? 'selected' : '' ?> ><?= lang('search_views_entry_number_between') ?></option>
                                </select>
                            </div>
                            <div class="col-md-4">
                            <label><?= lang('search_views_label_from') ?></label>
                                <input type="text" value="<?= set_value('entrynumber1'); ?>" class="form-control" name="entrynumber1">
                            </div>
                            <div class="col-md-4 entrynumber-in-between">
                            <label><?= lang('search_views_label_to') ?></label>
                                <input type="text" value="<?= set_value('entrynumber2'); ?>" class="form-control" name="entrynumber2">
                            </div>
                        </div>
                    </div>
                </fieldset>

                <fieldset>
                    <legend><?= lang('search_views_legend_amount') ?></legend>
                    <div class="form-group">
                        <div class="row">
                            <div class="col-md-3">
                                <label><?= lang('search_views_label_dr_or_cr') ?></label>
                                <select class="form-control" name="amount_dc">
                                    <option value="0" <?= ($this->input->post('amount_dc') == '0') ? 'selected' : '' ?>><?= lang('search_views_dr_or_cr_option_any') ?></option>
                                    <option value="D" <?= ($this->input->post('amount_dc') == "D") ? 'selected' : '' ?>><?= lang('search_views_dr_or_cr_option_dr') ?></option>
                                    <option value="C" <?= ($this->input->post('amount_dc') == "C") ? 'selected' : '' ?>><?= lang('search_views_dr_or_cr_option_cr') ?></option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label><?= lang('search_views_label_condition') ?></label>
                                <select class="form-control" id="SearchAmountRestriction" name="amount_restriction">
                                    <option value="1" <?= ($this->input->post('amount_restriction') == 1) ? 'selected' : '' ?> ><?= lang('search_views_condition_equal') ?></option>
                                    <option value="2" <?= ($this->input->post('amount_restriction') == 2) ? 'selected' : '' ?> ><?= lang('search_views_condition_less_equal') ?></option>
                                    <option value="3" <?= ($this->input->post('amount_restriction') == 3) ? 'selected' : '' ?> ><?= lang('search_views_condition_greater_equal') ?></option>
                                    <option value="4" <?= ($this->input->post('amount_restriction') == 4) ? 'selected' : '' ?> ><?= lang('search_views_condition_between') ?></option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label><?= lang('search_views_label_amount') ?></label>
                                <input type="text" class="form-control" value="<?= set_value('amount1'); ?>" name="amount1">
                            </div>

                            <div class="col-md-3 amount-in-between">
                                <label><?= lang('search_views_label_amount_in_between') ?></label>
                                <input type="text" class="form-control " value="<?= set_value('amount2'); ?>" name="amount2">
                            </div>
                        </div>
                    </div>
                </fieldset>

                <fieldset>
                    <legend><?= lang('search_views_legend_date') ?></legend>
                    <div class="form-group">
                        <div class="row">
                            <div class="col-md-4">
                                <label><?= lang('search_views_label_from') ?></label>
                                <input type="text" class="form-control" id="SearchFromdate" value="<?= set_value('fromdate'); ?>" name="fromdate">
                            </div>

                            <div class="col-md-4">
                                <label><?= lang('search_views_label_to') ?></label>
                                <input type="text" class="form-control" id="SearchTodate" value="<?= set_value('todate'); ?>" name="todate">
                            </div>
                        </div>
                    </div>
                </fieldset>

                <fieldset>
                    <legend><?= lang('search_views_legend_tags') ?></legend>
                    <div class="form-group">
                        <select class="form-control tag-dropdown" name="tag_ids[]" multiple="multiple">
                            <?php foreach ($tag_options as $id => $tag): ?>
                                <option value="<?= $id; ?>" <?php echo (isset($_POST['tag_ids']) && in_array($id, $_POST['tag_ids'])) ? 'selected' : ''; ?>><?= $tag; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </fieldset>

                <fieldset>
                    <legend><?= lang('search_views_legend_narration_contains') ?></legend>
                    <div class="form-group">
                        <textarea class="form-control" name="narration" rows="4"><?= set_value('narration'); ?></textarea>
                    </div>
                </fieldset>

                <div class="form-group">
                    <input type="submit" class="btn btn-primary" value="<?= lang('search_views_search_btn') ?>">
                </div>
                <?= form_close(); ?>

<?php if ($showEntries) { ?>

        <table class="table table-bordered table-condensed table-striped">
            <tr>
                <th><?= lang('search_views_th_date'); ?></th>
                <th><?= lang('search_views_th_number'); ?></th>
                <th><?= lang('search_views_th_ledger'); ?></th>
                <th><?= lang('search_views_th_type'); ?></th>
                <th><?= lang('search_views_th_tag'); ?></th>
                <th><?= lang('search_views_th_dr_amount'); ?> (<?= $this->mAccountSettings->currency_symbol; ?>) </th>
                <th><?= lang('search_views_th_cr_amount'); ?> (<?= $this->mAccountSettings->currency_symbol; ?>) </th>
                <th><?= lang('search_views_th_actions'); ?></th>
            </tr>
            <?php
            /* Show the entries table */
            foreach ($entries as $entry) {
                $et = $this->db->where('id', $entry['entrytype_id'])->get('sma_accounts_entrytypes')->row_array();
                $entryTypeName = $et['name'];
                $entryTypeLabel = $et['label'];

                echo '<tr>';
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
                    echo '<td>' . (lang('')) . '</td>';
                    echo '<td>' . (lang('')) . '</td>';
                }

                echo '<td>';
                ?>
                    <a href="<?= admin_url();?>entries/view/<?= ($entryTypeLabel); ?>/<?= $entry['entry_id']; ?>" class="no-hover" escape="false"><i class="fa fa-log-in"></i> View</a>
                    <span class="link-pad"></span>
                    <a href="<?= admin_url();?>entries/edit/<?= ($entryTypeLabel); ?>/<?= $entry['entry_id']; ?>" class="no-hover" escape="false"><i class="fa fa-edit"></i> Edit</a>
                    <span class="link-pad"></span>
                    <a href="<?= admin_url();?>entries/delete/<?= ($entryTypeLabel); ?>/<?= $entry['entry_id']; ?>" class="no-hover" escape="false"><i class="fa fa-trash"></i> Delete</a>
                <?php
                echo '</td>';
                echo '</tr>';
            } ?>
        </table>
    <?php } ?>
    </div>
</section>
     		
     	</div>
     </div>
</div>