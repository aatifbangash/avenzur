<?Php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/phpmailer/phpmailer/src/Exception.php';
require 'vendor/phpmailer/phpmailer/src/PHPMailer.php';
require 'vendor/phpmailer/phpmailer/src/SMTP.php';

$pass = 'bpnzmdrbhwbrxclc' ;
// Create a new PHPMailer instance
$mail = new PHPMailer(true);

try {
    //Server settings
    // $mail->isSMTP();
    // $mail->Host       = 'smtp.gmail.com';
    // $mail->SMTPAuth   = true;
    // $mail->Username   = 'aleemktk@gmail.com'; // Your Gmail address
    // $mail->Password   = $pass; // Your Gmail password
    // $mail->SMTPSecure = 'tls';
    // $mail->Port       = 587;

    $mail->isSMTP();
    $mail->Host       = 'mail.tododev.xyz';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'info@tododev.xyz';
    $mail->Password   = 'AvenzurPass@2023'; // Use the email account’s password
    $mail->SMTPSecure = 'ssl';
    $mail->Port       = 465;


    //Recipients
    $mail->setFrom('info@tododev.xyz', 'Info'); // Your name and email
    $mail->addAddress('aleemktk@gmail.com', 'Aleem Nawaz'); // Recipient's name and email

    //Content
    $mail->isHTML(true);
    $mail->Subject = 'Test Email';
    $mail->Body    = 'This is a test email from PHPMailer';

    // Send the email
    $mail->send();
    echo 'Email has been sent successfully';
} catch (Exception $e) {
    echo "Error: {$mail->ErrorInfo}";
}
?>
