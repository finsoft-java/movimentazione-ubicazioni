<?php
include("include/all.php");
$panthera->connect();

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    //do nothing, HTTP 200
    exit();
}
require_logged_user_JWT();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    
        $count = $richiesteMovimentazioneManager->checkStatusBatch();
             
        header('Content-Type: application/json');
        echo json_encode(['data' => $count]);
    
} else {
    //==========================================================
    print_error(405, "Unsupported method in request: " . $_SERVER['REQUEST_METHOD']);
}


?>