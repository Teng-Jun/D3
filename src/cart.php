<?php
session_start();
include "sessions/sessiontimeout.php";
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.php");
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
</head>

<body>
    <?php
    include "components/nav.inc.php";
?>
    <main class="container mt-3">
        <h1> Shopping Cart</h1>
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
        <form action="checkout.php" method="post" class="mt-3">
            <div class="cart-list" id="cart-list">
            </div>
            <?php
            if (empty($_SESSION['cart'])) {
                echo "<h2>Cart is empty!<h2>";
            } else {
                echo "<p class=mt-3>" .
                    "<input class='purchase-button addtocart' type='submit' value='Purchase'>" .
                    "</p>";
                echo '<input type="hidden" name="csrf_token" value="' . $_SESSION['csrf_token'] . '">';


            }
            ?>
            </p>
        </form>
    </main>
    <?php
    include "components/footer.inc.php";
    ?>
</body>
<link rel="stylesheet" href="css/cart.css">
<script defer src="js/cart.js"></script>

</html>