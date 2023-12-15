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
    if (event.keyCode === 13) {
        value = $("#qrcode").val();
        $.get({
            url: "./ws/GetUbicazione.php?codUbicazione=" + value,
            dataType: 'json',
            headers: {
                'Authorization': 'Bearer ' + sessionStorage.getItem('token')
            },
            success: function(data, status) {
                console.log("data ", data)
                let dati = data["value"];
                console.log("dati ", dati)
                if(dati == null || dati.length === 0) {   
                    showError("Ubicazione vuota");
                    $("#qrcode").val('');
                    return false;
                }
                if(dati.NOTE_POSIZIONE == null){
                    dati.NOTE_POSIZIONE = "";
                }
                if(dati.NOTE == null){
                    dati.NOTE = "";
                }
                let datiStampati = `<label>Note di posizionamento :</label> <textarea id="note_pos" class="form-control">${dati.NOTE_POSIZIONE}</textarea>
                                    <label>Note descrittive : </label> <textarea id="note" class="form-control">${dati.NOTE}</textarea>
                                    <input type="hidden" value="${dati.ID_UBICAZIONE}" id="idUbicazione">
                                    <div class="btn_50">
                                        <button onclick="salva()" type="button" class="btn btnOsai"> Salva </button>
                                        <button onclick="ripetiInterrogazione()" type="button" class="btn btnOsai"> Annulla </button>
                                    </div>`;
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
    }
});
function salva(){
    $.post({
        url: "./ws/Note.php",
        dataType: 'json',
        data: "idUbicazione=" + $("#idUbicazione").val().trim() + "&note=" + $("#note").val() + "&note_pos=" + $("#note_pos").val(),
        headers: {
            'Authorization': 'Bearer ' + sessionStorage.getItem('token')
        },
        success: function(data, status) {
            showSuccessMsg("Le note dell'ubicazione " + $("#idUbicazione").val() + " sono state modificate con successo");
            ripetiInterrogazione();
        },
        error: function(data, status){            
            console.log("ERRORE in svuotaUbicazione", data, status);
            showError(data);
            $("#qrcode").val('');
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
    $('#success_message').text(msg);
    $('#success_message').show();
    setTimeout(() => {
        $('#success_message').hide();
      }, "1000");
}
function ripetiInterrogazione() {
    $(".listaOsai").html("");
    timerOn = true;
    $("#btnInterroga").hide();
    $("#qrcode").show();
}