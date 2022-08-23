let timerOn = true;
let disableBtn = true;

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
    if(!$("#qrcode").val()== '' && i==1){
        disableBtn = false;
        $("#btnCambio").attr('disabled',false);
    } else {
        $("#btnCambio").attr('disabled',true);
    }

    if (event.keyCode === 13) {
        event.preventDefault();
        i++;
        barCode = $("#qrcode").val();

        if(i == 1) {
            if(barCode.trim() != ""){                
                ubicazione = barCode;
                $.get({
                    url: "./ws/Interrogazione.php?codUbicazione=" + ubicazione,
                    dataType: 'json',
                    success: function(data, status) {
                        
                        let dati = data["data"];
                        if(dati[0] == null || dati.length === 0) {
                            showError("Ubicazione inesistente si prega di riprovare");
                            $("#qrcode").val('');
                            i=0;
                            return false;
                        }
                        $("#qrcode").attr('placeholder','MAGAZZINO DEST.');
                        let datiStampati = "";
                        idMagazzino = dati[0].ID_MAGAZZINO;
                        datiStampati += "<p class='pOsai'> Magazzino origine: <strong>"+dati[0].ID_MAGAZZINO+"</strong></p>";
                        datiStampati += "<p class='pOsai'> Codice ubicazione: <strong>"+dati[0].ID_UBICAZIONE+"</strong></p>";
                        timerOn = false;
                        $("#appendData").html(datiStampati);
                       getMagazziniAlternativi();
                    },
                    error: function(data, status){
                        $("#qrcode").attr('placeholder','UBICAZIONE ORIG.');
                        console.log('ERRORE -> Interrogazione', data);
                        showError("Ubicazione inesistente si prega di riprovare");
                        $("#qrcode").val('');
                        ubicazione = null;
                    }
                });            
                $("#qrcode").val("");

            } else {

                showError("Ubicazione inesistente si prega di riprovare");
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
            $("select").val(barCode);
        }
    }
});
function getMagazziniAlternativi(){
    $.get({
        url: "./ws/GetMagazziniAlternativi.php?idMagazzino="+ idMagazzino,
        dataType: 'json',
        success: function(data, status) { 
            let dati = data["data"];
            arrUbicazioniDest = dati;
            if(dati[0] == null || dati.length === 0) {
                showError("Ubicazione inesistente si prega di riprovare");
                $("#qrcode").val('');
                return false;
            }
            let datiStampati = "";
            datiStampati += "<select onchange='updateInputValue();'' onclick='timerOn = false' id='magazzinoDest' onfocusout='timerOn = true' class='form-control'>";    
            datiStampati += "<option value='-1'> Seleziona magazzino destinazione </option>";                            
            for(let i=0; i<dati.length; i++) {
                datiStampati += "<option value='"+dati[i]+"'>" + dati[i] + "</option>";                    
            } 
            datiStampati+= "</select>";
            $("#appendSelect").append(datiStampati);
        }, error: function(data, status) {
            console.log('ERRORE -> getMagazziniAlternativi', data);
        }
    });
}
function cambioMagazzinoUbicazione() {

    timerOn = true;
    let magazzinoDest = $("#magazzinoDest").val();

    $("#qrcode").attr("disabled", true);

    if(magazzinoDest == -1){
        showError("Magazzino di destinazione inesistente si prega di riprovare");
        $("#qrcode").val('');
        return false;
    }

    $.post({
        url: "./ws/CambioMagazzinoUbicazione.php?codUbicazione=" + ubicazione + "&codMagazzinoDest=" + $("select").val(),
        dataType: 'json',
        success: function(data, status) {
            showSuccessMsg("Ubicazione spostata correttamente nel magazzino " + magazzinoDest);
            location.href="./index.html";
        },
        error: function(data, status){
            console.log('ERRORE -> cambioMagazzinoUbicazione', data);
            showError("Errore interno")
            $("#qrcode").val('');
            $("#btnCambio").attr('disabled',true);
        }
    });
}

function updateInputValue() {
    console.log("sel", $("#magazzinoDest :selected").val() );
    if($("#magazzinoDest :selected").val() != -1){
        $("#qrcode").val($("#magazzinoDest :selected").text());
        $("#btnCambio").attr('disabled',false);
    }
    else {
        $("#qrcode").val('');
        $("#btnCambio").attr('disabled',true);
    }
}

function showError(msg) {
    // $("#error_message").html("<div class='alert alert-danger' role='alert'>"+msg+"</div>");
    // $("#error_message div").css("display","block");
    // setTimeout(function() {
    //     $("#error_message").html('');
    // }, 3000);
    alert(msg);
}

function showSuccessMsg(msg) {
    alert(msg);
}