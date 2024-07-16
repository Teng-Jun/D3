<?php

// Start session
require_once "../sessions/sessiontimeout.php";

// Include the config file
$config = require_once ('config.php');

// Include predis
require_once '../vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');  // Points to the 'src' directory
$dotenv->load();
// Include log function
require_once "log.php";

use Predis\Client as PredisClient;

// Retrieve form data
$customer_email = filter_input(INPUT_POST, 'customer_email', FILTER_SANITIZE_EMAIL);
$customer_pwd = filter_input(INPUT_POST, 'customer_pwd', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

if (!$_SERVER["REQUEST_METHOD"] === "POST" || !isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    display_errorMsg('Please try again');
    header("Location: ../login.php");
    exit;

} else {

    $redis = new PredisClient([
        "scheme" => "tcp",
        "host" => "redis",
        "port" => 6379
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
        logMessage("application.log", "Failed login attempt for user $customer_email from IP $ip");
        $redis->set($blockKey, true);
        $redis->expire($blockKey, 600); // Block for 10 minutes
    }

    // Create database connection
    $conn = new mysqli($config['servername'], $config['username'], $config['password'], $config['dbname']);

    // Check connection
    if ($conn->connect_error) {
        display_errorMsg('Unable to connect to the service, please try again later.');
    }

    $secret = $_ENV['GOOGLE_CAPTCHA_KEY'];  // Replace with your secret key
    $response = $_POST['recaptcha_response'];
    $verify = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret={$secret}&response={$response}");
    $responseData = json_decode($verify);


    // Validate Email
    if (!filter_var($customer_email, FILTER_VALIDATE_EMAIL)) {
        display_errorMsg("Invalid email format.");
    } elseif (strlen($customer_email) < 10 || strlen($customer_email) > 40) {
        display_errorMsg("Email must be between 10 and 40 characters long.");
    }

    // Validate password
    if (strlen($customer_pwd) < 12 || strlen($customer_pwd) > 70) {
        display_errorMsg('Invalid password format.');
    }

    // Validate CSRF token
    if (!isset($_POST['csrf_token'], $_SESSION['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        display_errorMsg('CSRF token mismatch');
    }

    // Unset the CSRF token now that it's been checked
    unset($_SESSION['csrf_token']);

    if ($responseData->success && $responseData->score < 0.5) {  // Choose your threshold
        display_errorMsg('reCAPTCHA verification failed. Are you a robot?');
    }

    if (empty($_SESSION['errorMsg'])) {

        // Prepare SQL statement to avoid SQL injection
        if ($stmt = $conn->prepare("SELECT customer_id, customer_password FROM mechkeys.customer WHERE customer_email = ?")) {
            $stmt->bind_param("s", $customer_email);
            $stmt->execute();
            $result = $stmt->get_result();

            // Check if user exists
            if ($row = $result->fetch_assoc()) {
                // Verify password
                if (password_verify($customer_pwd, $row['customer_password'])) {
                    // Set session variables and redirect to a secure page
                    $_SESSION['customer_email'] = $customer_email;
                    $_SESSION['login_step'] = 'ga_verify';

                    header("Location: ../gaverify.php");
                    exit();
                } else {
                    // Handle when password is incorrect
                    display_errorMsg('Incorrect email or password');
                }
            } else {
                // Handle no user found
                display_errorMsg('Incorrect email or password');
            }
            // Close the statement
            $stmt->close();
        }
    }
    // If there are errors, redirect back to registration
    if (!empty($_SESSION['errorMsg'])) {
        header("Location: ../login.php");
        exit();
    }

    // Close the connection
    $conn->close();
}
?>