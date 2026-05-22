<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
require_once '../config/Database.php';
$db = (new Database())->getConnection();
$stmt = $db->query("SELECT level, class, AVG(score) as avg_score FROM exam_results GROUP BY level, class");
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo json_encode(["success" => true, "data" => $results]);
?>
