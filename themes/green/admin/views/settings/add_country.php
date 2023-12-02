<?php  defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-cog"></i><?= lang('Add_Country'); ?></h2>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">

                <p class="introtext"><?= lang('enter_info'); ?></p>

                <?php $attrib = ['data-toggle' => 'validator', 'role' => 'form'];
                echo admin_form_open_multipart('system_settings/add_country', $attrib );
                ?>
                
                <div class="row">
                    <div class="col-md-12">
                        <div class="col-md-6">
                            <div class="form-group">
                                <?= lang('name', 'name'); ?>
                                <?= form_input('name', set_value('name'), 'class="form-control" id="name" pattern=".{3,15}" required="" data-fv-notempty-message="' . lang('name_required') . '"'); ?>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <?= lang('Country Code', 'Country Code'); ?>
                                <?= form_input('code', set_value('code'), 'class="form-control" id="slug"   pattern=".{1,2}" required="" data-fv-notempty-message="' . lang('code_required') . '"'); ?>
                            </div>
                        </div>
            
                      
                         
                            <?php echo form_submit('add_country', lang('Add Country'), 'class="btn btn-primary"'); ?>
                        </div>

                    </div>
                </div>

                <?php echo form_close(); ?>
            </div>
        </div>
    </div>
</div>
