<div class="box">
	<div class="box-header">
        <h2 class="blue"><i
                class="fa-fw fa fa-barcode"></i><?= lang('Check Status') ?>
        </h2>

        <div class="box-icon">
            
        </div>
    </div>
    <div class="box-content">
     	<div class="row">
            <div class="col-lg-12">

                <p class="introtext"><?php echo lang('enter_info'); ?></p>
                <?php
                $attrib = ['data-toggle' => 'validator', 'role' => 'form'];
                echo admin_form_open_multipart('purchases/searchByReference', $attrib);
                ?>
                <div class="row">
                    <div class="col-lg-12">

                        <div class="col-md-8">
                            <div class="form-group">
                            <?= lang('Search By Reference No', 'podate'); ?>
                                <?php echo form_input('reference_no', ($_POST['reference_no'] ?? ''), 'class="form-control input-tip" placeholder="Reference No" id="reference_no" required="required"'); ?>
                            </div>
                        </div>
 
                            <div class="col-md-4">
                            <div class="from-group" style="margin-top:13px;"> 
                                <?php echo form_submit('Search', $this->lang->line('Search'), 'id="add_truck" class="btn btn-primary" style="padding: 6px 15px; margin:15px 0;"'); ?>
                            </div>
                           </div>
                    
                        
                    </div>
                </div>

                <?php
                 echo form_close(); ?>



                <?php
                $attrib = ['data-toggle' => 'validator', 'role' => 'form'];
                echo admin_form_open_multipart('purchases/searchByDate', $attrib);
                ?>
                <div class="row">
                    <div class="col-lg-12">

                      <div class="col-sm-4">
                            <div class="form-group">
                                <?= lang('start_date', 'start_date'); ?>
                                <?php echo form_input('start_date', (isset($_POST['start_date']) ? $_POST['start_date'] : ''), 'class="form-control datetime" id="start_date"'); ?>
                            </div>
                        </div>

                        <div class="col-sm-4">
                            <div class="form-group">
                                <?= lang('end_date', 'end_date'); ?>
                                <?php echo form_input('end_date', (isset($_POST['end_date']) ? $_POST['end_date'] : ''), 'class="form-control datetime" id="end_date"'); ?>
                            </div>
                        </div>
                        

                        <div class="col-md-4">
                            <div class="from-group" style="margin-top:13px;"> 
                                <?php echo form_submit('Search', $this->lang->line('Search'), 'id="add_truck" class="btn btn-primary" style="padding: 6px 15px; margin:15px 0;"'); ?>
                            </div>
                           </div>
                    
                        
                    </div>
                </div>

                <?php
                 echo form_close(); ?>
               
            </div>
     		
     	</div>
     </div>
</div>
