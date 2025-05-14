<?php include 'session.php'; include 'config.php'; ?>
<h3>Halo, <?= $_SESSION['username'] ?></h3>
<?php
$u = $_SESSION['username'];
$res = $conn->query("SELECT balance FROM users WHERE username='$u'");
$data = $res->fetch_assoc();
echo "<p>Saldo: Rp " . $data['balance'] . "</p>";
?>
<a href="transfer.php">Transfer Uang</a> |
<a href="login.php">Logout</a>
