<?php

include("include/all.php");
$panthera->connect();

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    //do nothing, HTTP 200
    exit();
}

//require_logged_user_JWT();

$codUbicazione = isset($_GET['codUbicazione']) ? $panthera->escape_string($_GET['codUbicazione']) : null;
$codMagazzinoDest = isset($_GET['codMagazzinoDest']) ? $panthera->escape_string($_GET['codMagazzinoDest']) : null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (empty($codUbicazione)) {
        print_error(400, "Missing argument codUbicazione");
    }
    if (empty($codMagazzinoDest)) {
        print_error(400, "Missing argument codMagazzinoDest");
    }

    // FIXME non ho ancora deciso come sarà la response
    $response = $caricamentiMassaManager->trasferisciUbicazione($codUbicazione, $codMagazzinoDest);
    
    header('Content-Type: application/json');
    echo json_encode(['msg' => $msg]);
} else {
    //==========================================================
    print_error(400, "Unsupported method in request: " . $_SERVER['REQUEST_METHOD']);
}


?>