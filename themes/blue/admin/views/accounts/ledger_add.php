<script type="text/javascript">
$(document).ready(function() {
	$('#LedgerGroupId').trigger('change');
	$("#LedgerGroupId").select2({width:'100%'});
});

	function getLedgerNumber() {
		var id = $("#LedgerGroupId option:selected").val()
		$.ajax({
	    	type:"POST",
	        url: "<?=admin_url(); ?>" + "ledgers/getNextCode",
	    	data: { id,"<?= $this->security->get_csrf_token_name() ?>":"<?= $this->security->get_csrf_hash() ?>" }
	    }).done(function(msg){
	    	$('#l_code').val(msg);
	    });
	}
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
                class="fa-fw fa fa-barcode"></i><?= lang('Add Ledger') ?>
        </h2>

        <div class="box-icon">
            
        </div>
    </div>
    <div class="box-content">
     	<div class="row">
     		<div class="col-xs-12">
          <div class="box">
            <div class="box-header with-border">
              <h3 class="box-title"><?= lang('ledgers_views_add_title'); ?></h3>
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
									<label><?= lang('ledgers_views_add_label_parent_group'); ?></label>
									<?= form_dropdown('group_id', $parents, set_value('group_id'), array('id' => 'LedgerGroupId', 'class'=>'form-control', 'onchange'=>"getLedgerNumber()")); ?>
								</div>
							</div>
							<div class="col-md-3">
								<div class="form-group">
									<label><?= lang('ledgers_views_add_label_ledger_code'); ?></label>
									<input type="text" name="code" id="l_code" value="<?= set_value('code');?>" class="form-control">
								</div>
							</div>
							<div class="col-md-4">
								<div class="form-group">
									<label><?= lang('ledgers_views_add_label_ledger_name'); ?></label>
									<input type="text" name="name" value="<?= set_value('name');?>" class="form-control">
								</div>
							</div>
						</div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label><?= lang('Type1'); ?></label>
								<!-- <input type="text" name="type1" id="l_type1" value="<?= set_value('type1');?>" class="form-control"> -->
                                <?= form_dropdown('type1', $accountTypeOne, set_value('type1', $ledger['type1']),array('class' => 'form-control', 'id'=>'l_type1')); ?>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label><?= lang('Type2'); ?></label>
                                <!-- <input type="text" name="type2" id="l_type2" value="<?= set_value('type2');?>" class="form-control"> -->
								<?= form_dropdown('type2', $accountTypeTwo, set_value('type2', $ledger['type2']),array('class' => 'form-control', 'id'=>'l_type2')); ?>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label><?= lang('Category'); ?></label>
                                <!-- <input type="text" name="category" value="<?= set_value('category');?>" class="form-control"> -->
								<?= form_dropdown('category', $accountCategories, set_value('category', $ledger['category']),array('class' => 'form-control', 'id'=>'category')); ?>
                            </div>
                        </div>
                    </div>

						<div class="form-group">
							<label><?= lang('ledgers_views_add_label_op_blnc'); ?></label>
							<div class="row">
								<div class="col-md-12">
									<div class="row">
										<div class="col-md-4">
											<?= form_dropdown('op_balance_dc', array('D' => lang('entries_views_addrow_label_dc_drcr_D'), 'C' => lang('entries_views_addrow_label_dc_drcr_C')), set_value('op_balance_dc'), array('class'=>'form-control')); ?>
										</div>
										<div class="col-md-8">
											<div class="form-group">
							                    <div class="input-group">
													<input type="number" value="<?= set_value('op_balance'); ?>" name="op_balance" class="form-control">
							                        <div class="input-group-addon">
							                            <i>
						                                	<div class="fa fa-info-circle" data-toggle="tooltip" title="<?= lang('ledgers_views_add_op_blnc_tooltip'); ?>">
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
									<label><input  type="checkbox" name="type" class="form-control" <?= set_value('type') ? 'checked' : '' ?>><?= lang('ledgers_views_add_label_bank_cash_account'); ?></label>
									<div class="input-group-addon">
										<i>
											<div class="fa fa-info-circle" data-toggle="tooltip" title="<?= lang('ledgers_views_add_bank_cash_account_tooltip'); ?>">
											</div>
										</i>
									</div>
								</div>
								<!-- /.input group -->
			                </div>
			                <!-- /.form group -->
			                <div class="form-group">
			                    <div class="input-group">
			                    	<label><input type="checkbox" name="reconciliation" class="form-control" <?= set_value('reconciliation') ? 'checked' : '' ?>><?= lang('entries_views_add_label_reconciliation'); ?></label>

			                        <div class="input-group-addon">
			                            <i>
			                                <div class="fa fa-info-circle" data-toggle="tooltip" title="<?= lang('ledgers_views_add_reconciliation_tooltip'); ?>">
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
								<label><?= lang('ledgers_views_add_label_notes'); ?></label>
								<textarea name="notes" rows="3" class="form-control"><?= set_value('notes') ?></textarea>
							</div>
						</div>
					</div>
				</div>
            </div>
            <div class="box-footer">
            	<div class="form-group">
					<input type="submit" name="Submit" value="<?= lang('ledgers_views_add_label_submit_btn'); ?>" class="btn btn-primary  pull-right">
					<a href="<?=admin_url(); ?>accounts" class="btn btn-default pull-right" style="margin-right: 5px;"><?= lang('ledgers_views_add_label_cancel_btn'); ?></a>
				</div>
		    </div>
		    <?= form_close(); ?>
        </div>

     	</div>
     </div>
</div>