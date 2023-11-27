let timerOn = true;
var maxItems = 1000;
var documenti = [];
var magazziniDisponibili = null;
var documentoSelezionato = null;
var rigaSelezionata = null;
var magazzinoCorrente = null;
var commessaCorrente = null;
var ubicazioneCorrente = null;
var magazzinoDest = null;
var commessaDest = null;
var ubicazioneDest = null;
var articoloMovimentazione = null;
var statoCorrente = 0;
var current_page = 0;
var loaded = 0;
var records_per_page = 10;
var qntSelezionata;
var noteRichiesta = "";
var riga =null;
let loadedPage = 0;
var testata =null;
var prelievi = [];
let datiUbicazionePartenza;
let annoDoc=null;
let numeroDoc=null;
/**
 * Inizializza l'interfaccia per un certo stato i
 */
function initStato(i) {
    console.log("inizio statoCorrente",statoCorrente);

    statoCorrente = i;
    if (statoCorrente == 0) {
        // griglia dei documenti
        rigaSelezionata = null;
        documentoSelezionato = null;
        ubicazioneCorrente = null;
        $('.btn_setting').attr("data-open","false").removeClass("activebtn redColor");
        $('.err_box').remove();
        $("#boxqnt").html("");
        $("#pagNumLabel").show();
        $("#divElencoDocumentiContainer").show();
        $("#divElencoRigheDocumentiContainer").hide();
        $("#divSingolaRigaContainer").hide();
        $('#btnConfirm').hide();
        $('#btnConfirmPart').hide();        
        $('#btnPreleva').hide();
        $('#btnBack').hide();
        $("#navigateRichieste").show();
        $(".qrcode").hide();
        $(".settings").hide();
        $(".filtroRicerca").hide();
        $("#search").show();
        annoDoc = null;
        numeroDoc = null;
    } else if (statoCorrente == 1) {
        // griglia delle righe del documento
        // deve anche mostrare l'ubicazione 
        $('.btn_setting').attr("data-open","false").removeClass("activebtn redColor");
        $('.err_box').remove();
        $(".filtroRicerca").show();
        $("#pagNumLabel").hide();
        $("#navigateRichieste").hide();
        rigaSelezionata = null;
        $("#boxqnt").html("");
        $("#divElencoDocumentiContainer").hide();
        $("#search").hide();
        $("#divElencoRigheDocumentiContainer").show();
        $("#divSingolaRigaContainer").hide();
        $('#btnConfirm').hide();
        $('#btnConfirmPart').hide();
        $('#btnPreleva').hide();
        $(".qrcode").hide();
        $(".settings").hide();
        $('#btnBack').show();
    } else if (statoCorrente == 2) {
        // maschera di scelta dell'ubicazione
        $('.err_box').remove();
        $("#pagNumLabel").hide();
        $(".filtroRicerca").hide();
        $("#ubi_arrivo").val("");
        $("#navigateRichieste").hide();
        $("#divElencoDocumentiContainer").hide();
        $("#divElencoRigheDocumentiContainer").hide();
        $("#divSingolaRigaContainer").show();
        $(".qrcode").hide();
        $(".settings").hide();
        $('#btnPreleva').show().prop('disabled', true);
        $('#btnConfirm').hide();
        if($("#selectCommessa").val() != '0'){
            $('#btnConfirmPart').show().prop('disabled', true);
        }
        $('#btnBack').show();
    }
    console.log("fine statoCorrente",statoCorrente);
}

function indietro() {
    console.log(statoCorrente);
    if (statoCorrente == 2) {
        riga = documenti[documentoSelezionato].RIGHE[rigaSelezionata];
        if (riga.PRELIEVI.length == 0 || confirm('Stai per annullare tutti i prelievi in corso, sei sicuro?')) {
            riga.PRELIEVI = [];
            riga.QTA_RESIDUA = parseFloat(riga.QTA_UM_PRM);
            initStato(1);
            ridisegnaElencoRigheDocumenti();
        } else {
            initStato(0);
        }
    } else if (statoCorrente == 1) {
        initStato(0);
    } else {
        console.error("indietro() chiamata in uno stato strano: " + statoCorrente);
    }
    $(".prelievoAccordion").remove();
}

/**
 * Infinity scroll
 */
function loadMore(loadedPage, operazione) {
    initStato(0);
    loaded = loaded != 0 ? loadedPage*10 : 0;
    if (loaded >= maxItems) return; // no more items
    $.get({
        url: "./ws/RichiesteMovimentazione.php?skip=" + loaded + "&top=10",
        dataType: 'json',
        headers: {
            'Authorization': 'Bearer ' + sessionStorage.getItem('token')
        },
        success: function(data, status) {
            $("#divElencoDocumenti").html("");
            let startLoad = 1;
            let endLoad = 10;
            if(loaded != 0){
                startLoad = loaded+startLoad;
                endLoad = loaded+endLoad+1;
            }
            $("#pagNumLabel").html(startLoad+"-"+endLoad+" di "+data.count+" Documenti");
            maxItems = data.count;
            let elementiCaricati = data.data;
            documenti = elementiCaricati;
            if(operazione == "prev"){
                loaded = loaded != 0 ? (loadedPage-1)*10 : 0;
            } 
            let i = 0;
            elementiCaricati.forEach(x => {                
                $("#divElencoDocumenti").append(getHtmlGrigliaDocumenti(i++, x));                
            });
        },
        error: function(data, status) {
            console.log('ERRORE nel caricare la griglia', data);
            showError(data);
        }
    });    
}

function showError(data) {
    const err = typeof data === 'string' ? data :
                data.responseJSON && data.responseJSON.error && data.responseJSON.error.value.length > 0 ? data.responseJSON.error.value :
                "Errore interno";
    alert(err);
}

function getHtmlGrigliaDocumenti(idDoc, x) {
    // qui x dovrebbe essere uguale a documenti[id]
    loaded++;
    var myDate = x.DATA_DOC.split(' ')[0];
    return `<div class="rigaDocumenti" onclick="openDoc('${idDoc}','${x.ID_ANNO_DOC}','${x.ID_NUMERO_DOC}')">
                <p style="padding: 30px 15px; margin: 0px; text-align: center;">
                    Documento: <strong>${x.NUMERO_DOC_FMT}</strong> del ${myDate} <br/>
                    Numero Righe: <strong>${x.CNT}</strong>
                </p><hr style="margin:0px;"/>
            </div>`;
}

