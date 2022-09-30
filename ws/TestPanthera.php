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



$panthera->execute_update("SET ANSI_WARNINGS OFF");

    $query_exec = "
DELETE FROM THIP.CM_DOC_TRA_TES;
  
";
	//$panthera->execute_update($query_exec);

    $query = "
--SELECT * from INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = 'THERA' ORDER BY TABLE_TYPE, TABLE_NAME
--SELECT * from INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = 'THIPPERS' and TABLE_NAME='YUBICAZIONI_CARRELLO'
--SELECT MAX(PROGRESSIVO) FROM THIPPERS.YUBICAZIONI_CARRELLO WHERE ID_CARRELLO='A01'
SELECT count(*) FROM THIPPERS.YUBICAZIONI_CARRELLO WHERE R_UBICAZIONE='PL-0001' AND ID_CARRELLO='A01'
--SELECT * FROM THERA.NUMERATOR WHERE NUMERATOR_ID='MOVUBI'
--SELECT * FROM THERA.BATCH_LOAD_HDR WHERE DATA_ORIGIN='CM-MOV-UBI'
--SELECT * FROM THERA.SCHEDULED_JOB;
--SELECT TOP 100 * FROM THIP.SALDI_UBICAZIONE

--SELECT * from THIP.CM_DOC_TRA_TES WHERE RUN_ID=30
--SELECT * from THIP.CM_DOC_TRA_RIG WHERE RUN_ID=30
--SELECT * FROM THIPPERS.YCARRELLO 
--SELECT * FROM THIPPERS.YUBICAZIONI_CARRELLO C 
--WHERE C.ID_AZIENDA='001' AND C.ID_CARRELLO='A01'
";
//echo $query;die();
    $result = $panthera->select_list($query);

    //header('Content-Type: application/json');
    //echo json_encode(['data' => $result]);
	echo "<html>" . print_query_html($result) . "</html>";

} else {
    //==========================================================
    print_error(405, "Unsupported method in request: " . $_SERVER['REQUEST_METHOD']);
}

?>