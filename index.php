<?php include 'Config/session.php'; include 'Config/config.php'; ?>
<?php
$u = $_SESSION['username'];
$res = $conn->query("SELECT balance FROM users WHERE username='$u'");
$data = $res->fetch_assoc();
$balance = $data['balance'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard User</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: #f4f6f8;
            color: #333;
            display: flex;
            flex-direction: column;
            align-items: center;
            margin: 0;
        }
        .container {
            margin-top: 60px;
            background: white;
            padding: 30px 40px;
            border-radius: 12px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
            width: 400px;
            text-align: center;
        }
        h3 {
            margin-top: 0;
            color: #2c3e50;
        }
        p {
            font-size: 18px;
            margin: 20px 0;
        }
        a {
            display: inline-block;
            margin: 10px;
            text-decoration: none;
            padding: 10px 20px;
            border-radius: 8px;
            color: white;
            background-color: #3498db;
            transition: background 0.3s;
        }
        a:hover {
            background-color: #2980b9;
        }
        .logout {
            background-color: #e74c3c;
        }
        .logout:hover {
            background-color: #c0392b;
        }
    </style>
</head>
<body>
    <div class="container">
        <h3>Halo, <?= htmlspecialchars($_SESSION['username']) ?></h3>
        <p><strong>Saldo Anda:</strong><br> Rp <?= number_format($balance, 0, ',', '.') ?></p>
        <a href="transfer.php">Transfer Uang</a>
        <a class="logout" href="logout.php">Logout</a>
    </div>
</body>
</html>
