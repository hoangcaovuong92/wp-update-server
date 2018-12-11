<?php
// required headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
 
// include database and object files
include_once './db/wd_server_database.php';
 
$database = WD_Server_Database::get_instance();

//Get customer information by purchase code
if (isset($_GET['check_active_by_purchase_code'])) {
    $purchase_code  = $_GET['check_active_by_purchase_code'];
    $data           = $database->get_customer_info_by_purchase_code($purchase_code);
    printf(json_encode($data, JSON_PRETTY_PRINT));
}

//Check purchase code exist
if (isset($_GET['check_purchase_code_exist'])) {
    $purchase_code  = $_GET['check_purchase_code_exist'];
    $data           = $database->get_purchase_code_info($purchase_code);
    printf(json_encode($data, JSON_PRETTY_PRINT));
}

//Check purchase code exist
if (isset($_GET['check_server_status'])) {
    $purchase_code  = $_GET['check_server_status'];
    $mess = $database->get_connection_status();
    printf(json_encode($mess, JSON_PRETTY_PRINT));
}