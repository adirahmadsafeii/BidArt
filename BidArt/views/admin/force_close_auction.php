<?php
session_start();
require_once '../../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $auction_id = $_POST['auction_id'];
    if (tutupLelang($auction_id)) {
        $_SESSION['success'] = 'Berhasil menutup lelang';
    } else {
        $_SESSION['error'] = 'Gagal menutup lelang';
    }
    header('Location: dashboard.php');
    exit();
}
