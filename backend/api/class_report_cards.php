<?php
header("Content-Type: text/html");
$class = $_GET['class'] ?? '';
$term = $_GET['term'] ?? 'Term 1';
$year = $_GET['year'] ?? date('Y');

if (!$class) { echo "Class parameter required"; exit; }

require_once '../config/Database.php';
$db = (new Database())->getConnection();

// Get all students in the class
$stmt = $db->prepare("SELECT * FROM students WHERE class = ? ORDER BY first_name");
$stmt->execute([$class]);
$students = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($students)) { echo "No students found in this class."; exit; }

// For each student, compute marks, average, rank within class
$class_report = [];
foreach ($students as $student) {
    $student_id = $student['student_id'];
    // Get marks for this term/year (simplified: all marks for now, add date filtering later)
    $marksStmt = $db->prepare("SELECT subject, score FROM exam_results WHERE student_id = ?");
    $marksStmt->execute([$student_id]);
    $marks = $marksStmt->fetchAll(PDO::FETCH_ASSOC);
    $total = 0;
    foreach ($marks as $m) $total += $m['score'];
    $average = count($marks) ? round($total / count($marks), 2) : 0;
    $class_report[] = [
        'student' => $student,
        'marks' => $marks,
        'average' => $average
    ];
}

// Sort by average descending for ranking
usort($class_report, function($a, $b) {
    return $b['average'] <=> $a['average'];
});
$rank = 1;
foreach ($class_report as &$cr) {
    $cr['rank'] = $rank++;
}
?>
<!DOCTYPE html>
<html>
<head><title>Report Cards - Class <?= htmlspecialchars($class) ?></title>
<style>
    body{font-family:'Segoe UI',Arial;padding:20px;background:#f0f2f5}
    .report-card{max-width:800px;margin:20px auto;background:white;border-radius:10px;box-shadow:0 2px 10px rgba(0,0,0,0.1);padding:30px;page-break-after:always}
    .report-card:last-child{page-break-after:auto}
    h2{color:#1e3c72;text-align:center}
    .student-info{border:1px solid #ddd;padding:10px;margin-bottom:20px;border-radius:5px}
    .marks-table{width:100%;border-collapse:collapse;margin-bottom:20px}
    .marks-table th,.marks-table td{border:1px solid #ddd;padding:8px;text-align:left}
    .marks-table th{background:#f0f0f0}
    .total{font-weight:bold;margin-top:10px}
    button{background:#2a5298;color:white;border:none;padding:10px 20px;border-radius:5px;cursor:pointer;margin-top:20px;display:block;margin:20px auto}
    @media print {
        .no-print{display:none}
        .report-card{page-break-after:always}
    }
</style>
</head>
<body>
<button class="no-print" onclick="window.print()">📄 Print All Report Cards</button>
<?php foreach ($class_report as $cr): 
    $student = $cr['student'];
    $marks = $cr['marks'];
    $average = $cr['average'];
    $rank = $cr['rank'];
?>
<div class="report-card">
    <h2>G.S. Murama – Kisaro, Rwanda</h2>
    <h3>END OF TERM REPORT CARD</h3>
    <div class="student-info">
        <p><strong>Student:</strong> <?= htmlspecialchars($student['first_name']) ?> <?= htmlspecialchars($student['last_name']) ?> (<?= htmlspecialchars($student['student_id']) ?>)</p>
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
            <tr><td><?= htmlspecialchars($m['subject']) ?></td><td><?= $score ?>%</a><td><?= $grade ?></a></tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <div class="total">
        <p><strong>Average Score:</strong> <?= $average ?>%</p>
        <p><strong>Class Rank:</strong> <?= $rank ?> out of <?= count($class_report) ?></p>
        <p><strong>Remarks:</strong> <?= ($average >= 70) ? 'Excellent! Keep it up.' : (($average >= 50) ? 'Good work, but can improve.' : 'Needs more effort.') ?></p>
    </div>
    <div class="footer" style="margin-top:30px;font-size:12px;color:#888;text-align:center">
        <p>Parent's signature: _______________ | Class Teacher: _______________</p>
    </div>
</div>
<?php endforeach; ?>
</body>
</html>
?>
