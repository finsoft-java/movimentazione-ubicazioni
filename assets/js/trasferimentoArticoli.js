$(document).ready(function(){
    $(".focus").focus();
    setInterval(function() {
        console.log("Focusing esaurimento");
        $("#qrcode").get(0).focus();
    }, 1000);
});             
let i = 0;
document.getElementById("qrcode").addEventListener("keyup", function(event) {
    if (event.keyCode === 13) {
        event.preventDefault();
        i++;
        barCode = $("#qrcode").val();
        if(i == 1) {
            sessionStorage.setItem('ubicazione', barCode);
        }
        if(i == 2) {
            sessionStorage.setItem('articolo', barCode);                       
            $.get({
                url: "./ws/Interrogazione.php?codUbicazione=" + sessionStorage.getItem('ubicazione') + "&codArticolo=" + sessionStorage.getItem('articolo'),
                dataType: 'json',
                success: function(data, status) {
                    let dati = data["data"];
                    console.log(data);
                    let datiStampati = "";
                        datiStampati += "<p style='float: left; width:100%; padding: 0px 15px'> Magazzino: <strong>"+dati[0].ID_MAGAZZINO+"</strong></p>";
                        datiStampati += "<p style='float: left; width:100%; padding: 0px 15px'> Articolo: <strong>"+dati[0].ID_ARTICOLO+"</strong>";
                        datiStampati += "<p style='float: left; width:100%; padding: 0px 15px'> Descrizione: <strong>"+dati[0].DESCRIZIONE+"</strong> </p>";
                        datiStampati += "<div style='float: left; width:100%; padding: 0px 15px'>";
                            datiStampati += "<p style='float: left'>";
                                datiStampati += " <input id='qty' class='inputOsai' type='number' value='1' min='1' max='" + dati[0].QTA_GIAC_PRM + "'/> ";
                            datiStampati += "</p>";
                        datiStampati += "<p style='float: right'>Quantita: <strong>"+dati[0].QTA_GIAC_PRM+"</strong></p></div>";                                
                    $("#appendData").html(datiStampati);
                }
            });
        }
        if(i == 3) {
            sessionStorage.setItem('ubicazione-destinazione', barCode);
            $("#btnTrasferimento").attr('disabled',false);
            $("#magazzinoDest").html("<p style='float: left; width:100%; padding: 0px 15px'> Magazzino destinazione: <strong>" + barCode + " </strong> </p>");
        }
    }
});

function trasferimentoArticoli() {
    $("#qrcode").attr("disabled", true);
    $.post({
        url: "./ws/TrasferimentoArticoli.php?codUbicazione=" + sessionStorage.getItem('ubicazione') + "&codArticolo=" + sessionStorage.getItem('articolo')+ "&qty=" + $("#qty").val() + "&codUbicazioneDest=" + sessionStorage.getItem('ubicazione-destinazione'),
        dataType: 'json',
        success: function(data, status) {
            $("#magazzinoDest").append("<div style='display: block' class='alert alert-success' role='alert'> Trasferimento avvenuto con successo all\'ubicazione <strong>"+sessionStorage.getItem('ubicazione-destinazione')+"</strong></div>");
        }
    });
}