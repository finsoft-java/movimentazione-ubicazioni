let timerOn = true;

$(document).ready(function(){
    $(".focus").focus();
    setInterval(function() {
        if(timerOn) {
            console.log("Focusing");
            $("#qrcode").get(0).focus();
        }
    }, 1000);
});

let i = 0;
// i e' uno STATO
// 0 = initial
// 1 = e' stato sparato il primo barcode (ubicazione)
// 2 = e' stato sparato il secondo barcode (magazzinoDest)
let arrUbicazioniDest = [];
let ubicazione;
let magazzinoDest;
let idMagazzino;
document.getElementById("qrcode").addEventListener("keyup", function(event) {
    this.value = this.value.toUpperCase();

    if (event.keyCode === 13) {
        event.preventDefault();
        i++;
        barCode = $("#qrcode").val();

        if(i == 1) {
            if(barCode.trim() != ""){                
                ubicazione = barCode;
                $.get({
                    url: "./ws/GetUbicazione.php?codUbicazione=" + ubicazione,
                    dataType: 'json',
                    headers: {
                        'Authorization': 'Bearer ' + sessionStorage.getItem('token')
                    },
                    success: function(data, status) {
                        let dati = data.value;
                        if(dati == null) {
                            showError("Ubicazione inesistente si prega di riprovare");
                            $("#qrcode").val('');
                            i=0;
                            return false;
                        }
                        if(dati.TRASFERIBILE=='N') {
                            showError("Ubicazione dichiarata non trasferibile");
                            $("#qrcode").val('');
                            i=0;
                            return false;
                        }
                        $("#qrcode").attr('placeholder','MAGAZZINO DEST.');
                        let datiStampati = "";
                        idMagazzino = dati.ID_MAGAZZINO;
                        datiStampati += "<p class='pOsai'> Magazzino origine: <strong>"+dati.ID_MAGAZZINO+"</strong></p>";
                        datiStampati += "<p class='pOsai'> Codice ubicazione: <strong>"+dati.ID_UBICAZIONE+"</strong></p>";
                        timerOn = false;
                        $("#appendData").html(datiStampati);
                        getMagazziniAlternativi();
                    },
                    error: function(data, status){
                        $("#qrcode").attr('placeholder','UBICAZIONE ORIG.');
                        console.log('ERRORE -> Interrogazione', data);
                        showError(data);
                        $("#qrcode").val('');
                        ubicazione = null;
                    }
                });            
                $("#qrcode").val("");

            } else {

                showError("Ubicazione mancante");
                $("#qrcode").val('');
                i=0;
                return false;

            }
        }
        
        if(i == 2) {
            if(barCode.trim() != ""){
                magazzinoDest = barCode; 
            } else {
                $("#qrcode").attr('placeholder','UBICAZIONE DEST.');
                showError("Magazzino di destinazione inesistente si prega di riprovare");
                $("#qrcode").val('');
                i=1;
                return false;
            }
            if(!arrUbicazioniDest.includes(barCode)) {         
                $("#qrcode").attr('placeholder','UBICAZIONE DEST.');
                showError("Magazzino di destinazione non valido");
                $("#qrcode").val('');
                magazzinoDest= null;
                i=1;
                return false;
            }
            $("#qrcode").attr('placeholder','UBICAZIONE ORIG.').val("").attr('disabled',true);
            $("#magazzinoDest").val(barCode);
            $("#magazzinoDest").trigger("change");
        }
    }
});
function getMagazziniAlternativi(){
    let datiStampati = "";
    $.get({
        url: "./ws/GetMagazziniAlternativi.php?idMagazzino="+ idMagazzino,
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
            
            datiStampati += "<select onchange='updateInputValue();'' onclick='timerOn = false' id='magazzinoDest' onfocusout='timerOn = true' class='form-control'>";    
            datiStampati += "<option value='-1'> Seleziona magazzino destinazione </option>";                            
            for(let i=0; i<dati.length; i++) {
                datiStampati += "<option value='"+dati[i]+"'>" + dati[i] + "</option>";                    
            } 
            datiStampati+= "</select>";
            setTimeout(function() {
                    console.log("appendSelect");
                    $("#appendSelect").html(datiStampati);
            }, 500);
            
        }, error: function(data, status) {
            console.log('ERRORE -> getMagazziniAlternativi', data);
            showError(data);
        }
    });
}
function cambioMagazzinoUbicazione() {

    timerOn = true;
    let magazzinoDest = $("#magazzinoDest").val();

    $("#qrcode").attr("disabled", true);
    $('#btnCambio').attr("disabled", true);

    if(magazzinoDest == -1){
        showError("Magazzino di destinazione inesistente si prega di riprovare");
        $("#qrcode").val('');
        return false;
    }

    $.post({
        url: "./ws/CambioMagazzinoUbicazione.php?codUbicazione=" + ubicazione + "&codMagazzinoDest=" + $("#magazzinoDest").val(),
        dataType: 'json',
        headers: {
            'Authorization': 'Bearer ' + sessionStorage.getItem('token')
        },
        success: function(data, status) {
            showSuccessMsg("Ubicazione spostata correttamente nel magazzino " + magazzinoDest);
        },
        error: function(data, status){
            console.log('ERRORE -> cambioMagazzinoUbicazione', data);
            showError(data)
            $("#qrcode").val('');
        }
    });
}

function updateInputValue() {
    if($("#magazzinoDest :selected").val() != -1){
        $("#qrcode").val($("#magazzinoDest :selected").text());
        $("#btnCambio").attr('disabled',false);
    }
    else {
        $("#qrcode").val('');
        $("#btnCambio").attr('disabled',true);
    }
}

function annulla() {
    $("#qrcode").val('');
    window.location.reload();
}

function showError(data) {
    const err = typeof data === 'string' ? data :
                data.responseJSON && data.responseJSON.error && data.responseJSON.error.value.length > 0 ? data.responseJSON.error.value :
                "Errore interno";
    alert(err);
}

function showSuccessMsg(msg) {
    $('#success_message').text(msg);
    $('#success_message').show();
    setTimeout(() => {
        $('#success_message').hide();
        location.href="./index.html";
      }, "1000");
}