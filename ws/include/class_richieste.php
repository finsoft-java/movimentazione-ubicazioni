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

        /*
        $sql = "SELECT DISTINCT * FROM (
                SELECT COSTO,DATA_REGISTRAZIONE,DES_ARTICOLO,DET_RIGA_DOC_RIF,DTA_RIFER_DOC,FLAG_RIS_UTE_1,FLAG_RIS_UTE_2,FLAG_RIS_UTE_3,FLAG_RIS_UTE_4,FLAG_RIS_UTE_5,FTT_CONVER_UM,ID_ANNO_DOC,ID_AZIENDA,ID_DET_RIGA_DOC,ID_NUMERO_DOC,ID_RIGA_DOC,NOTA,NUM_RIGA_DOC_RIF,NUM_RIS_UTE_1,NUM_RIS_UTE_2,OPER_CONVER_UM,QTA_UM_PRM,QTA_UM_SEC,R_ARTICOLO,R_CAU_RIG_DOCTRA,R_CENTRO_COSTO,R_CENTRO_RICAVO,R_CLIENTE,R_CLIENTE_ARR,R_COMMESSA,R_COMMESSA_ARR,R_CONFIGURAZIONE,R_DOCUMENTO_MM,R_FORNITORE,R_FORNITORE_ARR,R_GES_COMMENTI,R_GRP_CNT_CA,R_MAGAZZINO,R_MAGAZZINO_ARR,R_OPERAZIONE,R_UBI_ARR,R_UBI_PAR,R_UM_PRM,R_UM_SEC,R_UTENTE_AGG,R_UTENTE_CRZ,R_VERSIONE,RIFER_DOC,SEQUENZA_RIGA,SPL_RIGA,STATO,STATO_AVANZAMENTO,STRINGA_RIS_UTE_1,STRINGA_RIS_UTE_2,TIMESTAMP_AGG,TIMESTAMP_CRZ
                  FROM THIP.DOC_TRA_RIG
                WHERE ID_AZIENDA='$ID_AZIENDA' AND ID_ANNO_DOC='$idAnnoDoc' AND ID_NUMERO_DOC='$idNumeroDoc'
                AND STATO= 'V' AND STATO_AVANZAMENTO='1' AND QTA_UM_PRM > 0
                UNION 
                SELECT COSTO,DATA_REGISTRAZIONE,DES_ARTICOLO,DET_RIGA_DOC_RIF,DTA_RIFER_DOC,FLAG_RIS_UTE_1,FLAG_RIS_UTE_2,FLAG_RIS_UTE_3,FLAG_RIS_UTE_4,FLAG_RIS_UTE_5,FTT_CONVER_UM,ID_ANNO_DOC,ID_AZIENDA,ID_DET_RIGA_DOC,ID_NUMERO_DOC,ID_RIGA_DOC,NOTA,NUM_RIGA_DOC_RIF,NUM_RIS_UTE_1,NUM_RIS_UTE_2,OPER_CONVER_UM,QTA_UM_PRM,QTA_UM_SEC,R_ARTICOLO,R_CAU_RIG_DOCTRA,R_CENTRO_COSTO,R_CENTRO_RICAVO,R_CLIENTE,R_CLIENTE_ARR,R_COMMESSA,R_COMMESSA_ARR,R_CONFIGURAZIONE,R_DOCUMENTO_MM,R_FORNITORE,R_FORNITORE_ARR,R_GES_COMMENTI,R_GRP_CNT_CA,R_MAGAZZINO,R_MAGAZZINO_ARR,R_OPERAZIONE,R_UBI_ARR,R_UBI_PAR,R_UM_PRM,R_UM_SEC,R_UTENTE_AGG,R_UTENTE_CRZ,R_VERSIONE,RIFER_DOC,SEQUENZA_RIGA,SPL_RIGA,STATO,STATO_AVANZAMENTO,STRINGA_RIS_UTE_1,STRINGA_RIS_UTE_2,TIMESTAMP_AGG,TIMESTAMP_CRZ
                  FROM THIP.CM_DOC_TRA_RIG
                WHERE ID_AZIENDA='$ID_AZIENDA' AND ID_ANNO_DOC='$idAnnoDoc' AND ID_NUMERO_DOC='$idNumeroDoc'
                AND STATO= 'V' AND STATO_AVANZAMENTO='1' AND QTA_UM_PRM > 0 ) x";
*/
        $sql = "SELECT DISTINCT * FROM (
          SELECT COSTO,DES_ARTICOLO,DET_RIGA_DOC_RIF,DTA_RIFER_DOC,FLAG_RIS_UTE_1,FLAG_RIS_UTE_2,FLAG_RIS_UTE_3,FLAG_RIS_UTE_4,FLAG_RIS_UTE_5,FTT_CONVER_UM,ID_ANNO_DOC,ID_AZIENDA,ID_DET_RIGA_DOC,ID_NUMERO_DOC,ID_RIGA_DOC,NOTA,NUM_RIGA_DOC_RIF,NUM_RIS_UTE_1,NUM_RIS_UTE_2,OPER_CONVER_UM,QTA_UM_PRM,QTA_UM_SEC,R_ARTICOLO,R_CAU_RIG_DOCTRA,R_CENTRO_COSTO,R_CENTRO_RICAVO,R_CLIENTE,R_CLIENTE_ARR,R_COMMESSA,R_COMMESSA_ARR,R_CONFIGURAZIONE,R_DOCUMENTO_MM,R_FORNITORE,R_FORNITORE_ARR,R_GRP_CNT_CA,R_MAGAZZINO,R_MAGAZZINO_ARR,R_OPERAZIONE,R_UBI_ARR,R_UBI_PAR,R_UM_PRM,R_UM_SEC,R_VERSIONE,RIFER_DOC,SEQUENZA_RIGA,SPL_RIGA,STATO,STATO_AVANZAMENTO,STRINGA_RIS_UTE_1,STRINGA_RIS_UTE_2
            FROM THIP.DOC_TRA_RIG
          WHERE ID_AZIENDA='$ID_AZIENDA' AND ID_ANNO_DOC='$idAnnoDoc' AND ID_NUMERO_DOC='$idNumeroDoc'
          AND STATO= 'V' AND STATO_AVANZAMENTO='1' AND QTA_UM_PRM > 0
          UNION 
          SELECT COSTO,DES_ARTICOLO,DET_RIGA_DOC_RIF,DTA_RIFER_DOC,FLAG_RIS_UTE_1,FLAG_RIS_UTE_2,FLAG_RIS_UTE_3,FLAG_RIS_UTE_4,FLAG_RIS_UTE_5,FTT_CONVER_UM,ID_ANNO_DOC,ID_AZIENDA,ID_DET_RIGA_DOC,ID_NUMERO_DOC,ID_RIGA_DOC,NOTA,NUM_RIGA_DOC_RIF,NUM_RIS_UTE_1,NUM_RIS_UTE_2,OPER_CONVER_UM,QTA_UM_PRM,QTA_UM_SEC,R_ARTICOLO,R_CAU_RIG_DOCTRA,R_CENTRO_COSTO,R_CENTRO_RICAVO,R_CLIENTE,R_CLIENTE_ARR,R_COMMESSA,R_COMMESSA_ARR,R_CONFIGURAZIONE,R_DOCUMENTO_MM,R_FORNITORE,R_FORNITORE_ARR,R_GRP_CNT_CA,R_MAGAZZINO,R_MAGAZZINO_ARR,R_OPERAZIONE,R_UBI_ARR,R_UBI_PAR,R_UM_PRM,R_UM_SEC,R_VERSIONE,RIFER_DOC,SEQUENZA_RIGA,SPL_RIGA,STATO,STATO_AVANZAMENTO,STRINGA_RIS_UTE_1,STRINGA_RIS_UTE_2
            FROM THIP.CM_DOC_TRA_RIG
          WHERE ID_AZIENDA='$ID_AZIENDA' AND ID_ANNO_DOC='$idAnnoDoc' AND ID_NUMERO_DOC='$idNumeroDoc'
          AND STATO= 'V' AND STATO_AVANZAMENTO='1' AND QTA_UM_PRM > 0 ) x";
        $data = $panthera->select_list($sql);
      }
      
      return $data;
    }

    function getRichiestaUnion($idAnnoDoc, $idNumeroDoc) {
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

        $sql = "SELECT COSTO,DATA_REGISTRAZIONE,DES_ARTICOLO,DET_RIGA_DOC_RIF,DTA_RIFER_DOC,FLAG_RIS_UTE_1,FLAG_RIS_UTE_2,FLAG_RIS_UTE_3,FLAG_RIS_UTE_4,FLAG_RIS_UTE_5,FTT_CONVER_UM,ID_ANNO_DOC,ID_AZIENDA,ID_DET_RIGA_DOC,ID_NUMERO_DOC,ID_RIGA_DOC,NOTA,NUM_RIGA_DOC_RIF,NUM_RIS_UTE_1,NUM_RIS_UTE_2,OPER_CONVER_UM,QTA_UM_PRM,QTA_UM_SEC,R_ARTICOLO,R_CAU_RIG_DOCTRA,R_CENTRO_COSTO,R_CENTRO_RICAVO,R_CLIENTE,R_CLIENTE_ARR,R_COMMESSA,R_COMMESSA_ARR,R_CONFIGURAZIONE,R_DOCUMENTO_MM,R_FORNITORE,R_FORNITORE_ARR,R_GES_COMMENTI,R_GRP_CNT_CA,R_MAGAZZINO,R_MAGAZZINO_ARR,R_OPERAZIONE,R_UBI_ARR,R_UBI_PAR,R_UM_PRM,R_UM_SEC,R_UTENTE_AGG,R_UTENTE_CRZ,R_VERSIONE,RIFER_DOC,SEQUENZA_RIGA,SPL_RIGA,STATO,STATO_AVANZAMENTO,STRINGA_RIS_UTE_1,STRINGA_RIS_UTE_2,TIMESTAMP_AGG,TIMESTAMP_CRZ
                 FROM THIP.DOC_TRA_RIG
                WHERE ID_AZIENDA='$ID_AZIENDA' AND ID_ANNO_DOC='$idAnnoDoc' AND ID_NUMERO_DOC='$idNumeroDoc'
                AND STATO= 'V' AND STATO_AVANZAMENTO='1' AND QTA_UM_PRM > 0
                UNION 
                SELECT COSTO,DATA_REGISTRAZIONE,DES_ARTICOLO,DET_RIGA_DOC_RIF,DTA_RIFER_DOC,FLAG_RIS_UTE_1,FLAG_RIS_UTE_2,FLAG_RIS_UTE_3,FLAG_RIS_UTE_4,FLAG_RIS_UTE_5,FTT_CONVER_UM,ID_ANNO_DOC,ID_AZIENDA,ID_DET_RIGA_DOC,ID_NUMERO_DOC,ID_RIGA_DOC,NOTA,NUM_RIGA_DOC_RIF,NUM_RIS_UTE_1,NUM_RIS_UTE_2,OPER_CONVER_UM,QTA_UM_PRM,QTA_UM_SEC,R_ARTICOLO,R_CAU_RIG_DOCTRA,R_CENTRO_COSTO,R_CENTRO_RICAVO,R_CLIENTE,R_CLIENTE_ARR,R_COMMESSA,R_COMMESSA_ARR,R_CONFIGURAZIONE,R_DOCUMENTO_MM,R_FORNITORE,R_FORNITORE_ARR,R_GES_COMMENTI,R_GRP_CNT_CA,R_MAGAZZINO,R_MAGAZZINO_ARR,R_OPERAZIONE,R_UBI_ARR,R_UBI_PAR,R_UM_PRM,R_UM_SEC,R_UTENTE_AGG,R_UTENTE_CRZ,R_VERSIONE,RIFER_DOC,SEQUENZA_RIGA,SPL_RIGA,STATO,STATO_AVANZAMENTO,STRINGA_RIS_UTE_1,STRINGA_RIS_UTE_2,TIMESTAMP_AGG,TIMESTAMP_CRZ
                 FROM THIP.CM_DOC_TRA_RIG
                WHERE ID_AZIENDA='$ID_AZIENDA' AND ID_ANNO_DOC='$idAnnoDoc' AND ID_NUMERO_DOC='$idNumeroDoc'
                AND STATO= 'V' AND STATO_AVANZAMENTO='1' AND QTA_UM_PRM > 0
                ORDER BY ID_RIGA_DOC";
        $data = $panthera->select_list($sql);
      }
      
      return $data;
    }

    function getTestataRichiesta($idAnnoDoc, $idNumeroDoc) {
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

        $sql = "SELECT * FROM THIP.DOC_TRA_TES
                WHERE ID_AZIENDA='$ID_AZIENDA' AND ID_ANNO_DOC='$idAnnoDoc' AND ID_NUMERO_DOC='$idNumeroDoc'
                AND STATO= 'V' AND STATO_AVANZAMENTO='1'";
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

    function checkStatusBatch(){
      global $panthera;
      $sql = "SELECT count(*) FROM THERA.BATCH_JOB WHERE STATUS = 'A' AND SCHEDULED_JOB_ID = 'CMDocTrasf'";
      $count = $panthera->select_single_value($sql);
      return $count;
    }
}