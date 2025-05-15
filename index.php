<?php
include 'Config/session.php';
include 'Config/config.php';

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
            margin: 0;
            padding: 0;
        }
        .container {
            margin: 100px auto 0;
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

        /* Burger Menu Styles */
        .menu {
            position: absolute;
            top: 20px;
            left: 20px;
            z-index: 100;
        }
        .burger {
            font-size: 24px;
            cursor: pointer;
            background-color: #3498db;
            color: white;
            padding: 10px 14px;
            border-radius: 8px;
        }
        .dropdown {
            display: none;
            position: absolute;
            left: 0;
            background-color: #3498db;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            margin-top: 10px;
            border-radius: 10px;
            padding: 15px;
            width: 220px;
            text-align: left;
        }
        .dropdown p {
            margin: 5px 0;
            font-size: 14px;
            color: white;
        }
        .dropdown a {
            display: block;
            margin: 8px 0;
            font-size: 14px;
            color: white;
            text-decoration: none;
        }
        .dropdown a:hover {
            text-decoration: underline;
        }

    </style>
</head>
<body>

    <!-- Burger Menu Kiri Atas -->
    <div class="menu">
        <div class="burger" onclick="toggleMenu()">☰</div>
        <div class="dropdown" id="dropdownMenu">
            <p><strong>User:</strong> <?= htmlspecialchars($_SESSION['username']) ?></p>
            <p><strong>Saldo:</strong> Rp <?= number_format($balance, 0, ',', '.') ?></p>
            <a href="mining.php">⛏️ Mining</a>
            <a href="logout.php">🔓 Logout</a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="container">
        <h3>Halo, <?= htmlspecialchars($_SESSION['username']) ?></h3>
        <p><strong>Saldo Anda:</strong><br> Rp <?= number_format($balance, 0, ',', '.') ?></p>
        <a href="transfer.php">Transfer Uang</a>
    </div>

    <script>
    function toggleMenu() {
        const menu = document.getElementById("dropdownMenu");
        menu.style.display = (menu.style.display === "block") ? "none" : "block";
    }

    // Tutup dropdown saat klik di luar area menu
    document.addEventListener('click', function(event) {
        const dropdown = document.getElementById("dropdownMenu");
        const burger = document.querySelector(".burger");

        if (!dropdown.contains(event.target) && !burger.contains(event.target)) {
            dropdown.style.display = "none";
        }
    });
    </script>
</body>
</html>
