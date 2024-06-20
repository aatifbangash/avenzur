<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<section class="page-contents" id="contact-us-section"  style="background:white !important;">
  <div class="container container-max-width">
    


   <div class="row align-items-stretch">
         <div class="col-lg-4 mb-4">
            <h3 class="mb-3 fw-semibold">Information </h3>
            <?php //include 'sidebar2.php'; ?>
             <div class="panel panel-default margin-top-lg checkLeftCol-k contleftbar">
                 
                <div class="p-3 py-4 contleft">
                  <h4><i class="bi bi-telephone-forward"></i> 0114654636</h4>
                  <h4><i class="bi bi-envelope"></i> info@avenzur.com</h4>
                  <h4 class="mb-1"><i class="bi bi-geo-alt"></i> KSA, 6675, Olaya, Olaya District, 2628 </h4>
             
                  <!--<p class="px-2">
                    Riyadh ,Olaya main road,Mousa bin nosair street.
                    Silicon building no.1, Office 7
                  </p>-->
                  <h4 class="text-center contleftsocial mt-4 d-flex flex-wrap mb-0">
                    <a href="https://www.facebook.com/people/Avenzur/61553215776690/?mibextid=9R9pXO" class="text-dark text-decoration-none "> <i class="bi bi-facebook"></i></a> 
                    <a href="https://www.linkedin.com/company/avenzur/?viewAsMember=true" class="text-dark text-decoration-none "> <i class="bi bi-linkedin"></i></a>
                    
                    <a href="https://x.com/avenzurworld?s=11" class="text-dark text-decoration-none "> <i class="bi bi-twitter"></i></a>
                    <a href="https://www.instagram.com/avenzurworld?igsh=MmZicnZnZHZ2aGhl" class="text-dark text-decoration-none"><i class="bi bi-instagram"></i></a> 
                    <a href="https://www.tiktok.com/@avenzur?_t=8i4BKIMkJVK&_r=1" class="text-dark text-decoration-none "><i class="bi bi-tiktok"></i></a>
                </h4>
                </div>
               
             </div>
            
          </div>
          
         <div class="col-lg-8 ">
          <h3 class="mb-3 fw-semibold">
               <!-- <i class="bi bi-person-fill"></i>-->
                <?= $title; ?>
          </h3>
            <div class="panel panel-default margin-top-lg checkLeftCol-k ">
            
              <div class="panel-body">
                <?php
                // Check if there's a success message in flash data
                $successMessage = $this->session->flashdata('success_message');
                if (!empty($successMessage)) {
                  echo '<div class="alert alert-success" role="alert">' . $successMessage . '</div>';
                }
                ?>
                <?php echo form_open('page/contact-us', 'class="validate" id="contact-us"'); ?>
                <div class="form-group">
                  <label for="type">Query Type:</label>
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
                  <label for="content">Your Message:</label>
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
                <?php echo form_submit('submit', 'Submit', 'class="btn btn-lg btn-primary mt-3"'); ?>
                <?php echo form_close(); ?>
              </div>
            </div>
          </div>

          
  </div>
     
    
       
   
     <div class="my-4 contmap"><iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d57985.67709311361!2d46.62304628135416!3d24.72328148846339!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3e2ee380679744a9%3A0xf35ac2694fe69342!2sOlaya%20St%2C%20Riyadh%20Saudi%20Arabia!5e0!3m2!1sen!2s!4v1699297169005!5m2!1sen!2s" width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe></div>


     </div>




    </section>
