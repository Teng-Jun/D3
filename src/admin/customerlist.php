<?php
session_start();
include "../sessions/sessiontimeout.php";
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || $_SESSION['role'] !== 'admin') {
    header("Location: ../../index.php");
    exit;
}

?>
<html lang="en">
    <head>
        <?php
        include "components/essential.inc.php";
        ?>
        <link rel="stylesheet" href="css/main.css">
        <link rel="stylesheet" href="css/itemlist.css">
    </head>

    <body>
        <?php
        include "components/nav.inc.php";
        ?>
        <main class="container">
            <h1 class="display-4">Customer List</h1>
            <div class="filter_panel">
            </div>
            <div class=" row row-cols-3 g-3">
                <div  class="col-lg-12 col-md-12 col-sm-12 col-12 table-responsive">
                    <table id="item-list" class="table">

                    </table>
                </div>
            </div>
        </main>
        <?php
        include "components/footer.inc.php";
        ?>
    </body>
    <script defer src="js/customerlist.js"></script>
</html>