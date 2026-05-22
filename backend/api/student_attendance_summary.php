<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
require_once '../config/Database.php';
$db = (new Database())->getConnection();
$stmt = $db->query("SELECT s.student_id, s.first_name, s.last_name, s.class, COUNT(CASE WHEN a.status = 'Absent' THEN 1 END) as absences, COUNT(CASE WHEN a.status = 'Late' THEN 1 END) as lates FROM students s LEFT JOIN attendance a ON s.student_id = a.student_id GROUP BY s.id ORDER BY absences DESC");
$summary = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo json_encode(["success" => true, "data" => $summary]);
?>