function openDoc(idDoc, idAnnoDoc, idNumeroDoc) {
    documentoSelezionato = idDoc;
    let item = documenti[documentoSelezionato];
    initStato(1);
    $("#divSingolaUbicazPredef").html("");
    $.get({
        url: "./ws/RichiesteMovimentazione.php?idAnnoDoc=" + idAnnoDoc + "&idNumeroDoc=" + idNumeroDoc,
        dataType: 'json',
        headers: {
            'Authorization': 'Bearer ' + sessionStorage.getItem('token')
        },
        success: function(data, status) {
            item.RIGHE = data.data;
            item.RIGHE.forEach(riga => {
                // aggiungo i campi che non esistono server-side
                riga.PRELIEVI = [];
                riga.QTA_RESIDUA = parseFloat(riga.QTA_UM_PRM);
                controllaStatoRiga(riga);
            });
        },
        error: function(data, status) {
            console.log('ERRORE nel caricare la documenti delle righe', data);
            showError(data);
        }
    });    
}

function controllaStatoRiga(riga){
    let qntVolutaDoc = riga.QTA_UM_PRM;
    let articolo = riga.R_ARTICOLO;
    let commessaPartenza = riga.R_COMMESSA;    
    let magazzinoPartenza = riga.R_MAGAZZINO;
    $.get({
        url: "./ws/GetStatoRiga.php?qntVolutaDoc=" + qntVolutaDoc.trim() +"&articolo="+articolo.trim()+"&commessaPartenza="+commessaPartenza.trim()+"&magazzinoPartenza="+magazzinoPartenza.trim(),
        dataType: 'json',
        headers: {
            'Authorization': 'Bearer ' + sessionStorage.getItem('token')
        },
        success: function(data, status) {
            riga.STATO_RIGA = data.data;
            annoDoc = riga.ID_ANNO_DOC;
            numeroDoc = riga.ID_NUMERO_DOC;
            ridisegnaElencoRigheDocumenti();
        },
        error: function(data, status) {
            
        }
    });   

}

function ridisegnaElencoRigheDocumenti() {
    let doc = documenti[documentoSelezionato];
    $("#divElencoRigheDocumenti").html('');
    $("#divElencoRigheDocumenti").html(`<p style="text-align: center; margin-top: 30px;">Doc. <strong>${doc.NUMERO_DOC_FMT}</strong></p>`);
    let numRiga = 0;
    let tuttoPrelevato = true;
    doc.RIGHE.forEach(riga => {
        $("#divElencoRigheDocumenti").append(getHtmlGrigliaRighe(documentoSelezionato, numRiga++, riga));
        if (riga.QTA_RESIDUA > 0) tuttoPrelevato = false;
    });
    $("#btnConfirm button").attr("disabled", !tuttoPrelevato);
    $("#btnConfirmPart button").attr("disabled", !tuttoPrelevato);
    
    riga = doc;
    console.log("riga modificata", riga);
}

function getHtmlGrigliaRighe(idDoc, idRiga, riga) {
    // QUI x dovrebbe essere documenti[id].RIGHE[id2]
    
    console.log("rigaAA ",riga);
    console.log("riga statO ",riga.STATO_RIGA);
    let commessa = riga.R_COMMESSA || '-';
    let styleRiga = "";
    switch (riga.STATO_RIGA) {
        case "Evadibile":
            styleRiga = 'background-color: #00800096';
            break;
        case "Parziale":
            styleRiga = 'background-color: #ffff006b';
            break;
        default:
            styleRiga = 'background-color: #ff00006e';
            break;
    }
    return `<div class="rigaDocumenti" onclick="openRow(${idDoc},${idRiga})" style="text-align:center;border-top: 1px solid #000;padding: 20px 15px;${styleRiga}">
               <p style="margin:0px;">
                Articolo: <strong> ${riga.R_ARTICOLO}</strong> <br/> 
                Commessa :<strong>${commessa} </strong> <br/>
                Qta. richiesta <strong>${riga.QTA_UM_PRM} ${riga.R_UM_PRM}</strong><br/>
                <strong>${riga.STATO_RIGA}</strong>
               </p>
            </div>`;
}

