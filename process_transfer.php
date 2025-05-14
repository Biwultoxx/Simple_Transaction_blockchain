<?php
include 'session.php';
include 'config.php';

$sender = $_SESSION['username'];
$recipient = $_POST['recipient'];
$amount = floatval($_POST['amount']);

// Validasi saldo
$res = $conn->query("SELECT balance FROM users WHERE username='$sender'");
$user = $res->fetch_assoc();

if ($user['balance'] < $amount) {
    die("Saldo tidak cukup!");
}

// Update DB transaksi & saldo
$conn->query("UPDATE users SET balance = balance - $amount WHERE username='$sender'");
$conn->query("UPDATE users SET balance = balance + $amount WHERE username='$recipient'");
$conn->query("INSERT INTO transactions (sender, recipient, amount) VALUES ('$sender', '$recipient', $amount)");

// Kirim ke blockchain API
$data = json_encode([
    "sender" => $sender,
    "recipient" => $recipient,
    "amount" => $amount
]);
$opts = [
    'http' => [
        'method' => "POST",
        'header' => "Content-Type: application/json",
        'content' => $data
    ]
];
$context = stream_context_create($opts);
$response = file_get_contents('http://localhost:5000/transaction/new', false, $context);

echo "Transaksi berhasil. <a href='index.php'>Kembali</a>";
?>
