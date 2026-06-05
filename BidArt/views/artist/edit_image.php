<?php
session_start();
require_once '../../config/db.php';

// Hanya untuk artist yang login
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'artist') {
    header('Location: ../../views/auth/login.php');
    exit();
}

$artist_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['new_image']) && isset($_POST['artwork_id'])) {
    $artwork_id = (int)$_POST['artwork_id'];

    // Verifikasi kepemilikan artwork
    $query = "SELECT * FROM artworks WHERE id = $artwork_id AND artist_id = $artist_id";
    $result = mysqli_query($conn, $query);

    if (!mysqli_fetch_assoc($result)) {
        $_SESSION['error'] = "Artwork not found or you don't have permission.";
        header('Location: manage_images.php');
        exit();
    }

    // Validasi file
    $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
    $max_size = 5 * 1024 * 1024; // 5MB

    $file = $_FILES['new_image'];

    if ($file['error'] !== UPLOAD_ERR_OK) {
        $_SESSION['error'] = "Upload error occurred. Error code: " . $file['error'];
        header('Location: manage_images.php');
        exit();
    }

    if (!in_array($file['type'], $allowed_types)) {
        $_SESSION['error'] = "Invalid file type. Only JPG, PNG, GIF, and WebP are allowed.";
        header('Location: manage_images.php');
        exit();
    }

    if ($file['size'] > $max_size) {
        $_SESSION['error'] = "File too large. Maximum size is 5MB.";
        header('Location: manage_images.php');
        exit();
    }

    // Buat direktori jika belum ada
    $upload_dir = '../../assets/uploads/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }

    // Generate nama file unik
    $file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $new_filename = 'artwork_' . $artist_id . '_' . $artwork_id . '_' . time() . '.' . $file_extension;
    $upload_path = $upload_dir . $new_filename;
    $db_path = 'assets/uploads/' . $new_filename;

    // Ambil path gambar lama untuk dihapus nanti
    $query = "SELECT image_path FROM artworks WHERE id = $artwork_id";
    $result = mysqli_query($conn, $query);
    $old_image = mysqli_fetch_assoc($result)['image_path'];

    // Upload file baru
    if (move_uploaded_file($file['tmp_name'], $upload_path)) {
        // Update database dengan path gambar baru
        $update = "UPDATE artworks SET image_path = '$db_path' WHERE id = $artwork_id AND artist_id = $artist_id";

        if (mysqli_query($conn, $update)) {
            // Hapus file gambar lama jika ada
            if ($old_image && file_exists("../../" . $old_image) && $old_image !== $db_path) {
                unlink("../../" . $old_image);
            }

            $_SESSION['success'] = "Image uploaded successfully!";
        } else {
            $_SESSION['error'] = "Failed to update database: " . mysqli_error($conn);
            // Hapus file yang baru diupload jika gagal update database
            if (file_exists($upload_path)) {
                unlink($upload_path);
            }
        }
    } else {
        $_SESSION['error'] = "Failed to upload file.";
    }

    header('Location: manage_images.php');
    exit();
} else {
    $_SESSION['error'] = "Invalid request.";
    header('Location: manage_images.php');
    exit();
}