function openRow(idDoc, idRiga) {
    documentoSelezionato = idDoc;
    rigaSelezionata = idRiga;
    initStato(2);
    let x = documenti[documentoSelezionato].RIGHE[rigaSelezionata];
    console.log("DATI RIGA",x);
    magazzinoCorrente = x.R_MAGAZZINO;
    magazzinoDest = x.R_MAGAZZINO_ARR;
    commessaCorrente = x.R_COMMESSA;
    commessaDest = x.R_COMMESSA_ARR;
    ubicazioneCorrente = x.R_UBI_PAR;
    ubicazioneDest = x.R_UBI_ARR;
    qntTotaleRichiesta = x.QTA_UM_PRM;
    qntTotaleRichiestaHtml = qntTotaleRichiesta.replace(/\.00$/,'');
    articoloMovimentazione = x.R_ARTICOLO;
    risUte1 = x.STRINGA_RIS_UTE_1;
    risUte2 = x.STRINGA_RIS_UTE_2;
    noteRichiesta = x.NOTA == null ? "" : x.NOTA;
    unitaMisura = x.R_UM_PRM;
    qntResidua = x.QTA_RESIDUA;
    $.get({
        url: "./ws/GetUbicazione.php?codUbicazione=" + ubicazioneCorrente,
        dataType: 'json',
        headers: {
            'Authorization': 'Bearer ' + sessionStorage.getItem('token')
        },
        success: function(data, status) {
            datiUbicazionePartenza = data.value;
            console.log("aa",datiUbicazionePartenza);
            let notePartenza="";
            if(datiUbicazionePartenza.NOTE != "" && datiUbicazionePartenza.NOTE != null){
                notePartenza = datiUbicazionePartenza.NOTE;
            }
            
            let notePosizionePartenza="";
            if(datiUbicazionePartenza.NOTE_POSIZIONE != "" && datiUbicazionePartenza.NOTE_POSIZIONE != null){
                notePosizionePartenza = datiUbicazionePartenza.NOTE_POSIZIONE;
            }

            $("#boxqnt").html(`<p class='magArrLabel'>Magazzino Arrivo: <strong>${magazzinoDest}</strong> </br></p>
                       <p>Quantità richiesta Documento : <strong>${qntTotaleRichiestaHtml} ${unitaMisura}</strong></p>
                       <p>Quantità residua da Prelevare:  <strong>${qntResidua} ${unitaMisura}</strong></p>
                       <p>Descrizione: <strong>${notePartenza}</strong></p>
                       <p>Note Posizione: <strong>${notePosizionePartenza}</strong></p>`);
            if(blackListUbicazioni.includes(ubicazioneDest.trim())) {        
                $("#boxqnt").prepend("<p class='err_box' style='color: red;text-decoration: underline;font-weight: bold;'>UBICAZIONE '"+ubicazioneDest.trim()+"' NON SELEZIONABILE<br></p>");
            }                                          
            $("#qrcode").val(ubicazioneDest.trim());
            $(".magArrLabel").remove();
            let rigaPrelieviEffettuati="";
            if (x.PRELIEVI.length > 0) {
                rigaPrelieviEffettuati = `<div class="prelievoAccordion"><button class="accordion">Prelievi</button>
                                        <div class="panel">`;
                for (let i = 0; i < x.PRELIEVI.length; i++) {
                    let item = x.PRELIEVI[i];
                    let qntHtmlFormat = item.QUANTITA.replace(/\.000$/,'');
                    console.log("qntHtmlFormat",qntHtmlFormat);
                    rigaPrelieviEffettuati += `<p style="text-align:center;border-bottom: 1px solid;padding-bottom: 15px;">
                                                    Ubicazione Partenza: <strong>${item.UBICAZIONE}</strong><br/>
                                                    Commessa Partenza: <strong>${item.COMMESSA}</strong> <br/>                                                    
                                                    Quantità richiesta Documento : <strong>${qntHtmlFormat}</strong>
                                                </p>`;
                }
                rigaPrelieviEffettuati += `</div></div>`;
            }
            
            
            $("#divSingolaRiga").html(`<button class="accordion">Dati Riga</button>
                                    <div class="panel">
                                            <p style="text-align:center;margin-bottom:0px;">
                                                Articolo: <strong>  ${x.R_ARTICOLO} </strong> </br>
                                                Ubicazione Partenza: <strong>${ubicazioneCorrente}</strong><br/>
                                                Commessa Partenza: <strong>${commessaCorrente}</strong> <br/>
                                                Sottocommessa Partenza: <strong>${risUte1}</strong></br>                                            
                                                Ubicazione Arrivo: <strong>${ubicazioneDest}</strong> </br>
                                                Commessa Arrivo: <strong>${commessaDest}</strong> </br>                                                    
                                                Sottocommessa Arrivo: <strong>${risUte2}</strong></br>
                                            </p>
                                        </div>`);
            
            $(".prelievoAccordion").remove();
            $(".btnDiv").prepend(rigaPrelieviEffettuati);
            $("#ubi_arrivo").val(ubicazioneDest);
            $.get({
                url: "./ws/GetMagazziniAlternativi.php",
                dataType: 'json',
                headers: {
                    'Authorization': 'Bearer ' + sessionStorage.getItem('token')
                },
                success: function(data, status) { 
                    magazziniDisponibili = data["data"];
                    
                    $.get({
                        url: `./ws/Interrogazione.php?mov=Y&codArticolo=${x.R_ARTICOLO}`,
                        dataType: 'json',
                        headers: {
                            'Authorization': 'Bearer ' + sessionStorage.getItem('token')
                        },
                        success: function(data, status) { 
                            let dati = data["data"];
                            datiUbicazione = dati.filter((value, index, self) =>
                                index === self.findIndex((t) => (
                                    t.ID_UBICAZIONE === value.ID_UBICAZIONE
                                ))
                            );
                            console.log("datiUbicazione ",datiUbicazione);
                            datiMagazzino = dati.filter((value, index, self) =>
                                index === self.findIndex((t) => (
                                    t.ID_MAGAZZINO === value.ID_MAGAZZINO
                                ))
                            );
                            let datiStampati = "";
                            arrUbicazioniDest = dati;
                            if(dati[0] == null || dati.length === 0) {
                                showError("Nessuna ubicazione disponibile per l'Articolo "+articoloMovimentazione);
                                $("#qrcode").val('');
                                return false;
                            }

                            datiStampati += "<div id='magazPrelievo' style='display:none'><label>Magazzino Prelievo</label>";
                            datiStampati += "<select onchange='onChangeMagazzino();' onclick='timerOn = false' id='magazzinoOrigine' onfocusout='timerOn = true' class='form-control'>";    
                            datiStampati += "<option value='-1'> Seleziona magazzino disponibile </option>";                            
                            for (let i = 0; i < datiMagazzino.length; i++) {
                                let selected = "";
                                if(magazzinoCorrente == datiMagazzino[i].ID_MAGAZZINO){
                                    selected = "selected='selected'";
                                }
                                datiStampati += "<option "+selected+" value='"+datiMagazzino[i].ID_MAGAZZINO+"'>" + datiMagazzino[i].ID_MAGAZZINO + "</option>";                    
                            } 
                            datiStampati+= "</select></div>";

                            let magazzinoDestinazioneHtml = "";
                            magazzinoDestinazioneHtml += "<div id='magazArrivo' style='display:none'><label>Magazzino di Arrivo</label>";
                            magazzinoDestinazioneHtml += "<select onchange='onChangeMagazzinoDest();' onclick='timerOn = false' id='magazzinoDest' onfocusout='timerOn = true' class='form-control'>";    
                            magazzinoDestinazioneHtml += "<option value='-1'> Seleziona magazzino disponibile </option>";                            
                            for (let i = 0; i < magazziniDisponibili.length; i++) {
                                let selected = "";
                                if(magazzinoDest == magazziniDisponibili[i]){
                                    selected = "selected='selected'";

                                }
                                magazzinoDestinazioneHtml += "<option "+selected+" value='"+magazziniDisponibili[i]+"'>" + magazziniDisponibili[i] + "</option>";                    
                            } 
                            magazzinoDestinazioneHtml+= "</select></div>";
                            datiStampati += "<p style='text-align:center;margin-top:15px;'>Magazzino di Prelievo Documento: <strong>"+magazzinoCorrente+"</strong> <br/></p>";
                            datiStampati += "<div class='ubiPrelievoDiv'><label>Ubicazione di Prelievo</label>";
                            datiStampati += "<select id='ubicazioneOrigine' onclick='timerOn = false' onfocusout='timerOn = true' class='form-control'>";    
                            datiStampati += "<option value='-1'> Seleziona ubicazione disponibile </option>";                            
                            console.log("datiUbicazione dentro ",datiUbicazione);
                            let optUbicazioni = "";
                            for (let i = 0; i < datiUbicazione.length; i++) {
                                let selected = "";
                                let comm = datiUbicazione[i].ID_COMMESSA ? datiUbicazione[i].ID_COMMESSA.trim() : "";
                                let disabled = "";
                                if((ubicazioneCorrente.trim() === datiUbicazione[i].ID_UBICAZIONE.trim() && commessaCorrente.trim() === comm) || (ubicazioneCorrente.trim() !== datiUbicazione[i].ID_UBICAZIONE.trim() && commessaCorrente.trim() === comm )){
                                    selected = "selected='selected'";
                                }
                                if(blackListUbicazioni.includes(datiUbicazione[i].ID_UBICAZIONE.trim())){
                                    disabled = "disabled";
                                }
                                if(magazzinoCorrente == datiUbicazione[i].ID_MAGAZZINO){
                                    optUbicazioni = optUbicazioni+"<option "+selected+" "+disabled+" value='"+datiUbicazione[i].ID_UBICAZIONE+"' data-qta='"+datiUbicazione[i].QTA_GIAC_PRM+"'>" + datiUbicazione[i].ID_UBICAZIONE + " </option>";                    
                                }
                            } 
                            datiStampati += optUbicazioni+"</select>";
                            datiStampati += magazzinoDestinazioneHtml;                    
                            datiStampati += "<label>Note</label>";
                            datiStampati += "<textarea class='form-control' id='note' rows='3' onclick='timerOn = false' onfocusout='timerOn = true' >"+noteRichiesta+"</textarea></div>";
                            $("#divSingolaRiga").append(datiStampati);                  
                            $("#ubicazioneOrigine").trigger("change");   
                            $("#magazzinoOrigine").trigger("change");    
                            $("#magazzinoDest").trigger("change");  
                        }, error: function(data, status) {
                            console.log('ERRORE -> Interrogazione', data);
                            showError(data);
                        }
                    });

                }, error: function(data, status) {
                    console.log('ERRORE -> getMagazziniAlternativi', data);
                    showError(data);
                }
            });


        },
        error: function(data, status){
            $("#qrcode").attr('placeholder','UBICAZIONE ORIG.');
            console.log('ERRORE -> Interrogazione', data);
            showError(data);
            $("#qrcode").val('');
            ubicazione = null;
        }
    });   


    
            
}

