<?php

$magazziniManager = new MagazziniManager();

class MagazziniManager {

    function esisteMagazzino($ID_MAGAZZINO) {
        global $panthera, $ID_AZIENDA;
  
        if ($panthera->mock) {
            return true;
        }
        $sql = "SELECT COUNT(*)
                FROM THIP.MAGAZZINI
                WHERE ID_MAGAZZINO = '$ID_MAGAZZINO' AND TIPO_MAGAZ IN('0','2','5','7','9') AND ID_AZIENDA= '$ID_AZIENDA' ";
        //echo $sql."<br/>";          
        return $panthera->select_column($sql) > 0;
    }

    function checkMagazzino($ID_MAGAZZINO) {
        if (!$this->esisteMagazzino($ID_MAGAZZINO)) {
            print_error(404, "Magazzino $ID_MAGAZZINO non esistente o non valido");
        }
    }

    function getMagazziniAlternativi($ID_MAGAZZINO) {
      global $panthera, $ID_AZIENDA;

      if ($panthera->mock) {
          $data = [ 'M01', 'M02', 'M03' ];
      } else {
          $sql = "SELECT DISTINCT(ID_MAGAZZINO)
                  FROM THIP.MAGAZZINI
                  WHERE ID_MAGAZZINO != '$ID_MAGAZZINO' AND TIPO_MAGAZ IN('0','2','5','7','9') AND ID_AZIENDA= '$ID_AZIENDA'
                  ORDER BY 1";
          //echo $sql."<br/>";          
          $data = $panthera->select_column($sql);
      }
      $count = count($data);
      return [$data, $count];
    }
}