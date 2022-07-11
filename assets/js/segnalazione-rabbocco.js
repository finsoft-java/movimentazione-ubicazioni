require_login("segnalazione-rabbocco.html", "magazziniere");

$(document).ready(function(){
    disabilitaLettoreBadge();
    scheduleLogout();
    if(localStorage.getItem('dati_rabbocco') != null){
        popolaDatiRabbocco();
        if(sessionStorage.getItem("user") != localStorage.getItem('utente_rabbocco')){
            show_error("Rabbocco non confermato dall'utente: "+localStorage.getItem('utente_rabbocco'));
        }        
        $("#confermaRabbocco").attr("disabled", false); 
        $("#annullaRabbocco").attr("disabled", false);
    }
});

document.getElementById("qrcode").addEventListener("keyup", function(event) {
// Number 13 is the "Enter" key on the keyboard
    if (event.keyCode === 13) {
        event.preventDefault();
        scheduleLogout();
        chiama_ws_ubicazione();
    }
});

setInterval(function() {
    console.log("Focusing rabbocco");
    $("#qrcode").get(0).focus();
}, 1000);

var ubicazioni = [];

$(document).on("click", "#annullaRabbocco", function() {
    init();
});

$(document).on("click", "#confermaRabbocco", chiama_ws_rabbocco);


function popolaDatiRabbocco(){
    var elencoUbicazioni = JSON.parse(localStorage.getItem('dati_rabbocco'));
    let lista = "";
    for(let i = 0; i < elencoUbicazioni.length; i++){
        lista += `<li style="width:100%;line-height: 38px;padding:10px 15px;border-bottom:1px solid #000;">Articolo <b>${elencoUbicazioni[i].COD_ARTICOLO}</b> ${elencoUbicazioni[i].DESCRIZIONE}<br/>Quantità prevista <b>${elencoUbicazioni[i].QUANTITA_PREVISTA}</b> <button class="btn btn-danger" style="float:right" onclick="elimina('${elencoUbicazioni[i].COD_UBICAZIONE}')"> Elimina </button></li>`;
    }
    $("#lista").append(lista);
}

function init() {
    ubicazioni = [];
    $("#lista").html("");
    abilita_qrcode();
    abilita_o_disabilita_bottoni();
}

function abilita_qrcode() {
    $("#qrcode").val("");
    $("#qrcode").removeAttr("disabled");
    $("#qrcode").focus();
}

function abilita_o_disabilita_bottoni() {
    if (ubicazioni.length > 0) {
        $("#confermaRabbocco").attr("disabled", false); 
        $("#annullaRabbocco").attr("disabled", false);
    } else {
        $("#confermaRabbocco").attr("disabled", true); 
        $("#annullaRabbocco").attr("disabled", true);
    }
}

