<?php
header("Content-Type: text/html");
$type = $_GET['type'] ?? 'fees';
if ($type === 'fees') {
    require_once '../config/Database.php';
    $db = (new Database())->getConnection();
    $stmt = $db->query("SELECT s.student_id, s.first_name, s.last_name, s.class, SUM(f.paid_amount) as total_paid FROM students s LEFT JOIN fees f ON s.student_id = f.student_id GROUP BY s.id ORDER BY s.class");
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "<h1>Fee Report</h1><table border='1'><tr><th>Student</th><th>Class</th><th>Total Paid (RWF)</th></tr>";
    foreach ($data as $row) {
        echo "<tr><td>{$row['first_name']} {$row['last_name']}</td><td>{$row['class']}</td><td>{$row['total_paid']}</td></tr>";
    }
    echo "</table><button onclick='window.print()'>Print</button>";
}
?>
