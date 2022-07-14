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
document.getElementById("qrcode").addEventListener("keyup", function(event) {
    if (event.keyCode === 13) {
        event.preventDefault();
        i++;
        barCode = $("#qrcode").val();
        if(i == 1) {
            sessionStorage.setItem('ubicazione', barCode);                    
            $.get({
                url: "./ws/Interrogazione.php?codUbicazione=" + sessionStorage.getItem('ubicazione'),
                dataType: 'json',
                success: function(data, status) {
                    let dati = data["data"];
                    if(dati[0] == null) {                    
                        $("#error_message").html("<div class='alert alert-danger' role='alert'>Ubicazione inesistente si prega di riprovare.</div>");
                        $("#error_message div").css("display","block");
                        $("#qrcode").val('');
                        return false;
                    }
                    let datiStampati = "";
                        datiStampati += "<p class='pOsai'> Magazzino: <strong>"+dati[0].ID_MAGAZZINO+"</strong></p>";
                        datiStampati += "<p class='pOsai'> Codice ubicazione: <strong>"+dati[0].ID_UBICAZIONE+"</strong></p>";
                        timerOn = false;
                        $("#appendData").html(datiStampati);
                        $.get({
                            url: "./ws/GetMagazziniAlternativi.php?codUbicazione="+ sessionStorage.getItem('ubicazione'),
                            dataType: 'json',
                            success: function(data, status) { 
                                let dati = data["data"];
                                arrUbicazioniDest = dati;
                                if(dati[0] == null) {                    
                                    $("#error_message").html("<div class='alert alert-danger' role='alert'>Ubicazione inesistente si prega di riprovare.</div>");
                                    $("#error_message div").css("display","block");
                                    $("#qrcode").val('');
                                    return false;
                                }
                                let datiStampati = "";
                                datiStampati += "<select onclick='"+ (() => this.timerOn = false) +"' class=\"form-control\">";    
                                datiStampati += "<option value='-1'> Seleziona un magazzino </option>";                            
                                for(let i=0; i<dati.length; i++) {
                                    datiStampati += "<option value='"+dati[i]+"'>" + dati[i] + "</option>";                    
                                 } 
                                datiStampati+="</select>";
                                $("#magazzinoDest").html("<p class='pOsai'> Magazzino destinazione: <strong>" + barCode + " </strong> </p>");
                                $("#btnCambio").attr('disabled',false);
                                $("#appendData").append(datiStampati);
                            }
                        });
                    }
                });
                $("#qrcode").val("");
            }
            if(i == 2) {
            console.log(arrUbicazioniDest);
            sessionStorage.setItem('ubicazione-destinazione', barCode);
            if(!arrUbicazioniDest.includes(barCode)) {                    
                $("#error_message").html("<div class='alert alert-danger' role='alert'>Ubicazione non valida</div>");
                $("#error_message div").css("display","block");
                $("#qrcode").val('');
                return false;
            }
            $("#magazzinoDest").html("<p class='pOsai'> Magazzino destinazione: <strong>" + barCode + " </strong> </p>");
            $("select").val(barCode);
            $("#qrcode").val("");
        }
    }
});

function cambioMagazzinoUbicazione() {
    timerOn = true;
    let magazzinoDest = $("");
    $("#qrcode").attr("disabled", true);
    $.post({
        url: "./ws/CambioMagazzinoUbicazione.php?codUbicazione=" + sessionStorage.getItem('ubicazione') + "&codMagazzinoDest=" + $("select").val(),
        dataType: 'json',
        success: function(data, status) {
            $("#magazzinoDest").append("<div style='display: block' class='alert alert-success' role='alert'> Cambio avvenuto con successo al magazzino <strong>"+sessionStorage.getItem('ubicazione-destinazione')+"</strong></div>");
        }
    });
}