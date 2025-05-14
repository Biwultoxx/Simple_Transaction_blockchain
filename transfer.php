<?php include 'session.php'; ?>
<form action="process_transfer.php" method="POST">
    <input name="recipient" placeholder="Penerima" required>
    <input name="amount" type="number" step="0.01" required>
    <button type="submit">Kirim</button>
</form>
