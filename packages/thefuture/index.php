<?php
require __DIR__ . '/loader.php';
require __DIR__ . '/wd_server_database.php';
require __DIR__ . '/wd_checker_server.php';
$server = new wd_checker_server();
$server->handleRequest();