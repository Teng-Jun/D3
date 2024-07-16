<?php
session_start();
include "../sessions/sessiontimeout.php";
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || $_SESSION['role'] !== 'admin') {
    header("Location: ../../index.php");
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
                        <h2>Create an Admin Account!</h2>
                        <p>Enter the user's email account!</p>
                    </div>
                </div>
                <div class="right col-lg-6 col-md-6 col-sm-6 col-12">
                    <div class="login-form">
                        <h2>Invite a new admin!</h2>
                        <?php
                        if (isset($_SESSION['success_message'])) {
                            echo "<div class='errorMsg'>";
                            echo "<p class='error'>" . htmlspecialchars($_SESSION['success_message']) . "</p>";
                        }
                        unset($_SESSION['success_message']); // Clear the error message after displaying it

                        if (isset($_SESSION['errorMsg'])) {
                            echo "<div class='errorMsg'>";
                            foreach ($_SESSION['errorMsg'] as $message) {
                                echo "<p class='error'>" . htmlspecialchars($message) . "</p>";
                            }
                            echo "</div>";
                            unset($_SESSION['errorMsg']); // Clear the error message after displaying it
                        }
                        ?>
                        <form action="process/process_create_admin.php" method="post">
                            <!-- Include the CSRF token in the form -->
                            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                            <p>
                                <label for="admin_email">Email: <span>*</span></label>
                                <input type="email" id="admin_email" name="admin_email" placeholder="Enter Email" minlength="10" maxlength="30" required>
                            </p>
                            <div id="html_element"></div>
                            <p>
                                <input type="submit" value="Sign In">
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