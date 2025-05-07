<?php
defined('BASEPATH') or exit('No direct script access allowed');

if (isset($_COOKIE['companyID']) || isset($_GET['cid']) ) {

    // Connection to Primary DB Start
    $connection = new mysqli($hostname, $username, $password, $database);
    if ($connection->connect_error) {
        die("Connection failed: " . $connection->connect_error);
    }

    $companyId = null;
    if(isset($_COOKIE['companyID'])){
        $companyId = $_COOKIE['companyID'];
    }else if(isset($_GET['cid'])){
        $companyId = $_GET['cid'];
        $expirationTime = (time() + 3600 * 9999999);
        setcookie("companyID", $companyId, $expirationTime, '/');
    }

    $companyId = $companyId / 999;
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
}else{
    $host = $_SERVER['HTTP_HOST']; // e.g., retaj.avenzur.com
    $parts = explode('.', $host);

    // Check for subdomain existence
    if (count($parts) > 2) {
        $company_name = $parts[0]; // 'retaj'
    } else {
        die("Invalid Company");
    }

    $sql = "SELECT * FROM sma_multi_company m 
                Inner Join `sma_dbs` d 
                    ON d.id = m.db_id 
            WHERE is_used = 1 AND is_primary <> 1 AND m.company = {$company_name} limit 1";
    $result = $connection->query($sql);
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $username = trim($row["db_user"]);
        $password = trim($row["db_pass"]);
        $database = trim($row["db_name"]);
    }

    $connection->close();
}

// Connection to Primary DB End