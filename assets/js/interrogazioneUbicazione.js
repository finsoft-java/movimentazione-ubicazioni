

//esempio AAAAA;UBI-A7-003;xxx;1;pippo
$(document).ready(function(){
    disabilitaLettoreBadge();
    var user = sessionStorage.getItem('user');
    if (user) {
        $("#user_box p").append(user);
        $("#logout").show();
        scheduleLogout();
    }
    $(".focus").removeAttr("disabled");
    $(".focus").focus();
    if (sessionStorage.getItem('barCode')) {
        barCode = sessionStorage.getItem('barCode');
        chiama_ws_ubicazione();
    }
});

document.getElementById("qrcode").addEventListener("keyup", function(event) {
// Number 13 is the "Enter" key on the keyboard
    if (event.keyCode === 13) {
        event.preventDefault();
        scheduleLogout();
        barCode = $("#qrcode").val();
        sessionStorage.setItem('barCode', barCode);
        chiama_ws_ubicazione();
    }
});

setInterval(function() {
    console.log("Focusing interrogazione");
    $("#qrcode").get(0).focus();
}, 1000);

$(document).on("click", "#annullaEsaurimento", annulla);

function annulla() {
    $("#qrcode").val("");
    $("#qrcode").removeAttr("disabled");
    $("#qrcode").focus();
    $("#dettagli").html("");
    $("#messaggio").html("");
    $("#articoloEsaurito").attr("disabled", true); 
    $("#articoloEsaurito").css("display", "none"); 
    $("#annullaEsaurimento").css("display", "none");
    barCode = null;
    sessionStorage.removeItem('barCode');
}

$(document).on("click", "#articoloEsaurito", chiama_ws_esaurimento);

$(document).on("click", "#login", function() {
    require_login("segnalazione-esaurimento.html");
});

var barCode = null;
var codArticolo;
var codUbicazione;

function chiama_ws_ubicazione() {
    /*
    1)decodificaBarCode
    2)UbicazioniArticoli - COD_UBICAZIONE E COD_ARTICOLO 
    3)UbicazioniAlternative - COD_UBICAZIONE E COD_ARTICOLO (da chiamare solo se esaurimento è a YES nel punto 2) 
    */
    $.get({
        url: BASE_HREF + "/ws/DecodificaBarcode.php?barcode=" + barCode,
        dataType: 'json',
        headers: {
            'Authorization': 'Bearer ' + sessionStorage.getItem('token')
        },
        success: function(data, status) {
            codUbicazione = data.value.COD_UBICAZIONE;
            codArticolo = data.value.COD_ARTICOLO;
            $.get({
                url: BASE_HREF + "/ws/UbicazioniArticoli.php?COD_UBICAZIONE=" + data.value.COD_UBICAZIONE + '&COD_ARTICOLO='+ data.value.COD_ARTICOLO,
                dataType: 'json',
                headers: {
                    'Authorization': 'Bearer ' + sessionStorage.getItem('token')
                },
                success: function(data, status) {
                    console.log('data - UbicazioniArticoli');
                    console.log(data);
                    $("#dettagli").html(`Articolo <b>${data.value.COD_ARTICOLO}</b> <br/>${data.value.DESCRIZIONE}<br/>Quantità prevista <b>${data.value.QUANTITA_PREVISTA}</b>`);
                    if (data.value.SEGNALAZIONE_ESAURIMENTO == 'N') {
                        scheduleAnnulla(30);
                        if (sessionStorage.getItem('user') != null) {
                            $("#messaggio").html("Stai per dichiarare l'esaurimento di questa ubicazione. Confermi?");
                            $("#articoloEsaurito").removeAttr("disabled");            
                            $("#annullaEsaurimento").css("display","block");
                            $("#articoloEsaurito").css("display", "block");    
                        } else {
                            $("#messaggio").html("Per dichiarare l'esaurimento di un prodotto occorre il login.");
                            $("#login").css("display", "block");              
                        }
                    } else {
                        //chiamo ubicazioniAlternative e faccio vedere solo il codice ubicazione
                        $("#messaggio").html("Esiste già una segnalazione di esaurimento per questa ubicazione.");
                        $("#annullaEsaurimento").css("display","block");
                        $("#articoloEsaurito").css("display", "none");
                        scheduleAnnulla(15);
                    }
                    $("#qrcode").val("");
                    $("#qrcode").removeAttr("disabled");
                    $("#qrcode").focus();
                    beep();    


                    $.get({
                        url: BASE_HREF + "/ws/UbicazioniAlternative.php?COD_UBICAZIONE=" + data.value.COD_UBICAZIONE + '&COD_ARTICOLO='+ data.value.COD_ARTICOLO,
                        dataType: 'json',
                        headers: {
                            'Authorization': 'Bearer ' + sessionStorage.getItem('token')
                        },
                        success: function(data, status) {

                            console.log('data - UbicazioniAlternative');
                            console.log(data);
                            let ubicazioniAlternative = '';
                            for(let i = 0; i < data.count; i++) {
                                ubicazioniAlternative += data.data[i].COD_UBICAZIONE;
                                if(i > 0 && i < data.count) {
                                    ubicazioniAlternative += ', ';
                                }
                            }
                            console.log(ubicazioniAlternative);
                            $("#dettagli").append("<br/>Ubicazioni alternative: " + ubicazioniAlternative);
                        }
                    });                
                },
                error: function (xhr, ajaxOptions, thrownError) {
                    console.log(xhr);
                    if (xhr.responseJSON && xhr.responseJSON.error && xhr.responseJSON.error.value) {
                        show_error(xhr.responseJSON.error.value);
                    } else if (xhr.responseText) {
                        show_error(xhr.responseText);
                    } else {
                        show_error("Network error");
                    }
                    erroreBeep();
                    $("#qrcode").val("");
                    $("#qrcode").removeAttr("disabled");
                    $("#qrcode").focus();
                }
            });
        },
        error: function (xhr, ajaxOptions, thrownError) {
            console.log(xhr);
            if (xhr.responseJSON && xhr.responseJSON.error && xhr.responseJSON.error.value) {
                show_error(xhr.responseJSON.error.value);
            } else if (xhr.responseText) {
                show_error(xhr.responseText);
            } else {
                show_error("Network error");
            }
            erroreBeep();
            $("#qrcode").val("");
            $("#qrcode").removeAttr("disabled");
            $("#qrcode").focus();
        }
    });
    hide_errors();
    $("#qrcode").attr("disabled", true);
}

