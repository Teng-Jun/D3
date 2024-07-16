<?php
include "../sessions/sessiontimeout.php";
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || $_SESSION['role'] !== 'admin') {
    header("Location: ../../index.php");
    exit;
}

include "sessions/sessiontimeout.php";
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
        <main class="container mt-5">
           <div class="row-cols-3 g-3">
                <div class="col-lg-12 col-md-12 col-sm-12 col-12 edit_container">
                    <div id="edit_form">

                    </div>
                </div>
            </div>
        </main>
        <?php
        include "components/footer.inc.php";
        ?>
    </body>
    <script defer src="js/edit.js"></script>
    <link rel="stylesheet" href="css/edit.css">
</html>