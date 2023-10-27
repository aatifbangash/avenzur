<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-cog"></i><?= lang('Edit_Country'); ?></h2>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">

                <p class="introtext"><?= lang('update_info'); ?></p>

                <?php $attrib = ['data-toggle' => 'validator', 'role' => 'form'];
                echo admin_form_open_multipart('system_settings/edit_country/' . $page->id, $attrib);
                ?>
                
                <div class="row">
                    <div class="col-md-12">
                        <div class="col-md-6">
                            <div class="form-group">
                                <?= lang('name', 'name'); ?>
                                <?= form_input('name', set_value('name', $page->name), 'class="form-control" id="name" pattern=".{3,15}" required="" data-fv-notempty-message="' . lang('title_required') . '"'); ?>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <?= lang('Country Code', 'Country Code'); ?>
                                <?= form_input('code', set_value('slug', $page->code), 'class="form-control" id="code" required="" data-fv-notempty-message="' . lang('code_required') . '"'); ?>
                            </div>
                        </div>
                 

               
                           

                            <?php echo form_submit('edit_country', lang('edit_country'), 'class="btn btn-primary"'); ?>
                        </div>

                    </div>
                </div>

                <?php echo form_close(); ?>
            </div>
        </div>
    </div>
</div>
