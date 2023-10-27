<div class="box">
	<div class="box-header">
        <h2 class="blue"><i
                class="fa-fw fa fa-barcode"></i><?= lang('Add Deals') ?>
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
                echo admin_form_open_multipart('deals/add', $attrib);
                if($suppliers){
                    //var_dump($suppliers)
                ?>
                <div class="row">
                    <div class="col-lg-12">
                        <div class="col-md-4">
                            <div class="form-group">
                                <?= lang('Deal Number*', 'Deal No.'); ?>
                                <?php echo form_input('deal_no', ($_POST['deal_no'] ?? $dealnumber), 'class="form-control input-tip" id="dealno" required="required"'); ?>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <?= lang('supplier', 'posupplier'); ?>
                                <?php

                                    $sp[''] = '';

                    foreach ($suppliers as $supplier) {
                        $sp[$supplier->id] = $supplier->company;
                    }
                    echo form_dropdown('supplier', $sp, ($_POST['supplier'] ?? $selctedsupplier), 'id="suppliers" class="form-control input-tip select" data-placeholder="' . lang('select') . ' ' . lang('supplier') . '" required="required"'); ?>
                                </div>
                            </div>
                        
                        <div class="col-md-4">
                            <div class="form-group">
                                <?= lang('date', 'podate'); ?>
                                <?php echo form_input('ddate', ($_POST['ddate'] ?? ''), 'class="form-control input-tip date" id="dealdate" required="required"'); ?>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <?= lang('Product Discount on Sales Value (%)','productdiscount'); ?>
                                <?php echo form_input('pdiscount', ($_POST['pdiscount'] ?? ''), 'class="form-control input-tip" id="pdiscount" required="required"'); ?>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <?= lang('Sales Value','saleval'); ?>
                                <?php echo form_input('salesval', ($_POST['salesval'] ?? ''), 'class="form-control input-tip" id="salesval" required="required"'); ?>
                            </div>
                        </div>

                         <div class="col-md-4">
                            <div class="form-group">
                                <?= lang('Product Discount on Each Purchase Order (%)','productdiscountpurchase'); ?>
                                <?php echo form_input('pdiscountporder', ($_POST['pdiscountporder'] ?? ''), 'class="form-control input-tip" id="pdiscountporder" required="required"'); ?>
                            </div>
                        </div>
                         <div class="col-md-12">
                            <div
                                class="from-group"><?php echo form_submit('add_deal', $this->lang->line('submit'), 'id="add_deal" class="btn btn-primary" style="padding: 6px 15px; margin:15px 0;"'); ?>
                                
                            </div>
                        </div>

                        
                    </div>
                </div>
                <?php
                 }else{ echo 'No Supplier Left.';}
                 echo form_close(); ?>
            </div>
     		
     	</div>
     </div>
</div>