function setGiacenzaCommessaSelezionata() {
    qntSelezionata = $("#selectCommessa option:selected").val();
    unitaSelezionata = $("#selectCommessa option:selected").attr("data-prm");
    let item = documenti[documentoSelezionato].RIGHE[rigaSelezionata];

    if(item.QTA_RESIDUA != null && item.QTA_RESIDUA != "") qntTotaleRichiesta = item.QTA_RESIDUA;

    if(parseFloat(qntSelezionata) > 0) {
        $(".dispCommessa").remove();
        $("#boxqnt").append("<p class='dispCommessa'>Quantità disp. Ubicazione/Commessa: <strong>"+qntSelezionata+" "+unitaSelezionata+"</strong></p>");
    }

    if(parseFloat(qntSelezionata) >= parseFloat(qntTotaleRichiesta)){
        qntSelezionata = qntTotaleRichiesta;
    }
    $("#qty").val(qntSelezionata);   
    checkQty();
}

function checkQty() {
    let qty = $("#qty").val();
    
    if($("#ubi_arrivo").val() != null && $("#ubi_arrivo").val() != ""){
        if(parseFloat(qty) === parseFloat(qntTotaleRichiesta) ){
            $("#btnConfirm").show();
            $("#btnConfirm button").attr('disabled',false);
            $("#btnConfirmPart button").attr('disabled',true);            
            $("#btnPreleva button").attr('disabled',true);
        } else {
            $("#btnPreleva button").attr('disabled', qty > 0 && qty <= qntTotaleRichiesta ? false : true);
            $("#btnConfirm button").attr('disabled',true);
            
            if($("#selectCommessa").val() != '0'){
                $("#btnConfirmPart button").attr('disabled',false);            
            }
        }        
    } else {
        $("#btnPreleva button").attr('disabled', true);
        $("#btnConfirm button").attr('disabled', true);
        $("#btnConfirmPart button").attr('disabled', true);        
    }
    if($("#magazzinoOrigine").val() == -1 || $("#ubicazioneOrigine").val() == -1 || $("#magazzinoDest").val() == -1){
        $("#btnPreleva button").attr('disabled', true);
        $("#btnConfirm button").attr('disabled', true);
    }
    if(Object.keys(prelievi).length > 0 && $("#selectCommessa option").length == 1 && $("#selectCommessa").val() != '0'){
        $("#btnConfirm").show();
        $("#btnConfirmPart").show();
        $("#btnConfirm button").attr('disabled',false);
        $("#btnConfirmPart button").attr('disabled',false);        
        $("#btnPreleva button").attr('disabled',true);
    }    
    if(blackListUbicazioni.includes($("#ubi_arrivo").val().trim())) {        
        $("#btnPreleva button").attr('disabled', true);
        $("#btnConfirm button").attr('disabled', true);
        $("#btnConfirmPart button").attr('disabled', true);   
    }
}

