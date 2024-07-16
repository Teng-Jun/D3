<?php
// config.php
require_once __DIR__ . '/../vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');  // Points to the 'src' directory
$dotenv->load();
return [
    'servername' => $_ENV['SERVERNAME'],  // or your server name
    'username' => $_ENV['USERNAME'],
    'password' => $_ENV['PASSWORD'],
    'dbname' => $_ENV['DBNAME']
];
?>