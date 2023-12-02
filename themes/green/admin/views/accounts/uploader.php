<div class="box">
	<div class="box-header">
        <h2 class="blue"><i
                class="fa-fw fa fa-barcode"></i><?= lang('accounts_importer_heading') ?>
        </h2>

        <div class="box-icon">
            
        </div>
    </div>
    <div class="box-content">
     	<div class="row">
            <section class="content">
    <div class="box">
        
        <!-- /.box-header -->
        <div class="box-body">
            <?php echo form_open_multipart('admin/accounts/uploader'); ?>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="accountcsv"><?= 'Select CSV File:'; ?></label>
                        <input type="file" name="accountcsv" id="accountcsv">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="well well-lg">
                        <a href="<?php echo base_url(); ?>assets/csv/import_chart_of_accounts.csv" class="btn btn-primary"><?=lang('accounts_importer_sample_button');?></a>
                    </div>
                </div>
            </div>
        </div>
        <!-- /.box-body -->
        <div class="box-footer">
            <button type="submit" class="btn btn-primary pull-right"><?=lang('accounts_importer_submit_button');?></button>
            <a href="<?= admin_url(); ?>accounts/index" id="cancel" name="cancel" class="btn btn-default pull-right" style="margin-right: 5px;"><?=lang('accounts_importer_cancel_button');?></a>
        </div>
        <!-- /.box-footer -->
        <?php echo form_close(); ?>
    </div>
    <!-- /.box -->
</section>
<!-- /.content -->
     		
     	</div>
     </div>
</div>