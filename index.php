<?php
// Silence is golden.
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

$result = array(
    'connected' => true
);
printf(json_encode($result, JSON_PRETTY_PRINT));