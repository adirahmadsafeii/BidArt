<?php
session_start();
require_once '../../config/db.php'; // Contains basic DB connection

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    $role = $_SESSION['role'];
    header("Location: views/{$role}/dashboard.php");
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $full_name = trim($_POST['full_name']);
    $role = $_POST['role'];

    // Validation
    if (empty($username) || empty($email) || empty($password) || empty($full_name) || empty($role)) {
        $error = 'Please fill in all fields';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters';
    } elseif (!in_array($role, ['artist', 'buyer'])) {
        $error = 'Invalid role selected';
    } else {

        if (!$conn) {
            die("Connection failed: " . mysqli_connect_error());
        }

        // Escape inputs
        $username = mysqli_real_escape_string($conn, $username);
        $email = mysqli_real_escape_string($conn, $email);
        $full_name = mysqli_real_escape_string($conn, $full_name);
        $role = mysqli_real_escape_string($conn, $role);

        // Check if username/email exists
        $check_query = "SELECT id FROM users WHERE username = '$username' OR email = '$email'";
        $check_result = mysqli_query($conn, $check_query);

        if (mysqli_num_rows($check_result) > 0) {
            $error = 'Username or email already exists';
        } else {
            // Start transaction
            mysqli_begin_transaction($conn);

            try {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $initial_balance = ($role === 'buyer') ? 1000.00 : 0.00;

                // Insert user
                $insert_query = "INSERT INTO users (username, email, password, full_name, role, balance) 
                               VALUES ('$username', '$email', '$hashed_password', '$full_name', '$role', $initial_balance)";

                if (mysqli_query($conn, $insert_query)) {
                    $user_id = mysqli_insert_id($conn);

                    // If buyer, create transaction
                    if ($role === 'buyer') {
                        $transaction_query = "INSERT INTO transactions (user_id, type, amount, description, status) 
                                            VALUES ($user_id, 'deposit', $initial_balance, 'Initial account balance', 'completed')";
                        mysqli_query($conn, $transaction_query);
                    }

                    // Commit transaction
                    mysqli_commit($conn);
                    $success = 'Registration successful! You can now login.';
                } else {
                    throw new Exception('Registration failed');
                }
            } catch (Exception $e) {
                // Rollback on error
                mysqli_rollback($conn);
                $error = 'Registration failed. Please try again.';
            }
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
    <title>BidArt</title>
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
                    <a class="nav-link" href="login.php">Login</a>
                    <a class="nav-link active" href="register.php">Register</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                <div class="card shadow">
                    <div class="card-body p-4">
                        <h3 class="card-title text-center mb-4">Create Account</h3>

                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                        <?php endif; ?>

                        <?php if ($success): ?>
                            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
                        <?php endif; ?>

                        <form method="POST">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="username" class="form-label">Username</label>
                                    <input type="text" class="form-control" id="username" name="username" required
                                        value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="email" class="form-control" id="email" name="email" required
                                        value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="password" class="form-label">Password</label>
                                    <input type="password" class="form-control" id="password" name="password" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="confirm_password" class="form-label">Confirm Password</label>
                                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="full_name" class="form-label">Full Name</label>
                                <input type="text" class="form-control" id="full_name" name="full_name" required
                                    value="<?php echo isset($_POST['full_name']) ? htmlspecialchars($_POST['full_name']) : ''; ?>">
                            </div>

                            <div class="mb-3">
                                <label for="role" class="form-label">I want to:</label>
                                <select class="form-select" id="role" name="role" required>
                                    <option value="">Select role</option>
                                    <option value="artist" <?php echo (isset($_POST['role']) && $_POST['role'] === 'artist') ? 'selected' : ''; ?>>Sell my artwork</option>
                                    <option value="buyer" <?php echo (isset($_POST['role']) && $_POST['role'] === 'buyer') ? 'selected' : ''; ?>>Buy artwork</option>
                                </select>
                                <small class="text-muted">Buyers receive $1000 initial balance</small>
                            </div>

                            <button type="submit" class="btn  w-100">Register</button>
                        </form>

                        <div class="text-center mt-3">
                            <p>Already have an account? <a href="login.php">Login here</a></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>