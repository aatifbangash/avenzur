<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<?= form_open('login', 'class="validate" id="loginForm"'); ?>
<div class="row" id="login">
    <div class="col-md-12">
        <div class="form-group">
            <?php if (!$shop_settings->private) {
    ?>
                <a href="<?= site_url('login#register'); ?>" class="pull-right text-blue" onclick="registerBtn()"><?= lang('register'); ?></a>
            <?php
} ?>
            <?php $u = mt_rand(); ?>
            <label for="username<?= $u; ?>" class="control-label"><?= lang('identity'); ?></label>
            <input type="text" name="identity" id="username<?= $u; ?>" class="form-control" value="" required placeholder="<?= lang('Email or Mobile'); ?>">
        </div>
        
        <button id="loginBtnCall" value="login" name="login" class="btn btn-block btn-success"><?= lang('login'); ?></button>
    </div>
</div>
<?= form_close(); ?>

