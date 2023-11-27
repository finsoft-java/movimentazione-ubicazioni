<?php

include("include/all.php");
$panthera->connect();

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    //do nothing, HTTP 200
    exit();
}

require_logged_user_JWT();

$qntVolutaDoc = isset($_GET['qntVolutaDoc']) ? $panthera->escape_string($_GET['qntVolutaDoc']) : null;
$articolo = isset($_GET['articolo']) ? $panthera->escape_string($_GET['articolo']) : null;
$commessaPartenza = isset($_GET['commessaPartenza']) ? $panthera->escape_string($_GET['commessaPartenza']) : null;
$magazzinoPartenza = isset($_GET['magazzinoPartenza']) ? $panthera->escape_string($_GET['magazzinoPartenza']) : null;

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    
    $data = $ubicazioniManager->getStatoRigaQnt($magazzinoPartenza, $commessaPartenza, $articolo);
    $qntMassima=0;
    for ($i=0; $i < count($data[0]); $i++) { 
        $qntMassima += $data[0][$i]["QTA_GIAC_PRM"];
    }
    if($qntMassima == 0 ){
        $result = "Non Evadibile";
    } else {
        if($qntMassima >= $qntVolutaDoc){
            $result = "Evadibile";
        } else if($qntMassima < $qntVolutaDoc){
            $result = "Parziale";
        }
    }
    header('Content-Type: application/json');
    echo json_encode(['data' => $result]);
} else {
    //==========================================================
    print_error(405, "Unsupported method in request: " . $_SERVER['REQUEST_METHOD']);
}


?>