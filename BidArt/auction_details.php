<?php
session_start();
require_once 'middlewares/auth_check.php';
require_once 'config/db.php';

$auction_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$auction_id) {
    header('Location: index.php');
    exit();
}

// Ambil detail lelang + karya + seniman
$query = "SELECT a.*, ar.title, ar.description, ar.image_path, 
                 u.full_name AS artist_name, u.id AS artist_id,
                 TIMESTAMPDIFF(SECOND, NOW(), a.end_time) AS time_remaining,
                 (SELECT COUNT(*) FROM bids WHERE auction_id = a.id) AS total_bids
          FROM auctions a
          JOIN artworks ar ON a.artwork_id = ar.id
          JOIN users u ON ar.artist_id = u.id
          WHERE a.id = $auction_id
          LIMIT 1";

$result = mysqli_query($conn, $query);
$auction = mysqli_fetch_assoc($result);

if (!$auction) {
    header('Location: index.php');
    exit();
}

$total_bid_amount = hitungTotalBid($auction_id);

// Ambil riwayat bid
$bid_query = "SELECT b.*, u.username, u.full_name 
              FROM bids b
              JOIN users u ON b.bidder_id = u.id
              WHERE b.auction_id = $auction_id
              ORDER BY b.bid_amount DESC, b.bid_time ASC";

$bids = mysqli_query($conn, $bid_query);

// Cek apakah user saat ini adalah penawar tertinggi
$is_winning = false;
if (isset($_SESSION['user_id'])) {
    $top_bidder = getHighestBid($auction_id);
    if ($top_bidder == $_SESSION['user_id']) {
        $is_winning = true;
    }
}

