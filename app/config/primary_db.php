<?php
defined('BASEPATH') or exit('No direct script access allowed');

$url = $_SERVER['REQUEST_URI'];

if(strpos($url, '/admin/') === false){
    // Connection to Primary DB Start
    $connection = new mysqli($hostname, $username, $password, 'retaj');
    if ($connection->connect_error) {
        die("Connection failed: " . $connection->connect_error);
    }

    $connection->close();
}else if (isset($_COOKIE['companyID'])) {

    // Connection to Primary DB Start
    $connection = new mysqli($hostname, $username, $password, $database);
    if ($connection->connect_error) {
        die("Connection failed: " . $connection->connect_error);
    }

    $companyId = null;
    $companyId = $_COOKIE['companyID'] / 999;
    $sql = "SELECT * FROM sma_multi_company m 
                Inner Join `sma_dbs` d 
                    ON d.id = m.db_id 
            WHERE is_used = 1 AND is_primary <> 1 AND m.id = {$companyId} limit 1";
    $result = $connection->query($sql);
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $username = trim($row["db_user"]);
        $password = trim($row["db_pass"]);
        $database = trim($row["db_name"]);
    } else {
        $expirationTime = (time() + 3600 * 9999999) * -1;
        setcookie("companyID", "", $expirationTime, '/');
        die("Invalid request");
    }
    $connection->close();
}
// Connection to Primary DB End