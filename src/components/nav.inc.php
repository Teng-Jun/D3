<nav class="navbar navbar-expand-sm navbar-dark bg-dark primary-color" aria-label="Third navbar example">
    <div class="container-fluid">
        <a class="navbar-brand" href="index.php">MechKeys</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarToggler" aria-controls="navbarsExample03" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarToggler">
            <ul class="navbar-nav me-auto mb-2 mb-sm-0">
                <!-- <li class="nav-item">
                    <a class="nav-link active" href="index.php">Home <span class="sr-only"></a>
                </li> -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="dropdown03" data-bs-toggle="dropdown" aria-expanded="false">Products</a>
                    <ul class="dropdown-menu" aria-labelledby="dropdown03">
                        <li><a class="dropdown-item" href="barebone.php">Barebone</a></li>
                        <li><a class="dropdown-item" href="cables.php">Cables</a></li>
                        <li><a class="dropdown-item" href="keyboard.php">Keyboard</a></li>
                        <li><a class="dropdown-item" href="keycaps.php">Keycaps</a></li>
                        <li><a class="dropdown-item" href="switches.php">Switches</a></li>
                    </ul>
                </li>
                <!--<li class="nav-item">
                    <a class="nav-link" href="aboutus.php">About Us</a>
                </li> -->
            </ul>
            <?php
            if (@$_SESSION['role'] == "customer") {
                ?>
                <ul class="navbar-nav ml-auto">

                    <li class="nav-item">
                        <a class="nav-link" href="cart.php">Cart</a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" href="profile.php">Profile</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">Logout</a> <!-- redirect to login.php after logging out-->
                    </li>
                </ul>
                <?php
            } else {
                ?>
                <ul class="navbar-nav ml-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="login.php">Log In</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="register.php">Register</a>
                    </li>
                </ul>
                <?php
            }
            ?>
        </div>
    </div>
</nav>