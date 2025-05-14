<?php
include 'config.php';
session_start(); // OK karena login.php tidak include session.php

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $u = $_POST['username'];
    $p = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->bind_param("s", $u);
    $stmt->execute();
    $res = $stmt->get_result();
    $user = $res->fetch_assoc();

    if ($user && password_verify($p, $user['password'])) {
        $_SESSION['username'] = $u;
        header("Location: index.php");
        exit;
    } else {
        $error = "Login gagal.";
    }
}
?>

<form method="POST">
    <input name="username" required>
    <input name="password" type="password" required>
    <button type="submit">Login</button>
</form>
<?php if (isset($error)) echo "<p style='color:red;'>$error</p>"; ?>
