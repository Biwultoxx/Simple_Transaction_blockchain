<?php
include 'Config/session.php';
include 'Config/config.php';
if (!isset($_SESSION['username'])) die("Login required");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sender    = $_SESSION['username'];
    $recipient = mysqli_real_escape_string($conn, $_POST['recipient'] ?? '');
    $amount    = floatval($_POST['amount'] ?? 0);

    if (!$recipient || $amount <= 0) {
        die("Form incomplete");
    }

    // cek saldo
    $s = $conn->prepare("SELECT balance FROM users WHERE username=?");
    $s->bind_param("s", $sender);
    $s->execute();
    $s->bind_result($bal);
    $s->fetch();
    $s->close();
    if ($bal < $amount) die("Insufficient funds");

    // call blockchain (queue tx + bonus)
    $payload = json_encode(['sender' => $sender, 'recipient' => $recipient, 'amount' => $amount]);
    $opts = ['http' => [
        'method' => "POST",
        'header' => "Content-Type: application/json\r\n",
        'content' => $payload
    ]];
    $ctx = stream_context_create($opts);
    $resp = @file_get_contents('http://localhost:5000/transaction/new', false, $ctx);
    if ($resp === false) die("Blockchain error");
    $hdr = $http_response_header[0] ?? '';
    if (!preg_match('/^HTTP\/.*\s20[1]/', $hdr)) die("Bad resp: $hdr");
    $js = json_decode($resp, true);

    // Apply to DB
    $conn->begin_transaction();
    try {
        // Primary transfer
        $u = $conn->prepare("UPDATE users SET balance=balance-? WHERE username=?");
        $u->bind_param("ds", $amount, $sender); 
        $u->execute();
        $u = $conn->prepare("UPDATE users SET balance=balance+? WHERE username=?");
        $u->bind_param("ds", $amount, $recipient);
        $u->execute();
        $u = $conn->prepare("INSERT INTO transactions(sender,recipient,amount) VALUES(?,?,?)");
        $u->bind_param("ssd", $sender, $recipient, $amount);
        $u->execute();

        // Apply bonus
        if (!empty($js['bonus_transactions'])) {
            foreach ($js['bonus_transactions'] as $b) {
                $bamt = floatval($b['amount']);
                $buser = $b['recipient'];
                $u = $conn->prepare("UPDATE users SET balance=balance+? WHERE username=?");
                $u->bind_param("ds", $bamt, $buser); $u->execute();
                $u = $conn->prepare("INSERT INTO transactions(sender,recipient,amount) VALUES('system_bonus',?,?)");
                $u->bind_param("sd", $buser, $bamt); $u->execute();
            }
        }

        $conn->commit();
        echo "Success!";
    } catch (Exception $e) {
        $conn->rollback();
        echo "DB Error: ".$e->getMessage();
    }
}
?>
