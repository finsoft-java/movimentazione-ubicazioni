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
$qty = isset($_GET['qty']) ? $panthera->escape_string($_GET['qty']) : null;
$codUbicazioneDest = isset($_GET['codUbicazioneDest']) ? $panthera->escape_string($_GET['codUbicazioneDest']) : null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (empty($codUbicazione)) {
        print_error(400, "Missing argument codUbicazione");
    }
    if (empty($codArticolo)) {
        print_error(400, "Missing argument codArticolo");
    }
    if (empty($qty)) {
        print_error(400, "Missing argument qty");
    }
    if (empty($codUbicazioneDest)) {
        print_error(400, "Missing argument codUbicazioneDest");
    }

    // FIXME non ho ancora deciso come sarà la response
    $response = $caricamentiMassaManager->trasferisciArticolo($codUbicazione, $codUbicazioneDest, $codArticolo, $qty);
    
    header('Content-Type: application/json');
    echo json_encode(['msg' => "OK"]);
} else {
    //==========================================================
    print_error(400, "Unsupported method in request: " . $_SERVER['REQUEST_METHOD']);
}


?>