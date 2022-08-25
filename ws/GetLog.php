<?php

include("include/all.php");
$panthera->connect();

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    //do nothing, HTTP 200
    exit();
}

require_logged_user_JWT();

$search = isset($_GET['search']) ? $panthera->escape_string($_GET['search']) : '';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    [$data,$count] = $logManager->getLog($search);

    header('Content-Type: application/json');
    echo json_encode(['data' => $data, 'count' => $count]);
} else {
    //==========================================================
    print_error(400, "Unsupported method in request: " . $_SERVER['REQUEST_METHOD']);
}


?>