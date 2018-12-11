<?php
require_once __DIR__ . '/loader.php';
require_once '../../db/wd_server_database.php';
require_once __DIR__ . '/wd_checker_server.php';
$server = new wd_checker_server();
$server->handleRequest();