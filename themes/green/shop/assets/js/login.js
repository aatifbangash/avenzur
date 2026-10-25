
$(document).ready(function () {
  $("#loginOtpForm").submit(function (event) {
    event.preventDefault(); // Prevent the default form submission

    // Your Ajax code here
    $.ajax({
      type: "POST",
      url: site.base_url + "login_otp",
      data: $(this).serialize(),
      success: function (response) {
        // Handle the Ajax success response
        //console.log('Response:', response);

        // Check the status in a case-insensitive manner
        if (response.status && response.status.toLowerCase() === "error") {
          //console.log('Error condition met');
          $("#error").text(response.message);
          // Highlight the input fields with a red border
          $(".inputs input").addClass("error-border");
        } else if (response.redirect) {
          // Log success or other status
          //console.log('Success or other status:', response.redirect);
          window.location.href = response.redirect;
        } else {
          window.location.href = site.base_url;
        }
      },
      error: function (error) {
        // Handle the Ajax error
        //console.error('error', error);
      },
    });
  });
});

//copy paste
