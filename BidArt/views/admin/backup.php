<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo "Akses ditolak.";
    exit();
}

// Path ke file .bat
$batFile = realpath(__DIR__ . '/../../scripts/backup.cmd');

// Cek apakah file ada
if (!file_exists($batFile)) {
    $_SESSION['error'] = 'File backup.bat tidak ditemukan.';
    header('Location: dashboard.php');
    exit();
}

// Jalankan file .bat di background (tanpa nunggu proses)
pclose(popen("start /B \"\" \"$batFile\"", "r"));

$_SESSION['success'] = 'Backup database sedang diproses di background.';
header('Location: dashboard.php');
exit();
