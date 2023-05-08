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
        $("#divElencoDocumentiContainer").show();
        $("#divElencoRigheDocumentiContainer").hide();
        $("#divSingolaRigaContainer").hide();
        $('#btnConfirm').hide();
        $('#btnPreleva').hide();
        $('#btnBack').hide();
        $("#navigateRichieste").show();
        $(".qrcode").hide();
        $("#search").show();
    } else if (statoCorrente == 1) {
        // griglia delle righe del documento
        // deve anche mostrare l'ubicazione 
        $("#navigateRichieste").hide();
        rigaSelezionata = null;
        $("#divElencoDocumentiContainer").hide();
        $("#search").hide();
        $("#divElencoRigheDocumentiContainer").show();
        $("#divSingolaRigaContainer").hide();
        $('#btnConfirm').hide();
        $('#btnPreleva').hide();
        $(".qrcode").hide();
        $('#btnBack').show();
    } else if (statoCorrente == 2) {
        // maschera di scelta dell'ubicazione
        $("#navigateRichieste").hide();
        $("#divElencoDocumentiContainer").hide();
        $("#divElencoRigheDocumentiContainer").hide();
        $("#divSingolaRigaContainer").show();
        $(".qrcode").hide();
        $('#btnPreleva').show().prop('disabled', true);
        $('#btnConfirm').hide();
        $('#btnBack').show();
    }
    console.log("fine statoCorrente",statoCorrente);
}

