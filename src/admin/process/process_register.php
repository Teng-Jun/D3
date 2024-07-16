<?php
// Start session
session_start();

// Include the config file
$config = include('config.php');

require_once '../../vendor/autoload.php';

use Predis\Client as PredisClient;




$redis = new PredisClient([
    "scheme" => "tcp",
    "host"   => "redis",
    "port"   => 6379
]);

// Get client IP
$ip = $_SERVER['REMOTE_ADDR'];
$attemptKey = "login_attempts:$ip";
$blockKey = "blocked:$ip";

// Check if IP is currently blocked
if ($redis->get($blockKey)) {
    $delay = 30; // Delay in seconds
    sleep($delay); // Halt script execution to slow down the response
    echo "Access temporarily suspended due to unusual activity.";
    exit;
}

// Increment login attempts
$redis->incr($attemptKey);
$redis->expire($attemptKey, 30); // Expire in 30 seconds

// Check attempts count
$attempts = $redis->get($attemptKey);
if ($attempts > 5) {
    logMessage("application.log", "Failed register attempt for admin $admin_email from IP $ip");      
    $redis->set($blockKey, true);
    $redis->expire($blockKey, 600); // Block for 10 minutes
}

header("Location: ../register.php");
exit();

