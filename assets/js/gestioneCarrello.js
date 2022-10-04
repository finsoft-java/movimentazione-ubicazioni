let timerOn = true;
let operazioneCarrello;
let ubicazioni = [];
let magazzinoDest;
let codCarrello;
let arrUbicazioniDest = [];
let contatoreFasi = 0;
$(document).ready(function(){
    $(".focus").focus();
    setInterval(function() {
        console.log(timerOn);
        if(timerOn) {
            console.log("Focusing");
            $("#qrcode").get(0).focus();
        }
    }, 1000);
});

let ubicazione;

function disassocia() {
    $("#qrcode").prop("disabled",false);
    timerOn = true;
    $("#qrcode").attr("placeholder","COD UBICAZIONE");
    $(".listaOsai").css("display","none");
    $("#btnAssocia").css("display","none");
    $("#btnDissocia").css("display","");
    $("#bottoniStep1").css("display","none");
    $("#bottoniStep2").css("display","");
    $(".titleOsai").html("Disassociazione dal carrello");
    operazioneCarrello = "disassocia";
} 

function associa() {
    /*
    $("#qrcode").prop("disabled",true).css("display","none");
    $("#qrcode_ubi").prop("disabled",false).css("display","block");
    $("#qrcode_ubi").focus();
    setInterval(function() {
        console.log("Focusing");
        $("#qrcode_ubi").get(0).focus();
    }, 1000);
    */
    $("#qrcode").prop("disabled",false);
    timerOn = true;
    $("#qrcode").attr("placeholder","COD UBICAZIONE");
    $(".listaOsai").css("display","none");
    $("#btnAssocia").css("display","");
    $("#btnDissocia").css("display","none");
    $("#bottoniStep1").css("display","none");
    $("#bottoniStep2").css("display","");
    $(".titleOsai").html("Associazione al carrello");
    operazioneCarrello = "associa";
}

function trasferisciCarrello() {
    
    /*
    $("#qrcode").prop("disabled",true).css("display","none");
    $("#qrcode_ubi").prop("disabled",false).css("display","block").attr("placeholder","MAGAZZINO");
    $("#qrcode_ubi").focus();
    setInterval(function() {
        console.log("Focusing");
        $("#qrcode_ubi").get(0).focus();
    }, 1000);
    */
    $("#qrcode").prop("disabled",false);
    timerOn = true;
    
    $("#qrcode").attr("placeholder","COD MAGAZZINO");
    $(".listaOsai").css("display","none");
    $("#btnAssocia").css("display","none");
    $("#btnDissocia").css("display","none");
    $("#btnTrasferisci").css("display","");    
    $("#bottoniStep1").css("display","none");
    $("#bottoniStep2").css("display","");
    $(".titleOsai").html("Trasferisci carrello");
    operazioneCarrello = "trasferisci";
    $.get({
        url: "./ws/GetMagazziniAlternativi.php?idMagazzino=",
        dataType: 'json',
        headers: {
            'Authorization': 'Bearer ' + sessionStorage.getItem('token')
        },
        success: function(data, status) {
            let dati = data["data"];
            arrUbicazioniDest = dati;
            if(dati[0] == null || dati.length === 0) {
                showError("Errore interno nel reperire i magazzini");
                $("#qrcode").val('');
                return false;
            }
            let datiStampati = "";
            datiStampati += "<select onchange=\"$('#btnTrasferisci').removeAttr('disabled')\" onclick='timerOn = false' id='magazzinoDest' onfocusout='timerOn = true' class='form-control'>";    
            datiStampati += "<option value='-1'> Seleziona magazzino destinazione </option>";                            
            for(let i=0; i<dati.length; i++) {
                datiStampati += "<option value='"+dati[i]+"'>" + dati[i] + "</option>";                    
            } 
            datiStampati+= "</select>";
            setTimeout(function() {
                    console.log("appendSelect");
                    $("#appendSelect").html(datiStampati);
                    $("#qrcode").val("");
            }, 500);
        },
        error: function(data, status){
            showError(data);
            $(".listaOsai").html("");
            $(".btnCarrello").css('display','none');
            $("#qrcode").val("").attr("placeholder","CODICE CARRELLO");;
        }
    });
} 

