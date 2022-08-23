<?php

$ubicazioniManager = new UbicazioniManager();

class UbicazioniManager {
    
    /**
     * Restituisce tutti gli articoli contenuti in una certa ubicazione
     */
    function getContenutoUbicazioneArticolo($codUbicazione, $codArticolo) {
      global $panthera, $ID_AZIENDA;

      if ($panthera->mock) {
          $data = [ [ 'ID_ARTICOLO' => '00000564                 ', 'ID_MAGAZZINO' => 'E01', 'ID_UBICAZIONE' => 'EEE', 'DESCRIZIONE' => 'Lorem Ipsum is simply dummy text of the printing and typesetting industry.', 'DISEGNO' => 'ABC', 'R_UM_PRM_MAG' => 'NR', 'QTA_GIAC_PRM' => 10, 'TRASFERIBILE' => 'Y' ]]; 
          $count = 1;
          
      } else {

          $this->check_stato_ubicazione($codUbicazione);
          $this->check_articolo($codArticolo);

          $sql0 = "SELECT COUNT(*) AS cnt ";
          $sql1 = "SELECT U.ID_UBICAZIONE, U.ID_MAGAZZINO, S.ID_ARTICOLO, A.DESCRIZIONE, A.DISEGNO, A.R_UM_PRM_MAG, S.ID_COMMESSA, S.QTA_GIAC_PRM ";
          
          $sql2 = "FROM THIP.UBICAZIONI_LL U
                  JOIN THIPPERS.YUBICAZIONI_LL YU
                    ON U.ID_AZIENDA=YU.ID_AZIENDA AND U.ID_UBICAZIONE=YU.ID_UBICAZIONE AND U.ID_MAGAZZINO=YU.ID_MAGAZZINO
                  JOIN THIP.SALDI_UBICAZIONE_V01 S
                    ON U.ID_AZIENDA=S.ID_AZIENDA AND U.ID_UBICAZIONE=S.ID_UBICAZIONE AND U.ID_MAGAZZINO=S.ID_MAGAZZINO
                  JOIN THIP.ARTICOLI A
                    ON S.ID_AZIENDA=A.ID_AZIENDA AND S.ID_ARTICOLO=A.ID_ARTICOLO
                  WHERE U.ID_AZIENDA='$ID_AZIENDA' AND U.ID_UBICAZIONE='$codUbicazione' AND S.ID_ARTICOLO='$codArticolo' AND YU.TRASFERIBILE='Y' AND U.STATO='V' AND S.QTA_GIAC_PRM>0 ";
          
          $sql3 = " ORDER BY S.ID_ARTICOLO";
          $count = $panthera->select_single_value($sql0 . $sql2);
          $data = $panthera->select_list($sql1 . $sql2 . $sql3);
      }
      
      // se l'ubicazione e' vuota non do' errori
      return [$data, $count];
    }

