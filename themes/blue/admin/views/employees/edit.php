<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-barcode"></i><?= lang('Edit Department') ?>
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
                echo admin_form_open_multipart('employees/edit/' . $employee->id, $attrib);

                ?>
                <div class="row">
                    <div class="col-lg-12">

                        <!-- <div class="col-md-4">
                            <div class="form-group">
                                <?= lang('Parent Department', 'parent_department'); ?>
                                <?php

                                $depth[''] = '';

                                foreach ($departments as $department) {
                                    $depth[$department->id] = $department->name . (!empty($department->code) ? ' - (' . $department->code . ')' : '');
                                }
                                echo form_dropdown('parent_department', $depth, ($employee->id), 'id="parent_department" class="form-control input-tip select" data-placeholder="' . lang('select') . ' ' . lang('Parent Department') . '" required="required"'); ?>
                            </div>
                        </div> -->


                        <div class="col-md-4">
                            <div class="form-group">
                                <?= lang('Employee Name*', 'name'); ?>
                                <?php echo form_input('name', ($employee->name), 'class="form-control name-tip" id="dealno" required="required"'); ?>
                            </div>
                        </div>



                        <div class="col-md-12">
                            <div class="from-group"><?php echo form_submit('add_deal', $this->lang->line('submit'), 'id="add_deal" class="btn btn-primary" style="padding: 6px 15px; margin:15px 0;"'); ?>

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