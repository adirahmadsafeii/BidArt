<?php
session_start();
require_once '../../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo "Akses ditolak";
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sql = "SELECT id FROM auctions WHERE status = 'active' AND end_time <= NOW()";
    $result = mysqli_query($conn, $sql);

    $jumlah_ditutup = 0;

    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $id = $row['id'];
            mysqli_query($conn, "CALL tutup_lelang($id)");
            $jumlah_ditutup++;
        }
    }

    echo "Berhasil menutup $jumlah_ditutup lelang yang sudah berakhir.";
} else {
    echo "Akses tidak valid.";
}
