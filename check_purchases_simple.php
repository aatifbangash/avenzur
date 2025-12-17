<?php
$mysqli = new mysqli('localhost', 'root', '', 'rawabi_jeddah');
if ($mysqli->connect_error) die('DB Error');

echo "<h3>Purchase Data Check</h3>";

// Today
$r = $mysqli->query("SELECT COUNT(*) as c FROM sma_purchases WHERE DATE(date) = CURDATE()");
$row = $r->fetch_assoc();
echo "Today: " . $row['c'] . " purchases<br>";

// This month
$r = $mysqli->query("SELECT COUNT(*) as c FROM sma_purchases WHERE MONTH(date) = MONTH(CURDATE()) AND YEAR(date) = YEAR(CURDATE())");
$row = $r->fetch_assoc();
echo "This month: " . $row['c'] . " purchases<br>";

// This year
$r = $mysqli->query("SELECT COUNT(*) as c FROM sma_purchases WHERE YEAR(date) = YEAR(CURDATE())");
$row = $r->fetch_assoc();
echo "This year: " . $row['c'] . " purchases<br>";

// All time
$r = $mysqli->query("SELECT COUNT(*) as c FROM sma_purchases");
$row = $r->fetch_assoc();
echo "All time: " . $row['c'] . " purchases<br>";

// Recent 5
echo "<h4>Last 5 purchases:</h4>";
$r = $mysqli->query("SELECT reference_no, date, grand_total FROM sma_purchases ORDER BY date DESC LIMIT 5");
while($row = $r->fetch_assoc()) {
    echo $row['reference_no'] . " - " . $row['date'] . " - " . $row['grand_total'] . "<br>";
}

$mysqli->close();
?>

