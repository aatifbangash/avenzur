<div class="box">
	<div class="box-header">
        <h2 class="blue"><i
                class="fa-fw fa fa-barcode"></i><?= lang('Add Truck Registration') ?>
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
                echo admin_form_open_multipart('truck_registration/save', $attrib);
                ?>
                <div class="row">
                    <div class="col-lg-12">
                        <div class="col-md-6">
                            <div class="form-group">
                                <?= lang('Truck Number*', 'Truck No.'); ?>
                                <?php echo form_input('truck_no', ($_POST['truck_no'] ?? ''), 'class="form-control input-tip" id="truck_no" required="required"'); ?>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <?= lang('date', 'podate'); ?>
                                <?php echo form_input('ddate', ($_POST['ddate'] ?? ''), 'class="form-control input-tip date" id="truck_date" required="required"'); ?>
                            </div>
                        </div>


                        <div class="col-md-6">
                            <div class="form-group">
                                <?= lang('Time', 'potime'); ?>
                                <input type="time" value="<?php time(); ?>" class="form-control input-tip" name="truck_time" id="truck_time" required>
                            </div>
                        </div>


                           <div class="col-md-6">
                            <div class="form-group">
                                <?= lang('Reference No', 'poreference'); ?>
                                <select id="reference_no" name="reference_no" class="form-control input-tip select" required="required">
                                <option value="">Select (Reference / Supplier / Date)</option>
                                <?php
                                $sp[''] = '';
                                foreach ($purchase as $purchase) {
                                $purchaseId = $purchase->reference_no.'@/'.$purchase->id;
                                $reference_no = $purchase->reference_no;
                                   echo "<option value='$purchaseId'>$reference_no / $purchase->supplier / $purchase->date</option>";
                              
                                }
                                ?>
                                </select>
                                <!-- echo form_dropdown('reference_no', $sp, ($_POST['reference_no'] ?? ''), 'id="reference_no" class="form-control input-tip select" data-placeholder="' . lang('select') . ' ' . lang('reference') . '" required="required"'); ?> -->
                            
                                </div>
                            </div>

                         
                        
                            <div class="col-md-12">
                            <div
                            class="from-group"><?php echo form_submit('add_truck', $this->lang->line('submit'), 'id="add_truck" class="btn btn-primary" style="padding: 6px 15px; margin:15px 0;"'); ?>
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
