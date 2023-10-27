<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-cog"></i><?= lang('edit_blog'); ?></h2>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">

                <p class="introtext"><?= lang('update_info'); ?></p>

                <?php $attrib = ['data-toggle' => 'validator', 'role' => 'form'];
                echo admin_form_open_multipart('blog/edit_blog/' . $page->id, $attrib);
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
                                <?= lang('slug', 'slug'); ?>
                                <?= form_input('slug', set_value('slug', $page->slug), 'class="form-control" id="slug" required="" data-fv-notempty-message="' . lang('slug_required') . '"'); ?>
                            </div>
                        </div>
                        <div class="clearfix"></div>

                        <div class="col-md-8">
                            <div class="form-group">
                                <?= lang('title', 'title'); ?>
                                <?= form_input('title', set_value('title', $page->title), 'class="form-control" id="title" pattern=".{3,60}" required="" data-fv-notempty-message="' . lang('title_required') . '"'); ?>
                            </div>
                        </div>
                
                        <div class="col-md-4">
                            <div class="form-group">
                                <?= lang('category', 'category'); ?>
                                <?= form_input('category', set_value('category', $page->category), 'class="form-control" id="category" required=""'); ?>
                            </div>
                        </div>
                        <div class="clearfix"></div>

                        <div class="col-md-12">
                            <div class="form-group">
                                <?= lang('description', 'description'); ?>
                                <?= form_input('description', set_value('description', $page->description), 'class="form-control" id="description" required="" data-fv-notempty-message="' . lang('description_required') . '"'); ?>
                            </div>
                            <div class="form-group">
                                <?= lang('body', 'body'); ?>
                                <?= form_textarea('body', $page->body, 'class="form-control body" id="body" required="" data-fv-notempty-message="' . lang('body_required') . '"'); ?>
                            </div>
                           <div class="form-group">
                                    <?= lang('featured_image', 'image') ?>
                                     <input id="image" type="file" data-browse-label="<?= lang('browse'); ?>" name="userfile" data-show-upload="false" data-show-preview="false" class="form-control file">
                            </div>
                           

                            <?php echo form_submit('edit_blog', lang('edit_blog'), 'class="btn btn-primary"'); ?>
                        </div>

                    </div>
                </div>

                <?php echo form_close(); ?>
            </div>
        </div>
    </div>
</div>
