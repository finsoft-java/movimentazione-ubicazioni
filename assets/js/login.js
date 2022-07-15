var trials = 0;
$(document).ready(function(){
    abilitaLettoreBadge();
    trials = localStorage.getItem('trials') || 0;
    if (trials >= 5) {
        wait();
    }
    scheduleLogout();
});

setInterval(function() {
    $("#rfid").focus();
}, 1000);
document.getElementById("rfid").addEventListener("keyup", function(event) {
// Number 13 is the "Enter" key on the keyboard
    if (event.keyCode === 13) {
        event.preventDefault();
        login();
    }
});

function login () {
    const rfid = $("#rfid").val();
    hide_errors();
    scheduleLogout();
    $("#rfid").attr("disabled", true);
    $.post({
        url: BASE_HREF + "/ws/LoginBadge.php",
        dataType: 'json',
        data: {
            rfid: rfid
        },
        success: function(data, status) {
            if (sessionStorage.getItem('requiredRole') && data["value"]["ruolo"] != sessionStorage.getItem('requiredRole')) {
                show_error("Utente non autorizzato");
                $("#rfid").val("");
                $("#rfid").removeAttr("disabled");
            } else {
                sessionStorage.setItem( "user", (data["value"]["nome"] || '') + ' ' + (data["value"]["cognome"] || ''));
                sessionStorage.setItem( "token", data["value"]["username"] );
                sessionStorage.setItem( "role", data["value"]["ruolo"] );
                location.href = sessionStorage.getItem('redirectPage') || 'segnalazione-esaurimento.html';
            }
        },
        error:  function (xhr, ajaxOptions, thrownError) {
            console.log(xhr);
            var msg = (xhr.responseJSON && xhr.responseJSON.error && xhr.responseJSON.error.value) ? xhr.responseJSON.error.value
                : (xhr.responseText) ? xhr.responseText
                : "Network error";
            show_error(msg);
            
            $("#rfid").val("");
            localStorage.setItem('trials', ++trials);
            if (trials >= 5) {
                wait();
            } else {
                $("#rfid").removeAttr("disabled");
            }
        }
    });
}

function wait() {
    $("#rfid").attr("disabled", true);
    show_error("Attendere 60 secondi...");
    setTimeout(function(){
        $("#rfid").removeAttr("disabled");
        localStorage.setItem('trials', trials = 0);
        hide_errors();
    }, 60000);
}

var timeout = null;
function scheduleLogout() {
    if (timeout != null) clearTimeout(timeout);
    timeout = setTimeout(function() {
        console.log("Logout 120sec timeout");
        location.href = './';
    }, 120000);
}
