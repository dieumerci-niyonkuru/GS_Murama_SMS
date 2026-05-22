<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
require_once '../config/Database.php';

$db = (new Database())->getConnection();
$stmt = $db->query("
    SELECT s.student_id, s.first_name, s.last_name, s.class, 
           COALESCE(SUM(f.amount),0) as total_fees, 
           COALESCE(SUM(f.paid_amount),0) as total_paid,
           (COALESCE(SUM(f.amount),0) - COALESCE(SUM(f.paid_amount),0)) as balance
    FROM students s
    LEFT JOIN fees f ON s.student_id = f.student_id
    GROUP BY s.id
    HAVING balance > 0
    ORDER BY balance DESC
");
$defaulters = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo json_encode(["success" => true, "data" => $defaulters]);
?>
