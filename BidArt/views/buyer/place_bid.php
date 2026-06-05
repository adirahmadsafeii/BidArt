<?php
session_start();
require_once '../../config/db.php';

// Check jika user belum login atau bukan buyer
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'buyer') {
    header('Location: views/auth/login.php');
    exit();
}

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $auction_id = (int)$_POST['auction_id'];
        $bidder_id = (int)$_SESSION['user_id'];
        $bid_amount = (float)$_POST['bid_amount'];

        // Langsung query
        $query = "CALL sp_place_bid($auction_id, $bidder_id, $bid_amount)";
        mysqli_query($conn, $query);

        $_SESSION['success'] = "Bid placed successfully!";
    }
} catch (mysqli_sql_exception $e) {
    $_SESSION['error'] = "Failed to place bid: " . $e->getMessage();
} finally {
    header("Location: ../../auction_details.php?id=" . $auction_id);
    exit();
}

header('Location: index.php');
exit();
