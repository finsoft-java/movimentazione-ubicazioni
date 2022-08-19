<?php

include("include/all.php");
$panthera->connect();

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    //do nothing, HTTP 200
    exit();
}

//require_logged_user_JWT();

$codUbicazione = isset($_REQUEST['codUbicazione']) ? $panthera->escape_string($_REQUEST['codUbicazione']) : null;
$codArticolo = isset($_REQUEST['codArticolo']) ? $panthera->escape_string($_REQUEST['codArticolo']) : null;
$qty = isset($_REQUEST['qty']) ? $panthera->escape_string($_REQUEST['qty']) : null;
$codUbicazioneDest = isset($_REQUEST['codUbicazioneDest']) ? $panthera->escape_string($_REQUEST['codUbicazioneDest']) : null;

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

    $caricamentiMassaManager->trasferisciArticolo($codUbicazione, $codUbicazioneDest, $codArticolo, $qty);
    
    header('Content-Type: application/json');
    echo '{"msg":"OK"}';
} else {
    //==========================================================
    print_error(400, "Unsupported method in request: " . $_SERVER['REQUEST_METHOD']);
}


?>