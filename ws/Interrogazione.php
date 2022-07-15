<?php

include("include/all.php");
$panthera->connect();

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    //do nothing, HTTP 200
    exit();
}

//require_logged_user_JWT();

$codUbicazione = isset($_GET['codUbicazione']) ? $panthera->escape_string($_GET['codUbicazione']) : null;
$codArticolo = isset($_GET['codArticolo']) ? $panthera->escape_string($_GET['codArticolo']) : null;

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (empty($codUbicazione)) {
        print_error(400, "Missing argument codUbicazione");
    }
    if (!empty($codArticolo) || $codArticolo == null){
        [$data, $count] = $ubicazioniManager->getContenutoUbicazioneArticolo($codUbicazione,$codArticolo);
    } else {
        [$data, $count] = $ubicazioniManager->getContenutoUbicazione($codUbicazione);
    }
    
        
    header('Content-Type: application/json');
    echo json_encode(['data' => $data, 'count' => $count]);
} else {
    //==========================================================
    print_error(400, "Unsupported method in request: " . $_SERVER['REQUEST_METHOD']);
}


?>