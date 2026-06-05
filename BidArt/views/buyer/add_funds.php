<?php
session_start();
require_once '../../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'buyer') {
    header('Location: ../../login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $amount = (float) $_POST['amount'];
    $payment_method = $_POST['payment_method'];
    $user_id = $_SESSION['user_id'];

    if ($amount < 1) {
        $_SESSION['error'] = 'Minimum deposit amount is $1.00';
    } else {
        mysqli_begin_transaction($conn);

        // Tambahkan saldo ke akun user
        $update = "UPDATE users SET balance = balance + $amount WHERE id = $user_id";
        mysqli_query($conn, $update);

        // Simpan riwayat transaksi
        $description = mysqli_real_escape_string($conn, "Deposit via " . ucfirst(str_replace('_', ' ', $payment_method)));
        $log = "INSERT INTO transactions (user_id, type, amount, description, status) 
                    VALUES ($user_id, 'deposit', $amount, '$description', 'completed')";
        mysqli_query($conn, $log);

        // Ambil saldo terbaru
        $balanceRes = mysqli_query($conn, "SELECT balance FROM users WHERE id = $user_id");
        $row = mysqli_fetch_assoc($balanceRes);
        $_SESSION['balance'] = $row['balance'];

        mysqli_commit($conn);
        $_SESSION['success'] = "Successfully added $" . number_format($amount, 2) . " to your account!";
    }
}

header('Location: dashboard.php');
exit();
