<?php

$ubicazioniManager = new UbicazioniManager();

class UbicazioniManager {

/**
     * Restituisce tutti i dati dell'articolo 
     */
    function getArticoloByCod($codArticolo) {
      global $panthera, $ID_AZIENDA;

      if ($panthera->mock) {
          $data = []; 
          $count = 1;
          
      } else {

          $sql0 = "SELECT COUNT(*) AS cnt ";
          $sql1 = "SELECT ID_ARTICOLO, DESCRIZIONE, DISEGNO, R_UM_PRM_MAG ";
          
          $sql2 = "FROM THIP.ARTICOLI 
                   WHERE ID_AZIENDA='$ID_AZIENDA' and ID_ARTICOLO='$codArticolo'";          
          $sql3 = " ORDER BY ID_ARTICOLO";
          $count = $panthera->select_single_value($sql0 . $sql2);
          $data = $panthera->select_list($sql1 . $sql2 . $sql3);
      }
      
      return [$data, $count];
    }

     /**
     * Restituisce tutti i saldi (per commessa) di un certo articolo in una certa ubicazione
     */
    function getContenutoUbicazioneArticoloWithQty($codUbicazione, $codArticolo) {
      global $panthera, $ID_AZIENDA;

      if ($panthera->mock) {
          $data = [ [ 'ID_ARTICOLO' => '00000564                 ',
                      'ID_MAGAZZINO' => 'E01',
                      'ID_COMMESSA' => 'COMMESSA1',
                      'ID_UBICAZIONE' => 'EEE',
                      'DESCRIZIONE' => 'Lorem Ipsum is simply dummy text of the printing and typesetting industry.',
                      'DISEGNO' => 'ABC',
                      'R_UM_PRM_MAG' => 'NR',
                      'QTA_GIAC_PRM' => 10,
                      'TRASFERIBILE' => 'Y',
                      'R_UTENTE_AGG' => '001_finsoft         ',
                      'TIMESTAMP_AGG' => '2022-09-21 16:25:53.410'
                    ],
                    [ 'ID_ARTICOLO' => '00000564                 ',
                    'ID_MAGAZZINO' => 'E01',
                    'ID_COMMESSA' => 'COMMESSA2',
                    'ID_UBICAZIONE' => 'EEE',
                    'DESCRIZIONE' => 'Lorem Ipsum is simply dummy text of the printing and typesetting industry.',
                    'DISEGNO' => 'ABC',
                    'R_UM_PRM_MAG' => 'NR',
                    'QTA_GIAC_PRM' => 12,
                    'TRASFERIBILE' => 'Y',
                    'R_UTENTE_AGG' => '001_finsoft         ',
                    'TIMESTAMP_AGG' => '2022-09-21 16:25:53.410'
                ]]; 
          $count = 1;
          
      } else {

          $trasferibile = $this->check_stato_ubicazione($codUbicazione);
          $str_trasferibile = $trasferibile ? " AND YU.TRASFERIBILE='Y' " : "";
          $this->check_articolo($codArticolo);

          $sql0 = "SELECT COUNT(*) AS cnt ";
          $sql1 = "SELECT U.ID_UBICAZIONE, U.ID_MAGAZZINO, U.R_UTENTE_AGG, U.TIMESTAMP_AGG, S.ID_ARTICOLO, A.DESCRIZIONE, A.DISEGNO, A.R_UM_PRM_MAG, S.ID_COMMESSA, S.QTA_GIAC_PRM ";
          
          $sql2 = "FROM THIP.UBICAZIONI_LL U
                  JOIN THIPPERS.YUBICAZIONI_LL YU
                    ON U.ID_AZIENDA=YU.ID_AZIENDA AND U.ID_UBICAZIONE=YU.ID_UBICAZIONE AND U.ID_MAGAZZINO=YU.ID_MAGAZZINO
                  JOIN THIP.SALDI_UBICAZIONE S
                    ON U.ID_AZIENDA=S.ID_AZIENDA AND U.ID_UBICAZIONE=S.ID_UBICAZIONE AND U.ID_MAGAZZINO=S.ID_MAGAZZINO
                  JOIN THIP.ARTICOLI A
                    ON S.ID_AZIENDA=A.ID_AZIENDA AND S.ID_ARTICOLO=A.ID_ARTICOLO
                  WHERE U.ID_AZIENDA='$ID_AZIENDA' AND U.ID_UBICAZIONE='$codUbicazione' AND S.ID_ARTICOLO='$codArticolo' $str_trasferibile AND U.STATO='V' AND S.QTA_GIAC_PRM > 0";
          
          $sql3 = " ORDER BY S.ID_ARTICOLO";
          //echo $sql1 . $sql2 . $sql3;
         $count = $panthera->select_single_value($sql0 . $sql2);
          $data = $panthera->select_list($sql1 . $sql2 . $sql3);
      }
      
      // se l'ubicazione e' vuota non do' errori
      return [$data, $count];
    }

/**
     * Restituisce tutti i saldi (per commessa) di un certo articolo in una certa ubicazione
     */
    function getStatoRigaQnt($idMagazzino, $codCommessa, $articolo) {
      global $panthera, $ID_AZIENDA;

      if ($panthera->mock) {
          $data = [ [ 'ID_ARTICOLO' => '00000564                 ',
                      'ID_MAGAZZINO' => 'E01',
                      'ID_COMMESSA' => 'COMMESSA1',
                      'ID_UBICAZIONE' => 'EEE',
                      'DESCRIZIONE' => 'Lorem Ipsum is simply dummy text of the printing and typesetting industry.',
                      'DISEGNO' => 'ABC',
                      'R_UM_PRM_MAG' => 'NR',
                      'QTA_GIAC_PRM' => 10,
                      'TRASFERIBILE' => 'Y',
                      'R_UTENTE_AGG' => '001_finsoft         ',
                      'TIMESTAMP_AGG' => '2022-09-21 16:25:53.410'
                    ],
                    [ 'ID_ARTICOLO' => '00000564                 ',
                    'ID_MAGAZZINO' => 'E01',
                    'ID_COMMESSA' => 'COMMESSA2',
                    'ID_UBICAZIONE' => 'EEE',
                    'DESCRIZIONE' => 'Lorem Ipsum is simply dummy text of the printing and typesetting industry.',
                    'DISEGNO' => 'ABC',
                    'R_UM_PRM_MAG' => 'NR',
                    'QTA_GIAC_PRM' => 12,
                    'TRASFERIBILE' => 'Y',
                    'R_UTENTE_AGG' => '001_finsoft         ',
                    'TIMESTAMP_AGG' => '2022-09-21 16:25:53.410'
                ]]; 
          $count = 1;
          
      } else {

          $this->check_articolo($articolo);

          $sql = "SELECT S.QTA_GIAC_PRM FROM THIP.UBICAZIONI_LL U
                  JOIN THIPPERS.YUBICAZIONI_LL YU
                    ON U.ID_AZIENDA=YU.ID_AZIENDA AND U.ID_UBICAZIONE=YU.ID_UBICAZIONE AND U.ID_MAGAZZINO=YU.ID_MAGAZZINO
                  JOIN THIP.SALDI_UBICAZIONE S
                    ON U.ID_AZIENDA=S.ID_AZIENDA AND U.ID_UBICAZIONE=S.ID_UBICAZIONE AND U.ID_MAGAZZINO=S.ID_MAGAZZINO
                  JOIN THIP.ARTICOLI A
                    ON S.ID_AZIENDA=A.ID_AZIENDA AND S.ID_ARTICOLO=A.ID_ARTICOLO
                  WHERE U.ID_AZIENDA='$ID_AZIENDA' AND U.ID_MAGAZZINO='$idMagazzino' AND S.ID_ARTICOLO='$articolo' AND S.ID_COMMESSA = '$codCommessa' AND U.STATO='V' AND S.QTA_GIAC_PRM > 0 ORDER BY S.ID_ARTICOLO";
          //echo $sql1 . $sql2 . $sql3;
          $data = $panthera->select_list($sql);
      }
      
      // se l'ubicazione e' vuota non do' errori
      return [$data, $count];
    }