function conferma() {

    $("#btnConfirm button").attr("disabled",true);
    let item = documenti[documentoSelezionato].RIGHE[rigaSelezionata];

    let commessa = $("#selectCommessa option:selected").text();
    if (commessa == '-') commessa = null;
    let qty = parseFloat($("#qty").val());
    if($("#selectCommessa").val() != 0) {
        item.PRELIEVI.push({
            MAGAZZINO: $("#magazzinoOrigine").val(),
            UBICAZIONE: $("#ubicazioneOrigine").val(),
            COMMESSA: commessa,
            QUANTITA: $("#qty").val(),
            MAGAZZINO_ARRIVO: $("#magazzinoDest").val(),
            UBICAZIONE_ARRIVO: $("#ubi_arrivo").val(),
            COMMESSA_ARRIVO: item.R_COMMESSA_ARR,
            ID_ANNO_DOC: item.ID_ANNO_DOC,
            ID_RIGA_DOC: item.ID_RIGA_DOC,
            ID_NUMERO_DOC: item.ID_NUMERO_DOC,
            NOTA: $("#note").val()
        });
    }
    item.PRELIEVI = item.PRELIEVI.filter((value, index, self) =>
                                            index === self.findIndex((t) => (
                                                t.MAGAZZINO === value.MAGAZZINO && t.UBICAZIONE === value.UBICAZIONE &&
                                                t.COMMESSA === value.COMMESSA && t.QUANTITA === value.QUANTITA &&
                                                t.MAGAZZINO_ARRIVO === value.MAGAZZINO_ARRIVO && t.UBICAZIONE_ARRIVO === value.UBICAZIONE_ARRIVO && 
                                                t.COMMESSA_ARRIVO === value.COMMESSA_ARRIVO && t.ID_ANNO_DOC === value.ID_ANNO_DOC &&
                                                t.ID_RIGA_DOC === value.ID_RIGA_DOC && t.ID_NUMERO_DOC === value.ID_NUMERO_DOC &&
                                                t.NOTA === value.NOTA)
                                        ));
    item.QTA_RESIDUA -= qty;
    console.log(item.PRELIEVI);
    let isCompleta = false;
    if(item.QTA_RESIDUA == 0){
        isCompleta = true;
    }
    $.post({
        url: "./ws/RichiesteMovimentazione.php",
        dataType: 'json',
        data: {
            riga: item,
            testata : documenti[documentoSelezionato],
            isCompleta : isCompleta
        },
        headers: {
            'Authorization': 'Bearer ' + sessionStorage.getItem('token')
        },
        success: function(data, status) {
            showSuccessMsg("Riga confermata con successo");
            console.log("data->",data);
            checkStatoTrasferimento(data["id"], isCompleta);
            $("#btnConfirm button").attr("disabled",false);
        },
        error: function(data, status){
            console.log('ERRORE -> cambioMagazzinoUbicazione', data);
            showError(data)
            $("#qrcode").val('');
        }
    });
}

function confermaParziale() {
    $("#btnConfirmPart button").attr("disabled",true);
    $("#btnPreleva button").attr("disabled",true);
    
    let item = documenti[documentoSelezionato].RIGHE[rigaSelezionata];

    let commessa = $("#selectCommessa option:selected").text();
    if (commessa == '-') commessa = null;
    let qty = parseFloat($("#qty").val());
    if($("#selectCommessa").val() != 0) {
        item.PRELIEVI.push({
            MAGAZZINO: $("#magazzinoOrigine").val(),
            UBICAZIONE: $("#ubicazioneOrigine").val(),
            COMMESSA: commessa,
            QUANTITA: $("#qty").val(),
            MAGAZZINO_ARRIVO: $("#magazzinoDest").val(),
            UBICAZIONE_ARRIVO: $("#ubi_arrivo").val(),
            COMMESSA_ARRIVO: item.R_COMMESSA_ARR,
            ID_ANNO_DOC: item.ID_ANNO_DOC,
            ID_RIGA_DOC: item.ID_RIGA_DOC,
            ID_NUMERO_DOC: item.ID_NUMERO_DOC,
            NOTA: $("#note").val()
        });
    }
    item.PRELIEVI = item.PRELIEVI.filter((value, index, self) =>
                                            index === self.findIndex((t) => (
                                                t.MAGAZZINO === value.MAGAZZINO && t.UBICAZIONE === value.UBICAZIONE &&
                                                t.COMMESSA === value.COMMESSA && t.QUANTITA === value.QUANTITA &&
                                                t.MAGAZZINO_ARRIVO === value.MAGAZZINO_ARRIVO && t.UBICAZIONE_ARRIVO === value.UBICAZIONE_ARRIVO && 
                                                t.COMMESSA_ARRIVO === value.COMMESSA_ARRIVO && t.ID_ANNO_DOC === value.ID_ANNO_DOC &&
                                                t.ID_RIGA_DOC === value.ID_RIGA_DOC && t.ID_NUMERO_DOC === value.ID_NUMERO_DOC &&
                                                t.NOTA === value.NOTA)
                                        ));
    item.QTA_RESIDUA -= qty;
    let isCompleta = false;
    if(item.QTA_RESIDUA == 0){
        isCompleta = true;
    }
    
    $.post({
        url: "./ws/RichiesteMovimentazione.php",
        dataType: 'json',
        data: {
            riga: item,
            testata : documenti[documentoSelezionato],
            isCompleta : isCompleta
        },
        headers: {
            'Authorization': 'Bearer ' + sessionStorage.getItem('token')
        },
        success:function(data, status) {
            showSuccessMsg("Riga confermata con successo");
            console.log("data->",data);
            checkStatoTrasferimento(data["id"], null);
            $("#btnConfirmPart button").attr("disabled",false);
            $("#btnPreleva button").attr("disabled",false);    
        },
        error: function(data, status){
            console.log('ERRORE -> cambioMagazzinoUbicazione', data);
            showError(data)
            $("#qrcode").val('');
        }
    });
}


function checkStatoTrasferimento(id, completa) {
    console.log("begin checkStatoTrasferimento");
    let isCompleta = completa;
    console.log("isCompleta = "+isCompleta);
    let i = 1;
    let item = documenti[documentoSelezionato].RIGHE[rigaSelezionata];

    $.get({
        url: "./ws/CheckStatusRichiesta.php",
        dataType: 'json',
        headers: {
            'Authorization': 'Bearer ' + sessionStorage.getItem('token')
        },data: {
            riga: item,
            idDoc: id
        },
        success:function(data, status) {
            if(data.data != 0 ){
                i++;
                if(i < 5){
                    checkStatoTrasferimento(id);   
                } else {
                    showError("Problemi di load Batch di caricamento. Un caricamento è rimasto appeso o ci sta mettendo più tempo del previsto.");
                }
            } else {
                console.log("data.nrighe = "+data.nrighe);
                console.log("isCompleta = "+isCompleta);
                
                if(isCompleta != true){
                    openDoc(documentoSelezionato, item["ID_ANNO_DOC"], item["ID_NUMERO_DOC"]);
                    initStato(1);
                    window.scrollTo(0,0);
                } else {
                    if(data.nrighe > 0){
                        openDoc(documentoSelezionato, item["ID_ANNO_DOC"], item["ID_NUMERO_DOC"]);
                        initStato(1);
                        window.scrollTo(0,0);
                    } else {
                        loadMore(loadedPage, null);
                        window.scrollTo(0,0);
                    }
                }
            }
        },
        error: function(data, status) {
            console.log('ERRORE nel caricare i saldi', data);
            showError(data);
        }
    });
}

