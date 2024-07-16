<?php

// Start session
session_start();

if (!isset($_SESSION['registration_step'])) {
    header("Location: register.php");
    exit;
}


if (!$_SERVER["REQUEST_METHOD"] === "POST" || !isset($_POST['csrf_token'], $_SESSION['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token'] || $_SESSION['registration_step'] !== 'qrcode_verify') {
    header("Location: ../qrcode.php");
    exit;

} else {
    // Unset the CSRF token now that it's been checked
    unset($_SESSION['csrf_token']);

    // Include the config file
    $config = include ('config.php');

    // Create database connection
    $conn = new mysqli($config['servername'], $config['username'], $config['password'], $config['dbname']);

    // Check connection
    if ($conn->connect_error) {
        display_errorMsg('Unable to connect to the service, please try again later.');
    }

    function display_errorMsg($message)
    {
        if (!isset($_SESSION['errorMsg'])) {
            $_SESSION['errorMsg'] = [];
        }
        $_SESSION['errorMsg'][] = $message;

    }



    // Retrieve form data
    $qrcode = filter_input(INPUT_POST, 'qr_code', FILTER_SANITIZE_FULL_SPECIAL_CHARS);


    // Security through complexity
    if ($qrcode !== '1') {
        display_errorMsg('Error, please try again.');
    }


    if (empty($_SESSION['errorMsg'])) {
        $customer_email = $_SESSION['customer_email'];
        $customer_gacode = $_SESSION['GA_secret'];

        if ($stmt = $conn->prepare("UPDATE mechkeys.customer SET customer_gacode = ? WHERE customer_email = ?")) {
            $stmt->bind_param("ss", $customer_gacode, $customer_email);
            $stmt->execute();

            if ($stmt->affected_rows > 0) {
                display_errorMsg("Thank you for registering! Please log in to proceed.");
                header("Location: ../login.php");
            } else {
                // echo "No records updated";
                display_errorMsg('Something went wrong, please try again later.');
            }
            unset($_SESSION['customer_email']);
            unset($_SESSION['GA_secret']);
            unset($_SESSION['registration_step']);


            $stmt->close();
            $conn->close();

        }
    } else {
        header("Location: ../qrcode.php");
        exit();
    }

}


?>