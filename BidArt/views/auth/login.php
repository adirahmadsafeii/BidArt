<?php
session_start();
require_once '../../config/db.php'; // This will contain our simplified DB connection

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    $role = $_SESSION['role'];
    header("Location: ../{$role}/dashboard.php");
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    if (empty($username) || empty($password)) {
        $error = 'Please fill in all fields';
    } else {
        $username = mysqli_real_escape_string($conn, $username);

        // Query without prepared statements (simpler but less secure)
        $query = "SELECT id, username, email, password, full_name, role, balance 
                 FROM users WHERE username = '$username'";
        $result = mysqli_query($conn, $query);

        if ($result && mysqli_num_rows($result) > 0) {
            $user = mysqli_fetch_assoc($result);

            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['full_name'] = $user['full_name'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['balance'] = $user['balance'];

                header("Location: ../{$user['role']}/dashboard.php");
                exit();
            } else {
                $error = 'Invalid username or password';
            }
        } else {
            $error = 'Invalid username or password';
        }

        mysqli_close($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - BidArt</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/style.css">
    <style>
        body {
            background-color: #f8f9fa;
        }

        .card {
            border-radius: 10px;
        }
    </style>
</head>

<body>


    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="../../index.php">BidArt</a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="../../index.php">Auctions</a>
                <a class="nav-link" href="../../gallery.php">Gallery</a>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a class="nav-link" href="views/<?php echo $_SESSION['role']; ?>/dashboard.php">Dashboard</a>
                    <?php if ($_SESSION['role'] === 'artist'): ?>
                        <a class="nav-link" href="manage_images.php">Manage Images</a>
                    <?php endif; ?>
                    <a class="nav-link" href="../../middlewares/logout.php">Logout</a>
                <?php else: ?>
                    <a class="nav-link active" href="login.php">Login</a>
                    <a class="nav-link" href="register.php">Register</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-4">
                <div class="card shadow">
                    <div class="card-body p-4">
                        <h3 class="card-title text-center mb-4">Login</h3>

                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                        <?php endif; ?>

                        <form method="POST">
                            <div class="mb-3">
                                <label for="username" class="form-label">Username</label>
                                <input type="username" class="form-control" id="username" name="username" required
                                    value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                            <button type="submit" class="btn  w-100">Login</button>
                        </form>

                        <div class="text-center mt-3">
                            <p>No account? <a href="register.php">Register here</a></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>