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

let ubicazione;
let ubicazioneDest;

document.getElementById("qrcode").addEventListener("keyup", function(event) {
    this.value = this.value.toUpperCase();
    if (event.keyCode === 13) {
        event.preventDefault();
        i++;
        barCode = $("#qrcode").val();
        if(i == 1) {
            if(barCode.trim() != ""){       
                ubicazione =  barCode;
                $.get({
                    url: "./ws/GetUbicazione.php?codUbicazione=" + ubicazione,
                    dataType: 'json',
                    headers: {
                        'Authorization': 'Bearer ' + sessionStorage.getItem('token')
                    },
                    success: function(data, status) {
                        const dati = data.value;
                        let datiStampati = ""; 
                        datiStampati += "<p class='pOsai'> Ubicazione di partenza: <strong>"+dati.ID_UBICAZIONE+"</strong></p>";
                        datiStampati += "<p class='pOsai'> Magazzino: <strong>"+dati.ID_MAGAZZINO+"</strong></p>";
                        $("#appendData").html(datiStampati);
                        $("#qrcode").val("").attr('placeholder','UBICAZIONE DESTINAZIONE');
                    },
                    error: function(data, status){
                        $("#qrcode").attr('placeholder','UBICAZIONE ORIGINE');
                        console.log('ERRORE -> Interrogazione', data);
                        showError(data);
                        $("#qrcode").val('');
                        ubicazione = null;
                        i = 0;
                        return false;
                    }
                });
            } else {
                showError("Digitare qualcosa nel campo ubicazione!");
                i=0;
                return false;
            }
        }
        if(i == 2) {             
            if(barCode.trim() != ""){     
                ubicazioneDest = barCode;
                $.get({
                    url: "./ws/GetUbicazione.php?codUbicazione=" + ubicazioneDest,
                    dataType: 'json',
                    headers: {
                        'Authorization': 'Bearer ' + sessionStorage.getItem('token')
                    },
                    success: function(data, status) {
                        const dati = data.value;
                        let datiStampati = ""; 
                        datiStampati += "<p class='pOsai'> Ubicazione dest.: <strong>"+dati.ID_UBICAZIONE+"</strong></p>";
                        datiStampati += "<p class='pOsai'> Magazzino destinazione: <strong>"+dati.ID_MAGAZZINO+"</strong></p>";
                        $("#appendData").append(datiStampati);
                        $("#qrcode").val("").attr('placeholder', 'UBICAZIONE DESTINAZIONE');
                        $("#btnTrasferimento").attr('disabled', false);
                        $("#qrcode").val('').attr('disabled', true);
                    },
                    error: function(data, status){
                        $("#qrcode").attr('placeholder','UBICAZIONE DESTINAZIONE');
                        console.log('ERRORE -> Interrogazione', data);
                        showError(data);
                        $("#qrcode").val('');
                        ubicazioneDest = null;
                        i = 1;
                        return false;
                    }
                });  
            } else {
                showError("Ubicazione destinazione inesistente si prega di riprovare");
                $("#qrcode").val("").attr('placeholder','UBICAZIONE DESTINAZIONE');
                i=1;
                return false;
            }
       }
    }
});

function trasferisciContenuto()  {
    const qrcode = $("#qrcode");
    qrcode.attr("disabled", true);
    
    $("#btnTrasferimento").attr('disabled',true);


    //TODO cambiare la post
    $.post({
        url: "./ws/TrasferimentoArticoli.php?codUbicazione=" + ubicazione + "&codArticolo=" + articolo+ "&qty=" + qty  + "&codUbicazioneDest=" +ubicazioneDest+ "&commessa=" + codCommessa,
        dataType: 'json',
        headers: {
            'Authorization': 'Bearer ' + sessionStorage.getItem('token')
        },
        success: function(data, status) {
            showSuccessMsg("Trasferimento avvenuto con successo \n (ubicazione di partenza: " + ubicazione + ", ubicazione dest.: " + ubicazioneDest + ", articolo: " + articolo + ", quantità: " + qty + ")");
        },
        error: function(data, status){
            console.log("ERRORE in trasferimentoArticoli", data);
            showError(data);
            qrcode.val('');
        }
    });
}

function showError(data) {
    const err = typeof data === 'string' ? data :
                data.responseJSON && data.responseJSON.error && data.responseJSON.error.value.length > 0 ? data.responseJSON.error.value :
                "Errore interno";
    alert(err);
}

function showSuccessMsg(msg) {
    alert(msg); 
    setTimeout(function() { 
        window.close();
    }, 1000);
}