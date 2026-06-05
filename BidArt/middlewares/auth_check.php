<?php
// Middleware: Redirect to login page if user is not logged in
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$publicPages = [
    'index.php',
    'gallery.php',
    'auction_details.php',
];

$currentPage = basename($_SERVER['SCRIPT_NAME']);
if (in_array($currentPage, $publicPages, true)) {
    return; // allow access to public pages without login
}

if (!isset($_SESSION['user_id'])) {
    // Determine the correct path to login page based on current file location
    $currentDir = dirname($_SERVER['SCRIPT_FILENAME']);
    $rootDir = realpath(__DIR__ . '/..');
    
    // Calculate relative path from current script to login page
    if (strpos($currentDir, $rootDir) === 0) {
        $depth = substr_count(str_replace($rootDir, '', $currentDir), DIRECTORY_SEPARATOR);
    } else {
        $depth = 0;
    }
    
    $prefix = str_repeat('../', $depth);
    header("Location: {$prefix}views/auth/login.php");
    exit();
}
