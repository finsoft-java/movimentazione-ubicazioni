<?php

$carrelliManager = new CarrelliManager();

class CarrelliManager {
    
    function getContenutoCarrello($codCarrello) {
      global $panthera, $ID_AZIENDA;

      if ($panthera->mock) {
          $data = [ [ 'ID_CARRELLO' => 'AAAAAA',
                      'ID_UBICAZIONE' => 'UUUUUU1'
                    ],
                    [ 'ID_CARRELLO' => 'AAAAAA',
                    'ID_UBICAZIONE' => 'UUUUUU2'
                    ],
                    [ 'ID_CARRELLO' => 'AAAAAA',
                    'ID_UBICAZIONE' => 'UUUUUU3'
                    ]
                  ];
          
      } else {

          $sql = "SELECT *
                  FROM THIPPERS.YCARRELLO C
                  JOIN THIPPERS.YUBICAZIONI_CARRELLO UC
                    ON C.ID_AZIENDA=UC.ID_AZIENDA AND C.ID_CARRELLO=UC.ID_CARRELLO
                  WHERE C.ID_AZIENDA='$ID_AZIENDA' AND C.ID_CARRELLO='$codCarrello'
                  ORDER BY UC.ID_UBICAZIONE ";
          $data = $panthera->select_list($sql);
      }
      
      // se l'ubicazione e' vuota non do' errori
      return $data;
    }

    function associa($codCarrello, $codUbicazione) {
      global $panthera, $ID_AZIENDA, $logManager, $ubicazioniManager;

      if ($panthera->mock) {
        return;
      }

      $this->check_stato_ubicazione($codUbicazione);

      $sql = "INSERT INTO THIPPERS.YUBICAZIONI_CARRELLO(ID_AZIENDA,ID_CARRELLO,ID_UBICAZIONE)
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
              WHERE ID_AZIENDA='$ID_AZIENDA' AND ID_CARRELLO='$codCarrello' AND ID_UBICAZIONE='$codUbicazione' ";
      $panthera->execute_update($sql);

      $ubicazioniManager->updateDatiComuniUbicazione($codUbicazione);
    }
}