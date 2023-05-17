<?php

include("include/all.php");
$panthera->connect();

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    //do nothing, HTTP 200
    exit();
}

require_logged_user_JWT();

$codUbicazione = isset($_REQUEST['codUbicazione']) ? $panthera->escape_string($_REQUEST['codUbicazione']) : null;
$codArticolo = isset($_REQUEST['codArticolo']) ? $panthera->escape_string($_REQUEST['codArticolo']) : null;
$commessa = isset($_REQUEST['commessa']) ? $panthera->escape_string($_REQUEST['commessa']) : null;
$qty = isset($_REQUEST['qty']) ? $panthera->escape_string($_REQUEST['qty']) : null;
$codUbicazioneDest = isset($_REQUEST['codUbicazioneDest']) ? $panthera->escape_string($_REQUEST['codUbicazioneDest']) : null;
$whitelist = isset($_REQUEST['whitelist']) ? $panthera->escape_string($_REQUEST['whitelist']) : null;

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
    if (empty($commessa)) {
        print_error(400, "Missing argument commessa");
    }
    //da aggiungere anche la commessa
    $caricamentiMassaManager->trasferisciArticolo($codUbicazione, $codUbicazioneDest, $codArticolo, $qty, $commessa, $whitelist);
    
    header('Content-Type: application/json');
    echo '{"msg":"OK"}';
} else {
    //==========================================================
    print_error(405, "Unsupported method in request: " . $_SERVER['REQUEST_METHOD']);
}


?>