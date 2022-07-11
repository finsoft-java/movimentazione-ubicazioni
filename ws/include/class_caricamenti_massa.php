<?php

$caricamentiMassaManager = new CaricamentiMassaManager();

class CaricamentiMassaManager {
    
    function trasferisciArticolo($codUbicazioneSrc, $codUbicazioneDest, $qty) {
        global $panthera;

        if ($panthera->mock) {
            return;
        }

        // CM_DOC_TRA_TES/CM_DOC_TRA_RIG
        // oppure
        // CM_MOVIM_MAGAZ

        $sql = "INSERT ... BOH ...";
        $this->execute_update($sql);
    }
}