    /**
     * Restituisce tutti gli articoli contenuti in una certa ubicazione
     */
    function getContenutoUbicazione($codUbicazione) {
        global $panthera, $ID_AZIENDA;

        if ($panthera->mock) {
            $data = [ [ 'ID_ARTICOLO' => '00000000                 ', 'ID_MAGAZZINO' => 'E01', 'ID_UBICAZIONE' => 'EEE', 'DESCRIZIONE' => 'Lorem Ipsum is simply dummy text of the printing and typesetting industry.', 'DISEGNO' => 'ABC', 'R_UM_PRM_MAG' => 'NR', 'QTA_GIAC_PRM' => 10, 'TRASFERIBILE' => 'Y' ],
                      [ 'ID_ARTICOLO' => '00000564                 ', 'ID_MAGAZZINO' => 'E01', 'ID_UBICAZIONE' => 'EEE', 'DESCRIZIONE' => 'Lorem Ipsum is simply dummy text of the printing and typesetting industry.', 'DISEGNO' => 'ABC', 'R_UM_PRM_MAG' => 'NR', 'QTA_GIAC_PRM' => 100, 'TRASFERIBILE' => 'Y' ],
                      [ 'ID_ARTICOLO' => '00003289                 ', 'ID_MAGAZZINO' => 'E01', 'ID_UBICAZIONE' => 'FFF', 'DESCRIZIONE' => 'Lorem Ipsum is simply dummy text of the printing and typesetting industry.', 'DISEGNO' => 'ABC', 'R_UM_PRM_MAG' => 'LT', 'QTA_GIAC_PRM' => 0, 'TRASFERIBILE' => 'Y' ],
                      [ 'ID_ARTICOLO' => 'DDDD', 'ID_MAGAZZINO' => 'E01', 'ID_UBICAZIONE' => 'EEE', 'DESCRIZIONE' => 'Lorem Ipsum is simply dummy text of the printing and typesetting industry.', 'DISEGNO' => 'ABC', 'R_UM_PRM_MAG' => 'NR', 'QTA_GIAC_PRM' => 100, 'TRASFERIBILE' => 'Y' ],
                      [ 'ID_ARTICOLO' => 'EEEE', 'ID_MAGAZZINO' => 'E01', 'ID_UBICAZIONE' => 'FFF', 'DESCRIZIONE' => 'Lorem Ipsum is simply dummy text of the printing and typesetting industry.', 'DISEGNO' => 'ABC', 'R_UM_PRM_MAG' => 'NR', 'QTA_GIAC_PRM' => 0, 'TRASFERIBILE' => 'Y' ],
                      [ 'ID_ARTICOLO' => 'FFFF', 'ID_MAGAZZINO' => 'E01', 'ID_UBICAZIONE' => 'EEE', 'DESCRIZIONE' => 'Lorem Ipsum is simply dummy text of the printing and typesetting industry.', 'DISEGNO' => 'ABC', 'R_UM_PRM_MAG' => 'NR', 'QTA_GIAC_PRM' => 100, 'TRASFERIBILE' => 'Y' ],
                      [ 'ID_ARTICOLO' => 'GGGG', 'ID_MAGAZZINO' => 'E01', 'ID_UBICAZIONE' => 'FFF', 'DESCRIZIONE' => 'Lorem Ipsum is simply dummy text of the printing and typesetting industry.', 'DISEGNO' => 'ABC', 'R_UM_PRM_MAG' => 'MMT', 'QTA_GIAC_PRM' => 0, 'TRASFERIBILE' => 'Y' ],
                      [ 'ID_ARTICOLO' => 'HHHH', 'ID_MAGAZZINO' => 'E01', 'ID_UBICAZIONE' => 'EEE', 'DESCRIZIONE' => 'Lorem Ipsum is simply dummy text of the printing and typesetting industry.', 'DISEGNO' => 'ABC', 'R_UM_PRM_MAG' => 'NR', 'QTA_GIAC_PRM' => 100, 'TRASFERIBILE' => 'Y' ],
                      [ 'ID_ARTICOLO' => 'IIII', 'ID_MAGAZZINO' => 'E01', 'ID_UBICAZIONE' => 'FFF', 'DESCRIZIONE' => 'Lorem Ipsum is simply dummy text of the printing and typesetting industry.', 'DISEGNO' => 'ABC', 'R_UM_PRM_MAG' => 'NR', 'QTA_GIAC_PRM' => 0, 'TRASFERIBILE' => 'Y' ]
                     ]; 
            $count = 9;
        } else {

            $this->check_stato_ubicazione($codUbicazione);

            $sql0 = "SELECT COUNT(*) AS cnt ";
            $sql1 = "SELECT U.ID_UBICAZIONE, U.ID_MAGAZZINO, S.ID_ARTICOLO, A.DESCRIZIONE, A.DISEGNO, A.R_UM_PRM_MAG, S.ID_COMMESSA, S.QTA_GIAC_PRM ";
            $sql2 = "FROM THIP.UBICAZIONI_LL U
                    JOIN THIPPERS.YUBICAZIONI_LL YU
                      ON U.ID_AZIENDA=YU.ID_AZIENDA AND U.ID_UBICAZIONE=YU.ID_UBICAZIONE AND U.ID_MAGAZZINO=YU.ID_MAGAZZINO
                    JOIN THIP.SALDI_UBICAZIONE_V01 S
                      ON U.ID_AZIENDA=S.ID_AZIENDA AND U.ID_UBICAZIONE=S.ID_UBICAZIONE AND U.ID_MAGAZZINO=S.ID_MAGAZZINO
                    JOIN THIP.ARTICOLI A
                      ON S.ID_AZIENDA=A.ID_AZIENDA AND S.ID_ARTICOLO=A.ID_ARTICOLO
                    WHERE U.ID_AZIENDA='$ID_AZIENDA' AND U.ID_UBICAZIONE='$codUbicazione' AND S.QTA_GIAC_PRM <> 0 AND YU.TRASFERIBILE='Y' AND U.STATO='V' AND S.QTA_GIAC_PRM>0 ";
            $sql3 = " ORDER BY S.ID_ARTICOLO";
            $count = $panthera->select_single_value($sql0 . $sql2);
            $data = $panthera->select_list($sql1 . $sql2 . $sql3);
        }
        
        // se l'ubicazione e' vuota non do' errori
        return [$data, $count];
    }

