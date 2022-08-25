<?php

$logManager = new LogManager();

class LogManager {
    
  /**
   * Inserisce una riga di log
   */
  function log($idUbicazione, $severity, $msg) {
    global $panthera, $ID_AZIENDA;
    if ($panthera->mock) {
      return;
    }

    $severity = $panthera->escape_string($severity);
    $msg = $panthera->escape_string($msg);

    // TIMESTAMP ha default value
    $sql = "INSERT INTO THIPPERS.LOG_UBICAZIONI
            (ID_AZIENDA, ID_UBICAZIONE, SEVERITY, MESSAGE)
            VALUES('$ID_AZIENDA','$idUbicazione','$severity','$msg')";
    $panthera->execute_update($sql);
  }

  /**
   * Restituisce tutte le righe di log con una certa ricerca full-text
   */
  function getLog($search) {
    global $panthera, $ID_AZIENDA;

    if ($panthera->mock) {
        $data = [ [ 'ID_UBICAZIONE' => 'ABC-001', 'TIMESTAMP' => null, 'SEVERITY' => 'ERROR', 'MESSAGE' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua' ],
                  [ 'ID_UBICAZIONE' => 'ABC-001', 'TIMESTAMP' => null, 'SEVERITY' => 'INFO', 'MESSAGE' => 'Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat' ],
                  [ 'ID_UBICAZIONE' => 'ABC-001', 'TIMESTAMP' => null, 'SEVERITY' => 'INFO', 'MESSAGE' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua' ],
                  [ 'ID_UBICAZIONE' => 'ABC-002', 'TIMESTAMP' => null, 'SEVERITY' => 'INFO', 'MESSAGE' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua' ],
                  [ 'ID_UBICAZIONE' => 'ABC-002', 'TIMESTAMP' => null, 'SEVERITY' => 'ERROR', 'MESSAGE' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua' ],
                  [ 'ID_UBICAZIONE' => 'ABC-003', 'TIMESTAMP' => null, 'SEVERITY' => 'ERROR', 'MESSAGE' => 'Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat' ],
                ]; 
        $count = 100;
        
    } else {

        $this->check_stato_ubicazione($codUbicazione);
        $this->check_articolo($codArticolo);

        $sql0 = "SELECT COUNT(*) AS cnt ";
        $sql1 = "SELECT ID_UBICAZIONE, TIMESTAMP, SEVERITY, MESSAGE ";
        
        $sql2 = "FROM THIPPERS.LOG_UBICAZIONI
                WHERE ID_AZIENDA='$ID_AZIENDA' AND (ID_UBICAZIONE LIKE '%$search%' OR SEVERITY LIKE '%$search%' MESSAGE LIKE '%$search%' )";
        
        $sql3 = " ORDER BY TIMESTAMP DESC";
        $count = $panthera->select_single_value($sql0 . $sql2);
        $data = $panthera->select_list($sql1 . $sql2 . $sql3);
    }
    
    // se l'ubicazione e' vuota non do' errori
    return [$data, $count];
  }
}