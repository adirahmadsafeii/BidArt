<?php
session_start();
require_once 'middlewares/auth_check.php';
require_once 'config/db.php';


$query = "SELECT a.*, ar.title, ar.description, ar.image_path, u.full_name as artist_name,
            TIMESTAMPDIFF(SECOND, NOW(), a.end_time) as time_remaining
            FROM auctions a 
            JOIN artworks ar ON a.artwork_id = ar.id 
            JOIN users u ON ar.artist_id = u.id 
            WHERE a.status = 'active' AND a.end_time > NOW()
            ORDER BY a.end_time ASC";

$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BidArt - Digital Art Auction</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>

<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">BidArt</a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link active" href="index.php">Auctions</a>
                <a class="nav-link" href="gallery.php">Gallery</a>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a class="nav-link" href="views/<?php echo $_SESSION['role']; ?>/dashboard.php">Dashboard</a>
                    <?php if ($_SESSION['role'] === 'artist'): ?>
                        <a class="nav-link" href="views/artist/manage_images.php">Manage Images</a>
                    <?php endif; ?>
                    <a class="nav-link" href="middlewares/logout.php">Logout</a>
                <?php else: ?>
                    <a class="nav-link" href="views/auth/login.php">Login</a>
                    <a class="nav-link" href="views/auth/register.php">Register</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <div class="hero-section text-white py-5">
        <div class="container text-center">
            <h1 class="display-4">Digital Art Auction Platform</h1>
            <p class="lead">Discover and bid on unique digital artworks from talented artists worldwide</p>
        </div>
    </div>

    <!-- Active Auctions -->
    <div class="container my-5">
        <h2 class="mb-4">Active Auctions</h2>
        <div class="row">
            <?php if ($result && $result->num_rows > 0): ?>
                <?php while ($auction = $result->fetch_assoc()): ?>
                    <div class="col-md-4 mb-4">
                        <div class="card auction-card">
                            <img src="<?php echo htmlspecialchars($auction['image_path'] ?: 'assets/images/placeholder.jpg'); ?>"
                                class="card-img-top" alt="<?php echo htmlspecialchars($auction['title']); ?>" style="height: 250px; object-fit: cover;">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($auction['title']); ?></h5>
                                <p class="card-text text-muted">by <?php echo htmlspecialchars($auction['artist_name']); ?></p>
                                <p class="card-text"><?php echo htmlspecialchars(substr($auction['description'], 0, 100)) . '...'; ?></p>

                                <div class="auction-info">
                                    <div class="row">
                                        <div class="col-6">
                                            <small class="text-muted">Current Bid</small>
                                            <div class="fw-bold text-success">Rp. <?php echo number_format($auction['current_price'], 2, ',', '.'); ?></div>
                                        </div>
                                        <div class="col-6">
                                            <small class="text-muted">Time Left</small>
                                            <div class="fw-bold text-danger countdown" data-time="<?php echo $auction['time_remaining']; ?>">
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
                                    </div>
                                </div>

                                <div class="mt-3">
                                    <a href="auction_details.php?id=<?php echo $auction['id']; ?>" class="btn ">View Details</a>
                                    <?php if (isset($_SESSION['user_id']) && $_SESSION['role'] == 'buyer'): ?>
                                        <button class="btn btn-success" onclick="showBidModal(<?php echo $auction['id']; ?>, <?php echo $auction['current_price']; ?>)">Place Bid</button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="col-12">
                    <div class="alert alert-info text-center">
                        <h4>No Active Auctions</h4>
                        <p>Check back later for new artwork auctions!</p>
                    </div>
                </div>
            <?php endif; ?>
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
                }
            });
        }

        setInterval(updateCountdowns, 1000);
    </script>
</body>

</html>