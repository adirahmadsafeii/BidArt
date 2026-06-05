<?php
session_start();
require_once '../../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../auth/login.php');
    exit();
}

// Statistik
$stats = [];

// Total pengguna
$result = mysqli_query($conn, "SELECT COUNT(*) as count FROM users WHERE role != 'admin'");
$stats['total_users'] = mysqli_fetch_assoc($result)['count'];

// Total karya seni
$result = mysqli_query($conn, "SELECT COUNT(*) as count FROM artworks");
$stats['total_artworks'] = mysqli_fetch_assoc($result)['count'];

// Lelang aktif
$result = mysqli_query($conn, "SELECT COUNT(*) as count FROM auctions WHERE status = 'active'");
$stats['active_auctions'] = mysqli_fetch_assoc($result)['count'];

// Total transaksi
$result = mysqli_query($conn, "SELECT COUNT(*) as count FROM transactions");
$stats['total_transactions'] = mysqli_fetch_assoc($result)['count'];

// Lelang terbaru
$recent_auctions = mysqli_query($conn, "
    SELECT a.*, ar.title, u.full_name as artist_name, ar.image_path,
           (SELECT COUNT(*) FROM bids WHERE auction_id = a.id) as bid_count,
           hitung_total_bid(a.id) as total_bid_value
    FROM auctions a
    JOIN artworks ar ON a.artwork_id = ar.id
    JOIN users u ON ar.artist_id = u.id
    ORDER BY a.created_at DESC
    LIMIT 10
");
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard - BidArt</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>

    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="../../index.php">🎨 BidArt Admin</a>
            <div class="navbar-nav ms-auto">
                <span class="navbar-text me-3">Welcome, <?= htmlspecialchars($_SESSION['full_name']) ?></span>
                <a class="nav-link" href="backup.php">Backup Database</a>
                <a class="nav-link" href="../../middlewares/logout.php">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container my-4">
        <h2>System Overview</h2>

        <!-- Flash Messages -->
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($_SESSION['success']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($_SESSION['error']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <!-- Statistik -->
        <div class="row mb-4">
            <?php
            $cards = [
                ['Total Users', 'total_users', 'primary'],
                ['Total Artworks', 'total_artworks', 'success'],
                ['Active Auctions', 'active_auctions', 'warning'],
                ['Total Transactions', 'total_transactions', 'info']
            ];
            foreach ($cards as [$label, $key, $color]) :
            ?>
                <div class="col-md-3">
                    <div class="card bg-<?= $color ?> text-white mb-3">
                        <div class="card-body text-center">
                            <h3><?= $stats[$key] ?></h3>
                            <p class="mb-0"><?= $label ?></p>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Lelang Terbaru -->
        <div class="card mb-4">
            <div class="card-header">
                <h5>Recent Auctions</h5>
            </div>
            <div class="card-body table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Image</th>
                            <th>Title</th>
                            <th>Artist</th>
                            <th>Status</th>
                            <th>Current Price</th>
                            <th>Bids</th>
                            <th>Total Bid Value</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($auction = mysqli_fetch_assoc($recent_auctions)) : ?>
                            <tr>
                                <td><img src="../../<?= htmlspecialchars($auction['image_path']) ?>" alt="<?= htmlspecialchars($auction['title']) ?>" class="rounded" style="width: 50px; height: 50px; object-fit: cover;"></td>
                                <td><?= htmlspecialchars($auction['title']) ?></td>
                                <td><?= htmlspecialchars($auction['artist_name']) ?></td>
                                <td>
                                    <span class="badge <?= $auction['status'] === 'active' ? 'bg-success' : 'bg-secondary' ?>">
                                        <?= ucfirst($auction['status']) ?>
                                    </span>
                                </td>
                                <td>Rp. <?= number_format($auction['current_price'], 2, ',', '.') ?></td>
                                <td><?= $auction['bid_count'] ?></td>
                                <td>Rp. <?= number_format($auction['total_bid_value'], 2, ',', '.') ?></td>
                                <td>
                                    <a href="../../auction_details.php?id=<?= $auction['id'] ?>" class="btn btn-sm ">View</a>
                                    <?php if ($auction['status'] === 'active') : ?>
                                        <form method="POST" action="force_close_auction.php" style="display:inline;" onsubmit="return confirm('Yakin tutup lelang ini?')">
                                            <input type="hidden" name="auction_id" value="<?= $auction['id'] ?>">
                                            <button type="submit" class="btn btn-sm btn-warning">Force Close</button>
                                        </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Database Actions -->
        <div class="row">
            <div class="col-md-6">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5>Database Operations</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="backup.php" onsubmit="return confirm('Mulai backup database?')">
                            <button class="btn btn-success w-100 mb-2">🗄️ Backup Database</button>
                        </form>
                        <form method="POST" action="close_expired_auctions.php" onsubmit="return confirm('Tutup semua lelang yang kadaluarsa?')">
                            <button class="btn btn-warning w-100 mb-2">⏰ Close Expired Auctions</button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5>System Status</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-2"><strong>Database Functions:</strong> <span class="badge bg-success">Active</span></div>
                        <div class="mb-2"><strong>Stored Procedures:</strong> <span class="badge bg-success">Active</span></div>
                        <div class="mb-2"><strong>Triggers:</strong> <span class="badge bg-success">Active</span></div>
                        <div class="mb-2"><strong>Auto Backup:</strong> <span class="badge bg-success">Scheduled</span></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>