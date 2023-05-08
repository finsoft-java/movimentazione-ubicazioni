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

/*
STATO 
  A = ANNULLATO
  V = VALIDO
  S= SOSPESO
STATO AVANZAMENTO 
  2= DEFINITIVO
  1= PROVVISORIO
  0= TEMPLATE 
R_CAU_DOC_TRA
  CAUSALI DEFINITE DA config.php
*/

        $sql0 = "SELECT COUNT(*) AS cnt ";
        $sql1 = "SELECT * ";
        $sql = "FROM THIP.DOC_TRA_TES T
                WHERE T.ID_AZIENDA='$ID_AZIENDA'
                AND T.STATO = 'V' 
                AND T.STATO_AVANZAMENTO='1'
                AND T.R_CAU_DOC_TRA IN ('" . implode("','", $CAUSALI_RICHIESTE_MOV) . "')
                AND EXISTS( SELECT 1 FROM THIP.DOC_TRA_RIG R
                WHERE R.ID_AZIENDA=T.ID_AZIENDA AND R.ID_ANNO_DOC=T.ID_ANNO_DOC AND R.ID_NUMERO_DOC=T.ID_NUMERO_DOC AND R.STATO= 'V' AND R.STATO_AVANZAMENTO='1')";
        $count = $panthera->select_single_value($sql0 . $sql);

        $sql .= " ORDER BY T.ID_ANNO_DOC DESC, T.ID_NUMERO_DOC DESC";
        
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
                      'R_COMMESSA' => null,
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
                      'R_COMMESSA' => null,
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
                      'R_COMMESSA' => 'C0001',
                      'R_UM_PRM_MAG' => 'LT',
                      'QTA_UM_PRM' => '1000'
                    ]
                  ];
      } else {

        $sql = "SELECT * FROM THIP.DOC_TRA_RIG
                WHERE ID_AZIENDA='$ID_AZIENDA' AND ID_ANNO_DOC='$idAnnoDoc' AND ID_NUMERO_DOC='$idNumeroDoc'
                AND STATO= 'V' AND STATO_AVANZAMENTO='1'
                ORDER BY ID_RIGA_DOC";
        $data = $panthera->select_list($sql);
      }
      
      return $data;
    }

    function getRichiestaByNumeroDoc($idNumeroDoc) {
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
                      'R_COMMESSA' => null,
                      'R_UM_PRM_MAG' => 'NR',
                      'QTA_UM_PRM' => '10'
                    ]
                  ];
      } else {

        $sql = "SELECT DISTINCT * FROM THIP.DOC_TRA_TES
                WHERE ID_AZIENDA='$ID_AZIENDA' AND ID_NUMERO_DOC LIKE '%$idNumeroDoc%'
                AND STATO= 'V' AND STATO_AVANZAMENTO='1'";
        
        $data = $panthera->select_list($sql);

      }
      
      return $data;
    }

}