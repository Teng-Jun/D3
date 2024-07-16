<?php
session_start();
include "sessions/sessiontimeout.php";
if (!isset($_SESSION['registration_step'])) {
    header("Location: register.php");
    exit;
}
if ($_SESSION['registration_step'] !== 'email_verify') {
    header("Location: register.php");
    exit;
}
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
?>
<html lang="en">
    <head>
        <?php
        include "components/essential.inc.php";
        ?>
        <link rel="stylesheet" href="css/main.css">
        <link rel="stylesheet" href="css/login_reg.css">
    </head>

    <body>
        <?php
        include "components/nav.inc.php";
        ?>
        <main class="container mt-5">
            <div class="login">
                <div class="logincontainer row-cols-3 g-3">
                    <div class="left col-lg-6 col-md-6 col-sm-6 col-12">
                        <div class="login-text">
                            <h2>Get Verified!</h2>
                            <p>Enter the verification code that was sent to your email</p>
                        </div>
                    </div>
                    <div class="right col-lg-6 col-md-6 col-sm-6 col-12">
                        <div class="verify-form">
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
                            <form action="process/process_verify.php" method="post">
                                <!-- Include the CSRF token in the form -->
                                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                <p>
                                    <label for="customer_code">Verification Code: <span>*</span></label>
                                    <input type="text" id="customer_code" name="customer_code" placeholder="Enter the Verification Code" minlength="6" maxlength="6" required>
                                </p>
                                <div id="html_element"></div>
                                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                <p>
                                    <input type="submit" value="Verify">
                                </p>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </main>
        <?php
        include "components/footer.inc.php";
        ?>
    </body>
</html>