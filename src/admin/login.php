<?php
session_start();
include "sessions/sessiontimeout.php";
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
                            <h2>Admin Portal</h2>
                            <p>Start your Management by Logging in!</p>
                            <a href="register.php" class="btn">Register</a>
                        </div>
                    </div>
                    <div class="right col-lg-6 col-md-6 col-sm-6 col-12">
                        <div class="login-form">
                            <h2>Login</h2>
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
                            <form action="process/process_login.php" method="post">
                                <!-- Include the CSRF token in the form -->
                                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                <p>
                                    <label for="admin_email">Email: <span>*</span></label>
                                    <input type="email" id="admin_email" name="admin_email" placeholder="Enter Email" minlength="10" maxlength="30" required>
                                </p>
                                <p>
                                    <label for="admin_pwd">Password: <span>*</span></label>
                                    <input type="password" id="admin_pwd" name="admin_pwd" placeholder="Enter Password" minlength="12" maxlength="70" required>
                                </p>
                                <p>
                                    <label for="admin_gacode">Google Authenticator Code: <span>*</span></label>
                                    <input type="password" id="admin_gacode" name="admin_gacode" placeholder="Enter code" required>
                                </p>
                                <div id="html_element"></div>
                                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                <p>
                                    <input type="submit" value="Sign In">
                                </p>
                                <p>
                                    <a href="forgot_password.php">Forgot Password?</a>
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