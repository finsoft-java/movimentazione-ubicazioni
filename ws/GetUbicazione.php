<?php

include("include/all.php");
$panthera->connect();

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    //do nothing, HTTP 200
    exit();
}

require_logged_user_JWT();

$codUbicazione = isset($_GET['codUbicazione']) ? $panthera->escape_string($_GET['codUbicazione']) : null;

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (empty($codUbicazione)) {
        print_error(400, "Missing argument codUbicazione");
    }
    $ubicazione = $ubicazioniManager->getUbicazione($codUbicazione);
    
        
    header('Content-Type: application/json');
    echo json_encode(['value' => $ubicazione]);
} else {
    //==========================================================
    print_error(405, "Unsupported method in request: " . $_SERVER['REQUEST_METHOD']);
}


?>