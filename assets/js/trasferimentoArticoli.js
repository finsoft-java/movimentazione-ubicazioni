let timerOn = true;

$(document).ready(function(){
    $(".focus").focus();
    setInterval(function() {
        if(timerOn) {
           console.log("Focusing");
            $("#qrcode").get(0).focus();
        }
    }, 1000);
});   

let i = 0;
// i e' uno STATO
// 0 = initial
// 1 = e' stato sparato il primo barcode (ubicazione)
// 2 = e' stato sparato il secondo barcode (ubicazioneDest)
// 3 = e' stato sparato il terzo barcode (articolo)
let ubicazione;
let articolo;
let ubicazioneDest;
let maxQty;
let arrayCommessa = [];
let ubicazioneInWhitelist = null;
let magazzinoPartenza;
let magazzinoArrivo;

document.getElementById("qrcode").addEventListener("keyup", function(event) {
    this.value = this.value.toUpperCase();
    if (event.keyCode === 13) {
        event.preventDefault();
        i++;
        barCode = $("#qrcode").val();
        if(i == 1) {
            if(barCode.trim() != ""){       
                ubicazione =  barCode;
                $.get({
                    url: "./ws/GetUbicazione.php?codUbicazione=" + ubicazione,
                    dataType: 'json',
                    headers: {
                        'Authorization': 'Bearer ' + sessionStorage.getItem('token')
                    },
                    success: function(data, status) {
                        const dati = data.value;
                        let datiStampati = ""; 
                        ubicazione = dati.ID_UBICAZIONE.trim();
                        datiStampati += "<p class='pOsai'> Ubicazione di partenza: <strong>"+dati.ID_UBICAZIONE+"</strong></p>";
                        datiStampati += "<p class='pOsai'> Magazzino: <strong>"+dati.ID_MAGAZZINO+"</strong></p>";
                        magazzinoPartenza = dati.ID_MAGAZZINO;
                        $("#appendData").html(datiStampati);
                        $("#qrcode").val("").attr('placeholder','UBICAZIONE DESTINAZIONE');
                    },
                    error: function(data, status){
                        $("#qrcode").attr('placeholder','UBICAZIONE ORIGINE');
                        console.log('ERRORE -> Interrogazione', data);
                        showError(data);
                        $("#qrcode").val('');
                        ubicazione = null;
                        
                        magazzinoPartenza = null;
                        magazzinoArrivo = null;
                        i = 0;
                        return false;
                    }
                });
            } else {
                showError("Digitare qualcosa nel campo ubicazione!");
                i=0;
                return false;
            }
        }
        if(i == 2) {             
            if(barCode.trim() != ""){     
                ubicazioneDest = barCode;
                $.get({
                    url: "./ws/GetUbicazione.php?codUbicazione=" + ubicazioneDest,
                    dataType: 'json',
                    headers: {
                        'Authorization': 'Bearer ' + sessionStorage.getItem('token')
                    },
                    success: function(data, status) {
                        const dati = data.value;
                        let datiStampati = ""; 
                        datiStampati += "<p class='pOsai'> Ubicazione dest.: <strong>"+dati.ID_UBICAZIONE+"</strong></p>";
                        datiStampati += "<p class='pOsai'> Magazzino destinazione: <strong>"+dati.ID_MAGAZZINO+"</strong></p>";
                        let magazzinoArrivo = dati.ID_MAGAZZINO;
                        if(magazzinoPartenza != magazzinoArrivo) {
                            $("body").attr("style","background-color: #eded6e");
                        }
                        $("#appendData").append(datiStampati);
                        $("#qrcode").val("").attr('placeholder','ARTICOLO');
                    },
                    error: function(data, status){
                        $("#qrcode").attr('placeholder','UBICAZIONE DESTINAZIONE');
                        console.log('ERRORE -> Interrogazione', data);
                        showError(data);
                        $("#qrcode").val('');
                        ubicazioneDest = null;
                        magazzinoPartenza = null;
                        magazzinoArrivo = null;
                        i = 1;
                        return false;
                    }
                });  
            } else {
                showError("Ubicazione destinazione inesistente si prega di riprovare");
                $("#qrcode").val("").attr('placeholder','UBICAZIONE DESTINAZIONE');
                i=1;
                return false;
            }
       }
        if(i == 3) {
            if(barCode.trim() === ""){       
                showError("Inserire un articolo!");
                $("#qrcode").val("").attr('placeholder','ARTICOLO');
                i=2;
                return false;
            }
            articolo = barCode;
            $("#qrcode").val("").attr('placeholder','ARTICOLO').attr('disabled',false);
            $("#btnTrasferimento").attr('disabled',false);
            $("#btnRipeti").attr('disabled',false);
            
            $.get({
                url: "./ws/Interrogazione.php?codUbicazione=" + ubicazione + "&codArticolo=" +articolo,
                dataType: 'json',
                headers: {
                    'Authorization': 'Bearer ' + sessionStorage.getItem('token')
                },
                success: function(data, status) {
                    let dati = data["data"];
                    if(dati == null || dati.length === 0) {
                        showError("Articolo inesistente si prega di riprovare");
                        $("#qrcode").val("").attr('placeholder','ARTICOLO').attr('disabled',false);
                        i=2;
                        return false;
                    }
                    maxQty = dati[0].QTA_GIAC_PRM;
                    let datiStampati = ""; 
                    let optCommessa = "";
                    optCommessa += "<select id='selectCommessa' class='form-control'>"
                                  +"<option selected='selected' value='0'>Seleziona Commessa</option>";
                    let nomeCommessa = "";
                    let giacenzaIniziale = 0;
                    let um = "";
                    for(let i = 0; i < dati.length; i++){
                        if(dati[i].ID_COMMESSA == null) {
                            arrayCommessa.push("-");
                            nomeCommessa = "-";                            
                        } else {
                            nomeCommessa = dati[i].ID_COMMESSA.trim();
                            arrayCommessa.push(dati[i].ID_COMMESSA.trim());
                        }
                        if(i == 0) {
                            giacenzaIniziale = dati[i].QTA_GIAC_PRM;
                            um = dati[i].R_UM_PRM_MAG;
                        }
                        optCommessa += "<option value='"+nomeCommessa+"' data-maxQty='"+dati[i].QTA_GIAC_PRM+"' data-prm='"+dati[i].R_UM_PRM_MAG+"'>"+nomeCommessa+"</option>";
                    }
                    optCommessa += "</select>";
                    datiStampati += "<p class='pOsai'> Ubicazione di partenza: <strong>"+dati[0].ID_UBICAZIONE+"</strong></p>";
                    datiStampati += "<p class='pOsai'> Magazzino: <strong>"+dati[0].ID_MAGAZZINO+"</strong></p>";
                    datiStampati += "<p class='pOsai'> Ubicazione dest.: <strong>" + ubicazioneDest + " </strong> </p>";
                    datiStampati += "<p class='pOsai'> Articolo: <strong>"+dati[0].ID_ARTICOLO+"</strong></p>";
                    datiStampati += "<p class='pOsai'> Disegno: <strong>"+dati[0].DISEGNO+"</strong> </p>";
                    datiStampati += "<p class='pOsai'> Descrizione: <strong>"+dati[0].DESCRIZIONE+"</strong> </p>";
                    datiStampati += "<p class='pOsai'> Commessa:  </p>"+optCommessa;
                    datiStampati += "<div class='input-group inputDiv'>  <div class='input-group-prepend'><button class='btn btnInputForm btnMinus' type='button' onClick='minus(1)'>-</button></div>";
                    if(whiteList.includes(ubicazione)){
                        datiStampati += "<input type='number' class='form-control inputOsai' disabled onclick='timerOn = false' onblur='timerOn = true'  id='qty' class='inputOsai' value='1' min='' max='' placeholder='Quantità da trasferire' aria-label='Quantità da trasferire' aria-describedby='basic-addon2'>";
                    } else {
                        datiStampati += "<input type='number' class='form-control inputOsai' disabled onclick='timerOn = false' onblur='timerOn = true'  id='qty' class='inputOsai' value='1' min='0.001' max='"+giacenzaIniziale+"' placeholder='Quantità da trasferire' aria-label='Quantità da trasferire' aria-describedby='basic-addon2'>";
                    }
                    
                    datiStampati += "<div class='input-group-append'><button class='btn btnInputForm btnPlus' type='button' onClick='plus("+giacenzaIniziale+")'>+</button></div>";
                    datiStampati += "<button class='btn btnInputForm btnAll' type='button' onClick='selezionaTutti("+giacenzaIniziale+")'> Tutti </button></div>";
                    datiStampati += "<p class='pOsai'> Quantita Totale: <strong id='commessaQty'></strong></p>";

                    $.get({
                        url: "./ws/Interrogazione.php?codUbicazione=" + ubicazioneDest + "&codArticolo=" +articolo,
                        dataType: 'json',
                        headers: {
                            'Authorization': 'Bearer ' + sessionStorage.getItem('token')
                        },
                        success: function(data, status) {
                            let dati = data["data"];
                            if(data["count"] > 0){
                                datiStampati += "<p class='pOsai'> Quantita Ubicazione Destinazione: <strong id='commessaQty'>"+dati[0].QTA_GIAC_PRM+" "+dati[0].R_UM_PRM_MAG+"</strong></p>";
                            } else {
                                datiStampati += "<p class='pOsai'> Non ci sono articoli <strong>"+articolo+"</strong> in questa ubicazione <strong>"+ubicazioneDest+"</strong></p>";
                            }
                            $("#appendData").html(datiStampati);
                            $("#qrcode").val("").attr('placeholder','COMMESSA');
                        },
                        error: function(data, status){
                            console.log("ERRORE in i == 3 trasferimentoArticoli", data);
                            showError(data);
                            $("#qrcode").val('').attr('disabled',false);
                            i=2;
                            $("#btnTrasferimento").attr('disabled',true);
                            $("#btnRipeti").attr('disabled',true);
                        }
                    });

                },
                error: function(data, status){
                    console.log("ERRORE in i == 3 trasferimentoArticoli", data);
                    showError(data);
                    $("#qrcode").val('').attr('disabled',false);
                    i=2;
                    $("#btnTrasferimento").attr('disabled',true);
                    $("#btnRipeti").attr('disabled',true);
                }
            });
            $("#qrcode").val("");
        }
        
        if(i == 4) {
            $("#qrcode").val('').attr('disabled',false);
            if(barCode.trim() != ""){
                magazzinoDest = barCode; 
            } else {
                $("#qrcode").attr('placeholder','COMMESSA');
                showError("Commessa inesistente si prega di riprovare");
                $("#qrcode").val('');
                i=3;
                return false;
            }
            if(!arrayCommessa.includes(barCode)) {         
                $("#qrcode").attr('placeholder','COMMESSA');
                showError("COMMESSA non valida");
                $("#qrcode").val('');
                magazzinoDest= null;
                i=3;
                return false;
            } else {
                $("#qrcode").val('').attr('disabled',true);
                console.log("selectCommessa "+barCode);
                $("#selectCommessa").val(barCode);
                $("#selectCommessa").trigger("change");
                timerOn = false;
            }
        }
    }
});

