<?php

include("include/all.php");

$panthera->connect();


if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    //do nothing, HTTP 200
    exit();
}
    
// DO NOT require_logged_user_JWT();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    //==========================================================


$panthera->execute_update("SET ANSI_WARNINGS  OFF");

$sql = "SELECT top(10) * FROM THIP.DOC_TRA_TES
                WHERE STATO= 'V' AND STATO_AVANZAMENTO='1' ";
$data = $panthera->select_list($sql);

        
print_r($data[0]);
        
        foreach ($data[0] as $value) {
          echo '-----'.$value.'----';
          print_r($value);
          print_r(is_numeric($value));
          if (trim($value) == '') {
            $value = 'null';
          } else {
            if (is_numeric(trim($value)) && !(substr($value, 0, 1) == '0' and strlen(trim($value)) > 1)) {
              $value = $value;
            } else {
              $value = "'" . $this->mssql_escape_string(trim($value)) . "'";
            }
          }
        }



} else {
    //==========================================================
    print_error(400, "Unsupported method in request: " . $_SERVER['REQUEST_METHOD']);
}

?>