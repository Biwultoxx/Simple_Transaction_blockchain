<?php
$host = 'localhost';
$db = 'blockchain_app';
$user = 'root';
$pass = ''; // Atur sesuai phpMyAdmin-mu

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}
?>
