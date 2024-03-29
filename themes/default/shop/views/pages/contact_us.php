<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<section class="page-contents">
  <div class="container">
    <div class="row">
      <div class="col-xs-12">

        <div class="row">
          <div class="col-sm-9 col-md-10">
            <div class="panel panel-default margin-top-lg">
              <div class="panel-heading text-bold">
                <?= $title; ?>
              </div>
              <div class="panel-body">
                <?php
                // Check if there's a success message in flash data
                $successMessage = $this->session->flashdata('success_message');
                if (!empty($successMessage)) {
                  echo '<div class="alert alert-success" role="alert">' . $successMessage . '</div>';
                }
                ?>
                <?php echo form_open('page/contact-us'); ?>
                <div class="form-group">
                  <label for="type">Type:</label>
                  <?php
                  $options = array(
                    'cancel' => 'Cancel',
                    'return' => 'Return',
                    'refund' => 'Refund'
                  );
                  echo form_dropdown('type', $options, '', 'class="form-control"');
                  ?>
                </div>
                <div class="form-group">
                  <label for="content">Content:</label>
                  <?php
                  $textarea_data = array(
                    'name' => 'content',
                    'class' => 'form-control',
                    "required" => true,
                    'rows' => '5'
                  );
                  echo form_textarea($textarea_data);
                  ?>
                </div>
                <?php echo form_hidden('formSubmitted', '1'); ?>
                <?php echo form_submit('submit', 'Submit', 'class="btn btn-primary"'); ?>
                <?php echo form_close(); ?>
              </div>
            </div>
          </div>

          <div class="col-sm-3 col-md-2">
            <?php include 'sidebar2.php'; ?>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
