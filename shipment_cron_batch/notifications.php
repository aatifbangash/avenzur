<?php
function send_sms($phone, $message) {
    // Implement your SMS provider here
    // Example: file_get_contents("https://smsprovider.com/send?to=$phone&msg=" . urlencode($message));
    echo "SMS sent to $phone: $message\n";
}

function send_email($to, $subject, $message) {
    // Use mail() or any email library like PHPMailer
    echo "Email sent to $to: $subject - $message\n";
}
