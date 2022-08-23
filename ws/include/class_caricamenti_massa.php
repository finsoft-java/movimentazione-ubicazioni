<?php

$caricamentiMassaManager = new CaricamentiMassaManager();

$YEAR = date('Y');
$DATE = date('Ymd'); // Questo e' il formato richiesto da SQL Server

class CaricamentiMassaManager {

    /**
     * FUNZIONE "Cambia magazzino dell'ubicazione"
     */
    function trasferisciUbicazione($codUbicazione, $codMagazzinoDest) {
        global $panthera, $CAU_TESTATA, $CAU_RIGA, $YEAR, $DATE, $ID_AZIENDA, $UTENTE, $ubicazioniManager;

        if ($panthera->mock) {
            return;
        }

        $ubi1 = $ubicazioniManager->getUbicazione($codUbicazione);
        if ($ubi1 === null) print_error(400, "Ubicazione '$codUbicazione' inesistente");
        $codMagazzinoSrc = $ubi1['ID_MAGAZZINO'];
        $id = $panthera->get_numeratore('MOVUBI');
        //echo ">1< ";
  
        // BATCH_LOAD_HDR
        $this->creaTestataCaricamento($id);
        //echo ">2< ";

        // CM_DOC_TRA_TES
        $this->creaTestataDocumento($id, $CAU_TESTATA, $codMagazzinoSrc, $codMagazzinoDest);
        //echo ">3< ";
 
        // CM_DOC_TRA_RIG
        $this->creaRigheDocumento($id, $CAU_RIGA, $codMagazzinoSrc, $codUbicazione, $codMagazzinoDest, $codUbicazione);
        //echo ">4< ";

        // SCHEDULED_JOB
        $this->aggiorna_scheduled_job($id);
        //echo ">5< ";

        // lancia davvero il CM su Panthera
        $this->chiama_ws_panthera();

        usleep(500);
        if (!$this->checkCM($id)) {
          print_error(500, 'Il caricamento di massa non è andato a buon fine');
        }
    }

    /**
     * FUNZIONE "Trasferisci articolo da una ubicazione a un'altra"
     */
    function trasferisciArticolo($codUbicazioneSrc, $codUbicazioneDest, $articolo, $qty) {
        global $panthera, $ubicazioniManager, $CAU_TESTATA, $CAU_RIGA;

        if ($panthera->mock) {
            return;
        }

        $ubi1 = $ubicazioniManager->getUbicazione($codUbicazioneSrc);
        if ($ubi1 === null) print_error(400, "Ubicazione '$codUbicazioneSrc' inesistente");
        $codMagazzinoSrc = $ubi1['ID_MAGAZZINO'];

        $ubi2 = $ubicazioniManager->getUbicazione($codUbicazioneDest);
        if ($ubi2 === null) print_error(400, "Ubicazione '$codUbicazioneDest' inesistente");
        $codMagazzinoDest = $ubi2['ID_MAGAZZINO'];

        $ubicazioniManager->check_articolo($articolo);

        $id = $panthera->get_numeratore('MOVUBI');
        //echo ">1< ";

        // BATCH_LOAD_HDR
        $this->creaTestataCaricamento($id);
        //echo ">2< ";

        // CM_DOC_TRA_TES
        $this->creaTestataDocumento($id, $CAU_TESTATA, $codMagazzinoSrc, $codMagazzinoDest);
        //echo ">3< ";

        // CM_DOC_TRA_RIG
        $this->creaRigheDocumento($id, $CAU_RIGA, $codMagazzinoSrc, $codUbicazioneSrc, $codMagazzinoDest, $codUbicazioneDest, $articolo, $qty);
        //echo ">4< ";

        // SCHEDULED_JOB
        $this->aggiorna_scheduled_job($id);
        //echo ">5< ";

        // lancia davvero il CM su Panthera
        return $this->chiama_ws_panthera();

        usleep(500);
        if (!$this->checkCM($id)) {
          print_error(500, 'Il caricamento di massa non è andato a buon fine');
        }
    }

