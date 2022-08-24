<?php

include("include/all.php");
$panthera->connect();

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    //do nothing, HTTP 200
    exit();
}

require_logged_user_JWT();

$idMagazzino = isset($_GET['idMagazzino']) ? $panthera->escape_string($_GET['idMagazzino']) : null;

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    [$data, $count] = $magazziniManager->getMagazziniAlternativi($idMagazzino);
        
    header('Content-Type: application/json');
    echo json_encode(['data' => $data, 'count' => $count]);
} else {
    //==========================================================
    print_error(400, "Unsupported method in request: " . $_SERVER['REQUEST_METHOD']);
}


?>