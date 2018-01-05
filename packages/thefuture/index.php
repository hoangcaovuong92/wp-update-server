<?php
require __DIR__ . '/loader.php';
require __DIR__ . '/wd_libs/WD_Server_Database.php';
require __DIR__ . '/wd_libs/WD_Checker_Server.php';
$server = new WD_Checker_Server();
$server->handleRequest();