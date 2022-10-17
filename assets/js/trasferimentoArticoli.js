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
                        datiStampati += "<p class='pOsai'> Ubicazione di partenza: <strong>"+dati.ID_UBICAZIONE+"</strong></p>";
                        datiStampati += "<p class='pOsai'> Magazzino: <strong>"+dati.ID_MAGAZZINO+"</strong></p>";
                        $("#appendData").html(datiStampati);
                        $("#qrcode").val("").attr('placeholder','UBICAZIONE DESTINAZIONE');
                    },
                    error: function(data, status){
                        $("#qrcode").attr('placeholder','UBICAZIONE ORIGINE');
                        console.log('ERRORE -> Interrogazione', data);
                        showError(data);
                        $("#qrcode").val('');
                        ubicazione = null;
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
                        $("#appendData").append(datiStampati);
                        $("#qrcode").val("").attr('placeholder','ARTICOLO');
                    },
                    error: function(data, status){
                        $("#qrcode").attr('placeholder','UBICAZIONE DESTINAZIONE');
                        console.log('ERRORE -> Interrogazione', data);
                        showError(data);
                        $("#qrcode").val('');
                        ubicazioneDest = null;
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
            $("#qrcode").val("").attr('placeholder','ARTICOLO').attr('disabled',true);
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
                    let arrayCommessa = [];
                    let optCommessa = "";
                    optCommessa += "<select id='selectCommessa' class='form-control'>";
                    let nomeCommessa = "";
                    for(let i = 0; i < dati.length; i++){
                        if(dati[i].ID_COMMESSA == null){
                            arrayCommessa.push("-");
                            nomeCommessa = "-";                            
                        } else {
                            nomeCommessa = dati[i].ID_COMMESSA;
                            arrayCommessa.push(dati[i].ID_COMMESSA);
                        }
                        optCommessa += "<option value='"+dati[i].QTA_GIAC_PRM+"' data-prm='"+dati[i].R_UM_PRM_MAG+"'>"+nomeCommessa+"</option>";
                    }
                    optCommessa += "</select>";
                    datiStampati += "<p class='pOsai'> Ubicazione di partenza: <strong>"+dati[0].ID_UBICAZIONE+"</strong></p>";
                    datiStampati += "<p class='pOsai'> Magazzino: <strong>"+dati[0].ID_MAGAZZINO+"</strong></p>";
                    datiStampati += "<p class='pOsai'> Ubicazione dest.: <strong>" + ubicazioneDest + " </strong> </p>";
                    datiStampati += "<p class='pOsai'> Articolo: <strong>"+dati[0].ID_ARTICOLO+"</strong></p>";
                    datiStampati += "<p class='pOsai'> Disegno: <strong>"+dati[0].DISEGNO+"</strong> </p>";
                    datiStampati += "<p class='pOsai'> Descrizione: <strong>"+dati[0].DESCRIZIONE+"</strong> </p>";
                    datiStampati += "<p class='pOsai'> Commessa:  </p>"+optCommessa;
                    datiStampati += "<div class='input-group inputDiv'>  <div class='input-group-prepend'><button class='btn btnInputForm btnMinus' type='button' onClick='minus()'>-</button></div>";
                    datiStampati += "<input type='number' class='form-control inputOsai' disabled onclick='timerOn = false' onblur='timerOn = true'  id='qty' class='inputOsai' value='1' min='0.001' max='' placeholder='Quantità da trasferire' aria-label='Quantità da trasferire' aria-describedby='basic-addon2'>";
                    datiStampati += "<div class='input-group-append'><button class='btn btnInputForm btnPlus' type='button' onClick=''>+</button></div>";
                    datiStampati += "<button class='btn btnInputForm btnAll' type='button' onClick=''> Tutti </button></div>";
                    datiStampati += "<p class='pOsai'> Quantita Totale: <strong id='commessaQty'></strong> </p>";
                    $("#appendData").html(datiStampati);
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
    }
});

$(document).on("change", "#selectCommessa", function(){
    $("#commessaQty").html($(this).val()+" "+$(this).find('option:selected').data('prm'));
    $("#qty").attr("max",$(this).val()).attr("disabled",false);
    $(".btnPlus").attr('onClick','plus('+$(this).val()+')');
    $(".btnAll").attr('onClick','selezionaTutti('+$(this).val()+')');    
});

// $(document).on('input', 'input[type=number]', checkQty);

function trasferimentoArticoli(repeatFlag) { //flag a true -> ripete, false -> conferma e esce
    const qtyInput = $("#qty").val();
    const qrcode = $("#qrcode");

    if((!qtyInput.match(/^\d+(,\d+|\.\d+)?$/)) || !qtyInput || (parseFloat(qtyInput) < 0.001 || parseFloat(qtyInput) > maxQty)) { 
        showError("Inserire una quantità valida tra uno e " + maxQty + " (numeri decimali con il punto)");
        i=3;
        $("#btnTrasferimento").attr('disabled',false);
        $("#btnRipeti").attr('disabled',false);
        return;
    }
    const qty = parseFloat(qtyInput).toFixed(3);

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
        url: "./ws/TrasferimentoArticoli.php?codUbicazione=" + ubicazione + "&codArticolo=" + articolo+ "&qty=" + qty  + "&codUbicazioneDest=" +ubicazioneDest+ "&commessa=" +$("#selectCommessa option:selected").text(),
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
}

function plus(maxQty) {
    if($("#qty").val() <= maxQty - 1) {
        $("#qty").val((parseFloat($("#qty").val())+1).toFixed(3));    
    }
}

function minus(minimum = 1) {
    if($("#qty").val() >= minimum + 1) {
        $("#qty").val((parseFloat($("#qty").val())-1).toFixed(3));
    }
}

function selezionaTutti(maxQty) {
    $("#qty").val(maxQty);
}




