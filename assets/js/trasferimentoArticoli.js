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
let ubicazione;
let articolo;
let ubicazioneDest;

document.getElementById("qrcode").addEventListener("keyup", function(event) {
    if (event.keyCode === 13) {
        event.preventDefault();
        i++;
        barCode = $("#qrcode").val();
        if(i == 1) {
            if(barCode.trim() != ""){       
                ubicazione =  barCode;
                $("#qrcode").val("").attr('placeholder','ARTICOLO');
            } else {
                $("#error_message").html("<div class='alert alert-danger' role='alert'>Ubicazione inesistente o vuota si prega di riprovare.</div>");
                $("#error_message div").css("display","block");
                i=0;
                return false;
            }
        }
        if(i == 2) {             
            if(barCode.trim() != ""){       
                articolo = barCode;
                $("#qrcode").val("").attr('placeholder','UBICAZIONE DESTINAZIONE');
            } else {
                $("#error_message").html("<div class='alert alert-danger' role='alert'>Articolo inesistente si prega di riprovare.</div>");
                $("#error_message div").css("display","block");
                $("#qrcode").val("").attr('placeholder','ARTICOLO');
                i=1;
                return false;
            }
            $.get({
                url: "./ws/Interrogazione.php?codUbicazione=" + ubicazione + "&codArticolo=" +articolo,
                dataType: 'json',
                success: function(data, status) {
                    let dati = data["data"];
                    if(dati == null || dati.length === 0) {                    
                        $("#error_message").html("<div class='alert alert-danger' role='alert'>Articolo inesistente si prega di riprovare.</div>");
                        $("#error_message div").css("display","block");
                        $("#qrcode").val("").attr('placeholder','ARTICOLO');
                        i=1;
                        return false;
                    }
                    let datiStampati = "";
                        datiStampati += "<p class='pOsai'> Magazzino: <strong>"+dati[0].ID_MAGAZZINO+"</strong></p>";
                        datiStampati += "<p class='pOsai'> Articolo: <strong>"+dati[0].ID_ARTICOLO+"</strong>";
                        datiStampati += "<p class='pOsai'> Descrizione: <strong>"+dati[0].DESCRIZIONE+"</strong> </p>";
                        datiStampati += "<p class='pOsai'> Quantità da trasferire: <input onclick='timerOn = false' onblur='timerOn = true'  id='qty' class='inputOsai' type='number' value='1' min='1' max='" + dati[0].QTA_GIAC_PRM + "'/> </p>";
                        datiStampati += "<p class='pOsai' style='margin:0px;'> Quantita Totale: <strong>"+dati[0].QTA_GIAC_PRM+"</strong> </p>";                         
                    $("#appendData").html(datiStampati);
                },
                error: function(data, status){
                    console.log("ERRORE in i == 2 trasferimentoArticoli", data);
                    $("#error_message").html("<div class='alert alert-danger' role='alert'>Errore interno</div>");
                    $("#error_message div").css("display","block");
                    $("#qrcode").val('');
                    i=1;
                }
            });
            $("#qrcode").val("");
        }
        if(i == 3) {
            ubicazioneDest = barCode;
            $("#qrcode").val("").attr('placeholder','UBICAZIONE').attr('disabled',true);
            $("#btnTrasferimento").attr('disabled',false);
            setTimeout(function() {
                $("#magazzinoDest").html("<p class='pOsai'> Ubicazione destinazione: <strong>" + barCode + " </strong> </p>");
            }, 2000);
           
        }
    }
});

function trasferimentoArticoli() {
    $("#qrcode").attr("disabled", true);
    $.post({
        url: "./ws/TrasferimentoArticoli.php?codUbicazione=" + ubicazione + "&codArticolo=" + articolo+ "&qty=" + $("#qty").val() + "&codUbicazioneDest=" +ubicazioneDest,
        dataType: 'json',
        success: function(data, status) {
            $("#magazzinoDest").append("<div style='display: block' class='alert alert-success' role='alert'> Trasferimento avvenuto con successo all\'ubicazione <strong>"+ubicazioneDest+"</strong></div>");
            setTimeout(function() {
                location.href="./index.html";
            }, 5000);
        },
        error: function(data, status){
            console.log("ERRORE in trasferimentoArticoli", data);
            $("#error_message").html("<div class='alert alert-danger' role='alert'>Errore interno.</div>");
            $("#error_message div").css("display","block");
            $("#qrcode").val('');
        }
    });
}