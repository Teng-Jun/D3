<?php
// Set session cookie parameters before starting the session
if (session_status() === PHP_SESSION_NONE) {
    $cookieParams = session_get_cookie_params();
    session_set_cookie_params([
        'lifetime' => 1800,           // Set cookie to expire after 1800 seconds (30 minutes)
        'path' => $cookieParams["path"],
        'domain' => $cookieParams["domain"],
        'secure' => true,            // Ensure cookies are sent over HTTPS
        'httponly' => true,          // Make cookies accessible only through the HTTP protocol
        'samesite' => 'Lax'          // Mitigate the risk of cross-origin information leakage
    ]);
    session_start();
}

// Regenerate session ID regularly to prevent session fixation
if (!isset($_SESSION['created'])) {
    $_SESSION['created'] = time();
} else if (time() - $_SESSION['created'] > 1800) { // 30 minutes
    session_regenerate_id(true);   // Invalidate old session ID
    $_SESSION['created'] = time(); // Update creation time
}

// Handle session timeout
$inactive = 1800; // 30 minutes
if (isset($_SESSION["token_time"]) && (time() - $_SESSION["token_time"] > $inactive)) {
    // Properly destroy the session
    session_unset();               // Unset $_SESSION variable for the run-time
    session_destroy();             // Destroy session data in storage

    // Clear the session cookie
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]);
    }

    header("Location: login.php");
    exit();
}
$_SESSION['token_time'] = time(); // Reset session time on each active request
?>
