<?php
session_start();

// Cegah cache agar halaman tidak bisa diakses setelah logout
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: 0");

// Cek login
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}
?>
