<?php
// Quick script to check database schema
$pdo = new PDO("mysql:host=localhost;dbname=avnzor", "root", "");

echo "=== SMA_SALES TABLE STRUCTURE ===\n\n";
$stmt = $pdo->query("DESCRIBE sma_sales");
$columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($columns as $col) {
    echo $col['Field'] . " (" . $col['Type'] . ")\n";
}

echo "\n\n=== SMA_COMPANIES TABLE STRUCTURE ===\n\n";
$stmt = $pdo->query("DESCRIBE sma_companies");
$columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($columns as $col) {
    echo $col['Field'] . " (" . $col['Type'] . ")\n";
}

