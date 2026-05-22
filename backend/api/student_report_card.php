<?php
header("Content-Type: text/html");
$student_id = $_GET['student_id'] ?? '';
if (!$student_id) { echo "Student ID required"; exit; }

require_once '../config/Database.php';
$db = (new Database())->getConnection();

// Get student info
$stmt = $db->prepare("SELECT * FROM students WHERE student_id = ?");
$stmt->execute([$student_id]);
$student = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$student) { echo "Student not found"; exit; }

// Get marks
$marksStmt = $db->prepare("SELECT subject, score, exam_date FROM exam_results WHERE student_id = ?");
$marksStmt->execute([$student_id]);
$marks = $marksStmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate average
$total = 0;
foreach ($marks as $m) $total += $m['score'];
$average = count($marks) ? round($total / count($marks), 2) : 0;

?>
<!DOCTYPE html>
<html>
<head><title>Report Card - <?= $student['first_name'] ?> <?= $student['last_name'] ?></title>
<style>body{font-family:Arial;padding:20px}.report{max-width:800px;margin:auto;border:1px solid #ccc;padding:20px}h2{color:#1e3c72}</style>
</head>
<body>
<div class="report">
    <h2>G.S. Murama – Report Card</h2>
    <p><strong>Student:</strong> <?= $student['first_name'] ?> <?= $student['last_name'] ?> (<?= $student['student_id'] ?>)</p>
    <p><strong>Class:</strong> <?= $student['class'] ?> | Level: <?= $student['level'] ?></p>
    <h3>Exam Results</h3>
    <table border="1" width="100%">
        <tr><th>Subject</th><th>Score (%)</th><th>Date</th></tr>
        <?php foreach ($marks as $m): ?>
        <tr><td><?= $m['subject'] ?></td><td><?= $m['score'] ?></td><td><?= $m['exam_date'] ?></td></tr>
        <?php endforeach; ?>
    </table>
    <p><strong>Average Score:</strong> <?= $average ?>%</p>
    <hr>
    <p><em>Generated on <?= date('Y-m-d') ?></em></p>
    <button onclick="window.print()">Print / Save PDF</button>
</div>
</body>
</html>
?>