function chiama_ws_ubicazione() {
    hide_errors();
    $("#qrcode").attr("disabled", true);
    $.get({
        url: BASE_HREF + "/ws/DecodificaBarcode.php?barcode=" + $("#qrcode").val(),
        dataType: 'json',
        headers: {
            'Authorization': 'Bearer ' + sessionStorage.getItem('token')
        },
        success: function(data, status) {
            //codUbicazione = data.value.COD_UBICAZIONE;
            //codArticolo = data.value.COD_ARTICOLO;
            $.get({
                url: BASE_HREF + "/ws/UbicazioniArticoli.php?COD_UBICAZIONE=" + data.value.COD_UBICAZIONE + '&COD_ARTICOLO='+ data.value.COD_ARTICOLO,
                dataType: 'json',
                headers: {
                    'Authorization': 'Bearer ' + sessionStorage.getItem('token')
                },
                success: function(data, status) {
                    var ubicazione = data.value;
                    console.log('ubicazione');
                    console.log(ubicazione);
                    if (ubicazione.SEGNALAZIONE_ESAURIMENTO == 'N') {
                        show_error("Questa ubicazione non risulta essere in sofferenza");
                        // lo faccio lo stesso o no?
                    }
                    if (non_duplicata(ubicazione)) {
                        ubicazioni.push(ubicazione);
                        $("#lista").append(`<li style="width:100%;line-height: 38px;padding:10px 15px;border-bottom:1px solid #000;">Articolo <b>${ubicazione.COD_ARTICOLO}</b> ${ubicazione.DESCRIZIONE}<br/>Quantità prevista <b>${ubicazione.QUANTITA_PREVISTA}</b> <button class="btn btn-danger" style="float:right" onclick="elimina('${ubicazione.COD_UBICAZIONE}')"> Elimina </button></li>`);
                        
                    }
                    sessionStorage.getItem('requiredRole');
                    localStorage.setItem('dati_rabbocco', JSON.stringify(ubicazioni));
                    localStorage.setItem('utente_rabbocco', sessionStorage.getItem("user"));
                    abilita_qrcode();
                    abilita_o_disabilita_bottoni();
                    beep();
                    document.querySelector(".centralpanel").scrollTop = document.querySelector(".centralpanel").scrollHeight;
                },
                error: function (xhr, ajaxOptions, thrownError) {console.log('errore');
                    console.log(xhr);
                    if (xhr.responseJSON && xhr.responseJSON.error && xhr.responseJSON.error.value) {
                        show_error(xhr.responseJSON.error.value);
                    } else if (xhr.responseText) {
                        show_error(xhr.responseText);
                    } else {
                        console.log(xhr);
                        show_error("Network error");
                    }
                    erroreBeep();
                    abilita_qrcode();
                    abilita_o_disabilita_bottoni();
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
                console.log(xhr);
                show_error("Network error");
            }
            erroreBeep();
            abilita_qrcode();
            abilita_o_disabilita_bottoni();
        }
    });
}

function beep() {
    var snd = new Audio("data:audio/wav;base64,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qJrx4KOQjahZxWbcZgztj2c49nKmkId44S71j0c8eV9yDK6uPRzx5X18eDvjvQ6yKo9ZSS6l//8elePK/Lf//IInrOF/FvDoADYAGBMGb7FtErm5MXMlmPAJQVgWta7Zx2go+8xJ0UiCb8LHHdftWyLJE0QIAIsI+UbXu67dZMjmgDGCGl1H+vpF4NSDckSIkk7Vd+sxEhBQMRU8j/12UIRhzSaUdQ+rQU5kGeFxm+hb1oh6pWWmv3uvmReDl0UnvtapVaIzo1jZbf/pD6ElLqSX+rUmOQNpJFa/r+sa4e/pBlAABoAAAAA3CUgShLdGIxsY7AUABPRrgCABdDuQ5GC7DqPQCgbbJUAoRSUj+NIEig0YfyWUho1VBBBA//uQZB4ABZx5zfMakeAAAAmwAAAAF5F3P0w9GtAAACfAAAAAwLhMDmAYWMgVEG1U0FIGCBgXBXAtfMH10000EEEEEECUBYln03TTTdNBDZopopYvrTTdNa325mImNg3TTPV9q3pmY0xoO6bv3r00y+IDGid/9aaaZTGMuj9mpu9Mpio1dXrr5HERTZSmqU36A3CumzN/9Robv/Xx4v9ijkSRSNLQhAWumap82WRSBUqXStV/YcS+XVLnSS+WLDroqArFkMEsAS+eWmrUzrO0oEmE40RlMZ5+ODIkAyKAGUwZ3mVKmcamcJnMW26MRPgUw6j+LkhyHGVGYjSUUKNpuJUQoOIAyDvEyG8S5yfK6dhZc0Tx1KI/gviKL6qvvFs1+bWtaz58uUNnryq6kt5RzOCkPWlVqVX2a/EEBUdU1KrXLf40GoiiFXK///qpoiDXrOgqDR38JB0bw7SoL+ZB9o1RCkQjQ2CBYZKd/+VJxZRRZlqSkKiws0WFxUyCwsKiMy7hUVFhIaCrNQsKkTIsLivwKKigsj8XYlwt/WKi2N4d//uQRCSAAjURNIHpMZBGYiaQPSYyAAABLAAAAAAAACWAAAAApUF/Mg+0aohSIRobBAsMlO//Kk4soosy1JSFRYWaLC4qZBYWFRGZdwqKiwkNBVmoWFSJkWFxX4FFRQWR+LsS4W/rFRb/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////VEFHAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAU291bmRib3kuZGUAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAMjAwNGh0dHA6Ly93d3cuc291bmRib3kuZGUAAAAAAAAAACU=");  
    snd.play();
}
function erroreBeep() {
    var snd = new Audio("data:audio/wav;base64,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Mf4V/ab82fyx/RH/5AACA0oFaQdUCasKcguYCzULXwpECe0HnQZOBTwEZgPbAosCZAJkAnICoALTAioDlAMqBOAEuwWkBpUHcgguCccJHQpICicK1wlJCY8Isge+Br0FxwTeAyQDmgJVAl8CtAJKAwcE3QSjBVoG3wY1B0IHDgeYBvgFQQWCBNcDMQOyAkEC+QHIAa4BrAG3AdQB/AEvAmgCrQLoAi4DZwOgA80D6wP/A/oD6gO+A4ADNAPNAmYC+gGYAVIBKgEqAUYBhgHOAS4CjQLnAjYDWANaAyUD3QJ4AhYCxAF9AU8BOwE1AUgBaQGHAbMByAHfAd8B2gHSAcYByAHMAeIB+wEfAjgCUgJYAlkCSwIzAhoC9gHfAccBwQHIAeYBDAI9AnICkwKyArsCtwKuApYCgQJjAkoCNwIkAiICGgIhAiMCLQIyAjkCPwJEAkkCTgJTAlUCWwJaAloCWAJPAkkCQAI0Ai4CIAIeAhUCFQIXAh0CJgIzAj4CSQJWAlsCYAJcAlUCQgIzAhUCAwLqAd8BzwHMAckBzQHVAdkB4wHkAegB5gHgAdsB0QHTAcsB1gHVAeMB5QHpAekB2wHWAb8BtwGlAaMBlwGiAZ8BsAG6AcEBzAHBAbUBmwF2AVUBMAEVAQAB+gD5AAsBIQFFAWkBlAG4AdkB8QH+AQMC/QHvAdcBuAGVAWoBRgEfAf8A6QDOAM4AygDeAPoAFwFEAWkBlQG4Ad0B6wH+Ae8B5QHDAaQBdwFWASYBDQHpAOAA1gDhAO8ADgErAVQBegGeAcEB1gHgAdkBwAGcAWwBNAEEAcwAqwCGAIQAiQCqANMA/wApAUgBWQFjAVkBUQE6ASYBDQH1AOEAzAC+AKoApACTAJIAjQCRAJYAngCpALIAuQC5ALwAsgCuAKIAnACYAKQArgDRAOkAEAEfASoBEgHwALgAewBCABAA5v/X/8b/4//4/ykAWACAAJ0AqgCcAI4AYQBFABoAAwDs/+X/4//v//j/DQAWACAAHwAWAAMA7//a/8j/wv/C/8f/2f/g/+//7//x/+n/4P/S/8X/tP+t/6P/p/+p/7X/v//L/9X/3//g/+r/5f/v/+3/9f/3//v//v////3//P/3//z/+/8MABQALQA9AFMAXgBrAGoAbABiAFUASQA4ADMAMgA9AFIA//8=");
    snd.play();
}



function non_duplicata(ubicazione) {
    var codUbicazione = ubicazione.COD_UBICAZIONE;
    return ubicazioni.filter(x => x.COD_UBICAZIONE == codUbicazione).length === 0;
}

function chiama_ws_rabbocco() {
    hide_errors();
    scheduleLogout();
    $("#qrcode").attr("disabled", true);
    $("#confermaRabbocco").attr("disabled", true); 
    $("#annullaRabbocco").attr("disabled", true);
    $("#messaggio").html("Dichiarazione in corso...");
    //var listaUbicazioni = ubicazioni.map(x => x.COD_UBICAZIONE);

    $.post({
        url: BASE_HREF + "ws/SegnalazioneRabbocco.php",
        headers: {
            'Authorization': 'Bearer ' + sessionStorage.getItem('token')
        },
        data: JSON.stringify(ubicazioni),
        success: function(data, status) {
            console.log(data);
            init();
            beep();
            setTimeout("logout()", 500); // dopo la conferma, il tempo di sentire il bip e facciamo logout
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
            abilita_qrcode();
            abilita_o_disabilita_bottoni();
        }
    });
}

function logout() {
    //localStorage.clear();
    sessionStorage.clear();
    window.location.reload();
}

function elimina(codUbicazione) {
    scheduleLogout();
    if(ubicazioni == null || ubicazioni == ''){
        ubicazioni =  JSON.parse(localStorage.getItem('dati_rabbocco'));
    }
    var index = ubicazioni.findIndex(x => x.COD_UBICAZIONE == codUbicazione);
    ubicazioni.splice(index, 1);
    localStorage.removeItem("dati_rabbocco");
    localStorage.setItem('dati_rabbocco',JSON.stringify(ubicazioni));
    if(ubicazioni.length == 0){
        localStorage.removeItem("dati_rabbocco");
        localStorage.removeItem("utente_rabbocco");
        $("#error_message").css('display','').find("p").html("");
        $("#confermaRabbocco").attr("disabled", true); 
        $("#annullaRabbocco").attr("disabled", true);
    }
    $("#lista").find("li")[index].remove();
    beep();
}

var user = sessionStorage.getItem('user');
$("#user_box p").append(user);
$(".focus").removeAttr("disabled");
$(".focus").focus();

var timeout = null;

function scheduleLogout() {
    if (timeout != null) clearTimeout(timeout);
    timeout = setTimeout(function() {
        console.log("Logout 60sec timeout");
        logout();
    }, 60000);
}