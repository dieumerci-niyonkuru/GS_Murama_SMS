<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
require_once '../config/Database.php';
$course_id = $_GET['course_id'] ?? 0;
if (!$course_id) { echo json_encode(["success"=>false,"message"=>"Course ID required"]); exit(); }
$db = (new Database())->getConnection();
$stmt = $db->prepare("SELECT * FROM homework WHERE course_id = ? ORDER BY due_date ASC");
$stmt->execute([$course_id]);
$homework = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo json_encode(["success"=>true, "data"=>$homework]);
?>
