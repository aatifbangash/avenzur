<?php

if (($open = fopen("coa2.csv", "r")) !== false) {
    while (($data = fgetcsv($open, 1000, ",")) !== false) {
        //if($data[6] >=1000) continue;
        $array[] = $data;
    }
 
    fclose($open);
}


$data = array();
$levelOne = 0;
$levelTwo = 0;
$levelThree = 0;
$levelFour = 0;
$levelFive = 0;
$levelSix = 0;
foreach($array as $row){   

    $code = explode('-',$row[0]);
    

    if((int)$code[0]>0 && (int)$code[1]==0 && (int)$code[2]==0 && (int)$code[3]==0 && (int)$code[4]==0){
	    $data[$code[0]] = $row;
    }
    if((int)$code[0]>0 && (int)$code[1]>0 && (int)$code[2]==0 && (int)$code[3]==0 && (int)$code[4]==0){
        //$levelOne = $levelOne+1;
	    $data[$code[0]]['children'][$code[1]] = $row;
    }

    if((int)$code[0]>0 && (int)$code[1]>0 && (int)$code[2]>0 && (int)$code[3]==0 && (int)$code[4]==0){
        //$levelOne = $levelOne+1;
	    $data[$code[0]]['children'][$code[1]]['children'][$code[2]] = $row;
    }

    if((int)$code[0]>0 && (int)$code[1]>0 && (int)$code[2]==0 && (int)$code[3]==0 && (int)$code[4]>0){
        //$levelOne = $levelOne+1;
	    $data[$code[0]]['children'][$code[1]]['children'][$code[4]] = $row;
    }

    if((int)$code[0]>0 && (int)$code[1]>0 && (int)$code[2]>0 && (int)$code[3]>0 && (int)$code[4]==0){
        //$levelOne = $levelOne+1;
	    $data[$code[0]]['children'][$code[1]]['children'][$code[2]]['children'][$code[3]] = $row;
    }

    if((int)$code[0]>0 && (int)$code[1]>0 && (int)$code[2]>0 && (int)$code[3]==0 && (int)$code[4]>0){
        //$levelOne = $levelOne+1;
	    $data[$code[0]]['children'][$code[1]]['children'][$code[2]]['children'][$code[4]] = $row;
    }

    if((int)$code[0]>0 && (int)$code[1]>0 && (int)$code[2]>0 && (int)$code[3]>0 && (int)$code[4]>0){
        //$levelOne = $levelOne+1;
	    $data[$code[0]]['children'][$code[1]]['children'][$code[2]]['children'][$code[3]]['children'][$code[4]] = $row;
    }

    
    
}



$mysqli = new mysqli("localhost", "root", "", "avenzur");

// Check connection
if ($mysqli->connect_errno) {
    echo "Failed to connect to MySQL: " . $mysqli->connect_error;
    exit();
}

if (!empty($data)) {
    
    foreach ($data as $key => $level1) {
        
        $sql1 = "INSERT INTO `sma_accounts_groups` 
                        SET `parent_id`= NULL,
                            `code`='{$level1[0]}',
                            `name`='{$level1[1]}',
                            `name_arabic`='{$level1[2]}',
                            `type1`='{$level1[4]}',
                            `type2`='{$level1[6]}',
                            `category`='{$level1[7]}',                        
                            `affects_gross`='[value-5]'";
        $mysqli->query($sql1);
        $level1ID = $mysqli->insert_id;
     

        if (!empty($level1['children'])) {
            foreach ($level1['children'] as $key2 => $level2) {

                //INSERT ROW
                $level2ID = InsRec($level1ID, $level2);


                if (!empty($level2['children'])) {
                    foreach ($level2['children'] as $key3 => $level3) {

                        //INSERT ROW
                        $level3ID = InsRec($level2ID, $level3);

                       
                        if (!empty($level3['children'])) {
                            foreach ($level3['children'] as $key3 => $level4) {


                                //INSERT ROW
                                $level4ID = InsRec($level3ID, $level4);

                                
                                if (!empty($level4['children'])) {
                                    foreach ($level4['children'] as $key4 => $level5) {
                                       
                                        //INSERT ROW
                                        $level5ID = InsRec($level3ID, $level5);
                                       
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



function InsRec($previousLevelId, $row){
    global $mysqli;
    if(strtolower($row[3])=='group'){
        $sql = "INSERT INTO `sma_accounts_groups` 
                        SET `parent_id`= {$previousLevelId},
                            `code`='{$row[0]}',
                            `name`='{$row[1]}',
                            `name_arabic`='{$row[2]}',
                            `type1`='{$row[4]}',
                            `type2`='{$row[6]}',
                            `category`='{$row[7]}',                        
                            `affects_gross`='[value-5]'";

        $mysqli->query($sql);
        $levelID = $mysqli->insert_id;
    }

    if(strtolower($row[3])=='ledger'){

        if(strtolower($row[5])=='debit'){
            $op_balance_dc = 'D';
        }else{
            $op_balance_dc = 'C';
        }

        $sql = "INSERT INTO `sma_accounts_ledgers` 
                            SET `group_id`='{$previousLevelId}',
                                `code`='{$row[0]}',
                                `name`='{$row[1]}',
                                `name_arabic`='{$row[2]}',
                                `type1`='{$row[4]}',
                                `type2`='{$row[6]}',
                                `category`='{$row[7]}',
                                `op_balance_dc`='{$op_balance_dc}',
                                `notes`=''";
         $mysqli->query($sql);

    }
    
    return $levelID;


}