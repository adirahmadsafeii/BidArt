<?php
// Database configuration
$host = "localhost";
$username = "root";
$password = "";
$database = "bidart";

// Create connection
$conn = mysqli_connect($host, $username, $password, $database);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Set charset
mysqli_set_charset($conn, "utf8");

// Function to count total bids
function hitungTotalBid($auction_id)
{
    global $conn;
    $query = "SELECT hitung_total_bid($auction_id) as total";
    $result = mysqli_query($conn, $query);
    $row = mysqli_fetch_assoc($result);
    return $row['total'];
}
function getHighestBid($auction_id)
{
    global $conn;
    $auction_id = (int)$auction_id;
    $query = "SELECT get_highest_bid($auction_id) AS highest";
    $result = mysqli_query($conn, $query);
    $row = mysqli_fetch_assoc($result);
    return $row ? $row['highest'] : 0;
}
// Function to add artwork
function tambahKaryaSeni($title, $description, $artist_id, $starting_price, $image_path)
{
    global $conn;
    // Escape strings to prevent SQL injection (basic protection)
    $title = mysqli_real_escape_string($conn, $title);
    $description = mysqli_real_escape_string($conn, $description);
    $image_path = mysqli_real_escape_string($conn, $image_path);

    $query = "CALL sp_tambah_karya_seni('$title', '$description', $artist_id, $starting_price, '$image_path')";
    return mysqli_query($conn, $query);
}

// Function to close auction
function tutupLelang($auction_id)
{
    global $conn;
    $query = "CALL sp_tutup_lelang($auction_id)";
    return mysqli_query($conn, $query);
}

// Function to place bid with deadlock retry logic
function placeBidWithDeadlockRetry($auction_id, $bidder_id, $bid_amount)
{
    global $conn;
    
    $auction_id = (int)$auction_id;
    $bidder_id = (int)$bidder_id;
    $bid_amount = (float)$bid_amount;
    
    $query = "CALL sp_place_bid_with_deadlock_retry($auction_id, $bidder_id, $bid_amount, @success, @message)";
    
    if (mysqli_query($conn, $query)) {
        // Get the output parameters
        $result = mysqli_query($conn, "SELECT @success as success, @message as message");
        $row = mysqli_fetch_assoc($result);
        
        return array(
            'success' => (bool)$row['success'],
            'message' => $row['message'],
            'timestamp' => date('Y-m-d H:i:s')
        );
    } else {
        return array(
            'success' => false,
            'message' => 'Failed to execute bid placement: ' . mysqli_error($conn),
            'timestamp' => date('Y-m-d H:i:s')
        );
    }
}

// Transaction functions
function beginTransaction()
{
    global $conn;
    return mysqli_begin_transaction($conn);
}

function commit()
{
    global $conn;
    return mysqli_commit($conn);
}

function rollback()
{
    global $conn;
    return mysqli_rollback($conn);
}

// Close connection
function closeConnection()
{
    global $conn;
    mysqli_close($conn);
}
