<?php  defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-cog"></i><?= lang('Add_blog'); ?></h2>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">

                <p class="introtext"><?= lang('enter_info'); ?></p>

                <?php $attrib = ['data-toggle' => 'validator', 'role' => 'form'];
                echo admin_form_open_multipart('blog/add_blog', $attrib );
                ?>
                
                <div class="row">
                    <div class="col-md-12">
                        <div class="col-md-6">
                            <div class="form-group">
                                <?= lang('name', 'name'); ?>
                                <?= form_input('name', set_value('name'), 'class="form-control" id="name" pattern=".{3,15}" required="" data-fv-notempty-message="' . lang('title_required') . '"'); ?>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <?= lang('slug', 'slug'); ?>
                                <?= form_input('slug', set_value('slug'), 'class="form-control" id="slug" required="" data-fv-notempty-message="' . lang('slug_required') . '"'); ?>
                            </div>
                        </div>
                        <div class="clearfix"></div>

                        <div class="col-md-8">
                            <div class="form-group">
                                <?= lang('title', 'title'); ?>
                                <?= form_input('title', set_value('title'), 'class="form-control" id="title" pattern=".{3,60}" required="" data-fv-notempty-message="' . lang('title_required') . '"'); ?>
                            </div>
                        </div>
                     <!--<div class="form-group">-->
                                    
                                    <?php
                                //    $cat[''] = lang('select') . ' ' . lang('category');
                                 //   foreach ($categories as $pcat) {
                                  //      $cat[$pcat->id] = $pcat->name;
                                  //  }
                                  //  echo form_dropdown('category', $cat, (isset($_POST['parent']) ? $_POST['parent'] : $category->parent_id), 'class="form-control select" id="category" style="width:100%"')
                                    ?>
                                <!--</div>-->
                        <div class="col-md-4">
                            <div class="form-group">
                                  <?= lang('category', 'category'); ?>
                                <select name="category" class="form-control">
                                 
                                       <?php   foreach($result as $row) { ?>
<?php 
                                        
                                        echo '<option>'.$row->name.'</option>' ;
                                        
                                    
                                      
                                      ?>
                                 
                                    
                                   
                                    <?php  } ?>
                               
                             
                                </select>
                            </div>
                        </div>
                        <div class="clearfix"></div>

                        <div class="col-md-12">
                            <div class="form-group">
                                <?= lang('description', 'description'); ?>
                                <?= form_input('description', set_value('description'), 'class="form-control" id="description" required="" data-fv-notempty-message="' . lang('description_required') . '"'); ?>
                            </div>
                            <div class="form-group">
                                <?= lang('body', 'body'); ?>
                                <?= form_textarea('body', set_value('body'), 'class="form-control body" id="body" required="" data-fv-notempty-message="' . lang('body_required') . '"'); ?>
                            </div>
                                <div class="form-group">
                                                <?= lang('featured_image', 'image') ?>
                                                 <input id="image" type="file" data-browse-label="<?= lang('browse'); ?>" name="userfile" data-show-upload="false" data-show-preview="false" class="form-control file">
                                  </div>
                         
                            <?php echo form_submit('add_blog', lang('add_blog'), 'class="btn btn-primary"'); ?>
                        </div>

                    </div>
                </div>

                <?php echo form_close(); ?>
            </div>
        </div>
    </div>
</div>
