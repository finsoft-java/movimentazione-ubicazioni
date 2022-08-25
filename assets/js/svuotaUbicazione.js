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

//TODO stampa articolo quantità e unità
let ubicazione;

document.getElementById("qrcode").addEventListener("keyup", function(event) {
    const qrcode = $("#qrcode");
    this.value = this.value.toUpperCase();
    if (event.keyCode === 13) {
        ubicazione = qrcode.val();
        
        // event.preventDefault();
        // i++;
        // if(i == 1) {
        //     if(qrcode.val().trim() != ""){       
        //         ubicazione =  qrcode.val();
        //         qrcode.val("").attr('placeholder','MAGAZZINO DESTINAZIONE');
        //     } else {
        //         showError("Ubicazione inesistente o vuota si prega di riprovare");
        //         i=0;
        //         return false;
        //     }
        // }
        // if(i == 2) {             
        //     if(qrcode.val().trim() != ""){       
        //         codMagazzinoDest = qrcode.val();
        //     } else {
        //         showError("Magazzino destinazione inesistente si prega di riprovare");
        //         qrcode.val("").attr('placeholder','MAGAZZINO DESTINAZIONE');
        //         i=1;
        //         return false;
        //     }
        //     qrcode.val("").attr('placeholder','UBICAZIONE').attr('disabled', true);
            $.get({
                url: "./ws/Interrogazione.php?codUbicazione=" + ubicazione,
                dataType: 'json',
                headers: {
                    'Authorization': 'Bearer ' + sessionStorage.getItem('token')
                },
                success: function(data, status) {
                    let dati = data["data"];
                    if(dati[0] == null || dati.length === 0) {   
                        showError("Ubicazione vuota");
                        $("#qrcode").val('');
                        return false;
                    }
                    let datiStampati = "<p class='text-center'> Ubicazione: <strong style='text-transform:uppercase'>"+ ubicazione +"</strong></p>";
                    for(let i = 0; i < Object.keys(dati).length; i++){      
                        datiStampati += "<p class='pOsai'>Articolo: <strong>"+dati[i].ID_ARTICOLO+"</strong> | Quantita: <strong>"+dati[i].QTA_GIAC_PRM+" "+ dati[i].R_UM_PRM_MAG +" </strong></p>";
                        datiStampati += "<p class='pOsai'>Disegno: <strong>"+dati[i].DISEGNO+"</strong> </p>";
                        datiStampati += "<p class='pOsai'>Descrizione: <strong>"+dati[i].DESCRIZIONE+"</strong> </p>";
                        datiStampati += "<hr/>";
                    }
                    $("#appendData").html(datiStampati);
                    timerOn = false;
                    $("#btnSvuota").attr('disabled', false);
                    qrcode.attr('disabled', true);
                },
                error: function(data, status){
                    showError(data);
                    qrcode.val('');
                    $("#appendData").html('');
                }
            });
            qrcode.val('');
       }
});

function showSuccessMsg(msg) {
    alert(msg);
}

function showError(data) {
    const err = typeof data === 'string' ? data :
                data.responseJSON && data.responseJSON.error && data.responseJSON.error.value.length > 0 ? data.responseJSON.error.value :
                "Errore interno";
    alert(err);
}

function svuotaUbicazione() {
    // $("#qrcode").val("").attr('placeholder','UBICAZIONE');
    $("#btnSvuota").attr('disabled', true);

    $.post({
        url: "./ws/SvuotaUbicazione.php?codUbicazione=" + ubicazione,
        dataType: 'json',
        headers: {
            'Authorization': 'Bearer ' + sessionStorage.getItem('token')
        },
        success: function(data, status) {
            showSuccessMsg("Ubicazione " + ubicazione + " svuotata con successo");
        },
        error: function(data, status){            
            console.log("ERRORE in svuotaUbicazione", data, status);
            showError(data);
            $("#qrcode").val('');
        }
    });
    // $("#btnSvuota").attr('disabled', false);
    $("#appendData").html('');
    $("#qrcode").attr('disabled', false);
}