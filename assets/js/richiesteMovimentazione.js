var loaded = 0;
var maxItems = 1000;
var documenti = [];
var documentoSelezionato = null;
var rigaSelezionata = null;
var magazzinoCorrente = null;
var commessaCorrente = null;
var ubicazioneCorrente = null;
var statoCorrente = 0;

/**
 * Inizializza l'interfaccia per un certo stato i
 */
function initStato(i) {
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
    } else if (statoCorrente == 1) {
        // griglia delle righe del documento
        // deve anche mostrare l'ubicazione 
        rigaSelezionata = null;
        $("#divElencoDocumentiContainer").hide();
        $("#divElencoRigheDocumentiContainer").show();
        $("#divSingolaRigaContainer").hide();
        $('#btnConfirm').hide();
        $('#btnPreleva').hide();
        $('#btnBack').show();
    } else if (statoCorrente == 2) {
        // maschera di scelta dell'ubicazione
        $("#divElencoDocumentiContainer").hide();
        $("#divElencoRigheDocumentiContainer").hide();
        $("#divSingolaRigaContainer").show();
        $('#btnPreleva').show().attr('disabled', true);
        $('#btnConfirm').hide();
        $('#btnBack').show();
    }
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
function loadMore() {
    initStato(0);
    if (loaded >= maxItems) return; // no more items
    $.get({
        url: "./ws/RichiesteMovimentazione.php?skip=" + loaded + "&top=10",
        dataType: 'json',
        headers: {
            'Authorization': 'Bearer ' + sessionStorage.getItem('token')
        },
        success: function(data, status) {
            maxItems = data.count;
            let elementiCaricati = data.data;
            documenti = documenti.concat(elementiCaricati);
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

// cfr. https://dev.to/trex777/infinite-scrolling-using-intersection-observer-api-118l
document.addEventListener("DOMContentLoaded", () => {
    let options = {
      root: null,
      rootMargin: "0px",
      threshold: 0.25
    };
  
    function handleIntersect(entries, observer) {
      entries.forEach((entry) => {
        if (entry.isIntersecting) {
          loadMore();
        }
      });
    }
  
  let observer = new IntersectionObserver(handleIntersect, options);
  const loader = document.getElementById("loading");
  observer.observe(loader);
});

function showError(data) {
    const err = typeof data === 'string' ? data :
                data.responseJSON && data.responseJSON.error && data.responseJSON.error.value.length > 0 ? data.responseJSON.error.value :
                "Errore interno";
    alert(err);
}

function getHtmlGrigliaDocumenti(idDoc, x) {
    // qui x dovrebbe essere uguale a documenti[id]
    return `<div class="rigaDocumenti" onclick="openDoc(${idDoc})">
                <strong>${x.NUMERO_DOC_FMT}</strong><br/>del ${x.DATA_DOC}
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
    $("#divElencoRigheDocumenti").html(`Doc. <strong>${doc.NUMERO_DOC_FMT}</strong>`);
    let numRiga = 0;
    let tuttoPrelevato = true;
    doc.RIGHE.forEach(riga => {
        $("#divElencoRigheDocumenti").append(getHtmlGrigliaRighe(documentoSelezionato, numRiga++, riga));
        if (riga.QTA_RESIDUA > 0) tuttoPrelevato = false;
    });
    $("#btnConfirm").attr("disabled", !tuttoPrelevato);
}

function getHtmlGrigliaRighe(idDoc, idRiga, riga) {
    // QUI x dovrebbe essere documenti[id].RIGHE[id2]
    let commessa = riga.R_COMMESSA || '-';
    let stato = riga.QTA_RESIDUA <= 0 ? "&#10003;" : "&bullet;";
    return `<div class="rigaDocumenti" onclick="openRow(${idDoc},${idRiga})">
                ${stato} Art. ${riga.R_ARTICOLO} Comm. ${commessa} ${riga.QTA_UM_PRM} Qta. richiesta ${riga.R_UM_PRM_MAG}
            </div>`;
}

function openRow(idDoc, idRiga) {
    documentoSelezionato = idDoc;
    rigaSelezionata = idRiga;
    initStato(2);
    let x = documenti[documentoSelezionato].RIGHE[rigaSelezionata];
    magazzinoCorrente = x.R_MAGAZZINO;

    $("#divSingolaRiga").html(`Articolo ${x.R_ARTICOLO} Comm. ${x.R_COMMESSA} - ${x.QTA_UM_PRM} ${x.R_UM_PRM_MAG}<br/>`);

    $.get({
        url: "./ws/GetMagazziniAlternativi.php",
        dataType: 'json',
        headers: {
            'Authorization': 'Bearer ' + sessionStorage.getItem('token')
        },
        success: function(data, status) { 
            let dati = data["data"];
            arrUbicazioniDest = dati;
            if(dati[0] == null || dati.length === 0) {
                showError("Errore interno nel reperire i magazzini");
                $("#qrcode").val('');
                return false;
            }
            
            datiStampati += "<select onchange='onChangeMagazzino();'' onclick='timerOn = false' id='magazzinoOrigine' onfocusout='timerOn = true' class='form-control'>";    
            datiStampati += "<option value='-1'> Seleziona magazzino partenza </option>";                            
            for (let i = 0; i < dati.length; i++) {
                // FIXME di default mettere quello che arriva dalla riga del documento
                datiStampati += "<option value='"+dati[i]+"'>" + dati[i] + "</option>";                    
            } 
            datiStampati+= "</select>";
            $("#divSingolaRiga").append(datiStampati);

            $.get({
                url: `./ws/Interrogazione.php?codArticolo=${x.R_ARTICOLO}`,
                dataType: 'json',
                headers: {
                    'Authorization': 'Bearer ' + sessionStorage.getItem('token')
                },
                success: function(data, status) { 
                    let dati = data["data"];
                    arrUbicazioniDest = dati;
                    if(dati[0] == null || dati.length === 0) {
                        showError("Nessuna ubicazione disponibile");
                        $("#qrcode").val('');
                        return false;
                    }
                    
                    datiStampati += "<select onchange='onChangeUbicazione();'' onclick='timerOn = false' id='ubicazioneOrigine' onfocusout='timerOn = true' class='form-control'>";    
                    datiStampati += "<option value='-1'> Seleziona ubicazione partenza </option>";                            
                    for (let i = 0; i < dati.length; i++) {
                        datiStampati += "<option value='"+dati[i]+"'>" + dati[i] + "</option>";                    
                    } 
                    datiStampati+= "</select>";
                    $("#divSingolaRiga").append(datiStampati);
                    
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

    // TODO qui devo fare apparire un menu a tendina con tutti i magazzini, con preselezionato il magazzinoCorrente,
    // e poi un altro con tutte le ubicazioni del magazzino selezionato che contengano l'articolo x.R_ARTICOLO

    /*if (ubicazioneCorrente) {
        interrogaUbicazione(ubicazioneCorrente, x.R_ARTICOLO, (documentiGiacenze) => {
            let datiStampati = `Ubicazione ${ubicazioneCorrente}.<br/>
            Seleziona quantità oppure cambia ubicazione.
            <select id='selectCommessa' class='form-control' onchange='setGiacenzaCommessaSelezionata()'>`;
            documentiGiacenze.forEach(x => {
                let commessa = x.ID_COMMESSA || '-';
                let selected = (commessa == x.R_COMMESSA) ? " selected " : "";
                datiStampati += `<option value='${x.QTA_GIAC_PRM}' data-prm='${x.R_UM_PRM_MAG}' ${selected}>${commessa}</option>`
            });
            datiStampati += `</select>
            <input type='number' class='form-control inputOsai' disabled id='qty' class='inputOsai' value='1' min='0.001' placeholder='Quantità da prelevare' aria-label='Quantità da prelevare' aria-describedby='basic-addon2' onchange='checkQty()'>
            `;
            $("#divSingolaRiga").append(datiStampati);
            setGiacenzaCommessaSelezionata();
        })
    } else {
        $("#divSingolaRiga").append("Scegliere una ubicazione:");
    }*/
}

function setGiacenzaCommessaSelezionata() {
    let giacenzaSelezionata = $("#selectCommessa").val(); // spero che funzioni
    console.log("giacenzaSelezionata=", giacenzaSelezionata);
    $("#qty").val(giacenzaSelezionata);
    checkQty();
}

function checkQty() {
    let qty = $("#qty").val();
    $("#btnPreleva").attr('disabled', qty > 0 ? false : true);
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
