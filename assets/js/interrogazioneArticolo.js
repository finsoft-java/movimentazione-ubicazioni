let timerOn = true;
$("#btnInterroga").hide();

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

document.getElementById("qrcode").addEventListener("keyup", function(event) {
    this.value = this.value.toUpperCase();
    console.log("listening to keyup")
    //q
    if (event.keyCode === 13) {
        value = $("#qrcode").val();
        $.get({
            url: "./ws/Interrogazione.php?codArticolo=" + value+ "&codCommessa=" + $("#selectCommessa").val(),
            dataType: 'json',
            headers: {
                'Authorization': 'Bearer ' + sessionStorage.getItem('token')
            },
            success: function(data, status) {
                let dati = data["data"];
                console.log(dati);
                if(dati[0] == null || dati.length === 0) {   
                    showError("Articoli non presenti ");
                    $("#qrcode").val('');
                    return false;
                }
                console.log("dati ", dati);
                let datiStampati = "";
                for(let i = 0; i < Object.keys(dati).length;i++){
                    datiStampati += getHtmlArticolo(dati[i]);
                }
                $(".listaOsai").html(datiStampati);
                timerOn = false;
                $("#qrcode").hide();
                $("#btnInterroga").show();

            },
            error: function(data, status){
                showError(data);
                $("#qrcode").val('');
                $(".listaOsai").html("");
            }
        });
        $("#qrcode").val("");
        $("#searchAll").css("display","block");
    }
});

function getHtmlArticolo(x) {
    return "<p>Articolo: <strong>" + x.ID_ARTICOLO + "</strong> | Quantita: <strong>" + x.QTA_GIAC_PRM + " " + x.R_UM_PRM_MAG + "</strong></p>"
        + "<p>Disegno: <strong>" + x.DISEGNO + "</strong></p>"
        + "<p>Descrizione: <strong>" + x.DESCRIZIONE + "</strong></p>"
        + "<p>Ubicazione: <strong>" + x.ID_UBICAZIONE + "</strong></p>"
        + "<p>Magazzino: <strong style='text-transform:uppercase'>" + x.ID_MAGAZZINO + "</strong></p>"
        + "<p>Ult. modifica: " + x.TIMESTAMP_AGG + " " + x.R_UTENTE_AGG + "</p>"
        + (x.ID_COMMESSA ? "<p>Commessa: <strong>" + x.ID_COMMESSA + "</strong></p>" : "<p>Commessa: <strong> - </strong></p>")
        + "<hr/>";
}

function showError(data) {
    const err = typeof data === 'string' ? data :
                data.responseJSON && data.responseJSON.error && data.responseJSON.error.value.length > 0 ? data.responseJSON.error.value :
                "Errore interno";
    alert(err);
}

function ripetiInterrogazione() {
    $(".listaOsai").html("");
    timerOn = true;
    $("#btnInterroga").hide();
    $("#qrcode").show();
    $("#searchAll").css("display","none");
}

$(document).on("click","#selectCommessa",function(){
    timerOn=false;
});

$(document).on("change","#selectCommessa",function(){
    if($("#qrcode").val().trim() != ''){
        timerOn=false;
    } else {
        timerOn=true;
        $("#selectCommessa").prop("disabled",true);
    }
});