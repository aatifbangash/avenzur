<?php
require 'vendor/autoload.php';

// -------------------- DATABASE CREDENTIALS --------------------
/*$source_host = "localhost";
$source_user = "root";
$source_pass = "";
$source_db   = "rawabi_jeddah";*/

$source_host = "81.208.168.52";
$source_user =  "remote_user";
$source_pass = 're$Pa1msee$ot_ur';
$source_db = "abaad_asaha";

/*$target_host = "localhost";
$target_user = "root";
$target_pass = "";
$target_db   = "directpa_pharma";*/

$target_host = "81.208.168.52";
$target_user =  "remote_user";
$target_pass = 're$Pa1msee$ot_ur';
$target_db = "directpa_pharma";

// -------------------- CREATE CONNECTIONS --------------------
$source_conn = new mysqli($source_host, $source_user, $source_pass, $source_db);
if ($source_conn->connect_error) {
    die("Source DB Connection failed: " . $source_conn->connect_error);
}

$target_conn = new mysqli($target_host, $target_user, $target_pass, $target_db);
if ($target_conn->connect_error) {
    die("Target DB Connection failed: " . $target_conn->connect_error);
}

echo "<h3>Starting Product & Inventory Sync...</h3>";

// -------------------- FETCH ALL PRODUCTS FROM SOURCE --------------------
$sql = "SELECT * FROM sma_products";
$result_products = $source_conn->query($sql);

if ($result_products->num_rows > 0) {

    while ($p = $result_products->fetch_assoc()) {

        $code = $p['code'];

        // -------------------- CHECK PRODUCT IN TARGET DB --------------------
        $stmt = $target_conn->prepare("SELECT id FROM sma_products WHERE code = ? LIMIT 1");
        $stmt->bind_param("s", $code);
        $stmt->execute();
        $res = $stmt->get_result();
        $existingProduct = $res->fetch_assoc();
        $stmt->close();

        if ($existingProduct) {
            echo "Product Exists: $code<br>";
            $targetProductId = $existingProduct['id'];
        } else {
            echo "New Product: $code<br>";
            // -------------------- INSERT PRODUCT INTO TARGET --------------------
            $insert = $target_conn->prepare("
                INSERT INTO sma_products 
                (code, name, cost, price, alert_quantity, quantity, tax_rate, item_code)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");

            $insert->bind_param(
                "ssddidds",
                $p['code'],
                $p['name'],
                $p['cost'],
                $p['price'],
                $p['alert_quantity'],
                $p['quantity'],
                $p['tax_rate'],
                $p['item_code']
            );

            if ($insert->execute()) {
                echo "Inserted Product: $code<br>";
                $targetProductId = $insert->insert_id;
            } else {
                echo "Error inserting product $code: " . $insert->error . "<br>";
                continue;
            }

            $insert->close();
        }

        // -------------------- COPY INVENTORY MOVEMENTS --------------------
        $inv_stmt = $source_conn->prepare("SELECT * FROM sma_inventory_movements WHERE product_id = ?");
        $inv_stmt->bind_param("i", $p['id']);
        $inv_stmt->execute();
        $inv_result = $inv_stmt->get_result();

        while ($i = $inv_result->fetch_assoc()) {

            $insertInv = $target_conn->prepare("
                INSERT INTO sma_inventory_movements
                (product_id, batch_number, movement_date, type, quantity, location_id,
                 net_unit_cost, expiry_date, net_unit_sale, reference_id, real_unit_cost,
                 real_unit_sale, avz_item_code, bonus, customer_id)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");

            $insertInv->bind_param(
                "isssidssssddsss",
                $targetProductId,
                $i['batch_number'],
                $i['movement_date'],
                $i['type'],
                $i['quantity'],
                $i['location_id'],
                $i['net_unit_cost'],
                $i['expiry_date'],
                $i['net_unit_sale'],
                $i['reference_id'],
                $i['real_unit_cost'],
                $i['real_unit_sale'],
                $i['avz_item_code'],
                $i['bonus'],
                $i['customer_id']
            );

            $insertInv->execute();
            $insertInv->close();

            echo " → Inventory copied for $code<br>";
        }

        $inv_stmt->close();
    }

} else {
    echo "No products found in source DB.";
}

echo "<br><b>SYNC COMPLETE</b>";
?>