    function check_stato_ubicazione($codUbicazione) {
      global $panthera, $ID_AZIENDA;
      $sql = "SELECT COUNT(*)
              FROM THIP.UBICAZIONI_LL U
              JOIN THIPPERS.YUBICAZIONI_LL YU
                ON U.ID_AZIENDA=YU.ID_AZIENDA AND U.ID_UBICAZIONE=YU.ID_UBICAZIONE AND U.ID_MAGAZZINO=YU.ID_MAGAZZINO
              WHERE U.ID_AZIENDA='$ID_AZIENDA' AND U.ID_UBICAZIONE='$codUbicazione'
                ";
      $count = $panthera->select_single_value($sql);
      if ($count == 0) {
        print_error(404, "Ubicazione inesistente");
      }

      $sql = "SELECT COUNT(*)
              FROM THIP.UBICAZIONI_LL U
              JOIN THIPPERS.YUBICAZIONI_LL YU
                ON U.ID_AZIENDA=YU.ID_AZIENDA AND U.ID_UBICAZIONE=YU.ID_UBICAZIONE AND U.ID_MAGAZZINO=YU.ID_MAGAZZINO
              WHERE U.ID_AZIENDA='$ID_AZIENDA' AND U.ID_UBICAZIONE='$codUbicazione' AND YU.TRASFERIBILE='Y'
                ";
      $count = $panthera->select_single_value($sql);
      if ($count == 0) {
        print_error(500, "Ubicazione esistente ma non associata a un magazzino (flag 'trasferibile' non impostato)");
      }

      $sql = "SELECT COUNT(*)
              FROM THIP.UBICAZIONI_LL U
              JOIN THIPPERS.YUBICAZIONI_LL YU
                ON U.ID_AZIENDA=YU.ID_AZIENDA AND U.ID_UBICAZIONE=YU.ID_UBICAZIONE AND U.ID_MAGAZZINO=YU.ID_MAGAZZINO
              WHERE U.ID_AZIENDA='$ID_AZIENDA' AND U.ID_UBICAZIONE='$codUbicazione' AND YU.TRASFERIBILE='Y' AND U.STATO='V'
                ";
      $count = $panthera->select_single_value($sql);
      if ($count == 0) {
        print_error(404, "Ubicazione esistente ma in stato non valido");
      } elseif ($count > 1) {
        print_error(500, "Ubicazione associata a $count magazzini diversi");
      }
    }

    function check_articolo($codArticolo) {
      global $panthera, $ID_AZIENDA;

      $sql = "SELECT COUNT(*)
              FROM THIP.ARTICOLI A
              WHERE A.ID_AZIENDA='$ID_AZIENDA' AND A.ID_ARTICOLO='$codArticolo' ";
      $count = $panthera->select_single_value($sql);
      if ($count == 0) {
        print_error(404, "Articolo inesistente");
      }
    }

    /**
     * Restituisce l'unica ubicazione TRASFERIBILE con un certo codice
     */
    function getUbicazione($codUbicazione) {
      global $panthera, $ID_AZIENDA;

      if ($panthera->mock) {
        return [ 'ID_ARTICOLO' => 'AAAAA', 'ID_MAGAZZINO' => 'E1', 'ID_UBICAZIONE' => 'EEE', 'DESCRIZIONE' => 'XXX', 'TRASFERIBILE' => 'Y' ];
      } else {

        $this->check_stato_ubicazione($codUbicazione);

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
     * FUNZIONE salva note
     */
    function salvaNote($codUbicazione, $note, $notePosizione) {
      global $panthera, $ID_AZIENDA;

      if ($panthera->mock) {
        return;
      }

      $this->check_stato_ubicazione($codUbicazione);

      // ma lo setto a tappeto su tutti i magazzini???
      $sql = "UPDATE THIP.UBICAZIONI_LL U
              SET NOTE='$note'
              WHERE U.ID_AZIENDA='$ID_AZIENDA' AND U.ID_UBICAZIONE='$codUbicazione' ";
      $panthera->execute_update($sql);
      $sql = "UPDATE THIP.YUBICAZIONI_LL YU
              SET NOTE_POSIZIONE='$notePosizione'
              WHERE YU.ID_AZIENDA='$ID_AZIENDA' AND YU.ID_UBICAZIONE='$codUbicazione' ";
      $panthera->execute_update($sql);
    }
}