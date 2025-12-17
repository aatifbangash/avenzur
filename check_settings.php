<?php
$m = new mysqli('localhost', 'root', '', 'rawabi_jeddah');
if ($m->connect_error) die('DB Error');

echo "<h3>Settings Check</h3>";
$r = $m->query("SELECT setting, value FROM sma_settings WHERE setting LIKE '%theme%' OR setting LIKE '%admin%' LIMIT 20");
if ($r && $r->num_rows > 0) {
    while($row = $r->fetch_assoc()) {
        echo $row['setting'] . ' = ' . htmlspecialchars(substr($row['value'], 0, 100)) . "<br>";
    }
} else {
    echo "No theme/admin settings found<br>";
}

// Check if view file exists
$view_path = __DIR__ . '/themes/blue/admin/views/reports/purchase_per_invoice.php';
echo "<br><h3>View File Check</h3>";
echo "Looking for: " . $view_path . "<br>";
echo "Exists: " . (file_exists($view_path) ? 'YES' : 'NO') . "<br>";

$m->close();
?>

