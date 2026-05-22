<?php
header("Content-Type: text/csv");
header("Content-Disposition: attachment; filename=fees_report_".date('Y-m-d').".csv");
require_once '../config/Database.php';

$db = (new Database())->getConnection();
$stmt = $db->query("
    SELECT s.student_id, s.first_name, s.last_name, s.class, 
           COALESCE(SUM(f.amount),0) as total_fees, 
           COALESCE(SUM(f.paid_amount),0) as paid,
           (COALESCE(SUM(f.amount),0) - COALESCE(SUM(f.paid_amount),0)) as balance
    FROM students s
    LEFT JOIN fees f ON s.student_id = f.student_id
    GROUP BY s.id
");
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

$output = fopen('php://output', 'w');
fputcsv($output, ['Student ID', 'Name', 'Class', 'Total Fees', 'Paid', 'Balance']);
foreach ($rows as $row) {
    fputcsv($output, [
        $row['student_id'],
        $row['first_name'] . ' ' . $row['last_name'],
        $row['class'],
        $row['total_fees'],
        $row['paid'],
        $row['balance']
    ]);
}
fclose($output);
?>