    /**
     * FUNZIONE "Svuota ubicazione"
     */
    function svuotaUbicazione($codUbicazione) {
        global $panthera, $ubicazioniManager, $CAU_TESTATA_SVUOTA, $CAU_RIGA_SVUOTA, $COD_MAGAZ_SVUOTA, $UBIC_SVUOTA;

        if ($panthera->mock) {
            return;
        }

        $ubi1 = $ubicazioniManager->getUbicazione($codUbicazioneSrc);
        if ($ubi1 === null) print_error(400, "Ubicazione '$codUbicazioneSrc' inesistente");
        $codMagazzinoSrc = $ubi1['ID_MAGAZZINO'];

        $id = $panthera->get_numeratore('MOVUBI');
        //echo ">1< ";

        // BATCH_LOAD_HDR
        $this->creaTestataCaricamento($id);
        //echo ">2< ";

        // CM_DOC_TRA_TES
        $this->creaTestataDocumento($id, $CAU_TESTATA_SVUOTA, $codMagazzinoSrc, $COD_MAGAZ_SVUOTA);
        //echo ">3< ";

        // CM_DOC_TRA_RIG
        $this->creaRigheDocumento($id, $CAU_RIGA_SVUOTA, $codMagazzinoSrc, $codUbicazioneSrc, $COD_MAGAZ_SVUOTA, $UBIC_SVUOTA);
        //echo ">4< ";

        // SCHEDULED_JOB
        $this->aggiorna_scheduled_job($id);
        //echo ">5< ";

        // lancia davvero il CM su Panthera
        return $this->chiama_ws_panthera();

        usleep(500);
        if (!$this->checkCM($id)) {
          print_error(500, 'Il caricamento di massa non è andato a buon fine');
        }
    }

    function creaTestataCaricamento($id) {
      global $panthera, $DATA_ORIGIN;

      $sql = "INSERT INTO THERA.BATCH_LOAD_HDR (
                DATA_ORIGIN,
                RUN_ID,
                ENTITY_ID,
                TASK_ID,
                DESCRIPTION,
                CREATION_TS,
                SIMULATION_TOO
              ) VALUES (
                '$DATA_ORIGIN',
                '$id',
                'CMDocMagTra',
                'RUN',
                'Movimentazione ubicazioni',
                CURRENT_TIMESTAMP,
                'N'
              )
              ";
      
