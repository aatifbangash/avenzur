<?php
// Simple script to create missing sma_user_logs table

$hostname = "host.docker.internal";
$username = "admin";
$password = "R00tr00t";
$database = "retaj_aldawa";

// Create connection
$conn = new mysqli($hostname, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "Connected successfully to database: $database\n";

$sql = "CREATE TABLE IF NOT EXISTS `sma_user_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ip_address` varchar(45) NOT NULL,
  `location` varchar(255) DEFAULT '',
  `is_bot` tinyint(1) DEFAULT 0,
  `user_agent` text,
  `landing_url` varchar(500) DEFAULT '',
  `access_time` datetime NOT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_access_time` (`access_time`),
  KEY `idx_ip_address` (`ip_address`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";

if ($conn->query($sql) === TRUE) {
    echo "Table sma_user_logs created successfully or already exists\n";
} else {
    echo "Error creating table: " . $conn->error . "\n";
}

$conn->close();
echo "Database connection closed.\n";
?>