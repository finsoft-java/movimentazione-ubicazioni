<?php

include("include/all.php");
$panthera->connect();

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    //do nothing, HTTP 200
    exit();
}

require_logged_user_JWT();

$note = isset($_POST['note']) ? $panthera->escape_string($_POST['note']) : null;
$note_pos = isset($_POST['note_pos']) ? $panthera->escape_string($_POST['note_pos']) : null;
$idUbicazione = isset($_POST['idUbicazione']) ? $panthera->escape_string($_POST['idUbicazione']) : null;


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $ubicazioniManager->salvaNote($idUbicazione, $note, $note_pos);
    
    header('Content-Type: application/json');
    echo '{"msg":"OK"}';
} else {
    //==========================================================
    print_error(405, "Unsupported method in request: " . $_SERVER['REQUEST_METHOD']);
}


?>