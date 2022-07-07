<?php

$ubicazioniManager = new UbicazioniManager();

class UbicazioniManager {
    
    function getUbicazioni($codUbicazione) {
        global $panthera;

        if ($panthera->mock) {
            $data = [ [ 'R_ARTICOLO' => 'AAAAA', 'R_UBICAZIONE' => 'EEE', 'DESCRIZIONE' => 'XXX', 'QTY' => 10 ],
                      [ 'R_ARTICOLO' => 'BBBB', 'R_UBICAZIONE' => 'EEE', 'DESCRIZIONE' => 'YYY', 'QTY' => 100 ],
                      [ 'R_ARTICOLO' => 'ZZZZZZ', 'R_UBICAZIONE' => 'FFF', 'DESCRIZIONE' => 'ZZZ', 'QTY' => 0 ]
                     ];
            $count = 1000;
        } else {
            $sql0 = "SELECT COUNT(*) AS cnt ";
            $sql1 = "SELECT * ";
            $sql2 = "FROM THIP.UBICAZIONI_LL A
                    JOIN THIPPERS.YUBICAZIONI_LL B ON  
                    WHERE ID_AZIENDA='001' AND R_UBICAZIONE='$codUbicazione' AND QTY > 0
                    ORDER BY 1";
            $count = $this->select_single_value($sql0 . $sql2);

            $data = $this->select_list($sql1 . $sql2);
        }
        
        return [$data, $count];
    }
}