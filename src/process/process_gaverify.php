<?php
session_start();
require_once '../vendor/autoload.php';
require_once "log.php";
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');  // Points to the 'src' directory
$dotenv->load();
// use PHPGangsta_GoogleAuthenticator;
require_once '../vendor/phpgangsta/googleauthenticator/PHPGangsta/GoogleAuthenticator.php';

$ip = $_SERVER['REMOTE_ADDR'];
if (!isset($_SESSION['login_step'])) {
    header("Location: register.php");
    exit;
}

function display_errorMsg($message)
{
    if (!isset($_SESSION['errorMsg'])) {
        $_SESSION['errorMsg'] = [];
    }
    $_SESSION['errorMsg'][] = $message;
}

if (!$_SERVER["REQUEST_METHOD"] === "POST" || !isset($_POST['csrf_token'], $_SESSION['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token'] || $_SESSION['login_step'] !== 'ga_verify') {
    display_errorMsg('Please try again');
    header("Location: ../verify.php");
    exit;

} else {

    // Include the config file
    $config = include ('config.php');
    // Create database connection
    $conn = new mysqli($config['servername'], $config['username'], $config['password'], $config['dbname']);


    // Check connection
    if ($conn->connect_error) {
        display_errorMsg('Unable to connect to the service, please try again later.');
    }

    // Retrieve form data
    $customer_code = filter_input(INPUT_POST, 'customer_code', FILTER_SANITIZE_EMAIL);

    $customer_email = $_SESSION['customer_email'];

    // // Unset the CSRF token now that it's been checked
    unset($_SESSION['csrf_token']);

    // Prepare SQL statement to avoid SQL injection
    if ($stmt = $conn->prepare("SELECT * FROM mechkeys.customer WHERE customer_email = ?")) {
        $stmt->bind_param("s", $customer_email);
        $stmt->execute();
        $result = $stmt->get_result();

        // Check if user exists
        if ($row = $result->fetch_assoc()) {

            $ga = new PHPGangsta_GoogleAuthenticator();
            $encryption_key = $_ENV['GOOGLE_ENCRYPTION_KEY']; 
            $encrypted_secret = $row['customer_gacode'];
            $secret = openssl_decrypt($encrypted_secret, 'aes-256-cbc', $encryption_key, 0, $_ENV['GOOGLE_ENCRYPTION_SECRET']);
            $result = $ga->verifyCode($secret, $customer_code, 2); // 2 = 2*30sec clock tolerance

            // Verify password
            if ($result) {
                // Set session variables and redirect to a secure page
                $_SESSION['customer_email'] = $customer_email;
                $_SESSION['token'] = bin2hex(random_bytes(32)); // Generate a new token
                $_SESSION['token_time'] = time();
                $_SESSION['role'] = "customer";
                $_SESSION['customer_id'] = $row['customer_id'];
                $_SESSION['logged_in'] = true;

                logMessage("application.log", "Successful login for user $customer_email from IP $ip");
                unset($_SESSION['login_step']);
                header("Location: ../index.php");
                exit();
            } else {
                // echo "Error preparing statement: " . $conn->error;
                display_errorMsg('Please reenter the code!');
                header("Location: ../gaverify.php");
                exit();
            }
        } else {
            // Handle when password is incorrect
            display_errorMsg('Incorrect email or password');
        }
    } else {
        // Handle no user found
        // echo $_SESSION['$customer_email'];
        // echo $customer_code;
        // echo "Error preparing statement: (" . $conn->errno . ") " . $conn->error;
        display_errorMsg('Incorrect token');
        exit();
    }
    // Close the statement
    $stmt->close();


    // If there are errors, redirect back to registration
    if (!empty($_SESSION['errorMsg'])) {
        header("Location: ../gaverify.php");
        exit();
    }

    // Close the connection
    $conn->close();
}
?>