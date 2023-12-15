<?php

include("include/all.php");
$panthera->connect();

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    //do nothing, HTTP 200
    exit();
}

require_logged_user_JWT();

$codUbicazione = isset($_REQUEST['codUbicazione']) ? $panthera->escape_string($_REQUEST['codUbicazione']) : null;
$codUbicazioneDest = isset($_REQUEST['codUbicazioneDest']) ? $panthera->escape_string($_REQUEST['codUbicazioneDest']) : null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (empty($codUbicazione)) {
        print_error(400, "Missing argument codUbicazione");
    }
    if (empty($codUbicazioneDest)) {
        print_error(400, "Missing argument codUbicazioneDest");
    }
    //da aggiungere anche la commessa
    $caricamentiMassaManager->trasferisciContenuto($codUbicazione, $codUbicazioneDest);
    
    header('Content-Type: application/json');
    echo '{"msg":"OK"}';
} else {
    //==========================================================
    print_error(405, "Unsupported method in request: " . $_SERVER['REQUEST_METHOD']);
}


?>
