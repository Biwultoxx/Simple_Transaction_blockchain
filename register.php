<?php include 'config.php'; ?>
<form method="POST">
    <input name="username" required>
    <input name="password" type="password" required>
    <button type="submit">Daftar</button>
</form>

<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $u = $_POST['username'];
    $p = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $conn->query("INSERT INTO users (username, password) VALUES ('$u', '$p')");
    echo "Berhasil daftar. <a href='login.php'>Login</a>";
}
?>
