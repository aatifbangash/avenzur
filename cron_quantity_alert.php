<?php 
require 'vendor/autoload.php';
$mail = new PHPMailer\PHPMailer\PHPMailer();

// $hostname = "81.208.168.52";
// $username =  "remote_user";
// $password = 're$Pa1msee$ot_ur';
// $database = "retaj";

$hostname = "localhost";
$username =  "root";
$password = '';
$database = "pharma_with_data";

$conn = new mysqli($hostname, $username, $password, $database);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$mail->isSMTP();
$mail->Host = 'smtp.gmail.com'; // Specify main and backup SMTP servers
$mail->SMTPAuth = true; // Enable SMTP authentication
$mail->Username = 'info@avenzur.com'; // SMTP username
$mail->Password = 'aheoyqmsowyhclea'; //'bpnzmdrbhwbrxclc'; // SMTP password
$mail->SMTPSecure = 'ssl'; // Enable TLS encryption, `ssl` also accepted
$mail->Port = 465; // TCP port to connect to
$mail->isHTML(true); 
$sql= "SELECT p.code, p.name, p.alert_quantity, sum(inv.quantity) as total_quantity 
FROM `sma_inventory_movements` inv 
LEFT JOIN sma_products p on p.id=inv.product_id 
GROUP by inv.product_id HAVING sum(inv.quantity)<=p.alert_quantity  
ORDER BY `total_quantity` DESC";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    $uploadDir = 'files/quantity_alert.csv';
    $csvFile = fopen($uploadDir, 'w'); 
    fputcsv($csvFile, array('Product Code', 'Product Name', 'Alert Quantity' ,'Quantity')); // Write CSV header 
    while ($row = $result->fetch_assoc()) {  
        fputcsv($csvFile, $row); 
    }  
    fclose($csvFile);// Close the CSV file 
    $conn->close();
    try { 
        $mail->setFrom('info@avenzur.com', 'Avenzur');
        $mail->addAddress('agilkar@avenzur.com', 'agilkar'); //mushtaq@avenzur.com Add a recipient  
        $mail->addAttachment($uploadDir);  // Attach CSV file 
        $mail->isHTML(true);  // Set email format to HTML
        $mail->Subject = 'Items Reached the alert quantity';
        $mail->Body    = 'Dear admin, <br><br>  
        Please find the attached CSV containing products which reached the alert quantity';  
        $mail->send();// Send email
        @unlink($uploadDir); 
       // echo 'Email has been sent';
    } catch (Exception $e) {
        echo "Email could not be sent. Mailer Error: {$mail->ErrorInfo}";
    }    
}
?>