<?php

include("include/all.php");


/*define('DB_PTH_HOST', '172.18.0.15\\PANTH01');
define('DB_PTH_USER', 'finsoft');
define('DB_PTH_PASS', 'Th3R4$0FtTh!p');
define('DB_PTH_PASS', 't3st-f1N$');
define('DB_PTH_NAME', 'PANTH01');
*/

$panthera->connect();


if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    //do nothing, HTTP 200
    exit();
}
    
// DO NOT require_logged_user_JWT();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    //==========================================================


$conti_transitori_imploded = "'" . implode("','", array_keys($matrice_conti)) .  "'";
$conti_ricavi_imploded = "'" . implode("','", array_values($matrice_conti)) .  "'";
		
$codCommessa = 'C36911';
$filtroCommessa = '';
$progressivo = 0;
$numReg = -1;
$decode_conto = "'foo'";

$panthera->execute_update("SET ANSI_WARNINGS  OFF");


    $query_exec = "
update FINANCE.BETRAPT set TRAORIGI='ERROR' where TRAORIGI='RIC-COMM' and TRANUREG=23
  
";
	//$panthera->execute_update($query_exec);

    $query = "
						
		SELECT * from FINANCE.BETRAPT where TRAORIGI='RIC-COMM' and TRANUREG=24
";
//echo $query;die();
    $result = $panthera->select_list($query);
	
    //header('Content-Type: application/json');
    //echo json_encode(['data' => $result]);
	echo "<html>" . print_query_html($result) . "</html>";

} else {
    //==========================================================
    print_error(400, "Unsupported method in request: " . $_SERVER['REQUEST_METHOD']);
}

?>