    /**
     * Restituisce tutti i saldi (per commessa) di un certo articolo in una certa ubicazione
     */
    function getContenutoUbicazioneArticolo($codUbicazione, $codArticolo) {
      global $panthera, $ID_AZIENDA;

      if ($panthera->mock) {
          $data = [ [ 'ID_ARTICOLO' => '00000564                 ',
                      'ID_MAGAZZINO' => 'E01',
                      'ID_COMMESSA' => 'COMMESSA1',
                      'ID_UBICAZIONE' => 'EEE',
                      'DESCRIZIONE' => 'Lorem Ipsum is simply dummy text of the printing and typesetting industry.',
                      'DISEGNO' => 'ABC',
                      'R_UM_PRM_MAG' => 'NR',
                      'QTA_GIAC_PRM' => 10,
                      'TRASFERIBILE' => 'Y',
                      'R_UTENTE_AGG' => '001_finsoft         ',
                      'TIMESTAMP_AGG' => '2022-09-21 16:25:53.410'
                    ],
                    [ 'ID_ARTICOLO' => '00000564                 ',
                    'ID_MAGAZZINO' => 'E01',
                    'ID_COMMESSA' => 'COMMESSA2',
                    'ID_UBICAZIONE' => 'EEE',
                    'DESCRIZIONE' => 'Lorem Ipsum is simply dummy text of the printing and typesetting industry.',
                    'DISEGNO' => 'ABC',
                    'R_UM_PRM_MAG' => 'NR',
                    'QTA_GIAC_PRM' => 12,
                    'TRASFERIBILE' => 'Y',
                    'R_UTENTE_AGG' => '001_finsoft         ',
                    'TIMESTAMP_AGG' => '2022-09-21 16:25:53.410'
                ]]; 
          $count = 1;
          
      } else {

          $trasferibile = $this->check_stato_ubicazione($codUbicazione);
          $str_trasferibile = $trasferibile ? " AND YU.TRASFERIBILE='Y' " : "";
          $this->check_articolo($codArticolo);

          $sql0 = "SELECT COUNT(*) AS cnt ";
          $sql1 = "SELECT U.ID_UBICAZIONE, U.ID_MAGAZZINO, U.R_UTENTE_AGG, U.TIMESTAMP_AGG, S.ID_ARTICOLO, A.DESCRIZIONE, A.DISEGNO, A.R_UM_PRM_MAG, S.ID_COMMESSA, S.QTA_GIAC_PRM ";
          
          $sql2 = "FROM THIP.UBICAZIONI_LL U
                  JOIN THIPPERS.YUBICAZIONI_LL YU
                    ON U.ID_AZIENDA=YU.ID_AZIENDA AND U.ID_UBICAZIONE=YU.ID_UBICAZIONE AND U.ID_MAGAZZINO=YU.ID_MAGAZZINO
                  JOIN THIP.SALDI_UBICAZIONE S
                    ON U.ID_AZIENDA=S.ID_AZIENDA AND U.ID_UBICAZIONE=S.ID_UBICAZIONE AND U.ID_MAGAZZINO=S.ID_MAGAZZINO
                  JOIN THIP.ARTICOLI A
                    ON S.ID_AZIENDA=A.ID_AZIENDA AND S.ID_ARTICOLO=A.ID_ARTICOLO
                  WHERE U.ID_AZIENDA='$ID_AZIENDA' AND U.ID_UBICAZIONE='$codUbicazione' AND S.ID_ARTICOLO='$codArticolo' $str_trasferibile AND U.STATO='V' ";
          
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
    function getUbicazioneByMagazzino($idMagazzino,$codUbicazione) {
      global $panthera, $ID_AZIENDA, $caricamentiMassaManager;

      if ($panthera->mock) {
          $data = [ [ 'ID_ARTICOLO' => '00000000                 ','ID_COMMESSA' => 'COMMESSA1', 'ID_MAGAZZINO' => 'E01', 'ID_UBICAZIONE' => 'EEE', 'DESCRIZIONE' => 'Lorem Ipsum is simply dummy text of the printing and typesetting industry.', 'DISEGNO' => 'ABC', 'R_UM_PRM_MAG' => 'NR', 'QTA_GIAC_PRM' => 10, 'TRASFERIBILE' => 'Y', 'R_UTENTE_AGG' => '001_finsoft         ', 'TIMESTAMP_AGG' => '2022-09-21 16:25:53.410' ],
                    [ 'ID_ARTICOLO' => '00000564                 ', 'ID_MAGAZZINO' => 'E01', 'ID_UBICAZIONE' => 'EEE', 'DESCRIZIONE' => 'Lorem Ipsum is simply dummy text of the printing and typesetting industry.', 'DISEGNO' => 'ABC', 'R_UM_PRM_MAG' => 'NR', 'QTA_GIAC_PRM' => 100, 'TRASFERIBILE' => 'Y', 'R_UTENTE_AGG' => '001_finsoft         ', 'TIMESTAMP_AGG' => '2022-09-21 16:25:53.410' ],
                    [ 'ID_ARTICOLO' => '00003289                 ', 'ID_MAGAZZINO' => 'E01', 'ID_UBICAZIONE' => 'FFF', 'DESCRIZIONE' => 'Lorem Ipsum is simply dummy text of the printing and typesetting industry.', 'DISEGNO' => 'ABC', 'R_UM_PRM_MAG' => 'LT', 'QTA_GIAC_PRM' => 0, 'TRASFERIBILE' => 'Y', 'R_UTENTE_AGG' => '001_finsoft         ', 'TIMESTAMP_AGG' => '2022-09-21 16:25:53.410' ],
                    [ 'ID_ARTICOLO' => 'DDDD', 'ID_MAGAZZINO' => 'E01', 'ID_UBICAZIONE' => 'EEE', 'DESCRIZIONE' => 'Lorem Ipsum is simply dummy text of the printing and typesetting industry.', 'DISEGNO' => 'ABC', 'R_UM_PRM_MAG' => 'NR', 'QTA_GIAC_PRM' => 100, 'TRASFERIBILE' => 'Y', 'R_UTENTE_AGG' => '001_finsoft         ', 'TIMESTAMP_AGG' => '2022-09-21 16:25:53.410' ],
                    [ 'ID_ARTICOLO' => 'EEEE', 'ID_MAGAZZINO' => 'E01', 'ID_UBICAZIONE' => 'FFF', 'DESCRIZIONE' => 'Lorem Ipsum is simply dummy text of the printing and typesetting industry.', 'DISEGNO' => 'ABC', 'R_UM_PRM_MAG' => 'NR', 'QTA_GIAC_PRM' => 0, 'TRASFERIBILE' => 'Y', 'R_UTENTE_AGG' => '001_finsoft         ', 'TIMESTAMP_AGG' => '2022-09-21 16:25:53.410' ],
                    [ 'ID_ARTICOLO' => 'FFFF', 'ID_MAGAZZINO' => 'E01', 'ID_UBICAZIONE' => 'EEE', 'DESCRIZIONE' => 'Lorem Ipsum is simply dummy text of the printing and typesetting industry.', 'DISEGNO' => 'ABC', 'R_UM_PRM_MAG' => 'NR', 'QTA_GIAC_PRM' => 100, 'TRASFERIBILE' => 'Y', 'R_UTENTE_AGG' => '001_finsoft         ', 'TIMESTAMP_AGG' => '2022-09-21 16:25:53.410' ],
                    [ 'ID_ARTICOLO' => 'GGGG', 'ID_MAGAZZINO' => 'E01', 'ID_UBICAZIONE' => 'FFF', 'DESCRIZIONE' => 'Lorem Ipsum is simply dummy text of the printing and typesetting industry.', 'DISEGNO' => 'ABC', 'R_UM_PRM_MAG' => 'MMT', 'QTA_GIAC_PRM' => 0, 'TRASFERIBILE' => 'Y', 'R_UTENTE_AGG' => '001_finsoft         ', 'TIMESTAMP_AGG' => '2022-09-21 16:25:53.410' ],
                    [ 'ID_ARTICOLO' => 'HHHH', 'ID_MAGAZZINO' => 'E01', 'ID_UBICAZIONE' => 'EEE', 'DESCRIZIONE' => 'Lorem Ipsum is simply dummy text of the printing and typesetting industry.', 'DISEGNO' => 'ABC', 'R_UM_PRM_MAG' => 'NR', 'QTA_GIAC_PRM' => 100, 'TRASFERIBILE' => 'Y', 'R_UTENTE_AGG' => '001_finsoft         ', 'TIMESTAMP_AGG' => '2022-09-21 16:25:53.410' ],
                    [ 'ID_ARTICOLO' => 'IIII', 'ID_MAGAZZINO' => 'E01', 'ID_UBICAZIONE' => 'FFF', 'DESCRIZIONE' => 'Lorem Ipsum is simply dummy text of the printing and typesetting industry.', 'DISEGNO' => 'ABC', 'R_UM_PRM_MAG' => 'NR', 'QTA_GIAC_PRM' => 0, 'TRASFERIBILE' => 'Y', 'R_UTENTE_AGG' => '001_finsoft         ', 'TIMESTAMP_AGG' => '2022-09-21 16:25:53.410' ]
                   ]; 
          $count = 9;
      } else {

          $sql0 = "SELECT COUNT(*) AS cnt ";
          $sql1 = "SELECT U.* ";
          $sql2 = "FROM THIP.UBICAZIONI_LL U
                  JOIN THIPPERS.YUBICAZIONI_LL YU
                    ON U.ID_AZIENDA=YU.ID_AZIENDA AND U.ID_UBICAZIONE=YU.ID_UBICAZIONE AND U.ID_MAGAZZINO=YU.ID_MAGAZZINO
                  WHERE U.ID_AZIENDA='$ID_AZIENDA' AND U.ID_UBICAZIONE='$codUbicazione' AND U.ID_MAGAZZINO='$idMagazzino' AND U.STATO='V' ";
          $sql3 = " ";
          $count = $panthera->select_single_value($sql0 . $sql2);
          $data = $panthera->select_list($sql1 . $sql2 . $sql3);

          if($count == 0){
            $caricamentiMassaManager->chiama_ws_pantheraMagazzini();
          }
      }
      
      // se l'ubicazione e' vuota non do' errori
      return [$data, $count];
  }
/**
     * Restituisce tutti gli articoli contenuti in una certa ubicazione senza controllare la qnt
     */
    function getContenutoUbicazioneNoQnt($codUbicazione) {
      global $panthera, $ID_AZIENDA;

      if ($panthera->mock) {
          $data = [ [ 'ID_ARTICOLO' => '00000000                 ','ID_COMMESSA' => 'COMMESSA1', 'ID_MAGAZZINO' => 'E01', 'ID_UBICAZIONE' => 'EEE', 'DESCRIZIONE' => 'Lorem Ipsum is simply dummy text of the printing and typesetting industry.', 'DISEGNO' => 'ABC', 'R_UM_PRM_MAG' => 'NR', 'QTA_GIAC_PRM' => 10, 'TRASFERIBILE' => 'Y', 'R_UTENTE_AGG' => '001_finsoft         ', 'TIMESTAMP_AGG' => '2022-09-21 16:25:53.410' ],
                    [ 'ID_ARTICOLO' => '00000564                 ', 'ID_MAGAZZINO' => 'E01', 'ID_UBICAZIONE' => 'EEE', 'DESCRIZIONE' => 'Lorem Ipsum is simply dummy text of the printing and typesetting industry.', 'DISEGNO' => 'ABC', 'R_UM_PRM_MAG' => 'NR', 'QTA_GIAC_PRM' => 100, 'TRASFERIBILE' => 'Y', 'R_UTENTE_AGG' => '001_finsoft         ', 'TIMESTAMP_AGG' => '2022-09-21 16:25:53.410' ],
                    [ 'ID_ARTICOLO' => '00003289                 ', 'ID_MAGAZZINO' => 'E01', 'ID_UBICAZIONE' => 'FFF', 'DESCRIZIONE' => 'Lorem Ipsum is simply dummy text of the printing and typesetting industry.', 'DISEGNO' => 'ABC', 'R_UM_PRM_MAG' => 'LT', 'QTA_GIAC_PRM' => 0, 'TRASFERIBILE' => 'Y', 'R_UTENTE_AGG' => '001_finsoft         ', 'TIMESTAMP_AGG' => '2022-09-21 16:25:53.410' ],
                    [ 'ID_ARTICOLO' => 'DDDD', 'ID_MAGAZZINO' => 'E01', 'ID_UBICAZIONE' => 'EEE', 'DESCRIZIONE' => 'Lorem Ipsum is simply dummy text of the printing and typesetting industry.', 'DISEGNO' => 'ABC', 'R_UM_PRM_MAG' => 'NR', 'QTA_GIAC_PRM' => 100, 'TRASFERIBILE' => 'Y', 'R_UTENTE_AGG' => '001_finsoft         ', 'TIMESTAMP_AGG' => '2022-09-21 16:25:53.410' ],
                    [ 'ID_ARTICOLO' => 'EEEE', 'ID_MAGAZZINO' => 'E01', 'ID_UBICAZIONE' => 'FFF', 'DESCRIZIONE' => 'Lorem Ipsum is simply dummy text of the printing and typesetting industry.', 'DISEGNO' => 'ABC', 'R_UM_PRM_MAG' => 'NR', 'QTA_GIAC_PRM' => 0, 'TRASFERIBILE' => 'Y', 'R_UTENTE_AGG' => '001_finsoft         ', 'TIMESTAMP_AGG' => '2022-09-21 16:25:53.410' ],
                    [ 'ID_ARTICOLO' => 'FFFF', 'ID_MAGAZZINO' => 'E01', 'ID_UBICAZIONE' => 'EEE', 'DESCRIZIONE' => 'Lorem Ipsum is simply dummy text of the printing and typesetting industry.', 'DISEGNO' => 'ABC', 'R_UM_PRM_MAG' => 'NR', 'QTA_GIAC_PRM' => 100, 'TRASFERIBILE' => 'Y', 'R_UTENTE_AGG' => '001_finsoft         ', 'TIMESTAMP_AGG' => '2022-09-21 16:25:53.410' ],
                    [ 'ID_ARTICOLO' => 'GGGG', 'ID_MAGAZZINO' => 'E01', 'ID_UBICAZIONE' => 'FFF', 'DESCRIZIONE' => 'Lorem Ipsum is simply dummy text of the printing and typesetting industry.', 'DISEGNO' => 'ABC', 'R_UM_PRM_MAG' => 'MMT', 'QTA_GIAC_PRM' => 0, 'TRASFERIBILE' => 'Y', 'R_UTENTE_AGG' => '001_finsoft         ', 'TIMESTAMP_AGG' => '2022-09-21 16:25:53.410' ],
                    [ 'ID_ARTICOLO' => 'HHHH', 'ID_MAGAZZINO' => 'E01', 'ID_UBICAZIONE' => 'EEE', 'DESCRIZIONE' => 'Lorem Ipsum is simply dummy text of the printing and typesetting industry.', 'DISEGNO' => 'ABC', 'R_UM_PRM_MAG' => 'NR', 'QTA_GIAC_PRM' => 100, 'TRASFERIBILE' => 'Y', 'R_UTENTE_AGG' => '001_finsoft         ', 'TIMESTAMP_AGG' => '2022-09-21 16:25:53.410' ],
                    [ 'ID_ARTICOLO' => 'IIII', 'ID_MAGAZZINO' => 'E01', 'ID_UBICAZIONE' => 'FFF', 'DESCRIZIONE' => 'Lorem Ipsum is simply dummy text of the printing and typesetting industry.', 'DISEGNO' => 'ABC', 'R_UM_PRM_MAG' => 'NR', 'QTA_GIAC_PRM' => 0, 'TRASFERIBILE' => 'Y', 'R_UTENTE_AGG' => '001_finsoft         ', 'TIMESTAMP_AGG' => '2022-09-21 16:25:53.410' ]
                   ]; 
          $count = 9;
      } else {

          $trasferibile = $this->check_stato_ubicazione($codUbicazione);
          $str_trasferibile = $trasferibile ? " AND YU.TRASFERIBILE='Y' " : "";
          $sql0 = "SELECT COUNT(*) AS cnt ";
          $sql1 = "SELECT U.ID_UBICAZIONE, U.ID_MAGAZZINO, U.R_UTENTE_AGG, U.TIMESTAMP_AGG, S.ID_ARTICOLO, A.DESCRIZIONE, A.DISEGNO, A.R_UM_PRM_MAG, S.ID_COMMESSA, S.QTA_GIAC_PRM ";
          $sql2 = "FROM THIP.UBICAZIONI_LL U
                  JOIN THIPPERS.YUBICAZIONI_LL YU
                    ON U.ID_AZIENDA=YU.ID_AZIENDA AND U.ID_UBICAZIONE=YU.ID_UBICAZIONE AND U.ID_MAGAZZINO=YU.ID_MAGAZZINO
                  JOIN THIP.SALDI_UBICAZIONE S
                    ON U.ID_AZIENDA=S.ID_AZIENDA AND U.ID_UBICAZIONE=S.ID_UBICAZIONE AND U.ID_MAGAZZINO=S.ID_MAGAZZINO
                  JOIN THIP.ARTICOLI A
                    ON S.ID_AZIENDA=A.ID_AZIENDA AND S.ID_ARTICOLO=A.ID_ARTICOLO
                  WHERE U.ID_AZIENDA='$ID_AZIENDA' AND U.ID_UBICAZIONE='$codUbicazione' AND S.QTA_GIAC_PRM <= 0";
          $sql3 = " ORDER BY S.ID_ARTICOLO";
          //echo $sql1 . $sql2 . $sql3;
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
            $data = [ [ 'ID_ARTICOLO' => '00000000                 ','ID_COMMESSA' => 'COMMESSA1', 'ID_MAGAZZINO' => 'E01', 'ID_UBICAZIONE' => 'EEE', 'DESCRIZIONE' => 'Lorem Ipsum is simply dummy text of the printing and typesetting industry.', 'DISEGNO' => 'ABC', 'R_UM_PRM_MAG' => 'NR', 'QTA_GIAC_PRM' => 10, 'TRASFERIBILE' => 'Y', 'R_UTENTE_AGG' => '001_finsoft         ', 'TIMESTAMP_AGG' => '2022-09-21 16:25:53.410' ],
                      [ 'ID_ARTICOLO' => '00000564                 ', 'ID_MAGAZZINO' => 'E01', 'ID_UBICAZIONE' => 'EEE', 'DESCRIZIONE' => 'Lorem Ipsum is simply dummy text of the printing and typesetting industry.', 'DISEGNO' => 'ABC', 'R_UM_PRM_MAG' => 'NR', 'QTA_GIAC_PRM' => 100, 'TRASFERIBILE' => 'Y', 'R_UTENTE_AGG' => '001_finsoft         ', 'TIMESTAMP_AGG' => '2022-09-21 16:25:53.410' ],
                      [ 'ID_ARTICOLO' => '00003289                 ', 'ID_MAGAZZINO' => 'E01', 'ID_UBICAZIONE' => 'FFF', 'DESCRIZIONE' => 'Lorem Ipsum is simply dummy text of the printing and typesetting industry.', 'DISEGNO' => 'ABC', 'R_UM_PRM_MAG' => 'LT', 'QTA_GIAC_PRM' => 0, 'TRASFERIBILE' => 'Y', 'R_UTENTE_AGG' => '001_finsoft         ', 'TIMESTAMP_AGG' => '2022-09-21 16:25:53.410' ],
                      [ 'ID_ARTICOLO' => 'DDDD', 'ID_MAGAZZINO' => 'E01', 'ID_UBICAZIONE' => 'EEE', 'DESCRIZIONE' => 'Lorem Ipsum is simply dummy text of the printing and typesetting industry.', 'DISEGNO' => 'ABC', 'R_UM_PRM_MAG' => 'NR', 'QTA_GIAC_PRM' => 100, 'TRASFERIBILE' => 'Y', 'R_UTENTE_AGG' => '001_finsoft         ', 'TIMESTAMP_AGG' => '2022-09-21 16:25:53.410' ],
                      [ 'ID_ARTICOLO' => 'EEEE', 'ID_MAGAZZINO' => 'E01', 'ID_UBICAZIONE' => 'FFF', 'DESCRIZIONE' => 'Lorem Ipsum is simply dummy text of the printing and typesetting industry.', 'DISEGNO' => 'ABC', 'R_UM_PRM_MAG' => 'NR', 'QTA_GIAC_PRM' => 0, 'TRASFERIBILE' => 'Y', 'R_UTENTE_AGG' => '001_finsoft         ', 'TIMESTAMP_AGG' => '2022-09-21 16:25:53.410' ],
                      [ 'ID_ARTICOLO' => 'FFFF', 'ID_MAGAZZINO' => 'E01', 'ID_UBICAZIONE' => 'EEE', 'DESCRIZIONE' => 'Lorem Ipsum is simply dummy text of the printing and typesetting industry.', 'DISEGNO' => 'ABC', 'R_UM_PRM_MAG' => 'NR', 'QTA_GIAC_PRM' => 100, 'TRASFERIBILE' => 'Y', 'R_UTENTE_AGG' => '001_finsoft         ', 'TIMESTAMP_AGG' => '2022-09-21 16:25:53.410' ],
                      [ 'ID_ARTICOLO' => 'GGGG', 'ID_MAGAZZINO' => 'E01', 'ID_UBICAZIONE' => 'FFF', 'DESCRIZIONE' => 'Lorem Ipsum is simply dummy text of the printing and typesetting industry.', 'DISEGNO' => 'ABC', 'R_UM_PRM_MAG' => 'MMT', 'QTA_GIAC_PRM' => 0, 'TRASFERIBILE' => 'Y', 'R_UTENTE_AGG' => '001_finsoft         ', 'TIMESTAMP_AGG' => '2022-09-21 16:25:53.410' ],
                      [ 'ID_ARTICOLO' => 'HHHH', 'ID_MAGAZZINO' => 'E01', 'ID_UBICAZIONE' => 'EEE', 'DESCRIZIONE' => 'Lorem Ipsum is simply dummy text of the printing and typesetting industry.', 'DISEGNO' => 'ABC', 'R_UM_PRM_MAG' => 'NR', 'QTA_GIAC_PRM' => 100, 'TRASFERIBILE' => 'Y', 'R_UTENTE_AGG' => '001_finsoft         ', 'TIMESTAMP_AGG' => '2022-09-21 16:25:53.410' ],
                      [ 'ID_ARTICOLO' => 'IIII', 'ID_MAGAZZINO' => 'E01', 'ID_UBICAZIONE' => 'FFF', 'DESCRIZIONE' => 'Lorem Ipsum is simply dummy text of the printing and typesetting industry.', 'DISEGNO' => 'ABC', 'R_UM_PRM_MAG' => 'NR', 'QTA_GIAC_PRM' => 0, 'TRASFERIBILE' => 'Y', 'R_UTENTE_AGG' => '001_finsoft         ', 'TIMESTAMP_AGG' => '2022-09-21 16:25:53.410' ]
                     ]; 
            $count = 9;
        } else {

            $trasferibile = $this->check_stato_ubicazione($codUbicazione);
            $str_trasferibile = $trasferibile ? " AND YU.TRASFERIBILE='Y' " : "";
            $sql0 = "SELECT COUNT(*) AS cnt ";
            $sql1 = "SELECT U.ID_UBICAZIONE, U.ID_MAGAZZINO, U.R_UTENTE_AGG, U.TIMESTAMP_AGG, S.ID_ARTICOLO, A.DESCRIZIONE, A.DISEGNO, A.R_UM_PRM_MAG, S.ID_COMMESSA, S.QTA_GIAC_PRM ";
            $sql2 = "FROM THIP.UBICAZIONI_LL U
                    JOIN THIPPERS.YUBICAZIONI_LL YU
                      ON U.ID_AZIENDA=YU.ID_AZIENDA AND U.ID_UBICAZIONE=YU.ID_UBICAZIONE AND U.ID_MAGAZZINO=YU.ID_MAGAZZINO
                    JOIN THIP.SALDI_UBICAZIONE S
                      ON U.ID_AZIENDA=S.ID_AZIENDA AND U.ID_UBICAZIONE=S.ID_UBICAZIONE AND U.ID_MAGAZZINO=S.ID_MAGAZZINO
                    JOIN THIP.ARTICOLI A
                      ON S.ID_AZIENDA=A.ID_AZIENDA AND S.ID_ARTICOLO=A.ID_ARTICOLO
                    WHERE U.ID_AZIENDA='$ID_AZIENDA' AND U.ID_UBICAZIONE='$codUbicazione' AND S.QTA_GIAC_PRM <> 0 $str_trasferibile AND U.STATO='V' ";
            $sql3 = " ORDER BY S.ID_ARTICOLO";
            $count = $panthera->select_single_value($sql0 . $sql2);
            $data = $panthera->select_list($sql1 . $sql2 . $sql3);
        }
        
        // se l'ubicazione e' vuota non do' errori
        return [$data, $count];
    }
    
    /**
     * Restituisce tutte le ubicazioni che contengono un certo articolo
     */
    function getUbicazioniPerArticolo($codArticolo) {
      global $panthera, $ID_AZIENDA;

      if ($panthera->mock) {
          $data = [ [ 'ID_ARTICOLO' => '00000564                 ',
                      'ID_MAGAZZINO' => 'E01',
                      'ID_COMMESSA' => 'COMMESSA1',
                      'ID_UBICAZIONE' => 'EEE',
                      'DESCRIZIONE' => 'Lorem Ipsum is simply dummy text of the printing and typesetting industry.',
                      'DISEGNO' => 'ABC',
                      'R_UM_PRM_MAG' => 'NR',
                      'QTA_GIAC_PRM' => 10,
                      'TRASFERIBILE' => 'Y',
                      'R_UTENTE_AGG' => '001_finsoft         ',
                      'TIMESTAMP_AGG' => '2022-09-21 16:25:53.410'
                    ],
                    [ 'ID_ARTICOLO' => '00000564                 ',
                    'ID_MAGAZZINO' => 'E01',
                    'ID_COMMESSA' => 'COMMESSA1',
                    'ID_UBICAZIONE' => 'EEE2',
                    'DESCRIZIONE' => 'Lorem Ipsum is simply dummy text of the printing and typesetting industry.',
                    'DISEGNO' => 'ABC',
                    'R_UM_PRM_MAG' => 'NR',
                    'QTA_GIAC_PRM' => 10,
                    'TRASFERIBILE' => 'Y',
                    'R_UTENTE_AGG' => '001_finsoft         ',
                    'TIMESTAMP_AGG' => '2022-09-21 16:25:53.410'
                ]]; 
          $count = 2;
          
      } else {

          $this->check_articolo($codArticolo);

          $sql0 = "SELECT COUNT(*) AS cnt ";
          $sql1 = "SELECT U.ID_UBICAZIONE, U.ID_MAGAZZINO, U.R_UTENTE_AGG, U.TIMESTAMP_AGG, S.ID_ARTICOLO, A.DESCRIZIONE, A.DISEGNO, A.R_UM_PRM_MAG, S.ID_COMMESSA, S.QTA_GIAC_PRM ";
          
          $sql2 = "FROM THIP.UBICAZIONI_LL U
                  JOIN THIPPERS.YUBICAZIONI_LL YU
                    ON U.ID_AZIENDA=YU.ID_AZIENDA AND U.ID_UBICAZIONE=YU.ID_UBICAZIONE AND U.ID_MAGAZZINO=YU.ID_MAGAZZINO
                  JOIN THIP.SALDI_UBICAZIONE S
                    ON U.ID_AZIENDA=S.ID_AZIENDA AND U.ID_UBICAZIONE=S.ID_UBICAZIONE AND U.ID_MAGAZZINO=S.ID_MAGAZZINO
                  JOIN THIP.ARTICOLI A
                    ON S.ID_AZIENDA=A.ID_AZIENDA AND S.ID_ARTICOLO=A.ID_ARTICOLO
                  WHERE U.ID_AZIENDA='$ID_AZIENDA' AND S.ID_ARTICOLO='$codArticolo' AND U.STATO='V'
                  GROUP BY U.ID_UBICAZIONE, U.ID_MAGAZZINO, U.R_UTENTE_AGG, U.TIMESTAMP_AGG, S.ID_ARTICOLO, A.DESCRIZIONE, A.DISEGNO, A.R_UM_PRM_MAG, S.ID_COMMESSA, S.QTA_GIAC_PRM ";
          
          $sql3 = " ORDER BY U.ID_UBICAZIONE, S.ID_ARTICOLO";
          $count = $panthera->select_single_value($sql0 . $sql2);
          $data = $panthera->select_list($sql1 . $sql2 . $sql3);
      }
      
      // se l'ubicazione e' vuota non do' errori
      return [$data, $count];
    }

    /**
     * Restituisce tutte le ubicazioni che contengono un certo articolo
     */
    function getUbicazioniPerArticoloWithQty($codArticolo) {
      global $panthera, $ID_AZIENDA;

      if ($panthera->mock) {
          $data = [ [ 'ID_ARTICOLO' => '00000564                 ',
                      'ID_MAGAZZINO' => 'E01',
                      'ID_COMMESSA' => 'COMMESSA1',
                      'ID_UBICAZIONE' => 'EEE',
                      'DESCRIZIONE' => 'Lorem Ipsum is simply dummy text of the printing and typesetting industry.',
                      'DISEGNO' => 'ABC',
                      'R_UM_PRM_MAG' => 'NR',
                      'QTA_GIAC_PRM' => 10,
                      'TRASFERIBILE' => 'Y',
                      'R_UTENTE_AGG' => '001_finsoft         ',
                      'TIMESTAMP_AGG' => '2022-09-21 16:25:53.410'
                    ],
                    [ 'ID_ARTICOLO' => '00000564                 ',
                    'ID_MAGAZZINO' => 'E01',
                    'ID_COMMESSA' => 'COMMESSA1',
                    'ID_UBICAZIONE' => 'EEE2',
                    'DESCRIZIONE' => 'Lorem Ipsum is simply dummy text of the printing and typesetting industry.',
                    'DISEGNO' => 'ABC',
                    'R_UM_PRM_MAG' => 'NR',
                    'QTA_GIAC_PRM' => 10,
                    'TRASFERIBILE' => 'Y',
                    'R_UTENTE_AGG' => '001_finsoft         ',
                    'TIMESTAMP_AGG' => '2022-09-21 16:25:53.410'
                ]]; 
          $count = 2;
          
      } else {

          $this->check_articolo($codArticolo);

          $sql0 = "SELECT COUNT(*) AS cnt ";
          $sql1 = "SELECT U.ID_UBICAZIONE, U.ID_MAGAZZINO, U.R_UTENTE_AGG, U.TIMESTAMP_AGG, S.ID_ARTICOLO, A.DESCRIZIONE, A.DISEGNO, A.R_UM_PRM_MAG, S.ID_COMMESSA, S.QTA_GIAC_PRM ";
          
          $sql2 = "FROM THIP.UBICAZIONI_LL U
                  JOIN THIPPERS.YUBICAZIONI_LL YU
                    ON U.ID_AZIENDA=YU.ID_AZIENDA AND U.ID_UBICAZIONE=YU.ID_UBICAZIONE AND U.ID_MAGAZZINO=YU.ID_MAGAZZINO
                  JOIN THIP.SALDI_UBICAZIONE S
                    ON U.ID_AZIENDA=S.ID_AZIENDA AND U.ID_UBICAZIONE=S.ID_UBICAZIONE AND U.ID_MAGAZZINO=S.ID_MAGAZZINO
                  JOIN THIP.ARTICOLI A
                    ON S.ID_AZIENDA=A.ID_AZIENDA AND S.ID_ARTICOLO=A.ID_ARTICOLO
                  WHERE U.ID_AZIENDA='$ID_AZIENDA' AND S.ID_ARTICOLO='$codArticolo' AND U.STATO='V' AND S.QTA_GIAC_PRM  > 0
                  GROUP BY U.ID_UBICAZIONE, U.ID_MAGAZZINO, U.R_UTENTE_AGG, U.TIMESTAMP_AGG, S.ID_ARTICOLO, A.DESCRIZIONE, A.DISEGNO, A.R_UM_PRM_MAG, S.ID_COMMESSA, S.QTA_GIAC_PRM ";
          
          $sql3 = " ORDER BY U.ID_UBICAZIONE, S.ID_ARTICOLO";
          //echo $sql1 . $sql2 . $sql3;
          $count = $panthera->select_single_value($sql0 . $sql2);
          $data = $panthera->select_list($sql1 . $sql2 . $sql3);
      }
      
      // se l'ubicazione e' vuota non do' errori
      return [$data, $count];
    }

    function getUbicazioniPerArticoloCommessa($codArticolo,$codCommessa) {
      global $panthera, $ID_AZIENDA;
     
      if ($panthera->mock) {

          $data = [ [ 'ID_ARTICOLO' => '00000564                 ',
                      'ID_MAGAZZINO' => 'E01',
                      'ID_COMMESSA' => 'COMMESSA1',
                      'ID_UBICAZIONE' => 'EEE',
                      'DESCRIZIONE' => 'Lorem Ipsum is simply dummy text of the printing and typesetting industry.',
                      'DISEGNO' => 'ABC',
                      'R_UM_PRM_MAG' => 'NR',
                      'QTA_GIAC_PRM' => 10,
                      'TRASFERIBILE' => 'Y',
                      'R_UTENTE_AGG' => '001_finsoft         ',
                      'TIMESTAMP_AGG' => '2022-09-21 16:25:53.410'
                    ],[ 'ID_ARTICOLO' => '00000564                 ',
                    'ID_MAGAZZINO' => 'E01',
                    'ID_COMMESSA' => 'COMMESSA1',
                    'ID_UBICAZIONE' => 'EEE2',
                    'DESCRIZIONE' => 'Lorem Ipsum is simply dummy text of the printing and typesetting industry.',
                    'DISEGNO' => 'ABC',
                    'R_UM_PRM_MAG' => 'NR',
                    'QTA_GIAC_PRM' => 10,
                    'TRASFERIBILE' => 'Y',
                    'R_UTENTE_AGG' => '001_finsoft         ',
                    'TIMESTAMP_AGG' => '2022-09-21 16:25:53.410'
                ]]; 
          $count = 1;
          
      } else {

          $this->check_articolo($codArticolo);
          $sql0 = "SELECT COUNT(*) AS cnt ";
          $sql1 = "SELECT U.ID_UBICAZIONE, U.ID_MAGAZZINO, U.R_UTENTE_AGG, U.TIMESTAMP_AGG, S.ID_ARTICOLO, A.DESCRIZIONE, A.DISEGNO, A.R_UM_PRM_MAG, S.ID_COMMESSA, S.QTA_GIAC_PRM ";
          
          switch ($codCommessa) {
            case 'mg':
                $commessa = "RTrim(S.ID_COMMESSA) = 'MG' AND ";
                break;
            case 'notmg':
                $commessa = "RTrim(S.ID_COMMESSA) != 'MG' AND ";
                break;
            default:
                $commessa = "";
                break;
          }

          $sql2 = "FROM THIP.UBICAZIONI_LL U
                  JOIN THIPPERS.YUBICAZIONI_LL YU
                    ON U.ID_AZIENDA=YU.ID_AZIENDA AND U.ID_UBICAZIONE=YU.ID_UBICAZIONE AND U.ID_MAGAZZINO=YU.ID_MAGAZZINO
                  JOIN THIP.SALDI_UBICAZIONE S
                    ON U.ID_AZIENDA=S.ID_AZIENDA AND U.ID_UBICAZIONE=S.ID_UBICAZIONE AND U.ID_MAGAZZINO=S.ID_MAGAZZINO
                  JOIN THIP.ARTICOLI A
                    ON S.ID_AZIENDA=A.ID_AZIENDA AND S.ID_ARTICOLO=A.ID_ARTICOLO
                  WHERE $commessa U.ID_AZIENDA='$ID_AZIENDA' AND S.ID_ARTICOLO='$codArticolo' AND U.STATO='V' AND S.QTA_GIAC_PRM>0
                  GROUP BY U.ID_UBICAZIONE, U.ID_MAGAZZINO, U.R_UTENTE_AGG, U.TIMESTAMP_AGG, S.ID_ARTICOLO, A.DESCRIZIONE, A.DISEGNO, A.R_UM_PRM_MAG, S.ID_COMMESSA, S.QTA_GIAC_PRM ";
          
          $sql3 = " ORDER BY U.ID_UBICAZIONE, S.ID_ARTICOLO";
          //echo $sql1 . $sql2 . $sql3;
          $count = $panthera->select_single_value($sql0 . $sql2);
          $data = $panthera->select_list($sql1 . $sql2 . $sql3);
      }
      
      // se l'ubicazione e' vuota non do' errori
      return [$data, $count];
    }

    /**
     * Questa funzione controlla che l'ubicazione sia esistente e valida
     * @return true se l'ubicazione è trasferibile
     */
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
        print_error(404, "Ubicazione inesistente: $codUbicazione");
      }

      $sql = "SELECT COUNT(*)
              FROM THIP.UBICAZIONI_LL U
              JOIN THIPPERS.YUBICAZIONI_LL YU
                ON U.ID_AZIENDA=YU.ID_AZIENDA AND U.ID_UBICAZIONE=YU.ID_UBICAZIONE AND U.ID_MAGAZZINO=YU.ID_MAGAZZINO
              WHERE U.ID_AZIENDA='$ID_AZIENDA' AND U.ID_UBICAZIONE='$codUbicazione' AND U.STATO='V'
                ";
      $count = $panthera->select_single_value($sql);
      if ($count == 0) {
        print_error(404, "Ubicazione esistente ma in stato non valido: $codUbicazione");
      }

      $sql = "SELECT COUNT(*)
                FROM THIP.UBICAZIONI_LL U
                JOIN THIPPERS.YUBICAZIONI_LL YU
                  ON U.ID_AZIENDA=YU.ID_AZIENDA AND U.ID_UBICAZIONE=YU.ID_UBICAZIONE AND U.ID_MAGAZZINO=YU.ID_MAGAZZINO
                WHERE U.ID_AZIENDA='$ID_AZIENDA' AND U.ID_UBICAZIONE='$codUbicazione' AND YU.TRASFERIBILE='Y' AND U.STATO='V'
                  ";
      $countY = $panthera->select_single_value($sql);
      $sql = "SELECT COUNT(*)
                FROM THIP.UBICAZIONI_LL U
                JOIN THIPPERS.YUBICAZIONI_LL YU
                  ON U.ID_AZIENDA=YU.ID_AZIENDA AND U.ID_UBICAZIONE=YU.ID_UBICAZIONE AND U.ID_MAGAZZINO=YU.ID_MAGAZZINO
                WHERE U.ID_AZIENDA='$ID_AZIENDA' AND U.ID_UBICAZIONE='$codUbicazione' AND YU.TRASFERIBILE='N' AND U.STATO='V'
                  ";
      $countN = $panthera->select_single_value($sql);
      if ($countY > 1) {
        print_error(500, "Errore di configurazione! L'ubicazione trasferibile $codUbicazione è associata a $countY magazzini diversi");
      } elseif ($countY == 0 && $countN > 1) {
        print_error(500, "Errore di configurazione! L'ubicazione non trasferibile $codUbicazione è associata a $countN magazzini diversi");
      }
      return $countY > 0;
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


    function get_articoloUM($codArticolo) {
      global $panthera, $ID_AZIENDA;
      $data = null;
      $sql = "SELECT R_UM_PRM_MAG
              FROM THIP.ARTICOLI A
              WHERE A.ID_AZIENDA='$ID_AZIENDA' AND A.ID_ARTICOLO='$codArticolo' ";
      $data = $panthera->select_single_value($sql);
      if ($data == null) {
        print_error(404, "Articolo inesistente");
      }
      return $data;
    }


    function get_articolo($codArticolo) {
      global $panthera, $ID_AZIENDA;
      $data = null;
      $sql = "SELECT DISTINCT *
              FROM THIP.ARTICOLI A
              WHERE A.ID_AZIENDA='$ID_AZIENDA' AND A.ID_ARTICOLO='$codArticolo' ";
      $data = $panthera->select_single_value($sql);
      if ($data == null) {
        print_error(404, "Articolo inesistente");
      }
      return $data;
    }

    /**
     * Restituisce l'unica ubicazione TRASFERIBILE con un certo codice
     */
    function getUbicazione($codUbicazione) {
      global $panthera, $ID_AZIENDA;

      if ($panthera->mock) {
        return [ 'ID_ARTICOLO' => 'AAAAA', 'ID_MAGAZZINO' => 'E1', 'ID_UBICAZIONE' => 'EEE', 'DESCRIZIONE' => 'XXX', 'TRASFERIBILE' => 'Y' , 'NOTE' => 'Nota 1', 'NOTE_POSIZIONE' => 'Nota posizione 1'];
      } else {

        $trasferibile = $this->check_stato_ubicazione($codUbicazione);
        $str_trasferibile = $trasferibile ? "AND YU.TRASFERIBILE='Y'" : "";

        $sql = "SELECT *
                FROM THIP.UBICAZIONI_LL U
                JOIN THIPPERS.YUBICAZIONI_LL YU
                    ON U.ID_AZIENDA=YU.ID_AZIENDA AND U.ID_UBICAZIONE=YU.ID_UBICAZIONE AND U.ID_MAGAZZINO=YU.ID_MAGAZZINO
                WHERE U.ID_AZIENDA='$ID_AZIENDA'
                    AND U.ID_UBICAZIONE='$codUbicazione'
                    $str_trasferibile
                    AND U.STATO='V'";
        return $panthera->select_single($sql);
      }
    }

    /**
     * FUNZIONE salva note
     */
    function salvaNote($codUbicazione, $note, $notePosizione) {
      global $panthera, $ID_AZIENDA, $logManager;

      if ($panthera->mock) {
        return;
      }

      $trasferibile = $this->check_stato_ubicazione($codUbicazione);
      $str_trasferibile = $trasferibile ? " AND YU.TRASFERIBILE='Y' " : "";

      // reperisco i vecchi valori a scopo di log
      $sql = "SELECT U.ID_MAGAZZINO, U.NOTE, YU.NOTE_POSIZIONE
              FROM THIP.UBICAZIONI_LL U
              JOIN THIPPERS.YUBICAZIONI_LL YU
                  ON U.ID_AZIENDA=YU.ID_AZIENDA AND U.ID_UBICAZIONE=YU.ID_UBICAZIONE AND U.ID_MAGAZZINO=YU.ID_MAGAZZINO
              WHERE U.ID_AZIENDA='$ID_AZIENDA' AND U.ID_UBICAZIONE='$codUbicazione' $str_trasferibile AND U.STATO='V'";
      $old = $panthera->select_single($sql);

      // lo setto a tappeto su tutti i magazzini!
      $sql = "UPDATE THIP.UBICAZIONI_LL
              SET NOTE='$note'
              WHERE ID_AZIENDA='$ID_AZIENDA' AND ID_UBICAZIONE='$codUbicazione' ";
      $panthera->execute_update($sql);
      $sql = "UPDATE THIPPERS.YUBICAZIONI_LL
              SET NOTE_POSIZIONE='$notePosizione'
              WHERE ID_AZIENDA='$ID_AZIENDA' AND ID_UBICAZIONE='$codUbicazione' ";
      $panthera->execute_update($sql);

      $this->updateDatiComuniUbicazione($codUbicazione);
      $logManager->log($old['ID_MAGAZZINO'], $codUbicazione, 'Note', $old['NOTE'], $note);
      $logManager->log($old['ID_MAGAZZINO'], $codUbicazione, 'Note Posizione', $old['NOTE_POSIZIONE'], $notePosizione);
    }

    function updateDatiComuniUbicazione($codUbicazione, $codMagazzino = null) {
      global $panthera, $ID_AZIENDA, $logged_user;

      // lo setto a tappeto su tutti i magazzini!
      $sql = "UPDATE THIP.UBICAZIONI_LL
              SET R_UTENTE_AGG='{$logged_user->nome_utente}_$ID_AZIENDA',
                  TIMESTAMP_AGG=CURRENT_TIMESTAMP
              WHERE ID_AZIENDA='$ID_AZIENDA' AND ID_UBICAZIONE='$codUbicazione' ";
      if($codMagazzino != null) {
        $sql .= "AND ID_MAGAZZINO = '$codMagazzino' ";
      }
      $panthera->execute_update($sql);
    }
}