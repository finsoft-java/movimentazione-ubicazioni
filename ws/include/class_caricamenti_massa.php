<?php

$caricamentiMassaManager = new CaricamentiMassaManager();

$YEAR = date('Y');
$DATE = date('Y-m-d');

class CaricamentiMassaManager {

    function getIdCaricamento() {
        global $panthera;

        // FIXME ROLLBACK necessario? 
        $panthera->conn->sqlsrv_begin_transaction();
        $panthera->executeUpdate("UPDATE THERA.NUMERATOR SET LAST_NUMBER=LAST_NUMBER+1 WHERE NUMERATOR_ID='MOVUBI'");
        $id = $panthera->select_single_value("SELECT LAST_NUMBER FROM THERA.NUMERATOR WHERE NUMERATOR_ID='MOVUBI'");
        if ($id == null) {
            // first run
            $id = 1;
            $panthera->executeUpdate("INSERT INTO THERA.NUMERATOR(NUMERATOR_ID,LAST_NUMBER) VALUES ('MOVUBI',$id)");  
        }
        $panthera->conn->sqlsrv_commit();
        return $id;
    }

    function trasferisciArticolo($codUbicazioneSrc, $codUbicazioneDest, $articolo, $qty) {
        global $panthera, $CAU_TESTATA, $YEAR, $DATE, $ID_AZIENDA, $UTENTE, $ubicazioniManager;

        if ($panthera->mock) {
            return;
        }

        $id = $this->getIdCaricamento();
        $magazzinoSrc = $ubicazioniManager->getMagazzinoUbicazione($codUbicazioneSrc);
        $magazzinoDest = $ubicazioniManager->getMagazzinoUbicazione($codUbicazioneDest);

        // CM_DOC_TRA_TES
        $this->creaTestataDocumento($id, $magazzinoSrc, $magazzinoDest, $commessa);

        // CM_DOC_TRA_RIG
        $this->creaRigheDocumento($id, $codMagazzinoSrc, $codUbicazioneSrc, $codMagazzinoDest, $codUbicazioneDest, $commessa, $articolo, $qty);

        // ora dovrei invocare il webservice che innesca il job CM
    }