function preleva() {
    let item = documenti[documentoSelezionato].RIGHE[rigaSelezionata];
    if($("#ubi_arrivo").val() != null && $("#ubi_arrivo").val() != "") {    
        let commessa = $("#selectCommessa option:selected").text();
        if (commessa == '-') commessa = null;
        let qty = parseFloat($("#qty").val());
        item.PRELIEVI.push({
            MAGAZZINO: $("#magazzinoOrigine").val(),
            UBICAZIONE: $("#ubicazioneOrigine").val(),
            COMMESSA: commessa,
            QUANTITA: $("#qty").val(),
            MAGAZZINO_ARRIVO: $("#magazzinoDest").val(),
            UBICAZIONE_ARRIVO: $("#ubi_arrivo").val(),
            COMMESSA_ARRIVO: item.R_COMMESSA_ARR,
            ID_ANNO_DOC: item.ID_ANNO_DOC,
            ID_RIGA_DOC: item.ID_RIGA_DOC,
            ID_NUMERO_DOC: item.ID_NUMERO_DOC,
            NOTA: $("#note").val()
        });        
        prelievi = item.PRELIEVI;
        item.QTA_RESIDUA -= qty;
        if (item.QTA_RESIDUA > 0) {
            openRow(documentoSelezionato, rigaSelezionata);
        } else {
            conferma();
        }
    }
}

function plus(maxQty) {
    if(parseFloat(qntSelezionata) >= parseFloat(maxQty) && $("#qty").val() <= parseFloat(maxQty) - 1) {
        $("#qty").val((parseFloat($("#qty").val())+1).toFixed(3));    
    }
    checkQty();
}

function minus(minimum = 1) {
    if($("#qty").val() >= minimum + 1) {
        $("#qty").val((parseFloat($("#qty").val())-1).toFixed(3));
    }
    checkQty();
}


function plus10(maxQty) {
    if(parseFloat(qntSelezionata) >= parseFloat(maxQty) && $("#qty").val() <= parseFloat(maxQty) - 10) {
        $("#qty").val((parseFloat($("#qty").val())+10).toFixed(3));    
    }
    checkQty();
}

function minus10(minimum = 1) {
    if($("#qty").val() >= minimum + 10) {
        $("#qty").val((parseFloat($("#qty").val())-10).toFixed(3));
    }
    checkQty();
}

function interrogaUbicazione(ubicazione, articolo, callback) {
    $.get({
        url: "./ws/Interrogazione.php?codUbicazione=" + ubicazione + "&codArticolo=" + articolo + "&mov=Y",
        dataType: 'json',
        headers: {
            'Authorization': 'Bearer ' + sessionStorage.getItem('token')
        },
        success: function(data, status) {
            let documenti = data["data"];
            $("#ArtBox").remove();
            $("#divSingolaRiga > .panel > p").append("<div id='ArtBox'>Descrizione Art: <strong>"+ documenti[0].DESCRIZIONE+"</strong><br/>Disegno: <strong>"+ documenti[0].DISEGNO+"</strong><br/></div>" );
            callback(documenti);
        },
        error: function(data, status) {
            console.log('ERRORE nel caricare i saldi', data);
            showError(data);
        }
    });
}

