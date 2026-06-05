<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="../../index.php">BidArt</a>
        <div class="navbar-nav ms-auto">
            <a class="nav-link" href="index.php">Auctions</a>
            <a class="nav-link" href="image_gallery.php">Gallery</a>
            <?php if (isset($_SESSION['user_id'])): ?>
                <a class="nav-link" href="views/<?php echo $_SESSION['role']; ?>/dashboard.php">Dashboard</a>
                <?php if ($_SESSION['role'] === 'artist'): ?>
                    <a class="nav-link" href="manage_images.php">Manage Images</a>
                <?php endif; ?>
                <a class="nav-link" href="../../middlewares/logout.php">Logout</a>
            <?php else: ?>
                <a class="nav-link" href="views/auth/login.php">Login</a>
                <a class="nav-link" href="register.php">Register</a>
            <?php endif; ?>
        </div>
    </div>
</nav>