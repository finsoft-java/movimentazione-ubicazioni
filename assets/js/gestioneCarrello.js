let timerOn = true;
let operazioneCarrello;
let ubicazioni = [];
let codCarrello;

$(document).ready(function(){
    $(".focus").focus();
    setInterval(function() {
        if(timerOn) {
            console.log("Focusing");
            $("#qrcode").get(0).focus();
        }
    }, 1000);
});

let ubicazione;

document.getElementById("qrcode_ubi").addEventListener("keyup", function(event) {
    this.value = this.value.toUpperCase();
    
    console.log("listening to keyup")
    if (event.keyCode === 13) {
        ubicazione = $("#qrcode_ubi").val();
        $.get({
            url: "./ws/GetUbicazione.php?codUbicazione=" + ubicazione,
            dataType: 'json',
            headers: {
                'Authorization': 'Bearer ' + sessionStorage.getItem('token')
            },
            success: function(data, status) {
                const dati = data.value;
                ubicazioni.push(dati.ID_UBICAZIONE);
                console.log(ubicazioni);
                let datiStampati = ""; 
                datiStampati += "<p class='pOsai'> Ubicazione: <strong>"+dati.ID_UBICAZIONE+"</strong></p>";
                datiStampati += "<p class='pOsai'> Magazzino: <strong>"+dati.ID_MAGAZZINO+"</strong></p><hr/>";
                $("#appendData").append(datiStampati);
                if(operazioneCarrello == "associa"){
                    $("#btnAssocia").attr("disabled",false);
                    $("#btnDissocia").attr("disabled",true);
                } else {
                    $("#btnAssocia").attr("disabled",true);
                    $("#btnDissocia").attr("disabled",false);
                }
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
    }
});

function associa() {
    $("#qrcode").prop("disabled",true).css("display","none");
    $("#qrcode_ubi").prop("disabled",false).css("display","block");
    $(".listaOsai").css("display","none");
    $("#btnAssocia").css("display","");
    $("#btnDissocia").css("display","none");
    $("#bottoniStep1").css("display","none");
    $("#bottoniStep2").css("display","");
    $(".titleOsai").html("Associazione al carrello");
    operazioneCarrello = "associa";
}

function disassocia() {
    $("#qrcode").prop("disabled",true).css("display","none");
    $("#qrcode_ubi").prop("disabled",false).css("display","block");
    $(".listaOsai").css("display","none");
    $("#btnAssocia").css("display","none");
    $("#btnDissocia").css("display","");
    $("#bottoniStep1").css("display","none");
    $("#bottoniStep2").css("display","");
    $(".titleOsai").html("Disassociazione dal carrello");
    operazioneCarrello = "disassocia";
} 

document.getElementById("qrcode").addEventListener("keyup", function(event) {
    this.value = this.value.toUpperCase();
    console.log("listening to keyup")
    if (event.keyCode === 13) {
        value = $("#qrcode").val();
        $.get({
            url: "./ws/Carrelli.php?codCarrello=" + value,
            dataType: 'json',
            headers: {
                'Authorization': 'Bearer ' + sessionStorage.getItem('token')
            },
            success: function(data, status) {
                console.log(data);
                let dati = data["data"];
                let datiStampati = "";
                $("#codCarrello").html("<p>Cod Carrello: <strong>" + value + "</strong></p>");
                codCarrello = value;
                for(let i = 0; i < Object.keys(dati).length;i++) {
                    datiStampati += getHtmlArticolo(dati[i]);
                }
                $(".listaOsai").html(datiStampati);
                $(".btnCarrello").css('display','block');
                timerOn = false;
            },
            error: function(data, status){
                showError(data);
                $("#qrcode").val('');
                $(".listaOsai").html("");
                $(".btnCarrello").css('display','none');
            }
        });
        $("#qrcode").val("");
    }
});

function getHtmlArticolo(x) {
    return "<p>Ubicazione: <strong>" + x.R_UBICAZIONE + "</strong></p>"
        +  "<p>Descrizione: <strong>" + x.DESCR_RIDOTTA + "</strong></p>"
        +  "<hr/>";
}

function showError(data) {
    const err = typeof data === 'string' ? data :
                data.responseJSON && data.responseJSON.error && data.responseJSON.error.value.length > 0 ? data.responseJSON.error.value :
                "Errore interno";
    alert(err);
}
function confermaAssociazione() {
    let ub;
    for(let i=0; i < ubicazioni.length; i++){
        ub = ubicazioni[i];
        $.post({
            url: "./ws/Associa.php?codCarrello="+codCarrello+"&codUbicazione=" + ub,
            dataType: 'json',
            headers: {
                'Authorization': 'Bearer ' + sessionStorage.getItem('token')
            },
            success: function(data, status) {
                showSuccessMsg("Ubicazione " + ub + " associata con successo al carrello "+codCarrello);
                $("#appendData").html("");
                ubicazioni = [];
                $("#btnAssocia").attr("disabled",true);
                $("#btnDissocia").attr("disabled",true);
                $("#qrcode_ubi").val("");
            },
            error: function(data, status){            
                console.log("ERRORE in confermaAssociazione", data, status);
                showError(data);
                $("#qrcode").val('');
            }
        });
    }
}

function confermaDisassociazione() {
    let ub;
    for(let i=0; i < ubicazioni.length; i++){
        ub = ubicazioni[i];
        $.post({
            url: "./ws/Dissocia.php?codCarrello="+codCarrello+"&codUbicazione=" + ub,
            dataType: 'json',
            headers: {
                'Authorization': 'Bearer ' + sessionStorage.getItem('token')
            },
            success: function(data, status) {
                showSuccessMsg("Ubicazione " + ub + " disassociata con successo dal carrello "+codCarrello);
                $("#appendData").html("");
                ubicazioni = [];
                $("#btnAssocia").attr("disabled",true);
                $("#btnDissocia").attr("disabled",true);
                $("#qrcode_ubi").val("");
            },
            error: function(data, status){            
                console.log("ERRORE in confermaDisassociazione", data, status);
                showError(data);
                $("#qrcode").val('');
            }
        });
    }
}

function showSuccessMsg(msg) {
    alert(msg);
}



function showError(data) {
    const err = typeof data === 'string' ? data :
                data.responseJSON && data.responseJSON.error && data.responseJSON.error.value.length > 0 ? data.responseJSON.error.value :
                "Errore interno";
    alert(err);
}