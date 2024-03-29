<?php

define('JWT_SECRET_KEY', 'OSAISECRET2021');

/* LDAP / ACTIVE DIRECTORY */
define('AD_SERVER', 'ldap://osai.loc');
define('AD_DOMAIN', 'OSAI.LOC');
// define('AD_BASE_DN', "dc=OSAI,dc=LOC");
define('AD_BASE_DN', "OU=Users,OU=OU Osai.it,DC=osai,DC=loc");
// AD_FILTER: è il gruppo dei "Magazzinieri"
// Solo i magazzinieri possono accedere al backend.
// Invece al frontend possono accedere tutti gli utenti con badge, tuttavia i magazzinieri hanno più privilegi.
define('AD_FILTER', '(memberOf=CN=Gruppo Logistica,OU=OU Osai Groups,DC=osai,DC=loc)');
#define('AD_USERNAME', 'surveyosai@OSAI.LOC');
#define('AD_PASSWORD', 's0fu3Y2o19!');

define('GRUPPI_ABILITATI', 'GA,MGZZ_O');

/* DATABASE PANTHERA */
define('MOCK_PANTHERA', 'true');
define('DB_PTH_HOST', 'tcp:myserver.database.windows.net,1433');
define('DB_PTH_USER', 'my_user');
define('DB_PTH_PASS', 'my_pwd');
define('DB_PTH_NAME', 'PANTH01');

/* PARAMETRI PER LETTURA BARCODE CSV */
define('POS_UBICAZIONE', '1');
define('POS_ARTICOLO', '0');


/* costanti per il documento di trasferimento */
$ID_AZIENDA = '001';
$DATA_ORIGIN = 'CM-MOV-UBI';
$CAU_TESTATA = 'LL4';
$CAU_RIGA = 'LL2';
$SERIE = 'DT';
$COD_SCHEDULED_JOB = 'CMDocTrasf';
$URL_CM = 'http://172.18.0.15/panth01/ws?id=JOBLA&user=CAD&pwd=CADQWER&company=001&key=CMDocTrasf';
$URL_YGEN = 'http://172.18.0.15/panth01/ws?id=JOBLA&user=CAD&pwd=CADQWER&company=001&key=YGenUbTrasf';
$CAU_TESTATA_SVUOTA = 'SVT';
$CAU_RIGA_SVUOTA = 'LL4';
$COD_MAGAZ_SVUOTA = 'DIN';
$UBIC_SVUOTA = 'PRO';
$CAU_RIGA = 'LL2';

$CAUSALI_RICHIESTE_MOV = ['T01', 'T02'];

?>