    function creaTestataDocumento($id, $codMagazzinoSrc, $codMagazzinoDest, $commessa) {
        global $panthera, $CAU_RIGA, $YEAR, $DATE, $ID_AZIENDA, $UTENTE;

        $sql = "INSERT INTO THIP.CM_DOC_TRA_TES (
          DATA_ORIGIN,            -- 1
          RUN_ID,
          ROW_ID,
          RUN_ACTION,
          TRASF_STATUS,
          STATO_AVANZAMENTO,
          ID_AZIENDA,
          R_CAU_DOC_TRA,
          ID_ANNO_DOC,
          ID_ORIGINALE,           -- 10
          ID_NUMERO_DOC,
          DATA_DOC,
          NUMERO_DOC_FMT,
          R_MAGAZZINO,
          R_MAGAZZINO_ARR,
          R_COMMESSA,
          RIFER_DOC,
          DTA_RIFER_DOC,
          WF_CLASS_ID,
          WF_ID,                  -- 20
          WF_NODE_ID,
          WF_SUB_NODE_ID,
          NOTA,
          R_GES_COMMENTI,
          R_DOCUMENTO_MM,
          R_CENTRO_COSTO,
          R_CENTRO_RICAVO,
          COL_MAGAZZINO,
          LIS_CTL_STP_DOC,
          R_GRP_CNT_CA,           -- 30
          FLAG_RIS_UTE_1,
          FLAG_RIS_UTE_2,
          FLAG_RIS_UTE_3,
          FLAG_RIS_UTE_4,
          FLAG_RIS_UTE_5,
          STRINGA_RIS_UTE_1,
          STRINGA_RIS_UTE_2,
          NUM_RIS_UTE_1,
          NUM_RIS_UTE_2,
          STATO,                  -- 40
          R_UTENTE_CRZ,
          R_UTENTE_AGG,
          TIMESTAMP_CRZ,
          TIMESTAMP_AGG,
          R_COMMESSA_ARR,
          ANNO_DOC_RIF,
          NUMERO_DOC_RIF,
          TIPO_DOC_RIF,
          DA_LOGIS_LIGHT,
          DA_GEST_DOC_ORIG,       -- 50
          ID_ANNO_DOC_PPL,
          ID_NUMERO_DOC_PPL,
          ID_RIGA_DOC_PPL,
          ID_DET_RIGA_DOC_PPL,
          ID_RIGA_LOTTO_PPL,
          ID_LISTA_PRL,
          ID_RIGA_LISTA_PRL,
          R_CLIENTE,
          R_CLIENTE_ARR,
          R_FORNITORE,            -- 60
          R_FORNITORE_ARR)
        VALUES (
          '',                     -- 1
          '',
          '',
          '',
          '',
          '',
          '$ID_AZIENDA',
          '$CAU_TESTATA',
          '$YEAR',
          '$id',                       -- 10
          null,
          '$DATE',
          null,
          '$codMagazzinoSrc',
          '$codMagazzinoDest',
          '$commessa',
          null,
          null,
          null,
          null,                         -- 20
          null,
          null,
          'Trasferimento eseguito da tablet',
          null,
          null,
          null,
          null,
          null,
          null,
          null,                         -- 30
          null,
          null,
          null,
          null,
          null,
          null,
          null,
          null,
          null,
          'V',                         -- 40
          '$UTENTE',
          '$UTENTE',
          CURRENT_TIMESTAMP,
          CURRENT_TIMESTAMP,
          '$commessa',
          null,
          null,
          null,
          'N',
          null,                           -- 50
          null,
          null,
          null,
          null,
          null,
          null,
          null,
          null,
          null,
          null,                           -- 60
          null
        )
        ";
        $this->execute_update($sql);
    }

    function creaRigheDocumento($id, $codMagazzinoSrc, $codUbicazioneSrc, $codMagazzinoDest, $codUbicazioneDest, $commessa, $articolo, $qty) {
      global $panthera;

      $sql = "INSERT INTO THIP.CM_DOC_TRA_RIG (
        DATA_ORIGIN,              -- 1
        RUN_ID,
        ROW_ID,
        RUN_ACTION,
        TRASF_STATUS,
        STATO_AVANZAMENTO,
        ID_AZIENDA,
        ID_ANNO_DOC,
        ID_NUMERO_DOC,
        ID_ORIGINALE,                 -- 10
        ID_RIGA_DOC,
        ID_DET_RIGA_DOC,
        SPL_RIGA,
        SEQUENZA_RIGA,
        R_CAU_RIG_DOCTRA,
        DATA_REGISTRAZIONE,
        R_MAGAZZINO,
        R_MAGAZZINO_ARR,
        R_ARTICOLO,
        R_VERSIONE,                     -- 20
        R_CONFIGURAZIONE,
        R_OPERAZIONE,
        R_COMMESSA,
        DES_ARTICOLO,
        RIFER_DOC,
        DTA_RIFER_DOC,
        NOTA,
        R_GES_COMMENTI,
        R_DOCUMENTO_MM,
        R_UM_PRM,               -- 30
        QTA_UM_PRM,
        R_UM_SEC,
        QTA_UM_SEC,
        FTT_CONVER_UM,
        OPER_CONVER_UM,
        R_CENTRO_COSTO,
        R_CENTRO_RICAVO,
        R_GRP_CNT_CA,
        FLAG_RIS_UTE_1,
        FLAG_RIS_UTE_2,               -- 40
        FLAG_RIS_UTE_3,
        FLAG_RIS_UTE_4,
        FLAG_RIS_UTE_5,
        STRINGA_RIS_UTE_1,
        STRINGA_RIS_UTE_2,
        NUM_RIS_UTE_1,
        NUM_RIS_UTE_2,
        STATO,
        R_UTENTE_CRZ,
        R_UTENTE_AGG,                 -- 50
        TIMESTAMP_CRZ,
        TIMESTAMP_AGG,
        COSTO,
        R_COMMESSA_ARR,
        NUM_RIGA_DOC_RIF,
        DET_RIGA_DOC_RIF,
        R_UBI_PAR,
        R_UBI_ARR,
        R_CLIENTE,
        R_CLIENTE_ARR,                    -- 60
        R_FORNITORE,
        R_FORNITORE_ARR)
      VALUES (
        '',             -- 1
        '',
        '',
        '',
        '',
        '',
        '$ID_AZIENDA',
        '$YEAR',
        null,
        '$id',             -- 10
        ROW NUMBER OVER BLA BLA,
        1,
        1,
        ROW NUMBER * 10,
        '$CAU_RIGA',
        '$DATE',
        '$codMagazzinoSrc',
        '$codMagazzinoDest',
        '$articolo',
        0,              -- 20
        null,                         -- versione/cfg gestite ???
        null,
        '$commessa',
        null,
        null,
        null,
        null,
        null,
        null,
        '',              -- 30
        '',
        '',
        '',
        '',
        'M',
        null,
        null,
        null,
        null,
        null,              -- 40
        null,
        null,
        null,
        null,
        null,
        null,
        null,
        'V',
        '$UTENTE',
        '$UTENTE',              -- 50
        CURRENT_TIMESTAMP,
        CURRENT_TIMESTAMP,
        null,
        null,
        null,
        null,
        '$codUbicazioneSrc',
        '$codUbicazioneDest',
        null,
        null,              -- 60
        null,
        null
      )
      ";
      $this->execute_update($sql);
    }
}