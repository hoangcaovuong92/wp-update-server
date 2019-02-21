<?php
// required headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
 
// // include database and object files
// include_once './db/wd_server_database.php';
 
// $database = WD_Server_Database::get_instance();

// //Get customer information by purchase code
// if (isset($_GET['check_active_by_purchase_code'])) {
//     $purchase_code  = $_GET['check_active_by_purchase_code'];
//     $data           = $database->get_customer_info_by_purchase_code($purchase_code);
//     printf(json_encode($data, JSON_PRETTY_PRINT));
// }

// //Check purchase code exist
// if (isset($_GET['check_purchase_code_exist'])) {
//     $purchase_code  = $_GET['check_purchase_code_exist'];
//     $data           = $database->get_purchase_code_info($purchase_code);
//     printf(json_encode($data, JSON_PRETTY_PRINT));
// }

// //Check purchase code exist
// if (isset($_GET['check_server_status'])) {
//     $purchase_code  = $_GET['check_server_status'];
//     $mess = $database->get_connection_status();
//     printf(json_encode($mess, JSON_PRETTY_PRINT));
// }

//Check purchase code exist
if (isset($_GET['verify_purchase'])) {
    $purchase_code  = $_GET['verify_purchase'];
    $mess = array();
    if ($purchase_code) {
        $username = 'tvlgiao';
        // Set API Key  
        $api_key = '7qplew3cz4di546ozrs8dyzyzbpjruza';
        
        // Open cURL channel
        $ch = curl_init();
            
        // Set cURL options
        curl_setopt($ch, CURLOPT_URL, "http://marketplace.envato.com/api/edge/". $username ."/". $api_key ."/verify-purchase:". $purchase_code .".json");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        //Set the user agent
        $agent = 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1)';
        curl_setopt($ch, CURLOPT_USERAGENT, $agent);    

        // Decode returned JSON
        $output = json_decode(curl_exec($ch), true);
        // Close Channel
        curl_close($ch);
        // Return output
        //printf(json_encode($output));
        $mess =  $output;
    }
    printf(json_encode($mess, JSON_PRETTY_PRINT));
}