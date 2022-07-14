let timerOn = true;

$(document).ready(function(){
    $(".focus").focus();
    setInterval(function() {
        if(timerOn) {
           console.log("Focusing esaurimento");
            $("#qrcode").get(0).focus();
        }
        else console.log('a')
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
            $("#qrcode").val("");
        }
        if(i == 2) {
            sessionStorage.setItem('articolo', barCode);                       
            $.get({
                url: "./ws/Interrogazione.php?codUbicazione=" + sessionStorage.getItem('ubicazione') + "&codArticolo=" + sessionStorage.getItem('articolo'),
                dataType: 'json',
                success: function(data, status) {
                    let dati = data["data"];
                    if(dati == null) {                    
                        $("#error_message").html("<div class='alert alert-danger' role='alert'>Ubicazione inesistente si prega di riprovare.</div>");
                        $("#error_message div").css("display","block");
                        $("#qrcode").val('');
                        return false;
                    }
                    console.log(data);
                    let datiStampati = "";
                        datiStampati += "<p class='pOsai'> Magazzino: <strong>"+dati[0].ID_MAGAZZINO+"</strong></p>";
                        datiStampati += "<p class='pOsai'> Articolo: <strong>"+dati[0].ID_ARTICOLO+"</strong>";
                        datiStampati += "<p class='pOsai'> Descrizione: <strong>"+dati[0].DESCRIZIONE+"</strong> </p>";
                        datiStampati += "<p class='pOsai'> Quantità da trasferire: <input onclick='timerOn = false' onblur='timerOn = true'  id='qty' class='inputOsai' type='number' value='1' min='1' max='" + dati[0].QTA_GIAC_PRM + "'/> </p>";
                        datiStampati += "<p class='pOsai' style='margin:0px;'> Quantita Totale: <strong>"+dati[0].QTA_GIAC_PRM+"</strong> </p>";                         
                    $("#appendData").html(datiStampati);
                }
            });
            $("#qrcode").val("");
        }
        if(i == 3) {
            sessionStorage.setItem('ubicazione-destinazione', barCode);
            $("#btnTrasferimento").attr('disabled',false);
            $("#magazzinoDest").html("<p class='pOsai'> Magazzino destinazione: <strong>" + barCode + " </strong> </p>");
            $("#qrcode").val("");
        }
    }
});

function trasferimentoArticoli() {
    $("#qrcode").attr("disabled", true);
    $.post({
        url: "./ws/TrasferimentoArticoli.php?codUbicazione=" + sessionStorage.getItem('ubicazione') + "&codArticolo=" + sessionStorage.getItem('articolo')+ "&qty=" + $("#qty").val() + "&codUbicazioneDest=" + sessionStorage.getItem('ubicazione-destinazione'),
        dataType: 'json',
        success: function(data, status) {
            $("#magazzinoDest").append("<div style='display: block' class='alert alert-success' role='alert'> Trasferimento avvenuto con successo all\'ubicazione <strong>"+sessionStorage.getItem('ubicazione-destinazione')+"</strong></div>");
        }
    });
}