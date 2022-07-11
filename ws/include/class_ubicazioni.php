<?php

$ubicazioniManager = new UbicazioniManager();

class UbicazioniManager {
    
    function getUbicazioni($codUbicazione) {
        global $panthera;

        if ($panthera->mock) {
            $data = [ [ 'ID_ARTICOLO' => 'AAAAA', 'ID_MAGAZZINO' => 'E1', 'ID_UBICAZIONE' => 'EEE', 'DESCRIZIONE' => 'XXX', 'QTA_GIAC_PRM' => 10 ],
                      [ 'ID_ARTICOLO' => 'BBBB', 'ID_MAGAZZINO' => 'E1', 'ID_UBICAZIONE' => 'EEE', 'DESCRIZIONE' => 'YYY', 'QTA_GIAC_PRM' => 100 ],
                      [ 'ID_ARTICOLO' => 'ZZZZZZ', 'ID_MAGAZZINO' => 'D1', 'ID_UBICAZIONE' => 'FFF', 'DESCRIZIONE' => 'ZZZ', 'QTA_GIAC_PRM' => 0 ]
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
                    WHERE U.ID_AZIENDA='001' AND U.ID_UBICAZIONE='$codUbicazione' AND S.QTA_GIAC_PRM <> 0 AND S.TRASFERIBILE='Y'
                    ORDER BY S.ID_ARTICOLO";
            $count = $this->select_single_value($sql0 . $sql2);
            $data = $this->select_list($sql1 . $sql2);
        }
        
        return [$data, $count];
    }
}