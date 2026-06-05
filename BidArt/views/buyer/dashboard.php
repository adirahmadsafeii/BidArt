<?php
session_start();
require_once '../../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'buyer') {
    header('Location: ../../login.php');
    exit();
}

$user_id = (int)$_SESSION['user_id'];

// Ambil saldo user
$user_balance_query = "SELECT balance FROM users WHERE id = $user_id";
$user_balance_result = mysqli_query($conn, $user_balance_query);

if ($user_balance_result && mysqli_num_rows($user_balance_result) > 0) {
    $user_balance = mysqli_fetch_assoc($user_balance_result)['balance'];
} else {
    $user_balance = 0;
}

// Ambil data semua bid milik user
$query = "SELECT b.*, a.status as auction_status, ar.title, ar.image_path, u.full_name as artist_name,
                 a.current_price, a.end_time,
                 (SELECT MAX(bid_amount) FROM bids WHERE auction_id = b.auction_id) as highest_bid
          FROM bids b
          JOIN auctions a ON b.auction_id = a.id
          JOIN artworks ar ON a.artwork_id = ar.id
          JOIN users u ON ar.artist_id = u.id
          WHERE b.bidder_id = $user_id
          ORDER BY b.bid_time DESC";

$result = mysqli_query($conn, $query);

$bids = [];
if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $bids[] = $row;
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buyer Dashboard - BidArt</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>

<body>


    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="../../index.php">BidArt</a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link " href="../../index.php">Auctions</a>
                <a class="nav-link" href="../../gallery.php">Gallery</a>
                <a class="nav-link active" href="">Dashboard</a>
                <a class="nav-link" href="../../middlewares/logout.php">Logout</a>

            </div>
        </div>
    </nav>
    <div class="hero-section text-white py-5">
        <div class="container text-end">
            <h1 class="navbar-text me-3">Welcome, <?php echo htmlspecialchars($_SESSION['full_name']); ?></h1>
            <span class="navbar-text me-3">Balance: Rp. <?php echo number_format($user_balance, 2, ',', '.'); ?></span>
        </div>
    </div>

    <div class="container my-4">
        <div class="row">
            <div class="col-md-8">
                <h2>My Bids</h2>
                <?php if (!empty($bids)): ?>
                    <div class="row">
                        <?php foreach ($bids as $bid): ?>
                            <div class="col-md-6 mb-4">
                                <div class="card">
                                    <img src="../../<?php echo htmlspecialchars($bid['image_path']); ?>"
                                        class="card-img-top" alt="<?php echo htmlspecialchars($bid['title']); ?>"
                                        style="height: 200px; object-fit: cover;">
                                    <div class="card-body">
                                        <h5 class="card-title"><?php echo htmlspecialchars($bid['title']); ?></h5>
                                        <p class="card-text text-muted">by <?php echo htmlspecialchars($bid['artist_name']); ?></p>

                                        <div class="row text-center mb-3">
                                            <div class="col-4">
                                                <small class="text-muted">My Bid</small>
                                                <div class="fw-bold text-primary">Rp. <?php echo number_format($bid['bid_amount'], 2, ',', '.'); ?></div>
                                            </div>
                                            <div class="col-4">
                                                <small class="text-muted">Highest Bid</small>
                                                <div class="fw-bold text-success">Rp. <?php echo number_format($bid['highest_bid'], 2, ',', '.'); ?></div>
                                            </div>
                                            <div class="col-4">
                                                <small class="text-muted">Status</small>
                                                <div class="fw-bold <?php echo $bid['auction_status'] === 'active' ? 'text-success' : 'text-secondary'; ?>">
                                                    <?php echo ucfirst($bid['auction_status']); ?>
                                                </div>
                                            </div>
                                        </div>

                                        <?php if ($bid['bid_amount'] == $bid['highest_bid'] && $bid['auction_status'] === 'active'): ?>
                                            <div class="alert alert-success py-2">
                                                <small>You're currently winning!</small>
                                            </div>
                                        <?php elseif ($bid['bid_amount'] < $bid['highest_bid'] && $bid['auction_status'] === 'active'): ?>
                                            <div class="alert alert-warning py-2">
                                                <small>You've been outbid</small>
                                            </div>
                                        <?php endif; ?>

                                        <div class="mt-3">
                                            <a href="../../auction_details.php?id=<?php echo $bid['auction_id']; ?>" class="btn  btn-sm">View Auction</a>
                                            <?php if ($bid['auction_status'] === 'active' && strtotime($bid['end_time']) > time()): ?>
                                                <button class="btn btn-success btn-sm" onclick="showBidModal(<?php echo $bid['auction_id']; ?>, <?php echo $bid['current_price']; ?>)">Bid Again</button>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info">
                        <h4>No Bids Yet</h4>
                        <p>Start bidding on artworks to see them here!</p>
                        <a href="../../index.php" class="btn ">Browse Auctions</a>
                    </div>
                <?php endif; ?>
            </div>

            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h5>Account Balance</h5>
                    </div>
                    <div class="card-body text-center">
                        <h3 class="text-success">Rp. <?php echo number_format($user_balance, 2, ',', '.'); ?></h3>
                        <button class="btn " data-bs-toggle="modal" data-bs-target="#depositModal">Add Funds</button>
                    </div>
                </div>

                <div class="card mt-4">
                    <div class="card-header">
                        <h5>Quick Stats</h5>
                    </div>
                    <div class="card-body">
                        <?php
                        // Get user statistics
                        $stats_query = "SELECT 
                                          COUNT(*) as total_bids,
                                          COUNT(CASE WHEN a.winner_id = ? THEN 1 END) as won_auctions,
                                          SUM(b.bid_amount) as total_bid_amount
                                        FROM bids b
                                        JOIN auctions a ON b.auction_id = a.id
                                        WHERE b.bidder_id = ?";
                        $stmt = $conn->prepare($stats_query);
                        $stmt->bind_param("ii", $user_id, $user_id);
                        $stmt->execute();
                        $stats = $stmt->get_result()->fetch_assoc();
                        ?>

                        <div class="row text-center">
                            <div class="col-12 mb-3">
                                <small class="text-muted">Total Bids</small>
                                <div class="fw-bold"><?php echo $stats['total_bids']; ?></div>
                            </div>
                            <div class="col-12 mb-3">
                                <small class="text-muted">Won Auctions</small>
                                <div class="fw-bold text-success"><?php echo $stats['won_auctions']; ?></div>
                            </div>
                            <div class="col-12">
                                <small class="text-muted">Total Bid Amount</small>
                                <div class="fw-bold">Rp. <?php echo number_format($stats['total_bid_amount'], 2, ',', '.'); ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Deposit Modal -->
    <div class="modal fade" id="depositModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add Funds</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="add_funds.php">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="amount" class="form-label">Amount (Rp)</label>
                            <input type="number" class="form-control" id="amount" name="amount" step="0.01" min="1" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Payment Method</label>
                            <select class="form-control" name="payment_method" required>
                                <option value="credit_card">Credit Card</option>
                                <option value="paypal">PayPal</option>
                                <option value="bank_transfer">Bank Transfer</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success">Add Funds</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>