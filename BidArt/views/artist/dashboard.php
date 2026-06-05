<?php
session_start();
require_once '../../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'artist') {
    header('Location: ../auth/login.php');
    exit();
}

$artist_id = $_SESSION['user_id'];

// Ambil karya seni milik seniman
$sql = "SELECT a.*, ar.title, ar.description, ar.image_path,
               (SELECT COUNT(*) FROM bids WHERE auction_id = a.id) AS total_bids
        FROM auctions a
        JOIN artworks ar ON a.artwork_id = ar.id
        WHERE ar.artist_id = $artist_id
        ORDER BY a.created_at DESC";

$result = mysqli_query($conn, $sql);
$auctions = mysqli_fetch_all($result, MYSQLI_ASSOC);

// Cek notifikasi
$success = $_GET['success'] ?? '';
$error = $_GET['error'] ?? '';
?>



<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Artist Dashboard - BidArt</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }

        .card {
            border-radius: 10px;
            margin-bottom: 20px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .card-img-top {
            height: 200px;
            object-fit: cover;
            border-radius: 10px 10px 0 0;
        }

        .stats-badge {
            font-size: 0.9rem;
            padding: 5px 10px;
        }

        .form-section {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
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
                    <a class="nav-link active" href="">Dashboard</a>
                    <?php if ($_SESSION['role'] === 'artist'): ?>
                        <a class="nav-link" href="manage_images.php">Manage Images</a>
                    <?php endif; ?>
                    <a class="nav-link" href="../../middlewares/logout.php">Logout</a>
                <?php else: ?>
                    <a class="nav-link" href="../login.php">Login</a>
                    <a class="nav-link" href="register.php">Register</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <div class="container py-4">
        <div class="row">
            <div class="col-lg-8">
                <h2 class="mb-4">My Artworks</h2>

                <?php if ($success): ?>
                    <div class="alert alert-success"><?= $success ?></div>
                <?php endif; ?>

                <?php if ($error): ?>
                    <div class="alert alert-danger"><?= $error ?></div>
                <?php endif; ?>

                <div class="row">
                    <?php if (!empty($auctions)): ?>
                        <?php foreach ($auctions as $auction): ?>
                            <div class="col-md-6">
                                <div class="card h-100">
                                    <img src="../../<?= htmlspecialchars($auction['image_path']) ?>"
                                        class="card-img-top"
                                        alt="<?= htmlspecialchars($auction['title']) ?>">
                                    <div class="card-body">
                                        <h5 class="card-title"><?= htmlspecialchars($auction['title']) ?></h5>
                                        <p class="card-text text-muted"><?= substr(htmlspecialchars($auction['description']), 0, 100) ?>...</p>

                                        <div class="d-flex justify-content-between mb-3">
                                            <span class="badge bg-<?= $auction['status'] === 'active' ? 'success' : 'secondary' ?> stats-badge">
                                                <?= ucfirst($auction['status']) ?>
                                            </span>
                                            <span class="badge bg-primary stats-badge">
                                                Rp. <?= number_format($auction['current_price'], 2, ',', '.') ?>
                                            </span>
                                            <span class="badge bg-info stats-badge">
                                                <?= $auction['total_bids'] ?> bids
                                            </span>
                                        </div>

                                        <div class="d-grid gap-2">
                                            <a href="../../auction_details.php?id=<?= $auction['id'] ?>"
                                                class="btn btn-sm">
                                                View Details
                                            </a>

                                            <?php if ($auction['status'] === 'active') : ?>
                                                <form method="POST" action="close_auction.php" style="display:inline;" onsubmit="return confirm('Yakin tutup lelang ini?')">
                                                    <input type="hidden" name="auction_id" value="<?= $auction['id'] ?>">
                                                    <button type="submit" class=" btn-sm btn-warning">Close</button>
                                                </form>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="col-12">
                            <div class="alert alert-info">
                                <h4>No Artworks Yet</h4>
                                <p>Add your first artwork to get started!</p>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="form-section sticky-top" style="top: 20px;">
                    <h4 class="mb-4">Add New Artwork</h4>

                    <form method="POST" action="upload.php" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label class="form-label">Artwork Title*</label>
                            <input type="text" name="title" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Description*</label>
                            <textarea name="description" class="form-control" rows="3" required></textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Starting Price (Rp.)*</label>
                            <input type="number" name="starting_price" class="form-control" min="1" step="0.01" required>
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Artwork Image*</label>
                            <input type="file" name="artwork_image" class="form-control" accept="image/jpeg,image/png,image/webp" required onchange="previewImage(event)">
                            <small class="text-muted">Max 2MB (JPEG, PNG, WebP)</small>

                            <div class="mt-3 text-center">
                                <img id="imagePreview" style="max-width: 100%; max-height: 200px; display: none;" class="img-thumbnail">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Auction End Time*</label>
                            <input type="datetime-local" name="end_time" class="form-control" required>
                        </div>


                        <button type="submit" name="add_artwork" class="btn btn-success w-100 py-2">
                            Add Artwork & Start Auction
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.querySelector('input[name="artwork_image"]').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const preview = document.getElementById('imagePreview');
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                };
                reader.readAsDataURL(file);
            }
        });

        // document.querySelector('form').addEventListener('submit', function(e) {
        //     const price = document.querySelector('input[name="starting_price"]');
        //     if (price.value <= 0) {
        //         alert('Starting price must be greater than 0');
        //         e.preventDefault();
        //     }
        // });
    </script>
</body>

</html>