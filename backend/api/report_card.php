<?php
header("Content-Type: text/html");
$student_id = $_GET['student_id'] ?? '';
$term = $_GET['term'] ?? 'Term 1';
$year = $_GET['year'] ?? date('Y');

if (!$student_id) { echo "Student ID required"; exit; }

require_once '../config/Database.php';
$db = (new Database())->getConnection();

// Get student info
$stmt = $db->prepare("SELECT * FROM students WHERE student_id = ?");
$stmt->execute([$student_id]);
$student = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$student) { echo "Student not found"; exit; }

// Get marks for the term/year
$marksStmt = $db->prepare("SELECT subject, score, exam_date FROM exam_results WHERE student_id = ? AND exam_date BETWEEN ? AND ?");
// For simplicity, we'll get all marks (no term filter yet)
$marksStmt = $db->prepare("SELECT subject, score FROM exam_results WHERE student_id = ?");
$marksStmt->execute([$student_id]);
$marks = $marksStmt->fetchAll(PDO::FETCH_ASSOC);

$total = 0;
foreach ($marks as $m) $total += $m['score'];
$average = count($marks) ? round($total / count($marks), 2) : 0;

// Rank among class (optional – get all students in same class, average their marks)
$rankStmt = $db->prepare("
    SELECT s.student_id, AVG(er.score) as avg_score 
    FROM students s 
    LEFT JOIN exam_results er ON s.student_id = er.student_id 
    WHERE s.class = ? 
    GROUP BY s.id 
    ORDER BY avg_score DESC
");
$rankStmt->execute([$student['class']]);
$studentsRank = $rankStmt->fetchAll(PDO::FETCH_ASSOC);
$position = 1;
foreach ($studentsRank as $pos => $s) {
    if ($s['student_id'] == $student_id) { $position = $pos+1; break; }
}
?>
<!DOCTYPE html>
<html>
<head><title>Report Card - <?= htmlspecialchars($student['first_name']) ?> <?= htmlspecialchars($student['last_name']) ?></title>
<style>
    body{font-family:'Segoe UI',Arial;padding:20px;background:#f0f2f5}
    .report-card{max-width:800px;margin:auto;background:white;border-radius:10px;box-shadow:0 2px 10px rgba(0,0,0,0.1);padding:30px}
    h2{color:#1e3c72;text-align:center}
    .school-name{text-align:center;margin-bottom:20px}
    .student-info{border:1px solid #ddd;padding:10px;margin-bottom:20px;border-radius:5px}
    .marks-table{width:100%;border-collapse:collapse;margin-bottom:20px}
    .marks-table th,.marks-table td{border:1px solid #ddd;padding:8px;text-align:left}
    .marks-table th{background:#f0f0f0}
    .total{font-weight:bold;margin-top:10px}
    .footer{text-align:center;margin-top:30px;font-size:12px;color:#888}
    button{background:#2a5298;color:white;border:none;padding:10px 20px;border-radius:5px;cursor:pointer;margin-top:20px}
</style>
</head>
<body>
<div class="report-card">
    <div class="school-name">
        <h2>G.S. Murama – Kisaro, Rwanda</h2>
        <p>Nursery · Primary · Secondary</p>
        <h3>END OF TERM REPORT CARD</h3>
    </div>
    <div class="student-info">
        <p><strong>Student Name:</strong> <?= htmlspecialchars($student['first_name']) ?> <?= htmlspecialchars($student['last_name']) ?></p>
        <p><strong>Student ID:</strong> <?= htmlspecialchars($student['student_id']) ?></p>
        <p><strong>Class:</strong> <?= htmlspecialchars($student['class']) ?> | Level: <?= htmlspecialchars($student['level']) ?></p>
        <p><strong>Term:</strong> <?= $term ?> | Year: <?= $year ?></p>
    </div>
    <h3>Academic Performance</h3>
    <table class="marks-table">
        <thead><tr><th>Subject</th><th>Score (%)</th><th>Grade</th></tr></thead>
        <tbody>
        <?php foreach ($marks as $m): 
            $score = $m['score'];
            $grade = ($score >= 80) ? 'A' : (($score >= 70) ? 'B' : (($score >= 60) ? 'C' : (($score >= 50) ? 'D' : 'F')));
        ?>
            <tr><td><?= htmlspecialchars($m['subject']) ?></td><td><?= $score ?>%</td><td><?= $grade ?></td></tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <div class="total">
        <p><strong>Average Score:</strong> <?= $average ?>%</p>
        <p><strong>Class Rank:</strong> <?= $position ?> out of <?= count($studentsRank) ?></p>
        <p><strong>Remarks:</strong> <?= ($average >= 70) ? 'Excellent! Keep it up.' : (($average >= 50) ? 'Good work, but can improve.' : 'Needs more effort.') ?></p>
    </div>
    <div class="footer">
        <p>This is a system-generated report card. Parent's signature: _______________</p>
        <p>Head Teacher: ____________________ | Class Teacher: ____________________</p>
    </div>
    <button onclick="window.print()">📄 Print / Save as PDF</button>
</div>
</body>
</html>
?>
