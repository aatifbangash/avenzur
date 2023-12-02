<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (($open = fopen("coa.csv", "r")) !== false) {
    while (($data = fgetcsv($open, 1000, ",")) !== false) {
        //if($data[6] >=1000) continue;
        $array[] = $data;
    }

    fclose($open);
}


echo "<pre>";

$data = array();
$levelOne = 0;
$levelTwo = 0;
$levelThree = 0;
$levelFour = 0;
$levelFive = 0;
$levelSix = 0;
foreach ($array as $row) {
    //print_r($row); 
    if (empty($row[3]) && empty($row[4]) && empty($row[5]) && empty($row[6])) {
        $levelOne = $levelOne + 1;
        $row['level'] = 'level1';
        $data[$levelOne] = $row;
    }

    if (!empty($row[3]) && empty($row[4]) && empty($row[5]) && empty($row[6])) {
        $levelTwo = $levelTwo + 1;
        $row['level'] = 'level2';
        $data[$levelOne]['children'][$levelTwo] = $row;
    }

    if (!empty($row[3]) && !empty($row[4]) && empty($row[5]) && empty($row[6])) {
        $levelThree = $levelThree + 1;
        $row['level'] = 'level3';
        $data[$levelOne]['children'][$levelTwo]['children'][$levelThree] = $row;
    }

    if (!empty($row[3]) && !empty($row[4]) && !empty($row[5]) && empty($row[6])) {
        $levelFour = $levelFour + 1;
        $row['level'] = 'level4';
        $data[$levelOne]['children'][$levelTwo]['children'][$levelThree]['children'][$levelFour] = $row;
    }


    if (!empty($row[3]) && !empty($row[4]) && !empty($row[5]) && !empty($row[6]) && $row[6] == 1000) {
        $levelFive = $levelFive + 1;
        $row['level'] = 'level5';
        $data[$levelOne]['children'][$levelTwo]['children'][$levelThree]['children'][$levelFour]['children'][$levelFive] = $row;
    }

    if (!empty($row[3]) && !empty($row[4]) && !empty($row[5]) && !empty($row[6]) && $row[6] > 1000) {
        $levelSix = $levelSix + 1;
        $row['level'] = 'level6';
        $data[$levelOne]['children'][$levelTwo]['children'][$levelThree]['children'][$levelFour]['children'][$levelFive]['Ledger'][$levelSix] = $row;
    }
}

$mysqli = new mysqli("localhost", "root", "", "avenzur");

// Check connection
if ($mysqli->connect_errno) {
    echo "Failed to connect to MySQL: " . $mysqli->connect_error;
    exit();
}

if (!empty($data)) {
    // asset
    foreach ($data as $key => $level1) {
        $sql1 = "INSERT INTO `sma_accounts_groups` 
                    SET `parent_id`= NULL,
                        `name`='{$level1[1]}',
                        `code`='{$level1[0]}',
                        `affects_gross`='[value-5]'";

        $mysqli->query($sql1);
        $level1ID = $mysqli->insert_id;

        // current asset
        if (!empty($level1['children'])) {
            foreach ($level1['children'] as $key2 => $level2) {
                $sql2 = "INSERT INTO `sma_accounts_groups` 
                SET `parent_id`= {$level1ID},
                    `name`='{$level2[1]}',
                    `code`='{$level2[0]}',
                    `affects_gross`='[value-5]'";

                $mysqli->query($sql2);
                $level2ID = $mysqli->insert_id;

                // cash in fund & bank
                if (!empty($level2['children'])) {
                    foreach ($level2['children'] as $key3 => $level3) {
                        $sql3 = "INSERT INTO `sma_accounts_groups` 
                        SET `parent_id`= {$level2ID},
                            `name`='{$level3[1]}',
                            `code`='{$level3[0]}',
                            `affects_gross`='[value-5]'";

                        $mysqli->query($sql3);
                        $level3ID = $mysqli->insert_id;

                        // cash in fund
                        if (!empty($level3['children'])) {
                            foreach ($level3['children'] as $key3 => $level4) {
                                $sql3 = "INSERT INTO `sma_accounts_groups` 
                                SET `parent_id`= {$level3ID},
                                    `name`='{$level4[1]}',
                                    `code`='{$level4[0]}',
                                    `affects_gross`='[value-5]'";

                                $mysqli->query($sql3);
                                $level4ID = $mysqli->insert_id;

                                // cash in fund - retaj
                                if (!empty($level4['children'])) {
                                    foreach ($level4['children'] as $key4 => $level5) {
                                        $sql3 = "INSERT INTO `sma_accounts_groups` 
                                        SET `parent_id`= {$level4ID},
                                            `name`='{$level5[1]}',
                                            `code`='{$level5[0]}',
                                            `affects_gross`='[value-5]'";

                                        $mysqli->query($sql3);
                                        $level5ID = $mysqli->insert_id;

                                        // Ledger
                                        if (!empty($level5['Ledger'])) {
                                            foreach ($level5['Ledger'] as $key6 => $ledgers) {
                                                $sql4 = "INSERT INTO `sma_accounts_ledgers` 
                                                            SET `group_id`='{$level5ID}',
                                                            `name`='{$ledgers[1]}',
                                                            `code`='{$ledgers[0]}',
                                                            `op_balance_dc`='C',
                                                            `notes`='{$ledgers[7]}'";
                                                $mysqli->query($sql4);
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    }
}
