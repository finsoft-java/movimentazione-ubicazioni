<?php

$ubicazioniManager = new UbicazioniManager();

class UbicazioniManager {
    
    /**
     * Restituisce tutti gli articoli contenuti in una certa ubicazione
     */
    function getContenutoUbicazione($codUbicazione) {
        global $panthera, $ID_AZIENDA;

        if ($panthera->mock) {
            $data = [ [ 'ID_ARTICOLO' => 'AAAAA', 'ID_MAGAZZINO' => 'E1', 'ID_UBICAZIONE' => 'EEE', 'DESCRIZIONE' => 'XXX', 'QTA_GIAC_PRM' => 10, 'TRASFERIBILE' => 'Y' ],
                      [ 'ID_ARTICOLO' => 'BBBB', 'ID_MAGAZZINO' => 'E1', 'ID_UBICAZIONE' => 'EEE', 'DESCRIZIONE' => 'YYY', 'QTA_GIAC_PRM' => 100, 'TRASFERIBILE' => 'Y' ],
                      [ 'ID_ARTICOLO' => 'ZZZZZZ', 'ID_MAGAZZINO' => 'D1', 'ID_UBICAZIONE' => 'FFF', 'DESCRIZIONE' => 'ZZZ', 'QTA_GIAC_PRM' => 0, 'TRASFERIBILE' => 'Y' ]
                     ];
            $count = 1000;
        } else {
            $sql0 = "SELECT COUNT(*) AS cnt ";
            $sql1 = "SELECT U.ID_UBICAZIONE, U.ID_MAGAZZINO, S.ID_ARTICOLO, A.DESCRIZIONE, S.ID_COMMESSA, S.QTA_GIAC_PRM ";
            $sql2 = "FROM THIP.UBICAZIONI_LL U
                    JOIN THIPPERS.YUBICAZIONI_LL YU
                      ON U.ID_AZIENDA=YU.ID_AZIENDA AND U.ID_UBICAZIONE=YU.ID_UBICAZIONE AND U.ID_MAGAZZINO=YU.ID_MAGAZZINO
                    JOIN THIP.SALDI_UBICAZIONE_V01 S
                      ON U.ID_AZIENDA=S.ID_AZIENDA AND U.ID_UBICAZIONE=S.ID_UBICAZIONE AND U.ID_MAGAZZINO=S.ID_MAGAZZINO
                    JOIN THIP.ARTICOLI A
                      ON S.ID_ARTICOLO=A.ID_ARTICOLO
                    WHERE U.ID_AZIENDA='$ID_AZIENDA' AND U.ID_UBICAZIONE='$codUbicazione' AND S.QTA_GIAC_PRM <> 0 AND YU.TRASFERIBILE='Y' AND U.STATO='V' ";
            $sql3 = " ORDER BY S.ID_ARTICOLO";
            $count = $panthera->select_single_value($sql0 . $sql2);
            $data = $panthera->select_list($sql1 . $sql2 . $sql3);
        }
        
        return [$data, $count];
    }

    /**
     * Restituisce l'unica ubicazione TRASFERIBILE con un certo codice
     */
    function getUbicazione($codUbicazione) {
      global $panthera, $ID_AZIENDA;

      if ($panthera->mock) {
        return [ 'ID_ARTICOLO' => 'AAAAA', 'ID_MAGAZZINO' => 'E1', 'ID_UBICAZIONE' => 'EEE', 'DESCRIZIONE' => 'XXX', 'QTA_GIAC_PRM' => 10, 'TRASFERIBILE' => 'Y' ];
      } else {
        $sql = "SELECT *
                FROM THIP.UBICAZIONI_LL U
                JOIN THIPPERS.YUBICAZIONI_LL YU
                    ON U.ID_AZIENDA=YU.ID_AZIENDA AND U.ID_UBICAZIONE=YU.ID_UBICAZIONE AND U.ID_MAGAZZINO=YU.ID_MAGAZZINO
                WHERE U.ID_AZIENDA='$ID_AZIENDA'
                    AND U.ID_UBICAZIONE='$codUbicazione'
                    AND YU.TRASFERIBILE='Y'
                    AND U.STATO='V'";
        return $panthera->select_single($sql);
      }
    }
}