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
// 2 = e' stato sparato il secondo barcode (ubicazioneDest)
// 3 = e' stato sparato il secondo barcode (articolo)
let ubicazione;
let articolo;
let ubicazioneDest;

//fixme
document.getElementById("qrcode").addEventListener("keyup", function(event) {
    if (event.keyCode === 13) {
        event.preventDefault();
        i++;
        barCode = $("#qrcode").val();
        if(i == 1) {
            if(barCode.trim() != ""){       
                ubicazione =  barCode;
                $("#qrcode").val("").attr('placeholder','UBICAZIONE DESTINAZIONE');
            } else {
                showError("Ubicazione inesistente o vuota si prega di riprovare");
                i=0;
                return false;
            }
        }
        if(i == 2) {             
            if(barCode.trim() != ""){       
                ubicazioneDest = barCode;
                $("#qrcode").val("").attr('placeholder','ARTICOLO');
            } else {
                showError("Ubicazione destinazione inesistente si prega di riprovare");
                $("#qrcode").val("").attr('placeholder','UBICAZIONE DESTINAZIONE');
                i=1;
                return false;
            }
       }
        if(i == 3) {
            articolo = barCode;
            $("#qrcode").val("").attr('placeholder','UBICAZIONE DI PARTENZA').attr('disabled',true);
            $("#btnTrasferimento").attr('disabled',false);
            $.get({
                url: "./ws/Interrogazione.php?codUbicazione=" + ubicazione + "&codArticolo=" +articolo,
                dataType: 'json',
                success: function(data, status) {
                    let dati = data["data"];
                    if(dati == null || dati.length === 0) {
                        showError("Articolo inesistente si prega di riprovare");
                        $("#qrcode").val("").attr('placeholder','ARTICOLO');
                        i=2;
                        return false;
                    }
                    let datiStampati = ""; 
                        datiStampati += "<p class='pOsai'> Magazzino: <strong>"+dati[0].ID_MAGAZZINO+"</strong></p>";
                        datiStampati += "<p class='pOsai'> Ubicazione di partenza: <strong>"+dati[0].ID_UBICAZIONE+"</strong></p>";
                        datiStampati += "<p class='pOsai'> Articolo: <strong>"+dati[0].ID_ARTICOLO+"</strong></p>";
                        datiStampati += "<p class='pOsai'> Disegno: <strong>"+dati[0].DISEGNO+"</strong> </p>";
                        datiStampati += "<p class='pOsai'> Descrizione: <strong>"+dati[0].DESCRIZIONE+"</strong> </p>";
                        datiStampati += "<div class='input-group inputDiv'><input type='number' class='form-control inputOsai'  onclick='timerOn = false' onblur='timerOn = true'  id='qty' class='inputOsai' value='1' min='1' max='" + dati[0].QTA_GIAC_PRM + "' placeholder='Quantità da trasferire' aria-label='Quantità da trasferire' aria-describedby='basic-addon2'>";
                        datiStampati += "<div class='input-group-append'><button class='btn btnAll' type='button' onClick='selezionaTutti("+dati[0].QTA_GIAC_PRM+")'> Tutti </button></div></div>";
                        datiStampati += "<p class='pOsai'> Quantita Totale: <strong>"+dati[0].QTA_GIAC_PRM+ " "+ dati[0].R_UM_PRM_MAG +"</strong> </p>";                         
                        datiStampati += "<p class='pOsai'> Ubicazione destinazione: <strong>" + ubicazioneDest + " </strong> </p>";
                        $("#appendData").html(datiStampati);
                },
                error: function(data, status){
                    console.log("ERRORE in i == 3 trasferimentoArticoli", data);
                    showError("Errore interno");
                    $("#qrcode").val('');
                    i=2;
                }
            });
            $("#qrcode").val("");
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
            showError("Errore interno");
            $("#qrcode").val('');
        }
    });
}

function selezionaTutti(maximum) {
    $(".inputOsai").val(maximum);
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