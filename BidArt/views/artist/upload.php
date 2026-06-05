<?php
session_start();
require_once '../../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'artist') {
    header('Location: ../auth/login.php');
    exit();
}

$artist_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['add_artwork'])) {
    $_SESSION['error'] = "Permintaan tidak valid";
    header("Location: dashboard.php");
    exit();
}

$title = mysqli_real_escape_string($conn, $_POST['title']);
$description = mysqli_real_escape_string($conn, $_POST['description']);
$starting_price = (float) $_POST['starting_price'];
$image = $_FILES['artwork_image'];

// Validasi dasar
if (!$title || !$description || $starting_price <= 0 || !$image['tmp_name']) {
    $_SESSION['error'] = "Semua field wajib diisi";
    header("Location: dashboard.php");
    exit();
}

// Buat folder jika belum ada
$uploadDir = '../../assets/uploads/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

$newName = uniqid('art_') . '.' . strtolower(pathinfo($image['name'], PATHINFO_EXTENSION));
$targetPath = $uploadDir . $newName;
$relativePath = 'assets/uploads/' . $newName;

// Upload file
if (!move_uploaded_file($image['tmp_name'], $targetPath)) {
    $_SESSION['error'] = "Gagal mengunggah gambar.";
    header("Location: dashboard.php");
    exit();
}

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    // Panggil prosedur tersimpan
    $query = "CALL sp_tambah_karya_seni('$title', '$description', $artist_id, $starting_price, '$relativePath')";
    mysqli_query($conn, $query);

    $_SESSION['success'] = "Karya seni berhasil ditambahkan dan lelang dimulai!";
} catch (Exception $e) {
    $_SESSION['error'] = "Gagal menambahkan karya: " . $e->getMessage();

    // Hapus gambar kalau gagal
    if (file_exists($targetPath)) {
        unlink($targetPath);
    }
}

header("Location: dashboard.php");
exit();
