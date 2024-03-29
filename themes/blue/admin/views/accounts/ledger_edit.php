<script type="text/javascript">
$(document).ready(function() {
    $("#LedgerGroupId").select2({width:'100%'});
});
</script>

<style type="text/css">
.select2-container--default .select2-results__option {
    font-weight: bold;
    color: #333;
}
</style>
<div class="box">
	<div class="box-header">
        <h2 class="blue"><i
                class="fa-fw fa fa-barcode"></i><?= lang('Edit Ledger') ?>
        </h2>

        <div class="box-icon">
            
        </div>
    </div>
    <div class="box-content">
     	<div class="row">
            <div class="col-xs-12">
          <div class="box">
            <div class="box-header with-border">
              <h3 class="box-title"><?= lang('ledgers_views_edit_title'); ?></h3>
            </div>
            <!-- /.box-header -->
            <div class="box-body">
                <?= validation_errors('<div class="alert alert-danger">', '</div>'); ?>
                <div class="ledgers add form">
                    <?php
                        echo form_open(); ?>

                        <div class="row">
                            <div class="col-md-5">
                                <div class="form-group">
                                    <label><?= lang('ledgers_views_edit_label_parent_group'); ?></label>
                                    <?= form_dropdown('group_id', $parents, set_value('group_id', $ledger['group_id']),array('id' => 'LedgerGroupId', 'class'=>'form-control' )); ?>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label><?= lang('ledgers_views_edit_label_ledger_code'); ?></label>
                                    <input type="text" name="code" class="form-control" value="<?= set_value('code', $ledger['code']); ?>" >
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label><?= lang('ledgers_views_edit_label_ledger_name'); ?></label>
                                    <input type="text" name="name" class="form-control" value="<?= set_value('name', $ledger['name']); ?>" >
                                </div>
                            </div>
                        </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label><?= lang('Type1'); ?></label>
                                <!-- <input type="text" name="type1" class="form-control" value="<?= set_value('type1', $ledger['type1']); ?>" > -->
                                <?= form_dropdown('type1', $accountTypeOne, set_value('type1', $ledger['type1']),array('class' => 'form-control', 'id'=>'g_type1')); ?>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label><?= lang('Type2'); ?></label>
                                <!-- <input type="text" name="type2" class="form-control" value="<?= set_value('type2', $ledger['type2']); ?>" > -->
                                <?= form_dropdown('Type2', $accountTypeTwo, set_value('type2', $ledger['type2']),array('class' => 'form-control', 'id'=>'g_type1')); ?>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label><?= lang('Category'); ?></label>
                                <!-- <input type="text" name="category" class="form-control" value="<?= set_value('category', $ledger['category']); ?>" > -->
                                <?= form_dropdown('category', $accountCategories, set_value('category', $ledger['category']),array('class' => 'form-control', 'id'=>'category')); ?>
                            </div>
                        </div>
                    </div>

                        <div class="form-group">
                            <label><?= lang('ledgers_views_edit_label_op_blnc'); ?></label>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <?= form_dropdown('op_balance_dc', array('D' => lang('entries_views_addrow_label_dc_drcr_D'), 'C' => lang('entries_views_addrow_label_dc_drcr_C')), set_value('op_balance_dc', $ledger['op_balance_dc']), array('class'=>'form-control')); ?>
                                        </div>
                                        <div class="col-md-8">
                                            <div class="form-group">
                                                <div class="input-group">
                                                    <input type="number" name="op_balance" class="form-control" value="<?= set_value('op_balance_dc', $ledger['op_balance']); ?>">
                                                    <div class="input-group-addon">
                                                        <i>
                                                            <div class="fa fa-info-circle" data-toggle="tooltip" title="<?= lang('ledgers_views_edit_op_blnc_tooltip'); ?>">
                                                            </div>
                                                        </i>
                                                    </div>
                                                </div>
                                                <!-- /.input group -->
                                            </div>
                                            <!-- /.form group -->
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <div class="input-group">
                                    <label><input type="checkbox" name="type" class="form-control" <?= ($ledger['type'] or set_value('type')) ? 'checked' : '' ?>><?= lang('ledgers_views_edit_label_bank_cash_account'); ?></label>
                                    <div class="input-group-addon">
                                        <i>
                                            <div class="fa fa-info-circle" data-toggle="tooltip" title="<?= lang('ledgers_views_edit_bank_cash_account_tooltip'); ?>">
                                            </div>
                                        </i>
                                    </div>
                                </div>
                                <!-- /.input group -->
                            </div>
                            <!-- /.form group -->
                            <div class="form-group">
                                <div class="input-group">
                            <label><input type="checkbox" name="reconciliation" class="form-control" <?= ($ledger['reconciliation'] or set_value('reconciliation')) ? 'checked' : '' ?>><?= lang('entries_views_edit_label_reconciliation'); ?></label>
                                    <div class="input-group-addon">
                                        <i>
                                            <div class="fa fa-info-circle" data-toggle="tooltip" title="<?= lang('ledgers_views_edit_reconciliation_tooltip'); ?>">
                                            </div>
                                        </i>
                                    </div>
                                </div>
                                <!-- /.input group -->
                            </div>
                            <!-- /.form group -->
                        </div>
                        <div class="col-md-8">
                            <div class="form-group">
                                <label><?= lang('ledgers_views_edit_label_notes'); ?></label>
                            <textarea name="notes" rows="3" class="form-control"><?= set_value('notes', $ledger['notes']); ?></textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="box-footer">
                <div class="form-group">
                    <input type="submit" name="Submit" value="<?= lang('ledgers_views_add_label_submit_btn'); ?>" class="btn btn-primary  pull-right">
                    <a href="<?=admin_url(); ?>accounts" class="btn btn-default pull-right" style="margin-right: 5px;"><?= lang('ledgers_views_edit_label_cancel_btn'); ?></a>
                </div>
            </div>
            <?= form_close(); ?>
            </div>
        </div>
     		
     	</div>
     </div>
</div>