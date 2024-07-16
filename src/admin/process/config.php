<?php
// config.php

// Use the correct constant for the directory
require_once __DIR__ . '/../../vendor/autoload.php';
// Your other configuration code here
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../..');  // Go up one directory to reach the 'src' directory where .env resides
$dotenv->load();
return [
    'servername' => $_ENV['SERVERNAME'],  // or your server name
    'username' => $_ENV['ADMIN_USERNAME'],
    'password' => $_ENV['ADMIN_PASSWORD'],
    'dbname' => $_ENV['DBNAME']
];
?>