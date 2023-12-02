<?php
echo 'a';exit;
$mysqli = new mysqli("localhost", "user", "pass", "db");

// Check connection
if ($mysqli->connect_errno) {
    echo "Failed to connect to MySQL: " . $mysqli->connect_error;
    exit();
}

// Run database query
$sql = "CREATE DATABASE test_db";
$status = $mysqli->query($sql);

// If query was successful, it should return true
if ($status) {
  echo "Database created successfully";
} else {
  echo "Error creating database: " . $mysqli->error;
}

$mysqli->close();
