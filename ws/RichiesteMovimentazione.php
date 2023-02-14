<?php

include("include/all.php");
$panthera->connect();

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    //do nothing, HTTP 200
    exit();
}

require_logged_user_JWT();

$idAnnoDoc = isset($_GET['idAnnoDoc']) ? $panthera->escape_string($_GET['idAnnoDoc']) : null;
$idNumeroDoc = isset($_GET['idNumeroDoc']) ? $panthera->escape_string($_GET['idNumeroDoc']) : null;
$skip = isset($_GET['skip']) ? $panthera->escape_string($_GET['skip']) : null;
$top = isset($_GET['top']) ? $panthera->escape_string($_GET['top']) : null;

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (!empty($idNumeroDoc)) {
        $righe = $richiesteMovimentazioneManager->getRichiesta($idAnnoDoc, $idNumeroDoc);
        header('Content-Type: application/json');
        echo json_encode(['data' => $righe]);
    } else {
        [$lista, $cnt] = $richiesteMovimentazioneManager->getRichieste($skip, $top);
        header('Content-Type: application/json');
        echo json_encode(['data' => $lista, 'count' => $cnt]);
    }
} else {
    //==========================================================
    print_error(405, "Unsupported method in request: " . $_SERVER['REQUEST_METHOD']);
}


?>