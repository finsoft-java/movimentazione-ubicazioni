$(document).ready(function(){
    $(".focus").focus();
    setInterval(function() {
        console.log("Focusing esaurimento");
        $("#qrcode").get(0).focus();
    }, 1000);
});
document.getElementById("qrcode").addEventListener("keyup", function(event) {
    if (event.keyCode === 13) {
        $.get({
            url: "./ws/Interrogazione.php?codUbicazione=" + $("#qrcode").val(),
            dataType: 'json',
            success: function(data, status) {
                let dati = data["data"];
                if(dati[0] == null) {                    
                    $("#error_message").html("<div class='alert alert-danger' role='alert'>Ubicazione inesistente si prega di riprovare.</div>");
                    $("#error_message div").css("display","block");
                    $("#qrcode").val('');
                    return false;
                }
                let datiStampati = "<p>MAGAZZINO: <strong style='text-transform:uppercase'>"+dati[0].ID_MAGAZZINO+"</strong></p>";
                for(let i = 0; i < Object.keys(dati).length;i++){
                    
                    datiStampati += "<p>Articolo: <strong>"+dati[i].ID_ARTICOLO+"</strong> | Quantita: <strong>"+dati[i].QTA_GIAC_PRM+" pz. </strong></p>";
                    datiStampati += "<p>Descrizione: <strong>"+dati[i].DESCRIZIONE+"</strong> </p>";
                    datiStampati += "<hr/>";
                }
                $(".listaOsai").html(datiStampati);
            }
        });
        $("#qrcode").val("");
    }
});