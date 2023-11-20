<?php

include("include/all.php");
$panthera->connect();

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    //do nothing, HTTP 200
    exit();
}

require_logged_user_JWT();

$codUbicazione = isset($_REQUEST['codUbicazione']) ? $panthera->escape_string($_REQUEST['codUbicazione']) : null;
$codMagazzinoDest = isset($_REQUEST['codMagazzinoDest']) ? $panthera->escape_string($_REQUEST['codMagazzinoDest']) : null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (empty($codUbicazione)) {
        print_error(400, "Missing argument codUbicazione");
    }
    if (empty($codMagazzinoDest)) {
        print_error(400, "Missing argument codMagazzinoDest");
    }
    [$data, $count] = $ubicazioniManager->getContenutoUbicazioneNoQnt($codUbicazione);
    if($count > 0){
        print_error(500, "Sono presenti $count Articoli con quantità inferiori a 0 non è possibile cambiare magazzino");
    }
    
    $caricamentiMassaManager->trasferisciUbicazione($codUbicazione, $codMagazzinoDest);
    header('Content-Type: application/json');
    echo '{"msg":"OK"}';
    
} else {
    //==========================================================
    print_error(405, "Unsupported method in request: " . $_SERVER['REQUEST_METHOD']);
}


?>