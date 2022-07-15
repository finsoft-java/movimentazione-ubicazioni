let timerOn = true;

$(document).ready(function(){
    $(".focus").focus();
    setInterval(function() {
        if(timerOn) {
            console.log("Focusing esaurimento");
            $("#qrcode").get(0).focus();
        }
    }, 1000);
});

let i = 0;
let arrUbicazioniDest = [];
let ubicazione;
let ubicazioneDest;
document.getElementById("qrcode").addEventListener("keyup", function(event) {
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
                        
                        $("#error_message").html("");
                        $("#error_message div").css("display","none");
                        let dati = data["data"];
                        if(dati[0] == null || dati.length === 0) {                    
                            $("#error_message").html("<div class='alert alert-danger' role='alert'>Ubicazione "+ubicazione+" inesistente si prega di riprovare.</div>");
                            $("#error_message div").css("display","block");
                            $("#qrcode").val('');
                            i=0;
                            return false;
                        }
                        $("#qrcode").attr('placeholder','UBICAZIONE DEST.');
                        let datiStampati = "";
                        datiStampati += "<p class='pOsai'> Magazzino: <strong>"+dati[0].ID_MAGAZZINO+"</strong></p>";
                        datiStampati += "<p class='pOsai'> Codice ubicazione: <strong>"+dati[0].ID_UBICAZIONE+"</strong></p>";
                        datiStampati += "<p class='pOsai'> Magazzino destinazione: </p>";
                        timerOn = false;
                        $("#appendData").html(datiStampati);
                       getMagazziniAlternativi();
                    },
                    error: function(data, status){
                        $("#qrcode").attr('placeholder','UBICAZIONE ORIG.');
                        console.log('ERRORE -> Interrogazione', data);
                        $("#error_message").html("<div class='alert alert-danger' role='alert'>Ubicazione inesistente si prega di riprovare.</div>");
                        $("#error_message div").css("display","block");
                        $("#qrcode").val('');
                        ubicazione = null;
                    }
                });            
                $("#qrcode").val("");

            } else {

                $("#error_message").html("<div class='alert alert-danger' role='alert'>Ubicazione inesistente si prega di riprovare.</div>");
                $("#error_message div").css("display","block");
                $("#qrcode").val('');
                i=0;
                return false;

            }
        }
        
        if(i == 2) {
            
            if(barCode.trim() != ""){
                ubicazioneDest = barCode; 
            } else {
                $("#qrcode").attr('placeholder','UBICAZIONE DEST.');
                $("#error_message").html("<div class='alert alert-danger' role='alert'>Magazzino di destinazione inesistente si prega di riprovare.</div>");
                $("#error_message div").css("display","block");
                $("#qrcode").val('');
                i=1;
                return false;
            }

            if(!arrUbicazioniDest.includes(barCode)) {         
                $("#qrcode").attr('placeholder','UBICAZIONE DEST.');                       
                $("#error_message").html("<div class='alert alert-danger' role='alert'>Magazzino di destinazione non valido</div>");
                $("#error_message div").css("display","block");
                $("#qrcode").val('');
                ubicazioneDest= null;
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
        url: "./ws/GetMagazziniAlternativi.php?codUbicazione="+ ubicazione,
        dataType: 'json',
        success: function(data, status) { 
            $("#error_message").html("");
            $("#error_message div").css("display","none");
            let dati = data["data"];
            arrUbicazioniDest = dati;
            if(dati[0] == null || dati.length === 0) {                    
                $("#error_message").html("<div class='alert alert-danger' role='alert'>Ubicazione inesistente si prega di riprovare.</div>");
                $("#error_message div").css("display","block");
                $("#qrcode").val('');
                return false;
            }
            let datiStampati = "";
            datiStampati += "<select onclick='timerOn = false' id='ubicazioneDest' onfocusout='timerOn = true' class='form-control'>";    
                datiStampati += "<option value='-1'> Seleziona un magazzino </option>";                            
            for(let i=0; i<dati.length; i++) {
                datiStampati += "<option value='"+dati[i]+"'>" + dati[i] + "</option>";                    
            } 
            datiStampati+= "</select>";
            $("#btnCambio").attr('disabled',false);
            $("#appendData").append(datiStampati);
        }, error: function(data, status) {
            console.log('ERRORE -> getMagazziniAlternativi', data);
        }
    });
}
function cambioMagazzinoUbicazione() {

    timerOn = true;
    let magazzinoDest = $("#ubicazioneDest").val();

    $("#qrcode").attr("disabled", true);

    if(magazzinoDest == -1){
        $("#error_message").html("<div class='alert alert-danger' role='alert'>Magazzino di destinazione inesistente si prega di riprovare.</div>");
        $("#error_message div").css("display","block");
        $("#qrcode").val('');
        return false;
    }

    $.post({
        url: "./ws/CambioMagazzinoUbicazione.php?codUbicazione=" + ubicazione + "&codMagazzinoDest=" + $("select").val(),
        dataType: 'json',
        success: function(data, status) {
            $("#error_message").html("");
            $("#error_message div").css("display","none");
            $("#magazzinoDest").append("<div style='display: block' class='alert alert-success' role='alert'> Ubicazione spostata correttamente nel magazzino <strong>"+magazzinoDest+"</strong></div>");
            setTimeout(function() {
                location.href="./index.html";
            }, 5000);
        }, error: function(data, status) {
            console.log('ERRORE -> getMagazziniAlternativi', data);
        }
    });
}