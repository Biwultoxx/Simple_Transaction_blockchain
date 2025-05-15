<?php
include 'Config/session.php';
include 'Config/config.php';

$resultMessage = '';

if (!isset($_SESSION['username'])) die("Login required");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sender    = $_SESSION['username'];
    $recipient = mysqli_real_escape_string($conn, $_POST['recipient'] ?? '');
    $amount    = floatval($_POST['amount'] ?? 0);

    if (!$recipient || $amount <= 0) {
        $resultMessage = "Form incomplete";
    } elseif ($recipient === $sender) {
        $resultMessage = "Anda tidak bisa mentransfer ke diri sendiri.";
    } else {
        $s = $conn->prepare("SELECT balance FROM users WHERE username=?");
        $s->bind_param("s", $sender);
        $s->execute();
        $s->bind_result($bal);
        $s->fetch();
        $s->close();

        if ($bal < $amount) {
            $resultMessage = "Saldo tidak mencukupi.";
        } else {
            // Hitung bonus
            $bonus = 0;
            if ($amount > 9999) {
                $bonus = 1000;
            } elseif ($amount > 4999) {
                $bonus = 500;
            } elseif ($amount > 999) {
                $bonus = 100;
            }

            // Kirim transaksi ke Flask
            $payload = json_encode(['sender' => $sender, 'recipient' => $recipient, 'amount' => $amount]);
            $opts = ['http' => [
                'method' => "POST",
                'header' => "Content-Type: application/json\r\n",
                'content' => $payload
            ]];
            $ctx = stream_context_create($opts);
            $resp = @file_get_contents('http://localhost:5000/transaction/new', false, $ctx);
            $hdr = $http_response_header[0] ?? '';

            if ($resp === false || !preg_match('/^HTTP\/.*\s20[1]/', $hdr)) {
                $resultMessage = "Blockchain error atau response gagal.";
            } else {
                // Lanjutkan transaksi database
                $conn->begin_transaction();
                try {
                    // Kurangi saldo pengirim
                    $u = $conn->prepare("UPDATE users SET balance=balance-? WHERE username=?");
                    $u->bind_param("ds", $amount, $sender);
                    if (!$u->execute()) throw new Exception("Gagal mengurangi saldo pengirim.");

                    // Tambah saldo penerima
                    $u = $conn->prepare("UPDATE users SET balance=balance+? WHERE username=?");
                    $u->bind_param("ds", $amount, $recipient);
                    if (!$u->execute()) throw new Exception("Gagal menambahkan saldo penerima.");

                    // Simpan transaksi
                    $u = $conn->prepare("INSERT INTO transactions(sender,recipient,amount) VALUES(?,?,?)");
                    $u->bind_param("ssd", $sender, $recipient, $amount);
                    if (!$u->execute()) throw new Exception("Gagal menyimpan transaksi.");

                    // Simpan bonus jika ada
                    if ($bonus > 0) {
                        $u = $conn->prepare("UPDATE users SET balance=balance+? WHERE username=?");
                        $u->bind_param("ds", $bonus, $sender);
                        if (!$u->execute()) throw new Exception("Gagal menambahkan bonus.");

                        $u = $conn->prepare("INSERT INTO transactions(sender,recipient,amount) VALUES('system_bonus',?,?)");
                        $u->bind_param("sd", $sender, $bonus);
                        if (!$u->execute()) throw new Exception("Gagal menyimpan transaksi bonus.");
                    }

                    $conn->commit();
                    $resultMessage = "Transfer berhasil. Bonus: Rp " . number_format($bonus, 0, ',', '.');
                } catch (Exception $e) {
                    $conn->rollback();
                    $resultMessage = "Gagal: " . $e->getMessage();
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Hasil Transfer</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f3f4f6;
            margin: 0;
            padding: 0;
            display: flex;
            align-items: center;
            flex-direction: column;
            justify-content: center;
            height: 100vh;
        }
        .container {
            background: white;
            padding: 2rem 3rem;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            text-align: center;
        }
        .message {
            font-size: 1.25rem;
            margin-bottom: 1.5rem;
            color: #111827;
        }
        .btn {
            display: inline-block;
            padding: 0.6rem 1.2rem;
            background-color: #2563eb;
            color: white;
            border: none;
            border-radius: 6px;
            text-decoration: none;
            font-size: 1rem;
            transition: background 0.3s;
        }
        .btn:hover {
            background-color: #1d4ed8;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="message"><?= htmlspecialchars($resultMessage) ?></div>
        <a href="index.php" class="btn">← Kembali ke Halaman Utama</a>
    </div>
</body>
</html>
