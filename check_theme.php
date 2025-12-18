<?php
// Manually set database connection details
$hostname = 'localhost';
$username = 'root';
$password = '';
$database = 'rawabi_jeddah';

$m = new mysqli($hostname, $username, $password, $database);
if ($m->connect_error) {
    die('Connection failed: ' . $m->connect_error);
}

$r = $m->query("SELECT value FROM sma_settings WHERE setting = 'theme'");
$row = $r ? $r->fetch_assoc() : null;
echo $row ? $row['value'] : 'Not found';
$m->close();
?>

