<?php

include("include/all.php");
$panthera->connect();

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    //do nothing, HTTP 200
    exit();
}

require_logged_user_JWT();

$note = isset($_REQUEST['note']) ? $panthera->escape_string($_REQUEST['note']) : null;
$note_pos = isset($_REQUEST['note_pos']) ? $_REQUEST['note_pos'] : null;
$idUbicazione = isset($_REQUEST['idUbicazione']) ? $_REQUEST['idUbicazione'] : null;


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ubicazioniManager->salvaNote($idUbicazione, $note, $note_pos);
    
    header('Content-Type: application/json');
    echo '{"msg":"OK"}';
} else {
    //==========================================================
    print_error(400, "Unsupported method in request: " . $_SERVER['REQUEST_METHOD']);
}


?>