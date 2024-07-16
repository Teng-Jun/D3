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
                    <h1>Welcome Admin Keyboarder!</h1>
                    <p>Discover the Perfect Keyboard for You</p>
                </div>
            </div>

            <div class="container mt-5 selection">
                <h2>Manage List</h2>
                <div  id="card-deck" class="product-category row d-flex justify-content-center row-cols-3 g-3 mt-1">
                    <!-- Content goes here -->
                    <div class="card_container col-lg-3 col-md-6 col-sm-6 col-12">
                        <a href="customerlist.php">
                            <div class="card h-100">
                                <img class="card-img-top" src="images//home_card_user.jpg" alt="Card image cap" loading="lazy">
                                <div class="card-body">
                                    <h5 class="card-title text-center">Customer List</h5>
                                </div>
                            </div>
                        </a>
                    </div>

                    <div class="card_container col-lg-3 col-md-6 col-sm-6 col-12">
                        <a href="orderlist.php">
                            <div class="card h-100">
                                <img class="card-img-top" src="images/home_card_order.jpg" alt="Card image cap" loading="lazy">
                                <div class="card-body">
                                    <h5 class="card-title text-center">Order List</h5>
                                </div>
                            </div>
                        </a>
                    </div>

                    <div class="card_container col-lg-3 col-md-6 col-sm-6 col-12">
                        <a href="productlist.php">
                            <div class="card h-100">
                                <img class="card-img-top" src="images/home_card_product.jpg" alt="Card image cap" loading="lazy">
                                <div class="card-body">
                                    <h5 class="card-title text-center">Product List</h5>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </main>
        <?php
        include "components/footer.inc.php";
        ?>
    </body>
    <!-- <script defer src="js/indexcontent.js"></script> -->
</html>