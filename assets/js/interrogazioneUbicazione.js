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
                    showError("Ubicazione vuota");
                    $("#qrcode").val('');
                    return false;
                }
                let datiStampati = "<p>Magazzino: <strong style='text-transform:uppercase'>"+dati[0].ID_MAGAZZINO+"</strong></p>";
                for(let i = 0; i < Object.keys(dati).length;i++){
                    
                    datiStampati += "<p>Articolo: <strong>"+dati[i].ID_ARTICOLO+"</strong> | Quantita: <strong>"+dati[i].QTA_GIAC_PRM+" "+ dati[i].R_UM_PRM_MAG +" </strong></p>";
                    datiStampati += "<p>Disegno: <strong>"+dati[i].DISEGNO+"</strong> </p>";
                    datiStampati += "<p>Descrizione: <strong>"+dati[i].DESCRIZIONE+"</strong> </p>";
                    datiStampati += "<hr/>";
                }
                $(".listaOsai").html(datiStampati);
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
function showError(data) {
    const err = typeof data === 'string' ? data :
                data.responseJSON && data.responseJSON.error && data.responseJSON.error.value && data.responseJSON.error.value.length > 0 ? data.responseJSON.error.value :
                "Errore interno";
    alert(err);
}