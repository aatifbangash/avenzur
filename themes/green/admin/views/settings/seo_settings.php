
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-cog"></i><?= lang('SEO_Settings'); ?></h2>

        <div class="box-icon">
           
        </div>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">

                <p class="introtext"><?= lang('update_info'); ?></p>

                <?php $attrib = ['role' => 'form', 'id="seo_setting"'];
                echo admin_form_open('seo_setting/update', $attrib);
                ?>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <?= lang('title', 'title'); ?>*<br>
                            <input type="text" name="title" value="<?php echo $seo_settings->title; ?>">
                        </div>

                        <div class="form-group">
                            <?= lang('description', 'description'); ?>*
                            <textarea name="description"><?php echo $seo_settings->description; ?></textarea>

                        </div>
                      
                        <div class="form-group">
                            <?= lang('keywords', 'keywords'); ?>*
                            <textarea name="keywords"><?php echo $seo_settings->keywords; ?></textarea>
                        </div>
                        
                    </div>
                </div>
                <div style="clear: both; height: 10px;"></div>
                <div class="form-group">
                    <?php echo form_submit('update_settings', lang('update_settings'), 'class="btn btn-primary"'); ?>
                </div>
            </div>
            <?php echo form_close(); ?>
        </div>
    </div>
</div>

<!-- <form method="post" action="<?php echo site_url('admin_seo/update'); ?>">
    <label>Title:</label>
    <input type="text" name="title" value="<?php echo $seo_settings->title; ?>">

    <label>Description:</label>
    <textarea name="description"><?php echo $seo_settings->description; ?></textarea>

    <label>Keywords:</label>
    <textarea name="keywords"><?php echo $seo_settings->keywords; ?></textarea>

    <button type="submit">Save</button>
</form> -->