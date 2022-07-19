<?php

$ubicazioniManager = new UbicazioniManager();

class UbicazioniManager {
    
    /**
     * Restituisce tutti gli articoli contenuti in una certa ubicazione
     */
    function getContenutoUbicazioneArticolo($codUbicazione, $codArticolo) {
      global $panthera, $ID_AZIENDA;

      if ($panthera->mock) {
          $data = [ [ 'ID_ARTICOLO' => 'AAAA', 'ID_MAGAZZINO' => 'E1', 'ID_UBICAZIONE' => 'EEE', 'DESCRIZIONE' => 'Lorem Ipsum is simply dummy text of the printing and typesetting industry.', 'QTA_GIAC_PRM' => 10, 'TRASFERIBILE' => 'Y' ]]; 
          $count = 1;
          
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
                  WHERE U.ID_AZIENDA='$ID_AZIENDA' AND U.ID_UBICAZIONE='$codUbicazione' AND S.ID_ARTICOLO='$codArticolo' AND YU.TRASFERIBILE='Y' AND U.STATO='V' ";
          
          $sql3 = " ORDER BY S.ID_ARTICOLO";
          $count = $panthera->select_single_value($sql0 . $sql2);
          $data = $panthera->select_list($sql1 . $sql2 . $sql3);
      }
      
      return [$data, $count];
    }

    /**
     * Restituisce tutti gli articoli contenuti in una certa ubicazione
     */
    function getContenutoUbicazione($codUbicazione) {
        global $panthera, $ID_AZIENDA;

        if ($panthera->mock) {
            $data = [ [ 'ID_ARTICOLO' => 'AAAA', 'ID_MAGAZZINO' => 'E1', 'ID_UBICAZIONE' => 'EEE', 'DESCRIZIONE' => 'Lorem Ipsum is simply dummy text of the printing and typesetting industry.', 'QTA_GIAC_PRM' => 10, 'TRASFERIBILE' => 'Y' ],
                      [ 'ID_ARTICOLO' => 'BBBB', 'ID_MAGAZZINO' => 'E1', 'ID_UBICAZIONE' => 'EEE', 'DESCRIZIONE' => 'Lorem Ipsum is simply dummy text of the printing and typesetting industry.', 'QTA_GIAC_PRM' => 100, 'TRASFERIBILE' => 'Y' ],
                      [ 'ID_ARTICOLO' => 'CCCC', 'ID_MAGAZZINO' => 'E1', 'ID_UBICAZIONE' => 'FFF', 'DESCRIZIONE' => 'Lorem Ipsum is simply dummy text of the printing and typesetting industry.', 'QTA_GIAC_PRM' => 0, 'TRASFERIBILE' => 'Y' ],
                      [ 'ID_ARTICOLO' => 'DDDD', 'ID_MAGAZZINO' => 'E1', 'ID_UBICAZIONE' => 'EEE', 'DESCRIZIONE' => 'Lorem Ipsum is simply dummy text of the printing and typesetting industry.', 'QTA_GIAC_PRM' => 100, 'TRASFERIBILE' => 'Y' ],
                      [ 'ID_ARTICOLO' => 'EEEE', 'ID_MAGAZZINO' => 'E1', 'ID_UBICAZIONE' => 'FFF', 'DESCRIZIONE' => 'Lorem Ipsum is simply dummy text of the printing and typesetting industry.', 'QTA_GIAC_PRM' => 0, 'TRASFERIBILE' => 'Y' ],
                      [ 'ID_ARTICOLO' => 'FFFF', 'ID_MAGAZZINO' => 'E1', 'ID_UBICAZIONE' => 'EEE', 'DESCRIZIONE' => 'Lorem Ipsum is simply dummy text of the printing and typesetting industry.', 'QTA_GIAC_PRM' => 100, 'TRASFERIBILE' => 'Y' ],
                      [ 'ID_ARTICOLO' => 'GGGG', 'ID_MAGAZZINO' => 'E1', 'ID_UBICAZIONE' => 'FFF', 'DESCRIZIONE' => 'Lorem Ipsum is simply dummy text of the printing and typesetting industry.', 'QTA_GIAC_PRM' => 0, 'TRASFERIBILE' => 'Y' ],
                      [ 'ID_ARTICOLO' => 'HHHH', 'ID_MAGAZZINO' => 'E1', 'ID_UBICAZIONE' => 'EEE', 'DESCRIZIONE' => 'Lorem Ipsum is simply dummy text of the printing and typesetting industry.', 'QTA_GIAC_PRM' => 100, 'TRASFERIBILE' => 'Y' ],
                      [ 'ID_ARTICOLO' => 'IIII', 'ID_MAGAZZINO' => 'E1', 'ID_UBICAZIONE' => 'FFF', 'DESCRIZIONE' => 'Lorem Ipsum is simply dummy text of the printing and typesetting industry.', 'QTA_GIAC_PRM' => 0, 'TRASFERIBILE' => 'Y' ]
                     ]; 
            $count = 9;
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

    /**
     * Restituisce la lista di tutti i magazzini su cui l'ubicazione è definita con TRASFERIBILE='N'
     */
    function getMagazziniAlternativi($ID_MAGAZZINO) {
      global $panthera, $ID_AZIENDA;

      if ($panthera->mock) {
          $data = [ 'M01', 'M02', 'M03' ];
      } else {
          $sql = "SELECT DISTINCT * FROM THIP.MAGAZZINI WHERE ID_MAGAZZINO != '$ID_MAGAZZINO' TIPO_MAGAZ IN('0','2','5','7','9') AND ID_AZIENDA= 001";
          $data = $panthera->select_column($sql);
      }
      $count = count($data);
      return [$data, $count];
    }
}