<?php

include("include/all.php");
$panthera->connect();

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    //do nothing, HTTP 200
    exit();
}

require_logged_user_JWT();

$codCarrello = isset($_GET['codCarrello']) ? $panthera->escape_string($_GET['codCarrello']) : null;

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (empty($codCarrello)) {
        print_error(400, "Missing argument codCarrello");
    }
    $data = $carrelliManager->getContenutoCarrello($codCarrello);
    
        
    header('Content-Type: application/json');
    echo json_encode(['data' => $data, 'count' => count($data)]);
} else {
    //==========================================================
    print_error(405, "Unsupported method in request: " . $_SERVER['REQUEST_METHOD']);
}


?>