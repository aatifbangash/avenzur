<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<section class=" py-3 " style="margin-bottom: 50px;">
        <div class="container container-max-width">
            <div class="brad-crumb py-3">
                <nav aria-label="breadcrumb">
                  <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="#" class="text-decoration-none">Home</a></li>
                    <li class="breadcrumb-item " aria-current="page"><a href="#" class="text-decoration-none">My Account</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Register</li>
                  </ol>
                </nav>
            </div>
            <h3>Register</h3>
        <div class="p-md-5 p-3 registerform">
            <?php $attrib = ['class' => 'validate', 'role' => 'form'];
            echo form_open('register', $attrib); ?>
            <div class="row">
                <div class="col-md-6">
                
                    <div class="mb-3">
                        <label for="firstName" class="form-label">First Name</label>
                        <input type="text" class="form-control" id="firstName" name="first_name" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="lastName" class="form-label">Last Name</label>
                        <input type="text" class="form-control" id="lastName" name="last_name" required>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="country" class="form-label">Country</label>
                        <select class="form-select" id="country" name="country" required>
                        <?php
                            foreach($country as $country)
                            {
                                $selected = (trim($country->code) == trim($country_code)) ? 'selected' : '';
                                echo '<option value="'.$country->code.'"' . $selected . '>'.$country->name.'</option>';
                            }
                        ?>  
                        </select>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="phone" class="form-label">Phone</label>
                        <div class="input-group">
                        
                        <input type="tel" name="phone" class="form-control" id="phone" required/> 
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="username" class="form-label">Username</label>
                        <input type="text" class="form-control" id="username" name="username" required>
                    </div>
                </div>
            </div>

              
            
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="confirmPassword" class="form-label">Confirm Password</label>
                        <input type="password" class="form-control" id="confirmPassword" name="password_confirm" required>
                    </div>
                </div>
            </div>      
            
                  
            <button type="submit" name="register" class="btn primary-buttonAV  rounded-1 pb-2">Register</button>
            <span class="mt-3 ms-4">Already have an account? 
                <a id="login" onclick="LoginFn()" name="login" value="Login" href="#">Log in here</a>
            </span>
            <?= form_close(); ?>
        </div>
        
        </div>
    </div>
</section>

 <script>
    // Vanilla Javascript
    var input = document.querySelector("#phone");
    window.intlTelInput(input,({
        initialCountry: "<?= trim($country_code); ?>"
    }));
function LoginFn(){
             
         
                  $(".register").hide();
                    $("#register").hide();
                  $(".loginform").show();
     
           
}
function registerBtn(){
     $(".loginform").hide();
                $(".register").show();
                    $("#register").show();
                 
}


    $(document).ready(function() {
        $('.iti__flag-container').click(function() { 
          var countryCode = $('.iti__selected-flag').attr('title');
          var countryCode = countryCode.replace(/[^0-9]/g,'')
          $('#phone').val("");
          $('#phone').val("+"+countryCode+" "+ $('#phone').val());
       });
 
         
    });
         

  </script>