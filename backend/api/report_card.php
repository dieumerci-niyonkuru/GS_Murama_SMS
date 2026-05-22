<?php
header("Content-Type: text/html");
$student_id = $_GET['student_id'] ?? '';
if (!$student_id) { echo "Student ID required"; exit; }
require_once '../config/Database.php';
$db = (new Database())->getConnection();
$student = $db->prepare("SELECT * FROM students WHERE student_id = ?")->execute([$student_id])->fetch(PDO::FETCH_ASSOC);
if (!$student) { echo "Student not found"; exit; }
$marks = $db->prepare("SELECT m.*, c.course_name FROM marks m JOIN courses c ON m.course_id = c.id WHERE m.student_id = ?")->execute([$student_id])->fetchAll(PDO::FETCH_ASSOC);
?>
<html>
<head><title>Report Card - <?php echo $student['first_name'] . ' ' . $student['last_name']; ?></title>
<style>body{font-family:Arial;margin:40px}table{width:100%;border-collapse:collapse}th,td{border:1px solid #ddd;padding:8px}th{background:#2a5298;color:white}</style>
</head>
<body>
<h1>G.S. Murama - Report Card</h1>
<h2>Student: <?php echo $student['first_name'] . ' ' . $student['last_name']; ?> (<?php echo $student['student_id']; ?>)</h2>
<h3>Class: <?php echo $student['class']; ?> | Term: <?php echo date('Y') . ' Term 1'; ?></h3>
<table>
    <tr><th>Course</th><th>Assessment</th><th>Score</th><th>Max Score</th></tr>
    <?php foreach ($marks as $m): ?>
    <tr><td><?php echo $m['course_name']; ?></td><td><?php echo $m['assessment_type']; ?></td><td><?php echo $m['score']; ?></td><td><?php echo $m['max_score']; ?></td></tr>
    <?php endforeach; ?>
</table>
<p>Generated on <?php echo date('Y-m-d'); ?></p>
<button onclick="window.print()">Print / Save as PDF</button>
</body>
</html>
?>
