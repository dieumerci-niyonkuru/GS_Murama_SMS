<?php
header("Content-Type: text/html");
$receipt_id = $_GET['id'] ?? 0;
require_once '../config/Database.php';
$db = (new Database())->getConnection();
$stmt = $db->prepare("
    SELECT f.*, s.first_name, s.last_name, s.class, s.parent_phone, s.parent_email
    FROM fees f
    JOIN students s ON f.student_id = s.student_id
    WHERE f.id = ?
");
$stmt->execute([$receipt_id]);
$fee = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$fee) { echo "Receipt not found"; exit; }
?>
<!DOCTYPE html>
<html>
<head><title>Receipt #<?= $fee['receipt_number'] ?></title>
<style>
body{font-family:monospace; padding:20px}
.receipt{border:1px solid #ccc; padding:20px; max-width:400px; margin:auto}
.header{text-align:center}
.amount{font-size:24px; font-weight:bold}
</style>
</head>
<body>
<div class="receipt">
    <div class="header">
        <h2>G.S. Murama, Kisaro</h2>
        <p>School Fees Receipt</p>
    </div>
    <p><strong>Receipt No:</strong> <?= $fee['receipt_number'] ?></p>
    <p><strong>Date:</strong> <?= $fee['payment_date'] ?></p>
    <p><strong>Student:</strong> <?= $fee['first_name'] ?> <?= $fee['last_name'] ?> (<?= $fee['student_id'] ?>)</p>
    <p><strong>Class:</strong> <?= $fee['class'] ?></p>
    <p><strong>Term:</strong> <?= $fee['term'] ?> | Year: <?= $fee['year'] ?></p>
    <p><strong>Amount Paid:</strong> <span class="amount"><?= number_format($fee['paid_amount'], 0) ?> RWF</span></p>
    <p><strong>Payment Method:</strong> <?= $fee['payment_method'] ?></p>
    <p><strong>Balance:</strong> <?= number_format($fee['balance'], 0) ?> RWF</p>
    <hr>
    <p>Thank you for paying school fees.</p>
    <button onclick="window.print()">Print / Save PDF</button>
</div>
</body>
</html>
?>