function indietro() {
    if (statoCorrente == 2) {
        let riga = documenti[documentoSelezionato].RIGHE[rigaSelezionata];
        if (riga.PRELIEVI.length == 0 || confirm('Stai per annullare tutti i prelievi in corso, sei sicuro?')) {
            riga.PRELIEVI = [];
            riga.QTA_RESIDUA = parseFloat(riga.QTA_UM_PRM);
            initStato(1);
            ridisegnaElencoRigheDocumenti();
        }
    } else if (statoCorrente == 1) {
        initStato(0);
    } else {
        console.error("indietro() chiamata in uno stato strano: " + statoCorrente);
    }
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
            maxItems = data.count;
            let elementiCaricati = data.data;
            documenti = documenti.concat(elementiCaricati);
            if(operazione == "prev"){
                loaded = loaded != 0 ? (loadedPage-1)*10 : 0;
            } 
            elementiCaricati.forEach(x => {                
                $("#divElencoDocumenti").append(getHtmlGrigliaDocumenti(loaded++, x));                
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
    var myDate = x.DATA_DOC.split(' ')[0];
    return `<div class="rigaDocumenti" onclick="openDoc(${idDoc})">
                <p style="padding: 30px 15px; margin: 0px; text-align: center;">Documento: <strong>${x.NUMERO_DOC_FMT}</strong> del ${myDate}</p><hr style="margin:0px;"/>
            </div>`;
}

function openDoc(idDoc) {
    documentoSelezionato = idDoc;
    let item = documenti[documentoSelezionato];
    initStato(1);
    $("#divSingolaUbicazPredef").html("");
    $.get({
        url: "./ws/RichiesteMovimentazione.php?idAnnoDoc=" + item.ID_ANNO_DOC + "&idNumeroDoc=" + item.ID_NUMERO_DOC,
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
            ridisegnaElencoRigheDocumenti();
        },
        error: function(data, status) {
            console.log('ERRORE nel caricare la documenti delle righe', data);
            showError(data);
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
}

function getHtmlGrigliaRighe(idDoc, idRiga, riga) {
    // QUI x dovrebbe essere documenti[id].RIGHE[id2]
    let commessa = riga.R_COMMESSA || '-';
    return `<div class="rigaDocumenti" onclick="openRow(${idDoc},${idRiga})" style="text-align:center;border-top: 1px solid rgba(0,0,0,.1);padding: 20px 15px;">
               <p style="margin:0px;">
                Articolo: <strong> ${riga.R_ARTICOLO}</strong> Commessa :<strong>${commessa} </strong> <br/>
                Qta. richiesta <strong>${riga.QTA_UM_PRM} </strong>
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
    articoloMovimentazione = x.R_ARTICOLO;

    $("#divSingolaRiga").html(`<p style="margin:30px;text-align:center">
                                    Dati Riga:</br></br>
                                    Articolo: <strong>  ${x.R_ARTICOLO} </strong> </br>
                                    Commessa Partenza: <strong>${commessaCorrente}</strong> | Commessa Arrivo: <strong>${commessaDest}</strong> </br>
                                    Ubicazione Partenza: <strong>${ubicazioneCorrente}</strong> | Ubicazione Arrivo: <strong>${ubicazioneDest}</strong> </br>
                                    Magazzino Partenza: <strong>${magazzinoCorrente}</strong> | Magazzino Arrivo: <strong>${magazzinoDest}</strong> </br></br>
                                    Richiesta quantità: <strong>${qntTotaleRichiesta}</strong>
                               </p>`);

    $.get({
        url: "./ws/GetMagazziniAlternativi.php",
        dataType: 'json',
        headers: {
            'Authorization': 'Bearer ' + sessionStorage.getItem('token')
        },
        success: function(data, status) { 
            magazziniDisponibili = data["data"];
            
            $.get({
                url: `./ws/Interrogazione.php?codArticolo=${x.R_ARTICOLO}`,
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
                    datiMagazzino = dati.filter((value, index, self) =>
                        index === self.findIndex((t) => (
                            t.ID_MAGAZZINO === value.ID_MAGAZZINO
                        ))
                    );
                    let datiStampati = "";
                    arrUbicazioniDest = dati;
                    if(dati[0] == null || dati.length === 0) {
                        showError("Nessuna ubicazione disponibile");
                        $("#qrcode").val('');
                        return false;
                    }

                    datiStampati += "<label>Magazzino Prelievo</label>";
                    datiStampati += "<select onchange='onChangeMagazzino();' onclick='timerOn = false' id='magazzinoOrigine' onfocusout='timerOn = true' class='form-control'>";    
                    datiStampati += "<option value='-1'> Seleziona magazzino partenza </option>";                            
                    for (let i = 0; i < datiMagazzino.length; i++) {
                        let selected = "";
                        if(magazzinoCorrente == datiMagazzino[i].ID_MAGAZZINO){
                            selected = "selected='selected'";
                        }
                        datiStampati += "<option "+selected+" value='"+datiMagazzino[i].ID_MAGAZZINO+"'>" + datiMagazzino[i].ID_MAGAZZINO + "</option>";                    
                    } 
                    datiStampati+= "</select>";

                    let magazzinoDestinazioneHtml = "";
                    magazzinoDestinazioneHtml += "<label>Magazzino di Arrivo</label>";
                    magazzinoDestinazioneHtml += "<select onchange='onChangeMagazzinoDest();' onclick='timerOn = false' id='magazzinoDest' onfocusout='timerOn = true' class='form-control'>";    
                    magazzinoDestinazioneHtml += "<option value='-1'> Seleziona magazzino destinazione </option>";                            
                    for (let i = 0; i < magazziniDisponibili.length; i++) {
                        let selected = "";
                        if(magazzinoDest == magazziniDisponibili[i]){
                            selected = "selected='selected'";

                        }
                        magazzinoDestinazioneHtml += "<option "+selected+" value='"+magazziniDisponibili[i]+"'>" + magazziniDisponibili[i] + "</option>";                    
                    } 
                    magazzinoDestinazioneHtml+= "</select>";
                    
                    datiStampati += "<label>Ubicazione di Prelievo</label>";
                    datiStampati += "<select onclick='timerOn = false' id='ubicazioneOrigine' onfocusout='timerOn = true' class='form-control'>";    
                    datiStampati += "<option value='-1'> Seleziona ubicazione partenza </option>";                            
                    for (let i = 0; i < datiUbicazione.length; i++) {
                        let selected = "";
                        console.log("arr Interrogazione.php?codArticolo=-> "+dati[i]);
                        if(ubicazioneCorrente === datiUbicazione[i].ID_UBICAZIONE){
                            selected = "selected='selected'";
                        }
                        datiStampati += "<option "+selected+" value='"+datiUbicazione[i].ID_UBICAZIONE+"' data-qta='"+datiUbicazione[i].QTA_GIAC_PRM+"'>" + datiUbicazione[i].ID_UBICAZIONE + " </option>";                    
                    } 
                    datiStampati += "</select>";
                    datiStampati += magazzinoDestinazioneHtml;                    
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
            
}

function setGiacenzaCommessaSelezionata() {
    let giacenzaSelezionata = $("#selectCommessa").val(); // spero che funzioni
    console.log("giacenzaSelezionata=", giacenzaSelezionata);
    if(giacenzaSelezionata > qntTotaleRichiesta){
        giacenzaSelezionata = qntTotaleRichiesta;
    }
    $("#qty").val(giacenzaSelezionata);
    checkQty();
}

function checkQty() {
    let qty = $("#qty").val();

    if($("#ubi_arrivo").val() != null && $("#ubi_arrivo").val() != ""){
        if(qty == qntTotaleRichiesta){
            $("#btnConfirm").show();
            $("#btnConfirm button").attr('disabled',false)
            $("#btnPreleva button").attr('disabled',true);
        } else {
            $("#btnPreleva button").attr('disabled', qty > 0 && qty <= qntTotaleRichiesta ? false : true);
            $("#btnConfirm button").attr('disabled',true);
        }        
    }
    
}

function preleva() {
    let item = documenti[documentoSelezionato].RIGHE[rigaSelezionata];

    let commessa = $("#selectCommessa option:selected").text();
    if (commessa == '-') commessa = null;
    let qty = parseFloat($("#qty").val());
    item.PRELIEVI.push({
        UBICAZIONE: ubicazioneCorrente,
        COMMESSA: commessa,
        QUANTITA: qty
    });
    item.QTA_RESIDUA -= qty;

    if (item.QTA_RESIDUA > 0) {
        openRow(documentoSelezionato, rigaSelezionata);
    } else {
        indietro();
    }
}

function plus(maxQty) {
    if($("#qty").val() <= maxQty - 1) {
        $("#qty").val((parseFloat($("#qty").val())+1).toFixed(3));    
    }
}

function minus(minimum = 1) {
    if($("#qty").val() >= minimum + 1) {
        $("#qty").val((parseFloat($("#qty").val())-1).toFixed(3));
    }
}


function interrogaUbicazione(ubicazione, articolo, callback) {
    $.get({
        url: "./ws/Interrogazione.php?codUbicazione=" + ubicazione + "&codArticolo=" + articolo,
        dataType: 'json',
        headers: {
            'Authorization': 'Bearer ' + sessionStorage.getItem('token')
        },
        success: function(data, status) {
            let documenti = data["data"];
            callback(documenti);
        },
        error: function(data, status) {
            console.log('ERRORE nel caricare i saldi', data);
            showError(data);
        }
    });
}

$(document).on("change","#ubicazioneOrigine",function(){
    ubicazioneCorrente = $("#ubicazioneOrigine option:selected").val();
    if (ubicazioneCorrente) {
        interrogaUbicazione(ubicazioneCorrente, articoloMovimentazione, (documentiGiacenze) => {
            console.log("----");
            console.log(documentiGiacenze);
            console.log("----");
            let datiStampati = `<div class='commessaBox'>
                                    <p style='margin:30px; 0px;text-align:center'>Ubicazione ${ubicazioneCorrente}.<br/> Seleziona la commessa oppure cambia ubicazione. </p>
                                    <label>Commesse disponibili</label>
                                    <select id='selectCommessa' class='form-control' onchange='setGiacenzaCommessaSelezionata()'>
                                        <option value='0'>Seleziona una commessa </option>`;
                        documentiGiacenze.forEach(x => {
                            let commessa = x.ID_COMMESSA || '-';
                            let selected = (commessa == commessaDest) ? " selected " : "";
                            if(x.QTA_GIAC_PRM > 0){
                                datiStampati += `<option value='${x.QTA_GIAC_PRM}' data-prm='${x.R_UM_PRM_MAG}' ${selected}>${commessa}</option>`
                            }
                        });
                datiStampati +=     `</select>`;
                datiStampati +=     `<div class='input-group inputDiv'>  
                                        <div class='input-group-prepend'>
                                            <button class='btn btnInputForm btnMinus' type='button' onClick='minus(1)'>-</button>
                                        </div>`;
                    datiStampati += `   <input type='number' class='form-control inputOsai' disabled id='qty' class='inputOsai' value='${qntTotaleRichiesta}' min='0.001' onchange='checkQty()' placeholder='Quantità da prelevare' aria-label='Quantità da prelevare' aria-describedby='basic-addon2'>`;
                    datiStampati += `   <div class='input-group-append'>
                                            <button class='btn btnInputForm btnPlus' type='button' onClick='plus("${qntTotaleRichiesta}")'>+</button>
                                        </div>
                                    </div>
                                </div>`;
            $(".commessaBox").remove();
            $("#divSingolaRiga").append(datiStampati);
            $("#selectCommessa").trigger("change");
            setGiacenzaCommessaSelezionata();
        })
    } else {
        $("#divSingolaRiga").append("Scegliere una ubicazione:");
    }
});
function onChangeMagazzino() {
    console.log(datiUbicazione);
    let datiUbicazioneFiltrati ;
    if($("#magazzinoOrigine").val() != -1){
        datiUbicazioneFiltrati = datiUbicazione.filter((value, index, self) =>
                            index === self.findIndex((t) => (
                                t.ID_MAGAZZINO === $("#magazzinoOrigine").val()
                            ))
                        );
    } else {
        datiUbicazioneFiltrati = datiUbicazione;
    }
    let datiStampati = "";
    for (let i = 0; i < datiUbicazioneFiltrati.length; i++) {
        let selected = "";
        if(ubicazioneCorrente === datiUbicazioneFiltrati[i].ID_UBICAZIONE){
            selected = "selected='selected'";
        }
        datiStampati += "<option "+selected+" value='"+datiUbicazioneFiltrati[i].ID_UBICAZIONE+"' data-qta='"+datiUbicazioneFiltrati[i].QTA_GIAC_PRM+"'>" + datiUbicazioneFiltrati[i].ID_UBICAZIONE + " </option>";                    
    }      
    $("#ubicazioneOrigine").html(datiStampati);
                    
    $("#ubicazioneOrigine").trigger("change");     
    console.log(datiUbicazione);
}
function onChangeMagazzinoDest() {
    if($("#magazzinoDest").val() != -1){
        $(".qrcode").show();
    } else {
        $(".qrcode").hide();
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
                console.log(data);
                $("#divElencoDocumenti").html("");
                maxItems = data.count;
                let elementiCaricati = data.data;
                documenti = documenti.concat(elementiCaricati);
                console.log("----------------",elementiCaricati);
                elementiCaricati.forEach(x => {
                    $("#divElencoDocumenti").append(getHtmlGrigliaDocumenti(loaded++, x));
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
    this.value = this.value.toUpperCase();
    console.log("listening to keyup")
    if (event.keyCode === 13) {
        $("#ubi_arrivo").val($("#qrcode").val());
        checkQty();
        /*
        $.get({
            url: "./ws/Interrogazione.php?codUbicazione=" + value,
            dataType: 'json',
            headers: {
                'Authorization': 'Bearer ' + sessionStorage.getItem('token')
            },
            success: function(data, status) {
                let dati = data["data"];
                if(dati[0] == null || dati.length === 0) {   
                    showError("Ubicazione vuota");
                    $("#qrcode").val('');
                    return false;
                }
                console.log("dati ", dati)
                let datiStampati = getHtmlTestata(dati[0]);
                for(let i = 0; i < Object.keys(dati).length;i++){
                    datiStampati += getHtmlArticolo(dati[i]);
                }
                $(".listaOsai").html(datiStampati);
                timerOn = false;
                $("#qrcode").hide();
                $("#btnInterroga").show();

            },
            error: function(data, status){
                showError(data);
                $("#qrcode").val('');
                $(".listaOsai").html("");
            }
        });
        $("#qrcode").val("");
        $("#searchAll").css("display","block");
        */
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
    var listing_table = document.getElementById("divElencoDocumenti");

    // Validate page
    if (current_page < 1) current_page = 0;
    if (current_page > numPages()) current_page = numPages();

    listing_table.innerHTML = "";
    loadMore(current_page, operazione);
/*
    for (var i = (page-1) * records_per_page; i < (page * records_per_page) && i < maxItems; i++){
        listing_table.innerHTML += objJson[i].adName + "<br>";
    }
    page_span.innerHTML = page;

*/
    if (current_page == 0) {
        btn_prev.style.visibility = "hidden";
    } else {
        btn_prev.style.visibility = "visible";
    }

    if (current_page == numPages()) {
        btn_next.style.visibility = "hidden";
    } else {
        btn_next.style.visibility = "visible";
    }
}

function numPages()
{
    return Math.ceil(maxItems / records_per_page);
}