function chiama_ws_esaurimento() {
    hide_errors();
    $("#qrcode").attr("disabled", true);
    $("#articoloEsaurito").css("display", "none"); 
    $("#annullaEsaurimento").css("display", "none");
    $("#messaggio").html("Segnalazione in corso...");
    $.post({
        url: BASE_HREF + "/ws/SegnalazioneEsaurimento.php",
        headers: {
            'Authorization': 'Bearer ' + sessionStorage.getItem('token')
        },
        data:{
            COD_UBICAZIONE: codUbicazione,
            COD_ARTICOLO: codArticolo
        },
        success: function(data, status) {
            console.log(data);
            $("#messaggio").html("Segnalazione effettuata.");
            $("#qrcode").val("");
            $("#qrcode").removeAttr("disabled");
            $("#qrcode").focus();
            $("#dettagli").html("");
            $("#messaggio").html("");
            $("#articoloEsaurito").attr("disabled", true);
            $("#articoloEsaurito").css("display", "none");
            $("#annullaEsaurimento").css("display", "none");
            sessionStorage.removeItem('barCode');
            barCode = null;
        },
        error: function (xhr, ajaxOptions, thrownError) {
            console.log(xhr);
            if (xhr.responseJSON && xhr.responseJSON.error && xhr.responseJSON.error.value) {
                show_error(xhr.responseJSON.error.value);
            } else if (xhr.responseText) {
                show_error(xhr.responseText);
            } else {
                show_error("Network error");
            }
            $("#qrcode").val("");
            $("#qrcode").removeAttr("disabled");
            $("#qrcode").focus();
        }
    });
}

var timeout = null;

function scheduleLogout() {
    if (timeout != null) clearTimeout(timeout);
    timeout = setTimeout(function() {
        console.log("Logout 60sec timeout");
        logout();
    }, 60000);
}

function logout(){
    //localStorage.clear();
    sessionStorage.clear();
    window.location.reload();
}

var oldbarCode = null;
var tmrAnnulla = null;

function scheduleAnnulla(sec) {
    oldbarCode = barCode;
    if (tmrAnnulla != null) clearTimeout(tmrAnnulla);
    tmrAnnulla = setTimeout(function(){
        if (!$('#qrcode').val() || $('#qrcode').val() == oldbarCode) {
            console.log("timeout, operazione annullata");
            annulla();
        }
    }, sec*1000);
}
