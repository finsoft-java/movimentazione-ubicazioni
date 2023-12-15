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
        $sql1 = "SELECT T.*, (select count(*) from THIP.DOC_TRA_RIG R2 where R2.ID_AZIENDA=T.ID_AZIENDA AND R2.ID_ANNO_DOC=T.ID_ANNO_DOC AND R2.ID_NUMERO_DOC=T.ID_NUMERO_DOC AND R2.STATO= 'V' AND R2.STATO_AVANZAMENTO='1' ) as CNT ";
        $sql = "FROM THIP.DOC_TRA_TES T
                WHERE T.ID_AZIENDA='$ID_AZIENDA'
                AND T.STATO = 'V' 
                AND T.STATO_AVANZAMENTO='1'
                AND T.R_CAU_DOC_TRA IN ('" . implode("','", $CAUSALI_RICHIESTE_MOV) . "')
                AND EXISTS( SELECT 1 FROM THIP.DOC_TRA_RIG R
                WHERE R.ID_AZIENDA=T.ID_AZIENDA AND R.ID_ANNO_DOC=T.ID_ANNO_DOC AND R.ID_NUMERO_DOC=T.ID_NUMERO_DOC AND R.STATO= 'V' AND R.STATO_AVANZAMENTO='1')";
        $count = $panthera->select_single_value($sql0 . $sql);

        $sql .= " ORDER BY T.ID_ANNO_DOC ASC, T.ID_NUMERO_DOC ASC";
        
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

    function getRichiesteFiltro($idAnnoDoc, $idNumeroDoc, $search) {
      global $panthera, $ID_AZIENDA;
      $idAnnoDoc = trim($idAnnoDoc);
      $idNumeroDoc = trim($idNumeroDoc);
      $search = trim($search);
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
                WHERE ID_AZIENDA='$ID_AZIENDA' AND ID_ANNO_DOC='$idAnnoDoc' AND ID_NUMERO_DOC='$idNumeroDoc' AND STATO= 'V' AND STATO_AVANZAMENTO='1' 
                AND (R_COMMESSA LIKE '%$search%' OR R_ARTICOLO LIKE '%$search%') ORDER BY ID_RIGA_DOC";
        $data = $panthera->select_list($sql);
      }
      
      return $data;
    }

    function getCountRichiestaUnion($idAnnoDoc, $idNumeroDoc, $idRiga) {
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
        //DOC_TRA_RIG devo cercare != id_riga_doc
        $sql = "
          SELECT COSTO,DATA_REGISTRAZIONE,DES_ARTICOLO,DET_RIGA_DOC_RIF,DTA_RIFER_DOC,FLAG_RIS_UTE_1,FLAG_RIS_UTE_2,FLAG_RIS_UTE_3,FLAG_RIS_UTE_4,FLAG_RIS_UTE_5,FTT_CONVER_UM,ID_ANNO_DOC,ID_AZIENDA,ID_DET_RIGA_DOC,ID_NUMERO_DOC,ID_RIGA_DOC,NOTA,NUM_RIGA_DOC_RIF,NUM_RIS_UTE_1,NUM_RIS_UTE_2,OPER_CONVER_UM,QTA_UM_PRM,QTA_UM_SEC,R_ARTICOLO,R_CAU_RIG_DOCTRA,R_CENTRO_COSTO,R_CENTRO_RICAVO,R_CLIENTE,R_CLIENTE_ARR,R_COMMESSA,R_COMMESSA_ARR,R_CONFIGURAZIONE,R_DOCUMENTO_MM,R_FORNITORE,R_FORNITORE_ARR,R_GES_COMMENTI,R_GRP_CNT_CA,R_MAGAZZINO,R_MAGAZZINO_ARR,R_OPERAZIONE,R_UBI_ARR,R_UBI_PAR,R_UM_PRM,R_UM_SEC,R_UTENTE_AGG,R_UTENTE_CRZ,R_VERSIONE,RIFER_DOC,SEQUENZA_RIGA,SPL_RIGA,STATO,STATO_AVANZAMENTO,STRINGA_RIS_UTE_1,STRINGA_RIS_UTE_2,TIMESTAMP_AGG,TIMESTAMP_CRZ
            FROM THIP.DOC_TRA_RIG
          WHERE ID_AZIENDA='$ID_AZIENDA' AND ID_ANNO_DOC='$idAnnoDoc' AND ID_NUMERO_DOC='$idNumeroDoc' AND ID_RIGA_DOC != '$idRiga'
          AND STATO= 'V' AND STATO_AVANZAMENTO='1' AND QTA_UM_PRM > 0";
              $data = $panthera->select_list($sql);

              $sql2 = "
              SELECT COSTO,DATA_REGISTRAZIONE,DES_ARTICOLO,DET_RIGA_DOC_RIF,DTA_RIFER_DOC,FLAG_RIS_UTE_1,FLAG_RIS_UTE_2,FLAG_RIS_UTE_3,FLAG_RIS_UTE_4,FLAG_RIS_UTE_5,FTT_CONVER_UM,ID_ANNO_DOC,ID_AZIENDA,ID_DET_RIGA_DOC,ID_NUMERO_DOC,ID_RIGA_DOC,NOTA,NUM_RIGA_DOC_RIF,NUM_RIS_UTE_1,NUM_RIS_UTE_2,OPER_CONVER_UM,QTA_UM_PRM,QTA_UM_SEC,R_ARTICOLO,R_CAU_RIG_DOCTRA,R_CENTRO_COSTO,R_CENTRO_RICAVO,R_CLIENTE,R_CLIENTE_ARR,R_COMMESSA,R_COMMESSA_ARR,R_CONFIGURAZIONE,R_DOCUMENTO_MM,R_FORNITORE,R_FORNITORE_ARR,R_GES_COMMENTI,R_GRP_CNT_CA,R_MAGAZZINO,R_MAGAZZINO_ARR,R_OPERAZIONE,R_UBI_ARR,R_UBI_PAR,R_UM_PRM,R_UM_SEC,R_UTENTE_AGG,R_UTENTE_CRZ,R_VERSIONE,RIFER_DOC,SEQUENZA_RIGA,SPL_RIGA,STATO,STATO_AVANZAMENTO,STRINGA_RIS_UTE_1,STRINGA_RIS_UTE_2,TIMESTAMP_AGG,TIMESTAMP_CRZ
                FROM THIP.CM_DOC_TRA_RIG
              WHERE ID_AZIENDA='$ID_AZIENDA' AND ID_ANNO_DOC='$idAnnoDoc' AND ID_NUMERO_DOC='$idNumeroDoc' AND TRASF_STATUS = '0'
              AND STATO= 'V' AND STATO_AVANZAMENTO='1' AND QTA_UM_PRM > 0";
                  $data1 = $panthera->select_list($sql2);

            $d1 = count($data);
            $d2 = count($data1);
/*$data = $panthera->select_list($sql);
$sql = "SELECT DISTINCT * FROM (
              SELECT COSTO,DATA_REGISTRAZIONE,DES_ARTICOLO,DET_RIGA_DOC_RIF,DTA_RIFER_DOC,FLAG_RIS_UTE_1,FLAG_RIS_UTE_2,FLAG_RIS_UTE_3,FLAG_RIS_UTE_4,FLAG_RIS_UTE_5,FTT_CONVER_UM,ID_ANNO_DOC,ID_AZIENDA,ID_DET_RIGA_DOC,ID_NUMERO_DOC,ID_RIGA_DOC,NOTA,NUM_RIGA_DOC_RIF,NUM_RIS_UTE_1,NUM_RIS_UTE_2,OPER_CONVER_UM,QTA_UM_PRM,QTA_UM_SEC,R_ARTICOLO,R_CAU_RIG_DOCTRA,R_CENTRO_COSTO,R_CENTRO_RICAVO,R_CLIENTE,R_CLIENTE_ARR,R_COMMESSA,R_COMMESSA_ARR,R_CONFIGURAZIONE,R_DOCUMENTO_MM,R_FORNITORE,R_FORNITORE_ARR,R_GES_COMMENTI,R_GRP_CNT_CA,R_MAGAZZINO,R_MAGAZZINO_ARR,R_OPERAZIONE,R_UBI_ARR,R_UBI_PAR,R_UM_PRM,R_UM_SEC,R_UTENTE_AGG,R_UTENTE_CRZ,R_VERSIONE,RIFER_DOC,SEQUENZA_RIGA,SPL_RIGA,STATO,STATO_AVANZAMENTO,STRINGA_RIS_UTE_1,STRINGA_RIS_UTE_2,TIMESTAMP_AGG,TIMESTAMP_CRZ
                FROM THIP.DOC_TRA_RIG
              WHERE ID_AZIENDA='$ID_AZIENDA' AND ID_ANNO_DOC='$idAnnoDoc' AND ID_NUMERO_DOC='$idNumeroDoc' AND ID_RIGA_DOC != '$idRiga'
              AND STATO= 'V' AND STATO_AVANZAMENTO='1' AND QTA_UM_PRM > 0
              UNION 
              SELECT COSTO,DATA_REGISTRAZIONE,DES_ARTICOLO,DET_RIGA_DOC_RIF,DTA_RIFER_DOC,FLAG_RIS_UTE_1,FLAG_RIS_UTE_2,FLAG_RIS_UTE_3,FLAG_RIS_UTE_4,FLAG_RIS_UTE_5,FTT_CONVER_UM,ID_ANNO_DOC,ID_AZIENDA,ID_DET_RIGA_DOC,ID_NUMERO_DOC,ID_RIGA_DOC,NOTA,NUM_RIGA_DOC_RIF,NUM_RIS_UTE_1,NUM_RIS_UTE_2,OPER_CONVER_UM,QTA_UM_PRM,QTA_UM_SEC,R_ARTICOLO,R_CAU_RIG_DOCTRA,R_CENTRO_COSTO,R_CENTRO_RICAVO,R_CLIENTE,R_CLIENTE_ARR,R_COMMESSA,R_COMMESSA_ARR,R_CONFIGURAZIONE,R_DOCUMENTO_MM,R_FORNITORE,R_FORNITORE_ARR,R_GES_COMMENTI,R_GRP_CNT_CA,R_MAGAZZINO,R_MAGAZZINO_ARR,R_OPERAZIONE,R_UBI_ARR,R_UBI_PAR,R_UM_PRM,R_UM_SEC,R_UTENTE_AGG,R_UTENTE_CRZ,R_VERSIONE,RIFER_DOC,SEQUENZA_RIGA,SPL_RIGA,STATO,STATO_AVANZAMENTO,STRINGA_RIS_UTE_1,STRINGA_RIS_UTE_2,TIMESTAMP_AGG,TIMESTAMP_CRZ
                FROM THIP.CM_DOC_TRA_RIG
              WHERE ID_AZIENDA='$ID_AZIENDA' AND ID_ANNO_DOC='$idAnnoDoc' AND ID_NUMERO_DOC='$idNumeroDoc' AND TRASF_STATUS = '0'
              AND STATO= 'V' AND STATO_AVANZAMENTO='1' AND QTA_UM_PRM > 0 ) x";
                */
        
      }
      
      return $d1+$d2;
    }


    /***
        [STATO_AVANZAMENTO] => 1
        [ID_AZIENDA] => 001
        [R_CAU_DOC_TRA] => T01
        [ID_ANNO_DOC] => 2022 
        [ID_NUMERO_DOC] => BL  007114
        [DATA_DOC] => 2022-10-13 00:00:00.000
        [NUMERO_DOC_FMT] => 2022/BL/ 007114     
        [R_MAGAZZINO] => STA
        [R_MAGAZZINO_ARR] => STC
        [R_COMMESSA] => 
        [RIFER_DOC] => 
        [DTA_RIFER_DOC] => 1970-01-01 00:00:00.000
        [WF_CLASS_ID] => 
        [WF_ID] => 
        [WF_NODE_ID] => 
        [WF_SUB_NODE_ID] => 
        [NOTA] => OCL 342
        [R_GES_COMMENTI] => 326339
        [R_DOCUMENTO_MM] => 
        [R_CENTRO_COSTO] => 
        [R_CENTRO_RICAVO] =>
        [COL_MAGAZZINO] => 2
        [LIS_CTL_STP_DOC] => N
        [R_GRP_CNT_CA] =>   
        [FLAG_RIS_UTE_1] => -
        [FLAG_RIS_UTE_2] => -
        [FLAG_RIS_UTE_3] => -
        [FLAG_RIS_UTE_4] => -
        [FLAG_RIS_UTE_5] => -
        [STRINGA_RIS_UTE_1] => 
        [STRINGA_RIS_UTE_2] => 
        [NUM_RIS_UTE_1] => 0
        [NUM_RIS_UTE_2] => 0
        [STATO] => V        
        [R_UTENTE_CRZ] => dgadosai_001        
        [R_UTENTE_AGG] => ADMIN_001           
        [TIMESTAMP_CRZ] => 2022-10-13 12:30:38.470
        [TIMESTAMP_AGG] => 2023-11-20 14:38:14.177
        [R_COMMESSA_ARR] => 
        [ANNO_DOC_RIF] => 
        [NUMERO_DOC_RIF] =>           
        [TIPO_DOC_RIF] => T
        [DA_LOGIS_LIGHT] => N
        [DA_GEST_DOC_ORIG] => N
        [ID_ANNO_DOC_PPL] => 
        [ID_NUMERO_DOC_PPL] => 
        [ID_RIGA_DOC_PPL] => 
        [ID_DET_RIGA_DOC_PPL] => 
        [ID_RIGA_LOTTO_PPL] => 
        [ID_LISTA_PRL] => 
        [ID_RIGA_LISTA_PRL] => 
        [R_CLIENTE] => 
        [R_CLIENTE_ARR] => 
        [R_FORNITORE] => 
        [R_FORNITORE_ARR] => 
     **/
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
                AND STATO= 'V' AND STATO_AVANZAMENTO='1' ";
        $data = $panthera->select_list($sql);
        
      }
      
      return $data;
    }

    function mssql_escape_string($data) {
      if (!isset($data) or empty($data)) {
        return '';
      }
      if (is_numeric($data)) {
        return $data;
      }
   
      $non_displayables = array(
        '/%0[0-8bcef]/', // url encoded 00-08, 11, 12, 14, 15
        '/%1[0-9a-f]/', // url encoded 16-31
        '/[\x00-\x08]/', // 00-08
        '/\x0b/', // 11
        '/\x0c/', // 12
        '/[\x0e-\x1f]/' // 14-31
      );
   
      foreach ($non_displayables as $regex) {
        $data = preg_replace($regex, '', $data);
      }
   
      $data = str_replace("'", "''", $data);
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
        $sql0  = "SELECT count(*) FROM THIP.DOC_TRA_TES T ";
        $sql1 = "SELECT DISTINCT T.*, (select count(*) from THIP.DOC_TRA_RIG R2 where R2.ID_AZIENDA=T.ID_AZIENDA AND R2.ID_ANNO_DOC=T.ID_ANNO_DOC AND R2.ID_NUMERO_DOC=T.ID_NUMERO_DOC AND R2.STATO= 'V' AND R2.STATO_AVANZAMENTO='1' ) as CNT FROM THIP.DOC_TRA_TES T ";
        $sql  = "WHERE ID_AZIENDA='$ID_AZIENDA' AND ID_NUMERO_DOC LIKE '%$idNumeroDoc%'
                AND STATO= 'V' AND STATO_AVANZAMENTO='1' AND EXISTS( SELECT 1 FROM THIP.DOC_TRA_RIG R
                WHERE R.ID_AZIENDA=T.ID_AZIENDA AND R.ID_ANNO_DOC=T.ID_ANNO_DOC AND R.ID_NUMERO_DOC=T.ID_NUMERO_DOC AND R.STATO= 'V' AND R.STATO_AVANZAMENTO='1')";
        
        $data = $panthera->select_list($sql1 . $sql);
        $count = $panthera->select_single_value($sql0 . $sql);
      }
      
      return [$data, $count];
    }

    function checkStatusBatch(){
      global $panthera;
      $sql = "SELECT count(*) FROM THERA.BATCH_JOB WHERE STATUS = 'A' AND SCHEDULED_JOB_ID = 'CMDocTrasf'";
      $count = $panthera->select_single_value($sql);
      return $count;
    }

    function modificaTestataDocumentoMovimentazione($id, $testata) {
      global $panthera, $ID_AZIENDA,$DATA_ORIGIN;
      $idOriginale = $testata['ID_NUMERO_DOC'];
      $idAnnoDoc = $testata['ID_ANNO_DOC'];
      $sql = "UPDATE THIP.CM_DOC_TRA_TES SET STATO_AVANZAMENTO = '2', COL_MAGAZZINO = '2' WHERE ID_AZIENDA = '$ID_AZIENDA' and ID_NUMERO_DOC = '$idOriginale' and ID_ANNO_DOC = '$idAnnoDoc' and RUN_ID = '$id' ";
      //echo "CONCLUDO TESTATA ->".$sql;
      $panthera->execute_update($sql);
    }
}