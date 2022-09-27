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
                  LEFT JOIN THIPPERS.YUBICAZIONI_CARRELLO UC
                    ON C.ID_AZIENDA=UC.ID_AZIENDA AND C.ID_CARRELLO=UC.ID_CARRELLO
                  WHERE C.ID_AZIENDA='$ID_AZIENDA' AND C.ID_CARRELLO='$codCarrello'
                  ORDER BY UC.PROGRESSIVO ";
          $data = $panthera->select_list($sql);
      }
      
      // se l'ubicazione e' vuota non do' errori
      return $data;
    }

    function check_carrello($codCarrello) {
      global $panthera, $ID_AZIENDA;

      $sql = "SELECT STATO FROM THIPPERS.YCARRELLO WHERE C.ID_AZIENDA='$ID_AZIENDA' AND C.ID_CARRELLO='$codCarrello'";
      $data = $panthera->select_single_value($sql);
      if (empty($stato)) {
        print_error(404, "Carrello inesistente: $codCarrello");
      } elseif ($stato != 'V') {
        print_error(404, "Carrello in stato non valido: $codCarrello");
      }
    }

    function associa($codCarrello, $codUbicazione) {
      global $panthera, $ID_AZIENDA, $logManager, $ubicazioniManager;

      if ($panthera->mock) {
        return;
      }

      $this->check_stato_ubicazione($codUbicazione);

      $sql = "INSERT INTO THIPPERS.YUBICAZIONI_CARRELLO(ID_AZIENDA,ID_CARRELLO,R_UBICAZIONE)
              VALUES('$ID_AZIENDA','$codCarrello','$codUbicazione') ";
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
}