var loaded = 0;
var maxItems = 1000;
var lista = [];
var documentoSelezionato = null;
var rigaSelezionata = null;
var ubicazionePredefinita = null;
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
        ubicazionePredefinita = null;
        $("#divElencoDocumentiContainer").show();
        $("#divElencoRigheDocumentiContainer").hide();
        $("#divSingolaRigaContainer").hide();
        $('#btnConfirm').hide();
        $('#btnBack').hide();
        $('.qrcode').hide();
    } else if (statoCorrente == 1) {
        // griglia delle righe del documento
        // deve anche mostrare l'ubicazione 
        rigaSelezionata = null;
        $("#divElencoDocumentiContainer").hide();
        $("#divElencoRigheDocumentiContainer").show();
        $("#divSingolaRigaContainer").hide();
        $('#btnConfirm').hide();
        $('#btnBack').show();
        $('.qrcode').show();
        $('#qrcode').attr('placeholder', 'UBICAZ. PREDEFINITA');
    } else if (statoCorrente == 2) {
        // maschera di scelta dell'ubicazione
        $("#divElencoDocumentiContainer").hide();
        $("#divElencoRigheDocumentiContainer").hide();
        $("#divSingolaRigaContainer").show();
        $('#btnConfirm').show().attr('disabled', true);
        $('#btnBack').show();
        $('.qrcode').show();
        $('#qrcode').attr('placeholder', 'UBICAZIONE');
    }
}

function indietro() {
    if (statoCorrente == 2) {
        initStato(1);
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
            lista = lista.concat(elementiCaricati);
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

function getHtmlGrigliaDocumenti(id, x) {
    // qui x dovrebbe essere uguale a lista[id]
    return `<div class="rigaDocumenti" onclick="openDoc(${id})">
                <strong>${x.NUMERO_DOC_FMT}</strong><br/>del ${x.DATA_DOC}
            </div>`;
}

function openDoc(id) {
    documentoSelezionato = id;
    let item = lista[id];
    initStato(1);
    $("#divElencoRigheDocumenti").html(`Doc. <strong>${item.NUMERO_DOC_FMT}</strong>`);
    $("#divSingolaUbicazPredef").html("");
    $.get({
        url: "./ws/RichiesteMovimentazione.php?idAnnoDoc=" + item.ID_ANNO_DOC + "&idNumeroDoc=" + item.ID_NUMERO_DOC,
        dataType: 'json',
        headers: {
            'Authorization': 'Bearer ' + sessionStorage.getItem('token')
        },
        success: function(data, status) {
            item.RIGHE = data.data;
            let id2 = 0;
            data.data.forEach(x => {
                $("#divElencoRigheDocumenti").append(getHtmlGrigliaRighe(id, id2++, x));
            });
        },
        error: function(data, status) {
            console.log('ERRORE nel caricare la lista delle righe', data);
            showError(data);
        }
    });    
}

function getHtmlGrigliaRighe(id, id2, x) {
    // QUI x dovrebbe essere lista[id].RIGHE[id2]
    return `<div class="rigaDocumenti" onclick="openRow(${id},${id2})">
                ${x.ID_RIGA_DOC} Art. ${x.R_ARTICOLO} ${x.QTA_UM_PRM} ${x.R_UM_PRM_MAG}
            </div>`;
}

function openRow(id, id2, usaUbicazioneCorrente) {
    documentoSelezionato = id;
    rigaSelezionata = id2;
    initStato(2);
    let x = lista[id].RIGHE[id2];

    $("#divSingolaRiga").html(`Articolo ${x.R_ARTICOLO} ${x.QTA_UM_PRM} ${x.R_UM_PRM_MAG}<br/>`);

    if (usaUbicazioneCorrente && ubicazioneCorrente) {
        interrogaUbicazione(ubicazioneCorrente, x.R_ARTICOLO, (giacenza) => {
            $("#divSingolaRiga").append(`Ubicazione ${ubicazioneCorrente} giacenza ${giacenza}`);
        })
    } else if (ubicazionePredefinita) {
        interrogaUbicazione(ubicazionePredefinita, x.R_ARTICOLO, (giacenza) => {
            $("#divSingolaRiga").append(`Ubicazione predefinita ${ubicazionePredefinita} giacenza ${giacenza}`);
        })
    } else {
        $("#divSingolaRiga").append("Scegliere una ubicazione:");
    }
}

function sparaQrcode() {
    let ubicazione = $('#qrcode').val();
    ubicazioneCorrente = ubicazione;
    if (statoCorrente == 1) {
        ubicazionePredefinita = ubicazione;
        $("#divUbicazionePredef").html(`Ubicazione predefinita <strong>${ubicazionePredefinita}</strong>`);
    } else if (statoCorrente == 2) {
        let x = lista[documentoSelezionato].RIGHE[rigaSelezionata];
        openRow(documentoSelezionato, rigaSelezionata, true);
    }
    $('#qrcode').val("");
}

function interrogaUbicazione(ubicazione, articolo, callback) {
    $.get({
        url: "./ws/Interrogazione.php?codUbicazione=" + ubicazione + "&codArticolo=" + articolo,
        dataType: 'json',
        headers: {
            'Authorization': 'Bearer ' + sessionStorage.getItem('token')
        },
        success: function(data, status) {
            let dati = data["data"];
            // per ora me ne frego delle commesse
            let qty = 0.0;
            dati.forEach(x => { qty += x.QTA_GIAC_PRM });
            callback(qty);
        },
        error: function(data, status) {
            console.log('ERRORE nel caricare i saldi', data);
            showError(data);
        }
    });
}

var timerOn = false;
$(document).ready(function(){
    setInterval(function() {
        if (timerOn) {
            $("#qrcode").get(0).focus();
        }
    }, 1000);
});
