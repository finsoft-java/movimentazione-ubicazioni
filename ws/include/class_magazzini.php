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
        global $panthera, $ID_AZIENDA;
       
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

    function getMagazziniAlternativiConUbicazione($ID_MAGAZZINO, $ID_UBICAZIONE) {
        global $panthera, $ID_AZIENDA;
  
        if ($panthera->mock) {
            $data = [ 'M01', 'M02', 'M03' ];
        } else {
            $sql = "SELECT * FROM THIP.UBICAZIONI_LL U
                      JOIN THIPPERS.YUBICAZIONI_LL YU ON U.ID_AZIENDA=YU.ID_AZIENDA AND U.ID_UBICAZIONE=YU.ID_UBICAZIONE AND U.ID_MAGAZZINO=YU.ID_MAGAZZINO 
                      JOIN THIP.MAGAZZINI MG ON MG.ID_MAGAZZINO = U.ID_MAGAZZINO where U.ID_UBICAZIONE = '$ID_UBICAZIONE' AND U.ID_MAGAZZINO = '$ID_MAGAZZINO' AND MG.TIPO_MAGAZ IN('0','2','5','7','9') AND U.ID_AZIENDA= '$ID_AZIENDA' ORDER BY 1";
            $data = $panthera->select_list($sql);

        }
        $count = count($data);
        return $count;
      }			
}