$(document).on("change", "#selectCommessa", function(){
    if($(this).val() != 0){
        maxQty = $(this).find('option:selected').data("maxqty");
        $("#commessaQty").html(maxQty+" "+$(this).find('option:selected').data('prm'));
        if(!whiteList.includes(ubicazione.trim())){
            $("#qty").attr("max",maxQty).attr("disabled",false);
            $(".btnPlus").attr('onClick','plus('+maxQty+')');
            $(".btnAll").attr('onClick','selezionaTutti('+maxQty+')');
        } else {
            $("#qty").prop("disabled",false);
            $(".btnAll").attr('onClick','selezionaTutti(10)');
        }
        
        $("#qrcode").val('').attr('disabled',true);
        timerOn = false;
    } else {
        $("#qrcode").val('').attr('disabled',false);
        timerOn = true;
    }    
});

function trasferimentoArticoli(repeatFlag) { //flag a true -> ripete, false -> conferma e esce
    const qtyInput = $("#qty").val();
    const qrcode = $("#qrcode");
    
    //fixme devo controllare se è in whitelist
    if(!whiteList.includes(ubicazione)){
        if((!qtyInput.match(/^\d+(,\d+|\.\d+)?$/)) || !qtyInput || (parseFloat(qtyInput) < 0.001 || parseFloat(qtyInput) > maxQty)) { 
            showError("Inserire una quantità valida tra uno e " + maxQty + " (numeri decimali con il punto)");
            i=3;
            $("#btnTrasferimento").attr('disabled',false);
            $("#btnRipeti").attr('disabled',false);
            return;
        }
    } else {
        ubicazioneInWhitelist = "Y";
    }
    const qty = parseFloat(qtyInput).toFixed(3);
    const codCommessa = $("#selectCommessa option:selected").text();
    if(codCommessa == "Seleziona Commessa"){
        showError("Inserire una commessa valida");
        i=4;
        $("#btnTrasferimento").attr('disabled',false);
        $("#btnRipeti").attr('disabled',false);
        return;
    }
    if(repeatFlag) {
        qrcode.attr("disabled", false);
        qrcode.val("").attr('placeholder','ARTICOLO');
        $("#appendData").html("");
        timerOn = true;
        i = 2;
    } else {
        qrcode.attr("disabled", true);
    }
    $("#btnTrasferimento").attr('disabled',true);
    $("#btnRipeti").attr('disabled',true);

    $.post({
        url: "./ws/TrasferimentoArticoli.php?codUbicazione=" + ubicazione + "&codArticolo=" + articolo+ "&qty=" + qty  + "&codUbicazioneDest=" +ubicazioneDest+ "&commessa=" + codCommessa+ "&whitelist="+ubicazioneInWhitelist,
        dataType: 'json',
        headers: {
            'Authorization': 'Bearer ' + sessionStorage.getItem('token')
        },
        success: function(data, status) {
            showSuccessMsg("Trasferimento avvenuto con successo \n (ubicazione di partenza: " + ubicazione + ", ubicazione dest.: " + ubicazioneDest + ", articolo: " + articolo + ", quantità: " + qty + ")");
        },
        error: function(data, status){
            console.log("ERRORE in trasferimentoArticoli", data);
            showError(data);
            qrcode.val('');
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
    alert(msg); 
    setTimeout(function() { 
        window.close();
    }, 1000);
}

function plus(maxQty) {
    //devo fare il controllo in whitelist non c'è max quindi se ci sono 10 pz ne posso prelevare anche 12 e va in negativo
    if(whiteList.includes(ubicazione)) {
        $("#qty").val((parseFloat($("#qty").val())+1).toFixed(3));    
    } else {
        if($("#qty").val() <= maxQty - 1) {
            $("#qty").val((parseFloat($("#qty").val())+1).toFixed(3));    
        }
    }
}

function minus(minimum = 1) {
    //controllo se è in whitelist l'ubicazione di partenza 
    if($("#qty").val() >= minimum + 1) {
        $("#qty").val((parseFloat($("#qty").val())-1).toFixed(3));
    }
}

function selezionaTutti(maxQty) {
    $("#qty").val(maxQty);
}

$(document).on("click","#selectCommessa",function(){
    timerOn = false;
});