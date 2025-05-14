<?php include 'Config/session.php'; include 'Config/config.php' ?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Transfer Uang</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: #f7f9fb;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            margin: 0;
        }
        .transfer-box {
            background: white;
            padding: 30px 40px;
            border-radius: 12px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
            width: 350px;
        }
        h2 {
            text-align: center;
            margin-top: 0;
            color: #2c3e50;
        }
        input[type="text"], input[type="number"] {
            width: 100%;
            padding: 12px;
            margin: 10px 0 20px 0;
            border: 1px solid #ccc;
            border-radius: 6px;
            box-sizing: border-box;
        }
        button {
            width: 100%;
            padding: 12px;
            background: #27ae60;
            border: none;
            color: white;
            font-size: 16px;
            border-radius: 6px;
            cursor: pointer;
        }
        button:hover {
            background: #1e8449;
        }
        a {
            display: block;
            text-align: center;
            margin-top: 20px;
            color: #3498db;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="transfer-box">
        <h2>Transfer Uang</h2>
        <form action="process_transfer.php" method="POST">
            <input name="recipient" type="text" placeholder="Nama Penerima" required>
            <input name="amount" type="number" step="100" placeholder="Jumlah (Rp)" required>
            <button type="submit">Kirim</button>
        </form>
        <a href="index.php">← Kembali ke Beranda</a>
    </div>
</body>
</html>
