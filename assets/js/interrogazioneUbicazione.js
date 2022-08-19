$(document).ready(function(){
    $(".focus").focus();
    setInterval(function() {
        console.log("Focusing esaurimento");
        $("#qrcode").get(0).focus();
    }, 1000);
});
let ubicazione;
document.getElementById("qrcode").addEventListener("keyup", function(event) {
    if (event.keyCode === 13) {
        value = $("#qrcode").val();
        $.get({
            url: "./ws/Interrogazione.php?codUbicazione=" + value,
            dataType: 'json',
            success: function(data, status) {
                let dati = data["data"];
                if(dati[0] == null || dati.length === 0) {   
                    showError("Ubicazione inesistente o vuota si prega di riprovare");
                    $("#qrcode").val('');
                    return false;
                }
                let datiStampati = "<p>MAGAZZINO: <strong style='text-transform:uppercase'>"+dati[0].ID_MAGAZZINO+"</strong></p>";
                for(let i = 0; i < Object.keys(dati).length;i++){
                    
                    datiStampati += "<p>Articolo: <strong>"+dati[i].ID_ARTICOLO+"</strong> | Quantita: <strong>"+dati[i].QTA_GIAC_PRM+" "+ dati[i].R_UM_PRM_MAG +" </strong></p>";
                    datiStampati += "<p>Disegno: <strong>"+dati[i].DISEGNO+"</strong> </p>";
                    datiStampati += "<p>Descrizione: <strong>"+dati[i].DESCRIZIONE+"</strong> </p>";
                    datiStampati += "<hr/>";
                }
                // DEBUG CODE
                datiStampati += "<p style='color:green'>";
                for (var i = 0; i < value.length; ++i) {
                    datiStampati += "'" + value.charCodeAt(i) + "' ";
                }
                datiStampati += "</p>";
                $(".listaOsai").html(datiStampati);
            },
            error: function(data, status){
                showError("Ubicazione inesistente o vuota si prega di riprovare");
                $("#qrcode").val('');
                
                // DEBUG CODE
                datiStampati += "<p style='color:green'>";
                for (var i = 0; i < value.length; ++i) {
                    datiStampati += "'" + value.charCodeAt(i) + "' ";
                }
                datiStampati += "</p>";
                $(".listaOsai").html(datiStampati);
            }
        });
        $("#qrcode").val("");
    }
});
function showError(msg) {
    // $("#error_message").html("<div class='alert alert-danger' role='alert'>"+msg+"</div>");
    // $("#error_message div").css("display","block");
    // setTimeout(function() {
    //     $("#error_message").html('');
    // }, 3000);
    alert(msg);
}