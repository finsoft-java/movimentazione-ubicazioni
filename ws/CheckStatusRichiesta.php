<?php
include("include/all.php");
$panthera->connect();

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    //do nothing, HTTP 200
    exit();
}
require_logged_user_JWT();


$riga = isset($_GET['riga']) ? $_GET['riga'] : null;
$idDoc = isset($_GET['idDoc']) ? $_GET['idDoc'] : null;


if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    
        $count = $richiesteMovimentazioneManager->checkStatusBatch();

        if($count == 0) {
            $righeNonCompletate = $richiesteMovimentazioneManager->getCountRichiestaUnion($riga["ID_ANNO_DOC"], $riga["ID_NUMERO_DOC"], $riga["ID_RIGA_DOC"]);
            //print_r($righeNonCompletate);
            if($righeNonCompletate == 0) {
                $richiesteMovimentazioneManager->modificaTestataDocumentoMovimentazione($idDoc, $riga);
            }                   
            
        }
        header('Content-Type: application/json');
        echo json_encode(['data' => $count, 'nrighe' => $righeNonCompletate]);
    
} else {
    //==========================================================
    print_error(405, "Unsupported method in request: " . $_SERVER['REQUEST_METHOD']);
}


?>