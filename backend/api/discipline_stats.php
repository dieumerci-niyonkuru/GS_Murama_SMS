<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
require_once '../config/Database.php';
$db = (new Database())->getConnection();
$totalIncidents = $db->query("SELECT COUNT(*) FROM discipline_records")->fetchColumn();
$topStudents = $db->query("SELECT student_id, COUNT(*) as cnt FROM discipline_records GROUP BY student_id ORDER BY cnt DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
$byType = $db->query("SELECT incident_type, COUNT(*) as cnt FROM discipline_records GROUP BY incident_type")->fetchAll(PDO::FETCH_ASSOC);
echo json_encode(["success" => true, "total_incidents" => $totalIncidents, "top_students" => $topStudents, "by_type" => $byType]);
?>
