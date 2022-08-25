<?php 
use Firebase\JWT\JWT;
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: OPTIONS,GET,PUT,POST,DELETE");
header("Access-Control-Allow-Headers: Accept,Content-Type,Authorization");
# Chrome funziona anche con Access-Control-Allow-Headers: *  invece Firefox no

require 'vendor/autoload.php';

include("config.php");
include("functions.php");
include("class_ldap.php");
include("class_panthera.php");
include("class_magazzini.php");
include("class_ubicazioni.php");
include("class_caricamenti_massa.php");
include("class_log.php");
