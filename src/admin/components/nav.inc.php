<nav class="navbar navbar-expand-sm navbar-dark bg-dark primary-color" aria-label="Third navbar example">
    <div class="container-fluid">
        <a class="navbar-brand" href="index.php">MechKeys</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarToggler" aria-controls="navbarsExample03" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarToggler">
            <?php    
            if (@$_SESSION['role'] == "admin") {
                ?>
                <ul class="navbar-nav me-auto mb-2 mb-sm-0">
                    <!-- <li class="nav-item">
                        <a class="nav-link active" href="index.php">Home</a>
                    </li> -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="dropdown03" data-bs-toggle="dropdown" aria-expanded="false">Manage List</a>
                        <ul class="dropdown-menu" aria-labelledby="dropdown03">
                            <li><a class="dropdown-item" href="customerlist.php">Customer List</a></li>
                            <li><a class="dropdown-item" href="orderlist.php">Order List</a></li>
                            <li><a class="dropdown-item" href="productlist.php">Product List</a></li>
                            <li><a class="dropdown-item" href="createadmin.php">Invite an Admin</a></li>
                            <li><a class="dropdown-item" href="adminlist.php">Admin List</a></li>
                        </ul>
                    </li>
                </ul>
                <ul class="navbar-nav ml-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="profile.php">Profile</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">Logout</a>
                    </li>
                </ul>
                <?php
            } else {
                // Navigation for non-logged in users
                ?>
                <ul class="navbar-nav ml-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="login.php">Log In</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="register.php">Register</a> <!-- Link to register page -->
                    </li>
                </ul>
                <?php
            }
            ?>
        </div>
    </div>
</nav>