$(document).on("change","#ubicazioneOrigine",function() {
    
    ubicazioneCorrente = $("#ubicazioneOrigine option:selected").val();
    if (ubicazioneCorrente != null && ubicazioneCorrente != -1) {
        interrogaUbicazione(ubicazioneCorrente, articoloMovimentazione, (documentiGiacenze) => {
            stylecomm = "style='display:none'";
            if($(".btn_setting").hasClass("activebtn")){
                stylecomm = '';
            } 
            let datiStampati = `<div class='commessaBox' `+stylecomm+` >
                                    <label>Commesse disponibili</label>
                                    <select id='selectCommessa' class='form-control' onchange='setGiacenzaCommessaSelezionata()'>
                                        <option value='0'>Seleziona una commessa </option>`;
                       
                                        let opt = "";
                        documentiGiacenze.forEach(x => {
                            let stampoOpt = false;
                            let selected = false;
                            let commessa = x.ID_COMMESSA || '-';
                            if(Object.keys(prelievi).length != 0){
                                let qntResiduaOpt = x.QTA_GIAC_PRM;
                                prelievi.forEach(y => {
                                    if((y.COMMESSA == x.ID_COMMESSA && x.ID_UBICAZIONE == y.UBICAZIONE) && parseInt(y.QUANTITA) <= parseInt(x.QTA_GIAC_PRM)) {
                                        qntResiduaOpt = qntResiduaOpt - y.QUANTITA;
                                        if(qntResiduaOpt > 0) {
                                            stampoOpt = true;
                                        }
                                        //stampo option
                                    } else if((y.COMMESSA == x.ID_COMMESSA && x.ID_UBICAZIONE == y.UBICAZIONE) && parseInt(y.QUANTITA) > parseInt(x.QTA_GIAC_PRM)) {
                                        stampoOpt = false;
                                    }
                                    selected = (commessa == commessaCorrente) ? " selected " : "";                              
                                });

                                if(x.QTA_GIAC_PRM > 0 && qntResiduaOpt > 0) {
                                    opt += `<option value='${qntResiduaOpt}' data-prm='${x.R_UM_PRM_MAG}' ${selected}>${commessa}</option>`
                                }

                            } else {
                                let commessa = x.ID_COMMESSA || '-';
                                let selected = (commessa == commessaCorrente) ? " selected " : "";
                                if(x.QTA_GIAC_PRM > 0) {
                                    opt += `<option value='${x.QTA_GIAC_PRM}' data-prm='${x.R_UM_PRM_MAG}' ${selected}>${commessa}</option>`
                                }
                            }           
                        });
                datiStampati += `${opt}</select>
                             </div>`;
            datiStampati += `<div class='input-group inputDiv qntBox'>  
                                <div class='input-group-prepend'>
                                    <button class='btn btnInputForm btnMinus' type='button' onfocusout='timerOn = true' onClick='timerOn = false;minus10(1)'>-10</button>
                                </div>
                                <div class='input-group-prepend'>
                                    <button class='btn btnInputForm btnMinus' type='button' onfocusout='timerOn = true' onClick='timerOn = false;minus(1)'>-</button>
                                </div>`;
            datiStampati += `   <input type='number' onclick='timerOn = false' onfocusout='timerOn = true' disabled class='form-control inputOsai' id='qty' class='inputOsai' value='${qntTotaleRichiesta}' min='0.001' onchange='checkQty()' placeholder='Quantità da prelevare' aria-label='Quantità da prelevare' aria-describedby='basic-addon2'>`;
            datiStampati += `   <div class='input-group-append'>
                                    <button class='btn btnInputForm btnPlus' type='button' onfocusout='timerOn = true' onClick='timerOn = false; plus("${qntTotaleRichiesta}")'>+</button>
                                </div>
                                <div class='input-group-append'>
                                    <button class='btn btnInputForm btnPlus' type='button' onfocusout='timerOn = true' onClick='timerOn = false; plus10("${qntTotaleRichiesta}")'>+10</button>
                                </div>
                            </div>`;
            $(".commessaBox").remove();
            $(".qntBox").remove();
            $("#divSingolaRiga").append(datiStampati);
            $('.err_box').remove();
            
            if(blackListUbicazioni.includes($("#ubi_arrivo").val().trim())) {        
                $("#boxqnt").prepend("<p class='err_box' style='color: red;text-decoration: underline;font-weight: bold;'>UBICAZIONE '"+ubicazioneDest.trim()+"' NON SELEZIONABILE<br></p>");
            }
            if($("#selectCommessa").val() == 0){
                $("#boxqnt").prepend("<p class='err_box' style='color: red;text-decoration: underline;font-weight: bold;'>COMMESSA MANCANTE <br></p>");
            }
            
            if($("#magazzinoOrigine").val() == -1){
                $("#boxqnt").prepend("<p class='err_box' style='color: red;text-decoration: underline;font-weight: bold;'>MAGAZZINO PARTENZA MANCANTE <br></p>");
            }
            
            if($("#ubicazioneOrigine").val() == -1){
                $("#boxqnt").prepend("<p class='err_box' style='color: red;text-decoration: underline;font-weight: bold;'>UBICAZIONE PARTENZA MANCANTE <br></p>");
            }
            if($("#magazzinoDest").val() == -1){
                $("#boxqnt").prepend("<p class='err_box' style='color: red;text-decoration: underline;font-weight: bold;'>MAGAZZINO DESTINAZIONE MANCANTE <br></p>");
            }
            
            $("#selectCommessa").trigger("change");
            if(($("#selectCommessa").val() == 0 || $("#magazzinoOrigine").val() == -1 || $("#ubicazioneOrigine").val() == -1 || $("#magazzinoDest").val() == -1) 
                && !$(".btn_setting").hasClass("activebtn")){
                $(".btn_setting").addClass("redColor");
            }
        })
    } else {
        $(".err_box").remove();
        if($("#selectCommessa").val() == 0){
            $("#boxqnt").prepend("<p class='err_box' style='color: red;text-decoration: underline;font-weight: bold;'>COMMESSA MANCANTE <br></p>");
        }
        
        if($("#magazzinoOrigine").val() == -1 || $("#magazzinoOrigine").val() == null){
            $("#boxqnt").prepend("<p class='err_box' style='color: red;text-decoration: underline;font-weight: bold;'>MAGAZZINO PARTENZA MANCANTE <br></p>");
        }
        
        if($("#ubicazioneOrigine").val() == -1 || $("#ubicazioneOrigine").val() == null){
            $("#boxqnt").prepend("<p class='err_box' style='color: red;text-decoration: underline;font-weight: bold;'>UBICAZIONE PARTENZA MANCANTE <br></p>");
        }
        if($("#magazzinoDest").val() == -1  || $("#magazzinoDest").val() == null){
            $("#boxqnt").prepend("<p class='err_box' style='color: red;text-decoration: underline;font-weight: bold;'>MAGAZZINO DESTINAZIONE MANCANTE <br></p>");
        }
        if(blackListUbicazioni.includes($("#ubi_arrivo").val().trim())) {        
            $("#boxqnt").prepend("<p class='err_box' style='color: red;text-decoration: underline;font-weight: bold;'>UBICAZIONE '"+ubicazioneDest.trim()+"' NON SELEZIONABILE<br></p>");
        }
        $("#selectCommessa").trigger("change");
        if(($("#selectCommessa").val() == 0 || $("#magazzinoOrigine").val() == -1 || $("#ubicazioneOrigine").val() == -1 || $("#magazzinoDest").val() == -1) 
            && !$(".btn_setting").hasClass("activebtn")){
            $(".btn_setting").addClass("redColor");
        }
    }
});

function onChangeMagazzino() {
    let datiUbicazioneFiltrati;
    let datiStampati = "";
    for (let i = 0; i < datiUbicazione.length; i++) {
        let selected = "";
        if(ubicazioneCorrente === datiUbicazione[i].ID_UBICAZIONE){
            selected = "selected='selected'";
        }
        if($("#magazzinoOrigine").val() == datiUbicazione[i].ID_MAGAZZINO){
            datiStampati += "<option "+selected+" value='"+datiUbicazione[i].ID_UBICAZIONE+"' data-qta='"+datiUbicazione[i].QTA_GIAC_PRM+"'>" + datiUbicazione[i].ID_UBICAZIONE + " </option>";                    
        }
    }      
    $("#ubicazioneOrigine").html(datiStampati);
    $("#ubicazioneOrigine").trigger("change");
    if($("#magazzinoOrigine").val() == -1 || $("#ubicazioneOrigine").val() == -1 || $("#magazzinoDest").val() == -1  || $("#selectCommessa").val() == 0){
        $("#btnPreleva button").attr('disabled', true);
        $("#btnConfirm button").attr('disabled', true);
    }
}

