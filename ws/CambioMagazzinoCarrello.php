<?php

include("include/all.php");
$panthera->connect();

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    //do nothing, HTTP 200
    exit();
}

require_logged_user_JWT();

$codCarrello = isset($_REQUEST['codCarrello']) ? $panthera->escape_string($_REQUEST['codCarrello']) : null;
$codMagazzinoDest = isset($_REQUEST['codMagazzinoDest']) ? $panthera->escape_string($_REQUEST['codMagazzinoDest']) : null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (empty($codCarrello)) {
        print_error(400, "Missing argument codCarrello");
    }
    if (empty($codMagazzinoDest)) {
        print_error(400, "Missing argument codMagazzinoDest");
    }
    $caricamentiMassaManager->trasferisciCarrello($codCarrello, $codMagazzinoDest);
    header('Content-Type: application/json');
    echo '{"msg":"OK"}';
} else {
    //==========================================================
    print_error(405, "Unsupported method in request: " . $_SERVER['REQUEST_METHOD']);
}


?>