<?php
// Test if sma_user_logs table exists

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

// Check if table exists
$result = $conn->query("SHOW TABLES LIKE 'sma_user_logs'");

if ($result->num_rows > 0) {
    echo "✓ Table 'sma_user_logs' exists\n";
    
    // Show table structure
    $structure = $conn->query("DESCRIBE sma_user_logs");
    echo "\nTable structure:\n";
    while($row = $structure->fetch_assoc()) {
        echo "- " . $row['Field'] . " (" . $row['Type'] . ")\n";
    }
} else {
    echo "✗ Table 'sma_user_logs' does NOT exist\n";
}

$conn->close();
?>