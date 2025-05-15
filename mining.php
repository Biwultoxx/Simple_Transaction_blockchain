<?php
include 'Config/session.php';
include 'Config/config.php';

$u = $_SESSION['username'];
$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_encode(['username' => $u]);

    $options = [
        'http' => [
            'header'  => "Content-Type: application/json",
            'method'  => 'POST',
            'content' => $data,
        ]
    ];
    $context = stream_context_create($options);
    $response = @file_get_contents("http://localhost:5000/mining", false, $context);

    if ($response !== false) {
        $bonus = 500;

        $stmt = $conn->prepare("UPDATE users SET balance = balance + ? WHERE username = ?");
        $stmt->bind_param("is", $bonus, $u);
        $stmt->execute();

        $message = "✅ Block baru telah di-mining! Anda menerima bonus Rp " . number_format($bonus, 0, ',', '.');
    } else {
        $message = "❌ Gagal melakukan mining. Pastikan server Flask berjalan dan menerima request POST.";
    }
}

$res = $conn->query("SELECT balance FROM users WHERE username='$u'");
$data = $res->fetch_assoc();
$balance = $data['balance'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Mining Page</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: #eef2f5;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .box {
            background: white;
            padding: 30px 40px;
            border-radius: 12px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
            text-align: center;
            width: 100%;
            max-width: 420px;
        }
        h2 {
            margin-top: 0;
            color: #2c3e50;
        }
        p {
            font-size: 16px;
            color: #333;
        }
        .button-group {
            display: flex;
            justify-content: space-between;
            gap: 10px;
            margin-top: 20px;
        }
        button, .home-button {
            flex: 1;
            padding: 10px 0;
            background-color: #2ecc71;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 15px;
            cursor: pointer;
            text-decoration: none;
            transition: background-color 0.3s;
        }
        button:hover, .home-button:hover {
            background-color: #27ae60;
        }
        .message {
            margin-top: 20px;
            font-size: 15px;
            color: #2c3e50;
        }
    </style>
</head>
<body>
    <div class="box">
        <h2>⛏️ Mining Blockchain</h2>
        <p>Halo <strong><?= htmlspecialchars($u) ?></strong><br>Saldo Anda: <strong>Rp <?= number_format($balance, 0, ',', '.') ?></strong></p>
        
        <form method="post">
            <div class="button-group">
                <button type="submit">Mulai Mining</button>
                <a href="index.php" class="home-button">🏠 Home</a>
            </div>
        </form>

        <?php if ($message): ?>
            <div class="message"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>
    </div>
</body>
</html>