?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($auction['title']); ?> - BidArt</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">BidArt</a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="index.php">Auctions</a>
                <a class="nav-link" href="gallery.php">Gallery</a>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a class="nav-link" href="views/<?php echo $_SESSION['role']; ?>/dashboard.php">Dashboard</a>
                    <?php if ($_SESSION['role'] === 'artist'): ?>
                        <a class="nav-link" href="views/artist/manage_images.php">Manage Images</a>
                    <?php endif; ?>
                    <a class="nav-link" href="middlewares/logout.php">Logout</a>
                <?php else: ?>
                    <a class="nav-link" href="views/auth/login.php">Login</a>
                    <a class="nav-link" href="register.php">Register</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <div class="container my-4">
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success"><?php echo $_SESSION['success'];
                                                unset($_SESSION['success']); ?></div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger"><?php echo $_SESSION['error'];
                                            unset($_SESSION['error']); ?></div>
        <?php endif; ?>

        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <img src="<?php echo htmlspecialchars($auction['image_path'] ?: 'assets/images/placeholder.jpg'); ?>"
                        class="card-img-top" alt="<?php echo htmlspecialchars($auction['title']); ?>"
                        style="height: 400px; object-fit: cover;">
                    <div class="card-body">
                        <h2 class="card-title"><?php echo htmlspecialchars($auction['title']); ?></h2>
                        <p class="text-muted mb-3">by <strong><?php echo htmlspecialchars($auction['artist_name']); ?></strong></p>
                        <p class="card-text"><?php echo nl2br(htmlspecialchars($auction['description'])); ?></p>
                    </div>
                </div>

                <!-- Bid History -->
                <div class="card mt-4">
                    <div class="card-header">
                        <h5>Bid History (<?php echo $auction['total_bids']; ?> bids)</h5>
                        <!-- IMPLEMENTASI FUNCTION - Display total bid amount -->
                        <small class="text-muted">Total Bid Amount: Rp. <?php echo number_format($total_bid_amount, 2, ',', '.'); ?></small>
                    </div>
                    <div class="card-body">
                        <?php if ($bids && $bids->num_rows > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Bidder</th>
                                            <th>Amount</th>
                                            <th>Time</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $rank = 1;
                                        while ($bid = $bids->fetch_assoc()):
                                        ?>
                                            <tr class="<?php echo $rank === 1 ? 'table-success' : ''; ?>">
                                                <td>
                                                    <?php echo htmlspecialchars($bid['full_name']); ?>
                                                    <?php if ($rank === 1): ?>
                                                        <span class="badge bg-success ms-2">Highest</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="fw-bold">Rp. <?php echo number_format($bid['bid_amount'], 2, ',', '.'); ?></td>
                                                <td><?php echo date('M j, Y g:i A', strtotime($bid['bid_time'])); ?></td>
                                                <td>
                                                    <?php if ($rank === 1 && $auction['status'] === 'active'): ?>
                                                        <span class="badge bg-success">Leading</span>
                                                    <?php elseif ($auction['status'] === 'closed' && $rank === 1): ?>
                                                        <span class="badge bg-primary">Winner</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-secondary">Outbid</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php
                                            $rank++;
                                        endwhile;
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="text-center text-muted py-4">
                                <h5>No bids yet</h5>
                                <p>Be the first to bid on this artwork!</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <!-- Auction Info -->
                <div class="card">
                    <div class="card-header">
                        <h5>Auction Details</h5>
                    </div>
                    <div class="card-body">
                        <div class="row text-center mb-3">
                            <div class="col-6">
                                <small class="text-muted">Starting Price</small>
                                <div class="fw-bold">Rp. <?php echo number_format($auction['starting_price'], 2, ',', '.'); ?></div>
                            </div>
                            <div class="col-6">
                                <small class="text-muted">Current Price</small>
                                <div class="fw-bold text-success">Rp. <?php echo number_format($auction['current_price'], 2, ',', '.'); ?></div>
                            </div>
                        </div>

                        <div class="text-center mb-3">
                            <small class="text-muted">Status</small>
                            <div class="fw-bold">
                                <span class="badge <?php echo $auction['status'] === 'active' ? 'bg-success' : 'bg-secondary'; ?> fs-6">
                                    <?php echo ucfirst($auction['status']); ?>
                                </span>
                            </div>
                        </div>

                        <?php if ($auction['status'] === 'active'): ?>
                            <div class="text-center mb-3">
                                <small class="text-muted">Time Remaining</small>
                                <div class="fw-bold text-danger countdown fs-5" data-time="<?php echo $auction['time_remaining']; ?>">
                                    <?php
                                    $time_left = $auction['time_remaining'];
                                    if ($time_left > 0) {
                                        $days = floor($time_left / 86400);
                                        $hours = floor(($time_left % 86400) / 3600);
                                        $minutes = floor(($time_left % 3600) / 60);
                                        echo "{$days}d {$hours}h {$minutes}m";
                                    } else {
                                        echo "Ended";
                                    }
                                    ?>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if (isset($_SESSION['user_id'])): ?>
                            <?php if ($_SESSION['role'] === 'buyer' && $auction['status'] === 'active' && $auction['time_remaining'] > 0): ?>
                                <?php if ($is_winning): ?>
                                    <div class="alert alert-success text-center py-2 mb-3">
                                        <small>🏆 You're currently winning!</small>
                                    </div>
                                <?php endif; ?>

                                <button class="btn btn-success w-100 mb-2" onclick="showBidModal(<?php echo $auction['id']; ?>, <?php echo $auction['current_price']; ?>)">
                                    Place Bid
                                </button>

                                <div class="text-center">
                                    <small class="text-muted">Your Balance: Rp. <?php echo number_format($_SESSION['balance'], 2, ',', '.'); ?></small>
                                </div>
                            <?php elseif ($_SESSION['user_id'] == $auction['artist_id']): ?>
                                <div class="alert alert-info text-center">
                                    <small>This is your artwork</small>
                                </div>
                            <?php elseif ($_SESSION['role'] !== 'buyer'): ?>
                                <div class="alert alert-warning text-center">
                                    <small>Only buyers can place bids</small>
                                </div>
                            <?php endif; ?>
                        <?php else: ?>
                            <a href="views/auth/login.php" class="btn w-100">Login to Bid</a>
                        <?php endif; ?>

                        <?php if ($auction['status'] === 'closed' && $auction['winner_id']): ?>
                            <div class="alert alert-success text-center">
                                <strong>Auction Ended</strong><br>
                                <small>Winner will be contacted for payment</small>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Artist Info -->
                <div class="card mt-3">
                    <div class="card-header">
                        <h6>About the Artist</h6>
                    </div>
                    <div class="card-body">
                        <h6><?php echo htmlspecialchars($auction['artist_name']); ?></h6>
                        <?php
                        // Get artist stats
                        $artist_stats = $conn->query("
                            SELECT 
                                COUNT(DISTINCT ar.id) as total_artworks,
                                COUNT(DISTINCT a.id) as total_auctions,
                                COALESCE(AVG(a.current_price), 0) as avg_price
                            FROM users u
                            LEFT JOIN artworks ar ON u.id = ar.artist_id
                            LEFT JOIN auctions a ON ar.id = a.artwork_id
                            WHERE u.id = {$auction['artist_id']}
                        ")->fetch_assoc();
                        ?>
                        <div class="row text-center">
                            <div class="col-4">
                                <small class="text-muted">Artworks</small>
                                <div class="fw-bold"><?php echo $artist_stats['total_artworks']; ?></div>
                            </div>
                            <div class="col-4">
                                <small class="text-muted">Auctions</small>
                                <div class="fw-bold"><?php echo $artist_stats['total_auctions']; ?></div>
                            </div>
                            <div class="col-4">
                                <small class="text-muted">Avg Price</small>
                                <div class="fw-bold">Rp. <?php echo number_format($artist_stats['avg_price'], 0); ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bid Modal -->
    <div class="modal fade" id="bidModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Place Your Bid</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="bidForm" method="POST" action="views/buyer/place_bid.php">
                    <div class="modal-body">
                        <input type="hidden" id="auction_id" name="auction_id">
                        <div class="mb-3">
                            <label class="form-label">Current Highest Bid</label>
                            <div class="form-control-plaintext fw-bold text-success" id="current_price"></div>
                        </div>
                        <div class="mb-3">
                            <label for="bid_amount" class="form-label">Your Bid Amount (Rp)</label>
                            <input type="number" class="form-control" id="bid_amount" name="bid_amount" step="0.01" required>
                            <div class="form-text">Enter an amount higher than the current bid</div>
                        </div>
                        <div class="alert alert-info">
                            <small>
                                <strong>Note:</strong> Your bid amount will be temporarily held from your balance until the auction ends.
                            </small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success">Place Bid</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function showBidModal(auctionId, currentPrice) {
            document.getElementById('auction_id').value = auctionId;
            document.getElementById('current_price').textContent = '$' + parseFloat(currentPrice).toFixed(2);
            document.getElementById('bid_amount').min = (parseFloat(currentPrice) + 0.01).toFixed(2);
            new bootstrap.Modal(document.getElementById('bidModal')).show();
        }

        // Countdown timer
        function updateCountdowns() {
            document.querySelectorAll('.countdown').forEach(function(element) {
                let timeLeft = parseInt(element.dataset.time);
                if (timeLeft > 0) {
                    timeLeft--;
                    element.dataset.time = timeLeft;

                    const days = Math.floor(timeLeft / 86400);
                    const hours = Math.floor((timeLeft % 86400) / 3600);
                    const minutes = Math.floor((timeLeft % 3600) / 60);
                    const seconds = timeLeft % 60;

                    element.textContent = `${days}d ${hours}h ${minutes}m ${seconds}s`;
                } else {
                    element.textContent = 'Ended';
                    element.classList.add('text-muted');
                    // Reload page when auction ends
                    setTimeout(() => location.reload(), 2000);
                }
            });
        }

        setInterval(updateCountdowns, 1000);
    </script>
</body>

</html>