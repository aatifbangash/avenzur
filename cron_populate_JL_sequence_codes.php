<?php

/**
 * Backfill sma_accounts_entries.sequence_code
 *
 * One independent PREFIX-##### series per transaction_type, using the same
 * algorithm / prefixes as app/libraries/SequenceCode.php
 * (generateAccountsEntry / accountsEntryPrefixes).
 *
 * Usage: php cron_populate_JL_sequence_codes.php
 */

define('BASEPATH', true);

/*$hostname = "81.208.168.52";
$username =  "remote_user";
$password = 're$Pa1msee$ot_ur';
$database = "rawabi";*/

$hostname = "localhost";
$username = 'dev_user';
$password = 'rootR00T';
$database = "rawabi_jeddah";

$conn = new mysqli($hostname, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$conn->set_charset('utf8mb4');

/**
 * Must stay in sync with SequenceCode::accountsEntryPrefixes()
 *
 * @return array<string,string>
 */
function accounts_entry_prefixes()
{
    return [
        'purchaseorder'        => 'PI',
        'purchase_invoice'     => 'PI',
        'saleorder'            => 'SI',
        'sales_invoice'        => 'SI',
        'returncustomerorder'  => 'RC',
        'returnorder'          => 'RS',
        'creditmemo'           => 'CM',   // customer memo
        'debitmemo'            => 'DM',   // supplier memo
        'serviceinvoice'       => 'SCI',
        'customerpayment'      => 'CP',
        'supplierpayment'      => 'SP',
        'customeradvance'      => 'CA',
        'supplieradvance'      => 'SA',
        'pettycash'            => 'PC',
        'journal'              => 'JV',   // JV / JL entry
        'transferorder'        => 'TRSF',
        'salary'               => 'SAL',
        'salaries'             => 'SAL',
        'depreciation'         => 'DEP',
        'advancesettlement'    => 'AS',
        'adjustment'           => 'ADJ',
        'trial_balance_import' => 'TB',
        'opening_balance'      => 'OB',
        'balanceupload'        => 'BU',
    ];
}

/**
 * Same PREFIX-##### logic as SequenceCode::generate()
 */
function next_sequence_code($prefix, $currentMaxNumber, $sizeOfNumber = 5)
{
    $newNumber = ((int) $currentMaxNumber) + 1;
    return $prefix . '-' . str_pad((string) $newNumber, $sizeOfNumber, '0', STR_PAD_LEFT);
}

function max_sequence_number(mysqli $conn, $prefix)
{
    $prefixEsc = $conn->real_escape_string($prefix);
    $like = $conn->real_escape_string($prefix . '-%');
    $sql = "SELECT MAX(sequence_code) AS maxNumber
            FROM sma_accounts_entries
            WHERE sequence_code IS NOT NULL
              AND sequence_code != ''
              AND sequence_code LIKE '{$like}'";
    $res = $conn->query($sql);
    if (!$res) {
        die("Failed reading max sequence for {$prefix}: " . $conn->error . PHP_EOL);
    }
    $row = $res->fetch_assoc();
    $maxCode = $row['maxNumber'] ?? null;
    if ($maxCode === null || $maxCode === '') {
        return 0;
    }
    // PREFIX-#####  → numeric part after "PREFIX-"
    return (int) substr($maxCode, strlen($prefix) + 1);
}

echo "Starting JL sequence_code population..." . PHP_EOL;

$prefixes = accounts_entry_prefixes();
$sizeOfNumber = 5;
$totalUpdated = 0;
$started = microtime(true);

// Group transaction types that share a prefix (e.g. salary + salaries → JLSAL)
$prefixToTypes = [];
foreach ($prefixes as $type => $prefix) {
    $prefixToTypes[$prefix][] = $type;
}

foreach ($prefixToTypes as $prefix => $types) {
    $typeList = implode(',', array_map(function ($t) use ($conn) {
        return "'" . $conn->real_escape_string($t) . "'";
    }, $types));

    $sql = "SELECT id, transaction_type, date
            FROM sma_accounts_entries
            WHERE transaction_type IN ({$typeList})
              AND (sequence_code IS NULL OR sequence_code = '')
            ORDER BY date ASC, id ASC";
    //echo $sql . PHP_EOL;exit;
    $res = $conn->query($sql);
    if (!$res) {
        echo "ERROR selecting {$prefix} (" . implode(',', $types) . "): " . $conn->error . PHP_EOL;
        continue;
    }

    $count = (int) $res->num_rows;
    if ($count === 0) {
        echo "{$prefix}: nothing to update" . PHP_EOL;
        continue;
    }

    $currentMax = max_sequence_number($conn, $prefix);
    $updated = 0;
    $stmt = $conn->prepare("UPDATE sma_accounts_entries SET sequence_code = ? WHERE id = ? AND (sequence_code IS NULL OR sequence_code = '')");
    if (!$stmt) {
        die("Prepare failed: " . $conn->error . PHP_EOL);
    }

    while ($row = $res->fetch_assoc()) {
        $code = next_sequence_code($prefix, $currentMax, $sizeOfNumber);
        $currentMax++;
        $id = (int) $row['id'];
        $stmt->bind_param('si', $code, $id);
        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0) {
                $updated++;
            }
        } else {
            echo "Failed updating id={$id}: " . $stmt->error . PHP_EOL;
        }
    }
    $stmt->close();

    $totalUpdated += $updated;
    echo "{$prefix} [" . implode(', ', $types) . "]: updated {$updated} / {$count}" . PHP_EOL;
}

// Report any types present in DB but not mapped
$unmappedSql = "SELECT transaction_type, COUNT(*) AS cnt
                FROM sma_accounts_entries
                WHERE (sequence_code IS NULL OR sequence_code = '')
                GROUP BY transaction_type
                ORDER BY cnt DESC";
$unmappedRes = $conn->query($unmappedSql);
if ($unmappedRes && $unmappedRes->num_rows > 0) {
    echo PHP_EOL . "Still missing sequence_code (unmapped or failed):" . PHP_EOL;
    while ($u = $unmappedRes->fetch_assoc()) {
        echo "  {$u['transaction_type']}: {$u['cnt']}" . PHP_EOL;
    }
}

$elapsed = round(microtime(true) - $started, 2);
echo PHP_EOL . "Done. Total updated: {$totalUpdated} in {$elapsed}s" . PHP_EOL;

$conn->close();
