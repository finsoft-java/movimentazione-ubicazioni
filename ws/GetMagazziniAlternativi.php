<?php

include("include/all.php");
$panthera->connect();

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    //do nothing, HTTP 200
    exit();
}

//require_logged_user_JWT();

$codUbicazione = isset($_GET['codUbicazione']) ? $panthera->escape_string($_GET['codUbicazione']) : null;
$idMagazzino = isset($_GET['idMagazzino']) ? $panthera->escape_string($_GET['idMagazzino']) : null;

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    //if (empty($codUbicazione)) {
    //    print_error(400, "Missing argument codUbicazione");
    //}
    //no codUbicazione ma codMagazzino
    [$data, $count] = $ubicazioniManager->getMagazziniAlternativi($idMagazzino);
        
    header('Content-Type: application/json');
    echo json_encode(['data' => $data, 'count' => $count]);
} else {
    //==========================================================
    print_error(400, "Unsupported method in request: " . $_SERVER['REQUEST_METHOD']);
}


?>