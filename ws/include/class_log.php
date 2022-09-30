<?php

$logManager = new LogManager();

class LogManager {
    
  /**
   * Inserisce una riga di log per l'ubicazione
   */
  function log($idMagazzino, $codUbicazione, $attributeName, $oldValue, $newValue) {
    global $panthera, $ID_AZIENDA, $logged_user;
    if ($panthera->mock) {
      return;
    }

    $attributeName = $panthera->escape_string($attributeName);
    $oldValue = $panthera->escape_string($oldValue);
    $newValue = $panthera->escape_string($newValue);

    $id = $panthera->get_numeratore('LOG_UPDATE');

    // TIMESTAMP ha default value, vero??
    $sql = "INSERT INTO THERA.LOG_UPDATE
                (ID, CLASS_HDR_NAME, OBJECT_KEY, USER_ID, ATTRIBUTE_NAME, OLD_VALUE, NEW_VALUE)
            VALUES('$id',
                'UbicazioneLL',
                CONCAT('$ID_AZIENDA',CHAR(22),'$idMagazzino',CHAR(22),'$codUbicazione'),
                '{$logged_user->nome_utente}_$ID_AZIENDA',
                '$attributeName',
                '$oldValue',
                '$newValue')";
    $panthera->execute_update($sql);
  }
}