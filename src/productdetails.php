<?php
session_start();
include "sessions/sessiontimeout.php";
?>
<html lang="en">
    <head>
        <?php
        include "components/essential.inc.php";
        ?>
        <link rel="stylesheet" href="css/main.css">
        <link rel="stylesheet" href="css/productdetails.css">
    </head>
    <body>
        <?php
        include "components/nav.inc.php";
        ?>
        <main class="container mt-5" id="details-page">
      

        </main>
        <?php
        include "components/footer.inc.php";
        ?>
    </body>
    <link rel="stylesheet" href="css/productdetails.css">
    <script defer src="js/detailcontent.js"></script>
</html>