document.getElementById("qrcode").addEventListener("keyup", function(event) {
    this.value = this.value.toUpperCase();

    if (event.keyCode === 13) {
        value = $("#qrcode").val();
        console.log(contatoreFasi);
        if(contatoreFasi == 0){
            $.get({
                url: "./ws/Carrelli.php?codCarrello=" + value.trim(),
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
                    $("#qrcode").prop("disabled",true);
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
            contatoreFasi = contatoreFasi+1;
        } else {

            $.get({
                url: "./ws/GetUbicazione.php?codUbicazione=" + value.trim(),
                dataType: 'json',
                headers: {
                    'Authorization': 'Bearer ' + sessionStorage.getItem('token')
                },
                success: function(data, status) {
    
                    const dati = data.value;
                    ubicazioni.push(dati.ID_UBICAZIONE);
                    console.log("aggiungo ubicazione ", ubicazioni);
                    let datiStampati = ""; 
                    
                    $("#appendData").append(datiStampati);
                    if(operazioneCarrello == "associa"){
                        datiStampati += "<p class='pOsai'> Ubicazione: <strong>"+dati.ID_UBICAZIONE+"</strong></p>";
                        datiStampati += "<p class='pOsai'> Magazzino: <strong>"+dati.ID_MAGAZZINO+"</strong></p><hr/>";
                        $("#btnAssocia").attr("disabled",false);
                        $("#btnDissocia").attr("disabled",true);
                    } else if(operazioneCarrello == 'disassocia'){
                        datiStampati += "<p class='pOsai'> Ubicazione: <strong>"+dati.ID_UBICAZIONE+"</strong></p>";
                        datiStampati += "<p class='pOsai'> Magazzino: <strong>"+dati.ID_MAGAZZINO+"</strong></p><hr/>";
                        $("#btnAssocia").attr("disabled",true);
                        $("#btnDissocia").attr("disabled",false);
                    } else if(operazioneCarrello == 'trasferisci'){
                        $("#btnAssocia").attr("disabled",true);
                        $("#btnDissocia").attr("disabled",true);
                        console.log(ubicazione);
                        if(!arrUbicazioniDest.includes(ubicazione)) {         
                            showError("Magazzino non valido");
                            $("#qrcode").val('');
                            magazzinoDest = null;
                            return false;
                        }
                        $("#btnTrasferisci").attr("disabled",false);
                        magazzinoDest = ubicazione;
                        $("#magazzinoDest").val(ubicazione);
                        $("#magazzinoDest").trigger("change");
                        //compare select con tutti i magazzini                    
                    }
                    $("#appendData").append(datiStampati);
                    $("#qrcode").val('');
                },
                error: function(data, status){
                    showError(data);
                    $("#qrcode").val('');
                    ubicazione = null;
                    console.log("ubicazione = ",ubicazione);
                }
            });
        }
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
            url: "./ws/Associa.php?codCarrello="+codCarrello.trim()+"&codUbicazione=" + ub.trim(),
            dataType: 'json',
            headers: {
                'Authorization': 'Bearer ' + sessionStorage.getItem('token')
            },
            success: function(data, status) {
                showSuccessMsg("Ubicazione " + ub + " associata con successo al carrello "+codCarrello);
                //location.reload(true);
                $("#appendData").html("");
                ubicazioni = [];
                $("#btnAssocia").attr("disabled",true);
                $("#btnDissocia").attr("disabled",true);
                $("#btnTrasferisci").attr("disabled",true);
                $("#qrcode").val("");
            },
            error: function(data, status){            
                console.log("ERRORE in confermaAssociazione", data, status);
                showError(data);
                $("#qrcode").val('');
                $("#btnAssocia").attr("disabled",true);
                $("#btnDissocia").attr("disabled",true);
                $("#btnTrasferisci").attr("disabled",true);
                $("#appendData").html("");
                ubicazioni = [];
                console.log("ubicazione = ",ubicazioni);
            }
        });
    }
}

function confermaTrasferimento() {
    console.log('magazzinoDest',magazzinoDest);
    if(magazzinoDest == null){
        magazzinoDest = $("#magazzinoDest").val();
        console.log('magazzinoDest2 ',magazzinoDest);
    }
    $.post({
        url: "./ws/CambioMagazzinoCarrello.php?codCarrello="+codCarrello+"&codMagazzinoDest=" + magazzinoDest,
        dataType: 'json',
        headers: {
            'Authorization': 'Bearer ' + sessionStorage.getItem('token')
        },
        success: function(data, status) {
            showSuccessMsg("Trasferimento carrello " + codCarrello + " avvenuto con successo al magazzino "+magazzinoDest);
            location.reload(true);
        },
        error: function(data, status){            
            $("#qrcode").val("");
            console.log("ERRORE in confermaAssociazione", data, status);
            showError(data);
            ubicazioni = [];
            console.log("azzero ubicazione = ",ubicazioni);
        }
    });
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
                //location.reload(true);
                $("#appendData").html("");
                ubicazioni = [];
                console.log("azzero ubicazione successo = ",ubicazioni);
                $("#btnAssocia").attr("disabled",true);
                $("#btnDissocia").attr("disabled",true);
                $("#btnTrasferisci").attr("disabled",true);
                $("#qrcode").val("");
            },
            error: function(data, status){            
                console.log("ERRORE in confermaDisassociazione", data, status);
                showError(data);
                $("#qrcode").val('');
                $("#btnAssocia").attr("disabled",true);
                $("#btnDissocia").attr("disabled",true);
                $("#btnTrasferisci").attr("disabled",true);
                $("#appendData").html("");
                ubicazioni = [];
                console.log("azzero ubicazione errore = ",ubicazioni);
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