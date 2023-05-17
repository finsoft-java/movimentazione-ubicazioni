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
                    let datiStampati = "<p class='pOsai'> Ubicazione: <strong style='text-transform:uppercase'>" + ubicazione + "</strong></p>";
                    for(let i = 0; i < Object.keys(dati).length; i++){      
                        datiStampati += getHtml(dati[i]);
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

function getHtml(x) {
    return "<p class='pOsai'>Articolo: <strong>" + x.ID_ARTICOLO + "</strong> | Quantita: <strong>" + x.QTA_GIAC_PRM + " " + x.R_UM_PRM_MAG + "</strong></p>"
        + "<p class='pOsai'>Disegno: <strong>" + x.DISEGNO + "</strong></p>"
        + "<p class='pOsai'>Descrizione: <strong>" + x.DESCRIZIONE + "</strong></p>"
        + "<hr/>";
}

function showSuccessMsg(msg) {
    $('#success_message').text(msg);
    $('#success_message').show();
    setTimeout(() => {
        $('#success_message').hide();
      }, "1000");
}

function showError(data) {
    const err = typeof data === 'string' ? data :
                data.responseJSON && data.responseJSON.error && data.responseJSON.error.value.length > 0 ? data.responseJSON.error.value :
                "Errore interno";
    alert(err);
}

function svuotaUbicazione() {
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

    $("#appendData").html('');
    $("#qrcode").attr('disabled', false);
}