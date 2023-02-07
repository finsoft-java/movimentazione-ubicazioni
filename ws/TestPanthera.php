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


$panthera->execute_update("SET ANSI_WARNINGS OFF");

$query = "
--SELECT * from INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = 'THERA' ORDER BY TABLE_TYPE, TABLE_NAME
--SELECT * from INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = 'THIP' and TABLE_NAME='DOC_TRA_TES'
--SELECT MAX(PROGRESSIVO) FROM THIPPERS.YUBICAZIONI_CARRELLO WHERE ID_CARRELLO='A01'
--SELECT count(*) FROM THIPPERS.YUBICAZIONI_CARRELLO WHERE R_UBICAZIONE='PL-0001' AND ID_CARRELLO='A01'
--SELECT * FROM THERA.NUMERATOR WHERE NUMERATOR_ID='MOVUBI'
--SELECT TOP 10 * FROM THERA.BATCH_LOAD_HDR WHERE DATA_ORIGIN='CM-MOV-UBI' ORDER BY RUN_ID DESC
--SELECT * FROM THERA.SCHEDULED_JOB;
--SELECT TOP 100 * FROM THIP.SALDI_UBICAZIONE
--SELECT * FROM THIPPERS.YUBICAZIONI_CARRELLO; --WHERE R_UBICAZIONE='PL-0001' AND ID_CARRELLO='A01' AND ID_AZIENDA='001';
--SELECT TOP 10 * from THIP.CM_DOC_TRA_TES WHERE RUN_ID >= 193 ORDER BY RUN_ID DESC
SELECT TOP 10 * from THIP.CM_DOC_TRA_RIG WHERE ID_NUMERO_DOC='DT  000173' --RUN_ID >= 193 ORDER BY RUN_ID DESC
--SELECT * FROM THIPPERS.YCARRELLO 
--SELECT * FROM THIPPERS.YUBICAZIONI_CARRELLO C 
--WHERE C.ID_AZIENDA='001' AND C.ID_CARRELLO='A01'
--SELECT TOP 10 * FROM THIP.DOC_TRA_RIG  WHERE ID_ANNO_DOC='2022'  AND ID_NUMERO_DOC='DT  000173'   --
--SELECT TOP 10 * from THIP.CM_DOC_TRA_TES  WHERE RUN_ID=217  --ID_ANNO_DOC='2022'  AND ID_NUMERO_DOC='DT  000173'
--update THIP.CM_DOC_TRA_RIG set RUN_ID=217 where RUN_ID=0 AND DATA_ORIGIN='CM-MOV-UBI';
";

if (!empty($_REQUEST['query'])) {
    $query = $_REQUEST['query'];
}

?>

<html>
<body>
<div style="margin:5px">
	<form id="mainForm" action="./TestPanthera.php" method="POST">
	<textarea style="width:100%;height:4cm" name="query">
<?php echo $query; ?>


    </textarea>
	<input type="hidden" value="0" id="update" name="update"/>
	</form>
	<button type="button" onclick="document.getElementById('update').value='0';document.getElementById('mainForm').submit();">Execute SELECT</button>
	<button type="button" onclick="document.getElementById('update').value='1';document.getElementById('mainForm').submit();">Execute UPDATE</button>
</div>

<?php

if (!empty($_REQUEST['query'])) {

    if ($_REQUEST['update'] == '1') {
        $panthera->execute_update($query);
        echo "OK."; // se ci sono eccezioni SQL invece si spacca di brutto
    } else {
        $result = $panthera->select_list($query);
        //header('Content-Type: application/json');
        //echo json_encode(['data' => $result]);
        //die();
        echo "<html>" . print_query_html($result) . "</html>";
    }
}

?>

</body>
</html>