      $panthera->execute_update($sql);
    }

    function creaTestataDocumento($id, $cauTestata, $codMagazzinoSrc, $codMagazzinoDest) {
        global $panthera, $DATA_ORIGIN, $YEAR, $DATE, $ID_AZIENDA, $UTENTE;

        // FIXME RUN_ID
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
          '$DATA_ORIGIN',                     -- 1
          '$id',
          1,
          'I',
          '0',
          '2',
          '$ID_AZIENDA',
          '$cauTestata',
          '$YEAR',
          '$id',                       -- 10
          null,
          '$DATE',
          null,
          '$codMagazzinoSrc',
          '$codMagazzinoDest',
          null,
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
          '2',
          'N',
          null,                         -- 30
          '-',
          '-',
          '-',
          '-',
          '-',
          null,
          null,
          null,
          null,
          'V',                         -- 40
          '$UTENTE',
          '$UTENTE',
          CURRENT_TIMESTAMP,
          CURRENT_TIMESTAMP,
          null,
          null,
          null,
          'T',
          'N',
          'N',                           -- 50
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

        // echo $sql; die();

        $panthera->execute_update($sql);
    }

    function creaRigheDocumento($id, $cauRiga, $codMagazzinoSrc, $codUbicazioneSrc, $codMagazzinoDest, $codUbicazioneDest, $articolo=null, $qty=null) {
      global $panthera, $DATA_ORIGIN, $YEAR, $DATE, $ID_AZIENDA, $UTENTE;

      if (empty($articolo) || empty($qty)) {
        $qty = 'S.QTA_GIAC_PRM';
        $qtySec = 'S.QTA_GIAC_SEC';
      } else {
        $qtySec = "$qty * A.FTT_CONVER_UM";
        // FIXME potrebbe essere un * o un / a seconda dell'operatore...
      }

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
      SELECT
        '$DATA_ORIGIN',                     -- 1
        '$id',
        ROW_NUMBER() OVER(ORDER BY S.ID_ARTICOLO),
        'I',
        '0',
        '2',
        '$ID_AZIENDA',
        '$YEAR',
        '$id',
        ROW_NUMBER() OVER(ORDER BY S.ID_ARTICOLO),             -- 10 IN QUESTO CASO ID_ORIGINALE E' LA RIGA !
        ROW_NUMBER() OVER(ORDER BY S.ID_ARTICOLO),
        1,
        1,
        (ROW_NUMBER() OVER(ORDER BY S.ID_ARTICOLO)) * 10,
        '$cauRiga',
        '$DATE',
        '$codMagazzinoSrc',
        '$codMagazzinoDest',
        S.ID_ARTICOLO,
        S.ID_VERSIONE,              -- 20
        S.ID_CONFIG,
        null,
        S.ID_COMMESSA,
        null,
        null,
        null,
        null,
        null,
        null,
        A.R_UM_PRM_MAG,              -- 30
        $qty,
        A.R_UM_SEC_MAG,
        $qtySec,
        A.FTT_CONVER_UM,
        A.OPER_CONVER_UM,
        null,
        null,
        null,
        '-',
        '-',              -- 40
        '-',
        '-',
        '-',
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
        S.ID_COMMESSA,
        null,
        null,
        '$codUbicazioneSrc',
        '$codUbicazioneDest',
        null,
        null,              -- 60
        null,
        null
      FROM THIP.SALDI_UBICAZIONE S
      JOIN THIP.ARTICOLI A ON A.ID_AZIENDA=S.ID_AZIENDA AND A.ID_ARTICOLO=S.ID_ARTICOLO
      WHERE S.ID_AZIENDA='$ID_AZIENDA' AND S.ID_UBICAZIONE='$codUbicazioneSrc' AND S.QTA_GIAC_PRM > 0
      ";

      if (!empty($articolo)) {
        $sql .= " AND S.ID_ARTICOLO='$articolo' ";
      }

      // echo $sql; die();

      $panthera->execute_update($sql);

    }

    function aggiorna_scheduled_job($id) {
      global $panthera, $DATA_ORIGIN, $ID_AZIENDA, $COD_SCHEDULED_JOB;
      $parametri = [
        "PageTo=0",
        "PageFrom=0",
        "ImmediateExecution=N",
        "PrintPreviewEnabled=N",
        "ExportType=P",
        "ReportId=CMDocMagTraVal",
        "OnlyGroupLeader=N",
        "PrinterId=000",
        "CopyNumber=",
        "ExecutePrint=N",
        "SSDEnabled=N",
        "RunParameter.EntityIdService=CMDocMagTra",
        "RunParameter.DataOrigin=$DATA_ORIGIN",
        "RunParameter.OnlySimulator=N",
        "RunParameter.PrintValidData=N",
        "RunParameter.NumRecords=0",
        "RunParameter.RunId=$id",
        "RunParameter.PrintError=N",
        "NumeratorHandler.IdSottoserie=",
        "NumeratorHandler.IdNumeratore=",
        "NumeratorHandler.IdSerie=",
        "NumeratorHandler.IdAzienda=$ID_AZIENDA",
      ];
      $separatore = "',CHAR(18),'";
      $par_joined = "CONCAT('" . join($separatore, $parametri) . "',CHAR(18))";
      $sql = "UPDATE THERA.SCHEDULED_JOB SET JOB_PARAMETERS=$par_joined WHERE SCHEDULED_JOB_ID='$COD_SCHEDULED_JOB'";

      // echo $sql; die();

      $panthera->execute_update($sql);
    }

    function chiama_ws_panthera() {
      global $URL_CM;

      $curl = curl_init();
      curl_setopt($curl, CURLOPT_URL, $URL_CM);
      curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
      $result = curl_exec($curl);
      curl_close($curl);

      // il $result è assolutamente inutile, se non magari per il JobId
      return $result;

    }

    function checkCM($id) {
      global $panthera, $DATA_ORIGIN;
      $sql = "SELECT TOTAL_RECS,TRANSFERRED_RECS,WRONG_RECS
              FROM THERA.BATCH_LOAD_HDR
              WHERE DATA_ORIGIN='$DATA_ORIGIN' AND RUN_ID='$id'";
      $l = $panthera->select_single($sql);
      if ($l["TOTAL_RECS"] > 0 && $l["TRANSFERRED_RECS"] > 0 && $l["WRONG_RECS"] == 0) {
        return true;
      } else {
        return false;
      }
    }
}