function onChangeMagazzinoDest() {
    if($("#magazzinoDest").val() != -1){
        $(".qrcode").show();
        $(".settings").show();
    } else {
        if($("#magazzinoOrigine").val() == -1 || $("#ubicazioneOrigine").val() == -1 || $("#magazzinoDest").val() == -1 || $("#selectCommessa").val() == 0){
            $("#btnPreleva button").attr('disabled', true);
            $("#btnConfirm button").attr('disabled', true);
            $("#btnConfirmPart button").attr('disabled', true);            
        }
        $(".qrcode").hide();
        $(".settings").hide();
    }
}

document.getElementById("search").addEventListener("keyup", function(event) {
    this.value = this.value.toUpperCase();
    console.log("listening to keyup");
    if (event.keyCode === 13) {
        $.get({
            url: "./ws/RichiesteMovimentazione.php?idNumeroDoc=" + this.value,
            dataType: 'json',
            headers: {
                'Authorization': 'Bearer ' + sessionStorage.getItem('token')
            },
            success: function(data, status) {
                $("#divElencoDocumenti").html("");
                maxItems = data.count;                
                let elementiCaricati = data.data;
                documenti = elementiCaricati;
                let i = 0;
                elementiCaricati.forEach(x => {
                    $("#divElencoDocumenti").append(getHtmlGrigliaDocumenti(i++, x));
                });
            },
            error: function(data, status) {
                console.log('ERRORE nel caricare la documenti delle righe', data);
                showError(data);
            }
        }); 
    }
    return false;
});

document.getElementById("qrcode").addEventListener("keyup", function(event) {
    $("#ubi_arrivo").val("");
    this.value = this.value.toUpperCase();
    console.log("listening to keyup")
    if (event.keyCode === 13) {
        if(blackListUbicazioni.includes($("#qrcode").val().trim())) { 
            $('.err_box').remove();
            $("#boxqnt").prepend("<p class='err_box' style='color: red;text-decoration: underline;font-weight: bold;'>UBICAZIONE '"+$("#qrcode").val().trim()+"' NON SELEZIONABILE<br></p>");
            return false;
        } 

        $.get({
            url: "./ws/Interrogazione.php?idMagazzino=" + $("#magazzinoDest").val()+ "&codUbicazione="+$("#qrcode").val(),
            dataType: 'json',
            headers: {
                'Authorization': 'Bearer ' + sessionStorage.getItem('token')
            },
            success: function(data, status) {
                if(data["count"] > 0) {
                    $("#ubi_arrivo").val($("#qrcode").val());
                    checkQty();
                } else {
                    showError("Inserire una ubicazione esistente per il Magazzino "+ $("#magazzinoDest").val());
                }
            },
            error: function(data, status) {
                console.log('ERRORE nel caricare la documenti delle righe', data);
                showError(data);
            }
        }); 
    }
});


document.getElementById("filtroRicerca").addEventListener("keyup", function(event) {
    this.value = this.value.toUpperCase().trim();    
    let item = documenti[documentoSelezionato];
    if (event.keyCode === 13) {
        $.get({
            url: "./ws/RichiesteMovimentazione.php?idAnnoDoc=" + annoDoc + "&idNumeroDoc="+numeroDoc+ "&search=" + this.value,
            dataType: 'json',
            headers: {
                'Authorization': 'Bearer ' + sessionStorage.getItem('token')
            },
            success: function(data, status) {
                item.RIGHE = data.data;
                item.RIGHE.forEach(riga => {
                    // aggiungo i campi che non esistono server-side
                    riga.PRELIEVI = [];
                    riga.QTA_RESIDUA = parseFloat(riga.QTA_UM_PRM);
                });
                annoDoc = annoDoc;
                numeroDoc = numeroDoc;
                ridisegnaElencoRigheDocumenti();
            },
            error: function(data, status) {
                console.log('ERRORE nel caricare la documenti delle righe', data);
                showError(data);
            }
        }); 
    }
});



/* PAGINAZIONE */ 


function prevPage()
{
    if (current_page > 0) {
        current_page--;
        changePage("prev");
    }
}
function firstPage()
{
    current_page = 0;
    changePage("prev");
    
}
function lastPage()
{
    current_page = numPages()-1;
    changePage("next");
    
}

function nextPage()
{
    if (current_page < numPages()) {
        current_page++;
        changePage("next");
    }
}

changePage(current_page);

function changePage(operazione)
{
    var btn_next = document.getElementById("btn_next");
    var btn_prev = document.getElementById("btn_prev");
    var btn_first = document.getElementById("btn_first");
    var btn_last = document.getElementById("btn_last");
    
    if (current_page < 1) current_page = 0;
    if (current_page > numPages()) current_page = numPages();

    loadedPage = current_page;
    loadMore(current_page, operazione);
    
    if (current_page == 0) {
        btn_prev.style.visibility = "hidden";
        btn_first.style.visibility = "hidden";
    } else {
        btn_prev.style.visibility = "visible";
        btn_first.style.visibility = "visible";
    }

    if (current_page == numPages()-1) {
        btn_next.style.visibility = "hidden";
        btn_last.style.visibility = "hidden";
        
    } else {
        btn_next.style.visibility = "visible";
        btn_last.style.visibility = "visible";
    }
}

function numPages()
{
    return Math.ceil(maxItems / records_per_page);
}


function showSuccessMsg(msg) {
    alert(msg);
}

$(document).on('click','.btn_setting',function(){
    if($(this).attr("data-open") == "false"){
        $(this).attr("data-open","true").addClass("activebtn");
        $(".commessaBox, #magazArrivo, #magazPrelievo").show();
    } else {
        $(this).attr("data-open","false").removeClass("activebtn");
        $(".commessaBox, #magazArrivo, #magazPrelievo").hide();
    }
});

/*
$(document).ready(function(){
    $(".focus").focus();
    $(".focusRicerca").focus();
    setInterval(function() {
        if(timerOn) {
            console.log("Focusing");
            $("#qrcode").get(0).focus();
            $("#filtroRicerca").get(0).focus();
            $("#search").get(0).focus();
        }
    }, 1000);
});
*/