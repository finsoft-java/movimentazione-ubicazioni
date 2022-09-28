<?php

include("include/all.php");
$panthera->connect();

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    //do nothing, HTTP 200
    exit();
}

require_logged_user_JWT();

$codCarrello = isset($_REQUEST['codCarrello']) ? $panthera->escape_string($_REQUEST['codCarrello']) : null;
$codUbicazione = isset($_REQUEST['codUbicazione']) ? $panthera->escape_string($_REQUEST['codUbicazione']) : null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (empty($codCarrello)) {
        print_error(400, "Missing argument codCarrello");
    }
    if (empty($codUbicazione)) {
        print_error(400, "Missing argument codUbicazione");
    }
    $carrelliManager->dissocia($codCarrello, $codUbicazione);
    
        
    header('Content-Type: application/json');
    echo '{"msg":"OK"}';
} else {
    //==========================================================
    print_error(405, "Unsupported method in request: " . $_SERVER['REQUEST_METHOD']);
}


?>