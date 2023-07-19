<?php defined('BASEPATH') or exit('No direct script access allowed'); ?><!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= lang('pos_module') . ' | ' . $Settings->site_name; ?></title>
    <link rel="stylesheet" href="<?= $assets ?>styles/theme.css" type="text/css"/>
    <link rel="stylesheet" href="<?= $assets ?>styles/style.css" type="text/css"/>
    <link rel="stylesheet" href="<?= $assets ?>pos/css/posajax.css" type="text/css"/>
</head>

<body>
<div id="wrapper">
    <header id="header" class="navbar">
        <div class="container">
            <a class="navbar-brand" href="#"><span class="logo"><span
                            class="pos-logo-lg"><?= $Settings->site_name ?></span><span
                            class="pos-logo-sm"><?= lang('pos') ?></span></span></a>
        </div>
    </header>

    <div id="content">
        <div class="box">

            <?php if (isset($payload['company_added'])) { ?>
                <div class="box-header">
                    <h2 class="blue"><i class="fa-fw fa fa-barcode"></i>New Company Added</h2>
                    <div class="box-icon"></div>
                </div>
                <div class="box-content">
                    <div class="row">
                        <div class="col-lg-12">
                            <p class="introtext">Below is your company name and login admin panel. You can
                                copy the details.</p>
                            <?php $attrib = ['class' => 'form-horizontal', 'data-toggle' => 'validator', 'role' => 'form'];
                            echo admin_form_open('company/index', $attrib);
                            ?>
                            <div class="mb-3">
                                <label for="company_name" class="form-label">Company
                                    Name:- <?php echo $payload['row']['company_name'] ?></label>
                            </div>
                            <div class="mb-3">
                                <label for="owner_password" class="form-label">Admin URL:-</label>
                                <a href="<?php echo admin_url("login/" . ($payload['row']['company_id'] * 999)); ?>"><?php echo admin_url("login/" . ($payload['row']['company_id'] * 999)); ?></a>
                            </div>
                            <?php echo form_close(); ?>
                        </div>
                    </div>
                </div>
            <?php } else { ?>
                <div class="box-header">
                    <h2 class="blue"><i class="fa-fw fa fa-barcode"></i>Add New Company </h2>

                    <div class="box-icon">

                    </div>
                </div>
                <div class="box-content">
                    <div class="row">
                        <div class="col-lg-12">
                            <p class="introtext">Please fill in the information below. The field labels marked with *
                                are
                                required input fields.</p>
                            <?php $attrib = ['class' => 'form-horizontal', 'data-toggle' => 'validator', 'role' => 'form'];
                            echo admin_form_open('company/index', $attrib);
                            ?>
                            <div class="mb-3">
                                <label for="company_name" class="form-label">Company Name*</label>
                                <input type="text" class="form-control" id="company_name" name="company_name"
                                       placeholder="Enter your company name"
                                       value="<?php echo $_POST['company_name'] ?? ''; ?>"
                                >
                                <?php if (!empty($_POST) && empty($_POST['company_name'])) { ?>
                                    <div class="invalid-feedback" style="color: red;">Please enter company name.</div>
                                <?php } ?>
                            </div>
                            <button type="submit" class="btn btn-primary">Submit</button>
                            <?php echo form_close(); ?>
                        </div>
                    </div>
                </div>
            <?php } ?>
        </div>
    </div>
</div>
</body>
</html>
