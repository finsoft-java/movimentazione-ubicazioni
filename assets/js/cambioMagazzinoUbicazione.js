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
                    console.log(data);
                    let datiStampati = "";
                        datiStampati += "<p class='pOsai'> Magazzino: <strong>"+"dati[0].ID_MAGAZZINO"+"</strong></p>";
                        datiStampati += "<p class='pOsai'> Codice ubicazione: <strong>"+"dati[0].COD_UBICAZIONE"+"</strong></p>";
                        timerOn = false;
                        datiStampati += "<select class=\"form-control\"><option>Mag 1</option><option>Mag 2</option><option>Mag 3</option></select>"                     
                    $("#appendData").html(datiStampati);
                }
            });
        }
        // if(i == 2) {
        //     sessionStorage.setItem('ubicazione-destinazione', barCode);
        //     $("#btnTrasferimento").attr('disabled',false);
        //     $("#magazzinoDest").html("<p class='pOsai'> Magazzino destinazione: <strong>" + barCode + " </strong> </p>");
        // }
    }
});

function cambioMagazzinoUbicazione() {
    $("#qrcode").attr("disabled", true);
    $.post({
        url: "./ws/CambioMagazzinoUbicazione.php?codUbicazione=" + sessionStorage.getItem('ubicazione') + "&codUbicazioneDest=" + sessionStorage.getItem('ubicazione-destinazione'),
        dataType: 'json',
        success: function(data, status) {
            $("#magazzinoDest").append("<div style='display: block' class='alert alert-success' role='alert'> Cambio avvenuto con successo al magazzino <strong>"+sessionStorage.getItem('ubicazione-destinazione')+"</strong></div>");
        }
    });
}