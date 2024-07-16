<?php
session_start();
include "sessions/sessiontimeout.php";
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
?>
<html lang="en">
<head>
    <?php include "components/essential.inc.php"; ?>
    <link rel="stylesheet" href="css/main.css">
    <link rel="stylesheet" href="css/login_reg.css">
</head>
<body>
<?php include "components/nav.inc.php"; ?>
<main class="container mt-5">
    <div class="login">
        <div class="logincontainer row-cols-3 g-3">
            <div class="right col-lg-6 col-md-6 col-sm-6 col-12">
                <div class="login-form">
                    <?php
                    if (isset($_SESSION['errorMsg'])) {
                        echo "<div class='errorMsg'>";
                        foreach ($_SESSION['errorMsg'] as $message) {
                            echo "<p class='error'>" . htmlspecialchars($message) . "</p>";
                        }
                        echo "</div>";
                        unset($_SESSION['errorMsg']); // Clear the error message after displaying it
                    }
                    ?>
                    <h2>Forgot Password?</h2>
                    <p>Reset your password:</p>
                    <ul>
                        <li><a href="forgot_password_email.php">Reset via Email</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</main>
<?php include "components/footer.inc.php"; ?>
</body>
</html>