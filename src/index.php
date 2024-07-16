<?php
session_start();
include "sessions/sessiontimeout.php";
#header("Content-Security-Policy: default-src 'self'; img-src 'self' https:mechkeys.ddns.net script-src 'self';");
?>
<html lang="en">
    <head>
        <?php
        include "components/essential.inc.php";
        ?>
        <link rel="stylesheet" href="css/index.css">
        <link rel="stylesheet" href="css/main.css">
    </head>

    <body>
        <?php
        include "components/nav.inc.php";
        ?>
        <main class="container-fluid  p-0">
            <div class="banner">
                <img src="images/banner/banner.jpg" class="img-fluid w-100" alt="Banner Image">
                <div class="banner-text">
                    <h1>Welcome to Keyboarder!</h1>
                    <p>Discover the Perfect Keyboard for You</p>
                </div>
            </div>

            <div class="container mt-5 selection">
                <h2>Browse our Selections</h2>
                <div  id="card-deck" class="product-category row d-flex justify-content-center row-cols-3 g-3 mt-1">
                    <!-- Content goes here -->
                </div>
            </div>
        </main>
        <?php
        include "components/footer.inc.php";
        ?>
    </body>
    <script defer src="js/indexcontent.js"></script>
</html>
