<?php

include("include/all.php");
$panthera->connect();

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    //do nothing, HTTP 200
    exit();
}

require_logged_user_JWT();

$codUbicazione = isset($_GET['codUbicazione']) ? $panthera->escape_string($_GET['codUbicazione']) : null;
$codArticolo = isset($_GET['codArticolo']) ? $panthera->escape_string($_GET['codArticolo']) : null;
$idMagazzino = isset($_GET['idMagazzino']) ? $panthera->escape_string($_GET['idMagazzino']) : null;
$codCommessa = isset($_GET['codCommessa']) ? $panthera->escape_string($_GET['codCommessa']) : null;
$whitelist = isset($_GET['whitelist']) ? $panthera->escape_string($_GET['whitelist']) : null;
$movi = isset($_GET['mov']) ? $panthera->escape_string($_GET['mov']) : null;


if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    //if (empty($codUbicazione)) {
    //    print_error(400, "Missing argument codUbicazione");
    //}
    if(!empty($movi)){
        if (!empty($codArticolo) && !empty($codUbicazione)) {
            [$data, $count] = $ubicazioniManager->getContenutoUbicazioneArticoloWithQty($codUbicazione, $codArticolo);
        } else {
            [$data, $count] = $ubicazioniManager->getUbicazioniPerArticoloWithQty($codArticolo);
        }
    } else {
        
        if(!empty($whitelist) && !empty($codArticolo)){
            [$data, $count] = $ubicazioniManager->getArticoloByCod($codArticolo);
        } else {
            if(!empty($idMagazzino) && !empty($codUbicazione)){
                [$data, $count] = $ubicazioniManager->getUbicazioneByMagazzino($idMagazzino, $codUbicazione);
            } else {
                if (!empty($codArticolo)){
                    if(!empty($codCommessa)){
                        [$data, $count] = $ubicazioniManager->getUbicazioniPerArticoloCommessa($codArticolo, $codCommessa);
                    } else {
                        if (!empty($codArticolo) && !empty($codUbicazione)) {
                            [$data, $count] = $ubicazioniManager->getContenutoUbicazioneArticolo($codUbicazione, $codArticolo);
                        } else {
                            [$data, $count] = $ubicazioniManager->getUbicazioniPerArticolo($codArticolo);
                        }
                    }
                } else {
                    [$data, $count] = $ubicazioniManager->getContenutoUbicazione($codUbicazione);
                }
            }
        }
    
    }
    header('Content-Type: application/json');
    echo json_encode(['data' => $data, 'count' => $count]);
} else {
    //==========================================================
    print_error(405, "Unsupported method in request: " . $_SERVER['REQUEST_METHOD']);
}


?>