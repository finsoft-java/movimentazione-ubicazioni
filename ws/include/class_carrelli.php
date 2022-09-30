<?php

$carrelliManager = new CarrelliManager();

class CarrelliManager {
    
    function getContenutoCarrello($codCarrello) {
      global $panthera, $ID_AZIENDA;

      if ($panthera->mock) {
          $data = [ [
                      'PROGRESSIVO' => 1,
                      'ID_CARRELLO' => 'AAAAAA',
                      'R_UBICAZIONE' => 'UUUUUU1',
                      'DESCRIZIONE' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. ',
                      'DESCR_RIDOTTA' => 'Lorem ipsum dolor sit amet'
                    ],
                    [
                      'PROGRESSIVO' => 2,
                      'ID_CARRELLO' => 'AAAAAA',
                      'R_UBICAZIONE' => 'UUUUUU2',
                      'DESCRIZIONE' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. ',
                      'DESCR_RIDOTTA' => 'Lorem ipsum dolor sit amet'
                    ],
                    [
                      'PROGRESSIVO' => 3,
                      'ID_CARRELLO' => 'AAAAAA',
                      'R_UBICAZIONE' => 'UUUUUU3',
                      'DESCRIZIONE' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. ',
                      'DESCR_RIDOTTA' => 'Lorem ipsum dolor sit amet'
                    ]
                  ];
          
      } else {

          $this->check_carrello($codCarrello);

          $sql = "SELECT C.STATO,C.ID_CARRELLO,C.DESCRIZIONE,C.DESCR_RIDOTTA,UC.R_UBICAZIONE,UC.PROGRESSIVO
                  FROM THIPPERS.YCARRELLO C
                  JOIN THIPPERS.YUBICAZIONI_CARRELLO UC
                    ON C.ID_AZIENDA=UC.ID_AZIENDA AND C.ID_CARRELLO=UC.ID_CARRELLO
                  WHERE C.ID_AZIENDA='$ID_AZIENDA' AND C.ID_CARRELLO='$codCarrello'
                  ORDER BY UC.PROGRESSIVO ";
          $data = $panthera->select_list($sql);
      }
      
      // se l'ubicazione e' vuota non do' errori
      return $data;
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

    function check_carrelloUbi($codCarrello,$codUbicazione) {
      global $panthera, $ID_AZIENDA;

      $sql = "SELECT count(*) FROM THIPPERS.YUBICAZIONI_CARRELLO WHERE R_UBICAZIONE='$codUbicazione' AND ID_CARRELLO='$codCarrello'";
      $stato = $panthera->select_single_value($sql);
      if ($stato > 0) {
        print_error(404, "Ubicazione $codUbicazione già inserita");
      } 
    }

    function check_carrello($codCarrello) {
      global $panthera, $ID_AZIENDA;

      $sql = "SELECT C.STATO FROM THIPPERS.YCARRELLO C WHERE C.ID_AZIENDA='$ID_AZIENDA' AND C.ID_CARRELLO='$codCarrello'";
      $stato = $panthera->select_single_value($sql);
      if (empty($stato)) {
        print_error(404, "Carrello inesistente: $codCarrello");
      } elseif ($stato != 'V') {
        print_error(404, "Carrello in stato non valido: $codCarrello");
      }
    }

    function associa($codCarrello, $codUbicazione) {
      global $panthera, $ID_AZIENDA, $logManager, $ubicazioniManager,$logged_user;

      if ($panthera->mock) {
        return;
      }

      $this->check_stato_ubicazione($codUbicazione);
      $sql = "SELECT MAX(PROGRESSIVO) FROM THIPPERS.YUBICAZIONI_CARRELLO WHERE ID_CARRELLO='$codCarrello'";
      $progressivo = $panthera->select_single_value($sql);
      if($progressivo == null){
        $progressivo = 1;
      } else {
        $progressivo = $progressivo+1;
      }
      $sql = "INSERT INTO THIPPERS.YUBICAZIONI_CARRELLO(ID_AZIENDA, ID_CARRELLO, R_UBICAZIONE, PROGRESSIVO, STATO, R_UTENTE_CRZ, R_UTENTE_AGG) VALUES 
              ('$ID_AZIENDA','$codCarrello','$codUbicazione', '$progressivo', 'V', '{$logged_user->nome_utente}_$ID_AZIENDA','{$logged_user->nome_utente}_$ID_AZIENDA') ";
      $panthera->execute_update($sql);
      $ubicazioniManager->updateDatiComuniUbicazione($codUbicazione);
    }

    function dissocia($codCarrello, $codUbicazione) {
      global $panthera, $ID_AZIENDA, $logManager, $ubicazioniManager;

      if ($panthera->mock) {
        return;
      }

      $sql = "DELETE FROM THIPPERS.YUBICAZIONI_CARRELLO
              WHERE ID_AZIENDA='$ID_AZIENDA' AND ID_CARRELLO='$codCarrello' AND R_UBICAZIONE='$codUbicazione' ";
      $panthera->execute_update($sql);

      $ubicazioniManager->updateDatiComuniUbicazione($codUbicazione);
    }

    function getMagazzinoCarrello($codCarrello) {
      global $panthera, $ID_AZIENDA, $ubicazioniManager;

      // faccio una query per tirarmi fuori la prima ubicazione del carrello 
      $sql = "SELECT TOP 1 UC.R_UBICAZIONE
              FROM THIPPERS.YCARRELLO C
              JOIN THIPPERS.YUBICAZIONI_CARRELLO UC
                ON C.ID_AZIENDA=UC.ID_AZIENDA AND C.ID_CARRELLO=UC.ID_CARRELLO
              WHERE C.ID_AZIENDA='$ID_AZIENDA' AND C.ID_CARRELLO='$codCarrello'
              ORDER BY UC.PROGRESSIVO ";
      $primaUbicazione = $panthera->select_single($sql);

      if ($primaUbicazione == null) {
        // carrello vuoto: quindi il magazzino non è definito
        return null;
      }

      return $ubicazioniManager->getUbicazione($primaUbicazione)['ID_MAGAZZINO'];
    }
}