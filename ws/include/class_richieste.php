<?php

$richiesteMovimentazioneManager = new RichiesteMovimentazioneManager();

class RichiesteMovimentazioneManager {
    
    function getRichieste($skip=null, $top=null) {
      global $panthera, $ID_AZIENDA, $CAUSALI_RICHIESTE_MOV;

      if ($panthera->mock) {
          $data = [ [
                      'ID_ANNO_DOC' => '2022',
                      'ID_NUMERO_DOC' => 'BL 007106',
                      'DATA_DOC' => '2022-10-07 00:00:00.000',
                      'R_CAU_DOC_TRA' => 'T01',
                      'NUMERO_DOC_FMT' => '2022/BL/ 007106',
                      'R_MAGAZZINO' => '001',
                      'R_MAGAZZINO_ARR' => '002',
                      'STATO' => 'V',
                      'R_UTENTE_CRZ' => 'mfalosai_001'
                    ],
                    [
                      'ID_ANNO_DOC' => '2022',
                      'ID_NUMERO_DOC' => 'BL 007107',
                      'DATA_DOC' => '2022-10-07 00:00:00.000',
                      'R_CAU_DOC_TRA' => 'T01',
                      'NUMERO_DOC_FMT' => '2022/BL/ 007107',
                      'R_MAGAZZINO' => '001',
                      'R_MAGAZZINO_ARR' => '002',
                      'STATO' => 'V',
                      'R_UTENTE_CRZ' => 'mfalosai_001'
                    ],
                    [
                      'ID_ANNO_DOC' => '2022',
                      'ID_NUMERO_DOC' => 'BL 007108',
                      'DATA_DOC' => '2022-10-07 00:00:00.000',
                      'R_CAU_DOC_TRA' => 'T01',
                      'NUMERO_DOC_FMT' => '2022/BL/ 007108',
                      'R_MAGAZZINO' => '001',
                      'R_MAGAZZINO_ARR' => '002',
                      'STATO' => 'V',
                      'R_UTENTE_CRZ' => 'mfalosai_001'
                    ]
                  ];
          $count = 10; // metto 10 anziche' 3 per testare l'infinity scroll
      } else {


        $sql0 = "SELECT COUNT(*) AS cnt ";
        $sql1 = "SELECT * ";
        $sql = "FROM THIP.DOC_TRA_TES
                WHERE ID_AZIENDA='$ID_AZIENDA'
                AND STATO <>'A' AND STATO_AVANZAMENTO='0'
                AND R_CAU_DOC_TRA IN ('" . implode("','", $CAUSALI_RICHIESTE_MOV) . "')";
        $count = $panthera->select_single_value($sql0 . $sql);

        $sql .= " ORDER BY ID_ANNO_DOC DESC, ID_NUMERO_DOC DESC";
        
        if ($top != null) {
          if ($skip != null) {
            $sql .= " OFFSET $skip ROWS FETCH NEXT $top ROWS ONLY ";
          } else {
            $sql .= " OFFSET 0 ROWS FETCH NEXT $top ROWS ONLY ";
          }
        }

        $data = $panthera->select_list($sql1 . $sql);
      }
      
      return [$data, $count];
    }

    function getRichiesta($idAnnoDoc, $idNumeroDoc) {
      global $panthera, $ID_AZIENDA;

      if ($panthera->mock) {
          $data = [ [
                      'ID_ANNO_DOC' => '2022',
                      'ID_NUMERO_DOC' => 'BL 007106',
                      'ID_RIGA_DOC' => '1',
                      'DATA_DOC' => '2022-10-07 00:00:00.000',
                      'R_CAU_DOC_TRA' => 'T01',
                      'NUMERO_DOC_FMT' => '2022/BL/ 007106',
                      'R_MAGAZZINO' => '001',
                      'R_MAGAZZINO_ARR' => '002',
                      'STATO' => 'V',
                      'R_UTENTE_CRZ' => 'mfalosai_001',
                      'R_ARTICOLO' => '00000000',
                      'R_COMMESSE' => null,
                      'R_UM_PRM_MAG' => 'NR',
                      'QTA_UM_PRM' => '10'
                    ],
                    [
                      'ID_ANNO_DOC' => '2022',
                      'ID_NUMERO_DOC' => 'BL 007106',
                      'ID_RIGA_DOC' => '2',
                      'DATA_DOC' => '2022-10-07 00:00:00.000',
                      'R_CAU_DOC_TRA' => 'T01',
                      'NUMERO_DOC_FMT' => '2022/BL/ 007106',
                      'R_MAGAZZINO' => '001',
                      'R_MAGAZZINO_ARR' => '002',
                      'STATO' => 'V',
                      'R_UTENTE_CRZ' => 'mfalosai_001',
                      'R_ARTICOLO' => '11111111',
                      'R_COMMESSE' => null,
                      'R_UM_PRM_MAG' => 'NR',
                      'QTA_UM_PRM' => '100'
                    ],
                    [
                      'ID_ANNO_DOC' => '2022',
                      'ID_NUMERO_DOC' => 'BL 007106',
                      'ID_RIGA_DOC' => '3',
                      'DATA_DOC' => '2022-10-07 00:00:00.000',
                      'R_CAU_DOC_TRA' => 'T01',
                      'NUMERO_DOC_FMT' => '2022/BL/ 007106',
                      'R_MAGAZZINO' => '001',
                      'R_MAGAZZINO_ARR' => '002',
                      'STATO' => 'V',
                      'R_UTENTE_CRZ' => 'mfalosai_001',
                      'R_ARTICOLO' => '22222222',
                      'R_COMMESSE' => 'C0001',
                      'R_UM_PRM_MAG' => 'LT',
                      'QTA_UM_PRM' => '1000'
                    ]
                  ];
      } else {

        $sql = "SELECT * FROM THIP.DOC_TRA_RIG
                WHERE ID_AZIENDA='$ID_AZIENDA' AND ID_ANNO_DOC='$idAnnoDoc' AND ID_NUMERO_DOC='$idNumeroDoc'
                AND STATO<>'A' AND STATO_AVANZAMENTO='0'
                ORDER BY ID_RIGA_DOC";
        $data = $panthera->select_list($sql);
      }
      
      return $data;
    }

}