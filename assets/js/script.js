var BASE_HREF = "../../";
if (location.href.includes('localhost') || location.href.includes('127.0.0.1')) {
    BASE_HREF = "../../cassettiere/";
}

$(document).on("click", "#logout", function(){
    sessionStorage.clear();
    location.href = "./segnalazione-esaurimento.html";
});

function abilitaLettoreBadge() {
    $.get("http://localhost/cgi-bin/ungrabRFID.sh", function(data, status) {
        console.log("GRAB RFID Data: " + data + "\nStatus: " + status);
    });
}

function disabilitaLettoreBadge() {
    $.get("http://localhost/cgi-bin/grabRFID.sh", function(data, status) {
        console.log("UNGRAB RFID Data: " + data + "\nStatus: " + status);
    });
}

function require_login(callingPage, role) {
    var data = sessionStorage.getItem('token');
    if (data == null || (role && sessionStorage.getItem('role') != role)) {
        sessionStorage.setItem('redirectPage', callingPage);
        if (role) {
            sessionStorage.setItem('requiredRole', role);
        } else {
            sessionStorage.removeItem('requiredRole');
        }
        location.href = "./login.html";
    }
}

function show_error(msg) {
    $("#error_message p").html(msg);
    $("#error_message").css("display","block");
}

function hide_errors() {
    $("#error_message p").html("");
    $("#error_message").css("display","");
}